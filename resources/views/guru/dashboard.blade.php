@extends('layouts.app')

@section('title', 'Dashboard Guru')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">
    <!-- Welcome Alert Banner -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-700 via-blue-600 to-indigo-700 text-white p-6 sm:p-8 shadow-md flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <!-- Ambient background glow elements -->
        <div class="absolute -right-10 -top-12 w-64 h-64 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
        <div class="absolute right-52 -bottom-10 w-48 h-48 rounded-full bg-indigo-400/20 blur-xl pointer-events-none"></div>
        <div class="absolute left-1/3 top-1/2 -translate-y-1/2 w-40 h-40 rounded-full bg-blue-400/10 blur-xl pointer-events-none"></div>

        <!-- Left textual content -->
        <div class="relative z-10 max-w-2xl space-y-2">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 border border-white/20 text-xs font-semibold text-blue-50 backdrop-blur-sm">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-300"></span>
                <span>{{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight leading-tight">
                Selamat Datang, {{ auth()->user()->name }}!
            </h1>
            <p class="text-blue-100 text-sm leading-relaxed font-normal">
                Kelola kegiatan belajar mengajar, pantau progres tugas, dan berinteraksi secara aktif bersama peserta didik di LearnPoint.
            </p>
        </div>

        <!-- Right contextual summary -->
        <div class="relative z-10 flex flex-wrap sm:flex-nowrap items-center gap-3 shrink-0">
            <div class="px-4 py-3 rounded-xl bg-white/10 border border-white/20 backdrop-blur-sm min-w-[130px]">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-blue-100/90 block">Mapel Diampu</span>
                <span class="text-xl font-extrabold text-white leading-tight mt-0.5 block">{{ $totalMapelDiampu ?? 0 }} Mapel</span>
            </div>
            <div class="px-4 py-3 rounded-xl bg-white/10 border border-white/20 backdrop-blur-sm min-w-[130px]">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-blue-100/90 block">Perlu Dinilai</span>
                <span class="text-xl font-extrabold text-white leading-tight mt-0.5 block">{{ $perluDinilaiCount ?? 0 }} Tugas</span>
            </div>
        </div>
    </div>

    <!-- BEGIN: MetricCardsGrid -->
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" data-purpose="metrics-cards">
        <!-- Card 1: Total Kelas Diajar -->
        <a href="{{ route('guru.jadwal') }}" class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md hover:border-blue-300 hover:-translate-y-0.5 transition-all duration-200 flex items-center gap-4 relative overflow-hidden group block">
            <div class="absolute top-0 left-0 right-0 h-1 bg-blue-600"></div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100 group-hover:scale-105 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 group-hover:text-blue-600 transition-colors block">Kelas Diajar</span>
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 leading-tight mt-0.5 tracking-tight">{{ $totalKelasDiajar ?? 0 }}</div>
                <div class="flex items-center justify-between gap-2 mt-2.5 pt-2 border-t border-slate-100">
                    <span class="text-[11px] font-medium text-slate-400 truncate">Kelas aktif</span>
                    <span class="text-[11px] font-semibold text-blue-600 flex items-center gap-1 shrink-0">
                        Jadwal <span class="group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                    </span>
                </div>
            </div>
        </a>

        <!-- Card 2: Total Materi -->
        <a href="{{ route('guru.materi') }}" class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md hover:border-emerald-300 hover:-translate-y-0.5 transition-all duration-200 flex items-center gap-4 relative overflow-hidden group block">
            <div class="absolute top-0 left-0 right-0 h-1 bg-emerald-600"></div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100 group-hover:scale-105 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 group-hover:text-emerald-600 transition-colors block">Total Materi</span>
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 leading-tight mt-0.5 tracking-tight">{{ $totalMateri ?? 0 }}</div>
                <div class="flex items-center justify-between gap-2 mt-2.5 pt-2 border-t border-slate-100">
                    <span class="text-[11px] font-medium text-slate-400 truncate">Modul &amp; referensi</span>
                    <span class="text-[11px] font-semibold text-emerald-600 flex items-center gap-1 shrink-0">
                        Kelola <span class="group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                    </span>
                </div>
            </div>
        </a>

        <!-- Card 3: Total Tugas -->
        <a href="{{ route('guru.tugas') }}" class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md hover:border-amber-300 hover:-translate-y-0.5 transition-all duration-200 flex items-center gap-4 relative overflow-hidden group block">
            <div class="absolute top-0 left-0 right-0 h-1 bg-amber-500"></div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 border border-amber-100 group-hover:scale-105 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 group-hover:text-amber-600 transition-colors block">Total Tugas</span>
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 leading-tight mt-0.5 tracking-tight">{{ $totalTugas ?? 0 }}</div>
                <div class="flex items-center justify-between gap-2 mt-2.5 pt-2 border-t border-slate-100">
                    <span class="text-[11px] font-medium text-slate-400 truncate">Penugasan siswa</span>
                    <span class="text-[11px] font-semibold text-amber-600 flex items-center gap-1 shrink-0">
                        Kelola <span class="group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                    </span>
                </div>
            </div>
        </a>

        <!-- Card 4: Kuis & Ujian -->
        <a href="{{ route('guru.ujian.index') }}" class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md hover:border-indigo-300 hover:-translate-y-0.5 transition-all duration-200 flex items-center gap-4 relative overflow-hidden group block">
            <div class="absolute top-0 left-0 right-0 h-1 bg-indigo-600"></div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 border border-indigo-100 group-hover:scale-105 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 group-hover:text-indigo-600 transition-colors block">Kuis &amp; Ujian</span>
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 leading-tight mt-0.5 tracking-tight">{{ ($totalKuis ?? 0) + ($totalUjian ?? 0) }}</div>
                <div class="flex items-center justify-between gap-2 mt-2.5 pt-2 border-t border-slate-100">
                    <span class="text-[11px] font-medium text-slate-400 truncate">{{ $totalKuis ?? 0 }} Kuis &bull; {{ $totalUjian ?? 0 }} Ujian</span>
                    <span class="text-[11px] font-semibold text-indigo-600 flex items-center gap-1 shrink-0">
                        Kelola <span class="group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                    </span>
                </div>
            </div>
        </a>
    </section>
    <!-- END: MetricCardsGrid -->

    <!-- Main Dashboard Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Left Column (8 cols): Perlu Ditindaklanjuti & Aktivitas Pembelajaran -->
        <div class="lg:col-span-8 space-y-6">
            <!-- Section: Perlu Ditindaklanjuti -->
            <section class="bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between gap-3 bg-white">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-100 shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-sm font-bold text-slate-900 leading-tight">Perlu Ditindaklanjuti</h3>
                                @if(($perluDinilaiCount ?? 0) > 0)
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        {{ $perluDinilaiCount }} Menanti Penilaian
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5 truncate">Penugasan siswa yang sudah dikumpulkan dan belum dinilai</p>
                        </div>
                    </div>
                    <a href="{{ route('guru.tugas') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700 bg-blue-50/70 hover:bg-blue-50 px-3 py-1.5 rounded-lg border border-blue-100 inline-flex items-center gap-1 shrink-0 group transition-colors">
                        Lihat Semua <span class="group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                    </a>
                </div>

                <div class="p-6">
                    @if(($perluDinilaiCount ?? 0) > 0)
                        <div class="space-y-3">
                            @foreach($perluDinilaiTugases as $item)
                                <div class="p-3.5 rounded-xl border border-slate-200/80 bg-slate-50/60 hover:bg-white hover:border-amber-200 hover:shadow-sm transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="flex items-start gap-3 min-w-0">
                                        <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-700 border border-amber-200/80 flex items-center justify-center shrink-0 font-bold text-xs mt-0.5">
                                            {{ strtoupper(substr($item->siswa?->nama_siswa ?? 'S', 0, 2)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <h4 class="text-xs font-bold text-slate-900 truncate">{{ $item->siswa?->nama_siswa ?? 'Siswa' }}</h4>
                                                <span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 border border-blue-100 text-[10px] font-semibold">
                                                    {{ $item->tugas?->pengampuKelas?->kelas?->nama_kelas ?? 'Kelas' }}
                                                </span>
                                                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 border border-slate-200/70 text-[10px] font-semibold">
                                                    {{ $item->tugas?->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? 'Mapel' }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-slate-600 font-medium truncate mt-1">
                                                Tugas: <span class="text-slate-900 font-semibold">{{ $item->tugas?->judul ?? 'Tugas' }}</span>
                                            </p>
                                            <p class="text-[11px] text-slate-400 mt-0.5">
                                                Dikumpulkan {{ $item->created_at ? $item->created_at->diffForHumans() : '-' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="shrink-0 flex items-center sm:self-center">
                                        <a href="{{ route('guru.tugas.show', $item->tugas_id) }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-lg bg-blue-600 text-white font-semibold text-xs hover:bg-blue-700 transition-colors shadow-sm group/btn">
                                            <span>Beri Nilai</span>
                                            <span class="group-hover/btn:translate-x-0.5 transition-transform">&rarr;</span>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-8 px-4 text-center bg-slate-50/60 rounded-xl border border-dashed border-slate-200">
                            <div class="w-11 h-11 rounded-xl bg-white text-emerald-600 border border-slate-200/80 mx-auto mb-3 flex items-center justify-center shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <p class="text-sm font-bold text-slate-700">Semua Pemeriksaan Selesai</p>
                            <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Tidak ada pengumpulan tugas siswa yang tertunda. Kerja bagus!</p>
                        </div>
                    @endif
                </div>
            </section>

            <!-- Section: Aktivitas Pembelajaran Terbaru -->
            <section class="bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between gap-3 bg-white">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100 shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-sm font-bold text-slate-900 leading-tight">Aktivitas Pembelajaran Terbaru</h3>
                            <p class="text-xs text-slate-500 mt-0.5 truncate">Riwayat materi, tugas, dan kuis terakhir yang Anda buat</p>
                        </div>
                    </div>
                    <a href="{{ route('guru.materi') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700 bg-blue-50/70 hover:bg-blue-50 px-3 py-1.5 rounded-lg border border-blue-100 inline-flex items-center gap-1 shrink-0 group transition-colors">
                        Lihat Semua <span class="group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                    </a>
                </div>

                @if(isset($aktivitasTerbaru) && $aktivitasTerbaru->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/70 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200/60">
                                    <th class="px-6 py-3.5">Aktivitas</th>
                                    <th class="px-6 py-3.5">Mapel &amp; Kelas</th>
                                    <th class="px-6 py-3.5">Waktu</th>
                                    <th class="px-6 py-3.5">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs">
                                @foreach($aktivitasTerbaru as $item)
                                    @php
                                        $tipeStyle = match($item['tipe']) {
                                            'Materi' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                            'Tugas'  => 'bg-amber-50 text-amber-600 border-amber-100',
                                            default  => 'bg-indigo-50 text-indigo-600 border-indigo-100',
                                        };
                                        $isAktif = in_array($item['status'], ['Aktif', 'Berlangsung']);
                                    @endphp
                                    <tr class="hover:bg-slate-50/60 transition-colors">
                                        <td class="px-6 py-4 font-semibold text-slate-800">
                                            <a href="{{ $item['url'] }}" class="flex items-center gap-3 hover:text-blue-600 group/item">
                                                <div class="w-9 h-9 rounded-xl {{ $tipeStyle }} flex items-center justify-center border shrink-0 group-hover/item:scale-105 transition-transform">
                                                    @if($item['tipe'] === 'Materi')
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                                                    @elseif($item['tipe'] === 'Tugas')
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                                                    @else
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                                    @endif
                                                </div>
                                                <div class="min-w-0">
                                                    <span class="truncate block text-slate-900 group-hover/item:text-blue-600 transition-colors">{{ $item['judul'] }}</span>
                                                    <span class="text-[11px] text-slate-400 font-normal">{{ $item['kategori'] }}</span>
                                                </div>
                                            </a>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="font-semibold text-slate-700">{{ $item['mapel'] }}</span>
                                                <span class="text-slate-300">&bull;</span>
                                                <span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 border border-blue-100 text-[10px] font-semibold">{{ $item['kelas'] }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-slate-500 whitespace-nowrap">
                                            {{ $item['waktu'] ? $item['waktu']->diffForHumans() : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $isAktif ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/80' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $isAktif ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                                {{ $item['status'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-6">
                        <div class="py-8 px-4 text-center bg-slate-50/60 rounded-xl border border-dashed border-slate-200">
                            <div class="w-11 h-11 rounded-xl bg-white text-blue-600 border border-slate-200/80 mx-auto mb-3 flex items-center justify-center shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <p class="text-sm font-bold text-slate-700">Belum Ada Aktivitas Terbaru</p>
                            <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Materi, tugas, dan kuis yang Anda buat akan muncul secara otomatis di sini.</p>
                        </div>
                    </div>
                @endif
            </section>
        </div>

        <!-- Right Column (4 cols): Jadwal & Aksi Cepat -->
        <div class="lg:col-span-4 space-y-6">
            <!-- Section: Jadwal Mengajar -->
            <section class="bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between gap-3 bg-white">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100 shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-sm font-bold text-slate-900 leading-tight">Jadwal Mengajar</h3>
                            <p class="text-xs text-slate-500 mt-0.5 truncate">{{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
                        </div>
                    </div>
                    <a href="{{ route('guru.jadwal') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700 bg-blue-50/70 hover:bg-blue-50 px-3 py-1.5 rounded-lg border border-blue-100 inline-flex items-center gap-1 shrink-0 group transition-colors">
                        Lihat <span class="group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                    </a>
                </div>

                <div class="p-6 space-y-5">
                    @if(!empty($jadwalUrl))
                        <!-- Schedule Photo Preview Available -->
                        <div class="space-y-3">
                            <div class="relative group rounded-xl overflow-hidden border border-slate-200/80 bg-slate-50/70 p-2 text-center">
                                <img
                                    src="{{ $jadwalUrl }}"
                                    alt="Foto Jadwal Mengajar Guru"
                                    class="max-h-44 w-auto mx-auto rounded-lg object-contain shadow-sm group-hover:opacity-95 transition-opacity"
                                >
                                <div class="absolute inset-0 bg-slate-900/45 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2 rounded-xl">
                                    <a href="{{ route('guru.jadwal') }}" class="px-3 py-1.5 rounded-lg bg-white text-slate-800 text-xs font-semibold shadow-sm hover:bg-slate-50 inline-flex items-center gap-1.5 transition-colors">
                                        <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                        Perbesar
                                    </a>
                                    <a href="{{ $jadwalUrl }}" download class="px-3 py-1.5 rounded-lg bg-blue-600 text-white text-xs font-semibold shadow-sm hover:bg-blue-700 inline-flex items-center gap-1.5 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        Unduh
                                    </a>
                                </div>
                            </div>
                            <div class="flex items-center justify-between gap-2 text-xs">
                                <span class="inline-flex items-center gap-1.5 text-emerald-700 font-semibold bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200/80 text-[11px]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Jadwal Resmi Aktif
                                </span>
                                <a href="{{ route('guru.jadwal') }}" class="font-semibold text-blue-600 hover:text-blue-700 hover:underline text-[11px]">
                                    Buka Jadwal &rarr;
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="py-7 px-4 text-center bg-slate-50/60 rounded-xl border border-dashed border-slate-200">
                            <div class="w-11 h-11 rounded-xl bg-white text-blue-600 border border-slate-200/80 mx-auto mb-3 flex items-center justify-center shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <p class="text-sm font-bold text-slate-700">Foto Jadwal Belum Diunggah</p>
                            <p class="text-xs text-slate-500 mt-1 mb-3.5 max-w-xs mx-auto">Administrator belum mengunggah foto jadwal khusus untuk akun Anda.</p>
                            <a href="{{ route('guru.jadwal') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-blue-600 text-white font-semibold text-xs shadow-sm hover:bg-blue-700 transition-colors">
                                Buka Menu Jadwal <span>&rarr;</span>
                            </a>
                        </div>
                    @endif

                    <!-- Daftar Mapel & Kelas yang Diampu -->
                    @if(isset($guruMapels) && $guruMapels->isNotEmpty())
                        <div class="pt-4 border-t border-slate-100 space-y-2.5">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Kelas &amp; Mapel Diampu</span>
                            <div class="space-y-2">
                                @foreach($guruMapels as $gm)
                                    <div class="p-3 rounded-xl bg-slate-50/70 border border-slate-200/70 flex items-center justify-between gap-2 text-xs">
                                        <span class="font-bold text-slate-800 truncate">{{ $gm->mapel?->nama_mapel ?? 'Mapel' }}</span>
                                        <div class="flex items-center gap-1 flex-wrap justify-end shrink-0">
                                            @forelse($gm->pengampuKelases as $pk)
                                                <span class="px-2 py-0.5 rounded-md bg-blue-50 border border-blue-100 text-blue-700 text-[10px] font-semibold">
                                                    {{ $pk->kelas?->nama_kelas ?? '-' }}
                                                </span>
                                            @empty
                                                <span class="text-[11px] text-slate-400 italic">Belum dipetakan</span>
                                            @endforelse
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </section>

            <!-- Section: Aksi Cepat Guru -->
            <section class="bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between gap-3 bg-white">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100 shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-sm font-bold text-slate-900 leading-tight">Aksi Cepat Guru</h3>
                            <p class="text-xs text-slate-500 mt-0.5 truncate">Pintasan praktis mengajar hari ini</p>
                        </div>
                    </div>
                </div>

                <div class="p-6 space-y-2.5">
                    <!-- Option 1: Unggah Materi -->
                    <a href="{{ route('guru.materi') }}" class="p-3.5 rounded-xl bg-slate-50/60 border border-slate-200/80 hover:bg-emerald-50/50 hover:border-emerald-300 hover:shadow-sm transition-all flex items-center justify-between gap-3 group">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center shrink-0 group-hover:bg-emerald-600 group-hover:text-white group-hover:border-emerald-600 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-slate-800 group-hover:text-emerald-700 transition-colors truncate">Unggah Materi</p>
                                <p class="text-[11px] text-slate-400 truncate">Tambah modul &amp; bahan ajar</p>
                            </div>
                        </div>
                        <span class="text-slate-300 group-hover:text-emerald-600 group-hover:translate-x-0.5 transition-all text-xs font-bold shrink-0">&rarr;</span>
                    </a>

                    <!-- Option 2: Buat Tugas Baru -->
                    <a href="{{ route('guru.tugas') }}" class="p-3.5 rounded-xl bg-slate-50/60 border border-slate-200/80 hover:bg-amber-50/50 hover:border-amber-300 hover:shadow-sm transition-all flex items-center justify-between gap-3 group">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center shrink-0 group-hover:bg-amber-500 group-hover:text-white group-hover:border-amber-500 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-slate-800 group-hover:text-amber-700 transition-colors truncate">Buat Tugas Baru</p>
                                <p class="text-[11px] text-slate-400 truncate">Atur penugasan &amp; tenggat</p>
                            </div>
                        </div>
                        <span class="text-slate-300 group-hover:text-amber-600 group-hover:translate-x-0.5 transition-all text-xs font-bold shrink-0">&rarr;</span>
                    </a>

                    <!-- Option 3: Buat Kuis Baru -->
                    <a href="{{ route('guru.kuis.index') }}" class="p-3.5 rounded-xl bg-slate-50/60 border border-slate-200/80 hover:bg-indigo-50/50 hover:border-indigo-300 hover:shadow-sm transition-all flex items-center justify-between gap-3 group">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 border border-indigo-100 flex items-center justify-center shrink-0 group-hover:bg-indigo-600 group-hover:text-white group-hover:border-indigo-600 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-slate-800 group-hover:text-indigo-700 transition-colors truncate">Buat Kuis Baru</p>
                                <p class="text-[11px] text-slate-400 truncate">Susun evaluasi interaktif</p>
                            </div>
                        </div>
                        <span class="text-slate-300 group-hover:text-indigo-600 group-hover:translate-x-0.5 transition-all text-xs font-bold shrink-0">&rarr;</span>
                    </a>

                    <!-- Option 4: Buat Ujian Baru -->
                    <a href="{{ route('guru.ujian.create') }}" class="p-3.5 rounded-xl bg-slate-50/60 border border-slate-200/80 hover:bg-blue-50/50 hover:border-blue-300 hover:shadow-sm transition-all flex items-center justify-between gap-3 group">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center shrink-0 group-hover:bg-blue-600 group-hover:text-white group-hover:border-blue-600 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-slate-800 group-hover:text-blue-700 transition-colors truncate">Buat Ujian Baru</p>
                                <p class="text-[11px] text-slate-400 truncate">Jadwalkan ujian &amp; evaluasi</p>
                            </div>
                        </div>
                        <span class="text-slate-300 group-hover:text-blue-600 group-hover:translate-x-0.5 transition-all text-xs font-bold shrink-0">&rarr;</span>
                    </a>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
