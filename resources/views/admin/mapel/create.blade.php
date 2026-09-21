@extends('layouts.app')

@section('title', 'Tambah Mata Pelajaran')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Header Navigation -->
    <div class="flex items-center gap-3">
        <a href="{{ route('mapel.index') }}" class="p-2 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Tambah Mata Pelajaran</h1>
            <p class="text-sm text-gray-500">Daftarkan mata pelajaran kurikulum dan tentukan batas KKM.</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8">
        <form action="{{ route('mapel.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Nama Mapel -->
            <div>
                <label for="nama_mapel" class="block text-sm font-bold text-gray-700 mb-1">Nama Mata Pelajaran <span class="text-rose-500">*</span></label>
                <input 
                    type="text" 
                    id="nama_mapel" 
                    name="nama_mapel" 
                    value="{{ old('nama_mapel') }}" 
                    required 
                    placeholder="Contoh: Matematika, Bahasa Indonesia, IPA"
                    class="w-full px-4 py-2.5 rounded-xl border @error('nama_mapel') border-rose-400 bg-rose-50/30 @else border-gray-200 bg-gray-50/50 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-rose-500 font-semibold text-gray-900"
                >
                @error('nama_mapel')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Nilai KKM -->
            <div>
                <label for="kkm" class="block text-sm font-bold text-gray-700 mb-1">Kriteria Ketuntasan Minimal (KKM) <span class="text-rose-500">*</span></label>
                <input 
                    type="number" 
                    id="kkm" 
                    name="kkm" 
                    value="{{ old('kkm', 75) }}" 
                    required 
                    min="0" 
                    max="100" 
                    placeholder="Skala 0 - 100 (misal: 75)"
                    class="w-full px-4 py-2.5 rounded-xl border @error('kkm') border-rose-400 bg-rose-50/30 @else border-gray-200 bg-gray-50/50 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-rose-500 font-bold text-gray-900"
                >
                <p class="text-xs text-gray-400 mt-1">Standar nilai ketuntasan kelulusan siswa untuk mapel ini.</p>
                @error('kkm')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('mapel.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-rose-200 transition-all hover:shadow-lg">
                    Simpan Mapel
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
