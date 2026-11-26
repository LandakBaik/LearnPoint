<?php

namespace App\Services;

use App\Models\AcademicPeriod;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Materi;
use App\Models\Menjawab;
use App\Models\Nilai;
use App\Models\PengampuKelas;
use App\Models\Quiz;
use App\Models\SeleksiUjian;
use App\Models\Soal;
use App\Models\Tugas;
use App\Models\Ujian;
use Carbon\Carbon;

class MonitoringGuruService
{
    /**
     * Hitung ambang batas kategori keaktifan relatif terhadap rata-rata keseluruhan guru (±25%).
     *
     * @return array{rata_rata: float, batas_bawah: float, batas_atas: float, min_sangat_aktif: int, max_kurang_aktif: int}
     */
    public static function hitungAmbangKeaktifan(float $rataRata): array
    {
        $rataRata = max(0.0, round($rataRata, 2));
        $batasBawah = round($rataRata * 0.75, 2);
        $batasAtas = round($rataRata * 1.25, 2);

        return [
            'rata_rata' => $rataRata,
            'batas_bawah' => $batasBawah,
            'batas_atas' => $batasAtas,
            'min_sangat_aktif' => (int) floor($batasAtas) + 1,
            'max_kurang_aktif' => max(0, (int) ceil($batasBawah) - 1),
        ];
    }

    /**
     * Tentukan kategori tingkat keaktifan guru berdasarkan perbandingan terhadap rata-rata keseluruhan guru:
     * - 0 aktivitas               => belum_aktif
     * - > rata-rata + 25%         => sangat_aktif (cukup jauh di atas rata-rata)
     * - < rata-rata - 25%         => kurang_aktif (cukup jauh di bawah rata-rata)
     * - di sekitar rata-rata ±25% => aktif
     */
    public static function hitungKategoriKeaktifan(int $totalAktivitas, float $rataRata): string
    {
        if ($totalAktivitas <= 0) {
            return 'belum_aktif';
        }
        if ($rataRata <= 0.0) {
            return 'aktif';
        }

        $batasBawah = round($rataRata * 0.75, 2);
        $batasAtas = round($rataRata * 1.25, 2);

        if ($totalAktivitas > $batasAtas) {
            return 'sangat_aktif';
        }
        if ($totalAktivitas < $batasBawah) {
            return 'kurang_aktif';
        }

        return 'aktif';
    }

    /**
     * Hitung rata-rata total aktivitas keseluruhan guru pada periode akademik tertentu.
     */
    protected function hitungRataRataAktivitasPeriode(?int $academicPeriodId): float
    {
        $totalGuru = Guru::count();
        if ($totalGuru <= 0) {
            return 0.0;
        }

        $pengampuIds = PengampuKelas::query()
            ->when($academicPeriodId, fn ($q) => $q->where('academic_period_id', $academicPeriodId))
            ->whereHas('guruMapel', fn ($q) => $q->whereHas('guru')->whereHas('mapel'))
            ->whereHas('kelas')
            ->pluck('id')
            ->all();

        if (empty($pengampuIds)) {
            return 0.0;
        }

        $totalMateri = Materi::whereIn('pengampu_kelas_id', $pengampuIds)->count();
        $totalTugas = Tugas::whereIn('pengampu_kelas_id', $pengampuIds)->count();
        $totalKuisPub = Quiz::whereIn('pengampu_kelas_id', $pengampuIds)
            ->where('status', 'published')
            ->count();
        $totalUjianAktif = Ujian::whereIn('pengampu_kelas_id', $pengampuIds)
            ->whereIn('status', ['published', 'completed', 'scheduled'])
            ->count();

        $totalAktivitas = $totalMateri + $totalTugas + $totalKuisPub + $totalUjianAktif;

        return round($totalAktivitas / $totalGuru, 2);
    }

    /**
     * Membangun data perekapan & monitoring kinerja/aktivitas seluruh guru untuk Admin.
     *
     * @param  array{
     *     academic_period_id?: int|string|null,
     *     mapel_id?: int|string|null,
     *     kelas_id?: int|string|null,
     *     keaktifan?: string|null,
     *     search?: string|null,
     * }  $filters
     */
    public function buildAdminMonitoring(array $filters = []): array
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

