<!-- Brand Header -->
<div class="sidebar-header px-5 py-5 flex items-center justify-between">
    @php
        $dashboardRoute = auth()->user()->role === 'guru' ? 'guru.dashboard' : 'siswa.dashboard';
        $portalTitle = auth()->user()->role === 'guru' ? 'GURU PORTAL' : 'SISWA PORTAL';
    @endphp
    <a href="{{ route($dashboardRoute) }}" class="flex items-center gap-3.5">
        <div class="sidebar-logo-box relative p-2.5 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-600/30">
            <!-- Icon Book -->
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            <span class="absolute -top-1 -right-1 w-3 h-3 bg-amber-400 rounded-full border-2 border-[#0B2559]"></span>
        </div>
        <div>
            <span class="font-extrabold text-xl tracking-tight text-white block leading-tight">LearnPoint</span>
            <span class="text-[10px] text-blue-200/80 font-bold tracking-wider uppercase block">{{ $portalTitle }}</span>
        </div>
    </a>
    <button @click="sidebarOpen = false" class="lg:hidden text-blue-300 hover:text-white">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
</div>

<!-- User Profile Card -->
<div class="mx-4 my-4 p-3 rounded-2xl bg-white/10 backdrop-blur-md flex items-center gap-3 border border-white/10">
    @php
        $nameParts = explode(' ', auth()->user()->name);
        $initials = count($nameParts) >= 2 
            ? strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1))
            : strtoupper(substr(auth()->user()->name, 0, 2));
    @endphp
    <div class="w-10 h-10 rounded-full bg-blue-600 text-white font-bold text-sm flex items-center justify-center shrink-0 shadow-md">
        {{ $initials }}
    </div>
    <div class="min-w-0 flex-1">
        <h4 class="text-sm font-bold text-white truncate leading-snug">{{ auth()->user()->name }}</h4>
        <div class="flex items-center gap-1.5 mt-0.5">
            <span class="w-2 h-2 rounded-full bg-emerald-400 shrink-0"></span>
            <span class="text-[11px] font-medium text-emerald-300 truncate capitalize">{{ auth()->user()->role ?? 'Siswa' }}</span>
        </div>
    </div>
</div>

