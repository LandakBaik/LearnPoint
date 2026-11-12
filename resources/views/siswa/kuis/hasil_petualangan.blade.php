@extends('layouts.siswa')

@section('title', 'Hasil Akhir Petualangan: ' . $quiz->judul . ' - LearnPoint')

@push('styles')
<style>
    @media print {
        body {
            background: white !important;
        }
        header, nav, .no-print, button:not(.print-include) {
            display: none !important;
        }
        .print-shadow-none {
            box-shadow: none !important;
            border: 1px solid #e2e8f0 !important;
        }
    }
</style>
@endpush

@section('content')
<div 
    class="max-w-5xl mx-auto space-y-6 pb-16"
    x-data="{
        activeTab: 'semua',
        filterQuestion(soal) {
            if (this.activeTab === 'semua') return true;
            if (this.activeTab === 'benar') return soal.is_benar;
            if (this.activeTab === 'salah') return !soal.is_benar;
            if (this.activeTab === 'power') return soal.has_power;
            return true;
        }
    }"
>

    {{-- 1. BREADCRUMBS & TOP BAR --}}
    <div class="flex items-center justify-between gap-3 flex-wrap text-xs font-semibold text-slate-400 no-print">
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('siswa.dashboard') }}" class="hover:text-blue-600 transition flex items-center gap-1">
                <i class="fa-solid fa-house text-[11px]"></i>
                <span>Beranda</span>
            </a>
            <i class="fa-solid fa-chevron-right text-[9px] text-slate-300"></i>
            <a href="{{ route('siswa.kuis') }}" class="hover:text-blue-600 transition">
                Kuis
            </a>
            <i class="fa-solid fa-chevron-right text-[9px] text-slate-300"></i>
            <a href="{{ route('siswa.kuis.show', $quiz->id) }}" class="hover:text-blue-600 transition">
                Peta Petualangan
            </a>
            <i class="fa-solid fa-chevron-right text-[9px] text-slate-300"></i>
            <span class="text-slate-800 font-bold truncate max-w-xs">
                Hasil Akhir Petualangan
            </span>
        </div>

        <div class="flex items-center gap-2">
            <button 
                type="button" 
                onclick="window.print()" 
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-xs shadow-2xs transition"
                title="Cetak Rekap Hasil Petualangan"
            >
                <i class="fa-solid fa-print text-slate-500"></i>
                <span class="hidden sm:inline">Cetak Rekap</span>
            </button>
            <a 
                href="{{ route('siswa.kuis.show', $quiz->id) }}" 
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-xs shadow-2xs transition"
            >
                <i class="fa-solid fa-map-location-dot text-blue-600"></i>
                <span>Peta Petualangan</span>
            </a>
        </div>
    </div>

    @php
        $skor = (float) $nilaiGabungan;
        $isIstimewa = $skor >= 85;
        $isLulus = $skor >= 70;
        $mapelName = $quiz->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? 'Mata Pelajaran';
        $guruName = $quiz->pengampuKelas?->guruMapel?->guru?->nama_guru ?? 'Guru Pengampu';
        $kelasName = $quiz->pengampuKelas?->kelas?->nama_kelas ?? 'Kelas';
        $totalBintangTerkumpul = $journey['total_bintang'] ?? 0;
        $maxBintangSemuaLevel = $journey['max_bintang'] ?? (count($levelsData) * 3);
        $persentaseBenar = $totalSoal > 0 ? round(($totalBenar / $totalSoal) * 100, 1) : 0;
    @endphp

    {{-- Flash Alert Messages --}}
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between gap-3 shadow-xs no-print">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0 text-sm font-bold shadow-xs">
                    <i class="fa-solid fa-circle-check"></i>
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

    {{-- 2. HERO CELEBRATION CARD: HASIL GABUNGAN PETUALANGAN --}}
    <div class="relative overflow-hidden rounded-[32px] bg-white border border-slate-200/90 shadow-sm p-6 sm:p-8 md:p-10 text-center space-y-6 print-shadow-none">
        {{-- Background Aura Glow --}}
        <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-[500px] h-[500px] 
            {{ $isIstimewa ? 'bg-amber-400/10' : ($isLulus ? 'bg-emerald-400/10' : 'bg-blue-400/10') }} rounded-full blur-3xl pointer-events-none">
        </div>

        {{-- Badge Trophy & Status Penyelamatan --}}
        <div class="relative z-10 flex flex-col items-center">
            <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-3xl flex items-center justify-center text-4xl sm:text-5xl shadow-xl transition-transform hover:scale-105
                {{ $isIstimewa ? 'bg-gradient-to-tr from-amber-500 to-yellow-400 text-white shadow-amber-500/30 ring-8 ring-amber-100/60' :
                   ($isLulus ? 'bg-gradient-to-tr from-emerald-600 to-teal-400 text-white shadow-emerald-500/30 ring-8 ring-emerald-100/60' :
                   'bg-gradient-to-tr from-blue-600 to-indigo-500 text-white shadow-blue-500/30 ring-8 ring-blue-100/60') }}">
                @if($isIstimewa)
                    <i class="fa-solid fa-trophy drop-shadow-xs"></i>
                @elseif($isLulus)
                    <i class="fa-solid fa-medal drop-shadow-xs"></i>
                @else
                    <i class="fa-solid fa-award drop-shadow-xs"></i>
                @endif
            </div>

            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-wider mt-5
                {{ $isIstimewa ? 'bg-amber-50 text-amber-700 border border-amber-200' :
                   ($isLulus ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                   'bg-blue-50 text-blue-700 border border-blue-200') }}">
                <i class="fa-solid fa-shield-halved text-[11px]"></i>
                <span>Hasil Petualangan Lengkap • Misi Selesai</span>
            </div>
        </div>

        {{-- Title, Nama Kuis, Mapel & Guru --}}
        <div class="space-y-2 relative z-10 max-w-2xl mx-auto">
            <h1 class="text-2xl sm:text-3xl md:text-4xl font-black text-slate-900 tracking-tight leading-tight">
                {{ $quiz->judul }}
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 font-medium">
                Planet <strong class="text-slate-800">{{ $mapelName }}</strong> • Kelas <strong class="text-slate-800">{{ $kelasName }}</strong> • Guru: <span class="text-slate-700">{{ $guruName }}</span>
            </p>
        </div>

        {{-- Main Score & Bintang Penanda Nilai Card --}}
        <div class="relative z-10 max-w-md mx-auto p-6 sm:p-7 rounded-3xl bg-gradient-to-b from-slate-50 to-white border border-slate-200/90 shadow-2xs space-y-4">
            <div>
                <span class="text-xs uppercase font-extrabold text-slate-400 tracking-wider">
                    Nilai Akhir Petualangan
                </span>
                <div class="text-5xl sm:text-6xl font-black tracking-tight mt-1
                    {{ $isIstimewa ? 'text-amber-600' : ($isLulus ? 'text-emerald-600' : 'text-blue-600') }}">
                    {{ number_format($skor, 0) }}
                    <span class="text-xl font-bold text-slate-400">/ 100</span>
                </div>
                <span class="text-xs text-slate-500 font-semibold block mt-1">
                    (Gabungan akumulasi nilai dari seluruh {{ $totalSoal }} soal di kuis ini)
                </span>
            </div>

            {{-- Cluster Bintang Penanda Nilai (Aturan: >67 = 3 Bintang, 34-67 = 2 Bintang, 1-33 = 1 Bintang) --}}
            <div class="pt-4 border-t border-slate-200/70 space-y-2">
                <div class="flex items-center justify-center gap-2">
                    @for($s = 1; $s <= 3; $s++)
                        <i class="fa-solid fa-star text-3xl sm:text-4xl transition-transform hover:scale-110
                            {{ $s <= $bintangAkhir ? 'text-amber-400 drop-shadow-md animate-pulse' : 'text-slate-200' }}">
                        </i>
                    @endfor
                </div>
                <div class="text-sm font-black text-amber-800">
                    {{ $bintangAkhir }} dari 3 Bintang Penanda Nilai
                </div>
                <div class="text-[11px] text-slate-400 font-medium bg-white/80 py-1 px-3 rounded-full border border-slate-200 inline-block">
                    Kriteria: Nilai &gt; 67: ⭐⭐⭐ | 34 - 67: ⭐⭐ | ≤ 33: ⭐
                </div>
            </div>

            {{-- Total Bintang Level Terkumpul --}}
            <div class="pt-3 border-t border-slate-200/70 flex items-center justify-between text-xs font-bold text-slate-600 px-2">
                <span class="flex items-center gap-1.5 text-amber-600">
                    <i class="fa-solid fa-award"></i>
                    <span>Total Bintang Semua Level:</span>
                </span>
                <span class="text-slate-900 font-black">
                    {{ $totalBintangTerkumpul }} <span class="text-slate-400 font-normal">/ {{ $maxBintangSemuaLevel }} Bintang</span>
                </span>
            </div>
        </div>

        {{-- 3. STATISTIK GABUNGAN PETUALANGAN --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 max-w-3xl mx-auto pt-2 relative z-10">
            <div class="p-4 rounded-2xl bg-[#F8FAFC] border border-slate-200/80 text-center space-y-1">
                <div class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Benar</div>
                <div class="text-2xl font-black text-emerald-600">{{ $totalBenar }} <span class="text-xs text-slate-400 font-semibold">Soal</span></div>
                <div class="text-[10px] text-slate-400">{{ $persentaseBenar }}% Akurasi</div>
            </div>

            <div class="p-4 rounded-2xl bg-[#F8FAFC] border border-slate-200/80 text-center space-y-1">
                <div class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Salah</div>
                <div class="text-2xl font-black text-rose-500">{{ $totalSalah }} <span class="text-xs text-slate-400 font-semibold">Soal</span></div>
                <div class="text-[10px] text-slate-400">{{ $totalSoal > 0 ? round(($totalSalah / $totalSoal) * 100, 1) : 0 }}% Salah</div>
            </div>

            <div class="p-4 rounded-2xl bg-[#F8FAFC] border border-slate-200/80 text-center space-y-1">
                <div class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Total Soal</div>
                <div class="text-2xl font-black text-slate-800">{{ $totalSoal }} <span class="text-xs text-slate-400 font-semibold">Butir</span></div>
                <div class="text-[10px] text-slate-400">{{ count($levelsData) }} Level Petualangan</div>
            </div>

            <div class="p-4 rounded-2xl bg-[#F8FAFC] border border-slate-200/80 text-center space-y-1">
                <div class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Durasi</div>
                <div class="text-lg sm:text-xl font-black text-blue-600 truncate mt-0.5">{{ $durasiFormatted }}</div>
                <div class="text-[10px] text-slate-400">Total Waktu Kuis</div>
            </div>
        </div>

        {{-- Ringkasan Power (Jika ada yang digunakan) --}}
        @if($usedPowersCount > 0)
            <div class="relative z-10 max-w-2xl mx-auto p-4 rounded-2xl bg-[#EFF5FF] border border-[#DCE9FE] text-left space-y-2.5">
                <div class="flex items-center justify-between gap-2 flex-wrap">
                    <span class="text-xs font-extrabold text-[#2563EB] flex items-center gap-1.5 uppercase tracking-wider">
                        <i class="fa-solid fa-wand-magic-sparkles text-amber-500"></i>
                        <span>Efek Power Petualangan</span>
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full bg-blue-600 text-white text-[10px] font-extrabold">
                        {{ $usedPowersCount }} Kali Power Digunakan
                    </span>
                </div>
                <div class="grid grid-cols-3 gap-2 text-center bg-white rounded-xl p-3 border border-blue-100 text-xs">
                    <div>
                        <div class="text-[10px] text-slate-400 font-semibold">Bonus Pengganda</div>
                        <div class="font-extrabold text-emerald-600 mt-0.5">+{{ rtrim(rtrim(number_format($bonusPowerPoints, 2, '.', ''), '0'), '.') }} Poin</div>
                    </div>
                    <div>
                        <div class="text-[10px] text-slate-400 font-semibold">Penalti Risiko</div>
                        <div class="font-extrabold text-rose-600 mt-0.5">-{{ rtrim(rtrim(number_format($penaltyPowerPoints, 2, '.', ''), '0'), '.') }} Poin</div>
                    </div>
                    <div>
                        <div class="text-[10px] text-slate-400 font-semibold">Total Poin Gabungan</div>
                        <div class="font-black text-[#2563EB] mt-0.5">{{ number_format($skor, 0) }} / 100</div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Action Buttons --}}
        <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-3 relative z-10 no-print">
            <a 
                href="{{ route('siswa.kuis.show', $quiz->id) }}"
                class="w-full sm:w-auto px-6 py-3 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-extrabold text-xs sm:text-sm flex items-center justify-center gap-2 transition shadow-md shadow-blue-500/20"
            >
                <i class="fa-solid fa-map-location-dot text-xs"></i>
                <span>Buka Peta Petualangan</span>
            </a>

            <a 
                href="{{ route('siswa.kuis') }}"
                class="w-full sm:w-auto px-5 py-3 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs sm:text-sm flex items-center justify-center gap-2 transition"
            >
                <i class="fa-solid fa-compass text-blue-600 text-xs"></i>
                <span>Jelajahi Planet Lain</span>
            </a>

            <button 
                type="button" 
                onclick="window.print()" 
                class="w-full sm:w-auto px-5 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs sm:text-sm flex items-center justify-center gap-2 transition"
            >
                <i class="fa-solid fa-print text-xs text-slate-500"></i>
                <span>Cetak Lembar Nilai</span>
            </button>
        </div>
    </div>

    {{-- 4. REKAPITULASI HASIL PER TAHAP / LEVEL PETUALANGAN --}}
    <div class="bg-white rounded-3xl border border-slate-200/90 p-6 md:p-8 shadow-xs space-y-4 print-shadow-none">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 flex-wrap gap-2">
            <div>
                <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-route text-blue-600"></i>
                    <span>Rangkuman Performa Tiap Tahap Petualangan</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Hasil per level dari Arc Mudah hingga Sulit
                </p>
            </div>
            <span class="text-xs font-bold text-slate-500 bg-slate-50 px-3 py-1.5 rounded-full border border-slate-200/80">
                {{ count($levelsData) }} Level Selesai
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5 pt-1">
            @foreach($levelsData as $lvl)
                <div class="p-4 rounded-2xl bg-slate-50/70 border border-slate-200/80 hover:bg-white hover:border-blue-200 transition space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider"
                            style="background-color: {{ $lvl['badge_bg'] }}; color: {{ $lvl['badge_text'] }}; border: 1px solid {{ $lvl['badge_border'] }};">
                            Arc {{ $lvl['arc'] }}
                        </span>
                        {{-- Bintang Level --}}
                        <div class="flex items-center gap-0.5 text-amber-400 text-xs">
                            @for($s = 1; $s <= 3; $s++)
                                <i class="fa-solid fa-star {{ $s <= ($lvl['bintang'] ?? 0) ? 'text-amber-400' : 'text-slate-200' }}"></i>
                            @endfor
                        </div>
                    </div>

                    <div>
                        <div class="font-black text-slate-900 text-sm">
                            {{ $lvl['nama'] }}
                        </div>
                        <div class="text-xs text-slate-500 font-medium mt-0.5">
                            Skor Akurasi: <strong class="text-slate-800">{{ $lvl['skor_level'] }}%</strong> • {{ $lvl['correct_count'] }}/{{ $lvl['jumlah_soal'] }} Soal Benar
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between text-xs">
                        <span class="text-[11px] font-semibold text-emerald-600 flex items-center gap-1">
                            <i class="fa-solid fa-check text-[10px]"></i>
                            <span>{{ $lvl['bintang'] }} Bintang</span>
                        </span>
                        <a 
                            href="{{ route('siswa.kuis.hasil', ['quiz' => $quiz->id, 'arc' => $lvl['arc'], 'level' => $lvl['level_num']]) }}"
                            class="text-blue-600 hover:text-blue-800 font-bold text-[11px] flex items-center gap-1 no-print"
                        >
                            <span>Review</span>
                            <i class="fa-solid fa-arrow-right text-[9px]"></i>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- 5. DAFTAR LENGKAP SELURUH SOAL & GABUNGAN NILAI PER SOAL --}}
    <div class="bg-white rounded-3xl border border-slate-200/90 p-6 md:p-8 shadow-xs space-y-6 print-shadow-none">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <div>
                <h3 class="text-base sm:text-lg font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-blue-600"></i>
                    <span>Rincian Gabungan Seluruh Butir Soal Kuis</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Total {{ $totalSoal }} butir soal dengan bobot otomatis sistem dan perolehan poin masing-masing
                </p>
            </div>

            {{-- Filter Tabs (Semua, Benar, Salah, Power) --}}
            <div class="inline-flex items-center gap-1 p-1 bg-slate-100 rounded-2xl text-xs font-bold text-slate-600 self-start sm:self-auto no-print">
                <button 
                    type="button" 
                    @click="activeTab = 'semua'"
                    :class="activeTab === 'semua' ? 'bg-white text-blue-600 shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                    class="px-3 py-1.5 rounded-xl transition"
                >
                    Semua ({{ $totalSoal }})
                </button>
                <button 
                    type="button" 
                    @click="activeTab = 'benar'"
                    :class="activeTab === 'benar' ? 'bg-white text-emerald-600 shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                    class="px-3 py-1.5 rounded-xl transition"
                >
                    Benar ({{ $totalBenar }})
                </button>
                <button 
                    type="button" 
                    @click="activeTab = 'salah'"
                    :class="activeTab === 'salah' ? 'bg-white text-rose-600 shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                    class="px-3 py-1.5 rounded-xl transition"
                >
                    Salah ({{ $totalSalah }})
                </button>
                @if($usedPowersCount > 0)
                    <button 
                        type="button" 
                        @click="activeTab = 'power'"
                        :class="activeTab === 'power' ? 'bg-white text-amber-600 shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                        class="px-3 py-1.5 rounded-xl transition"
                    >
                        Power ({{ $usedPowersCount }})
                    </button>
                @endif
            </div>
        </div>

        {{-- Soal Cards List --}}
        <div class="space-y-4">
            @foreach($soals as $index => $soal)
                @php
                    $jawaban = $soal->menjawabs->first();
                    $isBenar = $jawaban && $jawaban->benar;
                    $powerUsed = $jawaban?->power;
                    $bobotSistem = (float) ($soal->bobot ?: 10);
                    $poinSoal = $jawaban
                        ? (($jawaban->power_id !== null || (float) $jawaban->poin_diperoleh != 0.0 || !$isBenar)
                            ? (float) $jawaban->poin_diperoleh
                            : $bobotSistem)
                        : 0.0;
                    $jawabanDipilihKey = strtolower(trim((string)($jawaban->jawaban_dipilih ?? '-')));
                    $kunciKey = is_array($soal->kunci_jawaban)
                        ? strtolower(trim((string)($soal->kunci_jawaban[0] ?? '')))
                        : strtolower(trim((string)$soal->kunci_jawaban));

                    $arcColor = match(ucfirst(strtolower(trim((string)$soal->kesulitan)))) {
                        'Sulit'  => 'bg-rose-50 text-rose-700 border-rose-200',
                        'Sedang' => 'bg-amber-50 text-amber-700 border-amber-200',
                        default  => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    };

                    $pilihanOptions = is_array($soal->pilihan) ? $soal->pilihan : [];
                @endphp

                <div 
                    x-show="filterQuestion({
                        is_benar: {{ $isBenar ? 'true' : 'false' }},
                        has_power: {{ $powerUsed ? 'true' : 'false' }}
                    })"
                    x-transition
                    class="rounded-2xl border {{ $isBenar ? 'border-slate-200 bg-white' : 'border-rose-200/80 bg-rose-50/20' }} p-4 sm:p-5 space-y-4 transition shadow-2xs"
                >
                    {{-- Header Soal: Nomor, Arc Kesulitan, Level, Bobot Sistem, Status --}}
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="w-7 h-7 rounded-lg font-black text-xs flex items-center justify-center shrink-0
                                {{ $isBenar ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                {{ $index + 1 }}
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border {{ $arcColor }}">
                                {{ $soal->kesulitan ?? 'Mudah' }} • {{ $soal->level ?? 'Level 1' }}
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 flex items-center gap-1"
                                title="Bobot otomatis ditentukan oleh sistem">
                                <i class="fa-solid fa-calculator text-[9px]"></i>
                                <span>Bobot: {{ rtrim(rtrim(number_format($bobotSistem, 2, '.', ''), '0'), '.') }} Poin</span>
                            </span>
                        </div>

                        {{-- Poin Diperoleh Badge --}}
                        <div class="flex items-center gap-2">
                            @if($powerUsed)
                                <span class="px-2 py-0.5 rounded-md bg-amber-50 border border-amber-200 text-amber-800 text-[10px] font-extrabold flex items-center gap-1">
                                    <i class="fa-solid fa-wand-magic-sparkles text-amber-500"></i>
                                    <span>{{ $powerUsed->nama }}</span>
                                </span>
                            @endif

                            <span class="px-3 py-1 rounded-xl text-xs font-black flex items-center gap-1
                                {{ $isBenar ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                <span>{{ $isBenar ? 'Benar' : 'Salah' }}</span>
                                <span>•</span>
                                <span>{{ ($poinSoal > 0 ? '+' : '') . rtrim(rtrim(number_format($poinSoal, 2, '.', ''), '0'), '.') }} Poin</span>
                            </span>
                        </div>
                    </div>

                    {{-- Teks Pertanyaan & Gambar --}}
                    <div class="space-y-3">
                        <p class="text-sm font-bold text-slate-800 leading-relaxed">
                            {{ $soal->pertanyaan }}
                        </p>

                        @if(!empty($soal->gambar))
                            <div class="max-w-md rounded-xl overflow-hidden border border-slate-200">
                                <img src="{{ asset('storage/' . $soal->gambar) }}" alt="Gambar Soal {{ $index + 1 }}" class="w-full h-auto object-cover max-h-56">
                            </div>
                        @endif
                    </div>

                    {{-- Pilihan Jawaban Grid --}}
                    @if(count($pilihanOptions) > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1 text-xs">
                            @foreach($pilihanOptions as $key => $teks)
                                @php
                                    $k = strtolower(trim((string)$key));
                                    $isDipilih = ($k === $jawabanDipilihKey);
                                    $isKunci = ($k === $kunciKey);

                                    $cardStyle = 'bg-slate-50 border-slate-200 text-slate-600';
                                    if ($isKunci) {
                                        $cardStyle = 'bg-emerald-50/80 border-emerald-300 text-emerald-900 font-bold';
                                    } elseif ($isDipilih && !$isBenar) {
                                        $cardStyle = 'bg-rose-50 border-rose-300 text-rose-900 font-bold';
                                    }
                                @endphp
                                <div class="p-2.5 rounded-xl border flex items-center justify-between gap-2 {{ $cardStyle }}">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="w-5 h-5 rounded-md font-bold text-[10px] flex items-center justify-center shrink-0 uppercase
                                            {{ $isKunci ? 'bg-emerald-600 text-white' : ($isDipilih && !$isBenar ? 'bg-rose-600 text-white' : 'bg-slate-200 text-slate-700') }}">
                                            {{ $k }}
                                        </span>
                                        <span class="truncate">{{ $teks }}</span>
                                    </div>
                                    @if($isKunci)
                                        <span class="text-[10px] text-emerald-700 font-extrabold shrink-0 flex items-center gap-1">
                                            <i class="fa-solid fa-check"></i> Kunci Benar
                                        </span>
                                    @elseif($isDipilih && !$isBenar)
                                        <span class="text-[10px] text-rose-600 font-extrabold shrink-0 flex items-center gap-1">
                                            <i class="fa-solid fa-xmark"></i> Jawabanmu
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        {{-- Essay / Tipe lain jika ada --}}
                        <div class="text-xs space-y-1 bg-slate-50 p-3 rounded-xl border border-slate-200">
                            <div>Jawabanmu: <strong class="{{ $isBenar ? 'text-emerald-700' : 'text-rose-700' }}">{{ $jawabanDipilihKey }}</strong></div>
                            <div>Kunci: <strong class="text-emerald-700">{{ $kunciKey }}</strong></div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

</div>
@endsection

