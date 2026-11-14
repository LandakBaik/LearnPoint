<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\GuruMapel;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Menjawab;
use App\Models\Nilai;
use App\Models\PengampuKelas;
use App\Models\Power;
use App\Models\Quiz;
use App\Models\Siswa;
use App\Models\SiswaPower;
use App\Models\Soal;
use App\Models\Tugas;
use App\Models\Ujian;
use App\Models\User;
use App\Services\AcademicRelocationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AcademicRelocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_studi_kasus_1_swap_guru_serah_terima_preserves_tugas_and_grades(): void
    {
        $guruSaya = Guru::create(['nuptk' => '1111222233334441', 'nama' => 'Guru Saya']);
        $guruAbim = Guru::create(['nuptk' => '1111222233334442', 'nama' => 'Guru Abim']);

        $mapel = Mapel::create(['nama_mapel' => 'Matematika', 'kkm' => 75]);
        $kelas7A = Kelas::create(['nama_kelas' => '7A', 'tingkatan' => 7]);
        $kelas7B = Kelas::create(['nama_kelas' => '7B', 'tingkatan' => 7]);

        // Human error: Saya ditugaskan ke 7B, Abim ke 7A
        $gmSaya = GuruMapel::create(['guru_id' => $guruSaya->id, 'mapel_id' => $mapel->id]);
        $gmAbim = GuruMapel::create(['guru_id' => $guruAbim->id, 'mapel_id' => $mapel->id]);

        $pk7A = PengampuKelas::create(['guru_mapel_id' => $gmAbim->id, 'kelas_id' => $kelas7A->id]);
        $pk7B = PengampuKelas::create(['guru_mapel_id' => $gmSaya->id, 'kelas_id' => $kelas7B->id]);

        // Tugas sudah terlanjur dibuat dan siswa sudah mengerjakan
        $tugas7A = Tugas::create([
            'judul' => 'Tugas Aljabar 7A',
            'deadline' => now()->addDays(2),
            'tipe' => 'pilihan_ganda',
            'pengampu_kelas_id' => $pk7A->id,
        ]);

        $siswa7A = Siswa::create([
            'nis' => '7001',
            'nama_siswa' => 'Budi 7A',
            'alamat' => 'Alamat',
            'tanggal_lahir' => '2013-01-01',
            'jenis_kelamin' => 'L',
            'wali_murid' => 'Wali',
            'nohp_wali' => '08123456789',
        ]);

        Nilai::create([
            'siswa_id' => $siswa7A->id,
            'tugas_id' => $tugas7A->id,
            'nilai' => 90,
            'status' => 'selesai',
        ]);

        $service = app(AcademicRelocationService::class);

        // Tukar (Swap) 7A dan 7B dengan mode serah_terima
        $result = $service->reassignOrSwapGuru($pk7A, $guruSaya->id, 'serah_terima', $pk7B->id);

        $this->assertEquals('swap_serah_terima', $result['type']);
        $this->assertEquals($gmSaya->id, $pk7A->fresh()->guru_mapel_id);
        $this->assertEquals($gmAbim->id, $pk7B->fresh()->guru_mapel_id);

        // Nilai siswa 7A tetap utuh 100%
        $this->assertDatabaseHas('nilais', [
            'siswa_id' => $siswa7A->id,
            'tugas_id' => $tugas7A->id,
            'nilai' => 90,
        ]);
    }

    public function test_studi_kasus_2a_relocate_same_class_different_mapel_keeps_grades(): void
    {
        $guru = Guru::create(['nuptk' => '2222333344445555', 'nama' => 'Guru Multi Mapel']);
        $mapelIpa = Mapel::create(['nama_mapel' => 'IPA', 'kkm' => 75]);
        $mapelBing = Mapel::create(['nama_mapel' => 'Bahasa Inggris', 'kkm' => 75]);
        $kelas7A = Kelas::create(['nama_kelas' => '7A', 'tingkatan' => 7]);

        $gmIpa = GuruMapel::create(['guru_id' => $guru->id, 'mapel_id' => $mapelIpa->id]);
        $gmBing = GuruMapel::create(['guru_id' => $guru->id, 'mapel_id' => $mapelBing->id]);

        $pkIpa7A = PengampuKelas::create(['guru_mapel_id' => $gmIpa->id, 'kelas_id' => $kelas7A->id]);
        $pkBing7A = PengampuKelas::create(['guru_mapel_id' => $gmBing->id, 'kelas_id' => $kelas7A->id]);

        // Salah input: Tugas IPA terinput di Bahasa Inggris Kelas 7A
        $tugas = Tugas::create([
            'judul' => 'Tugas Ekosistem IPA',
            'deadline' => now()->addDays(3),
            'tipe' => 'pilihan_ganda',
            'pengampu_kelas_id' => $pkBing7A->id,
        ]);

        $siswa = Siswa::create([
            'nis' => '7002',
            'nama_siswa' => 'Siti 7A',
            'alamat' => 'Alamat',
            'tanggal_lahir' => '2013-02-02',
            'jenis_kelamin' => 'P',
            'wali_murid' => 'Wali',
            'nohp_wali' => '08123456780',
        ]);

        Nilai::create([
            'siswa_id' => $siswa->id,
            'tugas_id' => $tugas->id,
            'nilai' => 85,
            'status' => 'selesai',
        ]);

        $service = app(AcademicRelocationService::class);
        $service->relocateActivity('tugas', $tugas->id, $pkIpa7A, 'pindah_aman', $guru->id);

        // Tugas berpindah ke IPA 7A dan nilai siswa tetap utuh
        $this->assertEquals($pkIpa7A->id, $tugas->fresh()->pengampu_kelas_id);
        $this->assertDatabaseHas('nilais', [
            'siswa_id' => $siswa->id,
            'tugas_id' => $tugas->id,
            'nilai' => 85,
        ]);
    }

    public function test_studi_kasus_2b_relocate_different_class_resets_grades_and_refunds_power(): void
    {
        Power::ensureDefaultCatalog();
        $power = Power::first();

        $guru = Guru::create(['nuptk' => '3333444455556666', 'nama' => 'Guru Kuis']);
        $mapel = Mapel::create(['nama_mapel' => 'IPA', 'kkm' => 75]);
        $kelas7A = Kelas::create(['nama_kelas' => '7A', 'tingkatan' => 7]);
        $kelas7B = Kelas::create(['nama_kelas' => '7B', 'tingkatan' => 7]);

        $gm = GuruMapel::create(['guru_id' => $guru->id, 'mapel_id' => $mapel->id]);
        $pk7A = PengampuKelas::create(['guru_mapel_id' => $gm->id, 'kelas_id' => $kelas7A->id]);
        $pk7B = PengampuKelas::create(['guru_mapel_id' => $gm->id, 'kelas_id' => $kelas7B->id]);

        $quiz = Quiz::create([
            'judul' => 'Kuis Sel Hewan',
            'level' => 'Level 1',
            'kesulitan' => 'Mudah',
            'status' => 'published',
            'pengampu_kelas_id' => $pk7B->id,
        ]);

        $soal = Soal::create([
            'pertanyaan' => 'Apa fungsi mitokondria?',
            'tipe_soal' => 'single_choice',
            'pilihan' => ['a' => 'Respirasi', 'b' => 'Fotosintesis'],
            'kunci_jawaban' => ['a'],
            'bobot' => 100,
            'urutan' => 1,
            'quiz_id' => $quiz->id,
            'kesulitan' => 'Mudah',
            'level' => 'Level 1',
        ]);

        $siswa7B = Siswa::create([
            'nis' => '7003',
            'nama_siswa' => 'Andi 7B',
            'alamat' => 'Alamat',
            'tanggal_lahir' => '2013-03-03',
            'jenis_kelamin' => 'L',
            'wali_murid' => 'Wali',
            'nohp_wali' => '08123456781',
        ]);

        SiswaPower::initializeDefaultInventory($siswa7B);
        $invBefore = SiswaPower::where('siswa_id', $siswa7B->id)->where('item_id', $power->id)->first();
        $initialQty = $invBefore->jumlah;

        // Siswa 7B memakai 1 Power dan menyelesaikan kuis yang salah kelas
        $invBefore->decrement('jumlah');
        Menjawab::create([
            'soal_id' => $soal->id,
            'siswa_id' => $siswa7B->id,
            'power_id' => $power->id,
            'jawaban_dipilih' => 'a',
            'benar' => true,
            'poin_diperoleh' => 100,
        ]);
        Nilai::create([
            'siswa_id' => $siswa7B->id,
            'quiz_id' => $quiz->id,
            'nilai' => 100,
            'status' => 'selesai',
        ]);

        $service = app(AcademicRelocationService::class);
        $service->relocateActivity('quiz', $quiz->id, $pk7A, 'pindah_reset', $guru->id);

        // Kuis pindah ke 7A, nilai 7B di-reset, dan Power siswa 7B di-refund kembali ke initialQty
        $this->assertEquals($pk7A->id, $quiz->fresh()->pengampu_kelas_id);
        $this->assertDatabaseMissing('nilais', ['quiz_id' => $quiz->id, 'siswa_id' => $siswa7B->id]);
        $this->assertEquals($initialQty, $invBefore->fresh()->jumlah);
    }
}

