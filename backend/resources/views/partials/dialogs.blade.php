@php
    $label = $label ?? fn ($value) => ucfirst(str_replace('_', ' ', (string) $value));
    $ownership = $ownership ?? fn ($value) => ['dpu_kp' => 'DPU KP', 'dishub' => 'DISHUB', 'pln' => 'PLN', 'lainnya' => 'Lainnya'][$value] ?? $label($value);
@endphp

@if ($canManagePoles)
    <dialog id="pole-create-dialog">
        <div class="modal-card">
            <div class="flex items-start justify-between gap-4 border-b border-white/10 p-5">
                <div>
                    <h2 class="text-xl font-black">Tambah Data Tiang</h2>
                    <p class="text-sm text-slate-400">Isi ID pelanggan, lokasi, spesifikasi, foto, dan keterangan aset PJU.</p>
                </div>
                <button type="button" class="btn btn-muted px-3 py-2" data-close-dialog><i data-lucide="x" class="icon"></i></button>
            </div>
            <form method="POST" action="{{ route('poles.store') }}" enctype="multipart/form-data" class="grid gap-4 p-5 md:grid-cols-2">
                @csrf
                @include('partials.pole-fields', ['pole' => null, 'prefix' => 'create-pole'])
                <div class="md:col-span-2">
                    <button class="btn btn-primary h-12" type="submit">
                        <i data-lucide="save" class="icon"></i>
                        Tambah Tiang
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    <dialog id="report-dialog">
        <div class="modal-card max-w-xl">
            <div class="flex items-start justify-between gap-4 border-b border-white/10 p-5">
                <div>
                    <h2 class="text-xl font-black">Laporan Kerusakan</h2>
                    <p class="text-sm text-slate-400">Unggah foto dan tambahkan catatan kerusakan dari lapangan.</p>
                </div>
                <button type="button" class="btn btn-muted px-3 py-2" data-close-dialog><i data-lucide="x" class="icon"></i></button>
            </div>
            <form method="POST" action="{{ route('daily-reports.store') }}" enctype="multipart/form-data" class="space-y-4 p-5">
                @csrf
                <div>
                    <label class="label">Tiang</label>
                    <select id="report-pole-id" class="field" name="pole_id" required>
                        <option value="">Pilih tiang</option>
                        @foreach ($allPoles as $pole)
                            <option value="{{ $pole->id }}">{{ $pole->customer_id }} - {{ $pole->street_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Catatan</label>
                    <textarea class="field" name="notes" rows="4" required placeholder="Tuliskan kondisi kerusakan..."></textarea>
                </div>
                <div>
                    <label class="label">Foto</label>
                    <label class="flex min-h-32 cursor-pointer flex-col items-center justify-center rounded-lg border border-dashed bg-white/5 p-4 text-center">
                        <span id="report-photo-preview" class="text-sm font-semibold text-slate-400">Ambil atau unggah foto laporan</span>
                        <input class="sr-only" name="photo" type="file" accept="image/*" capture="environment" data-photo-input="report-photo-preview">
                    </label>
                </div>
                <button class="btn btn-primary h-12" type="submit">
                    <i data-lucide="save" class="icon"></i>
                    Simpan Laporan
                </button>
            </form>
        </div>
    </dialog>
@endif

@foreach ($dialogPoles as $pole)
    <dialog id="pole-detail-{{ $pole->id }}">
        <div class="modal-card">
            <div class="sticky top-0 z-10 flex items-start justify-between gap-4 border-b border-white/10 bg-slate-950/80 backdrop-blur-xl p-5">
                <div>
                    <h2 class="text-xl font-black">Detail Data Tiang</h2>
                    <p class="text-sm text-slate-400">Informasi lengkap aset PJU, termasuk foto jika tersedia.</p>
                </div>
                <button type="button" class="btn btn-muted px-3 py-2" data-close-dialog><i data-lucide="x" class="icon"></i></button>
            </div>
            <div class="p-5">
                <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
                    <div class="rounded-xl border border-white/10 bg-white/5 p-5">
                        <div class="grid gap-y-5 gap-x-6 sm:grid-cols-2">
                            @include('partials.detail-item', ['label' => 'ID Pelanggan', 'value' => $pole->customer_id])
                            @include('partials.detail-item', ['label' => 'Kondisi', 'value' => $label($pole->condition)])
                            @include('partials.detail-item', ['label' => 'Nama Jalan', 'value' => $pole->street_name, 'class' => 'sm:col-span-2'])
                            @include('partials.detail-item', ['label' => 'Latitude', 'value' => number_format((float) $pole->latitude, 7)])
                            @include('partials.detail-item', ['label' => 'Longitude', 'value' => number_format((float) $pole->longitude, 7)])
                            @include('partials.detail-item', ['label' => 'Daya', 'value' => number_format($pole->power_usage).' Watt'])
                            @include('partials.detail-item', ['label' => 'Jenis Tiang', 'value' => $label($pole->pole_type)])
                            @include('partials.detail-item', ['label' => 'Tinggi', 'value' => $pole->height ? $pole->height.' m' : '-'])
                            @include('partials.detail-item', ['label' => 'Bentangan', 'value' => $pole->span_length ? $pole->span_length.' m' : '-'])
                            @include('partials.detail-item', ['label' => 'Pondasi', 'value' => $label($pole->foundation_type)])
                            @include('partials.detail-item', ['label' => 'Kepemilikan', 'value' => $ownership($pole->ownership)])
                            @include('partials.detail-item', ['label' => 'Trafo PLN', 'value' => $pole->pln_transformer ?: '-'])
                            @include('partials.detail-item', ['label' => 'Pertama Diinput', 'value' => optional($pole->created_at)->format('H:i d-m-Y') ?: '-'])
                            @include('partials.detail-item', ['label' => 'Diinput Oleh', 'value' => $pole->createdBy?->name ?? '-'])
                            @include('partials.detail-item', ['label' => 'Update Terakhir', 'value' => optional($pole->updated_at)->format('H:i d-m-Y') ?: '-'])
                            @include('partials.detail-item', ['label' => 'Update Oleh', 'value' => $pole->updatedBy?->name ?? '-'])
                            @include('partials.detail-item', ['label' => 'Keterangan', 'value' => $pole->notes ?: '-', 'class' => 'sm:col-span-2'])
                        </div>
                    </div>
                    <div class="flex flex-col rounded-xl border border-white/10 bg-white/5 p-5">
                        <p class="mb-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Foto Survei</p>
                        @if ($pole->photo_url)
                            <img src="{{ $pole->photo_url }}" alt="Foto {{ $pole->customer_id }}" class="max-h-96 w-full rounded-lg object-cover">
                        @else
                            <div class="flex flex-1 min-h-[200px] flex-col items-center justify-center rounded-lg border border-dashed border-white/20 text-slate-500">
                                <i data-lucide="image-off" class="icon size-8 mb-2 opacity-50"></i>
                                <span class="text-sm font-semibold">Belum ada foto</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </dialog>

    @if ($canManagePoles)
        <dialog id="pole-edit-{{ $pole->id }}">
            <div class="modal-card">
                <div class="flex items-start justify-between gap-4 border-b border-white/10 p-5">
                    <div>
                        <h2 class="text-xl font-black">Edit Data Tiang</h2>
                        <p class="text-sm text-slate-400">{{ $pole->customer_id }} - {{ $pole->street_name }}</p>
                    </div>
                    <button type="button" class="btn btn-muted px-3 py-2" data-close-dialog><i data-lucide="x" class="icon"></i></button>
                </div>
                <form method="POST" action="{{ route('poles.update', $pole) }}" enctype="multipart/form-data" class="grid gap-4 p-5 md:grid-cols-2">
                    @csrf
                    @method('PUT')
                    @include('partials.pole-fields', ['pole' => $pole, 'prefix' => 'edit-pole-'.$pole->id])
                    <div class="md:col-span-2">
                        <button class="btn btn-primary h-12" type="submit">
                            <i data-lucide="save" class="icon"></i>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </dialog>
    @endif
@endforeach

<dialog id="profile-dialog">
    <div class="modal-card max-w-xl">
        <div class="flex items-start justify-between gap-4 border-b border-white/10 p-5">
            <div>
                <h2 class="text-xl font-black">Ubah Profil</h2>
                <p class="text-sm text-slate-400">Perbarui nama, email, telepon, foto, dan alamat profil.</p>
            </div>
            <button type="button" class="btn btn-muted px-3 py-2" data-close-dialog><i data-lucide="x" class="icon"></i></button>
        </div>
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="grid gap-4 p-5 md:grid-cols-2">
            @csrf
            @method('PUT')
            <div>
                <label class="label">Nama</label>
                <input class="field" name="name" value="{{ old('name', $user->name) }}" required>
            </div>
            <div>
                <label class="label">Email</label>
                <input class="field" name="email" type="email" value="{{ old('email', $user->email) }}" required>
            </div>
            <div>
                <label class="label">Telepon</label>
                <input class="field" name="phone" value="{{ old('phone', $user->phone) }}">
            </div>
            <div>
                <label class="label">Foto</label>
                <input class="field" name="photo" type="file" accept="image/*">
            </div>
            <div class="md:col-span-2">
                <label class="label">Alamat</label>
                <textarea class="field" name="address" rows="3">{{ old('address', $user->address) }}</textarea>
            </div>
            <div class="md:col-span-2">
                <button class="btn btn-primary" type="submit">Simpan Profil</button>
            </div>
        </form>
    </div>
</dialog>

<dialog id="password-dialog">
    <div class="modal-card max-w-xl">
        <div class="flex items-start justify-between gap-4 border-b border-white/10 p-5">
            <div>
                <h2 class="text-xl font-black">Ubah Password</h2>
                <p class="text-sm text-slate-400">Masukkan password saat ini lalu password baru.</p>
            </div>
            <button type="button" class="btn btn-muted px-3 py-2" data-close-dialog><i data-lucide="x" class="icon"></i></button>
        </div>
        <form method="POST" action="{{ route('profile.password') }}" class="space-y-4 p-5">
            @csrf
            @method('PUT')
            <div>
                <label class="label">Password Saat Ini</label>
                <input class="field" name="current_password" type="password" required>
            </div>
            <div>
                <label class="label">Password Baru</label>
                <input class="field" name="password" type="password" minlength="6" required>
            </div>
            <div>
                <label class="label">Konfirmasi Password Baru</label>
                <input class="field" name="password_confirmation" type="password" minlength="6" required>
            </div>
            <button class="btn btn-primary" type="submit">Update Password</button>
        </form>
    </div>
</dialog>

@if ($isSuperadmin)
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
                    @csrf
                    @method('PUT')
                    @include('partials.user-fields', ['managedUser' => $managedUser])
                    <div class="md:col-span-2">
                        <button class="btn btn-primary" type="submit">Simpan User</button>
                    </div>
                </form>
            </div>
        </dialog>
    @endforeach
@endif
