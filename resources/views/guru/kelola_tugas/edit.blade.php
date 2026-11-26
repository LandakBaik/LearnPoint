@extends('layouts.app')

@section('title', 'Edit Tugas')

@section('content')
<div class="space-y-6"
    x-data="{
        showEditModal: true,
        editData: {},
        jenisTugas: 'upload',
        hasFileEdit: false,
        fileNameEdit: '',
        fileSizeEdit: '',

        bukaModal(data) {
            this.editData = JSON.parse(JSON.stringify(data || {}));

            let tipe = String(
                data.tipe ?? data.tipe_tugas ?? data.jenis_tugas ?? ''
            ).toLowerCase().trim();

            this.jenisTugas = ['pilgan', 'pilihan_ganda', 'pilihan ganda'].includes(tipe)
                ? 'pilihan_ganda'
                : 'upload';

            this.editData.tipe = this.jenisTugas;

            // DESKRIPSI
            this.editData.deskripsi = this.editData.deskripsi ?? '';

            // DEADLINE
            if (this.editData.deadline) {
                this.editData.deadline = String(this.editData.deadline)
                    .replace(' ', 'T')
                    .substring(0, 16);
            }

            // SOAL PILIHAN GANDA
            if (this.jenisTugas === 'pilihan_ganda') {
                let soals = this.editData.soals || [];

                this.editData.soals = soals.map(soal => {
                    let pilihan = soal.pilihan || {};

                    if (typeof pilihan === 'string') {
                        try {
                            pilihan = JSON.parse(pilihan);
                        } catch (e) {
                            pilihan = {};
                        }
                    }

                    return {
                        ...soal,
                        pertanyaan: soal.pertanyaan ?? '',
                        pilihan: {
                            a: pilihan.a ?? '',
                            b: pilihan.b ?? '',
                            c: pilihan.c ?? '',
                            d: pilihan.d ?? '',
                            e: pilihan.e ?? ''
                        },
                        kunci_jawaban: soal.kunci_jawaban ?? '',
                        gambar: soal.gambar ?? ''
                    };
                });

                // Jika tugas belum memiliki soal, buat 1 soal kosong
                if (this.editData.soals.length === 0) {
                    this.editData.soals = [{
                        pertanyaan: '',
                            pilihan: {
                                a: '',
                                b: '',
                                c: '',
                                d: '',
                                e: ''
                            },
                            kunci_jawaban: '',
                            gambar: ''
                    }];
                }
            }

            // FILE LAMPIRAN LAMA
            this.hasFileEdit = false;
            this.fileNameEdit = '';
            this.fileSizeEdit = '';

            let fileLama =
                data.file_tugas ??
                data.file_tugas_input ??
                data.lampiran ??
                data.file ??
                '';

            if (fileLama) {
                let namaFile = String(fileLama).split('/').pop();

                this.hasFileEdit = true;
                this.fileNameEdit = namaFile;
                this.fileSizeEdit = 'File lampiran tersimpan';
            }

            // KELAS YANG SUDAH TERHUBUNG DENGAN TUGAS
            let ids =
                data.pengampu_kelas_ids ??
                data.pengampu_kelas_id ??
                data.pengampu_kelases?.map(item => item.id) ??
                data.pengampu_kelases?.map(item => item.pivot?.pengampu_kelas_id) ??
                [];

            if (!Array.isArray(ids)) {
                ids = [ids];
            }

            this.editData.kelasTerpilih = ids
                .filter(id => id !== null && id !== undefined && id !== '')
                .map(String);

            this.showEditModal = true;

            this.$nextTick(() => {
                const selectMapel = document.getElementById('editFilterMapel');
                const selectTingkat = document.getElementById('editFilterTingkat');
                const dropdown = document.getElementById('editDropdownKelas');

                if (selectMapel) {
                    selectMapel.value = '';
                }

                if (selectTingkat) {
                    selectTingkat.value = '';
                }

                if (dropdown) {
                    dropdown.classList.add('hidden');
                }

                window.resetFilterKelasEdit?.();

                // Set kelas terpilih setelah daftar checkbox siap
                document.querySelectorAll('.checkboxPengampuEdit').forEach(cb => {
                    cb.checked = this.editData.kelasTerpilih.includes(String(cb.value));
                });

                window.updateJumlahEdit?.();

                // Reset error deadline
                const error = document.getElementById('editDeadlineError');
                const deadline = document.getElementById('edit_deadline');

                if (error) error.classList.add('hidden');
                if (deadline) deadline.classList.remove('border-red-500');
            });
        },

        tambahSoalEdit() {
            if (!this.editData.soals) {
                this.editData.soals = [];
            }

            this.editData.soals.push({
                pertanyaan: '',
                pilihan: {
                    a: '',
                    b: '',
                    c: '',
                    d: '',
                    e: ''
                },
                kunci_jawaban: '',
                gambar: ''
            });
        },

        hapusSoalEdit(index) {
            if (this.editData.soals.length <= 1) {
                return;
            }

            this.editData.soals.splice(index, 1);
        },

        hapusLampiranEdit() {
            this.hasFileEdit = false;
            this.fileNameEdit = '';
            this.fileSizeEdit = '';

            const input = document.getElementById('edit_file_tugas_input');

            if (input) {
                input.value = '';
            }

            const hapusInput = document.getElementById('hapus_lampiran_edit');

            if (hapusInput) {
                hapusInput.value = '1';
            }
        },

        pilihFileEdit(event) {
            const file = event.target.files?.[0];

            if (!file) {
                return;
            }

            this.hasFileEdit = true;
            this.fileNameEdit = file.name;
            this.fileSizeEdit = (file.size / 1024 / 1024).toFixed(2) + ' MB';

            const hapusInput = document.getElementById('hapus_lampiran_edit');

            if (hapusInput) {
                hapusInput.value = '0';
            }
        }
    }"
    x-init="bukaModal(@json($tugas))"
    class="space-y-6">

    <!-- HEADER: disamakan dengan halaman CREATE -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Edit Tugas</h1>
            <p class="text-sm text-gray-500 mt-1">Perbarui judul, kelas, jenis tugas, serta batas waktu pengumpulan.</p>
        </div>
        <a href="{{ route('guru.tugas') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-gray-300 bg-white text-gray-700 font-semibold text-sm hover:bg-gray-50 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </a>
    </div>


    <div class="w-full rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">


        <!-- FORM -->
        <form
            action="{{ route('guru.tugas.update', $tugas->id) }}"
            method="POST"
            enctype="multipart/form-data"
            id="formEditTugas"
            class="space-y-5 p-6 sm:p-8">

            @csrf
            @method('PUT')

            <input type="hidden" name="tipe" :value="jenisTugas">

            <!-- JUDUL TUGAS -->
            <div class="space-y-1.5">
                <label class="block text-sm font-semibold text-slate-700">
                    Judul Tugas <span class="text-red-500">*</span>
                </label>

                <input
                    type="text"
                    name="judul"
                    x-model="editData.judul"
                    required
                    placeholder="Contoh: Tugas Praktikum Pemrograman Web"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100">

                <p class="text-xs text-slate-400">
                    Masukkan judul tugas yang singkat dan mudah dipahami.
                </p>
            </div>

            <!-- DESKRIPSI TUGAS -->
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-slate-700">
                    Deskripsi Tugas
                    <span class="text-xs font-normal text-slate-400">(Opsional)</span>
                </label>

                <textarea
                    name="deskripsi"
                    rows="3"
                    x-model="editData.deskripsi"
                    placeholder="Tambahkan penjelasan atau instruksi pengerjaan tugas..."
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100"></textarea>
            </div>

            <!-- JENIS TUGAS -->
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-slate-700">
                    Jenis Tugas <span class="text-red-500">*</span>
                </label>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">

                    <!-- UPLOAD FILE -->
                    <button
                        type="button"
                        @click="jenisTugas='upload'"
                        :class="jenisTugas==='upload' ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-100' : 'border-slate-200 bg-white hover:border-blue-200 hover:bg-slate-50'"
                        class="rounded-xl border p-3 text-left transition">

                        <div class="flex items-start gap-3">
                            <div
                                :class="jenisTugas==='upload' ? 'bg-blue-100 text-blue-600' : 'bg-slate-100 text-slate-500'"
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 16V4m0 0L7 9m5-5 5 5M5 14v5a1 1 0 001 1h12a1 1 0 001-1v-5" />
                                </svg>
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-slate-800">
                                    Upload File
                                </p>
                                <p class="mt-1 text-xs leading-5 text-slate-500">
                                    Siswa mengumpulkan dokumen atau berkas tugas.
                                </p>
                            </div>

                            <svg
                                x-show="jenisTugas==='upload'"
                                class="h-5 w-5 shrink-0 text-blue-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m5 12 4 4L19 6" />
                            </svg>
                        </div>
                    </button>

                    <!-- PILIHAN GANDA -->
                    <button
                        type="button"
                        @click="jenisTugas='pilihan_ganda'"
                        :class="jenisTugas==='pilihan_ganda' ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-100' : 'border-slate-200 bg-white hover:border-blue-200 hover:bg-slate-50'"
                        class="rounded-xl border p-3 text-left transition">

                        <div class="flex items-start gap-3">
                            <div
                                :class="jenisTugas==='pilihan_ganda' ? 'bg-blue-100 text-blue-600' : 'bg-slate-100 text-slate-500'"
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5h10M9 12h10M9 19h10M4 5h.01M4 12h.01M4 19h.01" />
                                </svg>
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-slate-800">
                                    Pilihan Ganda
                                </p>
                                <p class="mt-1 text-xs leading-5 text-slate-500">
                                    Siswa mengerjakan soal dengan pilihan jawaban.
                                </p>
                            </div>

                            <svg
                                x-show="jenisTugas==='pilihan_ganda'"
                                class="h-5 w-5 shrink-0 text-blue-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m5 12 4 4L19 6" />
                            </svg>
                        </div>
                    </button>
                </div>
            </div>

            <div class="border-t border-slate-100"></div>

            <!-- MATA PELAJARAN DAN KELAS -->
            <div class="space-y-3">

                <div>
                    <label class="block text-sm font-semibold text-slate-800">
                        Mata Pelajaran & Kelas <span class="text-red-500">*</span>
                    </label>

                    <p class="mt-1 text-xs text-slate-400">
                        Pilih mata pelajaran dan kelas sasaran tugas ini.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-3 rounded-xl border border-slate-200 bg-slate-50/50 p-3 md:grid-cols-2">

                    <!-- MATA PELAJARAN -->
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-slate-700">
                            Mata Pelajaran <span class="text-red-500">*</span>
                        </label>

                        <select
                            id="editFilterMapel"
                            class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">

                            <option value="">-- Pilih Mata Pelajaran --</option>

                            @foreach($mapels as $mapel)
                            <option value="{{ (string) $mapel->id }}">
                                {{ $mapel->nama_mapel }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- KELAS -->
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-slate-700">
                            Kelas <span class="text-red-500">*</span>
                        </label>

                        <button
                            type="button"
                            id="editBtnBukaKelas"
                            class="flex w-full items-center justify-between rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-500 transition hover:border-blue-300">

                            <span id="editTeksPilihKelas">
                                Pilih Kelas
                            </span>

                            <svg
                                id="editIconKelas"
                                class="h-4 w-4 transition-transform"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7" />
                            </svg>
                        </button>
                    </div>

                    <!-- DROPDOWN KELAS -->
                    <div
                        id="editDropdownKelas"
                        class="hidden space-y-3 md:col-span-2">

                        <p
                            id="editPesanPilihMapel"
                            class="py-3 text-center text-xs font-medium text-slate-400">
                            Silakan pilih mata pelajaran terlebih dahulu.
                        </p>

                        <!-- FILTER TINGKAT -->
                        <select
                            id="editFilterTingkat"
                            class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">

                            <option value="">Semua Tingkat</option>
                        </select>

                        <!-- PILIH SEMUA -->
                        <label class="flex cursor-pointer items-center gap-3 rounded-lg bg-blue-50 p-3 transition hover:bg-blue-100/70">
                            <input
                                type="checkbox"
                                id="editPilihSemua"
                                class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">

                            <span class="text-sm font-semibold text-blue-700">
                                Pilih Semua
                            </span>
                        </label>

                        <!-- CHECKBOX KELAS -->
                        <div
                            id="editDaftarPengampu"
                            class="grid max-h-48 grid-cols-2 gap-2.5 overflow-y-auto sm:grid-cols-3">

                            @foreach($pengampuKelases as $pk)
                            <label
                                class="itemPengampuEdit flex cursor-pointer items-center rounded-xl border border-slate-200 bg-white p-3 transition hover:border-blue-300 hover:bg-blue-50/70"
                                data-tingkat="{{ preg_replace('/\D/', '', $pk->kelas?->nama_kelas ?? '') }}"
                                data-mapel="{{ (string) ($pk->guruMapel?->mapel_id ?? '') }}">

                                <input
                                    type="checkbox"
                                    name="pengampu_kelas_id[]"
                                    value="{{ $pk->id }}"
                                    class="checkboxPengampuEdit h-4 w-4 shrink-0 rounded border-slate-300 text-blue-600 focus:ring-blue-500">

                                <span class="ml-2.5 text-xs font-semibold text-slate-800">
                                    {{ $pk->kelas?->nama_kelas ?? 'Nama kelas tidak tersedia' }}
                                </span>
                            </label>
                            @endforeach
                        </div>

                        <p
                            id="editPesanKelasKosong"
                            class="hidden py-3 text-center text-xs text-slate-400">
                            Tidak ada kelas untuk mata pelajaran ini. Periksa data pengampu kelas.
                        </p>

                        <p
                            id="editJumlahTerpilih"
                            class="border-t border-slate-100 pt-3 text-xs font-medium text-slate-500">
                            0 kelas dipilih
                        </p>
                    </div>
                </div>
            </div>

            <!-- LAMPIRAN UPLOAD -->
            <template x-if="jenisTugas==='upload'">
                <div class="space-y-3">

                    <div>
                        <label class="block text-sm font-semibold text-slate-700">
                            Lampiran Lembar Kerja / Dokumen Pendukung
                        </label>

                        <p class="mt-1 text-xs text-slate-400">
                            Unggah dokumen pendukung untuk membantu siswa mengerjakan tugas.
                        </p>
                    </div>

                    <!-- CARD UPLOAD -->
                    <div class="relative overflow-hidden rounded-xl border-2 border-dashed border-blue-200 bg-slate-50 p-5 text-center transition hover:border-blue-400 hover:bg-blue-50/40">

                        <input
                            type="file"
                            name="file_tugas_input"
                            id="edit_file_tugas_input"
                            accept=".pdf,.docx,.jpg,.jpeg,.png"
                            @change="pilihFileEdit($event)"
                            class="absolute inset-0 z-10 h-full w-full cursor-pointer opacity-0">

                        <div class="pointer-events-none flex flex-col items-center">
                            <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 16V4m0 0L7 9m5-5 5 5M5 14v5a1 1 0 001 1h12a1 1 0 001-1v-5" />
                                </svg>
                            </div>

                            <p class="text-sm font-bold text-slate-800">
                                Klik atau tarik berkas ke sini
                            </p>

                            <p class="mt-2 text-xs text-slate-400">
                                Maksimal ukuran file: 10 MB (.pdf, .docx, .jpg, .png)
                            </p>

                            <p class="mt-3 text-xs font-semibold text-blue-600">
                                Pilih berkas dari perangkat
                            </p>
                        </div>
                    </div>

                    <!-- FILE LAMA / FILE BARU -->
                    <template x-if="hasFileEdit">
                        <div class="flex items-center justify-between gap-3 rounded-xl border border-blue-100 bg-blue-50 p-3">

                            <div class="flex min-w-0 items-center gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-blue-600">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 3h7l5 5v13H7a2 2 0 01-2-2V5a2 2 0 012-2zM14 3v5h5M9 13h6M9 17h6" />
                                    </svg>
                                </div>

                                <div class="min-w-0">
                                    <p
                                        class="truncate text-sm font-bold text-slate-800"
                                        x-text="fileNameEdit">
                                    </p>

                                    <p
                                        class="mt-1 text-xs text-slate-500"
                                        x-text="fileSizeEdit">
                                    </p>
                                </div>
                            </div>

                            <button
                                type="button"
                                @click="hapusLampiranEdit()"
                                class="relative z-20 shrink-0 rounded-lg p-2 text-red-500 transition hover:bg-red-50 hover:text-red-700">

                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </template>

                    <input
                        type="hidden"
                        name="hapus_lampiran"
                        id="hapus_lampiran_edit"
                        value="0">

                    <p class="text-xs text-slate-400">
                        Kosongkan jika tidak ingin mengganti lampiran sebelumnya.
                    </p>
                </div>
            </template>

            <!-- SOAL PILIHAN GANDA -->
            <template x-if="jenisTugas==='pilihan_ganda'">
                <div class="space-y-3">

                    <div class="border-b border-slate-100 pb-3">
                        <h4 class="text-sm font-bold text-slate-800">
                            Daftar Soal Pilihan Ganda
                        </h4>

                        <p class="mt-1 text-xs text-slate-500">
                            Tambahkan atau perbarui pertanyaan dan pilihan jawaban.
                        </p>
                    </div>

                    <!-- INFORMASI NILAI -->
                    <div class="rounded-xl border border-blue-100 bg-blue-50 p-3">
                        <div class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v3m0 4h.01M10.3 3.86L1.82 18.14A2 2 0 003.53 21h16.94a2 2 0 001.71-2.86L13.7 3.86a2 2 0 00-3.4 0z" />
                            </svg>

                            <div>
                                <p class="text-xs font-bold text-blue-800">
                                    Penilaian Otomatis
                                </p>

                                <p class="mt-1 text-xs leading-5 text-blue-700">
                                    Total nilai maksimal adalah 100. Bobot setiap soal akan dihitung otomatis berdasarkan jumlah soal yang dibuat.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- LIST SOAL -->
                    <template x-for="(soal,index) in editData.soals" :key="soal.id || index">
                        <div class="space-y-3 rounded-xl border border-slate-200 bg-white p-3">

                            <!-- HEADER SOAL -->
                            <div class="flex items-center justify-between">
                                <h5
                                    class="text-sm font-bold text-slate-800"
                                    x-text="'Pertanyaan '+(index+1)">
                                </h5>

                                <button
                                    type="button"
                                    x-show="editData.soals.length > 1"
                                    @click="hapusSoalEdit(index)"
                                    class="rounded-lg px-3 py-1.5 text-xs font-semibold text-red-500 transition hover:bg-red-50">
                                    Hapus Soal
                                </button>
                            </div>

                            <!-- ID SOAL -->
                            <input
                                type="hidden"
                                :name="'soal['+index+'][id]'"
                                :value="soal.id || ''">

                            <!-- PERTANYAAN -->
                            <div class="space-y-2">
                                <label class="block text-xs font-semibold text-slate-700">
                                    Pertanyaan
                                </label>

                                <textarea
                                    :name="'soal['+index+'][pertanyaan]'"
                                    x-model="soal.pertanyaan"
                                    required
                                    rows="3"
                                    placeholder="Tuliskan pertanyaan..."
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100"></textarea>
                            </div>

                            <!-- GAMBAR SOAL -->
                            <div class="space-y-2">
                                <label class="block text-xs font-semibold text-slate-700">
                                    Gambar Soal
                                    <span class="font-normal text-slate-400">(Opsional)</span>
                                </label>

                                <!-- GAMBAR LAMA -->
                                <template x-if="soal.gambar">
                                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                        <p class="mb-2 text-xs font-medium text-slate-500">
                                            Gambar yang sudah tersimpan
                                        </p>

                                        <img
                                            :src="'/storage/' + soal.gambar"
                                            alt="Gambar soal"
                                            class="max-h-48 rounded-lg object-contain">

                                        <p class="mt-2 text-xs text-slate-400">
                                            Upload gambar baru jika ingin mengganti gambar sebelumnya.
                                        </p>
                                    </div>
                                </template>

                                <!-- UPLOAD GAMBAR BARU -->
                                <input
                                    type="file"
                                    :name="'soal['+index+'][gambar]'"
                                    accept="image/png,image/jpeg"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-blue-100 file:px-3 file:py-2 file:text-xs file:font-bold file:text-blue-700">

                                <p class="text-xs text-slate-400">
                                    Format JPG, JPEG, atau PNG. Maksimal 2 MB.
                                </p>
                            </div>

                            <!-- PILIHAN JAWABAN -->
                            <div class="space-y-2">
                                <label class="block text-xs font-semibold text-slate-700">
                                    Pilihan Jawaban
                                    <span class="font-normal text-slate-400">(Minimal 2 pilihan)</span>
                                </label>

                                <template x-for="huruf in ['a','b','c','d','e']" :key="huruf">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-xs font-bold text-blue-700"
                                            x-text="huruf.toUpperCase()">
                                        </span>

                                        <input
                                            type="text"
                                            :name="'soal['+index+'][pilihan]['+huruf+']'"
                                            x-model="soal.pilihan[huruf]"
                                            :placeholder="'Pilihan '+huruf.toUpperCase()"
                                            class="min-w-0 flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100">
                                    </div>
                                </template>
                            </div>

                            <!-- KUNCI JAWABAN -->
                            <div class="space-y-2">
                                <label class="block text-xs font-semibold text-slate-700">
                                    Kunci Jawaban
                                </label>

                                <select
                                    :name="'soal['+index+'][kunci_jawaban]'"
                                    x-model="soal.kunci_jawaban"
                                    required
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100">

                                    <option value="">
                                        Pilih Kunci Jawaban
                                    </option>

                                    <template x-for="huruf in ['a','b','c','d','e']" :key="huruf">
                                        <option
                                            :value="huruf"
                                            :disabled="!soal.pilihan[huruf] || !soal.pilihan[huruf].trim()"
                                            x-text="huruf.toUpperCase()">
                                        </option>
                                    </template>
                                </select>
                            </div>
                        </div>
                    </template>

                    <!-- TAMBAH PERTANYAAN -->
                    <button
                        type="button"
                        @click="tambahSoalEdit()"
                        class="flex w-full items-center justify-center gap-2 rounded-xl border-2 border-dashed border-blue-200 py-2.5 text-sm font-bold text-blue-600 transition hover:border-blue-400 hover:bg-blue-50">

                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m-7-7h14" />
                        </svg>

                        Tambah Pertanyaan
                    </button>
                </div>
            </template>

            <!-- DEADLINE -->
            <div class="space-y-3">
                <div class="border-t border-slate-100 pt-4">
                    <label class="block text-sm font-semibold text-slate-700">
                        Batas Waktu Pengumpulan (Deadline)
                        <span class="text-red-500">*</span>
                    </label>

                    <p class="mt-1 text-xs text-slate-400">
                        Tentukan batas waktu pengerjaan tugas.
                    </p>
                </div>

                <input
                    type="datetime-local"
                    name="deadline"
                    id="edit_deadline"
                    x-model="editData.deadline"
                    required
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100">

                <p
                    id="editDeadlineError"
                    class="hidden text-xs text-red-500">
                    Deadline harus setelah waktu sekarang.
                </p>
            </div>

            <!-- TOMBOL AKSI -->
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">

                <!-- HAPUS TUGAS -->
                <form action="{{ route('guru.tugas.destroy', $tugas->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus tugas ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-xl border border-red-100 bg-red-50 px-5 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-100">Hapus Tugas</button>
                </form>

                <div class="flex flex-wrap gap-3">
                    <!-- BATAL -->
                    <a href="{{ route('guru.tugas') }}"
                        class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                        Batal
                    </a>

                    <!-- SIMPAN -->
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm shadow-blue-200 transition hover:bg-blue-700 active:scale-[0.98]">

                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>

                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
    <script>
        (function() {
            const mapel = document.getElementById('editFilterMapel');
            const tombol = document.getElementById('editBtnBukaKelas');
            const dropdown = document.getElementById('editDropdownKelas');
            const teksTombol = document.getElementById('editTeksPilihKelas');
            const icon = document.getElementById('editIconKelas');
            const tingkat = document.getElementById('editFilterTingkat');
            const pilihSemua = document.getElementById('editPilihSemua');
            const daftar = document.getElementById('editDaftarPengampu');
            const pesanMapel = document.getElementById('editPesanPilihMapel');
            const pesanKosong = document.getElementById('editPesanKelasKosong');
            const jumlah = document.getElementById('editJumlahTerpilih');
            const form = document.getElementById('formEditTugas');

            if (!mapel || !tombol || !dropdown || !daftar || !form) return;

            const semuaItem = Array.from(
                daftar.querySelectorAll('.itemPengampuEdit')
            );

            const semuaCheckbox = Array.from(
                daftar.querySelectorAll('.checkboxPengampuEdit')
            );

            // Buka/tutup daftar kelas
            tombol.addEventListener('click', function() {
                dropdown.classList.toggle('hidden');

                if (icon) {
                    icon.classList.toggle('rotate-180');
                }
            });

            // Ambil kelas sesuai mapel
            function itemSesuaiMapel() {
                return semuaItem.filter(item =>
                    item.dataset.mapel === mapel.value && mapel.value !== ''
                );
            }

            // Isi filter tingkat
            function isiTingkat(items) {
                const nilaiTingkat = [...new Set(
                    items.map(item => item.dataset.tingkat).filter(Boolean)
                )].sort((a, b) => Number(a) - Number(b));

                tingkat.innerHTML = '<option value="">Semua Tingkat</option>';

                nilaiTingkat.forEach(nilai => {
                    const option = document.createElement('option');
                    option.value = nilai;
                    option.textContent = 'Kelas ' + nilai;
                    tingkat.appendChild(option);
                });
            }

            // Update label jumlah kelas terpilih
            function updateJumlah() {
                const terpilih = semuaCheckbox.filter(cb => cb.checked);
                const total = terpilih.length;

                jumlah.textContent = total + ' kelas dipilih';

                if (total === 0) {
                    teksTombol.textContent = 'Pilih Kelas';
                } else {
                    const nama = terpilih.map(cb => {
                        const span = cb.closest('.itemPengampuEdit')
                            ?.querySelector('.namaKelasEdit');

                        return span ? span.textContent.trim() : 'Kelas';
                    });

                    teksTombol.textContent = total <= 2 ?
                        nama.join(', ') :
                        nama.slice(0, 2).join(', ') + ' + ' + (total - 2) + ' lainnya';
                }
            }

            // Filter tampilan kelas
            function tampilkanKelas() {
                const mapelId = mapel.value;
                const tingkatId = tingkat.value;
                let totalTampil = 0;

                semuaItem.forEach(item => {
                    const cocokMapel = item.dataset.mapel === mapelId;
                    const cocokTingkat = tingkatId === '' ||
                        item.dataset.tingkat === tingkatId;

                    const tampil = mapelId !== '' && cocokMapel && cocokTingkat;

                    item.classList.toggle('hidden', !tampil);

                    if (tampil) totalTampil++;
                });

                pesanMapel.classList.toggle('hidden', mapelId !== '');

                tingkat.classList.toggle('hidden', mapelId === '');
                pilihSemua.closest('label').classList.toggle('hidden', mapelId === '');

                pesanKosong.classList.toggle(
                    'hidden',
                    mapelId === '' || totalTampil > 0
                );

                const itemTampil = semuaItem.filter(item =>
                    !item.classList.contains('hidden')
                );

                pilihSemua.checked = itemTampil.length > 0 &&
                    itemTampil.every(item =>
                        item.querySelector('.checkboxPengampuEdit').checked
                    );

                updateJumlah();
            }

            // Reset filter tanpa menghapus kelas yang sudah terpilih
            window.resetFilterKelasEdit = function() {
                tingkat.value = '';
                isiTingkat(itemSesuaiMapel());
                tampilkanKelas();
            };

            // Update label jumlah dari Alpine
            window.updateJumlahEdit = updateJumlah;

            // Set mapel berdasarkan ID kelas yang tersimpan
            window.setMapelKelasEdit = function(ids) {
                const selectedIds = (ids || []).map(String);

                const selectedItems = semuaItem.filter(item => {
                    const cb = item.querySelector('.checkboxPengampuEdit');
                    return cb && selectedIds.includes(String(cb.value));
                });

                if (selectedItems.length > 0) {
                    mapel.value = selectedItems[0].dataset.mapel || '';
                }

                isiTingkat(itemSesuaiMapel());
                tampilkanKelas();
            };

            // Perubahan mapel
            mapel.addEventListener('change', function() {
                semuaCheckbox.forEach(cb => cb.checked = false);
                tingkat.value = '';

                isiTingkat(itemSesuaiMapel());
                tampilkanKelas();
            });

            // Perubahan filter tingkat
            tingkat.addEventListener('change', tampilkanKelas);

            // Pilih semua kelas yang sedang tampil
            pilihSemua.addEventListener('change', function() {
                semuaItem.forEach(item => {
                    if (!item.classList.contains('hidden')) {
                        item.querySelector('.checkboxPengampuEdit').checked =
                            pilihSemua.checked;
                    }
                });

                updateJumlah();
            });

            // Checkbox satu per satu
            semuaCheckbox.forEach(cb => {
                cb.addEventListener('change', function() {
                    updateJumlah();

                    const itemTampil = semuaItem.filter(item =>
                        !item.classList.contains('hidden')
                    );

                    pilihSemua.checked = itemTampil.length > 0 &&
                        itemTampil.every(item =>
                            item.querySelector('.checkboxPengampuEdit').checked
                        );
                });
            });

            // Validasi sebelum simpan
            form.addEventListener('submit', function(event) {
                const terpilih = semuaCheckbox.filter(cb => cb.checked);

                if (!mapel.value) {
                    event.preventDefault();
                    alert('Silakan pilih mata pelajaran terlebih dahulu.');
                    mapel.focus();
                    return;
                }

                if (terpilih.length === 0) {
                    event.preventDefault();
                    alert('Silakan pilih minimal satu kelas.');
                    dropdown.classList.remove('hidden');
                    tombol.focus();
                    return;
                }

                const bedaMapel = terpilih.some(cb =>
                    cb.closest('.itemPengampuEdit').dataset.mapel !== mapel.value
                );

                if (bedaMapel) {
                    event.preventDefault();
                    alert('Kelas yang dipilih harus berasal dari mata pelajaran yang sama.');
                }
            });

            // Awal
            isiTingkat(itemSesuaiMapel());
            tampilkanKelas();
        })();
    </script>
    @endsection