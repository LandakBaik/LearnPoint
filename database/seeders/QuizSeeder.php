<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\GuruMapel;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\PengampuKelas;
use App\Models\Quiz;
use App\Models\Soal;
use Illuminate\Database\Seeder;

class QuizSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Dapatkan pengampu kelas untuk Kelas 7-A (Matematika & IPA)
        $kelas7A = Kelas::where('nama_kelas', '7-A')->first() ?? Kelas::first();
        if (!$kelas7A) {
            return;
        }

        $guruAmba = Guru::where('nama', 'Amba')->first() ?? Guru::first();

        // Cari atau buat PengampuKelas Matematika 7-A
        $mapelMtk = Mapel::where('nama_mapel', 'Matematika')->first();
        $gmMtk = $mapelMtk ? GuruMapel::where('guru_id', $guruAmba->id)->where('mapel_id', $mapelMtk->id)->first() : null;
        $pkMtk = $gmMtk ? PengampuKelas::where('guru_mapel_id', $gmMtk->id)->where('kelas_id', $kelas7A->id)->first() : PengampuKelas::first();

        // Cari atau buat PengampuKelas IPA 7-A
        $mapelIpa = Mapel::where('nama_mapel', 'IPA')->first();
        $gmIpa = $mapelIpa ? GuruMapel::where('guru_id', $guruAmba->id)->where('mapel_id', $mapelIpa->id)->first() : null;
        $pkIpa = $gmIpa ? PengampuKelas::where('guru_mapel_id', $gmIpa->id)->where('kelas_id', $kelas7A->id)->first() : $pkMtk;

        if (!$pkMtk) {
            return;
        }

        // =========================================================================
        // KUIS 1: Petualangan Matematika: Aljabar & Logika
        // =========================================================================
        $quizMtk = Quiz::updateOrCreate(
            [
                'judul'             => 'Petualangan Aljabar & Aritmatika',
                'pengampu_kelas_id' => $pkMtk->id,
            ],
            [
                'deskripsi'         => 'Uji pemahamanmu tentang bilangan bulat, bentuk aljabar, dan persamaan linear dalam petualangan bertingkat!',
                'status'            => 'published',
                'kesulitan_aktif'   => ['Mudah', 'Sedang', 'Sulit'],
                'waktu_mulai'       => now()->subDays(2),
                'waktu_selesai'     => now()->addDays(30),
                'acak_jawaban'      => true,
                'level'             => 'Level 1',
                'kesulitan'         => 'Mudah',
            ]
        );

        $this->seedSoalMatematika($quizMtk);

        // =========================================================================
        // KUIS 2: Petualangan Sains: Ekosistem & Tata Surya (Jika ada pengampu IPA)
        // =========================================================================
        if ($pkIpa) {
            $quizIpa = Quiz::updateOrCreate(
                [
                    'judul'             => 'Eksplorasi Sains & Ekosistem Alam',
                    'pengampu_kelas_id' => $pkIpa->id,
                ],
                [
                    'deskripsi'         => 'Jelajahi rahasia klasifikasi makhluk hidup, rantai makanan ekosistem, dan misteri tata surya!',
                    'status'            => 'published',
                    'kesulitan_aktif'   => ['Mudah', 'Sedang', 'Sulit'],
                    'waktu_mulai'       => now()->subDays(1),
                    'waktu_selesai'     => now()->addDays(30),
                    'acak_jawaban'      => true,
                    'level'             => 'Level 1',
                    'kesulitan'         => 'Mudah',
                ]
            );

            $this->seedSoalIpa($quizIpa);
        }
    }

    /**
     * Butir-butir soal Matematika terstruktur per Arc dan Level.
     */
    protected function seedSoalMatematika(Quiz $quiz): void
    {
        $soals = [
            // === ARC MUDAH - LEVEL 1 ===
            [
                'kesulitan'     => 'Mudah',
                'level'         => 'Level 1',
                'urutan'        => 1,
                'pertanyaan'    => 'Hasil dari (-15) + 8 × (-2) adalah ...',
                'pilihan'       => [
                    'a' => '-31',
                    'b' => '-1',
                    'c' => '1',
                    'd' => '31',
                ],
                'kunci_jawaban' => 'a',
                'bobot'         => 10,
            ],
            [
                'kesulitan'     => 'Mudah',
                'level'         => 'Level 1',
                'urutan'        => 2,
                'pertanyaan'    => 'Faktor Persekutuan Terbesar (FPB) dari bilangan 24 dan 36 adalah ...',
                'pilihan'       => [
                    'a' => '6',
                    'b' => '8',
                    'c' => '12',
                    'd' => '18',
                ],
                'kunci_jawaban' => 'c',
                'bobot'         => 10,
            ],

            // === ARC MUDAH - LEVEL 2 ===
            [
                'kesulitan'     => 'Mudah',
                'level'         => 'Level 2',
                'urutan'        => 1,
                'pertanyaan'    => 'Bentuk paling sederhana dari ekspresi aljabar 4x + 7 - 2x + 3 adalah ...',
                'pilihan'       => [
                    'a' => '2x + 10',
                    'b' => '6x + 10',
                    'c' => '2x + 4',
                    'd' => '6x + 4',
                ],
                'kunci_jawaban' => 'a',
                'bobot'         => 10,
            ],
            [
                'kesulitan'     => 'Mudah',
                'level'         => 'Level 2',
                'urutan'        => 2,
                'pertanyaan'    => 'Jika nilai p = 3 dan q = -2, maka nilai dari 2p - 3q adalah ...',
                'pilihan'       => [
                    'a' => '0',
                    'b' => '6',
                    'c' => '12',
                    'd' => '-12',
                ],
                'kunci_jawaban' => 'c',
                'bobot'         => 10,
            ],

            // === ARC SEDANG - LEVEL 1 ===
            [
                'kesulitan'     => 'Sedang',
                'level'         => 'Level 1',
                'urutan'        => 1,
                'pertanyaan'    => 'Penyelesaian dari persamaan linear satu variabel 3x - 5 = 16 adalah x = ...',
                'pilihan'       => [
                    'a' => '5',
                    'b' => '7',
                    'c' => '8',
                    'd' => '11',
                ],
                'kunci_jawaban' => 'b',
                'bobot'         => 15,
            ],
            [
                'kesulitan'     => 'Sedang',
                'level'         => 'Level 1',
                'urutan'        => 2,
                'pertanyaan'    => 'Sebuah toko memberikan diskon 20% untuk sebuah baju seharga Rp 150.000,00. Berapakah harga yang harus dibayar pembeli?',
                'pilihan'       => [
                    'a' => 'Rp 110.000,00',
                    'b' => 'Rp 120.000,00',
                    'c' => 'Rp 125.000,00',
                    'd' => 'Rp 130.000,00',
                ],
                'kunci_jawaban' => 'b',
                'bobot'         => 15,
            ],

            // === ARC SULIT - LEVEL 1 ===
            [
                'kesulitan'     => 'Sulit',
                'level'         => 'Level 1',
                'urutan'        => 1,
                'pertanyaan'    => 'Suatu pekerjaan dapat diselesaikan oleh 12 orang pekerja dalam waktu 20 hari. Jika pekerjaan tersebut ingin diselesaikan dalam waktu 15 hari, berapa tambahan pekerja yang dibutuhkan?',
                'pilihan'       => [
                    'a' => '4 orang',
                    'b' => '6 orang',
                    'c' => '8 orang',
                    'd' => '16 orang',
                ],
                'kunci_jawaban' => 'a',
                'bobot'         => 20,
            ],
            [
                'kesulitan'     => 'Sulit',
                'level'         => 'Level 1',
                'urutan'        => 2,
                'pertanyaan'    => 'Perbandingan umur Ayah dan Andi adalah 5 : 2. Jika selisih umur keduanya adalah 30 tahun, berapakah jumlah umur mereka berdua?',
                'pilihan'       => [
                    'a' => '50 tahun',
                    'b' => '60 tahun',
                    'c' => '70 tahun',
                    'd' => '80 tahun',
                ],
                'kunci_jawaban' => 'c',
                'bobot'         => 20,
            ],
        ];

        foreach ($soals as $item) {
            Soal::updateOrCreate(
                [
                    'quiz_id'    => $quiz->id,
                    'kesulitan'  => $item['kesulitan'],
                    'level'      => $item['level'],
                    'urutan'     => $item['urutan'],
                ],
                array_merge($item, [
                    'tipe_soal' => 'single_choice',
                    'tugas_id'  => null,
                    'ujian_id'  => null,
                ])
            );
        }
    }

    /**
     * Butir-butir soal IPA terstruktur per Arc dan Level.
     */
    protected function seedSoalIpa(Quiz $quiz): void
    {
        $soals = [
            // === ARC MUDAH - LEVEL 1 ===
            [
                'kesulitan'     => 'Mudah',
                'level'         => 'Level 1',
                'urutan'        => 1,
                'pertanyaan'    => 'Organel sel yang berfungsi sebagai tempat berlangsungnya respirasi sel dan menghasilkan energi (ATP) adalah ...',
                'pilihan'       => [
                    'a' => 'Mitokondria',
                    'b' => 'Ribosom',
                    'c' => 'Kloroplas',
                    'd' => 'Badan Golgi',
                ],
                'kunci_jawaban' => 'a',
                'bobot'         => 10,
            ],
            [
                'kesulitan'     => 'Mudah',
                'level'         => 'Level 1',
                'urutan'        => 2,
                'pertanyaan'    => 'Ciri khusus dari kingdom Fungi (jamur) yang membedakannya dengan tumbuhan hijau adalah ...',
                'pilihan'       => [
                    'a' => 'Tidak memiliki inti sel (prokariotik)',
                    'b' => 'Tidak memiliki klorofil dan bersifat heterotrof',
                    'c' => 'Bersel tunggal tanpa dinding sel',
                    'd' => 'Dapat membuat makanan sendiri melalui fotosintesis',
                ],
                'kunci_jawaban' => 'b',
                'bobot'         => 10,
            ],

            // === ARC SEDANG - LEVEL 1 ===
            [
                'kesulitan'     => 'Sedang',
                'level'         => 'Level 1',
                'urutan'        => 1,
                'pertanyaan'    => 'Dalam suatu ekosistem sawah terdapat rantai makanan: Padi -> Belalang -> Katak -> Ular -> Elang. Apabila populasi katak menurun drastis karena diburu, peristiwa yang akan terjadi adalah ...',
                'pilihan'       => [
                    'a' => 'Populasi belalang menurun dan populasi padi meningkat',
                    'b' => 'Populasi ular meningkat drastis',
                    'c' => 'Populasi belalang meningkat sehingga panen padi menurun',
                    'd' => 'Populasi elang tidak terpengaruh sama sekali',
                ],
                'kunci_jawaban' => 'c',
                'bobot'         => 15,
            ],
            [
                'kesulitan'     => 'Sedang',
                'level'         => 'Level 1',
                'urutan'        => 2,
                'pertanyaan'    => 'Planet di tata surya yang sering dijuluki sebagai "Planet Merah" karena permukaannya yang kaya akan oksida besi adalah ...',
                'pilihan'       => [
                    'a' => 'Merkurius',
                    'b' => 'Venus',
                    'c' => 'Mars',
                    'd' => 'Yupiter',
                ],
                'kunci_jawaban' => 'c',
                'bobot'         => 15,
            ],

            // === ARC SULIT - LEVEL 1 ===
            [
                'kesulitan'     => 'Sulit',
                'level'         => 'Level 1',
                'urutan'        => 1,
                'pertanyaan'    => 'Peristiwa pasang perbani (pasang surut air laut dengan tinggi pasang minimum) terjadi pada saat posisi bulan, bumi, dan matahari membentuk sudut ...',
                'pilihan'       => [
                    'a' => '180 derajat (garis lurus saat bulan purnama)',
                    'b' => '90 derajat (tegak lurus saat bulan kuartir)',
                    'c' => '0 derajat (saat gerhana matahari total)',
                    'd' => '45 derajat',
                ],
                'kunci_jawaban' => 'b',
                'bobot'         => 20,
            ],
            [
                'kesulitan'     => 'Sulit',
                'level'         => 'Level 1',
                'urutan'        => 2,
                'pertanyaan'    => 'Sebongkah es bersuhu -5°C dipanaskan hingga mendidih menjadi uap air bersuhu 100°C. Proses di mana kalor diserap tanpa mengalami kenaikan suhu terjadi pada saat ...',
                'pilihan'       => [
                    'a' => 'Es naik suhunya dari -5°C menjadi 0°C',
                    'b' => 'Es melebur menjadi air pada suhu tetap 0°C (kalor laten lebur)',
                    'c' => 'Air naik suhunya dari 0°C menjadi 100°C',
                    'd' => 'Air mulai dipanaskan di atas kompor',
                ],
                'kunci_jawaban' => 'b',
                'bobot'         => 20,
            ],
        ];

        foreach ($soals as $item) {
            Soal::updateOrCreate(
                [
                    'quiz_id'    => $quiz->id,
                    'kesulitan'  => $item['kesulitan'],
                    'level'      => $item['level'],
                    'urutan'     => $item['urutan'],
                ],
                array_merge($item, [
                    'tipe_soal' => 'single_choice',
                    'tugas_id'  => null,
                    'ujian_id'  => null,
                ])
            );
        }
    }
}

