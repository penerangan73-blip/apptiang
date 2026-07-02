<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Pole;
use App\Models\PoleComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PoleCommentController extends Controller
{
    public function store(Request $request, Pole $pole): RedirectResponse
    {
        $validated = $request->validate([
            'comment' => ['required', 'string', 'max:2000'],
        ]);

        PoleComment::query()->create([
            'pole_id' => $pole->id,
            'user_id' => $request->user()->id,
            'comment' => $validated['comment'],
        ]);

        return redirect()
            ->to(route('poles.show', $pole).'#komentar-verifikator')
            ->with('status', 'Komentar verifikator berhasil ditambahkan.');
    }
}
