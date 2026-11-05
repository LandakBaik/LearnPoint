@extends('layouts.app')

@section('title', 'Kelola Jadwal Pelajaran (Foto)')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto" x-data="{ 
    activeTab: '{{ $activeTab ?? 'kelas' }}',
    modalPreview: false,
    previewUrl: '',
    previewTitle: '',
    uploadModalOpen: false,
    uploadType: 'kelas',
    selectedId: '',
    selectedName: '',
    selectedGuruMapelId: '',
    teacherMapels: [],
    filePreview: null,

    openUploadKelas(id, name) {
        this.uploadType = 'kelas';
        this.selectedId = id;
        this.selectedName = name;
        this.filePreview = null;
        this.uploadModalOpen = true;
    },

    openUploadGuru(guruId, guruName, mapelsList, defaultGmId = '') {
        this.uploadType = 'guru';
        this.selectedId = guruId;
        this.selectedName = guruName;
        this.teacherMapels = mapelsList || [];
        if (defaultGmId) {
            this.selectedGuruMapelId = defaultGmId;
        } else if (this.teacherMapels.length > 0) {
            this.selectedGuruMapelId = this.teacherMapels[0].id;
        } else {
            this.selectedGuruMapelId = 'new';
        }
        this.filePreview = null;
        this.uploadModalOpen = true;
    }
}">

    <!-- Header Title -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Kelola Jadwal (Foto)</h1>
            <p class="text-sm text-slate-500 mt-1">
                Unggah dan perbarui foto jadwal resmi untuk <span class="font-semibold text-slate-800">Jadwal Kelas (Siswa)</span> dan <span class="font-semibold text-slate-800">Jadwal Guru per Mata Pelajaran</span>.
            </p>
        </div>
    </div>

    <!-- 2 Highlight Stat Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Jadwal Kelas -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs hover:shadow-xs transition-all flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Jadwal Kelas (Siswa)</span>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ $kelases->whereNotNull('jadwal_url')->count() }}</span>
                    <span class="text-xs font-semibold text-slate-500">/ {{ $kelases->count() }} Terisi</span>
                </div>
                <div class="text-[11px] font-medium text-slate-400 mt-0.5">Foto jadwal kelas diunggah</div>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m3 0h1m-1-4h.01M9 16h.01M9 12h.01M9 8h.01M15 16h.01M15 12h.01M15 8h.01"/></svg>
            </div>
        </div>

        <!-- Jadwal Guru -->
        @php
            $totalGm = $gurus->flatMap->guruMapels;
            $filledGm = $totalGm->whereNotNull('jadwal_url')->count();
        @endphp
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs hover:shadow-xs transition-all flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Jadwal Mengajar Guru</span>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ $filledGm }}</span>
                    <span class="text-xs font-semibold text-slate-500">/ {{ $totalGm->count() }} Mapel Terisi</span>
                </div>
                <div class="text-[11px] font-medium text-slate-400 mt-0.5">Dari {{ $gurus->count() }} guru pengampu</div>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="inline-flex p-1.5 bg-slate-100/80 rounded-2xl border border-slate-200/60 shadow-2xs">
        <button
            type="button"
            @click="activeTab = 'kelas'"
            :class="activeTab === 'kelas' ? 'bg-white text-blue-600 font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900 font-semibold'"
            class="py-2.5 px-4 text-xs rounded-xl flex items-center gap-2 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m3 0h1m-1-4h.01M9 16h.01M9 12h.01M9 8h.01M15 16h.01M15 12h.01M15 8h.01" />
            </svg>
            <span>Jadwal Kelas (Siswa)</span>
            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold" :class="activeTab === 'kelas' ? 'bg-blue-50 text-blue-700' : 'bg-slate-200/80 text-slate-700'">
                {{ $kelases->count() }}
            </span>
        </button>

        <button
            type="button"
            @click="activeTab = 'guru'"
            :class="activeTab === 'guru' ? 'bg-white text-blue-600 font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900 font-semibold'"
            class="py-2.5 px-4 text-xs rounded-xl flex items-center gap-2 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <span>Jadwal Guru (Per Mapel)</span>
            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold" :class="activeTab === 'guru' ? 'bg-blue-50 text-blue-700' : 'bg-slate-200/80 text-slate-700'">
                {{ $gurus->count() }}
            </span>
        </button>
    </div>

    <!-- ==================== TAB 1: JADWAL KELAS ==================== -->
    <div x-show="activeTab === 'kelas'" x-transition class="space-y-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="py-3.5 px-4 sm:px-6">Kelas & Tingkat</th>
                            <th class="py-3.5 px-4">Wali Kelas</th>
                            <th class="py-3.5 px-4">Total Siswa</th>
                            <th class="py-3.5 px-4">Preview Foto Jadwal</th>
                            <th class="py-3.5 px-4 text-right pr-6">Aksi Upload / Kelola</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($kelases as $k)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4 sm:px-6 font-bold text-slate-900">
                                <span class="block text-sm">{{ $k->nama_kelas }}</span>
                                <span class="text-[11px] font-normal text-slate-400">Tingkat {{ $k->tingkatan }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($k->guru)
                                <p class="font-semibold text-slate-800">{{ $k->guru->nama }}</p>
                                <p class="text-[11px] text-slate-400">NUPTK: {{ $k->guru->nuptk }}</p>
                                @else
                                <span class="text-slate-400 italic text-[11px]">Belum ada wali kelas</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-700">
                                {{ $k->siswas->count() }} Siswa
                            </td>
                            <td class="py-3.5 px-4">
                                @if($k->jadwal_url)
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-16 h-11 rounded-xl border border-slate-200 overflow-hidden bg-slate-50 cursor-pointer hover:ring-2 hover:ring-blue-500 transition-all shrink-0"
                                        @click="modalPreview = true; previewUrl = '{{ $k->jadwal_url }}'; previewTitle = 'Jadwal Kelas {{ $k->nama_kelas }}'">
                                        <img src="{{ $k->jadwal_url }}" alt="Preview" class="w-full h-full object-cover">
                                    </div>
                                    <button
                                        type="button"
                                        @click="modalPreview = true; previewUrl = '{{ $k->jadwal_url }}'; previewTitle = 'Jadwal Kelas {{ $k->nama_kelas }}'"
                                        class="text-xs font-semibold text-blue-600 hover:underline flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        Perbesar
                                    </button>
                                </div>
                                @else
                                <span class="inline-flex items-center gap-1 text-[11px] text-amber-700 font-bold bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200/80">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    Belum Diunggah
                                </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right pr-6 whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button
                                        type="button"
                                        @click="openUploadKelas('{{ $k->id }}', 'Kelas {{ $k->nama_kelas }}')"
                                        class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold rounded-xl border border-blue-100 transition-colors flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                        </svg>
                                        <span>{{ $k->jadwal_url ? 'Ganti Foto' : 'Upload Foto' }}</span>
                                    </button>

                                    @if($k->jadwal_url)
                                    <form action="{{ route('admin.jadwal.destroy_kelas', $k->id) }}" method="POST" data-confirm="Apakah Anda yakin ingin menghapus foto jadwal kelas {{ $k->nama_kelas }}?" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-colors" title="Hapus Foto Jadwal">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-slate-400">
                                Belum ada data kelas terdaftar. Silakan buat data kelas terlebih dahulu.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ==================== TAB 2: JADWAL GURU ==================== -->
    <div x-show="activeTab === 'guru'" x-transition class="space-y-4" style="display: none;">
        
        <div class="space-y-4">
            @forelse($gurus as $g)
            @php
                $mapelsData = $g->guruMapels->map(function($gm) {
                    return [
                        'id' => $gm->id,
                        'nama_mapel' => $gm->mapel->nama_mapel ?? ('Mapel #' . $gm->id),
                        'has_jadwal' => !empty($gm->jadwal),
                        'jadwal_url' => $gm->jadwal_url,
                    ];
                })->values();
            @endphp
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden p-5 space-y-4">
                <!-- Teacher Header Card -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-black text-xs flex items-center justify-center shadow-xs shrink-0">
                            {{ strtoupper(substr($g->nama, 0, 2)) }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-slate-900 text-sm sm:text-base">{{ $g->nama }}</h3>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $g->guruMapels->count() > 0 ? 'bg-blue-50 text-blue-700 border border-blue-100' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $g->guruMapels->count() }} Mata Pelajaran
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-0.5">NUPTK: {{ $g->nuptk }}</p>
                        </div>
                    </div>
                    <div>
                        <button
                            type="button"
                            @click="openUploadGuru('{{ $g->id }}', 'Guru {{ addslashes($g->nama) }}', {{ json_encode($mapelsData) }})"
                            class="w-full sm:w-auto px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-500/20 transition-all hover:-translate-y-0.5 flex items-center justify-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>Upload / Tambah Jadwal Mapel</span>
                        </button>
                    </div>
                </div>

                <!-- Subjects & Specific Schedules List -->
                @if($g->guruMapels->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5">
                        @foreach($g->guruMapels as $gm)
                        <div class="rounded-2xl border {{ $gm->jadwal_url ? 'border-blue-100 bg-blue-50/20' : 'border-slate-200/80 bg-slate-50/40' }} p-4 flex flex-col justify-between space-y-3">
                            <div>
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-blue-700 bg-blue-50 border border-blue-100 px-2 py-0.5 rounded-md">
                                            Mata Pelajaran
                                        </span>
                                        <h4 class="font-bold text-slate-900 text-xs sm:text-sm mt-1.5">{{ $gm->mapel->nama_mapel ?? '-' }}</h4>
                                    </div>
                                    @if($gm->jadwal_url)
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Ada Jadwal
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full bg-amber-50 text-amber-700 border border-amber-200">
                                            Belum Ada Jadwal
                                        </span>
                                    @endif
                                </div>

                                <!-- Class Allocations for this Subject -->
                                <div class="mt-2 text-xs text-slate-500">
                                    <span class="font-semibold text-slate-600 text-[11px]">Alokasi Kelas:</span>
                                    @if($gm->pengampuKelases->count() > 0)
                                        <div class="flex flex-wrap gap-1 mt-1">
                                            @foreach($gm->pengampuKelases as $pk)
                                                <span class="px-2 py-0.5 rounded-md bg-white text-slate-700 text-[10px] border border-slate-200 font-semibold shadow-2xs">
                                                    {{ $pk->kelas->nama_kelas ?? '-' }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="italic text-slate-400 block mt-0.5 text-[11px]">Belum dialokasikan ke kelas</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Photo Schedule Preview & Actions -->
                            <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-2">
                                @if($gm->jadwal_url)
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-12 h-9 rounded-lg border border-slate-200 overflow-hidden bg-slate-50 cursor-pointer hover:ring-2 hover:ring-blue-500 transition-all shrink-0"
                                            @click="modalPreview = true; previewUrl = '{{ $gm->jadwal_url }}'; previewTitle = 'Jadwal {{ $gm->mapel->nama_mapel ?? 'Mapel' }} - {{ $g->nama }}'">
                                            <img src="{{ $gm->jadwal_url }}" alt="Preview" class="w-full h-full object-cover">
                                        </div>
                                        <button
                                            type="button"
                                            @click="modalPreview = true; previewUrl = '{{ $gm->jadwal_url }}'; previewTitle = 'Jadwal {{ $gm->mapel->nama_mapel ?? 'Mapel' }} - {{ $g->nama }}'"
                                            class="text-xs font-semibold text-blue-600 hover:underline">
                                            Lihat Foto
                                        </button>
                                    </div>
                                @else
                                    <span class="text-[11px] text-slate-400 italic">Tidak ada foto</span>
                                @endif

                                <div class="flex items-center gap-1.5">
                                    <button
                                        type="button"
                                        @click="openUploadGuru('{{ $g->id }}', 'Guru {{ addslashes($g->nama) }}', {{ json_encode($mapelsData) }}, '{{ $gm->id }}')"
                                        class="px-2.5 py-1 bg-white hover:bg-slate-50 text-slate-700 text-[11px] font-semibold rounded-lg border border-slate-200/80 transition-colors shadow-2xs">
                                        {{ $gm->jadwal_url ? 'Ganti Foto' : 'Upload' }}
                                    </button>

                                    @if($gm->jadwal_url)
                                    <form action="{{ route('admin.jadwal.destroy_guru', $gm->id) }}" method="POST" data-confirm="Apakah Anda yakin ingin menghapus foto jadwal mata pelajaran {{ $gm->mapel->nama_mapel ?? '' }} untuk guru {{ $g->nama }}?" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus Foto Jadwal Mapel Ini">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-4 rounded-xl border border-dashed border-slate-200 bg-slate-50/60 text-center">
                        <p class="text-xs text-slate-500 font-medium">Guru ini belum memiliki penugasan mata pelajaran.</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Klik tombol <strong>"Upload / Tambah Jadwal Mapel"</strong> di atas untuk langsung mengunggah foto jadwal sekaligus memilih mata pelajaran.</p>
                    </div>
                @endif
            </div>
            @empty
            <div class="bg-white rounded-2xl border border-slate-200 p-10 text-center text-slate-400">
                Belum ada data guru terdaftar.
            </div>
            @endforelse
        </div>
    </div>

    <!-- ==================== MODAL UPLOAD FOTO ==================== -->
    <div
        x-show="uploadModalOpen"
        style="display: none;"
        class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
        @click.self="uploadModalOpen = false; filePreview = null">
        <div class="max-w-md w-full bg-white rounded-3xl shadow-2xl p-6 space-y-5 border border-slate-100">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900">
                    Upload Foto Jadwal <span x-text="selectedName" class="text-blue-600 font-extrabold"></span>
                </h3>
                <button @click="uploadModalOpen = false; filePreview = null" class="p-1.5 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Form Upload Kelas -->
            <form
                x-show="uploadType === 'kelas'"
                action="{{ route('admin.jadwal.upload_kelas') }}"
                method="POST"
                enctype="multipart/form-data"
                class="space-y-4">
                @csrf
                <input type="hidden" name="kelas_id" :value="selectedId">

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">File Foto Jadwal (JPG, PNG, WEBP, Maks. 3MB)</label>
                    <input
                        type="file"
                        name="foto_jadwal"
                        required
                        accept="image/jpeg,image/png,image/jpg,image/webp"
                        @change="validateJadwalFileInput($event, $data)"
                        class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-slate-200 rounded-xl p-2 bg-slate-50/50">
                </div>

                <!-- Live Preview Image Box -->
                <template x-if="filePreview">
                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 text-center space-y-1.5">
                        <p class="text-[11px] font-bold text-slate-500">Preview Foto Yang Dipilih:</p>
                        <img :src="filePreview" alt="Live Preview" class="max-h-48 mx-auto rounded-xl object-contain shadow-2xs border border-slate-100">
                    </div>
                </template>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="uploadModalOpen = false; filePreview = null" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-500/20 transition-all hover:-translate-y-0.5">
                        Unggah Foto Jadwal Kelas
                    </button>
                </div>
            </form>

            <!-- Form Upload Guru (Dengan Pilihan Spesifik Mapel) -->
            <form
                x-show="uploadType === 'guru'"
                action="{{ route('admin.jadwal.upload_guru') }}"
                method="POST"
                enctype="multipart/form-data"
                class="space-y-4"
                style="display: none;">
                @csrf
                <input type="hidden" name="guru_id" :value="selectedId">

                <!-- Pilihan Spesifik Mapel Guru -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Pilih Mata Pelajaran Yang Diampu <span class="text-rose-500">*</span>
                    </label>

                    <div class="space-y-1.5 max-h-48 overflow-y-auto p-0.5">
                        <template x-for="item in teacherMapels" :key="item.id">
                            <label 
                                @click="selectedGuruMapelId = item.id"
                                class="flex items-center justify-between p-2.5 rounded-xl border cursor-pointer transition-all text-xs"
                                :class="selectedGuruMapelId == item.id ? 'border-blue-600 bg-blue-50/70 font-bold text-blue-950 ring-1 ring-blue-500 shadow-2xs' : 'border-slate-200 bg-white hover:bg-slate-50 text-slate-700'">
                                <div class="flex items-center gap-2.5">
                                    <input 
                                        type="radio" 
                                        name="guru_mapel_id" 
                                        :value="item.id" 
                                        x-model="selectedGuruMapelId" 
                                        class="w-4 h-4 text-blue-600 focus:ring-blue-500 border-slate-300">
                                    <span x-text="item.nama_mapel"></span>
                                </div>
                                <span 
                                    class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                    :class="item.has_jadwal ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-amber-50 text-amber-800 border border-amber-200'"
                                    x-text="item.has_jadwal ? 'Sudah Ada Foto' : 'Belum Ada Foto'">
                                </span>
                            </label>
                        </template>

                        <label 
                            @click="selectedGuruMapelId = 'new'"
                            class="flex items-center justify-between p-2.5 rounded-xl border cursor-pointer transition-all text-xs"
                            :class="selectedGuruMapelId === 'new' ? 'border-blue-600 bg-blue-50/70 font-bold text-blue-950 ring-1 ring-blue-500 shadow-2xs' : 'border-slate-200 bg-white hover:bg-slate-50 text-slate-700'">
                            <div class="flex items-center gap-2.5">
                                <input 
                                    type="radio" 
                                    name="guru_mapel_id" 
                                    value="new" 
                                    x-model="selectedGuruMapelId" 
                                    class="w-4 h-4 text-blue-600 focus:ring-blue-500 border-slate-300">
                                <span>+ Tambah & Pilih Mata Pelajaran Lain</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Form Tambahan Jika Memilih Mapel Baru -->
                <div x-show="selectedGuruMapelId === 'new'" x-transition class="p-3.5 bg-blue-50/70 border border-blue-200 rounded-2xl space-y-2">
                    <label class="block text-xs font-bold text-blue-900 uppercase">
                        Pilih Mata Pelajaran Baru dari Master Mapel:
                    </label>
                    <select
                        name="mapel_id"
                        class="w-full text-xs font-semibold rounded-xl border border-blue-200 p-2.5 bg-white text-slate-800 focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Pilih Mata Pelajaran --</option>
                        @foreach($mapels as $mpl)
                            <option value="{{ $mpl->id }}">{{ $mpl->nama_mapel }} (KKM: {{ $mpl->kkm }})</option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-blue-600">Mata pelajaran yang dipilih akan langsung ditambahkan ke daftar keahlian guru ini.</p>
                </div>

                <!-- Input File Foto Jadwal -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">File Foto Jadwal Mengajar (JPG, PNG, WEBP, Maks. 3MB)</label>
                    <input
                        type="file"
                        name="foto_jadwal"
                        required
                        accept="image/jpeg,image/png,image/jpg,image/webp"
                        @change="validateJadwalFileInput($event, $data)"
                        class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-slate-200 rounded-xl p-2 bg-slate-50/50">
                </div>

                <!-- Live Preview Image Box -->
                <template x-if="filePreview">
                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 text-center space-y-1.5">
                        <p class="text-[11px] font-bold text-slate-500">Preview Foto Yang Dipilih:</p>
                        <img :src="filePreview" alt="Live Preview" class="max-h-48 mx-auto rounded-xl object-contain shadow-2xs border border-slate-100">
                    </div>
                </template>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="uploadModalOpen = false; filePreview = null" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-500/20 transition-all hover:-translate-y-0.5">
                        Simpan Foto Jadwal Mapel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL PREVIEW ZOOM FOTO ==================== -->
    <div
        x-show="modalPreview"
        style="display: none;"
        class="fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-xs flex items-center justify-center p-4"
        @click.self="modalPreview = false">
        <div class="relative max-w-4xl w-full bg-white rounded-3xl shadow-2xl overflow-hidden p-3 border border-slate-200">
            <div class="flex items-center justify-between p-3 border-b border-slate-100">
                <h4 class="font-bold text-slate-900 text-sm" x-text="previewTitle"></h4>
                <div class="flex items-center gap-2">
                    <a :href="previewUrl" download class="p-1.5 px-3 text-xs text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-xl flex items-center gap-1 font-bold transition-colors" title="Unduh Foto Asli">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span>Unduh</span>
                    </a>
                    <button @click="modalPreview = false" class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            <div class="p-4 text-center max-h-[80vh] overflow-auto">
                <img :src="previewUrl" :alt="previewTitle" class="mx-auto max-h-[70vh] object-contain rounded-2xl shadow-sm">
            </div>
        </div>
    </div>

</div>

<script>
function validateJadwalFileInput(event, alpine) {
    const file = event.target.files[0];
    if (!file) {
        if (alpine) alpine.filePreview = null;
        return;
    }
    const maxSize = 3 * 1024 * 1024; // 3MB in bytes
    if (file.size > maxSize) {
        const actualMb = (file.size / (1024 * 1024)).toFixed(2);
        Swal.fire({
            icon: 'error',
            title: 'Ukuran Foto Terlalu Besar!',
            html: '<div style="text-align:center; font-size:13px; color:#475569; line-height: 1.6;">' +
                  'Ukuran file yang Anda pilih adalah <b style="color:#e11d48;">' + actualMb + ' MB</b>.<br>' +
                  'Kapasitas maksimum yang diperbolehkan adalah <b style="color:#2563eb;">3 MB</b>.<br><br>' +
                  'Silakan kompres foto atau pilih file dengan ukuran lebih kecil.' +
                  '</div>',
            confirmButtonColor: '#2563eb',
            confirmButtonText: 'Tutup & Pilih Ulang'
        });
        event.target.value = '';
        if (alpine) alpine.filePreview = null;
        return;
    }
    if (alpine) {
        alpine.filePreview = URL.createObjectURL(file);
    }
}
</script>
@endsection