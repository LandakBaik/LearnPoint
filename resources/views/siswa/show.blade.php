@extends('layouts.app')

@section('title', 'Detail Siswa: ' . $siswa->nama_siswa)

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{ modalOpen: false, modalImg: '' }">

    <!-- Header Navigation -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('siswa.index') }}" class="p-2 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Detail Data Siswa</h1>
                <p class="text-sm text-gray-500">Informasi biodata, kelas, kontak wali, dan jadwal pelajaran.</p>
            </div>
        </div>
        <div>
            <a href="{{ route('siswa.edit', $siswa->id) }}" class="px-4 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-sm font-semibold rounded-xl border border-emerald-200 transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Edit Siswa</span>
            </a>
        </div>
    </div>

    <!-- Profile Header Card -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5 pb-6 border-b border-gray-100">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-600 text-white font-extrabold text-xl flex items-center justify-center shadow-lg shadow-emerald-200 shrink-0">
                {{ strtoupper(substr($siswa->nama_siswa, 0, 2)) }}
            </div>
            <div class="flex-1">
                <div class="flex flex-wrap items-center gap-2.5">
                    <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900 leading-tight">{{ $siswa->nama_siswa }}</h2>
                    @if($siswa->jenis_kelamin === 'L')
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-700">Laki-laki</span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-700">Perempuan</span>
                    @endif
                </div>
                <div class="flex flex-wrap items-center gap-3 mt-1.5 text-sm text-gray-500">
                    <span class="font-mono bg-gray-100 px-2 py-0.5 rounded text-gray-700 text-xs font-semibold">NIS: {{ $siswa->nis }}</span>
                    <span>&bull;</span>
                    @if($siswa->kelas)
                        <span class="font-semibold text-emerald-700 text-xs bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                            Kelas: {{ $siswa->kelas->nama_kelas }} (Tingkat {{ $siswa->kelas->tingkatan }})
                        </span>
                    @else
                        <span class="text-xs text-amber-600 italic">Belum masuk kelas</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Grid Detail Biodata -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 pt-6 text-sm">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-0.5">Tanggal Lahir</p>
                <p class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($siswa->tanggal_lahir)->translatedFormat('d F Y') }}</p>
            </div>

            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-0.5">Orang Tua / Wali</p>
                <p class="font-semibold text-gray-800">{{ $siswa->wali_murid }}</p>
            </div>

            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-0.5">No. HP / WhatsApp Wali</p>
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $siswa->nohp_wali) }}" target="_blank" class="font-mono font-semibold text-emerald-600 hover:underline flex items-center gap-1">
                    <span>{{ $siswa->nohp_wali }}</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            </div>

            <div class="sm:col-span-2">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-0.5">Alamat Lengkap</p>
                <p class="text-gray-700 leading-relaxed">{{ $siswa->alamat }}</p>
            </div>

            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-0.5">Akun Login Sistem</p>
                @if($siswa->user)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 mt-0.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        {{ $siswa->user->email }}
                    </span>
                @else
                    <span class="text-xs text-amber-600 italic">Belum ada akun</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Card Jadwal Kelas Siswa -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-gray-900">Foto Jadwal Pelajaran Kelas</h3>
                <p class="text-xs text-gray-500">
                    @if($siswa->kelas)
                        Jadwal kelas {{ $siswa->kelas->nama_kelas }} yang dapat diakses oleh siswa ini.
                    @else
                        Siswa belum ditetapkan ke dalam kelas apapun.
                    @endif
                </p>
            </div>
            @if($siswa->kelas)
                <a href="{{ route('kelas.show', $siswa->kelas->id) }}" class="text-xs font-semibold text-emerald-600 hover:underline">
                    Buka Kelas &rarr;
                </a>
            @endif
        </div>

        @php
            $classJadwalUrl = $siswa->kelas?->jadwal_url;
        @endphp

        @if($classJadwalUrl)
            <div class="rounded-2xl overflow-hidden border border-gray-200 bg-gray-50 p-3 text-center max-w-lg mx-auto">
                <img 
                    src="{{ $classJadwalUrl }}" 
                    alt="Jadwal Kelas {{ $siswa->kelas->nama_kelas }}" 
                    class="max-h-72 w-auto mx-auto rounded-xl object-contain shadow-sm cursor-pointer hover:opacity-95 transition-opacity"
                    @click="modalOpen = true; modalImg = '{{ $classJadwalUrl }}'"
                >
                <p class="text-xs text-gray-400 mt-2">Klik foto untuk melihat dalam resolusi penuh</p>
            </div>
        @else
            <div class="p-8 text-center rounded-2xl border border-dashed border-gray-200 bg-gray-50/50">
                <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <p class="text-sm font-semibold text-gray-600">Belum ada foto jadwal untuk kelas ini</p>
                <p class="text-xs text-gray-400 mt-0.5">Admin dapat mengunggah foto jadwal kelas di menu Kelola Jadwal.</p>
            </div>
        @endif
    </div>

    <!-- Modal Zoom Foto Interaktif -->
    <div 
        x-show="modalOpen" 
        style="display: none;"
        class="fixed inset-0 z-50 bg-gray-900/80 backdrop-blur-sm flex items-center justify-center p-4"
        @click.self="modalOpen = false"
    >
        <div class="relative max-w-4xl w-full bg-white rounded-2xl shadow-2xl overflow-hidden p-2">
            <div class="flex items-center justify-between p-3 border-b border-gray-100">
                <h4 class="font-bold text-gray-800 text-sm">Foto Jadwal Kelas</h4>
                <button @click="modalOpen = false" class="p-1 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-4 text-center max-h-[80vh] overflow-auto">
                <img :src="modalImg" alt="Jadwal Zoom" class="mx-auto max-h-[70vh] object-contain rounded-lg">
            </div>
        </div>
    </div>

</div>
@endsection
