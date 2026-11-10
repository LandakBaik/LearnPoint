@extends('layouts.app')

@section('title', 'Detail Pengumpulan Pilihan Ganda')

@section('content')
<div class="space-y-5 text-slate-800">

    {{-- Breadcrumb dan Status Tugas --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <nav class="flex flex-wrap items-center gap-2 text-sm text-slate-500">

                <a href="{{ route('guru.tugas') }}"
                    class="inline-flex items-center gap-2 transition hover:text-blue-700">

                    <i class="fas fa-tasks text-base"></i>
                    Kelola Tugas

                </a>

                <i class="fas fa-chevron-right text-xs text-slate-400"></i>

                <span class="font-semibold text-blue-800">
                    Detail Pengumpulan
                </span>

            </nav>

            <h1 class="mt-3 text-2xl font-bold text-slate-800">
                {{ $tugas->judul }}
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Lihat pengerjaan dan hasil tugas siswa.
            </p>
        </div>

        <span class="w-fit rounded-full bg-blue-100 px-3 py-1.5 text-sm font-semibold text-blue-700">
            Pilihan Ganda
        </span>

    </div>


    {{-- Informasi Tugas --}}
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">

        <div class="flex flex-wrap items-start justify-between gap-4">

            <div>
                <h1 class="text-xl font-bold text-slate-900 sm:text-2xl">
                    {{ $tugas->judul }}
                </h1>

                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    {{ $tugas->deskripsi ?: 'Tidak ada deskripsi tugas.' }}
                </p>
            </div>

            <a href="{{ route('guru.tugas.kunci-jawaban', $tugas->id) }}"
                class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">

                <i class="fas fa-key"></i>
                Lihat Kunci Jawaban

            </a>

        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 border-t border-slate-100 pt-4 sm:grid-cols-3">

            <div>
                <p class="text-xs text-slate-500">Mata Pelajaran</p>

                <p class="mt-1 font-semibold text-slate-800">
                    {{ $tugas->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? '-' }}
                </p>
            </div>

            <div>
                <p class="text-xs text-slate-500">Kelas</p>

                <p class="mt-1 font-semibold text-slate-800">
                    {{ $tugas->pengampuKelas?->kelas?->nama_kelas ?? '-' }}
                </p>
            </div>

            <div>
                <p class="text-xs text-slate-500">Deadline</p>

                <p class="mt-1 font-semibold text-slate-800">
                    {{ $tugas->deadline
                        ? \Carbon\Carbon::parse($tugas->deadline)->locale('id')->translatedFormat('d F Y, H:i')
                        : '-' }}
                </p>
            </div>

        </div>

        {{-- Informasi Pilihan Ganda --}}
        <div class="mt-4 flex items-center gap-3 rounded-lg bg-blue-50 p-4">

            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                <i class="fas fa-list-ol"></i>
            </div>

            <div>
                <p class="text-sm font-semibold text-slate-800">
                    Tugas Pilihan Ganda
                </p>

                <p class="text-xs text-slate-600">
                    {{ $tugas->soals->count() }} soal dengan nilai maksimal 100.
                </p>
            </div>

        </div>

    </div>


    {{-- Ringkasan --}}
    @php
    $totalSiswa = $daftarSiswa->count();

    $totalMengerjakan = $daftarSiswa
    ->whereNotNull('pengumpulan_id')
    ->count();

    $totalBelum = $totalSiswa - $totalMengerjakan;

    $totalDinilai = $daftarSiswa
    ->where('status_filter', 'sudah_dinilai')
    ->count();
    @endphp

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">

        {{-- Total Siswa --}}
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">

            <p class="text-sm text-slate-500">
                Total Siswa
            </p>

            <p class="mt-2 text-2xl font-bold text-slate-800">
                {{ $totalSiswa }}
            </p>

        </div>

        {{-- Sudah Mengerjakan --}}
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">

            <p class="text-sm text-slate-500">
                Sudah Mengerjakan
            </p>

            <p class="mt-2 text-2xl font-bold text-blue-600">
                {{ $totalMengerjakan }}
            </p>

        </div>

        {{-- Belum Mengerjakan --}}
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">

            <p class="text-sm text-slate-500">
                Belum Mengerjakan
            </p>

            <p class="mt-2 text-2xl font-bold text-amber-600">
                {{ $totalBelum }}
            </p>

        </div>

        {{-- Sudah Dinilai --}}
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">

            <p class="text-sm text-slate-500">
                Sudah Dinilai
            </p>

            <p class="mt-2 text-2xl font-bold text-green-600">
                {{ $totalDinilai }}
            </p>

        </div>

    </div>


    {{-- Daftar Siswa --}}
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">

        <div>
            <h2 class="text-lg font-bold text-slate-800">
                Daftar Pengumpulan Siswa
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Lihat status pengerjaan dan hasil jawaban setiap siswa.
            </p>
        </div>

        <div class="mt-4 overflow-x-auto">

            <table class="w-full min-w-[850px] text-left text-sm">

                <thead class="border-y border-slate-200 bg-slate-50 text-slate-600">

                    <tr>
                        <th class="px-4 py-3 font-semibold">No.</th>
                        <th class="px-4 py-3 font-semibold">Nama Siswa</th>
                        <th class="px-4 py-3 font-semibold">NIS</th>
                        <th class="px-4 py-3 font-semibold">Waktu Mengerjakan</th>
                        <th class="px-4 py-3 font-semibold">Nilai</th>
                        <th class="px-4 py-3 font-semibold">Status</th>
                        <th class="px-4 py-3 text-center font-semibold">Aksi</th>
                    </tr>

                </thead>

                <tbody class="divide-y divide-slate-100">

                    @forelse($daftarSiswa as $index => $siswa)

                    <tr class="transition hover:bg-slate-50">

                        {{-- Nomor --}}
                        <td class="px-4 py-3 text-slate-500">
                            {{ $index + 1 }}
                        </td>

                        {{-- Nama --}}
                        <td class="px-4 py-3 font-medium text-slate-800">
                            {{ $siswa['nama'] }}
                        </td>

                        {{-- NIS --}}
                        <td class="px-4 py-3 text-slate-500">
                            {{ $siswa['nis'] }}
                        </td>

                        {{-- Waktu Mengerjakan --}}
                        <td class="px-4 py-3 text-slate-600">

                            @if($siswa['waktu'])

                            {{ $siswa['waktu'] }}

                            @if($siswa['terlambat'])
                            <span class="ml-1 text-xs font-medium text-red-600">
                                (Terlambat)
                            </span>
                            @endif

                            @else

                            <span class="text-slate-400">-</span>

                            @endif

                        </td>

                        {{-- Nilai --}}
                        <td class="px-4 py-3 font-semibold text-slate-800">
                            {{ $siswa['nilai'] !== null ? $siswa['nilai'] : '-' }}
                        </td>

                        {{-- Status --}}
                        <td class="px-4 py-3">

                            @if($siswa['status_filter'] === 'sudah_dinilai')

                            <span class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">
                                Sudah Dinilai
                            </span>

                            @elseif($siswa['status_filter'] === 'perlu_diperiksa')

                            <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                Perlu Diperiksa
                            </span>

                            @else

                            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                                Belum Mengerjakan
                            </span>

                            @endif

                        </td>

                        {{-- Aksi --}}
                        <td class="px-4 py-3 text-center">

                            @if($siswa['pengumpulan_id'])

                            <a href="{{ route('guru.tugas.detail-jawaban-siswa', [
                                    'id' => $tugas->id,
                                    'pengumpulanId' => $siswa['pengumpulan_id']
                                ]) }}"
                                class="inline-flex items-center gap-2 rounded-lg bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 transition hover:bg-blue-100">

                                <i class="fas fa-eye"></i>
                                Lihat Jawaban

                            </a>

                            @else

                            <span class="text-xs text-slate-400">
                                Belum tersedia
                            </span>

                            @endif

                        </td>

                    </tr>

                    @empty

                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-slate-500">
                            Belum ada siswa di kelas ini.
                        </td>
                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>
@endsection