@extends('layouts.app')

@section('title', 'Detail Mapel: ' . $mapel->nama_mapel)

@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="{ assignModalOpen: false }">

    <!-- Header Navigation & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('mapel.index') }}" class="p-2.5 rounded-xl bg-white border border-slate-200/80 text-slate-500 hover:text-slate-900 hover:bg-slate-50 transition-colors shadow-2xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ $mapel->nama_mapel }}</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200/80">
                        KKM: {{ $mapel->kkm }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Informasi kurikulum mata pelajaran dan daftar alokasi pengajar tiap kelas.</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button 
                @click="assignModalOpen = true"
                type="button"
                class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-500/20 transition-all hover:-translate-y-0.5"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ Tugaskan Guru</span>
            </button>
            <a href="{{ route('mapel.edit', $mapel->id) }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl border border-slate-200/80 shadow-2xs transition-colors">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Edit Mapel</span>
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50/80 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-3 shadow-2xs">
            <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <span class="font-bold">{{ session('success') }}</span>
        </div>
    @endif
    @if(isset($errors) && $errors->any())
        <div class="p-4 rounded-2xl bg-rose-50/80 border border-rose-200 text-rose-800 text-xs shadow-2xs">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- 3 Highlight Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Nama Mapel -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Mata Pelajaran</span>
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
            </div>
            <h3 class="text-xl sm:text-2xl font-black text-slate-900 leading-tight">{{ $mapel->nama_mapel }}</h3>
            <p class="text-[11px] text-slate-400 mt-1">Kode ID: #{{ $mapel->id }}</p>
        </div>

        <!-- Standar KKM -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Standar KKM</span>
                <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-amber-600 tracking-tight">{{ $mapel->kkm }}</div>
            <p class="text-[11px] text-slate-400 mt-1">Batas minimum ketuntasan belajar</p>
        </div>

        <!-- Total Guru Pengampu -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Guru Pengampu</span>
                <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ $mapel->guruMapels()->has('pengampuKelases')->count() }} Guru</div>
            <p class="text-[11px] text-slate-400 mt-1">Mengajar di {{ $mapel->pengampuKelases->count() }} kelas</p>
        </div>
    </div>

    <!-- Table of Assigned Teachers -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="text-base font-bold text-slate-900">Daftar Guru Pengampu Mata Pelajaran Ini</h3>
                <p class="text-xs text-slate-500 mt-0.5">Guru yang ditugaskan untuk mengajar mata pelajaran {{ $mapel->nama_mapel }} pada tiap kelas.</p>
            </div>
            <button 
                @click="assignModalOpen = true" 
                type="button" 
                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold rounded-xl border border-blue-100/80 transition-colors shrink-0"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Pilih Guru Pengampu</span>
            </button>
        </div>

        @php
            $groupedGuruPengampu = $mapel->pengampuKelases->groupBy('guru_mapel_id');
        @endphp

        @if($groupedGuruPengampu->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="py-3.5 px-6">Nama Guru Pengampu</th>
                            <th class="py-3.5 px-4">NIP</th>
                            <th class="py-3.5 px-4">Kelas yang Diajar</th>
                            <th class="py-3.5 px-4 text-center">Total Kelas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($groupedGuruPengampu as $gmId => $items)
                            @php
                                $first = $items->first();
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3.5 px-6 font-bold text-slate-900">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-700 font-bold text-xs flex items-center justify-center shrink-0 border border-slate-200">
                                            {{ strtoupper(substr($first->guru->nama ?? 'G', 0, 2)) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-800">{{ $first->guru->nama ?? 'Guru Dihapus' }}</p>
                                            @if($first->guru)
                                                <a href="{{ route('guru.show', $first->guru->id) }}" class="text-[11px] text-blue-600 hover:underline">Lihat Profil Guru &rarr;</a>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-slate-500">
                                    {{ $first->guru->nip ?? '-' }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        @foreach($items as $gm)
                                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-50 border border-blue-100 text-blue-800 text-[11px] font-bold">
                                                <a href="{{ route('kelas.show', $gm->kelas->id) }}" class="hover:underline">
                                                    {{ $gm->kelas->nama_kelas ?? '-' }}
                                                </a>
                                                <form action="{{ route('guru-mapel.destroy', $gm->id) }}" method="POST" data-confirm="Hapus penugasan guru {{ $first->guru->nama ?? '' }} dari kelas {{ $gm->kelas->nama_kelas ?? '' }}?" class="inline">
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
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700">
                                        {{ $items->count() }} Kelas
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="py-12 text-center text-slate-400">
                <div class="max-w-xs mx-auto text-center space-y-2">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto text-slate-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                    <p class="font-bold text-slate-700 text-sm">Belum Ada Guru Pengampu</p>
                    <p class="text-xs text-slate-400">Klik tombol "Pilih Guru Pengampu" di atas untuk menugaskan guru pada mapel ini.</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Modal Penugasan Guru Mapel -->
    <div 
        x-show="assignModalOpen" 
        class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;"
    >
        <div class="min-h-screen px-4 text-center flex items-center justify-center">
            <!-- Backdrop -->
            <div 
                class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" 
                @click="assignModalOpen = false"
            ></div>

            <!-- Modal Content -->
            <div class="relative bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 text-left shadow-2xl border border-slate-100 z-10 space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-lg font-extrabold text-slate-900">Tugaskan Guru Pengampu</h3>
                        <p class="text-xs text-slate-500">Pilih guru dan kelas untuk mata pelajaran {{ $mapel->nama_mapel }}.</p>
                    </div>
                    <button 
                        type="button" 
                        @click="assignModalOpen = false"
                        class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                @php
                    $assignedMap = $mapel->pengampuKelases->mapWithKeys(function($pk) {
                        return [$pk->kelas_id => [
                            'guru_id' => $pk->guruMapel?->guru_id,
                            'guru_nama' => $pk->guruMapel?->guru?->nama ?? 'Guru Lain',
                        ]];
                    });
                @endphp

                <form 
                    action="{{ route('guru-mapel.store') }}" 
                    method="POST" 
                    class="space-y-4"
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
                    @csrf
                    <input type="hidden" name="mapel_id" value="{{ $mapel->id }}">

                    <!-- Pilih Guru -->
                    <div>
                        <label for="guru_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Pilih Guru Pengajar <span class="text-rose-500">*</span>
                        </label>
                        <select 
                            id="guru_id" 
                            name="guru_id" 
                            x-model="selectedGuruId"
                            required 
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-semibold text-slate-800 transition-all"
                        >
                            <option value="">-- Pilih Guru --</option>
                            @foreach($gurus as $guru)
                                <option value="{{ $guru->id }}">{{ $guru->nama }} (NIP: {{ $guru->nip }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Pilih Kelas (Multi-select Checkbox) -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                Pilih Kelas yang Diampu <span class="text-rose-500">*</span>
                            </label>
                            <button 
                                type="button" 
                                @click="toggleAll()" 
                                :disabled="getAvailableClassIds().length === 0"
                                :class="getAvailableClassIds().length === 0 ? 'opacity-40 cursor-not-allowed' : 'text-blue-600 hover:text-blue-800 hover:underline'"
                                class="text-[11px] font-bold transition-all">
                                <span x-text="getAvailableClassIds().length === 0 ? 'Semua Kelas Sudah Terisi' : (isAllAvailableSelected() ? 'Batalkan Semua' : 'Pilih Semua Kelas Tersedia')"></span>
                            </button>
                        </div>

                        <div class="max-h-56 overflow-y-auto p-3 rounded-2xl border border-slate-200 bg-slate-50/50 space-y-3">
                            @foreach($kelases->groupBy('tingkatan') as $tingkat => $kelasList)
                                <div>
                                    <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1.5">Tingkat {{ $tingkat }}</p>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                        @foreach($kelasList as $kelas)
                                            <label 
                                                :class="isDisabled({{ $kelas->id }}) 
                                                    ? 'bg-slate-100 border-slate-200/90 text-slate-400 cursor-not-allowed select-none opacity-80' 
                                                    : (selectedClasses.includes({{ $kelas->id }}) ? 'bg-blue-50/70 border-blue-300 ring-1 ring-blue-400/30' : 'bg-white border-slate-200 hover:border-blue-300 hover:bg-blue-50/30') + ' cursor-pointer text-slate-800 shadow-2xs'"
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
                                                            name="kelas_ids[]" 
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
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Kelas tersedia
                            </span>
                            <span class="inline-flex items-center gap-1">
                                <span class="w-2 h-2 rounded-full bg-slate-400"></span> Sudah memiliki pengampu (terkunci)
                            </span>
                        </div>
                    </div>

                    <div class="pt-3 flex items-center justify-end gap-3">
                        <button 
                            type="button" 
                            @click="assignModalOpen = false"
                            class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition-all hover:-translate-y-0.5"
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