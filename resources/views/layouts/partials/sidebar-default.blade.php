<!-- Brand Header -->
<div class="sidebar-header px-5 py-5 flex items-center justify-between">
    <a href="{{ route('dashboard') }}" class="flex items-center gap-3.5">
        <div class="sidebar-logo-box relative p-2.5 rounded-2xl text-white flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            <span class="sidebar-logo-dot absolute -top-1 -right-1 w-3 h-3 rounded-full"></span>
        </div>
        <div>
            <span class="font-extrabold text-xl tracking-tight text-white block leading-tight">LearnPoint</span>
            <span class="text-[10px] text-blue-200/80 font-bold tracking-wider uppercase block">E-LEARNING SMP</span>
        </div>
    </a>
    <button @click="sidebarOpen = false" class="lg:hidden text-blue-300 hover:text-white">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
</div>

<!-- User Profile Card -->
<div class="sidebar-profile-card mx-4 my-4 p-3 rounded-2xl flex items-center gap-3">
    @php
        $nameParts = explode(' ', auth()->user()->name);
        $initials = count($nameParts) >= 2 
            ? strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1))
            : strtoupper(substr(auth()->user()->name, 0, 2));
        $userRole = auth()->user()->role;
    @endphp
    <div class="sidebar-avatar w-10 h-10 rounded-full text-white font-bold text-sm flex items-center justify-center shrink-0 shadow">
        {{ $initials }}
    </div>
    <div class="min-w-0 flex-1">
        <h4 class="text-sm font-bold text-white truncate leading-snug">{{ auth()->user()->name }}</h4>
        <div class="flex items-center gap-1.5 mt-0.5">
            <span class="w-2 h-2 rounded-full bg-emerald-400 shrink-0"></span>
            <span class="text-[11px] font-medium text-emerald-300 truncate">{{ ucfirst($userRole) }}</span>
        </div>
    </div>
</div>

<!-- Navigation Links -->
<nav class="flex-1 px-4 py-2 space-y-1.5 overflow-y-auto">
    @php $isDashboard = request()->routeIs('*.dashboard') || request()->routeIs('dashboard'); @endphp
    <a 
        href="{{ route('dashboard') }}" 
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

    @if(auth()->user()->isOperator())
        <div class="pt-3 pb-1 border-t border-blue-900/40 mt-3">
            <span class="px-3 text-[10px] font-bold text-blue-300/60 uppercase tracking-wider">Manajemen Admin</span>
        </div>
        <a href="{{ route('siswa.index') }}" class="sidebar-nav-link flex items-center gap-3.5 px-3.5 py-3 rounded-xl font-medium text-sm">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            <span>Data Siswa</span>
        </a>
    @endif
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
