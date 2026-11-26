<?php

namespace App\Services;

use App\Models\AcademicPeriod;
use App\Models\Guru;
use App\Models\Menjawab;
use App\Models\Nilai;
use App\Models\Notifikasi;
use App\Models\Quiz;
use App\Models\Tugas;
use App\Models\Ujian;
use App\Models\User;
use Carbon\Carbon;

class NotifikasiSchedulerService
{
    /**
     * Kirim pengingat deadline tugas (H-24 jam & H-3 jam) khusus siswa yang belum mengumpulkan.
     */
    public function kirimPengingatDeadlineTugas(): int
    {
        $now = now();
        $periodId = AcademicPeriod::currentId();
        $totalSent = 0;

        $tugases = Tugas::query()
            ->with(['pengampuKelas.guruMapel.mapel', 'pengampuKelas.kelas'])
            ->where('deadline', '>', $now)
            ->where('deadline', '<=', $now->copy()->addHours(24))
            ->get();

        foreach ($tugases as $tugas) {
            $pk = $tugas->pengampuKelas;
            if (!$pk || !$pk->kelas_id) {
                continue;
            }

            $deadline = Carbon::parse($tugas->deadline);
            $hoursLeft = $now->diffInMinutes($deadline) / 60;
            $window = $hoursLeft <= 3 ? 'h3' : 'h24';
            $labelWaktu = $window === 'h3' ? 'kurang dari 3 jam lagi' : 'dalam 24 jam ke depan';

            $mapel = $pk->guruMapel?->mapel;
            $mapelNama = $mapel?->nama_mapel ?? 'Mata Pelajaran';
            $targetAgama = $mapel?->target_agama;

            // Siswa yang sudah mengumpulkan tugas ini
            $submittedSiswaIds = Nilai::where('tugas_id', $tugas->id)
                ->pluck('siswa_id')
                ->all();

            // User siswa target di kelas yang belum mengumpulkan
            $candidateUserIds = User::where('status', '!=', 'nonaktif')
                ->whereHas('siswa', function ($sq) use ($pk, $periodId, $targetAgama, $submittedSiswaIds) {
                    $sq->where('status', '!=', 'nonaktif')
                        ->when(!empty($submittedSiswaIds), fn ($q) => $q->whereNotIn('id', $submittedSiswaIds))
                        ->when(filled($targetAgama), fn ($aq) => $aq->where('agama', $targetAgama))
                        ->whereHas('anggotaKelases', function ($ak) use ($pk, $periodId) {
                            $ak->where('kelas_id', $pk->kelas_id)
                                ->when($periodId, fn ($pq) => $pq->where('academic_period_id', $periodId));
                        });
                })
                ->pluck('id')
                ->all();

            if (empty($candidateUserIds)) {
                continue;
            }

            // Hindari duplikasi untuk jendela waktu yang sama
            $alreadyNotifiedUserIds = Notifikasi::query()
                ->where('tipe', 'pengingat_deadline')
                ->where('id_referensi', $tugas->id)
                ->where('data->window', $window)
                ->whereIn('user_id', $candidateUserIds)
                ->pluck('user_id')
                ->all();

            $targetUserIds = array_values(array_diff($candidateUserIds, $alreadyNotifiedUserIds));
            if (empty($targetUserIds)) {
                continue;
            }

            $deadlineFormatted = $deadline->locale('id')->isoFormat('D MMMM Y, HH:mm');

            $totalSent += Notifikasi::bulkInsertForUserIds(
                $targetUserIds,
                "Pengingat Deadline Tugas: {$tugas->judul}",
                "Tugas {$mapelNama} '{$tugas->judul}' akan berakhir {$labelWaktu} (Batas: {$deadlineFormatted} WIB). Segera kumpulkan tugas Anda.",
                'pengingat_deadline',
                route('siswa.tugas.show', $tugas->id),
                $tugas->id,
                [
                    'resource_type' => 'tugas',
                    'resource_id'   => $tugas->id,
                    'tugas_id'      => $tugas->id,
                    'mapel_id'      => $mapel?->id,
                    'window'        => $window,
                ]
            );
        }

        return $totalSent;
    }

