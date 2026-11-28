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
use App\Models\Siswa;
use App\Models\Tugas;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuruRekapNilaiTest extends TestCase
{
    use RefreshDatabase;

    private function createFixture(): array
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

        $kelas7A = Kelas::create(['nama_kelas' => '7-A', 'tingkatan' => 7]);
        $kelas7B = Kelas::create(['nama_kelas' => '7-B', 'tingkatan' => 7]);
        $kelas7C = Kelas::create(['nama_kelas' => '7-C', 'tingkatan' => 7]);

        $guruA = Guru::create(['nama' => 'Guru Alpha', 'nuptk' => '1111222233334444', 'status' => 'aktif']);
        $guruB = Guru::create(['nama' => 'Guru Beta', 'nuptk' => '5555666677778888', 'status' => 'aktif']);

        $userGuruA = User::factory()->create([
            'name'    => 'Guru Alpha',
            'role'    => 'guru',
            'status'  => 'aktif',
            'guru_id' => $guruA->id,
        ]);

        $userGuruB = User::factory()->create([
            'name'    => 'Guru Beta',
            'role'    => 'guru',
            'status'  => 'aktif',
            'guru_id' => $guruB->id,
        ]);

        $mapelMath = Mapel::create(['nama_mapel' => 'Matematika', 'kkm' => 75, 'tipe_mapel' => 'teori']);
        $mapelIpa  = Mapel::create(['nama_mapel' => 'IPA Terpadu', 'kkm' => 75, 'tipe_mapel' => 'teori']);
        $mapelIndo = Mapel::create(['nama_mapel' => 'Bahasa Indonesia', 'kkm' => 75, 'tipe_mapel' => 'teori']);

        // Guru A mengampu Matematika di 7-A dan IPA di 7-B
        $gmAMath = GuruMapel::create(['guru_id' => $guruA->id, 'mapel_id' => $mapelMath->id]);
        $gmAIpa  = GuruMapel::create(['guru_id' => $guruA->id, 'mapel_id' => $mapelIpa->id]);
        $pkA7AMath = PengampuKelas::create([
            'guru_mapel_id'      => $gmAMath->id,
            'kelas_id'           => $kelas7A->id,
            'academic_period_id' => $period->id,
        ]);
        $pkA7BIpa = PengampuKelas::create([
            'guru_mapel_id'      => $gmAIpa->id,
            'kelas_id'           => $kelas7B->id,
            'academic_period_id' => $period->id,
        ]);

        // Guru B mengampu Bahasa Indonesia di 7-A dan Matematika di 7-C
        $gmBIndo = GuruMapel::create(['guru_id' => $guruB->id, 'mapel_id' => $mapelIndo->id]);
        $gmBMath = GuruMapel::create(['guru_id' => $guruB->id, 'mapel_id' => $mapelMath->id]);
        $pkB7AIndo = PengampuKelas::create([
            'guru_mapel_id'      => $gmBIndo->id,
            'kelas_id'           => $kelas7A->id,
            'academic_period_id' => $period->id,
        ]);
        $pkB7CMath = PengampuKelas::create([
            'guru_mapel_id'      => $gmBMath->id,
            'kelas_id'           => $kelas7C->id,
            'academic_period_id' => $period->id,
        ]);

        $siswaA = Siswa::create([
            'nama_siswa' => 'Budi Kelas 7A', 'nis' => '1001', 'alamat' => 'Jl. 1',
            'tanggal_lahir' => '2013-01-01', 'jenis_kelamin' => 'L', 'agama' => 'Islam',
            'wali_murid' => 'Wali A', 'nohp_wali' => '081111111111', 'status' => 'aktif',
        ]);
        $siswaB = Siswa::create([
            'nama_siswa' => 'Citra Kelas 7B', 'nis' => '1002', 'alamat' => 'Jl. 2',
            'tanggal_lahir' => '2013-02-02', 'jenis_kelamin' => 'P', 'agama' => 'Islam',
            'wali_murid' => 'Wali B', 'nohp_wali' => '082222222222', 'status' => 'aktif',
        ]);
        $siswaC = Siswa::create([
            'nama_siswa' => 'Deni Kelas 7C', 'nis' => '1003', 'alamat' => 'Jl. 3',
            'tanggal_lahir' => '2013-03-03', 'jenis_kelamin' => 'L', 'agama' => 'Islam',
            'wali_murid' => 'Wali C', 'nohp_wali' => '083333333333', 'status' => 'aktif',
        ]);

        AnggotaKelas::create(['siswa_id' => $siswaA->id, 'kelas_id' => $kelas7A->id, 'academic_period_id' => $period->id]);
        AnggotaKelas::create(['siswa_id' => $siswaB->id, 'kelas_id' => $kelas7B->id, 'academic_period_id' => $period->id]);
        AnggotaKelas::create(['siswa_id' => $siswaC->id, 'kelas_id' => $kelas7C->id, 'academic_period_id' => $period->id]);

        // Tugas & Nilai
        $tugasAMath = Tugas::create([
            'judul' => 'Tugas Math 7A', 'deadline' => Carbon::now()->addDays(3),
            'tipe' => 'upload', 'pengampu_kelas_id' => $pkA7AMath->id,
        ]);
        Nilai::create(['siswa_id' => $siswaA->id, 'tugas_id' => $tugasAMath->id, 'nilai' => 88, 'status' => 'dinilai']);

        $tugasAIpa = Tugas::create([
            'judul' => 'Tugas IPA 7B', 'deadline' => Carbon::now()->addDays(3),
            'tipe' => 'upload', 'pengampu_kelas_id' => $pkA7BIpa->id,
        ]);
        Nilai::create(['siswa_id' => $siswaB->id, 'tugas_id' => $tugasAIpa->id, 'nilai' => 60, 'status' => 'dinilai']);

        // Nilai siswa A pada mapel Bahasa Indonesia milik Guru B (TIDAK BOLEH masuk hitungan Guru A)
        $tugasBIndo = Tugas::create([
            'judul' => 'Tugas Indo 7A', 'deadline' => Carbon::now()->addDays(3),
            'tipe' => 'upload', 'pengampu_kelas_id' => $pkB7AIndo->id,
        ]);
        Nilai::create(['siswa_id' => $siswaA->id, 'tugas_id' => $tugasBIndo->id, 'nilai' => 40, 'status' => 'dinilai']);

        // Nilai siswa C pada kelas 7-C milik Guru B
        $tugasBMathC = Tugas::create([
            'judul' => 'Tugas Math 7C', 'deadline' => Carbon::now()->addDays(3),
            'tipe' => 'upload', 'pengampu_kelas_id' => $pkB7CMath->id,
        ]);
        Nilai::create(['siswa_id' => $siswaC->id, 'tugas_id' => $tugasBMathC->id, 'nilai' => 95, 'status' => 'dinilai']);

        return compact(
            'period', 'kelas7A', 'kelas7B', 'kelas7C',
            'guruA', 'guruB', 'userGuruA', 'userGuruB',
            'mapelMath', 'mapelIpa', 'mapelIndo',
            'siswaA', 'siswaB', 'siswaC'
        );
    }

    public function test_guru_rekap_nilai_auto_selects_first_mapel_and_kelas_without_semua_options(): void
    {
        $fx = $this->createFixture();

        // Default: otomatis memilih mapel pertama (IPA Terpadu) & kelas pertamanya (7-B)
        $response = $this->actingAs($fx['userGuruA'])
            ->get(route('guru.rekap-nilai.index'));

        $response->assertOk()
            ->assertSee('Pilih Mata Pelajaran')
            ->assertSee('Pilih Kelas')
            ->assertSee('Tingkat 7')
            ->assertDontSee('Semua Mapel')
            ->assertDontSee('Semua Kelas')
            ->assertDontSee('Leger Mapel')
            ->assertSee('IPA Terpadu')
            ->assertSee('Matematika')
            ->assertSee('Citra Kelas 7B')
            ->assertDontSee('Budi Kelas 7A')
            ->assertDontSee('Deni Kelas 7C')
            ->assertDontSee('Bahasa Indonesia')
            ->assertDontSee('7-C');

        $response->assertViewHas('selectedMapelId', $fx['mapelIpa']->id);
        $response->assertViewHas('selectedKelasId', $fx['kelas7B']->id);
        $response->assertViewHas('rows', function ($rows) use ($fx) {
            $items = collect($rows->items());
            $rowB = $items->firstWhere('siswa_id', $fx['siswaB']->id);

            return $items->count() === 1
                && $rowB !== null
                && (float) $rowB['nilai_akhir'] === 60.0
                && $rowB['status_ketuntasan'] === 'belum_tuntas';
        });
    }

    public function test_guru_filter_by_mapel_only_shows_students_in_classes_taught_for_that_mapel(): void
    {
        $fx = $this->createFixture();

        $response = $this->actingAs($fx['userGuruA'])
            ->get(route('guru.rekap-nilai.index', ['mapel_id' => $fx['mapelMath']->id]));

        $response->assertOk()
            ->assertSee('Budi Kelas 7A')
            ->assertDontSee('Citra Kelas 7B')
            ->assertDontSee('Deni Kelas 7C')
            ->assertViewHas('selectedMapelId', $fx['mapelMath']->id)
            ->assertViewHas('selectedKelasId', $fx['kelas7A']->id)
            ->assertViewHas('kelasList', fn ($kelasList) => $kelasList->pluck('id')->all() === [$fx['kelas7A']->id])
            ->assertViewHas('mapelList', fn ($mapelList) => $mapelList->count() === 2)
            ->assertViewHas('rows', function ($rows) use ($fx) {
                $items = collect($rows->items());
                $rowA = $items->firstWhere('siswa_id', $fx['siswaA']->id);

                // Nilai Budi harus 88.00 (hanya Matematika Guru A, bukan rata-rata dengan 40 dari Bahasa Indonesia Guru B)
                return $items->count() === 1
                    && $rowA !== null
                    && (float) $rowA['nilai_akhir'] === 88.0
                    && $rowA['status_ketuntasan'] === 'tuntas';
            });
    }

    public function test_guru_cannot_bypass_scope_by_tampering_kelas_or_mapel_query_params(): void
    {
        $fx = $this->createFixture();

        $response = $this->actingAs($fx['userGuruA'])
            ->get(route('guru.rekap-nilai.index', [
                'kelas_id' => $fx['kelas7C']->id,
                'mapel_id' => $fx['mapelIndo']->id,
            ]));

        $response->assertOk()
            ->assertDontSee('Deni Kelas 7C')
            ->assertDontSee('Bahasa Indonesia');

        // Fallback aman ke mapel & kelas pertama yang benar-benar diampu Guru A
        $response->assertViewHas('selectedMapelId', $fx['mapelIpa']->id);
        $response->assertViewHas('selectedKelasId', $fx['kelas7B']->id);
    }

    public function test_guru_siswa_detail_only_shows_taught_subjects_and_forbids_unassigned_student(): void
    {
        $fx = $this->createFixture();

        // Guru A boleh melihat detail Budi (kelas 7-A), tapi hanya mapel Matematika miliknya
        $okResponse = $this->actingAs($fx['userGuruA'])
            ->get(route('guru.rekap-nilai.siswa', $fx['siswaA']->id));

        $okResponse->assertOk()
            ->assertSee('Budi Kelas 7A')
            ->assertSee('Matematika')
            ->assertSee('Tugas Math 7A')
            ->assertDontSee('Bahasa Indonesia')
            ->assertDontSee('Tugas Indo 7A');

        // Guru A dilarang melihat detail Deni (kelas 7-C yang tidak diampu)
        $this->actingAs($fx['userGuruA'])
            ->get(route('guru.rekap-nilai.siswa', $fx['siswaC']->id))
            ->assertForbidden();

        $this->actingAs($fx['userGuruA'])
            ->get(route('guru.rekap-nilai.siswa.pdf', $fx['siswaC']->id))
            ->assertForbidden();
    }

    public function test_guru_can_export_csv_and_pdf_scoped_to_assigned_classes(): void
    {
        $fx = $this->createFixture();

        $csvResponse = $this->actingAs($fx['userGuruA'])
            ->get(route('guru.rekap-nilai.export-csv', ['mapel_id' => $fx['mapelMath']->id]));

        $csvResponse->assertOk();
        $csvContent = $csvResponse->streamedContent();
        $this->assertStringContainsString('Budi Kelas 7A', $csvContent);
        $this->assertStringNotContainsString('Citra Kelas 7B', $csvContent);
        $this->assertStringNotContainsString('Deni Kelas 7C', $csvContent);

        $pdfResponse = $this->actingAs($fx['userGuruA'])
            ->get(route('guru.rekap-nilai.export-pdf', [
                'mapel_id' => $fx['mapelMath']->id,
                'kelas_id' => $fx['kelas7A']->id,
            ]));

        $pdfResponse->assertOk();
    }
}
