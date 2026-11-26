@extends('layouts.siswa')

@section('title', $viewMode === 'mission' ? 'Planet ' . $selectedPlanet->nama . ' - Misi Kuis Petualangan' : 'Kuis Petualangan Siswa - LearnPoint')

@section('content')
@php
    $siswa = $siswa ?? auth()->user()?->siswa;
    $activeBanner = $siswa?->active_banner ?? ['theme' => 'light', 'gif_url' => null, 'nama' => 'Tema Bawaan'];
    $isDarkTheme = ($activeBanner['theme'] ?? 'light') === 'dark';
    $activeBannerUrl = !empty($activeBanner['gif_url']) ? asset($activeBanner['gif_url']) : null;
@endphp
<div x-data="kuisBannerManager()" class="space-y-6">

    {{-- Komponen Modal Gacha Power & Banner --}}
    @include('siswa.kuis.partials.gacha-modal')

    @include('siswa.partials.unassigned-kelas-banner', ['kelas' => $kelas ?? null])

    @if($viewMode === 'planets')
        {{-- ================================================================= --}}
        {{-- MODE 1: GALAXY MAP / PILIHAN PLANET (SESUAI GAMBAR 1)           --}}
        {{-- ================================================================= --}}

        {{-- 1. Breadcrumb Navigation --}}
        <nav class="flex items-center gap-2 text-xs font-semibold text-slate-400">
            <a href="{{ route('siswa.dashboard') }}" class="hover:text-blue-600 transition flex items-center gap-1.5">
                <i class="fa-solid fa-house text-[11px]"></i>
                <span>Beranda</span>
            </a>
            <span class="text-[#2563EB] font-bold">›</span>
            <a href="{{ route('siswa.kuis') }}" class="text-[#2563EB] font-bold">
                Kuis Petualangan
            </a>
        </nav>

        {{-- 2. Hero Banner: Pure GIF Banner (Ukuran sama dengan Dashboard) --}}
        <div class="relative overflow-hidden rounded-[26px] p-7 md:p-9 border transition-all duration-300 min-h-[280px] md:min-h-[320px] {{ $activeBannerUrl ? 'border-slate-700/80 shadow-none' : 'bg-gradient-to-r from-[#EBF4FF] via-[#EDF6FF] to-[#DCEEFE] border-blue-100 shadow-sm' }}">
            {{-- Background GIF murni tanpa overlay agar terlihat jelas --}}
            @if(!empty($activeBannerUrl))
                <img src="{{ $activeBannerUrl }}" alt="{{ $activeBanner['nama'] }}" class="absolute inset-0 w-full h-full object-cover pointer-events-none select-none z-0">
            @endif

            {{-- Tombol Aksi di Pojok Kanan Atas: Ubah Banner --}}
            <div class="absolute top-4 right-4 z-20 flex items-center gap-2">
                <button
                    type="button"
                    @click="bannerModalOpen = true"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-md backdrop-blur-md cursor-pointer {{ $isDarkTheme && $activeBannerUrl ? 'bg-slate-900/75 hover:bg-slate-900/95 text-white border border-white/25 hover:border-white/40' : 'bg-white/85 hover:bg-white text-slate-700 border border-slate-200' }}"
                    title="Ganti Banner GIF"
                >
                    <i class="fa-solid fa-paintbrush text-[11px] {{ $isDarkTheme && $activeBannerUrl ? 'text-amber-400' : 'text-blue-600' }}"></i>
                    <span class="hidden sm:inline">Ubah Banner</span>
                </button>
            </div>
        </div>

        {{-- Judul & Deskripsi Kuis Petualangan (Di luar banner) --}}
        <div class="pt-1">
            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight leading-tight text-slate-900">
                Kuis Petualangan
            </h1>
            <p class="text-sm md:text-base mt-1.5 leading-relaxed font-medium text-slate-600">
                Kamu adalah pengembara antar galaksi yang suka menolong. Bantu kami menyelamatkan dunia!
            </p>
        </div>

        {{-- 3. Section Title, Panduan & Tombol Peti Gacha --}}
        <div class="pt-2 flex items-center justify-between gap-4 flex-wrap">
            <div>
                <h2 class="text-lg md:text-xl font-extrabold text-slate-900 tracking-tight">
                    Pilihan pelanet yang ingin diselamatkan!!!
                </h2>
                <p class="text-xs md:text-sm text-slate-500 font-medium mt-0.5">
                    Pilih planet yang mau ditolong
                </p>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap">
                {{-- Tombol Leaderboard Kuis --}}
                <a 
                    href="{{ route('siswa.kuis.leaderboard') }}"
                    class="px-4 py-2.5 rounded-2xl bg-white hover:bg-slate-50 text-slate-700 hover:text-amber-600 font-bold text-xs flex items-center gap-2 border border-slate-200 transition shadow-2xs cursor-pointer"
                    title="Lihat Peringkat Leaderboard Tingkatan"
                >
                    <i class="fa-solid fa-trophy text-amber-500 text-sm"></i>
                    <span>Leaderboard</span>
                </a>

                {{-- Tombol Panduan Kuis --}}
                <a 
                    href="{{ route('siswa.kuis.panduan') }}"
                    class="px-4 py-2.5 rounded-2xl bg-white hover:bg-slate-50 text-slate-700 hover:text-blue-600 font-bold text-xs flex items-center gap-2 border border-slate-200 transition shadow-2xs cursor-pointer"
                    title="Buku Panduan Petualangan & Aturan Main"
                >
                    <i class="fa-solid fa-circle-question text-blue-500 text-sm"></i>
                    <span>Panduan Kuis</span>
                </a>

                {{-- Tombol Buka Peti Gacha --}}
                <a 
                    href="{{ route('siswa.gacha') }}"
                    class="px-4 py-2.5 rounded-2xl bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-600 hover:to-yellow-600 text-white font-extrabold text-xs flex items-center gap-2 shadow-md shadow-amber-500/20 transition cursor-pointer"
                >
                    <i class="fa-solid fa-box-open text-sm"></i>
                    <span>Buka Peti Gacha</span>
                    <span class="px-2 py-0.5 rounded-full bg-white/20 text-white text-[11px] font-black">
                        <i class="fa-solid fa-coins text-[10px] mr-0.5 text-amber-200"></i>
                        {{ number_format(auth()->user()?->siswa?->koin ?? 0, 0, ',', '.') }}
                    </span>
                </a>
            </div>
        </div>

        {{-- 4. Planet Cards Grid (Mobile-style Zig-Zag Layout) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-8 px-3 sm:px-5 py-3">
            @forelse($planets as $planet)
                @php
                    $isRight = $loop->index % 2 === 1;
                @endphp
                <a 
                    href="{{ route('siswa.kuis', ['mapel_id' => $planet->id]) }}"
                    class="group relative flex items-center justify-between bg-[#F4F5F7] hover:bg-[#ECEEF2] rounded-[24px] min-h-[148px] sm:min-h-[164px] px-5 sm:px-7 py-5 shadow-[0_6px_20px_rgba(15,23,42,0.06)] hover:shadow-[0_12px_28px_rgba(37,99,235,0.12)] border border-slate-200/60 hover:border-blue-300/70 transition-all duration-300 {{ $isRight ? 'flex-row-reverse text-right' : 'flex-row text-left' }}"
                >
                    {{-- Asset Planet (Menonjol di sisi kiri/kanan secara bergantian) --}}
                    <div class="shrink-0 pointer-events-none select-none {{ $isRight ? '-mr-8 sm:-mr-10 -my-6' : '-ml-8 sm:-ml-10 -my-6' }}">
                        <img 
                            src="{{ asset('Images/planets/planet1.png') }}" 
                            alt="Planet {{ $planet->nama }}" 
                            class="w-36 h-36 sm:w-44 sm:h-44 object-contain drop-shadow-[0_12px_18px_rgba(15,23,42,0.18)] transition-transform duration-500 group-hover:scale-105 group-hover:rotate-3"
                        >
                    </div>

                    {{-- Nama Mapel & Deskripsi --}}
                    <div class="flex-1 min-w-0 {{ $isRight ? 'pr-3 sm:pr-5' : 'pl-3 sm:pl-5' }}">
                        <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 group-hover:text-blue-600 transition leading-snug truncate">
                            {{ $planet->nama }}
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-500 font-medium mt-1 line-clamp-2">
                            {{ $planet->deskripsi }}
                        </p>
                        <div class="mt-3 flex items-center gap-2 {{ $isRight ? 'justify-end' : 'justify-start' }}">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/90 border border-slate-200/80 text-[11px] font-bold text-slate-600 shadow-2xs">
                                <span class="w-1.5 h-1.5 rounded-full {{ $planet->masalah_terdeteksi > 0 ? 'bg-amber-500 animate-pulse' : 'bg-emerald-500' }}"></span>
                                {{ $planet->masalah_terdeteksi }} masalah terdeteksi
                            </span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full bg-white rounded-[24px] border border-slate-200/80 p-10 text-center space-y-2">
                    <i class="fa-solid fa-planet-ringed text-3xl text-slate-300"></i>
                    <p class="text-sm font-bold text-slate-700">Belum ada mata pelajaran tersedia.</p>
                    <p class="text-xs text-slate-400">Planet petualangan akan muncul otomatis sesuai mata pelajaran yang tersedia.</p>
                </div>
            @endforelse
        </div>

    @else
        {{-- ================================================================= --}}
        {{-- MODE 2: PLANET MISSION STAGE                                      --}}
        {{-- ================================================================= --}}

        {{-- 1. Breadcrumb Navigation --}}
        <nav class="flex items-center gap-2 text-xs font-semibold text-slate-400">
            <a href="{{ route('siswa.dashboard') }}" class="hover:text-blue-600 transition flex items-center gap-1.5">
                <i class="fa-solid fa-house text-[11px]"></i>
                <span>Beranda</span>
            </a>
            <span class="text-slate-300">›</span>
            <a href="{{ route('siswa.kuis') }}" class="hover:text-blue-600 transition">
                Kuis Petualangan
            </a>
            <span class="text-slate-300">›</span>
            <span class="text-slate-600">Planet {{ $selectedPlanet->nama }}</span>
        </nav>

        {{-- 2. Hero Card: Planet Info & Sector Stats --}}
        <div class="relative overflow-hidden rounded-[28px] bg-gradient-to-r from-[#EBF3FE] via-[#EEF5FF] to-[#E3EFFF] p-6 md:p-8 border border-blue-100/60 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-1">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 md:w-14 md:h-14 rounded-2xl {{ $selectedPlanet->bg_color ?? 'bg-blue-50' }} border {{ $selectedPlanet->border_color ?? 'border-blue-100' }} flex items-center justify-center p-1 shadow-2xs shrink-0">
                        @include('siswa.kuis.partials.planet-icon', ['planet' => $selectedPlanet, 'size' => 'w-10 h-10 md:w-11 md:h-11'])
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-slate-900 leading-tight">
                                Planet {{ $selectedPlanet->nama }}
                            </h1>
                            <a href="{{ route('siswa.kuis') }}" class="text-xs px-2.5 py-1 rounded-lg bg-white/70 hover:bg-white text-blue-600 font-semibold border border-blue-100 transition shadow-2xs" title="Ganti Planet">
                                <i class="fa-solid fa-rotate-left mr-1"></i> Ganti Planet
                            </a>
                            <a href="{{ route('siswa.kuis.leaderboard', ['mapel_id' => $selectedPlanet->id]) }}" class="text-xs px-2.5 py-1 rounded-lg bg-white/70 hover:bg-white text-amber-600 font-semibold border border-amber-200 transition shadow-2xs" title="Lihat Leaderboard Mapel Ini">
                                <i class="fa-solid fa-trophy mr-1 text-amber-500"></i> Leaderboard
                            </a>
                        </div>
                        <p class="text-xs md:text-sm text-slate-600 font-medium mt-0.5">
                            {{ $selectedPlanet->lore ?? 'Lorem Ipsum' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Right Stats Box --}}
            <div class="bg-white/95 rounded-2xl p-4 md:px-6 md:py-3.5 border border-slate-100 shadow-sm flex items-center gap-6 shrink-0">
                {{-- Progres Sektor --}}
                <div>
                    <div class="text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-1.5">
                        PROGRES SEKTOR
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-24 md:w-28 h-2 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-[#2563EB] rounded-full transition-all duration-500" style="width: {{ $progresSektor }}%"></div>
                        </div>
                        <span class="text-sm font-extrabold text-[#2563EB]">{{ $progresSektor }}%</span>
                    </div>
                </div>

                {{-- Divider --}}
                <div class="w-px h-8 bg-slate-200"></div>

                {{-- Koin & Tombol Gacha --}}
                <div class="flex items-center gap-3">
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-1">
                            KOIN ANDA
                        </div>
                        <div class="flex items-center gap-1.5 font-black text-sm text-amber-600">
                            <i class="fa-solid fa-coins text-amber-500"></i>
                            <span>{{ number_format(auth()->user()?->siswa?->koin ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <a 
                        href="{{ route('siswa.gacha') }}"
                        class="px-3 py-1.5 rounded-xl bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-600 text-white font-extrabold text-[11px] flex items-center gap-1.5 shadow-xs transition cursor-pointer"
                        title="Buka Peti Gacha Power & Banner"
                    >
                        <i class="fa-solid fa-box-open"></i> Gacha
                    </a>
                </div>
            </div>
        </div>

        {{-- 3. Two-Column Layout: Tahapan Penyelamatan vs Target & Tips --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 pt-2">

            {{-- Kolom Kiri: Tahapan Penyelamatan (60%) --}}
            <div class="lg:col-span-7 xl:col-span-8 space-y-4">
                <div>
                    <h2 class="text-lg md:text-xl font-extrabold text-slate-900 tracking-tight">
                        Tahapan Penyelamatan
                    </h2>
                    <p class="text-xs md:text-sm text-slate-500 font-medium mt-0.5">
                        Pilih pos aktif untuk melanjutkan petualangan numerik
                    </p>
                </div>

                {{-- List of Zonas --}}
                <div class="space-y-4 pt-1">
                    @forelse($zonas as $zona)
                        @if($zona->is_completed)
                            {{-- 1. ZONA SELESAI --}}
                            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 md:p-5 flex items-center justify-between gap-4 shadow-xs">
                                <div class="flex items-center gap-3.5 min-w-0">
                                    <div class="w-10 h-10 rounded-full bg-[#EBF8F2] text-[#10B981] flex items-center justify-center font-bold text-base shrink-0 border border-emerald-100">
                                        <i class="fa-solid fa-check"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h4 class="font-extrabold text-slate-900 text-sm md:text-base truncate">
                                                {{ $zona->judul }}
                                            </h4>
                                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#EBF8F2] text-[#10B981] border border-emerald-100 shrink-0">
                                                Selesai
                                            </span>
                                        </div>
                                        <div class="text-xs text-slate-500 font-medium mt-0.5 flex items-center gap-1.5 flex-wrap">
                                            <span>Nilai: <strong class="text-slate-800">{{ number_format($zona->skor, 0) }}/100</strong></span>
                                            <span>•</span>
                                            <span class="inline-flex items-center gap-0.5 text-amber-500 font-bold">
                                                @for($s = 1; $s <= 3; $s++)
                                                    <i class="fa-solid fa-star text-[11px] {{ $s <= $zona->bintang ? 'text-amber-400' : 'text-slate-200' }}"></i>
                                                @endfor
                                                <span class="text-slate-600 text-[11px] ml-1">({{ $zona->bintang }}/3 Bintang)</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                @if($zona->quiz)
                                    <a 
                                        href="{{ route('siswa.kuis.hasil-petualangan', $zona->quiz->id) }}"
                                        class="px-4 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-xs font-bold text-slate-700 flex items-center gap-1.5 transition shrink-0"
                                        title="Lihat Hasil Akhir Petualangan"
                                    >
                                        <i class="fa-solid fa-trophy text-[11px] text-amber-500"></i>
                                        <span>Hasil Petualangan</span>
                                    </a>
                                @endif
                            </div>
                        @else
                            {{-- 2. ZONA AKTIF / BELUM DIKERJAKAN (BLUE BORDER) --}}
                            <div class="bg-white rounded-2xl border-2 border-[#2563EB] p-5 md:p-6 shadow-sm shadow-blue-500/5 space-y-4">
                                <div class="flex items-start gap-3.5">
                                    <div class="w-10 h-10 rounded-xl bg-[#EEF4FF] text-[#2563EB] flex items-center justify-center font-bold text-base shrink-0 border border-blue-100 mt-0.5">
                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                    </div>
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h4 class="font-extrabold text-slate-900 text-base">
                                                {{ $zona->judul }}
                                            </h4>
                                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FDF2F0] text-[#E05252] border border-[#FCDAD7] shrink-0">
                                                Belum Dikerjakan
                                            </span>
                                        </div>
                                        <p class="text-xs md:text-sm text-slate-500 font-medium leading-relaxed">
                                            {{ $zona->deskripsi }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Level Info Strip --}}
                                <div class="bg-[#F8FAFC] border border-slate-100 rounded-xl p-3 text-xs font-bold text-slate-700">
                                    {{ $zona->level_label }}
                                </div>

                                {{-- Button Buka Level Sekarang --}}
                                @if($zona->quiz)
                                    <a 
                                        href="{{ route('siswa.kuis.show', $zona->quiz->id) }}"
                                        class="w-full py-3 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-sm flex items-center justify-center gap-2 transition shadow-sm shadow-blue-500/20"
                                    >
                                        <span>{{ $zona->buka_level_label }}</span>
                                        <i class="fa-solid fa-arrow-right text-xs"></i>
                                    </a>
                                @endif
                            </div>
                        @endif
                    @empty
                        <div class="bg-white rounded-2xl border border-slate-200/80 p-8 text-center space-y-2 shadow-xs">
                            <div class="w-12 h-12 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center mx-auto border border-slate-100">
                                <i class="fa-solid fa-compass text-lg"></i>
                            </div>
                            <h4 class="font-extrabold text-slate-800 text-sm">Belum Ada Zona Misi</h4>
                            <p class="text-xs text-slate-500 font-medium max-w-sm mx-auto">
                                Belum ada kuis petualangan yang diterbitkan oleh guru untuk planet {{ $selectedPlanet->nama }}.
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Kolom Kanan: Target Penyelamatan & Tips (40%) --}}
            <div class="lg:col-span-5 xl:col-span-4 space-y-5">

                {{-- 1. Card: Target Penyelamatan --}}
                <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs space-y-4">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2 font-extrabold text-slate-900 text-sm">
                            <i class="fa-solid fa-rocket text-[#2563EB]"></i>
                            <span>Target Penyelamatan</span>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-[#2563EB] border border-blue-100">
                            3 Tingkat Kesulitan
                        </span>
                    </div>

                    <div class="bg-[#F8FAFC] border border-slate-100 rounded-xl p-4 space-y-2">
                        <div class="text-xs font-bold text-slate-700 leading-snug">
                            {{ $activeZona->judul ?? 'Lorem Ipsum Dolor Sit Amet' }}
                        </div>
                        <div class="flex items-center gap-1.5 text-xs font-bold text-emerald-600">
                            <i class="fa-solid fa-check text-[11px]"></i>
                            <span>Semoga Berhasil</span>
                        </div>
                    </div>

                    @if(isset($activeZona->quiz) && $activeZona->quiz)
                        <a 
                            href="{{ route('siswa.kuis.show', $activeZona->quiz->id) }}"
                            class="w-full py-3 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-sm flex items-center justify-center gap-2 transition shadow-sm"
                        >
                            <i class="fa-solid fa-map-location-dot text-xs"></i>
                            <span>Buka Peta Petualangan</span>
                        </a>
                    @else
                        <button 
                            type="button"
                            onclick="alert('Pilih kuis dari daftar untuk membuka peta petualangan level!');"
                            class="w-full py-3 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-sm flex items-center justify-center gap-2 transition shadow-sm"
                        >
                            <i class="fa-solid fa-map-location-dot text-xs"></i>
                            <span>Buka Peta Petualangan</span>
                        </button>
                    @endif
                </div>

                {{-- 2. Card: Tips Pill Box --}}
                <div class="bg-[#EFF4FF] border border-[#DBE7FE] rounded-2xl p-4 text-xs text-[#2A4374] flex items-start gap-2.5 shadow-2xs">
                    <i class="fa-regular fa-lightbulb text-indigo-500 mt-0.5 text-sm shrink-0"></i>
                    <div class="leading-relaxed">
                        <strong>Tips:</strong> {{ $tips }}
                    </div>
                </div>

            </div>

        </div>

    @endif

    {{-- MODAL LEMARI KOLEKSI BANNER GIF SISWA (Sinkron dengan Dashboard) --}}
    <div 
        x-show="bannerModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto"
        role="dialog"
    >
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" @click="bannerModalOpen = false"></div>
        <div class="relative w-full max-w-2xl bg-white rounded-3xl border border-slate-200 shadow-2xl p-6 sm:p-7 z-10 space-y-5">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center text-lg">
                        <i class="fa-solid fa-paintbrush"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-800">Koleksi Banner GIF</h3>
                        <p class="text-xs text-slate-400">Banner yang dipasang berlaku di Dashboard & Kuis Petualangan!</p>
                    </div>
                </div>
                <button type="button" @click="bannerModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 cursor-pointer">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-h-[60vh] overflow-y-auto pr-1">
                <template x-for="b in bannerList" :key="b.kode">
                    <div 
                        class="rounded-2xl border-2 p-3.5 flex flex-col justify-between gap-3 transition-all relative overflow-hidden"
                        :class="b.is_equipped ? 'border-blue-500 bg-blue-50/40' : (b.is_unlocked ? 'border-slate-200 bg-white hover:border-blue-300' : 'border-slate-100 bg-slate-50 opacity-60')"
                    >
                        {{-- Preview Banner --}}
                        <div class="h-24 rounded-xl overflow-hidden relative bg-slate-100 flex items-center justify-center border border-slate-200/80">
                            <template x-if="b.gif_url">
                                <img :src="'{{ asset('') }}' + b.gif_url" :alt="b.nama" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!b.gif_url">
                                <div class="w-full h-full bg-gradient-to-r from-blue-100 to-indigo-100 flex items-center justify-center text-blue-600 font-bold text-xs">
                                    <i class="fa-solid fa-book-open mr-1.5"></i> Tema Bawaan
                                </div>
                            </template>

                            <span 
                                class="absolute top-2 left-2 px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase tracking-wider backdrop-blur-md"
                                :class="b.rarity === 'legendary' ? 'bg-gradient-to-r from-amber-500 to-yellow-400 text-white shadow-xs' : (b.rarity === 'epic' ? 'bg-purple-600/90 text-white' : (b.rarity === 'rare' ? 'bg-blue-600/90 text-white' : 'bg-slate-700/80 text-white'))"
                                x-text="b.badge"
                            ></span>
                        </div>

                        <div>
                            <div class="flex items-center justify-between gap-2">
                                <h4 class="text-xs font-extrabold text-slate-800" x-text="b.nama"></h4>
                                <template x-if="b.is_equipped">
                                    <span class="text-[10px] font-bold text-blue-600 flex items-center gap-1">
                                        <i class="fa-solid fa-check"></i> Aktif
                                    </span>
                                </template>
                            </div>
                            <p class="text-[11px] text-slate-500 line-clamp-2 mt-0.5" x-text="b.deskripsi"></p>
                        </div>

                        <div>
                            <template x-if="b.is_equipped">
                                <button type="button" disabled class="w-full py-2 rounded-xl bg-blue-600 text-white text-xs font-bold cursor-default">
                                    Sedang Digunakan
                                </button>
                            </template>
                            <template x-if="b.is_unlocked && !b.is_equipped">
                                <button type="button" @click="equipBanner(b.kode)" class="w-full py-2 rounded-xl bg-slate-100 hover:bg-blue-600 hover:text-white text-slate-700 text-xs font-bold transition cursor-pointer">
                                    Pasang Banner
                                </button>
                            </template>
                            <template x-if="!b.is_unlocked">
                                <div class="w-full py-2 rounded-xl bg-slate-100 text-slate-400 text-xs font-semibold text-center flex items-center justify-center gap-1.5">
                                    <i class="fa-solid fa-lock text-[10px]"></i> Dapatkan via Gacha
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span class="flex items-center gap-1.5 font-bold text-amber-600">
                    <i class="fa-solid fa-coins"></i> Saldo: {{ number_format($siswa->koin ?? 0, 0, ',', '.') }} Koin
                </span>
                <a href="{{ route('siswa.gacha') }}" class="font-bold text-blue-600 hover:text-blue-700 flex items-center gap-1">
                    <span>Buka Halaman Gacha</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function kuisBannerManager() {
    return {
        bannerModalOpen: false,
        bannerList: @json($siswa?->banners_list ?? []),
        equipBanner(code) {
            fetch('{{ route('siswa.banner.equip') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ banner_code: code })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Gagal mengubah banner');
                }
            })
            .catch(() => alert('Terjadi kesalahan jaringan.'));
        }
    };
}
</script>
@endpush
@endsection
