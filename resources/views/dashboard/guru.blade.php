@extends('layouts.app')

@section('title', 'Home Guru')

@section('content')
<div class="space-y-6">
    <!-- Greeting -->
    <h1>Halo {{ Auth::user()->name }}</h1>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-5">
        <!-- Card Total Kelas -->
        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Kelas yang Diajar</p>
                    <h3 class="text-3xl font-extrabold text-gray-900 mt-1">0</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
            </div>
        </div>

        <!-- Card Total Tugas Belum Dinilai -->
        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Jumlah Tugas Belum Dinilai</p>
                    <h3 class="text-3xl font-extrabold text-gray-900 mt-1">0</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m3 0h1m-1-4h.01M9 16h.01M9 12h.01M9 8h.01M15 16h.01M15 12h.01M15 8h.01"/></svg>
                </div>
            </div>
        </div>

        <!-- Card Total Kuis -->
        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Jumlah Kuis Sedang Berlangsung</p>
                    <h3 class="text-3xl font-extrabold text-gray-900 mt-1">0</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
            </div>
        </div>
        <!-- Card Total Mapel -->
        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Mata Pelajaran yang Diampu</p>
                    <h3 class="text-3xl font-extrabold text-gray-900 mt-1">0</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Dashboard Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Column: Perlu Ditindaklanjuti & Aktivitas Terbaru -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Section: Perlu Ditindaklanjuti -->
            <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Perlu Ditindaklanjuti</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Beberapa aktivitas yang perlu segera Anda tindak lanjuti.</p>
                    </div>
                    <a href="#" class="text-xs font-semibold text-blue-600 hover:text-blue-700 flex items-center gap-1 transition-colors">
                        Lihat Semua
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>

                <div class="py-10 text-center text-gray-500 bg-gray-50/50 rounded-xl border border-dashed border-gray-200">
                    <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-sm font-medium text-gray-600">Tidak ada aktivitas yang perlu ditindaklanjuti</p>
                    <p class="text-xs text-gray-400 mt-0.5">Semua tugas dan kuis siswa telah diperiksa.</p>
                </div>
            </div>

            <!-- Section: Aktivitas Terbaru -->
            <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-bold text-gray-900">Aktivitas Terbaru</h3>
                    <a href="#" class="text-xs font-semibold text-blue-600 hover:text-blue-700 flex items-center gap-1 transition-colors">
                        Lihat Semua
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>

                <div class="py-10 text-center text-gray-500 bg-gray-50/50 rounded-xl border border-dashed border-gray-200">
                    <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-sm font-medium text-gray-600">Belum ada aktivitas terbaru</p>
                    <p class="text-xs text-gray-400 mt-0.5">Riwayat aktivitas pembelajaran akan muncul di sini.</p>
                </div>
            </div>

        </div>

        <!-- Right Column: Jadwal Hari Ini & Aksi Cepat -->
        <div class="space-y-6">

            <!-- Section: Jadwal Hari Ini -->
            <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                <div class="mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Jadwal Hari Ini</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Jadwal pelajaran</p>
                </div>

                <div class="py-8 text-center text-gray-500 bg-gray-50/50 rounded-xl border border-dashed border-gray-200">
                    <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <p class="text-xs font-medium text-gray-600">Tidak ada jadwal pelajaran hari ini</p>
                </div>
            </div>

            <!-- Section: Aksi Cepat -->
            <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                <div class="mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Aksi Cepat</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Pintasan praktis untuk mengajar hari ini</p>
                </div>

                <div class="space-y-3">
                    <!-- Option 1: Buat Tugas Baru -->
                    <a href="/guru/tugas" class="p-4 rounded-xl border border-gray-200 bg-white hover:border-blue-300 hover:bg-blue-50/30 transition-all flex items-center justify-between group">
                        <div class="flex items-center gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 group-hover:bg-blue-100 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </div>
                            <span class="text-sm font-bold text-gray-800 group-hover:text-blue-600 transition-colors">+ Buat Tugas Baru</span>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-600 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>

                    <!-- Option 2: Unggah Materi -->
                    <a href="/guru/materi" class="p-4 rounded-xl border border-gray-200 bg-white hover:border-emerald-300 hover:bg-emerald-50/30 transition-all flex items-center justify-between group">
                        <div class="flex items-center gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 group-hover:bg-emerald-100 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            </div>
                            <span class="text-sm font-bold text-gray-800 group-hover:text-emerald-600 transition-colors">+ Unggah Materi</span>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 group-hover:text-emerald-600 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>

                    <!-- Option 3: Jadwalkan Kuis -->
                    <a href="/guru/kuis" class="p-4 rounded-xl border border-gray-200 bg-white hover:border-purple-300 hover:bg-purple-50/30 transition-all flex items-center justify-between group">
                        <div class="flex items-center gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0 group-hover:bg-purple-100 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <span class="text-sm font-bold text-gray-800 group-hover:text-purple-600 transition-colors">+ Jadwalkan Kuis</span>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 group-hover:text-purple-600 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
