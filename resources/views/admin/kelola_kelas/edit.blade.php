@extends('layouts.app')

@section('title', 'Edit Kelas: ' . $kelas->nama_kelas)

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Header Navigation -->
    <div class="flex items-center gap-3">
        <a href="{{ route('kelas.index') }}" class="p-2.5 rounded-xl bg-white border border-slate-200/80 text-slate-500 hover:text-slate-900 hover:bg-slate-50 transition-colors shadow-2xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Edit Kelas</h1>
            <p class="text-xs text-slate-500 mt-0.5">Perbarui informasi kelas <span class="font-bold text-slate-800">{{ $kelas->nama_kelas }}</span>.</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-6 sm:p-8">
        <form action="{{ route('kelas.update', $kelas->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Nama Kelas -->
            <div>
                <label for="nama_kelas" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Nama Kelas <span class="text-rose-500">*</span></label>
                <input 
                    type="text" 
                    id="nama_kelas" 
                    name="nama_kelas" 
                    value="{{ old('nama_kelas', $kelas->nama_kelas) }}" 
                    required 
                    class="w-full px-4 py-2.5 rounded-xl border @error('nama_kelas') border-rose-400 bg-rose-50/30 @else border-slate-200 bg-slate-50/50 @enderror text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-semibold text-slate-900 transition-all"
                >
                @error('nama_kelas')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tingkatan -->
            <div>
                <label for="tingkatan" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Tingkatan Jenjang <span class="text-rose-500">*</span></label>
                <select id="tingkatan" name="tingkatan" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-semibold text-slate-800 transition-all">
                    <option value="7" {{ old('tingkatan', $kelas->tingkatan) == '7' ? 'selected' : '' }}>Tingkat 7 (Kelas VII)</option>
                    <option value="8" {{ old('tingkatan', $kelas->tingkatan) == '8' ? 'selected' : '' }}>Tingkat 8 (Kelas VIII)</option>
                    <option value="9" {{ old('tingkatan', $kelas->tingkatan) == '9' ? 'selected' : '' }}>Tingkat 9 (Kelas IX)</option>
                </select>
                @error('tingkatan')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Guru Wali Kelas -->
            <div>
                <label for="guru_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Wali Kelas (Opsional)</label>
                <select id="guru_id" name="guru_id" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-semibold text-slate-800 transition-all">
                    <option value="">-- Belum Ditentukan / Kosongkan --</option>
                    @foreach($gurus as $g)
                        <option value="{{ $g->id }}" {{ old('guru_id', $kelas->guru_id) == $g->id ? 'selected' : '' }}>
                            {{ $g->nama }} (NUPTK: {{ $g->nuptk }})
                        </option>
                    @endforeach
                </select>
                @error('guru_id')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('kelas.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-500/20 transition-all hover:-translate-y-0.5">
                    Perbarui Kelas
                </button>
            </div>
        </form>
    </div>

</div>
@endsection