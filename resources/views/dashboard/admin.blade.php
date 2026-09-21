@extends('layouts.app')

@section('title', 'Dashboard Admin / Operator')

@section('content')
<div class="space-y-6">
    <!-- Welcome Header Banner -->
    <div class="bg-gradient-to-r from-purple-700 to-indigo-800 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="relative z-10">
            <span class="px-3 py-1 bg-white/20 rounded-full text-xs font-semibold uppercase tracking-wider">Halaman Admin</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold mt-3 mb-2">Selamat Datang, {{ auth()->user()->name }}! 👋</h2>
            <p class="text-purple-100 text-sm sm:text-base max-w-2xl">
                Anda memiliki akses penuh untuk mengelola pengguna, kelas, mata pelajaran, dan konfigurasi sistem e-learning LearnPoint.
            </p>
        </div>
        <!-- Decorative Circle Backgrounds -->
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/10 rounded-full blur-2xl"></div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Card Total Siswa -->
        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Siswa</p>
                    <h3 class="text-3xl font-extrabold text-gray-900 mt-1">{{ $totalSiswa }}</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                <span>Terdaftar aktif</span>
                <a href="{{ route('siswa.index') }}" class="text-indigo-600 font-semibold hover:underline">Kelola Siswa &rarr;</a>
            </div>
        </div>

        <!-- Card Total Guru -->
        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Guru</p>
                    <h3 class="text-3xl font-extrabold text-gray-900 mt-1">{{ $totalGuru }}</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                <span>Pengajar aktif</span>
                <span class="text-gray-400 font-medium">Terverifikasi</span>
            </div>
        </div>

        <!-- Card Total Kelas -->
        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Jumlah Kelas</p>
                    <h3 class="text-3xl font-extrabold text-gray-900 mt-1">{{ $totalKelas }}</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m3 0h1m-1-4h.01M9 16h.01M9 12h.01M9 8h.01M15 16h.01M15 12h.01M15 8h.01"/></svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                <span>Rombongan belajar</span>
                <span class="text-gray-400 font-medium">Tingkat SMP</span>
            </div>
        </div>

        <!-- Card Total Pengguna -->
        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total User</p>
                    <h3 class="text-3xl font-extrabold text-gray-900 mt-1">{{ $totalUser }}</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-purple-100 text-purple-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                <span>Seluruh akun sistem</span>
                <span class="text-purple-600 font-semibold">Aktif</span>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
        <h4 class="text-base font-bold text-gray-900 mb-4">Aksi Cepat Admin</h4>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <a href="{{ route('siswa.create') }}" class="p-4 rounded-xl bg-gray-50 border border-gray-200 hover:bg-indigo-50 hover:border-indigo-300 transition-all group flex items-center gap-3">
                <div class="p-2 bg-indigo-600 text-white rounded-lg group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-800">Tambah Siswa Baru</p>
                    <p class="text-xs text-gray-500">Daftarkan data siswa ke sistem</p>
                </div>
            </a>
            <a href="{{ route('siswa.index') }}" class="p-4 rounded-xl bg-gray-50 border border-gray-200 hover:bg-emerald-50 hover:border-emerald-300 transition-all group flex items-center gap-3">
                <div class="p-2 bg-emerald-600 text-white rounded-lg group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-800">Lihat Daftar Siswa</p>
                    <p class="text-xs text-gray-500">Kelola dan update data siswa</p>
                </div>
            </a>
            <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 flex items-center gap-3 opacity-75">
                <div class="p-2 bg-purple-600 text-white rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-800">Pengaturan Sistem</p>
                    <p class="text-xs text-gray-500">Akses konfigurasi e-learning</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
