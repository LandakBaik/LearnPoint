@extends('layouts.siswa')

@section('title', 'Detail Tugas - ' . $tugas->judul)

@section('content')

<div class="space-y-5">

    {{-- Navbar Detail Tugas --}}
    <div class="flex flex-wrap items-center gap-3 text-sm font-medium">

        <a href="{{ route('siswa.beranda') }}"
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

    {{-- Notifikasi Berhasil --}}
    @if(session('success'))
        <div class="max-w-3xl mx-auto w-full px-4 sm:px-0">
            <div class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-xs font-semibold">
                <i class="fa-solid fa-circle-check mr-1.5"></i>
                {{ session('success') }}
            </div>
        </div>
    @endif

    {{-- Notifikasi Error --}}
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

    {{-- Konten Utama --}}
    <div class="max-w-3xl mx-auto w-full px-4 sm:px-0 space-y-5">

        {{-- Card Informasi Tugas --}}
        <section class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-sm space-y-4">

            {{-- Judul dan Deadline --}}
            <div class="space-y-3">

                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-sky-50 text-sky-700 border border-sky-100">
                        <i class="fa-regular fa-calendar"></i>
                        Deadline:
                        {{ \Carbon\Carbon::parse($tugas->deadline)->translatedFormat('d M Y, H:i') }}
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

                <div class="pt-4 border-t border-slate-100">

                    <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">
                        Berkas Lampiran
                    </h2>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 rounded-xl border border-slate-200 bg-slate-50">

                        <div class="flex items-center gap-3 min-w-0">

                            <div class="w-10 h-10 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                                <i class="fa-regular fa-file-pdf text-lg"></i>
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

                        <a href="{{ asset('storage/' . $tugas->file_lampiran) }}"
                            download
                            class="inline-flex items-center justify-center gap-2 px-3.5 py-2 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition shrink-0">

                            <i class="fa-solid fa-download"></i>
                            Unduh Berkas

                        </a>

                    </div>

                </div>

            @endif

        </section>

        {{-- Card Pengumpulan dan Nilai --}}
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
                    {{ $sudahDinilai ? 'Sudah Dinilai' : ($pengumpulan ? 'Menunggu Penilaian' : 'Belum Terkirim') }}
                </span>

            </div>

            {{-- Informasi Pengumpulan Sebelumnya --}}
            @if($pengumpulan)

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 space-y-3">

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

                    {{-- Lihat File Terunggah --}}
                    @if($pengumpulan->file_jawaban)

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">

                            <a href="{{ asset('storage/' . $pengumpulan->file_jawaban) }}"
                                target="_blank"
                                class="inline-flex items-center gap-2 text-xs font-semibold text-sky-700 hover:text-sky-800 hover:underline break-all">

                                <i class="fa-regular fa-eye shrink-0"></i>
                                Lihat Berkas Terunggah

                            </a>

                        </div>

                    @endif

                </div>

            @endif

            {{-- Form Upload atau Revisi --}}
            @if(!$sudahDinilai)

                <form action="{{ route('siswa.tugas.kumpulkan', $tugas->id) }}"
                    method="POST"
                    enctype="multipart/form-data"
                    class="space-y-4">

                    @csrf

                    {{-- Upload File --}}
                    <div>

                        <label for="file_jawaban"
                            class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">

                            {{ $pengumpulan ? 'Ganti Berkas Jawaban (Revisi)' : 'Unggah Berkas Jawaban' }}

                        </label>

                        <label for="file_jawaban"
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
                                Format PDF, DOC, DOCX, ZIP, atau RAR
                            </p>

                            <p class="text-xs text-slate-400 mt-1">
                                Ukuran maksimal 5 MB
                            </p>

                            <p id="nama-file"
                                class="text-xs text-sky-700 font-semibold mt-3 break-all">
                                Belum ada file dipilih
                            </p>

                            <input type="file"
                                name="file_jawaban"
                                id="file_jawaban"
                                required
                                accept=".pdf,.doc,.docx,.zip,.rar"
                                class="hidden"
                                onchange="document.getElementById('nama-file').textContent = this.files.length ? this.files[0].name : 'Belum ada file dipilih'">

                        </label>

                    </div>

                    {{-- Informasi Pengumpulan --}}
                    <div class="flex items-start gap-2.5 rounded-xl bg-sky-50 border border-sky-100 p-3.5 text-xs text-sky-800">

                        <i class="fa-solid fa-circle-info mt-0.5 text-sky-600"></i>

                        <p class="leading-relaxed">

                            @if($pengumpulan)

                                Kamu dapat mengganti berkas jawaban selama tugas belum dinilai oleh guru.

                            @else

                                Pastikan berkas jawaban sudah benar sebelum dikirim. Format yang didukung adalah PDF, DOC, DOCX, ZIP, dan RAR.

                            @endif

                        </p>

                    </div>

                    {{-- Tombol Kirim --}}
                    <div class="flex justify-center pt-1">

                        <button type="submit"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-sky-700 hover:bg-sky-800 text-white text-xs font-bold shadow-sm transition active:scale-95">

                            {{ $pengumpulan ? 'Simpan Perubahan (Revisi)' : 'Kirim Tugas Sekarang' }}

                        </button>

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