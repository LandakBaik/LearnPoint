@extends('layouts.app')

@section('title', 'Relokasi Mapel & Kelas')

@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="{
    search: '',
    filterJenis: 'semua',
    filterPengampu: '',
    activities: {{ Js::from($allActivities) }},
    targetOptions: {{ Js::from($targetOptions) }},

    modalOpen: false,
    selectedItem: null,
    targetPengampuId: '',
    selectedAksi: 'pindah_aman',

    get filteredActivities() {
        return this.activities.filter(item => {
            if (this.filterJenis !== 'semua' && item.jenis !== this.filterJenis) {
                return false;
            }
            if (this.filterPengampu !== '' && String(item.pengampu_kelas_id) !== String(this.filterPengampu)) {
                return false;
            }
            if (this.search.trim() !== '') {
                const q = this.search.toLowerCase();
                return (item.judul || '').toLowerCase().includes(q) ||
                       (item.mapel_nama || '').toLowerCase().includes(q) ||
                       (item.kelas_nama || '').toLowerCase().includes(q);
            }
            return true;
        });
    },

    openModal(item) {
        this.selectedItem = item;
        this.targetPengampuId = '';
        this.selectedAksi = 'pindah_aman';
        this.modalOpen = true;
    },

    get selectedTargetObj() {
        if (!this.targetPengampuId) return null;
        return this.targetOptions.find(opt => String(opt.id) === String(this.targetPengampuId)) || null;
    },

    get isSameClassSelected() {
        if (!this.selectedItem || !this.selectedTargetObj) return false;
        return String(this.selectedItem.kelas_id) === String(this.selectedTargetObj.kelas_id);
    },

    onTargetChange() {
        if (this.isSameClassSelected) {
            this.selectedAksi = 'pindah_aman';
        } else {
            this.selectedAksi = (this.selectedItem && this.selectedItem.submission_count > 0) ? 'salin' : 'pindah_reset';
        }
    }
}">

    <!-- Header Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-blue-600 via-indigo-600 to-indigo-700 p-6 sm:p-8 text-white shadow-lg">
        <div class="absolute -right-10 -top-10 w-56 h-56 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2 max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 backdrop-blur-xs border border-white/20 text-xs font-semibold text-blue-100">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    <span>Pusat Koreksi Input Mapel &amp; Kelas Guru</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Relokasi &amp; Duplikasi Aktivitas Pembelajaran
                </h1>
                <p class="text-xs sm:text-sm text-blue-100 leading-relaxed">
                    Salah memilih mata pelajaran (misal tugas IPA terinput di Bahasa Inggris) atau salah memilih kelas? Pindahkan atau salin Materi, Tugas, Kuis, dan Ujian Anda di sini tanpa kehilangan soal maupun nilai siswa.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3 shrink-0">
                <div class="bg-white/10 backdrop-blur-xs border border-white/20 rounded-2xl p-3.5 text-center min-w-[110px]">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-blue-200">Total Aktivitas</p>
                    <p class="text-2xl font-black mt-1">{{ $counts['total'] }}</p>
                </div>
                <div class="bg-white/10 backdrop-blur-xs border border-white/20 rounded-2xl p-3.5 text-center min-w-[110px]">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-blue-200">Alokasi Kelas</p>
                    <p class="text-2xl font-black mt-1">{{ $pengampuKelases->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Panduan Singkat 2 Skenario -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-emerald-50/70 border border-emerald-200/80 rounded-2xl p-4 flex items-start gap-3.5">
            <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="text-xs space-y-1">
                <h3 class="font-bold text-emerald-950">Salah Mapel di Kelas yang Sama (Nilai 100% Aman)</h3>
                <p class="text-emerald-800 leading-relaxed">
                    Jika Anda memindahkan tugas/kuis/ujian antar mata pelajaran di <strong>kelas yang sama</strong> (contoh: IPA 7A &rarr; B. Inggris 7A), seluruh nilai dan jawaban siswa yang sudah mengerjakan akan <strong>otomatis ikut berpindah secara utuh</strong>.
                </p>
            </div>
        </div>

        <div class="bg-amber-50/70 border border-amber-200/80 rounded-2xl p-4 flex items-start gap-3.5">
            <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>
            </div>
            <div class="text-xs space-y-1">
                <h3 class="font-bold text-amber-950">Salah Kelas (Pindah ke Kelas Berbeda)</h3>
                <p class="text-amber-800 leading-relaxed">
                    Jika dipindah ke <strong>kelas berbeda</strong> (contoh: 7B &rarr; 7A), Anda dapat memilih untuk <strong>Menyalin (Duplikat) Soal</strong> ke kelas baru, atau <strong>Memindahkan &amp; Mereset Nilai Kelas Lama</strong> (stok Power kuis siswa otomatis dikembalikan).
                </p>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-2xs space-y-4">
        <!-- Tabs Jenis Aktivitas -->
        <div class="flex flex-wrap items-center gap-2 pb-3 border-b border-slate-100">
            <button type="button" @click="filterJenis = 'semua'"
                :class="filterJenis === 'semua' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer">
                Semua ({{ $counts['total'] }})
            </button>
            <button type="button" @click="filterJenis = 'tugas'"
                :class="filterJenis === 'tugas' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100'"
                class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer">
                Tugas ({{ $counts['tugas'] }})
            </button>
            <button type="button" @click="filterJenis = 'quiz'"
                :class="filterJenis === 'quiz' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100'"
                class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer">
                Kuis Petualangan ({{ $counts['quiz'] }})
            </button>
            <button type="button" @click="filterJenis = 'ujian'"
                :class="filterJenis === 'ujian' ? 'bg-amber-600 text-white shadow-xs' : 'bg-amber-50 text-amber-700 hover:bg-amber-100'"
                class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer">
                Ujian Evaluasi ({{ $counts['ujian'] }})
            </button>
            <button type="button" @click="filterJenis = 'materi'"
                :class="filterJenis === 'materi' ? 'bg-blue-600 text-white shadow-xs' : 'bg-blue-50 text-blue-700 hover:bg-blue-100'"
                class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer">
                Materi ({{ $counts['materi'] }})
            </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="sm:col-span-2 relative">
                <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input
                    type="text"
                    x-model="search"
                    placeholder="Cari judul tugas, kuis, ujian, materi, atau nama kelas..."
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-xs font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                >
            </div>
            <div>
                <select
                    x-model="filterPengampu"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-xs font-semibold text-slate-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                >
                    <option value="">-- Semua Mapel &amp; Kelas Asal --</option>
                    @foreach($targetOptions as $opt)
                        <option value="{{ $opt['id'] }}">{{ $opt['label'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Tabel Daftar Aktivitas -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3.5 px-5">Judul Aktivitas</th>
                        <th class="py-3.5 px-4">Jenis</th>
                        <th class="py-3.5 px-4">Posisi Saat Ini (Mapel &amp; Kelas)</th>
                        <th class="py-3.5 px-4 text-center">Butir Soal</th>
                        <th class="py-3.5 px-4 text-center">Pengerjaan Siswa</th>
                        <th class="py-3.5 px-5 text-right">Aksi Koreksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="item in filteredActivities" :key="item.uid">
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-5">
                                <p class="font-bold text-slate-900 text-xs" x-text="item.judul"></p>
                                <p class="text-[11px] text-slate-400 mt-0.5" x-text="'Dibuat: ' + (item.created_at || '-')"></p>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold border"
                                      :class="{
                                          'bg-indigo-50 text-indigo-700 border-indigo-200': item.jenis === 'tugas',
                                          'bg-emerald-50 text-emerald-700 border-emerald-200': item.jenis === 'quiz',
                                          'bg-amber-50 text-amber-700 border-amber-200': item.jenis === 'ujian',
                                          'bg-blue-50 text-blue-700 border-blue-200': item.jenis === 'materi'
                                      }"
                                      x-text="item.jenis_label">
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex flex-col">
                                    <span class="font-bold text-slate-800" x-text="item.mapel_nama"></span>
                                    <span class="text-[11px] text-blue-600 font-semibold" x-text="'Kelas ' + item.kelas_nama + ' (Tingkat ' + item.tingkatan + ')'"></span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span x-show="item.jenis !== 'materi'" class="font-bold text-slate-700" x-text="item.soal_count + ' Soal'"></span>
                                <span x-show="item.jenis === 'materi'" class="text-slate-400">-</span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <template x-if="item.jenis !== 'materi'">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold"
                                          :class="item.submission_count > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500'"
                                          x-text="item.submission_count + ' Siswa'">
                                    </span>
                                </template>
                                <span x-show="item.jenis === 'materi'" class="text-slate-400">-</span>
                            </td>
                            <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                <button
                                    type="button"
                                    @click="openModal(item)"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-600 text-blue-700 hover:text-white border border-blue-200/80 text-xs font-bold transition-all cursor-pointer shadow-2xs"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                    <span>Pindah / Salin</span>
                                </button>
                            </td>
                        </tr>
                    </template>

                    <tr x-show="filteredActivities.length === 0">
                        <td colspan="6" class="py-12 text-center text-slate-400">
                            <p class="font-bold text-slate-600 text-sm">Tidak ada aktivitas yang sesuai dengan filter</p>
                            <p class="text-xs text-slate-400 mt-1">Coba ubah kata kunci pencarian atau pilih tab jenis aktivitas lainnya.</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Relokasi / Duplikasi Aktivitas -->
    <div
        x-show="modalOpen"
        style="display: none;"
        class="fixed inset-0 z-50 overflow-y-auto"
    >
        <div class="min-h-screen px-4 text-center flex items-center justify-center">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="modalOpen = false"></div>

            <div class="relative bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 text-left shadow-2xl border border-slate-100 z-10 space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <span class="px-2.5 py-0.5 rounded-md bg-blue-50 text-blue-700 border border-blue-200 text-[10px] font-extrabold uppercase tracking-wider" x-text="selectedItem?.jenis_label"></span>
                        <h3 class="text-lg font-extrabold text-slate-900 mt-1">Pindahkan / Salin ke Mapel &amp; Kelas Lain</h3>
                    </div>
                    <button type="button" @click="modalOpen = false" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('guru.relokasi.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="jenis" :value="selectedItem?.jenis">
                    <input type="hidden" name="aktivitas_id" :value="selectedItem?.id">

                    <!-- Info Aktivitas Asal -->
                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 text-xs space-y-1">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Aktivitas Terpilih</p>
                        <p class="font-extrabold text-slate-900 text-sm" x-text="selectedItem?.judul"></p>
                        <div class="flex items-center justify-between pt-1 text-[11px] text-slate-600">
                            <span>Asal: <strong class="text-slate-900" x-text="(selectedItem?.mapel_nama || '') + ' — Kelas ' + (selectedItem?.kelas_nama || '')"></strong></span>
                            <span x-show="selectedItem?.jenis !== 'materi'" class="px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 font-bold" x-text="(selectedItem?.submission_count || 0) + ' Siswa Mengerjakan'"></span>
                        </div>
                    </div>

                    <!-- Pilih Target Mapel & Kelas -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Pilih Mata Pelajaran &amp; Kelas Tujuan <span class="text-rose-500">*</span>
                        </label>
                        <select
                            name="target_pengampu_kelas_id"
                            x-model="targetPengampuId"
                            @change="onTargetChange()"
                            required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-xs font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                        >
                            <option value="">-- Pilih Mapel &amp; Kelas Tujuan --</option>
                            <template x-for="opt in targetOptions" :key="opt.id">
                                <option
                                    :value="String(opt.id)"
                                    :disabled="String(opt.id) === String(selectedItem?.pengampu_kelas_id)"
                                    x-text="opt.label + (String(opt.id) === String(selectedItem?.pengampu_kelas_id) ? ' (Posisi Saat Ini)' : (String(opt.kelas_id) === String(selectedItem?.kelas_id) ? ' ★ [Kelas Sama - Nilai Aman]' : ''))"
                                ></option>
                            </template>
                        </select>
                    </div>

                    <!-- Deteksi Otomatis & Pilihan Aksi -->
                    <div x-show="selectedTargetObj" class="space-y-3">
                        <!-- Banner jika Kelas Sama -->
                        <div x-show="isSameClassSelected" class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-900 flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <div>
                                <p class="font-bold">Kelas Sama Terdeteksi (<span x-text="'Kelas ' + (selectedTargetObj?.kelas_nama || '')"></span>)!</p>
                                <p class="text-[11px] text-emerald-700 mt-0.5">
                                    Karena siswa di kelas ini sama, memindahkan mata pelajaran akan <strong>mempertahankan 100% nilai dan jawaban siswa</strong> yang sudah masuk tanpa perlu mengerjakan ulang.
                                </p>
                            </div>
                        </div>

                        <!-- Banner jika Beda Kelas -->
                        <div x-show="!isSameClassSelected" class="p-3.5 rounded-2xl bg-amber-50 border border-amber-200 text-xs text-amber-900 flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <div>
                                <p class="font-bold">Perpindahan Antar Kelas (<span x-text="'Kelas ' + (selectedItem?.kelas_nama || '') + ' → Kelas ' + (selectedTargetObj?.kelas_nama || '')"></span>)</p>
                                <p class="text-[11px] text-amber-800 mt-0.5">
                                    Pilih apakah Anda ingin <strong>menyalin soal</strong> ke kelas tujuan, atau <strong>memindahkan aktivitas</strong> dan mereset pengerjaan dari siswa kelas lama.
                                </p>
                            </div>
                        </div>

                        <!-- Radio Pilihan Aksi -->
                        <div class="space-y-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                Pilih Metode Eksekusi
                            </label>

                            <!-- Opsi 1: Pindah Aman (Jika Kelas Sama) -->
                            <label x-show="isSameClassSelected"
                                   class="flex items-start gap-3 p-3 rounded-xl border cursor-pointer transition-all"
                                   :class="selectedAksi === 'pindah_aman' ? 'bg-emerald-50/70 border-emerald-300' : 'bg-white border-slate-200'">
                                <input type="radio" name="aksi" value="pindah_aman" x-model="selectedAksi" class="mt-1 text-emerald-600">
                                <div class="text-xs">
                                    <p class="font-bold text-slate-900">Pindahkan ke Mata Pelajaran Tujuan (Nilai Siswa Utuh)</p>
                                    <p class="text-[11px] text-slate-500 mt-0.5">Mengubah mata pelajaran aktivitas ini tanpa menghapus jawaban maupun nilai siswa.</p>
                                </div>
                            </label>

                            <!-- Opsi 2: Pindah & Reset Nilai Kelas Lama (Jika Beda Kelas) -->
                            <label x-show="!isSameClassSelected"
                                   class="flex items-start gap-3 p-3 rounded-xl border cursor-pointer transition-all"
                                   :class="selectedAksi === 'pindah_reset' ? 'bg-rose-50/70 border-rose-300' : 'bg-white border-slate-200'">
                                <input type="radio" name="aksi" value="pindah_reset" x-model="selectedAksi" class="mt-1 text-rose-600">
                                <div class="text-xs">
                                    <p class="font-bold text-slate-900">Pindahkan ke Kelas Tujuan &amp; Bersihkan Nilai Kelas Lama</p>
                                    <p class="text-[11px] text-slate-500 mt-0.5">
                                        Aktivitas berpindah ke kelas tujuan. Jika ada siswa kelas lama yang terlanjur mengerjakan, nilainya di-reset dan item Power kuisnya dikembalikan.
                                    </p>
                                </div>
                            </label>

                            <!-- Opsi 3: Salin / Duplikat (Tersedia untuk Semua Kondisi) -->
                            <label class="flex items-start gap-3 p-3 rounded-xl border cursor-pointer transition-all"
                                   :class="selectedAksi === 'salin' ? 'bg-blue-50/70 border-blue-300' : 'bg-white border-slate-200'">
                                <input type="radio" name="aksi" value="salin" x-model="selectedAksi" class="mt-1 text-blue-600">
                                <div class="text-xs">
                                    <p class="font-bold text-slate-900">Salin / Duplikat ke Mapel &amp; Kelas Tujuan</p>
                                    <p class="text-[11px] text-slate-500 mt-0.5">
                                        Membuat salinan baru beserta seluruh soalnya di kelas/mapel tujuan, sedangkan data &amp; nilai di kelas asal dibiarkan tetap ada.
                                    </p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="pt-3 flex items-center justify-end gap-3">
                        <button type="button" @click="modalOpen = false" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors">
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="!targetPengampuId"
                            :class="!targetPengampuId ? 'bg-slate-200 text-slate-400 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700 text-white shadow-md shadow-blue-500/20 hover:-translate-y-0.5'"
                            class="px-5 py-2.5 rounded-xl text-xs font-bold transition-all"
                        >
                            Eksekusi Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

