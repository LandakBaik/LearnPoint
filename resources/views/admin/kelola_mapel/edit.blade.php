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
            @php
                $assignedMap = $mapel->pengampuKelases->mapWithKeys(function($pk) {
                    return [$pk->kelas_id => [
                        'guru_id' => $pk->guruMapel?->guru_id,
                        'guru_nama' => $pk->guruMapel?->guru?->nama ?? 'Guru Lain',
                    ]];
                });
            @endphp
            <div 
                class="p-4 rounded-2xl bg-slate-50/70 border border-slate-200/80 space-y-4"
                x-data="{
                    selectedGuruId: '',
                    selectedClasses: [],
                    assignedMap: {{ $assignedMap->toJson() }},
                    allClassIds: {{ $kelases->pluck('id')->toJson() }},
                    isAssigned(kelasId) {
                        return Boolean(this.assignedMap[kelasId]);
                    },
                    isAssignedToSelectedGuru(kelasId) {
                        if (!this.selectedGuruId) return false;
                        return this.assignedMap[kelasId] && this.assignedMap[kelasId].guru_id == this.selectedGuruId;
                    },
                    isDisabled(kelasId) {
                        return this.isAssigned(kelasId);
                    },
                    getTeacherName(kelasId) {
                        return this.assignedMap[kelasId] ? this.assignedMap[kelasId].guru_nama : '';
                    },
                    getAvailableClassIds() {
                        return this.allClassIds.filter(id => !this.isDisabled(id));
                    },
                    isAllAvailableSelected() {
                        const available = this.getAvailableClassIds();
                        return available.length > 0 && available.every(id => this.selectedClasses.includes(id));
                    },
                    toggleAll() {
                        const available = this.getAvailableClassIds();
                        if (available.length === 0) return;
                        if (this.isAllAvailableSelected()) {
                            this.selectedClasses = this.selectedClasses.filter(id => !available.includes(id));
                        } else {
                            const set = new Set([...this.selectedClasses, ...available]);
                            this.selectedClasses = Array.from(set);
                        }
                    }
                }"
            >
                <div class="flex items-center gap-2 text-slate-800">
                    <div class="w-6 h-6 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs">
                        +
                    </div>
                    <span class="text-xs font-bold uppercase tracking-wider">Tambah Guru Pengampu & Alokasi Kelas Baru (Opsional)</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1.5">Pilih Guru Pengampu</label>
                    <select 
                        name="new_guru_id" 
                        x-model="selectedGuruId"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                    >
                        <option value="">-- Pilih Guru --</option>
                        @foreach($gurus as $g)
                            <option value="{{ $g->id }}">{{ $g->nama }} (NUPTK: {{ $g->nuptk }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Multi-select Checkbox Kelas -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-slate-600 uppercase">Pilih Kelas yang Diampu</label>
                        <button 
                            type="button" 
                            @click="toggleAll()" 
                            :disabled="getAvailableClassIds().length === 0"
                            :class="getAvailableClassIds().length === 0 ? 'opacity-40 cursor-not-allowed' : 'text-blue-600 hover:text-blue-800 hover:underline'"
                            class="text-[11px] font-bold transition-all">
                            <span x-text="getAvailableClassIds().length === 0 ? 'Semua Kelas Sudah Terisi' : (isAllAvailableSelected() ? 'Batalkan Semua' : 'Pilih Semua Kelas Tersedia')"></span>
                        </button>
                    </div>

                    <div class="max-h-52 overflow-y-auto p-3 rounded-2xl border border-slate-200 bg-white space-y-3">
                        @foreach($kelases->groupBy('tingkatan') as $tingkat => $kelasList)
                            <div>
                                <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1.5">Tingkat {{ $tingkat }}</p>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                    @foreach($kelasList as $kelas)
                                        <label 
                                            :class="isDisabled({{ $kelas->id }}) 
                                                ? 'bg-slate-100 border-slate-200 text-slate-400 cursor-not-allowed select-none opacity-80' 
                                                : (selectedClasses.includes({{ $kelas->id }}) ? 'bg-blue-50/70 border-blue-300 ring-1 ring-blue-400/30' : 'bg-slate-50 border-slate-200/80 hover:border-blue-300 hover:bg-blue-50/30') + ' cursor-pointer text-slate-800 shadow-2xs'"
                                            class="flex items-center justify-between p-2 rounded-xl border transition-all text-xs font-medium"
                                        >
                                            <div class="flex items-center gap-2 truncate">
                                                <template x-if="isDisabled({{ $kelas->id }})">
                                                    <span class="w-4 h-4 rounded bg-slate-200 text-slate-500 flex items-center justify-center shrink-0">
                                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                    </span>
                                                </template>
                                                <template x-if="!isDisabled({{ $kelas->id }})">
                                                    <input 
                                                        type="checkbox" 
                                                        name="new_kelas_ids[]" 
                                                        value="{{ $kelas->id }}"
                                                        x-model="selectedClasses"
                                                        class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                                                </template>
                                                <span :class="isDisabled({{ $kelas->id }}) ? 'text-slate-400 font-normal line-through' : 'text-slate-800 font-bold'">
                                                    {{ $kelas->nama_kelas }}
                                                </span>
                                            </div>

                                            <div>
                                                <template x-if="isAssignedToSelectedGuru({{ $kelas->id }})">
                                                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-blue-100 text-blue-700 whitespace-nowrap">
                                                        Sudah Diampu
                                                    </span>
                                                </template>
                                                <template x-if="!isAssignedToSelectedGuru({{ $kelas->id }}) && isAssigned({{ $kelas->id }})">
                                                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-slate-200 text-slate-600 truncate max-w-[90px] inline-block align-middle" :title="'Sudah diajar oleh ' + getTeacherName({{ $kelas->id }})" x-text="getTeacherName({{ $kelas->id }})">
                                                    </span>
                                                </template>
                                                <template x-if="!isAssigned({{ $kelas->id }})">
                                                    <span class="text-[9px] font-semibold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-100">
                                                        Tersedia
                                                    </span>
                                                </template>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="flex items-center gap-3 text-[11px] text-slate-500 pt-1">
                        <span class="inline-flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Tersedia
                        </span>
                        <span class="inline-flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-slate-400"></span> Sudah memiliki pengampu
                        </span>
                    </div>
                </div>
                <p class="text-[11px] text-slate-400">Pilih Guru dan centang kelas yang tersedia untuk langsung menambahkan alokasi pengampu baru saat formulir disimpan.</p>
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
                        <th class="py-3 px-4">NUPTK</th>
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
                                {{ $first->guru->nuptk ?? '-' }}
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