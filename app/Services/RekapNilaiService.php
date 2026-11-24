<?php

namespace App\Services;

use App\Models\AcademicPeriod;
use App\Models\AnggotaKelas;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Nilai;
use App\Models\PengampuKelas;
use App\Models\Quiz;
use App\Models\SeleksiUjian;
use App\Models\Siswa;
use App\Models\Tugas;
use App\Models\Ujian;
use Illuminate\Support\Collection;

class RekapNilaiService
{
    /**
     * Hitung predikat nilai berdasarkan skor dan KKM.
     */
    public static function hitungPredikat(?float $nilai, float $kkm = 75.0): string
    {
        if ($nilai === null) {
            return '-';
        }
        if ($nilai >= 90.0) {
            return 'A';
        }
        if ($nilai >= 80.0) {
            return 'B';
        }
        if ($nilai >= $kkm) {
            return 'C';
        }

        return 'D';
    }

    /**
     * Membangun data perekapan nilai keseluruhan untuk Admin (dan reusable untuk role lain).
     *
     * @param  array{
     *     academic_period_id?: int|string|null,
     *     kelas_id?: int|string|null,
     *     mapel_id?: int|string|null,
     *     status?: string|null,
     *     search?: string|null,
     *     mode?: string|null,
     * }  $filters
     */
    public function buildAdminRekap(array $filters = []): array
    {
        $periods = AcademicPeriod::orderByDesc('tahun_ajaran')
            ->orderByRaw("CASE WHEN semester = 'genap' THEN 2 ELSE 1 END DESC")
            ->get();

        $activePeriod = AcademicPeriod::current();
        $selectedPeriodId = !empty($filters['academic_period_id'])
            ? (int) $filters['academic_period_id']
            : ($activePeriod->id ?? $periods->first()?->id);

        $selectedPeriod = $periods->firstWhere('id', $selectedPeriodId) ?? $activePeriod;
        $selectedPeriodId = $selectedPeriod?->id;

        $kelasList = Kelas::with('guru')
            ->orderBy('tingkatan')
            ->orderBy('nama_kelas')
            ->get();

        $selectedKelasId = !empty($filters['kelas_id']) ? (int) $filters['kelas_id'] : null;
        $selectedKelas = $selectedKelasId ? $kelasList->firstWhere('id', $selectedKelasId) : null;

        // Ambil pengampu kelas untuk periode (dan kelas jika dipilih)
        $pengampuQuery = PengampuKelas::with(['guruMapel.mapel', 'guruMapel.guru', 'kelas'])
            ->when($selectedPeriodId, fn ($q) => $q->where('academic_period_id', $selectedPeriodId))
            ->when($selectedKelasId, fn ($q) => $q->where('kelas_id', $selectedKelasId));

        $allPengampus = $pengampuQuery->get()
            ->filter(fn ($p) => $p->guruMapel?->mapel && $p->kelas)
            ->values();

        // Daftar mapel unik yang tersedia pada filter periode (+ kelas) saat ini
        $mapelList = $allPengampus
            ->map(fn ($p) => $p->guruMapel->mapel)
            ->unique('id')
            ->sortBy('nama_mapel')
            ->values();

        if ($mapelList->isEmpty()) {
            $mapelList = Mapel::orderBy('nama_mapel')->get();
        }

        $selectedMapelId = !empty($filters['mapel_id']) ? (int) $filters['mapel_id'] : null;
        $selectedMapel = $selectedMapelId ? $mapelList->firstWhere('id', $selectedMapelId) : null;

        $selectedStatus = $filters['status'] ?? 'semua';
        if (!in_array($selectedStatus, ['semua', 'tuntas', 'belum_tuntas', 'belum_ada'], true)) {
            $selectedStatus = 'semua';
        }

        $search = trim((string) ($filters['search'] ?? ''));

        $mode = $filters['mode'] ?? 'ringkasan';
        if (!in_array($mode, ['ringkasan', 'leger'], true)) {
            $mode = 'ringkasan';
        }
        // Mode leger matriks mapel hanya tersedia jika kelas dipilih dan mapel tidak difilter tunggal
        if ($mode === 'leger' && (!$selectedKelas || $selectedMapel)) {
            $mode = 'ringkasan';
        }

        // Filter pengampu berdasarkan mapel jika dipilih
        $pengampus = $selectedMapelId
            ? $allPengampus->filter(fn ($p) => (int) $p->guruMapel->mapel_id === $selectedMapelId)->values()
            : $allPengampus;

        // Ambil anggota kelas untuk periode (dan kelas jika dipilih)
        $anggotaQuery = AnggotaKelas::with(['siswa.user', 'kelas'])
            ->when($selectedPeriodId, fn ($q) => $q->where('academic_period_id', $selectedPeriodId))
            ->when($selectedKelasId, fn ($q) => $q->where('kelas_id', $selectedKelasId))
            ->whereHas('siswa');

        $anggotaList = $anggotaQuery->get()
            ->unique(fn ($a) => $a->siswa_id . '-' . $a->kelas_id)
            ->values();

        // Jika filter mapel khusus agama dipilih, saring siswa yang berhak mengikuti mapel tersebut
        if ($selectedMapel && filled($selectedMapel->target_agama)) {
            $anggotaList = $anggotaList->filter(
                fn ($a) => $selectedMapel->isAccessibleBySiswa($a->siswa)
            )->values();
        }

        // Pencarian siswa berdasarkan nama atau NIS
        if ($search !== '') {
            $needle = mb_strtolower($search);
            $anggotaList = $anggotaList->filter(function ($a) use ($needle) {
                $nama = mb_strtolower((string) ($a->siswa->nama_siswa ?? $a->siswa->user?->name ?? ''));
                $nis = mb_strtolower((string) ($a->siswa->nis ?? ''));
                $kelas = mb_strtolower((string) ($a->kelas->nama_kelas ?? ''));

                return str_contains($nama, $needle)
                    || str_contains($nis, $needle)
                    || str_contains($kelas, $needle);
            })->values();
        }

        $pengampuIds = $pengampus->pluck('id')->all();
        $siswaIds = $anggotaList->pluck('siswa_id')->unique()->values()->all();

        // Batch query semua komponen penilaian sekaligus (hindari N+1)
        $tugasByPengampu = collect();
        $quizByPengampu = collect();
        $ujianByPengampu = collect();

        $nilaiTugasBySiswa = collect();
        $nilaiQuizBySiswa = collect();
        $seleksiUjianBySiswa = collect();

        if (!empty($pengampuIds) && !empty($siswaIds)) {
            $tugases = Tugas::whereIn('pengampu_kelas_id', $pengampuIds)
                ->select('id', 'judul', 'pengampu_kelas_id')
                ->get();
            $tugasByPengampu = $tugases->groupBy('pengampu_kelas_id');

            $quizzes = Quiz::whereIn('pengampu_kelas_id', $pengampuIds)
                ->where('status', 'published')
                ->select('id', 'judul', 'pengampu_kelas_id')
                ->get();
            $quizByPengampu = $quizzes->groupBy('pengampu_kelas_id');

            $ujians = Ujian::whereIn('pengampu_kelas_id', $pengampuIds)
                ->where(function ($q) {
                    $q->where('jenis_ujian', 'reguler')->orWhereNull('jenis_ujian');
                })
                ->whereIn('status', ['published', 'completed', 'unpublished'])
                ->select('id', 'judul', 'kkm', 'pengampu_kelas_id')
                ->get();
            $ujianByPengampu = $ujians->groupBy('pengampu_kelas_id');

            $tugasIds = $tugases->pluck('id')->all();
            if (!empty($tugasIds)) {
                $nilaiTugasBySiswa = Nilai::whereIn('tugas_id', $tugasIds)
                    ->whereIn('siswa_id', $siswaIds)
                    ->whereNotNull('nilai')
                    ->select('id', 'siswa_id', 'tugas_id', 'nilai', 'status')
                    ->get()
                    ->groupBy('siswa_id');
            }

            $quizIds = $quizzes->pluck('id')->all();
            if (!empty($quizIds)) {
                $nilaiQuizBySiswa = Nilai::whereIn('quiz_id', $quizIds)
                    ->whereIn('siswa_id', $siswaIds)
                    ->where('status', 'selesai')
                    ->whereNotNull('nilai')
                    ->select('id', 'siswa_id', 'quiz_id', 'nilai', 'status')
                    ->get()
                    ->groupBy('siswa_id');
            }

            $ujianIds = $ujians->pluck('id')->all();
            if (!empty($ujianIds)) {
                $seleksiUjianBySiswa = SeleksiUjian::whereIn('ujian_id', $ujianIds)
                    ->whereIn('siswa_id', $siswaIds)
                    ->where(function ($q) {
                        $q->where('status', 'selesai')->orWhereNotNull('waktu_selesai');
                    })
                    ->whereNotNull('nilai_akhir')
                    ->select('id', 'siswa_id', 'ujian_id', 'nilai_awal', 'nilai_akhir', 'jumlah_remidi', 'status')
                    ->get()
                    ->groupBy('siswa_id');
            }
        }

        // Pengampu dikelompokkan per kelas_id
        $pengampuByKelas = $pengampus->groupBy('kelas_id');

        // Daftar kolom mapel untuk mode Leger (jika kelas dipilih)
        $legerMapels = $selectedKelas
            ? ($pengampuByKelas->get($selectedKelas->id) ?? collect())
                ->sortBy(fn ($p) => strtolower((string) $p->guruMapel->mapel->nama_mapel))
                ->values()
            : collect();

        // Hitung baris rekap per siswa
        $rows = $anggotaList->map(function ($anggota) use (
            $pengampuByKelas,
            $tugasByPengampu,
            $quizByPengampu,
            $ujianByPengampu,
            $nilaiTugasBySiswa,
            $nilaiQuizBySiswa,
            $seleksiUjianBySiswa,
            $selectedMapel
        ) {
            $siswa = $anggota->siswa;
            $kelas = $anggota->kelas;
            $pengampuKelasList = ($pengampuByKelas->get($kelas->id) ?? collect())
                ->sortBy(fn ($p) => strtolower((string) $p->guruMapel->mapel->nama_mapel))
                ->values();

            $siswaTugasMap = ($nilaiTugasBySiswa->get($siswa->id) ?? collect())->keyBy('tugas_id');
            $siswaQuizMap = ($nilaiQuizBySiswa->get($siswa->id) ?? collect())->keyBy('quiz_id');
            $siswaUjianMap = ($seleksiUjianBySiswa->get($siswa->id) ?? collect())->keyBy('ujian_id');

            $mapelScores = [];
            $allTugasScores = [];
            $allQuizScores = [];
            $allUjianScores = [];

            $totalTugasTersedia = 0;
            $totalTugasDinilai = 0;
            $totalQuizTersedia = 0;
            $totalQuizDinilai = 0;
            $totalUjianTersedia = 0;
            $totalUjianDinilai = 0;

            $nilaiAkhirPerMapel = [];
            $kkmPerMapel = [];
            $totalMapelWajib = 0;
            $mapelTuntasCount = 0;
            $mapelBelumTuntasCount = 0;
            $mapelDinilaiCount = 0;

            foreach ($pengampuKelasList as $pk) {
                $mapel = $pk->guruMapel->mapel;
                $kkm = (float) ($mapel->kkm ?? 75);

                // Cek kecocokan agama untuk mapel khusus agama
                if (!$mapel->isAccessibleBySiswa($siswa)) {
                    $mapelScores[$mapel->id] = [
                        'accessible' => false,
                        'mapel_id' => $mapel->id,
                        'nama_mapel' => $mapel->nama_mapel,
                        'kkm' => $kkm,
                        'rata_tugas' => null,
                        'rata_kuis' => null,
                        'rata_ujian' => null,
                        'nilai_akhir' => null,
                        'tuntas' => null,
                        'status' => 'na',
                    ];
                    continue;
                }

                $totalMapelWajib++;
                $kkmPerMapel[] = $kkm;

                // 1. Tugas pada pengampu ini
                $pkTugases = $tugasByPengampu->get($pk->id) ?? collect();
                $totalTugasTersedia += $pkTugases->count();
                $pkTugasVals = [];
                foreach ($pkTugases as $t) {
                    $n = $siswaTugasMap->get($t->id);
                    if ($n && $n->nilai !== null) {
                        $val = (float) $n->nilai;
                        $pkTugasVals[] = $val;
                        $allTugasScores[] = $val;
                        $totalTugasDinilai++;
                    }
                }
                $rataTugasMapel = count($pkTugasVals) > 0
                    ? round(array_sum($pkTugasVals) / count($pkTugasVals), 2)
                    : null;

                // 2. Kuis pada pengampu ini
                $pkQuizzes = $quizByPengampu->get($pk->id) ?? collect();
                $totalQuizTersedia += $pkQuizzes->count();
                $pkQuizVals = [];
                foreach ($pkQuizzes as $qz) {
                    $n = $siswaQuizMap->get($qz->id);
                    if ($n && $n->nilai !== null) {
                        $val = (float) $n->nilai;
                        $pkQuizVals[] = $val;
                        $allQuizScores[] = $val;
                        $totalQuizDinilai++;
                    }
                }
                $rataQuizMapel = count($pkQuizVals) > 0
                    ? round(array_sum($pkQuizVals) / count($pkQuizVals), 2)
                    : null;

                // 3. Ujian pada pengampu ini
                $pkUjians = $ujianByPengampu->get($pk->id) ?? collect();
                $totalUjianTersedia += $pkUjians->count();
                $pkUjianVals = [];
                foreach ($pkUjians as $uj) {
                    $s = $siswaUjianMap->get($uj->id);
                    if ($s && $s->nilai_akhir !== null) {
                        $val = (float) $s->nilai_akhir;
                        $pkUjianVals[] = $val;
                        $allUjianScores[] = $val;
                        $totalUjianDinilai++;
                    }
                }
                $rataUjianMapel = count($pkUjianVals) > 0
                    ? round(array_sum($pkUjianVals) / count($pkUjianVals), 2)
                    : null;

                // Nilai akhir mapel = rata-rata komponen yang memiliki nilai
                $komponenAktif = array_values(array_filter(
                    [$rataTugasMapel, $rataQuizMapel, $rataUjianMapel],
                    fn ($v) => $v !== null
                ));

                $nilaiAkhirMapel = count($komponenAktif) > 0
                    ? round(array_sum($komponenAktif) / count($komponenAktif), 2)
                    : null;

                $statusMapel = 'belum_ada';
                $isTuntas = null;
                if ($nilaiAkhirMapel !== null) {
                    $nilaiAkhirPerMapel[] = $nilaiAkhirMapel;
                    $mapelDinilaiCount++;
                    if ($nilaiAkhirMapel >= $kkm) {
                        $isTuntas = true;
                        $statusMapel = 'tuntas';
                        $mapelTuntasCount++;
                    } else {
                        $isTuntas = false;
                        $statusMapel = 'belum_tuntas';
                        $mapelBelumTuntasCount++;
                    }
                }

                $mapelScores[$mapel->id] = [
                    'accessible' => true,
                    'mapel_id' => $mapel->id,
                    'nama_mapel' => $mapel->nama_mapel,
                    'kkm' => $kkm,
                    'rata_tugas' => $rataTugasMapel,
                    'rata_kuis' => $rataQuizMapel,
                    'rata_ujian' => $rataUjianMapel,
                    'nilai_akhir' => $nilaiAkhirMapel,
                    'tuntas' => $isTuntas,
                    'status' => $statusMapel,
                ];
            }

            $rataTugas = count($allTugasScores) > 0
                ? round(array_sum($allTugasScores) / count($allTugasScores), 2)
                : null;
            $rataKuis = count($allQuizScores) > 0
                ? round(array_sum($allQuizScores) / count($allQuizScores), 2)
                : null;
            $rataUjian = count($allUjianScores) > 0
                ? round(array_sum($allUjianScores) / count($allUjianScores), 2)
                : null;

            $nilaiAkhirKeseluruhan = count($nilaiAkhirPerMapel) > 0
                ? round(array_sum($nilaiAkhirPerMapel) / count($nilaiAkhirPerMapel), 2)
                : null;

            // Tentukan KKM acuan dari kolom mapels.kkm (rata-rata KKM mapel wajib siswa jika Semua Mapel)
            $kkmAcuan = $selectedMapel
                ? (float) ($selectedMapel->kkm ?? 75)
                : (count($kkmPerMapel) > 0 ? round(array_sum($kkmPerMapel) / count($kkmPerMapel), 2) : 75.0);

            if ($nilaiAkhirKeseluruhan === null) {
                $statusKeseluruhan = 'belum_ada';
            } elseif ($selectedMapel) {
                $statusKeseluruhan = $nilaiAkhirKeseluruhan >= $kkmAcuan ? 'tuntas' : 'belum_tuntas';
            } else {
                $statusKeseluruhan = ($mapelBelumTuntasCount === 0 && $mapelDinilaiCount > 0)
                    ? 'tuntas'
                    : 'belum_tuntas';
            }

            return [
                'siswa_id' => $siswa->id,
                'nama_siswa' => $siswa->nama_siswa ?? $siswa->user?->name ?? ('Siswa #' . $siswa->id),
                'nis' => $siswa->nis ?? '-',
                'agama' => $siswa->agama ?? '-',
                'kelas_id' => $kelas->id,
                'nama_kelas' => $kelas->nama_kelas,
                'tingkatan' => $kelas->tingkatan,
                'kkm_acuan' => $kkmAcuan,
                'rata_tugas' => $rataTugas,
                'tugas_dinilai' => $totalTugasDinilai,
                'tugas_total' => $totalTugasTersedia,
                'rata_kuis' => $rataKuis,
                'kuis_dinilai' => $totalQuizDinilai,
                'kuis_total' => $totalQuizTersedia,
                'rata_ujian' => $rataUjian,
                'ujian_dinilai' => $totalUjianDinilai,
                'ujian_total' => $totalUjianTersedia,
                'nilai_akhir' => $nilaiAkhirKeseluruhan,
                'predikat' => self::hitungPredikat($nilaiAkhirKeseluruhan, $kkmAcuan),
                'status_ketuntasan' => $statusKeseluruhan,
                'mapel_tuntas' => $mapelTuntasCount,
                'mapel_belum_tuntas' => $mapelBelumTuntasCount,
                'mapel_dinilai' => $mapelDinilaiCount,
                'mapel_wajib' => $totalMapelWajib,
                'mapel_scores' => $mapelScores,
                'peringkat' => null,
            ];
        });

        // Urutkan berdasarkan nilai_akhir tertinggi (null di bawah), lalu nama A-Z, lalu beri peringkat
        $rankedRows = $rows
            ->sort(function ($a, $b) {
                $na = $a['nilai_akhir'];
                $nb = $b['nilai_akhir'];
                if ($na === null && $nb === null) {
                    return strcasecmp($a['nama_siswa'], $b['nama_siswa']);
                }
                if ($na === null) {
                    return 1;
                }
                if ($nb === null) {
                    return -1;
                }
                if (abs($nb - $na) > 0.001) {
                    return $nb <=> $na;
                }

                return strcasecmp($a['nama_siswa'], $b['nama_siswa']);
            })
            ->values();

        $rankCounter = 0;
        $rankedRows = $rankedRows->map(function ($row) use (&$rankCounter) {
            if ($row['nilai_akhir'] !== null) {
                $rankCounter++;
                $row['peringkat'] = $rankCounter;
            }

            return $row;
        });

        // Statistik KPI sebelum filter status agar tetap akurat, atau sesudah filter status?
        // Lebih intuitif jika KPI mengikuti seluruh siswa dalam filter periode/kelas/mapel/search,
        // lalu tabel disaring sesuai tab status ketuntasan.
        $filteredRows = $selectedStatus === 'semua'
            ? $rankedRows
            : $rankedRows->where('status_ketuntasan', $selectedStatus)->values();

        $nilaiValid = $rankedRows->pluck('nilai_akhir')->filter(fn ($v) => $v !== null);
        $tugasValid = $rankedRows->pluck('rata_tugas')->filter(fn ($v) => $v !== null);
        $kuisValid = $rankedRows->pluck('rata_kuis')->filter(fn ($v) => $v !== null);
        $ujianValid = $rankedRows->pluck('rata_ujian')->filter(fn ($v) => $v !== null);

        $totalSiswa = $rankedRows->count();
        $jumlahTuntas = $rankedRows->where('status_ketuntasan', 'tuntas')->count();
        $jumlahBelumTuntas = $rankedRows->where('status_ketuntasan', 'belum_tuntas')->count();
        $jumlahBelumAda = $rankedRows->where('status_ketuntasan', 'belum_ada')->count();
        $siswaDinilai = $jumlahTuntas + $jumlahBelumTuntas;

        $topStudent = $rankedRows->first(fn ($r) => $r['nilai_akhir'] !== null);
        $lowestStudent = $rankedRows->filter(fn ($r) => $r['nilai_akhir'] !== null)->last();

        $summary = [
            'total_siswa' => $totalSiswa,
            'siswa_dinilai' => $siswaDinilai,
            'jumlah_tuntas' => $jumlahTuntas,
            'jumlah_belum_tuntas' => $jumlahBelumTuntas,
            'jumlah_belum_ada' => $jumlahBelumAda,
            'persen_tuntas' => $siswaDinilai > 0 ? round(($jumlahTuntas / $siswaDinilai) * 100, 1) : 0,
            'rata_keseluruhan' => $nilaiValid->isNotEmpty() ? round($nilaiValid->avg(), 2) : null,
            'rata_tugas' => $tugasValid->isNotEmpty() ? round($tugasValid->avg(), 2) : null,
            'rata_kuis' => $kuisValid->isNotEmpty() ? round($kuisValid->avg(), 2) : null,
            'rata_ujian' => $ujianValid->isNotEmpty() ? round($ujianValid->avg(), 2) : null,
            'nilai_tertinggi' => $topStudent['nilai_akhir'] ?? null,
            'siswa_tertinggi' => $topStudent['nama_siswa'] ?? null,
            'nilai_terendah' => $lowestStudent['nilai_akhir'] ?? null,
            'siswa_terendah' => $lowestStudent['nama_siswa'] ?? null,
        ];

        // Ringkasan rata-rata per kelas (berguna ketika filter Semua Kelas)
        $rekapPerKelas = $rankedRows
            ->groupBy('kelas_id')
            ->map(function (Collection $group) {
                $first = $group->first();
                $vals = $group->pluck('nilai_akhir')->filter(fn ($v) => $v !== null);
                $kkmVals = $group->pluck('kkm_acuan')->filter(fn ($v) => $v !== null);
                $tuntas = $group->where('status_ketuntasan', 'tuntas')->count();
                $dinilai = $group->whereIn('status_ketuntasan', ['tuntas', 'belum_tuntas'])->count();

                return [
                    'kelas_id' => $first['kelas_id'],
                    'nama_kelas' => $first['nama_kelas'],
                    'tingkatan' => $first['tingkatan'],
                    'total_siswa' => $group->count(),
                    'siswa_dinilai' => $dinilai,
                    'jumlah_tuntas' => $tuntas,
                    'persen_tuntas' => $dinilai > 0 ? round(($tuntas / $dinilai) * 100, 1) : 0,
                    'rata_nilai' => $vals->isNotEmpty() ? round($vals->avg(), 2) : null,
                    'rata_kkm' => $kkmVals->isNotEmpty() ? round($kkmVals->avg(), 2) : 75.0,
                ];
            })
            ->sortBy([['tingkatan', 'asc'], ['nama_kelas', 'asc']])
            ->values();

        return [
            'periods' => $periods,
            'selectedPeriod' => $selectedPeriod,
            'selectedPeriodId' => $selectedPeriodId,
            'kelasList' => $kelasList,
            'selectedKelas' => $selectedKelas,
            'selectedKelasId' => $selectedKelasId,
            'mapelList' => $mapelList,
            'selectedMapel' => $selectedMapel,
            'selectedMapelId' => $selectedMapelId,
            'selectedStatus' => $selectedStatus,
            'search' => $search,
            'mode' => $mode,
            'legerMapels' => $legerMapels,
            'rows' => $filteredRows,
            'allRowsCount' => $totalSiswa,
            'summary' => $summary,
            'rekapPerKelas' => $rekapPerKelas,
        ];
    }

