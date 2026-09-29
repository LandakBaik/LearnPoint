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
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Tests\TestCase;

class MateriTugasStudentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_materi_index_filters_a_group_but_returns_all_its_classes(): void
    {
        $kelasA = Kelas::create(['nama_kelas' => '7-A', 'tingkatan' => 7]);
        $kelasB = Kelas::create(['nama_kelas' => '7-B', 'tingkatan' => 7]);
        $kelasDelapan = Kelas::create(['nama_kelas' => '8-A', 'tingkatan' => 8]);
        $guru = Guru::create([
            'nama' => 'Guru Index',
            'nip' => '1980012345678902',
            'status' => 'aktif',
        ]);
        $guruLain = Guru::create([
            'nama' => 'Guru Lain',
            'nip' => '1980012345678903',
            'status' => 'aktif',
        ]);
        $guruUser = User::factory()->create([
            'role' => 'guru',
            'status' => 'aktif',
            'guru_id' => $guru->id,
        ]);
        $mapel = Mapel::create(['nama_mapel' => 'Matematika', 'kkm' => 75]);
        $mapelLain = Mapel::create(['nama_mapel' => 'IPA', 'kkm' => 75]);

        $guruMapel = GuruMapel::create(['guru_id' => $guru->id, 'mapel_id' => $mapel->id]);
        $assignmentA = PengampuKelas::create(['guru_mapel_id' => $guruMapel->id, 'kelas_id' => $kelasA->id]);
        $assignmentB = PengampuKelas::create(['guru_mapel_id' => $guruMapel->id, 'kelas_id' => $kelasB->id]);
        $assignmentDelapan = PengampuKelas::create(['guru_mapel_id' => $guruMapel->id, 'kelas_id' => $kelasDelapan->id]);

        $guruMapelLain = GuruMapel::create(['guru_id' => $guru->id, 'mapel_id' => $mapelLain->id]);
        PengampuKelas::create(['guru_mapel_id' => $guruMapelLain->id, 'kelas_id' => $kelasA->id]);

        $guruMapelLainGuru = GuruMapel::create(['guru_id' => $guruLain->id, 'mapel_id' => $mapel->id]);
        $assignmentGuruLain = PengampuKelas::create([
            'guru_mapel_id' => $guruMapelLainGuru->id,
            'kelas_id' => $kelasA->id,
        ]);

        $groupId = (string) Str::uuid();
        foreach ([[$assignmentA, 'Aljabar Dasar'], [$assignmentB, 'Aljabar Dasar']] as [$assignment, $title]) {
            Materi::create([
                'judul' => $title,
                'file_materi' => 'materi/aljabar.pdf',
                'pengampu_kelas_id' => $assignment->id,
                'id_grub_materi' => $groupId,
            ]);
        }

        Materi::create([
            'judul' => 'Aljabar Tingkat 8',
            'file_materi' => 'materi/aljabar-8.pdf',
            'pengampu_kelas_id' => $assignmentDelapan->id,
            'id_grub_materi' => (string) Str::uuid(),
        ]);
        Materi::create([
            'judul' => 'Aljabar Pribadi',
            'file_materi' => 'materi/pribadi.pdf',
            'pengampu_kelas_id' => $assignmentGuruLain->id,
            'id_grub_materi' => (string) Str::uuid(),
        ]);

        $request = Request::create('/guru/materi', 'GET', [
            'mapel_id' => $mapel->id,
            'tingkatan' => 7,
            'kelas_id' => $kelasA->id,
            'search' => 'Aljabar Dasar',
        ]);
        $request->setUserResolver(fn () => $guruUser);

        $viewData = app(\App\Http\Controllers\MateriController::class)
            ->index($request)
            ->getData();

        $this->assertCount(1, $viewData['materiGroups']);
        $this->assertCount(2, $viewData['materiGroups']->first());
        $this->assertSame($groupId, $viewData['materiGroups']->keys()->first());
        $this->assertSame(['IPA', 'Matematika'], $viewData['mapels']->pluck('nama_mapel')->all());
        $this->assertSame(['7-A', '7-B'], $viewData['kelasOptions']->pluck('nama_kelas')->all());
        $this->assertSame(['7', '8'], $viewData['tingkatanOptions']->map(fn ($level) => (string) $level)->all());
        $this->assertFalse($viewData['materis']->contains('judul', 'Aljabar Pribadi'));

        $this->actingAs($guruUser)
            ->get(route('guru.materi', [
                'mapel_id' => $mapel->id,
                'tingkatan' => 7,
                'kelas_id' => $kelasA->id,
                'search' => 'Aljabar Dasar',
            ]))
            ->assertOk()
            ->assertSee('Aljabar Dasar')
            ->assertSee('7-A')
            ->assertSee('7-B')
            ->assertDontSee('Aljabar Tingkat 8')
            ->assertDontSee('Aljabar Pribadi');
    }

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
            'id_grub_materi' => (string) Str::uuid(),
        ]);
        Materi::create([
            'judul' => 'Sistem Tata Surya',
            'file_materi' => 'materi/tata-surya.pdf',
            'pengampu_kelas_id' => $pengampuKelasLain->id,
            'id_grub_materi' => (string) Str::uuid(),
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
