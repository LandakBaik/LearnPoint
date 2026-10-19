@extends('layouts.app')

@section('title', 'Kuis Pembelajaran')

@section('content')
<div class="space-y-6" x-data="{
    showModal: false,
    showEditModal: false,
    editData: {
        id: '',
        judul: '',
        deskripsi: '',
        pengampu_kelas_id: '',
        kesulitan_aktif: ['Mudah', 'Sedang', 'Sulit'],
        status: 'draft'
    },

    openEditModal(quiz) {
        this.editData = {
            id: quiz.id,
            judul: quiz.judul || '',
            deskripsi: quiz.deskripsi || '',
            pengampu_kelas_id: quiz.pengampu_kelas_id,
            kesulitan_aktif: Array.isArray(quiz.kesulitan_aktif) && quiz.kesulitan_aktif.length > 0
                ? quiz.kesulitan_aktif
                : ['Mudah', 'Sedang', 'Sulit'],
            status: quiz.status || 'draft'
        };
        this.showEditModal = true;
    }
}">

    {{-- =========================================================
        HEADER & ACTION
    ========================================================== --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-2xl bg-purple-100 text-purple-700 shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                </span>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                        Kuis Petualangan
                    </h1>
                </div>
            </div>
            <p class="mt-1 text-sm text-gray-500">
                Sistem kuis bertema petualangan: draf kuis dengan pilihan 1 hingga 3 Arc Kesulitan (Mudah, Sedang, Sulit), 5 level per arc, dan maksimal 10 soal per level.
            </p>
        </div>

        <button
            @click="showModal = true"
            type="button"
            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-purple-600 text-white text-sm font-semibold hover:bg-purple-700 active:scale-[0.98] transition shadow-sm cursor-pointer"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            Buat Draf Kuis
        </button>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-3 animate-in fade-in">
            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center gap-3 animate-in fade-in">
            <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm space-y-1">
            @foreach($errors->all() as $error)
                <p>• {{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- =========================================================
        INFO PANDUAN PETUALANGAN KUIS
    ========================================================== --}}
    <div class="bg-gradient-to-r from-purple-50 via-indigo-50 to-blue-50 border border-purple-100 rounded-2xl p-5 shadow-sm">
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
            <div class="space-y-1">
                <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                    <span class="inline-flex w-2.5 h-2.5 rounded-full bg-purple-600 animate-pulse"></span>
                    Struktur Petualangan Kuis
                </h3>
                <p class="text-xs text-gray-600 leading-relaxed max-w-3xl">
                    1. <strong>Draf Kuis</strong> berisi petualangan belajar yang dapat Anda terbitkan kapan saja.<br>
                    2. Didalam setiap kuis terdapat <strong>hingga 3 Arc Kesulitan</strong> (Mudah, Sedang, Sulit. Anda bisa memilih hanya 2 atau 3 arc).<br>
                    3. Setiap Arc Kesulitan memiliki <strong>5 Level Tantangan</strong>, dan setiap level dapat diisi <strong>maksimal 10 butir soal</strong>.
                </p>
            </div>
            <div class="flex items-center gap-2 shrink-0 flex-wrap">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-100/90 text-emerald-800 border border-emerald-200 text-xs font-bold shadow-xs">
                    😊 Arc Mudah
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-100/90 text-indigo-800 border border-indigo-200 text-xs font-bold shadow-xs">
                    🧠 Arc Sedang
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-100/90 text-rose-800 border border-rose-200 text-xs font-bold shadow-xs">
                    🔥 Arc Sulit
                </span>
            </div>
        </div>
    </div>

    {{-- =========================================================
        STATISTIK CARDS
    ========================================================== --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Kuis Petualangan --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm flex items-center justify-between hover:shadow-md transition">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Kuis</p>
                <h3 class="text-2xl font-extrabold text-gray-900 mt-1">{{ $totalKuis }}</h3>
                <p class="text-[11px] text-gray-400 mt-0.5">Petualangan dibuat</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
            </div>
        </div>

        {{-- Kuis Draf --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm flex items-center justify-between hover:shadow-md transition">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Status Draf</p>
                <h3 class="text-2xl font-extrabold text-amber-600 mt-1">{{ $totalDraft }}</h3>
                <p class="text-[11px] text-gray-400 mt-0.5">Masih dalam penyusunan</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </div>
        </div>

        {{-- Kuis Diterbitkan --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm flex items-center justify-between hover:shadow-md transition">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Diterbitkan</p>
                <h3 class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $totalPublished }}</h3>
                <p class="text-[11px] text-gray-400 mt-0.5">Siap diakses siswa</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        {{-- Total Butir Soal --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm flex items-center justify-between hover:shadow-md transition">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Soal</p>
                <h3 class="text-2xl font-extrabold text-blue-600 mt-1">{{ $totalSoal }}</h3>
                <p class="text-[11px] text-gray-400 mt-0.5">Maks 10 soal per level</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- =========================================================
        FILTER & SEARCH
    ========================================================== --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-4 sm:p-5 shadow-sm">
        <form method="GET" action="{{ route('guru.kuis.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            {{-- Search --}}
            <div class="relative lg:col-span-2">
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m2.35-5.65a8 8 0 11-16 0 8 8 0 0116 0z"/>
                </svg>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari judul kuis petualangan..."
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:bg-white focus:border-purple-500 focus:ring-2 focus:ring-purple-100 outline-none transition"
                >
            </div>

            {{-- Status --}}
            <select
                name="status"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-700 focus:bg-white focus:border-purple-500 focus:ring-2 focus:ring-purple-100 outline-none transition"
            >
                <option value="">Semua Status</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draf</option>
                <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Diterbitkan</option>
            </select>

            {{-- Kesulitan / Arc --}}
            <select
                name="kesulitan"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-700 focus:bg-white focus:border-purple-500 focus:ring-2 focus:ring-purple-100 outline-none transition"
            >
                <option value="">Semua Kesulitan</option>
                <option value="Mudah" {{ request('kesulitan') === 'Mudah' ? 'selected' : '' }}>Memiliki Arc Mudah</option>
                <option value="Sedang" {{ request('kesulitan') === 'Sedang' ? 'selected' : '' }}>Memiliki Arc Sedang</option>
                <option value="Sulit" {{ request('kesulitan') === 'Sulit' ? 'selected' : '' }}>Memiliki Arc Sulit</option>
            </select>

            {{-- Kelas & Action --}}
            <div class="flex items-center gap-2">
                <select
                    name="kelas_id"
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-700 focus:bg-white focus:border-purple-500 focus:ring-2 focus:ring-purple-100 outline-none transition"
                >
                    <option value="">Semua Kelas</option>
                    @foreach($kelases as $kls)
                        <option value="{{ $kls->id }}" {{ request('kelas_id') == $kls->id ? 'selected' : '' }}>
                            {{ $kls->tingkatan }} {{ $kls->nama_kelas }}
                        </option>
                    @endforeach
                </select>

                <button
                    type="submit"
                    class="px-4 py-2.5 bg-gray-900 text-white rounded-xl text-sm font-semibold hover:bg-gray-800 transition shrink-0 cursor-pointer"
                >
                    Filter
                </button>

                @if(request()->hasAny(['search', 'status', 'kesulitan', 'kelas_id']))
                    <a
                        href="{{ route('guru.kuis.index') }}"
                        class="px-3 py-2.5 border border-gray-200 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50 transition shrink-0"
                        title="Reset Filter"
                    >
                        ✕
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- =========================================================
        LIST KUIS PETUALANGAN
    ========================================================== --}}
    @if($quizzes->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($quizzes as $quiz)
                @php
                    $isPublished = ($quiz->status === 'published');
                    $activeArcs = $quiz->kesulitan_aktif_array;
                    $soalTotal = $quiz->soals_count ?? $quiz->soals->count();
                @endphp
                <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between group relative overflow-hidden">
                    <div class="space-y-3">
                        {{-- Top Bar: Status Draf/Published & Mapel --}}
                        <div class="flex items-center justify-between gap-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold border {{ $isPublished ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $isPublished ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                {{ $isPublished ? 'Diterbitkan' : 'Draf' }}
                            </span>

                            <span class="text-xs font-semibold text-gray-500 truncate max-w-[150px]">
                                Kelas {{ $quiz->pengampuKelas?->kelas?->tingkatan }} {{ $quiz->pengampuKelas?->kelas?->nama_kelas }}
                            </span>
                        </div>

                        {{-- Judul & Deskripsi --}}
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 group-hover:text-purple-600 transition-colors line-clamp-1">
                                {{ $quiz->judul }}
                            </h3>
                            @if($quiz->deskripsi)
                                <p class="text-xs text-gray-500 mt-1 line-clamp-2 leading-relaxed">
                                    {{ $quiz->deskripsi }}
                                </p>
                            @endif
                        </div>

                        {{-- Mapel --}}
                        <div class="flex items-center gap-2 text-xs text-gray-600">
                            <svg class="w-4 h-4 text-purple-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                            <span class="font-semibold text-gray-800">
                                {{ $quiz->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? 'Mata Pelajaran' }}
                            </span>
                        </div>

                        {{-- Arc Kesulitan yang Aktif (1 s/d 3) --}}
                        <div class="pt-2">
                            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">
                                Arc Kesulitan ({{ count($activeArcs) }} / 3):
                            </p>
                            <div class="flex items-center gap-1.5 flex-wrap">
                                @if(in_array('Mudah', $activeArcs))
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        😊 Mudah
                                    </span>
                                @endif
                                @if(in_array('Sedang', $activeArcs))
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        🧠 Sedang
                                    </span>
                                @endif
                                @if(in_array('Sulit', $activeArcs))
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        🔥 Sulit
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Total Soal & Info Percobaan --}}
                        <div class="pt-2 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                            <span class="flex items-center gap-1 text-gray-700 font-medium">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <strong>{{ $soalTotal }}</strong> Butir Soal Terpasang
                            </span>
                            <span>
                                {{ $quiz->nilais->count() }} siswa selesai
                            </span>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="mt-5 pt-3 border-t border-gray-100 flex items-center justify-between gap-2">
                        <a
                            href="{{ route('guru.kuis.show', $quiz) }}"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-purple-600 text-white text-xs font-bold hover:bg-purple-700 transition shadow-xs"
                        >
                            <span>Kelola Arc & Level</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>

                        <div class="flex items-center gap-1">
                            {{-- Toggle Publish Status --}}
                            <form method="POST" action="{{ route('guru.kuis.toggle-status', $quiz) }}">
                                @csrf
                                @method('PATCH')
                                <button
                                    type="submit"
                                    class="p-2 rounded-xl border text-xs font-bold transition {{ $isPublished ? 'border-amber-200 text-amber-700 hover:bg-amber-50' : 'border-emerald-200 text-emerald-700 hover:bg-emerald-50' }}"
                                    title="{{ $isPublished ? 'Ubah ke Draf' : 'Terbitkan Kuis Sekarang' }}"
                                >
                                    @if($isPublished)
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    @endif
                                </button>
                            </form>

                            {{-- Edit --}}
                            <button
                                @click="openEditModal({{ json_encode($quiz) }})"
                                type="button"
                                class="p-2 text-gray-400 hover:text-blue-600 rounded-xl hover:bg-blue-50 transition cursor-pointer"
                                title="Edit Pengaturan Kuis"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>

                            {{-- Delete --}}
                            <form
                                method="POST"
                                action="{{ route('guru.kuis.destroy', $quiz) }}"
                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus kuis petualangan {{ $quiz->judul }}? Seluruh level dan butir soal akan ikut terhapus.')"
                            >
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="p-2 text-gray-400 hover:text-rose-600 rounded-xl hover:bg-rose-50 transition cursor-pointer"
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
            <h3 class="text-base font-bold text-gray-900">Belum Ada Kuis Petualangan</h3>
            <p class="text-sm text-gray-500 mt-1 max-w-sm mx-auto">
                Mulai buat draf kuis dengan sistem petualangan (hingga 3 Arc Kesulitan, 5 level tiap arc, dan 10 soal per level).
            </p>
            <button
                @click="showModal = true"
                type="button"
                class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-purple-600 text-white text-sm font-semibold hover:bg-purple-700 transition cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                Buat Draf Kuis Sekarang
            </button>
        </div>
    @endif

    {{-- =========================================================
        MODAL BUAT DRAF KUIS BARU
    ========================================================== --}}
    <div
        x-show="showModal"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 overflow-y-auto"
    >
        <div
            @click.away="showModal = false"
            class="bg-white w-full max-w-xl rounded-2xl shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-200 my-8"
        >
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Buat Draf Kuis Petualangan</h3>
                        <p class="text-xs text-gray-500">Tentukan judul, kelas, dan Arc Kesulitan yang aktif.</p>
                    </div>
                </div>
                <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('guru.kuis.store') }}" class="p-6 space-y-4">
                @csrf

                {{-- Judul Kuis --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">
                        Judul Kuis Petualangan <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="judul"
                        required
                        placeholder="Contoh: Petualangan Menjelajah Logika Aljabar"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:bg-white focus:border-purple-500 focus:ring-2 focus:ring-purple-100 outline-none transition"
                    >
                </div>

                {{-- Deskripsi --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">
                        Deskripsi / Pengantar Petualangan (Opsional)
                    </label>
                    <textarea
                        name="deskripsi"
                        rows="2"
                        placeholder="Ceritakan latar belakang misi atau materi pada petualangan kuis ini..."
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:bg-white focus:border-purple-500 focus:ring-2 focus:ring-purple-100 outline-none transition"
                    ></textarea>
                </div>

                {{-- Pengampu Kelas (Mapel & Kelas) --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">
                        Mata Pelajaran & Kelas <span class="text-rose-500">*</span>
                    </label>
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

                {{-- Pilihan Arc Kesulitan yang Aktif (1 sampai 3 Arc) --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">
                        Pilih Arc Kesulitan yang Aktif (Maksimal 3) <span class="text-rose-500">*</span>
                    </label>
                    <p class="text-xs text-gray-500 mb-2.5">
                        Kuis dapat memiliki hingga 3 tingkat kesulitan (misal hanya Mudah & Sedang, atau ketiganya). Tiap arc memiliki 5 level.
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                        {{-- Mudah --}}
                        <label class="flex items-start gap-3 p-3 rounded-xl border border-emerald-200 bg-emerald-50/40 hover:bg-emerald-50 cursor-pointer transition">
                            <input
                                type="checkbox"
                                name="kesulitan_aktif[]"
                                value="Mudah"
                                checked
                                class="w-4 h-4 mt-0.5 text-emerald-600 rounded focus:ring-emerald-500"
                            >
                            <div>
                                <span class="text-xs font-bold text-emerald-900 block">😊 Arc Mudah</span>
                                <span class="text-[11px] text-emerald-700">Level 1 - 5 (Konsep Dasar)</span>
                            </div>
                        </label>

                        {{-- Sedang --}}
                        <label class="flex items-start gap-3 p-3 rounded-xl border border-indigo-200 bg-indigo-50/40 hover:bg-indigo-50 cursor-pointer transition">
                            <input
                                type="checkbox"
                                name="kesulitan_aktif[]"
                                value="Sedang"
                                checked
                                class="w-4 h-4 mt-0.5 text-indigo-600 rounded focus:ring-indigo-500"
                            >
                            <div>
                                <span class="text-xs font-bold text-indigo-900 block">🧠 Arc Sedang</span>
                                <span class="text-[11px] text-indigo-700">Level 1 - 5 (Analisis)</span>
                            </div>
                        </label>

                        {{-- Sulit --}}
                        <label class="flex items-start gap-3 p-3 rounded-xl border border-rose-200 bg-rose-50/40 hover:bg-rose-50 cursor-pointer transition">
                            <input
                                type="checkbox"
                                name="kesulitan_aktif[]"
                                value="Sulit"
                                checked
                                class="w-4 h-4 mt-0.5 text-rose-600 rounded focus:ring-rose-500"
                            >
                            <div>
                                <span class="text-xs font-bold text-rose-900 block">🔥 Arc Sulit</span>
                                <span class="text-[11px] text-rose-700">Level 1 - 5 (Tantangan HOTS)</span>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Status Kuis --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">
                        Status Awal
                    </label>
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700">
                            <input type="radio" name="status" value="draft" checked class="text-purple-600 focus:ring-purple-500">
                            <span>Simpan sebagai <strong>Draf</strong> (belum tampil ke siswa)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700">
                            <input type="radio" name="status" value="published" class="text-purple-600 focus:ring-purple-500">
                            <span>Langsung <strong>Diterbitkan</strong></span>
                        </label>
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100">
                    <button
                        @click="showModal = false"
                        type="button"
                        class="px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="px-5 py-2.5 rounded-xl bg-purple-600 text-white text-sm font-semibold hover:bg-purple-700 transition shadow-sm cursor-pointer"
                    >
                        Simpan & Kelola Level
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- =========================================================
        MODAL EDIT PENGATURAN KUIS
    ========================================================== --}}
    <div
        x-show="showEditModal"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 overflow-y-auto"
    >
        <div
            @click.away="showEditModal = false"
            class="bg-white w-full max-w-xl rounded-2xl shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-200 my-8"
        >
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Edit Pengaturan Kuis</h3>
                        <p class="text-xs text-gray-500">Sesuaikan judul, kelas, arc kesulitan, atau status.</p>
                    </div>
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

                {{-- Judul Kuis --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">
                        Judul Kuis Petualangan <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="judul"
                        x-model="editData.judul"
                        required
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:bg-white focus:border-purple-500 focus:ring-2 focus:ring-purple-100 outline-none transition"
                    >
                </div>

                {{-- Deskripsi --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">
                        Deskripsi Petualangan
                    </label>
                    <textarea
                        name="deskripsi"
                        x-model="editData.deskripsi"
                        rows="2"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:bg-white focus:border-purple-500 focus:ring-2 focus:ring-purple-100 outline-none transition"
                    ></textarea>
                </div>

                {{-- Pengampu Kelas --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">
                        Mata Pelajaran & Kelas <span class="text-rose-500">*</span>
                    </label>
                    <select
                        name="pengampu_kelas_id"
                        x-model="editData.pengampu_kelas_id"
                        required
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-700 focus:bg-white focus:border-purple-500 focus:ring-2 focus:ring-purple-100 outline-none transition"
                    >
                        @foreach($pengampuKelases as $pk)
                            <option value="{{ $pk->id }}">
                                {{ $pk->guruMapel?->mapel?->nama_mapel }} - Kelas {{ $pk->kelas?->tingkatan }} {{ $pk->kelas?->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Pilihan Arc Kesulitan Aktif --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">
                        Arc Kesulitan yang Aktif (1 s/d 3) <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                        <label class="flex items-start gap-3 p-3 rounded-xl border border-emerald-200 bg-emerald-50/40 hover:bg-emerald-50 cursor-pointer transition">
                            <input
                                type="checkbox"
                                name="kesulitan_aktif[]"
                                value="Mudah"
                                :checked="editData.kesulitan_aktif && editData.kesulitan_aktif.includes('Mudah')"
                                class="w-4 h-4 mt-0.5 text-emerald-600 rounded focus:ring-emerald-500"
                            >
                            <div>
                                <span class="text-xs font-bold text-emerald-900 block">😊 Arc Mudah</span>
                                <span class="text-[11px] text-emerald-700">5 Level</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-3 rounded-xl border border-indigo-200 bg-indigo-50/40 hover:bg-indigo-50 cursor-pointer transition">
                            <input
                                type="checkbox"
                                name="kesulitan_aktif[]"
                                value="Sedang"
                                :checked="editData.kesulitan_aktif && editData.kesulitan_aktif.includes('Sedang')"
                                class="w-4 h-4 mt-0.5 text-indigo-600 rounded focus:ring-indigo-500"
                            >
                            <div>
                                <span class="text-xs font-bold text-indigo-900 block">🧠 Arc Sedang</span>
                                <span class="text-[11px] text-indigo-700">5 Level</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-3 rounded-xl border border-rose-200 bg-rose-50/40 hover:bg-rose-50 cursor-pointer transition">
                            <input
                                type="checkbox"
                                name="kesulitan_aktif[]"
                                value="Sulit"
                                :checked="editData.kesulitan_aktif && editData.kesulitan_aktif.includes('Sulit')"
                                class="w-4 h-4 mt-0.5 text-rose-600 rounded focus:ring-rose-500"
                            >
                            <div>
                                <span class="text-xs font-bold text-rose-900 block">🔥 Arc Sulit</span>
                                <span class="text-[11px] text-rose-700">5 Level</span>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">
                        Status Kuis
                    </label>
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700">
                            <input type="radio" name="status" value="draft" x-model="editData.status" class="text-purple-600 focus:ring-purple-500">
                            <span>Draf</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700">
                            <input type="radio" name="status" value="published" x-model="editData.status" class="text-purple-600 focus:ring-purple-500">
                            <span>Diterbitkan</span>
                        </label>
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100">
                    <button
                        @click="showEditModal = false"
                        type="button"
                        class="px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="px-5 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition shadow-sm cursor-pointer"
                    >
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
