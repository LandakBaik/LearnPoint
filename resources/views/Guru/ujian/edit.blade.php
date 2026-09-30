@extends('layouts.app')

@section('title', 'Edit Ujian')

@section('content')

<div
    class="max-w-7xl mx-auto"
    x-data="editUjian()"
>

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">

        <div>
            <a
                href="{{ route('guru.ujian.show', $ujian) }}"
                class="text-sm text-gray-500 hover:text-teal-600"
            >
                ← Kembali ke Detail Ujian
            </a>

            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mt-2">
                Edit Ujian
            </h1>

            <p class="text-gray-500 mt-1">
                {{ $ujian->judul }}
            </p>
        </div>

    </div>


    {{-- INFORMASI UJIAN --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">

        <h2 class="text-lg font-bold text-gray-800 mb-5">
            Pengaturan Ujian
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

            {{-- JUDUL --}}
            <div class="md:col-span-2">

                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Judul Ujian
                </label>

                <input
                    type="text"
                    name="judul"
                    form="form-edit-ujian"
                    value="{{ old('judul', $ujian->judul) }}"
                    required
                    class="w-full border border-gray-300 rounded-xl px-4 py-3
                           focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                >

                @error('judul')
                    <p class="text-red-500 text-sm mt-1">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            {{-- MAPEL + KELAS --}}
            <div>

                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Mata Pelajaran & Kelas
                </label>

                <select
                    name="pengampu_kelas_id"
                    form="form-edit-ujian"
                    required
                    class="w-full border border-gray-300 rounded-xl px-4 py-3
                           focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                >

                    <option value="">
                        Pilih mata pelajaran & kelas
                    </option>

                    @foreach($pengampuKelases as $pengampu)

                        <option
                            value="{{ $pengampu->id }}"
                            @selected(
                                old(
                                    'pengampu_kelas_id',
                                    $ujian->pengampu_kelas_id
                                ) == $pengampu->id
                            )
                        >
                            {{ $pengampu->guruMapel?->mapel?->nama_mapel ?? '-' }}
                            -
                            {{ $pengampu->kelas?->nama_kelas ?? '-' }}
                        </option>

                    @endforeach

                </select>

                @error('pengampu_kelas_id')
                    <p class="text-red-500 text-sm mt-1">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            {{-- DURASI --}}
            <div>

                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Durasi
                </label>

                <div class="relative">

                    <input
                        type="number"
                        name="durasi_menit"
                        form="form-edit-ujian"
                        min="1"
                        max="999"
                        maxlength="3"
                        value="{{ old('durasi_menit', $ujian->durasi_menit) }}"
                        required
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 pr-20
                               focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                    >

                    <span class="absolute right-4 top-3 text-gray-400">
                        menit
                    </span>

                </div>

            </div>


            {{-- KKM --}}
            <div>

                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    KKM
                </label>

                <input
                    type="number"
                    name="kkm"
                    form="form-edit-ujian"
                    min="0"
                    max="99"
                    step="0.01"
                    value="{{ old('kkm', $ujian->kkm) }}"
                    required
                    class="w-full border border-gray-300 rounded-xl px-4 py-3
                           focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                >

            </div>


            {{-- WAKTU MULAI --}}
            <div>

                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Waktu Mulai
                </label>

                <input
                    type="datetime-local"
                    name="waktu_mulai"
                    form="form-edit-ujian"
                    value="{{ old(
                        'waktu_mulai',
                        $ujian->waktu_mulai?->format('Y-m-d\TH:i')
                    ) }}"
                    class="w-full border border-gray-300 rounded-xl px-4 py-3
                           focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                >

            </div>


            {{-- DEADLINE --}}
            <div>

                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Deadline
                </label>

                <input
                    type="datetime-local"
                    name="deadline"
                    form="form-edit-ujian"
                    value="{{ old(
                        'deadline',
                        $ujian->deadline?->format('Y-m-d\TH:i')
                    ) }}"
                    class="w-full border border-gray-300 rounded-xl px-4 py-3
                           focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                >

            </div>


            {{-- KISI-KISI --}}
            <div class="md:col-span-2">

                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Kisi-kisi
                    <span class="font-normal text-gray-400">
                        (opsional)
                    </span>
                </label>

                <textarea
    name="kisi_kisi"
    form="form-edit-ujian"
    rows="4"
    maxlength="500"
    class="w-full border border-gray-300 rounded-xl px-4 py-3
           focus:ring-2 focus:ring-teal-500
           focus:border-teal-500"
