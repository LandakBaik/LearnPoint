<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\MateriController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\TugasController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Guest Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Notifikasi Route
    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');

    // Main Dashboard Router
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Dashboard Admin / Operator
    Route::middleware(['role:operator,admin'])->group(function () {
        Route::get('/operator/dashboard', [DashboardController::class, 'operatorDashboard'])->name('operator.dashboard');
        Route::get('/admin/dashboard', [DashboardController::class, 'operatorDashboard'])->name('admin.dashboard');
        Route::resource('siswa', SiswaController::class);
    });

    // Dashboard Guru
    Route::middleware(['role:guru'])->group(function () {
        Route::get('/guru/dashboard', [DashboardController::class, 'guruDashboard'])->name('guru.dashboard');
        Route::get('/guru/materi', [MateriController::class, 'index'])->name('guru.materi');
        Route::get('/guru/tugas', [TugasController::class, 'index'])->name('guru.tugas');
        Route::get('/guru/jadwal', [JadwalController::class, 'index'])->name('guru.jadwal');
    });

    // Dashboard Siswa
    Route::middleware(['role:siswa'])->group(function () {
        Route::get('/siswa/dashboard', [DashboardController::class, 'siswaDashboard'])->name('siswa.dashboard');
    });

    // Dashboard Kepala Sekolah
    Route::middleware(['role:kepala_sekolah'])->group(function () {
        Route::get('/kepala-sekolah/dashboard', [DashboardController::class, 'kepalaSekolahDashboard'])->name('kepala_sekolah.dashboard');
    });
});