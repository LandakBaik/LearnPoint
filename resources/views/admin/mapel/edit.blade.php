@extends('layouts.app')

@section('title', 'Edit Mata Pelajaran & Pengampu')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Navigation -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('mapel.index') }}" class="p-2.5 rounded-xl bg-white border border-slate-200/80 text-slate-500 hover:text-slate-900 hover:bg-slate-50 transition-colors shadow-2xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Edit Mata Pelajaran</h1>
                <p class="text-xs text-slate-500 mt-0.5">Perbarui kurikulum dan kelola guru pengampu untuk <span class="font-bold text-slate-800">{{ $mapel->nama_mapel }}</span>.</p>
            </div>
        </div>
        <div>
            <a href="{{ route('mapel.show', $mapel->id) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-blue-50 text-blue-700 text-xs font-bold hover:bg-blue-100 border border-blue-100 transition-colors">
                <span>Lihat Detail Mapel</span>
                <span>&rarr;</span>
            </a>
        </div>
    </div>

    <!-- Form Card 1: Data Pokok Mapel & Tambah Guru Pengampu -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-6 sm:p-8">
        <h2 class="text-base font-extrabold text-slate-900 pb-3 border-b border-slate-100 mb-6">Kurikulum & Penugasan Pengampu</h2>
        <form action="{{ route('mapel.update', $mapel->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Nama Mapel -->
                <div>
                    <label for="nama_mapel" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Nama Mata Pelajaran <span class="text-rose-500">*</span></label>
                    <input 
                        type="text" 
                        id="nama_mapel" 
                        name="nama_mapel" 
                        value="{{ old('nama_mapel', $mapel->nama_mapel) }}" 
                        required 
                        class="w-full px-4 py-2.5 rounded-xl border @error('nama_mapel') border-rose-400 bg-rose-50/30 @else border-slate-200 bg-slate-50/50 @enderror text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-semibold text-slate-900 transition-all"
                    >
                    @error('nama_mapel')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nilai KKM -->
                <div>
                    <label for="kkm" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Kriteria Ketuntasan Minimal (KKM) <span class="text-rose-500">*</span></label>
                    <input 
                        type="number" 
                        id="kkm" 
                        name="kkm" 
                        value="{{ old('kkm', $mapel->kkm) }}" 
                        required 
                        min="0" 
                        max="100" 
                        class="w-full px-4 py-2.5 rounded-xl border @error('kkm') border-rose-400 bg-rose-50/30 @else border-slate-200 bg-slate-50/50 @enderror text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-bold text-slate-900 transition-all"
                    >
                    @error('kkm')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Tambah Guru Pengampu Sekaligus -->
            <div class="p-4 rounded-2xl bg-slate-50/70 border border-slate-200/80 space-y-4">
                <div class="flex items-center gap-2 text-slate-800">
                    <div class="w-6 h-6 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs">
                        +
                    </div>
                    <span class="text-xs font-bold uppercase tracking-wider">Tambah Guru Pengampu & Alokasi Kelas Baru (Opsional)</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1.5">Pilih Guru Pengampu</label>
                    <select name="new_guru_id" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        <option value="">-- Pilih Guru --</option>
                        @foreach($gurus as $g)
                            <option value="{{ $g->id }}">{{ $g->nama }} (NIP: {{ $g->nip }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Multi-select Checkbox Kelas -->
                <div x-data="{
                    selectedClasses: [],
                    allClassIds: {{ $kelases->pluck('id')->toJson() }},
                    toggleAll() {
                        if (this.selectedClasses.length === this.allClassIds.length) {
                            this.selectedClasses = [];
                        } else {
                            this.selectedClasses = [...this.allClassIds];
                        }
                    }
                }" class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-slate-600 uppercase">Pilih Kelas yang Diampu</label>
                        <button 
                            type="button" 
                            @click="toggleAll()" 
                            class="text-[11px] font-bold text-blue-600 hover:text-blue-800 hover:underline">
                            <span x-text="selectedClasses.length === allClassIds.length ? 'Batalkan Semua' : 'Pilih Semua Kelas'"></span>
                        </button>
                    </div>

                    <div class="max-h-52 overflow-y-auto p-3 rounded-2xl border border-slate-200 bg-white space-y-3">
                        @foreach($kelases->groupBy('tingkatan') as $tingkat => $kelasList)
                            <div>
                                <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1.5">Tingkat {{ $tingkat }}</p>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                    @foreach($kelasList as $kelas)
                                        <label class="flex items-center gap-2 p-2 rounded-xl bg-slate-50 border border-slate-200/80 hover:border-blue-300 hover:bg-blue-50/30 cursor-pointer transition-all text-xs font-medium text-slate-800 shadow-2xs">
                                            <input 
                                                type="checkbox" 
                                                name="new_kelas_ids[]" 
                                                value="{{ $kelas->id }}"
                                                x-model="selectedClasses"
                                                class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                                            <span>{{ $kelas->nama_kelas }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <p class="text-[11px] text-slate-400">Pilih Guru dan centang kelas untuk langsung menambahkan alokasi pengampu baru saat formulir disimpan.</p>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('mapel.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-500/20 transition-all hover:-translate-y-0.5">
                    Perbarui Mapel & Pengampu
                </button>
            </div>
        </form>
    </div>

    <!-- Section 2: Tabel Daftar Guru Pengampu Mapel Ini Saat Ini -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-extrabold text-slate-900">Daftar Guru Pengampu Mapel {{ $mapel->nama_mapel }}</h3>
                <p class="text-xs text-slate-500 mt-0.5">Seluruh tenaga pendidik yang saat ini mengajar mapel ini pada masing-masing kelas.</p>
            </div>
            <span class="px-3 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-bold border border-blue-100">
                {{ $mapel->guruMapels()->has('pengampuKelases')->count() }} Guru ({{ $mapel->pengampuKelases->count() }} Kelas)
            </span>
        </div>

        @php
            $groupedGuruPengampu = $mapel->pengampuKelases->groupBy('guru_mapel_id');
        @endphp

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3 px-6">Nama Guru Pengampu</th>
                        <th class="py-3 px-4">NIP</th>
                        <th class="py-3 px-4">Kelas yang Diajar</th>
                        <th class="py-3 px-4 text-center">Total Kelas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($groupedGuruPengampu as $gmId => $items)
                        @php $first = $items->first(); @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-6 font-bold text-slate-900">
                                {{ $first->guru->nama ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-xs text-slate-500">
                                {{ $first->guru->nip ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    @foreach($items as $gm)
                                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-blue-50 text-blue-800 font-bold text-xs rounded-lg border border-blue-100">
                                            <span>{{ $gm->kelas->nama_kelas ?? '-' }}</span>
                                            <form action="{{ route('guru-mapel.destroy', $gm->id) }}" method="POST" data-confirm="Hapus penugasan guru {{ $first->guru->nama ?? '' }} dari mapel ini pada kelas {{ $gm->kelas->nama_kelas ?? '' }}?" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-blue-400 hover:text-rose-600 transition-colors ml-0.5" title="Lepas kelas {{ $gm->kelas->nama_kelas ?? '' }}">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                                    {{ $items->count() }} Kelas
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-slate-400">
                                <p class="text-sm font-semibold">Belum ada guru yang ditugaskan mengampu mata pelajaran ini.</p>
                                <p class="text-xs text-slate-400 mt-1">Gunakan opsi pada form di atas untuk menugaskan guru dan kelas pertama untuk mapel ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection