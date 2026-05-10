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
            ->with(['user:id,name,role', 'pole:id,customer_id,street_name'])
            ->when($date, fn ($query, string $date) => $query->whereDate('report_date', $date))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.reports.index', [
            'date' => $date,
            'reports' => $reports,
            'user' => $request->user(),
        ]);
    }
}
