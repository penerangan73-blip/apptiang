<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyReport;
use App\Models\Pole;
use App\Support\ImageStorage;
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
            ->with('photos')
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
            'photos' => ['sometimes', 'array', 'max:10'],
            'photos.*' => ['image', 'max:10240'],
            'photo' => ['sometimes', 'nullable', 'image', 'max:10240'],
        ]);

        $files = $request->file('photos', []);
        $files = is_array($files) ? $files : [$files];
        $files = array_filter($files);

        if ($request->hasFile('photo')) {
            $files[] = $request->file('photo');
        }

        $report = DailyReport::query()->create([
            'user_id' => $request->user()->id,
            'pole_id' => $input['pole_id'],
            'report_date' => now()->toDateString(),
            'photo_url' => null,
            'notes' => $input['notes'],
        ]);

        foreach ($files as $file) {
            $path = ImageStorage::store($file, 'reports');
            $photoUrl = asset("storage/{$path}");

            $report->photos()->create([
                'photo_url' => $photoUrl,
                'sort_order' => $report->photos()->count(),
            ]);

            if (! $report->photo_url) {
                $report->forceFill(['photo_url' => $photoUrl])->save();
            }
        }

        Pole::query()
            ->whereKey($input['pole_id'])
            ->update([
                'condition' => 'keropos',
                'updated_by_id' => $request->user()->id,
            ]);

        return response()->json([
            'data' => $report->load('photos'),
        ], 201);
    }
}
