<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DailyReport;
use App\Models\Pole;
use App\Models\PolePhoto;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PoleController extends Controller
{
    public function export(Request $request): Response
    {
        $validated = $request->validate([
            'query' => ['sometimes', 'nullable', 'string', 'max:120'],
            'condition' => ['sometimes', 'nullable', Rule::in(['baik', 'rusak', 'perbaikan'])],
        ]);

        $poles = Pole::query()
            ->with(['createdBy:id,name,role,email', 'updatedBy:id,name,role,email', 'photos'])
            ->search($validated['query'] ?? null)
            ->condition($validated['condition'] ?? null)
            ->latest()
            ->get();

        $headers = [
            'ID Pelanggan',
            'Nama Jalan',
            'Latitude',
            'Longitude',
            'Daya Watt',
            'Kondisi',
            'Jenis Tiang',
            'Tinggi',
            'Bentangan',
            'Pondasi',
            'Kepemilikan',
            'Trafo PLN',
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
                $pole->span_length,
                $this->formatOption($pole->foundation_type),
                $this->formatOwnership($pole->ownership),
                $pole->pln_transformer,
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
        return $request->validate([
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
            'condition' => ['required', Rule::in(['baik', 'rusak', 'perbaikan'])],
            'pole_type' => ['required', Rule::in(['lurus', 'lengkung', 'tanpa_tiang', 'lainnya'])],
            'height' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'span_length' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'foundation_type' => ['required', Rule::in(['beton', 'tanam', 'base_plate', 'lainnya'])],
            'ownership' => ['required', Rule::in(['dpu_kp', 'dishub', 'pln', 'lainnya'])],
            'pln_transformer' => ['nullable', 'string', 'max:160'],
            'photos' => ['sometimes', 'array', 'max:10'],
            'photos.*' => ['image', 'max:5120'],
            'photo' => ['sometimes', 'nullable', 'image', 'max:5120'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
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
            $path = $file->store('poles', 'public');
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

    private function formatOption(?string $value): string
    {
        return str_replace('_', ' ', (string) $value);
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
