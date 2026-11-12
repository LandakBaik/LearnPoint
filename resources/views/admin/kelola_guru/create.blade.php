@extends('layouts.app')

@section('title', 'Tambah Data Guru')

@section('content')
<div class="max-w-3xl mx-auto space-y-6" 
    x-data="{ 
        assignments: [],
        existingPengampus: {{ $existingPengampus->toJson() }},
        allClassIds: {{ $kelases->pluck('id')->toJson() }},

        getExistingPengampu(mapelId, kelasId) {
            if (!mapelId) return null;
            return this.existingPengampus.find(p => p.mapel_id == mapelId && p.kelas_id == kelasId);
        },

        isChosenInOtherRow(mapelId, kelasId, currentIndex) {
            if (!mapelId) return false;
            return this.assignments.some((row, idx) => {
                if (idx === currentIndex) return false;
                return row.mapel_id == mapelId && Array.isArray(row.kelas_ids) && row.kelas_ids.includes(kelasId);
            });
        },

        isClassDisabled(kelasId, mapelId, currentIndex) {
            if (!mapelId) return true;
            if (this.getExistingPengampu(mapelId, kelasId)) return true;
            if (this.isChosenInOtherRow(mapelId, kelasId, currentIndex)) return true;
            return false;
        },

        getConflictTeacherName(mapelId, kelasId, currentIndex) {
            if (!mapelId) return '';
            const existing = this.getExistingPengampu(mapelId, kelasId);
            if (existing) return existing.guru_nama;
            if (this.isChosenInOtherRow(mapelId, kelasId, currentIndex)) return 'Baris Lain';
            return '';
        },

        getAvailableClassIds(mapelId, currentIndex) {
            if (!mapelId) return [];
            return this.allClassIds.filter(id => !this.isClassDisabled(id, mapelId, currentIndex));
        },

        isAllAvailableSelected(index) {
            const item = this.assignments[index];
            if (!item || !item.mapel_id) return false;
            const available = this.getAvailableClassIds(item.mapel_id, index);
            if (available.length === 0) return false;
            const selected = item.kelas_ids || [];
            return available.every(id => selected.includes(id));
        },

        toggleAllForRow(index) {
            const item = this.assignments[index];
            if (!item || !item.mapel_id) return;
            if (!Array.isArray(item.kelas_ids)) item.kelas_ids = [];
            const available = this.getAvailableClassIds(item.mapel_id, index);
            if (available.length === 0) return;

            if (this.isAllAvailableSelected(index)) {
                item.kelas_ids = item.kelas_ids.filter(id => !available.includes(id));
            } else {
                const set = new Set([...item.kelas_ids, ...available]);
                item.kelas_ids = Array.from(set);
            }
        },

        onMapelChange(index) {
            const item = this.assignments[index];
            if (!item) return;
            if (Array.isArray(item.kelas_ids)) {
                item.kelas_ids = item.kelas_ids.filter(id => !this.isClassDisabled(id, item.mapel_id, index));
            }
        }
    }"
