@extends('layouts.app')

@section('title', 'Tambah Akun Pengguna')

@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="{ selectedRole: '{{ old('role', 'operator') }}' }">

    <!-- Header Navigation -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('users.index') }}" class="p-2.5 rounded-xl bg-white border border-slate-200/80 text-slate-500 hover:text-slate-900 hover:bg-slate-50 transition-colors shadow-2xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Tambah Akun Baru</h1>
                <p class="text-xs text-slate-500 mt-0.5">Daftarkan akun login pengguna baru untuk ekosistem LearnPoint.</p>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-50/80 border border-rose-200 text-rose-800 text-xs flex items-center gap-3 shadow-2xs">
            <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- FORM INPUT MANUAL -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-6 sm:p-8">
        <form action="{{ route('users.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Hidden Status (Auto Aktif) -->
            <input type="hidden" name="status" value="aktif">

            <!-- Nama Lengkap -->
            <div>
                <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Nama Lengkap <span class="text-rose-500">*</span></label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    value="{{ old('name') }}" 
                    required 
                    placeholder="Contoh: Budi Santoso, M.Pd."
                    oninput="this.value = this.value.replace(/[^a-zA-Z\s\.,]/g, '')"
                    class="w-full px-4 py-2.5 rounded-xl border @error('name') border-rose-400 bg-rose-50/30 @else border-slate-200 bg-slate-50/50 @enderror text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-slate-800"
                >
                <p class="text-[11px] text-slate-400 mt-1">Hanya boleh berisi huruf, spasi, koma (,), dan titik (.).</p>
                @error('name')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Username & Email (Grid 2 Kolom) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="username" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Username <span class="text-rose-500">*</span></label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        value="{{ old('username') }}" 
                        required 
                        placeholder="Contoh: budi_santoso"
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
                        value="{{ old('email') }}" 
                        required 
                        placeholder="Contoh: budi@sekolah.sch.id"
                        class="w-full px-4 py-2.5 rounded-xl border @error('email') border-rose-400 bg-rose-50/30 @else border-slate-200 bg-slate-50/50 @enderror text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-slate-800"
                    >
                    @error('email')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Password <span class="text-rose-500">*</span></label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required 
                    placeholder="Minimal 4 karakter"
                    class="w-full px-4 py-2.5 rounded-xl border @error('password') border-rose-400 bg-rose-50/30 @else border-slate-200 bg-slate-50/50 @enderror text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-slate-800"
                >
                @error('password')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Role Selector -->
            <div>
                <label for="role" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Peran Pengguna (Role) <span class="text-rose-500">*</span></label>
                <select 
                    id="role" 
                    name="role" 
                    x-model="selectedRole" 
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-semibold text-slate-800 transition-all"
                >
                    <option value="operator">Operator / Administrator</option>
                    <option value="kepala_sekolah">Kepala Sekolah</option>
                    <option value="guru">Guru (Tenaga Pengajar)</option>
                    <option value="siswa">Siswa (Peserta Didik)</option>
                </select>
            </div>

            <!-- Tautan ke Data Guru (Kondisional jika role == guru) -->
            <div x-show="selectedRole === 'guru'" x-transition class="p-4 rounded-2xl bg-blue-50/60 border border-blue-200/80 space-y-2">
                <label for="guru_id" class="block text-xs font-bold uppercase tracking-wider text-blue-900">Hubungkan dengan Data Guru (Opsional)</label>
                <select id="guru_id" name="guru_id" class="w-full px-4 py-2.5 text-xs rounded-xl border border-blue-200 bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    <option value="">-- Pilih Data Guru yang Belum Memiliki Akun --</option>
                    @foreach($unlinkedGurus as $g)
                        <option value="{{ $g->id }}" {{ old('guru_id') == $g->id ? 'selected' : '' }}>
                            {{ $g->nama }} (NUPTK: {{ $g->nuptk }})
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-blue-700 font-medium">Jika dipilih, akun ini akan langsung dapat mengelola tugas dan melihat jadwal mengajar guru tersebut.</p>
            </div>

            <!-- Tautan ke Data Siswa (Kondisional jika role == siswa) -->
            <div x-show="selectedRole === 'siswa'" x-transition class="p-4 rounded-2xl bg-emerald-50/60 border border-emerald-200/80 space-y-2">
                <label for="siswa_id" class="block text-xs font-bold uppercase tracking-wider text-emerald-900">Hubungkan dengan Data Siswa (Opsional)</label>
                <select id="siswa_id" name="siswa_id" class="w-full px-4 py-2.5 text-xs rounded-xl border border-emerald-200 bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    <option value="">-- Pilih Data Siswa yang Belum Memiliki Akun --</option>
                    @foreach($unlinkedSiswas as $s)
                        <option value="{{ $s->id }}" {{ old('siswa_id') == $s->id ? 'selected' : '' }}>
                            {{ $s->nama_siswa }} (NIS: {{ $s->nis }} - Kelas: {{ $s->kelas->nama_kelas ?? '-' }})
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-emerald-700 font-medium">Jika dipilih, akun siswa ini akan langsung terhubung ke materi, tugas, dan jadwal kelasnya.</p>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('users.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-500/20 transition-all hover:-translate-y-0.5">
                    Simpan Akun
                </button>
            </div>
        </form>
    </div>

</div>
@endsection