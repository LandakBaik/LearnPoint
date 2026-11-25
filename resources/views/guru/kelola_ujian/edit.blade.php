@extends('layouts.app')

@section('title', 'Edit Ujian')

@section('content')
    @php
        $onlyBobot = $modeEditBobot ?? in_array($ujian->status, ['unpublished', 'completed'], true);
    @endphp

    <div x-data="editUjian()" class="max-w-7xl mx-auto space-y-6">

        {{-- ========================================================= --}}
        {{-- NOTIFIKASI VALIDASI SERVER (Khusus $errors dari withErrors) --}}
        {{-- ========================================================= --}}
        @if ($errors->any())
            <div x-data="{ tampil: true }" x-show="tampil" x-transition
                class="flex items-start justify-between gap-3 px-5 py-3.5 rounded-2xl bg-rose-50 border border-rose-200/80 text-rose-800 text-xs sm:text-sm shadow-xs">
                <div class="flex items-start gap-2.5">
                    <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div class="space-y-1">
                        @if ($errors->count() === 1)
                            <p class="font-medium leading-relaxed">{{ $errors->first() }}</p>
                        @else
                            <p class="font-semibold">Terdapat beberapa kesalahan validasi:</p>
                            <ul class="list-disc list-inside text-xs space-y-0.5 text-rose-700">
                                @foreach ($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
                <button type="button" @click="tampil = false"
                    class="text-rose-500 hover:text-rose-700 p-0.5 rounded-lg transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        {{-- ========================================================= --}}
        {{-- HEADER --}}
        {{-- ========================================================= --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

            <div>
                <a href="{{ route('guru.ujian.index') }}"
                    class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-blue-600 transition mb-2">
                    ← Kembali ke Ujian
                </a>

                <h1 class="text-2xl font-bold text-gray-800">
                    @if ($onlyBobot)
                        Penyesuaian Bobot Soal Ujian
                    @elseif ($modeJadwalkan)
                        Jadwalkan Ujian
                    @else
                        Halaman Edit Ujian
                    @endif
                </h1>

                <p class="text-sm text-gray-500 mt-1">
                    {{ $ujian->judul }}
                </p>
            </div>

        </div>

        {{-- BANNER LENGKAPI JADWAL --}}
        @if ($modeJadwalkan && !$onlyBobot)
            <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl">
                <div class="flex items-start gap-3">
                    <span class="text-xl">📅</span>
                    <div>
                        <p class="text-sm font-bold text-amber-800">Lengkapi jadwal ujian</p>
                        <p class="text-xs text-amber-700 mt-1 leading-relaxed">
                            Isi Waktu Mulai dan Deadline untuk menjadwalkan ujian.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- BANNER MODE PENYESUAIAN BOBOT SOAL (MUNCUL SAAT UNPUBLISHED / COMPLETED) --}}
        @if ($onlyBobot)
            <div class="p-4 sm:p-5 rounded-2xl bg-amber-50 border border-amber-200 flex items-start gap-3.5 shadow-2xs">

                <div class="text-xs sm:text-sm text-amber-900 space-y-1">
                    <p class="font-bold">
                        Mode Penyesuaian Bobot Soal Aktif (Status: {{ $ujian->status_label ?? ucfirst($ujian->status) }})
                    </p>
                    <p class="text-amber-800 leading-relaxed">
                        Untuk menjaga integritas dan mencegah hilangnya riwayat pengerjaan siswa pada sesi sebelumnya,
                        seluruh pengaturan ujian, teks pertanyaan, gambar, pilihan ganda, serta kunci jawaban
                        <strong>dikunci</strong>. Anda hanya dapat mengubah <strong>Tingkat Kesulitan / Bobot Soal</strong>
                        (termasuk memilih <em>"Tidak Valid (Poin: 0)"</em> untuk menganulir soal).
                    </p>
                </div>
            </div>
        @endif


        {{-- ========================================================= --}}
        {{-- CARD UTAMA: INFORMASI UJIAN + EDITOR SOAL (SAMA SEPERTI CREATE) --}}
        {{-- ========================================================= --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">

            <div class="flex items-start justify-between gap-3 mb-6">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">
                        Informasi Ujian
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Perbarui informasi dasar, target kelas, jadwal, dan sesi pelaksanaan ujian.
                    </p>
                </div>

                @if ($onlyBobot)
                    <span
                        class="text-xs font-bold px-3 py-1 rounded-lg bg-slate-100 text-slate-600 border border-slate-200 shrink-0">
                        🔒 Terkunci
                    </span>
                @endif
            </div>

            {{-- novalidate mencegah browser memblokir submit diam-diam pada tab soal yang tersembunyi --}}
            <form method="POST" action="{{ route('guru.ujian.update', $ujian) }}" enctype="multipart/form-data"
                id="form-edit-ujian" novalidate @submit="validateForm($event)">
                @csrf
                @method('PUT')


                {{-- ================================================= --}}
                {{-- DATA DASAR UJIAN (DIKUNCI OTOMATIS JIKA $onlyBobot) --}}
                {{-- ================================================= --}}
                <div
                    class="grid grid-cols-1 md:grid-cols-2 gap-5 {{ $onlyBobot ? 'opacity-65 pointer-events-none select-none' : '' }}">

                    {{-- JUDUL --}}
                    <div class="md:col-span-2">

                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Judul Ujian
                            <span class="text-red-500">*</span>
                        </label>

                        <input type="text" name="judul" x-ref="inputJudul"
                            value="{{ old('judul', $ujian->judul) }}" maxlength="100"
                            {{ $onlyBobot ? 'readonly tabindex=-1' : '' }}
                            placeholder="Contoh: Ujian Tengah Semester IPA Kelas VIII"
                            oninput="this.value = this.value.replace(/[^\p{L}\p{N} ]/gu, '')"
                            class="w-full border border-gray-300 rounded-xl px-4 py-3 outline-none transition
                                 {{ $onlyBobot ? 'bg-gray-100 cursor-not-allowed' : 'focus:ring-2 focus:ring-blue-500/25 focus:border-blue-500' }}">

                        <p class="text-xs text-gray-400 mt-1">
                            Maksimal 100 karakter (huruf, angka, dan spasi).
                        </p>

                        @error('judul')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror

                    </div>


                    {{-- MAPEL + TINGKATAN + KELAS --}}
                    <div class="md:col-span-2" id="section-target-kelas">

                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Mata Pelajaran &amp; Target Kelas
                            <span class="text-red-500">*</span>
                        </label>

                        <div class="border border-gray-300 rounded-xl p-4 space-y-5">

                            {{-- PILIH MAPEL --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    1. Mata Pelajaran
                                </label>

                                <select name="mapel_id" x-model="selectedMapel" @change="ubahMapel()"
                                    {{ $onlyBobot ? 'disabled' : '' }}
                                    class="w-full border border-gray-300 rounded-xl px-4 py-3 outline-none transition disabled:bg-gray-100
                                           focus:ring-2 focus:ring-blue-500/25 focus:border-blue-500">

                                    <option value="">-- Pilih Mata Pelajaran --</option>

                                    <template x-for="mapel in mapelList" :key="mapel.id">
                                        <option :value="String(mapel.id)" x-text="mapel.nama">
                                        </option>
                                    </template>

                                </select>

                                {{-- INFO PERUBAHAN MAPEL --}}
                                <div x-show="selectedMapel && initialMapel && String(selectedMapel) !== String(initialMapel)"
                                    class="mt-2.5 p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 leading-relaxed">
                                    <strong>Perhatian:</strong> Anda mengubah mata pelajaran ujian. Setelah disimpan, ujian
                                    akan dipindahkan ke mata pelajaran &amp; kelas baru.
                                </div>
                            </div>

                            {{-- PILIH TINGKATAN --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    2. Tingkatan Kelas
                                </label>

                                <select x-model="selectedTingkatan" @change="ubahTingkatan()"
                                    :disabled="onlyBobot || !selectedMapel || daftarTingkatan.length === 0"
                                    class="w-full border border-gray-300 rounded-xl px-4 py-3 outline-none transition
                                           disabled:bg-gray-100 disabled:text-gray-400
                                           focus:ring-2 focus:ring-blue-500/25 focus:border-blue-500">

                                    <option value="">-- Pilih Tingkatan --</option>

                                    <template x-for="tingkat in daftarTingkatan" :key="tingkat.nama">
                                        <option :value="tingkat.nama" x-text="tingkat.nama">
                                        </option>
                                    </template>

                                </select>

                                <p x-show="selectedMapel && daftarTingkatan.length === 0"
                                    class="text-xs text-amber-600 mt-2">
                                    Belum ada tingkatan kelas untuk mata pelajaran ini.
                                </p>
                            </div>

                            {{-- PILIH KELAS --}}
                            <div>
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">

                                    <label class="block text-sm font-medium text-gray-700">
                                        3. Pilih Kelas
                                    </label>

                                    <span x-show="kelasTerlihat.length > 0" class="text-xs font-semibold text-blue-600"
                                        x-text="kelasDipilih.length + ' kelas dipilih'">
                                    </span>

                                </div>

                                {{-- BELUM PILIH MAPEL --}}
                                <div x-show="!selectedMapel"
                                    class="p-4 bg-gray-50 border border-dashed border-gray-300 rounded-xl text-center">

                                    <p class="text-sm text-gray-500">
                                        Pilih mata pelajaran terlebih dahulu.
                                    </p>

                                </div>

                                {{-- BELUM PILIH TINGKATAN --}}
                                <div x-show="selectedMapel && !selectedTingkatan"
                                    class="p-4 bg-gray-50 border border-dashed border-gray-300 rounded-xl text-center">

                                    <p class="text-sm text-gray-500">
                                        Pilih tingkatan untuk menampilkan daftar kelas.
                                    </p>

                                </div>

                                {{-- DAFTAR KELAS --}}
                                <div x-show="selectedMapel && selectedTingkatan"
                                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">

                                    <template x-for="kelas in kelasTerlihat" :key="kelas.pengampu_kelas_id">

                                        <label
                                            class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg
                                                   hover:bg-blue-50/70 hover:border-blue-300 cursor-pointer transition"
                                            :class="kelasDipilih.includes(String(kelas.pengampu_kelas_id)) ?
                                                'border-blue-500 bg-blue-50' :
                                                'bg-white'">

                                            <input type="checkbox" name="pengampu_kelas_id[]"
                                                :value="String(kelas.pengampu_kelas_id)" x-model="kelasDipilih"
                                                @change="sinkronkanSesiKelas()" {{ $onlyBobot ? 'disabled' : '' }}
                                                class="w-4 h-4 text-blue-600 accent-blue-600 rounded focus:ring-blue-500">

                                            <span class="text-sm text-gray-700" x-text="kelas.nama_kelas">
                                            </span>

                                        </label>

                                    </template>

                                    <div x-show="kelasTerlihat.length === 0"
                                        class="sm:col-span-2 lg:col-span-3 p-4 text-center
                                               bg-gray-50 rounded-xl border border-dashed border-gray-300">

                                        <p class="text-sm text-gray-500">
                                            Tidak ada kelas pada tingkatan ini.
                                        </p>

                                    </div>

                                </div>

                            </div>

                            {{-- RINGKASAN KELAS --}}
                            <div x-show="kelasDipilih.length > 0" class="p-4 bg-blue-50 border border-blue-100 rounded-xl">

                                <p class="text-sm font-semibold text-blue-800">
                                    Target ujian
                                </p>

                                <p class="text-sm text-blue-700 mt-1"
                                    x-text="kelasDipilih.length + ' kelas telah dipilih.'">
                                </p>

                            </div>

                        </div>

                        <p class="text-xs text-gray-400 mt-2">
                            Pilih mapel, tingkatan, lalu satu atau beberapa kelas.
                            Soal akan digunakan untuk seluruh kelas yang dipilih.
                        </p>

                        @error('mapel_id')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror

                        @error('pengampu_kelas_id')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror

                        @error('pengampu_kelas_id.*')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror

                    </div>


                    {{-- DURASI --}}
                    <div>

                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Durasi Ujian
                            <span class="text-red-500">*</span>
                        </label>

                        <div class="relative">

                            <input type="text" name="durasi_menit" x-ref="inputDurasi"
                                x-model="durasiMenit" @input="sinkronkanJamSesi()"
                                inputmode="numeric" maxlength="3"
                                {{ $onlyBobot ? 'readonly tabindex=-1' : '' }}
                                oninput="
                                    this.value = this.value.replace(/[^0-9]/g, '').slice(0, 3);
                                    if (this.value !== '' && Number(this.value) > 999) {
                                        this.value = '999';
                                    }
                                "
                                class="w-full border border-gray-300 rounded-xl px-4 py-3 pr-20 outline-none transition
                                 {{ $onlyBobot ? 'bg-gray-100 cursor-not-allowed' : 'focus:ring-2 focus:ring-blue-500/25 focus:border-blue-500' }}">
                            <span class="absolute right-4 top-3 text-sm text-gray-400">
                                menit
                            </span>

                        </div>

                        @error('durasi_menit')
                            <p class="text-sm text-red-600 mt-1">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- KKM --}}
                    <div>

                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            KKM
                            <span class="text-red-500">*</span>
                        </label>

                        <input type="text" name="kkm" x-ref="inputKkm"
                            value="{{ old('kkm', (int) ($ujian->kkm ?? 75)) }}"
                            inputmode="numeric" maxlength="3"
                            {{ $onlyBobot ? 'readonly tabindex=-1' : '' }}
                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 3); if (Number(this.value) > 100) this.value = '100';"
                            class="w-full border border-gray-300 rounded-xl px-4 py-3 outline-none transition
                         {{ $onlyBobot ? 'bg-gray-100 cursor-not-allowed' : 'focus:ring-2 focus:ring-blue-500/25 focus:border-blue-500' }}">

                        <p class="text-xs text-gray-400 mt-1">
                            Nilai KKM 0–100.
                        </p>

                        @error('kkm')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror

                    </div>

                    {{-- Pengaturan Visibilitas Nilai Siswa --}}
                    <div
                        class="md:col-span-2 p-4 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between gap-4">
                        <div>
                            <label for="tampilkan_nilai" class="text-sm font-bold text-gray-900 cursor-pointer block">
                                Tampilkan Nilai ke Siswa Setelah Ujian
                            </label>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Jika dinonaktifkan, siswa hanya melihat konfirmasi bahwa ujian telah terkirim tanpa
                                mengetahui skor nilainya.
                            </p>
                        </div>

                        <label class="relative inline-flex items-center cursor-pointer shrink-0">
                            <input type="hidden" name="tampilkan_nilai" value="0">
                            <input type="checkbox" id="tampilkan_nilai" name="tampilkan_nilai" value="1"
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
                            Waktu Mulai
                            <span class="text-gray-400 font-normal">
                                ({{ $modeJadwalkan ? 'wajib diisi' : 'opsional untuk draft' }})
                            </span>
                        </label>

                        <input type="datetime-local" name="waktu_mulai" x-ref="inputWaktuMulai"
                            x-model="waktuMulai" @change="sinkronkanSesiKelas(); sinkronkanJamSesi();"
                            {{ $onlyBobot ? 'readonly tabindex=-1' : '' }}
                            class="w-full border border-gray-300 rounded-xl px-4 py-3 outline-none transition
                               {{ $onlyBobot ? 'bg-gray-100 cursor-not-allowed' : 'focus:ring-2 focus:ring-blue-500/25 focus:border-blue-500' }}">
                        @error('waktu_mulai')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror

                    </div>


                    {{-- DEADLINE --}}
                    <div>

                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Deadline
                            <span class="text-gray-400 font-normal">
                                ({{ $modeJadwalkan ? 'wajib diisi' : 'opsional untuk draft' }})
                            </span>
                        </label>

                        <input type="datetime-local" name="deadline" x-ref="inputDeadline"
                            value="{{ old('deadline', $ujian->deadline?->format('Y-m-d\TH:i')) }}"
                            {{ $onlyBobot ? 'readonly tabindex=-1' : '' }}
                            class="w-full border border-gray-300 rounded-xl px-4 py-3 outline-none transition
                               {{ $onlyBobot ? 'bg-gray-100 cursor-not-allowed' : 'focus:ring-2 focus:ring-blue-500/25 focus:border-blue-500' }}">
                        @error('deadline')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror

                    </div>

                    {{-- ================================================= --}}
                    {{-- PEMBAGIAN SESI & KODE MASUK (TOKEN) --}}
                    {{-- ================================================= --}}
                    <div class="md:col-span-2">
                        {{-- Petunjuk jika jadwal atau kelas belum diisi --}}
                        <div x-show="kelasDipilih.length === 0 || !waktuMulai"
                            class="p-4 rounded-2xl bg-slate-50 border border-dashed border-slate-300 text-xs text-slate-500 flex items-center gap-2.5">
                            <span class="text-base">🔑</span>
                            <span>
                                Pilih minimal 1 kelas target dan isi <strong>Waktu Mulai</strong> di atas untuk mengatur
                                pembagian <strong>Sesi Ujian</strong> dan menampilkan <strong>Kode Masuk (Token)</strong>
                                masing-masing sesi.
                            </span>
                        </div>

                        {{-- Panel Sesi & Kode Masuk Aktif --}}
                        <div x-show="kelasDipilih.length > 0 && waktuMulai" x-cloak
                            class="rounded-2xl border border-blue-200 bg-blue-50/40 p-5 space-y-5">

                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-blue-100">
                                <div>
                                    <h3 class="text-sm font-extrabold text-blue-950 flex items-center gap-2">
                                        <span>🔑</span> Pembagian Sesi Kelas &amp; Kode Masuk (Token)
                                    </h3>
                                    <p class="text-xs text-blue-700/80 mt-0.5">
                                        Atur sesi untuk tiap kelas. Setiap sesi langsung memiliki kode masuk unik yang
                                        otomatis tersimpan saat ujian disimpan.
                                    </p>
                                </div>
                                <span class="text-xs font-bold px-3 py-1 rounded-full bg-blue-100 text-blue-800 self-start sm:self-auto"
                                    x-text="ringkasanSesiAktif.length + ' Sesi Aktif'"></span>
                            </div>

                            {{-- A. Pilih Sesi per Kelas --}}
                            <div class="space-y-2.5">
                                <p class="text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    1. Tentukan Sesi Masing-Masing Kelas
                                </p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                    <template x-for="kelas in detailKelasDipilih" :key="kelas.pengampu_kelas_id">
                                        <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-white border border-blue-100 shadow-2xs">
                                            <span class="text-sm font-bold text-gray-800 truncate"
                                                x-text="'Kelas ' + kelas.nama_kelas"></span>

                                            <select :name="`sesi_kelas[${kelas.pengampu_kelas_id}]`"
                                                x-model.number="sesiKelas[String(kelas.pengampu_kelas_id)]"
                                                {{ $onlyBobot ? 'disabled' : '' }}
                                                @change="pastikanTokenSesi($event.target.value); sinkronkanJamSesi();"
                                                class="px-3 py-1.5 rounded-lg border border-blue-200 text-xs font-bold text-blue-700 bg-blue-50/60 focus:ring-2 focus:ring-blue-500/25 outline-none cursor-pointer">
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

                            {{-- B. Daftar Kode Masuk (Token) & Jam Mulai per Sesi --}}
                            <div class="space-y-2.5 pt-2 border-t border-blue-100">
                                <p class="text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    2. Kode Masuk &amp; Jadwal per Sesi
                                </p>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5">
                                    <template x-for="item in ringkasanSesiAktif" :key="item.sesi">
                                        <div class="rounded-xl bg-white border border-blue-200/80 p-4 space-y-3 shadow-2xs">
                                            <div class="flex items-start justify-between gap-2">
                                                <div>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[11px] font-extrabold bg-blue-600 text-white">
                                                        SESI <span class="ml-1" x-text="item.sesi"></span>
                                                    </span>
                                                    <p class="text-xs font-semibold text-gray-700 mt-1.5">
                                                        Kelas: <span class="text-blue-700 font-bold" x-text="item.kelasList.join(', ')"></span>
                                                    </p>
                                                </div>

                                                <div class="text-right">
                                                    <span class="block text-[10px] font-bold uppercase text-gray-400">Kode Masuk</span>
                                                    <span class="inline-block mt-0.5 font-mono text-base font-black tracking-widest text-blue-700 bg-blue-50 border border-blue-200 px-2.5 py-1 rounded-lg select-all"
                                                        x-text="tokenSesi[item.sesi]"></span>
                                                    <input type="hidden" :name="`token_sesi[${item.sesi}]`" :value="tokenSesi[item.sesi]">
                                                </div>
                                            </div>

                                            <div class="flex items-center justify-between gap-2 pt-2 border-t border-gray-100">
                                                <label class="text-[11px] font-semibold text-gray-600">Jam Mulai:</label>
                                                <input type="time" :name="`jam_sesi[${item.sesi}]`"
                                                    x-model="jamSesi[item.sesi]"
                                                    {{ $onlyBobot ? 'readonly' : '' }}
                                                    class="text-xs font-mono font-bold text-gray-800 border border-gray-300 rounded-lg px-2 py-1 focus:border-blue-500 outline-none">
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
                            Kisi-kisi
                            <span class="text-gray-400 font-normal">
                                (opsional)
                            </span>
                        </label>

                        <textarea name="kisi_kisi" rows="4" maxlength="500"
                            {{ $onlyBobot ? 'readonly tabindex=-1' : '' }}
                            placeholder="Tuliskan kisi-kisi atau kompetensi yang akan diujikan..."
                            class="w-full border border-gray-300 rounded-xl px-4 py-3 outline-none transition
                               {{ $onlyBobot ? 'bg-gray-100 cursor-not-allowed' : 'focus:ring-2 focus:ring-blue-500/25 focus:border-blue-500' }}">{{ old('kisi_kisi', $ujian->kisi_kisi) }}</textarea>

                        <p class="text-xs text-gray-400 mt-1">
                            Maksimal 500 karakter.
                        </p>

                    </div>

                </div>


                {{-- ================================================= --}}
                {{-- EDITOR SOAL (SAMA PERSIS DENGAN CREATE) --}}
                {{-- ================================================= --}}
                <div class="mt-8 border-t border-gray-100 pt-8" id="section-editor-soal">

                    {{-- HEADER SOAL --}}
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-5">

                        <div>

                            <h2 class="text-lg font-bold text-gray-800">
                                Soal Ujian
                                <span
                                    class="text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-200 px-2.5 py-1 rounded-lg ml-2"
                                    x-text="questions.length + ' Soal' + (onlyBobot ? '' : ' (Minimal 2 Soal)')"></span>
                            </h2>

                            <p class="text-sm text-gray-500 mt-1">
                                {{ $onlyBobot
                                    ? 'Sesuaikan tingkat kesulitan soal untuk menghitung ulang bobot dan nilai siswa sesi sebelumnya.'
                                    : 'Tambahkan minimal 2 soal. Bobot otomatis disesuaikan menjadi total 100 poin setiap kali soal ditambah atau dihapus.' }}
                            </p>

                        </div>

                        @if (!$onlyBobot)
                            {{-- TAMBAH SOAL --}}
                            <button type="button" @click="addQuestion()"
                                class="inline-flex items-center justify-center gap-2
                                   px-5 py-3 bg-blue-600 hover:bg-blue-700
                                   text-white rounded-xl font-semibold transition shadow-xs cursor-pointer">

                                <span class="text-lg">+</span>

                                Tambah Soal

                            </button>
                        @endif

                    </div>


                    {{-- ================================================= --}}
                    {{-- DAFTAR + EDITOR SOAL --}}
                    {{-- ================================================= --}}
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">


                        {{-- DAFTAR SOAL --}}
                        <div class="lg:col-span-3 xl:col-span-2">

                            <div class="bg-slate-50/80 rounded-2xl border border-slate-200 p-4">

                                <h3 class="font-semibold text-gray-700 mb-3">
                                    Daftar Soal
                                </h3>

                                <div class="space-y-2 max-h-[600px] overflow-y-auto pr-0.5">

                                    <template x-for="(question, index) in questions" :key="question.localId">

                                        <button type="button" @click="switchQuestion(index)"
                                            class="w-full text-left px-3.5 py-3 rounded-xl border transition cursor-pointer"
                                            :class="activeQuestion === index ?
                                                'bg-blue-600 text-white border-blue-600 shadow-xs' :
                                                (Number(question.kesulitan) === 0 ?
                                                    'bg-rose-50/70 text-rose-800 border-rose-300 hover:border-rose-400' :
                                                    (isQuestionComplete(question) ?
                                                        'bg-white text-gray-700 border-gray-200 hover:border-blue-400' :
                                                        'bg-amber-50/70 text-gray-800 border-amber-300 hover:border-amber-400'
                                                    ))">

                                            <div class="flex items-center justify-between gap-2">

                                                <div class="flex items-center gap-1.5">
                                                    <span class="font-semibold text-sm">
                                                        Soal <span x-text="index + 1"></span>
                                                    </span>

                                                    <template x-if="Number(question.kesulitan) === 0">
                                                        <span
                                                            class="px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 text-[9px] font-extrabold"
                                                            title="Soal Tidak Valid / Dianulir">0 PT</span>
                                                    </template>
                                                    <template
                                                        x-if="Number(question.kesulitan) !== 0 && !isQuestionComplete(question)">
                                                        <span
                                                            class="w-4 h-4 rounded-full bg-amber-500 text-white text-[10px] font-black inline-flex items-center justify-center"
                                                            title="Soal ini belum lengkap">!</span>
                                                    </template>
                                                    <template
                                                        x-if="Number(question.kesulitan) !== 0 && isQuestionComplete(question)">
                                                        <span
                                                            class="w-4 h-4 rounded-full bg-emerald-500 text-white text-[10px] font-black inline-flex items-center justify-center"
                                                            title="Soal sudah lengkap">✓</span>
                                                    </template>
                                                </div>

                                                @if (!$onlyBobot)
                                                    <span x-show="questions.length > 2" @click.stop="removeQuestion(index)"
                                                        class="text-xs hover:underline"
                                                        :class="activeQuestion === index ?
                                                            'text-white' :
                                                            'text-red-500'">
                                                        Hapus
                                                    </span>
                                                @endif

                                            </div>

                                            <div class="text-[11px] mt-1 opacity-80"
                                                x-text="Number(question.kesulitan) === 0
                                                    ? 'Tidak Valid (0 Poin)'
                                                    : ('Bobot: ' + (question.bobot || 0) + ' Poin')"></div>

                                        </button>

                                    </template>

                                </div>

                            </div>

                        </div>


                        {{-- EDITOR SOAL --}}
                        <div class="lg:col-span-9 xl:col-span-10 min-w-0">

                            <div x-show="questions.length === 0"
                                class="bg-white border border-gray-200 rounded-2xl p-12 text-center">
                                <div class="text-4xl mb-3">📝</div>
                                <h3 class="font-bold text-gray-700">Belum ada soal</h3>
                                @if (!$onlyBobot)
                                    <button type="button" @click="addQuestion()"
                                        class="mt-4 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition cursor-pointer">
                                        Tambahkan Soal
                                    </button>
                                @endif
                            </div>

                            <template x-for="(question, index) in questions" :key="question.localId">

                                <div x-show="activeQuestion === index"
                                    class="bg-white border border-gray-200 rounded-2xl p-6">

                                    {{-- HEADER EDITOR --}}
                                    <div class="flex items-center justify-between mb-6">

                                        <div>

                                            <span class="text-sm text-gray-400">
                                                Soal Nomor
                                            </span>

                                            <h3 class="text-xl font-bold text-gray-800">
                                                <span x-text="index + 1"></span>
                                            </h3>

                                        </div>

                                        <div class="text-sm border px-3.5 py-1.5 rounded-xl"
                                            :class="Number(question.kesulitan) === 0
                                                ? 'text-rose-700 bg-rose-50 border-rose-200'
                                                : 'text-gray-500 bg-blue-50 border-blue-200'">
                                            <template x-if="Number(question.kesulitan) === 0">
                                                <span class="font-bold">Status: Tidak Valid (0 Poin)</span>
                                            </template>
                                            <template x-if="Number(question.kesulitan) !== 0">
                                                <span>
                                                    Bobot soal:
                                                    <span class="font-bold text-blue-700"
                                                        x-text="question.bobot + ' Poin'"></span>
                                                </span>
                                            </template>
                                        </div>

                                    </div>

                                    {{-- PEMBUNGKUS KONTEN SOAL YANG DIKUNCI SAAT $onlyBobot --}}
                                    <div class="{{ $onlyBobot ? 'opacity-65 pointer-events-none select-none' : '' }}">

                                        {{-- TIPE SOAL --}}
                                        <div class="mb-5">

                                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                                Tipe Soal
                                            </label>

                                            <select :name="`soals[${index}][tipe_soal]`" x-model="question.tipe_soal"
                                                @change="changeType(index)" {{ $onlyBobot ? 'disabled' : '' }}
                                                class="w-full border border-gray-300 rounded-xl px-4 py-3 outline-none transition
                                                       {{ $onlyBobot ? 'bg-gray-100' : 'focus:ring-2 focus:ring-blue-500/25 focus:border-blue-500' }}">

                                                <option value="single_choice">Pilihan Ganda</option>
                                                <option value="multiple_choice">Pilihan Ganda Kompleks</option>
                                                <option value="essay">Essay</option>
                                                <option value="matching">Menjodohkan</option>

                                            </select>

                                        </div>


                                        {{-- PERTANYAAN --}}
                                        <div class="mb-5">

                                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                                Pertanyaan <span class="text-red-500">*</span>
                                            </label>

                                            <textarea :name="`soals[${index}][pertanyaan]`" :id="`textarea-pertanyaan-${index}`"
                                                x-model="question.pertanyaan" rows="5" maxlength="500"
                                                {{ $onlyBobot ? 'readonly tabindex=-1' : '' }}
                                                placeholder="Tulis pertanyaan soal..."
                                                class="w-full border border-gray-300 rounded-xl px-4 py-3 outline-none transition
                                                   {{ $onlyBobot ? 'bg-gray-100' : 'focus:ring-2 focus:ring-blue-500/25 focus:border-blue-500' }}"></textarea>

                                            <p class="text-xs text-gray-400 mt-1">
                                                Maksimal 500 karakter.
                                            </p>

                                        </div>


                                        {{-- GAMBAR SOAL --}}
                                        <div class="mb-5">

                                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                                Gambar Soal
                                                <span class="text-gray-400 font-normal">(opsional)</span>
                                            </label>

                                            @if (!$onlyBobot)
                                                <input type="file" :name="`soals[${index}][gambar]`"
                                                    :id="`input-gambar-${question.localId}`"
                                                    @change="handleImageUpload($event, index)"
                                                    accept=".jpg,.jpeg,.png,.webp,.gif,image/jpeg,image/png,image/webp,image/gif"
                                                    class="w-full border border-gray-300 rounded-xl px-4 py-3
                                                           file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0
                                                           file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700
                                                           hover:file:bg-blue-100 cursor-pointer">

                                                <p class="text-xs text-gray-400 mt-1">
                                                    Format JPG, PNG, WEBP, atau GIF. Maksimal 2 MB.
                                                </p>
                                            @endif

                                            <template x-if="question.error_gambar">
                                                <p class="text-xs font-semibold text-red-600 mt-1.5 flex items-center gap-1"
                                                    x-text="question.error_gambar"></p>
                                            </template>

                                            <template x-if="question.preview_gambar">
                                                <div class="mt-3 relative inline-block">
                                                    <div class="border border-gray-200 rounded-xl p-2 bg-gray-50 shadow-xs">
                                                        <img :src="question.preview_gambar" alt="Preview Gambar Soal"
                                                            class="max-h-56 max-w-full rounded-lg object-contain block bg-white">
                                                    </div>

                                                    <div class="mt-2 flex flex-wrap items-center gap-2">
                                                        <span class="text-xs text-blue-700 font-medium"
                                                            x-text="question.nama_file_gambar"></span>

                                                        @if (!$onlyBobot)
                                                            <button type="button"
                                                                @click="removeImage(index, question.localId)"
                                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-xs font-semibold transition border border-red-200 cursor-pointer"
                                                                title="Batalkan / Hapus gambar">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                                    stroke-width="2" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                                    </path>
                                                                </svg>
                                                                <span
                                                                    x-text="question.is_new_image && question.existing_gambar ? 'Batalkan Gambar Baru' : 'Hapus Gambar'"></span>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            </template>

                                            <input type="hidden" :name="`soals[${index}][hapus_gambar]`"
                                                :value="question.hapus_gambar ? '1' : '0'">

                                        </div>


                                        {{-- PILIHAN GANDA --}}
                                        <template
                                            x-if="question.tipe_soal === 'single_choice' || question.tipe_soal === 'multiple_choice'">
                                            <div class="mb-5">

                                                <div class="flex items-center justify-between mb-3">

                                                    <label class="block text-sm font-semibold text-gray-700">
                                                        Pilihan Jawaban <span class="text-red-500">*</span>
                                                    </label>

                                                    @if (!$onlyBobot)
                                                        <button type="button" @click="addOption(index)"
                                                            x-show="question.pilihan.length < 6"
                                                            class="text-sm text-blue-600 hover:text-blue-800 font-semibold cursor-pointer">
                                                            + Tambah Pilihan
                                                        </button>
                                                    @endif

                                                </div>


                                                <div class="space-y-3">

                                                    <template x-for="(option, optionIndex) in question.pilihan"
                                                        :key="'option-' + question.localId + '-' + optionIndex">

                                                        <div class="flex items-center gap-3">

                                                            <template x-if="question.tipe_soal === 'single_choice'">
                                                                <input type="radio"
                                                                    :name="`soals[${index}][kunci_radio]`"
                                                                    :value="String.fromCharCode(97 + optionIndex)"
                                                                    :checked="question.kunci_jawaban.includes(String.fromCharCode(97 + optionIndex))"
                                                                    {{ $onlyBobot ? 'disabled' : '' }}
                                                                    @change="setSingleAnswer(index, optionIndex)"
                                                                    class="w-4 h-4 text-blue-600 accent-blue-600 cursor-pointer">
                                                            </template>

                                                            <template x-if="question.tipe_soal === 'multiple_choice'">
                                                                <input type="checkbox"
                                                                    :value="String.fromCharCode(97 + optionIndex)"
                                                                    :checked="question.kunci_jawaban.includes(String.fromCharCode(97 + optionIndex))"
                                                                    {{ $onlyBobot ? 'disabled' : '' }}
                                                                    @change="toggleMultipleAnswer(index, optionIndex)"
                                                                    class="w-4 h-4 text-blue-600 accent-blue-600 cursor-pointer">
                                                            </template>

                                                            <input type="text"
                                                                :id="`input-pilihan-${index}-${optionIndex}`"
                                                                :name="`soals[${index}][pilihan][${optionIndex}]`"
                                                                x-model="question.pilihan[optionIndex]"
                                                                {{ $onlyBobot ? 'readonly tabindex=-1' : '' }}
                                                                :placeholder="`Pilihan ${String.fromCharCode(65 + optionIndex)}`"
                                                                maxlength="200"
                                                                class="flex-1 min-w-0 border border-gray-300 rounded-xl px-4 py-3 outline-none transition
                                                                   {{ $onlyBobot ? 'bg-gray-100' : 'focus:ring-2 focus:ring-blue-500/25 focus:border-blue-500' }}">

                                                            @if (!$onlyBobot)
                                                                <button type="button"
                                                                    @click="removeOption(index, optionIndex)"
                                                                    class="px-3 py-2 text-red-500 hover:bg-red-50
                                                                       rounded-lg text-lg font-bold cursor-pointer"
                                                                    title="Hapus pilihan">
                                                                    ×
                                                                </button>
                                                            @endif

                                                        </div>

                                                    </template>

                                                </div>

                                                <p class="text-xs text-gray-400 mt-2">
                                                    Pilih kunci jawaban yang benar menggunakan tombol di sebelah kiri.
                                                </p>

                                            </div>
                                        </template>


                                        {{-- ESSAY & RUBRIK ESSAY --}}
                                        <template x-if="question.tipe_soal === 'essay'">
                                            <div class="mb-5 space-y-4">

                                                <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
                                                    <p class="text-sm text-blue-700">
                                                        Soal essay tidak menggunakan pilihan jawaban. Jawaban siswa akan
                                                        dikoreksi oleh guru menggunakan pedoman/rubrik di bawah ini.
                                                    </p>
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                                        Rubrik / Kunci Jawaban Essay
                                                        <span class="text-gray-400 font-normal">(opsional — boleh
                                                            dikosongkan)</span>
                                                    </label>

                                                    <textarea :name="`soals[${index}][kunci_jawaban_text]`"
                                                        x-model="question.kunci_jawaban_text" rows="4" maxlength="1000"
                                                        {{ $onlyBobot ? 'readonly tabindex=-1' : '' }}
                                                        placeholder="Tuliskan kata kunci jawaban, langkah penyelesaian, atau pedoman penskoran..."
                                                        class="w-full border border-gray-300 rounded-xl px-4 py-3 outline-none transition
                                                               {{ $onlyBobot ? 'bg-gray-100' : 'focus:ring-2 focus:ring-blue-500/25 focus:border-blue-500' }}"></textarea>

                                                    <p class="text-xs text-gray-400 mt-1">
                                                        Rubrik ini hanya tampil di halaman koreksi guru sebagai acuan
                                                        pemberian skor dan tidak terlihat oleh siswa saat ujian.
                                                    </p>
                                                </div>

                                            </div>
                                        </template>


                                        {{-- MATCHING --}}
                                        <template x-if="question.tipe_soal === 'matching'">
                                            <div class="mb-5">

                                                <div class="bg-purple-50 border border-purple-100 rounded-xl p-4">
                                                    <p class="text-sm text-purple-700">
                                                        Tipe menjodohkan dapat menggunakan data pasangan pada bagian pilihan
                                                        jawaban.
                                                    </p>
                                                </div>

                                            </div>
                                        </template>

                                    </div>


                                    {{-- TINGKAT KESULITAN & BOBOT OTOMATIS (TETAP AKTIF DI SEMUA STATUS!) --}}
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                                Tingkat Kesulitan {{ $onlyBobot ? '(Dapat Diubah)' : '' }}
                                            </label>

                                            <select :name="`soals[${index}][kesulitan]`"
                                                x-model.number="question.kesulitan" @change="hitungBobotOtomatis()"
                                                class="w-full border rounded-xl px-4 py-3 outline-none transition cursor-pointer
                                                       focus:ring-2 focus:ring-blue-500/25 focus:border-blue-500"
                                                :class="Number(question.kesulitan) === 0 ? 'border-rose-300 bg-rose-50/40 text-rose-700 font-semibold' : 'border-gray-300 bg-white'">
                                                @if ($onlyBobot)
                                                    <option value="0">Tidak Valid / Dianulir (Poin: 0)</option>
                                                @endif
                                                <option value="1">Mudah (Poin: 1)</option>
                                                <option value="2">Menengah (Poin: 2)</option>
                                                <option value="3">Susah (Poin: 3)</option>
                                            </select>

                                            <p class="text-xs mt-1"
                                                :class="Number(question.kesulitan) === 0 ? 'text-rose-600 font-semibold' : 'text-gray-400'"
                                                x-text="Number(question.kesulitan) === 0
                                                    ? 'Soal ini dianulir dan tidak mempengaruhi nilai siswa.'
                                                    : 'Bobot soal akan dihitung otomatis proporsional.'">
                                            </p>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                                Bobot Poin (Otomatis)
                                            </label>

                                            <div class="relative">
                                                <input type="number" step="0.01" :name="`soals[${index}][bobot]`"
                                                    x-model="question.bobot" readonly
                                                    class="w-full bg-gray-100 border border-gray-300 rounded-xl px-4 py-3 pr-14 font-bold text-gray-800 cursor-not-allowed">
                                                <span class="absolute right-4 top-3 text-sm font-semibold text-gray-400">
                                                    Poin
                                                </span>
                                            </div>

                                            <p class="text-xs text-blue-600 font-medium mt-1">
                                                ✓ Dihitung otomatis agar total tepat 100.
                                            </p>
                                        </div>

                                    </div>

                                    {{-- HIDDEN INPUT UNTUK SETIAP SOAL --}}
                                    <input type="hidden" :name="`soals[${index}][id]`" :value="question.id ?? ''">
                                    <input type="hidden" :name="`soals[${index}][urutan]`" :value="index + 1">

                                    <template x-for="(kunci, kIndex) in question.kunci_jawaban"
                                        :key="'kunci-' + question.localId + '-' + kIndex">
                                        <input type="hidden" :name="`soals[${index}][kunci_jawaban][${kIndex}]`"
                                            :value="kunci">
                                    </template>

                                </div>

                            </template>

                        </div>

                    </div>

                    {{-- RINGKASAN TOTAL BOBOT --}}
                    <div class="mt-6 p-4 rounded-xl border"
                        :class="totalBobot > 100.01 || totalBobot <= 0 ? 'bg-red-50 border-red-200' : 'bg-blue-50 border-blue-200'">

                        <div class="flex justify-between items-center gap-3">
                            <span class="text-sm font-semibold text-gray-700">Total Bobot Seluruh Soal</span>
                            <span class="font-bold text-lg"
                                :class="totalBobot > 100.01 || totalBobot <= 0 ? 'text-red-600' : 'text-blue-700'"
                                x-text="totalBobot + ' / 100'"></span>
                        </div>

                        <p class="text-xs mt-1"
                            :class="totalBobot > 100.01 || totalBobot <= 0 ? 'text-red-600' : 'text-gray-500'"
                            x-text="totalBobot <= 0
                                ? 'Minimal harus ada 1 soal yang valid (bukan Tidak Valid)!'
                                : (totalBobot > 100.01 ? 'Total bobot melebihi batas maksimal 100 poin!' : 'Total bobot soal terbagi proporsional menjadi tepat 100 poin.')">
                        </p>

                    </div>


                    {{-- TOMBOL AKSI UJIAN --}}
                    <div
                        class="w-full flex flex-row flex-wrap justify-end items-center
                           gap-3 mt-6 pt-6 border-t border-gray-100">

                        <a href="{{ route('guru.ujian.index') }}"
                            class="px-5 py-3 rounded-xl border border-gray-300
                               bg-white hover:bg-gray-50 text-gray-700
                               font-semibold transition">
                            Batal
                        </a>

                        <button type="submit"
                            class="px-5 py-3 rounded-xl bg-blue-600
                               hover:bg-blue-700 text-white
                               font-semibold transition shadow-xs cursor-pointer">
                            @if ($onlyBobot)
                                Simpan Perubahan Bobot &amp; Hitung Ulang Nilai
                            @elseif ($modeJadwalkan)
                                Simpan Jadwal
                            @else
                                Simpan Perubahan
                            @endif
                        </button>

                    </div>

                </div>

            </form>

        </div>

        {{-- ========================================================= --}}
        {{-- MODAL ALERT VALIDASI & KONFIRMASI (IDENTIK DENGAN CREATE) --}}
        {{-- ========================================================= --}}
        <div x-show="modalAlert.open" x-cloak x-transition.opacity @keydown.escape.window="closeModal()"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/45 backdrop-blur-[2px]">
            <div @click.away="closeModal()"
                class="bg-white rounded-2xl max-w-md sm:max-w-lg w-full border border-slate-200/80 shadow-2xl overflow-hidden">

                <div class="p-6 space-y-4">
                    {{-- Header Modal --}}
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-extrabold uppercase tracking-wider flex items-center gap-1.5"
                                :class="modalAlert.variant === 'danger' ? 'text-rose-600' : 'text-blue-600'">
                                <template x-if="modalAlert.showDot">
                                    <span class="w-1.5 h-1.5 rounded-full"
                                        :class="modalAlert.variant === 'danger' ? 'bg-rose-600' : 'bg-blue-600'"></span>
                                </template>
                                <span x-text="modalAlert.kicker"></span>
                            </p>
                            <h3 class="text-xl font-extrabold text-slate-900 tracking-tight mt-1.5 leading-snug"
                                x-text="modalAlert.title"></h3>
                            <p x-show="modalAlert.subtitle"
                                class="text-xs sm:text-sm text-slate-500 mt-1.5 leading-relaxed"
                                x-text="modalAlert.subtitle"></p>
                        </div>

                        <button type="button" @click="closeModal()"
                            class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition cursor-pointer shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Info Butir Soal (Khusus Validasi / Hapus Butir Soal) --}}
                    <template x-if="modalAlert.soalInfo">
                        <div
                            class="flex flex-wrap items-center justify-between gap-3 p-3.5 rounded-xl bg-slate-50/90 border border-slate-200/70">
                            <div class="flex items-center gap-3 min-w-0">
                                <div
                                    class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 border border-blue-100/80 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs sm:text-sm font-bold text-slate-900"
                                        x-text="'Soal Nomor ' + modalAlert.soalInfo.nomor"></p>
                                    <p class="text-[11px] text-slate-500 mt-0.5 truncate"
                                        x-text="'Tipe Soal: ' + modalAlert.soalInfo.tipe + ' • Bobot: ' + modalAlert.soalInfo.bobot + ' Poin'">
                                    </p>
                                </div>
                            </div>

                            <template x-if="modalAlert.soalInfo.badge">
                                <span
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-600 border border-blue-200/70 shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="7" />
                                        <circle cx="12" cy="12" r="2" />
                                    </svg>
                                    <span x-text="modalAlert.soalInfo.badge"></span>
                                </span>
                            </template>
                        </div>
                    </template>

                    {{-- Callout Box Peringatan --}}
                    <div class="flex items-start gap-3 p-4 rounded-xl border text-xs sm:text-[13px] leading-relaxed"
                        :class="modalAlert.variant === 'danger'
                            ? 'bg-rose-50/90 border-rose-200 text-rose-800'
                            : 'bg-amber-50/85 border-amber-200/90 text-amber-900'">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 mt-0.5"
                            :class="modalAlert.variant === 'danger' ? 'bg-rose-100 text-rose-600' : 'bg-amber-100/80 text-amber-600'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="space-y-1">
                            <template x-if="modalAlert.inlineCallout">
                                <p>
                                    <strong x-text="modalAlert.calloutTitle"></strong>
                                    <span x-text="' ' + modalAlert.calloutBody"></span>
                                </p>
                            </template>
                            <template x-if="!modalAlert.inlineCallout">
                                <div>
                                    <p class="font-bold" x-text="modalAlert.calloutTitle"></p>
                                    <p x-show="modalAlert.calloutBody" class="opacity-90 mt-0.5"
                                        x-text="modalAlert.calloutBody"></p>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Langkah Perbaikan (Opsional) --}}
                    <template x-if="modalAlert.steps && modalAlert.steps.length > 0">
                        <div class="p-4 rounded-xl bg-slate-50/80 border border-slate-200/70 space-y-2.5">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500"
                                x-text="modalAlert.stepsTitle"></p>
                            <div class="space-y-2">
                                <template x-for="(step, sIdx) in modalAlert.steps" :key="sIdx">
                                    <div class="flex items-start gap-2.5 text-xs text-slate-700 leading-relaxed">
                                        <span
                                            class="w-5 h-5 rounded-full bg-blue-100 text-blue-700 text-[11px] font-bold flex items-center justify-center shrink-0 mt-0.5"
                                            x-text="sIdx + 1"></span>
                                        <span x-html="step"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Footer Modal --}}
                <div class="px-6 py-4 bg-slate-50/70 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" @click="closeModal()"
                        class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200/80 transition cursor-pointer"
                        x-text="modalAlert.cancelText">
                    </button>

                    <button type="button" @click="handleModalAction()"
                        class="inline-flex items-center gap-1.5 px-5 py-2 rounded-xl text-xs font-semibold text-white transition shadow-xs cursor-pointer"
                        :class="modalAlert.variant === 'danger'
                            ? 'bg-rose-600 hover:bg-rose-700'
                            : 'bg-blue-600 hover:bg-blue-700'">
                        <template x-if="modalAlert.actionIcon === 'pencil'">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </template>
                        <template x-if="modalAlert.actionIcon === 'trash'">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </template>
                        <span x-text="modalAlert.actionText"></span>
                        <template x-if="modalAlert.actionIcon === 'arrow'">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </template>
                    </button>
                </div>

            </div>
        </div>

    </div>


    {{-- ============================================================= --}}
    {{-- JAVASCRIPT --}}
    {{-- ============================================================= --}}
    <script>
        function editUjian() {
            return {
                onlyBobot: @js((bool) $onlyBobot),
                modeJadwalkan: @js((bool) $modeJadwalkan),
                initialMapel: @js((string) ($ujian->pengampuKelas?->guruMapel?->mapel_id ?? '')),

                // State Modal Validasi & Konfirmasi (identik dengan Create)
                modalAlert: {
                    open: false,
                    variant: 'warning',
                    showDot: true,
                    kicker: '',
                    title: '',
                    subtitle: '',
                    soalInfo: null,
                    inlineCallout: false,
                    calloutTitle: '',
                    calloutBody: '',
                    stepsTitle: 'LANGKAH PERBAIKAN CEPAT:',
                    steps: [],
                    cancelText: 'Tutup',
                    actionText: 'Mengerti',
                    actionIcon: 'arrow',
                    onAction: null,
                },

                openModal(cfg) {
                    this.modalAlert = {
                        open: true,
                        variant: cfg.variant || 'warning',
                        showDot: cfg.showDot !== undefined ? cfg.showDot : true,
                        kicker: cfg.kicker || 'VALIDASI INSTRUMEN UJIAN',
                        title: cfg.title || 'Periksa Kembali Isian',
                        subtitle: cfg.subtitle || '',
                        soalInfo: cfg.soalInfo || null,
                        inlineCallout: cfg.inlineCallout || false,
                        calloutTitle: cfg.calloutTitle || '',
                        calloutBody: cfg.calloutBody || '',
                        stepsTitle: cfg.stepsTitle || 'LANGKAH PERBAIKAN CEPAT:',
                        steps: cfg.steps || [],
                        cancelText: cfg.cancelText || 'Tutup',
                        actionText: cfg.actionText || 'Mengerti',
                        actionIcon: cfg.actionIcon !== undefined ? cfg.actionIcon : 'arrow',
                        onAction: cfg.onAction || null,
                    };
                },

                closeModal() {
                    this.modalAlert.open = false;
                },

                handleModalAction() {
                    const cb = this.modalAlert.onAction;
                    this.modalAlert.open = false;
                    if (typeof cb === 'function') {
                        this.$nextTick(() => cb());
                    }
                },

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

                // Notifikasi berhasil tetap menggunakan alert asli
                salinKode(kode) {
                    if (navigator.clipboard && window.isSecureContext) {
                        navigator.clipboard.writeText(kode);
                    } else {
                        const el = document.createElement('textarea');
                        el.value = kode;
                        el.style.position = 'fixed';
                        el.style.opacity = '0';
                        document.body.appendChild(el);
                        el.select();
                        document.execCommand('copy');
                        document.body.removeChild(el);
                    }

                    this.openModal({
                        kicker: 'SALIN KODE MASUK',
                        title: 'Kode Masuk Berhasil Disalin!',
                        subtitle: 'Kode sesi ujian telah tersimpan ke clipboard perangkat Anda.',
                        calloutTitle: `Kode masuk ${kode} berhasil disalin ke clipboard!`,
                        calloutBody: 'Silakan bagikan kode ini kepada siswa pada sesi yang sesuai saat ujian dimulai.',
                        cancelText: 'Tutup',
                        actionText: 'Mengerti',
                        actionIcon: 'none',
                    });
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

                    this.sinkronkanSesiKelas();
                },

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

                validateForm(event) {
                    // Validasi minimal 1 soal valid (berlaku juga pada mode $onlyBobot)
                    const adaSoalValid = this.questions.some(q => Number(q.kesulitan) > 0 && Number(q.bobot) > 0);
                    if (!adaSoalValid || this.totalBobot <= 0) {
                        event.preventDefault();
                        this.openModal({
                            kicker: 'VALIDASI BOBOT SOAL',
                            title: 'Belum Ada Soal yang Valid!',
                            subtitle: 'Minimal satu butir soal harus memiliki bobot penilaian aktif.',
                            calloutTitle: 'Minimal harus ada 1 soal yang valid (bukan berstatus "Tidak Valid")!',
                            calloutBody: 'Ubah tingkat kesulitan minimal pada satu soal menjadi Mudah, Menengah, atau Susah agar total bobot terhitung 100 poin.',
                            cancelText: 'Tutup',
                            actionText: 'Periksa Bobot Soal',
                            actionIcon: 'arrow',
                            onAction: () => {
                                document.getElementById('section-editor-soal')?.scrollIntoView({ behavior: 'smooth' });
                            }
                        });
                        return;
                    }

                    if (this.totalBobot > 100.5) {
                        event.preventDefault();
                        this.openModal({
                            kicker: 'VALIDASI BOBOT SOAL',
                            title: 'Total Bobot Melebihi 100 Poin!',
                            subtitle: 'Periksa kembali pembagian bobot seluruh butir soal ujian.',
                            calloutTitle: 'Total bobot soal melebihi 100 poin!',
                            calloutBody: `Total saat ini adalah ${this.totalBobot} poin. Sesuaikan tingkat kesulitan soal agar total bobot kembali pas 100 poin.`,
                            cancelText: 'Tutup',
                            actionText: 'Periksa Soal',
                            actionIcon: 'arrow',
                            onAction: () => {
                                document.getElementById('section-editor-soal')?.scrollIntoView({ behavior: 'smooth' });
                            }
                        });
                        return;
                    }

                    if (this.onlyBobot) {
                        return;
                    }

                    // 1. Judul Ujian Wajib Diisi
                    const inputJudul = this.$refs.inputJudul;
                    if (!inputJudul || !inputJudul.value.trim()) {
                        event.preventDefault();
                        this.openModal({
                            showDot: false,
                            kicker: 'VALIDASI INSTRUMEN UJIAN',
                            title: 'Judul Ujian Wajib Diisi',
                            subtitle: 'Lengkapi judul instrumen ujian sebelum melanjutkan proses penyimpanan atau penerbitan.',
                            inlineCallout: true,
                            calloutTitle: 'Kolom Judul Ujian tidak boleh kosong.',
                            calloutBody: 'Masukkan nama atau topik ujian yang jelas agar mudah diidentifikasi oleh guru pengampu dan peserta didik.',
                            cancelText: 'Tutup',
                            actionText: 'Mengerti & Lengkapi',
                            actionIcon: 'pencil',
                            onAction: () => {
                                inputJudul?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                inputJudul?.focus();
                            }
                        });
                        return;
                    }

                    // 2. Target Kelas Belum Dipilih
                    if (this.kelasDipilih.length === 0) {
                        event.preventDefault();
                        this.openModal({
                            showDot: false,
                            kicker: 'VALIDASI TARGET KELAS',
                            title: 'Target Kelas Belum Dipilih!',
                            subtitle: 'Silakan lengkapi konfigurasi target peserta sebelum menyimpan atau menerbitkan ujian.',
                            calloutTitle: 'Silakan pilih Mata Pelajaran, Tingkatan, dan centang minimal 1 Kelas terlebih dahulu.',
                            stepsTitle: 'LANGKAH YANG DIPERLUKAN:',
                            steps: [
                                '<strong>Pilih Mata Pelajaran</strong> <span class="text-slate-400">(misal: Matematika / IPA)</span>',
                                '<strong>Pilih Tingkatan Kelas</strong> <span class="text-slate-400">(misal: Kelas 7, 8, atau 9)</span>',
                                '<strong>Centang Minimal 1 Rombel / Kelas Target</strong> <span class="text-slate-400">(misal: 7-A, 7-B)</span>',
                            ],
                            cancelText: 'Batal',
                            actionText: 'Pilih Kelas Sekarang',
                            actionIcon: 'arrow',
                            onAction: () => {
                                document.getElementById('section-target-kelas')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            }
                        });
                        return;
                    }

                    // 3. Durasi Ujian
                    const durasiVal = Number(this.$refs.inputDurasi?.value || 0);
                    if (!durasiVal || durasiVal < 1 || durasiVal > 999) {
                        event.preventDefault();
                        this.openModal({
                            kicker: 'VALIDASI PENGATURAN UJIAN',
                            title: 'Durasi Ujian Tidak Valid!',
                            subtitle: 'Tentukan lama waktu pengerjaan ujian sebelum menyimpan perubahan.',
                            calloutTitle: 'Durasi Ujian harus diisi antara 1 sampai 999 menit!',
                            calloutBody: 'Pastikan kolom durasi berisi angka yang valid agar timer pengerjaan siswa berjalan akurat.',
                            cancelText: 'Tutup',
                            actionText: 'Atur Durasi',
                            actionIcon: 'arrow',
                            onAction: () => {
                                this.$refs.inputDurasi?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                this.$refs.inputDurasi?.focus();
                            }
                        });
                        return;
                    }

                    // 4. Nilai KKM
                    const kkmRaw = this.$refs.inputKkm?.value;
                    if (kkmRaw === '' || Number(kkmRaw) < 0 || Number(kkmRaw) > 100) {
                        event.preventDefault();
                        this.openModal({
                            kicker: 'VALIDASI PENGATURAN UJIAN',
                            title: 'Nilai KKM Tidak Valid!',
                            subtitle: 'Tentukan batas ketuntasan minimal siswa sebelum menyimpan perubahan.',
                            calloutTitle: 'Nilai KKM harus diisi antara 0 sampai 100!',
                            calloutBody: 'Nilai KKM digunakan sebagai acuan ketuntasan hasil evaluasi dan rekomendasi remidi.',
                            cancelText: 'Tutup',
                            actionText: 'Atur Nilai KKM',
                            actionIcon: 'arrow',
                            onAction: () => {
                                this.$refs.inputKkm?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                this.$refs.inputKkm?.focus();
                            }
                        });
                        return;
                    }

                    // 5. Jadwal (Waktu Mulai & Deadline)
                    const waktuMulaiVal = this.$refs.inputWaktuMulai?.value || '';
                    const deadlineVal = this.$refs.inputDeadline?.value || '';

                    if (this.modeJadwalkan) {
                        if (!waktuMulaiVal) {
                            event.preventDefault();
                            this.openModal({
                                kicker: 'VALIDASI JADWAL UJIAN',
                                title: 'Waktu Mulai Belum Diisi!',
                                subtitle: 'Silakan tentukan jadwal waktu mulai sebelum menjadwalkan atau mengaktifkan ujian.',
                                calloutTitle: 'Untuk menjadwalkan ujian, kolom "Waktu Mulai" wajib diisi!',
                                calloutBody: 'Sistem membutuhkan waktu mulai yang spesifik agar token ujian dapat diaktifkan dan dikerjakan siswa sesuai sesi yang ditentukan.',
                                stepsTitle: 'LANGKAH PERBAIKAN CEPAT:',
                                steps: [
                                    'Klik tombol <strong>"Atur Waktu Mulai"</strong> di bawah.',
                                    'Pilih tanggal dan jam pelaksanaan ujian pada kolom input.',
                                    'Pastikan batas akhir (<em>deadline</em>) lebih lama dari waktu mulai.',
                                ],
                                cancelText: 'Tutup',
                                actionText: 'Atur Waktu Mulai',
                                actionIcon: 'arrow',
                                onAction: () => {
                                    this.$refs.inputWaktuMulai?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                    this.$refs.inputWaktuMulai?.focus();
                                }
                            });
                            return;
                        }
                        if (!deadlineVal) {
                            event.preventDefault();
                            this.openModal({
                                kicker: 'VALIDASI JADWAL UJIAN',
                                title: 'Deadline Belum Diisi!',
                                subtitle: 'Silakan tentukan batas akhir pengerjaan sebelum menjadwalkan ujian.',
                                calloutTitle: 'Untuk menjadwalkan ujian, kolom "Deadline" wajib diisi!',
                                calloutBody: 'Sistem membutuhkan batas akhir yang jelas agar sesi ujian otomatis ditutup setelah waktu berakhir.',
                                stepsTitle: 'LANGKAH PERBAIKAN CEPAT:',
                                steps: [
                                    'Klik tombol <strong>"Atur Deadline"</strong> di bawah.',
                                    'Pilih tanggal dan jam batas akhir pengerjaan pada kolom input.',
                                    'Pastikan waktu deadline lebih akhir daripada waktu mulai.',
                                ],
                                cancelText: 'Tutup',
                                actionText: 'Atur Deadline',
                                actionIcon: 'arrow',
                                onAction: () => {
                                    this.$refs.inputDeadline?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                    this.$refs.inputDeadline?.focus();
                                }
                            });
                            return;
                        }
                    }

                    if (waktuMulaiVal && deadlineVal) {
                        if (new Date(deadlineVal) <= new Date(waktuMulaiVal)) {
                            event.preventDefault();
                            this.openModal({
                                kicker: 'VALIDASI JADWAL UJIAN',
                                title: 'Rentang Jadwal Tidak Valid!',
                                subtitle: 'Periksa kembali perbandingan antara Waktu Mulai dan Deadline ujian.',
                                calloutTitle: 'Waktu Deadline harus lebih akhir daripada Waktu Mulai!',
                                calloutBody: 'Sesuaikan tanggal atau jam batas akhir agar siswa memiliki waktu pengerjaan yang valid.',
                                cancelText: 'Tutup',
                                actionText: 'Perbaiki Deadline',
                                actionIcon: 'arrow',
                                onAction: () => {
                                    this.$refs.inputDeadline?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                    this.$refs.inputDeadline?.focus();
                                }
                            });
                            return;
                        }
                    }

                    // 6. Minimal 2 Soal
                    if (this.questions.length < 2) {
                        event.preventDefault();
                        this.openModal({
                            kicker: 'VALIDASI BUTIR SOAL',
                            title: 'Jumlah Soal Belum Mencukupi!',
                            subtitle: 'Tambahkan butir soal sebelum menyimpan perubahan ujian.',
                            calloutTitle: 'Ujian minimal harus memiliki 2 soal!',
                            calloutBody: `Saat ini baru terdapat ${this.questions.length} soal. Silakan klik tombol "+ Tambah Soal" pada bagian Editor Soal.`,
                            cancelText: 'Tutup',
                            actionText: 'Ke Editor Soal',
                            actionIcon: 'arrow',
                            onAction: () => {
                                document.getElementById('section-editor-soal')?.scrollIntoView({ behavior: 'smooth' });
                            }
                        });
                        return;
                    }

                    // 7. Kelengkapan Setiap Butir Soal
                    for (let i = 0; i < this.questions.length; i++) {
                        const q = this.questions[i];
                        const nomor = i + 1;
                        const infoSoal = {
                            nomor,
                            tipe: this.typeLabel(q.tipe_soal),
                            bobot: q.bobot || 0,
                            badge: 'Fokus Otomatis',
                        };

                        if (!q.pertanyaan || q.pertanyaan.trim() === '') {
                            event.preventDefault();
                            this.activeQuestion = i;
                            this.openModal({
                                kicker: 'VALIDASI BUTIR SOAL',
                                title: 'Pertanyaan Soal Belum Diisi!',
                                subtitle: 'Lengkapi teks pertanyaan dan opsi jawaban sebelum menyimpan instrumen ujian.',
                                soalInfo: infoSoal,
                                calloutTitle: `Pertanyaan pada Soal nomor ${nomor} belum diisi!`,
                                calloutBody: `Layar telah otomatis diarahkan ke editor Soal nomor ${nomor} agar Anda dapat langsung mengetikkan pertanyaan.`,
                                cancelText: 'Tutup',
                                actionText: `Isi Soal Nomor ${nomor}`,
                                actionIcon: 'arrow',
                                onAction: () => {
                                    const el = document.getElementById(`textarea-pertanyaan-${i}`);
                                    el?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                    el?.focus();
                                }
                            });
                            return;
                        }

                        if (q.tipe_soal === 'single_choice' || q.tipe_soal === 'multiple_choice') {
                            if (!Array.isArray(q.pilihan) || q.pilihan.length < 2) {
                                event.preventDefault();
                                this.activeQuestion = i;
                                this.openModal({
                                    kicker: 'VALIDASI BUTIR SOAL',
                                    title: 'Pilihan Jawaban Kurang!',
                                    subtitle: 'Soal pilihan ganda membutuhkan minimal 2 opsi jawaban.',
                                    soalInfo: infoSoal,
                                    calloutTitle: `Soal nomor ${nomor} minimal harus memiliki 2 pilihan jawaban!`,
                                    calloutBody: 'Silakan tambahkan opsi jawaban melalui tombol "+ Tambah Pilihan".',
                                    cancelText: 'Tutup',
                                    actionText: `Lengkapi Soal Nomor ${nomor}`,
                                    actionIcon: 'arrow',
                                    onAction: () => {
                                        document.getElementById('section-editor-soal')?.scrollIntoView({ behavior: 'smooth' });
                                    }
                                });
                                return;
                            }

                            for (let p = 0; p < q.pilihan.length; p++) {
                                if (!q.pilihan[p] || String(q.pilihan[p]).trim() === '') {
                                    event.preventDefault();
                                    this.activeQuestion = i;
                                    const huruf = String.fromCharCode(65 + p);
                                    this.openModal({
                                        kicker: 'VALIDASI BUTIR SOAL',
                                        title: `Pilihan ${huruf} Belum Diisi!`,
                                        subtitle: 'Pastikan seluruh kolom opsi jawaban terisi sebelum menyimpan instrumen ujian.',
                                        soalInfo: infoSoal,
                                        calloutTitle: `Pilihan ${huruf} pada Soal nomor ${nomor} belum diisi!`,
                                        calloutBody: `Layar telah otomatis diarahkan ke editor Soal nomor ${nomor} agar Anda dapat mengisi Pilihan ${huruf} atau menghapus opsi yang tidak diperlukan.`,
                                        cancelText: 'Tutup',
                                        actionText: `Isi Pilihan ${huruf}`,
                                        actionIcon: 'arrow',
                                        onAction: () => {
                                            const el = document.getElementById(`input-pilihan-${i}-${p}`);
                                            el?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                            el?.focus();
                                        }
                                    });
                                    return;
                                }
                            }

                            if (!Array.isArray(q.kunci_jawaban) || q.kunci_jawaban.length === 0) {
                                event.preventDefault();
                                this.activeQuestion = i;
                                this.openModal({
                                    kicker: 'VALIDASI BUTIR SOAL',
                                    title: 'Kunci Jawaban Belum Dipilih!',
                                    subtitle: 'Setiap soal pilihan ganda wajib memiliki kunci jawaban untuk penilaian otomatis.',
                                    soalInfo: infoSoal,
                                    calloutTitle: `Kunci jawaban pada Soal nomor ${nomor} belum dipilih!`,
                                    calloutBody: `Silakan pilih atau centang jawaban yang benar di sebelah kiri opsi pada Soal nomor ${nomor}.`,
                                    cancelText: 'Tutup',
                                    actionText: `Pilih Kunci Soal ${nomor}`,
                                    actionIcon: 'arrow',
                                    onAction: () => {
                                        const el = document.getElementById(`input-pilihan-${i}-0`);
                                        el?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                    }
                                });
                                return;
                            }
                        }
                    }
                },

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
                        this.openModal({
                            kicker: 'BATAS BUTIR SOAL',
                            title: 'Batas Maksimal Soal Tercapai',
                            subtitle: 'Satu instrumen ujian dibatasi hingga 100 butir soal.',
                            calloutTitle: 'Maksimal 100 soal dalam satu ujian.',
                            cancelText: 'Tutup',
                            actionText: 'Mengerti',
                            actionIcon: 'none',
                        });
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

                executeRemoveQuestion(index) {
                    const targetQuestion = this.questions[index];
                    if (targetQuestion?.is_new_image && targetQuestion?.preview_gambar) {
                        URL.revokeObjectURL(targetQuestion.preview_gambar);
                    }

                    this.questions.splice(index, 1);

                    if (this.activeQuestion >= this.questions.length) {
                        this.activeQuestion = this.questions.length - 1;
                    }

                    this.hitungBobotOtomatis();
                },

                removeQuestion(index) {
                    if (this.onlyBobot) return;

                    if (this.questions.length <= 2) {
                        this.openModal({
                            kicker: 'BATAS BUTIR SOAL',
                            title: 'Soal Tidak Dapat Dihapus',
                            subtitle: 'Setiap instrumen ujian wajib memiliki minimal 2 butir soal.',
                            calloutTitle: 'Minimal harus ada 2 soal dalam satu ujian.',
                            cancelText: 'Tutup',
                            actionText: 'Mengerti',
                            actionIcon: 'none',
                        });
                        return;
                    }

                    const targetQuestion = this.questions[index];
                    if (this.isQuestionFilled(targetQuestion)) {
                        this.openModal({
                            variant: 'danger',
                            kicker: 'KONFIRMASI HAPUS SOAL',
                            title: `Hapus Soal Nomor ${index + 1}?`,
                            subtitle: 'Soal ini sudah memiliki isian pertanyaan, gambar, atau pilihan jawaban.',
                            soalInfo: {
                                nomor: index + 1,
                                tipe: this.typeLabel(targetQuestion.tipe_soal),
                                bobot: targetQuestion.bobot || 0,
                                badge: null,
                            },
                            calloutTitle: `Soal nomor ${index + 1} sudah memiliki isian.`,
                            calloutBody: 'Jika dihapus, seluruh isian pada soal ini akan hilang dan bobot soal lainnya akan dihitung ulang otomatis.',
                            cancelText: 'Batal',
                            actionText: 'Ya, Hapus Soal',
                            actionIcon: 'trash',
                            onAction: () => this.executeRemoveQuestion(index),
                        });
                        return;
                    }

                    this.executeRemoveQuestion(index);
                },

                addOption(qIndex) {
                    if (this.onlyBobot) return;

                    if (this.questions[qIndex].pilihan.length >= 6) {
                        this.openModal({
                            kicker: 'VALIDASI PILIHAN JAWABAN',
                            title: 'Batas Pilihan Jawaban Tercapai',
                            subtitle: 'Setiap soal dibatasi maksimal 6 opsi jawaban.',
                            calloutTitle: 'Maksimal 6 pilihan jawaban, yaitu A sampai F.',
                            cancelText: 'Tutup',
                            actionText: 'Mengerti',
                            actionIcon: 'none',
                        });
                        return;
                    }
                    this.questions[qIndex].pilihan.push('');
                },

                removeOption(qIndex, optIndex) {
                    if (this.onlyBobot) return;

                    const q = this.questions[qIndex];
                    if (q.pilihan.length <= 2) {
                        this.openModal({
                            kicker: 'VALIDASI PILIHAN JAWABAN',
                            title: 'Minimal 2 Pilihan Jawaban',
                            subtitle: 'Opsi jawaban tidak dapat dikurangi lagi.',
                            calloutTitle: 'Minimal harus ada 2 pilihan jawaban.',
                            cancelText: 'Tutup',
                            actionText: 'Mengerti',
                            actionIcon: 'none',
                        });
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
                        event.target.value = '';
                        this.openModal({
                            kicker: 'VALIDASI GAMBAR SOAL',
                            title: 'Format Gambar Tidak Didukung!',
                            subtitle: 'Pilih file gambar dengan format yang diizinkan oleh sistem.',
                            calloutTitle: 'Format file tidak didukung!',
                            calloutBody: 'Hanya diperbolehkan mengunggah gambar berformat JPG, PNG, WEBP, atau GIF.',
                            cancelText: 'Tutup',
                            actionText: 'Mengerti',
                            actionIcon: 'none',
                        });
                        return;
                    }

                    const maxSizeBytes = 2 * 1024 * 1024;
                    if (file.size > maxSizeBytes) {
                        const ukuranMb = (file.size / (1024 * 1024)).toFixed(2);
                        const msg = `Ukuran gambar terlalu besar (${ukuranMb} MB)! Maksimal ukuran yang diperbolehkan adalah 2 MB.`;
                        q.error_gambar = msg;
                        event.target.value = '';
                        this.openModal({
                            kicker: 'VALIDASI GAMBAR SOAL',
                            title: 'Ukuran Gambar Terlalu Besar!',
                            subtitle: 'Kompres ukuran gambar terlebih dahulu sebelum mengunggah ke soal.',
                            calloutTitle: `Ukuran gambar terlalu besar (${ukuranMb} MB)!`,
                            calloutBody: 'Maksimal ukuran file gambar yang diperbolehkan adalah 2 MB.',
                            cancelText: 'Tutup',
                            actionText: 'Mengerti',
                            actionIcon: 'none',
                        });
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
