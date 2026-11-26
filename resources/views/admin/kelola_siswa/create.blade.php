@extends('layouts.app')

@section('title', 'Tambah Data Siswa')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header Navigation -->
    <div class="flex items-center gap-3">
        <a href="{{ route('siswa.index') }}" class="p-2 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Tambah Siswa Baru</h1>
            <p class="text-sm text-gray-500">Daftarkan peserta didik baru dan tetapkan kelas.</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8">
        <form action="{{ route('siswa.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Nama & NIS (Grid 2 Kolom) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="nama_siswa" class="block text-sm font-bold text-gray-700 mb-1">Nama Lengkap Siswa <span class="text-rose-500">*</span></label>
                    <input 
                        type="text" 
                        id="nama_siswa" 
                        name="nama_siswa" 
                        value="{{ old('nama_siswa') }}" 
                        required 
                        maxlength="100"
                        placeholder="Contoh: Muhammad Rizky"
                        class="w-full px-4 py-2.5 rounded-xl border @error('nama_siswa') border-rose-400 bg-rose-50/30 @else border-gray-200 bg-gray-50/50 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    >
                    <p class="text-xs text-gray-500 mt-1">Maksimal 100 karakter (huruf, spasi, koma, titik).</p>
                    @error('nama_siswa')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- NIS Siswa (Min 4, Max 10 Digit) -->
                <div>
                    <label for="nis" class="block text-sm font-bold text-gray-700 mb-1">Nomor Induk Siswa (NIS) <span class="text-rose-500">*</span></label>
                    <input 
                        type="text" 
                        id="nis" 
                        name="nis" 
                        inputmode="numeric"
                        pattern="[0-9]{4,10}"
                        minlength="4"
                        maxlength="10"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                        value="{{ old('nis') }}" 
                        required 
                        placeholder="Contoh: 20241001 (4-10 digit)"
                        class="w-full px-4 py-2.5 rounded-xl border @error('nis') border-rose-400 bg-rose-50/30 @else border-gray-200 bg-gray-50/50 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    >
                    <p class="text-xs text-gray-500 mt-1">NIS harus terdiri dari 4 hingga 10 digit angka.</p>
                    @error('nis')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Kelas, Angkatan & Status (Grid 3 Kolom) -->
            @php
                $selectedAngkatan = (int) old('angkatan', \App\Models\AcademicPeriod::defaultAngkatan());
                $maxYear = max(\App\Models\Siswa::maxAngkatan(), $selectedAngkatan);
                $minYear = min(\App\Models\Siswa::MIN_ANGKATAN, $selectedAngkatan > 0 ? $selectedAngkatan : \App\Models\Siswa::MIN_ANGKATAN);
                $minBirthYear = $selectedAngkatan - \App\Models\Siswa::MAX_ENTRY_AGE;
                $maxBirthYear = $selectedAngkatan - \App\Models\Siswa::MIN_ENTRY_AGE;
            @endphp
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="kelas_id" class="block text-sm font-bold text-gray-700 mb-1">Kelas ({{ \App\Models\AcademicPeriod::activeTahunAjaran() }})</label>
                    <select id="kelas_id" name="kelas_id" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium text-gray-800">
                        <option value="">-- Tanpa Kelas / Alumni --</option>
                        @foreach($kelases as $k)
                            <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kelas }} (Tingkat {{ $k->tingkatan }})
                            </option>
                        @endforeach
                    </select>
                    @error('kelas_id')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="angkatan" class="block text-sm font-bold text-gray-700 mb-1">Angkatan (Tahun Masuk)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <select
                            id="angkatan"
                            name="angkatan"
                            class="w-full pl-10 pr-4 py-2.5 rounded-xl border @error('angkatan') border-rose-400 bg-rose-50/30 @else border-gray-200 bg-gray-50/50 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium text-gray-800"
                        >
                            @for($yr = $maxYear; $yr >= $minYear; $yr--)
                                <option value="{{ $yr }}" {{ $selectedAngkatan === $yr ? 'selected' : '' }}>{{ $yr }}</option>
                            @endfor
                        </select>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Tahun masuk sekolah ({{ $minYear }}–{{ $maxYear }}).</p>
                    @error('angkatan')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-bold text-gray-700 mb-1">Status Siswa</label>
                    <select id="status" name="status" class="w-full px-4 py-2.5 rounded-xl border @error('status') border-rose-400 bg-rose-50/30 @else border-gray-200 bg-gray-50/50 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium text-gray-800">
                        @foreach(\App\Models\Siswa::STATUS_OPTIONS as $statusVal => $statusLabel)
                            <option value="{{ $statusVal }}" {{ old('status', 'aktif') === $statusVal ? 'selected' : '' }}>{{ $statusLabel }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Pilih "Lulus" untuk data alumni.</p>
                    @error('status')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Jenis Kelamin, Agama & Tanggal Lahir -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="jenis_kelamin" class="block text-sm font-bold text-gray-700 mb-1">Jenis Kelamin <span class="text-rose-500">*</span></label>
                    <select id="jenis_kelamin" name="jenis_kelamin" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium text-gray-800">
                        <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                        <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan (P)</option>
                    </select>
                    @error('jenis_kelamin')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="agama" class="block text-sm font-bold text-gray-700 mb-1">Agama</label>
                    <select id="agama" name="agama" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium text-gray-800">
                        <option value="">-- Pilih Agama --</option>
                        @foreach(\App\Models\Siswa::AGAMA_OPTIONS as $agamaOpt)
                            <option value="{{ $agamaOpt }}" {{ old('agama') == $agamaOpt ? 'selected' : '' }}>{{ $agamaOpt }}</option>
                        @endforeach
                    </select>
                    @error('agama')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="tanggal_lahir" class="block text-sm font-bold text-gray-700 mb-1">Tanggal Lahir <span class="text-rose-500">*</span></label>
                    <input 
                        type="date" 
                        id="tanggal_lahir" 
                        name="tanggal_lahir" 
                        min="{{ $minBirthYear }}-01-01"
                        max="{{ $maxBirthYear }}-12-31"
                        value="{{ old('tanggal_lahir') }}" 
                        required 
                        class="w-full px-4 py-2.5 rounded-xl border @error('tanggal_lahir') border-rose-400 bg-rose-50/30 @else border-gray-200 bg-gray-50/50 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    >
                    <p id="tanggal_lahir_hint" class="text-xs text-gray-500 mt-1">Usia masuk {{ \App\Models\Siswa::MIN_ENTRY_AGE }}–{{ \App\Models\Siswa::MAX_ENTRY_AGE }} tahun (lahir {{ $minBirthYear }}–{{ $maxBirthYear }} untuk angkatan {{ $selectedAngkatan }}).</p>
                    @error('tanggal_lahir')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Wali Murid & No HP Wali -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="wali_murid" class="block text-sm font-bold text-gray-700 mb-1">Nama Orang Tua / Wali <span class="text-rose-500">*</span></label>
                    <input 
                        type="text" 
                        id="wali_murid" 
                        name="wali_murid" 
                        value="{{ old('wali_murid') }}" 
                        required 
                        maxlength="100"
                        placeholder="Contoh: Hendra Kusuma"
                        class="w-full px-4 py-2.5 rounded-xl border @error('wali_murid') border-rose-400 bg-rose-50/30 @else border-gray-200 bg-gray-50/50 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    >
                    <p class="text-xs text-gray-400 mt-1">Maksimal 100 karakter.</p>
                    @error('wali_murid')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="nohp_wali" class="block text-sm font-bold text-gray-700 mb-1">No. HP / WhatsApp Wali <span class="text-rose-500">*</span></label>
                    <input 
                        type="text" 
                        id="nohp_wali" 
                        name="nohp_wali" 
                        inputmode="numeric"
                        pattern="[0-9]*"
                        minlength="10"
                        maxlength="13"
                        value="{{ old('nohp_wali') }}" 
                        required 
                        placeholder="Contoh: 081234567890 (Hanya angka)"
                        class="w-full px-4 py-2.5 rounded-xl border @error('nohp_wali') border-rose-400 bg-rose-50/30 @else border-gray-200 bg-gray-50/50 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    >
                    @error('nohp_wali')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-400 mt-1">Nomor telepon harus berupa karakter angka (10-13 digit).</p>
                </div>
            </div>

            <!-- Alamat Lengkap -->
            <div>
                <label for="alamat" class="block text-sm font-bold text-gray-700 mb-1">Alamat Tempat Tinggal <span class="text-rose-500">*</span></label>
                <textarea 
                    id="alamat" 
                    name="alamat" 
                    rows="3" 
                    required 
                    placeholder="Contoh: Jl. Merpati Putih No. 12, Kel. Sukamaju, Kec. Cilandak"
                    class="w-full px-4 py-2.5 rounded-xl border @error('alamat') border-rose-400 bg-rose-50/30 @else border-gray-200 bg-gray-50/50 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                >{{ old('alamat') }}</textarea>
                @error('alamat')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Auto Account Generation Notice (Poin 5) -->
            <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200 flex items-start gap-3">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-emerald-900">Akun Siswa Dibuat Otomatis</h4>
                    <p class="text-xs text-emerald-700 mt-0.5 leading-relaxed">
                        Akun login untuk siswa ini akan dibuatkan otomatis oleh sistem dengan rincian:<br>
                        <span class="font-bold text-emerald-900">Username: NIS</span> &bull; 
                        <span class="font-bold text-emerald-900">Password: NIS</span> &bull; 
                        <span class="font-bold text-emerald-900">Email: NIS@siswa.learnpoint.sch.id</span> &bull; 
                        <span class="font-bold text-emerald-800">Status Akun: Mengikuti Status Siswa (Aktif / Nonaktif)</span>
                    </p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('siswa.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-emerald-200 transition-all hover:shadow-lg">
                    Simpan Data Siswa
                </button>
            </div>
        </form>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const angkatanEl = document.getElementById('angkatan');
    const tglEl = document.getElementById('tanggal_lahir');
    const hintEl = document.getElementById('tanggal_lahir_hint');
    if (!angkatanEl || !tglEl) return;

    const minAge = {{ \App\Models\Siswa::MIN_ENTRY_AGE }};
    const maxAge = {{ \App\Models\Siswa::MAX_ENTRY_AGE }};

    function syncBirthDateBounds() {
        const angkatan = parseInt(angkatanEl.value, 10);
        if (!angkatan) return;
        const minYear = angkatan - maxAge;
        const maxYear = angkatan - minAge;
        tglEl.min = `${minYear}-01-01`;
        tglEl.max = `${maxYear}-12-31`;
        if (hintEl) {
            hintEl.textContent = `Usia masuk ${minAge}–${maxAge} tahun (lahir ${minYear}–${maxYear} untuk angkatan ${angkatan}).`;
        }
    }

    angkatanEl.addEventListener('change', syncBirthDateBounds);
});
</script>
@endsection
