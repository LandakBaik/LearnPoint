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


            <form method="POST" action="{{ route('guru.ujian.store') }}" enctype="multipart/form-data" id="ujianForm"
                @submit="validateForm($event)">
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

                        <input type="text" name="judul" value="{{ old('judul') }}" maxlength="100"
                            pattern="[\p{L}\p{N} ]+" title="Judul hanya boleh berisi huruf, angka, dan spasi." required
                            placeholder="Contoh: Ujian Tengah Semester IPA Kelas VIII"
                            oninput="this.value = this.value.replace(/[^\p{L}\p{N} ]/gu, '')"
                            class="w-full border border-gray-300 rounded-xl px-4 py-3
                                 focus:ring-2 focus:ring-teal-500
                                 focus:border-teal-500">

                        <p class="text-xs text-gray-400 mt-1">
                            Maksimal 100 karakter.
                        </p>

                    </div>



                    {{-- MAPEL + TINGKATAN + KELAS --}}
                    <div class="md:col-span-2" x-data="targetKelasForm()">

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

                                <select name="mapel_id" x-model="selectedMapel" @change="ubahMapel()" required
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
                                                :value="kelas.pengampu_kelas_id" x-model="kelasDipilih"
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

                            <input type="text" name="durasi_menit" value="{{ old('durasi_menit', 60) }}"
                                inputmode="numeric" maxlength="3" pattern="[1-9][0-9]{0,2}"
                                title="Durasi harus berupa angka bulat dari 1 sampai 999." required
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

                        <input type="text" name="kkm" value="{{ old('kkm', 75) }}" inputmode="numeric"
                            maxlength="2" pattern="[0-9]{1,2}" title="KKM harus berupa angka bulat dari 0 sampai 99."
                            required
                            oninput="
                             this.value = this.value.replace(/[^0-9]/g, '').slice(0, 2);
                             "
                            class="w-full border border-gray-300 rounded-xl px-4 py-3
                         focus:ring-2 focus:ring-teal-500
                         focus:border-teal-500">

                        <p class="text-xs text-gray-400 mt-1">
                            Nilai KKM 0–99.
                        </p>

                    </div>


                    {{-- WAKTU MULAI --}}
                    <div>

                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Waktu Mulai
                            <span class="text-gray-400 font-normal">
                                (opsional untuk draft)
                            </span>
                        </label>

                        <input type="datetime-local" name="waktu_mulai" value="{{ old('waktu_mulai') }}"
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
                                (opsional untuk draft)
                            </span>
                        </label>

                        <input type="datetime-local" name="deadline" value="{{ old('deadline') }}"
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
                <div class="mt-8 border-t border-gray-100 pt-8">

                    {{-- HEADER SOAL --}}
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-5">

                        <div>

                            <h2 class="text-lg font-bold text-gray-800">
                                Soal Ujian
                            </h2>

                            <p class="text-sm text-gray-500 mt-1">
                                Tambahkan soal dan tentukan jenis jawabannya.
                            </p>

                        </div>


                        {{-- TAMBAH SOAL --}}
                        <button type="button" @click="addQuestion()"
                            class="inline-flex items-center justify-center gap-2
                               px-5 py-3 bg-teal-700 hover:bg-teal-800
                               text-white rounded-xl font-semibold transition">

                            <span class="text-lg">+</span>

                            Tambah Soal

                        </button>

                    </div>


                    {{-- ================================================= --}}
                    {{-- DAFTAR + EDITOR SOAL --}}
                    {{-- ================================================= --}}
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">


                        {{-- ================================================= --}}
                        {{-- DAFTAR SOAL --}}
                        {{-- ================================================= --}}
                        <div class="lg:col-span-2">

                            <div class="bg-gray-50 rounded-2xl border border-gray-200 p-4">

                                <h3 class="font-semibold text-gray-700 mb-3">
                                    Daftar Soal
                                </h3>

                                <div class="space-y-2">

                                    <template x-for="(question, index) in questions" :key="question.id">

                                        <button type="button" @click="selectedQuestion = index"
                                            class="w-full text-left px-4 py-3 rounded-xl border transition"
                                            :class="selectedQuestion === index ?
                                                'bg-teal-700 text-white border-teal-700' :
                                                'bg-white text-gray-700 border-gray-200 hover:border-teal-400'">

                                            <div class="flex items-center justify-between gap-2">

                                                <span class="font-semibold">
                                                    Soal
                                                    <span x-text="index + 1"></span>
                                                </span>

                                                <span x-show="questions.length > 1" @click.stop="removeQuestion(index)"
                                                    class="text-xs hover:underline"
                                                    :class="selectedQuestion === index ?
                                                        'text-white' :
                                                        'text-red-500'">
                                                    Hapus
                                                </span>

                                            </div>

                                        </button>

                                    </template>

                                </div>

                            </div>

                        </div>


                        {{-- ================================================= --}}
                        {{-- EDITOR SOAL --}}
                        {{-- ================================================= --}}
                        <div class="lg:col-span-10 min-w-0">

                            <template x-for="(question, index) in questions" :key="question.id">

                                <div x-show="selectedQuestion === index"
                                    class="bg-white border border-gray-200 rounded-2xl p-6">

                                    {{-- HEADER EDITOR --}}
                                    <div class="flex items-center justify-between mb-6">

                                        <div>

                                            <span class="text-sm text-gray-400">
                                                Soal
                                            </span>

                                            <h3 class="text-xl font-bold text-gray-800">
                                                <span x-text="index + 1"></span>
                                            </h3>

                                        </div>

                                        <div class="text-sm text-gray-500">
                                            Bobot soal:
                                            <span class="font-semibold text-gray-700" x-text="question.bobot"></span>
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
                                            Pertanyaan
                                        </label>

                                        <textarea :name="`soals[${index}][pertanyaan]`" x-model="question.pertanyaan" rows="5" maxlength="500"
                                            required placeholder="Tulis pertanyaan soal..."
                                            class="w-full border border-gray-300 rounded-xl px-4 py-3
                                               focus:ring-2 focus:ring-teal-500
                                               focus:border-teal-500"></textarea>

                                        <p class="text-xs text-gray-400 mt-1">
                                            Maksimal 500 karakter.
                                        </p>

                                    </div>


                                    {{-- ================================================= --}}
                                    {{-- GAMBAR --}}
                                    {{-- ================================================= --}}
                                    <div class="mb-5">

                                        <label class="block text-sm font-semibold text-gray-700 mb-2">

                                            Gambar Soal

                                            <span class="text-gray-400 font-normal">
                                                (opsional)
                                            </span>

                                        </label>

                                        <input type="file" :name="`soals[${index}][gambar]`"
                                            accept="image/png,image/jpeg,image/jpg,image/webp,image/gif"
                                            class="w-full border border-gray-300 rounded-xl px-4 py-3">

                                        <p class="text-xs text-gray-400 mt-1">
                                            Format JPG, PNG, WEBP, atau GIF. Maksimal 2 MB.
                                        </p>

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
                                                Pilihan Jawaban
                                            </label>

                                            <button type="button" @click="addOption(index)"
                                                x-show="question.pilihan.length < 6"
                                                class="text-sm text-teal-700 hover:text-teal-900 font-semibold">
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
                                                            :value="optionIndex" x-model="question.kunci_jawaban"
                                                            class="w-4 h-4 text-teal-700">

                                                    </template>


                                                    {{-- CHECKBOX PILIHAN GANDA KOMPLEKS --}}
                                                    <template x-if="question.tipe_soal === 'multiple_choice'">
                                                        <input type="checkbox" :name="`soals[${index}][kunci_jawaban][]`"
                                                            :value="optionIndex" x-model="question.kunci_jawaban"
                                                            class="w-4 h-4 text-teal-700">
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
                                                           rounded-lg text-lg font-bold"
                                                        title="Hapus pilihan">
                                                        ×
                                                    </button>

                                                </div>

                                            </template>

                                        </div>

                                        <p class="text-xs text-gray-400 mt-2">
                                            Pilih jawaban yang benar menggunakan tombol di sebelah kiri.
                                        </p>

                                    </div>


                                    {{-- ================================================= --}}
                                    {{-- ESSAY --}}
                                    {{-- ================================================= --}}
                                    <div x-show="question.tipe_soal === 'essay'" class="mb-5">

                                        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">

                                            <p class="text-sm text-blue-700">
                                                Soal essay tidak menggunakan pilihan jawaban.
                                                Jawaban siswa dapat diperiksa oleh guru.
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
                                    {{-- BOBOT --}}
                                    {{-- ================================================= --}}
                                    <div>

                                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                                            Bobot Soal
                                        </label>

                                        <input type="number" :name="`soals[${index}][bobot]`" x-model="question.bobot"
                                            min="0" max="99" maxlength="2" step="1"
                                            class="w-full md:w-48 border border-gray-300 rounded-xl
                                               px-4 py-3
                                               focus:ring-2 focus:ring-teal-500
                                               focus:border-teal-500">

                                        <p class="text-xs text-gray-400 mt-1">
                                            Maksimal 99 poin.
                                        </p>

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
            : 'Total bobot soal masih dalam batas yang diperbolehkan.'">
                        </p>

                    </div>


                    {{-- ================================================= --}}
                    {{-- TOMBOL AKSI UJIAN --}}
                    {{-- DI LUAR GRID EDITOR --}}
                    {{-- ================================================= --}}
                    <div
                        class="w-full flex flex-row flex-wrap justify-end items-center
                           gap-3 mt-6 pt-6 border-t border-gray-100">

                        <button type="submit" name="action" value="draft"
                            class="px-5 py-3 rounded-xl border border-gray-300
                               bg-white hover:bg-gray-50 text-gray-700
                               font-semibold transition">
                            Simpan Draft Ujian
                        </button>


                        <button type="submit" name="action" value="schedule"
                            class="px-5 py-3 rounded-xl bg-yellow-500
                               hover:bg-yellow-600 text-white
                               font-semibold transition">
                            Jadwalkan Ujian
                        </button>


                        <button type="submit" name="action" value="publish"
                            class="px-5 py-3 rounded-xl bg-teal-700
                               hover:bg-teal-800 text-white
                               font-semibold transition">
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

                selectedQuestion: (() => {
                    const errorKeys = @js(array_keys($errors->getMessages()));
                    const firstSoalError = @js($errors->first('soals'));

                    // Cari error validasi Laravel seperti soals.1.pertanyaan
                    const fieldError = errorKeys.find(key =>
                        /^soals\.\d+/.test(key)
                    );

                    if (fieldError) {
                        const match = fieldError.match(/^soals\.(\d+)/);

                        if (match) {
                            return Number(match[1]);
                        }
                    }

                    // Cari nomor soal dari pesan validasi khusus controller
                    const messageMatch = String(firstSoalError || '').match(
                        /soal(?: pilihan tunggal)? nomor\s+(\d+)/i
                    );

                    if (messageMatch) {
                        return Number(messageMatch[1]) - 1;
                    }

                    return 0;
                })(),

                // Menghitung total bobot semua soal.
                get totalBobot() {
                    return this.questions.reduce((total, question) => {
                        const bobot = Number(question.bobot) || 0;
                        return total + bobot;
                    }, 0);
                },

                // Memeriksa total bobot sebelum form dikirim.
                validateForm(event) {

                    if (this.totalBobot > 100) {
                        event.preventDefault();

                        alert(
                            'Total bobot soal melebihi 100 poin!\n' +
                            'Total bobot saat ini: ' + this.totalBobot + ' poin.\n' +
                            'Silakan kurangi bobot soal terlebih dahulu.'
                        );

                        return;
                    }
                },

                questions: (() => {
                    // Ambil kembali data soal yang dikirim Laravel
                    const oldQuestions = @js(old('soals', []));

                    // Jika sebelumnya belum ada data soal, buat satu soal kosong
                    if (!Array.isArray(oldQuestions) || oldQuestions.length === 0) {
                        return [{
                            id: Date.now(),
                            tipe_soal: 'single_choice',
                            pertanyaan: '',
                            pilihan: ['', '', '', ''],
                            kunci_jawaban: [],
                            bobot: 1
                        }];
                    }

                    // Pulihkan seluruh soal yang sebelumnya sudah diisi
                    return oldQuestions.map((question, index) => ({
                        id: Date.now() + index + Math.random(),

                        tipe_soal: question.tipe_soal || 'single_choice',

                        pertanyaan: question.pertanyaan || '',

                        pilihan: Array.isArray(question.pilihan) ?
                            question.pilihan : ['', '', '', ''],

                        kunci_jawaban: question.kunci_jawaban ?? '',

                        bobot: question.bobot ?? 1
                    }));
                })(),


                // =========================================================
                // TAMBAH SOAL
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

                        pilihan: [
                            '',
                            '',
                            '',
                            ''
                        ],

                        kunci_jawaban: [],

                        bobot: 1

                    });


                    this.selectedQuestion = this.questions.length - 1;
                },


                // =========================================================
                // HAPUS PILIHAN
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

                            .filter(index =>
                                Number(index) !== Number(optionIndex)
                            )

                            .map(index =>
                                Number(index) > Number(optionIndex) ?
                                Number(index) - 1 :
                                Number(index)
                            );
                    }

                },


                // =========================================================
                // HAPUS SOAL
                // =========================================================
                removeQuestion(index) {

                    if (this.questions.length <= 1) {

                        alert('Minimal harus ada satu soal.');

                        return;
                    }


                    this.questions.splice(index, 1);


                    if (this.selectedQuestion >= this.questions.length) {

                        this.selectedQuestion = this.questions.length - 1;
                    }

                },


                // =========================================================
                // TAMBAH PILIHAN
                // =========================================================
                addOption(questionIndex) {

                    const question = this.questions[questionIndex];


                    if (question.pilihan.length >= 6) {

                        alert('Maksimal 6 pilihan jawaban, yaitu A sampai F.');

                        return;
                    }


                    question.pilihan.push('');

                }

            }

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
                    // Pulihkan tingkatan jika validasi sebelumnya gagal.
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

                            // Hanya pertahankan kelas yang benar-benar cocok.
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
