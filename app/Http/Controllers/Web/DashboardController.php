<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DailyReport;
use App\Models\Pole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
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
            ->with(['user:id,name,role', 'pole' => fn ($query) => $query->select($this->poleColumns([
                'id',
                'sub_name',
                'customer_id',
                'street_name',
                'address',
                'village',
                'district',
                'regency',
                'province',
            ]))])
            ->latest()
            ->limit(8)
            ->get();

        $poleStats = Pole::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN `sub_name` IS NULL OR `sub_name` = '' THEN 1 ELSE 0 END) as sumber")
            ->selectRaw("SUM(CASE WHEN `sub_name` IS NOT NULL AND `sub_name` <> '' THEN 1 ELSE 0 END) as sub")
            ->selectRaw("SUM(CASE WHEN `condition` = 'kokoh' THEN 1 ELSE 0 END) as kokoh")
            ->selectRaw("SUM(CASE WHEN `condition` = 'keropos' THEN 1 ELSE 0 END) as keropos")
            ->selectRaw("SUM(CASE WHEN `condition` = 'miring' THEN 1 ELSE 0 END) as miring")
            ->selectRaw("SUM(CASE WHEN `condition` = 'berkarat' THEN 1 ELSE 0 END) as berkarat")
            ->selectRaw("SUM(CASE WHEN `condition` = 'lainnya' THEN 1 ELSE 0 END) as lainnya")
            ->selectRaw("SUM(CASE WHEN `lamp_status` = 'menyala' THEN 1 ELSE 0 END) as lamp_menyala")
            ->selectRaw("SUM(CASE WHEN `lamp_status` = 'redup' THEN 1 ELSE 0 END) as lamp_redup")
            ->selectRaw("SUM(CASE WHEN `lamp_status` = 'berkedip' THEN 1 ELSE 0 END) as lamp_berkedip")
            ->selectRaw("SUM(CASE WHEN `lamp_status` = 'mati' THEN 1 ELSE 0 END) as lamp_mati")
            ->selectRaw("SUM(CASE WHEN `lamp_status` = 'rusak_pecah' THEN 1 ELSE 0 END) as lamp_rusak_pecah")
            ->selectRaw("SUM(CASE WHEN `lamp_status` = 'tidak_ada_lampu' THEN 1 ELSE 0 END) as lamp_tidak_ada_lampu")
            ->selectRaw('COALESCE(SUM(power_usage), 0) as daya')
            ->first();

        $stats = [
            'total' => (int) $poleStats->total,
            'sumber' => (int) $poleStats->sumber,
            'sub' => (int) $poleStats->sub,
            'kokoh' => (int) $poleStats->kokoh,
            'keropos' => (int) $poleStats->keropos,
            'miring' => (int) $poleStats->miring,
            'berkarat' => (int) $poleStats->berkarat,
            'lainnya' => (int) $poleStats->lainnya,
            'lamp_menyala' => (int) $poleStats->lamp_menyala,
            'lamp_redup' => (int) $poleStats->lamp_redup,
            'lamp_berkedip' => (int) $poleStats->lamp_berkedip,
            'lamp_mati' => (int) $poleStats->lamp_mati,
            'lamp_rusak_pecah' => (int) $poleStats->lamp_rusak_pecah,
            'lamp_tidak_ada_lampu' => (int) $poleStats->lamp_tidak_ada_lampu,
            'daya' => (int) $poleStats->daya,
        ];

        $sourcePoles = collect();

        return view('pages.dashboard.index', [
            'recentPoles' => $recentPoles,
            'sourcePoles' => $sourcePoles,
            'reports' => $reports,
            'stats' => $stats,
            'user' => $request->user(),
            'canCreatePoles' => in_array($request->user()->role, ['superadmin', 'surveyor'], true),
            'canManagePoles' => in_array($request->user()->role, ['superadmin', 'surveyor', 'verifikator'], true),
        ]);
    }

    private function poleColumns(array $columns): array
    {
        $existingColumns = Schema::getColumnListing('poles');

        return array_values(array_intersect($columns, $existingColumns));
    }
}
