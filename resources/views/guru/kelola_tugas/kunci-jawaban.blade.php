@extends('layouts.app')

@section('title', 'Kunci Jawaban')

@section('content')
<div class="space-y-5 text-slate-800">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('guru.tugas.show', $tugas->id) }}"
                class="mb-3 inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-blue-600">
                <span>&larr;</span>
                Kembali ke Detail Tugas
            </a>

            <h1 class="text-2xl font-bold text-slate-800">
                Kunci Jawaban
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Daftar soal dan kunci jawaban tugas pilihan ganda.
            </p>
        </div>

        <div class="rounded-xl bg-blue-50 px-4 py-3 text-sm text-blue-700">
            <span class="font-semibold">Total Soal:</span>
            {{ $soals->count() }} soal
        </div>
    </div>

    {{-- Informasi Tugas --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-slate-500">Nama Tugas</p>
                <h2 class="mt-1 text-lg font-bold text-slate-800">
                    {{ $tugas->judul }}
                </h2>

                <p class="mt-2 text-sm text-slate-500">
                    {{ $tugas->pengampuKelas?->kelas?->nama_kelas ?? 'Kelas tidak tersedia' }}
                </p>
            </div>

            <span class="w-fit rounded-full bg-blue-100 px-4 py-2 text-xs font-semibold text-blue-700">
                Pilihan Ganda
            </span>
        </div>
    </div>

    {{-- Keterangan --}}
    <div class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4">
        <div class="text-emerald-600">
            <svg xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.291 9 11.622C17.176 19.291 21 14.591 21 9c0-1.042-.133-2.052-.382-3.016z" />
            </svg>
        </div>

        <div>
            <p class="text-sm font-semibold text-emerald-800">
                Informasi Kunci Jawaban
            </p>
            <p class="mt-1 text-sm text-emerald-700">
                Jawaban yang ditandai dengan warna hijau merupakan kunci jawaban yang telah ditentukan oleh guru.
            </p>
        </div>
    </div>

    {{-- Daftar Soal --}}
    <div class="space-y-5">

        @forelse ($soals as $index => $soal)

        @php
        $pilihan = $soal->pilihan ?? [];

        $kunci = $soal->kunci_jawaban ?? '';

        if (is_array($kunci)) {
        $kunci = $kunci['jawaban'] ?? $kunci[0] ?? '';
        }

        $kunci = strtolower(trim((string) $kunci));

        $daftarPilihan = [
        'a' => 'A',
        'b' => 'B',
        'c' => 'C',
        'd' => 'D',
        'e' => 'E',
        ];
        @endphp

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            {{-- Nomor dan Pertanyaan --}}
            <div class="border-b border-slate-100 p-5">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <span class="rounded-lg bg-blue-100 px-3 py-1.5 text-sm font-bold text-blue-700">
                        Soal {{ $index + 1 }}
                    </span>

                    <span class="text-xs font-medium text-slate-500">
                        Bobot: {{ $soal->bobot ?? 0 }} poin
                    </span>
                </div>

                <p class="whitespace-pre-line text-base font-semibold leading-relaxed text-slate-800">
                    {{ $soal->pertanyaan }}
                </p>

                {{-- Gambar Soal --}}
                @if ($soal->gambar)
                <div class="mt-4">
                    <img
                        src="{{ asset('storage/' . $soal->gambar) }}"
                        alt="Gambar soal {{ $index + 1 }}"
                        class="max-h-72 rounded-xl border border-slate-200 object-contain">
                </div>
                @endif
            </div>

            {{-- Pilihan Jawaban --}}
            <div class="space-y-3 p-5">

                <p class="mb-3 text-sm font-semibold text-slate-600">
                    Pilihan Jawaban
                </p>

                @foreach ($daftarPilihan as $key => $label)

                @php
                $teksPilihan = $pilihan[$key] ?? null;
                $isCorrect = $kunci === $key;
                @endphp

                @if ($teksPilihan !== null && $teksPilihan !== '')
                <div class="flex items-start gap-3 rounded-xl border p-4
                                    {{ $isCorrect
                                        ? 'border-emerald-300 bg-emerald-50'
                                        : 'border-slate-200 bg-white' }}">

                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-sm font-bold
                                        {{ $isCorrect
                                            ? 'bg-emerald-600 text-white'
                                            : 'bg-slate-100 text-slate-600' }}">
                        {{ strtoupper($label) }}
                    </span>

                    <div class="min-w-0 flex-1 pt-1">
                        <p class="text-sm leading-relaxed
                                            {{ $isCorrect
                                                ? 'font-semibold text-emerald-800'
                                                : 'text-slate-700' }}">
                            {{ $teksPilihan }}
                        </p>
                    </div>

                    @if ($isCorrect)
                    <span class="shrink-0 rounded-full bg-emerald-600 px-3 py-1 text-xs font-semibold text-white">
                        Benar
                    </span>
                    @endif

                </div>
                @endif

                @endforeach

                {{-- Jika Kunci Tidak Ditemukan --}}
                @if (!in_array($kunci, ['a', 'b', 'c', 'd', 'e']))
                <p class="rounded-lg bg-amber-50 p-3 text-sm text-amber-700">
                    Kunci jawaban belum tersedia atau formatnya belum sesuai.
                </p>
                @endif

            </div>
        </div>

        @empty

        <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center">
            <div class="mb-3 text-4xl">📝</div>

            <h3 class="font-semibold text-slate-700">
                Belum Ada Soal
            </h3>

            <p class="mt-1 text-sm text-slate-500">
                Belum ada soal pilihan ganda yang ditambahkan pada tugas ini.
            </p>
        </div>

        @endforelse

    </div>

    {{-- Tombol Kembali --}}
    <div class="flex justify-end pt-2">
        <a href="{{ route('guru.tugas.show', $tugas->id) }}"
            class="inline-flex items-center rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-indigo-700">
        Kembali ke Detail Tugas
        </a>
    </div>

</div>
</div>
@endsection