@extends('layouts.app')

@section('title', 'Edit Akun Pengguna')

@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="{ selectedRole: '{{ old('role', $user->role) }}' }">

    <!-- Header Navigation -->
    <div class="flex items-center gap-3">
        <a href="{{ route('users.index') }}" class="p-2 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Edit Akun Pengguna</h1>
            <p class="text-sm text-gray-500">Perbarui profil atau kata sandi untuk akun <span class="font-bold text-gray-800">{{ $user->name }}</span>.</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8">
        <form action="{{ route('users.update', $user->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Nama Lengkap -->
            <div>
                <label for="name" class="block text-sm font-bold text-gray-700 mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    value="{{ old('name', $user->name) }}" 
                    required 
                    class="w-full px-4 py-2.5 rounded-xl border @error('name') border-rose-400 bg-rose-50/30 @else border-gray-200 bg-gray-50/50 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                @error('name')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Username & Email -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="username" class="block text-sm font-bold text-gray-700 mb-1">Username <span class="text-rose-500">*</span></label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        value="{{ old('username', $user->username) }}" 
                        required 
                        class="w-full px-4 py-2.5 rounded-xl border @error('username') border-rose-400 bg-rose-50/30 @else border-gray-200 bg-gray-50/50 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                    @error('username')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-bold text-gray-700 mb-1">Alamat Email <span class="text-rose-500">*</span></label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="{{ old('email', $user->email) }}" 
                        required 
                        class="w-full px-4 py-2.5 rounded-xl border @error('email') border-rose-400 bg-rose-50/30 @else border-gray-200 bg-gray-50/50 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                    @error('email')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Password (Opsional) -->
            <div>
                <label for="password" class="block text-sm font-bold text-gray-700 mb-1">Password Baru (Opsional)</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="Kosongkan jika tidak ingin mengubah password"
                    class="w-full px-4 py-2.5 rounded-xl border @error('password') border-rose-400 bg-rose-50/30 @else border-gray-200 bg-gray-50/50 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                <p class="text-xs text-gray-400 mt-1">Hanya isi kolom ini apabila Anda hendak mereset password akun pengguna ini.</p>
                @error('password')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Role & Status Selector -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="role" class="block text-sm font-bold text-gray-700 mb-1">Peran Pengguna (Role) <span class="text-rose-500">*</span></label>
                    <select 
                        id="role" 
                        name="role" 
                        x-model="selectedRole" 
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium text-gray-800"
                    >
                        <option value="operator">Operator / Administrator</option>
                        <option value="guru">Guru (Tenaga Pengajar)</option>
                        <option value="siswa">Siswa (Peserta Didik)</option>
                        <option value="kepala_sekolah">Kepala Sekolah</option>
                    </select>
                    @error('role')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-bold text-gray-700 mb-1">Status Akun <span class="text-rose-500">*</span></label>
                    <select 
                        id="status" 
                        name="status" 
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium text-gray-800"
                    >
                        <option value="aktif" {{ old('status', $user->status ?? 'aktif') === 'aktif' ? 'selected' : '' }}>Aktif (Dapat Login)</option>
                        <option value="nonaktif" {{ old('status', $user->status ?? 'aktif') === 'nonaktif' ? 'selected' : '' }}>Nonaktif (Diblokir)</option>
                    </select>
                    @error('status')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Tautan ke Data Guru -->
            <div x-show="selectedRole === 'guru'" x-transition class="p-4 rounded-xl bg-indigo-50/60 border border-indigo-200 space-y-2">
                <label for="guru_id" class="block text-sm font-bold text-indigo-900">Hubungkan dengan Data Guru (Opsional)</label>
                <select id="guru_id" name="guru_id" class="w-full px-4 py-2 text-sm rounded-xl border border-indigo-200 bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- Tidak Terhubung ke Guru Tertentu --</option>
                    @foreach($unlinkedGurus as $g)
                        <option value="{{ $g->id }}" {{ old('guru_id', $user->guru_id) == $g->id ? 'selected' : '' }}>
                            {{ $g->nama }} (NIP: {{ $g->nip }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Tautan ke Data Siswa -->
            <div x-show="selectedRole === 'siswa'" x-transition class="p-4 rounded-xl bg-emerald-50/60 border border-emerald-200 space-y-2">
                <label for="siswa_id" class="block text-sm font-bold text-emerald-900">Hubungkan dengan Data Siswa (Opsional)</label>
                <select id="siswa_id" name="siswa_id" class="w-full px-4 py-2 text-sm rounded-xl border border-emerald-200 bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">-- Tidak Terhubung ke Siswa Tertentu --</option>
                    @foreach($unlinkedSiswas as $s)
                        <option value="{{ $s->id }}" {{ old('siswa_id', $user->siswa_id) == $s->id ? 'selected' : '' }}>
                            {{ $s->nama_siswa }} (NIS: {{ $s->nis }} - Kelas: {{ $s->kelas->nama_kelas ?? '-' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('users.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-indigo-200 transition-all hover:shadow-lg">
                    Perbarui Akun
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