>

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

            <!-- NUPTK Guru (Wajib 16 Digit Angka) -->
            <div>
                <label for="nuptk" class="block text-sm font-bold text-gray-700 mb-1">Nomor Unik Pendidik dan Tenaga Kependidikan (NUPTK) <span class="text-rose-500">*</span></label>
                <input 
                    type="text" 
                    id="nuptk" 
                    name="nuptk" 
                    inputmode="numeric"
                    pattern="[0-9]{16}"
                    minlength="16"
                    maxlength="16"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 16);"
                    value="{{ old('nuptk') }}" 
                    required 
                    placeholder="Contoh: 1234567890123456 (Tepat 16 digit angka)"
                    class="w-full px-4 py-2.5 rounded-xl border @error('nuptk') border-rose-400 bg-rose-50/30 @else border-gray-200 bg-gray-50/50 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                <p class="text-xs text-gray-500 mt-1">NUPTK harus tepat 16 digit angka.</p>

                @error('nuptk')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Grid: Jenis Kelamin & Nomor Telepon -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Jenis Kelamin -->
                <div>
                    <label for="jenis_kelamin" class="block text-sm font-bold text-gray-700 mb-1">Jenis Kelamin</label>
                    <select 
                        id="jenis_kelamin" 
                        name="jenis_kelamin" 
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="">-- Pilih Jenis Kelamin --</option>
                        <option value="Laki-laki" {{ old('jenis_kelamin') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="Perempuan" {{ old('jenis_kelamin') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    @error('jenis_kelamin')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nomor Telepon -->
                <div>
                    <label for="nomor_telepon" class="block text-sm font-bold text-gray-700 mb-1">Nomor Telepon / WhatsApp</label>
                    <input 
                        type="tel" 
                        id="nomor_telepon" 
                        name="nomor_telepon" 
                        value="{{ old('nomor_telepon') }}" 
                        placeholder="Contoh: 081234567890" 
                        maxlength="13"
                        minlength="10"
                        pattern="[0-9]{10,13}"
                        inputmode="numeric"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 13)"
                        class="w-full px-4 py-2.5 rounded-xl border @error('nomor_telepon') border-rose-400 bg-rose-50/30 @else border-gray-200 bg-gray-50/50 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                    <p class="text-[11px] text-gray-400 mt-1">Hanya angka, 10 - 13 digit.</p>
                    @error('nomor_telepon')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Auto Account Generation Notice -->
            <div class="p-4 rounded-2xl bg-indigo-50/70 border border-indigo-200 flex items-start gap-3">
                <div class="w-8 h-8 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-indigo-900">Akun Pengguna Dibuat Otomatis</h4>
                    <p class="text-xs text-indigo-700 mt-0.5 leading-relaxed">
                        Akun login untuk guru ini akan otomatis dibuatkan oleh sistem dengan rincian:<br>
                        <span class="font-bold text-indigo-900">Username: NUPTK</span> &bull; 
                        <span class="font-bold text-indigo-900">Password: NUPTK</span> &bull; 
                        <span class="font-bold text-indigo-900">Email: NUPTK@guru.learnpoint.sch.id</span> &bull; 
                        <span class="font-bold text-emerald-700">Status: Aktif</span>
                    </p>
                </div>
            </div>

            <!-- Seksi Penugasan Mata Pelajaran & Kelas yang Diampu -->
            <div class="space-y-3 pt-2">
                <div class="flex items-center justify-between">
                    <div>
                        <label class="block text-sm font-bold text-gray-800">Mata Pelajaran & Kelas yang Diampu (Opsional)</label>
                        <p class="text-xs text-gray-500">Pilih mata pelajaran yang diajarkan oleh guru ini beserta kelasnya.</p>
                    </div>
                    <button 
                        type="button" 
                        @click="assignments.push({ mapel_id: '', kelas_ids: [] })"
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
                                <select 
                                    :name="'assignments[' + index + '][mapel_id]'" 
                                    x-model="item.mapel_id" 
                                    @change="onMapelChange(index)"
                                    required 
                                    class="w-full px-3 py-2 rounded-lg border border-gray-200 bg-white text-xs font-semibold text-gray-800 focus:ring-2 focus:ring-indigo-500"
                                >
                                    <option value="">-- Pilih Mata Pelajaran --</option>
                                    @foreach($mapels as $m)
                                        <option value="{{ $m->id }}">{{ $m->nama_mapel }} (KKM: {{ $m->kkm }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Pilih Kelas -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="block text-[11px] font-bold text-gray-500 uppercase">Pilih Kelas yang Diampu (Bisa Lebih Dari Satu) <span class="text-rose-500">*</span></label>
                                    <template x-if="item.mapel_id">
                                        <button 
                                            type="button" 
                                            @click="toggleAllForRow(index)"
                                            :disabled="getAvailableClassIds(item.mapel_id, index).length === 0"
                                            :class="getAvailableClassIds(item.mapel_id, index).length === 0 ? 'opacity-40 cursor-not-allowed' : 'text-indigo-600 hover:text-indigo-800 hover:underline'"
                                            class="text-[11px] font-bold transition-all"
                                        >
                                            <span x-text="getAvailableClassIds(item.mapel_id, index).length === 0 ? 'Semua Kelas Sudah Terisi' : (isAllAvailableSelected(index) ? 'Batalkan Semua' : 'Pilih Semua Kelas Tersedia')"></span>
                                        </button>
                                    </template>
                                </div>

                                <template x-if="!item.mapel_id">
                                    <div class="p-3 bg-amber-50/70 border border-amber-200/80 rounded-xl text-xs text-amber-800">
                                        Pilih mata pelajaran terlebih dahulu untuk melihat ketersediaan kelas.
                                    </div>
                                </template>

                                <template x-if="item.mapel_id">
                                    <div class="max-h-52 overflow-y-auto p-2.5 bg-white rounded-xl border border-gray-200 space-y-2.5">
                                        @foreach($kelases->groupBy('tingkatan') as $tingkat => $kelasList)
                                            <div>
                                                <p class="text-[10px] font-extrabold uppercase tracking-wider text-gray-400 mb-1">Tingkat {{ $tingkat }}</p>
                                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                                    @foreach($kelasList as $k)
                                                        <label 
                                                            :class="isClassDisabled({{ $k->id }}, item.mapel_id, index) 
                                                                ? 'bg-gray-100 border-gray-200 text-gray-400 cursor-not-allowed opacity-80 select-none' 
                                                                : ((item.kelas_ids || []).includes({{ $k->id }}) ? 'bg-indigo-50/70 border-indigo-300 ring-1 ring-indigo-400/30' : 'bg-gray-50 border-gray-200/80 hover:border-indigo-300 hover:bg-indigo-50/30') + ' cursor-pointer text-gray-800'"
                                                            class="flex items-center justify-between p-2 rounded-xl border transition-all text-xs font-medium"
                                                        >
                                                            <div class="flex items-center gap-2 truncate">
                                                                <template x-if="isClassDisabled({{ $k->id }}, item.mapel_id, index)">
                                                                    <span class="w-3.5 h-3.5 rounded bg-gray-200 text-gray-400 flex items-center justify-center shrink-0 text-[10px] font-bold">✕</span>
                                                                </template>
                                                                <template x-if="!isClassDisabled({{ $k->id }}, item.mapel_id, index)">
                                                                    <input 
                                                                        type="checkbox" 
                                                                        :name="'assignments[' + index + '][kelas_ids][]'" 
                                                                        value="{{ $k->id }}"
                                                                        x-model="item.kelas_ids"
                                                                        class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                                                </template>
                                                                <span :class="isClassDisabled({{ $k->id }}, item.mapel_id, index) ? 'text-gray-400 font-normal line-through' : 'text-gray-800 font-bold'">
                                                                    {{ $k->nama_kelas }}
                                                                </span>
                                                            </div>

                                                            <div class="shrink-0 ml-1">
                                                                <template x-if="isClassDisabled({{ $k->id }}, item.mapel_id, index)">
                                                                    <span 
                                                                        class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 truncate max-w-[110px] inline-block align-middle"
                                                                        :title="'Sudah diampu: ' + getConflictTeacherName(item.mapel_id, {{ $k->id }}, index)"
                                                                        x-text="'🔒 ' + getConflictTeacherName(item.mapel_id, {{ $k->id }}, index)"
                                                                    ></span>
                                                                </template>
                                                                <template x-if="!isClassDisabled({{ $k->id }}, item.mapel_id, index)">
                                                                    <span class="text-[9px] font-semibold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-100">
                                                                        Tersedia
                                                                    </span>
                                                                </template>
                                                            </div>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </template>
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
