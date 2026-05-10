<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Pole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MapPageController extends Controller
{
    public function __invoke(Request $request): View
    {
        $query = trim((string) $request->query('query', ''));
        $condition = $request->query('condition');

        $poles = $this->filteredPoleQuery($query, $condition)
            ->with(['createdBy:id,name,role,email', 'updatedBy:id,name,role,email'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $dialogPoles = $poles->getCollection()->unique('id')->values();
        $user = $request->user();

        return view('pages.map.index', [
            'poles' => $poles,
            'dialogPoles' => $dialogPoles,
            'query' => $query,
            'condition' => $condition,
            'user' => $user,
            'canManagePoles' => in_array($user->role, ['superadmin', 'surveyor'], true),
        ]);
    }

    public function markers(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => ['sometimes', 'nullable', 'string', 'max:120'],
            'condition' => ['sometimes', 'nullable', Rule::in(['baik', 'rusak', 'perbaikan'])],
            'summary' => ['sometimes', 'boolean'],
        ]);
        $summary = $request->boolean('summary');

        $markers = $this->filteredPoleQuery(
            trim((string) ($validated['query'] ?? '')),
            $validated['condition'] ?? null
        )
            ->select($summary ? [
                'id',
                'customer_id',
                'street_name',
                'latitude',
                'longitude',
                'power_usage',
                'condition',
            ] : [
                'id',
                'customer_id',
                'street_name',
                'latitude',
                'longitude',
                'power_usage',
                'condition',
                'pole_type',
                'height',
                'span_length',
                'foundation_type',
                'ownership',
                'pln_transformer',
                'photo_url',
                'notes',
            ])
            ->latest()
            ->get();

        return response()->json([
            'data' => $markers,
        ]);
    }

    private function filteredPoleQuery(string $query, ?string $condition)
    {
        return Pole::query()->search($query)->condition($condition);
    }
}
