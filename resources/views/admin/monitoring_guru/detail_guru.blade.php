@extends('layouts.app')

@section('title', 'Detail Monitoring Guru')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">
    <!-- Top Bar -->
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.monitoring-guru.index', ['academic_period_id' => $selectedPeriodId]) }}"
           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:text-blue-600 hover:border-blue-200 text-xs font-bold shadow-xs transition-all">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Kembali ke Monitoring Guru</span>
        </a>

        <div class="flex flex-wrap items-center gap-2.5">
            <form method="GET" action="{{ route('admin.monitoring-guru.guru', $guru->id) }}">
                <select name="academic_period_id" onchange="this.form.submit()"
                        class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 shadow-xs focus:border-blue-500 focus:outline-none">
                    @foreach($periods as $period)
                        <option value="{{ $period->id }}" {{ (int) $selectedPeriodId === (int) $period->id ? 'selected' : '' }}>
                            {{ $period->label }} {{ $period->is_active ? '(Aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
            </form>

            <a href="{{ route('admin.monitoring-guru.guru.pdf', ['guru' => $guru->id, 'academic_period_id' => $selectedPeriodId]) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-xs transition-all">
                <i class="fa-solid fa-file-pdf"></i>
                <span>Export PDF Laporan Guru</span>
            </a>
        </div>
    </div>

    <!-- Guru Identity Banner -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-700 via-blue-600 to-cyan-600 text-white p-6 sm:p-8 shadow-md">
        <div class="absolute -right-10 -top-12 w-56 h-56 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                @php
                    $nama = $guru->nama ?? $guru->user?->name ?? 'Guru';
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
                        <i class="fa-solid fa-chalkboard-user text-[10px]"></i>
                        {{ $selectedPeriod?->label ?? 'Periode Aktif' }}
                    </span>
                    <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight mt-1">{{ $nama }}</h1>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-blue-100 mt-1">
                        <span><i class="fa-solid fa-id-badge mr-1 opacity-75"></i> NUPTK: <strong>{{ $guru->nuptk ?? '-' }}</strong></span>
                        <span><i class="fa-solid fa-envelope mr-1 opacity-75"></i> {{ $guru->user?->email ?? '-' }}</span>
                        @if($guru->kelas)
                            <span><i class="fa-solid fa-door-open mr-1 opacity-75"></i> Wali Kelas: <strong>{{ $guru->kelas->nama_kelas }}</strong></span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4 bg-white/15 border border-white/20 rounded-2xl px-5 py-3.5 backdrop-blur-xs shrink-0">
                <div class="text-center pr-4 border-r border-white/20">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-blue-100">Total Aktivitas</div>
                    <div class="text-3xl font-extrabold mt-0.5">{{ $summary['total_aktivitas'] }}</div>
                </div>
                <div class="text-center">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-blue-100">Status</div>
                    <div class="text-sm font-extrabold mt-1.5 px-2.5 py-1 rounded-lg bg-white/20">
                        {{ match ($summary['kategori_keaktifan']) {
                            'sangat_aktif' => 'Sangat Aktif',
                            'aktif' => 'Aktif',
                            'kurang_aktif' => 'Kurang Aktif',
                            default => 'Belum Aktif',
                        } }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Strip -->
    <section class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Mapel / Kelas</div>
            <div class="text-2xl font-extrabold text-slate-900 mt-1">
                {{ $summary['total_mapel'] }}
                <span class="text-xs font-semibold text-slate-400">({{ $summary['total_kelas_mapel'] }} kelas)</span>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-blue-200/70 p-4 shadow-xs">
            <div class="text-[11px] font-bold uppercase tracking-wider text-blue-600">Total Materi</div>
            <div class="text-2xl font-extrabold text-blue-700 mt-1">{{ $summary['total_materi'] }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-emerald-200/70 p-4 shadow-xs">
            <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Total Tugas</div>
            <div class="text-2xl font-extrabold text-emerald-700 mt-1">{{ $summary['total_tugas'] }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-indigo-200/70 p-4 shadow-xs">
            <div class="text-[11px] font-bold uppercase tracking-wider text-indigo-600">Kuis (Pub/Total)</div>
            <div class="text-2xl font-extrabold text-indigo-700 mt-1">{{ $summary['total_kuis_published'] }}/{{ $summary['total_kuis'] }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-amber-200/70 p-4 shadow-xs">
            <div class="text-[11px] font-bold uppercase tracking-wider text-amber-600">Ujian (Aktif/Total)</div>
            <div class="text-2xl font-extrabold text-amber-700 mt-1">{{ $summary['total_ujian_aktif'] }}/{{ $summary['total_ujian'] }}</div>
        </div>
    </section>

    <!-- Rincian Per Mata Pelajaran (Dikelompokkan) -->
    <section class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="text-base font-extrabold text-slate-900">Rincian Aktivitas Per Mata Pelajaran</h2>
            <p class="text-xs text-slate-500 mt-0.5">Dikelompokkan berdasarkan mata pelajaran. Klik baris mapel untuk melihat daftar Materi, Tugas, Kuis, dan Ujian serta memfilter per kelas.</p>
        </div>

        @if($detailMapels->isEmpty())
            <div class="p-12 text-center">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                    <i class="fa-solid fa-chalkboard text-xl"></i>
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
                                class="w-full text-left px-5 py-4 hover:bg-slate-50/90 transition-colors flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                            <div class="flex items-start gap-3.5 min-w-0">
                                <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-700 text-xs font-extrabold flex items-center justify-center shrink-0 mt-0.5">
                                    {{ $idx + 1 }}
                                </span>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-extrabold text-slate-900 text-sm">{{ $dm['nama_mapel'] }}</span>
                                        <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-500 text-[10px] font-semibold">KKM {{ (int) $dm['kkm'] }}</span>
                                        <span class="px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 text-[10px] font-bold">{{ $dm['jumlah_kelas'] }} Kelas</span>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-1 mt-1.5">
                                        @foreach($dm['kelas_list'] as $kls)
                                            <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 text-[10px] font-semibold">{{ $kls }}</span>
                                        @endforeach
                                    </div>
                                    @if($dm['tugas_menunggu_dinilai'] > 0)
                                        <div class="text-[11px] text-amber-700 font-semibold mt-1">
                                            <i class="fa-solid fa-clock mr-1"></i> {{ $dm['tugas_menunggu_dinilai'] }} pengumpulan tugas menunggu dinilai
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-3 sm:gap-5 shrink-0">
                                <div class="text-center min-w-[55px]">
                                    <div class="text-[10px] uppercase font-bold text-slate-400">Materi</div>
                                    <div class="text-sm font-extrabold text-blue-700">{{ $dm['jumlah_materi'] }}</div>
                                </div>
                                <div class="text-center min-w-[55px]">
                                    <div class="text-[10px] uppercase font-bold text-slate-400">Tugas</div>
                                    <div class="text-sm font-extrabold text-emerald-700">{{ $dm['jumlah_tugas'] }}</div>
                                </div>
                                <div class="text-center min-w-[55px]">
                                    <div class="text-[10px] uppercase font-bold text-slate-400">Kuis</div>
                                    <div class="text-sm font-extrabold text-indigo-700">{{ $dm['jumlah_kuis_published'] }}/{{ $dm['jumlah_kuis'] }}</div>
                                </div>
                                <div class="text-center min-w-[55px]">
                                    <div class="text-[10px] uppercase font-bold text-slate-400">Ujian</div>
                                    <div class="text-sm font-extrabold text-amber-700">{{ $dm['jumlah_ujian_aktif'] }}/{{ $dm['jumlah_ujian'] }}</div>
                                </div>
                                <div class="text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180 text-blue-600' : ''">
                                    <i class="fa-solid fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </button>

                        <div x-show="open" x-cloak x-transition class="px-5 pb-5 pt-3 bg-slate-50/70 border-t border-slate-100 space-y-4">
                            <!-- Filter Kelas di dalam Mapel -->
                            @if($dm['jumlah_kelas'] > 1)
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="text-[11px] font-bold text-slate-500 mr-1">Filter Kelas:</span>
                                    <button type="button" @click="activeKelas = 'all'"
                                            :class="activeKelas === 'all' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100'"
                                            class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all">
                                        Semua Kelas ({{ $dm['jumlah_kelas'] }})
                                    </button>
                                    @foreach($dm['kelas_list'] as $kls)
                                        <button type="button" @click="activeKelas = {{ \Illuminate\Support\Js::from($kls) }}"
                                                :class="activeKelas === {{ \Illuminate\Support\Js::from($kls) }} ? 'bg-blue-600 text-white shadow-xs' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100'"
                                                class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all">
                                            {{ $kls }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Materi -->
                                <div class="bg-white rounded-xl border border-slate-200/80 p-4">
                                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-blue-700 mb-3">
                                        <i class="fa-solid fa-book-open mr-1"></i> Materi (<span x-text="counts[activeKelas]?.materi ?? 0">{{ $dm['materi_items']->count() }}</span>)
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
                                        <i class="fa-solid fa-clipboard-list mr-1"></i> Tugas (<span x-text="counts[activeKelas]?.tugas ?? 0">{{ $dm['tugas_items']->count() }}</span>)
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
                                        <i class="fa-solid fa-puzzle-piece mr-1"></i> Kuis (<span x-text="counts[activeKelas]?.kuis ?? 0">{{ $dm['quiz_items']->count() }}</span>)
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
                                        <i class="fa-solid fa-file-pen mr-1"></i> Ujian (<span x-text="counts[activeKelas]?.ujian ?? 0">{{ $dm['ujian_items']->count() }}</span>)
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
    </section>
</div>
@endsection
