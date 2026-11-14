<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\GuruMapel;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\PengampuKelas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuruPengampuUniquePerMapelKelasTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Mapel $mtk;
    private Mapel $ipa;
    private Kelas $kelas7A;
    private Kelas $kelas7B;
    private Kelas $kelas7C;
    private Guru $guruAmba;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role'   => 'operator',
            'status' => 'aktif',
        ]);

        $this->mtk = Mapel::create(['nama_mapel' => 'Matematika', 'kkm' => 75]);
        $this->ipa = Mapel::create(['nama_mapel' => 'IPA', 'kkm' => 75]);

        $this->kelas7A = Kelas::create(['nama_kelas' => '7-A', 'tingkatan' => 7]);
        $this->kelas7B = Kelas::create(['nama_kelas' => '7-B', 'tingkatan' => 7]);
        $this->kelas7C = Kelas::create(['nama_kelas' => '7-C', 'tingkatan' => 7]);

        // Pak Amba mengampu Matematika di 7-A dan 7-B
        $this->guruAmba = Guru::create([
            'nama'   => 'Pak Amba',
            'nuptk'  => '1234567890123456',
            'status' => 'aktif',
        ]);
        $gmAmbaMtk = GuruMapel::create([
            'guru_id'  => $this->guruAmba->id,
            'mapel_id' => $this->mtk->id,
        ]);
        PengampuKelas::create(['guru_mapel_id' => $gmAmbaMtk->id, 'kelas_id' => $this->kelas7A->id]);
        PengampuKelas::create(['guru_mapel_id' => $gmAmbaMtk->id, 'kelas_id' => $this->kelas7B->id]);
    }

    public function test_guru_lain_tidak_bisa_mengampu_mapel_yang_sama_di_kelas_yang_sudah_diampu_saat_create_guru(): void
    {
        // Mas Rusdi mencoba ditambahkan mengampu Matematika di 7-A (yang sudah diampu Pak Amba)
        $response = $this->actingAs($this->admin)->post(route('guru.store'), [
            'nama'        => 'Mas Rusdi',
            'nuptk'       => '9876543210987654',
            'assignments' => [
                [
                    'mapel_id'  => $this->mtk->id,
                    'kelas_ids' => [$this->kelas7A->id],
                ],
            ],
        ]);

        $response->assertSessionHasErrors('assignments');
        $this->assertDatabaseMissing('gurus', ['nama' => 'Mas Rusdi']);
    }

    public function test_guru_lain_bisa_mengampu_mapel_berbeda_di_kelas_yang_sama(): void
    {
        // Mas Rusdi mengampu IPA di 7-A dan 7-B (karena belum ada guru IPA di 7-A dan 7-B)
        $response = $this->actingAs($this->admin)->post(route('guru.store'), [
            'nama'        => 'Mas Rusdi',
            'nuptk'       => '9876543210987654',
            'assignments' => [
                [
                    'mapel_id'  => $this->ipa->id,
                    'kelas_ids' => [$this->kelas7A->id, $this->kelas7B->id],
                ],
            ],
        ]);

        $response->assertRedirect(route('guru.index'));
        $rusdi = Guru::where('nama', 'Mas Rusdi')->first();
        $this->assertNotNull($rusdi);

        // Pastikan Mas Rusdi terdaftar mengampu IPA di 7-A dan 7-B
        $gmRusdiIpa = GuruMapel::where('guru_id', $rusdi->id)->where('mapel_id', $this->ipa->id)->first();
        $this->assertNotNull($gmRusdiIpa);
        $this->assertDatabaseHas('pengampu_kelas', [
            'guru_mapel_id' => $gmRusdiIpa->id,
            'kelas_id'      => $this->kelas7A->id,
        ]);
        $this->assertDatabaseHas('pengampu_kelas', [
            'guru_mapel_id' => $gmRusdiIpa->id,
            'kelas_id'      => $this->kelas7B->id,
        ]);
    }

    public function test_guru_lain_bisa_mengampu_mapel_sama_di_kelas_yang_belum_diampu(): void
    {
        // Mas Rusdi mengampu Matematika di 7-C (karena 7-C belum diampu Pak Amba)
        $response = $this->actingAs($this->admin)->post(route('guru.store'), [
            'nama'        => 'Mas Rusdi',
            'nuptk'       => '9876543210987654',
            'assignments' => [
                [
                    'mapel_id'  => $this->mtk->id,
                    'kelas_ids' => [$this->kelas7C->id],
                ],
            ],
        ]);

        $response->assertRedirect(route('guru.index'));
        $rusdi = Guru::where('nama', 'Mas Rusdi')->first();
        $this->assertNotNull($rusdi);

        $gmRusdiMtk = GuruMapel::where('guru_id', $rusdi->id)->where('mapel_id', $this->mtk->id)->first();
        $this->assertNotNull($gmRusdiMtk);
        $this->assertDatabaseHas('pengampu_kelas', [
            'guru_mapel_id' => $gmRusdiMtk->id,
            'kelas_id'      => $this->kelas7C->id,
        ]);
    }

    public function test_guru_mapel_controller_store_menolak_jika_mapel_di_kelas_sudah_diampu_guru_lain(): void
    {
        $rusdi = Guru::create([
            'nama'   => 'Mas Rusdi',
            'nuptk'  => '9876543210987654',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->admin)->post(route('guru-mapel.store'), [
            'guru_id'   => $rusdi->id,
            'mapel_id'  => $this->mtk->id,
            'kelas_ids' => [$this->kelas7A->id, $this->kelas7B->id],
        ]);

        $response->assertSessionHas('error');

        // Pastikan tidak ada pengampu_kelas baru untuk Mas Rusdi di Matematika
        $gmRusdi = GuruMapel::where('guru_id', $rusdi->id)->where('mapel_id', $this->mtk->id)->first();
        if ($gmRusdi) {
            $this->assertDatabaseMissing('pengampu_kelas', [
                'guru_mapel_id' => $gmRusdi->id,
                'kelas_id'      => $this->kelas7A->id,
            ]);
        }
    }

    public function test_eloquent_pengampu_kelas_model_saving_mencegah_duplikasi_mapel_per_kelas(): void
    {
        $rusdi = Guru::create([
            'nama'   => 'Mas Rusdi',
            'nuptk'  => '9876543210987654',
            'status' => 'aktif',
        ]);
        $gmRusdiMtk = GuruMapel::create([
            'guru_id'  => $rusdi->id,
            'mapel_id' => $this->mtk->id,
        ]);

        $this->expectException(\InvalidArgumentException::class);

        // Mencoba create PengampuKelas di 7-A untuk Mas Rusdi pada mapel Matematika
        PengampuKelas::create([
            'guru_mapel_id' => $gmRusdiMtk->id,
            'kelas_id'      => $this->kelas7A->id,
        ]);
    }

    public function test_guru_update_menolak_jika_mapel_di_kelas_sudah_diampu_guru_lain(): void
    {
        $rusdi = Guru::create([
            'nama'   => 'Mas Rusdi',
            'nuptk'  => '9876543210987654',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->admin)->put(route('guru.update', $rusdi->id), [
            'nama'          => 'Mas Rusdi',
            'nuptk'         => '9876543210987654',
            'new_mapel_id'  => $this->mtk->id,
            'new_kelas_ids' => [$this->kelas7A->id],
        ]);

        $response->assertSessionHasErrors('new_kelas_ids');
    }

    public function test_mapel_update_menolak_jika_kelas_sudah_diampu_guru_lain(): void
    {
        $rusdi = Guru::create([
            'nama'   => 'Mas Rusdi',
            'nuptk'  => '9876543210987654',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->admin)->put(route('mapel.update', $this->mtk->id), [
            'nama_mapel'    => 'Matematika',
            'kkm'           => 75,
            'new_guru_id'   => $rusdi->id,
            'new_kelas_ids' => [$this->kelas7A->id],
        ]);

        $response->assertSessionHasErrors('new_kelas_ids');
    }
}
