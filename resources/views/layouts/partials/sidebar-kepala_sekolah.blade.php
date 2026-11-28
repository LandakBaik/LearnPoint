<div class="flex flex-col flex-1 overflow-y-auto">

    {{-- App Brand Logo --}}
    <div class="px-6 py-5 flex items-center justify-between border-b border-slate-100">
        <a href="{{ route('kepala_sekolah.dashboard') }}" class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-blue-700 to-blue-500 flex items-center justify-center text-white shadow-md shadow-blue-500/25 shrink-0 ring-1 ring-white/20">
                <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                    <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                    <circle cx="12" cy="15" r="1.5" fill="currentColor"></circle>
                </svg>
            </div>

            <div class="flex flex-col">
                <div class="flex items-center gap-1 leading-none">
                    <span class="text-lg font-extrabold tracking-tight text-slate-900">Learn</span>
                    <span class="text-lg font-extrabold tracking-tight text-blue-600">Point</span>
                </div>
                <span class="text-[10px] font-medium text-slate-400 tracking-wider uppercase mt-0.5">
                    E-Learning System
                </span>
            </div>
        </a>

        {{-- Close Sidebar Mobile --}}
        <button
            @click="sidebarOpen = false"
            class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    {{-- Kepala Sekolah Identity Card --}}
    <div class="p-4">
        @php
        $userName = auth()->user()->name ?? 'Kepala Sekolah';
        $nameParts = explode(' ', trim($userName));

        $initials = count($nameParts) >= 2
        ? strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1))
        : strtoupper(substr($userName, 0, 2));
        @endphp

        <div class="p-3 rounded-xl bg-slate-50/80 border border-slate-200/80 flex items-center gap-3 hover:bg-slate-100/60 transition-colors cursor-pointer group">

            <div class="w-9 h-9 rounded-lg bg-blue-600 text-white font-bold text-xs flex items-center justify-center shadow-xs shrink-0">
                {{ $initials }}
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-1">
                    <h2 class="text-xs font-bold text-slate-800 truncate leading-tight group-hover:text-blue-600 transition-colors">
                        {{ $userName }}
                    </h2>

                    <span class="px-1.5 py-0.5 text-[10px] font-semibold bg-blue-50 text-blue-700 rounded border border-blue-100/60 leading-none shrink-0">
                        Kepala Sekolah
                    </span>
                </div>

                <p class="text-[11px] text-slate-500 font-medium truncate mt-0.5">
                    {{ auth()->user()->email }}
                </p>
            </div>
        </div>
    </div>

    {{-- Main Navigation --}}
    <nav class="px-3.5 space-y-1.5 pb-6">

        {{-- Dashboard --}}
        @php
        $isDashboard = request()->routeIs('kepala_sekolah.dashboard');
        @endphp

        <a href="{{ route('kepala_sekolah.dashboard') }}"
            class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold text-sm transition-all
            {{ $isDashboard ? 'bg-blue-50 text-blue-600 shadow-xs border border-blue-100/60' : 'text-slate-600 hover:text-blue-600 hover:bg-slate-50' }} group">

            <svg class="w-5 h-5 {{ $isDashboard ? 'text-blue-600' : 'text-slate-400 group-hover:text-blue-600' }} transition-colors"
                fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect height="7" rx="2" width="7" x="3" y="3"></rect>
                <rect height="7" rx="2" width="7" x="14" y="3"></rect>
                <rect height="7" rx="2" width="7" x="14" y="14"></rect>
                <rect height="7" rx="2" width="7" x="3" y="14"></rect>
            </svg>

            <span>Dashboard</span>
        </a>

        {{-- Category: Laporan & Monitoring --}}
        <div class="pt-4 mt-2 pb-1.5 px-3 border-t border-slate-100">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                Laporan &amp; Monitoring
            </span>
        </div>

        {{-- Rekap Nilai --}}
        @php
        $isRekapNilai = request()->routeIs('kepala_sekolah.rekap-nilai.*');
        @endphp

        <a href="{{ route('kepala_sekolah.rekap-nilai.index') }}"
            title="Rekap Nilai"
            class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-sm transition-colors group {{ $isRekapNilai ? 'bg-blue-50 text-blue-600 font-semibold shadow-xs border border-blue-100/60' : 'text-slate-600 hover:text-blue-600 hover:bg-slate-50 font-medium' }}">

        <svg class="w-5 h-5 {{ $isRekapNilai ? 'text-blue-600' : 'text-slate-400 group-hover:text-blue-600' }} transition-colors"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
            viewBox="0 0 24 24">
            <path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 01 2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a1 1 0 01-2 2z"
                stroke-linecap="round"
                stroke-linejoin="round"></path>
        </svg>

        <span>Rekap Nilai</span>
        </a>
        {{-- Monitoring Aktivitas Guru --}}
        @php
        $isMonitoringGuru = request()->routeIs('kepala_sekolah.monitoring-guru.*');
        @endphp

        <a href="{{ route('kepala_sekolah.monitoring-guru.index') }}"
            class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-sm transition-colors group {{ $isMonitoringGuru ? 'bg-blue-50 text-blue-600 font-semibold shadow-xs border border-blue-100/60' : 'text-slate-600 hover:text-blue-600 hover:bg-slate-50 font-medium' }}">

            <svg class="w-5 h-5 {{ $isMonitoringGuru ? 'text-blue-600' : 'text-slate-400 group-hover:text-blue-600' }} transition-colors"
                fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2  0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2  0 002 2h2a2 2 0 002-2m0 0V5a2 2  0 012-2h2a2 2  0 012 2v14a2 2  0 01-2 2h-2a2 2  0 01-2-2z"
                    stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>

            <span>Monitoring Guru</span>
        </a>

        {{-- Category Profil --}}
        <div class="pt-4 mt-2 pb-1.5 px-3 border-t border-slate-100">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                Akun
            </span>
        </div>

        {{-- Profil --}}
        @php
        $isProfil = request()->routeIs('profile.show');
        @endphp

        <a href="{{ route('profile.show') }}"
            class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-sm transition-colors group
            {{ $isProfil ? 'bg-blue-50 text-blue-600 font-semibold shadow-xs border border-blue-100/60' : 'text-slate-600 hover:text-blue-600 hover:bg-slate-50 font-medium' }}">

            <svg class="w-5 h-5 {{ $isProfil ? 'text-blue-600' : 'text-slate-400 group-hover:text-blue-600' }} transition-colors"
                fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <circle cx="12" cy="8" r="4" />
                <path d="M5 21a7 7 0 0114 0" />
            </svg>

            <span>Profil</span>
        </a>

    </nav>
</div>

{{-- Sidebar Bottom: Logout --}}
<div class="p-4 border-t border-slate-100 shrink-0">

    <form id="logout-form-kepsek" method="POST" action="{{ route('logout') }}" class="hidden">
        @csrf
    </form>

    <button type="button"
        onclick="event.preventDefault(); document.getElementById('logout-form-kepsek').submit();"
        class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-slate-600 hover:text-rose-600 hover:bg-rose-50/80 font-medium text-sm transition-colors group">

        <svg class="w-5 h-5 text-slate-400 group-hover:text-rose-600 transition-colors"
            fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3  0 013 3v1"
                stroke-linecap="round" stroke-linejoin="round"></path>
        </svg>

        <span>Keluar</span>
    </button>
</div>