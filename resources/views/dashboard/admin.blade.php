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

    <!-- Stats Grid (Poin 1: Setiap Card Langsung Menuju Detail Fiturnya) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Card 1: Total Siswa -->
        <a href="{{ route('siswa.index') }}" class="group block bg-white p-6 rounded-2xl border border-gray-200 shadow-sm hover:shadow-lg hover:border-emerald-300 hover:-translate-y-1 transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider group-hover:text-emerald-600 transition-colors">Total Siswa</p>
                    <h3 class="text-3xl font-extrabold text-gray-900 mt-1">{{ $totalSiswa }}</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                <span>Terdaftar aktif</span>
                <span class="text-emerald-600 font-bold group-hover:translate-x-1 transition-transform inline-flex items-center gap-1">
                    Kelola Siswa &rarr;
                </span>
            </div>
        </a>

        <!-- Card 2: Total Guru -->
        <a href="{{ route('guru.index') }}" class="group block bg-white p-6 rounded-2xl border border-gray-200 shadow-sm hover:shadow-lg hover:border-indigo-300 hover:-translate-y-1 transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider group-hover:text-indigo-600 transition-colors">Total Guru</p>
                    <h3 class="text-3xl font-extrabold text-gray-900 mt-1">{{ $totalGuru }}</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                <span>Pengajar aktif</span>
                <span class="text-indigo-600 font-bold group-hover:translate-x-1 transition-transform inline-flex items-center gap-1">
                    Kelola Guru &rarr;
                </span>
            </div>
        </a>

        <!-- Card 3: Total Kelas -->
        <a href="{{ route('kelas.index') }}" class="group block bg-white p-6 rounded-2xl border border-gray-200 shadow-sm hover:shadow-lg hover:border-amber-300 hover:-translate-y-1 transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider group-hover:text-amber-600 transition-colors">Jumlah Kelas</p>
                    <h3 class="text-3xl font-extrabold text-gray-900 mt-1">{{ $totalKelas }}</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m3 0h1m-1-4h.01M9 16h.01M9 12h.01M9 8h.01M15 16h.01M15 12h.01M15 8h.01"/></svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                <span>Rombongan belajar</span>
                <span class="text-amber-600 font-bold group-hover:translate-x-1 transition-transform inline-flex items-center gap-1">
                    Kelola Kelas &rarr;
                </span>
            </div>
        </a>

        <!-- Card 4: Total Mapel -->
        <a href="{{ route('mapel.index') }}" class="group block bg-white p-6 rounded-2xl border border-gray-200 shadow-sm hover:shadow-lg hover:border-purple-300 hover:-translate-y-1 transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider group-hover:text-purple-600 transition-colors">Total Mapel</p>
                    <h3 class="text-3xl font-extrabold text-gray-900 mt-1">{{ $totalMapel }}</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-purple-100 text-purple-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                <span>Mata pelajaran aktif</span>
                <span class="text-purple-600 font-bold group-hover:translate-x-1 transition-transform inline-flex items-center gap-1">
                    Kelola Mapel &rarr;
                </span>
            </div>
        </a>
    </div>

    <!-- Charts Infografis Dashboard (Poin 2: Di Atas Aksi Cepat) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Chart 1: Komposisi Pengguna Sistem (Pie / Doughnut Chart) -->
        <div class="lg:col-span-5 bg-white p-6 rounded-2xl border border-gray-200 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-1">
                    <h4 class="text-base font-bold text-gray-900">Komposisi Pengguna</h4>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-700">Role Sistem</span>
                </div>
                <p class="text-xs text-gray-500">Perbandingan jumlah akun terdaftar berdasarkan peran sistem.</p>
            </div>

            <div class="py-4 relative flex items-center justify-center">
                <div class="w-56 h-56 sm:w-60 sm:h-60">
                    <canvas id="userRoleChart"></canvas>
                </div>
            </div>

            <div class="pt-3 border-t border-gray-100 grid grid-cols-2 gap-2 text-xs">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-emerald-500 shrink-0"></span>
                    <span class="text-gray-600">Siswa: <strong class="text-gray-900">{{ $userRoleCounts['siswa'] }}</strong></span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-indigo-600 shrink-0"></span>
                    <span class="text-gray-600">Guru: <strong class="text-gray-900">{{ $userRoleCounts['guru'] }}</strong></span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-purple-600 shrink-0"></span>
                    <span class="text-gray-600">Admin: <strong class="text-gray-900">{{ $userRoleCounts['operator'] }}</strong></span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-amber-500 shrink-0"></span>
                    <span class="text-gray-600">Kepsek: <strong class="text-gray-900">{{ $userRoleCounts['kepala_sekolah'] }}</strong></span>
                </div>
            </div>
        </div>

        <!-- Chart 2: Persebaran Siswa & Rombel per Tingkatan (Bar Chart) -->
        <div class="lg:col-span-7 bg-white p-6 rounded-2xl border border-gray-200 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-1">
                    <h4 class="text-base font-bold text-gray-900">Statistik Siswa & Rombel per Tingkat</h4>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">Jenjang Tingkat</span>
                </div>
                <p class="text-xs text-gray-500">Distribusi jumlah siswa aktif dan rombongan belajar di tiap tingkatan kelas.</p>
            </div>

            <div class="py-4 h-64 sm:h-72">
                <canvas id="tingkatanChart"></canvas>
            </div>

            <div class="pt-3 border-t border-gray-100 flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500">
                <span class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded bg-indigo-600"></span> Jumlah Siswa
                    <span class="w-3 h-3 rounded bg-amber-400 ml-2"></span> Jumlah Rombel
                </span>
                <a href="{{ route('kelas.index') }}" class="text-indigo-600 font-bold hover:underline">Lihat Rombel &rarr;</a>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
        <h4 class="text-base font-bold text-gray-900 mb-4">Aksi Cepat Admin</h4>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <a href="{{ route('users.index') }}" class="p-4 rounded-xl bg-gray-50 border border-gray-200 hover:bg-purple-50 hover:border-purple-300 transition-all group flex items-center gap-3">
                <div class="p-2.5 bg-purple-600 text-white rounded-xl group-hover:scale-105 transition-transform shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-800">Kelola Akun</p>
                    <p class="text-xs text-gray-500">Akses pengguna sistem</p>
                </div>
            </a>

            <a href="{{ route('guru.index') }}" class="p-4 rounded-xl bg-gray-50 border border-gray-200 hover:bg-indigo-50 hover:border-indigo-300 transition-all group flex items-center gap-3">
                <div class="p-2.5 bg-indigo-600 text-white rounded-xl group-hover:scale-105 transition-transform shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-800">Data Guru</p>
                    <p class="text-xs text-gray-500">Kelola pengajar & NIP</p>
                </div>
            </a>

            <a href="{{ route('siswa.index') }}" class="p-4 rounded-xl bg-gray-50 border border-gray-200 hover:bg-emerald-50 hover:border-emerald-300 transition-all group flex items-center gap-3">
                <div class="p-2.5 bg-emerald-600 text-white rounded-xl group-hover:scale-105 transition-transform shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-800">Data Siswa</p>
                    <p class="text-xs text-gray-500">Daftar siswa & kelas</p>
                </div>
            </a>

            <a href="{{ route('kelas.index') }}" class="p-4 rounded-xl bg-gray-50 border border-gray-200 hover:bg-amber-50 hover:border-amber-300 transition-all group flex items-center gap-3">
                <div class="p-2.5 bg-amber-600 text-white rounded-xl group-hover:scale-105 transition-transform shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m3 0h1m-1-4h.01M9 16h.01M9 12h.01M9 8h.01M15 16h.01M15 12h.01M15 8h.01"/></svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-800">Kelola Kelas</p>
                    <p class="text-xs text-gray-500">Kelas & wali kelas</p>
                </div>
            </a>

            <a href="{{ route('mapel.index') }}" class="p-4 rounded-xl bg-gray-50 border border-gray-200 hover:bg-rose-50 hover:border-rose-300 transition-all group flex items-center gap-3">
                <div class="p-2.5 bg-rose-600 text-white rounded-xl group-hover:scale-105 transition-transform shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-800">Mata Pelajaran</p>
                    <p class="text-xs text-gray-500">Daftar mapel & nilai KKM</p>
                </div>
            </a>

            <a href="{{ route('admin.jadwal.index') }}" class="p-4 rounded-xl bg-gray-50 border border-gray-200 hover:bg-blue-50 hover:border-blue-300 transition-all group flex items-center gap-3">
                <div class="p-2.5 bg-blue-600 text-white rounded-xl group-hover:scale-105 transition-transform shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-800">Jadwal (Foto)</p>
                    <p class="text-xs text-gray-500">Upload jadwal kelas & guru</p>
                </div>
            </a>
        </div>
    </div>
