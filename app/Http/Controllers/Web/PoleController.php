<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DailyReport;
use App\Models\Pole;
use App\Models\PolePhoto;
use App\Support\ImageStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PoleController extends Controller
{
    public function export(Request $request): Response
    {
        $validated = $request->validate([
            'query' => ['sometimes', 'nullable', 'string', 'max:120'],
            'year' => ['sometimes', 'nullable', 'integer', 'min:2000', 'max:2100'],
            'condition' => ['sometimes', 'nullable', Rule::in(['keropos', 'miring', 'berkarat', 'kokoh'])],
        ]);

        $poles = Pole::query()
            ->with(['createdBy:id,name,role,email', 'updatedBy:id,name,role,email', 'photos'])
            ->search($validated['query'] ?? null)
            ->when($validated['year'] ?? null, fn ($query, $year) => $query->whereYear('created_at', (int) $year))
            ->condition($validated['condition'] ?? null)
            ->latest()
            ->get();

        $headers = [
            'ID Pelanggan',
            'Nama Jalan',
            'Latitude',
            'Longitude',
            'Daya Lampu',
            'Kondisi Tiang',
            'Jenis Tiang',
            'Tinggi Tiang',
            'Tipe Ornamen',
            'Jenis Lampu',
            'Status Lampu',
            'Kondisi Rumah Lampu',
            'Jumlah Titik Lampu',
            'KWH Meter',
            'Kepemilikan',
            'Pertama Diinput',
            'Diinput Oleh',
            'Update Terakhir',
            'Update Oleh',
            'Foto URL',
            'Keterangan',
        ];

        $html = '<html><head><meta charset="UTF-8"></head><body><table border="1"><thead><tr>';
        foreach ($headers as $header) {
            $html .= '<th>'.e($header).'</th>';
        }
        $html .= '</tr></thead><tbody>';

        foreach ($poles as $pole) {
            $row = [
                $pole->customer_id,
                $pole->street_name,
                $pole->latitude,
                $pole->longitude,
                $pole->power_usage,
                $this->formatOption($pole->condition),
                $this->formatOption($pole->pole_type),
                $pole->height,
                $this->formatOption($pole->ornament_type),
                $this->formatLampType($pole->lamp_type),
                $this->formatOption($pole->lamp_status),
                $this->formatOption($pole->lamp_house_condition),
                $pole->light_point_count,
                $this->formatOption($pole->kwh_meter),
                $this->formatOwnership($pole->ownership),
                optional($pole->created_at)->format('H:i d-m-Y'),
                $pole->createdBy?->name,
                optional($pole->updated_at)->format('H:i d-m-Y'),
                $pole->updatedBy?->name,
                $pole->photos->pluck('photo_url')->implode(', ') ?: $pole->photo_url,
                $pole->notes,
            ];

            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td>'.e((string) $cell).'</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table></body></html>';

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="data-tiang-pju-'.now()->toDateString().'.xls"',
        ]);
    }

    public function datatable(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'draw' => ['sometimes', 'integer', 'min:0'],
            'start' => ['sometimes', 'integer', 'min:0'],
            'length' => ['sometimes', 'integer', 'min:-1', 'max:100'],
            'search.value' => ['sometimes', 'nullable', 'string', 'max:120'],
            'year' => ['sometimes', 'nullable', 'integer', 'min:2000', 'max:2100'],
            'condition' => ['sometimes', 'nullable', Rule::in(['keropos', 'miring', 'berkarat', 'kokoh'])],
            'order.0.column' => ['sometimes', 'integer', 'min:0'],
            'order.0.dir' => ['sometimes', Rule::in(['asc', 'desc'])],
        ]);

        $start = (int) ($validated['start'] ?? 0);
        $length = (int) ($validated['length'] ?? 10);
        $search = trim((string) data_get($validated, 'search.value', ''));
        $year = $validated['year'] ?? null;
        $condition = $validated['condition'] ?? null;
        $orderColumn = (int) data_get($validated, 'order.0.column', 12);
        $orderDirection = data_get($validated, 'order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderMap = [
            0 => 'customer_id',
            1 => 'street_name',
            2 => 'power_usage',
            3 => 'condition',
            4 => 'pole_type',
            5 => 'height',
            6 => 'ornament_type',
            7 => 'lamp_type',
            8 => 'lamp_status',
            9 => 'lamp_house_condition',
            10 => 'light_point_count',
            11 => 'kwh_meter',
            12 => 'created_at',
            13 => 'updated_at',
        ];

        $baseQuery = Pole::query();
        $filteredQuery = Pole::query()
            ->with(['createdBy:id,name,role,email', 'updatedBy:id,name,role,email'])
            ->search($search)
            ->when($year, fn ($query) => $query->whereYear('created_at', (int) $year))
            ->condition($condition);

        $recordsTotal = (clone $baseQuery)->count();
        $recordsFiltered = (clone $filteredQuery)->count();
        $sortColumn = $orderMap[$orderColumn] ?? 'created_at';

        if (! Schema::hasColumn('poles', $sortColumn)) {
            $sortColumn = 'created_at';
        }

        $poles = $filteredQuery
            ->orderBy($sortColumn, $orderDirection)
            ->when($sortColumn !== 'id', fn ($query) => $query->orderByDesc('id'))
            ->when($length !== -1, fn ($query) => $query->skip($start)->take($length))
            ->get();

        return response()->json([
            'draw' => (int) ($validated['draw'] ?? 0),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $poles->map(fn (Pole $pole) => $this->datatableRow($pole))->values(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $input = $this->validatedPoleInput($request);
        unset($input['photos'], $input['photo']);
        $input['created_by_id'] = $request->user()->id;
        $input['updated_by_id'] = $request->user()->id;

        $pole = Pole::query()->create($input);
        $this->storePolePhotos($request, $pole);

        $report = DailyReport::query()->create([
            'user_id' => $request->user()->id,
            'pole_id' => $pole->id,
            'report_date' => now()->toDateString(),
            'photo_url' => $pole->photo_url,
            'notes' => "Data pelanggan {$pole->customer_id} ditambahkan ke inventaris.",
        ]);
        $this->copyPolePhotosToReport($pole, $report);

        return back()->with('status', 'Data tiang berhasil ditambahkan.');
    }

    public function update(Request $request, Pole $pole): RedirectResponse
    {
        $input = $this->validatedPoleInput($request, $pole);
        unset($input['photos'], $input['photo']);
        $input['updated_by_id'] = $request->user()->id;

        $pole->update($input);
        $this->storePolePhotos($request, $pole);

        $report = DailyReport::query()->create([
            'user_id' => $request->user()->id,
            'pole_id' => $pole->id,
            'report_date' => now()->toDateString(),
            'photo_url' => $pole->photo_url,
            'notes' => "Data pelanggan {$pole->customer_id} diperbarui.",
        ]);
        $this->copyPolePhotosToReport($pole, $report);

        return back()->with('status', 'Data tiang berhasil diperbarui.');
    }

    public function destroy(Pole $pole): RedirectResponse
    {
        $customerId = $pole->customer_id;

        try {
            $pole->delete();
        } catch (\Throwable $exception) {
            report($exception);

            return back()->with('error', "Data {$customerId} gagal dihapus. Silakan coba lagi.");
        }

        return back()->with('status', "Data {$customerId} berhasil dihapus.");
    }

    public function destroyPhoto(PolePhoto $photo): RedirectResponse
    {
        $pole = $photo->pole;
        $deletedUrl = $photo->photo_url;

        $this->deleteStoredPhoto($deletedUrl);
        $photo->delete();

        if ($pole && $pole->photo_url === $deletedUrl) {
            $nextPhoto = $pole->photos()->first();
            $pole->forceFill(['photo_url' => $nextPhoto?->photo_url])->save();
        }

        return back()->with('status', 'Foto tiang berhasil dihapus.');
    }

    private function validatedPoleInput(Request $request, ?Pole $pole = null): array
    {
        $validated = $request->validate([
            'customer_id' => [
                'required',
                'string',
                'max:80',
                Rule::unique('poles', 'customer_id')->ignore($pole?->id),
            ],
            'street_name' => ['required', 'string', 'max:160'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'power_usage' => ['required', 'integer', 'min:1', 'max:100000'],
            'condition' => ['required', Rule::in(['keropos', 'miring', 'berkarat', 'kokoh'])],
            'pole_type' => ['required', Rule::in(['tiang_baja_galvanis', 'tiang_beton', 'menempel_tiang_pln'])],
            'height' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'ornament_type' => ['required', Rule::in(['single_arm', 'double_arm', 'high_mast'])],
            'lamp_type' => ['required', Rule::in(['led', 'hps', 'son_t', 'mercury', 'lainnya'])],
            'lamp_status' => ['required', Rule::in(['menyala', 'redup', 'berkedip', 'mati'])],
            'lamp_house_condition' => ['required', Rule::in(['pecah', 'kusam', 'kemasukan_air'])],
            'light_point_count' => ['required', 'integer', 'min:1', 'max:1000'],
            'kwh_meter' => ['required', Rule::in(['sudah_ada', 'belum'])],
            'ownership' => ['required', Rule::in(['dpu_kp', 'dishub', 'pln', 'lainnya'])],
            'photos' => ['sometimes', 'array', 'max:10'],
            'photos.*' => ['image', 'max:10240'],
            'photo' => ['sometimes', 'nullable', 'image', 'max:10240'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        return $validated;
    }

    private function storePolePhotos(Request $request, Pole $pole): void
    {
        $files = $request->file('photos', []);
        $files = is_array($files) ? $files : [$files];
        $files = array_filter($files);

        if ($request->hasFile('photo')) {
            $files[] = $request->file('photo');
        }

        foreach ($files as $file) {
            $path = ImageStorage::store($file, 'poles');
            $photoUrl = asset("storage/{$path}");

            $pole->photos()->create([
                'photo_url' => $photoUrl,
                'sort_order' => $pole->photos()->count(),
            ]);

            if (! $pole->photo_url) {
                $pole->forceFill(['photo_url' => $photoUrl])->save();
            }
        }
    }

    private function copyPolePhotosToReport(Pole $pole, DailyReport $report): void
    {
        $pole->loadMissing('photos');

        foreach ($pole->photos as $index => $photo) {
            $report->photos()->create([
                'photo_url' => $photo->photo_url,
                'sort_order' => $index,
            ]);
        }
    }

    private function datatableRow(Pole $pole): array
    {
        return [
            'customer' => '<p class="font-bold text-slate-100">'.e($pole->customer_id).'</p>',
            'street_name' => '<span class="pju-location-cell font-medium text-slate-300" title="'.e($pole->street_name).'">'.e($pole->street_name).'</span>',
            'power_usage' => e(number_format((int) $pole->power_usage)).' Watt',
            'condition' => '<span class="badge badge-'.e((string) $pole->condition).'">'.e($this->formatOption($pole->condition)).'</span>',
            'pole_type' => e($this->formatOption($pole->pole_type)),
            'height' => e($pole->height ? $pole->height.' m' : '-'),
            'ornament_type' => e($this->formatOption($pole->ornament_type)),
            'lamp_type' => e($this->formatOption($pole->lamp_type)),
            'lamp_status' => e($this->formatOption($pole->lamp_status)),
            'lamp_house_condition' => e($this->formatOption($pole->lamp_house_condition)),
            'light_point_count' => e($pole->light_point_count ? number_format((int) $pole->light_point_count) : '-'),
            'kwh_meter' => e($this->formatOption($pole->kwh_meter)),
            'created_at' => e(optional($pole->created_at)->format('H:i d-m-Y') ?: '-'),
            'created_by' => e($pole->createdBy?->name ?? '-'),
            'updated_at' => e(optional($pole->updated_at)->format('H:i d-m-Y') ?: '-'),
            'updated_by' => e($pole->updatedBy?->name ?? '-'),
            'actions' => '<div class="flex justify-end gap-2"><a class="btn btn-muted px-3 py-2" href="'.e(route('poles.show', $pole)).'"><i data-lucide="eye" class="icon"></i> Detail</a></div>',
        ];
    }

    private function formatOption(?string $value): string
    {
        return [
            'baik' => 'Kokoh',
            'rusak' => 'Keropos',
            'perbaikan' => 'Berkarat',
            'lurus' => 'Tiang Baja (Galvanis)',
            'lengkung' => 'Tiang Baja (Galvanis)',
            'tanpa_tiang' => 'Menempel Tiang PLN',
            'tunggal' => 'Single Arm',
            'ganda' => 'Double Arm',
            'dekoratif' => 'Single Arm',
            'sodium' => 'HPS (Sodium Tekanan Tinggi)',
            'hilang' => 'Kusam',
            'tiang_baja_galvanis' => 'Tiang Baja (Galvanis)',
            'tiang_beton' => 'Tiang Beton',
            'menempel_tiang_pln' => 'Menempel Tiang PLN',
            'single_arm' => 'Single Arm',
            'double_arm' => 'Double Arm',
            'high_mast' => 'High Mast',
            'keropos' => 'Keropos',
            'miring' => 'Miring',
            'berkarat' => 'Berkarat',
            'kokoh' => 'Kokoh',
            'led' => 'LED',
            'hps' => 'HPS (Sodium Tekanan Tinggi)',
            'son_t' => 'SON-T',
            'mercury' => 'Mercury',
            'lainnya' => 'Lainnya',
            'menyala' => 'Menyala',
            'redup' => 'Redup',
            'berkedip' => 'Berkedip',
            'mati' => 'Mati',
            'pecah' => 'Pecah',
            'kusam' => 'Kusam',
            'kemasukan_air' => 'Kemasukan Air',
            'sudah_ada' => 'Sudah Ada',
            'belum' => 'Belum',
        ][$value] ?? ucfirst(str_replace('_', ' ', (string) $value));
    }

    private function formatOwnership(?string $value): string
    {
        return [
            'dpu_kp' => 'DPU KP',
            'dishub' => 'DISHUB',
            'pln' => 'PLN',
            'lainnya' => 'Lainnya',
        ][$value] ?? (string) $value;
    }

    private function formatLampType(?string $value): string
    {
        return $this->formatOption($value);
    }

    private function deleteStoredPhoto(string $photoUrl): void
    {
        $path = parse_url($photoUrl, PHP_URL_PATH) ?: '';
        $storagePrefix = '/storage/';
        $position = strpos($path, $storagePrefix);

        if ($position === false) {
            return;
        }

        Storage::disk('public')->delete(rawurldecode(substr($path, $position + strlen($storagePrefix))));
    }
}
