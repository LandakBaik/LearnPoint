@extends('layouts.app')

@section('title', 'Tambah Data Guru')

@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="{ assignments: [] }">

    <!-- Header Navigation -->
    <div class="flex items-center gap-3">
        <a href="{{ route('guru.index') }}" class="p-2 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Tambah Data Guru</h1>
            <p class="text-sm text-gray-500">Daftarkan tenaga pendidik baru beserta mata pelajaran dan kelas yang diampu.</p>
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

            <!-- NIP Guru (Wajib Angka) -->
            <div>
                <label for="nip" class="block text-sm font-bold text-gray-700 mb-1">Nomor Induk Pegawai (NIP) <span class="text-rose-500">*</span></label>
                <input 
                    type="text" 
                    id="nip" 
                    name="nip" 
                    inputmode="numeric"
                    pattern="[0-9]*"
                    value="{{ old('nip') }}" 
                    required 
                    placeholder="Contoh: 198501012010011005 (Hanya angka)"
                    class="w-full px-4 py-2.5 rounded-xl border @error('nip') border-rose-400 bg-rose-50/30 @else border-gray-200 bg-gray-50/50 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono"
                >
                @error('nip')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-400 mt-1">NIP harus berupa karakter angka (5-30 digit).</p>
            </div>

            <!-- Auto Account Generation Notice (Poin 5) -->
            <div class="p-4 rounded-2xl bg-indigo-50/70 border border-indigo-200 flex items-start gap-3">
                <div class="w-8 h-8 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-indigo-900">Akun Pengguna Dibuat Otomatis</h4>
                    <p class="text-xs text-indigo-700 mt-0.5 leading-relaxed">
                        Akun login untuk guru ini akan otomatis dibuatkan oleh sistem dengan rincian:<br>
                        <span class="font-mono font-bold text-indigo-900">Username: NIP</span> &bull; 
                        <span class="font-mono font-bold text-indigo-900">Password: NIP</span> &bull; 
                        <span class="font-mono font-bold text-indigo-900">Email: NIP@guru.learnpoint.sch.id</span> &bull; 
                        <span class="font-bold text-emerald-700">Status: Aktif</span>
                    </p>
                </div>
            </div>

            <!-- Seksi Penugasan Mata Pelajaran & Kelas yang Diampu (Poin 3) -->
            <div class="space-y-3 pt-2">
                <div class="flex items-center justify-between">
                    <div>
                        <label class="block text-sm font-bold text-gray-800">Mata Pelajaran & Kelas yang Diampu (Opsional)</label>
                        <p class="text-xs text-gray-500">Pilih mata pelajaran yang diajarkan oleh guru ini beserta kelasnya.</p>
                    </div>
                    <button 
                        type="button" 
                        @click="assignments.push({ mapel_id: '', kelas_id: '' })"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold rounded-xl transition-colors"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Tambah Mapel</span>
                    </button>
                </div>

                <div class="space-y-2.5">
                    <template x-for="(item, index) in assignments" :key="index">
                        <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 flex flex-col sm:flex-row items-center gap-3">
                            <div class="w-full sm:flex-1">
                                <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">Mata Pelajaran</label>
                                <select :name="'assignments[' + index + '][mapel_id]'" x-model="item.mapel_id" required class="w-full px-3 py-2 rounded-lg border border-gray-200 bg-white text-xs font-semibold text-gray-800 focus:ring-2 focus:ring-indigo-500">
                                    <option value="">-- Pilih Mata Pelajaran --</option>
                                    @foreach($mapels as $m)
                                        <option value="{{ $m->id }}">{{ $m->nama_mapel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-full sm:flex-1">
                                <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">Kelas</label>
                                <select :name="'assignments[' + index + '][kelas_id]'" x-model="item.kelas_id" required class="w-full px-3 py-2 rounded-lg border border-gray-200 bg-white text-xs font-semibold text-gray-800 focus:ring-2 focus:ring-indigo-500">
                                    <option value="">-- Pilih Kelas --</option>
                                    @foreach($kelases as $k)
                                        <option value="{{ $k->id }}">{{ $k->nama_kelas }} (Tingkat {{ $k->tingkatan }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="pt-2 sm:pt-4 self-end sm:self-center">
                                <button 
                                    type="button" 
                                    @click="assignments.splice(index, 1)"
                                    class="p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors"
                                    title="Hapus penugasan ini"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    </template>

                    <div x-show="assignments.length === 0" class="py-5 text-center border-2 border-dashed border-gray-200 rounded-xl bg-gray-50/50">
                        <p class="text-xs text-gray-400">Belum ada mata pelajaran dipilih. Klik <strong>+ Tambah Mapel</strong> jika ingin langsung menambahkan penugasan.</p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('guru.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-indigo-200 transition-all hover:shadow-lg">
                    Simpan Guru & Penugasan
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
