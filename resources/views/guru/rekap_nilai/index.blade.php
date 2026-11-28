@extends('layouts.app')

@section('title', 'Rekap Nilai')

@section('content')
@php
    $queryParams = array_filter([
        'academic_period_id' => $selectedPeriodId,
        'kelas_id' => $selectedKelasId,
        'mapel_id' => $selectedMapelId,
        'status' => $selectedStatus !== 'semua' ? $selectedStatus : null,
        'search' => $search !== '' ? $search : null,
        'mode' => $mode !== 'ringkasan' ? $mode : null,
        'hitung_kosong' => $hitungKosong !== 'abaikan' ? $hitungKosong : null,
        'sort_by' => $sortBy !== 'peringkat' ? $sortBy : null,
        'sort_dir' => ($sortBy !== 'peringkat' || $sortDir !== 'asc') ? $sortDir : null,
        'per_page' => $perPage !== 25 ? $perPage : null,
    ], fn ($v) => $v !== null && $v !== '');

    $exportParams = array_filter([
        'academic_period_id' => $selectedPeriodId,
        'kelas_id' => $selectedKelasId,
        'mapel_id' => $selectedMapelId,
        'status' => $selectedStatus !== 'semua' ? $selectedStatus : null,
        'search' => $search !== '' ? $search : null,
        'mode' => $mode !== 'ringkasan' ? $mode : null,
        'hitung_kosong' => $hitungKosong !== 'abaikan' ? $hitungKosong : null,
        'sort_by' => $sortBy !== 'peringkat' ? $sortBy : null,
        'sort_dir' => ($sortBy !== 'peringkat' || $sortDir !== 'asc') ? $sortDir : null,
    ], fn ($v) => $v !== null && $v !== '');

    $detailQuery = array_filter(array_merge($queryParams, [
        'academic_period_id' => $selectedPeriodId,
        'page' => $rows->currentPage() > 1 ? $rows->currentPage() : null,
    ]), fn ($v) => $v !== null && $v !== '');

    $sortUrl = function (string $column, string $defaultDir = 'asc') use ($queryParams, $sortBy, $sortDir) {
        $nextDir = ($sortBy === $column)
            ? ($sortDir === 'asc' ? 'desc' : 'asc')
            : $defaultDir;

        return route('guru.rekap-nilai.index', array_merge($queryParams, [
            'sort_by' => $column,
            'sort_dir' => $nextDir,
            'page' => null,
        ]));
    };

    $sortIcon = function (string $column) use ($sortBy, $sortDir) {
        if ($sortBy !== $column) {
            return 'fa-sort text-slate-300';
        }

        return $sortDir === 'asc' ? 'fa-sort-up text-blue-600' : 'fa-sort-down text-blue-600';
    };

    $hasActiveFilter = $selectedStatus !== 'semua' || $search !== '' || $hitungKosong !== 'abaikan' || $sortBy !== 'peringkat' || $sortDir !== 'asc';

    $rekapPerMapelById = $rekapPerMapel->keyBy('mapel_id');
    $rekapPerKelasById = $rekapPerKelas->keyBy('kelas_id');
    $kelasByTingkatan = $kelasList
        ->groupBy(fn ($k) => filled($k->tingkatan) ? (string) $k->tingkatan : 'Lainnya')
        ->sortKeys(SORT_NATURAL);
@endphp

