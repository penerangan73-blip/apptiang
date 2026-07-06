@extends('layouts.app', ['title' => $pageTitle ?? 'Inventarisasi APJ Non APP'])

@section('body')
@php
    $user = auth()->user();
    $canManagePoles = in_array($user->role, ['superadmin', 'surveyor', 'verifikator'], true);
    $isSuperadmin = $user->role === 'superadmin';
    $label = fn ($value) => ucfirst(str_replace('_', ' ', (string) $value));
    $ownership = fn ($value) => ['dpu_kp' => 'DPU KP', 'dishub' => 'DISHUB', 'pln' => 'PLN', 'lainnya' => 'Lainnya'][$value] ?? $label($value);

    $navItems = [
        ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'gauge'],
        ['route' => 'poles.index', 'label' => 'Data Tiang', 'icon' => 'database'],
        ['route' => 'map.index', 'label' => 'Peta', 'icon' => 'map-pinned'],
        ['route' => 'reports.index', 'label' => 'Aktivitas', 'icon' => 'activity'],
    ];
    if ($isSuperadmin) {
        $navItems[] = ['route' => 'users.index', 'label' => 'Manajemen User', 'icon' => 'users'];
    }
@endphp

<div class="min-h-screen pb-24 lg:grid lg:grid-cols-[280px_1fr] lg:pb-0">
    {{-- Desktop Sidebar --}}
    <aside class="fixed inset-y-0 left-0 z-40 hidden w-72 border-r border-white/10 bg-slate-950/50 backdrop-blur-xl text-white lg:flex lg:flex-col">
        <div class="border-b border-white/10 p-6">
            <div class="grid grid-cols-[40px_minmax(0,1fr)_40px] items-center gap-2">
                <div class="flex size-10 items-center justify-center rounded-xl border border-white/20 bg-white/95 p-0.5 shadow-lg">
                    <img src="{{ asset('image/gunungkidul.png') }}" alt="Logo Kabupaten Gunungkidul" class="size-full object-contain">
                </div>
                <div class="min-w-0 text-center">
                    <p class="text-[10px] font-black uppercase leading-4 tracking-[0.08em] text-cyan-400 drop-shadow-sm">Dinas Perhubungan</p>
                    <h1 class="text-sm font-bold leading-5 text-slate-100">Inventarisasi APJ</h1>
                </div>
                <div class="flex size-10 items-center justify-center rounded-xl border border-white/20 bg-white/95 p-0.5 shadow-lg">
                    <img src="{{ asset('image/dishub.png') }}" alt="Logo Dishub" class="size-full object-contain">
                </div>
            </div>
        </div>
        <nav class="flex-1 space-y-1 p-4">
            @foreach ($navItems as $nav)
                <a href="{{ route($nav['route']) }}"
                   class="flex h-11 w-full items-center gap-3 rounded-lg px-3 text-sm font-semibold transition hover:bg-slate-900/10
                          {{ request()->routeIs($nav['route']) ? 'bg-cyan-500/15 text-cyan-300 border-l-[3px] border-cyan-400 rounded-l-none' : 'text-slate-400 border-l-[3px] border-transparent' }}">
                    <i data-lucide="{{ $nav['icon'] }}" class="icon"></i>
                    {{ $nav['label'] }}
                </a>
            @endforeach
        </nav>
        <div class="p-4">
            <div class="rounded-2xl border border-cyan-400/20 bg-cyan-400/10 p-4 shadow-[0_14px_34px_rgba(6,182,212,0.08)]">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-white/90 p-1.5">
                        <img src="{{ asset('image/dishub.png') }}" alt="Logo Dishub" class="size-full object-contain">
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-black uppercase tracking-widest text-cyan-300">Profil Singkat</p>
                        <h2 class="mt-0.5 text-sm font-black leading-5 text-slate-100">Dishub Gunungkidul</h2>
                    </div>
                </div>
                <p class="mt-3 text-xs leading-5 text-slate-300">
                    Mendukung pelayanan transportasi, keselamatan jalan, dan tata kelola aset APJ Kabupaten Gunungkidul.
                </p>
                <div class="mt-3 grid gap-2 text-xs font-semibold text-slate-300">
                    <div class="flex items-center gap-2">
                        <i data-lucide="map-pin" class="size-4 text-cyan-300"></i>
                        Wonosari, Gunungkidul
                    </div>
                    <div class="flex items-center gap-2">
                        <i data-lucide="lightbulb" class="size-4 text-amber-300"></i>
                        Inventarisasi APJ Non APP
                    </div>
                </div>
                <a href="https://dishub.gunungkidulkab.go.id/" target="_blank" rel="noopener noreferrer" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg border border-white/10 bg-slate-950/40 px-3 py-2 text-xs font-black text-cyan-200 transition hover:bg-slate-950/70">
                    <i data-lucide="external-link" class="size-4"></i>
                    Situs Resmi
                </a>
            </div>
        </div>
    </aside>

    {{-- Main Content --}}
    <div class="min-w-0 lg:col-start-2 pt-[64px] lg:pt-0">
        {{-- Header --}}
        <header class="fixed inset-x-0 top-0 z-50 border-b border-white/10 bg-slate-950/90 backdrop-blur-xl lg:relative lg:w-auto">
            <div class="flex items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
                <div class="flex min-w-0 items-center gap-3">
                    <div class="size-10 shrink-0 rounded-full border border-white/20 bg-white/5 p-1 lg:hidden">
                        <img src="{{ asset('image/dishub.png') }}" alt="Logo Dishub" class="size-full object-contain">
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-black uppercase tracking-widest text-cyan-400 drop-shadow-sm">Dinas Perhubungan</p>
                        <h2 class="truncate text-base font-bold text-slate-100">Inventarisasi APJ Non APP</h2>
                    </div>
                </div>

                <div class="relative">
                    <button type="button" class="btn btn-muted" data-menu-toggle="profile-menu">
                        @if ($user->photo_url)
                            <img src="{{ $user->photo_url }}" alt="{{ $user->name }}" class="size-7 rounded-full object-cover">
                        @else
                            <i data-lucide="user-circle" class="icon text-cyan-400"></i>
                        @endif
                        <span class="hidden max-w-36 truncate sm:inline">{{ $user->name }}</span>
                        <i data-lucide="chevron-down" class="icon"></i>
                    </button>
                    <div id="profile-menu" class="absolute right-0 top-12 z-50 hidden w-72 overflow-hidden rounded-xl border border-white/10 bg-slate-900 shadow-2xl">
                        <div class="border-b border-white/10 p-4">
                            <p class="truncate font-bold text-slate-100">{{ $user->name }}</p>
                            <p class="truncate text-xs font-semibold text-slate-400">{{ $user->email }}</p>
                        </div>
                        <div class="p-2">
                            <button type="button" class="flex h-10 w-full items-center gap-2 rounded-md px-3 text-sm font-semibold text-slate-300 hover:bg-white/5" data-open-dialog="profile-dialog">
                                <i data-lucide="user-circle" class="icon text-cyan-400"></i>
                                Ubah Profil
                            </button>
                            <button type="button" class="flex h-10 w-full items-center gap-2 rounded-md px-3 text-sm font-semibold text-slate-300 hover:bg-white/5" data-open-dialog="password-dialog">
                                <i data-lucide="key-round" class="icon text-cyan-400"></i>
                                Ubah Password
                            </button>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="flex h-10 w-full items-center gap-2 rounded-md px-3 text-sm font-semibold text-red-600 hover:bg-slate-900/5" type="submit">
                                    <i data-lucide="log-out" class="icon"></i>
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        {{-- Page Content --}}
        <main class="mx-auto max-w-[1500px] space-y-5 px-4 py-5 sm:px-6 lg:px-8">
            @if (session('status'))
                <div id="status-alert" class="rounded-xl border border-cyan-500/30 bg-cyan-500/10 px-5 py-4 text-sm font-semibold text-cyan-400 shadow-[0_0_15px_rgba(6,182,212,0.15)] transition-opacity duration-500">
                    <div class="flex items-center gap-3">
                        <i data-lucide="check-circle-2" class="icon"></i>
                        {{ session('status') }}
                    </div>
                </div>
                <script>
                    setTimeout(() => {
                        const alert = document.getElementById('status-alert');
                        if (alert) {
                            alert.style.opacity = '0';
                            setTimeout(() => alert.remove(), 500);
                        }
                    }, 3000);
                </script>
            @endif

            @if (session('error'))
                <div id="error-alert" class="rounded-xl border border-red-500/30 bg-red-500/10 px-5 py-4 text-sm font-semibold text-red-400 shadow-[0_0_15px_rgba(239,68,68,0.15)] transition-opacity duration-500">
                    <div class="flex items-center gap-3">
                        <i data-lucide="alert-triangle" class="icon"></i>
                        {{ session('error') }}
                    </div>
                </div>
                <script>
                    setTimeout(() => {
                        const alert = document.getElementById('error-alert');
                        if (alert) {
                            alert.style.opacity = '0';
                            setTimeout(() => alert.remove(), 500);
                        }
                    }, 5000);
                </script>
            @endif

            @if ($errors->any())
                <div class="rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-400">
                    <p class="font-bold">Periksa lagi input berikut:</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    {{-- Mobile Bottom Nav --}}
    <nav class="fixed inset-x-0 bottom-0 z-50 border-t border-white/5 bg-slate-950/95 pb-2 px-2 pt-2 backdrop-blur-2xl lg:hidden shadow-[0_-10px_40px_rgba(0,0,0,0.8)]" style="padding-bottom: max(0.5rem, env(safe-area-inset-bottom));">
        <div class="mx-auto grid max-w-lg gap-1" style="grid-template-columns: repeat({{ count($navItems) }}, minmax(0, 1fr));">
            @foreach ($navItems as $nav)
                <a href="{{ route($nav['route']) }}"
                   class="flex h-14 flex-col items-center justify-center gap-1.5 rounded-xl text-[10px] font-bold transition-all
                          {{ request()->routeIs($nav['route']) ? 'text-cyan-400' : 'text-slate-400 hover:bg-white/5 hover:text-slate-200' }}">
                    <i data-lucide="{{ $nav['icon'] }}" class="icon"></i>
                    {{ $nav['label'] }}
                </a>
            @endforeach
        </div>
    </nav>
