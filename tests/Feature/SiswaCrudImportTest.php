<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class SiswaCrudImportTest extends TestCase
{
    use RefreshDatabase;

    protected function operator(): User
    {
        return User::factory()->create([
            'role' => 'operator',
            'status' => 'aktif',
        ]);
    }

    protected function kelas(string $nama): Kelas
    {
        return Kelas::create([
            'nama_kelas' => $nama,
            'tingkatan' => 7,
        ]);
    }

    protected function siswaPayload(string $nis): array
    {
        return [
            'nama_siswa' => 'Siswa Uji ' . $nis,
            'nis' => $nis,
            'alamat' => 'Jl. Pengujian No. 1',
            'tanggal_lahir' => '2012-05-14',
            'jenis_kelamin' => 'L',
            'wali_murid' => 'Wali Uji',
            'nohp_wali' => '081234567890',
        ];
    }

    public function test_siswa_crud_uses_class_membership_pivot(): void
    {
        $operator = $this->operator();
        $kelasAwal = $this->kelas('7-A');
        $kelasBaru = $this->kelas('7-B');

        $response = $this->actingAs($operator)->post(route('siswa.store'), array_merge(
            $this->siswaPayload('20241001'),
            ['kelas_id' => $kelasAwal->id]
        ));

        $response->assertRedirect(route('siswa.index'));
        $siswa = Siswa::where('nis', '20241001')->firstOrFail();
        $this->assertDatabaseHas('anggota_kelases', [
            'siswa_id' => $siswa->id,
            'kelas_id' => $kelasAwal->id,
            'tahun_ajaran' => '2026/2027',
            'semester' => 'ganjil',
        ]);
        $this->assertDatabaseHas('users', ['siswa_id' => $siswa->id, 'role' => 'siswa']);

        $response = $this->actingAs($operator)->put(route('siswa.update', $siswa), array_merge(
            $this->siswaPayload('20241001'),
            ['nama_siswa' => 'Siswa Diperbarui', 'kelas_id' => $kelasBaru->id]
        ));

        $response->assertRedirect(route('siswa.index'));
        $this->assertDatabaseMissing('anggota_kelases', [
            'siswa_id' => $siswa->id,
            'kelas_id' => $kelasAwal->id,
            'tahun_ajaran' => '2026/2027',
            'semester' => 'ganjil',
        ]);
        $this->assertDatabaseHas('anggota_kelases', [
            'siswa_id' => $siswa->id,
            'kelas_id' => $kelasBaru->id,
            'tahun_ajaran' => '2026/2027',
            'semester' => 'ganjil',
        ]);

        $response = $this->actingAs($operator)->patch(route('siswa.toggle-status', $siswa));

        $response->assertRedirect(route('siswa.index'));
        $this->assertDatabaseHas('siswas', ['id' => $siswa->id, 'status' => 'nonaktif']);
        $this->assertDatabaseHas('users', ['siswa_id' => $siswa->id, 'status' => 'nonaktif']);
    }

    public function test_siswa_csv_import_creates_students_accounts_and_class_memberships(): void
    {
        $operator = $this->operator();
        $kelas = $this->kelas('7-A');
        $csv = implode("\n", [
            'nama_siswa,nis,alamat,tanggal_lahir,jenis_kelamin,wali_murid,nohp_wali,kelas',
            'Siswa CSV,20241002,Jl. CSV No. 2,2012-08-20,L,Wali CSV,081234567891,7-A',
        ]);

        $response = $this->actingAs($operator)->post(route('siswa.import-csv'), [
            'csv_file' => UploadedFile::fake()->createWithContent('siswa.csv', $csv),
        ]);

        $response->assertRedirect(route('siswa.index'));
        $siswa = Siswa::where('nis', '20241002')->firstOrFail();
        $this->assertSame('Siswa CSV', $siswa->nama_siswa);
        $this->assertDatabaseHas('anggota_kelases', [
            'siswa_id' => $siswa->id,
            'kelas_id' => $kelas->id,
            'tahun_ajaran' => '2026/2027',
            'semester' => 'ganjil',
        ]);
        $this->assertDatabaseHas('users', [
            'username' => '20241002',
            'siswa_id' => $siswa->id,
        ]);
    }

    public function test_siswa_csv_import_leaves_class_null_when_class_column_is_empty(): void
    {
        $operator = $this->operator();
        $csv = implode("\n", [
            'nama_siswa,nis,alamat,tanggal_lahir,jenis_kelamin,wali_murid,nohp_wali,kelas',
            'Siswa Tanpa Kelas,20241003,Jl. CSV No. 3,2012-09-20,P,Wali Tanpa Kelas,081234567892,',
        ]);

        $response = $this->actingAs($operator)->post(route('siswa.import-csv'), [
            'csv_file' => UploadedFile::fake()->createWithContent('siswa-tanpa-kelas.csv', $csv),
        ]);

        $response->assertRedirect(route('siswa.index'));
        $siswa = Siswa::where('nis', '20241003')->firstOrFail();

        $this->assertDatabaseMissing('anggota_kelases', ['siswa_id' => $siswa->id]);
        $this->assertNull($siswa->kelas()->first());
    }

    public function test_siswa_csv_template_matches_import_format_and_leaves_class_empty(): void
    {
        $response = $this->actingAs($this->operator())->get(route('siswa.template-csv'));

        $response->assertOk();
        $content = $response->streamedContent();
        $lines = array_values(array_filter(preg_split('/\r\n|\n|\r/', trim($content))));

        $this->assertSame(
            'nama_siswa,nis,alamat,tanggal_lahir,jenis_kelamin,wali_murid,nohp_wali,kelas',
            $lines[0]
        );
        $this->assertStringEndsWith(',', $lines[1]);
        $this->assertStringEndsWith(',', $lines[2]);
        $this->assertStringEndsWith(',', $lines[3]);
    }
}
