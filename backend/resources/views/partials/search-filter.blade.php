<div class="card p-4">
    <form method="GET" action="{{ route('dashboard') }}" class="grid gap-3 md:grid-cols-[1fr_220px_auto]">
        <input class="field" name="query" value="{{ $query }}" placeholder="Cari jalan atau ID pelanggan">
        <select class="field" name="condition">
            <option value="">Semua kondisi</option>
            @foreach (['baik', 'rusak', 'perbaikan'] as $option)
                <option value="{{ $option }}" @selected($condition === $option)>{{ ucfirst($option) }}</option>
            @endforeach
        </select>
        <button class="btn btn-muted" type="submit">
            <i data-lucide="search" class="icon"></i>
            Filter
        </button>
    </form>
</div>
