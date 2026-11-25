<?php

namespace App\Services;

use App\Models\AcademicPeriod;
use App\Models\AnggotaKelas;
use App\Models\PengampuKelas;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AcademicPeriodService
{
    public function getActivePeriod(): AcademicPeriod
    {
        return AcademicPeriod::current();
    }

    /**
     * Mengaktifkan satu periode akademik dan menonaktifkan yang lain.
     */
    public function activatePeriod(AcademicPeriod $period): AcademicPeriod
    {
        return DB::transaction(function () use ($period) {
            AcademicPeriod::where('id', '!=', $period->id)->update(['is_active' => false]);
            $period->update(['is_active' => true]);

            return $period->fresh();
        });
    }

    /**
     * Membuat periode akademik baru (atau mengambil yang sudah ada) lalu mengaktifkannya jika diminta.
     */
    public function createPeriod(
        string $tahunAjaran,
        string $semester,
        ?string $tanggalMulai = null,
        ?string $tanggalSelesai = null,
        bool $activate = false
    ): AcademicPeriod {
        [$startYear, $endYear] = $this->parseYears($tahunAjaran);

        if (!$tanggalMulai || !$tanggalSelesai) {
            if ($semester === 'ganjil') {
                $tanggalMulai   = $tanggalMulai ?? "{$startYear}-07-01";
                $tanggalSelesai = $tanggalSelesai ?? "{$startYear}-12-31";
            } else {
                $tanggalMulai   = $tanggalMulai ?? "{$endYear}-01-01";
                $tanggalSelesai = $tanggalSelesai ?? "{$endYear}-06-30";
            }
        }

        return DB::transaction(function () use ($tahunAjaran, $semester, $tanggalMulai, $tanggalSelesai, $activate) {
            $period = AcademicPeriod::firstOrCreate(
                [
                    'tahun_ajaran' => $tahunAjaran,
                    'semester'     => $semester,
                ],
                [
                    'tanggal_mulai'   => $tanggalMulai,
                    'tanggal_selesai' => $tanggalSelesai,
                    'is_active'       => false,
                ]
            );

            if ($activate) {
                return $this->activatePeriod($period);
            }

            return $period;
        });
    }

    /**
     * Memperbarui data periode akademik (misal koreksi salah input nama tahun ajaran atau tanggal).
     */
    public function updatePeriod(AcademicPeriod $period, array $data): AcademicPeriod
    {
        $period->update([
            'tahun_ajaran'    => $data['tahun_ajaran'],
            'semester'        => $data['semester'],
            'tanggal_mulai'   => $data['tanggal_mulai'] ?? null,
            'tanggal_selesai' => $data['tanggal_selesai'] ?? null,
        ]);

        return $period->fresh();
    }

    /**
     * Menghapus periode akademik jika belum aktif dan belum memiliki relasi anggota kelas/pengampu.
     */
    public function deletePeriod(AcademicPeriod $period): void
    {
        if ($period->is_active) {
            throw new \InvalidArgumentException('Periode yang sedang aktif tidak dapat dihapus.');
        }

        if ($period->anggotaKelases()->exists() || $period->pengampuKelases()->exists()) {
            throw new \InvalidArgumentException('Periode ini sudah memiliki riwayat kelas atau pengampu mapel sehingga tidak dapat dihapus.');
        }

        $period->delete();
    }

    /**
     * Menghasilkan saran periode berikutnya (Ganjil -> Genap di TA sama; Genap -> Ganjil TA baru).
     */
    public function suggestNextPeriod(AcademicPeriod $current): array
    {
        [$startYear, $endYear] = $this->parseYears($current->tahun_ajaran);

        if ($current->semester === 'ganjil') {
            return [
                'tahun_ajaran' => $current->tahun_ajaran,
                'semester'     => 'genap',
                'label'        => "{$current->tahun_ajaran} • Genap",
            ];
        }

        $nextTa = $endYear . '/' . ($endYear + 1);
        return [
            'tahun_ajaran' => $nextTa,
            'semester'     => 'ganjil',
            'label'        => "{$nextTa} • Ganjil",
        ];
    }

    /**
     * Pergantian semester (mis. Ganjil -> Genap pada tahun ajaran yang sama):
     * Menyalin keanggotaan kelas siswa aktif dan opsional pengampu kelas ke periode tujuan
     * tanpa menghapus histori periode sebelumnya.
     */
    public function copySemesterMemberships(
        AcademicPeriod $from,
        AcademicPeriod $to,
        bool $copyPengampu = true,
        bool $activateTarget = true
    ): array {
        return DB::transaction(function () use ($from, $to, $copyPengampu, $activateTarget) {
            $sourceMembers = AnggotaKelas::with('siswa')
                ->where('academic_period_id', $from->id)
                ->get();

            $copiedStudents = 0;
            foreach ($sourceMembers as $member) {
                if (!$member->siswa || $member->siswa->status !== 'aktif') {
                    continue;
                }

                $created = AnggotaKelas::firstOrCreate(
                    [
                        'siswa_id'           => $member->siswa_id,
                        'academic_period_id' => $to->id,
                    ],
                    [
                        'kelas_id' => $member->kelas_id,
                    ]
                );

                if ($created->wasRecentlyCreated) {
                    $copiedStudents++;
                }
            }

            $copiedPengampu = 0;
            if ($copyPengampu) {
                $sourcePengampus = PengampuKelas::where('academic_period_id', $from->id)->get();

                foreach ($sourcePengampus as $pk) {
                    $createdPk = PengampuKelas::firstOrCreate([
                        'guru_mapel_id'      => $pk->guru_mapel_id,
                        'kelas_id'           => $pk->kelas_id,
                        'academic_period_id' => $to->id,
                    ]);

                    if ($createdPk->wasRecentlyCreated) {
                        $copiedPengampu++;
                    }
                }
            }

            if ($activateTarget) {
                $this->activatePeriod($to);
            }

            return [
                'copied_students' => $copiedStudents,
                'copied_pengampu' => $copiedPengampu,
            ];
        });
    }

    /**
     * Kenaikan kelas & kelulusan massal saat pergantian tahun ajaran.
     */
    public function promoteStudents(
        AcademicPeriod $from,
        AcademicPeriod $to,
        array $classMapping,
        array $stayStudentIds = [],
        array $graduateStudentIds = [],
        bool $activateTarget = true
    ): array {
        return DB::transaction(function () use ($from, $to, $classMapping, $stayStudentIds, $graduateStudentIds, $activateTarget) {
            $sourceMembers = AnggotaKelas::with('siswa')
                ->where('academic_period_id', $from->id)
                ->get();

            $promotedCount  = 0;
            $stayedCount    = 0;
            $graduatedCount = 0;

            foreach ($sourceMembers as $member) {
                $siswa = $member->siswa;
                if (!$siswa || $siswa->status !== 'aktif') {
                    continue;
                }

                $target = $classMapping[$member->kelas_id] ?? null;

                if (in_array($siswa->id, $graduateStudentIds, true) || $target === 'lulus') {
                    $siswa->update(['status' => 'lulus']);
                    $graduatedCount++;
                    continue;
                }

                if (in_array($siswa->id, $stayStudentIds, true)) {
                    $created = AnggotaKelas::firstOrCreate(
                        [
                            'siswa_id'           => $siswa->id,
                            'academic_period_id' => $to->id,
                        ],
                        [
                            'kelas_id' => $member->kelas_id,
                        ]
                    );
                    if ($created->wasRecentlyCreated) {
                        $stayedCount++;
                    }
                    continue;
                }

                if ($target && is_numeric($target)) {
                    $created = AnggotaKelas::firstOrCreate(
                        [
                            'siswa_id'           => $siswa->id,
                            'academic_period_id' => $to->id,
                        ],
                        [
                            'kelas_id' => (int) $target,
                        ]
                    );
                    if ($created->wasRecentlyCreated) {
                        $promotedCount++;
                    }
                }
            }

            if ($activateTarget) {
                $this->activatePeriod($to);
            }

            return [
                'promoted'  => $promotedCount,
                'stayed'    => $stayedCount,
                'graduated' => $graduatedCount,
            ];
        });
    }

    private function parseYears(string $tahunAjaran): array
    {
        $parts = preg_split('/[\/\-]/', trim($tahunAjaran));
        $start = isset($parts[0]) && is_numeric($parts[0]) ? (int) $parts[0] : (int) Carbon::now()->format('Y');
        $end   = isset($parts[1]) && is_numeric($parts[1]) ? (int) $parts[1] : ($start + 1);

        return [$start, $end];
    }
}
