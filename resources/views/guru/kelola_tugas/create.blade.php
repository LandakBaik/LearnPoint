@extends('layouts.app')

@section('title', 'Tambah Tugas')

@section('content')
<div class="space-y-6" x-data="{
    jenisTugas: 'upload',
    daftarSoal: [{ pertanyaan: '', pilihan: { a: '', b: '', c: '', d: '', e: '' }, kunci_jawaban: '' }],
    hasFile: false,
    fileName: '',
    fileSize: '',
    handleFileChange(event) {
        const file = event.target.files[0];
        if (file) {
            this.fileName = file.name;
            this.fileSize = (file.size / (1024 * 1024)).toFixed(1) + ' MB • Siap diunggah';
            this.hasFile = true;
        } else {
            this.hasFile = false;
            this.fileName = '';
            this.fileSize = '';
        }
    },
    tambahSoal() {
        this.daftarSoal.push({ pertanyaan: '', pilihan: { a: '', b: '', c: '', d: '', e: '' }, kunci_jawaban: '' });
    },
    hapusSoal(index) {
        if (this.daftarSoal.length > 1) this.daftarSoal.splice(index, 1);
    }
}">
    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Tambah Tugas</h1>
            <p class="text-sm text-gray-500 mt-1">Buat tugas baru dan tentukan judul, kelas, jenis tugas, serta batas waktu.</p>
        </div>
        <a href="{{ route('guru.tugas') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-gray-300 bg-white text-gray-700 font-semibold text-sm hover:bg-gray-50 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </a>
    </div>


    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <form action="{{ route('guru.tugas.store') }}" method="POST" enctype="multipart/form-data" id="formBuatTugas" class="space-y-6 p-6 sm:p-8">
            @csrf
            <input type="hidden" name="tipe" :value="jenisTugas">

            <!-- JUDUL TUGAS -->
            <div class="space-y-1.5">
                <label class="block text-sm font-semibold text-slate-700">Judul Tugas <span class="text-red-500">*</span></label>
                <input type="text" name="judul" required placeholder="Contoh: Tugas Praktikum Pemrograman Web" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100">
                <p class="text-xs text-slate-400">Masukkan judul tugas yang singkat dan mudah dipahami.</p>
            </div>

            <!-- DESKRIPSI TUGAS -->
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-slate-700">Deskripsi Tugas <span class="text-xs font-normal text-slate-400">(Opsional)</span></label>
                <textarea name="deskripsi" rows="3" placeholder="Tambahkan penjelasan atau instruksi pengerjaan tugas..." class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100"></textarea>
            </div>

            <!-- JENIS TUGAS -->
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-slate-700">Jenis Tugas <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <!-- UPLOAD FILE -->
                    <button type="button" @click="jenisTugas='upload'" :class="jenisTugas==='upload' ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-100' : 'border-slate-200 bg-white hover:border-blue-200 hover:bg-slate-50'" class="rounded-xl border p-3 text-left transition">
                        <div class="flex items-start gap-3">
                            <div :class="jenisTugas==='upload' ? 'bg-blue-100 text-blue-600' : 'bg-slate-100 text-slate-500'" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 16V4m0 0L7 9m5-5 5 5M5 14v5a1 1 0 001 1h12a1 1 0 001-1v-5" />
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-slate-800">Upload File</p>
                                <p class="mt-1 text-xs leading-5 text-slate-500">Siswa mengumpulkan dokumen atau berkas tugas.</p>
                            </div>
                            <svg x-show="jenisTugas==='upload'" class="h-5 w-5 shrink-0 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m5 12 4 4L19 6" />
                            </svg>
                        </div>
                    </button>

                    <!-- PILIHAN GANDA -->
                    <button type="button" @click="jenisTugas='pilihan_ganda'" :class="jenisTugas==='pilihan_ganda' ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-100' : 'border-slate-200 bg-white hover:border-blue-200 hover:bg-slate-50'" class="rounded-xl border p-3 text-left transition">
                        <div class="flex items-start gap-3">
                            <div :class="jenisTugas==='pilihan_ganda' ? 'bg-blue-100 text-blue-600' : 'bg-slate-100 text-slate-500'" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5h10M9 12h10M9 19h10M4 5h.01M4 12h.01M4 19h.01" />
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-slate-800">Pilihan Ganda</p>
                                <p class="mt-1 text-xs leading-5 text-slate-500">Siswa mengerjakan soal dengan pilihan jawaban.</p>
                            </div>
                            <svg x-show="jenisTugas==='pilihan_ganda'" class="h-5 w-5 shrink-0 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                    <label class="block text-sm font-semibold text-slate-800">Mata Pelajaran & Kelas <span class="text-red-500">*</span></label>
                    <p class="mt-1 text-xs text-slate-400">Pilih mata pelajaran dan kelas sasaran tugas ini.</p>
                </div>

                <div class="grid grid-cols-1 gap-3 rounded-xl border border-slate-200 bg-slate-50/50 p-3 md:grid-cols-2">
                    <!-- MATA PELAJARAN -->
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-slate-700">Mata Pelajaran <span class="text-red-500">*</span></label>
                        <select id="filterMapel" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            @foreach($mapels as $mapel)
                            <option value="{{ (string) $mapel->id }}">{{ $mapel->nama_mapel }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- KELAS -->
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-slate-700">Kelas <span class="text-red-500">*</span></label>
                        <button type="button" id="btnBukaKelas" class="flex w-full items-center justify-between rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-500 transition hover:border-blue-300">
                            <span id="teksPilihKelas">Pilih Kelas</span>
                            <svg id="iconKelas" class="h-4 w-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7" />
                            </svg>
                        </button>
                    </div>

                    <!-- DROPDOWN KELAS -->
                    <div id="dropdownKelas" class="hidden space-y-3 md:col-span-2">
                        <p id="pesanPilihMapel" class="py-3 text-center text-xs font-medium text-slate-400">Silakan pilih mata pelajaran terlebih dahulu.</p>

                        <!-- FILTER TINGKAT -->
                        <select id="filterTingkat" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="">Semua Tingkat</option>
                        </select>

                        <!-- PILIH SEMUA -->
                        <label class="flex cursor-pointer items-center gap-3 rounded-lg bg-blue-50 p-3 transition hover:bg-blue-100/70">
                            <input type="checkbox" id="pilihSemua" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm font-semibold text-blue-700">Pilih Semua</span>
                        </label>

                        <!-- CHECKBOX KELAS HORIZONTAL -->
                        <div id="daftarPengampu" class="grid max-h-48 grid-cols-2 gap-2.5 overflow-y-auto sm:grid-cols-3">
                            @foreach($pengampuKelases as $pk)
                            <label class="item-pengampu flex cursor-pointer items-center rounded-xl border border-slate-200 bg-white p-3 transition hover:border-blue-300 hover:bg-blue-50/70" data-tingkat="{{ preg_replace('/\D/', '', $pk->kelas?->nama_kelas ?? '') }}" data-mapel="{{ (string) ($pk->guruMapel?->mapel_id ?? '') }}">
                                <input type="checkbox" name="pengampu_kelas_id[]" value="{{ $pk->id }}" class="checkbox-pengampu h-4 w-4 shrink-0 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2.5 text-xs font-semibold text-slate-800">{{ $pk->kelas?->nama_kelas ?? 'Nama kelas tidak tersedia' }}</span>
                            </label>
                            @endforeach
                        </div>

                        <p id="pesanKelasKosong" class="hidden py-3 text-center text-xs text-slate-400">Tidak ada kelas untuk mata pelajaran ini. Periksa data pengampu kelas.</p>
                        <p id="jumlahTerpilih" class="border-t border-slate-100 pt-3 text-xs font-medium text-slate-500">0 kelas dipilih</p>
                    </div>
                </div>
            </div>

            <!-- LAMPIRAN UPLOAD -->
            <template x-if="jenisTugas==='upload'">
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Lampiran Lembar Kerja / Dokumen Pendukung</label>
                        <p class="mt-1 text-xs text-slate-400">Unggah dokumen pendukung untuk membantu siswa mengerjakan tugas.</p>
                    </div>

                    <div class="relative overflow-hidden rounded-xl border-2 border-dashed border-blue-200 bg-slate-50 p-5 text-center transition hover:border-blue-400 hover:bg-blue-50/40">
                        <input type="file" name="file_tugas_input" id="file_tugas_input" accept=".pdf,.docx,.jpg,.jpeg,.png" @change="handleFileChange($event)" class="absolute inset-0 z-10 h-full w-full cursor-pointer opacity-0">
                        <div class="pointer-events-none flex flex-col items-center">
                            <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 16V4m0 0L7 9m5-5 5 5M5 14v5a1 1 0 001 1h12a1 1 0 001-1v-5" />
                                </svg>
                            </div>
                            <p class="text-sm font-bold text-slate-800">Klik atau tarik berkas ke sini</p>
                            <p class="mt-2 text-xs text-slate-400">Maksimal ukuran file: 10 MB (.pdf, .docx, .jpg, .png)</p>
                            <p class="mt-3 text-xs font-semibold text-blue-600">Pilih berkas dari perangkat</p>
                        </div>
                    </div>

                    <template x-if="hasFile">
                        <div class="flex items-center justify-between gap-3 rounded-xl border border-blue-100 bg-blue-50 p-3">
                            <div class="flex min-w-0 items-center gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-blue-600">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 3h7l5 5v13H7a2 2 0 01-2-2V5a2 2 0 012-2zM14 3v5h5M9 13h6M9 17h6" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold text-slate-800" x-text="fileName"></p>
                                    <p class="mt-1 text-xs text-slate-500" x-text="fileSize"></p>
                                </div>
                            </div>
                            <button type="button" @click="hasFile=false; fileName=''; fileSize=''; document.getElementById('file_tugas_input').value=''" class="relative z-20 shrink-0 rounded-lg p-2 text-red-500 transition hover:bg-red-50 hover:text-red-700">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>
            </template>


            <!-- SOAL PILIHAN GANDA -->
            <template x-if="jenisTugas==='pilihan_ganda'">
                <div class="space-y-3">
                    <div class="border-b border-slate-100 pb-3">
                        <h4 class="text-sm font-bold text-slate-800">Daftar Soal Pilihan Ganda</h4>
                        <p class="mt-1 text-xs text-slate-500">Tambahkan pertanyaan dan pilihan jawaban.</p>
                    </div>

                    <div class="rounded-xl border border-blue-100 bg-blue-50 p-3">
                        <div class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v3m0 4h.01M10.3 3.86L1.82 18.14A2 2 0 003.53 21h16.94a2 2 0 001.71-2.86L13.7 3.86a2 2 0 00-3.4 0z" />
                            </svg>
                            <div>
                                <p class="text-xs font-bold text-blue-800">Penilaian Otomatis</p>
                                <p class="mt-1 text-xs leading-5 text-blue-700">Total nilai maksimal adalah 100. Bobot setiap soal akan dihitung otomatis berdasarkan jumlah soal yang dibuat.</p>
                            </div>
                        </div>
                    </div>

                    <template x-for="(soal,index) in daftarSoal" :key="index">
                        <div class="space-y-3 rounded-xl border border-slate-200 bg-white p-3">
                            <div class="flex items-center justify-between">
                                <h5 class="text-sm font-bold text-slate-800" x-text="'Pertanyaan '+(index+1)"></h5>
                                <button type="button" x-show="daftarSoal.length>1" @click="hapusSoal(index)" class="rounded-lg px-3 py-1.5 text-xs font-semibold text-red-500 transition hover:bg-red-50">Hapus Soal</button>
                            </div>

                            <!-- PERTANYAAN -->
                            <div class="space-y-2">
                                <label class="block text-xs font-semibold text-slate-700">Pertanyaan</label>
                                <textarea :name="'soal['+index+'][pertanyaan]'" x-model="soal.pertanyaan" required rows="3" placeholder="Tuliskan pertanyaan..." class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100"></textarea>
                            </div>

                            <!-- UPLOAD GAMBAR SOAL (OPSIONAL) -->
                            <div class="space-y-2">
                                <label class="block text-xs font-semibold text-slate-700">
                                    Gambar Soal
                                    <span class="font-normal text-slate-400">(Opsional)</span>
                                </label>

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
                                <label class="block text-xs font-semibold text-slate-700">Pilihan Jawaban <span class="font-normal text-slate-400">(Minimal 2 pilihan)</span></label>
                                <template x-for="huruf in ['a','b','c','d','e']" :key="huruf">
                                    <div class="flex items-center gap-2">
                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-xs font-bold text-blue-700" x-text="huruf.toUpperCase()"></span>
                                        <input type="text" :name="'soal['+index+'][pilihan]['+huruf+']'" x-model="soal.pilihan[huruf]" :placeholder="'Pilihan '+huruf.toUpperCase()" class="min-w-0 flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100">
                                    </div>
                                </template>
                            </div>

                            <!-- KUNCI JAWABAN -->
                            <div class="space-y-2">
                                <label class="block text-xs font-semibold text-slate-700">Kunci Jawaban</label>
                                <select :name="'soal['+index+'][kunci_jawaban]'" x-model="soal.kunci_jawaban" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100">
                                    <option value="">Pilih Kunci Jawaban</option>
                                    <template x-for="huruf in ['a','b','c','d','e']" :key="huruf">
                                        <option :value="huruf" :disabled="!soal.pilihan[huruf] || !soal.pilihan[huruf].trim()" x-text="huruf.toUpperCase()"></option>
                                    </template>
                                </select>
                            </div>

                            <!-- TAMBAH PERTANYAAN -->
                            <button type="button" @click="tambahSoal()" class="flex w-full items-center justify-center gap-2 rounded-xl border-2 border-dashed border-blue-200 py-2.5 text-sm font-bold text-blue-600 transition hover:border-blue-400 hover:bg-blue-50">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m-7-7h14" />
                                </svg>
                                Tambah Pertanyaan
                            </button>
                        </div>
                    </template>
                </div>
            </template>


            <!-- DEADLINE -->
            <div class="space-y-3">
                <div class="border-t border-slate-100 pt-4">
                    <label class="block text-sm font-semibold text-slate-700">Batas Waktu Pengumpulan (Deadline) <span class="text-red-500">*</span></label>
                    <p class="mt-1 text-xs text-slate-400">Tentukan batas waktu pengerjaan tugas.</p>
                </div>
                <input type="datetime-local" name="deadline" id="deadline" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100">
                <p id="deadlineError" class="hidden text-xs text-red-500">Deadline harus setelah waktu sekarang.</p>
            </div>

            <!-- TOMBOL AKSI -->
            <div class="flex flex-wrap items-center justify-end gap-3 border-t border-slate-100 pt-4">
                <a href="{{ route('guru.tugas') }}" class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">Batal</a>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm shadow-blue-200 transition hover:bg-blue-700 active:scale-[0.98]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Simpan Tugas
                </button>
            </div>
        </form>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterMapel = document.getElementById('filterMapel');
        const btnBukaKelas = document.getElementById('btnBukaKelas');
        const dropdownKelas = document.getElementById('dropdownKelas');
        const teksPilihKelas = document.getElementById('teksPilihKelas');
        const iconKelas = document.getElementById('iconKelas');
        const filterTingkat = document.getElementById('filterTingkat');
        const pilihSemua = document.getElementById('pilihSemua');
        const daftarPengampu = document.getElementById('daftarPengampu');
        const pesanPilihMapel = document.getElementById('pesanPilihMapel');
        const pesanKelasKosong = document.getElementById('pesanKelasKosong');
        const jumlahTerpilih = document.getElementById('jumlahTerpilih');
        const form = document.getElementById('formBuatTugas');

        if (!filterMapel || !btnBukaKelas || !dropdownKelas || !form) {
            console.error('Elemen pemilih kelas tidak ditemukan.');
            return;
        }

        const semuaItem = Array.from(
            daftarPengampu.querySelectorAll('.item-pengampu')
        );

        const semuaCheckbox = Array.from(
            daftarPengampu.querySelectorAll('.checkbox-pengampu')
        );

        // Membuka dan menutup dropdown kelas
        btnBukaKelas.addEventListener('click', function() {
            dropdownKelas.classList.toggle('hidden');

            if (iconKelas) {
                iconKelas.classList.toggle('rotate-180');
            }
        });

        // Mengambil kelas berdasarkan mata pelajaran yang dipilih
        function getItemMapel() {
            const mapelId = filterMapel.value;

            return semuaItem.filter(item => {
                return item.dataset.mapel === mapelId && mapelId !== '';
            });
        }

        // Mengisi pilihan tingkat berdasarkan kelas dari mapel terpilih
        function isiFilterTingkat(items) {
            const tingkat = [
                ...new Set(
                    items
                    .map(item => item.dataset.tingkat)
                    .filter(value => value !== '')
                )
            ].sort((a, b) => Number(a) - Number(b));

            filterTingkat.innerHTML = '<option value="">Semua Tingkat</option>';

            tingkat.forEach(value => {
                const option = document.createElement('option');
                option.value = value;
                option.textContent = 'Kelas ' + value;
                filterTingkat.appendChild(option);
            });
        }

        // Memperbarui tampilan checkbox kelas
        function tampilkanKelas() {
            const mapelId = filterMapel.value;
            const tingkatId = filterTingkat.value;

            const itemMapel = getItemMapel();

            pesanPilihMapel.classList.toggle('hidden', mapelId !== '');

            if (mapelId === '') {
                filterTingkat.classList.add('hidden');
                pilihSemua.closest('label').classList.add('hidden');
            } else {
                filterTingkat.classList.remove('hidden');
                pilihSemua.closest('label').classList.remove('hidden');
            }

            let jumlahTampil = 0;

            semuaItem.forEach(item => {
                const sesuaiMapel = item.dataset.mapel === mapelId;
                const sesuaiTingkat = tingkatId === '' ||
                    item.dataset.tingkat === tingkatId;

                const tampil = mapelId !== '' && sesuaiMapel && sesuaiTingkat;

                item.classList.toggle('hidden', !tampil);

                if (tampil) {
                    jumlahTampil++;
                }
            });

            pesanKelasKosong.classList.toggle(
                'hidden',
                mapelId === '' || jumlahTampil > 0
            );

            // Pilih semua hanya untuk kelas yang sedang ditampilkan
            pilihSemua.checked = jumlahTampil > 0 &&
                semuaItem
                .filter(item => !item.classList.contains('hidden'))
                .every(item => item.querySelector('input').checked);

            perbaruiJumlah();
        }

        // Menghitung kelas terpilih dan memperbarui label tombol
        function perbaruiJumlah() {
            const checkboxTerpilih = semuaCheckbox.filter(cb => cb.checked);

            const jumlah = checkboxTerpilih.length;

            jumlahTerpilih.textContent = jumlah + ' kelas dipilih';

            if (jumlah === 0) {
                teksPilihKelas.textContent = 'Pilih Kelas';
                teksPilihKelas.classList.add('text-slate-500');
                teksPilihKelas.classList.remove('text-blue-700');
            } else {
                const namaKelas = checkboxTerpilih.map(cb => {
                    return cb.closest('.item-pengampu')
                        .querySelector('span.ml-2\\.5')
                        ?.textContent.trim() || 'Kelas';
                });

                teksPilihKelas.textContent = namaKelas.length <= 2 ?
                    namaKelas.join(', ') :
                    namaKelas.slice(0, 2).join(', ') +
                    ' + ' + (namaKelas.length - 2) + ' lainnya';

                teksPilihKelas.classList.remove('text-slate-500');
                teksPilihKelas.classList.add('text-blue-700');
            }
        }

        // Perubahan mata pelajaran
        filterMapel.addEventListener('change', function() {
            // Reset pilihan kelas sebelumnya saat mapel berubah
            semuaCheckbox.forEach(cb => cb.checked = false);

            filterTingkat.value = '';

            isiFilterTingkat(getItemMapel());
            tampilkanKelas();
        });

        // Filter berdasarkan tingkat
        filterTingkat.addEventListener('change', function() {
            tampilkanKelas();
        });

        // Pilih semua kelas yang terlihat
        pilihSemua.addEventListener('change', function() {
            const itemTerlihat = semuaItem.filter(item => {
                return !item.classList.contains('hidden');
            });

            itemTerlihat.forEach(item => {
                item.querySelector('.checkbox-pengampu').checked = pilihSemua.checked;
            });

            perbaruiJumlah();
        });

        // Ketika checkbox kelas dipilih satu per satu
        semuaCheckbox.forEach(cb => {
            cb.addEventListener('change', function() {
                perbaruiJumlah();

                const itemTerlihat = semuaItem.filter(item => {
                    return !item.classList.contains('hidden');
                });

                pilihSemua.checked = itemTerlihat.length > 0 &&
                    itemTerlihat.every(item => {
                        return item.querySelector('.checkbox-pengampu').checked;
                    });
            });
        });

        // Validasi sebelum tugas disimpan
        form.addEventListener('submit', function(event) {
            const mapelId = filterMapel.value;

            const kelasTerpilih = semuaCheckbox.filter(cb => cb.checked);

            if (!mapelId) {
                event.preventDefault();
                alert('Silakan pilih mata pelajaran terlebih dahulu.');
                filterMapel.focus();
                return;
            }

            if (kelasTerpilih.length === 0) {
                event.preventDefault();
                alert('Silakan pilih minimal satu kelas.');
                dropdownKelas.classList.remove('hidden');
                filterTingkat.classList.remove('hidden');
                btnBukaKelas.focus();
                return;
            }

            // Memastikan kelas yang dipilih berasal dari mapel yang sama
            const kelasTidakSesuai = kelasTerpilih.some(cb => {
                return cb.closest('.item-pengampu').dataset.mapel !== mapelId;
            });

            if (kelasTidakSesuai) {
                event.preventDefault();
                alert('Kelas yang dipilih harus berasal dari mata pelajaran yang sama.');
            }
        });

        // Inisialisasi awal
        isiFilterTingkat(getItemMapel());
        tampilkanKelas();
    });
</script>
@endsection