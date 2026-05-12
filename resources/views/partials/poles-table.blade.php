@php
    $label = $label ?? fn ($value) => [
        'baik' => 'Kokoh',
        'rusak' => 'Keropos',
        'perbaikan' => 'Berkarat',
        'lurus' => 'Tiang Baja (Galvanis)',
        'lengkung' => 'Tiang Baja (Galvanis)',
        'tanpa_tiang' => 'Menempel Tiang PLN',
        'tunggal' => 'Single Arm',
        'ganda' => 'Double Arm',
        'dekoratif' => 'Single Arm',
        'sodium' => 'HPS (Sodium Tekanan Tinggi)',
        'hilang' => 'Kusam',
        'tiang_baja_galvanis' => 'Tiang Baja (Galvanis)',
        'tiang_beton' => 'Tiang Beton',
        'menempel_tiang_pln' => 'Menempel Tiang PLN',
        'single_arm' => 'Single Arm',
        'double_arm' => 'Double Arm',
        'high_mast' => 'High Mast',
        'keropos' => 'Keropos',
        'miring' => 'Miring',
        'berkarat' => 'Berkarat',
        'kokoh' => 'Kokoh',
        'led' => 'LED',
        'hps' => 'HPS (Sodium Tekanan Tinggi)',
        'son_t' => 'SON-T',
        'mercury' => 'Mercury',
        'lainnya' => 'Lainnya',
        'menyala' => 'Menyala',
        'redup' => 'Redup',
        'berkedip' => 'Berkedip',
        'mati' => 'Mati',
        'pecah' => 'Pecah',
        'kusam' => 'Kusam',
        'kemasukan_air' => 'Kemasukan Air',
        'sudah_ada' => 'Sudah Ada',
        'belum' => 'Belum',
    ][$value] ?? ucfirst(str_replace('_', ' ', (string) $value));
    $ownership = $ownership ?? fn ($value) => ['dpu_kp' => 'DPU KP', 'dishub' => 'DISHUB', 'pln' => 'PLN', 'lainnya' => 'Lainnya'][$value] ?? $label($value);
    $showManageActions = $showManageActions ?? true;
    $showDirectionActions = $showDirectionActions ?? false;
@endphp

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-white/10 text-sm">
            <thead class="bg-white/5 text-left text-xs font-black uppercase tracking-wide text-slate-400">
                <tr>
                    <th class="px-5 py-3">Nomor Tiang Listrik</th>
                    <th class="px-5 py-3">Lokasi</th>
                    <th class="px-5 py-3">Daya Lampu</th>
                    <th class="px-5 py-3">Kondisi Tiang</th>
                    <th class="px-5 py-3">Jenis Tiang</th>
                    <th class="px-5 py-3">Tinggi Tiang</th>
                    <th class="px-5 py-3">Tipe Ornamen</th>
                    <th class="px-5 py-3">Jenis Lampu</th>
                    <th class="px-5 py-3">Status Lampu</th>
                    <th class="px-5 py-3">Kondisi Rumah Lampu</th>
                    <th class="px-5 py-3">Jumlah Titik Lampu</th>
                    <th class="px-5 py-3">KWH Meter</th>
                    @if ($showTimestamps)
                        <th class="px-5 py-3">Diinput</th>
                    @endif
                    <th class="px-5 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse ($poles as $pole)
                    <tr>
                        <td class="px-5 py-4">
                            <p class="font-bold text-slate-100">{{ $pole->customer_id }}</p>
                        </td>
                        <td class="px-5 py-4 font-medium text-slate-300">
                            <span class="pju-location-cell" title="{{ $pole->street_name }}">{{ $pole->street_name }}</span>
                        </td>
                        <td class="px-5 py-4">{{ number_format($pole->power_usage) }} Watt</td>
                        <td class="px-5 py-4"><span class="badge badge-{{ $pole->condition }}">{{ $label($pole->condition) }}</span></td>
                        <td class="px-5 py-4 text-slate-300">{{ $label($pole->pole_type) }}</td>
                        <td class="px-5 py-4 text-slate-300">{{ $pole->height ? $pole->height.' m' : '-' }}</td>
                        <td class="px-5 py-4 text-slate-300">{{ $label($pole->ornament_type) }}</td>
                        <td class="px-5 py-4 text-slate-300">{{ $label($pole->lamp_type) }}</td>
                        <td class="px-5 py-4 text-slate-300">{{ $label($pole->lamp_status) }}</td>
                        <td class="px-5 py-4 text-slate-300">{{ $label($pole->lamp_house_condition) }}</td>
                        <td class="px-5 py-4 text-slate-300">{{ number_format((int) $pole->light_point_count) }}</td>
                        <td class="px-5 py-4 text-slate-300">{{ $label($pole->kwh_meter) ?: '-' }}</td>
                        @if ($showTimestamps)
                            <td class="px-5 py-4 text-slate-300">{{ optional($pole->created_at)->format('H:i d-m-Y') }}</td>
                        @endif
                        <td class="px-5 py-4">
                            <div class="flex justify-end gap-2">
                                @if ($showDirectionActions)
                                    <a class="btn btn-primary px-3 py-2" href="https://www.google.com/maps/dir/?api=1&destination={{ $pole->latitude }},{{ $pole->longitude }}" target="_blank" rel="noopener noreferrer">
                                        <i data-lucide="navigation" class="icon"></i>
                                        Menuju Lokasi
                                    </a>
                                @endif
                                <a class="btn btn-muted px-3 py-2" href="{{ route('poles.show', $pole) }}">
                                    <i data-lucide="eye" class="icon"></i>
                                    Detail
                                </a>
                                @if ($canManagePoles && $showManageActions)
                                    <button type="button" class="btn btn-muted px-3 py-2" data-open-dialog="pole-edit-{{ $pole->id }}">
                                        <i data-lucide="edit-3" class="icon"></i>
                                        Edit
                                    </button>
                                    <button type="button" class="btn btn-danger px-3 py-2" data-open-dialog="pole-delete-{{ $pole->id }}">
                                        <i data-lucide="trash-2" class="icon"></i>
                                        Hapus
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $showTimestamps ? 14 : 13 }}" class="px-5 py-10 text-center text-slate-400">
                            Tidak ada tiang untuk filter ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if (method_exists($poles, 'links'))
        <div class="border-t border-white/10 px-5 py-4">{{ $poles->links() }}</div>
    @endif
</div>
