<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Guru;
use App\Models\GuruMapel;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\PengampuKelas;
use App\Models\Siswa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MapelAgamaTest extends TestCase
{
    use RefreshDatabase;

    public function test_mapel_agama_hanya_tampil_dan_direkap_untuk_siswa_seagama(): void
    {
        $period = AcademicPeriod::firstOrCreate(
            ['tahun_ajaran' => '2026/2027', 'semester' => 'ganjil'],
            ['is_active' => true]
        );

        $kelas = Kelas::create([
            'nama_kelas' => '7A',
            'tingkatan'  => 7,
        ]);

        $siswaIslam = Siswa::create([
            'nama_siswa'    => 'Ahmad',
            'nis'           => '10001',
            'alamat'        => 'Jember',
            'tanggal_lahir' => '2013-01-01',
            'jenis_kelamin' => 'L',
            'agama'         => 'Islam',
            'wali_murid'    => 'Budi',
            'nohp_wali'     => '081234567890',
        ]);

        $siswaKristen = Siswa::create([
            'nama_siswa'    => 'Daniel',
            'nis'           => '10002',
            'alamat'        => 'Jember',
            'tanggal_lahir' => '2013-02-02',
            'jenis_kelamin' => 'L',
            'agama'         => 'Kristen',
            'wali_murid'    => 'Yohanes',
            'nohp_wali'     => '081234567891',
        ]);

        $mapelUmum = Mapel::create([
            'nama_mapel' => 'Matematika',
            'kkm'        => 75,
            'tipe_mapel' => 'teori',
        ]);

        $mapelPraktik = Mapel::create([
            'nama_mapel' => 'PJOK',
            'kkm'        => 75,
            'tipe_mapel' => 'praktik',
        ]);

        $mapelIslam = Mapel::create([
            'nama_mapel' => 'PAIBP',
            'kkm'        => 75,
            'tipe_mapel' => 'agama_islam',
        ]);

        $mapelKristen = Mapel::create([
            'nama_mapel' => 'Pendidikan Agama Kristen',
            'kkm'        => 75,
            'tipe_mapel' => 'agama_kristen',
        ]);

        $guruIslam = Guru::create([
            'nama'  => 'Umi Kulsum',
            'nuptk' => '1234567890123451',
        ]);

        $guruKristen = Guru::create([
            'nama'  => 'Guru Kristen',
            'nuptk' => '1234567890123452',
        ]);

        // Dua guru agama berbeda boleh mengampu di kelas yang sama karena mapel agamanya terpisah
        $gmIslam = GuruMapel::create(['guru_id' => $guruIslam->id, 'mapel_id' => $mapelIslam->id]);
        $gmKristen = GuruMapel::create(['guru_id' => $guruKristen->id, 'mapel_id' => $mapelKristen->id]);

        $pkIslam = PengampuKelas::create([
            'guru_mapel_id'      => $gmIslam->id,
            'kelas_id'           => $kelas->id,
            'academic_period_id' => $period->id,
        ]);

        $pkKristen = PengampuKelas::create([
            'guru_mapel_id'      => $gmKristen->id,
            'kelas_id'           => $kelas->id,
            'academic_period_id' => $period->id,
        ]);

        $this->assertNotNull($pkIslam->id);
        $this->assertNotNull($pkKristen->id);

        // Filter mapel untuk siswa Islam: Matematika (teori), PJOK (praktik), + PAIBP (agama_islam)
        $mapelUntukIslam = Mapel::forSiswa($siswaIslam)->pluck('nama_mapel')->all();
        $this->assertContains('Matematika', $mapelUntukIslam);
        $this->assertContains('PJOK', $mapelUntukIslam);
        $this->assertContains('PAIBP', $mapelUntukIslam);
        $this->assertNotContains('Pendidikan Agama Kristen', $mapelUntukIslam);

        // Filter mapel untuk siswa Kristen: Matematika (teori), PJOK (praktik), + Pendidikan Agama Kristen (agama_kristen)
        $mapelUntukKristen = Mapel::forSiswa($siswaKristen)->pluck('nama_mapel')->all();
        $this->assertContains('Matematika', $mapelUntukKristen);
        $this->assertContains('PJOK', $mapelUntukKristen);
        $this->assertContains('Pendidikan Agama Kristen', $mapelUntukKristen);
        $this->assertNotContains('PAIBP', $mapelUntukKristen);

        // Filter pengampu kelas untuk siswa Kristen
        $pengampuUntukKristen = PengampuKelas::forSiswa($siswaKristen)->pluck('id')->all();
        $this->assertContains($pkKristen->id, $pengampuUntukKristen);
        $this->assertNotContains($pkIslam->id, $pengampuUntukKristen);

        // Filter siswa untuk rekap mapel Pendidikan Agama Kristen
        $siswaKristenRekap = Siswa::forMapel($mapelKristen)->pluck('id')->all();
        $this->assertEquals([$siswaKristen->id], $siswaKristenRekap);

        // Filter siswa untuk mapel umum: semua siswa tampil
        $siswaUmumRekap = Siswa::forMapel($mapelUmum)->pluck('id')->all();
        $this->assertCount(2, $siswaUmumRekap);
    }
}
