<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\AnggotaKelas;
use App\Models\Guru;
use App\Models\GuruMapel;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Menjawab;
use App\Models\Nilai;
use App\Models\Notifikasi;
use App\Models\PengampuKelas;
use App\Models\Quiz;
use App\Models\Siswa;
use App\Models\Soal;
use App\Models\Tugas;
use App\Models\Ujian;
use App\Models\User;
use App\Services\NotifikasiSchedulerService;
use App\Services\UjianNotifikasiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotifikasiEnhancementTest extends TestCase
{
    use RefreshDatabase;

    private function seedClassContext(?string $targetAgama = null): array
    {
        $period = AcademicPeriod::current();

        $guru = Guru::create([
            'nama'   => 'Guru Notif',
            'nuptk'  => '9988776655443322',
            'status' => 'aktif',
        ]);

        $guruUser = User::factory()->create([
            'name'    => 'Guru Notif',
            'role'    => 'guru',
            'status'  => 'aktif',
            'guru_id' => $guru->id,
        ]);

        $mapel = Mapel::create([
            'nama_mapel' => $targetAgama ? "Pendidikan Agama {$targetAgama}" : 'Matematika',
            'kkm'        => 75,
            'tipe_mapel' => $targetAgama ? 'agama_' . strtolower($targetAgama) : 'teori',
        ]);

        $guruMapel = GuruMapel::create([
            'guru_id'  => $guru->id,
            'mapel_id' => $mapel->id,
        ]);

        $kelas = Kelas::create(['nama_kelas' => '7-A', 'tingkatan' => 7]);

        $pk = PengampuKelas::create([
            'guru_mapel_id'      => $guruMapel->id,
            'kelas_id'           => $kelas->id,
            'academic_period_id' => $period->id,
        ]);

        $siswaIslam = Siswa::create([
            'nama_siswa'    => 'Ahmad',
            'nis'           => '10001',
            'jenis_kelamin' => 'L',
            'tanggal_lahir' => '2011-05-01',
            'alamat'        => 'Jl. Merdeka 1',
            'wali_murid'    => 'Bapak Ahmad',
            'nohp_wali'     => '081234567890',
            'agama'         => 'Islam',
            'status'        => 'aktif',
        ]);
        $userIslam = User::factory()->create([
            'name'     => 'Ahmad',
            'role'     => 'siswa',
            'status'   => 'aktif',
            'siswa_id' => $siswaIslam->id,
        ]);
        AnggotaKelas::create([
            'siswa_id'           => $siswaIslam->id,
            'kelas_id'           => $kelas->id,
            'academic_period_id' => $period->id,
        ]);

        $siswaKristen = Siswa::create([
            'nama_siswa'    => 'Daniel',
            'nis'           => '10002',
            'jenis_kelamin' => 'L',
            'tanggal_lahir' => '2011-06-01',
            'alamat'        => 'Jl. Merdeka 2',
            'wali_murid'    => 'Bapak Daniel',
            'nohp_wali'     => '081234567891',
            'agama'         => 'Kristen',
            'status'        => 'aktif',
        ]);
        $userKristen = User::factory()->create([
            'name'     => 'Daniel',
            'role'     => 'siswa',
            'status'   => 'aktif',
            'siswa_id' => $siswaKristen->id,
        ]);
        AnggotaKelas::create([
            'siswa_id'           => $siswaKristen->id,
            'kelas_id'           => $kelas->id,
            'academic_period_id' => $period->id,
        ]);

        return compact(
            'period',
            'guru',
            'guruUser',
            'mapel',
            'guruMapel',
            'kelas',
            'pk',
            'siswaIslam',
            'userIslam',
            'siswaKristen',
            'userKristen'
        );
    }

    public function test_materi_notification_respects_target_agama_and_cleans_up_on_delete(): void
    {
        Storage::fake('local');
        $ctx = $this->seedClassContext('Islam');

        $file = UploadedFile::fake()->create('pai.pdf', 100, 'application/pdf');

        $this->actingAs($ctx['guruUser'])->post(route('guru.materi.store'), [
            'judul'              => 'Fiqih Bab 1',
            'deskripsi'          => 'Ringkasan fiqih',
            'mapel_id'           => $ctx['mapel']->id,
            'tingkatan'          => 7,
            'pengampu_kelas_ids' => [$ctx['pk']->id],
            'file_materi'        => $file,
            'url_youtube'        => null,
        ])->assertRedirect(route('guru.materi'));

        $this->assertDatabaseHas('notifikasis', [
            'user_id' => $ctx['userIslam']->id,
            'tipe'    => 'materi_baru',
            'judul'   => 'Materi Baru: Fiqih Bab 1',
        ]);
        $this->assertDatabaseMissing('notifikasis', [
            'user_id' => $ctx['userKristen']->id,
            'tipe'    => 'materi_baru',
        ]);

        $notif = Notifikasi::where('user_id', $ctx['userIslam']->id)->first();
        $this->assertEquals('materi', $notif->data['resource_type'] ?? null);
        $idGrub = $notif->data['id_grub_materi'] ?? null;
        $this->assertNotNull($idGrub);

        // Hapus materi -> notifikasi terkait ikut terhapus
        $this->actingAs($ctx['guruUser'])->delete(route('guru.materi.destroy', $idGrub))
            ->assertRedirect(route('guru.materi'));

        $this->assertDatabaseMissing('notifikasis', [
            'id' => $notif->id,
        ]);
    }

    public function test_ujian_remidi_notifies_only_peserta_khusus_and_essay_grading_notifies_siswa(): void
    {
        $ctx = $this->seedClassContext(null);

        $ujianRemidi = Ujian::create([
            'judul'             => 'Remidi UTS Matematika',
            'pengampu_kelas_id' => $ctx['pk']->id,
            'durasi_menit'      => 60,
            'kkm'               => 75,
            'waktu_mulai'       => now()->addHour(),
            'deadline'          => now()->addHours(2),
            'status'            => 'published',
            'jenis_ujian'       => 'remidi',
            'peserta_khusus'    => [$ctx['siswaIslam']->id],
        ]);

        $service = app(UjianNotifikasiService::class);
        $sent = $service->notifyUjianPublished($ujianRemidi, false);

        $this->assertSame(1, $sent);
        $this->assertDatabaseHas('notifikasis', [
            'user_id'      => $ctx['userIslam']->id,
            'tipe'         => 'ujian_remidi',
            'id_referensi' => $ujianRemidi->id,
            'url'          => route('siswa.ujian.show', $ujianRemidi->id),
        ]);
        $this->assertDatabaseMissing('notifikasis', [
            'user_id'      => $ctx['userKristen']->id,
            'id_referensi' => $ujianRemidi->id,
        ]);

        // Nilai essay disimpan 2 kali -> tetap 1 notifikasi ter-update (tidak spam)
        $service->notifyNilaiUjianSiswa($ujianRemidi->id, $ctx['siswaIslam']->id, 70);
        $service->notifyNilaiUjianSiswa($ujianRemidi->id, $ctx['siswaIslam']->id, 85);

        $nilaiNotifs = Notifikasi::where('user_id', $ctx['userIslam']->id)
            ->where('tipe', 'nilai_ujian')
            ->where('id_referensi', $ujianRemidi->id)
            ->get();

        $this->assertCount(1, $nilaiNotifs);
        $this->assertStringContainsString('85', $nilaiNotifs->first()->pesan);
        $this->assertSame(route('siswa.ujian.hasil', $ujianRemidi->id), $nilaiNotifs->first()->url);

        // Cleanup saat ujian dihapus
        $service->cleanupOnDelete([$ujianRemidi->id]);
        $this->assertDatabaseMissing('notifikasis', ['id_referensi' => $ujianRemidi->id]);
    }

    public function test_guru_submission_notifications_rollup_when_multiple_students_submit(): void
    {
        $ctx = $this->seedClassContext(null);

        $first = Notifikasi::kirimAtauRollupKeGuru(
            $ctx['guruUser']->id,
            'Pengumpulan Tugas Masuk: PR Aljabar',
            "Siswa Ahmad (7-A) telah mengumpulkan tugas 'PR Aljabar'.",
            'tugas_dikumpulkan',
            '/guru/tugas/10',
            10,
            'Ahmad',
            'PR Aljabar',
            'Mengumpulkan Tugas',
            ['resource_type' => 'tugas', 'resource_id' => 10]
        );

        $second = Notifikasi::kirimAtauRollupKeGuru(
            $ctx['guruUser']->id,
            'Pengumpulan Tugas Masuk: PR Aljabar',
            "Siswa Daniel (7-A) telah mengumpulkan tugas 'PR Aljabar'.",
            'tugas_dikumpulkan',
            '/guru/tugas/10',
            10,
            'Daniel',
            'PR Aljabar',
            'Mengumpulkan Tugas',
            ['resource_type' => 'tugas', 'resource_id' => 10]
        );

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, Notifikasi::where('user_id', $ctx['guruUser']->id)->count());
        $this->assertStringContainsString('2 Siswa Mengumpulkan Tugas', $second->judul);
        $this->assertStringContainsString('Ahmad dan Daniel', $second->pesan);
    }

    public function test_web_and_api_notifikasi_filters_clear_read_and_guru_dashboard_unread_count(): void
    {
        $ctx = $this->seedClassContext(null);

        $nRead = Notifikasi::kirim($ctx['guruUser']->id, 'Materi Lama', 'Pesan materi', 'materi_baru');
        $nRead->markAsRead();

        $nUnread = Notifikasi::kirim($ctx['guruUser']->id, 'Tugas Baru', 'Pesan tugas', 'tugas_dikumpulkan');

        // Web index dengan filter materi, header dropdown, & date grouping
        $webRes = $this->actingAs($ctx['guruUser'])->get(route('notifikasi.index', ['filter' => 'materi']));
        $webRes->assertOk();
        $webRes->assertSee('Materi Lama');
        $webRes->assertSee('Notifikasi Terbaru');
        $webRes->assertSee('Hari Ini');
        $webRes->assertSee('Hapus Sudah Dibaca');
        $this->assertSame(['Materi Lama'], $webRes->viewData('notifikasis')->pluck('judul')->all());

        // API guruDashboard memiliki unread_notif_count
        Sanctum::actingAs($ctx['guruUser']);
        $dashRes = $this->getJson('/api/guru/dashboard');
        $dashRes->assertOk();
        $dashRes->assertJsonPath('data.statistics.unread_notif_count', 1);

        // API notifikasi counts mencakup materi, pengingat, & read + metadata date_group/kategori
        $apiIdx = $this->getJson('/api/notifikasi?filter=materi');
        $apiIdx->assertOk();
        $apiIdx->assertJsonPath('data.counts.total', 2);
        $apiIdx->assertJsonPath('data.counts.unread', 1);
        $apiIdx->assertJsonPath('data.counts.read', 1);
        $apiIdx->assertJsonPath('data.counts.materi', 1);
        $apiIdx->assertJsonPath('data.notifikasis.data.0.date_group', 'Hari Ini');
        $apiIdx->assertJsonPath('data.notifikasis.data.0.kategori', 'materi');

        // API preview & counts endpoints
        $previewRes = $this->getJson('/api/notifikasi/preview?limit=5');
        $previewRes->assertOk();
        $previewRes->assertJsonPath('data.unread_count', 1);
        $previewRes->assertJsonCount(2, 'data.items');

        $countsRes = $this->getJson('/api/notifikasi/counts');
        $countsRes->assertOk();
        $countsRes->assertJsonPath('data.read', 1);

        // API toggleRead bersifat satu arah (tidak mengembalikan ke unread)
        $toggleRes = $this->patchJson("/api/notifikasi/{$nRead->id}/toggle");
        $toggleRes->assertOk();
        $toggleRes->assertJsonPath('data.is_read', true);

        // API clearRead hanya menghapus yang sudah dibaca
        $clearRes = $this->deleteJson('/api/notifikasi/read');
        $clearRes->assertOk();
        $clearRes->assertJsonPath('data.deleted_count', 1);

        $this->assertDatabaseMissing('notifikasis', ['id' => $nRead->id]);
        $this->assertDatabaseHas('notifikasis', ['id' => $nUnread->id]);
    }

    public function test_scheduler_reminders_and_auto_pruning(): void
    {
        $ctx = $this->seedClassContext(null);
        $scheduler = app(NotifikasiSchedulerService::class);

        // 1. Tugas deadline 2 jam lagi -> Ahmad sudah kumpul, Daniel belum
        $tugas = Tugas::create([
            'pengampu_kelas_id' => $ctx['pk']->id,
            'judul'             => 'PR Limit',
            'deskripsi'         => 'Kerjakan nomor 1-5',
            'deadline'          => now()->addHours(2),
            'tipe'              => 'upload',
        ]);

        Nilai::create([
            'siswa_id' => $ctx['siswaIslam']->id,
            'tugas_id' => $tugas->id,
            'nilai'    => null, // belum dinilai guru
            'status'   => 'selesai',
        ]);

        $sentDeadline = $scheduler->kirimPengingatDeadlineTugas();
        $this->assertSame(1, $sentDeadline);
        $this->assertDatabaseHas('notifikasis', [
            'user_id'      => $ctx['userKristen']->id,
            'tipe'         => 'pengingat_deadline',
            'id_referensi' => $tugas->id,
        ]);
        $this->assertDatabaseMissing('notifikasis', [
            'user_id' => $ctx['userIslam']->id,
            'tipe'    => 'pengingat_deadline',
        ]);

        // Jalankan ulang -> deduplikasi (0 terkirim lagi)
        $this->assertSame(0, $scheduler->kirimPengingatDeadlineTugas());

        // 2. Ujian mulai 15 menit lagi
        $ujian = Ujian::create([
            'judul'             => 'UTS Matematika',
            'pengampu_kelas_id' => $ctx['pk']->id,
            'durasi_menit'      => 60,
            'kkm'               => 75,
            'waktu_mulai'       => now()->addMinutes(15),
            'deadline'          => now()->addHours(2),
            'status'            => 'scheduled',
            'jenis_ujian'       => 'reguler',
        ]);

        $sentJadwal = $scheduler->kirimPengingatJadwalUjianDanKuis();
        $this->assertSame(2, $sentJadwal);
        $this->assertSame(0, $scheduler->kirimPengingatJadwalUjianDanKuis());

        // 3. Pengingat koreksi harian guru (ada 1 tugas belum dinilai)
        $sentKoreksi = $scheduler->kirimPengingatKoreksiGuru();
        $this->assertSame(1, $sentKoreksi);
        $this->assertDatabaseHas('notifikasis', [
            'user_id' => $ctx['guruUser']->id,
            'tipe'    => 'pengingat_koreksi',
        ]);
        $this->assertSame(0, $scheduler->kirimPengingatKoreksiGuru());

        // 4. Auto-pruning notifikasi sudah dibaca > 30 hari
        $oldRead = Notifikasi::kirim($ctx['userIslam']->id, 'Lama Dibaca', 'Pesan lama', 'info');
        $oldRead->update(['is_read' => true, 'read_at' => now()->subDays(45)]);

        $oldUnread = Notifikasi::kirim($ctx['userIslam']->id, 'Lama Belum Dibaca', 'Pesan lama', 'info');
        $oldUnread->forceFill(['created_at' => now()->subDays(45), 'updated_at' => now()->subDays(45)])->save();

        $pruned = $scheduler->pruneNotifikasiLama(30);
        $this->assertSame(1, $pruned);
        $this->assertDatabaseMissing('notifikasis', ['id' => $oldRead->id]);
        $this->assertDatabaseHas('notifikasis', ['id' => $oldUnread->id]);
    }
}
