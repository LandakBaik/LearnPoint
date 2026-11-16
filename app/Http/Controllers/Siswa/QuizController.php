<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Mapel;
use App\Models\Menjawab;
use App\Models\Nilai;
use App\Models\Quiz;
use App\Models\Siswa;
use App\Models\SiswaPower;
use App\Services\QuizAdventureService;
use App\Services\QuizService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    public function __construct(
        protected QuizService $quizService,
        protected QuizAdventureService $adventureService
    ) {}

    /**
     * SISWA: Menampilkan daftar kuis petualangan yang dipublikasikan untuk kelas siswa.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $siswa = $user?->siswa;

        $kelas = $this->quizService->getSiswaPrimaryKelas($siswa);
        $kelasIds = $this->quizService->getSiswaKelasIds($siswa);

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
            $query->whereHas('pengampuKelas', fn ($q) => $q->whereIn('kelas_id', $kelasIds));
        }

        $allQuizzes = $query->get();
        $dbMapels = Mapel::all();

        $planets = $this->adventureService->buildPlanetsCollection($allQuizzes, $dbMapels);

        // Cek apakah siswa sedang memilih planet tertentu
        $selectedPlanet = null;
        if ($request->filled('mapel_id') || $request->filled('planet')) {
            $searchId = $request->input('mapel_id');
            $searchKey = strtolower($request->input('planet', ''));

            $selectedPlanet = $planets->first(function ($p) use ($searchId, $searchKey) {
                if ($searchId && $p->id == $searchId) {
                    return true;
                }
                if ($searchKey && ($p->key === $searchKey || strtolower($p->nama) === $searchKey)) {
                    return true;
                }
                return false;
            }) ?? $planets->first();
        }

        $topThree = $this->adventureService->getTopThreeLeaderboard();
        $top1 = $topThree->get(0);
        $top2 = $topThree->get(1);
        $top3 = $topThree->get(2);

        // Jika planet dipilih: Tampilkan Mode 2 (Tahapan Penyelamatan & Target)
        if ($selectedPlanet) {
            $quizzesInPlanet = $allQuizzes->filter(
                fn (Quiz $q) => $this->adventureService->isQuizMatchingPlanet(
                    $q,
                    $selectedPlanet->id,
                    $selectedPlanet->key,
                    (string) ($selectedPlanet->nama ?? '')
                )
            )->values();

            $zonas = $this->adventureService->buildPlanetZonas($quizzesInPlanet, $siswa);

            $completedCount = $zonas->where('is_completed', true)->count();
            $progresSektor = $zonas->isNotEmpty()
                ? (int) round(($completedCount / $zonas->count()) * 100)
                : 0;

            $energi = $siswa
                ? (int) SiswaPower::where('siswa_id', $siswa->id)
                    ->whereHas('power', fn ($q) => $q->where('is_active', true))
                    ->sum('jumlah')
                : 0;

            $totalXpSiswa = $siswa
                ? (int) round((float) Nilai::where('siswa_id', $siswa->id)
                    ->whereNotNull('quiz_id')
                    ->where('status', 'selesai')
                    ->sum('nilai'))
                : 0;
            $xp = number_format($totalXpSiswa, 0, ',', '.');

            $requestedQuizId = $request->input('quiz_id');
            $activeZona = null;
            if ($requestedQuizId) {
                $activeZona = $zonas->first(fn ($z) => $z->quiz?->id == $requestedQuizId);
            }
            if (!$activeZona) {
                $activeZona = $zonas->firstWhere('status', 'belum_dikerjakan') ?? $zonas->first();
            }

            $leaderboard = $this->adventureService->getSectorLeaderboard($siswa);

            return view('siswa.kuis.index', [
                'viewMode'       => 'mission',
                'siswa'          => $siswa,
                'selectedPlanet' => $selectedPlanet,
                'planets'        => $planets,
                'zonas'          => $zonas,
                'activeZona'     => $activeZona,
                'progresSektor'  => $progresSektor,
                'energi'         => $energi,
                'xp'             => $xp,
                'leaderboard'    => $leaderboard,
                'tips'           => $selectedPlanet->tips,
                'kelas'          => $kelas,
                'top1'           => $top1,
                'top2'           => $top2,
                'top3'           => $top3,
            ]);
        }

        // Tampilan Mode 1 (Pilihan Planet)
        return view('siswa.kuis.index', [
            'viewMode' => 'planets',
            'siswa'    => $siswa,
            'planets'  => $planets,
            'kelas'    => $kelas,
            'top1'     => $top1,
            'top2'     => $top2,
            'top3'     => $top3,
        ]);
    }

    /**
     * SISWA: Menampilkan panduan dan penjelasan lengkap sistem Kuis Petualangan.
     */
    public function panduan()
    {
        $siswa = auth()->user()?->siswa;
        abort_unless($siswa, 403, 'Akses ditolak.');

        Item::ensureDefaultCatalog();
        $powers = Item::powers()->where('is_active', true)->get();

        return view('siswa.kuis.panduan', compact('siswa', 'powers'));
    }

    public function siswaIndex(Request $request)
    {
        return $this->index($request);
    }

    /**
     * SISWA: Halaman peta petualangan dan pemilihan level kuis sebelum mulai pengerjaan.
     * Menampilkan perjalanan level secara berurutan dari Mudah -> Sedang -> Sulit sesuai konfigurasi Guru.
     */
    public function show(Quiz $quiz)
    {
        $siswa = $this->resolveAuthorizedSiswa($quiz);
        abort_unless($quiz->isPublished(), 403, 'Kuis belum dipublikasikan.');

        if (!app()->runningUnitTests()) {
            SiswaPower::initializeDefaultInventory($siswa);
        }

        $quiz->load([
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.guruMapel.guru',
            'pengampuKelas.kelas',
            'soals',
        ]);

        $mapel = $quiz->pengampuKelas?->guruMapel?->mapel;
        $guru = $quiz->pengampuKelas?->guruMapel?->guru;
        $kelas = $quiz->pengampuKelas?->kelas;

        $journey = $this->adventureService->buildQuizAdventureJourney($quiz, $siswa);

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
        $totalBintang = $journey['total_bintang'];
        $maxBintang = $journey['max_bintang'];

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
            'isAllCompleted',
            'totalBintang',
            'maxBintang'
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
        $siswa = $this->resolveAuthorizedSiswa($quiz);
        abort_unless($quiz->isPublished(), 403, 'Kuis belum dipublikasikan.');

        if (!app()->runningUnitTests()) {
            SiswaPower::initializeDefaultInventory($siswa);
        }

        if ($quiz->waktu_mulai && now()->lessThan($quiz->waktu_mulai)) {
            return redirect()->route('siswa.kuis.show', $quiz->id)
                ->with('error', 'Kuis belum dimulai. Jadwal mulai: ' . $quiz->waktu_mulai->translatedFormat('d F Y, H:i') . ' WIB.');
        }

        if ($quiz->waktu_selesai && now()->greaterThan($quiz->waktu_selesai)) {
            return redirect()->route('siswa.kuis.show', $quiz->id)
                ->with('error', 'Waktu pengerjaan kuis telah berakhir pada: ' . $quiz->waktu_selesai->translatedFormat('d F Y, H:i') . ' WIB.');
        }

        $quiz->load([
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.guruMapel.guru',
            'soals',
        ]);

        $journey = $this->adventureService->buildQuizAdventureJourney($quiz, $siswa);
        $levelsData = collect($journey['levels']);

        if ($levelsData->isEmpty()) {
            return redirect()->route('siswa.kuis.show', $quiz->id)
                ->with('warning', 'Guru belum menambahkan soal pada kuis ini.');
        }

        $targetLevel = $this->adventureService->resolveTargetLevel(
            $levelsData,
            request('arc'),
            $this->quizService->parseLevelNumber(request('level', '0')),
            (int) request('step', 0),
            true
        );

        if ($targetLevel['status'] === 'terkunci') {
            return redirect()->route('siswa.kuis.show', $quiz->id)
                ->with('warning', "Selesaikan level sebelumnya terlebih dahulu untuk membuka {$targetLevel['full_nama']}.");
        }

        $soals = $targetLevel['soals'];
        $selectedArc = $targetLevel['arc'];
        $selectedLevel = $targetLevel['level_num'];
        $selectedStep = $targetLevel['step'];

        foreach ($soals as $soal) {
            $soal->pilihan_shuffled = $this->quizService->getShuffledOptionsForSoal($quiz, $soal);
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
        $siswa = $this->resolveAuthorizedSiswa($quiz, 'Akses ditolak.');
        abort_unless($quiz->isPublished(), 403, 'Kuis belum dipublikasikan.');

        if ($quiz->waktu_selesai && now()->greaterThan($quiz->waktu_selesai->copy()->addMinutes(2))) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Waktu pengerjaan kuis telah kedaluwarsa.',
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
                    $isBenar = $this->quizService->isAnswerCorrect($soal, $jawabanUser);

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
        $siswa = $this->resolveAuthorizedSiswa($quiz, 'Akses ditolak.');
        abort_unless($quiz->isPublished(), 403, 'Kuis belum dipublikasikan.');

        if ($quiz->waktu_selesai && now()->greaterThan($quiz->waktu_selesai->copy()->addMinutes(2))) {
            return redirect()->route('siswa.kuis.show', $quiz->id)
                ->with('error', 'Waktu pengumpulan kuis telah kedaluwarsa.');
        }

        $quiz->load('soals');
        $journeyBefore = $this->adventureService->buildQuizAdventureJourney($quiz, $siswa);
        $levelsBefore = collect($journeyBefore['levels']);

        $targetLevel = $this->adventureService->resolveTargetLevel(
            $levelsBefore,
            $request->input('arc'),
            $this->quizService->parseLevelNumber($request->input('level', '0')),
            (int) $request->input('step', 0),
            true
        );

        if (!$targetLevel) {
            return redirect()->route('siswa.kuis.show', $quiz->id);
        }

        $soalsSession = $targetLevel['soals'];
        $koinBaruDidapat = 0;

        DB::transaction(function () use ($soalsSession, $request, $siswa, &$koinBaruDidapat) {
            foreach ($soalsSession as $soal) {
                $existingMenjawab = Menjawab::with('power')
                    ->where('soal_id', $soal->id)
                    ->where('siswa_id', $siswa->id)
                    ->first();

                $jawabanUser = $request->input("jawaban.{$soal->id}");

                if ($jawabanUser === null || $jawabanUser === '') {
                    if ($existingMenjawab && $existingMenjawab->jawaban_dipilih !== null && $existingMenjawab->jawaban_dipilih !== '') {
                        $jawabanUser = $existingMenjawab->jawaban_dipilih;
                    }
                }

                if ($jawabanUser === null || $jawabanUser === '') {
                    $jawabanUser = '-';
                }

                $isBenar = $this->quizService->isAnswerCorrect($soal, $jawabanUser);
                $power = $existingMenjawab?->power;
                $powerId = $existingMenjawab?->power_id;
                $bobotSoal = (float) ($soal->bobot ?: 10);
                $poinDiperoleh = Menjawab::hitungPoin($bobotSoal, $isBenar, $power);

                $sudahKlaimKoin = (bool) ($existingMenjawab?->koin_diklaim ?? false);
                $klaimSekarang = false;

                if (!$sudahKlaimKoin && $poinDiperoleh > 0) {
                    $koinBaruDidapat += (int) round($poinDiperoleh);
                    $klaimSekarang = true;
                }

                Menjawab::updateOrCreate(
                    [
                        'soal_id'  => $soal->id,
                        'siswa_id' => $siswa->id,
                    ],
                    [
                        'jawaban_dipilih' => is_array($jawabanUser) ? json_encode($jawabanUser) : (string) $jawabanUser,
                        'benar'           => $isBenar,
                        'power_id'        => $powerId,
                        'poin_diperoleh'  => $poinDiperoleh,
                        'koin_diklaim'    => $sudahKlaimKoin || $klaimSekarang,
                    ]
                );
            }

            if ($koinBaruDidapat > 0) {
                $siswa->increment('koin', $koinBaruDidapat);
            }
        });

        $journeyAfter = $this->adventureService->buildQuizAdventureJourney($quiz, $siswa);
        $durasiDetik = $this->quizService->parseDurationToSeconds($request->input('durasi', 0));

        $durasiSessionKey = "quiz_durasi_{$quiz->id}_{$siswa->id}";
        $existingNilai = Nilai::where('siswa_id', $siswa->id)->where('quiz_id', $quiz->id)->first();
        $prevDurasi = (int) ($existingNilai?->durasi ?? session($durasiSessionKey, 0));
        $totalDurasi = $prevDurasi + $durasiDetik;

        $allQuizSoals = $quiz->soals;
        $totalAllSoal = $allQuizSoals->count();
        $answeredCount = Menjawab::where('siswa_id', $siswa->id)
            ->whereIn('soal_id', $allQuizSoals->pluck('id'))
            ->whereNotNull('jawaban_dipilih')
            ->where('jawaban_dipilih', '!=', '')
            ->count();

        $isSemuaSoalDikerjakan = ($totalAllSoal > 0 && $answeredCount >= $totalAllSoal) || $journeyAfter['is_all_completed'];

        if ($isSemuaSoalDikerjakan) {
            $allQuizSoalIds = $allQuizSoals->pluck('id')->toArray();
            $totalBobot = (float) ($allQuizSoals->sum('bobot') ?: 100);
            $skorDidapatRaw = 0.0;

            $menjawabSemua = Menjawab::with('power')
                ->where('siswa_id', $siswa->id)
                ->whereIn('soal_id', $allQuizSoalIds)
                ->get()
                ->keyBy('soal_id');

            foreach ($allQuizSoals as $soal) {
                $bobot = (float) ($soal->bobot ?: 10);
                $ans = $menjawabSemua->get($soal->id);
                if ($ans) {
                    if ($ans->power_id !== null || (float) $ans->poin_diperoleh != 0.0 || !$ans->benar) {
                        $skorDidapatRaw += (float) $ans->poin_diperoleh;
                    } else {
                        $skorDidapatRaw += $bobot;
                    }
                }
            }

            $totalPoinSiswa = max(0.0, $skorDidapatRaw);
            $nilaiAkhir = $totalBobot > 0 ? min(100, round(($totalPoinSiswa / $totalBobot) * 100, 2)) : 100;

            Nilai::updateOrCreate(
                [
                    'siswa_id' => $siswa->id,
                    'quiz_id'  => $quiz->id,
                ],
                [
                    'nilai'  => $nilaiAkhir,
                    'durasi' => $totalDurasi,
                    'status' => 'selesai',
                ]
            );

            session()->forget($durasiSessionKey);

            if (!$existingNilai || $existingNilai->status !== 'selesai') {
                $this->quizService->notifyGuruQuizCompleted($quiz, $siswa, $user?->name);
            }
        } else {
            session([$durasiSessionKey => $totalDurasi]);
        }

        if ($isSemuaSoalDikerjakan) {
            return redirect()->route('siswa.kuis.hasil-petualangan', $quiz->id)
                ->with('success', "Selamat! Seluruh soal petualangan kuis {$quiz->judul} telah berhasil kamu selesaikan!");
        }

        $successMsg = "Level {$targetLevel['level_num']} pada Arc {$targetLevel['arc']} berhasil diselesaikan!";
        if ($koinBaruDidapat > 0) {
            $successMsg .= " Kamu mendapatkan +{$koinBaruDidapat} Koin Petualangan!";
        }

        return redirect()->route('siswa.kuis.hasil', [
            'quiz'  => $quiz->id,
            'arc'   => $targetLevel['arc'],
            'level' => $targetLevel['level_num'],
        ])->with('success', $successMsg);
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
        $siswa = $this->resolveAuthorizedSiswa($quiz, 'Akses ditolak.');

        $quiz->load([
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.guruMapel.guru',
            'pengampuKelas.kelas',
            'soals',
        ]);

        $journey = $this->adventureService->buildQuizAdventureJourney($quiz, $siswa);
        $levelsData = collect($journey['levels']);
        $nilai = $journey['nilai'];

        $reqArc = request('arc');
        $reqLevelNum = $this->quizService->parseLevelNumber(request('level', '0'));
        $reqStep = (int) request('step', 0);

        $currentLevel = $this->adventureService->resolveTargetLevel(
            $levelsData,
            $reqArc,
            $reqLevelNum,
            $reqStep,
            false
        );

        if (!$currentLevel) {
            $currentLevel = $levelsData->where('status', 'selesai')->last();
        }

        if (!$currentLevel && !$nilai) {
            return redirect()->route('siswa.kuis.show', $quiz->id)
                ->with('warning', 'Kamu belum menyelesaikan level pada kuis ini.');
        }

        $nextLevel = null;
        $isNewArcUnlocked = false;
        if ($currentLevel) {
            $nextLevel = $levelsData->firstWhere('step', $currentLevel['step'] + 1);
            if ($nextLevel && $nextLevel['arc'] !== $currentLevel['arc']) {
                $isNewArcUnlocked = true;
            }
        }

        $showAllSoals = request()->boolean('all') || (!$reqArc && !$reqLevelNum && !$reqStep && $journey['is_all_completed']);

        if ($showAllSoals || !$currentLevel) {
            $soals = $quiz->soals()
                ->with(['menjawabs' => fn ($q) => $q->with('power')->where('siswa_id', $siswa->id)])
                ->orderBy('urutan')
                ->orderBy('id')
                ->get();
        } else {
            $soals = $quiz->soals()
                ->whereIn('id', $currentLevel['soal_ids'])
                ->with(['menjawabs' => fn ($q) => $q->with('power')->where('siswa_id', $siswa->id)])
                ->orderBy('urutan')
                ->orderBy('id')
                ->get();
        }

        $totalSoal = max(1, $soals->count());
        $totalBenar = $soals->filter(fn ($s) => $s->menjawabs->first()?->benar)->count();
        $totalSalah = max(0, $soals->count() - $totalBenar);
        $skorDitampilkan = $currentLevel && !$showAllSoals
            ? $currentLevel['skor_level']
            : ($nilai && $nilai->status === 'selesai' ? round($nilai->nilai) : (int) round(($totalBenar / $totalSoal) * 100));

        $bintangDitampilkan = Quiz::hitungBintang((float) $skorDitampilkan);

        return view('siswa.kuis.hasil', [
            'quiz'               => $quiz,
            'nilai'              => $nilai,
            'soals'              => $soals,
            'totalSoal'          => $soals->count(),
            'totalBenar'         => $totalBenar,
            'totalSalah'         => $totalSalah,
            'skorDitampilkan'    => $skorDitampilkan,
            'bintangDitampilkan' => $bintangDitampilkan,
            'currentLevel'       => $currentLevel,
            'nextLevel'          => $nextLevel,
            'isNewArcUnlocked'   => $isNewArcUnlocked,
            'journey'            => $journey,
            'showAllSoals'       => $showAllSoals,
        ]);
    }

    public function siswaHasil(Quiz $quiz)
    {
        return $this->hasil($quiz);
    }

    /**
     * SISWA: Halaman Hasil Petualangan Lengkap.
     * Menampilkan gabungan nilai akhir dari seluruh soal di kuis petualangan tersebut,
     * rekapitulasi bintang 1-3 penanda nilai, statistik performa, dan rincian seluruh soal.
     */
    public function hasilPetualangan(Quiz $quiz)
    {
        $siswa = $this->resolveAuthorizedSiswa($quiz, 'Akses ditolak.');

        $quiz->load([
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.guruMapel.guru',
            'pengampuKelas.kelas',
            'soals',
        ]);

        $journey = $this->adventureService->buildQuizAdventureJourney($quiz, $siswa);
        $levelsData = collect($journey['levels']);
        $nilai = $journey['nilai'];

        $allSoalsRaw = $quiz->soals()
            ->with(['menjawabs' => fn ($q) => $q->with('power')->where('siswa_id', $siswa->id)])
            ->get();

        $allSoals = $this->adventureService->sortSoalsByAdventureOrder($allSoalsRaw);

        $totalSoal = $allSoals->count();
        $totalBenar = $allSoals->filter(fn ($s) => $s->menjawabs->first()?->benar)->count();
        $totalSalah = max(0, $totalSoal - $totalBenar);

        $totalPoinDidapat = 0.0;
        $totalBobotSemuaSoal = 0.0;
        $usedPowersCount = 0;
        $bonusPowerPoints = 0.0;
        $penaltyPowerPoints = 0.0;

        foreach ($allSoals as $sItem) {
            $bobotDasar = (float) ($sItem->bobot ?: 10);
            $totalBobotSemuaSoal += $bobotDasar;
            $jwb = $sItem->menjawabs->first();

            if ($jwb) {
                $poin = (float) $jwb->poin_diperoleh;
                if ($jwb->power_id !== null || $poin != 0.0 || !$jwb->benar) {
                    $totalPoinDidapat += $poin;
                } else {
                    $totalPoinDidapat += $bobotDasar;
                }

                if ($jwb->power_id && $jwb->power) {
                    $usedPowersCount++;
                    if ($jwb->benar && $poin > $bobotDasar) {
                        $bonusPowerPoints += ($poin - $bobotDasar);
                    } elseif (!$jwb->benar && $poin < 0) {
                        $penaltyPowerPoints += abs($poin);
                    }
                }
            }
        }

        $totalPoinDidapat = max(0.0, $totalPoinDidapat);
        $totalBobotKuis = $totalBobotSemuaSoal > 0 ? $totalBobotSemuaSoal : 100.0;

        $nilaiGabungan = $nilai && $nilai->status === 'selesai'
            ? (float) $nilai->nilai
            : min(100.0, round(($totalPoinDidapat / $totalBobotKuis) * 100, 2));

        $bintangAkhir = Quiz::hitungBintang($nilaiGabungan);

        $durasiDetik = (int) ($nilai?->durasi ?? 0);
        $menit = floor($durasiDetik / 60);
        $detik = $durasiDetik % 60;
        $durasiFormatted = sprintf('%02d Menit %02d Detik', $menit, $detik);

        return view('siswa.kuis.hasil_petualangan', [
            'quiz'               => $quiz,
            'nilai'              => $nilai,
            'soals'              => $allSoals,
            'totalSoal'          => $totalSoal,
            'totalBenar'         => $totalBenar,
            'totalSalah'         => $totalSalah,
            'nilaiGabungan'      => $nilaiGabungan,
            'bintangAkhir'       => $bintangAkhir,
            'totalPoinDidapat'   => $totalPoinDidapat,
            'totalBobotKuis'     => $totalBobotKuis,
            'durasiFormatted'    => $durasiFormatted,
            'journey'            => $journey,
            'levelsData'         => $levelsData,
            'usedPowersCount'    => $usedPowersCount,
            'bonusPowerPoints'   => $bonusPowerPoints,
            'penaltyPowerPoints' => $penaltyPowerPoints,
        ]);
    }

    /**
     * Memastikan user yang login memiliki profil Siswa dan berhak mengakses kelas kuis.
     */
    private function resolveAuthorizedSiswa(Quiz $quiz, string $missingSiswaMessage = 'Akses khusus siswa.'): Siswa
    {
        $siswa = auth()->user()?->siswa;
        abort_unless($siswa, 403, $missingSiswaMessage);

        abort_unless(
            $this->quizService->isSiswaAuthorizedForQuiz($quiz, $siswa),
            403,
            'Akses ditolak: Kuis ini bukan untuk kelas Anda.'
        );

        return $siswa;
    }
}
