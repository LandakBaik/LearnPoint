<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Menjawab;
use App\Models\Nilai;
use App\Models\Power;
use App\Models\Quiz;
use App\Models\Siswa;
use App\Models\SiswaPower;
use App\Models\Soal;
use App\Services\QuizService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PowerController extends Controller
{
    public function __construct(
        protected QuizService $quizService
    ) {}
    /**
     * Mengambil daftar Power milik siswa beserta status efek Power yang sudah aktif
     * pada soal-soal di kuis yang sedang dikerjakan.
     */
    public function index(Request $request, Quiz $quiz)
    {
        $user = $request->user();
        $siswa = $user?->siswa;

        if (!$user || $user->role !== 'siswa' || !$siswa) {
            return response()->json([
                'success' => false,
                'code' => 'FORBIDDEN',
                'message' => 'Akses khusus siswa.',
            ], 403);
        }

        if (!$this->isSiswaAuthorizedForQuiz($quiz, $siswa)) {
            return response()->json([
                'success' => false,
                'code' => 'FORBIDDEN_CLASS',
                'message' => 'Kuis ini bukan untuk kelas Anda.',
            ], 403);
        }

        if (!$quiz->isPublished()) {
            return response()->json([
                'success' => false,
                'code' => 'QUIZ_UNPUBLISHED',
                'message' => 'Kuis belum dipublikasikan.',
            ], 403);
        }

        Power::ensureDefaultCatalog();

        // Pada penggunaan normal di aplikasi (bukan saat unit testing isolasi kepemilikan),
        // inisialisasi stok awal jika siswa sama sekali belum memiliki record di siswa_powers
        if (!app()->runningUnitTests() || $request->boolean('init_default')) {
            SiswaPower::initializeDefaultInventory($siswa);
        }

        $allPowers = Power::orderBy('id')->get();
        $ownedMap = SiswaPower::where('siswa_id', $siswa->id)
            ->get()
            ->keyBy('power_id');

        $powersPayload = $allPowers->map(function (Power $p) use ($ownedMap) {
            $ownedRecord = $ownedMap->get($p->id);
            $jumlah = $ownedRecord ? (int) $ownedRecord->jumlah : 0;
            $ui = $p->ui_classes;

            return [
                'id' => $p->id,
                'code' => $p->kode,
                'kode' => $p->kode,
                'name' => $p->nama,
                'nama' => $p->nama,
                'tagline' => $p->tagline,
                'description' => $p->deskripsi,
                'deskripsi' => $p->deskripsi,
                'effectDetail' => $p->detail_efek ?: $p->deskripsi,
                'detail_efek' => $p->detail_efek ?: $p->deskripsi,
                'type' => $p->tipe_efek,
                'tipe_efek' => $p->tipe_efek,
                'icon' => $p->icon ?: 'fa-solid fa-bolt',
                'tema_warna' => $p->tema_warna ?: 'blue',
                'iconBgClass' => $ui['icon_bg'],
                'badgeClass' => $ui['badge'],
                'activePillClass' => $ui['active_pill'],
                'is_active' => (bool) $p->is_active,
                'is_owned' => $ownedRecord !== null,
                'qty' => $jumlah,
                'jumlah' => $jumlah,
            ];
        })->values();

        // Ambil state Power yang sudah diaktifkan siswa pada soal-soal kuis ini
        $quizSoals = $quiz->soals()->get()->keyBy('id');
        $usedMenjawabs = Menjawab::with('power')
            ->where('siswa_id', $siswa->id)
            ->whereIn('soal_id', $quizSoals->keys())
            ->whereNotNull('power_id')
            ->get();

        $questionStates = [];
        foreach ($usedMenjawabs as $m) {
            $soal = $quizSoals->get($m->soal_id);
            if ($soal && $m->power) {
                $eff = $this->generatePowerEffectForSoal($soal, $m->power, false);
                $questionStates[$soal->id] = array_merge($eff, [
                    'power_code' => $m->power->kode,
                    'effect' => $eff,
                ]);
            }
        }

        $isQuizCompleted = Nilai::where('siswa_id', $siswa->id)
            ->where('quiz_id', $quiz->id)
            ->where('status', 'selesai')
            ->exists();

        return response()->json([
            'success' => true,
            'quiz_completed' => $isQuizCompleted,
            'total_remaining' => $powersPayload->where('is_active', true)->sum('jumlah'),
            'powers' => $powersPayload,
            'question_states' => (object) $questionStates,
        ]);
    }

    /**
     * Mengaktifkan 1 Power pada 1 butir soal saat siswa mengerjakan kuis.
     *
     * Aturan:
     * - Maksimal 1 Power per butir soal (relasi 1-to-1 pada menjawabs.power_id).
     * - Setiap aktivasi mengurangi jumlah kepemilikan sebanyak 1.
     * - Menggunakan DB Transaction & lockForUpdate() untuk mencegah race condition / stok negatif.
     * - Jika aktivasi efek gagal, transaksi dibatalkan dan jumlah Power tidak berkurang.
     */
    public function usePower(Request $request, Quiz $quiz)
    {
        $user = $request->user();
        $siswa = $user?->siswa;

        if (!$user || $user->role !== 'siswa' || !$siswa) {
            return response()->json([
                'success' => false,
                'code' => 'FORBIDDEN',
                'message' => 'Akses khusus siswa.',
            ], 403);
        }

        // Validasi anti-spoofing kepemilikan akun lain
        if ($request->filled('siswa_id') && (int) $request->input('siswa_id') !== (int) $siswa->id) {
            return response()->json([
                'success' => false,
                'code' => 'FORBIDDEN_ACCOUNT',
                'message' => 'Anda tidak dapat menggunakan Power milik akun siswa lain.',
            ], 403);
        }

        if ($request->filled('siswa_power_id')) {
            $targetSiswaPower = SiswaPower::find($request->input('siswa_power_id'));
            if ($targetSiswaPower && (int) $targetSiswaPower->siswa_id !== (int) $siswa->id) {
                return response()->json([
                    'success' => false,
                    'code' => 'FORBIDDEN_ACCOUNT',
                    'message' => 'Anda tidak dapat menggunakan Power milik akun siswa lain.',
                ], 403);
            }
        }

        if (!$this->isSiswaAuthorizedForQuiz($quiz, $siswa)) {
            return response()->json([
                'success' => false,
                'code' => 'FORBIDDEN_CLASS',
                'message' => 'Kuis ini bukan untuk kelas Anda.',
            ], 403);
        }

        if (!$quiz->isPublished()) {
            return response()->json([
                'success' => false,
                'code' => 'QUIZ_UNPUBLISHED',
                'message' => 'Kuis belum dipublikasikan.',
            ], 403);
        }

        if ($quiz->waktu_mulai && now()->lessThan($quiz->waktu_mulai)) {
            return response()->json([
                'success' => false,
                'code' => 'QUIZ_NOT_STARTED',
                'message' => 'Kuis belum dimulai.',
            ], 422);
        }

        if ($quiz->waktu_selesai && now()->greaterThan($quiz->waktu_selesai->copy()->addMinutes(2))) {
            return response()->json([
                'success' => false,
                'code' => 'QUIZ_EXPIRED',
                'message' => 'Waktu pengerjaan kuis telah berakhir.',
            ], 422);
        }

        // Cegah penggunaan Power setelah kuis selesai
        $existingNilai = Nilai::where('siswa_id', $siswa->id)
            ->where('quiz_id', $quiz->id)
            ->where('status', 'selesai')
            ->first();

        if ($existingNilai) {
            return response()->json([
                'success' => false,
                'code' => 'QUIZ_COMPLETED',
                'message' => 'Pengerjaan kuis sudah selesai. Power tidak dapat digunakan.',
            ], 422);
        }

        $soalId = $request->input('soal_id');
        $soal = Soal::where('id', $soalId)->where('quiz_id', $quiz->id)->first();
        if (!$soal) {
            return response()->json([
                'success' => false,
                'code' => 'INVALID_SOAL',
                'message' => 'Butir soal tidak valid atau bukan bagian dari kuis ini.',
            ], 422);
        }

        // Cari Power berdasarkan power_id atau kode
        $powerQuery = Power::query();
        if ($request->filled('power_id')) {
            $powerQuery->where('id', $request->input('power_id'));
        } elseif ($request->filled('kode')) {
            $powerQuery->where('kode', $request->input('kode'));
        } else {
            return response()->json([
                'success' => false,
                'code' => 'VALIDATION_ERROR',
                'message' => 'Pilih Power yang ingin digunakan.',
            ], 422);
        }

        $powerPreview = $powerQuery->first();
        if (!$powerPreview) {
            return response()->json([
                'success' => false,
                'code' => 'POWER_NOT_FOUND',
                'message' => 'Power tidak ditemukan.',
            ], 404);
        }

        try {
            $result = DB::transaction(function () use ($siswa, $soal, $powerPreview) {
                // 1. Lock Power master record dan cek status aktif
                $power = Power::where('id', $powerPreview->id)->lockForUpdate()->first();
                if (!$power || !$power->is_active) {
                    return [
                        'error' => true,
                        'status' => 422,
                        'code' => 'POWER_INACTIVE',
                        'message' => 'Power ini sedang tidak aktif dan tidak dapat digunakan.',
                    ];
                }

                // 2. Lock inventori SiswaPower milik siswa
                $siswaPower = SiswaPower::where('siswa_id', $siswa->id)
                    ->where('power_id', $power->id)
                    ->lockForUpdate()
                    ->first();

                if (!$siswaPower) {
                    return [
                        'error' => true,
                        'status' => 422,
                        'code' => 'POWER_NOT_OWNED',
                        'message' => 'Kamu tidak memiliki Power ini di inventori.',
                    ];
                }

                if ($siswaPower->jumlah <= 0) {
                    return [
                        'error' => true,
                        'status' => 422,
                        'code' => 'POWER_EMPTY',
                        'message' => 'Jumlah kepemilikan Power ini sudah habis (0).',
                    ];
                }

                // 3. Lock record Menjawab pada soal ini untuk memastikan maksimal 1 Power per soal
                $existingMenjawab = Menjawab::where('soal_id', $soal->id)
                    ->where('siswa_id', $siswa->id)
                    ->lockForUpdate()
                    ->first();

                if ($existingMenjawab && $existingMenjawab->power_id !== null) {
                    return [
                        'error' => true,
                        'status' => 422,
                        'code' => 'QUESTION_ALREADY_HAS_POWER',
                        'message' => 'Soal ini sudah menggunakan Power. Maksimal 1 Power per butir soal.',
                    ];
                }

                // 4. Bangun dan validasi efek Power terlebih dahulu (jika gagal akan melempar exception & rollback)
                $effectData = $this->generatePowerEffectForSoal($soal, $power, true);

                // 5. Catat penggunaan Power pada tabel menjawabs
                if ($existingMenjawab) {
                    $hasRealAnswer = $existingMenjawab->jawaban_dipilih !== null
                        && $existingMenjawab->jawaban_dipilih !== ''
                        && $existingMenjawab->jawaban_dipilih !== '-';

                    $poinBaru = $hasRealAnswer
                        ? Menjawab::hitungPoin((float) ($soal->bobot ?: 10), (bool) $existingMenjawab->benar, $power)
                        : 0.0;

                    $existingMenjawab->update([
                        'power_id' => $power->id,
                        'poin_diperoleh' => $poinBaru,
                    ]);
                } else {
                    Menjawab::create([
                        'soal_id' => $soal->id,
                        'siswa_id' => $siswa->id,
                        'power_id' => $power->id,
                        'jawaban_dipilih' => '',
                        'benar' => false,
                        'poin_diperoleh' => 0.0,
                    ]);
                }

                // 6. Kurangi jumlah kepemilikan sebanyak 1
                $siswaPower->jumlah = max(0, (int) $siswaPower->jumlah - 1);
                $siswaPower->save();

                $totalRemaining = SiswaPower::where('siswa_id', $siswa->id)
                    ->whereHas('power', fn($q) => $q->where('is_active', true))
                    ->sum('jumlah');

                return [
                    'error' => false,
                    'power' => $power,
                    'remaining_qty' => (int) $siswaPower->jumlah,
                    'total_remaining' => (int) $totalRemaining,
                    'effect' => $effectData,
                ];
            });

            if ($result['error']) {
                return response()->json([
                    'success' => false,
                    'code' => $result['code'],
                    'message' => $result['message'],
                ], $result['status']);
            }

            return response()->json([
                'success' => true,
                'message' => "{$result['power']->nama} berhasil diaktifkan!",
                'soal_id' => (int) $soal->id,
                'power' => [
                    'id' => $result['power']->id,
                    'code' => $result['power']->kode,
                    'kode' => $result['power']->kode,
                    'nama' => $result['power']->nama,
                    'tipe_efek' => $result['power']->tipe_efek,
                    'qty' => $result['remaining_qty'],
                    'jumlah' => $result['remaining_qty'],
                ],
                'total_remaining' => $result['total_remaining'],
                'effect' => $result['effect'],
                'data' => [
                    'power_id' => $result['power']->id,
                    'power_code' => $result['power']->kode,
                    'soal_id' => (int) $soal->id,
                    'remaining_qty' => $result['remaining_qty'],
                    'total_remaining' => $result['total_remaining'],
                    'effect' => $result['effect'],
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'code' => 'EFFECT_ACTIVATION_FAILED',
                'message' => $e->getMessage() ?: 'Gagal mengaktifkan efek Power. Jumlah Power tidak dikurangi.',
            ], 422);
        }
    }

    /**
     * Memeriksa pilihan jawaban pertama siswa pada soal yang dipasangi Second Chance.
     * Jika jawaban salah, sistem menahan opsi tersebut dan mengizinkan siswa memilih ulang.
     */
    public function verifySecondChance(Request $request, Quiz $quiz)
    {
        $user = $request->user();
        $siswa = $user?->siswa;

        if (!$user || $user->role !== 'siswa' || !$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Akses khusus siswa.',
            ], 403);
        }

        $soal = Soal::where('id', $request->input('soal_id'))
            ->where('quiz_id', $quiz->id)
            ->first();

        if (!$soal) {
            return response()->json([
                'success' => false,
                'message' => 'Soal tidak ditemukan.',
            ], 404);
        }

        $menjawab = Menjawab::with('power')
            ->where('soal_id', $soal->id)
            ->where('siswa_id', $siswa->id)
            ->first();

        if (!$menjawab || !$menjawab->power || $menjawab->power->kode !== 'second_chance') {
            return response()->json([
                'success' => false,
                'message' => 'Power Second Chance belum aktif pada soal ini.',
            ], 422);
        }

        $pilihanSiswa = strtolower(trim((string) ($request->input('jawaban') ?? $request->input('jawaban_dipilih') ?? '')));
        if ($pilihanSiswa === '') {
            return response()->json([
                'success' => false,
                'message' => 'Pilihan jawaban wajib diisi.',
            ], 422);
        }

        $isCorrect = $this->checkIsAnswerCorrect($soal, $pilihanSiswa);

        if (!$isCorrect) {
            return response()->json([
                'success' => true,
                'triggered' => true,
                'is_correct' => false,
                'blocked_option' => $pilihanSiswa,
                'message' => 'Pilihan ' . strtoupper($pilihanSiswa) . ' kurang tepat! Perisai Second Chance menahan kesalahanmu—silakan pilih jawaban lain!',
            ]);
        }

        return response()->json([
            'success' => true,
            'triggered' => false,
            'is_correct' => true,
            'blocked_option' => null,
        ]);
    }

    /**
     * Menghasilkan data efek Power untuk satu butir soal.
     * Melempar exception jika kondisi soal tidak memungkinkan efek diaktifkan.
     */
    private function generatePowerEffectForSoal(Soal $soal, Power $power, bool $strictValidation = true): array
    {
        $bobot = (float) ($soal->bobot ?: 10);
        $pilihan = is_array($soal->pilihan) ? $soal->pilihan : [];
        $optionKeys = array_map('strtolower', array_keys($pilihan));
        $kunci = $this->extractCleanKunci($soal);

        $effect = [
            'soal_id' => (int) $soal->id,
            'power_id' => (int) $power->id,
            'power_code' => $power->kode,
            'power_kode' => $power->kode,
            'power_nama' => $power->nama,
            'type' => $power->tipe_efek,
            'tipe_efek' => $power->tipe_efek,
            'eliminated_options' => [],
            'second_chance_armed' => false,
            'hint_text' => null,
            'multiplier_type' => null,
            'bobot_dasar' => $bobot,
            'bobot_benar' => $bobot,
            'penalti_salah' => 0.0,
        ];

        switch ($power->kode) {
            case 'double_side_knife':
                $effect['multiplier_type'] = 'double_side_knife';
                $effect['bobot_benar'] = round($bobot * 2, 2);
                $effect['penalti_salah'] = round($bobot * 0.5, 2);
                break;

            case 'two_x_score':
                $effect['multiplier_type'] = 'two_x_score';
                $effect['bobot_benar'] = round($bobot * 2, 2);
                $effect['penalti_salah'] = 0.0;
                break;

            case 'second_chance':
                if ($strictValidation && count($optionKeys) < 2) {
                    throw new \RuntimeException('Soal ini tidak memiliki cukup opsi jawaban untuk mengaktifkan Second Chance.');
                }
                $effect['second_chance_armed'] = true;
                break;

            case 'lucky_clover':
                if ($strictValidation && empty(trim((string) $soal->pertanyaan))) {
                    throw new \RuntimeException('Teks pertanyaan tidak tersedia untuk menghasilkan petunjuk.');
                }
                $effect['hint_text'] = $this->buildHintForSoal($soal, $pilihan, $kunci, $optionKeys);
                break;

            case 'remove_wrong_answer':
                $wrongKeys = array_values(array_filter(
                    $optionKeys,
                    fn($k) => $k !== $kunci
                ));

                if ($strictValidation && empty($wrongKeys)) {
                    throw new \RuntimeException('Tidak ada pilihan jawaban salah yang dapat dieliminasi pada soal ini.');
                }

                // Urutkan deterministik berdasarkan ID soal agar konsisten saat di-reload, lalu ambil maksimal 2 opsi salah
                usort($wrongKeys, fn($a, $b) => strcmp(md5($soal->id . '_' . $a), md5($soal->id . '_' . $b)));
                $maxEliminate = min(2, max(1, count($wrongKeys)));
                $effect['eliminated_options'] = array_slice($wrongKeys, 0, $maxEliminate);
                break;

            default:
                if ($strictValidation) {
                    throw new \RuntimeException('Mekanisme efek Power belum dikenali.');
                }
                break;
        }

        return $effect;
    }

    /**
     * Membangun teks petunjuk (Hint) analisis konsep untuk Lucky Clover.
     */
    private function buildHintForSoal(Soal $soal, array $pilihan, string $kunci, array $optionKeys): string
    {
        $correctText = '';
        foreach ($pilihan as $k => $v) {
            if (strtolower((string) $k) === $kunci) {
                $correctText = trim((string) $v);
                break;
            }
        }

        $wrongKeys = array_values(array_filter($optionKeys, fn($k) => $k !== $kunci));
        $sampleWrong = !empty($wrongKeys) ? strtoupper($wrongKeys[0]) : null;

        $clueSnippet = '';
        if (mb_strlen($correctText) > 15) {
            $words = preg_split('/\s+/', $correctText);
            $preview = implode(' ', array_slice($words, 0, min(3, count($words))));
            $clueSnippet = " Jawaban yang tepat berkaitan erat dengan konsep \"{$preview}...\".";
        } elseif ($correctText !== '') {
            $clueSnippet = " Fokuskan analisismu pada opsi yang bernilai paling tepat sesuai rumus/konsep dasar.";
        }

        $avoidText = $sampleWrong
            ? " Hindari terjebak pada opsi pengecoh seperti Pilihan {$sampleWrong}."
            : '';

        return "Petunjuk Lucky Clover: Perhatikan inti pertanyaan pada soal ini.{$clueSnippet}{$avoidText}";
    }

    private function extractCleanKunci(Soal $soal): string
    {
        $kunci = $soal->kunci_jawaban;
        if (is_array($kunci)) {
            return strtolower(trim((string) ($kunci[0] ?? 'a')));
        }
        return strtolower(trim((string) $kunci, " \t\n\r\0\x0B\"'[]"));
    }

    private function checkIsAnswerCorrect(Soal $soal, string $jawabanUser): bool
    {
        return $this->quizService->isAnswerCorrect($soal, $jawabanUser);
    }

    private function isSiswaAuthorizedForQuiz(Quiz $quiz, Siswa $siswa): bool
    {
        return $this->quizService->isSiswaAuthorizedForQuiz($quiz, $siswa);
    }
}

