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
            ['nip' => '198501012024011001'],
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
            ['nama_kelas' => 'X IPA 1'],
            [
                'tingkatan' => 10,
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
                'wali_murid' => 'Orang Tua Budi',
                'nohp_wali' => '081234567890',
                'kelas_id' => $kelas->id,
            ]
        );

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
    }
}


