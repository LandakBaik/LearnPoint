@extends('layouts.app')

@section('title', 'Detail Transkrip Nilai Siswa')

@section('content')
@php
    $nama = $siswa->nama_siswa ?? $siswa->user?->name ?? 'Siswa';
    $parts = explode(' ', trim($nama));
    $inisial = count($parts) >= 2
        ? strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1))
        : strtoupper(substr($nama, 0, 2));
@endphp
<div class="space-y-6 max-w-7xl mx-auto">

    <!-- Top Bar Navigation -->
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('kepala_sekolah.rekap-nilai.index', $returnFilters ?? ['academic_period_id' => $selectedPeriodId, 'kelas_id' => $kelas?->id]) }}"
           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-slate-200/80 text-slate-700 hover:text-blue-600 hover:border-blue-200 text-xs font-bold shadow-2xs transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali ke Rekap Nilai</span>
        </a>

        <div class="flex flex-wrap items-center gap-2">
            <form method="GET" action="{{ route('kepala_sekolah.rekap-nilai.siswa', $siswa->id) }}" class="flex flex-wrap items-center gap-2">
                @foreach(($returnFilters ?? []) as $rk => $rv)
                    @if(!in_array($rk, ['academic_period_id', 'hitung_kosong'], true))
                        <input type="hidden" name="{{ $rk }}" value="{{ $rv }}">
                    @endif
                @endforeach
                <select name="academic_period_id" onchange="this.form.submit()"
                        class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 shadow-2xs focus:border-blue-500 focus:outline-none">
                    @foreach($periods as $period)
                        <option value="{{ $period->id }}" {{ (int) $selectedPeriodId === (int) $period->id ? 'selected' : '' }}>
                            {{ $period->label }} {{ $period->is_active ? '(Aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
                <select name="hitung_kosong" onchange="this.form.submit()"
                        class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 shadow-2xs focus:border-blue-500 focus:outline-none">
                    <option value="abaikan" {{ ($hitungKosong ?? 'abaikan') === 'abaikan' ? 'selected' : '' }}>Kosong: Abaikan</option>
                    <option value="nol" {{ ($hitungKosong ?? 'abaikan') === 'nol' ? 'selected' : '' }}>Kosong: Hitung 0</option>
                </select>
            </form>

            <a href="{{ route('kepala_sekolah.rekap-nilai.siswa.pdf', ['siswa' => $siswa->id, 'academic_period_id' => $selectedPeriodId, 'hitung_kosong' => $hitungKosong ?? 'abaikan']) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition-all hover:-translate-y-0.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Export PDF Transkrip</span>
            </a>
        </div>
    </div>

    <!-- Student Identity Card (Konsisten dengan Gaya Admin) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5 sm:p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-5">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center text-xl font-black text-blue-600 shrink-0">
                    {{ $inisial }}
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">{{ $nama }}</h1>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md bg-blue-50 border border-blue-200/70 text-[11px] font-bold text-blue-700">
                            {{ $selectedPeriod?->label ?? 'Periode Aktif' }}
                        </span>
                    </div>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500 mt-1.5">
                        <span>NIS: <strong class="text-slate-700">{{ $siswa->nis ?? '-' }}</strong></span>
                        <span>Kelas: <strong class="text-slate-700">{{ $kelas?->nama_kelas ?? 'Belum terdaftar di periode ini' }}</strong></span>
                        <span>Wali Kelas: <strong class="text-slate-700">{{ $kelas?->guru?->nama ?? '-' }}</strong></span>
                        @if($siswa->agama)
                            <span>Agama: <strong class="text-slate-700">{{ $siswa->agama }}</strong></span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4 bg-slate-50 border border-slate-200/80 rounded-2xl px-5 py-3.5 shrink-0">
                <div class="text-center">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Rata-rata Akhir</div>
                    <div class="text-2xl sm:text-3xl font-black text-slate-900 mt-0.5">
                        {{ $summary['rata_keseluruhan'] !== null ? number_format($summary['rata_keseluruhan'], 2) : '—' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4 Summary Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs">
            <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Mapel Diikuti</div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 mt-2 tracking-tight">{{ $summary['total_mapel'] }}</div>
            <div class="text-[11px] font-medium text-slate-500 mt-1">Mata pelajaran aktif</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs">
            <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Mapel Sudah Dinilai</div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 mt-2 tracking-tight">{{ $summary['mapel_dinilai'] }}</div>
            <div class="text-[11px] font-medium text-slate-500 mt-1">Memiliki komponen nilai</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs">
            <div class="text-xs font-bold uppercase tracking-wider text-emerald-600">Mapel Tuntas</div>
            <div class="text-2xl sm:text-3xl font-black text-emerald-700 mt-2 tracking-tight">{{ $summary['mapel_tuntas'] }}</div>
            <div class="text-[11px] font-medium text-slate-500 mt-1">Memenuhi KKM mapel</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs">
            <div class="text-xs font-bold uppercase tracking-wider text-rose-600">Belum Tuntas</div>
            <div class="text-2xl sm:text-3xl font-black text-rose-600 mt-2 tracking-tight">{{ $summary['mapel_belum_tuntas'] }}</div>
            <div class="text-[11px] font-medium text-slate-500 mt-1">Di bawah KKM mapel</div>
        </div>
    </div>

    <!-- Detail Per Mata Pelajaran (Expandable) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="text-sm font-extrabold text-slate-900">Rincian Nilai Per Mata Pelajaran</h2>
            <p class="text-xs text-slate-500 mt-0.5">Klik baris mata pelajaran untuk melihat rincian tiap Tugas, Kuis, dan Ujian. Ketuntasan mengikuti KKM masing-masing mapel.</p>
        </div>

        @if($detailMapels->isEmpty())
            <div class="p-12 text-center">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-slate-800">Belum Ada Mata Pelajaran</h3>
                <p class="text-xs text-slate-500 mt-1">Siswa ini belum terdaftar pada kelas atau belum memiliki guru pengampu mata pelajaran pada periode yang dipilih.</p>
            </div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach($detailMapels as $idx => $dm)
                    <div x-data="{ open: false }" class="transition-colors">
                        <!-- Row Header -->
                        <button type="button" @click="open = !open"
                                class="w-full text-left px-5 py-4 hover:bg-slate-50/80 transition-colors flex flex-col lg:flex-row lg:items-center justify-between gap-4 cursor-pointer">
                            <div class="flex items-start gap-3.5 min-w-0">
                                <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-700 text-xs font-extrabold flex items-center justify-center shrink-0 mt-0.5">
                                    {{ $idx + 1 }}
                                </span>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-bold text-slate-900 text-sm">{{ $dm['nama_mapel'] }}</span>
                                        <span class="px-2 py-0.5 rounded-md bg-amber-50 border border-amber-200/80 text-amber-700 text-[10px] font-bold">KKM: {{ (int) $dm['kkm'] }}</span>
                                    </div>
                                    <div class="text-xs text-slate-500 mt-0.5">
                                        Guru Pengampu: <strong class="text-slate-700">{{ $dm['nama_guru'] }}</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-3 sm:gap-5 shrink-0">
                                <div class="text-center min-w-[56px]">
                                    <div class="text-[10px] uppercase font-bold text-slate-400">Tugas</div>
                                    <div class="text-xs font-bold text-slate-700">{{ $dm['rata_tugas'] !== null ? number_format($dm['rata_tugas'], 1) : '—' }}</div>
                                </div>
                                <div class="text-center min-w-[56px]">
                                    <div class="text-[10px] uppercase font-bold text-slate-400">Kuis</div>
                                    <div class="text-xs font-bold text-slate-700">{{ $dm['rata_kuis'] !== null ? number_format($dm['rata_kuis'], 1) : '—' }}</div>
                                </div>
                                <div class="text-center min-w-[56px]">
                                    <div class="text-[10px] uppercase font-bold text-slate-400">Ujian</div>
                                    <div class="text-xs font-bold text-slate-700">{{ $dm['rata_ujian'] !== null ? number_format($dm['rata_ujian'], 1) : '—' }}</div>
                                </div>
                                <div class="text-center min-w-[72px] px-3 py-1.5 rounded-xl bg-blue-50/70 border border-blue-100">
                                    <div class="text-[10px] uppercase font-bold text-blue-600">Akhir</div>
                                    <div class="text-sm font-extrabold {{ $dm['status'] === 'tuntas' ? 'text-emerald-700' : ($dm['status'] === 'belum_tuntas' ? 'text-rose-600' : 'text-slate-400') }}">
                                        {{ $dm['nilai_akhir'] !== null ? number_format($dm['nilai_akhir'], 2) : '—' }}
                                    </div>
                                </div>
                                <div class="min-w-[96px] text-center">
                                    @if($dm['status'] === 'tuntas')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                                            Tuntas
                                        </span>
                                    @elseif($dm['status'] === 'belum_tuntas')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200/80">
                                            Belum Tuntas
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-500">
                                            Belum Ada
                                        </span>
                                    @endif
                                </div>
                                <div class="text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180 text-blue-600' : ''">
                                    <i class="fa-solid fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </button>

                        <!-- Expanded Item Details -->
                        <div x-show="open" x-cloak x-transition class="px-5 pb-5 pt-3 bg-slate-50/60 border-t border-slate-100">
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                                <!-- Daftar Tugas -->
                                <div class="bg-white rounded-xl border border-slate-200/80 p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 class="text-xs font-extrabold uppercase tracking-wider text-blue-700">
                                            Tugas ({{ $dm['tugas_items']->count() }})
                                        </h4>
                                        <span class="text-xs font-bold text-slate-600">
                                            Rata: {{ $dm['rata_tugas'] !== null ? number_format($dm['rata_tugas'], 2) : '-' }}
                                        </span>
                                    </div>
                                    @if($dm['tugas_items']->isEmpty())
                                        <p class="text-xs text-slate-400 italic">Belum ada tugas pada mapel ini.</p>
                                    @else
                                        <ul class="space-y-2">
                                            @foreach($dm['tugas_items'] as $t)
                                                <li class="flex items-center justify-between gap-2 text-xs py-1.5 border-b border-slate-100 last:border-0">
                                                    <span class="text-slate-700 font-medium truncate" title="{{ $t['judul'] }}">{{ $t['judul'] }}</span>
                                                    @if($t['nilai'] !== null)
                                                        <span class="px-2 py-0.5 rounded-md font-bold shrink-0 {{ $t['nilai'] >= $dm['kkm'] ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                                            {{ number_format($t['nilai'], 1) }}
                                                        </span>
                                                    @elseif($t['status'] === 'menunggu_penilaian')
                                                        <span class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 font-semibold shrink-0">Menunggu</span>
                                                    @else
                                                        <span class="text-slate-400 shrink-0">Belum</span>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>

                                <!-- Daftar Kuis -->
                                <div class="bg-white rounded-xl border border-slate-200/80 p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 class="text-xs font-extrabold uppercase tracking-wider text-indigo-700">
                                            Kuis ({{ $dm['quiz_items']->count() }})
                                        </h4>
                                        <span class="text-xs font-bold text-slate-600">
                                            Rata: {{ $dm['rata_kuis'] !== null ? number_format($dm['rata_kuis'], 2) : '-' }}
                                        </span>
                                    </div>
                                    @if($dm['quiz_items']->isEmpty())
                                        <p class="text-xs text-slate-400 italic">Belum ada kuis dipublikasikan.</p>
                                    @else
                                        <ul class="space-y-2">
                                            @foreach($dm['quiz_items'] as $qz)
                                                <li class="flex items-center justify-between gap-2 text-xs py-1.5 border-b border-slate-100 last:border-0">
                                                    <span class="text-slate-700 font-medium truncate" title="{{ $qz['judul'] }}">{{ $qz['judul'] }}</span>
                                                    @if($qz['nilai'] !== null)
                                                        <span class="px-2 py-0.5 rounded-md font-bold shrink-0 {{ $qz['nilai'] >= $dm['kkm'] ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                                            {{ number_format($qz['nilai'], 1) }}
                                                        </span>
                                                    @else
                                                        <span class="text-slate-400 shrink-0">Belum</span>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>

                                <!-- Daftar Ujian -->
                                <div class="bg-white rounded-xl border border-slate-200/80 p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 class="text-xs font-extrabold uppercase tracking-wider text-amber-700">
                                            Ujian ({{ $dm['ujian_items']->count() }})
                                        </h4>
                                        <span class="text-xs font-bold text-slate-600">
                                            Rata: {{ $dm['rata_ujian'] !== null ? number_format($dm['rata_ujian'], 2) : '-' }}
                                        </span>
                                    </div>
                                    @if($dm['ujian_items']->isEmpty())
                                        <p class="text-xs text-slate-400 italic">Belum ada ujian reguler pada mapel ini.</p>
                                    @else
                                        <ul class="space-y-2">
                                            @foreach($dm['ujian_items'] as $uj)
                                                <li class="flex items-center justify-between gap-2 text-xs py-1.5 border-b border-slate-100 last:border-0">
                                                    <span class="text-slate-700 font-medium truncate" title="{{ $uj['judul'] }}">
                                                        {{ $uj['judul'] }}
                                                        @if($uj['jumlah_remidi'] > 0)
                                                            <span class="text-[10px] font-bold text-blue-600">(R)</span>
                                                        @endif
                                                    </span>
                                                    @if($uj['nilai_akhir'] !== null)
                                                        <span class="px-2 py-0.5 rounded-md font-bold shrink-0 {{ $uj['nilai_akhir'] >= $dm['kkm'] ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                                            {{ number_format($uj['nilai_akhir'], 1) }}
                                                        </span>
                                                    @else
                                                        <span class="text-slate-400 shrink-0">Belum</span>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
