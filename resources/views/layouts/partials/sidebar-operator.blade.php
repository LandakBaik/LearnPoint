<!-- Brand Header -->
<div class="sidebar-header px-5 py-5 flex items-center justify-between">
    <a href="{{ route('dashboard') }}" class="flex items-center gap-3.5">
        <div class="sidebar-logo-box relative p-2.5 rounded-2xl text-white flex items-center justify-center shadow-lg shadow-indigo-500/30">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            <span class="sidebar-logo-dot absolute -top-1 -right-1 w-3 h-3 rounded-full bg-emerald-400"></span>
        </div>
        <div>
            <span class="font-extrabold text-xl tracking-tight text-white block leading-tight">LearnPoint</span>
            <span class="text-[10px] text-purple-200/90 font-bold tracking-wider uppercase block">PANEL OPERATOR / ADMIN</span>
        </div>
    </a>
    <button @click="sidebarOpen = false" class="lg:hidden text-blue-300 hover:text-white">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</div>

<!-- User Profile Card -->
<div class="sidebar-profile-card mx-4 my-3 p-3 rounded-2xl flex items-center gap-3">
    @php
    $nameParts = explode(' ', auth()->user()->name);
    $initials = count($nameParts) >= 2
    ? strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1))
    : strtoupper(substr(auth()->user()->name, 0, 2));
    @endphp
    <div class="sidebar-avatar w-10 h-10 rounded-full text-white font-bold text-sm flex items-center justify-center shrink-0 shadow-md">
        {{ $initials }}
    </div>
    <div class="min-w-0 flex-1">
        <h4 class="text-sm font-bold text-white truncate leading-snug">{{ auth()->user()->name }}</h4>
        <div class="flex items-center gap-1.5 mt-0.5">
            <span class="w-2 h-2 rounded-full bg-emerald-400 shrink-0"></span>
            <span class="text-[11px] font-medium text-emerald-300 truncate">Administrator</span>
        </div>
    </div>
</div>

<!-- Navigation Links -->
<nav class="flex-1 px-4 py-2 space-y-1.5 overflow-y-auto">
    @php $isDashboard = request()->routeIs('operator.dashboard') || request()->routeIs('admin.dashboard') || request()->routeIs('dashboard'); @endphp
    <a
        href="{{ route('dashboard') }}"
        class="sidebar-nav-link flex items-center gap-3.5 px-3.5 py-3 rounded-xl font-medium text-sm {{ $isDashboard ? 'sidebar-nav-link-active' : '' }}">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
        </svg>
        <span>Dashboard</span>
        @if($isDashboard)
        <span class="sidebar-active-indicator w-1.5 h-5 rounded-full ml-auto shrink-0"></span>
        @endif
    </a>

    <div class="pt-3 pb-1 border-t border-blue-900/40 mt-3">
        <span class="px-3 text-[10px] font-bold text-blue-300/70 uppercase tracking-wider">Manajemen Akun & Data</span>
    </div>

    <!-- Kelola Akun -->
    @php $isUsers = request()->routeIs('users.*'); @endphp
    <a
        href="{{ route('users.index') }}"
        class="sidebar-nav-link flex items-center gap-3.5 px-3.5 py-3 rounded-xl font-medium text-sm {{ $isUsers ? 'sidebar-nav-link-active' : '' }}">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
        <span>Kelola Akun</span>
        @if($isUsers)
        <span class="sidebar-active-indicator w-1.5 h-5 rounded-full ml-auto shrink-0"></span>
        @endif
    </a>

    <!-- Data Guru -->
    @php $isGuru = request()->routeIs('guru.*'); @endphp
    <a
        href="{{ route('guru.index') }}"
        class="sidebar-nav-link flex items-center gap-3.5 px-3.5 py-3 rounded-xl font-medium text-sm {{ $isGuru ? 'sidebar-nav-link-active' : '' }}">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
        </svg>
        <span>Data Guru</span>
        @if($isGuru)
        <span class="sidebar-active-indicator w-1.5 h-5 rounded-full ml-auto shrink-0"></span>
        @endif
    </a>

    <!-- Data Siswa -->
    @php $isSiswa = request()->routeIs('siswa.*'); @endphp
    <a
        href="{{ route('siswa.index') }}"
        class="sidebar-nav-link flex items-center gap-3.5 px-3.5 py-3 rounded-xl font-medium text-sm {{ $isSiswa ? 'sidebar-nav-link-active' : '' }}">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        <span>Data Siswa</span>
        @if($isSiswa)
        <span class="sidebar-active-indicator w-1.5 h-5 rounded-full ml-auto shrink-0"></span>
        @endif
    </a>

    <div class="pt-3 pb-1 border-t border-blue-900/40 mt-3">
        <span class="px-3 text-[10px] font-bold text-blue-300/70 uppercase tracking-wider">Akademik & Jadwal</span>
    </div>

    <!-- Kelola Kelas -->
    @php $isKelas = request()->routeIs('kelas.*'); @endphp
    <a
        href="{{ route('kelas.index') }}"
        class="sidebar-nav-link flex items-center gap-3.5 px-3.5 py-3 rounded-xl font-medium text-sm {{ $isKelas ? 'sidebar-nav-link-active' : '' }}">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m3 0h1m-1-4h.01M9 16h.01M9 12h.01M9 8h.01M15 16h.01M15 12h.01M15 8h.01" />
        </svg>
        <span>Kelola Kelas</span>
        @if($isKelas)
        <span class="sidebar-active-indicator w-1.5 h-5 rounded-full ml-auto shrink-0"></span>
        @endif
    </a>

    <!-- Kelola Mapel -->
    @php $isMapel = request()->routeIs('mapel.*'); @endphp
    <a
        href="{{ route('mapel.index') }}"
        class="sidebar-nav-link flex items-center gap-3.5 px-3.5 py-3 rounded-xl font-medium text-sm {{ $isMapel ? 'sidebar-nav-link-active' : '' }}">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
        </svg>
        <span>Mata Pelajaran</span>
        @if($isMapel)
        <span class="sidebar-active-indicator w-1.5 h-5 rounded-full ml-auto shrink-0"></span>
        @endif
    </a>

    <!-- Kelola Jadwal (Foto) -->
    @php $isJadwal = request()->routeIs('admin.jadwal.*'); @endphp
    <a
        href="{{ route('admin.jadwal.index') }}"
        class="sidebar-nav-link flex items-center gap-3.5 px-3.5 py-3 rounded-xl font-medium text-sm {{ $isJadwal ? 'sidebar-nav-link-active' : '' }}">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <span>Kelola Jadwal (Foto)</span>
        @if($isJadwal)
        <span class="sidebar-active-indicator w-1.5 h-5 rounded-full ml-auto shrink-0"></span>
        @endif
    </a>
</nav>

<!-- Sidebar Footer Bottom Section -->
<div class="sidebar-footer px-4 py-4 space-y-1 mt-auto">
    <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
        @csrf
    </form>
    <button
        type="button"
        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
        class="sidebar-nav-link w-full flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl font-medium text-sm text-left hover:bg-rose-500/20 text-rose-200 hover:text-white transition-colors">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
        </svg>
        <span>Keluar</span>
    </button>
</div>