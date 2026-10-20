@extends('layouts.app')

@section('title', 'Ujian & Evaluasi')

@section('content')

<div class="space-y-6">

    {{-- =========================================================
        HEADER
    ========================================================== --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

        <div>
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">
                Ujian & Evaluasi
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Kelola ujian, soal, jadwal, dan evaluasi siswa.
            </p>
        </div>

        <a
            href="{{ route('guru.ujian.create') }}"
            class="inline-flex items-center justify-center gap-2 px-5 py-3
                   rounded-xl bg-indigo-600 text-white text-sm font-semibold
                   hover:bg-indigo-700 transition shadow-sm"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 4v16m8-8H4"/>
            </svg>

            Tambah Ujian
        </a>

    </div>


    {{-- =========================================================
        STATISTIK
    ========================================================== --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Total --}}
        <div class="bg-white rounded-2xl border border-gray-200
                    p-5 shadow-sm">

            <div class="flex items-center justify-between">

                <div>
                    <p class="text-xs font-semibold uppercase
                              tracking-wide text-gray-500">
                        Total Ujian
                    </p>

                    <p class="mt-2 text-3xl font-bold text-gray-900">
                        {{ $ujians->total() }}
                    </p>
                </div>

                <div class="w-11 h-11 rounded-xl bg-indigo-50
                            text-indigo-600 flex items-center justify-center">

                    <svg class="w-6 h-6" fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="1.8"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a3 3 0 006 0M9 5h6"/>
                    </svg>

                </div>

            </div>
        </div>


        {{-- Draft --}}
        <div class="bg-white rounded-2xl border border-gray-200
                    p-5 shadow-sm">

            <div class="flex items-center justify-between">

                <div>
                    <p class="text-xs font-semibold uppercase
                              tracking-wide text-gray-500">
                        Draft
                    </p>

                    <p class="mt-2 text-3xl font-bold text-gray-900">
                        {{ $ujians->getCollection()->where('status', 'draft')->count() }}
                    </p>
                </div>

                <div class="w-11 h-11 rounded-xl bg-gray-100
                            text-gray-600 flex items-center justify-center">

                    <svg class="w-6 h-6" fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="1.8"
                              d="M12 6v6l4 2"/>
                    </svg>

                </div>

            </div>
        </div>


        {{-- Terjadwal --}}
        <div class="bg-white rounded-2xl border border-gray-200
                    p-5 shadow-sm">

            <div class="flex items-center justify-between">

                <div>
                    <p class="text-xs font-semibold uppercase
                              tracking-wide text-gray-500">
                        Terjadwal
                    </p>

                    <p class="mt-2 text-3xl font-bold text-gray-900">
                        {{ $ujians->getCollection()->where('status', 'scheduled')->count() }}
                    </p>
                </div>

                <div class="w-11 h-11 rounded-xl bg-amber-50
                            text-amber-600 flex items-center justify-center">

                    <svg class="w-6 h-6" fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="1.8"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>

                </div>

            </div>
        </div>


        {{-- Dipublikasi --}}
        <div class="bg-white rounded-2xl border border-gray-200
                    p-5 shadow-sm">

            <div class="flex items-center justify-between">

                <div>
                    <p class="text-xs font-semibold uppercase
                              tracking-wide text-gray-500">
                        Dipublikasi
                    </p>

                    <p class="mt-2 text-3xl font-bold text-gray-900">
                        {{ $ujians->getCollection()->where('status', 'published')->count() }}
                    </p>
                </div>

                <div class="w-11 h-11 rounded-xl bg-emerald-50
                            text-emerald-600 flex items-center justify-center">

                    <svg class="w-6 h-6" fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="1.8"
                              d="M5 13l4 4L19 7"/>
                    </svg>

                </div>

            </div>
        </div>

    </div>


    {{-- =========================================================
        FILTER
    ========================================================== --}}
    <div class="bg-white rounded-2xl border border-gray-200
                p-5 shadow-sm">

        <form method="GET"
              action="{{ route('guru.ujian.index') }}"
              class="grid grid-cols-1 md:grid-cols-[1fr_200px_auto] gap-3">

            {{-- Search --}}
            <div class="relative">

                <svg class="absolute left-3 top-1/2 -translate-y-1/2
                            w-5 h-5 text-gray-400"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="m21 21-4.35-4.35m2.35-5.65a8 8 0 11-16 0 8 8 0 0116 0z"/>
                </svg>

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari judul ujian..."
                    class="w-full pl-10 pr-4 py-3 rounded-xl
                           border border-gray-200 bg-gray-50
                           text-sm focus:bg-white focus:border-indigo-400
                           focus:ring-2 focus:ring-indigo-100
                           outline-none transition"
                >

            </div>


            {{-- Status --}}
            <select
                name="status"
                class="w-full px-4 py-3 rounded-xl
                       border border-gray-200 bg-gray-50
                       text-sm text-gray-700
                       focus:bg-white focus:border-indigo-400
                       focus:ring-2 focus:ring-indigo-100
                       outline-none"
            >

                <option value="">Semua Status</option>

                <option value="draft"
                    {{ request('status') === 'draft' ? 'selected' : '' }}>
                    Draft
                </option>

                <option value="scheduled"
                    {{ request('status') === 'scheduled' ? 'selected' : '' }}>
                    Terjadwal
                </option>

                <option value="published"
                    {{ request('status') === 'published' ? 'selected' : '' }}>
                    Dipublikasi
                </option>

                <option value="finished"
                    {{ request('status') === 'finished' ? 'selected' : '' }}>
                    Selesai
                </option>

            </select>


            {{-- Button --}}
            <button
                type="submit"
                class="px-5 py-3 rounded-xl bg-gray-900
                       text-white text-sm font-semibold
                       hover:bg-gray-800 transition"
            >
                Filter
            </button>

        </form>

    </div>


    {{-- =========================================================
        DAFTAR UJIAN
    ========================================================== --}}
    <div class="bg-white rounded-2xl border border-gray-200
                shadow-sm overflow-hidden">

        <div class="px-5 py-4 border-b border-gray-100">

            <h3 class="font-bold text-gray-900">
                Daftar Ujian
            </h3>

            <p class="text-xs text-gray-500 mt-1">
                Semua ujian yang dibuat oleh Anda.
            </p>

        </div>


        @forelse($ujians as $ujian)

            <div class="p-5 border-b border-gray-100 last:border-b-0
                        hover:bg-gray-50/70 transition">

                <div class="flex flex-col lg:flex-row
                            lg:items-center lg:justify-between gap-5">

                    {{-- Informasi utama --}}
                    <div class="min-w-0 flex-1">

                        <div class="flex flex-wrap items-center gap-2">

                            <h4 class="text-base sm:text-lg font-bold
                                       text-gray-900">
                                {{ $ujian->judul }}
                            </h4>

                            @php
                                $badgeClass = match($ujian->status) {
                                    'draft' => 'bg-gray-100 text-gray-700',
                                    'scheduled' => 'bg-amber-100 text-amber-700',
                                    'published' => 'bg-emerald-100 text-emerald-700',
                                    'finished' => 'bg-slate-100 text-slate-700',
                                    default => 'bg-gray-100 text-gray-700',
                                };
                            @endphp

                            <span class="px-2.5 py-1 rounded-full
                                         text-xs font-semibold {{ $badgeClass }}">
                                {{ $ujian->status_label }}
                            </span>

                        </div>


                        {{-- Mapel & Kelas --}}
                        <div class="mt-2 flex flex-wrap items-center
                                    gap-x-4 gap-y-1 text-xs text-gray-500">

                            <span>
                                {{ $ujian->pengampuKelas?->guruMapel?->mapel?->nama ?? 'Mata pelajaran belum tersedia' }}
                            </span>

                            <span class="hidden sm:inline">•</span>

                            <span>
                                Kelas:
                                {{ $ujian->pengampuKelas?->kelas?->nama ?? '-' }}
                            </span>

                        </div>


                        {{-- Detail --}}
                        <div class="mt-3 flex flex-wrap gap-4
                                    text-xs text-gray-500">

                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>

                                {{ $ujian->waktu_mulai
                                    ? $ujian->waktu_mulai->format('d M Y H:i')
                                    : 'Belum dijadwalkan' }}
                            </span>


                            <span class="inline-flex items-center gap-1.5">

                                <svg class="w-4 h-4" fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M12 8v4l3 2"/>
                                </svg>

                                {{ $ujian->durasi_menit }} menit

                            </span>


                            <span class="inline-flex items-center gap-1.5">

                                <svg class="w-4 h-4" fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z"/>
                                </svg>

                                {{ $ujian->soals->count() }} soal

                            </span>

                        </div>

                    </div>


                    {{-- Action --}}
                    <div class="flex flex-wrap items-center gap-2
                                lg:justify-end">

                        <a
                            href="{{ route('guru.ujian.show', $ujian) }}"
                            class="px-3.5 py-2 rounded-lg border
                                   border-gray-200 bg-white
                                   text-xs font-semibold text-gray-700
                                   hover:bg-gray-50 transition"
                        >
                            Lihat
                        </a>

                        <a
                            href="{{ route('guru.ujian.edit', $ujian) }}"
                            class="px-3.5 py-2 rounded-lg border
                                   border-indigo-200 bg-indigo-50
                                   text-xs font-semibold text-indigo-700
                                   hover:bg-indigo-100 transition"
                        >
                            Edit
                        </a>

                        @if($ujian->status === 'draft')

                            <form
                                method="POST"
                                action="{{ route('guru.ujian.publish', $ujian) }}"
                                data-confirm="Publikasikan ujian ini sekarang?"
                            >
                                @csrf

                                <button
                                    type="submit"
                                    class="px-3.5 py-2 rounded-lg
                                           bg-emerald-600 text-white
                                           text-xs font-semibold
                                           hover:bg-emerald-700 transition"
                                >
                                    Publikasi
                                </button>
                            </form>

                        @endif


                        <form
                            method="POST"
                            action="{{ route('guru.ujian.destroy', $ujian) }}"
                            data-confirm="Ujian ini akan dihapus beserta soal-soalnya. Apakah Anda yakin?"
                        >
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="px-3.5 py-2 rounded-lg
                                       border border-red-200
                                       bg-red-50 text-red-600
                                       text-xs font-semibold
                                       hover:bg-red-100 transition"
                            >
                                Hapus
                            </button>

                        </form>

                    </div>

                </div>

            </div>

        @empty

            {{-- Empty state --}}
            <div class="px-6 py-16 text-center">

                <div class="w-16 h-16 mx-auto rounded-2xl
                            bg-indigo-50 text-indigo-500
                            flex items-center justify-center">

                    <svg class="w-8 h-8" fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="1.5"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a3 3 0 006 0M9 5h6"/>
                    </svg>

                </div>

                <h3 class="mt-4 text-base font-bold text-gray-900">
                    Belum ada ujian
                </h3>

                <p class="mt-1 text-sm text-gray-500 max-w-md mx-auto">
                    Mulai buat ujian pertama Anda untuk memberikan
                    evaluasi kepada siswa.
                </p>

                <a
                    href="{{ route('guru.ujian.create') }}"
                    class="inline-flex items-center gap-2 mt-5
                           px-5 py-2.5 rounded-xl
                           bg-indigo-600 text-white
                           text-sm font-semibold
                           hover:bg-indigo-700 transition"
                >
                    + Tambah Ujian
                </a>

            </div>

        @endforelse


        {{-- Pagination --}}
        @if($ujians->hasPages())

            <div class="px-5 py-4 border-t border-gray-100">
                {{ $ujians->links() }}
            </div>

        @endif

    </div>

</div>

@endsection
