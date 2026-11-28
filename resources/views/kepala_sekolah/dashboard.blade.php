@extends('layouts.app')

@section('title', 'Dashboard Kepala Sekolah')

@section('content')
@php
$totalStatusNilai = $jumlahTuntas + $jumlahBelumTuntas + $jumlahBelumAdaNilai;

$persenTuntasChart = $totalStatusNilai > 0
? ($jumlahTuntas / $totalStatusNilai) * 100
: 0;

$persenBelumTuntasChart = $totalStatusNilai > 0
? ($jumlahBelumTuntas / $totalStatusNilai) * 100
: 0;

$persenBelumAdaChart = $totalStatusNilai > 0
? ($jumlahBelumAdaNilai / $totalStatusNilai) * 100
: 0;

$monitoringGuruRows = $monitoringGuruRows ?? collect();
$monitoringGuruSummary = $monitoringGuruSummary ?? [];
@endphp

<div class="space-y-6">

    {{-- Welcome Banner --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-amber-600 to-orange-700 p-6 sm:p-8 text-white shadow-sm">
        <div class="relative z-10">
            <span class="inline-flex rounded-full bg-white/20 px-3 py-1 text-xs font-semibold uppercase tracking-wider">
                Halaman Kepala Sekolah
            </span>

            <h2 class="mt-3 text-2xl font-extrabold sm:text-3xl">
                Selamat Datang, Bapak/Ibu {{ auth()->user()->name }}!
            </h2>

            <p class="mt-2 max-w-2xl text-sm text-amber-100 sm:text-base">
                Pantau perkembangan akademik dan aktivitas pembelajaran sekolah melalui LearnPoint.
            </p>
        </div>

        <div class="absolute -right-10 -bottom-10 h-48 w-48 rounded-full bg-white/10 blur-2xl"></div>
        <div class="absolute right-24 -top-16 h-36 w-36 rounded-full bg-orange-300/10 blur-2xl"></div>
    </div>


    {{-- Statistik Sekolah --}}
    <section>
        <div class="mb-4">
            <h3 class="text-lg font-bold text-gray-900">Statistik Sekolah</h3>
            <p class="mt-1 text-sm text-gray-500">Ringkasan data umum sekolah.</p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

            {{-- Total Siswa --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Siswa</p>
                        <h3 class="mt-2 text-3xl font-bold text-gray-900">
                            {{ number_format($totalSiswa, 0, ',', '.') }}
                        </h3>
                        <p class="mt-1 text-xs text-gray-400">Siswa terdaftar</p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Total Guru --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Guru</p>
                        <h3 class="mt-2 text-3xl font-bold text-gray-900">
                            {{ number_format($totalGuru, 0, ',', '.') }}
                        </h3>
                        <p class="mt-1 text-xs text-gray-400">Tenaga pengajar</p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 14l9-5-9-5-9 5 9 5zm0 0v7m-7-10v5c3 3 11 3 14 0v-5" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Total Kelas --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Jumlah Kelas</p>
                        <h3 class="mt-2 text-3xl font-bold text-gray-900">
                            {{ number_format($totalKelas, 0, ',', '.') }}
                        </h3>
                        <p class="mt-1 text-xs text-gray-400">Kelas tersedia</p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 21V5a2 2 0 012-2h12a2 2 0 012 2v16M4 21h16M9 7h2m-2 4h2m-2 4h2m4-8h1m-1 4h1m-1 4h1" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Total Mapel --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Mata Pelajaran</p>
                        <h3 class="mt-2 text-3xl font-bold text-gray-900">
                            {{ number_format($totalMapel, 0, ',', '.') }}
                        </h3>
                        <p class="mt-1 text-xs text-gray-400">Mapel tersedia</p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-purple-50 text-purple-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18s-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                </div>
            </div>

        </div>
    </section>


    {{-- Gambaran Akademik --}}
    <section>
        <div class="mb-4">
            <h3 class="text-lg font-bold text-gray-900">Gambaran Akademik</h3>
            <p class="mt-1 text-sm text-gray-500">
                Ringkasan pencapaian hasil belajar siswa.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">

            {{-- Rata-rata Nilai --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Rata-rata Nilai Sekolah</p>
                        <p class="mt-3 text-4xl font-extrabold text-blue-700">
                            {{ number_format($rataRataSekolah, 2, ',', '.') }}
                        </p>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>

                <div class="mt-5 border-t border-gray-100 pt-4">
                    <p class="text-xs text-gray-500">
                        {{ number_format($jumlahSiswaDinilai, 0, ',', '.') }} siswa telah memiliki penilaian.
                    </p>
                </div>
            </div>

            {{-- Ketuntasan Hasil Belajar --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm xl:col-span-2">
                <div class="flex flex-col gap-6 sm:flex-row sm:items-center">

                    <div class="flex-1">
                        <h4 class="font-bold text-gray-900">Ketuntasan Hasil Belajar</h4>
                        <p class="mt-1 text-sm text-gray-500">
                            Distribusi status pencapaian nilai siswa.
                        </p>

                        <div class="mt-5 space-y-3">

                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="h-3 w-3 rounded-full bg-emerald-500"></span>
                                    <span class="text-sm text-gray-600">Tuntas</span>
                                </div>
                                <span class="text-sm font-semibold text-gray-900">
                                    {{ number_format($jumlahTuntas, 0, ',', '.') }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="h-3 w-3 rounded-full bg-amber-500"></span>
                                    <span class="text-sm text-gray-600">Belum Tuntas</span>
                                </div>
                                <span class="text-sm font-semibold text-gray-900">
                                    {{ number_format($jumlahBelumTuntas, 0, ',', '.') }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="h-3 w-3 rounded-full bg-gray-300"></span>
                                    <span class="text-sm text-gray-600">Belum Ada Nilai</span>
                                </div>
                                <span class="text-sm font-semibold text-gray-900">
                                    {{ number_format($jumlahBelumAdaNilai, 0, ',', '.') }}
                                </span>
                            </div>

                        </div>
                    </div>

                    {{-- Donut --}}
                    <div class="flex flex-col items-center justify-center sm:min-w-[170px]">
                        <div
                            class="relative flex h-36 w-36 items-center justify-center rounded-full"
                            style="background: conic-gradient(
                                #10b981 0% {{ $persenTuntasChart }}%,
                                #f59e0b {{ $persenTuntasChart }}% {{ $persenTuntasChart + $persenBelumTuntasChart }}%,
                                #d1d5db {{ $persenTuntasChart + $persenBelumTuntasChart }}% 100%
                            );">

                            <div class="flex h-24 w-24 flex-col items-center justify-center rounded-full bg-white">
                                <span class="text-2xl font-extrabold text-gray-900">
                                    {{ number_format($persenTuntasChart, 1, ',', '.') }}%
                                </span>
                                <span class="text-xs text-gray-500">Tuntas</span>
                            </div>
                        </div>

                        <p class="mt-3 text-xs text-gray-400">
                            Total {{ number_format($totalStatusNilai, 0, ',', '.') }} siswa
                        </p>
                    </div>

                </div>
            </div>

        </div>
    </section>


    {{-- Performa Akademik per Kelas --}}
    <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">

        <div class="mb-6">
            <h3 class="text-lg font-bold text-gray-900">Performa Akademik per Kelas</h3>
            <p class="mt-1 text-sm text-gray-500">
                Perbandingan rata-rata nilai dan ketuntasan setiap kelas.
            </p>
        </div>

        @if($rekapPerKelas->isNotEmpty())

        <div class="space-y-5">
            @foreach($rekapPerKelas as $kelas)
            @php
            $nilaiKelas = max(0, min(100, (float) ($kelas['rata_nilai'] ?? 0)));
            @endphp

            <div>
                <div class="mb-2 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">
                            {{ $kelas['nama_kelas'] ?? 'Kelas' }}
                        </p>
                        <p class="mt-1 text-xs text-gray-400">
                            {{ $kelas['siswa_dinilai'] ?? 0 }} dari {{ $kelas['total_siswa'] ?? 0 }} siswa dinilai
                        </p>
                    </div>

                    <div class="text-right">
                        <p class="text-sm font-bold text-gray-900">
                            {{ number_format($nilaiKelas, 2, ',', '.') }}
                        </p>
                        <p class="text-xs text-gray-400">
                            {{ number_format((float) ($kelas['persen_tuntas'] ?? 0), 1, ',', '.') }}% tuntas
                        </p>
                    </div>
                </div>

                <div class="h-2.5 w-full overflow-hidden rounded-full bg-gray-100">
                    <div
                        class="h-full rounded-full bg-blue-600 transition-all"
                        style="width: {{ $nilaiKelas }}%">
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        @else

        <div class="rounded-xl border border-dashed border-gray-300 px-5 py-8 text-center">
            <p class="text-sm font-semibold text-gray-700">
                Belum ada data performa kelas
            </p>
            <p class="mt-1 text-xs text-gray-500">
                Data akan ditampilkan setelah tersedia rekap nilai siswa.
            </p>
        </div>

        @endif

    </section>


    {{-- Monitoring Aktivitas Guru --}}
    <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">

        <div class="mb-5 flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Monitoring Aktivitas Guru</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Ringkasan aktivitas pembelajaran guru.
                </p>

                @if($periodeMonitoringGuru)
                <p class="mt-1 text-xs font-medium text-blue-600">
                    Periode {{ $periodeMonitoringGuru->tahun_ajaran }}
                    · {{ ucfirst($periodeMonitoringGuru->semester) }}
                </p>
                @endif
            </div>

            <span class="w-fit rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700">
                {{ number_format($monitoringGuruSummary['total_guru'] ?? 0, 0, ',', '.') }} Guru
            </span>
        </div>

        {{-- Ringkasan Aktivitas --}}
        <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-4">

            <div class="rounded-xl bg-blue-50 p-4">
                <p class="text-xs text-blue-600">Materi</p>
                <p class="mt-1 text-2xl font-bold text-blue-800">
                    {{ number_format($monitoringGuruSummary['total_materi'] ?? 0, 0, ',', '.') }}
                </p>
            </div>

            <div class="rounded-xl bg-emerald-50 p-4">
                <p class="text-xs text-emerald-600">Tugas</p>
                <p class="mt-1 text-2xl font-bold text-emerald-800">
                    {{ number_format($monitoringGuruSummary['total_tugas'] ?? 0, 0, ',', '.') }}
                </p>
            </div>

            <div class="rounded-xl bg-purple-50 p-4">
                <p class="text-xs text-purple-600">Kuis</p>
                <p class="mt-1 text-2xl font-bold text-purple-800">
                    {{ number_format($monitoringGuruSummary['total_kuis_published'] ?? 0, 0, ',', '.') }}
                </p>
            </div>

            <div class="rounded-xl bg-amber-50 p-4">
                <p class="text-xs text-amber-600">Ujian</p>
                <p class="mt-1 text-2xl font-bold text-amber-800">
                    {{ number_format($monitoringGuruSummary['total_ujian_aktif'] ?? 0, 0, ',', '.') }}
                </p>
            </div>

        </div>

        {{-- Tabel Guru --}}
        @if($monitoringGuruRows->isNotEmpty())

        <div class="overflow-x-auto">
            <table class="w-full min-w-[650px] text-left">

                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500">Nama Guru</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500">Materi</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500">Tugas</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500">Kuis</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500">Ujian</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500">Aktivitas</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">

                    @foreach($monitoringGuruRows as $guru)
                    <tr class="transition hover:bg-gray-50">

                        <td class="px-4 py-3">
                            <p class="text-sm font-semibold text-gray-800">
                                {{ $guru['nama_guru'] ?? 'Guru' }}
                            </p>
                        </td>

                        <td class="px-4 py-3 text-center text-sm text-gray-600">
                            {{ $guru['jumlah_materi'] ?? 0 }}
                        </td>

                        <td class="px-4 py-3 text-center text-sm text-gray-600">
                            {{ $guru['jumlah_tugas'] ?? 0 }}
                        </td>

                        <td class="px-4 py-3 text-center text-sm text-gray-600">
                            {{ $guru['jumlah_kuis_published'] ?? 0 }}
                        </td>

                        <td class="px-4 py-3 text-center text-sm text-gray-600">
                            {{ $guru['jumlah_ujian_aktif'] ?? 0 }}
                        </td>

                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex rounded-lg bg-blue-50 px-3 py-1 text-sm font-bold text-blue-700">
                                {{ $guru['total_aktivitas'] ?? 0 }}
                            </span>
                        </td>

                    </tr>
                    @endforeach

                </tbody>

            </table>
        </div>

        <div class="mt-4 flex flex-col justify-between gap-2 border-t border-gray-100 pt-4 sm:flex-row sm:items-center">
            <p class="text-xs text-gray-400">
                Menampilkan {{ $monitoringGuruRows->count() }} dari
                {{ $monitoringGuruSummary['total_guru'] ?? 0 }} guru.
            </p>

            @if(\Illuminate\Support\Facades\Route::has('admin.monitoring-guru.index'))
            <a href="{{ route('admin.monitoring-guru.index') }}"
                class="inline-flex items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-800">
                Lihat Monitoring Lengkap
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5 12h14m-7-7l7 7-7 7" />
                </svg>
            </a>
            @endif
        </div>

        @else

        <div class="rounded-xl border border-dashed border-gray-300 px-5 py-8 text-center">
            <p class="text-sm font-semibold text-gray-700">
                Belum ada aktivitas guru
            </p>
            <p class="mt-1 text-xs text-gray-500">
                Aktivitas guru akan muncul setelah terdapat materi, tugas, kuis, atau ujian.
            </p>
        </div>

        @endif

        <p class="mt-4 text-xs text-gray-400">
            Total aktivitas merupakan indikator jumlah konten pembelajaran, bukan penilaian kualitas mengajar.
        </p>

    </section>


    {{-- Evaluasi Akademik --}}
    <section>
        <div class="mb-4">
            <h3 class="text-lg font-bold text-gray-900">Evaluasi Akademik</h3>
            <p class="mt-1 text-sm text-gray-500">
                Akses laporan hasil belajar siswa secara lengkap.
            </p>
        </div>

        <a href="{{ route('kepala_sekolah.rekap-nilai.index') }}"
            class="group flex flex-col gap-4 rounded-2xl border border-blue-200 bg-white p-5 shadow-sm transition hover:border-blue-400 hover:shadow-md sm:flex-row sm:items-center sm:justify-between">

            <div class="flex items-start gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 transition group-hover:bg-blue-600 group-hover:text-white">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>

                <div>
                    <h4 class="font-bold text-gray-900 group-hover:text-blue-600">
                        Rekap Nilai Siswa
                    </h4>
                    <p class="mt-1 max-w-2xl text-sm leading-relaxed text-gray-500">
                        Lihat detail nilai siswa, rata-rata kelas, ketuntasan KKM, dan hasil belajar berdasarkan mata pelajaran.
                    </p>
                </div>
            </div>

            <div class="inline-flex shrink-0 items-center gap-2 text-sm font-semibold text-blue-600">
                Lihat Rekap Nilai
                <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5 12h14m-7-7l7 7-7 7" />
                </svg>
            </div>

        </a>
    </section>

</div>
@endsection