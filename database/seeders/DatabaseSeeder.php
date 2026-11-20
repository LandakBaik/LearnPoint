<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Admin / Operator
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin Operator',
                'username' => 'admin',
                'password' => Hash::make('1234'),
                'role' => 'operator',
            ]
        );

        // 2. Akun Guru (Amba)
        $guru = Guru::firstOrCreate(
            ['nuptk' => '1234567890123456'],
            ['nama' => 'Amba']
        );

        User::updateOrCreate(
            ['email' => 'amba@gmail.com'],
            [
                'name' => 'Amba',
                'username' => 'amba',
                'password' => Hash::make('1234'),
                'role' => 'guru',
                'guru_id' => $guru->id,
            ]
        );

        // 3. Akun Siswa & Kelas
        $kelas = Kelas::firstOrCreate(
            ['nama_kelas' => 'VII IPA 1'],
            [
                'tingkatan' => 7,
                'guru_id' => $guru->id,
            ]
        );

        $siswa = Siswa::firstOrCreate(
            ['nis' => '12345'],
            [
                'nama_siswa' => 'Budi Siswa',
                'alamat' => 'Jl. Pendidikan No. 123',
                'tanggal_lahir' => '2008-01-01',
                'jenis_kelamin' => 'L',
                'agama' => 'Islam',
                'wali_murid' => 'Orang Tua Budi',
                'nohp_wali' => '081234567890',
            ]
        );
        if (empty($siswa->agama)) {
            $siswa->update(['agama' => 'Islam']);
        }

        User::updateOrCreate(
            ['email' => 'siswa@gmail.com'],
            [
                'name' => 'Budi Siswa',
                'username' => 'siswa',
                'password' => Hash::make('1234'),
                'role' => 'siswa',
                'siswa_id' => $siswa->id,
            ]
        );

        // 4. Akun Kepala Sekolah
        User::updateOrCreate(
            ['email' => 'kepsek@gmail.com'],
            [
                'name' => 'Dr. Sukarno, M.Pd.',
                'username' => 'kepsek',
                'password' => Hash::make('1234'),
                'role' => 'kepala_sekolah',
            ]
        );
        // 5. Seeder Materi & Kelas Guru Amba
        $this->call(MateriGuruSeeder::class);

        // 6. Daftarkan Siswa ke Kelas 7-A pada periode aktif
        $kelas7A = Kelas::where('nama_kelas', '7-A')->first() ?? $kelas;
        $activePeriodId = \App\Models\AcademicPeriod::currentId();
        \App\Models\AnggotaKelas::firstOrCreate([
            'siswa_id'           => $siswa->id,
            'academic_period_id' => $activePeriodId,
        ], [
            'kelas_id'           => $kelas7A->id,
        ]);

        // 7. Seeder Tugas & Kuis Petualangan
        $this->call([
            TugasSeeder::class,
            QuizSeeder::class,
        ]);

        // 8. Katalog Master Items (Power & Banner) & Inventaris Awal Siswa
        \App\Models\Item::ensureDefaultCatalog();
        \App\Models\Inventory::initializeDefaultInventory($siswa);

        // 9. Seeder Tambahan Siswa dengan Kelas & Agama Berbeda
        $kelas7B = Kelas::where('nama_kelas', '7-B')->first() ?? $kelas7A;
        $kelas7C = Kelas::where('nama_kelas', '7-C')->first() ?? $kelas7A;
        $kelas8A = Kelas::firstOrCreate(['nama_kelas' => '8-A'], ['tingkatan' => 8]);

        $additionalStudents = [
            [
                'nis'           => '12346',
                'nama_siswa'    => 'Daniel Pratama',
                'email'         => 'daniel@gmail.com',
                'username'      => 'daniel',
                'jenis_kelamin' => 'L',
                'agama'         => 'Kristen',
                'tanggal_lahir' => '2013-02-14',
                'wali_murid'    => 'Yohanes Pratama',
                'nohp_wali'     => '081234567891',
                'kelas'         => $kelas7A,
            ],
            [
                'nis'           => '12347',
                'nama_siswa'    => 'Maria Fransiska',
                'email'         => 'maria@gmail.com',
                'username'      => 'maria',
                'jenis_kelamin' => 'P',
                'agama'         => 'Katolik',
                'tanggal_lahir' => '2013-04-20',
                'wali_murid'    => 'Antonius Susanto',
                'nohp_wali'     => '081234567892',
                'kelas'         => $kelas7A,
            ],
            [
                'nis'           => '12348',
                'nama_siswa'    => 'Siti Aisyah',
                'email'         => 'aisyah@gmail.com',
                'username'      => 'aisyah',
                'jenis_kelamin' => 'P',
                'agama'         => 'Islam',
                'tanggal_lahir' => '2013-06-10',
                'wali_murid'    => 'Ahmad Fauzi',
                'nohp_wali'     => '081234567893',
                'kelas'         => $kelas7B,
            ],
            [
                'nis'           => '12349',
                'nama_siswa'    => 'Made Wirawan',
                'email'         => 'made@gmail.com',
                'username'      => 'made',
                'jenis_kelamin' => 'L',
                'agama'         => 'Hindu',
                'tanggal_lahir' => '2013-08-05',
                'wali_murid'    => 'Wayan Sudarta',
                'nohp_wali'     => '081234567894',
                'kelas'         => $kelas7B,
            ],
            [
                'nis'           => '12350',
                'nama_siswa'    => 'Kevin Wijaya',
                'email'         => 'kevin@gmail.com',
                'username'      => 'kevin',
                'jenis_kelamin' => 'L',
                'agama'         => 'Buddha',
                'tanggal_lahir' => '2013-09-18',
                'wali_murid'    => 'Hendra Wijaya',
                'nohp_wali'     => '081234567895',
                'kelas'         => $kelas7C,
            ],
            [
                'nis'           => '12351',
                'nama_siswa'    => 'Mei Ling',
                'email'         => 'meiling@gmail.com',
                'username'      => 'meiling',
                'jenis_kelamin' => 'P',
                'agama'         => 'Konghucu',
                'tanggal_lahir' => '2012-11-22',
                'wali_murid'    => 'Budi Hartono',
                'nohp_wali'     => '081234567896',
                'kelas'         => $kelas8A,
            ],
        ];

        foreach ($additionalStudents as $item) {
            $s = Siswa::updateOrCreate(
                ['nis' => $item['nis']],
                [
                    'nama_siswa'    => $item['nama_siswa'],
                    'alamat'        => 'Jl. Pendidikan Jember',
                    'tanggal_lahir' => $item['tanggal_lahir'],
                    'jenis_kelamin' => $item['jenis_kelamin'],
                    'agama'         => $item['agama'],
                    'wali_murid'    => $item['wali_murid'],
                    'nohp_wali'     => $item['nohp_wali'],
                ]
            );

            User::updateOrCreate(
                ['email' => $item['email']],
                [
                    'name'     => $item['nama_siswa'],
                    'username' => $item['username'],
                    'password' => Hash::make('1234'),
                    'role'     => 'siswa',
                    'siswa_id' => $s->id,
                ]
            );

            \App\Models\AnggotaKelas::updateOrCreate(
                [
                    'siswa_id'           => $s->id,
                    'academic_period_id' => $activePeriodId,
                ],
                [
                    'kelas_id' => $item['kelas']->id,
                ]
            );

            \App\Models\Inventory::initializeDefaultInventory($s);
        }
    }
}
