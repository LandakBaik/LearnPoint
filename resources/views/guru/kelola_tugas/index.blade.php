@extends('layouts.app')

@section('title', 'Daftar Tugas')

@section('content')
<div
    class="space-y-6 text-slate-900"
    x-data="{
        searchQuery: '',
        selectedStatus: 'semua',
        selectedKelas: '',
        selectedMapel: '',
        sortBy: 'terbaru',
        deadlineFilter: false,
        visibleTaskCount: 0
    }">

    {{-- HEADER --}}
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">
                    Daftar Tugas
                </h1>
            </div>

            <p class="mt-1 text-sm text-slate-500">
                Kelola penugasan, pengumpulan, dan penilaian siswa secara terpusat.
            </p>
        </div>

        <a
            href="{{ route('guru.tugas.create') }}"
            class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition duration-200 hover:bg-blue-700 hover:shadow-md active:scale-[0.98]">

            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
            </svg>

            Tambah Tugas
        </a>
    </div>

    {{-- RINGKASAN --}}
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">

        {{-- TOTAL TUGAS --}}
        <button
            type="button"
            @click="
            selectedStatus = 'semua';
            deadlineFilter = false;
        "
            :class="selectedStatus === 'semua' && !deadlineFilter
            ? 'border-blue-400 ring-2 ring-blue-100'
            : 'border-slate-200 hover:border-blue-300'"
            class="w-full rounded-xl border bg-white p-3 text-left shadow-sm transition hover:shadow-md">

            <div class="flex items-center justify-between gap-2">

                {{-- INFORMASI --}}
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        Total Tugas
                    </p>

                    <p class="mt-1 text-xl font-extrabold text-slate-900">
                        {{ $totalTugas ?? 0 }}
                    </p>

                    <p class="mt-1 flex items-center gap-1.5 text-[10px] text-slate-500">
                        <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                        Seluruh penugasan
                    </p>
                </div>

                {{-- ICON KANAN --}}
                <div class="flex shrink-0 flex-col items-center gap-1.5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>

                    <svg class="h-3.5 w-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </div>
        </button>


        {{-- TUGAS AKTIF --}}
        <button
            type="button"
            @click="
            selectedStatus = 'aktif';
            deadlineFilter = false;
        "
            :class="selectedStatus === 'aktif' && !deadlineFilter
            ? 'border-indigo-400 ring-2 ring-indigo-100'
            : 'border-slate-200 hover:border-indigo-300'"
            class="w-full rounded-xl border bg-white p-3 text-left shadow-sm transition hover:shadow-md">

            <div class="flex items-center justify-between gap-2">

                {{-- INFORMASI --}}
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        Tugas Aktif
                    </p>

                    <p class="mt-1 text-xl font-extrabold text-slate-900">
                        {{ $totalAktif ?? 0 }}
                    </p>

                    <p class="mt-1 flex items-center gap-1.5 text-[10px] text-slate-500">
                        <span class="h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
                        Belum melewati deadline
                    </p>
                </div>

                <div class="flex shrink-0 flex-col items-center gap-1.5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>

                    <svg class="h-3.5 w-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </div>
        </button>


        {{-- PERLU DINILAI --}}
        <button
            type="button"
            @click="
            selectedStatus = 'perlu_dinilai';
            deadlineFilter = false;
        "
            :class="selectedStatus === 'perlu_dinilai' && !deadlineFilter
            ? 'border-rose-400 ring-2 ring-rose-100'
            : 'border-slate-200 hover:border-rose-300'"
            class="w-full rounded-xl border bg-white p-3 text-left shadow-sm transition hover:shadow-md">

            <div class="flex items-center justify-between gap-2">

                {{-- INFORMASI --}}
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        Perlu Dinilai
                    </p>

                    <p class="mt-1 text-xl font-extrabold text-slate-900">
                        {{ $perluDinilai ?? 0 }}
                    </p>

                    <p class="mt-1 flex items-center gap-1.5 text-[10px] text-slate-500">
                        <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                        Pengumpulan menunggu nilai
                    </p>
                </div>

                {{-- ICON KANAN --}}
                <div class="flex shrink-0 flex-col items-center gap-1.5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-50 text-rose-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 11-2.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>

                    <svg class="h-3.5 w-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </div>
        </button>


        {{-- DEADLINE TERDEKAT --}}
        <button
            type="button"
            @click="
            deadlineFilter = !deadlineFilter;
            selectedStatus = 'semua';
        "
            :class="deadlineFilter
            ? 'border-amber-400 ring-2 ring-amber-100'
            : 'border-slate-200 hover:border-amber-300'"
            class="w-full rounded-xl border bg-white p-3 text-left shadow-sm transition hover:shadow-md">

            <div class="flex items-center justify-between gap-2">

                {{-- INFORMASI --}}
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        Deadline Terdekat
                    </p>

                    <p
                        class="mt-1 text-xl font-extrabold text-slate-900"
                        x-effect="
                        $nextTick(() => {
                            const sekarang = Math.floor(Date.now() / 1000);
                            const batas = sekarang + 86400;

                            const jumlah = [...$refs.daftarTugas.querySelectorAll('article')]
                                .filter(card => {
                                    const deadline = Number(card.dataset.deadline);

                                    return card.getClientRects().length > 0 &&
                                        deadline >= sekarang &&
                                        deadline <= batas;
                                }).length;

                            $el.textContent = jumlah;
                        });
                    ">
                        {{ $deadlineTerdekat ?? 0 }}
                    </p>

                    <p class="mt-1 flex items-center gap-1.5 text-[10px] text-slate-500">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                        Segera berakhir
                    </p>
                </div>

                {{-- ICON KANAN --}}
                <div class="flex shrink-0 flex-col items-center gap-1.5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>

                    <svg class="h-3.5 w-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </div>
        </button>

    </div>

    {{-- FILTER DAN PENCARIAN --}}
    <div class="space-y-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">

        {{-- FILTER STATUS --}}
        <div class="flex items-center gap-2 overflow-x-auto pb-1">

            <button
                type="button"
                @click="selectedStatus = 'semua'; deadlineFilter = false"
                :class="selectedStatus === 'semua' && !deadlineFilter ? 'bg-blue-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-blue-50 hover:text-blue-700'"
                class="whitespace-nowrap rounded-lg px-3.5 py-2 text-xs font-bold transition">
                Semua Tugas
            </button>

            <button
                type="button"
                @click="selectedStatus = 'aktif'; deadlineFilter = false"
                :class="selectedStatus === 'aktif' && !deadlineFilter ? 'bg-blue-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-blue-50 hover:text-blue-700'"
                class="whitespace-nowrap rounded-lg px-3.5 py-2 text-xs font-semibold transition">
                Tugas Aktif
            </button>

            <button
                type="button"
                @click="selectedStatus = 'perlu_dinilai'; deadlineFilter = false"
                :class="selectedStatus === 'perlu_dinilai' && !deadlineFilter ? 'bg-blue-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-blue-50 hover:text-blue-700'"
                class="whitespace-nowrap rounded-lg px-3.5 py-2 text-xs font-semibold transition">
                Perlu Dinilai
            </button>

            <button
                type="button"
                @click="selectedStatus = 'selesai'; deadlineFilter = false"
                :class="selectedStatus === 'selesai' && !deadlineFilter ? 'bg-blue-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-blue-50 hover:text-blue-700'"
                class="whitespace-nowrap rounded-lg px-3.5 py-2 text-xs font-semibold transition">
                Riwayat / Selesai
            </button>
        </div>

        {{-- PENCARIAN DAN FILTER --}}
        <div class="flex flex-col items-center gap-3 md:flex-row">

            {{-- PENCARIAN --}}
            <div class="group relative w-full min-w-0 flex-1">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>

                <input
                    x-model="searchQuery"
                    type="text"
                    placeholder="Cari tugas berdasarkan judul..."
                    class="h-10 w-full rounded-lg border border-slate-200 bg-slate-50 pl-10 pr-4 text-xs font-medium text-slate-700 placeholder:text-slate-400 transition focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-100">
            </div>

            {{-- FILTER KELAS, MAPEL, DAN SORTING --}}
            <div class="flex w-full shrink-0 items-center justify-end gap-3 md:w-auto">

                <select
                    x-model="selectedKelas"
                    class="h-10 w-full cursor-pointer rounded-lg border border-slate-200 bg-slate-50 px-3 text-xs font-semibold text-slate-600 focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-100">

                    <option value="">Semua Kelas</option>

                    @foreach($kelases as $kelas)
                    <option value="{{ $kelas->id }}">
                        {{ $kelas->nama_kelas }}
                    </option>
                    @endforeach
                </select>

                <select
                    x-model="selectedMapel"
                    class="h-10 w-full cursor-pointer rounded-lg border border-slate-200 bg-slate-50 px-3 text-xs font-semibold text-slate-600 focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-100">

                    <option value="">Semua Mata Pelajaran</option>

                    @foreach($mapels as $mapel)
                    <option value="{{ $mapel->id }}">
                        {{ $mapel->nama_mapel }}
                    </option>
                    @endforeach
                </select>

                <div class="flex gap-2">

                    <select
                        x-model="sortBy"
                        @change="
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
                        "
                        class="h-10 min-w-0 flex-1 cursor-pointer rounded-lg border border-slate-200 bg-slate-50 px-3 text-xs font-semibold text-slate-600 focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-100">

                        <option value="terbaru">Terbaru</option>
                        <option value="terlama">Terlama</option>
                        <option value="deadline">Deadline Terdekat</option>
                    </select>

                    {{-- RESET FILTER --}}
                    <button
                        @click="
                            searchQuery = '';
                            selectedKelas = '';
                            selectedMapel = '';
                            selectedStatus = 'semua';
                            deadlineFilter = false;
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
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-indigo-100 hover:bg-indigo-50 hover:text-indigo-700">

                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </button>

                </div>
            </div>
        </div>
    </div>

    {{-- DAFTAR TUGAS --}}
    <div class="space-y-4">

        {{-- HEADER DAFTAR --}}
        <div class="flex flex-col justify-between gap-2 px-1 sm:flex-row sm:items-center">
            <div class="flex items-center gap-2">
                <h3 class="text-base font-bold text-slate-900">
                    Daftar Tugas
                </h3>

                {{-- JUMLAH TUGAS SESUAI FILTER --}}
                <span
                    class="rounded-full border border-indigo-100 bg-indigo-50 px-2.5 py-1 text-[11px] font-bold text-indigo-700"
                    x-text="visibleTaskCount + ' Tugas'">
                </span>
            </div>

            <p class="text-xs font-medium text-slate-400">
                Kelola pengumpulan dan penilaian siswa
            </p>
        </div>

        {{-- CONTAINER CARD TUGAS --}}
        <div
            x-ref="daftarTugas"

            {{-- MEMANTAU SEMUA FILTER --}}
            x-effect="
            searchQuery;
            selectedStatus;
            selectedKelas;
            selectedMapel;
            deadlineFilter;

            $nextTick(() => {
                visibleTaskCount = [...$refs.daftarTugas.querySelectorAll('article')]
                    .filter(card =>
                        window.getComputedStyle(card).display !== 'none'
                    )
                    .length;
            });
        "

            class="space-y-4">

            @forelse ($tugases as $tugas)

            @php
            $totalKumpul = $tugas->pengumpulans_count ?? 0;
            $totalDinilai = $tugas->pengumpulans_dinilai_count ?? 0;
            $belumDinilai = max(0, $totalKumpul - $totalDinilai);

            // Aktif: deadline belum lewat, meskipun sudah ada pengumpulan
            $statusDeadline = $tugas->deadline
                ? \Carbon\Carbon::parse($tugas->deadline)->isFuture()
                : false;

            // Perlu dinilai: masih ada pengumpulan yang belum dinilai
            $statusPenilaian = $belumDinilai > 0;

            // Selesai: deadline sudah lewat dan tidak ada pengumpulan yang menunggu nilai
            $statusSelesai = !$statusDeadline && !$statusPenilaian;

            $judulTugas = strtolower($tugas->judul ?? '');

            $kelasIds = $tugas->pengampuKelas
            ? [(string) $tugas->pengampuKelas->kelas_id]
            : [];

            $namaMapel = (string) (
            $tugas->pengampuKelas?->guruMapel?->mapel_id ?? ''
            );

            $mapelLabel = $tugas->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? 'Mapel';
            @endphp

            {{-- CARD TUGAS --}}
            <article
                data-created="{{ $tugas->created_at?->timestamp ?? 0 }}"
                data-deadline="{{ $tugas->deadline ? \Carbon\Carbon::parse($tugas->deadline)->timestamp : 0 }}"

                x-show="
                (
                    selectedStatus === 'semua' ||
                    (selectedStatus === 'aktif' && @js($statusDeadline)) ||
                    (selectedStatus === 'perlu_dinilai' && @js($statusPenilaian)) ||
                    (selectedStatus === 'selesai' && @js($statusSelesai))
                ) &&
                (@js($judulTugas).includes(searchQuery.toLowerCase())) &&
                (selectedKelas === '' || @js($kelasIds).includes(selectedKelas)) &&
                (selectedMapel === '' || selectedMapel === @js($namaMapel)) &&
                (!deadlineFilter || (
                    Number($el.dataset.deadline) >= Math.floor(Date.now() / 1000) &&
                    Number($el.dataset.deadline) <= Math.floor(Date.now() / 1000) + 86400
                ))
            "

                class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm transition-all hover:shadow-md">

                {{-- INDIKATOR STATUS --}}
                <div class="absolute bottom-0 left-0 top-0 w-1
                {{ $totalKumpul > 0 && $totalDinilai == $totalKumpul
                    ? 'bg-emerald-500'
                    : ($belumDinilai > 0 ? 'bg-rose-500' : 'bg-blue-600') }}">
                </div>

                <div class="space-y-3 p-4 pl-5 sm:p-5 sm:pl-6">

                    {{-- STATUS DAN DEADLINE --}}
                    <div class="flex flex-col justify-between gap-3 lg:flex-row lg:items-center">

                        <div class="flex flex-wrap items-center gap-2">

                            {{-- STATUS PENGUMPULAN --}}
                            @if ($totalKumpul == 0)

                            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-600">
                                <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                Belum Ada Pengumpulan
                            </span>

                            @elseif ($totalDinilai == $totalKumpul)

                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                </svg>
                                {{ $totalDinilai }} Selesai Dinilai
                            </span>

                            @elseif ($totalDinilai > 0)

                            <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-2.5 py-1 text-[11px] font-bold text-indigo-700">
                                {{ $totalDinilai }} / {{ $totalKumpul }} Dinilai
                            </span>

                            @else

                            <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-2.5 py-1 text-[11px] font-bold text-rose-700">
                                <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                                Perlu Dinilai
                            </span>

                            @endif

                            {{-- TIPE TUGAS --}}
                            <span class="rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-semibold text-blue-700">
                                {{ $tugas->tipe === 'upload' ? 'Upload File' : 'Pilihan Ganda' }}
                            </span>

                            {{-- MATA PELAJARAN --}}
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-600">
                                {{ $mapelLabel }}
                            </span>

                            {{-- KELAS --}}
                            @if ($tugas->pengampuKelas)
                            <span class="rounded-md bg-indigo-50 px-2.5 py-1 text-[11px] font-semibold text-indigo-700">
                                {{ $tugas->pengampuKelas->kelas?->nama_kelas ?? 'Kelas' }}
                            </span>
                            @else
                            <span class="rounded-md bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-500">
                                Belum ada kelas
                            </span>
                            @endif

                        </div>

                        {{-- DEADLINE --}}
                        <div class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500">
                            <svg class="h-4 w-4 {{ $belumDinilai > 0 ? 'text-rose-500' : 'text-slate-400' }}"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>

                            <span>
                                Deadline:
                                {{ \Carbon\Carbon::parse($tugas->deadline)->locale('id')->translatedFormat('d M Y, H:i') }}
                            </span>
                        </div>

                    </div>

                    {{-- JUDUL DAN DESKRIPSI --}}
                    <div class="space-y-1">

                        <h4 class="text-sm font-bold leading-snug text-slate-900 sm:text-base">
                            {{ $tugas->judul }}
                        </h4>

                        <p class="line-clamp-2 text-xs leading-relaxed text-slate-500 sm:text-sm">
                            {{ $tugas->deskripsi ?? 'Tidak ada deskripsi' }}
                        </p>

                        {{-- LAMPIRAN --}}
                        @if ($tugas->file_lampiran)
                        <a
                            href="{{ route('file.tugas', $tugas->id) }}"
                            target="_blank"
                            class="inline-flex items-center gap-1.5 pt-1 text-xs font-semibold text-indigo-700 hover:text-indigo-900 hover:underline">

                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2  0 012-2h5.586a1 1  0 01.707.293l5.414 5.414a1 1  0 01.293.707V19a2 2  0 01-2 2z" />
                            </svg>

                            Lihat Berkas Lampiran
                        </a>
                        @endif

                    </div>

                    {{-- PROGRES PENGUMPULAN --}}
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-3">

                        <div class="mb-2 flex flex-col justify-between gap-2 sm:flex-row sm:items-center">

                            <div class="flex items-center gap-2 text-xs font-semibold text-slate-700">
                                <svg class="h-4 w-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M17 20h5v-2a3 3 0 00-5.196-2.04M17 20H7m10 0v-2c0-.7-.12-1.37-.34-2M7 20H2v-2a3 3  0 015.196-2.04M7 20v-2c0-.7.12-1.37.34-2m0 0a5.002 5.002  0 019.32 0M15 6a3 3  0 11-6 0 3 3  0 016 0zm6 3a2 2  0 11-4 0 2 2  0 012 0zM7 9a2 2  0 11-4 0 2 2  0 014 0z" />
                                </svg>
                                Progres Pengumpulan
                            </div>

                            <span class="text-xs text-slate-500">
                                <strong class="text-slate-900">{{ $totalKumpul }}</strong>
                                siswa mengumpulkan

                                @if ($belumDinilai > 0)
                                <span class="font-semibold text-rose-600">
                                    • {{ $belumDinilai }} perlu dinilai
                                </span>
                                @endif
                            </span>
                        </div>

                        {{-- PROGRESS BAR --}}
                        <div class="flex h-2 w-full overflow-hidden rounded-full bg-slate-200">

                            @if ($totalKumpul > 0)

                            <div
                                class="h-full bg-emerald-500"
                                style="width: {{ ($totalDinilai / $totalKumpul) * 100 }}%">
                            </div>

                            <div
                                class="h-full bg-rose-400"
                                style="width: {{ ($belumDinilai / $totalKumpul) * 100 }}%">
                            </div>

                            @else

                            <div class="h-full w-full bg-slate-300"></div>

                            @endif
                        </div>

                        {{-- KETERANGAN PROGRES --}}
                        <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-2 text-[11px] font-medium text-slate-500">

                            <span class="inline-flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                {{ $totalDinilai }} Selesai dinilai
                            </span>

                            <span class="inline-flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full bg-rose-400"></span>
                                {{ $belumDinilai }} Menunggu penilaian
                            </span>
                        </div>

                    </div>

                    {{-- TOMBOL AKSI --}}
                    <div class="flex flex-col justify-end gap-2 pt-1 sm:flex-row sm:items-center">

                        {{-- EDIT TUGAS --}}
                        <a
                            href="{{ route('guru.tugas.edit', $tugas->id) }}"
                            class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-slate-100 px-3.5 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-200">

                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M16.862 4.487l1.687-1.688a2.121 2.121 0 113 3l-9.5 9.5a4 4  0 01-1.897 1.13l-3.152.9.9-3.152a4 4  0 011.13-1.897l7.832-7.793z" />
                            </svg>

                            Edit Tugas
                        </a>

                        {{-- LIHAT PENGUMPULAN --}}
                        <a
                            href="{{ route('guru.tugas.show', $tugas->id) }}"
                            class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-blue-700">

                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M15 12a3 3  0 11-6 0 3 3  0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7s-8.268-2.943-9.542-7z" />
                            </svg>

                            Lihat Pengumpulan Siswa
                        </a>

                        {{-- FORM HAPUS --}}
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

            {{-- EMPTY STATE --}}
            <div class="rounded-2xl border border-slate-200/80 bg-white px-6 py-16 text-center shadow-sm">

                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-500">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 5H7a2 2  0 00-2 2v12a2 2  0 002 2h10a2 2  0 002-2V7a2 2  0 00-2-2h-2M9 5a2 2  0 002 2h2a2 2  0 002-2M9 5a2 2  0 012-2h2a2 2  0 al2 2m-6 9l2 2 4-4" />
                    </svg>
                </div>

                <p class="text-base font-bold text-slate-800">
                    Belum Ada Data Tugas
                </p>

                <p class="mt-1 text-xs text-slate-400">
                    Tugas yang Anda buat akan ditampilkan di sini.
                </p>
            </div>

            @endforelse

        </div>

        {{-- JUMLAH TUGAS --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 text-sm text-slate-500 shadow-sm">
            Menampilkan
            <strong class="text-slate-900" x-text="visibleTaskCount"></strong>
            tugas
        </div>

    </div>

</div>
@endsection