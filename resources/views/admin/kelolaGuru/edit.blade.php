@extends('layouts.app')

@section('title', 'Edit Data Guru & Mapel')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Navigation -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('guru.index') }}" class="p-2 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Edit Data Guru & Mapel</h1>
                <p class="text-sm text-gray-500">Perbarui biodata dan kelola penugasan mata pelajaran untuk <span class="font-bold text-gray-800">{{ $guru->nama }}</span>.</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('guru.show', $guru->id) }}" class="px-3.5 py-2 rounded-xl bg-indigo-50 text-indigo-700 text-xs font-bold hover:bg-indigo-100 transition-colors">
                Lihat Profil Lengkap
            </a>
        </div>
    </div>

    <!-- Form Card 1: Biodata Guru & Tambah Penugasan Baru -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8">
        <h2 class="text-base font-extrabold text-gray-900 pb-3 border-b border-gray-100 mb-6">Informasi Biodata Guru</h2>
        <form action="{{ route('guru.update', $guru->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Nama Guru -->
                <div>
                    <label for="nama" class="block text-sm font-bold text-gray-700 mb-1">Nama Lengkap & Gelar <span class="text-rose-500">*</span></label>
                    <input 
                        type="text" 
                        id="nama" 
                        name="nama" 
                        value="{{ old('nama', $guru->nama) }}" 
                        required 
                        class="w-full px-4 py-2.5 rounded-xl border @error('nama') border-rose-400 bg-rose-50/30 @else border-gray-200 bg-gray-50/50 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                    @error('nama')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- NIP Guru (Wajib 18 Digit Angka) -->
                <div>
                    <label for="nip" class="block text-sm font-bold text-gray-700 mb-1">Nomor Induk Pegawai (NIP) <span class="text-rose-500">*</span></label>
                    <input 
                        type="text" 
                        id="nip" 
                        name="nip" 
                        inputmode="numeric"
                        pattern="[0-9]{18}"
                        minlength="18"
                        maxlength="18"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 18)"
                        value="{{ old('nip', $guru->nip) }}" 
                        required 
                        class="w-full px-4 py-2.5 rounded-xl border @error('nip') border-rose-400 bg-rose-50/30 @else border-gray-200 bg-gray-50/50 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono"
                    >
                    <p class="text-xs text-gray-500 mt-1">NIP harus tepat 18 digit angka.</p>
                    @error('nip')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 space-y-3">
                <div class="flex items-center gap-2 text-gray-800">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    <span class="text-xs font-bold uppercase tracking-wider">Tambah Penugasan Mapel & Kelas Baru (Opsional)</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">Mata Pelajaran</label>
                        <select name="new_mapel_id" class="w-full px-3 py-2 rounded-lg border border-gray-200 bg-white text-xs font-semibold text-gray-800 focus:ring-2 focus:ring-indigo-500">
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            @foreach($mapels as $m)
                                <option value="{{ $m->id }}">{{ $m->nama_mapel }} (KKM: {{ $m->kkm }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">Kelas</label>
                        <select name="new_kelas_id" class="w-full px-3 py-2 rounded-lg border border-gray-200 bg-white text-xs font-semibold text-gray-800 focus:ring-2 focus:ring-indigo-500">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($kelases as $k)
                                <option value="{{ $k->id }}">{{ $k->nama_kelas }} (Tingkat {{ $k->tingkatan }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <p class="text-[11px] text-gray-500">Pilih Mapel dan Kelas untuk menambahkan tugas mengajar baru saat menekan tombol simpan.</p>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('guru.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-indigo-200 transition-all hover:shadow-lg">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    <!-- Section: Daftar Mapel yang Sedang Diampu Saat Ini -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-extrabold text-gray-900">Daftar Mapel & Kelas yang Sedang Diampu</h3>
                <p class="text-xs text-gray-500 mt-0.5">Penugasan mengajar aktif untuk guru {{ $guru->nama }}.</p>
            </div>
            <span class="px-3 py-1 bg-indigo-50 text-indigo-700 rounded-full text-xs font-bold">
                {{ $guru->guruMapels->count() }} Penugasan
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-200 text-[11px] font-bold uppercase tracking-wider text-gray-500">
                        <th class="py-3 px-6">Mata Pelajaran</th>
                        <th class="py-3 px-4">KKM</th>
                        <th class="py-3 px-4">Kelas</th>
                        <th class="py-3 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($guru->guruMapels as $gm)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="py-3.5 px-6 font-bold text-gray-900">
                                {{ $gm->mapel->nama_mapel ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-amber-600">
                                {{ $gm->mapel->kkm ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 font-bold text-xs rounded-lg">
                                    {{ $gm->kelas->nama_kelas ?? '-' }} (Tingkat {{ $gm->kelas->tingkatan ?? '-' }})
                                </span>
                            </td>
                            <td class="py-3.5 px-6 text-right">
                                <form action="{{ route('guru-mapel.destroy', $gm->id) }}" method="POST" data-confirm="Hapus penugasan mengajar mapel {{ $gm->mapel->nama_mapel ?? '' }} di kelas {{ $gm->kelas->nama_kelas ?? '' }} untuk guru ini?" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1 text-xs font-bold text-rose-600 hover:bg-rose-50 rounded-lg transition-colors inline-flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        <span>Lepas Penugasan</span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-gray-400">
                                <p class="text-sm font-semibold">Guru ini belum ditugaskan mengajar mata pelajaran apa pun.</p>
                                <p class="text-xs text-gray-400 mt-1">Gunakan form di atas untuk menugaskan mapel dan kelas pertama bagi guru ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
