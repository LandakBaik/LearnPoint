@extends('layouts.app')

@section('title', 'Daftar Tugas')

@section('content')
<div class="space-y-5">

    {{-- 1. Breadcrumb Navigation --}}
    <nav class="flex items-center gap-2 text-xs font-semibold text-slate-400">
        <a href="{{ route('siswa.dashboard') }}" class="hover:text-blue-600 transition flex items-center gap-1.5">
            <i class="fa-solid fa-house text-[11px]"></i>
            <span>Beranda</span>
        </a>
        <span class="text-[#2563EB] font-bold">›</span>
        <a href="{{ route('siswa.tugas') }}" class="text-[#2563EB] font-bold">
            Tugas
        </a>
    </nav>

    <!-- 3. Header Daftar Tugas -->
    <div id="tugas-terbaru" class="space-y-4">

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-slate-800 tracking-tight">
                    Timeline Tugas
                </h2>
                <p class="text-xs text-slate-500 mt-1">
                    Daftar tugas yang belum kamu kerjakan.
                </p>
            </div>

            <!-- Filter Deadline -->
            <form method="GET" action="{{ route('siswa.tugas') }}">
                <div class="relative">
                    <select
                        name="urutan"
                        onchange="this.form.submit()"
                        class="appearance-none w-full sm:w-auto min-w-[190px] pl-10 pr-10 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 shadow-sm outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 cursor-pointer">

                        <option value="terbaru" {{ request('urutan', 'terbaru') === 'terbaru' ? 'selected' : '' }}>
                            Deadline Terbaru
                        </option>

                        <option value="terlama" {{ request('urutan') === 'terlama' ? 'selected' : '' }}>
                            Deadline Terlama
                        </option>
                    </select>

                    <i class="fa-solid fa-arrow-down-wide-short absolute left-3.5 top-1/2 -translate-y-1/2 text-blue-600 text-sm pointer-events-none"></i>

                    <i class="fa-solid fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] pointer-events-none"></i>
                </div>
            </form>
        </div>

        <!-- 4. Daftar Item Tugas -->
        <div class="space-y-4">

            @forelse($tugasBelumSelesai as $tugas)

            @php
            $tenggatFormatted = $tugas->deadline
            ? \Carbon\Carbon::parse($tugas->deadline)->isoFormat('D MMM YYYY, HH:mm') . ' WIB'
            : 'Tidak ada tenggat';
            @endphp

            <!-- Card Tugas -->
            <div class="bg-white p-5 rounded-2xl border border-blue-200/80 shadow-xs hover:shadow-md transition flex flex-col md:flex-row md:items-center justify-between gap-4">

                <div class="flex items-start gap-4">

                    <!-- Icon -->
                    <div class="p-3.5 rounded-xl bg-blue-50 text-blue-600 text-xl flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-clipboard-list"></i>
                    </div>

                    <div class="space-y-1">

                        <div class="flex items-center gap-2 flex-wrap">

                            <!-- Nama Guru -->
                            <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold tracking-wide uppercase bg-blue-100 text-blue-800">
                                {{ $tugas->pengampuKelas?->guruMapel?->guru?->nama
                                        ?? $tugas->pengampuKelas?->guru?->nama
                                        ?? 'GURU PENGAMPU' }}
                            </span>

                            <!-- Status -->
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-600">
                                <i class="fa-regular fa-circle-dot mr-1"></i>
                                Belum Dikerjakan
                            </span>

                            <!-- Deadline -->
                            <span class="text-xs text-slate-400">
                                <i class="fa-regular fa-clock mr-1"></i>
                                Tenggat: {{ $tenggatFormatted }}
                            </span>
                        </div>

                        <h3 class="text-base font-bold text-slate-800">
                            {{ $tugas->judul }}
                        </h3>

                        <p class="text-xs text-slate-500 max-w-2xl leading-relaxed">
                            {{ $tugas->deskripsi ?? 'Tidak ada deskripsi tugas.' }}
                        </p>

                        @if($tugas->file_lampiran)
                        <a href="{{ route('file.tugas', $tugas->id) }}"
                            target="_blank"
                            class="inline-flex items-center gap-1.5 text-xs text-blue-600 font-semibold hover:underline pt-2">
                            <i class="fa-solid fa-paperclip"></i>
                            Lihat Lampiran Guru
                        </a>
                        @endif

                    </div>
                </div>

                <!-- Tombol Kerjakan -->
                <div class="shrink-0 self-end md:self-center">
                    <a href="{{ route('siswa.tugas.show', $tugas->id) }}"
                        class="px-5 py-2.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition flex items-center gap-2 shadow-md">
                        <i class="fa-solid fa-arrow-up-from-bracket"></i>
                        Kerjakan
                    </a>
                </div>

            </div>

            @empty

            <!-- Empty State -->
            <div class="p-10 sm:p-12 bg-white rounded-2xl border border-dashed border-slate-200 text-center">

                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-2xl">
                    <i class="fa-solid fa-circle-check"></i>
                </div>

                <p class="text-base font-bold text-slate-800">
                    Tidak Ada Tugas
                </p>

                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto leading-relaxed">
                    Semua tugas sudah kamu kerjakan. Belum ada tugas lain yang perlu dikumpulkan.
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

</div>
@endsection