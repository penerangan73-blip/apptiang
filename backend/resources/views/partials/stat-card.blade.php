<div class="card p-5 group hover:border-cyan-500/50 transition-colors">
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="text-sm font-bold text-slate-400 uppercase tracking-wider">{{ $label }}</p>
            <p class="mt-2 text-3xl font-black text-white tracking-tight">{{ $value }}</p>
            <p class="mt-1 text-sm font-medium text-slate-500">{{ $description }}</p>
        </div>
        <div class="flex size-12 items-center justify-center rounded-xl bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 shadow-[0_0_15px_rgba(6,182,212,0.15)] group-hover:bg-cyan-500/20 transition-all">
            <i data-lucide="{{ $icon }}" class="icon"></i>
        </div>
    </div>
</div>
