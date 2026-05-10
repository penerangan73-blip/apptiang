<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyReport;
use App\Models\Pole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PoleController extends Controller
{
    public function index(Request $request): JsonResponse
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

        return response()->json([
            'data' => $poles,
        ]);
    }

    public function store(Request $request): JsonResponse
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

        return response()->json([
            'data' => $pole->load(['createdBy:id,name,role,email', 'updatedBy:id,name,role,email', 'photos']),
        ], 201);
    }

    public function update(Request $request, Pole $pole): JsonResponse
    {
        $input = $this->validatedPoleInput($request, $pole);
        unset($input['photos'], $input['photo']);
        $input['updated_by_id'] = $request->user()->id;

        $pole->update($input);
        $this->storePolePhotos($request, $pole);
        $pole->refresh();

        $report = DailyReport::query()->create([
            'user_id' => $request->user()->id,
            'pole_id' => $pole->id,
            'report_date' => now()->toDateString(),
            'photo_url' => $pole->photo_url,
            'notes' => "Data pelanggan {$pole->customer_id} diperbarui oleh surveyor.",
        ]);
        $this->copyPolePhotosToReport($pole, $report);

        return response()->json([
            'data' => $pole->load(['createdBy:id,name,role,email', 'updatedBy:id,name,role,email', 'photos']),
        ]);
    }

    public function destroy(Pole $pole): JsonResponse
    {
        $pole->delete();

        return response()->json([
            'message' => 'Data tiang berhasil dihapus.',
        ]);
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
}
