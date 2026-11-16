@extends('layouts.app')

@section('title', 'Rekap Nilai & Koreksi: ' . $ujian->judul)

@section('content')
<div class="space-y-6" x-data="rekapNilaiApp({
    pesertas: {{ Js::from($rekapSiswa) }},
    kkm: {{ $kkm }}
})">

    {{-- HEADER --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <a href="{{ route('guru.ujian.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-500 hover:text-blue-600 transition">
                &larr; Kembali ke Daftar Ujian
            </a>
            <div class="flex items-center gap-3 mt-1.5 flex-wrap">
                <h1 class="text-xl sm:text-2xl font-black text-gray-900">Rekap Nilai: {{ $ujian->judul }}</h1>
                <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    KKM: {{ number_format($kkm, 0) }}
                </span>
                @if($ujian->jenis_ujian !== 'reguler')
                    <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-amber-50 text-amber-700 border border-amber-200 uppercase">
                        {{ $ujian->jenis_ujian }}
                    </span>
                @endif
            </div>
            <p class="text-xs text-gray-500 mt-1">
                {{ $ujian->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? 'Mata Pelajaran' }} &bull;
                Kelas: {{ $ujian->pengampuKelas?->kelas?->nama_kelas ?? '-' }}
            </p>
        </div>

        {{-- TOMBOL AKSI UTAMA --}}
        <div class="flex items-center gap-2.5 flex-wrap">
            @if($ringkasan['ada_soal_essay'])
                <a href="{{ route('guru.ujian.koreksi-essay', $ujian->id) }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-extrabold transition shadow-xs">
                    <span>✍️ Koreksi Essay Massal</span>
                    @if($ringkasan['menunggu_essay'] > 0)
                        <span class="px-2 py-0.5 rounded-full bg-white text-amber-700 text-[10px] font-black">
                            {{ $ringkasan['menunggu_essay'] }}
                        </span>
                    @endif
                </a>
            @endif

            <button type="button" @click="bukaModalRemidiSusulan('remidi')"
                class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-extrabold transition shadow-xs cursor-pointer">
                <span>🔄 Buat Remidi</span>
                <span class="px-1.5 py-0.5 rounded-md bg-rose-800/60 text-[10px]">{{ $ringkasan['remidi'] }}</span>
            </button>

            <button type="button" @click="bukaModalRemidiSusulan('susulan')"
                class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-slate-700 hover:bg-slate-800 text-white rounded-xl text-xs font-extrabold transition shadow-xs cursor-pointer">
                <span>📋 Buat Susulan</span>
                <span class="px-1.5 py-0.5 rounded-md bg-slate-900/60 text-[10px]">{{ $ringkasan['belum_ujian'] }}</span>
            </button>

            <a href="{{ route('guru.ujian.laporan.pdf', ['mode' => 'detail', 'pengampu_kelas_id' => $ujian->pengampu_kelas_id, 'ujian_id' => $ujian->id]) }}"
                class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-extrabold transition shadow-xs">
                <span>🖨️ Cetak PDF Berita Acara</span>
            </a>
        </div>
    </div>

    {{-- FILTER CEPAT & PENCARIAN --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-4 shadow-2xs flex flex-col lg:flex-row lg:items-center justify-between gap-3">
        <div class="flex items-center gap-1.5 flex-wrap">
            <button type="button" @click="filterStatus = 'semua'"
                :class="filterStatus === 'semua' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                class="px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer">
                Semua ({{ $ringkasan['total'] }})
            </button>

            @if($ringkasan['ada_soal_essay'])
                <button type="button" @click="filterStatus = 'menunggu_essay'"
                    :class="filterStatus === 'menunggu_essay' ? 'bg-amber-500 text-white' : 'bg-amber-50 text-amber-800 border border-amber-200 hover:bg-amber-100'"
                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer">
                    Perlu Koreksi Essay ({{ $ringkasan['menunggu_essay'] }})
                </button>
            @endif

            <button type="button" @click="filterStatus = 'remidi'"
                :class="filterStatus === 'remidi' ? 'bg-rose-600 text-white' : 'bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100'"
                class="px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer">
                Di Bawah KKM / Remidi ({{ $ringkasan['remidi'] }})
            </button>

            <button type="button" @click="filterStatus = 'tuntas'"
                :class="filterStatus === 'tuntas' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100'"
                class="px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer">
                Tuntas ({{ $ringkasan['tuntas'] }})
            </button>

            <button type="button" @click="filterStatus = 'belum_ujian'"
                :class="filterStatus === 'belum_ujian' ? 'bg-slate-600 text-white' : 'bg-slate-100 text-slate-600 border border-slate-200 hover:bg-slate-200'"
                class="px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer">
                Belum Ujian / Susulan ({{ $ringkasan['belum_ujian'] }})
            </button>
        </div>

        <div class="relative w-full lg:w-64">
            <input type="text" x-model="searchQuery" placeholder="Cari nama siswa / NISN..."
                class="w-full pl-8 pr-3 py-1.5 rounded-xl border border-gray-200 text-xs focus:border-blue-500 outline-none">
            <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m2.35-5.65a8 8 0 11-16 0 8 8 0 0116 0z"/>
            </svg>
        </div>
    </div>

    {{-- TABEL REKAP NILAI --}}
    <div class="bg-white border border-gray-200 rounded-2xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/80 text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                        <th class="py-3.5 px-4 w-10 text-center">#</th>
                        <th class="py-3.5 px-4">Nama Siswa & Kelas</th>
                        <th class="py-3.5 px-4">Statistik PG</th>
                        <th class="py-3.5 px-4">Nilai PG</th>
                        <th class="py-3.5 px-4">Nilai Essay</th>
                        <th class="py-3.5 px-4">Nilai Akhir</th>
                        <th class="py-3.5 px-4">Status Kelulusan</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs">
                    <template x-for="(row, idx) in filteredPesertas" :key="row.siswa_id">
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-4 text-center font-bold text-gray-400" x-text="idx + 1"></td>

                            <td class="py-3.5 px-4">
                                <div class="font-bold text-gray-900" x-text="row.nama"></div>
                                <div class="text-[11px] text-gray-400">
                                    Kelas <span class="font-semibold text-gray-600" x-text="row.kelas"></span> &bull; NISN: <span x-text="row.nisn"></span>
                                </div>
                            </td>

                            <td class="py-3.5 px-4">
                                <template x-if="row.sudah_selesai && row.total_pg > 0">
                                    <div class="flex items-center gap-2 font-bold text-[11px]">
                                        <span class="text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200" x-text="row.pg_benar + ' Benar'"></span>
                                        <span class="text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md border border-rose-200" x-text="row.pg_salah + ' Salah'"></span>
                                    </div>
                                </template>
                                <template x-if="!row.sudah_selesai || row.total_pg === 0">
                                    <span class="text-gray-400">-</span>
                                </template>
                            </td>

                            <td class="py-3.5 px-4 font-bold text-gray-800">
                                <template x-if="row.sudah_selesai && row.total_pg > 0">
                                    <span x-text="row.nilai_pg + ' / ' + row.bobot_pg"></span>
                                </template>
                                <template x-if="!row.sudah_selesai || row.total_pg === 0">
                                    <span class="text-gray-400">-</span>
                                </template>
                            </td>

                            <td class="py-3.5 px-4">
                                <template x-if="!row.sudah_selesai || row.total_essay === 0">
                                    <span class="text-gray-400">-</span>
                                </template>
                                <template x-if="row.sudah_selesai && row.total_essay > 0 && row.menunggu_koreksi_essay">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        Belum Dikoreksi (<span x-text="row.essay_dinilai + '/' + row.total_essay"></span>)
                                    </span>
                                </template>
                                <template x-if="row.sudah_selesai && row.total_essay > 0 && !row.menunggu_koreksi_essay">
                                    <span class="font-bold text-gray-800" x-text="row.nilai_essay + ' / ' + row.bobot_essay"></span>
                                </template>
                            </td>

                            <td class="py-3.5 px-4">
                                <template x-if="!row.sudah_selesai || row.menunggu_koreksi_essay">
                                    <span class="font-black text-gray-400 text-sm">-</span>
                                </template>
                                <template x-if="row.sudah_selesai && !row.menunggu_koreksi_essay">
                                    <div>
                                        <span class="text-base font-black"
                                            :class="row.nilai_akhir >= kkm ? 'text-emerald-600' : 'text-rose-600'"
                                            x-text="row.nilai_akhir"></span>
                                        <template x-if="row.jumlah_remidi > 0">
                                            <span class="block text-[10px] text-gray-400" x-text="'(Awal: ' + row.nilai_awal + ')'"></span>
                                        </template>
                                    </div>
                                </template>
                            </td>

                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border"
                                    :class="{
                                        'bg-emerald-50 text-emerald-700 border-emerald-200': row.status_kategori === 'tuntas',
                                        'bg-rose-50 text-rose-700 border-rose-200': row.status_kategori === 'remidi',
                                        'bg-amber-50 text-amber-800 border-amber-200': row.status_kategori === 'menunggu_essay',
                                        'bg-gray-100 text-gray-600 border-gray-200': row.status_kategori === 'belum_ujian'
                                    }"
                                    x-text="row.status_label">
                                </span>
                            </td>

                            <td class="py-3.5 px-4 text-right">
                                <template x-if="row.sudah_selesai">
                                    <a :href="`/guru/ujian/${row.ujian_id}/peserta/${row.siswa_id}/jawaban`"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-xl text-[11px] font-bold transition">
                                        👁️ Lihat & Nilai Jawaban
                                    </a>
                                </template>
                                <template x-if="!row.sudah_selesai">
                                    <span class="text-[11px] text-gray-400 italic">Belum ada jawaban</span>
                                </template>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL BUAT REMIDI / SUSULAN (OTOMATIS CENTANG SISWA MERAH / BELUM UJIAN) --}}
    <div x-show="showModalRemidi" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4">
        <div class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl border border-gray-200 space-y-5 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div>
                    <h3 class="text-lg font-black text-gray-900"
                        x-text="jenisModal === 'remidi' ? '🔄 Buat Ujian Remidi' : '📋 Buat Ujian Susulan'"></h3>
                    <p class="text-xs text-gray-500 mt-0.5"
                        x-text="jenisModal === 'remidi' ? 'Siswa dengan nilai di bawah KKM otomatis dicentang.' : 'Siswa yang belum mengerjakan ujian utama otomatis dicentang.'"></p>
                </div>
                <button type="button" @click="showModalRemidi = false" class="text-gray-400 hover:text-gray-600 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <form action="{{ route('guru.ujian.buat-remidi-susulan', $ujian->id) }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="jenis" :value="jenisModal">

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Judul Ujian <span x-text="jenisModal === 'remidi' ? 'Remidi' : 'Susulan'"></span></label>
                    <input type="text" name="judul" required
                        :value="(jenisModal === 'remidi' ? 'Remidi - ' : 'Susulan - ') + '{{ addslashes($ujian->judul) }}'"
                        class="w-full rounded-xl border border-gray-300 px-3.5 py-2 text-xs font-semibold focus:border-indigo-500 outline-none">
                </div>

                {{-- 3 Kebijakan Nilai Remidi (Muncul Khusus Remidi) --}}
                <div x-show="jenisModal === 'remidi'" class="p-3.5 rounded-2xl bg-indigo-50/70 border border-indigo-200 space-y-2">
                    <label class="block text-xs font-extrabold text-indigo-950">Kebijakan Perhitungan Nilai Akhir Remidi:</label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs">
                        <label class="p-2.5 rounded-xl bg-white border border-indigo-200 cursor-pointer flex items-start gap-2">
                            <input type="radio" name="kebijakan_remidi" value="kkm" checked class="mt-0.5">
                            <div>
                                <span class="font-bold text-gray-900 block">Maksimal KKM ({{ number_format($kkm, 0) }})</span>
                                <span class="text-[10px] text-gray-500">Nilai remidi &ge; KKM dicatat sebesar KKM.</span>
                            </div>
                        </label>
                        <label class="p-2.5 rounded-xl bg-white border border-indigo-200 cursor-pointer flex items-start gap-2">
                            <input type="radio" name="kebijakan_remidi" value="terbaik" class="mt-0.5">
                            <div>
                                <span class="font-bold text-gray-900 block">Nilai Terbaik</span>
                                <span class="text-[10px] text-gray-500">Ambil nilai tertinggi antara awal &amp; remidi.</span>
                            </div>
                        </label>
                        <label class="p-2.5 rounded-xl bg-white border border-indigo-200 cursor-pointer flex items-start gap-2">
                            <input type="radio" name="kebijakan_remidi" value="rata_rata" class="mt-0.5">
                            <div>
                                <span class="font-bold text-gray-900 block">Rata-rata</span>
                                <span class="text-[10px] text-gray-500">(Nilai Awal + Nilai Remidi) &divide; 2.</span>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Paket Soal & Durasi --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Paket Soal yang Digunakan</label>
                        <select name="sumber_soal" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-xs font-semibold outline-none">
                            <option value="salin_induk">Gunakan Soal Ujian Utama (Salin Otomatis)</option>
                            <option value="buat_baru">Buat Paket Soal Baru (Masuk Editor Soal)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Durasi Pengerjaan (Menit)</label>
                        <input type="number" name="durasi_menit" value="{{ $ujian->durasi_menit }}" min="1" max="999" required
                            class="w-full rounded-xl border border-gray-300 px-3 py-2 text-xs font-semibold outline-none">
                    </div>
                </div>

                {{-- Jadwal Pelaksanaan --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Waktu Mulai</label>
                        <input type="datetime-local" name="waktu_mulai" value="{{ now()->format('Y-m-d\TH:i') }}" required
                            class="w-full rounded-xl border border-gray-300 px-3 py-2 text-xs font-semibold outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Batas Akhir (Deadline)</label>
                        <input type="datetime-local" name="deadline" value="{{ now()->addDay()->format('Y-m-d\TH:i') }}" required
                            class="w-full rounded-xl border border-gray-300 px-3 py-2 text-xs font-semibold outline-none">
                    </div>
                </div>

                {{-- Daftar Siswa Peserta (Tercentang Otomatis) --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-xs font-bold text-gray-700">
                            Daftar Siswa Peserta (<span x-text="selectedSiswaIds.length"></span> dipilih):
                        </label>
                        <div class="flex gap-2 text-[11px]">
                            <button type="button" @click="pilihOtomatisSiswa()" class="text-indigo-600 font-bold hover:underline cursor-pointer">Reset Otomatis</button>
                            <button type="button" @click="selectedSiswaIds = []" class="text-gray-400 hover:underline cursor-pointer">Kosongkan</button>
                        </div>
                    </div>

                    <div class="max-h-44 overflow-y-auto border border-gray-200 rounded-2xl divide-y divide-gray-100 p-2 space-y-1">
                        <template x-for="p in pesertas" :key="p.siswa_id">
                            <label class="flex items-center justify-between p-2 rounded-xl hover:bg-gray-50 cursor-pointer text-xs">
                                <div class="flex items-center gap-2.5">
                                    <input type="checkbox" name="siswa_ids[]" :value="p.siswa_id" x-model="selectedSiswaIds"
                                        class="rounded border-gray-300 text-indigo-600">
                                    <div>
                                        <span class="font-bold text-gray-800" x-text="p.nama"></span>
                                        <span class="text-[11px] text-gray-400 ml-1" x-text="'(' + p.kelas + ')'"></span>
                                    </div>
                                </div>
                                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full"
                                    :class="{
                                        'bg-rose-50 text-rose-600': p.status_kategori === 'remidi',
                                        'bg-gray-100 text-gray-600': p.status_kategori === 'belum_ujian',
                                        'bg-emerald-50 text-emerald-600': p.status_kategori === 'tuntas',
                                        'bg-amber-50 text-amber-700': p.status_kategori === 'menunggu_essay'
                                    }"
                                    x-text="p.sudah_selesai && !p.menunggu_koreksi_essay ? ('Nilai: ' + p.nilai_akhir) : p.status_label">
                                </span>
                            </label>
                        </template>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" @click="showModalRemidi = false"
                        class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-xs font-bold text-gray-600 cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" :disabled="selectedSiswaIds.length === 0"
                        class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-xs font-bold text-white shadow-xs cursor-pointer disabled:opacity-50">
                        Simpan &amp; Buat <span x-text="jenisModal === 'remidi' ? 'Remidi' : 'Susulan'"></span>
                    </button>
                </div>
            </form>
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
        showModalRemidi: false,
        jenisModal: 'remidi',
        selectedSiswaIds: [],

        get filteredPesertas() {
            return this.pesertas.filter(p => {
                const matchSearch = p.nama.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                                    p.nisn.toLowerCase().includes(this.searchQuery.toLowerCase());
                const matchStatus = this.filterStatus === 'semua' || p.status_kategori === this.filterStatus;
                return matchSearch && matchStatus;
            });
        },

        bukaModalRemidiSusulan(jenis) {
            this.jenisModal = jenis;
            this.pilihOtomatisSiswa();
            this.showModalRemidi = true;
        },

        pilihOtomatisSiswa() {
            const targetKategori = this.jenisModal === 'remidi' ? 'remidi' : 'belum_ujian';
            this.selectedSiswaIds = this.pesertas
                .filter(p => p.status_kategori === targetKategori)
                .map(p => String(p.siswa_id));
        }
    };
}
</script>
@endsection
