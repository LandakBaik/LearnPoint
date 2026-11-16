@extends('layouts.app')

@section('title', 'Detail Jawaban Siswa')

@section('content')
@php
    $namaSiswa = $siswa?->nama_siswa ?? 'Nama tidak tersedia';
    $inisialSiswa = collect(explode(' ', trim($namaSiswa)))
        ->filter()
        ->take(2)
        ->map(fn($part) => strtoupper(mb_substr($part, 0, 1)))
        ->implode('') ?: 'S';

    $deadline = $tugas->deadline ? \Carbon\Carbon::parse($tugas->deadline) : null;
    $waktuKumpul = $pengumpulan?->created_at ? \Carbon\Carbon::parse($pengumpulan->created_at) : null;
    $terlambat = $waktuKumpul && $deadline ? $waktuKumpul->greaterThan($deadline) : false;

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

<div class="space-y-5 text-slate-800">

    {{-- Breadcrumb dan Status Tugas --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <nav class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
                <a href="{{ route('guru.tugas') }}"
                    class="inline-flex items-center gap-2 transition hover:text-blue-700">
                    <i class="fas fa-tasks text-base"></i>
                    Kelola Tugas
                </a>

                <i class="fas fa-chevron-right text-xs text-slate-400"></i>

                <a href="{{ route('guru.tugas.show', $tugas->id) }}"
                    class="transition hover:text-blue-700">
                    Detail Pengumpulan
                </a>

                <i class="fas fa-chevron-right text-xs text-slate-400"></i>

                <span class="font-semibold text-blue-800">
                    Detail Jawaban Siswa
                </span>
            </nav>

            <h1 class="mt-3 text-2xl font-bold text-slate-800">
                {{ $tugas->judul }}
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Hasil pengerjaan soal pilihan ganda siswa.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <span class="w-fit rounded-full bg-blue-100 px-3 py-1.5 text-sm font-semibold text-blue-700">
                Pilihan Ganda
            </span>

            <a href="{{ route('guru.tugas.kunci-jawaban', $tugas->id) }}"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-blue-700">
                <i class="fas fa-key"></i>
                Lihat Kunci Jawaban
            </a>
        </div>
    </div>

    {{-- Informasi Siswa & Nilai --}}
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3.5">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-100 text-sm font-bold text-blue-800">
                    {{ $inisialSiswa }}
                </div>

                <div>
                    <h2 class="text-lg font-bold text-slate-900">
                        {{ $namaSiswa }}
                    </h2>

                    <p class="text-xs text-slate-500">
                        NIS: {{ $siswa?->nis ?? '-' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 rounded-xl border border-indigo-100 bg-indigo-50 px-5 py-3">
                <div>
                    <p class="text-xs font-semibold text-indigo-600">Nilai Akhir</p>
                    <p class="text-2xl font-extrabold text-indigo-800">
                        {{ number_format((float) ($pengumpulan->nilai ?? 0), 2, ',', '.') }}
                        <span class="text-xs font-semibold text-indigo-400">/ 100</span>
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 border-t border-slate-100 pt-4 sm:grid-cols-3">
            <div>
                <p class="text-xs text-slate-500">Mata Pelajaran</p>
                <p class="mt-1 font-semibold text-slate-800">
                    {{ $tugas->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? '-' }}
                </p>
            </div>

            <div>
                <p class="text-xs text-slate-500">Kelas</p>
                <p class="mt-1 font-semibold text-slate-800">
                    {{ $tugas->pengampuKelas?->kelas?->nama_kelas ?? '-' }}
                </p>
            </div>

            <div>
                <p class="text-xs text-slate-500">Waktu Pengumpulan</p>
                <div class="mt-1 flex flex-wrap items-center gap-2">
                    <span class="font-semibold text-slate-800">
                        {{ $waktuKumpul ? $waktuKumpul->locale('id')->translatedFormat('d M Y, H:i') : '-' }}
                    </span>

                    @if ($waktuKumpul)
                        <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold {{ $terlambat ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-700' }}">
                            {{ $terlambat ? 'Terlambat' : 'Tepat Waktu' }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Ringkasan Jawaban --}}
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-sm text-slate-500">Total Soal</p>
            <p class="mt-2 text-2xl font-bold text-slate-800">
                {{ $totalSoal }}
            </p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-sm text-slate-500">Terjawab</p>
            <p class="mt-2 text-2xl font-bold text-blue-600">
                {{ $jumlahTerjawab }}
            </p>
            @if ($belumDijawab > 0)
                <p class="mt-1 text-xs text-amber-600">{{ $belumDijawab }} belum dijawab</p>
            @endif
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-sm text-slate-500">Jawaban Benar</p>
            <p class="mt-2 text-2xl font-bold text-emerald-600">
                {{ $jumlahBenar }}
            </p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-sm text-slate-500">Jawaban Salah</p>
            <p class="mt-2 text-2xl font-bold text-red-600">
                {{ $jumlahSalah }}
            </p>
        </div>
    </div>

    {{-- Daftar Jawaban --}}
    <div class="space-y-4">
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
                if (is_string($pilihan)) {
                    $pilihan = json_decode($pilihan, true) ?? [];
                }

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

            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                {{-- Pertanyaan --}}
                <div class="border-b border-slate-100 p-5">
                    <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <span class="rounded-lg bg-blue-100 px-3 py-1.5 text-sm font-bold text-blue-700">
                                Soal {{ $index + 1 }}
                            </span>

                            <span class="text-xs font-medium text-slate-500">
                                Bobot: {{ number_format((float) ($soal->bobot ?? 0), 2, ',', '.') }} poin
                            </span>
                        </div>

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

                                <div class="flex shrink-0 flex-wrap items-center justify-end gap-1.5">
                                    @if ($isKunci)
                                        <span class="rounded-full bg-emerald-600 px-3 py-1 text-xs font-semibold text-white">
                                            Kunci Jawaban
                                        </span>
                                    @endif

                                    @if ($isDipilih)
                                        <span class="rounded-full {{ $isKunci ? 'bg-blue-600' : 'bg-red-600' }} px-3 py-1 text-xs font-semibold text-white">
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
            <div class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center">
                <p class="font-semibold text-slate-700">
                    Belum Ada Soal
                </p>
                <p class="mt-1 text-sm text-slate-500">
                    Belum ada soal pada tugas ini.
                </p>
            </div>
        @endforelse
    </div>

    {{-- Tombol Kembali --}}
    <div class="flex justify-end pt-2">
        <a href="{{ route('guru.tugas.show', $tugas->id) }}"
            class="inline-flex items-center gap-2 rounded-xl bg-slate-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
            <i class="fas fa-arrow-left text-xs"></i>
            Kembali ke Detail Pengumpulan
        </a>
    </div>

</div>
@endsection
