@extends('layouts.siswa')

@section('title', 'Detail Tugas - ' . $tugas->judul)

@section('content')

@php
use Illuminate\Support\Facades\Storage;

$sudahDinilai = $pengumpulan
&& $pengumpulan->nilai !== null
&& $pengumpulan->nilai > 0;

$fileTerkirim = $pengumpulan?->file_jawaban;

$fileTerkirimAda = $fileTerkirim
&& Storage::disk('public')->exists($fileTerkirim);

$fileTerkirimUrl = $fileTerkirimAda
? asset('storage/' . $fileTerkirim)
: null;

$fileTerkirimExt = $fileTerkirim
? strtolower(pathinfo($fileTerkirim, PATHINFO_EXTENSION))
: null;
@endphp

<div class="space-y-5">

    {{-- 1. Navbar Detail Tugas --}}
    <div class="flex flex-wrap items-center gap-3 text-sm font-medium">

        <a href="{{ route('siswa.dashboard') }}"
            class="inline-flex items-center gap-2 px-4 py-2 bg-white text-slate-700 hover:bg-slate-100 rounded-full border border-slate-200 transition shadow-sm shrink-0">
            <i class="fa-solid fa-arrow-left text-xs"></i>
            Kembali ke Beranda
        </a>

        <span class="text-slate-300">/</span>

        <a href="{{ route('siswa.tugas') }}"
            class="text-slate-700 hover:text-sky-700 hover:underline font-semibold transition">
            Tugas
        </a>

        <span class="text-slate-300">/</span>

        <span class="text-slate-800 font-semibold">
            {{ $tugas->judul }}
        </span>
    </div>

    {{-- 2. Notifikasi Berhasil --}}
    @if(session('success'))
    <div class="max-w-3xl mx-auto w-full px-4 sm:px-0">
        <div class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-xs font-semibold">
            <i class="fa-solid fa-circle-check mr-1.5"></i>
            {{ session('success') }}
        </div>
    </div>
    @endif

    {{-- 3. Notifikasi Error --}}
    @if($errors->any())
    <div class="max-w-3xl mx-auto w-full px-4 sm:px-0">
        <div class="p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 text-xs">
            <p class="font-bold mb-1">Pengumpulan gagal.</p>

            @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
            @endforeach
        </div>
    </div>
    @endif

    {{-- 4. Konten Utama --}}
    <div class="max-w-3xl mx-auto w-full px-4 sm:px-0 space-y-5">

        {{-- 5. Card Informasi Tugas --}}
        <section class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-sm space-y-4">

            {{-- Judul dan Deadline --}}
            <div class="space-y-3">

                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-sky-50 text-sky-700 border border-sky-100">
                        <i class="fa-regular fa-calendar"></i>
                        Deadline:

                        {{ $tugas->deadline
                            ? \Carbon\Carbon::parse($tugas->deadline)->translatedFormat('d M Y, H:i')
                            : 'Tidak ditentukan' }}
                    </span>
                </div>

                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight leading-snug">
                    {{ $tugas->judul }}
                </h1>
            </div>

            {{-- Deskripsi Tugas --}}
            <div class="pt-4 border-t border-slate-100">
                <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">
                    Deskripsi Tugas
                </h2>

                <p class="text-sm text-slate-600 leading-relaxed whitespace-pre-line">
                    {{ $tugas->deskripsi ?? 'Tidak ada deskripsi tugas.' }}
                </p>
            </div>

            {{-- Lampiran Guru --}}
            @if($tugas->file_lampiran)

            @php
            $lampiranExt = strtolower(pathinfo($tugas->file_lampiran, PATHINFO_EXTENSION));
            $lampiranUrl = Storage::disk('public')->url($tugas->file_lampiran);
            @endphp

            <div class="pt-4 border-t border-slate-100">

                <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">
                    Berkas Lampiran
                </h2>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 rounded-xl border border-slate-200 bg-slate-50">

                    <div class="flex items-center gap-3 min-w-0">

                        <div class="w-10 h-10 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                            <i class="fa-regular fa-file-lines text-lg"></i>
                        </div>

                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-800">
                                Lampiran Tugas
                            </p>

                            <p class="text-xs text-slate-500 truncate">
                                {{ basename($tugas->file_lampiran) }}
                            </p>
                        </div>
                    </div>

                    <a href="{{ $lampiranUrl }}"
                        target="_blank"
                        class="inline-flex items-center justify-center gap-2 px-3.5 py-2 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition shrink-0">

                        <i class="fa-regular fa-eye"></i>
                        Lihat Berkas
                    </a>
                </div>
            </div>
            @endif

        </section>

        {{-- 6. Card Pengumpulan Tugas --}}
        <section class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-sm space-y-5">

            {{-- Header --}}
            <div class="flex items-center justify-between gap-3 pb-4 border-b border-slate-100">

                <div class="flex items-center gap-3">

                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <i class="fa-solid fa-cloud-arrow-up text-lg"></i>
                    </div>

                    <div>
                        <h2 class="text-base font-bold text-slate-900">
                            Pengumpulan Tugas
                        </h2>

                        <p class="text-xs text-slate-500 mt-0.5">
                            Kirimkan hasil pekerjaan kamu
                        </p>
                    </div>
                </div>

                <span class="text-xs font-semibold
                    {{ $sudahDinilai ? 'text-emerald-600' : ($pengumpulan ? 'text-sky-600' : 'text-rose-600') }}">
                    {{ $sudahDinilai ? 'Sudah Dinilai' : ($pengumpulan ? 'Sudah Terkirim' : 'Belum Terkirim') }}
                </span>
            </div>

            {{-- KONDISI 1: BELUM TERKIRIM --}}
            @if(!$pengumpulan)

            <form action="{{ route('siswa.tugas.kumpulkan', $tugas->id) }}"
                method="POST"
                enctype="multipart/form-data"
                id="form-kumpul-tugas"
                class="space-y-4">

                @csrf

                <div>

                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">
                        Unggah Berkas Jawaban
                    </label>

                    {{-- Area Upload --}}
                    <label for="file_jawaban"
                        id="upload-area"
                        class="flex flex-col items-center justify-center text-center rounded-xl border-2 border-dashed border-slate-300 bg-slate-50/60 hover:bg-sky-50 hover:border-sky-400 p-7 cursor-pointer transition">

                        <div class="w-12 h-12 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-sky-600 mb-3">
                            <i class="fa-solid fa-cloud-arrow-up text-xl"></i>
                        </div>

                        <p class="text-sm font-semibold text-slate-700">
                            <span class="text-sky-700">Klik untuk memilih file</span>
                        </p>

                        <p class="text-xs text-slate-500 mt-1">
                            Format PDF, DOCX, JPG, atau PNG
                        </p>

                        <p class="text-xs text-slate-400 mt-1">
                            Ukuran maksimal 10 MB
                        </p>
                    </label>

                    <input type="file"
                        name="file_jawaban"
                        id="file_jawaban"
                        accept=".pdf,.docx,.jpg,.jpeg,.png"
                        required
                        class="hidden">

                    {{-- Kartu File Setelah Dipilih --}}
                    <div id="selected-file-card"
                        class="hidden rounded-xl border border-sky-200 bg-sky-50/50 p-4 mt-3">

                        <div class="flex items-center gap-3">

                            <div id="selected-file-icon"
                                class="w-11 h-11 rounded-xl bg-white text-sky-600 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-file-lines text-xl"></i>
                            </div>

                            <div class="min-w-0 flex-1">
                                <p id="selected-file-name"
                                    class="text-sm font-bold text-slate-800 break-all">
                                </p>

                                <p id="selected-file-size"
                                    class="text-xs text-slate-500 mt-1">
                                </p>
                            </div>

                            <i class="fa-solid fa-circle-check text-emerald-500 text-lg"></i>
                        </div>

                        <div class="flex flex-wrap gap-2 mt-4 pt-3 border-t border-sky-100">

                            <button type="button"
                                id="btn-preview-selected"
                                class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition">
                                <i class="fa-regular fa-eye"></i>
                                Lihat File
                            </button>

                            <label for="file_jawaban"
                                class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-100 cursor-pointer transition">
                                <i class="fa-solid fa-rotate"></i>
                                Ganti File
                            </label>

                            <button type="button"
                                id="btn-remove-selected"
                                class="inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg bg-rose-50 border border-rose-100 text-xs font-semibold text-rose-600 hover:bg-rose-100 transition">
                                <i class="fa-solid fa-trash"></i>
                                Hapus
                            </button>
                        </div>
                    </div>

                    @error('file_jawaban')
                    <p class="text-xs text-rose-600 mt-2">{{ $message }}</p>
                    @enderror

                </div>

                <div class="flex items-start gap-2.5 rounded-xl bg-sky-50 border border-sky-100 p-3.5 text-xs text-sky-800">
                    <i class="fa-solid fa-circle-info mt-0.5 text-sky-600"></i>

                    <p class="leading-relaxed">
                        Periksa kembali berkas tugas sebelum dikirim.
                        Kamu dapat melihat pratinjau file terlebih dahulu.
                        Ukuran maksimal file adalah 10 MB.
                    </p>
                </div>

                <div class="pt-2 flex justify-center">
                    <button type="submit"
                        id="btn-kirim-tugas"
                        class="w-full sm:w-1/3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 rounded-xl shadow-lg shadow-indigo-200 transition active:scale-[0.98]">

                        <i class="fa-solid fa-paper-plane mr-2"></i>
                        Kirim Tugas
                    </button>
                </div>

            </form>

            @else

            {{-- KONDISI 2: SUDAH TERKIRIM --}}
            <div class="space-y-4">

                <div class="rounded-xl border border-emerald-100 bg-emerald-50/60 p-4">

                    <div class="flex items-center gap-2 text-emerald-700">
                        <i class="fa-solid fa-circle-check"></i>

                        <p class="text-sm font-bold">
                            Tugas berhasil dikumpulkan!
                        </p>
                    </div>

                    <p class="text-xs text-emerald-700/80 mt-1 ml-6">
                        Berkas tugas kamu sudah tersimpan dan berhasil dikirim kepada guru.
                    </p>
                </div>

                {{-- Kartu File Terkirim --}}
                @if($fileTerkirimAda)

                <div class="rounded-xl border border-slate-200 bg-white p-4">

                    <div class="flex items-center gap-3">

                        <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0
                                    {{ $fileTerkirimExt === 'pdf' ? 'bg-rose-50 text-rose-600' : (in_array($fileTerkirimExt, ['jpg', 'jpeg', 'png']) ? 'bg-sky-50 text-sky-600' : 'bg-indigo-50 text-indigo-600') }}">

                            <i class="text-xl
                                        {{ $fileTerkirimExt === 'pdf' ? 'fa-regular fa-file-pdf' : (in_array($fileTerkirimExt, ['jpg', 'jpeg', 'png']) ? 'fa-regular fa-file-image' : 'fa-regular fa-file-word') }}">
                            </i>
                        </div>

                        <div class="flex-1 min-w-0">

                            <p class="text-sm font-bold text-slate-800">
                                Berkas Tugas
                            </p>

                            <p class="text-xs text-slate-500 truncate mt-1">
                                {{ basename($fileTerkirim) }}
                            </p>
                        </div>

                        <i class="fa-solid fa-circle-check text-emerald-500 text-lg"></i>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">

                        <div class="text-xs text-slate-500">

                            <i class="fa-regular fa-calendar-check mr-1"></i>
                            Dikirim pada

                            <span class="font-semibold text-slate-700">
                                {{ $pengumpulan->updated_at
                                            ? \Carbon\Carbon::parse($pengumpulan->updated_at)->translatedFormat('d M Y, H:i')
                                            : '-' }}
                            </span>
                        </div>

                        <button type="button"
                            data-preview-url="{{ $fileTerkirimUrl }}"
                            data-preview-extension="{{ $fileTerkirimExt }}"
                            data-preview-name="{{ basename($fileTerkirim) }}"
                            class="btn-preview-file inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-indigo-50 border border-indigo-100 text-indigo-700 text-xs font-bold hover:bg-indigo-100 transition">

                            <i class="fa-regular fa-eye"></i>
                            Lihat File
                        </button>
                    </div>
                </div>

                @else

                <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs text-amber-800">
                    Berkas tugas tidak ditemukan. Silakan hubungi guru.
                </div>

                @endif

                {{-- Status Penilaian --}}
                @if($sudahDinilai)

                <div class="rounded-xl border border-indigo-100 bg-indigo-50 p-4">

                    <div class="flex items-center justify-between gap-3">

                        <div>
                            <p class="text-xs font-semibold text-indigo-700">
                                Nilai dari Guru
                            </p>

                            <p class="text-xs text-indigo-500 mt-1">
                                Hasil penilaian tugas kamu
                            </p>
                        </div>

                        <p class="text-2xl font-extrabold text-indigo-600">
                            {{ number_format($pengumpulan->nilai, 0) }}
                            <span class="text-xs text-indigo-400">/ 100</span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 text-xs text-emerald-700 bg-emerald-50 rounded-xl p-3">
                    <i class="fa-solid fa-lock"></i>
                    Tugas sudah dinilai. Berkas tidak dapat diubah lagi.
                </div>

                @else

                <div class="flex items-start gap-2.5 rounded-xl border border-amber-200 bg-amber-50 p-3.5 text-xs text-amber-800">

                    <i class="fa-solid fa-clock mt-0.5"></i>

                    <p class="leading-relaxed">
                        Tugas sedang menunggu pemeriksaan guru.
                        Kamu masih dapat melakukan revisi jika diperlukan.
                    </p>
                </div>

                {{-- Form Revisi --}}
                <details class="group rounded-xl border border-slate-200 overflow-hidden">

                    <summary class="flex items-center justify-between gap-3 p-4 cursor-pointer hover:bg-slate-50 transition">

                        <span class="text-sm font-semibold text-slate-700">
                            <i class="fa-solid fa-rotate mr-2 text-indigo-600"></i>
                            Ganti Berkas Jawaban
                        </span>

                        <i class="fa-solid fa-chevron-down text-xs text-slate-400 group-open:rotate-180 transition"></i>
                    </summary>

                    <form action="{{ route('siswa.tugas.kumpulkan', $tugas->id) }}"
                        method="POST"
                        enctype="multipart/form-data"
                        class="p-4 pt-0 space-y-3">

                        @csrf

                        <p class="text-xs text-slate-500">
                            Pilih berkas baru jika ingin mengganti tugas sebelumnya.
                        </p>

                        <input type="file"
                            name="file_jawaban"
                            accept=".pdf,.docx,.jpg,.jpeg,.png"
                            required
                            class="block w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700 file:font-semibold hover:file:bg-indigo-100">

                        @error('file_jawaban')
                        <p class="text-xs text-rose-600">{{ $message }}</p>
                        @enderror

                        <div class="flex justify-center">
                        <button type="submit"
                            class="w-full sm:w-1/3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 rounded-xl shadow-lg shadow-indigo-200 transition active:scale-[0.98]">
                            <i class="fa-solid fa-rotate mr-2"></i>
                            Simpan Revisi
                        </button>
                    </form>
                </details>
                @endif
            </div>
            @endif

        </section>

        {{-- MODAL PREVIEW FILE --}}
        <div id="preview-modal"
            class="hidden fixed inset-0 z-[999] bg-slate-900/60 backdrop-blur-sm items-center justify-center p-3 sm:p-6">

            <div class="bg-white w-full max-w-4xl rounded-2xl shadow-2xl overflow-hidden">

                <div class="flex items-center justify-between gap-3 p-4 border-b border-slate-100">

                    <div class="min-w-0">
                        <h3 class="text-sm font-bold text-slate-800">
                            Pratinjau Berkas
                        </h3>

                        <p id="preview-file-name" class="text-xs text-slate-500 truncate mt-1"></p>
                    </div>

                    <button type="button"
                        onclick="closeFilePreview()"
                        class="w-9 h-9 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 flex items-center justify-center shrink-0">

                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="bg-slate-100 p-3 min-h-[300px] max-h-[75vh] overflow-auto flex items-center justify-center">

                    <img id="preview-image"
                        class="hidden max-w-full max-h-[65vh] object-contain rounded-lg shadow-sm"
                        alt="Pratinjau gambar">

                    <iframe id="preview-pdf"
                        class="hidden w-full h-[65vh] rounded-lg bg-white"
                        title="Pratinjau PDF">
                    </iframe>

                    <div id="preview-docx" class="hidden text-center p-8">

                        <i class="fa-regular fa-file-word text-5xl text-indigo-500"></i>

                        <p class="text-sm font-semibold text-slate-700 mt-3">
                            Pratinjau DOCX tidak tersedia langsung di browser.
                        </p>

                        <p class="text-xs text-slate-500 mt-1">
                            Silakan buka atau unduh berkas untuk melihat isinya.
                        </p>

                        <a id="preview-download"
                            href="#"
                            target="_blank"
                            class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs font-semibold">

                            <i class="fa-solid fa-download"></i>
                            Buka / Unduh File
                        </a>
                    </div>
                </div>

                <div class="p-3 border-t border-slate-100 flex justify-end">

                    <button type="button"
                        onclick="closeFilePreview()"
                        class="px-5 py-2 rounded-lg bg-slate-100 text-slate-700 text-xs font-semibold hover:bg-slate-200">

                        Tutup
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    let selectedPreviewUrl = null;

    const fileInput = document.getElementById('file_jawaban');

    // Membuka preview file
    function openFilePreview(url, extension, filename) {
        const modal = document.getElementById('preview-modal');
        const image = document.getElementById('preview-image');
        const pdf = document.getElementById('preview-pdf');
        const docx = document.getElementById('preview-docx');

        image.classList.add('hidden');
        pdf.classList.add('hidden');
        docx.classList.add('hidden');

        image.src = '';
        pdf.src = '';

        document.getElementById('preview-file-name').textContent = filename;

        extension = extension.toLowerCase();

        if (['jpg', 'jpeg', 'png'].includes(extension)) {
            image.src = url;
            image.classList.remove('hidden');

        } else if (extension === 'pdf') {
            pdf.src = url;
            pdf.classList.remove('hidden');

        } else {
            document.getElementById('preview-download').href = url;
            docx.classList.remove('hidden');
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    // Menutup preview
    function closeFilePreview() {
        const modal = document.getElementById('preview-modal');

        modal.classList.add('hidden');
        modal.classList.remove('flex');

        document.getElementById('preview-pdf').src = '';
        document.getElementById('preview-image').src = '';
    }

    // Preview file yang sudah tersimpan
    document.querySelectorAll('.btn-preview-file').forEach(button => {
        button.addEventListener('click', function() {
            openFilePreview(
                this.dataset.previewUrl,
                this.dataset.previewExtension,
                this.dataset.previewName
            );
        });
    });

    // Memilih file baru
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const file = this.files[0];

            if (!file) return;

            const allowedExtensions = ['pdf', 'docx', 'jpg', 'jpeg', 'png'];
            const extension = file.name.split('.').pop().toLowerCase();

            if (!allowedExtensions.includes(extension)) {
                alert('Format file tidak didukung.');
                this.value = '';
                return;
            }

            if (file.size > 10 * 1024 * 1024) {
                alert('Ukuran file maksimal 10 MB.');
                this.value = '';
                return;
            }

            if (selectedPreviewUrl) {
                URL.revokeObjectURL(selectedPreviewUrl);
            }

            selectedPreviewUrl = URL.createObjectURL(file);

            document.getElementById('selected-file-name').textContent = file.name;

            document.getElementById('selected-file-size').textContent =
                (file.size / (1024 * 1024)).toFixed(2) + ' MB';

            document.getElementById('upload-area').classList.add('hidden');
            document.getElementById('selected-file-card').classList.remove('hidden');

            const icon = document.getElementById('selected-file-icon');

            if (extension === 'pdf') {
                icon.innerHTML = '<i class="fa-regular fa-file-pdf text-xl"></i>';
                icon.className = 'w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0';

            } else if (['jpg', 'jpeg', 'png'].includes(extension)) {
                icon.innerHTML = '<i class="fa-regular fa-file-image text-xl"></i>';
                icon.className = 'w-11 h-11 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0';

            } else {
                icon.innerHTML = '<i class="fa-regular fa-file-word text-xl"></i>';
                icon.className = 'w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0';
            }
        });
    }

    // Preview file sebelum dikirim
    const previewButton = document.getElementById('btn-preview-selected');

    if (previewButton) {
        previewButton.addEventListener('click', function() {
            const file = fileInput?.files[0];

            if (file && selectedPreviewUrl) {
                const extension = file.name.split('.').pop().toLowerCase();

                openFilePreview(selectedPreviewUrl, extension, file.name);
            }
        });
    }

    // Menghapus file yang dipilih
    const removeButton = document.getElementById('btn-remove-selected');

    if (removeButton) {
        removeButton.addEventListener('click', function() {
            if (selectedPreviewUrl) {
                URL.revokeObjectURL(selectedPreviewUrl);
                selectedPreviewUrl = null;
            }

            fileInput.value = '';

            document.getElementById('selected-file-card').classList.add('hidden');
            document.getElementById('upload-area').classList.remove('hidden');
        });
    }

    // Validasi sebelum submit
    const submitForm = document.getElementById('form-kumpul-tugas');

    if (submitForm) {
        submitForm.addEventListener('submit', function(event) {
            if (!fileInput.files.length) {
                event.preventDefault();
                alert('Silakan pilih file jawaban terlebih dahulu.');
            }
        });
    }

    // Tutup modal jika klik area luar
    document.getElementById('preview-modal').addEventListener('click', function(event) {
        if (event.target === this) {
            closeFilePreview();
        }
    });
</script>

@endsection