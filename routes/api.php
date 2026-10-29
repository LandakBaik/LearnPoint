<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\JadwalController;
use App\Http\Controllers\Api\MateriController;
use App\Http\Controllers\Api\NotifikasiController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\TugasController;
use App\Http\Controllers\Api\UjianController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Mobile Version
|--------------------------------------------------------------------------
*/

// Public Authentication Route
Route::post('/login', [AuthController::class, 'login']);

// Authenticated Routes (Sanctum)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/notifikasi', [NotifikasiController::class, 'index']);

    // ==========================================
    // Fitur Guru API
    // ==========================================
    Route::middleware(['role:guru'])->prefix('guru')->group(function () {
        // Dashboard Guru
        Route::get('/dashboard', [DashboardController::class, 'guruDashboard']);

        // Jadwal Mengajar Guru
        Route::get('/jadwal', [JadwalController::class, 'guruIndex']);

        // Materi Guru
        Route::get('/materi', [MateriController::class, 'guruIndex']);
        Route::get('/materi/options', [MateriController::class, 'guruOptions']);
        Route::get('/materi/{id_grub_materi}', [MateriController::class, 'guruShow']);
        Route::post('/materi', [MateriController::class, 'guruStore']);
        Route::post('/materi/{id_grub_materi}', [MateriController::class, 'guruUpdate']); // Support multipart/form-data
        Route::put('/materi/{id_grub_materi}', [MateriController::class, 'guruUpdate']);
        Route::delete('/materi/{id_grub_materi}', [MateriController::class, 'guruDestroy']);

        // Tugas Guru
        Route::get('/tugas', [TugasController::class, 'guruIndex']);
        Route::get('/tugas/options', [TugasController::class, 'guruOptions']);
        Route::get('/tugas/{id}', [TugasController::class, 'guruShow']);
        Route::post('/tugas', [TugasController::class, 'guruStore']);
        Route::post('/tugas/{id}', [TugasController::class, 'guruUpdate']); // Support multipart/form-data
        Route::put('/tugas/{id}', [TugasController::class, 'guruUpdate']);
        Route::delete('/tugas/{id}', [TugasController::class, 'guruDestroy']);
        Route::post('/tugas/{id}/nilai', [TugasController::class, 'guruBerikanNilai']);

        // Kuis Guru
        Route::get('/kuis', [QuizController::class, 'guruIndex']);
        Route::get('/kuis/options', [QuizController::class, 'guruOptions']);
        Route::post('/kuis/bulk-destroy', [QuizController::class, 'guruBulkDestroy']);
        Route::get('/kuis/{quiz}', [QuizController::class, 'guruShow']);
        Route::post('/kuis', [QuizController::class, 'guruStore']);
        Route::put('/kuis/{quiz}', [QuizController::class, 'guruUpdate']);
        Route::delete('/kuis/{quiz}', [QuizController::class, 'guruDestroy']);

        // Butir Soal Kuis Guru
        Route::post('/kuis/{quiz}/soal', [QuizController::class, 'guruStoreSoal']);
        Route::post('/kuis/{quiz}/soal/{soal}', [QuizController::class, 'guruUpdateSoal']); // Support multipart/form-data
        Route::put('/kuis/{quiz}/soal/{soal}', [QuizController::class, 'guruUpdateSoal']);
        Route::delete('/kuis/{quiz}/soal/{soal}', [QuizController::class, 'guruDestroySoal']);

        // Ujian Guru
        Route::get('/ujian', [UjianController::class, 'guruIndex']);
        Route::get('/ujian/options', [UjianController::class, 'guruOptions']);
        Route::get('/ujian/{ujian}', [UjianController::class, 'guruShow']);
        Route::post('/ujian', [UjianController::class, 'guruStore']);
        Route::put('/ujian/{ujian}', [UjianController::class, 'guruUpdate']);
        Route::delete('/ujian/{ujian}', [UjianController::class, 'guruDestroy']);
        Route::post('/ujian/{ujian}/publish', [UjianController::class, 'guruPublish']);
        Route::post('/ujian/{ujian}/schedule', [UjianController::class, 'guruSchedule']);
    });

    // ==========================================
    // Fitur Siswa API
    // ==========================================
    Route::middleware(['role:siswa'])->prefix('siswa')->group(function () {
        // Dashboard Siswa
        Route::get('/dashboard', [DashboardController::class, 'siswaDashboard']);

        // Jadwal Kelas Siswa
        Route::get('/jadwal', [JadwalController::class, 'siswaIndex']);

        // Materi Siswa
        Route::get('/materi', [MateriController::class, 'siswaIndex']);
        Route::get('/materi/{mapelId}', [MateriController::class, 'siswaShow']);

        // Tugas Siswa
       
        Route::get('/tugas/{id}', [TugasController::class, 'siswaShow']);
        Route::post('/tugas/{id}/kumpulkan', [TugasController::class, 'siswaKumpulkan']);

        // Kuis Siswa
        Route::get('/kuis', [QuizController::class, 'siswaIndex']);
        Route::get('/kuis/{quiz}', [QuizController::class, 'siswaShow']);
        Route::post('/kuis/{quiz}/kerjakan', [QuizController::class, 'siswaKerjakan']);

        // Ujian Siswa
        Route::get('/ujian', [UjianController::class, 'siswaIndex']);
        Route::get('/ujian/{ujian}', [UjianController::class, 'siswaShow']);
    });
    
});
Route::get('/siswa/tugas', [TugasController::class, 'siswaIndex']);
