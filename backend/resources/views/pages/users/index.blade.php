@extends('layouts.dashboard', ['pageTitle' => 'Manajemen User - Inventarisasi APJ Non APP'])

@section('content')
@php $label = fn ($value) => ucfirst(str_replace('_', ' ', (string) $value)); @endphp

<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h2 class="text-2xl font-bold text-slate-100">Manajemen User</h2>
        <p class="text-sm text-slate-400">Kelola akun superadmin, surveyor, dan user read-only.</p>
    </div>
    <button type="button" class="btn btn-primary" data-open-dialog="user-create-dialog">
        <i data-lucide="plus-circle" class="icon"></i> Tambah User
    </button>
</div>

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-white/10 text-sm">
            <thead class="bg-slate-900/5 text-left text-xs font-black uppercase tracking-wide text-slate-400">
                <tr>
                    <th class="px-5 py-3">Nama</th>
                    <th class="px-5 py-3">Email</th>
                    <th class="px-5 py-3">Telepon</th>
                    <th class="px-5 py-3">Role</th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @foreach ($users as $managedUser)
                    <tr>
                        <td class="px-5 py-4 font-bold">{{ $managedUser->name }}</td>
                        <td class="px-5 py-4">{{ $managedUser->email }}</td>
                        <td class="px-5 py-4">{{ $managedUser->phone ?: '-' }}</td>
                        <td class="px-5 py-4">{{ $label($managedUser->role) }}</td>
                        <td class="px-5 py-4">
                            <div class="flex justify-end gap-2">
                                <button type="button" class="btn btn-muted px-3 py-2" data-open-dialog="user-edit-{{ $managedUser->id }}">
                                    <i data-lucide="edit-3" class="icon"></i> Edit
                                </button>
                                <form method="POST" action="{{ route('users.destroy', $managedUser) }}" onsubmit="return confirm('Hapus user ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger px-3 py-2" type="submit" @disabled($managedUser->is(auth()->user()))>
                                        <i data-lucide="trash-2" class="icon"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('dialogs')
<dialog id="user-create-dialog">
    <div class="modal-card max-w-xl">
        <div class="flex items-start justify-between gap-4 border-b border-white/10 p-5">
            <div>
                <h2 class="text-xl font-black">Tambah User</h2>
                <p class="text-sm text-slate-400">Buat akun baru untuk akses aplikasi.</p>
            </div>
            <button type="button" class="btn btn-muted px-3 py-2" data-close-dialog><i data-lucide="x" class="icon"></i></button>
        </div>
        <form method="POST" action="{{ route('users.store') }}" class="grid gap-4 p-5 md:grid-cols-2">
            @csrf
            @include('partials.user-fields', ['managedUser' => null])
            <div class="md:col-span-2">
                <button class="btn btn-primary" type="submit">Simpan User</button>
            </div>
        </form>
    </div>
</dialog>

@foreach ($users as $managedUser)
    <dialog id="user-edit-{{ $managedUser->id }}">
        <div class="modal-card max-w-xl">
            <div class="flex items-start justify-between gap-4 border-b border-white/10 p-5">
                <div>
                    <h2 class="text-xl font-black">Edit User</h2>
                    <p class="text-sm text-slate-400">{{ $managedUser->email }}</p>
                </div>
                <button type="button" class="btn btn-muted px-3 py-2" data-close-dialog><i data-lucide="x" class="icon"></i></button>
            </div>
            <form method="POST" action="{{ route('users.update', $managedUser) }}" class="grid gap-4 p-5 md:grid-cols-2">
                @csrf @method('PUT')
                @include('partials.user-fields', ['managedUser' => $managedUser])
                <div class="md:col-span-2">
                    <button class="btn btn-primary" type="submit">Simpan User</button>
                </div>
            </form>
        </div>
    </dialog>
@endforeach
@endsection