    /**
     * Membangun transkrip detail nilai seorang siswa pada periode akademik tertentu.
     */
    public function buildSiswaDetail(Siswa $siswa, ?int $academicPeriodId = null): array
    {
        $periods = AcademicPeriod::orderByDesc('tahun_ajaran')
            ->orderByRaw("CASE WHEN semester = 'genap' THEN 2 ELSE 1 END DESC")
            ->get();

        $activePeriod = AcademicPeriod::current();
        $selectedPeriodId = $academicPeriodId ?: ($activePeriod->id ?? $periods->first()?->id);
        $selectedPeriod = $periods->firstWhere('id', $selectedPeriodId) ?? $activePeriod;
        $selectedPeriodId = $selectedPeriod?->id;

        $siswa->loadMissing('user');

        $anggota = AnggotaKelas::with('kelas.guru')
            ->where('siswa_id', $siswa->id)
            ->when($selectedPeriodId, fn ($q) => $q->where('academic_period_id', $selectedPeriodId))
            ->first();

        $kelas = $anggota?->kelas;
        $detailMapels = collect();

        if ($kelas) {
            $pengampus = PengampuKelas::with(['guruMapel.mapel', 'guruMapel.guru'])
                ->where('kelas_id', $kelas->id)
                ->when($selectedPeriodId, fn ($q) => $q->where('academic_period_id', $selectedPeriodId))
                ->get()
                ->filter(fn ($p) => $p->guruMapel?->mapel && $p->guruMapel->mapel->isAccessibleBySiswa($siswa))
                ->sortBy(fn ($p) => strtolower((string) $p->guruMapel->mapel->nama_mapel))
                ->values();

            $pengampuIds = $pengampus->pluck('id')->all();

            $tugases = Tugas::whereIn('pengampu_kelas_id', $pengampuIds)
                ->orderBy('created_at')
                ->get()
                ->groupBy('pengampu_kelas_id');

            $quizzes = Quiz::whereIn('pengampu_kelas_id', $pengampuIds)
                ->where('status', 'published')
                ->orderBy('created_at')
                ->get()
                ->groupBy('pengampu_kelas_id');

            $ujians = Ujian::whereIn('pengampu_kelas_id', $pengampuIds)
                ->where(function ($q) {
                    $q->where('jenis_ujian', 'reguler')->orWhereNull('jenis_ujian');
                })
                ->whereIn('status', ['published', 'completed', 'unpublished'])
                ->orderBy('waktu_mulai')
                ->orderBy('id')
                ->get()
                ->groupBy('pengampu_kelas_id');

            $allTugasIds = $tugases->flatten()->pluck('id')->all();
            $allQuizIds = $quizzes->flatten()->pluck('id')->all();
            $allUjianIds = $ujians->flatten()->pluck('id')->all();

            $nilaiTugasMap = !empty($allTugasIds)
                ? Nilai::where('siswa_id', $siswa->id)->whereIn('tugas_id', $allTugasIds)->get()->keyBy('tugas_id')
                : collect();

            $nilaiQuizMap = !empty($allQuizIds)
                ? Nilai::where('siswa_id', $siswa->id)->whereIn('quiz_id', $allQuizIds)->get()->keyBy('quiz_id')
                : collect();

            $seleksiUjianMap = !empty($allUjianIds)
                ? SeleksiUjian::where('siswa_id', $siswa->id)->whereIn('ujian_id', $allUjianIds)->get()->keyBy('ujian_id')
                : collect();

            $detailMapels = $pengampus->map(function ($pk) use ($tugases, $quizzes, $ujians, $nilaiTugasMap, $nilaiQuizMap, $seleksiUjianMap) {
                $mapel = $pk->guruMapel->mapel;
                $guru = $pk->guruMapel->guru;
                $kkm = (float) ($mapel->kkm ?? 75);

                // Rincian Tugas
                $tugasItems = ($tugases->get($pk->id) ?? collect())->map(function ($t) use ($nilaiTugasMap) {
                    $n = $nilaiTugasMap->get($t->id);
                    $status = 'belum_mengumpulkan';
                    if ($n) {
                        $status = $n->nilai !== null ? 'dinilai' : 'menunggu_penilaian';
                    }

                    return [
                        'id' => $t->id,
                        'judul' => $t->judul,
                        'deadline' => $t->deadline,
                        'nilai' => $n?->nilai !== null ? round((float) $n->nilai, 2) : null,
                        'status_pengumpulan' => $n?->status,
                        'status' => $status,
                    ];
                })->values();

                // Rincian Kuis
                $quizItems = ($quizzes->get($pk->id) ?? collect())->map(function ($qz) use ($nilaiQuizMap) {
                    $n = $nilaiQuizMap->get($qz->id);
                    $selesai = $n && $n->status === 'selesai' && $n->nilai !== null;

                    return [
                        'id' => $qz->id,
                        'judul' => $qz->judul,
                        'nilai' => $selesai ? round((float) $n->nilai, 2) : null,
                        'durasi' => $n?->durasi_formatted,
                        'status' => $selesai ? 'selesai' : 'belum_dikerjakan',
                    ];
                })->values();

                // Rincian Ujian (KKM mengikuti kolom kkm pada tabel mapels)
                $ujianItems = ($ujians->get($pk->id) ?? collect())->map(function ($uj) use ($seleksiUjianMap, $kkm) {
                    $s = $seleksiUjianMap->get($uj->id);
                    $selesai = $s && ($s->status === 'selesai' || !is_null($s->waktu_selesai)) && $s->nilai_akhir !== null;

                    return [
                        'id' => $uj->id,
                        'judul' => $uj->judul,
                        'kkm' => $kkm,
                        'waktu_mulai' => $uj->waktu_mulai,
                        'nilai_awal' => $selesai ? round((float) $s->nilai_awal, 2) : null,
                        'nilai_akhir' => $selesai ? round((float) $s->nilai_akhir, 2) : null,
                        'jumlah_remidi' => (int) ($s->jumlah_remidi ?? 0),
                        'status' => $selesai ? 'selesai' : ($s ? 'sedang_dikerjakan' : 'belum_ujian'),
                    ];
                })->values();

                $tugasVals = $tugasItems->pluck('nilai')->filter(fn ($v) => $v !== null);
                $quizVals = $quizItems->pluck('nilai')->filter(fn ($v) => $v !== null);
                $ujianVals = $ujianItems->pluck('nilai_akhir')->filter(fn ($v) => $v !== null);

                $rataTugas = $tugasVals->isNotEmpty() ? round($tugasVals->avg(), 2) : null;
                $rataKuis = $quizVals->isNotEmpty() ? round($quizVals->avg(), 2) : null;
                $rataUjian = $ujianVals->isNotEmpty() ? round($ujianVals->avg(), 2) : null;

                $komponen = array_values(array_filter([$rataTugas, $rataKuis, $rataUjian], fn ($v) => $v !== null));
                $nilaiAkhir = count($komponen) > 0 ? round(array_sum($komponen) / count($komponen), 2) : null;

                $status = 'belum_ada';
                if ($nilaiAkhir !== null) {
                    $status = $nilaiAkhir >= $kkm ? 'tuntas' : 'belum_tuntas';
                }

                return [
                    'pengampu_kelas_id' => $pk->id,
                    'mapel_id' => $mapel->id,
                    'nama_mapel' => $mapel->nama_mapel,
                    'tipe_label' => $mapel->tipe_label,
                    'kkm' => $kkm,
                    'nama_guru' => $guru->nama ?? '-',
                    'rata_tugas' => $rataTugas,
                    'rata_kuis' => $rataKuis,
                    'rata_ujian' => $rataUjian,
                    'nilai_akhir' => $nilaiAkhir,
                    'predikat' => self::hitungPredikat($nilaiAkhir, $kkm),
                    'status' => $status,
                    'tugas_items' => $tugasItems,
                    'quiz_items' => $quizItems,
                    'ujian_items' => $ujianItems,
                ];
            });
        }

        $nilaiValid = $detailMapels->pluck('nilai_akhir')->filter(fn ($v) => $v !== null);
        $rataKeseluruhan = $nilaiValid->isNotEmpty() ? round($nilaiValid->avg(), 2) : null;
        $rataKkm = $detailMapels->isNotEmpty() ? round((float) $detailMapels->avg('kkm'), 2) : 75.0;

        return [
            'siswa' => $siswa,
            'kelas' => $kelas,
            'periods' => $periods,
            'selectedPeriod' => $selectedPeriod,
            'selectedPeriodId' => $selectedPeriodId,
            'detailMapels' => $detailMapels,
            'summary' => [
                'total_mapel' => $detailMapels->count(),
                'mapel_dinilai' => $nilaiValid->count(),
                'mapel_tuntas' => $detailMapels->where('status', 'tuntas')->count(),
                'mapel_belum_tuntas' => $detailMapels->where('status', 'belum_tuntas')->count(),
                'rata_keseluruhan' => $rataKeseluruhan,
                'rata_kkm' => $rataKkm,
                'predikat' => self::hitungPredikat($rataKeseluruhan, $rataKkm),
            ],
        ];
    }
}
