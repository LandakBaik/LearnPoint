@extends('layouts.app')

@section('title', 'Tambah Ujian')

@section('content')

    <div x-data="ujianForm()" class="space-y-6">

        {{-- ========================================================= --}}
        {{-- HEADER --}}
        {{-- ========================================================= --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

            <div>
                <a href="{{ route('guru.ujian.index') }}"
                    class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-teal-700 mb-2">
                    ← Kembali ke Ujian
                </a>

                <h1 class="text-2xl font-bold text-gray-800">
                    Halaman Tambah Ujian
                </h1>

                <p class="text-sm text-gray-500 mt-1">
                    Buat dan kelola soal ujian untuk siswa.
                </p>
            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- INFORMASI UJIAN --}}
        {{-- ========================================================= --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">

            <div class="mb-6">
                <h2 class="text-lg font-bold text-gray-800">
                    Informasi Ujian
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Isi informasi dasar ujian terlebih dahulu.
                </p>
            </div>


            {{-- novalidate mencegah browser memblokir submit diam-diam pada tab soal yang tersembunyi --}}
            <form method="POST" action="{{ route('guru.ujian.store') }}" enctype="multipart/form-data" id="ujianForm"
                novalidate @submit="validateForm($event)">
                @csrf


                {{-- ================================================= --}}
                {{-- DATA DASAR UJIAN --}}
                {{-- ================================================= --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    {{-- JUDUL --}}
                    <div class="md:col-span-2">

                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Judul Ujian
                            <span class="text-red-500">*</span>
                        </label>

                        <input type="text" name="judul" x-ref="inputJudul" value="{{ old('judul') }}" maxlength="100"
                            placeholder="Contoh: Ujian Tengah Semester IPA Kelas VIII"
                            oninput="this.value = this.value.replace(/[^\p{L}\p{N} ]/gu, '')"
                            class="w-full border border-gray-300 rounded-xl px-4 py-3
                                 focus:ring-2 focus:ring-teal-500
                                 focus:border-teal-500">

                        <p class="text-xs text-gray-400 mt-1">
                            Maksimal 100 karakter (huruf, angka, dan spasi).
                        </p>

                    </div>



                    {{-- MAPEL + TINGKATAN + KELAS --}}
                    <div class="md:col-span-2" x-data="targetKelasForm()" id="section-target-kelas">

                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Mata Pelajaran & Target Kelas
                            <span class="text-red-500">*</span>
                        </label>

                        <div class="border border-gray-300 rounded-xl p-4 space-y-5">

                            {{-- PILIH MAPEL --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    1. Mata Pelajaran
                                </label>

                                <select name="mapel_id" x-model="selectedMapel" @change="ubahMapel()"
                                    class="w-full border border-gray-300 rounded-xl px-4 py-3
                                           focus:ring-2 focus:ring-teal-500 focus:border-teal-500">

                                    <option value="">-- Pilih Mata Pelajaran --</option>

                                    <template x-for="mapel in mapelList" :key="mapel.id">
                                        <option :value="String(mapel.id)" x-text="mapel.nama">
                                        </option>
                                    </template>

                                </select>
                            </div>

                            {{-- PILIH TINGKATAN --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    2. Tingkatan Kelas
                                </label>

                                <select x-model="selectedTingkatan" @change="ubahTingkatan()"
                                    :disabled="!selectedMapel || daftarTingkatan.length === 0"
                                    class="w-full border border-gray-300 rounded-xl px-4 py-3
                                           disabled:bg-gray-100 disabled:text-gray-400
                                           focus:ring-2 focus:ring-teal-500 focus:border-teal-500">

                                    <option value="">-- Pilih Tingkatan --</option>

                                    <template x-for="tingkat in daftarTingkatan" :key="tingkat.nama">
                                        <option :value="tingkat.nama" x-text="tingkat.nama">
                                        </option>
                                    </template>

                                </select>

                                <p x-show="selectedMapel && daftarTingkatan.length === 0"
                                    class="text-xs text-amber-600 mt-2">
                                    Belum ada tingkatan kelas untuk mata pelajaran ini.
                                </p>
                            </div>

                            {{-- PILIH KELAS --}}
                            <div>
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">

                                    <label class="block text-sm font-medium text-gray-700">
                                        3. Pilih Kelas
                                    </label>

                                    <span x-show="kelasTerlihat.length > 0" class="text-xs font-semibold text-teal-700"
                                        x-text="kelasDipilih.length + ' kelas dipilih'">
                                    </span>

                                </div>

                                {{-- BELUM PILIH MAPEL --}}
                                <div x-show="!selectedMapel"
                                    class="p-4 bg-gray-50 border border-dashed border-gray-300 rounded-xl text-center">

                                    <p class="text-sm text-gray-500">
                                        Pilih mata pelajaran terlebih dahulu.
                                    </p>

                                </div>

                                {{-- BELUM PILIH TINGKATAN --}}
                                <div x-show="selectedMapel && !selectedTingkatan"
                                    class="p-4 bg-gray-50 border border-dashed border-gray-300 rounded-xl text-center">

                                    <p class="text-sm text-gray-500">
                                        Pilih tingkatan untuk menampilkan daftar kelas.
                                    </p>

                                </div>

                                {{-- DAFTAR KELAS --}}
                                <div x-show="selectedMapel && selectedTingkatan"
                                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">

                                    <template x-for="kelas in kelasTerlihat" :key="kelas.pengampu_kelas_id">

                                        <label
                                            class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg
                                                   hover:bg-teal-50 cursor-pointer transition"
                                            :class="kelasDipilih.includes(String(kelas.pengampu_kelas_id)) ?
                                                'border-teal-500 bg-teal-50' :
                                                'bg-white'">

                                            <input type="checkbox" name="pengampu_kelas_id[]"
                                                :value="String(kelas.pengampu_kelas_id)" x-model="kelasDipilih"
                                                class="w-4 h-4 text-teal-700 rounded focus:ring-teal-500">

                                            <span class="text-sm text-gray-700" x-text="kelas.nama_kelas">
                                            </span>

                                        </label>

                                    </template>

                                    <div x-show="kelasTerlihat.length === 0"
                                        class="sm:col-span-2 lg:col-span-3 p-4 text-center
                                               bg-gray-50 rounded-xl border border-dashed border-gray-300">

                                        <p class="text-sm text-gray-500">
                                            Tidak ada kelas pada tingkatan ini.
                                        </p>

                                    </div>

                                </div>

                            </div>

                            {{-- RINGKASAN KELAS --}}
                            <div x-show="kelasDipilih.length > 0" class="p-4 bg-teal-50 border border-teal-100 rounded-xl">

                                <p class="text-sm font-semibold text-teal-800">
                                    Target ujian
                                </p>

                                <p class="text-sm text-teal-700 mt-1"
                                    x-text="kelasDipilih.length + ' kelas telah dipilih.'">
                                </p>

                            </div>

                        </div>

                        <p class="text-xs text-gray-400 mt-2">
                            Pilih mapel, tingkatan, lalu satu atau beberapa kelas.
                            Soal akan digunakan untuk seluruh kelas yang dipilih.
                        </p>

                        @error('mapel_id')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror

                        @error('pengampu_kelas_id')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror

                        @error('pengampu_kelas_id.*')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror

                    </div>



                    {{-- DURASI --}}
                    <div>

                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Durasi Ujian
                            <span class="text-red-500">*</span>
                        </label>

                        <div class="relative">

                            <input type="text" name="durasi_menit" x-ref="inputDurasi"
                                value="{{ old('durasi_menit', 60) }}" inputmode="numeric" maxlength="3"
                                oninput="
                                    this.value = this.value.replace(/[^0-9]/g, '').slice(0, 3);
                                    if (this.value !== '' && Number(this.value) > 999) {
                                        this.value = '999';
                                    }
                                "
                                class="w-full border border-gray-300 rounded-xl px-4 py-3 pr-20
                                 focus:ring-2 focus:ring-teal-500
                                 focus:border-teal-500">
                            <span class="absolute right-4 top-3 text-sm text-gray-400">
                                menit
                            </span>

                        </div>

                        @error('durasi_menit')
                            <p class="text-sm text-red-600 mt-1">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- KKM --}}
                    <div>

                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            KKM
                            <span class="text-red-500">*</span>
                        </label>

                        <input type="text" name="kkm" x-ref="inputKkm" value="{{ old('kkm', 75) }}"
                            inputmode="numeric" maxlength="2"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 2);"
                            class="w-full border border-gray-300 rounded-xl px-4 py-3
                         focus:ring-2 focus:ring-teal-500
                         focus:border-teal-500">

                        <p class="text-xs text-gray-400 mt-1">
                            Nilai KKM 0–99.
                        </p>

                    </div>

                    {{-- Pengaturan Visibilitas Nilai Siswa --}}
                    <div
                        class="md:col-span-2 p-4 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between gap-4">
                        <div>
                            <label for="tampilkan_nilai" class="text-sm font-bold text-gray-900 cursor-pointer block">
                                Tampilkan Nilai ke Siswa Setelah Ujian
                            </label>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Jika dinonaktifkan, siswa hanya melihat konfirmasi bahwa ujian telah terkirim tanpa
                                mengetahui skor nilainya.
                            </p>
                        </div>

                        <label class="relative inline-flex items-center cursor-pointer shrink-0">
                            <input type="hidden" name="tampilkan_nilai" value="0">
                            <input type="checkbox" id="tampilkan_nilai" name="tampilkan_nilai" value="1"
                                {{ old('tampilkan_nilai', true) ? 'checked' : '' }} class="sr-only peer">
                            <div
                                class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                            </div>
                        </label>
                    </div>


                    {{-- WAKTU MULAI --}}
                    <div>

                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Waktu Mulai
                            <span class="text-gray-400 font-normal">
                                (wajib diisi jika klik Jadwalkan Ujian)
                            </span>
                        </label>

                        <input type="datetime-local" name="waktu_mulai" x-ref="inputWaktuMulai"
                            value="{{ old('waktu_mulai') }}"
                            class="w-full border border-gray-300 rounded-xl px-4 py-3
                               focus:ring-2 focus:ring-teal-500
                               focus:border-teal-500">
                        @error('waktu_mulai')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror

                    </div>


                    {{-- DEADLINE --}}
                    <div>

                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Deadline
                            <span class="text-gray-400 font-normal">
                                (wajib diisi jika klik Jadwalkan Ujian)
                            </span>
                        </label>

                        <input type="datetime-local" name="deadline" x-ref="inputDeadline"
                            value="{{ old('deadline') }}"
                            class="w-full border border-gray-300 rounded-xl px-4 py-3
                               focus:ring-2 focus:ring-teal-500
                               focus:border-teal-500">
                        @error('deadline')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror

                    </div>


                    {{-- KISI-KISI --}}
                    <div class="md:col-span-2">

                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Kisi-kisi
                            <span class="text-gray-400 font-normal">
                                (opsional)
                            </span>
                        </label>

                        <textarea name="kisi_kisi" rows="4" maxlength="500"
                            placeholder="Tuliskan kisi-kisi atau kompetensi yang akan diujikan..."
                            class="w-full border border-gray-300 rounded-xl px-4 py-3
                               focus:ring-2 focus:ring-teal-500
                               focus:border-teal-500">{{ old('kisi_kisi') }}</textarea>

                        <p class="text-xs text-gray-400 mt-1">
                            Maksimal 500 karakter.
                        </p>

                    </div>

                </div>


                {{-- ================================================= --}}
                {{-- EDITOR SOAL --}}
                {{-- ================================================= --}}
                <div class="mt-8 border-t border-gray-100 pt-8" id="section-editor-soal">

                    {{-- HEADER SOAL --}}
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-5">

                        <div>

                            <h2 class="text-lg font-bold text-gray-800">
                                Soal Ujian
                                <span
                                    class="text-xs font-semibold text-teal-700 bg-teal-50 border border-teal-200 px-2.5 py-1 rounded-lg ml-2"
                                    x-text="questions.length + ' Soal (Minimal 2 Soal)'"></span>
                            </h2>

                            <p class="text-sm text-gray-500 mt-1">
                                Tambahkan minimal 2 soal. Bobot otomatis disesuaikan menjadi total 100 poin setiap kali soal
                                ditambah atau dihapus.
                            </p>

                        </div>


                        {{-- TAMBAH SOAL --}}
                        <button type="button" @click="addQuestion()"
                            class="inline-flex items-center justify-center gap-2
                               px-5 py-3 bg-teal-700 hover:bg-teal-800
                               text-white rounded-xl font-semibold transition cursor-pointer">

                            <span class="text-lg">+</span>

                            Tambah Soal

                        </button>

                    </div>


                    {{-- ================================================= --}}
                    {{-- DAFTAR + EDITOR SOAL --}}
                    {{-- ================================================= --}}
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">


                        {{-- ================================================= --}}
                        {{-- DAFTAR SOAL (DENGAN INDIKATOR VALIDASI) --}}
                        {{-- ================================================= --}}
                        <div class="lg:col-span-3 xl:col-span-2">

                            <div class="bg-gray-50 rounded-2xl border border-gray-200 p-4">

                                <h3 class="font-semibold text-gray-700 mb-3">
                                    Daftar Soal
                                </h3>

                                <div class="space-y-2">

                                    <template x-for="(question, index) in questions" :key="question.id">

                                        <button type="button" @click="selectedQuestion = index"
                                            class="w-full text-left px-3.5 py-3 rounded-xl border transition cursor-pointer"
                                            :class="selectedQuestion === index ?
                                                'bg-teal-700 text-white border-teal-700 shadow-xs' :
                                                (isQuestionComplete(question) ?
                                                    'bg-white text-gray-700 border-gray-200 hover:border-teal-400' :
                                                    'bg-amber-50/70 text-gray-800 border-amber-300 hover:border-amber-400'
                                                    )">

                                            <div class="flex items-center justify-between gap-2">

                                                <div class="flex items-center gap-1.5">
                                                    <span class="font-semibold text-sm">
                                                        Soal <span x-text="index + 1"></span>
                                                    </span>

                                                    {{-- Badge Indikator Belum Lengkap / Lengkap --}}
                                                    <template x-if="!isQuestionComplete(question)">
                                                        <span
                                                            class="w-4 h-4 rounded-full bg-amber-500 text-white text-[10px] font-black inline-flex items-center justify-center"
                                                            title="Soal ini belum lengkap">!</span>
                                                    </template>
                                                    <template x-if="isQuestionComplete(question)">
                                                        <span
                                                            class="w-4 h-4 rounded-full bg-emerald-500 text-white text-[10px] font-black inline-flex items-center justify-center"
                                                            title="Soal sudah lengkap">✓</span>
                                                    </template>
                                                </div>

                                                <span x-show="questions.length > 2" @click.stop="removeQuestion(index)"
                                                    class="text-xs hover:underline"
                                                    :class="selectedQuestion === index ?
                                                        'text-white' :
                                                        'text-red-500'">
                                                    Hapus
                                                </span>

                                            </div>

                                            <div class="text-[11px] mt-1 opacity-80"
                                                x-text="'Bobot: ' + question.bobot + ' Poin'"></div>

                                        </button>

                                    </template>

                                </div>

                            </div>

                        </div>


                        {{-- ================================================= --}}
                        {{-- EDITOR SOAL --}}
                        {{-- ================================================= --}}
                        <div class="lg:col-span-9 xl:col-span-10 min-w-0">

                            <template x-for="(question, index) in questions" :key="question.id">

                                <div x-show="selectedQuestion === index"
                                    class="bg-white border border-gray-200 rounded-2xl p-6">

                                    {{-- HEADER EDITOR --}}
                                    <div class="flex items-center justify-between mb-6">

                                        <div>

                                            <span class="text-sm text-gray-400">
                                                Soal Nomor
                                            </span>

                                            <h3 class="text-xl font-bold text-gray-800">
                                                <span x-text="index + 1"></span>
                                            </h3>

                                        </div>

                                        <div
                                            class="text-sm text-gray-500 bg-teal-50 border border-teal-200 px-3.5 py-1.5 rounded-xl">
                                            Bobot soal:
                                            <span class="font-bold text-teal-800"
                                                x-text="question.bobot + ' Poin'"></span>
                                        </div>

                                    </div>


                                    {{-- ================================================= --}}
                                    {{-- TIPE SOAL --}}
                                    {{-- ================================================= --}}
                                    <div class="mb-5">

                                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                                            Tipe Soal
                                        </label>

                                        <select :name="`soals[${index}][tipe_soal]`" x-model="question.tipe_soal"
                                            @change="
                                                question.kunci_jawaban =
                                                    question.tipe_soal === 'multiple_choice'
                                                        ? []
                                                        : '';
                                            "
                                            class="w-full border border-gray-300 rounded-xl px-4 py-3
                                                   focus:ring-2 focus:ring-teal-500
                                                   focus:border-teal-500">

                                            <option value="single_choice">
                                                Pilihan Ganda
                                            </option>

                                            <option value="multiple_choice">
                                                Pilihan Ganda Kompleks
                                            </option>

                                            <option value="essay">
                                                Essay
                                            </option>

                                            <option value="matching">
                                                Menjodohkan
                                            </option>

                                        </select>

                                    </div>


                                    {{-- ================================================= --}}
                                    {{-- PERTANYAAN --}}
                                    {{-- ================================================= --}}
                                    <div class="mb-5">

                                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                                            Pertanyaan <span class="text-red-500">*</span>
                                        </label>

                                        <textarea :name="`soals[${index}][pertanyaan]`" :id="`textarea-pertanyaan-${index}`" x-model="question.pertanyaan"
                                            rows="5" maxlength="500" placeholder="Tulis pertanyaan soal..."
                                            class="w-full border border-gray-300 rounded-xl px-4 py-3
                                               focus:ring-2 focus:ring-teal-500
                                               focus:border-teal-500"></textarea>

                                        <p class="text-xs text-gray-400 mt-1">
                                            Maksimal 500 karakter.
                                        </p>

                                    </div>


                                    {{-- ================================================= --}}
                                    {{-- GAMBAR SOAL (VALIDASI FORMAT & UKURAN KETAT) --}}
                                    {{-- ================================================= --}}
                                    <div class="mb-5">

                                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                                            Gambar Soal
                                            <span class="text-gray-400 font-normal">
                                                (opsional)
                                            </span>
                                        </label>

                                        <input type="file" :name="`soals[${index}][gambar]`"
                                            :id="`input-gambar-${question.id}`" @change="handleImageUpload($event, index)"
                                            accept=".jpg,.jpeg,.png,.webp,.gif,image/jpeg,image/png,image/webp,image/gif"
                                            class="w-full border border-gray-300 rounded-xl px-4 py-3
                                                   file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0
                                                   file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700
                                                   hover:file:bg-teal-100 cursor-pointer">

                                        <p class="text-xs text-gray-400 mt-1">
                                            Format JPG, PNG, WEBP, atau GIF. Maksimal 2 MB.
                                        </p>

                                        {{-- Pesan Error Validasi File Gambar --}}
                                        <template x-if="question.error_gambar">
                                            <p class="text-xs font-semibold text-red-600 mt-1.5 flex items-center gap-1"
                                                x-text="question.error_gambar"></p>
                                        </template>

                                        {{-- PREVIEW GAMBAR LANGSUNG --}}
                                        <template x-if="question.preview_gambar">
                                            <div class="mt-3 relative inline-block">
                                                <div class="border border-gray-200 rounded-xl p-2 bg-gray-50 shadow-xs">
                                                    <img :src="question.preview_gambar" alt="Preview Gambar Soal"
                                                        class="max-h-56 max-w-full rounded-lg object-contain block">
                                                </div>

                                                <div class="mt-2 flex items-center gap-2">
                                                    <span class="text-xs text-emerald-700 font-medium"
                                                        x-text="question.nama_file_gambar"></span>
                                                    <button type="button" @click="removeImage(index, question.id)"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-xs font-semibold transition border border-red-200 cursor-pointer"
                                                        title="Batalkan / Hapus gambar">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                            stroke-width="2" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                            </path>
                                                        </svg>
                                                        Hapus Gambar
                                                    </button>
                                                </div>
                                            </div>
                                        </template>

                                    </div>


                                    {{-- ================================================= --}}
                                    {{-- PILIHAN GANDA --}}
                                    {{-- ================================================= --}}
                                    <div x-show="
                                        question.tipe_soal === 'single_choice'
                                        || question.tipe_soal === 'multiple_choice'
                                    "
                                        class="mb-5">

                                        <div class="flex items-center justify-between mb-3">

                                            <label class="block text-sm font-semibold text-gray-700">
                                                Pilihan Jawaban <span class="text-red-500">*</span>
                                            </label>

                                            <button type="button" @click="addOption(index)"
                                                x-show="question.pilihan.length < 6"
                                                class="text-sm text-teal-700 hover:text-teal-900 font-semibold cursor-pointer">
                                                + Tambah Pilihan
                                            </button>

                                        </div>


                                        <div class="space-y-3">

                                            <template x-for="(option, optionIndex) in question.pilihan"
                                                :key="optionIndex">

                                                <div class="flex items-center gap-3">

                                                    {{-- RADIO --}}
                                                    <template x-if="question.tipe_soal === 'single_choice'">

                                                        <input type="radio" :name="`soals[${index}][kunci_jawaban]`"
                                                            :value="String(optionIndex)" x-model="question.kunci_jawaban"
                                                            class="w-4 h-4 text-teal-700 cursor-pointer">

                                                    </template>


                                                    {{-- CHECKBOX PILIHAN GANDA KOMPLEKS --}}
                                                    <template x-if="question.tipe_soal === 'multiple_choice'">
                                                        <input type="checkbox" :name="`soals[${index}][kunci_jawaban][]`"
                                                            :value="String(optionIndex)" x-model="question.kunci_jawaban"
                                                            class="w-4 h-4 text-teal-700 cursor-pointer">
                                                    </template>


                                                    {{-- TEKS PILIHAN --}}
                                                    <input type="text"
                                                        :name="`soals[${index}][pilihan][${optionIndex}]`"
                                                        x-model="question.pilihan[optionIndex]"
                                                        :placeholder="`Pilihan ${String.fromCharCode(65 + optionIndex)}`"
                                                        maxlength="200"
                                                        class="flex-1 min-w-0 border border-gray-300 rounded-xl px-4 py-3
                                                           focus:ring-2 focus:ring-teal-500
                                                           focus:border-teal-500">


                                                    {{-- HAPUS PILIHAN --}}
                                                    <button type="button" @click="removeOption(index, optionIndex)"
                                                        class="px-3 py-2 text-red-500 hover:bg-red-50
                                                           rounded-lg text-lg font-bold cursor-pointer"
                                                        title="Hapus pilihan">
                                                        ×
                                                    </button>

                                                </div>

                                            </template>

                                        </div>

                                        <p class="text-xs text-gray-400 mt-2">
                                            Pilih kunci jawaban yang benar menggunakan tombol di sebelah kiri.
                                        </p>

                                    </div>


                                    {{-- ================================================= --}}
                                    {{-- ESSAY & RUBRIK ESSAY (OPSIONAL) --}}
                                    {{-- ================================================= --}}
                                    <div x-show="question.tipe_soal === 'essay'" class="mb-5 space-y-4">

                                        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
                                            <p class="text-sm text-blue-700">
                                                Soal essay tidak menggunakan pilihan jawaban. Jawaban siswa akan dikoreksi
                                                oleh guru menggunakan pedoman/rubrik di bawah ini.
                                            </p>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                                Rubrik / Kunci Jawaban Essay
                                                <span class="text-gray-400 font-normal">(opsional — boleh
                                                    dikosongkan)</span>
                                            </label>

                                            <textarea :name="question.tipe_soal === 'essay' ? `soals[${index}][kunci_jawaban]` : null"
                                                x-model="question.rubrik_essay" rows="4" maxlength="1000"
                                                placeholder="Tuliskan kata kunci jawaban, langkah penyelesaian, atau pedoman penskoran untuk memudahkan saat mengoreksi essay..."
                                                class="w-full border border-gray-300 rounded-xl px-4 py-3
                                                       focus:ring-2 focus:ring-teal-500 focus:border-teal-500"></textarea>

                                            <p class="text-xs text-gray-400 mt-1">
                                                Rubrik ini hanya tampil di halaman koreksi guru sebagai acuan pemberian skor
                                                dan tidak terlihat oleh siswa saat ujian.
                                            </p>
                                        </div>

                                    </div>


                                    {{-- ================================================= --}}
                                    {{-- MATCHING --}}
                                    {{-- ================================================= --}}
                                    <div x-show="question.tipe_soal === 'matching'" class="mb-5">

                                        <div class="bg-purple-50 border border-purple-100 rounded-xl p-4">

                                            <p class="text-sm text-purple-700">
                                                Tipe menjodohkan dapat menggunakan data pasangan
                                                pada bagian pilihan jawaban.
                                            </p>

                                        </div>

                                    </div>


                                    {{-- ================================================= --}}
                                    {{-- TINGKAT KESULITAN & BOBOT OTOMATIS --}}
                                    {{-- ================================================= --}}
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                                        {{-- TINGKAT KESULITAN --}}
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                                Tingkat Kesulitan
                                            </label>

                                            <select :name="`soals[${index}][kesulitan]`" x-model.number="question.kesulitan"
                                                @change="hitungBobotOtomatis()"
                                                class="w-full border border-gray-300 rounded-xl px-4 py-3
                                                       focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                                                <option value="0">Tidak Valid / Dianulir (Poin: 0)</option>
                                                <option value="1">Mudah (Poin: 1)</option>
                                                <option value="2">Menengah (Poin: 2)</option>
                                                <option value="3">Susah (Poin: 3)</option>
                                            </select>

                                            <p class="text-xs text-gray-400 mt-1">
                                                Bobot soal akan dihitung otomatis proporsional.
                                            </p>
                                        </div>

                                        {{-- BOBOT HASIL HITUNG (READ-ONLY) --}}
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                                Bobot Poin (Otomatis)
                                            </label>

                                            <div class="relative">
                                                <input type="number" step="0.01" :name="`soals[${index}][bobot]`"
                                                    x-model="question.bobot" readonly
                                                    class="w-full bg-gray-100 border border-gray-300 rounded-xl px-4 py-3 pr-14 font-bold text-gray-800 cursor-not-allowed">
                                                <span class="absolute right-4 top-3 text-sm font-semibold text-gray-400">
                                                    Poin
                                                </span>
                                            </div>

                                            <p class="text-xs text-teal-600 font-medium mt-1">
                                                ✓ Dihitung otomatis agar total tepat 100.
                                            </p>
                                        </div>

                                    </div>


                                    {{-- URUTAN --}}
                                    <input type="hidden" :name="`soals[${index}][urutan]`" :value="index + 1">

                                </div>

                            </template>

                        </div>

                    </div>

                    {{-- RINGKASAN TOTAL BOBOT --}}
                    <div class="mt-6 p-4 rounded-xl border"
                        :class="totalBobot > 100 ?
                            'bg-red-50 border-red-200' :
                            'bg-teal-50 border-teal-200'">

                        <div class="flex justify-between items-center gap-3">

                            <span class="text-sm font-semibold text-gray-700">
                                Total Bobot Seluruh Soal
                            </span>

                            <span class="font-bold text-lg" :class="totalBobot > 100 ? 'text-red-600' : 'text-teal-700'"
                                x-text="totalBobot + ' / 100'"></span>

                        </div>

                        <p class="text-xs mt-1" :class="totalBobot > 100 ? 'text-red-600' : 'text-gray-500'"
                            x-text="totalBobot > 100
                                ? 'Total bobot melebihi batas maksimal 100 poin!'
                                : 'Total bobot soal terbagi proporsional menjadi tepat 100 poin.'">
                        </p>

                    </div>


                    {{-- ================================================= --}}
                    {{-- TOMBOL AKSI UJIAN --}}
                    {{-- ================================================= --}}
                    <div
                        class="w-full flex flex-row flex-wrap justify-end items-center
                           gap-3 mt-6 pt-6 border-t border-gray-100">

                        <button type="submit" name="action" value="draft" @click="submitAction = 'draft'"
                            class="px-5 py-3 rounded-xl border border-gray-300
                               bg-white hover:bg-gray-50 text-gray-700
                               font-semibold transition cursor-pointer">
                            Simpan Draft Ujian
                        </button>


                        <button type="submit" name="action" value="schedule" @click="submitAction = 'schedule'"
                            class="px-5 py-3 rounded-xl bg-yellow-500
                               hover:bg-yellow-600 text-white
                               font-semibold transition cursor-pointer">
                            Jadwalkan Ujian
                        </button>


                        <button type="submit" name="action" value="publish" @click="submitAction = 'publish'"
                            class="px-5 py-3 rounded-xl bg-teal-700
                               hover:bg-teal-800 text-white
                               font-semibold transition cursor-pointer">
                            Publikasi Ujian Sekarang
                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>


    {{-- ============================================================= --}}
    {{-- JAVASCRIPT --}}
    {{-- ============================================================= --}}

    <script>
        function ujianForm() {

            return {
                submitAction: 'draft',

                selectedQuestion: (() => {
                    const errorKeys = @js(array_keys($errors->getMessages()));
                    const firstSoalError = @js($errors->first('soals'));

                    const fieldError = errorKeys.find(key =>
                        /^soals\.\d+/.test(key)
                    );

                    if (fieldError) {
                        const match = fieldError.match(/^soals\.(\d+)/);
                        if (match) {
                            return Number(match[1]);
                        }
                    }

                    const messageMatch = String(firstSoalError || '').match(
                        /soal(?: pilihan tunggal)? nomor\s+(\d+)/i
                    );

                    if (messageMatch) {
                        return Number(messageMatch[1]) - 1;
                    }

                    return 0;
                })(),

                get totalBobot() {
                    const sum = this.questions.reduce((total, question) => {
                        const bobot = Number(question.bobot) || 0;
                        return total + bobot;
                    }, 0);
                    return Number(sum.toFixed(2));
                },

                // Cek kelengkapan 1 butir soal secara realtime untuk indikator di Daftar Soal
                isQuestionComplete(q) {
                    if (!q.pertanyaan || q.pertanyaan.trim() === '') return false;

                    if (q.tipe_soal === 'single_choice' || q.tipe_soal === 'multiple_choice') {
                        if (!q.pilihan || q.pilihan.length < 2) return false;
                        for (let p = 0; p < q.pilihan.length; p++) {
                            if (!q.pilihan[p] || String(q.pilihan[p]).trim() === '') return false;
                        }
                        const kunci = Array.isArray(q.kunci_jawaban) ?
                            q.kunci_jawaban :
                            (q.kunci_jawaban !== '' && q.kunci_jawaban !== null && q.kunci_jawaban !== undefined ? [q
                                .kunci_jawaban
                            ] : []);
                        if (kunci.length === 0) return false;
                    }

                    return true;
                },

                // =========================================================
                // VALIDASI LENGKAP DI CLIENT-SIDE SEBELUM SUBMIT
                // Mencegah reload ke server agar gambar & aktivitas tidak pernah hilang
                // =========================================================
                validateForm(event) {
                    const actionDipilih = event.submitter?.value || this.submitAction || 'draft';

                    // 1. Validasi Judul Ujian
                    const inputJudul = this.$refs.inputJudul;
                    if (!inputJudul || !inputJudul.value.trim()) {
                        event.preventDefault();
                        alert('Judul Ujian wajib diisi terlebih dahulu!');
                        inputJudul?.focus();
                        return;
                    }

                    // 2. Validasi Target Kelas (pengampu_kelas_id[])
                    const checkedKelas = document.querySelectorAll('input[name="pengampu_kelas_id[]"]:checked');
                    if (checkedKelas.length === 0) {
                        event.preventDefault();
                        alert(
                            'Target Kelas belum dipilih!\nSilakan pilih Mata Pelajaran, Tingkatan, dan centang minimal 1 Kelas terlebih dahulu.');
                        document.getElementById('section-target-kelas')?.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        return;
                    }

                    // 3. Validasi Durasi & KKM
                    const durasiVal = Number(this.$refs.inputDurasi?.value || 0);
                    if (!durasiVal || durasiVal < 1 || durasiVal > 999) {
                        event.preventDefault();
                        alert('Durasi Ujian harus diisi antara 1 sampai 999 menit!');
                        this.$refs.inputDurasi?.focus();
                        return;
                    }

                    const kkmRaw = this.$refs.inputKkm?.value;
                    if (kkmRaw === '' || Number(kkmRaw) < 0 || Number(kkmRaw) > 99) {
                        event.preventDefault();
                        alert('Nilai KKM harus diisi antara 0 sampai 99!');
                        this.$refs.inputKkm?.focus();
                        return;
                    }

                    // 4. Validasi Jadwal (Waktu Mulai & Deadline) saat klik Jadwalkan atau jika salah satu diisi
                    const waktuMulaiVal = this.$refs.inputWaktuMulai?.value || '';
                    const deadlineVal = this.$refs.inputDeadline?.value || '';

                    if (actionDipilih === 'schedule') {
                        if (!waktuMulaiVal) {
                            event.preventDefault();
                            alert('Untuk menjadwalkan ujian, kolom "Waktu Mulai" wajib diisi!');
                            this.$refs.inputWaktuMulai?.focus();
                            return;
                        }
                        if (!deadlineVal) {
                            event.preventDefault();
                            alert('Untuk menjadwalkan ujian, kolom "Deadline" wajib diisi!');
                            this.$refs.inputDeadline?.focus();
                            return;
                        }
                    }

                    if (waktuMulaiVal && deadlineVal) {
                        if (new Date(deadlineVal) <= new Date(waktuMulaiVal)) {
                            event.preventDefault();
                            alert('Waktu Deadline harus lebih akhir daripada Waktu Mulai!');
                            this.$refs.inputDeadline?.focus();
                            return;
                        }
                    }

                    // 5. Validasi Minimal 2 Soal
                    if (this.questions.length < 2) {
                        event.preventDefault();
                        alert(
                            `Ujian minimal harus memiliki 2 soal!\nSaat ini baru terdapat ${this.questions.length} soal. Silakan klik tombol "+ Tambah Soal".`);
                        document.getElementById('section-editor-soal')?.scrollIntoView({
                            behavior: 'smooth'
                        });
                        return;
                    }

                    const adaSoalValid = this.questions.some(q => Number(q.kesulitan) > 0 && Number(q.bobot) > 0);
                    if (!adaSoalValid || this.totalBobot <= 0) {
                        event.preventDefault();
                        alert('Minimal harus ada 1 soal yang valid (bukan berstatus "Tidak Valid")!');
                        return;
                    }


                    // 6. Validasi Total Bobot Soal
                    if (this.totalBobot > 100.5) {
                        event.preventDefault();
                        alert(
                            'Total bobot soal melebihi 100 poin!\n' +
                            'Total saat ini: ' + this.totalBobot + ' poin.'
                        );
                        return;
                    }

                    // 7. Validasi Kelengkapan Setiap Butir Soal (Otomatis pindah ke soal yang belum lengkap)
                    for (let i = 0; i < this.questions.length; i++) {
                        const q = this.questions[i];
                        const nomor = i + 1;

                        if (!q.pertanyaan || q.pertanyaan.trim() === '') {
                            event.preventDefault();
                            this.selectedQuestion = i;
                            alert(
                                `Pertanyaan pada Soal nomor ${nomor} belum diisi!\nLayar telah diarahkan ke Soal nomor ${nomor}.`);
                            this.$nextTick(() => {
                                document.getElementById(`textarea-pertanyaan-${i}`)?.focus();
                            });
                            return;
                        }

                        if (q.tipe_soal === 'single_choice' || q.tipe_soal === 'multiple_choice') {
                            if (!q.pilihan || q.pilihan.length < 2) {
                                event.preventDefault();
                                this.selectedQuestion = i;
                                alert(`Soal nomor ${nomor} minimal harus memiliki 2 pilihan jawaban!`);
                                return;
                            }

                            for (let p = 0; p < q.pilihan.length; p++) {
                                if (!q.pilihan[p] || String(q.pilihan[p]).trim() === '') {
                                    event.preventDefault();
                                    this.selectedQuestion = i;
                                    const huruf = String.fromCharCode(65 + p);
                                    alert(
                                        `Pilihan ${huruf} pada Soal nomor ${nomor} belum diisi!\nLayar telah diarahkan ke Soal nomor ${nomor}.`);
                                    return;
                                }
                            }

                            const kunci = Array.isArray(q.kunci_jawaban) ?
                                q.kunci_jawaban :
                                (q.kunci_jawaban !== '' && q.kunci_jawaban !== null && q.kunci_jawaban !== undefined ? [q
                                    .kunci_jawaban
                                ] : []);

                            if (kunci.length === 0) {
                                event.preventDefault();
                                this.selectedQuestion = i;
                                alert(
                                    `Kunci jawaban pada Soal nomor ${nomor} belum dipilih!\nSilakan centang/pilih jawaban yang benar pada Soal nomor ${nomor}.`);
                                return;
                            }
                        }
                    }
                },

                // Default dimulai dengan 2 soal agar langsung memenuhi syarat minimal 2 soal
                questions: (() => {
                    const oldQuestions = @js(old('soals', []));

                    if (!Array.isArray(oldQuestions) || oldQuestions.length === 0) {
                        return [{
                                id: Date.now() + 1,
                                tipe_soal: 'single_choice',
                                pertanyaan: '',
                                pilihan: ['', '', '', ''],
                                kunci_jawaban: '',
                                rubrik_essay: '',
                                kesulitan: 1,
                                bobot: 50,
                                preview_gambar: null,
                                nama_file_gambar: '',
                                error_gambar: ''
                            },
                            {
                                id: Date.now() + 2,
                                tipe_soal: 'single_choice',
                                pertanyaan: '',
                                pilihan: ['', '', '', ''],
                                kunci_jawaban: '',
                                rubrik_essay: '',
                                kesulitan: 1,
                                bobot: 50,
                                preview_gambar: null,
                                nama_file_gambar: '',
                                error_gambar: ''
                            }
                        ];
                    }

                    return oldQuestions.map((question, index) => ({
                        id: Date.now() + index + Math.random(),
                        tipe_soal: question.tipe_soal || 'single_choice',
                        pertanyaan: question.pertanyaan || '',
                        pilihan: Array.isArray(question.pilihan) ?
                            question.pilihan : ['', '', '', ''],
                        kunci_jawaban: question.kunci_jawaban ?? '',
                        rubrik_essay: question.tipe_soal === 'essay' ?
                            (Array.isArray(question.kunci_jawaban) ? (question.kunci_jawaban[0] || '') :
                                (question.kunci_jawaban || '')) :
                            '',
                        kesulitan: Number(question.kesulitan) || 1,
                        bobot: Number(question.bobot) || 50,
                        preview_gambar: null,
                        nama_file_gambar: '',
                        error_gambar: ''
                    }));
                })(),

                init() {
                    this.hitungBobotOtomatis();
                },

                // =========================================================
                // TAMBAH SOAL (OTOMATIS REKALKULASI BOBOT SEPERTI DI EDIT)
                // =========================================================
                addQuestion() {
                    if (this.questions.length >= 100) {
                        alert('Maksimal 100 soal dalam satu ujian.');
                        return;
                    }

                    this.questions.push({
                        id: Date.now() + Math.random(),
                        tipe_soal: 'single_choice',
                        pertanyaan: '',
                        pilihan: ['', '', '', ''],
                        kunci_jawaban: '',
                        rubrik_essay: '',
                        kesulitan: 1,
                        bobot: 0,
                        preview_gambar: null,
                        nama_file_gambar: '',
                        error_gambar: ''
                    });

                    this.selectedQuestion = this.questions.length - 1;
                    this.hitungBobotOtomatis();
                },

                // Cek apakah soal sudah memiliki isian sebelum dihapus
                isQuestionFilled(q) {
                    if (q.pertanyaan && q.pertanyaan.trim() !== '') return true;
                    if (q.preview_gambar) return true;
                    if (q.rubrik_essay && q.rubrik_essay.trim() !== '') return true;

                    if (q.tipe_soal === 'single_choice' || q.tipe_soal === 'multiple_choice') {
                        if (Array.isArray(q.pilihan) && q.pilihan.some(p => String(p || '').trim() !== '')) {
                            return true;
                        }
                        const hasKunci = Array.isArray(q.kunci_jawaban) ?
                            q.kunci_jawaban.length > 0 :
                            (q.kunci_jawaban !== '' && q.kunci_jawaban !== null && q.kunci_jawaban !== undefined);
                        if (hasKunci) return true;
                    }

                    return false;
                },

                // =========================================================
                // HAPUS SOAL (DENGAN KONFIRMASI JIKA SOAL SUDAH TERISI)
                // =========================================================
                removeQuestion(index) {
                    if (this.questions.length <= 2) {
                        alert('Minimal harus ada 2 soal dalam satu ujian.');
                        return;
                    }

                    const targetQuestion = this.questions[index];
                    if (this.isQuestionFilled(targetQuestion)) {
                        if (!confirm(
                                `Soal nomor ${index + 1} sudah memiliki isian. Apakah Anda yakin ingin menghapus soal ini?`
                                )) {
                            return;
                        }
                    }

                    if (targetQuestion?.preview_gambar) {
                        URL.revokeObjectURL(targetQuestion.preview_gambar);
                    }

                    this.questions.splice(index, 1);

                    if (this.selectedQuestion >= this.questions.length) {
                        this.selectedQuestion = this.questions.length - 1;
                    }

                    this.hitungBobotOtomatis();
                },

                // =========================================================
                // HITUNG BOBOT OTOMATIS BERDASARKAN TINGKAT KESULITAN (TOTAL 100)
                // =========================================================
                hitungBobotOtomatis() {
                    const totalSoal = this.questions.length;
                    if (totalSoal === 0) return;

                    const totalPoinKesulitan = this.questions.reduce((sum, q) => {
                        const k = q.kesulitan !== undefined && q.kesulitan !== null && q.kesulitan !== ''
                            ? Number(q.kesulitan)
                            : 1;
                        return sum + Math.max(0, k);
                    }, 0);

                    if (totalPoinKesulitan <= 0) {
                        this.questions.forEach(q => {
                            q.bobot = 0;
                        });
                        return;
                    }

                    // Cari indeks soal VALID terakhir (kesulitan > 0) untuk menampung sisa pembulatan 100.00
                    let lastValidIndex = -1;
                    this.questions.forEach((q, idx) => {
                        const k = q.kesulitan !== undefined && q.kesulitan !== null && q.kesulitan !== ''
                            ? Number(q.kesulitan)
                            : 1;
                        if (k > 0) lastValidIndex = idx;
                    });

                    let totalBobotTerbagi = 0;

                    this.questions.forEach((question, index) => {
                        const kesulitan = question.kesulitan !== undefined && question.kesulitan !== null && question.kesulitan !== ''
                            ? Number(question.kesulitan)
                            : 1;

                        if (kesulitan <= 0) {
                            question.bobot = 0;
                        } else if (index === lastValidIndex) {
                            question.bobot = Number((100 - totalBobotTerbagi).toFixed(2));
                        } else {
                            const porsi = Number(((kesulitan / totalPoinKesulitan) * 100).toFixed(2));
                            question.bobot = porsi;
                            totalBobotTerbagi += porsi;
                        }
                    });
                },
                // =========================================================
                // VALIDASI FILE GAMBAR (HANYA JPG, PNG, WEBP, GIF & MAKS 2MB)
                // =========================================================
                handleImageUpload(event, index) {
                    const file = event.target.files[0];
                    this.questions[index].error_gambar = '';

                    if (!file) {
                        this.questions[index].preview_gambar = null;
                        this.questions[index].nama_file_gambar = '';
                        return;
                    }

                    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                    const allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                    const fileExtension = (file.name.split('.').pop() || '').toLowerCase();

                    if (!allowedTypes.includes(file.type) || !allowedExtensions.includes(fileExtension)) {
                        const msg = 'Format file tidak didukung! Hanya diperbolehkan JPG, PNG, WEBP, atau GIF.';
                        this.questions[index].error_gambar = msg;
                        alert(msg);
                        event.target.value = '';
                        this.questions[index].preview_gambar = null;
                        this.questions[index].nama_file_gambar = '';
                        return;
                    }

                    const maxSizeBytes = 2 * 1024 * 1024; // 2 MB
                    if (file.size > maxSizeBytes) {
                        const ukuranMb = (file.size / (1024 * 1024)).toFixed(2);
                        const msg =
                            `Ukuran gambar terlalu besar (${ukuranMb} MB)! Maksimal ukuran yang diperbolehkan adalah 2 MB.`;
                        this.questions[index].error_gambar = msg;
                        alert(msg);
                        event.target.value = '';
                        this.questions[index].preview_gambar = null;
                        this.questions[index].nama_file_gambar = '';
                        return;
                    }

                    if (this.questions[index].preview_gambar) {
                        URL.revokeObjectURL(this.questions[index].preview_gambar);
                    }

                    this.questions[index].preview_gambar = URL.createObjectURL(file);
                    this.questions[index].nama_file_gambar = `${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
                },

                removeImage(index, questionId) {
                    if (this.questions[index].preview_gambar) {
                        URL.revokeObjectURL(this.questions[index].preview_gambar);
                    }
                    this.questions[index].preview_gambar = null;
                    this.questions[index].nama_file_gambar = '';
                    this.questions[index].error_gambar = '';
                    const input = document.getElementById('input-gambar-' + questionId);
                    if (input) {
                        input.value = '';
                    }
                },

                // =========================================================
                // TAMBAH & HAPUS PILIHAN JAWABAN
                // =========================================================
                removeOption(questionIndex, optionIndex) {
                    const question = this.questions[questionIndex];
                    if (question.pilihan.length <= 2) {
                        alert('Minimal harus ada 2 pilihan jawaban.');
                        return;
                    }
                    question.pilihan.splice(optionIndex, 1);

                    if (Array.isArray(question.kunci_jawaban)) {
                        question.kunci_jawaban = question.kunci_jawaban
                            .filter(index => Number(index) !== Number(optionIndex))
                            .map(index => String(Number(index) > Number(optionIndex) ? Number(index) - 1 : Number(index)));
                    } else if (question.kunci_jawaban !== '' && question.kunci_jawaban !== null) {
                        if (Number(question.kunci_jawaban) === Number(optionIndex)) {
                            question.kunci_jawaban = '';
                        } else if (Number(question.kunci_jawaban) > Number(optionIndex)) {
                            question.kunci_jawaban = String(Number(question.kunci_jawaban) - 1);
                        }
                    }
                },

                addOption(questionIndex) {
                    const question = this.questions[questionIndex];
                    if (question.pilihan.length >= 6) {
                        alert('Maksimal 6 pilihan jawaban, yaitu A sampai F.');
                        return;
                    }
                    question.pilihan.push('');
                }

            };

        }

        function targetKelasForm() {
            return {
                mapelList: @js($dataMapel),

                selectedMapel: @js(old('mapel_id', '')),

                selectedTingkatan: '',

                kelasDipilih: @js(array_map('strval', (array) old('pengampu_kelas_id', []))),

                get mapelAktif() {
                    return this.mapelList.find(
                        mapel => String(mapel.id) === String(this.selectedMapel)
                    ) || null;
                },

                get daftarTingkatan() {
                    return this.mapelAktif?.tingkatan || [];
                },

                get tingkatanAktif() {
                    return this.daftarTingkatan.find(
                        tingkat => tingkat.nama === this.selectedTingkatan
                    ) || null;
                },

                get kelasTerlihat() {
                    return this.tingkatanAktif?.kelas || [];
                },

                init() {
                    if (this.selectedMapel && this.kelasDipilih.length > 0) {
                        const mapel = this.mapelAktif;

                        if (!mapel) {
                            this.selectedMapel = '';
                            this.kelasDipilih = [];
                            return;
                        }

                        const tingkat = mapel.tingkatan.find(item =>
                            item.kelas.some(kelas =>
                                this.kelasDipilih.includes(
                                    String(kelas.pengampu_kelas_id)
                                )
                            )
                        );

                        if (tingkat) {
                            this.selectedTingkatan = tingkat.nama;

                            const idValid = tingkat.kelas.map(kelas =>
                                String(kelas.pengampu_kelas_id)
                            );

                            this.kelasDipilih = this.kelasDipilih.filter(id =>
                                idValid.includes(id)
                            );
                        } else {
                            this.kelasDipilih = [];
                        }
                    }
                },

                ubahMapel() {
                    this.selectedTingkatan = '';
                    this.kelasDipilih = [];
                },

                ubahTingkatan() {
                    this.kelasDipilih = [];
                }
            };
        }
    </script>

@endsection
