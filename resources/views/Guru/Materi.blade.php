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

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm">
        <form method="GET" action="{{ route('guru.materi') }}" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-3">
            <div class="relative sm:col-span-2 xl:col-span-2">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input
                    type="search"
                    name="search"
                    value="{{ $filters['search'] ?? '' }}"
                    placeholder="Cari judul materi"
                    class="w-full pl-10 pr-4 py-2 rounded-full border border-gray-200 bg-gray-50/50 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all placeholder-gray-400"
                >
            </div>

            <select name="mapel_id" class="rounded-full border border-gray-200 px-4 py-2 text-xs font-medium bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                <option value="">Semua Mata Pelajaran</option>
                @foreach($mapels as $mapel)
                    <option value="{{ $mapel->id }}" @selected(($filters['mapel_id'] ?? '') == $mapel->id)>{{ $mapel->nama_mapel }}</option>
                @endforeach
            </select>

            <select name="tingkatan" class="rounded-full border border-gray-200 px-4 py-2 text-xs font-medium bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                <option value="">Semua Tingkatan</option>
                @foreach($tingkatanOptions as $tingkatan)
                    <option value="{{ $tingkatan }}" @selected(($filters['tingkatan'] ?? '') == $tingkatan)>Tingkat {{ $tingkatan }}</option>
                @endforeach
            </select>

            <select name="kelas_id" class="rounded-full border border-gray-200 px-4 py-2 text-xs font-medium bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                <option value="">Semua Kelas</option>
                @foreach($kelasOptions as $kelas)
                    <option value="{{ $kelas->id }}" @selected(($filters['kelas_id'] ?? '') == $kelas->id)>{{ $kelas->nama_kelas }} (Tingkat {{ $kelas->tingkatan }})</option>
                @endforeach
            </select>

            <div class="sm:col-span-2 xl:col-span-5 flex items-center justify-end gap-2">
                <a href="{{ route('guru.materi') }}" class="px-4 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-100 rounded-full">Reset</a>
                <button type="submit" class="px-5 py-2 bg-gray-800 hover:bg-gray-900 text-white rounded-full text-xs font-semibold">Terapkan Filter</button>
            </div>
        </form>
    </div>

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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-16 text-center text-gray-500">
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

    <!-- Page Footer -->
    <div class="pt-6 border-t border-gray-200/80 flex flex-col md:flex-row items-center justify-between gap-4 text-xs text-gray-400">
        <div>
            <p class="font-bold text-gray-700">LearnPoint SMP</p>
            <p class="mt-0.5">&copy; 2024 LearnPoint Indonesia &bull; Terintegrasi dengan Kurikulum Merdeka SMP. Hak Cipta Dilindungi Undang-Undang.</p>
        </div>
        <div class="flex items-center gap-4 flex-wrap">
            <a href="#" class="hover:text-gray-600">Kebijakan Privasi</a>
            <a href="#" class="hover:text-gray-600">Syarat & Ketentuan Layanan</a>
            <a href="#" class="hover:text-gray-600">Pusat Bantuan Sekolah</a>
            <a href="#" class="hover:text-gray-600">Kontak Admin</a>
        </div>
    </div>

</div>
@endsection