>{{ old('kisi_kisi', $ujian->kisi_kisi) }}</textarea>

<p class="text-xs text-gray-400 mt-1">
    Maksimal 500 karakter.
</p>

            </div>

        </div>

    </div>


    {{-- DAFTAR SOAL --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- SIDEBAR SOAL --}}
        <div class="lg:col-span-4">

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5
                        lg:sticky lg:top-6">

                <div class="flex items-center justify-between mb-4">

                    <div>
                        <h2 class="font-bold text-gray-800">
                            Daftar Soal
                        </h2>

                        <p class="text-sm text-gray-500">
                            <span x-text="questions.length"></span> soal
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="addQuestion()"
                        class="px-3 py-2 bg-teal-600 hover:bg-teal-700
                               text-white rounded-xl text-sm font-semibold"
                    >
                        + Soal
                    </button>

                </div>


                <div class="space-y-2 max-h-[600px] overflow-y-auto pr-1">

                    <template
                        x-for="(question, index) in questions"
                        :key="question.localId"
                    >

                        <button
                            type="button"
                            @click="activeQuestion = index"
                            class="w-full text-left p-3 rounded-xl border transition"

                            :class="
                                activeQuestion === index
                                ? 'border-teal-500 bg-teal-50'
                                : 'border-gray-200 hover:border-gray-300'
                            "
                        >

                            <div class="flex items-center gap-3">

                                <div
                                    class="w-9 h-9 rounded-lg flex items-center
                                           justify-center font-bold flex-shrink-0"
                                    :class="
                                        activeQuestion === index
                                        ? 'bg-teal-600 text-white'
                                        : 'bg-gray-100 text-gray-600'
                                    "
                                    x-text="index + 1"
                                ></div>

                                <div class="min-w-0">

                                    <p
                                        class="font-semibold text-sm truncate"
                                        x-text="
                                            question.pertanyaan ||
                                            'Soal belum diisi'
                                        "
                                    ></p>

                                    <p
                                        class="text-xs text-gray-500 mt-1"
                                        x-text="typeLabel(question.tipe_soal)"
                                    ></p>

                                </div>

                            </div>

                        </button>

                    </template>

                </div>

            </div>

        </div>


        {{-- EDITOR SOAL --}}
        <div class="lg:col-span-8">

            <div
                x-show="questions.length === 0"
                class="bg-white rounded-2xl border border-gray-100
                       p-12 text-center"
            >

                <div class="text-4xl mb-3">
                    📝
                </div>

                <h3 class="font-bold text-gray-700">
                    Belum ada soal
                </h3>

                <button
                    type="button"
                    @click="addQuestion()"
                    class="mt-4 px-4 py-2 bg-teal-600 text-white
                           rounded-xl font-semibold"
                >
                    Tambahkan Soal
                </button>

            </div>


            <template x-if="questions.length > 0">

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">

                    <div class="flex items-center justify-between mb-6">

                        <div>

                            <p class="text-sm text-gray-500">
                                Soal
                            </p>

                            <h2 class="text-xl font-bold text-gray-800">
                                Soal ke-<span x-text="activeQuestion + 1"></span>
                            </h2>

                        </div>

                        <button
                            type="button"
                            @click="removeQuestion(activeQuestion)"
                            class="px-3 py-2 text-red-600 hover:bg-red-50
                                   rounded-xl text-sm font-semibold"
                        >
                            Hapus Soal
                        </button>

                    </div>


                    {{-- PERTANYAAN --}}
                    <div class="mb-5">

                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Pertanyaan
                        </label>

                        <textarea
                            :name="`soals[${activeQuestion}][pertanyaan]`"
                            form="form-edit-ujian"
                            x-model="questions[activeQuestion].pertanyaan"
                            rows="5"
                            maxlength="500"
                            required
                            class="w-full border border-gray-300 rounded-xl px-4 py-3
                                   focus:ring-2 focus:ring-teal-500
                                   focus:border-teal-500"
                        ></textarea>
                        <p class="text-xs text-gray-400 mt-1">
    Maksimal 500 karakter.

                    </div>


                    {{-- TIPE SOAL --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">

                        <div>

                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Tipe Soal
                            </label>

                            <select
                                :name="`soals[${activeQuestion}][tipe_soal]`"
                                form="form-edit-ujian"
                                x-model="questions[activeQuestion].tipe_soal"
                                @change="changeType()"
                                class="w-full border border-gray-300 rounded-xl px-4 py-3
                                       focus:ring-2 focus:ring-teal-500
                                       focus:border-teal-500"
                            >

                                <option value="single_choice">
                                    Pilihan Tunggal
                                </option>

                                <option value="multiple_choice">
                                    Pilihan Ganda
                                </option>

                                <option value="essay">
                                    Essay
                                </option>

                                <option value="matching">
                                    Menjodohkan
                                </option>

                            </select>

                        </div>


                        {{-- BOBOT --}}
                        <div>

                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Bobot
                            </label>

                            <input
    type="number"
    min="0"
    max="99"
    step="1"
    form="form-edit-ujian"
    :name="`soals[${activeQuestion}][bobot]`"
    x-model="questions[activeQuestion].bobot"
    class="w-full border border-gray-300 rounded-xl px-4 py-3
           focus:ring-2 focus:ring-teal-500
           focus:border-teal-500"
>

                        </div>

                    </div>


                   {{-- PILIHAN --}}
<template
    x-if="
        questions[activeQuestion].tipe_soal === 'single_choice' ||
        questions[activeQuestion].tipe_soal === 'multiple_choice'
    "
>
    <div class="mb-5">

        <div class="flex items-center justify-between mb-3">
            <label class="text-sm font-semibold text-gray-700">
                Pilihan Jawaban
            </label>

            <button
    type="button"
    @click="addOption(activeQuestion)"
    x-show="questions[activeQuestion].pilihan.length < 6"
    class="text-sm font-semibold text-teal-600 hover:text-teal-700"
>
    + Tambah Pilihan
</button>
        </div>

        <div class="space-y-3">

            <template
                x-for="(option, optionIndex) in questions[activeQuestion].pilihan"
                :key="'option-' + questions[activeQuestion].localId + '-' + optionIndex"
            >

                <div class="flex items-center gap-3">

                    {{-- SINGLE CHOICE --}}
                    <template
                        x-if="
                            questions[activeQuestion].tipe_soal ===
                            'single_choice'
                        "
                    >
                        <input
                            type="radio"
                            class="form-check-input"
                            :value="String(optionIndex)"
                            x-model="questions[activeQuestion].kunci_jawaban"
                        >
                    </template>


                    {{-- MULTIPLE CHOICE --}}
                    <template
                        x-if="
                            questions[activeQuestion].tipe_soal ===
                            'multiple_choice'
                        "
                    >
                        <input
                            type="checkbox"
                            class="form-check-input"
                            :value="String(optionIndex)"
                            x-model="questions[activeQuestion].kunci_jawaban"
                        >
                    </template>


                    {{-- TEXT PILIHAN --}}
                    <input
                        type="text"
                        form="form-edit-ujian"
                        :name="
                            `soals[${activeQuestion}][pilihan][${optionIndex}]`
                        "
                        x-model="
                            questions[activeQuestion].pilihan[optionIndex]
                        "
                        placeholder="Tulis pilihan jawaban"
                        class="flex-1 border border-gray-300 rounded-xl px-4 py-3
                               focus:ring-2 focus:ring-teal-500
                               focus:border-teal-500"
                    >


                    {{-- HAPUS PILIHAN --}}
                    <button
                        type="button"
                        @click="removeOption(activeQuestion, optionIndex)"
                        class="text-red-500 hover:bg-red-50
                               p-2 rounded-lg text-xl"
                    >
                        ×
                    </button>

                </div>

            </template>

        </div>

    </div>
</template>

        </div>


        {{-- =====================================================
        KUNCI JAWABAN AKTIF
        ====================================================== --}}


    </div>
</template>


                    {{-- ESSAY --}}
                    <template
                        x-if="questions[activeQuestion].tipe_soal === 'essay'"
                    >

                        <div class="mb-5 p-4 bg-blue-50 rounded-xl">

                            <p class="text-sm text-blue-700">
                                Soal essay tidak membutuhkan pilihan jawaban.
                                Masukkan kunci jawaban/rubrik pada bagian jawaban
                                di bawah jika diperlukan.
                            </p>

                        </div>

                    </template>


                    {{-- MATCHING --}}
                    <template
                        x-if="questions[activeQuestion].tipe_soal === 'matching'"
                    >

                        <div class="mb-5 p-4 bg-purple-50 rounded-xl">

                            <p class="text-sm text-purple-700">
                                Soal menjodohkan dapat dikembangkan dengan pasangan
                                pertanyaan dan jawaban.
                            </p>

                        </div>

                    </template>


                    {{-- KUNCI JAWABAN ESSAY --}}
                    <template
                        x-if="questions[activeQuestion].tipe_soal === 'essay'"
                    >

                        <div class="mb-5">

                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Kunci Jawaban / Rubrik
                            </label>

                            <textarea
    :name="`soals[${activeQuestion}][kunci_jawaban_text]`"
    form="form-edit-ujian"
    x-model="questions[activeQuestion].kunci_jawaban_text"
    rows="4"
    class="w-full border border-gray-300 rounded-xl px-4 py-3
           focus:ring-2 focus:ring-teal-500
           focus:border-teal-500"
></textarea>
                        </div>

                    </template>



{{-- GAMBAR --}}
<div class="mb-5">

    <label class="block text-sm font-semibold text-gray-700 mb-2">
        Gambar Soal
        <span class="font-normal text-gray-400">
            (opsional)
        </span>
    </label>


    {{-- GAMBAR LAMA / PREVIEW --}}
    <template x-if="questions[activeQuestion].gambar">

        <div class="mb-4">

            <p class="text-xs text-gray-500 mb-2">
                Gambar saat ini:
            </p>

            <img
                :src="questions[activeQuestion].gambar"
                alt="Gambar soal"
                class="rounded-xl border border-gray-200 max-w-full"
                style="max-height: 250px;"
            >

        </div>

    </template>


    {{-- INPUT GAMBAR --}}
    <input
        id="input-gambar-soal"
        type="file"
        form="form-edit-ujian"
        :name="`soals[${activeQuestion}][gambar]`"
        accept="image/jpeg,image/png,image/webp,image/gif"
        @change="handleImageChange($event)"
        class="w-full border border-gray-300 rounded-xl px-4 py-3
               focus:ring-2 focus:ring-teal-500
               focus:border-teal-500"
    >


    <p class="text-xs text-gray-500 mt-2">
        Format: JPG, JPEG, PNG, WEBP, atau GIF.
        Maksimal 2 MB.
    </p>


    <p
        x-show="questions[activeQuestion].gambar"
        class="text-xs text-teal-600 mt-1"
    >
        Pilih gambar baru jika ingin mengganti gambar lama.
    </p>

</div>
                    <input
    type="hidden"
    form="form-edit-ujian"
    :name="`soals[${activeQuestion}][id]`"
    :value="questions[activeQuestion].id ?? ''"
>
{{-- KUNCI JAWABAN SOAL AKTIF --}}
<template
    x-for="(kunci, kunciIndex) in questions[activeQuestion].kunci_jawaban"
    :key="'active-answer-' + questions[activeQuestion].localId + '-' + kunciIndex"
>
    <input
        type="hidden"
        form="form-edit-ujian"
        :name="`soals[${activeQuestion}][kunci_jawaban][${kunciIndex}]`"
        :value="kunci"
    >
</template>


                    {{-- URUTAN --}}
                    <input
                        type="hidden"
                        form="form-edit-ujian"
                        :name="`soals[${activeQuestion}][urutan]`"
                        :value="activeQuestion + 1"
                    >

                </div>

            </template>

        </div>

    </div>


    {{-- FORM UPDATE --}}
    <form
    id="form-edit-ujian"
    action="{{ route('guru.ujian.update', $ujian) }}"
    method="POST"
    enctype="multipart/form-data"
>

        @csrf
        @method('PUT')

        {{-- INPUT SOAL DIBUAT OLEH EDITOR DI ATAS --}}

        <div class="flex flex-col sm:flex-row sm:justify-end gap-3">

            <a
                href="{{ route('guru.ujian.show', $ujian) }}"
                class="px-5 py-3 border border-gray-300 text-gray-700
                       rounded-xl font-semibold text-center hover:bg-gray-50"
            >
                Batal
            </a>

            <button
                type="submit"
                class="px-5 py-3 bg-teal-600 hover:bg-teal-700
                       text-white rounded-xl font-semibold"
            >
                Simpan Perubahan
            </button>

        </div>

    </form>
    {{-- ============================================================
DATA TERSEMBUNYI UNTUK SOAL YANG TIDAK AKTIF
Soal aktif dikirim melalui editor di atas.
Soal lainnya dikirim melalui input hidden.
============================================================ --}}

<template
    x-for="(soal, index) in questions"
    :key="'hidden-' + soal.localId"
>
    <div>

        {{-- =====================================================
        ID SOAL
        ====================================================== --}}
        <input
            type="hidden"
            :name="`soals[${index}][id]`"
            :value="soal.id ?? ''"
            :disabled="index === activeQuestion"
            form="form-edit-ujian"
        >

        {{-- =====================================================
        PERTANYAAN
        ====================================================== --}}
        <input
            type="hidden"
            :name="`soals[${index}][pertanyaan]`"
            :value="soal.pertanyaan || ''"
            :disabled="index === activeQuestion"
            form="form-edit-ujian"
        >

        {{-- =====================================================
        TIPE SOAL
        ====================================================== --}}
        <input
            type="hidden"
            :name="`soals[${index}][tipe_soal]`"
            :value="soal.tipe_soal"
            :disabled="index === activeQuestion"
            form="form-edit-ujian"
        >

        {{-- =====================================================
        BOBOT
        ====================================================== --}}
        <input
            type="hidden"
            :name="`soals[${index}][bobot]`"
            :value="soal.bobot ?? 1"
            :disabled="index === activeQuestion"
            form="form-edit-ujian"
        >

        {{-- =====================================================
        URUTAN
        ====================================================== --}}
        <input
            type="hidden"
            :name="`soals[${index}][urutan]`"
            :value="index + 1"
            :disabled="index === activeQuestion"
            form="form-edit-ujian"
        >

        {{-- =====================================================
        PILIHAN JAWABAN
        ====================================================== --}}
        <template
            x-for="(pilihan, pilihanIndex) in (soal.pilihan || [])"
            :key="'pilihan-' + soal.localId + '-' + pilihanIndex"
        >
            <input
                type="hidden"
                :name="`soals[${index}][pilihan][${pilihanIndex}]`"
                :value="pilihan"
                :disabled="index === activeQuestion"
                form="form-edit-ujian"
            >
        </template>

        {{-- =====================================================
        KUNCI JAWABAN
        ====================================================== --}}
        <template
            x-for="(kunci, kunciIndex) in (soal.kunci_jawaban || [])"
            :key="'kunci-' + soal.localId + '-' + kunciIndex"
        >
            <input
                type="hidden"
                :name="`soals[${index}][kunci_jawaban][${kunciIndex}]`"
                :value="kunci"
                :disabled="index === activeQuestion"
                form="form-edit-ujian"
            >
        </template>

        {{-- =====================================================
        KUNCI JAWABAN ESSAY
        ====================================================== --}}
        <input
            type="hidden"
            :name="`soals[${index}][kunci_jawaban_text]`"
            :value="soal.kunci_jawaban_text || ''"
            :disabled="index === activeQuestion"
            form="form-edit-ujian"
        >

    </div>
</template>

</div>


<script>

function editUjian() {

    return {

        activeQuestion: 0,

        questions: @js($soalData),

        handleImageChange(event) {
    const file = event.target.files[0];

    if (!file) {
        return;
    }

    if (!file.type.startsWith('image/')) {
        alert('File harus berupa gambar.');
        event.target.value = '';
        return;
    }

    if (file.size > 2 * 1024 * 1024) {
        alert('Ukuran gambar maksimal 2 MB.');
        event.target.value = '';
        return;
    }

    this.questions[this.activeQuestion].gambar =
        URL.createObjectURL(file);
},

       init() {

    this.questions = this.questions.map((question, index) => {

        return {
            ...question,

            localId:
                question.localId ??
                'existing-' + question.id,

            pilihan:
                Array.isArray(question.pilihan)
                    ? question.pilihan
                    : [],

            kunci_jawaban:
                Array.isArray(question.kunci_jawaban)
                    ? question.kunci_jawaban.map(value => String(value))
                    : [],

            kunci_jawaban_text:
                question.kunci_jawaban_text ?? '',

            bobot:
                question.bobot ?? 1,

        };

    });


    if (this.questions.length === 0) {
        this.addQuestion();
    }

},


        typeLabel(type) {

            return {

                single_choice: 'Pilihan Tunggal',

                multiple_choice: 'Pilihan Ganda',

                essay: 'Essay',

                matching: 'Menjodohkan',

            }[type] ?? type;

        },


       addQuestion() {

    if (this.questions.length >= 100) {
        alert('Maksimal 100 soal dalam satu ujian.');
        return;
    }

    this.questions.push({

        id: null,

        localId: 'new-' + Date.now(),

        pertanyaan: '',

        tipe_soal: 'single_choice',

        pilihan: [
            '',
            '',
            '',
            ''
        ],

        kunci_jawaban: [],

        kunci_jawaban_text: '',

        gambar: null,

        bobot: 1,

    });

    this.activeQuestion = this.questions.length - 1;

},


        removeQuestion(index) {

            if (!confirm('Hapus soal ini?')) {
                return;
            }

            this.questions.splice(index, 1);

            if (this.questions.length === 0) {

                this.activeQuestion = 0;

            } else if (this.activeQuestion >= this.questions.length) {

                this.activeQuestion = this.questions.length - 1;

            }

        },


        addOption(questionIndex) {

    if (this.questions[questionIndex].pilihan.length >= 6) {
        alert('Maksimal 6 pilihan jawaban (A sampai F).');
        return;
    }

    this.questions[questionIndex].pilihan.push('');

},


        removeOption(questionIndex, optionIndex) {

            this.questions[questionIndex].pilihan.splice(optionIndex, 1);

            this.questions[questionIndex].kunci_jawaban =
                this.questions[questionIndex].kunci_jawaban
                    .filter(index => Number(index) !== Number(optionIndex))
                    .map(index =>
                        Number(index) > Number(optionIndex)
                            ? Number(index) - 1
                            : Number(index)
                    );

        },





        changeType() {

    const question =
        this.questions[this.activeQuestion];

    if (
        question.tipe_soal === 'single_choice' ||
        question.tipe_soal === 'multiple_choice'
    ) {

        if (
            !Array.isArray(question.pilihan) ||
            question.pilihan.length === 0
        ) {
            question.pilihan = [
                '',
                '',
                '',
                ''
            ];
        }

        if (!Array.isArray(question.kunci_jawaban)) {
            question.kunci_jawaban = [];
        }

    } else {

        question.kunci_jawaban = [];

    }
},

    }

}

</script>

@endsection
