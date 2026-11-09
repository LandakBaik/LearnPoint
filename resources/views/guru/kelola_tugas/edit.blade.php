<!-- MODAL EDIT & HAPUS TUGAS -->
<div
    x-data="{
        showEditModal: false,
        editData: {},
        tipeEdit: 'upload',

        bukaModal(data) {
            this.editData = JSON.parse(JSON.stringify(data));

            let tipe = String(
                data.tipe ??
                data.tipe_tugas ??
                data.jenis_tugas ??
                ''
            ).toLowerCase().trim();

            this.tipeEdit = (
                tipe === 'pilgan' ||
                tipe === 'pilihan_ganda' ||
                tipe === 'pilihan ganda'
            ) ? 'pilgan' : 'upload';

            this.editData.tipe = this.tipeEdit;

            // Menyesuaikan format deadline untuk datetime-local
            if (this.editData.deadline) {
                this.editData.deadline = String(this.editData.deadline)
                    .replace(' ', 'T')
                    .substring(0, 16);
            }

            if (this.tipeEdit === 'pilgan') {
                this.editData.soals = (this.editData.soals || []).map(soal => {
                    let pilihan = soal.pilihan || [];

                    if (typeof pilihan === 'string') {
                        try {
                            pilihan = JSON.parse(pilihan);
                        } catch (e) {
                            pilihan = [];
                        }
                    }

                    return {
                        ...soal,
                        pilihan: pilihan,
                        kunci_jawaban: soal.kunci_jawaban || ''
                    };
                });
            }

            this.showEditModal = true;

            this.$nextTick(() => {
                const select = document.getElementById('edit_pengampu_kelas_id');

                if (select) {
                    select.value = this.editData.pengampu_kelas_id || '';
                }
            });
        }
    }"
    @open-edit-modal.window="bukaModal($event.detail)"
    x-show="showEditModal"
    x-cloak
    style="display: none;"
    @keydown.escape.window="showEditModal = false"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">

    <!-- BACKDROP -->
    <div
        x-show="showEditModal"
        x-transition.opacity
        @click="showEditModal = false"
        class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm">
    </div>

    <!-- MODAL -->
    <div
        x-show="showEditModal"
        x-transition
        class="relative z-10 my-auto w-full max-w-xl overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-2xl">

        <!-- HEADER -->
        <div class="flex items-start justify-between gap-4 border-b border-indigo-100 bg-indigo-50/50 p-6 sm:p-7">

            <div>
                <h3 class="text-xl font-extrabold text-slate-900">
                    Edit Tugas
                </h3>

                <p class="mt-1 text-xs text-slate-500">
                    Perbarui informasi tugas.
                </p>

                <span
                    class="mt-3 inline-block rounded-full px-3 py-1 text-xs font-bold"
                    :class="tipeEdit === 'pilgan'
                        ? 'bg-purple-100 text-purple-700'
                        : 'bg-blue-100 text-blue-700'"
                    x-text="tipeEdit === 'pilgan' ? 'Pilihan Ganda' : 'Upload File'">
                </span>
            </div>

            <button
                type="button"
                @click="showEditModal = false"
                class="flex h-8 w-8 items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-600">

                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- FORM -->
        <form
            :action="'/guru/tugas/' + editData.id"
            method="POST"
            enctype="multipart/form-data"
            class="space-y-5 p-6 sm:p-7"
            id="formEditTugas">

            @csrf
            @method('PUT')

            <!-- TIPE TUGAS -->
            <input
                type="hidden"
                name="tipe"
                :value="tipeEdit === 'pilgan' ? 'pilihan_ganda' : 'upload'">

            <!-- JUDUL -->
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700">
                    Judul Tugas <span class="text-red-500">*</span>
                </label>

                <input
                    type="text"
                    name="judul"
                    x-model="editData.judul"
                    required
                    placeholder="Masukkan judul tugas"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50/50 px-4 py-3 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <!-- MAPEL DAN KELAS -->
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700">
                    Mata Pelajaran & Kelas <span class="text-red-500">*</span>
                </label>

                <select
                    name="pengampu_kelas_id"
                    id="edit_pengampu_kelas_id"
                    required
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50/50 px-4 py-3 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">

                    @foreach($pengampuKelases as $pk)
                    <option value="{{ $pk->id }}">
                        {{ $pk->guruMapel?->mapel?->nama_mapel ?? 'Mapel' }}
                        -
                        {{ $pk->kelas?->nama_kelas ?? 'Kelas' }}
                    </option>
                    @endforeach

                </select>
            </div>

            <!-- DESKRIPSI -->
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700">
                    Deskripsi Tugas
                </label>

                <textarea
                    name="deskripsi"
                    rows="3"
                    x-model="editData.deskripsi"
                    placeholder="Tambahkan penjelasan tugas..."
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50/50 px-4 py-3 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>

            <!-- FORM UPLOAD -->
            <template x-if="tipeEdit === 'upload'">
                <div class="space-y-3">

                    <div class="border-b border-slate-100 pb-2">
                        <h4 class="text-sm font-bold text-slate-800">
                            Lampiran Tugas
                        </h4>

                        <p class="mt-1 text-xs text-slate-500">
                            Ganti file tugas jika diperlukan.
                        </p>
                    </div>

                    <div class="rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/40 p-4">

                        <label class="mb-3 block text-xs font-bold text-slate-700">
                            Ganti Lampiran File (Opsional)
                        </label>

                        <input
                            type="file"
                            name="file_tugas_input"
                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                            class="w-full text-xs text-slate-500 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-indigo-800">

                        <p class="mt-2 text-[11px] text-slate-400">
                            Kosongkan jika tidak ingin mengganti lampiran sebelumnya.
                        </p>
                    </div>
                </div>
            </template>

            <!-- FORM PILIHAN GANDA -->
            <template x-if="tipeEdit === 'pilgan'">
                <div class="space-y-4">

                    <div class="border-b border-slate-100 pb-2">
                        <h4 class="text-sm font-bold text-slate-800">
                            Soal Pilihan Ganda
                        </h4>

                        <p class="mt-1 text-xs text-slate-500">
                            Perbarui pertanyaan dan pilihan jawaban.
                        </p>
                    </div>

                    <template
                        x-for="(soal, index) in editData.soals"
                        :key="soal.id || index">

                        <div class="space-y-3 rounded-xl border border-slate-200 p-4">

                            <input
                                type="hidden"
                                :name="`soal[${index}][id]`"
                                :value="soal.id || ''">

                            <!-- PERTANYAAN -->
                            <div>
                                <label class="mb-2 block text-xs font-bold text-slate-700">
                                    Pertanyaan <span x-text="index + 1"></span>
                                </label>

                                <textarea
                                    :name="`soal[${index}][pertanyaan]`"
                                    x-model="soal.pertanyaan"
                                    rows="3"
                                    required
                                    placeholder="Masukkan pertanyaan"
                                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                            </div>

                            <!-- PILIHAN JAWABAN -->
                            <div class="space-y-2">
                                <label class="block text-xs font-bold text-slate-700">
                                    Pilihan Jawaban
                                </label>

                                <template
                                    x-for="(pilihan, huruf) in soal.pilihan"
                                    :key="huruf">

                                    <div class="flex items-center gap-2">
                                        <span
                                            class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-50 text-xs font-bold text-indigo-700"
                                            x-text="huruf.toUpperCase()">
                                        </span>

                                        <input
                                            type="text"
                                            :name="`soal[${index}][pilihan][${huruf}]`"
                                            x-model="soal.pilihan[huruf]"
                                            required
                                            placeholder="Masukkan pilihan jawaban"
                                            class="flex-1 rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    </div>
                                </template>
                            </div>

                            <!-- KUNCI JAWABAN -->
                            <div>
                                <label class="mb-2 block text-xs font-bold text-slate-700">
                                    Kunci Jawaban
                                </label>

                                <select
                                    :name="`soal[${index}][kunci_jawaban]`"
                                    x-model="soal.kunci_jawaban"
                                    required
                                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">

                                    <template
                                        x-for="(pilihan, huruf) in soal.pilihan"
                                        :key="huruf">

                                        <option
                                            :value="huruf"
                                            x-text="huruf.toUpperCase()">
                                        </option>
                                    </template>
                                </select>
                            </div>

                        </div>
                    </template>

                </div>
            </template>

            <!-- DEADLINE (BERLAKU UNTUK SEMUA TIPE TUGAS) -->
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700">
                    Batas Waktu Pengumpulan (Deadline)
                    <span class="text-red-500">*</span>
                </label>

                <input
                    type="datetime-local"
                    name="deadline"
                    id="edit_deadline"
                    x-model="editData.deadline"
                    required
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50/50 px-4 py-3 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">

                <p
                    id="editDeadlineError"
                    class="mt-2 hidden text-xs text-red-500">
                    Deadline harus setelah waktu sekarang.
                </p>

                <p class="text-[11px] text-slate-400">
                    Deadline harus ditentukan setelah waktu sekarang.
                </p>
            </div>

            <!-- TOMBOL -->
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">

                <button
                    type="button"
                    @click="
                        if(confirm('Apakah Anda yakin ingin menghapus tugas ini secara permanen?')) {
                            document.getElementById('delete-form-' + editData.id).submit();
                        }
                    "
                    class="rounded-full bg-red-50 px-5 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-100">
                    Hapus Tugas
                </button>

                <div class="flex items-center gap-3">

                    <button
                        type="button"
                        @click="showEditModal = false"
                        class="rounded-full border border-slate-300 px-6 py-2.5 text-sm font-semibold text-slate-700 hover:bg-indigo-50">
                        Batal
                    </button>

                    <button
                        type="submit"
                        class="rounded-full bg-indigo-800 px-7 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-900">
                        Simpan Perubahan
                    </button>

                </div>
            </div>

        </form>
    </div>
</div>

<!-- VALIDASI DEADLINE -->
<script>
    document.addEventListener('DOMContentLoaded', function() {

        const form = document.getElementById('formEditTugas');
        const deadlineInput = document.getElementById('edit_deadline');
        const deadlineError = document.getElementById('editDeadlineError');

        if (!form || !deadlineInput || !deadlineError) return;

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

        deadlineInput.addEventListener('change', function() {
            if (this.value && this.value <= getWaktuSekarang()) {
                this.value = '';
                tampilkanError('Deadline tidak boleh lebih awal dari waktu sekarang.');
            } else {
                sembunyikanError();
            }
        });

        form.addEventListener('submit', function(event) {
            if (!deadlineInput.value || deadlineInput.value <= getWaktuSekarang()) {
                event.preventDefault();
                tampilkanError('Silakan pilih deadline setelah waktu sekarang.');
                deadlineInput.focus();
            } else {
                sembunyikanError();
            }
        });

        window.addEventListener('open-edit-modal', sembunyikanError);

    });
</script>
