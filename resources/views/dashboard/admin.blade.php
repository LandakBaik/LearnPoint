@extends('layouts.app')

@section('title', 'Dashboard Admin / Operator')

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
                Selamat Datang, {{ auth()->user()->name }}!
            </h1>
            <p class="text-blue-100 text-sm sm:text-base leading-relaxed font-normal">
                Semoga harimu menyenangkan dalam mengelola, menganalisis, dan memantau seluruh ekosistem e-learning sekolah.
            </p>
        </div>
    </div>

    <!-- BEGIN: MetricCardsGrid -->
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" data-purpose="metrics-cards">
        <!-- Card 1: Total Siswa -->
        <div class="bg-white border border-blue-100 rounded-2xl p-5 shadow-sm hover:shadow-md hover:border-blue-200 transition-all duration-200 flex items-center gap-4 relative overflow-hidden group">
            <div class="absolute top-0 left-0 right-0 h-1 bg-blue-600 rounded-t-2xl"></div>
            <div class="w-13 h-13 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0 p-3 shadow-xs border border-blue-100/60 group-hover:scale-105 transition-transform">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <span class="text-[11px] font-bold uppercase tracking-wider text-blue-700/90 block">TOTAL SISWA</span>
                <div class="text-2xl font-extrabold text-slate-900 leading-tight mt-1">{{ $totalSiswa }}</div>
                <div class="flex items-center justify-between gap-2 mt-2 pt-1 border-t border-slate-100">
                    <span class="text-xs text-slate-400 truncate">Akun aktif</span>
                    <a class="text-xs font-semibold text-blue-600 hover:text-blue-700 flex items-center gap-1 group/link flex-shrink-0" href="{{ route('siswa.index') }}">
                        Lihat Detail <span class="group-hover/link:translate-x-0.5 transition-transform">→</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Card 2: Total Guru -->
        <div class="bg-white border border-emerald-200/60 rounded-2xl p-5 shadow-sm hover:shadow-md hover:border-emerald-300 transition-all duration-200 flex items-center gap-4 relative overflow-hidden group">
            <div class="absolute top-0 left-0 right-0 h-1 bg-emerald-700 rounded-t-2xl"></div>
            <div class="w-13 h-13 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center flex-shrink-0 p-3 shadow-xs border border-emerald-200/60 group-hover:scale-105 transition-transform">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700 block">TOTAL GURU</span>
                <div class="text-2xl font-extrabold text-slate-900 leading-tight mt-1">{{ $totalGuru }}</div>
                <div class="flex items-center justify-between gap-2 mt-2 pt-1 border-t border-slate-100">
                    <span class="text-xs text-slate-400 truncate">Pengajar aktif</span>
                    <a class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 flex items-center gap-1 group/link flex-shrink-0" href="{{ route('guru.index') }}">
                        Lihat Detail <span class="group-hover/link:translate-x-0.5 transition-transform">→</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Card 3: Jumlah Kelas -->
        <div class="bg-white border border-amber-200/60 rounded-2xl p-5 shadow-sm hover:shadow-md hover:border-amber-300 transition-all duration-200 flex items-center gap-4 relative overflow-hidden group">
            <div class="absolute top-0 left-0 right-0 h-1 bg-amber-600 rounded-t-2xl"></div>
            <div class="w-13 h-13 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0 p-3 shadow-xs border border-amber-200/60 group-hover:scale-105 transition-transform">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-700 block">JUMLAH KELAS</span>
                <div class="text-2xl font-extrabold text-slate-900 leading-tight mt-1">{{ $totalKelas }}</div>
                <div class="flex items-center justify-between gap-2 mt-2 pt-1 border-t border-slate-100">
                    <span class="text-xs text-slate-400 truncate">Kelas terdaftar</span>
                    <a class="text-xs font-semibold text-amber-700 hover:text-amber-800 flex items-center gap-1 group/link flex-shrink-0" href="{{ route('kelas.index') }}">
                        Lihat Detail <span class="group-hover/link:translate-x-0.5 transition-transform">→</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Card 4: Total Mapel -->
        <div class="bg-white border border-blue-200/60 rounded-2xl p-5 shadow-sm hover:shadow-md hover:border-blue-300 transition-all duration-200 flex items-center gap-4 relative overflow-hidden group">
            <div class="absolute top-0 left-0 right-0 h-1 bg-blue-700 rounded-t-2xl"></div>
            <div class="w-13 h-13 rounded-2xl bg-blue-50 text-blue-700 flex items-center justify-center flex-shrink-0 p-3 shadow-xs border border-blue-100/60 group-hover:scale-105 transition-transform">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <span class="text-[11px] font-bold uppercase tracking-wider text-blue-700 block">TOTAL MAPEL</span>
                <div class="text-2xl font-extrabold text-slate-900 leading-tight mt-1">{{ $totalMapel }}</div>
                <div class="flex items-center justify-between gap-2 mt-2 pt-1 border-t border-slate-100">
                    <span class="text-xs text-slate-400 truncate">Mata pelajaran aktif</span>
                    <a class="text-xs font-semibold text-blue-700 hover:text-blue-800 flex items-center gap-1 group/link flex-shrink-0" href="{{ route('mapel.index') }}">
                        Lihat Detail <span class="group-hover/link:translate-x-0.5 transition-transform">→</span>
                    </a>
                </div>
            </div>
        </div>
    </section>
    <!-- END: MetricCardsGrid -->

    <!-- BEGIN: VisualizationsSection -->
    <section class="grid grid-cols-1 lg:grid-cols-12 gap-6" data-purpose="charts-row">
        <!-- Left Chart: Komposisi Pengguna (Donut) -->
        @php
            $totalAccounts = array_sum($userRoleCounts);
            $adminCount = $userRoleCounts['operator'] ?? 0;
            $guruCount = $userRoleCounts['guru'] ?? 0;
            $siswaCount = $userRoleCounts['siswa'] ?? 0;
            $kepsekCount = $userRoleCounts['kepala_sekolah'] ?? 0;

            $adminPct = $totalAccounts > 0 ? round(($adminCount / $totalAccounts) * 100, 1) : 0;
            $guruPct = $totalAccounts > 0 ? round(($guruCount / $totalAccounts) * 100, 1) : 0;
            $siswaPct = $totalAccounts > 0 ? round(($siswaCount / $totalAccounts) * 100, 1) : 0;
            $kepsekPct = $totalAccounts > 0 ? round(($kepsekCount / $totalAccounts) * 100, 1) : 0;
        @endphp
        <div class="lg:col-span-5 bg-white border border-slate-200/80 rounded-2xl p-6 flex flex-col justify-between shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                <div class="flex items-center gap-2.5 font-bold text-slate-800 text-sm">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100/60 shadow-xs">
                        <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"></path>
                            <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"></path>
                        </svg>
                    </div>
                    <span>Komposisi Pengguna</span>
                </div>
                <a class="text-xs font-semibold text-blue-600 hover:text-blue-700 flex items-center gap-1 group/link" href="{{ route('users.index') }}">
                    Lihat Detail <span class="group-hover/link:translate-x-0.5 transition-transform">→</span>
                </a>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-around py-6 gap-6 my-auto">
                <div class="relative w-40 h-40 flex items-center justify-center shrink-0">
                    <canvas id="userRoleChart" class="w-full h-full"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none text-center">
                        <span class="text-2xl font-extrabold text-slate-900 leading-none">{{ $totalAccounts }}</span>
                        <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mt-1">Total Akun</span>
                    </div>
                </div>

                <div class="flex-1 max-w-[210px] w-full space-y-3">
                    <div class="flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                            <span class="font-medium text-slate-700">Admin</span>
                        </div>
                        <span class="font-bold text-slate-900">{{ $adminCount }}&nbsp;&nbsp;<span class="font-normal text-slate-400">({{ $adminPct }}%)</span></span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                            <span class="font-medium text-slate-700">Guru</span>
                        </div>
                        <span class="font-bold text-slate-900">{{ $guruCount }}&nbsp;&nbsp;<span class="font-normal text-slate-400">({{ $guruPct }}%)</span></span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                            <span class="font-medium text-slate-700">Siswa</span>
                        </div>
                        <span class="font-bold text-slate-900">{{ $siswaCount }}&nbsp;&nbsp;<span class="font-normal text-slate-400">({{ $siswaPct }}%)</span></span>
                    </div>
                    @if($kepsekCount > 0)
                    <div class="flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                            <span class="font-medium text-slate-700">Kepsek</span>
                        </div>
                        <span class="font-bold text-slate-900">{{ $kepsekCount }}&nbsp;&nbsp;<span class="font-normal text-slate-400">({{ $kepsekPct }}%)</span></span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Chart: Siswa dan Kelas per Tingkat (Bar Chart) -->
        <div class="lg:col-span-7 bg-white border border-slate-200/80 rounded-2xl p-6 flex flex-col justify-between shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5 font-bold text-slate-800 text-sm">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100/60 shadow-xs">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </div>
                    <span>Siswa dan Kelas per Tingkat</span>
                </div>
                <a class="text-xs font-semibold text-blue-600 hover:text-blue-700 flex items-center gap-1 group/link" href="{{ route('kelas.index') }}">
                    Lihat Detail <span class="group-hover/link:translate-x-0.5 transition-transform">→</span>
                </a>
            </div>

            <div class="flex justify-end gap-5 py-2 text-xs">
                <div class="flex items-center gap-2">
                    <span class="w-3.5 h-3.5 rounded-sm shadow-xs bg-blue-600"></span>
                    <span class="text-slate-600 font-medium">Jumlah Siswa</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3.5 h-3.5 rounded-sm shadow-xs bg-amber-500"></span>
                    <span class="text-slate-600 font-medium">Jumlah Kelas</span>
                </div>
            </div>

            <div class="w-full h-52 relative">
                <canvas id="tingkatanChart" class="w-full h-full"></canvas>
            </div>
        </div>
    </section>
    <!-- END: VisualizationsSection -->

    <!-- BEGIN: RecentActivitiesSection -->
    <section class="bg-white border border-slate-200/80 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow" data-purpose="recent-activities-table">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-white">
            <div class="flex items-center gap-2.5 text-sm font-bold text-slate-800">
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100/60 shadow-xs">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                </div>
                <span>Aktivitas Terbaru</span>
            </div>
            <a class="text-xs font-semibold text-blue-600 hover:text-blue-700 bg-blue-50/80 px-3 py-1.5 rounded-lg border border-blue-100/60 flex items-center gap-1 group transition-colors" href="{{ route('users.index') }}">
                Lihat Semua <span class="group-hover:translate-x-0.5 transition-transform">→</span>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200/60">
                        <th class="px-6 py-3.5">Aktivitas</th>
                        <th class="px-6 py-3.5">Pengguna</th>
                        <th class="px-6 py-3.5">Waktu</th>
                        <th class="px-6 py-3.5">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="px-6 py-4 font-semibold text-slate-800 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100/60 shadow-xs shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </div>
                            <span>Manajemen data siswa & akun</span>
                        </td>
                        <td class="px-6 py-4 text-slate-600 font-medium">{{ auth()->user()->name }}</td>
                        <td class="px-6 py-4 text-slate-500">Hari ini, {{ date('H:i') }} WIB</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60 shadow-xs">
                                <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20">
                                    <path clip-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" fill-rule="evenodd"></path>
                                </svg>
                                Berhasil
                            </span>
                        </td>
                    </tr>
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="px-6 py-4 font-semibold text-slate-800 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100/60 shadow-xs shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </div>
                            <span>Sinkronisasi data kelas & ruang</span>
                        </td>
                        <td class="px-6 py-4 text-slate-600 font-medium">Sistem LearnPoint</td>
                        <td class="px-6 py-4 text-slate-500">Kemarin, 15:20 WIB</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200/60 shadow-xs">
                                <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20">
                                    <path clip-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" fill-rule="evenodd"></path>
                                </svg>
                                Diperbarui
                            </span>
                        </td>
                    </tr>
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="px-6 py-4 font-semibold text-slate-800 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-200/60 shadow-xs shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </div>
                            <span>Daftar mata pelajaran & silabus</span>
                        </td>
                        <td class="px-6 py-4 text-slate-600 font-medium">{{ auth()->user()->name }}</td>
                        <td class="px-6 py-4 text-slate-500">2 hari yang lalu</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60 shadow-xs">
                                <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20">
                                    <path clip-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" fill-rule="evenodd"></path>
                                </svg>
                                Berhasil
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
    <!-- END: RecentActivitiesSection -->

    <!-- BEGIN: QuickActions -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <h4 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            Aksi Cepat Admin
        </h4>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
            <a href="{{ route('users.index') }}" class="p-3.5 rounded-xl bg-slate-50/70 border border-slate-200/80 hover:bg-blue-50/60 hover:border-blue-200 transition-all group flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-800 group-hover:text-blue-600 transition-colors">Kelola Akun</p>
                    <p class="text-[11px] text-slate-400">Akses seluruh akun sistem</p>
                </div>
            </a>

            <a href="{{ route('guru.index') }}" class="p-3.5 rounded-xl bg-slate-50/70 border border-slate-200/80 hover:bg-emerald-50/60 hover:border-emerald-200 transition-all group flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-700 text-white flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2H9.17A3.001 3.001 0 0112 14z" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-800 group-hover:text-emerald-700 transition-colors">Data Guru</p>
                    <p class="text-[11px] text-slate-400">Kelola pengajar & NUPTK</p>
                </div>
            </a>

            <a href="{{ route('siswa.index') }}" class="p-3.5 rounded-xl bg-slate-50/70 border border-slate-200/80 hover:bg-blue-50/60 hover:border-blue-200 transition-all group flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-800 group-hover:text-blue-600 transition-colors">Data Siswa</p>
                    <p class="text-[11px] text-slate-400">Daftar siswa & rombel</p>
                </div>
            </a>

            <a href="{{ route('kelas.index') }}" class="p-3.5 rounded-xl bg-slate-50/70 border border-slate-200/80 hover:bg-amber-50/60 hover:border-amber-200 transition-all group flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-600 text-white flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-800 group-hover:text-amber-700 transition-colors">Kelola Kelas</p>
                    <p class="text-[11px] text-slate-400">Tingkat & wali kelas</p>
                </div>
            </a>

            <a href="{{ route('mapel.index') }}" class="p-3.5 rounded-xl bg-slate-50/70 border border-slate-200/80 hover:bg-blue-50/60 hover:border-blue-200 transition-all group flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-700 text-white flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-800 group-hover:text-blue-700 transition-colors">Mata Pelajaran</p>
                    <p class="text-[11px] text-slate-400">Daftar mapel & kurikulum</p>
                </div>
            </a>

            <a href="{{ route('admin.jadwal.index') }}" class="p-3.5 rounded-xl bg-slate-50/70 border border-slate-200/80 hover:bg-indigo-50/60 hover:border-indigo-200 transition-all group flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-800 group-hover:text-indigo-600 transition-colors">Kelola Jadwal (Foto)</p>
                    <p class="text-[11px] text-slate-400">Upload jadwal kelas & guru</p>
                </div>
            </a>
        </div>
    </div>
    <!-- END: QuickActions -->
