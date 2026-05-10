<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DailyReport;
use App\Models\Pole;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $recentPoles = Pole::query()
            ->with(['createdBy:id,name,role,email', 'updatedBy:id,name,role,email'])
            ->latest()
            ->limit(5)
            ->get();

        $reports = DailyReport::query()
            ->with(['user:id,name,role', 'pole:id,customer_id,street_name'])
            ->latest()
            ->limit(8)
            ->get();

        $poleStats = Pole::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN `condition` = 'baik' THEN 1 ELSE 0 END) as baik")
            ->selectRaw("SUM(CASE WHEN `condition` = 'rusak' THEN 1 ELSE 0 END) as rusak")
            ->selectRaw("SUM(CASE WHEN `condition` = 'perbaikan' THEN 1 ELSE 0 END) as perbaikan")
            ->selectRaw('COALESCE(SUM(power_usage), 0) as daya')
            ->first();

        $stats = [
            'total' => (int) $poleStats->total,
            'baik' => (int) $poleStats->baik,
            'rusak' => (int) $poleStats->rusak,
            'perbaikan' => (int) $poleStats->perbaikan,
            'daya' => (int) $poleStats->daya,
        ];

        return view('pages.dashboard.index', [
            'recentPoles' => $recentPoles,
            'reports' => $reports,
            'stats' => $stats,
            'user' => $request->user(),
            'canManagePoles' => in_array($request->user()->role, ['superadmin', 'surveyor'], true),
        ]);
    }
}
