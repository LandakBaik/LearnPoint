@extends('layouts.app')

@section('title', 'Edit Ujian')

@section('content')
    @php
        $onlyBobot = $modeEditBobot ?? in_array($ujian->status, ['unpublished', 'completed'], true);
    @endphp

    <div class="max-w-7xl mx-auto space-y-6" x-data="editUjian()">

        {{-- HEADER --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <a href="{{ route('guru.ujian.index') }}"
                    class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-500 hover:text-teal-700 transition">
                    ← Kembali ke Dashboard Ujian
                </a>

                <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 tracking-tight mt-2">
                    @if ($onlyBobot)
                        Penyesuaian Bobot Soal Ujian
                    @elseif ($modeJadwalkan)
                        Jadwalkan Ujian
                    @else
                        Edit Ujian
                    @endif
                </h1>

                @if ($modeJadwalkan && !$onlyBobot)
                    <div class="mt-4 p-4 bg-amber-50 border border-amber-200 rounded-xl">
                        <div class="flex items-start gap-3">
                            <span class="text-xl">📅</span>
                            <div>
                                <p class="font-semibold text-amber-800">Lengkapi jadwal ujian</p>
                                <p class="text-sm text-amber-700 mt-1">
                                    Isi Waktu Mulai dan Deadline untuk menjadwalkan ujian.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <p class="text-gray-500 mt-1 text-sm">
                    {{ $ujian->judul }}
                </p>
            </div>
        </div>

        {{-- BANNER MODE PENYESUAIAN BOBOT SOAL (MUNCUL SAAT UNPUBLISHED / COMPLETED) --}}
        @if ($onlyBobot)
            <div class="p-4 sm:p-5 rounded-2xl bg-amber-50 border border-amber-200 flex items-start gap-3.5 shadow-2xs">
                <span class="text-xl shrink-0">🔒</span>
                <div class="text-xs sm:text-sm text-amber-900 space-y-1">
                    <p class="font-bold">
                        Mode Penyesuaian Bobot Soal Aktif (Status: {{ $ujian->status_label ?? ucfirst($ujian->status) }})
                    </p>
                    <p class="text-amber-800 leading-relaxed">
                        Untuk menjaga integritas dan mencegah hilangnya riwayat pengerjaan siswa pada sesi sebelumnya, seluruh pengaturan ujian, teks pertanyaan, gambar, pilihan ganda, serta kunci jawaban <strong>dikunci</strong>. Anda hanya dapat mengubah <strong>Tingkat Kesulitan / Bobot Soal</strong> (termasuk memilih <em>"Tidak Valid (Poin: 0)"</em> untuk menganulir soal).
                    </p>
                </div>
            </div>
        @endif

        {{-- INFORMASI DASAR UJIAN (DIKUNCI OTOMATIS JIKA $onlyBobot) --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 {{ $onlyBobot ? 'opacity-65 pointer-events-none select-none' : '' }}">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-lg font-bold text-gray-800">
                    Pengaturan Ujian
                </h2>
                @if ($onlyBobot)
                    <span class="text-xs font-bold px-2.5 py-1 rounded-lg bg-gray-100 text-gray-600 border border-gray-200">
                        🔒 Terkunci
                    </span>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                {{-- JUDUL --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Judul Ujian <span class="text-red-500">*</span>
                    </label>

                    <input type="text" name="judul" x-ref="inputJudul" form="form-edit-ujian"
                        value="{{ old('judul', $ujian->judul) }}" maxlength="100"
                        {{ $onlyBobot ? 'readonly tabindex=-1' : '' }}
                        oninput="this.value = this.value.replace(/[^\p{L}\p{N} ]/gu, '')"
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 {{ $onlyBobot ? 'bg-gray-100 cursor-not-allowed' : 'focus:ring-2 focus:ring-teal-500 focus:border-teal-500' }}">

                    <p class="text-xs text-gray-400 mt-1">
                        Maksimal 100 karakter (huruf, angka, dan spasi).
                    </p>

                    @error('judul')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- FILTER BERTINGKAT: MAPEL + TINGKATAN + TARGET KELAS --}}
                <div class="md:col-span-2" id="section-target-kelas">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Mata Pelajaran & Target Kelas <span class="text-red-500">*</span>
                    </label>

                    <div class="border border-gray-300 rounded-xl p-4 space-y-5">

                        {{-- 1. PILIH MAPEL --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                1. Mata Pelajaran
                            </label>

                            <select name="mapel_id" form="form-edit-ujian" x-model="selectedMapel" @change="ubahMapel()"
                                {{ $onlyBobot ? 'disabled' : '' }}
                                class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 disabled:bg-gray-100">
                                <option value="">-- Pilih Mata Pelajaran --</option>
                                <template x-for="mapel in mapelList" :key="mapel.id">
                                    <option :value="String(mapel.id)" x-text="mapel.nama"></option>
                                </template>
                            </select>

                            {{-- INFO PERUBAHAN MAPEL --}}
                            <div x-show="selectedMapel && initialMapel && String(selectedMapel) !== String(initialMapel)"
                                class="mt-2.5 p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800">
                                <strong>Perhatian:</strong> Anda mengubah mata pelajaran ujian. Setelah disimpan, ujian akan dipindahkan ke mata pelajaran &amp; kelas baru dan Anda akan diarahkan langsung ke Dashboard Ujian.
                            </div>
                        </div>

                        {{-- 2. PILIH TINGKATAN --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                2. Tingkatan Kelas
                            </label>

                            <select x-model="selectedTingkatan" @change="ubahTingkatan()"
                                :disabled="onlyBobot || !selectedMapel || daftarTingkatan.length === 0"
                                class="w-full border border-gray-300 rounded-xl px-4 py-3 disabled:bg-gray-100 disabled:text-gray-400 focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                                <option value="">-- Pilih Tingkatan --</option>
                                <template x-for="tingkat in daftarTingkatan" :key="tingkat.nama">
                                    <option :value="tingkat.nama" x-text="tingkat.nama"></option>
                                </template>
                            </select>

                            <p x-show="selectedMapel && daftarTingkatan.length === 0" class="text-xs text-amber-600 mt-2">
                                Belum ada tingkatan kelas untuk mata pelajaran ini.
                            </p>
                        </div>

                        {{-- 3. PILIH KELAS --}}
                        <div>
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
                                <label class="block text-sm font-medium text-gray-700">
                                    3. Pilih Kelas Target
                                </label>

                                <span x-show="kelasTerlihat.length > 0" class="text-xs font-semibold text-teal-700"
                                    x-text="kelasDipilih.length + ' kelas dipilih'"></span>
                            </div>

                            <div x-show="!selectedMapel"
                                class="p-4 bg-gray-50 border border-dashed border-gray-300 rounded-xl text-center">
                                <p class="text-sm text-gray-500">Pilih mata pelajaran terlebih dahulu.</p>
                            </div>

                            <div x-show="selectedMapel && !selectedTingkatan"
                                class="p-4 bg-gray-50 border border-dashed border-gray-300 rounded-xl text-center">
                                <p class="text-sm text-gray-500">Pilih tingkatan untuk menampilkan daftar kelas.</p>
                            </div>

                            <div x-show="selectedMapel && selectedTingkatan"
                                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                <template x-for="kelas in kelasTerlihat" :key="kelas.pengampu_kelas_id">
                                    <label
                                        class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-teal-50 cursor-pointer transition"
                                        :class="kelasDipilih.includes(String(kelas.pengampu_kelas_id)) ?
                                            'border-teal-500 bg-teal-50' : 'bg-white'">
                                        <input type="checkbox" name="pengampu_kelas_id[]" form="form-edit-ujian"
                                            :value="String(kelas.pengampu_kelas_id)" x-model="kelasDipilih"
                                            {{ $onlyBobot ? 'disabled' : '' }}
                                            class="w-4 h-4 text-teal-700 rounded focus:ring-teal-500">
                                        <span class="text-sm text-gray-700 font-medium" x-text="kelas.nama_kelas"></span>
                                    </label>
                                </template>

                                <div x-show="kelasTerlihat.length === 0"
                                    class="sm:col-span-2 lg:col-span-3 p-4 text-center bg-gray-50 rounded-xl border border-dashed border-gray-300">
                                    <p class="text-sm text-gray-500">Tidak ada kelas pada tingkatan ini.</p>
                                </div>
                            </div>
                        </div>

                        {{-- RINGKASAN KELAS DIPILIH --}}
                        <div x-show="kelasDipilih.length > 0" class="p-4 bg-teal-50 border border-teal-100 rounded-xl">
                            <p class="text-sm font-semibold text-teal-800">Target ujian aktif:</p>
                            <p class="text-sm text-teal-700 mt-0.5" x-text="kelasDipilih.length + ' kelas telah dipilih.'">
                            </p>
                        </div>
                    </div>

                    @error('pengampu_kelas_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- DURASI --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Durasi Ujian <span class="text-red-500">*</span>
                    </label>

                    <div class="relative">
                        <input type="text" inputmode="numeric" name="durasi_menit" x-ref="inputDurasi"
                            form="form-edit-ujian" value="{{ old('durasi_menit', $ujian->durasi_menit) }}" maxlength="3"
                            {{ $onlyBobot ? 'readonly tabindex=-1' : '' }}
                            oninput="
                                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 3);
                                if (this.value !== '' && Number(this.value) > 999) {
                                    this.value = '999';
                                }
                            "
                            class="w-full border border-gray-300 rounded-xl px-4 py-3 pr-20 {{ $onlyBobot ? 'bg-gray-100' : 'focus:ring-2 focus:ring-teal-500 focus:border-teal-500' }}">
                        <span class="absolute right-4 top-3 text-gray-400 text-sm">menit</span>
                    </div>

                    @error('durasi_menit')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- KKM --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        KKM <span class="text-red-500">*</span>
                    </label>

                    <input type="text" inputmode="numeric" name="kkm" x-ref="inputKkm" form="form-edit-ujian"
                        value="{{ old('kkm', (int) ($ujian->kkm ?? 75)) }}" maxlength="3"
                        {{ $onlyBobot ? 'readonly tabindex=-1' : '' }}
                        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 3); if (Number(this.value) > 100) this.value = '100';"
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 {{ $onlyBobot ? 'bg-gray-100' : 'focus:ring-2 focus:ring-teal-500 focus:border-teal-500' }}">

                    <p class="text-xs text-gray-400 mt-1">Nilai KKM 0–100.</p>

                    @error('kkm')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Pengaturan Visibilitas Nilai Siswa --}}
                <div class="md:col-span-2 p-4 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between gap-4">
                    <div>
                        <label for="tampilkan_nilai" class="text-sm font-bold text-gray-900 cursor-pointer block">
                            Tampilkan Nilai ke Siswa Setelah Ujian
                        </label>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Jika dinonaktifkan, siswa hanya melihat konfirmasi bahwa ujian telah terkirim tanpa mengetahui
                            skor nilainya.
                        </p>
                    </div>

                    <label class="relative inline-flex items-center cursor-pointer shrink-0">
                        <input type="hidden" name="tampilkan_nilai" value="0" form="form-edit-ujian">
                        <input type="checkbox" id="tampilkan_nilai" name="tampilkan_nilai" value="1"
                            form="form-edit-ujian"
                            {{ $onlyBobot ? 'disabled' : '' }}
                            {{ old('tampilkan_nilai', $ujian->tampilkan_nilai) ? 'checked' : '' }} class="sr-only peer">
                        <div
                            class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                        </div>
                    </label>
                </div>

                {{-- WAKTU MULAI --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Waktu Mulai <span class="font-normal text-gray-400">({{ $modeJadwalkan ? 'wajib diisi' : 'opsional untuk draft' }})</span>
                    </label>

                    <input type="datetime-local" name="waktu_mulai" x-ref="inputWaktuMulai" form="form-edit-ujian"
                        value="{{ old('waktu_mulai', $ujian->waktu_mulai?->format('Y-m-d\TH:i')) }}"
                        {{ $onlyBobot ? 'readonly tabindex=-1' : '' }}
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 {{ $onlyBobot ? 'bg-gray-100' : 'focus:ring-2 focus:ring-teal-500 focus:border-teal-500' }}">

                    @error('waktu_mulai')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- DEADLINE --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Deadline <span class="font-normal text-gray-400">({{ $modeJadwalkan ? 'wajib diisi' : 'opsional untuk draft' }})</span>
                    </label>

                    <input type="datetime-local" name="deadline" x-ref="inputDeadline" form="form-edit-ujian"
                        value="{{ old('deadline', $ujian->deadline?->format('Y-m-d\TH:i')) }}"
                        {{ $onlyBobot ? 'readonly tabindex=-1' : '' }}
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 {{ $onlyBobot ? 'bg-gray-100' : 'focus:ring-2 focus:ring-teal-500 focus:border-teal-500' }}">

                    @error('deadline')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- PEMBAGIAN SESI & KODE MASUK (TOKEN) --}}
                <div class="md:col-span-2">
                    <div x-show="kelasDipilih.length === 0 || !waktuMulai"
                        class="p-4 rounded-2xl bg-slate-50 border border-dashed border-slate-300 text-xs text-slate-500 flex items-center gap-2.5">
                        <span class="text-base">🔑</span>
                        <span>
                            Isi <strong>Waktu Mulai</strong> dan pilih minimal 1 kelas untuk mengatur <strong>Sesi Ujian</strong> dan menampilkan <strong>Kode Masuk (Token)</strong> masing-masing sesi.
                        </span>
                    </div>

                    <div x-show="kelasDipilih.length > 0 && waktuMulai" x-cloak
                        class="rounded-2xl border border-indigo-200 bg-indigo-50/40 p-5 space-y-5">

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-indigo-100">
                            <div>
                                <h3 class="text-sm font-extrabold text-indigo-950 flex items-center gap-2">
                                    <span>🔑</span> Pembagian Sesi Kelas & Kode Masuk (Token)
                                </h3>
                                <p class="text-xs text-indigo-700/80 mt-0.5">
                                    Setiap sesi memiliki kode masuk unik yang dapat langsung dibagikan atau diacak ulang.
                                </p>
                            </div>
                            <span class="text-xs font-bold px-3 py-1 rounded-full bg-indigo-100 text-indigo-800 self-start sm:self-auto"
                                x-text="ringkasanSesiAktif.length + ' Sesi Aktif'"></span>
                        </div>

                        <div class="space-y-2.5">
                            <p class="text-xs font-bold text-gray-700 uppercase tracking-wider">
                                1. Tentukan Sesi Masing-Masing Kelas
                            </p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                <template x-for="kelas in detailKelasDipilih" :key="kelas.pengampu_kelas_id">
                                    <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-white border border-indigo-100 shadow-2xs">
                                        <span class="text-sm font-bold text-gray-800 truncate"
                                            x-text="'Kelas ' + kelas.nama_kelas"></span>

                                        <select :name="`sesi_kelas[${kelas.pengampu_kelas_id}]`" form="form-edit-ujian"
                                            x-model.number="sesiKelas[String(kelas.pengampu_kelas_id)]"
                                            {{ $onlyBobot ? 'disabled' : '' }}
                                            @change="pastikanTokenSesi($event.target.value); sinkronkanJamSesi();"
                                            class="px-3 py-1.5 rounded-lg border border-indigo-200 text-xs font-bold text-indigo-700 bg-indigo-50/60 focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                                            <option value="1">Sesi 1</option>
                                            <option value="2">Sesi 2</option>
                                            <option value="3">Sesi 3</option>
                                            <option value="4">Sesi 4</option>
                                            <option value="5">Sesi 5</option>
                                        </select>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="space-y-2.5 pt-2 border-t border-indigo-100">
                            <p class="text-xs font-bold text-gray-700 uppercase tracking-wider">
                                2. Kode Masuk & Jadwal per Sesi
                            </p>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5">
                                <template x-for="item in ringkasanSesiAktif" :key="item.sesi">
                                    <div class="rounded-xl bg-white border border-indigo-200/80 p-4 space-y-3 shadow-2xs">
                                        <div class="flex items-start justify-between gap-2">
                                            <div>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[11px] font-extrabold bg-indigo-600 text-white">
                                                    SESI <span class="ml-1" x-text="item.sesi"></span>
                                                </span>
                                                <p class="text-xs font-semibold text-gray-700 mt-1.5">
                                                    Kelas: <span class="text-indigo-700 font-bold" x-text="item.kelasList.join(', ')"></span>
                                                </p>
                                            </div>

                                            <div class="text-right">
                                                <span class="block text-[10px] font-bold uppercase text-gray-400">Kode Masuk</span>
                                                <span class="inline-block mt-0.5 font-mono text-base font-black tracking-widest text-indigo-700 bg-indigo-50 border border-indigo-200 px-2.5 py-1 rounded-lg select-all"
                                                    x-text="tokenSesi[item.sesi]"></span>
                                                <input type="hidden" form="form-edit-ujian"
                                                    :name="`token_sesi[${item.sesi}]`" :value="tokenSesi[item.sesi]">
                                            </div>
                                        </div>

                                        <div class="flex items-center justify-between gap-2 pt-2 border-t border-gray-100">
                                            <label class="text-[11px] font-semibold text-gray-600">Jam Mulai:</label>
                                            <input type="time" form="form-edit-ujian"
                                                :name="`jam_sesi[${item.sesi}]`"
                                                x-model="jamSesi[item.sesi]"
                                                {{ $onlyBobot ? 'readonly' : '' }}
                                                class="text-xs font-mono font-bold text-gray-800 border border-gray-300 rounded-lg px-2 py-1 focus:border-indigo-500">
                                        </div>

                                        @if (!$onlyBobot)
                                            <div class="flex items-center gap-2">
                                                <button type="button" @click="salinKode(tokenSesi[item.sesi])"
                                                    class="flex-1 py-1.5 px-2 bg-gray-50 hover:bg-gray-100 text-gray-700 border border-gray-200 rounded-lg text-[11px] font-bold transition cursor-pointer">
                                                    📋 Salin Kode
                                                </button>
                                                <button type="button" @click="acakTokenSesi(item.sesi)"
                                                    class="flex-1 py-1.5 px-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 rounded-lg text-[11px] font-bold transition cursor-pointer">
                                                    🔄 Acak Kode
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </template>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- KISI-KISI --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Kisi-kisi <span class="font-normal text-gray-400">(opsional)</span>
                    </label>

                    <textarea name="kisi_kisi" form="form-edit-ujian" rows="4" maxlength="500"
                        {{ $onlyBobot ? 'readonly tabindex=-1' : '' }}
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 {{ $onlyBobot ? 'bg-gray-100' : 'focus:ring-2 focus:ring-teal-500 focus:border-teal-500' }}">{{ old('kisi_kisi', $ujian->kisi_kisi) }}</textarea>

                    <p class="text-xs text-gray-400 mt-1">Maksimal 500 karakter.</p>
                </div>

            </div>
        </div>

        {{-- RINGKASAN BOBOT TOTAL --}}
        <div class="p-4 rounded-xl border transition"
            :class="totalBobot > 100.01 || totalBobot <= 0 ? 'bg-red-50 border-red-200' : 'bg-teal-50 border-teal-200'">
            <div class="flex items-center justify-between">
                <span class="font-semibold text-gray-700 text-sm">Total Bobot Seluruh Soal</span>
                <span class="font-black text-lg" :class="totalBobot > 100.01 || totalBobot <= 0 ? 'text-red-600' : 'text-teal-700'"
                    x-text="totalBobot + ' / 100'"></span>
            </div>
            <p class="text-xs mt-1" :class="totalBobot > 100.01 || totalBobot <= 0 ? 'text-red-600' : 'text-gray-500'"
                x-text="totalBobot <= 0
                    ? 'Minimal harus ada 1 soal yang valid (bukan Tidak Valid)!'
                    : (totalBobot > 100.01 ? 'Total bobot melebihi 100 poin! Silakan periksa kembali bobot soal.' : 'Total bobot terdistribusi proporsional 100 poin pada soal yang valid.')">
            </p>
        </div>

        {{-- DAFTAR SOAL & EDITOR SOAL --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6" id="section-editor-soal">

            {{-- SIDEBAR DAFTAR SOAL (DENGAN INDIKATOR VALIDASI SEPERTI CREATE) --}}
            <div class="lg:col-span-4">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 lg:sticky lg:top-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="font-bold text-gray-800">Daftar Soal</h2>
                            <p class="text-xs text-gray-500 mt-0.5">
                                <span x-text="questions.length"></span> butir soal {{ $onlyBobot ? '' : '(Minimal 2 Soal)' }}
                            </p>
                        </div>

                        @if (!$onlyBobot)
                            <button type="button" @click="addQuestion()"
                                class="px-3.5 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">
                                + Tambah Soal
                            </button>
                        @endif
                    </div>

                    <div class="space-y-2 max-h-[600px] overflow-y-auto pr-1">
                        <template x-for="(question, index) in questions" :key="question.localId">
                            <button type="button" @click="switchQuestion(index)"
                                class="w-full text-left p-3 rounded-xl border transition relative group cursor-pointer"
                                :class="activeQuestion === index ?
                                    'border-teal-500 bg-teal-50/70 shadow-xs' :
                                    (Number(question.kesulitan) === 0 ?
                                        'border-rose-200 bg-rose-50/50 hover:border-rose-300' :
                                        (isQuestionComplete(question) ?
                                            'border-gray-200 hover:border-gray-300 bg-white' :
                                            'border-amber-300 bg-amber-50/70 hover:border-amber-400'))">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center font-bold flex-shrink-0 text-xs"
                                            :class="activeQuestion === index ? 'bg-teal-600 text-white' :
                                                (Number(question.kesulitan) === 0 ? 'bg-rose-100 text-rose-700' : 'bg-gray-100 text-gray-600')"
                                            x-text="index + 1"></div>

                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5">
                                                <p class="font-semibold text-xs truncate text-gray-800"
                                                    x-text="question.pertanyaan || 'Soal belum diisi'"></p>

                                                {{-- Badge Indikator Kelengkapan / Dianulir --}}
                                                <template x-if="Number(question.kesulitan) === 0">
                                                    <span class="px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 text-[9px] font-extrabold shrink-0"
                                                        title="Soal Tidak Valid / Dianulir">0 PT</span>
                                                </template>
                                                <template x-if="Number(question.kesulitan) !== 0 && !isQuestionComplete(question)">
                                                    <span class="w-4 h-4 rounded-full bg-amber-500 text-white text-[10px] font-black inline-flex items-center justify-center shrink-0"
                                                        title="Soal ini belum lengkap">!</span>
                                                </template>
                                                <template x-if="Number(question.kesulitan) !== 0 && isQuestionComplete(question)">
                                                    <span class="w-4 h-4 rounded-full bg-emerald-500 text-white text-[10px] font-black inline-flex items-center justify-center shrink-0"
                                                        title="Soal sudah lengkap">✓</span>
                                                </template>
                                            </div>
                                            <p class="text-[11px] mt-0.5"
                                                :class="Number(question.kesulitan) === 0 ? 'text-rose-500 font-semibold' : 'text-gray-400'"
                                                x-text="Number(question.kesulitan) === 0
                                                    ? (typeLabel(question.tipe_soal) + ' • Tidak Valid (0 pt)')
                                                    : (typeLabel(question.tipe_soal) + ' • ' + (question.bobot || 0) + ' pt')">
                                            </p>
                                        </div>
                                    </div>

                                    {{-- TOMBOL SILANG (X) UNTUK MENGHAPUS SOAL DARI SIDEBAR (SEMBUNYIKAN JIKA $onlyBobot) --}}
                                    @if (!$onlyBobot)
                                        <span x-show="questions.length > 2" @click.stop="removeQuestion(index)"
                                            class="w-6 h-6 flex items-center justify-center rounded-lg text-xs font-bold text-gray-400 hover:text-white hover:bg-red-500 transition shrink-0"
                                            title="Hapus soal ini">
                                            ✕
                                        </span>
                                    @endif
                                </div>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            {{-- EDITOR SOAL (MENGGUNAKAN x-for + x-show AGAR STATE & GAMBAR TIDAK TER-RESET) --}}
            <div class="lg:col-span-8 min-w-0">

                <div x-show="questions.length === 0" class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
                    <div class="text-4xl mb-3">📝</div>
                    <h3 class="font-bold text-gray-700">Belum ada soal</h3>
                    @if (!$onlyBobot)
                        <button type="button" @click="addQuestion()"
                            class="mt-4 px-4 py-2 bg-teal-600 text-white rounded-xl font-semibold">
                            Tambahkan Soal
                        </button>
                    @endif
                </div>

                <template x-for="(question, index) in questions" :key="question.localId">
                    <div x-show="activeQuestion === index"
                        class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">

                        <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                            <div>
                                <span class="text-xs text-gray-400 font-semibold uppercase tracking-wider">Editor Butir
                                    Soal</span>
                                <h2 class="text-xl font-bold text-gray-800 mt-0.5">
                                    Soal Nomor <span x-text="index + 1"></span>
                                </h2>
                            </div>

                            @if (!$onlyBobot)
                                <button type="button" @click="removeQuestion(index)" x-show="questions.length > 2"
                                    class="px-3 py-1.5 text-red-600 hover:bg-red-50 rounded-xl text-xs font-bold transition border border-red-200 cursor-pointer">
                                    Hapus Soal
                                </button>
                            @else
                                <span class="px-3 py-1 rounded-xl text-xs font-bold"
                                    :class="Number(question.kesulitan) === 0 ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-teal-50 text-teal-800 border border-teal-200'"
                                    x-text="Number(question.kesulitan) === 0 ? 'Status: Tidak Valid (0 Poin)' : ('Bobot: ' + question.bobot + ' Poin')">
                                </span>
                            @endif
                        </div>

                        {{-- TIPE SOAL (DIKUNCI JIKA $onlyBobot) --}}
                        <div class="{{ $onlyBobot ? 'opacity-65 pointer-events-none select-none' : '' }}">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Tipe Soal
                            </label>

                            <select :name="`soals[${index}][tipe_soal]`" form="form-edit-ujian"
                                x-model="question.tipe_soal" @change="changeType(index)"
                                {{ $onlyBobot ? 'disabled' : '' }}
                                class="w-full border border-gray-300 rounded-xl px-4 py-3 {{ $onlyBobot ? 'bg-gray-100' : 'focus:ring-2 focus:ring-teal-500 focus:border-teal-500' }}">
                                <option value="single_choice">Pilihan Ganda (Tunggal)</option>
                                <option value="multiple_choice">Pilihan Ganda Kompleks (Banyak Jawaban)</option>
                                <option value="essay">Essay</option>
                                <option value="matching">Menjodohkan</option>
                            </select>
                        </div>

                        {{-- TINGKAT KESULITAN & BOBOT OTOMATIS (TETAP AKTIF DI SEMUA STATUS!) --}}
                        <div
                            class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 rounded-2xl border transition"
                            :class="Number(question.kesulitan) === 0 ? 'bg-rose-50/60 border-rose-200' : 'bg-gray-50/70 border-gray-200'">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                                    Tingkat Kesulitan {{ $onlyBobot ? '(Dapat Diubah)' : '' }}
                                </label>

                                <select :name="`soals[${index}][kesulitan]`" form="form-edit-ujian"
                                    x-model.number="question.kesulitan" @change="hitungBobotOtomatis()"
                                    class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm bg-white focus:ring-2 focus:ring-teal-500 cursor-pointer font-semibold">
                                    @if ($onlyBobot)
                                        <option value="0">Tidak Valid / Dianulir (Poin: 0)</option>
                                    @endif
                                    <option value="1">Mudah (Poin: 1)</option>
                                    <option value="2">Menengah (Poin: 2)</option>
                                    <option value="3">Sulit (Poin: 3)</option>
                                </select>
                                <p class="text-[11px] mt-1"
                                    :class="Number(question.kesulitan) === 0 ? 'text-rose-600 font-semibold' : 'text-gray-400'"
                                    x-text="Number(question.kesulitan) === 0
                                        ? 'Soal ini dianulir dan tidak mempengaruhi nilai siswa.'
                                        : 'Bobot soal akan disesuaikan otomatis.'">
                                </p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                                    Bobot Soal (Otomatis)
                                </label>

                                <div class="relative">
                                    <input type="number" step="0.01" :name="`soals[${index}][bobot]`"
                                        form="form-edit-ujian" x-model="question.bobot" readonly
                                        class="w-full bg-gray-100 border border-gray-300 rounded-xl px-3.5 py-2.5 pr-14 text-sm font-bold text-gray-800 cursor-not-allowed">
                                    <span class="absolute right-4 top-2.5 text-xs font-bold text-gray-400">Poin</span>
                                </div>
                                <p class="text-[11px] text-teal-600 font-semibold mt-1">✓ Dihitung otomatis agar total pas
                                    100.</p>
                            </div>
                        </div>

                        {{-- PEMBUNGKUS KONTEN SOAL (PERTANYAAN, GAMBAR, PILIHAN, KUNCI) -> DIKUNCI JIKA $onlyBobot --}}
                        <div class="space-y-5 {{ $onlyBobot ? 'opacity-65 pointer-events-none select-none' : '' }}">

                            {{-- PERTANYAAN --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Pertanyaan <span class="text-red-500">*</span>
                                </label>

                                <textarea :name="`soals[${index}][pertanyaan]`" :id="`textarea-pertanyaan-${index}`"
                                    form="form-edit-ujian" x-model="question.pertanyaan" rows="5" maxlength="500"
                                    {{ $onlyBobot ? 'readonly tabindex=-1' : '' }}
                                    placeholder="Tuliskan pertanyaan soal..."
                                    class="w-full border border-gray-300 rounded-xl px-4 py-3 {{ $onlyBobot ? 'bg-gray-100' : 'focus:ring-2 focus:ring-teal-500 focus:border-teal-500' }}"></textarea>
                                <p class="text-xs text-gray-400 mt-1">Maksimal 500 karakter.</p>
                            </div>

                            {{-- GAMBAR SOAL (MEKANISME SAMA DENGAN CREATE + AMAN UNTUK GAMBAR LAMA) --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Gambar Soal <span class="font-normal text-gray-400">(opsional)</span>
                                </label>

                                @if (!$onlyBobot)
                                    <input type="file" :id="`input-gambar-${question.localId}`"
                                        :name="`soals[${index}][gambar]`" form="form-edit-ujian"
                                        accept=".jpg,.jpeg,.png,.webp,.gif,image/jpeg,image/png,image/webp,image/gif"
                                        @change="handleImageUpload($event, index)"
                                        class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 cursor-pointer">

                                    <p class="text-xs text-gray-400 mt-1">Format JPG, PNG, WEBP, atau GIF. Maksimal 2 MB.</p>
                                @endif

                                {{-- Pesan Error Validasi File Gambar --}}
                                <template x-if="question.error_gambar">
                                    <p class="text-xs font-semibold text-red-600 mt-1.5 flex items-center gap-1"
                                        x-text="question.error_gambar"></p>
                                </template>

                                {{-- PREVIEW GAMBAR --}}
                                <template x-if="question.preview_gambar">
                                    <div class="mt-3 relative inline-block">
                                        <div class="border border-gray-200 rounded-xl p-2 bg-gray-50 shadow-xs">
                                            <img :src="question.preview_gambar" alt="Preview Gambar Soal"
                                                class="max-h-56 max-w-full rounded-lg object-contain block bg-white">
                                        </div>

                                        <div class="mt-2 flex flex-wrap items-center gap-2">
                                            <span class="text-xs text-emerald-700 font-medium"
                                                x-text="question.nama_file_gambar"></span>

                                            @if (!$onlyBobot)
                                                <button type="button" @click="removeImage(index, question.localId)"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-xs font-semibold transition border border-red-200 cursor-pointer"
                                                    title="Batalkan / Hapus gambar">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                        stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                        </path>
                                                    </svg>
                                                    <span x-text="question.is_new_image && question.existing_gambar ? 'Batalkan Gambar Baru' : 'Hapus Gambar'"></span>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </template>

                                <input type="hidden" form="form-edit-ujian" :name="`soals[${index}][hapus_gambar]`"
                                    :value="question.hapus_gambar ? '1' : '0'">
                            </div>

                            {{-- PILIHAN GANDA --}}
                            <template
                                x-if="question.tipe_soal === 'single_choice' || question.tipe_soal === 'multiple_choice'">
                                <div>
                                    <div class="flex items-center justify-between mb-3">
                                        <label class="text-sm font-semibold text-gray-700">
                                            Pilihan Jawaban <span class="text-red-500">*</span>
                                        </label>

                                        @if (!$onlyBobot)
                                            <button type="button" @click="addOption(index)"
                                                x-show="question.pilihan.length < 6"
                                                class="text-xs font-bold text-teal-700 hover:text-teal-900 cursor-pointer">
                                                + Tambah Pilihan
                                            </button>
                                        @endif
                                    </div>

                                    <div class="space-y-3">
                                        <template x-for="(option, optionIndex) in question.pilihan"
                                            :key="'option-' + question.localId + '-' + optionIndex">
                                            <div class="flex items-center gap-3">

                                                {{-- Radio untuk single_choice --}}
                                                <template x-if="question.tipe_soal === 'single_choice'">
                                                    <input type="radio" class="w-4 h-4 text-teal-700 focus:ring-teal-500 cursor-pointer"
                                                        :name="`soals[${index}][kunci_radio]`"
                                                        :value="String.fromCharCode(97 + optionIndex)"
                                                        :checked="question.kunci_jawaban.includes(String.fromCharCode(97 + optionIndex))"
                                                        {{ $onlyBobot ? 'disabled' : '' }}
                                                        @change="setSingleAnswer(index, optionIndex)">
                                                </template>

                                                {{-- Checkbox untuk multiple_choice --}}
                                                <template x-if="question.tipe_soal === 'multiple_choice'">
                                                    <input type="checkbox"
                                                        class="w-4 h-4 text-teal-700 rounded focus:ring-teal-500 cursor-pointer"
                                                        :value="String.fromCharCode(97 + optionIndex)"
                                                        :checked="question.kunci_jawaban.includes(String.fromCharCode(97 + optionIndex))"
                                                        {{ $onlyBobot ? 'disabled' : '' }}
                                                        @change="toggleMultipleAnswer(index, optionIndex)">
                                                </template>

                                                <span
                                                    class="w-7 h-7 rounded-lg bg-gray-100 text-gray-600 font-bold text-xs flex items-center justify-center shrink-0"
                                                    x-text="String.fromCharCode(65 + optionIndex)"></span>

                                                <input type="text" form="form-edit-ujian"
                                                    :id="`input-pilihan-${index}-${optionIndex}`"
                                                    :name="`soals[${index}][pilihan][${optionIndex}]`"
                                                    x-model="question.pilihan[optionIndex]"
                                                    {{ $onlyBobot ? 'readonly tabindex=-1' : '' }}
                                                    placeholder="Tulis pilihan jawaban..."
                                                    maxlength="200"
                                                    class="flex-1 border border-gray-300 rounded-xl px-4 py-2.5 text-sm {{ $onlyBobot ? 'bg-gray-100' : 'focus:ring-2 focus:ring-teal-500 focus:border-teal-500' }}">

                                                @if (!$onlyBobot)
                                                    <button type="button" @click="removeOption(index, optionIndex)"
                                                        class="p-2 text-red-500 hover:bg-red-50 rounded-lg text-lg font-bold cursor-pointer"
                                                        title="Hapus pilihan">
                                                        ×
                                                    </button>
                                                @endif
                                            </div>
                                        </template>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-2">Centang/Pilih radio di sebelah kiri untuk menandai
                                        opsi sebagai kunci jawaban.</p>
                                </div>
                            </template>

                            {{-- ESSAY --}}
                            <template x-if="question.tipe_soal === 'essay'">
                                <div class="space-y-3">
                                    <div class="p-4 bg-blue-50 border border-blue-100 rounded-xl text-xs text-blue-700">
                                        Soal essay tidak membutuhkan pilihan ganda. Guru dapat mengisi pedoman/rubrik penilaian
                                        di bawah.
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                                            Pedoman Jawaban / Rubrik (Opsional)
                                        </label>
                                        <textarea :name="`soals[${index}][kunci_jawaban_text]`" form="form-edit-ujian"
                                            x-model="question.kunci_jawaban_text" rows="4" maxlength="1000"
                                            {{ $onlyBobot ? 'readonly tabindex=-1' : '' }}
                                            class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm {{ $onlyBobot ? 'bg-gray-100' : 'focus:ring-2 focus:ring-teal-500' }}"></textarea>
                                    </div>
                                </div>
                            </template>

                            {{-- MATCHING --}}
                            <template x-if="question.tipe_soal === 'matching'">
                                <div class="p-4 bg-purple-50 border border-purple-100 rounded-xl text-xs text-purple-700">
                                    Tipe soal menjodohkan menggunakan data pasangan pertanyaan dan jawaban.
                                </div>
                            </template>

                        </div>

                        {{-- HIDDEN INPUT UNTUK SETIAP SOAL --}}
                        <input type="hidden" form="form-edit-ujian" :name="`soals[${index}][id]`"
                            :value="question.id ?? ''">
                        <input type="hidden" form="form-edit-ujian" :name="`soals[${index}][urutan]`"
                            :value="index + 1">

                        <template x-for="(kunci, kIndex) in question.kunci_jawaban"
                            :key="'kunci-' + question.localId + '-' + kIndex">
                            <input type="hidden" form="form-edit-ujian"
                                :name="`soals[${index}][kunci_jawaban][${kIndex}]`" :value="kunci">
                        </template>

                    </div>
                </template>
            </div>

        </div>

        {{-- FORM UPDATE --}}
        <form id="form-edit-ujian" action="{{ route('guru.ujian.update', $ujian) }}" method="POST"
            enctype="multipart/form-data" novalidate @submit="validateForm($event)">

            @csrf
            @method('PUT')

            <div class="flex flex-col sm:flex-row sm:justify-end gap-3 pt-6 border-t border-gray-200">
                <a href="{{ route('guru.ujian.index') }}"
                    class="px-5 py-3 border border-gray-300 text-gray-700 rounded-xl font-semibold text-center hover:bg-gray-50 transition text-sm">
                    Batal
                </a>

                <button type="submit"
                    class="px-6 py-3 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-bold transition shadow-xs text-sm cursor-pointer">
                    @if ($onlyBobot)
                        Simpan Perubahan Bobot & Hitung Ulang Nilai
                    @elseif ($modeJadwalkan)
                        Simpan Jadwal
                    @else
                        Simpan Perubahan
                    @endif
                </button>
            </div>
        </form>

    </div>

    {{-- =============================================================
        JAVASCRIPT ALPINE.JS
    ============================================================= --}}
    <script>
        function editUjian() {
            return {
                onlyBobot: @js((bool) $onlyBobot),
                modeJadwalkan: @js((bool) $modeJadwalkan),
                initialMapel: @js((string) ($ujian->pengampuKelas?->guruMapel?->mapel_id ?? '')),

                mapelList: @js($dataMapel ?? []),
                selectedMapel: @js((string) old('mapel_id', $ujian->pengampuKelas?->guruMapel?->mapel_id ?? '')),
                selectedTingkatan: '',
                kelasDipilih: @js(array_map('strval', (array) old('pengampu_kelas_id', $grupUjians->pluck('pengampu_kelas_id')->toArray()))),

                // State Jadwal, Sesi & Kode Masuk
                waktuMulai: @js(old('waktu_mulai', $ujian->waktu_mulai?->format('Y-m-d\TH:i') ?? '')),
                durasiMenit: @js((int) old('durasi_menit', $ujian->durasi_menit ?? 60)),
                sesiKelas: @js(old('sesi_kelas', $sesiKelasAwal ?? [])),
                tokenSesi: @js(old('token_sesi', $tokenSesiAwal ?? [])),
                jamSesi: @js(old('jam_sesi', $jamSesiAwal ?? [])),

                get detailKelasDipilih() {
                    return this.kelasTerlihat.filter(k =>
                        this.kelasDipilih.includes(String(k.pengampu_kelas_id))
                    );
                },

                get ringkasanSesiAktif() {
                    const mapSesi = {};
                    this.detailKelasDipilih.forEach(k => {
                        const idStr = String(k.pengampu_kelas_id);
                        const noSesi = Number(this.sesiKelas[idStr] || 1);
                        if (!mapSesi[noSesi]) {
                            mapSesi[noSesi] = { sesi: noSesi, kelasList: [] };
                        }
                        mapSesi[noSesi].kelasList.push(k.nama_kelas);
                    });

                    return Object.values(mapSesi).sort((a, b) => a.sesi - b.sesi);
                },

                buatKodeAcak(nomorSesi) {
                    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
                    let rnd = '';
                    for (let i = 0; i < 4; i++) {
                        rnd += chars.charAt(Math.floor(Math.random() * chars.length));
                    }
                    return `S${nomorSesi}-${rnd}`;
                },

                pastikanTokenSesi(nomorSesi) {
                    const s = Number(nomorSesi || 1);
                    if (!this.tokenSesi[s]) {
                        this.tokenSesi[s] = this.buatKodeAcak(s);
                    }
                },

                acakTokenSesi(nomorSesi) {
                    if (this.onlyBobot) return;
                    this.tokenSesi[nomorSesi] = this.buatKodeAcak(nomorSesi);
                },

                salinKode(kode) {
                    navigator.clipboard.writeText(kode);
                    alert('Kode masuk ' + kode + ' berhasil disalin!');
                },

                sinkronkanSesiKelas() {
                    this.kelasDipilih.forEach(id => {
                        const key = String(id);
                        if (!this.sesiKelas[key]) {
                            this.sesiKelas[key] = 1;
                        }
                        this.pastikanTokenSesi(this.sesiKelas[key]);
                    });
                    this.sinkronkanJamSesi(false);
                },

                sinkronkanJamSesi(timpaManual = true) {
                    if (!this.waktuMulai) return;
                    const baseDate = new Date(this.waktuMulai);
                    if (isNaN(baseDate.getTime())) return;

                    const durasi = Math.max(1, Number(this.durasiMenit) || 60);

                    this.ringkasanSesiAktif.forEach(item => {
                        this.pastikanTokenSesi(item.sesi);
                        if (timpaManual || !this.jamSesi[item.sesi]) {
                            const d = new Date(baseDate.getTime() + (item.sesi - 1) * durasi * 60000);
                            const hh = String(d.getHours()).padStart(2, '0');
                            const mm = String(d.getMinutes()).padStart(2, '0');
                            this.jamSesi[item.sesi] = `${hh}:${mm}`;
                        }
                    });
                },

                activeQuestion: 0,
                questions: @js($soalData ?? []),

                get mapelAktif() {
                    return this.mapelList.find(m => String(m.id) === String(this.selectedMapel)) || null;
                },

                get daftarTingkatan() {
                    return this.mapelAktif?.tingkatan || [];
                },

                get tingkatanAktif() {
                    return this.daftarTingkatan.find(t => t.nama === this.selectedTingkatan) || null;
                },

                get kelasTerlihat() {
                    return this.tingkatanAktif?.kelas || [];
                },

                ubahMapel() {
                    if (this.onlyBobot) return;
                    this.selectedTingkatan = '';
                    this.kelasDipilih = [];
                },

                ubahTingkatan() {
                    if (this.onlyBobot) return;
                    this.kelasDipilih = [];
                },

                init() {
                    // 1. Inisialisasi format soal, gambar & kesulitan (mendukung nilai 0 = Tidak Valid saat onlyBobot)
                    this.questions = this.questions.map((q, idx) => {
                        const rawKesulitan = (q.kesulitan !== undefined && q.kesulitan !== null && q.kesulitan !== '')
                            ? Number(q.kesulitan)
                            : (Number(q.bobot) <= 0 ? 0 : 1);

                        return {
                            ...q,
                            localId: q.localId ?? 'existing-' + (q.id || idx),
                            pilihan: Array.isArray(q.pilihan) ? q.pilihan : [],
                            kunci_jawaban: Array.isArray(q.kunci_jawaban) ?
                                q.kunci_jawaban.map(v => {
                                    const ans = String(v).toLowerCase();
                                    return /^\d+$/.test(ans) ? String.fromCharCode(97 + Number(ans)) : ans;
                                }) : [],
                            kunci_jawaban_text: q.kunci_jawaban_text ?? '',
                            existing_gambar: q.gambar || null,
                            preview_gambar: q.gambar || null,
                            nama_file_gambar: q.gambar ? 'Gambar tersimpan' : '',
                            error_gambar: '',
                            is_new_image: false,
                            hapus_gambar: false,
                            kesulitan: this.onlyBobot ? rawKesulitan : Math.max(1, rawKesulitan),
                            bobot: q.bobot !== undefined && q.bobot !== null ? Number(q.bobot) : 1,
                        };
                    });

                    if (this.questions.length === 0 && !this.onlyBobot) {
                        this.addQuestion();
                        this.addQuestion();
                    } else {
                        this.hitungBobotOtomatis();
                    }

                    // 2. Pulihkan selectedTingkatan berdasarkan kelasDipilih awal
                    if (this.selectedMapel && this.kelasDipilih.length > 0) {
                        const mapel = this.mapelAktif;
                        if (mapel && Array.isArray(mapel.tingkatan)) {
                            const tingkat = mapel.tingkatan.find(t =>
                                t.kelas.some(k => this.kelasDipilih.includes(String(k.pengampu_kelas_id)))
                            );
                            if (tingkat) {
                                this.selectedTingkatan = tingkat.nama;
                            }
                        }
                    }

                    // 3. Sinkronisasi sesi, token & jam sesi awal
                    this.sinkronkanSesiKelas();
                },

                // =========================================================
                // CEK KELENGKAPAN & STATUS TERISI SOAL
                // =========================================================
                isQuestionComplete(q) {
                    if (!q.pertanyaan || q.pertanyaan.trim() === '') return false;

                    if (q.tipe_soal === 'single_choice' || q.tipe_soal === 'multiple_choice') {
                        if (!Array.isArray(q.pilihan) || q.pilihan.length < 2) return false;
                        for (let p = 0; p < q.pilihan.length; p++) {
                            if (!q.pilihan[p] || String(q.pilihan[p]).trim() === '') return false;
                        }
                        if (!Array.isArray(q.kunci_jawaban) || q.kunci_jawaban.length === 0) return false;
                    }

                    return true;
                },

                isQuestionFilled(q) {
                    if (q.pertanyaan && q.pertanyaan.trim() !== '') return true;
                    if (q.preview_gambar) return true;
                    if (q.kunci_jawaban_text && q.kunci_jawaban_text.trim() !== '') return true;

                    if (q.tipe_soal === 'single_choice' || q.tipe_soal === 'multiple_choice') {
                        if (Array.isArray(q.pilihan) && q.pilihan.some(p => String(p || '').trim() !== '')) {
                            return true;
                        }
                        if (Array.isArray(q.kunci_jawaban) && q.kunci_jawaban.length > 0) {
                            return true;
                        }
                    }

                    return false;
                },

                // =========================================================
                // VALIDASI LENGKAP CLIENT-SIDE (SELARAS DENGAN CREATE)
                // =========================================================
                validateForm(event) {
                    // Pastikan minimal ada 1 soal yang valid (kesulitan > 0)
                    const adaSoalValid = this.questions.some(q => Number(q.kesulitan) > 0 && Number(q.bobot) > 0);
                    if (!adaSoalValid || this.totalBobot <= 0) {
                        event.preventDefault();
                        alert('Minimal harus ada 1 soal yang valid (bukan berstatus "Tidak Valid")!');
                        return;
                    }

                    if (this.totalBobot > 100.5) {
                        event.preventDefault();
                        alert(`Total bobot soal ${this.totalBobot} poin melebihi batas maksimal 100 poin!`);
                        return;
                    }

                    // Jika sedang dalam Mode Penyesuaian Bobot (unpublished / completed),
                    // langsung izinkan submit tanpa memvalidasi ulang input yang dikunci.
                    if (this.onlyBobot) {
                        return;
                    }

                    // 1. Validasi Judul Ujian
                    const inputJudul = this.$refs.inputJudul;
                    if (!inputJudul || !inputJudul.value.trim()) {
                        event.preventDefault();
                        alert('Judul Ujian wajib diisi terlebih dahulu!');
                        inputJudul?.focus();
                        return;
                    }

                    // 2. Validasi Target Kelas
                    if (this.kelasDipilih.length === 0) {
                        event.preventDefault();
                        alert('Target Kelas belum dipilih!\nSilakan pilih Mata Pelajaran, Tingkatan, dan centang minimal 1 Kelas terlebih dahulu.');
                        document.getElementById('section-target-kelas')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        return;
                    }

                    // 3. Validasi Durasi & KKM
                    const durasiVal = Number(this.$refs.inputDurasi?.value || 0);
                    if (!durasiVal || durasiVal < 1 || durasiVal > 999) {
                        event.preventDefault();
                        alert('Durasi Ujian harus diisi antara 1 sampai 999 menit!');
                        this.$refs.inputDurasi?.focus();
                        return;
                    }

                    const kkmRaw = this.$refs.inputKkm?.value;
                    if (kkmRaw === '' || Number(kkmRaw) < 0 || Number(kkmRaw) > 100) {
                        event.preventDefault();
                        alert('Nilai KKM harus diisi antara 0 sampai 100!');
                        this.$refs.inputKkm?.focus();
                        return;
                    }

                    // 4. Validasi Jadwal
                    const waktuMulaiVal = this.$refs.inputWaktuMulai?.value || '';
                    const deadlineVal = this.$refs.inputDeadline?.value || '';

                    if (this.modeJadwalkan) {
                        if (!waktuMulaiVal) {
                            event.preventDefault();
                            alert('Untuk menjadwalkan ujian, kolom "Waktu Mulai" wajib diisi!');
                            this.$refs.inputWaktuMulai?.focus();
                            return;
                        }
                        if (!deadlineVal) {
                            event.preventDefault();
                            alert('Untuk menjadwalkan ujian, kolom "Deadline" wajib diisi!');
                            this.$refs.inputDeadline?.focus();
                            return;
                        }
                    }

                    if (waktuMulaiVal && deadlineVal) {
                        if (new Date(deadlineVal) <= new Date(waktuMulaiVal)) {
                            event.preventDefault();
                            alert('Waktu Deadline harus lebih akhir daripada Waktu Mulai!');
                            this.$refs.inputDeadline?.focus();
                            return;
                        }
                    }

                    // 5. Validasi Minimal 2 Soal
                    if (this.questions.length < 2) {
                        event.preventDefault();
                        alert(`Ujian minimal harus memiliki 2 soal!\nSaat ini baru terdapat ${this.questions.length} soal. Silakan klik tombol "+ Tambah Soal".`);
                        document.getElementById('section-editor-soal')?.scrollIntoView({ behavior: 'smooth' });
                        return;
                    }

                    // 6. Validasi Kelengkapan Setiap Butir Soal (Otomatis pindah ke soal yang belum lengkap)
                    for (let i = 0; i < this.questions.length; i++) {
                        const q = this.questions[i];
                        const nomor = i + 1;

                        if (!q.pertanyaan || q.pertanyaan.trim() === '') {
                            event.preventDefault();
                            this.activeQuestion = i;
                            alert(`Pertanyaan pada Soal nomor ${nomor} belum diisi!\nLayar telah diarahkan ke Soal nomor ${nomor}.`);
                            this.$nextTick(() => {
                                document.getElementById(`textarea-pertanyaan-${i}`)?.focus();
                            });
                            return;
                        }

                        if (q.tipe_soal === 'single_choice' || q.tipe_soal === 'multiple_choice') {
                            if (!Array.isArray(q.pilihan) || q.pilihan.length < 2) {
                                event.preventDefault();
                                this.activeQuestion = i;
                                alert(`Soal nomor ${nomor} minimal harus memiliki 2 pilihan jawaban!`);
                                return;
                            }

                            for (let p = 0; p < q.pilihan.length; p++) {
                                if (!q.pilihan[p] || String(q.pilihan[p]).trim() === '') {
                                    event.preventDefault();
                                    this.activeQuestion = i;
                                    const huruf = String.fromCharCode(65 + p);
                                    alert(`Pilihan ${huruf} pada Soal nomor ${nomor} belum diisi!\nLayar telah diarahkan ke Soal nomor ${nomor}.`);
                                    this.$nextTick(() => {
                                        document.getElementById(`input-pilihan-${i}-${p}`)?.focus();
                                    });
                                    return;
                                }
                            }

                            if (!Array.isArray(q.kunci_jawaban) || q.kunci_jawaban.length === 0) {
                                event.preventDefault();
                                this.activeQuestion = i;
                                alert(`Kunci jawaban pada Soal nomor ${nomor} belum dipilih!\nSilakan centang/pilih jawaban yang benar pada Soal nomor ${nomor}.`);
                                return;
                            }
                        }
                    }
                },

                // =========================================================
                // HITUNG BOBOT OTOMATIS BERDASARKAN TINGKAT KESULITAN (TOTAL 100)
                // Mendukung kesulitan = 0 (Tidak Valid -> Bobot 0)
                // =========================================================
                hitungBobotOtomatis() {
                    const totalSoal = this.questions.length;
                    if (totalSoal === 0) return;

                    const totalPoinKesulitan = this.questions.reduce((sum, q) => {
                        const k = (q.kesulitan !== undefined && q.kesulitan !== null && q.kesulitan !== '')
                            ? Number(q.kesulitan)
                            : 1;
                        return sum + Math.max(0, k);
                    }, 0);

                    if (totalPoinKesulitan <= 0) {
                        this.questions.forEach(q => {
                            q.bobot = 0;
                        });
                        return;
                    }

                    // Cari indeks soal VALID terakhir (kesulitan > 0) untuk menampung sisa pembulatan 100.00
                    let lastValidIndex = -1;
                    this.questions.forEach((q, idx) => {
                        const k = (q.kesulitan !== undefined && q.kesulitan !== null && q.kesulitan !== '')
                            ? Number(q.kesulitan)
                            : 1;
                        if (k > 0) lastValidIndex = idx;
                    });

                    let totalBobotTerbagi = 0;

                    this.questions.forEach((question, index) => {
                        const kesulitan = (question.kesulitan !== undefined && question.kesulitan !== null && question.kesulitan !== '')
                            ? Number(question.kesulitan)
                            : 1;

                        if (kesulitan <= 0) {
                            question.bobot = 0;
                        } else if (index === lastValidIndex) {
                            question.bobot = Number((100 - totalBobotTerbagi).toFixed(2));
                        } else {
                            const porsi = Number(((kesulitan / totalPoinKesulitan) * 100).toFixed(2));
                            question.bobot = porsi;
                            totalBobotTerbagi += porsi;
                        }
                    });
                },

                get totalBobot() {
                    return Number(this.questions.reduce((total, q) => {
                        return total + (parseFloat(q.bobot) || 0);
                    }, 0).toFixed(2));
                },

                typeLabel(type) {
                    return {
                        single_choice: 'Pilihan Ganda',
                        multiple_choice: 'Pilihan Ganda Kompleks',
                        essay: 'Essay',
                        matching: 'Menjodohkan',
                    }[type] ?? type;
                },

                addQuestion() {
                    if (this.onlyBobot) return;

                    if (this.questions.length >= 100) {
                        alert('Maksimal 100 soal dalam satu ujian.');
                        return;
                    }

                    this.questions.push({
                        id: null,
                        localId: 'new-' + Date.now() + Math.random(),
                        pertanyaan: '',
                        tipe_soal: 'single_choice',
                        pilihan: ['', '', '', ''],
                        kunci_jawaban: [],
                        kunci_jawaban_text: '',
                        existing_gambar: null,
                        preview_gambar: null,
                        nama_file_gambar: '',
                        error_gambar: '',
                        is_new_image: false,
                        hapus_gambar: false,
                        kesulitan: 1,
                        bobot: 0,
                    });

                    this.activeQuestion = this.questions.length - 1;
                    this.hitungBobotOtomatis();
                },

                removeQuestion(index) {
                    if (this.onlyBobot) return;

                    if (this.questions.length <= 2) {
                        alert('Minimal harus ada 2 soal dalam satu ujian.');
                        return;
                    }

                    const targetQuestion = this.questions[index];
                    if (this.isQuestionFilled(targetQuestion)) {
                        if (!confirm(`Soal nomor ${index + 1} sudah memiliki isian. Apakah Anda yakin ingin menghapus soal ini?`)) {
                            return;
                        }
                    }

                    if (targetQuestion?.is_new_image && targetQuestion?.preview_gambar) {
                        URL.revokeObjectURL(targetQuestion.preview_gambar);
                    }

                    this.questions.splice(index, 1);

                    if (this.activeQuestion >= this.questions.length) {
                        this.activeQuestion = this.questions.length - 1;
                    }

                    this.hitungBobotOtomatis();
                },

                addOption(qIndex) {
                    if (this.onlyBobot) return;

                    if (this.questions[qIndex].pilihan.length >= 6) {
                        alert('Maksimal 6 pilihan jawaban (A sampai F).');
                        return;
                    }
                    this.questions[qIndex].pilihan.push('');
                },

                removeOption(qIndex, optIndex) {
                    if (this.onlyBobot) return;

                    const q = this.questions[qIndex];
                    if (q.pilihan.length <= 2) {
                        alert('Minimal harus ada 2 pilihan jawaban.');
                        return;
                    }
                    const removedLetter = String.fromCharCode(97 + optIndex);
                    q.pilihan.splice(optIndex, 1);

                    q.kunci_jawaban = q.kunci_jawaban
                        .filter(k => k !== removedLetter)
                        .map(k => {
                            const code = k.charCodeAt(0) - 97;
                            return code > optIndex ? String.fromCharCode(97 + (code - 1)) : k;
                        });
                },

                setSingleAnswer(qIndex, optIndex) {
                    if (this.onlyBobot) return;
                    const letter = String.fromCharCode(97 + optIndex);
                    this.questions[qIndex].kunci_jawaban = [letter];
                },

                toggleMultipleAnswer(qIndex, optIndex) {
                    if (this.onlyBobot) return;
                    const letter = String.fromCharCode(97 + optIndex);
                    const answers = this.questions[qIndex].kunci_jawaban;
                    const idx = answers.indexOf(letter);

                    if (idx > -1) {
                        answers.splice(idx, 1);
                    } else {
                        answers.push(letter);
                    }
                },

                changeType(index) {
                    if (this.onlyBobot) return;
                    const q = this.questions[index];
                    if (q.tipe_soal === 'single_choice' || q.tipe_soal === 'multiple_choice') {
                        if (!Array.isArray(q.pilihan) || q.pilihan.length === 0) {
                            q.pilihan = ['', '', '', ''];
                        }
                        q.kunci_jawaban = [];
                    } else {
                        q.kunci_jawaban = [];
                    }
                },

                switchQuestion(index) {
                    this.activeQuestion = index;
                },

                // =========================================================
                // VALIDASI & PREVIEW GAMBAR (SELARAS DENGAN CREATE)
                // =========================================================
                handleImageUpload(event, index) {
                    if (this.onlyBobot) return;

                    const file = event.target.files[0];
                    const q = this.questions[index];
                    q.error_gambar = '';

                    if (!file) {
                        if (!q.is_new_image) {
                            q.preview_gambar = q.existing_gambar;
                            q.nama_file_gambar = q.existing_gambar ? 'Gambar tersimpan' : '';
                        }
                        return;
                    }

                    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                    const allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                    const fileExtension = (file.name.split('.').pop() || '').toLowerCase();

                    if (!allowedTypes.includes(file.type) || !allowedExtensions.includes(fileExtension)) {
                        const msg = 'Format file tidak didukung! Hanya diperbolehkan JPG, PNG, WEBP, atau GIF.';
                        q.error_gambar = msg;
                        alert(msg);
                        event.target.value = '';
                        return;
                    }

                    const maxSizeBytes = 2 * 1024 * 1024; // 2 MB
                    if (file.size > maxSizeBytes) {
                        const ukuranMb = (file.size / (1024 * 1024)).toFixed(2);
                        const msg = `Ukuran gambar terlalu besar (${ukuranMb} MB)! Maksimal ukuran yang diperbolehkan adalah 2 MB.`;
                        q.error_gambar = msg;
                        alert(msg);
                        event.target.value = '';
                        return;
                    }

                    if (q.is_new_image && q.preview_gambar) {
                        URL.revokeObjectURL(q.preview_gambar);
                    }

                    q.preview_gambar = URL.createObjectURL(file);
                    q.nama_file_gambar = `${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
                    q.is_new_image = true;
                    q.hapus_gambar = false;
                },

                removeImage(index, localId) {
                    if (this.onlyBobot) return;

                    const q = this.questions[index];

                    if (q.is_new_image && q.preview_gambar) {
                        URL.revokeObjectURL(q.preview_gambar);
                    }

                    const input = document.getElementById('input-gambar-' + localId);
                    if (input) {
                        input.value = '';
                    }

                    q.error_gambar = '';

                    if (q.is_new_image && q.existing_gambar) {
                        q.preview_gambar = q.existing_gambar;
                        q.nama_file_gambar = 'Gambar tersimpan';
                        q.is_new_image = false;
                        q.hapus_gambar = false;
                        return;
                    }

                    q.preview_gambar = null;
                    q.nama_file_gambar = '';
                    q.is_new_image = false;
                    if (q.existing_gambar) {
                        q.hapus_gambar = true;
                    }
                }
            };
        }
    </script>

@endsection
