@extends('layouts.siswa')

@section('title', 'Hasil Kuis: ' . $quiz->judul . ' - LearnPoint')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Breadcrumb Navigation --}}
    <div class="flex items-center gap-2 text-xs font-semibold text-slate-400">
        <a href="{{ route('siswa.dashboard') }}" class="hover:text-blue-600 transition flex items-center gap-1">
            <i class="fa-solid fa-house text-[11px]"></i>
            <span>Beranda</span>
        </a>
        <i class="fa-solid fa-chevron-right text-[9px] text-slate-300"></i>
        <a href="{{ route('siswa.kuis') }}" class="hover:text-blue-600 transition">
            Daftar Kuis
        </a>
        <i class="fa-solid fa-chevron-right text-[9px] text-slate-300"></i>
        <span class="text-slate-700 truncate max-w-xs">Hasil: {{ $quiz->judul }}</span>
    </div>

    @php
        $skor = (float)$nilai->nilai;
        $isLulus = $skor >= 70;
        $isIstimewa = $skor >= 85;

        $mapelName = $quiz->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? 'Mata Pelajaran';
        $guruName = $quiz->pengampuKelas?->guruMapel?->guru?->nama ?? 'Guru Pengampu';
    @endphp

    {{-- 1. Celebration Result Hero Card --}}
    <div class="relative overflow-hidden rounded-3xl bg-white border border-slate-200/90 shadow-sm p-6 md:p-10 text-center space-y-6">
        {{-- Background Gradient Pill Glow --}}
        <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-96 h-96 
            {{ $isIstimewa ? 'bg-amber-400/10' : ($isLulus ? 'bg-emerald-400/10' : 'bg-blue-400/10') }} rounded-full blur-3xl pointer-events-none">
        </div>

        {{-- Trophy / Result Icon --}}
        <div class="relative z-10">
            <div class="w-24 h-24 mx-auto rounded-3xl flex items-center justify-center text-4xl shadow-xl transition-transform hover:scale-105
                {{ $isIstimewa ? 'bg-gradient-to-tr from-amber-500 to-yellow-400 text-white shadow-amber-500/30' :
                   ($isLulus ? 'bg-gradient-to-tr from-emerald-600 to-teal-400 text-white shadow-emerald-500/30' :
                   'bg-gradient-to-tr from-blue-600 to-indigo-500 text-white shadow-blue-500/30') }}">
                @if($isIstimewa)
                    <i class="fa-solid fa-trophy animate-bounce"></i>
                @elseif($isLulus)
                    <i class="fa-solid fa-award"></i>
                @else
                    <i class="fa-solid fa-star"></i>
                @endif
            </div>
        </div>

        {{-- Title & Headline --}}
        <div class="space-y-1 relative z-10 max-w-lg mx-auto">
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider mb-2
                {{ $isIstimewa ? 'bg-amber-50 text-amber-700 border border-amber-200' :
                   ($isLulus ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                   'bg-blue-50 text-blue-700 border border-blue-200') }}">
                @if($isIstimewa)
                    <i class="fa-solid fa-crown text-[10px]"></i> Prestasi Istimewa
                @elseif($isLulus)
                    <i class="fa-solid fa-circle-check text-[10px]"></i> Hasil Memuaskan
                @else
                    <i class="fa-solid fa-lightbulb text-[10px]"></i> Selesai Dikerjakan
                @endif
            </div>

            <h1 class="text-2xl md:text-3xl font-black text-slate-800 tracking-tight">
                @if($isIstimewa)
                    Luar Biasa! Kerja Sangat Bagus!
                @elseif($isLulus)
                    Bagus Sekali! Pertahankan Prestasimu!
                @else
                    Tetap Semangat! Terus Asah Kemampuanmu!
                @endif
            </h1>

            <p class="text-xs md:text-sm text-slate-500">
                Kamu telah menyelesaikan kuis <strong>{{ $quiz->judul }}</strong> pada mata pelajaran <strong>{{ $mapelName }}</strong>.
            </p>
        </div>

        {{-- Score Highlight Banner --}}
        <div class="relative z-10 max-w-xs mx-auto p-5 rounded-2xl bg-slate-50 border border-slate-200/80">
            <span class="text-xs uppercase font-bold text-slate-400 tracking-wider">Nilai Akhir Kamu</span>
            <div class="text-5xl font-black tracking-tight mt-1
                {{ $isIstimewa ? 'text-amber-600' : ($isLulus ? 'text-emerald-600' : 'text-blue-600') }}">
                {{ number_format($skor, 0) }}
                <span class="text-lg font-bold text-slate-400">/ 100</span>
            </div>
            <span class="text-[11px] text-slate-500 font-medium block mt-1">
                Status: <strong class="capitalize text-slate-700">{{ $nilai->status ?? 'Selesai' }}</strong>
            </span>
        </div>

        {{-- Statistics Grid 4 Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 max-w-2xl mx-auto pt-2 relative z-10">
            <div class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-200/70 text-center">
                <div class="text-xs text-slate-400 font-medium">Benar</div>
                <div class="text-xl font-black text-emerald-600 mt-0.5">{{ $totalBenar }} Soal</div>
            </div>

            <div class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-200/70 text-center">
                <div class="text-xs text-slate-400 font-medium">Salah</div>
                <div class="text-xl font-black text-rose-500 mt-0.5">{{ $totalSalah }} Soal</div>
            </div>

            <div class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-200/70 text-center">
                <div class="text-xs text-slate-400 font-medium">Durasi Pengerjaan</div>
                <div class="text-xl font-black text-slate-800 mt-0.5 font-mono">{{ $nilai->durasi_formatted }}</div>
            </div>

            <div class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-200/70 text-center">
                <div class="text-xs text-slate-400 font-medium">Total Soal</div>
                <div class="text-xl font-black text-slate-800 mt-0.5">{{ $totalSoal }} Butir</div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-3 relative z-10">
            <a 
                href="{{ route('siswa.kuis') }}"
                class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs flex items-center justify-center gap-2 transition shadow-md shadow-blue-500/20"
            >
                <i class="fa-solid fa-list-check text-xs"></i>
                <span>Kembali ke Daftar Kuis</span>
            </a>

            <a 
                href="{{ route('siswa.dashboard') }}"
                class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs flex items-center justify-center gap-2 transition"
            >
                <i class="fa-solid fa-house text-xs"></i>
                <span>Ke Beranda Siswa</span>
            </a>
        </div>
    </div>

    {{-- 2. Ringkasan Performa per Soal --}}
    <div class="bg-white rounded-3xl border border-slate-200/90 p-6 md:p-8 shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-chart-pie text-blue-600"></i>
                <span>Ringkasan Jawaban Soal</span>
            </h3>
            <span class="text-xs text-slate-400">
                Diselesaikan pada: {{ $nilai->created_at ? $nilai->created_at->translatedFormat('d M Y, H:i') : '-' }} WIB
            </span>
        </div>

        <div class="divide-y divide-slate-100">
            @foreach($soals as $index => $soal)
                @php
                    $jawaban = $soal->menjawabs->first();
                    $isBenar = $jawaban && $jawaban->benar;
                @endphp
                <div class="py-3.5 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="w-7 h-7 rounded-lg font-bold text-xs flex items-center justify-center shrink-0
                            {{ $isBenar ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                            {{ $index + 1 }}
                        </span>
                        <div>
                            <p class="text-xs font-semibold text-slate-800 line-clamp-1 max-w-md sm:max-w-xl">
                                {{ $soal->pertanyaan }}
                            </p>
                            <span class="text-[10px] text-slate-400 capitalize">
                                {{ $soal->kesulitan ?? 'Sedang' }} • Bobot: {{ number_format($soal->bobot, 0) }} Poin
                            </span>
                        </div>
                    </div>

                    <div class="shrink-0">
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
