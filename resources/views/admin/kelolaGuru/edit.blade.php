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
                        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 18); updateNipPreview(this.value);"
                        value="{{ old('nip', $guru->nip) }}" 
                        required 
                        class="w-full px-4 py-2.5 rounded-xl border @error('nip') border-rose-400 bg-rose-50/30 @else border-gray-200 bg-gray-50/50 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono"
                    >
                    <p class="text-xs text-gray-500 mt-1">NIP harus tepat 18 digit angka.</p>

                    <!-- Live Preview NIP: Tanggal Lahir & Jenis Kelamin -->
                    <div id="nip-preview" class="mt-2.5 p-3.5 rounded-xl bg-blue-50/80 border border-blue-200 text-xs space-y-1.5 transition-all">
                        <div class="font-bold text-blue-900 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Deteksi Otomatis Profil Guru dari NIP:</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-gray-700 pt-1">
                            <div class="flex items-center gap-2">
                                <span class="text-gray-500 font-medium">Tanggal Lahir (8 Digit Pertama):</span>
                                <span id="preview-tgl-lahir" class="font-bold text-blue-800">{{ $guru->tanggal_lahir ?? '-' }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-gray-500 font-medium">Jenis Kelamin (1 Digit Ketiga):</span>
                                <span id="preview-jenis-kelamin" class="font-bold text-indigo-800">{{ $guru->jenis_kelamin ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    @error('nip')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 space-y-4">
                <div class="flex items-center gap-2 text-gray-800">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    <span class="text-xs font-bold uppercase tracking-wider">Tambah Penugasan Mapel & Kelas Baru (Opsional)</span>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">Mata Pelajaran</label>
                    <select name="new_mapel_id" class="w-full px-3 py-2 rounded-lg border border-gray-200 bg-white text-xs font-semibold text-gray-800 focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Pilih Mata Pelajaran --</option>
                        @foreach($mapels as $m)
                            <option value="{{ $m->id }}">{{ $m->nama_mapel }} (KKM: {{ $m->kkm }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Multi-select Checkbox Kelas -->
                <div x-data="{
                    selectedClasses: [],
                    allClassIds: {{ $kelases->pluck('id')->toJson() }},
                    toggleAll() {
                        if (this.selectedClasses.length === this.allClassIds.length) {
                            this.selectedClasses = [];
                        } else {
                            this.selectedClasses = [...this.allClassIds];
                        }
                    }
                }" class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-[11px] font-bold text-gray-500 uppercase">Pilih Kelas yang Diampu</label>
                        <button 
                            type="button" 
                            @click="toggleAll()" 
                            class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 hover:underline">
                            <span x-text="selectedClasses.length === allClassIds.length ? 'Batalkan Semua' : 'Pilih Semua Kelas'"></span>
                        </button>
                    </div>

                    <div class="max-h-52 overflow-y-auto p-3 rounded-xl border border-gray-200 bg-white space-y-3">
                        @foreach($kelases->groupBy('tingkatan') as $tingkat => $kelasList)
                            <div>
                                <p class="text-[10px] font-extrabold uppercase tracking-wider text-gray-400 mb-1.5">Tingkat {{ $tingkat }}</p>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                    @foreach($kelasList as $kelas)
                                        <label class="flex items-center gap-2 p-2 rounded-lg bg-gray-50 border border-gray-200 hover:border-indigo-300 hover:bg-indigo-50/30 cursor-pointer transition-all text-xs font-medium text-gray-800">
                                            <input 
                                                type="checkbox" 
                                                name="new_kelas_ids[]" 
                                                value="{{ $kelas->id }}"
                                                x-model="selectedClasses"
                                                class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                            <span>{{ $kelas->nama_kelas }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <p class="text-[11px] text-gray-500">Pilih Mapel dan centang satu atau lebih Kelas untuk menambahkan tugas mengajar baru saat menekan tombol simpan.</p>
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
                {{ $guru->guruMapels()->has('pengampuKelases')->count() }} Mata Pelajaran ({{ $guru->pengampuKelases->count() }} Kelas)
            </span>
        </div>

        @php
            $groupedPengampu = $guru->pengampuKelases->groupBy('guru_mapel_id');
        @endphp

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-200 text-[11px] font-bold uppercase tracking-wider text-gray-500">
                        <th class="py-3 px-6">Mata Pelajaran</th>
                        <th class="py-3 px-4">KKM</th>
                        <th class="py-3 px-4">Kelas yang Diampu</th>
                        <th class="py-3 px-4 text-center">Total Kelas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($groupedPengampu as $gmId => $items)
                        @php $first = $items->first(); @endphp
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="py-3.5 px-6 font-bold text-gray-900">
                                {{ $first->mapel->nama_mapel ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-amber-600">
                                {{ $first->mapel->kkm ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    @foreach($items as $gm)
                                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-indigo-50 text-indigo-800 font-bold text-xs rounded-lg border border-indigo-100">
                                            <span>{{ $gm->kelas->nama_kelas ?? '-' }}</span>
                                            <form action="{{ route('guru-mapel.destroy', $gm->id) }}" method="POST" data-confirm="Hapus penugasan mengajar mapel {{ $first->mapel->nama_mapel ?? '' }} di kelas {{ $gm->kelas->nama_kelas ?? '' }} untuk guru ini?" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-indigo-400 hover:text-rose-600 transition-colors ml-0.5" title="Lepas kelas {{ $gm->kelas->nama_kelas ?? '' }}">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700">
                                    {{ $items->count() }} Kelas
                                </span>
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

<script>
function updateNipPreview(nip) {
    const clean = (nip || '').replace(/[^0-9]/g, '');
    const previewBox = document.getElementById('nip-preview');
    const tglEl = document.getElementById('preview-tgl-lahir');
    const jkEl = document.getElementById('preview-jenis-kelamin');
    if (!previewBox || !tglEl || !jkEl) return;

    if (clean.length < 8) {
        previewBox.classList.add('hidden');
        return;
    }

    previewBox.classList.remove('hidden');

    // 8 digit pertama: YYYYMMDD
    const y = clean.substring(0, 4);
    const m = clean.substring(4, 6);
    const d = clean.substring(6, 8);
    const months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    const mInt = parseInt(m, 10);
    const dInt = parseInt(d, 10);

    if (mInt >= 1 && mInt <= 12 && dInt >= 1 && dInt <= 31) {
        tglEl.textContent = `${dInt} ${months[mInt]} ${y}`;
    } else {
        tglEl.textContent = `${d}-${m}-${y} (Tanggal tidak valid)`;
    }

    // 1 digit ketiga: index 14 (digit ke-15)
    if (clean.length >= 15) {
        const jkCode = clean.charAt(14);
        if (jkCode === '1') {
            jkEl.textContent = 'Laki-laki (Kode 1)';
        } else if (jkCode === '2') {
            jkEl.textContent = 'Perempuan (Kode 2)';
        } else {
            jkEl.textContent = `Kode ${jkCode} (Tidak dikenal)`;
        }
    } else {
        jkEl.textContent = 'Menunggu input hingga 15 digit...';
    }
}

// Inisialisasi awal
document.addEventListener('DOMContentLoaded', function() {
    const nipInput = document.getElementById('nip');
    if (nipInput && nipInput.value) {
        updateNipPreview(nipInput.value);
    }
});
</script>
@endsection
