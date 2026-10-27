<div class="flex flex-col flex-1 overflow-y-auto">
    <!-- App Brand Logo -->
    <div class="px-6 py-5 flex items-center justify-between border-b border-slate-100">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
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
                <span class="text-[10px] font-medium text-slate-400 tracking-wider uppercase mt-0.5">E-Learning System</span>
            </div>
        </a>
        <button @click="sidebarOpen = false" class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Admin Identity Card Mini -->
    <div class="p-4">
        @php
            $nameParts = explode(' ', trim(auth()->user()->name ?? 'Admin'));
            $initials = count($nameParts) >= 2
                ? strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1))
                : strtoupper(substr(auth()->user()->name ?? 'AO', 0, 2));
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
                    <span class="px-1.5 py-0.5 text-[10px] font-semibold bg-blue-50 text-blue-700 rounded border border-blue-100/60 leading-none">
                        Admin
                    </span>
                </div>
                <p class="text-[11px] text-slate-500 font-medium truncate mt-0.5">
                    {{ auth()->user()->email }}
                </p>
            </div>
        </div>
    </div>

    <!-- Main Navigation Links -->
    <nav class="px-3.5 space-y-1.5 pb-6">
        @php
            $isDashboard = request()->routeIs('operator.dashboard') || request()->routeIs('admin.dashboard') || request()->routeIs('dashboard');
        @endphp

        <!-- Dashboard Item -->
        <a href="{{ route('dashboard') }}"
           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold text-sm transition-all {{ $isDashboard ? 'bg-blue-50 text-blue-600 shadow-xs border border-blue-100/60' : 'text-slate-600 hover:text-blue-600 hover:bg-slate-50' }} group">
            <svg class="w-5 h-5 {{ $isDashboard ? 'text-blue-600' : 'text-slate-400 group-hover:text-blue-600' }} transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect height="7" rx="2" width="7" x="3" y="3"></rect>
                <rect height="7" rx="2" width="7" x="14" y="3"></rect>
                <rect height="7" rx="2" width="7" x="14" y="14"></rect>
                <rect height="7" rx="2" width="7" x="3" y="14"></rect>
            </svg>
            <span>Dashboard</span>
        </a>

        <!-- Category: MANAJEMEN AKUN & DATA -->
        <div class="pt-4 pb-1.5 px-3">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Manajemen Akun &amp; Data</span>
        </div>

        <!-- Kelola Akun -->
        @php $isUsers = request()->routeIs('users.*'); @endphp
        <a href="{{ route('users.index') }}"
           class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-sm transition-colors group {{ $isUsers ? 'bg-blue-50 text-blue-600 font-semibold shadow-xs border border-blue-100/60' : 'text-slate-600 hover:text-blue-600 hover:bg-slate-50 font-medium' }}">
            <svg class="w-5 h-5 {{ $isUsers ? 'text-blue-600' : 'text-slate-400 group-hover:text-blue-600' }} transition-colors" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
            <span>Kelola Akun</span>
        </a>

        <!-- Data Guru -->
        @php $isGuru = request()->routeIs('guru.*'); @endphp
        <a href="{{ route('guru.index') }}"
           class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-sm transition-colors group {{ $isGuru ? 'bg-blue-50 text-blue-600 font-semibold shadow-xs border border-blue-100/60' : 'text-slate-600 hover:text-blue-600 hover:bg-slate-50 font-medium' }}">
            <svg class="w-5 h-5 {{ $isGuru ? 'text-blue-600' : 'text-slate-400 group-hover:text-blue-600' }} transition-colors" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2H9.17A3.001 3.001 0 0112 14z" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
            <span>Data Guru</span>
        </a>

        <!-- Data Siswa -->
        @php $isSiswa = request()->routeIs('siswa.*'); @endphp
        <a href="{{ route('siswa.index') }}"
           class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-sm transition-colors group {{ $isSiswa ? 'bg-blue-50 text-blue-600 font-semibold shadow-xs border border-blue-100/60' : 'text-slate-600 hover:text-blue-600 hover:bg-slate-50 font-medium' }}">
            <svg class="w-5 h-5 {{ $isSiswa ? 'text-blue-600' : 'text-slate-400 group-hover:text-blue-600' }} transition-colors" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
            <span>Data Siswa</span>
        </a>

        <!-- Kelola Kelas -->
        @php $isKelas = request()->routeIs('kelas.*'); @endphp
        <a href="{{ route('kelas.index') }}"
           class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-sm transition-colors group {{ $isKelas ? 'bg-blue-50 text-blue-600 font-semibold shadow-xs border border-blue-100/60' : 'text-slate-600 hover:text-blue-600 hover:bg-slate-50 font-medium' }}">
            <svg class="w-5 h-5 {{ $isKelas ? 'text-blue-600' : 'text-slate-400 group-hover:text-blue-600' }} transition-colors" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
            <span>Kelola Kelas</span>
        </a>

        <!-- Category: AKADEMIK & JADWAL -->
        <div class="pt-4 pb-1.5 px-3">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Akademik &amp; Jadwal</span>
        </div>

        <!-- Mata Pelajaran -->
        @php $isMapel = request()->routeIs('mapel.*'); @endphp
        <a href="{{ route('mapel.index') }}"
           class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-sm transition-colors group {{ $isMapel ? 'bg-blue-50 text-blue-600 font-semibold shadow-xs border border-blue-100/60' : 'text-slate-600 hover:text-blue-600 hover:bg-slate-50 font-medium' }}">
            <svg class="w-5 h-5 {{ $isMapel ? 'text-blue-600' : 'text-slate-400 group-hover:text-blue-600' }} transition-colors" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
            <span>Mata Pelajaran</span>
        </a>

        <!-- Kelola Jadwal (Foto) -->
        @php $isJadwal = request()->routeIs('admin.jadwal.*'); @endphp
        <a href="{{ route('admin.jadwal.index') }}"
           class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-sm transition-colors group {{ $isJadwal ? 'bg-blue-50 text-blue-600 font-semibold shadow-xs border border-blue-100/60' : 'text-slate-600 hover:text-blue-600 hover:bg-slate-50 font-medium' }}">
            <svg class="w-5 h-5 {{ $isJadwal ? 'text-blue-600' : 'text-slate-400 group-hover:text-blue-600' }} transition-colors" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
            <span>Kelola Jadwal (Foto)</span>
        </a>
    </nav>
</div>

<!-- Sidebar Bottom: Logout -->
<div class="p-4 border-t border-slate-100 shrink-0">
    <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
        @csrf
    </form>
    <button type="button"
            onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
            class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-slate-600 hover:text-rose-600 hover:bg-rose-50/80 font-medium text-sm transition-colors group">
        <svg class="w-5 h-5 text-slate-400 group-hover:text-rose-600 transition-colors" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" stroke-linecap="round" stroke-linejoin="round"></path>
        </svg>
        <span>Keluar</span>
    </button>
</div>