<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Ujian;

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

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
