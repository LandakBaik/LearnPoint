<?php

namespace Tests\Feature;

use App\Models\AnggotaKelas;
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
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class QuizPowerTest extends TestCase
{
    use RefreshDatabase;

    protected User $userSiswa;
    protected Siswa $siswa;
    protected User $userSiswaLain;
    protected Siswa $siswaLain;
    protected Quiz $quiz;
    protected Soal $soal1;
    protected Soal $soal2;
    protected Soal $soal3;

    protected function setUp(): void
    {
        parent::setUp();

        // Pastikan katalog default Power tersedia
        Power::ensureDefaultCatalog();

        // Buat data Guru, Mapel, Kelas, dan PengampuKelas untuk pengujian terisolasi
        $guru = Guru::create([
            'nuptk' => '9988776655443322',
            'nama' => 'Guru Tester Power',
        ]);

        $mapel = Mapel::create([
            'nama_mapel' => 'Matematika Power Test',
            'kkm' => 75,
        ]);

        $kelas = Kelas::create([
            'nama_kelas' => '7-POWER',
            'tingkatan' => 7,
            'guru_id' => $guru->id,
        ]);

        $guruMapel = GuruMapel::create([
            'guru_id' => $guru->id,
            'mapel_id' => $mapel->id,
        ]);

        $pengampu = PengampuKelas::create([
            'guru_mapel_id' => $guruMapel->id,
            'kelas_id' => $kelas->id,
        ]);

        // Siswa Utama
        $this->siswa = Siswa::create([
            'nis' => '555001',
            'nama_siswa' => 'Siswa Utama Power',
            'alamat' => 'Jl. Pendidikan No. 1',
            'tanggal_lahir' => '2011-05-10',
            'jenis_kelamin' => 'L',
            'wali_murid' => 'Wali Siswa Utama',
            'nohp_wali' => '081234567891',
            'status' => 'aktif',
        ]);

        $this->userSiswa = User::create([
            'name' => 'Siswa Utama Power',
            'username' => 'siswa_power_1',
            'email' => 'siswa_power_1@example.com',
            'password' => Hash::make('password'),
            'role' => 'siswa',
            'siswa_id' => $this->siswa->id,
            'status' => 'aktif',
        ]);

        AnggotaKelas::create([
            'siswa_id' => $this->siswa->id,
            'kelas_id' => $kelas->id,
            'tahun_ajaran' => '2026/2027',
            'semester' => 'ganjil',
        ]);

        // Siswa Lain (untuk menguji isolasi kepemilikan Power)
        $this->siswaLain = Siswa::create([
            'nis' => '555002',
            'nama_siswa' => 'Siswa Lain Power',
            'alamat' => 'Jl. Pendidikan No. 2',
            'tanggal_lahir' => '2011-06-12',
            'jenis_kelamin' => 'P',
            'wali_murid' => 'Wali Siswa Lain',
            'nohp_wali' => '081234567892',
            'status' => 'aktif',
        ]);

        $this->userSiswaLain = User::create([
            'name' => 'Siswa Lain Power',
            'username' => 'siswa_power_2',
            'email' => 'siswa_power_2@example.com',
            'password' => Hash::make('password'),
            'role' => 'siswa',
            'siswa_id' => $this->siswaLain->id,
            'status' => 'aktif',
        ]);

        AnggotaKelas::create([
            'siswa_id' => $this->siswaLain->id,
            'kelas_id' => $kelas->id,
            'tahun_ajaran' => '2026/2027',
            'semester' => 'ganjil',
        ]);

        // Kuis Petualangan yang sudah dipublikasikan
        $this->quiz = Quiz::create([
            'pengampu_kelas_id' => $pengampu->id,
            'judul' => 'Kuis Petualangan Aljabar',
            'deskripsi' => 'Uji mekanisme Power LearnPoint',
            'acak_jawaban' => false,
            'status' => 'published',
        ]);

        // 3 Butir Soal pada Arc Mudah Level 1 (masing-masing bobot 10, total bobot dasar = 30)
        $this->soal1 = Soal::create([
            'quiz_id' => $this->quiz->id,
            'pertanyaan' => 'Berapakah hasil dari 12 + 8?',
            'tipe' => 'pilihan_ganda',
            'kesulitan' => 'Mudah',
            'level' => 'Level 1',
            'pilihan' => [
                'a' => '20 (Jawaban Tepat)',
                'b' => '18',
                'c' => '22',
                'd' => '24',
            ],
            'kunci_jawaban' => 'a',
            'bobot' => 10,
            'urutan' => 1,
        ]);

        $this->soal2 = Soal::create([
            'quiz_id' => $this->quiz->id,
            'pertanyaan' => 'Berapakah hasil dari 9 x 5?',
            'tipe' => 'pilihan_ganda',
            'kesulitan' => 'Mudah',
            'level' => 'Level 1',
            'pilihan' => [
                'a' => '40',
                'b' => '45 (Jawaban Tepat)',
                'c' => '50',
                'd' => '35',
            ],
            'kunci_jawaban' => 'b',
            'bobot' => 10,
            'urutan' => 2,
        ]);

        $this->soal3 = Soal::create([
            'quiz_id' => $this->quiz->id,
            'pertanyaan' => 'Berapakah akar kuadrat dari 81?',
            'tipe' => 'pilihan_ganda',
            'kesulitan' => 'Mudah',
            'level' => 'Level 1',
            'pilihan' => [
                'a' => '7',
                'b' => '8',
                'c' => '9 (Jawaban Tepat)',
                'd' => '10',
            ],
            'kunci_jawaban' => 'c',
            'bobot' => 10,
            'urutan' => 3,
        ]);
    }

    /**
     * Skenario 1: Siswa dapat melihat daftar Power miliknya.
     */
    public function test_siswa_dapat_melihat_daftar_power_miliknya(): void
    {
        $powerClover = Power::where('kode', 'lucky_clover')->firstOrFail();
        $powerKnife = Power::where('kode', 'double_side_knife')->firstOrFail();

        SiswaPower::create([
            'siswa_id' => $this->siswa->id,
            'power_id' => $powerClover->id,
            'jumlah' => 4,
        ]);

        SiswaPower::create([
            'siswa_id' => $this->siswa->id,
            'power_id' => $powerKnife->id,
            'jumlah' => 2,
        ]);

        $response = $this->actingAs($this->userSiswa)
            ->getJson(route('siswa.kuis.powers.index', $this->quiz->id));

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(5, 'powers');

        $powersData = collect($response->json('powers'))->keyBy('code');
        $this->assertSame(4, $powersData['lucky_clover']['qty']);
        $this->assertSame(2, $powersData['double_side_knife']['qty']);
        $this->assertSame(0, $powersData['two_x_score']['qty']);
    }

    /**
     * Skenario 2: Siswa dapat menggunakan Power yang dimiliki.
     */
    public function test_siswa_dapat_menggunakan_power_yang_dimiliki(): void
    {
        $power = Power::where('kode', 'lucky_clover')->firstOrFail();
        SiswaPower::create([
            'siswa_id' => $this->siswa->id,
            'power_id' => $power->id,
            'jumlah' => 3,
        ]);

        $response = $this->actingAs($this->userSiswa)
            ->postJson(route('siswa.kuis.powers.use', $this->quiz->id), [
                'power_id' => $power->id,
                'soal_id' => $this->soal1->id,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.power_code', 'lucky_clover')
            ->assertJsonPath('data.soal_id', $this->soal1->id)
            ->assertJsonPath('data.effect.type', 'hint');

        $this->assertDatabaseHas('menjawabs', [
            'siswa_id' => $this->siswa->id,
            'soal_id' => $this->soal1->id,
            'power_id' => $power->id,
        ]);
    }

    /**
     * Skenario 3: Jumlah Power berkurang 1 setelah berhasil digunakan.
     */
    public function test_jumlah_power_berkurang_satu_setelah_berhasil_digunakan(): void
    {
        $power = Power::where('kode', 'remove_wrong_answer')->firstOrFail();
        SiswaPower::create([
            'siswa_id' => $this->siswa->id,
            'power_id' => $power->id,
            'jumlah' => 2,
        ]);

        $response = $this->actingAs($this->userSiswa)
            ->postJson(route('siswa.kuis.powers.use', $this->quiz->id), [
                'power_id' => $power->id,
                'soal_id' => $this->soal1->id,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.remaining_qty', 1);

        $this->assertDatabaseHas('siswa_powers', [
            'siswa_id' => $this->siswa->id,
            'power_id' => $power->id,
            'jumlah' => 1,
        ]);
    }

    /**
     * Skenario 4: Siswa tidak dapat menggunakan Power yang tidak dimiliki.
     */
    public function test_siswa_tidak_dapat_menggunakan_power_yang_tidak_dimiliki(): void
    {
        $power = Power::where('kode', 'two_x_score')->firstOrFail();
        // Siswa tidak memiliki record di siswa_powers sama sekali

        $response = $this->actingAs($this->userSiswa)
            ->postJson(route('siswa.kuis.powers.use', $this->quiz->id), [
                'power_id' => $power->id,
                'soal_id' => $this->soal1->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 'POWER_NOT_OWNED');

        $this->assertDatabaseMissing('menjawabs', [
            'siswa_id' => $this->siswa->id,
            'soal_id' => $this->soal1->id,
            'power_id' => $power->id,
        ]);
    }

    /**
     * Skenario 5: Siswa tidak dapat menggunakan Power yang jumlahnya 0.
     */
    public function test_siswa_tidak_dapat_menggunakan_power_yang_jumlahnya_nol(): void
    {
        $power = Power::where('kode', 'double_side_knife')->firstOrFail();
        SiswaPower::create([
            'siswa_id' => $this->siswa->id,
            'power_id' => $power->id,
            'jumlah' => 0,
        ]);

        $response = $this->actingAs($this->userSiswa)
            ->postJson(route('siswa.kuis.powers.use', $this->quiz->id), [
                'power_id' => $power->id,
                'soal_id' => $this->soal1->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 'POWER_EMPTY');

        $this->assertDatabaseHas('siswa_powers', [
            'siswa_id' => $this->siswa->id,
            'power_id' => $power->id,
            'jumlah' => 0,
        ]);
    }

    /**
     * Skenario 6: Siswa tidak dapat menggunakan Power yang tidak aktif.
     */
    public function test_siswa_tidak_dapat_menggunakan_power_yang_tidak_aktif(): void
    {
        $power = Power::where('kode', 'second_chance')->firstOrFail();
        $power->update(['is_active' => false]);

        SiswaPower::create([
            'siswa_id' => $this->siswa->id,
            'power_id' => $power->id,
            'jumlah' => 3,
        ]);

        $response = $this->actingAs($this->userSiswa)
            ->postJson(route('siswa.kuis.powers.use', $this->quiz->id), [
                'power_id' => $power->id,
                'soal_id' => $this->soal1->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 'POWER_INACTIVE');

        // Pastikan jumlah Power tidak berkurang
        $this->assertDatabaseHas('siswa_powers', [
            'siswa_id' => $this->siswa->id,
            'power_id' => $power->id,
            'jumlah' => 3,
        ]);
    }

    /**
     * Skenario 7: Siswa tidak dapat menggunakan Power saat kuis sudah selesai.
     */
    public function test_siswa_tidak_dapat_menggunakan_power_saat_kuis_sudah_selesai(): void
    {
        $power = Power::where('kode', 'lucky_clover')->firstOrFail();
        SiswaPower::create([
            'siswa_id' => $this->siswa->id,
            'power_id' => $power->id,
            'jumlah' => 2,
        ]);

        // Tandai kuis sudah selesai dikerjakan oleh siswa
        Nilai::create([
            'siswa_id' => $this->siswa->id,
            'quiz_id' => $this->quiz->id,
            'nilai' => 100,
            'durasi' => 120,
            'status' => 'selesai',
        ]);

        $response = $this->actingAs($this->userSiswa)
            ->postJson(route('siswa.kuis.powers.use', $this->quiz->id), [
                'power_id' => $power->id,
                'soal_id' => $this->soal1->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 'QUIZ_COMPLETED');

        // Jumlah Power tetap utuh
        $this->assertDatabaseHas('siswa_powers', [
            'siswa_id' => $this->siswa->id,
            'power_id' => $power->id,
            'jumlah' => 2,
        ]);
    }

    /**
     * Skenario 8: Siswa tidak dapat menggunakan Power milik siswa lain.
     */
    public function test_siswa_tidak_dapat_menggunakan_power_milik_siswa_lain(): void
    {
        $power = Power::where('kode', 'two_x_score')->firstOrFail();

        // Siswa Lain memiliki 5 Power, sedangkan Siswa Utama tidak memiliki Power ini
        $siswaLainPower = SiswaPower::create([
            'siswa_id' => $this->siswaLain->id,
            'power_id' => $power->id,
            'jumlah' => 5,
        ]);

        // 1. Siswa Utama mencoba menggunakan Power tanpa memilikinya (sementara Siswa Lain memiliki 5)
        $responseUnowned = $this->actingAs($this->userSiswa)
            ->postJson(route('siswa.kuis.powers.use', $this->quiz->id), [
                'power_id' => $power->id,
                'soal_id' => $this->soal1->id,
            ]);

        $responseUnowned->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 'POWER_NOT_OWNED');

        // 2. Siswa Utama mencoba melakukan spoofing siswa_id / siswa_power_id milik Siswa Lain di payload
        $responseSpoof = $this->actingAs($this->userSiswa)
            ->postJson(route('siswa.kuis.powers.use', $this->quiz->id), [
                'power_id' => $power->id,
                'soal_id' => $this->soal1->id,
                'siswa_id' => $this->siswaLain->id,
                'siswa_power_id' => $siswaLainPower->id,
            ]);

        $responseSpoof->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 'FORBIDDEN_ACCOUNT');

        // Pastikan kepemilikan Siswa Lain sama sekali tidak berkurang
        $this->assertDatabaseHas('siswa_powers', [
            'id' => $siswaLainPower->id,
            'siswa_id' => $this->siswaLain->id,
            'jumlah' => 5,
        ]);
    }

    /**
     * Skenario 9: Power tidak berkurang apabila aktivasi efek gagal.
     */
    public function test_power_tidak_berkurang_apabila_aktivasi_efek_gagal(): void
    {
        $powerRemove = Power::where('kode', 'remove_wrong_answer')->firstOrFail();
        SiswaPower::create([
            'siswa_id' => $this->siswa->id,
            'power_id' => $powerRemove->id,
            'jumlah' => 2,
        ]);

        // Buat soal yang seluruh opsinya adalah kunci jawaban sehingga tidak ada opsi salah yang bisa dieliminasi
        $soalTanpaOpsiSalah = Soal::create([
            'quiz_id' => $this->quiz->id,
            'pertanyaan' => 'Soal khusus tanpa opsi pengecoh salah',
            'tipe' => 'pilihan_ganda',
            'kesulitan' => 'Mudah',
            'level' => 'Level 1',
            'pilihan' => [
                'a' => 'Benar',
            ],
            'kunci_jawaban' => 'a',
            'bobot' => 10,
            'urutan' => 4,
        ]);

        $response = $this->actingAs($this->userSiswa)
            ->postJson(route('siswa.kuis.powers.use', $this->quiz->id), [
                'power_id' => $powerRemove->id,
                'soal_id' => $soalTanpaOpsiSalah->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 'EFFECT_ACTIVATION_FAILED');

        // Pastikan jumlah Power tidak berkurang (tetap 2) dan tidak tercatat di menjawabs
        $this->assertDatabaseHas('siswa_powers', [
            'siswa_id' => $this->siswa->id,
            'power_id' => $powerRemove->id,
            'jumlah' => 2,
        ]);

        $this->assertDatabaseMissing('menjawabs', [
            'siswa_id' => $this->siswa->id,
            'soal_id' => $soalTanpaOpsiSalah->id,
        ]);
    }

    /**
     * Skenario 10: Efek Power benar-benar diterapkan pada mekanisme pengerjaan/penilaian kuis.
     * Menguji rumus pasti:
     * - Soal 1 (bobot 10) + 2X Score + Jawaban Benar -> poin_diperoleh = 20
     * - Soal 2 (bobot 10) + Double Side Knife + Jawaban Salah -> poin_diperoleh = -5 (-0.5x bobot)
     * - Soal 3 (bobot 10) + Tanpa Power + Jawaban Salah -> poin_diperoleh = 0
     * Total Poin Siswa = max(0, 20 + (-5) + 0) = 15 dari Total Bobot Dasar 30 -> Nilai Akhir = 50.00
     */
    public function test_efek_power_benar_benar_diterapkan_pada_mekanisme_penilaian_kuis(): void
    {
        $twoXScore = Power::where('kode', 'two_x_score')->firstOrFail();
        $doubleKnife = Power::where('kode', 'double_side_knife')->firstOrFail();

        SiswaPower::create([
            'siswa_id' => $this->siswa->id,
            'power_id' => $twoXScore->id,
            'jumlah' => 1,
        ]);

        SiswaPower::create([
            'siswa_id' => $this->siswa->id,
            'power_id' => $doubleKnife->id,
            'jumlah' => 1,
        ]);

        // Aktifkan 2X Score pada Soal 1
        $this->actingAs($this->userSiswa)
            ->postJson(route('siswa.kuis.powers.use', $this->quiz->id), [
                'power_id' => $twoXScore->id,
                'soal_id' => $this->soal1->id,
            ])->assertStatus(200);

        // Aktifkan Double Side Knife pada Soal 2
        $this->actingAs($this->userSiswa)
            ->postJson(route('siswa.kuis.powers.use', $this->quiz->id), [
                'power_id' => $doubleKnife->id,
                'soal_id' => $this->soal2->id,
            ])->assertStatus(200);

        // Submit jawaban level: Soal 1 Benar ('a'), Soal 2 Salah ('a', harusnya 'b'), Soal 3 Salah ('a', harusnya 'c')
        $submitResponse = $this->actingAs($this->userSiswa)
            ->post(route('siswa.kuis.submit', $this->quiz->id), [
                'arc' => 'Mudah',
                'level' => 1,
                'step' => 1,
                'durasi' => '01:30',
                'jawaban' => [
                    $this->soal1->id => 'a', // Benar dengan 2X Score -> +20 poin
                    $this->soal2->id => 'a', // Salah dengan Double Side Knife -> -5 poin
                    $this->soal3->id => 'a', // Salah tanpa Power -> 0 poin
                ],
            ]);

        $submitResponse->assertRedirect();

        // Verifikasi poin_diperoleh per butir soal di tabel menjawabs
        $ans1 = Menjawab::where('siswa_id', $this->siswa->id)->where('soal_id', $this->soal1->id)->firstOrFail();
        $ans2 = Menjawab::where('siswa_id', $this->siswa->id)->where('soal_id', $this->soal2->id)->firstOrFail();
        $ans3 = Menjawab::where('siswa_id', $this->siswa->id)->where('soal_id', $this->soal3->id)->firstOrFail();

        $this->assertEquals(20.00, (float) $ans1->poin_diperoleh);
        $this->assertEquals(-5.00, (float) $ans2->poin_diperoleh);
        $this->assertEquals(0.00, (float) $ans3->poin_diperoleh);

        // Total Poin = 20 - 5 + 0 = 15 dari Total Bobot 30 => Nilai Akhir = (15 / 30) * 100 = 50
        $nilai = Nilai::where('siswa_id', $this->siswa->id)->where('quiz_id', $this->quiz->id)->firstOrFail();
        $this->assertEquals(50.00, (float) $nilai->nilai);
        $this->assertSame('selesai', $nilai->status);
    }

    /**
     * Skenario 11: Power tidak dapat digunakan berulang pada kondisi yang dilarang (Maksimal 1 Power per Soal).
     */
    public function test_power_tidak_dapat_digunakan_berulang_pada_soal_yang_sama(): void
    {
        $powerClover = Power::where('kode', 'lucky_clover')->firstOrFail();
        $powerKnife = Power::where('kode', 'double_side_knife')->firstOrFail();

        SiswaPower::create([
            'siswa_id' => $this->siswa->id,
            'power_id' => $powerClover->id,
            'jumlah' => 3,
        ]);

        SiswaPower::create([
            'siswa_id' => $this->siswa->id,
            'power_id' => $powerKnife->id,
            'jumlah' => 2,
        ]);

        // Gunakan Lucky Clover pada Soal 1 -> Berhasil
        $this->actingAs($this->userSiswa)
            ->postJson(route('siswa.kuis.powers.use', $this->quiz->id), [
                'power_id' => $powerClover->id,
                'soal_id' => $this->soal1->id,
            ])->assertStatus(200);

        // Coba gunakan Lucky Clover lagi pada Soal 1 -> Ditolak
        $repeatSame = $this->actingAs($this->userSiswa)
            ->postJson(route('siswa.kuis.powers.use', $this->quiz->id), [
                'power_id' => $powerClover->id,
                'soal_id' => $this->soal1->id,
            ]);

        $repeatSame->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 'QUESTION_ALREADY_HAS_POWER');

        // Coba gunakan Power berbeda (Double Side Knife) pada Soal 1 yang sama -> Ditolak (Aturan 1-to-1)
        $repeatDifferent = $this->actingAs($this->userSiswa)
            ->postJson(route('siswa.kuis.powers.use', $this->quiz->id), [
                'power_id' => $powerKnife->id,
                'soal_id' => $this->soal1->id,
            ]);

        $repeatDifferent->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 'QUESTION_ALREADY_HAS_POWER');

        // Pastikan jumlah Lucky Clover hanya berkurang 1x (sisa 2) dan Double Side Knife tetap utuh (sisa 2)
        $this->assertDatabaseHas('siswa_powers', [
            'siswa_id' => $this->siswa->id,
            'power_id' => $powerClover->id,
            'jumlah' => 2,
        ]);

        $this->assertDatabaseHas('siswa_powers', [
            'siswa_id' => $this->siswa->id,
            'power_id' => $powerKnife->id,
            'jumlah' => 2,
        ]);
    }

    /**
     * Skenario 12: Penggunaan Power bersamaan/beruntun saat stok tersisa 1 tidak pernah membuat jumlah menjadi negatif.
     */
    public function test_penggunaan_power_bersamaan_tidak_membuat_jumlah_menjadi_negatif(): void
    {
        $power = Power::where('kode', 'lucky_clover')->firstOrFail();

        // Stok hanya 1
        SiswaPower::create([
            'siswa_id' => $this->siswa->id,
            'power_id' => $power->id,
            'jumlah' => 1,
        ]);

        // Request 1 pada Soal 1 -> Berhasil menghabiskan stok terakhir (menjadi 0)
        $res1 = $this->actingAs($this->userSiswa)
            ->postJson(route('siswa.kuis.powers.use', $this->quiz->id), [
                'power_id' => $power->id,
                'soal_id' => $this->soal1->id,
            ]);
        $res1->assertStatus(200)
            ->assertJsonPath('data.remaining_qty', 0);

        // Request 2 beruntun pada Soal 2 saat stok sudah 0 -> Ditolak & stok tidak pernah negatif
        $res2 = $this->actingAs($this->userSiswa)
            ->postJson(route('siswa.kuis.powers.use', $this->quiz->id), [
                'power_id' => $power->id,
                'soal_id' => $this->soal2->id,
            ]);
        $res2->assertStatus(422)
            ->assertJsonPath('code', 'POWER_EMPTY');

        $siswaPower = SiswaPower::where('siswa_id', $this->siswa->id)
            ->where('power_id', $power->id)
            ->firstOrFail();

        $this->assertGreaterThanOrEqual(0, $siswaPower->jumlah);
        $this->assertSame(0, (int) $siswaPower->jumlah);
    }

    /**
     * Skenario 13: Sistem kuis yang sudah ada tetap berjalan normal ketika siswa tidak menggunakan Power.
     */
    public function test_sistem_kuis_tetap_berjalan_normal_ketika_siswa_tidak_menggunakan_power(): void
    {
        // Siswa mengerjakan dan mengumpulkan seluruh soal tanpa mengaktifkan Power apapun
        $submitResponse = $this->actingAs($this->userSiswa)
            ->post(route('siswa.kuis.submit', $this->quiz->id), [
                'arc' => 'Mudah',
                'level' => 1,
                'step' => 1,
                'durasi' => '02:00',
                'jawaban' => [
                    $this->soal1->id => 'a', // Benar (+10)
                    $this->soal2->id => 'b', // Benar (+10)
                    $this->soal3->id => 'a', // Salah (0)
                ],
            ]);

        $submitResponse->assertRedirect();

        // Semua jawaban tersimpan dengan power_id = null
        $this->assertDatabaseHas('menjawabs', [
            'siswa_id' => $this->siswa->id,
            'soal_id' => $this->soal1->id,
            'benar' => true,
            'power_id' => null,
            'poin_diperoleh' => 10.00,
        ]);

        $this->assertDatabaseHas('menjawabs', [
            'siswa_id' => $this->siswa->id,
            'soal_id' => $this->soal2->id,
            'benar' => true,
            'power_id' => null,
            'poin_diperoleh' => 10.00,
        ]);

        $this->assertDatabaseHas('menjawabs', [
            'siswa_id' => $this->siswa->id,
            'soal_id' => $this->soal3->id,
            'benar' => false,
            'power_id' => null,
            'poin_diperoleh' => 0.00,
        ]);

        // Nilai akhir standar: 20 / 30 * 100 = 66.67
        $nilai = Nilai::where('siswa_id', $this->siswa->id)->where('quiz_id', $this->quiz->id)->firstOrFail();
        $this->assertEquals(66.67, (float) $nilai->nilai);
        $this->assertSame('selesai', $nilai->status);
    }
}

