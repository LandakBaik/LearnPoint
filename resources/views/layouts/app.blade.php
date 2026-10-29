<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - LearnPoint</title>

    <!-- Tailwind CSS (CDN / Vite) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Font Plus Jakarta Sans & Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Sidebar & Layout Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-[#F8FAFC] text-slate-800 font-sans antialiased h-screen flex overflow-hidden selection:bg-blue-100 selection:text-blue-900" x-data="{ sidebarOpen: false }">

    <!-- Mobile Sidebar Backdrop -->
    <div
        x-show="sidebarOpen"
        @click="sidebarOpen = false"
        x-transition:enter="transition-opacity ease-linear duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-900/50 z-40 lg:hidden"
        style="display: none;"
    ></div>

    <!-- Sidebar Navigation -->
    @php
    $userRole = auth()->user()->role ?? 'guru';
    @endphp

    @if($userRole !== 'siswa')
    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="sidebar-container fixed lg:sticky top-0 left-0 h-screen w-64 z-50 lg:z-30 transform transition-transform duration-300 ease-in-out flex flex-col justify-between bg-white border-r border-slate-200/80 text-slate-700 shrink-0 shadow-xl lg:shadow-none select-none"
    >
        @php
            $userRole = auth()->user()->role ?? 'guru';
        @endphp

        @if(view()->exists('layouts.partials.sidebar-' . $userRole))
        @include('layouts.partials.sidebar-' . $userRole)
        @else
        @include('layouts.partials.sidebar-default')
        @endif

    </aside>
    @endif

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        <!-- Header Navbar -->
        <header class="sticky top-0 h-16 bg-white border-b border-slate-200/80 flex items-center justify-between px-4 sm:px-8 z-10 shadow-xs shrink-0">

            @if(($userRole ?? '') === 'siswa')
            <!-- Header Khusus Siswa -->
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white shadow-md shadow-blue-500/20 shrink-0">
                    <i class="fa-solid fa-book-open text-lg"></i>
                </div>
                <div class="flex flex-col">
                    <h1 class="text-base font-extrabold text-slate-800 leading-tight">LearnPoint</h1>
                    <p class="text-[10px] font-bold text-slate-400 tracking-wider uppercase leading-none mt-0.5">PORTAL BELAJAR SISWA</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                @php
                $userName = auth()->user()->name ?? 'Siswa';
                $nameParts = explode(' ', $userName);
                $initials = count($nameParts) >= 2
                ? strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1))
                : strtoupper(substr($userName, 0, 2));
                @endphp

                <div class="w-10 h-10 bg-blue-600 text-white font-bold text-xs rounded-full flex items-center justify-center shrink-0 shadow-sm ring-2 ring-blue-100">
                    {{ $initials }}
                </div>

                <div class="flex flex-col justify-center text-left">
                    <div class="flex items-center gap-1">
                        <span class="text-sm font-bold text-slate-800 leading-tight">{{ $userName }}</span>
                        <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 ml-1"></i>
                    </div>
                    <span class="text-[11px] text-slate-400 font-medium leading-tight mt-0.5">
                        Kelas {{ auth()->user()->siswa->anggotaKelases->first()->kelas->nama_kelas ?? 'Belum ditentukan' }}
                    </span>
                </div>
            </div>

            @else
            <!-- Header (Guru / Admin) -->
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 -ml-2 rounded-xl text-slate-500 hover:text-slate-800 hover:bg-slate-100 focus:outline-none transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <h1 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">@yield('title', 'Dashboard')</h1>
            </div>

            <!-- Notification Bell & Profile Info -->
            <div class="flex items-center gap-3 sm:gap-4">
                @php
                    $unreadHeaderCount = auth()->user()?->unreadNotifikasiCount() ?? 0;
                @endphp
                <!-- Bell Button -->
                <a href="{{ route('notifikasi.index') }}" class="relative p-2 rounded-xl text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition-colors" title="Pusat Notifikasi">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                    </svg>
                    @if($unreadHeaderCount > 0)
                        <span class="absolute top-1 right-1 flex h-4 min-w-4 px-1 items-center justify-center rounded-full bg-rose-500 text-[10px] font-extrabold text-white ring-2 ring-white">
                            {{ $unreadHeaderCount > 99 ? '99+' : $unreadHeaderCount }}
                        </span>
                    @endif
                </a>

                <div class="h-6 w-px bg-slate-200 hidden sm:block"></div>

                <div class="text-right hidden sm:block">
                    <p class="text-sm font-bold text-slate-800 leading-tight">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-slate-400 leading-tight mt-0.5">{{ auth()->user()->email }}</p>
                </div>
                @php
                    $headerNames = explode(' ', trim(auth()->user()->name ?? 'User'));
                    $headerInitials = count($headerNames) >= 2
                        ? strtoupper(substr($headerNames[0], 0, 1) . substr($headerNames[1], 0, 1))
                        : strtoupper(substr(auth()->user()->name ?? 'U', 0, 2));
                @endphp
                <a href="{{ route('profile.show') }}" class="w-10 h-10 rounded-full bg-blue-600 text-white font-bold text-sm flex items-center justify-center ring-2 ring-blue-100 shadow-sm shrink-0 hover:ring-blue-300 transition-all" title="Lihat Profil">
                    {{ $headerInitials }}
                </a>
            </div>
            @endif

        </header>

        <!-- Main Body -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 bg-[#F8FAFC]">
            <!-- Flash Message Warning / Success / CSV Errors / Validation Errors -->
            @if(session('csv_errors'))
                <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 shadow-xs">
                    <div class="flex items-center gap-2 mb-2 font-bold text-rose-900 text-sm">
                        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('error') ?? 'Import CSV Dibatalkan' }}</span>
                    </div>
                    <ul class="list-disc list-inside text-xs font-mono space-y-1 text-rose-700 max-h-48 overflow-y-auto pl-1">
                        @foreach(session('csv_errors') as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @elseif(isset($errors) && $errors->any())
                <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 shadow-xs">
                    <div class="flex items-center gap-2.5 mb-2 font-bold text-rose-900 text-sm">
                        <div class="w-7 h-7 rounded-xl bg-rose-100 flex items-center justify-center text-rose-600 shrink-0">
                            <i class="fa-solid fa-triangle-exclamation text-xs"></i>
                        </div>
                        <span>Gagal Menyimpan Data (Terdapat {{ $errors->count() }} Kesalahan Input):</span>
                    </div>
                    <ul class="list-disc list-inside text-xs space-y-1 text-rose-700 pl-2">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @elseif(session('error'))
                <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 flex items-start justify-between shadow-xs">
                    <div class="flex items-center gap-3">
                        <div class="w-7 h-7 rounded-xl bg-rose-100 flex items-center justify-center text-rose-600 shrink-0">
                            <i class="fa-solid fa-circle-xmark text-sm"></i>
                        </div>
                        <span class="text-sm font-semibold">{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            @if(session('warning'))
                <div class="mb-6 p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 flex items-start justify-between shadow-xs">
                    <div class="flex items-center gap-3">
                        <div class="w-7 h-7 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600 shrink-0">
                            <i class="fa-solid fa-triangle-exclamation text-xs"></i>
                        </div>
                        <span class="text-sm font-semibold">{{ session('warning') }}</span>
                    </div>
                </div>
            @endif

            @if(session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-start justify-between shadow-xs">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                        <i class="fa-solid fa-circle-check text-sm"></i>
                    </div>
                    <span class="text-sm font-semibold">{{ session('success') }}</span>
                </div>
            </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- SweetAlert2 Handler Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: {!! json_encode(session('success')) !!},
                    confirmButtonColor: '#10b981',
                    timer: 3500,
                    timerProgressBar: true
                });
            @endif

            @if(session('csv_errors'))
                @php
                    $csvErrList = session('csv_errors');
                    $htmlList = '<div style="text-align: left; font-size: 12px; background-color: #fef2f2; padding: 12px; border-radius: 10px; border: 1px solid #fecaca; max-height: 200px; overflow-y: auto; font-family: monospace; color: #b91c1c;">';
                    foreach ($csvErrList as $err) {
                        $htmlList .= '<div style="margin-bottom: 4px;">&bull; ' . e($err) . '</div>';
                    }
                    $htmlList .= '</div>';
                @endphp
                Swal.fire({
                    icon: 'error',
                    title: 'Import CSV Dibatalkan',
                    text: {!! json_encode(session('error') ?? 'Terdapat data tidak valid pada file CSV.') !!},
                    html: {!! json_encode($htmlList) !!},
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'Tutup & Perbaiki CSV',
                    width: '32rem'
                });
            @elseif(isset($errors) && $errors->any())
                @php
                    $errList = $errors->all();
                    $errHtml = '<div style="text-align: left; font-size: 13px; background-color: #fff1f2; padding: 14px 16px; border-radius: 12px; border: 1px solid #fecdd3; max-height: 240px; overflow-y: auto; color: #9f1239;">';
                    $errHtml .= '<div style="font-weight: 700; margin-bottom: 8px; color: #881337;"><i class=\"fa-solid fa-circle-exclamation mr-1\"></i> Mohon periksa kesalahan input berikut:</div>';
                    $errHtml .= '<ul style="list-style-type: disc; padding-left: 18px; margin: 0; line-height: 1.6;">';
                    foreach ($errList as $err) {
                        $errHtml .= '<li style="margin-bottom: 4px;">' . e($err) . '</li>';
                    }
                    $errHtml .= '</ul></div>';
                @endphp
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menyimpan Data!',
                    text: 'Terdapat data yang belum sesuai ketentuan atau melebihi batas.',
                    html: {!! json_encode($errHtml) !!},
                    confirmButtonColor: '#e11d48',
                    confirmButtonText: 'Tutup & Perbaiki',
                    width: '32rem'
                });
            @elseif(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Terjadi Kesalahan',
                    text: {!! json_encode(session('error')) !!},
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'Mengerti'
                });
            @endif

            @if(session('warning'))
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: {!! json_encode(session('warning')) !!},
                    confirmButtonColor: '#f59e0b'
                });
            @endif

            @if($errors->any() && !session('error') && !session('csv_errors'))
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Formulir Gagal',
                    text: {!! json_encode($errors->first()) !!},
                    confirmButtonColor: '#ef4444'
                });
            @endif
 
            // Global Confirmation Handler for Forms with data-confirm
            document.addEventListener('submit', function (e) {
                const form = e.target;
                const confirmMessage = form.getAttribute('data-confirm');
                if (confirmMessage && !form.dataset.confirmed) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Konfirmasi Tindakan',
                        text: confirmMessage,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#4f46e5',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Ya, Lanjutkan!',
                        cancelButtonText: 'Batal',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.dataset.confirmed = 'true';
                            form.submit();
                        }
                    });
                }
            });
        });
    </script>
</body>

</html>