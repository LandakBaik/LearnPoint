@extends('layouts.app')

@section('title', 'Monitoring Aktivitas Guru')

@section('content')
@php
    $queryParams = array_filter([
        'academic_period_id' => $selectedPeriodId,
        'kelas_id' => $selectedKelasId,
        'mapel_id' => $selectedMapelId,
        'keaktifan' => $selectedKeaktifan !== 'semua' ? $selectedKeaktifan : null,
        'search' => $search !== '' ? $search : null,
    ], fn ($v) => $v !== null && $v !== '');
@endphp

<div class="space-y-6 max-w-7xl mx-auto">
    <!-- Header Banner -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-700 via-blue-600 to-cyan-600 text-white p-6 sm:p-8 shadow-md">
        <div class="absolute -right-10 -top-12 w-64 h-64 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
        <div class="absolute right-44 -bottom-12 w-48 h-48 rounded-full bg-cyan-300/20 blur-xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-5">
            <div class="space-y-2 max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 border border-white/20 text-xs font-semibold text-blue-50 backdrop-blur-xs">
                    <i class="fa-solid fa-calendar-check text-[11px]"></i>
                    <span>Periode: {{ $selectedPeriod?->label ?? 'Aktif' }}</span>
                    @if($selectedKelas)
                        <span class="opacity-60">•</span>
                        <span>Kelas {{ $selectedKelas->nama_kelas }}</span>
                    @endif
                    @if($selectedMapel)
                        <span class="opacity-60">•</span>
                        <span>{{ $selectedMapel->nama_mapel }}</span>
                    @endif
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight leading-tight">
                    Monitoring &amp; Rekap Aktivitas Guru
                </h1>
                <p class="text-blue-100 text-sm leading-relaxed">
                    Pantau keaktifan guru dalam memberikan <strong>Materi</strong>, <strong>Tugas</strong>, <strong>Kuis</strong>, <strong>Ujian</strong>, serta progres koreksi tugas dan essay siswa.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <a href="{{ route('admin.monitoring-guru.export-csv', $queryParams) }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/15 hover:bg-white/25 border border-white/25 text-white text-sm font-semibold transition-all shadow-xs">
                    <i class="fa-solid fa-file-csv text-base"></i>
                    <span>Export CSV</span>
                </a>
                <a href="{{ route('admin.monitoring-guru.export-pdf', $queryParams) }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white text-blue-700 hover:bg-blue-50 text-sm font-bold transition-all shadow-sm">
                    <i class="fa-solid fa-file-pdf text-base text-rose-600"></i>
                    <span>Export PDF</span>
                </a>
            </div>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Guru -->
        <div class="bg-white border border-blue-100 rounded-2xl p-5 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-blue-600"></div>
            <div class="flex items-start justify-between gap-3">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-blue-700">Total Guru</span>
                    <div class="text-2xl font-extrabold text-slate-900 mt-1">{{ $summary['total_guru'] }}</div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100 shrink-0">
                    <i class="fa-solid fa-chalkboard-user text-lg"></i>
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span class="text-emerald-700 font-semibold">Aktif: {{ $summary['guru_aktif'] }}</span>
                <span class="text-rose-600 font-semibold">Belum Aktif: {{ $summary['guru_belum_aktif'] }}</span>
            </div>
        </div>

        <!-- Card 2: Total Materi & Tugas -->
        <div class="bg-white border border-emerald-100 rounded-2xl p-5 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-emerald-600"></div>
            <div class="flex items-start justify-between gap-3">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Materi &amp; Tugas</span>
                    <div class="text-2xl font-extrabold text-slate-900 mt-1">{{ $summary['total_materi'] + $summary['total_tugas'] }}</div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100 shrink-0">
                    <i class="fa-solid fa-book-open-reader text-lg"></i>
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Materi: <strong class="text-slate-700">{{ $summary['total_materi'] }}</strong></span>
                <span>Tugas: <strong class="text-slate-700">{{ $summary['total_tugas'] }}</strong></span>
            </div>
        </div>

        <!-- Card 3: Total Kuis & Ujian -->
        <div class="bg-white border border-indigo-100 rounded-2xl p-5 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-indigo-600"></div>
            <div class="flex items-start justify-between gap-3">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-700">Kuis &amp; Ujian Aktif</span>
                    <div class="text-2xl font-extrabold text-slate-900 mt-1">{{ $summary['total_kuis_published'] + $summary['total_ujian_aktif'] }}</div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100 shrink-0">
                    <i class="fa-solid fa-file-pen text-lg"></i>
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Kuis: <strong class="text-slate-700">{{ $summary['total_kuis_published'] }}/{{ $summary['total_kuis_all'] }}</strong></span>
                <span>Ujian: <strong class="text-slate-700">{{ $summary['total_ujian_aktif'] }}/{{ $summary['total_ujian_all'] }}</strong></span>
            </div>
        </div>

        <!-- Card 4: Progres Koreksi -->
        <div class="bg-white border border-amber-100 rounded-2xl p-5 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-amber-500"></div>
            <div class="flex items-start justify-between gap-3">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-amber-700">Koreksi Tugas</span>
                    <div class="text-2xl font-extrabold text-slate-900 mt-1">{{ $summary['persen_koreksi_tugas'] }}%</div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-100 shrink-0">
                    <i class="fa-solid fa-clipboard-check text-lg"></i>
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Menunggu: <strong class="{{ $summary['tugas_menunggu_dinilai'] > 0 ? 'text-amber-700' : 'text-slate-700' }}">{{ $summary['tugas_menunggu_dinilai'] }}</strong></span>
                <span>Essay: <strong class="{{ $summary['essay_menunggu_koreksi'] > 0 ? 'text-rose-600' : 'text-slate-700' }}">{{ $summary['essay_menunggu_koreksi'] }}</strong></span>
            </div>
        </div>
    </section>

    <!-- Filter Panel -->
    <section class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5">
        <form method="GET" action="{{ route('admin.monitoring-guru.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
                <!-- Periode Akademik -->
                <div>
                    <label for="academic_period_id" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Periode Akademik
                    </label>
                    <select id="academic_period_id" name="academic_period_id" onchange="this.form.submit()"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-2.5 text-sm font-medium text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                        @foreach($periods as $period)
                            <option value="{{ $period->id }}" {{ (int) $selectedPeriodId === (int) $period->id ? 'selected' : '' }}>
                                {{ $period->label }} {{ $period->is_active ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Mata Pelajaran -->
                <div>
                    <label for="mapel_id" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Mata Pelajaran
                    </label>
                    <select id="mapel_id" name="mapel_id" onchange="this.form.submit()"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-2.5 text-sm font-medium text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                        <option value="">Semua Mata Pelajaran</option>
                        @foreach($mapelList as $mapel)
                            <option value="{{ $mapel->id }}" {{ (int) $selectedMapelId === (int) $mapel->id ? 'selected' : '' }}>
                                {{ $mapel->nama_mapel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Kelas -->
                <div>
                    <label for="kelas_id" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Kelas Diampu
                    </label>
                    <select id="kelas_id" name="kelas_id" onchange="this.form.submit()"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-2.5 text-sm font-medium text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                        <option value="">Semua Kelas</option>
                        @foreach($kelasList as $kelas)
                            <option value="{{ $kelas->id }}" {{ (int) $selectedKelasId === (int) $kelas->id ? 'selected' : '' }}>
                                {{ $kelas->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Tingkat Keaktifan -->
                <div>
                    <label for="keaktifan" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Tingkat Keaktifan
                    </label>
                    <select id="keaktifan" name="keaktifan" onchange="this.form.submit()"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-2.5 text-sm font-medium text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                        <option value="semua" {{ $selectedKeaktifan === 'semua' ? 'selected' : '' }}>Semua Tingkat</option>
                        <option value="sangat_aktif" {{ $selectedKeaktifan === 'sangat_aktif' ? 'selected' : '' }}>Sangat Aktif ({{ $summary['guru_sangat_aktif'] }})</option>
                        <option value="aktif" {{ $selectedKeaktifan === 'aktif' ? 'selected' : '' }}>Aktif ({{ $summary['guru_aktif'] - $summary['guru_sangat_aktif'] }})</option>
                        <option value="kurang_aktif" {{ $selectedKeaktifan === 'kurang_aktif' ? 'selected' : '' }}>Kurang Aktif ({{ $summary['guru_kurang_aktif'] }})</option>
                        <option value="belum_aktif" {{ $selectedKeaktifan === 'belum_aktif' ? 'selected' : '' }}>Belum Aktif ({{ $summary['guru_belum_aktif'] }})</option>
                    </select>
                </div>

                <!-- Pencarian -->
                <div>
                    <label for="search" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Cari Guru
                    </label>
                    <div class="relative">
                        <input type="text" id="search" name="search" value="{{ $search }}"
                               placeholder="Nama / NUPTK..."
                               class="w-full rounded-xl border border-slate-200 bg-slate-50/70 pl-9 pr-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                        <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs absolute left-3 top-1/2 -translate-y-1/2"></i>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-slate-100">
                <div class="flex flex-wrap items-center gap-2 text-[11px] text-slate-500">
                    <span class="font-bold text-slate-600">Indikator Keaktifan:</span>
                    <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 font-semibold">Sangat Aktif (≥10 / lengkap)</span>
                    <span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 font-semibold">Aktif (≥3)</span>
                    <span class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 font-semibold">Kurang Aktif (1–2)</span>
                    <span class="px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 font-semibold">Belum Aktif (0)</span>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.monitoring-guru.index') }}"
                       class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors">
                        <i class="fa-solid fa-rotate-left mr-1"></i> Reset Filter
                    </a>
                    <button type="submit"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 transition-colors shadow-xs">
                        <i class="fa-solid fa-filter mr-1"></i> Terapkan
                    </button>
                </div>
            </div>
        </form>
    </section>

    <!-- Tabel Utama Monitoring Guru -->
    <section class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h2 class="text-base font-extrabold text-slate-900">Rekap Aktivitas Pembelajaran Per Guru</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Menampilkan <strong class="text-slate-700">{{ $rows->count() }}</strong> dari {{ $allRowsCount }} guru, diurutkan dari total aktivitas tertinggi.
                </p>
            </div>
        </div>

        @if($rows->isEmpty())
            <div class="p-12 text-center">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mb-4">
                    <i class="fa-solid fa-user-slash text-2xl"></i>
                </div>
                <h3 class="text-base font-bold text-slate-800">Data Guru Tidak Ditemukan</h3>
                <p class="text-sm text-slate-500 mt-1 max-w-md mx-auto">
                    Tidak ada guru yang cocok dengan filter periode, mata pelajaran, kelas, atau tingkat keaktifan yang dipilih.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/90 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="py-3.5 px-4 text-center w-12">No</th>
                            <th class="py-3.5 px-4 min-w-[220px]">Guru &amp; Mapel Diampu</th>
                            <th class="py-3.5 px-3 text-center">Materi</th>
                            <th class="py-3.5 px-3 text-center">Tugas</th>
                            <th class="py-3.5 px-3 text-center">Kuis</th>
                            <th class="py-3.5 px-3 text-center">Ujian</th>
                            <th class="py-3.5 px-3 text-center">Koreksi</th>
                            <th class="py-3.5 px-3 text-center bg-blue-50/60 text-blue-800">Total</th>
                            <th class="py-3.5 px-4 text-center">Keaktifan</th>
                            <th class="py-3.5 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($rows as $i => $row)
                            <tr class="hover:bg-blue-50/30 transition-colors">
                                <td class="py-3.5 px-4 text-center text-xs font-bold text-slate-500">{{ $i + 1 }}</td>

                                <!-- Guru & Mapel -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-slate-900 leading-tight">{{ $row['nama_guru'] }}</span>
                                        @if($row['wali_kelas'])
                                            <span class="px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700 text-[10px] font-bold border border-indigo-100" title="Wali Kelas">
                                                Wali {{ $row['wali_kelas'] }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">
                                        NUPTK: {{ $row['nuptk'] }} • {{ $row['jumlah_kelas'] }} Kelas
                                    </div>
                                    @if(!empty($row['mapel_list']))
                                        <div class="flex flex-wrap gap-1 mt-1.5">
                                            @foreach(array_slice($row['mapel_list'], 0, 3) as $mp)
                                                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[10px] font-semibold">{{ $mp }}</span>
                                            @endforeach
                                            @if(count($row['mapel_list']) > 3)
                                                <span class="px-1.5 py-0.5 rounded-md bg-slate-100 text-slate-500 text-[10px] font-semibold">+{{ count($row['mapel_list']) - 3 }}</span>
                                            @endif
                                        </div>
                                    @else
                                        <div class="text-[10px] text-rose-500 italic mt-1">Belum ada penugasan kelas di periode ini</div>
                                    @endif
                                </td>

                                <!-- Materi -->
                                <td class="py-3.5 px-3 text-center">
                                    <div class="font-extrabold text-slate-800">{{ $row['jumlah_materi'] }}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5" title="Lampiran File / Video YouTube">
                                        <i class="fa-regular fa-file-lines"></i> {{ $row['materi_file'] }} · <i class="fa-brands fa-youtube"></i> {{ $row['materi_youtube'] }}
                                    </div>
                                </td>

                                <!-- Tugas -->
                                <td class="py-3.5 px-3 text-center">
                                    <div class="font-extrabold text-slate-800">{{ $row['jumlah_tugas'] }}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">{{ $row['total_pengumpulan_tugas'] }} kumpul</div>
                                </td>

                                <!-- Kuis -->
                                <td class="py-3.5 px-3 text-center">
                                    <div class="font-extrabold text-slate-800">{{ $row['jumlah_kuis_published'] }}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">
                                        @if($row['jumlah_kuis_draft'] > 0)
                                            +{{ $row['jumlah_kuis_draft'] }} draft
                                        @else
                                            published
                                        @endif
                                    </div>
                                </td>

                                <!-- Ujian -->
                                <td class="py-3.5 px-3 text-center">
                                    <div class="font-extrabold text-slate-800">{{ $row['jumlah_ujian_aktif'] }}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">
                                        @if($row['jumlah_ujian_draft'] > 0)
                                            +{{ $row['jumlah_ujian_draft'] }} draft
                                        @else
                                            aktif/selesai
                                        @endif
                                    </div>
                                </td>

                                <!-- Progres Koreksi -->
                                <td class="py-3.5 px-3 text-center">
                                    @if($row['tugas_menunggu_dinilai'] > 0 || $row['essay_menunggu_koreksi'] > 0)
                                        @if($row['tugas_menunggu_dinilai'] > 0)
                                            <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 border border-amber-200/70 text-[10px] font-bold">
                                                {{ $row['tugas_menunggu_dinilai'] }} tugas
                                            </div>
                                        @endif
                                        @if($row['essay_menunggu_koreksi'] > 0)
                                            <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 border border-rose-200/70 text-[10px] font-bold mt-0.5">
                                                {{ $row['essay_menunggu_koreksi'] }} essay
                                            </div>
                                        @endif
                                    @elseif($row['total_pengumpulan_tugas'] > 0)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 text-[10px] font-bold">
                                            <i class="fa-solid fa-check"></i> 100%
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400">—</span>
                                    @endif
                                </td>

                                <!-- Total Aktivitas -->
                                <td class="py-3.5 px-3 text-center bg-blue-50/30">
                                    <div class="text-base font-extrabold text-blue-700">{{ $row['total_aktivitas'] }}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">
                                        {{ $row['aktivitas_terakhir'] ? $row['aktivitas_terakhir']->format('d M Y') : 'Belum ada' }}
                                    </div>
                                </td>

                                <!-- Status Keaktifan -->
                                <td class="py-3.5 px-4 text-center">
                                    @if($row['kategori_keaktifan'] === 'sangat_aktif')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/70">
                                            <i class="fa-solid fa-star text-[10px]"></i> Sangat Aktif
                                        </span>
                                    @elseif($row['kategori_keaktifan'] === 'aktif')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200/70">
                                            <i class="fa-solid fa-check text-[10px]"></i> Aktif
                                        </span>
                                    @elseif($row['kategori_keaktifan'] === 'kurang_aktif')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200/70">
                                            <i class="fa-solid fa-clock text-[10px]"></i> Kurang Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200/70">
                                            <i class="fa-solid fa-triangle-exclamation text-[10px]"></i> Belum Aktif
                                        </span>
                                    @endif
                                </td>

                                <!-- Aksi -->
                                <td class="py-3.5 px-4 text-center">
                                    <a href="{{ route('admin.monitoring-guru.guru', ['guru' => $row['guru_id'], 'academic_period_id' => $selectedPeriodId]) }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-blue-50 text-blue-700 hover:bg-blue-600 hover:text-white transition-all border border-blue-100">
                                        <i class="fa-solid fa-eye text-[11px]"></i>
                                        <span>Detail</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>
@endsection
