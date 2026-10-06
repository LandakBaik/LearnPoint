@extends('layouts.app')

@section('title', 'Kelola Soal Kuis: ' . $quiz->judul)

@section('content')
<div class="space-y-6" x-data="{
    showAddModal: false,
    showEditSoalModal: false,
    editSoalData: {
        id: '',
        pertanyaan: '',
        tipe_soal: 'single_choice',
        bobot: 10,
        kunci_jawaban: 'a',
        pilihan: {
            a: '',
            b: '',
            c: '',
            d: ''
        }
    },

    openEditModal(soal) {
        let pil = soal.pilihan || {};
        this.editSoalData = {
            id: soal.id,
            pertanyaan: soal.pertanyaan || '',
            tipe_soal: soal.tipe_soal || 'single_choice',
            bobot: soal.bobot || 10,
            kunci_jawaban: typeof soal.kunci_jawaban === 'string' ? soal.kunci_jawaban : (Array.isArray(soal.kunci_jawaban) ? soal.kunci_jawaban[0] : 'a'),
            pilihan: {
                a: pil.a || '',
                b: pil.b || '',
                c: pil.c || '',
                d: pil.d || ''
            }
        };
        this.showEditSoalModal = true;
    }
}">

    {{-- Alert Success / Error --}}
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-3 animate-in fade-in">
            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm space-y-1">
            @foreach ($errors->all() as $error)
                <p>• {{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- Header with back button & Add Soal action --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a
                href="{{ route('guru.kuis.index') }}"
                class="p-2.5 rounded-xl border border-gray-200 bg-white text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition shadow-sm"
                title="Kembali ke Daftar Kuis"
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

        <div class="flex items-center gap-3">
            @php
                $diffBadge = match(strtolower($quiz->kesulitan)) {
                    'mudah' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'sedang' => 'bg-blue-50 text-blue-700 border-blue-200',
                    'sulit' => 'bg-rose-50 text-rose-700 border-rose-200',
                    default => 'bg-gray-50 text-gray-700 border-gray-200',
                };
            @endphp
            <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold border {{ $diffBadge }}">
                {{ $quiz->kesulitan }}
            </span>
            <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200">
                {{ $quiz->level }}
            </span>
            <button
                @click="showAddModal = true"
                type="button"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-purple-600 text-white font-semibold text-sm hover:bg-purple-700 transition shadow-sm cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                + Tambah Butir Soal
            </button>
        </div>
    </div>

    {{-- Overview Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Jumlah Soal</p>
                <h3 class="text-2xl font-extrabold text-gray-900 mt-1">{{ $quiz->soals->count() }} Butir</h3>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Poin/Bobot</p>
                <h3 class="text-2xl font-extrabold text-purple-600 mt-1">{{ $quiz->soals->sum('bobot') }} Poin</h3>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Siswa Telah Mengerjakan</p>
                <h3 class="text-2xl font-extrabold text-blue-600 mt-1">{{ $quiz->nilais->count() }} Siswa</h3>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Rata-rata Skor</p>
                <h3 class="text-2xl font-extrabold text-emerald-600 mt-1">
                    {{ $quiz->nilais->count() > 0 ? number_format($quiz->nilais->avg('nilai'), 1) : '-' }}
                </h3>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Daftar Soal --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-gray-100 pb-4">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Daftar Butir Soal Kuis</h3>
                <p class="text-xs text-gray-500 mt-0.5">Soal-soal interaktif yang akan dikerjakan siswa secara bertahap.</p>
            </div>
            <button
                @click="showAddModal = true"
                type="button"
                class="text-xs font-bold text-purple-600 hover:text-purple-700 flex items-center gap-1 transition"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Soal
            </button>
        </div>

        @if($quiz->soals->count() > 0)
            <div class="space-y-4">
                @foreach($quiz->soals as $index => $soal)
                    <div class="p-5 rounded-2xl bg-gray-50/70 border border-gray-200 hover:border-purple-200 transition space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-3">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-xl bg-purple-600 text-white font-extrabold text-xs shrink-0 shadow-sm">
                                    {{ $index + 1 }}
                                </span>
                                <div class="text-sm font-semibold text-gray-800 leading-relaxed">
                                    {!! nl2br(e($soal->pertanyaan)) !!}
                                </div>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                <span class="text-xs font-bold text-purple-700 bg-purple-50 px-2.5 py-1 rounded-lg border border-purple-200">
                                    {{ $soal->bobot }} Poin
                                </span>

                                {{-- Edit Soal Button --}}
                                <button
                                    @click="openEditModal({{ json_encode($soal) }})"
                                    type="button"
                                    class="p-1.5 text-gray-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition"
                                    title="Edit Soal"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>

                                {{-- Delete Soal --}}
                                <form
                                    method="POST"
                                    action="{{ route('guru.kuis.soal.destroy', ['quiz' => $quiz, 'soal' => $soal]) }}"
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus butir soal ini?')"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="p-1.5 text-gray-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition"
                                        title="Hapus Soal"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Image if exists --}}
                        @if($soal->gambar)
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $soal->gambar) }}" alt="Gambar Soal" class="max-h-48 rounded-xl border border-gray-200 object-cover">
                            </div>
                        @endif

                        {{-- Options List --}}
                        @if(!empty($soal->pilihan) && is_array($soal->pilihan))
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-2">
                                @foreach($soal->pilihan as $key => $opt)
                                    @php
                                        $isKey = is_array($soal->kunci_jawaban) 
                                            ? in_array($key, $soal->kunci_jawaban) 
                                            : (strtolower($soal->kunci_jawaban) == strtolower($key));
                                    @endphp
                                    <div class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl text-xs transition {{ $isKey ? 'bg-emerald-50 border border-emerald-300 font-bold text-emerald-900 shadow-sm' : 'bg-white border border-gray-200 text-gray-700' }}">
                                        <span class="w-5 h-5 rounded-lg {{ $isKey ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600' }} font-bold flex items-center justify-center shrink-0">
                                            {{ strtoupper($key) }}
                                        </span>
                                        <span class="flex-1">{{ $opt }}</span>
                                        @if($isKey)
                                            <span class="text-emerald-600 font-extrabold text-[11px] shrink-0">✓ KUNCI</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="py-12 text-center text-gray-400">
                <div class="w-14 h-14 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <p class="text-sm font-bold text-gray-700">Belum ada butir soal pada kuis ini</p>
                <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                    Klik tombol di bawah untuk mulai menambahkan pertanyaan pilihan ganda atau tipe soal lainnya.
                </p>
                <button
                    @click="showAddModal = true"
                    type="button"
                    class="mt-4 px-4 py-2 rounded-xl bg-purple-600 text-white font-semibold text-xs hover:bg-purple-700 transition"
                >
                    + Tambah Soal Pertama
                </button>
            </div>
        @endif
    </div>

    {{-- =========================================================
        MODAL TAMBAH BUTIR SOAL
    ========================================================== --}}
    <div
        x-show="showAddModal"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
    >
        <div
            @click.away="showAddModal = false"
            class="bg-white w-full max-w-2xl rounded-2xl shadow-xl overflow-hidden max-h-[90vh] flex flex-col animate-in fade-in zoom-in-95 duration-200"
        >
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-gray-900">Tambah Butir Soal Kuis</h3>
                </div>
                <button @click="showAddModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form
                method="POST"
                action="{{ route('guru.kuis.soal.store', $quiz) }}"
                enctype="multipart/form-data"
                class="p-6 overflow-y-auto space-y-4"
            >
                @csrf
                <input type="hidden" name="tipe_soal" value="single_choice">

                {{-- Pertanyaan --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Pertanyaan / Soal <span class="text-rose-500">*</span></label>
                    <textarea
                        name="pertanyaan"
                        rows="3"
                        required
                        placeholder="Tuliskan teks pertanyaan kuis..."
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:bg-white focus:border-purple-500 focus:ring-2 focus:ring-purple-100 outline-none transition"
                    ></textarea>
                </div>

                {{-- Bobot & Gambar --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Bobot Poin <span class="text-rose-500">*</span></label>
                        <input
                            type="number"
                            name="bobot"
                            value="10"
                            min="1"
                            max="100"
                            required
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:bg-white focus:border-purple-500 focus:ring-2 focus:ring-purple-100 outline-none transition"
                        >
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Gambar Pendukung (Opsional)</label>
                        <input
                            type="file"
                            name="gambar"
                            accept="image/*"
                            class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 cursor-pointer"
                        >
                    </div>
                </div>

                {{-- Opsi Pilihan A, B, C, D & Radio Kunci Jawaban --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Pilihan Jawaban & Tentukan Kunci Jawaban <span class="text-rose-500">*</span></label>
                    <div class="space-y-2.5">
                        @foreach(['a' => 'Pilihan A', 'b' => 'Pilihan B', 'c' => 'Pilihan C', 'd' => 'Pilihan D'] as $key => $label)
                            <div class="flex items-center gap-3 p-2.5 rounded-xl border border-gray-200 bg-gray-50/50 hover:border-purple-200 transition">
                                <label class="flex items-center gap-2 cursor-pointer shrink-0">
                                    <input
                                        type="radio"
                                        name="kunci_jawaban"
                                        value="{{ $key }}"
                                        {{ $key === 'a' ? 'checked' : '' }}
                                        required
                                        class="w-4 h-4 text-purple-600 focus:ring-purple-500"
                                    >
                                    <span class="w-6 h-6 rounded-lg bg-gray-200 font-bold text-xs flex items-center justify-center text-gray-700">
                                        {{ strtoupper($key) }}
                                    </span>
                                </label>
                                <input
                                    type="text"
                                    name="pilihan[{{ $key }}]"
                                    required
                                    placeholder="Teks untuk {{ $label }}..."
                                    class="flex-1 px-3 py-1.5 rounded-lg border border-gray-200 bg-white text-sm focus:border-purple-500 focus:ring-1 focus:ring-purple-100 outline-none transition"
                                >
                            </div>
                        @endforeach
                    </div>
                    <p class="text-[11px] text-gray-400 mt-1.5">* Pilih tombol radio lingkaran pada opsi yang merupakan kunci jawaban yang benar.</p>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100">
                    <button
                        @click="showAddModal = false"
                        type="button"
                        class="px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="px-5 py-2.5 rounded-xl bg-purple-600 text-white text-sm font-semibold hover:bg-purple-700 transition shadow-sm"
                    >
                        Simpan Soal
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- =========================================================
        MODAL EDIT BUTIR SOAL
    ========================================================== --}}
    <div
        x-show="showEditSoalModal"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
    >
        <div
            @click.away="showEditSoalModal = false"
            class="bg-white w-full max-w-2xl rounded-2xl shadow-xl overflow-hidden max-h-[90vh] flex flex-col animate-in fade-in zoom-in-95 duration-200"
        >
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-gray-900">Edit Butir Soal Kuis</h3>
                </div>
                <button @click="showEditSoalModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form
                method="POST"
                :action="'/guru/kuis/{{ $quiz->id }}/soal/' + editSoalData.id"
                enctype="multipart/form-data"
                class="p-6 overflow-y-auto space-y-4"
            >
                @csrf
                @method('PUT')
                <input type="hidden" name="tipe_soal" value="single_choice">

                {{-- Pertanyaan --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Pertanyaan / Soal <span class="text-rose-500">*</span></label>
                    <textarea
                        name="pertanyaan"
                        x-model="editSoalData.pertanyaan"
                        rows="3"
                        required
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:bg-white focus:border-purple-500 focus:ring-2 focus:ring-purple-100 outline-none transition"
                    ></textarea>
                </div>

                {{-- Bobot & Gambar --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Bobot Poin <span class="text-rose-500">*</span></label>
                        <input
                            type="number"
                            name="bobot"
                            x-model="editSoalData.bobot"
                            min="1"
                            max="100"
                            required
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:bg-white focus:border-purple-500 focus:ring-2 focus:ring-purple-100 outline-none transition"
                        >
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Ganti Gambar (Opsional)</label>
                        <input
                            type="file"
                            name="gambar"
                            accept="image/*"
                            class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 cursor-pointer"
                        >
                    </div>
                </div>

                {{-- Opsi Pilihan A, B, C, D --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Pilihan Jawaban & Kunci Jawaban <span class="text-rose-500">*</span></label>
                    <div class="space-y-2.5">
                        @foreach(['a' => 'Pilihan A', 'b' => 'Pilihan B', 'c' => 'Pilihan C', 'd' => 'Pilihan D'] as $key => $label)
                            <div class="flex items-center gap-3 p-2.5 rounded-xl border border-gray-200 bg-gray-50/50 hover:border-purple-200 transition">
                                <label class="flex items-center gap-2 cursor-pointer shrink-0">
                                    <input
                                        type="radio"
                                        name="kunci_jawaban"
                                        value="{{ $key }}"
                                        x-model="editSoalData.kunci_jawaban"
                                        required
                                        class="w-4 h-4 text-purple-600 focus:ring-purple-500"
                                    >
                                    <span class="w-6 h-6 rounded-lg bg-gray-200 font-bold text-xs flex items-center justify-center text-gray-700">
                                        {{ strtoupper($key) }}
                                    </span>
                                </label>
                                <input
                                    type="text"
                                    name="pilihan[{{ $key }}]"
                                    x-model="editSoalData.pilihan.{{ $key }}"
                                    required
                                    placeholder="Teks untuk {{ $label }}..."
                                    class="flex-1 px-3 py-1.5 rounded-lg border border-gray-200 bg-white text-sm focus:border-purple-500 focus:ring-1 focus:ring-purple-100 outline-none transition"
                                >
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100">
                    <button
                        @click="showEditSoalModal = false"
                        type="button"
                        class="px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="px-5 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition shadow-sm"
                    >
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
