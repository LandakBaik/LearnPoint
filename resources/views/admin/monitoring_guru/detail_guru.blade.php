@extends('layouts.app')

@section('title', 'Detail Monitoring Guru')

@section('content')
@php
    $nama = $guru->nama ?? $guru->user?->name ?? 'Guru';
    $parts = explode(' ', trim($nama));
    $inisial = count($parts) >= 2
        ? strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1))
        : strtoupper(substr($nama, 0, 2));

    $statusLabel = match ($summary['kategori_keaktifan']) {
        'sangat_aktif' => 'Sangat Aktif',
        'aktif' => 'Aktif',
        'kurang_aktif' => 'Kurang Aktif',
        default => 'Belum Aktif',
    };
    $statusBadgeClass = match ($summary['kategori_keaktifan']) {
        'sangat_aktif' => 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
        'aktif' => 'bg-blue-50 text-blue-700 border-blue-200/80',
        'kurang_aktif' => 'bg-amber-50 text-amber-700 border-amber-200/80',
        default => 'bg-rose-50 text-rose-700 border-rose-200/80',
    };
@endphp
<div class="space-y-6 max-w-7xl mx-auto">

    <!-- Top Bar Navigation -->
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.monitoring-guru.index', ['academic_period_id' => $selectedPeriodId]) }}"
           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-slate-200/80 text-slate-700 hover:text-blue-600 hover:border-blue-200 text-xs font-bold shadow-2xs transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali ke Monitoring Guru</span>
        </a>

        <div class="flex flex-wrap items-center gap-2">
            <form method="GET" action="{{ route('admin.monitoring-guru.guru', $guru->id) }}">
                <select name="academic_period_id" onchange="this.form.submit()"
                        class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 shadow-2xs focus:border-blue-500 focus:outline-none">
                    @foreach($periods as $period)
                        <option value="{{ $period->id }}" {{ (int) $selectedPeriodId === (int) $period->id ? 'selected' : '' }}>
                            {{ $period->label }} {{ $period->is_active ? '(Aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
            </form>

            <a href="{{ route('admin.monitoring-guru.guru.pdf', ['guru' => $guru->id, 'academic_period_id' => $selectedPeriodId]) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition-all hover:-translate-y-0.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Export PDF Laporan Guru</span>
            </a>
        </div>
    </div>

    <!-- Guru Identity Card (Konsisten dengan Gaya Admin) -->
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
                        <span>NUPTK: <strong class="text-slate-700">{{ $guru->nuptk ?? '-' }}</strong></span>
                        <span>Email: <strong class="text-slate-700">{{ $guru->user?->email ?? '-' }}</strong></span>
                        @if($guru->kelas)
                            <span>Wali Kelas: <strong class="text-slate-700">{{ $guru->kelas->nama_kelas }}</strong></span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4 bg-slate-50 border border-slate-200/80 rounded-2xl px-5 py-3.5 shrink-0">
                <div class="text-center pr-4 border-r border-slate-200">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Aktivitas</div>
                    <div class="text-2xl sm:text-3xl font-black text-slate-900 mt-0.5">{{ $summary['total_aktivitas'] }}</div>
                    <div class="text-[10px] font-medium text-slate-400 mt-0.5">Rata-rata guru: {{ number_format($summary['rata_rata_aktivitas'] ?? 0, 1) }}</div>
                </div>
                <div class="text-center">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Keaktifan</div>
                    <div class="mt-1.5">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold border {{ $statusBadgeClass }}">
                            {{ $statusLabel }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 5 Summary Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs">
            <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Mapel / Kelas</div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 mt-2 tracking-tight">
                {{ $summary['total_mapel'] }}
                <span class="text-xs font-semibold text-slate-400">({{ $summary['total_kelas_mapel'] }} kelas)</span>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs">
            <div class="text-xs font-bold uppercase tracking-wider text-blue-600">Total Materi</div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 mt-2 tracking-tight">{{ $summary['total_materi'] }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs">
            <div class="text-xs font-bold uppercase tracking-wider text-emerald-600">Total Tugas</div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 mt-2 tracking-tight">{{ $summary['total_tugas'] }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs">
            <div class="text-xs font-bold uppercase tracking-wider text-indigo-600">Kuis (Pub/Total)</div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 mt-2 tracking-tight">{{ $summary['total_kuis_published'] }}/{{ $summary['total_kuis'] }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs">
            <div class="text-xs font-bold uppercase tracking-wider text-amber-600">Ujian (Aktif/Total)</div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 mt-2 tracking-tight">{{ $summary['total_ujian_aktif'] }}/{{ $summary['total_ujian'] }}</div>
        </div>
    </div>

    <!-- Rincian Per Mata Pelajaran (Dikelompokkan) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="text-sm font-extrabold text-slate-900">Rincian Aktivitas Per Mata Pelajaran</h2>
            <p class="text-xs text-slate-500 mt-0.5">Dikelompokkan berdasarkan mata pelajaran. Klik baris mapel untuk melihat daftar Materi, Tugas, Kuis, dan Ujian serta memfilter per kelas.</p>
        </div>

        @if($detailMapels->isEmpty())
            <div class="p-12 text-center">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-slate-800">Belum Ada Penugasan Mata Pelajaran</h3>
                <p class="text-xs text-slate-500 mt-1">Guru ini belum diplot mengampu kelas/mata pelajaran pada periode akademik yang dipilih.</p>
            </div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach($detailMapels as $idx => $dm)
                    @php
                        $countsByKelas = [
                            'all' => [
                                'materi' => $dm['materi_items']->count(),
                                'tugas'  => $dm['tugas_items']->count(),
                                'kuis'   => $dm['quiz_items']->count(),
                                'ujian'  => $dm['ujian_items']->count(),
                            ],
                        ];
                        foreach ($dm['kelas_list'] as $klsName) {
                            $countsByKelas[$klsName] = [
                                'materi' => $dm['materi_items']->where('nama_kelas', $klsName)->count(),
                                'tugas'  => $dm['tugas_items']->where('nama_kelas', $klsName)->count(),
                                'kuis'   => $dm['quiz_items']->where('nama_kelas', $klsName)->count(),
                                'ujian'  => $dm['ujian_items']->where('nama_kelas', $klsName)->count(),
                            ];
                        }
                    @endphp
                    <div x-data="{ open: {{ $idx === 0 ? 'true' : 'false' }}, activeKelas: 'all', counts: {{ \Illuminate\Support\Js::from($countsByKelas) }} }">
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
                                        <span class="px-2 py-0.5 rounded-md bg-indigo-50 border border-indigo-200/70 text-indigo-700 text-[10px] font-bold">{{ $dm['jumlah_kelas'] }} Kelas</span>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-1 mt-1.5">
                                        @foreach($dm['kelas_list'] as $kls)
                                            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[10px] font-semibold">{{ $kls }}</span>
                                        @endforeach
                                    </div>
                                    @if($dm['tugas_menunggu_dinilai'] > 0)
                                        <div class="text-[11px] text-amber-700 font-semibold mt-1">
                                            {{ $dm['tugas_menunggu_dinilai'] }} pengumpulan tugas menunggu dinilai
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-3 sm:gap-5 shrink-0">
                                <div class="text-center min-w-[55px]">
                                    <div class="text-[10px] uppercase font-bold text-slate-400">Materi</div>
                                    <div class="text-xs font-extrabold text-blue-700">{{ $dm['jumlah_materi'] }}</div>
                                </div>
                                <div class="text-center min-w-[55px]">
                                    <div class="text-[10px] uppercase font-bold text-slate-400">Tugas</div>
                                    <div class="text-xs font-extrabold text-emerald-700">{{ $dm['jumlah_tugas'] }}</div>
                                </div>
                                <div class="text-center min-w-[55px]">
                                    <div class="text-[10px] uppercase font-bold text-slate-400">Kuis</div>
                                    <div class="text-xs font-extrabold text-indigo-700">{{ $dm['jumlah_kuis_published'] }}/{{ $dm['jumlah_kuis'] }}</div>
                                </div>
                                <div class="text-center min-w-[55px]">
                                    <div class="text-[10px] uppercase font-bold text-slate-400">Ujian</div>
                                    <div class="text-xs font-extrabold text-amber-700">{{ $dm['jumlah_ujian_aktif'] }}/{{ $dm['jumlah_ujian'] }}</div>
                                </div>
                                <div class="text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180 text-blue-600' : ''">
                                    <i class="fa-solid fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </button>

                        <div x-show="open" x-cloak x-transition class="px-5 pb-5 pt-3 bg-slate-50/60 border-t border-slate-100 space-y-4">
                            <!-- Filter Kelas di dalam Mapel -->
                            @if($dm['jumlah_kelas'] > 1)
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="text-[11px] font-bold text-slate-500 mr-1">Filter Kelas:</span>
                                    <button type="button" @click="activeKelas = 'all'"
                                            :class="activeKelas === 'all' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100'"
                                            class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all cursor-pointer">
                                        Semua Kelas ({{ $dm['jumlah_kelas'] }})
                                    </button>
                                    @foreach($dm['kelas_list'] as $kls)
                                        <button type="button" @click="activeKelas = {{ \Illuminate\Support\Js::from($kls) }}"
                                                :class="activeKelas === {{ \Illuminate\Support\Js::from($kls) }} ? 'bg-blue-600 text-white shadow-xs' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100'"
                                                class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all cursor-pointer">
                                            {{ $kls }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Materi -->
                                <div class="bg-white rounded-xl border border-slate-200/80 p-4">
                                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-blue-700 mb-3">
                                        Materi (<span x-text="counts[activeKelas]?.materi ?? 0">{{ $dm['materi_items']->count() }}</span>)
                                    </h4>
                                    <p x-show="(counts[activeKelas]?.materi ?? 0) === 0" class="text-xs text-slate-400 italic">
                                        Belum ada materi diunggah<span x-show="activeKelas !== 'all'" x-text="' di kelas ' + activeKelas"></span>.
                                    </p>
                                    @if($dm['materi_items']->isNotEmpty())
                                        <ul x-show="(counts[activeKelas]?.materi ?? 0) > 0" class="space-y-2 max-h-60 overflow-y-auto pr-1">
                                            @foreach($dm['materi_items'] as $m)
                                                <li x-show="activeKelas === 'all' || activeKelas === {{ \Illuminate\Support\Js::from($m['nama_kelas']) }}"
                                                    class="flex items-center justify-between gap-2 text-xs py-1.5 border-b border-slate-100 last:border-0">
                                                    <div class="flex items-center gap-1.5 min-w-0">
                                                        <span class="px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700 text-[10px] font-bold shrink-0">{{ $m['nama_kelas'] }}</span>
                                                        <span class="font-medium text-slate-700 truncate" title="{{ $m['judul'] }}">{{ $m['judul'] }}</span>
                                                    </div>
                                                    <span class="text-[10px] text-slate-400 shrink-0">{{ $m['created_at']?->format('d M Y') }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>

                                <!-- Tugas -->
                                <div class="bg-white rounded-xl border border-slate-200/80 p-4">
                                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-emerald-700 mb-3">
                                        Tugas (<span x-text="counts[activeKelas]?.tugas ?? 0">{{ $dm['tugas_items']->count() }}</span>)
                                    </h4>
                                    <p x-show="(counts[activeKelas]?.tugas ?? 0) === 0" class="text-xs text-slate-400 italic">
                                        Belum ada tugas dibuat<span x-show="activeKelas !== 'all'" x-text="' di kelas ' + activeKelas"></span>.
                                    </p>
                                    @if($dm['tugas_items']->isNotEmpty())
                                        <ul x-show="(counts[activeKelas]?.tugas ?? 0) > 0" class="space-y-2 max-h-60 overflow-y-auto pr-1">
                                            @foreach($dm['tugas_items'] as $t)
                                                <li x-show="activeKelas === 'all' || activeKelas === {{ \Illuminate\Support\Js::from($t['nama_kelas']) }}"
                                                    class="flex items-center justify-between gap-2 text-xs py-1.5 border-b border-slate-100 last:border-0">
                                                    <div class="flex items-center gap-1.5 min-w-0">
                                                        <span class="px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700 text-[10px] font-bold shrink-0">{{ $t['nama_kelas'] }}</span>
                                                        <span class="font-medium text-slate-700 truncate" title="{{ $t['judul'] }}">{{ $t['judul'] }}</span>
                                                    </div>
                                                    <span class="shrink-0 text-[10px] font-bold px-2 py-0.5 rounded {{ $t['menunggu_dinilai'] > 0 ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700' }}">
                                                        {{ $t['sudah_dinilai'] }}/{{ $t['total_pengumpulan'] }} dinilai
                                                    </span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>

                                <!-- Kuis -->
                                <div class="bg-white rounded-xl border border-slate-200/80 p-4">
                                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-indigo-700 mb-3">
                                        Kuis (<span x-text="counts[activeKelas]?.kuis ?? 0">{{ $dm['quiz_items']->count() }}</span>)
                                    </h4>
                                    <p x-show="(counts[activeKelas]?.kuis ?? 0) === 0" class="text-xs text-slate-400 italic">
                                        Belum ada kuis dibuat<span x-show="activeKelas !== 'all'" x-text="' di kelas ' + activeKelas"></span>.
                                    </p>
                                    @if($dm['quiz_items']->isNotEmpty())
                                        <ul x-show="(counts[activeKelas]?.kuis ?? 0) > 0" class="space-y-2 max-h-60 overflow-y-auto pr-1">
                                            @foreach($dm['quiz_items'] as $qz)
                                                <li x-show="activeKelas === 'all' || activeKelas === {{ \Illuminate\Support\Js::from($qz['nama_kelas']) }}"
                                                    class="flex items-center justify-between gap-2 text-xs py-1.5 border-b border-slate-100 last:border-0">
                                                    <div class="flex items-center gap-1.5 min-w-0">
                                                        <span class="px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700 text-[10px] font-bold shrink-0">{{ $qz['nama_kelas'] }}</span>
                                                        <span class="font-medium text-slate-700 truncate" title="{{ $qz['judul'] }}">{{ $qz['judul'] }}</span>
                                                    </div>
                                                    <span class="shrink-0 text-[10px] font-bold px-2 py-0.5 rounded {{ $qz['status'] === 'published' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                                        {{ ucfirst($qz['status']) }} · {{ $qz['peserta_selesai'] }} selesai
                                                    </span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>

                                <!-- Ujian -->
                                <div class="bg-white rounded-xl border border-slate-200/80 p-4">
                                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-amber-700 mb-3">
                                        Ujian (<span x-text="counts[activeKelas]?.ujian ?? 0">{{ $dm['ujian_items']->count() }}</span>)
                                    </h4>
                                    <p x-show="(counts[activeKelas]?.ujian ?? 0) === 0" class="text-xs text-slate-400 italic">
                                        Belum ada ujian dibuat<span x-show="activeKelas !== 'all'" x-text="' di kelas ' + activeKelas"></span>.
                                    </p>
                                    @if($dm['ujian_items']->isNotEmpty())
                                        <ul x-show="(counts[activeKelas]?.ujian ?? 0) > 0" class="space-y-2 max-h-60 overflow-y-auto pr-1">
                                            @foreach($dm['ujian_items'] as $uj)
                                                <li x-show="activeKelas === 'all' || activeKelas === {{ \Illuminate\Support\Js::from($uj['nama_kelas']) }}"
                                                    class="flex items-center justify-between gap-2 text-xs py-1.5 border-b border-slate-100 last:border-0">
                                                    <div class="flex items-center gap-1.5 min-w-0">
                                                        <span class="px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700 text-[10px] font-bold shrink-0">{{ $uj['nama_kelas'] }}</span>
                                                        <span class="font-medium text-slate-700 truncate" title="{{ $uj['judul'] }}">
                                                            {{ $uj['judul'] }}
                                                            @if($uj['jenis_ujian'] !== 'reguler')
                                                                <span class="text-[10px] font-bold text-blue-600">({{ ucfirst($uj['jenis_ujian']) }})</span>
                                                            @endif
                                                        </span>
                                                    </div>
                                                    <span class="shrink-0 text-[10px] font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-700">
                                                        {{ ucfirst($uj['status']) }} · {{ $uj['peserta_selesai'] }}/{{ $uj['total_peserta'] }}
                                                    </span>
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
