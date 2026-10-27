<div class="flex flex-col flex-1 overflow-y-auto">
    <!-- Brand Header -->
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- User Profile Card Mini -->
    <div class="p-4">
        @php
            $nameParts = explode(' ', trim(auth()->user()->name ?? 'User'));
            $initials = count($nameParts) >= 2 
                ? strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1))
                : strtoupper(substr(auth()->user()->name ?? 'U', 0, 2));
            $userRole = auth()->user()->role ?? 'user';
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
                        {{ ucfirst($userRole) }}
                    </span>
                </div>
                <p class="text-[11px] text-slate-500 font-medium truncate mt-0.5">
                    {{ auth()->user()->email }}
                </p>
            </div>
        </div>
    </div>

    <!-- Navigation Links -->
    <nav class="px-3.5 space-y-1.5 pb-6">
        @php $isDashboard = request()->routeIs('*.dashboard') || request()->routeIs('dashboard'); @endphp
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

        @if(auth()->user()->isOperator())
            <div class="pt-4 pb-1.5 px-3">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Manajemen Admin</span>
            </div>
            <a href="{{ route('siswa.index') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-slate-600 hover:text-blue-600 hover:bg-slate-50 font-medium text-sm transition-colors group">
                <svg class="w-5 h-5 text-slate-400 group-hover:text-blue-600 transition-colors" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <span>Data Siswa</span>
            </a>
        @endif

        <a href="{{ route('profile.show') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-slate-600 hover:text-blue-600 hover:bg-slate-50 font-medium text-sm transition-colors group">
            <svg class="w-5 h-5 text-slate-400 group-hover:text-blue-600 transition-colors" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
            <span>Profil</span>
        </a>
    </nav>
</div>

<!-- Sidebar Bottom: Logout -->
<div class="p-4 border-t border-slate-100 shrink-0">
    <form id="logout-form-default" method="POST" action="{{ route('logout') }}" class="hidden">
        @csrf
    </form>
    <button type="button" 
            onclick="event.preventDefault(); document.getElementById('logout-form-default').submit();"
            class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-slate-600 hover:text-rose-600 hover:bg-rose-50/80 font-medium text-sm transition-colors group">
        <svg class="w-5 h-5 text-slate-400 group-hover:text-rose-600 transition-colors" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" stroke-linecap="round" stroke-linejoin="round"></path>
        </svg>
        <span>Keluar</span>
    </button>
</div>
