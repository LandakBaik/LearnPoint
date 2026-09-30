@extends('layouts.app')

@section('title', 'Detail Ujian')

@section('content')

<div class="max-w-7xl mx-auto space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

        <div>
            <div class="flex items-center gap-3 mb-2">
                <a
                    href="{{ route('guru.ujian.index') }}"
                    class="text-gray-500 hover:text-teal-600"
                >
                    ← Kembali
                </a>
            </div>

            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">
                {{ $ujian->judul }}
            </h1>

            <p class="text-gray-500 mt-1">
                Detail dan pengaturan ujian
            </p>
        </div>

        <div class="flex flex-wrap gap-2">

            <a
                href="{{ route('guru.ujian.edit', $ujian) }}"
                class="px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white
                       rounded-xl font-semibold transition"
            >
                ✏️ Edit Ujian
            </a>

            @if($ujian->status === 'draft')

                <form
                    action="{{ route('guru.ujian.publish', $ujian) }}"
                    method="POST"
                >
                    @csrf

                    <button
                        type="submit"
                        data-confirm="Publikasikan ujian ini sekarang?"
                        class="px-4 py-2.5 bg-green-600 hover:bg-green-700
                               text-white rounded-xl font-semibold transition"
                    >
                        📢 Publikasi
                    </button>
                </form>

            @endif

        </div>
    </div>


    {{-- INFORMASI UTAMA --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- STATUS --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">

            <p class="text-sm text-gray-500 mb-2">
                Status
            </p>

            <span
                class="inline-flex px-3 py-1 rounded-full text-sm font-semibold
                @if($ujian->status === 'draft')
                    bg-gray-100 text-gray-700
                @elseif($ujian->status === 'scheduled')
                    bg-yellow-100 text-yellow-700
                @elseif($ujian->status === 'published')
                    bg-green-100 text-green-700
                @else
                    bg-gray-200 text-gray-800
                @endif"
            >
                {{ $ujian->status_label }}
            </span>

        </div>


        {{-- MATA PELAJARAN --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">

            <p class="text-sm text-gray-500 mb-2">
                Mata Pelajaran
            </p>

            <p class="font-bold text-gray-800">
                {{ $ujian->mapel?->nama_mapel ?? '-' }}
            </p>

        </div>


        {{-- KELAS --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">

            <p class="text-sm text-gray-500 mb-2">
                Kelas
            </p>

            <p class="font-bold text-gray-800">
                {{ $ujian->kelas?->nama_kelas ?? '-' }}
            </p>

        </div>


        {{-- JUMLAH SOAL --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">

            <p class="text-sm text-gray-500 mb-2">
                Jumlah Soal
            </p>

            <p class="text-2xl font-bold text-gray-800">
                {{ $ujian->total_soal }}
            </p>

        </div>

    </div>


    {{-- DETAIL PENGATURAN --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- INFORMASI UJIAN --}}
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100">

            <div class="p-6 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-800">
                    Informasi Ujian
                </h2>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">

                <div>
                    <p class="text-sm text-gray-500">
                        Durasi
                    </p>

                    <p class="font-semibold text-gray-800 mt-1">
                        {{ $ujian->durasi_menit }} menit
                    </p>
                </div>


                <div>
                    <p class="text-sm text-gray-500">
                        KKM
                    </p>

                    <p class="font-semibold text-gray-800 mt-1">
                        {{ $ujian->kkm }}
                    </p>
                </div>


                <div>
                    <p class="text-sm text-gray-500">
                        Waktu Mulai
                    </p>

                    <p class="font-semibold text-gray-800 mt-1">
                        {{ $ujian->waktu_mulai?->format('d M Y, H:i') ?? '-' }}
                    </p>
                </div>


                <div>
                    <p class="text-sm text-gray-500">
                        Deadline
                    </p>

                    <p class="font-semibold text-gray-800 mt-1">
                        {{ $ujian->deadline?->format('d M Y, H:i') ?? '-' }}
                    </p>
                </div>


                <div>
                    <p class="text-sm text-gray-500">
                        Dibuat
                    </p>

                    <p class="font-semibold text-gray-800 mt-1">
                        {{ $ujian->created_at?->format('d M Y, H:i') ?? '-' }}
                    </p>
                </div>


                <div>
                    <p class="text-sm text-gray-500">
                        Dipublikasi
                    </p>

                    <p class="font-semibold text-gray-800 mt-1">
                        {{ $ujian->dipublikasi_at?->format('d M Y, H:i') ?? '-' }}
                    </p>
                </div>

            </div>

        </div>


        {{-- RINGKASAN SOAL --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">

            <div class="p-6 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-800">
                    Ringkasan Soal
                </h2>
            </div>

            <div class="p-6 space-y-4">

                @php
                    $types = [
                        'single_choice' => 'Pilihan Tunggal',
                        'multiple_choice' => 'Pilihan Ganda',
                        'essay' => 'Essay',
                        'matching' => 'Menjodohkan',
                    ];
                @endphp

                @foreach($types as $type => $label)

                    <div class="flex items-center justify-between">

                        <span class="text-sm text-gray-600">
                            {{ $label }}
                        </span>

                        <span class="font-bold text-gray-800">
                            {{ $ujian->question_type_counts[$type] ?? 0 }}
                        </span>

                    </div>

                @endforeach


                <div class="border-t border-gray-100 pt-4 flex justify-between">

                    <span class="font-semibold text-gray-700">
                        Total Poin
                    </span>

                    <span class="font-bold text-teal-600">
                        {{ $ujian->total_poin }}
                    </span>

                </div>

            </div>

        </div>

    </div>


    {{-- KISI-KISI --}}
    @if($ujian->kisi_kisi)

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">

            <div class="p-6 border-b border-gray-100">

                <h2 class="text-lg font-bold text-gray-800">
                    Kisi-kisi Ujian
                </h2>

            </div>

            <div class="p-6">

                <p class="text-gray-700 whitespace-pre-line leading-relaxed">
                    {{ $ujian->kisi_kisi }}
                </p>

            </div>

        </div>

    @endif


    {{-- DAFTAR SOAL --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100">

        <div class="p-6 border-b border-gray-100">

            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">

                <div>
                    <h2 class="text-lg font-bold text-gray-800">
                        Daftar Soal
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Periksa soal sebelum ujian dipublikasikan.
                    </p>
                </div>

                <span class="text-sm font-semibold text-gray-500">
                    {{ $ujian->total_soal }} soal
                </span>

            </div>

        </div>


        <div class="p-6 space-y-5">

            @forelse($ujian->soals as $index => $soal)

                <div class="border border-gray-200 rounded-2xl p-5">

                    {{-- HEADER SOAL --}}
                    <div class="flex flex-col md:flex-row md:items-start
                                md:justify-between gap-3 mb-4">

                        <div class="flex gap-3">

                            <div
                                class="w-9 h-9 rounded-xl bg-teal-100 text-teal-700
                                       flex items-center justify-center font-bold
                                       flex-shrink-0"
                            >
                                {{ $index + 1 }}
                            </div>

                            <div>

                                <span
                                    class="inline-flex px-2.5 py-1 rounded-lg
                                           bg-gray-100 text-gray-600 text-xs
                                           font-semibold mb-2"
                                >
                                    @switch($soal->tipe_soal)

                                        @case('single_choice')
                                            Pilihan Tunggal
                                            @break

                                        @case('multiple_choice')
                                            Pilihan Ganda
                                            @break

                                        @case('essay')
                                            Essay
                                            @break

                                        @case('matching')
                                            Menjodohkan
                                            @break

                                        @default
                                            {{ $soal->tipe_soal }}

                                    @endswitch
                                </span>

                                <p class="text-sm text-gray-500">
                                    Bobot: {{ $soal->bobot }}
                                </p>

                            </div>

                        </div>

                    </div>


                    {{-- PERTANYAAN --}}
                    <div class="text-gray-800 font-medium leading-relaxed mb-4">
                        {!! nl2br(e($soal->pertanyaan)) !!}
                    </div>


                    {{-- GAMBAR --}}
                    @if($soal->gambar)

                        <div class="mb-4">

                            <img
                                src="{{ asset('storage/' . $soal->gambar) }}"
                                alt="Gambar soal"
                                class="max-w-md max-h-80 rounded-xl border border-gray-200 object-contain"
                            >

                        </div>

                    @endif


                    {{-- PILIHAN --}}
                    @if(is_array($soal->pilihan) && count($soal->pilihan))

                        <div class="space-y-2">

                            @foreach($soal->pilihan as $key => $pilihan)

                                <div
                                    class="flex items-start gap-3 p-3 rounded-xl
                                    @if(is_array($soal->kunci_jawaban)
                                        && in_array($key, $soal->kunci_jawaban))
                                        bg-green-50 border border-green-200
                                    @else
                                        bg-gray-50
                                    @endif"
                                >

                                    <span class="font-bold text-gray-600">
                                        {{ is_string($key) ? $key : $key + 1 }}.
                                    </span>

                                    <span class="text-gray-700">
                                        {{ $pilihan }}
                                    </span>

                                </div>

                            @endforeach

                        </div>

                    @elseif($soal->tipe_soal === 'essay')

                        <div class="p-4 bg-gray-50 rounded-xl text-sm text-gray-500">
                            Jawaban berupa teks/essay.
                        </div>

                    @elseif($soal->tipe_soal === 'matching')

                        <div class="p-4 bg-gray-50 rounded-xl text-sm text-gray-500">
                            Soal tipe menjodohkan.
                        </div>

                    @endif

                </div>

            @empty

                <div class="text-center py-12">

                    <div class="text-4xl mb-3">
                        📝
                    </div>

                    <h3 class="font-bold text-gray-700">
                        Belum ada soal
                    </h3>

                    <p class="text-sm text-gray-500 mt-1">
                        Tambahkan soal terlebih dahulu.
                    </p>

                </div>

            @endforelse

        </div>

    </div>


    {{-- PERINGATAN --}}
    <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-5">

        <div class="flex gap-3">

            <div class="text-xl">
                ⚠️
            </div>

            <div>

                <h3 class="font-bold text-yellow-800">
                    Periksa kembali ujian
                </h3>

                <p class="text-sm text-yellow-700 mt-1 leading-relaxed">
                    Pastikan soal, jawaban, durasi, kelas, dan jadwal sudah benar
                    sebelum ujian dipublikasikan kepada siswa.
                </p>

            </div>

        </div>

    </div>

</div>

@endsection
