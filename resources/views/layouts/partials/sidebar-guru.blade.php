<!-- Brand Header -->
<div class="sidebar-header px-5 py-5 flex items-center justify-between">
    <a href="{{ route('guru.dashboard') }}" class="flex items-center gap-3.5">
        <div class="sidebar-logo-box relative p-2.5 rounded-2xl text-white flex items-center justify-center">
            <!-- Icon Book -->
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            <span class="sidebar-logo-dot absolute -top-1 -right-1 w-3 h-3 rounded-full"></span>
        </div>
        <div>
            <span class="font-extrabold text-xl tracking-tight text-white block leading-tight">LearnPoint</span>
            <span class="text-[10px] text-blue-200/80 font-bold tracking-wider uppercase block">GURU PORTAL</span>
        </div>
    </a>
    <button @click="sidebarOpen = false" class="lg:hidden text-blue-300 hover:text-white">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
</div>

<!-- User Profile Card Guru -->
<div class="sidebar-profile-card mx-4 my-4 p-3 rounded-2xl flex items-center gap-3">
    @php
        $nameParts = explode(' ', auth()->user()->name);
        $initials = count($nameParts) >= 2 
            ? strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1))
            : strtoupper(substr(auth()->user()->name, 0, 2));
    @endphp
    <div class="sidebar-avatar w-10 h-10 rounded-full text-white font-bold text-sm flex items-center justify-center shrink-0 shadow">
        {{ $initials }}
    </div>
    <div class="min-w-0 flex-1">
        <h4 class="text-sm font-bold text-white truncate leading-snug">{{ auth()->user()->name }}</h4>
        <div class="flex items-center gap-1.5 mt-0.5">
            <span class="w-2 h-2 rounded-full bg-emerald-400 shrink-0"></span>
            <span class="text-[11px] font-medium text-emerald-300 truncate">Guru Pengajar</span>
        </div>
    </div>
</div>

<!-- Navigation Links Guru -->
<nav class="flex-1 px-4 py-2 space-y-1.5 overflow-y-auto">
    <!-- Dashboard -->
    @php $isDashboard = request()->routeIs('guru.dashboard') || request()->routeIs('dashboard'); @endphp
    <a 
        href="{{ route('guru.dashboard') }}" 
        class="sidebar-nav-link flex items-center gap-3.5 px-3.5 py-3 rounded-xl font-medium text-sm {{ $isDashboard ? 'sidebar-nav-link-active' : '' }}"
    >
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
        </svg>
        <span>Dashboard</span>
        @if($isDashboard)
            <span class="sidebar-active-indicator w-1.5 h-5 rounded-full ml-auto shrink-0"></span>
        @endif
    </a>
    <!-- Materi -->
    @php $isMateri = request()->routeIs('guru.materi'); @endphp
    <a 
        href="{{ route('guru.materi') }}" 
        class="sidebar-nav-link flex items-center gap-3.5 px-3.5 py-3 rounded-xl font-medium text-sm {{ $isMateri ? 'sidebar-nav-link-active' : '' }}"
    >
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
        </svg>
        <span>Materi</span>
        @if($isMateri)
            <span class="sidebar-active-indicator w-1.5 h-5 rounded-full ml-auto shrink-0"></span>
        @endif
    </a>

    <!-- Tugas -->
    @php $isTugas = request()->routeIs('guru.tugas'); @endphp
    <a 
        href="{{ route('guru.tugas') }}" 
        class="sidebar-nav-link flex items-center gap-3.5 px-3.5 py-3 rounded-xl font-medium text-sm {{ $isTugas ? 'sidebar-nav-link-active' : '' }}"
    >
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
        </svg>
        <span>Tugas</span>
        @if($isTugas)
            <span class="sidebar-active-indicator w-1.5 h-5 rounded-full ml-auto shrink-0"></span>
        @endif
    </a>

    <!-- Kuis & Ujian -->
    <a href="#" class="sidebar-nav-link flex items-center gap-3.5 px-3.5 py-3 rounded-xl font-medium text-sm">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
        <span>Kuis & Ujian</span>
    </a>

    <!-- Nilai -->
    <a href="#" class="sidebar-nav-link flex items-center gap-3.5 px-3.5 py-3 rounded-xl font-medium text-sm">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
        </svg>
        <span>Nilai</span>
    </a>

    <!-- Jadwal -->
    @php $isJadwal = request()->routeIs('guru.jadwal'); @endphp
    <a 
        href="{{ route('guru.jadwal') }}" 
        class="sidebar-nav-link flex items-center gap-3.5 px-3.5 py-3 rounded-xl font-medium text-sm {{ $isJadwal ? 'sidebar-nav-link-active' : '' }}"
    >
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <span>Jadwal</span>
        @if($isJadwal)
            <span class="sidebar-active-indicator w-1.5 h-5 rounded-full ml-auto shrink-0"></span>
        @endif
    </a>

    <!-- Notifikasi -->
    @php $isNotifikasi = request()->routeIs('notifikasi.index'); @endphp
    <a 
        href="{{ route('notifikasi.index') }}" 
        class="sidebar-nav-link flex items-center gap-3.5 px-3.5 py-3 rounded-xl font-medium text-sm {{ $isNotifikasi ? 'sidebar-nav-link-active' : '' }}"
    >
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 01-6 0v-1m6 0H9"/>
        </svg>
        <span>Notifikasi</span>
        @if($isNotifikasi)
            <span class="sidebar-active-indicator w-1.5 h-5 rounded-full ml-auto shrink-0"></span>
        @else
            <span class="sidebar-badge-amber w-5 h-5 font-extrabold text-[11px] rounded-full flex items-center justify-center ml-auto shrink-0 shadow-sm">0</span>
        @endif
    </a>
</nav>

<!-- Sidebar Footer Bottom Section -->
<div class="sidebar-footer px-4 py-4 space-y-1 mt-auto">
    <a href="{{ route('profile.show') }}" class="sidebar-nav-link flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl font-medium text-sm {{ request()->routeIs('profile.show') ? 'sidebar-nav-link-active' : '' }}">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        <span>Profil</span>
    </a>

    <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
        @csrf
    </form>
    <button 
        type="button" 
        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
        class="sidebar-nav-link w-full flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl font-medium text-sm text-left"
    >
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
        </svg>
        <span>Keluar</span>
    </button>
</div>
