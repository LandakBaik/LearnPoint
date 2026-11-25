@extends('layouts.app')

@section('title', 'Detail Ujian')

@section('content')

@php
    $anggotaGrup = isset($grupUjians) && $grupUjians->isNotEmpty()
        ? $grupUjians
        : collect([$ujian]);

    $daftarKelas = $anggotaGrup
        ->map(fn($item) => $item->pengampuKelas?->kelas?->nama_kelas ?? $item->kelas?->nama_kelas)
        ->filter()
        ->unique()
        ->values();

    $adaPengerjaan = $anggotaGrup->sum(fn($item) => $item->seleksiUjians->count()) > 0;

    // Tombol Edit tampil untuk draft, scheduled (0 peserta), unpublished (Edit Bobot), dan completed (Edit Bobot)
    $bisaEdit = $ujian->bolehDiedit();
    $labelTombolEdit = $ujian->hanyaBisaEditBobot() ? 'Edit Bobot Soal' : 'Edit Ujian';

    $jumlahSoal = $ujian->soals->count();
    $jumlahSingle = $ujian->soals->where('tipe_soal', 'single_choice')->count();
    $jumlahMultiple = $ujian->soals->where('tipe_soal', 'multiple_choice')->count();
    $jumlahEssay = $ujian->soals->where('tipe_soal', 'essay')->count();
    $jumlahMatching = $ujian->soals->where('tipe_soal', 'matching')->count();
    $totalBobot = round($ujian->soals->sum('bobot'), 2);

    $kelasLabelRingkas = $daftarKelas->isNotEmpty()
        ? 'Kelas ' . $daftarKelas->implode(', ')
        : '-';
    $judulAman = str_replace(["\r", "\n"], ' ', addslashes($ujian->judul));
    $kelasAman = str_replace(["\r", "\n"], ' ', addslashes($kelasLabelRingkas));

    $statusBadgeClass = match ($ujian->status) {
        'published' => 'bg-emerald-50 text-emerald-700 border-emerald-200/70',
        'scheduled' => 'bg-amber-50 text-amber-700 border-amber-200/70',
        'completed' => 'bg-slate-100 text-slate-600 border-slate-200',
        'draft' => 'bg-blue-50 text-blue-600 border-blue-200/70',
        'unpublished' => 'bg-rose-50 text-rose-600 border-rose-200/70',
        default => 'bg-slate-100 text-slate-600 border-slate-200',
    };

    $statusIconBoxClass = match ($ujian->status) {
        'published' => 'bg-emerald-50 text-emerald-600 border-emerald-200/60',
        'scheduled' => 'bg-amber-50 text-amber-600 border-amber-200/60',
        'completed' => 'bg-slate-100 text-slate-600 border-slate-200/60',
        'draft' => 'bg-blue-50 text-blue-600 border-blue-100/80',
        'unpublished' => 'bg-rose-50 text-rose-600 border-rose-200/60',
        default => 'bg-slate-100 text-slate-600 border-slate-200/60',
    };
@endphp

