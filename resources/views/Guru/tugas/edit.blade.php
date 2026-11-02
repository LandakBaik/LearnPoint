<!-- Modal Popup Edit & Hapus Tugas -->
<div
    x-data="{ showEditModal: false, editData: {} }"
    @open-edit-modal.window="showEditModal = true; editData = $event.detail"
    x-show="showEditModal"
    x-cloak
    style="display: none;"
    @keydown.escape.window="showEditModal = false"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">

    <!-- Modal Backdrop -->
    <div
        x-show="showEditModal"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="showEditModal = false"
        class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity">
    </div>

    <!-- Modal Dialog -->
    <div
        x-show="showEditModal"
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
                    Edit Tugas
                </h3>
                <p class="text-xs text-slate-500 mt-1">
                    Perbarui rincian informasi tugas atau hapus jika tidak diperlukan.
                </p>
            </div>

            <button
                @click="showEditModal = false"
                type="button"
                class="w-8 h-8 rounded-full text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition-colors shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Form Edit -->
        <form
            :action="'/guru/tugas/' + editData.id"
            method="POST"
            enctype="multipart/form-data"
            class="p-6 sm:p-7 space-y-5"
            id="formEditTugas">

            @csrf
            @method('PUT')

            <input type="hidden" name="tipe" :value="editData.tipe || 'upload'">

            <!-- Judul Tugas -->
            <div class="space-y-1.5">
                <label for="edit_judul" class="block text-xs font-bold text-slate-700">
                    Judul Tugas <span class="text-red-500">*</span>
                </label>

                <input
                    type="text"
                    name="judul"
                    id="edit_judul"
                    :value="editData.judul"
                    required
                    placeholder="Masukkan judul tugas"
                    class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
            </div>

            <!-- Mata Pelajaran & Kelas -->
            <div class="space-y-1.5">
                <label for="edit_pengampu_kelas_id" class="block text-xs font-bold text-slate-700">
                    Mata Pelajaran & Kelas <span class="text-red-500">*</span>
                </label>

                <select
                    name="pengampu_kelas_id"
                    id="edit_pengampu_kelas_id"
                    required
                    class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all cursor-pointer">

                    @foreach($pengampuKelases as $pk)
                        <option
                            value="{{ $pk->id }}"
                            :selected="editData.pengampu_kelas_id == {{ $pk->id }}">
                            {{ $pk->guruMapel?->mapel?->nama_mapel ?? 'Mapel' }} -
                            {{ $pk->kelas?->nama_kelas ?? 'Kelas' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Deskripsi Tugas -->
            <div class="space-y-1.5">
                <label for="edit_deskripsi" class="block text-xs font-bold text-slate-700">
                    Deskripsi Tugas
                </label>

                <textarea
                    name="deskripsi"
                    id="edit_deskripsi"
                    rows="3"
                    x-text="editData.deskripsi"
                    placeholder="Tambahkan penjelasan tugas..."
                    class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"></textarea>
            </div>

            <!-- Ganti Lampiran -->
            <div class="space-y-2">
                <label class="block text-xs font-bold text-slate-700">
                    Ganti Lampiran File (Opsional)
                </label>

                <div class="border-2 border-dashed border-slate-200 bg-slate-50/40 rounded-2xl p-4 text-center cursor-pointer hover:bg-indigo-50 transition-colors">
                    <input
                        type="file"
                        name="file_tugas_input"
                        accept=".pdf,.docx,.jpg,.png"
                        class="text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-800 hover:file:bg-indigo-100">
                </div>
            </div>

            <!-- Deadline -->
            <div class="space-y-1.5">
                <label for="edit_deadline" class="block text-xs font-bold text-slate-700">
                    Batas Waktu Pengumpulan (Deadline)
                    <span class="text-red-500">*</span>
                </label>

                <input
                    type="datetime-local"
                    name="deadline"
                    id="edit_deadline"
                    :value="editData.deadline"
                    required
                    class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">

                <p
                    id="editDeadlineError"
                    class="hidden text-xs text-red-500 mt-2">
                    Deadline harus setelah waktu sekarang.
                </p>

                <p class="text-[11px] text-slate-400">
                    Deadline harus ditentukan setelah waktu sekarang.
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between pt-4 border-t border-slate-100">

                <!-- Tombol Hapus -->
                <button
                    type="button"
                    @click="if(confirm('Apakah Anda yakin ingin menghapus tugas ini secara permanen?')) { document.getElementById('delete-form-' + editData.id).submit(); }"
                    class="px-5 py-2.5 rounded-full bg-red-50 hover:bg-red-100 text-red-600 text-sm font-semibold transition-colors flex items-center gap-1.5">

                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>

                    Hapus Tugas
                </button>

                <!-- Tombol Batal & Simpan -->
                <div class="flex items-center gap-3">
                    <button
                        @click="showEditModal = false"
                        type="button"
                        class="px-6 py-2.5 rounded-full border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-indigo-50 transition-colors">
                        Batal
                    </button>

                    <button
                        type="submit"
                        class="px-7 py-2.5 rounded-full bg-indigo-800 text-white text-sm font-semibold hover:bg-indigo-900 transition-colors shadow-sm">
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Validasi Deadline Edit -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const deadlineInput = document.getElementById('edit_deadline');
    const deadlineError = document.getElementById('editDeadlineError');
    const form = document.getElementById('formEditTugas');

    if (!deadlineInput || !form) return;

    function getWaktuSekarang() {
        const now = new Date();
        const pad = angka => String(angka).padStart(2, '0');

        return `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`;
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

    // Validasi saat deadline diubah
    deadlineInput.addEventListener('change', function () {
        if (this.value && this.value <= getWaktuSekarang()) {
            this.value = '';
            tampilkanError('Deadline tidak boleh lebih awal dari waktu sekarang.');
        } else {
            sembunyikanError();
        }
    });

    // Validasi saat form disimpan
    form.addEventListener('submit', function (event) {
        if (!deadlineInput.value || deadlineInput.value <= getWaktuSekarang()) {
            event.preventDefault();

            tampilkanError('Silakan pilih deadline setelah waktu sekarang.');
            deadlineInput.focus();
            return;
        }

        sembunyikanError();
    });

    // Reset pesan ketika modal dibuka
    window.addEventListener('open-edit-modal', function () {
        sembunyikanError();
    });
});
</script>
