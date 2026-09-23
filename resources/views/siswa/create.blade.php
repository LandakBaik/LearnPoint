@extends('layouts.app')

@section('title', 'Tambah Data Siswa')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header Navigation -->
    <div class="flex items-center gap-3">
        <a href="{{ route('siswa.index') }}" class="p-2 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Tambah Siswa Baru</h1>
            <p class="text-sm text-gray-500">Daftarkan peserta didik baru dan tetapkan kelas.</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8">
        <form action="{{ route('siswa.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Nama & NIS (Grid 2 Kolom) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="nama_siswa" class="block text-sm font-bold text-gray-700 mb-1">Nama Lengkap Siswa <span class="text-rose-500">*</span></label>
                    <input 
                        type="text" 
                        id="nama_siswa" 
                        name="nama_siswa" 
                        value="{{ old('nama_siswa') }}" 
                        required 
                        placeholder="Contoh: Muhammad Rizky"
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
                        inputmode="numeric"
                        pattern="[0-9]*"
                        value="{{ old('nis') }}" 
                        required 
                        placeholder="Contoh: 20241001 (Hanya angka)"
                        class="w-full px-4 py-2.5 rounded-xl border @error('nis') border-rose-400 bg-rose-50/30 @else border-gray-200 bg-gray-50/50 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono"
                    >
                    @error('nis')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-400 mt-1">NIS harus berupa karakter angka (3-30 digit).</p>
                </div>
            </div>

            <!-- Kelas & Jenis Kelamin & Tanggal Lahir (Grid 3 Kolom) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="kelas_id" class="block text-sm font-bold text-gray-700 mb-1">Kelas</label>
                    <select id="kelas_id" name="kelas_id" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium text-gray-800">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelases as $k)
                            <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
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
                        <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                        <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan (P)</option>
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
                        value="{{ old('tanggal_lahir') }}" 
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
                        value="{{ old('wali_murid') }}" 
                        required 
                        placeholder="Contoh: Hendra Kusuma"
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
                        inputmode="numeric"
                        pattern="[0-9]*"
                        value="{{ old('nohp_wali') }}" 
                        required 
                        placeholder="Contoh: 081234567890 (Hanya angka)"
                        class="w-full px-4 py-2.5 rounded-xl border @error('nohp_wali') border-rose-400 bg-rose-50/30 @else border-gray-200 bg-gray-50/50 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono"
                    >
                    @error('nohp_wali')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-400 mt-1">Nomor telepon harus berupa karakter angka (8-20 digit).</p>
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
                    placeholder="Contoh: Jl. Merpati Putih No. 12, Kel. Sukamaju, Kec. Cilandak"
                    class="w-full px-4 py-2.5 rounded-xl border @error('alamat') border-rose-400 bg-rose-50/30 @else border-gray-200 bg-gray-50/50 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                >{{ old('alamat') }}</textarea>
                @error('alamat')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Auto Account Generation Notice (Poin 5) -->
            <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200 flex items-start gap-3">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-emerald-900">Akun Siswa Dibuat Otomatis</h4>
                    <p class="text-xs text-emerald-700 mt-0.5 leading-relaxed">
                        Akun login untuk siswa ini akan dibuatkan otomatis oleh sistem dengan rincian:<br>
                        <span class="font-mono font-bold text-emerald-900">Username: NIS</span> &bull; 
                        <span class="font-mono font-bold text-emerald-900">Password: NIS</span> &bull; 
                        <span class="font-mono font-bold text-emerald-900">Email: NIS@siswa.learnpoint.sch.id</span> &bull; 
                        <span class="font-bold text-emerald-800">Status: Aktif</span>
                    </p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('siswa.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-emerald-200 transition-all hover:shadow-lg">
                    Simpan Data Siswa
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
