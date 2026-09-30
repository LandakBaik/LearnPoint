<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\GuruMapel;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Materi;
use App\Models\PengampuKelas;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MateriGuruSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Dapatkan atau buat Guru Amba & akun User
        $guru = Guru::firstOrCreate(
            ['nip' => '198501012024011001'],
            ['nama' => 'Amba', 'status' => 'aktif']
        );

        User::updateOrCreate(
            ['email' => 'amba@gmail.com'],
            [
                'name' => 'Amba',
                'username' => 'amba',
                'password' => Hash::make('1234'),
                'role' => 'guru',
                'guru_id' => $guru->id,
                'status' => 'aktif',
            ]
        );

        // 2. Buat 3 Kelas Tingkat 7
        $kelas7A = Kelas::firstOrCreate(
            ['nama_kelas' => '7-A'],
            ['tingkatan' => 7]
        );

        $kelas7B = Kelas::firstOrCreate(
            ['nama_kelas' => '7-B'],
            ['tingkatan' => 7]
        );

        $kelas7C = Kelas::firstOrCreate(
            ['nama_kelas' => '7-C'],
            ['tingkatan' => 7]
        );

        // 3. Buat 2 Mata Pelajaran (Matematika & IPA)
        $mapelMtk = Mapel::firstOrCreate(
            ['nama_mapel' => 'Matematika'],
            ['kkm' => 75]
        );

        $mapelIpa = Mapel::firstOrCreate(
            ['nama_mapel' => 'IPA'],
            ['kkm' => 75]
        );

        // 4. Guru Amba mengampu 2 Mapel tersebut (GuruMapel)
        $gmMtk = GuruMapel::firstOrCreate([
            'guru_id' => $guru->id,
            'mapel_id' => $mapelMtk->id,
        ]);

        $gmIpa = GuruMapel::firstOrCreate([
            'guru_id' => $guru->id,
            'mapel_id' => $mapelIpa->id,
        ]);

        // 5. Hubungkan GuruMapel ke 3 Kelas (PengampuKelas)
        $pkMtk7A = PengampuKelas::firstOrCreate(['guru_mapel_id' => $gmMtk->id, 'kelas_id' => $kelas7A->id]);
        $pkMtk7B = PengampuKelas::firstOrCreate(['guru_mapel_id' => $gmMtk->id, 'kelas_id' => $kelas7B->id]);
        $pkMtk7C = PengampuKelas::firstOrCreate(['guru_mapel_id' => $gmMtk->id, 'kelas_id' => $kelas7C->id]);

        $pkIpa7A = PengampuKelas::firstOrCreate(['guru_mapel_id' => $gmIpa->id, 'kelas_id' => $kelas7A->id]);
        $pkIpa7B = PengampuKelas::firstOrCreate(['guru_mapel_id' => $gmIpa->id, 'kelas_id' => $kelas7B->id]);
        $pkIpa7C = PengampuKelas::firstOrCreate(['guru_mapel_id' => $gmIpa->id, 'kelas_id' => $kelas7C->id]);

        // 6. Buat 3 Materi dengan id_grub_materi (UUID)
        
        // Materi 1: Matematika - Sistem Persamaan Linear (7-A & 7-B)
        $uuidMateri1 = (string) Str::uuid();
        Materi::firstOrCreate([
            'pengampu_kelas_id' => $pkMtk7A->id,
            'id_grub_materi' => $uuidMateri1,
        ], [
            'judul' => 'Sistem Persamaan Linear Satu Variabel',
            'deskripsi' => 'Pelajari konsep dasar persamaan linear satu variabel beserta contoh soal dan pembahasan.',
            'file_materi' => 'materi_files/persamaan_linear.pdf',
            'url_youtube' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);

        Materi::firstOrCreate([
            'pengampu_kelas_id' => $pkMtk7B->id,
            'id_grub_materi' => $uuidMateri1,
        ], [
            'judul' => 'Sistem Persamaan Linear Satu Variabel',
            'deskripsi' => 'Pelajari konsep dasar persamaan linear satu variabel beserta contoh soal dan pembahasan.',
            'file_materi' => 'materi_files/persamaan_linear.pdf',
            'url_youtube' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);

        // Materi 2: Matematika - Aljabar & Operasi Hitung (7-A, 7-B & 7-C)
        $uuidMateri2 = (string) Str::uuid();
        foreach ([$pkMtk7A, $pkMtk7B, $pkMtk7C] as $pk) {
            Materi::firstOrCreate([
                'pengampu_kelas_id' => $pk->id,
                'id_grub_materi' => $uuidMateri2,
            ], [
                'judul' => 'Pengenalan Aljabar & Operasi Hitung Dasar',
                'deskripsi' => 'Pengenalan variabel, koefisien, konstanta, serta penjumlahan dan pengurangan suku sejenis.',
                'file_materi' => 'materi_files/aljabar_dasar.pdf',
                'url_youtube' => null,
            ]);
        }

        // Materi 3: IPA - Klasifikasi Makhluk Hidup (7-A & 7-C)
        $uuidMateri3 = (string) Str::uuid();
        foreach ([$pkIpa7A, $pkIpa7C] as $pk) {
            Materi::firstOrCreate([
                'pengampu_kelas_id' => $pk->id,
                'id_grub_materi' => $uuidMateri3,
            ], [
                'judul' => 'Klasifikasi Makhluk Hidup & Keanekaragaman',
                'deskripsi' => 'Modul pembelajaran mengenai taksonomi, ciri-ciri makhluk hidup, dan pengelompokan 5 kingdom.',
                'file_materi' => 'materi_files/klasifikasi_makhluk_hidup.pdf',
                'url_youtube' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            ]);
        }
    }
}
