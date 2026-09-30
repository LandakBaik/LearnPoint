@extends('layouts.app')

@section('title', 'Materi Pembelajaran')

@section('content')
<div class="space-y-6">

    <!-- Header Title & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Materi Pembelajaran</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola dan bagikan materi pembelajaran untuk siswa.</p>
        </div>
        <div>
            <a
                href="{{ route('guru.materi.create') }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 text-white font-semibold text-sm hover:bg-blue-700 transition-colors shadow-sm cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Tambah Materi
            </a>
        </div>
    </div>

    <!-- Stats Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Card 1: Total Materi -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500">Total Materi Tersedia</p>
                <h3 class="text-2xl font-extrabold text-gray-900 mt-0.5">{{ $materiGroups->count() }} Grup</h3>
            </div>
        </div>

        <!-- Card 2: Format PDF -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-red-50 text-red-500 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V7.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 1H7a2 2 0 00-2 2v16a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500">Berkas Format PDF</p>
                <h3 class="text-2xl font-extrabold text-gray-900 mt-0.5">{{ $materiGroups->count() }} Berkas</h3>
            </div>
        </div>

        <!-- Card 3: Video Interaktif -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500">Video Interaktif</p>
                <h3 class="text-2xl font-extrabold text-gray-900 mt-0.5">{{ $materiGroups->filter(fn ($group) => filled($group->first()?->url_youtube))->count() }} Video</h3>
            </div>
        </div>
    </div>

    <!-- Section Filter Mapel (Kolom Pilihan Warna-Warni Mapel Diampu Guru) -->
    <div class="space-y-2">
        <div class="flex items-center justify-between px-1">
            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                Pilih Mata Pelajaran
            </h3>
            <span class="text-[11px] text-gray-400 font-medium">{{ $mapels->count() }} Mata Pelajaran Diampu</span>
        </div>

        @php
            $selectedMapelId = $filters['mapel_id'] ?? '';
            $colorSchemes = [
                ['active' => 'bg-gradient-to-br from-indigo-600 to-blue-600 text-white shadow-md shadow-indigo-500/20 border-indigo-600', 'inactive' => 'bg-white text-gray-700 border-gray-200 hover:bg-indigo-50/60 hover:border-indigo-300 hover:text-indigo-700', 'iconBg' => 'bg-indigo-100 text-indigo-600'],
                ['active' => 'bg-gradient-to-br from-emerald-600 to-teal-600 text-white shadow-md shadow-emerald-500/20 border-emerald-600', 'inactive' => 'bg-white text-gray-700 border-gray-200 hover:bg-emerald-50/60 hover:border-emerald-300 hover:text-emerald-700', 'iconBg' => 'bg-emerald-100 text-emerald-600'],
                ['active' => 'bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-md shadow-amber-500/20 border-amber-500', 'inactive' => 'bg-white text-gray-700 border-gray-200 hover:bg-amber-50/60 hover:border-amber-300 hover:text-amber-700', 'iconBg' => 'bg-amber-100 text-amber-600'],
                ['active' => 'bg-gradient-to-br from-purple-600 to-pink-600 text-white shadow-md shadow-purple-500/20 border-purple-600', 'inactive' => 'bg-white text-gray-700 border-gray-200 hover:bg-purple-50/60 hover:border-purple-300 hover:text-purple-700', 'iconBg' => 'bg-purple-100 text-purple-600'],
                ['active' => 'bg-gradient-to-br from-rose-600 to-red-600 text-white shadow-md shadow-rose-500/20 border-rose-600', 'inactive' => 'bg-white text-gray-700 border-gray-200 hover:bg-rose-50/60 hover:border-rose-300 hover:text-rose-700', 'iconBg' => 'bg-rose-100 text-rose-600'],
                ['active' => 'bg-gradient-to-br from-cyan-600 to-blue-600 text-white shadow-md shadow-cyan-500/20 border-cyan-600', 'inactive' => 'bg-white text-gray-700 border-gray-200 hover:bg-cyan-50/60 hover:border-cyan-300 hover:text-cyan-700', 'iconBg' => 'bg-cyan-100 text-cyan-600'],
            ];
        @endphp

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-6 gap-3">
            {{-- Tombol: Semua Mapel --}}
            <button
                type="button"
                onclick="setMapelFilter('')"
                class="group relative flex items-center gap-3 p-3.5 rounded-2xl border transition-all duration-200 cursor-pointer text-left font-semibold text-xs {{ empty($selectedMapelId) ? 'bg-gray-900 text-white shadow-md shadow-gray-900/20 border-gray-900' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50 hover:border-gray-300' }}"
            >
                <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 transition-colors {{ empty($selectedMapelId) ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-gray-200' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate font-bold leading-tight">Semua Mapel</p>
                    <p class="text-[10px] mt-0.5 opacity-80 font-normal">Tampilkan Semua</p>
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
                    class="group relative flex items-center gap-3 p-3.5 rounded-2xl border transition-all duration-200 cursor-pointer text-left font-semibold text-xs {{ $isSelected ? $scheme['active'] : $scheme['inactive'] }}"
                >
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 transition-colors {{ $isSelected ? 'bg-white/20 text-white' : $scheme['iconBg'] }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate font-bold leading-tight">{{ $mapel->nama_mapel }}</p>
                        <p class="text-[10px] mt-0.5 opacity-80 font-normal">Mapel Diampu</p>
                    </div>
                </button>
            @endforeach
        </div>
    </div>

    <!-- Filter Bar Tambahan (Search, Tingkatan, Kelas) -->
    <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm">
        <form id="materiFilterForm" method="GET" action="{{ route('guru.materi') }}" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3">
            <input type="hidden" name="mapel_id" id="filter_mapel_id" value="{{ $filters['mapel_id'] ?? '' }}">

            <div class="relative sm:col-span-2">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input
                    type="search"
                    name="search"
                    value="{{ $filters['search'] ?? '' }}"
                    placeholder="Cari judul materi..."
                    class="w-full pl-10 pr-4 py-2 rounded-full border border-gray-200 bg-gray-50/50 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all placeholder-gray-400"
                >
            </div>

            <select name="tingkatan" onchange="document.getElementById('materiFilterForm').submit()" class="rounded-full border border-gray-200 px-4 py-2 text-xs font-medium bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                <option value="">Semua Tingkatan</option>
                @foreach($tingkatanOptions as $tingkatan)
                    <option value="{{ $tingkatan }}" @selected(($filters['tingkatan'] ?? '') == $tingkatan)>Tingkat {{ $tingkatan }}</option>
                @endforeach
            </select>

            <select name="kelas_id" onchange="document.getElementById('materiFilterForm').submit()" class="rounded-full border border-gray-200 px-4 py-2 text-xs font-medium bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                <option value="">Semua Kelas</option>
                @foreach($kelasOptions as $kelas)
                    <option value="{{ $kelas->id }}" @selected(($filters['kelas_id'] ?? '') == $kelas->id)>{{ $kelas->nama_kelas }} (Tingkat {{ $kelas->tingkatan }})</option>
                @endforeach
            </select>

            <div class="sm:col-span-2 xl:col-span-4 flex items-center justify-end gap-2 pt-1 border-t border-gray-100">
                <a href="{{ route('guru.materi') }}" class="px-4 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-100 rounded-full transition-colors">Reset Filter</a>
                <button type="submit" class="px-5 py-2 bg-gray-800 hover:bg-gray-900 text-white rounded-full text-xs font-semibold transition-colors">Cari Materi</button>
            </div>
        </form>
    </div>

    <script>
        function setMapelFilter(mapelId) {
            document.getElementById('filter_mapel_id').value = mapelId;
            document.getElementById('materiFilterForm').submit();
        }
    </script>

    <!-- Data Table Container -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-200 text-xs font-bold text-gray-500 uppercase tracking-wider">
                        <th class="py-4 px-6">Materi Pembelajaran</th>
                        <th class="py-4 px-6">Mata Pelajaran & Kelas</th>
                        <th class="py-4 px-6">Format / Jenis</th>
                        <th class="py-4 px-6">Tanggal Unggah</th>
                        <th class="py-4 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
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
                        <tr class="hover:bg-gray-50/60">
                            <td class="py-4 px-6">
                                <p class="font-bold text-gray-900">{{ $materi->judul }}</p>
                                @if($materi->deskripsi)
                                    <p class="text-xs text-gray-500 mt-1">{{ $materi->deskripsi }}</p>
                                @endif
                                <p class="text-[11px] text-gray-400 mt-1">{{ $group->count() }} kelas dalam grup</p>
                            </td>
                            <td class="py-4 px-6">
                                <p class="font-semibold text-gray-800">{{ $mapelNama }}</p>
                                <div class="flex flex-wrap gap-1.5 mt-1.5">
                                    @foreach($kelasGrup as $kelas)
                                        <span class="px-2 py-1 rounded-full bg-blue-50 text-blue-700 text-[11px] font-semibold">
                                            {{ $kelas->nama_kelas }} · Tingkat {{ $kelas->tingkatan }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <a href="{{ asset('storage/' . $materi->file_materi) }}" target="_blank" rel="noopener" class="text-blue-700 hover:underline text-xs font-semibold">Buka file</a>
                                @if($materi->url_youtube)
                                    <a href="{{ $materi->url_youtube }}" target="_blank" rel="noopener" class="block mt-1 text-red-600 hover:underline text-xs font-semibold">Video YouTube</a>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-xs text-gray-500">{{ $materi->created_at?->format('d M Y') }}</td>
                            <td class="py-4 px-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('guru.materi.edit', $materi->id_grub_materi) }}"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 text-xs font-semibold hover:bg-blue-100 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        Edit
                                    </a>

                                    <form action="{{ route('guru.materi.destroy', $materi->id_grub_materi) }}" method="POST"
                                        data-confirm="Apakah Anda yakin ingin menghapus materi '{{ addslashes($materi->judul) }}' dari seluruh {{ $group->count() }} kelas? Tindakan ini tidak dapat dibatalkan.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-50 text-red-600 text-xs font-semibold hover:bg-red-100 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-16 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 rounded-2xl bg-gray-100 text-gray-400 flex items-center justify-center mb-3">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <p class="text-base font-bold text-gray-800">Belum Ada Data Materi</p>
                                <p class="text-xs text-gray-400 mt-1">Data materi pembelajaran yang telah diunggah akan muncul di sini.</p>
                            </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination & Rows Count Footer -->
        <div class="px-6 py-4 border-t border-gray-200 bg-white flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-xs text-gray-500">
                Menampilkan <span class="font-bold text-gray-800">{{ $materiGroups->count() }}</span> grup materi
            </p>
        </div>
    </div>

</div>
@endsection
