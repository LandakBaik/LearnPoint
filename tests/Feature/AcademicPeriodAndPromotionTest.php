<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\AnggotaKelas;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicPeriodAndPromotionTest extends TestCase
{
    use RefreshDatabase;

    protected function operator(): User
    {
        return User::factory()->create([
            'role'   => 'operator',
            'status' => 'aktif',
        ]);
    }

    public function test_siswa_stores_permanent_angkatan_and_follows_active_academic_period(): void
    {
        $operator = $this->operator();
        $kelas7A = Kelas::create(['nama_kelas' => '7-A', 'tingkatan' => 7]);
        $kelas8A = Kelas::create(['nama_kelas' => '8-A', 'tingkatan' => 8]);
        $kelas9A = Kelas::create(['nama_kelas' => '9-A', 'tingkatan' => 9]);

        // 1. Tambah siswa baru pada periode aktif default (2026/2027 ganjil)
        $this->actingAs($operator)->post(route('siswa.store'), [
            'nama_siswa'    => 'Siswa Naik Kelas',
            'nis'           => '20260001',
            'angkatan'      => 2026,
            'alamat'        => 'Jl. Merdeka 1',
            'tanggal_lahir' => '2013-04-10',
            'jenis_kelamin' => 'L',
            'wali_murid'    => 'Wali Satu',
            'nohp_wali'     => '081234567890',
            'kelas_id'      => $kelas7A->id,
        ])->assertRedirect(route('siswa.index'));

        $this->actingAs($operator)->post(route('siswa.store'), [
            'nama_siswa'    => 'Siswa Kelas Sembilan',
            'nis'           => '20240009',
            'angkatan'      => 2024,
            'alamat'        => 'Jl. Merdeka 9',
            'tanggal_lahir' => '2011-05-11',
            'jenis_kelamin' => 'P',
            'wali_murid'    => 'Wali Sembilan',
            'nohp_wali'     => '081234567899',
            'kelas_id'      => $kelas9A->id,
        ])->assertRedirect(route('siswa.index'));

        $siswa7 = Siswa::where('nis', '20260001')->firstOrFail();
        $siswa9 = Siswa::where('nis', '20240009')->firstOrFail();

        $this->assertSame(2026, $siswa7->angkatan);
        $this->assertSame($kelas7A->id, $siswa7->kelas->id);

        // 2. Pergantian semester: 2026/2027 ganjil -> 2026/2027 genap
        $ganjil = AcademicPeriod::current();
        $this->actingAs($operator)->post(route('admin.tahun-ajaran.copy-semester'), [
            'source_period_id' => $ganjil->id,
            'tahun_ajaran'     => '2026/2027',
            'semester'         => 'genap',
            'copy_pengampu'    => '1',
        ])->assertRedirect(route('admin.tahun-ajaran.index'));

        $genap = AcademicPeriod::current();
        $this->assertSame('2026/2027', $genap->tahun_ajaran);
        $this->assertSame('genap', $genap->semester);
        $this->assertDatabaseHas('anggota_kelases', [
            'siswa_id'           => $siswa7->id,
            'kelas_id'           => $kelas7A->id,
            'academic_period_id' => $genap->id,
        ]);

        // 3. Kenaikan kelas: 2026/2027 genap -> 2027/2028 ganjil
        $this->actingAs($operator)->post(route('admin.tahun-ajaran.promote'), [
            'source_period_id' => $genap->id,
            'tahun_ajaran'     => '2027/2028',
            'semester'         => 'ganjil',
            'mapping'          => [
                $kelas7A->id => $kelas8A->id,
                $kelas9A->id => 'lulus',
            ],
        ])->assertRedirect(route('admin.tahun-ajaran.index'));

        $siswa7->refresh();
        $siswa9->refresh();

        // Angkatan tetap 2026, kelas aktif otomatis berubah ke 8-A, histori 7-A tetap utuh
        $this->assertSame(2026, $siswa7->angkatan);
        $this->assertSame($kelas8A->id, $siswa7->kelas->id);
        $this->assertSame(3, AnggotaKelas::where('siswa_id', $siswa7->id)->count());

        // Siswa kelas 9 menjadi lulus
        $this->assertSame('lulus', $siswa9->status);
    }
}
