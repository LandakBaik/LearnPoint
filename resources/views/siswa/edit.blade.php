@extends('layouts.app')

@section('title', 'Edit Data Siswa')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header Navigation -->
    <div class="flex items-center gap-3">
        <a href="{{ route('siswa.index') }}" class="p-2 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Edit Data Siswa</h1>
            <p class="text-sm text-gray-500">Perbarui biodata dan data kelas untuk siswa <span class="font-bold text-gray-800">{{ $siswa->nama_siswa }}</span>.</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8">
        <form action="{{ route('siswa.update', $siswa->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Nama & NIS (Grid 2 Kolom) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="nama_siswa" class="block text-sm font-bold text-gray-700 mb-1">Nama Lengkap Siswa <span class="text-rose-500">*</span></label>
                    <input 
                        type="text" 
                        id="nama_siswa" 
                        name="nama_siswa" 
                        value="{{ old('nama_siswa', $siswa->nama_siswa) }}" 
                        required 
                        class="w-full px-4 py-2.5 rounded-xl border @error('nama_siswa') border-rose-400 bg-rose-50/30 @else border-gray-200 bg-gray-50/50 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    >
                    @error('nama_siswa')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="nis" class="block text-sm font-bold text-gray-700 mb-1">Nomor Induk Siswa (NIS) <span class="text-rose-500">*</span></label>
                    <input 
                        type="text" 
                        id="nis" 
                        name="nis" 
                        value="{{ old('nis', $siswa->nis) }}" 
                        required 
                        class="w-full px-4 py-2.5 rounded-xl border @error('nis') border-rose-400 bg-rose-50/30 @else border-gray-200 bg-gray-50/50 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono"
                    >
                    @error('nis')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Kelas & Jenis Kelamin & Tanggal Lahir (Grid 3 Kolom) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="kelas_id" class="block text-sm font-bold text-gray-700 mb-1">Kelas Rombel</label>
                    <select id="kelas_id" name="kelas_id" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium text-gray-800">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelases as $k)
                            <option value="{{ $k->id }}" {{ old('kelas_id', $siswa->kelas_id) == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kelas }} (Tingkat {{ $k->tingkatan }})
                            </option>
                        @endforeach
                    </select>
                    @error('kelas_id')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="jenis_kelamin" class="block text-sm font-bold text-gray-700 mb-1">Jenis Kelamin <span class="text-rose-500">*</span></label>
                    <select id="jenis_kelamin" name="jenis_kelamin" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium text-gray-800">
                        <option value="L" {{ old('jenis_kelamin', $siswa->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                        <option value="P" {{ old('jenis_kelamin', $siswa->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan (P)</option>
                    </select>
                    @error('jenis_kelamin')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="tanggal_lahir" class="block text-sm font-bold text-gray-700 mb-1">Tanggal Lahir <span class="text-rose-500">*</span></label>
                    <input 
                        type="date" 
                        id="tanggal_lahir" 
                        name="tanggal_lahir" 
                        value="{{ old('tanggal_lahir', $siswa->tanggal_lahir) }}" 
                        required 
                        class="w-full px-4 py-2.5 rounded-xl border @error('tanggal_lahir') border-rose-400 bg-rose-50/30 @else border-gray-200 bg-gray-50/50 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    >
                    @error('tanggal_lahir')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Wali Murid & No HP Wali -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="wali_murid" class="block text-sm font-bold text-gray-700 mb-1">Nama Orang Tua / Wali <span class="text-rose-500">*</span></label>
                    <input 
                        type="text" 
                        id="wali_murid" 
                        name="wali_murid" 
                        value="{{ old('wali_murid', $siswa->wali_murid) }}" 
                        required 
                        class="w-full px-4 py-2.5 rounded-xl border @error('wali_murid') border-rose-400 bg-rose-50/30 @else border-gray-200 bg-gray-50/50 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    >
                    @error('wali_murid')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="nohp_wali" class="block text-sm font-bold text-gray-700 mb-1">No. HP / WhatsApp Wali <span class="text-rose-500">*</span></label>
                    <input 
                        type="text" 
                        id="nohp_wali" 
                        name="nohp_wali" 
                        value="{{ old('nohp_wali', $siswa->nohp_wali) }}" 
                        required 
                        class="w-full px-4 py-2.5 rounded-xl border @error('nohp_wali') border-rose-400 bg-rose-50/30 @else border-gray-200 bg-gray-50/50 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono"
                    >
                    @error('nohp_wali')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Alamat Lengkap -->
            <div>
                <label for="alamat" class="block text-sm font-bold text-gray-700 mb-1">Alamat Tempat Tinggal <span class="text-rose-500">*</span></label>
                <textarea 
                    id="alamat" 
                    name="alamat" 
                    rows="3" 
                    required 
                    class="w-full px-4 py-2.5 rounded-xl border @error('alamat') border-rose-400 bg-rose-50/30 @else border-gray-200 bg-gray-50/50 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                >{{ old('alamat', $siswa->alamat) }}</textarea>
                @error('alamat')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('siswa.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-emerald-200 transition-all hover:shadow-lg">
                    Perbarui Siswa
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
