@extends('layouts.siswa')

@section('title', $viewMode === 'mission' ? 'Planet ' . $selectedPlanet->nama . ' - Misi Kuis Petualangan' : 'Kuis Petualangan Siswa - LearnPoint')

@section('content')
<div class="space-y-6">

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
            <span class="text-slate-300">›</span>
            <a href="{{ route('siswa.kuis') }}" class="hover:text-blue-600 transition">
                Kuis
            </a>
            <span class="text-slate-300">›</span>
            <span class="text-[#2563EB] font-bold">Matematika</span>
        </nav>

        {{-- 2. Hero Card: Kuis Petualangan --}}
        <div class="relative overflow-hidden rounded-[28px] bg-gradient-to-r from-[#EBF3FE] via-[#EEF5FF] to-[#E3EFFF] p-8 md:p-10 border border-blue-100/60 shadow-xs">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="max-w-xl">
                    <h1 class="text-2xl md:text-3xl lg:text-4xl font-extrabold tracking-tight text-slate-900 leading-tight">
                        Kuis Petualangan
                    </h1>
                    <p class="text-sm md:text-base text-slate-600 mt-2.5 leading-relaxed font-medium">
                        Kamu adalah pengembara antar galaksi yang suka menolong<br>
                        Bantu kami menyelamatkan dunia
                    </p>
                </div>

                {{-- Top 3 Leaderboard Podium Profile Circles (Kiri: Top 2, Tengah: Top 1, Kanan: Top 3) --}}
                <div class="relative flex items-end justify-center gap-2.5 md:gap-3.5 shrink-0 select-none pt-4">
                    {{-- TOP 2 (KIRI) --}}
                    @if(isset($top2) && $top2)
                        <div class="flex flex-col items-center group relative -mr-1 z-10 transition-transform hover:scale-105" title="Peringkat 2: {{ $top2->name }} ({{ number_format($top2->skor ?? 0, 0) }} XP)">
                            <div class="relative">
                                <div class="w-16 h-16 md:w-18 md:h-18 rounded-full border-2 border-white shadow-md overflow-hidden bg-slate-300 flex items-center justify-center ring-2 ring-slate-200">
                                    @if(!empty($top2->foto))
                                        <img src="{{ asset('storage/' . $top2->foto) }}" alt="{{ $top2->name }}" class="w-full h-full object-cover">
                                    @else
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($top2->name) }}&background=64748B&color=fff&size=160&bold=true" alt="{{ $top2->name }}" class="w-full h-full object-cover">
                                    @endif
                                </div>
                                <!-- Badge Rank 2 -->
                                <div class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-slate-200 text-slate-800 border-2 border-white font-black text-[10px] flex items-center justify-center shadow-xs">
                                    2
                                </div>
                            </div>
                            <span class="text-[11px] font-bold text-slate-700 mt-1.5 max-w-[70px] truncate text-center">
                                {{ explode(' ', trim($top2->name))[0] }}
                            </span>
                            <span class="text-[9px] font-semibold text-slate-400">Top 2</span>
                        </div>
                    @else
                        {{-- Top 2 Kosong: Tampilkan shape bulat abu-abu polos sesuai mockup --}}
                        <div class="flex flex-col items-center relative -mr-1 z-10">
                            <div class="w-16 h-16 md:w-18 md:h-18 rounded-full bg-[#7E7E7E] shadow-xs"></div>
                        </div>
                    @endif

                    {{-- TOP 1 (TENGAH - LEBIH TINGGI & BESAR) --}}
                    @if(isset($top1) && $top1)
                        <div class="flex flex-col items-center group relative z-20 -mb-1 transition-transform hover:scale-105" title="Peringkat 1: {{ $top1->name }} ({{ number_format($top1->skor ?? 0, 0) }} XP)">
                            <div class="relative">
                                <!-- Mahkota Emas Top 1 -->
                                <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 text-amber-500 text-sm drop-shadow-xs animate-bounce">
                                    <i class="fa-solid fa-crown"></i>
                                </div>
                                <div class="w-20 h-20 md:w-22 md:h-22 rounded-full border-3 border-amber-300 shadow-lg overflow-hidden bg-blue-100 flex items-center justify-center ring-4 ring-amber-100/70">
                                    @if(!empty($top1->foto))
                                        <img src="{{ asset('storage/' . $top1->foto) }}" alt="{{ $top1->name }}" class="w-full h-full object-cover">
                                    @else
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($top1->name) }}&background=2563EB&color=fff&size=160&bold=true" alt="{{ $top1->name }}" class="w-full h-full object-cover">
                                    @endif
                                </div>
                                <!-- Badge Rank 1 -->
                                <div class="absolute -bottom-1.5 -right-1 w-7 h-7 rounded-full bg-amber-400 text-amber-950 border-2 border-white font-black text-xs flex items-center justify-center shadow-xs">
                                    1
                                </div>
                            </div>
                            <span class="text-xs font-black text-slate-900 mt-1.5 max-w-[85px] truncate text-center">
                                {{ explode(' ', trim($top1->name))[0] }}
                            </span>
                            <span class="text-[10px] font-bold text-amber-600">Juara 1</span>
                        </div>
                    @else
                        {{-- Top 1 Kosong: Tampilkan shape bulat abu-abu polos tengah sesuai mockup --}}
                        <div class="flex flex-col items-center relative z-20 -mb-3">
                            <div class="w-20 h-20 md:w-22 md:h-22 rounded-full bg-[#7E7E7E] shadow-xs"></div>
                        </div>
                    @endif

                    {{-- TOP 3 (KANAN) --}}
                    @if(isset($top3) && $top3)
                        <div class="flex flex-col items-center group relative -ml-1 z-10 transition-transform hover:scale-105" title="Peringkat 3: {{ $top3->name }} ({{ number_format($top3->skor ?? 0, 0) }} XP)">
                            <div class="relative">
                                <div class="w-16 h-16 md:w-18 md:h-18 rounded-full border-2 border-white shadow-md overflow-hidden bg-amber-100 flex items-center justify-center ring-2 ring-amber-200/60">
                                    @if(!empty($top3->foto))
                                        <img src="{{ asset('storage/' . $top3->foto) }}" alt="{{ $top3->name }}" class="w-full h-full object-cover">
                                    @else
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($top3->name) }}&background=D97706&color=fff&size=160&bold=true" alt="{{ $top3->name }}" class="w-full h-full object-cover">
                                    @endif
                                </div>
                                <!-- Badge Rank 3 -->
                                <div class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-amber-700 text-white border-2 border-white font-black text-[10px] flex items-center justify-center shadow-xs">
                                    3
                                </div>
                            </div>
                            <span class="text-[11px] font-bold text-slate-700 mt-1.5 max-w-[70px] truncate text-center">
                                {{ explode(' ', trim($top3->name))[0] }}
                            </span>
                            <span class="text-[9px] font-semibold text-slate-400">Top 3</span>
                        </div>
                    @else
                        {{-- Top 3 Kosong: Tampilkan shape bulat abu-abu polos sesuai mockup --}}
                        <div class="flex flex-col items-center relative -ml-1 z-10">
                            <div class="w-16 h-16 md:w-18 md:h-18 rounded-full bg-[#7E7E7E] shadow-xs"></div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- 3. Section Title --}}
        <div class="pt-2">
            <h2 class="text-lg md:text-xl font-extrabold text-slate-900 tracking-tight">
                Pilihan pelanet yang ingin diselamatkan!!!
            </h2>
            <p class="text-xs md:text-sm text-slate-500 font-medium mt-0.5">
                Pilih planet yang mau ditolong
            </p>
        </div>

        {{-- 4. Planet Cards Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($planets as $planet)
                <div class="bg-white rounded-[24px] border border-slate-100 p-6 shadow-xs hover:shadow-md transition-all duration-200 flex flex-col justify-between group">
                    <div>
                        {{-- Top Icon & Issue Badge --}}
                        <div class="flex items-center justify-between gap-3 mb-6">
                            <div class="w-12 h-12 rounded-2xl {{ $planet->bg_color }} {{ $planet->text_color }} flex items-center justify-center font-bold text-xl shadow-2xs">
                                {{ $planet->simbol }}
                            </div>
                            <span class="px-3.5 py-1.5 rounded-full bg-[#F8FAFC] border border-slate-200/80 text-slate-500 text-xs font-semibold">
                                {{ $planet->masalah_terdeteksi }} masalah terdeteksi
                            </span>
                        </div>

                        {{-- Title & Description --}}
                        <h3 class="text-base md:text-lg font-extrabold text-slate-900 group-hover:text-blue-600 transition mb-1">
                            {{ $planet->nama }}
                        </h3>
                        <p class="text-xs md:text-sm text-slate-500 font-medium mb-8">
                            {{ $planet->deskripsi }}
                        </p>
                    </div>

                    {{-- Pergi Action Button --}}
                    <a 
                        href="{{ route('siswa.kuis', ['mapel_id' => $planet->id]) }}"
                        class="w-full py-2.5 px-4 rounded-xl border border-slate-200 hover:border-blue-500 hover:text-blue-600 text-slate-700 font-semibold text-xs flex items-center justify-center gap-2 transition bg-white hover:bg-blue-50/20"
                    >
                        <span>Pergi</span>
                        <i class="fa-solid fa-arrow-right text-[11px]"></i>
                    </a>
                </div>
            @endforeach
        </div>

    @else
        {{-- ================================================================= --}}
        {{-- MODE 2: PLANET MISSION STAGE (SESUAI GAMBAR 2)                  --}}
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
            <span class="text-slate-300">›</span>
            <span class="text-[#2563EB] font-bold">Misi Penyelamatan</span>
        </nav>

        {{-- 2. Hero Card: Planet Info & Sector Stats --}}
        <div class="relative overflow-hidden rounded-[28px] bg-gradient-to-r from-[#EBF3FE] via-[#EEF5FF] to-[#E3EFFF] p-6 md:p-8 border border-blue-100/60 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-1">
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-slate-900 leading-tight">
                        Planet {{ $selectedPlanet->nama }}
                    </h1>
                    <a href="{{ route('siswa.kuis') }}" class="text-xs px-2.5 py-1 rounded-lg bg-white/70 hover:bg-white text-blue-600 font-semibold border border-blue-100 transition shadow-2xs" title="Ganti Planet">
                        <i class="fa-solid fa-rotate-left mr-1"></i> Ganti Planet
                    </a>
                </div>
                <p class="text-xs md:text-sm text-slate-600 font-medium">
                    {{ $selectedPlanet->lore ?? 'Lorem Ipsum' }}
                </p>
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

                {{-- Energi & XP --}}
                <div>
                    <div class="text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-1.5">
                        ENERGI & XP
                    </div>
                    <div class="flex items-center gap-3 font-bold text-sm text-slate-800">
                        <span class="flex items-center gap-1.5 text-amber-500">
                            <i class="fa-solid fa-bolt"></i>
                            <strong class="text-slate-800 font-extrabold">{{ $energi }}</strong>
                        </span>
                        <span class="flex items-center gap-1.5 text-blue-600">
                            <i class="fa-solid fa-trophy text-blue-600"></i>
                            <strong class="text-slate-800 font-extrabold">{{ $xp }}</strong>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. Two-Column Layout: Tahapan Penyelamatan vs Target & Leaderboard --}}
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
                    @foreach($zonas as $zona)
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
                                        <div class="text-xs text-slate-400 font-medium mt-0.5">
                                            Skor Sempurna: {{ number_format($zona->skor, 0) }}/100 • {{ $zona->bintang }} Bintang
                                        </div>
                                    </div>
                                </div>

                                @if($zona->quiz)
                                    <a 
                                        href="{{ route('siswa.kuis.hasil', $zona->quiz->id) }}"
                                        class="px-4 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-xs font-bold text-slate-700 flex items-center gap-1.5 transition shrink-0"
                                    >
                                        <i class="fa-solid fa-rotate-right text-[11px] text-slate-400"></i>
                                        <span>Review</span>
                                    </a>
                                @else
                                    <button 
                                        type="button" 
                                        onclick="alert('Riwayat pengerjaan: Skor Sempurna 100/100 (3 Bintang)');"
                                        class="px-4 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-xs font-bold text-slate-700 flex items-center gap-1.5 transition shrink-0"
                                    >
                                        <i class="fa-solid fa-rotate-right text-[11px] text-slate-400"></i>
                                        <span>Review</span>
                                    </button>
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
                                        href="{{ route('siswa.kuis.kerjakan', $zona->quiz->id) }}"
                                        class="w-full py-3 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-sm flex items-center justify-center gap-2 transition shadow-sm shadow-blue-500/20"
                                    >
                                        <span>{{ $zona->buka_level_label }}</span>
                                        <i class="fa-solid fa-arrow-right text-xs"></i>
                                    </a>
                                @else
                                    <button 
                                        type="button"
                                        onclick="alert('Memulai petualangan kuis level aktif!');"
                                        class="w-full py-3 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-sm flex items-center justify-center gap-2 transition shadow-sm shadow-blue-500/20"
                                    >
                                        <span>{{ $zona->buka_level_label }}</span>
                                        <i class="fa-solid fa-arrow-right text-xs"></i>
                                    </button>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- Kolom Kanan: Target Penyelamatan, Leaderboard & Tips (40%) --}}
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
                            href="{{ route('siswa.kuis.kerjakan', $activeZona->quiz->id) }}"
                            class="w-full py-3 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-sm flex items-center justify-center gap-2 transition shadow-sm"
                        >
                            <i class="fa-solid fa-rocket text-xs"></i>
                            <span>Mulai Kuis</span>
                        </a>
                    @else
                        <button 
                            type="button"
                            onclick="alert('Memulai petualangan kuis!');"
                            class="w-full py-3 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-sm flex items-center justify-center gap-2 transition shadow-sm"
                        >
                            <i class="fa-solid fa-rocket text-xs"></i>
                            <span>Mulai Kuis</span>
                        </button>
                    @endif
                </div>

                {{-- 2. Card: Leaderboard --}}
                <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs space-y-3">
                    <div class="flex items-center gap-2 font-extrabold text-slate-900 text-sm">
                        <i class="fa-solid fa-medal text-amber-500"></i>
                        <span>Leaderboard</span>
                    </div>

                    <div class="space-y-2">
                        @forelse($leaderboard as $lb)
                            <div class="{{ $lb['is_user'] ? 'bg-[#EFF6FF] border border-blue-200/60' : 'bg-[#F8FAFC]' }} rounded-xl px-4 py-2.5 flex items-center gap-3 transition">
                                <div class="w-8 h-8 rounded-full {{ $lb['bg'] }} {{ $lb['text'] }} font-bold text-xs flex items-center justify-center shrink-0">
                                    {{ $lb['initials'] }}
                                </div>
                                <span class="text-xs {{ $lb['is_user'] ? 'font-extrabold text-blue-900' : 'font-semibold text-slate-700' }} truncate">
                                    {{ $lb['name'] }}
                                </span>
                            </div>
                        @empty
                            <div class="py-5 text-center text-xs text-slate-400">
                                <i class="fa-solid fa-trophy text-slate-300 text-xl mb-1.5 block"></i>
                                <span>Belum ada data peringkat</span>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- 3. Card: Tips Pill Box --}}
                <div class="bg-[#EFF4FF] border border-[#DBE7FE] rounded-2xl p-4 text-xs text-[#2A4374] flex items-start gap-2.5 shadow-2xs">
                    <i class="fa-regular fa-lightbulb text-indigo-500 mt-0.5 text-sm shrink-0"></i>
                    <div class="leading-relaxed">
                        <strong>Tips:</strong> {{ $tips }}
                    </div>
                </div>

            </div>

        </div>

    @endif

</div>
@endsection
