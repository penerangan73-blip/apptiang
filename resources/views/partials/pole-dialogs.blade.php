@php
    $label = $label ?? fn ($value) => ucfirst(str_replace('_', ' ', (string) $value));
    $ownership = $ownership ?? fn ($value) => ['dpu_kp' => 'DPU KP', 'dishub' => 'DISHUB', 'pln' => 'PLN', 'lainnya' => 'Lainnya'][$value] ?? $label($value);
    $canManagePoles = $canManagePoles ?? in_array(auth()->user()->role, ['superadmin', 'surveyor'], true);
    $reportPoles = $reportPoles ?? ($allPoles ?? collect());
@endphp

@if ($canManagePoles)
    <dialog id="pole-create-dialog">
        <div class="modal-card">
            <div class="flex items-start justify-between gap-4 border-b border-white/10 p-5">
                <div>
                    <h2 class="text-xl font-black">Tambah Data Tiang</h2>
                    <p class="text-sm text-slate-400">Isi nomor tiang listrik, lokasi, spesifikasi, foto, dan keterangan aset PJU.</p>
                </div>
                <button type="button" class="btn btn-muted px-3 py-2" data-close-dialog><i data-lucide="x" class="icon"></i></button>
            </div>
            <form method="POST" action="{{ route('poles.store') }}" enctype="multipart/form-data" class="grid gap-4 p-5 md:grid-cols-2">
                @csrf
                @include('partials.pole-fields', ['pole' => null, 'prefix' => 'create-pole'])
                <div class="md:col-span-2">
                    <button class="btn btn-primary h-12" type="submit"><i data-lucide="save" class="icon"></i> Tambah Tiang</button>
                </div>
            </form>
        </div>
    </dialog>

    @if ($reportPoles->isNotEmpty())
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
                        @foreach ($reportPoles as $p)
                            <option value="{{ $p->id }}">{{ $p->customer_id }} - {{ $p->street_name }}</option>
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
                        <span id="report-photo-preview" class="text-sm font-semibold text-slate-400">Ambil atau unggah beberapa foto laporan</span>
                        <input class="sr-only" name="photos[]" type="file" accept="image/*" capture="environment" multiple data-photo-input="report-photo-preview">
                    </label>
                </div>
                <button class="btn btn-primary h-12" type="submit"><i data-lucide="save" class="icon"></i> Simpan Laporan</button>
            </form>
        </div>
    </dialog>
    @endif
@endif

@foreach ($dialogPoles as $pole)
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
                        <button class="btn btn-primary h-12" type="submit"><i data-lucide="save" class="icon"></i> Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </dialog>

        <dialog id="pole-delete-{{ $pole->id }}">
            <div class="modal-card max-w-lg">
                <div class="border-b border-white/10 p-5">
                    <div class="flex items-start gap-4">
                        <div class="flex size-12 shrink-0 items-center justify-center rounded-xl border border-red-500/30 bg-red-500/10 text-red-400">
                            <i data-lucide="alert-triangle" class="icon"></i>
                        </div>
                        <div class="min-w-0">
                            <h2 class="text-xl font-black text-slate-100">Hapus Data Tiang?</h2>
                            <p class="mt-1 text-sm leading-6 text-slate-400">Data nomor tiang <span class="font-bold text-slate-200">{{ $pole->customer_id }}</span> akan dihapus. Laporan yang terkait dengan tiang ini juga dapat ikut terhapus.</p>
                        </div>
                    </div>
                </div>
                <div class="p-5">
                    <div class="rounded-xl border border-white/10 bg-white/5 p-4 text-sm text-slate-300">
                        <p class="font-bold text-slate-100">{{ $pole->customer_id }}</p>
                        <p class="mt-1">{{ $pole->street_name }}</p>
                    </div>
                    <div class="mt-5 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <button type="button" class="btn btn-muted" data-close-dialog>Batal</button>
                        <form method="POST" action="{{ route('poles.destroy', $pole) }}">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger w-full sm:w-auto" type="submit">
                                <i data-lucide="trash-2" class="icon"></i>
                                Ya, Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </dialog>
    @endif
@endforeach
