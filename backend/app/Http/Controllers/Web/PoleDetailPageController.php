<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Pole;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PoleDetailPageController extends Controller
{
    public function __invoke(Request $request, Pole $pole): View
    {
        $pole->load([
            'createdBy:id,name,role,email',
            'updatedBy:id,name,role,email',
        ]);

        $reports = $pole->reports()
            ->with('user:id,name,role')
            ->latest('report_date')
            ->latest()
            ->paginate(8)
            ->withQueryString();

        $user = $request->user();

        return view('pages.poles.show', [
            'pole' => $pole,
            'reports' => $reports,
            'user' => $user,
            'canManagePoles' => in_array($user->role, ['superadmin', 'surveyor'], true),
        ]);
    }
}
