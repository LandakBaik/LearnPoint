@extends('layouts.app')

@section('title', 'Ujian & Evaluasi')

@section('content')

    <div x-data="kelolaIndexUjian()" class="space-y-6">

        {{-- =========================================================
            NOTIFIKASI FLASH (Success / Error)
        ========================================================== --}}
        @if (session('success'))
            <div
                class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-sm font-semibold flex items-center justify-between shadow-xs">
                <span class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ session('success') }}
                </span>
            </div>
        @endif

        @if (session('error') || $errors->has('hapus'))
            <div
                class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-sm font-semibold flex items-center justify-between shadow-xs">
                <span class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    {{ session('error') ?? $errors->first('hapus') }}
                </span>
            </div>
        @endif

        {{-- =========================================================
            HEADER HALAMAN
        ========================================================== --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2.5">
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                        Ujian & Evaluasi Siswa
                    </h2>
                    <span
                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Aktif
                    </span>
                </div>
                <p class="mt-1 text-sm text-gray-500">
                    Kelola pelaksanaan tes, bank soal, dan analisis pencapaian belajar siswa.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <a href="{{ route('guru.ujian.laporan') }}"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 text-sm font-bold transition shadow-xs">
                    <span>📊</span>
                    <span>Laporan & Leger PDF</span>
                </a>

                <a href="{{ route('guru.ujian.create') }}"
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition-all duration-200 shadow-sm hover:shadow-md hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Ujian Baru
                </a>
            </div>
        </div>

        {{-- =========================================================
            HERO BANNER (CBT Sync Center)
        ========================================================== --}}
        <div
            class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-blue-600 via-indigo-600 to-indigo-700 p-6 sm:p-8 text-white shadow-lg">
            <div class="absolute -right-12 -top-12 w-64 h-64 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
            <div class="absolute right-40 -bottom-16 w-56 h-56 rounded-full bg-indigo-500/20 blur-xl pointer-events-none">
            </div>

            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="max-w-2xl space-y-3">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 backdrop-blur-md border border-white/20 text-xs font-medium text-blue-100">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        Live Sync CBT &bull; Terhubung ke {{ $daftarKelas->count() }} Kelas
                    </div>

                    <h3 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white flex items-center gap-2">
                        Pusat Ujian & Evaluasi Siswa
                        <span>📄</span>
                    </h3>

                    <p class="text-sm text-blue-100 leading-relaxed font-normal">
                        Kelola bank soal, jadwal pelaksanaan asesmen, dan pantau hasil evaluasi belajar siswa secara
                        terpusat dan terstruktur.
                    </p>
                </div>

                {{-- Status Ringkasan --}}
                <div
                    class="hidden md:flex flex-col items-start bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl p-4 min-w-[210px] text-white shadow-inner">
                    <div class="flex items-center gap-2 text-xs font-semibold tracking-wider text-blue-100 uppercase">
                        <svg class="w-4 h-4 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <span>Tingkat Kesiapan</span>
                    </div>
                    @php
                        $persenSiap =
                            $stats['total'] > 0
                                ? round((($stats['published'] + $stats['completed']) / $stats['total']) * 100, 1)
                                : 0;
                    @endphp
                    <div class="mt-2 text-3xl font-black tracking-tight">
                        {{ $persenSiap }}%
                    </div>
                    <p class="mt-0.5 text-xs text-blue-200">
                        Instrumen rilis & tuntas
                    </p>
                </div>
            </div>
        </div>


        {{-- =========================================================
            KARTU STATISTIK
        ========================================================== --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5">

            {{-- Total Ujian --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-xs hover:border-gray-200 transition">
                <div class="flex items-center justify-between">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a3 3 0 006 0M9 5h6" />
                        </svg>
                    </div>
                    <span class="text-[10px] font-semibold text-gray-400 bg-gray-50 px-2 py-0.5 rounded-md">Total</span>
                </div>
                <div class="mt-3">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Total Ujian</p>
                    <p id="stat-total" class="mt-1 text-2xl font-black text-gray-900">{{ $stats['total'] }}</p>
                    <p class="mt-1 text-[11px] text-gray-500 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Instrumen aktif
                    </p>
                </div>
            </div>

            {{-- Draft --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-xs hover:border-gray-200 transition">
                <div class="flex items-center justify-between">
                    <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <span
                        class="text-[10px] font-semibold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-md">Tindakan</span>
                </div>
                <div class="mt-3">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Draft</p>
                    <p id="stat-draft" class="mt-1 text-2xl font-black text-gray-900">{{ $stats['draft'] }}</p>
                    <p class="mt-1 text-[11px] text-gray-500 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Perlu selesai
                    </p>
                </div>
            </div>

            {{-- Terjadwal --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-xs hover:border-gray-200 transition">
                <div class="flex items-center justify-between">
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <span
                        class="text-[10px] font-semibold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md">Jadwal</span>
                </div>
                <div class="mt-3">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Terjadwal</p>
                    <p id="stat-scheduled" class="mt-1 text-2xl font-black text-gray-900">{{ $stats['scheduled'] }}</p>
                    <p class="mt-1 text-[11px] text-gray-500 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span> Akan datang
                    </p>
                </div>
            </div>

            {{-- Dipublikasi --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-xs hover:border-gray-200 transition">
                <div class="flex items-center justify-between">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <span
                        class="text-[10px] font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md">Rilis</span>
                </div>
                <div class="mt-3">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Dipublikasi</p>
                    <p id="stat-published" class="mt-1 text-2xl font-black text-gray-900">{{ $stats['published'] }}</p>
                    <p class="mt-1 text-[11px] text-gray-500 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Siap dikerjakan
                    </p>
                </div>
            </div>

            {{-- Selesai --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-xs hover:border-gray-200 transition">
                <div class="flex items-center justify-between">
                    <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M9 12l2 2 4-4m5 2a8 8 0 11-16 0 8 8 0 0116 0z" />
                        </svg>
                    </div>
                    <span
                        class="text-[10px] font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">Tuntas</span>
                </div>
                <div class="mt-3">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Selesai</p>
                    <p id="stat-completed" class="mt-1 text-2xl font-black text-gray-900">{{ $stats['completed'] }}</p>
                    <p class="mt-1 text-[11px] text-gray-500 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span> Sudah dievaluasi
                    </p>
                </div>
            </div>

            {{-- Tidak Dipublikasi --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-xs hover:border-gray-200 transition">
                <div class="flex items-center justify-between">
                    <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                    <span class="text-[10px] font-semibold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md">Jeda</span>
                </div>
                <div class="mt-3">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Nonaktif</p>
                    <p id="stat-unpublished" class="mt-1 text-2xl font-black text-gray-900">{{ $stats['unpublished'] }}
                    </p>
                    <p class="mt-1 text-[11px] text-gray-500 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span> Ditarik sementara
                    </p>
                </div>
            </div>

        </div>


        {{-- =========================================================
            BAR FILTER & PENCARIAN
        ========================================================== --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-xs">
            <form method="GET" action="{{ route('guru.ujian.index') }}" class="space-y-3">

                {{-- Input Pencarian Utama --}}
                <div class="relative">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m21 21-4.35-4.35m2.35-5.65a8 8 0 11-16 0 8 8 0 0116 0z" />
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari judul ujian, topik asesmen, atau mata pelajaran..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 text-sm text-gray-800 placeholder-gray-400 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                </div>

                {{-- Filter Pilihan & Tombol Submit --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_auto] gap-3">

                    {{-- Status Dropdown --}}
                    <div>
                        <select id="filter-status" name="status"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 text-xs font-medium text-gray-700 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition cursor-pointer">
                            <option value="">Semua Status</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draf</option>
                            <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Terjadwal
                            </option>
                            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>
                                Dipublikasi</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai
                            </option>
                            <option value="unpublished" {{ request('status') === 'unpublished' ? 'selected' : '' }}>Tidak
                                Dipublikasi</option>
                        </select>
                    </div>

                    {{-- Kelas Dropdown --}}
                    <div>
                        <select name="kelas_id"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 text-xs font-medium text-gray-700 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition cursor-pointer">
                            <option value="">Semua Kelas</option>
                            @foreach ($daftarKelas as $kelas)
                                <option value="{{ $kelas->id }}"
                                    {{ request('kelas_id') == $kelas->id ? 'selected' : '' }}>
                                    {{ $kelas->nama_kelas }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tombol Terapkan --}}
                    <div class="flex items-center gap-2">
                        <button type="submit"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-xs transition cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            Terapkan Filter
                        </button>

                        @if (request()->hasAny(['search', 'status', 'kelas_id']))
                            <a href="{{ route('guru.ujian.index') }}"
                                class="px-3 py-2.5 rounded-xl border border-gray-200 bg-white text-gray-500 hover:text-gray-700 hover:bg-gray-50 text-xs font-medium transition"
                                title="Reset Filter">
                                Reset
                            </a>
                        @endif
                    </div>

                </div>

            </form>
        </div>


        {{-- =========================================================
            DAFTAR UJIAN & FOLDER ARSIP (ALA WHATSAPP)
        ========================================================== --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">

            {{-- Card Header Dinamis (Daftar Utama vs Isi Folder Arsip) --}}
            <div
                class="px-6 py-4.5 border-b border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 bg-white">
                <div x-show="!bukaFolderArsip">
                    <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        Daftar Ujian & Asesmen Aktif
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                    </h3>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Instrumen ujian aktif. Ujian yang telah selesai dapat dipindahkan ke folder Diarsipkan agar tampilan tetap bersih.
                    </p>
                </div>

                <div x-show="bukaFolderArsip" x-cloak class="flex items-center gap-3">
                    <button type="button" @click="bukaFolderArsip = false"
                        class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition cursor-pointer"
                        title="Kembali ke Daftar Utama">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </button>
                    <div>
                        <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                            📁 Folder Ujian Diarsipkan
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Daftar ujian selesai yang disembunyikan dari halaman utama.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span x-show="!bukaFolderArsip"
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                        {{ $ujians->total() }} Ujian di Daftar Utama
                    </span>

                    <span x-show="bukaFolderArsip" x-cloak
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                        {{ isset($ujiansArsip) ? $ujiansArsip->count() : 0 }} Ujian Diarsipkan
                    </span>
                </div>
            </div>

            {{-- =========================================================
                BAR FOLDER DIARSIPKAN ALA WHATSAPP (TAMPIL DI ATAS DAFTAR)
            ========================================================== --}}
            @if (isset($ujiansArsip) && $ujiansArsip->count() > 0)
                <div x-show="!bukaFolderArsip"
                    @click="bukaFolderArsip = true"
                    class="px-6 py-3.5 bg-slate-50/90 hover:bg-slate-100/90 border-b border-gray-200/80 flex items-center justify-between cursor-pointer transition group select-none">
                    <div class="flex items-center gap-3.5">
                        <div class="w-9 h-9 rounded-xl bg-slate-200/80 group-hover:bg-indigo-100 text-slate-600 group-hover:text-indigo-600 flex items-center justify-center transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-800 group-hover:text-indigo-700 transition">
                                Diarsipkan
                            </p>
                            <p class="text-[11px] text-gray-500">
                                Klik untuk melihat ujian selesai yang telah diarsipkan
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-700">
                            {{ $ujiansArsip->count() }}
                        </span>
                        <svg class="w-4 h-4 text-gray-400 group-hover:translate-x-0.5 transition" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </div>
            @endif

            {{-- =========================================================
                VIEW 1: DAFTAR UJIAN UTAMA (TIDAK DIARSIPKAN)
            ========================================================== --}}
            <div x-show="!bukaFolderArsip" id="daftar-ujian" class="p-4 sm:p-6 space-y-4 bg-slate-50/50">
                @forelse($ujians as $ujian)
                    @php
                        $namaMapel = $ujian->kelasDalamGrup
                            ->map(fn($anggota) => $anggota->pengampuKelas?->guruMapel?->mapel?->nama_mapel)
                            ->filter()
                            ->unique()
                            ->values();

                        $namaKelas = $ujian->kelasDalamGrup
                            ->map(fn($anggota) => $anggota->pengampuKelas?->kelas?->nama_kelas)
                            ->filter()
                            ->unique()
                            ->values();

                        $mapelName = $namaMapel->first() ?? 'Umum';

                        $stripeColor = match ($ujian->status) {
                            'published' => 'bg-emerald-500',
                            'scheduled' => 'bg-amber-500',
                            'completed' => 'bg-slate-400',
                            'draft' => 'bg-amber-400',
                            'unpublished' => 'bg-rose-500',
                            default => 'bg-gray-300',
                        };

                        $badgeClass = match ($ujian->status) {
                            'published' => 'text-emerald-700 bg-emerald-50 border-emerald-200/80',
                            'scheduled' => 'text-amber-700 bg-amber-50 border-amber-200/80',
                            'completed' => 'text-slate-700 bg-slate-100 border-slate-200/80',
                            'draft' => 'text-gray-700 bg-gray-100 border-gray-200/80',
                            'unpublished' => 'text-rose-700 bg-rose-50 border-rose-200/80',
                            default => 'text-gray-700 bg-gray-100 border-gray-200/80',
                        };

                        $singkatanMapel = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $mapelName), 0, 3));
                        $kodeIdUjian =
                            'EX-' .
                            date('Y') .
                            '-' .
                            ($singkatanMapel ?: 'UJN') .
                            str_pad($ujian->id, 2, '0', STR_PAD_LEFT);

                        $totalPeserta = isset($ujian->kelasDalamGrup)
                            ? $ujian->kelasDalamGrup->sum('seleksi_ujians_count')
                            : $ujian->seleksi_ujians_count ?? 0;
                        $adaPengerjaan = $totalPeserta > 0;
                    @endphp

                    <div data-ujian-id="{{ $ujian->id }}" data-ujian-status="{{ $ujian->status }}"
                        class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm relative flex flex-col md:flex-row md:items-center justify-between gap-4 hover:shadow-md transition duration-150">

                        <div class="absolute left-0 top-4 bottom-4 w-1.5 rounded-r {{ $stripeColor }}"></div>

                        <div class="flex-1 pl-3 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500 mb-1.5">
                                <span class="text-blue-600 bg-blue-50 px-2 py-0.5 rounded font-medium">
                                    {{ $mapelName }}
                                </span>
                                <span>&bull;</span>
                                <span>Kelas: {{ $namaKelas->isNotEmpty() ? $namaKelas->implode(', ') : '-' }}</span>
                                <span>&bull;</span>
                                <span class="text-gray-400 font-mono">ID: {{ $kodeIdUjian }}</span>
                            </div>

                            <h3 class="text-base font-semibold text-gray-900 mb-2 truncate" title="{{ $ujian->judul }}">
                                {{ $ujian->judul }}
                            </h3>

                            <div class="flex flex-wrap items-center gap-4 text-xs text-gray-400">
                                <span class="flex items-center gap-1.5 {{ !$ujian->waktu_mulai ? 'italic' : '' }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    {{ $ujian->waktu_mulai ? $ujian->waktu_mulai->format('d M Y, H:i') . ' WIB' : 'Belum dijadwalkan' }}
                                </span>

                                <span class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ $ujian->durasi_menit }} menit
                                </span>

                                <span class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    {{ $ujian->soals->count() }} Soal
                                </span>
                            </div>
                        </div>

                        <div
                            class="flex flex-col items-start md:items-end justify-between self-stretch gap-3 shrink-0 pt-2 md:pt-0">

                            <div>
                                <span data-status-badge
                                    class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full border before:content-[''] before:inline-block before:w-1.5 before:h-1.5 before:rounded-full before:bg-current {{ $badgeClass }}">
                                    {{ $ujian->status_label }}
                                </span>
                            </div>

                            <div class="flex items-center justify-end gap-2 flex-wrap">

                                {{-- 1. STATUS: PUBLISHED (SEDANG BERJALAN) --}}
                                @if ($ujian->status === 'published')
                                    <a href="{{ route('guru.ujian.monitoring', $ujian->id) }}"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-xs">
                                        <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                                        Monitoring Live
                                    </a>

                                    <form action="{{ route('guru.ujian.unpublish', $ujian->id) }}" method="POST"
                                        onsubmit="return confirm('PENTING: Tarik publikasi ujian ini?\n\nSiswa yang belum selesai akan dikeluarkan dari lembar ujian. Nilai siswa yang sudah selesai tetap tersimpan aman.')"
                                        class="inline">
                                        @csrf
                                        <button type="submit"
                                            class="px-3.5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-xl text-xs font-bold transition cursor-pointer">
                                            Tarik Publikasi
                                        </button>
                                    </form>

                                    <a href="{{ route('guru.ujian.show', $ujian->id) }}"
                                        class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition">
                                        Detail
                                    </a>

                                {{-- 2. STATUS: COMPLETED (SELESAI NORMAL -> BISA DIARSIPKAN ALA WHATSAPP) --}}
                                @elseif($ujian->status === 'completed')
                                    <a href="{{ route('guru.ujian.show', $ujian->id) }}"
                                        class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition">
                                        Detail
                                    </a>

                                    <a href="{{ route('guru.ujian.rekap', $ujian->id) }}"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition shadow-xs">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path
                                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                            </path>
                                        </svg>
                                        Rekap Nilai
                                    </a>

                                    <form action="{{ route('guru.ujian.duplicate', $ujian->id) }}" method="POST"
                                        onsubmit="return confirm('Duplikasi instrumen soal ke draf ujian baru?')"
                                        class="inline">
                                        @csrf
                                        <button type="submit"
                                            class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition cursor-pointer">
                                            Duplikat
                                        </button>
                                    </form>

                                    {{-- Tombol Masukkan ke Folder Diarsipkan --}}
                                    <form action="{{ route('guru.ujian.toggle-archive', $ujian->id) }}" method="POST"
                                        class="inline">
                                        @csrf
                                        <button type="submit" title="Pindahkan ke Folder Diarsipkan"
                                            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-50 hover:bg-slate-200/80 text-slate-600 border border-slate-200 rounded-xl text-xs font-semibold transition cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                            </svg>
                                            <span>Arsipkan</span>
                                        </button>
                                    </form>

                                    <a href="{{ route('guru.ujian.edit', $ujian->id) }}"
                                      class="px-3.5 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 rounded-xl text-xs font-bold transition">
                                      Edit Bobot
                                  </a>

                                {{-- 3. STATUS: UNPUBLISHED (DITARIK / REM DARURAT) --}}
                                @elseif($ujian->status === 'unpublished')
                                    <form action="{{ route('guru.ujian.publish', $ujian->id) }}" method="POST"
                                        onsubmit="return confirm('Publikasikan kembali ujian ini agar dapat dikerjakan siswa?')"
                                        class="inline">
                                        @csrf
                                        <input type="hidden" name="tipe_publikasi" value="langsung">
                                        <button type="submit"
                                            class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">
                                            Publikasikan Lagi
                                        </button>
                                    </form>

                                    <a href="{{ route('guru.ujian.show', $ujian->id) }}"
                                        class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition">
                                        Detail
                                    </a>

                                    <a href="{{ route('guru.ujian.edit', $ujian->id) }}"
                                        class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 rounded-xl text-xs font-semibold transition">
                                        Edit bobot
                                    </a>

                                    @if ($adaPengerjaan)
                                        <a href="{{ route('guru.ujian.rekap', $ujian->id) }}"
                                            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 rounded-xl text-xs font-bold transition">
                                            <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <path
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                </path>
                                            </svg>
                                            Rekap Sesi ({{ $totalPeserta }})
                                        </a>
                                    @endif

                                {{-- 4. STATUS: SCHEDULED (TERJADWAL -> BISA DIEDIT SELAMA BELUM ADA PESERTA) --}}
                                @elseif($ujian->status === 'scheduled')
                                    @if (!$adaPengerjaan)
                                        <a href="{{ route('guru.ujian.edit', $ujian->id) }}"
                                            class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 rounded-xl text-xs font-semibold transition">
                                            Edit Soal
                                        </a>
                                    @endif

                                    <form action="{{ route('guru.ujian.unpublish', $ujian->id) }}" method="POST"
                                        onsubmit="return confirm('Batalkan jadwal ujian ini?')"
                                        class="inline">
                                        @csrf
                                        <button type="submit"
                                            class="px-3.5 py-2 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 rounded-xl text-xs font-bold transition cursor-pointer">
                                            Batalkan Jadwal
                                        </button>
                                    </form>

                                    <a href="{{ route('guru.ujian.show', $ujian->id) }}"
                                        class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition">
                                        Detail
                                    </a>

                                {{-- 5. STATUS: DRAFT --}}
                                @else
                                    <a href="{{ route('guru.ujian.show', $ujian->id) }}"
                                        class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition">
                                        Detail
                                    </a>

                                    <a href="{{ route('guru.ujian.edit', $ujian->id) }}"
                                        class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition">
                                        Edit Soal
                                    </a>

                                    <button type="button"
                                        @click="bukaModalPublikasi({{ $ujian->id }}, '{{ addslashes($ujian->judul) }}', '{{ route('guru.ujian.publish', $ujian) }}', '{{ $ujian->waktu_mulai ? \Carbon\Carbon::parse($ujian->waktu_mulai)->format('Y-m-d\TH:i') : '' }}', '{{ $ujian->deadline ? \Carbon\Carbon::parse($ujian->deadline)->format('Y-m-d\TH:i') : '' }}')"
                                        class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">
                                        Kelola Publikasi
                                    </button>
                                @endif

                                {{-- TOMBOL HAPUS / DESTROY (Muncul jika belum ada siswa yang mengerjakan) --}}
                                @if (!$adaPengerjaan && in_array($ujian->status, ['draft', 'scheduled', 'unpublished']))
                                    <form action="{{ route('guru.ujian.destroy', $ujian->id) }}" method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus ujian &quot;{{ addslashes($ujian->judul) }}&quot; beserta seluruh soalnya secara permanen?')"
                                        class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus Ujian"
                                            class="inline-flex items-center gap-1 px-3 py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 rounded-xl text-xs font-bold transition cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            <span>Hapus</span>
                                        </button>
                                    </form>
                                @endif
                            </div>

                        </div>

                    </div>
                @empty
                    {{-- Empty state daftar utama --}}
                    <div class="px-6 py-16 text-center bg-white rounded-2xl border border-gray-200">
                        <div
                            class="w-16 h-16 mx-auto rounded-2xl bg-blue-50 text-blue-500 flex items-center justify-center">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a3 3 0 006 0M9 5h6" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-base font-bold text-gray-900">Daftar ujian utama bersih</h3>
                        <p class="mt-1 text-sm text-gray-500 max-w-sm mx-auto">
                            Belum ada ujian aktif, atau seluruh ujian selesai telah dipindahkan ke folder Diarsipkan.
                        </p>
                        <a href="{{ route('guru.ujian.create') }}"
                            class="inline-flex items-center gap-2 mt-5 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold transition shadow-xs">
                            + Tambah Ujian
                        </a>
                    </div>
                @endforelse
            </div>

            {{-- =========================================================
                VIEW 2: ISI FOLDER DIARSIPKAN (ALA WHATSAPP ARCHIVE)
            ========================================================== --}}
            <div x-show="bukaFolderArsip" x-cloak class="p-4 sm:p-6 space-y-4 bg-slate-100/60">
                @if (isset($ujiansArsip) && $ujiansArsip->count() > 0)
                    @foreach ($ujiansArsip as $ujian)
                        @php
                            $namaMapel = $ujian->kelasDalamGrup
                                ->map(fn($anggota) => $anggota->pengampuKelas?->guruMapel?->mapel?->nama_mapel)
                                ->filter()
                                ->unique()
                                ->values();

                            $namaKelas = $ujian->kelasDalamGrup
                                ->map(fn($anggota) => $anggota->pengampuKelas?->kelas?->nama_kelas)
                                ->filter()
                                ->unique()
                                ->values();

                            $mapelName = $namaMapel->first() ?? 'Umum';
                            $singkatanMapel = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $mapelName), 0, 3));
                            $kodeIdUjian = 'EX-' . date('Y') . '-' . ($singkatanMapel ?: 'UJN') . str_pad($ujian->id, 2, '0', STR_PAD_LEFT);
                        @endphp

                        <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-xs relative flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="absolute left-0 top-4 bottom-4 w-1.5 rounded-r bg-slate-400"></div>

                            <div class="flex-1 pl-3 min-w-0">
                                <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500 mb-1.5">
                                    <span class="text-blue-600 bg-blue-50 px-2 py-0.5 rounded font-medium">
                                        {{ $mapelName }}
                                    </span>
                                    <span>&bull;</span>
                                    <span>Kelas: {{ $namaKelas->isNotEmpty() ? $namaKelas->implode(', ') : '-' }}</span>
                                    <span>&bull;</span>
                                    <span class="text-gray-400 font-mono">ID: {{ $kodeIdUjian }}</span>
                                </div>

                                <h3 class="text-base font-semibold text-gray-900 mb-2 truncate">
                                    {{ $ujian->judul }}
                                </h3>

                                <div class="flex flex-wrap items-center gap-4 text-xs text-gray-400">
                                    <span>{{ $ujian->waktu_mulai ? $ujian->waktu_mulai->format('d M Y, H:i') . ' WIB' : 'Selesai' }}</span>
                                    <span>&bull;</span>
                                    <span>{{ $ujian->durasi_menit }} menit</span>
                                    <span>&bull;</span>
                                    <span>{{ $ujian->soals->count() }} Soal</span>
                                </div>
                            </div>

                            <div class="flex flex-col items-start md:items-end justify-between gap-3 shrink-0">
                                <span class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full border text-slate-700 bg-slate-100 border-slate-200">
                                    📁 Diarsipkan ({{ $ujian->status_label }})
                                </span>

                                <div class="flex items-center justify-end gap-2 flex-wrap">
                                    <a href="{{ route('guru.ujian.show', $ujian->id) }}"
                                        class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition">
                                        Detail
                                    </a>

                                    <a href="{{ route('guru.ujian.rekap', $ujian->id) }}"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition shadow-xs">
                                        Rekap Nilai
                                    </a>

                                    <form action="{{ route('guru.ujian.duplicate', $ujian->id) }}" method="POST"
                                        onsubmit="return confirm('Duplikasi instrumen soal ke draf ujian baru?')"
                                        class="inline">
                                        @csrf
                                        <button type="submit"
                                            class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition cursor-pointer">
                                            Duplikat
                                        </button>
                                    </form>

                                    {{-- Tombol Keluarkan dari Folder Arsip --}}
                                    <form action="{{ route('guru.ujian.toggle-archive', $ujian->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 rounded-xl text-xs font-bold transition cursor-pointer">
                                            <span>📤 Keluarkan dari Arsip</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="px-6 py-14 text-center bg-white rounded-2xl border border-gray-200">
                        <p class="text-sm font-bold text-gray-700">Folder Arsip masih kosong</p>
                        <p class="text-xs text-gray-400 mt-1">Ujian berstatus Selesai yang Anda arsipkan akan tersimpan rapi di sini.</p>
                    </div>
                @endif
            </div>

            {{-- Pagination & Footer List (Hanya untuk Daftar Utama) --}}
            @if ($ujians->hasPages())
                <div x-show="!bukaFolderArsip"
                    class="px-6 py-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4 bg-white">
                    <p class="text-xs text-gray-500">
                        Menampilkan <span class="font-semibold text-gray-800">{{ $ujians->firstItem() ?? 0 }}</span>
                        sampai <span class="font-semibold text-gray-800">{{ $ujians->lastItem() ?? 0 }}</span> dari <span
                            class="font-semibold text-gray-800">{{ $ujians->total() }}</span> ujian aktif
                    </p>
                    <div class="text-xs">
                        {{ $ujians->links() }}
                    </div>
                </div>
            @endif

        </div>

        {{-- =========================================================
            MODAL KONFIRMASI PUBLIKASI / ATUR JADWAL (ALPINE.JS)
        ========================================================== --}}
        <div x-show="modalTerbuka" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-xs">
            <div @click.away="modalTerbuka = false"
                class="bg-white rounded-3xl p-6 sm:p-7 max-w-lg w-full border border-gray-200 shadow-2xl space-y-5">

                <div>
                    <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Penerbitan Ujian</span>
                    <h3 class="text-lg font-bold text-gray-900 mt-1" x-text="targetJudul"></h3>
                    <p class="text-xs text-gray-500 mt-1">Tentukan bagaimana siswa mengakses instrumen ujian ini.</p>
                </div>

                <form :action="targetActionUrl" method="POST" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-2 gap-3">
                        <label class="p-3.5 border rounded-2xl cursor-pointer transition text-center"
                            :class="tipePublikasi === 'langsung' ?
                                'border-emerald-500 bg-emerald-50/70 text-emerald-800 font-bold' :
                                'border-gray-200 hover:bg-gray-50 text-gray-600 font-medium'">
                            <input type="radio" name="tipe_publikasi" value="langsung" x-model="tipePublikasi"
                                class="hidden">
                            <span class="block text-sm">🚀 Terbitkan Sekarang</span>
                            <span class="block text-[11px] font-normal text-gray-400 mt-0.5">Langsung aktif (Live)</span>
                        </label>

                        <label class="p-3.5 border rounded-2xl cursor-pointer transition text-center"
                            :class="tipePublikasi === 'jadwal' ?
                                'border-indigo-500 bg-indigo-50/70 text-indigo-800 font-bold' :
                                'border-gray-200 hover:bg-gray-50 text-gray-600 font-medium'">
                            <input type="radio" name="tipe_publikasi" value="jadwal" x-model="tipePublikasi"
                                class="hidden">
                            <span class="block text-sm">⏱ Atur Jadwal</span>
                            <span class="block text-[11px] font-normal text-gray-400 mt-0.5">Tayang masa depan</span>
                        </label>
                    </div>

                    {{-- Form Input Jadwal (Muncul jika opsi 'jadwal' dipilih) --}}
                    <div x-show="tipePublikasi === 'jadwal'" class="space-y-3 pt-2">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">
                                Waktu Mulai Tayang <span class="text-red-500">*</span>
                            </label>
                            <input type="datetime-local" name="waktu_mulai" x-model="waktuMulai"
                                :required="tipePublikasi === 'jadwal'"
                                class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">
                                Batas Akhir / Deadline <span class="text-gray-400 font-normal">(Opsional)</span>
                            </label>
                            <input type="datetime-local" name="deadline" x-model="deadline"
                                class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-gray-100">
                        <button type="button" @click="modalTerbuka = false"
                            class="px-4 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-100 rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition shadow-xs">
                            Simpan & Publikasikan
                        </button>
                    </div>
                </form>

            </div>
        </div>

    </div>

    {{-- =========================================================
        LOGIKA JAVASCRIPT REALTIME, FILTER, & MODAL
    ========================================================== --}}
    <script>
        function kelolaIndexUjian() {
            return {
                bukaFolderArsip: false,
                modalTerbuka: false,
                targetId: null,
                targetJudul: '',
                targetActionUrl: '',
                tipePublikasi: 'langsung',
                waktuMulai: '',
                deadline: '',

                bukaModalPublikasi(id, judul, url, mulai, selesai) {
                    this.targetId = id;
                    this.targetJudul = judul;
                    this.targetActionUrl = url;
                    this.waktuMulai = mulai || '';
                    this.deadline = selesai || '';
                    this.tipePublikasi = this.waktuMulai ? 'jadwal' : 'langsung';
                    this.modalTerbuka = true;
                }
            };
        }

        document.addEventListener('DOMContentLoaded', function() {

            const statusUrl = @json(route('guru.ujian.status-realtime'));

            const statusStyles = {
                draft: 'bg-gray-100 text-gray-700 border-gray-200/80',
                scheduled: 'bg-amber-50 text-amber-700 border-amber-200/80',
                published: 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
                completed: 'bg-slate-100 text-slate-700 border-slate-200/80',
                unpublished: 'bg-rose-50 text-rose-700 border-rose-200/80',
            };

            const filterStatus = document.getElementById('filter-status');

            function terapkanFilterStatus() {
                const statusDipilih = filterStatus ? filterStatus.value : '';

                document.querySelectorAll('[data-ujian-id]').forEach(function(kartu) {
                    const statusKartu = kartu.dataset.ujianStatus;

                    kartu.style.display =
                        (!statusDipilih || statusKartu === statusDipilih) ?
                        '' :
                        'none';
                });
            }

            async function perbaruiStatusUjian() {
                try {
                    const response = await fetch(statusUrl, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                    });

                    if (!response.ok) {
                        throw new Error('Gagal mengambil status ujian.');
                    }

                    const data = await response.json();

                    if (data.stats) {
                        const setVal = (id, val) => {
                            const el = document.getElementById(id);
                            if (el && val !== undefined) el.textContent = val;
                        };
                        setVal('stat-total', data.stats.total);
                        setVal('stat-draft', data.stats.draft);
                        setVal('stat-scheduled', data.stats.scheduled);
                        setVal('stat-published', data.stats.published);
                        setVal('stat-completed', data.stats.completed);
                        setVal('stat-unpublished', data.stats.unpublished);
                    }

                    if (Array.isArray(data.ujians)) {
                        data.ujians.forEach(function(ujian) {
                            const kartu = document.querySelector(`[data-ujian-id="${ujian.id}"]`);
                            if (!kartu) return;

                            kartu.dataset.ujianStatus = ujian.status;

                            const badge = kartu.querySelector('[data-status-badge]');
                            if (badge) {
                                badge.textContent = ujian.status_label;
                                badge.className =
                                    'inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full border before:content-[\'\'] before:inline-block before:w-1.5 before:h-1.5 before:rounded-full before:bg-current ' +
                                    (statusStyles[ujian.status] || statusStyles.draft);
                            }
                        });
                    }

                    terapkanFilterStatus();

                } catch (error) {
                    console.error('Pembaruan status ujian realtime:', error);
                }
            }

            if (filterStatus) {
                filterStatus.addEventListener('change', terapkanFilterStatus);
            }

            terapkanFilterStatus();
            perbaruiStatusUjian();

            setInterval(perbaruiStatusUjian, 60000);

        });
    </script>

@endsection
