@extends('layouts.app')

@section('title', 'Detail Mapel: ' . $mapel->nama_mapel)

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{ assignModalOpen: false }">

    <!-- Header Navigation -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('mapel.index') }}" class="p-2 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Detail Mata Pelajaran</h1>
                <p class="text-sm text-gray-500">Informasi kurikulum mapel dan penugasan guru pengampu.</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button 
                @click="assignModalOpen = true"
                type="button"
                class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-rose-200 transition-all flex items-center gap-2"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tugaskan Guru Mapel</span>
            </button>
            <a href="{{ route('mapel.edit', $mapel->id) }}" class="px-3.5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition-colors">
                Edit Mapel
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <!-- Nama Mapel -->
        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Mata Pelajaran</p>
            <h3 class="text-2xl font-extrabold text-gray-900 leading-tight">{{ $mapel->nama_mapel }}</h3>
            <span class="inline-block mt-2 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-700">
                ID Mapel: #{{ $mapel->id }}
            </span>
        </div>

        <!-- Standar KKM -->
        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Standar KKM</p>
            <h3 class="text-3xl font-extrabold text-amber-600">{{ $mapel->kkm }}</h3>
            <p class="text-xs text-gray-500 mt-1">Batas minimum ketuntasan belajar</p>
        </div>

        <!-- Total Guru Pengampu -->
        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Total Penugasan Mengajar</p>
            <h3 class="text-3xl font-extrabold text-indigo-600">{{ $mapel->guruMapels->count() }} Kelas</h3>
            <p class="text-xs text-gray-500 mt-1">Kelas aktif terlayani guru pengampu</p>
        </div>
    </div>

    <!-- Table of Assigned Teachers -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="text-base font-bold text-gray-900">Daftar Guru Pengampu Mapel Ini</h3>
                <p class="text-xs text-gray-500 mt-0.5">Guru yang ditugaskan untuk mengajar mata pelajaran {{ $mapel->nama_mapel }} pada tiap kelas.</p>
            </div>
            <button 
                @click="assignModalOpen = true"
                type="button" 
                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold rounded-xl transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ Pilih Guru Pengampu</span>
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-200 text-[11px] font-bold uppercase tracking-wider text-gray-500">
                        <th class="py-3.5 px-6">Nama Guru Pengampu</th>
                        <th class="py-3.5 px-4">NIP</th>
                        <th class="py-3.5 px-4">Kelas yang Diajar</th>
                        <th class="py-3.5 px-4">Tingkatan</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($mapel->guruMapels as $gm)
                        <tr class="hover:bg-rose-50/30 transition-colors">
                            <td class="py-4 px-6 font-bold text-gray-900">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-700 font-bold text-xs flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr($gm->guru->nama ?? 'G', 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-800">{{ $gm->guru->nama ?? 'Guru Dihapus' }}</p>
                                        @if($gm->guru)
                                            <a href="{{ route('guru.show', $gm->guru->id) }}" class="text-xs text-indigo-600 hover:underline">Lihat Profil Guru &rarr;</a>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4 font-mono text-xs text-gray-600">
                                {{ $gm->guru->nip ?? '-' }}
                            </td>
                            <td class="py-4 px-4 font-semibold text-gray-800">
                                @if($gm->kelas)
                                    <a href="{{ route('kelas.show', $gm->kelas->id) }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 font-bold text-xs hover:bg-amber-100 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m3 0h1m-1-4h.01M9 16h.01M9 12h.01M9 8h.01M15 16h.01M15 12h.01M15 8h.01"/></svg>
                                        {{ $gm->kelas->nama_kelas }}
                                    </a>
                                @else
                                    <span class="text-gray-400 italic">Semua Kelas</span>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                    Tingkat {{ $gm->kelas->tingkatan ?? '-' }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right whitespace-nowrap">
                                <form action="{{ route('guru-mapel.destroy', $gm->id) }}" method="POST" data-confirm="Hapus penugasan guru {{ $gm->guru->nama ?? '' }} untuk mapel ini di kelas {{ $gm->kelas->nama_kelas ?? '' }}?" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-xl transition-colors" title="Hapus Penugasan">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-gray-400">
                                <div class="max-w-xs mx-auto text-center space-y-2">
                                    <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto text-gray-400">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                    </div>
                                    <p class="font-bold text-gray-700">Belum Ada Guru Pengampu</p>
                                    <p class="text-xs text-gray-400">Klik tombol "Tugaskan Guru Mapel" di atas untuk memilih guru pengampu mapel ini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Penugasan Guru Mapel (Poin 9: Pilih Guru Pengampu) -->
    <div 
        x-show="assignModalOpen" 
        class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;"
    >
        <div class="min-h-screen px-4 text-center flex items-center justify-center">
            <!-- Backdrop -->
            <div 
                class="fixed inset-0 bg-gray-900/60 transition-opacity" 
                @click="assignModalOpen = false"
            ></div>

            <!-- Modal Content -->
            <div class="relative bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 text-left shadow-2xl border border-gray-100 z-10 space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div>
                        <h3 class="text-lg font-extrabold text-gray-900">Tugaskan Guru Pengampu Mapel</h3>
                        <p class="text-xs text-gray-500">Pilih guru dan kelas untuk mata pelajaran {{ $mapel->nama_mapel }}.</p>
                    </div>
                    <button 
                        type="button" 
                        @click="assignModalOpen = false"
                        class="p-2 text-gray-400 hover:text-gray-700 rounded-xl hover:bg-gray-100"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('guru-mapel.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="mapel_id" value="{{ $mapel->id }}">

                    <!-- Pilih Guru -->
                    <div>
                        <label for="guru_id" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">
                            Pilih Guru Pengajar <span class="text-rose-500">*</span>
                        </label>
                        <select 
                            id="guru_id" 
                            name="guru_id" 
                            required 
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 text-sm focus:outline-none focus:ring-2 focus:ring-rose-500 font-medium text-gray-800"
                        >
                            <option value="">-- Pilih Guru --</option>
                            @foreach($gurus as $guru)
                                <option value="{{ $guru->id }}">{{ $guru->nama }} (NIP: {{ $guru->nip }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Pilih Kelas -->
                    <div>
                        <label for="kelas_id" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">
                            Pilih Kelas <span class="text-rose-500">*</span>
                        </label>
                        <select 
                            id="kelas_id" 
                            name="kelas_id" 
                            required 
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 text-sm focus:outline-none focus:ring-2 focus:ring-rose-500 font-medium text-gray-800"
                        >
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($kelases as $kelas)
                                <option value="{{ $kelas->id }}">{{ $kelas->nama_kelas }} (Tingkat {{ $kelas->tingkatan }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="pt-3 flex items-center justify-end gap-3">
                        <button 
                            type="button" 
                            @click="assignModalOpen = false"
                            class="px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold transition-colors"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold shadow-md shadow-rose-200 transition-all"
                        >
                            Simpan Penugasan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
