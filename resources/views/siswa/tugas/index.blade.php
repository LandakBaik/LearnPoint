@extends('layouts.siswa')

@section('title', 'Daftar Tugas - LearnPoint')

@section('content')

{{-- Container Utama --}}
<div class="w-full">

    {{-- Navbar / Breadcrumb --}}
    <div class="flex flex-wrap items-center gap-3 text-sm font-medium mb-6">

        <a href="{{ route('siswa.beranda') }}"
            class="inline-flex items-center gap-2 px-4 py-2 bg-white text-slate-700 hover:bg-slate-100 rounded-full border border-slate-200 transition shadow-sm shrink-0">
            <i class="fa-solid fa-arrow-left text-xs"></i>
            Kembali ke Beranda
        </a>

        <span class="text-slate-300">/</span>

        <a href="{{ route('siswa.tugas') }}"
            class="text-slate-700 hover:text-sky-700 hover:underline font-semibold transition">
            Tugas
        </a>

    </div>

    {{-- Konten Tugas --}}
    <div class="w-full max-w-5xl mx-auto space-y-5">

        {{-- Header --}}
        <div class="flex items-center gap-4 pt-0">
            <div class="w-14 h-14 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-clipboard-list text-2xl text-blue-600"></i>
            </div>

            <div>
                <h2 class="text-lg font-extrabold text-slate-800 tracking-tight">
                    Tugas & PR
                </h2>

                <p class="text-sm text-slate-500">
                    Kerjakan dan kumpulkan tugas sesuai tenggat waktu yang ditentukan guru pengampu.
                </p>
            </div>
        </div>

        {{-- Daftar Tugas --}}
        <div id="tugas-terbaru" class="w-full space-y-2 mt-4">

            @forelse($daftarTugas as $tugas)

            @php
            $pengumpulan = $tugas->nilais->first();
            $sudahDikumpul = $pengumpulan !== null;

            $deadline = $tugas->deadline
            ? \Carbon\Carbon::parse($tugas->deadline)
            : null;

            $tenggatLewat = $deadline
            ? $deadline->isPast()
            : false;

            $statusTerlambat = !$sudahDikumpul && $tenggatLewat;

            $tenggatFormatted = $deadline
            ? $deadline->isoFormat('D MMM YYYY, HH:mm')
            : 'Tidak ada tenggat';

            $sisaWaktu = $deadline && !$tenggatLewat
            ? now()->diffInSeconds($deadline)
            : 0;

            $sisaWaktuFormatted = $sisaWaktu >= 86400
            ? 'Sisa ' . (int) floor($sisaWaktu / 86400) . ' hari lagi'
            : ($sisaWaktu > 0
            ? 'Sisa ' . (int) ceil($sisaWaktu / 3600) . ' jam lagi'
            : null);
            @endphp

            {{-- Card Tugas --}}
            <div class="w-full bg-white p-5 rounded-2xl border border-blue-200/80 shadow-xs hover:shadow-md transition flex flex-col md:flex-row md:items-center justify-between gap-4">

                {{-- Informasi Tugas --}}
                <div class="flex items-start gap-4 min-w-0 flex-1">

                    {{-- Ikon Dokumen --}}
                    <div class="w-16 h-16 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                        <i class="fa-regular fa-file-lines text-3xl"></i>
                    </div>

                    <div class="space-y-1 min-w-0 flex-1">

                        {{-- Mata Pelajaran --}}
                        <div>
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-600">
                                {{ $tugas->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? $tugas->pengampuKelas?->guruMapel?->mapel?->nama ?? 'Mata Pelajaran' }}
                            </span>
                        </div>

                        {{-- Judul Tugas --}}
                        <h3 class="text-base font-bold text-slate-800">
                            {{ $loop->iteration }}. {{ $tugas->judul }}
                        </h3>

                        {{-- Tenggat dan Sisa Waktu --}}
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm">

                            <span class="{{ $statusTerlambat ? 'text-red-500' : 'text-slate-400' }}">
                                <i class="fa-solid fa-calendar-days mr-1"></i>
                                Batas: {{ $tenggatFormatted }}
                            </span>

                            @if($sisaWaktuFormatted && !$sudahDikumpul)

                            <span class="text-slate-300">|</span>

                            <span class="font-semibold text-blue-600">
                                <i class="fa-regular fa-clock mr-1"></i>
                                {{ $sisaWaktuFormatted }}
                            </span>

                            @elseif($statusTerlambat)

                            <span class="font-semibold text-red-500">
                                <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                                Tenggat telah lewat
                            </span>

                            @elseif($sudahDikumpul)

                            <span class="font-semibold text-amber-600">
                                <i class="fa-regular fa-clock mr-1"></i>
                                Menunggu Penilaian
                            </span>

                            @endif

                        </div>

                        {{-- Deskripsi --}}
                        <p class="text-xs text-slate-500 max-w-2xl leading-relaxed">
                            {{ $tugas->deskripsi ?? 'Tidak ada deskripsi tugas.' }}
                        </p>

                    </div>
                </div>

                {{-- Tombol Aksi --}}
                <div class="shrink-0 self-end md:self-center">

                    <a href="{{ route('siswa.tugas.show', $tugas->id) }}"
                        class="px-5 py-2.5 text-xs font-semibold rounded-xl transition flex items-center justify-between gap-4
                            {{ $sudahDikumpul
                                ? 'bg-white text-blue-600 border border-blue-200 hover:bg-blue-50'
                                : 'bg-blue-600 text-white hover:bg-blue-700 shadow-md' }}">

                        @if($sudahDikumpul)
                        Lihat Jawaban
                        @else
                        Kerjakan
                        @endif

                        <i class="fa-solid fa-arrow-right"></i>

                    </a>

                </div>

            </div>

            @empty

            {{-- Tampilan Kosong --}}
            <div class="p-8 bg-white rounded-2xl border border-dashed border-slate-200 text-center text-slate-400">

                <i class="fa-regular fa-file-lines text-3xl text-blue-300 mb-2"></i>

                <p class="text-sm font-semibold text-slate-700">
                    Tidak Ada Tugas
                </p>

                <p class="text-xs text-slate-400 mt-0.5">
                    Belum ada tugas yang diberikan oleh guru.
                </p>

            </div>

            @endforelse

        </div>
    </div>
</div>

@endsection