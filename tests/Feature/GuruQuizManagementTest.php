<?php

namespace Tests\Feature;

use App\Models\AnggotaKelas;
use App\Models\Guru;
use App\Models\GuruMapel;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Notifikasi;
use App\Models\PengampuKelas;
use App\Models\Quiz;
use App\Models\Siswa;
use App\Models\Soal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GuruQuizManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Guru $guru;
    protected User $userGuru;
    protected PengampuKelas $pengampu1;
    protected PengampuKelas $pengampu2;
    protected User $userSiswa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guru = Guru::create([
            'nuptk' => '1234567890123456',
            'nama'  => 'Guru Kuis Tester',
        ]);

        $this->userGuru = User::create([
            'name'     => 'Guru Kuis Tester',
            'username' => 'guru_kuis_test',
            'email'    => 'guru_kuis_test@example.com',
            'password' => Hash::make('password'),
            'role'     => 'guru',
            'guru_id'  => $this->guru->id,
            'status'   => 'aktif',
        ]);

        $mapel = Mapel::create([
            'nama_mapel' => 'Matematika Kuis',
            'kkm'        => 75,
        ]);

        $kelas1 = Kelas::create([
            'nama_kelas' => '7A',
            'tingkatan'  => 7,
            'guru_id'    => $this->guru->id,
        ]);

        $kelas2 = Kelas::create([
            'nama_kelas' => '7B',
            'tingkatan'  => 7,
            'guru_id'    => $this->guru->id,
        ]);

        $guruMapel = GuruMapel::create([
            'guru_id'  => $this->guru->id,
            'mapel_id' => $mapel->id,
        ]);

        $this->pengampu1 = PengampuKelas::create([
            'guru_mapel_id' => $guruMapel->id,
            'kelas_id'      => $kelas1->id,
        ]);

        $this->pengampu2 = PengampuKelas::create([
            'guru_mapel_id' => $guruMapel->id,
            'kelas_id'      => $kelas2->id,
        ]);

        $siswa = Siswa::create([
            'nis'           => '777001',
            'nama_siswa'    => 'Siswa Kelas 7A',
            'alamat'        => 'Jl. Uji No. 1',
            'tanggal_lahir' => '2012-01-01',
            'jenis_kelamin' => 'L',
            'wali_murid'    => 'Wali Uji',
            'nohp_wali'     => '081234567890',
            'status'        => 'aktif',
        ]);

        $this->userSiswa = User::create([
            'name'     => 'Siswa Kelas 7A',
            'username' => 'siswa_7a_test',
            'email'    => 'siswa_7a_test@example.com',
            'password' => Hash::make('password'),
            'role'     => 'siswa',
            'siswa_id' => $siswa->id,
            'status'   => 'aktif',
        ]);

        AnggotaKelas::create([
            'siswa_id'     => $siswa->id,
            'kelas_id'     => $kelas1->id,
            'tahun_ajaran' => '2026/2027',
            'semester'     => 'ganjil',
        ]);
    }

    public function test_multi_class_quiz_creation_and_soal_sync(): void
    {
        $response = $this->actingAs($this->userGuru)->post(route('guru.kuis.store'), [
            'judul'              => 'Kuis Aljabar Multi Kelas',
            'deskripsi'          => 'Deskripsi test',
            'pengampu_kelas_ids' => [$this->pengampu1->id, $this->pengampu2->id],
            'kesulitan_aktif'    => ['Mudah'],
            'status'             => 'draft',
        ]);

        $response->assertRedirect();

        $quizzes = Quiz::orderBy('id')->get();
        $this->assertCount(2, $quizzes);
        $this->assertNotNull($quizzes[0]->id_grup_kuis);
        $this->assertEquals($quizzes[0]->id_grup_kuis, $quizzes[1]->id_grup_kuis);

        $firstQuiz = $quizzes[0];
        $secondQuiz = $quizzes[1];

        $this->actingAs($this->userGuru)->post(route('guru.kuis.soal.store', $firstQuiz), [
            'pertanyaan'    => 'Berapakah 2 + 3?',
            'tipe_soal'     => 'single_choice',
            'kesulitan'     => 'Mudah',
            'level'         => 'Level 1',
            'pilihan'       => ['a' => '4', 'b' => '5', 'c' => '6', 'd' => '7'],
            'kunci_jawaban' => 'b',
        ])->assertRedirect();

        $this->assertCount(1, $firstQuiz->soals()->get());
        $this->assertCount(1, $secondQuiz->soals()->get());
        $this->assertEquals('Berapakah 2 + 3?', $secondQuiz->soals()->first()->pertanyaan);

        $firstSoal = $firstQuiz->soals()->first();
        $this->actingAs($this->userGuru)->put(route('guru.kuis.soal.update', [$firstQuiz, $firstSoal]), [
            'pertanyaan'    => 'Berapakah 2 + 4?',
            'tipe_soal'     => 'single_choice',
            'pilihan'       => ['a' => '5', 'b' => '6', 'c' => '7', 'd' => '8'],
            'kunci_jawaban' => 'b',
        ])->assertRedirect();

        $this->assertEquals('Berapakah 2 + 4?', $secondQuiz->soals()->first()->pertanyaan);

        $this->actingAs($this->userGuru)->delete(route('guru.kuis.soal.destroy', [$firstQuiz, $firstSoal]))
            ->assertRedirect();

        $this->assertCount(0, $firstQuiz->soals()->get());
        $this->assertCount(0, $secondQuiz->soals()->get());
    }

    public function test_update_cannot_publish_quiz_without_questions_in_active_arcs(): void
    {
        $quiz = Quiz::create([
            'judul'             => 'Kuis Kosong',
            'pengampu_kelas_id' => $this->pengampu1->id,
            'kesulitan_aktif'   => ['Mudah', 'Sedang'],
            'status'            => 'draft',
        ]);

        $response = $this->actingAs($this->userGuru)->put(route('guru.kuis.update', $quiz), [
            'judul'             => 'Kuis Kosong',
            'pengampu_kelas_id' => $this->pengampu1->id,
            'kesulitan_aktif'   => ['Mudah', 'Sedang'],
            'status'            => 'published',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals('draft', $quiz->fresh()->status);
    }

    public function test_update_allows_unchanged_past_waktu_mulai(): void
    {
        $pastStart = now()->subDays(2)->setSecond(0);
        $futureEnd = now()->addDays(2)->setSecond(0);

        $quiz = Quiz::create([
            'judul'             => 'Kuis Berlangsung',
            'pengampu_kelas_id' => $this->pengampu1->id,
            'kesulitan_aktif'   => ['Mudah'],
            'waktu_mulai'       => $pastStart,
            'waktu_selesai'     => $futureEnd,
            'status'            => 'draft',
        ]);

        $response = $this->actingAs($this->userGuru)->put(route('guru.kuis.update', $quiz), [
            'judul'             => 'Kuis Berlangsung Diedit',
            'pengampu_kelas_id' => $this->pengampu1->id,
            'kesulitan_aktif'   => ['Mudah'],
            'waktu_mulai'       => $pastStart->format('Y-m-d\TH:i'),
            'waktu_selesai'     => $futureEnd->copy()->addDay()->format('Y-m-d\TH:i'),
            'status'            => 'draft',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('Kuis Berlangsung Diedit', $quiz->fresh()->judul);
    }

    public function test_toggle_status_to_published_sends_notification_to_students(): void
    {
        $quiz = Quiz::create([
            'judul'             => 'Kuis Notifikasi',
            'pengampu_kelas_id' => $this->pengampu1->id,
            'kesulitan_aktif'   => ['Mudah'],
            'status'            => 'draft',
        ]);

        Soal::create([
            'quiz_id'       => $quiz->id,
            'pertanyaan'    => '1 + 1 = ?',
            'tipe_soal'     => 'single_choice',
            'kesulitan'     => 'Mudah',
            'level'         => 'Level 1',
            'pilihan'       => ['a' => '1', 'b' => '2', 'c' => '3', 'd' => '4'],
            'kunci_jawaban' => 'b',
            'bobot'         => 100,
            'urutan'        => 1,
        ]);

        $this->actingAs($this->userGuru)
            ->patch(route('guru.kuis.toggle-status', $quiz))
            ->assertSessionHas('success');

        $this->assertEquals('published', $quiz->fresh()->status);
        $this->assertDatabaseHas('notifikasis', [
            'user_id'      => $this->userSiswa->id,
            'tipe'         => 'kuis_baru',
            'id_referensi' => $quiz->id,
        ]);
    }

    public function test_update_soal_can_remove_existing_image(): void
    {
        Storage::fake('public');

        $quiz = Quiz::create([
            'judul'             => 'Kuis Gambar',
            'pengampu_kelas_id' => $this->pengampu1->id,
            'kesulitan_aktif'   => ['Mudah'],
            'status'            => 'draft',
        ]);

        $file = UploadedFile::fake()->image('soal.jpg');
        $storedPath = $file->store('soals', 'public');

        $soal = Soal::create([
            'quiz_id'       => $quiz->id,
            'pertanyaan'    => 'Perhatikan gambar',
            'tipe_soal'     => 'single_choice',
            'kesulitan'     => 'Mudah',
            'level'         => 'Level 1',
            'pilihan'       => ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'],
            'kunci_jawaban' => 'a',
            'gambar'        => $storedPath,
            'bobot'         => 100,
            'urutan'        => 1,
        ]);

        Storage::disk('public')->assertExists($storedPath);

        $this->actingAs($this->userGuru)->put(route('guru.kuis.soal.update', [$quiz, $soal]), [
            'pertanyaan'    => 'Pertanyaan tanpa gambar',
            'tipe_soal'     => 'single_choice',
            'pilihan'       => ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'],
            'kunci_jawaban' => 'a',
            'hapus_gambar'  => '1',
        ])->assertRedirect();

        $this->assertNull($soal->fresh()->gambar);
        Storage::disk('public')->assertMissing($storedPath);
    }
}
