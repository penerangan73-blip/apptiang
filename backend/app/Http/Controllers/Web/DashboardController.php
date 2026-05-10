<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DailyReport;
use App\Models\Pole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $query = trim((string) $request->query('query', ''));
        $condition = $request->query('condition');

        $poles = Pole::query()
            ->with(['createdBy:id,name,role,email', 'updatedBy:id,name,role,email'])
            ->when($query !== '', function ($builder) use ($query) {
                $builder->where(function ($builder) use ($query) {
                    $builder
                        ->where('street_name', 'like', "%{$query}%")
                        ->orWhere('customer_id', 'like', "%{$query}%");
                });
            })
            ->when(in_array($condition, ['baik', 'rusak', 'perbaikan'], true), fn ($builder) => $builder->where('condition', $condition))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $allPoles = Pole::query()
            ->with(['createdBy:id,name,role,email', 'updatedBy:id,name,role,email'])
            ->latest()
            ->get();

        $reports = DailyReport::query()
            ->with(['user:id,name,role', 'pole:id,customer_id,street_name'])
            ->latest()
            ->limit(8)
            ->get();

        $stats = [
            'total' => Pole::query()->count(),
            'baik' => Pole::query()->where('condition', 'baik')->count(),
            'rusak' => Pole::query()->where('condition', 'rusak')->count(),
            'perbaikan' => Pole::query()->where('condition', 'perbaikan')->count(),
            'daya' => Pole::query()->sum('power_usage'),
        ];

        $dialogPoles = $allPoles
            ->take(5)
            ->merge($poles->getCollection())
            ->unique('id')
            ->values();

        return view('dashboard', [
            'allPoles' => $allPoles,
            'condition' => $condition,
            'dialogPoles' => $dialogPoles,
            'poles' => $poles,
            'query' => $query,
            'reports' => $reports,
            'stats' => $stats,
            'users' => Auth::user()?->role === 'superadmin' ? User::query()->orderBy('name')->get() : collect(),
        ]);
    }
}
