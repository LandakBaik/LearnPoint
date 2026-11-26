<?php

use App\Models\Ujian;
use App\Services\NotifikasiSchedulerService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    Ujian::query()
        ->whereNotNull('waktu_mulai')
        ->whereNotNull('deadline')
        ->whereIn('status', ['scheduled', 'published'])
        ->get()
        ->each(function ($ujian) {
            $ujian->sinkronisasiStatus();
        });
})->everyMinute();

// Pengingat deadline tugas (H-24 jam & H-3 jam) untuk siswa yang belum mengumpulkan
Schedule::call(function () {
    app(NotifikasiSchedulerService::class)->kirimPengingatDeadlineTugas();
})->everyThirtyMinutes()->name('notifikasi:pengingat-deadline');

// Pengingat jadwal ujian & kuis (30 menit sebelum mulai)
Schedule::call(function () {
    app(NotifikasiSchedulerService::class)->kirimPengingatJadwalUjianDanKuis();
})->everyTenMinutes()->name('notifikasi:pengingat-jadwal');

// Pengingat koreksi harian untuk guru
Schedule::call(function () {
    app(NotifikasiSchedulerService::class)->kirimPengingatKoreksiGuru();
})->dailyAt('08:00')->name('notifikasi:pengingat-koreksi');

// Auto-pruning notifikasi yang sudah dibaca > 30 hari
Schedule::call(function () {
    app(NotifikasiSchedulerService::class)->pruneNotifikasiLama(30);
})->weeklyOn(0, '02:00')->name('notifikasi:prune');

Artisan::command('notifikasi:run-reminders', function (NotifikasiSchedulerService $scheduler) {
    $deadline = $scheduler->kirimPengingatDeadlineTugas();
    $jadwal = $scheduler->kirimPengingatJadwalUjianDanKuis();
    $koreksi = $scheduler->kirimPengingatKoreksiGuru();
    $this->info("Pengingat terkirim — Deadline Tugas: {$deadline}, Jadwal Ujian/Kuis: {$jadwal}, Koreksi Guru: {$koreksi}.");
})->purpose('Jalankan seluruh pengingat notifikasi otomatis (deadline, jadwal ujian/kuis, koreksi guru).');

Artisan::command('notifikasi:prune {--days=30}', function (NotifikasiSchedulerService $scheduler) {
    $days = (int) $this->option('days');
    $deleted = $scheduler->pruneNotifikasiLama($days);
    $this->info("{$deleted} notifikasi lama (> {$days} hari & sudah dibaca) berhasil dibersihkan.");
})->purpose('Bersihkan riwayat notifikasi lama yang sudah dibaca.');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
