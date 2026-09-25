@extends('layouts.app')

@section('title', 'Detail Materi')

@section('content')
<div class="p-6">
    <!-- Breadcrumb & Header -->
    <div class="mb-6">
    <p class="text-xs text-gray-400 mb-1">
        <!-- Link Kembali ke Halaman Utama Materi -->
        <a href="{{ route('siswa.materi') }}" class="hover:text-blue-600 hover:underline transition">
            Materi Pelajaran
        </a> 
        &gt; 
        <!-- Nama Mapel Dinamis dari Controller -->
        <span class="font-semibold text-gray-700">
            {{ $mapel->nama_mapel ?? $mapel->nama ?? 'Mata Pelajaran' }}
        </span>
        </div>
    </div>

    @if(!isset($babs) || $babs->isEmpty())
        <div class="flex flex-col items-center justify-center p-12 bg-white rounded-2xl border border-gray-100 text-center shadow-sm">
            <div class="w-16 h-16 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center text-3xl mb-4">
                📂
            </div>
            <h3 class="text-lg font-bold text-gray-800">Belum Ada Materi</h3>
            <p class="text-sm text-gray-500 max-w-sm mt-1">
                Guru mata pelajaran ini belum mengunggah materi atau modul pembelajaran. Silakan cek lagi nanti.
            </p>
        </div>

    @else
        <div class="space-y-4" x-data="{ activeAccordion: null }">
            @foreach($babs as $index => $bab)
                <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
                    <!-- Header Bab (Bisa Diklik) -->
                    <button @click="activeAccordion = (activeAccordion === {{ $index }} ? null : {{ $index }})" 
                            class="w-full p-4 flex items-center justify-between bg-white hover:bg-gray-50 transition">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-full bg-blue-600 text-white font-bold text-sm flex items-center justify-center">
                                {{ $loop->iteration }}
                            </span>
                            <div class="text-left">
                                <h2 class="font-bold text-gray-800 text-base">{{ $bab->nama_bab }}</h2>
                                <p class="text-xs text-gray-400">{{ $bab->materis->count() }} item materi</p>
                            </div>
                        </div>
                        <span class="text-gray-400 text-sm" x-text="activeAccordion === {{ $index }} ? '▲' : '▼'">▼</span>
                    </button>

                    <!-- Isi Berkas/File Materi dalam Bab -->
                    <div x-show="activeAccordion === {{ $index }}" class="p-4 bg-slate-50 border-t border-gray-100 space-y-3">
                        @forelse($bab->materis as $materi)
                            <div class="flex items-center justify-between p-3 bg-white rounded-xl border border-gray-100 shadow-2xs">
                                <div class="flex items-center gap-3">
                                    <!-- Icon berdasarkan ekstensi/tipe file -->
                                    <div class="p-2 rounded-lg bg-red-50 text-red-500 text-xl">📄</div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800">{{ $materi->judul }}</p>
                                        <p class="text-xs text-gray-400">{{ $materi->guru->nama ?? 'Guru' }} • {{ $materi->file_size ?? 'File' }}</p>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    @if($materi->file_path)
                                        <a href="{{ asset('storage/' . $materi->file_path) }}" target="_blank" class="px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-full hover:bg-blue-100 transition">
                                            Pratinjau
                                        </a>
                                        <a href="{{ asset('storage/' . $materi->file_path) }}" download class="px-3 py-1.5 text-xs font-medium text-white bg-blue-600 rounded-full hover:bg-blue-700 transition">
                                            Unduh
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 text-center py-2">Belum ada file di bab ini.</p>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection