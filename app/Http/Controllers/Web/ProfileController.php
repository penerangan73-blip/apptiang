<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\ImageStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $input = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:2000'],
            'photo' => ['sometimes', 'nullable', 'image', 'max:10240'],
        ]);

        if ($request->hasFile('photo')) {
            $path = ImageStorage::store($request->file('photo'), 'profiles');
            $input['photo_url'] = asset("storage/{$path}");
        }

        unset($input['photo']);
        $user->update($input);

        return back()->with('status', 'Profil berhasil diperbarui.');
    }

    public function password(Request $request): RedirectResponse
    {
        $input = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if (! Hash::check($input['current_password'], $request->user()->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Password saat ini tidak sesuai.'],
            ]);
        }

        $request->user()->update([
            'password' => $input['password'],
        ]);

        return back()->with('status', 'Password berhasil diperbarui.');
    }
}
