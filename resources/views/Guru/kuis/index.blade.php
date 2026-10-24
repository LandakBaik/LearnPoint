@extends('layouts.app')

@section('title', 'Kuis Pembelajaran')

@section('content')
<div class="space-y-6" x-data="{
    showModal: false,
    showEditModal: false,
    selectedQuizzes: [],
    allQuizIds: {{ json_encode($quizzes->pluck('id')->values()->all()) }},
    todayMinDate: '{{ now()->format('Y-m-d\TH:i') }}',
    todayStartDate: '{{ now()->format('Y-m-d\T00:00') }}',

    toggleSelectAll() {
        if (this.selectedQuizzes.length === this.allQuizIds.length) {
            this.selectedQuizzes = [];
        } else {
            this.selectedQuizzes = [...this.allQuizIds];
        }
    },

    confirmBulkDelete() {
        if (!this.selectedQuizzes.length) return;
        Swal.fire({
            title: 'Hapus ' + this.selectedQuizzes.length + ' Kuis Sekaligus?',
            text: 'Seluruh level tantangan, butir soal, dan riwayat nilai pada kuis yang dipilih akan dihapus secara permanen!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus Semua',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('bulkDeleteForm').submit();
            }
        });
    },

    editData: {
        id: '',
        judul: '',
        deskripsi: '',
        pengampu_kelas_id: '',
        kesulitan_aktif: ['Mudah', 'Sedang', 'Sulit'],
        status: 'draft',
        waktu_mulai: '',
        waktu_selesai: '',
        acak_jawaban: false
    },

    formatDateInput(val) {
        if (!val) return '';
        try {
            const d = new Date(val);
            if (isNaN(d.getTime())) return '';
            const offset = d.getTimezoneOffset() * 60000;
            const local = new Date(d.getTime() - offset);
            return local.toISOString().slice(0, 16);
        } catch (e) {
            return '';
        }
    },

    validateSchedule(start, end) {
        if (start) {
            const startDate = new Date(start);
            const now = new Date();
            const startOfToday = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 0, 0, 0);
            if (startDate < startOfToday) {
                Swal.fire({
                    icon: 'error',
                    title: 'Jadwal Tidak Valid',
                    text: 'Waktu mulai kuis tidak boleh sebelum hari ini!'
                });
                return false;
            }
        }
        if (start && end) {
            const startDate = new Date(start);
            const endDate = new Date(end);
            if (endDate.getTime() === startDate.getTime()) {
                Swal.fire({
                    icon: 'error',
                    title: 'Jadwal Tidak Valid',
                    text: 'Waktu selesai tidak boleh sama dengan waktu mulai! Waktu selesai harus lebih lambat dari waktu mulai.'
                });
                return false;
            }
            if (endDate < startDate) {
                Swal.fire({
                    icon: 'error',
                    title: 'Jadwal Tidak Valid',
                    text: 'Waktu selesai harus lebih lambat dari waktu mulai!'
                });
                return false;
            }
        }
        return true;
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
            status: quiz.status || 'draft',
            waktu_mulai: this.formatDateInput(quiz.waktu_mulai),
            waktu_selesai: this.formatDateInput(quiz.waktu_selesai),
            acak_jawaban: !!quiz.acak_jawaban
        };
        this.showEditModal = true;
    }
}">

    {{-- =========================================================
        HEADER & ACTION
    ========================================================== --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-start sm:items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center shrink-0 shadow-xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Kuis Petualangan
                </h1>
                <p class="mt-0.5 text-sm text-slate-500">
                    Sistem kuis bertema petualangan.
                </p>
            </div>
        </div>

        <button
            @click="showModal = true"
            type="button"
            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 active:scale-[0.98] transition shadow-xs hover:shadow cursor-pointer shrink-0"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Buat Draf Kuis</span>
        </button>
    </div>



    {{-- =========================================================
        PANEL INFORMASI STRUKTUR PETUALANGAN
    ========================================================== --}}
    <div class="bg-gradient-to-r from-blue-50/90 via-sky-50/50 to-blue-50/40 border border-blue-100 rounded-2xl p-5 sm:p-6 shadow-xs relative overflow-hidden">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="space-y-2">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-blue-100 text-blue-700 text-xs font-bold">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                    <h3 class="text-sm font-bold text-slate-900 tracking-tight">
                        Struktur & Alur Petualangan Kuis
                    </h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 pt-1 text-xs text-slate-600">
                    <div class="flex items-start gap-2 bg-white/70 backdrop-blur-xs p-2.5 rounded-xl border border-blue-100/70">
                        <span class="font-bold text-blue-600 shrink-0">1.</span>
                        <span>Maksimal <strong>3 Tingkat Kesulitan</strong>: Mudah, Sedang, dan Sulit (dapat disesuaikan).</span>
                    </div>
                    <div class="flex items-start gap-2 bg-white/70 backdrop-blur-xs p-2.5 rounded-xl border border-blue-100/70">
                        <span class="font-bold text-blue-600 shrink-0">2.</span>
                        <span>Setiap tingkat kesulitan memiliki <strong>5 Level</strong> tantangan berurutan.</span>
                    </div>
                    <div class="flex items-start gap-2 bg-white/70 backdrop-blur-xs p-2.5 rounded-xl border border-blue-100/70">
                        <span class="font-bold text-blue-600 shrink-0">3.</span>
                        <span>Tiap level dapat berisi <strong>maksimal 10 butir soal</strong> pilihan ganda.</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0 flex-wrap pt-2 lg:pt-0 border-t lg:border-t-0 border-blue-100">
                <span class="text-xs font-semibold text-slate-500 mr-1">Tingkat Kesulitan:</span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold shadow-xs">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Mudah
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 text-blue-700 border border-blue-200 text-xs font-bold shadow-xs">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                    Sedang
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-50 text-rose-700 border border-rose-200 text-xs font-bold shadow-xs">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                    Sulit
                </span>
            </div>
        </div>
    </div>

    {{-- =========================================================
        STATISTIK CARDS
    ========================================================== --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        {{-- Total Kuis --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs hover:shadow-sm hover:border-slate-300 transition-all flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Total Kuis</p>
                <h3 class="text-3xl font-extrabold text-slate-900">{{ $totalKuis }}</h3>
                <p class="text-[11px] text-slate-400 mt-1">Petualangan dibuat</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
            </div>
        </div>

        {{-- Status Draf --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs hover:shadow-sm hover:border-slate-300 transition-all flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Status Draf</p>
                <h3 class="text-3xl font-extrabold text-amber-600">{{ $totalDraft }}</h3>
                <p class="text-[11px] text-slate-400 mt-1">Masih dalam penyusunan</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 border border-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </div>
        </div>

        {{-- Diterbitkan --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs hover:shadow-sm hover:border-slate-300 transition-all flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Diterbitkan</p>
                <h3 class="text-3xl font-extrabold text-emerald-600">{{ $totalPublished }}</h3>
                <p class="text-[11px] text-slate-400 mt-1">Siap diakses siswa</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        {{-- Total Soal --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs hover:shadow-sm hover:border-slate-300 transition-all flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Total Soal</p>
                <h3 class="text-3xl font-extrabold text-blue-600">{{ $totalSoal }}</h3>
                <p class="text-[11px] text-slate-400 mt-1">Maks 10 soal per level</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-sky-50 border border-sky-100 text-sky-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- =========================================================
        FILTER & SEARCH
    ========================================================== --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 shadow-xs">
        <form method="GET" action="{{ route('guru.kuis.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3">
            {{-- Search --}}
            <div class="relative lg:col-span-4">
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m2.35-5.65a8 8 0 11-16 0 8 8 0 0116 0z"/>
                </svg>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari judul kuis petualangan..."
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition"
                >
            </div>

            {{-- Status --}}
            <div class="lg:col-span-2">
                <select
                    name="status"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 text-sm text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition"
                >
                    <option value="">Semua Status</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draf</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Diterbitkan</option>
                </select>
            </div>

            {{-- Kesulitan / Arc --}}
            <div class="lg:col-span-2">
                <select
                    name="kesulitan"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 text-sm text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition"
                >
                    <option value="">Semua Kesulitan</option>
                    <option value="Mudah" {{ request('kesulitan') === 'Mudah' ? 'selected' : '' }}>Memiliki Arc Mudah</option>
                    <option value="Sedang" {{ request('kesulitan') === 'Sedang' ? 'selected' : '' }}>Memiliki Arc Sedang</option>
                    <option value="Sulit" {{ request('kesulitan') === 'Sulit' ? 'selected' : '' }}>Memiliki Arc Sulit</option>
                </select>
            </div>

            {{-- Kelas --}}
            <div class="lg:col-span-2">
                <select
                    name="kelas_id"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 text-sm text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition"
                >
                    <option value="">Semua Kelas</option>
                    @foreach($kelases as $kls)
                        <option value="{{ $kls->id }}" {{ request('kelas_id') == $kls->id ? 'selected' : '' }}>
                            {{ $kls->tingkatan }} {{ $kls->nama_kelas }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Action Buttons --}}
            <div class="lg:col-span-2 flex items-center gap-2">
                <button
                    type="submit"
                    class="flex-1 py-2.5 px-4 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 active:scale-[0.98] transition shadow-xs cursor-pointer flex items-center justify-center gap-1.5"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <span>Filter</span>
                </button>

                @if(request()->hasAny(['search', 'status', 'kesulitan', 'kelas_id']))
                    <a
                        href="{{ route('guru.kuis.index') }}"
                        class="p-2.5 border border-slate-200 rounded-xl text-sm font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition shrink-0 flex items-center justify-center"
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
        LIST KUIS PETUALANGAN
    ========================================================== --}}
    @if($quizzes->count() > 0)
        {{-- Toolbar Seleksi Massal --}}
        <div class="flex items-center justify-between gap-3 bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center gap-3">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input
                        type="checkbox"
                        @change="toggleSelectAll()"
                        :checked="selectedQuizzes.length > 0 && selectedQuizzes.length === allQuizIds.length"
                        class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500 border-slate-300 cursor-pointer"
                    >
                    <span class="text-xs font-bold text-slate-700">Pilih Semua Halaman Ini (<span x-text="allQuizIds.length"></span>)</span>
                </label>

                <template x-if="selectedQuizzes.length > 0">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-extrabold bg-blue-50 text-blue-700 border border-blue-200">
                        <span x-text="selectedQuizzes.length"></span> Terpilih
                    </span>
                </template>
            </div>

            <template x-if="selectedQuizzes.length > 0">
                <button
                    type="button"
                    @click="confirmBulkDelete()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-600 text-white text-xs font-bold hover:bg-rose-700 transition shadow-xs cursor-pointer"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>Hapus (<span x-text="selectedQuizzes.length"></span>) Kuis Terpilih</span>
                </button>
            </template>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
            @foreach($quizzes as $quiz)
                @php
                    $isPublished = ($quiz->status === 'published');
                    $activeArcs = $quiz->kesulitan_aktif_array;
                    $soalTotal = $quiz->soals_count ?? $quiz->soals->count();
                @endphp
                <div
                    class="rounded-2xl border p-5 sm:p-6 shadow-xs hover:shadow-md transition-all flex flex-col justify-between group"
                    :class="selectedQuizzes.includes({{ $quiz->id }}) ? 'border-blue-500 ring-2 ring-blue-100 bg-blue-50/10' : 'border-slate-200/80 bg-white hover:border-blue-200'"
                >
                    <div class="space-y-3.5">
                        {{-- Top Bar: Checkbox, Status Draf/Diterbitkan & Kelas --}}
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <label class="relative flex items-center cursor-pointer p-0.5 rounded-lg hover:bg-slate-100 transition" title="Pilih kuis ini">
                                    <input
                                        type="checkbox"
                                        :value="{{ $quiz->id }}"
                                        x-model="selectedQuizzes"
                                        class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500 border-slate-300 cursor-pointer"
                                    >
                                </label>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold border {{ $isPublished ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $isPublished ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                    {{ $isPublished ? 'Diterbitkan' : 'Draf' }}
                                </span>
                            </div>

                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-50 text-slate-600 border border-slate-200/80 text-xs font-semibold truncate max-w-[170px]">
                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                <span>Kelas {{ $quiz->pengampuKelas?->kelas?->tingkatan }} {{ $quiz->pengampuKelas?->kelas?->nama_kelas }}</span>
                            </span>
                        </div>

                        {{-- Judul Kuis & Mata Pelajaran --}}
                        <div>
                            <h3 class="text-base sm:text-lg font-bold text-slate-900 group-hover:text-blue-600 transition-colors line-clamp-1 leading-snug">
                                {{ $quiz->judul }}
                            </h3>

                            <div class="flex items-center gap-1.5 text-xs text-blue-600 font-semibold mt-1">
                                <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                <span>{{ $quiz->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? 'Mata Pelajaran' }}</span>
                            </div>

                            @if($quiz->deskripsi)
                                <p class="text-xs text-slate-500 mt-2 line-clamp-2 leading-relaxed">
                                    {{ $quiz->deskripsi }}
                                </p>
                            @endif
                        </div>

                        {{-- Arc Kesulitan yang Aktif (1 s/d 3) --}}
                        <div class="pt-1">
                            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">
                                Arc Kesulitan ({{ count($activeArcs) }} / 3):
                            </p>
                            <div class="flex items-center gap-1.5 flex-wrap">
                                @if(in_array('Mudah', $activeArcs))
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Mudah
                                    </span>
                                @endif
                                @if(in_array('Sedang', $activeArcs))
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        Sedang
                                    </span>
                                @endif
                                @if(in_array('Sulit', $activeArcs))
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        Sulit
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Jadwal & Pengaturan Kuis --}}
                        @php
                            $sched = $quiz->schedule_info;
                        @endphp
                        <div class="pt-2 flex flex-col gap-1.5 text-xs">
                            <div class="flex items-center gap-2 flex-wrap">
                                @if($quiz->waktu_mulai || $quiz->waktu_selesai)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold border {{ $sched['badge_class'] }}" title="{{ $sched['description'] }}">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ $sched['label'] }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[11px] font-medium text-slate-500 bg-slate-100 border border-slate-200">
                                        Tanpa Batas Waktu
                                    </span>
                                @endif

                                @if($quiz->acak_jawaban)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-200" title="Pilihan jawaban diacak untuk setiap siswa">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                        Acak Jawaban
                                    </span>
                                @endif
                            </div>

                            @if($quiz->waktu_mulai || $quiz->waktu_selesai)
                                <div class="text-[11px] text-slate-500 flex items-center gap-1.5 truncate">
                                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span>{{ $quiz->waktu_mulai ? $quiz->waktu_mulai->format('d/m/y H:i') : 'Langsung' }} - {{ $quiz->waktu_selesai ? $quiz->waktu_selesai->format('d/m/y H:i') : 'Seterusnya' }}</span>
                                </div>
                            @endif
                        </div>

                        {{-- Total Soal & Info Siswa Selesai --}}
                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                            <span class="flex items-center gap-1.5 text-slate-700 font-medium">
                                <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <span><strong>{{ $soalTotal }}</strong> Soal Terpasang</span>
                            </span>
                            <span class="flex items-center gap-1 text-slate-500 font-medium">
                                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                <span>{{ $quiz->nilais->count() }} siswa selesai</span>
                            </span>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="mt-5 pt-3.5 border-t border-slate-100 flex items-center justify-between gap-2">
                        <a
                            href="{{ route('guru.kuis.show', $quiz) }}"
                            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 active:scale-[0.98] transition shadow-xs"
                        >
                            <span>Kelola Arc & Level</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>

                        <div class="flex items-center gap-1.5">
                            {{-- Toggle Publish Status --}}
                            <form method="POST" action="{{ route('guru.kuis.toggle-status', $quiz) }}"
                                data-confirm="{{ $isPublished ? 'Apakah Anda yakin ingin mengubah status kuis \'' . addslashes($quiz->judul) . '\' menjadi Draf? Siswa tidak akan dapat mengakses kuis ini sementara waktu.' : 'Apakah Anda yakin ingin menerbitkan kuis \'' . addslashes($quiz->judul) . '\'? Siswa di kelas terkait akan dapat melihat dan mengerjakan kuis ini.' }}">
                                @csrf
                                @method('PATCH')
                                <button
                                    type="submit"
                                    class="p-2 rounded-xl border text-xs font-bold transition {{ $isPublished ? 'border-amber-200 text-amber-700 bg-amber-50/50 hover:bg-amber-100/60' : 'border-emerald-200 text-emerald-700 bg-emerald-50/50 hover:bg-emerald-100/60' }}"
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
                                class="p-2 text-slate-500 hover:text-blue-600 border border-slate-200/80 hover:border-blue-200 rounded-xl hover:bg-blue-50/50 transition cursor-pointer"
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
                                data-confirm="Apakah Anda yakin ingin menghapus kuis petualangan '{{ addslashes($quiz->judul) }}'? Seluruh level tantangan dan butir soal di dalamnya akan dihapus secara permanen."
                            >
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="p-2 text-slate-400 hover:text-rose-600 border border-slate-200/80 hover:border-rose-200 rounded-xl hover:bg-rose-50/50 transition cursor-pointer"
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
        <div class="bg-white rounded-2xl border border-dashed border-slate-200 p-12 text-center shadow-xs">
            <div class="w-16 h-16 rounded-2xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
            </div>
            @if(request()->filled('search') || request()->filled('status') || request()->filled('kelas_id') || request()->filled('kesulitan'))
                <h3 class="text-base font-bold text-slate-900">Kuis Tidak Ditemukan</h3>
                <p class="text-sm text-slate-500 mt-1 max-w-sm mx-auto">
                    Tidak ditemukan kuis petualangan yang cocok dengan kriteria pencarian atau filter yang Anda terapkan.
                </p>
                <div class="mt-5">
                    <a href="{{ route('guru.kuis.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-semibold transition shadow-xs">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span>Reset Filter</span>
                    </a>
                </div>
            @else
                <h3 class="text-base font-bold text-slate-900">Belum Ada Kuis Petualangan</h3>
                <p class="text-sm text-slate-500 mt-1 max-w-sm mx-auto">
                    Mulai buat draf kuis dengan sistem petualangan (hingga 3 Arc Kesulitan, 5 level tiap arc, dan 10 soal per level).
                </p>
                <button
                    @click="showModal = true"
                    type="button"
                    class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 active:scale-[0.98] transition shadow-xs cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Buat Draf Kuis Sekarang</span>
                </button>
            @endif
        </div>
    @endif

    {{-- =========================================================
        MODAL BUAT DRAF KUIS BARU
    ========================================================== --}}
    <div
        x-show="showModal"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4 sm:p-6 overflow-hidden"
    >
        <div
            @click.away="showModal = false"
            class="bg-white w-full max-w-xl rounded-2xl shadow-2xl border border-slate-200/80 overflow-hidden max-h-[90vh] flex flex-col animate-in fade-in zoom-in-95 duration-200 my-auto"
        >
            <div class="px-6 py-4 sm:py-5 border-b border-slate-100 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Buat Draf Kuis Petualangan</h3>
                        <p class="text-xs text-slate-500">Tentukan judul, kelas, jadwal, dan Arc Kesulitan.</p>
                    </div>
                </div>
                <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('guru.kuis.store') }}"
                @submit="
                    if (![...$el.querySelectorAll('input[name=\'kesulitan_aktif[]\']:checked')].length) { 
                        $event.preventDefault(); 
                        Swal.fire({icon: 'warning', title: 'Peringatan', text: 'Pilih minimal satu tingkat kesulitan (Arc) untuk petualangan kuis ini!'}); 
                        return;
                    }
                    const wm = $el.querySelector('input[name=\'waktu_mulai\']').value;
                    const ws = $el.querySelector('input[name=\'waktu_selesai\']').value;
                    if (!validateSchedule(wm, ws)) {
                        $event.preventDefault();
                    }
                "
                class="p-6 space-y-4 overflow-y-auto flex-1">
                @csrf

                {{-- Judul Kuis --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                        Judul Kuis Petualangan <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="judul"
                        required
                        placeholder="Contoh: Petualangan Menjelajah Logika Aljabar"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition"
                    >
                </div>

                {{-- Deskripsi --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                        Deskripsi / Pengantar Petualangan (Opsional)
                    </label>
                    <textarea
                        name="deskripsi"
                        rows="2"
                        placeholder="Ceritakan latar belakang misi atau materi pada petualangan kuis ini..."
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition"
                    ></textarea>
                </div>

                {{-- Pengampu Kelas (Mapel & Kelas) --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                        Mata Pelajaran & Kelas <span class="text-rose-500">*</span>
                    </label>
                    <select
                        name="pengampu_kelas_id"
                        required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 text-sm text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition"
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
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                        Pilih Arc Kesulitan yang Aktif (Maksimal 3) <span class="text-rose-500">*</span>
                    </label>
                    <p class="text-xs text-slate-500 mb-2.5">
                        Kuis dapat memiliki hingga 3 tingkat kesulitan (Mudah, Sedang, Sulit). Tiap arc memiliki 5 level.
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                        {{-- Mudah --}}
                        <label class="flex items-start gap-2.5 p-3 rounded-xl border border-emerald-200 bg-emerald-50/50 hover:bg-emerald-50 cursor-pointer transition">
                            <input
                                type="checkbox"
                                name="kesulitan_aktif[]"
                                value="Mudah"
                                checked
                                class="w-4 h-4 mt-0.5 text-emerald-600 rounded focus:ring-emerald-500"
                            >
                            <div>
                                <span class="text-xs font-bold text-emerald-900 block">Mudah</span>
                                <span class="text-[11px] text-emerald-700">5 Level (Dasar)</span>
                            </div>
                        </label>

                        {{-- Sedang --}}
                        <label class="flex items-start gap-2.5 p-3 rounded-xl border border-blue-200 bg-blue-50/50 hover:bg-blue-50 cursor-pointer transition">
                            <input
                                type="checkbox"
                                name="kesulitan_aktif[]"
                                value="Sedang"
                                checked
                                class="w-4 h-4 mt-0.5 text-blue-600 rounded focus:ring-blue-500"
                            >
                            <div>
                                <span class="text-xs font-bold text-blue-900 block">Sedang</span>
                                <span class="text-[11px] text-blue-700">5 Level (Analisis)</span>
                            </div>
                        </label>

                        {{-- Sulit --}}
                        <label class="flex items-start gap-2.5 p-3 rounded-xl border border-rose-200 bg-rose-50/50 hover:bg-rose-50 cursor-pointer transition">
                            <input
                                type="checkbox"
                                name="kesulitan_aktif[]"
                                value="Sulit"
                                checked
                                class="w-4 h-4 mt-0.5 text-rose-600 rounded focus:ring-rose-500"
                            >
                            <div>
                                <span class="text-xs font-bold text-rose-900 block">Sulit</span>
                                <span class="text-[11px] text-rose-700">5 Level (HOTS)</span>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Jadwal Mulai & Selesai (Opsional) --}}
                <div class="border-t border-slate-100 pt-3">
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        Jadwal Pengerjaan Kuis (Opsional)
                    </label>
                    <p class="text-xs text-slate-500 mb-2.5">
                        Kosongkan jika kuis dapat dikerjakan kapan saja tanpa batasan tanggal/waktu.
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Waktu Mulai</label>
                            <input
                                type="datetime-local"
                                name="waktu_mulai"
                                :min="todayStartDate"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 text-sm text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition"
                            >
                            <p class="text-[10px] text-slate-400 mt-1">Tidak boleh sebelum hari ini</p>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Waktu Berakhir</label>
                            <input
                                type="datetime-local"
                                name="waktu_selesai"
                                :min="todayStartDate"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 text-sm text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition"
                            >
                            <p class="text-[10px] text-slate-400 mt-1">Harus lebih lambat dari waktu mulai</p>
                        </div>
                    </div>
                </div>

                {{-- Pengacakan Jawaban --}}
                <div class="border-t border-slate-100 pt-3">
                    <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-200 bg-slate-50/60 hover:bg-slate-50 cursor-pointer transition">
                        <input
                            type="checkbox"
                            name="acak_jawaban"
                            value="1"
                            class="w-4 h-4 mt-0.5 text-blue-600 rounded focus:ring-blue-500"
                        >
                        <div>
                            <span class="text-xs font-bold text-slate-900 block">Acak Pilihan Jawaban Soal</span>
                            <span class="text-[11px] text-slate-500">Urutan pilihan jawaban (A, B, C, ...) akan diacak secara dinamis untuk tiap siswa saat mengerjakan.</span>
                        </div>
                    </label>
                </div>

                {{-- Status Kuis --}}
                <div>
                    <input type="hidden" name="status" value="draft">
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-600 flex items-center justify-between">
                        <span>Status Awal: <strong class="text-slate-900">Draf</strong></span>
                        <span class="text-[11px] text-slate-400">Dapat diterbitkan setelah memiliki butir soal</span>
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100 shrink-0">
                    <button
                        @click="showModal = false"
                        type="button"
                        class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="px-5 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 active:scale-[0.98] transition shadow-xs cursor-pointer"
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
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4 sm:p-6 overflow-hidden"
    >
        <div
            @click.away="showEditModal = false"
            class="bg-white w-full max-w-xl rounded-2xl shadow-2xl border border-slate-200/80 overflow-hidden max-h-[90vh] flex flex-col animate-in fade-in zoom-in-95 duration-200 my-auto"
        >
            <div class="px-6 py-4 sm:py-5 border-b border-slate-100 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Edit Pengaturan Kuis</h3>
                        <p class="text-xs text-slate-500">Sesuaikan judul, kelas, arc kesulitan, atau jadwal.</p>
                    </div>
                </div>
                <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form
                method="POST"
                :action="'/guru/kuis/' + editData.id"
                @submit="
                    if (![...$el.querySelectorAll('input[name=\'kesulitan_aktif[]\']:checked')].length) { 
                        $event.preventDefault(); 
                        Swal.fire({icon: 'warning', title: 'Peringatan', text: 'Pilih minimal satu tingkat kesulitan (Arc) untuk petualangan kuis ini!'}); 
                        return;
                    }
                    if (!validateSchedule(editData.waktu_mulai, editData.waktu_selesai)) {
                        $event.preventDefault();
                    }
                "
                class="p-6 space-y-4 overflow-y-auto flex-1"
            >
                @csrf
                @method('PUT')

                {{-- Judul Kuis --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                        Judul Kuis Petualangan <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="judul"
                        x-model="editData.judul"
                        required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition"
                    >
                </div>

                {{-- Deskripsi --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                        Deskripsi Petualangan
                    </label>
                    <textarea
                        name="deskripsi"
                        x-model="editData.deskripsi"
                        rows="2"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition"
                    ></textarea>
                </div>

                {{-- Pengampu Kelas --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                        Mata Pelajaran & Kelas <span class="text-rose-500">*</span>
                    </label>
                    <select
                        name="pengampu_kelas_id"
                        x-model="editData.pengampu_kelas_id"
                        required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 text-sm text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition"
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
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                        Arc Kesulitan yang Aktif (1 s/d 3) <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                        <label class="flex items-start gap-2.5 p-3 rounded-xl border border-emerald-200 bg-emerald-50/50 hover:bg-emerald-50 cursor-pointer transition">
                            <input
                                type="checkbox"
                                name="kesulitan_aktif[]"
                                value="Mudah"
                                :checked="editData.kesulitan_aktif && editData.kesulitan_aktif.includes('Mudah')"
                                class="w-4 h-4 mt-0.5 text-emerald-600 rounded focus:ring-emerald-500"
                            >
                            <div>
                                <span class="text-xs font-bold text-emerald-900 block">Arc Mudah</span>
                                <span class="text-[11px] text-emerald-700">5 Level</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-2.5 p-3 rounded-xl border border-blue-200 bg-blue-50/50 hover:bg-blue-50 cursor-pointer transition">
                            <input
                                type="checkbox"
                                name="kesulitan_aktif[]"
                                value="Sedang"
                                :checked="editData.kesulitan_aktif && editData.kesulitan_aktif.includes('Sedang')"
                                class="w-4 h-4 mt-0.5 text-blue-600 rounded focus:ring-blue-500"
                            >
                            <div>
                                <span class="text-xs font-bold text-blue-900 block">Arc Sedang</span>
                                <span class="text-[11px] text-blue-700">5 Level</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-2.5 p-3 rounded-xl border border-rose-200 bg-rose-50/50 hover:bg-rose-50 cursor-pointer transition">
                            <input
                                type="checkbox"
                                name="kesulitan_aktif[]"
                                value="Sulit"
                                :checked="editData.kesulitan_aktif && editData.kesulitan_aktif.includes('Sulit')"
                                class="w-4 h-4 mt-0.5 text-rose-600 rounded focus:ring-rose-500"
                            >
                            <div>
                                <span class="text-xs font-bold text-rose-900 block">Arc Sulit</span>
                                <span class="text-[11px] text-rose-700">5 Level</span>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Jadwal Mulai & Selesai (Opsional) --}}
                <div class="border-t border-slate-100 pt-3">
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        Jadwal Pengerjaan Kuis (Opsional)
                    </label>
                    <p class="text-xs text-slate-500 mb-2.5">
                        Kosongkan jika kuis dapat dikerjakan kapan saja tanpa batasan tanggal/waktu.
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Waktu Mulai</label>
                            <input
                                type="datetime-local"
                                name="waktu_mulai"
                                x-model="editData.waktu_mulai"
                                :min="todayStartDate"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 text-sm text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition"
                            >
                            <p class="text-[10px] text-slate-400 mt-1">Tidak boleh sebelum hari ini</p>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Waktu Berakhir</label>
                            <input
                                type="datetime-local"
                                name="waktu_selesai"
                                x-model="editData.waktu_selesai"
                                :min="todayStartDate"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 text-sm text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition"
                            >
                            <p class="text-[10px] text-slate-400 mt-1">Harus lebih lambat dari waktu mulai</p>
                        </div>
                    </div>
                </div>

                {{-- Pengacakan Jawaban --}}
                <div class="border-t border-slate-100 pt-3">
                    <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-200 bg-slate-50/60 hover:bg-slate-50 cursor-pointer transition">
                        <input
                            type="checkbox"
                            name="acak_jawaban"
                            value="1"
                            x-model="editData.acak_jawaban"
                            class="w-4 h-4 mt-0.5 text-blue-600 rounded focus:ring-blue-500"
                        >
                        <div>
                            <span class="text-xs font-bold text-slate-900 block">Acak Pilihan Jawaban Soal</span>
                            <span class="text-[11px] text-slate-500">Urutan pilihan jawaban (A, B, C, ...) akan diacak secara dinamis untuk tiap siswa saat mengerjakan.</span>
                        </div>
                    </label>
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                        Status Kuis
                    </label>
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 cursor-pointer text-sm text-slate-700">
                            <input type="radio" name="status" value="draft" x-model="editData.status" class="text-blue-600 focus:ring-blue-500">
                            <span>Draf</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer text-sm text-slate-700">
                            <input type="radio" name="status" value="published" x-model="editData.status" class="text-blue-600 focus:ring-blue-500">
                            <span>Diterbitkan</span>
                        </label>
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100 shrink-0">
                    <button
                        @click="showEditModal = false"
                        type="button"
                        class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="px-5 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 active:scale-[0.98] transition shadow-xs cursor-pointer"
                    >
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- =========================================================
        FORM TERSEMBUNYI UNTUK HAPUS MASSAL (BULK DESTROY)
    ========================================================== --}}
    <form id="bulkDeleteForm" method="POST" action="{{ route('guru.kuis.bulk-destroy') }}" class="hidden">
        @csrf
        <template x-for="id in selectedQuizzes" :key="id">
            <input type="hidden" name="quiz_ids[]" :value="id">
        </template>
    </form>

    {{-- =========================================================
        FLOATING ACTION BAR SELEKSI MASSAL
    ========================================================== --}}
    <div
        x-show="selectedQuizzes.length > 0"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-6"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-6"
        class="fixed bottom-6 inset-x-0 z-40 max-w-lg mx-auto px-4"
    >
        <div class="bg-slate-900/95 backdrop-blur-md text-white rounded-2xl shadow-2xl p-4 flex items-center justify-between gap-4 border border-slate-700/80">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-xl bg-blue-600 text-white text-xs font-black flex items-center justify-center shrink-0 shadow-xs" x-text="selectedQuizzes.length"></span>
                <div>
                    <p class="text-xs font-bold text-white leading-tight">
                        <span x-text="selectedQuizzes.length"></span> Kuis Terpilih
                    </p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Siap dihapus sekaligus</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    @click="selectedQuizzes = []"
                    class="px-3 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-800 transition cursor-pointer"
                >
                    Batalkan
                </button>
                <button
                    type="button"
                    @click="confirmBulkDelete()"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 active:scale-[0.98] text-white text-xs font-bold transition shadow-xs cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    <span>Hapus (<span x-text="selectedQuizzes.length"></span>) Kuis</span>
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
