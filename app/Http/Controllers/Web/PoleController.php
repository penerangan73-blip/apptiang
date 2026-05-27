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
use Illuminate\Validation\ValidationException;

class PoleController extends Controller
{
    public function export(Request $request): Response
    {
        $validated = $request->validate([
            'query' => ['sometimes', 'nullable', 'string', 'max:120'],
            'search_columns' => ['sometimes', 'array', 'max:30'],
            'search_columns.*' => ['string', Rule::in(array_keys($this->searchColumnMap()))],
            'year' => ['sometimes', 'nullable', 'integer', 'min:2000', 'max:2100'],
            'condition' => ['sometimes', 'nullable', Rule::in(['keropos', 'miring', 'berkarat', 'kokoh', 'lainnya'])],
        ]);
        $searchColumns = $this->resolveSearchColumns($validated['search_columns'] ?? []);

        $poles = Pole::query()
            ->with(['createdBy:id,name,role,email', 'updatedBy:id,name,role,email', 'photos'])
            ->search($validated['query'] ?? null, $searchColumns)
            ->when($validated['year'] ?? null, fn ($query, $year) => $query->whereYear('created_at', (int) $year))
            ->condition($validated['condition'] ?? null)
            ->latest()
            ->get();

        $headers = [
            'Nomor Tiang Listrik',
            'Alamat',
            'Kelurahan',
            'Kecamatan',
            'Kabupaten',
            'Provinsi',
            'Latitude',
            'Longitude',
            'Daya Lampu',
            'Kondisi Tiang',
            'Jenis Tiang',
            'Tinggi Tiang',
            'Tipe Ornamen',
            'Jenis Lampu',
            'Kondisi Lampu',
            'Jenis Rumah Lampu',
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
                $pole->displayName(),
                $pole->addressLine(),
                $pole->village,
                $pole->district,
                $pole->regency,
                $pole->province,
                $pole->latitude,
                $pole->longitude,
                $pole->power_usage,
                $this->formatOption($pole->condition),
                $this->formatOption($pole->pole_type),
                $pole->height,
                $this->formatOption($pole->ornament_type),
                $this->formatLampType($pole->lamp_type),
                $this->formatOption($pole->lamp_status),
                $this->formatOption($pole->lamp_house_type),
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
            'search_columns' => ['sometimes', 'array', 'max:30'],
            'search_columns.*' => ['string', Rule::in(array_keys($this->searchColumnMap()))],
            'year' => ['sometimes', 'nullable', 'integer', 'min:2000', 'max:2100'],
            'condition' => ['sometimes', 'nullable', Rule::in(['keropos', 'miring', 'berkarat', 'kokoh', 'lainnya'])],
            'order.0.column' => ['sometimes', 'integer', 'min:0'],
            'order.0.dir' => ['sometimes', Rule::in(['asc', 'desc'])],
        ]);

