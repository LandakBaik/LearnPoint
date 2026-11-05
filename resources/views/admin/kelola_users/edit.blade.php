@extends('layouts.app')

@section('title', 'Edit Akun Pengguna')

@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="{ selectedRole: '{{ old('role', $user->role) }}' }">

    <!-- Header Navigation -->
    <div class="flex items-center gap-3">
        <a href="{{ route('users.index') }}" class="p-2.5 rounded-xl bg-white border border-slate-200/80 text-slate-500 hover:text-slate-900 hover:bg-slate-50 transition-colors shadow-2xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Edit Akun Pengguna</h1>
            <p class="text-xs text-slate-500 mt-0.5">Perbarui informasi profil atau reset kata sandi untuk akun <span class="font-bold text-slate-800">{{ $user->name }}</span>.</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-6 sm:p-8">
        <form action="{{ route('users.update', $user->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Nama Lengkap -->
            <div>
                <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Nama Lengkap <span class="text-rose-500">*</span></label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    value="{{ old('name', $user->name) }}" 
                    required 
                    oninput="this.value = this.value.replace(/[^a-zA-Z\s\.,]/g, '')"
                    class="w-full px-4 py-2.5 rounded-xl border @error('name') border-rose-400 bg-rose-50/30 @else border-slate-200 bg-slate-50/50 @enderror text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-slate-800"
                >
                <p class="text-[11px] text-slate-400 mt-1">Hanya boleh berisi huruf, spasi, koma (,), dan titik (.).</p>
                @error('name')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Username & Email -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="username" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Username <span class="text-rose-500">*</span></label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        value="{{ old('username', $user->username) }}" 
                        required 
                        class="w-full px-4 py-2.5 rounded-xl border @error('username') border-rose-400 bg-rose-50/30 @else border-slate-200 bg-slate-50/50 @enderror text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-slate-800"
                    >
                    @error('username')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Alamat Email <span class="text-rose-500">*</span></label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="{{ old('email', $user->email) }}" 
                        required 
                        class="w-full px-4 py-2.5 rounded-xl border @error('email') border-rose-400 bg-rose-50/30 @else border-slate-200 bg-slate-50/50 @enderror text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-slate-800"
                    >
                    @error('email')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Password (Opsional) -->
            <div>
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Password Baru (Opsional)</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="Kosongkan jika tidak ingin mengubah password"
                    class="w-full px-4 py-2.5 rounded-xl border @error('password') border-rose-400 bg-rose-50/30 @else border-slate-200 bg-slate-50/50 @enderror text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-slate-800"
                >
                <p class="text-[11px] text-slate-400 mt-1">Hanya isi kolom ini jika Anda ingin mereset password pengguna ini.</p>
                @error('password')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Role & Status Selector -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="role" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Peran Pengguna (Role) <span class="text-rose-500">*</span></label>
                    <select 
                        id="role" 
                        name="role" 
                        x-model="selectedRole" 
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-semibold text-slate-800 transition-all"
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
                    <label for="status" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Status Akun <span class="text-rose-500">*</span></label>
                    <select 
                        id="status" 
                        name="status" 
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-semibold text-slate-800 transition-all"
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
            <div x-show="selectedRole === 'guru'" x-transition class="p-4 rounded-2xl bg-blue-50/60 border border-blue-200/80 space-y-2">
                <label for="guru_id" class="block text-xs font-bold uppercase tracking-wider text-blue-900">Hubungkan dengan Data Guru (Opsional)</label>
                <select id="guru_id" name="guru_id" class="w-full px-4 py-2.5 text-xs rounded-xl border border-blue-200 bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    <option value="">-- Tidak Terhubung ke Guru Tertentu --</option>
                    @foreach($unlinkedGurus as $g)
                        <option value="{{ $g->id }}" {{ old('guru_id', $user->guru_id) == $g->id ? 'selected' : '' }}>
                            {{ $g->nama }} (NUPTK: {{ $g->nuptk }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Tautan ke Data Siswa -->
            <div x-show="selectedRole === 'siswa'" x-transition class="p-4 rounded-2xl bg-emerald-50/60 border border-emerald-200/80 space-y-2">
                <label for="siswa_id" class="block text-xs font-bold uppercase tracking-wider text-emerald-900">Hubungkan dengan Data Siswa (Opsional)</label>
                <select id="siswa_id" name="siswa_id" class="w-full px-4 py-2.5 text-xs rounded-xl border border-emerald-200 bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    <option value="">-- Tidak Terhubung ke Siswa Tertentu --</option>
                    @foreach($unlinkedSiswas as $s)
                        <option value="{{ $s->id }}" {{ old('siswa_id', $user->siswa_id) == $s->id ? 'selected' : '' }}>
                            {{ $s->nama_siswa }} (NIS: {{ $s->nis }} - Kelas: {{ $s->kelas->nama_kelas ?? '-' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('users.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-500/20 transition-all hover:-translate-y-0.5">
                    Perbarui Akun
                </button>
            </div>
        </form>
    </div>

</div>
@endsection