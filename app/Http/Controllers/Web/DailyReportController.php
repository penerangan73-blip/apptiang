<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DailyReport;
use App\Models\DailyReportPhoto;
use App\Models\Pole;
use App\Support\ImageStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DailyReportController extends Controller
{
    public function store(Request $request): RedirectResponse
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

        return back()->with('status', 'Laporan kerusakan berhasil disimpan.');
    }

    public function destroyPhoto(DailyReportPhoto $photo): RedirectResponse
    {
        $report = $photo->report;
        $deletedUrl = $photo->photo_url;

        $this->deleteStoredPhoto($deletedUrl);
        $photo->delete();

        if ($report && $report->photo_url === $deletedUrl) {
            $nextPhoto = $report->photos()->first();
            $report->forceFill(['photo_url' => $nextPhoto?->photo_url])->save();
        }

        return back()->with('status', 'Foto laporan berhasil dihapus.');
    }

    private function deleteStoredPhoto(string $photoUrl): void
    {
        $path = parse_url($photoUrl, PHP_URL_PATH) ?: '';
        $storagePrefix = '/storage/';
        $position = strpos($path, $storagePrefix);

        if ($position === false) {
            return;
        }

        Storage::disk('public')->delete(rawurldecode(substr($path, $position + strlen($storagePrefix))));
    }
}
