<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DailyReport;
use App\Models\Pole;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            ->with(['createdBy:id,name,role,email', 'updatedBy:id,name,role,email'])
            ->when($validated['query'] ?? null, function ($query, string $term) {
                $query->where(function ($query) use ($term) {
                    $query
                        ->where('street_name', 'like', "%{$term}%")
                        ->orWhere('customer_id', 'like', "%{$term}%");
                });
            })
            ->when($validated['condition'] ?? null, fn ($query, string $condition) => $query->where('condition', $condition))
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
                $pole->photo_url,
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
        $input = $this->storePolePhoto($request, $input);
        $input['created_by_id'] = $request->user()->id;
        $input['updated_by_id'] = $request->user()->id;

        $pole = Pole::query()->create($input);

        DailyReport::query()->create([
            'user_id' => $request->user()->id,
            'pole_id' => $pole->id,
            'report_date' => now()->toDateString(),
            'notes' => "Data pelanggan {$pole->customer_id} ditambahkan ke inventaris.",
        ]);

        return back()->with('status', 'Data tiang berhasil ditambahkan.');
    }

    public function update(Request $request, Pole $pole): RedirectResponse
    {
        $input = $this->validatedPoleInput($request, $pole);
        $input = $this->storePolePhoto($request, $input);
        $input['updated_by_id'] = $request->user()->id;

        $pole->update($input);

        DailyReport::query()->create([
            'user_id' => $request->user()->id,
            'pole_id' => $pole->id,
            'report_date' => now()->toDateString(),
            'notes' => "Data pelanggan {$pole->customer_id} diperbarui.",
        ]);

        return back()->with('status', 'Data tiang berhasil diperbarui.');
    }

    public function destroy(Pole $pole): RedirectResponse
    {
        $pole->delete();

        return back()->with('status', 'Data tiang berhasil dihapus.');
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
            'photo' => ['sometimes', 'nullable', 'image', 'max:5120'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function storePolePhoto(Request $request, array $input): array
    {
        unset($input['photo']);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('poles', 'public');
            $input['photo_url'] = asset("storage/{$path}");
        }

        return $input;
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
}
