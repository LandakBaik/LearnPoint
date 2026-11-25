@extends('layouts.app')

@section('title', 'Ujian & Evaluasi')

@section('content')

    @php
        $statusAktif = request('status', '');
    @endphp

    <div x-data="kelolaIndexUjian()" class="space-y-6 max-w-7xl mx-auto">

        

        {{-- =========================================================
            HEADER HALAMAN
        ========================================================== --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="space-y-1">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-tight">
                    Ujian &amp; Evaluasi Siswa
                </h1>
                <p class="text-sm text-slate-500 leading-relaxed font-normal">
                    Kelola pelaksanaan tes, bank soal, dan analisis pencapaian belajar siswa.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <a href="{{ route('guru.ujian.laporan') }}"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200/80 text-xs font-semibold transition-all duration-200 shadow-xs hover:shadow-sm">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="1.8"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Laporan &amp; Leger PDF</span>
                </a>

                <a href="{{ route('guru.ujian.create') }}"
                    class="inline-flex items-center justify-center gap-2 px-4 sm:px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold transition-all duration-200 shadow-xs hover:shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Ujian Baru</span>
                </a>
            </div>
        </div>

        {{-- =========================================================
            KARTU STATISTIK (6 KOLOM)
        ========================================================== --}}
        <section class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">

            {{-- Total Ujian --}}
            <a href="{{ route('guru.ujian.index', request()->except(['status', 'page'])) }}"
                class="group rounded-2xl p-4 sm:p-5 transition-all duration-200 block {{ $statusAktif === '' ? 'bg-blue-50/40 border-2 border-blue-600 shadow-sm' : 'bg-white border border-slate-200/80 hover:border-blue-200 shadow-sm hover:shadow-md' }}">
                <span
                    class="text-[11px] font-bold uppercase tracking-wider flex items-center gap-1.5 {{ $statusAktif === '' ? 'text-blue-700' : 'text-slate-500' }}">
                    @if ($statusAktif === '')
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                    @endif
                    Total Ujian
                </span>
                <div class="mt-2.5 flex items-center justify-between gap-2">
                    <div id="stat-total" class="text-2xl font-extrabold text-slate-900 leading-tight">{{ $stats['total'] }}
                    </div>
                    <div
                        class="w-9 h-9 rounded-xl bg-slate-100/90 text-slate-600 border border-slate-200/60 shadow-xs flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
                <p class="mt-2.5 pt-2 border-t border-slate-100 text-xs text-slate-400 truncate">Instrumen terdaftar</p>
            </a>

            {{-- Draft --}}
            <a href="{{ route('guru.ujian.index', array_merge(request()->except('page'), ['status' => 'draft'])) }}"
                class="group rounded-2xl p-4 sm:p-5 transition-all duration-200 block {{ $statusAktif === 'draft' ? 'bg-blue-50/40 border-2 border-blue-600 shadow-sm' : 'bg-white border border-slate-200/80 hover:border-blue-200 shadow-sm hover:shadow-md' }}">
                <span
                    class="text-[11px] font-bold uppercase tracking-wider flex items-center gap-1.5 {{ $statusAktif === 'draft' ? 'text-blue-700' : 'text-slate-500' }}">
                    @if ($statusAktif === 'draft')
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                    @endif
                    Draft
                </span>
                <div class="mt-2.5 flex items-center justify-between gap-2">
                    <div id="stat-draft" class="text-2xl font-extrabold text-slate-900 leading-tight">{{ $stats['draft'] }}
                    </div>
                    <div
                        class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 border border-blue-100/80 shadow-xs flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                </div>
                <p class="mt-2.5 pt-2 border-t border-slate-100 text-xs text-slate-400 truncate">Perlu dilengkapi</p>
            </a>

            {{-- Terjadwal --}}
            <a href="{{ route('guru.ujian.index', array_merge(request()->except('page'), ['status' => 'scheduled'])) }}"
                class="group rounded-2xl p-4 sm:p-5 transition-all duration-200 block {{ $statusAktif === 'scheduled' ? 'bg-amber-50/40 border-2 border-amber-500 shadow-sm' : 'bg-white border border-slate-200/80 hover:border-amber-200 shadow-sm hover:shadow-md' }}">
                <span
                    class="text-[11px] font-bold uppercase tracking-wider flex items-center gap-1.5 {{ $statusAktif === 'scheduled' ? 'text-amber-700' : 'text-slate-500' }}">
                    @if ($statusAktif === 'scheduled')
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    @endif
                    Terjadwal
                </span>
                <div class="mt-2.5 flex items-center justify-between gap-2">
                    <div id="stat-scheduled" class="text-2xl font-extrabold text-slate-900 leading-tight">
                        {{ $stats['scheduled'] }}</div>
                    <div
                        class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 border border-amber-200/60 shadow-xs flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="mt-2.5 pt-2 border-t border-slate-100 text-xs text-slate-400 truncate">Menunggu waktu</p>
            </a>

            {{-- Dipublikasi --}}
            <a href="{{ route('guru.ujian.index', array_merge(request()->except('page'), ['status' => 'published'])) }}"
                class="group rounded-2xl p-4 sm:p-5 transition-all duration-200 block {{ $statusAktif === 'published' ? 'bg-emerald-50/40 border-2 border-emerald-500 shadow-sm' : 'bg-white border border-slate-200/80 hover:border-emerald-200 shadow-sm hover:shadow-md' }}">
                <span
                    class="text-[11px] font-bold uppercase tracking-wider flex items-center gap-1.5 {{ $statusAktif === 'published' ? 'text-emerald-700' : 'text-slate-500' }}">
                    @if ($statusAktif === 'published')
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    @endif
                    Dipublikasi
                </span>
                <div class="mt-2.5 flex items-center justify-between gap-2">
                    <div id="stat-published" class="text-2xl font-extrabold text-slate-900 leading-tight">
                        {{ $stats['published'] }}</div>
                    <div
                        class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200/60 shadow-xs flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                        </svg>
                    </div>
                </div>
                <p class="mt-2.5 pt-2 border-t border-slate-100 text-xs text-slate-400 truncate">Sedang berlangsung</p>
            </a>

            {{-- Selesai --}}
            <a href="{{ route('guru.ujian.index', array_merge(request()->except('page'), ['status' => 'completed'])) }}"
                class="group rounded-2xl p-4 sm:p-5 transition-all duration-200 block {{ $statusAktif === 'completed' ? 'bg-slate-100/70 border-2 border-slate-500 shadow-sm' : 'bg-white border border-slate-200/80 hover:border-slate-300 shadow-sm hover:shadow-md' }}">
                <span
                    class="text-[11px] font-bold uppercase tracking-wider flex items-center gap-1.5 {{ $statusAktif === 'completed' ? 'text-slate-700' : 'text-slate-500' }}">
                    @if ($statusAktif === 'completed')
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-600"></span>
                    @endif
                    Selesai
                </span>
                <div class="mt-2.5 flex items-center justify-between gap-2">
                    <div id="stat-completed" class="text-2xl font-extrabold text-slate-900 leading-tight">
                        {{ $stats['completed'] }}</div>
                    <div
                        class="w-9 h-9 rounded-xl bg-slate-100 text-slate-600 border border-slate-200/60 shadow-xs flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="mt-2.5 pt-2 border-t border-slate-100 text-xs text-slate-400 truncate">Sudah dievaluasi</p>
            </a>

            {{-- Nonaktif / Tidak Dipublikasi --}}
            <a href="{{ route('guru.ujian.index', array_merge(request()->except('page'), ['status' => 'unpublished'])) }}"
                class="group rounded-2xl p-4 sm:p-5 transition-all duration-200 block {{ $statusAktif === 'unpublished' ? 'bg-rose-50/40 border-2 border-rose-500 shadow-sm' : 'bg-white border border-slate-200/80 hover:border-rose-200 shadow-sm hover:shadow-md' }}">
                <span
                    class="text-[11px] font-bold uppercase tracking-wider flex items-center gap-1.5 {{ $statusAktif === 'unpublished' ? 'text-rose-700' : 'text-slate-500' }}">
                    @if ($statusAktif === 'unpublished')
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                    @endif
                    Nonaktif
                </span>
                <div class="mt-2.5 flex items-center justify-between gap-2">
                    <div id="stat-unpublished" class="text-2xl font-extrabold text-slate-900 leading-tight">
                        {{ $stats['unpublished'] }}</div>
                    <div
                        class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 border border-rose-200/60 shadow-xs flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                    </div>
                </div>
                <p class="mt-2.5 pt-2 border-t border-slate-100 text-xs text-slate-400 truncate">Ditarik sementara</p>
            </a>

        </section>

        {{-- =========================================================
            KARTU UTAMA: FILTER, BAR ARSIP, TABEL UJIAN, PAGINATION
        ========================================================== --}}
        <div
            class="bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow overflow-hidden">

            {{-- BAR FILTER & PENCARIAN --}}
            <div class="px-6 py-4 border-b border-slate-100 bg-white">
                <form method="GET" action="{{ route('guru.ujian.index') }}"
                    class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">

                    <div class="flex flex-col sm:flex-row sm:items-center gap-2.5 flex-1">
                        {{-- Input Pencarian --}}
                        <div class="relative flex-1 max-w-md">
                            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"
                                fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m21 21-4.35-4.35m2.35-5.65a8 8 0 11-16 0 8 8 0 0116 0z" />
                            </svg>
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Cari judul ujian, topik, mapel..."
                                class="w-full pl-9 pr-3.5 py-2 rounded-xl border border-slate-200/80 bg-slate-50/60 text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                        </div>

                        {{-- Dropdown Status --}}
                        <select id="filter-status" name="status" onchange="this.form.submit()"
                            class="px-3.5 py-2 rounded-xl border border-slate-200/80 bg-white text-xs font-medium text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition cursor-pointer">
                            <option value="">Semua Status</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Terjadwal
                            </option>
                            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>
                                Dipublikasi</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai
                            </option>
                            <option value="unpublished" {{ request('status') === 'unpublished' ? 'selected' : '' }}>Tidak
                                Dipublikasi</option>
                        </select>

                        {{-- Dropdown Kelas --}}
                        <select name="kelas_id" onchange="this.form.submit()"
                            class="px-3.5 py-2 rounded-xl border border-slate-200/80 bg-white text-xs font-medium text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition cursor-pointer">
                            <option value="">Semua Kelas</option>
                            @foreach ($daftarKelas as $kelas)
                                <option value="{{ $kelas->id }}"
                                    {{ request('kelas_id') == $kelas->id ? 'selected' : '' }}>
                                    {{ $kelas->nama_kelas }}
                                </option>
                            @endforeach
                        </select>

                        <button type="submit"
                            class="sm:hidden px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold transition cursor-pointer shadow-xs">
                            Terapkan Filter
                        </button>

                        @if (request()->hasAny(['search', 'status', 'kelas_id']))
                            <a href="{{ route('guru.ujian.index') }}"
                                class="text-xs font-semibold text-slate-500 hover:text-blue-600 underline underline-offset-4 px-1.5 py-1 transition self-start sm:self-center">
                                Reset Filter
                            </a>
                        @endif
                    </div>

                    {{-- Info Total Kanan --}}
                    <div class="flex items-center justify-between sm:justify-end gap-2 shrink-0">
                        <span x-show="!bukaFolderArsip"
                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-slate-50/80 border border-slate-200/80 text-xs text-slate-500">
                            Total: <strong class="font-bold text-slate-800">{{ $ujians->total() }}</strong> ujian
                        </span>
                        <span x-show="bukaFolderArsip" x-cloak
                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-slate-100 border border-slate-200/80 text-xs text-slate-600">
                            Arsip: <strong
                                class="font-bold text-slate-900">{{ isset($ujiansArsip) ? $ujiansArsip->count() : 0 }}</strong>
                            ujian
                        </span>
                    </div>

                </form>
            </div>

            {{-- BAR FOLDER DIARSIPKAN (TAMPIL SAAT DI DAFTAR UTAMA) --}}
            @if (isset($ujiansArsip) && $ujiansArsip->count() > 0)
                <div x-show="!bukaFolderArsip" @click="bukaFolderArsip = true"
                    class="px-6 py-3.5 bg-slate-50/70 hover:bg-slate-100/70 border-b border-slate-200/60 flex items-center justify-between gap-3 cursor-pointer transition group select-none">
                    <div class="flex items-center gap-3 min-w-0">
                        <div
                            class="w-8 h-8 rounded-lg bg-white border border-slate-200/80 text-slate-600 group-hover:text-blue-600 group-hover:border-blue-200 flex items-center justify-center shrink-0 shadow-xs transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                            </svg>
                        </div>
                        <div class="flex flex-wrap items-baseline gap-x-2 gap-y-0.5 min-w-0">
                            <span class="text-xs sm:text-sm font-bold text-slate-800 group-hover:text-blue-600 transition">
                                Diarsipkan
                            </span>
                            <span class="text-xs text-slate-400 truncate">
                                Klik untuk melihat ujian selesai yang telah diarsipkan
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <span class="text-xs font-semibold text-slate-600 group-hover:text-blue-600 transition">
                            Lihat Arsip
                        </span>
                        <span
                            class="inline-flex items-center justify-center min-w-[22px] h-5 px-1.5 rounded-full text-[11px] font-bold bg-slate-200/80 text-slate-700">
                            {{ $ujiansArsip->count() }}
                        </span>
                        <svg class="w-4 h-4 text-slate-400 group-hover:text-blue-600 group-hover:translate-x-0.5 transition"
                            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </div>
            @endif

            {{-- HEADER SAAT MEMBUKA FOLDER ARSIP --}}
            <div x-show="bukaFolderArsip" x-cloak
                class="px-6 py-4 bg-slate-50/70 border-b border-slate-200/60 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <button type="button" @click="bukaFolderArsip = false"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white hover:bg-slate-100 border border-slate-200/80 text-slate-700 shadow-xs transition cursor-pointer"
                        title="Kembali ke Daftar Utama">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </button>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 leading-tight">Folder Ujian Diarsipkan</h3>
                        <p class="text-xs text-slate-400 font-normal mt-0.5">Daftar ujian selesai yang disembunyikan dari
                            halaman utama.</p>
                    </div>
                </div>
                <button type="button" @click="bukaFolderArsip = false"
                    class="text-xs font-semibold text-blue-600 hover:text-blue-700 bg-blue-50/80 px-3 py-1.5 rounded-lg border border-blue-100/60 transition-colors cursor-pointer">
                    Kembali ke Daftar Aktif
                </button>
            </div>

            {{-- =========================================================
                VIEW 1: TABEL DAFTAR UJIAN UTAMA
            ========================================================== --}}
            <div x-show="!bukaFolderArsip" id="daftar-ujian" class="overflow-x-auto">
                @if ($ujians->count() > 0)
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="bg-slate-50/70 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200/60">
                                <th class="py-3.5 pl-6 pr-4">Judul Ujian</th>
                                <th class="py-3.5 px-4 whitespace-nowrap">Status</th>
                                <th class="py-3.5 px-4 whitespace-nowrap">Deadline</th>
                                <th class="py-3.5 px-4">Kelas</th>
                                <th class="py-3.5 pl-4 pr-6 text-right whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            @foreach ($ujians as $ujian)
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
                                    $jumlahSoal = $ujian->soals->count();
                                    $kelasLabelRingkas = $namaKelas->isNotEmpty()
                                        ? 'Kelas ' . $namaKelas->implode(', ')
                                        : '-';

                                    $badgeClass = match ($ujian->status) {
                                        'published' => 'bg-emerald-50 text-emerald-700 border-emerald-200/70',
                                        'scheduled' => 'bg-amber-50 text-amber-700 border-amber-200/70',
                                        'completed' => 'bg-slate-100 text-slate-600 border-slate-200',
                                        'draft' => 'bg-blue-50 text-blue-600 border-blue-200/70',
                                        'unpublished' => 'bg-rose-50 text-rose-600 border-rose-200/70',
                                        default => 'bg-slate-100 text-slate-600 border-slate-200',
                                    };

                                    $totalPeserta = isset($ujian->kelasDalamGrup)
                                        ? $ujian->kelasDalamGrup->sum('seleksi_ujians_count')
                                        : $ujian->seleksi_ujians_count ?? 0;
                                    $adaPengerjaan = $totalPeserta > 0;

                                    $judulAman = str_replace(["\r", "\n"], ' ', addslashes($ujian->judul));
                                    $kelasAman = str_replace(["\r", "\n"], ' ', addslashes($kelasLabelRingkas));
                                    $jadwalMulaiTeks = $ujian->waktu_mulai
                                        ? $ujian->waktu_mulai->format('d M Y, H:i') . ' WIB'
                                        : 'Belum dijadwalkan';
                                @endphp

                                <tr data-ujian-id="{{ $ujian->id }}" data-ujian-status="{{ $ujian->status }}"
                                    class="hover:bg-slate-50/60 transition-colors">

                                    {{-- KOLOM 1: JUDUL UJIAN --}}
                                    <td class="py-4 pl-6 pr-4 min-w-[230px]">
                                        <div class="font-bold text-slate-900 text-xs sm:text-[13px] leading-snug"
                                            title="{{ $ujian->judul }}">
                                            {{ $ujian->judul }}
                                        </div>
                                        <div
                                            class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[11px] text-slate-400 font-normal">
                                            <span class="{{ !$ujian->waktu_mulai ? 'italic' : '' }}">
                                                Mulai: {{ $jadwalMulaiTeks }}
                                            </span>
                                            <span class="text-slate-300">&bull;</span>
                                            <span>{{ $jumlahSoal }} Soal</span>
                                            <span class="text-slate-300">&bull;</span>
                                            <span>{{ $ujian->durasi_menit }} mnt</span>
                                        </div>
                                    </td>

                                    {{-- KOLOM 2: STATUS --}}
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        <span data-status-badge
                                            class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-2.5 py-1 rounded-full border shadow-2xs before:content-[''] before:inline-block before:w-1.5 before:h-1.5 before:rounded-full before:bg-current {{ $badgeClass }}">
                                            {{ $ujian->status_label }}
                                        </span>
                                    </td>

                                    {{-- KOLOM 3: DEADLINE --}}
                                    <td class="py-4 px-4 whitespace-nowrap text-xs">
                                        @if ($ujian->deadline)
                                            <span class="font-medium text-slate-700">
                                                {{ $ujian->deadline->format('d M Y, H:i') }} WIB
                                            </span>
                                        @else
                                            <span class="text-slate-400 italic">Belum diatur</span>
                                        @endif
                                    </td>

                                    {{-- KOLOM 4: KELAS --}}
                                    <td class="py-4 px-4 min-w-[140px]">
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            @forelse ($namaKelas as $kls)
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-600 border border-blue-100/80 whitespace-nowrap">
                                                    {{ $namaKelas->count() > 2 ? $kls : 'Kelas ' . $kls }}
                                                </span>
                                            @empty
                                                <span class="text-xs text-slate-400">-</span>
                                            @endforelse
                                        </div>
                                    </td>

                                    {{-- KOLOM 5: AKSI --}}
                                    <td class="py-4 pl-4 pr-6 whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">

                                            {{-- 1. STATUS: PUBLISHED --}}
                                            @if ($ujian->status === 'published')
                                                <a href="{{ route('guru.ujian.monitoring', $ujian->id) }}"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200/80 text-xs font-semibold transition-colors shadow-2xs">
                                                    <span
                                                        class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                    Monitoring Live
                                                </a>

                                                <button type="button"
                                                    @click="bukaModalKonfirmasi({
                                                        jenis: 'tarik',
                                                        judul: '{{ $judulAman }}',
                                                        soal: {{ $jumlahSoal }},
                                                        kelas: '{{ $kelasAman }}',
                                                        url: '{{ route('guru.ujian.unpublish', $ujian->id) }}',
                                                        method: 'POST'
                                                    })"
                                                    class="px-3 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200/80 text-xs font-semibold transition-colors shadow-2xs cursor-pointer">
                                                    Tarik Publikasi
                                                </button>

                                                <a href="{{ route('guru.ujian.show', $ujian->id) }}" title="Lihat Detail"
                                                    class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        stroke-width="1.8" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </a>

                                            {{-- 2. STATUS: COMPLETED --}}
                                            @elseif ($ujian->status === 'completed')
                                                <a href="{{ route('guru.ujian.rekap', $ujian->id) }}"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200/80 text-slate-700 border border-slate-200/80 text-xs font-semibold transition-colors shadow-2xs">
                                                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none"
                                                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                                    </svg>
                                                    Rekap Nilai
                                                </a>

                                                <a href="{{ route('guru.ujian.edit', $ujian->id) }}"
                                                    class="px-3 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-600 border border-blue-200/70 text-xs font-semibold transition-colors shadow-2xs">
                                                    Ubah Bobot
                                                </a>

                                                <a href="{{ route('guru.ujian.show', $ujian->id) }}" title="Lihat Detail"
                                                    class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        stroke-width="1.8" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </a>

                                                <a href="{{ route('guru.ujian.monitoring', $ujian->id) }}"
                                                    title="Hasil Monitoring & Pelanggaran"
                                                    class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        stroke-width="1.8" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                    </svg>
                                                </a>

                                                <button type="button" title="Duplikasi ke Draf"
                                                    @click="bukaModalKonfirmasi({
                                                        jenis: 'duplikat',
                                                        judul: '{{ $judulAman }}',
                                                        soal: {{ $jumlahSoal }},
                                                        kelas: '{{ $kelasAman }}',
                                                        url: '{{ route('guru.ujian.duplicate', $ujian->id) }}',
                                                        method: 'POST'
                                                    })"
                                                    class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors cursor-pointer">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        stroke-width="1.8" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                    </svg>
                                                </button>

                                                <button type="button" title="Arsipkan Ujian"
                                                    @click="bukaModalKonfirmasi({
                                                        jenis: 'arsipkan',
                                                        judul: '{{ $judulAman }}',
                                                        soal: {{ $jumlahSoal }},
                                                        kelas: '{{ $kelasAman }}',
                                                        url: '{{ route('guru.ujian.toggle-archive', $ujian->id) }}',
                                                        method: 'POST'
                                                    })"
                                                    class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        stroke-width="1.8" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                                    </svg>
                                                </button>

                                            {{-- 3. STATUS: UNPUBLISHED --}}
                                            @elseif ($ujian->status === 'unpublished')
                                                <button type="button"
                                                    @click="bukaModalKonfirmasi({
                                                        jenis: 'publikasi_ulang',
                                                        judul: '{{ $judulAman }}',
                                                        soal: {{ $jumlahSoal }},
                                                        kelas: '{{ $kelasAman }}',
                                                        url: '{{ route('guru.ujian.publish', $ujian->id) }}',
                                                        method: 'POST',
                                                        tipePublikasiHidden: 'langsung'
                                                    })"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200/80 text-xs font-semibold transition-colors shadow-2xs cursor-pointer">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                        stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                    </svg>
                                                    Publikasi Lagi
                                                </button>

                                                <a href="{{ route('guru.ujian.edit', $ujian->id) }}"
                                                    class="px-3 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-600 border border-blue-200/70 text-xs font-semibold transition-colors shadow-2xs">
                                                    Edit Bobot
                                                </a>

                                                <a href="{{ route('guru.ujian.show', $ujian->id) }}" title="Lihat Detail"
                                                    class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        stroke-width="1.8" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </a>

                                                @if ($adaPengerjaan)
                                                    <a href="{{ route('guru.ujian.rekap', $ujian->id) }}"
                                                        title="Rekap Sesi ({{ $totalPeserta }} peserta)"
                                                        class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition-colors">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            stroke-width="1.8" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                                        </svg>
                                                    </a>
                                                @endif

                                            {{-- 4. STATUS: SCHEDULED --}}
                                            @elseif ($ujian->status === 'scheduled')
                                                <button type="button"
                                                    @click="bukaModalKonfirmasi({
                                                        jenis: 'batalkan_jadwal',
                                                        judul: '{{ $judulAman }}',
                                                        soal: {{ $jumlahSoal }},
                                                        kelas: '{{ $kelasAman }}',
                                                        jadwal: '{{ $jadwalMulaiTeks }}',
                                                        url: '{{ route('guru.ujian.unpublish', $ujian->id) }}',
                                                        method: 'POST'
                                                    })"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200/80 text-xs font-semibold transition-colors shadow-2xs cursor-pointer">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                        stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                    Batalkan Jadwal
                                                </button>

                                                @if (!$adaPengerjaan)
                                                    <a href="{{ route('guru.ujian.edit', $ujian->id) }}"
                                                        class="px-3 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-600 border border-blue-200/70 text-xs font-semibold transition-colors shadow-2xs">
                                                        Edit Soal
                                                    </a>
                                                @endif

                                                <a href="{{ route('guru.ujian.show', $ujian->id) }}" title="Lihat Detail"
                                                    class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        stroke-width="1.8" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </a>

                                            {{-- 5. STATUS: DRAFT --}}
                                            @else
                                                <a href="{{ route('guru.ujian.edit', $ujian->id) }}"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-600 border border-blue-200/70 text-xs font-semibold transition-colors shadow-2xs">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                        stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                    </svg>
                                                    Edit Soal
                                                </a>

                                                <button type="button"
                                                    @click="bukaModalPublikasi({
                                                        id: {{ $ujian->id }},
                                                        judul: '{{ $judulAman }}',
                                                        soal: {{ $jumlahSoal }},
                                                        kelas: '{{ $kelasAman }}',
                                                        url: '{{ route('guru.ujian.publish', $ujian) }}',
                                                        mulai: '{{ $ujian->waktu_mulai ? \Carbon\Carbon::parse($ujian->waktu_mulai)->format('Y-m-d\TH:i') : '' }}',
                                                        selesai: '{{ $ujian->deadline ? \Carbon\Carbon::parse($ujian->deadline)->format('Y-m-d\TH:i') : '' }}'
                                                    })"
                                                    class="px-3 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200/80 text-xs font-semibold transition-colors shadow-2xs cursor-pointer">
                                                    Kelola Publikasi
                                                </button>

                                                <a href="{{ route('guru.ujian.show', $ujian->id) }}" title="Lihat Detail"
                                                    class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        stroke-width="1.8" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </a>
                                            @endif

                                            {{-- TOMBOL HAPUS (Untuk draft / scheduled / unpublished yang belum ada pengerjaan) --}}
                                            @if (!$adaPengerjaan && in_array($ujian->status, ['draft', 'scheduled', 'unpublished']))
                                                <button type="button" title="Hapus Ujian"
                                                    @click="bukaModalKonfirmasi({
                                                        jenis: 'hapus',
                                                        judul: '{{ $judulAman }}',
                                                        soal: {{ $jumlahSoal }},
                                                        kelas: '{{ $kelasAman }}',
                                                        url: '{{ route('guru.ujian.destroy', $ujian->id) }}',
                                                        method: 'DELETE'
                                                    })"
                                                    class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        stroke-width="1.8" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            @endif

                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    {{-- Empty State Daftar Utama --}}
                    <div class="p-6">
                        <div
                            class="py-12 px-6 text-center text-slate-500 bg-slate-50/60 rounded-xl border border-dashed border-slate-200/80">
                            <div
                                class="w-12 h-12 mx-auto rounded-2xl bg-white text-blue-600 border border-slate-200/80 flex items-center justify-center shadow-xs">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a3 3 0 006 0M9 5h6" />
                                </svg>
                            </div>
                            <h3 class="mt-3 text-sm font-bold text-slate-700">Daftar ujian utama bersih</h3>
                            <p class="mt-1 text-xs text-slate-400 max-w-sm mx-auto leading-relaxed">
                                Belum ada ujian yang sesuai filter, atau seluruh ujian selesai telah dipindahkan ke folder
                                Diarsipkan.
                            </p>
                            <a href="{{ route('guru.ujian.create') }}"
                                class="inline-flex items-center gap-1.5 mt-4 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold transition-colors shadow-xs">
                                + Tambah Ujian Baru
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            {{-- =========================================================
                VIEW 2: TABEL FOLDER DIARSIPKAN
            ========================================================== --}}
            <div x-show="bukaFolderArsip" x-cloak class="overflow-x-auto">
                @if (isset($ujiansArsip) && $ujiansArsip->count() > 0)
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="bg-slate-50/70 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200/60">
                                <th class="py-3.5 pl-6 pr-4">Judul Ujian</th>
                                <th class="py-3.5 px-4 whitespace-nowrap">Status</th>
                                <th class="py-3.5 px-4 whitespace-nowrap">Deadline</th>
                                <th class="py-3.5 px-4">Kelas</th>
                                <th class="py-3.5 pl-4 pr-6 text-right whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            @foreach ($ujiansArsip as $ujian)
                                @php
                                    $namaKelas = $ujian->kelasDalamGrup
                                        ->map(fn($anggota) => $anggota->pengampuKelas?->kelas?->nama_kelas)
                                        ->filter()
                                        ->unique()
                                        ->values();
                                    $jumlahSoal = $ujian->soals->count();
                                    $kelasLabelRingkas = $namaKelas->isNotEmpty()
                                        ? 'Kelas ' . $namaKelas->implode(', ')
                                        : '-';
                                    $judulAman = str_replace(["\r", "\n"], ' ', addslashes($ujian->judul));
                                    $kelasAman = str_replace(["\r", "\n"], ' ', addslashes($kelasLabelRingkas));
                                @endphp
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="py-4 pl-6 pr-4 min-w-[230px]">
                                        <div class="font-bold text-slate-900 text-xs sm:text-[13px] leading-snug">
                                            {{ $ujian->judul }}
                                        </div>
                                        <div
                                            class="mt-1 flex flex-wrap items-center gap-x-2 text-[11px] text-slate-400 font-normal">
                                            <span>Mulai:
                                                {{ $ujian->waktu_mulai ? $ujian->waktu_mulai->format('d M Y, H:i') . ' WIB' : 'Selesai' }}</span>
                                            <span class="text-slate-300">&bull;</span>
                                            <span>{{ $jumlahSoal }} Soal</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-2.5 py-1 rounded-full border shadow-2xs bg-slate-100 text-slate-600 border-slate-200 before:content-[''] before:inline-block before:w-1.5 before:h-1.5 before:rounded-full before:bg-current">
                                            Diarsipkan ({{ $ujian->status_label }})
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 whitespace-nowrap text-xs font-medium text-slate-600">
                                        {{ $ujian->deadline ? $ujian->deadline->format('d M Y, H:i') . ' WIB' : '-' }}
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            @forelse ($namaKelas as $kls)
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-600 border border-blue-100/80 whitespace-nowrap">
                                                    {{ $namaKelas->count() > 2 ? $kls : 'Kelas ' . $kls }}
                                                </span>
                                            @empty
                                                <span class="text-xs text-slate-400">-</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="py-4 pl-4 pr-6 whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a href="{{ route('guru.ujian.rekap', $ujian->id) }}"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200/80 text-slate-700 border border-slate-200/80 text-xs font-semibold transition-colors shadow-2xs">
                                                Rekap Nilai
                                            </a>

                                            <a href="{{ route('guru.ujian.edit', $ujian->id) }}"
                                                class="px-3 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-600 border border-blue-200/70 text-xs font-semibold transition-colors shadow-2xs">
                                                Ubah Bobot
                                            </a>

                                            <a href="{{ route('guru.ujian.show', $ujian->id) }}" title="Lihat Detail"
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    stroke-width="1.8" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>

                                            <a href="{{ route('guru.ujian.monitoring', $ujian->id) }}"
                                                title="Hasil Monitoring & Pelanggaran"
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    stroke-width="1.8" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                            </a>

                                            <button type="button" title="Duplikasi ke Draf"
                                                @click="bukaModalKonfirmasi({
                                                    jenis: 'duplikat',
                                                    judul: '{{ $judulAman }}',
                                                    soal: {{ $jumlahSoal }},
                                                    kelas: '{{ $kelasAman }}',
                                                    url: '{{ route('guru.ujian.duplicate', $ujian->id) }}',
                                                    method: 'POST'
                                                })"
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors cursor-pointer">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    stroke-width="1.8" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                </svg>
                                            </button>

                                            <button type="button" title="Keluarkan dari Arsip"
                                                @click="bukaModalKonfirmasi({
                                                    jenis: 'keluarkan_arsip',
                                                    judul: '{{ $judulAman }}',
                                                    soal: {{ $jumlahSoal }},
                                                    kelas: '{{ $kelasAman }}',
                                                    url: '{{ route('guru.ujian.toggle-archive', $ujian->id) }}',
                                                    method: 'POST'
                                                })"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200/80 text-xs font-semibold transition-colors shadow-2xs cursor-pointer">
                                                Keluarkan
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="p-6">
                        <div
                            class="py-10 px-6 text-center bg-slate-50/60 rounded-xl border border-dashed border-slate-200/80">
                            <p class="text-sm font-bold text-slate-700">Folder Arsip masih kosong</p>
                            <p class="text-xs text-slate-400 mt-1">Ujian berstatus Selesai yang Anda arsipkan akan
                                tersimpan rapi di sini.</p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- FOOTER & PAGINATION --}}
            @if ($ujians->total() > 0)
                <div x-show="!bukaFolderArsip"
                    class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 bg-white text-xs text-slate-500">
                    <p>
                        Menampilkan
                        <span
                            class="font-bold text-slate-800">{{ $ujians->firstItem() ?? 0 }}–{{ $ujians->lastItem() ?? 0 }}</span>
                        dari
                        <span class="font-bold text-slate-800">{{ $ujians->total() }}</span>
                        ujian
                    </p>

                    <div class="flex items-center gap-1.5">
                        @if ($ujians->onFirstPage())
                            <span
                                class="px-3 py-1.5 rounded-lg border border-slate-200/80 text-slate-300 cursor-not-allowed select-none">
                                &lsaquo; Sebelumnya
                            </span>
                        @else
                            <a href="{{ $ujians->previousPageUrl() }}"
                                class="px-3 py-1.5 rounded-lg border border-slate-200/80 text-slate-600 hover:bg-slate-50 font-semibold transition-colors">
                                &lsaquo; Sebelumnya
                            </a>
                        @endif

                        @foreach ($ujians->getUrlRange(max(1, $ujians->currentPage() - 2), min($ujians->lastPage(), $ujians->currentPage() + 2)) as $page => $url)
                            @if ($page == $ujians->currentPage())
                                <span
                                    class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-blue-600 text-white font-bold shadow-xs">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}"
                                    class="w-8 h-8 inline-flex items-center justify-center rounded-lg border border-slate-200/80 text-slate-600 hover:bg-slate-50 font-semibold transition-colors">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach

                        @if ($ujians->hasMorePages())
                            <a href="{{ $ujians->nextPageUrl() }}"
                                class="px-3 py-1.5 rounded-lg border border-slate-200/80 text-slate-600 hover:bg-slate-50 font-semibold transition-colors">
                                Selanjutnya &rsaquo;
                            </a>
                        @else
                            <span
                                class="px-3 py-1.5 rounded-lg border border-slate-200/80 text-slate-300 cursor-not-allowed select-none">
                                Selanjutnya &rsaquo;
                            </span>
                        @endif
                    </div>
                </div>
            @endif

        </div>

        {{-- =========================================================
            MODAL 1: PENERBITAN UJIAN (KELOLA PUBLIKASI)
        ========================================================== --}}
        <div x-show="modalTerbuka" x-cloak x-transition.opacity @keydown.escape.window="modalTerbuka = false"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/45 backdrop-blur-[2px]">
            <div @click.away="modalTerbuka = false"
                class="bg-white rounded-2xl max-w-lg w-full border border-slate-200/80 shadow-2xl overflow-hidden">

                <form :action="targetActionUrl" method="POST" @submit="validasiPublikasi($event)" novalidate>
                    @csrf

                    <div class="p-6 space-y-5">
                        {{-- Header --}}
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <span class="text-[11px] font-bold text-blue-600 uppercase tracking-wider block">
                                    Penerbitan Ujian
                                </span>
                                <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight mt-1"
                                    x-text="targetJudul"></h3>
                                <p class="text-xs text-slate-500 leading-relaxed mt-1">
                                    Tentukan bagaimana siswa mengakses instrumen ujian ini.
                                </p>
                            </div>
                            <button type="button" @click="modalTerbuka = false"
                                class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        {{-- Info Bar Ujian --}}
                        <div
                            class="flex flex-wrap items-center justify-between gap-2 px-3.5 py-2.5 rounded-xl bg-slate-50/80 border border-slate-200/80">
                            <div class="flex items-center gap-2 min-w-0">
                                <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor"
                                    stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span class="text-xs font-bold text-slate-800 truncate" x-text="targetJudul"></span>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-200/70 text-slate-600"
                                    x-text="targetSoal + ' Soal'"></span>
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-600 border border-blue-100/80"
                                    x-text="targetKelas"></span>
                            </div>
                        </div>

                        {{-- Opsi Radio Card --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            {{-- Terbitkan Sekarang --}}
                            <label @click="publikasiError = ''"
                                class="p-4 rounded-xl border cursor-pointer transition-all duration-200 flex flex-col justify-between gap-3"
                                :class="tipePublikasi === 'langsung'
                                    ? 'border-blue-600 bg-blue-50/40 ring-1 ring-blue-600 shadow-xs'
                                    : 'border-slate-200/80 hover:border-slate-300 bg-white'">
                                <input type="radio" name="tipe_publikasi" value="langsung" x-model="tipePublikasi"
                                    class="hidden">
                                <div class="flex items-start justify-between">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center border shadow-xs transition"
                                        :class="tipePublikasi === 'langsung'
                                            ? 'bg-blue-100/80 text-blue-600 border-blue-200/60'
                                            : 'bg-slate-50 text-slate-500 border-slate-200/60'">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 01-2.448-2.448 14.9 14.9 0 01.06-.312m-2.24 2.39a4.493 4.493 0 00-1.757 4.306 4.493 4.493 0 004.306-1.758M16.5 9a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                                        </svg>
                                    </div>
                                    <span class="w-5 h-5 rounded-full border-2 flex items-center justify-center"
                                        :class="tipePublikasi === 'langsung' ? 'border-blue-600' : 'border-slate-300'">
                                        <span x-show="tipePublikasi === 'langsung'"
                                            class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm font-bold"
                                        :class="tipePublikasi === 'langsung' ? 'text-blue-900' : 'text-slate-800'">
                                        Terbitkan Sekarang
                                    </p>
                                    <p class="text-xs mt-1 leading-relaxed"
                                        :class="tipePublikasi === 'langsung' ? 'text-blue-600' : 'text-slate-500'">
                                        Siswa dapat langsung mengerjakan ujian sekarang.
                                    </p>
                                </div>
                            </label>

                            {{-- Atur Jadwal --}}
                            <label @click="publikasiError = ''"
                                class="p-4 rounded-xl border cursor-pointer transition-all duration-200 flex flex-col justify-between gap-3"
                                :class="tipePublikasi === 'jadwal'
                                    ? 'border-blue-600 bg-blue-50/40 ring-1 ring-blue-600 shadow-xs'
                                    : 'border-slate-200/80 hover:border-slate-300 bg-white'">
                                <input type="radio" name="tipe_publikasi" value="jadwal" x-model="tipePublikasi"
                                    class="hidden">
                                <div class="flex items-start justify-between">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center border shadow-xs transition"
                                        :class="tipePublikasi === 'jadwal'
                                            ? 'bg-blue-100/80 text-blue-600 border-blue-200/60'
                                            : 'bg-slate-50 text-slate-500 border-slate-200/60'">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <span class="w-5 h-5 rounded-full border-2 flex items-center justify-center"
                                        :class="tipePublikasi === 'jadwal' ? 'border-blue-600' : 'border-slate-300'">
                                        <span x-show="tipePublikasi === 'jadwal'"
                                            class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm font-bold"
                                        :class="tipePublikasi === 'jadwal' ? 'text-blue-900' : 'text-slate-800'">
                                        Atur Jadwal
                                    </p>
                                    <p class="text-xs mt-1 leading-relaxed"
                                        :class="tipePublikasi === 'jadwal' ? 'text-blue-600' : 'text-slate-500'">
                                        Tentukan tanggal mulai &amp; batas waktu pengerjaan.
                                    </p>
                                </div>
                            </label>
                        </div>

                        {{-- Info Callout Saat Terbitkan Sekarang --}}
                        <div x-show="tipePublikasi === 'langsung'"
                            class="flex items-start gap-2.5 p-3.5 rounded-xl bg-blue-50/70 border border-blue-200/70 text-xs text-blue-800 leading-relaxed">
                            <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>
                                Ujian akan langsung tampil pada dasbor siswa <strong x-text="targetKelas"></strong> segera
                                setelah disimpan.
                            </span>
                        </div>

                        {{-- Form Input Jadwal Saat Atur Jadwal --}}
                        <div x-show="tipePublikasi === 'jadwal'" class="space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                        Waktu Mulai Tayang <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="datetime-local" name="waktu_mulai" x-model="waktuMulai"
                                        @input="publikasiError = ''"
                                        class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200/80 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                        Batas Akhir / Deadline <span class="text-slate-400 font-normal">(Opsional)</span>
                                    </label>
                                    <input type="datetime-local" name="deadline" x-model="deadline"
                                        @input="publikasiError = ''"
                                        class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200/80 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                                </div>
                            </div>

                            {{-- Pesan Validasi Inline --}}
                            <div x-show="publikasiError" x-cloak
                                class="flex items-start gap-2 p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700 font-medium">
                                <svg class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                    stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span x-text="publikasiError"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Footer Modal --}}
                    <div class="px-6 py-4 bg-slate-50/70 border-t border-slate-100 flex items-center justify-end gap-2.5">
                        <button type="button" @click="modalTerbuka = false"
                            class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200/80 transition-colors cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-5 py-2 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 transition-colors shadow-xs cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Simpan &amp; Publikasikan</span>
                        </button>
                    </div>
                </form>

            </div>
        </div>

        {{-- =========================================================
            MODAL 2: KONFIRMASI AKSI SERAGAM
            (Hapus, Tarik Publikasi, Publikasi Ulang, Batalkan Jadwal, Duplikasi, Arsip)
        ========================================================== --}}
        <div x-show="konfirmasiModal.terbuka" x-cloak x-transition.opacity
            @keydown.escape.window="konfirmasiModal.terbuka = false"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/45 backdrop-blur-[2px]">
            <div @click.away="konfirmasiModal.terbuka = false"
                class="bg-white rounded-2xl max-w-lg w-full border border-slate-200/80 shadow-2xl overflow-hidden">

                <form :action="konfirmasiModal.url" method="POST">
                    @csrf
                    <template x-if="konfirmasiModal.method === 'DELETE'">
                        <input type="hidden" name="_method" value="DELETE">
                    </template>
                    <template x-if="konfirmasiModal.tipePublikasiHidden">
                        <input type="hidden" name="tipe_publikasi" :value="konfirmasiModal.tipePublikasiHidden">
                    </template>

                    <div class="p-6 space-y-5">
                        {{-- Header --}}
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <span class="text-[11px] font-bold uppercase tracking-wider block"
                                    :class="konfirmasiTema.kickerClass" x-text="konfirmasiTema.kicker"></span>
                                <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight mt-1"
                                    x-text="konfirmasiModal.judul"></h3>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed" x-text="konfirmasiTema.subtitle">
                                </p>
                            </div>
                            <button type="button" @click="konfirmasiModal.terbuka = false"
                                class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        {{-- Info Bar Ujian --}}
                        <div
                            class="flex flex-wrap items-center justify-between gap-2 px-3.5 py-2.5 rounded-xl bg-slate-50/80 border border-slate-200/80">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <template x-if="konfirmasiModal.jenis === 'batalkan_jadwal'">
                                    <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor"
                                        stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </template>
                                <template x-if="konfirmasiModal.jenis === 'duplikat'">
                                    <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor"
                                        stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </template>
                                <template
                                    x-if="konfirmasiModal.jenis !== 'batalkan_jadwal' && konfirmasiModal.jenis !== 'duplikat'">
                                    <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor"
                                        stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </template>

                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-800 truncate" x-text="konfirmasiModal.judul">
                                    </p>
                                    <p x-show="konfirmasiModal.jadwal" class="text-[11px] text-slate-400"
                                        x-text="konfirmasiModal.jadwal"></p>
                                </div>
                            </div>

                            <div class="flex items-center gap-1.5 shrink-0">
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-200/70 text-slate-600"
                                    x-text="konfirmasiModal.soal + ' Soal'"></span>
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-600 border border-blue-100/80"
                                    x-text="konfirmasiModal.kelas"></span>
                            </div>
                        </div>

                        {{-- Callout Box Peringatan / Info --}}
                        <div class="flex items-start gap-2.5 p-3.5 rounded-xl border text-xs leading-relaxed"
                            :class="konfirmasiTema.calloutClass">
                            <template x-if="konfirmasiTema.iconType === 'warning'">
                                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </template>
                            <template x-if="konfirmasiTema.iconType === 'check'">
                                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </template>
                            <template x-if="konfirmasiTema.iconType === 'info'">
                                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </template>

                            <span x-text="konfirmasiTema.calloutText"></span>
                        </div>
                    </div>

                    {{-- Footer Modal --}}
                    <div class="px-6 py-4 bg-slate-50/70 border-t border-slate-100 flex items-center justify-end gap-2.5">
                        <button type="button" @click="konfirmasiModal.terbuka = false"
                            class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200/80 transition-colors cursor-pointer">
                            Batal
                        </button>

                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-5 py-2 rounded-xl text-xs font-semibold text-white transition-colors shadow-xs cursor-pointer"
                            :class="konfirmasiTema.btnClass">
                            <template x-if="konfirmasiModal.jenis === 'hapus'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </template>
                            <template x-if="konfirmasiModal.jenis === 'tarik'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </template>
                            <template x-if="konfirmasiModal.jenis === 'publikasi_ulang'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                            </template>
                            <template x-if="konfirmasiModal.jenis === 'batalkan_jadwal'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </template>
                            <template x-if="konfirmasiModal.jenis === 'duplikat'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </template>
                            <template
                                x-if="konfirmasiModal.jenis === 'arsipkan' || konfirmasiModal.jenis === 'keluarkan_arsip'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                </svg>
                            </template>

                            <span x-text="konfirmasiTema.btnLabel"></span>
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

                // State Modal Penerbitan Ujian
                modalTerbuka: false,
                targetId: null,
                targetJudul: '',
                targetSoal: 0,
                targetKelas: '',
                targetActionUrl: '',
                tipePublikasi: 'langsung',
                waktuMulai: '',
                deadline: '',
                publikasiError: '',

                // State Modal Konfirmasi Universal
                konfirmasiModal: {
                    terbuka: false,
                    jenis: 'hapus',
                    judul: '',
                    soal: 0,
                    kelas: '',
                    jadwal: '',
                    url: '',
                    method: 'POST',
                    tipePublikasiHidden: '',
                },

                bukaModalPublikasi(payload) {
                    this.targetId = payload.id;
                    this.targetJudul = payload.judul || '';
                    this.targetSoal = payload.soal || 0;
                    this.targetKelas = payload.kelas || '-';
                    this.targetActionUrl = payload.url || '';
                    this.waktuMulai = payload.mulai || '';
                    this.deadline = payload.selesai || '';
                    this.tipePublikasi = this.waktuMulai ? 'jadwal' : 'langsung';
                    this.publikasiError = '';
                    this.modalTerbuka = true;
                },

                validasiPublikasi(event) {
                    this.publikasiError = '';
                    if (this.tipePublikasi === 'jadwal') {
                        if (!this.waktuMulai) {
                            event.preventDefault();
                            this.publikasiError = 'Waktu Mulai Tayang wajib diisi saat memilih opsi Atur Jadwal.';
                            return;
                        }
                        if (this.deadline && new Date(this.deadline) <= new Date(this.waktuMulai)) {
                            event.preventDefault();
                            this.publikasiError = 'Batas Akhir / Deadline harus lebih akhir daripada Waktu Mulai Tayang.';
                            return;
                        }
                    }
                },

                bukaModalKonfirmasi(payload) {
                    this.konfirmasiModal = {
                        terbuka: true,
                        jenis: payload.jenis || 'hapus',
                        judul: payload.judul || '',
                        soal: payload.soal || 0,
                        kelas: payload.kelas || '-',
                        jadwal: payload.jadwal || '',
                        url: payload.url || '',
                        method: payload.method || 'POST',
                        tipePublikasiHidden: payload.tipePublikasiHidden || '',
                    };
                },

                get konfirmasiTema() {
                    const m = this.konfirmasiModal;
                    switch (m.jenis) {
                        case 'hapus':
                            return {
                                kicker: 'Hapus Ujian ?',
                                kickerClass: 'text-rose-600',
                                subtitle: `Yakin ingin menghapus ujian "${m.judul}" beserta seluruh butir soalnya secara permanen?`,
                                iconType: 'warning',
                                calloutClass: 'bg-rose-50/90 border-rose-200 text-rose-700',
                                calloutText: 'Tindakan ini bersifat permanen dan tidak dapat dibatalkan. Seluruh soal, bobot penilaian, dan konfigurasi kelas pada ujian ini akan ikut terhapus.',
                                btnClass: 'bg-rose-600 hover:bg-rose-700',
                                btnLabel: 'Ya, Hapus Ujian',
                            };
                        case 'tarik':
                            return {
                                kicker: 'Tarik Publikasi Ujian ?',
                                kickerClass: 'text-red-700',
                                subtitle: 'Apakah Anda yakin ingin menarik publikasi ujian ini dari akses siswa sekarang?',
                                iconType: 'warning',
                                calloutClass: 'bg-rose-50/90 border-rose-200 text-red-700',
                                calloutText: 'Siswa yang belum selesai akan dikeluarkan dari lembar ujian. Nilai siswa yang sudah selesai tetap tersimpan aman.',
                                btnClass: 'bg-red-700 hover:bg-red-800',
                                btnLabel: 'Ya, Tarik Publikasi',
                            };
                        case 'publikasi_ulang':
                            return {
                                kicker: 'Publikasi Ulang Ujian',
                                kickerClass: 'text-emerald-700',
                                subtitle: 'Publikasikan kembali ujian ini agar dapat dikerjakan siswa?',
                                iconType: 'check',
                                calloutClass: 'bg-emerald-50/80 border-emerald-200 text-emerald-800',
                                calloutText: `Ujian akan kembali aktif dan dapat diakses oleh siswa ${m.kelas} sesuai jadwal serta batas waktu yang berlaku.`,
                                btnClass: 'bg-emerald-600 hover:bg-emerald-700',
                                btnLabel: 'Ya, Publikasikan Lagi',
                            };
                        case 'batalkan_jadwal':
                            return {
                                kicker: 'Batalkan Jadwal Ujian ?',
                                kickerClass: 'text-amber-700',
                                subtitle: 'Batalkan jadwal ujian ini?',
                                iconType: 'warning',
                                calloutClass: 'bg-amber-50/90 border-amber-200 text-amber-800',
                                calloutText: 'Jadwal tayang ujian akan dibatalkan dan status ujian akan dinonaktifkan sehingga belum dapat diakses oleh siswa.',
                                btnClass: 'bg-amber-600 hover:bg-amber-700',
                                btnLabel: 'Ya, Batalkan Jadwal',
                            };
                        case 'duplikat':
                            return {
                                kicker: 'Duplikasi Instrumen Ujian',
                                kickerClass: 'text-blue-600',
                                subtitle: 'Duplikasi instrumen soal ke draf ujian baru?',
                                iconType: 'info',
                                calloutClass: 'bg-blue-50/70 border-blue-200 text-blue-700',
                                calloutText: 'Seluruh butir soal, kunci jawaban, dan pengaturan bobot akan disalin menjadi draf ujian baru tanpa menyertakan riwayat nilai siswa.',
                                btnClass: 'bg-blue-600 hover:bg-blue-700',
                                btnLabel: 'Ya, Duplikasi ke Draf',
                            };
                        case 'arsipkan':
                            return {
                                kicker: 'Arsipkan Ujian',
                                kickerClass: 'text-slate-600',
                                subtitle: 'Pindahkan ujian selesai ini ke dalam Folder Diarsipkan?',
                                iconType: 'info',
                                calloutClass: 'bg-slate-50 border-slate-200 text-slate-700',
                                calloutText: 'Ujian akan disembunyikan dari daftar utama agar tampilan tetap bersih. Data nilai dan hasil monitoring tetap tersimpan dan dapat dibuka kapan saja di Folder Arsip.',
                                btnClass: 'bg-slate-700 hover:bg-slate-800',
                                btnLabel: 'Ya, Arsipkan',
                            };
                        default:
                            return {
                                kicker: 'Keluarkan dari Arsip',
                                kickerClass: 'text-amber-700',
                                subtitle: 'Kembalikan ujian ini ke daftar utama?',
                                iconType: 'info',
                                calloutClass: 'bg-amber-50/80 border-amber-200 text-amber-800',
                                calloutText: 'Ujian akan ditampilkan kembali di daftar utama Pusat Ujian.',
                                btnClass: 'bg-amber-600 hover:bg-amber-700',
                                btnLabel: 'Ya, Keluarkan',
                            };
                    }
                }
            };
        }

        document.addEventListener('DOMContentLoaded', function() {

            const statusUrl = @json(route('guru.ujian.status-realtime'));

            const statusStyles = {
                draft: 'bg-blue-50 text-blue-600 border-blue-200/70',
                scheduled: 'bg-amber-50 text-amber-700 border-amber-200/70',
                published: 'bg-emerald-50 text-emerald-700 border-emerald-200/70',
                completed: 'bg-slate-100 text-slate-600 border-slate-200',
                unpublished: 'bg-rose-50 text-rose-600 border-rose-200/70',
            };

            const filterStatus = document.getElementById('filter-status');

            function terapkanFilterStatus() {
                const statusDipilih = filterStatus ? filterStatus.value : '';

                document.querySelectorAll('[data-ujian-id]').forEach(function(baris) {
                    const statusBaris = baris.dataset.ujianStatus;

                    baris.style.display =
                        (!statusDipilih || statusBaris === statusDipilih) ?
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
                            const baris = document.querySelector(`[data-ujian-id="${ujian.id}"]`);
                            if (!baris) return;

                            baris.dataset.ujianStatus = ujian.status;

                            const badge = baris.querySelector('[data-status-badge]');
                            if (badge) {
                                badge.textContent = ujian.status_label;
                                badge.className =
                                    'inline-flex items-center gap-1.5 text-[11px] font-semibold px-2.5 py-1 rounded-full border shadow-2xs before:content-[\'\'] before:inline-block before:w-1.5 before:h-1.5 before:rounded-full before:bg-current ' +
                                    (statusStyles[ujian.status] || statusStyles.draft);
                            }
                        });
                    }

                    terapkanFilterStatus();

                } catch (error) {
                    console.error('Pembaruan status ujian realtime:', error);
                }
            }

            terapkanFilterStatus();
            perbaruiStatusUjian();

            setInterval(perbaruiStatusUjian, 60000);

        });
    </script>

@endsection
