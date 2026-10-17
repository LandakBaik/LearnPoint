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
                    value="{{ old('nip') }}" 
                    required 
                    placeholder="Contoh: 198501012010011005 (Tepat 18 digit angka)"
                    class="w-full px-4 py-2.5 rounded-xl border @error('nip') border-rose-400 bg-rose-50/30 @else border-gray-200 bg-gray-50/50 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono"
                >
                <p class="text-xs text-gray-500 mt-1">NIP harus tepat 18 digit angka.</p>

                <!-- Live Preview NIP: Tanggal Lahir & Jenis Kelamin -->
                <div id="nip-preview" class="hidden mt-2.5 p-3.5 rounded-xl bg-blue-50/80 border border-blue-200 text-xs space-y-1.5 transition-all">
                    <div class="font-bold text-blue-900 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Deteksi Otomatis Profil Guru dari NIP:</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-gray-700 pt-1">
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500 font-medium">Tanggal Lahir (8 Digit Pertama):</span>
                            <span id="preview-tgl-lahir" class="font-bold text-blue-800">-</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500 font-medium">Jenis Kelamin (1 Digit Ketiga):</span>
                            <span id="preview-jenis-kelamin" class="font-bold text-indigo-800">-</span>
                        </div>
                    </div>
                </div>

                @error('nip')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
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

                <div class="space-y-3">
                    <template x-for="(item, index) in assignments" :key="index">
                        <div class="p-4 bg-gray-50 rounded-2xl border border-gray-200 space-y-3">
                            <div class="flex items-center justify-between pb-2 border-b border-gray-200/60">
                                <span class="text-xs font-bold text-gray-800" x-text="'Penugasan Mapel #' + (index + 1)"></span>
                                <button 
                                    type="button" 
                                    @click="assignments.splice(index, 1)"
                                    class="p-1 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors inline-flex items-center gap-1 text-xs font-semibold"
                                    title="Hapus penugasan ini"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    <span>Hapus</span>
                                </button>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">Mata Pelajaran <span class="text-rose-500">*</span></label>
                                <select :name="'assignments[' + index + '][mapel_id]'" x-model="item.mapel_id" required class="w-full px-3 py-2 rounded-lg border border-gray-200 bg-white text-xs font-semibold text-gray-800 focus:ring-2 focus:ring-indigo-500">
                                    <option value="">-- Pilih Mata Pelajaran --</option>
                                    @foreach($mapels as $m)
                                        <option value="{{ $m->id }}">{{ $m->nama_mapel }} (KKM: {{ $m->kkm }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1.5">Pilih Kelas yang Diampu (Bisa Lebih Dari Satu) <span class="text-rose-500">*</span></label>
                                <div class="max-h-44 overflow-y-auto p-2.5 bg-white rounded-xl border border-gray-200 space-y-2.5">
                                    @foreach($kelases->groupBy('tingkatan') as $tingkat => $kelasList)
                                        <div>
                                            <p class="text-[10px] font-extrabold uppercase tracking-wider text-gray-400 mb-1">Tingkat {{ $tingkat }}</p>
                                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                                @foreach($kelasList as $k)
                                                    <label class="flex items-center gap-2 p-1.5 rounded-lg bg-gray-50 border border-gray-100 hover:border-indigo-300 hover:bg-indigo-50/30 cursor-pointer text-xs text-gray-700 font-medium">
                                                        <input 
                                                            type="checkbox" 
                                                            :name="'assignments[' + index + '][kelas_ids][]'" 
                                                            value="{{ $k->id }}"
                                                            class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                                        <span>{{ $k->nama_kelas }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
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

// Inisialisasi jika ada old value
document.addEventListener('DOMContentLoaded', function() {
    const nipInput = document.getElementById('nip');
    if (nipInput && nipInput.value) {
        updateNipPreview(nipInput.value);
    }
});
</script>
@endsection