    /**
     * Kirim pengingat 30 menit sebelum Ujian atau Kuis dimulai.
     */
    public function kirimPengingatJadwalUjianDanKuis(): int
    {
        $now = now();
        $until = $now->copy()->addMinutes(30);
        $periodId = AcademicPeriod::currentId();
        $totalSent = 0;

        // 1. Ujian yang akan mulai dalam <= 30 menit
        $ujians = Ujian::query()
            ->with(['pengampuKelas.guruMapel.mapel'])
            ->whereIn('status', ['scheduled', 'published'])
            ->whereNotNull('waktu_mulai')
            ->where('waktu_mulai', '>', $now)
            ->where('waktu_mulai', '<=', $until)
            ->get();

        foreach ($ujians as $ujian) {
            $pk = $ujian->pengampuKelas;
            if (!$pk || !$pk->kelas_id) {
                continue;
            }

            $mapel = $pk->guruMapel?->mapel;
            $mapelNama = $mapel?->nama_mapel ?? 'Mata Pelajaran';
            $isKhusus = in_array($ujian->jenis_ujian ?? 'reguler', ['remidi', 'susulan'], true);

            if ($isKhusus) {
                $pesertaKhusus = is_array($ujian->peserta_khusus) ? $ujian->peserta_khusus : [];
                $candidateUserIds = empty($pesertaKhusus) ? [] : User::where('status', '!=', 'nonaktif')
                    ->whereHas('siswa', fn ($sq) => $sq->where('status', '!=', 'nonaktif')->whereIn('id', $pesertaKhusus))
                    ->pluck('id')
                    ->all();
            } else {
                $candidateUserIds = User::where('status', '!=', 'nonaktif')
                    ->whereHas('siswa', function ($sq) use ($pk, $periodId, $mapel) {
                        $sq->where('status', '!=', 'nonaktif')
                            ->when(filled($mapel?->target_agama), fn ($aq) => $aq->where('agama', $mapel->target_agama))
                            ->whereHas('anggotaKelases', function ($ak) use ($pk, $periodId) {
                                $ak->where('kelas_id', $pk->kelas_id)
                                    ->when($periodId, fn ($pq) => $pq->where('academic_period_id', $periodId));
                            });
                    })
                    ->pluck('id')
                    ->all();
            }

            if (empty($candidateUserIds)) {
                continue;
            }

            $alreadyNotified = Notifikasi::query()
                ->where('tipe', 'pengingat_ujian')
                ->where('id_referensi', $ujian->id)
                ->where('data->resource_type', 'ujian')
                ->whereIn('user_id', $candidateUserIds)
                ->pluck('user_id')
                ->all();

            $targetUserIds = array_values(array_diff($candidateUserIds, $alreadyNotified));
            if (empty($targetUserIds)) {
                continue;
            }

            $jamMulai = Carbon::parse($ujian->waktu_mulai)->locale('id')->translatedFormat('H:i');

            $totalSent += Notifikasi::bulkInsertForUserIds(
                $targetUserIds,
                "Ujian Segera Dimulai: {$ujian->judul}",
                "Ujian {$mapelNama} '{$ujian->judul}' akan dimulai pukul {$jamMulai} WIB. Pastikan persiapan Anda sudah siap.",
                'pengingat_ujian',
                route('siswa.ujian.show', $ujian->id),
                $ujian->id,
                [
                    'resource_type' => 'ujian',
                    'resource_id'   => $ujian->id,
                    'ujian_id'      => $ujian->id,
                    'mapel_id'      => $mapel?->id,
                ]
            );
        }

        // 2. Kuis terjadwal yang akan mulai dalam <= 30 menit
        $quizzes = Quiz::query()
            ->with(['pengampuKelas.guruMapel.mapel'])
            ->where('status', 'published')
            ->whereNotNull('waktu_mulai')
            ->where('waktu_mulai', '>', $now)
            ->where('waktu_mulai', '<=', $until)
            ->get();

        foreach ($quizzes as $quiz) {
            $pk = $quiz->pengampuKelas;
            if (!$pk || !$pk->kelas_id) {
                continue;
            }

            $mapel = $pk->guruMapel?->mapel;
            $mapelNama = $mapel?->nama_mapel ?? 'Mata Pelajaran';

            $candidateUserIds = User::where('status', '!=', 'nonaktif')
                ->whereHas('siswa', function ($sq) use ($pk, $periodId, $mapel) {
                    $sq->where('status', '!=', 'nonaktif')
                        ->when(filled($mapel?->target_agama), fn ($aq) => $aq->where('agama', $mapel->target_agama))
                        ->whereHas('anggotaKelases', function ($ak) use ($pk, $periodId) {
                            $ak->where('kelas_id', $pk->kelas_id)
                                ->when($periodId, fn ($pq) => $pq->where('academic_period_id', $periodId));
                        });
                })
                ->pluck('id')
                ->all();

            if (empty($candidateUserIds)) {
                continue;
            }

            $alreadyNotified = Notifikasi::query()
                ->where('tipe', 'pengingat_ujian')
                ->where('id_referensi', $quiz->id)
                ->where('data->resource_type', 'quiz')
                ->whereIn('user_id', $candidateUserIds)
                ->pluck('user_id')
                ->all();

            $targetUserIds = array_values(array_diff($candidateUserIds, $alreadyNotified));
            if (empty($targetUserIds)) {
                continue;
            }

            $jamMulai = Carbon::parse($quiz->waktu_mulai)->locale('id')->translatedFormat('H:i');

            $totalSent += Notifikasi::bulkInsertForUserIds(
                $targetUserIds,
                "Kuis Segera Dimulai: {$quiz->judul}",
                "Kuis {$mapelNama} '{$quiz->judul}' akan dimulai pukul {$jamMulai} WIB.",
                'pengingat_ujian',
                route('siswa.kuis.show', $quiz->id),
                $quiz->id,
                [
                    'resource_type' => 'quiz',
                    'resource_id'   => $quiz->id,
                    'quiz_id'       => $quiz->id,
                    'mapel_id'      => $mapel?->id,
                ]
            );
        }

        return $totalSent;
    }

