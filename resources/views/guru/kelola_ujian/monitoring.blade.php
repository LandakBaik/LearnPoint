@extends('layouts.app')

@section('title', 'Monitoring Live: ' . $ujian->judul)

@section('content')
<div class="space-y-6" x-data="monitoringLive({{ $ujian->id }}, {{ (int) $totalSoal }})">

    {{-- =========================================================
        HEADER & CONTROLS
    ========================================================== --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <a href="{{ route('guru.ujian.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-500 hover:text-blue-600 transition">
                ← Kembali ke Daftar Ujian
            </a>
            <div class="flex items-center gap-3 mt-1.5">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                    MONITORING LIVE
                </span>
                <h1 class="text-xl sm:text-2xl font-black text-gray-900 truncate">
                    {{ $ujian->judul }}
                </h1>
            </div>
            <p class="text-xs text-gray-500 mt-1">
                {{ $ujian->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? 'Mata Pelajaran' }} &bull;
                Kelas: {{ $ujian->pengampuKelas?->kelas?->nama_kelas ?? '-' }} &bull;
                Total Soal: <span class="font-bold text-gray-800">{{ $totalSoal }} Butir</span>
            </p>
        </div>

        {{-- SINKRONISASI INDIKATOR --}}
        <div class="flex items-center gap-2 self-start md:self-auto">
            <span class="inline-flex items-center gap-1.5 text-xs text-gray-500 bg-white border border-gray-200 px-3 py-2 rounded-xl shadow-2xs">
                <span class="w-2 h-2 rounded-full" :class="isSyncing ? 'bg-amber-400 animate-spin' : 'bg-emerald-500'"></span>
                <span x-text="isSyncing ? 'Menyinkronkan data...' : 'Auto-sync tiap 5 dtk'"></span>
            </span>

            <button type="button" @click="fetchDataMonitoring()"
                class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">
                Refresh
            </button>
        </div>
    </div>

    {{-- =========================================================
        PANEL TOKEN UJIAN PER SESI & PENGATURAN SESI KELAS
    ========================================================== --}}
    <div class="bg-white border border-gray-200 rounded-3xl p-5 sm:p-6 shadow-xs space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-gray-100">
            <div>
                <h2 class="text-sm font-black text-gray-900 uppercase tracking-wider flex items-center gap-2">
                    <span>🔑</span> Daftar Token Sesi Ujian
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Kelas dalam sesi yang sama menggunakan token yang sama. Timer siswa otomatis menyesuaikan jam aktif sesi.
                </p>
            </div>
            <button type="button" @click="showModalSesi = true"
                class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition cursor-pointer self-start sm:self-auto">
                ⚙️ Atur Pembagian Sesi Kelas
            </button>
        </div>

        {{-- Banner Kode Buka Blokir (Unlock Code) --}}
        <div class="rounded-2xl bg-rose-50/80 border border-rose-200 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center text-lg shrink-0">
                    🔓
                </div>
                <div>
                    <p class="text-[11px] font-extrabold uppercase tracking-wider text-rose-800">
                        Kode Buka Blokir Darurat (Berlaku Untuk Semua Peserta Ujian Ini)
                    </p>
                    <p class="text-[11px] text-rose-600 mt-0.5">
                        Berikan kode ini kepada siswa yang terkunci, lalu klik <strong>Refresh Kode</strong> setelah digunakan agar tidak disalahgunakan.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 self-start sm:self-auto">
                <span class="font-mono text-lg font-black tracking-widest text-rose-700 bg-white border border-rose-300 px-3.5 py-1.5 rounded-xl select-all shadow-2xs"
                    x-text="unlockCode"></span>

                <button type="button" @click="salinUnlockCode()"
                    class="px-3 py-2 bg-white hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-xl text-xs font-bold transition cursor-pointer">
                    📋 Salin
                </button>

                <button type="button" @click="refreshUnlockCode()" :disabled="isRefreshingUnlock"
                    class="px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition shadow-2xs cursor-pointer flex items-center gap-1.5 disabled:opacity-50">
                    <svg class="w-3.5 h-3.5" :class="isRefreshingUnlock ? 'animate-spin' : ''" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <span>Refresh Kode</span>
                </button>
            </div>
        </div>

        {{-- Daftar Kartu Sesi --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <template x-for="item in daftarSesi" :key="item.sesi">
                <div class="rounded-2xl border border-indigo-100 bg-gradient-to-br from-indigo-50/60 via-white to-blue-50/40 p-4 flex flex-col justify-between gap-4 shadow-2xs">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[11px] font-extrabold bg-indigo-600 text-white">
                                SESI <span class="ml-1" x-text="item.sesi"></span>
                            </span>
                            <p class="text-xs font-bold text-gray-800 mt-2">
                                Kelas: <span class="text-indigo-600" x-text="item.kelas_list.join(', ')"></span>
                            </p>
                            <p class="text-[11px] text-gray-500 mt-0.5">
                                Jadwal Sesi: <strong class="text-gray-700" x-text="item.jam_mulai + ' – ' + item.jam_selesai + ' WIB'"></strong>
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block">TOKEN AKTIF</span>
                            <span class="mt-0.5 inline-block font-mono text-xl font-black tracking-widest text-indigo-700 bg-indigo-100/80 border border-indigo-200 px-3 py-1 rounded-xl select-all"
                                x-text="item.token"></span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-2 pt-3 border-t border-indigo-100/80">
                        <button type="button" @click="salinToken(item.token)"
                            class="flex-1 py-1.5 px-2.5 bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 rounded-xl text-[11px] font-bold transition cursor-pointer">
                            📋 Salin
                        </button>
                        <button type="button" @click="rilisTokenBaru(item.sesi, false)"
                            class="flex-1 py-1.5 px-2.5 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 rounded-xl text-[11px] font-bold transition cursor-pointer"
                            title="Ganti kode token jika bocor tanpa mengubah jam sesi">
                            🔄 Acak Token
                        </button>
                        <button type="button" @click="rilisTokenBaru(item.sesi, true)"
                            class="flex-1 py-1.5 px-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-[11px] font-bold transition cursor-pointer"
                            title="Ganti token dan mulai hitung mundur sesi ini dari sekarang">
                            ▶️ Mulai Sesi
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- MODAL PENGATURAN PEMBAGIAN SESI KELAS (7-A & 7-B = Sesi 1, 7-C = Sesi 2) --}}
    <div x-show="showModalSesi" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-gray-200 space-y-5">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <h3 class="text-base font-black text-gray-900">Atur Pembagian Sesi & Jam Kelas</h3>
                <button type="button" @click="showModalSesi = false" class="text-gray-400 hover:text-gray-600 font-bold text-lg cursor-pointer">&times;</button>
            </div>

            <form action="{{ route('guru.ujian.atur-sesi', $ujian->id) }}" method="POST" class="space-y-4">
                @csrf
                <div class="space-y-2.5 max-h-60 overflow-y-auto pr-1">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">1. Pilih Sesi untuk Masing-Masing Kelas:</p>
                    @foreach($grupUjians as $itemUjian)
                        <div class="flex items-center justify-between p-3 rounded-2xl bg-gray-50 border border-gray-200">
                            <div>
                                <p class="text-xs font-bold text-gray-900">
                                    Kelas {{ $itemUjian->pengampuKelas?->kelas?->nama_kelas ?? '-' }}
                                </p>
                                <p class="text-[11px] text-gray-400">Token saat ini: {{ $itemUjian->token ?? '-' }}</p>
                            </div>
                            <select name="sesi_ujian[{{ $itemUjian->id }}]"
                                class="px-3 py-1.5 rounded-xl border border-gray-300 text-xs font-bold text-indigo-700 bg-white outline-none cursor-pointer">
                                @for($s = 1; $s <= 5; $s++)
                                    <option value="{{ $s }}" {{ (int)($itemUjian->sesi ?? 1) === $s ? 'selected' : '' }}>
                                        Sesi {{ $s }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    @endforeach
                </div>

                <div class="space-y-2 pt-3 border-t border-gray-100">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">2. Jam Mulai per Sesi (Opsional):</p>
                    <div class="grid grid-cols-2 gap-2.5">
                        @for($s = 1; $s <= 4; $s++)
                            @php
                                $sampleSesi = $grupUjians->where('sesi', $s)->first();
                                $defaultJam = $sampleSesi && $sampleSesi->waktu_mulai_sesi
                                    ? \Carbon\Carbon::parse($sampleSesi->waktu_mulai_sesi)->format('H:i')
                                    : '';
                            @endphp
                            <div class="p-2.5 rounded-xl border border-gray-200 flex items-center justify-between gap-2">
                                <span class="text-xs font-bold text-gray-700">Sesi {{ $s }}:</span>
                                <input type="time" name="jam_sesi[{{ $s }}]" value="{{ $defaultJam }}"
                                    class="text-xs font-mono font-bold text-gray-800 border border-gray-200 rounded-lg px-2 py-1">
                            </div>
                        @endfor
                    </div>
                    <p class="text-[11px] text-gray-400">
                        *Jika jam dikosongkan, jadwal sesi dihitung berurutan otomatis sesuai durasi ujian ({{ $ujian->durasi_menit }} menit/sesi).
                    </p>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" @click="showModalSesi = false"
                        class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-xs font-bold text-gray-600 cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-xs font-bold text-white shadow-xs cursor-pointer">
                        Simpan Pengaturan Sesi
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- =========================================================
        KARTU STATISTIK PENGERJAAN
    ========================================================== --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3.5">
        <div class="bg-white border border-gray-200 rounded-2xl p-4 shadow-2xs">
            <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Total Peserta</p>
            <p class="mt-1 text-2xl font-black text-gray-900" x-text="stats.total_peserta"></p>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl p-4 shadow-2xs">
            <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Sedang Aktif</p>
            <p class="mt-1 text-2xl font-black text-emerald-600" x-text="stats.aktif"></p>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl p-4 shadow-2xs">
            <p class="text-[11px] font-bold uppercase tracking-wider text-blue-600">Telah Mengumpulkan</p>
            <p class="mt-1 text-2xl font-black text-blue-600" x-text="stats.selesai"></p>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl p-4 shadow-2xs">
            <p class="text-[11px] font-bold uppercase tracking-wider text-amber-600">Sesi Terkunci</p>
            <p class="mt-1 text-2xl font-black text-amber-600" x-text="stats.terkunci"></p>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl p-4 shadow-2xs col-span-2 sm:col-span-1">
            <p class="text-[11px] font-bold uppercase tracking-wider text-rose-600">Pelanggaran Layar</p>
            <p class="mt-1 text-2xl font-black text-rose-600" x-text="stats.pelanggaran"></p>
        </div>
    </div>

    {{-- =========================================================
        FILTER & PENCARIAN SISWA
    ========================================================== --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-4 shadow-2xs flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <div class="relative flex-1">
            <input type="text" x-model="searchQuery" placeholder="Cari nama siswa atau NISN..."
                class="w-full pl-9 pr-4 py-2 rounded-xl border border-gray-200 text-xs text-gray-800 placeholder-gray-400 focus:border-blue-500 outline-none">
            <svg class="absolute left-3 top-2.5 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m2.35-5.65a8 8 0 11-16 0 8 8 0 0116 0z"/>
            </svg>
        </div>

        <div class="flex items-center gap-2">
            <select x-model="filterStatus"
                class="px-3 py-2 rounded-xl border border-gray-200 text-xs font-semibold text-gray-700 outline-none cursor-pointer">
                <option value="">Semua Status</option>
                <option value="sedang_mengerjakan">Sedang Mengerjakan</option>
                <option value="selesai">Selesai</option>
                <option value="terkunci">Terkunci</option>
                <option value="pelanggaran">Ada Pelanggaran</option>
            </select>
        </div>
    </div>

    {{-- =========================================================
        TABEL MONITORING PESERTA LIVE
    ========================================================== --}}
    <div class="bg-white border border-gray-200 rounded-2xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/70 text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                        <th class="py-3.5 px-4 w-12 text-center">#</th>
                        <th class="py-3.5 px-4">Nama Siswa</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 w-52">Progres Soal</th>
                        <th class="py-3.5 px-4">Sisa Waktu</th>
                        <th class="py-3.5 px-4 text-center">Log Curang</th>
                        <th class="py-3.5 px-4 text-right">Aksi Darurat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs">
                    <template x-for="(peserta, index) in filteredPesertas" :key="peserta.seleksi_id">
                        <tr class="hover:bg-slate-50/70 transition">
                            {{-- Nomor --}}
                            <td class="py-3.5 px-4 text-center font-bold text-gray-400" x-text="index + 1"></td>

                            {{-- Info Siswa --}}
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-gray-900" x-text="peserta.nama"></div>
                                <div class="text-[11px] text-gray-400 font-mono" x-text="'NISN: ' + peserta.nisn"></div>
                            </td>

                            {{-- Status Badge --}}
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border"
                                    :class="{
                                        'bg-emerald-50 text-emerald-700 border-emerald-200': peserta.status === 'sedang_mengerjakan' && peserta.sisa_detik > 0,
                                        'bg-blue-50 text-blue-700 border-blue-200': peserta.status === 'selesai',
                                        'bg-rose-50 text-rose-700 border-rose-200 animate-pulse': peserta.status === 'terkunci',
                                        'bg-gray-100 text-gray-600 border-gray-200': peserta.status === 'belum_mulai' || (peserta.status === 'sedang_mengerjakan' && peserta.sisa_detik <= 0)
                                    }">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                    <span x-text="peserta.status_label"></span>
                                </span>
                            </td>

                            {{-- Progres Pengerjaan Soal --}}
                            <td class="py-3.5 px-4">
                                <div class="flex items-center justify-between text-[11px] font-bold mb-1">
                                    <span class="text-gray-700" x-text="peserta.total_dijawab + ' / ' + totalSoal + ' Soal'"></span>
                                    <span class="text-blue-600" x-text="peserta.progres_persen + '%'"></span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                                    <div class="h-2 rounded-full transition-all duration-300"
                                        :class="peserta.progres_persen === 100 ? 'bg-emerald-500' : 'bg-blue-600'"
                                        :style="`width: ${peserta.progres_persen}%`"></div>
                                </div>
                            </td>

                            {{-- Countdown Sisa Waktu --}}
                            <td class="py-3.5 px-4">
                                <template x-if="peserta.status === 'sedang_mengerjakan' && peserta.sisa_detik > 0">
                                    <span class="font-mono font-bold text-xs"
                                        :class="peserta.sisa_detik < 300 ? 'text-rose-600 animate-pulse' : 'text-gray-800'"
                                        x-text="formatWaktu(peserta.sisa_detik)"></span>
                                </template>
                                <template x-if="peserta.status === 'selesai'">
                                    <span class="text-gray-400 font-semibold italic text-[11px]">Tuntas</span>
                                </template>
                                <template x-if="peserta.status !== 'selesai' && peserta.sisa_detik <= 0">
                                    <span class="text-rose-500 font-semibold italic text-[11px]">Habis</span>
                                </template>
                            </td>

                            {{-- Peringatan Pelanggaran Layar (Anti-Cheat) --}}
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-lg text-xs font-black"
                                    :class="peserta.total_pelanggaran > 0 ? 'bg-rose-100 text-rose-700 border border-rose-200' : 'bg-gray-100 text-gray-400'"
                                    x-text="peserta.total_pelanggaran + 'x'">
                                </span>
                            </td>

                            {{-- Aksi Darurat Guru --}}
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    {{-- Buka Kunci Sesi Terblokir --}}
                                    <button type="button" x-show="peserta.status === 'terkunci'"
                                        @click="bukaKunci(peserta)"
                                        class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 rounded-lg text-[11px] font-bold transition cursor-pointer">
                                        Buka Kunci
                                    </button>

                                    {{-- Paksa Kumpul --}}
                                    <button type="button" x-show="peserta.status === 'sedang_mengerjakan' || peserta.status === 'terkunci'"
                                        @click="paksaSelesai(peserta)"
                                        class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg text-[11px] font-bold transition cursor-pointer">
                                        Paksa Kumpul
                                    </button>

                                    {{-- Buka Kembali Ujian yang Sudah Selesai (Backup Plan) --}}
                                    <button type="button" x-show="peserta.status === 'selesai'"
                                        @click="bukaKembaliUjian(peserta)"
                                        class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-lg text-[11px] font-bold transition cursor-pointer"
                                        title="Izinkan siswa melanjutkan ujian dengan jawaban sebelumnya">
                                        ↺ Buka Kembali
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>

                    {{-- Empty State Jika Tidak Ada Siswa --}}
                    <tr x-show="filteredPesertas.length === 0">
                        <td colspan="7" class="py-12 text-center text-gray-400">
                            <p class="font-semibold text-xs text-gray-600">Tidak ada peserta yang cocok dengan filter.</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- =========================================================
    LOGIKA JAVASCRIPT & REALTIME POLLING ALPINE.JS
========================================================== --}}
<script>
function monitoringLive(ujianId, totalSoal) {
    return {
        ujianId: ujianId,
        totalSoal: totalSoal,
        pesertas: [],
        stats: { total_peserta: 0, aktif: 0, selesai: 0, terkunci: 0, pelanggaran: 0 },
        searchQuery: '',
        filterStatus: '',
        isSyncing: false,
        pollInterval: null,
        tickerInterval: null,

        // === [DITAMBAHKAN] State untuk Manajemen Sesi & Token ===
        daftarSesi: {{ Js::from($daftarSesi ?? []) }},
        showModalSesi: false,

        // === [BARU] State untuk Unlock Code ===
        unlockCode: '{{ $unlockCode ?? "" }}',
        isRefreshingUnlock: false,

        init() {
            this.fetchDataMonitoring();

            // 1. Polling AJAX ke server setiap 5 detik
            this.pollInterval = setInterval(() => {
                this.fetchDataMonitoring();
            }, 5000);

            // 2. Countdown ticker lokal setiap 1 detik
            this.tickerInterval = setInterval(() => {
                this.pesertas.forEach(p => {
                    if (p.status === 'sedang_mengerjakan' && p.sisa_detik > 0) {
                        p.sisa_detik--;
                    }
                });
            }, 1000);
        },

        get filteredPesertas() {
            return this.pesertas.filter(p => {
                const matchSearch = p.nama.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                                    p.nisn.toLowerCase().includes(this.searchQuery.toLowerCase());

                let matchStatus = true;
                if (this.filterStatus === 'pelanggaran') {
                    matchStatus = p.total_pelanggaran > 0;
                } else if (this.filterStatus) {
                    matchStatus = p.status === this.filterStatus;
                }

                return matchSearch && matchStatus;
            });
        },

        async fetchDataMonitoring() {
            this.isSyncing = true;
            try {
                const res = await fetch(`/guru/ujian/${this.ujianId}/monitoring/data`, {
                    headers: { 'Accept': 'application/json' }
                });
                if (!res.ok) throw new Error('Gagal mengambil data monitoring.');

                const data = await res.json();
                this.stats = data.stats;
                this.pesertas = data.pesertas;
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

        async bukaKunci(peserta) {
            if (!confirm(`Buka kunci sesi ujian untuk ${peserta.nama}? Siswa akan dapat melanjutkan pengerjaan.`)) return;

            try {
                const res = await fetch(`/guru/ujian/${this.ujianId}/peserta/${peserta.seleksi_id}/unlock`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                if (res.ok) this.fetchDataMonitoring();
            } catch (e) {
                alert('Gagal membuka kunci sesi.');
            }
        },

        async paksaSelesai(peserta) {
            if (!confirm(`PENTING: Paksa kumpulkan ujian untuk ${peserta.nama}? Pengerjaan siswa akan langsung diakhiri.`)) return;

            try {
                const res = await fetch(`/guru/ujian/${this.ujianId}/peserta/${peserta.seleksi_id}/force-submit`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                if (res.ok) this.fetchDataMonitoring();
            } catch (e) {
                alert('Gagal menyelesaikan ujian siswa.');
            }
        },

        // === [DITAMBAHKAN] Method Salin Token & Rilis Token Baru ===
        salinToken(token) {
            navigator.clipboard.writeText(token);
            alert('Token ' + token + ' berhasil disalin ke clipboard!');
        },

        async rilisTokenBaru(nomorSesi, mulaiSekarang = false) {
            const pesan = mulaiSekarang
                ? `Mulai Sesi ${nomorSesi} sekarang? Token baru akan dirilis dan hitung mundur waktu Sesi ${nomorSesi} (${this.totalSoal} soal) akan dimulai dari detik ini.`
                : `Acak ulang token untuk Sesi ${nomorSesi}? Token lama Sesi ${nomorSesi} akan hangus untuk siswa yang belum masuk.`;

            if (!confirm(pesan)) return;

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
                    this.fetchDataMonitoring();
                    alert(data.message);
                }
            } catch (e) {
                alert('Gagal memperbarui token sesi.');
            }
        },

        // === [BARU] Method Unlock Code & Buka Kembali Ujian ===
        salinUnlockCode() {
            navigator.clipboard.writeText(this.unlockCode);
            alert('Kode buka blokir ' + this.unlockCode + ' berhasil disalin!');
        },

        async refreshUnlockCode() {
            if (!confirm('Acak ulang Kode Buka Blokir?\n\nKode lama akan langsung hangus dan diganti kode baru.')) return;

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
                }
            } catch (e) {
                alert('Gagal memperbarui kode buka blokir.');
            } finally {
                this.isRefreshingUnlock = false;
            }
        },

        async bukaKembaliUjian(peserta) {
            if (!confirm(`Buka kembali ujian untuk ${peserta.nama}?\n\nStatus selesai akan dibatalkan dan siswa dapat masuk kembali untuk melanjutkan pengerjaan dengan seluruh jawaban sebelumnya tetap tersimpan.`)) return;

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
                    this.fetchDataMonitoring();
                    alert(data.message);
                } else {
                    alert(data.message || 'Gagal membuka kembali ujian siswa.');
                }
            } catch (e) {
                alert('Terjadi kesalahan jaringan saat membuka kembali ujian.');
            }
        }
    };
}
</script>
@endsection
