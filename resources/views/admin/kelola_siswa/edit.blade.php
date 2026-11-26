@extends('layouts.app')

@section('title', 'Edit Data Siswa')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header Navigation -->
    <div class="flex items-center gap-3">
        <a href="{{ route('siswa.index') }}" class="p-2 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Edit Data Siswa</h1>
            <p class="text-sm text-gray-500">Perbarui biodata dan data kelas untuk siswa <span class="font-bold text-gray-800">{{ $siswa->nama_siswa }}</span>.</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8">
        <form action="{{ route('siswa.update', $siswa->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Nama & NIS (Grid 2 Kolom) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="nama_siswa" class="block text-sm font-bold text-gray-700 mb-1">Nama Lengkap Siswa <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        id="nama_siswa"
                        name="nama_siswa"
                        value="{{ old('nama_siswa', $siswa->nama_siswa) }}"
                        required
                        maxlength="100"
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
                        value="{{ old('nis', $siswa->nis) }}"
                        required
                        class="w-full px-4 py-2.5 rounded-xl border @error('nis') border-rose-400 bg-rose-50/30 @else border-gray-200 bg-gray-50/50 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    >
                    <p class="text-xs text-gray-500 mt-1">NIS harus terdiri dari 4 hingga 10 digit angka.</p>
                    @error('nis')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Kelas & Angkatan (Grid 2 Kolom) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="kelas_id" class="block text-sm font-bold text-gray-700 mb-1">Kelas ({{ \App\Models\AcademicPeriod::activeTahunAjaran() }})</label>
                    <select id="kelas_id" name="kelas_id" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium text-gray-800">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelases as $k)
                            <option value="{{ $k->id }}" {{ old('kelas_id', $siswa->kelas?->id) == $k->id ? 'selected' : '' }}>
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
                    @php
                        $selectedAngkatan = (int) old('angkatan', $siswa->angkatan ?? \App\Models\AcademicPeriod::defaultAngkatan());
                        $maxYear = max((int) date('Y') + 5, $selectedAngkatan);
                        $minYear = min(2000, $selectedAngkatan > 0 ? $selectedAngkatan : 2000);
                    @endphp
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
                    <p class="text-xs text-gray-500 mt-1">Pilih tahun siswa pertama kali masuk sekolah (format tahun: YYYY).</p>
                    @error('angkatan')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Jenis Kelamin, Agama & Tanggal Lahir -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="jenis_kelamin" class="block text-sm font-bold text-gray-700 mb-1">Jenis Kelamin <span class="text-rose-500">*</span></label>
                    <select id="jenis_kelamin" name="jenis_kelamin" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium text-gray-800">
                        <option value="L" {{ old('jenis_kelamin', $siswa->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                        <option value="P" {{ old('jenis_kelamin', $siswa->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan (P)</option>
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
                            <option value="{{ $agamaOpt }}" {{ old('agama', $siswa->agama) == $agamaOpt ? 'selected' : '' }}>{{ $agamaOpt }}</option>
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
                        min="{{ now()->subYears(16)->format('Y-m-d') }}"
                        max="{{ now()->subYears(10)->format('Y-m-d') }}"
                        value="{{ old('tanggal_lahir', $siswa->tanggal_lahir) }}"
                        required
                        class="w-full px-4 py-2.5 rounded-xl border @error('tanggal_lahir') border-rose-400 bg-rose-50/30 @else border-gray-200 bg-gray-50/50 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    >
                    <p class="text-xs text-gray-500 mt-1">Usia siswa harus antara 10 hingga 16 tahun.</p>
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
                        value="{{ old('wali_murid', $siswa->wali_murid) }}"
                        required
                        maxlength="100"
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
                        value="{{ old('nohp_wali', $siswa->nohp_wali) }}"
                        required
                        class="w-full px-4 py-2.5 rounded-xl border @error('nohp_wali') border-rose-400 bg-rose-50/30 @else border-gray-200 bg-gray-50/50 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    >
                    @error('nohp_wali')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
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
                    class="w-full px-4 py-2.5 rounded-xl border @error('alamat') border-rose-400 bg-rose-50/30 @else border-gray-200 bg-gray-50/50 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                >{{ old('alamat', $siswa->alamat) }}</textarea>
                @error('alamat')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('siswa.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-emerald-200 transition-all hover:shadow-lg">
                    Perbarui Siswa
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
