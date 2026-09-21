@extends('layouts.app')

@section('title', 'Materi Pembelajaran')

@section('content')
<div class="space-y-6" x-data="{ 
    showModal: false, 
    formatKonten: 'pdf',
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
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Materi Pembelajaran</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola dan bagikan materi pembelajaran untuk siswa.</p>
        </div>
        <div>
            <button 
                @click="showModal = true" 
                type="button" 
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 text-white font-semibold text-sm hover:bg-blue-700 transition-colors shadow-sm cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Tambah Materi
            </button>
        </div>
    </div>

    <!-- Stats Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Card 1: Total Materi -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500">Total Materi Tersedia</p>
                <h3 class="text-2xl font-extrabold text-gray-900 mt-0.5">{{ $materis->count() }} Dokumen</h3>
            </div>
        </div>

        <!-- Card 2: Format PDF -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-red-50 text-red-500 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V7.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 1H7a2 2 0 00-2 2v16a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500">Berkas Format PDF</p>
                <h3 class="text-2xl font-extrabold text-gray-900 mt-0.5">{{ $materis->count() }} Berkas</h3>
            </div>
        </div>

        <!-- Card 3: Video Interaktif -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500">Video Interaktif</p>
                <h3 class="text-2xl font-extrabold text-gray-900 mt-0.5">{{ $materis->count() }} Video</h3>
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
                placeholder="Cari berdasarkan judul materi" 
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
                <option>Semua Jenis</option>
                <option>PDF</option>
                <option>Video YouTube</option>
            </select>

            <button type="button" class="w-9 h-9 rounded-full border border-gray-200 flex items-center justify-center text-gray-500 hover:bg-gray-50 transition-colors shrink-0" title="Reset Filter">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
            </button>
        </div>
    </div>

    <!-- Data Table Container -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-200 text-xs font-bold text-gray-500 uppercase tracking-wider">
                        <th class="py-4 px-6">Materi Pembelajaran</th>
                        <th class="py-4 px-6">Mata Pelajaran & Kelas</th>
                        <th class="py-4 px-6">Format / Jenis</th>
                        <th class="py-4 px-6">Tanggal Unggah</th>
                        <th class="py-4 px-6 text-right">Aksi Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    <tr>
                        <td colspan="5" class="py-16 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 rounded-2xl bg-gray-100 text-gray-400 flex items-center justify-center mb-3">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <p class="text-base font-bold text-gray-800">Belum Ada Data Materi</p>
                                <p class="text-xs text-gray-400 mt-1">Data materi pembelajaran yang telah diunggah akan muncul di sini.</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination & Rows Count Footer -->
        <div class="px-6 py-4 border-t border-gray-200 bg-white flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-xs text-gray-500">
                Menampilkan <span class="font-bold text-gray-800">0</span> materi
            </p>
        </div>
    </div>

    <!-- Modal Popup Tambah Materi Pembelajaran -->
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
            class="relative w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-slate-100 z-10 overflow-hidden"
        >
            <!-- Modal Header -->
            <div class="p-6 sm:p-7 border-b border-slate-100 flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">Tambah Materi Pembelajaran</h3>
                    <p class="text-xs text-slate-500 mt-1">Lengkapi detail materi kurikulum yang akan diakses oleh siswa.</p>
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
                <!-- Field 1: Judul Materi -->
                <div class="space-y-1.5">
                    <label for="judul" class="block text-xs font-bold text-slate-700">Judul Materi <span class="text-red-500">*</span></label>
                    <input 
                        type="text" 
                        name="judul" 
                        id="judul" 
                        placeholder="Sistem Persamaan Linear" 
                        class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-slate-400"
                    >
                </div>

                <!-- Field 2 & 3: Mata Pelajaran & Kelas Sasaran -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Mata Pelajaran -->
                    <div class="space-y-1.5">
                        <label for="mapel" class="block text-xs font-bold text-slate-700">Mata Pelajaran <span class="text-red-500">*</span></label>
                        <select 
                            name="mapel" 
                            id="mapel" 
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
                        <label for="kelas" class="block text-xs font-bold text-slate-700">Kelas Sasaran <span class="text-red-500">*</span></label>
                        <select 
                            name="kelas" 
                            id="kelas" 
                            class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all cursor-pointer"
                        >
                            <option value="Kelas 7A">Kelas 7A</option>
                            <option value="Kelas 7B">Kelas 7B</option>
                            <option value="Kelas 8A">Kelas 8A</option>
                            <option value="Kelas 9A">Kelas 9A</option>
                        </select>
                    </div>
                </div>

                <!-- Field 4: Deskripsi / Petunjuk Belajar -->
                <div class="space-y-1.5">
                    <label for="deskripsi" class="block text-xs font-bold text-slate-700">Deskripsi / Petunjuk Belajar</label>
                    <textarea 
                        name="deskripsi" 
                        id="deskripsi" 
                        rows="3" 
                        placeholder="Pelajari bab ini secara teliti sebelum menghadapi kuis mingguan hari Kamis." 
                        class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-slate-400"
                    ></textarea>
                </div>

                <!-- Field 5: Format Konten Materi Toggle -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700">Format Konten Materi <span class="text-red-500">*</span></label>
                    <div class="bg-slate-100/80 p-1 rounded-2xl grid grid-cols-2 gap-1 text-sm font-semibold">
                        <!-- Option PDF -->
                        <button 
                            type="button" 
                            @click="formatKonten = 'pdf'" 
                            :class="formatKonten === 'pdf' ? 'bg-white text-blue-700 shadow-sm border border-slate-200/60 font-bold' : 'text-slate-600 hover:text-slate-900'"
                            class="py-2.5 rounded-xl transition-all flex items-center justify-center gap-2"
                        >
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Dokumen PDF</span>
                        </button>

                        <!-- Option YouTube -->
                        <button 
                            type="button" 
                            @click="formatKonten = 'youtube'" 
                            :class="formatKonten === 'youtube' ? 'bg-white text-blue-700 shadow-sm border border-slate-200/60 font-bold' : 'text-slate-600 hover:text-slate-900'"
                            class="py-2.5 rounded-xl transition-all flex items-center justify-center gap-2"
                        >
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Video YouTube</span>
                        </button>
                    </div>
                </div>

                <!-- Option Content: Upload Area PDF -->
                <div x-show="formatKonten === 'pdf'" class="space-y-3">
                    <div class="border-2 border-dashed border-blue-200/80 bg-blue-50/20 rounded-2xl p-6 text-center space-y-2 hover:bg-blue-50/40 transition-colors cursor-pointer">
                        <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mx-auto mb-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        </div>
                        <p class="text-xs font-semibold text-slate-700">
                            Tarik & letakkan berkas PDF ke sini, atau <label for="file_pdf" class="text-blue-600 font-bold hover:underline cursor-pointer">pilih berkas</label>
                        </p>
                        <p class="text-[11px] text-slate-400">Maksimal ukuran file: 25 MB (.pdf)</p>
                        <input type="file" id="file_pdf" name="file_pdf" accept=".pdf" @change="handleFileChange($event)" class="hidden">
                    </div>

                    <!-- File Preview Pill -->
                    <template x-if="hasFile">
                        <div class="bg-indigo-50/50 border border-indigo-100/80 rounded-2xl p-3.5 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-xl bg-red-100 text-red-600 flex items-center justify-center shrink-0 font-bold">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V7.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 1H7a2 2 0 00-2 2v16a2 2 0 002 2z"/></svg>
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

                <!-- Option Content: Video YouTube Link -->
                <div x-show="formatKonten === 'youtube'" class="space-y-1.5" style="display: none;">
                    <label for="url_youtube" class="block text-xs font-bold text-slate-700">Tautan / URL Video YouTube <span class="text-red-500">*</span></label>
                    <input 
                        type="url" 
                        name="url_youtube" 
                        id="url_youtube" 
                        placeholder="https://www.youtube.com/watch?v=..." 
                        class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-slate-400"
                    >
                </div>

                <!-- Modal Action Buttons -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button 
                        @click="showModal = false" 
                        type="button" 
                        class="px-6 py-2.5 rounded-full border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition-colors"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit" 
                        class="px-7 py-2.5 rounded-full bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition-colors shadow-sm"
                    >
                        Simpan & Bagikan
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
