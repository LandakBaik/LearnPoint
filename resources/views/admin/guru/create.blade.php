@extends('layouts.app')

@section('title', 'Tambah Data Guru')

@section('content')
<div class="max-w-2xl mx-auto space-y-6" x-data="{ buatAkun: {{ old('buat_akun') ? 'true' : 'false' }} }">

    <!-- Header Navigation -->
    <div class="flex items-center gap-3">
        <a href="{{ route('guru.index') }}" class="p-2 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Tambah Data Guru</h1>
            <p class="text-sm text-gray-500">Daftarkan tenaga pendidik baru ke database sekolah.</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8">
        <form action="{{ route('guru.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Nama Guru -->
            <div>
                <label for="nama" class="block text-sm font-bold text-gray-700 mb-1">Nama Lengkap & Gelar <span class="text-rose-500">*</span></label>
                <input 
                    type="text" 
                    id="nama" 
                    name="nama" 
                    value="{{ old('nama') }}" 
                    required 
                    placeholder="Contoh: Dra. Siti Nurhaliza, M.Pd."
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
                    value="{{ old('nip') }}" 
                    required 
                    placeholder="Contoh: 198501012010011005"
                    class="w-full px-4 py-2.5 rounded-xl border @error('nip') border-rose-400 bg-rose-50/30 @else border-gray-200 bg-gray-50/50 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono"
                >
                @error('nip')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Option Buat Akun Sekaligus -->
            <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 space-y-4">
                <div class="flex items-center gap-3">
                    <input 
                        type="checkbox" 
                        id="buat_akun" 
                        name="buat_akun" 
                        value="1" 
                        x-model="buatAkun"
                        class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300"
                    >
                    <label for="buat_akun" class="text-sm font-bold text-gray-800 cursor-pointer">
                        Buatkan akun login pengguna untuk guru ini sekarang
                    </label>
                </div>

                <div x-show="buatAkun" x-transition class="space-y-4 pt-3 border-t border-gray-200">
                    <div>
                        <label for="username" class="block text-xs font-bold text-gray-700 mb-1">Username Login <span class="text-rose-500">*</span></label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            value="{{ old('username') }}" 
                            placeholder="Contoh: siti_nurhaliza"
                            class="w-full px-4 py-2 rounded-xl border border-gray-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono"
                        >
                        @error('username')
                            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-bold text-gray-700 mb-1">Email <span class="text-rose-500">*</span></label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="{{ old('email') }}" 
                            placeholder="Contoh: siti@smp.sch.id"
                            class="w-full px-4 py-2 rounded-xl border border-gray-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                        @error('email')
                            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-bold text-gray-700 mb-1">Password <span class="text-rose-500">*</span></label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Minimal 4 karakter"
                            class="w-full px-4 py-2 rounded-xl border border-gray-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                        @error('password')
                            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('guru.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-indigo-200 transition-all hover:shadow-lg">
                    Simpan Guru
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
