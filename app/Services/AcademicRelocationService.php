<?php

namespace App\Services;

use App\Models\GuruMapel;
use App\Models\Materi;
use App\Models\Menjawab;
use App\Models\Nilai;
use App\Models\Notifikasi;
use App\Models\PengampuKelas;
use App\Models\Quiz;
use App\Models\SeleksiUjian;
use App\Models\SiswaPower;
use App\Models\Soal;
use App\Models\Tugas;
use App\Models\Ujian;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AcademicRelocationService
{
    /**
     * Hitung ringkasan aktivitas akademik dan pengerjaan siswa pada suatu PengampuKelas.
     */
    public function getPengampuActivitySummary(PengampuKelas $pk): array
    {
        $materiCount = $pk->materis()->count();
        $tugasCount = $pk->tugases()->count();
        $quizCount = $pk->quizzes()->count();
        $ujianCount = $pk->ujians()->count();

        $tugasNilaiCount = Nilai::whereHas('tugas', fn($q) => $q->where('pengampu_kelas_id', $pk->id))->count();
        $quizNilaiCount = Nilai::whereHas('quiz', fn($q) => $q->where('pengampu_kelas_id', $pk->id))->count();
        $ujianSeleksiCount = SeleksiUjian::whereHas('ujian', fn($q) => $q->where('pengampu_kelas_id', $pk->id))->count();

        $totalAktivitas = $materiCount + $tugasCount + $quizCount + $ujianCount;
        $totalPengerjaan = $tugasNilaiCount + $quizNilaiCount + $ujianSeleksiCount;

        return [
            'materi_count'      => $materiCount,
            'tugas_count'       => $tugasCount,
            'quiz_count'        => $quizCount,
            'ujian_count'       => $ujianCount,
            'total_aktivitas'   => $totalAktivitas,
            'total_pengerjaan'  => $totalPengerjaan,
            'has_data'          => $totalAktivitas > 0,
            'has_submissions'   => $totalPengerjaan > 0,
        ];
    }

    /**
     * STUDI KASUS 1 (ADMIN):
     * Ganti Guru Pengampu pada suatu kelas atau Tukar Kelas (Swap) antar 2 guru di mata pelajaran yang sama.
     *
     * @param PengampuKelas $sourcePk Penugasan kelas asal yang ingin diganti/ditukar gurunya
     * @param int $targetGuruId ID Guru tujuan
     * @param string $mode 'serah_terima' (default: tugas & nilai tetap di kelasnya) | 'bawa_soal_reset_nilai' (soal ikut guru, nilai salah kelas di-reset)
     * @param int|null $swapWithPengampuId Opsional ID PengampuKelas milik guru tujuan jika ingin menukar kelas (Swap)
     */
    public function reassignOrSwapGuru(
        PengampuKelas $sourcePk,
        int $targetGuruId,
        string $mode = 'serah_terima',
        ?int $swapWithPengampuId = null
    ): array {
        $sourcePk->load(['guruMapel.guru', 'guruMapel.mapel', 'kelas']);
        $oldGuruMapel = $sourcePk->guruMapel;

        if (!$oldGuruMapel) {
            throw new \RuntimeException('Data relasi guru mata pelajaran tidak ditemukan.');
        }

        $mapelId = $oldGuruMapel->mapel_id;
        $oldGuruId = $oldGuruMapel->guru_id;

        if ((int) $oldGuruId === (int) $targetGuruId) {
            throw new \InvalidArgumentException('Guru tujuan sama dengan guru pengampu saat ini.');
        }

        return DB::transaction(function () use ($sourcePk, $oldGuruMapel, $mapelId, $oldGuruId, $targetGuruId, $mode, $swapWithPengampuId) {
            // Pastikan GuruMapel untuk guru tujuan pada mapel ini ada
            $targetGuruMapel = GuruMapel::firstOrCreate([
                'guru_id'  => $targetGuruId,
                'mapel_id' => $mapelId,
            ]);

            // Cek apakah ini operasi SWAP (Tukar Kelas antar 2 Guru di mapel yang sama)
            $swapPk = null;
            if ($swapWithPengampuId) {
                $swapPk = PengampuKelas::with(['guruMapel.guru', 'kelas'])
                    ->where('id', $swapWithPengampuId)
                    ->where('guru_mapel_id', $targetGuruMapel->id)
                    ->first();

                if (!$swapPk) {
                    throw new \InvalidArgumentException('Kelas milik guru tujuan yang akan ditukar tidak valid.');
                }
            }

            if ($swapPk) {
                // =========================================================
                // KASUS SWAP (TUKAR KELAS ANTAR 2 GURU, MISAL 7A <-> 7B)
                // =========================================================
                $kelasSourceNama = $sourcePk->kelas?->nama_kelas ?? 'Kelas A';
                $kelasSwapNama = $swapPk->kelas?->nama_kelas ?? 'Kelas B';
                $guruLamaNama = $oldGuruMapel->guru?->nama ?? 'Guru A';
                $guruTargetNama = $targetGuruMapel->guru?->nama ?? 'Guru B';
                $mapelNama = $oldGuruMapel->mapel?->nama_mapel ?? 'Mata Pelajaran';

                if ($mode === 'bawa_soal_reset_nilai') {
                    // Soal/Tugas tetap melekat pada guru pembuatnya, tapi kelasnya ditukar.
                    // Karena siswa yang mengerjakan sebelumnya berasal dari kelas yang salah, reset pengerjaan siswa.
                    $resetSource = $this->resetAllSubmissionsInPengampu($sourcePk);
                    $resetSwap = $this->resetAllSubmissionsInPengampu($swapPk);

                    $kelasIdSource = $sourcePk->kelas_id;
                    $kelasIdSwap = $swapPk->kelas_id;

                    // Gunakan pertukaran aman terhadap unique constraint (guru_mapel_id, kelas_id)
                    $sourcePk->update(['guru_mapel_id' => $targetGuruMapel->id]);
                    $swapPk->update(['guru_mapel_id' => $oldGuruMapel->id]);

                    // Lalu tukar kembali guru_mapel_id dan kelas_id agar ID PengampuKelas (beserta tugasnya) mengikuti guru aslinya ke kelas baru
                    // Catatan: Karena unique(guru_mapel_id, kelas_id), kita hapus sementara constraint bentrok atau tukar via urutan
                    // Cara paling aman tanpa melanggar unique key: pindahkan parent pengampu_kelas_id dari semua aktivitas antara sourcePk & swapPk!
                    // Pertama kembalikan guru_mapel_id ke pemilik aslinya? Tidak, kalau sourcePk sekarang punya targetGuruMapel (di kelasSource) dan swapPk punya oldGuruMapel (di kelasSwap),
                    // maka dengan menukar pengampu_kelas_id pada seluruh Materi/Tugas/Quiz/Ujian antara sourcePk dan swapPk, soal Guru A ikut ke kelas baru Guru A!
                    $this->swapActivitiesBetweenPengampu($sourcePk, $swapPk);

                    $this->detachCrossTeacherGroups($sourcePk);
                    $this->detachCrossTeacherGroups($swapPk);

                    $totalReset = $resetSource + $resetSwap;

                    return [
                        'type'    => 'swap_bawa_soal',
                        'message' => "Berhasil menukar kelas {$mapelNama}: {$guruLamaNama} kini di {$kelasSwapNama} dan {$guruTargetNama} kini di {$kelasSourceNama}. Tugas/Ujian masing-masing guru ikut berpindah ke kelas barunya (dan {$totalReset} data pengerjaan siswa kelas lama telah di-reset & Power dikembalikan).",
                    ];
                }

                // DEFAULT MODE: 'serah_terima'
                // Tugas, Kuis, Ujian, dan Nilai Siswa tetap tinggal di kelasnya masing-masing, hanya gurunya yang bertukar!
                $sourcePk->update(['guru_mapel_id' => $targetGuruMapel->id]);
                $swapPk->update(['guru_mapel_id' => $oldGuruMapel->id]);

                $this->detachCrossTeacherGroups($sourcePk);
                $this->detachCrossTeacherGroups($swapPk);

                return [
                    'type'    => 'swap_serah_terima',
                    'message' => "Berhasil menukar hak akses kelas {$mapelNama}: {$guruTargetNama} kini mengampu {$kelasSourceNama} dan {$guruLamaNama} mengampu {$kelasSwapNama}. Seluruh tugas, ujian, dan nilai siswa di kedua kelas tetap tersimpan utuh!",
                ];
            }

            // =========================================================
            // KASUS SINGLE REASSIGN (GANTI GURU PENGAMPU PADA 1 KELAS)
            // =========================================================
            $conflict = PengampuKelas::where('guru_mapel_id', $targetGuruMapel->id)
                ->where('kelas_id', $sourcePk->kelas_id)
                ->where('id', '!=', $sourcePk->id)
                ->first();

            if ($conflict) {
                throw new \InvalidArgumentException('Guru tujuan sudah terdaftar mengampu mata pelajaran ini di kelas tersebut.');
            }

            $resetCount = 0;
            if ($mode === 'bawa_soal_reset_nilai') {
                $resetCount = $this->resetAllSubmissionsInPengampu($sourcePk);
            }

            $sourcePk->update([
                'guru_mapel_id' => $targetGuruMapel->id,
            ]);

            $this->detachCrossTeacherGroups($sourcePk);

            // Jika GuruMapel lama sudah tidak punya kelas sama sekali dan tidak punya foto jadwal, bersihkan
            if ($oldGuruMapel->pengampuKelases()->count() === 0 && empty($oldGuruMapel->jadwal)) {
                $oldGuruMapel->delete();
            }

            $kelasNama = $sourcePk->kelas?->nama_kelas ?? 'Kelas';
            $guruBaruNama = $targetGuruMapel->guru?->nama ?? 'Guru Baru';
            $mapelNama = $oldGuruMapel->mapel?->nama_mapel ?? 'Mata Pelajaran';

            $extraMsg = $mode === 'bawa_soal_reset_nilai'
                ? " ({$resetCount} pengerjaan siswa telah di-reset)."
                : " Seluruh tugas, ujian, dan nilai siswa di kelas {$kelasNama} tetap utuh dan dialihkan ke {$guruBaruNama}.";

            return [
                'type'    => 'reassign',
                'message' => "Pengampu {$mapelNama} di kelas {$kelasNama} berhasil diganti ke {$guruBaruNama}!{$extraMsg}",
            ];
        });
    }

    /**
     * STUDI KASUS 2 (GURU):
     * Pindahkan atau Salin Aktivitas (Materi / Tugas / Quiz / Ujian) antar Mapel atau antar Kelas.
     *
     * @param string $jenis 'materi' | 'tugas' | 'quiz' | 'ujian'
     * @param int $aktivitasId ID record aktivitas
     * @param PengampuKelas $targetPk Target PengampuKelas (Mapel & Kelas tujuan)
     * @param string $aksi 'pindah_aman' (otomatis jika kelas sama) | 'pindah_reset' (pindah beda kelas & reset nilai lama) | 'salin' (duplikat ke kelas/mapel tujuan)
     * @param int $guruId ID Guru yang sedang login (untuk validasi otorisasi)
     */
    public function relocateActivity(
        string $jenis,
        int $aktivitasId,
        PengampuKelas $targetPk,
        string $aksi,
        int $guruId
    ): array {
        $targetPk->load(['guruMapel.mapel', 'kelas']);

        if ((int) $targetPk->guruMapel?->guru_id !== (int) $guruId) {
            throw new \InvalidArgumentException('Kelas dan mata pelajaran tujuan bukan wewenang Anda.');
        }

        return DB::transaction(function () use ($jenis, $aktivitasId, $targetPk, $aksi, $guruId) {
            return match ($jenis) {
                'tugas'  => $this->relocateTugas($aktivitasId, $targetPk, $aksi, $guruId),
                'quiz'   => $this->relocateQuiz($aktivitasId, $targetPk, $aksi, $guruId),
                'ujian'  => $this->relocateUjian($aktivitasId, $targetPk, $aksi, $guruId),
                'materi' => $this->relocateMateri($aktivitasId, $targetPk, $aksi, $guruId),
                default  => throw new \InvalidArgumentException('Jenis aktivitas tidak dikenali.'),
            };
        });
    }

    /**
     * Relokasi / Duplikasi TUGAS.
     */
    protected function relocateTugas(int $tugasId, PengampuKelas $targetPk, string $aksi, int $guruId): array
    {
        $tugas = Tugas::with(['pengampuKelas.guruMapel.mapel', 'pengampuKelas.kelas', 'soals'])->findOrFail($tugasId);
        $sourcePk = $tugas->pengampuKelas;

        if ((int) $sourcePk?->guruMapel?->guru_id !== (int) $guruId) {
            throw new \InvalidArgumentException('Anda tidak memiliki akses pada tugas ini.');
        }

        if ((int) $sourcePk->id === (int) $targetPk->id) {
            throw new \InvalidArgumentException('Mapel dan kelas tujuan sama dengan posisi tugas saat ini.');
        }

        $isSameClass = (int) $sourcePk->kelas_id === (int) $targetPk->kelas_id;
        $targetLabel = ($targetPk->guruMapel?->mapel?->nama_mapel ?? 'Mapel') . ' - Kelas ' . ($targetPk->kelas?->nama_kelas ?? '');

        if ($aksi === 'salin') {
            $newTugas = $tugas->replicate(['pengumpulans_count', 'pengumpulans_dinilai_count']);
            $newTugas->pengampu_kelas_id = $targetPk->id;
            $newTugas->save();

            foreach ($tugas->soals as $soal) {
                $newSoal = $soal->replicate();
                $newSoal->tugas_id = $newTugas->id;
                $newSoal->quiz_id = null;
                $newSoal->ujian_id = null;
                $newSoal->save();
            }

            return [
                'message' => "Tugas '{$tugas->judul}' berhasil disalin (diduplikasi) ke {$targetLabel} beserta {$tugas->soals->count()} butir soalnya! Data di kelas asal tetap utuh.",
            ];
        }

        // Mode Pindah:
        $resetCount = 0;
        if (!$isSameClass) {
            // Beda kelas -> bersihkan pengerjaan siswa dari kelas yang salah
            $resetCount = $this->resetTugasSubmissions($tugas);
        }

        $tugas->update([
            'pengampu_kelas_id' => $targetPk->id,
        ]);

        // Sinkronisasi notifikasi
        $this->syncActivityNotifications('tugas_baru', $tugas->id, $sourcePk, $targetPk, "Tugas: {$tugas->judul}", route('siswa.tugas.show', $tugas->id));

        if ($isSameClass) {
            $keptCount = $tugas->nilais()->count();
            return [
                'message' => "Tugas '{$tugas->judul}' berhasil dipindahkan ke {$targetLabel}. Karena berada di kelas yang sama ({$targetPk->kelas?->nama_kelas}), seluruh {$keptCount} data pengumpulan & nilai siswa tetap tersimpan 100% aman!",
            ];
        }

        return [
            'message' => "Tugas '{$tugas->judul}' berhasil dipindahkan ke {$targetLabel}." . ($resetCount > 0 ? " ({$resetCount} pengumpulan dari siswa kelas sebelumnya telah di-reset)." : ''),
        ];
    }

    /**
     * Relokasi / Duplikasi QUIZ PETUALANGAN.
     */
    protected function relocateQuiz(int $quizId, PengampuKelas $targetPk, string $aksi, int $guruId): array
    {
        $quiz = Quiz::with(['pengampuKelas.guruMapel.mapel', 'pengampuKelas.kelas', 'soals'])->findOrFail($quizId);
        $sourcePk = $quiz->pengampuKelas;

        if ((int) $sourcePk?->guruMapel?->guru_id !== (int) $guruId) {
            throw new \InvalidArgumentException('Anda tidak memiliki akses pada kuis ini.');
        }

        if ((int) $sourcePk->id === (int) $targetPk->id) {
            throw new \InvalidArgumentException('Mapel dan kelas tujuan sama dengan posisi kuis saat ini.');
        }

        $isSameClass = (int) $sourcePk->kelas_id === (int) $targetPk->kelas_id;
        $targetLabel = ($targetPk->guruMapel?->mapel?->nama_mapel ?? 'Mapel') . ' - Kelas ' . ($targetPk->kelas?->nama_kelas ?? '');

        if ($aksi === 'salin') {
            $newQuiz = $quiz->replicate();
            $newQuiz->pengampu_kelas_id = $targetPk->id;
            $newQuiz->save();

            foreach ($quiz->soals as $soal) {
                $newSoal = $soal->replicate();
                $newSoal->quiz_id = $newQuiz->id;
                $newSoal->tugas_id = null;
                $newSoal->ujian_id = null;
                $newSoal->save();
            }

            return [
                'message' => "Kuis '{$quiz->judul}' berhasil disalin (diduplikasi) ke {$targetLabel} beserta seluruh level dan {$quiz->soals->count()} butir soalnya!",
            ];
        }

        // Mode Pindah:
        $resetInfo = ['nilai_deleted' => 0, 'power_refunded' => 0];
        if (!$isSameClass) {
            $resetInfo = $this->resetQuizSubmissions($quiz);
        }

        $quiz->update([
            'pengampu_kelas_id' => $targetPk->id,
        ]);

        if ($isSameClass) {
            $keptCount = $quiz->nilais()->count();
            return [
                'message' => "Kuis '{$quiz->judul}' berhasil dipindahkan ke {$targetLabel}. Karena berada di kelas yang sama ({$targetPk->kelas?->nama_kelas}), seluruh {$keptCount} nilai dan progres petualangan siswa tetap aman!",
            ];
        }

        $extra = $resetInfo['nilai_deleted'] > 0
            ? " ({$resetInfo['nilai_deleted']} riwayat pengerjaan kelas lama telah di-reset dan {$resetInfo['power_refunded']} item Power siswa telah dikembalikan)."
            : '';

        return [
            'message' => "Kuis '{$quiz->judul}' berhasil dipindahkan ke {$targetLabel}.{$extra}",
        ];
    }

    /**
     * Relokasi / Duplikasi UJIAN CBT.
     */
    protected function relocateUjian(int $ujianId, PengampuKelas $targetPk, string $aksi, int $guruId): array
    {
        $ujian = Ujian::with(['pengampuKelas.guruMapel.mapel', 'pengampuKelas.kelas', 'soals'])->findOrFail($ujianId);
        $sourcePk = $ujian->pengampuKelas;

        if ((int) $sourcePk?->guruMapel?->guru_id !== (int) $guruId) {
            throw new \InvalidArgumentException('Anda tidak memiliki akses pada ujian ini.');
        }

        if ((int) $sourcePk->id === (int) $targetPk->id) {
            throw new \InvalidArgumentException('Mapel dan kelas tujuan sama dengan posisi ujian saat ini.');
        }

        $isSameClass = (int) $sourcePk->kelas_id === (int) $targetPk->kelas_id;
        $targetLabel = ($targetPk->guruMapel?->mapel?->nama_mapel ?? 'Mapel') . ' - Kelas ' . ($targetPk->kelas?->nama_kelas ?? '');

        if ($aksi === 'salin') {
            $newUjian = $ujian->replicate();
            $newUjian->pengampu_kelas_id = $targetPk->id;
            $newUjian->id_grup_ujian = (string) Str::uuid();
            $newUjian->save();

            foreach ($ujian->soals as $soal) {
                $newSoal = $soal->replicate();
                $newSoal->ujian_id = $newUjian->id;
                $newSoal->quiz_id = null;
                $newSoal->tugas_id = null;
                $newSoal->save();
            }

            return [
                'message' => "Ujian '{$ujian->judul}' berhasil disalin (diduplikasi) ke {$targetLabel} beserta {$ujian->soals->count()} butir soalnya!",
            ];
        }

        $resetCount = 0;
        if (!$isSameClass) {
            $resetCount = $this->resetUjianSubmissions($ujian);
        }

        // Berikan id_grup_ujian baru jika ujian sebelumnya tergabung dalam grup multi-kelas / mapel berbeda
        $newGroupId = $ujian->id_grup_ujian;
        if ($ujian->id_grup_ujian) {
            $otherInGroup = Ujian::where('id_grup_ujian', $ujian->id_grup_ujian)
                ->where('id', '!=', $ujian->id)
                ->exists();
            if ($otherInGroup) {
                $newGroupId = (string) Str::uuid();
            }
        }

        $ujian->update([
            'pengampu_kelas_id' => $targetPk->id,
            'id_grup_ujian'     => $newGroupId,
        ]);

        if ($isSameClass) {
            $keptCount = $ujian->seleksiUjians()->count();
            return [
                'message' => "Ujian '{$ujian->judul}' berhasil dipindahkan ke {$targetLabel}. Karena berada di kelas yang sama ({$targetPk->kelas?->nama_kelas}), seluruh {$keptCount} nilai & jawaban ujian siswa tetap tersimpan utuh!",
            ];
        }

        return [
            'message' => "Ujian '{$ujian->judul}' berhasil dipindahkan ke {$targetLabel}." . ($resetCount > 0 ? " ({$resetCount} hasil ujian siswa kelas sebelumnya telah di-reset)." : ''),
        ];
    }

    /**
     * Relokasi / Duplikasi MATERI PEMBELAJARAN.
     */
    protected function relocateMateri(int $materiId, PengampuKelas $targetPk, string $aksi, int $guruId): array
    {
        $materi = Materi::with(['pengampuKelas.guruMapel.mapel', 'pengampuKelas.kelas'])->findOrFail($materiId);
        $sourcePk = $materi->pengampuKelas;

        if ((int) $sourcePk?->guruMapel?->guru_id !== (int) $guruId) {
            throw new \InvalidArgumentException('Anda tidak memiliki akses pada materi ini.');
        }

        if ((int) $sourcePk->id === (int) $targetPk->id) {
            throw new \InvalidArgumentException('Mapel dan kelas tujuan sama dengan posisi materi saat ini.');
        }

        $targetLabel = ($targetPk->guruMapel?->mapel?->nama_mapel ?? 'Mapel') . ' - Kelas ' . ($targetPk->kelas?->nama_kelas ?? '');

        if ($aksi === 'salin') {
            $newMateri = $materi->replicate();
            $newMateri->pengampu_kelas_id = $targetPk->id;
            $newMateri->id_grub_materi = (string) Str::uuid();
            $newMateri->save();

            return [
                'message' => "Materi '{$materi->judul}' berhasil disalin ke {$targetLabel}!",
            ];
        }

        // Pecah id_grub_materi jika pindah mapel agar tidak tercampur grup dengan mapel lama
        $newGroupId = $materi->id_grub_materi;
        if ($materi->id_grub_materi && $sourcePk->guru_mapel_id !== $targetPk->guru_mapel_id) {
            $otherInGroup = Materi::where('id_grub_materi', $materi->id_grub_materi)
                ->where('id', '!=', $materi->id)
                ->exists();
            if ($otherInGroup) {
                $newGroupId = (string) Str::uuid();
            }
        }

        $materi->update([
            'pengampu_kelas_id' => $targetPk->id,
            'id_grub_materi'    => $newGroupId,
        ]);

        return [
            'message' => "Materi '{$materi->judul}' berhasil dipindahkan ke {$targetLabel}!",
        ];
    }

    /**
     * Reset seluruh pengerjaan siswa pada suatu Tugas (termasuk hapus file fisik tugas upload & menjawabs).
     */
    public function resetTugasSubmissions(Tugas $tugas): int
    {
        $nilais = $tugas->nilais()->get();
        $count = $nilais->count();

        foreach ($nilais as $nilai) {
            if ($nilai->file_jawaban && Storage::disk('public')->exists($nilai->file_jawaban)) {
                Storage::disk('public')->delete($nilai->file_jawaban);
            }
            $nilai->delete();
        }

        $soalIds = $tugas->soals()->pluck('id');
        if ($soalIds->isNotEmpty()) {
            Menjawab::whereIn('soal_id', $soalIds)->delete();
        }

        return $count;
    }

    /**
     * Reset seluruh pengerjaan siswa pada suatu Quiz (dan otomatis REFUND stok Power siswa yang terpakai).
     */
    public function resetQuizSubmissions(Quiz $quiz): array
    {
        $soalIds = $quiz->soals()->pluck('id');
        $powerRefunded = 0;

        if ($soalIds->isNotEmpty()) {
            // Cari semua penggunaan Power pada soal-soal kuis ini untuk di-refund ke inventory siswa
            $usedPowers = Menjawab::whereIn('soal_id', $soalIds)
                ->whereNotNull('power_id')
                ->get(['siswa_id', 'power_id']);

            foreach ($usedPowers as $up) {
                $inv = SiswaPower::where('siswa_id', $up->siswa_id)
                    ->where('item_id', $up->power_id)
                    ->first();

                if ($inv) {
                    $inv->increment('jumlah');
                    $powerRefunded++;
                }
            }

            Menjawab::whereIn('soal_id', $soalIds)->delete();
        }

        $nilaiDeleted = $quiz->nilais()->delete();

        return [
            'nilai_deleted'  => $nilaiDeleted,
            'power_refunded' => $powerRefunded,
        ];
    }

    /**
     * Reset seluruh pengerjaan siswa pada suatu Ujian.
     */
    public function resetUjianSubmissions(Ujian $ujian): int
    {
        $soalIds = $ujian->soals()->pluck('id');
        if ($soalIds->isNotEmpty()) {
            Menjawab::whereIn('soal_id', $soalIds)->delete();
        }

        $seleksiIds = $ujian->seleksiUjians()->pluck('id');
        if ($seleksiIds->isNotEmpty()) {
            Nilai::whereIn('seleksi_ujian_id', $seleksiIds)->delete();
        }

        return $ujian->seleksiUjians()->delete();
    }

    /**
     * Reset seluruh pengerjaan siswa pada semua aktivitas di dalam satu PengampuKelas.
     */
    protected function resetAllSubmissionsInPengampu(PengampuKelas $pk): int
    {
        $total = 0;

        foreach ($pk->tugases as $tugas) {
            $total += $this->resetTugasSubmissions($tugas);
        }

        foreach ($pk->quizzes as $quiz) {
            $res = $this->resetQuizSubmissions($quiz);
            $total += $res['nilai_deleted'];
        }

        foreach ($pk->ujians as $ujian) {
            $total += $this->resetUjianSubmissions($ujian);
        }

        return $total;
    }

    /**
     * Tukar kepemilikan pengampu_kelas_id seluruh aktivitas antara 2 PengampuKelas.
     */
    protected function swapActivitiesBetweenPengampu(PengampuKelas $pkA, PengampuKelas $pkB): void
    {
        $materiIdsA = $pkA->materis()->pluck('id');
        $materiIdsB = $pkB->materis()->pluck('id');

        $tugasIdsA = $pkA->tugases()->pluck('id');
        $tugasIdsB = $pkB->tugases()->pluck('id');

        $quizIdsA = $pkA->quizzes()->pluck('id');
        $quizIdsB = $pkB->quizzes()->pluck('id');

        $ujianIdsA = $pkA->ujians()->pluck('id');
        $ujianIdsB = $pkB->ujians()->pluck('id');

        if ($materiIdsA->isNotEmpty()) {
            Materi::whereIn('id', $materiIdsA)->update(['pengampu_kelas_id' => $pkB->id]);
        }
        if ($materiIdsB->isNotEmpty()) {
            Materi::whereIn('id', $materiIdsB)->update(['pengampu_kelas_id' => $pkA->id]);
        }

        if ($tugasIdsA->isNotEmpty()) {
            Tugas::whereIn('id', $tugasIdsA)->update(['pengampu_kelas_id' => $pkB->id]);
        }
        if ($tugasIdsB->isNotEmpty()) {
            Tugas::whereIn('id', $tugasIdsB)->update(['pengampu_kelas_id' => $pkA->id]);
        }

        if ($quizIdsA->isNotEmpty()) {
            Quiz::whereIn('id', $quizIdsA)->update(['pengampu_kelas_id' => $pkB->id]);
        }
        if ($quizIdsB->isNotEmpty()) {
            Quiz::whereIn('id', $quizIdsB)->update(['pengampu_kelas_id' => $pkA->id]);
        }

        if ($ujianIdsA->isNotEmpty()) {
            Ujian::whereIn('id', $ujianIdsA)->update(['pengampu_kelas_id' => $pkB->id]);
        }
        if ($ujianIdsB->isNotEmpty()) {
            Ujian::whereIn('id', $ujianIdsB)->update(['pengampu_kelas_id' => $pkA->id]);
        }
    }

    /**
     * Pecah UUID grup (id_grup_ujian & id_grub_materi) jika dalam satu grup terdapat lebih dari 1 guru berbeda,
     * agar tidak memicu error 403 Forbidden saat Guru mengedit/mempublikasikan Ujian atau Materi.
     */
    public function detachCrossTeacherGroups(PengampuKelas $pk): void
    {
        $pk->loadMissing('guruMapel');
        $currentGuruId = $pk->guruMapel?->guru_id;

        if (!$currentGuruId) {
            return;
        }

        // Cek Ujian pada PengampuKelas ini
        $ujians = Ujian::where('pengampu_kelas_id', $pk->id)
            ->whereNotNull('id_grup_ujian')
            ->get();

        foreach ($ujians as $ujian) {
            $hasDifferentTeacher = Ujian::where('id_grup_ujian', $ujian->id_grup_ujian)
                ->where('id', '!=', $ujian->id)
                ->whereHas('pengampuKelas.guruMapel', fn($q) => $q->where('guru_id', '!=', $currentGuruId))
                ->exists();

            if ($hasDifferentTeacher) {
                $ujian->update(['id_grup_ujian' => (string) Str::uuid()]);
            }
        }

        // Cek Materi pada PengampuKelas ini
        $materis = Materi::where('pengampu_kelas_id', $pk->id)
            ->whereNotNull('id_grub_materi')
            ->get();

        foreach ($materis as $materi) {
            $hasDifferentTeacher = Materi::where('id_grub_materi', $materi->id_grub_materi)
                ->where('id', '!=', $materi->id)
                ->whereHas('pengampuKelas.guruMapel', fn($q) => $q->where('guru_id', '!=', $currentGuruId))
                ->exists();

            if ($hasDifferentTeacher) {
                $materi->update(['id_grub_materi' => (string) Str::uuid()]);
            }
        }
    }

    /**
     * Sinkronisasi notifikasi siswa saat aktivitas dipindahkan ke mapel/kelas baru.
     */
    protected function syncActivityNotifications(
        string $tipeNotif,
        int $referensiId,
        PengampuKelas $sourcePk,
        PengampuKelas $targetPk,
        string $judul,
        string $url
    ): void {
        $mapelBaru = $targetPk->guruMapel?->mapel?->nama_mapel ?? 'Mata Pelajaran';

        if ((int) $sourcePk->kelas_id === (int) $targetPk->kelas_id) {
            // Kelas sama -> cukup update pesan pada notifikasi yang sudah ada
            Notifikasi::where('tipe', $tipeNotif)
                ->where('id_referensi', $referensiId)
                ->update([
                    'judul' => $judul,
                    'pesan' => "Aktivitas pembelajaran telah disesuaikan ke mata pelajaran {$mapelBaru}.",
                ]);
            return;
        }

        // Beda kelas -> hapus notifikasi di kelas lama, kirim notifikasi ke kelas tujuan
        Notifikasi::where('tipe', $tipeNotif)
            ->where('id_referensi', $referensiId)
            ->delete();

        Notifikasi::kirimKeKelas(
            $targetPk->kelas_id,
            $judul,
            "Guru telah menerbitkan aktivitas pembelajaran pada mata pelajaran {$mapelBaru}.",
            $tipeNotif,
            $url,
            $referensiId
        );
    }
}

