<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DailyReport;
use App\Models\Pole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DailyReportController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $input = $request->validate([
            'pole_id' => ['required', 'exists:poles,id'],
            'notes' => ['required', 'string', 'max:2000'],
            'photo' => ['sometimes', 'nullable', 'image', 'max:5120'],
        ]);

        $photoUrl = null;

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('reports', 'public');
            $photoUrl = asset("storage/{$path}");
        }

        DailyReport::query()->create([
            'user_id' => $request->user()->id,
            'pole_id' => $input['pole_id'],
            'report_date' => now()->toDateString(),
            'photo_url' => $photoUrl,
            'notes' => $input['notes'],
        ]);

        Pole::query()
            ->whereKey($input['pole_id'])
            ->update([
                'condition' => 'rusak',
                'updated_by_id' => $request->user()->id,
            ]);

        return back()->with('status', 'Laporan kerusakan berhasil disimpan.');
    }
}
