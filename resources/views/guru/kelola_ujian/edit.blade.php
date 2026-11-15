@extends('layouts.app')

@section('title', 'Edit Ujian')

@section('content')

    <div class="max-w-7xl mx-auto space-y-6" x-data="editUjian()">

        {{-- HEADER --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <a href="{{ route('guru.ujian.show', $ujian) }}"
                    class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-500 hover:text-teal-700 transition">
                    ← Kembali ke Detail Ujian
                </a>

                <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 tracking-tight mt-2">
                    {{ $modeJadwalkan ? 'Jadwalkan Ujian' : 'Edit Ujian' }}
                </h1>

                @if ($modeJadwalkan)
                    <div class="mt-4 p-4 bg-amber-50 border border-amber-200 rounded-xl">
                        <div class="flex items-start gap-3">
                            <span class="text-xl">📅</span>
                            <div>
                                <p class="font-semibold text-amber-800">Lengkapi jadwal ujian</p>
                                <p class="text-sm text-amber-700 mt-1">
                                    Isi Waktu Mulai dan Deadline untuk menjadwalkan ujian.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <p class="text-gray-500 mt-1 text-sm">
                    {{ $ujian->judul }}
                </p>
            </div>
        </div>

        {{-- INFORMASI DASAR UJIAN --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-5">
                Pengaturan Ujian
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                {{-- JUDUL --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Judul Ujian <span class="text-red-500">*</span>
                    </label>

                    <input type="text" name="judul" form="form-edit-ujian" value="{{ old('judul', $ujian->judul) }}"
                        maxlength="100" pattern="[\p{L}\p{N} ]+" title="Judul hanya boleh berisi huruf, angka, dan spasi."
                        required
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-teal-500 focus:border-teal-500">

                    @error('judul')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- FILTER BERTINGKAT: MAPEL + TINGKATAN + TARGET KELAS --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Mata Pelajaran & Target Kelas <span class="text-red-500">*</span>
                    </label>

                    <div class="border border-gray-300 rounded-xl p-4 space-y-5">

                        {{-- 1. PILIH MAPEL --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                1. Mata Pelajaran
                            </label>

                            <select name="mapel_id" form="form-edit-ujian" x-model="selectedMapel" @change="ubahMapel()"
                                required
                                class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                                <option value="">-- Pilih Mata Pelajaran --</option>
                                <template x-for="mapel in mapelList" :key="mapel.id">
                                    <option :value="String(mapel.id)" x-text="mapel.nama"></option>
                                </template>
                            </select>
                        </div>

                        {{-- 2. PILIH TINGKATAN --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                2. Tingkatan Kelas
                            </label>

                            <select x-model="selectedTingkatan" @change="ubahTingkatan()"
                                :disabled="!selectedMapel || daftarTingkatan.length === 0"
                                class="w-full border border-gray-300 rounded-xl px-4 py-3 disabled:bg-gray-100 disabled:text-gray-400 focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                                <option value="">-- Pilih Tingkatan --</option>
                                <template x-for="tingkat in daftarTingkatan" :key="tingkat.nama">
                                    <option :value="tingkat.nama" x-text="tingkat.nama"></option>
                                </template>
                            </select>

                            <p x-show="selectedMapel && daftarTingkatan.length === 0" class="text-xs text-amber-600 mt-2">
                                Belum ada tingkatan kelas untuk mata pelajaran ini.
                            </p>
                        </div>

                        {{-- 3. PILIH KELAS --}}
                        <div>
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
                                <label class="block text-sm font-medium text-gray-700">
                                    3. Pilih Kelas Target
                                </label>

                                <span x-show="kelasTerlihat.length > 0" class="text-xs font-semibold text-teal-700"
                                    x-text="kelasDipilih.length + ' kelas dipilih'"></span>
                            </div>

                            <div x-show="!selectedMapel"
                                class="p-4 bg-gray-50 border border-dashed border-gray-300 rounded-xl text-center">
                                <p class="text-sm text-gray-500">Pilih mata pelajaran terlebih dahulu.</p>
                            </div>

                            <div x-show="selectedMapel && !selectedTingkatan"
                                class="p-4 bg-gray-50 border border-dashed border-gray-300 rounded-xl text-center">
                                <p class="text-sm text-gray-500">Pilih tingkatan untuk menampilkan daftar kelas.</p>
                            </div>

                            <div x-show="selectedMapel && selectedTingkatan"
                                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                <template x-for="kelas in kelasTerlihat" :key="kelas.pengampu_kelas_id">
                                    <label
                                        class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-teal-50 cursor-pointer transition"
                                        :class="kelasDipilih.includes(String(kelas.pengampu_kelas_id)) ?
                                            'border-teal-500 bg-teal-50' : 'bg-white'">
                                        <input type="checkbox" name="pengampu_kelas_id[]" form="form-edit-ujian"
                                            :value="String(kelas.pengampu_kelas_id)" x-model="kelasDipilih"
                                            class="w-4 h-4 text-teal-700 rounded focus:ring-teal-500">
                                        <span class="text-sm text-gray-700 font-medium" x-text="kelas.nama_kelas"></span>
                                    </label>
                                </template>

                                <div x-show="kelasTerlihat.length === 0"
                                    class="sm:col-span-2 lg:col-span-3 p-4 text-center bg-gray-50 rounded-xl border border-dashed border-gray-300">
                                    <p class="text-sm text-gray-500">Tidak ada kelas pada tingkatan ini.</p>
                                </div>
                            </div>
                        </div>

                        {{-- RINGKASAN KELAS DIPILIH --}}
                        <div x-show="kelasDipilih.length > 0" class="p-4 bg-teal-50 border border-teal-100 rounded-xl">
                            <p class="text-sm font-semibold text-teal-800">Target ujian aktif:</p>
                            <p class="text-sm text-teal-700 mt-0.5" x-text="kelasDipilih.length + ' kelas telah dipilih.'">
                            </p>
                        </div>
                    </div>

                    @error('pengampu_kelas_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- DURASI --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Durasi Ujian <span class="text-red-500">*</span>
                    </label>

                    <div class="relative">
                        <input type="text" inputmode="numeric" name="durasi_menit" form="form-edit-ujian"
                            value="{{ old('durasi_menit', $ujian->durasi_menit) }}" maxlength="3" pattern="[1-9][0-9]{0,2}"
                            title="Durasi harus berupa angka 1 sampai 999." required
                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 3)"
                            class="w-full border border-gray-300 rounded-xl px-4 py-3 pr-20 focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                        <span class="absolute right-4 top-3 text-gray-400 text-sm">menit</span>
                    </div>

                    @error('durasi_menit')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- KKM (PERBAIKAN: Nilai desimal dari DB otomatis dibersihkan ke integer) --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        KKM <span class="text-red-500">*</span>
                    </label>

                    <input type="text" inputmode="numeric" name="kkm" form="form-edit-ujian"
                        value="{{ old('kkm', (int) ($ujian->kkm ?? 75)) }}" maxlength="3" pattern="[0-9]{1,3}"
                        title="KKM harus berupa angka bulat 0 sampai 100." required
                        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 3); if (Number(this.value) > 100) this.value = '100';"
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-teal-500 focus:border-teal-500">

                    <p class="text-xs text-gray-400 mt-1">Nilai KKM 0–100.</p>

                    @error('kkm')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Pengaturan Visibilitas Nilai Siswa --}}
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between gap-4">
                    <div>
                        <label for="tampilkan_nilai" class="text-sm font-bold text-gray-900 cursor-pointer block">
                            Tampilkan Nilai ke Siswa Setelah Ujian
                        </label>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Jika dinonaktifkan, siswa hanya melihat konfirmasi bahwa ujian telah terkirim tanpa mengetahui
                            skor nilainya.
                        </p>
                    </div>

                    <label class="relative inline-flex items-center cursor-pointer shrink-0">
                        <input type="hidden" name="tampilkan_nilai" value="0">
                        <input type="checkbox" id="tampilkan_nilai" name="tampilkan_nilai" value="1"
                            {{ old('tampilkan_nilai', $ujian->tampilkan_nilai) ? 'checked' : '' }} class="sr-only peer">
                        <div
                            class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                        </div>
                    </label>
                </div>

                {{-- WAKTU MULAI --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Waktu Mulai <span class="font-normal text-gray-400">(opsional untuk draft)</span>
                    </label>

                    <input type="datetime-local" name="waktu_mulai" form="form-edit-ujian"
                        value="{{ old('waktu_mulai', $ujian->waktu_mulai?->format('Y-m-d\TH:i')) }}"
                        {{ $modeJadwalkan ? 'required' : '' }}
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-teal-500 focus:border-teal-500">

                    @error('waktu_mulai')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- DEADLINE --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Deadline <span class="font-normal text-gray-400">(opsional untuk draft)</span>
                    </label>

                    <input type="datetime-local" name="deadline" form="form-edit-ujian"
                        value="{{ old('deadline', $ujian->deadline?->format('Y-m-d\TH:i')) }}"
                        {{ $modeJadwalkan ? 'required' : '' }}
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-teal-500 focus:border-teal-500">

                    @error('deadline')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- KISI-KISI --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Kisi-kisi <span class="font-normal text-gray-400">(opsional)</span>
                    </label>

                    <textarea name="kisi_kisi" form="form-edit-ujian" rows="4" maxlength="500"
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-teal-500 focus:border-teal-500">{{ old('kisi_kisi', $ujian->kisi_kisi) }}</textarea>

                    <p class="text-xs text-gray-400 mt-1">Maksimal 500 karakter.</p>
                </div>

            </div>
        </div>

        {{-- RINGKASAN BOBOT TOTAL --}}
        <div class="p-4 rounded-xl border transition"
            :class="totalBobot > 100.01 ? 'bg-red-50 border-red-200' : 'bg-teal-50 border-teal-200'">
            <div class="flex items-center justify-between">
                <span class="font-semibold text-gray-700 text-sm">Total Bobot Seluruh Soal</span>
                <span class="font-black text-lg" :class="totalBobot > 100.01 ? 'text-red-600' : 'text-teal-700'"
                    x-text="totalBobot + ' / 100'"></span>
            </div>
            <p class="text-xs mt-1" :class="totalBobot > 100.01 ? 'text-red-600' : 'text-gray-500'"
                x-text="totalBobot > 100.01 ? 'Total bobot melebihi 100 poin! Silakan periksa kembali bobot soal.' : 'Total bobot terdistribusi proporsional 100 poin.'">
            </p>
        </div>

        {{-- DAFTAR SOAL & EDITOR SOAL --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            {{-- SIDEBAR DAFTAR SOAL --}}
            <div class="lg:col-span-4">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 lg:sticky lg:top-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="font-bold text-gray-800">Daftar Soal</h2>
                            <p class="text-xs text-gray-500 mt-0.5">
                                <span x-text="questions.length"></span> butir soal
                            </p>
                        </div>

                        <button type="button" @click="addQuestion()"
                            class="px-3.5 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-bold transition shadow-xs">
                            + Tambah Soal
                        </button>
                    </div>

                    <div class="space-y-2 max-h-[600px] overflow-y-auto pr-1">
                        <template x-for="(question, index) in questions" :key="question.localId">
                            <button type="button" @click="switchQuestion(index)"
                                class="w-full text-left p-3 rounded-xl border transition relative group"
                                :class="activeQuestion === index ? 'border-teal-500 bg-teal-50/70 shadow-xs' :
                                    'border-gray-200 hover:border-gray-300 bg-white'">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center font-bold flex-shrink-0 text-xs"
                                            :class="activeQuestion === index ? 'bg-teal-600 text-white' :
                                                'bg-gray-100 text-gray-600'"
                                            x-text="index + 1"></div>

                                        <div class="min-w-0">
                                            <p class="font-semibold text-xs truncate text-gray-800"
                                                x-text="question.pertanyaan || 'Soal belum diisi'"></p>
                                            <p class="text-[11px] text-gray-400 mt-0.5"
                                                x-text="typeLabel(question.tipe_soal) + ' • ' + (question.bobot || 0) + ' pt'">
                                            </p>
                                        </div>
                                    </div>

                                    {{-- TOMBOL SILANG (X) UNTUK MENGHAPUS SOAL DARI SIDEBAR --}}
                                    <span x-show="questions.length > 1" @click.stop="removeQuestion(index)"
                                        class="w-6 h-6 flex items-center justify-center rounded-lg text-xs font-bold text-gray-400 hover:text-white hover:bg-red-500 transition shrink-0"
                                        title="Hapus soal ini">
                                        ✕
                                    </span>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            {{-- EDITOR SOAL AKTIF --}}
            <div class="lg:col-span-8 min-w-0">

                <div x-show="questions.length === 0" class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
                    <div class="text-4xl mb-3">📝</div>
                    <h3 class="font-bold text-gray-700">Belum ada soal</h3>
                    <button type="button" @click="addQuestion()"
                        class="mt-4 px-4 py-2 bg-teal-600 text-white rounded-xl font-semibold">
                        Tambahkan Soal
                    </button>
                </div>

                <template x-if="questions.length > 0">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">

                        <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                            <div>
                                <span class="text-xs text-gray-400 font-semibold uppercase tracking-wider">Editor Butir
                                    Soal</span>
                                <h2 class="text-xl font-bold text-gray-800 mt-0.5">
                                    Soal Nomor <span x-text="activeQuestion + 1"></span>
                                </h2>
                            </div>

                            <button type="button" @click="removeQuestion(activeQuestion)" x-show="questions.length > 1"
                                class="px-3 py-1.5 text-red-600 hover:bg-red-50 rounded-xl text-xs font-bold transition border border-red-200">
                                Hapus Soal
                            </button>
                        </div>

                        {{-- TIPE SOAL --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Tipe Soal
                            </label>

                            <select :name="`soals[${activeQuestion}][tipe_soal]`" form="form-edit-ujian"
                                x-model="questions[activeQuestion].tipe_soal" @change="changeType()"
                                class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                                <option value="single_choice">Pilihan Ganda (Tunggal)</option>
                                <option value="multiple_choice">Pilihan Ganda Kompleks (Banyak Jawaban)</option>
                                <option value="essay">Essay</option>
                                <option value="matching">Menjodohkan</option>
                            </select>
                        </div>

                        {{-- TINGKAT KESULITAN & BOBOT OTOMATIS --}}
                        <div
                            class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 bg-gray-50/70 border border-gray-200 rounded-2xl">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                                    Tingkat Kesulitan
                                </label>

                                <select :name="`soals[${activeQuestion}][kesulitan]`" form="form-edit-ujian"
                                    x-model="questions[activeQuestion].kesulitan" @change="hitungBobotOtomatis()"
                                    class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm bg-white focus:ring-2 focus:ring-teal-500">
                                    <option value="1">Mudah (Poin: 1)</option>
                                    <option value="2">Menengah (Poin: 2)</option>
                                    <option value="3">Sulit (Poin: 3)</option>
                                </select>
                                <p class="text-[11px] text-gray-400 mt-1">Bobot soal akan disesuaikan otomatis.</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                                    Bobot Soal (Otomatis)
                                </label>

                                <div class="relative">
                                    <input type="number" step="0.01" :name="`soals[${activeQuestion}][bobot]`"
                                        form="form-edit-ujian" x-model="questions[activeQuestion].bobot" readonly
                                        class="w-full bg-gray-100 border border-gray-300 rounded-xl px-3.5 py-2.5 pr-14 text-sm font-bold text-gray-800 cursor-not-allowed">
                                    <span class="absolute right-4 top-2.5 text-xs font-bold text-gray-400">Poin</span>
                                </div>
                                <p class="text-[11px] text-teal-600 font-semibold mt-1">✓ Dihitung otomatis agar total pas
                                    100.</p>
                            </div>
                        </div>

                        {{-- PERTANYAAN --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Pertanyaan <span class="text-red-500">*</span>
                            </label>

                            <textarea :name="`soals[${activeQuestion}][pertanyaan]`" form="form-edit-ujian"
                                x-model="questions[activeQuestion].pertanyaan" rows="5" maxlength="500" required
                                placeholder="Tuliskan pertanyaan soal..."
                                class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-teal-500 focus:border-teal-500"></textarea>
                            <p class="text-xs text-gray-400 mt-1">Maksimal 500 karakter.</p>
                        </div>

                        {{-- GAMBAR SOAL --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Gambar Soal <span class="font-normal text-gray-400">(opsional)</span>
                            </label>

                            <template x-if="questions[activeQuestion].gambar">
                                <div class="mb-3 relative inline-block">
                                    <div class="border border-gray-200 rounded-xl p-2 bg-gray-50">
                                        <img :src="questions[activeQuestion].gambar" alt="Gambar Soal"
                                            class="max-h-56 max-w-full rounded-lg object-contain block bg-white">
                                    </div>
                                </div>
                            </template>

                            <template x-for="(question, index) in questions" :key="'image-' + question.localId">
                                <input type="file" :id="`gambar-soal-${index}`" :name="`soals[${index}][gambar]`"
                                    form="form-edit-ujian" accept="image/jpeg,image/png,image/webp,image/gif"
                                    x-show="activeQuestion === index" @change="handleImageChange($event, index)"
                                    class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 cursor-pointer">
                            </template>
                            <p class="text-xs text-gray-400 mt-1">Format JPG, PNG, WEBP, atau GIF. Maksimal 2 MB.</p>
                        </div>

                        {{-- PILIHAN GANDA --}}
                        <template
                            x-if="questions[activeQuestion].tipe_soal === 'single_choice' || questions[activeQuestion].tipe_soal === 'multiple_choice'">
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <label class="text-sm font-semibold text-gray-700">
                                        Pilihan Jawaban
                                    </label>

                                    <button type="button" @click="addOption(activeQuestion)"
                                        x-show="questions[activeQuestion].pilihan.length < 6"
                                        class="text-xs font-bold text-teal-700 hover:text-teal-900">
                                        + Tambah Pilihan
                                    </button>
                                </div>

                                <div class="space-y-3">
                                    <template x-for="(option, optionIndex) in questions[activeQuestion].pilihan"
                                        :key="'option-' + questions[activeQuestion].localId + '-' + optionIndex">
                                        <div class="flex items-center gap-3">

                                            {{-- Radio untuk single_choice --}}
                                            <template x-if="questions[activeQuestion].tipe_soal === 'single_choice'">
                                                <input type="radio" class="w-4 h-4 text-teal-700 focus:ring-teal-500"
                                                    :name="`soals[${activeQuestion}][kunci_radio]`"
                                                    :value="String.fromCharCode(97 + optionIndex)"
                                                    :checked="questions[activeQuestion].kunci_jawaban.includes(String
                                                        .fromCharCode(97 + optionIndex))"
                                                    @change="setSingleAnswer(optionIndex)">
                                            </template>

                                            {{-- Checkbox untuk multiple_choice --}}
                                            <template x-if="questions[activeQuestion].tipe_soal === 'multiple_choice'">
                                                <input type="checkbox"
                                                    class="w-4 h-4 text-teal-700 rounded focus:ring-teal-500"
                                                    :value="String.fromCharCode(97 + optionIndex)"
                                                    :checked="questions[activeQuestion].kunci_jawaban.includes(String
                                                        .fromCharCode(97 + optionIndex))"
                                                    @change="toggleMultipleAnswer(optionIndex)">
                                            </template>

                                            <span
                                                class="w-7 h-7 rounded-lg bg-gray-100 text-gray-600 font-bold text-xs flex items-center justify-center shrink-0"
                                                x-text="String.fromCharCode(65 + optionIndex)"></span>

                                            <input type="text" form="form-edit-ujian"
                                                :name="`soals[${activeQuestion}][pilihan][${optionIndex}]`"
                                                x-model="questions[activeQuestion].pilihan[optionIndex]"
                                                placeholder="Tulis pilihan jawaban..."
                                                class="flex-1 border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">

                                            <button type="button" @click="removeOption(activeQuestion, optionIndex)"
                                                class="p-2 text-red-500 hover:bg-red-50 rounded-lg text-lg font-bold"
                                                title="Hapus pilihan">
                                                ×
                                            </button>
                                        </div>
                                    </template>
                                </div>
                                <p class="text-xs text-gray-400 mt-2">Centang/Pilih radio di sebelah kiri untuk menandai
                                    opsi sebagai kunci jawaban.</p>
                            </div>
                        </template>

                        {{-- ESSAY --}}
                        <template x-if="questions[activeQuestion].tipe_soal === 'essay'">
                            <div class="space-y-3">
                                <div class="p-4 bg-blue-50 border border-blue-100 rounded-xl text-xs text-blue-700">
                                    Soal essay tidak membutuhkan pilihan ganda. Guru dapat mengisi pedoman/rubrik penilaian
                                    di bawah.
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Pedoman Jawaban / Rubrik (Opsional)
                                    </label>
                                    <textarea :name="`soals[${activeQuestion}][kunci_jawaban_text]`" form="form-edit-ujian"
                                        x-model="questions[activeQuestion].kunci_jawaban_text" rows="4"
                                        class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-teal-500"></textarea>
                                </div>
                            </div>
                        </template>

                        {{-- MATCHING --}}
                        <template x-if="questions[activeQuestion].tipe_soal === 'matching'">
                            <div class="p-4 bg-purple-50 border border-purple-100 rounded-xl text-xs text-purple-700">
                                Tipe soal menjodohkan menggunakan data pasangan pertanyaan dan jawaban.
                            </div>
                        </template>

                        {{-- HIDDEN INPUT UNTUK SOAL AKTIF --}}
                        <input type="hidden" form="form-edit-ujian" :name="`soals[${activeQuestion}][id]`"
                            :value="questions[activeQuestion].id ?? ''">
                        <input type="hidden" form="form-edit-ujian" :name="`soals[${activeQuestion}][urutan]`"
                            :value="activeQuestion + 1">

                        <template x-for="(kunci, kIndex) in questions[activeQuestion].kunci_jawaban"
                            :key="'active-kunci-' + questions[activeQuestion].localId + '-' + kIndex">
                            <input type="hidden" form="form-edit-ujian"
                                :name="`soals[${activeQuestion}][kunci_jawaban][${kIndex}]`" :value="kunci">
                        </template>

                    </div>
                </template>
            </div>

        </div>

        {{-- FORM UPDATE --}}
        <form id="form-edit-ujian" action="{{ route('guru.ujian.update', $ujian) }}" method="POST"
            enctype="multipart/form-data" @submit="if (!validateBeforeSubmit()) $event.preventDefault()">

            @csrf
            @method('PUT')

            <div class="flex flex-col sm:flex-row sm:justify-end gap-3 pt-6 border-t border-gray-200">
                <a href="{{ route('guru.ujian.show', $ujian) }}"
                    class="px-5 py-3 border border-gray-300 text-gray-700 rounded-xl font-semibold text-center hover:bg-gray-50 transition text-sm">
                    Batal
                </a>

                <button type="submit"
                    class="px-6 py-3 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-bold transition shadow-xs text-sm">
                    {{ $modeJadwalkan ? 'Simpan Jadwal' : 'Simpan Perubahan' }}
                </button>
            </div>
        </form>

        {{-- ============================================================
            DATA TERSEMBUNYI UNTUK SOAL YANG TIDAK AKTIF
        ============================================================ --}}
        <template x-for="(soal, index) in questions" :key="'hidden-' + soal.localId">
            <div>
                <input type="hidden" :name="`soals[${index}][id]`" :value="soal.id ?? ''"
                    :disabled="index === activeQuestion" form="form-edit-ujian">

                <input type="hidden" :name="`soals[${index}][pertanyaan]`" :value="soal.pertanyaan || ''"
                    :disabled="index === activeQuestion" form="form-edit-ujian">

                <input type="hidden" :name="`soals[${index}][tipe_soal]`" :value="soal.tipe_soal"
                    :disabled="index === activeQuestion" form="form-edit-ujian">

                <input type="hidden" :name="`soals[${index}][kesulitan]`" :value="soal.kesulitan ?? 1"
                    :disabled="index === activeQuestion" form="form-edit-ujian">

                <input type="hidden" :name="`soals[${index}][bobot]`" :value="soal.bobot ?? 1"
                    :disabled="index === activeQuestion" form="form-edit-ujian">

                <input type="hidden" :name="`soals[${index}][urutan]`" :value="index + 1"
                    :disabled="index === activeQuestion" form="form-edit-ujian">

                <template x-for="(pilihan, pIdx) in (soal.pilihan || [])" :key="'pilihan-' + soal.localId + '-' + pIdx">
                    <input type="hidden" :name="`soals[${index}][pilihan][${pIdx}]`" :value="pilihan"
                        :disabled="index === activeQuestion" form="form-edit-ujian">
                </template>

                <template x-for="(kunci, kIdx) in (soal.kunci_jawaban || [])" :key="'kunci-' + soal.localId + '-' + kIdx">
                    <input type="hidden" :name="`soals[${index}][kunci_jawaban][${kIdx}]`" :value="kunci"
                        :disabled="index === activeQuestion" form="form-edit-ujian">
                </template>

                <input type="hidden" :name="`soals[${index}][kunci_jawaban_text]`"
                    :value="soal.kunci_jawaban_text || ''" :disabled="index === activeQuestion" form="form-edit-ujian">
            </div>
        </template>

    </div>

    {{-- =============================================================
        JAVASCRIPT ALPINE.JS
    ============================================================= --}}
    <script>
        function editUjian() {
            return {
                mapelList: @js($dataMapel ?? []),
                selectedMapel: @js((string) old('mapel_id', $ujian->pengampuKelas?->guruMapel?->mapel_id ?? '')),
                selectedTingkatan: '',
                kelasDipilih: @js(array_map('strval', (array) old('pengampu_kelas_id', $grupUjians->pluck('pengampu_kelas_id')->toArray()))),

                activeQuestion: 0,
                questions: @js($soalData ?? []),

                get mapelAktif() {
                    return this.mapelList.find(m => String(m.id) === String(this.selectedMapel)) || null;
                },

                get daftarTingkatan() {
                    return this.mapelAktif?.tingkatan || [];
                },

                get tingkatanAktif() {
                    return this.daftarTingkatan.find(t => t.nama === this.selectedTingkatan) || null;
                },

                get kelasTerlihat() {
                    return this.tingkatanAktif?.kelas || [];
                },

                ubahMapel() {
                    this.selectedTingkatan = '';
                    this.kelasDipilih = [];
                },

                ubahTingkatan() {
                    this.kelasDipilih = [];
                },

                init() {
                    // 1. Inisialisasi format soal & kesulitan
                    this.questions = this.questions.map((q, idx) => ({
                        ...q,
                        localId: q.localId ?? 'existing-' + (q.id || idx),
                        pilihan: Array.isArray(q.pilihan) ? q.pilihan : [],
                        kunci_jawaban: Array.isArray(q.kunci_jawaban) ?
                            q.kunci_jawaban.map(v => {
                                const ans = String(v).toLowerCase();
                                return /^\d+$/.test(ans) ? String.fromCharCode(97 + Number(ans)) : ans;
                            }) :
                            [],
                        kunci_jawaban_text: q.kunci_jawaban_text ?? '',
                        kesulitan: Number(q.kesulitan) || 1,
                        bobot: Number(q.bobot) || 1,
                    }));

                    if (this.questions.length === 0) {
                        this.addQuestion();
                    } else {
                        this.hitungBobotOtomatis();
                    }

                    // 2. Pulihkan selectedTingkatan berdasarkan kelasDipilih awal
                    if (this.selectedMapel && this.kelasDipilih.length > 0) {
                        const mapel = this.mapelAktif;
                        if (mapel && Array.isArray(mapel.tingkatan)) {
                            const tingkat = mapel.tingkatan.find(t =>
                                t.kelas.some(k => this.kelasDipilih.includes(String(k.pengampu_kelas_id)))
                            );
                            if (tingkat) {
                                this.selectedTingkatan = tingkat.nama;
                            }
                        }
                    }
                },

                // =========================================================
                // HITUNG BOBOT OTOMATIS BERDASARKAN TINGKAT KESULITAN (TOTAL 100)
                // =========================================================
                hitungBobotOtomatis() {
                    const totalSoal = this.questions.length;
                    if (totalSoal === 0) return;

                    const totalPoinKesulitan = this.questions.reduce((sum, q) => {
                        return sum + (Number(q.kesulitan) || 1);
                    }, 0);

                    if (totalPoinKesulitan <= 0) return;

                    let totalBobotTerbagi = 0;

                    this.questions.forEach((question, index) => {
                        const kesulitan = Number(question.kesulitan) || 1;

                        if (index === totalSoal - 1) {
                            // Soal terakhir mengambil sisa selisih pembulatan agar genap 100.00
                            question.bobot = Number((100 - totalBobotTerbagi).toFixed(2));
                        } else {
                            const porsi = Number(((kesulitan / totalPoinKesulitan) * 100).toFixed(2));
                            question.bobot = porsi;
                            totalBobotTerbagi += porsi;
                        }
                    });
                },

                get totalBobot() {
                    return Number(this.questions.reduce((total, q) => {
                        return total + (parseFloat(q.bobot) || 0);
                    }, 0).toFixed(2));
                },

                typeLabel(type) {
                    return {
                        single_choice: 'Pilihan Ganda',
                        multiple_choice: 'Pilihan Ganda Kompleks',
                        essay: 'Essay',
                        matching: 'Menjodohkan',
                    } [type] ?? type;
                },

                addQuestion() {
                    if (this.questions.length >= 100) {
                        alert('Maksimal 100 soal dalam satu ujian.');
                        return;
                    }

                    this.questions.push({
                        id: null,
                        localId: 'new-' + Date.now() + Math.random(),
                        pertanyaan: '',
                        tipe_soal: 'single_choice',
                        pilihan: ['', '', '', ''],
                        kunci_jawaban: [],
                        kunci_jawaban_text: '',
                        gambar: null,
                        kesulitan: 1, // Default: Mudah (1)
                        bobot: 0,
                    });

                    this.activeQuestion = this.questions.length - 1;
                    this.hitungBobotOtomatis();
                },

                removeQuestion(index) {
                    if (this.questions.length <= 1) {
                        alert('Minimal harus ada satu soal dalam ujian.');
                        return;
                    }

                    if (!confirm(`Hapus soal nomor ${index + 1}?`)) {
                        return;
                    }

                    this.questions.splice(index, 1);

                    if (this.activeQuestion >= this.questions.length) {
                        this.activeQuestion = this.questions.length - 1;
                    }

                    this.hitungBobotOtomatis();
                },

                addOption(qIndex) {
                    if (this.questions[qIndex].pilihan.length >= 6) {
                        alert('Maksimal 6 pilihan jawaban (A sampai F).');
                        return;
                    }
                    this.questions[qIndex].pilihan.push('');
                },

                removeOption(qIndex, optIndex) {
                    const q = this.questions[qIndex];
                    if (q.pilihan.length <= 2) {
                        alert('Minimal harus ada 2 pilihan jawaban.');
                        return;
                    }
                    const removedLetter = String.fromCharCode(97 + optIndex);
                    q.pilihan.splice(optIndex, 1);

                    q.kunci_jawaban = q.kunci_jawaban
                        .filter(k => k !== removedLetter)
                        .map(k => {
                            const code = k.charCodeAt(0) - 97;
                            return code > optIndex ? String.fromCharCode(97 + (code - 1)) : k;
                        });
                },

                setSingleAnswer(optIndex) {
                    const letter = String.fromCharCode(97 + optIndex);
                    this.questions[this.activeQuestion].kunci_jawaban = [letter];
                },

                toggleMultipleAnswer(optIndex) {
                    const letter = String.fromCharCode(97 + optIndex);
                    const answers = this.questions[this.activeQuestion].kunci_jawaban;
                    const idx = answers.indexOf(letter);

                    if (idx > -1) {
                        answers.splice(idx, 1);
                    } else {
                        answers.push(letter);
                    }
                },

                changeType() {
                    const q = this.questions[this.activeQuestion];
                    if (q.tipe_soal === 'single_choice' || q.tipe_soal === 'multiple_choice') {
                        if (!Array.isArray(q.pilihan) || q.pilihan.length === 0) {
                            q.pilihan = ['', '', '', ''];
                        }
                        q.kunci_jawaban = [];
                    } else {
                        q.kunci_jawaban = [];
                    }
                },

                switchQuestion(index) {
                    this.activeQuestion = index;
                },

                handleImageChange(event, index) {
                    const file = event.target.files[0];
                    if (!file) return;

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

                    this.questions[index].gambar = URL.createObjectURL(file);
                },

                validateBeforeSubmit() {
                    if (this.kelasDipilih.length === 0) {
                        alert('Silakan pilih minimal satu kelas target ujian.');
                        return false;
                    }

                    if (this.totalBobot > 100.01) {
                        alert(`Total bobot soal ${this.totalBobot}. Maksimal 100 poin.`);
                        return false;
                    }

                    for (let i = 0; i < this.questions.length; i++) {
                        const q = this.questions[i];
                        const nomor = i + 1;

                        if (!q.pertanyaan || q.pertanyaan.trim() === '') {
                            this.activeQuestion = i;
                            alert(`Pertanyaan pada soal nomor ${nomor} belum diisi.`);
                            return false;
                        }

                        if (q.tipe_soal === 'single_choice' || q.tipe_soal === 'multiple_choice') {
                            if (!Array.isArray(q.pilihan) || q.pilihan.length < 2) {
                                this.activeQuestion = i;
                                alert(`Soal nomor ${nomor} minimal harus memiliki 2 pilihan jawaban.`);
                                return false;
                            }

                            for (let p = 0; p < q.pilihan.length; p++) {
                                if (!q.pilihan[p] || q.pilihan[p].trim() === '') {
                                    this.activeQuestion = i;
                                    const huruf = String.fromCharCode(65 + p);
                                    alert(`Pilihan ${huruf} pada soal nomor ${nomor} belum diisi.`);
                                    return false;
                                }
                            }

                            if (!Array.isArray(q.kunci_jawaban) || q.kunci_jawaban.length === 0) {
                                this.activeQuestion = i;
                                alert(`Soal nomor ${nomor} belum memiliki kunci jawaban.`);
                                return false;
                            }
                        }
                    }

                    return true;
                }
            };
        }
    </script>

@endsection
