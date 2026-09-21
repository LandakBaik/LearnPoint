@extends('layouts.app')

@section('title', 'Daftar Tugas')

@section('content')
<div class="space-y-6" x-data="{ 
    showModal: false, 
    fileName: '',
    fileSize: '',
    hasFile: false,
    handleFileChange(event) {
        const file = event.target.files[0];
        if (file) {
            this.fileName = file.name;
            this.fileSize = (file.size / (1024 * 1024)).toFixed(1) + ' MB • Siap diunggah';
            this.hasFile = true;
        }
    }
}">

    <!-- Header Title & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Tugas</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola tugas, pengumpulan, dan penilaian siswa SMP terpadu.</p>
        </div>
        <div>
            <button 
                @click="showModal = true" 
                type="button" 
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 text-white font-semibold text-sm hover:bg-blue-700 transition-colors shadow-sm cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Buat Tugas
            </button>
        </div>
    </div>

    <!-- Stats Summary Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Card 1: Total Tugas -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500">Total Tugas</p>
                <h3 class="text-2xl font-extrabold text-gray-900 mt-1">0</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>

        <!-- Card 2: Aktif -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500">Aktif</p>
                <h3 class="text-2xl font-extrabold text-gray-900 mt-1">0</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <!-- Card 3: Perlu Dinilai -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500">Perlu Dinilai</p>
                <h3 class="text-2xl font-extrabold text-gray-900 mt-1">0</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </div>
        </div>

        <!-- Card 4: Deadline Terdekat -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500">Deadline Terdekat</p>
                <h3 class="text-2xl font-extrabold text-gray-900 mt-1">-</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-red-50 text-red-500 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm flex flex-col md:flex-row gap-3 items-center justify-between">
        <!-- Search Input -->
        <div class="relative w-full md:w-80">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <input 
                type="text" 
                placeholder="Cari tugas berdasarkan judul atau materi..." 
                class="w-full pl-10 pr-4 py-2 rounded-full border border-gray-200 bg-gray-50/50 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all placeholder-gray-400"
            >
        </div>

        <!-- Filter Dropdowns -->
        <div class="flex items-center gap-2.5 w-full md:w-auto flex-wrap md:flex-nowrap">
            <select class="rounded-full border border-gray-200 px-4 py-2 text-xs font-medium bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                <option>Semua Kelas</option>
                <option>Kelas 7A</option>
                <option>Kelas 7B</option>
                <option>Kelas 8A</option>
            </select>

            <select class="rounded-full border border-gray-200 px-4 py-2 text-xs font-medium bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                <option>Semua Mata Pelajaran</option>
                <option>Matematika</option>
                <option>IPA</option>
                <option>Bahasa Indonesia</option>
            </select>

            <select class="rounded-full border border-gray-200 px-4 py-2 text-xs font-medium bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                <option>Semua Status</option>
                <option>Aktif</option>
                <option>Perlu Dinilai</option>
                <option>Selesai</option>
            </select>

            <select class="rounded-full border border-gray-200 px-4 py-2 text-xs font-medium bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                <option>Urutan: Terbaru</option>
                <option>Urutan: Terlama</option>
                <option>Urutan: Deadline</option>
            </select>

            <button type="button" class="w-9 h-9 rounded-full border border-gray-200 flex items-center justify-center text-gray-500 hover:bg-gray-50 transition-colors shrink-0" title="Refresh Filter">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </button>
        </div>
    </div>

    <!-- Data Table Container -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <!-- Table Top Header -->
        <div class="px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
            <div class="flex items-center gap-2.5">
                <h3 class="text-base font-bold text-gray-900">Daftar Tugas</h3>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-600 border border-blue-100">0 Aktif Ditampilkan</span>
            </div>
            <p class="text-xs text-gray-400 font-medium">Tahun Ajaran 2026/2027 &bull; Semester Ganjil</p>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-200 text-xs font-bold text-gray-500 uppercase tracking-wider">
                        <th class="py-4 px-6">Tugas & Detail Info</th>
                        <th class="py-4 px-6">Penilaian</th>
                        <th class="py-4 px-6">Pengumpulan & Progres</th>
                        <th class="py-4 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    <tr>
                        <td colspan="4" class="py-16 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 rounded-2xl bg-gray-100 text-gray-400 flex items-center justify-center mb-3">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                </div>
                                <p class="text-base font-bold text-gray-800">Belum Ada Data Tugas</p>
                                <p class="text-xs text-gray-400 mt-1">Tugas yang Anda buat akan ditampilkan di sini secara terstruktur.</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Rows Count Footer -->
        <div class="px-6 py-4 border-t border-gray-200 bg-white flex items-center justify-between">
            <p class="text-xs text-gray-500">
                Menampilkan <span class="font-bold text-gray-800">0</span> total tugas
            </p>
        </div>
    </div>

    <!-- Modal Popup Buat Tugas Baru -->
    <div 
        x-show="showModal" 
        x-cloak 
        @keydown.escape.window="showModal = false"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto"
    >
        <!-- Modal Backdrop Overlay -->
        <div 
            x-show="showModal" 
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="showModal = false" 
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
        ></div>

        <!-- Modal Dialog Box -->
        <div 
            x-show="showModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-4"
            class="relative w-full max-w-xl bg-white rounded-3xl shadow-2xl border border-slate-100 z-10 overflow-hidden"
        >
            <!-- Modal Header -->
            <div class="p-6 sm:p-7 border-b border-slate-100 flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">Buat Tugas Baru</h3>
                    <p class="text-xs text-slate-500 mt-1">Isi formulir penugasan untuk siswa kelas yang Anda ampu.</p>
                </div>
                <button 
                    @click="showModal = false" 
                    type="button" 
                    class="w-8 h-8 rounded-full text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition-colors shrink-0"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Modal Form Body -->
            <form action="#" method="POST" enctype="multipart/form-data" @submit.preventDefault="showModal = false" class="p-6 sm:p-7 space-y-5">
                <!-- Field 1: Judul Tugas -->
                <div class="space-y-1.5">
                    <label for="judul_tugas" class="block text-xs font-bold text-slate-700">Judul Tugas <span class="text-red-500">*</span></label>
                    <input 
                        type="text" 
                        name="judul_tugas" 
                        id="judul_tugas" 
                        placeholder="Masukkan judul tugas (contoh: Sistem Persamaan Linear)" 
                        class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-slate-400"
                    >
                </div>

                <!-- Field 2 & 3: Mata Pelajaran & Kelas Sasaran -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Mata Pelajaran -->
                    <div class="space-y-1.5">
                        <label for="mapel_tugas" class="block text-xs font-bold text-slate-700">Mata Pelajaran <span class="text-red-500">*</span></label>
                        <select 
                            name="mapel_tugas" 
                            id="mapel_tugas" 
                            class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all cursor-pointer"
                        >
                            <option value="Matematika">Matematika</option>
                            <option value="IPA">IPA</option>
                            <option value="Bahasa Indonesia">Bahasa Indonesia</option>
                            <option value="Bahasa Inggris">Bahasa Inggris</option>
                        </select>
                    </div>

                    <!-- Kelas Sasaran -->
                    <div class="space-y-1.5">
                        <label for="kelas_tugas" class="block text-xs font-bold text-slate-700">Kelas Sasaran <span class="text-red-500">*</span></label>
                        <select 
                            name="kelas_tugas" 
                            id="kelas_tugas" 
                            class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all cursor-pointer"
                        >
                            <option value="Kelas 7A">Kelas 7A</option>
                            <option value="Kelas 7B">Kelas 7B</option>
                            <option value="Kelas 8A">Kelas 8A</option>
                            <option value="Kelas 9A">Kelas 9A</option>
                        </select>
                    </div>
                </div>

                <!-- Field 4: Deskripsi Tugas -->
                <div class="space-y-1.5">
                    <label for="deskripsi_tugas" class="block text-xs font-bold text-slate-700">Deskripsi Tugas</label>
                    <textarea 
                        name="deskripsi_tugas" 
                        id="deskripsi_tugas" 
                        rows="3" 
                        placeholder="Tambahkan penjelasan konteks tugas atau ringkasan capaian pembelajaran..." 
                        class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-slate-400"
                    ></textarea>
                </div>

                <!-- Field 5: Lampiran Lembar Kerja / Dokumen Pendukung -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700">Lampiran Lembar Kerja / Dokumen Pendukung</label>
                    <div class="border-2 border-dashed border-slate-200 bg-slate-50/40 rounded-2xl p-5 text-center space-y-1.5 hover:bg-slate-50 transition-colors cursor-pointer">
                        <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mx-auto mb-1 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        </div>
                        <p class="text-xs font-semibold text-slate-700">
                            Tarik & lepaskan file lembar kerja di sini, atau <label for="file_tugas_input" class="text-blue-600 font-bold hover:underline cursor-pointer">Pilih Berkas</label>
                        </p>
                        <p class="text-[11px] text-slate-400">Mendukung berkas: PDF, DOCX, JPG, PNG (Ukuran maksimal: 10 MB)</p>
                        <input type="file" id="file_tugas_input" name="file_tugas_input" accept=".pdf,.docx,.jpg,.png" @change="handleFileChange($event)" class="hidden">
                    </div>

                    <!-- Dynamic Upload Preview Pill -->
                    <template x-if="hasFile">
                        <div class="bg-indigo-50/50 border border-indigo-100/80 rounded-2xl p-3 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center shrink-0 font-bold">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V7.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 1H7a2 2 0 00-2 2v16a2 2 0 002 2z"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-800 truncate" x-text="fileName"></p>
                                    <p class="text-[11px] text-slate-500 mt-0.5" x-text="fileSize"></p>
                                </div>
                            </div>
                            <button @click="hasFile = false" type="button" class="text-slate-400 hover:text-slate-600 p-1 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </template>
                </div>

                <!-- Field 6 & 7: Tanggal Mulai & Deadline Waktu -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Tanggal Mulai -->
                    <div class="space-y-1.5">
                        <label for="tanggal_mulai" class="block text-xs font-bold text-slate-700">Tanggal Mulai <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input 
                                type="text" 
                                name="tanggal_mulai" 
                                id="tanggal_mulai" 
                                value="09/16/2026 08:00 AM" 
                                class="w-full pl-4 pr-10 py-3 rounded-2xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                            >
                            <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </span>
                        </div>
                    </div>

                    <!-- Batas Waktu Pengumpulan (Deadline) -->
                    <div class="space-y-1.5">
                        <label for="deadline" class="block text-xs font-bold text-slate-700">Batas Waktu Pengumpulan (Deadline) <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input 
                                type="text" 
                                name="deadline" 
                                id="deadline" 
                                value="09/20/2026 11:59 PM" 
                                class="w-full pl-4 pr-10 py-3 rounded-2xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                            >
                            <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Field 8: Instruksi Pengumpulan untuk Siswa -->
                <div class="space-y-1.5">
                    <label for="instruksi" class="block text-xs font-bold text-slate-700">Instruksi Pengumpulan untuk Siswa <span class="text-red-500">*</span></label>
                    <textarea 
                        name="instruksi" 
                        id="instruksi" 
                        rows="3" 
                        class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                    >Kerjakan pada buku tugas dengan tulisan tangan rapi, kemudian foto atau pindai lalu unggah hasilnya dalam format PDF/JPG.</textarea>
                </div>

                <!-- Modal Action Buttons -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button 
                        @click="showModal = false" 
                        type="button" 
                        class="px-6 py-2.5 rounded-full border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition-colors"
                    >
                        Batalkan
                    </button>
                    <button 
                        type="submit" 
                        class="px-7 py-2.5 rounded-full bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition-colors shadow-sm"
                    >
                        Publikasikan Tugas
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Page Footer -->
    <div class="pt-6 border-t border-gray-200/80 flex flex-col md:flex-row items-center justify-between gap-4 text-xs text-gray-400">
        <div>
            <p class="font-bold text-gray-700">LearnPoint SMP</p>
            <p class="mt-0.5">&copy; 2024 LearnPoint Indonesia &bull; Terintegrasi dengan Kurikulum Merdeka SMP. Hak Cipta Dilindungi Undang-Undang.</p>
        </div>
        <div class="flex items-center gap-4 flex-wrap">
            <a href="#" class="hover:text-gray-600">Kebijakan Privasi</a>
            <a href="#" class="hover:text-gray-600">Syarat & Ketentuan Layanan</a>
            <a href="#" class="hover:text-gray-600">Pusat Bantuan Sekolah</a>
            <a href="#" class="hover:text-gray-600">Kontak Admin</a>
        </div>
    </div>

</div>
@endsection
