<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'E-Learning Dashboard - LearnPoint')</title>

    <!-- Google Font: Plus Jakarta Sans -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Tailwind CSS v3 CDN -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        navy: {
                            900: '#0F1E36',
                            800: '#172B4D',
                        },
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Custom Typography and Reset Styles -->
    <style data-purpose="custom-typography">
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        .custom-shadow-card {
            box-shadow: 0 4px 20px -2px rgba(148, 163, 184, 0.12);
        }
    </style>
    @stack('styles')
</head>
<body class="bg-[#F4F7FC] min-h-screen text-slate-700 antialiased selection:bg-blue-100 selection:text-blue-700">
    <!-- BEGIN: MainHeader -->
    <header class="bg-white border-b border-slate-100 sticky top-0 z-50 px-6 lg:px-12 py-3.5 flex items-center justify-between shadow-2xs">
        <!-- Brand Logo -->
        <a href="{{ route('siswa.dashboard') }}" class="flex items-center gap-3 group">
            <div class="text-[#2563EB] group-hover:scale-105 transition-transform duration-200">
                <!-- Graduation Cap Logo Icon -->
                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 3L1 9L12 15L21 10.09V17H23V9M5 13.18V17.18C5 19.94 8.13 22 12 22C15.87 22 19 19.94 19 17.18V13.18L12 17L5 13.18Z"></path>
                </svg>
            </div>
            <span class="text-xl font-bold tracking-tight text-[#1E293B]">LearnPoint</span>
        </a>

        <!-- Header Actions (Notifications & Profile) -->
        <div class="flex items-center gap-6">
            <!-- Notification Icon with Badge -->
            <a href="{{ route('notifikasi.index') }}" aria-label="Notifikasi" class="relative text-slate-600 hover:text-slate-800 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                @php
                    $unreadNotifs = auth()->user()?->notifikasi()->count() ?? 0;
                @endphp
                @if($unreadNotifs > 0)
                    <span class="absolute -top-1 -right-1 bg-[#EF4444] text-white text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center border-2 border-white">{{ $unreadNotifs }}</span>
                @endif
            </a>

            <!-- User Profile Dropdown Pill -->
            <div class="relative" x-data="{ profileOpen: false }" @click.outside="profileOpen = false">
                @php
                    $fullName = auth()->user()->siswa->nama_siswa ?? auth()->user()->name ?? 'Siswa';
                    $firstName = explode(' ', trim($fullName))[0] ?? 'Siswa';
                @endphp
                <button @click="profileOpen = !profileOpen" type="button" class="flex items-center gap-2.5 cursor-pointer focus:outline-none select-none">
                    <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-600 font-bold text-xs flex items-center justify-center border border-slate-200 overflow-hidden shadow-2xs">
                        {{ strtoupper(substr($firstName, 0, 2)) }}
                    </div>
                    <span class="text-sm font-semibold text-slate-700">Halo, {{ $firstName }}</span>
                    <svg class="w-4 h-4 text-slate-500 transition-transform duration-200" :class="profileOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                </button>

                <!-- Dropdown Content -->
                <div x-show="profileOpen"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                     x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                     class="absolute right-0 mt-3 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 z-50 divide-y divide-slate-100"
                     style="display: none;">
                    <div class="px-4 py-2.5">
                        <p class="text-xs font-bold text-slate-800 truncate">{{ $fullName }}</p>
                        <p class="text-[11px] text-slate-400 truncate">{{ auth()->user()->email ?? auth()->user()->username }}</p>
                    </div>
                    <div class="py-1">
                        <a href="{{ route('profile.show') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition">
                            <i class="fa-regular fa-user text-slate-400"></i> Profil Saya
                        </a>
                        <a href="{{ route('siswa.dashboard') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition">
                            <i class="fa-solid fa-house-chimney text-slate-400"></i> Beranda Belajar
                        </a>
                    </div>
                    <div class="py-1">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2 text-xs font-medium text-red-600 hover:bg-red-50 transition text-left">
                                <i class="fa-solid fa-arrow-right-from-bracket text-red-400"></i> Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <!-- END: MainHeader -->

    <!-- BEGIN: MainContent -->
    <main class="max-w-[1440px] mx-auto px-6 lg:px-12 py-7 space-y-6">
        @yield('content')
    </main>
    <!-- END: MainContent -->

    @stack('scripts')
</body>
</html>