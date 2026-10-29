<div class="flex flex-col flex-1 overflow-y-auto">
    <!-- App Brand Logo -->
    <div class="px-6 py-5 flex items-center justify-between border-b border-slate-100">
        <a href="{{ route('guru.dashboard') }}" class="flex items-center gap-3">
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
                <span class="text-[10px] font-medium text-slate-400 tracking-wider uppercase mt-0.5">Portal Guru</span>
            </div>
        </a>
        <button @click="sidebarOpen = false" class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Guru Identity Card Mini -->
    <div class="p-4">
        @php
            $nameParts = explode(' ', trim(auth()->user()->name ?? 'Guru'));
            $initials = count($nameParts) >= 2
                ? strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1))
                : strtoupper(substr(auth()->user()->name ?? 'G', 0, 2));
        @endphp
        <div class="p-3 rounded-xl bg-slate-50/80 border border-slate-200/80 flex items-center gap-3 hover:bg-slate-100/60 transition-colors cursor-pointer group">
            <div class="w-9 h-9 rounded-lg bg-blue-600 text-white font-bold text-xs flex items-center justify-center shadow-xs shrink-0">
                {{ $initials }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-1">
                    <h2 class="text-xs font-bold text-slate-800 truncate leading-tight group-hover:text-blue-600 transition-colors">
                        {{ auth()->user()->name }}
                    </h2>
                    <span class="px-1.5 py-0.5 text-[10px] font-semibold bg-emerald-50 text-emerald-700 rounded border border-emerald-200/60 leading-none">
                        Guru
                    </span>
                </div>
                <p class="text-[11px] text-slate-500 font-medium truncate mt-0.5">
                    {{ auth()->user()->email }}
                </p>
            </div>
        </div>
    </div>

    <!-- Main Navigation Links Guru -->
    <nav class="px-3.5 space-y-1.5 pb-6">
        @php
            $isDashboard = request()->routeIs('guru.dashboard') || request()->routeIs('dashboard');
        @endphp

        <!-- Dashboard Item -->
        <a href="{{ route('guru.dashboard') }}"
           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold text-sm transition-all {{ $isDashboard ? 'bg-blue-50 text-blue-600 shadow-xs border border-blue-100/60' : 'text-slate-600 hover:text-blue-600 hover:bg-slate-50' }} group">
            <svg class="w-5 h-5 {{ $isDashboard ? 'text-blue-600' : 'text-slate-400 group-hover:text-blue-600' }} transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect height="7" rx="2" width="7" x="3" y="3"></rect>
                <rect height="7" rx="2" width="7" x="14" y="3"></rect>
                <rect height="7" rx="2" width="7" x="14" y="14"></rect>
                <rect height="7" rx="2" width="7" x="3" y="14"></rect>
            </svg>
            <span>Dashboard</span>
        </a>

        <!-- Category: PEMBELAJARAN -->
        <div class="pt-4 pb-1.5 px-3">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Pembelajaran</span>
        </div>

        <!-- Materi -->
        @php $isMateri = request()->routeIs('guru.materi'); @endphp
        <a href="{{ route('guru.materi') }}"
           class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-sm transition-colors group {{ $isMateri ? 'bg-blue-50 text-blue-600 font-semibold shadow-xs border border-blue-100/60' : 'text-slate-600 hover:text-blue-600 hover:bg-slate-50 font-medium' }}">
            <svg class="w-5 h-5 {{ $isMateri ? 'text-blue-600' : 'text-slate-400 group-hover:text-blue-600' }} transition-colors" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
            <span>Materi</span>
        </a>

        <!-- Tugas -->
        @php $isTugas = request()->routeIs('guru.tugas'); @endphp
        <a href="{{ route('guru.tugas') }}"
           class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-sm transition-colors group {{ $isTugas ? 'bg-blue-50 text-blue-600 font-semibold shadow-xs border border-blue-100/60' : 'text-slate-600 hover:text-blue-600 hover:bg-slate-50 font-medium' }}">
            <svg class="w-5 h-5 {{ $isTugas ? 'text-blue-600' : 'text-slate-400 group-hover:text-blue-600' }} transition-colors" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
            <span>Tugas</span>
        </a>

        <!-- Kuis -->
        @php $isKuis = request()->routeIs('guru.kuis*'); @endphp
        <a href="{{ route('guru.kuis.index') }}"
           class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-sm transition-colors group {{ $isKuis ? 'bg-blue-50 text-blue-600 font-semibold shadow-xs border border-blue-100/60' : 'text-slate-600 hover:text-blue-600 hover:bg-slate-50 font-medium' }}">
            <svg class="w-5 h-5 {{ $isKuis ? 'text-blue-600' : 'text-slate-400 group-hover:text-blue-600' }} transition-colors" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
            <span>Kuis</span>
        </a>

        <!-- Ujian -->
        @php $isUjian = request()->routeIs('guru.ujian*'); @endphp
        <a href="{{ route('guru.ujian.index') }}"
           class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-sm transition-colors group {{ $isUjian ? 'bg-blue-50 text-blue-600 font-semibold shadow-xs border border-blue-100/60' : 'text-slate-600 hover:text-blue-600 hover:bg-slate-50 font-medium' }}">
            <svg class="w-5 h-5 {{ $isUjian ? 'text-blue-600' : 'text-slate-400 group-hover:text-blue-600' }} transition-colors" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
            <span>Ujian</span>
        </a>

        <!-- Category: AKADEMIK & LAINNYA -->
        <div class="pt-4 pb-1.5 px-3">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Akademik &amp; Lainnya</span>
        </div>

        <!-- Jadwal -->
        @php $isJadwal = request()->routeIs('guru.jadwal'); @endphp
        <a href="{{ route('guru.jadwal') }}"
           class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-sm transition-colors group {{ $isJadwal ? 'bg-blue-50 text-blue-600 font-semibold shadow-xs border border-blue-100/60' : 'text-slate-600 hover:text-blue-600 hover:bg-slate-50 font-medium' }}">
            <svg class="w-5 h-5 {{ $isJadwal ? 'text-blue-600' : 'text-slate-400 group-hover:text-blue-600' }} transition-colors" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
            <span>Jadwal</span>
        </a>

        <!-- Notifikasi -->
        @php $isNotifikasi = request()->routeIs('notifikasi.index'); @endphp
        <a href="{{ route('notifikasi.index') }}"
           class="flex items-center justify-between px-3.5 py-2 rounded-xl text-sm transition-colors group {{ $isNotifikasi ? 'bg-blue-50 text-blue-600 font-semibold shadow-xs border border-blue-100/60' : 'text-slate-600 hover:text-blue-600 hover:bg-slate-50 font-medium' }}">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 {{ $isNotifikasi ? 'text-blue-600' : 'text-slate-400 group-hover:text-blue-600' }} transition-colors" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 01-6 0v-1m6 0H9" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <span>Notifikasi</span>
            </div>
            @php
                $sidebarUnreadCount = auth()->user()?->unreadNotifikasiCount() ?? 0;
            @endphp
            @if($sidebarUnreadCount > 0)
                <span class="min-w-5 h-5 px-1.5 font-bold text-[10px] rounded-full bg-rose-500 text-white flex items-center justify-center shadow-xs">
                    {{ $sidebarUnreadCount > 99 ? '99+' : $sidebarUnreadCount }}
                </span>
            @endif
        </a>

        <!-- Profil -->
        @php $isProfile = request()->routeIs('profile.show'); @endphp
        <a href="{{ route('profile.show') }}"
           class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-sm transition-colors group {{ $isProfile ? 'bg-blue-50 text-blue-600 font-semibold shadow-xs border border-blue-100/60' : 'text-slate-600 hover:text-blue-600 hover:bg-slate-50 font-medium' }}">
            <svg class="w-5 h-5 {{ $isProfile ? 'text-blue-600' : 'text-slate-400 group-hover:text-blue-600' }} transition-colors" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
            <span>Profil Akun</span>
        </a>
    </nav>
</div>

<!-- Sidebar Bottom: Logout -->
<div class="p-4 border-t border-slate-100 shrink-0">
    <form id="logout-form-guru" method="POST" action="{{ route('logout') }}" class="hidden">
        @csrf
    </form>
    <button type="button"
            onclick="event.preventDefault(); document.getElementById('logout-form-guru').submit();"
            class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-slate-600 hover:text-rose-600 hover:bg-rose-50/80 font-medium text-sm transition-colors group">
        <svg class="w-5 h-5 text-slate-400 group-hover:text-rose-600 transition-colors" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" stroke-linecap="round" stroke-linejoin="round"></path>
        </svg>
        <span>Keluar</span>
    </button>
</div>
