@extends('layouts.app')

@section('title', 'Dashboard Guru')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">
    <!-- Welcome Alert Banner -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-700 via-blue-600 to-indigo-700 text-white p-7 sm:p-9 shadow-md flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <!-- Ambient background glow elements -->
        <div class="absolute -right-10 -top-12 w-64 h-64 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
        <div class="absolute right-52 -bottom-10 w-48 h-48 rounded-full bg-indigo-400/20 blur-xl pointer-events-none"></div>
        <div class="absolute left-1/3 top-1/2 -translate-y-1/2 w-40 h-40 rounded-full bg-blue-400/10 blur-xl pointer-events-none"></div>

        <!-- Left textual content -->
        <div class="relative z-10 max-w-xl space-y-2">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight leading-tight">
                Selamat Datang, {{ auth()->user()->name }}! 👨‍🏫
            </h1>
            <p class="text-blue-100 text-sm sm:text-base leading-relaxed font-normal">
                Kelola kegiatan belajar mengajar, pantau progres tugas, dan berinteraksi secara aktif bersama peserta didik di LearnPoint.
            </p>
        </div>
    </div>

    <!-- BEGIN: MetricCardsGrid -->
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" data-purpose="metrics-cards">
        <!-- Card 1: Total Kelas Diajar -->
        <div class="bg-white border border-blue-100 rounded-2xl p-5 shadow-sm hover:shadow-md hover:border-blue-200 transition-all duration-200 flex items-center gap-4 relative overflow-hidden group">
            <div class="absolute top-0 left-0 right-0 h-1 bg-blue-600 rounded-t-2xl"></div>
            <div class="w-13 h-13 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0 p-3 shadow-xs border border-blue-100/60 group-hover:scale-105 transition-transform">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <span class="text-[11px] font-bold uppercase tracking-wider text-blue-700/90 block">KELAS DIAJAR</span>
                <div class="text-2xl font-extrabold text-slate-900 leading-tight mt-1">{{ $totalKelasDiajar ?? 0 }}</div>
                <div class="flex items-center justify-between gap-2 mt-2 pt-1 border-t border-slate-100">
                    <span class="text-xs text-slate-400 truncate">Rombel aktif</span>
                    <a class="text-xs font-semibold text-blue-600 hover:text-blue-700 flex items-center gap-1 group/link flex-shrink-0" href="{{ route('guru.jadwal') }}">
                        Lihat Jadwal <span class="group-hover/link:translate-x-0.5 transition-transform">→</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Card 2: Materi Pembelajaran -->
        <div class="bg-white border border-emerald-200/60 rounded-2xl p-5 shadow-sm hover:shadow-md hover:border-emerald-300 transition-all duration-200 flex items-center gap-4 relative overflow-hidden group">
            <div class="absolute top-0 left-0 right-0 h-1 bg-emerald-700 rounded-t-2xl"></div>
            <div class="w-13 h-13 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center flex-shrink-0 p-3 shadow-xs border border-emerald-200/60 group-hover:scale-105 transition-transform">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700 block">TOTAL MATERI</span>
                <div class="text-2xl font-extrabold text-slate-900 leading-tight mt-1">{{ $totalMateri ?? 0 }}</div>
                <div class="flex items-center justify-between gap-2 mt-2 pt-1 border-t border-slate-100">
                    <span class="text-xs text-slate-400 truncate">Modul & referensi</span>
                    <a class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 flex items-center gap-1 group/link flex-shrink-0" href="{{ route('guru.materi') }}">
                        Kelola Materi <span class="group-hover/link:translate-x-0.5 transition-transform">→</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Card 3: Total Tugas -->
        <div class="bg-white border border-amber-200/60 rounded-2xl p-5 shadow-sm hover:shadow-md hover:border-amber-300 transition-all duration-200 flex items-center gap-4 relative overflow-hidden group">
            <div class="absolute top-0 left-0 right-0 h-1 bg-amber-600 rounded-t-2xl"></div>
            <div class="w-13 h-13 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0 p-3 shadow-xs border border-amber-200/60 group-hover:scale-105 transition-transform">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-700 block">TOTAL TUGAS</span>
                <div class="text-2xl font-extrabold text-slate-900 leading-tight mt-1">{{ $totalTugas ?? 0 }}</div>
                <div class="flex items-center justify-between gap-2 mt-2 pt-1 border-t border-slate-100">
                    <span class="text-xs text-slate-400 truncate">Penugasan siswa</span>
                    <a class="text-xs font-semibold text-amber-700 hover:text-amber-800 flex items-center gap-1 group/link flex-shrink-0" href="{{ route('guru.tugas') }}">
                        Kelola Tugas <span class="group-hover/link:translate-x-0.5 transition-transform">→</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Card 4: Kuis & Ujian -->
        <div class="bg-white border border-blue-200/60 rounded-2xl p-5 shadow-sm hover:shadow-md hover:border-blue-300 transition-all duration-200 flex items-center gap-4 relative overflow-hidden group">
            <div class="absolute top-0 left-0 right-0 h-1 bg-blue-700 rounded-t-2xl"></div>
            <div class="w-13 h-13 rounded-2xl bg-blue-50 text-blue-700 flex items-center justify-center flex-shrink-0 p-3 shadow-xs border border-blue-100/60 group-hover:scale-105 transition-transform">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <span class="text-[11px] font-bold uppercase tracking-wider text-blue-700 block">KUIS &amp; UJIAN</span>
                <div class="text-2xl font-extrabold text-slate-900 leading-tight mt-1">{{ $totalKuis ?? 0 }}</div>
                <div class="flex items-center justify-between gap-2 mt-2 pt-1 border-t border-slate-100">
                    <span class="text-xs text-slate-400 truncate">Evaluasi aktif</span>
                    <a class="text-xs font-semibold text-blue-700 hover:text-blue-800 flex items-center gap-1 group/link flex-shrink-0" href="{{ route('guru.kuis.index') }}">
                        Lihat Kuis <span class="group-hover/link:translate-x-0.5 transition-transform">→</span>
                    </a>
                </div>
            </div>
        </div>
    </section>
    <!-- END: MetricCardsGrid -->

    <!-- Main Dashboard Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left Column (8 cols): Perlu Ditindaklanjuti & Aktivitas Pembelajaran -->
        <div class="lg:col-span-8 space-y-6">
            <!-- Section: Perlu Ditindaklanjuti -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2.5 font-bold text-slate-800 text-sm">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-200/60 shadow-xs">
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 leading-tight">Perlu Ditindaklanjuti</h3>
                            <p class="text-xs text-slate-400 font-normal mt-0.5">Penugasan atau ujian siswa yang belum dinilai</p>
                        </div>
                    </div>
                    <a href="{{ route('guru.tugas') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700 flex items-center gap-1 group/link">
                        Lihat Semua <span class="group-hover/link:translate-x-0.5 transition-transform">→</span>
                    </a>
                </div>

                <div class="py-10 text-center text-slate-500 bg-slate-50/60 rounded-xl border border-dashed border-slate-200/80">
                    <div class="w-12 h-12 rounded-2xl bg-white text-emerald-600 border border-slate-200/80 mx-auto mb-3 flex items-center justify-center shadow-xs">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="text-sm font-bold text-slate-700">Semua Pemeriksaan Selesai</p>
                    <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">Tidak ada tugas atau kuis yang tertunda. Kerja bagus!</p>
                </div>
            </div>

            <!-- Section: Aktivitas Terbaru -->
            <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-white">
                    <div class="flex items-center gap-2.5 text-sm font-bold text-slate-800">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100/60 shadow-xs">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span>Aktivitas Pembelajaran Terbaru</span>
                    </div>
                    <a class="text-xs font-semibold text-blue-600 hover:text-blue-700 bg-blue-50/80 px-3 py-1.5 rounded-lg border border-blue-100/60 flex items-center gap-1 group transition-colors" href="{{ route('guru.materi') }}">
                        Lihat Semua <span class="group-hover:translate-x-0.5 transition-transform">→</span>
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/70 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200/60">
                                <th class="px-6 py-3.5">Aktivitas</th>
                                <th class="px-6 py-3.5">Kategori</th>
                                <th class="px-6 py-3.5">Waktu</th>
                                <th class="px-6 py-3.5">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-6 py-4 font-semibold text-slate-800 flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100/60 shadow-xs shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                    </div>
                                    <span>Pemberian materi pembelajaran</span>
                                </td>
                                <td class="px-6 py-4 text-slate-600 font-medium">Modul Ajar</td>
                                <td class="px-6 py-4 text-slate-500">Hari ini, {{ date('H:i') }} WIB</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60 shadow-xs">
                                        <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20">
                                            <path clip-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" fill-rule="evenodd"></path>
                                        </svg>
                                        Aktif
                                    </span>
                                </td>
                            </tr>
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-6 py-4 font-semibold text-slate-800 flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-200/60 shadow-xs shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                        </svg>
                                    </div>
                                    <span>Tugas mingguan siswa</span>
                                </td>
                                <td class="px-6 py-4 text-slate-600 font-medium">Tugas Mandiri</td>
                                <td class="px-6 py-4 text-slate-500">Kemarin, 14:00 WIB</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200/60 shadow-xs">
                                        <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20">
                                            <path clip-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" fill-rule="evenodd"></path>
                                        </svg>
                                        Berlangsung
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column (4 cols): Jadwal & Aksi Cepat -->
        <div class="lg:col-span-4 space-y-6">
            <!-- Section: Jadwal Mengajar -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between pb-3.5 mb-3.5 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100/60 shadow-xs">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 leading-tight">Jadwal Mengajar</h3>
                            <p class="text-xs text-slate-400 font-normal mt-0.5">Hari {{ now()->locale('id')->isoFormat('dddd') }}</p>
                        </div>
                    </div>
                    <a href="{{ route('guru.jadwal') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700 flex items-center gap-1 group/link">
                        Lihat <span class="group-hover/link:translate-x-0.5 transition-transform">→</span>
                    </a>
                </div>

                <div class="py-6 text-center text-slate-500 bg-slate-50/60 rounded-xl border border-dashed border-slate-200/80">
                    <div class="w-10 h-10 rounded-xl bg-white text-blue-600 border border-slate-200/80 mx-auto mb-2 flex items-center justify-center shadow-xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <p class="text-xs font-semibold text-slate-700">Jadwal Pengajaran Terdata</p>
                    <p class="text-[11px] text-slate-400 mt-0.5 mb-3">Akses jadwal lengkap Anda di menu jadwal.</p>
                    <a href="{{ route('guru.jadwal') }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-blue-600 text-white font-semibold text-xs shadow-xs hover:bg-blue-700 transition-colors">
                        Buka Jadwal Pelajaran
                    </a>
                </div>
            </div>

            <!-- Section: Aksi Cepat Guru -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100/60 shadow-xs">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 leading-tight">Aksi Cepat Guru</h3>
                        <p class="text-xs text-slate-400 font-normal mt-0.5">Pintasan praktis mengajar hari ini</p>
                    </div>
                </div>

                <div class="space-y-2.5">
                    <!-- Option 1: Buat Tugas Baru -->
                    <a href="{{ route('guru.tugas') }}" class="p-3 rounded-xl border border-slate-200/80 bg-slate-50/60 hover:bg-blue-50/60 hover:border-blue-200 transition-all flex items-center justify-between group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            </div>
                            <span class="text-xs font-bold text-slate-800 group-hover:text-blue-600 transition-colors">Buat Tugas Baru</span>
                        </div>
                        <svg class="w-4 h-4 text-slate-400 group-hover:text-blue-600 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>

                    <!-- Option 2: Unggah Materi -->
                    <a href="{{ route('guru.materi') }}" class="p-3 rounded-xl border border-slate-200/80 bg-slate-50/60 hover:bg-emerald-50/60 hover:border-emerald-200 transition-all flex items-center justify-between group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-700 text-white flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            </div>
                            <span class="text-xs font-bold text-slate-800 group-hover:text-emerald-700 transition-colors">Unggah Materi</span>
                        </div>
                        <svg class="w-4 h-4 text-slate-400 group-hover:text-emerald-700 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>

                    <!-- Option 3: Buat Kuis Baru -->
                    <a href="{{ route('guru.kuis.index') }}" class="p-3 rounded-xl border border-slate-200/80 bg-slate-50/60 hover:bg-amber-50/60 hover:border-amber-200 transition-all flex items-center justify-between group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-amber-600 text-white flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                            </div>
                            <span class="text-xs font-bold text-slate-800 group-hover:text-amber-700 transition-colors">Buat Kuis Baru</span>
                        </div>
                        <svg class="w-4 h-4 text-slate-400 group-hover:text-amber-700 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>

                    <!-- Option 4: Buat Ujian Baru -->
                    <a href="{{ route('guru.ujian.create') }}" class="p-3 rounded-xl border border-slate-200/80 bg-slate-50/60 hover:bg-blue-50/60 hover:border-blue-200 transition-all flex items-center justify-between group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-700 text-white flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <span class="text-xs font-bold text-slate-800 group-hover:text-blue-700 transition-colors">Buat Ujian Baru</span>
                        </div>
                        <svg class="w-4 h-4 text-slate-400 group-hover:text-blue-700 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
