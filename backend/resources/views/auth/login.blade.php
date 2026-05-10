@extends('layouts.app', ['title' => 'Login - Inventarisasi APJ Non APP'])

@section('body')
<main class="relative min-h-screen bg-slate-950 font-sans text-slate-100 overflow-hidden flex items-center justify-center p-4">
    <!-- Glowing background effects -->
    <div class="absolute top-[-10%] left-[-10%] w-[50%] h-[50%] bg-blue-600/20 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[50%] h-[50%] bg-cyan-500/20 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="relative w-full max-w-5xl grid gap-12 md:grid-cols-2 items-center z-10">
        <!-- Left Section: Branding -->
        <section class="flex flex-col justify-center text-center md:text-left">
            <div class="inline-flex items-center justify-center md:justify-start gap-4 mb-6">
                <div class="w-16 h-16 flex items-center justify-center transition-transform duration-300 hover:scale-105">
                    <img src="{{ asset('image/dishub.png') }}" alt="Logo Dishub" class="w-full h-full object-contain drop-shadow-lg">
                </div>
                <p class="text-sm font-bold uppercase tracking-[.25em] text-cyan-400 drop-shadow-md">Dinas Perhubungan</p>
            </div>
            <h1 class="text-4xl font-black leading-tight md:text-5xl lg:text-6xl tracking-tight text-white drop-shadow-sm">
                Inventarisasi <br/>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500">APJ Non APP.</span>
            </h1>
            <p class="mt-6 text-lg leading-relaxed text-slate-400 max-w-md mx-auto md:mx-0">
                Akses dashboard manajemen PJU untuk pemantauan aset dan laporan harian secara real-time.
            </p>
            
            <div class="mt-10 flex items-center justify-center md:justify-start gap-4 text-sm text-slate-500 font-medium">
                <div class="flex items-center gap-2">
                    <i data-lucide="shield-check" class="w-4 h-4 text-cyan-500"></i>
                    Aman & Terenkripsi
                </div>
                <div class="w-1.5 h-1.5 rounded-full bg-slate-700"></div>
                <div class="flex items-center gap-2">
                    <i data-lucide="zap" class="w-4 h-4 text-cyan-500"></i>
                    Performa Cepat
                </div>
            </div>

            <!-- Floating Image Card (Option 2) -->
            <div class="mt-12 relative max-w-[90%] mx-auto md:mx-0 group">
                <!-- Glowing shadow effect behind the image -->
                <div class="absolute inset-0 bg-blue-500/20 blur-xl rounded-2xl group-hover:bg-cyan-500/30 transition-colors duration-500"></div>
                
                <!-- Glassmorphic Image Frame -->
                <div class="relative rounded-2xl overflow-hidden border border-white/10 bg-slate-900/50 backdrop-blur-sm shadow-2xl transition-transform duration-500 hover:-translate-y-1">
                    <img src="{{ asset('image/bg-login.jpeg') }}" alt="Strategis Taktis Dinamis" class="w-full h-auto object-cover opacity-80 mix-blend-lighten group-hover:opacity-100 group-hover:mix-blend-normal transition-all duration-500">
                    
                    <!-- Text Overlay -->
                    <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-slate-950 via-slate-950/80 to-transparent p-5">
                        <p class="text-xs font-black text-cyan-400 tracking-[0.2em] uppercase">Fasilitas Utama</p>
                        <p class="mt-1 text-sm font-medium text-slate-300">Strategis • Taktis • Dinamis</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Right Section: Login Form -->
        <section class="relative">
            <!-- Glassmorphic Card -->
            <div class="relative bg-slate-900/60 backdrop-blur-2xl border border-white/10 p-8 md:p-10 rounded-3xl shadow-[0_0_40px_-10px_rgba(0,0,0,0.5)]">
                
                <h2 class="text-3xl font-bold text-white tracking-tight">Selamat Datang</h2>
                <p class="mt-2 text-sm text-slate-400">Silakan masuk menggunakan kredensial Anda.</p>

                @if (session('status'))
                    <div class="mt-6 flex items-center gap-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 px-4 py-3 text-sm font-medium text-emerald-400">
                        <i data-lucide="check-circle-2" class="w-5 h-5 flex-shrink-0"></i>
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mt-6 flex items-start gap-3 rounded-xl bg-red-500/10 border border-red-500/20 px-4 py-3 text-sm text-red-400">
                        <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0 mt-0.5"></i>
                        <div>{{ $errors->first() }}</div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-6">
                    @csrf
                    
                    <!-- Email Field -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-slate-300" for="email">Alamat Email</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i data-lucide="mail" class="w-5 h-5 text-slate-500 group-focus-within:text-cyan-400 transition-colors"></i>
                            </div>
                            <input 
                                class="w-full bg-slate-950/50 border border-slate-700/50 rounded-xl py-3 pl-12 pr-4 text-white placeholder:text-slate-600 focus:outline-none focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500 transition-all duration-300" 
                                id="email" 
                                name="email" 
                                type="email" 
                                placeholder="nama@perusahaan.com"
                                value="{{ old('email') }}" 
                                required 
                                autofocus>
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-semibold text-slate-300" for="password">Kata Sandi</label>
                        </div>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i data-lucide="lock" class="w-5 h-5 text-slate-500 group-focus-within:text-cyan-400 transition-colors"></i>
                            </div>
                            <input 
                                class="w-full bg-slate-950/50 border border-slate-700/50 rounded-xl py-3 pl-12 pr-4 text-white placeholder:text-slate-600 focus:outline-none focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500 transition-all duration-300" 
                                id="password" 
                                name="password" 
                                type="password" 
                                placeholder="••••••••"
                                required>
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <label class="flex items-center gap-3 cursor-pointer group w-max">
                        <div class="relative flex items-center justify-center">
                            <input type="checkbox" name="remember" value="1" class="peer appearance-none w-5 h-5 border-2 border-slate-600 rounded bg-slate-900/50 checked:bg-cyan-500 checked:border-cyan-500 transition-colors cursor-pointer">
                            <i data-lucide="check" class="absolute w-3.5 h-3.5 text-white opacity-0 peer-checked:opacity-100 pointer-events-none"></i>
                        </div>
                        <span class="text-sm font-medium text-slate-400 group-hover:text-slate-300 transition-colors">Ingat saya di perangkat ini</span>
                    </label>

                    <!-- Submit Button -->
                    <button class="w-full relative group overflow-hidden rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 px-4 py-3.5 text-sm font-bold text-white shadow-[0_0_20px_-5px_rgba(6,182,212,0.4)] transition-all hover:scale-[1.02] hover:shadow-[0_0_25px_-5px_rgba(6,182,212,0.6)] active:scale-[0.98]" type="submit">
                        <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300 ease-out"></div>
                        <span class="relative flex items-center justify-center gap-2">
                            Masuk ke Dashboard
                            <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                        </span>
                    </button>
                </form>



            </div>
        </section>
    </div>

    <!-- Init Lucide Icons -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
</main>
@endsection