</div>

{{-- Global Dialogs (Profile & Password) --}}
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

@yield('dialogs')

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) {
            lucide.createIcons();
        }
        setupDialogs();
        setupProfileMenu();
        setupPhotoPreviews();
        try {
            @yield('page-scripts')
        } catch (error) {
            console.error('Script halaman gagal dijalankan.', error);
        }
    });

    function setupDialogs() {
        if (window.__pjuDialogsReady) return;
        window.__pjuDialogsReady = true;

        document.addEventListener('click', (event) => {
            const openButton = event.target.closest('[data-open-dialog]');
            if (openButton) {
                const dialog = document.getElementById(openButton.dataset.openDialog);
                if (!dialog) {
                    console.warn(`Dialog ${openButton.dataset.openDialog} tidak ditemukan.`);
                    return;
                }

                if (dialog.id === 'pole-create-dialog') {
                    const lampHouseType = dialog.querySelector('[name="lamp_house_type"]');
                    if (lampHouseType) lampHouseType.value = '';
                }

                if (openButton.dataset.reportPole) {
                    const select = document.getElementById('report-pole-id');
                    if (select) select.value = openButton.dataset.reportPole;
                }

                if (openButton.dataset.deleteUrl) {
                    const form = dialog.querySelector('#pole-datatable-delete-form');
                    const code = dialog.querySelector('#pole-datatable-delete-code');
                    const number = dialog.querySelector('#pole-datatable-delete-number');
                    const street = dialog.querySelector('#pole-datatable-delete-street');

                    if (form) form.action = openButton.dataset.deleteUrl;
                    if (code) code.textContent = openButton.dataset.deleteCode || '-';
                    if (number) number.textContent = openButton.dataset.deleteCode || '-';
                    if (street) street.textContent = openButton.dataset.deleteStreet || '-';
                }

                if (dialog.showModal) {
                    dialog.showModal();
                } else {
                    dialog.setAttribute('open', 'open');
                }

                if (typeof window.setupCoordinatePickers === 'function') {
                    window.setupCoordinatePickers(dialog);
                }
                if (typeof window.setupLampTechnicalFields === 'function') {
                    window.setupLampTechnicalFields(dialog);
                }
                if (window.pjuMaps) {
                    setTimeout(() => Object.values(window.pjuMaps).forEach((map) => map.invalidateSize()), 120);
                    setTimeout(() => Object.values(window.pjuMaps).forEach((map) => map.invalidateSize()), 350);
                }
                return;
            }

            const closeButton = event.target.closest('[data-close-dialog]');
            if (closeButton) {
                closeButton.closest('dialog')?.close();
            }
        });

        document.querySelectorAll('dialog').forEach((dialog) => {
            dialog.addEventListener('click', (event) => {
                if (event.target === dialog) dialog.close();
            });
        });
    }

    function setupProfileMenu() {
        document.querySelectorAll('[data-menu-toggle]').forEach((button) => {
            button.addEventListener('click', (event) => {
                event.stopPropagation();
                document.getElementById(button.dataset.menuToggle)?.classList.toggle('hidden');
            });
        });
        document.addEventListener('click', () => document.getElementById('profile-menu')?.classList.add('hidden'));
    }

    function setupPhotoPreviews() {
        document.querySelectorAll('[data-photo-input]').forEach((input) => {
            const renderPreview = () => {
                const preview = document.getElementById(input.dataset.photoInput);
                const files = Array.from(input.files || []);
                if (!preview || !files.length) return;

                preview.innerHTML = `
                    <div class="grid w-full grid-cols-2 gap-2 sm:grid-cols-3">
                        ${files.map((file, index) => `
                            <div class="relative overflow-hidden rounded-md border border-white/10">
                                <img src="${URL.createObjectURL(file)}" alt="Preview foto ${index + 1}" class="h-24 w-full object-cover">
                                <button type="button" class="absolute right-1.5 top-1.5 flex size-7 items-center justify-center rounded-md bg-red-600/90 text-white shadow-lg transition hover:bg-red-500" data-remove-selected-photo="${index}" aria-label="Hapus preview foto ${index + 1}">
                                    <i data-lucide="x" class="size-4"></i>
                                </button>
                            </div>
                        `).join('')}
                    </div>
                    <p class="mt-2 text-xs font-bold text-slate-500">${files.length} foto dipilih</p>
                `;

                if (window.lucide) lucide.createIcons();
            };

            input.addEventListener('change', renderPreview);

            document.getElementById(input.dataset.photoInput)?.addEventListener('click', (event) => {
                const removeButton = event.target.closest('[data-remove-selected-photo]');
                if (!removeButton) return;

                event.preventDefault();
                event.stopPropagation();

                const removeIndex = Number(removeButton.dataset.removeSelectedPhoto);
                const dataTransfer = new DataTransfer();
                Array.from(input.files || []).forEach((file, index) => {
                    if (index !== removeIndex) dataTransfer.items.add(file);
                });
                input.files = dataTransfer.files;

                if (input.files.length) {
                    renderPreview();
                } else {
                    const preview = document.getElementById(input.dataset.photoInput);
                    if (preview) preview.innerHTML = '<span class="text-sm font-semibold text-slate-400">Ambil atau unggah beberapa foto</span>';
                }
            });
        });
    }

    function escapeHtml(value) {
        return String(value).replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;');
    }

    function formatOption(value) {
        return {
            baik: 'Baik',
            rusak: 'Rusak',
            perbaikan: 'Berkarat',
            lurus: 'Tiang Baja (Galvanis)',
            lengkung: 'Tiang Baja (Galvanis)',
            tanpa_tiang: 'Menempel Tiang PLN',
            tunggal: 'Single Arm',
            ganda: 'Double Arm',
            dekoratif: 'Single Arm',
            sodium: 'HPS (Sodium Tekanan Tinggi)',
            hilang: 'Kusam',
            tiang_baja_galvanis: 'Tiang Baja (Galvanis)',
            tiang_beton: 'Tiang Beton',
            tiang_cor: 'Tiang Cor',
            tiang_kayu: 'Kayu',
            tiang_bambu: 'Bambu',
            menempel_tiang_pln: 'Menempel Tiang PLN',
            menempel_tembok: 'Menempel Tembok',
            menempel_pohon: 'Menempel Pohon',
            tanpa_penyangga: 'Tanpa Penyangga',
            single_arm: 'Single Arm',
            double_arm: 'Double Arm',
            high_mast: 'High Mast',
            keropos: 'Keropos',
            miring: 'Miring',
            berkarat: 'Berkarat',
            kokoh: 'Kokoh',
            led: 'LED',
            cfl: 'CFL',
            hps: 'HPS (Sodium Tekanan Tinggi)',
            son_t: 'SON-T',
            mercury: 'Mercury',
            hpit: 'HPIT',
            tl_neon: 'TL/Neon',
            lainnya: 'Lainnya',
            menyala: 'Menyala',
            redup: 'Redup',
            berkedip: 'Berkedip',
            mati: 'Mati',
            rusak_pecah: 'Rusak Pecah',
            tidak_ada_lampu: 'Tidak Ada Lampu',
            caping: 'Caping',
            konvensional: 'Konvensional',
            tidak_ada: 'Tidak Ada',
            pecah: 'Pecah',
            kusam: 'Kusam',
            kemasukan_air: 'Kemasukan Air',
            sudah_ada: 'Sudah Ada',
            belum: 'Belum',
        }[value] || String(value || '').replaceAll('_', ' ');
    }

    function formatOwnership(value) {
        return { dpu_kp: 'DPU KP', dishub: 'DISHUB', pln: 'PLN', lainnya: 'Lainnya' }[value] || formatOption(value);
    }
</script>
@endsection
