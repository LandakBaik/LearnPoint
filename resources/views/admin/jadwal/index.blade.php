@extends('layouts.app')

@section('title', 'Kelola Jadwal Pelajaran (Foto)')

@section('content')
<div class="space-y-6" x-data="{ 
    activeTab: '{{ $activeTab ?? 'kelas' }}',
    modalPreview: false,
    previewUrl: '',
    previewTitle: '',
    uploadModalOpen: false,
    uploadType: 'kelas',
    selectedId: '',
    selectedName: ''
}">

    <!-- Header Title -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Kelola Jadwal (Foto)</h1>
            <p class="text-sm text-gray-500 mt-1">
                Unggah dan perbarui foto jadwal resmi untuk <span class="font-semibold text-gray-800">Jadwal Kelas (dilihat oleh Siswa)</span> dan <span class="font-semibold text-gray-800">Jadwal Guru (dilihat oleh Guru)</span>.
            </p>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex border-b border-gray-200 bg-white px-6 pt-3 rounded-2xl border shadow-sm">
        <button
            type="button"
            @click="activeTab = 'kelas'"
            :class="activeTab === 'kelas' ? 'border-indigo-600 text-indigo-600 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium'"
            class="py-3 px-4 border-b-2 text-sm flex items-center gap-2 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m3 0h1m-1-4h.01M9 16h.01M9 12h.01M9 8h.01M15 16h.01M15 12h.01M15 8h.01" />
            </svg>
            <span>Jadwal Kelas (Untuk Siswa)</span>
            <span class="ml-1.5 px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">{{ $kelases->count() }}</span>
        </button>

        <button
            type="button"
            @click="activeTab = 'guru'"
            :class="activeTab === 'guru' ? 'border-indigo-600 text-indigo-600 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium'"
            class="py-3 px-4 border-b-2 text-sm flex items-center gap-2 transition-all ml-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <span>Jadwal Guru (Untuk Masing-masing Guru)</span>
            <span class="ml-1.5 px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">{{ $gurus->count() }}</span>
        </button>
    </div>

    <!-- ==================== TAB 1: JADWAL KELAS ==================== -->
    <div x-show="activeTab === 'kelas'" x-transition class="space-y-4">
        <!-- <div class="bg-blue-50/60 border border-blue-200 p-4 rounded-2xl flex items-start gap-3">
            <svg class="w-5 h-5 text-blue-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-xs text-blue-900 leading-relaxed">
                Foto jadwal kelas yang Anda unggah di sini akan langsung tersimpan di data keanggotaan kelas (<code class="bg-blue-100 px-1 py-0.5 rounded font-mono">anggota_kelases</code>) dan secara otomatis dapat dilihat oleh seluruh siswa di kelas tersebut.
            </p>
        </div> -->

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-200 text-[11px] font-bold uppercase tracking-wider text-gray-500">
                            <th class="py-3.5 px-4 sm:px-6">Kelas & Tingkat</th>
                            <th class="py-3.5 px-4">Wali Kelas</th>
                            <th class="py-3.5 px-4">Total Siswa</th>
                            <th class="py-3.5 px-4">Preview Foto Jadwal</th>
                            <th class="py-3.5 px-4 text-right pr-6">Aksi Upload / Kelola</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        @forelse($kelases as $k)
                        <tr class="hover:bg-indigo-50/30 transition-colors">
                            <td class="py-4 px-4 sm:px-6 font-bold text-gray-900">
                                <span class="block">{{ $k->nama_kelas }}</span>
                                <span class="text-xs font-normal text-gray-500">Tingkat {{ $k->tingkatan }}</span>
                            </td>
                            <td class="py-4 px-4">
                                @if($k->guru)
                                <p class="font-medium text-xs text-gray-800">{{ $k->guru->nama }}</p>
                                <p class="text-[10px] text-gray-400 font-mono">NIP: {{ $k->guru->nip }}</p>
                                @else
                                <span class="text-xs text-gray-400 italic">Belum ada wali kelas</span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-xs font-semibold text-gray-600">
                                {{ $k->siswas->count() }} Siswa
                            </td>
                            <td class="py-4 px-4">
                                @if($k->jadwal_url)
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-16 h-12 rounded-lg border border-gray-200 overflow-hidden bg-gray-50 cursor-pointer hover:ring-2 hover:ring-indigo-400 transition-all shrink-0"
                                        @click="modalPreview = true; previewUrl = '{{ $k->jadwal_url }}'; previewTitle = 'Jadwal Kelas {{ $k->nama_kelas }}'">
                                        <img src="{{ $k->jadwal_url }}" alt="Preview" class="w-full h-full object-cover">
                                    </div>
                                    <button
                                        type="button"
                                        @click="modalPreview = true; previewUrl = '{{ $k->jadwal_url }}'; previewTitle = 'Jadwal Kelas {{ $k->nama_kelas }}'"
                                        class="text-xs font-semibold text-indigo-600 hover:underline flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        Perbesar
                                    </button>
                                </div>
                                @else
                                <span class="inline-flex items-center gap-1 text-xs text-amber-600 font-medium bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    Belum Diunggah
                                </span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-right pr-6 whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- Tombol Buka Modal Upload -->
                                    <button
                                        type="button"
                                        @click="uploadModalOpen = true; uploadType = 'kelas'; selectedId = '{{ $k->id }}'; selectedName = 'Kelas {{ $k->nama_kelas }}'"
                                        class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold rounded-lg border border-indigo-200 transition-colors flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                        </svg>
                                        <span>{{ $k->jadwal_url ? 'Ganti Foto' : 'Upload Foto' }}</span>
                                    </button>

                                    @if($k->jadwal_url)
                                    <form action="{{ route('admin.jadwal.destroy_kelas', $k->id) }}" method="POST" data-confirm="Apakah Anda yakin ingin menghapus foto jadwal kelas {{ $k->nama_kelas }}?" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus Foto Jadwal">
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
                            <td colspan="5" class="py-10 text-center text-gray-400">
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
        <!-- <div class="bg-indigo-50/60 border border-indigo-200 p-4 rounded-2xl flex items-start gap-3">
            <svg class="w-5 h-5 text-indigo-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-xs text-indigo-900 leading-relaxed">
                Foto jadwal guru disimpan sebagai atribut pada data pengampu (<code class="bg-indigo-100 px-1 py-0.5 rounded font-mono">guru_mapels.jadwal</code>). Guru dapat melihat jadwal mengajar mereka secara langsung di halaman <strong>Jadwal Pembelajaran</strong> akun mereka.
            </p>
        </div> -->

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-200 text-[11px] font-bold uppercase tracking-wider text-gray-500">
                            <th class="py-3.5 px-4 sm:px-6">Nama Guru & NIP</th>
                            <th class="py-3.5 px-4">Mapel Diampu</th>
                            <th class="py-3.5 px-4">Foto Jadwal Mengajar</th>
                            <th class="py-3.5 px-4 text-right pr-6">Aksi Upload / Kelola</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        @forelse($gurus as $g)
                        <tr class="hover:bg-indigo-50/30 transition-colors">
                            <td class="py-4 px-4 sm:px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-indigo-500 to-blue-600 text-white font-bold text-xs flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr($g->nama, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900 leading-snug">{{ $g->nama }}</p>
                                        <p class="text-xs text-gray-500 font-mono">NIP: {{ $g->nip }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-xs">
                                @if($g->guruMapels->count() > 0)
                                <div class="flex flex-wrap gap-1">
                                    @foreach($g->guruMapels as $gm)
                                    <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-700 border border-gray-200">
                                        {{ $gm->mapel->nama_mapel ?? '-' }} ({{ $gm->kelas->nama_kelas ?? '-' }})
                                    </span>
                                    @endforeach
                                </div>
                                @else
                                <span class="text-gray-400 italic">Belum ada alokasi mapel</span>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                @if($g->jadwal_url)
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-16 h-12 rounded-lg border border-gray-200 overflow-hidden bg-gray-50 cursor-pointer hover:ring-2 hover:ring-indigo-400 transition-all shrink-0"
                                        @click="modalPreview = true; previewUrl = '{{ $g->jadwal_url }}'; previewTitle = 'Jadwal Mengajar {{ $g->nama }}'">
                                        <img src="{{ $g->jadwal_url }}" alt="Preview" class="w-full h-full object-cover">
                                    </div>
                                    <button
                                        type="button"
                                        @click="modalPreview = true; previewUrl = '{{ $g->jadwal_url }}'; previewTitle = 'Jadwal Mengajar {{ $g->nama }}'"
                                        class="text-xs font-semibold text-indigo-600 hover:underline flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        Perbesar
                                    </button>
                                </div>
                                @else
                                <span class="inline-flex items-center gap-1 text-xs text-amber-600 font-medium bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    Belum Diunggah
                                </span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-right pr-6 whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        type="button"
                                        @click="uploadModalOpen = true; uploadType = 'guru'; selectedId = '{{ $g->id }}'; selectedName = 'Guru {{ $g->nama }}'"
                                        class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold rounded-lg border border-indigo-200 transition-colors flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                        </svg>
                                        <span>{{ $g->jadwal_url ? 'Ganti Foto' : 'Upload Foto' }}</span>
                                    </button>

                                    @if($g->guruMapels->first() && $g->jadwal_url)
                                    <form action="{{ route('admin.jadwal.destroy_guru', $g->guruMapels->first()->id) }}" method="POST" data-confirm="Apakah Anda yakin ingin menghapus foto jadwal mengajar guru {{ $g->nama }}?" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus Foto Jadwal">
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
                            <td colspan="4" class="py-10 text-center text-gray-400">
                                Belum ada data guru terdaftar.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ==================== MODAL UPLOAD FOTO ==================== -->
    <div
        x-show="uploadModalOpen"
        style="display: none;"
        class="fixed inset-0 z-50 bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4"
        @click.self="uploadModalOpen = false">
        <div class="max-w-md w-full bg-white rounded-2xl shadow-2xl p-6 space-y-5">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="text-base font-bold text-gray-900">
                    Upload Foto Jadwal <span x-text="selectedName" class="text-indigo-600 font-extrabold"></span>
                </h3>
                <button @click="uploadModalOpen = false" class="p-1 text-gray-400 hover:text-gray-700 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Pilih File Foto Jadwal (JPG, PNG, WEBP, Maks. 3MB)</label>
                    <input
                        type="file"
                        name="foto_jadwal"
                        required
                        accept="image/jpeg,image/png,image/jpg,image/webp"
                        class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer border border-gray-200 rounded-xl p-2 bg-gray-50/50">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" @click="uploadModalOpen = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-md shadow-indigo-200">
                        Unggah Foto Jadwal
                    </button>
                </div>
            </form>

            <!-- Form Upload Guru -->
            <form
                x-show="uploadType === 'guru'"
                action="{{ route('admin.jadwal.upload_guru') }}"
                method="POST"
                enctype="multipart/form-data"
                class="space-y-4"
                style="display: none;">
                @csrf
                <input type="hidden" name="guru_id" :value="selectedId">

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Pilih File Foto Jadwal Mengajar (JPG, PNG, WEBP, Maks. 3MB)</label>
                    <input
                        type="file"
                        name="foto_jadwal"
                        required
                        accept="image/jpeg,image/png,image/jpg,image/webp"
                        class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer border border-gray-200 rounded-xl p-2 bg-gray-50/50">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" @click="uploadModalOpen = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-md shadow-indigo-200">
                        Unggah Foto Jadwal Guru
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL PREVIEW ZOOM FOTO ==================== -->
    <div
        x-show="modalPreview"
        style="display: none;"
        class="fixed inset-0 z-50 bg-gray-900/80 backdrop-blur-sm flex items-center justify-center p-4"
        @click.self="modalPreview = false">
        <div class="relative max-w-4xl w-full bg-white rounded-2xl shadow-2xl overflow-hidden p-2">
            <div class="flex items-center justify-between p-3 border-b border-gray-100">
                <h4 class="font-bold text-gray-800 text-sm" x-text="previewTitle"></h4>
                <div class="flex items-center gap-2">
                    <a :href="previewUrl" download class="p-1.5 text-xs text-indigo-600 hover:bg-indigo-50 rounded-lg flex items-center gap-1 font-semibold" title="Unduh Foto Asli">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span>Unduh</span>
                    </a>
                    <button @click="modalPreview = false" class="p-1 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            <div class="p-4 text-center max-h-[80vh] overflow-auto">
                <img :src="previewUrl" :alt="previewTitle" class="mx-auto max-h-[70vh] object-contain rounded-lg shadow-sm">
            </div>
        </div>
    </div>

</div>
@endsection