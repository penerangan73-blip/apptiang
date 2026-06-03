<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => User::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $input = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160', Rule::unique('users', 'email')],
            'phone' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:2000'],
            'role' => ['required', Rule::in(['superadmin', 'surveyor', 'verifikator', 'user'])],
            'password' => ['required', 'string', 'min:6', 'max:160'],
        ]);

        $user = User::query()->create($input);

        return response()->json([
            'data' => $user,
        ], 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $input = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:2000'],
            'role' => ['required', Rule::in(['superadmin', 'surveyor', 'verifikator', 'user'])],
            'password' => ['nullable', 'string', 'min:6', 'max:160'],
        ]);

        if ($user->role === 'superadmin' && $input['role'] !== 'superadmin' && $this->isLastSuperadmin($user)) {
            throw ValidationException::withMessages([
                'role' => ['Superadmin terakhir tidak boleh diturunkan rolenya.'],
            ]);
        }

        if (empty($input['password'])) {
            unset($input['password']);
        }

        $user->update($input);

        return response()->json([
            'data' => $user->refresh(),
        ]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($request->user()->is($user)) {
            throw ValidationException::withMessages([
                'user' => ['Tidak bisa menghapus akun sendiri.'],
            ]);
        }

        if ($user->role === 'superadmin' && $this->isLastSuperadmin($user)) {
            throw ValidationException::withMessages([
                'user' => ['Superadmin terakhir tidak boleh dihapus.'],
            ]);
        }

        $user->delete();

        return response()->json([
            'message' => 'User berhasil dihapus.',
        ]);
    }

    private function isLastSuperadmin(User $user): bool
    {
        return User::query()
            ->where('role', 'superadmin')
            ->whereKeyNot($user->id)
            ->doesntExist();
    }
}