    /**
     * Kirim ringkasan pengingat harian ke guru jika ada tugas atau essay ujian yang belum dinilai.
     */
    public function kirimPengingatKoreksiGuru(): int
    {
        $totalSent = 0;

        $gurus = Guru::with('user')
            ->whereHas('user', fn ($q) => $q->where('status', '!=', 'nonaktif'))
            ->get();

        foreach ($gurus as $guru) {
            $guruUser = $guru->user;
            if (!$guruUser) {
                continue;
            }

            // Maksimal 1 kali per hari per guru
            $alreadySentToday = Notifikasi::query()
                ->where('user_id', $guruUser->id)
                ->where('tipe', 'pengingat_koreksi')
                ->where('created_at', '>=', now()->startOfDay())
                ->exists();

            if ($alreadySentToday) {
                continue;
            }

            $pendingTugas = Nilai::query()
                ->whereNull('nilai')
                ->whereNotNull('tugas_id')
                ->whereHas('tugas.pengampuKelas.guruMapel', fn ($q) => $q->where('guru_id', $guru->id))
                ->count();

            $pendingEssay = Menjawab::query()
                ->whereNull('skor_essay')
                ->whereHas('soal', function ($sq) use ($guru) {
                    $sq->where('tipe_soal', 'essay')
                        ->whereNotNull('ujian_id')
                        ->whereHas('ujian.pengampuKelas.guruMapel', fn ($q) => $q->where('guru_id', $guru->id));
                })
                ->count();

            if ($pendingTugas === 0 && $pendingEssay === 0) {
                continue;
            }

            $bagian = [];
            if ($pendingTugas > 0) {
                $bagian[] = "{$pendingTugas} pengumpulan tugas";
            }
            if ($pendingEssay > 0) {
                $bagian[] = "{$pendingEssay} jawaban essay ujian";
            }

            $ringkasan = implode(' dan ', $bagian);
            $url = $pendingTugas > 0 ? route('guru.tugas') : route('guru.ujian.index');

            Notifikasi::kirim(
                $guruUser->id,
                'Pengingat Koreksi Evaluasi Siswa',
                "Anda memiliki {$ringkasan} yang menunggu untuk dinilai.",
                'pengingat_koreksi',
                $url,
                null,
                [
                    'resource_type' => 'koreksi',
                    'pending_tugas' => $pendingTugas,
                    'pending_essay' => $pendingEssay,
                ]
            );

            $totalSent++;
        }

        return $totalSent;
    }

    /**
     * Auto-pruning: hapus notifikasi yang sudah dibaca lebih dari N hari (default 30 hari).
     */
    public function pruneNotifikasiLama(int $days = 30): int
    {
        $cutoff = now()->subDays(max(1, $days));

        return Notifikasi::query()
            ->where('is_read', true)
            ->where(function ($q) use ($cutoff) {
                $q->where('read_at', '<=', $cutoff)
                    ->orWhere(function ($q2) use ($cutoff) {
                        $q2->whereNull('read_at')->where('updated_at', '<=', $cutoff);
                    });
            })
            ->delete();
    }
}
