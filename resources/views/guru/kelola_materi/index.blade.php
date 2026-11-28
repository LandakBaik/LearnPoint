@extends('layouts.app')

@section('title', 'Materi Pembelajaran')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">

    <!-- Header Title & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-start sm:items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center shrink-0 shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Materi Pembelajaran</h1>
                <p class="text-sm text-slate-500 mt-0.5">Kelola dan bagikan modul pembelajaran serta referensi video untuk siswa.</p>
            </div>
        </div>
        <a
            href="{{ route('guru.materi.create') }}"
            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 text-white font-semibold text-sm hover:bg-blue-700 active:scale-[0.98] transition-all shadow-sm hover:shadow-md cursor-pointer shrink-0"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            <span>Tambah Materi</span>
        </a>
    </div>

    <!-- Stats Summary Cards -->
    <section class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Card 1: Total Materi -->
        <div class="group rounded-2xl p-4 sm:p-5 bg-white border border-slate-200/80 hover:border-emerald-200 shadow-sm hover:shadow-md transition-all duration-200">
            <span class="text-[11px] font-bold uppercase tracking-wider flex items-center gap-1.5 text-slate-500">
                Total Materi
            </span>
            <div class="mt-2.5 flex items-center justify-between gap-2">
                <div class="text-2xl font-extrabold text-slate-900 leading-tight">{{ $materiGroups->count() }}</div>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200/60 shadow-sm flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
            </div>
            <p class="mt-2.5 pt-2 border-t border-slate-100 text-xs text-slate-400 truncate">Grup materi aktif</p>
        </div>

        <!-- Card 2: File Materi -->
        <div class="group rounded-2xl p-4 sm:p-5 bg-white border border-slate-200/80 hover:border-blue-200 shadow-sm hover:shadow-md transition-all duration-200">
            <span class="text-[11px] font-bold uppercase tracking-wider flex items-center gap-1.5 text-slate-500">
                Dokumen Terunggah
            </span>
            <div class="mt-2.5 flex items-center justify-between gap-2">
                <div class="text-2xl font-extrabold text-slate-900 leading-tight">{{ $materiGroups->count() }}</div>
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 border border-blue-100/80 shadow-sm flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V7.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 1H7a2 2 0 00-2 2v16a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <p class="mt-2.5 pt-2 border-t border-slate-100 text-xs text-slate-400 truncate">Berkas PDF / PPT</p>
        </div>

        <!-- Card 3: Video Interaktif -->
        <div class="group rounded-2xl p-4 sm:p-5 bg-white border border-slate-200/80 hover:border-amber-200 shadow-sm hover:shadow-md transition-all duration-200">
            <span class="text-[11px] font-bold uppercase tracking-wider flex items-center gap-1.5 text-slate-500">
                Video Interaktif
            </span>
            <div class="mt-2.5 flex items-center justify-between gap-2">
                <div class="text-2xl font-extrabold text-slate-900 leading-tight">{{ $materiGroups->filter(fn ($group) => filled($group->first()?->url_youtube))->count() }}</div>
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 border border-amber-200/60 shadow-sm flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p class="mt-2.5 pt-2 border-t border-slate-100 text-xs text-slate-400 truncate">Tautan video YouTube</p>
        </div>
    </section>

    <!-- Section Filter Mapel -->
    <div class="space-y-2.5">
        <div class="flex items-center justify-between px-1">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                Pilih Mata Pelajaran
            </h3>
            <span class="text-xs text-slate-400 font-medium">{{ $mapels->count() }} Mata Pelajaran Diampu</span>
        </div>

        @php
            $selectedMapelId = $filters['mapel_id'] ?? '';
            $colorSchemes = [
                ['active' => 'bg-gradient-to-br from-blue-600 to-indigo-600 text-white shadow-md shadow-blue-500/20 border-blue-600', 'inactive' => 'bg-white text-slate-700 border-slate-200/80 hover:bg-blue-50/60 hover:border-blue-300 hover:text-blue-700', 'iconBg' => 'bg-blue-50 text-blue-600 border border-blue-100'],
                ['active' => 'bg-gradient-to-br from-emerald-600 to-teal-600 text-white shadow-md shadow-emerald-500/20 border-emerald-600', 'inactive' => 'bg-white text-slate-700 border-slate-200/80 hover:bg-emerald-50/60 hover:border-emerald-300 hover:text-emerald-700', 'iconBg' => 'bg-emerald-50 text-emerald-600 border border-emerald-100'],
                ['active' => 'bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-md shadow-amber-500/20 border-amber-500', 'inactive' => 'bg-white text-slate-700 border-slate-200/80 hover:bg-amber-50/60 hover:border-amber-300 hover:text-amber-700', 'iconBg' => 'bg-amber-50 text-amber-600 border border-amber-100'],
                ['active' => 'bg-gradient-to-br from-indigo-600 to-violet-600 text-white shadow-md shadow-indigo-500/20 border-indigo-600', 'inactive' => 'bg-white text-slate-700 border-slate-200/80 hover:bg-indigo-50/60 hover:border-indigo-300 hover:text-indigo-700', 'iconBg' => 'bg-indigo-50 text-indigo-600 border border-indigo-100'],
                ['active' => 'bg-gradient-to-br from-rose-600 to-pink-600 text-white shadow-md shadow-rose-500/20 border-rose-600', 'inactive' => 'bg-white text-slate-700 border-slate-200/80 hover:bg-rose-50/60 hover:border-rose-300 hover:text-rose-700', 'iconBg' => 'bg-rose-50 text-rose-600 border border-rose-100'],
                ['active' => 'bg-gradient-to-br from-sky-600 to-blue-600 text-white shadow-md shadow-sky-500/20 border-sky-600', 'inactive' => 'bg-white text-slate-700 border-slate-200/80 hover:bg-sky-50/60 hover:border-sky-300 hover:text-sky-700', 'iconBg' => 'bg-sky-50 text-sky-600 border border-sky-100'],
            ];
        @endphp

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-6 gap-3">
            {{-- Tombol: Semua Mapel --}}
            <button
                type="button"
                onclick="setMapelFilter('')"
                class="group relative flex items-center gap-3 p-3.5 rounded-2xl border transition-all duration-200 cursor-pointer text-left font-semibold text-xs shadow-sm {{ empty($selectedMapelId) ? 'bg-slate-900 text-white shadow-md shadow-slate-900/20 border-slate-900' : 'bg-white text-slate-700 border-slate-200/80 hover:bg-slate-50 hover:border-slate-300' }}"
            >
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 transition-colors {{ empty($selectedMapelId) ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600 group-hover:bg-slate-200' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate font-bold leading-tight">Semua Mapel</p>
                    <p class="text-[10px] mt-0.5 opacity-80 font-normal">Tampilkan semua</p>
                </div>
            </button>

            {{-- Tombol per Mapel yang diampu Guru --}}
            @foreach($mapels as $index => $mapel)
                @php
                    $isSelected = (string)$selectedMapelId === (string)$mapel->id;
                    $scheme = $colorSchemes[$index % count($colorSchemes)];
                @endphp
                <button
                    type="button"
                    onclick="setMapelFilter('{{ $mapel->id }}')"
                    class="group relative flex items-center gap-3 p-3.5 rounded-2xl border transition-all duration-200 cursor-pointer text-left font-semibold text-xs shadow-sm {{ $isSelected ? $scheme['active'] : $scheme['inactive'] }}"
                >
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 transition-colors {{ $isSelected ? 'bg-white/20 text-white' : $scheme['iconBg'] }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate font-bold leading-tight">{{ $mapel->nama_mapel }}</p>
                        <p class="text-[10px] mt-0.5 opacity-80 font-normal">Mapel diampu</p>
                    </div>
                </button>
            @endforeach
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 shadow-sm">
        <form id="materiFilterForm" method="GET" action="{{ route('guru.materi') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3">
            <input type="hidden" name="mapel_id" id="filter_mapel_id" value="{{ $filters['mapel_id'] ?? '' }}">

            <div class="relative lg:col-span-4">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input
                    type="search"
                    name="search"
                    value="{{ $filters['search'] ?? '' }}"
                    placeholder="Cari judul materi..."
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition"
                >
            </div>

            <div class="lg:col-span-2">
                <select name="tingkatan" id="filter_tingkatan" onchange="onTingkatanChange()" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 text-sm text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition cursor-pointer">
                    <option value="">Semua Tingkatan</option>
                    @foreach($tingkatanOptions as $tingkatan)
                        <option value="{{ $tingkatan }}" @selected(($filters['tingkatan'] ?? '') == $tingkatan)>Tingkat {{ $tingkatan }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2">
                <select name="kelas_id" id="filter_kelas_id" onchange="document.getElementById('materiFilterForm').submit()" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 text-sm text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition cursor-pointer">
                    <option value="">Semua Kelas</option>
                    @foreach($kelasOptions as $kelas)
                        <option value="{{ $kelas->id }}" @selected(($filters['kelas_id'] ?? '') == $kelas->id)>{{ $kelas->nama_kelas }} (Tingkat {{ $kelas->tingkatan }})</option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2">
                <select name="sort" id="filter_sort" onchange="document.getElementById('materiFilterForm').submit()" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 text-sm text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition cursor-pointer">
                    <option value="terbaru" @selected(($filters['sort'] ?? 'terbaru') === 'terbaru')>Terbaru</option>
                    <option value="terlama" @selected(($filters['sort'] ?? '') === 'terlama')>Terlama</option>
                    <option value="judul_asc" @selected(($filters['sort'] ?? '') === 'judul_asc')>Judul (A - Z)</option>
                    <option value="judul_desc" @selected(($filters['sort'] ?? '') === 'judul_desc')>Judul (Z - A)</option>
                </select>
            </div>

            <div class="lg:col-span-2 flex items-center gap-2">
                <button type="submit" class="flex-1 py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold active:scale-[0.98] transition shadow-sm cursor-pointer inline-flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    <span>Filter</span>
                </button>
                @if(request()->hasAny(['search', 'mapel_id', 'tingkatan', 'kelas_id', 'sort']))
                    <a href="{{ route('guru.materi') }}" title="Reset Filter" class="p-2.5 border border-slate-200 rounded-xl text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition shrink-0 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <script>
        function setMapelFilter(mapelId) {
            document.getElementById('filter_mapel_id').value = mapelId;
            document.getElementById('filter_tingkatan').value = '';
            document.getElementById('filter_kelas_id').value = '';
            document.getElementById('materiFilterForm').submit();
        }

        function onTingkatanChange() {
            document.getElementById('filter_kelas_id').value = '';
            document.getElementById('materiFilterForm').submit();
        }
    </script>

    <!-- Data Table Container -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between gap-3 bg-white">
            <div class="flex items-center gap-2.5">
                <h3 class="text-sm font-bold text-slate-900">Daftar Materi</h3>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                    {{ $materiGroups->count() }} Grup
                </span>
            </div>
            <p class="text-xs text-slate-400 hidden sm:block">Materi dikelompokkan berdasarkan unggahan ke kelas sasaran</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-200/60 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-6">Materi Pembelajaran</th>
                        <th class="py-3.5 px-6">Mata Pelajaran &amp; Kelas</th>
                        <th class="py-3.5 px-6">Format / Lampiran</th>
                        <th class="py-3.5 px-6">Tanggal Unggah</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($materiGroups as $group)
                        @php
                            $materi = $group->first();
                            $mapelNama = $materi->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? 'Mata Pelajaran';
                            $kelasGrup = $group
                                ->map(fn ($item) => $item->pengampuKelas?->kelas)
                                ->filter()
                                ->unique('id')
                                ->sortBy('nama_kelas');
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-4 px-6">
                                <div class="flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-slate-900 leading-snug">{{ $materi->judul }}</p>
                                        @if($materi->deskripsi)
                                            <p class="text-xs text-slate-500 mt-1 line-clamp-2">{{ $materi->deskripsi }}</p>
                                        @endif
                                        <p class="text-[11px] text-slate-400 mt-1">{{ $group->count() }} kelas dalam grup</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <p class="text-xs font-bold text-slate-800">{{ $mapelNama }}</p>
                                <div class="flex flex-wrap gap-1.5 mt-1.5">
                                    @foreach($kelasGrup as $kelas)
                                        <span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 border border-blue-100 text-[11px] font-semibold">
                                            {{ $kelas->nama_kelas }} &middot; Tingkat {{ $kelas->tingkatan }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex flex-col items-start gap-1.5">
                                    <a href="{{ route('file.materi', $materi->id) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 border border-blue-100 hover:bg-blue-100 text-xs font-semibold transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V7.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 1H7a2 2 0 00-2 2v16a2 2 0 002 2z"/></svg>
                                        Buka File
                                    </a>
                                    @if($materi->url_youtube)
                                        <a href="{{ $materi->url_youtube }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 border border-rose-100 hover:bg-rose-100 text-xs font-semibold transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Video YouTube
                                        </a>
                                    @endif
                                </div>
                            </td>
                            <td class="py-4 px-6 text-xs text-slate-500 whitespace-nowrap">{{ $materi->created_at?->format('d M Y') }}</td>
                            <td class="py-4 px-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('guru.materi.edit', $materi->id_grub_materi) }}"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 bg-white text-slate-700 text-xs font-semibold hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        Edit
                                    </a>

                                    <form action="{{ route('guru.materi.destroy', $materi->id_grub_materi) }}" method="POST"
                                        data-confirm="Apakah Anda yakin ingin menghapus materi '{{ addslashes($materi->judul) }}' dari seluruh {{ $group->count() }} kelas? Tindakan ini tidak dapat dibatalkan.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-rose-200 bg-rose-50/70 text-rose-600 text-xs font-semibold hover:bg-rose-100 transition-colors cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-6">
                                <div class="py-12 px-4 text-center bg-slate-50/60 rounded-xl border border-dashed border-slate-200">
                                    <div class="w-12 h-12 rounded-xl bg-white text-emerald-600 border border-slate-200/80 mx-auto mb-3 flex items-center justify-center shadow-sm">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <p class="text-sm font-bold text-slate-800">Belum Ada Data Materi</p>
                                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Data materi pembelajaran yang telah diunggah akan muncul di sini.</p>
                                    <a href="{{ route('guru.materi.create') }}" class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700 transition shadow-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                        Tambah Materi Pertama
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 border-t border-slate-100 bg-white flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-xs text-slate-500">
                Menampilkan <span class="font-bold text-slate-900">{{ $materiGroups->count() }}</span> grup materi
            </p>
        </div>
    </div>

</div>
@endsection
