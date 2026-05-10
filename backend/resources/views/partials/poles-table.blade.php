@php
    $label = $label ?? fn ($value) => ucfirst(str_replace('_', ' ', (string) $value));
    $ownership = $ownership ?? fn ($value) => ['dpu_kp' => 'DPU KP', 'dishub' => 'DISHUB', 'pln' => 'PLN', 'lainnya' => 'Lainnya'][$value] ?? $label($value);
    $showManageActions = $showManageActions ?? true;
@endphp

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-white/10 text-sm">
            <thead class="bg-white/5 text-left text-xs font-black uppercase tracking-wide text-slate-400">
                <tr>
                    <th class="px-5 py-3">ID Pelanggan</th>
                    <th class="px-5 py-3">Lokasi</th>
                    <th class="px-5 py-3">Daya</th>
                    <th class="px-5 py-3">Kondisi</th>
                    <th class="px-5 py-3">Jenis</th>
                    @if ($showTimestamps)
                        <th class="px-5 py-3">Diinput</th>
                        <th class="px-5 py-3">Diinput Oleh</th>
                        <th class="px-5 py-3">Update Terakhir</th>
                        <th class="px-5 py-3">Update Oleh</th>
                    @endif
                    <th class="px-5 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse ($poles as $pole)
                    <tr>
                        <td class="px-5 py-4">
                            <p class="font-bold text-slate-100">{{ $pole->customer_id }}</p>
                            <p class="text-xs text-slate-400">{{ number_format((float) $pole->latitude, 5) }}, {{ number_format((float) $pole->longitude, 5) }}</p>
                        </td>
                        <td class="px-5 py-4 font-medium text-slate-300">{{ $pole->street_name }}</td>
                        <td class="px-5 py-4">{{ number_format($pole->power_usage) }} Watt</td>
                        <td class="px-5 py-4"><span class="badge badge-{{ $pole->condition }}">{{ $label($pole->condition) }}</span></td>
                        <td class="px-5 py-4 text-slate-300">{{ $label($pole->pole_type) }}</td>
                        @if ($showTimestamps)
                            <td class="px-5 py-4 text-slate-300">{{ optional($pole->created_at)->format('H:i d-m-Y') }}</td>
                            <td class="px-5 py-4 text-slate-300">{{ $pole->createdBy?->name ?? '-' }}</td>
                            <td class="px-5 py-4 text-slate-300">{{ optional($pole->updated_at)->format('H:i d-m-Y') }}</td>
                            <td class="px-5 py-4 text-slate-300">{{ $pole->updatedBy?->name ?? '-' }}</td>
                        @endif
                        <td class="px-5 py-4">
                            <div class="flex justify-end gap-2">
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
                        <td colspan="{{ $showTimestamps ? 10 : 6 }}" class="px-5 py-10 text-center text-slate-400">
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
