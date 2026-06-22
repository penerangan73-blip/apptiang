<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Pole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PolePageController extends Controller
{
    public function __invoke(Request $request): View
    {
        $query = trim((string) $request->query('query', ''));
        $condition = $request->query('condition');

        $allPoles = Pole::query()
            ->select($this->poleColumns(['id', 'sub_name', 'customer_id', 'street_name', 'address', 'village', 'district', 'regency', 'province']))
            ->latest()
            ->get();

        $sourcePoles = collect();

        $years = Pole::query()
            ->whereNotNull('created_at')
            ->latest('created_at')
            ->pluck('created_at')
            ->map(fn ($date) => optional($date)->year)
            ->filter()
            ->unique()
            ->values();
        $attributeOptions = [
            'village' => $this->distinctPoleOptions('village'),
            'district' => $this->distinctPoleOptions('district'),
            'province' => $this->distinctPoleOptions('province'),
        ];

        $user = $request->user();

        return view('pages.poles.index', [
            'allPoles' => $allPoles,
            'sourcePoles' => $sourcePoles,
            'years' => $years,
            'attributeOptions' => $attributeOptions,
            'query' => $query,
            'year' => $request->query('year'),
            'condition' => $condition,
            'user' => $user,
            'canCreatePoles' => in_array($user->role, ['superadmin', 'surveyor'], true),
            'canManagePoles' => in_array($user->role, ['superadmin', 'surveyor', 'verifikator'], true),
        ]);
    }

    private function poleColumns(array $columns): array
    {
        $existingColumns = Schema::getColumnListing('poles');

        return array_values(array_intersect($columns, $existingColumns));
    }

    private function distinctPoleOptions(string $column)
    {
        if (! Schema::hasColumn('poles', $column)) {
            return collect();
        }

        return Pole::query()
            ->whereNotNull($column)
            ->where($column, '<>', '')
            ->orderBy($column)
            ->distinct()
            ->pluck($column)
            ->values();
    }
}