        $kelasList = Kelas::orderBy('tingkatan')->orderBy('nama_kelas')->get();
        $mapelList = Mapel::orderBy('nama_mapel')->get();

        $selectedKelasId = !empty($filters['kelas_id']) ? (int) $filters['kelas_id'] : null;
        $selectedKelas = $selectedKelasId ? $kelasList->firstWhere('id', $selectedKelasId) : null;

        $selectedMapelId = !empty($filters['mapel_id']) ? (int) $filters['mapel_id'] : null;
        $selectedMapel = $selectedMapelId ? $mapelList->firstWhere('id', $selectedMapelId) : null;

        $selectedKeaktifan = $filters['keaktifan'] ?? 'semua';
        if (!in_array($selectedKeaktifan, ['semua', 'sangat_aktif', 'aktif', 'kurang_aktif', 'belum_aktif'], true)) {
            $selectedKeaktifan = 'semua';
        }

        $search = trim((string) ($filters['search'] ?? ''));

        // Ambil seluruh pengampu kelas sesuai filter periode, kelas, mapel
        $pengampuQuery = PengampuKelas::with(['guruMapel.guru.user', 'guruMapel.guru.kelas', 'guruMapel.mapel', 'kelas'])
            ->when($selectedPeriodId, fn ($q) => $q->where('academic_period_id', $selectedPeriodId))
            ->when($selectedKelasId, fn ($q) => $q->where('kelas_id', $selectedKelasId))
            ->when($selectedMapelId, function ($q) use ($selectedMapelId) {
                $q->whereHas('guruMapel', fn ($gm) => $gm->where('mapel_id', $selectedMapelId));
            });

        $pengampus = $pengampuQuery->get()
            ->filter(fn ($p) => $p->guruMapel?->guru && $p->guruMapel?->mapel && $p->kelas)
            ->values();

        // Ambil daftar guru: jika tidak ada filter kelas/mapel, tampilkan semua guru terdaftar
        // agar Admin juga melihat guru yang belum memiliki jadwal pengampu di periode ini.
        if ($selectedKelasId || $selectedMapelId) {
            $guruIds = $pengampus->map(fn ($p) => $p->guruMapel->guru_id)->unique()->values()->all();
            $gurus = Guru::with(['user', 'kelas'])
                ->whereIn('id', $guruIds)
                ->orderBy('nama')
                ->get();
        } else {
            $gurus = Guru::with(['user', 'kelas'])
                ->orderBy('nama')
                ->get();
        }

        $pengampuByGuru = $pengampus->groupBy(fn ($p) => $p->guruMapel->guru_id);
        $pengampuIds = $pengampus->pluck('id')->all();

        // Batch query semua aktivitas pembelajaran sekaligus (hindari N+1)
        $materiByPengampu = collect();
        $tugasByPengampu = collect();
        $quizByPengampu = collect();
        $ujianByPengampu = collect();
        $nilaiTugasStatsByTugas = collect();
        $essayMenungguByUjian = collect();

