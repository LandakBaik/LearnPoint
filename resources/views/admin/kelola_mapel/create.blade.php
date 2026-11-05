@extends('layouts.app')

@section('title', 'Tambah Mata Pelajaran')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Header Navigation -->
    <div class="flex items-center gap-3">
        <a href="{{ route('mapel.index') }}" class="p-2.5 rounded-xl bg-white border border-slate-200/80 text-slate-500 hover:text-slate-900 hover:bg-slate-50 transition-colors shadow-2xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Tambah Mata Pelajaran</h1>
            <p class="text-xs text-slate-500 mt-0.5">Daftarkan mata pelajaran kurikulum baru dan tentukan batas KKM kelulusan.</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-6 sm:p-8">
        <form action="{{ route('mapel.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Nama Mapel -->
            <div>
                <label for="nama_mapel" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Nama Mata Pelajaran <span class="text-rose-500">*</span></label>
                <input 
                    type="text" 
                    id="nama_mapel" 
                    name="nama_mapel" 
                    value="{{ old('nama_mapel') }}" 
                    required 
                    placeholder="Contoh: Matematika, Bahasa Indonesia, IPA Terpadu"
                    class="w-full px-4 py-2.5 rounded-xl border @error('nama_mapel') border-rose-400 bg-rose-50/30 @else border-slate-200 bg-slate-50/50 @enderror text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-semibold text-slate-900 transition-all"
                >
                @error('nama_mapel')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Nilai KKM -->
            <div>
                <label for="kkm" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Kriteria Ketuntasan Minimal (KKM) <span class="text-rose-500">*</span></label>
                <input 
                    type="number" 
                    id="kkm" 
                    name="kkm" 
                    value="{{ old('kkm', 75) }}" 
                    required 
                    min="0" 
                    max="100" 
                    placeholder="Skala 0 - 100 (misal: 75)"
                    class="w-full px-4 py-2.5 rounded-xl border @error('kkm') border-rose-400 bg-rose-50/30 @else border-slate-200 bg-slate-50/50 @enderror text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-bold text-slate-900 transition-all"
                >
                <p class="text-[11px] text-slate-400 mt-1">Standar nilai batas ketuntasan belajar siswa untuk mata pelajaran ini.</p>
                @error('kkm')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('mapel.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-500/20 transition-all hover:-translate-y-0.5">
                    Simpan Mapel
                </button>
            </div>
        </form>
    </div>

</div>
@endsection