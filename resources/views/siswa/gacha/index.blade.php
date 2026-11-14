@extends('layouts.siswa')

@section('title', 'Wish Antariksa - Gacha Power & Banner - LearnPoint')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
<style>
    .font-pixel {
        font-family: 'Press Start 2P', monospace;
    }

    /* Styling Animasi & Efek Genshin Impact Wish */
    @keyframes starPulse {
        0%, 100% { opacity: 0.8; transform: scale(1); }
        50% { opacity: 1; transform: scale(1.08); filter: drop-shadow(0 0 12px rgba(251, 191, 36, 0.85)); }
    }
    .animate-star-pulse {
        animation: starPulse 3s infinite ease-in-out;
    }

    @keyframes floatSlow {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-6px); }
    }
    .animate-float-slow {
        animation: floatSlow 3.5s infinite ease-in-out;
    }

    /* === POV ASTRONOT DOOR-OPENING ZOOM TO LIGHT ANIMATION (5.8s) === */
    @keyframes povDoorZoom {
        0%, 64% {
            transform: scale(1);
        }
        96%, 100% {
            transform: scale(2.35);
        }
    }
    .animate-pov-door-zoom {
        transform-origin: center center;
        will-change: transform;
        animation: povDoorZoom 5.8s cubic-bezier(0.32, 0, 0.2, 1) forwards;
    }

    @keyframes doorLightBloom {
        0%, 62% {
            opacity: 0;
            transform: scale(0.25);
        }
        82% {
            opacity: 0.9;
            transform: scale(1.6);
        }
        100% {
            opacity: 1;
            transform: scale(3);
        }
    }
    .animate-door-light-bloom {
        animation: doorLightBloom 5.8s cubic-bezier(0.25, 0.1, 0.25, 1) forwards;
    }

    @keyframes landingFlash {
        0%, 82% { opacity: 0; }
        95%, 100% { opacity: 1; }
    }
    .animate-landing-flash {
        animation: landingFlash 5.8s ease-in forwards;
    }

    @keyframes starTwinkle {
        0%, 100% { opacity: 0.25; transform: scale(0.8); }
        50% { opacity: 1; transform: scale(1.2); }
    }
    .animate-star-twinkle {
        animation: starTwinkle 2s infinite ease-in-out;
    }

    @keyframes cardPopIn {
        0% {
            opacity: 0;
            transform: translateY(24px) scale(0.82) rotateY(25deg);
        }
        70% {
            opacity: 1;
            transform: translateY(-4px) scale(1.03) rotateY(0deg);
        }
        100% {
            opacity: 1;
            transform: translateY(0) scale(1) rotateY(0deg);
        }
    }
    .animate-card-reveal {
        opacity: 0;
        animation: cardPopIn 0.5s cubic-bezier(0.22, 1, 0.36, 1) forwards;
    }

    /* Rarity Card Gradients */
    .rarity-epic-card {
        background: linear-gradient(145deg, #1e1b4b 0%, #312e81 40%, #d97706 100%);
        border-color: #f59e0b;
        box-shadow: 0 0 25px rgba(245, 158, 11, 0.35);
    }
    .rarity-rare-card {
        background: linear-gradient(145deg, #064e3b 0%, #065f46 50%, #10b981 100%);
        border-color: #34d399;
        box-shadow: 0 0 20px rgba(52, 211, 153, 0.3);
    }
    .rarity-common-card {
        background: linear-gradient(145deg, #0f172a 0%, #1e293b 60%, #3b82f6 100%);
        border-color: #60a5fa;
        box-shadow: 0 0 15px rgba(96, 165, 250, 0.25);
    }
</style>
@endpush

@section('content')
<div x-data="genshinWishSystem()" class="space-y-6 pb-16 select-none">

    {{-- 1. TOP HEADER & CURRENCY BAR (Genshin Style) --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900/90 border border-slate-800 rounded-3xl p-5 shadow-xl backdrop-blur-md">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-amber-500 via-orange-500 to-yellow-400 text-white flex items-center justify-center text-xl shadow-lg shadow-amber-500/30 shrink-0">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-xl font-black text-white tracking-tight leading-tight">
                        Wish Petualangan Antariksa
                    </h1>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-500/20 text-amber-300 border border-amber-500/30" x-text="currentBanner.nama + ' Banner'">
                        Space Horizon Banner
                    </span>
                </div>
                <p class="text-xs text-slate-400 font-medium mt-0.5">
                    Tukarkan Koin hasil ketelitianmu untuk memanggil Power legendaris & Banner GIF Dashboard eksklusif!
                </p>
            </div>
        </div>

        {{-- Sisi Kanan: Panduan, Audio Toggle, Saldo Koin & Pity Counter --}}
        <div class="flex items-center gap-3 shrink-0 self-start sm:self-auto flex-wrap">
            {{-- Tombol Panduan Gacha --}}
            <a 
                href="{{ route('siswa.gacha.panduan') }}"
                class="px-3.5 py-2 rounded-2xl bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700/80 hover:border-amber-400/50 text-slate-300 hover:text-white text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shadow-xs"
                title="Penjelasan Lengkap Sistem Wish, Rate Peluang & Pity"
            >
                <i class="fa-solid fa-circle-question text-amber-400"></i>
                <span class="hidden sm:inline">Panduan Wish</span>
            </a>

            {{-- Tombol Mute / Unmute Audio --}}
            <button
                type="button"
                @click="toggleSound()"
                :title="soundEnabled ? 'Matikan Efek Suara' : 'Nyalakan Efek Suara'"
                class="w-10 h-10 rounded-2xl border flex items-center justify-center transition cursor-pointer"
                :class="soundEnabled 
                    ? 'bg-slate-800/80 border-slate-700/80 text-amber-400 hover:border-amber-400/60 hover:bg-slate-800' 
                    : 'bg-slate-900/60 border-slate-800 text-slate-500 hover:text-slate-300'"
            >
                <i class="fa-solid text-sm" :class="soundEnabled ? 'fa-volume-high' : 'fa-volume-xmark'"></i>
            </button>

            {{-- Counter Pity --}}
            <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl px-4 py-2 text-center">
                <div class="text-[9px] font-extrabold uppercase tracking-widest text-slate-400">PITY BANNER</div>
                <div class="text-xs font-black text-amber-400">
                    <span x-text="pityCounter"></span> <span class="text-slate-500">/ 30</span>
                </div>
            </div>

            {{-- Saldo Koin --}}
            <div class="bg-gradient-to-r from-amber-500/20 to-yellow-500/20 border border-amber-500/40 rounded-2xl px-4 py-2 flex items-center gap-2.5 shadow-inner">
                <div class="w-7 h-7 rounded-full bg-amber-400 text-amber-950 flex items-center justify-center text-xs font-black shadow-xs">
                    <i class="fa-solid fa-coins"></i>
                </div>
                <div>
                    <div class="text-[9px] font-extrabold uppercase tracking-widest text-amber-300/80">Koin Kamu</div>
                    <div class="text-sm font-black text-white" x-text="koin"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. MAIN BANNER SHOWCASE (Dynamic Pixel Space Wish Card) --}}
    <div 
        @mouseenter="pauseCarousel()" 
        @mouseleave="resumeCarousel()"
        class="relative overflow-hidden rounded-[28px] border border-slate-700/80 bg-[#090b1e] shadow-2xl min-h-[490px] md:min-h-[530px] flex flex-col justify-between p-6 sm:p-9 lg:p-10 group"
    >
        {{-- Dynamic Background GIF (Smooth Crossfade + Subtle Zoom per available banner) --}}
        <template x-for="(b, idx) in availableBanners" :key="b.kode">
            <img 
                :src="b.asset_url" 
                :alt="b.nama"
                class="absolute inset-0 w-full h-full object-cover pointer-events-none select-none z-0 transition-all duration-[1200ms] ease-[cubic-bezier(0.4,0,0.2,1)] brightness-90 contrast-110 will-change-transform"
                :class="activeBannerIdx === idx ? 'opacity-55 scale-100' : 'opacity-0 scale-[1.04]'"
            >
        </template>

        {{-- Deep Space Vignette & Legibility Gradient Overlays --}}
        <div class="absolute inset-0 bg-gradient-to-r from-[#060818]/90 via-[#060818]/55 to-[#060818]/35 z-0 pointer-events-none"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-[#060818] via-transparent to-[#060818]/70 z-0 pointer-events-none"></div>

        {{-- Carousel Prev / Next Arrows (Muncul jika ada > 1 banner) --}}
        <template x-if="availableBanners.length > 1">
            <div>
                <button 
                    type="button" 
                    @click="prevBanner()"
                    title="Banner Sebelumnya"
                    class="absolute left-3 top-1/2 -translate-y-1/2 z-20 w-9 h-9 rounded-full bg-slate-950/70 hover:bg-slate-900 border border-white/15 hover:border-amber-400/60 text-white/75 hover:text-amber-300 flex items-center justify-center transition-all opacity-0 group-hover:opacity-100 cursor-pointer backdrop-blur-md shadow-lg"
                >
                    <i class="fa-solid fa-chevron-left text-xs"></i>
                </button>
                <button 
                    type="button" 
                    @click="nextBanner()"
                    title="Banner Berikutnya"
                    class="absolute right-3 top-1/2 -translate-y-1/2 z-20 w-9 h-9 rounded-full bg-slate-950/70 hover:bg-slate-900 border border-white/15 hover:border-amber-400/60 text-white/75 hover:text-amber-300 flex items-center justify-center transition-all opacity-0 group-hover:opacity-100 cursor-pointer backdrop-blur-md shadow-lg"
                >
                    <i class="fa-solid fa-chevron-right text-xs"></i>
                </button>
            </div>
        </template>

        {{-- TOP ROW: Pixel Title & Description (Left) + Rate Peluang Item Card (Right) --}}
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-start justify-between gap-6">
            {{-- Left Info (Smooth Fade-Slide on Banner Switch) --}}
            <div 
                class="space-y-3.5 max-w-xl transition-all duration-300 ease-out"
                :class="isBannerSwitching ? 'opacity-0 translate-y-1.5' : 'opacity-100 translate-y-0'"
            >
                <div class="flex items-center gap-2 flex-wrap">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-[#0c1026]/85 border border-amber-400/45 backdrop-blur-md shadow-sm">
                        <i class="fa-solid fa-star text-amber-400 text-[10px] animate-star-pulse"></i>
                        <span class="text-[10px] font-extrabold text-amber-300 uppercase tracking-[0.14em]">Garansi Hadiah Legendaris</span>
                    </div>
                    <span 
                        class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border backdrop-blur-md transition-colors duration-300"
                        :class="currentBanner.rarity === 'legendary' 
                            ? 'bg-amber-500/20 text-amber-300 border-amber-400/50' 
                            : (currentBanner.rarity === 'epic' ? 'bg-purple-500/20 text-purple-300 border-purple-400/50' : 'bg-cyan-500/20 text-cyan-300 border-cyan-400/50')"
                        x-text="currentBanner.badge"
                    ></span>
                </div>

                {{-- Big Pixel Title --}}
                <h2 
                    class="font-pixel text-2xl sm:text-3xl md:text-[38px] text-white uppercase tracking-wide leading-[1.28] drop-shadow-[0_4px_0_rgba(15,23,42,0.9)]"
                    x-text="currentBanner.nama"
                ></h2>

                {{-- Description --}}
                <p class="text-xs sm:text-[13px] text-slate-200/95 font-medium leading-relaxed max-w-md drop-shadow-sm">
                    <span x-text="currentBanner.deskripsi"></span>
                    Peluang meraih <strong class="text-white">Banner GIF Bergerak Eksklusif</strong> untuk profil dashboard-mu!
                </p>
            </div>

            {{-- Right: Rate Peluang Item Card --}}
            <div class="bg-[#0b1026]/85 border border-slate-700/75 backdrop-blur-md rounded-2xl p-4 w-full lg:w-[310px] shrink-0 shadow-2xl space-y-3">
                <div class="flex items-center justify-between border-b border-slate-700/80 pb-2">
                    <span class="text-[11px] font-extrabold text-slate-300 uppercase tracking-wider">Rate Peluang Item</span>
                    <span class="text-[11px] font-black text-amber-400 tracking-wider">PITY: 30X</span>
                </div>

                <div class="space-y-2.5">
                    {{-- Epic --}}
                    <div class="flex items-center justify-between gap-2.5">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-7 h-7 rounded-lg bg-amber-500/15 border border-amber-400/30 text-amber-400 flex items-center justify-center text-xs shrink-0">
                                <i class="fa-solid fa-bolt"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs font-bold text-white truncate">Banner GIF & 2X Score</div>
                                <div class="text-[10px] text-slate-400 font-medium">Peluang 8%</div>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider bg-amber-500/15 border border-amber-400/40 text-amber-300 shrink-0">
                            EPIC
                        </span>
                    </div>

                    {{-- Rare --}}
                    <div class="flex items-center justify-between gap-2.5">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-7 h-7 rounded-lg bg-emerald-500/15 border border-emerald-400/30 text-emerald-400 flex items-center justify-center text-xs shrink-0">
                                <i class="fa-solid fa-khanda"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs font-bold text-white truncate">Second Chance & Knife</div>
                                <div class="text-[10px] text-slate-400 font-medium">Peluang 27%</div>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider bg-emerald-500/15 border border-emerald-400/40 text-emerald-300 shrink-0">
                            RARE
                        </span>
                    </div>

                    {{-- Common --}}
                    <div class="flex items-center justify-between gap-2.5">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-7 h-7 rounded-lg bg-blue-500/15 border border-blue-400/30 text-emerald-400 flex items-center justify-center text-xs shrink-0">
                                <i class="fa-solid fa-puzzle-piece"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs font-bold text-white truncate">Clover & Remove Wrong</div>
                                <div class="text-[10px] text-slate-400 font-medium">Peluang 65%</div>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider bg-blue-500/15 border border-blue-400/40 text-blue-300 shrink-0">
                            COMMON
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- MIDDLE: Center Guarantee Pill + Banner Switcher Dots --}}
        <div class="relative z-10 py-5 my-auto flex flex-col items-center justify-center gap-3">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-[#0b1026]/75 border border-white/15 text-slate-200 text-xs backdrop-blur-md shadow-lg pointer-events-none">
                <span class="w-2.5 h-2.5 rounded-[2px] bg-emerald-400 inline-block shadow-[0_0_8px_rgba(74,222,128,0.8)]"></span>
                <span>Setiap tarikan <strong class="text-white">100% PASTI</strong> menghasilkan 1 Item Power!</span>
            </div>

            {{-- Dynamic Banner Switcher Pills --}}
            <template x-if="availableBanners.length > 1">
                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-[#070a18]/75 border border-slate-700/70 backdrop-blur-md">
                    <template x-for="(b, idx) in availableBanners" :key="b.kode">
                        <button
                            type="button"
                            @click="selectBanner(idx)"
                            :title="b.nama + (b.is_unlocked ? ' (Sudah Dimiliki)' : '')"
                            class="h-2 rounded-full transition-all duration-300 cursor-pointer"
                            :class="activeBannerIdx === idx 
                                ? 'w-6 bg-amber-400 shadow-[0_0_8px_rgba(251,191,36,0.7)]' 
                                : (b.is_unlocked ? 'w-2 bg-emerald-400/60 hover:bg-emerald-300' : 'w-2 bg-slate-500/60 hover:bg-slate-300')"
                        ></button>
                    </template>
                    <span class="text-[10px] font-bold text-slate-300 ml-1.5 tabular-nums">
                        <span x-text="activeBannerIdx + 1"></span>/<span x-text="availableBanners.length"></span>
                    </span>
                </div>
            </template>
        </div>

        {{-- BOTTOM BAR: Segmented 30-Block Pity Bar (Left) + Wish x1 & x10 Buttons (Right) --}}
        <div class="relative z-10 pt-4 flex flex-col lg:flex-row lg:items-end justify-between gap-5 border-t border-white/10">
            {{-- Segmented Pity Progress Bar --}}
            <div class="w-full lg:max-w-lg space-y-2">
                <div class="flex items-center justify-between text-xs font-bold">
                    <span class="text-slate-200">Garansi Banner GIF</span>
                    <span class="text-amber-400 font-black tracking-wider uppercase">
                        <span x-text="pityCounter"></span> / 30 WISH
                    </span>
                </div>

                {{-- 30 Discrete Pity Blocks --}}
                <div class="grid grid-cols-[repeat(30,minmax(0,1fr))] gap-1">
                    <template x-for="seg in 30" :key="seg">
                        <div 
                            class="h-3.5 rounded-[3px] transition-all duration-300"
                            :class="seg <= pityCounter 
                                ? 'bg-amber-400 shadow-[0_0_8px_rgba(251,191,36,0.55)]' 
                                : (seg > 25 ? 'bg-rose-950/80 border border-rose-500/35' : 'bg-slate-800/85 border border-slate-700/50')"
                        ></div>
                    </template>
                </div>

                <p class="text-[11px] text-slate-400 font-medium">
                    <template x-if="pityCounter < 30">
                        <span>Tinggal <strong class="text-slate-200" x-text="30 - pityCounter"></strong> wish lagi sampai Banner GIF dijamin.</span>
                    </template>
                    <template x-if="pityCounter >= 30">
                        <span class="text-amber-300 font-bold">Wish berikutnya DIJAMIN mendapatkan Banner GIF!</span>
                    </template>
                </p>
            </div>

            {{-- Wish Buttons (1x & 10x) --}}
            <div class="flex items-center gap-3.5 w-full sm:w-auto shrink-0">
                {{-- Wish 1x --}}
                <button 
                    type="button" 
                    @click="executeWish(1)"
                    :disabled="loading || isAnimating || koin < 20"
                    class="flex-1 sm:flex-none min-w-[140px] px-6 py-3 rounded-2xl border font-bold transition-all duration-200 flex flex-col items-center justify-center cursor-pointer shadow-lg backdrop-blur-md"
                    :class="koin >= 20 && !loading && !isAnimating
                        ? 'border-slate-500/80 bg-[#111731]/90 hover:border-amber-400/80 hover:bg-[#171f42] text-white active:scale-95' 
                        : 'border-slate-800 bg-slate-900/70 text-slate-500 cursor-not-allowed opacity-60'"
                >
                    <span class="text-sm font-black tracking-tight">Wish ×1</span>
                    <span class="text-[11px] font-semibold text-slate-300 mt-0.5">20 Koin</span>
                </button>

                {{-- Wish 10x --}}
                <button 
                    type="button" 
                    @click="executeWish(10)"
                    :disabled="loading || isAnimating || koin < 180"
                    class="flex-1 sm:flex-none min-w-[156px] px-7 py-3 rounded-2xl border font-bold transition-all duration-200 flex flex-col items-center justify-center cursor-pointer shadow-xl relative"
                    :class="koin >= 180 && !loading && !isAnimating
                        ? 'border-amber-200/90 bg-gradient-to-b from-[#fcd34d] via-[#f59e0b] to-[#d97706] hover:brightness-105 text-slate-950 active:scale-95 shadow-amber-500/25' 
                        : 'border-slate-800 bg-slate-900/70 text-slate-500 cursor-not-allowed opacity-60'"
                >
                    <span class="absolute -top-2.5 right-3 px-2.5 py-0.5 rounded-full text-[9px] font-black bg-rose-500 text-white uppercase tracking-wider shadow-md">
                        Hemat 20 Koin
                    </span>
                    <span class="text-sm font-black tracking-tight">Wish ×10</span>
                    <span class="text-[11px] font-extrabold mt-0.5" :class="koin >= 180 ? 'text-slate-900' : 'text-slate-500'">
                        180 Koin <span class="line-through opacity-60 text-[10px] ml-0.5">200</span>
                    </span>
                </button>
            </div>
        </div>
    </div>

    {{-- 3. KOLEKSI BANNER DASHBOARD (DIPASANG / TERBUKA / TERKUNCI) --}}
    <div class="bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-8 shadow-xs space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-slate-100">
            <div>
                <h3 class="text-base sm:text-lg font-extrabold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-palette text-blue-600"></i>
                    <span>Koleksi Banner GIF Dashboard Kamu</span>
                </h3>
                <p class="text-xs text-slate-400 font-medium mt-0.5">
                    Banner yang berhasil kamu dapatkan dari Wish akan terbuka di sini dan dapat dipasang ke dashboard kapan saja.
                </p>
            </div>
            <a href="{{ route('siswa.dashboard') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700 flex items-center gap-1 self-start sm:self-auto">
                <span>Lihat Dashboard</span>
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($banners as $b)
                <div 
                    class="rounded-3xl border-2 p-4 flex flex-col justify-between gap-4 transition-all relative overflow-hidden group
                        {{ $b['is_equipped'] ? 'border-blue-500 bg-blue-50/40 shadow-sm' : ($b['is_unlocked'] ? 'border-slate-200 bg-white hover:border-blue-300' : 'border-slate-100 bg-slate-50/70 opacity-60') }}"
                >
                    {{-- Pratinjau Gambar Banner --}}
                    <div class="h-28 rounded-2xl overflow-hidden relative bg-slate-900 flex items-center justify-center border border-slate-200">
                        @if(!empty($b['gif_url']))
                            <img src="{{ asset($b['gif_url']) }}" alt="{{ $b['nama'] }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-gradient-to-r from-blue-600 to-indigo-600 flex items-center justify-center text-white font-bold text-xs gap-2">
                                <i class="fa-solid fa-book-open"></i>
                                <span>Tema Bawaan</span>
                            </div>
                        @endif

                        {{-- Rarity Badge --}}
                        <span class="absolute top-2.5 left-2.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider shadow-sm backdrop-blur-md
                            {{ $b['rarity'] === 'legendary' ? 'bg-gradient-to-r from-amber-500 to-yellow-400 text-white shadow-xs' : ($b['rarity'] === 'epic' ? 'bg-purple-600/90 text-white' : ($b['rarity'] === 'rare' ? 'bg-blue-600/90 text-white' : 'bg-slate-800/80 text-white')) }}">
                            {{ $b['badge'] }}
                        </span>

                        @if($b['is_equipped'])
                            <span class="absolute top-2.5 right-2.5 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-blue-600 text-white shadow-sm flex items-center gap-1">
                                <i class="fa-solid fa-check text-[9px]"></i> Terpasang
                            </span>
                        @endif
                    </div>

                    {{-- Informasi Banner --}}
                    <div>
                        <h4 class="text-sm font-extrabold text-slate-800">{{ $b['nama'] }}</h4>
                        <p class="text-xs text-slate-500 line-clamp-2 mt-1 leading-relaxed">{{ $b['deskripsi'] }}</p>
                    </div>

                    {{-- Tombol Pasang --}}
                    <div>
                        @if($b['is_equipped'])
                            <button type="button" disabled class="w-full py-2.5 rounded-xl bg-blue-600 text-white text-xs font-bold cursor-default shadow-xs">
                                Sedang Digunakan
                            </button>
                        @elseif($b['is_unlocked'])
                            <button 
                                type="button" 
                                @click="equipBanner('{{ $b['kode'] }}')"
                                class="w-full py-2.5 rounded-xl bg-slate-100 hover:bg-blue-600 hover:text-white text-slate-700 text-xs font-bold transition cursor-pointer border border-slate-200"
                            >
                                Pasang ke Dashboard
                            </button>
                        @else
                            <div class="w-full py-2.5 rounded-xl bg-slate-100 text-slate-400 text-xs font-bold text-center flex items-center justify-center gap-1.5 border border-slate-200">
                                <i class="fa-solid fa-lock text-[10px]"></i> Belum Terbuka (via Wish)
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- OVERLAY ANIMASI POV ASTRONOT & ZOOM CAHAYA PINTU TERBUKA                  --}}
    {{-- ========================================================================= --}}
    <div 
        x-show="isAnimating" 
        x-cloak 
        class="fixed inset-0 z-[60] flex items-center justify-center overflow-hidden bg-[#030510]"
    >
        {{-- Fullscreen POV Astronaut GIF dengan Zoom ke Cahaya saat Pintu Terbuka --}}
        <template x-if="isAnimating">
            <div class="absolute inset-0 animate-pov-door-zoom">
                <img 
                    :src="'{{ asset('images/Banner/pov-astronot-mendarat.gif') }}?t=' + wishAnimKey" 
                    alt="Animasi POV Astronot" 
                    class="w-full h-full object-cover pointer-events-none select-none"
                >
            </div>
        </template>

        {{-- Rarity Atmospheric Vignette --}}
        <div 
            class="absolute inset-0 pointer-events-none transition-colors duration-500"
            :class="wishHighestRarity === 'jackpot' 
                ? 'bg-[radial-gradient(circle_at_center,transparent_30%,rgba(3,5,16,0.65)_100%)]' 
                : 'bg-[radial-gradient(circle_at_center,transparent_35%,rgba(3,5,16,0.6)_100%)]'"
        ></div>

        {{-- Radiant Center Light Bloom (Saat Pintu Terbuka) --}}
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-10">
            <div 
                class="w-72 h-72 sm:w-96 sm:h-96 rounded-full blur-2xl animate-door-light-bloom"
                :class="wishHighestRarity === 'jackpot' 
                    ? 'bg-[radial-gradient(circle,rgba(255,255,255,0.95)_0%,rgba(251,191,36,0.75)_45%,transparent_75%)]' 
                    : (wishHighestRarity === 'epic' 
                        ? 'bg-[radial-gradient(circle,rgba(255,255,255,0.95)_0%,rgba(192,132,252,0.75)_45%,transparent_75%)]' 
                        : (wishHighestRarity === 'rare' 
                            ? 'bg-[radial-gradient(circle,rgba(255,255,255,0.95)_0%,rgba(52,211,153,0.7)_45%,transparent_75%)]' 
                            : 'bg-[radial-gradient(circle,rgba(255,255,255,0.95)_0%,rgba(96,165,250,0.7)_45%,transparent_75%)]'))"
            ></div>
        </div>

        {{-- Bright Light Transition Flash --}}
        <div class="absolute inset-0 bg-white animate-landing-flash pointer-events-none z-20"></div>

        {{-- Status Text --}}
        <div class="absolute top-10 sm:top-14 text-center space-y-1.5 pointer-events-none z-10 px-4">
            <div class="font-pixel text-xs sm:text-sm text-white tracking-widest uppercase drop-shadow-[0_2px_8px_rgba(0,0,0,0.9)]">
                Membuka Pintu Kapsul...
            </div>
            <div 
                class="text-xs font-semibold drop-shadow-[0_2px_6px_rgba(0,0,0,0.9)]"
                :class="wishHighestRarity === 'jackpot' ? 'text-amber-300' : 'text-slate-200'"
                x-text="wishHighestRarity === 'jackpot' ? 'Cahaya Legendaris Memancar dari Balik Pintu!' : 'Menuju cahaya di balik pintu kapsul'"
            ></div>
        </div>

        {{-- Skip Button --}}
        <button 
            type="button" 
            @click="skipAnimation()"
            class="absolute top-6 right-6 z-30 px-4 py-2 rounded-full bg-slate-950/60 hover:bg-slate-900/80 border border-white/25 text-white text-xs font-bold backdrop-blur-md transition cursor-pointer flex items-center gap-1.5 shadow-lg"
        >
            <span>Lewati</span>
            <i class="fa-solid fa-forward text-[10px]"></i>
        </button>
    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL POP-UP ANIMASI & HASIL WISH (GENSHIN IMPACT REVEAL SCREEN)         --}}
    {{-- ========================================================================= --}}
    <div 
        x-show="showResultModal" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 overflow-y-auto"
        role="dialog"
    >
        {{-- Backdrop Gelap Berbintang --}}
        <div class="fixed inset-0 bg-slate-950/90 backdrop-blur-md" @click="showResultModal = false"></div>

        <div class="relative w-full max-w-4xl bg-slate-900 border-2 border-slate-700/80 rounded-[36px] shadow-2xl p-6 sm:p-10 z-10 space-y-6 my-6 overflow-hidden text-center">
            
            {{-- Header Hasil Reveal --}}
            <div class="space-y-1">
                <div class="w-16 h-16 mx-auto rounded-3xl bg-gradient-to-tr from-amber-400 to-yellow-300 text-amber-950 flex items-center justify-center text-3xl shadow-lg shadow-amber-400/20">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                </div>
                <h3 class="text-2xl sm:text-3xl font-black text-white tracking-tight pt-2">
                    Hasil Pemanggilan Wish
                </h3>
                <p class="text-xs sm:text-sm text-slate-400 font-medium">
                    Item Power & Banner berhasil didapatkan dan langsung tersimpan di akunmu!
                </p>
            </div>

            {{-- Grid Kartu Hasil Gacha (1x atau 10x) dengan Staggered Animation --}}
            <div 
                class="grid gap-3.5 max-h-[55vh] overflow-y-auto p-2"
                :class="wishResults.length === 1 ? 'grid-cols-1 max-w-xs mx-auto' : 'grid-cols-2 sm:grid-cols-5'"
            >
                <template x-for="(item, idx) in wishResults" :key="idx">
                    <div 
                        class="rounded-3xl border-2 p-4 flex flex-col items-center justify-between text-center gap-3 transition-transform hover:scale-105 duration-200 animate-card-reveal"
                        :style="'animation-delay: ' + (idx * 80) + 'ms'"
                        :class="item.power.rarity === 'epic' ? 'rarity-epic-card' : (item.power.rarity === 'rare' ? 'rarity-rare-card' : 'rarity-common-card')"
                    >
                        {{-- Icon Power --}}
                        <div class="w-14 h-14 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center text-2xl text-white shadow-inner">
                            <i :class="item.power.icon"></i>
                        </div>

                        {{-- Metadata Power --}}
                        <div class="space-y-0.5">
                            <span 
                                class="text-[10px] font-black uppercase tracking-wider block"
                                :class="item.power.rarity === 'epic' ? 'text-amber-300' : (item.power.rarity === 'rare' ? 'text-emerald-300' : 'text-blue-300')"
                                x-text="item.power.rarity"
                            ></span>
                            <h4 class="text-xs sm:text-sm font-black text-white line-clamp-1" x-text="item.power.nama"></h4>
                            <span class="text-[11px] font-extrabold text-white/70 block">+1 Item</span>
                        </div>

                        {{-- Tagline --}}
                        <div class="text-[9px] font-semibold text-white/60 line-clamp-1 border-t border-white/10 pt-1.5 w-full" x-text="item.power.tagline"></div>
                    </div>
                </template>
            </div>

            {{-- Banner JACKPOT Reveal jika dapat Banner GIF --}}
            <template x-if="wonBanners.length > 0">
                <div class="space-y-3 pt-2">
                    <template x-for="(wb, bIdx) in wonBanners" :key="bIdx">
                        <div class="p-4 sm:p-5 rounded-3xl bg-gradient-to-r from-amber-500/20 via-yellow-500/25 to-amber-500/20 border-2 border-amber-400 text-left flex flex-col sm:flex-row sm:items-center gap-4 shadow-xl animate-card-reveal">
                            <template x-if="wb.gif_url">
                                <img :src="'/' + wb.gif_url.replace(/^\/+/, '')" :alt="wb.nama" class="w-full sm:w-28 h-20 object-cover rounded-2xl border border-amber-300/60 shrink-0 shadow-md">
                            </template>
                            <template x-if="!wb.gif_url">
                                <div class="w-14 h-14 rounded-2xl bg-amber-400 text-amber-950 flex items-center justify-center text-2xl shrink-0 shadow-lg shadow-amber-400/40">
                                    <i class="fa-solid fa-crown"></i>
                                </div>
                            </template>
                            <div class="flex-1 min-w-0">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-amber-400 text-amber-950 uppercase tracking-wider">
                                    JACKPOT! BANNER GIF BARU
                                </span>
                                <h4 class="text-base sm:text-lg font-black text-white mt-1 truncate" x-text="wb.nama"></h4>
                                <p class="text-xs text-amber-200 line-clamp-2" x-text="wb.deskripsi"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            {{-- Footer Action Modal --}}
            <div class="pt-4 border-t border-slate-800 flex items-center justify-between gap-4 text-xs font-bold text-slate-400">
                <div class="flex items-center gap-1.5">
                    <span>Sisa Koin:</span>
                    <strong class="text-amber-400 font-black text-sm" x-text="koin"></strong>
                </div>
                <button 
                    type="button" 
                    @click="showResultModal = false; window.location.reload();"
                    class="px-8 py-3 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-extrabold transition cursor-pointer shadow-lg shadow-blue-600/30"
                >
                    Selesai & Tutup
                </button>
            </div>
        </div>
    </div>

</div>

@push('scripts')
@php
    $gachaBanners = collect($banners)
        ->filter(fn($b) => !empty($b['gif_url']))
        ->map(fn($b) => [
            'kode'        => $b['kode'],
            'nama'        => $b['nama'],
            'deskripsi'   => $b['deskripsi'],
            'rarity'      => $b['rarity'] ?? 'epic',
            'badge'       => $b['badge'] ?? 'Banner GIF',
            'asset_url'   => asset($b['gif_url']),
            'is_unlocked' => (bool) ($b['is_unlocked'] ?? false),
        ])
        ->values()
        ->all();

    if (empty($gachaBanners)) {
        $gachaBanners = [[
            'kode'        => 'space_hero',
            'nama'        => 'Space Horizon',
            'deskripsi'   => 'Dapatkan item Power untuk mendominasi Kuis Petualangan!',
            'rarity'      => 'epic',
            'badge'       => 'Epic Space',
            'asset_url'   => asset('images/Banner/space-hero.gif'),
            'is_unlocked' => false,
        ]];
    }
@endphp
<script>
function genshinWishSystem() {
    return {
        koin: {{ (int) ($siswa->koin ?? 0) }},
        pityCounter: {{ (int) ($siswa->pity_gacha ?? 0) }},
        loading: false,
        isAnimating: false,
        showResultModal: false,
        wishResults: [],
        wonBanners: [],
        wishHighestRarity: 'common',
        wishAnimKey: 0,
        animationTimer: null,

        availableBanners: @json($gachaBanners),
        activeBannerIdx: 0,
        isBannerSwitching: false,
        bannerSwitchTimer: null,
        carouselTimer: null,

        get currentBanner() {
            return this.availableBanners[this.activeBannerIdx] || this.availableBanners[0];
        },

        init() {
            this.startCarousel();
        },

        startCarousel() {
            if (this.availableBanners.length <= 1) return;
            this.stopCarousel();
            this.carouselTimer = setInterval(() => {
                this.nextBanner();
            }, 6500);
        },

        stopCarousel() {
            if (this.carouselTimer) {
                clearInterval(this.carouselTimer);
                this.carouselTimer = null;
            }
        },

        pauseCarousel() {
            this.stopCarousel();
        },

        resumeCarousel() {
            this.startCarousel();
        },

        switchToBanner(idx) {
            if (idx === this.activeBannerIdx) return;
            if (this.bannerSwitchTimer) clearTimeout(this.bannerSwitchTimer);

            this.isBannerSwitching = true;
            this.bannerSwitchTimer = setTimeout(() => {
                this.activeBannerIdx = idx;
                this.isBannerSwitching = false;
                this.bannerSwitchTimer = null;
            }, 220);
        },

        selectBanner(idx) {
            this.switchToBanner(idx);
            this.startCarousel();
        },

        nextBanner() {
            if (this.availableBanners.length <= 1) return;
            const nextIdx = (this.activeBannerIdx + 1) % this.availableBanners.length;
            this.switchToBanner(nextIdx);
        },

        prevBanner() {
            if (this.availableBanners.length <= 1) return;
            const prevIdx = (this.activeBannerIdx - 1 + this.availableBanners.length) % this.availableBanners.length;
            this.switchToBanner(prevIdx);
        },

        computeHighestRarity(items, banners) {
            if (banners && banners.length > 0) return 'jackpot';
            const rarities = (items || []).map(i => i.power?.rarity || 'common');
            if (rarities.includes('epic')) return 'epic';
            if (rarities.includes('rare')) return 'rare';
            return 'common';
        },

        skipAnimation() {
            if (this.animationTimer) {
                clearTimeout(this.animationTimer);
                this.animationTimer = null;
            }
            this.isAnimating = false;
            this.showResultModal = true;
        },

        executeWish(rolls) {
            if (this.loading || this.isAnimating) return;
            this.loading = true;

            fetch('{{ route('siswa.gacha.pull') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ rolls: rolls })
            })
            .then(res => res.json())
            .then(data => {
                this.loading = false;
                if (data.success) {
                    this.koin = data.sisa_koin;
                    this.pityCounter = data.pity_counter;
                    this.wishResults = data.items || [];
                    this.wonBanners = (data.items || [])
                        .map(item => item.banner)
                        .filter(Boolean);

                    this.wishHighestRarity = this.computeHighestRarity(this.wishResults, this.wonBanners);
                    this.wishAnimKey = Date.now();
                    this.isAnimating = true;

                    this.animationTimer = setTimeout(() => {
                        this.isAnimating = false;
                        this.showResultModal = true;
                        this.animationTimer = null;
                    }, 5800);
                } else {
                    alert(data.message || 'Wish gagal diproses.');
                }
            })
            .catch(() => {
                this.loading = false;
                alert('Terjadi kesalahan jaringan.');
            });
        },

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