<div x-data="detailUjianModal()" class="max-w-7xl mx-auto space-y-6">

    {{-- =========================================================
        HEADER HALAMAN
    ========================================================== --}}
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">

        <div class="space-y-1">
            <div class="mb-2">
                <a href="{{ route('guru.ujian.index') }}"
                    class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-slate-500 hover:text-blue-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Kembali ke Daftar Ujian</span>
                </a>
            </div>

            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-tight">
                {{ $ujian->judul }}
            </h1>

            <p class="text-sm text-slate-500 leading-relaxed font-normal">
                Detail pengaturan instrumen ujian dan butir evaluasi.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 shrink-0">

            @if ($ujian->status === 'published')
                <a href="{{ route('guru.ujian.monitoring', $ujian->id) }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200/80 text-xs font-semibold transition-all duration-200 shadow-xs hover:shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Monitoring Live</span>
                </a>

                <button type="button"
                    @click="bukaModal({
                        jenis: 'tarik',
                        judul: '{{ $judulAman }}',
                        soal: {{ $jumlahSoal }},
                        kelas: '{{ $kelasAman }}',
                        url: '{{ route('guru.ujian.unpublish', $ujian->id) }}'
                    })"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200/80 text-xs font-semibold transition-all duration-200 shadow-xs hover:shadow-sm cursor-pointer">
                    <span>Tarik Publikasi</span>
                </button>
            @endif

            {{-- Tombol Edit tampil sesuai logika $ujian->bolehDiedit() --}}
            @if ($bisaEdit)
                <a href="{{ route('guru.ujian.edit', $ujian) }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold transition-all duration-200 shadow-xs hover:shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span>{{ $labelTombolEdit }}</span>
                </a>
            @endif

            @if ($ujian->status === 'draft')
                <button type="button"
                    @click="bukaModal({
                        jenis: 'publikasi',
                        judul: '{{ $judulAman }}',
                        soal: {{ $jumlahSoal }},
                        kelas: '{{ $kelasAman }}',
                        url: '{{ route('guru.ujian.publish', $ujian) }}'
                    })"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold transition-all duration-200 shadow-xs hover:shadow-sm cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Publikasikan</span>
                </button>
            @endif

        </div>
    </div>


    {{-- =========================================================
        4 KARTU INFORMASI UTAMA
    ========================================================== --}}
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- STATUS UJIAN --}}
        <div
            class="group bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md hover:border-slate-300/80 transition-all duration-200 flex flex-col justify-between gap-4">
            <div class="flex items-center justify-between gap-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Status Ujian</span>
                <div
                    class="w-9 h-9 rounded-xl border shadow-xs flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform {{ $statusIconBoxClass }}">
                    @if ($ujian->status === 'published' || $ujian->status === 'completed')
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    @elseif ($ujian->status === 'scheduled')
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    @elseif ($ujian->status === 'unpublished')
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                    @else
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    @endif
                </div>
            </div>
            <div>
                <span
                    class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-2.5 py-1 rounded-full border shadow-2xs before:content-[''] before:inline-block before:w-1.5 before:h-1.5 before:rounded-full before:bg-current {{ $statusBadgeClass }}">
                    {{ $ujian->status_label ?? ucfirst($ujian->status) }}
                </span>
            </div>
        </div>

        {{-- MATA PELAJARAN --}}
        <div
            class="group bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md hover:border-blue-200 transition-all duration-200 flex flex-col justify-between gap-4">
            <div class="flex items-center justify-between gap-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Mata Pelajaran</span>
                <div
                    class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 border border-blue-100/80 shadow-xs flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
            </div>
            <p class="text-lg font-extrabold text-slate-900 leading-tight truncate"
                title="{{ $ujian->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? $ujian->mapel?->nama_mapel ?? '-' }}">
                {{ $ujian->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? $ujian->mapel?->nama_mapel ?? '-' }}
            </p>
        </div>

        {{-- TARGET KELAS --}}
        <div
            class="group bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md hover:border-indigo-200 transition-all duration-200 flex flex-col justify-between gap-4">
            <div class="flex items-center justify-between gap-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Target Kelas</span>
                <div
                    class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 border border-indigo-100/80 shadow-xs flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-1.5">
                @if ($daftarKelas->isNotEmpty())
                    @foreach ($daftarKelas as $namaKls)
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-600 border border-blue-100/80 whitespace-nowrap">
                            {{ str_starts_with(strtolower($namaKls), 'kelas') ? $namaKls : 'Kelas ' . $namaKls }}
                        </span>
                    @endforeach
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200/70 whitespace-nowrap">
                        {{ $daftarKelas->count() }} Kelas
                    </span>
                @else
                    <p class="text-sm font-bold text-slate-800">-</p>
                @endif
            </div>
        </div>

        {{-- JUMLAH SOAL --}}
        <div
            class="group bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md hover:border-amber-200 transition-all duration-200 flex flex-col justify-between gap-4">
            <div class="flex items-center justify-between gap-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Jumlah Soal</span>
                <div
                    class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 border border-amber-200/60 shadow-xs flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
            </div>
            <div class="flex items-baseline gap-1.5">
                <span class="text-2xl font-extrabold text-slate-900 leading-tight">{{ $jumlahSoal }}</span>
                <span class="text-xs text-slate-400 font-medium">Butir Soal</span>
            </div>
        </div>

    </section>


    {{-- =========================================================
        DETAIL PENGATURAN (KIRI: INFORMASI + KISI-KISI | KANAN: KOMPOSISI)
    ========================================================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

        {{-- KOLOM KIRI (2/3): INFORMASI UJIAN & KISI-KISI --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- KARTU INFORMASI UJIAN --}}
            <div
                class="bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="text-base font-bold text-slate-900 tracking-tight">Informasi Ujian</h2>
                    <span class="text-slate-400" title="Ringkasan jadwal dan pengaturan ujian">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                </div>

                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-5">

                    {{-- Durasi --}}
                    <div>
                        <p class="text-xs text-slate-400 font-medium flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor"
                                stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Durasi</span>
                        </p>
                        <p class="text-sm font-bold text-slate-900 mt-1.5">{{ $ujian->durasi_menit }} menit</p>
                    </div>

                    {{-- KKM --}}
                    <div>
                        <p class="text-xs text-slate-400 font-medium flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor"
                                stroke-width="1.8" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="9" />
                                <circle cx="12" cy="12" r="4" />
                            </svg>
                            <span>KKM</span>
                        </p>
                        <p class="text-sm font-bold text-slate-900 mt-1.5">{{ $ujian->kkm }}</p>
                    </div>

                    {{-- Waktu Mulai --}}
                    <div>
                        <p class="text-xs text-slate-400 font-medium flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor"
                                stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>Waktu Mulai</span>
                        </p>
                        <p class="text-sm font-bold text-slate-900 mt-1.5">
                            {{ $ujian->waktu_mulai ? \Carbon\Carbon::parse($ujian->waktu_mulai)->isoFormat('D MMMM Y, HH:mm') . ' WIB' : 'Belum dijadwalkan' }}
                        </p>
                    </div>

                    {{-- Deadline --}}
                    <div>
                        <p class="text-xs text-slate-400 font-medium flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor"
                                stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                            </svg>
                            <span>Deadline</span>
                        </p>
                        <p class="text-sm font-bold text-slate-900 mt-1.5">
                            {{ $ujian->deadline ? \Carbon\Carbon::parse($ujian->deadline)->isoFormat('D MMMM Y, HH:mm') . ' WIB' : 'Belum ditentukan' }}
                        </p>
                    </div>

                    {{-- Dibuat --}}
                    <div>
                        <p class="text-xs text-slate-400 font-medium flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor"
                                stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Dibuat</span>
                        </p>
                        <p class="text-sm font-bold text-slate-900 mt-1.5">
                            {{ $ujian->created_at ? $ujian->created_at->isoFormat('D MMM Y, HH:mm') : '-' }}
                        </p>
                    </div>

                    {{-- Dipublikasi --}}
                    <div>
                        <p class="text-xs text-slate-400 font-medium flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor"
                                stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                            </svg>
                            <span>Dipublikasi</span>
                        </p>
                        <p class="text-sm font-bold text-slate-900 mt-1.5">
                            {{ $ujian->dipublikasi_at ? \Carbon\Carbon::parse($ujian->dipublikasi_at)->isoFormat('D MMM Y, HH:mm') : '-' }}
                        </p>
                    </div>

                </div>
            </div>

            {{-- KARTU KISI-KISI UJIAN --}}
            @if ($ujian->kisi_kisi)
                <div
                    class="bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <h2 class="text-base font-bold text-slate-900 tracking-tight">Kisi-kisi Ujian</h2>
                        <span class="text-slate-400" title="Kompetensi atau topik materi yang diujikan">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                    </div>
                    <div class="p-6">
                        <div
                            class="rounded-xl bg-slate-50/70 border border-slate-200/80 px-4 py-3.5 text-sm text-slate-700 whitespace-pre-line leading-relaxed">
                            {{ $ujian->kisi_kisi }}
                        </div>
                    </div>
                </div>
            @endif

        </div>

        {{-- KOLOM KANAN (1/3): KOMPOSISI SOAL --}}
        <div
            class="bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden">

            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="text-base font-bold text-slate-900 tracking-tight">Komposisi Soal</h2>
            </div>

            <div class="p-6 space-y-4">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-sm text-slate-600">Pilihan Ganda Biasa</span>
                    <span
                        class="inline-flex items-center justify-center min-w-[32px] px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $jumlahSingle > 0 ? 'bg-blue-50 text-blue-600 border-blue-200/70' : 'bg-slate-50 text-slate-400 border-slate-200/70' }}">
                        {{ $jumlahSingle }}
                    </span>
                </div>

                <div class="flex items-center justify-between gap-3">
                    <span class="text-sm text-slate-600">Pilihan Ganda Kompleks</span>
                    <span
                        class="inline-flex items-center justify-center min-w-[32px] px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $jumlahMultiple > 0 ? 'bg-blue-50 text-blue-600 border-blue-200/70' : 'bg-slate-50 text-slate-400 border-slate-200/70' }}">
                        {{ $jumlahMultiple }}
                    </span>
                </div>

                <div class="flex items-center justify-between gap-3">
                    <span class="text-sm text-slate-600">Essay</span>
                    <span
                        class="inline-flex items-center justify-center min-w-[32px] px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $jumlahEssay > 0 ? 'bg-blue-50 text-blue-600 border-blue-200/70' : 'bg-slate-50 text-slate-400 border-slate-200/70' }}">
                        {{ $jumlahEssay }}
                    </span>
                </div>

                <div class="flex items-center justify-between gap-3">
                    <span class="text-sm text-slate-600">Menjodohkan</span>
                    <span
                        class="inline-flex items-center justify-center min-w-[32px] px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $jumlahMatching > 0 ? 'bg-blue-50 text-blue-600 border-blue-200/70' : 'bg-slate-50 text-slate-400 border-slate-200/70' }}">
                        {{ $jumlahMatching }}
                    </span>
                </div>

                <div class="pt-2">
                    <div
                        class="px-4 py-3.5 rounded-xl border flex items-center justify-between {{ $totalBobot > 100.01 ? 'bg-rose-50/70 border-rose-200 text-rose-700' : 'bg-blue-50/70 border-blue-200/80 text-blue-700' }}">
                        <span class="text-sm font-bold">Total Bobot</span>
                        <span class="text-base font-extrabold tracking-tight">
                            {{ $totalBobot }} / 100
                        </span>
                    </div>
                </div>
            </div>

        </div>

    </div>


    {{-- =========================================================
        DAFTAR SOAL (TETAP SAMA SEPERTI KODE ASLI)
    ========================================================== --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80">

        <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="text-base font-bold text-slate-900">Daftar Butir Soal</h2>
                <p class="text-xs text-slate-400 mt-0.5 leading-relaxed">
                    Periksa kembali butir soal dan kebenaran kunci jawaban sebelum ujian diikuti siswa.
                </p>
            </div>
            <span class="text-[11px] font-semibold text-blue-700 bg-blue-50 border border-blue-200/70 px-2.5 py-1 rounded-full shadow-2xs">
                {{ $ujian->soals->count() }} Butir Soal
            </span>
        </div>

        <div class="p-6 space-y-6">

            @forelse($ujian->soals as $index => $soal)
                @php
                    $rawKunci = $soal->kunci_jawaban;
                    if (is_string($rawKunci)) {
                        $decoded = json_decode($rawKunci, true);
                        $kunciArr = (json_last_error() === JSON_ERROR_NONE && is_array($decoded))
                            ? $decoded
                            : array_filter(explode(',', $rawKunci));
                    } elseif (is_array($rawKunci)) {
                        $kunciArr = $rawKunci;
                    } else {
                        $kunciArr = $rawKunci !== null ? [$rawKunci] : [];
                    }

                    $kunciNormalized = array_map(function($k) {
                        return strtolower(trim((string)$k));
                    }, $kunciArr);

                    $labelKunci = [];
                    if (is_array($soal->pilihan)) {
                        foreach ($soal->pilihan as $keyOpt => $textOpt) {
                            $hKecil = chr(97 + (int)$keyOpt);
                            $hBesar = chr(65 + (int)$keyOpt);
                            $idxStr = (string)$keyOpt;

                            if (in_array($hKecil, $kunciNormalized) || in_array($idxStr, $kunciNormalized)) {
                                $labelKunci[] = $hBesar . '. ' . $textOpt;
                            }
                        }
                    }
                @endphp

                <div class="border border-gray-200 rounded-2xl p-5 bg-white space-y-4 hover:border-gray-300 transition">

                    {{-- HEADER SOAL --}}
                    <div class="flex items-center justify-between gap-3 border-b border-gray-100 pb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center font-black text-sm border border-blue-100">
                                {{ $index + 1 }}
                            </span>
                            <span class="px-2.5 py-0.5 rounded-md text-xs font-bold uppercase tracking-wider bg-gray-100 text-gray-600">
                                @switch($soal->tipe_soal)
                                    @case('single_choice') Pilihan Tunggal @break
                                    @case('multiple_choice') Pilihan Ganda Kompleks @break
                                    @case('essay') Essay @break
                                    @case('matching') Menjodohkan @break
                                    @default {{ $soal->tipe_soal }}
                                @endswitch
                            </span>
                        </div>

                        <div class="text-xs font-semibold text-gray-500">
                            Bobot: <span class="font-bold text-gray-800">{{ $soal->bobot }} Poin</span>
                        </div>
                    </div>

                    {{-- TEKS PERTANYAAN --}}
                    <div class="text-gray-900 font-medium leading-relaxed text-sm">
                        {!! nl2br(e($soal->pertanyaan)) !!}
                    </div>

                    {{-- GAMBAR SOAL --}}
                    @if($soal->gambar)
                        <div class="my-3">
                            <div class="inline-block p-1.5 border border-gray-200 rounded-2xl bg-gray-50 shadow-2xs">
                                <img
                                    src="{{ asset('storage/' . ltrim($soal->gambar, '/')) }}"
                                    alt="Gambar soal {{ $index + 1 }}"
                                    class="max-w-md max-h-72 w-auto rounded-xl object-contain block bg-white"
                                    onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'p-3 text-xs text-rose-600 font-semibold flex items-center gap-1.5\'>⚠️ Gambar belum terhubung. Jalankan: <code>php artisan storage:link</code> di terminal.</div>';"
                                >
                            </div>
                        </div>
                    @endif

                    {{-- OPSI JAWABAN (PILIHAN GANDA) --}}
                    @if(in_array($soal->tipe_soal, ['single_choice', 'multiple_choice']) && is_array($soal->pilihan) && count($soal->pilihan))
                        <div class="space-y-2 pt-1">
                            @foreach($soal->pilihan as $key => $pilihan)
                                @php
                                    $hurufKecil = chr(97 + (int)$key);
                                    $hurufBesar = chr(65 + (int)$key);
                                    $indexStr   = (string)$key;

                                    $isBenar = in_array($hurufKecil, $kunciNormalized)
                                            || in_array($indexStr, $kunciNormalized);
                                @endphp

                                <div
                                    class="flex items-center justify-between p-3 rounded-xl border text-sm transition
                                    {{ $isBenar
                                        ? 'bg-emerald-50/80 border-emerald-300 ring-1 ring-emerald-300/40 text-emerald-950 font-semibold'
                                        : 'bg-gray-50/60 border-gray-200/80 text-gray-700 font-normal' }}"
                                >
                                    <div class="flex items-center gap-3 min-w-0">
                                        <span class="w-7 h-7 rounded-lg text-xs font-bold flex items-center justify-center shrink-0
                                            {{ $isBenar ? 'bg-emerald-600 text-white shadow-xs' : 'bg-gray-200 text-gray-600' }}">
                                            {{ $hurufBesar }}
                                        </span>
                                        <span class="truncate">{{ $pilihan }}</span>
                                    </div>

                                    @if($isBenar)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 shrink-0 ml-2">
                                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Kunci Jawaban
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        {{-- RINGKASAN KUNCI JAWABAN SOAL INI --}}
                        <div class="pt-2 flex items-center gap-2 text-xs">
                            <span class="font-bold text-gray-500 uppercase tracking-wider">Kunci Benar:</span>
                            @if(count($labelKunci) > 0)
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($labelKunci as $itemKunci)
                                        <span class="px-2.5 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 font-bold border border-emerald-200">
                                            {{ $itemKunci }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-rose-500 italic font-semibold">Belum disetel</span>
                            @endif
                        </div>

                    @elseif($soal->tipe_soal === 'essay')
                        <div class="p-4 bg-blue-50/60 border border-blue-200 rounded-xl space-y-1">
                            <span class="text-xs font-bold uppercase tracking-wider text-blue-700 block">Kunci / Pedoman Jawaban Essay:</span>
                            <p class="text-sm text-gray-800 font-medium">
                                {{ !empty($kunciNormalized) ? implode(', ', $kunciNormalized) : 'Guru akan mengoreksi jawaban essay siswa secara manual.' }}
                            </p>
                        </div>

                    @elseif($soal->tipe_soal === 'matching')
                        <div class="p-4 bg-purple-50/60 border border-purple-200 rounded-xl text-xs text-purple-700 font-medium">
                            Soal tipe menjodohkan (pasangan pilihan telah disetel di instrumen soal).
                        </div>
                    @endif

                </div>

            @empty
                <div class="text-center py-12">
                    <div class="text-4xl mb-3">📝</div>
                    <h3 class="font-bold text-gray-700">Belum ada soal</h3>
                    <p class="text-sm text-gray-500 mt-1">Tambahkan butir soal terlebih dahulu melalui menu edit.</p>
                </div>
            @endforelse

        </div>

    </div>

    {{-- PERINGATAN --}}
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
        <div class="flex gap-3">
            <div class="text-xl">⚠️</div>
            <div>
                <h3 class="font-bold text-amber-800 text-sm">Pemeriksaan Kunci Jawaban</h3>
                <p class="text-xs text-amber-700 mt-0.5 leading-relaxed">
                    Pastikan seluruh butir soal telah memiliki lencana hijau <strong>Kunci Jawaban</strong> sebelum mempublikasikan ujian agar sistem dapat melakukan penilaian otomatis dengan tepat.
                </p>
            </div>
        </div>
    </div>

    {{-- =========================================================
        MODAL KONFIRMASI SERAGAM (PUBLIKASI & TARIK PUBLIKASI)
    ========================================================== --}}
    <div x-show="modal.terbuka" x-cloak x-transition.opacity @keydown.escape.window="modal.terbuka = false"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/45 backdrop-blur-[2px]">
        <div @click.away="modal.terbuka = false"
            class="bg-white rounded-2xl max-w-lg w-full border border-slate-200/80 shadow-2xl overflow-hidden">

            <form :action="modal.url" method="POST">
                @csrf
                <template x-if="modal.jenis === 'publikasi'">
                    <input type="hidden" name="tipe_publikasi" value="langsung">
                </template>

                <div class="p-6 space-y-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider block"
                                :class="temaModal.kickerClass" x-text="temaModal.kicker"></span>
                            <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight mt-1"
                                x-text="modal.judul"></h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed" x-text="temaModal.subtitle"></p>
                        </div>
                        <button type="button" @click="modal.terbuka = false"
                            class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Info Bar Ujian --}}
                    <div
                        class="flex flex-wrap items-center justify-between gap-2 px-3.5 py-2.5 rounded-xl bg-slate-50/80 border border-slate-200/80">
                        <div class="flex items-center gap-2 min-w-0">
                            <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor"
                                stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="text-xs font-bold text-slate-800 truncate" x-text="modal.judul"></span>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <span
                                class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-200/70 text-slate-600"
                                x-text="modal.soal + ' Soal'"></span>
                            <span
                                class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-600 border border-blue-100/80"
                                x-text="modal.kelas"></span>
                        </div>
                    </div>

                    {{-- Callout Box --}}
                    <div class="flex items-start gap-2.5 p-3.5 rounded-xl border text-xs leading-relaxed"
                        :class="temaModal.calloutClass">
                        <template x-if="modal.jenis === 'tarik'">
                            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </template>
                        <template x-if="modal.jenis === 'publikasi'">
                            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </template>
                        <span x-text="temaModal.calloutText"></span>
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50/70 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" @click="modal.terbuka = false"
                        class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200/80 transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 px-5 py-2 rounded-xl text-xs font-semibold text-white transition-colors shadow-xs cursor-pointer"
                        :class="temaModal.btnClass">
                        <span x-text="temaModal.btnLabel"></span>
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>

<script>
    function detailUjianModal() {
        return {
            modal: {
                terbuka: false,
                jenis: 'tarik',
                judul: '',
                soal: 0,
                kelas: '',
                url: '',
            },

            bukaModal(payload) {
                this.modal = {
                    terbuka: true,
                    jenis: payload.jenis || 'tarik',
                    judul: payload.judul || '',
                    soal: payload.soal || 0,
                    kelas: payload.kelas || '-',
                    url: payload.url || '',
                };
            },

            get temaModal() {
                if (this.modal.jenis === 'publikasi') {
                    return {
                        kicker: 'Publikasi Ujian',
                        kickerClass: 'text-emerald-700',
                        subtitle: 'Publikasikan ujian ini sekarang untuk semua kelas target?',
                        calloutClass: 'bg-emerald-50/80 border-emerald-200 text-emerald-800',
                        calloutText: `Ujian akan langsung aktif dan dapat diakses pada dasbor siswa ${this.modal.kelas} segera setelah dipublikasikan.`,
                        btnClass: 'bg-emerald-600 hover:bg-emerald-700',
                        btnLabel: 'Ya, Publikasikan Sekarang',
                    };
                }

                return {
                    kicker: 'Tarik Publikasi Ujian ?',
                    kickerClass: 'text-red-700',
                    subtitle: 'Apakah Anda yakin ingin menarik publikasi ujian ini dari akses siswa sekarang?',
                    calloutClass: 'bg-rose-50/90 border-rose-200 text-red-700',
                    calloutText: 'Siswa yang belum selesai akan dikeluarkan dari lembar ujian. Nilai siswa yang sudah selesai tetap tersimpan aman.',
                    btnClass: 'bg-red-700 hover:bg-red-800',
                    btnLabel: 'Ya, Tarik Publikasi',
                };
            }
        };
    }
</script>

@endsection
