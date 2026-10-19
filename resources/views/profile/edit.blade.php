@extends('layouts.app')

@section('title', 'Edit Profil')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">

    <!-- Header Title & Back Button -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Edit Profil</h1>
            <p class="text-sm text-gray-500 mt-1">Perbarui informasi akun Anda.</p>
        </div>
        <a href="{{ route('profile.show') }}" class="px-4 py-2 rounded-xl border border-gray-300 text-gray-700 font-semibold text-sm hover:bg-gray-50 transition-colors inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
            <div class="font-bold flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Mohon periksa kembali form berikut:</span>
            </div>
            <ul class="list-disc list-inside mt-2 text-xs space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('profile.update') }}" method="POST" class="bg-white p-6 sm:p-8 rounded-2xl border border-gray-200 shadow-sm space-y-6">
        @csrf
        @method('PUT')

        @php
            $nuptkGuru = $user->nuptk ?? $user->guru?->nuptk ?? ($user->isGuru() ? $user->username : null);
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Nama Lengkap -->
            <div class="space-y-1.5">
                <label for="name" class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Nama Lengkap <span class="text-rose-500">*</span></label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    value="{{ old('name', $user->name) }}" 
                    required 
                    class="w-full px-4 py-2.5 rounded-xl border @error('name') border-rose-400 bg-rose-50/30 @else border-gray-300 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                @error('name')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div class="space-y-1.5">
                <label for="email" class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Email <span class="text-rose-500">*</span></label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="{{ old('email', $user->email) }}" 
                    required 
                    class="w-full px-4 py-2.5 rounded-xl border @error('email') border-rose-400 bg-rose-50/30 @else border-gray-300 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                @error('email')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            @if($user->isGuru())
                <!-- NUPTK (Read-only) -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">NUPTK (Tidak dapat diubah)</label>
                    <input 
                        type="text" 
                        value="{{ $nuptkGuru ?? '-' }}" 
                        disabled 
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm bg-gray-100 text-gray-500 font-mono cursor-not-allowed"
                    >
                </div>

                <!-- Nomor Telepon (Hanya role guru di halaman profil) -->
                <div class="space-y-1.5">
                    <label for="nomor_telepon" class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Nomor Telepon</label>
                    <input 
                        type="text" 
                        id="nomor_telepon" 
                        name="nomor_telepon" 
                        inputmode="tel"
                        placeholder="Contoh: 081234567890"
                        value="{{ old('nomor_telepon', $user->guru?->nomor_telepon) }}" 
                        class="w-full px-4 py-2.5 rounded-xl border @error('nomor_telepon') border-rose-400 bg-rose-50/30 @else border-gray-300 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                    @error('nomor_telepon')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Jenis Kelamin (Hanya role guru di halaman profil) -->
                <div class="space-y-1.5">
                    <label for="jenis_kelamin" class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Jenis Kelamin</label>
                    <select 
                        id="jenis_kelamin" 
                        name="jenis_kelamin" 
                        class="w-full px-4 py-2.5 rounded-xl border @error('jenis_kelamin') border-rose-400 bg-rose-50/30 @else border-gray-300 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">-- Pilih Jenis Kelamin --</option>
                        <option value="Laki-laki" {{ old('jenis_kelamin', $user->guru?->jenis_kelamin) === 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="Perempuan" {{ old('jenis_kelamin', $user->guru?->jenis_kelamin) === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    @error('jenis_kelamin')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            @endif
        </div>

        <!-- Seksi Ubah Password (Opsional) -->
        <div class="pt-4 border-t border-gray-100 space-y-4">
            <div>
                <h4 class="text-sm font-bold text-gray-900">Ubah Password</h4>
                <p class="text-xs text-gray-500 mt-0.5">Biarkan kosong jika Anda tidak ingin mengubah password akun.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Password Baru -->
                <div class="space-y-1.5">
                    <label for="password" class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Password Baru</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Minimal 6 karakter"
                        class="w-full px-4 py-2.5 rounded-xl border @error('password') border-rose-400 bg-rose-50/30 @else border-gray-300 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>

                <!-- Konfirmasi Password -->
                <div class="space-y-1.5">
                    <label for="password_confirmation" class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Konfirmasi Password Baru</label>
                    <input 
                        type="password" 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        placeholder="Ketik ulang password baru"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>
            </div>
        </div>

        <!-- Buttons Action -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
            <a href="{{ route('profile.show') }}" class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 font-semibold text-sm hover:bg-gray-50 transition-colors">
                Batal
            </a>
            <button type="submit" class="px-7 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm shadow-sm transition-colors">
                Simpan Perubahan
            </button>
        </div>
    </form>

</div>
@endsection
