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
            ->with(['user:id,name,role', 'pole:id,source_name,sub_name,customer_id,street_name'])
            ->latest()
            ->limit(8)
            ->get();

        $poleStats = Pole::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN `condition` = 'kokoh' THEN 1 ELSE 0 END) as kokoh")
            ->selectRaw("SUM(CASE WHEN `condition` = 'keropos' THEN 1 ELSE 0 END) as keropos")
            ->selectRaw("SUM(CASE WHEN `condition` = 'miring' THEN 1 ELSE 0 END) as miring")
            ->selectRaw("SUM(CASE WHEN `condition` = 'berkarat' THEN 1 ELSE 0 END) as berkarat")
            ->selectRaw("SUM(CASE WHEN `lamp_status` = 'menyala' THEN 1 ELSE 0 END) as lamp_menyala")
            ->selectRaw("SUM(CASE WHEN `lamp_status` = 'redup' THEN 1 ELSE 0 END) as lamp_redup")
            ->selectRaw("SUM(CASE WHEN `lamp_status` = 'berkedip' THEN 1 ELSE 0 END) as lamp_berkedip")
            ->selectRaw("SUM(CASE WHEN `lamp_status` = 'mati' THEN 1 ELSE 0 END) as lamp_mati")
            ->selectRaw('COALESCE(SUM(power_usage), 0) as daya')
            ->first();

        $stats = [
            'total' => (int) $poleStats->total,
            'kokoh' => (int) $poleStats->kokoh,
            'keropos' => (int) $poleStats->keropos,
            'miring' => (int) $poleStats->miring,
            'berkarat' => (int) $poleStats->berkarat,
            'lamp_menyala' => (int) $poleStats->lamp_menyala,
            'lamp_redup' => (int) $poleStats->lamp_redup,
            'lamp_berkedip' => (int) $poleStats->lamp_berkedip,
            'lamp_mati' => (int) $poleStats->lamp_mati,
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
