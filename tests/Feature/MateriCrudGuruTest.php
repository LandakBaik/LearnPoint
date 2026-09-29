<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\GuruMapel;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Materi;
use App\Models\PengampuKelas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class MateriCrudGuruTest extends TestCase
{
    use RefreshDatabase;

    private function createGuruWithClasses()
    {
        $guru = Guru::create([
            'nama' => 'Guru Test',
            'nip' => '198501012010011001',
            'status' => 'aktif',
        ]);

        $user = User::factory()->create([
            'role' => 'guru',
            'status' => 'aktif',
            'guru_id' => $guru->id,
        ]);

        $mapel = Mapel::create(['nama_mapel' => 'Matematika', 'kkm' => 75]);
        $guruMapel = GuruMapel::create(['guru_id' => $guru->id, 'mapel_id' => $mapel->id]);

        $kelasA = Kelas::create(['nama_kelas' => '7-A', 'tingkatan' => 7]);
        $kelasB = Kelas::create(['nama_kelas' => '7-B', 'tingkatan' => 7]);

        $pkA = PengampuKelas::create(['guru_mapel_id' => $guruMapel->id, 'kelas_id' => $kelasA->id]);
        $pkB = PengampuKelas::create(['guru_mapel_id' => $guruMapel->id, 'kelas_id' => $kelasB->id]);

        return compact('guru', 'user', 'mapel', 'guruMapel', 'kelasA', 'kelasB', 'pkA', 'pkB');
    }

    public function test_guru_can_create_materi_for_multiple_classes(): void
    {
        Storage::fake('public');
        $data = $this->createGuruWithClasses();

        $file = UploadedFile::fake()->create('materi1.pdf', 1000, 'application/pdf');

        $response = $this->actingAs($data['user'])->post(route('guru.materi.store'), [
            'judul' => 'Sistem Persamaan Linear',
            'deskripsi' => 'Petunjuk belajar bab 1',
            'mapel_id' => $data['mapel']->id,
            'tingkatan' => 7,
            'pengampu_kelas_ids' => [$data['pkA']->id, $data['pkB']->id],
            'file_materi' => $file,
            'url_youtube' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);

        $response->assertRedirect(route('guru.materi'));
        $response->assertSessionHas('success');

        $this->assertDatabaseCount('materis', 2);
        
        $materiA = Materi::where('pengampu_kelas_id', $data['pkA']->id)->first();
        $materiB = Materi::where('pengampu_kelas_id', $data['pkB']->id)->first();

        $this->assertNotNull($materiA);
        $this->assertNotNull($materiB);
        $this->assertEquals($materiA->id_grub_materi, $materiB->id_grub_materi);
        $this->assertEquals('Sistem Persamaan Linear', $materiA->judul);

        Storage::disk('public')->assertExists($materiA->file_materi);
    }

    public function test_guru_cannot_create_materi_with_invalid_file_or_missing_fields(): void
    {
        Storage::fake('public');
        $data = $this->createGuruWithClasses();

        $invalidFile = UploadedFile::fake()->create('script.exe', 500, 'application/x-msdownload');

        $response = $this->actingAs($data['user'])->post(route('guru.materi.store'), [
            'judul' => '', // Required
            'mapel_id' => $data['mapel']->id,
            'tingkatan' => 7,
            'pengampu_kelas_ids' => [$data['pkA']->id],
            'file_materi' => $invalidFile,
        ]);

        $response->assertSessionHasErrors(['judul', 'file_materi']);
        $this->assertDatabaseCount('materis', 0);
    }

    public function test_guru_can_update_materi_and_sync_classes(): void
    {
        Storage::fake('public');
        $data = $this->createGuruWithClasses();

        $fileOld = UploadedFile::fake()->create('materi_lama.pdf', 500, 'application/pdf');
        $idGrub = (string) Str::uuid();

        $materiA = Materi::create([
            'judul' => 'Judul Lama',
            'deskripsi' => 'Deskripsi Lama',
            'file_materi' => $fileOld->store('materi_files', 'public'),
            'pengampu_kelas_id' => $data['pkA']->id,
            'id_grub_materi' => $idGrub,
        ]);

        // Edit page
        $editResponse = $this->actingAs($data['user'])->get(route('guru.materi.edit', $idGrub));
        $editResponse->assertOk();
        $editResponse->assertSee('Judul Lama');

        // Update: Ubah judul dan tambah kelas B
        $updateResponse = $this->actingAs($data['user'])->put(route('guru.materi.update', $idGrub), [
            'judul' => 'Judul Baru',
            'deskripsi' => 'Deskripsi Baru',
            'pengampu_kelas_ids' => [$data['pkA']->id, $data['pkB']->id],
        ]);

        $updateResponse->assertRedirect(route('guru.materi'));

        $this->assertDatabaseCount('materis', 2);
        $this->assertDatabaseHas('materis', [
            'id_grub_materi' => $idGrub,
            'judul' => 'Judul Baru',
            'pengampu_kelas_id' => $data['pkB']->id,
        ]);
    }

    public function test_guru_can_delete_materi_group_and_its_file(): void
    {
        Storage::fake('public');
        $data = $this->createGuruWithClasses();

        $file = UploadedFile::fake()->create('materi_hapus.pdf', 500, 'application/pdf');
        $path = $file->store('materi_files', 'public');
        $idGrub = (string) Str::uuid();

        Materi::create([
            'judul' => 'Materi Dihapus',
            'file_materi' => $path,
            'pengampu_kelas_id' => $data['pkA']->id,
            'id_grub_materi' => $idGrub,
        ]);

        Materi::create([
            'judul' => 'Materi Dihapus',
            'file_materi' => $path,
            'pengampu_kelas_id' => $data['pkB']->id,
            'id_grub_materi' => $idGrub,
        ]);

        Storage::disk('public')->assertExists($path);
        $this->assertDatabaseCount('materis', 2);

        $deleteResponse = $this->actingAs($data['user'])->delete(route('guru.materi.destroy', $idGrub));

        $deleteResponse->assertRedirect(route('guru.materi'));
        $deleteResponse->assertSessionHas('success');

        $this->assertDatabaseCount('materis', 0);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_guru_can_unassign_classes_and_auto_delete_when_zero_classes_remain(): void
    {
        Storage::fake('public');
        $data = $this->createGuruWithClasses();

        $file = UploadedFile::fake()->create('materi_sync.pdf', 500, 'application/pdf');
        $path = $file->store('materi_files', 'public');
        $idGrub = (string) Str::uuid();

        Materi::create([
            'judul' => 'Materi Multi Kelas',
            'file_materi' => $path,
            'pengampu_kelas_id' => $data['pkA']->id,
            'id_grub_materi' => $idGrub,
        ]);

        Materi::create([
            'judul' => 'Materi Multi Kelas',
            'file_materi' => $path,
            'pengampu_kelas_id' => $data['pkB']->id,
            'id_grub_materi' => $idGrub,
        ]);

        // 1. Lepas kelas B (sisa 1 kelas: kelas A)
        $response1 = $this->actingAs($data['user'])->put(route('guru.materi.update', $idGrub), [
            'judul' => 'Materi Multi Kelas',
            'pengampu_kelas_ids' => [$data['pkA']->id],
        ]);
        $response1->assertRedirect(route('guru.materi'));
        $this->assertDatabaseCount('materis', 1);
        $this->assertDatabaseHas('materis', ['pengampu_kelas_id' => $data['pkA']->id]);

        // 2. Lepas semua kelas (0 kelas) -> Otomatis hapus materi & file
        $response2 = $this->actingAs($data['user'])->put(route('guru.materi.update', $idGrub), [
            'judul' => 'Materi Multi Kelas',
            'pengampu_kelas_ids' => [],
        ]);
        $response2->assertRedirect(route('guru.materi'));
        $response2->assertSessionHas('success', 'Materi berhasil dihapus dari seluruh kelas.');

        $this->assertDatabaseCount('materis', 0);
        Storage::disk('public')->assertMissing($path);
    }
}
