<?php

namespace App\Services;

use App\Models\Mapel;
use App\Models\Menjawab;
use App\Models\Nilai;
use App\Models\Quiz;
use App\Models\Siswa;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class QuizAdventureService
{
    public const ARCS_ORDER = ['Mudah', 'Sedang', 'Sulit'];

    public const ARC_META = [
        'Mudah' => [
            'nama' => 'Arc Mudah',
            'label' => 'Mudah',
            'dots' => 1,
            'badge_bg' => '#ecfdf5',
            'badge_text' => '#059669',
            'badge_border' => '#a7f3d0',
        ],
        'Sedang' => [
            'nama' => 'Arc Sedang',
            'label' => 'Sedang',
            'dots' => 2,
            'badge_bg' => '#fffbeb',
            'badge_text' => '#d97706',
            'badge_border' => '#fde68a',
        ],
        'Sulit' => [
            'nama' => 'Arc Sulit',
            'label' => 'Sulit',
            'dots' => 3,
            'badge_bg' => '#fef2f2',
            'badge_text' => '#dc2626',
            'badge_border' => '#fecaca',
        ],
    ];

    public const PLANET_PRESETS = [
        'matematika' => [
            'nama' => 'Matematika',
            'deskripsi' => 'Planet yang penuh dengan angka',
            'simbol' => 'Σ',
            'bg_color' => 'bg-[#EEF4FF]',
            'text_color' => 'text-[#2563EB]',
            'border_color' => 'border-blue-100',
            'tips' => 'Manfaatkan tripel Pythagoras 3:4:5 dan 5:12:13 untuk menghitung lebih cepat.',
            'lore' => 'Eksplorasi sektor numerik dan selesaikan setiap pos tantangan matematika.',
        ],
        'ipa' => [
            'nama' => 'Ilmu Pengetahuan Alam',
            'deskripsi' => 'Planet yang penuh dengan makluk hidup',
            'simbol' => 'Δ',
            'bg_color' => 'bg-[#E8F8F0]',
            'text_color' => 'text-[#10B981]',
            'border_color' => 'border-emerald-100',
            'tips' => 'Pahami siklus energi dan ekosistem makhluk hidup untuk menuntaskan misi flora fauna.',
            'lore' => 'Jelajahi ekosistem sains dan pecahkan fenomena alam semesta.',
        ],
        'ips' => [
            'nama' => 'Ilmu Pengetahuan Sosial',
            'deskripsi' => 'Planet maju yang keren',
            'simbol' => 'X²',
            'bg_color' => 'bg-[#F4EFFE]',
            'text_color' => 'text-[#8B5CF6]',
            'border_color' => 'border-purple-100',
            'tips' => 'Cermati dinamika kependudukan dan interaksi ruang sosial untuk strategi peradaban terbaik.',
            'lore' => 'Pelajari peradaban, sejarah, dan dinamika interaksi sosial.',
        ],
    ];

    /**
     * Membangun alur petualangan bertingkat (Duolingo-style) dari konfigurasi level riil yang dibuat Guru.
     *
     * Aturan Mekanisme Sistem Level LearnPoint:
     * 1. Terdapat 3 Arc berurutan: Mudah -> Sedang -> Sulit.
     * 2. Setiap Arc maksimal 5 level (Level 1 s/d Level 5), maksimal 10 soal per level.
     * 3. Hanya level yang benar-benar dibuat oleh guru (memiliki minimal 1 soal) yang ditampilkan di peta siswa.
     * 4. Level terbuka secara berurutan mulai dari Arc Mudah Level 1 (tanpa syarat nilai minimum).
     * 5. Jika seluruh level pada suatu Arc selesai, otomatis membuka Arc berikutnya.
     * 6. Setelah seluruh Arc selesai, kuis dinyatakan selesai.
     */
    public function buildQuizAdventureJourney(Quiz $quiz, ?Siswa $siswa): array
    {
        $allSoals = $quiz->relationLoaded('soals')
            ? $quiz->soals->sortBy(fn ($s) => sprintf('%05d-%010d', (int) ($s->urutan ?? 0), (int) $s->id))->values()
            : $quiz->soals()->orderBy('urutan')->orderBy('id')->get();

        if ($allSoals->isNotEmpty() && $allSoals->contains(fn ($s) => (float) $s->bobot <= 0)) {
            $quiz->sinkronisasiBobotSoal();
            $allSoals = $quiz->soals()->orderBy('urutan')->orderBy('id')->get();
        }

        $jawabanSiswa = collect();
        if ($siswa && $allSoals->isNotEmpty()) {
            $jawabanSiswa = Menjawab::with('power')
                ->where('siswa_id', $siswa->id)
                ->whereIn('soal_id', $allSoals->pluck('id'))
                ->get()
                ->keyBy('soal_id');
        }

        $nilai = $siswa
            ? Nilai::where('siswa_id', $siswa->id)->where('quiz_id', $quiz->id)->first()
            : null;

        // Kelompokkan soal berdasarkan Arc (Mudah, Sedang, Sulit) dan Level (1..5)
        $grouped = [];
        foreach (self::ARCS_ORDER as $arcName) {
            $grouped[$arcName] = [];
            for ($l = 1; $l <= 5; $l++) {
                $grouped[$arcName][$l] = collect();
            }
        }

        foreach ($allSoals as $soal) {
            $rawArc = ucfirst(strtolower(trim((string) ($soal->kesulitan ?? 'Mudah'))));
            if (!in_array($rawArc, self::ARCS_ORDER, true)) {
                $rawArc = 'Mudah';
            }

            $lvlNum = (int) preg_replace('/[^0-9]/', '', (string) ($soal->level ?? 'Level 1'));
            if ($lvlNum < 1 || $lvlNum > 5) {
                $lvlNum = 1;
            }

            $grouped[$rawArc][$lvlNum]->push($soal);
        }

        // Bangun daftar level berurutan HANYA untuk level yang dibuat oleh guru
        $levelsData = [];
        $stepNumber = 0;
        $previousCompleted = true; // Level pertama otomatis terbuka
        $activeLevelStep = null;
        $arcSummaries = [];

        foreach (self::ARCS_ORDER as $arcName) {
            $arcLevelsCount = 0;
            $arcCompletedCount = 0;
            $firstStepInArc = null;
            $meta = self::ARC_META[$arcName];

            for ($lvlNum = 1; $lvlNum <= 5; $lvlNum++) {
                $soalsInLevel = $grouped[$arcName][$lvlNum]->take(Quiz::MAX_SOAL)->values();

                // Level yang tidak dibuat oleh guru TIDAK ditampilkan pada peta siswa
                if ($soalsInLevel->isEmpty()) {
                    continue;
                }

                $stepNumber++;
                $arcLevelsCount++;
                if ($firstStepInArc === null) {
                    $firstStepInArc = $stepNumber;
                }

                $countSoal = $soalsInLevel->count();
                $lvlSoalIds = $soalsInLevel->pluck('id')->toArray();

                $answeredCount = 0;
                $correctCount = 0;
                $bobotTotalLevel = 0.0;
                $poinLevelRaw = 0.0;

                foreach ($soalsInLevel as $s) {
                    $bobot = (float) ($s->bobot ?: 10);
                    $bobotTotalLevel += $bobot;
                    $ans = $jawabanSiswa->get($s->id);
                    if ($ans && $ans->jawaban_dipilih !== null && $ans->jawaban_dipilih !== '') {
                        $answeredCount++;
                        if ($ans->benar) {
                            $correctCount++;
                        }

                        // Gunakan poin_diperoleh hasil kalkulasi Power (dengan fallback ke bobot standar untuk data lama sebelum migrasi)
                        if ($ans->power_id !== null || (float) $ans->poin_diperoleh != 0.0 || !$ans->benar) {
                            $poinLevelRaw += (float) $ans->poin_diperoleh;
                        } else {
                            $poinLevelRaw += $bobot;
                        }
                    }
                }

                $poinLevel = max(0.0, $poinLevelRaw);
                $skorLevel = $bobotTotalLevel > 0
                    ? min(100, (int) round(($poinLevel / $bobotTotalLevel) * 100))
                    : ($countSoal > 0 ? (int) round(($correctCount / $countSoal) * 100) : 0);

                // Tentukan status level (tanpa syarat nilai minimum: asal seluruh soal level selesai dijawab)
                if (($nilai && $nilai->status === 'selesai') || ($countSoal > 0 && $answeredCount >= $countSoal)) {
                    $status = 'selesai';
                    $progresLabel = $correctCount . '/' . $countSoal . ' Benar';
                    $arcCompletedCount++;
                } elseif ($previousCompleted) {
                    $status = 'aktif';
                    $progresLabel = $answeredCount . '/' . $countSoal;
                    $previousCompleted = false;
                    if ($activeLevelStep === null) {
                        $activeLevelStep = $stepNumber;
                    }
                } else {
                    $status = 'terkunci';
                    $progresLabel = '0/' . $countSoal;
                }

                $estimasiWaktu = max(3, $countSoal * 2);
                $bintangLevel = ($status === 'selesai') ? Quiz::hitungBintang((float) $skorLevel) : 0;

                $levelsData[] = [
                    'id' => $stepNumber,
                    'step' => $stepNumber,
                    'nomor' => $stepNumber,
                    'arc' => $arcName,
                    'arc_nama' => $meta['nama'],
                    'level_num' => $lvlNum,
                    'level_key' => 'Level ' . $lvlNum,
                    'nama' => 'Level ' . $lvlNum,
                    'full_nama' => $arcName . ' • Level ' . $lvlNum,
                    'status' => $status,
                    'kesulitan' => $meta['label'],
                    'dots' => $meta['dots'],
                    'badge_bg' => $meta['badge_bg'],
                    'badge_text' => $meta['badge_text'],
                    'badge_border' => $meta['badge_border'],
                    'jumlah_soal' => $countSoal,
                    'estimasi_waktu' => $estimasiWaktu,
                    'progres' => $progresLabel,
                    'answered_count' => $answeredCount,
                    'correct_count' => $correctCount,
                    'skor_level' => $skorLevel,
                    'bintang' => $bintangLevel,
                    'is_first_in_arc' => ($arcLevelsCount === 1),
                    'is_last_in_arc' => false,
                    'is_final' => false,
                    'soal_ids' => $lvlSoalIds,
                    'soals' => $soalsInLevel,
                    'keterangan' => match ($status) {
                        'selesai' => "Kamu telah menyelesaikan {$arcName} Level {$lvlNum} dengan akurasi {$skorLevel}%.",
                        'aktif' => "Level terbuka! Kerjakan {$countSoal} soal pada {$arcName} Level {$lvlNum} untuk membuka perjalanan berikutnya.",
                        default => "Selesaikan level sebelumnya untuk membuka {$arcName} Level {$lvlNum}.",
                    },
                ];
            }

            // Tandai level terakhir pada Arc ini
            if ($arcLevelsCount > 0) {
                $lastIdx = count($levelsData) - 1;
                $levelsData[$lastIdx]['is_last_in_arc'] = true;

                $arcStatus = 'terkunci';
                if ($arcCompletedCount >= $arcLevelsCount) {
                    $arcStatus = 'selesai';
                } elseif ($firstStepInArc !== null && $levelsData[$firstStepInArc - 1]['status'] !== 'terkunci') {
                    $arcStatus = 'aktif';
                }

                $arcSummaries[$arcName] = [
                    'arc' => $arcName,
                    'nama' => $meta['nama'],
                    'dots' => $meta['dots'],
                    'total_levels' => $arcLevelsCount,
                    'completed_levels' => $arcCompletedCount,
                    'status' => $arcStatus,
                ];
            }
        }

        // Tandai level paling akhir dari seluruh petualangan sebagai final node
        $totalLevels = count($levelsData);
        if ($totalLevels > 0) {
            $levelsData[$totalLevels - 1]['is_final'] = true;
        }

        // Hitung koordinat X dan Y dinamis untuk jalur kurva-S peta petualangan
        $xPattern = [180, 275, 365, 250, 145, 235, 345, 260, 160, 275, 360, 240, 150, 265, 355];
        $currentY = 65;

        foreach ($levelsData as $idx => &$lvl) {
            if ($idx > 0) {
                $currentY += $lvl['is_first_in_arc'] ? 148 : 112;
            } else {
                $currentY = 85;
            }
            $lvl['x'] = $xPattern[$idx % count($xPattern)];
            $lvl['y'] = $currentY;
        }
        unset($lvl);

        $svgHeight = $totalLevels > 0 ? ($levelsData[$totalLevels - 1]['y'] + 95) : 360;

        $completedLevels = collect($levelsData)->where('status', 'selesai')->count();
        $remainingLevels = max(0, $totalLevels - $completedLevels);
        $percentage = $totalLevels > 0 ? (int) round(($completedLevels / $totalLevels) * 100) : 0;
        $isAllCompleted = $totalLevels > 0 && $completedLevels >= $totalLevels;

        $totalBintangTerkumpul = collect($levelsData)->where('status', 'selesai')->sum('bintang');
        $maxBintang = $totalLevels * 3;

        $defaultSelectedStep = $activeLevelStep ?? ($totalLevels > 0 ? $levelsData[$totalLevels - 1]['step'] : 1);

        $currentArc = 'Mudah';
        if ($activeLevelStep !== null && isset($levelsData[$activeLevelStep - 1])) {
            $currentArc = $levelsData[$activeLevelStep - 1]['arc'];
        } elseif ($totalLevels > 0) {
            $currentArc = $levelsData[$totalLevels - 1]['arc'];
        }

        return [
            'levels' => $levelsData,
            'arc_summaries' => array_values($arcSummaries),
            'total_levels' => $totalLevels,
            'total_level' => $totalLevels,
            'completed_levels' => $completedLevels,
            'remaining_levels' => $remainingLevels,
            'total_bintang' => $totalBintangTerkumpul,
            'max_bintang' => $maxBintang,
            'percentage' => $percentage,
            'is_all_completed' => $isAllCompleted,
            'active_step' => $activeLevelStep,
            'default_selected_step' => $defaultSelectedStep,
            'current_arc' => $currentArc,
            'svg_height' => $svgHeight,
            'nilai' => $nilai,
        ];
    }

    /**
     * Mencari level target pada peta petualangan berdasarkan input arc, nomor level, atau step.
     */
    public function resolveTargetLevel(
        Collection $levelsData,
        ?string $reqArc = null,
        int $reqLevelNum = 0,
        int $reqStep = 0,
        bool $fallbackToActive = true
    ): ?array {
        $targetLevel = null;

        if ($reqArc && $reqLevelNum > 0) {
            $targetLevel = $levelsData->first(function ($l) use ($reqArc, $reqLevelNum) {
                return strtolower($l['arc']) === strtolower($reqArc) && (int) $l['level_num'] === $reqLevelNum;
            });
        }

        if (!$targetLevel && $reqStep > 0) {
            $targetLevel = $levelsData->firstWhere('step', $reqStep);
        }

        if (!$targetLevel && !$reqArc && $reqLevelNum > 0) {
            $targetLevel = $levelsData->firstWhere('step', $reqLevelNum)
                ?? $levelsData->firstWhere('level_num', $reqLevelNum);
        }

        if (!$targetLevel && $fallbackToActive) {
            $targetLevel = $levelsData->firstWhere('status', 'aktif') ?? $levelsData->first();
        }

        return $targetLevel;
    }

    /**
     * Memeriksa apakah sebuah kuis termasuk ke dalam kategori planet/mata pelajaran tertentu.
     */
    public function isQuizMatchingPlanet(Quiz $quiz, int|string|null $mapelId, string $planetKey, string $planetName): bool
    {
        $mId = $quiz->pengampuKelas?->guruMapel?->mapel_id;
        $mName = strtolower($quiz->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? '');
        $cleanPlanetName = strtolower($planetName);

        return ($mapelId && $mId == $mapelId)
            || ($cleanPlanetName !== '' && str_contains($mName, $cleanPlanetName))
            || ($planetKey === 'matematika' && str_contains($mName, 'matematika'))
            || ($planetKey === 'ipa' && (str_contains($mName, 'ipa') || str_contains($mName, 'alam')))
            || ($planetKey === 'ips' && (str_contains($mName, 'ips') || str_contains($mName, 'sosial')));
    }

    /**
     * Membangun daftar planet petualangan dari mata pelajaran dan kuis riil di database.
     */
    public function buildPlanetsCollection(Collection $allQuizzes, Collection $dbMapels): Collection
    {
        $planets = collect();

        foreach (self::PLANET_PRESETS as $key => $preset) {
            $mapelModel = $dbMapels->first(function ($m) use ($preset, $key) {
                $name = strtolower($m->nama_mapel ?? '');
                return str_contains($name, strtolower($preset['nama']))
                    || str_contains($name, $key)
                    || ($key === 'ipa' && $name === 'ipa')
                    || ($key === 'ips' && $name === 'ips')
                    || ($key === 'matematika' && str_contains($name, 'matematika'));
            });

            $mapelId = $mapelModel?->id ?? ($key === 'matematika' ? 1 : ($key === 'ipa' ? 2 : 3));

            $quizzesInMapel = $allQuizzes->filter(
                fn (Quiz $q) => $this->isQuizMatchingPlanet($q, $mapelId, $key, $preset['nama'])
            );

            $uncompletedCount = $quizzesInMapel->filter(
                fn (Quiz $q) => $q->nilais->where('status', 'selesai')->isEmpty()
            )->count();

            $planets->push((object) [
                'id' => $mapelId,
                'key' => $key,
                'mapel' => $mapelModel,
                'nama' => $mapelModel?->nama_mapel ?? $preset['nama'],
                'deskripsi' => $preset['deskripsi'],
                'simbol' => $preset['simbol'],
                'bg_color' => $preset['bg_color'],
                'text_color' => $preset['text_color'],
                'border_color' => $preset['border_color'],
                'masalah_terdeteksi' => $uncompletedCount,
                'tips' => $preset['tips'],
                'lore' => $preset['lore'],
            ]);
        }

        // Tambahkan mata pelajaran lain dari database apabila memiliki kuis yang diterbitkan
        foreach ($dbMapels as $m) {
            $alreadyIncluded = $planets->contains(fn ($p) => $p->id == $m->id);
            if ($alreadyIncluded) {
                continue;
            }

            $quizzesInMapel = $allQuizzes->filter(
                fn (Quiz $q) => $q->pengampuKelas?->guruMapel?->mapel_id == $m->id
            );

            if ($quizzesInMapel->isNotEmpty()) {
                $uncompletedCount = $quizzesInMapel->filter(
                    fn (Quiz $q) => $q->nilais->where('status', 'selesai')->isEmpty()
                )->count();

                $planets->push((object) [
                    'id' => $m->id,
                    'key' => 'mapel-' . $m->id,
                    'mapel' => $m,
                    'nama' => $m->nama_mapel,
                    'deskripsi' => 'Planet petualangan ' . $m->nama_mapel,
                    'simbol' => strtoupper(substr($m->nama_mapel, 0, 2)),
                    'bg_color' => 'bg-sky-50',
                    'text_color' => 'text-sky-600',
                    'border_color' => 'border-sky-100',
                    'masalah_terdeteksi' => $uncompletedCount,
                    'tips' => 'Pelajari materi dengan teliti untuk menyelesaikan pos misi.',
                    'lore' => 'Eksplorasi sektor ' . $m->nama_mapel . ' dan selesaikan setiap pos tantangan.',
                ]);
            }
        }

        return $planets;
    }

    /**
     * Membangun daftar Zona Misi untuk planet yang dipilih berdasarkan kuis riil di database.
     */
    public function buildPlanetZonas(Collection $quizzesInPlanet, ?Siswa $siswa): Collection
    {
        $zonas = collect();
        $zoneIndex = 1;

        foreach ($quizzesInPlanet as $quiz) {
            $journey = $this->buildQuizAdventureJourney($quiz, $siswa);
            $nilai = $quiz->nilais->first();
            $isCompleted = $journey['is_all_completed'];
            $skor = $nilai ? (float) $nilai->nilai : 0;
            $stars = Quiz::hitungBintang($skor);

            $activeLvl = collect($journey['levels'])->firstWhere('status', 'aktif')
                ?? collect($journey['levels'])->first();

            $bukaLabel = $activeLvl
                ? 'Mulai ' . $activeLvl['arc'] . ' • ' . $activeLvl['nama']
                : 'Buka Peta Petualangan';

            $arcNames = array_column($journey['arc_summaries'], 'arc');
            $totalLvlCount = $journey['total_levels'] ?? 0;
            $soalsCount = $quiz->soals_count ?? $quiz->soals->count();

            $levelInfoLabel = $totalLvlCount > 0
                ? $totalLvlCount . ' Level (' . implode(' → ', $arcNames) . ') • ' . $soalsCount . ' Soal'
                : $soalsCount . ' Soal';

            $zonas->push((object) [
                'quiz' => $quiz,
                'zona_nomor' => $zoneIndex,
                'judul' => 'Zona ' . $zoneIndex . ': ' . $quiz->judul,
                'deskripsi' => $quiz->deskripsi ?: 'Selesaikan setiap level mulai dari tingkat Mudah hingga Sulit pada peta petualangan.',
                'status' => $isCompleted ? 'selesai' : 'belum_dikerjakan',
                'is_completed' => $isCompleted,
                'skor' => $skor,
                'bintang' => $stars,
                'soals_count' => $soalsCount,
                'level_label' => $levelInfoLabel,
                'buka_level_label' => $bukaLabel,
            ]);
            $zoneIndex++;
        }

        return $zonas;
    }

    /**
     * Mengambil Top 3 Leaderboard berdasarkan akumulasi nilai kuis yang telah diselesaikan.
     */
    public function getTopThreeLeaderboard(): Collection
    {
        $rows = Nilai::whereNotNull('quiz_id')
            ->where('status', 'selesai')
            ->select('siswa_id', DB::raw('SUM(nilai) as total_skor'))
            ->groupBy('siswa_id')
            ->orderByDesc('total_skor')
            ->take(3)
            ->get();

        if ($rows->isEmpty()) {
            return collect();
        }

        $siswas = Siswa::with('user')
            ->whereIn('id', $rows->pluck('siswa_id'))
            ->get()
            ->keyBy('id');

        $topThree = collect();
        foreach ($rows as $index => $item) {
            $siswaModel = $siswas->get($item->siswa_id);
            if (!$siswaModel) {
                continue;
            }

            $name = $siswaModel->nama_siswa ?? $siswaModel->user?->name ?? 'Siswa';
            $foto = $siswaModel->foto ?? $siswaModel->user?->foto ?? null;
            $initials = strtoupper(substr(explode(' ', trim($name))[0] ?? 'S', 0, 2));

            $topThree->push((object) [
                'rank' => $index + 1,
                'name' => $name,
                'skor' => (int) $item->total_skor,
                'foto' => $foto,
                'initials' => $initials,
            ]);
        }

        return $topThree;
    }

    /**
     * Mengambil daftar peringkat (Leaderboard) sektor untuk ditampilkan pada halaman misi planet.
     */
    public function getSectorLeaderboard(?Siswa $currentSiswa, int $limit = 5): Collection
    {
        $realScores = Nilai::whereNotNull('quiz_id')
            ->where('status', 'selesai')
            ->select('siswa_id', DB::raw('MAX(nilai) as top_skor'))
            ->groupBy('siswa_id')
            ->orderByDesc('top_skor')
            ->take($limit)
            ->get();

        if ($realScores->isEmpty()) {
            return collect();
        }

        $siswas = Siswa::whereIn('id', $realScores->pluck('siswa_id'))
            ->get()
            ->keyBy('id');

        $leaderboard = collect();
        foreach ($realScores as $rs) {
            $sModel = $siswas->get($rs->siswa_id);
            if (!$sModel) {
                continue;
            }

            $sName = $sModel->nama_siswa ?? 'Siswa';
            $isCurUser = ($currentSiswa && (int) $rs->siswa_id === (int) $currentSiswa->id);
            $initials = strtoupper(substr(explode(' ', trim($sName))[0] ?? 'S', 0, 2));

            $leaderboard->push([
                'initials' => $initials,
                'name' => $isCurUser ? explode(' ', trim($sName))[0] . ' (Kamu)' : $sName,
                'bg' => $isCurUser ? 'bg-[#1D4ED8]' : 'bg-blue-100',
                'text' => $isCurUser ? 'text-white' : 'text-[#2563EB]',
                'is_user' => $isCurUser,
            ]);
        }

        return $leaderboard;
    }

    /**
     * Mengurutkan koleksi butir soal sesuai prioritas Arc (Mudah -> Sedang -> Sulit), Level (1..5), dan Urutan.
     */
    public function sortSoalsByAdventureOrder(Collection $soals): Collection
    {
        $arcOrderPriority = ['Mudah' => 1, 'Sedang' => 2, 'Sulit' => 3];

        return $soals->sort(function ($a, $b) use ($arcOrderPriority) {
            $arcA = $arcOrderPriority[ucfirst(strtolower(trim((string) $a->kesulitan)))] ?? 99;
            $arcB = $arcOrderPriority[ucfirst(strtolower(trim((string) $b->kesulitan)))] ?? 99;
            if ($arcA !== $arcB) {
                return $arcA <=> $arcB;
            }

            $lvlA = (int) preg_replace('/[^0-9]/', '', (string) $a->level);
            $lvlB = (int) preg_replace('/[^0-9]/', '', (string) $b->level);
            if ($lvlA !== $lvlB) {
                return $lvlA <=> $lvlB;
            }

            $urtA = (int) ($a->urutan ?? 0);
            $urtB = (int) ($b->urutan ?? 0);
            if ($urtA !== $urtB) {
                return $urtA <=> $urtB;
            }

            return (int) $a->id <=> (int) $b->id;
        })->values();
    }
}

