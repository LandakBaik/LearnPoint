<?php

use Illuminate\Support\Facades\Route;

// Shared & Auth Controllers
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\ProfileController;

// Admin / Operator Controllers
use App\Http\Controllers\Admin\GuruController as AdminGuruController;
use App\Http\Controllers\Admin\GuruMapelController as AdminGuruMapelController;
use App\Http\Controllers\Admin\JadwalController as AdminJadwalController;
use App\Http\Controllers\Admin\KelasController as AdminKelasController;
use App\Http\Controllers\Admin\MapelController as AdminMapelController;
use App\Http\Controllers\Admin\SiswaController as AdminSiswaController;
use App\Http\Controllers\Admin\UserController as AdminUserController;

// Guru Controllers
use App\Http\Controllers\Guru\JadwalController as GuruJadwalController;
use App\Http\Controllers\Guru\MateriController as GuruMateriController;
use App\Http\Controllers\Guru\QuizController as GuruQuizController;
use App\Http\Controllers\Guru\TugasController as GuruTugasController;
use App\Http\Controllers\Guru\UjianController as GuruUjianController;

// Siswa Controllers
use App\Http\Controllers\Siswa\GachaBannerController as SiswaGachaBannerController;
use App\Http\Controllers\Siswa\MateriController as SiswaMateriController;
use App\Http\Controllers\Siswa\PowerController as SiswaPowerController;
use App\Http\Controllers\Siswa\QuizController as SiswaQuizController;
use App\Http\Controllers\Siswa\TugasController as SiswaTugasController;
use App\Http\Controllers\Siswa\UjianController as SiswaUjianController;

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

    // Notifikasi Routes
    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::get('/notifikasi/{notifikasi}/baca', [NotifikasiController::class, 'readAndRedirect'])->name('notifikasi.read');
    Route::patch('/notifikasi/{notifikasi}/toggle', [NotifikasiController::class, 'toggleRead'])->name('notifikasi.toggle');
    Route::post('/notifikasi/tandai-semua-dibaca', [NotifikasiController::class, 'markAllRead'])->name('notifikasi.mark-all-read');
    Route::delete('/notifikasi/{notifikasi}', [NotifikasiController::class, 'destroy'])->name('notifikasi.destroy');
    Route::delete('/notifikasi-semua', [NotifikasiController::class, 'clearAll'])->name('notifikasi.clear-all');

    // Main Dashboard Router
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ==========================================
    // Fitur Guru
    // ==========================================
    Route::middleware(['role:guru'])->group(function () {
        Route::get('/guru/dashboard', [DashboardController::class, 'guruDashboard'])->name('guru.dashboard');
        Route::get('/guru/materi', [GuruMateriController::class, 'index'])->name('guru.materi');
        Route::get('/guru/materi/create', [GuruMateriController::class, 'create'])->name('guru.materi.create');
        Route::post('/guru/materi', [GuruMateriController::class, 'store'])->name('guru.materi.store');
        Route::get('/guru/materi/{id_grub_materi}/edit', [GuruMateriController::class, 'edit'])->name('guru.materi.edit');
        Route::put('/guru/materi/{id_grub_materi}', [GuruMateriController::class, 'update'])->name('guru.materi.update');
        Route::delete('/guru/materi/{id_grub_materi}', [GuruMateriController::class, 'destroy'])->name('guru.materi.destroy');
        Route::get('/guru/tugas', [GuruTugasController::class, 'index'])->name('guru.tugas');

        // Halaman tambah tugas
        Route::post('/guru/tugas', [GuruTugasController::class, 'store'])
            ->name('guru.tugas.store');
        Route::get('/guru/tugas/{id}', [GuruTugasController::class, 'show'])
            ->name('guru.tugas.show');

        // Simpan / perbarui nilai siswa
        Route::post('/guru/pengumpulan/{id}/nilai', [GuruTugasController::class, 'berikanNilai'])
            ->name('guru.pengumpulan.nilai');
        Route::put('/guru/tugas/{id}', [GuruTugasController::class, 'update'])
            ->name('guru.tugas.update');
        Route::delete('/guru/tugas/{id}', [GuruTugasController::class, 'destroy'])
            ->name('guru.tugas.destroy');
        // Halaman kunci jawaban pilihan ganda
        Route::get('/guru/tugas/{id}/kunci-jawaban', [GuruTugasController::class, 'kunciJawaban'])
            ->name('guru.tugas.kunci-jawaban');

        // Halaman detail jawaban siswa
        Route::get('/guru/tugas/{id}/jawaban-siswa/{pengumpulanId}', [GuruTugasController::class, 'detailJawabanSiswa'])
            ->name('guru.tugas.detail-jawaban-siswa');
            
        Route::get('/guru/jadwal', [GuruJadwalController::class, 'index'])
            ->name('guru.jadwal');

        // Daftar semua ujian
        Route::get('/guru/ujian', [GuruUjianController::class, 'index'])
            ->name('guru.ujian.index');

        // Halaman tambah ujian
        Route::get('/guru/ujian/create', [GuruUjianController::class, 'create'])
            ->name('guru.ujian.create');

        // Simpan ujian baru
        Route::post('/guru/ujian', [GuruUjianController::class, 'store'])
            ->name('guru.ujian.store');

        Route::get('/guru/ujian/status-realtime', [GuruUjianController::class, 'statusRealtime'])
            ->name('guru.ujian.status-realtime');

        // Detail ujian
        Route::get('/guru/ujian/{ujian}', [GuruUjianController::class, 'show'])
            ->name('guru.ujian.show');

        // Halaman edit ujian
        Route::get('/guru/ujian/{ujian}/edit', [GuruUjianController::class, 'edit'])
            ->name('guru.ujian.edit');

        // Update ujian
        Route::put('/guru/ujian/{ujian}', [GuruUjianController::class, 'update'])
            ->name('guru.ujian.update');

        // Hapus ujian
        Route::delete('/guru/ujian/{ujian}', [GuruUjianController::class, 'destroy'])
            ->name('guru.ujian.destroy');

        // Publikasi ujian sekarang
        Route::post('/guru/ujian/{ujian}/publish', [GuruUjianController::class, 'publish'])
            ->name('guru.ujian.publish');

        // Jadwalkan ujian
        Route::post('/guru/ujian/{ujian}/schedule', [GuruUjianController::class, 'schedule'])
            ->name('guru.ujian.schedule');


        // Kuis Guru
        Route::get('/guru/kuis', [GuruQuizController::class, 'index'])
            ->name('guru.kuis.index');
        Route::post('/guru/kuis', [GuruQuizController::class, 'store'])
            ->name('guru.kuis.store');
        Route::post('/guru/kuis/bulk-destroy', [GuruQuizController::class, 'bulkDestroy'])
            ->name('guru.kuis.bulk-destroy');
        Route::get('/guru/kuis/{quiz}', [GuruQuizController::class, 'show'])
            ->name('guru.kuis.show');
        Route::put('/guru/kuis/{quiz}', [GuruQuizController::class, 'update'])
            ->name('guru.kuis.update');
        Route::delete('/guru/kuis/{quiz}', [GuruQuizController::class, 'destroy'])
            ->name('guru.kuis.destroy');
        Route::patch('/guru/kuis/{quiz}/toggle-status', [GuruQuizController::class, 'toggleStatus'])
            ->name('guru.kuis.toggle-status');

        // Soal Kuis Guru
        Route::post('/guru/kuis/{quiz}/soal', [GuruQuizController::class, 'storeSoal'])
            ->name('guru.kuis.soal.store');
        Route::put('/guru/kuis/{quiz}/soal/{soal}', [GuruQuizController::class, 'updateSoal'])
            ->name('guru.kuis.soal.update');
        Route::delete('/guru/kuis/{quiz}/soal/{soal}', [GuruQuizController::class, 'destroySoal'])
            ->name('guru.kuis.soal.destroy');
    });

    // ==========================================
    // Fitur Siswa
    // ==========================================
    Route::middleware(['role:siswa'])->group(function () {
        Route::get('/siswa/dashboard', [DashboardController::class, 'siswaDashboard'])->name('siswa.dashboard');
        Route::get('/siswa/beranda', [DashboardController::class, 'siswaDashboard'])->name('siswa.beranda');
        Route::get('/siswa/materi', [SiswaMateriController::class, 'index'])->name('siswa.materi');
        Route::get('/siswa/materi/{id}', [SiswaMateriController::class, 'show'])->name('siswa.materi.show');
        Route::get('/siswa/tugas', [SiswaTugasController::class, 'index'])->name('siswa.tugas');
        Route::get('/siswa/tugas/{id}', [SiswaTugasController::class, 'show'])->name('siswa.tugas.show');
        Route::post('/siswa/tugas/{id}/kumpulkan', [SiswaTugasController::class, 'kumpulkanTugas'])->name('siswa.tugas.kumpulkan');
        Route::get('/siswa/jadwal', function () {
            return redirect()->route('siswa.dashboard', ['tab' => 'jadwal']);
        })->name('siswa.jadwal');

        // Kuis Petualangan Siswa
        Route::get('/siswa/kuis', [SiswaQuizController::class, 'index'])->name('siswa.kuis');
        Route::get('/siswa/kuis/{quiz}', [SiswaQuizController::class, 'show'])->name('siswa.kuis.show');
        Route::get('/siswa/kuis/{quiz}/kerjakan', [SiswaQuizController::class, 'kerjakan'])->name('siswa.kuis.kerjakan');
        Route::post('/siswa/kuis/{quiz}/simpan-draft', [SiswaQuizController::class, 'simpanDraft'])->name('siswa.kuis.simpan-draft');
        Route::post('/siswa/kuis/{quiz}/selesaikan', [SiswaQuizController::class, 'submit'])->name('siswa.kuis.submit');
        Route::get('/siswa/kuis/{quiz}/hasil', [SiswaQuizController::class, 'hasil'])->name('siswa.kuis.hasil');
        Route::get('/siswa/kuis/{quiz}/hasil-petualangan', [SiswaQuizController::class, 'hasilPetualangan'])->name('siswa.kuis.hasil-petualangan');

        // Sistem Power Kuis Petualangan Siswa
        Route::get('/siswa/kuis/{quiz}/powers', [SiswaPowerController::class, 'index'])->name('siswa.kuis.powers.index');
        Route::post('/siswa/kuis/{quiz}/powers/use', [SiswaPowerController::class, 'usePower'])->name('siswa.kuis.powers.use');
        Route::post('/siswa/kuis/{quiz}/powers/second-chance', [SiswaPowerController::class, 'verifySecondChance'])->name('siswa.kuis.powers.second-chance');

        // Sistem Gacha Power & Kustomisasi Banner Siswa
        Route::get('/siswa/gacha', [SiswaGachaBannerController::class, 'index'])->name('siswa.gacha');
        Route::post('/siswa/gacha/pull', [SiswaGachaBannerController::class, 'pull'])->name('siswa.gacha.pull');
        Route::post('/siswa/banner/equip', [SiswaGachaBannerController::class, 'equipBanner'])->name('siswa.banner.equip');

        Route::get('/siswa/ujian/{ujian}/kerjakan', [SiswaUjianController::class, 'kerjakan'])->name('siswa.ujian.kerjakan');
        Route::post('/siswa/ujian/{ujian}/simpan-jawaban', [SiswaUjianController::class, 'simpanJawaban'])->name('siswa.ujian.simpan-jawaban');

        // TAMBAHKAN 2 BARIS INI:
        Route::post('/siswa/ujian/{ujian}/submit', [SiswaUjianController::class, 'submit'])->name('siswa.ujian.submit');
        Route::get('/siswa/ujian/{ujian}/hasil', [SiswaUjianController::class, 'hasil'])->name('siswa.ujian.hasil');
    });

    // ==========================================
    // Fitur Admin / Operator
    // ==========================================
    Route::middleware(['role:operator,admin'])->group(function () {
        Route::get('/operator/dashboard', [DashboardController::class, 'operatorDashboard'])->name('operator.dashboard');
        Route::get('/admin/dashboard', [DashboardController::class, 'operatorDashboard'])->name('admin.dashboard');

        // User Toggle Status
        Route::patch('/users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::resource('users', AdminUserController::class)->except(['show']);

        // Guru CSV & Resource
        Route::get('/guru/template-csv', [AdminGuruController::class, 'downloadTemplateCsv'])->name('guru.template-csv');
        Route::post('/guru/import-csv', [AdminGuruController::class, 'importCsv'])->name('guru.import-csv');
        Route::patch('/guru/{guru}/toggle-status', [AdminGuruController::class, 'toggleStatus'])->name('guru.toggle-status');
        Route::resource('guru', AdminGuruController::class);

        // Siswa CSV & Resource
        Route::get('/siswa/template-csv', [AdminSiswaController::class, 'downloadTemplateCsv'])->name('siswa.template-csv');
        Route::post('/siswa/import-csv', [AdminSiswaController::class, 'importCsv'])->name('siswa.import-csv');
        Route::patch('/siswa/{siswa}/toggle-status', [AdminSiswaController::class, 'toggleStatus'])->name('siswa.toggle-status');
        Route::resource('siswa', AdminSiswaController::class);

        Route::resource('kelas', AdminKelasController::class);
        Route::resource('mapel', AdminMapelController::class);

        // Guru Mapel Assignment
        Route::post('/guru-mapel', [AdminGuruMapelController::class, 'store'])->name('guru-mapel.store');
        Route::delete('/guru-mapel/{guruMapel}', [AdminGuruMapelController::class, 'destroy'])->name('guru-mapel.destroy');

        // Jadwal (Foto)
        Route::get('/kelola-jadwal', [AdminJadwalController::class, 'adminIndex'])->name('admin.jadwal.index');
        Route::post('/kelola-jadwal/kelas', [AdminJadwalController::class, 'uploadJadwalKelas'])->name('admin.jadwal.upload_kelas');
        Route::post('/kelola-jadwal/guru', [AdminJadwalController::class, 'uploadJadwalGuru'])->name('admin.jadwal.upload_guru');
        Route::delete('/kelola-jadwal/kelas/{kelas}', [AdminJadwalController::class, 'destroyJadwalKelas'])->name('admin.jadwal.destroy_kelas');
        Route::delete('/kelola-jadwal/guru/{guruMapel}', [AdminJadwalController::class, 'destroyJadwalGuru'])->name('admin.jadwal.destroy_guru');
    });

    // ==========================================
    // Fitur Kepala Sekolah
    // ==========================================
    Route::middleware(['role:kepala_sekolah'])->group(function () {
        Route::get('/kepala-sekolah/dashboard', [DashboardController::class, 'kepalaSekolahDashboard'])->name('kepala_sekolah.dashboard');
    });
});
