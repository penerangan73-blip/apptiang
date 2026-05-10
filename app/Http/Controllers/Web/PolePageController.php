<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Pole;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PolePageController extends Controller
{
    public function __invoke(Request $request): View
    {
        $query = trim((string) $request->query('query', ''));
        $condition = $request->query('condition');

        $poles = Pole::query()
            ->with(['createdBy:id,name,role,email', 'updatedBy:id,name,role,email'])
            ->search($query)
            ->condition($condition)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $allPoles = Pole::query()
            ->select(['id', 'customer_id', 'street_name'])
            ->latest()
            ->get();

        $dialogPoles = $poles->getCollection()
            ->unique('id')
            ->values();

        $user = $request->user();

        return view('pages.poles.index', [
            'poles' => $poles,
            'allPoles' => $allPoles,
            'dialogPoles' => $dialogPoles,
            'query' => $query,
            'condition' => $condition,
            'user' => $user,
            'canManagePoles' => in_array($user->role, ['superadmin', 'surveyor'], true),
        ]);
    }
}
