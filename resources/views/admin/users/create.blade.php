@extends('layouts.app')

@section('title', 'Tambah Akun Pengguna')

@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="{ selectedRole: '{{ old('role', 'operator') }}' }">

    <!-- Header Navigation -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('users.index') }}" class="p-2 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Tambah Akun Baru</h1>
                <p class="text-sm text-gray-500">Daftarkan akun login pengguna sistem LearnPoint.</p>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center gap-3">
            <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- FORM INPUT MANUAL -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8">
        <form action="{{ route('users.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Nama Lengkap -->
            <div>
                <label for="name" class="block text-sm font-bold text-gray-700 mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    value="{{ old('name') }}" 
                    required 
                    placeholder="Contoh: Budi Santoso, M.Pd."
                    class="w-full px-4 py-2.5 rounded-xl border @error('name') border-rose-400 bg-rose-50/30 @else border-gray-200 bg-gray-50/50 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                @error('name')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Username & Email (Grid 2 Kolom) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="username" class="block text-sm font-bold text-gray-700 mb-1">Username <span class="text-rose-500">*</span></label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        value="{{ old('username') }}" 
                        required 
                        placeholder="Contoh: budi_santoso"
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
                        value="{{ old('email') }}" 
                        required 
                        placeholder="Contoh: budi@sekolah.sch.id"
                        class="w-full px-4 py-2.5 rounded-xl border @error('email') border-rose-400 bg-rose-50/30 @else border-gray-200 bg-gray-50/50 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                    @error('email')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-bold text-gray-700 mb-1">Password <span class="text-rose-500">*</span></label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required 
                    placeholder="Minimal 4 karakter"
                    class="w-full px-4 py-2.5 rounded-xl border @error('password') border-rose-400 bg-rose-50/30 @else border-gray-200 bg-gray-50/50 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                @error('password')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Role Selector & Status -->
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
                        <option value="kepala_sekolah">Kepala Sekolah</option>
                        <option value="guru">Guru (Tenaga Pengajar)</option>
                        <option value="siswa">Siswa (Peserta Didik)</option>
                    </select>
                </div>

                <div>
                    <label for="status" class="block text-sm font-bold text-gray-700 mb-1">Status Awal Akun <span class="text-rose-500">*</span></label>
                    <select 
                        id="status" 
                        name="status" 
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium text-gray-800"
                    >
                        <option value="aktif">Aktif (Dapat Login)</option>
                        <option value="nonaktif">Nonaktif (Diblokir Sementara)</option>
                    </select>
                </div>
            </div>

            <!-- Tautan ke Data Guru (Kondisional jika role == guru) -->
            <div x-show="selectedRole === 'guru'" x-transition class="p-4 rounded-xl bg-indigo-50/60 border border-indigo-200 space-y-2">
                <label for="guru_id" class="block text-sm font-bold text-indigo-900">Hubungkan dengan Data Guru (Opsional)</label>
                <select id="guru_id" name="guru_id" class="w-full px-4 py-2 text-sm rounded-xl border border-indigo-200 bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- Pilih Data Guru yang Belum Memiliki Akun --</option>
                    @foreach($unlinkedGurus as $g)
                        <option value="{{ $g->id }}" {{ old('guru_id') == $g->id ? 'selected' : '' }}>
                            {{ $g->nama }} (NIP: {{ $g->nip }})
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-indigo-700">Jika dipilih, akun ini akan langsung dapat mengelola tugas dan melihat jadwal mengajar guru tersebut.</p>
            </div>

            <!-- Tautan ke Data Siswa (Kondisional jika role == siswa) -->
            <div x-show="selectedRole === 'siswa'" x-transition class="p-4 rounded-xl bg-emerald-50/60 border border-emerald-200 space-y-2">
                <label for="siswa_id" class="block text-sm font-bold text-emerald-900">Hubungkan dengan Data Siswa (Opsional)</label>
                <select id="siswa_id" name="siswa_id" class="w-full px-4 py-2 text-sm rounded-xl border border-emerald-200 bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">-- Pilih Data Siswa yang Belum Memiliki Akun --</option>
                    @foreach($unlinkedSiswas as $s)
                        <option value="{{ $s->id }}" {{ old('siswa_id') == $s->id ? 'selected' : '' }}>
                            {{ $s->nama_siswa }} (NIS: {{ $s->nis }} - Kelas: {{ $s->kelas->nama_kelas ?? '-' }})
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-emerald-700">Jika dipilih, akun siswa ini akan langsung terhubung ke materi, tugas, dan jadwal kelasnya.</p>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('users.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-indigo-200 transition-all hover:shadow-lg">
                    Simpan Akun
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
