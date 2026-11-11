@extends('layouts.siswa')

@section('title', 'Detail Tugas - ' . $tugas->judul)

@section('content')

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
            $lampiranExt = strtolower(
            pathinfo($tugas->file_lampiran, PATHINFO_EXTENSION)
            );

            $lampiranUrl = asset('storage/' . $tugas->file_lampiran);
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



        {{-- 6. Card Pengumpulan dan Nilai --}}
        <section class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-sm space-y-5">

            @php
            $sudahDinilai = $pengumpulan && $pengumpulan->nilai > 0;
            @endphp


            {{-- Header Pengumpulan --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">

                <div class="flex items-center gap-3 min-w-0">

                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-cloud-arrow-up text-base"></i>
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

                <span class="text-[11px] font-semibold sm:text-right {{ $sudahDinilai ? 'text-emerald-600' : ($pengumpulan ? 'text-amber-600' : 'text-rose-600') }}">

                    {{ $sudahDinilai
                        ? 'Sudah Dinilai'
                        : ($pengumpulan ? 'Menunggu Penilaian' : 'Belum Terkirim') }}

                </span>

            </div>



            {{-- 7. Informasi Pengumpulan Sebelumnya --}}
            @if($pengumpulan)

            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 space-y-4">

                <p class="text-xs text-slate-500">

                    Dikumpulkan pada:

                    <strong class="text-slate-700">
                        {{ \Carbon\Carbon::parse($pengumpulan->updated_at)->translatedFormat('d M Y, H:i') }}
                    </strong>

                </p>


                {{-- Nilai Guru --}}
                @if($sudahDinilai)

                <div class="flex items-center justify-between gap-3 rounded-xl border border-indigo-100 bg-white p-3.5">

                    <div>

                        <p class="text-xs font-semibold text-slate-600">
                            Nilai dari Guru
                        </p>

                        <p class="text-[11px] text-slate-400 mt-1">
                            Hasil penilaian tugas
                        </p>

                    </div>

                    <p class="text-xl font-extrabold text-indigo-600 shrink-0">

                        {{ number_format($pengumpulan->nilai, 0) }}

                        <span class="text-xs text-slate-400 font-semibold">
                            / 100
                        </span>

                    </p>

                </div>

                @else

                <div class="flex items-start gap-2.5 rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs text-amber-800">

                    <i class="fa-solid fa-clock mt-0.5 text-amber-600"></i>

                    <p class="leading-relaxed">
                        Tugas sudah terkirim dan sedang menunggu pemeriksaan guru.
                    </p>

                </div>

                @endif



                {{-- 8. Preview File Jawaban Siswa --}}
                @if($pengumpulan->file_jawaban)

                @php
                $fileExt = strtolower(
                pathinfo($pengumpulan->file_jawaban, PATHINFO_EXTENSION)
                );

                $fileUrl = asset('storage/' . $pengumpulan->file_jawaban);
                @endphp

                <div class="pt-3 border-t border-slate-200 space-y-3">

                    <div class="flex items-center justify-between gap-2">

                        <div>

                            <h3 class="text-xs font-bold text-slate-700">
                                Berkas Jawaban Kamu
                            </h3>

                            <p class="text-[11px] text-slate-500 mt-1 break-all">
                                {{ basename($pengumpulan->file_jawaban) }}
                            </p>

                        </div>

                        <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-[10px] font-bold uppercase shrink-0">
                            {{ $fileExt }}
                        </span>

                    </div>


                    {{-- Preview PDF --}}
                    @if($fileExt === 'pdf')

                    <div class="rounded-xl border border-slate-200 overflow-hidden bg-slate-100">

                        <iframe
                            src="{{ $fileUrl }}"
                            class="w-full h-[500px] sm:h-[650px]"
                            title="Preview Jawaban PDF">
                        </iframe>

                    </div>


                    {{-- Preview JPG dan PNG --}}
                    @elseif(in_array($fileExt, ['jpg', 'png', 'jpeg']))

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 flex justify-center">

                        <img
                            src="{{ $fileUrl }}"
                            alt="Jawaban Tugas Siswa"
                            class="max-w-full max-h-[650px] object-contain rounded-lg"
                            loading="lazy">

                    </div>


                    {{-- Preview DOCX --}}
                    @elseif($fileExt === 'docx')

                    <div class="rounded-xl border border-slate-200 bg-white p-4 space-y-3">

                        <div class="flex items-center gap-3">

                            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                <i class="fa-regular fa-file-word text-xl"></i>
                            </div>

                            <div class="min-w-0">

                                <p class="text-sm font-semibold text-slate-800">
                                    Dokumen Word
                                </p>

                                <p class="text-xs text-slate-500 break-all">
                                    {{ basename($pengumpulan->file_jawaban) }}
                                </p>

                            </div>

                        </div>

                        <div class="rounded-lg bg-amber-50 border border-amber-100 p-3 text-xs text-amber-800 leading-relaxed">

                            <i class="fa-solid fa-circle-info mr-1"></i>

                            Format DOCX belum dapat ditampilkan langsung oleh browser. Untuk menampilkan isi dokumen di halaman ini, file perlu dikonversi ke PDF terlebih dahulu.

                        </div>

                        <a href="{{ $fileUrl }}"
                            target="_blank"
                            class="inline-flex items-center gap-2 text-xs font-semibold text-sky-700 hover:text-sky-900 hover:underline">

                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            Buka Dokumen

                        </a>

                    </div>

                    @else

                    <div class="rounded-xl border border-slate-200 bg-white p-4">

                        <p class="text-xs text-slate-500">
                            Format file belum mendukung preview langsung.
                        </p>

                    </div>

                    @endif

                </div>

                @endif

            </div>

            @endif



            {{-- 9. Form Upload atau Revisi --}}
            @if(!$sudahDinilai)

            <form action="{{ route('siswa.tugas.kumpulkan', $tugas->id) }}"
                method="POST"
                enctype="multipart/form-data"
                class="space-y-4">

                @csrf


                {{-- Upload File --}}
                <div>

                    <label for="file_tugas_input"
                        class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">

                        {{ $pengumpulan ? 'Ganti Berkas Jawaban (Revisi)' : 'Unggah Berkas Jawaban' }}

                    </label>


                    <label for="file_tugas_input"
                        class="flex flex-col items-center justify-center text-center rounded-xl border-2 border-dashed border-slate-300 bg-slate-50/60 hover:bg-sky-50/60 hover:border-sky-400 p-6 sm:p-7 cursor-pointer transition-colors">

                        <div class="w-11 h-11 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-sky-600 mb-3">

                            <i class="fa-solid fa-cloud-arrow-up text-xl"></i>

                        </div>

                        <p class="text-sm font-semibold text-slate-700">

                            <span class="text-sky-700">
                                Klik untuk memilih file
                            </span>

                        </p>

                        <p class="text-xs text-slate-500 mt-1">
                            Format PDF, DOCX, JPG, atau PNG
                        </p>

                        <p class="text-xs text-slate-400 mt-1">
                            Ukuran maksimal 10 MB
                        </p>

                        <p id="nama-file"
                            class="text-xs text-sky-700 font-semibold mt-3 break-all">

                            Belum ada file dipilih

                        </p>


                        <input
                            type="file"
                            name="file_tugas_input"
                            id="file_tugas_input"
                            {{ $pengumpulan ? '' : 'required' }}
                            accept=".pdf,.docx,.jpg,.png"
                            class="hidden"
                            onchange="document.getElementById('nama-file').textContent = this.files.length ? this.files[0].name : 'Belum ada file dipilih'">

                    </label>

                    @error('file_tugas_input')
                    <p class="text-xs text-rose-600 mt-2">
                        {{ $message }}
                    </p>
                    @enderror

                </div>



                {{-- Informasi Pengumpulan --}}
                <div class="flex items-start gap-2.5 rounded-xl bg-sky-50 border border-sky-100 p-3.5 text-xs text-sky-800">

                    <i class="fa-solid fa-circle-info mt-0.5 text-sky-600"></i>

                    <p class="leading-relaxed">

                        @if($pengumpulan)

                        Kamu dapat mengganti berkas jawaban selama tugas belum dinilai oleh guru. Jika tidak memilih file baru, pengumpulan sebelumnya tetap dipertahankan.

                        @else

                        Pastikan berkas jawaban sudah benar sebelum dikirim. Format yang didukung adalah PDF, DOCX, JPG, dan PNG dengan ukuran maksimal 10 MB.

                        @endif

                    </p>

                </div>



                {{-- 10. Tombol Submit --}}
                <div class="pt-5 flex justify-center">

                    <button
                        type="submit"
                        class="w-1/3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 rounded-xl shadow-lg shadow-indigo-200 transition duration-200 active:scale-[0.98]">

                        {{ $pengumpulan ? 'Simpan Perubahan (Revisi)' : 'Kirim Tugas Sekarang' }}

                    </button>

                </div>

    </div>

    </form>


    @else

    {{-- Tugas Sudah Dinilai --}}
    <div class="rounded-xl bg-indigo-50 border border-indigo-100 p-3.5 flex items-start gap-2.5 text-xs text-indigo-800">

        <i class="fa-solid fa-circle-info mt-0.5"></i>

        <p class="leading-relaxed">
            Tugas ini sudah dinilai oleh guru dan jawaban tidak dapat diubah lagi.
        </p>

    </div>

    @endif

    </section>

</div>

</div>

@endsection