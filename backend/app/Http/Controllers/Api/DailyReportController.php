<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyReport;
use App\Models\Pole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DailyReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['sometimes', 'nullable', 'date_format:Y-m-d'],
        ]);

        $reports = DailyReport::query()
            ->when($validated['date'] ?? null, fn ($query, string $date) => $query->whereDate('report_date', $date))
            ->latest()
            ->get();

        return response()->json([
            'data' => $reports,
        ]);
    }

    public function store(Request $request): JsonResponse
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

        $report = DailyReport::query()->create([
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

        return response()->json([
            'data' => $report,
        ], 201);
    }
}
