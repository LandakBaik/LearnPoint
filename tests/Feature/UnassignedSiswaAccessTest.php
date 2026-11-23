<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Guru;
use App\Models\GuruMapel;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Materi;
use App\Models\PengampuKelas;
use App\Models\Quiz;
use App\Models\Siswa;
use App\Models\Tugas;
use App\Models\Ujian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class UnassignedSiswaAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_unassigned_siswa_sees_empty_state_and_cannot_access_other_class_content(): void
    {
        $period = AcademicPeriod::firstOrCreate(
            ['tahun_ajaran' => '2026/2027', 'semester' => 'ganjil'],
            ['is_active' => true]
        );

        $kelas = Kelas::create([
            'nama_kelas' => '7-A',
            'tingkatan'  => 7,
        ]);

        $guru = Guru::create([
            'nama'   => 'Guru Pengampu',
            'nuptk'  => '1234567890123499',
            'status' => 'aktif',
        ]);

        $mapel = Mapel::create([
            'nama_mapel' => 'Fisika Kuantum',
            'kkm'        => 75,
            'tipe_mapel' => 'teori',
        ]);

        $guruMapel = GuruMapel::create([
            'guru_id'  => $guru->id,
            'mapel_id' => $mapel->id,
        ]);

        $pengampuKelas = PengampuKelas::create([
            'guru_mapel_id'      => $guruMapel->id,
            'kelas_id'           => $kelas->id,
            'academic_period_id' => $period->id,
        ]);

        Materi::create([
            'judul'             => 'Materi Rahasia 7A',
            'file_materi'       => 'materi/rahasia.pdf',
            'pengampu_kelas_id' => $pengampuKelas->id,
            'id_grub_materi'    => (string) Str::uuid(),
        ]);

        Tugas::create([
            'judul'             => 'Tugas Rahasia 7A',
            'deadline'          => now()->addDays(2),
            'tipe'              => 'upload',
            'pengampu_kelas_id' => $pengampuKelas->id,
        ]);

        $quiz = Quiz::create([
            'judul'             => 'Kuis Rahasia 7A',
            'status'            => 'published',
            'pengampu_kelas_id' => $pengampuKelas->id,
        ]);

        $ujian = Ujian::create([
            'judul'             => 'Ujian Rahasia 7A',
            'status'            => 'published',
            'durasi_menit'      => 60,
            'waktu_mulai'       => now()->subMinutes(5),
            'deadline'          => now()->addHour(),
            'pengampu_kelas_id' => $pengampuKelas->id,
        ]);

        // Siswa baru yang belum ditempatkan di kelas manapun
        $siswa = Siswa::create([
            'nama_siswa'    => 'Siswa Tanpa Kelas',
            'nis'           => '20269999',
            'alamat'        => 'Jl. Baru No. 9',
            'tanggal_lahir' => '2013-03-10',
            'jenis_kelamin' => 'L',
            'agama'         => 'Islam',
            'wali_murid'    => 'Wali Baru',
            'nohp_wali'     => '081234567899',
            'status'        => 'aktif',
        ]);

        $user = User::factory()->create([
            'role'     => 'siswa',
            'status'   => 'aktif',
            'siswa_id' => $siswa->id,
        ]);

        // 1. Dashboard: boleh diakses, tampil banner belum ditempatkan, tidak tampil mapel/materi/tugas
        $this->actingAs($user)
            ->get(route('siswa.dashboard'))
            ->assertOk()
            ->assertSee('Belum Ditempatkan di Kelas')
            ->assertDontSee('Fisika Kuantum')
            ->assertDontSee('Materi Rahasia 7A')
            ->assertDontSee('Tugas Rahasia 7A');

        // 2. Halaman Mapel / Materi: kosong + banner, akses langsung ke mapel ditolak (403)
        $this->actingAs($user)
            ->get(route('siswa.mapel'))
            ->assertOk()
            ->assertSee('Belum Ditempatkan di Kelas')
            ->assertDontSee('Fisika Kuantum');

        $this->actingAs($user)
            ->get(route('siswa.mapel.show', $mapel->id))
            ->assertForbidden();

        // 3. Halaman Tugas: kosong + banner
        $this->actingAs($user)
            ->get(route('siswa.tugas'))
            ->assertOk()
            ->assertSee('Belum Ditempatkan di Kelas')
            ->assertDontSee('Tugas Rahasia 7A');

        // 4. Halaman Kuis: kosong + banner, akses langsung ke kuis ditolak (403)
        $this->actingAs($user)
            ->get(route('siswa.kuis'))
            ->assertOk()
            ->assertSee('Belum Ditempatkan di Kelas')
            ->assertDontSee('Fisika Kuantum')
            ->assertDontSee('Kuis Rahasia 7A');

        $this->actingAs($user)
            ->get(route('siswa.kuis.show', $quiz->id))
            ->assertForbidden();

        // 5. Halaman Ujian: kosong + banner, akses langsung ke ujian ditolak (403)
        $this->actingAs($user)
            ->get(route('siswa.ujian.index'))
            ->assertOk()
            ->assertSee('Belum Ditempatkan di Kelas')
            ->assertDontSee('Ujian Rahasia 7A');

        $this->actingAs($user)
            ->get(route('siswa.ujian.show', $ujian->id))
            ->assertForbidden();

        // 6. API Siswa: pastikan endpoint API juga konsisten saat siswa belum ditempatkan di kelas
        $this->actingAs($user, 'sanctum')
            ->getJson('/api/siswa/dashboard')
            ->assertOk()
            ->assertJsonPath('data.kelas', null)
            ->assertJsonPath('data.statistics.total_mapel', 0)
            ->assertJsonPath('data.evaluasi.rata_rata_semua', 0)
            ->assertJsonPath('data.evaluasi.kategori_nilai', 'Belum Ada Data')
            ->assertJsonPath('data.evaluasi.nilai_mapel', []);

        $this->actingAs($user, 'sanctum')->getJson('/api/siswa/mapel')->assertNotFound();
        $this->actingAs($user, 'sanctum')->getJson('/api/siswa/mapel/' . $mapel->id)->assertNotFound();
        $this->actingAs($user, 'sanctum')->getJson('/api/siswa/tugas')->assertNotFound();
        $this->actingAs($user, 'sanctum')->getJson('/api/siswa/kuis')->assertNotFound();
        $this->actingAs($user, 'sanctum')->getJson('/api/siswa/ujian')->assertNotFound();
        $this->actingAs($user, 'sanctum')->getJson('/api/siswa/jadwal')->assertNotFound();
    }
}
