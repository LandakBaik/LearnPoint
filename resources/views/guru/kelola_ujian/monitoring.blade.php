@extends('layouts.app')

@php
    $modeArsip = $isSelesai ?? $ujian->status === 'completed';
@endphp

@section('title', ($modeArsip ? 'Hasil Monitoring: ' : 'Monitoring Live: ') . $ujian->judul)

@section('content')
    <div class="max-w-7xl mx-auto space-y-6" x-data="monitoringLive({
        ujianId: {{ $ujian->id }},
        totalSoal: {{ (int) $totalSoal }},
        durasiMenit: {{ (int) ($ujian->durasi_menit ?? 60) }},
        judulUjian: {{ Js::from($ujian->judul) }},
        isSelesai: {{ $modeArsip ? 'true' : 'false' }},
        daftarSesi: {{ Js::from($daftarSesi ?? []) }},
        unlockCode: {{ Js::from($unlockCode ?? '') }}
    })">

        {{-- =========================================================
            HEADER & CONTROLS
        ========================================================== --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div class="space-y-1 min-w-0">
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
                        {{ $ujian->judul }}
                    </h1>

                    @if ($modeArsip)
                        <span
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200 shadow-2xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span>
                            Hasil Monitoring
                        </span>
                    @else
                        <span
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/80 shadow-2xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Monitoring Live
                        </span>
                    @endif
                </div>

                <p class="text-sm text-slate-500 leading-relaxed font-normal">
                    {{ $ujian->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? 'Mata Pelajaran' }}
                    <span class="mx-1.5 text-slate-300">&bull;</span>
                    Total Soal: <span class="font-semibold text-slate-700">{{ $totalSoal }} Butir</span>
                    @if ($modeArsip)
                        <span class="mx-1.5 text-slate-300">&bull;</span>
                        <span class="text-slate-600 font-medium">Ujian telah berakhir (Mode Riwayat)</span>
                    @endif
                </p>
            </div>

            {{-- SINKRONISASI / AKSI HEADER --}}
            <div class="flex items-center gap-2.5 flex-wrap shrink-0">
                @if ($modeArsip)
                    <a href="{{ route('guru.ujian.rekap', $ujian->id) }}"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold transition-all duration-200 shadow-xs hover:shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <span>Buka Rekap Nilai</span>
                    </a>
                @else
                    <span
                        class="inline-flex items-center gap-2 text-xs font-medium text-slate-600 bg-white border border-slate-200/80 px-3.5 py-2.5 rounded-xl shadow-xs">
                        <span class="w-2 h-2 rounded-full"
                            :class="isSyncing ? 'bg-amber-400 animate-ping' : 'bg-emerald-500'"></span>
                        <span x-text="isSyncing ? 'Menyinkronkan data...' : 'Auto-sync tiap 5 dtk'"></span>
                    </span>

                    <button type="button" @click="fetchDataMonitoring()"
                        class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200/80 rounded-xl text-xs font-semibold transition-all duration-200 shadow-xs hover:shadow-sm cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-slate-500" :class="isSyncing ? 'animate-spin' : ''" fill="none"
                            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span>Refresh</span>
                    </button>
                @endif
            </div>
        </div>

        {{-- =========================================================
            PANEL TOKEN UJIAN PER SESI & PENGATURAN SESI KELAS (HANYA SAAT LIVE)
        ========================================================== --}}
        @if (!$modeArsip)
            <div
                class="bg-white border border-slate-200/80 rounded-2xl shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden">

                {{-- Header Kartu Token --}}
                <div
                    class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3 group">
                        <div
                            class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 border border-amber-200/60 shadow-xs flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900 tracking-tight">
                                Daftar Token Sesi Ujian
                            </h2>
                            <p class="text-xs text-slate-400 mt-0.5 leading-relaxed">
                                Kelas dalam sesi yang sama menggunakan token yang sama. Timer siswa otomatis menyesuaikan
                                jam aktif sesi.
                            </p>
                        </div>
                    </div>

                    <button type="button" @click="showModalSesi = true"
                        class="inline-flex items-center gap-2 px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200/80 rounded-xl text-xs font-semibold transition shadow-2xs cursor-pointer self-start sm:self-auto shrink-0">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="1.8"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>Atur Pembagian Sesi Kelas</span>
                    </button>
                </div>

                <div class="p-6 space-y-5">
                    {{-- Banner Kode Buka Blokir Darurat (Unlock Code) --}}
                    <div
                        class="rounded-2xl bg-rose-50/70 border border-rose-200/80 px-4 py-3.5 flex flex-col lg:flex-row lg:items-center justify-between gap-3.5">
                        <div class="flex items-start sm:items-center gap-3">
                            <div
                                class="w-9 h-9 rounded-xl bg-rose-100/90 text-rose-600 border border-rose-200/60 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-[11px] font-extrabold uppercase tracking-wider text-rose-700">
                                    Kode Buka Blokir Darurat (Berlaku Untuk Semua Peserta Ujian Ini)
                                </p>
                                <p class="text-xs text-rose-600/90 mt-0.5 leading-relaxed">
                                    Berikan kode ini kepada siswa yang terkunci, lalu klik <strong>Refresh Kode</strong>
                                    setelah digunakan agar tidak disalahgunakan.
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 flex-wrap shrink-0">
                            <span
                                class="font-mono text-base font-extrabold tracking-widest text-slate-900 bg-white border border-slate-200/90 px-4 py-2 rounded-xl select-all shadow-2xs"
                                x-text="unlockCode"></span>

                            <button type="button" @click="salinUnlockCode()"
                                class="inline-flex items-center gap-1.5 px-3 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200/80 rounded-xl text-xs font-semibold transition shadow-2xs cursor-pointer">
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor"
                                    stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                <span>Salin</span>
                            </button>

                            <button type="button" @click="konfirmasiRefreshUnlockCode()" :disabled="isRefreshingUnlock"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200/90 rounded-xl text-xs font-semibold transition shadow-2xs cursor-pointer disabled:opacity-50">
                                <svg class="w-3.5 h-3.5" :class="isRefreshingUnlock ? 'animate-spin' : ''" fill="none"
                                    stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <span>Refresh Kode</span>
                            </button>
                        </div>
                    </div>

                    {{-- Daftar Kartu Sesi --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <template x-for="item in daftarSesi" :key="item.sesi">
                            <div
                                class="rounded-2xl border border-slate-200/80 bg-white p-5 flex flex-col justify-between gap-4 shadow-2xs hover:border-blue-200 transition-all duration-200">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0 space-y-2">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-600 border border-blue-200/70">
                                                SESI <span class="ml-1" x-text="item.sesi"></span>
                                            </span>
                                            <p class="text-sm font-bold text-slate-900 truncate">
                                                Kelas: <span x-text="item.kelas_list.join(', ')"></span>
                                            </p>
                                        </div>

                                        <p class="text-xs text-slate-500 flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none"
                                                stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span>Jadwal Sesi:</span>
                                            <strong class="text-slate-700 font-semibold"
                                                x-text="item.jam_mulai + ' – ' + item.jam_selesai + ' WIB'"></strong>
                                        </p>
                                    </div>

                                    <div class="text-right shrink-0">
                                        <span
                                            class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Token
                                            Aktif</span>
                                        <span
                                            class="mt-1 inline-block font-mono text-base font-extrabold tracking-widest text-blue-700 bg-blue-50/80 border border-blue-200/80 px-3.5 py-1.5 rounded-xl select-all"
                                            x-text="item.token"></span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-3 gap-2.5 pt-3.5 border-t border-slate-100">
                                    <button type="button" @click="salinToken(item.token)"
                                        class="inline-flex items-center justify-center gap-1.5 py-2 px-3 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200/80 rounded-xl text-xs font-semibold transition cursor-pointer">
                                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor"
                                            stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                        <span>Salin</span>
                                    </button>

                                    <button type="button" @click="konfirmasiRilisToken(item, false)"
                                        class="inline-flex items-center justify-center gap-1.5 py-2 px-3 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200/80 rounded-xl text-xs font-semibold transition cursor-pointer">
                                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor"
                                            stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        <span>Acak Token</span>
                                    </button>

                                    <button type="button" @click="konfirmasiRilisToken(item, true)"
                                        class="inline-flex items-center justify-center gap-1.5 py-2 px-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold transition shadow-xs cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M8 5v14l11-7z" />
                                        </svg>
                                        <span>Mulai Sesi</span>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- MODAL PENGATURAN PEMBAGIAN SESI KELAS --}}
            <div x-show="showModalSesi" x-cloak x-transition.opacity
                @keydown.escape.window="showModalSesi = false"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/45 backdrop-blur-[2px] p-4">
                <div @click.away="showModalSesi = false"
                    class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-200/80 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-start justify-between gap-4">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-blue-600 block">
                                Pengaturan Sesi Ujian
                            </span>
                            <h3 class="text-lg font-extrabold text-slate-900 tracking-tight mt-0.5">
                                Atur Pembagian Sesi &amp; Jam Kelas
                            </h3>
                        </div>
                        <button type="button" @click="showModalSesi = false"
                            class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form action="{{ route('guru.ujian.atur-sesi', $ujian->id) }}" method="POST">
                        @csrf
                        <div class="p-6 space-y-5">
                            <div class="space-y-2.5 max-h-60 overflow-y-auto pr-1">
                                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                    1. Pilih Sesi untuk Masing-Masing Kelas
                                </p>
                                @foreach ($grupUjians as $itemUjian)
                                    <div
                                        class="flex items-center justify-between p-3 rounded-xl bg-slate-50/80 border border-slate-200/80">
                                        <div>
                                            <p class="text-xs font-bold text-slate-900">
                                                Kelas {{ $itemUjian->pengampuKelas?->kelas?->nama_kelas ?? '-' }}
                                            </p>
                                            <p class="text-[11px] text-slate-400 mt-0.5">
                                                Token saat ini: <span
                                                    class="font-mono font-semibold text-slate-600">{{ $itemUjian->token ?? '-' }}</span>
                                            </p>
                                        </div>
                                        <select name="sesi_ujian[{{ $itemUjian->id }}]"
                                            class="px-3 py-1.5 rounded-xl border border-slate-200/80 text-xs font-semibold text-blue-700 bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none cursor-pointer">
                                            @for ($s = 1; $s <= 5; $s++)
                                                <option value="{{ $s }}"
                                                    {{ (int) ($itemUjian->sesi ?? 1) === $s ? 'selected' : '' }}>
                                                    Sesi {{ $s }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                @endforeach
                            </div>

                            <div class="space-y-2.5 pt-4 border-t border-slate-100">
                                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                    2. Jam Mulai per Sesi (Opsional)
                                </p>
                                <div class="grid grid-cols-2 gap-2.5">
                                    @for ($s = 1; $s <= 4; $s++)
                                        @php
                                            $sampleSesi = $grupUjians->where('sesi', $s)->first();
                                            $defaultJam =
                                                $sampleSesi && $sampleSesi->waktu_mulai_sesi
                                                    ? \Carbon\Carbon::parse($sampleSesi->waktu_mulai_sesi)->format('H:i')
                                                    : '';
                                        @endphp
                                        <div
                                            class="p-2.5 rounded-xl border border-slate-200/80 bg-slate-50/50 flex items-center justify-between gap-2">
                                            <span class="text-xs font-semibold text-slate-700">Sesi {{ $s }}:</span>
                                            <input type="time" name="jam_sesi[{{ $s }}]"
                                                value="{{ $defaultJam }}"
                                                class="text-xs font-mono font-bold text-slate-800 bg-white border border-slate-200/80 rounded-lg px-2 py-1 focus:border-blue-500 outline-none">
                                        </div>
                                    @endfor
                                </div>
                                <p class="text-[11px] text-slate-400 leading-relaxed">
                                    *Jika jam dikosongkan, jadwal sesi dihitung berurutan otomatis sesuai durasi ujian
                                    ({{ $ujian->durasi_menit }} menit/sesi).
                                </p>
                            </div>
                        </div>

                        <div
                            class="px-6 py-4 bg-slate-50/70 border-t border-slate-100 flex items-center justify-end gap-2.5">
                            <button type="button" @click="showModalSesi = false"
                                class="px-4 py-2 rounded-xl bg-white hover:bg-slate-50 border border-slate-200/80 text-xs font-semibold text-slate-700 transition cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-xs font-semibold text-white shadow-xs transition cursor-pointer">
                                Simpan Pengaturan Sesi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- =========================================================
            5 KARTU STATISTIK PENGERJAAN & PELANGGARAN
        ========================================================== --}}
        <section class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
            {{-- 1. Total Peserta --}}
            <div
                class="group bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md hover:border-blue-200 transition-all duration-200">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Peserta</span>
                    <div
                        class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 border border-blue-100/80 shadow-xs flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                </div>
                <p class="mt-2.5 text-2xl font-extrabold text-slate-900 leading-tight" x-text="stats.total_peserta"></p>
                <p class="mt-2.5 pt-2 border-t border-slate-100 text-xs text-slate-400 truncate">Terdaftar dalam sesi</p>
            </div>

            {{-- 2. Sedang Aktif / Tanpa Pelanggaran --}}
            @if (!$modeArsip)
                <div
                    class="group bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md hover:border-emerald-200 transition-all duration-200">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Sedang Aktif</span>
                        <div
                            class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200/60 shadow-xs flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-2.5 text-2xl font-extrabold text-slate-900 leading-tight" x-text="stats.aktif"></p>
                    <p class="mt-2.5 pt-2 border-t border-slate-100 text-xs text-slate-400 truncate">Sedang mengerjakan</p>
                </div>
            @else
                <div
                    class="group bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md hover:border-emerald-200 transition-all duration-200">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Tanpa
                            Pelanggaran</span>
                        <div
                            class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200/60 shadow-xs flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-2.5 text-2xl font-extrabold text-slate-900 leading-tight"
                        x-text="Math.max(0, stats.total_peserta - stats.pelanggaran)"></p>
                    <p class="mt-2.5 pt-2 border-t border-slate-100 text-xs text-slate-400 truncate">Evaluasi berjalan
                        bersih</p>
                </div>
            @endif

            {{-- 3. Telah Mengumpulkan --}}
            <div
                class="group bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md hover:border-sky-200 transition-all duration-200">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Telah Mengumpulkan</span>
                    <div
                        class="w-9 h-9 rounded-xl bg-sky-50 text-sky-600 border border-sky-200/60 shadow-xs flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="mt-2.5 text-2xl font-extrabold text-slate-900 leading-tight" x-text="stats.selesai"></p>
                <p class="mt-2.5 pt-2 border-t border-slate-100 text-xs text-slate-400 truncate">Jawaban tersimpan</p>
            </div>

            {{-- 4. Sesi Terkunci --}}
            <div
                class="group bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md hover:border-amber-200 transition-all duration-200">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        {{ $modeArsip ? 'Terkunci Akhir' : 'Sesi Terkunci' }}
                    </span>
                    <div
                        class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 border border-amber-200/60 shadow-xs flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                </div>
                <p class="mt-2.5 text-2xl font-extrabold text-slate-900 leading-tight" x-text="stats.terkunci"></p>
                <p class="mt-2.5 pt-2 border-t border-slate-100 text-xs text-slate-400 truncate">
                    {{ $modeArsip ? 'Terkunci saat berakhir' : 'Perlu buka blokir' }}
                </p>
            </div>

            {{-- 5. Siswa Melanggar --}}
            <div
                class="group bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md hover:border-rose-200 transition-all duration-200 col-span-2 sm:col-span-1">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Siswa Melanggar</span>
                    <div
                        class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 border border-rose-200/60 shadow-xs flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-2.5 flex items-baseline gap-1.5">
                    <p class="text-2xl font-extrabold text-slate-900 leading-tight" x-text="stats.pelanggaran"></p>
                    <span class="text-xs font-bold text-rose-600"
                        x-text="'(' + (stats.total_kejadian_pelanggaran || 0) + 'x total)'"></span>
                </div>
                <p class="mt-2.5 pt-2 border-t border-slate-100 text-xs text-slate-400 truncate">Terdeteksi sistem</p>
            </div>
        </section>

        {{-- =========================================================
            KARTU TABEL MONITORING PESERTA (FILTER + TABEL + PAGINATION)
        ========================================================== --}}
        <div
            class="bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden">

            {{-- BAR PENCARIAN & FILTER --}}
            <div
                class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white">
                <div class="flex flex-col sm:flex-row sm:items-center gap-2.5 flex-1">
                    <div class="relative flex-1 max-w-md">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"
                            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m21 21-4.35-4.35m2.35-5.65a8 8 0 11-16 0 8 8 0 0116 0z" />
                        </svg>
                        <input type="text" x-model="searchQuery" @input="currentPage = 1"
                            placeholder="Cari nama siswa, kelas, atau NISN..."
                            class="w-full pl-9 pr-3.5 py-2 rounded-xl border border-slate-200/80 bg-slate-50/50 text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                    </div>

                    <select x-model="filterStatus" @change="currentPage = 1"
                        class="px-3.5 py-2 rounded-xl border border-slate-200/80 bg-white text-xs font-medium text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none cursor-pointer">
                        <option value="">Semua Status</option>
                        @if (!$modeArsip)
                            <option value="sedang_mengerjakan">Sedang Mengerjakan</option>
                        @endif
                        <option value="selesai">Selesai</option>
                        <option value="terkunci">Terkunci</option>
                        <option value="pelanggaran">Ada Pelanggaran</option>
                    </select>

                    @if ($modeArsip)
                        <button type="button"
                            @click="filterStatus = (filterStatus === 'pelanggaran' ? '' : 'pelanggaran'); currentPage = 1"
                            :class="filterStatus === 'pelanggaran'
                                ? 'bg-rose-600 text-white border-rose-600'
                                : 'bg-rose-50 text-rose-600 border-rose-200/80 hover:bg-rose-100'"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border text-xs font-semibold transition cursor-pointer">
                            <span>Hanya Melanggar (<span x-text="stats.pelanggaran"></span>)</span>
                        </button>
                    @endif
                </div>

                @if ($modeArsip)
                    <div class="flex items-center justify-end shrink-0">
                        <button type="button" @click="eksporDataCsv()"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200/80 text-xs font-semibold transition shadow-2xs cursor-pointer">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span>Ekspor Data</span>
                        </button>
                    </div>
                @endif
            </div>

            {{-- TABEL PESERTA --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-slate-50/70 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200/60">
                            <th class="py-3.5 pl-6 pr-3 w-12 text-center">#</th>
                            <th class="py-3.5 px-4">Nama Siswa &amp; Kelas</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Status</th>
                            <th class="py-3.5 px-4 w-56">Progres Soal</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">
                                {{ $modeArsip ? 'Waktu Selesai & Nilai' : 'Sisa Waktu' }}
                            </th>
                            <th class="py-3.5 px-4 text-center whitespace-nowrap">Log Pelanggaran</th>
                            <th class="py-3.5 pl-4 pr-6 text-right whitespace-nowrap">
                                {{ $modeArsip ? 'Lembar Jawaban' : 'Aksi Darurat' }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        <template x-for="(peserta, index) in paginatedPesertas" :key="peserta.seleksi_id">
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                {{-- Nomor --}}
                                <td class="py-4 pl-6 pr-3 text-center font-semibold text-slate-400"
                                    x-text="((safePage - 1) * perPage) + index + 1"></td>

                                {{-- Info Siswa --}}
                                <td class="py-4 px-4 min-w-[210px]">
                                    <div class="font-bold text-slate-900 text-xs sm:text-[13px] leading-snug"
                                        x-text="peserta.nama"></div>
                                    <div class="mt-1 flex items-center gap-1.5 flex-wrap text-[11px] text-slate-400">
                                        <span class="font-mono" x-text="'NISN: ' + peserta.nisn"></span>
                                        <span class="text-slate-300">&bull;</span>
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full bg-blue-50 text-blue-600 border border-blue-100/80 font-semibold"
                                            x-text="'Kelas ' + peserta.kelas + ' (Sesi ' + peserta.sesi + ')'"></span>
                                    </div>
                                </td>

                                {{-- Status Badge --}}
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold border shadow-2xs"
                                        :class="{
                                            'bg-blue-50 text-blue-600 border-blue-200/70': peserta.status === 'sedang_mengerjakan' && peserta.sisa_detik > 0,
                                            'bg-emerald-50 text-emerald-700 border-emerald-200/70': peserta.status === 'selesai',
                                            'bg-rose-50 text-rose-600 border-rose-200/70': peserta.status === 'terkunci',
                                            'bg-slate-100 text-slate-600 border-slate-200/80': peserta.status === 'belum_mulai' || (peserta.status === 'sedang_mengerjakan' && peserta.sisa_detik <= 0)
                                        }">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                        <span x-text="peserta.status_label"></span>
                                    </span>
                                </td>

                                {{-- Progres Pengerjaan Soal --}}
                                <td class="py-4 px-4 min-w-[180px]">
                                    <div class="flex items-center justify-between text-[11px] font-bold mb-1.5">
                                        <span class="text-slate-700"
                                            x-text="peserta.total_dijawab + ' / ' + totalSoal + ' Soal'"></span>
                                        <span class="text-blue-600" x-text="peserta.progres_persen + '%'"></span>
                                    </div>
                                    <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                        <div class="h-1.5 rounded-full bg-blue-600 transition-all duration-300"
                                            :style="`width: ${peserta.progres_persen}%`"></div>
                                    </div>
                                </td>

                                {{-- Sisa Waktu (Live) ATAU Waktu Selesai & Nilai (Hasil Monitoring) --}}
                                <td class="py-4 px-4 whitespace-nowrap">
                                    @if ($modeArsip)
                                        <div>
                                            <div class="font-bold text-slate-800"
                                                x-text="peserta.nilai_akhir !== null ? ('Nilai: ' + peserta.nilai_akhir) : 'Belum Dinilai'">
                                            </div>
                                            <div class="text-[11px] text-slate-400 mt-0.5"
                                                x-text="peserta.waktu_selesai || '-'"></div>
                                        </div>
                                    @else
                                        <template x-if="peserta.status === 'sedang_mengerjakan' && peserta.sisa_detik > 0">
                                            <span class="font-mono font-bold text-xs"
                                                :class="peserta.sisa_detik < 300 ? 'text-rose-600 animate-pulse' : 'text-slate-800'"
                                                x-text="formatWaktu(peserta.sisa_detik)"></span>
                                        </template>
                                        <template x-if="peserta.status === 'selesai'">
                                            <span class="text-slate-400 font-medium italic text-xs">Tuntas</span>
                                        </template>
                                        <template x-if="peserta.status !== 'selesai' && peserta.sisa_detik <= 0">
                                            <span class="text-rose-600 font-semibold italic text-xs">Habis</span>
                                        </template>
                                    @endif
                                </td>

                                {{-- Log Pelanggaran (Anti-Cheat Kumulatif) --}}
                                <td class="py-4 px-4 text-center whitespace-nowrap">
                                    <div class="inline-flex flex-col items-center gap-1">
                                        <span
                                            class="inline-flex items-center justify-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold border shadow-2xs"
                                            :class="peserta.total_pelanggaran >= 3
                                                ? 'bg-rose-600 text-white border-rose-600'
                                                : (peserta.total_pelanggaran > 0
                                                    ? 'bg-rose-50 text-rose-600 border-rose-200/80'
                                                    : 'bg-emerald-50 text-emerald-700 border-emerald-200/70')">
                                            <template x-if="peserta.total_pelanggaran > 0">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                            </template>
                                            <template x-if="peserta.total_pelanggaran === 0">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor"
                                                    stroke-width="2.2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M5 13l4 4L19 7" />
                                                </svg>
                                            </template>
                                            <span
                                                x-text="peserta.total_pelanggaran > 0 ? (peserta.total_pelanggaran + 'x Pelanggaran') : '0x'"></span>
                                        </span>

                                        <span
                                            x-show="peserta.total_pelanggaran >= 3 && peserta.status === 'sedang_mengerjakan'"
                                            class="text-[10px] font-bold text-rose-600">
                                            Toleransi habis
                                        </span>
                                    </div>
                                </td>

                                {{-- Aksi Darurat (Live) / Lembar Jawaban (Riwayat) --}}
                                <td class="py-4 pl-4 pr-6 text-right whitespace-nowrap">
                                    @if (!$modeArsip)
                                        <div class="inline-flex items-center gap-1.5">
                                            <button type="button" x-show="peserta.status === 'terkunci'"
                                                @click="konfirmasiBukaKunci(peserta)"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200/80 rounded-xl text-xs font-semibold transition shadow-2xs cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                                </svg>
                                                <span>Buka Kunci</span>
                                            </button>

                                            <button type="button"
                                                x-show="peserta.status === 'sedang_mengerjakan' || peserta.status === 'terkunci'"
                                                @click="konfirmasiPaksaSelesai(peserta)"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200/80 rounded-xl text-xs font-semibold transition shadow-2xs cursor-pointer">
                                                <span>Paksa Kumpul</span>
                                            </button>

                                            <button type="button" x-show="peserta.status === 'selesai'"
                                                @click="konfirmasiBukaKembali(peserta)"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200/80 rounded-xl text-xs font-semibold transition shadow-2xs cursor-pointer"
                                                title="Izinkan siswa melanjutkan ujian dengan jawaban sebelumnya">
                                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none"
                                                    stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                </svg>
                                                <span>Buka Kembali</span>
                                            </button>
                                        </div>
                                    @else
                                        <a :href="`/guru/ujian/${ujianId}/rekap/siswa/${peserta.siswa_id}`"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50/70 hover:bg-blue-100 text-blue-600 border border-blue-200/80 rounded-xl text-xs font-semibold transition shadow-2xs">
                                            <span>Lihat Jawaban</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                            </svg>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        </template>

                        <tr x-show="filteredPesertas.length === 0" x-cloak>
                            <td colspan="7" class="py-12 px-6 text-center">
                                <p class="text-sm font-bold text-slate-700">Tidak ada peserta ditemukan</p>
                                <p class="text-xs text-slate-400 mt-1">
                                    Belum ada peserta yang masuk atau cocok dengan filter pencarian Anda.
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
                        <button type="button" @click="if (safePage > 1) currentPage = safePage - 1"
                            :disabled="safePage <= 1"
                            class="w-8 h-8 inline-flex items-center justify-center rounded-lg border border-slate-200/80 text-slate-600 hover:bg-slate-50 disabled:text-slate-300 disabled:cursor-not-allowed transition-colors cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>

                        <template x-for="page in totalPages" :key="page">
                            <button type="button" @click="currentPage = page"
                                class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-xs transition-colors cursor-pointer"
                                :class="safePage === page
                                    ? 'bg-blue-600 text-white font-bold shadow-xs'
                                    : 'border border-slate-200/80 text-slate-600 hover:bg-slate-50 font-semibold'"
                                x-text="page"></button>
                        </template>

                        <button type="button" @click="if (safePage < totalPages) currentPage = safePage + 1"
                            :disabled="safePage >= totalPages"
                            class="w-8 h-8 inline-flex items-center justify-center rounded-lg border border-slate-200/80 text-slate-600 hover:bg-slate-50 disabled:text-slate-300 disabled:cursor-not-allowed transition-colors cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

        </div>

        {{-- =========================================================
            MODAL KONFIRMASI & ALERT SERAGAM (SESUAI DESAIN)
        ========================================================== --}}
        <div x-show="modalAlert.open" x-cloak x-transition.opacity
            @keydown.escape.window="if (!modalAlert.loading) closeModalAlert()"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/45 backdrop-blur-[2px]">
            <div @click.away="if (!modalAlert.loading) closeModalAlert()"
                class="bg-white rounded-2xl max-w-md sm:max-w-lg w-full border border-slate-200/80 shadow-2xl overflow-hidden">

                <div class="p-6 space-y-4">
                    {{-- Header Modal --}}
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-extrabold uppercase tracking-wider flex items-center gap-1.5"
                                :class="modalAlert.variant === 'danger' ? 'text-rose-600' : 'text-blue-600'">
                                <span class="w-1.5 h-1.5 rounded-full"
                                    :class="modalAlert.variant === 'danger' ? 'bg-rose-600' : 'bg-blue-600'"></span>
                                <span x-text="modalAlert.kicker"></span>
                            </p>
                            <h3 class="text-xl font-extrabold text-slate-900 tracking-tight mt-1.5 leading-snug"
                                x-text="modalAlert.title"></h3>
                            <p x-show="modalAlert.subtitle"
                                class="text-xs sm:text-sm text-slate-500 mt-1.5 leading-relaxed"
                                x-text="modalAlert.subtitle"></p>
                        </div>

                        <button type="button" @click="if (!modalAlert.loading) closeModalAlert()"
                            class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition cursor-pointer shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Info Box Konteks (Sesi / Peserta / Kode) --}}
                    <template x-if="modalAlert.infoCard">
                        <div
                            class="flex flex-wrap items-center justify-between gap-3 p-3.5 rounded-xl bg-slate-50/90 border border-slate-200/70">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 rounded-xl border flex items-center justify-center shrink-0"
                                    :class="modalAlert.variant === 'danger'
                                        ? 'bg-rose-50 text-rose-600 border-rose-200/70'
                                        : 'bg-blue-50 text-blue-600 border-blue-100/80'">
                                    <template x-if="modalAlert.infoCard.icon === 'play'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                        </svg>
                                    </template>
                                    <template x-if="modalAlert.infoCard.icon === 'refresh'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                    </template>
                                    <template x-if="modalAlert.infoCard.icon === 'unlock'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                        </svg>
                                    </template>
                                    <template x-if="modalAlert.infoCard.icon === 'user'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </template>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs sm:text-sm font-bold text-slate-900 truncate"
                                        x-text="modalAlert.infoCard.title"></p>
                                    <p class="text-[11px] text-slate-500 mt-0.5 truncate"
                                        x-text="modalAlert.infoCard.meta"></p>
                                </div>
                            </div>

                            <template x-if="modalAlert.infoCard.badge">
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold border shrink-0"
                                    :class="modalAlert.infoCard.badgeClass || 'bg-emerald-50 text-emerald-700 border-emerald-200/80'"
                                    x-text="modalAlert.infoCard.badge"></span>
                            </template>
                        </div>
                    </template>

                    {{-- Callout Box Peringatan --}}
                    <div class="flex items-start gap-3 p-4 rounded-xl border text-xs sm:text-[13px] leading-relaxed"
                        :class="modalAlert.calloutVariant === 'danger'
                            ? 'bg-rose-50/90 border-rose-200 text-rose-800'
                            : 'bg-amber-50/85 border-amber-200/90 text-amber-900'">
                        <svg class="w-5 h-5 shrink-0 mt-0.5"
                            :class="modalAlert.calloutVariant === 'danger' ? 'text-rose-600' : 'text-amber-600'"
                            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div class="space-y-1">
                            <p class="font-bold" x-text="modalAlert.calloutTitle"></p>
                            <p x-show="modalAlert.calloutBody" class="opacity-90" x-text="modalAlert.calloutBody"></p>
                        </div>
                    </div>
                </div>

                {{-- Footer Modal --}}
                <div class="px-6 py-4 bg-slate-50/70 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" x-show="modalAlert.showCancel" :disabled="modalAlert.loading"
                        @click="closeModalAlert()"
                        class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200/80 transition cursor-pointer disabled:opacity-50"
                        x-text="modalAlert.cancelText">
                    </button>

                    <button type="button" :disabled="modalAlert.loading" @click="executeModalConfirm()"
                        class="inline-flex items-center gap-1.5 px-5 py-2 rounded-xl text-xs font-semibold text-white transition shadow-xs cursor-pointer disabled:opacity-60"
                        :class="modalAlert.variant === 'danger'
                            ? 'bg-rose-600 hover:bg-rose-700'
                            : 'bg-blue-600 hover:bg-blue-700'">
                        <span x-text="modalAlert.loading ? 'Memproses...' : modalAlert.actionText"></span>
                        <template x-if="!modalAlert.loading && modalAlert.showArrow">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </template>
                    </button>
                </div>

            </div>
        </div>

    </div>

    <script>
        function monitoringLive(config) {
            return {
                ujianId: config.ujianId,
                totalSoal: config.totalSoal,
                durasiMenit: config.durasiMenit || 60,
                judulUjian: config.judulUjian || 'Ujian',
                isSelesai: config.isSelesai,
                pesertas: [],
                stats: {
                    total_peserta: 0,
                    aktif: 0,
                    selesai: 0,
                    terkunci: 0,
                    pelanggaran: 0,
                    total_kejadian_pelanggaran: 0
                },
                searchQuery: '',
                filterStatus: '',
                isSyncing: false,
                pollInterval: null,
                tickerInterval: null,

                // Pagination client-side
                currentPage: 1,
                perPage: 10,

                daftarSesi: config.daftarSesi || [],
                showModalSesi: false,
                unlockCode: config.unlockCode || '',
                isRefreshingUnlock: false,

                // Modal Konfirmasi & Error Seragam
                modalAlert: {
                    open: false,
                    loading: false,
                    variant: 'primary',
                    calloutVariant: 'warning',
                    kicker: '',
                    title: '',
                    subtitle: '',
                    infoCard: null,
                    calloutTitle: '',
                    calloutBody: '',
                    showCancel: true,
                    cancelText: 'Batal',
                    actionText: 'Ya, Lanjutkan',
                    showArrow: true,
                    onConfirm: null,
                },

                openModalAlert(cfg) {
                    this.modalAlert = {
                        open: true,
                        loading: false,
                        variant: cfg.variant || 'primary',
                        calloutVariant: cfg.calloutVariant || (cfg.variant === 'danger' ? 'danger' : 'warning'),
                        kicker: cfg.kicker || 'KONFIRMASI AKSI',
                        title: cfg.title || 'Konfirmasi',
                        subtitle: cfg.subtitle || '',
                        infoCard: cfg.infoCard || null,
                        calloutTitle: cfg.calloutTitle || '',
                        calloutBody: cfg.calloutBody || '',
                        showCancel: cfg.showCancel !== undefined ? cfg.showCancel : true,
                        cancelText: cfg.cancelText || 'Batal',
                        actionText: cfg.actionText || 'Ya, Lanjutkan',
                        showArrow: cfg.showArrow !== undefined ? cfg.showArrow : true,
                        onConfirm: cfg.onConfirm || null,
                    };
                },

                openErrorModal(title, message) {
                    this.openModalAlert({
                        variant: 'danger',
                        calloutVariant: 'danger',
                        kicker: 'TERJADI KENDALA',
                        title: title || 'Gagal Memproses Aksi',
                        subtitle: 'Sistem tidak dapat menyelesaikan permintaan saat ini.',
                        calloutTitle: message || 'Terjadi kesalahan jaringan atau server.',
                        showCancel: false,
                        actionText: 'Mengerti',
                        showArrow: false,
                        onConfirm: null,
                    });
                },

                closeModalAlert() {
                    this.modalAlert.open = false;
                    this.modalAlert.loading = false;
                },

                async executeModalConfirm() {
                    const cb = this.modalAlert.onConfirm;
                    if (typeof cb !== 'function') {
                        this.closeModalAlert();
                        return;
                    }
                    this.modalAlert.loading = true;
                    try {
                        await cb();
                    } finally {
                        this.modalAlert.loading = false;
                    }
                },

                init() {
                    this.fetchDataMonitoring();

                    // Jalankan polling & countdown hanya saat ujian masih berlangsung (Live)
                    if (!this.isSelesai) {
                        this.pollInterval = setInterval(() => {
                            this.fetchDataMonitoring();
                        }, 12000);

                        this.tickerInterval = setInterval(() => {
                            this.pesertas.forEach(p => {
                                if (p.status === 'sedang_mengerjakan' && p.sisa_detik > 0) {
                                    p.sisa_detik--;
                                }
                            });
                        }, 1000);
                    }
                },

                get filteredPesertas() {
                    const q = this.searchQuery.toLowerCase();
                    return this.pesertas.filter(p => {
                        const matchSearch = (p.nama || '').toLowerCase().includes(q) ||
                            (p.nisn || '').toLowerCase().includes(q) ||
                            (p.kelas || '').toLowerCase().includes(q);

                        let matchStatus = true;
                        if (this.filterStatus === 'pelanggaran') {
                            matchStatus = p.total_pelanggaran > 0;
                        } else if (this.filterStatus) {
                            matchStatus = p.status === this.filterStatus;
                        }

                        return matchSearch && matchStatus;
                    });
                },

                get totalPages() {
                    return Math.max(1, Math.ceil(this.filteredPesertas.length / this.perPage));
                },

                get safePage() {
                    return Math.min(this.currentPage, this.totalPages);
                },

                get paginatedPesertas() {
                    const start = (this.safePage - 1) * this.perPage;
                    return this.filteredPesertas.slice(start, start + this.perPage);
                },

                get rangeTampil() {
                    const total = this.filteredPesertas.length;
                    if (total === 0) return '0';
                    const start = (this.safePage - 1) * this.perPage + 1;
                    const end = Math.min(total, this.safePage * this.perPage);
                    return `${start}–${end}`;
                },

                async fetchDataMonitoring() {
                    this.isSyncing = true;
                    try {
                        const res = await fetch(`/guru/ujian/${this.ujianId}/monitoring/data`, {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        if (!res.ok) throw new Error('Gagal mengambil data monitoring.');

                        const data = await res.json();
                        this.stats = data.stats;
                        // Pada mode Hasil Monitoring, urutkan siswa dengan pelanggaran terbanyak di bagian atas
                        this.pesertas = this.isSelesai ?
                            data.pesertas.sort((a, b) => (b.total_pelanggaran || 0) - (a.total_pelanggaran || 0)) :
                            data.pesertas;
                    } catch (err) {
                        console.error('Monitoring sync error:', err);
                    } finally {
                        this.isSyncing = false;
                    }
                },

                formatWaktu(detik) {
                    detik = Math.floor(Number(detik) || 0);
                    const h = String(Math.floor(detik / 3600)).padStart(2, '0');
                    const m = String(Math.floor((detik % 3600) / 60)).padStart(2, '0');
                    const s = String(detik % 60).padStart(2, '0');
                    return `${h}:${m}:${s}`;
                },

                eksporDataCsv() {
                    if (!this.isSelesai) {
                        this.openErrorModal(
                            'Ekspor Tidak Tersedia',
                            'Ekspor data hanya dapat dilakukan setelah ujian berakhir pada halaman Hasil Monitoring.'
                        );
                        return;
                    }
                    const rows = this.filteredPesertas;
                    if (rows.length === 0) {
                        this.openErrorModal('Tidak Ada Data untuk Diekspor', 'Belum ada data peserta yang tampil pada tabel saat ini.');
                        return;
                    }

                    const headers = ['No', 'Nama Siswa', 'NISN', 'Kelas', 'Sesi', 'Status', 'Dijawab', 'Total Soal', 'Progres (%)', 'Pelanggaran'];
                    const csvLines = [headers.join(',')];

                    rows.forEach((p, idx) => {
                        const cols = [
                            idx + 1,
                            `"${String(p.nama || '').replace(/"/g, '""')}"`,
                            `"${String(p.nisn || '-').replace(/"/g, '""')}"`,
                            `"${String(p.kelas || '-').replace(/"/g, '""')}"`,
                            p.sesi || 1,
                            `"${String(p.status_label || '-').replace(/"/g, '""')}"`,
                            p.total_dijawab ?? 0,
                            this.totalSoal,
                            p.progres_persen ?? 0,
                            p.total_pelanggaran ?? 0,
                        ];
                        csvLines.push(cols.join(','));
                    });

                    const blob = new Blob(['\uFEFF' + csvLines.join('\n')], { type: 'text/csv;charset=utf-8;' });
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = `Monitoring-${String(this.judulUjian).replace(/[^a-zA-Z0-9]+/g, '-')}.csv`;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    URL.revokeObjectURL(url);
                },

                // =========================================================
                // 1. BUKA KUNCI PESERTA
                // =========================================================
                konfirmasiBukaKunci(peserta) {
                    this.openModalAlert({
                        variant: 'primary',
                        calloutVariant: 'warning',
                        kicker: 'KONFIRMASI BUKA KUNCI',
                        title: `Buka Kunci Sesi ${peserta.nama}?`,
                        subtitle: 'Siswa akan dapat kembali melanjutkan pengerjaan ujian dari perangkatnya.',
                        infoCard: {
                            icon: 'unlock',
                            title: peserta.nama,
                            meta: `Kelas ${peserta.kelas} (Sesi ${peserta.sesi}) • NISN: ${peserta.nisn}`,
                            badge: `${peserta.total_pelanggaran}x Pelanggaran`,
                            badgeClass: 'bg-rose-50 text-rose-600 border-rose-200/80',
                        },
                        calloutTitle: `Total pelanggaran (${peserta.total_pelanggaran}x) tetap tercatat dan tidak di-reset.`,
                        calloutBody: 'Jika siswa kembali melanggar batas layar, sesi akan langsung terkunci otomatis.',
                        actionText: 'Ya, Buka Kunci',
                        onConfirm: () => this.bukaKunci(peserta),
                    });
                },

                async bukaKunci(peserta) {
                    try {
                        const res = await fetch(`/guru/ujian/${this.ujianId}/peserta/${peserta.seleksi_id}/unlock`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        });
                        const data = await res.json();
                        if (res.ok) {
                            this.closeModalAlert();
                            this.fetchDataMonitoring();
                        } else {
                            this.openErrorModal('Gagal Membuka Kunci', data.message || 'Gagal membuka kunci sesi.');
                        }
                    } catch (e) {
                        this.openErrorModal('Gagal Membuka Kunci', 'Terjadi kesalahan jaringan saat membuka kunci sesi.');
                    }
                },

                // =========================================================
                // 2. PAKSA KUMPUL PESERTA
                // =========================================================
                konfirmasiPaksaSelesai(peserta) {
                    this.openModalAlert({
                        variant: 'danger',
                        calloutVariant: 'danger',
                        kicker: 'KONFIRMASI AKSI DARURAT',
                        title: `Paksa Kumpulkan Ujian ${peserta.nama}?`,
                        subtitle: 'Pengerjaan ujian siswa ini akan langsung diakhiri dan dinilai sekarang juga.',
                        infoCard: {
                            icon: 'user',
                            title: peserta.nama,
                            meta: `Progres: ${peserta.total_dijawab}/${this.totalSoal} Soal (${peserta.progres_persen}%) • Kelas ${peserta.kelas}`,
                            badge: peserta.status_label,
                            badgeClass: 'bg-rose-50 text-rose-600 border-rose-200/80',
                        },
                        calloutTitle: 'Pengerjaan siswa akan langsung diakhiri secara paksa.',
                        calloutBody: 'Jawaban yang telah tersimpan otomatis akan dihitung sebagai nilai akhir dan sesi dinyatakan selesai.',
                        actionText: 'Ya, Paksa Kumpul',
                        showArrow: false,
                        onConfirm: () => this.paksaSelesai(peserta),
                    });
                },

                async paksaSelesai(peserta) {
                    try {
                        const res = await fetch(
                            `/guru/ujian/${this.ujianId}/peserta/${peserta.seleksi_id}/force-submit`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                }
                            });
                        if (res.ok) {
                            this.closeModalAlert();
                            this.fetchDataMonitoring();
                        } else {
                            const data = await res.json().catch(() => ({}));
                            this.openErrorModal('Gagal Paksa Kumpul', data.message || 'Gagal menyelesaikan ujian siswa.');
                        }
                    } catch (e) {
                        this.openErrorModal('Gagal Paksa Kumpul', 'Terjadi kesalahan jaringan saat menyelesaikan ujian siswa.');
                    }
                },

                // =========================================================
                // 3. SALIN & RILIS / MULAI TOKEN SESI
                // =========================================================
                // Notifikasi berhasil tetap menggunakan alert asli
                salinToken(token) {
                    navigator.clipboard.writeText(token);
                    alert('Token ' + token + ' berhasil disalin ke clipboard!');
                },

                konfirmasiRilisToken(item, mulaiSekarang = false) {
                    const kelasText = Array.isArray(item.kelas_list) ? item.kelas_list.join(', ') : '-';

                    if (mulaiSekarang) {
                        this.openModalAlert({
                            variant: 'primary',
                            calloutVariant: 'warning',
                            kicker: 'KONFIRMASI AKTIVASI SESI',
                            title: `Mulai Sesi ${item.sesi} Sekarang?`,
                            subtitle: 'Token ujian baru akan otomatis dirilis ke peserta dan timer hitung mundur pengerjaan akan segera aktif.',
                            infoCard: {
                                icon: 'play',
                                title: `Sesi ${item.sesi}: Kelas ${kelasText}`,
                                meta: `Jadwal: ${item.jam_mulai} – ${item.jam_selesai} WIB • ${this.durasiMenit} Menit`,
                                badge: 'Siap Dimulai',
                                badgeClass: 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
                            },
                            calloutTitle: `Token baru akan dirilis dan hitung mundur waktu Sesi ${item.sesi} akan dimulai dari detik ini.`,
                            calloutBody: 'Pastikan seluruh pengawas dan siswa di ruang ujian telah bersiap sebelum tombol konfirmasi ditekan.',
                            actionText: `Ya, Mulai Sesi ${item.sesi}`,
                            onConfirm: () => this.rilisTokenBaru(item.sesi, true),
                        });
                    } else {
                        this.openModalAlert({
                            variant: 'primary',
                            calloutVariant: 'warning',
                            kicker: 'KONFIRMASI ACAK TOKEN',
                            title: `Acak Ulang Token Sesi ${item.sesi}?`,
                            subtitle: 'Token baru akan diterbitkan untuk menggantikan token aktif sesi ini.',
                            infoCard: {
                                icon: 'refresh',
                                title: `Sesi ${item.sesi}: Kelas ${kelasText}`,
                                meta: `Token aktif: ${item.token} • Jadwal: ${item.jam_mulai} – ${item.jam_selesai} WIB`,
                                badge: `Sesi ${item.sesi}`,
                                badgeClass: 'bg-blue-50 text-blue-600 border-blue-200/70',
                            },
                            calloutTitle: `Token lama Sesi ${item.sesi} akan hangus untuk siswa yang belum masuk.`,
                            calloutBody: 'Siswa yang sudah berada di dalam ruang pengerjaan ujian tetap dapat melanjutkan tanpa terganggu.',
                            actionText: 'Ya, Acak Token',
                            onConfirm: () => this.rilisTokenBaru(item.sesi, false),
                        });
                    }
                },

                async rilisTokenBaru(nomorSesi, mulaiSekarang = false) {
                    try {
                        const res = await fetch(`/guru/ujian/${this.ujianId}/reset-token`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                sesi: nomorSesi,
                                mulai_sekarang: mulaiSekarang
                            })
                        });

                        const data = await res.json();
                        if (data.status === 'success') {
                            const target = this.daftarSesi.find(s => s.sesi === nomorSesi);
                            if (target) {
                                target.token = data.token_baru;
                                target.jam_mulai = data.jam_mulai;
                                target.jam_selesai = data.jam_selesai;
                            }
                            this.closeModalAlert();
                            this.fetchDataMonitoring();
                            alert(data.message);
                        } else {
                            this.openErrorModal('Gagal Memperbarui Token', data.message || 'Gagal memperbarui token sesi.');
                        }
                    } catch (e) {
                        this.openErrorModal('Gagal Memperbarui Token', 'Terjadi kesalahan jaringan saat memperbarui token sesi.');
                    }
                },

                // =========================================================
                // 4. SALIN & REFRESH KODE BUKA BLOKIR
                // =========================================================
                // Notifikasi berhasil tetap menggunakan alert asli
                salinUnlockCode() {
                    navigator.clipboard.writeText(this.unlockCode);
                    alert('Kode buka blokir ' + this.unlockCode + ' berhasil disalin!');
                },

                konfirmasiRefreshUnlockCode() {
                    this.openModalAlert({
                        variant: 'danger',
                        calloutVariant: 'warning',
                        kicker: 'KONFIRMASI KODE DARURAT',
                        title: 'Acak Ulang Kode Buka Blokir?',
                        subtitle: 'Sistem akan menerbitkan kode darurat baru untuk seluruh peserta ujian ini.',
                        infoCard: {
                            icon: 'unlock',
                            title: `Kode Aktif: ${this.unlockCode}`,
                            meta: 'Berlaku untuk seluruh kelas dalam grup ujian ini',
                            badge: 'Kode Darurat',
                            badgeClass: 'bg-rose-50 text-rose-600 border-rose-200/80',
                        },
                        calloutTitle: 'Kode lama akan langsung hangus dan diganti dengan kode baru.',
                        calloutBody: 'Pastikan siswa yang baru diberi kode lama sudah berhasil membuka blokir sebelum memperbarui kode.',
                        actionText: 'Ya, Refresh Kode',
                        showArrow: false,
                        onConfirm: () => this.refreshUnlockCode(),
                    });
                },

                async refreshUnlockCode() {
                    this.isRefreshingUnlock = true;
                    try {
                        const res = await fetch(`/guru/ujian/${this.ujianId}/refresh-unlock-code`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        });
                        const data = await res.json();
                        if (data.status === 'success') {
                            this.unlockCode = data.unlock_code;
                            this.closeModalAlert();
                        } else {
                            this.openErrorModal('Gagal Memperbarui Kode', data.message || 'Gagal memperbarui kode buka blokir.');
                        }
                    } catch (e) {
                        this.openErrorModal('Gagal Memperbarui Kode', 'Terjadi kesalahan jaringan saat memperbarui kode buka blokir.');
                    } finally {
                        this.isRefreshingUnlock = false;
                    }
                },

                // =========================================================
                // 5. BUKA KEMBALI UJIAN PESERTA (REOPEN)
                // =========================================================
                konfirmasiBukaKembali(peserta) {
                    this.openModalAlert({
                        variant: 'primary',
                        calloutVariant: 'warning',
                        kicker: 'KONFIRMASI BUKA KEMBALI UJIAN',
                        title: `Buka Kembali Ujian ${peserta.nama}?`,
                        subtitle: 'Izinkan siswa melanjutkan pengerjaan selama sisa waktu sesi masih tersedia.',
                        infoCard: {
                            icon: 'refresh',
                            title: peserta.nama,
                            meta: `Kelas ${peserta.kelas} (Sesi ${peserta.sesi}) • Progres: ${peserta.total_dijawab}/${this.totalSoal} Soal`,
                            badge: 'Selesai',
                            badgeClass: 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
                        },
                        calloutTitle: 'Status selesai akan dibatalkan dengan jawaban sebelumnya tetap tersimpan.',
                        calloutBody: `Riwayat pelanggaran (${peserta.total_pelanggaran}x) juga tetap dipertahankan dan tidak di-reset.`,
                        actionText: 'Ya, Buka Kembali',
                        onConfirm: () => this.bukaKembaliUjian(peserta),
                    });
                },

                async bukaKembaliUjian(peserta) {
                    try {
                        const res = await fetch(`/guru/ujian/${this.ujianId}/peserta/${peserta.seleksi_id}/reopen`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        });
                        const data = await res.json();
                        if (res.ok && data.status === 'success') {
                            this.closeModalAlert();
                            this.fetchDataMonitoring();
                            alert(data.message);
                        } else {
                            this.openErrorModal('Gagal Membuka Kembali Ujian', data.message || 'Gagal membuka kembali ujian siswa.');
                        }
                    } catch (e) {
                        this.openErrorModal('Gagal Membuka Kembali Ujian', 'Terjadi kesalahan jaringan saat membuka kembali ujian.');
                    }
                }
            };
        }
    </script>
@endsection
