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
use Illuminate\Validation\ValidationException;

class PoleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => ['sometimes', 'nullable', 'string', 'max:120'],
            'condition' => ['sometimes', 'nullable', Rule::in(['keropos', 'miring', 'berkarat', 'kokoh', 'lainnya'])],
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

    public function verify(Request $request, Pole $pole): JsonResponse
    {
        $validated = $request->validate([
            'is_verified' => ['required', 'boolean'],
        ]);

        $isVerified = (bool) $validated['is_verified'];

        $pole->forceFill([
            'is_verified' => $isVerified,
            'verified_by_id' => $isVerified ? $request->user()->id : null,
            'verified_at' => $isVerified ? now() : null,
        ])->save();

        return response()->json([
            'data' => $pole->load(['createdBy:id,name,role,email', 'updatedBy:id,name,role,email', 'verifiedBy:id,name,role,email', 'photos']),
        ]);
    }

    private function validatedPoleInput(Request $request, ?Pole $pole = null): array
    {
        $routeType = (string) $request->input('route_type', '');
        $subNameForUnique = $this->normalizedSubNameForRequest($request, $pole);
        $customerIdRules = [
            'required',
            'string',
            'max:80',
        ];

        if ($routeType === 'sub') {
            $customerIdRules[] = Rule::exists('poles', 'customer_id');
        }

        $customerIdRules[] = Rule::unique('poles', 'customer_id')
            ->where(fn ($query) => $query->where('sub_name', $subNameForUnique))
            ->ignore($pole?->id);

        $validated = $request->validate([
            'route_type' => ['sometimes', Rule::in(['sumber', 'sub'])],
            'sub_name' => ['sometimes', 'nullable', 'string', 'max:120'],
            'customer_id' => $customerIdRules,
            'street_name' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'address' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'village' => ['sometimes', 'nullable', 'string', 'max:120'],
            'district' => ['sometimes', 'nullable', 'string', 'max:120'],
            'regency' => ['sometimes', 'nullable', 'string', 'max:120'],
            'province' => ['sometimes', 'nullable', 'string', 'max:120'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'power_usage' => ['required', 'integer', 'min:0', 'max:100000'],
            'condition' => ['required', Rule::in(['keropos', 'miring', 'berkarat', 'kokoh', 'lainnya'])],
            'pole_type' => ['required', Rule::in(['tiang_baja_galvanis', 'tiang_beton', 'tiang_cor', 'tiang_kayu', 'tiang_bambu', 'menempel_tiang_pln', 'menempel_tembok', 'menempel_pohon', 'tanpa_penyangga'])],
            'height' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'ornament_type' => ['required', Rule::in(['single_arm', 'double_arm', 'high_mast', 'lainnya'])],
            'lamp_type' => ['required', Rule::in(['led', 'cfl', 'hps', 'son_t', 'mercury', 'lainnya'])],
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

        if (($validated['lamp_status'] ?? null) === 'tidak_ada_lampu') {
            $validated['power_usage'] = 0;
        } elseif ((int) ($validated['power_usage'] ?? 0) < 1) {
            throw ValidationException::withMessages([
                'power_usage' => 'Daya lampu minimal 1 watt kecuali kondisi lampu Tidak Ada Lampu.',
            ]);
        }

        $validated['sub_name'] = $subNameForUnique;
        unset($validated['route_type']);

        return $this->normalizeAddressInput($validated);
    }

    private function normalizedSubNameForRequest(Request $request, ?Pole $pole = null): string
    {
        if ($request->input('route_type') !== 'sub') {
            return trim((string) $request->input('sub_name', ''));
        }

        if ($pole && trim((string) $pole->sub_name) !== '') {
            return trim((string) $pole->sub_name);
        }

        $subName = trim((string) $request->input('sub_name', ''));

        if ($subName !== '') {
            return $subName;
        }

        return Pole::nextSubNameForCustomer(trim((string) $request->input('customer_id', '')));
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
