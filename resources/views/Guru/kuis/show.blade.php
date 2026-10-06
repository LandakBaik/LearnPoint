@extends('layouts.app')

@section('title', 'Detail Kuis: ' . $quiz->judul)

@section('content')
<div class="space-y-6">

    {{-- Header with back button --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a
                href="{{ route('guru.kuis.index') }}"
                class="p-2.5 rounded-xl border border-gray-200 bg-white text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                    {{ $quiz->judul }}
                </h1>
                <p class="text-sm text-gray-500 mt-0.5">
                    {{ $quiz->pengampuKelas?->guruMapel?->mapel?->nama_mapel }} • Kelas {{ $quiz->pengampuKelas?->kelas?->tingkatan }} {{ $quiz->pengampuKelas?->kelas?->nama_kelas }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @php
                $diffBadge = match(strtolower($quiz->kesulitan)) {
                    'mudah' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'sedang' => 'bg-blue-50 text-blue-700 border-blue-200',
                    'sulit' => 'bg-rose-50 text-rose-700 border-rose-200',
                    default => 'bg-gray-50 text-gray-700 border-gray-200',
                };
            @endphp
            <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold border {{ $diffBadge }}">
                {{ $quiz->kesulitan }}
            </span>
            <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200">
                {{ $quiz->level }}
            </span>
        </div>
    </div>

    {{-- Overview Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Jumlah Soal</p>
                <h3 class="text-2xl font-extrabold text-gray-900 mt-1">{{ $quiz->soals->count() }}</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Siswa Menjawab</p>
                <h3 class="text-2xl font-extrabold text-blue-600 mt-1">{{ $quiz->nilais->count() }}</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Rata-rata Nilai</p>
                <h3 class="text-2xl font-extrabold text-emerald-600 mt-1">
                    {{ $quiz->nilais->count() > 0 ? number_format($quiz->nilais->avg('nilai'), 1) : '-' }}
                </h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Daftar Soal --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-gray-100 pb-4">
            <h3 class="text-lg font-bold text-gray-900">Daftar Pertanyaan / Soal Kuis</h3>
            <span class="text-xs font-semibold text-gray-500">{{ $quiz->soals->count() }} Pertanyaan</span>
        </div>

        @if($quiz->soals->count() > 0)
            <div class="space-y-4">
                @foreach($quiz->soals as $index => $soal)
                    <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 space-y-2">
                        <div class="flex items-start justify-between gap-3">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-purple-100 text-purple-700 font-bold text-xs shrink-0">
                                {{ $index + 1 }}
                            </span>
                            <div class="flex-1 text-sm font-medium text-gray-800">
                                {!! nl2br(e($soal->pertanyaan)) !!}
                            </div>
                            <span class="text-xs font-bold text-gray-500 bg-white px-2.5 py-1 rounded-md border border-gray-200 shrink-0">
                                Bobot: {{ $soal->bobot }}
                            </span>
                        </div>

                        @if(!empty($soal->pilihan) && is_array($soal->pilihan))
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-3 pt-2 border-t border-gray-200/60">
                                @foreach($soal->pilihan as $key => $opt)
                                    @php
                                        $isKey = is_array($soal->kunci_jawaban) 
                                            ? in_array($key, $soal->kunci_jawaban) 
                                            : ($soal->kunci_jawaban == $key);
                                    @endphp
                                    <div class="px-3 py-2 rounded-lg text-xs {{ $isKey ? 'bg-emerald-100/80 border border-emerald-300 font-bold text-emerald-800' : 'bg-white border border-gray-200 text-gray-700' }}">
                                        <span class="font-bold mr-1">{{ strtoupper($key) }}.</span> {{ $opt }}
                                        @if($isKey)
                                            <span class="ml-1 text-emerald-600 font-bold">✓ (Kunci)</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="py-8 text-center text-gray-400">
                <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-sm font-medium text-gray-500">Belum ada pertanyaan pada kuis ini.</p>
            </div>
        @endif
    </div>

</div>
@endsection