<!-- Navigation Links -->
<nav class="flex-1 px-4 py-2 space-y-2 overflow-y-auto">
    {{-- Class styling untuk tombol biasa vs aktif --}}
    @php
        $activeClass = 'bg-blue-600 text-white font-bold shadow-lg shadow-blue-600/30';
        $inactiveClass = 'text-blue-100/70 hover:bg-white/10 hover:text-white font-medium';
    @endphp

    <!-- Dashboard -->
    @php $isDashboard = request()->routeIs('siswa.dashboard') || request()->routeIs('guru.dashboard') || request()->routeIs('dashboard'); @endphp
    <a 
        href="{{ route($dashboardRoute) }}" 
        class="flex items-center gap-3.5 px-4 py-3 rounded-2xl text-sm transition-all relative {{ $isDashboard ? $activeClass : $inactiveClass }}"
    >
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
        </svg>
        <span class="text-base font-semibold">Dashboard</span>
        @if($isDashboard)
            <!-- Indikator putih lonjong vertikal di sebelah kanan -->
            <span class="w-2 h-6 bg-white rounded-full ml-auto shrink-0 shadow-sm"></span>
        @endif
    </a>

    <!-- Materi -->
    @php $isMateri = request()->routeIs('siswa.materi*') || request()->routeIs('guru.materi*'); @endphp
    <a 
        href="{{ Route::has('siswa.materi') ? route('siswa.materi') : '#' }}" 
        class="flex items-center gap-3.5 px-4 py-3 rounded-2xl text-sm transition-all relative {{ $isMateri ? $activeClass : $inactiveClass }}"
    >
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
        </svg>
        <span class="text-base font-semibold">Materi</span>
        @if($isMateri)
            <span class="w-2 h-6 bg-white rounded-full ml-auto shrink-0 shadow-sm"></span>
        @endif
    </a>

    <!-- Tugas -->
    @php $isTugas = request()->routeIs('siswa.tugas*') || request()->routeIs('guru.tugas*'); @endphp
    <a 
        href="{{ Route::has('siswa.tugas') ? route('siswa.tugas') : '#' }}" 
        class="flex items-center gap-3.5 px-4 py-3 rounded-2xl text-sm transition-all relative {{ $isTugas ? $activeClass : $inactiveClass }}"
    >
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
        </svg>
        <span class="text-base font-semibold">Tugas</span>
        @if($isTugas)
            <span class="w-2 h-6 bg-white rounded-full ml-auto shrink-0 shadow-sm"></span>
        @endif
    </a>

    <!-- Kuis & Ujian -->
    @php $isKuis = request()->routeIs('siswa.kuis*') || request()->routeIs('guru.kuis*'); @endphp
    <a 
        href="{{ Route::has('siswa.kuis') ? route('siswa.kuis') : '#' }}" 
        class="flex items-center gap-3.5 px-4 py-3 rounded-2xl text-sm transition-all relative {{ $isKuis ? $activeClass : $inactiveClass }}"
    >
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
        <span class="text-base font-semibold">Kuis & Ujian</span>
        @if($isKuis)
            <span class="w-2 h-6 bg-white rounded-full ml-auto shrink-0 shadow-sm"></span>
        @endif
    </a>

    <!-- Nilai -->
    @php $isNilai = request()->routeIs('siswa.nilai*') || request()->routeIs('guru.nilai*'); @endphp
    <a 
        href="{{ Route::has('siswa.nilai') ? route('siswa.nilai') : '#' }}" 
        class="flex items-center gap-3.5 px-4 py-3 rounded-2xl text-sm transition-all relative {{ $isNilai ? $activeClass : $inactiveClass }}"
    >
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
        </svg>
        <span class="text-base font-semibold">Nilai</span>
        @if($isNilai)
            <span class="w-2 h-6 bg-white rounded-full ml-auto shrink-0 shadow-sm"></span>
        @endif
    </a>

    <!-- Jadwal -->
    @php $isJadwal = request()->routeIs('siswa.jadwal*') || request()->routeIs('guru.jadwal*'); @endphp
    <a 
        href="{{ Route::has('siswa.jadwal') ? route('siswa.jadwal') : '#' }}" 
        class="flex items-center gap-3.5 px-4 py-3 rounded-2xl text-sm transition-all relative {{ $isJadwal ? $activeClass : $inactiveClass }}"
    >
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <span class="text-base font-semibold">Jadwal</span>
        @if($isJadwal)
            <span class="w-2 h-6 bg-white rounded-full ml-auto shrink-0 shadow-sm"></span>
        @endif
    </a>

    <!-- Notifikasi -->
    @php $isNotifikasi = request()->routeIs('notifikasi*'); @endphp
    <a 
        href="{{ Route::has('notifikasi.index') ? route('notifikasi.index') : '#' }}" 
        class="flex items-center gap-3.5 px-4 py-3 rounded-2xl text-sm transition-all relative {{ $isNotifikasi ? $activeClass : $inactiveClass }}"
    >
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 01-6 0v-1m6 0H9"/>
        </svg>
        <span class="text-base font-semibold">Notifikasi</span>
        @if($isNotifikasi)
            <span class="w-2 h-6 bg-white rounded-full ml-auto shrink-0 shadow-sm"></span>
        @else
            <span class="w-6 h-6 bg-amber-500 text-white font-extrabold text-[11px] rounded-full flex items-center justify-center ml-auto shrink-0 shadow-sm">0</span>
        @endif
    </a>
</nav>

<!-- Sidebar Footer Bottom Section -->
<div class="px-4 py-4 space-y-1 mt-auto">
    @php $isProfile = request()->routeIs('profile*'); @endphp
    <a 
        href="{{ Route::has('profile.show') ? route('profile.show') : '#' }}" 
        class="flex items-center gap-3.5 px-4 py-2.5 rounded-2xl text-sm transition-all {{ $isProfile ? $activeClass : $inactiveClass }}"
    >
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        <span class="text-base font-semibold">Profil</span>
        @if($isProfile)
            <span class="w-2 h-6 bg-white rounded-full ml-auto shrink-0 shadow-sm"></span>
        @endif
    </a>

    <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
        @csrf
    </form>
    <button 
        type="button" 
        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
        class="w-full flex items-center gap-3.5 px-4 py-2.5 rounded-2xl text-sm transition-all text-blue-100/70 hover:bg-white/10 hover:text-white font-medium text-left"
    >
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
        </svg>
        <span class="text-base font-semibold">Keluar</span>
    </button>
</div>