<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\AnggotaKelas;
use App\Models\Guru;
use App\Models\GuruMapel;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Nilai;
use App\Models\PengampuKelas;
use App\Models\Quiz;
use App\Models\SeleksiUjian;
use App\Models\Siswa;
use App\Models\Tugas;
use App\Models\Ujian;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiswaStatistikDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function createStudentFixture(): array
    {
        $period = AcademicPeriod::updateOrCreate(
            [
                'tahun_ajaran' => '2026/2027',
                'semester'     => 'ganjil',
            ],
            [
                'tanggal_mulai'   => '2026-07-01',
                'tanggal_selesai' => '2026-12-31',
                'is_active'       => true,
            ]
        );

        $kelas = Kelas::create([
            'nama_kelas' => '8-A',
            'tingkatan'  => 8,
        ]);

        $guru = Guru::create([
            'nama'   => 'Budi Santoso',
            'nuptk'  => '1234567890123456',
            'status' => 'aktif',
        ]);

        $mapelMath = Mapel::create([
            'nama_mapel' => 'Matematika',
            'kkm'        => 75,
            'tipe_mapel' => 'teori',
        ]);

        $mapelIpa = Mapel::create([
            'nama_mapel' => 'IPA Terpadu',
            'kkm'        => 75,
            'tipe_mapel' => 'teori',
        ]);

        $gmMath = GuruMapel::create(['guru_id' => $guru->id, 'mapel_id' => $mapelMath->id]);
        $gmIpa = GuruMapel::create(['guru_id' => $guru->id, 'mapel_id' => $mapelIpa->id]);

        $pkMath = PengampuKelas::create([
            'guru_mapel_id'      => $gmMath->id,
            'kelas_id'           => $kelas->id,
            'academic_period_id' => $period->id,
        ]);

        $pkIpa = PengampuKelas::create([
            'guru_mapel_id'      => $gmIpa->id,
            'kelas_id'           => $kelas->id,
            'academic_period_id' => $period->id,
        ]);

        $siswa = Siswa::create([
            'nama_siswa'    => 'Andi Pratama',
            'nis'           => '20260001',
            'alamat'        => 'Jl. Merdeka No. 1',
            'tanggal_lahir' => '2012-05-10',
            'jenis_kelamin' => 'L',
            'agama'         => 'Islam',
            'wali_murid'    => 'Orang Tua Andi',
            'nohp_wali'     => '081234567890',
            'status'        => 'aktif',
        ]);

        AnggotaKelas::create([
            'siswa_id'           => $siswa->id,
            'kelas_id'           => $kelas->id,
            'academic_period_id' => $period->id,
        ]);

        $user = User::factory()->create([
            'role'     => 'siswa',
            'status'   => 'aktif',
            'siswa_id' => $siswa->id,
        ]);

        return compact('period', 'kelas', 'guru', 'mapelMath', 'mapelIpa', 'pkMath', 'pkIpa', 'siswa', 'user');
    }

    public function test_statistik_shows_real_empty_state_without_dummy_benchmarks_when_no_grades(): void
    {
        $fx = $this->createStudentFixture();

        $response = $this->actingAs($fx['user'])
            ->get(route('siswa.dashboard', ['tab' => 'statistik']));

        $response->assertOk()
            ->assertSee('Belum Ada Data')
            ->assertSee('Belum Ada Riwayat Nilai')
            ->assertSee('Matematika')
            ->assertSee('IPA Terpadu')
            ->assertSee('Belum ada nilai')
            // Pastikan tidak ada angka dummy benchmark atau mapel palsu
            ->assertDontSee('87,5')
            ->assertDontSee('85,2')
            ->assertDontSee('90,1')
            ->assertDontSee('86,7')
            ->assertDontSee('PPKn');

        $response->assertViewHas('statistik', function ($stat) {
            return $stat['has_real_data'] === false
                && $stat['rata_rata_semua'] === 0.0
                && $stat['donut_degree'] === 0
                && count($stat['list_nilai_mapel']) === 2
                && $stat['perkembangan_nilai']['has_chart_data'] === false;
        });
    }

    public function test_statistik_computes_real_dynamic_averages_per_mapel_and_monthly_chart(): void
    {
        $fx = $this->createStudentFixture();

        // 1. Tugas: 1 dinilai (80), 1 belum dinilai (harus diabaikan dari rata-rata)
        $tugas1 = Tugas::create([
            'judul'             => 'Tugas Aljabar 1',
            'deadline'          => Carbon::create(2026, 8, 20),
            'tipe'              => 'upload',
            'pengampu_kelas_id' => $fx['pkMath']->id,
        ]);
        $tugas2 = Tugas::create([
            'judul'             => 'Tugas Aljabar 2',
            'deadline'          => Carbon::create(2026, 9, 20),
            'tipe'              => 'upload',
            'pengampu_kelas_id' => $fx['pkMath']->id,
        ]);

        $nilaiTugas1 = Nilai::create([
            'siswa_id' => $fx['siswa']->id,
            'tugas_id' => $tugas1->id,
            'nilai'    => 80,
            'status'   => 'dinilai',
        ]);
        $nilaiTugas1->forceFill([
            'created_at' => Carbon::create(2026, 8, 15),
            'updated_at' => Carbon::create(2026, 8, 15),
        ])->save();

        // Tugas belum dinilai (nilai = null) -> harus diabaikan
        Nilai::create([
            'siswa_id' => $fx['siswa']->id,
            'tugas_id' => $tugas2->id,
            'nilai'    => null,
            'status'   => 'dikumpulkan',
        ]);

        // 2. Kuis: 1 selesai (90), 1 masih draft (harus diabaikan)
        $quiz1 = Quiz::create([
            'judul'             => 'Kuis Matematika 1',
            'status'            => 'published',
            'pengampu_kelas_id' => $fx['pkMath']->id,
        ]);
        $quiz2 = Quiz::create([
            'judul'             => 'Kuis IPA 1',
            'status'            => 'published',
            'pengampu_kelas_id' => $fx['pkIpa']->id,
        ]);

        $nilaiQuiz1 = Nilai::create([
            'siswa_id' => $fx['siswa']->id,
            'quiz_id'  => $quiz1->id,
            'nilai'    => 90,
            'status'   => 'selesai',
        ]);
        $nilaiQuiz1->forceFill([
            'created_at' => Carbon::create(2026, 9, 10),
            'updated_at' => Carbon::create(2026, 9, 10),
        ])->save();

        // Kuis draft -> harus diabaikan
        Nilai::create([
            'siswa_id' => $fx['siswa']->id,
            'quiz_id'  => $quiz2->id,
            'nilai'    => 20,
            'status'   => 'draft',
        ]);

        // 3. Ujian: 1 selesai (70) di IPA, 1 sedang dikerjakan dengan nilai_akhir=0 (harus diabaikan)
        $ujian1 = Ujian::create([
            'judul'             => 'UTS IPA',
            'status'            => 'completed',
            'jenis_ujian'       => 'reguler',
            'durasi_menit'      => 60,
            'waktu_mulai'       => Carbon::create(2026, 10, 10, 8, 0),
            'deadline'          => Carbon::create(2026, 10, 10, 10, 0),
            'pengampu_kelas_id' => $fx['pkIpa']->id,
        ]);
        $ujian2 = Ujian::create([
            'judul'             => 'UTS Matematika',
            'status'            => 'published',
            'jenis_ujian'       => 'reguler',
            'durasi_menit'      => 60,
            'waktu_mulai'       => Carbon::create(2026, 10, 11, 8, 0),
            'deadline'          => Carbon::create(2026, 10, 11, 10, 0),
            'pengampu_kelas_id' => $fx['pkMath']->id,
        ]);

        SeleksiUjian::create([
            'ujian_id'      => $ujian1->id,
            'siswa_id'      => $fx['siswa']->id,
            'nilai_awal'    => 70,
            'nilai_akhir'   => 70,
            'jumlah_remidi' => 0,
            'waktu_mulai'   => Carbon::create(2026, 10, 10, 8, 0),
            'waktu_selesai' => Carbon::create(2026, 10, 10, 8, 45),
            'status'        => 'selesai',
        ]);

        // Sedang dikerjakan (status != selesai & waktu_selesai null) -> harus diabaikan
        SeleksiUjian::create([
            'ujian_id'      => $ujian2->id,
            'siswa_id'      => $fx['siswa']->id,
            'nilai_awal'    => 0,
            'nilai_akhir'   => 0,
            'jumlah_remidi' => 0,
            'waktu_mulai'   => Carbon::create(2026, 10, 11, 8, 0),
            'waktu_selesai' => null,
            'status'        => 'sedang_dikerjakan',
        ]);

        // Rata-rata riil: Tugas=80.0, Kuis=90.0, Ujian=70.0 -> Keseluruhan=(80+90+70)/3 = 80.0 (Baik)
        // Mapel Matematika: Tugas=80.0, Kuis=90.0 -> 85.0 (Tuntas >= 75)
        // Mapel IPA Terpadu: Ujian=70.0 -> 70.0 (Belum Tuntas < 75)
        $response = $this->actingAs($fx['user'])
            ->get(route('siswa.dashboard', ['tab' => 'statistik']));

        $response->assertOk()
            ->assertSee('80,0')
            ->assertSee('90,0')
            ->assertSee('70,0')
            ->assertSee('85,0')
            ->assertSee('Baik')
            ->assertSee('1/2')
            ->assertDontSee('Belum Ada Riwayat Nilai');

        $response->assertViewHas('statistik', function ($stat) {
            $mapels = collect($stat['list_nilai_mapel'])->keyBy('nama');

            return $stat['has_real_data'] === true
                && $stat['rata_rata_tugas'] === 80.0
                && $stat['rata_rata_quiz'] === 90.0
                && $stat['rata_rata_ujian'] === 70.0
                && $stat['rata_rata_semua'] === 80.0
                && $stat['kategori_nilai'] === 'Baik'
                && $stat['tugas_dinilai'] === 1
                && $stat['tugas_total'] === 2
                && $stat['kuis_dinilai'] === 1
                && $stat['kuis_total'] === 2
                && $stat['ujian_dinilai'] === 1
                && $stat['ujian_total'] === 2
                && $mapels['Matematika']['nilai'] === 85.0
                && $mapels['Matematika']['tuntas'] === true
                && $mapels['IPA Terpadu']['nilai'] === 70.0
                && $mapels['IPA Terpadu']['tuntas'] === false
                && $stat['perkembangan_nilai']['has_chart_data'] === true
                && count($stat['perkembangan_nilai']['tugas']['coords']) === 1
                && count($stat['perkembangan_nilai']['kuiz']['coords']) === 1
                && count($stat['perkembangan_nilai']['ujian']['coords']) === 1;
        });

        // Verifikasi endpoint API juga mengembalikan data riil yang konsisten
        $this->actingAs($fx['user'], 'sanctum')
            ->getJson('/api/siswa/dashboard')
            ->assertOk()
            ->assertJsonPath('data.evaluasi.has_real_data', true)
            ->assertJsonPath('data.evaluasi.rata_rata_tugas', 80)
            ->assertJsonPath('data.evaluasi.rata_rata_quiz', 90)
            ->assertJsonPath('data.evaluasi.rata_rata_ujian', 70)
            ->assertJsonPath('data.evaluasi.rata_rata_semua', 80)
            ->assertJsonPath('data.evaluasi.kategori_nilai', 'Baik');
    }
}
