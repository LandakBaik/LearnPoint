@extends('layouts.app')

@section('title', 'Detail Jawaban Siswa')

@section('content')
<div class="space-y-5 text-slate-800">

    {{-- Header --}}
    <div>
        <a href="{{ route('guru.tugas.show', $tugas->id) }}"
            class="mb-3 inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-blue-600">
            &larr; Kembali ke Detail Tugas
        </a>

        <h1 class="text-2xl font-bold text-slate-800">
            Detail Jawaban Siswa
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Hasil pengerjaan soal pilihan ganda siswa.
        </p>
    </div>

    {{-- Informasi Siswa --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <p class="text-sm text-slate-500">Nama Siswa</p>

                <h2 class="mt-1 text-lg font-bold text-slate-800">
                    {{ $siswa?->nama ?? 'Nama tidak tersedia' }}
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    NIS: {{ $siswa?->nis ?? '-' }}
                </p>
            </div>

            <div class="rounded-xl bg-blue-50 px-4 py-3">
                <p class="text-xs text-blue-600">Tugas</p>
                <p class="mt-1 font-semibold text-blue-800">
                    {{ $tugas->judul }}
                </p>
            </div>

        </div>
    </div>

    {{-- Ringkasan Nilai --}}
    @php
    $totalSoal = $soals->count();
    $jumlahTerjawab = 0;
    $jumlahBenar = 0;
    $jumlahSalah = 0;

    foreach ($soals as $soal) {
    $jawabanSiswa = $jawabans->get($soal->id);

    if ($jawabanSiswa && $jawabanSiswa->jawaban_dipilih) {
    $jumlahTerjawab++;

    if ($jawabanSiswa->benar) {
    $jumlahBenar++;
    } else {
    $jumlahSalah++;
    }
    }
    }

    $belumDijawab = $totalSoal - $jumlahTerjawab;
    @endphp

    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">

        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-sm text-slate-500">Total Soal</p>
            <p class="mt-2 text-2xl font-bold text-slate-800">
                {{ $totalSoal }}
            </p>
        </div>

        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
            <p class="text-sm text-blue-600">Terjawab</p>
            <p class="mt-2 text-2xl font-bold text-blue-700">
                {{ $jumlahTerjawab }}
            </p>
        </div>

        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
            <p class="text-sm text-emerald-600">Benar</p>
            <p class="mt-2 text-2xl font-bold text-emerald-700">
                {{ $jumlahBenar }}
            </p>
        </div>

        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
            <p class="text-sm text-red-600">Salah</p>
            <p class="mt-2 text-2xl font-bold text-red-700">
                {{ $jumlahSalah }}
            </p>
        </div>

    </div>

    {{-- Informasi Nilai --}}
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-slate-500">Nilai Siswa</p>
                <p class="mt-1 text-3xl font-bold text-slate-800">
                    {{ number_format((float) $pengumpulan->nilai, 2, ',', '.') }}
                </p>
            </div>

            <div class="text-sm text-slate-500">
                Belum dijawab:
                <span class="font-semibold text-slate-700">
                    {{ $belumDijawab }} soal
                </span>
            </div>
        </div>
    </div>

    {{-- Daftar Jawaban --}}
    <div class="space-y-5">

        <div>
            <h2 class="text-lg font-bold text-slate-800">
                Hasil Jawaban
            </h2>
            <p class="mt-1 text-sm text-slate-500">
                Perbandingan jawaban siswa dengan kunci jawaban guru.
            </p>
        </div>

        @forelse ($soals as $index => $soal)

        @php
        $pilihan = $soal->pilihan ?? [];

        $jawabanSiswa = $jawabans->get($soal->id);

        $dipilih = strtolower(trim((string) ($jawabanSiswa?->jawaban_dipilih ?? '')));

        $kunci = $soal->kunci_jawaban ?? '';

        if (is_array($kunci)) {
        $kunci = $kunci['jawaban'] ?? $kunci[0] ?? '';
        }

        $kunci = strtolower(trim((string) $kunci));

        $sudahMenjawab = $dipilih !== '';

        $benar = $sudahMenjawab && $jawabanSiswa->benar;

        $daftarPilihan = [
        'a' => 'A',
        'b' => 'B',
        'c' => 'C',
        'd' => 'D',
        'e' => 'E',
        ];
        @endphp

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            {{-- Pertanyaan --}}
            <div class="border-b border-slate-100 p-5">
                <div class="mb-3 flex flex-wrap items-center justify-between gap-3">

                    <span class="rounded-lg bg-blue-100 px-3 py-1.5 text-sm font-bold text-blue-700">
                        Soal {{ $index + 1 }}
                    </span>

                    @if (!$sudahMenjawab)
                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                        Belum Dijawab
                    </span>
                    @elseif ($benar)
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                        Jawaban Benar
                    </span>
                    @else
                    <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                        Jawaban Salah
                    </span>
                    @endif

                </div>

                <p class="whitespace-pre-line text-base font-semibold leading-relaxed text-slate-800">
                    {{ $soal->pertanyaan }}
                </p>

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

                @foreach ($daftarPilihan as $key => $label)

                @php
                $teksPilihan = $pilihan[$key] ?? null;

                $isKunci = $kunci === $key;
                $isDipilih = $dipilih === $key;
                @endphp

                @if ($teksPilihan !== null && $teksPilihan !== '')

                <div class="flex items-start gap-3 rounded-xl border p-4
                                    @if ($isKunci)
                                        border-emerald-300 bg-emerald-50
                                    @elseif ($isDipilih)
                                        border-red-300 bg-red-50
                                    @else
                                        border-slate-200 bg-white
                                    @endif">

                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-sm font-bold
                                        @if ($isKunci)
                                            bg-emerald-600 text-white
                                        @elseif ($isDipilih)
                                            bg-red-600 text-white
                                        @else
                                            bg-slate-100 text-slate-600
                                        @endif">
                        {{ $label }}
                    </span>

                    <div class="min-w-0 flex-1 pt-1">
                        <p class="text-sm leading-relaxed
                                            @if ($isKunci)
                                                font-semibold text-emerald-800
                                            @elseif ($isDipilih)
                                                font-semibold text-red-800
                                            @else
                                                text-slate-700
                                            @endif">
                            {{ $teksPilihan }}
                        </p>
                    </div>

                    <div class="flex shrink-0 flex-col items-end gap-1">

                        @if ($isKunci)
                        <span class="rounded-full bg-emerald-600 px-3 py-1 text-xs font-semibold text-white">
                            Kunci Jawaban
                        </span>
                        @endif

                        @if ($isDipilih)
                        <span class="rounded-full bg-blue-600 px-3 py-1 text-xs font-semibold text-white">
                            Dipilih Siswa
                        </span>
                        @endif

                    </div>

                </div>

                @endif

                @endforeach

                @if (!$sudahMenjawab)
                <div class="rounded-lg bg-amber-50 p-3 text-sm text-amber-700">
                    Siswa belum memilih jawaban untuk soal ini.
                </div>
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
                Belum ada soal pada tugas ini.
            </p>
        </div>

        @endforelse

    </div>

    {{-- Tombol Kembali --}}
    <div class="flex justify-end pt-2">
        <a href="{{ route('guru.tugas.show', $tugas->id) }}"
            class="rounded-xl bg-slate-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
            Kembali ke Daftar Siswa
        </a>
    </div>

</div>
</div>
@endsection