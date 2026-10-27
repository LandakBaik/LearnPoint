@extends('layouts.siswa')

@section('title', 'Daftar Tugas - LearnPoint')

@section('content')
<div class="space-y-5">

    {{-- 1. Header Daftar Tugas --}}
    <div class="flex items-center justify-between pt-0">
        <h2 class="text-lg font-extrabold text-slate-800 tracking-tight">
            Daftar Tugas
        </h2>
    </div>

    {{-- 2. Daftar Tugas --}}
    <div id="tugas-terbaru" class="space-y-2 mt-4">

        @forelse($daftarTugas as $tugas)
            @php
                $pengumpulan = $tugas->nilais->first();
                $sudahDikumpul = $pengumpulan !== null;

                $tenggatLewat = $tugas->deadline
                    ? \Carbon\Carbon::parse($tugas->deadline)->isPast()
                    : false;

                $statusTerlambat = !$sudahDikumpul && $tenggatLewat;

                $tenggatFormatted = $tugas->deadline
                    ? \Carbon\Carbon::parse($tugas->deadline)->isoFormat('D MMM YYYY, HH:mm') . ' WIB'
                    : 'Tidak ada tenggat';
            @endphp

            {{-- Card Tugas --}}
            <div class="bg-white p-5 rounded-2xl border border-blue-200/80 shadow-xs hover:shadow-md transition flex flex-col md:flex-row md:items-center justify-between gap-4">

                <div class="flex items-start gap-4">

                    {{-- Icon Buku --}}
                    <div class="p-3.5 rounded-xl bg-blue-50 text-blue-600 text-xl flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-book-open"></i>
                    </div>

                    <div class="space-y-1">
                        <div class="flex items-center gap-2 flex-wrap">

                            {{-- Nama Guru --}}
                            <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold tracking-wide uppercase bg-blue-100 text-blue-800">
                                {{ $tugas->pengampuKelas?->guruMapel?->guru?->nama ?? $tugas->pengampuKelas?->guru?->nama ?? 'GURU PENGAMPU' }}
                            </span>

                            {{-- Status Tugas --}}
                            @if($sudahDikumpul)
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-amber-100 text-amber-700">
                                    <i class="fa-solid fa-clock mr-1"></i>
                                    Menunggu Penilaian
                                </span>
                            @elseif($statusTerlambat)
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-red-100 text-red-700">
                                    <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                                    Melewati Tenggat
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-blue-100 text-blue-700">
                                    <i class="fa-solid fa-circle-info mr-1"></i>
                                    Belum Dikumpulkan
                                </span>
                            @endif

                            {{-- Tenggat --}}
                            <span class="text-xs {{ $statusTerlambat ? 'text-red-500' : 'text-slate-400' }}">
                                • Tenggat: {{ $tenggatFormatted }}
                            </span>
                        </div>

                        {{-- Judul Tugas --}}
                        <h3 class="text-base font-bold text-slate-800">
                            {{ $tugas->judul }}
                        </h3>

                        {{-- Deskripsi Tugas --}}
                        <p class="text-xs text-slate-500 max-w-2xl leading-relaxed">
                            {{ $tugas->deskripsi ?? 'Tidak ada deskripsi tugas.' }}
                        </p>
                    </div>
                </div>

                {{-- Tombol Aksi --}}
                <div class="shrink-0 self-end md:self-center">
                    <a href="{{ route('siswa.tugas.show', $tugas->id) }}"
                        class="px-5 py-2.5 text-xs font-semibold rounded-xl transition flex items-center gap-2
                        {{ $sudahDikumpul
                            ? 'bg-white text-blue-600 border border-blue-600 hover:bg-blue-50'
                            : 'bg-blue-600 text-white hover:bg-blue-700 shadow-md' }}">

                        @if($sudahDikumpul)
                            <i class="fa-solid fa-eye"></i>
                            Lihat Jawaban
                        @else
                            Kumpulkan Tugas
                        @endif
                    </a>
                </div>
            </div>

        @empty
            {{-- Tampilan Saat Tidak Ada Tugas --}}
            <div class="p-8 bg-white rounded-2xl border border-dashed border-slate-200 text-center text-slate-400">
                <i class="fa-solid fa-circle-check text-3xl text-emerald-400 mb-2"></i>

                <p class="text-sm font-semibold text-slate-700">
                    Tidak Ada Tugas Pending
                </p>

                <p class="text-xs text-slate-400 mt-0.5">
                    Semua tugas kamu sudah selesai dan dinilai oleh guru.
                </p>
            </div>
        @endforelse

    </div>
</div>
@endsection