        if (!empty($pengampuIds)) {
            $materis = Materi::whereIn('pengampu_kelas_id', $pengampuIds)
                ->select('id', 'pengampu_kelas_id', 'file_materi', 'url_youtube', 'created_at', 'updated_at')
                ->get();
            $materiByPengampu = $materis->groupBy('pengampu_kelas_id');

            $tugases = Tugas::whereIn('pengampu_kelas_id', $pengampuIds)
                ->select('id', 'pengampu_kelas_id', 'created_at', 'updated_at')
                ->get();
            $tugasByPengampu = $tugases->groupBy('pengampu_kelas_id');

            $quizzes = Quiz::whereIn('pengampu_kelas_id', $pengampuIds)
                ->select('id', 'pengampu_kelas_id', 'status', 'created_at', 'updated_at')
                ->get();
            $quizByPengampu = $quizzes->groupBy('pengampu_kelas_id');

            $ujians = Ujian::whereIn('pengampu_kelas_id', $pengampuIds)
                ->select('id', 'pengampu_kelas_id', 'status', 'jenis_ujian', 'created_at', 'updated_at')
                ->get();
            $ujianByPengampu = $ujians->groupBy('pengampu_kelas_id');

            // Statistik penilaian tugas (berapa pengumpulan sudah dinilai vs menunggu)
            $tugasIds = $tugases->pluck('id')->all();
            if (!empty($tugasIds)) {
                $nilaiTugasStatsByTugas = Nilai::whereIn('tugas_id', $tugasIds)
                    ->selectRaw('tugas_id, COUNT(*) as total_pengumpulan, SUM(CASE WHEN nilai IS NOT NULL THEN 1 ELSE 0 END) as sudah_dinilai, SUM(CASE WHEN nilai IS NULL THEN 1 ELSE 0 END) as menunggu_dinilai')
                    ->groupBy('tugas_id')
                    ->get()
                    ->keyBy('tugas_id');
            }

            // Cek soal essay ujian yang sudah disubmit siswa tapi belum dikoreksi guru
            $ujianIds = $ujians->pluck('id')->all();
            if (!empty($ujianIds)) {
                $essaySoals = Soal::whereIn('ujian_id', $ujianIds)
                    ->where('tipe_soal', 'essay')
                    ->select('id', 'ujian_id')
                    ->get();

                if ($essaySoals->isNotEmpty()) {
                    $soalToUjian = $essaySoals->pluck('ujian_id', 'id');
                    $essaySoalIds = $essaySoals->pluck('id')->all();

                    $selesaiPeserta = SeleksiUjian::whereIn('ujian_id', $ujianIds)
                        ->where(function ($q) {
                            $q->where('status', 'selesai')->orWhereNotNull('waktu_selesai');
                        })
                        ->select('ujian_id', 'siswa_id')
                        ->get();

                    $selesaiLookup = [];
                    foreach ($selesaiPeserta as $sp) {
                        $selesaiLookup[$sp->ujian_id . '-' . $sp->siswa_id] = true;
                    }

                    $unscoredEssays = Menjawab::whereIn('soal_id', $essaySoalIds)
                        ->whereNull('skor_essay')
                        ->select('soal_id', 'siswa_id')
                        ->get();

                    $counts = [];
                    foreach ($unscoredEssays as $ue) {
                        $uId = $soalToUjian[$ue->soal_id] ?? null;
                        if ($uId && isset($selesaiLookup[$uId . '-' . $ue->siswa_id])) {
                            $counts[$uId] = ($counts[$uId] ?? 0) + 1;
                        }
                    }
                    $essayMenungguByUjian = collect($counts);
                }
            }
        }

