@extends('layouts.app')

@section('title', 'Detail Guru: ' . $guru->nama)

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{ modalOpen: false, modalImg: '', assignModalOpen: false }">

    <!-- Header Navigation -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('guru.index') }}" class="p-2 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Detail Profil Guru</h1>
                <p class="text-sm text-gray-500">Informasi biodata, kelas yang diwalikan, dan jadwal mengajar.</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button 
                @click="assignModalOpen = true" 
                type="button" 
                class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-rose-200 transition-all flex items-center gap-1.5"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tugas Mengajar</span>
            </button>
            <a href="{{ route('guru.edit', $guru->id) }}" class="px-4 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-sm font-semibold rounded-xl border border-indigo-200 transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Edit Data</span>
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

    <!-- Profile Summary Card -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5 pb-6 border-b border-gray-100">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-indigo-600 to-purple-600 text-white font-extrabold text-xl flex items-center justify-center shadow-lg shadow-indigo-200 shrink-0">
                {{ strtoupper(substr($guru->nama, 0, 2)) }}
            </div>
            <div class="flex-1">
                <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900 leading-tight">{{ $guru->nama }}</h2>
                <div class="flex flex-wrap items-center gap-3 mt-1 text-sm text-gray-500">
                    <span class="font-mono bg-gray-100 px-2 py-0.5 rounded text-gray-700 text-xs font-semibold">NIP: {{ $guru->nip }}</span>
                    <span>&bull;</span>
                    @if($guru->user)
                        <span class="inline-flex items-center gap-1 text-emerald-600 font-semibold text-xs">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            Akun Sistem: {{ $guru->user->email }}
                        </span>
                    @else
                        <span class="text-amber-600 font-medium text-xs">Belum memiliki akun login</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Grid Info -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-6">
            <!-- Wali Kelas Info -->
            <div class="p-4 rounded-xl bg-gray-50 border border-gray-200">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Status Wali Kelas</p>
                @if($guru->kelas)
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-lg font-extrabold text-indigo-700">{{ $guru->kelas->nama_kelas }}</h4>
                            <p class="text-xs text-gray-500">{{ $guru->kelas->siswas->count() }} Siswa terdaftar</p>
                        </div>
                        <a href="{{ route('kelas.show', $guru->kelas->id) }}" class="text-xs font-semibold text-indigo-600 hover:underline">
                            Lihat Kelas &rarr;
                        </a>
                    </div>
                @else
                    <p class="text-sm text-gray-500 italic">Tidak ditugaskan sebagai wali kelas.</p>
                @endif
            </div>

            <!-- Total Mapel Diampu -->
            <div class="p-4 rounded-xl bg-gray-50 border border-gray-200">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Mata Pelajaran Diampu</p>
                <h4 class="text-lg font-extrabold text-gray-900">{{ $guru->guruMapels()->has('pengampuKelases')->count() }} Mata Pelajaran</h4>
                <p class="text-xs text-gray-500">{{ $guru->pengampuKelases->count() }} total kelas diampu</p>
            </div>
        </div>
    </div>

    <!-- Mata Pelajaran Diampu Table -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="text-base font-bold text-gray-900">Daftar Mata Pelajaran & Kelas Mengajar</h3>
                <p class="text-xs text-gray-500">Mata pelajaran dan kelas yang diampu oleh {{ $guru->nama }}.</p>
            </div>
            <button 
                @click="assignModalOpen = true" 
                type="button" 
                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold rounded-xl transition-colors shrink-0"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tugaskan Mapel & Kelas</span>
            </button>
        </div>

        @php
            $groupedPengampu = $guru->pengampuKelases->groupBy('guru_mapel_id');
        @endphp

        @if($groupedPengampu->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-[11px] font-bold uppercase text-gray-500">
                            <th class="py-3 px-6">Mata Pelajaran</th>
                            <th class="py-3 px-4">Kelas yang Diampu</th>
                            <th class="py-3 px-4 text-center">Total Kelas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($groupedPengampu as $gmId => $items)
                            @php
                                $first = $items->first();
                            @endphp
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="py-3.5 px-6 font-bold text-gray-900">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm">{{ $first->mapel->nama_mapel ?? '-' }}</span>
                                        @if($first->mapel)
                                            <span class="text-[11px] px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 border border-amber-200 font-mono font-bold">KKM: {{ $first->mapel->kkm }}</span>
                                            <a href="{{ route('mapel.show', $first->mapel->id) }}" class="text-xs text-indigo-600 hover:underline">Detail &rarr;</a>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        @foreach($items as $gm)
                                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50 border border-indigo-100 text-indigo-800 text-xs font-semibold">
                                                <a href="{{ route('kelas.show', $gm->kelas->id) }}" class="hover:underline">
                                                    {{ $gm->kelas->nama_kelas ?? '-' }}
                                                </a>
                                                <form action="{{ route('guru-mapel.destroy', $gm->id) }}" method="POST" data-confirm="Hapus penugasan mapel {{ $first->mapel->nama_mapel ?? '' }} di kelas {{ $gm->kelas->nama_kelas ?? '' }} untuk guru ini?" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-indigo-400 hover:text-rose-600 transition-colors ml-0.5" title="Lepas kelas {{ $gm->kelas->nama_kelas ?? '' }}">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700">
                                        {{ $items->count() }} Kelas
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-6 text-center rounded-xl border border-dashed border-gray-200 bg-gray-50/50">
                <p class="text-sm font-semibold text-gray-600">Guru ini belum memiliki alokasi mapel atau kelas mengajar.</p>
                <p class="text-xs text-gray-400 mt-1">Gunakan tombol "+ Tugaskan Mapel & Kelas" untuk menugaskan guru ini.</p>
            </div>
        @endif
    </div>

    <!-- Foto Jadwal Mengajar Guru Card -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-gray-900">Foto Jadwal Mengajar Guru</h3>
                <p class="text-xs text-gray-500">Jadwal ini akan dilihat oleh guru saat membuka menu Jadwal.</p>
            </div>
            <a href="{{ route('admin.jadwal.index', ['tab' => 'guru']) }}" class="text-xs font-semibold text-indigo-600 hover:underline">
                Upload / Ganti di Kelola Jadwal &rarr;
            </a>
        </div>

        @if($guru->jadwal_url)
            <div class="relative group rounded-2xl overflow-hidden border border-gray-200 bg-gray-50 p-2 text-center max-w-lg mx-auto">
                <img src="{{ $guru->jadwal_url }}" alt="Jadwal {{ $guru->nama }}" class="max-h-72 w-auto mx-auto rounded-xl object-contain shadow-sm cursor-pointer hover:opacity-95 transition-opacity" @click="modalOpen = true; modalImg = '{{ $guru->jadwal_url }}'">
                <p class="text-xs text-gray-400 mt-2">Klik gambar untuk melihat resolusi penuh</p>
            </div>
        @else
            <div class="p-8 text-center rounded-2xl border border-dashed border-gray-200 bg-gray-50/50">
                <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <p class="text-sm font-semibold text-gray-600">Belum ada foto jadwal yang diunggah</p>
                <p class="text-xs text-gray-400 mt-0.5">Admin dapat mengunggah foto jadwal mengajar di menu Kelola Jadwal.</p>
            </div>
        @endif
    </div>

    <!-- Modal Zoom Foto Interaktif -->
    <div 
        x-show="modalOpen" 
        style="display: none;"
        class="fixed inset-0 z-50 bg-gray-900/80 backdrop-blur-sm flex items-center justify-center p-4"
        @click.self="modalOpen = false"
    >
        <div class="relative max-w-4xl w-full bg-white rounded-2xl shadow-2xl overflow-hidden p-2">
            <div class="flex items-center justify-between p-3 border-b border-gray-100">
                <h4 class="font-bold text-gray-800 text-sm">Foto Jadwal</h4>
                <button @click="modalOpen = false" class="p-1 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-4 text-center max-h-[80vh] overflow-auto">
                <img :src="modalImg" alt="Jadwal Zoom" class="mx-auto max-h-[70vh] object-contain rounded-lg">
            </div>
        </div>
    </div>

    <!-- Modal Penugasan Mapel & Kelas (Poin 9) -->
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
                        <h3 class="text-lg font-extrabold text-gray-900">Tugaskan Mengajar Mapel & Kelas</h3>
                        <p class="text-xs text-gray-500">Pilih mapel dan kelas untuk {{ $guru->nama }}.</p>
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
                    <input type="hidden" name="guru_id" value="{{ $guru->id }}">

                    <!-- Pilih Mapel -->
                    <div>
                        <label for="guru_modal_mapel_id" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">
                            Pilih Mata Pelajaran <span class="text-rose-500">*</span>
                        </label>
                        <select 
                            id="guru_modal_mapel_id" 
                            name="mapel_id" 
                            required 
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium text-gray-800"
                        >
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            @foreach($allMapels as $mapel)
                                <option value="{{ $mapel->id }}">{{ $mapel->nama_mapel }} (KKM: {{ $mapel->kkm }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Pilih Kelas (Multi-select Checkbox) -->
                    <div x-data="{
                        selectedClasses: [],
                        allClassIds: {{ $allKelases->pluck('id')->toJson() }},
                        toggleAll() {
                            if (this.selectedClasses.length === this.allClassIds.length) {
                                this.selectedClasses = [];
                            } else {
                                this.selectedClasses = [...this.allClassIds];
                            }
                        }
                    }" class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700">
                                Pilih Kelas yang Diampu <span class="text-rose-500">*</span>
                            </label>
                            <button 
                                type="button" 
                                @click="toggleAll()" 
                                class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 hover:underline">
                                <span x-text="selectedClasses.length === allClassIds.length ? 'Batalkan Semua' : 'Pilih Semua Kelas'"></span>
                            </button>
                        </div>

                        <div class="max-h-56 overflow-y-auto p-3 rounded-xl border border-gray-200 bg-gray-50/50 space-y-3">
                            @foreach($allKelases->groupBy('tingkatan') as $tingkat => $kelasList)
                                <div>
                                    <p class="text-[10px] font-extrabold uppercase tracking-wider text-gray-400 mb-1.5">Tingkat {{ $tingkat }}</p>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                        @foreach($kelasList as $kelas)
                                            <label class="flex items-center gap-2 p-2 rounded-lg bg-white border border-gray-200 hover:border-indigo-300 hover:bg-indigo-50/30 cursor-pointer transition-all text-xs font-medium text-gray-800 shadow-2xs">
                                                <input 
                                                    type="checkbox" 
                                                    name="kelas_ids[]" 
                                                    value="{{ $kelas->id }}"
                                                    x-model="selectedClasses"
                                                    class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                                <span>{{ $kelas->nama_kelas }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <p class="text-[11px] text-gray-400">Centang satu atau beberapa kelas yang akan diajar untuk mata pelajaran ini.</p>
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
                            class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold shadow-md shadow-indigo-200 transition-all"
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
