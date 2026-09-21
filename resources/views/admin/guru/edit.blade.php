@extends('layouts.app')

@section('title', 'Edit Data Guru')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Header Navigation -->
    <div class="flex items-center gap-3">
        <a href="{{ route('guru.index') }}" class="p-2 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Edit Data Guru</h1>
            <p class="text-sm text-gray-500">Perbarui data tenaga pendidik <span class="font-bold text-gray-800">{{ $guru->nama }}</span>.</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8">
        <form action="{{ route('guru.update', $guru->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Nama Guru -->
            <div>
                <label for="nama" class="block text-sm font-bold text-gray-700 mb-1">Nama Lengkap & Gelar <span class="text-rose-500">*</span></label>
                <input 
                    type="text" 
                    id="nama" 
                    name="nama" 
                    value="{{ old('nama', $guru->nama) }}" 
                    required 
                    class="w-full px-4 py-2.5 rounded-xl border @error('nama') border-rose-400 bg-rose-50/30 @else border-gray-200 bg-gray-50/50 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                @error('nama')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- NIP Guru -->
            <div>
                <label for="nip" class="block text-sm font-bold text-gray-700 mb-1">Nomor Induk Pegawai (NIP) <span class="text-rose-500">*</span></label>
                <input 
                    type="text" 
                    id="nip" 
                    name="nip" 
                    value="{{ old('nip', $guru->nip) }}" 
                    required 
                    class="w-full px-4 py-2.5 rounded-xl border @error('nip') border-rose-400 bg-rose-50/30 @else border-gray-200 bg-gray-50/50 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono"
                >
                @error('nip')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('guru.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-indigo-200 transition-all hover:shadow-lg">
                    Perbarui Guru
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