        // Bangun baris monitoring per guru
        $rows = $gurus->map(function ($guru) use (
            $pengampuByGuru,
            $materiByPengampu,
            $tugasByPengampu,
            $quizByPengampu,
            $ujianByPengampu,
            $nilaiTugasStatsByTugas,
            $essayMenungguByUjian
        ) {
            $guruPengampus = $pengampuByGuru->get($guru->id) ?? collect();

            $mapelNames = $guruPengampus
                ->map(fn ($p) => $p->guruMapel->mapel->nama_mapel)
                ->unique()
                ->sort()
                ->values();

            $kelasNames = $guruPengampus
                ->map(fn ($p) => $p->kelas->nama_kelas)
                ->unique()
                ->sort()
                ->values();

            $jumlahMateri = 0;
            $materiFile = 0;
            $materiYoutube = 0;

            $jumlahTugas = 0;
            $totalPengumpulanTugas = 0;
            $tugasSudahDinilai = 0;
            $tugasMenungguDinilai = 0;

            $jumlahKuisTotal = 0;
            $jumlahKuisPublished = 0;
            $jumlahKuisDraft = 0;

            $jumlahUjianTotal = 0;
            $jumlahUjianReguler = 0;
            $jumlahUjianRemidiSusulan = 0;
            $jumlahUjianAktifSelesai = 0;
            $jumlahUjianDraft = 0;
            $essayMenungguKoreksi = 0;

            $latestActivityAt = null;
            $trackLatest = function ($dt) use (&$latestActivityAt) {
                if (!$dt) {
                    return;
                }
                $c = $dt instanceof Carbon ? $dt : Carbon::parse($dt);
                if ($latestActivityAt === null || $c->greaterThan($latestActivityAt)) {
                    $latestActivityAt = $c;
                }
            };

            foreach ($guruPengampus as $pk) {
                // Materi
                $pkMateris = $materiByPengampu->get($pk->id) ?? collect();
                $jumlahMateri += $pkMateris->count();
                foreach ($pkMateris as $m) {
                    if (filled($m->file_materi)) {
                        $materiFile++;
                    }
                    if (filled($m->url_youtube)) {
                        $materiYoutube++;
                    }
                    $trackLatest($m->updated_at ?? $m->created_at);
                }

                // Tugas & progres penilaian
                $pkTugases = $tugasByPengampu->get($pk->id) ?? collect();
                $jumlahTugas += $pkTugases->count();
                foreach ($pkTugases as $t) {
                    $stat = $nilaiTugasStatsByTugas->get($t->id);
                    if ($stat) {
                        $totalPengumpulanTugas += (int) $stat->total_pengumpulan;
                        $tugasSudahDinilai += (int) $stat->sudah_dinilai;
                        $tugasMenungguDinilai += (int) $stat->menunggu_dinilai;
                    }
                    $trackLatest($t->updated_at ?? $t->created_at);
                }

                // Kuis
                $pkQuizzes = $quizByPengampu->get($pk->id) ?? collect();
                $jumlahKuisTotal += $pkQuizzes->count();
                foreach ($pkQuizzes as $qz) {
                    if (($qz->status ?? 'draft') === 'published') {
                        $jumlahKuisPublished++;
                    } else {
                        $jumlahKuisDraft++;
                    }
                    $trackLatest($qz->updated_at ?? $qz->created_at);
                }

                // Ujian
                $pkUjians = $ujianByPengampu->get($pk->id) ?? collect();
                $jumlahUjianTotal += $pkUjians->count();
                foreach ($pkUjians as $uj) {
                    $jenis = $uj->jenis_ujian ?? 'reguler';
                    if ($jenis === 'reguler') {
                        $jumlahUjianReguler++;
                    } else {
                        $jumlahUjianRemidiSusulan++;
                    }

                    if (in_array($uj->status, ['published', 'completed', 'scheduled'], true)) {
                        $jumlahUjianAktifSelesai++;
                    } else {
                        $jumlahUjianDraft++;
                    }

                    $essayMenungguKoreksi += (int) ($essayMenungguByUjian->get($uj->id) ?? 0);
                    $trackLatest($uj->updated_at ?? $uj->created_at);
                }
            }

            $totalAktivitas = $jumlahMateri + $jumlahTugas + $jumlahKuisPublished + $jumlahUjianAktifSelesai;

            $persenKoreksiTugas = $totalPengumpulanTugas > 0
                ? round(($tugasSudahDinilai / $totalPengumpulanTugas) * 100, 1)
                : null;

            return [
                'guru_id' => $guru->id,
                'nama_guru' => $guru->nama ?? $guru->user?->name ?? ('Guru #' . $guru->id),
                'nuptk' => $guru->nuptk ?? '-',
                'email' => $guru->user?->email ?? '-',
                'status_akun' => $guru->status ?? 'aktif',
                'wali_kelas' => $guru->kelas?->nama_kelas,
                'jumlah_kelas' => $kelasNames->count(),
                'kelas_list' => $kelasNames->all(),
                'mapel_list' => $mapelNames->all(),
                'jumlah_pengampu' => $guruPengampus->count(),
                'jumlah_materi' => $jumlahMateri,
                'materi_file' => $materiFile,
                'materi_youtube' => $materiYoutube,
                'jumlah_tugas' => $jumlahTugas,
                'total_pengumpulan_tugas' => $totalPengumpulanTugas,
                'tugas_sudah_dinilai' => $tugasSudahDinilai,
                'tugas_menunggu_dinilai' => $tugasMenungguDinilai,
                'persen_koreksi_tugas' => $persenKoreksiTugas,
                'jumlah_kuis_total' => $jumlahKuisTotal,
                'jumlah_kuis_published' => $jumlahKuisPublished,
                'jumlah_kuis_draft' => $jumlahKuisDraft,
                'jumlah_ujian_total' => $jumlahUjianTotal,
                'jumlah_ujian_reguler' => $jumlahUjianReguler,
                'jumlah_ujian_remidi_susulan' => $jumlahUjianRemidiSusulan,
                'jumlah_ujian_aktif' => $jumlahUjianAktifSelesai,
                'jumlah_ujian_draft' => $jumlahUjianDraft,
                'essay_menunggu_koreksi' => $essayMenungguKoreksi,
                'total_aktivitas' => $totalAktivitas,
                'kategori_keaktifan' => 'belum_aktif',
                'aktivitas_terakhir' => $latestActivityAt,
            ];
        })->sortByDesc('total_aktivitas')->values();

