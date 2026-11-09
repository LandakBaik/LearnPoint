
<!-- Modal Buat Tugas Baru -->
<div
    x-show="showModal"
    x-cloak
    style="display: none;"
    @keydown.escape.window="showModal = false"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">

    <!-- Backdrop -->
    <div
        x-show="showModal"
        x-transition.opacity
        @click="showModal = false"
        class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm">
    </div>

    <!-- Modal -->
    <div
        x-show="showModal"
        x-transition
        class="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto bg-white rounded-2xl shadow-2xl border border-slate-100 z-10">

        <!-- Header -->
        <div class="sticky top-0 z-20 bg-indigo-50 p-6 border-b border-indigo-100 flex justify-between items-start">
            <div>
                <h3 class="text-xl font-extrabold text-slate-900">
                    Buat Tugas Baru
                </h3>

                <p class="text-xs text-slate-500 mt-1">
                    Isi formulir penugasan untuk siswa kelas yang Anda ampu.
                </p>
            </div>

            <button
                type="button"
                @click="showModal = false"
                class="w-8 h-8 rounded-full text-slate-400 hover:bg-slate-100 flex items-center justify-center">
                ✕
            </button>
        </div>

        <!-- Form -->
        <form
            action="{{ route('guru.tugas.store') }}"
            method="POST"
            enctype="multipart/form-data"
            id="formBuatTugas"
            class="p-6 space-y-5">

            @csrf

            <input type="hidden" name="tipe" :value="jenisTugas">

            <!-- Judul -->
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700">
                    Judul Tugas <span class="text-red-500">*</span>
                </label>

                <input
                    type="text"
                    name="judul"
                    required
                    placeholder="Masukkan judul tugas"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <!-- Jenis Tugas -->
            <div class="space-y-2">
                <label class="block text-xs font-bold text-slate-700">
                    Jenis Tugas <span class="text-red-500">*</span>
                </label>

                <div class="grid grid-cols-2 gap-3">
                    <button
                        type="button"
                        @click="jenisTugas = 'upload'"
                        :class="jenisTugas === 'upload'
                            ? 'border-indigo-500 bg-indigo-50 text-indigo-700'
                            : 'border-slate-200 bg-white text-slate-600'"
                        class="p-4 rounded-xl border-2 text-sm font-bold">

                        Upload File

                        <p class="text-xs font-normal mt-1">
                            Siswa mengumpulkan dokumen
                        </p>
                    </button>

                    <button
                        type="button"
                        @click="jenisTugas = 'pilihan_ganda'"
                        :class="jenisTugas === 'pilihan_ganda'
                            ? 'border-indigo-500 bg-indigo-50 text-indigo-700'
                            : 'border-slate-200 bg-white text-slate-600'"
                        class="p-4 rounded-xl border-2 text-sm font-bold">

                        Pilihan Ganda

                        <p class="text-xs font-normal mt-1">
                            Siswa menjawab soal
                        </p>
                    </button>
                </div>
            </div>

            <!-- Mata Pelajaran dan Kelas -->
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700">
                    Mata Pelajaran & Kelas <span class="text-red-500">*</span>
                </label>

                <select
                    name="pengampu_kelas_id"
                    required
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">

                    <option value="" disabled selected>
                        -- Pilih Mapel & Kelas --
                    </option>

                    @foreach($pengampuKelases as $pk)
                        <option value="{{ $pk->id }}">
                            {{ $pk->guruMapel?->mapel?->nama_mapel ?? 'Mapel' }}
                            -
                            {{ $pk->kelas?->nama_kelas ?? 'Kelas' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Deskripsi -->
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700">
                    Deskripsi Tugas
                </label>

                <textarea
                    name="deskripsi"
                    rows="3"
                    placeholder="Tambahkan deskripsi tugas..."
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
            </div>

            <!-- Lampiran Upload -->
            <template x-if="jenisTugas === 'upload'">
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700">
                        Lampiran Lembar Kerja / Dokumen Pendukung
                    </label>

                    <div class="border-2 border-dashed border-slate-200 bg-slate-50 rounded-xl p-5 text-center">
                        <p class="text-sm font-semibold text-slate-700">
                            Pilih berkas untuk dilampirkan
                        </p>

                        <input
                            type="file"
                            name="file_tugas_input"
                            id="file_tugas_input"
                            accept=".pdf,.docx,.jpg,.jpeg,.png"
                            @change="handleFileChange($event)"
                            class="w-full mt-3 text-xs">

                        <p class="text-[11px] text-slate-400 mt-2">
                            PDF, DOCX, JPG, PNG. Maksimal 10 MB.
                        </p>
                    </div>

                    <template x-if="hasFile">
                        <div class="bg-indigo-50 rounded-xl p-3 flex justify-between items-center">
                            <div>
                                <p class="text-xs font-bold text-slate-800" x-text="fileName"></p>
                                <p class="text-xs text-slate-500" x-text="fileSize"></p>
                            </div>

                            <button
                                type="button"
                                @click="hasFile = false; fileName = ''; fileSize = ''; document.getElementById('file_tugas_input').value = ''"
                                class="text-red-500 text-xs font-bold">
                                Hapus
                            </button>
                        </div>
                    </template>
                </div>
            </template>

            <!-- Soal Pilihan Ganda -->
            <template x-if="jenisTugas === 'pilihan_ganda'">
                <div class="space-y-4">

                    <div>
                        <h4 class="text-sm font-bold text-slate-800">
                            Daftar Soal Pilihan Ganda
                        </h4>

                        <p class="text-xs text-slate-500 mt-1">
                            Tambahkan pertanyaan dan pilihan jawaban.
                        </p>
                    </div>

                    <!-- Informasi Bobot -->
                    <div class="rounded-xl border border-indigo-100 bg-indigo-50 p-3">
                        <p class="text-xs font-semibold text-indigo-800">
                            Penilaian Otomatis
                        </p>

                        <p class="mt-1 text-xs text-indigo-700">
                            Total nilai maksimal adalah 100. Bobot setiap soal akan dihitung otomatis berdasarkan jumlah soal yang dibuat.
                        </p>
                    </div>

                    <template x-for="(soal, index) in daftarSoal" :key="index">
                        <div class="border border-slate-200 rounded-xl p-4 space-y-3">

                            <div class="flex justify-between items-center">
                                <h5
                                    class="text-sm font-bold text-indigo-700"
                                    x-text="'Pertanyaan ' + (index + 1)">
                                </h5>

                                <button
                                    type="button"
                                    x-show="daftarSoal.length > 1"
                                    @click="hapusSoal(index)"
                                    class="text-xs text-red-500 font-bold">
                                    Hapus Soal
                                </button>
                            </div>

                            <!-- Pertanyaan -->
                            <div>
                                <label class="block text-xs font-semibold mb-1">
                                    Pertanyaan
                                </label>

                                <textarea
                                    :name="'soal[' + index + '][pertanyaan]'"
                                    x-model="soal.pertanyaan"
                                    required
                                    rows="3"
                                    placeholder="Tuliskan pertanyaan..."
                                    class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
                            </div>

                            <!-- Pilihan Jawaban -->
                            <div class="space-y-2">
                                <label class="block text-xs font-semibold">
                                    Pilihan Jawaban
                                </label>

                                <template x-for="huruf in ['a', 'b', 'c', 'd', 'e']" :key="huruf">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="w-8 h-8 shrink-0 rounded-lg bg-indigo-50 text-indigo-700 flex items-center justify-center text-xs font-bold"
                                            x-text="huruf.toUpperCase()">
                                        </span>

                                        <input
                                            type="text"
                                            :name="'soal[' + index + '][pilihan][' + huruf + ']'"
                                            x-model="soal.pilihan[huruf]"
                                            required
                                            :placeholder="'Pilihan ' + huruf.toUpperCase()"
                                            class="flex-1 min-w-0 px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                    </div>
                                </template>
                            </div>

                            <!-- Kunci Jawaban -->
                            <div>
                                <label class="block text-xs font-semibold mb-1">
                                    Kunci Jawaban
                                </label>

                                <select
                                    :name="'soal[' + index + '][kunci_jawaban]'"
                                    x-model="soal.kunci_jawaban"
                                    required
                                    class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">

                                    <option value="a">A</option>
                                    <option value="b">B</option>
                                    <option value="c">C</option>
                                    <option value="d">D</option>
                                    <option value="e">E</option>
                                </select>
                            </div>

                        </div>
                    </template>

                    <!-- Tambah Soal -->
                    <button
                        type="button"
                        @click="tambahSoal()"
                        class="w-full py-3 rounded-xl border-2 border-dashed border-indigo-300 text-indigo-700 text-sm font-bold hover:bg-indigo-50">
                        + Tambah Pertanyaan
                    </button>

                </div>
            </template>

            <!-- Deadline -->
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700">
                    Batas Waktu Pengumpulan (Deadline)
                    <span class="text-red-500">*</span>
                </label>

                <input
                    type="datetime-local"
                    name="deadline"
                    id="deadline"
                    required
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">

                <p id="deadlineError" class="hidden text-xs text-red-500 mt-2">
                    Deadline harus setelah waktu sekarang.
                </p>

                <p class="text-[11px] text-slate-400">
                    Tentukan batas waktu pengerjaan tugas.
                </p>
            </div>

            <!-- Tombol -->
            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <button
                    type="button"
                    @click="showModal = false"
                    class="px-6 py-2.5 rounded-full border border-slate-300 text-slate-700 text-sm font-semibold">
                    Batalkan
                </button>

                <button
                    type="submit"
                    class="px-7 py-2.5 rounded-full bg-indigo-800 text-white text-sm font-semibold hover:bg-indigo-900">
                    Publikasikan Tugas
                </button>
            </div>

        </form>
    </div>
</div>

<!-- Validasi Deadline -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const deadlineInput = document.getElementById('deadline');
    const deadlineError = document.getElementById('deadlineError');
    const form = document.getElementById('formBuatTugas');

    if (!deadlineInput || !form) return;

    function getWaktuSekarang() {
        const now = new Date();
        const pad = angka => String(angka).padStart(2, '0');

        return now.getFullYear() + '-' +
            pad(now.getMonth() + 1) + '-' +
            pad(now.getDate()) + 'T' +
            pad(now.getHours()) + ':' +
            pad(now.getMinutes());
    }

    function updateMinDeadline() {
        deadlineInput.min = getWaktuSekarang();
    }

    function tampilkanError(pesan) {
        deadlineError.textContent = pesan;
        deadlineError.classList.remove('hidden');
        deadlineInput.classList.add('border-red-500');
    }

    function sembunyikanError() {
        deadlineError.classList.add('hidden');
        deadlineInput.classList.remove('border-red-500');
    }

    updateMinDeadline();

    deadlineInput.addEventListener('focus', updateMinDeadline);

    deadlineInput.addEventListener('change', function () {
        updateMinDeadline();

        if (this.value && this.value < this.min) {
            this.value = '';
            tampilkanError('Deadline tidak boleh lebih awal dari waktu sekarang.');
        } else {
            sembunyikanError();
        }
    });

    form.addEventListener('submit', function (event) {
        updateMinDeadline();

        if (!deadlineInput.value || deadlineInput.value < deadlineInput.min) {
            event.preventDefault();
            tampilkanError('Silakan pilih deadline setelah waktu sekarang.');
            deadlineInput.focus();
            return;
        }

        sembunyikanError();
    });
});
</script>
