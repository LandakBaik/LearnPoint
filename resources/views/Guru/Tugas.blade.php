@extends('layouts.app')

@section('title', 'Daftar Tugas')

@section('content')
<div class="space-y-6" x-data="{ 
    searchQuery: '',
    showModal: false, 
    showEditModal: false,
    editData: {},
    fileName: '',
    fileSize: '',
    hasFile: false,

    handleFileChange(event) {
        const file = event.target.files[0];
        if (file) {
            this.fileName = file.name;
            this.fileSize = (file.size / (1024 * 1024)).toFixed(1) + ' MB • Siap diunggah';
            this.hasFile = true;
        }
}">

    <!-- Header Title & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Tugas</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola tugas, pengumpulan, dan penilaian siswa SMP terpadu.</p>
        </div>
        <div>
            <button
                @click="showModal = true"
                type="button"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 text-white font-semibold text-sm hover:bg-blue-700 transition-colors shadow-sm cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                Buat Tugas
            </button>
        </div>
    </div>

    <!-- Stats Summary Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Card 1: Total Tugas -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500">Total Tugas</p>
                <h3 class="text-2xl font-extrabold text-gray-900 mt-1">{{ $totalTugas ?? 0 }}</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
        </div>
        
        <!-- Card 2: Aktif -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500">Aktif</p>
                <h3 class="text-2xl font-extrabold text-gray-900 mt-1">{{ $totalAktif }}</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <!-- Card 3: Perlu Dinilai -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500">Perlu Dinilai</p>
                <h3 class="text-2xl font-extrabold text-gray-900 mt-1">{{ $perluDinilai ?? 0 }}</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </div>
        </div>

        <!-- Card 4: Deadline Terdekat -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500">Deadline Terdekat</p>
                <h3 class="text-xl font-extrabold text-gray-900 mt-1">{{ $deadlineTerdekat ?? '-' }}</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-red-50 text-red-500 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm flex flex-col md:flex-row gap-3 items-center justify-between">

        <!-- Search Input -->
        <div class="relative w-full md:w-80">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </span>
            <input
                x-model="searchQuery"
                type="text"
                placeholder="Cari tugas berdasarkan judul..."
                class="w-full pl-10 pr-4 py-2 rounded-full border border-gray-200 bg-gray-50/50 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all placeholder-gray-400">
        </div>

        <!-- Filter Dropdowns -->
        <div class="flex items-center gap-2.5 w-full md:w-auto flex-wrap md:flex-nowrap">

            <!-- Filter Kelas -->
            <select x-model="selectedKelas" class="rounded-full border border-gray-200 px-4 py-2 text-xs font-medium bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                <option value="">Semua Kelas</option>
                @foreach($kelases as $kelas)
                <option value="{{ $kelas->id }}">{{ $kelas->nama_kelas }}</option>
                @endforeach
            </select>

            <!-- Filter Mata Pelajaran -->
            <select x-model="selectedMapel" class="rounded-full border border-gray-200 px-4 py-2 text-xs font-medium bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                <option value="">Semua Mata Pelajaran</option>
                @foreach($mapels as $mapel)
                <option value="{{ $mapel->id }}">{{ $mapel->nama_mapel }}</option>
                @endforeach
            </select>

            <!-- Filter Status -->
            <select x-model="selectedStatus" class="rounded-full border border-gray-200 px-4 py-2 text-xs font-medium bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                <option value="">Semua Status</option>
                <option value="aktif">Aktif</option>
                <option value="perlu_dinilai">Perlu Dinilai</option>
                <option value="selesai">Selesai</option>
            </select>

            <!-- Urutan (Sort) -->
            <select x-model="sortBy" class="rounded-full border border-gray-200 px-4 py-2 text-xs font-medium bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                <option value="terbaru">Urutan: Terbaru</option>
                <option value="terlama">Urutan: Terlama</option>
                <option value="deadline">Urutan: Deadline</option>
            </select>

            <!-- Tombol Reset Filter -->
            <button
                @click="searchQuery = ''; selectedKelas = ''; selectedMapel = ''; selectedStatus = ''; sortBy = 'terbaru';"
                type="button"
                class="w-9 h-9 rounded-full border border-gray-200 flex items-center justify-center text-gray-500 hover:bg-gray-50 transition-colors shrink-0 cursor-pointer"
                title="Reset Filter">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Data Table Container -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <!-- Table Top Header -->
        <div class="px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
            <div class="flex items-center gap-2.5">
                <h3 class="text-base font-bold text-gray-900">Daftar Tugas</h3>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-600 border border-blue-100">
                    {{ $tugases->count() }} Aktif Ditampilkan
                </span>
            </div>
            <p class="text-xs text-gray-400 font-medium">Tahun Ajaran 2026/2027 &bull; Semester Ganjil</p>
        </div>
        
        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-200 text-xs font-bold text-gray-500 uppercase tracking-wider">
                        <th class="py-4 px-6">Tugas & Detail Info</th>
                        <th class="py-4 px-6">Penilaian</th>
                        <th class="py-4 px-6">Pengumpulan & Progres</th>
                        <th class="py-4 px-6 text-right">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse ($tugases as $tugas)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <!-- 1. Tugas & Detail Info -->
                        <td class="py-4 px-6">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-600 text-[11px] font-bold">
                                        {{ $tugas->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? 'Mapel' }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 text-[11px] font-bold">
                                        {{ $tugas->pengampuKelas?->kelas?->nama_kelas ?? 'Kelas' }}
                                    </span>
                                </div>
                                <h4 class="font-bold text-gray-800 text-sm">{{ $tugas->judul }}</h4>
                                <p class="text-xs text-gray-500 line-clamp-1">{{ $tugas->deskripsi ?? 'Tidak ada deskripsi' }}</p>

                                @if ($tugas->file_lampiran)
                                <a href="{{ asset('storage/' . $tugas->file_lampiran) }}" target="_blank" class="inline-flex items-center gap-1 text-xs text-blue-600 font-semibold hover:underline pt-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Berkas Lampiran
                                </a>
                                @endif
                            </div>
                        </td>

                        <!-- 2. Penilaian (Dinamis) -->
                        <td class="py-4 px-6">
                            @php
                            $totalKumpul = $tugas->pengumpulans_count ?? 0;
                            $totalDinilai = $tugas->pengumpulans_dinilai_count ?? 0;
                            @endphp

                            @if ($totalKumpul == 0)
                            <!-- Jika belum ada siswa yang mengumpulkan -->
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                                Belum Ada Pengumpulan
                            </span>
                            @elseif ($totalDinilai == $totalKumpul)
                            <!-- Jika semua yang mengumpulkan sudah dinilai -->
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-600 border border-emerald-200/60">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                {{ $totalDinilai }} Selesai Dinilai
                            </span>
                            @elseif ($totalDinilai > 0)
                            <!-- Jika baru sebagian yang dinilai -->
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-600 border border-blue-200/60">
                                {{ $totalDinilai }} / {{ $totalKumpul }} Dinilai
                            </span>
                            @else
                            <!-- Jika ada pengumpulan tapi belum ada yang dinilai sama sekali -->
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200/60">
                                Perlu Dinilai
                            </span>
                            @endif
                        </td>

                        <!-- 3. Pengumpulan & Progres -->
                        <td class="py-4 px-6">
                            <div class="space-y-1">
                                <div class="flex items-center gap-1.5 text-xs font-semibold text-gray-700">
                                    <span class="text-gray-900 font-bold">{{ $tugas->pengumpulans_count ?? 0 }}</span>
                                    <span class="text-gray-400">Siswa Mengumpulkan</span>
                                </div>
                                <div class="w-32 bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-blue-600 h-1.5 rounded-full" style="width: 0%"></div>
                                </div>
                            </div>
                        </td>

                        <!-- 4. Aksi -->
                        <td class="py-4 px-6 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <!-- Tombol Detail (Membuka Halaman Detail & Penilaian Siswa) -->
                                <a href="{{ route('guru.tugas.show', $tugas->id) }}" class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-lg text-xs font-bold transition-colors inline-flex items-center">
                                    Detail
                                </a>

                                <!-- Tombol Edit (Membuka Modal Edit yang di dalamnya ada tombol Hapus) -->
                                <button
                                    type="button"
                                    @click="$dispatch('open-edit-modal', {
                id: {{ $tugas->id }},
                judul: '{{ addslashes($tugas->judul) }}',
                deskripsi: '{{ addslashes($tugas->deskripsi ?? '') }}',
                deadline: '{{ \Carbon\Carbon::parse($tugas->deadline)->format('Y-m-d\TH:i') }}',
                pengampu_kelas_id: {{ $tugas->pengampu_kelas_id }},
                tipe: '{{ $tugas->tipe }}'
            })"
                                    class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition-colors">
                                    Edit
                                </button>

                                <!-- Form Delete Tersembunyi (Dipanggil via JS saat tombol "Hapus Tugas" di modal Edit diklik) -->
                                <form id="delete-form-{{ $tugas->id }}" action="{{ route('guru.tugas.destroy', $tugas->id) }}" method="POST" class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-16 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 rounded-2xl bg-gray-100 text-gray-400 flex items-center justify-center mb-3">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                    </svg>
                                </div>
                                <p class="text-base font-bold text-gray-800">Belum Ada Data Tugas</p>
                                <p class="text-xs text-gray-400 mt-1">Tugas yang Anda buat akan ditampilkan di sini secara terstruktur.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Rows Count Footer -->
        <div class="px-6 py-4 border-t border-gray-200 bg-white flex items-center justify-between">
            <p class="text-xs text-gray-500">
                Menampilkan <span class="font-bold text-gray-800">{{ $tugases->count() }}</span> total tugas
            </p>
        </div>
    </div>

    <!-- Modal Popup Buat Tugas Baru -->
    <div
        x-show="showModal"
        x-cloak
        @keydown.escape.window="showModal = false"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">

        <!-- Modal Backdrop Overlay -->
        <div
            x-show="showModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="showModal = false"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

        <!-- Modal Dialog Box -->
        <div
            x-show="showModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-4"
            class="relative w-full max-w-xl bg-white rounded-3xl shadow-2xl border border-slate-100 z-10 overflow-hidden">

            <!-- Modal Header -->
            <div class="p-6 sm:p-7 border-b border-slate-100 flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">Buat Tugas Baru</h3>
                    <p class="text-xs text-slate-500 mt-1">Isi formulir penugasan untuk siswa kelas yang Anda ampu.</p>
                </div>
                <button
                    @click="showModal = false"
                    type="button"
                    class="w-8 h-8 rounded-full text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition-colors shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Single Form Wrapper -->
            <form action="{{ route('guru.tugas.store') }}" method="POST" enctype="multipart/form-data" class="p-6 sm:p-7 space-y-5">
                @csrf

                <!-- Hidden Input Tipe Tugas agar Lolos Validasi Controller -->
                <input type="hidden" name="tipe" value="upload">

                <!-- Field 1: Judul Tugas -->
                <div class="space-y-1.5">
                    <label for="judul" class="block text-xs font-bold text-slate-700">Judul Tugas <span class="text-red-500">*</span></label>
                    <input
                        type="text"
                        name="judul"
                        id="judul"
                        required
                        placeholder="Masukkan judul tugas (contoh: Sistem Persamaan Linear)"
                        class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-slate-400">
                </div>

                <!-- Field 2: Mata Pelajaran & Kelas Sasaran -->
                <div class="space-y-1.5">
                    <label for="pengampu_kelas_id" class="block text-xs font-bold text-slate-700">Mata Pelajaran & Kelas <span class="text-red-500">*</span></label>
                    <select
                        name="pengampu_kelas_id"
                        id="pengampu_kelas_id"
                        required
                        class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all cursor-pointer">
                        <option value="" disabled selected>-- Pilih Mapel & Kelas --</option>
                        @foreach($pengampuKelases as $pk)
                        <option value="{{ $pk->id }}">
                            {{ $pk->guruMapel?->mapel?->nama_mapel ?? 'Mapel' }} - {{ $pk->kelas?->nama_kelas ?? 'Kelas' }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Field 3: Deskripsi Tugas -->
                <div class="space-y-1.5">
                    <label for="deskripsi" class="block text-xs font-bold text-slate-700">Deskripsi Tugas</label>
                    <textarea
                        name="deskripsi"
                        id="deskripsi"
                        rows="3"
                        placeholder="Tambahkan penjelasan konteks tugas atau ringkasan capaian pembelajaran..."
                        class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-slate-400"></textarea>
                </div>

                <!-- Field 4: Lampiran Lembar Kerja / Dokumen Pendukung -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700">Lampiran Lembar Kerja / Dokumen Pendukung</label>
                    <div class="border-2 border-dashed border-slate-200 bg-slate-50/40 rounded-2xl p-5 text-center space-y-1.5 hover:bg-slate-50 transition-colors cursor-pointer">
                        <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mx-auto mb-1 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                        </div>
                        <p class="text-xs font-semibold text-slate-700">
                            Tarik & lepaskan file lembar kerja di sini, atau <label for="file_tugas_input" class="text-blue-600 font-bold hover:underline cursor-pointer">Pilih Berkas</label>
                        </p>
                        <p class="text-[11px] text-slate-400">Mendukung berkas: PDF, DOCX, JPG, PNG (Ukuran maksimal: 10 MB)</p>
                        <input type="file" id="file_tugas_input" name="file_tugas_input" accept=".pdf,.docx,.jpg,.png" @change="handleFileChange($event)" class="hidden">
                    </div>

                    <!-- Dynamic Upload Preview Pill -->
                    <template x-if="hasFile">
                        <div class="bg-indigo-50/50 border border-indigo-100/80 rounded-2xl p-3 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center shrink-0 font-bold">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V7.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 1H7a2 2 0 00-2 2v16a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-800 truncate" x-text="fileName"></p>
                                    <p class="text-[11px] text-slate-500 mt-0.5" x-text="fileSize"></p>
                                </div>
                            </div>
                            <button @click="hasFile = false; document.getElementById('file_tugas_input').value = ''" type="button" class="text-slate-400 hover:text-slate-600 p-1 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>

                <!-- Field 5: Batas Waktu Pengumpulan (Deadline) -->
                <div class="space-y-1.5">
                    <label for="deadline" class="block text-xs font-bold text-slate-700">Batas Waktu Pengumpulan (Deadline) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input
                            type="datetime-local"
                            name="deadline"
                            id="deadline"
                            required
                            class="w-full pl-4 pr-10 py-3 rounded-2xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button
                        @click="showModal = false"
                        type="button"
                        class="px-6 py-2.5 rounded-full border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition-colors">
                        Batalkan
                    </button>
                    <button
                        type="submit"
                        class="px-7 py-2.5 rounded-full bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition-colors shadow-sm">
                        Publikasikan Tugas
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Popup Edit & Hapus Tugas -->
    <div
        x-data="{ showEditModal: false, editData: {} }"
        @open-edit-modal.window="showEditModal = true; editData = $event.detail"
        x-show="showEditModal"
        x-cloak
        @keydown.escape.window="showEditModal = false"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">

        <!-- Modal Backdrop Overlay -->
        <div
            x-show="showEditModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="showEditModal = false"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

        <!-- Modal Dialog Box -->
        <div
            x-show="showEditModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-4"
            class="relative w-full max-w-xl bg-white rounded-3xl shadow-2xl border border-slate-100 z-10 overflow-hidden">

            <!-- Modal Header -->
            <div class="p-6 sm:p-7 border-b border-slate-100 flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">Edit Tugas</h3>
                    <p class="text-xs text-slate-500 mt-1">Perbarui rincian informasi tugas atau hapus jika tidak diperlukan.</p>
                </div>
                <button
                    @click="showEditModal = false"
                    type="button"
                    class="w-8 h-8 rounded-full text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition-colors shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Form Edit Body -->
            <form :action="'/guru/tugas/' + editData.id" method="POST" enctype="multipart/form-data" class="p-6 sm:p-7 space-y-5">
                @csrf
                @method('PUT')

                <input type="hidden" name="tipe" :value="editData.tipe || 'upload'">

                <!-- Field 1: Judul Tugas -->
                <div class="space-y-1.5">
                    <label for="edit_judul" class="block text-xs font-bold text-slate-700">Judul Tugas <span class="text-red-500">*</span></label>
                    <input
                        type="text"
                        name="judul"
                        id="edit_judul"
                        :value="editData.judul"
                        required
                        placeholder="Masukkan judul tugas"
                        class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                </div>

                <!-- Field 2: Mata Pelajaran & Kelas Sasaran -->
                <div class="space-y-1.5">
                    <label for="edit_pengampu_kelas_id" class="block text-xs font-bold text-slate-700">Mata Pelajaran & Kelas <span class="text-red-500">*</span></label>
                    <select
                        name="pengampu_kelas_id"
                        id="edit_pengampu_kelas_id"
                        required
                        class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all cursor-pointer">
                        @foreach($pengampuKelases as $pk)
                        <option value="{{ $pk->id }}" :selected="editData.pengampu_kelas_id == {{ $pk->id }}">
                            {{ $pk->guruMapel?->mapel?->nama_mapel ?? 'Mapel' }} - {{ $pk->kelas?->nama_kelas ?? 'Kelas' }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Field 3: Deskripsi Tugas -->
                <div class="space-y-1.5">
                    <label for="edit_deskripsi" class="block text-xs font-bold text-slate-700">Deskripsi Tugas</label>
                    <textarea
                        name="deskripsi"
                        id="edit_deskripsi"
                        rows="3"
                        x-text="editData.deskripsi"
                        placeholder="Tambahkan penjelasan tugas..."
                        class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"></textarea>
                </div>

                <!-- Field 4: Ganti Lampiran (Opsional) -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700">Ganti Lampiran File (Opsional)</label>
                    <div class="border-2 border-dashed border-slate-200 bg-slate-50/40 rounded-2xl p-4 text-center cursor-pointer hover:bg-slate-50 transition-colors">
                        <input type="file" name="file_tugas_input" accept=".pdf,.docx,.jpg,.png" class="text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                </div>

                <!-- Field 5: Deadline -->
                <div class="space-y-1.5">
                    <label for="edit_deadline" class="block text-xs font-bold text-slate-700">Batas Waktu Pengumpulan (Deadline) <span class="text-red-500">*</span></label>
                    <input
                        type="datetime-local"
                        name="deadline"
                        id="edit_deadline"
                        :value="editData.deadline"
                        required
                        class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                </div>

                <!-- Action Buttons Footer (Tombol Hapus Gabung di Sini) -->
                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <!-- Tombol Hapus (Kiri) -->
                    <button
                        type="button"
                        @click="if(confirm('Apakah Anda yakin ingin menghapus tugas ini secara permanen?')) { document.getElementById('delete-form-' + editData.id).submit(); }"
                        class="px-5 py-2.5 rounded-full bg-red-50 hover:bg-red-100 text-red-600 text-sm font-semibold transition-colors flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Hapus Tugas
                    </button>

                    <!-- Tombol Batal & Simpan (Kanan) -->
                    <div class="flex items-center gap-3">
                        <button
                            @click="showEditModal = false"
                            type="button"
                            class="px-6 py-2.5 rounded-full border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition-colors">
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="px-7 py-2.5 rounded-full bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition-colors shadow-sm">
                            Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
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