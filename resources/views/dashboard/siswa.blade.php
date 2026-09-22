@extends('layouts.app')

@section('title', 'Dashboard Siswa')

@section('content')
<div class="space-y-6">
    <!-- Welcome Header Banner -->
    <div class="bg-gradient-to-r from-emerald-600 to-teal-700 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="relative z-10">
            <span class="px-3 py-1 bg-white/20 rounded-full text-xs font-semibold uppercase tracking-wider">Halaman Siswa</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold mt-3 mb-2">Selamat Belajar, {{ auth()->user()->name }}! 🎓</h2>
            <p class="text-emerald-100 text-sm sm:text-base max-w-2xl">
                Pantau jadwal pelajaran, unduh materi belajar, kerjakan kuis, dan lihat pencapaian nilai kamu di sini.
            </p>
        </div>
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/10 rounded-full blur-2xl"></div>
    </div>

    <!-- Info Detail Siswa -->
    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 font-extrabold text-xl shadow-inner">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-900">{{ auth()->user()->name }}</h3>
                <p class="text-xs text-gray-500 font-medium">
                    NIS: <span class="font-bold text-gray-700">{{ $siswa->nis ?? 'Belum diatur' }}</span> | 
                    Kelas: <span class="font-bold text-gray-700">{{ $siswa->kelas->nama_kelas ?? 'Belum ditentukan' }}</span>
                </p>
            </div>
        </div>
        <span class="px-3 py-1 bg-emerald-100 text-emerald-700 font-semibold text-xs rounded-full border border-emerald-200">
            Status Siswa Aktif
        </span>
    </div>

    <!-- Overview Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Mata Pelajaran</p>
                    <h3 class="text-3xl font-extrabold text-gray-900 mt-1">{{ $totalMapel }}</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-teal-100 text-teal-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Tugas Pending</p>
                    <h3 class="text-3xl font-extrabold text-gray-900 mt-1">0</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Nilai Rata-Rata</p>
                    <h3 class="text-3xl font-extrabold text-emerald-600 mt-1">-</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Guru Pengampu Mata Pelajaran di Kelas Saya (Poin 7) -->
    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h4 class="text-base font-bold text-gray-900">Guru Pengampu Mata Pelajaran</h4>
                <p class="text-xs text-gray-500 mt-0.5">
                    Daftar bapak/ibu guru pengampu pelajaran untuk rombel <strong class="text-emerald-700">{{ $siswa->kelas->nama_kelas ?? 'Kelas Belum Ditentukan' }}</strong>.
                </p>
            </div>
            @if($siswa && $siswa->kelas)
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Tingkat {{ $siswa->kelas->tingkatan }}
                </span>
            @endif
        </div>

        @if(isset($guruPengampus) && $guruPengampus->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($guruPengampus as $gp)
                    <div class="p-4 rounded-xl bg-gray-50/70 border border-gray-200/80 hover:border-emerald-300 hover:bg-emerald-50/20 transition-all flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-600 text-white font-bold text-xs flex items-center justify-center shadow-sm shrink-0">
                            {{ strtoupper(substr($gp->guru->nama ?? 'G', 0, 2)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase tracking-wider mb-1">
                                {{ $gp->mapel->nama_mapel ?? 'Mapel' }}
                            </span>
                            <h5 class="text-sm font-bold text-gray-900 truncate leading-tight">{{ $gp->guru->nama ?? 'Belum Ditentukan' }}</h5>
                            <p class="text-xs text-gray-400 font-mono mt-0.5">NIP: {{ $gp->guru->nip ?? '-' }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-6 text-center rounded-xl border border-dashed border-gray-200 bg-gray-50/50">
                <p class="text-sm font-semibold text-gray-600">Belum ada guru pengampu yang terdaftar untuk kelas Anda</p>
                <p class="text-xs text-gray-400 mt-1">Administrator sekolah sedang mengatur jadwal dan pengampu mata pelajaran.</p>
            </div>
        @endif
    </div>

    <!-- Quick Features for Siswa -->
    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
        <h4 class="text-base font-bold text-gray-900 mb-4">Aktivitas Siswa</h4>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 hover:bg-emerald-50 transition-all cursor-pointer">
                <div class="w-10 h-10 bg-emerald-600 text-white rounded-lg flex items-center justify-center mb-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <h5 class="text-sm font-bold text-gray-800">Materi Pembelajaran</h5>
                <p class="text-xs text-gray-500 mt-1">Buka dan pelajari materi sekolah</p>
            </div>
            <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 hover:bg-teal-50 transition-all cursor-pointer">
                <div class="w-10 h-10 bg-teal-600 text-white rounded-lg flex items-center justify-center mb-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h5 class="text-sm font-bold text-gray-800">Tugas & Kuis Saya</h5>
                <p class="text-xs text-gray-500 mt-1">Kumpulkan tugas tepat waktu</p>
            </div>
            <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 hover:bg-amber-50 transition-all cursor-pointer">
                <div class="w-10 h-10 bg-amber-600 text-white rounded-lg flex items-center justify-center mb-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/></svg>
                </div>
                <h5 class="text-sm font-bold text-gray-800">Hasil & Rekap Nilai</h5>
                <p class="text-xs text-gray-500 mt-1">Cek perkembangan akademik kamu</p>
            </div>
        </div>
    </div>
</div>
@endsection
