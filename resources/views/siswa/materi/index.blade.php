@extends('layouts.app')

@section('title', 'Materi Pelajaran')

@section('content')

<div class="w-full space-y-6 p-2">

    {{-- 1. Sub-Header Halaman --}}
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

    {{-- 2. Grid Card Mata Pelajaran --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 pt-2">
        @forelse ($daftarMapel as $mapel)
            @php
                // Ambil nama guru dari relasi
                $guruModel = $mapel->guruMapels->first()->guru ?? null;
                $namaGuru = $guruModel->nama ?? $guruModel->name ?? 'Guru Pengampu';
                
                // Ambil inisial nama guru
                $nameParts = explode(' ', trim(str_replace(['Dra.', 'Drs.', 'M.Pd', 'S.Pd', 'M.Kom', ','], '', $namaGuru)));
                $initial = strtoupper(substr($nameParts[0] ?? 'G', 0, 1));

                // Pilihan warna avatar
                $avatarColors = ['bg-red-500', 'bg-amber-500', 'bg-emerald-600', 'bg-blue-600', 'bg-indigo-600', 'bg-rose-600', 'bg-teal-600', 'bg-fuchsia-600', 'bg-slate-800'];
                $avatarColor = $avatarColors[$mapel->id % count($avatarColors)];

                // Hitung Bab & Materi secara Dinamis
                $jumlahBab = $mapel->babs_count ?? (isset($mapel->babs) ? count($mapel->babs) : 0);
                $jumlahMateri = $mapel->materis_count ?? (isset($mapel->materis) ? count($mapel->materis) : 0);
            @endphp

            {{-- Seluruh Kartu Dibuat Link--}}
            <a 
                href="{{ route('siswa.materi.show', $mapel->id) }}"
                style="box-shadow: -5px 5px 10px rgba(37, 99, 235, 0.5);" 
                class="group bg-white rounded-2xl border border-slate-100 transition-all duration-300 p-5 flex flex-col justify-between h-full space-y-5 hover:-translate-y-1 block"
            >
                
                <!-- Card Header -->
                <div class="flex items-center justify-between">
                    <!-- Icon Box: Abu-abu default, berubah jadi biru saat kartu di-hover / dipencet -->
                    <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-500 group-hover:bg-blue-600 group-hover:text-white transition-colors duration-200 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>

                    <!-- Jumlah Bab & Materi -->
                    <span class="px-3 py-1 bg-slate-100/80 text-slate-500 font-semibold text-[11px] rounded-full tracking-tight">
                        {{ $jumlahBab }} Bab • {{ $jumlahMateri }} Modul
                    </span>
                </div>

                <!-- Card Content -->
                <div class="space-y-2">
                    <h2 class="text-base font-bold text-slate-800 leading-snug line-clamp-1 group-hover:text-blue-600 transition-colors" title="{{ $mapel->nama_mapel }}">
                        {{ $mapel->nama_mapel }}
                    </h2>

                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full {{ $avatarColor }} text-white text-[11px] font-bold flex items-center justify-center shrink-0">
                            {{ $initial }}
                        </div>
                        <span class="text-xs font-medium text-slate-500 truncate">
                            {{ $namaGuru }}
                        </span>
                    </div>
                </div>

                <!-- Card Footer -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-[11px] text-slate-400 font-normal">
                        {{ $mapel->updated_at ? $mapel->updated_at->diffForHumans() : 'Masuk Pembelajaran' }}
                    </span>
                    <span class="text-xs font-bold text-blue-600 group-hover:text-blue-700 flex items-center gap-1 transition-colors">
                        Buka Materi
                        <svg class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </span>
                </div>

            </a>
        @empty
            <div class="col-span-full py-12 text-center bg-white rounded-2xl border border-dashed border-slate-200 text-slate-400">
                Belum ada data mata pelajaran yang tersedia.
            </div>
        @endforelse
    </div>

</div>
@endsection