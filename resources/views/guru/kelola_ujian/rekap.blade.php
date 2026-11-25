@extends('layouts.app')

@section('title', 'Rekap Nilai & Koreksi: ' . $ujian->judul)

@section('content')
<div class="max-w-7xl mx-auto space-y-6" x-data="rekapNilaiApp({
    pesertas: {{ Js::from($rekapSiswa) }},
    kkm: {{ $kkm }},
    judulDefault: {{ Js::from($ujian->judul) }},
    durasiDefault: {{ (int) ($ujian->durasi_menit ?? 60) }},
    mulaiDefault: '{{ now()->format('Y-m-d\TH:i') }}',
    deadlineDefault: '{{ now()->addDay()->format('Y-m-d\TH:i') }}'
})">

    {{-- =========================================================
        NOTIFIKASI VALIDASI SERVER (Khusus $errors dari withErrors)
    ========================================================== --}}
    @if ($errors->any())
        <div x-data="{ tampil: true }" x-show="tampil" x-transition
            class="flex items-start justify-between gap-3 px-5 py-3.5 rounded-2xl bg-rose-50 border border-rose-200/80 text-rose-800 text-xs sm:text-sm shadow-xs">
            <div class="flex items-start gap-2.5">
                <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="space-y-1">
                    @if ($errors->count() === 1)
                        <p class="font-medium leading-relaxed">{{ $errors->first() }}</p>
                    @else
                        <p class="font-semibold">Terdapat beberapa kesalahan validasi:</p>
                        <ul class="list-disc list-inside text-xs space-y-0.5 text-rose-700">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
            <button type="button" @click="tampil = false"
                class="text-rose-500 hover:text-rose-700 p-0.5 rounded-lg transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    {{-- =========================================================
        HEADER HALAMAN
    ========================================================== --}}
    <div class="flex flex-col xl:flex-row xl:items-end justify-between gap-4">
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

            <div class="flex items-center gap-2.5 flex-wrap">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-tight">
                    Rekap Nilai: {{ $ujian->judul }}
                </h1>
                <span
                    class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-600 border border-blue-200/70 shadow-2xs">
                    KKM: {{ number_format($kkm, 0) }}
                </span>
                @if ($ujian->jenis_ujian !== 'reguler')
                    <span
                        class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200/70 shadow-2xs uppercase">
                        {{ $ujian->jenis_ujian }}
                    </span>
                @endif
            </div>

            <p class="text-sm text-slate-500 leading-relaxed font-normal">
                {{ $ujian->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? 'Mata Pelajaran' }}
                <span class="mx-1.5 text-slate-300">&bull;</span>
                Kelas: {{ $ujian->pengampuKelas?->kelas?->nama_kelas ?? '-' }}
            </p>
        </div>

        {{-- TOMBOL AKSI UTAMA --}}
        <div class="flex items-center gap-2.5 flex-wrap shrink-0">
            <a href="{{ route('guru.ujian.laporan.pdf', ['mode' => 'detail', 'pengampu_kelas_id' => $ujian->pengampu_kelas_id, 'ujian_id' => $ujian->id]) }}"
                class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200/80 text-xs font-semibold transition-all duration-200 shadow-xs hover:shadow-sm">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="1.8"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Cetak PDF Berita Acara</span>
            </a>

            <button type="button" @click="bukaModalRemidiSusulan('susulan')"
                class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200/80 text-xs font-semibold transition-all duration-200 shadow-xs hover:shadow-sm cursor-pointer">
                <span>Buat Susulan</span>
                <span
                    class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full bg-slate-100 text-slate-600 text-[11px] font-bold">
                    {{ $ringkasan['belum_ujian'] }}
                </span>
            </button>

            <button type="button" @click="bukaModalRemidiSusulan('remidi')"
                class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200/80 text-xs font-semibold transition-all duration-200 shadow-xs hover:shadow-sm cursor-pointer">
                <span>Buat Remidi</span>
                <span
                    class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full bg-slate-100 text-slate-600 text-[11px] font-bold">
                    {{ $ringkasan['remidi'] }}
                </span>
            </button>

            @if ($ringkasan['ada_soal_essay'])
                <a href="{{ route('guru.ujian.koreksi-essay', $ujian->id) }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold transition-all duration-200 shadow-xs hover:shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span>Koreksi Essay Massal</span>
                    @if ($ringkasan['menunggu_essay'] > 0)
                        <span
                            class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full bg-white/25 text-white text-[11px] font-bold">
                            {{ $ringkasan['menunggu_essay'] }}
                        </span>
                    @endif
                </a>
            @endif
        </div>
    </div>

    {{-- =========================================================
        KARTU UTAMA: FILTER TAB, PENCARIAN, TABEL REKAP, PAGINATION
    ========================================================== --}}
    <div
        class="bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden">

        {{-- BAR FILTER CEPAT & PENCARIAN --}}
        <div
            class="px-6 py-4 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center justify-between gap-3 bg-white">
            <div class="flex items-center gap-1.5 flex-wrap">
                <button type="button" @click="setFilter('semua')"
                    :class="filterStatus === 'semua'
                        ? 'bg-blue-50 text-blue-600 border border-blue-200/80 font-bold shadow-2xs'
                        : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50 border border-transparent font-medium'"
                    class="px-3.5 py-1.5 rounded-xl text-xs transition-colors cursor-pointer">
                    Semua ({{ $ringkasan['total'] }})
                </button>

                @if ($ringkasan['ada_soal_essay'])
                    <button type="button" @click="setFilter('menunggu_essay')"
                        :class="filterStatus === 'menunggu_essay'
                            ? 'bg-blue-50 text-blue-600 border border-blue-200/80 font-bold shadow-2xs'
                            : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50 border border-transparent font-medium'"
                        class="px-3.5 py-1.5 rounded-xl text-xs transition-colors cursor-pointer">
                        Perlu Koreksi Essay ({{ $ringkasan['menunggu_essay'] }})
                    </button>
                @endif

                <button type="button" @click="setFilter('remidi')"
                    :class="filterStatus === 'remidi'
                        ? 'bg-blue-50 text-blue-600 border border-blue-200/80 font-bold shadow-2xs'
                        : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50 border border-transparent font-medium'"
                    class="px-3.5 py-1.5 rounded-xl text-xs transition-colors cursor-pointer">
                    Di Bawah KKM / Remidi ({{ $ringkasan['remidi'] }})
                </button>

                <button type="button" @click="setFilter('tuntas')"
                    :class="filterStatus === 'tuntas'
                        ? 'bg-blue-50 text-blue-600 border border-blue-200/80 font-bold shadow-2xs'
                        : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50 border border-transparent font-medium'"
                    class="px-3.5 py-1.5 rounded-xl text-xs transition-colors cursor-pointer">
                    Tuntas ({{ $ringkasan['tuntas'] }})
                </button>

                <button type="button" @click="setFilter('belum_ujian')"
                    :class="filterStatus === 'belum_ujian'
                        ? 'bg-blue-50 text-blue-600 border border-blue-200/80 font-bold shadow-2xs'
                        : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50 border border-transparent font-medium'"
                    class="px-3.5 py-1.5 rounded-xl text-xs transition-colors cursor-pointer">
                    Belum Ujian / Susulan ({{ $ringkasan['belum_ujian'] }})
                </button>
            </div>

            <div class="relative w-full lg:w-64 shrink-0">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="m21 21-4.35-4.35m2.35-5.65a8 8 0 11-16 0 8 8 0 0116 0z" />
                </svg>
                <input type="text" x-model="searchQuery" @input="currentPage = 1"
                    placeholder="Cari nama siswa / NISN..."
                    class="w-full pl-9 pr-3.5 py-2 rounded-xl border border-slate-200/80 bg-slate-50/50 text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
            </div>
        </div>

        {{-- TABEL REKAP NILAI --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr
                        class="bg-slate-50/70 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200/60">
                        <th class="py-3.5 pl-6 pr-3 w-12 text-center">#</th>
                        <th class="py-3.5 px-4">Nama Siswa &amp; Kelas</th>
                        <th class="py-3.5 px-4 whitespace-nowrap">Statistik PG</th>
                        <th class="py-3.5 px-4 whitespace-nowrap">Nilai PG</th>
                        <th class="py-3.5 px-4 whitespace-nowrap">Nilai Essay</th>
                        <th class="py-3.5 px-4 whitespace-nowrap">Nilai Akhir</th>
                        <th class="py-3.5 px-4 whitespace-nowrap">Status Kelulusan</th>
                        <th class="py-3.5 pl-4 pr-6 text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    <template x-for="(row, idx) in paginatedPesertas" :key="row.siswa_id">
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            {{-- Nomor --}}
                            <td class="py-4 pl-6 pr-3 text-center font-semibold text-slate-400"
                                x-text="((currentPage - 1) * perPage) + idx + 1"></td>

                            {{-- Nama Siswa & Kelas --}}
                            <td class="py-4 px-4 min-w-[200px]">
                                <div class="font-bold text-slate-900 text-xs sm:text-[13px] leading-snug"
                                    x-text="row.nama"></div>
                                <div class="mt-1 flex items-center gap-1.5 flex-wrap text-[11px] text-slate-400">
                                    <span
                                        :class="row.sudah_selesai
                                            ? 'inline-flex items-center px-2 py-0.5 rounded-md bg-blue-50 text-blue-600 border border-blue-100/80 font-semibold'
                                            : 'text-slate-400 font-medium'"
                                        x-text="'Kelas ' + row.kelas"></span>
                                    <span class="text-slate-300">&bull;</span>
                                    <span>NISN: <span class="font-mono" x-text="row.nisn"></span></span>
                                </div>
                            </td>

                            {{-- Statistik PG --}}
                            <td class="py-4 px-4 whitespace-nowrap">
                                <template x-if="row.sudah_selesai && row.total_pg > 0">
                                    <div class="inline-flex items-center gap-1.5">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/70 shadow-2xs"
                                            x-text="row.pg_benar + ' Benar'"></span>
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-600 border border-rose-200/70 shadow-2xs"
                                            x-text="row.pg_salah + ' Salah'"></span>
                                    </div>
                                </template>
                                <template x-if="!row.sudah_selesai || row.total_pg === 0">
                                    <span class="text-slate-300">&mdash;</span>
                                </template>
                            </td>

                            {{-- Nilai PG --}}
                            <td class="py-4 px-4 whitespace-nowrap">
                                <template x-if="row.sudah_selesai && row.total_pg > 0">
                                    <span class="font-bold text-slate-900 text-[13px]"
                                        x-text="row.nilai_pg + ' / ' + row.bobot_pg"></span>
                                </template>
                                <template x-if="!row.sudah_selesai || row.total_pg === 0">
                                    <span class="text-slate-300">&mdash;</span>
                                </template>
                            </td>

                            {{-- Nilai Essay --}}
                            <td class="py-4 px-4 whitespace-nowrap">
                                <template x-if="!row.sudah_selesai || row.total_essay === 0">
                                    <span class="text-slate-300">&mdash;</span>
                                </template>
                                <template x-if="row.sudah_selesai && row.total_essay > 0 && row.menunggu_koreksi_essay">
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200/80 shadow-2xs">
                                        Belum Dikoreksi (<span
                                            x-text="row.essay_dinilai + ' / ' + row.total_essay"></span>)
                                    </span>
                                </template>
                                <template x-if="row.sudah_selesai && row.total_essay > 0 && !row.menunggu_koreksi_essay">
                                    <span class="font-bold text-slate-900 text-[13px]"
                                        x-text="row.nilai_essay + ' / ' + row.bobot_essay"></span>
                                </template>
                            </td>

                            {{-- Nilai Akhir --}}
                            <td class="py-4 px-4 whitespace-nowrap">
                                <template x-if="!row.sudah_selesai || row.menunggu_koreksi_essay">
                                    <span class="text-slate-300">&mdash;</span>
                                </template>
                                <template x-if="row.sudah_selesai && !row.menunggu_koreksi_essay">
                                    <div>
                                        <span class="text-sm font-extrabold"
                                            :class="row.nilai_akhir >= kkm ? 'text-emerald-600' : 'text-rose-600'"
                                            x-text="row.nilai_akhir"></span>
                                        <template x-if="row.jumlah_remidi > 0">
                                            <span class="block text-[10px] text-slate-400"
                                                x-text="'(Awal: ' + row.nilai_awal + ')'"></span>
                                        </template>
                                    </div>
                                </template>
                            </td>

                            {{-- Status Kelulusan --}}
                            <td class="py-4 px-4 whitespace-nowrap">
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold border shadow-2xs"
                                    :class="{
                                        'bg-emerald-50 text-emerald-700 border-emerald-200/70': row.status_kategori === 'tuntas',
                                        'bg-rose-50 text-rose-600 border-rose-200/70': row.status_kategori === 'remidi',
                                        'bg-amber-50 text-amber-700 border-amber-200/80': row.status_kategori === 'menunggu_essay',
                                        'bg-slate-100 text-slate-500 border-slate-200/80': row.status_kategori === 'belum_ujian'
                                    }">
                                    <template x-if="row.status_kategori !== 'belum_ujian'">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                    </template>
                                    <span x-text="row.status_label"></span>
                                </span>
                            </td>

                            {{-- Aksi --}}
                            <td class="py-4 pl-4 pr-6 text-right whitespace-nowrap">
                                <template x-if="row.sudah_selesai">
                                    <a :href="`/guru/ujian/${row.ujian_id}/peserta/${row.siswa_id}/jawaban`"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-blue-50/70 hover:bg-blue-100 text-blue-600 border border-blue-200/80 rounded-xl text-xs font-semibold transition-colors shadow-2xs">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <span>Lihat &amp; Nilai Jawaban</span>
                                    </a>
                                </template>
                                <template x-if="!row.sudah_selesai">
                                    <span class="text-xs text-slate-400 italic">Belum ada jawaban</span>
                                </template>
                            </td>
                        </tr>
                    </template>

                    {{-- Empty State --}}
                    <tr x-show="filteredPesertas.length === 0" x-cloak>
                        <td colspan="8" class="py-12 px-6 text-center">
                            <p class="text-sm font-bold text-slate-700">Tidak ada data siswa ditemukan</p>
                            <p class="text-xs text-slate-400 mt-1">
                                Coba ubah tab filter atau kata kunci pencarian nama / NISN siswa.
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- FOOTER & PAGINATION CLIENT-SIDE --}}
        <div
            class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 bg-white text-xs text-slate-500">
            <p>
                Menampilkan
                <span class="font-bold text-slate-800" x-text="rangeTampil"></span>
                dari
                <span class="font-bold text-slate-800" x-text="filteredPesertas.length"></span>
                siswa
            </p>

            <div class="flex items-center gap-3 flex-wrap justify-center">
                <div class="flex items-center gap-2">
                    <span class="text-slate-500">Baris per halaman:</span>
                    <select x-model.number="perPage" @change="currentPage = 1"
                        class="px-2.5 py-1 rounded-lg border border-slate-200/80 bg-white text-xs font-semibold text-slate-700 focus:border-blue-500 outline-none cursor-pointer">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>

                <div class="flex items-center gap-1">
                    <button type="button" @click="if (currentPage > 1) currentPage--" :disabled="currentPage <= 1"
                        class="w-8 h-8 inline-flex items-center justify-center rounded-lg border border-slate-200/80 text-slate-600 hover:bg-slate-50 disabled:text-slate-300 disabled:cursor-not-allowed transition-colors cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <template x-for="page in totalPages" :key="page">
                        <button type="button" @click="currentPage = page"
                            class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-xs transition-colors cursor-pointer"
                            :class="currentPage === page
                                ? 'bg-blue-600 text-white font-bold shadow-xs'
                                : 'border border-slate-200/80 text-slate-600 hover:bg-slate-50 font-semibold'"
                            x-text="page"></button>
                    </template>

                    <button type="button" @click="if (currentPage < totalPages) currentPage++"
                        :disabled="currentPage >= totalPages"
                        class="w-8 h-8 inline-flex items-center justify-center rounded-lg border border-slate-200/80 text-slate-600 hover:bg-slate-50 disabled:text-slate-300 disabled:cursor-not-allowed transition-colors cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

    </div>

    {{-- =========================================================
        MODAL BUAT REMIDI / SUSULAN (DESAIN SELARAS)
    ========================================================== --}}
    <div x-show="showModalRemidi" x-cloak x-transition.opacity
        @keydown.escape.window="if (!modalAlert.open) showModalRemidi = false"
        class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/45 backdrop-blur-[2px] p-4">
        <div @click.away="if (!modalAlert.open) showModalRemidi = false"
            class="bg-white rounded-2xl max-w-2xl w-full shadow-2xl border border-slate-200/80 max-h-[90vh] flex flex-col overflow-hidden">

            {{-- Header Modal --}}
            <div class="px-6 py-4 border-b border-slate-100 flex items-start justify-between gap-4 shrink-0">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider block"
                        :class="jenisModal === 'remidi' ? 'text-rose-600' : 'text-blue-600'"
                        x-text="jenisModal === 'remidi' ? 'PROGRAM REMIDIAL' : 'UJIAN SUSULAN'"></span>
                    <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight mt-0.5"
                        x-text="jenisModal === 'remidi' ? 'Buat Ujian Remidi' : 'Buat Ujian Susulan'"></h3>
                    <p class="text-xs text-slate-500 mt-0.5 leading-relaxed"
                        x-text="jenisModal === 'remidi'
                            ? 'Siswa dengan nilai di bawah KKM otomatis dicentang sebagai peserta remidi.'
                            : 'Siswa yang belum mengerjakan ujian utama otomatis dicentang sebagai peserta susulan.'">
                    </p>
                </div>
                <button type="button" @click="showModalRemidi = false"
                    class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition cursor-pointer shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="form-remidi-susulan" action="{{ route('guru.ujian.buat-remidi-susulan', $ujian->id) }}"
                method="POST" novalidate @submit="validasiFormRemidi($event)"
                class="flex flex-col flex-1 min-h-0">
                @csrf
                <input type="hidden" name="jenis" :value="jenisModal">

                <div class="p-6 space-y-5 overflow-y-auto flex-1">
                    {{-- Judul --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            Judul Ujian <span x-text="jenisModal === 'remidi' ? 'Remidi' : 'Susulan'"></span>
                            <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="judul" x-ref="inputJudulRemidi" x-model="formRemidi.judul"
                            maxlength="120"
                            class="w-full rounded-xl border border-slate-200/80 bg-slate-50/40 px-3.5 py-2.5 text-xs font-semibold text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                    </div>

                    {{-- 3 Kebijakan Nilai Remidi (Khusus Remidi) --}}
                    <div x-show="jenisModal === 'remidi'"
                        class="p-4 rounded-xl bg-blue-50/50 border border-blue-200/70 space-y-2.5">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-blue-800">
                            Kebijakan Perhitungan Nilai Akhir Remidi
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-xs">
                            <label
                                class="p-3 rounded-xl bg-white border cursor-pointer flex items-start gap-2 transition"
                                :class="formRemidi.kebijakan === 'kkm'
                                    ? 'border-blue-600 ring-1 ring-blue-600/20 bg-blue-50/30'
                                    : 'border-slate-200/80 hover:border-blue-300'">
                                <input type="radio" name="kebijakan_remidi" value="kkm" x-model="formRemidi.kebijakan"
                                    class="mt-0.5 text-blue-600 accent-blue-600">
                                <div>
                                    <span class="font-bold text-slate-900 block">Maksimal KKM
                                        ({{ number_format($kkm, 0) }})</span>
                                    <span class="text-[11px] text-slate-500 leading-snug">Nilai remidi &ge; KKM dicatat
                                        sebesar KKM.</span>
                                </div>
                            </label>
                            <label
                                class="p-3 rounded-xl bg-white border cursor-pointer flex items-start gap-2 transition"
                                :class="formRemidi.kebijakan === 'terbaik'
                                    ? 'border-blue-600 ring-1 ring-blue-600/20 bg-blue-50/30'
                                    : 'border-slate-200/80 hover:border-blue-300'">
                                <input type="radio" name="kebijakan_remidi" value="terbaik"
                                    x-model="formRemidi.kebijakan" class="mt-0.5 text-blue-600 accent-blue-600">
                                <div>
                                    <span class="font-bold text-slate-900 block">Nilai Terbaik</span>
                                    <span class="text-[11px] text-slate-500 leading-snug">Ambil nilai tertinggi antara
                                        awal &amp; remidi.</span>
                                </div>
                            </label>
                            <label
                                class="p-3 rounded-xl bg-white border cursor-pointer flex items-start gap-2 transition"
                                :class="formRemidi.kebijakan === 'rata_rata'
                                    ? 'border-blue-600 ring-1 ring-blue-600/20 bg-blue-50/30'
                                    : 'border-slate-200/80 hover:border-blue-300'">
                                <input type="radio" name="kebijakan_remidi" value="rata_rata"
                                    x-model="formRemidi.kebijakan" class="mt-0.5 text-blue-600 accent-blue-600">
                                <div>
                                    <span class="font-bold text-slate-900 block">Rata-rata</span>
                                    <span class="text-[11px] text-slate-500 leading-snug">(Nilai Awal + Nilai Remidi)
                                        &divide; 2.</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Paket Soal & Durasi --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                                Paket Soal yang Digunakan
                            </label>
                            <select name="sumber_soal" x-model="formRemidi.sumberSoal"
                                class="w-full rounded-xl border border-slate-200/80 bg-white px-3.5 py-2.5 text-xs font-semibold text-slate-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition cursor-pointer">
                                <option value="salin_induk">Gunakan Soal Ujian Utama (Salin Otomatis)</option>
                                <option value="buat_baru">Buat Paket Soal Baru (Masuk Editor Soal)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                                Durasi Pengerjaan (Menit) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" name="durasi_menit" x-ref="inputDurasiRemidi"
                                x-model="formRemidi.durasi" min="1" max="999"
                                class="w-full rounded-xl border border-slate-200/80 bg-slate-50/40 px-3.5 py-2.5 text-xs font-semibold text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                        </div>
                    </div>

                    {{-- Jadwal Pelaksanaan --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                                Waktu Mulai <span class="text-rose-500">*</span>
                            </label>
                            <input type="datetime-local" name="waktu_mulai" x-ref="inputMulaiRemidi"
                                x-model="formRemidi.waktuMulai"
                                class="w-full rounded-xl border border-slate-200/80 bg-slate-50/40 px-3.5 py-2.5 text-xs font-semibold text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                                Batas Akhir (Deadline) <span class="text-rose-500">*</span>
                            </label>
                            <input type="datetime-local" name="deadline" x-ref="inputDeadlineRemidi"
                                x-model="formRemidi.deadline"
                                class="w-full rounded-xl border border-slate-200/80 bg-slate-50/40 px-3.5 py-2.5 text-xs font-semibold text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                        </div>
                    </div>

                    {{-- Daftar Siswa Peserta --}}
                    <div id="section-peserta-remidi">
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-600">
                                Daftar Siswa Peserta (<span class="text-blue-600"
                                    x-text="selectedSiswaIds.length"></span> dipilih) <span
                                    class="text-rose-500">*</span>
                            </label>
                            <div class="flex items-center gap-3 text-[11px]">
                                <button type="button" @click="pilihOtomatisSiswa()"
                                    class="text-blue-600 font-semibold hover:underline cursor-pointer">
                                    Reset Otomatis
                                </button>
                                <button type="button" @click="selectedSiswaIds = []"
                                    class="text-slate-400 hover:text-slate-600 hover:underline cursor-pointer">
                                    Kosongkan
                                </button>
                            </div>
                        </div>

                        <div
                            class="max-h-48 overflow-y-auto border border-slate-200/80 rounded-xl divide-y divide-slate-100 p-1.5 bg-slate-50/40">
                            <template x-for="p in pesertas" :key="p.siswa_id">
                                <label
                                    class="flex items-center justify-between p-2.5 rounded-lg hover:bg-white cursor-pointer text-xs transition-colors">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <input type="checkbox" name="siswa_ids[]" :value="String(p.siswa_id)"
                                            x-model="selectedSiswaIds"
                                            class="w-4 h-4 rounded border-slate-300 text-blue-600 accent-blue-600 focus:ring-blue-500 cursor-pointer">
                                        <div class="truncate">
                                            <span class="font-bold text-slate-800" x-text="p.nama"></span>
                                            <span class="text-[11px] text-slate-400 ml-1"
                                                x-text="'(' + p.kelas + ')'"></span>
                                        </div>
                                    </div>
                                    <span
                                        class="text-[11px] font-semibold px-2.5 py-0.5 rounded-full border shrink-0 ml-2"
                                        :class="{
                                            'bg-rose-50 text-rose-600 border-rose-200/70': p.status_kategori === 'remidi',
                                            'bg-slate-100 text-slate-600 border-slate-200/80': p.status_kategori === 'belum_ujian',
                                            'bg-emerald-50 text-emerald-700 border-emerald-200/70': p.status_kategori === 'tuntas',
                                            'bg-amber-50 text-amber-700 border-amber-200/80': p.status_kategori === 'menunggu_essay'
                                        }"
                                        x-text="p.sudah_selesai && !p.menunggu_koreksi_essay ? ('Nilai: ' + p.nilai_akhir) : p.status_label">
                                    </span>
                                </label>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Footer Modal --}}
                <div
                    class="px-6 py-4 bg-slate-50/70 border-t border-slate-100 flex items-center justify-end gap-2.5 shrink-0">
                    <button type="button" @click="showModalRemidi = false"
                        class="px-4 py-2 rounded-xl bg-white hover:bg-slate-50 border border-slate-200/80 text-xs font-semibold text-slate-700 transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-xs font-semibold text-white shadow-xs transition-colors cursor-pointer">
                        <span>Simpan &amp; Buat <span
                                x-text="jenisModal === 'remidi' ? 'Remidi' : 'Susulan'"></span></span>
                    </button>
                </div>
            </form>

        </div>
    </div>

    {{-- =========================================================
        MODAL ALERT VALIDASI SERAGAM (SESUAI DESAIN)
    ========================================================== --}}
    <div x-show="modalAlert.open" x-cloak x-transition.opacity @keydown.escape.window="closeModalAlert()"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-[2px]">
        <div @click.away="closeModalAlert()"
            class="bg-white rounded-2xl max-w-md sm:max-w-lg w-full border border-slate-200/80 shadow-2xl overflow-hidden">

            <div class="p-6 space-y-4">
                {{-- Header --}}
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-extrabold uppercase tracking-wider text-blue-600 flex items-center gap-1.5">
                            <template x-if="modalAlert.showDot">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                            </template>
                            <span x-text="modalAlert.kicker"></span>
                        </p>
                        <h3 class="text-xl font-extrabold text-slate-900 tracking-tight mt-1.5 leading-snug"
                            x-text="modalAlert.title"></h3>
                        <p x-show="modalAlert.subtitle"
                            class="text-xs sm:text-sm text-slate-500 mt-1.5 leading-relaxed"
                            x-text="modalAlert.subtitle"></p>
                    </div>

                    <button type="button" @click="closeModalAlert()"
                        class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition cursor-pointer shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Callout Box Peringatan --}}
                <div
                    class="flex items-start gap-3 p-4 rounded-xl border bg-amber-50/85 border-amber-200/90 text-amber-900 text-xs sm:text-[13px] leading-relaxed">
                    <div
                        class="w-7 h-7 rounded-lg bg-amber-100/80 text-amber-600 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="space-y-1">
                        <p class="font-bold" x-text="modalAlert.calloutTitle"></p>
                        <p x-show="modalAlert.calloutBody" class="opacity-90" x-text="modalAlert.calloutBody"></p>
                    </div>
                </div>

                {{-- Langkah Perbaikan --}}
                <template x-if="modalAlert.steps && modalAlert.steps.length > 0">
                    <div class="p-4 rounded-xl bg-slate-50/80 border border-slate-200/70 space-y-2.5">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500"
                            x-text="modalAlert.stepsTitle"></p>
                        <div class="space-y-2">
                            <template x-for="(step, sIdx) in modalAlert.steps" :key="sIdx">
                                <div class="flex items-start gap-2.5 text-xs text-slate-700 leading-relaxed">
                                    <span
                                        class="w-5 h-5 rounded-full bg-blue-100 text-blue-700 text-[11px] font-bold flex items-center justify-center shrink-0 mt-0.5"
                                        x-text="sIdx + 1"></span>
                                    <span x-html="step"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Footer --}}
            <div class="px-6 py-4 bg-slate-50/70 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <button type="button" @click="closeModalAlert()"
                    class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200/80 transition cursor-pointer"
                    x-text="modalAlert.cancelText">
                </button>

                <button type="button" @click="handleModalAlertAction()"
                    class="inline-flex items-center gap-1.5 px-5 py-2 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 transition shadow-xs cursor-pointer">
                    <span x-text="modalAlert.actionText"></span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </div>

        </div>
    </div>