</div>

<!-- Chart.js CDN & Inisialisasi Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Inisialisasi Donut / Pie Chart Komposisi Pengguna
    const roleCtx = document.getElementById('userRoleChart').getContext('2d');
    new Chart(roleCtx, {
        type: 'doughnut',
        data: {
            labels: ['Siswa', 'Guru', 'Operator/Admin', 'Kepala Sekolah'],
            datasets: [{
                data: [
                    {{ $userRoleCounts['siswa'] }},
                    {{ $userRoleCounts['guru'] }},
                    {{ $userRoleCounts['operator'] }},
                    {{ $userRoleCounts['kepala_sekolah'] }}
                ],
                backgroundColor: [
                    '#10b981', // emerald
                    '#4f46e5', // indigo
                    '#9333ea', // purple
                    '#f59e0b'  // amber
                ],
                borderWidth: 3,
                borderColor: '#ffffff',
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const val = context.parsed || 0;
                            return ` ${label}: ${val} Akun`;
                        }
                    }
                }
            },
            cutout: '68%'
        }
    });

    // 2. Inisialisasi Bar Chart Persebaran Siswa & Rombel
    const tingkatanCtx = document.getElementById('tingkatanChart').getContext('2d');
    new Chart(tingkatanCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($tingkatanLabels) !!},
            datasets: [
                {
                    label: 'Jumlah Siswa',
                    data: {!! json_encode($siswaPerTingkat) !!},
                    backgroundColor: '#4f46e5',
                    borderRadius: 8,
                    barPercentage: 0.6,
                    categoryPercentage: 0.7
                },
                {
                    label: 'Jumlah Rombel (Kelas)',
                    data: {!! json_encode($kelasPerTingkat) !!},
                    backgroundColor: '#f59e0b',
                    borderRadius: 8,
                    barPercentage: 0.6,
                    categoryPercentage: 0.7
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    align: 'end',
                    labels: {
                        boxWidth: 12,
                        boxHeight: 12,
                        usePointStyle: true,
                        pointStyle: 'rectRounded',
                        font: {
                            size: 11,
                            family: "'Poppins', sans-serif"
                        }
                    }
                },
                tooltip: {
                    padding: 10,
                    cornerRadius: 8
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        font: {
                            size: 11
                        }
                    },
                    grid: {
                        color: '#f3f4f6'
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 11,
                            weight: '500'
                        }
                    }
                }
            }
        }
    });
});
</script>
@endsection
