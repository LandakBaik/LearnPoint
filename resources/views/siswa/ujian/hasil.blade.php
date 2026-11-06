@extends('layouts.siswa')

@section('title', 'Hasil Ujian - ' . ($ujian->judul ?? 'LearnPoint'))

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4 space-y-6">

    <!-- Card Ringkasan Nilai -->
    <div class="bg-white rounded-3xl p-8 border border-slate-200/80 shadow-sm text-center space-y-6">
        <div class="w-20 h-20 mx-auto rounded-3xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shadow-sm">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>

        <div>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-600 border border-indigo-100">
                {{ $ujian->pengampuKelas->guruMapel->mapel->nama_mapel ?? 'Mata Pelajaran' }}
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-2">{{ $ujian->judul }}</h1>
            <p class="text-sm text-slate-500 mt-1">Ujian telah berhasil diselesaikan dan dikoreksi otomatis.</p>
        </div>

        <!-- Nilai Akhir -->
        <div class="max-w-xs mx-auto py-6 px-8 rounded-3xl bg-slate-50 border border-slate-200/70">
            <span class="block text-xs font-bold uppercase tracking-wider text-slate-400">Nilai Akhir Kamu</span>
            <span class="text-5xl font-black text-indigo-600 mt-2 block">{{ $nilai->nilai }}</span>
            <span class="text-xs font-semibold text-slate-500 mt-1 block">KKM: {{ $ujian->kkm ?? 75 }}</span>
        </div>

        <!-- Statistik Jawaban -->
        <div class="grid grid-cols-3 gap-4 max-w-lg mx-auto pt-4">
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 text-center">
                <span class="block text-xs text-slate-400 font-medium">Total Soal</span>
                <span class="text-xl font-bold text-slate-800 mt-1 block">{{ $totalSoal }}</span>
            </div>
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-100 text-center">
                <span class="block text-xs text-emerald-600 font-medium">Benar</span>
                <span class="text-xl font-bold text-emerald-700 mt-1 block">{{ $totalBenar }}</span>
            </div>
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-100 text-center">
                <span class="block text-xs text-rose-600 font-medium">Salah</span>
                <span class="text-xl font-bold text-rose-700 mt-1 block">{{ $totalSalah }}</span>
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="pt-6 border-t border-slate-100">
            <a href="{{ route('siswa.materi') }}"
               class="inline-flex items-center gap-2 px-6 py-3 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-2xl transition shadow-xs">
                <span>&larr; Kembali ke Daftar Mata Pelajaran</span>
            </a>
        </div>
    </div>

</div>
@endsection
