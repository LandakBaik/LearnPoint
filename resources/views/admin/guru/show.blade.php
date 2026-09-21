@extends('layouts.app')

@section('title', 'Detail Guru: ' . $guru->nama)

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{ modalOpen: false, modalImg: '' }">

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
            <a href="{{ route('guru.edit', $guru->id) }}" class="px-4 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-sm font-semibold rounded-xl border border-indigo-200 transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Edit Data</span>
            </a>
        </div>
    </div>

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
                <h4 class="text-lg font-extrabold text-gray-900">{{ $guru->guruMapels->count() }} Penugasan Kelas</h4>
                <p class="text-xs text-gray-500">Pengampu materi & evaluasi belajar</p>
            </div>
        </div>
    </div>

    <!-- Mata Pelajaran Diampu Table -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-4">
        <h3 class="text-base font-bold text-gray-900">Daftar Mata Pelajaran & Kelas Mengajar</h3>
        @if($guru->guruMapels->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-[11px] font-bold uppercase text-gray-500">
                            <th class="py-2.5 px-4">Mata Pelajaran</th>
                            <th class="py-2.5 px-4">Kelas</th>
                            <th class="py-2.5 px-4">Foto Jadwal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($guru->guruMapels as $gm)
                            <tr>
                                <td class="py-3 px-4 font-semibold text-gray-800">{{ $gm->mapel->nama_mapel ?? '-' }}</td>
                                <td class="py-3 px-4 font-medium text-indigo-600">{{ $gm->kelas->nama_kelas ?? '-' }}</td>
                                <td class="py-3 px-4">
                                    @if($gm->jadwal)
                                        <button 
                                            type="button" 
                                            @click="modalOpen = true; modalImg = '{{ $gm->jadwal_url }}'"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 text-xs font-semibold hover:bg-blue-100 transition-colors"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            Lihat Foto Jadwal
                                        </button>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Belum diupload</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-sm text-gray-400 italic">Guru ini belum memiliki alokasi mapel atau kelas mengajar.</p>
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

</div>
@endsection