        // Hitung rata-rata aktivitas keseluruhan guru, lalu tetapkan kategori keaktifan relatif (±25% dari rata-rata)
        $rataRataAktivitas = $rows->isNotEmpty()
            ? round((float) $rows->avg('total_aktivitas'), 2)
            : 0.0;
        $ambangKeaktifan = self::hitungAmbangKeaktifan($rataRataAktivitas);

        $rows = $rows->map(function (array $row) use ($rataRataAktivitas) {
            $row['kategori_keaktifan'] = self::hitungKategoriKeaktifan(
                $row['total_aktivitas'],
                $rataRataAktivitas
            );

            return $row;
        });

        // Terapkan filter kategori keaktifan & pencarian SETELAH kategori dihitung agar rata-rata tetap akurat
        $filteredRows = $rows;
        if ($selectedKeaktifan !== 'semua') {
            $filteredRows = $filteredRows->where('kategori_keaktifan', $selectedKeaktifan)->values();
        }
        if ($search !== '') {
            $needle = mb_strtolower($search);
            $filteredRows = $filteredRows->filter(function (array $r) use ($needle) {
                return str_contains(mb_strtolower((string) $r['nama_guru']), $needle)
                    || str_contains(mb_strtolower((string) $r['nuptk']), $needle);
            })->values();
        }

        // KPI Summary
        $totalGuru = $rows->count();
        $guruAktif = $rows->whereIn('kategori_keaktifan', ['sangat_aktif', 'aktif'])->count();
        $guruKurangAktif = $rows->where('kategori_keaktifan', 'kurang_aktif')->count();
        $guruBelumAktif = $rows->where('kategori_keaktifan', 'belum_aktif')->count();

        $totalPengumpulanAll = $rows->sum('total_pengumpulan_tugas');
        $totalSudahDinilaiAll = $rows->sum('tugas_sudah_dinilai');
        $totalMenungguDinilaiAll = $rows->sum('tugas_menunggu_dinilai');

