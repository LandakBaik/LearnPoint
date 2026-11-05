<!-- Modal Popup Buat Tugas Baru -->
<div
    x-show="showModal"
    x-cloak
    style="display: none;"
    @keydown.escape.window="showModal = false"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">

    <!-- Modal Backdrop -->
    <div
        x-show="showModal"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="showModal = false"
        class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity">
    </div>

    <!-- Modal Dialog -->
    <div
        x-show="showModal"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95 translate-y-4"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-4"
        class="relative w-full max-w-xl bg-white rounded-2xl shadow-2xl border border-slate-100 z-10 overflow-hidden">

        <!-- Header -->
        <div class="bg-indigo-50/50 p-6 sm:p-7 border-b border-indigo-100 flex items-start justify-between gap-4">
            <div>
                <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">
                    Buat Tugas Baru
                </h3>
                <p class="text-xs text-slate-500 mt-1">
                    Isi formulir penugasan untuk siswa kelas yang Anda ampu.
                </p>
            </div>

            <button
                @click="showModal = false"
                type="button"
                class="w-8 h-8 rounded-full text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition-colors shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Form -->
        <form
            action="{{ route('guru.tugas.store') }}"
            method="POST"
            enctype="multipart/form-data"
            class="p-6 sm:p-7 space-y-5"
            id="formBuatTugas">

            @csrf

            <input type="hidden" name="tipe" value="upload">

            <!-- Judul Tugas -->
            <div class="space-y-1.5">
                <label for="judul" class="block text-xs font-bold text-slate-700">
                    Judul Tugas <span class="text-red-500">*</span>
                </label>

                <input
                    type="text"
                    name="judul"
                    id="judul"
                    required
                    placeholder="Masukkan judul tugas (contoh: Sistem Persamaan Linear)"
                    class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all placeholder:text-slate-400">
            </div>

            <!-- Mata Pelajaran & Kelas -->
            <div class="space-y-1.5">
                <label for="pengampu_kelas_id" class="block text-xs font-bold text-slate-700">
                    Mata Pelajaran & Kelas <span class="text-red-500">*</span>
                </label>

                <select
                    name="pengampu_kelas_id"
                    id="pengampu_kelas_id"
                    required
                    class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all cursor-pointer">

                    <option value="" disabled selected>-- Pilih Mapel & Kelas --</option>

                    @foreach($pengampuKelases as $pk)
                        <option value="{{ $pk->id }}">
                            {{ $pk->guruMapel?->mapel?->nama_mapel ?? 'Mapel' }} -
                            {{ $pk->kelas?->nama_kelas ?? 'Kelas' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Deskripsi Tugas -->
            <div class="space-y-1.5">
                <label for="deskripsi" class="block text-xs font-bold text-slate-700">
                    Deskripsi Tugas
                </label>

                <textarea
                    name="deskripsi"
                    id="deskripsi"
                    rows="3"
                    placeholder="Tambahkan penjelasan konteks tugas atau ringkasan capaian pembelajaran..."
                    class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all placeholder:text-slate-400"></textarea>
            </div>

            <!-- Lampiran -->
            <div class="space-y-2">
                <label class="block text-xs font-bold text-slate-700">
                    Lampiran Lembar Kerja / Dokumen Pendukung
                </label>

                <div class="border-2 border-dashed border-slate-200 bg-slate-50/40 rounded-2xl p-5 text-center space-y-1.5 hover:bg-indigo-50 transition-colors cursor-pointer">
                    <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center mx-auto mb-1 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                    </div>

                    <p class="text-xs font-semibold text-slate-700">
                        Tarik & lepaskan file lembar kerja di sini, atau
                        <label for="file_tugas_input" class="text-indigo-700 font-bold hover:underline cursor-pointer">
                            Pilih Berkas
                        </label>
                    </p>

                    <p class="text-[11px] text-slate-400">
                        Mendukung berkas: PDF, DOCX, JPG, PNG (Ukuran maksimal: 10 MB)
                    </p>

                    <input
                        type="file"
                        id="file_tugas_input"
                        name="file_tugas_input"
                        accept=".pdf,.docx,.jpg,.png"
                        @change="handleFileChange($event)"
                        class="hidden">
                </div>

                <template x-if="hasFile">
                    <div class="bg-indigo-50/50 border border-indigo-100/80 rounded-2xl p-3 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-8 h-8 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0 font-bold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V7.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 1H7a2 2 0 00-2 2v16a2 2 0 002 2z" />
                                </svg>
                            </div>

                            <div class="min-w-0">
                                <p class="text-xs font-bold text-slate-800 truncate" x-text="fileName"></p>
                                <p class="text-[11px] text-slate-500 mt-0.5" x-text="fileSize"></p>
                            </div>
                        </div>

                        <button
                            @click="hasFile = false; document.getElementById('file_tugas_input').value = ''"
                            type="button"
                            class="text-slate-400 hover:text-slate-600 p-1 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </template>
            </div>

            <!-- Deadline -->
            <div class="space-y-1.5">
                <label for="deadline" class="block text-xs font-bold text-slate-700">
                    Batas Waktu Pengumpulan (Deadline)
                    <span class="text-red-500">*</span>
                </label>

                <div class="relative">
                    <input
                        type="datetime-local"
                        name="deadline"
                        id="deadline"
                        required
                        class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">

                    <p id="deadlineError" class="hidden text-xs text-red-500 mt-2">
                        Deadline harus setelah waktu sekarang.
                    </p>
                </div>

                <p class="text-[11px] text-slate-400">
                    Tentukan batas waktu pengumpulan setelah waktu sekarang.
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button
                    @click="showModal = false"
                    type="button"
                    class="px-6 py-2.5 rounded-full border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-indigo-50 transition-colors">
                    Batalkan
                </button>

                <button
                    type="submit"
                    class="px-7 py-2.5 rounded-full bg-indigo-800 text-white text-sm font-semibold hover:bg-indigo-900 transition-colors shadow-sm">
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

        return [
            now.getFullYear(),
            '-',
            pad(now.getMonth() + 1),
            '-',
            pad(now.getDate()),
            'T',
            pad(now.getHours()),
            ':',
            pad(now.getMinutes())
        ].join('');
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