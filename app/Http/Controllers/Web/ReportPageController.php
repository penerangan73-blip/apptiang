<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DailyReport;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportPageController extends Controller
{
    public function __invoke(Request $request): View
    {
        $date = $request->query('date');

        $reports = DailyReport::query()
            ->with(['user:id,name,role', 'pole:id,sub_name,customer_id,street_name,photo_url', 'pole.photos', 'photos'])
            ->when($date, fn ($query, string $date) => $query->whereDate('report_date', $date))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.reports.index', [
            'date' => $date,
            'reports' => $reports,
            'user' => $request->user(),
            'canManagePoles' => in_array($request->user()->role, ['superadmin', 'surveyor'], true),
        ]);
    }
}
