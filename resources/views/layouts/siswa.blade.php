<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Dashboard Siswa - LearnPoint')</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@600;700&display=swap"
        rel="stylesheet">

    @stack('styles')
</head>

<body class="bg-[#F4F7FE] min-h-screen text-slate-800 antialiased m-0 p-0">

    <!-- 1. HEADER DENGAN LOGO (KIRI) DAN PROFIL SISWA (KANAN) -->
    <header class="sticky top-0 z-50 w-full bg-white/95 backdrop-blur-md border-b border-gray-200 px-4 sm:px-8 py-3 shadow-xs">
        <div class="max-w-7xl mx-auto flex items-center justify-between">

            <!-- LOGO LEARNPOINT (SISI KIRI) -->
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white shadow-md shadow-blue-500/20 shrink-0">
                    <i class="fa-solid fa-book-open text-lg"></i>
                </div>

                <div class="flex flex-col justify-center">
                    <h1 class="text-base font-extrabold text-slate-800 leading-tight tracking-tight">
                        LearnPoint
                    </h1>
                    <p class="text-[10px] font-bold text-slate-400 tracking-wider uppercase leading-none mt-0.5">
                        PORTAL BELAJAR SISWA
                    </p>
                </div>
            </div>

            <!-- PROFIL SISWA (SISI KANAN SEJAJAR) -->
            <div class="flex items-center gap-3">
                @php
                $userName = auth()->user()->name ?? 'Nuri M.';
                $nameParts = explode(' ', $userName);

                $initials = count($nameParts) >= 2
                ? strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1))
                : strtoupper(substr($userName, 0, 2));
                @endphp

                <div class="w-10 h-10 bg-blue-600 text-white font-bold text-xs rounded-full flex items-center justify-center shrink-0 shadow-sm">
                    {{ $initials }}
                </div>

                <div class="flex flex-col justify-center text-left">
                    <div class="flex items-center gap-1.5">
                        <span class="text-sm font-bold text-slate-800 leading-tight">
                            {{ $userName }}
                        </span>

                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>

                    <span class="text-[11px] text-slate-400 font-medium leading-tight mt-0.5">
                        Kelas {{ auth()->user()->siswa->anggotaKelases->first()->kelas->nama_kelas ?? 'Belum ditentukan' }}
                    </span>
                </div>
            </div>

        </div>
    </header>


    <!-- 2. CONTAINER UTAMA -->
    <div class="max-w-7xl mx-auto space-y-6 p-4 sm:p-6">

        <!-- WELCOME HERO BANNER -->
        <section
            class="relative isolate overflow-hidden rounded-[2.5rem] p-6 sm:p-10 text-white shadow-xl shadow-indigo-900/10"
            style="
        height: 270px;
        box-sizing: border-box;
        background: linear-gradient(110deg, #3036B8 0%, #3D42D0 52%, #454BAF 100%);">
            <div
                class="absolute inset-0 pointer-events-none"
                style="background: radial-gradient(ellipse at 82% 40%, rgba(180,195,255,0.25), transparent 55%);">
            </div>
            <div class="absolute -top-10 -right-8 w-36 h-36 rounded-full bg-blue-300/20 pointer-events-none"></div>
            <div class="absolute -bottom-16 -right-10 w-48 h-36 rounded-tl-full bg-indigo-300/20 pointer-events-none"></div>
            <div class="absolute -bottom-12 -left-10 w-28 h-24 rounded-tr-full bg-blue-300/20 pointer-events-none"></div>
            <div class="absolute top-7 right-8 grid grid-cols-4 gap-2 opacity-40 pointer-events-none">
                @for ($i = 0; $i < 12; $i++)
                    <span class="w-2 h-2 rounded-full bg-blue-100"></span>
                    @endfor
            </div>

            <!-- KONTEN BANNER -->
            <div class="relative z-10 flex items-center justify-between gap-4 h-full">
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-4">
                        <span class="inline-flex items-center gap-1.5 bg-white/10 backdrop-blur-md border border-white/15 px-3 py-1.5 rounded-full text-[11px] font-semibold tracking-wide">
                            <i class="fa-solid fa-house-user text-[10px] text-[#D5C6FF]"></i>
                            PORTAL SISWA • T.A. {{ $taAktif ?? '2025/2026 GANJIL' }}
                        </span>

                        <span class="inline-flex items-center gap-1.5 bg-emerald-500 text-white text-[10px] font-bold px-2.5 py-1.5 rounded-full">
                            <span class="w-1.5 h-1.5 bg-white rounded-full animate-pulse"></span>
                            Status: Siswa Aktif
                        </span>
                    </div>

                    <!-- Judul -->
                    <h2 class="text-2xl sm:text-4xl font-extrabold tracking-tight mb-3">
                        Selamat Datang, {{ auth()->user()->name }}
                    </h2>

                    <!-- Deskripsi -->
                    <p class="text-indigo-100/90 text-xs sm:text-sm leading-relaxed mb-6 max-w-xl">
                        Akses materi pelajaran, kumpulkan tugas & PR, ikuti kuis harian, serta pantau jadwal ujian dengan mudah dan terpusat.
                    </p>

                    <!-- Informasi siswa -->
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <span class="bg-white/10 backdrop-blur-md px-3 py-2 rounded-lg flex items-center gap-2 border border-white/10">
                            <i class="fa-solid fa-id-card text-[#D5C6FF]"></i>
                            NIS:
                            <span class="font-bold">{{ $siswa->nis ?? 'Belum diatur' }}</span>
                        </span>

                        <span class="bg-white/10 backdrop-blur-md px-3 py-2 rounded-lg flex items-center gap-2 border border-white/10">
                            <i class="fa-solid fa-book-open-reader text-[#D5C6FF]"></i>
                            Kelas:
                            <span class="font-bold">
                                {{ auth()->user()->siswa?->anggotaKelases?->first()?->kelas?->nama_kelas ?? 'Belum ditentukan' }}
                            </span>
                        </span>
                    </div>
                </div>

                <!-- SECTION KANAN -->
                <div class="relative hidden md:block w-[390px] lg:w-[460px] h-full shrink-0 select-none">
                    <div class="absolute -top-10 -right-10 w-32 h-32 rounded-full bg-blue-300/20 pointer-events-none"></div>
                    <svg class="absolute top-[12%] left-[30%] w-7 h-7 text-blue-200 fill-current" viewBox="0 0 24 24">
                        <path d="M12 0L14.59 9.41L24 12L14.59 14.59L12 24L9.41 14.59L0 12L9.41 9.41Z" />
                    </svg>
                    <svg class="absolute top-[24%] left-[17%] w-3 h-3 text-blue-200 fill-current" viewBox="0 0 24 24">
                        <path d="M12 0L14.59 9.41L24 12L14.59 14.59L12 24L9.41 14.59L0 12L9.41 9.41Z" />
                    </svg>
                    <svg class="absolute top-[23%] right-[5%] w-6 h-6 text-[#E2D5FF] fill-current" viewBox="0 0 24 24">
                        <path d="M12 0L14.59 9.41L24 12L14.59 14.59L12 24L9.41 14.59L0 12L9.41 9.41Z" />
                    </svg>

                    <div class="absolute right-[12%] top-1/2 -translate-y-1/2 z-10 w-72 md:w-80 lg:w-96 h-auto -rotate-3">
                        <img
                            src="{{ asset('images/icon-book.png') }}"
                            alt="Ilustrasi Buku"
                            class="w-full h-full object-contain drop-shadow-[0_14px_16px_rgba(15,23,100,0.25)]">
                    </div>
                    <div
                        class="absolute top-[29%] right-1 z-30 w-[100px] rotate-[-5deg] text-[#E2D5FF]"
                        style="font-family: 'Caveat', 'Comic Sans MS', cursive;">
                        <p class="text-xl font-bold leading-tight">Semangat</p>
                        <p class="text-2xl font-extrabold leading-tight">Belajar!</p>

                        <div class="flex justify-end mt-1">
                            <svg class="w-10 h-5" viewBox="0 0 40 20" fill="none">
                                <path
                                    d="M2 16 Q20 2 38 5"
                                    stroke="currentColor"
                                    stroke-width="2.5"
                                    stroke-linecap="round" />
                            </svg>
                        </div>
                    </div>

                    <svg class="absolute bottom-[20%] left-[15%] w-5 h-5 text-blue-200 fill-current" viewBox="0 0 24 24">
                        <path d="M12 0L14.59 9.41L24 12L14.59 14.59L12 24L9.41 14.59L0 12L9.41 9.41Z" />
                    </svg>

                    <div class="absolute bottom-[10%] left-[12%] right-[5%] h-8 border-b-2 border-blue-200/70 rounded-[50%] rotate-[-7deg] pointer-events-none"></div>
                    <div class="absolute bottom-[7%] right-8 flex gap-2 opacity-60">
                        <span class="w-3 h-3 rounded-full border-2 border-blue-200"></span>
                        <span class="w-3 h-3 rounded-full border-2 border-blue-200"></span>
                    </div>

                </div>

            </div>
        </section>


        <!-- NAVIGASI DAN PENCARIAN -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">

            <nav class="flex flex-wrap gap-2">

                <!-- Mata Pelajaran -->
                <a
                    href="{{ route('siswa.dashboard') }}"
                    class="px-5 py-2.5 rounded-full flex items-center gap-2 text-xs sm:text-sm transition font-bold {{ request()->routeIs('siswa.dashboard') || request()->routeIs('siswa.materi*') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'bg-white hover:bg-gray-50 text-gray-500 border border-gray-200' }}">
                    <i class="fa-solid fa-book-open"></i>
                    Mata Pelajaran
                </a>

                <!-- Tugas -->
                <a
                    href="{{ Route::has('siswa.tugas') ? route('siswa.tugas') : '#' }}"
                    class="px-5 py-2.5 rounded-full flex items-center gap-2 text-xs sm:text-sm transition font-bold {{ request()->routeIs('siswa.tugas*') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'bg-white hover:bg-gray-50 text-gray-500 border border-gray-200' }}">
                    <i class="fa-solid fa-file-pen"></i>
                    Tugas
                </a>

                <!-- Kuis -->
                <a
                    href="{{ Route::has('siswa.kuis') ? route('siswa.kuis') : '#' }}"
                    class="px-5 py-2.5 rounded-full flex items-center gap-2 text-xs sm:text-sm transition font-bold {{ request()->routeIs('siswa.kuis*') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'bg-white hover:bg-gray-50 text-gray-500 border border-gray-200' }}">
                    <i class="fa-solid fa-list-check"></i>
                    Kuis
                </a>

                <!-- Ujian -->
                <a
                    href="{{ Route::has('siswa.ujian') ? route('siswa.ujian') : '#' }}"
                    class="px-5 py-2.5 rounded-full flex items-center gap-2 text-xs sm:text-sm transition font-bold {{ request()->routeIs('siswa.ujian*') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'bg-white hover:bg-gray-50 text-gray-500 border border-gray-200' }}">
                    <i class="fa-solid fa-file-invoice"></i>
                    Ujian & Penilaian
                </a>

            </nav>

            <!-- PENCARIAN -->
            <form
                action="{{ route('siswa.dashboard') }}"
                method="GET"
                class="relative w-full md:w-72">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari mata pelajaran atau bab..."
                    class="w-full bg-white text-xs border border-gray-200 rounded-full py-2.5 pl-4 pr-10 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">

                <button
                    type="submit"
                    aria-label="Cari"
                    class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600 text-xs">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </form>

        </div>

        <!-- KONTEN HALAMAN -->
        <main class="w-full">
            @yield('content')
        </main>
    </div>
    @stack('scripts')

</body>

</html>