        $start = (int) ($validated['start'] ?? 0);
        $length = (int) ($validated['length'] ?? 10);
        $search = trim((string) data_get($validated, 'search.value', ''));
        $searchColumns = $this->resolveSearchColumns($validated['search_columns'] ?? []);
        $year = $validated['year'] ?? null;
        $condition = $validated['condition'] ?? null;
        $canDeletePoles = $this->canDeletePoles($request);
        $orderColumn = (int) data_get($validated, 'order.0.column', $canDeletePoles ? 17 : 16);
        $orderDirection = data_get($validated, 'order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderMap = $canDeletePoles
            ? [
                1 => 'customer_id',
                2 => 'address',
                3 => 'village',
                4 => 'district',
                5 => 'regency',
                6 => 'power_usage',
                7 => 'condition',
                8 => 'pole_type',
                9 => 'height',
                10 => 'ornament_type',
                11 => 'lamp_type',
                12 => 'lamp_status',
                13 => 'lamp_house_type',
                14 => 'lamp_house_condition',
                15 => 'light_point_count',
                16 => 'kwh_meter',
                17 => 'created_at',
            ]
            : [
                0 => 'customer_id',
                1 => 'address',
                2 => 'village',
                3 => 'district',
                4 => 'regency',
                5 => 'power_usage',
                6 => 'condition',
                7 => 'pole_type',
                8 => 'height',
                9 => 'ornament_type',
                10 => 'lamp_type',
                11 => 'lamp_status',
                12 => 'lamp_house_type',
                13 => 'lamp_house_condition',
                14 => 'light_point_count',
                15 => 'kwh_meter',
                16 => 'created_at',
            ];

        $baseQuery = Pole::query();
        $filteredQuery = Pole::query()
            ->with(['createdBy:id,name,role,email', 'updatedBy:id,name,role,email'])
            ->search($search, $searchColumns)
            ->when($year, fn ($query) => $query->whereYear('created_at', (int) $year))
            ->condition($condition);

        $recordsTotal = (clone $baseQuery)->count();
        $recordsFiltered = (clone $filteredQuery)->count();
        $sortColumn = $orderMap[$orderColumn] ?? 'created_at';
        if ($sortColumn === 'address' && ! Schema::hasColumn('poles', 'address')) {
            $sortColumn = 'street_name';
        }

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
            'data' => $poles->map(fn (Pole $pole) => $this->datatableRow($pole, $canDeletePoles))->values(),
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
            'notes' => "Data pelanggan {$pole->displayName()} ditambahkan ke inventaris.",
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
            'notes' => "Data pelanggan {$pole->displayName()} diperbarui.",
        ]);
        $this->copyPolePhotosToReport($pole, $report);

        return back()->with('status', 'Data tiang berhasil diperbarui.');
    }

    public function destroy(Pole $pole): RedirectResponse
    {
        $customerId = $pole->displayName();

        try {
            $this->deletePoleWithAssets($pole);
        } catch (\Throwable $exception) {
            report($exception);

            return back()->with('error', "Data {$customerId} gagal dihapus. Silakan coba lagi.");
        }

        return back()->with('status', "Data {$customerId} berhasil dihapus.");
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:200'],
            'ids.*' => ['integer', 'distinct', 'exists:poles,id'],
        ]);

        $poles = Pole::query()
            ->with(['photos', 'reports.photos'])
            ->whereIn('id', $validated['ids'])
            ->get();

        try {
            foreach ($poles as $pole) {
                $this->deletePoleWithAssets($pole);
            }
        } catch (\Throwable $exception) {
            report($exception);

            return back()->with('error', 'Sebagian data gagal dihapus. Silakan coba lagi.');
        }

        return back()->with('status', $poles->count().' data tiang berhasil dihapus.');
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
            'route_type' => ['required', Rule::in(['sumber', 'sub'])],
            'sub_name' => ['required_if:route_type,sub', 'nullable', 'string', 'max:120'],
            'customer_id' => [
                'required',
                'string',
                'max:80',
                Rule::unique('poles', 'customer_id')->ignore($pole?->id),
            ],
            'street_name' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'address' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'village' => ['sometimes', 'nullable', 'string', 'max:120'],
            'district' => ['sometimes', 'nullable', 'string', 'max:120'],
            'regency' => ['sometimes', 'nullable', 'string', 'max:120'],
            'province' => ['sometimes', 'nullable', 'string', 'max:120'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'power_usage' => ['required', 'integer', 'min:1', 'max:100000'],
            'condition' => ['required', Rule::in(['keropos', 'miring', 'berkarat', 'kokoh', 'lainnya'])],
            'pole_type' => ['required', Rule::in(['tiang_baja_galvanis', 'tiang_beton', 'menempel_tiang_pln', 'menempel_pohon'])],
            'height' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'ornament_type' => ['required', Rule::in(['single_arm', 'double_arm', 'high_mast', 'lainnya'])],
            'lamp_type' => ['required', Rule::in(['led', 'hps', 'son_t', 'mercury', 'lainnya'])],
            'lamp_status' => ['required', Rule::in(['menyala', 'redup', 'berkedip', 'mati', 'rusak_pecah', 'tidak_ada_lampu'])],
            'lamp_house_type' => ['required', Rule::in(['caping', 'konvensional', 'tidak_ada'])],
            'lamp_house_condition' => ['required', Rule::in(['baik', 'rusak'])],
            'light_point_count' => ['required', 'integer', 'min:1', 'max:1000'],
            'kwh_meter' => ['required', Rule::in(['sudah_ada', 'belum'])],
            'ownership' => ['required', Rule::in(['dpu_kp', 'dishub', 'pln', 'lainnya'])],
            'photos' => ['sometimes', 'array', 'max:10'],
            'photos.*' => ['image', 'max:10240'],
            'photo' => ['sometimes', 'nullable', 'image', 'max:10240'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if (($validated['route_type'] ?? null) === 'sumber') {
            $validated['sub_name'] = null;
        }

        unset($validated['route_type']);

        return $this->normalizeAddressInput($validated);
    }

    private function normalizeAddressInput(array $validated): array
    {
        $address = trim((string) ($validated['address'] ?? $validated['street_name'] ?? ''));

        if ($address === '') {
            throw ValidationException::withMessages([
                'address' => 'Alamat wajib diisi.',
            ]);
        }

        $validated['address'] = $address;
        $validated['street_name'] = $address;

        foreach (['village', 'district', 'regency', 'province'] as $field) {
            $validated[$field] = trim((string) ($validated[$field] ?? '')) ?: null;
        }

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

    private function datatableRow(Pole $pole, bool $canDeletePoles): array
    {
        $actions = '<a class="btn btn-muted px-3 py-2" href="'.e(route('poles.show', $pole)).'"><i data-lucide="eye" class="icon"></i> Detail</a>';

        if ($canDeletePoles) {
            $actions .= '<button type="button" class="btn btn-danger px-3 py-2" data-open-dialog="pole-datatable-delete-dialog" data-delete-url="'.e(route('poles.destroy', $pole)).'" data-delete-code="'.e($pole->displayName()).'" data-delete-street="'.e($pole->locationLabel()).'"><i data-lucide="trash-2" class="icon"></i> Hapus</button>';
        }

        $row = [
            'customer' => '<p class="min-w-44 whitespace-nowrap font-bold text-slate-100">'.e($pole->displayName()).'</p>',
            'street_name' => '<span class="pju-location-cell font-medium text-slate-300" title="'.e($pole->locationLabel()).'">'.e($pole->addressLine()).'</span>',
            'village' => e($pole->village ?: '-'),
            'district' => e($pole->district ?: '-'),
            'regency' => e($pole->regency ?: '-'),
            'power_usage' => e(number_format((int) $pole->power_usage)).' Watt',
            'condition' => '<span class="badge badge-'.e((string) $pole->condition).'">'.e($this->formatOption($pole->condition)).'</span>',
            'pole_type' => e($this->formatOption($pole->pole_type)),
            'height' => e($pole->height ? $pole->height.' m' : '-'),
            'ornament_type' => e($this->formatOption($pole->ornament_type)),
            'lamp_type' => e($this->formatOption($pole->lamp_type)),
            'lamp_status' => e($this->formatOption($pole->lamp_status)),
            'lamp_house_type' => e($this->formatOption($pole->lamp_house_type)),
            'lamp_house_condition' => e($this->formatOption($pole->lamp_house_condition)),
            'light_point_count' => e($pole->light_point_count ? number_format((int) $pole->light_point_count) : '-'),
            'kwh_meter' => e($this->formatOption($pole->kwh_meter)),
            'created_at' => e(optional($pole->created_at)->format('H:i d-m-Y') ?: '-'),
            'created_by' => e($pole->createdBy?->name ?? '-'),
            'updated_at' => e(optional($pole->updated_at)->format('H:i d-m-Y') ?: '-'),
            'updated_by' => e($pole->updatedBy?->name ?? '-'),
            'actions' => '<div class="flex justify-end gap-2">'.$actions.'</div>',
        ];

        if ($canDeletePoles) {
            $row = [
                'select' => '<input type="checkbox" class="pju-pole-select size-4 rounded border-white/20 bg-slate-900 text-cyan-500" value="'.e($pole->id).'" aria-label="Pilih '.e($pole->displayName()).'">',
            ] + $row;
        }

        return $row;
    }

    private function canDeletePoles(Request $request): bool
    {
        return in_array($request->user()?->role, ['superadmin', 'surveyor'], true);
    }

    private function searchColumnMap(): array
    {
        return [
            'customer_id' => ['customer_id', 'sub_name'],
            'address' => ['address', 'street_name'],
            'village' => ['village'],
            'district' => ['district'],
            'regency' => ['regency'],
            'province' => ['province'],
            'latitude' => ['latitude'],
            'longitude' => ['longitude'],
            'power_usage' => ['power_usage'],
            'condition' => ['condition'],
            'pole_type' => ['pole_type'],
            'height' => ['height'],
            'ornament_type' => ['ornament_type'],
            'lamp_type' => ['lamp_type'],
            'lamp_status' => ['lamp_status'],
            'lamp_house_type' => ['lamp_house_type'],
            'lamp_house_condition' => ['lamp_house_condition'],
            'light_point_count' => ['light_point_count'],
            'kwh_meter' => ['kwh_meter'],
            'ownership' => ['ownership'],
            'created_at' => ['created_at'],
            'updated_at' => ['updated_at'],
            'created_by' => ['created_by'],
            'updated_by' => ['updated_by'],
            'notes' => ['notes'],
        ];
    }

    private function resolveSearchColumns(array $selectedColumns): ?array
    {
        $selectedColumns = array_values(array_filter(array_unique($selectedColumns)));

        if ($selectedColumns === []) {
            return null;
        }

        $map = $this->searchColumnMap();

        return collect($selectedColumns)
            ->flatMap(fn (string $column) => $map[$column] ?? [])
            ->unique()
            ->values()
            ->all();
    }

    private function formatOption(?string $value): string
    {
        return [
            'baik' => 'Baik',
            'rusak' => 'Rusak',
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
            'menempel_pohon' => 'Menempel Pohon',
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
            'rusak_pecah' => 'Rusak Pecah',
            'tidak_ada_lampu' => 'Tidak Ada Lampu',
            'caping' => 'Caping',
            'konvensional' => 'Konvensional',
            'tidak_ada' => 'Tidak Ada',
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

    private function deletePoleWithAssets(Pole $pole): void
    {
        $pole->loadMissing(['photos', 'reports.photos']);

        $photoUrls = collect([$pole->photo_url])
            ->merge($pole->photos->pluck('photo_url'))
            ->merge($pole->reports->pluck('photo_url'))
            ->merge($pole->reports->flatMap(fn (DailyReport $report) => $report->photos->pluck('photo_url')))
            ->filter()
            ->unique()
            ->values();

        $pole->delete();

        $photoUrls->each(fn (string $photoUrl) => $this->deleteStoredPhoto($photoUrl));
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
