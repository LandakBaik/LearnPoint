<?php

namespace Database\Seeders;

use App\Models\PengampuKelas;
use App\Models\Tugas;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class TugasSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tugases')->insert([
            [
                'judul' => 'Tugas 1 - Dasar Pemrograman',
                'deskripsi' => 'Buatlah program sederhana menggunakan bahasa pemrograman yang telah dipelajari. Sertakan penjelasan mengenai alur program.',
                'deadline' => Carbon::now()->addDays(7),
                'tipe' => 'upload',
                'file_lampiran' => null,
                'pengampu_kelas_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'judul' => 'Tugas 2 - Algoritma dan Logika',
                'deskripsi' => 'Kerjakan soal mengenai algoritma, flowchart, dan pseudocode. Kumpulkan jawaban dalam bentuk PDF.',
                'deadline' => Carbon::now()->addDays(5),
                'tipe' => 'upload',
                'file_lampiran' => null,
                'pengampu_kelas_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'judul' => 'Kuis Pemahaman Materi',
                'deskripsi' => 'Pilih jawaban yang paling tepat sesuai materi yang telah dipelajari di kelas.',
                'deadline' => Carbon::now()->addDays(3),
                'tipe' => 'pilihan_ganda',
                'file_lampiran' => null,
                'pengampu_kelas_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
        ]);
    }
}
