<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Menjawab;
use App\Models\Nilai;
use App\Models\Notifikasi;
use App\Models\Quiz;
use App\Models\Siswa;
use App\Models\Soal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    /**
     * SISWA: Menampilkan daftar kuis petualangan yang dipublikasikan untuk kelas siswa.
     * Terhubung langsung dengan kuis yang dibuat dan dipublikasikan oleh Guru di database.
     * Jika data di database belum ada (kosong), otomatis menggunakan data dummy agar halaman selalu tampil persis desain mockup.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $siswa = $user?->siswa;

        // Ambil kelas siswa jika ada, atau fallback ke dummy kelas 7-A
        $kelas = null;
        $kelasIds = [];
        if ($siswa) {
            $anggotaKelas = $siswa->anggotaKelases()?->latest()?->first();
            $kelas = $siswa->kelas ?? $anggotaKelas?->kelas;
            $kelasIds = $siswa->anggotaKelases()->pluck('kelas_id')->filter()->unique()->toArray();
            if ($kelas?->id) {
                $kelasIds[] = $kelas->id;
            }
            $kelasIds = array_unique($kelasIds);
        }

        if (!$kelas) {
            $kelas = Kelas::first() ?? (object)['id' => 1, 'nama_kelas' => '7-A'];
        }

        // Ambil kuis dari database yang dipublikasikan oleh guru (secara aman)
        $allQuizzes = collect();
        $dbMapels = collect();

        try {
            $query = Quiz::where('status', 'published')
                ->with([
                    'pengampuKelas.guruMapel.mapel',
                    'pengampuKelas.guruMapel.guru',
                    'pengampuKelas.kelas',
                    'nilais' => function ($q) use ($siswa) {
                        if ($siswa) {
                            $q->where('siswa_id', $siswa->id);
                        }
                    },
                    'soals',
                ])
                ->withCount('soals')
                ->latest();

            if (!empty($kelasIds)) {
                $classQuizzes = (clone $query)->whereHas('pengampuKelas', function ($q) use ($kelasIds) {
                    $q->whereIn('kelas_id', $kelasIds);
                })->get();

                if ($classQuizzes->isNotEmpty()) {
                    $allQuizzes = $classQuizzes;
                } else {
                    $allQuizzes = $query->get();
                }
            } else {
                $allQuizzes = $query->get();
            }

            $dbMapels = Mapel::all();
        } catch (\Throwable $e) {
            $allQuizzes = collect();
            $dbMapels = collect();
        }

        // Preset visual galaksi agar tampilan selalu persis gambar desain mockup
        $planetPresets = [
            'matematika' => [
                'nama' => 'Matematika',
                'deskripsi' => 'Planet yang penuh dengan angka',
                'simbol' => 'Σ',
                'bg_color' => 'bg-[#EEF4FF]',
                'text_color' => 'text-[#2563EB]',
                'border_color' => 'border-blue-100',
                'tips' => 'Manfaatkan tripel Pythagoras 3:4:5 dan 5:12:13 untuk menghitung lebih cepat.',
                'lore' => 'Lorem Ipsum',
                'default_masalah' => 1,
            ],
            'ipa' => [
                'nama' => 'Ilmu Pengetahuan Alam',
                'deskripsi' => 'Planet yang penuh dengan makluk hidup',
                'simbol' => 'Δ',
                'bg_color' => 'bg-[#E8F8F0]',
                'text_color' => 'text-[#10B981]',
                'border_color' => 'border-emerald-100',
                'tips' => 'Pahami siklus energi dan ekosistem makhluk hidup untuk menuntaskan misi flora fauna.',
                'lore' => 'Lorem Ipsum',
                'default_masalah' => 2,
            ],
            'ips' => [
                'nama' => 'Ilmu Pengetahuan Sosial',
                'deskripsi' => 'Planet maju yang keren',
                'simbol' => 'X²',
                'bg_color' => 'bg-[#F4EFFE]',
                'text_color' => 'text-[#8B5CF6]',
                'border_color' => 'border-purple-100',
                'tips' => 'Cermati dinamika kependudukan dan interaksi ruang sosial untuk strategi peradaban terbaik.',
                'lore' => 'Lorem Ipsum',
                'default_masalah' => 0,
            ],
        ];

        // Buat koleksi Planet petualangan
        $planets = collect();
        foreach ($planetPresets as $key => $preset) {
            $mapelModel = $dbMapels->first(function ($m) use ($preset, $key) {
                $name = strtolower($m->nama_mapel ?? '');
                return str_contains($name, strtolower($preset['nama'])) 
                    || str_contains($name, $key)
                    || ($key === 'ipa' && $name === 'ipa')
                    || ($key === 'ips' && $name === 'ips')
                    || ($key === 'matematika' && str_contains($name, 'matematika'));
            });

            $mapelId = $mapelModel?->id ?? ($key === 'matematika' ? 1 : ($key === 'ipa' ? 2 : 3));

            // Saring kuis guru yang sesuai dengan mapel ini
            $quizzesInMapel = $allQuizzes->filter(function ($q) use ($mapelId, $key, $preset) {
                $mId = $q->pengampuKelas?->guruMapel?->mapel_id;
                $mName = strtolower($q->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? '');
                return ($mapelId && $mId == $mapelId)
                    || str_contains($mName, strtolower($preset['nama']))
                    || ($key === 'matematika' && str_contains($mName, 'matematika'))
                    || ($key === 'ipa' && (str_contains($mName, 'ipa') || str_contains($mName, 'alam')))
                    || ($key === 'ips' && (str_contains($mName, 'ips') || str_contains($mName, 'sosial')));
            });

            // Jika ada kuis asli dari guru, hitung yang belum dikerjakan oleh siswa
            if ($quizzesInMapel->isNotEmpty()) {
                $uncompletedCount = $quizzesInMapel->filter(fn($q) => $q->nilais->isEmpty())->count();
            } else {
                $uncompletedCount = $preset['default_masalah'];
            }

            $planets->push((object)[
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

        // Tambahkan mapel lain dari database jika ada kuis yang dibuat guru
        foreach ($dbMapels as $m) {
            $alreadyIncluded = $planets->contains(fn($p) => $p->id == $m->id);
            if (!$alreadyIncluded) {
                $quizzesInMapel = $allQuizzes->filter(function ($q) use ($m) {
                    return $q->pengampuKelas?->guruMapel?->mapel_id == $m->id;
                });

                if ($quizzesInMapel->isNotEmpty()) {
                    $uncompletedCount = $quizzesInMapel->filter(fn($q) => $q->nilais->isEmpty())->count();

                    $planets->push((object)[
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
                        'lore' => 'Lorem Ipsum',
                    ]);
                }
            }
        }

        // Cek apakah siswa sedang memilih planet tertentu
        $selectedPlanet = null;
        if ($request->filled('mapel_id') || $request->filled('planet')) {
            $searchId = $request->input('mapel_id');
            $searchKey = strtolower($request->input('planet', ''));

            $selectedPlanet = $planets->first(function ($p) use ($searchId, $searchKey) {
                if ($searchId && $p->id == $searchId) return true;
                if ($searchKey && ($p->key === $searchKey || strtolower($p->nama) === $searchKey)) return true;
                return false;
            }) ?? $planets->first();
        }

        // Query Top 3 Leaderboard untuk Podiums di Hero Banner (Kiri: Top 2, Tengah: Top 1, Kanan: Top 3)
        $topThree = collect();
        try {
            $realLeaderboardQuery = Nilai::whereNotNull('quiz_id')
                ->where('status', 'selesai')
                ->select('siswa_id', DB::raw('SUM(nilai) as total_skor'))
                ->groupBy('siswa_id')
                ->orderByDesc('total_skor')
                ->take(3)
                ->get();

            foreach ($realLeaderboardQuery as $index => $item) {
                $siswaModel = Siswa::with('user')->find($item->siswa_id);
                if (!$siswaModel) continue;

                $name = $siswaModel->nama_siswa ?? $siswaModel->user?->name ?? 'Siswa';
                $foto = $siswaModel->foto ?? $siswaModel->user?->foto ?? null;
                $initials = strtoupper(substr(explode(' ', trim($name))[0] ?? 'S', 0, 2));

                $topThree->push((object)[
                    'rank' => $index + 1,
                    'name' => $name,
                    'skor' => (int)$item->total_skor,
                    'foto' => $foto,
                    'initials' => $initials,
                ]);
            }
        } catch (\Throwable $e) {
            $topThree = collect();
        }

        // Jika belum ada siswa di peringkat 1, 2, atau 3, nilai bernilai null (kosong)
        $top1 = $topThree->get(0) ?? null;
        $top2 = $topThree->get(1) ?? null;
        $top3 = $topThree->get(2) ?? null;

        // Jika planet dipilih: Tampilkan Mode 2 (Tahapan Penyelamatan & Target)
        if ($selectedPlanet) {
            $mapelId = $selectedPlanet->id;
            $quizzesInPlanet = $allQuizzes->filter(function ($q) use ($mapelId, $selectedPlanet) {
                $mId = $q->pengampuKelas?->guruMapel?->mapel_id;
                $mName = strtolower($q->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? '');
                $key = $selectedPlanet->key;
                $nama = strtolower($selectedPlanet->nama ?? '');
                return ($mId && $mId == $mapelId)
                    || ($nama && str_contains($mName, $nama))
                    || ($key === 'matematika' && str_contains($mName, 'matematika'))
                    || ($key === 'ipa' && (str_contains($mName, 'ipa') || str_contains($mName, 'alam')))
                    || ($key === 'ips' && (str_contains($mName, 'ips') || str_contains($mName, 'sosial')));
            })->values();

            $zonas = collect();
            $zoneIndex = 1;

            // Hubungkan kuis nyata milik guru
            foreach ($quizzesInPlanet as $quiz) {
                $journey = $this->buildQuizAdventureJourney($quiz, $siswa);
                $nilai = $quiz->nilais->first();
                $isCompleted = $journey['is_all_completed'];
                $skor = $nilai ? (float)$nilai->nilai : 0;
                $stars = $skor >= 100 ? 3 : ($skor >= 80 ? 2 : ($skor > 0 ? 1 : 0));

                $activeLvl = collect($journey['levels'])->firstWhere('status', 'aktif') ?? collect($journey['levels'])->first();
                $bukaLabel = $activeLvl
                    ? 'Mulai ' . $activeLvl['arc'] . ' • ' . $activeLvl['nama']
                    : 'Buka Peta Petualangan';

                $arcNames = array_column($journey['arc_summaries'], 'arc');
                $totalLvlCount = $journey['total_levels'] ?? $journey['total_level'] ?? 0;
                $levelInfoLabel = $totalLvlCount > 0
                    ? $totalLvlCount . ' Level (' . implode(' → ', $arcNames) . ') • ' . ($quiz->soals_count ?? $quiz->soals->count()) . ' Soal'
                    : ($quiz->soals_count ?? $quiz->soals->count()) . ' Soal';

                $zonas->push((object)[
                    'quiz' => $quiz,
                    'zona_nomor' => $zoneIndex,
                    'judul' => 'Zona ' . $zoneIndex . ': ' . $quiz->judul,
                    'deskripsi' => $quiz->deskripsi ?: 'Selesaikan setiap level mulai dari tingkat Mudah hingga Sulit pada peta petualangan.',
                    'status' => $isCompleted ? 'selesai' : 'belum_dikerjakan',
                    'is_completed' => $isCompleted,
                    'skor' => $skor,
                    'bintang' => $stars,
                    'soals_count' => $quiz->soals_count ?? $quiz->soals->count(),
                    'level_label' => $levelInfoLabel,
                    'buka_level_label' => $bukaLabel,
                ]);
                $zoneIndex++;
            }

            // Jika belum ada kuis dari guru di database untuk planet ini, gunakan zona dummy mockup
            if ($zonas->isEmpty()) {
                $zonas->push((object)[
                    'quiz' => null,
                    'zona_nomor' => 1,
                    'judul' => 'Zona 1: Pos Aljabar Dasar',
                    'deskripsi' => 'Kuasai konsep aljabar dan rumus dasar matematika untuk memulai petualangan.',
                    'status' => 'selesai',
                    'is_completed' => true,
                    'skor' => 100,
                    'bintang' => 3,
                    'soals_count' => 10,
                    'level_label' => '10 Level',
                    'buka_level_label' => 'Buka Level 1 Sekarang',
                ]);

                $zonas->push((object)[
                    'quiz' => null,
                    'zona_nomor' => 2,
                    'judul' => 'Zona 2: Kawah Geometri & Pythagoras',
                    'deskripsi' => 'Kalkulasi sudut siku-siku & teorema hipotenusa untuk menutup celah retakan dimensi.',
                    'status' => 'belum_dikerjakan',
                    'is_completed' => false,
                    'skor' => 0,
                    'bintang' => 0,
                    'soals_count' => 30,
                    'level_label' => '30 Level',
                    'buka_level_label' => 'Buka Level 26 Sekarang',
                ]);
            }

            $completedCount = $zonas->where('is_completed', true)->count();
            $progresSektor = 35;
            if ($zonas->count() > 2) {
                $progresSektor = round(($completedCount / $zonas->count()) * 100);
            }

            $energi = 3;
            $xp = '2.450';

            // Active Zona untuk Target Penyelamatan
            $requestedQuizId = $request->input('quiz_id');
            $activeZona = null;
            if ($requestedQuizId) {
                $activeZona = $zonas->first(fn($z) => $z->quiz?->id == $requestedQuizId);
            }
            if (!$activeZona) {
                $activeZona = $zonas->firstWhere('status', 'belum_dikerjakan') ?? $zonas->first();
            }

            // Leaderboard terhubung dengan data Nilai asli di database
            $leaderboard = collect();
            try {
                $realScores = Nilai::whereNotNull('quiz_id')
                    ->where('status', 'selesai')
                    ->select('siswa_id', DB::raw('MAX(nilai) as top_skor'))
                    ->groupBy('siswa_id')
                    ->orderByDesc('top_skor')
                    ->take(5)
                    ->get();

                foreach ($realScores as $rs) {
                    $sModel = Siswa::find($rs->siswa_id);
                    if (!$sModel) continue;

                    $sName = $sModel->nama_siswa ?? 'Siswa';
                    $isCurUser = ($siswa && $rs->siswa_id == $siswa->id);
                    $initials = strtoupper(substr(explode(' ', trim($sName))[0] ?? 'S', 0, 2));

                    $leaderboard->push([
                        'initials' => $initials,
                        'name' => $isCurUser ? explode(' ', trim($sName))[0] . ' (Kamu)' : $sName,
                        'bg' => $isCurUser ? 'bg-[#1D4ED8]' : 'bg-blue-100',
                        'text' => $isCurUser ? 'text-white' : 'text-[#2563EB]',
                        'is_user' => $isCurUser,
                    ]);
                }
            } catch (\Throwable $e) {
                $leaderboard = collect();
            }

            return view('siswa.kuis.index', [
                'viewMode' => 'mission',
                'selectedPlanet' => $selectedPlanet,
                'planets' => $planets,
                'zonas' => $zonas,
                'activeZona' => $activeZona,
                'progresSektor' => $progresSektor,
                'energi' => $energi,
                'xp' => $xp,
                'leaderboard' => $leaderboard,
                'tips' => $selectedPlanet->tips,
                'kelas' => $kelas,
                'top1' => $top1,
                'top2' => $top2,
                'top3' => $top3,
            ]);
        }

        // Tampilan Mode 1 (Pilihan Planet)
        return view('siswa.kuis.index', [
            'viewMode' => 'planets',
            'planets' => $planets,
            'kelas' => $kelas,
            'top1' => $top1,
            'top2' => $top2,
            'top3' => $top3,
        ]);
    }

    public function siswaIndex(Request $request)
    {
        return $this->index($request);
    }

    /**
     * Helper: Membangun alur petualangan bertingkat (Duolingo-style) dari konfigurasi level riil yang dibuat Guru.
     *
     * Aturan Mekanisme Sistem Level LearnPoint:
     * 1. Terdapat 3 Arc berurutan: Mudah -> Sedang -> Sulit.
     * 2. Setiap Arc maksimal 5 level (Level 1 s/d Level 5), maksimal 10 soal per level.
     * 3. Hanya level yang benar-benar dibuat oleh guru (memiliki minimal 1 soal) yang ditampilkan di peta siswa.
     * 4. Level terbuka secara berurutan mulai dari Arc Mudah Level 1 (tanpa syarat nilai minimum).
     * 5. Jika seluruh level pada suatu Arc selesai, otomatis membuka Arc berikutnya.
     * 6. Setelah seluruh Arc selesai, kuis dinyatakan selesai.
     */
    private function buildQuizAdventureJourney(Quiz $quiz, ?Siswa $siswa): array
    {
        $allSoals = $quiz->relationLoaded('soals')
            ? $quiz->soals->sortBy(fn($s) => sprintf('%05d-%010d', (int) ($s->urutan ?? 0), (int) $s->id))->values()
            : $quiz->soals()->orderBy('urutan')->orderBy('id')->get();

        $jawabanSiswa = collect();
        if ($siswa && $allSoals->isNotEmpty()) {
            $jawabanSiswa = Menjawab::where('siswa_id', $siswa->id)
                ->whereIn('soal_id', $allSoals->pluck('id'))
                ->get()
                ->keyBy('soal_id');
        }

        $nilai = $siswa
            ? Nilai::where('siswa_id', $siswa->id)->where('quiz_id', $quiz->id)->first()
            : null;

        $arcsOrder = ['Mudah', 'Sedang', 'Sulit'];
        $arcMeta = [
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

        // Kelompokkan soal berdasarkan Arc (Mudah, Sedang, Sulit) dan Level (1..5)
        $grouped = [
            'Mudah' => [],
            'Sedang' => [],
            'Sulit' => [],
        ];

        foreach ($arcsOrder as $arcName) {
            for ($l = 1; $l <= 5; $l++) {
                $grouped[$arcName][$l] = collect();
            }
        }

        foreach ($allSoals as $soal) {
            $rawArc = ucfirst(strtolower(trim((string) ($soal->kesulitan ?? 'Mudah'))));
            if (!in_array($rawArc, $arcsOrder, true)) {
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

        foreach ($arcsOrder as $arcName) {
            $arcLevelsCount = 0;
            $arcCompletedCount = 0;
            $firstStepInArc = null;

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
                $bobotTotalLevel = 0;
                $bobotBenarLevel = 0;

                foreach ($soalsInLevel as $s) {
                    $bobot = (int) ($s->bobot ?: 10);
                    $bobotTotalLevel += $bobot;
                    $ans = $jawabanSiswa->get($s->id);
                    if ($ans && $ans->jawaban_dipilih !== null && $ans->jawaban_dipilih !== '') {
                        $answeredCount++;
                        if ($ans->benar) {
                            $correctCount++;
                            $bobotBenarLevel += $bobot;
                        }
                    }
                }

                $skorLevel = $bobotTotalLevel > 0
                    ? (int) round(($bobotBenarLevel / $bobotTotalLevel) * 100)
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

                $levelsData[] = [
                    'id' => $stepNumber,
                    'step' => $stepNumber,
                    'nomor' => $stepNumber,
                    'arc' => $arcName,
                    'arc_nama' => $arcMeta[$arcName]['nama'],
                    'level_num' => $lvlNum,
                    'level_key' => 'Level ' . $lvlNum,
                    'nama' => 'Level ' . $lvlNum,
                    'full_nama' => $arcName . ' • Level ' . $lvlNum,
                    'status' => $status,
                    'kesulitan' => $arcMeta[$arcName]['label'],
                    'dots' => $arcMeta[$arcName]['dots'],
                    'badge_bg' => $arcMeta[$arcName]['badge_bg'],
                    'badge_text' => $arcMeta[$arcName]['badge_text'],
                    'badge_border' => $arcMeta[$arcName]['badge_border'],
                    'jumlah_soal' => $countSoal,
                    'estimasi_waktu' => $estimasiWaktu,
                    'progres' => $progresLabel,
                    'answered_count' => $answeredCount,
                    'correct_count' => $correctCount,
                    'skor_level' => $skorLevel,
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
                    'nama' => $arcMeta[$arcName]['nama'],
                    'dots' => $arcMeta[$arcName]['dots'],
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
                // Beri jarak ekstra saat berpindah Arc (Mudah -> Sedang -> Sulit) untuk badge transisi Arc
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

        // Default level yang dipilih pada panel kanan: level yang sedang aktif, atau level terakhir jika semua selesai
        $defaultSelectedStep = $activeLevelStep ?? ($totalLevels > 0 ? $levelsData[$totalLevels - 1]['step'] : 1);

        // Current Arc yang sedang dijalani siswa
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
     * SISWA: Halaman peta petualangan dan pemilihan level kuis sebelum mulai pengerjaan.
     * Menampilkan perjalanan level secara berurutan dari Mudah -> Sedang -> Sulit sesuai konfigurasi Guru.
     */
    public function show(Quiz $quiz)
    {
        $user = auth()->user();
        $siswa = $user?->siswa;

        if (!$siswa && $user) {
            $siswa = Siswa::firstOrCreate(['nis' => '12345'], [
                'nama_siswa' => $user->name,
                'status' => 'aktif',
            ]);
            $user->update(['siswa_id' => $siswa->id]);
        }

        abort_unless($siswa, 403, 'Akses khusus siswa.');
        $this->authorizeSiswaClass($quiz, $siswa);
        abort_unless($quiz->isPublished(), 403, 'Kuis belum dipublikasikan.');

        $quiz->load([
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.guruMapel.guru',
            'pengampuKelas.kelas',
            'soals',
        ]);

        $mapel = $quiz->pengampuKelas?->guruMapel?->mapel;
        $guru = $quiz->pengampuKelas?->guruMapel?->guru;
        $kelas = $quiz->pengampuKelas?->kelas;

        $journey = $this->buildQuizAdventureJourney($quiz, $siswa);

        $nilai = $journey['nilai'];
        $levelsData = $journey['levels'];
        $arcSummaries = $journey['arc_summaries'];
        $totalSelesai = $journey['completed_levels'];
        $totalTersisa = $journey['remaining_levels'];
        $totalLevel = $journey['total_levels'];
        $persentase = $journey['percentage'];
        $defaultSelectedLevelId = $journey['default_selected_step'];
        $currentArc = $journey['current_arc'];
        $svgHeight = $journey['svg_height'];
        $isAllCompleted = $journey['is_all_completed'];

        return view('siswa.kuis.show', compact(
            'quiz',
            'mapel',
            'guru',
            'kelas',
            'nilai',
            'levelsData',
            'arcSummaries',
            'totalSelesai',
            'totalTersisa',
            'totalLevel',
            'persentase',
            'defaultSelectedLevelId',
            'currentArc',
            'svgHeight',
            'isAllCompleted'
        ));
    }

    public function siswaShow(Quiz $quiz)
    {
        return $this->show($quiz);
    }

    /**
     * SISWA: Halaman pengerjaan kuis interaktif per level dengan timer dan pengacakan opsi.
     */
    public function kerjakan(Quiz $quiz)
    {
        $user = auth()->user();
        $siswa = $user?->siswa;

        if (!$siswa && $user) {
            $siswa = Siswa::firstOrCreate(['nis' => '12345'], [
                'nama_siswa' => $user->name,
                'status' => 'aktif',
            ]);
            $user->update(['siswa_id' => $siswa->id]);
        }

        abort_unless($siswa, 403, 'Akses khusus siswa.');
        $this->authorizeSiswaClass($quiz, $siswa);
        abort_unless($quiz->isPublished(), 403, 'Kuis belum dipublikasikan.');

        // Cek jadwal mulai
        if ($quiz->waktu_mulai && now()->lessThan($quiz->waktu_mulai)) {
            return redirect()->route('siswa.kuis.show', $quiz->id)
                ->with('error', 'Kuis belum dimulai. Jadwal mulai: ' . $quiz->waktu_mulai->translatedFormat('d F Y, H:i') . ' WIB.');
        }

        // Cek jadwal selesai
        if ($quiz->waktu_selesai && now()->greaterThan($quiz->waktu_selesai)) {
            return redirect()->route('siswa.kuis.show', $quiz->id)
                ->with('error', 'Waktu pengerjaan kuis telah berakhir pada: ' . $quiz->waktu_selesai->translatedFormat('d F Y, H:i') . ' WIB.');
        }

        $quiz->load([
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.guruMapel.guru',
            'soals',
        ]);

        $journey = $this->buildQuizAdventureJourney($quiz, $siswa);
        $levelsData = collect($journey['levels']);

        if ($levelsData->isEmpty()) {
            return redirect()->route('siswa.kuis.show', $quiz->id)
                ->with('warning', 'Guru belum menambahkan soal pada kuis ini.');
        }

        // Cari level target berdasarkan parameter arc & level, atau step, atau otomatis ke level aktif
        $reqArc = request('arc');
        $reqLevelNum = (int) preg_replace('/[^0-9]/', '', (string) request('level', '0'));
        $reqStep = (int) request('step', 0);

        $targetLevel = null;

        if ($reqArc && $reqLevelNum > 0) {
            $targetLevel = $levelsData->first(function ($l) use ($reqArc, $reqLevelNum) {
                return strtolower($l['arc']) === strtolower($reqArc) && (int) $l['level_num'] === $reqLevelNum;
            });
        }

        if (!$targetLevel && $reqStep > 0) {
            $targetLevel = $levelsData->firstWhere('step', $reqStep);
        }

        if (!$targetLevel && $reqLevelNum > 0) {
            $targetLevel = $levelsData->firstWhere('step', $reqLevelNum)
                ?? $levelsData->firstWhere('level_num', $reqLevelNum);
        }

        if (!$targetLevel) {
            $targetLevel = $levelsData->firstWhere('status', 'aktif') ?? $levelsData->first();
        }

        // Cegah akses ke level yang masih terkunci
        if ($targetLevel['status'] === 'terkunci') {
            return redirect()->route('siswa.kuis.show', $quiz->id)
                ->with('warning', "Selesaikan level sebelumnya terlebih dahulu untuk membuka {$targetLevel['full_nama']}.");
        }

        $soals = $targetLevel['soals'];
        $selectedArc = $targetLevel['arc'];
        $selectedLevel = $targetLevel['level_num'];
        $selectedStep = $targetLevel['step'];

        // Acak opsi jawaban jika fitur acak_jawaban diaktifkan oleh guru
        if ($quiz->acak_jawaban) {
            foreach ($soals as $soal) {
                $pilihan = $soal->pilihan;
                if (is_array($pilihan) && count($pilihan) > 1) {
                    $keys = array_keys($pilihan);
                    shuffle($keys);
                    $shuffled = [];
                    foreach ($keys as $k) {
                        $shuffled[$k] = $pilihan[$k];
                    }
                    $soal->pilihan_shuffled = $shuffled;
                } else {
                    $soal->pilihan_shuffled = $pilihan;
                }
            }
        } else {
            foreach ($soals as $soal) {
                $soal->pilihan_shuffled = $soal->pilihan;
            }
        }

        $savedAnswers = Menjawab::where('siswa_id', $siswa->id)
            ->whereIn('soal_id', $soals->pluck('id'))
            ->pluck('jawaban_dipilih', 'soal_id')
            ->toArray();

        $remainingSeconds = null;
        if ($quiz->waktu_selesai) {
            $remainingSeconds = max(0, now()->diffInSeconds($quiz->waktu_selesai, false));
        }

        return view('siswa.kuis.kerjakan', compact(
            'quiz',
            'soals',
            'remainingSeconds',
            'savedAnswers',
            'targetLevel',
            'selectedArc',
            'selectedLevel',
            'selectedStep'
        ));
    }

    public function siswaKerjakan(Quiz $quiz)
    {
        return $this->kerjakan($quiz);
    }

    /**
     * SISWA: Menyimpan progres jawaban kuis sementara (draft) dan kembali ke peta petualangan.
     */
    public function simpanDraft(Request $request, Quiz $quiz)
    {
        $user = auth()->user();
        $siswa = $user?->siswa;
        abort_unless($siswa, 403, 'Akses ditolak.');

        $this->authorizeSiswaClass($quiz, $siswa);
        abort_unless($quiz->isPublished(), 403, 'Kuis belum dipublikasikan.');

        // Cek keterlambatan pengumpulan (toleransi 2 menit)
        if ($quiz->waktu_selesai && now()->greaterThan($quiz->waktu_selesai->copy()->addMinutes(2))) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Waktu pengerjaan kuis telah kedaluwarsa.'
                ], 400);
            }
            return redirect()->route('siswa.kuis.show', $quiz->id)
                ->with('error', 'Waktu pengumpulan kuis telah kedaluwarsa.');
        }

        $soals = $quiz->soals;
        $jawabanInputs = $request->input('jawaban', []);

        DB::transaction(function () use ($soals, $jawabanInputs, $siswa) {
            foreach ($soals as $soal) {
                if (isset($jawabanInputs[$soal->id]) && $jawabanInputs[$soal->id] !== '' && $jawabanInputs[$soal->id] !== null) {
                    $jawabanUser = $jawabanInputs[$soal->id];
                    $isBenar = false;

                    $kunci = $soal->kunci_jawaban;
                    if (is_array($kunci)) {
                        $cleanKunci = array_map(fn($k) => strtolower(trim((string) $k)), $kunci);
                        $isBenar = in_array(strtolower(trim((string) $jawabanUser)), $cleanKunci);
                    } else {
                        $isBenar = strtolower(trim((string) $jawabanUser)) === strtolower(trim((string) $kunci));
                    }

                    Menjawab::updateOrCreate(
                        [
                            'soal_id'  => $soal->id,
                            'siswa_id' => $siswa->id,
                        ],
                        [
                            'jawaban_dipilih' => is_array($jawabanUser) ? json_encode($jawabanUser) : (string) $jawabanUser,
                            'benar'           => $isBenar,
                        ]
                    );
                }
            }
        });

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'      => true,
                'message'      => 'Progres jawaban level berhasil disimpan! Kamu dapat melanjutkan pengerjaan kapan saja.',
                'redirect_url' => route('siswa.kuis.show', $quiz->id),
            ]);
        }

        return redirect()->route('siswa.kuis.show', $quiz->id)
            ->with('success', 'Progres jawaban level berhasil disimpan!');
    }

    public function siswaSimpanDraft(Request $request, Quiz $quiz)
    {
        return $this->simpanDraft($request, $quiz);
    }

    /**
     * SISWA: Memproses pengiriman jawaban pada suatu level, membuka level/Arc berikutnya,
     * dan langsung menampilkan halaman hasil pengerjaan level tersebut.
     */
    public function submit(Request $request, Quiz $quiz)
    {
        $user = auth()->user();
        $siswa = $user?->siswa;
        abort_unless($siswa, 403, 'Akses ditolak.');

        $this->authorizeSiswaClass($quiz, $siswa);
        abort_unless($quiz->isPublished(), 403, 'Kuis belum dipublikasikan.');

        // Cek keterlambatan pengumpulan (toleransi 2 menit)
        if ($quiz->waktu_selesai && now()->greaterThan($quiz->waktu_selesai->copy()->addMinutes(2))) {
            return redirect()->route('siswa.kuis.show', $quiz->id)
                ->with('error', 'Waktu pengumpulan kuis telah kedaluwarsa.');
        }

        $quiz->load('soals');
        $journeyBefore = $this->buildQuizAdventureJourney($quiz, $siswa);
        $levelsBefore = collect($journeyBefore['levels']);

        $reqArc = $request->input('arc');
        $reqLevelNum = (int) preg_replace('/[^0-9]/', '', (string) $request->input('level', '0'));
        $reqStep = (int) $request->input('step', 0);

        $targetLevel = null;
        if ($reqArc && $reqLevelNum > 0) {
            $targetLevel = $levelsBefore->first(function ($l) use ($reqArc, $reqLevelNum) {
                return strtolower($l['arc']) === strtolower($reqArc) && (int) $l['level_num'] === $reqLevelNum;
            });
        }
        if (!$targetLevel && $reqStep > 0) {
            $targetLevel = $levelsBefore->firstWhere('step', $reqStep);
        }
        if (!$targetLevel) {
            $targetLevel = $levelsBefore->firstWhere('status', 'aktif') ?? $levelsBefore->first();
        }

        if (!$targetLevel) {
            return redirect()->route('siswa.kuis.show', $quiz->id);
        }

        $soalsSession = $targetLevel['soals'];

        DB::transaction(function () use ($soalsSession, $request, $siswa) {
            foreach ($soalsSession as $soal) {
                $jawabanUser = $request->input("jawaban.{$soal->id}");

                // Jika tidak ada di request, coba ambil dari draft tersimpan sebelumnya
                if ($jawabanUser === null || $jawabanUser === '') {
                    $existingMenjawab = Menjawab::where('soal_id', $soal->id)->where('siswa_id', $siswa->id)->first();
                    if ($existingMenjawab && $existingMenjawab->jawaban_dipilih !== null && $existingMenjawab->jawaban_dipilih !== '') {
                        $jawabanUser = $existingMenjawab->jawaban_dipilih;
                    }
                }

                // Pastikan soal dalam level yang disubmit tercatat selesai dijawab
                if ($jawabanUser === null || $jawabanUser === '') {
                    $jawabanUser = '-';
                }

                $isBenar = false;
                if ($jawabanUser !== '-') {
                    $kunci = $soal->kunci_jawaban;
                    if (is_array($kunci)) {
                        $cleanKunci = array_map(fn($k) => strtolower(trim((string) $k)), $kunci);
                        $isBenar = in_array(strtolower(trim((string) $jawabanUser)), $cleanKunci);
                    } else {
                        $isBenar = strtolower(trim((string) $jawabanUser)) === strtolower(trim((string) $kunci));
                    }
                }

                Menjawab::updateOrCreate(
                    [
                        'soal_id'  => $soal->id,
                        'siswa_id' => $siswa->id,
                    ],
                    [
                        'jawaban_dipilih' => is_array($jawabanUser) ? json_encode($jawabanUser) : (string) $jawabanUser,
                        'benar'           => $isBenar,
                    ]
                );
            }
        });

        // Evaluasi ulang progres petualangan setelah level ini diselesaikan
        $journeyAfter = $this->buildQuizAdventureJourney($quiz, $siswa);

        // Jika seluruh level pada semua Arc telah selesai, rekap nilai akhir kuis
        if ($journeyAfter['is_all_completed']) {
            $allQuizSoals = $quiz->soals;
            $allQuizSoalIds = $allQuizSoals->pluck('id')->toArray();
            $totalBobot = $allQuizSoals->sum('bobot') ?: 100;
            $skorDidapat = 0;

            $menjawabSemua = Menjawab::where('siswa_id', $siswa->id)
                ->whereIn('soal_id', $allQuizSoalIds)
                ->get();

            foreach ($allQuizSoals as $soal) {
                $ans = $menjawabSemua->firstWhere('soal_id', $soal->id);
                if ($ans && $ans->benar) {
                    $skorDidapat += ($soal->bobot ?: 10);
                }
            }

            $nilaiAkhir = $totalBobot > 0 ? round(($skorDidapat / $totalBobot) * 100, 2) : 100;
            if ($nilaiAkhir > 100) {
                $nilaiAkhir = 100;
            }

            $rawDurasi = $request->input('durasi', 0);
            $durasiDetik = 0;
            if (is_numeric($rawDurasi)) {
                $durasiDetik = (int) $rawDurasi;
            } elseif (is_string($rawDurasi) && strpos($rawDurasi, ':') !== false) {
                $parts = explode(':', trim($rawDurasi));
                if (count($parts) === 2) {
                    $durasiDetik = ((int) $parts[0] * 60) + (int) $parts[1];
                } elseif (count($parts) === 3) {
                    $durasiDetik = ((int) $parts[0] * 3600) + ((int) $parts[1] * 60) + (int) $parts[2];
                }
            }

            $existingNilai = Nilai::where('siswa_id', $siswa->id)->where('quiz_id', $quiz->id)->first();
            $prevDurasi = (int) ($existingNilai?->durasi ?? 0);

            Nilai::updateOrCreate(
                [
                    'siswa_id' => $siswa->id,
                    'quiz_id'  => $quiz->id,
                ],
                [
                    'nilai'  => $nilaiAkhir,
                    'durasi' => $prevDurasi + $durasiDetik,
                    'status' => 'selesai',
                ]
            );

            // Kirim notifikasi interaktif ke Guru pengampu jika baru pertama kali selesai
            if (!$existingNilai) {
                $guruUser = $quiz->pengampuKelas?->guruMapel?->guru?->user;
                if ($guruUser) {
                    $namaSiswa = $siswa->nama_siswa ?? $user->name;
                    $namaKelas = $quiz->pengampuKelas?->kelas?->nama_kelas ?? 'Kelas';
                    Notifikasi::kirim(
                        $guruUser->id,
                        "Kuis Selesai Dikerjakan: {$quiz->judul}",
                        "Siswa {$namaSiswa} ({$namaKelas}) telah menyelesaikan seluruh level kuis '{$quiz->judul}'.",
                        'kuis_selesai',
                        route('guru.kuis.show', $quiz->id),
                        $quiz->id,
                        ['siswa_id' => $siswa->id, 'quiz_id' => $quiz->id]
                    );
                }
            }
        }

        // Langsung tampilkan hasil pengerjaan level yang baru saja diselesaikan
        return redirect()->route('siswa.kuis.hasil', [
            'quiz' => $quiz->id,
            'arc' => $targetLevel['arc'],
            'level' => $targetLevel['level_num'],
        ])->with('success', "Level {$targetLevel['level_num']} pada Arc {$targetLevel['arc']} berhasil diselesaikan!");
    }

    public function siswaSubmit(Request $request, Quiz $quiz)
    {
        return $this->submit($request, $quiz);
    }

    /**
     * SISWA: Menampilkan hasil pengerjaan level yang baru diselesaikan maupun rekap keseluruhan kuis.
     */
    public function hasil(Quiz $quiz)
    {
        $user = auth()->user();
        $siswa = $user?->siswa;
        abort_unless($siswa, 403, 'Akses ditolak.');

        $this->authorizeSiswaClass($quiz, $siswa);

        $quiz->load([
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.guruMapel.guru',
            'pengampuKelas.kelas',
            'soals',
        ]);

        $journey = $this->buildQuizAdventureJourney($quiz, $siswa);
        $levelsData = collect($journey['levels']);
        $nilai = $journey['nilai'];

        // Cari apakah siswa sedang melihat hasil dari level spesifik
        $reqArc = request('arc');
        $reqLevelNum = (int) preg_replace('/[^0-9]/', '', (string) request('level', '0'));
        $reqStep = (int) request('step', 0);

        $currentLevel = null;
        if ($reqArc && $reqLevelNum > 0) {
            $currentLevel = $levelsData->first(function ($l) use ($reqArc, $reqLevelNum) {
                return strtolower($l['arc']) === strtolower($reqArc) && (int) $l['level_num'] === $reqLevelNum;
            });
        }
        if (!$currentLevel && $reqStep > 0) {
            $currentLevel = $levelsData->firstWhere('step', $reqStep);
        }

        // Jika tidak ada parameter level, ambil level terakhir yang sudah selesai
        if (!$currentLevel) {
            $currentLevel = $levelsData->where('status', 'selesai')->last();
        }

        if (!$currentLevel && !$nilai) {
            return redirect()->route('siswa.kuis.show', $quiz->id)
                ->with('warning', 'Kamu belum menyelesaikan level pada kuis ini.');
        }

        // Tentukan level berikutnya yang terbuka setelah level ini
        $nextLevel = null;
        $isNewArcUnlocked = false;
        if ($currentLevel) {
            $nextLevel = $levelsData->firstWhere('step', $currentLevel['step'] + 1);
            if ($nextLevel && $nextLevel['arc'] !== $currentLevel['arc']) {
                $isNewArcUnlocked = true;
            }
        }

        // Ambil daftar soal yang ditampilkan pada ulasan (soal pada level tersebut, atau seluruh soal jika melihat hasil akhir penuh)
        $showAllSoals = request()->boolean('all') || (!$reqArc && !$reqLevelNum && !$reqStep && $journey['is_all_completed']);

        if ($showAllSoals || !$currentLevel) {
            $soals = $quiz->soals()->with(['menjawabs' => fn($q) => $q->where('siswa_id', $siswa->id)])->orderBy('urutan')->orderBy('id')->get();
        } else {
            $soals = $quiz->soals()
                ->whereIn('id', $currentLevel['soal_ids'])
                ->with(['menjawabs' => fn($q) => $q->where('siswa_id', $siswa->id)])
                ->orderBy('urutan')
                ->orderBy('id')
                ->get();
        }

        $totalSoal = max(1, $soals->count());
        $totalBenar = $soals->filter(fn($s) => $s->menjawabs->first()?->benar)->count();
        $totalSalah = max(0, $soals->count() - $totalBenar);
        $skorDitampilkan = $currentLevel && !$showAllSoals
            ? $currentLevel['skor_level']
            : ($nilai ? round($nilai->nilai) : (int) round(($totalBenar / $totalSoal) * 100));

        return view('siswa.kuis.hasil', [
            'quiz' => $quiz,
            'nilai' => $nilai,
            'soals' => $soals,
            'totalSoal' => $soals->count(),
            'totalBenar' => $totalBenar,
            'totalSalah' => $totalSalah,
            'skorDitampilkan' => $skorDitampilkan,
            'currentLevel' => $currentLevel,
            'nextLevel' => $nextLevel,
            'isNewArcUnlocked' => $isNewArcUnlocked,
            'journey' => $journey,
            'showAllSoals' => $showAllSoals,
        ]);
    }

    public function siswaHasil(Quiz $quiz)
    {
        return $this->hasil($quiz);
    }

    /**
     * Helper verifikasi bahwa kuis ditujukan untuk kelas siswa yang login.
     */
    private function authorizeSiswaClass(Quiz $quiz, Siswa $siswa): void
    {
        $quizKelasId = $quiz->pengampuKelas?->kelas_id;
        if (!$quizKelasId) {
            return;
        }

        $siswaKelasIds = $siswa->anggotaKelases()->pluck('kelas_id')->toArray();
        if ($siswa->kelas?->id) {
            $siswaKelasIds[] = $siswa->kelas->id;
        }

        if (empty($siswaKelasIds)) {
            return;
        }

        if (!in_array($quizKelasId, $siswaKelasIds)) {
            $totalKelas = Kelas::count();
            if ($totalKelas <= 1) {
                return;
            }
            abort(403, 'Akses ditolak: Kuis ini bukan untuk kelas Anda.');
        }
    }
}

