<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Mapel;
use App\Models\Menjawab;
use App\Models\Nilai;
use App\Models\Notifikasi;
use App\Models\PengampuKelas;
use App\Models\Quiz;
use App\Models\Siswa;
use App\Models\SiswaPower;
use App\Models\Soal;
use App\Services\QuizAdventureService;
use App\Services\QuizService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class QuizController extends Controller
{
    public function __construct(
        protected QuizService $quizService,
        protected QuizAdventureService $adventureService
    ) {}

    /**
     * Helper to authorize Guru ownership.
     */
    protected function authorizeGuru(Quiz $quiz, $guruId)
    {
        if (!$this->quizService->isGuruOwner($quiz, $guruId ? (int) $guruId : null)) {
            abort(403, 'Akses ditolak.');
        }
    }

    /**
     * GURU: Menampilkan daftar semua kuis petualangan milik guru.
     */
    public function guruIndex(Request $request)
    {
        $guruId = $request->user()?->guru?->id ?? $request->user()?->guru_id;

        if (!$guruId) {
            return $this->notFoundResponse('Profil guru tidak ditemukan.');
        }

        $perPage = max(1, min(100, (int) $request->query('per_page', 15)));
        $quizzes = $this->quizService
            ->buildGuruQuizzesQuery((int) $guruId, $request, ['soals', 'nilais'])
            ->latest()
            ->paginate($perPage);

        return $this->successResponse([
            'statistics' => $this->quizService->getGuruQuizStatistics((int) $guruId),
            'quizzes'    => $quizzes,
        ]);
    }

    /**
     * GURU: Opsi pengampu kelas untuk kuis.
     */
    public function guruOptions(Request $request)
    {
        $guruId = $request->user()?->guru?->id ?? $request->user()?->guru_id;

        $pengampuKelases = PengampuKelas::with(['guruMapel.mapel', 'kelas'])
            ->whereHas('guruMapel', fn ($q) => $q->where('guru_id', $guruId))
            ->get()
            ->map(fn ($pk) => [
                'pengampu_kelas_id' => $pk->id,
                'kelas_id'          => $pk->kelas?->id,
                'nama_kelas'        => $pk->kelas?->nama_kelas,
                'tingkatan'         => $pk->kelas?->tingkatan,
                'mapel_id'          => $pk->guruMapel?->mapel?->id,
                'nama_mapel'        => $pk->guruMapel?->mapel?->nama_mapel,
            ]);

        return $this->successResponse($pengampuKelases);
    }

    /**
     * GURU: Detail kuis, daftar butir soal, dan progres seluruh siswa kelas.
     */
    public function guruShow(Request $request, Quiz $quiz)
    {
        $guruId = $request->user()?->guru?->id ?? $request->user()?->guru_id;
        $this->authorizeGuru($quiz, $guruId);

        $quiz->sinkronisasiBobotSoal();

        $quiz->load([
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.kelas',
            'soals',
            'nilais.siswa',
        ]);

        $activeDifficulties = $quiz->kesulitan_aktif_array;
        $levelCounts = $this->quizService->calculateLevelCounts($quiz, $activeDifficulties);

        $soals = $quiz->soals->map(fn ($s) => [
            'id'            => $s->id,
            'pertanyaan'    => $s->pertanyaan,
            'tipe_soal'     => $s->tipe_soal,
            'kesulitan'     => $s->kesulitan,
            'level'         => $s->level,
            'pilihan'       => $s->pilihan,
            'kunci_jawaban' => $s->kunci_jawaban,
            'bobot'         => $s->bobot,
            'urutan'        => $s->urutan,
            'gambar'        => $s->gambar,
            'gambar_url'    => $s->gambar ? asset('storage/' . $s->gambar) : null,
        ]);

        $nilais = $quiz->nilais->map(fn ($n) => [
            'id'         => $n->id,
            'siswa'      => [
                'id'   => $n->siswa?->id,
                'nama' => $n->siswa?->nama_siswa,
                'nis'  => $n->siswa?->nis,
            ],
            'nilai'      => $n->nilai,
            'status'     => $n->status,
            'durasi'     => $n->durasi,
            'created_at' => $n->created_at,
        ]);

        $groupQuizzes = $this->quizService->getGroupQuizzes($quiz)
            ->loadMissing('pengampuKelas.kelas')
            ->map(fn ($gq) => [
                'id'                => $gq->id,
                'judul'             => $gq->judul,
                'pengampu_kelas_id' => $gq->pengampu_kelas_id,
                'kelas'             => $gq->pengampuKelas?->kelas?->nama_kelas,
            ]);

        $daftarSiswa = $this->quizService->getClassStudentProgress($quiz);

        return $this->successResponse([
            'id'                   => $quiz->id,
            'id_grup_kuis'         => $quiz->id_grup_kuis,
            'judul'                => $quiz->judul,
            'deskripsi'            => $quiz->deskripsi,
            'status'               => $quiz->status,
            'asset'                => (int) ($quiz->asset ?? 0),
            'kesulitan_aktif'      => $activeDifficulties,
            'waktu_mulai'          => $quiz->waktu_mulai?->format('Y-m-d H:i:s'),
            'waktu_selesai'        => $quiz->waktu_selesai?->format('Y-m-d H:i:s'),
            'acak_jawaban'         => (bool) $quiz->acak_jawaban,
            'mapel'                => $quiz->pengampuKelas?->guruMapel?->mapel?->nama_mapel,
            'kelas'                => $quiz->pengampuKelas?->kelas?->nama_kelas,
            'pengampu_kelas_id'    => $quiz->pengampu_kelas_id,
            'level_counts'         => $levelCounts,
            'soals'                => $soals,
            'nilais'               => $nilais,
            'group_quizzes'        => $groupQuizzes,
            'daftar_siswa'         => $daftarSiswa,
            'has_student_activity' => $quiz->hasStudentActivity(),
        ]);
    }

    /**
     * GURU: Simpan draf kuis baru (mendukung multi-kelas).
     */
    public function guruStore(Request $request)
    {
        $guruId = $request->user()?->guru?->id ?? $request->user()?->guru_id;

        $validator = Validator::make($request->all(), [
            'judul'                => ['required', 'string', 'max:200'],
            'deskripsi'            => ['nullable', 'string', 'max:1000'],
            'pengampu_kelas_id'    => ['nullable'],
            'pengampu_kelas_ids'   => ['nullable', 'array', 'min:1'],
            'pengampu_kelas_ids.*' => ['exists:pengampu_kelas,id'],
            'kesulitan_aktif'      => ['required', 'array', 'min:1', 'max:3'],
            'kesulitan_aktif.*'    => ['string', 'in:Mudah,Sedang,Sulit'],
            'waktu_mulai'          => ['nullable', 'date', 'after_or_equal:today'],
            'waktu_selesai'        => ['nullable', 'date', 'after:waktu_mulai'],
            'acak_jawaban'         => ['nullable', 'boolean'],
            'status'               => ['nullable', 'string', 'in:draft,published'],
            'asset'                => ['nullable', 'integer', 'min:0', 'max:255'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $validated = $validator->validated();

        $targetIds = [];
        if ($request->filled('pengampu_kelas_ids')) {
            $targetIds = (array) $request->input('pengampu_kelas_ids');
        } elseif ($request->filled('pengampu_kelas_id')) {
            $targetIds = (array) $request->input('pengampu_kelas_id');
        }

        if (empty($targetIds)) {
            return $this->validationErrorResponse([
                'pengampu_kelas_id' => ['Silakan pilih minimal satu kelas sasaran untuk kuis ini.'],
            ]);
        }

        $validPengampu = $guruId ? $this->quizService->getValidPengampuIdsForGuru($targetIds, (int) $guruId) : [];

        if (empty($validPengampu)) {
            return $this->forbiddenResponse('Anda tidak memiliki hak akses pada kelas/mata pelajaran ini.');
        }

        $orderedDifficulties = $this->quizService->normalizeActiveDifficulties($validated['kesulitan_aktif']);
        $grupId = count($validPengampu) > 1 ? (string) Str::uuid() : null;

        $createdQuizzes = [];
        foreach ($validPengampu as $pkId) {
            $createdQuizzes[] = Quiz::create([
                'id_grup_kuis'      => $grupId,
                'judul'             => $validated['judul'],
                'deskripsi'         => $validated['deskripsi'] ?? null,
                'pengampu_kelas_id' => $pkId,
                'kesulitan_aktif'   => $orderedDifficulties,
                'waktu_mulai'       => $validated['waktu_mulai'] ?? null,
                'waktu_selesai'     => $validated['waktu_selesai'] ?? null,
                'acak_jawaban'      => $request->boolean('acak_jawaban'),
                'status'            => $validated['status'] ?? 'draft',
                'asset'             => (int) ($validated['asset'] ?? 0),
                'level'             => 'Level 1',
                'kesulitan'         => $orderedDifficulties[0] ?? 'Mudah',
            ]);
        }

        $firstQuiz = $createdQuizzes[0];

        return $this->createdResponse(
            count($createdQuizzes) === 1 ? $firstQuiz : $createdQuizzes,
            'Draf kuis berhasil dibuat.'
        );
    }

    /**
     * GURU: Perbarui pengaturan kuis.
     */
    public function guruUpdate(Request $request, Quiz $quiz)
    {
        $guruId = $request->user()?->guru?->id ?? $request->user()?->guru_id;
        $this->authorizeGuru($quiz, $guruId);

        $isWaktuMulaiUnchanged = false;
        if ($request->filled('waktu_mulai') && $quiz->waktu_mulai) {
            try {
                $isWaktuMulaiUnchanged = Carbon::parse($request->input('waktu_mulai'))
                    ->format('Y-m-d H:i') === $quiz->waktu_mulai->format('Y-m-d H:i');
            } catch (\Throwable $e) {
                $isWaktuMulaiUnchanged = false;
            }
        }

        $waktuMulaiRules = ['nullable', 'date'];
        if (!$isWaktuMulaiUnchanged) {
            $waktuMulaiRules[] = 'after_or_equal:today';
        }

        $validator = Validator::make($request->all(), [
            'judul'             => ['required', 'string', 'max:200'],
            'deskripsi'         => ['nullable', 'string', 'max:1000'],
            'pengampu_kelas_id' => ['required', 'exists:pengampu_kelas,id'],
            'kesulitan_aktif'   => ['required', 'array', 'min:1', 'max:3'],
            'kesulitan_aktif.*' => ['string', 'in:Mudah,Sedang,Sulit'],
            'waktu_mulai'       => $waktuMulaiRules,
            'waktu_selesai'     => ['nullable', 'date', 'after:waktu_mulai'],
            'acak_jawaban'      => ['nullable', 'boolean'],
            'status'            => ['nullable', 'string', 'in:draft,published'],
            'asset'             => ['nullable', 'integer', 'min:0', 'max:255'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $validated = $validator->validated();

        if (empty($this->quizService->getValidPengampuIdsForGuru($validated['pengampu_kelas_id'], (int) $guruId))) {
            return $this->forbiddenResponse('Anda tidak memiliki hak akses pada kelas/mata pelajaran ini.');
        }

        $orderedDifficulties = $this->quizService->normalizeActiveDifficulties($validated['kesulitan_aktif']);
        $targetStatus = $validated['status'] ?? $quiz->status;

        if ($targetStatus === 'published') {
            $publishError = $this->quizService->validateQuizBeforePublish($quiz, 'butir soal', $orderedDifficulties);
            if ($publishError !== null) {
                return $this->errorResponse($publishError, 422);
            }
        }

        $wasDraft = ($quiz->status ?? 'draft') !== 'published';

        $quiz->update([
            'judul'             => $validated['judul'],
            'deskripsi'         => $validated['deskripsi'] ?? null,
            'pengampu_kelas_id' => $validated['pengampu_kelas_id'],
            'kesulitan_aktif'   => $orderedDifficulties,
            'waktu_mulai'       => $validated['waktu_mulai'] ?? null,
            'waktu_selesai'     => $validated['waktu_selesai'] ?? null,
            'acak_jawaban'      => $request->boolean('acak_jawaban'),
            'status'            => $targetStatus,
            'asset'             => array_key_exists('asset', $validated) ? (int) $validated['asset'] : (int) ($quiz->asset ?? 0),
        ]);

        if ($wasDraft && $targetStatus === 'published') {
            $this->quizService->notifyKelasQuizPublished($quiz);
        }

        return $this->successResponse($quiz, 'Pengaturan kuis berhasil diperbarui.');
    }

    /**
     * GURU: Mengubah status kuis antara Draf dan Published.
     */
    public function guruToggleStatus(Request $request, Quiz $quiz)
    {
        $guruId = $request->user()?->guru?->id ?? $request->user()?->guru_id;
        $this->authorizeGuru($quiz, $guruId);

        if ($quiz->status === 'published') {
            $quiz->update(['status' => 'draft']);

            return $this->successResponse([
                'status' => 'draft',
            ], 'Kuis berhasil diubah menjadi draf.');
        }

        $publishError = $this->quizService->validateQuizBeforePublish($quiz, 'butir soal');
        if ($publishError !== null) {
            return $this->errorResponse($publishError, 422);
        }

        $quiz->update(['status' => 'published']);
        $this->quizService->notifyKelasQuizPublished($quiz);

        return $this->successResponse([
            'status' => 'published',
        ], 'Kuis berhasil diterbitkan untuk siswa.');
    }

    /**
     * GURU: Hapus kuis beserta butir soalnya.
     */
    public function guruDestroy(Request $request, Quiz $quiz)
    {
        $guruId = $request->user()?->guru?->id ?? $request->user()?->guru_id;
        $this->authorizeGuru($quiz, $guruId);

        $this->quizService->deleteQuizWithAssets($quiz);

        return $this->successResponse(null, 'Kuis berhasil dihapus.');
    }

    /**
     * GURU: Hapus beberapa kuis sekaligus (Bulk Destroy).
     */
    public function guruBulkDestroy(Request $request)
    {
        $guruId = $request->user()?->guru?->id ?? $request->user()?->guru_id;
        if (!$guruId) {
            return $this->forbiddenResponse('Akses ditolak.');
        }

        $validator = Validator::make($request->all(), [
            'quiz_ids'   => ['required', 'array', 'min:1'],
            'quiz_ids.*' => ['required', 'exists:quizzes,id'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $validated = $validator->validated();
        $count = $this->quizService->bulkDeleteGuruQuizzes((int) $guruId, $validated['quiz_ids']);

        if ($count === 0) {
            return $this->notFoundResponse('Tidak ada kuis valid yang dapat dihapus.');
        }

        return $this->successResponse([
            'deleted_count' => $count,
        ], "{$count} kuis berhasil dihapus.");
    }

    /**
     * GURU: Tambahkan butir soal ke dalam level tertentu pada Arc kuis (sinkron ke grup kuis).
     */
    public function guruStoreSoal(Request $request, Quiz $quiz)
    {
        $guruId = $request->user()?->guru?->id ?? $request->user()?->guru_id;
        $this->authorizeGuru($quiz, $guruId);

        $validator = Validator::make($request->all(), [
            'pertanyaan'    => ['required', 'string'],
            'tipe_soal'     => ['required', 'in:single_choice,multiple_choice,essay,matching'],
            'kesulitan'     => ['required', 'string', 'in:Mudah,Sedang,Sulit'],
            'level'         => ['required', 'string', 'in:Level 1,Level 2,Level 3,Level 4,Level 5,1,2,3,4,5'],
            'pilihan'       => ['required', 'array', 'min:2', 'max:6'],
            'kunci_jawaban' => ['required'],
            'bobot'         => ['nullable', 'numeric', 'min:1'],
            'gambar'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $validated = $validator->validated();

        $levelNum = $this->quizService->parseLevelNumber($validated['level']);
        if ($levelNum < 1 || $levelNum > Quiz::MAX_LEVEL) {
            return $this->errorResponse('Level tidak valid. Maksimal 5 level.', 422);
        }
        $levelFormatted = 'Level ' . $levelNum;
        $kesulitan = $validated['kesulitan'];

        if (!in_array($kesulitan, $quiz->kesulitan_aktif_array)) {
            return $this->errorResponse("Arc tingkat kesulitan {$kesulitan} tidak aktif pada kuis ini.", 422);
        }

        if (!$quiz->canAddSoalFor($kesulitan, $levelFormatted)) {
            return $this->errorResponse("Tingkat {$kesulitan} {$levelFormatted} sudah mencapai batas maksimal " . Quiz::MAX_SOAL . " soal.", 422);
        }

        $gambarPath = $this->quizService->handleSoalImageUpload($request);

        $pilihanFormatted = [
            'a' => trim((string) $request->input('pilihan.a', '')),
            'b' => trim((string) $request->input('pilihan.b', '')),
            'c' => trim((string) $request->input('pilihan.c', '')),
            'd' => trim((string) $request->input('pilihan.d', '')),
        ];

        $normalizedPilihan = array_map(
            static fn ($val) => mb_strtolower($val),
            array_values($pilihanFormatted)
        );
        if (count($normalizedPilihan) !== count(array_unique($normalizedPilihan))) {
            return $this->errorResponse('Pilihan jawaban tidak boleh ada yang sama.', 422);
        }

        $kunci = $validated['kunci_jawaban'];
        if (is_array($kunci)) {
            $kunci = strtolower(trim((string) ($kunci[0] ?? 'a')));
        } else {
            $kunci = strtolower(trim((string) $kunci));
        }

        $groupQuizzes = $this->quizService->getGroupQuizzes($quiz);
        $primarySoal = null;

        DB::transaction(function () use (
            $groupQuizzes,
            $quiz,
            $validated,
            $kesulitan,
            $levelFormatted,
            $pilihanFormatted,
            $kunci,
            $gambarPath,
            &$primarySoal
        ) {
            foreach ($groupQuizzes as $targetQuiz) {
                if ($targetQuiz->id !== $quiz->id && !$targetQuiz->canAddSoalFor($kesulitan, $levelFormatted)) {
                    continue;
                }

                $nextUrutan = ($targetQuiz->soalsFor($kesulitan, $levelFormatted)->max('urutan') ?? 0) + 1;

                $createdSoal = Soal::create([
                    'pertanyaan'    => $validated['pertanyaan'],
                    'tipe_soal'     => $validated['tipe_soal'],
                    'kesulitan'     => $kesulitan,
                    'level'         => $levelFormatted,
                    'pilihan'       => $pilihanFormatted,
                    'kunci_jawaban' => $kunci,
                    'bobot'         => $validated['bobot'] ?? 10,
                    'urutan'        => $nextUrutan,
                    'gambar'        => $gambarPath,
                    'quiz_id'       => $targetQuiz->id,
                    'tugas_id'      => null,
                    'ujian_id'      => null,
                ]);

                $targetQuiz->sinkronisasiBobotSoal();

                if ($targetQuiz->id === $quiz->id) {
                    $primarySoal = $createdSoal->fresh();
                }
            }
        });

        return $this->createdResponse($primarySoal, 'Soal berhasil ditambahkan.');
    }

    /**
     * GURU: Perbarui butir soal kuis (sinkron ke grup kuis).
     */
    public function guruUpdateSoal(Request $request, Quiz $quiz, Soal $soal)
    {
        $guruId = $request->user()?->guru?->id ?? $request->user()?->guru_id;
        $this->authorizeGuru($quiz, $guruId);

        if ($soal->quiz_id !== $quiz->id) {
            return $this->forbiddenResponse('Soal bukan milik kuis ini.');
        }

        $validator = Validator::make($request->all(), [
            'pertanyaan'    => ['required', 'string'],
            'tipe_soal'     => ['required', 'in:single_choice,multiple_choice,essay,matching'],
            'kesulitan'     => ['nullable', 'string', 'in:Mudah,Sedang,Sulit'],
            'level'         => ['nullable', 'string', 'in:Level 1,Level 2,Level 3,Level 4,Level 5,1,2,3,4,5'],
            'pilihan'       => ['required', 'array', 'min:2', 'max:6'],
            'kunci_jawaban' => ['required'],
            'bobot'         => ['nullable', 'numeric', 'min:1'],
            'gambar'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'hapus_gambar'  => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $validated = $validator->validated();

        $oldImage = $soal->gambar;
        $oldKesulitan = $soal->kesulitan;
        $oldLevel = $soal->level;
        $oldUrutan = $soal->urutan;
        $oldPertanyaan = $soal->pertanyaan;

        if ($request->hasFile('gambar')) {
            $gambarPath = $request->file('gambar')->store('soals', 'public');
        } elseif ($request->boolean('hapus_gambar')) {
            $gambarPath = null;
        } else {
            $gambarPath = $oldImage;
        }

        $pilihanFormatted = [
            'a' => trim((string) $request->input('pilihan.a', $soal->pilihan['a'] ?? '')),
            'b' => trim((string) $request->input('pilihan.b', $soal->pilihan['b'] ?? '')),
            'c' => trim((string) $request->input('pilihan.c', $soal->pilihan['c'] ?? '')),
            'd' => trim((string) $request->input('pilihan.d', $soal->pilihan['d'] ?? '')),
        ];

        $normalizedPilihan = array_map(
            static fn ($val) => mb_strtolower($val),
            array_values($pilihanFormatted)
        );
        if (count($normalizedPilihan) !== count(array_unique($normalizedPilihan))) {
            return $this->errorResponse('Pilihan jawaban (A, B, C, dan D) tidak boleh ada yang sama agar tidak ambigu.', 422);
        }

        $kunci = $validated['kunci_jawaban'];
        if (is_array($kunci)) {
            $kunci = strtolower(trim((string) ($kunci[0] ?? 'a')));
        } else {
            $kunci = strtolower(trim((string) $kunci));
        }

        $updateData = [
            'pertanyaan'    => $validated['pertanyaan'],
            'tipe_soal'     => $validated['tipe_soal'],
            'pilihan'       => $pilihanFormatted,
            'kunci_jawaban' => $kunci,
            'bobot'         => $validated['bobot'] ?? $soal->bobot,
            'gambar'        => $gambarPath,
        ];

        if (!empty($validated['kesulitan'])) {
            $updateData['kesulitan'] = $validated['kesulitan'];
        }

        if (!empty($validated['level'])) {
            $levelNum = $this->quizService->parseLevelNumber($validated['level']);
            $updateData['level'] = 'Level ' . $levelNum;
        }

        $groupQuizzes = $this->quizService->getGroupQuizzes($quiz);

        DB::transaction(function () use (
            $groupQuizzes,
            $quiz,
            $soal,
            $updateData,
            $oldKesulitan,
            $oldLevel,
            $oldUrutan,
            $oldPertanyaan
        ) {
            $soal->update($updateData);
            $quiz->sinkronisasiBobotSoal();

            foreach ($groupQuizzes as $otherQuiz) {
                if ($otherQuiz->id === $quiz->id) {
                    continue;
                }

                $matchingSoal = $otherQuiz->soals()
                    ->where('kesulitan', $oldKesulitan)
                    ->where('level', $oldLevel)
                    ->where(function ($q) use ($oldUrutan, $oldPertanyaan) {
                        $q->where('urutan', $oldUrutan)
                          ->orWhere('pertanyaan', $oldPertanyaan);
                    })
                    ->first();

                if ($matchingSoal) {
                    $matchingSoal->update($updateData);
                    $otherQuiz->sinkronisasiBobotSoal();
                }
            }
        });

        if ($oldImage && $oldImage !== $gambarPath) {
            $this->quizService->deleteSoalImage($oldImage);
        }

        return $this->successResponse($soal->fresh(), 'Soal berhasil diperbarui.');
    }

    /**
     * GURU: Hapus butir soal kuis (sinkron ke grup kuis).
     */
    public function guruDestroySoal(Request $request, Quiz $quiz, Soal $soal)
    {
        $guruId = $request->user()?->guru?->id ?? $request->user()?->guru_id;
        $this->authorizeGuru($quiz, $guruId);

        if ($soal->quiz_id !== $quiz->id) {
            return $this->forbiddenResponse('Soal bukan milik kuis ini.');
        }

        $arc = $soal->kesulitan ?? 'Mudah';
        $level = $soal->level ?? 'Level 1';
        $urutan = $soal->urutan;
        $pertanyaan = $soal->pertanyaan;
        $gambar = $soal->gambar;

        $groupQuizzes = $this->quizService->getGroupQuizzes($quiz);

        DB::transaction(function () use ($groupQuizzes, $quiz, $soal, $arc, $level, $urutan, $pertanyaan) {
            $soal->delete();
            $quiz->sinkronisasiBobotSoal();

            foreach ($groupQuizzes as $otherQuiz) {
                if ($otherQuiz->id === $quiz->id) {
                    continue;
                }

                $matchingSoal = $otherQuiz->soals()
                    ->where('kesulitan', $arc)
                    ->where('level', $level)
                    ->where(function ($q) use ($urutan, $pertanyaan) {
                        $q->where('urutan', $urutan)
                          ->orWhere('pertanyaan', $pertanyaan);
                    })
                    ->first();

                if ($matchingSoal) {
                    $matchingSoal->delete();
                    $otherQuiz->sinkronisasiBobotSoal();
                }
            }
        });

        $this->quizService->deleteSoalImage($gambar);

        return $this->successResponse(null, 'Soal berhasil dihapus.');
    }

    /**
     * SISWA: Menampilkan daftar kuis terpublikasi untuk kelas siswa beserta data Planet.
     */
    public function siswaIndex(Request $request)
    {
        $siswa = $request->user()?->siswa;
        $kelas = $this->quizService->getSiswaPrimaryKelas($siswa);
        $kelasId = $kelas?->id;

        if (!$kelasId) {
            return $this->notFoundResponse('Data kelas siswa tidak ditemukan.');
        }

        $query = Quiz::whereHas('pengampuKelas', fn ($q) => $q->currentPeriod()->where('kelas_id', $kelasId)->forSiswa($siswa))
            ->where('status', 'published')
            ->with([
                'pengampuKelas.guruMapel.mapel',
                'pengampuKelas.guruMapel.guru',
                'nilais' => function ($q) use ($siswa) {
                    if ($siswa) {
                        $q->where('siswa_id', $siswa->id);
                    }
                },
                'soals',
            ])
            ->withCount('soals');

        if ($request->filled('mapel_id')) {
            $query->whereHas('pengampuKelas.guruMapel', fn ($q) => $q->where('mapel_id', $request->mapel_id));
        }

        $allQuizzes = $query->latest()->get();

        $quizzes = $allQuizzes->map(function ($quiz) {
            $nilai = $quiz->nilais->first();

            return [
                'id'              => $quiz->id,
                'judul'           => $quiz->judul,
                'deskripsi'       => $quiz->deskripsi,
                'asset'           => (int) ($quiz->asset ?? 0),
                'kesulitan_aktif' => $quiz->kesulitan_aktif_array,
                'mapel_id'        => $quiz->pengampuKelas?->guruMapel?->mapel_id,
                'mapel'           => $quiz->pengampuKelas?->guruMapel?->mapel?->nama_mapel,
                'mapel_asset'     => (int) ($quiz->pengampuKelas?->guruMapel?->mapel?->asset ?? 0),
                'guru'            => $quiz->pengampuKelas?->guruMapel?->guru?->nama,
                'total_soal'      => $quiz->soals_count,
                'waktu_mulai'     => $quiz->waktu_mulai?->format('Y-m-d H:i:s'),
                'waktu_selesai'   => $quiz->waktu_selesai?->format('Y-m-d H:i:s'),
                'is_completed'    => $nilai !== null && $nilai->status === 'selesai',
                'result'          => $nilai ? [
                    'id'           => $nilai->id,
                    'nilai'        => $nilai->nilai,
                    'bintang'      => Quiz::hitungBintang((float) $nilai->nilai),
                    'status'       => $nilai->status,
                    'durasi'       => $nilai->durasi,
                    'completed_at' => $nilai->created_at,
                ] : null,
            ];
        });

        // Jika klien meminta mode petualangan (with_planets=1), sertakan planets
        if ($request->boolean('with_planets')) {
            $dbMapels = Mapel::forSiswa($siswa)
                ->whereHas('pengampuKelases', fn ($q) => $q->currentPeriod()->where('kelas_id', $kelasId))
                ->get();
            $planets = $this->adventureService->buildPlanetsCollection($allQuizzes, $dbMapels);

            return $this->successResponse([
                'quizzes' => $quizzes,
                'planets' => $planets,
            ]);
        }

        return $this->successResponse($quizzes);
    }

    /**
     * SISWA: Panduan sistem Kuis Petualangan & katalog Power aktif.
     */
    public function siswaPanduan(Request $request)
    {
        $siswa = $request->user()?->siswa;
        if (!$siswa) {
            return $this->notFoundResponse('Profil siswa tidak ditemukan.');
        }

        Item::ensureDefaultCatalog();
        $powers = Item::powers()->where('is_active', true)->orderBy('id')->get()->map(fn (Item $p) => [
            'id'          => $p->id,
            'kode'        => $p->kode,
            'nama'        => $p->nama,
            'tagline'     => $p->tagline,
            'deskripsi'   => $p->deskripsi,
            'detail_efek' => $p->detail_efek,
            'tipe_efek'   => $p->tipe_efek,
            'rarity'      => $p->rarity,
            'icon'        => $p->icon,
            'tema_warna'  => $p->tema_warna,
        ]);

        return $this->successResponse([
            'powers' => $powers,
        ]);
    }

    /**
     * SISWA: Detail kuis petualangan (Ringkasan Arc, Peta Petualangan Bertingkat & Status Pengerjaan).
     */
    public function siswaShow(Request $request, Quiz $quiz)
    {
        $siswa = $request->user()?->siswa;
        if (!$this->quizService->isSiswaAuthorizedForQuiz($quiz, $siswa)) {
            return $this->forbiddenResponse('Kuis tidak ditemukan untuk kelas Anda.');
        }

        if ($quiz->status !== 'published') {
            return $this->forbiddenResponse('Kuis belum dipublikasikan oleh guru.');
        }

        if ($siswa && !app()->runningUnitTests()) {
            SiswaPower::initializeDefaultInventory($siswa);
        }

        $quiz->load(['pengampuKelas.guruMapel.mapel', 'pengampuKelas.guruMapel.guru', 'soals']);

        $soalCountsByArc = [];
        foreach ($quiz->kesulitan_aktif_array as $arc) {
            $soalCountsByArc[$arc] = $quiz->soals->where('kesulitan', $arc)->count();
        }

        $soalIds = $quiz->soals->pluck('id');
        $answeredSoalIds = $siswa
            ? Menjawab::where('siswa_id', $siswa->id)
                ->whereIn('soal_id', $soalIds)
                ->whereNotNull('jawaban_dipilih')
                ->where('jawaban_dipilih', '!=', '')
                ->pluck('soal_id')
                ->toArray()
            : [];

        $levelsInfo = [];
        foreach (Quiz::LEVEL_LIST as $index => $lvl) {
            $soalsInLevel = $quiz->soals->where('level', $lvl);
            $count = $soalsInLevel->count();
            $answeredCount = $soalsInLevel->whereIn('id', $answeredSoalIds)->count();
            $levelsInfo[] = [
                'level_number'  => $index + 1,
                'level'         => $lvl,
                'total_soal'    => $count,
                'answered_soal' => $answeredCount,
                'is_completed'  => $count > 0 && $answeredCount >= $count,
                'is_unlocked'   => $count > 0,
            ];
        }

        $journey = $this->adventureService->buildQuizAdventureJourney($quiz, $siswa);
        $riwayatNilai = $journey['nilai'];

        // Bersihkan koleksi model `soals` dari `journey['levels']` agar payload ringkas
        $adventureLevels = collect($journey['levels'])->map(function ($lvl) {
            $copy = $lvl;
            unset($copy['soals']);
            return $copy;
        })->values();

        return $this->successResponse([
            'id'                 => $quiz->id,
            'judul'              => $quiz->judul,
            'deskripsi'          => $quiz->deskripsi,
            'asset'              => (int) ($quiz->asset ?? 0),
            'kesulitan_aktif'    => $quiz->kesulitan_aktif_array,
            'waktu_mulai'        => $quiz->waktu_mulai?->format('Y-m-d H:i:s'),
            'waktu_selesai'      => $quiz->waktu_selesai?->format('Y-m-d H:i:s'),
            'acak_jawaban'       => (bool) $quiz->acak_jawaban,
            'mapel'              => $quiz->pengampuKelas?->guruMapel?->mapel?->nama_mapel,
            'mapel_asset'        => (int) ($quiz->pengampuKelas?->guruMapel?->mapel?->asset ?? 0),
            'guru'               => $quiz->pengampuKelas?->guruMapel?->guru?->nama,
            'soal_counts_by_arc' => $soalCountsByArc,
            'levels'             => $levelsInfo,
            'adventure'          => [
                'levels'                => $adventureLevels,
                'arc_summaries'         => $journey['arc_summaries'],
                'total_levels'          => $journey['total_levels'],
                'completed_levels'      => $journey['completed_levels'],
                'remaining_levels'      => $journey['remaining_levels'],
                'percentage'            => $journey['percentage'],
                'total_bintang'         => $journey['total_bintang'],
                'max_bintang'           => $journey['max_bintang'],
                'active_step'           => $journey['active_step'],
                'default_selected_step' => $journey['default_selected_step'],
                'current_arc'           => $journey['current_arc'],
                'is_all_completed'      => $journey['is_all_completed'],
            ],
            'is_completed'       => ($riwayatNilai !== null && $riwayatNilai->status === 'selesai') || $journey['is_all_completed'],
            'result'             => $riwayatNilai ? [
                'id'           => $riwayatNilai->id,
                'nilai'        => $riwayatNilai->nilai,
                'bintang'      => Quiz::hitungBintang((float) $riwayatNilai->nilai),
                'status'       => $riwayatNilai->status,
                'durasi'       => $riwayatNilai->durasi,
                'completed_at' => $riwayatNilai->created_at,
            ] : null,
        ]);
    }

    /**
     * SISWA: Ambil daftar butir soal kuis untuk dikerjakan.
     */
    public function siswaKerjakan(Request $request, Quiz $quiz)
    {
        $siswa = $request->user()?->siswa;
        if (!$this->quizService->isSiswaAuthorizedForQuiz($quiz, $siswa)) {
            return $this->forbiddenResponse('Akses ditolak.');
        }

        if ($quiz->status !== 'published') {
            return $this->forbiddenResponse('Kuis belum dipublikasikan.');
        }

        if ($siswa && !app()->runningUnitTests()) {
            SiswaPower::initializeDefaultInventory($siswa);
        }

        $existing = Nilai::where('siswa_id', $siswa->id)->where('quiz_id', $quiz->id)->first();
        if ($existing && $existing->status === 'selesai') {
            return $this->errorResponse('Anda telah menyelesaikan kuis ini sebelumnya.', 400, [
                'nilai'        => $existing->nilai,
                'completed_at' => $existing->created_at,
            ]);
        }

        if ($quiz->waktu_mulai && now()->lessThan($quiz->waktu_mulai)) {
            return $this->forbiddenResponse('Kuis belum dimulai. Jadwal mulai pada: ' . $quiz->waktu_mulai->format('d M Y, H:i') . ' WIB.');
        }

        if ($quiz->waktu_selesai && now()->greaterThan($quiz->waktu_selesai)) {
            return $this->forbiddenResponse('Waktu pengerjaan kuis telah berakhir.');
        }

        $quiz->loadMissing('soals');
        $journey = $this->adventureService->buildQuizAdventureJourney($quiz, $siswa);

        if ($journey['is_all_completed']) {
            return $this->errorResponse('Anda telah menyelesaikan seluruh level pada kuis ini.', 400);
        }

        $targetLevel = $this->adventureService->resolveTargetLevel(
            collect($journey['levels']),
            $request->query('arc'),
            $this->quizService->parseLevelNumber($request->query('level', '0')),
            (int) $request->query('step', 0),
            true
        );

        if (!$targetLevel) {
            return $this->notFoundResponse('Level kuis tidak ditemukan.');
        }

        if ($targetLevel['status'] === 'terkunci') {
            return $this->forbiddenResponse("Selesaikan level sebelumnya terlebih dahulu untuk membuka {$targetLevel['full_nama']}.");
        }

        if ($targetLevel['status'] === 'selesai') {
            return $this->errorResponse("{$targetLevel['full_nama']} sudah selesai dikerjakan dan tidak dapat dikerjakan ulang.", 400);
        }

        $soalsRaw = $quiz->soals()
            ->whereIn('id', $targetLevel['soal_ids'])
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();

        $savedAnswers = Menjawab::where('siswa_id', $siswa->id)
            ->whereIn('soal_id', $soalsRaw->pluck('id'))
            ->pluck('jawaban_dipilih', 'soal_id')
            ->toArray();

        $soals = $soalsRaw->map(function ($s) use ($quiz, $savedAnswers) {
            $pilihan = $this->quizService->getShuffledOptionsForSoal($quiz, $s);

            return [
                'id'            => $s->id,
                'urutan'        => $s->urutan,
                'kesulitan'     => $s->kesulitan,
                'level'         => $s->level,
                'tipe_soal'     => $s->tipe_soal,
                'pertanyaan'    => $s->pertanyaan,
                'pilihan'       => $pilihan,
                'saved_answer'  => $savedAnswers[$s->id] ?? null,
                'gambar_url'    => $s->gambar ? asset('storage/' . $s->gambar) : null,
                'bobot'         => $s->bobot,
            ];
        });

        $remainingSeconds = $quiz->waktu_selesai
            ? max(0, now()->diffInSeconds($quiz->waktu_selesai, false))
            : null;

        return $this->successResponse([
            'quiz' => [
                'id'                => $quiz->id,
                'judul'             => $quiz->judul,
                'waktu_selesai'     => $quiz->waktu_selesai?->format('Y-m-d H:i:s'),
                'remaining_seconds' => $remainingSeconds,
            ],
            'soals'         => $soals,
            'saved_answers' => (object) $savedAnswers,
        ]);
    }

    /**
     * SISWA: Menyimpan progres jawaban kuis sementara (draft).
     */
    public function siswaSimpanDraft(Request $request, Quiz $quiz)
    {
        $siswa = $request->user()?->siswa;
        if (!$this->quizService->isSiswaAuthorizedForQuiz($quiz, $siswa)) {
            return $this->forbiddenResponse('Akses ditolak.');
        }

        if ($quiz->status !== 'published') {
            return $this->forbiddenResponse('Kuis belum dipublikasikan.');
        }

        $alreadyCompleted = Nilai::where('siswa_id', $siswa->id)
            ->where('quiz_id', $quiz->id)
            ->where('status', 'selesai')
            ->exists();

        if ($alreadyCompleted) {
            return $this->forbiddenResponse('Kuis ini sudah selesai dikerjakan dan tidak dapat diubah lagi.');
        }

        if ($quiz->waktu_mulai && now()->lessThan($quiz->waktu_mulai)) {
            return $this->forbiddenResponse('Kuis belum dimulai.');
        }

        if ($quiz->waktu_selesai && now()->greaterThan($quiz->waktu_selesai->copy()->addMinutes(2))) {
            return $this->errorResponse('Waktu pengerjaan kuis telah kedaluwarsa.', 400);
        }

        $quiz->load('soals');
        $journey = $this->adventureService->buildQuizAdventureJourney($quiz, $siswa);
        $activeLevel = collect($journey['levels'])->firstWhere('status', 'aktif');
        $soals = $activeLevel ? collect($activeLevel['soals']) : collect();
        $jawabanInputs = $request->input('jawaban', []);

        DB::transaction(function () use ($soals, $jawabanInputs, $siswa) {
            foreach ($soals as $soal) {
                $jawabanUser = $jawabanInputs[$soal->id] ?? $jawabanInputs[(string) $soal->id] ?? null;
                if ($jawabanUser !== null && $jawabanUser !== '') {
                    $existing = Menjawab::where('soal_id', $soal->id)
                        ->where('siswa_id', $siswa->id)
                        ->lockForUpdate()
                        ->first();

                    if ($existing && $existing->koin_diklaim) {
                        continue;
                    }

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

        return $this->successResponse(null, 'Progres jawaban level berhasil disimpan!');
    }

    /**
     * SISWA: Submit jawaban kuis (per level maupun keseluruhan), koreksi otomatis, dan rekam riwayat pengerjaan.
     */
    public function siswaSubmit(Request $request, Quiz $quiz)
    {
        $user = $request->user();
        $siswa = $user->siswa;

        if (!$this->quizService->isSiswaAuthorizedForQuiz($quiz, $siswa)) {
            return $this->forbiddenResponse('Akses ditolak.');
        }

        if ($quiz->status !== 'published') {
            return $this->forbiddenResponse('Kuis belum dipublikasikan.');
        }

        $existing = Nilai::where('siswa_id', $siswa->id)->where('quiz_id', $quiz->id)->first();
        if ($existing && $existing->status === 'selesai') {
            return $this->errorResponse('Jawaban kuis Anda sebelumnya sudah tersimpan.', 400, [
                'nilai'  => $existing->nilai,
                'durasi' => $existing->durasi,
            ]);
        }

        if ($quiz->waktu_mulai && now()->lessThan($quiz->waktu_mulai)) {
            return $this->forbiddenResponse('Kuis belum dimulai.');
        }

        if ($quiz->waktu_selesai && now()->greaterThan($quiz->waktu_selesai->copy()->addMinutes(2))) {
            return $this->forbiddenResponse('Waktu pengumpulan kuis telah kedaluwarsa.');
        }

        $validator = Validator::make($request->all(), [
            'jawaban' => ['required', 'array'],
            'durasi'  => ['nullable'],
            'arc'     => ['nullable', 'string'],
            'level'   => ['nullable'],
            'step'    => ['nullable', 'integer'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $quiz->load('soals');
        $allQuizSoals = $quiz->soals;
        $journeyBefore = $this->adventureService->buildQuizAdventureJourney($quiz, $siswa);

        if ($journeyBefore['is_all_completed']) {
            return $this->errorResponse('Kuis ini sudah selesai dikerjakan dan tidak dapat dikerjakan ulang.', 400);
        }

        $targetLevel = $this->adventureService->resolveTargetLevel(
            collect($journeyBefore['levels']),
            $request->input('arc'),
            $this->quizService->parseLevelNumber($request->input('level', '0')),
            (int) $request->input('step', 0),
            true
        );

        if (!$targetLevel || empty($targetLevel['soals'])) {
            return $this->notFoundResponse('Level kuis tidak ditemukan.');
        }

        if ($targetLevel['status'] === 'terkunci') {
            return $this->forbiddenResponse("Selesaikan level sebelumnya terlebih dahulu untuk membuka {$targetLevel['full_nama']}.");
        }

        if ($targetLevel['status'] === 'selesai') {
            return $this->errorResponse("{$targetLevel['full_nama']} sudah selesai dikerjakan dan tidak dapat dikerjakan ulang.", 400);
        }

        $soalsToProcess = $targetLevel['soals'];

        $jumlahBenar = 0;
        $jumlahSalah = 0;
        $koinBaruDidapat = 0;

        DB::transaction(function () use ($soalsToProcess, $request, $siswa, &$jumlahBenar, &$jumlahSalah, &$koinBaruDidapat) {
            // Lock baris siswa untuk mencegah race condition / duplikasi koin pada submit paralel
            \App\Models\Siswa::whereKey($siswa->id)->lockForUpdate()->first();

            $submittedAnswers = $request->input('jawaban', []);
            foreach ($soalsToProcess as $soal) {
                $existingAns = Menjawab::with('power')
                    ->where('soal_id', $soal->id)
                    ->where('siswa_id', $siswa->id)
                    ->lockForUpdate()
                    ->first();

                if ($existingAns && $existingAns->koin_diklaim) {
                    if ($existingAns->benar) {
                        $jumlahBenar++;
                    } else {
                        $jumlahSalah++;
                    }
                    continue;
                }

                $jawabanUser = $submittedAnswers[$soal->id] ?? $submittedAnswers[(string) $soal->id] ?? null;

                if ($jawabanUser === null || $jawabanUser === '') {
                    if ($existingAns && $existingAns->jawaban_dipilih !== null && $existingAns->jawaban_dipilih !== '') {
                        $jawabanUser = $existingAns->jawaban_dipilih;
                    }
                }

                if ($jawabanUser !== null && $jawabanUser !== '') {
                    $isBenar = $this->quizService->isAnswerCorrect($soal, $jawabanUser);
                    $power = $existingAns?->power;
                    $powerId = $existingAns?->power_id;
                    $bobotSoal = (float) ($soal->bobot ?: 10);
                    $poinDiperoleh = Menjawab::hitungPoin($bobotSoal, $isBenar, $power);

                    if ($poinDiperoleh > 0) {
                        $koinBaruDidapat += (int) round($poinDiperoleh);
                    }

                    if ($isBenar) {
                        $jumlahBenar++;
                    } else {
                        $jumlahSalah++;
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
                            'koin_diklaim'    => true,
                        ]
                    );
                } else {
                    $jumlahSalah++;
                    Menjawab::updateOrCreate(
                        [
                            'soal_id'  => $soal->id,
                            'siswa_id' => $siswa->id,
                        ],
                        [
                            'jawaban_dipilih' => '-',
                            'benar'           => false,
                            'power_id'        => $existingAns?->power_id,
                            'poin_diperoleh'  => 0,
                            'koin_diklaim'    => true,
                        ]
                    );
                }
            }

            if ($koinBaruDidapat > 0) {
                $siswa->increment('koin', $koinBaruDidapat);
            }
        });

        $durasiDetik = $this->quizService->parseDurationToSeconds($request->input('durasi', 0));
        $prevDurasi = (int) ($existing?->durasi ?? 0);
        $totalDurasi = $prevDurasi + $durasiDetik;

        $journeyAfter = $this->adventureService->buildQuizAdventureJourney($quiz, $siswa);
        $totalAllSoal = $allQuizSoals->count();
        $answeredCount = Menjawab::where('siswa_id', $siswa->id)
            ->whereIn('soal_id', $allQuizSoals->pluck('id'))
            ->where('koin_diklaim', true)
            ->whereNotNull('jawaban_dipilih')
            ->where('jawaban_dipilih', '!=', '')
            ->count();

        // Jika tidak mengirim parameter level spesifik atau seluruh soal sudah dijawab, selesaikan kuis penuh
        $isFullQuizSubmit = !$targetLevel || ($totalAllSoal > 0 && $answeredCount >= $totalAllSoal) || $journeyAfter['is_all_completed'];

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
        $nilaiAkhir = $totalBobot > 0 ? min(100, round(($totalPoinSiswa / $totalBobot) * 100, 2)) : 0;

        $nilaiRecord = null;
        if ($isFullQuizSubmit) {
            $nilaiRecord = Nilai::updateOrCreate(
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

            if (!$existing || $existing->status !== 'selesai') {
                $this->quizService->notifyGuruQuizCompleted($quiz, $siswa, $user->name, [
                    'nilai' => $nilaiRecord->nilai,
                ]);
            }
        }

        return $this->successResponse([
            'is_quiz_completed' => $isFullQuizSubmit,
            'nilai'             => $nilaiRecord ? $nilaiRecord->nilai : $nilaiAkhir,
            'bintang'           => Quiz::hitungBintang((float) ($nilaiRecord ? $nilaiRecord->nilai : $nilaiAkhir)),
            'total_soal'        => $soalsToProcess->count(),
            'jumlah_benar'      => $jumlahBenar,
            'jumlah_salah'      => $jumlahSalah,
            'durasi_detik'      => $totalDurasi,
            'koin_didapat'      => $koinBaruDidapat,
            'total_koin_siswa'  => (int) $siswa->fresh()->koin,
        ], $isFullQuizSubmit
            ? 'Selamat! Kuis petualangan berhasil Anda selesaikan.'
            : "Level {$targetLevel['level_num']} pada Arc {$targetLevel['arc']} berhasil diselesaikan!");
    }

    /**
     * SISWA: Hasil evaluasi pengerjaan kuis siswa (mendukung filter per level maupun rekap keseluruhan).
     */
    public function siswaHasil(Request $request, Quiz $quiz)
    {
        $siswa = $request->user()?->siswa;
        if (!$this->quizService->isSiswaAuthorizedForQuiz($quiz, $siswa)) {
            return $this->forbiddenResponse('Akses ditolak.');
        }

        if ($quiz->status !== 'published') {
            return $this->forbiddenResponse('Kuis belum dipublikasikan.');
        }

        $quiz->load(['pengampuKelas.guruMapel.mapel', 'pengampuKelas.guruMapel.guru', 'soals']);
        $journey = $this->adventureService->buildQuizAdventureJourney($quiz, $siswa);
        $nilai = $journey['nilai'];
        $isQuizCompleted = $journey['is_all_completed'] || ($nilai && $nilai->status === 'selesai');

        $reqArc = $request->query('arc');
        $reqLevelNum = $this->quizService->parseLevelNumber($request->query('level', '0'));
        $reqStep = (int) $request->query('step', 0);

        $currentLevel = null;
        if ($reqArc || $reqLevelNum > 0 || $reqStep > 0) {
            $currentLevel = $this->adventureService->resolveTargetLevel(
                collect($journey['levels']),
                $reqArc,
                $reqLevelNum,
                $reqStep,
                false
            );
        }

        if ($currentLevel && $currentLevel['status'] !== 'selesai' && !$isQuizCompleted) {
            return $this->forbiddenResponse('Anda belum menyelesaikan level tersebut.');
        }

        if (!$currentLevel && !$isQuizCompleted) {
            $currentLevel = collect($journey['levels'])->where('status', 'selesai')->last();
            if (!$currentLevel) {
                return $this->notFoundResponse('Anda belum menyelesaikan kuis ini.');
            }
        }

        $soalsQuery = $quiz->soals()->with(['menjawabs' => fn ($q) => $q->with('power')->where('siswa_id', $siswa->id)]);
        if ($currentLevel && !empty($currentLevel['soal_ids'])) {
            $soalsQuery->whereIn('id', $currentLevel['soal_ids']);
        }

        $soals = $soalsQuery->orderBy('urutan')->orderBy('id')->get();

        $totalSoal  = $soals->count();
        $totalBenar = $soals->filter(fn ($s) => $s->menjawabs->first()?->benar)->count();
        $totalSalah = max(0, $totalSoal - $totalBenar);

        $detailSoal = $soals->map(function ($s) {
            $menjawab = $s->menjawabs->first();

            return [
                'id'              => $s->id,
                'pertanyaan'      => $s->pertanyaan,
                'tipe_soal'       => $s->tipe_soal,
                'kesulitan'       => $s->kesulitan,
                'level'           => $s->level,
                'pilihan'         => $s->pilihan,
                'kunci_jawaban'   => $s->kunci_jawaban,
                'jawaban_dipilih' => $menjawab?->jawaban_dipilih,
                'is_benar'        => (bool) $menjawab?->benar,
                'bobot'           => $s->bobot,
                'poin_diperoleh'  => $menjawab?->poin_diperoleh,
                'power'           => $menjawab?->power ? [
                    'id'   => $menjawab->power->id,
                    'kode' => $menjawab->power->kode,
                    'nama' => $menjawab->power->nama,
                ] : null,
            ];
        });

        $skorDitampilkan = $currentLevel
            ? $currentLevel['skor_level']
            : ($nilai ? (float) $nilai->nilai : ($totalSoal > 0 ? round(($totalBenar / $totalSoal) * 100, 2) : 0));

        return $this->successResponse([
            'quiz' => [
                'id'    => $quiz->id,
                'judul' => $quiz->judul,
                'mapel' => $quiz->pengampuKelas?->guruMapel?->mapel?->nama_mapel,
                'guru'  => $quiz->pengampuKelas?->guruMapel?->guru?->nama,
            ],
            'summary' => [
                'nilai'        => $skorDitampilkan,
                'bintang'      => Quiz::hitungBintang((float) $skorDitampilkan),
                'durasi_detik' => $nilai?->durasi ?? 0,
                'total_soal'   => $totalSoal,
                'total_benar'  => $totalBenar,
                'total_salah'  => $totalSalah,
                'completed_at' => $nilai?->created_at,
            ],
            'detail_soals' => $detailSoal,
        ]);
    }

    /**
     * SISWA: Hasil Petualangan Lengkap (rekap nilai akhir gabungan, bintang, statistik Power, dan seluruh soal).
     */
    public function siswaHasilPetualangan(Request $request, Quiz $quiz)
    {
        $siswa = $request->user()?->siswa;
        if (!$this->quizService->isSiswaAuthorizedForQuiz($quiz, $siswa)) {
            return $this->forbiddenResponse('Akses ditolak.');
        }

        if ($quiz->status !== 'published') {
            return $this->forbiddenResponse('Kuis belum dipublikasikan.');
        }

        $quiz->load([
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.guruMapel.guru',
            'pengampuKelas.kelas',
            'soals',
        ]);

        $journey = $this->adventureService->buildQuizAdventureJourney($quiz, $siswa);
        $nilai = $journey['nilai'];

        if (!$journey['is_all_completed'] && (!$nilai || $nilai->status !== 'selesai')) {
            return $this->forbiddenResponse('Selesaikan seluruh level kuis terlebih dahulu untuk melihat hasil petualangan lengkap.');
        }

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

        $detailSoals = $allSoals->map(function ($s) {
            $jwb = $s->menjawabs->first();
            return [
                'id'              => $s->id,
                'pertanyaan'      => $s->pertanyaan,
                'tipe_soal'       => $s->tipe_soal,
                'kesulitan'       => $s->kesulitan,
                'level'           => $s->level,
                'pilihan'         => $s->pilihan,
                'kunci_jawaban'   => $s->kunci_jawaban,
                'jawaban_dipilih' => $jwb?->jawaban_dipilih,
                'is_benar'        => (bool) $jwb?->benar,
                'bobot'           => $s->bobot,
                'poin_diperoleh'  => $jwb?->poin_diperoleh,
                'power'           => $jwb?->power ? [
                    'id'   => $jwb->power->id,
                    'kode' => $jwb->power->kode,
                    'nama' => $jwb->power->nama,
                ] : null,
            ];
        });

        return $this->successResponse([
            'quiz' => [
                'id'    => $quiz->id,
                'judul' => $quiz->judul,
                'mapel' => $quiz->pengampuKelas?->guruMapel?->mapel?->nama_mapel,
                'guru'  => $quiz->pengampuKelas?->guruMapel?->guru?->nama,
            ],
            'summary' => [
                'nilai_gabungan'       => $nilaiGabungan,
                'bintang_akhir'        => $bintangAkhir,
                'total_bintang_level'  => $journey['total_bintang'],
                'max_bintang_level'    => $journey['max_bintang'],
                'total_poin_didapat'   => round($totalPoinDidapat, 2),
                'total_bobot_kuis'     => round($totalBobotKuis, 2),
                'total_soal'           => $totalSoal,
                'total_benar'          => $totalBenar,
                'total_salah'          => $totalSalah,
                'durasi_detik'         => $durasiDetik,
                'durasi_formatted'     => $durasiFormatted,
                'used_powers_count'    => $usedPowersCount,
                'bonus_power_points'   => round($bonusPowerPoints, 2),
                'penalty_power_points' => round($penaltyPowerPoints, 2),
                'completed_at'         => $nilai?->created_at,
            ],
            'detail_soals' => $detailSoals,
        ]);
    }
}
