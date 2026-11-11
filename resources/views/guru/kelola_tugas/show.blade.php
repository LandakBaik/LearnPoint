
@extends('layouts.app')

@section('title', 'Detail Upload')

@section('content')

@php
    // Statistik pengumpulan.
    $deadline = $tugas->deadline ? \Carbon\Carbon::parse($tugas->deadline) : null;
    $totalSiswa = $daftarSiswa->count();
    $sudahKumpul = $daftarSiswa->whereNotNull('pengumpulan_id')->count();
    $belumKumpul = $totalSiswa - $sudahKumpul;
    $perluDiperiksa = $daftarSiswa->where('status_filter', 'perlu_diperiksa')->count();
    $jumlahTerlambat = $daftarSiswa->where('terlambat', true)->count();
    $tepatWaktu = $sudahKumpul - $jumlahTerlambat;
    $deadlineLewat = $deadline && now()->gt($deadline);
@endphp

<style>
    [x-cloak] { display: none !important; }
</style>

<div class="min-h-screen bg-slate-50">
    <div class="space-y-4 text-slate-900"
        x-data="detailPengumpulanUpload(@js($daftarSiswa->values()))">

        {{-- Breadcrumb dan deadline --}}
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <nav class="flex items-center gap-2 text-sm text-slate-500">
                <a href="{{ route('guru.tugas') }}" class="inline-flex items-center gap-2 hover:text-blue-700">
                    <i class="fas fa-tasks"></i>
                    Kelola Tugas
                </a>
                <i class="fas fa-chevron-right text-xs text-slate-400"></i>
                <span class="font-semibold text-blue-800">Detail Pengumpulan</span>
            </nav>

            <span class="inline-flex w-fit items-center gap-2 rounded-full px-3 py-1.5 text-xs font-bold
                {{ $deadlineLewat ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700' }}">
                <span class="h-2 w-2 rounded-full {{ $deadlineLewat ? 'bg-red-500' : 'bg-emerald-500' }}"></span>
                {{ $deadlineLewat ? 'Deadline Terlewati' : 'Tugas Aktif' }}
            </span>
        </div>

        {{-- Informasi tugas --}}
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="p-4 sm:p-5">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0 flex-1">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">
                                {{ $tugas->tipe === 'pilihan_ganda' ? 'Pilihan Ganda' : 'Upload File' }}
                            </span>
                            <span class="text-xs text-slate-400">Detail tugas dan pengumpulan siswa</span>
                        </div>

                        <h1 class="text-xl font-bold text-slate-900 sm:text-2xl">{{ $tugas->judul }}</h1>
                        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                            {{ $tugas->deskripsi ?: 'Tidak ada deskripsi tugas.' }}
                        </p>
                    </div>

                    <div class="flex shrink-0 items-center gap-3 rounded-lg bg-blue-50 p-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white text-blue-700">
                            <i class="fas fa-users text-lg"></i>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">Total Siswa</p>
                            <p class="text-lg font-bold text-slate-900">{{ $totalSiswa }} Siswa</p>
                        </div>
                    </div>
                </div>

                {{-- Informasi kelas dan deadline --}}
                <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-3">
                    <div class="flex items-center gap-3 rounded-lg bg-slate-50 p-3">
                        <i class="fas fa-book-open text-xl text-blue-700"></i>
                        <div class="min-w-0">
                            <p class="text-xs text-slate-400">Mata Pelajaran</p>
                            <p class="truncate text-sm font-semibold text-slate-800">
                                {{ $tugas->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? 'Mata Pelajaran' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 rounded-lg bg-slate-50 p-3">
                        <i class="fas fa-users text-xl text-blue-700"></i>
                        <div>
                            <p class="text-xs text-slate-400">Kelas</p>
                            <p class="text-sm font-semibold text-slate-800">
                                {{ $tugas->pengampuKelas?->kelas?->nama_kelas ?? 'Kelas' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 rounded-lg bg-slate-50 p-3">
                        <i class="fas fa-calendar-alt text-xl text-red-500"></i>
                        <div>
                            <p class="text-xs text-slate-400">Batas Pengumpulan</p>
                            <p class="text-sm font-semibold text-slate-800">
                                {{ $deadline?->locale('id')->translatedFormat('d M Y, H:i') ?? '-' }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Lampiran tugas --}}
                @if($tugas->tipe === 'upload' && $tugas->file_lampiran)
                    <div class="mt-4 flex flex-col gap-3 rounded-lg border border-blue-100 bg-blue-50/60 p-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-white text-red-500">
                                <i class="fas fa-file-alt text-lg"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-slate-800">Lampiran Tugas dari Guru</p>
                                <p class="truncate text-xs text-slate-500">{{ basename($tugas->file_lampiran) }}</p>
                            </div>
                        </div>
                        <a href="{{ asset('storage/' . $tugas->file_lampiran) }}"
                            target="_blank" rel="noopener"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-700 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-800">
                            <i class="fas fa-eye"></i>
                            Lihat Lampiran
                        </a>
                    </div>
                @endif
            </div>
        </section>

        {{-- Notifikasi --}}
        @if(session('success'))
            <div class="flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm font-semibold text-emerald-800">
                <i class="fas fa-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 p-3 text-sm font-semibold text-red-800">
                <i class="fas fa-exclamation-circle"></i>
                {{ session('error') }}
            </div>
        @endif

        {{-- Ringkasan pengumpulan --}}
        <section>
            <div class="mb-3">
                <h2 class="text-base font-bold text-slate-900">Ringkasan Pengumpulan</h2>
                <p class="text-xs text-slate-500">Status pengumpulan tugas seluruh siswa</p>
            </div>

            <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <p class="text-xs font-semibold text-slate-500">Total Siswa</p>
                            <p class="mt-2 text-2xl font-bold text-slate-900">{{ $totalSiswa }}</p>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-700">
                            <i class="fas fa-users text-lg"></i>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-slate-400">Siswa dalam kelas</p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <p class="text-xs font-semibold text-slate-500">Sudah Kumpul</p>
                            <p class="mt-2 text-2xl font-bold text-emerald-600">{{ $sudahKumpul }}</p>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                            <i class="fas fa-check-circle text-lg"></i>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-slate-500">{{ $tepatWaktu }} tepat waktu · {{ $jumlahTerlambat }} terlambat</p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <p class="text-xs font-semibold text-slate-500">Perlu Diperiksa</p>
                            <p class="mt-2 text-2xl font-bold text-indigo-600">{{ $perluDiperiksa }}</p>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                            <i class="fas fa-clipboard-check text-lg"></i>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-slate-400">Menunggu pemeriksaan guru</p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <p class="text-xs font-semibold text-slate-500">Belum Kumpul</p>
                            <p class="mt-2 text-2xl font-bold text-red-600">{{ $belumKumpul }}</p>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-50 text-red-600">
                            <i class="fas fa-clock text-lg"></i>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-slate-400">Belum mengirim jawaban</p>
                </div>
            </div>
        </section>

        {{-- Pencarian dan filter --}}
        <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="mb-3">
                <h2 class="text-base font-bold text-slate-900">Daftar Pengumpulan Siswa</h2>
                <p class="text-xs text-slate-500">Periksa status pengumpulan dan berikan nilai kepada siswa</p>
            </div>

            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                {{-- Pencarian di kiri --}}
                <div class="relative w-full lg:max-w-xs">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                    <input type="search"
                        x-model.debounce.250ms="kataKunci"
                        @input="halaman = 1"
                        placeholder="Cari nama siswa atau NIS..."
                        class="h-10 w-full rounded-lg border border-slate-200 bg-slate-50 pl-10 pr-3 text-sm outline-none focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-100">
                </div>

                {{-- Filter di kanan --}}
                <div class="flex gap-2 overflow-x-auto pb-1">
                    <template x-for="tab in tabs" :key="tab.key">
                        <button type="button"
                            @click="filterAktif = tab.key; halaman = 1"
                            :class="filterAktif === tab.key
                                ? 'bg-blue-700 text-white'
                                : 'bg-slate-100 text-slate-600 hover:bg-blue-50 hover:text-blue-700'"
                            class="inline-flex shrink-0 items-center gap-2 rounded-lg px-3 py-2 text-xs font-bold transition">
                            <span x-text="tab.label"></span>
                            <span x-text="jumlahFilter(tab.key)"
                                :class="filterAktif === tab.key ? 'bg-white/20 text-white' : 'bg-white text-slate-600'"
                                class="rounded-full px-2 py-0.5 text-[10px]"></span>
                        </button>
                    </template>
                </div>
            </div>
        </section>

        {{-- Tabel pengumpulan siswa --}}
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[850px] text-left">
                    <thead class="bg-slate-50">
                        <tr class="border-b border-slate-200 text-xs font-bold uppercase text-slate-500">
                            <th class="px-4 py-3">Nama Siswa</th>
                            <th class="px-4 py-3">Waktu Pengumpulan</th>
                            <th class="px-4 py-3">Berkas Jawaban</th>
                            <th class="px-4 py-3 text-center">Nilai</th>
                            <th class="px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        <template x-for="siswa in dataHalaman" :key="siswa.siswa_id">
                            <tr class="transition hover:bg-slate-50"
                                :class="selected && selected.siswa_id === siswa.siswa_id ? 'bg-blue-50' : ''">

                                {{-- Identitas siswa --}}
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-800"
                                            x-text="inisial(siswa.nama)"></div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-slate-800" x-text="siswa.nama"></p>
                                            <p class="text-xs text-slate-400" x-text="'NIS: ' + (siswa.nis || '-')"></p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Waktu pengumpulan --}}
                                <td class="px-4 py-3">
                                    <template x-if="siswa.pengumpulan_id">
                                        <div>
                                            <p class="text-xs font-semibold text-slate-700" x-text="siswa.waktu || '-'"></p>
                                            <span class="mt-1 inline-flex rounded-full px-2 py-1 text-[10px] font-bold"
                                                :class="siswa.terlambat ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-700'"
                                                x-text="siswa.terlambat ? 'Terlambat' : 'Tepat Waktu'"></span>
                                        </div>
                                    </template>
                                    <template x-if="!siswa.pengumpulan_id">
                                        <span class="rounded-full bg-red-50 px-2 py-1 text-[10px] font-bold text-red-600">Belum Kumpul</span>
                                    </template>
                                </td>

                                {{-- Berkas jawaban --}}
                                <td class="px-4 py-3">
                                    <template x-if="siswa.file_url">
                                        <div class="flex items-center gap-1">
                                            <a :href="siswa.file_url" target="_blank" rel="noopener"
                                                class="inline-flex max-w-[180px] items-center gap-2 rounded-lg px-2 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-50">
                                                <i class="fas fa-file-alt"></i>
                                                <span class="truncate" x-text="namaFile(siswa.file)"></span>
                                            </a>
                                            <button type="button" @click="bukaReview(siswa)"
                                                title="Periksa jawaban"
                                                class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-blue-100 hover:text-blue-700">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </template>
                                    <template x-if="!siswa.file_url && siswa.pengumpulan_id">
                                        <span class="text-xs italic text-slate-400">Tidak ada file</span>
                                    </template>
                                    <template x-if="!siswa.pengumpulan_id">
                                        <span class="text-xs text-slate-400">-</span>
                                    </template>
                                </td>

                                {{-- Nilai siswa --}}
                                <td class="px-4 py-3 text-center">
                                    <span x-show="siswa.nilai !== null"
                                        class="inline-flex rounded-lg bg-emerald-50 px-2.5 py-1.5 text-xs font-bold text-emerald-700"
                                        x-text="formatNilai(siswa.nilai) + '/100'"></span>
                                    <span x-show="siswa.nilai === null && siswa.pengumpulan_id"
                                        class="inline-flex rounded-lg bg-amber-50 px-2.5 py-1.5 text-xs font-semibold text-amber-700">Belum Dinilai</span>
                                    <span x-show="!siswa.pengumpulan_id" class="text-xs text-slate-400">-</span>
                                </td>

                                {{-- Status penilaian --}}
                                <td class="px-4 py-3 text-center">
                                    <span x-show="!siswa.pengumpulan_id"
                                        class="inline-flex rounded-full bg-red-50 px-2.5 py-1 text-[10px] font-bold text-red-600">Belum Kumpul</span>
                                    <span x-show="siswa.pengumpulan_id && siswa.nilai === null"
                                        class="inline-flex rounded-full bg-indigo-50 px-2.5 py-1 text-[10px] font-bold text-indigo-700">Perlu Diperiksa</span>
                                    <span x-show="siswa.pengumpulan_id && siswa.nilai !== null"
                                        class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-bold text-emerald-700">Sudah Dinilai</span>
                                </td>
                            </tr>
                        </template>

                        {{-- Data kosong --}}
                        <tr x-show="dataFilter.length === 0">
                            <td colspan="5" class="px-4 py-10 text-center">
                                <i class="fas fa-search text-3xl text-slate-300"></i>
                                <p class="mt-2 text-sm font-semibold text-slate-700">Data tidak ditemukan</p>
                                <p class="mt-1 text-xs text-slate-400">Tidak ada siswa yang cocok dengan pencarian atau filter.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs text-slate-500">
                    Menampilkan
                    <strong class="text-slate-800" x-text="dataFilter.length ? mulai + 1 : 0"></strong>
                    sampai
                    <strong class="text-slate-800" x-text="Math.min(mulai + perHalaman, dataFilter.length)"></strong>
                    dari
                    <strong class="text-slate-800" x-text="dataFilter.length"></strong>
                    siswa
                </p>

                <div class="flex items-center gap-1">
                    <button type="button" @click="halaman--" :disabled="halaman <= 1"
                        class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-600 hover:bg-white disabled:cursor-not-allowed disabled:opacity-30">
                        <i class="fas fa-chevron-left text-xs"></i>
                    </button>

                    <template x-for="nomor in totalHalaman" :key="nomor">
                        <button type="button" @click="halaman = nomor"
                            :class="halaman === nomor ? 'bg-blue-700 text-white' : 'text-slate-600 hover:bg-white'"
                            class="h-8 min-w-8 rounded-lg px-2 text-xs font-bold"
                            x-text="nomor"></button>
                    </template>

                    <button type="button" @click="halaman++" :disabled="halaman >= totalHalaman"
                        class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-600 hover:bg-white disabled:cursor-not-allowed disabled:opacity-30">
                        <i class="fas fa-chevron-right text-xs"></i>
                    </button>
                </div>
            </div>
        </section>

        {{-- Panel pemeriksaan dan penilaian --}}
        <section x-show="showReview" x-cloak x-transition
            class="overflow-hidden rounded-xl border border-blue-200 bg-white shadow-sm">

            <div class="flex items-center justify-between border-b border-slate-100 bg-blue-50/60 px-4 py-3">
                <div>
                    <div class="flex items-center gap-2">
                        <i class="fas fa-clipboard-check text-lg text-blue-700"></i>
                        <h2 class="text-sm font-bold text-slate-900">Pemeriksaan dan Penilaian</h2>
                    </div>
                    <p class="mt-1 text-xs text-slate-500">Masukkan nilai berdasarkan hasil pekerjaan siswa.</p>
                </div>

                <button type="button" @click="showReview = false" title="Tutup panel"
                    class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-white hover:text-red-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="p-4">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                    {{-- Identitas siswa --}}
                    <div class="flex min-w-0 items-center gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-blue-100 text-sm font-bold text-blue-800"
                            x-text="selected ? inisial(selected.nama) : ''"></div>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-bold text-slate-900"
                                x-text="selected ? selected.nama : ''"></p>
                            <p class="text-xs text-slate-500"
                                x-text="selected ? 'NIS: ' + selected.nis : ''"></p>
                            <span class="mt-1 inline-flex rounded-full px-2 py-1 text-[10px] font-bold"
                                :class="selected && selected.terlambat ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-700'"
                                x-text="selected && selected.terlambat ? 'Terlambat Mengumpulkan' : 'Tepat Waktu'"></span>
                        </div>
                    </div>

                    {{-- Form nilai --}}
                    <form method="POST"
                        class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:items-end"
                        :action="selected ? '/guru/pengumpulan/' + selected.pengumpulan_id + '/nilai' : '#'">
                        @csrf

                        <div>
                            <label for="nilaiAkhir" class="mb-1.5 block text-xs font-bold text-slate-600">Nilai Akhir</label>
                            <div class="flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3">
                                <input id="nilaiAkhir" type="number" name="nilai"
                                    min="0" max="100" step="0.01"
                                    x-model="nilaiInput" required
                                    class="h-10 w-20 border-0 bg-transparent text-center text-base font-bold text-slate-900 outline-none focus:ring-0">
                                <span class="text-xs font-semibold text-slate-400">/ 100</span>
                            </div>
                        </div>

                        <button type="submit"
                            :disabled="!selected || !selected.pengumpulan_id || nilaiInput === '' || Number(nilaiInput) < 0 || Number(nilaiInput) > 100"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-blue-700 px-4 text-xs font-bold text-white hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-40">
                            <i class="fas fa-save"></i>
                            Simpan Nilai
                        </button>
                    </form>
                </div>
            </div>
        </section>

        {{-- Informasi penilaian --}}
        <div class="flex items-start gap-2 rounded-lg border border-blue-100 bg-blue-50 p-3">
            <i class="fas fa-info-circle mt-0.5 shrink-0 text-blue-700"></i>
            <div>
                <p class="text-xs font-bold text-blue-900">Informasi Penilaian</p>
                <p class="mt-1 text-xs leading-5 text-blue-800">
                    Klik ikon mata untuk memilih siswa yang akan dinilai.
                    Klik nama berkas untuk membuka jawaban siswa.
                    Setelah nilai disimpan, status pengumpulan akan berubah menjadi sudah dinilai.
                </p>
            </div>
        </div>
    </div>
</div>

{{-- Logika pencarian, filter, pagination, dan penilaian --}}
<script>
    function detailPengumpulanUpload(dataAwal) {
        return {
            semuaData: dataAwal || [],
            filterAktif: 'semua',
            kataKunci: '',
            halaman: 1,
            perHalaman: 5,
            showReview: false,
            selected: null,
            nilaiInput: '',

            tabs: [
                { key: 'semua', label: 'Semua Siswa' },
                { key: 'perlu_diperiksa', label: 'Perlu Diperiksa' },
                { key: 'sudah_dinilai', label: 'Sudah Dinilai' },
                { key: 'terlambat', label: 'Terlambat' },
                { key: 'belum_kumpul', label: 'Belum Kumpul' }
            ],

            // Data sesuai filter dan pencarian.
            get dataFilter() {
                let hasil = this.semuaData;

                const filter = {
                    perlu_diperiksa: s => s.status_filter === 'perlu_diperiksa',
                    sudah_dinilai: s => s.status_filter === 'sudah_dinilai',
                    terlambat: s => s.pengumpulan_id && s.terlambat,
                    belum_kumpul: s => !s.pengumpulan_id
                };

                if (filter[this.filterAktif]) {
                    hasil = hasil.filter(filter[this.filterAktif]);
                }

                const cari = this.kataKunci.trim().toLowerCase();

                if (cari) {
                    hasil = hasil.filter(s =>
                        String(s.nama || '').toLowerCase().includes(cari) ||
                        String(s.nis || '').toLowerCase().includes(cari)
                    );
                }

                return hasil;
            },

            // Data pagination.
            get totalHalaman() {
                return Math.max(1, Math.ceil(this.dataFilter.length / this.perHalaman));
            },

            get mulai() {
                return (this.halaman - 1) * this.perHalaman;
            },

            get dataHalaman() {
                return this.dataFilter.slice(this.mulai, this.mulai + this.perHalaman);
            },

            // Jumlah siswa pada setiap filter.
            jumlahFilter(key) {
                const hitung = {
                    semua: s => true,
                    perlu_diperiksa: s => s.status_filter === 'perlu_diperiksa',
                    sudah_dinilai: s => s.status_filter === 'sudah_dinilai',
                    terlambat: s => s.pengumpulan_id && s.terlambat,
                    belum_kumpul: s => !s.pengumpulan_id
                };

                return this.semuaData.filter(hitung[key] || (() => false)).length;
            },

            // Membuka panel penilaian.
            bukaReview(siswa) {
                if (!siswa.pengumpulan_id) return;

                this.selected = siswa;
                this.nilaiInput = siswa.nilai ?? '';
                this.showReview = true;

                this.$nextTick(() => {
                    document.getElementById('nilaiAkhir')?.focus();
                });
            },

            // Membuat inisial nama siswa.
            inisial(nama) {
                return String(nama || 'S')
                    .trim()
                    .split(/\s+/)
                    .slice(0, 2)
                    .map(kata => kata.charAt(0).toUpperCase())
                    .join('');
            },

            // Mengambil nama file.
            namaFile(path) {
                return path ? String(path).split('/').pop() : '-';
            },

            // Format nilai.
            formatNilai(nilai) {
                const angka = Number(nilai);
                return Number.isInteger(angka) ? angka : angka.toFixed(2);
            }
        };
    }
</script>

@endsection