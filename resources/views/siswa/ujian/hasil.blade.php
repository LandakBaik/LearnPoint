@extends('layouts.siswa')

@section('title', 'Hasil Ujian - ' . ($ujian->judul ?? 'LearnPoint'))

@section('content')
<div class="max-w-2xl mx-auto py-10 px-4">
    <div class="bg-white rounded-3xl p-8 sm:p-10 border border-slate-200/80 shadow-sm text-center space-y-6">

        {{-- Ikon Sukses --}}
        <div class="w-20 h-20 mx-auto rounded-3xl bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center shadow-xs">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
        </div>

        @if (!($ujian->tampilkan_nilai ?? true))
            {{-- =========================================================
                MODE NILAI DISEMBUNYIKAN GURU
            ========================================================== --}}
            <div class="space-y-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Ujian Telah Terkirim
                </span>
                <h1 class="text-2xl font-black text-slate-900 mt-2">
                    {{ $ujian->judul }}
                </h1>
                <p class="text-sm text-slate-600 font-medium pt-2">
                    Ujian telah berhasil diselesaikan dan dikoreksi otomatis.
                </p>
            </div>

            <div class="pt-4">
                <a href="{{ route('siswa.dashboard') }}"
                    class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-extrabold transition shadow-sm">
                    <span>&larr; Kembali ke Beranda</span>
                </a>
            </div>
        @else
            {{-- =========================================================
                MODE NILAI DITAMPILKAN KE SISWA
            ========================================================== --}}
            @php
                $nilaiAngka = (float) ($nilai->nilai ?? $sesi->nilai_akhir ?? 0);
                $kkm = (float) ($ujian->kkm ?? 75);
                $lulus = $nilaiAngka >= $kkm;
                $adaEssay = $ujian->soals()->where('tipe_soal', 'essay')->exists();
            @endphp

            <div class="space-y-1.5">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-600 border border-indigo-100">
                    {{ $ujian->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? 'Mata Pelajaran' }}
                </span>
                <h1 class="text-2xl font-black text-slate-900">{{ $ujian->judul }}</h1>
                <p class="text-xs text-slate-500">
                    Dikumpulkan pada {{ $sesi->waktu_selesai ? \Carbon\Carbon::parse($sesi->waktu_selesai)->isoFormat('D MMM Y, HH:mm') . ' WIB' : now()->isoFormat('D MMM Y, HH:mm') . ' WIB' }}
                </p>
            </div>

            {{-- Kotak Skor Akhir --}}
            <div class="p-6 rounded-3xl border {{ $lulus ? 'bg-emerald-50/60 border-emerald-200' : 'bg-rose-50/60 border-rose-200' }}">
                <p class="text-xs font-extrabold uppercase tracking-wider {{ $lulus ? 'text-emerald-700' : 'text-rose-700' }}">
                    Nilai Akhir Anda
                </p>
                <div class="text-5xl font-black mt-2 {{ $lulus ? 'text-emerald-600' : 'text-rose-600' }}">
                    {{ number_format($nilaiAngka, 2) }}
                </div>
                <p class="text-xs font-bold mt-2 {{ $lulus ? 'text-emerald-700' : 'text-rose-700' }}">
                    {{ $lulus ? 'Selamat! Anda Melampaui KKM (' . number_format($kkm, 0) . ')' : 'Di Bawah KKM (' . number_format($kkm, 0) . ')' }}
                </p>
                @if ($adaEssay)
                    <p class="text-[11px] text-amber-700 bg-amber-50 border border-amber-200 rounded-xl px-3 py-1.5 mt-3 inline-block">
                        *Catatan: Nilai akhir dapat diperbarui setelah guru mengoreksi soal Essay.
                    </p>
                @endif
            </div>

            {{-- Statistik Benar / Salah --}}
            <div class="grid grid-cols-3 gap-3 text-center">
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
                    <p class="text-[11px] font-bold text-slate-400 uppercase">Total Soal</p>
                    <p class="text-lg font-black text-slate-800 mt-0.5">{{ $totalSoal }}</p>
                </div>
                <div class="p-3.5 rounded-2xl bg-emerald-50/70 border border-emerald-200">
                    <p class="text-[11px] font-bold text-emerald-600 uppercase">Benar</p>
                    <p class="text-lg font-black text-emerald-700 mt-0.5">{{ $totalBenar }}</p>
                </div>
                <div class="p-3.5 rounded-2xl bg-rose-50/70 border border-rose-200">
                    <p class="text-[11px] font-bold text-rose-600 uppercase">Salah / Kosong</p>
                    <p class="text-lg font-black text-rose-700 mt-0.5">{{ $totalSalah }}</p>
                </div>
            </div>

            <div class="pt-2">
                <a href="{{ route('siswa.dashboard') }}"
                    class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-extrabold transition shadow-sm">
                    <span>&larr; Kembali ke Beranda</span>
                </a>
            </div>
        @endif

    </div>
</div>
@endsection
