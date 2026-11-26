<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\GachaBannerController;
use App\Http\Controllers\Api\JadwalController;
use App\Http\Controllers\Api\MateriController;
use App\Http\Controllers\Api\NotifikasiController;
use App\Http\Controllers\Api\PowerController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\RelokasiAktivitasController;
use App\Http\Controllers\Api\TugasController;
use App\Http\Controllers\Api\UjianController;
use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Mobile Version
|--------------------------------------------------------------------------
| Endpoints untuk integrasi aplikasi mobile Flutter (Siswa & Guru).
*/

// Public Authentication Route
Route::post('/login', [AuthController::class, 'login']);

// Authenticated Routes (Sanctum)
Route::middleware(['auth:sanctum', 'active'])->group(function () {
    // Auth & Identity
    Route::get('/user', [AuthController::class, 'me']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/auth/check', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Berkas Privat Terautentikasi (Materi, Lampiran Tugas, Jawaban Siswa)
    Route::get('/file/materi/{materi}', [FileController::class, 'materi']);
    Route::get('/file/tugas/{tugas}', [FileController::class, 'tugas']);
    Route::get('/file/jawaban/{nilai}', [FileController::class, 'jawaban']);

    // Profile Management (Guru & Siswa)
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::post('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);

    // Notifikasi Interaktif
    Route::get('/notifikasi', [NotifikasiController::class, 'index']);
    Route::patch('/notifikasi/{notifikasi}/toggle', [NotifikasiController::class, 'toggleRead']);
    Route::post('/notifikasi/{notifikasi}/read', [NotifikasiController::class, 'markAsRead']);
    Route::post('/notifikasi/mark-all-read', [NotifikasiController::class, 'markAllRead']);
    Route::post('/notifikasi/tandai-semua-dibaca', [NotifikasiController::class, 'markAllRead']);
    Route::delete('/notifikasi/{notifikasi}', [NotifikasiController::class, 'destroy']);
    Route::delete('/notifikasi', [NotifikasiController::class, 'clearAll']);

    // ==========================================
    // Fitur Guru API
    // ==========================================
    Route::middleware(['role:guru'])->prefix('guru')->group(function () {
        // Dashboard Guru
        Route::get('/dashboard', [DashboardController::class, 'guruDashboard']);

        // Jadwal Mengajar Guru
        Route::get('/jadwal', [JadwalController::class, 'guruIndex']);

        // Materi Pembelajaran Guru
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
        Route::get('/tugas/{id}/kunci-jawaban', [TugasController::class, 'guruKunciJawaban']);
        Route::get('/tugas/{id}/jawaban-siswa/{pengumpulanId}', [TugasController::class, 'guruDetailJawabanSiswa']);
        Route::post('/tugas', [TugasController::class, 'guruStore']);
        Route::post('/tugas/{id}', [TugasController::class, 'guruUpdate']); // Support multipart/form-data
        Route::put('/tugas/{id}', [TugasController::class, 'guruUpdate']);
        Route::delete('/tugas/{id}', [TugasController::class, 'guruDestroy']);
        Route::post('/tugas/{id}/nilai', [TugasController::class, 'guruBerikanNilai']);
        Route::post('/pengumpulan/{id}/nilai', [TugasController::class, 'guruBerikanNilai']); // Alias sesuai route web

        // Kuis Petualangan Guru
        Route::get('/kuis', [QuizController::class, 'guruIndex']);
        Route::get('/kuis/options', [QuizController::class, 'guruOptions']);
        Route::post('/kuis/bulk-destroy', [QuizController::class, 'guruBulkDestroy']);
        Route::get('/kuis/{quiz}', [QuizController::class, 'guruShow']);
        Route::post('/kuis', [QuizController::class, 'guruStore']);
        Route::put('/kuis/{quiz}', [QuizController::class, 'guruUpdate']);
        Route::delete('/kuis/{quiz}', [QuizController::class, 'guruDestroy']);
        Route::patch('/kuis/{quiz}/toggle-status', [QuizController::class, 'guruToggleStatus']);
        Route::post('/kuis/{quiz}/toggle-status', [QuizController::class, 'guruToggleStatus']);

        // Butir Soal Kuis Guru
        Route::post('/kuis/{quiz}/soal', [QuizController::class, 'guruStoreSoal']);
        Route::post('/kuis/{quiz}/soal/{soal}', [QuizController::class, 'guruUpdateSoal']); // Support multipart/form-data
        Route::put('/kuis/{quiz}/soal/{soal}', [QuizController::class, 'guruUpdateSoal']);
        Route::delete('/kuis/{quiz}/soal/{soal}', [QuizController::class, 'guruDestroySoal']);

        // Ujian Guru
        Route::get('/ujian', [UjianController::class, 'guruIndex']);
        Route::get('/ujian/options', [UjianController::class, 'guruOptions']);
        Route::get('/ujian/status-realtime', [UjianController::class, 'guruStatusRealtime']);
        Route::get('/ujian-laporan', [UjianController::class, 'guruLaporan']);
        Route::get('/ujian/{ujian}', [UjianController::class, 'guruShow']);
        Route::post('/ujian', [UjianController::class, 'guruStore']);
        Route::put('/ujian/{ujian}', [UjianController::class, 'guruUpdate']);
        Route::post('/ujian/{ujian}', [UjianController::class, 'guruUpdate']); // Support multipart/form-data
        Route::delete('/ujian/{ujian}', [UjianController::class, 'guruDestroy']);
        Route::post('/ujian/{ujian}/publish', [UjianController::class, 'guruPublish']);
        Route::post('/ujian/{ujian}/schedule', [UjianController::class, 'guruSchedule']);
        Route::post('/ujian/{ujian}/unpublish', [UjianController::class, 'guruUnpublish']);
        Route::post('/ujian/{ujian}/duplicate', [UjianController::class, 'guruDuplicate']);
        Route::post('/ujian/{ujian}/toggle-archive', [UjianController::class, 'guruToggleArchive']);

        // Monitoring Live & Sesi Token Ujian Guru
        Route::get('/ujian/{ujian}/monitoring', [UjianController::class, 'guruMonitoringData']);
        Route::get('/ujian/{ujian}/monitoring/data', [UjianController::class, 'guruMonitoringData']);
        Route::post('/ujian/{ujian}/peserta/{seleksi}/unlock', [UjianController::class, 'guruUnlockPeserta']);
        Route::post('/ujian/{ujian}/peserta/{seleksi}/force-submit', [UjianController::class, 'guruForceSubmit']);
        Route::post('/ujian/{ujian}/peserta/{seleksi}/reopen', [UjianController::class, 'guruReopenPeserta']);
        Route::post('/ujian/{ujian}/reset-token', [UjianController::class, 'guruResetToken']);
        Route::post('/ujian/{ujian}/atur-sesi', [UjianController::class, 'guruAturSesiKelas']);
        Route::post('/ujian/{ujian}/refresh-unlock-code', [UjianController::class, 'guruRefreshUnlockCode']);

        // Rekap Nilai, Koreksi Essay & Remidi/Susulan Ujian Guru
        Route::get('/ujian/{ujian}/rekap', [UjianController::class, 'guruRekap']);
        Route::get('/ujian/{ujian}/koreksi-essay', [UjianController::class, 'guruKoreksiEssay']);
        Route::post('/ujian/{ujian}/simpan-nilai-essay', [UjianController::class, 'guruSimpanNilaiEssay']);
        Route::get('/ujian/{ujian}/peserta/{siswa}/jawaban', [UjianController::class, 'guruDetailJawabanSiswa']);
        Route::post('/ujian/{ujian}/buat-remidi-susulan', [UjianController::class, 'guruBuatRemidiSusulan']);

        // Pusat Relokasi / Duplikasi Aktivitas Guru
        Route::get('/relokasi', [RelokasiAktivitasController::class, 'guruIndex']);
        Route::post('/relokasi', [RelokasiAktivitasController::class, 'guruRelocate']);
    });

    // ==========================================
    // Fitur Siswa API
    // ==========================================
    Route::middleware(['role:siswa'])->prefix('siswa')->group(function () {
        // Dashboard Siswa
        Route::get('/dashboard', [DashboardController::class, 'siswaDashboard']);
        Route::get('/beranda', [DashboardController::class, 'siswaDashboard']);

        // Jadwal Kelas Siswa
        Route::get('/jadwal', [JadwalController::class, 'siswaIndex']);

        // Mata Pelajaran & Materi Pembelajaran Siswa
        Route::get('/mapel', [MateriController::class, 'siswaIndex']);
        Route::get('/mapel/{mapelId}', [MateriController::class, 'siswaShow']);

        // Tugas Siswa
        Route::get('/tugas', [TugasController::class, 'siswaIndex']);
        Route::get('/tugas/{id}', [TugasController::class, 'siswaShow']);
        Route::post('/tugas/{id}/kumpulkan', [TugasController::class, 'siswaKumpulkan']);

        // Kuis Petualangan Siswa
        Route::get('/kuis', [QuizController::class, 'siswaIndex']);
        Route::get('/kuis/panduan', [QuizController::class, 'siswaPanduan']);
        Route::get('/kuis/{quiz}', [QuizController::class, 'siswaShow']);
        Route::get('/kuis/{quiz}/kerjakan', [QuizController::class, 'siswaKerjakan']);
        Route::post('/kuis/{quiz}/simpan-draft', [QuizController::class, 'siswaSimpanDraft']);
        Route::post('/kuis/{quiz}/submit', [QuizController::class, 'siswaSubmit']);
        Route::post('/kuis/{quiz}/selesaikan', [QuizController::class, 'siswaSubmit']);
        Route::post('/kuis/{quiz}/kerjakan', [QuizController::class, 'siswaSubmit']); // Aliasing
        Route::get('/kuis/{quiz}/hasil', [QuizController::class, 'siswaHasil']);
        Route::get('/kuis/{quiz}/hasil-petualangan', [QuizController::class, 'siswaHasilPetualangan']);

        // Sistem Power Kuis Petualangan Siswa
        Route::get('/kuis/{quiz}/powers', [PowerController::class, 'index']);
        Route::post('/kuis/{quiz}/powers/use', [PowerController::class, 'usePower']);
        Route::post('/kuis/{quiz}/powers/second-chance', [PowerController::class, 'verifySecondChance']);

        // Gacha Power & Banner Siswa
        Route::get('/gacha', [GachaBannerController::class, 'status']);
        Route::get('/gacha/status', [GachaBannerController::class, 'status']);
        Route::get('/gacha/panduan', [GachaBannerController::class, 'panduan']);
        Route::post('/gacha/pull', [GachaBannerController::class, 'pull']);
        Route::post('/banner/equip', [GachaBannerController::class, 'equipBanner']);

        // Ujian Evaluasi Siswa (CBT)
        Route::get('/ujian', [UjianController::class, 'siswaIndex']);
        Route::get('/ujian/{ujian}', [UjianController::class, 'siswaShow']);
        Route::get('/ujian/{ujian}/konfirmasi', [UjianController::class, 'siswaKonfirmasi']);
        Route::post('/ujian/{ujian}/validasi-token', [UjianController::class, 'siswaValidasiToken']);
        Route::get('/ujian/{ujian}/kerjakan', [UjianController::class, 'siswaKerjakan']);
        Route::post('/ujian/{ujian}/simpan-jawaban', [UjianController::class, 'siswaSimpanJawaban']);
        Route::post('/ujian/{ujian}/submit', [UjianController::class, 'siswaSubmit']);
        Route::get('/ujian/{ujian}/hasil', [UjianController::class, 'siswaHasil']);
        Route::post('/ujian/{ujian}/pelanggaran', [UjianController::class, 'siswaCatatPelanggaran']);
        Route::get('/ujian/{ujian}/status-sesi', [UjianController::class, 'siswaCekStatusSesi']);
        Route::post('/ujian/{ujian}/buka-kunci-kode', [UjianController::class, 'siswaBukaKunciDenganKode']);
    });
});
