@extends('layouts.siswa')

@section('title', 'Materi Pelajaran - LearnPoint')

@section('content')

<div class="space-y-6">

    {{-- 1. Breadcrumb Navigation --}}
    <nav class="flex items-center gap-2 text-xs font-semibold text-slate-400">
        <a href="{{ route('siswa.dashboard') }}" class="hover:text-blue-600 transition flex items-center gap-1.5">
            <i class="fa-solid fa-house text-[11px]"></i>
            <span>Beranda</span>
        </a>
        <span class="text-[#2563EB] font-bold">›</span>
        <a href="{{ route('siswa.mapel') }}" class="text-[#2563EB] font-bold">
            Mata Pelajaran
        </a>
    </nav>

    @include('siswa.partials.unassigned-kelas-banner', ['kelas' => $kelas])

    {{-- 2. Sub-Header Halaman --}}
    <header class="space-y-1">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-800">
                Materi Pelajaran
            </h1>
            <span class="px-2.5 py-1 bg-blue-50 text-blue-600 font-bold text-xs rounded-full border border-blue-100">
                {{ $daftarMapel->count() }} Pelajaran
            </span>
        </div>
        <p class="text-sm text-slate-500 font-normal">
            Akses modul interaktif, rangkuman, dan capaian pembelajaran terstruktur.
        </p>
    </header>

    {{-- 3. Daftar Card Mata Pelajaran --}}
    <div class="space-y-4">
        @forelse ($daftarMapel as $mapel)
            @php
                $pengampuKelas = $mapel->pengampuKelases->first();
                $guruModel = $pengampuKelas?->guruMapel?->guru ?? $mapel->guruMapels?->first()?->guru;
                $namaGuru = $guruModel->nama ?? $guruModel->name ?? 'Guru Pengampu';

                // Ambil daftar materi unik untuk mata pelajaran ini pada kelas siswa secara riil
                $materiList = $mapel->pengampuKelases->flatMap->materis->unique('id_grub_materi');
                $jumlahMateri = $materiList->count();
                $jumlahBab = $jumlahMateri > 0 ? $jumlahMateri : 0;
                $latestMateri = $materiList->sortByDesc('created_at')->first();
            @endphp

            <!-- Card Materi -->
            <div class="bg-white p-5 rounded-2xl border border-blue-200/80 shadow-xs hover:shadow-md transition flex flex-col md:flex-row md:items-center justify-between gap-4">

                <div class="flex items-start gap-4">

                    <!-- Icon -->
                    <div class="p-3.5 rounded-xl bg-blue-50 text-blue-600 text-xl flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-book-open"></i>
                    </div>

                    <div class="space-y-1">

                        <div class="flex items-center gap-2 flex-wrap">

                            <!-- Nama Guru -->
                            <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold tracking-wide uppercase bg-blue-100 text-blue-800">
                                {{ $namaGuru }}
                            </span>

                            <!-- Jumlah Bab & Modul -->
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-600">
                                <i class="fa-regular fa-file-lines mr-1"></i>
                                {{ $jumlahBab }} Bab • {{ $jumlahMateri }} Modul
                            </span>

                            <!-- Waktu Diperbarui -->
                            <span class="text-xs text-slate-400">
                                <i class="fa-regular fa-clock mr-1"></i>
                                {{ $latestMateri?->created_at ? 'Diperbarui ' . $latestMateri->created_at->diffForHumans() : 'Belum ada modul' }}
                            </span>
                        </div>

                        <h3 class="text-base font-bold text-slate-800">
                            {{ $mapel->nama_mapel }}
                        </h3>

                        <p class="text-xs text-slate-500 max-w-2xl leading-relaxed">
                            {{ $latestMateri ? 'Materi terbaru: ' . $latestMateri->judul : 'Akses modul pembelajaran, rangkuman, dan bahan ajar mata pelajaran ini.' }}
                        </p>

                    </div>
                </div>

                <!-- Tombol Buka Materi -->
                <div class="shrink-0 self-end md:self-center">
                    <a href="{{ route('siswa.mapel.show', $mapel->id) }}"
                        class="px-5 py-2.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition flex items-center gap-2 shadow-md">
                        <i class="fa-solid fa-book-open-reader"></i>
                        Buka Materi
                    </a>
                </div>

            </div>
        @empty
            <!-- Empty State -->
            <div class="p-10 sm:p-12 bg-white rounded-2xl border border-dashed border-slate-200 text-center">

                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-blue-50 text-blue-500 flex items-center justify-center text-2xl">
                    <i class="fa-solid fa-book-open"></i>
                </div>

                <p class="text-base font-bold text-slate-800">
                    Belum Ada Mata Pelajaran
                </p>

                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto leading-relaxed">
                    Belum ada data mata pelajaran atau modul materi yang tersedia untuk kelas kamu saat ini.
                </p>

                <a href="{{ route('siswa.beranda') }}"
                    class="inline-flex items-center gap-2 mt-5 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl transition">
                    <i class="fa-solid fa-house"></i>
                    Kembali ke Beranda
                </a>

            </div>
        @endforelse
    </div>

</div>
@endsection
