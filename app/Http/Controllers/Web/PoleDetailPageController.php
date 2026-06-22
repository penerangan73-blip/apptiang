<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Pole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PoleDetailPageController extends Controller
{
    public function __invoke(Request $request, Pole $pole): View
    {
        $pole->load([
            'createdBy:id,name,role,email',
            'updatedBy:id,name,role,email',
            'verifiedBy:id,name,role,email',
            'photos',
        ]);

        $reports = $pole->reports()
            ->with(['user:id,name,role', 'photos'])
            ->latest('report_date')
            ->latest()
            ->paginate(8)
            ->withQueryString();

        $user = $request->user();
        $sourcePoles = collect();

        return view('pages.poles.show', [
            'pole' => $pole,
            'reports' => $reports,
            'sourcePoles' => $sourcePoles,
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
}