</div>

<!-- Chart.js CDN & Inisialisasi Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Donut Chart Komposisi Pengguna
    const roleCtx = document.getElementById('userRoleChart').getContext('2d');
    new Chart(roleCtx, {
        type: 'doughnut',
        data: {
            labels: ['Admin', 'Guru', 'Siswa'@if($kepsekCount > 0), 'Kepsek'@endif],
            datasets: [{
                data: [
                    {{ $adminCount }},
                    {{ $guruCount }},
                    {{ $siswaCount }}
                    @if($kepsekCount > 0), {{ $kepsekCount }}@endif
                ],
                backgroundColor: [
                    '#2563EB', // Blue Admin
                    '#047857', // Emerald Guru
                    '#F59E0B'  // Amber Siswa
                    @if($kepsekCount > 0), '#6366F1'@endif
                ],
                borderWidth: 2,
                borderColor: '#ffffff',
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#1E293B',
                    titleFont: { size: 12, family: "'Plus Jakarta Sans', sans-serif" },
                    bodyFont: { size: 12, family: "'Plus Jakarta Sans', sans-serif" },
                    cornerRadius: 8,
                    padding: 8,
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const val = context.parsed || 0;
                            return ` ${label}: ${val} Akun`;
                        }
                    }
                }
            },
            cutout: '72%'
        }
    });

    // 2. Bar Chart Persebaran Siswa & Kelas
    const tingkatanCtx = document.getElementById('tingkatanChart').getContext('2d');
    new Chart(tingkatanCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($tingkatanLabels) !!},
            datasets: [
                {
                    label: 'Jumlah Siswa',
                    data: {!! json_encode($siswaPerTingkat) !!},
                    backgroundColor: '#2563EB',
                    borderRadius: 4,
                    barPercentage: 0.55,
                    categoryPercentage: 0.6
                },
                {
                    label: 'Jumlah Kelas',
                    data: {!! json_encode($kelasPerTingkat) !!},
                    backgroundColor: '#F59E0B',
                    borderRadius: 4,
                    barPercentage: 0.55,
                    categoryPercentage: 0.6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#1E293B',
                    titleFont: { size: 12, family: "'Plus Jakarta Sans', sans-serif" },
                    bodyFont: { size: 12, family: "'Plus Jakarta Sans', sans-serif" },
                    cornerRadius: 8,
                    padding: 8
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        color: '#94A3B8',
                        font: {
                            size: 11,
                            family: "'Plus Jakarta Sans', sans-serif"
                        }
                    },
                    grid: {
                        color: '#F1F5F9'
                    },
                    border: {
                        dash: [4, 4]
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#475569',
                        font: {
                            size: 11,
                            weight: '600',
                            family: "'Plus Jakarta Sans', sans-serif"
                        }
                    }
                }
            }
        }
    });
});
</script>
@endsection
