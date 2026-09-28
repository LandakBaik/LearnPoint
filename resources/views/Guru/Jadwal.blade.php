@extends('layouts.app')

@section('title', 'Jadwal Mengajar')

@section('content')
@php
    $mapelsWithJadwal = $guruMapels->whereNotNull('jadwal');
    $firstMapelWithJadwal = $mapelsWithJadwal->first();
@endphp

<div class="space-y-6" x-data="{ 
    modalOpen: false,
    modalImg: '',
    modalTitle: '',
    activeMapelId: '{{ $firstMapelWithJadwal ? $firstMapelWithJadwal->id : ($guruMapels->first() ? $guruMapels->first()->id : '') }}',
    
    openModal(url, title) {
        this.modalImg = url;
        this.modalTitle = title;
        this.modalOpen = true;
    }
}">

    <!-- Header Title -->
    <div>
        <div class="flex items-center gap-3">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Jadwal Mengajar</h1>
        </div>
        <p class="text-sm text-gray-500 mt-1">Jadwal resmi pembelajaran Anda per mata pelajaran semester ini.</p>
    </div>

    @if($mapelsWithJadwal->count() > 0)
        <!-- If Teacher has multiple mapels, show Mapel Selector Tabs -->
        @if($guruMapels->count() > 1)
            <div class="flex items-center gap-2 overflow-x-auto pb-1">
                @foreach($guruMapels as $gm)
                    <button 
                        type="button"
                        @click="activeMapelId = '{{ $gm->id }}'"
                        :class="activeMapelId == '{{ $gm->id }}' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200'"
                        class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2 shrink-0">
                        <span>{{ $gm->mapel->nama_mapel ?? 'Mapel #' . $gm->id }}</span>
                        @if($gm->jadwal_url)
                            <span class="w-2 h-2 rounded-full" :class="activeMapelId == '{{ $gm->id }}' ? 'bg-emerald-300' : 'bg-emerald-500'"></span>
                        @endif
                    </button>
                @endforeach
            </div>
        @endif

        <!-- Schedule Cards for Each Subject -->
        @foreach($guruMapels as $gm)
            <div x-show="activeMapelId == '{{ $gm->id }}'" x-transition class="space-y-6">
                @if($gm->jadwal_url)
                    <!-- Photo Schedule Card -->
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-gray-100">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-indigo-600 bg-indigo-50 border border-indigo-100 px-2 py-0.5 rounded">
                                        Mata Pelajaran
                                    </span>
                                    <h3 class="text-base font-bold text-gray-900">{{ $gm->mapel->nama_mapel ?? 'Mata Pelajaran' }}</h3>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Diterbitkan oleh Administrator / Bagian Kurikulum.</p>
                            </div>
                            <div>
                                <a href="{{ $gm->jadwal_url }}" download class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-md shadow-indigo-200 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    <span>Unduh Foto Jadwal</span>
                                </a>
                            </div>
                        </div>

                        <!-- Image Viewer -->
                        <div class="relative group rounded-2xl overflow-hidden border border-gray-200 bg-gray-50/50 p-3 text-center">
                            <img 
                                src="{{ $gm->jadwal_url }}" 
                                alt="Jadwal {{ $gm->mapel->nama_mapel ?? 'Mapel' }} - {{ $guru->nama }}" 
                                class="max-h-[600px] w-auto mx-auto rounded-xl object-contain shadow-sm cursor-pointer hover:opacity-95 transition-opacity"
                                @click="openModal('{{ $gm->jadwal_url }}', 'Jadwal {{ $gm->mapel->nama_mapel ?? 'Mapel' }} - {{ $guru->nama }}')"
                            >
                            <div class="mt-3 flex items-center justify-center gap-2 text-xs text-gray-500">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                <span>Klik pada foto untuk melihat tampilan penuh (zoom)</span>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Empty State for This Subject -->
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-8 text-center space-y-3">
                        <div class="w-14 h-14 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center mx-auto border border-amber-100">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h4 class="font-bold text-gray-900 text-sm">Belum Ada Foto Jadwal Untuk {{ $gm->mapel->nama_mapel ?? 'Mapel Ini' }}</h4>
                        <p class="text-xs text-gray-500 max-w-sm mx-auto">Admin belum mengunggah jadwal khusus untuk mata pelajaran ini. Silakan hubungi bagian kurikulum.</p>
                    </div>
                @endif
            </div>
        @endforeach

        <!-- Assignments Summary Card -->
        @if($guruMapels->count() > 0)
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-4">
                <h3 class="text-base font-bold text-gray-900">Daftar Kelas Mengajar per Mata Pelajaran</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($guruMapels as $gm)
                        @foreach($gm->pengampuKelases as $pk)
                            <div class="p-4 rounded-xl border border-gray-100 bg-gray-50/70 space-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">
                                        {{ $pk->kelas->nama_kelas ?? '-' }}
                                    </span>
                                    <span class="text-[10px] text-gray-400 font-mono">Tingkat {{ $pk->kelas->tingkatan ?? '-' }}</span>
                                </div>
                                <h4 class="font-bold text-gray-900 text-sm mt-1">{{ $gm->mapel->nama_mapel ?? '-' }}</h4>
                                <p class="text-xs text-gray-500">KKM Standar: {{ $gm->mapel->kkm ?? '-' }}</p>
                            </div>
                        @endforeach
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Zoom Modal -->
        <div 
            x-show="modalOpen" 
            style="display: none;"
            class="fixed inset-0 z-50 bg-gray-900/80 backdrop-blur-sm flex items-center justify-center p-4"
            @click.self="modalOpen = false"
        >
            <div class="relative max-w-5xl w-full bg-white rounded-2xl shadow-2xl overflow-hidden p-2">
                <div class="flex items-center justify-between p-3 border-b border-gray-100">
                    <h4 class="font-bold text-gray-800 text-sm" x-text="modalTitle"></h4>
                    <div class="flex items-center gap-2">
                        <a :href="modalImg" download class="p-1.5 text-xs text-indigo-600 hover:bg-indigo-50 rounded-lg flex items-center gap-1 font-semibold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Unduh</span>
                        </a>
                        <button @click="modalOpen = false" class="p-1 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
                <div class="p-4 text-center max-h-[85vh] overflow-auto">
                    <img :src="modalImg" :alt="modalTitle" class="mx-auto max-h-[75vh] object-contain rounded-lg">
                </div>
            </div>
        </div>
    @else
        <!-- Empty State -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-8 sm:p-12 text-center">
            <div class="max-w-md mx-auto py-4 space-y-4">
                <div class="w-16 h-16 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center mx-auto border border-blue-100 shadow-inner">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 01-6 0v-1m6 0H9" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Belum Ada Foto Jadwal</h3>
                    <p class="text-sm text-gray-500 mt-1">
                        Saat ini admin belum mengunggah foto jadwal mengajar untuk akun Anda. Silakan hubungi bagian kurikulum atau administrator sekolah.
                    </p>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection