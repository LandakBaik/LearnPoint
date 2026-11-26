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

    $hasActiveFilter = $selectedKelasId || $selectedMapelId || $selectedKeaktifan !== 'semua' || $search !== '';
@endphp

<div class="space-y-6 max-w-7xl mx-auto">

    <!-- Header & Action Buttons (Konsisten dengan Gaya Admin) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Monitoring Aktivitas Guru</h1>
            <p class="text-sm text-slate-500 mt-1">
                Keaktifan guru pada Materi, Tugas, Kuis, Ujian, serta progres koreksi — Periode <strong class="text-slate-700">{{ $selectedPeriod?->label ?? 'Aktif' }}</strong>
                @if($selectedKelas)
                    • Kelas <strong class="text-slate-700">{{ $selectedKelas->nama_kelas }}</strong>
                @endif
                @if($selectedMapel)
                    • <strong class="text-slate-700">{{ $selectedMapel->nama_mapel }}</strong>
                @endif
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.monitoring-guru.export-csv', $queryParams) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200/80 text-xs font-bold rounded-xl shadow-2xs transition-all">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Export CSV</span>
            </a>
            <a href="{{ route('admin.monitoring-guru.export-pdf', $queryParams) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-500/20 transition-all hover:-translate-y-0.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Export PDF</span>
            </a>
        </div>
    </div>

    <!-- 4 Highlight Stat Cards (Konsisten dengan Gaya Admin) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Guru (Klik untuk Semua Tingkat) -->
        <a href="{{ route('admin.monitoring-guru.index', array_merge($queryParams, ['keaktifan' => null])) }}"
           class="p-5 rounded-2xl transition-all duration-200 relative overflow-hidden group block {{ $selectedKeaktifan === 'semua' ? 'bg-blue-50/40 border-2 border-blue-600 shadow-xs' : 'bg-white border border-slate-200/80 hover:border-blue-200 shadow-2xs hover:shadow-xs' }}">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider flex items-center gap-1.5 {{ $selectedKeaktifan === 'semua' ? 'text-blue-700' : 'text-slate-400' }}">
                    @if($selectedKeaktifan === 'semua')
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                    @endif
                    Total Guru
                </span>
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ $summary['total_guru'] }}</div>
            <div class="text-[11px] font-medium text-slate-500 mt-1">
                <span class="text-emerald-700 font-semibold">Aktif: {{ $summary['guru_aktif'] }}</span> • <span class="text-rose-600 font-semibold">Belum: {{ $summary['guru_belum_aktif'] }}</span>
            </div>
        </a>

        <!-- Materi & Tugas -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-2xs relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Materi &amp; Tugas</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ $summary['total_materi'] + $summary['total_tugas'] }}</div>
            <div class="text-[11px] font-medium text-slate-500 mt-1">
                Materi: {{ $summary['total_materi'] }} • Tugas: {{ $summary['total_tugas'] }}
            </div>
        </div>

        <!-- Kuis & Ujian Aktif -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-2xs relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Kuis &amp; Ujian</span>
                <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ $summary['total_kuis_published'] + $summary['total_ujian_aktif'] }}</div>
            <div class="text-[11px] font-medium text-slate-500 mt-1">
                Kuis: {{ $summary['total_kuis_published'] }}/{{ $summary['total_kuis_all'] }} • Ujian: {{ $summary['total_ujian_aktif'] }}/{{ $summary['total_ujian_all'] }}
            </div>
        </div>

        <!-- Progres Koreksi -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-2xs relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Koreksi Tugas</span>
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ $summary['persen_koreksi_tugas'] }}%</div>
            <div class="text-[11px] font-medium text-slate-500 mt-1">
                Menunggu: <span class="{{ $summary['tugas_menunggu_dinilai'] > 0 ? 'text-amber-700 font-semibold' : '' }}">{{ $summary['tugas_menunggu_dinilai'] }}</span> • Essay: <span class="{{ $summary['essay_menunggu_koreksi'] > 0 ? 'text-rose-600 font-semibold' : '' }}">{{ $summary['essay_menunggu_koreksi'] }}</span>
            </div>
        </div>
    </div>

    <!-- Filter & Search Card (Konsisten dengan Gaya Admin) -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-2xs">
        <form method="GET" action="{{ route('admin.monitoring-guru.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3">
                <!-- Cari Guru -->
                <div class="lg:col-span-4 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" id="search" name="search" value="{{ $search }}"
                           placeholder="Cari nama guru atau NUPTK..."
                           class="w-full pl-10 pr-8 py-2.5 text-xs rounded-xl border border-slate-200 bg-slate-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-slate-800">
                    @if($search !== '')
                        <a href="{{ route('admin.monitoring-guru.index', array_merge($queryParams, ['search' => null])) }}"
                           class="text-slate-400 hover:text-slate-600 absolute right-2.5 top-1/2 -translate-y-1/2 p-1"
                           title="Hapus pencarian">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </a>
                    @endif
                </div>

                <!-- Periode Akademik -->
                <div class="lg:col-span-2">
                    <select id="academic_period_id" name="academic_period_id" onchange="this.form.submit()"
                            class="w-full py-2.5 px-3 text-xs rounded-xl border border-slate-200 bg-slate-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-slate-700 font-semibold">
                        @foreach($periods as $period)
                            <option value="{{ $period->id }}" {{ (int) $selectedPeriodId === (int) $period->id ? 'selected' : '' }}>
                                {{ $period->label }} {{ $period->is_active ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Mata Pelajaran -->
                <div class="lg:col-span-2">
                    <select id="mapel_id" name="mapel_id" onchange="this.form.submit()"
                            class="w-full py-2.5 px-3 text-xs rounded-xl border border-slate-200 bg-slate-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-slate-700 font-semibold">
                        <option value="">Semua Mapel</option>
                        @foreach($mapelList as $mapel)
                            <option value="{{ $mapel->id }}" {{ (int) $selectedMapelId === (int) $mapel->id ? 'selected' : '' }}>
                                {{ $mapel->nama_mapel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Kelas Diampu -->
                <div class="lg:col-span-2">
                    <select id="kelas_id" name="kelas_id" onchange="this.form.submit()"
                            class="w-full py-2.5 px-3 text-xs rounded-xl border border-slate-200 bg-slate-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-slate-700 font-semibold">
                        <option value="">Semua Kelas</option>
                        @foreach($kelasList as $kelas)
                            <option value="{{ $kelas->id }}" {{ (int) $selectedKelasId === (int) $kelas->id ? 'selected' : '' }}>
                                {{ $kelas->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Tingkat Keaktifan -->
                <div class="lg:col-span-2">
                    <select id="keaktifan" name="keaktifan" onchange="this.form.submit()"
                            class="w-full py-2.5 px-3 text-xs rounded-xl border border-slate-200 bg-slate-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-slate-700 font-semibold">
                        <option value="semua" {{ $selectedKeaktifan === 'semua' ? 'selected' : '' }}>Semua Tingkat</option>
                        <option value="sangat_aktif" {{ $selectedKeaktifan === 'sangat_aktif' ? 'selected' : '' }}>Sangat Aktif ({{ $summary['guru_sangat_aktif'] }})</option>
                        <option value="aktif" {{ $selectedKeaktifan === 'aktif' ? 'selected' : '' }}>Aktif ({{ $summary['guru_aktif'] - $summary['guru_sangat_aktif'] }})</option>
                        <option value="kurang_aktif" {{ $selectedKeaktifan === 'kurang_aktif' ? 'selected' : '' }}>Kurang Aktif ({{ $summary['guru_kurang_aktif'] }})</option>
                        <option value="belum_aktif" {{ $selectedKeaktifan === 'belum_aktif' ? 'selected' : '' }}>Belum Aktif ({{ $summary['guru_belum_aktif'] }})</option>
                    </select>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-2 text-[11px] text-slate-500">
                    <span class="font-bold text-slate-600">Indikator:</span>
                    <span class="px-2 py-0.5 rounded-md bg-emerald-50 border border-emerald-200/70 text-emerald-700 font-bold">Sangat Aktif (≥10)</span>
                    <span class="px-2 py-0.5 rounded-md bg-blue-50 border border-blue-200/70 text-blue-700 font-bold">Aktif (≥3)</span>
                    <span class="px-2 py-0.5 rounded-md bg-amber-50 border border-amber-200/70 text-amber-700 font-bold">Kurang Aktif (1–2)</span>
                    <span class="px-2 py-0.5 rounded-md bg-rose-50 border border-rose-200/70 text-rose-700 font-bold">Belum Aktif (0)</span>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit"
                            class="py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-500/20 transition-all">
                        Filter
                    </button>
                    @if($hasActiveFilter)
                        <a href="{{ route('admin.monitoring-guru.index', ['academic_period_id' => $selectedPeriodId]) }}"
                           class="py-2 px-3.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition-colors">
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Table Card (Konsisten dengan Gaya Admin) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h2 class="text-sm font-extrabold text-slate-900">Rekap Aktivitas Pembelajaran Per Guru</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Menampilkan <strong class="text-slate-700">{{ $rows->count() }}</strong> dari {{ $allRowsCount }} guru, diurutkan dari total aktivitas tertinggi.
                </p>
            </div>
        </div>

        @if($rows->isEmpty())
            <div class="p-12 text-center">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-slate-800">Data Guru Tidak Ditemukan</h3>
                <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                    Tidak ada guru yang cocok dengan filter periode, mata pelajaran, kelas, atau tingkat keaktifan yang dipilih.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="py-3.5 px-4 text-center w-12">No</th>
                            <th class="py-3.5 px-4 min-w-[220px]">Guru &amp; Mapel Diampu</th>
                            <th class="py-3.5 px-3 text-center">Materi</th>
                            <th class="py-3.5 px-3 text-center">Tugas</th>
                            <th class="py-3.5 px-3 text-center">Kuis</th>
                            <th class="py-3.5 px-3 text-center">Ujian</th>
                            <th class="py-3.5 px-3 text-center">Koreksi</th>
                            <th class="py-3.5 px-3 text-center bg-blue-50/60 text-blue-700">Total</th>
                            <th class="py-3.5 px-4 text-center">Keaktifan</th>
                            <th class="py-3.5 px-4 text-right pr-6">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @foreach($rows as $i => $row)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3.5 px-4 text-center font-semibold text-slate-500">{{ $i + 1 }}</td>

                                <!-- Guru & Mapel -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-bold text-slate-900 leading-snug">{{ $row['nama_guru'] }}</span>
                                        @if($row['wali_kelas'])
                                            <span class="px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 text-[10px] font-bold border border-indigo-200/70" title="Wali Kelas">
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
                                    <div class="font-bold text-slate-800">{{ $row['jumlah_materi'] }}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5" title="Lampiran File / Video YouTube">
                                        <i class="fa-regular fa-file-lines"></i> {{ $row['materi_file'] }} · <i class="fa-brands fa-youtube"></i> {{ $row['materi_youtube'] }}
                                    </div>
                                </td>

                                <!-- Tugas -->
                                <td class="py-3.5 px-3 text-center">
                                    <div class="font-bold text-slate-800">{{ $row['jumlah_tugas'] }}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">{{ $row['total_pengumpulan_tugas'] }} kumpul</div>
                                </td>

                                <!-- Kuis -->
                                <td class="py-3.5 px-3 text-center">
                                    <div class="font-bold text-slate-800">{{ $row['jumlah_kuis_published'] }}</div>
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
                                    <div class="font-bold text-slate-800">{{ $row['jumlah_ujian_aktif'] }}</div>
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
                                            <div class="inline-flex items-center px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 border border-amber-200/80 text-[10px] font-bold">
                                                {{ $row['tugas_menunggu_dinilai'] }} tugas
                                            </div>
                                        @endif
                                        @if($row['essay_menunggu_koreksi'] > 0)
                                            <div class="inline-flex items-center px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 border border-rose-200/80 text-[10px] font-bold mt-0.5">
                                                {{ $row['essay_menunggu_koreksi'] }} essay
                                            </div>
                                        @endif
                                    @elseif($row['total_pengumpulan_tugas'] > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200/70 text-[10px] font-bold">
                                            100%
                                        </span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>

                                <!-- Total Aktivitas -->
                                <td class="py-3.5 px-3 text-center bg-blue-50/30">
                                    <div class="text-sm font-extrabold text-blue-700">{{ $row['total_aktivitas'] }}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">
                                        {{ $row['aktivitas_terakhir'] ? $row['aktivitas_terakhir']->format('d M Y') : 'Belum ada' }}
                                    </div>
                                </td>

                                <!-- Status Keaktifan -->
                                <td class="py-3.5 px-4 text-center">
                                    @if($row['kategori_keaktifan'] === 'sangat_aktif')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                                            Sangat Aktif
                                        </span>
                                    @elseif($row['kategori_keaktifan'] === 'aktif')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200/80">
                                            Aktif
                                        </span>
                                    @elseif($row['kategori_keaktifan'] === 'kurang_aktif')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200/80">
                                            Kurang Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200/80">
                                            Belum Aktif
                                        </span>
                                    @endif
                                </td>

                                <!-- Aksi -->
                                <td class="py-3.5 px-4 text-right pr-6">
                                    <a href="{{ route('admin.monitoring-guru.guru', ['guru' => $row['guru_id'], 'academic_period_id' => $selectedPeriodId]) }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-blue-50 text-blue-700 hover:bg-blue-600 hover:text-white transition-all border border-blue-100">
                                        <span>Detail</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
