@extends('layouts.app')

@section('title', 'Ujian & Evaluasi')

@section('content')

    <div class="space-y-6">

        {{-- =========================================================
            HEADER HALAMAN
        ========================================================== --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2.5">
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                        Ujian & Evaluasi Siswa
                    </h2>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Aktif
                    </span>
                </div>
                <p class="mt-1 text-sm text-gray-500">
                    Kelola pelaksanaan tes, bank soal, dan analisis pencapaian belajar siswa.
                </p>
            </div>

            <a href="{{ route('guru.ujian.create') }}"
                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition-all duration-200 shadow-sm hover:shadow-md hover:-translate-y-0.5 shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Ujian Baru
            </a>
        </div>


        {{-- =========================================================
            HERO BANNER (CBT Sync Center)
        ========================================================== --}}
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-blue-600 via-indigo-600 to-indigo-700 p-6 sm:p-8 text-white shadow-lg">
            {{-- Background decorative shapes --}}
            <div class="absolute -right-12 -top-12 w-64 h-64 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
            <div class="absolute right-40 -bottom-16 w-56 h-56 rounded-full bg-indigo-500/20 blur-xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="max-w-2xl space-y-3">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 backdrop-blur-md border border-white/20 text-xs font-medium text-blue-100">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        Live Sync CBT &bull; Terhubung ke {{ $daftarKelas->count() }} Kelas
                    </div>

                    <h3 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white flex items-center gap-2">
                        Pusat Ujian & Evaluasi Siswa
                        <span>📄</span>
                    </h3>

                    <p class="text-sm text-blue-100 leading-relaxed font-normal">
                        Kelola bank soal, jadwal pelaksanaan asesmen, dan pantau hasil evaluasi belajar siswa secara terpusat dan terstruktur.
                    </p>
                </div>

                {{-- Status Ringkasan --}}
                <div class="hidden md:flex flex-col items-start bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl p-4 min-w-[210px] text-white shadow-inner">
                    <div class="flex items-center gap-2 text-xs font-semibold tracking-wider text-blue-100 uppercase">
                        <svg class="w-4 h-4 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <span>Tingkat Kesiapan</span>
                    </div>
                    @php
                        $persenSiap = $stats['total'] > 0 ? round((($stats['published'] + $stats['completed']) / $stats['total']) * 100, 1) : 0;
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a3 3 0 006 0M9 5h6" />
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <span class="text-[10px] font-semibold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-md">Tindakan</span>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <span class="text-[10px] font-semibold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md">Jadwal</span>
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
                    <span class="text-[10px] font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md">Rilis</span>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5 2a8 8 0 11-16 0 8 8 0 0116 0z" />
                        </svg>
                    </div>
                    <span class="text-[10px] font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">Tuntas</span>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                    <span class="text-[10px] font-semibold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md">Arsip</span>
                </div>
                <div class="mt-3">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Nonaktif</p>
                    <p id="stat-unpublished" class="mt-1 text-2xl font-black text-gray-900">{{ $stats['unpublished'] }}</p>
                    <p class="mt-1 text-[11px] text-gray-500 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span> Belum tayang
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
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m2.35-5.65a8 8 0 11-16 0 8 8 0 0116 0z" />
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari judul ujian, topik asesmen, atau mata pelajaran..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 text-sm text-gray-800 placeholder-gray-400 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                </div>

                {{-- Filter Pilihan & Tombol Submit --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_auto] gap-3">

                    {{-- Status Dropdown (ID filter-status digunakan oleh JavaScript realtime) --}}
                    <div>
                        <select id="filter-status" name="status"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 text-xs font-medium text-gray-700 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition cursor-pointer">
                            <option value="">Semua Status</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draf</option>
                            <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Terjadwal</option>
                            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Dipublikasi</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
                            <option value="unpublished" {{ request('status') === 'unpublished' ? 'selected' : '' }}>Tidak Dipublikasi</option>
                        </select>
                    </div>

                    {{-- Kelas Dropdown --}}
                    <div>
                        <select name="kelas_id"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 text-xs font-medium text-gray-700 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition cursor-pointer">
                            <option value="">Semua Kelas</option>
                            @foreach ($daftarKelas as $kelas)
                                <option value="{{ $kelas->id }}" {{ request('kelas_id') == $kelas->id ? 'selected' : '' }}>
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
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
            DAFTAR UJIAN & ASESMEN
        ========================================================== --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">

            {{-- Card Header --}}
            <div class="px-6 py-4.5 border-b border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 bg-white">
                <div>
                    <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        Daftar Ujian & Asesmen
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                    </h3>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Semua instrumen ujian dan asesmen yang dikelola oleh Anda.
                    </p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                    {{ $ujians->total() }} Ujian Terdata
                </span>
            </div>

            {{-- =========================================================
            DAFTAR UJIAN & ASESMEN (IMPLEMENTASI KODE CARD)
        ========================================================== --}}
        <div id="daftar-ujian" class="p-4 sm:p-6 space-y-4 bg-slate-50/50">
            @forelse($ujians as $ujian)
                @php
                    // Ambil nama mata pelajaran dari seluruh anggota grup
                    $namaMapel = $ujian->kelasDalamGrup
                        ->map(fn($anggota) => $anggota->pengampuKelas?->guruMapel?->mapel?->nama_mapel)
                        ->filter()
                        ->unique()
                        ->values();

                    // Ambil nama kelas dari seluruh anggota grup
                    $namaKelas = $ujian->kelasDalamGrup
                        ->map(fn($anggota) => $anggota->pengampuKelas?->kelas?->nama_kelas)
                        ->filter()
                        ->unique()
                        ->values();

                    $mapelName = $namaMapel->first() ?? 'Umum';

                    // Garis aksen samping dinamis sesuai status
                    $stripeColor = match ($ujian->status) {
                        'published'   => 'bg-emerald-500',
                        'scheduled'   => 'bg-amber-500',
                        'completed'   => 'bg-slate-400',
                        'draft'       => 'bg-amber-400',
                        'unpublished' => 'bg-rose-500',
                        default       => 'bg-gray-300',
                    };

                    // Warna badge status dinamis
                    $badgeClass = match ($ujian->status) {
                        'published'   => 'text-emerald-700 bg-emerald-50 border-emerald-200/80',
                        'scheduled'   => 'text-amber-700 bg-amber-50 border-amber-200/80',
                        'completed'   => 'text-slate-700 bg-slate-100 border-slate-200/80',
                        'draft'       => 'text-gray-700 bg-gray-100 border-gray-200/80',
                        'unpublished' => 'text-rose-700 bg-rose-50 border-rose-200/80',
                        default       => 'text-gray-700 bg-gray-100 border-gray-200/80',
                    };

                    // Format kode ID ujian dinamis
                    $singkatanMapel = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $mapelName), 0, 3));
                    $kodeIdUjian = 'EX-' . date('Y') . '-' . ($singkatanMapel ?: 'UJN') . str_pad($ujian->id, 2, '0', STR_PAD_LEFT);
                @endphp

                <div data-ujian-id="{{ $ujian->id }}" data-ujian-status="{{ $ujian->status }}"
                    class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm relative flex flex-col md:flex-row md:items-center justify-between gap-4 hover:shadow-md transition duration-150">

                    <!-- Garis Aksen Samping (Warna Dinamis Sesuai Status) -->
                    <div class="absolute left-0 top-4 bottom-4 w-1.5 rounded-r bg-blue-600"></div>

                    <!-- Bagian Kiri: Metadata & Judul -->
                    <div class="flex-1 pl-3 min-w-0">
                        <!-- Tag Mapel, Kelas, & ID -->
                        <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500 mb-1.5">
                            <span class="text-blue-600 bg-blue-50 px-2 py-0.5 rounded font-medium">
                                {{ $mapelName }}
                            </span>
                            <span>•</span>
                            <span>Kelas: {{ $namaKelas->isNotEmpty() ? $namaKelas->implode(', ') : '-' }}</span>
                            <span>•</span>
                            <span class="text-gray-400 font-mono">ID: {{ $kodeIdUjian }}</span>
                        </div>

                        <!-- Judul Ujian -->
                        <h3 class="text-base font-semibold text-gray-900 mb-2 truncate" title="{{ $ujian->judul }}">
                            {{ $ujian->judul }}
                        </h3>

                        <!-- Informasi Waktu & Jumlah Soal -->
                        <div class="flex flex-wrap items-center gap-4 text-xs text-gray-400">
                            {{-- Jadwal Mulai --}}
                            <span class="flex items-center gap-1.5 {{ !$ujian->waktu_mulai ? 'italic' : '' }}">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ $ujian->waktu_mulai ? $ujian->waktu_mulai->format('d M Y, H:i') . ' WIB' : 'Belum dijadwalkan' }}
                            </span>

                            {{-- Durasi --}}
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $ujian->durasi_menit }} menit
                            </span>

                            {{-- Jumlah Soal --}}
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                {{ $ujian->soals->count() }} Soal
                            </span>
                        </div>
                    </div>

                    <!-- Bagian Kanan: Status Badge & Tombol Aksi -->
                    <div class="flex flex-col items-start md:items-end justify-between self-stretch gap-3 shrink-0 pt-2 md:pt-0">

                        <!-- Status Badge (Diposisikan di kanan atas) -->
                        <div>
                            {{-- Menggunakan pseudo ::before agar titik dot tidak terhapus saat script realtime update text --}}
                            <span data-status-badge
                                class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full border before:content-[''] before:inline-block before:w-1.5 before:h-1.5 before:rounded-full before:bg-current {{ $badgeClass }}">
                                {{ $ujian->status_label }}
                            </span>
                        </div>

                        <!-- Grup Tombol Aksi (Diposisikan di kanan bawah) -->
                        <div class="flex flex-wrap items-center gap-2">

                            {{-- 1. Preview Soal --}}
                            <a href="{{ route('guru.ujian.show', $ujian) }}"
                                class="text-xs text-gray-500 hover:text-gray-700 px-3 py-1.5 rounded-lg border border-transparent hover:border-gray-200 transition">
                                Preview Soal
                            </a>

                            {{-- 2. Tombol Utama Sesuai Status --}}
                            @if ($ujian->status === 'draft')
                                <a href="{{ route('guru.ujian.edit', $ujian) }}"
                                    class="text-xs text-gray-600 hover:text-gray-900 px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                                    Edit Soal
                                </a>

                                <form method="POST" action="{{ route('guru.ujian.publish', $ujian) }}"
                                    data-confirm="Publikasikan ujian ini sekarang?" class="inline">
                                    @csrf
                                    <button type="submit"
                                        class="text-xs font-medium text-white bg-emerald-600 hover:bg-emerald-700 px-3.5 py-1.5 rounded-lg shadow-sm transition cursor-pointer">
                                        Publikasikan
                                    </button>
                                </form>
                            @elseif ($ujian->status === 'scheduled')
                                <a href="{{ route('guru.ujian.edit', $ujian) }}"
                                    class="text-xs font-medium text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 px-3.5 py-1.5 rounded-lg shadow-sm transition">
                                    Kelola / Edit
                                </a>
                            @elseif ($ujian->status === 'published')
                                <a href="{{ route('guru.ujian.show', $ujian) }}"
                                    class="text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 px-3.5 py-1.5 rounded-lg shadow-sm transition">
                                    Detail Hasil
                                </a>
                            @else
                                <a href="{{ route('guru.ujian.show', $ujian) }}"
                                    class="text-xs font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 px-3.5 py-1.5 rounded-lg shadow-sm transition">
                                    Rekap Nilai
                                </a>
                            @endif

                            {{-- 3. Tombol Hapus --}}
                            <form method="POST" action="{{ route('guru.ujian.destroy', $ujian) }}"
                                data-confirm="Ujian ini akan dihapus beserta soal-soalnya. Apakah Anda yakin?" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="text-xs text-red-500 hover:text-red-700 px-2 py-1.5 transition cursor-pointer">
                                    Hapus
                                </button>
                            </form>

                        </div>

                    </div>

                </div>
            @empty
                {{-- Empty state --}}
                <div class="px-6 py-16 text-center bg-white rounded-2xl border border-gray-200">
                    <div class="w-16 h-16 mx-auto rounded-2xl bg-blue-50 text-blue-500 flex items-center justify-center">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a3 3 0 006 0M9 5h6" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-base font-bold text-gray-900">Belum ada ujian</h3>
                    <p class="mt-1 text-sm text-gray-500 max-w-sm mx-auto">
                        Mulai buat ujian pertama Anda untuk memberikan evaluasi kepada siswa.
                    </p>
                    <a href="{{ route('guru.ujian.create') }}"
                        class="inline-flex items-center gap-2 mt-5 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold transition shadow-xs">
                        + Tambah Ujian
                    </a>
                </div>
            @endforelse
        </div>

            {{-- Pagination & Footer List --}}
            @if ($ujians->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4 bg-white">
                    <p class="text-xs text-gray-500">
                        Menampilkan <span class="font-semibold text-gray-800">{{ $ujians->firstItem() ?? 0 }}</span> - <span class="font-semibold text-gray-800">{{ $ujians->lastItem() ?? 0 }}</span> dari <span class="font-semibold text-gray-800">{{ $ujians->total() }}</span> ujian
                    </p>
                    <div class="text-xs">
                        {{ $ujians->links() }}
                    </div>
                </div>
            @endif

        </div>

    </div>

    {{-- =========================================================
        LOGIKA JAVASCRIPT REALTIME & FILTER
    ========================================================== --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const statusUrl = @json(route('guru.ujian.status-realtime'));

            const statusStyles = {
                draft: 'bg-amber-50 text-amber-700 border-amber-200',
                scheduled: 'bg-indigo-50 text-indigo-700 border-indigo-200',
                published: 'bg-emerald-50 text-emerald-700 border-emerald-200',
                completed: 'bg-slate-100 text-slate-700 border-slate-200',
                unpublished: 'bg-rose-50 text-rose-700 border-rose-200',
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

                    // Simpan status awal setiap kartu
                    const statusSebelumnya = {};
                    document.querySelectorAll('[data-ujian-id]').forEach(function(kartu) {
                        statusSebelumnya[kartu.dataset.ujianId] = kartu.dataset.ujianStatus;
                    });

                    // Perbarui counter statistik realtime
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

                    // Perbarui status kartu ujian
                    if (Array.isArray(data.ujians)) {
                        data.ujians.forEach(function(ujian) {
                            const kartu = document.querySelector(`[data-ujian-id="${ujian.id}"]`);
                            if (!kartu) return;

                            kartu.dataset.ujianStatus = ujian.status;

                            const badge = kartu.querySelector('[data-status-badge]');
                            if (badge) {
                                badge.textContent = ujian.status_label;
                                badge.className =
                                    'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold border ' +
                                    (statusStyles[ujian.status] || statusStyles.draft);
                            }
                        });
                    }

                    // Terapkan kembali filter setelah data sinkron
                    terapkanFilterStatus();

                } catch (error) {
                    console.error('Pembaruan status ujian realtime:', error);
                }
            }

            // Pasang event listener filter lokal
            if (filterStatus) {
                filterStatus.addEventListener('change', terapkanFilterStatus);
            }

            terapkanFilterStatus();
            perbaruiStatusUjian();

            // Interval polling setiap 60 detik
            setInterval(perbaruiStatusUjian, 60000);

        });
    </script>

@endsection
