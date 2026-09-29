<?php

namespace Tests\Feature;

use App\Models\AnggotaKelas;
use App\Models\Guru;
use App\Models\GuruMapel;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Materi;
use App\Models\PengampuKelas;
use App\Models\Siswa;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MateriTugasStudentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_materi_and_tugas_pages_use_pengampu_kelas_relationships(): void
    {
        $kelas = Kelas::create([
            'nama_kelas' => '7-A',
            'tingkatan' => 7,
        ]);
        $guru = Guru::create([
            'nama' => 'Guru Uji',
            'nip' => '1980012345678901',
            'status' => 'aktif',
        ]);
        $mapel = Mapel::create([
            'nama_mapel' => 'Matematika',
            'kkm' => 75,
        ]);
        $guruMapel = GuruMapel::create([
            'guru_id' => $guru->id,
            'mapel_id' => $mapel->id,
        ]);
        $pengampuKelas = PengampuKelas::create([
            'guru_mapel_id' => $guruMapel->id,
            'kelas_id' => $kelas->id,
        ]);
        $kelasLain = Kelas::create([
            'nama_kelas' => '7-B',
            'tingkatan' => 7,
        ]);
        $mapelLain = Mapel::create([
            'nama_mapel' => 'IPA',
            'kkm' => 75,
        ]);
        $guruMapelLain = GuruMapel::create([
            'guru_id' => $guru->id,
            'mapel_id' => $mapelLain->id,
        ]);
        $pengampuKelasLain = PengampuKelas::create([
            'guru_mapel_id' => $guruMapelLain->id,
            'kelas_id' => $kelasLain->id,
        ]);
        $siswa = Siswa::create([
            'nama_siswa' => 'Siswa Uji',
            'nis' => '20241021',
            'alamat' => 'Jl. Uji No. 1',
            'tanggal_lahir' => '2012-05-14',
            'jenis_kelamin' => 'L',
            'wali_murid' => 'Wali Uji',
            'nohp_wali' => '081234567890',
            'status' => 'aktif',
        ]);
        AnggotaKelas::create([
            'kelas_id' => $kelas->id,
            'siswa_id' => $siswa->id,
            'tahun_ajaran' => '2026/2027',
            'semester' => 'ganjil',
        ]);
        $user = User::factory()->create([
            'role' => 'siswa',
            'status' => 'aktif',
            'siswa_id' => $siswa->id,
        ]);
        Materi::create([
            'judul' => 'Persamaan Linear',
            'file_materi' => 'materi/persamaan-linear.pdf',
            'pengampu_kelas_id' => $pengampuKelas->id,
        ]);
        Materi::create([
            'judul' => 'Sistem Tata Surya',
            'file_materi' => 'materi/tata-surya.pdf',
            'pengampu_kelas_id' => $pengampuKelasLain->id,
        ]);
        Tugas::create([
            'judul' => 'Latihan Persamaan',
            'deadline' => now()->addDay(),
            'tipe' => 'upload',
            'pengampu_kelas_id' => $pengampuKelas->id,
        ]);
        Tugas::create([
            'judul' => 'Tugas Kelas Lain',
            'deadline' => now()->addDay(),
            'tipe' => 'upload',
            'pengampu_kelas_id' => $pengampuKelasLain->id,
        ]);

        $this->actingAs($user)
            ->get(route('siswa.materi'))
            ->assertOk()
            ->assertSee('Matematika')
            ->assertSee('1 Modul')
            ->assertDontSee('Sistem Tata Surya')
            ->assertDontSee('IPA');

        $this->actingAs($user)
            ->get(route('siswa.tugas'))
            ->assertOk()
            ->assertSee('Latihan Persamaan')
            ->assertSee('Matematika')
            ->assertDontSee('Tugas Kelas Lain');
    }
}
