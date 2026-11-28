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
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RekapNilaiService
{
    /**
     * Hitung batas interval predikat secara proporsional berdasarkan KKM.
     *
     * @return array{kkm: float, c_max: float, b_min: float, b_max: float, a_min: float}
     */
    public static function predikatThreshold(float $kkm = 75.0): array
    {
        $kkm = max(0.0, min(100.0, $kkm));
        $interval = (100.0 - $kkm) / 3.0;
        $bMin = round($kkm + $interval, 2);
        $aMin = round($kkm + ($interval * 2.0), 2);

        return [
            'kkm' => $kkm,
            'b_min' => $bMin,
            'a_min' => $aMin,
        ];
    }

    /**
     * Hitung predikat nilai berdasarkan skor dan KKM secara proporsional.
     * Nilai di bawah KKM selalu mendapat predikat D.
     */
    public static function hitungPredikat(?float $nilai, float $kkm = 75.0): string
    {
        if ($nilai === null) {
            return '-';
        }
        if ($nilai < $kkm) {
            return 'D';
        }
        if ($kkm >= 100.0) {
            return 'A';
        }

        $threshold = self::predikatThreshold($kkm);
        if ($nilai >= $threshold['a_min']) {
            return 'A';
        }
        if ($nilai >= $threshold['b_min']) {
            return 'B';
        }

        return 'C';
    }

    /**
     * Hitung nilai akhir mapel dari 3 komponen (Tugas, Kuis, Ujian) sesuai metode perhitungan.
     */
    public static function hitungNilaiAkhirKomponen(
        ?float $rataTugas,
        ?float $rataKuis,
        ?float $rataUjian,
        string $metodeHitung = 'rata'
    ): ?float {
        if ($metodeHitung === 'bobot') {
            $bobotMap = [
                'tugas' => ['val' => $rataTugas, 'weight' => 30.0],
                'kuis' => ['val' => $rataKuis, 'weight' => 20.0],
                'ujian' => ['val' => $rataUjian, 'weight' => 50.0],
            ];

            $weightedSum = 0.0;
            $totalWeight = 0.0;

            foreach ($bobotMap as $item) {
                if ($item['val'] !== null) {
                    $weightedSum += $item['val'] * $item['weight'];
                    $totalWeight += $item['weight'];
                }
            }

            return $totalWeight > 0 ? round($weightedSum / $totalWeight, 2) : null;
        }

        $komponenAktif = array_values(array_filter(
            [$rataTugas, $rataKuis, $rataUjian],
            fn ($v) => $v !== null
        ));

        return count($komponenAktif) > 0
            ? round(array_sum($komponenAktif) / count($komponenAktif), 2)
            : null;
    }

    /**
     * Membangun data perekapan nilai khusus untuk Guru (hanya memantau mapel & kelas yang diampu).
     */
    public function buildGuruRekap(int $guruId, array $filters = []): array
    {
        return $this->buildAdminRekap($filters, $guruId);
    }

    /**
     * Membangun data perekapan nilai keseluruhan untuk Admin (dan reusable untuk role Guru).
     *
     * @param  array{
     *     academic_period_id?: int|string|null,
     *     kelas_id?: int|string|null,
     *     mapel_id?: int|string|null,
     *     status?: string|null,
     *     search?: string|null,
     *     mode?: string|null,
     *     hitung_kosong?: string|null,
     *     metode_hitung?: string|null,
     *     sort_by?: string|null,
     *     sort_dir?: string|null,
     * }  $filters
     */
    public function buildAdminRekap(array $filters = [], ?int $guruId = null): array
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

        if ($guruId !== null) {
            // Scope khusus Guru: hanya pengampu, mapel, dan kelas yang benar-benar diampu guru pada periode ini
            $guruPengampusForPeriod = PengampuKelas::with(['guruMapel.mapel', 'guruMapel.guru', 'kelas.guru'])
                ->whereHas('guruMapel', fn ($q) => $q->where('guru_id', $guruId))
                ->when($selectedPeriodId, fn ($q) => $q->where('academic_period_id', $selectedPeriodId))
                ->get()
                ->filter(fn ($p) => $p->guruMapel?->mapel && $p->kelas)
                ->values();

            // Daftar semua mapel yang diampu guru pada periode ini (selalu lengkap agar guru bebas berpindah mapel)
            $mapelList = $guruPengampusForPeriod
                ->map(fn ($p) => $p->guruMapel->mapel)
                ->unique('id')
                ->sortBy('nama_mapel')
                ->values();

            $selectedMapelId = !empty($filters['mapel_id']) ? (int) $filters['mapel_id'] : null;
            $selectedMapel = $selectedMapelId ? $mapelList->firstWhere('id', $selectedMapelId) : null;
            if (!$selectedMapel && $mapelList->isNotEmpty()) {
                $selectedMapel = $mapelList->first();
            }
            $selectedMapelId = $selectedMapel?->id;

            // Daftar kelas yang diampu guru untuk mapel terpilih
            $pengampusBySelectedMapel = $selectedMapelId
                ? $guruPengampusForPeriod->filter(fn ($p) => (int) $p->guruMapel->mapel_id === $selectedMapelId)->values()
                : $guruPengampusForPeriod;

            $kelasList = $pengampusBySelectedMapel
                ->map(fn ($p) => $p->kelas)
                ->unique('id')
                ->sortBy([['tingkatan', 'asc'], ['nama_kelas', 'asc']])
                ->values();

            $selectedKelasId = !empty($filters['kelas_id']) ? (int) $filters['kelas_id'] : null;
            $selectedKelas = $selectedKelasId ? $kelasList->firstWhere('id', $selectedKelasId) : null;
            if (!$selectedKelas && $kelasList->isNotEmpty()) {
                $selectedKelas = $kelasList->first();
            }
            $selectedKelasId = $selectedKelas?->id;

            $allPengampus = $selectedKelasId
                ? $guruPengampusForPeriod->where('kelas_id', $selectedKelasId)->values()
                : $guruPengampusForPeriod;
        } else {
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
        }

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

        $hitungKosong = $filters['hitung_kosong'] ?? 'abaikan';
        if (!in_array($hitungKosong, ['abaikan', 'nol'], true)) {
            $hitungKosong = 'abaikan';
        }

        $metodeHitung = $filters['metode_hitung'] ?? 'rata';
        if (!in_array($metodeHitung, ['rata', 'bobot'], true)) {
            $metodeHitung = 'rata';
        }

        $allowedSorts = ['peringkat', 'nilai_akhir', 'nama_siswa', 'nis', 'nama_kelas', 'rata_tugas', 'rata_kuis', 'rata_ujian'];
        $sortBy = $filters['sort_by'] ?? 'peringkat';
        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'peringkat';
        }

        $sortDir = strtolower((string) ($filters['sort_dir'] ?? ($sortBy === 'nilai_akhir' ? 'desc' : 'asc')));
        if (!in_array($sortDir, ['asc', 'desc'], true)) {
            $sortDir = 'asc';
        }

        // Filter pengampu berdasarkan mapel jika dipilih
        $pengampus = $selectedMapelId
            ? $allPengampus->filter(fn ($p) => (int) $p->guruMapel->mapel_id === $selectedMapelId)->values()
            : $allPengampus;

        // Ambil anggota kelas untuk periode (dan kelas jika dipilih)
        if ($guruId !== null) {
            $allowedKelasIds = $pengampus->pluck('kelas_id')->unique()->values()->all();
            if (empty($allowedKelasIds)) {
                $anggotaList = collect();
            } else {
                $anggotaQuery = AnggotaKelas::with(['siswa.user', 'kelas'])
                    ->when($selectedPeriodId, fn ($q) => $q->where('academic_period_id', $selectedPeriodId))
                    ->whereIn('kelas_id', $allowedKelasIds)
                    ->whereHas('siswa');

                $anggotaList = $anggotaQuery->get()
                    ->unique(fn ($a) => $a->siswa_id . '-' . $a->kelas_id)
                    ->values();
            }
        } else {
            $anggotaQuery = AnggotaKelas::with(['siswa.user', 'kelas'])
                ->when($selectedPeriodId, fn ($q) => $q->where('academic_period_id', $selectedPeriodId))
                ->when($selectedKelasId, fn ($q) => $q->where('kelas_id', $selectedKelasId))
                ->whereHas('siswa');

            $anggotaList = $anggotaQuery->get()
                ->unique(fn ($a) => $a->siswa_id . '-' . $a->kelas_id)
                ->values();
        }

        // Jika filter mapel khusus agama dipilih, saring siswa yang berhak mengikuti mapel tersebut
        if ($selectedMapel && filled($selectedMapel->target_agama)) {
            $anggotaList = $anggotaList->filter(
                fn ($a) => $selectedMapel->isAccessibleBySiswa($a->siswa)
            )->values();
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
                ->select('id', 'judul', 'pengampu_kelas_id')
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

        // Hitung baris rekap per siswa (sebelum filter search agar peringkat & KPI tetap akurat)
        $rows = $anggotaList->map(function ($anggota) use (
            $pengampuByKelas,
            $tugasByPengampu,
            $quizByPengampu,
            $ujianByPengampu,
            $nilaiTugasBySiswa,
            $nilaiQuizBySiswa,
            $seleksiUjianBySiswa,
            $selectedMapel,
            $hitungKosong,
            $metodeHitung
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
            $mapelBelumTuntasList = [];

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
                    } elseif ($hitungKosong === 'nol') {
                        $pkTugasVals[] = 0.0;
                        $allTugasScores[] = 0.0;
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
                    } elseif ($hitungKosong === 'nol') {
                        $pkQuizVals[] = 0.0;
                        $allQuizScores[] = 0.0;
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
                    } elseif ($hitungKosong === 'nol') {
                        $pkUjianVals[] = 0.0;
                        $allUjianScores[] = 0.0;
                    }
                }
                $rataUjianMapel = count($pkUjianVals) > 0
                    ? round(array_sum($pkUjianVals) / count($pkUjianVals), 2)
                    : null;

                // Nilai akhir mapel berdasarkan metode hitung (rata-rata setara atau berbobot)
                $nilaiAkhirMapel = self::hitungNilaiAkhirKomponen(
                    $rataTugasMapel,
                    $rataQuizMapel,
                    $rataUjianMapel,
                    $metodeHitung
                );

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
                        $mapelBelumTuntasList[] = $mapel->nama_mapel;
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
                'mapel_belum_tuntas_list' => $mapelBelumTuntasList,
                'mapel_dinilai' => $mapelDinilaiCount,
                'mapel_wajib' => $totalMapelWajib,
                'mapel_scores' => $mapelScores,
                'peringkat' => null,
            ];
        });

        // Khusus Guru: hanya tampilkan siswa yang mengikuti minimal 1 mapel yang diampu guru
        if ($guruId !== null) {
            $rows = $rows->filter(fn ($r) => ($r['mapel_wajib'] ?? 0) > 0)->values();
        }

        // Urutkan berdasarkan nilai_akhir tertinggi (null di bawah), lalu nama A-Z, lalu beri peringkat (Standard Competition Ranking untuk nilai seri)
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

        $position = 0;
        $lastScore = null;
        $currentRank = 0;
        $rankedRows = $rankedRows->map(function ($row) use (&$position, &$lastScore, &$currentRank) {
            if ($row['nilai_akhir'] !== null) {
                $position++;
                if ($lastScore === null || abs($lastScore - $row['nilai_akhir']) > 0.001) {
                    $currentRank = $position;
                    $lastScore = $row['nilai_akhir'];
                }
                $row['peringkat'] = $currentRank;
            }

            return $row;
        });

        // KPI, distribusi predikat, rekap per kelas & per mapel dihitung pada scope periode/kelas/mapel (tidak terdistorsi oleh search/status)
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

        $distribusiPredikat = [
            'A' => $rankedRows->where('predikat', 'A')->count(),
            'B' => $rankedRows->where('predikat', 'B')->count(),
            'C' => $rankedRows->where('predikat', 'C')->count(),
            'D' => $rankedRows->where('predikat', 'D')->count(),
            '-' => $rankedRows->where('predikat', '-')->count(),
        ];

        $avgKkmScope = $selectedMapel
            ? (float) ($selectedMapel->kkm ?? 75)
            : ($rankedRows->isNotEmpty() ? round((float) $rankedRows->avg('kkm_acuan'), 2) : 75.0);
        $predikatThreshold = self::predikatThreshold($avgKkmScope);

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

        // Ringkasan performa per mata pelajaran (berguna ketika filter Semua Mata Pelajaran)
        $rekapPerMapel = collect();
        if (!$selectedMapel && $rankedRows->isNotEmpty()) {
            $mapelBucket = [];
            foreach ($rankedRows as $row) {
                foreach ($row['mapel_scores'] as $mId => $cell) {
                    if (!$cell['accessible']) {
                        continue;
                    }
                    if (!isset($mapelBucket[$mId])) {
                        $mapelBucket[$mId] = [
                            'mapel_id' => $mId,
                            'nama_mapel' => $cell['nama_mapel'],
                            'kkm' => $cell['kkm'],
                            'total_siswa' => 0,
                            'siswa_dinilai' => 0,
                            'jumlah_tuntas' => 0,
                            'scores' => [],
                        ];
                    }
                    $mapelBucket[$mId]['total_siswa']++;
                    if ($cell['nilai_akhir'] !== null) {
                        $mapelBucket[$mId]['siswa_dinilai']++;
                        $mapelBucket[$mId]['scores'][] = $cell['nilai_akhir'];
                        if ($cell['tuntas']) {
                            $mapelBucket[$mId]['jumlah_tuntas']++;
                        }
                    }
                }
            }

            $rekapPerMapel = collect($mapelBucket)
                ->map(function (array $item) {
                    $dinilai = $item['siswa_dinilai'];
                    $scores = $item['scores'];

                    return [
                        'mapel_id' => $item['mapel_id'],
                        'nama_mapel' => $item['nama_mapel'],
                        'kkm' => $item['kkm'],
                        'total_siswa' => $item['total_siswa'],
                        'siswa_dinilai' => $dinilai,
                        'jumlah_tuntas' => $item['jumlah_tuntas'],
                        'persen_tuntas' => $dinilai > 0 ? round(($item['jumlah_tuntas'] / $dinilai) * 100, 1) : 0,
                        'rata_nilai' => count($scores) > 0 ? round(array_sum($scores) / count($scores), 2) : null,
                    ];
                })
                ->sortBy(fn ($m) => strtolower((string) $m['nama_mapel']))
                ->values();
        }

        // Terapkan filter status & pencarian SETELAH ranking agar peringkat asli siswa tetap terjaga
        $filteredRows = $rankedRows;
        if ($selectedStatus !== 'semua') {
            $filteredRows = $filteredRows->where('status_ketuntasan', $selectedStatus)->values();
        }

        if ($search !== '') {
            $needle = mb_strtolower($search);
            $filteredRows = $filteredRows->filter(function ($r) use ($needle) {
                return str_contains(mb_strtolower((string) $r['nama_siswa']), $needle)
                    || str_contains(mb_strtolower((string) $r['nis']), $needle)
                    || str_contains(mb_strtolower((string) $r['nama_kelas']), $needle);
            })->values();
        }

        // Terapkan sorting fleksibel pada hasil tabel
        $filteredRows = $filteredRows->sort(function ($a, $b) use ($sortBy, $sortDir) {
            $dir = $sortDir === 'desc' ? -1 : 1;

            if (in_array($sortBy, ['nama_siswa', 'nis', 'nama_kelas'], true)) {
                $cmp = strcasecmp((string) $a[$sortBy], (string) $b[$sortBy]);
                if ($cmp !== 0) {
                    return $cmp * $dir;
                }

                return strcasecmp((string) $a['nama_siswa'], (string) $b['nama_siswa']);
            }

            $va = $a[$sortBy];
            $vb = $b[$sortBy];

            // Nilai null selalu ditaruh di bawah agar rapi
            if ($va === null && $vb === null) {
                return strcasecmp((string) $a['nama_siswa'], (string) $b['nama_siswa']);
            }
            if ($va === null) {
                return 1;
            }
            if ($vb === null) {
                return -1;
            }

            if (abs((float) $va - (float) $vb) > 0.001) {
                return ($va <=> $vb) * $dir;
            }

            return strcasecmp((string) $a['nama_siswa'], (string) $b['nama_siswa']);
        })->values();

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
            'hitungKosong' => $hitungKosong,
            'metodeHitung' => $metodeHitung,
            'sortBy' => $sortBy,
            'sortDir' => $sortDir,
            'legerMapels' => $legerMapels,
            'rows' => $filteredRows,
            'allRowsCount' => $totalSiswa,
            'summary' => $summary,
            'distribusiPredikat' => $distribusiPredikat,
            'predikatThreshold' => $predikatThreshold,
            'rekapPerKelas' => $rekapPerKelas,
            'rekapPerMapel' => $rekapPerMapel,
        ];
    }

    /**
     * Cek apakah seorang guru berhak melihat rekap detail seorang siswa
     * (siswa berada di kelas yang diampu oleh guru tersebut).
     */
    public function guruCanAccessSiswa(int $guruId, Siswa $siswa, ?int $academicPeriodId = null): bool
    {
        $pengampuQuery = PengampuKelas::whereHas('guruMapel', fn ($q) => $q->where('guru_id', $guruId));

        if ($academicPeriodId) {
            $kelasIdsInPeriod = (clone $pengampuQuery)
                ->where('academic_period_id', $academicPeriodId)
                ->pluck('kelas_id')
                ->unique()
                ->all();

            if (!empty($kelasIdsInPeriod)) {
                $existsInPeriod = AnggotaKelas::where('siswa_id', $siswa->id)
                    ->where('academic_period_id', $academicPeriodId)
                    ->whereIn('kelas_id', $kelasIdsInPeriod)
                    ->exists();

                if ($existsInPeriod) {
                    return true;
                }
            }
        }

        // Fallback: izinkan jika siswa berada di kelas yang pernah diampu guru pada periode mana pun
        // (berguna saat guru mengganti dropdown periode di halaman detail siswa).
        $allGuruPairs = $pengampuQuery->get(['kelas_id', 'academic_period_id']);
        if ($allGuruPairs->isEmpty()) {
            return false;
        }

        $anggotaPairs = AnggotaKelas::where('siswa_id', $siswa->id)
            ->get(['kelas_id', 'academic_period_id']);

        foreach ($anggotaPairs as $ap) {
            $matched = $allGuruPairs->contains(function ($gp) use ($ap) {
                if ((int) $gp->kelas_id !== (int) $ap->kelas_id) {
                    return false;
                }
                if ($gp->academic_period_id && $ap->academic_period_id) {
                    return (int) $gp->academic_period_id === (int) $ap->academic_period_id;
                }

                return true;
            });

            if ($matched) {
                return true;
            }
        }

        return false;
    }

    /**
     * Membangun transkrip detail nilai seorang siswa khusus untuk mapel yang diampu guru tertentu.
     */
    public function buildGuruSiswaDetail(
        int $guruId,
        Siswa $siswa,
        ?int $academicPeriodId = null,
        string $hitungKosong = 'abaikan',
        string $metodeHitung = 'rata'
    ): array {
        return $this->buildSiswaDetail($siswa, $academicPeriodId, $hitungKosong, $metodeHitung, $guruId);
    }

    /**
     * Membangun transkrip detail nilai seorang siswa pada periode akademik tertentu.
     */
    public function buildSiswaDetail(
        Siswa $siswa,
        ?int $academicPeriodId = null,
        string $hitungKosong = 'abaikan',
        string $metodeHitung = 'rata',
        ?int $guruId = null
    ): array {
        if (!in_array($hitungKosong, ['abaikan', 'nol'], true)) {
            $hitungKosong = 'abaikan';
        }
        if (!in_array($metodeHitung, ['rata', 'bobot'], true)) {
            $metodeHitung = 'rata';
        }

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
                ->when($guruId !== null, fn ($q) => $q->whereHas('guruMapel', fn ($gm) => $gm->where('guru_id', $guruId)))
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

            $detailMapels = $pengampus->map(function ($pk) use (
                $tugases,
                $quizzes,
                $ujians,
                $nilaiTugasMap,
                $nilaiQuizMap,
                $seleksiUjianMap,
                $hitungKosong,
                $metodeHitung
            ) {
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

                $tugasVals = $hitungKosong === 'nol'
                    ? $tugasItems->map(fn ($item) => $item['nilai'] ?? 0.0)
                    : $tugasItems->pluck('nilai')->filter(fn ($v) => $v !== null);

                $quizVals = $hitungKosong === 'nol'
                    ? $quizItems->map(fn ($item) => $item['nilai'] ?? 0.0)
                    : $quizItems->pluck('nilai')->filter(fn ($v) => $v !== null);

                $ujianVals = $hitungKosong === 'nol'
                    ? $ujianItems->map(fn ($item) => $item['nilai_akhir'] ?? 0.0)
                    : $ujianItems->pluck('nilai_akhir')->filter(fn ($v) => $v !== null);

                $rataTugas = $tugasVals->isNotEmpty() ? round($tugasVals->avg(), 2) : null;
                $rataKuis = $quizVals->isNotEmpty() ? round($quizVals->avg(), 2) : null;
                $rataUjian = $ujianVals->isNotEmpty() ? round($ujianVals->avg(), 2) : null;

                $nilaiAkhir = self::hitungNilaiAkhirKomponen($rataTugas, $rataKuis, $rataUjian, $metodeHitung);

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
            'hitungKosong' => $hitungKosong,
            'metodeHitung' => $metodeHitung,
            'predikatThreshold' => self::predikatThreshold($rataKkm),
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

    /**
     * Membangun data statistik nilai riil & dinamis untuk Dashboard Siswa (Web & API).
     */
    public function buildSiswaStatistik(?Siswa $siswa, ?int $kelasId = null, ?AcademicPeriod $period = null): array
    {
        $period = $period ?? AcademicPeriod::current();
        $periodId = $period?->id;

        if ($siswa && !$kelasId) {
            $anggota = AnggotaKelas::where('siswa_id', $siswa->id)
                ->when($periodId, fn ($q) => $q->where('academic_period_id', $periodId))
                ->first();
            $kelasId = $anggota?->kelas_id ?? $siswa->kelas?->id;
        }

        $pengampus = collect();
        if ($siswa && $kelasId) {
            $pengampus = PengampuKelas::with(['guruMapel.mapel', 'guruMapel.guru'])
                ->where('kelas_id', $kelasId)
                ->when($periodId, fn ($q) => $q->where('academic_period_id', $periodId))
                ->get()
                ->filter(fn ($p) => $p->guruMapel?->mapel && $p->guruMapel->mapel->isAccessibleBySiswa($siswa))
                ->sortBy(fn ($p) => strtolower((string) $p->guruMapel->mapel->nama_mapel))
                ->values();
        }

        $pengampuIds = $pengampus->pluck('id')->all();
        $siswaId = $siswa?->id;

        $tugases = collect();
        $quizzes = collect();
        $ujians = collect();
        $nilaiTugasList = collect();
        $nilaiQuizList = collect();
        $seleksiUjianList = collect();

        if ($siswaId && !empty($pengampuIds)) {
            $tugases = Tugas::whereIn('pengampu_kelas_id', $pengampuIds)
                ->select('id', 'judul', 'pengampu_kelas_id', 'created_at')
                ->get();

            $quizzes = Quiz::whereIn('pengampu_kelas_id', $pengampuIds)
                ->where('status', 'published')
                ->select('id', 'judul', 'pengampu_kelas_id', 'created_at')
                ->get();

            $ujians = Ujian::whereIn('pengampu_kelas_id', $pengampuIds)
                ->where(function ($q) {
                    $q->where('jenis_ujian', 'reguler')->orWhereNull('jenis_ujian');
                })
                ->whereIn('status', ['published', 'completed', 'unpublished'])
                ->select('id', 'judul', 'kkm', 'pengampu_kelas_id', 'waktu_mulai', 'created_at')
                ->get();

            $tugasIds = $tugases->pluck('id')->all();
            if (!empty($tugasIds)) {
                $nilaiTugasList = Nilai::where('siswa_id', $siswaId)
                    ->whereIn('tugas_id', $tugasIds)
                    ->whereNotNull('nilai')
                    ->select('id', 'siswa_id', 'tugas_id', 'nilai', 'status', 'created_at', 'updated_at')
                    ->get();
            }

            $quizIds = $quizzes->pluck('id')->all();
            if (!empty($quizIds)) {
                $nilaiQuizList = Nilai::where('siswa_id', $siswaId)
                    ->whereIn('quiz_id', $quizIds)
                    ->where('status', 'selesai')
                    ->whereNotNull('nilai')
                    ->select('id', 'siswa_id', 'quiz_id', 'nilai', 'status', 'created_at', 'updated_at')
                    ->get();
            }

            $ujianIds = $ujians->pluck('id')->all();
            if (!empty($ujianIds)) {
                $seleksiUjianList = SeleksiUjian::where('siswa_id', $siswaId)
                    ->whereIn('ujian_id', $ujianIds)
                    ->where(function ($q) {
                        $q->where('status', 'selesai')->orWhereNotNull('waktu_selesai');
                    })
                    ->whereNotNull('nilai_akhir')
                    ->select('id', 'siswa_id', 'ujian_id', 'nilai_awal', 'nilai_akhir', 'waktu_selesai', 'status', 'created_at', 'updated_at')
                    ->get();
            }
        }

        // Hitung rata-rata riil per komponen
        $avgTugasReal = $nilaiTugasList->isNotEmpty() ? round((float) $nilaiTugasList->avg('nilai'), 1) : null;
        $avgQuizReal = $nilaiQuizList->isNotEmpty() ? round((float) $nilaiQuizList->avg('nilai'), 1) : null;
        $avgUjianReal = $seleksiUjianList->isNotEmpty() ? round((float) $seleksiUjianList->avg('nilai_akhir'), 1) : null;

        $komponenAktif = array_values(array_filter(
            [$avgTugasReal, $avgQuizReal, $avgUjianReal],
            fn ($v) => $v !== null
        ));

        $hasRealData = count($komponenAktif) > 0;
        $rataRataTugas = $avgTugasReal ?? 0.0;
        $rataRataQuiz = $avgQuizReal ?? 0.0;
        $rataRataUjian = $avgUjianReal ?? 0.0;
        $rataRataSemua = $hasRealData
            ? round(array_sum($komponenAktif) / count($komponenAktif), 1)
            : 0.0;

        if (!$hasRealData) {
            $kategoriNilai = 'Belum Ada Data';
        } elseif ($rataRataSemua >= 90.0) {
            $kategoriNilai = 'Sangat Baik';
        } elseif ($rataRataSemua >= 80.0) {
            $kategoriNilai = 'Baik';
        } elseif ($rataRataSemua >= 70.0) {
            $kategoriNilai = 'Cukup';
        } else {
            $kategoriNilai = 'Perlu Bimbingan';
        }

        $donutDegree = $hasRealData
            ? (int) round((min(100.0, max(0.0, $rataRataSemua)) / 100.0) * 360)
            : 0;

        // Data nilai riil per Mata Pelajaran (gabungan Tugas, Kuis, Ujian)
        $tugasByPengampu = $tugases->groupBy('pengampu_kelas_id');
        $quizByPengampu = $quizzes->groupBy('pengampu_kelas_id');
        $ujianByPengampu = $ujians->groupBy('pengampu_kelas_id');

        $nilaiTugasMap = $nilaiTugasList->keyBy('tugas_id');
        $nilaiQuizMap = $nilaiQuizList->keyBy('quiz_id');
        $seleksiUjianMap = $seleksiUjianList->keyBy('ujian_id');

        $listNilaiMapel = [];
        foreach ($pengampus as $index => $pk) {
            $mapel = $pk->guruMapel->mapel;
            $kkm = (float) ($mapel->kkm ?? 75);

            $pkTugasVals = [];
            foreach (($tugasByPengampu->get($pk->id) ?? collect()) as $t) {
                $n = $nilaiTugasMap->get($t->id);
                if ($n && $n->nilai !== null) {
                    $pkTugasVals[] = (float) $n->nilai;
                }
            }
            $rataTugasMapel = count($pkTugasVals) > 0 ? round(array_sum($pkTugasVals) / count($pkTugasVals), 1) : null;

            $pkQuizVals = [];
            foreach (($quizByPengampu->get($pk->id) ?? collect()) as $qz) {
                $n = $nilaiQuizMap->get($qz->id);
                if ($n && $n->nilai !== null) {
                    $pkQuizVals[] = (float) $n->nilai;
                }
            }
            $rataQuizMapel = count($pkQuizVals) > 0 ? round(array_sum($pkQuizVals) / count($pkQuizVals), 1) : null;

            $pkUjianVals = [];
            foreach (($ujianByPengampu->get($pk->id) ?? collect()) as $uj) {
                $s = $seleksiUjianMap->get($uj->id);
                if ($s && $s->nilai_akhir !== null) {
                    $pkUjianVals[] = (float) $s->nilai_akhir;
                }
            }
            $rataUjianMapel = count($pkUjianVals) > 0 ? round(array_sum($pkUjianVals) / count($pkUjianVals), 1) : null;

            $mapelKomponen = array_values(array_filter(
                [$rataTugasMapel, $rataQuizMapel, $rataUjianMapel],
                fn ($v) => $v !== null
            ));
            $nilaiMapel = count($mapelKomponen) > 0
                ? round(array_sum($mapelKomponen) / count($mapelKomponen), 1)
                : null;

            $visual = $this->resolveMapelVisual((string) $mapel->nama_mapel, $index);

            $listNilaiMapel[] = [
                'id' => $mapel->id,
                'mapel_id' => $mapel->id,
                'nama' => $mapel->nama_mapel,
                'kode_mapel' => $mapel->kode_mapel ?? null,
                'kkm' => $kkm,
                'nilai' => $nilaiMapel,
                'has_nilai' => $nilaiMapel !== null,
                'tuntas' => $nilaiMapel !== null ? $nilaiMapel >= $kkm : null,
                'rata_tugas' => $rataTugasMapel,
                'rata_kuis' => $rataQuizMapel,
                'rata_ujian' => $rataUjianMapel,
                'color' => $visual['color'],
                'type' => $visual['type'],
            ];
        }

        // Perkembangan nilai bulanan riil (dinamis mengikuti periode akademik / data penilaian)
        $perkembanganNilai = $this->buildPerkembanganNilaiBulanan(
            $period,
            $nilaiTugasList,
            $nilaiQuizList,
            $seleksiUjianList
        );

        return [
            'has_real_data' => $hasRealData,
            'has_tugas_data' => $avgTugasReal !== null,
            'has_quiz_data' => $avgQuizReal !== null,
            'has_ujian_data' => $avgUjianReal !== null,
            'rata_rata_semua' => $rataRataSemua,
            'rata_rata_tugas' => $rataRataTugas,
            'rata_rata_quiz' => $rataRataQuiz,
            'rata_rata_ujian' => $rataRataUjian,
            'tugas_dinilai' => $nilaiTugasList->count(),
            'tugas_total' => $tugases->count(),
            'kuis_dinilai' => $nilaiQuizList->count(),
            'kuis_total' => $quizzes->count(),
            'ujian_dinilai' => $seleksiUjianList->count(),
            'ujian_total' => $ujians->count(),
            'kategori_nilai' => $kategoriNilai,
            'donut_degree' => $donutDegree,
            'list_nilai_mapel' => $listNilaiMapel,
            'perkembangan_nilai' => $perkembanganNilai,
        ];
    }

    /**
     * Tentukan warna & tipe ikon visual untuk kartu mata pelajaran.
     */
    private function resolveMapelVisual(string $namaMapel, int $index): array
    {
        $lower = mb_strtolower($namaMapel);

        if (str_contains($lower, 'matematika') || str_contains($lower, 'math')) {
            return ['color' => '#2563EB', 'type' => 'math'];
        }
        if (str_contains($lower, 'indonesia')) {
            return ['color' => '#EF4444', 'type' => 'book'];
        }
        if (str_contains($lower, 'inggris') || str_contains($lower, 'english')) {
            return ['color' => '#6366F1', 'type' => 'letters'];
        }
        if (str_contains($lower, 'ipa') || str_contains($lower, 'sains') || str_contains($lower, 'biologi') || str_contains($lower, 'fisika') || str_contains($lower, 'kimia')) {
            return ['color' => '#10B981', 'type' => 'beaker'];
        }
        if (str_contains($lower, 'ips') || str_contains($lower, 'sosial') || str_contains($lower, 'geografi') || str_contains($lower, 'sejarah')) {
            return ['color' => '#F59E0B', 'type' => 'globe'];
        }
        if (str_contains($lower, 'pkn') || str_contains($lower, 'ppkn') || str_contains($lower, 'kewarganegaraan')) {
            return ['color' => '#06B6D4', 'type' => 'group'];
        }

        $palette = ['#2563EB', '#EF4444', '#6366F1', '#10B981', '#F59E0B', '#06B6D4', '#8B5CF6', '#EC4899'];

        return [
            'color' => $palette[$index % count($palette)],
            'type' => 'default',
        ];
    }

    /**
     * Membangun seri grafik perkembangan nilai bulanan riil dari data Tugas, Kuis, dan Ujian.
     */
    private function buildPerkembanganNilaiBulanan(
        ?AcademicPeriod $period,
        Collection $nilaiTugasList,
        Collection $nilaiQuizList,
        Collection $seleksiUjianList
    ): array {
        $groupByMonth = function (Collection $items, callable $dateResolver, callable $scoreResolver): array {
            $buckets = [];
            foreach ($items as $item) {
                $dt = $dateResolver($item);
                if (!$dt) {
                    continue;
                }
                $key = Carbon::parse($dt)->format('Y-m');
                $buckets[$key][] = (float) $scoreResolver($item);
            }

            $averages = [];
            foreach ($buckets as $key => $scores) {
                $averages[$key] = [
                    'avg' => round(array_sum($scores) / count($scores), 1),
                    'count' => count($scores),
                ];
            }

            return $averages;
        };

        $tugasByMonth = $groupByMonth(
            $nilaiTugasList,
            fn ($n) => $n->updated_at ?? $n->created_at,
            fn ($n) => $n->nilai
        );

        $quizByMonth = $groupByMonth(
            $nilaiQuizList,
            fn ($n) => $n->updated_at ?? $n->created_at,
            fn ($n) => $n->nilai
        );

        $ujianByMonth = $groupByMonth(
            $seleksiUjianList,
            fn ($s) => $s->waktu_selesai ?? $s->updated_at ?? $s->created_at,
            fn ($s) => $s->nilai_akhir
        );

        $allDataMonths = array_unique(array_merge(
            array_keys($tugasByMonth),
            array_keys($quizByMonth),
            array_keys($ujianByMonth)
        ));
        sort($allDataMonths);

        $monthSlots = $this->resolveSemesterMonths($period, $allDataMonths);

        $slotCount = count($monthSlots);
        $xStart = 70;
        $xEnd = 490;
        $xCoords = [];
        for ($i = 0; $i < $slotCount; $i++) {
            $xCoords[] = $slotCount > 1
                ? (int) round($xStart + ($i * ($xEnd - $xStart) / ($slotCount - 1)))
                : (int) round(($xStart + $xEnd) / 2);
        }

        $shortNames = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];
        $fullNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $buildSeries = function (array $byMonth) use ($monthSlots, $xCoords, $fullNames): array {
            $coords = [];
            $pointsArr = [];
            foreach ($monthSlots as $idx => $ym) {
                if (!isset($byMonth[$ym])) {
                    continue;
                }
                $score = min(100.0, max(0.0, (float) $byMonth[$ym]['avg']));
                $x = $xCoords[$idx];
                // Y-axis SVG: 100 -> 20, 0 -> 195 (tinggi 175px)
                $y = round(195 - ($score / 100.0) * 175, 1);
                $monthNum = (int) substr($ym, 5, 2);
                $yearNum = substr($ym, 0, 4);

                $coords[] = [
                    'x' => $x,
                    'y' => $y,
                    'nilai' => $score,
                    'jumlah' => $byMonth[$ym]['count'],
                    'bulan' => ($fullNames[$monthNum] ?? $ym) . ' ' . $yearNum,
                    'index' => $idx,
                ];
                $pointsArr[] = "{$x},{$y}";
            }

            return [
                'points' => count($pointsArr) >= 2 ? implode(' ', $pointsArr) : '',
                'coords' => $coords,
            ];
        };

        $bulanLabels = [];
        $monthlySummary = [];
        foreach ($monthSlots as $idx => $ym) {
            $monthNum = (int) substr($ym, 5, 2);
            $yearNum = substr($ym, 0, 4);
            $short = $shortNames[$monthNum] ?? $ym;
            $full = ($fullNames[$monthNum] ?? $ym) . ' ' . $yearNum;

            $bulanLabels[] = $short;
            $monthlySummary[] = [
                'ym' => $ym,
                'label' => $short,
                'bulan_penuh' => $full,
                'x' => $xCoords[$idx],
                'tugas' => $tugasByMonth[$ym]['avg'] ?? null,
                'kuis' => $quizByMonth[$ym]['avg'] ?? null,
                'ujian' => $ujianByMonth[$ym]['avg'] ?? null,
            ];
        }

        $tugasSeries = $buildSeries($tugasByMonth);
        $kuizSeries = $buildSeries($quizByMonth);
        $ujianSeries = $buildSeries($ujianByMonth);

        $hasChartData = !empty($tugasSeries['coords']) || !empty($kuizSeries['coords']) || !empty($ujianSeries['coords']);

        return [
            'bulan' => $bulanLabels,
            'x_coords' => $xCoords,
            'tugas' => $tugasSeries,
            'kuiz' => $kuizSeries,
            'ujian' => $ujianSeries,
            'monthly_summary' => $monthlySummary,
            'has_chart_data' => $hasChartData,
        ];
    }

    /**
     * Menentukan 6 slot bulan (YYYY-MM) sesuai semester aktif atau rentang data riil siswa.
     */
    private function resolveSemesterMonths(?AcademicPeriod $period, array $allDataMonths): array
    {
        $slots = [];

        if ($period && $period->tanggal_mulai && $period->tanggal_selesai) {
            $cursor = $period->tanggal_mulai->copy()->startOfMonth();
            $end = $period->tanggal_selesai->copy()->startOfMonth();
            while ($cursor->lte($end) && count($slots) < 6) {
                $slots[] = $cursor->format('Y-m');
                $cursor->addMonth();
            }
        }

        if (empty($slots)) {
            $startYear = (int) now()->format('Y');
            if ($period && preg_match('/^(\d{4})/', (string) $period->tahun_ajaran, $m)) {
                $startYear = (int) $m[1];
            }

            $isGenap = $period && strtolower((string) $period->semester) === 'genap';
            $cursor = $isGenap
                ? Carbon::create($startYear + 1, 1, 1)
                : Carbon::create($startYear, 7, 1);

            for ($i = 0; $i < 6; $i++) {
                $slots[] = $cursor->copy()->addMonths($i)->format('Y-m');
            }
        }

        // Jika ada data riil namun seluruhnya berada di luar rentang default semester,
        // geser jendela 6 bulan hingga bulan data terbaru agar grafik tetap menampilkan data riil.
        if (!empty($allDataMonths) && empty(array_intersect($slots, $allDataMonths))) {
            $latestMonth = Carbon::createFromFormat('Y-m', end($allDataMonths))->startOfMonth();
            $slots = [];
            for ($i = 5; $i >= 0; $i--) {
                $slots[] = $latestMonth->copy()->subMonths($i)->format('Y-m');
            }
        }

        return $slots;
    }
}
