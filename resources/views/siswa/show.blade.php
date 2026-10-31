@extends('layouts.app')

@section('title', 'Detail Siswa: ' . $siswa->nama_siswa)

@section('content')
@php
    $activeKelas = $siswa->kelas ?? $siswa->anggotaKelases->last()?->kelas;
    $classJadwalUrl = $activeKelas?->jadwal_url;
    $age = $siswa->tanggal_lahir ? \Carbon\Carbon::parse($siswa->tanggal_lahir)->age : null;
    
    $cleanPhone = preg_replace('/[^0-9]/', '', $siswa->nohp_wali ?? '');
    if ($cleanPhone && str_starts_with($cleanPhone, '0')) {
        $waNumber = '62' . substr($cleanPhone, 1);
    } else {
        $waNumber = $cleanPhone;
    }

    $nameWords = explode(' ', trim($siswa->nama_siswa));
    $initials = count($nameWords) >= 2
        ? strtoupper(substr($nameWords[0], 0, 1) . substr($nameWords[1], 0, 1))
        : strtoupper(substr($siswa->nama_siswa, 0, 2));
@endphp

<div class="max-w-6xl mx-auto space-y-6" x-data="{ modalOpen: false, modalImg: '' }">

    <!-- Top Navigation & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('siswa.index') }}" 
               class="p-2.5 rounded-xl bg-white border border-slate-200/80 text-slate-500 hover:text-blue-600 hover:bg-blue-50/60 hover:border-blue-200 transition-all shadow-2xs group"
               title="Kembali ke Daftar Siswa">
                <svg class="w-5 h-5 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-0.5">
                    <a href="{{ route('dashboard') }}" class="hover:text-blue-600 transition-colors">Dashboard</a>
                    <span>/</span>
                    <a href="{{ route('siswa.index') }}" class="hover:text-blue-600 transition-colors">Data Siswa</a>
                    <span>/</span>
                    <span class="text-blue-600 font-bold">Detail Siswa</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight leading-tight">
                    Detail Data Siswa
                </h1>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2.5">
            <a href="{{ route('siswa.edit', $siswa->id) }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-md shadow-blue-500/20 hover:shadow-lg hover:shadow-blue-500/30 transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>Edit Data Siswa</span>
            </a>
        </div>
    </div>

    <!-- Student Hero Identity Banner -->
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
                            {{ $siswa->nama_siswa }}
                        </h2>

                        @if($siswa->jenis_kelamin === 'L')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-100/80 text-blue-700 border border-blue-200/60 shadow-2xs">
                                <svg class="w-3.5 h-3.5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                </svg>
                                Laki-laki
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100/80 text-rose-700 border border-rose-200/60 shadow-2xs">
                                <svg class="w-3.5 h-3.5 text-rose-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                </svg>
                                Perempuan
                            </span>
                        @endif

                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Siswa Aktif
                        </span>
                    </div>

                    <!-- Meta details row -->
                    <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500 font-medium">
                        <div class="flex items-center gap-1.5 bg-white/80 border border-slate-200/80 px-2.5 py-1 rounded-lg text-slate-700 shadow-2xs">
                            <span class="text-slate-400 font-semibold">NIS:</span>
                            <span class="font-bold text-slate-800">{{ $siswa->nis }}</span>
                        </div>

                        <span class="text-slate-300">|</span>

                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-blue-600 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 3L1 9L12 15L21 10.09V17H23V9M5 13.18V17.18C5 19.94 8.13 22 12 22C15.87 22 19 19.94 19 17.18V13.18L12 17L5 13.18Z"></path>
                            </svg>
                            @if($activeKelas)
                                <span class="text-slate-700 font-semibold">
                                    Kelas {{ $activeKelas->nama_kelas }} (Tingkat {{ $activeKelas->tingkatan }})
                                </span>
                            @else
                                <span class="text-amber-600 italic font-semibold">Belum ditempatkan di kelas</span>
                            @endif
                        </div>

                        <span class="text-slate-300">|</span>

                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span>Wali: <strong class="text-slate-800">{{ $siswa->wali_murid }}</strong></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Metric / Highlight Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: NIS Resmi -->
        <div class="bg-white border border-blue-100 rounded-2xl p-5 shadow-xs hover:shadow-md hover:border-blue-200 transition-all duration-200 flex items-center gap-4 relative overflow-hidden group">
            <div class="absolute top-0 left-0 right-0 h-1 bg-blue-600 rounded-t-2xl"></div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100/60 group-hover:scale-105 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2H9.17A3.001 3.001 0 0112 14z" />
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <span class="text-[11px] font-bold uppercase tracking-wider text-blue-700/90 block">NIS RESMI</span>
                <div class="text-xl font-extrabold text-slate-900 leading-tight mt-0.5 tracking-tight truncate">{{ $siswa->nis }}</div>
                <span class="text-[11px] text-slate-400 mt-1 block">Nomor Induk Siswa</span>
            </div>
        </div>

        <!-- Card 2: Kelas & Tingkat -->
        <div class="bg-white border border-amber-200/60 rounded-2xl p-5 shadow-xs hover:shadow-md hover:border-amber-300 transition-all duration-200 flex items-center gap-4 relative overflow-hidden group">
            <div class="absolute top-0 left-0 right-0 h-1 bg-amber-600 rounded-t-2xl"></div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 border border-amber-200/60 group-hover:scale-105 transition-transform">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 3L1 9L12 15L21 10.09V17H23V9M5 13.18V17.18C5 19.94 8.13 22 12 22C15.87 22 19 19.94 19 17.18V13.18L12 17L5 13.18Z"></path>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-700 block">KELAS AKTIF</span>
                <div class="text-xl font-extrabold text-slate-900 leading-tight mt-0.5 truncate">
                    {{ $activeKelas->nama_kelas ?? 'Belum Ada' }}
                </div>
                <div class="mt-1 flex items-center justify-between">
                    <span class="text-[11px] text-slate-400">
                        {{ $activeKelas ? 'Tingkat ' . $activeKelas->tingkatan : 'Belum diatur' }}
                    </span>
                    @if($activeKelas)
                        <a href="{{ route('kelas.show', $activeKelas->id) }}" class="text-[11px] font-semibold text-amber-700 hover:text-amber-800 flex items-center gap-0.5">
                            Buka &rarr;
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Card 3: Akun Login LearnPoint -->
        <div class="bg-white border border-emerald-200/60 rounded-2xl p-5 shadow-xs hover:shadow-md hover:border-emerald-300 transition-all duration-200 flex items-center gap-4 relative overflow-hidden group">
            <div class="absolute top-0 left-0 right-0 h-1 bg-emerald-700 rounded-t-2xl"></div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0 border border-emerald-200/60 group-hover:scale-105 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700 block">AKUN PORTAL</span>
                <div class="text-sm font-bold text-slate-900 leading-tight mt-1 truncate" title="{{ $siswa->user->email ?? 'Belum ada akun' }}">
                    {{ $siswa->user->email ?? 'Belum Ada Akun' }}
                </div>
                <div class="mt-1 flex items-center gap-1.5">
                    @if($siswa->user)
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                        </span>
                    @else
                        <span class="text-[11px] text-amber-600 italic">Belum terhubung</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Card 4: Kontak Cepat Wali -->
        <div class="bg-white border border-blue-200/60 rounded-2xl p-5 shadow-xs hover:shadow-md hover:border-blue-300 transition-all duration-200 flex items-center gap-4 relative overflow-hidden group">
            <div class="absolute top-0 left-0 right-0 h-1 bg-blue-700 rounded-t-2xl"></div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-700 flex items-center justify-center shrink-0 border border-blue-100/60 group-hover:scale-105 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <span class="text-[11px] font-bold uppercase tracking-wider text-blue-700 block">KONTAK WALI</span>
                <div class="text-sm font-bold text-slate-900 leading-tight mt-1 truncate">
                    {{ $siswa->nohp_wali }}
                </div>
                <div class="mt-1">
                    @if($waNumber)
                        <a href="https://wa.me/{{ $waNumber }}" target="_blank" 
                           class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 hover:text-emerald-700">
                            <span>Hubungi WA</span>
                            <span class="text-xs">&rarr;</span>
                        </a>
                    @else
                        <span class="text-[11px] text-slate-400">Tidak ada nomor</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Layout (Grid 12 Columns) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left Column: Biodata & Kontak (7 Columns) -->
        <div class="lg:col-span-7 space-y-6">

            <!-- Card: Informasi Pribadi Siswa -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs hover:shadow-sm transition-shadow space-y-5">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100/60 shadow-2xs">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 leading-tight">Biodata Lengkap Siswa</h3>
                            <p class="text-xs text-slate-400 font-normal mt-0.5">Informasi identitas kependudukan siswa</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <!-- Nama Lengkap -->
                    <div class="p-3.5 rounded-xl bg-slate-50/70 border border-slate-200/70">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Nama Lengkap</span>
                        <p class="text-sm font-bold text-slate-800 leading-snug">{{ $siswa->nama_siswa }}</p>
                    </div>

                    <!-- NIS -->
                    <div class="p-3.5 rounded-xl bg-slate-50/70 border border-slate-200/70">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Nomor Induk Siswa (NIS)</span>
                        <p class="text-sm font-bold text-slate-800 leading-snug">{{ $siswa->nis }}</p>
                    </div>

                    <!-- Tanggal Lahir & Usia -->
                    <div class="p-3.5 rounded-xl bg-slate-50/70 border border-slate-200/70">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Tanggal Lahir &amp; Usia</span>
                        <p class="text-sm font-bold text-slate-800 leading-snug">
                            {{ \Carbon\Carbon::parse($siswa->tanggal_lahir)->translatedFormat('d F Y') }}
                            @if($age)
                                <span class="text-xs font-semibold text-blue-600">({{ $age }} Tahun)</span>
                            @endif
                        </p>
                    </div>

                    <!-- Jenis Kelamin -->
                    <div class="p-3.5 rounded-xl bg-slate-50/70 border border-slate-200/70">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Jenis Kelamin</span>
                        <p class="text-sm font-bold text-slate-800 leading-snug">
                            {{ $siswa->jenis_kelamin === 'L' ? 'Laki-laki (L)' : 'Perempuan (P)' }}
                        </p>
                    </div>

                    <!-- Alamat Lengkap -->
                    <div class="sm:col-span-2 p-3.5 rounded-xl bg-slate-50/70 border border-slate-200/70">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Alamat Tempat Tinggal</span>
                        <p class="text-xs font-medium text-slate-700 leading-relaxed">{{ $siswa->alamat }}</p>
                    </div>
                </div>
            </div>

            <!-- Card: Informasi Wali Murid & Kontak -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs hover:shadow-sm transition-shadow space-y-5">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center border border-emerald-200/60 shadow-2xs">
                            <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 leading-tight">Data Orang Tua / Wali</h3>
                            <p class="text-xs text-slate-400 font-normal mt-0.5">Kontak darurat dan penanggung jawab siswa</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <!-- Nama Wali -->
                    <div class="p-3.5 rounded-xl bg-slate-50/70 border border-slate-200/70">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Nama Orang Tua / Wali</span>
                        <p class="text-sm font-bold text-slate-800 leading-snug">{{ $siswa->wali_murid }}</p>
                    </div>

                    <!-- Nomor HP / WhatsApp -->
                    <div class="p-3.5 rounded-xl bg-slate-50/70 border border-slate-200/70 flex flex-col justify-between">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Nomor Telepon / WhatsApp</span>
                            <p class="text-sm font-bold text-slate-800 leading-snug">{{ $siswa->nohp_wali }}</p>
                        </div>
                        @if($waNumber)
                            <div class="mt-2 pt-2 border-t border-slate-200/60">
                                <a href="https://wa.me/{{ $waNumber }}" target="_blank" 
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-[11px] shadow-2xs transition-colors">
                                    <i class="fa-brands fa-whatsapp text-xs"></i>
                                    <span>Kirim WhatsApp</span>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Card: Akun Portal E-Learning -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs hover:shadow-sm transition-shadow space-y-4">
                <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100/60 shadow-2xs">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 leading-tight">Akun Login Siswa</h3>
                            <p class="text-xs text-slate-400 font-normal mt-0.5">Informasi akun untuk masuk ke portal LearnPoint</p>
                        </div>
                    </div>
                    @if($siswa->user)
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

                @if($siswa->user)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div class="p-3 rounded-xl bg-slate-50/70 border border-slate-200/70">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-0.5">Email Akun</span>
                            <p class="text-xs font-bold text-slate-800 truncate">{{ $siswa->user->email }}</p>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50/70 border border-slate-200/70">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-0.5">Peran / Role</span>
                            <p class="text-xs font-bold text-slate-800 capitalize">{{ $siswa->user->role }}</p>
                        </div>
                    </div>
                @else
                    <div class="p-4 rounded-xl bg-amber-50/60 border border-amber-200/60 text-xs text-amber-800 flex items-center justify-between">
                        <span>Akun login untuk siswa ini belum dibuat di sistem.</span>
                        <a href="{{ route('users.create') }}" class="font-bold text-amber-900 hover:underline shrink-0">
                            + Buat Akun &rarr;
                        </a>
                    </div>
                @endif
            </div>

        </div>

        <!-- Right Column: Informasi Akademik & Foto Jadwal Kelas (5 Columns) -->
        <div class="lg:col-span-5 space-y-6">

            <!-- Card: Info Kelas & Wali Kelas -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs hover:shadow-sm transition-shadow space-y-4">
                <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-200/60 shadow-2xs">
                            <svg class="w-4 h-4 text-amber-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 3L1 9L12 15L21 10.09V17H23V9M5 13.18V17.18C5 19.94 8.13 22 12 22C15.87 22 19 19.94 19 17.18V13.18L12 17L5 13.18Z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 leading-tight">Penempatan Kelas</h3>
                            <p class="text-xs text-slate-400 font-normal mt-0.5">Kelas belajar saat ini</p>
                        </div>
                    </div>
                    @if($activeKelas)
                        <a href="{{ route('kelas.show', $activeKelas->id) }}" 
                           class="text-xs font-semibold text-blue-600 hover:text-blue-700 flex items-center gap-1 group/link">
                            Detail Kelas <span class="group-hover/link:translate-x-0.5 transition-transform">&rarr;</span>
                        </a>
                    @endif
                </div>

                @if($activeKelas)
                    <div class="space-y-3 text-xs">
                        <div class="p-3.5 rounded-xl bg-slate-50/70 border border-slate-200/70 flex items-center justify-between">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-0.5">Nama Kelas</span>
                                <h4 class="text-base font-extrabold text-slate-800">{{ $activeKelas->nama_kelas }}</h4>
                            </div>
                            <span class="px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 border border-blue-100/60 font-bold text-xs">
                                Tingkat {{ $activeKelas->tingkatan }}
                            </span>
                        </div>

                        <div class="p-3.5 rounded-xl bg-slate-50/70 border border-slate-200/70">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-0.5">Guru Wali Kelas</span>
                            @if($activeKelas->guru)
                                <div class="flex items-center justify-between mt-1">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-blue-600 text-white font-bold text-xs flex items-center justify-center shrink-0">
                                            {{ strtoupper(substr($activeKelas->guru->nama, 0, 1)) }}
                                        </div>
                                        <span class="text-xs font-bold text-slate-800">{{ $activeKelas->guru->nama }}</span>
                                    </div>
                                    <a href="{{ route('guru.show', $activeKelas->guru->id) }}" class="text-[11px] font-semibold text-blue-600 hover:underline">
                                        Lihat Guru &rarr;
                                    </a>
                                </div>
                            @else
                                <p class="text-xs text-slate-400 italic">Belum ditentukan</p>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="p-5 text-center rounded-xl border border-dashed border-slate-200 bg-slate-50/50 space-y-2">
                        <svg class="w-8 h-8 text-slate-300 mx-auto" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 3L1 9L12 15L21 10.09V17H23V9M5 13.18V17.18C5 19.94 8.13 22 12 22C15.87 22 19 19.94 19 17.18V13.18L12 17L5 13.18Z"></path>
                        </svg>
                        <p class="text-xs font-bold text-slate-700">Siswa Belum Terdaftar di Kelas</p>
                        <p class="text-[11px] text-slate-400">Atur penempatan kelas melalui tombol edit siswa.</p>
                        <a href="{{ route('siswa.edit', $siswa->id) }}" class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 hover:underline pt-1">
                            + Masukkan ke Kelas
                        </a>
                    </div>
                @endif
            </div>

            <!-- Card: Foto Jadwal Pelajaran Kelas -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs hover:shadow-sm transition-shadow space-y-4">
                <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100/60 shadow-2xs">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 leading-tight">Foto Jadwal Pelajaran</h3>
                            <p class="text-xs text-slate-400 font-normal mt-0.5">
                                {{ $activeKelas ? 'Jadwal resmi untuk kelas ' . $activeKelas->nama_kelas : 'Jadwal pelajaran kelas' }}
                            </p>
                        </div>
                    </div>
                    @if($classJadwalUrl)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Tersedia
                        </span>
                    @endif
                </div>

                @if($classJadwalUrl)
                    <div class="space-y-3">
                        <div class="relative group rounded-xl overflow-hidden border border-slate-200/80 bg-slate-50/50 p-2 text-center cursor-pointer shadow-2xs hover:border-blue-300 transition-all"
                             @click="modalOpen = true; modalImg = '{{ $classJadwalUrl }}'">
                            <img src="{{ $classJadwalUrl }}" 
                                 alt="Jadwal Kelas {{ $activeKelas?->nama_kelas }}" 
                                 class="max-h-60 w-auto mx-auto rounded-lg object-contain transition-transform duration-200 group-hover:scale-[1.02]">
                            
                            <!-- Overlay Hover -->
                            <div class="absolute inset-0 bg-slate-900/10 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center rounded-xl">
                                <span class="px-3 py-1.5 bg-white/95 text-slate-800 text-xs font-bold rounded-lg shadow-md flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                    </svg>
                                    Perbesar Foto
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-1 text-xs">
                            <button type="button" 
                                    @click="modalOpen = true; modalImg = '{{ $classJadwalUrl }}'"
                                    class="font-semibold text-blue-600 hover:text-blue-700 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <span>Lihat Ukuran Penuh</span>
                            </button>

                            <a href="{{ $classJadwalUrl }}" 
                               download 
                               class="inline-flex items-center gap-1 font-semibold text-slate-600 hover:text-slate-900 bg-white hover:bg-slate-50 px-2.5 py-1 rounded-lg border border-slate-200 transition-colors shadow-2xs">
                                <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                <span>Unduh</span>
                            </a>
                        </div>
                    </div>
                @else
                    <div class="p-6 text-center rounded-xl border border-dashed border-slate-200 bg-slate-50/50 space-y-2">
                        <div class="w-10 h-10 rounded-xl bg-white text-slate-400 border border-slate-200 mx-auto flex items-center justify-center shadow-2xs">
                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <p class="text-xs font-bold text-slate-700">Belum Ada Foto Jadwal Kelas</p>
                        <p class="text-[11px] text-slate-400">Admin dapat mengunggah foto jadwal kelas di menu Kelola Jadwal.</p>
                        <a href="{{ route('admin.jadwal.index') }}" class="inline-block text-xs font-bold text-blue-600 hover:text-blue-700 hover:underline pt-1">
                            + Kelola Jadwal &rarr;
                        </a>
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
                    <h4 class="font-bold text-slate-800 text-sm">
                        Foto Jadwal Kelas {{ $activeKelas?->nama_kelas ?? '' }}
                    </h4>
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
                <img :src="modalImg" alt="Jadwal Zoom" class="mx-auto max-h-[70vh] object-contain rounded-xl shadow-xs">
            </div>
        </div>
    </div>

</div>
@endsection