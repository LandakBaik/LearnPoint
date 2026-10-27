@extends('layouts.app')

@section('title', 'Edit Materi: ' . $firstMateri->judul)

@section('content')
<div class="space-y-6" x-data="{
    kelasPerMapel: {{ Js::from($kelasPerMapel) }},
    selectedMapelId: '{{ $selectedMapelId }}',
    selectedTingkatan: '{{ $selectedTingkatan }}',
    selectedKelasIds: {{ Js::from(array_map('strval', $selectedKelasIds)) }},
    kelases: [],

    fileName: '',
    fileSize: '',
    hasNewFile: false,

    init() {
        this.loadKelas();
    },

    loadKelas() {
        if (this.kelasPerMapel[this.selectedMapelId] && this.kelasPerMapel[this.selectedMapelId][this.selectedTingkatan]) {
            this.kelases = this.kelasPerMapel[this.selectedMapelId][this.selectedTingkatan];
        } else {
            this.kelases = [];
        }
    },

    isChecked(pkId) {
        return this.selectedKelasIds.includes(String(pkId));
    },

    handleFileChange(event) {
        const file = event.target.files[0];
        if (file) {
            this.fileName = file.name;
            this.fileSize = (file.size / (1024 * 1024)).toFixed(1) + ' MB • Siap diunggah';
            this.hasNewFile = true;
        } else {
            this.hasNewFile = false;
        }
    }
}">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Edit Materi</h1>
            <p class="text-sm text-gray-500 mt-1">Perbarui informasi materi. Mata pelajaran dan tingkatan tidak dapat diubah.</p>
        </div>
        <div>
            <a href="{{ route('guru.materi') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-gray-300 bg-white text-gray-700 font-semibold text-sm hover:bg-gray-50 transition-colors shadow-sm cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Terdapat kesalahan pada isian Anda:</h3>
                    <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <form action="{{ route('guru.materi.update', $id_grub_materi) }}" method="POST" enctype="multipart/form-data" class="p-6 sm:p-8 space-y-6">
            @csrf
            @method('PUT')

            {{-- Section 1: Informasi Dasar --}}
            <div>
                <h3 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100">Informasi Dasar</h3>
                <div class="space-y-4">
                    {{-- Judul --}}
                    <div class="space-y-1.5">
                        <label for="judul" class="block text-sm font-bold text-gray-700">Judul Materi <span class="text-red-500">*</span></label>
                        <input
                            type="text"
                            name="judul"
                            id="judul"
                            value="{{ old('judul', $firstMateri->judul) }}"
                            placeholder="Contoh: Sistem Persamaan Linear"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 text-sm font-medium text-gray-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400"
                            maxlength="255"
                            required
                        >
                    </div>

                    {{-- Deskripsi --}}
                    <div class="space-y-1.5">
                        <label for="deskripsi" class="block text-sm font-bold text-gray-700">Deskripsi / Petunjuk Belajar (Opsional)</label>
                        <textarea
                            name="deskripsi"
                            id="deskripsi"
                            rows="3"
                            placeholder="Tambahkan petunjuk untuk siswa mengenai materi ini..."
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 text-sm font-medium text-gray-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400"
                            maxlength="1000"
                        >{{ old('deskripsi', $firstMateri->deskripsi) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Section 2: Sasaran Kelas (Lock Mapel & Tingkatan) --}}
            <div class="pt-2">
                <h3 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100">Sasaran Kelas</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                    {{-- Mata Pelajaran (readonly) --}}
                    <div class="space-y-1.5">
                        <label class="block text-sm font-bold text-gray-700">Mata Pelajaran</label>
                        <div class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-100 text-sm font-medium text-gray-500 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            {{ $mapels->firstWhere('id', $selectedMapelId)?->nama_mapel ?? '-' }}
                        </div>
                    </div>

                    {{-- Tingkatan (readonly) --}}
                    <div class="space-y-1.5">
                        <label class="block text-sm font-bold text-gray-700">Tingkat Kelas</label>
                        <div class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-100 text-sm font-medium text-gray-500 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            Tingkat {{ $selectedTingkatan }}
                        </div>
                    </div>
                </div>

                {{-- Checkbox Kelas --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-sm font-bold text-gray-700">Pilih Kelas</label>
                        <span class="text-xs text-gray-500 font-normal">Hapus centang untuk menarik materi dari kelas tersebut</span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                        <template x-for="kelas in kelases" :key="kelas.pengampu_kelas_id">
                            <label class="relative flex items-center p-3 rounded-xl border cursor-pointer transition-colors"
                                :class="isChecked(kelas.pengampu_kelas_id)
                                    ? 'bg-blue-50 border-blue-300'
                                    : 'border-gray-200 hover:bg-blue-50 hover:border-blue-200'">
                                <input
                                    type="checkbox"
                                    name="pengampu_kelas_ids[]"
                                    :value="kelas.pengampu_kelas_id"
                                    :checked="isChecked(kelas.pengampu_kelas_id)"
                                    @change="
                                        if ($event.target.checked) {
                                            if (!selectedKelasIds.includes(String(kelas.pengampu_kelas_id))) {
                                                selectedKelasIds.push(String(kelas.pengampu_kelas_id));
                                            }
                                        } else {
                                            selectedKelasIds = selectedKelasIds.filter(id => id !== String(kelas.pengampu_kelas_id));
                                        }
                                    "
                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                                >
                                <span class="ml-2.5 text-sm font-medium text-gray-800" x-text="kelas.nama_kelas"></span>
                            </label>
                        </template>
                    </div>

                    <p class="text-xs text-amber-600 flex items-center gap-1 font-medium mt-1" x-show="selectedKelasIds.length === 0">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Semua kelas dilepas. Menyimpan perubahan ini akan menghapus seluruh materi ini.
                    </p>

                    @error('pengampu_kelas_ids')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Section 3: Berkas Materi --}}
            <div class="pt-2">
                <h3 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100">Berkas Materi</h3>
                <div class="space-y-4">

                    {{-- File saat ini --}}
                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-3.5 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V7.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 1H7a2 2 0 00-2 2v16a2 2 0 002 2z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-amber-700 uppercase tracking-wide">File Saat Ini</p>
                            <p class="text-sm font-medium text-gray-800 truncate">{{ basename($firstMateri->file_materi) }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">Kosongkan kolom unggah jika tidak ingin mengganti file</p>
                        </div>
                    </div>

                    {{-- Upload File Baru (opsional) --}}
                    <div class="space-y-1.5">
                        <label class="block text-sm font-bold text-gray-700">Ganti Dokumen Materi (PDF / PPT / PPTX) <span class="text-gray-400 font-normal">- Opsional</span></label>

                        <div class="border-2 border-dashed border-blue-200/80 bg-blue-50/20 rounded-2xl p-6 text-center space-y-2 hover:bg-blue-50/40 transition-colors cursor-pointer relative">
                            <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mx-auto mb-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            </div>
                            <p class="text-sm font-semibold text-gray-700">Klik atau tarik berkas ke sini untuk mengganti</p>
                            <p class="text-xs text-gray-400">Maksimal 10 MB (.pdf, .ppt, .pptx)</p>

                            <input
                                type="file"
                                id="file_materi"
                                name="file_materi"
                                accept=".pdf,.ppt,.pptx"
                                @change="handleFileChange($event)"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                            >
                        </div>

                        {{-- Preview file baru --}}
                        <template x-if="hasNewFile">
                            <div class="mt-3 bg-green-50 border border-green-200 rounded-2xl p-3.5 flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-green-100 text-green-600 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-semibold text-green-700 uppercase tracking-wide">File Baru Dipilih</p>
                                    <p class="text-sm font-bold text-gray-800 truncate" x-text="fileName"></p>
                                    <p class="text-xs text-gray-500 mt-0.5" x-text="fileSize"></p>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- YouTube --}}
                    <div class="space-y-1.5 pt-2">
                        <label for="url_youtube" class="block text-sm font-bold text-gray-700">Tautan Video YouTube (Opsional)</label>
                        <input
                            type="url"
                            name="url_youtube"
                            id="url_youtube"
                            value="{{ old('url_youtube', $firstMateri->url_youtube) }}"
                            placeholder="Contoh: https://www.youtube.com/watch?v=..."
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 text-sm font-medium text-gray-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400"
                        >
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="pt-6 mt-6 border-t border-gray-100 flex items-center justify-end gap-3">
                <a href="{{ route('guru.materi') }}" class="px-6 py-2.5 rounded-xl border border-gray-300 text-gray-700 text-sm font-semibold hover:bg-gray-50 transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-8 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition-colors shadow-sm inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
