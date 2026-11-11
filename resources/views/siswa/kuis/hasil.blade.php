@extends('layouts.siswa')

@section('title', 'Hasil Pengerjaan: ' . $quiz->judul . ' - LearnPoint')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 pb-12">

    {{-- Breadcrumb Navigation --}}
    <div class="flex items-center justify-between gap-3 flex-wrap text-xs font-semibold text-slate-400">
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
            <span class="text-slate-700 font-bold truncate max-w-xs">
                Hasil {{ ($currentLevel && empty($showAllSoals)) ? $currentLevel['full_nama'] : $quiz->judul }}
            </span>
        </div>

        <a 
            href="{{ route('siswa.kuis.show', $quiz->id) }}" 
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-xs shadow-2xs transition"
        >
            <i class="fa-solid fa-map-location-dot text-blue-600"></i>
            <span>Kembali ke Peta Petualangan</span>
        </a>
    </div>

    @php
        $skor = (float) ($skorDitampilkan ?? ($nilai->nilai ?? 0));
        $isLulus = $skor >= 70;
        $isIstimewa = $skor >= 85;

        $mapelName = $quiz->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? 'Mata Pelajaran';
        $isAllDone = $journey['is_all_completed'] ?? false;
    @endphp

    {{-- 1. Celebration Result Hero Card --}}
    <div class="relative overflow-hidden rounded-3xl bg-white border border-slate-200/90 shadow-xs p-6 md:p-10 text-center space-y-6">
        {{-- Background Gradient Pill Glow --}}
        <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-96 h-96 
            {{ $isIstimewa ? 'bg-amber-400/10' : ($isLulus ? 'bg-emerald-400/10' : 'bg-blue-400/10') }} rounded-full blur-3xl pointer-events-none">
        </div>

        {{-- Trophy / Result Icon --}}
        <div class="relative z-10">
            <div class="w-20 h-20 mx-auto rounded-3xl flex items-center justify-center text-3xl shadow-lg transition-transform hover:scale-105
                {{ $isIstimewa ? 'bg-gradient-to-tr from-amber-500 to-yellow-400 text-white shadow-amber-500/25' :
                   ($isLulus ? 'bg-gradient-to-tr from-emerald-600 to-teal-400 text-white shadow-emerald-500/25' :
                   'bg-gradient-to-tr from-blue-600 to-indigo-500 text-white shadow-blue-500/25') }}">
                @if($isAllDone)
                    <i class="fa-solid fa-trophy"></i>
                @elseif($isIstimewa)
                    <i class="fa-solid fa-medal"></i>
                @else
                    <i class="fa-solid fa-circle-check"></i>
                @endif
            </div>
        </div>

        {{-- Title & Headline --}}
        <div class="space-y-1.5 relative z-10 max-w-lg mx-auto">
            <div class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider mb-1
                {{ $isIstimewa ? 'bg-amber-50 text-amber-700 border border-amber-200' :
                   ($isLulus ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                   'bg-blue-50 text-blue-700 border border-blue-200') }}">
                <i class="fa-solid fa-flag-checkered text-[10px]"></i>
                @if($currentLevel && empty($showAllSoals))
                    <span>{{ $currentLevel['full_nama'] }} Selesai</span>
                @else
                    <span>Seluruh Kuis Selesai</span>
                @endif
            </div>

            <h1 class="text-2xl md:text-3xl font-black text-slate-800 tracking-tight">
                @if($isAllDone && !empty($showAllSoals))
                    Selamat! Seluruh Petualangan Kuis Tuntas!
                @elseif($isIstimewa)
                    Luar Biasa! Hasil Pengerjaan Sangat Bagus!
                @elseif($isLulus)
                    Bagus Sekali! Level Berhasil Diselesaikan!
                @else
                    Level Selesai! Terus Lanjutkan Perjalananmu!
                @endif
            </h1>

            <p class="text-xs md:text-sm text-slate-500">
                @if($currentLevel && empty($showAllSoals))
                    Kamu telah menyelesaikan <strong>{{ $currentLevel['full_nama'] }}</strong> pada kuis <strong>{{ $quiz->judul }}</strong> ({{ $mapelName }}).
                @else
                    Kamu telah menuntaskan seluruh Arc pada kuis <strong>{{ $quiz->judul }}</strong> ({{ $mapelName }}).
                @endif
            </p>
        </div>

        {{-- Score Highlight Banner --}}
        <div class="relative z-10 max-w-sm mx-auto p-5 rounded-3xl bg-slate-50 border border-slate-200/80 space-y-2">
            <span class="text-xs uppercase font-bold text-slate-400 tracking-wider">
                {{ ($currentLevel && empty($showAllSoals)) ? 'Skor Akurasi ' . $currentLevel['full_nama'] : 'Nilai Akhir Kuis' }}
            </span>
            <div class="text-5xl font-black tracking-tight mt-1
                {{ $isIstimewa ? 'text-amber-600' : ($isLulus ? 'text-emerald-600' : 'text-blue-600') }}">
                {{ number_format($skor, 0) }}
                <span class="text-lg font-bold text-slate-400">/ 100</span>
            </div>

            {{-- Bintang Penanda Nilai (Aturan: >67 -> 3 Bintang, 34-67 -> 2 Bintang, <=33 -> 1 Bintang) --}}
            @php
                $bintangFinal = $bintangDitampilkan ?? \App\Models\Quiz::hitungBintang($skor);
            @endphp
            <div class="pt-2 border-t border-slate-200/60">
                <div class="flex items-center justify-center gap-2">
                    @for($s = 1; $s <= 3; $s++)
                        <i class="fa-solid fa-star text-2xl {{ $s <= $bintangFinal ? 'text-amber-400 drop-shadow-xs' : 'text-slate-200' }}"></i>
                    @endfor
                </div>
                <div class="text-xs font-black text-amber-700 mt-1">
                    {{ $bintangFinal }} dari 3 Bintang
                </div>
                <div class="text-[10px] text-slate-400 font-medium">
                    (Nilai > 67: ⭐⭐⭐ • 34 - 67: ⭐⭐ • ≤ 33: ⭐)
                </div>
            </div>

            @if($currentLevel && empty($showAllSoals) && !$isAllDone)
                <div class="mt-2 pt-2 border-t border-slate-200/60 text-[11px] font-semibold text-blue-700 bg-blue-50/70 rounded-xl p-2 flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-circle-info text-blue-500"></i>
                    <span>Semua hasil soal akan dijumlahkan saat semua soal sudah dikerjakan.</span>
                </div>
            @endif

            <span class="text-[11px] text-slate-500 font-medium block pt-1">
                Progres Petualangan: <strong class="text-slate-700">{{ $journey['completed_levels'] ?? 1 }} dari {{ $journey['total_levels'] ?? 1 }} Level Selesai</strong>
            </span>
        </div>

        {{-- Statistics Grid --}}
        <div class="grid grid-cols-3 gap-3 max-w-lg mx-auto pt-1 relative z-10">
            <div class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-200/70 text-center">
                <div class="text-xs text-slate-400 font-medium">Jawaban Benar</div>
                <div class="text-xl font-black text-emerald-600 mt-0.5">{{ $totalBenar }} Soal</div>
            </div>

            <div class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-200/70 text-center">
                <div class="text-xs text-slate-400 font-medium">Jawaban Salah</div>
                <div class="text-xl font-black text-rose-500 mt-0.5">{{ $totalSalah }} Soal</div>
            </div>

            <div class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-200/70 text-center">
                <div class="text-xs text-slate-400 font-medium">Jumlah Soal</div>
                <div class="text-xl font-black text-slate-800 mt-0.5">{{ $totalSoal }} Butir</div>
            </div>
        </div>

        {{-- Ringkasan Efek Power dari Database (Tampil otomatis jika siswa menggunakan Power pada sesi ini) --}}
        @php
            $usedPowersCount = 0;
            $bonusPowerPoints = 0.0;
            $penaltyPowerPoints = 0.0;

            foreach ($soals as $sItem) {
                $jwb = $sItem->menjawabs->first();
                if ($jwb && $jwb->power_id && $jwb->power) {
                    $usedPowersCount++;
                    $bobotDasar = (float) ($sItem->bobot ?: 10);
                    $poinDidapat = (float) $jwb->poin_diperoleh;
                    if ($jwb->benar && $poinDidapat > $bobotDasar) {
                        $bonusPowerPoints += ($poinDidapat - $bobotDasar);
                    } elseif (!$jwb->benar && $poinDidapat < 0) {
                        $penaltyPowerPoints += abs($poinDidapat);
                    }
                }
            }
        @endphp
        @if($usedPowersCount > 0)
            <div class="relative z-10 max-w-xl mx-auto p-4 rounded-2xl bg-[#EFF5FF] border border-[#DCE9FE] text-left space-y-2.5">
                <div class="flex items-center justify-between gap-2 flex-wrap">
                    <span class="text-xs font-extrabold text-[#2563EB] flex items-center gap-1.5 uppercase tracking-wider">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                        <span>Ringkasan Efek Power</span>
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full bg-blue-600 text-white text-[10px] font-extrabold">
                        {{ $usedPowersCount }} Power Digunakan
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
                        <div class="text-[10px] text-slate-400 font-semibold">Skor Akhir</div>
                        <div class="font-black text-[#2563EB] mt-0.5">{{ number_format($skor, 0) }} / 100</div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Banner Notifikasi Level / Arc Berikutnya Terbuka Otomatis --}}
        @if(!empty($nextLevel))
            <div class="relative z-10 max-w-xl mx-auto p-4 rounded-2xl {{ !empty($isNewArcUnlocked) ? 'bg-amber-50 border border-amber-200 text-amber-900' : 'bg-[#EFF6FF] border border-[#BFDBFE] text-slate-800' }} flex flex-col sm:flex-row items-center justify-between gap-4 text-left">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-2xl {{ !empty($isNewArcUnlocked) ? 'bg-amber-500 text-white' : 'bg-[#2563EB] text-white' }} flex items-center justify-center text-lg shrink-0 shadow-xs">
                        <i class="fa-solid {{ !empty($isNewArcUnlocked) ? 'fa-bolt' : 'fa-unlock-keyhole' }}"></i>
                    </div>
                    <div>
                        <div class="text-[11px] font-extrabold uppercase tracking-wider {{ !empty($isNewArcUnlocked) ? 'text-amber-700' : 'text-[#2563EB]' }}">
                            @if(!empty($isNewArcUnlocked))
                                Arc Berikutnya Terbuka Otomatis!
                            @else
                                Level Berikutnya Terbuka!
                            @endif
                        </div>
                        <p class="text-xs sm:text-sm font-bold text-slate-800 mt-0.5">
                            @if(!empty($isNewArcUnlocked))
                                Seluruh level pada Arc {{ $currentLevel['arc'] }} selesai! Kini kamu memasuki <strong>{{ $nextLevel['full_nama'] }}</strong>.
                            @else
                                <strong>{{ $nextLevel['full_nama'] }}</strong> ({{ $nextLevel['jumlah_soal'] }} Soal) kini sudah terbuka dan siap dikerjakan.
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        @elseif($isAllDone)
            <div class="relative z-10 max-w-xl mx-auto p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 flex flex-col sm:flex-row items-center justify-between gap-4 text-left">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-2xl bg-emerald-600 text-white flex items-center justify-center text-lg shrink-0 shadow-xs">
                        <i class="fa-solid fa-crown"></i>
                    </div>
                    <div>
                        <div class="text-[11px] font-extrabold uppercase tracking-wider text-emerald-700">
                            Petualangan Kuis Selesai Sepenuhnya
                        </div>
                        <p class="text-xs sm:text-sm font-bold text-emerald-950 mt-0.5">
                            Seluruh level telah kamu tuntaskan! Nilai gabungan kuis kamu tercatat <strong>{{ number_format((float)($nilai->nilai ?? $skor), 0) }}/100</strong>.
                        </p>
                    </div>
                </div>
                <a href="{{ route('siswa.kuis.hasil-petualangan', $quiz->id) }}" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black shrink-0 transition shadow-xs flex items-center gap-1.5 self-stretch sm:self-auto justify-center">
                    <i class="fa-solid fa-trophy"></i>
                    <span>Hasil Petualangan</span>
                </a>
            </div>
        @endif

        {{-- Action Buttons --}}
        <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3 relative z-10">
            @if(!empty($nextLevel))
                <a 
                    href="{{ route('siswa.kuis.kerjakan', ['quiz' => $quiz->id, 'arc' => $nextLevel['arc'], 'level' => $nextLevel['level_num']]) }}"
                    class="w-full sm:w-auto px-6 py-3 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-extrabold text-xs sm:text-sm flex items-center justify-center gap-2 transition shadow-md shadow-blue-500/20"
                >
                    <span>Lanjut ke {{ $nextLevel['full_nama'] }}</span>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </a>
            @else
                <a 
                    href="{{ route('siswa.kuis.hasil-petualangan', $quiz->id) }}"
                    class="w-full sm:w-auto px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs sm:text-sm flex items-center justify-center gap-2 transition shadow-md shadow-emerald-500/20"
                >
                    <i class="fa-solid fa-award text-xs"></i>
                    <span>Buka Halaman Hasil Petualangan</span>
                </a>
            @endif

            <a 
                href="{{ route('siswa.kuis.show', $quiz->id) }}"
                class="w-full sm:w-auto px-6 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs sm:text-sm flex items-center justify-center gap-2 transition"
            >
                <i class="fa-solid fa-route text-xs"></i>
                <span>Lihat Peta Petualangan</span>
            </a>

            <a 
                href="{{ route('siswa.kuis') }}"
                class="w-full sm:w-auto px-5 py-3 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 font-bold text-xs sm:text-sm flex items-center justify-center gap-2 transition"
            >
                <i class="fa-solid fa-list-check text-xs"></i>
                <span>Daftar Kuis</span>
            </a>
        </div>
    </div>

    {{-- 2. Ringkasan Performa per Soal --}}
    <div class="bg-white rounded-3xl border border-slate-200/90 p-6 md:p-8 shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 flex-wrap gap-2">
            <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-chart-pie text-blue-600"></i>
                <span>
                    Ringkasan Jawaban {{ ($currentLevel && empty($showAllSoals)) ? '(' . $currentLevel['full_nama'] . ')' : '(Seluruh Level)' }}
                </span>
            </h3>
            <span class="text-xs text-slate-400">
                {{ $totalBenar }} Benar dari {{ $totalSoal }} Soal
            </span>
        </div>

        <div class="divide-y divide-slate-100">
            @foreach($soals as $index => $soal)
                @php
                    $jawaban = $soal->menjawabs->first();
                    $isBenar = $jawaban && $jawaban->benar;
                    $powerUsed = $jawaban?->power;
                    $bobotDasar = (float) ($soal->bobot ?: 10);
                    $poinSoal = $jawaban
                        ? (($jawaban->power_id !== null || (float) $jawaban->poin_diperoleh != 0.0 || !$isBenar)
                            ? (float) $jawaban->poin_diperoleh
                            : $bobotDasar)
                        : 0.0;
                    $poinFormatted = ($poinSoal > 0 ? '+' : '') . rtrim(rtrim(number_format($poinSoal, 2, '.', ''), '0'), '.') . ' Poin';
                @endphp
                <div class="py-3.5 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="w-7 h-7 rounded-lg font-bold text-xs flex items-center justify-center shrink-0
                            {{ $isBenar ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                            {{ $index + 1 }}
                        </span>
                        <div class="space-y-1">
                            <p class="text-xs font-semibold text-slate-800 line-clamp-2 max-w-md sm:max-w-xl">
                                {{ $soal->pertanyaan }}
                            </p>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-[10px] text-slate-400 capitalize">
                                    {{ $soal->kesulitan ?? 'Mudah' }} • {{ $soal->level ?? 'Level 1' }} • Bobot: {{ number_format($bobotDasar, 0) }} Poin
                                </span>
                                @if($powerUsed)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold border {{ $powerUsed->ui_classes['activePillClass'] ?? 'bg-blue-50 text-blue-700 border-blue-200' }}">
                                        <i class="{{ $powerUsed->icon }} text-[9px]"></i>
                                        <span>{{ $powerUsed->nama }}</span>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="shrink-0 flex items-center gap-2">
                        <span class="px-2.5 py-1 rounded-lg text-[11px] font-extrabold border
                            {{ $poinSoal > $bobotDasar ? 'bg-amber-50 text-amber-700 border-amber-200' : ($poinSoal < 0 ? 'bg-rose-50 text-rose-700 border-rose-200' : ($poinSoal > 0 ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-50 text-slate-500 border-slate-200')) }}">
                            {{ $poinFormatted }}
                        </span>
                        @if($isBenar)
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1.5">
                                <i class="fa-solid fa-check text-[11px]"></i>
                                <span>Benar</span>
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 flex items-center gap-1.5">
                                <i class="fa-solid fa-xmark text-[11px]"></i>
                                <span>Salah</span>
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
