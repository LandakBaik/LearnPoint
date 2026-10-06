@extends('layouts.app')

@section('title', 'Kuis Pembelajaran')

@section('content')
<div class="space-y-6" x-data="{
    searchQuery: '{{ request('search', '') }}',
    showModal: false,
    showEditModal: false,
    editData: {
        id: '',
        judul: '',
        level: '',
        kesulitan: '',
        pengampu_kelas_id: ''
    },

    openEditModal(quiz) {
        this.editData = {
            id: quiz.id,
            judul: quiz.judul,
            level: quiz.level,
            kesulitan: quiz.kesulitan,
            pengampu_kelas_id: quiz.pengampu_kelas_id
        };
        this.showEditModal = true;
    }
}">

    {{-- =========================================================
        HEADER
    ========================================================== --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                Kuis Pembelajaran
            </h1>
            <p class="mt-1 text-sm text-gray-500">
                Kelola kuis interaktif, level tantangan, dan evaluasi capaian materi siswa.
            </p>
        </div>

        <button
            @click="showModal = true"
            type="button"
            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-purple-600 text-white text-sm font-semibold hover:bg-purple-700 transition shadow-sm cursor-pointer"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            Buat Kuis Baru
        </button>
    </div>

    {{-- Alert Success / Error --}}
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center gap-3">
            <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- =========================================================
        STATISTIK CARDS
    ========================================================== --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Kuis --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Kuis</p>
                <h3 class="text-2xl font-extrabold text-gray-900 mt-1">{{ $totalKuis }}</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
            </div>
        </div>

        {{-- Total Soal --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Soal</p>
                <h3 class="text-2xl font-extrabold text-gray-900 mt-1">{{ $totalSoal }}</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        {{-- Kuis Tingkat Mudah --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Level Mudah</p>
                <h3 class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $kuisMudah }}</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        {{-- Kuis Tingkat Sulit --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Level Sulit</p>
                <h3 class="text-2xl font-extrabold text-amber-600 mt-1">{{ $kuisSulit }}</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- =========================================================
        FILTER & SEARCH
    ========================================================== --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
        <form method="GET" action="{{ route('guru.kuis.index') }}" class="grid grid-cols-1 md:grid-cols-[1fr_200px_200px_auto] gap-3">
            {{-- Search --}}
            <div class="relative">
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m2.35-5.65a8 8 0 11-16 0 8 8 0 0116 0z"/>
                </svg>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari judul kuis..."
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:bg-white focus:border-purple-500 focus:ring-2 focus:ring-purple-100 outline-none transition"
                >
            </div>

            {{-- Kesulitan --}}
            <select
                name="kesulitan"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-700 focus:bg-white focus:border-purple-500 focus:ring-2 focus:ring-purple-100 outline-none transition"
            >
                <option value="">Semua Kesulitan</option>
                <option value="Mudah" {{ request('kesulitan') === 'Mudah' ? 'selected' : '' }}>Mudah</option>
                <option value="Sedang" {{ request('kesulitan') === 'Sedang' ? 'selected' : '' }}>Sedang</option>
                <option value="Sulit" {{ request('kesulitan') === 'Sulit' ? 'selected' : '' }}>Sulit</option>
            </select>

            {{-- Kelas --}}
            <select
                name="kelas_id"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-700 focus:bg-white focus:border-purple-500 focus:ring-2 focus:ring-purple-100 outline-none transition"
            >
                <option value="">Semua Kelas</option>
                @foreach($kelases as $kls)
                    <option value="{{ $kls->id }}" {{ request('kelas_id') == $kls->id ? 'selected' : '' }}>
                        Kelas {{ $kls->tingkatan }} {{ $kls->nama_kelas }}
                    </option>
                @endforeach
            </select>

            {{-- Buttons --}}
            <div class="flex items-center gap-2">
                <button
                    type="submit"
                    class="px-4 py-2.5 bg-gray-900 text-white rounded-xl text-sm font-semibold hover:bg-gray-800 transition"
                >
                    Filter
                </button>
                @if(request()->hasAny(['search', 'kesulitan', 'kelas_id']))
                    <a
                        href="{{ route('guru.kuis.index') }}"
                        class="px-3 py-2.5 border border-gray-200 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50 transition"
                    >
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- =========================================================
        LIST KUIS
    ========================================================== --}}
    @if($quizzes->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($quizzes as $quiz)
                @php
                    $difficultyBadge = match(strtolower($quiz->kesulitan)) {
                        'mudah' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'sedang' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'sulit' => 'bg-rose-50 text-rose-700 border-rose-200',
                        default => 'bg-gray-50 text-gray-700 border-gray-200',
                    };
                @endphp
                <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between group">
                    <div>
                        {{-- Top Badges --}}
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold border {{ $difficultyBadge }}">
                                {{ $quiz->kesulitan }}
                            </span>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                {{ $quiz->level }}
                            </span>
                        </div>

                        {{-- Title --}}
                        <h3 class="text-lg font-bold text-gray-900 group-hover:text-purple-600 transition-colors line-clamp-2">
                            {{ $quiz->judul }}
                        </h3>

                        {{-- Meta --}}
                        <div class="mt-3 space-y-1.5 text-xs text-gray-500">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                <span class="font-medium text-gray-700 truncate">
                                    {{ $quiz->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? 'Mapel Umum' }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m3 0h1"/>
                                </svg>
                                <span>
                                    Kelas {{ $quiz->pengampuKelas?->kelas?->tingkatan }} {{ $quiz->pengampuKelas?->kelas?->nama_kelas }}
                                </span>
                            </div>
                        </div>

                        {{-- Counts --}}
                        <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs font-semibold text-gray-600">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $quiz->soals->count() }} Soal
                            </span>
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                {{ $quiz->nilais->count() }} Percobaan
                            </span>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="mt-5 pt-3 border-t border-gray-100 flex items-center justify-between gap-2">
                        <a
                            href="{{ route('guru.kuis.show', $quiz) }}"
                            class="px-3.5 py-1.5 rounded-lg bg-purple-50 text-purple-700 text-xs font-bold hover:bg-purple-100 transition"
                        >
                            Detail Soal
                        </a>

                        <div class="flex items-center gap-1">
                            {{-- Edit --}}
                            <button
                                @click="openEditModal({{ json_encode($quiz) }})"
                                type="button"
                                class="p-1.5 text-gray-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition"
                                title="Edit Kuis"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>

                            {{-- Delete --}}
                            <form
                                method="POST"
                                action="{{ route('guru.kuis.destroy', $quiz) }}"
                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus kuis ini?')"
                            >
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="p-1.5 text-gray-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition"
                                    title="Hapus Kuis"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $quizzes->links() }}
        </div>
    @else
        <div class="bg-white rounded-2xl border border-dashed border-gray-300 p-12 text-center">
            <div class="w-16 h-16 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
            </div>
            <h3 class="text-base font-bold text-gray-900">Belum Ada Kuis</h3>
            <p class="text-sm text-gray-500 mt-1 max-w-sm mx-auto">
                Mulai buat kuis interaktif untuk menguji pemahaman siswa terhadap materi pembelajaran.
            </p>
            <button
                @click="showModal = true"
                type="button"
                class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-purple-600 text-white text-sm font-semibold hover:bg-purple-700 transition"
            >
                + Buat Kuis Sekarang
            </button>
        </div>
    @endif

    {{-- =========================================================
        MODAL BUAT KUIS BARU
    ========================================================== --}}
    <div
        x-show="showModal"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
    >
        <div
            @click.away="showModal = false"
            class="bg-white w-full max-w-lg rounded-2xl shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-200"
        >
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Buat Kuis Baru</h3>
                </div>
                <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('guru.kuis.store') }}" class="p-6 space-y-4">
                @csrf

                {{-- Judul --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Judul Kuis <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        name="judul"
                        required
                        placeholder="Contoh: Kuis 1 - Pemahaman Aljabar"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:bg-white focus:border-purple-500 focus:ring-2 focus:ring-purple-100 outline-none transition"
                    >
                </div>

                {{-- Kelas & Mapel --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Mata Pelajaran & Kelas <span class="text-rose-500">*</span></label>
                    <select
                        name="pengampu_kelas_id"
                        required
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-700 focus:bg-white focus:border-purple-500 focus:ring-2 focus:ring-purple-100 outline-none transition"
                    >
                        <option value="">Pilih Mata Pelajaran & Kelas...</option>
                        @foreach($pengampuKelases as $pk)
                            <option value="{{ $pk->id }}">
                                {{ $pk->guruMapel?->mapel?->nama_mapel }} - Kelas {{ $pk->kelas?->tingkatan }} {{ $pk->kelas?->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    {{-- Level --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Level / Kategori <span class="text-rose-500">*</span></label>
                        <input
                            type="text"
                            name="level"
                            required
                            placeholder="Contoh: Level 1 / Dasar"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:bg-white focus:border-purple-500 focus:ring-2 focus:ring-purple-100 outline-none transition"
                        >
                    </div>

                    {{-- Kesulitan --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Tingkat Kesulitan <span class="text-rose-500">*</span></label>
                        <select
                            name="kesulitan"
                            required
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-700 focus:bg-white focus:border-purple-500 focus:ring-2 focus:ring-purple-100 outline-none transition"
                        >
                            <option value="Mudah">Mudah</option>
                            <option value="Sedang">Sedang</option>
                            <option value="Sulit">Sulit</option>
                        </select>
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100">
                    <button
                        @click="showModal = false"
                        type="button"
                        class="px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="px-5 py-2.5 rounded-xl bg-purple-600 text-white text-sm font-semibold hover:bg-purple-700 transition"
                    >
                        Simpan Kuis
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- =========================================================
        MODAL EDIT KUIS
    ========================================================== --}}
    <div
        x-show="showEditModal"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
    >
        <div
            @click.away="showEditModal = false"
            class="bg-white w-full max-w-lg rounded-2xl shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-200"
        >
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Edit Kuis</h3>
                </div>
                <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form
                method="POST"
                :action="'/guru/kuis/' + editData.id"
                class="p-6 space-y-4"
            >
                @csrf
                @method('PUT')

                {{-- Judul --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Judul Kuis <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        name="judul"
                        x-model="editData.judul"
                        required
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:bg-white focus:border-purple-500 focus:ring-2 focus:ring-purple-100 outline-none transition"
                    >
                </div>

                {{-- Kelas & Mapel --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Mata Pelajaran & Kelas <span class="text-rose-500">*</span></label>
                    <select
                        name="pengampu_kelas_id"
                        x-model="editData.pengampu_kelas_id"
                        required
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-700 focus:bg-white focus:border-purple-500 focus:ring-2 focus:ring-purple-100 outline-none transition"
                    >
                        <option value="">Pilih Mata Pelajaran & Kelas...</option>
                        @foreach($pengampuKelases as $pk)
                            <option value="{{ $pk->id }}">
                                {{ $pk->guruMapel?->mapel?->nama_mapel }} - Kelas {{ $pk->kelas?->tingkatan }} {{ $pk->kelas?->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    {{-- Level --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Level / Kategori <span class="text-rose-500">*</span></label>
                        <input
                            type="text"
                            name="level"
                            x-model="editData.level"
                            required
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:bg-white focus:border-purple-500 focus:ring-2 focus:ring-purple-100 outline-none transition"
                        >
                    </div>

                    {{-- Kesulitan --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Tingkat Kesulitan <span class="text-rose-500">*</span></label>
                        <select
                            name="kesulitan"
                            x-model="editData.kesulitan"
                            required
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-700 focus:bg-white focus:border-purple-500 focus:ring-2 focus:ring-purple-100 outline-none transition"
                        >
                            <option value="Mudah">Mudah</option>
                            <option value="Sedang">Sedang</option>
                            <option value="Sulit">Sulit</option>
                        </select>
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100">
                    <button
                        @click="showEditModal = false"
                        type="button"
                        class="px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="px-5 py-2.5 rounded-xl bg-purple-600 text-white text-sm font-semibold hover:bg-purple-700 transition"
                    >
                        Perbarui Kuis
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
