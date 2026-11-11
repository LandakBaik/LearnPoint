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
    @if(session('error') || $errors->any())
    <div class="max-w-3xl mx-auto w-full px-4 sm:px-0">
        <div class="p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 text-xs">

            <p class="font-bold mb-1">
                Pengumpulan gagal.
            </p>

            @if(session('error'))
            <p>{{ session('error') }}</p>
            @endif

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

                {{-- Deadline dan Tipe Tugas --}}
                <div class="flex flex-wrap items-center gap-2">

                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-sky-50 text-sky-700 border border-sky-100">
                        <i class="fa-regular fa-calendar"></i>
                        Deadline:
                        {{ $tugas->deadline
                            ? \Carbon\Carbon::parse($tugas->deadline)->translatedFormat('d M Y, H:i')
                            : 'Tidak ditentukan' }}
                    </span>

                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                        <i class="fa-solid fa-list-check"></i>
                        Pilihan Ganda
                    </span>

                </div>

                {{-- Judul Tugas --}}
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


            {{-- Informasi Guru dan Mapel --}}
            <div class="pt-4 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-2 gap-4">

                {{-- Guru Pengampu --}}
                <div class="flex items-center gap-3 min-w-0">

                    <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-user-tie"></i>
                    </div>

                    <div class="min-w-0">

                        <p class="text-xs font-semibold text-slate-400">
                            Guru Pengampu
                        </p>

                        <p class="text-sm font-semibold text-slate-700 truncate">
                            {{ $tugas->pengampuKelas?->guru?->nama_guru
                                ?? $tugas->pengampuKelas?->guru?->user?->name
                                ?? '-' }}
                        </p>

                    </div>

                </div>


                {{-- Mata Pelajaran --}}
                <div class="flex items-center gap-3 min-w-0">

                    <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-book"></i>
                    </div>

                    <div class="min-w-0">

                        <p class="text-xs font-semibold text-slate-400">
                            Mata Pelajaran
                        </p>

                        <p class="text-sm font-semibold text-slate-700 truncate">
                            {{ $tugas->pengampuKelas?->mapel?->nama_mapel ?? '-' }}
                        </p>

                    </div>

                </div>

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


        {{-- 6. Informasi Nilai --}}
        @if($pengumpulan)

        <section class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-sm">

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">

                <div class="flex items-center gap-3">

                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-circle-check text-lg"></i>
                    </div>

                    <div>

                        <h2 class="text-base font-bold text-slate-900">
                            Tugas Selesai Dikerjakan
                        </h2>

                        <p class="text-xs text-slate-500 mt-0.5">
                            Jawaban berhasil dikumpulkan dan dinilai otomatis.
                        </p>

                    </div>

                </div>


                {{-- Nilai Akhir --}}
                <div class="rounded-xl border border-indigo-100 bg-indigo-50 px-5 py-3 text-center">

                    <p class="text-xs font-semibold text-indigo-500">
                        Nilai Akhir
                    </p>

                    <p class="text-2xl font-extrabold text-indigo-700">
                        {{ number_format($pengumpulan->nilai, 2) }}
                        <span class="text-xs font-semibold text-indigo-400">
                            / 100
                        </span>
                    </p>

                </div>

            </div>


            {{-- Informasi Jawaban Terkunci --}}
            <div class="mt-4 flex items-start gap-2.5 rounded-xl bg-emerald-50 border border-emerald-100 p-3.5 text-xs text-emerald-800">

                <i class="fa-solid fa-lock mt-0.5 text-emerald-600"></i>

                <p class="leading-relaxed">
                    Jawaban tugas sudah dikunci. Kamu tidak dapat mengubah atau mengerjakan ulang tugas ini setelah dikumpulkan.
                </p>

            </div>

        </section>

        @endif


        {{-- 7. Daftar Soal --}}
        <section class="space-y-4">

            {{-- Header Soal --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">

                <div>

                    <h2 class="text-base font-bold text-slate-900">
                        Soal Pilihan Ganda
                    </h2>

                    <p class="text-xs text-slate-500 mt-1">
                        {{ $soals->count() }} soal tersedia. Pilih satu jawaban yang paling tepat.
                    </p>

                </div>


                @if(!$pengumpulan)

                <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-sky-700 bg-sky-50 border border-sky-100 px-3 py-1.5 rounded-full">
                    <i class="fa-solid fa-pen-to-square"></i>
                    Belum Dikerjakan
                </span>

                @else

                <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-emerald-700 bg-emerald-50 border border-emerald-100 px-3 py-1.5 rounded-full">
                    <i class="fa-solid fa-check-circle"></i>
                    Sudah Dikumpulkan
                </span>

                @endif

            </div>


            {{-- Jika Belum Ada Soal --}}
            @if($soals->isEmpty())

            <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center shadow-sm">

                <div class="w-12 h-12 mx-auto rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                    <i class="fa-solid fa-file-circle-question text-xl"></i>
                </div>

                <p class="text-sm font-semibold text-slate-700">
                    Belum ada soal tersedia
                </p>

                <p class="text-xs text-slate-400 mt-1">
                    Guru belum menambahkan soal untuk tugas ini.
                </p>

            </div>

            @else


            {{-- 8. Form Jawaban --}}
            <form action="{{ route('siswa.tugas.kumpulkan', $tugas->id) }}"
                method="POST"
                id="formTugas"
                class="space-y-4">

                @csrf


                {{-- Loop Soal --}}
                @foreach($soals as $index => $soal)

                @php
                $jawabanTerpilih = $jawabanSiswa->get($soal->id)?->jawaban_dipilih;
                $pilihan = $soal->pilihan ?? [];
                @endphp


                <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-sm space-y-4">

                    {{-- Nomor dan Bobot --}}
                    <div class="flex items-center justify-between gap-3">

                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-sky-50 text-sky-700 text-xs font-bold">
                            Soal {{ $index + 1 }}
                        </span>

                        <span class="text-[11px] font-semibold text-slate-400">
                            Bobot: {{ $soal->bobot ?? 1 }}
                        </span>

                    </div>


                    {{-- Pertanyaan --}}
                    <div class="text-sm sm:text-base font-semibold leading-relaxed text-slate-800">
                        {{ $soal->pertanyaan }}
                    </div>


                    {{-- Gambar Soal --}}
                    @if($soal->gambar)

                    <div>
                        <img
                            src="{{ asset('storage/' . $soal->gambar) }}"
                            alt="Gambar soal"
                            class="max-h-72 max-w-full rounded-xl border border-slate-200 object-contain">
                    </div>

                    @endif


                    {{-- Pilihan Jawaban --}}
                    <div class="space-y-2.5">

                        @foreach(['a', 'b', 'c', 'd', 'e'] as $huruf)

                        @if(isset($pilihan[$huruf]) && $pilihan[$huruf] !== null && $pilihan[$huruf] !== '')

                        <label class="flex items-start gap-3 p-3.5 sm:p-4 rounded-xl border transition
                                            {{ strtolower((string) $jawabanTerpilih) === $huruf
                                                ? 'border-sky-400 bg-sky-50'
                                                : 'border-slate-200 bg-white hover:border-sky-300 hover:bg-slate-50' }}
                                            {{ $pengumpulan ? 'cursor-not-allowed' : 'cursor-pointer' }}">

                            <input
                                type="radio"
                                name="jawaban[{{ $soal->id }}]"
                                value="{{ $huruf }}"
                                class="mt-1 w-4 h-4 accent-sky-700 shrink-0"
                                {{ strtolower((string) $jawabanTerpilih) === $huruf ? 'checked' : '' }}
                                {{ $pengumpulan ? 'disabled' : '' }}
                                {{ !$pengumpulan ? 'required' : '' }}>


                            {{-- Huruf Pilihan --}}
                            <span class="w-7 h-7 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center text-xs font-bold uppercase shrink-0">
                                {{ $huruf }}
                            </span>


                            {{-- Isi Pilihan --}}
                            <span class="text-sm text-slate-700 leading-relaxed">
                                {{ $pilihan[$huruf] }}
                            </span>

                        </label>

                        @endif

                        @endforeach

                    </div>

                </div>

                @endforeach


                {{-- 9. Tombol Pengumpulan --}}
                @if(!$pengumpulan)

                <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-sm space-y-4">

                    {{-- Informasi Sebelum Mengumpulkan --}}
                    <div class="flex items-start gap-2.5 rounded-xl bg-sky-50 border border-sky-100 p-3.5 text-xs text-sky-800">

                        <i class="fa-solid fa-circle-info mt-0.5 text-sky-600"></i>

                        <p class="leading-relaxed">
                            Periksa kembali seluruh jawaban sebelum mengumpulkan. Setelah dikirim, jawaban tidak dapat diubah atau dikerjakan ulang. Nilai akan dihitung secara otomatis oleh sistem.
                        </p>

                    </div>


                    {{-- Tombol Submit --}}
                    <div class="pt-5 flex justify-center">

                        <button
                            type="submit"
                            class="w-1/2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 rounded-xl shadow-lg shadow-indigo-200 transition duration-200 active:scale-[0.98]">

                            {{ $pengumpulan ? 'Simpan Perubahan (Revisi)' : 'Kirim Jawaban' }}

                        </button>

                    </div>

                </div>

    </div>

    @else

    {{-- Sudah Dikumpulkan --}}
    <div class="rounded-xl bg-emerald-50 border border-emerald-100 p-4 flex items-start gap-2.5 text-xs text-emerald-800">

        <i class="fa-solid fa-circle-check mt-0.5 text-emerald-600"></i>

        <p class="leading-relaxed">
            Kamu sudah menyelesaikan tugas ini. Jawaban tidak dapat diubah kembali.
        </p>

    </div>

    @endif

    </form>

    @endif

    </section>

</div>

</div>

@endsection