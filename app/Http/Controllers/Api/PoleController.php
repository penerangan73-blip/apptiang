<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyReport;
use App\Models\Pole;
use App\Support\ImageStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PoleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => ['sometimes', 'nullable', 'string', 'max:120'],
            'condition' => ['sometimes', 'nullable', Rule::in(['keropos', 'miring', 'berkarat', 'kokoh'])],
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
            'notes' => "Data pelanggan {$pole->displayName()} ditambahkan ke inventaris.",
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
            'notes' => "Data pelanggan {$pole->displayName()} diperbarui oleh surveyor.",
        ]);
        $this->copyPolePhotosToReport($pole, $report);

        return response()->json([
            'data' => $pole->load(['createdBy:id,name,role,email', 'updatedBy:id,name,role,email', 'photos']),
        ]);
    }

    public function destroy(Pole $pole): JsonResponse
    {
        $this->deletePoleWithAssets($pole);

        return response()->json([
            'message' => 'Data tiang berhasil dihapus.',
        ]);
    }

    private function validatedPoleInput(Request $request, ?Pole $pole = null): array
    {
        $validated = $request->validate([
            'source_name' => ['sometimes', 'nullable', 'string', 'max:120'],
            'sub_name' => ['sometimes', 'nullable', 'string', 'max:120'],
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
            'lamp_status' => ['required', Rule::in(['menyala', 'redup', 'berkedip', 'mati', 'rusak_pecah', 'tidak_ada_lampu'])],
            'lamp_house_type' => ['sometimes', Rule::in(['caping', 'konvensional'])],
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
