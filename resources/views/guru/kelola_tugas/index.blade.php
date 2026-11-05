@extends('layouts.app')

@section('title', 'Daftar Tugas')

@section('content')
<div class="space-y-6 text-slate-900" x-data="{
    searchQuery: '',
    selectedStatus: 'semua',
    selectedKelas: '',
    selectedMapel: '',
    sortBy: 'terbaru',
    visibleTaskCount: 0,

    showModal: false,
    showEditModal: false,
    editData: {},
    fileName: '',
    fileSize: '',
    hasFile: false,

    handleFileChange(event) {
        const file = event.target.files[0];
        if (file) {
            this.fileName = file.name;
            this.fileSize = (file.size / (1024 * 1024)).toFixed(1) + ' MB • Siap diunggah';
            this.hasFile = true;
        }
    }
}">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Tugas</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola tugas, pengumpulan, dan penilaian siswa SMP terpadu.</p>
        </div>

        <div>
            <button
                @click="showModal = true"
                type="button"
                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm shadow-lg shadow-indigo-200 transition duration-200 active:scale-[0.98] cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Tugas
            </button>
        </div>
    </div>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

        {{-- Total Tugas --}}
        <div class="relative overflow-hidden bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500">Total Tugas</p>
                    <div class="flex items-baseline gap-2 mt-1">
                        <h3 class="text-2xl font-extrabold text-slate-900">{{ $totalTugas ?? 0 }}</h3>
                        <span class="text-xs text-slate-400">Tugas</span>
                    </div>
                </div>

                <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
            </div>

            <div class="absolute top-0 left-0 right-0 h-1 bg-indigo-100">
                <div class="h-full bg-indigo-700 w-full"></div>
            </div>
        </div>

        {{-- Tugas Aktif --}}
        <div class="relative overflow-hidden bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500">Tugas Aktif</p>
                    <div class="flex items-baseline gap-2 mt-1">
                        <h3 class="text-2xl font-extrabold text-slate-900">{{ $totalAktif }}</h3>
                        <span class="text-xs text-slate-400">Tugas</span>
                    </div>
                </div>

                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            <div class="absolute top-0 left-0 right-0 h-1 bg-blue-100">
                <div class="h-full bg-blue-600 w-full"></div>
            </div>
        </div>

        {{-- Perlu Dinilai --}}
        <div class="relative overflow-hidden bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500">Perlu Dinilai</p>
                    <div class="flex items-baseline gap-2 mt-1">
                        <h3 class="text-2xl font-extrabold text-slate-900">{{ $perluDinilai ?? 0 }}</h3>
                        <span class="text-xs text-slate-400">Tugas</span>
                    </div>
                </div>

                <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>
            </div>

            <div class="absolute top-0 left-0 right-0 h-1 bg-rose-100">
                <div class="h-full bg-rose-500 w-full"></div>
            </div>
        </div>


        {{-- Deadline Terdekat --}}
        <div class="relative overflow-hidden bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500">Deadline Terdekat</p>

                    <div class="flex items-baseline gap-2 mt-1">
                        <h3
                            class="text-2xl font-extrabold text-slate-900"
                            x-effect="
                        searchQuery;
                        selectedStatus;
                        selectedKelas;
                        selectedMapel;

                        $nextTick(() => {
                            const sekarang = Math.floor(Date.now() / 1000);
                            const batasDeadline = sekarang + 86400;

                            const jumlah = [...$refs.daftarTugas.querySelectorAll('article')]
                                .filter(card => {
                                    const deadline = Number(card.dataset.deadline);

                                    return card.getClientRects().length > 0 &&
                                           deadline >= sekarang &&
                                           deadline <= batasDeadline;
                                }).length;

                            $el.textContent = jumlah;
                        });
                    ">{{ $jumlahDeadlineTerdekat ?? 0 }}</h3>

                        <span class="text-xs text-slate-400">Tugas</span>
                    </div>
                </div>

                <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
            </div>

            <div class="absolute top-0 left-0 right-0 h-1 bg-amber-100">
                <div class="h-full bg-amber-500 w-full"></div>
            </div>
        </div>
    </div>

    {{-- Filter dan Pencarian --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm space-y-4">
        <div class="flex items-center gap-2 overflow-x-auto pb-1">
            <button
                type="button"
                @click="selectedStatus = 'semua'"
                :class="selectedStatus === 'semua' ? 'bg-indigo-600 text-white' : 'bg-slate-50 text-slate-600'"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-bold whitespace-nowrap">
                Semua Tugas
            </button>

            <button
                type="button"
                @click="selectedStatus = 'aktif'"
                :class="selectedStatus === 'aktif' ? 'bg-indigo-600 text-white' : 'bg-slate-50 text-slate-600'"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-semibold whitespace-nowrap">
                Tugas Aktif
            </button>

            <button
                type="button"
                @click="selectedStatus = 'perlu_dinilai'"
                :class="selectedStatus === 'perlu_dinilai' ? 'bg-indigo-600 text-white' : 'bg-slate-50 text-slate-600'"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-semibold whitespace-nowrap">
                <span>Perlu Dinilai</span>
            </button>

            <button
                type="button"
                @click="selectedStatus = 'selesai'"
                :class="selectedStatus === 'selesai' ? 'bg-indigo-600 text-white' : 'bg-slate-50 text-slate-600'"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-semibold whitespace-nowrap">
                <span>Riwayat / Selesai</span>
            </button>
        </div>

        <div class="flex flex-col md:flex-row items-center gap-3">

            {{-- Pencarian --}}
            <div class="relative flex-1 w-full min-w-0 group">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>

                <input
                    x-model="searchQuery"
                    type="text"
                    placeholder="Cari tugas berdasarkan judul..."
                    class="w-full h-10 pl-10 pr-4 rounded-lg border-0 bg-slate-50 text-xs font-medium text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-200 transition">
            </div>

            {{-- Filter Kelas, Mapel, dan Sorting --}}
            <div class="flex items-center justify-end gap-3 w-full md:w-auto shrink-0">
                <select
                    x-model="selectedKelas"
                    class="w-full h-10 px-3 rounded-lg border-0 bg-slate-50 text-xs font-semibold text-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-200 cursor-pointer">
                    <option value="">Semua Kelas</option>
                    @foreach($kelases as $kelas)
                    <option value="{{ $kelas->id }}">{{ $kelas->nama_kelas }}</option>
                    @endforeach
                </select>

                <select
                    x-model="selectedMapel"
                    class="w-full h-10 px-3 rounded-lg border-0 bg-slate-50 text-xs font-semibold text-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-200 cursor-pointer">
                    <option value="">Semua Mata Pelajaran</option>
                    @foreach($mapels as $mapel)
                    <option value="{{ $mapel->id }}">{{ $mapel->nama_mapel }}</option>
                    @endforeach
                </select>

                <div class="flex gap-2">
                    <select
                        x-model="sortBy"
                        @change="(() => {
                            const container = $refs.daftarTugas;
                            const cards = [...container.querySelectorAll(':scope > article')];

                            cards.sort((a, b) => {
                                const A = Number(a.dataset.created);
                                const B = Number(b.dataset.created);
                                const D1 = Number(a.dataset.deadline);
                                const D2 = Number(b.dataset.deadline);

                                return sortBy === 'terbaru' ? B - A :
                                       sortBy === 'terlama' ? A - B : D1 - D2;
                            });

                            cards.forEach(card => container.appendChild(card));
                        })()"
                        class="min-w-0 flex-1 h-10 px-3 rounded-lg border-0 bg-slate-50 text-xs font-semibold text-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-200 cursor-pointer">

                        <option value="terbaru">Terbaru</option>
                        <option value="terlama">Terlama</option>
                        <option value="deadline">Deadline Terdekat</option>
                    </select>

                    <button
                        @click="
                            searchQuery = '';
                            selectedKelas = '';
                            selectedMapel = '';
                            selectedStatus = 'semua';
                            sortBy = 'terbaru';

                            $nextTick(() => {
                                const container = $refs.daftarTugas;
                                const cards = [...container.querySelectorAll(':scope > article')];

                                cards.sort((a, b) =>
                                    Number(b.dataset.created) - Number(a.dataset.created)
                                );

                                cards.forEach(card => container.appendChild(card));
                            });
                        "
                        type="button"
                        title="Reset Filter"
                        class="w-10 h-10 shrink-0 rounded-lg border border-slate-200 text-slate-500 hover:bg-indigo-50 hover:text-indigo-700 hover:border-indigo-100 transition flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Daftar Tugas --}}
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 px-1">
            <div class="flex items-center gap-2">
                <h3 class="text-base font-bold text-slate-900">Daftar Tugas</h3>

                <span
                    class="px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100 text-[11px] font-bold"
                    x-text="visibleTaskCount + ' Tugas'">
                </span>
            </div>

            <p class="text-xs font-medium text-slate-400">
                Kelola pengumpulan dan penilaian siswa
            </p>
        </div>

        <div
            x-ref="daftarTugas"
            x-effect="
                searchQuery;
                selectedStatus;
                selectedKelas;
                selectedMapel;

                $nextTick(() => {
                    visibleTaskCount = [...$refs.daftarTugas.querySelectorAll('article')]
                        .filter(card => card.getClientRects().length > 0)
                        .length;
                });
            "
            class="space-y-4">

            @forelse ($tugases as $tugas)
            @php
            $totalKumpul = $tugas->pengumpulans_count ?? 0;
            $totalDinilai = $tugas->pengumpulans_dinilai_count ?? 0;
            $belumDinilai = max(0, $totalKumpul - $totalDinilai);

            if ($belumDinilai > 0) {
            $statusFilter = 'perlu_dinilai';
            } elseif ($totalKumpul > 0) {
            $statusFilter = 'selesai';
            } elseif (\Carbon\Carbon::parse($tugas->deadline)->isFuture()) {
            $statusFilter = 'aktif';
            } else {
            $statusFilter = 'selesai';
            }

            $judulTugas = strtolower($tugas->judul ?? '');
            $namaKelas = (string) ($tugas->pengampuKelas?->kelas_id ?? '');
            $namaMapel = (string) ($tugas->pengampuKelas?->guruMapel?->mapel_id ?? '');
            @endphp

            <article
                data-created="{{ $tugas->created_at?->timestamp ?? 0 }}"
                data-deadline="{{ $tugas->deadline ? \Carbon\Carbon::parse($tugas->deadline)->timestamp : 0 }}"
                data-deadline-label="{{ $tugas->deadline ? \Carbon\Carbon::parse($tugas->deadline)->translatedFormat('d M Y') : '' }}"
                x-show="
                        (selectedStatus === 'semua' || selectedStatus === @js($statusFilter)) &&
                        (@js($judulTugas).includes(searchQuery.toLowerCase())) &&
                        (selectedKelas === '' || selectedKelas === @js($namaKelas)) &&
                        (selectedMapel === '' || selectedMapel === @js($namaMapel))
                    "
                class="relative overflow-hidden bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition-all">

                <div class="absolute left-0 top-0 bottom-0 w-1 {{ $totalKumpul > 0 && $totalDinilai == $totalKumpul ? 'bg-emerald-500' : ($belumDinilai > 0 ? 'bg-rose-500' : 'bg-indigo-600') }}"></div>

                <div class="p-4 sm:p-5 pl-5 sm:pl-6 space-y-3">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                        <div class="flex flex-wrap items-center gap-2">

                            @if ($totalKumpul == 0)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 text-[11px] font-bold">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                Belum Ada Pengumpulan
                            </span>

                            @elseif ($totalDinilai == $totalKumpul)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-[11px] font-bold">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                </svg>
                                {{ $totalDinilai }} Selesai Dinilai
                            </span>

                            @elseif ($totalDinilai > 0)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700 text-[11px] font-bold">
                                {{ $totalDinilai }} / {{ $totalKumpul }} Dinilai
                            </span>

                            @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-rose-50 text-rose-700 text-[11px] font-bold">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                Perlu Dinilai
                            </span>
                            @endif

                            <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 text-[11px] font-semibold">
                                {{ $tugas->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? 'Mapel' }}
                            </span>

                            <span class="px-2.5 py-1 rounded-md bg-indigo-50 text-indigo-700 text-[11px] font-semibold">
                                {{ $tugas->pengampuKelas?->kelas?->nama_kelas ?? 'Kelas' }}
                            </span>
                        </div>

                        <div class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500">
                            <svg class="w-4 h-4 {{ $belumDinilai > 0 ? 'text-rose-500' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>Deadline: {{ \Carbon\Carbon::parse($tugas->deadline)->format('d M Y, H:i') }}</span>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <h4 class="text-sm sm:text-base font-bold text-slate-900 leading-snug">{{ $tugas->judul }}</h4>

                        <p class="text-xs sm:text-sm text-slate-500 leading-relaxed line-clamp-2">
                            {{ $tugas->deskripsi ?? 'Tidak ada deskripsi' }}
                        </p>

                        @if ($tugas->file_lampiran)
                        <a
                            href="{{ asset('storage/' . $tugas->file_lampiran) }}"
                            target="_blank"
                            class="inline-flex items-center gap-1.5 text-xs text-indigo-700 font-semibold hover:text-indigo-900 hover:underline pt-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Lihat Berkas Lampiran
                        </a>
                        @endif
                    </div>

                    {{-- Progres Pengumpulan --}}
                    <div class="rounded-xl bg-slate-50 border border-slate-100 p-3">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2">
                            <div class="flex items-center gap-2 text-xs font-semibold text-slate-700">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.196-2.04M17 20H7m10 0v-2c0-.7-.12-1.37-.34-2M7 20H2v-2a3 3 0 015.196-2.04M7 20v-2c0-.7.12-1.37.34-2m0 0a5.002 5.002 0 019.32 0M15 6a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 9a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                Progres Pengumpulan
                            </div>

                            <span class="text-xs text-slate-500">
                                <strong class="text-slate-900">{{ $totalKumpul }}</strong> siswa mengumpulkan

                                @if ($belumDinilai > 0)
                                <span class="text-rose-600 font-semibold">• {{ $belumDinilai }} perlu dinilai</span>
                                @endif
                            </span>
                        </div>

                        <div class="w-full h-2 rounded-full bg-slate-200 overflow-hidden flex">
                            @if ($totalKumpul > 0)
                            <div class="h-full bg-emerald-500" style="width: {{ ($totalDinilai / $totalKumpul) * 100 }}%"></div>
                            <div class="h-full bg-rose-400" style="width: {{ ($belumDinilai / $totalKumpul) * 100 }}%"></div>
                            @else
                            <div class="h-full bg-slate-300 w-full"></div>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-x-4 gap-y-2 mt-2 text-[11px] font-medium text-slate-500">
                            <span class="inline-flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                {{ $totalDinilai }} Selesai dinilai
                            </span>

                            <span class="inline-flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-rose-400"></span>
                                {{ $belumDinilai }} Menunggu penilaian
                            </span>
                        </div>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="flex flex-col sm:flex-row sm:items-center justify-end gap-2 pt-1">
                        <button
                            type="button"
                            @click="$dispatch('open-edit-modal', {
                                    id: {{ $tugas->id }},
                                    judul: {{ Js::from($tugas->judul) }},
                                    deskripsi: {{ Js::from($tugas->deskripsi ?? '') }},
                                    deadline: {{ Js::from(\Carbon\Carbon::parse($tugas->deadline)->format('Y-m-d\TH:i')) }},
                                    pengampu_kelas_id: {{ $tugas->pengampu_kelas_id }},
                                    tipe: {{ Js::from($tugas->tipe) }}
                                })"
                            class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16.862 4.487l1.687-1.688a2.121 2.121 0 113 3l-9.5 9.5a4 4 0 01-1.897 1.13l-3.152.9.9-3.152a4 4 0 011.13-1.897l7.832-7.793z" />
                            </svg>
                            Edit Tugas
                        </button>

                        <a
                            href="{{ route('guru.tugas.show', $tugas->id) }}"
                            class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-lg bg-indigo-800 hover:bg-indigo-900 text-white text-xs font-bold shadow-sm transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7s-8.268-2.943-9.542-7z" />
                            </svg>
                            Lihat Pengumpulan Siswa
                        </a>

                        <form
                            id="delete-form-{{ $tugas->id }}"
                            action="{{ route('guru.tugas.destroy', $tugas->id) }}"
                            method="POST"
                            class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>
                    </div>
                </div>
            </article>

            @empty
            <div class="bg-white rounded-2xl border border-slate-200/80 px-6 py-16 text-center shadow-sm">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-indigo-50 text-indigo-500 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>

                <p class="text-base font-bold text-slate-800">Belum Ada Data Tugas</p>
                <p class="text-xs text-slate-400 mt-1">Tugas yang Anda buat akan ditampilkan di sini.</p>
            </div>
            @endforelse

        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 text-sm text-slate-500">
            Menampilkan <strong class="text-slate-900" x-text="visibleTaskCount"></strong> total tugas
        </div>
    </div>

    {{-- Modal Buat Tugas --}}
    @include('guru.kelola_tugas.create')

    {{-- Modal Edit dan Hapus Tugas --}}
    @include('guru.kelola_tugas.edit')

</div>
@endsection