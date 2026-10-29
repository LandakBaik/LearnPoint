@extends('layouts.app')

@section('title', 'Ujian & Evaluasi')

@section('content')

<div class="space-y-6">

    {{-- =========================================================
        HEADER
    ========================================================== --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                Ujian &amp; Evaluasi
            </h1>
            <p class="mt-1 text-sm text-gray-500">
                Kelola bank soal, jadwal ujian, dan evaluasi hasil belajar siswa secara terpusat.
            </p>
        </div>

        <div>
            <a
                href="{{ route('guru.ujian.create') }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 text-white font-semibold text-sm hover:bg-blue-700 transition shadow-sm"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Tambah Ujian</span>
            </a>
        </div>
    </div>


    {{-- =========================================================
        STATISTIK KARTU (Consistent with Dashboard & Tugas)
    ========================================================== --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Total Ujian --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm hover:shadow-md transition-shadow flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500">
                    Total Ujian
                </p>
                <h3 class="mt-1.5 text-2xl font-extrabold text-gray-900">
                    {{ $ujians->total() }}
                </h3>
                <p class="text-[11px] text-gray-400 mt-0.5">Semua evaluasi terdaftar</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100/60 shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a3 3 0 006 0M9 5h6"/>
                </svg>
            </div>
        </div>

        {{-- Draft --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm hover:shadow-md transition-shadow flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                    Draf
                </p>
                <h3 class="mt-1.5 text-2xl font-extrabold text-slate-800">
                    {{ $ujians->getCollection()->where('status', 'draft')->count() }}
                </h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Belum dipublikasikan</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-600 flex items-center justify-center border border-slate-200/60 shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v6l4 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        {{-- Terjadwal --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm hover:shadow-md transition-shadow flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-amber-700">
                    Terjadwal
                </p>
                <h3 class="mt-1.5 text-2xl font-extrabold text-slate-900">
                    {{ $ujians->getCollection()->where('status', 'scheduled')->count() }}
                </h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Menunggu waktu tayang</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-200/60 shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>

        {{-- Dipublikasi --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm hover:shadow-md transition-shadow flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">
                    Dipublikasi
                </p>
                <h3 class="mt-1.5 text-2xl font-extrabold text-slate-900">
                    {{ $ujians->getCollection()->where('status', 'published')->count() }}
                </h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Aktif dikerjakan siswa</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-200/60 shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

    </div>


    {{-- =========================================================
        FILTER & PENCARIAN
    ========================================================== --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-4 sm:p-5 shadow-sm">
        <form method="GET" action="{{ route('guru.ujian.index') }}" class="flex flex-col md:flex-row gap-3 items-center justify-between">

            {{-- Search Input --}}
            <div class="relative w-full md:flex-1">
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m2.35-5.65a8 8 0 11-16 0 8 8 0 0116 0z"/>
                </svg>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari judul ujian atau materi evaluasi..."
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50/60 text-xs font-medium text-gray-800 placeholder-gray-400 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition"
                >
            </div>

            <div class="flex items-center gap-2.5 w-full md:w-auto">
                {{-- Status Filter --}}
                <select
                    name="status"
                    class="w-full md:w-48 px-3.5 py-2.5 rounded-xl border border-gray-200 bg-gray-50/60 text-xs font-medium text-gray-700 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition cursor-pointer"
                >
                    <option value="">Semua Status</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draf</option>
                    <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Terjadwal</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Dipublikasi</option>
                    <option value="finished" {{ request('status') === 'finished' ? 'selected' : '' }}>Selesai</option>
                </select>

                {{-- Submit Button --}}
                <button
                    type="submit"
                    class="px-5 py-2.5 rounded-xl bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700 transition shadow-xs flex items-center justify-center gap-1.5 shrink-0"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    <span>Filter</span>
                </button>

                @if(request()->hasAny(['search', 'status']))
                    <a
                        href="{{ route('guru.ujian.index') }}"
                        class="p-2.5 border border-gray-200 rounded-xl text-xs font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-50 transition shrink-0 flex items-center justify-center"
                        title="Reset Filter"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                @endif
            </div>

        </form>
    </div>


    {{-- =========================================================
        DAFTAR UJIAN (Cards Grid Layout)
    ========================================================== --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">

        <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
            <div>
                <h3 class="text-base font-bold text-gray-900">
                    Daftar Ujian
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">
                    Semua instrumen ujian dan asesmen yang dikelola oleh Anda.
                </p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">
                {{ $ujians->total() }} Ujian Terdata
            </span>
        </div>

        @if($ujians->count() > 0)
            <div class="divide-y divide-gray-100">
                @foreach($ujians as $ujian)
                    @php
                        $badgeStyle = match($ujian->status) {
                            'draft' => 'bg-slate-100 text-slate-700 border-slate-200',
                            'scheduled' => 'bg-amber-50 text-amber-700 border-amber-200',
                            'published' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'finished' => 'bg-blue-50 text-blue-700 border-blue-200',
                            default => 'bg-gray-100 text-gray-700 border-gray-200',
                        };

                        $mapelName = $ujian->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? $ujian->mapel?->nama_mapel ?? 'Mata Pelajaran';
                        $kelasName = $ujian->pengampuKelas?->kelas?->nama_kelas ?? $ujian->kelas?->nama_kelas ?? 'Kelas';
                    @endphp

                    <div class="p-5 sm:p-6 hover:bg-slate-50/60 transition-colors flex flex-col lg:flex-row lg:items-center justify-between gap-5 group">
                        
                        {{-- Left Info Block --}}
                        <div class="min-w-0 flex-1 space-y-2.5">
                            
                            {{-- Top Tags: Mapel, Kelas & Status --}}
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-lg bg-blue-50 text-blue-700 text-xs font-bold border border-blue-100/60">
                                    {{ $mapelName }}
                                </span>
                                <span class="px-2.5 py-0.5 rounded-lg bg-slate-100 text-slate-700 text-xs font-bold border border-slate-200/60">
                                    {{ $kelasName }}
                                </span>
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $badgeStyle }}">
                                    @if($ujian->status === 'published')
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    @endif
                                    {{ $ujian->status_label }}
                                </span>
                            </div>

                            {{-- Judul Ujian --}}
                            <div>
                                <h4 class="text-base sm:text-lg font-bold text-gray-900 group-hover:text-blue-600 transition-colors">
                                    <a href="{{ route('guru.ujian.show', $ujian) }}">
                                        {{ $ujian->judul }}
                                    </a>
                                </h4>
                                @if($ujian->deskripsi)
                                    <p class="text-xs text-gray-500 mt-1 line-clamp-1">
                                        {{ $ujian->deskripsi }}
                                    </p>
                                @endif
                            </div>

                            {{-- Metadata Badges: Waktu, Durasi, Soal, KKM --}}
                            <div class="flex flex-wrap items-center gap-y-1.5 gap-x-4 text-xs text-gray-500 pt-0.5">
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span>{{ $ujian->waktu_mulai ? $ujian->waktu_mulai->format('d M Y, H:i') : 'Belum dijadwalkan' }}</span>
                                </span>

                                <span class="text-gray-300">•</span>

                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>{{ $ujian->durasi_menit }} menit</span>
                                </span>

                                <span class="text-gray-300">•</span>

                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span class="font-semibold text-gray-700">{{ $ujian->soals->count() }} butir soal</span>
                                </span>

                                @if($ujian->kkm)
                                    <span class="text-gray-300">•</span>
                                    <span class="inline-flex items-center gap-1 font-semibold text-gray-600 bg-gray-100 px-2 py-0.5 rounded text-[11px]">
                                        KKM: {{ $ujian->kkm }}
                                    </span>
                                @endif
                            </div>

                        </div>

                        {{-- Right Action Buttons --}}
                        <div class="flex flex-wrap items-center gap-2 lg:justify-end shrink-0 pt-2 lg:pt-0">
                            {{-- Detail Button --}}
                            <a
                                href="{{ route('guru.ujian.show', $ujian) }}"
                                class="px-3.5 py-2 rounded-xl border border-gray-200 bg-white text-xs font-semibold text-gray-700 hover:bg-gray-50 hover:border-gray-300 transition shadow-2xs inline-flex items-center gap-1.5"
                            >
                                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>Detail</span>
                            </a>

                            {{-- Edit Button --}}
                            <a
                                href="{{ route('guru.ujian.edit', $ujian) }}"
                                class="px-3.5 py-2 rounded-xl border border-blue-200 bg-blue-50 text-xs font-semibold text-blue-700 hover:bg-blue-100 transition shadow-2xs inline-flex items-center gap-1.5"
                            >
                                <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <span>Edit</span>
                            </a>

                            {{-- Publish Button (if draft) --}}
                            @if($ujian->status === 'draft')
                                <form
                                    method="POST"
                                    action="{{ route('guru.ujian.publish', $ujian) }}"
                                    data-confirm="Publikasikan ujian ini sekarang? Siswa di kelas terkait akan dapat mengakses ujian sesuai jadwal."
                                >
                                    @csrf
                                    <button
                                        type="submit"
                                        class="px-3.5 py-2 rounded-xl bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 transition shadow-2xs inline-flex items-center gap-1.5"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span>Publikasi</span>
                                    </button>
                                </form>
                            @endif

                            {{-- Delete Button --}}
                            <form
                                method="POST"
                                action="{{ route('guru.ujian.destroy', $ujian) }}"
                                data-confirm="Ujian ini akan dihapus beserta butir soal dan data pengumpulannya. Lanjutkan?"
                            >
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="px-3.5 py-2 rounded-xl border border-rose-200 bg-rose-50 text-rose-600 text-xs font-semibold hover:bg-rose-100 transition shadow-2xs inline-flex items-center gap-1.5"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    <span>Hapus</span>
                                </button>
                            </form>
                        </div>

                    </div>
                @endforeach
            </div>
        @else
            {{-- Empty State --}}
            <div class="px-6 py-16 text-center">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a3 3 0 006 0M9 5h6"/>
                    </svg>
                </div>

                <h3 class="mt-4 text-base font-bold text-gray-900">
                    Belum Ada Ujian Terdaftar
                </h3>

                <p class="mt-1 text-xs text-gray-500 max-w-sm mx-auto">
                    Mulai buat paket ujian pertama Anda untuk memberikan evaluasi terstruktur kepada peserta didik.
                </p>

                <div class="mt-5">
                    <a
                        href="{{ route('guru.ujian.create') }}"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700 transition shadow-sm"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span>Buat Ujian Baru</span>
                    </a>
                </div>
            </div>
        @endif

        {{-- Pagination --}}
        @if($ujians->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $ujians->links() }}
            </div>
        @endif

    </div>

</div>

@endsection
