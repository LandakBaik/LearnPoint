@extends('layouts.app')

@section('content')
<div class="p-1 max-w-7xl mx-auto space-y-1">

    <!-- Header -->
    <div class="flex items-center gap-3">
        <a href="{{ route('guru.tugas') }}" class="p-2 bg-white rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-slate-800">Detail & Penilaian Tugas</h1>
            <p class="text-xs text-slate-500">Kelola informasi tugas dan berikan penilaian untuk siswa.</p>
        </div>
    </div>

    <!-- Alert Success Message -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center justify-between text-emerald-700 text-xs font-bold">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <!-- Ringkasan Informasi Tugas -->
    <div class="p-6 bg-white rounded-2xl border border-slate-100 shadow-sm space-y-4">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-lg text-xs font-bold">
                    {{ $tugas->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? 'Mata Pelajaran' }}
                </span>
                <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-lg text-xs font-bold">
                    {{ $tugas->pengampuKelas?->kelas?->nama_kelas ?? 'Kelas' }}
                </span>
                <span class="px-3 py-1 bg-purple-50 text-purple-600 rounded-lg text-xs font-bold capitalize">
                    {{ str_replace('_', ' ', $tugas->tipe) }}
                </span>
            </div>
            <div class="text-xs text-slate-400 font-medium">
                Deadline: <strong class="text-slate-700">{{ \Carbon\Carbon::parse($tugas->deadline)->format('d M Y, H:i') }}</strong>
            </div>
        </div>

        <div>
            <h2 class="text-lg font-bold text-slate-900">{{ $tugas->judul }}</h2>
            <p class="text-xs text-slate-600 mt-1">{{ $tugas->deskripsi ?? 'Tidak ada deskripsi tugas.' }}</p>
        </div>

        @if ($tugas->file_lampiran)
            <div class="pt-2 border-t border-slate-100">
                <a href="{{ asset('storage/' . $tugas->file_lampiran) }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-blue-600 font-bold hover:underline">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Unduh Berkas Lampiran Guru
                </a>
            </div>
        @endif
    </div>

    <!-- Table Pengumpulan Siswa -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden" x-data="{ showNilaiModal: false, selectedPengumpulan: {} }">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-black text-gray-900 text-base">Daftar Pengumpulan Siswa</h3>
            <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-600 text-xs font-bold">
                {{ count($pengumpulans) }} Siswa Mengumpulkan
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/50 text-[11px] font-bold uppercase tracking-wider text-gray-400">
                        <th class="py-3.5 px-6">Nama Siswa</th>
                        <th class="py-3.5 px-6">Waktu Pengumpulan</th>
                        <th class="py-3.5 px-6">File Jawaban</th>
                        <th class="py-3.5 px-6">Nilai</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse ($pengumpulans as $p)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <!-- Nama Siswa -->
                        <td class="py-4 px-6 font-bold text-gray-800">
                            {{ $p->siswa?->nama ?? 'Siswa' }}
                        </td>

                        <!-- Waktu Pengumpulan -->
                        <td class="py-4 px-6 text-xs text-gray-500">
                            {{ \Carbon\Carbon::parse($p->created_at)->format('d M Y, H:i') }}
                        </td>

                        <!-- File Jawaban -->
                        <td class="py-4 px-6">
                            @if($p->file_jawaban)
                            <a href="{{ asset('storage/' . $p->file_jawaban) }}" target="_blank" class="inline-flex items-center gap-1 text-xs text-blue-600 font-semibold hover:underline">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Lihat Jawaban
                            </a>
                            @else
                            <span class="text-xs text-gray-400">Teks / Langsung</span>
                            @endif
                        </td>

                        <!-- Nilai saat ini -->
                        <td class="py-4 px-6">
                            @if($p->nilai !== null)
                            <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-lg font-black text-xs border border-emerald-200/60">
                                {{ $p->nilai }} / 100
                            </span>
                            @else
                            <span class="px-2.5 py-1 bg-amber-50 text-amber-700 rounded-lg font-medium text-xs border border-amber-200/60">
                                Belum Dinilai
                            </span>
                            @endif
                        </td>

                        <!-- Aksi Beri Nilai -->
                        <td class="py-4 px-6 text-right">
                            <button 
                                @click="showNilaiModal = true; selectedPengumpulan = { id: {{ $p->id }}, nama: '{{ addslashes($p->siswa?->nama ?? 'Siswa') }}', nilai: '{{ $p->nilai ?? '' }}' }"
                                type="button" 
                                class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-all shadow-sm cursor-pointer">
                                {{ $p->nilai !== null ? 'Edit Nilai' : 'Beri Nilai' }}
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-gray-400 text-xs font-medium">
                            Belum ada siswa yang mengumpulkan tugas ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Modal Form Penilaian Ringkas -->
        <div x-show="showNilaiModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
            <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-xl space-y-4" @click.away="showNilaiModal = false">
                <div>
                    <h3 class="text-lg font-black text-gray-900">Input Nilai Siswa</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Siswa: <span class="font-bold text-gray-800" x-text="selectedPengumpulan.nama"></span></p>
                </div>

                <form :action="'/guru/pengumpulan/' + selectedPengumpulan.id + '/nilai'" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Nilai (0 - 100)</label>
                        <input 
                            type="number" 
                            name="nilai" 
                            min="0" 
                            max="100" 
                            x-model="selectedPengumpulan.nilai" 
                            required 
                            placeholder="Masukkan nilai..." 
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-bold text-gray-800 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="showNilaiModal = false" class="px-4 py-2 text-xs font-bold text-gray-500 hover:bg-gray-100 rounded-xl transition-colors cursor-pointer">Batal</button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors cursor-pointer">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection