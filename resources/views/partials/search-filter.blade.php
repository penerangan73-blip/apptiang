<div class="card p-4">
    <form id="search-filter-form" method="GET" action="{{ route($actionRoute ?? 'poles.index') }}" class="grid gap-3 md:grid-cols-[1fr_220px_auto]">
        <input class="field" id="search-query" name="query" value="{{ $query }}" placeholder="Cari semua kolom data tiang...">
        <select class="field" id="search-condition" name="condition">
            <option value="">Semua kondisi tiang</option>
            @foreach (['kokoh' => 'Kokoh', 'keropos' => 'Keropos', 'miring' => 'Miring', 'berkarat' => 'Berkarat'] as $option => $text)
                <option value="{{ $option }}" @selected($condition === $option)>{{ $text }}</option>
            @endforeach
        </select>
        <button class="btn btn-primary" type="submit">
            <i data-lucide="search" class="icon"></i>
            Cari
        </button>
    </form>
</div>

<script>
(function() {
    const form = document.getElementById('search-filter-form');
    const conditionSelect = document.getElementById('search-condition');

    // Auto-submit langsung saat pilih kondisi
    conditionSelect?.addEventListener('change', () => form.submit());
})();
</script>
