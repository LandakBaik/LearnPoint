@extends('layouts.app')

@section('title', 'Detail Transkrip Nilai Siswa')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">
    <!-- Top Bar Navigation -->
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.rekap-nilai.index', ['academic_period_id' => $selectedPeriodId, 'kelas_id' => $kelas?->id]) }}"
           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:text-blue-600 hover:border-blue-200 text-xs font-bold shadow-xs transition-all">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Kembali ke Rekap Nilai</span>
        </a>

        <div class="flex flex-wrap items-center gap-2.5">
            <form method="GET" action="{{ route('admin.rekap-nilai.siswa', $siswa->id) }}">
                <select name="academic_period_id" onchange="this.form.submit()"
                        class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 shadow-xs focus:border-blue-500 focus:outline-none">
                    @foreach($periods as $period)
                        <option value="{{ $period->id }}" {{ (int) $selectedPeriodId === (int) $period->id ? 'selected' : '' }}>
                            {{ $period->label }} {{ $period->is_active ? '(Aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
            </form>

            <a href="{{ route('admin.rekap-nilai.siswa.pdf', ['siswa' => $siswa->id, 'academic_period_id' => $selectedPeriodId]) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-xs transition-all">
                <i class="fa-solid fa-file-pdf"></i>
                <span>Export PDF Transkrip</span>
            </a>
        </div>
    </div>

    <!-- Student Identity Banner -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-700 via-blue-600 to-indigo-700 text-white p-6 sm:p-8 shadow-md">
        <div class="absolute -right-10 -top-12 w-56 h-56 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                @php
                    $nama = $siswa->nama_siswa ?? $siswa->user?->name ?? 'Siswa';
                    $parts = explode(' ', trim($nama));
                    $inisial = count($parts) >= 2
                        ? strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1))
                        : strtoupper(substr($nama, 0, 2));
                @endphp
                <div class="w-16 h-16 rounded-2xl bg-white/20 border border-white/30 flex items-center justify-center text-2xl font-extrabold text-white shrink-0 shadow-sm">
                    {{ $inisial }}
                </div>
                <div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-white/15 text-[11px] font-semibold text-blue-50">
                        <i class="fa-solid fa-graduation-cap text-[10px]"></i>
                        {{ $selectedPeriod?->label ?? 'Periode Aktif' }}
                    </span>
                    <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight mt-1">{{ $nama }}</h1>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-blue-100 mt-1">
                        <span><i class="fa-solid fa-id-card mr-1 opacity-75"></i> NIS: <strong>{{ $siswa->nis ?? '-' }}</strong></span>
                        <span><i class="fa-solid fa-door-open mr-1 opacity-75"></i> Kelas: <strong>{{ $kelas?->nama_kelas ?? 'Belum terdaftar di periode ini' }}</strong></span>
                        <span><i class="fa-solid fa-user-tie mr-1 opacity-75"></i> Wali Kelas: <strong>{{ $kelas?->guru?->nama ?? '-' }}</strong></span>
                        @if($siswa->agama)
                            <span><i class="fa-solid fa-book-bible mr-1 opacity-75"></i> Agama: <strong>{{ $siswa->agama }}</strong></span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4 bg-white/15 border border-white/20 rounded-2xl px-5 py-3.5 backdrop-blur-xs shrink-0">
                <div class="text-center pr-4 border-r border-white/20">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-blue-100">Rata-rata Akhir</div>
                    <div class="text-3xl font-extrabold mt-0.5">
                        {{ $summary['rata_keseluruhan'] !== null ? number_format($summary['rata_keseluruhan'], 2) : '—' }}
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-blue-100">Predikat</div>
                    <div class="text-3xl font-extrabold mt-0.5">{{ $summary['predikat'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Strip -->
    <section class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Mapel Diikuti</div>
            <div class="text-2xl font-extrabold text-slate-900 mt-1">{{ $summary['total_mapel'] }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
            <div class="text-[11px] font-bold uppercase tracking-wider text-blue-600">Mapel Sudah Dinilai</div>
            <div class="text-2xl font-extrabold text-slate-900 mt-1">{{ $summary['mapel_dinilai'] }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-emerald-200/70 p-4 shadow-xs">
            <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Mapel Tuntas</div>
            <div class="text-2xl font-extrabold text-emerald-700 mt-1">{{ $summary['mapel_tuntas'] }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-rose-200/70 p-4 shadow-xs">
            <div class="text-[11px] font-bold uppercase tracking-wider text-rose-600">Belum Tuntas</div>
            <div class="text-2xl font-extrabold text-rose-600 mt-1">{{ $summary['mapel_belum_tuntas'] }}</div>
        </div>
    </section>

    <!-- Detail Per Mata Pelajaran (Expandable) -->
    <section class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="text-base font-extrabold text-slate-900">Rincian Nilai Per Mata Pelajaran</h2>
            <p class="text-xs text-slate-500 mt-0.5">Klik baris mata pelajaran untuk melihat rincian tiap Tugas, Kuis, dan Ujian.</p>
        </div>

        @if($detailMapels->isEmpty())
            <div class="p-12 text-center">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                    <i class="fa-solid fa-book-open text-xl"></i>
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
                                class="w-full text-left px-5 py-4 hover:bg-slate-50/90 transition-colors flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                            <div class="flex items-start gap-3.5 min-w-0">
                                <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-700 text-xs font-extrabold flex items-center justify-center shrink-0 mt-0.5">
                                    {{ $idx + 1 }}
                                </span>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-extrabold text-slate-900 text-sm">{{ $dm['nama_mapel'] }}</span>
                                        <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[10px] font-bold">KKM {{ (int) $dm['kkm'] }}</span>
                                    </div>
                                    <div class="text-xs text-slate-500 mt-0.5">
                                        Guru Pengampu: <strong class="text-slate-700">{{ $dm['nama_guru'] }}</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-3 sm:gap-5 shrink-0">
                                <div class="text-center min-w-[60px]">
                                    <div class="text-[10px] uppercase font-bold text-slate-400">Tugas</div>
                                    <div class="text-sm font-bold text-slate-700">{{ $dm['rata_tugas'] !== null ? number_format($dm['rata_tugas'], 1) : '—' }}</div>
                                </div>
                                <div class="text-center min-w-[60px]">
                                    <div class="text-[10px] uppercase font-bold text-slate-400">Kuis</div>
                                    <div class="text-sm font-bold text-slate-700">{{ $dm['rata_kuis'] !== null ? number_format($dm['rata_kuis'], 1) : '—' }}</div>
                                </div>
                                <div class="text-center min-w-[60px]">
                                    <div class="text-[10px] uppercase font-bold text-slate-400">Ujian</div>
                                    <div class="text-sm font-bold text-slate-700">{{ $dm['rata_ujian'] !== null ? number_format($dm['rata_ujian'], 1) : '—' }}</div>
                                </div>
                                <div class="text-center min-w-[75px] px-3 py-1.5 rounded-xl bg-blue-50/70 border border-blue-100">
                                    <div class="text-[10px] uppercase font-bold text-blue-600">Akhir</div>
                                    <div class="text-base font-extrabold {{ $dm['status'] === 'tuntas' ? 'text-emerald-700' : ($dm['status'] === 'belum_tuntas' ? 'text-rose-600' : 'text-slate-400') }}">
                                        {{ $dm['nilai_akhir'] !== null ? number_format($dm['nilai_akhir'], 2) : '—' }}
                                    </div>
                                </div>
                                <div class="min-w-[100px] text-center">
                                    @if($dm['status'] === 'tuntas')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/70">
                                            <i class="fa-solid fa-check text-[10px]"></i> Tuntas ({{ $dm['predikat'] }})
                                        </span>
                                    @elseif($dm['status'] === 'belum_tuntas')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200/70">
                                            <i class="fa-solid fa-xmark text-[10px]"></i> Belum ({{ $dm['predikat'] }})
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-500">
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
                        <div x-show="open" x-cloak x-transition class="px-5 pb-5 pt-2 bg-slate-50/70 border-t border-slate-100">
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                                <!-- Daftar Tugas -->
                                <div class="bg-white rounded-xl border border-slate-200/80 p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 class="text-xs font-extrabold uppercase tracking-wider text-blue-700">
                                            <i class="fa-solid fa-clipboard-list mr-1"></i> Tugas ({{ $dm['tugas_items']->count() }})
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
                                            <i class="fa-solid fa-puzzle-piece mr-1"></i> Kuis ({{ $dm['quiz_items']->count() }})
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
                                            <i class="fa-solid fa-file-pen mr-1"></i> Ujian ({{ $dm['ujian_items']->count() }})
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
                                                        <span class="px-2 py-0.5 rounded-md font-bold shrink-0 {{ $uj['nilai_akhir'] >= $uj['kkm'] ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
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
    </section>
</div>
@endsection
