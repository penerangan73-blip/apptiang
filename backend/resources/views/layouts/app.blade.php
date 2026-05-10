<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'PJU Surveyor' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('image/dishub.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
        
        :root {
            --bg: #020617;
            --card: #0f172a;
            --panel: rgba(15, 23, 42, 0.6);
            --line: rgba(255, 255, 255, 0.08);
            --muted: #64748b;
            --primary: #06b6d4;
            --primary-dark: #0891b2;
            --danger: #ef4444;
            --success: #10b981;
            --warning: #f59e0b;
        }
        /* Premium Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(6, 182, 212, 0.3); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(6, 182, 212, 0.6); }

        body { background: var(--bg); color: #f1f5f9; font-family: 'Inter', sans-serif; overflow-x: hidden; }
        body:has(dialog[open]) { overflow: hidden; }
        dialog::backdrop { background: rgba(2, 6, 23, 0.8); backdrop-filter: blur(8px); }
        dialog { border: 0; padding: 0; background: transparent; color: #f1f5f9; width: min(960px, calc(100vw - 24px)); overflow: hidden; }
        .field { width: 100%; border: 1px solid var(--line); border-radius: 8px; padding: 10px 12px; background: rgba(15, 23, 42, 0.8); color: #f8fafc; transition: all 0.2s; }
        .field:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 2px rgba(6, 182, 212, 0.3); }
        .label { display: block; margin-bottom: 6px; font-size: 12px; font-weight: 800; color: #94a3b8; }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; border-radius: 8px; padding: 10px 14px; font-weight: 700; transition: all 0.2s; }
        .btn-primary { background: linear-gradient(to right, #06b6d4, #3b82f6); color: #fff; box-shadow: 0 4px 14px 0 rgba(6, 182, 212, 0.39); border: none; }
        .btn-primary:hover { box-shadow: 0 6px 20px rgba(6, 182, 212, 0.5); transform: translateY(-1px); }
        .btn-muted { background: rgba(255,255,255,0.05); color: #cbd5e1; border: 1px solid var(--line); }
        .btn-muted:hover { background: rgba(255,255,255,0.1); }
        .btn-danger { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }
        .btn-dark { background: #0f172a; color: #fff; border: 1px solid var(--line); }
        .card { border: 1px solid var(--line); border-radius: 16px; background: var(--card); box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5); overflow: hidden; }
        .dark-card { border: 1px solid var(--line); border-radius: 16px; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(12px); color: #fff; box-shadow: 0 16px 40px rgba(0, 0, 0, 0.6); overflow: hidden; }
        .glass-panel { border: 1px solid var(--line); background: rgba(15, 23, 42, 0.4); backdrop-filter: blur(10px); }
        .badge { border-radius: 999px; padding: 4px 10px; font-size: 12px; font-weight: 800; }
        .badge-baik { background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #34d399; }
        .badge-rusak { background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #f87171; }
        .badge-perbaikan { background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.2); color: #fbbf24; }
        .tab-panel[hidden] { display: none; }
        .tab-button.active { background: rgba(6, 182, 212, 0.15); color: #22d3ee; border-left: 3px solid #22d3ee; border-radius: 0 8px 8px 0; }
        .tab-button:not(.active) { color: #94a3b8; border-left: 3px solid transparent; }
        .mobile-tab.active { color: #22d3ee; }
        .modal-card { max-height: calc(100vh - 32px); overflow: auto; border-radius: 16px; background: #0f172a; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 24px 64px rgba(0, 0, 0, 0.8); }
        .icon { width: 18px; height: 18px; }
    </style>
</head>
<body>
    @yield('body')
</body>
</html>
