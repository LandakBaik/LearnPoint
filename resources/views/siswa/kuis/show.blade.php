@extends('layouts.siswa')

@section('title', 'Peta Petualangan - ' . ($quiz->judul ?? 'Kuis'))

@section('content')
<div 
    class="max-w-7xl mx-auto space-y-6 pb-12"
    x-data="{
        selectedLevelId: {{ $defaultSelectedLevelId ?? 1 }},
        levels: {{ Js::from($levelsData) }},
        get currentLevel() {
            if (!this.levels || this.levels.length === 0) return null;
            return this.levels.find(l => l.id === this.selectedLevelId) || this.levels[0];
        },
        selectLevel(id) {
            this.selectedLevelId = id;
        }
    }"
>

    {{-- 1. BREADCRUMBS & TOMBOL KEMBALI --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
        <div class="flex items-center gap-2 font-semibold text-slate-400 flex-wrap">
            <a href="{{ route('siswa.dashboard') }}" class="hover:text-blue-600 transition flex items-center gap-1.5">
                <i class="fa-solid fa-house text-[11px] text-slate-400"></i>
                <span>Beranda</span>
            </a>
            <span class="text-slate-300">/</span>
            <a href="{{ route('siswa.kuis') }}" class="hover:text-blue-600 transition">
                Kuis
            </a>
            <span class="text-slate-300">/</span>
            <a href="{{ route('siswa.kuis', ['mapel_id' => $mapel->id ?? 1]) }}" class="hover:text-blue-600 transition">
                {{ $mapel->nama_mapel ?? 'Mata Pelajaran' }}
            </a>
            <span class="text-slate-300">/</span>
            <span class="text-slate-800 font-bold truncate max-w-xs">
                {{ $quiz->judul }}
            </span>
        </div>

        <a 
            href="{{ route('siswa.kuis', ['mapel_id' => $mapel->id ?? null]) }}" 
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 hover:text-slate-900 font-bold text-xs shadow-2xs transition shrink-0 self-start sm:self-auto"
        >
            <i class="fa-solid fa-arrow-left text-xs text-slate-500"></i>
            <span>Kembali</span>
        </a>
    </div>

    {{-- Alert Flash Messages --}}
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0 text-sm font-bold shadow-xs">
                    <i class="fa-solid fa-check"></i>
                </div>
                <p class="text-xs sm:text-sm font-bold text-emerald-900 leading-snug">
                    {{ session('success') }}
                </p>
            </div>
            <button type="button" @click="$el.closest('div').remove()" class="text-emerald-500 hover:text-emerald-700 p-1">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>
    @endif

    @if(session('warning') || session('error'))
        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 flex items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 text-sm font-bold shadow-xs">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <p class="text-xs sm:text-sm font-bold text-amber-900 leading-snug">
                    {{ session('warning') ?? session('error') }}
                </p>
            </div>
            <button type="button" @click="$el.closest('div').remove()" class="text-amber-500 hover:text-amber-700 p-1">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>
    @endif

    {{-- 2. TOP BANNER CARD --}}
    <div class="bg-[#EFF5FF] border border-[#DCE9FE] rounded-3xl p-6 sm:p-7 md:p-8 flex flex-col md:flex-row md:items-center justify-between gap-6 shadow-2xs">
        {{-- Sisi Kiri: Icon + Judul + Deskripsi --}}
        <div class="flex items-center gap-4 sm:gap-5 min-w-0">
            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-[#2563EB] text-white flex items-center justify-center shrink-0 shadow-sm shadow-blue-500/20">
                <svg class="w-7 h-7 sm:w-8 sm:h-8 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                    <path d="M2 17l10 5 10-5"></path>
                    <path d="M2 12l10 5 10-5"></path>
                </svg>
            </div>

            <div class="min-w-0">
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h1 class="text-xl sm:text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight leading-tight truncate">
                        {{ $quiz->judul }}
                    </h1>
                    @php
                        $arcBadgeStyle = match($currentArc ?? 'Mudah') {
                            'Sulit' => 'bg-rose-50 text-rose-600 border-rose-200',
                            'Sedang' => 'bg-amber-50 text-amber-700 border-amber-200',
                            default => 'bg-[#EAF8F1] text-[#10B981] border-[#C6F0DB]',
                        };
                    @endphp
                    <span class="px-3 py-0.5 rounded-full text-xs font-bold border shrink-0 {{ $arcBadgeStyle }}">
                        Arc {{ $currentArc ?? 'Mudah' }}
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-slate-500 font-medium mt-1 leading-relaxed">
                    {{ $quiz->deskripsi ?: 'Selesaikan tiap level secara berurutan dari Mudah, Sedang, hingga Sulit untuk menuntaskan misi!' }}
                </p>
            </div>
        </div>

        {{-- Sisi Kanan: Widget Progres Penyelamatan & Persentase --}}
        <div class="bg-white rounded-2xl py-3 px-5 border border-blue-100 shadow-2xs flex items-center gap-4 shrink-0 self-start md:self-auto">
            <div class="text-left">
                <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                    Progres Penyelamatan
                </div>
                <div class="text-sm sm:text-base font-extrabold text-[#2563EB] mt-0.5 whitespace-nowrap">
                    {{ $totalSelesai }} dari {{ $totalLevel }} Level Selesai
                </div>
            </div>
            <div class="w-12 h-12 rounded-full bg-[#EFF6FF] border border-[#BFDBFE] flex items-center justify-center text-sm font-black text-[#2563EB] shrink-0">
                {{ $persentase }}%
            </div>
        </div>
    </div>

    @if(count($levelsData) === 0)
        {{-- Empty State jika Guru belum membuat soal/level sama sekali --}}
        <div class="bg-white rounded-3xl border border-slate-200/90 p-12 text-center shadow-xs space-y-4">
            <div class="w-16 h-16 rounded-2xl bg-blue-50 text-[#2563EB] flex items-center justify-center mx-auto text-2xl">
                <i class="fa-solid fa-route"></i>
            </div>
            <div class="space-y-1 max-w-md mx-auto">
                <h2 class="text-lg font-extrabold text-slate-800">Level Petualangan Belum Tersedia</h2>
                <p class="text-xs sm:text-sm text-slate-500">
                    Guru belum mengonfigurasi soal pada level kuis ini. Silakan kembali lagi nanti.
                </p>
            </div>
        </div>
    @else
        {{-- 3. GRID 2 KOLOM (PETA PETUALANGAN & DETAIL LEVEL) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            {{-- KOLOM KIRI: JALUR PETUALANGAN BELAJAR (8 KOLOM / ~65%) --}}
            <div class="lg:col-span-8 bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-8 shadow-xs flex flex-col justify-between relative overflow-hidden">

                {{-- Header Jalur Petualangan --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="text-[#2563EB] text-lg">
                            <svg class="w-5 h-5 text-[#2563EB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="18" cy="5" r="3"></circle>
                                <circle cx="6" cy="12" r="3"></circle>
                                <circle cx="18" cy="19" r="3"></circle>
                                <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
                                <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
                            </svg>
                        </div>
                        <h2 class="text-base sm:text-lg font-extrabold text-slate-800 tracking-tight">
                            Jalur Petualangan Belajar
                        </h2>
                    </div>

                    {{-- Indikator Progres & Status Pills --}}
                    <div class="flex items-center gap-2 flex-wrap">
                        <div class="inline-flex items-center gap-3 px-3.5 py-1.5 rounded-full bg-slate-50 border border-slate-200/80 text-xs font-semibold text-slate-600 shrink-0">
                            <span class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-[#10B981]"></span>
                                <span>{{ $totalSelesai }} Selesai</span>
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                                <span>{{ $totalTersisa }} Tersisa</span>
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Ringkasan Alur Arc Otomatis (Read-Only Indicator: Mudah -> Sedang -> Sulit) --}}
                @if(!empty($arcSummaries))
                    <div class="pt-4 pb-2 flex items-center justify-center gap-2 sm:gap-3 flex-wrap">
                        @foreach($arcSummaries as $arcIdx => $arcSum)
                            @php
                                $pillClass = match($arcSum['status']) {
                                    'selesai' => 'bg-emerald-50 border-emerald-200 text-emerald-700',
                                    'aktif' => 'bg-blue-50 border-blue-200 text-[#2563EB]',
                                    default => 'bg-slate-50 border-slate-200 text-slate-400',
                                };
                            @endphp
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border text-xs font-bold {{ $pillClass }}">
                                @if($arcSum['status'] === 'selesai')
                                    <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                                @elseif($arcSum['status'] === 'aktif')
                                    <span class="w-2 h-2 rounded-full bg-[#2563EB] animate-pulse"></span>
                                @else
                                    <i class="fa-solid fa-lock text-[10px]"></i>
                                @endif
                                <span>{{ $arcSum['nama'] }}</span>
                                <span class="text-[11px] opacity-80">({{ $arcSum['completed_levels'] }}/{{ $arcSum['total_levels'] }})</span>
                            </div>

                            @if($arcIdx < count($arcSummaries) - 1)
                                <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
                            @endif
                        @endforeach
                    </div>
                @endif

                {{-- Canvas Peta Interaktif Berurutan (Single Continuous S-Curve Roadmap) --}}
                <div class="relative w-full my-2 select-none" style="height: {{ $svgHeight }}px;">

                    {{-- Jalur Garis Bezier SVG Dinamis --}}
                    <svg 
                        class="w-full h-full absolute inset-0 pointer-events-none" 
                        viewBox="0 0 600 {{ $svgHeight }}" 
                        preserveAspectRatio="none"
                    >
                        @for($i = 0; $i < count($levelsData) - 1; $i++)
                            @php
                                $curr = $levelsData[$i];
                                $next = $levelsData[$i + 1];
                                $x1 = $curr['x'];
                                $y1 = $curr['y'];
                                $x2 = $next['x'];
                                $y2 = $next['y'];
                                $midY = (int) round(($y1 + $y2) / 2);
                                $isDoneLine = $curr['status'] === 'selesai';
                            @endphp
                            <path 
                                d="M {{ $x1 }} {{ $y1 }} C {{ $x1 }} {{ $midY }}, {{ $x2 }} {{ $midY }}, {{ $x2 }} {{ $y2 }}" 
                                stroke="{{ $isDoneLine ? '#15803D' : '#CBD5E1' }}" 
                                stroke-width="{{ $isDoneLine ? '3.5' : '3' }}" 
                                @if(!$isDoneLine) stroke-dasharray="7,7" @endif
                                fill="none" 
                                stroke-linecap="round"
                            />
                        @endfor
                    </svg>

                    {{-- Render Checkpoint Transisi Arc & Node Level secara Berurutan --}}
                    @foreach($levelsData as $idx => $lvl)
                        @php
                            $leftPercent = round(($lvl['x'] / 600) * 100, 2);
                            $topPx = $lvl['y'];
                            $prevLvl = $idx > 0 ? $levelsData[$idx - 1] : null;
                        @endphp

                        {{-- Penanda Transisi Arc (Mudah -> Sedang -> Sulit) --}}
                        @if($lvl['is_first_in_arc'])
                            @php
                                $checkpointTop = $idx === 0
                                    ? max(22, $topPx - 58)
                                    : (int) round(($prevLvl['y'] + $topPx) / 2);

                                $arcChipStyle = match($lvl['arc']) {
                                    'Mudah' => 'bg-emerald-50/95 border-emerald-200 text-emerald-700',
                                    'Sedang' => 'bg-amber-50/95 border-amber-200 text-amber-700',
                                    'Sulit' => 'bg-rose-50/95 border-rose-200 text-rose-700',
                                    default => 'bg-slate-50 border-slate-200 text-slate-600',
                                };
                            @endphp
                            <div 
                                class="absolute left-1/2 -translate-x-1/2 -translate-y-1/2 z-10 pointer-events-none flex items-center gap-2 px-3.5 py-1 rounded-full border shadow-2xs text-[11px] font-extrabold tracking-wide uppercase {{ $arcChipStyle }}"
                                style="top: {{ $checkpointTop }}px;"
                            >
                                <span class="flex items-center gap-0.5">
                                    @for($d = 1; $d <= 3; $d++)
                                        <span class="w-1.5 h-1.5 rounded-full {{ $d <= $lvl['dots'] ? 'bg-current' : 'bg-slate-300' }}"></span>
                                    @endfor
                                </span>
                                <span>Arc {{ $lvl['arc'] }}</span>
                            </div>
                        @endif

                        {{-- Tombol Node Level --}}
                        <button 
                            type="button" 
                            @click="selectLevel({{ $lvl['id'] }})"
                            class="absolute -translate-x-1/2 -translate-y-1/2 group cursor-pointer focus:outline-none transition-transform hover:scale-105 active:scale-95 z-20"
                            style="left: {{ $leftPercent }}%; top: {{ $topPx }}px;"
                            title="{{ $lvl['full_nama'] }}"
                        >
                            @if($lvl['status'] === 'selesai')
                                {{-- Status: Selesai --}}
                                <div 
                                    class="relative w-14 h-14 rounded-full border-2 {{ $lvl['is_final'] ? 'border-amber-400 bg-amber-50 text-amber-600' : 'border-[#10B981] bg-[#F0FDF4] text-[#10B981]' }} flex items-center justify-center font-bold text-xl shadow-xs transition"
                                    :class="selectedLevelId === {{ $lvl['id'] }} ? 'ring-4 {{ $lvl['is_final'] ? 'ring-amber-200' : 'ring-emerald-200' }} scale-105' : ''"
                                >
                                    @if($lvl['is_final'])
                                        <i class="fa-solid fa-trophy text-amber-500 text-lg"></i>
                                    @else
                                        <i class="fa-solid fa-check text-xl text-[#10B981]"></i>
                                    @endif
                                    <span class="absolute -top-1 -right-1 w-5 h-5 rounded-full {{ $lvl['is_final'] ? 'bg-amber-600' : 'bg-[#065F46]' }} text-white text-[10px] font-black flex items-center justify-center shadow-xs">
                                        {{ $lvl['level_num'] }}
                                    </span>
                                    <span class="absolute -bottom-5 left-1/2 -translate-x-1/2 text-[10px] font-bold text-slate-500 whitespace-nowrap">
                                        {{ $lvl['arc'] }} {{ $lvl['level_num'] }}
                                    </span>
                                </div>
                            @elseif($lvl['status'] === 'aktif')
                                {{-- Status: Aktif --}}
                                <div 
                                    class="relative w-16 h-16 rounded-full bg-[#2563EB] text-white flex items-center justify-center font-black text-2xl shadow-lg shadow-blue-500/30 transition"
                                    :class="selectedLevelId === {{ $lvl['id'] }} ? 'ring-8 ring-blue-200 scale-105' : 'ring-8 ring-blue-100'"
                                >
                                    <span>{{ $lvl['level_num'] }}</span>
                                    <span class="absolute -bottom-6 left-1/2 -translate-x-1/2 px-2.5 py-0.5 rounded-full bg-[#EFF6FF] border border-blue-200 text-[#2563EB] text-[11px] font-black shadow-2xs whitespace-nowrap">
                                        Aktif • {{ $lvl['arc'] }} {{ $lvl['level_num'] }}
                                    </span>
                                </div>
                            @else
                                {{-- Status: Terkunci --}}
                                <div 
                                    class="relative w-13 h-13 rounded-full border-2 {{ $lvl['is_final'] ? 'border-dashed border-slate-300 bg-white' : 'border-slate-200 bg-[#F8FAFC]' }} text-slate-400 flex items-center justify-center transition group-hover:border-slate-300"
                                    :class="selectedLevelId === {{ $lvl['id'] }} ? 'ring-4 ring-slate-200 border-slate-400' : ''"
                                >
                                    @if($lvl['is_final'])
                                        <i class="fa-solid fa-award text-slate-400 text-base"></i>
                                    @else
                                        <i class="fa-solid fa-lock text-sm text-slate-400"></i>
                                    @endif
                                    <span class="absolute -bottom-5 left-1/2 -translate-x-1/2 text-[11px] font-bold text-slate-400 whitespace-nowrap">
                                        {{ $lvl['arc'] }} {{ $lvl['level_num'] }}
                                    </span>
                                </div>
                            @endif
                        </button>
                    @endforeach

                </div>

                {{-- Footer Note dengan Info Icon --}}
                <div class="pt-4 border-t border-slate-100 flex items-center gap-2.5 text-xs text-slate-400 font-medium">
                    <i class="fa-solid fa-circle-info text-[#2563EB] text-sm shrink-0"></i>
                    <span x-text="currentLevel ? currentLevel.keterangan : 'Selesaikan tiap level secara berurutan untuk membuka level dan Arc kesulitan berikutnya.'">
                        Selesaikan tiap level secara berurutan untuk membuka level dan Arc kesulitan berikutnya.
                    </span>
                </div>

            </div>

            {{-- KOLOM KANAN: DETAIL LEVEL (4 KOLOM / ~35%) --}}
            <div class="lg:col-span-4 bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-7 shadow-xs space-y-5 sticky top-24" x-show="currentLevel">

                {{-- Pill Tag: Detail Level & Tahap --}}
                <div class="flex items-center justify-between gap-2">
                    <span class="inline-block px-3 py-1 rounded-lg bg-[#EFF6FF] text-[#2563EB] text-xs font-bold">
                        Detail Level
                    </span>
                    <span class="text-xs font-bold text-slate-400" x-text="'Tahap ' + currentLevel.step + ' dari {{ $totalLevel }}'"></span>
                </div>

                {{-- Judul Level & Arc Kesulitan --}}
                <div>
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight" x-text="currentLevel.nama">
                        Level 1
                    </h2>

                    {{-- Tingkat Kesulitan (Otomatis mengikuti Arc level) --}}
                    <div class="flex items-center gap-2 mt-2">
                        <span class="text-xs font-medium text-slate-500">Tingkat Kesulitan:</span>
                        <span 
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border text-xs font-bold"
                            :style="`background-color: ${currentLevel.badge_bg}; color: ${currentLevel.badge_text}; border-color: ${currentLevel.badge_border};`"
                        >
                            <span class="flex items-center gap-0.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                <span class="w-1.5 h-1.5 rounded-full" :class="currentLevel.dots >= 2 ? 'bg-current' : 'bg-slate-300'"></span>
                                <span class="w-1.5 h-1.5 rounded-full" :class="currentLevel.dots >= 3 ? 'bg-current' : 'bg-slate-300'"></span>
                            </span>
                            <span x-text="currentLevel.kesulitan">Mudah</span>
                        </span>
                    </div>
                </div>

                {{-- Info Cards / Statistics (Data Riil Konfigurasi Guru) --}}
                <div class="space-y-3 pt-1">
                    {{-- 1. Jumlah Soal Riil --}}
                    <div class="bg-[#F8FAFC] border border-slate-100 rounded-2xl p-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#2563EB] flex items-center justify-center text-sm font-bold">
                                <i class="fa-regular fa-file-lines text-base"></i>
                            </div>
                            <span class="text-sm font-bold text-slate-700">Jumlah Soal</span>
                        </div>
                        <span class="text-sm font-extrabold text-[#2563EB]" x-text="currentLevel.jumlah_soal + ' Butir Soal'">
                            5 Butir Soal
                        </span>
                    </div>

                    {{-- 2. Estimasi Waktu Riil --}}
                    <div class="bg-[#F8FAFC] border border-slate-100 rounded-2xl p-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#2563EB] flex items-center justify-center text-sm font-bold">
                                <i class="fa-regular fa-clock text-base"></i>
                            </div>
                            <span class="text-sm font-bold text-slate-700">Estimasi Waktu</span>
                        </div>
                        <span class="text-sm font-extrabold text-slate-900" x-text="currentLevel.estimasi_waktu + ' Menit'">
                            10 Menit
                        </span>
                    </div>

                    {{-- 3. Progres Riil --}}
                    <div class="bg-[#F8FAFC] border border-slate-100 rounded-2xl p-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-[#10B981] flex items-center justify-center text-sm font-bold">
                                <i class="fa-regular fa-circle-check text-base"></i>
                            </div>
                            <span class="text-sm font-bold text-slate-700">Progres</span>
                        </div>
                        <span class="text-sm font-extrabold text-[#10B981]" x-text="currentLevel.progres">
                            0/5
                        </span>
                    </div>
                </div>

                {{-- Tombol Aksi Utama --}}
                <div class="pt-2 space-y-2.5">
                    <template x-if="currentLevel.status === 'aktif'">
                        <a 
                            :href="'{{ route('siswa.kuis.kerjakan', $quiz->id) }}?arc=' + encodeURIComponent(currentLevel.arc) + '&level=' + currentLevel.level_num"
                            class="w-full py-4 rounded-2xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-extrabold text-base flex items-center justify-center gap-2.5 shadow-md shadow-blue-500/20 transition-all hover:translate-y-[-1px] active:translate-y-[0px] group"
                        >
                            <span>Mulai Kuis Sekarang</span>
                            <i class="fa-solid fa-arrow-right text-sm group-hover:translate-x-1 transition-transform"></i>
                        </a>
                    </template>

                    <template x-if="currentLevel.status === 'selesai'">
                        <div class="space-y-2.5">
                            <a 
                                :href="'{{ route('siswa.kuis.hasil', $quiz->id) }}?arc=' + encodeURIComponent(currentLevel.arc) + '&level=' + currentLevel.level_num"
                                class="w-full py-3.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-sm flex items-center justify-center gap-2 shadow-sm transition-all"
                            >
                                <i class="fa-solid fa-chart-pie text-xs"></i>
                                <span>Lihat Hasil Level Ini</span>
                            </a>
                            <a 
                                :href="'{{ route('siswa.kuis.kerjakan', $quiz->id) }}?arc=' + encodeURIComponent(currentLevel.arc) + '&level=' + currentLevel.level_num"
                                class="w-full py-3 rounded-2xl bg-white hover:bg-slate-50 text-[#2563EB] border border-blue-200 font-bold text-xs flex items-center justify-center gap-2 transition-all"
                            >
                                <i class="fa-solid fa-rotate-right text-xs"></i>
                                <span>Kerjakan Ulang Level</span>
                            </a>
                        </div>
                    </template>

                    <template x-if="currentLevel.status === 'terkunci'">
                        <button 
                            type="button" 
                            disabled 
                            class="w-full py-4 rounded-2xl bg-slate-100 text-slate-400 font-bold text-sm flex items-center justify-center gap-2 cursor-not-allowed border border-slate-200"
                        >
                            <i class="fa-solid fa-lock text-xs"></i>
                            <span>Level Masih Terkunci</span>
                        </button>
                    </template>
                </div>

                {{-- Subtext Keterangan Mekanisme --}}
                <div class="text-xs text-slate-400 font-medium text-center flex items-center justify-center gap-1.5 pt-1">
                    <i class="fa-solid fa-wand-magic-sparkles text-xs text-blue-500"></i>
                    <span>Level berikutnya terbuka otomatis setelah selesai</span>
                </div>

            </div>

        </div>
    @endif

</div>
@endsection