<div class="space-y-6 max-w-7xl mx-auto">

    <!-- Header & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Rekap Nilai</h1>
            <p class="text-sm text-slate-500 mt-1">
                Akumulasi nilai Tugas, Kuis, dan Ujian pada kelas &amp; mata pelajaran yang Anda ampu — Periode <strong class="text-slate-700">{{ $selectedPeriod?->label ?? 'Aktif' }}</strong>
                @if($selectedKelas)
                    • Kelas <strong class="text-slate-700">{{ $selectedKelas->nama_kelas }}</strong>
                @endif
                @if($selectedMapel)
                    • <strong class="text-slate-700">{{ $selectedMapel->nama_mapel }}</strong> (KKM {{ (int) $selectedMapel->kkm }})
                @endif
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('guru.rekap-nilai.export-csv', $exportParams) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200/80 text-xs font-bold rounded-xl shadow-2xs transition-all">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Export CSV</span>
            </a>
            <a href="{{ route('guru.rekap-nilai.export-pdf', $exportParams) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-500/20 transition-all hover:-translate-y-0.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Export PDF</span>
            </a>
        </div>
    </div>

    <!-- 4 Highlight Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Siswa (Klik untuk Semua Status) -->
        <a href="{{ route('guru.rekap-nilai.index', array_merge($queryParams, ['status' => null, 'page' => null])) }}"
           class="p-5 rounded-2xl transition-all duration-200 relative overflow-hidden group block {{ $selectedStatus === 'semua' ? 'bg-blue-50/40 border-2 border-blue-600 shadow-xs' : 'bg-white border border-slate-200/80 hover:border-blue-200 shadow-2xs hover:shadow-xs' }}">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider flex items-center gap-1.5 {{ $selectedStatus === 'semua' ? 'text-blue-700' : 'text-slate-400' }}">
                    @if($selectedStatus === 'semua')
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                    @endif
                    Total Siswa
                </span>
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ $summary['total_siswa'] }}</div>
            <div class="text-[11px] font-medium text-slate-500 mt-1">
                Dinilai: {{ $summary['siswa_dinilai'] }} • Belum ada: {{ $summary['jumlah_belum_ada'] }}
            </div>
        </a>

        <!-- Rata-rata Nilai -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-2xs relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Rata-Rata Nilai</span>
                <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                {{ $summary['rata_keseluruhan'] !== null ? number_format($summary['rata_keseluruhan'], 2) : '—' }}
            </div>
            <div class="text-[11px] font-medium text-slate-500 mt-1">
                Tgs {{ $summary['rata_tugas'] !== null ? number_format($summary['rata_tugas'], 1) : '-' }} • Kuis {{ $summary['rata_kuis'] !== null ? number_format($summary['rata_kuis'], 1) : '-' }} • Ujian {{ $summary['rata_ujian'] !== null ? number_format($summary['rata_ujian'], 1) : '-' }}
            </div>
        </div>

        <!-- Tingkat Ketuntasan (Klik untuk Filter Tuntas) -->
        <a href="{{ route('guru.rekap-nilai.index', array_merge($queryParams, ['status' => 'tuntas', 'page' => null])) }}"
           class="p-5 rounded-2xl transition-all duration-200 relative overflow-hidden group block {{ $selectedStatus === 'tuntas' ? 'bg-emerald-50/40 border-2 border-emerald-500 shadow-xs' : 'bg-white border border-slate-200/80 hover:border-emerald-200 shadow-2xs hover:shadow-xs' }}">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider flex items-center gap-1.5 {{ $selectedStatus === 'tuntas' ? 'text-emerald-700' : 'text-slate-400' }}">
                    @if($selectedStatus === 'tuntas')
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    @endif
                    Ketuntasan
                </span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ $summary['persen_tuntas'] }}%</div>
            <div class="text-[11px] font-medium text-slate-500 mt-1">
                <span class="text-emerald-700 font-semibold">{{ $summary['jumlah_tuntas'] }} Tuntas</span> • {{ $summary['jumlah_belum_tuntas'] }} Belum
            </div>
        </a>

        <!-- Belum Tuntas (Klik untuk Filter Belum Tuntas) -->
        <a href="{{ route('guru.rekap-nilai.index', array_merge($queryParams, ['status' => 'belum_tuntas', 'page' => null])) }}"
           class="p-5 rounded-2xl transition-all duration-200 relative overflow-hidden group block {{ $selectedStatus === 'belum_tuntas' ? 'bg-rose-50/40 border-2 border-rose-500 shadow-xs' : 'bg-white border border-slate-200/80 hover:border-rose-200 shadow-2xs hover:shadow-xs' }}">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider flex items-center gap-1.5 {{ $selectedStatus === 'belum_tuntas' ? 'text-rose-700' : 'text-slate-400' }}">
                    @if($selectedStatus === 'belum_tuntas')
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                    @endif
                    Belum Tuntas
                </span>
                <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ $summary['jumlah_belum_tuntas'] }}</div>
            <div class="text-[11px] font-medium text-slate-500 mt-1">
                Tertinggi: {{ $summary['nilai_tertinggi'] !== null ? number_format($summary['nilai_tertinggi'], 1) : '-' }} • Min: {{ $summary['nilai_terendah'] !== null ? number_format($summary['nilai_terendah'], 1) : '-' }}
            </div>
        </a>
    </div>

    <!-- Pilih Mata Pelajaran & Kelas (Sederhana & Bersih) -->
    @if($mapelList->isNotEmpty())
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 space-y-5 shadow-2xs">
            <!-- Pilih Mata Pelajaran -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Pilih Mata Pelajaran</h3>
                    <span class="text-xs text-slate-400">{{ $mapelList->count() }} Mapel</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-2.5">
                    @foreach($mapelList as $mapel)
                        @php
                            $isSelectedMapel = (int) $selectedMapelId === (int) $mapel->id;
                            $rm = $rekapPerMapelById->get($mapel->id);
                            $rataMapel = $rm ? $rm['rata_nilai'] : ($isSelectedMapel ? $summary['rata_keseluruhan'] : null);
                        @endphp
                        <a href="{{ route('guru.rekap-nilai.index', array_merge($queryParams, ['mapel_id' => $mapel->id, 'kelas_id' => null, 'page' => null])) }}"
                           class="flex items-center justify-between gap-2 px-3.5 py-3 rounded-xl border transition-colors {{ $isSelectedMapel ? 'bg-blue-600 border-blue-600 text-white' : 'bg-white border-slate-200 text-slate-800 hover:border-slate-300 hover:bg-slate-50' }}">
                            <div class="min-w-0">
                                <div class="text-xs font-bold truncate" title="{{ $mapel->nama_mapel }}">{{ $mapel->nama_mapel }}</div>
                                <div class="text-[11px] mt-0.5 {{ $isSelectedMapel ? 'text-blue-100' : 'text-slate-400' }}">
                                    KKM {{ (int) ($mapel->kkm ?? 75) }}
                                </div>
                            </div>
                            @if($rataMapel !== null)
                                <span class="text-xs font-bold tabular-nums shrink-0 {{ $isSelectedMapel ? 'text-white' : 'text-slate-600' }}">
                                    {{ number_format($rataMapel, 1) }}
                                </span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Pilih Kelas (Dipisahkan per Tingkatan) -->
            @if($kelasList->isNotEmpty())
                <div class="pt-4 border-t border-slate-100 space-y-3.5">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Pilih Kelas</h3>
                        <span class="text-xs text-slate-400">
                            {{ $kelasList->count() }} Kelas • {{ $selectedMapel?->nama_mapel }}
                        </span>
                    </div>

                    @foreach($kelasByTingkatan as $tingkatan => $kelasGroup)
                        <div>
                            <div class="text-[11px] font-semibold text-slate-400 mb-2">
                                {{ $tingkatan === 'Lainnya' ? 'Lainnya' : 'Tingkat ' . $tingkatan }}
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-2.5">
                                @foreach($kelasGroup as $kelas)
                                    @php
                                        $isSelectedKelas = (int) $selectedKelasId === (int) $kelas->id;
                                        $rk = $rekapPerKelasById->get($kelas->id);
                                    @endphp
                                    <a href="{{ route('guru.rekap-nilai.index', array_merge($queryParams, ['kelas_id' => $kelas->id, 'page' => null])) }}"
                                       class="flex items-center justify-between gap-2 px-3.5 py-3 rounded-xl border transition-colors {{ $isSelectedKelas ? 'bg-blue-600 border-blue-600 text-white' : 'bg-white border-slate-200 text-slate-800 hover:border-slate-300 hover:bg-slate-50' }}">
                                        <div class="min-w-0">
                                            <div class="text-xs font-bold truncate">{{ $kelas->nama_kelas }}</div>
                                            <div class="text-[11px] mt-0.5 {{ $isSelectedKelas ? 'text-blue-100' : 'text-slate-400' }}">
                                                @if($rk)
                                                    {{ $rk['total_siswa'] }} siswa
                                                @else
                                                    Tingkat {{ $kelas->tingkatan ?? '-' }}
                                                @endif
                                            </div>
                                        </div>
                                        @if($rk && $rk['rata_nilai'] !== null)
                                            <span class="text-xs font-bold tabular-nums shrink-0 {{ $isSelectedKelas ? 'text-white' : 'text-slate-600' }}">
                                                {{ number_format($rk['rata_nilai'], 1) }}
                                            </span>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    <!-- Filter & Search Card -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-2xs">
        <form method="GET" action="{{ route('guru.rekap-nilai.index') }}" class="space-y-4">
            <input type="hidden" name="mode" value="{{ $mode }}">
            <input type="hidden" name="per_page" value="{{ $perPage }}">
            @if($selectedKelasId)
                <input type="hidden" name="kelas_id" value="{{ $selectedKelasId }}">
            @endif
            @if($selectedMapelId)
                <input type="hidden" name="mapel_id" value="{{ $selectedMapelId }}">
            @endif
            <input type="hidden" name="sort_by" value="{{ $sortBy }}">
            <input type="hidden" name="sort_dir" value="{{ $sortDir }}">

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3">
                <!-- Cari Siswa -->
                <div class="lg:col-span-4 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" id="search" name="search" value="{{ $search }}"
                           placeholder="Cari nama siswa atau NIS..."
                           class="w-full pl-10 pr-8 py-2.5 text-xs rounded-xl border border-slate-200 bg-slate-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-slate-800">
                    @if($search !== '')
                        <a href="{{ route('guru.rekap-nilai.index', array_merge($queryParams, ['search' => null, 'page' => null])) }}"
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

                <!-- Status Ketuntasan -->
                <div class="lg:col-span-2">
                    <select id="status" name="status" onchange="this.form.submit()"
                            class="w-full py-2.5 px-3 text-xs rounded-xl border border-slate-200 bg-slate-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-slate-700 font-semibold">
                        <option value="semua" {{ $selectedStatus === 'semua' ? 'selected' : '' }}>Semua Status</option>
                        <option value="tuntas" {{ $selectedStatus === 'tuntas' ? 'selected' : '' }}>Tuntas ({{ $summary['jumlah_tuntas'] }})</option>
                        <option value="belum_tuntas" {{ $selectedStatus === 'belum_tuntas' ? 'selected' : '' }}>Belum Tuntas ({{ $summary['jumlah_belum_tuntas'] }})</option>
                        <option value="belum_ada" {{ $selectedStatus === 'belum_ada' ? 'selected' : '' }}>Belum Ada Nilai ({{ $summary['jumlah_belum_ada'] }})</option>
                    </select>
                </div>

                <!-- Item Belum Dinilai -->
                <div class="lg:col-span-2">
                    <select id="hitung_kosong" name="hitung_kosong" onchange="this.form.submit()"
                            class="w-full py-2.5 px-3 text-xs rounded-xl border border-slate-200 bg-slate-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-slate-700 font-semibold">
                        <option value="abaikan" {{ $hitungKosong === 'abaikan' ? 'selected' : '' }}>Kosong: Abaikan</option>
                        <option value="nol" {{ $hitungKosong === 'nol' ? 'selected' : '' }}>Kosong: Hitung 0</option>
                    </select>
                </div>

                <!-- Urutkan -->
                <div class="lg:col-span-2">
                    @php $currentSortCombo = $sortBy . ':' . $sortDir; @endphp
                    <select id="sort_preset"
                            onchange="const [by, dir] = this.value.split(':'); this.form.sort_by.value = by; this.form.sort_dir.value = dir; this.form.submit();"
                            class="w-full py-2.5 px-3 text-xs rounded-xl border border-slate-200 bg-slate-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-slate-700 font-semibold">
                        <option value="peringkat:asc" {{ in_array($currentSortCombo, ['peringkat:asc', 'nilai_akhir:desc'], true) ? 'selected' : '' }}>Nilai Tertinggi</option>
                        <option value="nilai_akhir:asc" {{ in_array($currentSortCombo, ['nilai_akhir:asc', 'peringkat:desc'], true) ? 'selected' : '' }}>Nilai Terendah</option>
                        <option value="nama_siswa:asc" {{ $currentSortCombo === 'nama_siswa:asc' ? 'selected' : '' }}>Nama (A–Z)</option>
                        <option value="nama_siswa:desc" {{ $currentSortCombo === 'nama_siswa:desc' ? 'selected' : '' }}>Nama (Z–A)</option>
                    </select>
                </div>
            </div>

            <!-- Baris Bawah: Tombol Aksi Filter -->
            <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
                <div class="text-xs text-slate-500">
                    @if($selectedMapel && $selectedKelas)
                        Menampilkan nilai <strong class="text-slate-700">{{ $selectedMapel->nama_mapel }}</strong> — Kelas <strong class="text-slate-700">{{ $selectedKelas->nama_kelas }}</strong> (KKM {{ (int) ($selectedMapel->kkm ?? 75) }})
                    @endif
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit"
                            class="py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-500/20 transition-all">
                        Filter
                    </button>
                    @if($hasActiveFilter)
                        <a href="{{ route('guru.rekap-nilai.index', array_filter(['academic_period_id' => $selectedPeriodId, 'mapel_id' => $selectedMapelId, 'kelas_id' => $selectedKelasId])) }}"
                           class="py-2 px-3.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition-colors">
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h2 class="text-sm font-extrabold text-slate-900">
                    @if($mode === 'leger')
                        Leger Nilai — Kelas {{ $selectedKelas->nama_kelas }}
                    @elseif($selectedMapel)
                        Rekap Nilai — {{ $selectedMapel->nama_mapel }} (KKM {{ (int) $selectedMapel->kkm }})
                    @elseif($selectedKelas)
                        Rekap Nilai — Kelas {{ $selectedKelas->nama_kelas }}
                    @else
                        Daftar Rekap Nilai Siswa Diampu
                    @endif
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Menampilkan <strong class="text-slate-700">{{ $rows->firstItem() ?? 0 }}–{{ $rows->lastItem() ?? 0 }}</strong>
                    dari <strong class="text-slate-700">{{ $filteredTotal }}</strong> siswa
                    @if($filteredTotal !== $allRowsCount)
                        (total {{ $allRowsCount }} siswa pada scope ini).
                    @else
                        — Ketuntasan mengacu pada KKM masing-masing mata pelajaran yang Anda ampu.
                    @endif
                    @if($hitungKosong === 'nol')
                        <span class="text-rose-600 font-semibold">Item belum dinilai dihitung 0.</span>
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-2 text-[11px] font-bold">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200/70">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Tuntas (≥ KKM)
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 border border-rose-200/70">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Belum Tuntas
                </span>
            </div>
        </div>

        @if($rows->isEmpty())
            <div class="p-12 text-center">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-slate-800">Data Nilai Tidak Ditemukan</h3>
                <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                    @if($kelasList->isEmpty())
                        Anda belum memiliki penugasan mata pelajaran &amp; kelas pada periode akademik ini.
                    @else
                        Belum ada siswa atau data nilai yang sesuai dengan filter yang dipilih.
                    @endif
                </p>
            </div>
        @elseif($mode === 'leger' && $legerMapels->isNotEmpty())
            <!-- MODE LEGER MATRIKS MAPEL -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="py-3.5 px-3 text-center w-14 sticky left-0 bg-slate-50 z-10">
                                <a href="{{ $sortUrl('peringkat', 'asc') }}" class="inline-flex items-center gap-1 hover:text-blue-600">
                                    Rank <i class="fa-solid {{ $sortIcon('peringkat') }} text-[10px]"></i>
                                </a>
                            </th>
                            <th class="py-3.5 px-4 min-w-[200px] sticky left-14 bg-slate-50 z-10 border-r border-slate-200/80">
                                <a href="{{ $sortUrl('nama_siswa', 'asc') }}" class="inline-flex items-center gap-1 hover:text-blue-600">
                                    Siswa <i class="fa-solid {{ $sortIcon('nama_siswa') }} text-[10px]"></i>
                                </a>
                            </th>
                            @foreach($legerMapels as $pk)
                                @php $m = $pk->guruMapel->mapel; @endphp
                                <th class="py-3 px-3 text-center min-w-[100px]">
                                    <div class="text-slate-700 leading-tight">{{ $m->nama_mapel }}</div>
                                    <div class="text-[10px] font-semibold text-amber-600 normal-case mt-0.5">KKM {{ (int) ($m->kkm ?? 75) }}</div>
                                </th>
                            @endforeach
                            <th class="py-3.5 px-3 text-center bg-blue-50/60 text-blue-700 min-w-[95px]">
                                <a href="{{ $sortUrl('nilai_akhir', 'desc') }}" class="inline-flex items-center gap-1 hover:text-blue-900">
                                    Rata-rata <i class="fa-solid {{ $sortIcon('nilai_akhir') }} text-[10px]"></i>
                                </a>
                            </th>
                            <th class="py-3.5 px-4 text-right pr-6 min-w-[90px]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @foreach($rows as $row)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3.5 px-3 text-center sticky left-0 bg-white z-10">
                                    @if($row['peringkat'] === 1)
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-100 text-amber-700 font-extrabold text-xs">1</span>
                                    @elseif($row['peringkat'] === 2)
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-200 text-slate-700 font-extrabold text-xs">2</span>
                                    @elseif($row['peringkat'] === 3)
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-orange-100 text-orange-700 font-extrabold text-xs">3</span>
                                    @else
                                        <span class="font-bold text-slate-500">{{ $row['peringkat'] ?? '—' }}</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 sticky left-14 bg-white z-10 border-r border-slate-100">
                                    <div class="font-bold text-slate-900 leading-snug">{{ $row['nama_siswa'] }}</div>
                                    <div class="text-[11px] text-slate-400">NIS: {{ $row['nis'] }}</div>
                                </td>
                                @foreach($legerMapels as $pk)
                                    @php
                                        $cell = $row['mapel_scores'][$pk->guruMapel->mapel_id] ?? null;
                                    @endphp
                                    <td class="py-3.5 px-3 text-center">
                                        @if(!$cell || !$cell['accessible'])
                                            <span class="text-[11px] font-medium text-slate-300" title="Mapel agama berbeda">N/A</span>
                                        @elseif($cell['nilai_akhir'] === null)
                                            <span class="text-slate-400">—</span>
                                        @else
                                            <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-md text-xs font-bold border {{ $cell['tuntas'] ? 'bg-emerald-50 text-emerald-700 border-emerald-200/70' : 'bg-rose-50 text-rose-700 border-rose-200/70' }}"
                                                  title="KKM {{ (int) $cell['kkm'] }} | Tugas: {{ $cell['rata_tugas'] ?? '-' }} | Kuis: {{ $cell['rata_kuis'] ?? '-' }} | Ujian: {{ $cell['rata_ujian'] ?? '-' }}">
                                                {{ number_format($cell['nilai_akhir'], 1) }}
                                            </span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="py-3.5 px-3 text-center bg-blue-50/30 font-extrabold text-slate-900">
                                    {{ $row['nilai_akhir'] !== null ? number_format($row['nilai_akhir'], 2) : '—' }}
                                </td>
                                <td class="py-3.5 px-4 text-right pr-6">
                                    <a href="{{ route('guru.rekap-nilai.siswa', array_merge(['siswa' => $row['siswa_id']], $detailQuery)) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-bold bg-blue-50 text-blue-700 hover:bg-blue-600 hover:text-white border border-blue-100 transition-colors">
                                        <span>Detail</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <!-- MODE RINGKASAN KOMPONEN -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="py-3.5 px-4 text-center w-16">
                                <a href="{{ $sortUrl('peringkat', 'asc') }}" class="inline-flex items-center gap-1 hover:text-blue-600">
                                    Rank <i class="fa-solid {{ $sortIcon('peringkat') }} text-[10px]"></i>
                                </a>
                            </th>
                            <th class="py-3.5 px-4 min-w-[210px]">
                                <a href="{{ $sortUrl('nama_siswa', 'asc') }}" class="inline-flex items-center gap-1 hover:text-blue-600">
                                    Siswa &amp; Kelas <i class="fa-solid {{ $sortIcon('nama_siswa') }} text-[10px]"></i>
                                </a>
                            </th>
                            <th class="py-3.5 px-4 text-center">
                                <a href="{{ $sortUrl('rata_tugas', 'desc') }}" class="inline-flex items-center gap-1 hover:text-blue-600">
                                    Rata Tugas <i class="fa-solid {{ $sortIcon('rata_tugas') }} text-[10px]"></i>
                                </a>
                            </th>
                            <th class="py-3.5 px-4 text-center">
                                <a href="{{ $sortUrl('rata_kuis', 'desc') }}" class="inline-flex items-center gap-1 hover:text-blue-600">
                                    Rata Kuis <i class="fa-solid {{ $sortIcon('rata_kuis') }} text-[10px]"></i>
                                </a>
                            </th>
                            <th class="py-3.5 px-4 text-center">
                                <a href="{{ $sortUrl('rata_ujian', 'desc') }}" class="inline-flex items-center gap-1 hover:text-blue-600">
                                    Rata Ujian <i class="fa-solid {{ $sortIcon('rata_ujian') }} text-[10px]"></i>
                                </a>
                            </th>
                            <th class="py-3.5 px-4 text-center bg-blue-50/60 text-blue-700">
                                <a href="{{ $sortUrl('nilai_akhir', 'desc') }}" class="inline-flex items-center gap-1 hover:text-blue-900">
                                    Nilai Akhir <i class="fa-solid {{ $sortIcon('nilai_akhir') }} text-[10px]"></i>
                                </a>
                            </th>
                            <th class="py-3.5 px-4 text-center">Ketuntasan</th>
                            <th class="py-3.5 px-4 text-right pr-6">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @foreach($rows as $row)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <!-- Peringkat -->
                                <td class="py-3.5 px-4 text-center">
                                    @if($row['peringkat'] === 1)
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-100 text-amber-700 font-extrabold text-xs" title="Peringkat 1">1</span>
                                    @elseif($row['peringkat'] === 2)
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-200 text-slate-700 font-extrabold text-xs">2</span>
                                    @elseif($row['peringkat'] === 3)
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-orange-100 text-orange-700 font-extrabold text-xs">3</span>
                                    @else
                                        <span class="font-bold text-slate-500">{{ $row['peringkat'] ?? '—' }}</span>
                                    @endif
                                </td>

                                <!-- Siswa -->
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900 leading-snug">{{ $row['nama_siswa'] }}</div>
                                    <div class="flex flex-wrap items-center gap-1.5 mt-0.5">
                                        <span class="text-[11px] text-slate-400">NIS: {{ $row['nis'] }}</span>
                                        <span class="text-slate-300">•</span>
                                        <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[10px] font-bold">
                                            {{ $row['nama_kelas'] }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Tugas -->
                                <td class="py-3.5 px-4 text-center">
                                    @if($row['rata_tugas'] !== null)
                                        <div class="font-bold text-slate-800">{{ number_format($row['rata_tugas'], 2) }}</div>
                                    @else
                                        <div class="text-slate-400">—</div>
                                    @endif
                                    <div class="text-[10px] text-slate-400 mt-0.5">{{ $row['tugas_dinilai'] }}/{{ $row['tugas_total'] }} dinilai</div>
                                </td>

                                <!-- Kuis -->
                                <td class="py-3.5 px-4 text-center">
                                    @if($row['rata_kuis'] !== null)
                                        <div class="font-bold text-slate-800">{{ number_format($row['rata_kuis'], 2) }}</div>
                                    @else
                                        <div class="text-slate-400">—</div>
                                    @endif
                                    <div class="text-[10px] text-slate-400 mt-0.5">{{ $row['kuis_dinilai'] }}/{{ $row['kuis_total'] }} selesai</div>
                                </td>

                                <!-- Ujian -->
                                <td class="py-3.5 px-4 text-center">
                                    @if($row['rata_ujian'] !== null)
                                        <div class="font-bold text-slate-800">{{ number_format($row['rata_ujian'], 2) }}</div>
                                    @else
                                        <div class="text-slate-400">—</div>
                                    @endif
                                    <div class="text-[10px] text-slate-400 mt-0.5">{{ $row['ujian_dinilai'] }}/{{ $row['ujian_total'] }} selesai</div>
                                </td>

                                <!-- Nilai Akhir -->
                                <td class="py-3.5 px-4 text-center bg-blue-50/30">
                                    @if($row['nilai_akhir'] !== null)
                                        <span class="text-sm font-extrabold {{ $row['status_ketuntasan'] === 'tuntas' ? 'text-emerald-700' : 'text-rose-600' }}">
                                            {{ number_format($row['nilai_akhir'], 2) }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>

                                <!-- Ketuntasan -->
                                <td class="py-3.5 px-4 text-center">
                                    @if($row['status_ketuntasan'] === 'tuntas')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                                            Tuntas
                                        </span>
                                    @elseif($row['status_ketuntasan'] === 'belum_tuntas')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200/80">
                                            Belum Tuntas
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-500">
                                            Belum Ada Nilai
                                        </span>
                                    @endif

                                    @if(!$selectedMapel && $row['mapel_wajib'] > 0)
                                        <div class="text-[10px] text-slate-400 mt-1">
                                            {{ $row['mapel_tuntas'] }}/{{ $row['mapel_wajib'] }} Mapel Tuntas
                                        </div>
                                        @if(!empty($row['mapel_belum_tuntas_list']))
                                            @php
                                                $belumList = $row['mapel_belum_tuntas_list'];
                                                $preview = array_slice($belumList, 0, 2);
                                                $sisa = count($belumList) - count($preview);
                                            @endphp
                                            <div class="text-[10px] text-rose-600 font-medium mt-0.5 max-w-[170px] mx-auto truncate"
                                                 title="Belum tuntas: {{ implode(', ', $belumList) }}">
                                                &lt; KKM: {{ implode(', ', $preview) }}@if($sisa > 0) +{{ $sisa }}@endif
                                            </div>
                                        @endif
                                    @endif
                                </td>

                                <!-- Aksi -->
                                <td class="py-3.5 px-4 text-right pr-6">
                                    <a href="{{ route('guru.rekap-nilai.siswa', array_merge(['siswa' => $row['siswa_id']], $detailQuery)) }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-blue-50 text-blue-700 hover:bg-blue-600 hover:text-white transition-all border border-blue-100">
                                        <span>Transkrip</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Footer Pagination & Per Page -->
        @if($filteredTotal > 0)
            <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50/40 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
                <div class="flex items-center gap-2">
                    <span>Baris per halaman:</span>
                    <form method="GET" action="{{ route('guru.rekap-nilai.index') }}">
                        @foreach($queryParams as $k => $v)
                            @if($k !== 'per_page')
                                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                            @endif
                        @endforeach
                        <select name="per_page" onchange="this.form.submit()"
                                class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-bold text-slate-700 focus:border-blue-500 focus:outline-none">
                            @foreach([15, 25, 50, 100] as $opt)
                                <option value="{{ $opt }}" {{ (int) $perPage === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>

                @if($rows->hasPages())
                    <div class="flex items-center gap-1 flex-wrap justify-center">
                        @if($rows->onFirstPage())
                            <span class="w-8 h-8 inline-flex items-center justify-center rounded-lg border border-slate-200/70 text-slate-300 cursor-not-allowed">
                                <i class="fa-solid fa-chevron-left text-[10px]"></i>
                            </span>
                        @else
                            <a href="{{ $rows->previousPageUrl() }}"
                               class="w-8 h-8 inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition-colors">
                                <i class="fa-solid fa-chevron-left text-[10px]"></i>
                            </a>
                        @endif

                        @php
                            $startPage = max(1, $rows->currentPage() - 2);
                            $endPage = min($rows->lastPage(), $rows->currentPage() + 2);
                        @endphp

                        @if($startPage > 1)
                            <a href="{{ $rows->url(1) }}" class="w-8 h-8 inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 font-semibold">1</a>
                            @if($startPage > 2)
                                <span class="px-1 text-slate-400">…</span>
                            @endif
                        @endif

                        @for($p = $startPage; $p <= $endPage; $p++)
                            @if($p === $rows->currentPage())
                                <span class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-blue-600 text-white font-bold shadow-xs">{{ $p }}</span>
                            @else
                                <a href="{{ $rows->url($p) }}" class="w-8 h-8 inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 font-semibold">{{ $p }}</a>
                            @endif
                        @endfor

                        @if($endPage < $rows->lastPage())
                            @if($endPage < $rows->lastPage() - 1)
                                <span class="px-1 text-slate-400">…</span>
                            @endif
                            <a href="{{ $rows->url($rows->lastPage()) }}" class="w-8 h-8 inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 font-semibold">{{ $rows->lastPage() }}</a>
                        @endif

                        @if($rows->hasMorePages())
                            <a href="{{ $rows->nextPageUrl() }}"
                               class="w-8 h-8 inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition-colors">
                                <i class="fa-solid fa-chevron-right text-[10px]"></i>
                            </a>
                        @else
                            <span class="w-8 h-8 inline-flex items-center justify-center rounded-lg border border-slate-200/70 text-slate-300 cursor-not-allowed">
                                <i class="fa-solid fa-chevron-right text-[10px]"></i>
                            </span>
                        @endif
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