        $summary = [
            'total_guru' => $totalGuru,
            'guru_sangat_aktif' => $rows->where('kategori_keaktifan', 'sangat_aktif')->count(),
            'guru_aktif' => $guruAktif,
            'guru_kurang_aktif' => $guruKurangAktif,
            'guru_belum_aktif' => $guruBelumAktif,
            'rata_rata_aktivitas' => $rataRataAktivitas,
            'total_materi' => $rows->sum('jumlah_materi'),
            'total_tugas' => $rows->sum('jumlah_tugas'),
            'total_kuis_published' => $rows->sum('jumlah_kuis_published'),
            'total_kuis_all' => $rows->sum('jumlah_kuis_total'),
            'total_ujian_aktif' => $rows->sum('jumlah_ujian_aktif'),
            'total_ujian_all' => $rows->sum('jumlah_ujian_total'),
            'total_aktivitas' => $rows->sum('total_aktivitas'),
            'total_pengumpulan_tugas' => $totalPengumpulanAll,
            'tugas_sudah_dinilai' => $totalSudahDinilaiAll,
            'tugas_menunggu_dinilai' => $totalMenungguDinilaiAll,
            'persen_koreksi_tugas' => $totalPengumpulanAll > 0
                ? round(($totalSudahDinilaiAll / $totalPengumpulanAll) * 100, 1)
                : 100,
            'essay_menunggu_koreksi' => $rows->sum('essay_menunggu_koreksi'),
        ];

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
            'selectedKeaktifan' => $selectedKeaktifan,
            'search' => $search,
            'rows' => $filteredRows,
            'allRowsCount' => $totalGuru,
            'summary' => $summary,
            'ambangKeaktifan' => $ambangKeaktifan,
        ];
    }

    /**
     * Membangun laporan detail aktivitas mengajar untuk satu guru tertentu pada periode akademik.
     */
    public function buildGuruDetail(Guru $guru, ?int $academicPeriodId = null): array
    {
        $periods = AcademicPeriod::orderByDesc('tahun_ajaran')
            ->orderByRaw("CASE WHEN semester = 'genap' THEN 2 ELSE 1 END DESC")
            ->get();

        $activePeriod = AcademicPeriod::current();
        $selectedPeriodId = $academicPeriodId ?: ($activePeriod->id ?? $periods->first()?->id);
        $selectedPeriod = $periods->firstWhere('id', $selectedPeriodId) ?? $activePeriod;
        $selectedPeriodId = $selectedPeriod?->id;

        $guru->loadMissing(['user', 'kelas']);

        $pengampus = PengampuKelas::with(['guruMapel.mapel', 'kelas'])
            ->whereHas('guruMapel', fn ($q) => $q->where('guru_id', $guru->id))
            ->when($selectedPeriodId, fn ($q) => $q->where('academic_period_id', $selectedPeriodId))
            ->get()
            ->filter(fn ($p) => $p->guruMapel?->mapel && $p->kelas)
            ->sortBy(fn ($p) => strtolower($p->kelas->nama_kelas . '-' . $p->guruMapel->mapel->nama_mapel))
            ->values();

        $pengampuIds = $pengampus->pluck('id')->all();

        $materis = !empty($pengampuIds)
            ? Materi::whereIn('pengampu_kelas_id', $pengampuIds)->orderByDesc('created_at')->get()->groupBy('pengampu_kelas_id')
            : collect();

        $tugases = !empty($pengampuIds)
            ? Tugas::whereIn('pengampu_kelas_id', $pengampuIds)->orderByDesc('created_at')->get()->groupBy('pengampu_kelas_id')
            : collect();

        $quizzes = !empty($pengampuIds)
            ? Quiz::whereIn('pengampu_kelas_id', $pengampuIds)->orderByDesc('created_at')->get()->groupBy('pengampu_kelas_id')
            : collect();

        $ujians = !empty($pengampuIds)
            ? Ujian::whereIn('pengampu_kelas_id', $pengampuIds)->orderByDesc('created_at')->get()->groupBy('pengampu_kelas_id')
            : collect();

        $allTugasIds = $tugases->flatten()->pluck('id')->all();
        $nilaiTugasStats = !empty($allTugasIds)
            ? Nilai::whereIn('tugas_id', $allTugasIds)
                ->selectRaw('tugas_id, COUNT(*) as total_pengumpulan, SUM(CASE WHEN nilai IS NOT NULL THEN 1 ELSE 0 END) as sudah_dinilai, SUM(CASE WHEN nilai IS NULL THEN 1 ELSE 0 END) as menunggu_dinilai')
                ->groupBy('tugas_id')
                ->get()
                ->keyBy('tugas_id')
            : collect();

        $allUjianIds = $ujians->flatten()->pluck('id')->all();
        $pesertaUjianStats = !empty($allUjianIds)
            ? SeleksiUjian::whereIn('ujian_id', $allUjianIds)
                ->selectRaw('ujian_id, COUNT(*) as total_peserta, SUM(CASE WHEN status = "selesai" OR waktu_selesai IS NOT NULL THEN 1 ELSE 0 END) as selesai')
                ->groupBy('ujian_id')
                ->get()
                ->keyBy('ujian_id')
            : collect();

        $allQuizIds = $quizzes->flatten()->pluck('id')->all();
        $pesertaQuizStats = !empty($allQuizIds)
            ? Nilai::whereIn('quiz_id', $allQuizIds)
                ->where('status', 'selesai')
                ->selectRaw('quiz_id, COUNT(*) as selesai')
                ->groupBy('quiz_id')
                ->get()
                ->keyBy('quiz_id')
            : collect();

        $detailPengampus = $pengampus->map(function ($pk) use (
            $materis,
            $tugases,
            $quizzes,
            $ujians,
            $nilaiTugasStats,
            $pesertaUjianStats,
            $pesertaQuizStats
        ) {
            $pkMateris = ($materis->get($pk->id) ?? collect())->map(fn ($m) => [
                'id' => $m->id,
                'judul' => $m->judul,
                'has_file' => filled($m->file_materi),
                'has_youtube' => filled($m->url_youtube),
                'created_at' => $m->created_at,
            ])->values();

            $pkTugases = ($tugases->get($pk->id) ?? collect())->map(function ($t) use ($nilaiTugasStats) {
                $st = $nilaiTugasStats->get($t->id);

                return [
                    'id' => $t->id,
                    'judul' => $t->judul,
                    'deadline' => $t->deadline,
                    'total_pengumpulan' => (int) ($st->total_pengumpulan ?? 0),
                    'sudah_dinilai' => (int) ($st->sudah_dinilai ?? 0),
                    'menunggu_dinilai' => (int) ($st->menunggu_dinilai ?? 0),
                    'created_at' => $t->created_at,
                ];
            })->values();

            $pkQuizzes = ($quizzes->get($pk->id) ?? collect())->map(function ($qz) use ($pesertaQuizStats) {
                $st = $pesertaQuizStats->get($qz->id);

                return [
                    'id' => $qz->id,
                    'judul' => $qz->judul,
                    'status' => $qz->status ?? 'draft',
                    'peserta_selesai' => (int) ($st->selesai ?? 0),
                    'created_at' => $qz->created_at,
                ];
            })->values();

            $pkUjians = ($ujians->get($pk->id) ?? collect())->map(function ($uj) use ($pesertaUjianStats) {
                $st = $pesertaUjianStats->get($uj->id);

                return [
                    'id' => $uj->id,
                    'judul' => $uj->judul,
                    'jenis_ujian' => $uj->jenis_ujian ?? 'reguler',
                    'status' => $uj->status ?? 'draft',
                    'waktu_mulai' => $uj->waktu_mulai,
                    'total_peserta' => (int) ($st->total_peserta ?? 0),
                    'peserta_selesai' => (int) ($st->selesai ?? 0),
                    'created_at' => $uj->created_at,
                ];
            })->values();

            $menungguTugas = $pkTugases->sum('menunggu_dinilai');
            $totalPengumpulan = $pkTugases->sum('total_pengumpulan');

            return [
                'pengampu_kelas_id' => $pk->id,
                'mapel_id' => $pk->guruMapel->mapel_id,
                'nama_kelas' => $pk->kelas->nama_kelas,
                'nama_mapel' => $pk->guruMapel->mapel->nama_mapel,
                'kkm' => (float) ($pk->guruMapel->mapel->kkm ?? 75),
                'jumlah_materi' => $pkMateris->count(),
                'jumlah_tugas' => $pkTugases->count(),
                'jumlah_kuis' => $pkQuizzes->count(),
                'jumlah_kuis_published' => $pkQuizzes->where('status', 'published')->count(),
                'jumlah_ujian' => $pkUjians->count(),
                'jumlah_ujian_aktif' => $pkUjians->whereIn('status', ['published', 'completed', 'scheduled'])->count(),
                'total_pengumpulan_tugas' => $totalPengumpulan,
                'tugas_menunggu_dinilai' => $menungguTugas,
                'materi_items' => $pkMateris,
                'tugas_items' => $pkTugases,
                'quiz_items' => $pkQuizzes,
                'ujian_items' => $pkUjians,
            ];
        });

        // Kelompokkan per Mata Pelajaran agar tampilan Detail Monitoring Guru ringkas dan tidak duplikat per kelas
        $detailMapels = $detailPengampus
            ->groupBy('mapel_id')
            ->map(function ($group) {
                $first = $group->first();
                $kelasList = $group->pluck('nama_kelas')->unique()->sort()->values();

                $materiItems = $group->flatMap(function ($dp) {
                    return $dp['materi_items']->map(fn ($item) => array_merge($item, ['nama_kelas' => $dp['nama_kelas']]));
                })->sortByDesc('created_at')->values();

                $tugasItems = $group->flatMap(function ($dp) {
                    return $dp['tugas_items']->map(fn ($item) => array_merge($item, ['nama_kelas' => $dp['nama_kelas']]));
                })->sortByDesc('created_at')->values();

                $quizItems = $group->flatMap(function ($dp) {
                    return $dp['quiz_items']->map(fn ($item) => array_merge($item, ['nama_kelas' => $dp['nama_kelas']]));
                })->sortByDesc('created_at')->values();

                $ujianItems = $group->flatMap(function ($dp) {
                    return $dp['ujian_items']->map(fn ($item) => array_merge($item, ['nama_kelas' => $dp['nama_kelas']]));
                })->sortByDesc('created_at')->values();

                return [
                    'mapel_id' => $first['mapel_id'],
                    'nama_mapel' => $first['nama_mapel'],
                    'kkm' => $first['kkm'],
                    'kelas_list' => $kelasList,
                    'jumlah_kelas' => $kelasList->count(),
                    'jumlah_materi' => $group->sum('jumlah_materi'),
                    'jumlah_tugas' => $group->sum('jumlah_tugas'),
                    'jumlah_kuis' => $group->sum('jumlah_kuis'),
                    'jumlah_kuis_published' => $group->sum('jumlah_kuis_published'),
                    'jumlah_ujian' => $group->sum('jumlah_ujian'),
                    'jumlah_ujian_aktif' => $group->sum('jumlah_ujian_aktif'),
                    'total_pengumpulan_tugas' => $group->sum('total_pengumpulan_tugas'),
                    'tugas_menunggu_dinilai' => $group->sum('tugas_menunggu_dinilai'),
                    'materi_items' => $materiItems,
                    'tugas_items' => $tugasItems,
                    'quiz_items' => $quizItems,
                    'ujian_items' => $ujianItems,
                ];
            })
            ->sortBy(fn ($m) => strtolower((string) $m['nama_mapel']))
            ->values();

        $totalMateri = $detailPengampus->sum('jumlah_materi');
        $totalTugas = $detailPengampus->sum('jumlah_tugas');
        $totalKuisPub = $detailPengampus->sum('jumlah_kuis_published');
        $totalUjianAktif = $detailPengampus->sum('jumlah_ujian_aktif');
        $totalAktivitas = $totalMateri + $totalTugas + $totalKuisPub + $totalUjianAktif;
        $rataRataAktivitas = $this->hitungRataRataAktivitasPeriode($selectedPeriodId);
        $ambangKeaktifan = self::hitungAmbangKeaktifan($rataRataAktivitas);

        return [
            'guru' => $guru,
            'periods' => $periods,
            'selectedPeriod' => $selectedPeriod,
            'selectedPeriodId' => $selectedPeriodId,
            'detailPengampus' => $detailPengampus,
            'detailMapels' => $detailMapels,
            'ambangKeaktifan' => $ambangKeaktifan,
            'summary' => [
                'total_mapel' => $detailMapels->count(),
                'total_kelas_mapel' => $detailPengampus->count(),
                'total_materi' => $totalMateri,
                'total_tugas' => $totalTugas,
                'total_kuis' => $detailPengampus->sum('jumlah_kuis'),
                'total_kuis_published' => $totalKuisPub,
                'total_ujian' => $detailPengampus->sum('jumlah_ujian'),
                'total_ujian_aktif' => $totalUjianAktif,
                'total_pengumpulan_tugas' => $detailPengampus->sum('total_pengumpulan_tugas'),
                'tugas_menunggu_dinilai' => $detailPengampus->sum('tugas_menunggu_dinilai'),
                'total_aktivitas' => $totalAktivitas,
                'rata_rata_aktivitas' => $rataRataAktivitas,
                'kategori_keaktifan' => self::hitungKategoriKeaktifan($totalAktivitas, $rataRataAktivitas),
            ],
        ];
    }
}