</div>

<script>
function rekapNilaiApp(config) {
    return {
        pesertas: config.pesertas || [],
        kkm: config.kkm || 75,
        filterStatus: 'semua',
        searchQuery: '',

        // Pagination client-side
        currentPage: 1,
        perPage: 10,

        // Modal Buat Remidi / Susulan
        showModalRemidi: false,
        jenisModal: 'remidi',
        selectedSiswaIds: [],
        formRemidi: {
            judul: '',
            kebijakan: 'kkm',
            sumberSoal: 'salin_induk',
            durasi: config.durasiDefault || 60,
            waktuMulai: config.mulaiDefault || '',
            deadline: config.deadlineDefault || '',
        },

        // Modal Alert Validasi Seragam
        modalAlert: {
            open: false,
            showDot: false,
            kicker: '',
            title: '',
            subtitle: '',
            calloutTitle: '',
            calloutBody: '',
            stepsTitle: 'LANGKAH YANG DIPERLUKAN:',
            steps: [],
            cancelText: 'Batal',
            actionText: 'Mengerti',
            onAction: null,
        },

        openModalAlert(cfg) {
            this.modalAlert = {
                open: true,
                showDot: cfg.showDot ?? false,
                kicker: cfg.kicker || 'VALIDASI FORMULIR',
                title: cfg.title || 'Periksa Kembali Isian',
                subtitle: cfg.subtitle || '',
                calloutTitle: cfg.calloutTitle || '',
                calloutBody: cfg.calloutBody || '',
                stepsTitle: cfg.stepsTitle || 'LANGKAH YANG DIPERLUKAN:',
                steps: cfg.steps || [],
                cancelText: cfg.cancelText || 'Batal',
                actionText: cfg.actionText || 'Mengerti & Lengkapi',
                onAction: cfg.onAction || null,
            };
        },

        closeModalAlert() {
            this.modalAlert.open = false;
        },

        handleModalAlertAction() {
            const cb = this.modalAlert.onAction;
            this.modalAlert.open = false;
            if (typeof cb === 'function') {
                this.$nextTick(() => cb());
            }
        },

        setFilter(status) {
            this.filterStatus = status;
            this.currentPage = 1;
        },

        get filteredPesertas() {
            const q = this.searchQuery.toLowerCase();
            return this.pesertas.filter(p => {
                const matchSearch = (p.nama || '').toLowerCase().includes(q) ||
                                    (p.nisn || '').toLowerCase().includes(q);
                const matchStatus = this.filterStatus === 'semua' || p.status_kategori === this.filterStatus;
                return matchSearch && matchStatus;
            });
        },

        get totalPages() {
            return Math.max(1, Math.ceil(this.filteredPesertas.length / this.perPage));
        },

        get paginatedPesertas() {
            const start = (this.currentPage - 1) * this.perPage;
            return this.filteredPesertas.slice(start, start + this.perPage);
        },

        get rangeTampil() {
            const total = this.filteredPesertas.length;
            if (total === 0) return '0';
            const start = (this.currentPage - 1) * this.perPage + 1;
            const end = Math.min(total, this.currentPage * this.perPage);
            return `${start}–${end}`;
        },

        bukaModalRemidiSusulan(jenis) {
            this.jenisModal = jenis;
            this.formRemidi.judul = (jenis === 'remidi' ? 'Remidi - ' : 'Susulan - ') + (config.judulDefault || '');
            this.formRemidi.kebijakan = 'kkm';
            this.formRemidi.sumberSoal = 'salin_induk';
            this.formRemidi.durasi = config.durasiDefault || 60;
            this.formRemidi.waktuMulai = config.mulaiDefault || '';
            this.formRemidi.deadline = config.deadlineDefault || '';
            this.pilihOtomatisSiswa();
            this.showModalRemidi = true;
        },

        pilihOtomatisSiswa() {
            const targetKategori = this.jenisModal === 'remidi' ? 'remidi' : 'belum_ujian';
            this.selectedSiswaIds = this.pesertas
                .filter(p => p.status_kategori === targetKategori)
                .map(p => String(p.siswa_id));
        },

        validasiFormRemidi(event) {
            const labelJenis = this.jenisModal === 'remidi' ? 'Remidi' : 'Susulan';

            if (!this.formRemidi.judul || !this.formRemidi.judul.trim()) {
                event.preventDefault();
                this.openModalAlert({
                    kicker: 'VALIDASI INSTRUMEN UJIAN',
                    title: `Judul Ujian ${labelJenis} Wajib Diisi!`,
                    subtitle: `Lengkapi judul ujian ${labelJenis.toLowerCase()} sebelum menyimpan.`,
                    calloutTitle: 'Kolom Judul Ujian tidak boleh kosong.',
                    calloutBody: 'Masukkan judul yang jelas agar siswa dapat mengenali jadwal ujian dengan mudah.',
                    cancelText: 'Tutup',
                    actionText: 'Isi Judul Sekarang',
                    onAction: () => this.$refs.inputJudulRemidi?.focus(),
                });
                return;
            }

            const durasi = Number(this.formRemidi.durasi || 0);
            if (!durasi || durasi < 1 || durasi > 999) {
                event.preventDefault();
                this.openModalAlert({
                    kicker: 'VALIDASI PENGATURAN UJIAN',
                    title: 'Durasi Pengerjaan Tidak Valid!',
                    subtitle: 'Tentukan lama waktu pengerjaan ujian sebelum melanjutkan.',
                    calloutTitle: 'Durasi pengerjaan harus diisi antara 1 sampai 999 menit!',
                    cancelText: 'Tutup',
                    actionText: 'Atur Durasi',
                    onAction: () => this.$refs.inputDurasiRemidi?.focus(),
                });
                return;
            }

            if (!this.formRemidi.waktuMulai || !this.formRemidi.deadline) {
                event.preventDefault();
                this.openModalAlert({
                    showDot: true,
                    kicker: 'VALIDASI JADWAL UJIAN',
                    title: 'Jadwal Ujian Belum Lengkap!',
                    subtitle: `Tentukan Waktu Mulai dan Batas Akhir (Deadline) untuk ujian ${labelJenis.toLowerCase()}.`,
                    calloutTitle: 'Kolom Waktu Mulai dan Deadline wajib diisi!',
                    calloutBody: 'Sistem membutuhkan jadwal lengkap agar sesi ujian aktif sesuai waktu yang ditentukan.',
                    stepsTitle: 'LANGKAH PERBAIKAN CEPAT:',
                    steps: [
                        'Isi tanggal dan jam pada kolom <strong>Waktu Mulai</strong>.',
                        'Isi tanggal dan jam pada kolom <strong>Batas Akhir (Deadline)</strong>.',
                        'Pastikan batas akhir lebih lama daripada waktu mulai.',
                    ],
                    cancelText: 'Tutup',
                    actionText: 'Atur Jadwal',
                    onAction: () => {
                        if (!this.formRemidi.waktuMulai) {
                            this.$refs.inputMulaiRemidi?.focus();
                        } else {
                            this.$refs.inputDeadlineRemidi?.focus();
                        }
                    },
                });
                return;
            }

            if (new Date(this.formRemidi.deadline) <= new Date(this.formRemidi.waktuMulai)) {
                event.preventDefault();
                this.openModalAlert({
                    showDot: true,
                    kicker: 'VALIDASI JADWAL UJIAN',
                    title: 'Rentang Jadwal Tidak Valid!',
                    subtitle: 'Periksa kembali perbandingan antara Waktu Mulai dan Deadline.',
                    calloutTitle: 'Batas Akhir (Deadline) harus lebih akhir daripada Waktu Mulai!',
                    cancelText: 'Tutup',
                    actionText: 'Perbaiki Deadline',
                    onAction: () => this.$refs.inputDeadlineRemidi?.focus(),
                });
                return;
            }

            if (this.selectedSiswaIds.length === 0) {
                event.preventDefault();
                this.openModalAlert({
                    kicker: 'VALIDASI PESERTA UJIAN',
                    title: 'Siswa Peserta Belum Dipilih!',
                    subtitle: `Silakan pilih minimal 1 siswa yang akan mengikuti ujian ${labelJenis.toLowerCase()} sebelum menyimpan.`,
                    calloutTitle: 'Centang minimal 1 siswa pada Daftar Siswa Peserta terlebih dahulu.',
                    stepsTitle: 'LANGKAH YANG DIPERLUKAN:',
                    steps: [
                        `Periksa daftar siswa pada kotak <strong>Daftar Siswa Peserta</strong>.`,
                        `Klik <strong>Reset Otomatis</strong> untuk mencentang siswa ${this.jenisModal === 'remidi' ? 'di bawah KKM' : 'yang belum ujian'}, atau centang manual siswa yang dituju.`,
                        `Setelah minimal 1 siswa terpilih, klik tombol <strong>Simpan &amp; Buat ${labelJenis}</strong>.`,
                    ],
                    cancelText: 'Batal',
                    actionText: 'Pilih Siswa Sekarang',
                    onAction: () => {
                        document.getElementById('section-peserta-remidi')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    },
                });
                return;
            }
        }
    };
}
</script>
@endsection
