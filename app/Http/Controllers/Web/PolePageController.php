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

        $years = Pole::query()
            ->whereNotNull('created_at')
            ->latest('created_at')
            ->pluck('created_at')
            ->map(fn ($date) => optional($date)->year)
            ->filter()
            ->unique()
            ->values();

        $user = $request->user();

        return view('pages.poles.index', [
            'allPoles' => $allPoles,
            'years' => $years,
            'query' => $query,
            'year' => $request->query('year'),
            'condition' => $condition,
            'user' => $user,
            'canManagePoles' => in_array($user->role, ['superadmin', 'surveyor'], true),
        ]);
    }

    private function poleColumns(array $columns): array
    {
        $existingColumns = Schema::getColumnListing('poles');

        return array_values(array_intersect($columns, $existingColumns));
    }
}
