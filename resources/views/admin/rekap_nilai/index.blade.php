@extends('layouts.app')

@section('title', 'Perekapan Nilai Keseluruhan')

@section('content')
@php
    $queryParams = array_filter([
        'academic_period_id' => $selectedPeriodId,
        'kelas_id' => $selectedKelasId,
        'mapel_id' => $selectedMapelId,
        'status' => $selectedStatus !== 'semua' ? $selectedStatus : null,
        'search' => $search !== '' ? $search : null,
        'mode' => $mode !== 'ringkasan' ? $mode : null,
    ], fn ($v) => $v !== null && $v !== '');

    $canUseLeger = $selectedKelas && !$selectedMapel;
@endphp

<div class="space-y-6 max-w-7xl mx-auto">
    <!-- Header Banner -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-700 via-blue-600 to-indigo-700 text-white p-6 sm:p-8 shadow-md">
        <div class="absolute -right-10 -top-12 w-64 h-64 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
        <div class="absolute right-44 -bottom-12 w-48 h-48 rounded-full bg-indigo-400/20 blur-xl pointer-events-none"></div>

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
                        <span>{{ $selectedMapel->nama_mapel }} (KKM {{ (int) ($selectedMapel->kkm ?? 75) }})</span>
                    @endif
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight leading-tight">
                    Perekapan Nilai Keseluruhan
                </h1>
                <p class="text-blue-100 text-sm leading-relaxed">
                    Pantau dan analisis akumulasi nilai <strong>Tugas</strong>, <strong>Kuis</strong>, dan <strong>Ujian</strong> seluruh siswa di semua kelas dan mata pelajaran secara terpusat.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <a href="{{ route('admin.rekap-nilai.export-csv', $queryParams) }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/15 hover:bg-white/25 border border-white/25 text-white text-sm font-semibold transition-all shadow-xs">
                    <i class="fa-solid fa-file-csv text-base"></i>
                    <span>Export CSV</span>
                </a>
                <a href="{{ route('admin.rekap-nilai.export-pdf', $queryParams) }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white text-blue-700 hover:bg-blue-50 text-sm font-bold transition-all shadow-sm">
                    <i class="fa-solid fa-file-pdf text-base text-rose-600"></i>
                    <span>Export PDF</span>
                </a>
            </div>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Siswa -->
        <div class="bg-white border border-blue-100 rounded-2xl p-5 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-blue-600"></div>
            <div class="flex items-start justify-between gap-3">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-blue-700">Total Siswa</span>
                    <div class="text-2xl font-extrabold text-slate-900 mt-1">{{ $summary['total_siswa'] }}</div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100 shrink-0">
                    <i class="fa-solid fa-users text-lg"></i>
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Sudah dinilai: <strong class="text-slate-700">{{ $summary['siswa_dinilai'] }}</strong></span>
                <span>Belum ada: <strong class="text-slate-700">{{ $summary['jumlah_belum_ada'] }}</strong></span>
            </div>
        </div>

        <!-- Card 2: Rata-rata Keseluruhan -->
        <div class="bg-white border border-indigo-100 rounded-2xl p-5 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-indigo-600"></div>
            <div class="flex items-start justify-between gap-3">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-700">Rata-rata Nilai</span>
                    <div class="text-2xl font-extrabold text-slate-900 mt-1">
                        {{ $summary['rata_keseluruhan'] !== null ? number_format($summary['rata_keseluruhan'], 2) : '—' }}
                    </div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100 shrink-0">
                    <i class="fa-solid fa-chart-line text-lg"></i>
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between gap-2 text-[11px] text-slate-500">
                <span>Tgs: <strong class="text-slate-700">{{ $summary['rata_tugas'] !== null ? number_format($summary['rata_tugas'], 1) : '-' }}</strong></span>
                <span>Kuis: <strong class="text-slate-700">{{ $summary['rata_kuis'] !== null ? number_format($summary['rata_kuis'], 1) : '-' }}</strong></span>
                <span>Ujian: <strong class="text-slate-700">{{ $summary['rata_ujian'] !== null ? number_format($summary['rata_ujian'], 1) : '-' }}</strong></span>
            </div>
        </div>

        <!-- Card 3: Tingkat Ketuntasan -->
        <div class="bg-white border border-emerald-100 rounded-2xl p-5 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-emerald-600"></div>
            <div class="flex items-start justify-between gap-3">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Tingkat Ketuntasan</span>
                    <div class="text-2xl font-extrabold text-slate-900 mt-1">{{ $summary['persen_tuntas'] }}%</div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100 shrink-0">
                    <i class="fa-solid fa-circle-check text-lg"></i>
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span class="text-emerald-700 font-semibold">Tuntas: {{ $summary['jumlah_tuntas'] }}</span>
                <span class="text-rose-600 font-semibold">Belum: {{ $summary['jumlah_belum_tuntas'] }}</span>
            </div>
        </div>

        <!-- Card 4: Nilai Tertinggi & Terendah -->
        <div class="bg-white border border-amber-100 rounded-2xl p-5 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-amber-500"></div>
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-amber-700">Nilai Tertinggi</span>
                    <div class="text-2xl font-extrabold text-slate-900 mt-1">
                        {{ $summary['nilai_tertinggi'] !== null ? number_format($summary['nilai_tertinggi'], 2) : '—' }}
                    </div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-100 shrink-0">
                    <i class="fa-solid fa-trophy text-lg"></i>
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between gap-2 text-xs text-slate-500">
                <span class="truncate" title="{{ $summary['siswa_tertinggi'] ?? '-' }}">
                    <i class="fa-solid fa-crown text-amber-500 mr-1"></i>{{ $summary['siswa_tertinggi'] ?? 'Belum ada' }}
                </span>
                <span class="shrink-0 text-slate-400">
                    Min: <strong class="text-slate-600">{{ $summary['nilai_terendah'] !== null ? number_format($summary['nilai_terendah'], 1) : '-' }}</strong>
                </span>
            </div>
        </div>
    </section>

    <!-- Filter Panel -->
    <section class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5">
        <form method="GET" action="{{ route('admin.rekap-nilai.index') }}" class="space-y-4">
            <input type="hidden" name="mode" value="{{ $mode }}">

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

                <!-- Kelas -->
                <div>
                    <label for="kelas_id" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Kelas
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
                                {{ $mapel->nama_mapel }} (KKM {{ (int) ($mapel->kkm ?? 75) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Ketuntasan -->
                <div>
                    <label for="status" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Status Ketuntasan
                    </label>
                    <select id="status" name="status" onchange="this.form.submit()"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-2.5 text-sm font-medium text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                        <option value="semua" {{ $selectedStatus === 'semua' ? 'selected' : '' }}>Semua Status</option>
                        <option value="tuntas" {{ $selectedStatus === 'tuntas' ? 'selected' : '' }}>Tuntas ({{ $summary['jumlah_tuntas'] }})</option>
                        <option value="belum_tuntas" {{ $selectedStatus === 'belum_tuntas' ? 'selected' : '' }}>Belum Tuntas ({{ $summary['jumlah_belum_tuntas'] }})</option>
                        <option value="belum_ada" {{ $selectedStatus === 'belum_ada' ? 'selected' : '' }}>Belum Ada Nilai ({{ $summary['jumlah_belum_ada'] }})</option>
                    </select>
                </div>

                <!-- Pencarian -->
                <div>
                    <label for="search" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Cari Siswa
                    </label>
                    <div class="relative">
                        <input type="text" id="search" name="search" value="{{ $search }}"
                               placeholder="Nama / NIS..."
                               class="w-full rounded-xl border border-slate-200 bg-slate-50/70 pl-9 pr-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                        <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs absolute left-3 top-1/2 -translate-y-1/2"></i>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-slate-100">
                <!-- Mode Tampilan Tabs -->
                <div class="flex items-center gap-2">
                    @php
                        $ringkasanUrl = route('admin.rekap-nilai.index', array_merge($queryParams, ['mode' => 'ringkasan']));
                        $legerUrl = route('admin.rekap-nilai.index', array_merge($queryParams, ['mode' => 'leger']));
                    @endphp
                    <a href="{{ $ringkasanUrl }}"
                       class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all {{ $mode === 'ringkasan' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        <i class="fa-solid fa-table-list"></i>
                        <span>Ringkasan Komponen</span>
                    </a>

                    @if($canUseLeger)
                        <a href="{{ $legerUrl }}"
                           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all {{ $mode === 'leger' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            <i class="fa-solid fa-table-cells"></i>
                            <span>Leger Matriks Mapel</span>
                        </a>
                    @else
                        <span class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-50 text-slate-400 border border-slate-200/70 cursor-not-allowed"
                              title="Pilih salah satu Kelas (dengan Semua Mata Pelajaran) untuk membuka tampilan Leger Matriks Mapel">
                            <i class="fa-solid fa-table-cells"></i>
                            <span>Leger Matriks Mapel</span>
                            <span class="text-[10px] font-medium">(Pilih 1 Kelas)</span>
                        </span>
                    @endif
                </div>

                <!-- Action buttons -->
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.rekap-nilai.index') }}"
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

    <!-- Ringkasan Per Kelas (muncul saat Semua Kelas dipilih) -->
    @if(!$selectedKelas && $rekapPerKelas->isNotEmpty())
        <section class="space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider">Performa Rata-rata Per Kelas</h2>
                    <p class="text-xs text-slate-500">Klik kartu kelas untuk langsung melihat detail atau leger nilai kelas tersebut.</p>
                </div>
                <span class="text-xs font-semibold text-slate-500">{{ $rekapPerKelas->count() }} Kelas</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                @foreach($rekapPerKelas as $rk)
                    <a href="{{ route('admin.rekap-nilai.index', array_merge($queryParams, ['kelas_id' => $rk['kelas_id'], 'mode' => $selectedMapel ? 'ringkasan' : 'leger'])) }}"
                       class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs hover:shadow-md hover:border-blue-300 transition-all group">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-sm font-extrabold text-slate-900 group-hover:text-blue-600 transition-colors">
                                {{ $rk['nama_kelas'] }}
                            </span>
                            <span class="px-2 py-0.5 rounded-lg text-xs font-extrabold {{ $rk['rata_nilai'] === null ? 'bg-slate-100 text-slate-500' : ($rk['rata_nilai'] >= $rk['rata_kkm'] ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : 'bg-rose-50 text-rose-700 border border-rose-200/60') }}">
                                {{ $rk['rata_nilai'] !== null ? number_format($rk['rata_nilai'], 1) : '—' }}
                            </span>
                        </div>
                        <div class="mt-2.5 flex items-center justify-between text-xs text-slate-500">
                            <span>{{ $rk['total_siswa'] }} Siswa</span>
                            <span class="font-semibold text-emerald-700">{{ $rk['persen_tuntas'] }}% Tuntas</span>
                        </div>
                        <div class="mt-2 w-full h-1.5 rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full rounded-full bg-gradient-to-r from-blue-500 to-emerald-500"
                                 style="width: {{ min(100, max(0, $rk['persen_tuntas'])) }}%"></div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <!-- Tabel Utama Rekap Nilai -->
    <section class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h2 class="text-base font-extrabold text-slate-900">
                    @if($mode === 'leger')
                        Leger Nilai Keseluruhan — Kelas {{ $selectedKelas->nama_kelas }}
                    @elseif($selectedMapel)
                        Rekap Nilai Mata Pelajaran {{ $selectedMapel->nama_mapel }}
                    @elseif($selectedKelas)
                        Rekap Nilai Keseluruhan — Kelas {{ $selectedKelas->nama_kelas }}
                    @else
                        Daftar Rekap Nilai Seluruh Siswa
                    @endif
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Menampilkan <strong class="text-slate-700">{{ $rows->count() }}</strong> dari {{ $allRowsCount }} siswa.
                    Nilai Akhir dihitung dari rata-rata komponen yang telah dinilai (Tugas, Kuis, Ujian).
                </p>
            </div>
            <div class="flex items-center gap-2 text-[11px] font-semibold">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Tuntas (≥ KKM)
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 border border-rose-200/60">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span> Belum Tuntas
                </span>
            </div>
        </div>

        @if($rows->isEmpty())
            <div class="p-12 text-center">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mb-4">
                    <i class="fa-solid fa-folder-open text-2xl"></i>
                </div>
                <h3 class="text-base font-bold text-slate-800">Data Nilai Tidak Ditemukan</h3>
                <p class="text-sm text-slate-500 mt-1 max-w-md mx-auto">
                    Belum ada siswa atau data nilai yang sesuai dengan filter periode, kelas, mata pelajaran, atau status ketuntasan yang dipilih.
                </p>
            </div>
        @elseif($mode === 'leger' && $legerMapels->isNotEmpty())
            <!-- MODE LEGER MATRIKS MAPEL -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/90 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="py-3.5 px-3 text-center w-12 sticky left-0 bg-slate-50 z-10">Rank</th>
                            <th class="py-3.5 px-4 min-w-[210px] sticky left-12 bg-slate-50 z-10 border-r border-slate-200">Siswa</th>
                            @foreach($legerMapels as $pk)
                                @php $m = $pk->guruMapel->mapel; @endphp
                                <th class="py-3 px-3 text-center min-w-[105px]">
                                    <div class="text-slate-700 leading-tight">{{ $m->nama_mapel }}</div>
                                    <div class="text-[10px] font-medium text-slate-400 normal-case mt-0.5">KKM {{ (int) ($m->kkm ?? 75) }}</div>
                                </th>
                            @endforeach
                            <th class="py-3.5 px-3 text-center bg-blue-50/70 text-blue-800 min-w-[95px]">Rata-rata</th>
                            <th class="py-3.5 px-3 text-center min-w-[75px]">Predikat</th>
                            <th class="py-3.5 px-3 text-center min-w-[90px]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($rows as $row)
                            <tr class="hover:bg-blue-50/30 transition-colors">
                                <td class="py-3 px-3 text-center sticky left-0 bg-white z-10">
                                    @if($row['peringkat'] === 1)
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-100 text-amber-700 font-extrabold text-xs ring-2 ring-amber-200">1</span>
                                    @elseif($row['peringkat'] === 2)
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-200 text-slate-700 font-extrabold text-xs">2</span>
                                    @elseif($row['peringkat'] === 3)
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-orange-100 text-orange-700 font-extrabold text-xs">3</span>
                                    @else
                                        <span class="text-xs font-bold text-slate-500">{{ $row['peringkat'] ?? '—' }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 sticky left-12 bg-white z-10 border-r border-slate-100">
                                    <div class="font-bold text-slate-900 leading-tight">{{ $row['nama_siswa'] }}</div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">NIS: {{ $row['nis'] }}</div>
                                </td>
                                @foreach($legerMapels as $pk)
                                    @php
                                        $cell = $row['mapel_scores'][$pk->guruMapel->mapel_id] ?? null;
                                    @endphp
                                    <td class="py-3 px-3 text-center">
                                        @if(!$cell || !$cell['accessible'])
                                            <span class="text-[11px] font-medium text-slate-300" title="Mapel agama berbeda">N/A</span>
                                        @elseif($cell['nilai_akhir'] === null)
                                            <span class="text-xs text-slate-400">—</span>
                                        @else
                                            <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-lg text-xs font-bold {{ $cell['tuntas'] ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}"
                                                  title="Tugas: {{ $cell['rata_tugas'] ?? '-' }} | Kuis: {{ $cell['rata_kuis'] ?? '-' }} | Ujian: {{ $cell['rata_ujian'] ?? '-' }}">
                                                {{ number_format($cell['nilai_akhir'], 1) }}
                                            </span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="py-3 px-3 text-center bg-blue-50/30 font-extrabold text-slate-900">
                                    {{ $row['nilai_akhir'] !== null ? number_format($row['nilai_akhir'], 2) : '—' }}
                                </td>
                                <td class="py-3 px-3 text-center">
                                    @php
                                        $predClass = match ($row['predikat']) {
                                            'A' => 'bg-emerald-100 text-emerald-800',
                                            'B' => 'bg-blue-100 text-blue-800',
                                            'C' => 'bg-amber-100 text-amber-800',
                                            'D' => 'bg-rose-100 text-rose-800',
                                            default => 'bg-slate-100 text-slate-500',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-xs font-extrabold {{ $predClass }}">
                                        {{ $row['predikat'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <a href="{{ route('admin.rekap-nilai.siswa', ['siswa' => $row['siswa_id'], 'academic_period_id' => $selectedPeriodId]) }}"
                                       class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors">
                                        <i class="fa-solid fa-eye text-[11px]"></i> Detail
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
                        <tr class="bg-slate-50/90 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="py-3.5 px-4 text-center w-16">Rank</th>
                            <th class="py-3.5 px-4 min-w-[220px]">Siswa &amp; Kelas</th>
                            <th class="py-3.5 px-4 text-center">Rata-rata Tugas</th>
                            <th class="py-3.5 px-4 text-center">Rata-rata Kuis</th>
                            <th class="py-3.5 px-4 text-center">Rata-rata Ujian</th>
                            <th class="py-3.5 px-4 text-center bg-blue-50/60 text-blue-800">Nilai Akhir</th>
                            <th class="py-3.5 px-4 text-center">Predikat</th>
                            <th class="py-3.5 px-4 text-center">Ketuntasan</th>
                            <th class="py-3.5 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($rows as $row)
                            <tr class="hover:bg-blue-50/30 transition-colors">
                                <!-- Peringkat -->
                                <td class="py-3.5 px-4 text-center">
                                    @if($row['peringkat'] === 1)
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-amber-100 text-amber-700 font-extrabold text-xs ring-2 ring-amber-200" title="Peringkat 1">
                                            <i class="fa-solid fa-crown text-[11px] mr-0.5"></i>1
                                        </span>
                                    @elseif($row['peringkat'] === 2)
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-slate-200 text-slate-700 font-extrabold text-xs">2</span>
                                    @elseif($row['peringkat'] === 3)
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-orange-100 text-orange-700 font-extrabold text-xs">3</span>
                                    @else
                                        <span class="text-xs font-bold text-slate-500">{{ $row['peringkat'] ?? '—' }}</span>
                                    @endif
                                </td>

                                <!-- Siswa -->
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900 leading-tight">{{ $row['nama_siswa'] }}</div>
                                    <div class="flex flex-wrap items-center gap-1.5 mt-1">
                                        <span class="text-[11px] text-slate-500">NIS: {{ $row['nis'] }}</span>
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
                                        <div class="text-slate-400 font-medium">—</div>
                                    @endif
                                    <div class="text-[10px] text-slate-400 mt-0.5">{{ $row['tugas_dinilai'] }}/{{ $row['tugas_total'] }} dinilai</div>
                                </td>

                                <!-- Kuis -->
                                <td class="py-3.5 px-4 text-center">
                                    @if($row['rata_kuis'] !== null)
                                        <div class="font-bold text-slate-800">{{ number_format($row['rata_kuis'], 2) }}</div>
                                    @else
                                        <div class="text-slate-400 font-medium">—</div>
                                    @endif
                                    <div class="text-[10px] text-slate-400 mt-0.5">{{ $row['kuis_dinilai'] }}/{{ $row['kuis_total'] }} selesai</div>
                                </td>

                                <!-- Ujian -->
                                <td class="py-3.5 px-4 text-center">
                                    @if($row['rata_ujian'] !== null)
                                        <div class="font-bold text-slate-800">{{ number_format($row['rata_ujian'], 2) }}</div>
                                    @else
                                        <div class="text-slate-400 font-medium">—</div>
                                    @endif
                                    <div class="text-[10px] text-slate-400 mt-0.5">{{ $row['ujian_dinilai'] }}/{{ $row['ujian_total'] }} selesai</div>
                                </td>

                                <!-- Nilai Akhir -->
                                <td class="py-3.5 px-4 text-center bg-blue-50/30">
                                    @if($row['nilai_akhir'] !== null)
                                        <span class="text-base font-extrabold {{ $row['status_ketuntasan'] === 'tuntas' ? 'text-emerald-700' : 'text-rose-600' }}">
                                            {{ number_format($row['nilai_akhir'], 2) }}
                                        </span>
                                    @else
                                        <span class="text-sm text-slate-400">—</span>
                                    @endif
                                </td>

                                <!-- Predikat -->
                                <td class="py-3.5 px-4 text-center">
                                    @php
                                        $predClass = match ($row['predikat']) {
                                            'A' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                            'B' => 'bg-blue-100 text-blue-800 border-blue-200',
                                            'C' => 'bg-amber-100 text-amber-800 border-amber-200',
                                            'D' => 'bg-rose-100 text-rose-800 border-rose-200',
                                            default => 'bg-slate-100 text-slate-500 border-slate-200',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl text-xs font-extrabold border {{ $predClass }}">
                                        {{ $row['predikat'] }}
                                    </span>
                                </td>

                                <!-- Ketuntasan -->
                                <td class="py-3.5 px-4 text-center">
                                    @if($row['status_ketuntasan'] === 'tuntas')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/70">
                                            <i class="fa-solid fa-check text-[10px]"></i> Tuntas
                                        </span>
                                    @elseif($row['status_ketuntasan'] === 'belum_tuntas')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200/70">
                                            <i class="fa-solid fa-xmark text-[10px]"></i> Belum Tuntas
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-500">
                                            Belum Ada Nilai
                                        </span>
                                    @endif

                                    @if(!$selectedMapel && $row['mapel_wajib'] > 0)
                                        <div class="text-[10px] text-slate-400 mt-1">
                                            {{ $row['mapel_tuntas'] }}/{{ $row['mapel_wajib'] }} Mapel Tuntas
                                        </div>
                                    @endif
                                </td>

                                <!-- Aksi -->
                                <td class="py-3.5 px-4 text-center">
                                    <a href="{{ route('admin.rekap-nilai.siswa', ['siswa' => $row['siswa_id'], 'academic_period_id' => $selectedPeriodId]) }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-blue-50 text-blue-700 hover:bg-blue-600 hover:text-white transition-all border border-blue-100">
                                        <i class="fa-solid fa-file-lines text-[11px]"></i>
                                        <span>Transkrip</span>
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
