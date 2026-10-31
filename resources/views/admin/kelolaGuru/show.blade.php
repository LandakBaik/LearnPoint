@extends('layouts.app')

@section('title', 'Detail Guru: ' . $guru->nama)

@section('content')
@php
    $cleanPhone = preg_replace('/[^0-9]/', '', $guru->nomor_telepon ?? '');
    if ($cleanPhone && str_starts_with($cleanPhone, '0')) {
        $waNumber = '62' . substr($cleanPhone, 1);
    } else {
        $waNumber = $cleanPhone;
    }

    $nameWords = explode(' ', trim($guru->nama));
    $initials = count($nameWords) >= 2
        ? strtoupper(substr($nameWords[0], 0, 1) . substr($nameWords[1], 0, 1))
        : strtoupper(substr($guru->nama, 0, 2));

    $groupedPengampu = $guru->pengampuKelases->groupBy('guru_mapel_id');
    $totalMapelDiampu = $guru->guruMapels()->has('pengampuKelases')->count();
    $totalKelasDiampu = $guru->pengampuKelases->pluck('kelas_id')->unique()->count();
@endphp

<div class="max-w-6xl mx-auto space-y-6" x-data="{ modalOpen: false, modalImg: '', modalTitle: '', assignModalOpen: false }">

    <!-- Top Navigation & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('guru.index') }}" 
               class="p-2.5 rounded-xl bg-white border border-slate-200/80 text-slate-500 hover:text-blue-600 hover:bg-blue-50/60 hover:border-blue-200 transition-all shadow-2xs group"
               title="Kembali ke Data Guru">
                <svg class="w-5 h-5 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-0.5">
                    <a href="{{ route('dashboard') }}" class="hover:text-blue-600 transition-colors">Dashboard</a>
                    <span>/</span>
                    <a href="{{ route('guru.index') }}" class="hover:text-blue-600 transition-colors">Data Guru</a>
                    <span>/</span>
                    <span class="text-blue-600 font-bold">Detail Profil Guru</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight leading-tight">
                    Detail Profil Guru
                </h1>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2.5">
            <button @click="assignModalOpen = true" 
                    type="button" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-md shadow-blue-500/20 hover:shadow-lg hover:shadow-blue-500/30 transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tugaskan Mapel &amp; Kelas</span>
            </button>

            <a href="{{ route('guru.edit', $guru->id) }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs border border-slate-200/80 shadow-2xs transition-colors">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>Edit Data</span>
            </a>
        </div>
    </div>

    <!-- Teacher Hero Identity Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-blue-50 via-indigo-50/40 to-blue-50/20 border border-blue-100/90 shadow-sm p-6 sm:p-8">
        <!-- Ambient decorative shapes -->
        <div class="absolute -right-8 -top-8 w-48 h-48 rounded-full bg-blue-400/10 blur-2xl pointer-events-none"></div>
        <div class="absolute right-40 -bottom-10 w-40 h-40 rounded-full bg-indigo-400/10 blur-xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5">
                <!-- Avatar with Initials -->
                <div class="w-20 h-20 rounded-2xl bg-gradient-to-tr from-blue-700 to-blue-500 text-white font-extrabold text-2xl flex items-center justify-center shadow-lg shadow-blue-500/25 ring-4 ring-white shrink-0">
                    {{ $initials }}
                </div>

                <div class="space-y-1.5">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-tight">
                            {{ $guru->nama }}
                        </h2>

                        @if($guru->jenis_kelamin === 'Laki-laki' || $guru->jenis_kelamin === 'L')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-100/80 text-blue-700 border border-blue-200/60 shadow-2xs">
                                <svg class="w-3.5 h-3.5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                </svg>
                                Laki-laki
                            </span>
                        @elseif($guru->jenis_kelamin === 'Perempuan' || $guru->jenis_kelamin === 'P')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100/80 text-rose-700 border border-rose-200/60 shadow-2xs">
                                <svg class="w-3.5 h-3.5 text-rose-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                </svg>
                                Perempuan
                            </span>
                        @endif

                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Guru Pengajar Aktif
                        </span>
                    </div>

                    <!-- Meta details row -->
                    <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500 font-medium">
                        <div class="flex items-center gap-1.5 bg-white/80 border border-slate-200/80 px-2.5 py-1 rounded-lg text-slate-700 shadow-2xs">
                            <span class="text-slate-400 font-semibold">NUPTK:</span>
                            <span class="font-bold text-slate-800">{{ $guru->nuptk }}</span>
                        </div>

                        <span class="text-slate-300">|</span>

                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            @if($guru->kelas)
                                <span class="text-slate-700 font-semibold">
                                    Wali Kelas: <strong class="text-blue-700">{{ $guru->kelas->nama_kelas }}</strong>
                                </span>
                            @else
                                <span class="text-slate-400 italic">Bukan Wali Kelas</span>
                            @endif
                        </div>

                        <span class="text-slate-300">|</span>

                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <span>Telp: <strong class="text-slate-800">{{ $guru->nomor_telepon ?? '-' }}</strong></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Metric / Highlight Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: NUPTK -->
        <div class="bg-white border border-blue-100 rounded-2xl p-5 shadow-xs hover:shadow-md hover:border-blue-200 transition-all duration-200 flex items-center gap-4 relative overflow-hidden group">
            <div class="absolute top-0 left-0 right-0 h-1 bg-blue-600 rounded-t-2xl"></div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100/60 group-hover:scale-105 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2H9.17A3.001 3.001 0 0112 14z" />
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <span class="text-[11px] font-bold uppercase tracking-wider text-blue-700/90 block">NUPTK GURU</span>
                <div class="text-xl font-extrabold text-slate-900 leading-tight mt-0.5 tracking-tight truncate">{{ $guru->nuptk }}</div>
                <span class="text-[11px] text-slate-400 mt-1 block">Nomor Pendidik Resmi</span>
            </div>
        </div>

        <!-- Card 2: Status Wali Kelas -->
        <div class="bg-white border border-amber-200/60 rounded-2xl p-5 shadow-xs hover:shadow-md hover:border-amber-300 transition-all duration-200 flex items-center gap-4 relative overflow-hidden group">
            <div class="absolute top-0 left-0 right-0 h-1 bg-amber-600 rounded-t-2xl"></div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 border border-amber-200/60 group-hover:scale-105 transition-transform">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 3L1 9L12 15L21 10.09V17H23V9M5 13.18V17.18C5 19.94 8.13 22 12 22C15.87 22 19 19.94 19 17.18V13.18L12 17L5 13.18Z"></path>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-700 block">WALI KELAS</span>
                <div class="text-xl font-extrabold text-slate-900 leading-tight mt-0.5 truncate">
                    {{ $guru->kelas->nama_kelas ?? 'Bukan Wali' }}
                </div>
                <div class="mt-1 flex items-center justify-between">
                    <span class="text-[11px] text-slate-400">
                        {{ $guru->kelas ? $guru->kelas->siswas->count() . ' Siswa' : 'Pengajar biasa' }}
                    </span>
                    @if($guru->kelas)
                        <a href="{{ route('kelas.show', $guru->kelas->id) }}" class="text-[11px] font-semibold text-amber-700 hover:text-amber-800 flex items-center gap-0.5">
                            Buka &rarr;
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Card 3: Mapel Diampu -->
        <div class="bg-white border border-blue-200/60 rounded-2xl p-5 shadow-xs hover:shadow-md hover:border-blue-300 transition-all duration-200 flex items-center gap-4 relative overflow-hidden group">
            <div class="absolute top-0 left-0 right-0 h-1 bg-blue-700 rounded-t-2xl"></div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-700 flex items-center justify-center shrink-0 border border-blue-100/60 group-hover:scale-105 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <span class="text-[11px] font-bold uppercase tracking-wider text-blue-700 block">MAPEL DIAMPU</span>
                <div class="text-xl font-extrabold text-slate-900 leading-tight mt-0.5">
                    {{ $totalMapelDiampu }} Mapel
                </div>
                <span class="text-[11px] text-slate-400 mt-1 block">Mata pelajaran aktif</span>
            </div>
        </div>

        <!-- Card 4: Total Kelas Mengajar -->
        <div class="bg-white border border-indigo-200/60 rounded-2xl p-5 shadow-xs hover:shadow-md hover:border-indigo-300 transition-all duration-200 flex items-center gap-4 relative overflow-hidden group">
            <div class="absolute top-0 left-0 right-0 h-1 bg-indigo-600 rounded-t-2xl"></div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 border border-indigo-200/60 group-hover:scale-105 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-700 block">TOTAL KELAS</span>
                <div class="text-xl font-extrabold text-slate-900 leading-tight mt-0.5">
                    {{ $totalKelasDiampu }} Kelas
                </div>
                <span class="text-[11px] text-slate-400 mt-1 block">{{ $guru->pengampuKelases->count() }} alokasi mengajar</span>
            </div>
        </div>
    </div>

    <!-- Main Content Layout (Grid 12 Columns) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left Column: Informasi Biodata & Kontak (5 Columns) -->
        <div class="lg:col-span-5 space-y-6">

            <!-- Card: Biodata & Kontak Guru -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs hover:shadow-sm transition-shadow space-y-5">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100/60 shadow-2xs">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 leading-tight">Data Profil Pendidik</h3>
                            <p class="text-xs text-slate-400 font-normal mt-0.5">Informasi kontak dan identitas guru</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-3.5 text-xs">
                    <!-- Nama Lengkap -->
                    <div class="p-3.5 rounded-xl bg-slate-50/70 border border-slate-200/70">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Nama Lengkap</span>
                        <p class="text-sm font-bold text-slate-800 leading-snug">{{ $guru->nama }}</p>
                    </div>

                    <!-- NUPTK -->
                    <div class="p-3.5 rounded-xl bg-slate-50/70 border border-slate-200/70">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">NUPTK</span>
                        <p class="text-sm font-bold text-slate-800 leading-snug">{{ $guru->nuptk }}</p>
                    </div>

                    <!-- Jenis Kelamin -->
                    <div class="p-3.5 rounded-xl bg-slate-50/70 border border-slate-200/70">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Jenis Kelamin</span>
                        <p class="text-sm font-bold text-slate-800 leading-snug">{{ $guru->jenis_kelamin ?? '-' }}</p>
                    </div>

                    <!-- Nomor Telepon & WhatsApp -->
                    <div class="p-3.5 rounded-xl bg-slate-50/70 border border-slate-200/70 flex flex-col justify-between">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Nomor Telepon / WhatsApp</span>
                            <p class="text-sm font-bold text-slate-800 leading-snug">{{ $guru->nomor_telepon ?? '-' }}</p>
                        </div>
                        @if($waNumber)
                            <div class="mt-2.5 pt-2 border-t border-slate-200/60">
                                <a href="https://wa.me/{{ $waNumber }}" target="_blank" 
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-[11px] shadow-2xs transition-colors">
                                    <i class="fa-brands fa-whatsapp text-xs"></i>
                                    <span>Kirim Pesan WhatsApp</span>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Card: Akun Login Portal E-Learning -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs hover:shadow-sm transition-shadow space-y-4">
                <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100/60 shadow-2xs">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 leading-tight">Akun Login Guru</h3>
                            <p class="text-xs text-slate-400 font-normal mt-0.5">Akses pengguna di portal LearnPoint</p>
                        </div>
                    </div>
                    @if($guru->user)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Terhubung
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200/60">
                            Belum Ada Akun
                        </span>
                    @endif
                </div>

                @if($guru->user)
                    <div class="space-y-3 text-xs">
                        <div class="p-3 rounded-xl bg-slate-50/70 border border-slate-200/70">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-0.5">Email Pengguna</span>
                            <p class="text-xs font-bold text-slate-800 truncate">{{ $guru->user->email }}</p>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50/70 border border-slate-200/70 flex items-center justify-between">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-0.5">Peran Sistem</span>
                                <p class="text-xs font-bold text-slate-800 capitalize">{{ $guru->user->role }}</p>
                            </div>
                            <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 font-semibold text-[10px] border border-blue-100">
                                Hak Akses Guru
                            </span>
                        </div>
                    </div>
                @else
                    <div class="p-4 rounded-xl bg-amber-50/60 border border-amber-200/60 text-xs text-amber-800 flex items-center justify-between">
                        <span>Akun pengguna untuk guru ini belum dibuat.</span>
                        <a href="{{ route('users.create') }}" class="font-bold text-amber-900 hover:underline shrink-0">
                            + Buat Akun &rarr;
                        </a>
                    </div>
                @endif
            </div>

        </div>

        <!-- Right Column: Daftar Mapel & Foto Jadwal (7 Columns) -->
        <div class="lg:col-span-7 space-y-6">

            <!-- Card: Daftar Mata Pelajaran & Kelas Mengajar -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs hover:shadow-sm transition-shadow space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3.5 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100/60 shadow-2xs">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 leading-tight">Alokasi Mapel &amp; Kelas Mengajar</h3>
                            <p class="text-xs text-slate-400 font-normal mt-0.5">Daftar kelas yang diajar oleh guru ini</p>
                        </div>
                    </div>
                    <button @click="assignModalOpen = true" 
                            type="button" 
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold rounded-xl border border-blue-100/80 transition-colors shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>+ Tugaskan Kelas</span>
                    </button>
                </div>

                @if($groupedPengampu->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-50/70 border-b border-slate-200/70 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                    <th class="py-3 px-4">Mata Pelajaran</th>
                                    <th class="py-3 px-4">Kelas Diampu</th>
                                    <th class="py-3 px-4 text-center">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($groupedPengampu as $gmId => $items)
                                    @php
                                        $first = $items->first();
                                    @endphp
                                    <tr class="hover:bg-slate-50/60 transition-colors">
                                        <td class="py-3.5 px-4 font-bold text-slate-800 align-top">
                                            <div class="space-y-1">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs font-bold text-slate-900">{{ $first->mapel->nama_mapel ?? '-' }}</span>
                                                    @if($first->mapel)
                                                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-100 font-bold">
                                                            KKM: {{ $first->mapel->kkm }}
                                                        </span>
                                                    @endif
                                                </div>
                                                @if($first->mapel)
                                                    <a href="{{ route('mapel.show', $first->mapel->id) }}" class="inline-block text-[11px] font-semibold text-blue-600 hover:underline">
                                                        Detail Mapel &rarr;
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4 align-top">
                                            <div class="flex flex-wrap items-center gap-1.5">
                                                @foreach($items as $gm)
                                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-50/70 border border-blue-100 text-blue-800 text-xs font-semibold">
                                                        <a href="{{ route('kelas.show', $gm->kelas->id) }}" class="hover:underline">
                                                            {{ $gm->kelas->nama_kelas ?? '-' }}
                                                        </a>
                                                        <form action="{{ route('guru-mapel.destroy', $gm->id) }}" method="POST" data-confirm="Hapus penugasan mapel {{ $first->mapel->nama_mapel ?? '' }} di kelas {{ $gm->kelas->nama_kelas ?? '' }} untuk guru ini?" class="inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-slate-400 hover:text-rose-600 transition-colors ml-0.5" title="Lepas kelas {{ $gm->kelas->nama_kelas ?? '' }}">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                                </svg>
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4 text-center align-top">
                                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                                                {{ $items->count() }} Kelas
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-6 text-center rounded-xl border border-dashed border-slate-200 bg-slate-50/50 space-y-2">
                        <p class="text-xs font-bold text-slate-700">Guru ini belum memiliki penugasan mata pelajaran atau kelas.</p>
                        <p class="text-[11px] text-slate-400">Gunakan tombol di atas untuk mengalokasikan mapel dan kelas mengajar.</p>
                    </div>
                @endif
            </div>

            <!-- Card: Foto Jadwal Mengajar Guru per Mapel -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs hover:shadow-sm transition-shadow space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100/60 shadow-2xs">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-bold text-slate-900 leading-tight">Foto Jadwal Mengajar Guru</h3>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100/60">
                                    {{ $guru->guruMapels->count() }} Mapel
                                </span>
                            </div>
                            <p class="text-xs text-slate-400 font-normal mt-0.5">Daftar foto jadwal resmi per mata pelajaran</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.jadwal.index', ['tab' => 'guru']) }}" 
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold rounded-xl border border-blue-100/80 transition-colors shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        <span>Kelola di Menu Jadwal &rarr;</span>
                    </a>
                </div>

                @if($guru->guruMapels->count() > 0)
                    <div class="grid grid-cols-1 {{ $guru->guruMapels->count() > 1 ? 'md:grid-cols-2' : '' }} gap-4">
                        @foreach($guru->guruMapels as $gm)
                            <div class="rounded-2xl border {{ $gm->jadwal_url ? 'border-blue-100 bg-blue-50/20' : 'border-slate-200/80 bg-slate-50/40' }} p-4 flex flex-col justify-between space-y-3.5 hover:shadow-2xs transition-all">
                                <!-- Top Info -->
                                <div class="space-y-2">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-blue-700 bg-blue-50 border border-blue-100/60 px-2 py-0.5 rounded">
                                                Mata Pelajaran
                                            </span>
                                            <h4 class="font-bold text-slate-900 text-sm mt-1">
                                                {{ $gm->mapel->nama_mapel ?? 'Mapel #' . $gm->id }}
                                            </h4>
                                        </div>
                                        <div class="flex flex-col items-end gap-1">
                                            @if($gm->jadwal_url)
                                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200/60 flex items-center gap-1">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                    Ada Jadwal
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full bg-amber-50 text-amber-700 border border-amber-200/60">
                                                    Belum Ada
                                                </span>
                                            @endif
                                            @if($gm->mapel)
                                                <span class="text-[10px] text-slate-400 font-medium">KKM: {{ $gm->mapel->kkm }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Classes Chips -->
                                    <div>
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Kelas Diampu:</p>
                                        @if($gm->pengampuKelases->count() > 0)
                                            <div class="flex flex-wrap gap-1 mt-1">
                                                @foreach($gm->pengampuKelases as $pk)
                                                    <span class="px-2 py-0.5 rounded bg-white border border-slate-200 text-slate-700 text-[11px] font-semibold shadow-2xs">
                                                        {{ $pk->kelas->nama_kelas ?? '-' }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-[11px] text-slate-400 italic mt-0.5">Belum dialokasikan ke kelas</p>
                                        @endif
                                    </div>
                                </div>

                                <!-- Schedule Image Preview -->
                                @if($gm->jadwal_url)
                                    <div class="space-y-2.5">
                                        <div class="relative group rounded-xl overflow-hidden border border-slate-200/80 bg-white p-2 text-center cursor-pointer shadow-2xs hover:border-blue-300 transition-all"
                                             @click="modalOpen = true; modalImg = '{{ $gm->jadwal_url }}'; modalTitle = 'Jadwal {{ addslashes($gm->mapel->nama_mapel ?? 'Mapel') }} - {{ addslashes($guru->nama) }}'">
                                            <img src="{{ $gm->jadwal_url }}" 
                                                 alt="Jadwal {{ $gm->mapel->nama_mapel ?? 'Mapel' }} - {{ $guru->nama }}" 
                                                 class="max-h-48 w-auto mx-auto rounded-lg object-contain transition-transform duration-200 group-hover:scale-[1.02]">
                                            <div class="absolute inset-0 bg-slate-900/10 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center rounded-xl">
                                                <span class="px-3 py-1.5 bg-white/95 text-slate-800 text-xs font-bold rounded-lg shadow-md flex items-center gap-1.5">
                                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                                    </svg>
                                                    Perbesar
                                                </span>
                                            </div>
                                        </div>

                                        <div class="flex items-center justify-between text-xs pt-1">
                                            <button type="button" 
                                                    @click="modalOpen = true; modalImg = '{{ $gm->jadwal_url }}'; modalTitle = 'Jadwal {{ addslashes($gm->mapel->nama_mapel ?? 'Mapel') }} - {{ addslashes($guru->nama) }}'"
                                                    class="font-semibold text-blue-600 hover:text-blue-700 flex items-center gap-1 text-[11px]">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                <span>Lihat Penuh</span>
                                            </button>

                                            <a href="{{ $gm->jadwal_url }}" 
                                               download 
                                               class="inline-flex items-center gap-1 font-semibold text-slate-600 hover:text-slate-900 bg-white hover:bg-slate-50 px-2 py-1 rounded-lg border border-slate-200 transition-colors shadow-2xs text-[11px]">
                                                <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                </svg>
                                                <span>Unduh</span>
                                            </a>
                                        </div>
                                    </div>
                                @else
                                    <div class="p-5 text-center rounded-xl border border-dashed border-slate-200 bg-white space-y-1.5">
                                        <svg class="w-7 h-7 text-slate-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <p class="text-[11px] font-semibold text-slate-600">Belum ada foto jadwal</p>
                                        <a href="{{ route('admin.jadwal.index', ['tab' => 'guru']) }}" class="inline-block text-[11px] font-bold text-blue-600 hover:underline">
                                            + Upload Jadwal &rarr;
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-8 text-center rounded-2xl border border-dashed border-slate-200 bg-slate-50/50 space-y-2">
                        <svg class="w-9 h-9 text-slate-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="text-xs font-bold text-slate-700">Guru Belum Memiliki Alokasi Mapel</p>
                        <p class="text-[11px] text-slate-400">Tugaskan mata pelajaran terlebih dahulu untuk mengunggah foto jadwal mengajar.</p>
                    </div>
                @endif
            </div>

        </div>

    </div>

    <!-- Modal Zoom Foto Interaktif -->
    <div x-show="modalOpen" 
         style="display: none;"
         class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 transition-all"
         @click.self="modalOpen = false">
        <div class="relative max-w-4xl w-full bg-white rounded-3xl shadow-2xl overflow-hidden p-3 border border-slate-100">
            <div class="flex items-center justify-between p-3.5 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h4 class="font-bold text-slate-800 text-sm" x-text="modalTitle || 'Foto Jadwal Mengajar'"></h4>
                </div>
                <div class="flex items-center gap-2">
                    <a :href="modalImg" download class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span>Unduh</span>
                    </a>
                    <button @click="modalOpen = false" class="p-1.5 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            <div class="p-4 text-center max-h-[75vh] overflow-auto bg-slate-50/50 rounded-2xl">
                <img :src="modalImg" :alt="modalTitle || 'Jadwal Zoom'" class="mx-auto max-h-[70vh] object-contain rounded-xl shadow-xs">
            </div>
        </div>
    </div>

    <!-- Modal Penugasan Mapel & Kelas -->
    <div x-show="assignModalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <div class="min-h-screen px-4 text-center flex items-center justify-center">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" 
                 @click="assignModalOpen = false"></div>

            <!-- Modal Content -->
            <div class="relative bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 text-left shadow-2xl border border-slate-100 z-10 space-y-5">
                <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100/60 shadow-2xs">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900">Tugaskan Mengajar Mapel &amp; Kelas</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Alokasikan mata pelajaran dan kelas untuk {{ $guru->nama }}</p>
                        </div>
                    </div>
                    <button type="button" 
                            @click="assignModalOpen = false"
                            class="p-1.5 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form action="{{ route('guru-mapel.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="guru_id" value="{{ $guru->id }}">

                    <!-- Pilih Mapel -->
                    <div>
                        <label for="guru_modal_mapel_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Pilih Mata Pelajaran <span class="text-rose-500">*</span>
                        </label>
                        <select id="guru_modal_mapel_id" 
                                name="mapel_id" 
                                required 
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium text-slate-800">
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            @foreach($allMapels as $mapel)
                                <option value="{{ $mapel->id }}">{{ $mapel->nama_mapel }} (KKM: {{ $mapel->kkm }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Pilih Kelas (Multi-select Checkbox) -->
                    <div x-data="{
                        selectedClasses: [],
                        allClassIds: {{ $allKelases->pluck('id')->toJson() }},
                        toggleAll() {
                            if (this.selectedClasses.length === this.allClassIds.length) {
                                this.selectedClasses = [];
                            } else {
                                this.selectedClasses = [...this.allClassIds];
                            }
                        }
                    }" class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                Pilih Kelas yang Diampu <span class="text-rose-500">*</span>
                            </label>
                            <button type="button" 
                                    @click="toggleAll()" 
                                    class="text-[11px] font-bold text-blue-600 hover:text-blue-800 hover:underline">
                                <span x-text="selectedClasses.length === allClassIds.length ? 'Batalkan Semua' : 'Pilih Semua Kelas'"></span>
                            </button>
                        </div>

                        <div class="max-h-56 overflow-y-auto p-3 rounded-xl border border-slate-200 bg-slate-50/50 space-y-3">
                            @foreach($allKelases->groupBy('tingkatan') as $tingkat => $kelasList)
                                <div>
                                    <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1.5">Tingkat {{ $tingkat }}</p>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                        @foreach($kelasList as $kelas)
                                            <label class="flex items-center gap-2 p-2 rounded-lg bg-white border border-slate-200 hover:border-blue-300 hover:bg-blue-50/30 cursor-pointer transition-all text-xs font-medium text-slate-800 shadow-2xs">
                                                <input type="checkbox" 
                                                       name="kelas_ids[]" 
                                                       value="{{ $kelas->id }}"
                                                       x-model="selectedClasses"
                                                       class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                                                <span>{{ $kelas->nama_kelas }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <p class="text-[11px] text-slate-400">Centang satu atau beberapa kelas yang akan diajar untuk mata pelajaran ini.</p>
                    </div>

                    <div class="pt-3 flex items-center justify-end gap-3 border-t border-slate-100">
                        <button type="button" 
                                @click="assignModalOpen = false"
                                class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors">
                            Batal
                        </button>
                        <button type="submit" 
                                class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-500/20 transition-all">
                            Simpan Penugasan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection