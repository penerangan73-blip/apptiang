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
            ->with(['createdBy:id,name,role,email', 'updatedBy:id,name,role,email'])
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

        return response()->json([
            'data' => $pole->load(['createdBy:id,name,role,email', 'updatedBy:id,name,role,email']),
        ], 201);
    }

    public function update(Request $request, Pole $pole): JsonResponse
    {
        $input = $this->validatedPoleInput($request, $pole);
        $input = $this->storePolePhoto($request, $input);
        $input['updated_by_id'] = $request->user()->id;

        $pole->update($input);
        $pole->refresh();

        DailyReport::query()->create([
            'user_id' => $request->user()->id,
            'pole_id' => $pole->id,
            'report_date' => now()->toDateString(),
            'notes' => "Data pelanggan {$pole->customer_id} diperbarui oleh surveyor.",
        ]);

        return response()->json([
            'data' => $pole->load(['createdBy:id,name,role,email', 'updatedBy:id,name,role,email']),
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
}
