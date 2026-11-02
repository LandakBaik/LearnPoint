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
                'judul' => 'Dasar Pemrograman',
                'deskripsi' => 'Buatlah program sederhana menggunakan bahasa pemrograman yang telah dipelajari. Sertakan penjelasan mengenai alur program.',
                'deadline' => Carbon::now()->addDays(7),
                'tipe' => 'upload',
                'file_lampiran' => null,
                'pengampu_kelas_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'judul' => 'Algoritma dan Logika',
                'deskripsi' => 'Kerjakan soal mengenai algoritma, flowchart, dan pseudocode. Kumpulkan jawaban dalam bentuk PDF.',
                'deadline' => Carbon::now()->addDays(5),
                'tipe' => 'upload',
                'file_lampiran' => null,
                'pengampu_kelas_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'judul' => 'Struktur Data',
                'deskripsi' => 'Buatlah implementasi struktur data dalam bahasa pemrograman pilihanmu. Sertakan dokumentasi kode.',
                'deadline' => Carbon::now()->addDays(10),
                'tipe' => 'upload',
                'file_lampiran' => null,
                'pengampu_kelas_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
        ]);
    }
}
