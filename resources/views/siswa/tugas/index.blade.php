@extends('layouts.app')

@section('title', 'Daftar Tugas')

@section('content')

<div class="w-full space-y-6 p-2" x-data="{ tab: 'semua', sortSudah: 'terbaru', openSort: false }">

    {{-- 1. Sub-Header Halaman --}}
    <header class="space-y-1">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-800">
                Daftar Tugas
            </h1>
            <span class="px-2.5 py-1 bg-blue-50 text-blue-600 font-bold text-xs rounded-full border border-blue-100">
                {{ $daftarTugas->count() }} Tugas
            </span>
        </div>
        <p class="text-sm text-slate-500 font-normal">
            Pantau tenggat waktu, kelola tugas harian, dan kumpulkan pekerjaan sekolahmu tepat waktu.
        </p>
    </header>

    {{-- 2. Filter Tabs --}}
    <div class="flex items-center gap-1.5 p-1 bg-slate-100/80 rounded-2xl w-fit">
        <button @click="tab = 'semua'" :class="tab === 'semua' ? 'bg-blue-600 text-white font-bold shadow-sm' : 'text-slate-600 font-medium hover:text-slate-800'" class="px-4 py-2 text-xs rounded-xl transition-all">
            Semua
        </button>
        <button @click="tab = 'belum'" :class="tab === 'belum' ? 'bg-blue-600 text-white font-bold shadow-sm' : 'text-slate-600 font-medium hover:text-slate-800'" class="px-4 py-2 text-xs rounded-xl transition-all">
            Belum Dikumpulkan
        </button>
        <button @click="tab = 'sudah'" :class="tab === 'sudah' ? 'bg-blue-600 text-white font-bold shadow-sm' : 'text-slate-600 font-medium hover:text-slate-800'" class="px-4 py-2 text-xs rounded-xl transition-all">
            Sudah Dikumpulkan
        </button>
    </div>

    @php
        $tugasBelum = $daftarTugas->filter(fn($t) => !$t->pengumpulanSiswa);
        $tugasSudah = $daftarTugas->filter(fn($t) => $t->pengumpulanSiswa);
    @endphp

    {{-- 3. Group 1: TUGAS BELUM DIKUMPULKAN (Hanya muncul jika tab 'semua' ATAU 'belum') --}}
    <div class="space-y-4 pt-2" x-show="tab === 'semua' || tab === 'belum'">
        <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
            <h2 class="text-xs font-extrabold tracking-wider text-slate-500 uppercase">
                TUGAS BELUM DIKUMPULKAN
            </h2>
            <span class="px-2 py-0.5 bg-rose-50 text-rose-600 font-bold text-[10px] rounded-md">
                {{ $tugasBelum->count() }} Perlu Dikerjakan
            </span>
        </div>

        <div class="space-y-4">
            @forelse ($tugasBelum as $tugas)
                @php
                    $isTerlambat = \Carbon\Carbon::now()->gt(\Carbon\Carbon::parse($tugas->deadline));
                    $mapelNama = $tugas->guruMapel->mapel->nama_mapel ?? $tugas->mapel->nama_mapel ?? 'Mata Pelajaran';
                    $kategori = $tugas->kategori ?? 'Tugas Harian';
                @endphp

                <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm hover:border-blue-200 transition-all space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 bg-rose-50 text-rose-600 text-[10px] font-bold tracking-wider uppercase rounded-md">
                                {{ $mapelNama }}
                            </span>
                            <span class="text-xs text-slate-400 font-medium">{{ $kategori }}</span>
                        </div>

                        @if ($isTerlambat)
                            <span class="px-3 py-1 bg-rose-50 text-rose-600 text-xs font-bold rounded-full flex items-center gap-1 w-fit">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Terlambat
                            </span>
                        @else
                            <span class="px-3 py-1 bg-amber-50 text-amber-700 text-xs font-bold rounded-full flex items-center gap-1 w-fit">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ \Carbon\Carbon::parse($tugas->deadline)->diffForHumans() }}
                            </span>
                        @endif
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div class="space-y-1">
                            <h3 class="text-base font-bold text-slate-800 leading-snug">
                                {{ $tugas->judul }}
                            </h3>
                            <p class="text-xs text-slate-400 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Tenggat: {{ \Carbon\Carbon::parse($tugas->deadline)->format('d M Y, H:i') }} WIB
                            </p>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-xs text-rose-500 font-medium">
                            {{ $isTerlambat ? '⚠️ Segera selesaikan walau tenggat terlewat' : '⚡ Segera selesaikan' }}
                        </span>
                        <a href="{{ Route::has('siswa.tugas.show') ? route('siswa.tugas.show', $tugas->id) : '#' }}" class="px-5 py-2 {{ $isTerlambat ? 'bg-rose-600 hover:bg-rose-700' : 'bg-blue-600 hover:bg-blue-700' }} text-white text-xs font-bold rounded-xl transition-all shadow-sm flex items-center gap-1.5">
                            Kumpulkan →
                        </a>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center bg-white rounded-2xl border border-dashed border-slate-200 text-slate-400 text-xs">
                    Tidak ada tugas yang belum dikumpulkan.
                </div>
            @endforelse
        </div>
    </div>

    {{-- 4. Group 2: SUDAH DIKUMPULKAN (Hanya muncul jika tab 'semua' ATAU 'sudah') --}}
    <div class="space-y-4 pt-4" x-show="tab === 'semua' || tab === 'sudah'" style="display: none;">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <h2 class="text-xs font-extrabold tracking-wider text-slate-500 uppercase">
                    SUDAH DIKUMPULKAN
                </h2>
                <span class="px-2 py-0.5 bg-emerald-50 text-emerald-600 font-bold text-[10px] rounded-md">
                    {{ $tugasSudah->count() }} Selesai
                </span>
            </div>

            <!-- Menu Dropdown Urutkan khusus Tugas Selesai -->
            <div class="relative" @click.outside="openSort = false">
                <button @click="openSort = !openSort" class="px-3.5 py-1.5 bg-white border border-slate-200 text-slate-600 text-xs font-semibold rounded-xl flex items-center gap-2 hover:bg-slate-50 shadow-xs transition-all">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-3-3m3 3l3-3"/></svg>
                    <span>Urutkan</span>
                    <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="openSort ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <div x-show="openSort" 
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="absolute right-0 mt-2 w-48 bg-white rounded-2xl border border-slate-100 shadow-xl py-1.5 z-50 text-xs font-medium text-slate-600 space-y-0.5"
                     style="display: none;">
                    
                    <button @click="sortSudah = 'terbaru'; openSort = false" class="w-full text-left px-4 py-2 hover:bg-slate-50 flex items-center justify-between transition-colors" :class="sortSudah === 'terbaru' ? 'text-blue-600 font-bold bg-blue-50/50' : ''">
                        <span>Pengumpulan Terbaru</span>
                        <span x-show="sortSudah === 'terbaru'">✓</span>
                    </button>
                    <button @click="sortSudah = 'terlama'; openSort = false" class="w-full text-left px-4 py-2 hover:bg-slate-50 flex items-center justify-between transition-colors" :class="sortSudah === 'terlama' ? 'text-blue-600 font-bold bg-blue-50/50' : ''">
                        <span>Pengumpulan Terlama</span>
                        <span x-show="sortSudah === 'terlama'">✓</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="space-y-4" x-data="{
            sortSudahCards() {
                let container = $el;
                let cards = Array.from(container.children).filter(el => el.hasAttribute('data-submit-time'));
                cards.sort((a, b) => {
                    if (sortSudah === 'terbaru') return b.dataset.submitTime - a.dataset.submitTime;
                    if (sortSudah === 'terlama') return a.dataset.submitTime - b.dataset.submitTime;
                });
                cards.forEach(card => container.appendChild(card));
            }
        }" x-effect="sortSudahCards()">
            @forelse ($tugasSudah as $tugas)
                @php
                    $pengumpulan = $tugas->pengumpulanSiswa;
                    $mapelNama = $tugas->guruMapel->mapel->nama_mapel ?? $tugas->mapel->nama_mapel ?? 'Mata Pelajaran';
                    $submitTimestamp = \Carbon\Carbon::parse($pengumpulan->created_at ?? $tugas->updated_at)->timestamp;
                @endphp

                <div 
                    data-submit-time="{{ $submitTimestamp }}"
                    class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm space-y-4"
                >
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 bg-slate-100 text-slate-600 text-[10px] font-bold tracking-wider uppercase rounded-md">
                                {{ $mapelNama }}
                            </span>
                            <span class="text-xs text-slate-400 font-medium">{{ $tugas->kategori ?? 'Tugas' }}</span>
                        </div>
                        <span class="px-3 py-1 bg-emerald-50 text-emerald-600 text-xs font-bold rounded-full flex items-center gap-1 w-fit">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Tepat Waktu
                        </span>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="space-y-1">
                            <h3 class="text-base font-bold text-slate-800 leading-snug">
                                {{ $tugas->judul }}
                            </h3>
                            <p class="text-xs text-slate-400">
                                Dikumpulkan pada {{ \Carbon\Carbon::parse($pengumpulan->created_at ?? now())->format('d M Y, H:i') }} WIB
                            </p>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                        @if ($pengumpulan && $pengumpulan->nilai !== null)
                            <span class="text-xs font-bold text-slate-700">
                                Nilai Tugas: <span class="text-emerald-600 text-sm font-extrabold">{{ $pengumpulan->nilai }} / 100</span>
                            </span>
                        @else
                            <span class="text-xs text-amber-600 font-medium flex items-center gap-1">
                                📌 Status: Sedang Dinilai Guru
                            </span>
                        @endif

                        <a href="{{ Route::has('siswa.tugas.show') ? route('siswa.tugas.show', $tugas->id) : '#' }}" class="text-xs font-bold text-blue-600 hover:text-blue-700 flex items-center gap-1">
                            Lihat Feedback →
                        </a>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center bg-white rounded-2xl border border-dashed border-slate-200 text-slate-400 text-xs">
                    Belum ada tugas yang dikumpulkan.
                </div>
            @endforelse
        </div>
    </div>

</div>

@endsection