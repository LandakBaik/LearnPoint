<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\PengampuKelas;
use App\Models\Quiz;
use App\Models\Soal;
use App\Services\QuizService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuizController extends Controller
{
    public function __construct(
        protected QuizService $quizService
    ) {}

    // Menampilkan daftar semua kuis petualangan milik guru.
    public function index(Request $request)
    {
        $guruId = $this->resolveGuruId();

        $quizzes = $this->quizService
            ->buildGuruQuizzesQuery($guruId, $request, ['soals', 'nilais'])
            ->latest()
            ->paginate(12)
            ->withQueryString();

        // Pengampu kelas untuk dropdown pilihan kelas
        $pengampuKelases = PengampuKelas::with(['guruMapel.mapel', 'kelas'])
            ->whereHas('guruMapel', fn ($q) => $q->where('guru_id', $guruId))
            ->get();

        $kelases = $pengampuKelases->pluck('kelas')->unique('id')->filter();

        // Data terstruktur untuk pemilihan cascading Sasaran Kelas (Mapel -> Tingkatan -> Kelas)
        $mapelData = [];
        foreach ($pengampuKelases as $pk) {
            $mapel = $pk->guruMapel?->mapel;
            $kelas = $pk->kelas;
            if (!$mapel || !$kelas) {
                continue;
            }

            if (!isset($mapelData[$mapel->id])) {
                $mapelData[$mapel->id] = [
                    'id' => $mapel->id,
                    'nama' => $mapel->nama_mapel,
                    'asset' => (int) ($mapel->asset ?? 0),
                    'planet_label' => $mapel->planet_label,
                    'scenes' => $mapel->planet_scenes,
                    'tingkatans' => [],
                ];
            }
            if (!isset($mapelData[$mapel->id]['tingkatans'][$kelas->tingkatan])) {
                $mapelData[$mapel->id]['tingkatans'][$kelas->tingkatan] = [];
            }
            $mapelData[$mapel->id]['tingkatans'][$kelas->tingkatan][] = [
                'kelas_id' => $kelas->id,
                'nama_kelas' => $kelas->nama_kelas,
                'pengampu_kelas_id' => $pk->id,
            ];
        }

        usort($mapelData, fn ($a, $b) => strcmp($a['nama'], $b['nama']));

        $stats = $this->quizService->getGuruQuizStatistics($guruId);
        $totalKuis = $stats['total_kuis'];
        $totalDraft = $stats['total_draft'];
        $totalPublished = $stats['total_published'];
        $totalSoal = $stats['total_soal'];

        return view('guru.kelola_kuis.index', compact(
            'quizzes',
            'pengampuKelases',
            'kelases',
            'mapelData',
            'totalKuis',
            'totalDraft',
            'totalPublished',
            'totalSoal'
        ));
    }

    //Menyimpan draf kuis petualangan baru.
    public function store(Request $request)
    {
        $guruId = $this->resolveGuruId();

        $validated = $request->validate([
            'judul'                => ['required', 'string', 'max:200'],
            'deskripsi'            => ['nullable', 'string', 'max:1000'],
            'pengampu_kelas_id'    => ['nullable', 'exists:pengampu_kelas,id'],
            'pengampu_kelas_ids'   => ['nullable', 'array', 'min:1'],
            'pengampu_kelas_ids.*' => ['exists:pengampu_kelas,id'],
            'kesulitan_aktif'      => ['required', 'array', 'min:1', 'max:3'],
            'kesulitan_aktif.*'    => ['string', 'in:Mudah,Sedang,Sulit'],
            'waktu_mulai'          => ['nullable', 'date', 'after_or_equal:today'],
            'waktu_selesai'        => ['nullable', 'date', 'after:waktu_mulai'],
            'acak_jawaban'         => ['nullable', 'boolean'],
            'status'               => ['nullable', 'string', 'in:draft,published'],
            'asset'                => ['nullable', 'integer', 'min:0', 'max:255'],
        ], [
            'kesulitan_aktif.required'   => 'Pilih minimal satu tingkat kesulitan untuk petualangan kuis ini.',
            'kesulitan_aktif.min'        => 'Kuis petualangan harus memiliki minimal 1 tingkat kesulitan.',
            'kesulitan_aktif.max'        => 'Kuis petualangan maksimal memiliki 3 tingkat kesulitan (Mudah, Sedang, Sulit).',
            'waktu_mulai.after_or_equal' => 'Waktu mulai kuis tidak boleh sebelum hari ini.',
            'waktu_selesai.after'        => 'Waktu selesai kuis harus lebih lambat dari waktu mulai (tidak boleh sama atau lebih awal).',
        ]);

        $targetIds = [];
        if ($request->filled('pengampu_kelas_ids')) {
            $targetIds = (array) $request->input('pengampu_kelas_ids');
        } elseif ($request->filled('pengampu_kelas_id')) {
            $targetIds = [$request->input('pengampu_kelas_id')];
        }

        if (empty($targetIds)) {
            return back()->withInput()->with('error', 'Silakan pilih minimal satu kelas sasaran untuk kuis ini.');
        }

        $validPengampu = $this->quizService->getValidPengampuIdsForGuru($targetIds, $guruId);

        if (empty($validPengampu)) {
            return back()->withInput()->with('error', 'Anda tidak memiliki hak akses pada kelas/mata pelajaran yang dipilih.');
        }

        $orderedDifficulties = $this->quizService->normalizeActiveDifficulties($validated['kesulitan_aktif']);
        $grupId = count($validPengampu) > 1 ? (string) Str::uuid() : null;

        $firstQuiz = null;
        $createdCount = 0;
        foreach ($validPengampu as $pkId) {
            $quiz = Quiz::create([
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

            if (!$firstQuiz) {
                $firstQuiz = $quiz;
            }
            $createdCount++;
        }

        $msg = $createdCount > 1
            ? "Draf kuis '{$firstQuiz->judul}' berhasil dibuat untuk {$createdCount} kelas! Soal yang Anda tambahkan akan otomatis tersinkron ke seluruh kelas dalam grup ini."
            : "Draf kuis '{$firstQuiz->judul}' berhasil dibuat! Silakan kelola arc, level tantangan, dan butir soal di bawah.";

        return redirect()->route('guru.kuis.show', $firstQuiz)->with('success', $msg);
    }

    // Menampilkan detail kuis petualangan: tingkat Kesulitan, Level 1-5, dan daftar soal.
    public function show(Request $request, Quiz $quiz)
    {
        $guruId = $this->resolveGuruId();
        $this->authorizeGuru($quiz, $guruId);

        // Pastikan bobot seluruh soal selalu tersinkronisasi otomatis oleh sistem
        $quiz->sinkronisasiBobotSoal();

        $quiz->load([
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.kelas',
            'soals',
            'nilais.siswa',
        ]);

        $activeDifficulties = $quiz->kesulitan_aktif_array;

        $activeArc = $request->query('arc');
        if (!in_array($activeArc, $activeDifficulties)) {
            $activeArc = $activeDifficulties[0] ?? 'Mudah';
        }

        $activeLevel = $request->query('level', 'Level 1');
        if (!in_array($activeLevel, Quiz::LEVEL_LIST)) {
            $activeLevel = 'Level 1';
        }

        $levelCounts = $this->quizService->calculateLevelCounts($quiz, $activeDifficulties);

        $soalsInLevel = $quiz->soals()
            ->where('kesulitan', $activeArc)
            ->where('level', $activeLevel)
            ->orderBy('urutan')
            ->get();

        $groupQuizzes = $this->quizService->getGroupQuizzes($quiz)
            ->loadMissing('pengampuKelas.kelas');
        $daftarSiswa = $this->quizService->getClassStudentProgress($quiz);
        $hasStudentActivity = $quiz->hasStudentActivity();

        return view('guru.kelola_kuis.show', compact(
            'quiz',
            'activeDifficulties',
            'activeArc',
            'activeLevel',
            'levelCounts',
            'soalsInLevel',
            'groupQuizzes',
            'daftarSiswa',
            'hasStudentActivity'
        ));
    }

    // Memperbarui draf / pengaturan kuis petualangan.
    public function update(Request $request, Quiz $quiz)
    {
        $guruId = $this->resolveGuruId();
        $this->authorizeGuru($quiz, $guruId);

        // Izinkan waktu_mulai yang sudah berlalu jika tidak diubah oleh guru saat edit
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

        $validated = $request->validate([
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
        ], [
            'kesulitan_aktif.required'   => 'Pilih minimal satu tingkat kesulitan (Arc) untuk petualangan kuis ini.',
            'kesulitan_aktif.min'        => 'Kuis petualangan harus memiliki minimal 1 tingkat kesulitan.',
            'kesulitan_aktif.max'        => 'Kuis petualangan maksimal memiliki 3 tingkat kesulitan (Mudah, Sedang, Sulit).',
            'waktu_mulai.after_or_equal' => 'Waktu mulai kuis tidak boleh sebelum hari ini.',
            'waktu_selesai.after'        => 'Waktu selesai kuis harus lebih lambat dari waktu mulai (tidak boleh sama atau lebih awal).',
        ]);

        $validPengampu = $this->quizService->getValidPengampuIdsForGuru($validated['pengampu_kelas_id'], $guruId);
        if (empty($validPengampu)) {
            return back()->withInput()->with('error', 'Anda tidak memiliki hak akses pada kelas/mata pelajaran ini.');
        }

        $orderedDifficulties = $this->quizService->normalizeActiveDifficulties($validated['kesulitan_aktif']);
        $targetStatus = $validated['status'] ?? $quiz->status;

        if ($targetStatus === 'published') {
            $publishError = $this->quizService->validateQuizBeforePublish($quiz, 'soal', $orderedDifficulties);
            if ($publishError !== null) {
                return back()->withInput()->with('error', $publishError);
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

        return redirect()->back()->with('success', 'Pengaturan kuis petualangan berhasil diperbarui!');
    }

    // Mengubah status kuis antara Draf dan Diterbitkan.
    public function toggleStatus(Quiz $quiz)
    {
        $guruId = $this->resolveGuruId();
        $this->authorizeGuru($quiz, $guruId);

        if ($quiz->status === 'published') {
            $quiz->update(['status' => 'draft']);

            return redirect()->back()
                ->with('success', 'Kuis berhasil diubah menjadi draf.');
        }

        $publishError = $this->quizService->validateQuizBeforePublish($quiz, 'soal');
        if ($publishError !== null) {
            return redirect()->back()->with('error', $publishError);
        }

        $quiz->update(['status' => 'published']);
        $this->quizService->notifyKelasQuizPublished($quiz);

        return redirect()->back()
            ->with('success', 'Kuis berhasil diterbitkan dan notifikasi telah dikirim ke siswa.');
    }

    /**
     * Menghapus kuis beserta seluruh butir soalnya.
     */
    public function destroy(Quiz $quiz)
    {
        $guruId = $this->resolveGuruId();
        $this->authorizeGuru($quiz, $guruId);

        $this->quizService->deleteQuizWithAssets($quiz);

        return redirect()->route('guru.kuis.index')->with('success', 'Kuis petualangan berhasil dihapus!');
    }

    // Menghapus beberapa kuis sekaligus.
    public function bulkDestroy(Request $request)
    {
        $guruId = $this->resolveGuruId();

        $validated = $request->validate([
            'quiz_ids'   => ['required', 'array', 'min:1'],
            'quiz_ids.*' => ['required', 'exists:quizzes,id'],
        ], [
            'quiz_ids.required' => 'Pilih minimal satu kuis untuk dihapus.',
            'quiz_ids.min'      => 'Pilih minimal satu kuis untuk dihapus.',
        ]);

        $count = $this->quizService->bulkDeleteGuruQuizzes($guruId, $validated['quiz_ids']);

        if ($count === 0) {
            return redirect()->route('guru.kuis.index')
                ->with('error', 'Tidak ada kuis valid milik Anda yang dapat dihapus.');
        }

        return redirect()->route('guru.kuis.index')
            ->with('success', "{$count} kuis petualangan terpilih berhasil dihapus!");
    }

    // Menambahkan butir soal ke dalam level tertentu pada Arc kuis. Sistem: Maksimal 5 butir soal per level.
    public function storeSoal(Request $request, Quiz $quiz)
    {
        $guruId = $this->resolveGuruId();
        $this->authorizeGuru($quiz, $guruId);

        $validated = $request->validate([
            'pertanyaan'    => ['required', 'string'],
            'tipe_soal'     => ['required', 'in:single_choice,multiple_choice,essay,matching'],
            'kesulitan'     => ['required', 'string', 'in:Mudah,Sedang,Sulit'],
            'level'         => ['required', 'string', 'in:Level 1,Level 2,Level 3,Level 4,Level 5,1,2,3,4,5'],
            'pilihan'       => ['required', 'array'],
            'pilihan.a'     => ['required', 'string'],
            'pilihan.b'     => ['required', 'string'],
            'pilihan.c'     => ['required', 'string'],
            'pilihan.d'     => ['required', 'string'],
            'kunci_jawaban' => ['required', 'string', 'in:a,b,c,d,A,B,C,D'],
            'bobot'         => ['nullable', 'numeric', 'min:1'],
            'gambar'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'pilihan.required'       => 'Pilihan jawaban wajib diisi lengkap 4 opsi (A, B, C, dan D).',
            'pilihan.a.required'     => 'Pilihan jawaban A wajib diisi.',
            'pilihan.b.required'     => 'Pilihan jawaban B wajib diisi.',
            'pilihan.c.required'     => 'Pilihan jawaban C wajib diisi.',
            'pilihan.d.required'     => 'Pilihan jawaban D wajib diisi.',
            'kunci_jawaban.required' => 'Pilih kunci jawaban yang benar.',
            'kunci_jawaban.in'       => 'Kunci jawaban harus merupakan salah satu dari opsi A, B, C, atau D.',
        ]);

        $levelNum = $this->quizService->parseLevelNumber($validated['level']);
        if ($levelNum < 1 || $levelNum > Quiz::MAX_LEVEL) {
            return back()->withInput()->with('error', 'Level tidak valid. Maksimal 5 level per tingkat kesulitan.');
        }
        $levelFormatted = 'Level ' . $levelNum;
        $kesulitan = $validated['kesulitan'];

        if (!in_array($kesulitan, $quiz->kesulitan_aktif_array)) {
            return back()->withInput()->with('error', "Arc tingkat kesulitan {$kesulitan} tidak aktif pada kuis ini.");
        }

        $currentCount = $quiz->countSoalsFor($kesulitan, $levelFormatted);

        if ($currentCount >= Quiz::MAX_SOAL) {
            return back()->withInput()->with('error', "Level ini ({$kesulitan} - {$levelFormatted}) sudah mencapai batas maksimal " . Quiz::MAX_SOAL . " butir soal.");
        }

        $gambarPath = $this->quizService->handleSoalImageUpload($request);

        $validKeys = ['a', 'b', 'c', 'd'];
        $pilihanFormatted = [];
        foreach ($validKeys as $k) {
            $val = $request->input("pilihan.{$k}");
            if ($val === null || trim((string) $val) === '') {
                return back()->withInput()->with('error', 'Semua 4 opsi pilihan jawaban (A, B, C, dan D) wajib diisi.');
            }
            $pilihanFormatted[$k] = trim((string) $val);
        }

        if ($this->hasDuplicatePilihan($pilihanFormatted)) {
            return back()->withInput()->with('error', 'Pilihan jawaban tidak boleh ada yang sama.');
        }

        $kunci = strtolower(trim((string) $validated['kunci_jawaban']));
        if (!in_array($kunci, $validKeys)) {
            return back()->withInput()->with('error', 'Kunci jawaban harus salah satu dari A, B, C, atau D.');
        }

        $groupQuizzes = $this->quizService->getGroupQuizzes($quiz);
        $syncedCount = 0;

        DB::transaction(function () use (
            $groupQuizzes,
            $quiz,
            $validated,
            $kesulitan,
            $levelFormatted,
            $pilihanFormatted,
            $kunci,
            $gambarPath,
            &$syncedCount
        ) {
            foreach ($groupQuizzes as $targetQuiz) {
                if ($targetQuiz->id !== $quiz->id && !$targetQuiz->canAddSoalFor($kesulitan, $levelFormatted)) {
                    continue;
                }

                $nextUrutan = ($targetQuiz->soalsFor($kesulitan, $levelFormatted)->max('urutan') ?? 0) + 1;

                Soal::create([
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

                // Rekalkulasi bobot semua soal oleh sistem sesuai jumlah soal & tingkat kesulitan
                $targetQuiz->sinkronisasiBobotSoal();
                $syncedCount++;
            }
        });

        $syncNote = $syncedCount > 1 ? " (Tersinkron ke {$syncedCount} kelas dalam grup)" : '';

        return redirect()->route('guru.kuis.show', [
            'quiz'  => $quiz,
            'arc'   => $kesulitan,
            'level' => $levelFormatted,
        ])->with('success', "Soal berhasil ditambahkan ke {$kesulitan} - {$levelFormatted}! (" . ($currentCount + 1) . "/" . Quiz::MAX_SOAL . " soal){$syncNote}");
    }

    /**
     * Memeriksa apakah ada teks pilihan jawaban yang duplikat (case-insensitive).
     */
    private function hasDuplicatePilihan(array $pilihan): bool
    {
        $normalized = array_map(
            static fn ($val) => mb_strtolower(trim((string) $val)),
            array_values($pilihan)
        );

        return count($normalized) !== count(array_unique($normalized));
    }

    /**
     * Memperbarui butir soal kuis.
     */
    public function updateSoal(Request $request, Quiz $quiz, Soal $soal)
    {
        $guruId = $this->resolveGuruId();
        $this->authorizeGuru($quiz, $guruId);
        abort_unless($soal->quiz_id === $quiz->id, 404);

        $validated = $request->validate([
            'pertanyaan'    => ['required', 'string'],
            'tipe_soal'     => ['required', 'in:single_choice,multiple_choice,essay,matching'],
            'pilihan'       => ['required', 'array'],
            'pilihan.a'     => ['required', 'string'],
            'pilihan.b'     => ['required', 'string'],
            'pilihan.c'     => ['required', 'string'],
            'pilihan.d'     => ['required', 'string'],
            'kunci_jawaban' => ['required', 'string', 'in:a,b,c,d,A,B,C,D'],
            'bobot'         => ['nullable', 'numeric', 'min:1'],
            'gambar'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'hapus_gambar'  => ['nullable', 'boolean'],
        ], [
            'pilihan.required'       => 'Pilihan jawaban wajib diisi lengkap 4 opsi (A, B, C, dan D).',
            'pilihan.a.required'     => 'Pilihan jawaban A wajib diisi.',
            'pilihan.b.required'     => 'Pilihan jawaban B wajib diisi.',
            'pilihan.c.required'     => 'Pilihan jawaban C wajib diisi.',
            'pilihan.d.required'     => 'Pilihan jawaban D wajib diisi.',
            'kunci_jawaban.required' => 'Pilih kunci jawaban yang benar.',
            'kunci_jawaban.in'       => 'Kunci jawaban harus merupakan salah satu dari opsi A, B, C, atau D.',
        ]);

        $validKeys = ['a', 'b', 'c', 'd'];
        $pilihanFormatted = [];
        foreach ($validKeys as $k) {
            $val = $request->input("pilihan.{$k}");
            if ($val === null || trim((string) $val) === '') {
                return back()->withInput()->with('error', 'Semua 4 opsi pilihan jawaban (A, B, C, dan D) wajib diisi.');
            }
            $pilihanFormatted[$k] = trim((string) $val);
        }

        if ($this->hasDuplicatePilihan($pilihanFormatted)) {
            return back()->withInput()->with('error', 'Pilihan jawaban (A, B, C, dan D) tidak boleh ada yang sama agar tidak ambigu.');
        }

        $kunci = strtolower(trim((string) $validated['kunci_jawaban']));
        if (!in_array($kunci, $validKeys)) {
            return back()->withInput()->with('error', 'Kunci jawaban yang dipilih harus salah satu dari opsi A, B, C, atau D.');
        }

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

        $groupQuizzes = $this->quizService->getGroupQuizzes($quiz);
        $updatedSoalIds = [$soal->id];

        DB::transaction(function () use (
            $groupQuizzes,
            $quiz,
            $soal,
            $validated,
            $pilihanFormatted,
            $kunci,
            $gambarPath,
            $oldKesulitan,
            $oldLevel,
            $oldUrutan,
            $oldPertanyaan,
            &$updatedSoalIds
        ) {
            $soal->update([
                'pertanyaan'    => $validated['pertanyaan'],
                'tipe_soal'     => $validated['tipe_soal'],
                'pilihan'       => $pilihanFormatted,
                'kunci_jawaban' => $kunci,
                'bobot'         => $validated['bobot'] ?? $soal->bobot,
                'gambar'        => $gambarPath,
            ]);
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
                    $matchingSoal->update([
                        'pertanyaan'    => $validated['pertanyaan'],
                        'tipe_soal'     => $validated['tipe_soal'],
                        'pilihan'       => $pilihanFormatted,
                        'kunci_jawaban' => $kunci,
                        'bobot'         => $validated['bobot'] ?? $matchingSoal->bobot,
                        'gambar'        => $gambarPath,
                    ]);
                    $updatedSoalIds[] = $matchingSoal->id;
                    $otherQuiz->sinkronisasiBobotSoal();
                }
            }
        });

        if ($oldImage && $oldImage !== $gambarPath) {
            $this->quizService->deleteSoalImage($oldImage);
        }

        return redirect()->route('guru.kuis.show', [
            'quiz'  => $quiz,
            'arc'   => $soal->kesulitan ?? 'Mudah',
            'level' => $soal->level ?? 'Level 1',
        ])->with('success', 'Butir soal berhasil diperbarui!');
    }

    /**
     * Menghapus butir soal kuis.
     */
    public function destroySoal(Quiz $quiz, Soal $soal)
    {
        $guruId = $this->resolveGuruId();
        $this->authorizeGuru($quiz, $guruId);
        abort_unless($soal->quiz_id === $quiz->id, 404);

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

        return redirect()->route('guru.kuis.show', [
            'quiz'  => $quiz,
            'arc'   => $arc,
            'level' => $level,
        ])->with('success', 'Butir soal berhasil dihapus! Slot soal kini kembali tersedia.');
    }

    /**
     * Mengambil ID guru dari user yang sedang login dan memvalidasi role.
     */
    private function resolveGuruId(): int
    {
        $user = Auth::user();
        $guruId = $user?->guru?->id ?? $user?->guru_id;
        abort_unless($user && $user->role === 'guru' && $guruId, 403, 'Akses ditolak.');

        return (int) $guruId;
    }

    /**
     * Helper verifikasi kepemilikan kuis oleh guru yang login.
     */
    private function authorizeGuru(Quiz $quiz, int $guruId): void
    {
        abort_unless(
            $this->quizService->isGuruOwner($quiz, $guruId),
            403,
            'Akses ditolak: Anda bukan pengampu kelas untuk kuis ini.'
        );
    }
}
