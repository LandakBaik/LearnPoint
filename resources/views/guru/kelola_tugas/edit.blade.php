<!-- MODAL EDIT & HAPUS TUGAS -->
<div
    x-data="{
        showEditModal: false,
        editData: {},
        tipeEdit: 'upload',

        bukaModal(data) {
            this.editData = JSON.parse(JSON.stringify(data));

            let tipe = String(data.tipe ?? data.tipe_tugas ?? data.jenis_tugas ?? '').toLowerCase().trim();
            this.tipeEdit = ['pilgan', 'pilihan_ganda', 'pilihan ganda'].includes(tipe) ? 'pilgan' : 'upload';

            this.editData.tipe = this.tipeEdit;

            if (this.editData.deadline) {
                this.editData.deadline = String(this.editData.deadline).replace(' ', 'T').substring(0, 16);
            }

            if (this.tipeEdit === 'pilgan') {
                this.editData.soals = (this.editData.soals || []).map(soal => {
                    let pilihan = soal.pilihan || {};

                    if (typeof pilihan === 'string') {
                        try { pilihan = JSON.parse(pilihan); }
                        catch { pilihan = {}; }
                    }

                    return {
                        ...soal,
                        pilihan,
                        kunci_jawaban: soal.kunci_jawaban || ''
                    };
                });
            }

            // Ambil semua ID kelas yang terhubung dengan tugas
            let ids = data.pengampu_kelas_ids
                ?? data.pengampu_kelas_id
                ?? data.pengampu_kelases?.map(item => item.id)
                ?? data.pengampu_kelases?.map(item => item.pivot?.pengampu_kelas_id)
                ?? [];

            if (!Array.isArray(ids)) ids = [ids];

            this.editData.kelasTerpilih = ids.map(String);
            this.showEditModal = true;

            this.$nextTick(() => {
                document.querySelectorAll('.checkboxPengampuEdit').forEach(cb => {
                    cb.checked = this.editData.kelasTerpilih.includes(String(cb.value));
                });

                window.updateJumlahEdit?.();
            });
        }
    }"
    @open-edit-modal.window="bukaModal($event.detail)"
    x-show="showEditModal"
    x-cloak
    style="display:none"
    @keydown.escape.window="showEditModal = false"
    class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4">

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
        class="relative z-10 my-auto flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-2xl">

        <!-- HEADER -->
        <div class="flex shrink-0 items-center justify-between border-b border-slate-100 bg-white px-6 py-4">
            <div>
                <h3 class="text-lg font-bold text-slate-900">Edit Tugas</h3>
                <p class="mt-1 text-xs text-slate-500">Perbarui informasi dan pengaturan tugas.</p>

                <span
                    class="mt-2 inline-block rounded-full bg-blue-50 px-3 py-1 text-[11px] font-bold text-blue-700"
                    x-text="tipeEdit === 'pilgan' ? 'Pilihan Ganda' : 'Upload File'">
                </span>
            </div>

            <button
                type="button"
                @click="showEditModal = false"
                class="rounded-full p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                ✕
            </button>
        </div>

        <!-- FORM -->
        <form
            :action="'/guru/tugas/' + editData.id"
            method="POST"
            enctype="multipart/form-data"
            id="formEditTugas"
            class="min-h-0 space-y-5 overflow-y-auto p-6 sm:p-7">

            @csrf
            @method('PUT')

            <input
                type="hidden"
                name="tipe"
                :value="tipeEdit === 'pilgan' ? 'pilihan_ganda' : 'upload'">

            <!-- JUDUL -->
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">
                    Judul Tugas <span class="text-red-500">*</span>
                </label>

                <input
                    type="text"
                    name="judul"
                    x-model="editData.judul"
                    required
                    placeholder="Masukkan judul tugas"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100">
            </div>

            <!-- DESKRIPSI -->
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">
                    Deskripsi Tugas
                </label>

                <textarea
                    name="deskripsi"
                    rows="3"
                    x-model="editData.deskripsi"
                    placeholder="Tambahkan instruksi pengerjaan tugas..."
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100"></textarea>
            </div>

            <div class="border-t border-slate-100"></div>

            <!-- KELAS -->
            <div class="space-y-3">
                <div>
                    <label class="text-sm font-semibold text-slate-800">
                        Kelas Tujuan <span class="text-red-500">*</span>
                    </label>
                    <p class="mt-1 text-xs text-slate-400">
                        Pilih satu atau beberapa kelas untuk tugas ini.
                    </p>
                </div>

                <div class="space-y-3 rounded-xl border border-slate-200 bg-slate-50/50 p-3">

                    <!-- FILTER TINGKAT -->
                    <select
                        id="filterTingkatEdit"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">

                        <option value="">Semua Tingkat</option>

                        @foreach($pengampuKelases->pluck('kelas.nama_kelas')->filter()->map(fn($nama) => preg_replace('/\D/', '', $nama))->filter()->unique()->sort() as $tingkat)
                        <option value="{{ $tingkat }}">Tingkat {{ $tingkat }}</option>
                        @endforeach
                    </select>

                    <!-- PILIH SEMUA -->
                    <label class="flex cursor-pointer items-center gap-3 rounded-lg bg-blue-50 p-3">
                        <input
                            type="checkbox"
                            id="pilihSemuaEdit"
                            class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">

                        <span class="text-sm font-semibold text-blue-700">
                            Pilih Semua
                        </span>
                    </label>

                    <!-- DAFTAR KELAS -->
                    <div
                        id="daftarPengampuEdit"
                        class="grid max-h-48 grid-cols-2 gap-2.5 overflow-y-auto sm:grid-cols-3">

                        @foreach($pengampuKelases as $pk)
                        <label
                            class="itemPengampuEdit flex cursor-pointer items-center rounded-xl border border-slate-200 bg-white p-3 transition hover:border-blue-300 hover:bg-blue-50"
                            data-tingkat="{{ preg_replace('/\D/', '', $pk->kelas?->nama_kelas ?? '') }}">

                            <input
                                type="checkbox"
                                name="pengampu_kelas_id[]"
                                value="{{ $pk->id }}"
                                class="checkboxPengampuEdit h-4 w-4 shrink-0 rounded border-slate-300 text-blue-600 focus:ring-blue-500">

                            <span class="ml-2 text-xs font-semibold text-slate-800">
                                {{ $pk->kelas?->nama_kelas ?? 'Kelas' }}
                            </span>
                        </label>
                        @endforeach
                    </div>

                    <p
                        id="jumlahTerpilihEdit"
                        class="border-t border-slate-100 pt-3 text-xs font-medium text-slate-500">
                        0 kelas dipilih
                    </p>
                </div>
            </div>

            <div class="border-t border-slate-100"></div>


            <!-- UPLOAD FILE -->
            <template x-if="tipeEdit === 'upload'">
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">
                            Lampiran Lembar Kerja / Dokumen Pendukung
                        </label>
                        <p class="mt-1 text-xs text-slate-400">
                            Unggah dokumen pendukung untuk tugas ini.
                        </p>
                    </div>

                    <!-- CARD UPLOAD -->
                    <label
                        for="edit_file_tugas_input"
                        class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-blue-200 bg-blue-50/40 px-5 py-8 text-center transition hover:border-blue-400 hover:bg-blue-50">

                        <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M12 16V4m0 0L7 9m5-5 5 5M5 14v5a1 1 0 001 1h12a1 1 0 001-1v-5" />
                            </svg>
                        </div>

                        <p class="text-sm font-bold text-slate-800">
                            Klik untuk mengunggah file
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            PDF, DOC, DOCX, JPG, atau PNG (maks. 10 MB)
                        </p>

                        <span class="mt-3 rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white">
                            Pilih File
                        </span>

                        <input
                            type="file"
                            name="file_tugas_input"
                            id="edit_file_tugas_input"
                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                            class="hidden">
                    </label>

                    <p class="text-xs text-slate-400">
                        Kosongkan jika tidak ingin mengganti lampiran sebelumnya.
                    </p>

                    <!-- FILE TERPILIH -->
                    <div
                        id="editFileInfo"
                        class="hidden items-center justify-between gap-3 rounded-xl border border-blue-100 bg-blue-50 p-3">

                        <div class="flex min-w-0 items-center gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-white text-blue-600">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M7 3h7l5 5v13H7a2 2 0 01-2-2V5a2 2 0 012-2zM14 3v5h5M9 13h6M9 17h6" />
                                </svg>
                            </div>

                            <div class="min-w-0">
                                <p id="editFileName" class="truncate text-sm font-semibold text-slate-800"></p>
                                <p id="editFileSize" class="text-xs text-slate-500"></p>
                            </div>
                        </div>

                        <button
                            type="button"
                            id="hapusFileEdit"
                            class="shrink-0 rounded-lg p-2 text-red-500 transition hover:bg-red-100">
                            ✕
                        </button>
                    </div>
                </div>
            </template>


            <!-- PILIHAN GANDA -->
            <template x-if="tipeEdit === 'pilgan'">
                <div class="space-y-3">
                    <div>
                        <h4 class="text-sm font-bold text-slate-800">
                            Daftar Soal Pilihan Ganda
                        </h4>
                        <p class="mt-1 text-xs text-slate-500">
                            Perbarui pertanyaan dan pilihan jawaban.
                        </p>
                    </div>

                    <div class="rounded-xl border border-blue-100 bg-blue-50 p-3 text-xs text-blue-700">
                        Total nilai maksimal 100. Bobot soal dihitung otomatis berdasarkan jumlah soal.
                    </div>

                    <template x-for="(soal, index) in editData.soals" :key="soal.id || index">
                        <div class="space-y-3 rounded-xl border border-slate-200 p-4">

                            <h5
                                class="text-sm font-bold text-slate-800"
                                x-text="'Pertanyaan ' + (index + 1)">
                            </h5>

                            <input
                                type="hidden"
                                :name="`soal[${index}][id]`"
                                :value="soal.id || ''">

                            <!-- PERTANYAAN -->
                            <textarea
                                :name="`soal[${index}][pertanyaan]`"
                                x-model="soal.pertanyaan"
                                rows="3"
                                required
                                placeholder="Tuliskan pertanyaan..."
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100"></textarea>

                            <!-- PILIHAN -->
                            <div class="space-y-2">
                                <label class="text-xs font-semibold text-slate-700">
                                    Pilihan Jawaban
                                </label>

                                <template x-for="huruf in ['a','b','c','d','e']" :key="huruf">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-xs font-bold text-blue-700"
                                            x-text="huruf.toUpperCase()">
                                        </span>

                                        <input
                                            type="text"
                                            :name="`soal[${index}][pilihan][${huruf}]`"
                                            x-model="soal.pilihan[huruf]"
                                            :placeholder="'Pilihan ' + huruf.toUpperCase()"
                                            class="min-w-0 flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100">
                                    </div>
                                </template>
                            </div>

                            <!-- KUNCI JAWABAN -->
                            <div>
                                <label class="mb-2 block text-xs font-semibold text-slate-700">
                                    Kunci Jawaban
                                </label>

                                <select
                                    :name="`soal[${index}][kunci_jawaban]`"
                                    x-model="soal.kunci_jawaban"
                                    required
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100">

                                    <option value="">Pilih Kunci Jawaban</option>

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
                </div>
            </template>

            <!-- DEADLINE -->
            <div class="space-y-2 border-t border-slate-100 pt-4">
                <label class="block text-sm font-semibold text-slate-700">
                    Batas Waktu Pengumpulan <span class="text-red-500">*</span>
                </label>

                <input
                    type="datetime-local"
                    name="deadline"
                    id="edit_deadline"
                    x-model="editData.deadline"
                    required
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100">

                <p id="editDeadlineError" class="hidden text-xs text-red-500">
                    Deadline harus setelah waktu sekarang.
                </p>
            </div>

            <!-- TOMBOL -->
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">

                <button
                    type="button"
                    @click="if(confirm('Yakin ingin menghapus tugas ini?')) document.getElementById('delete-form-' + editData.id).submit()"
                    class="rounded-xl border border-red-100 bg-red-50 px-5 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-100">
                    Hapus Tugas
                </button>

                <div class="flex gap-3">
                    <button
                        type="button"
                        @click="showEditModal = false"
                        class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                        Batal
                    </button>

                    <button
                        type="submit"
                        class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-700">
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>