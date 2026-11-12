<?php

namespace App\Services;

use App\Models\Kelas;
use App\Models\Menjawab;
use App\Models\Nilai;
use App\Models\Notifikasi;
use App\Models\PengampuKelas;
use App\Models\Quiz;
use App\Models\Siswa;
use App\Models\Soal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class QuizService
{
    /**
     * Memastikan kuis dimiliki oleh guru yang sedang login.
     */
    public function isGuruOwner(Quiz $quiz, ?int $guruId): bool
    {
        if (!$guruId || !$quiz->pengampu_kelas_id) {
            return false;
        }

        if ($quiz->relationLoaded('pengampuKelas') && $quiz->pengampuKelas?->relationLoaded('guruMapel')) {
            return (int) $quiz->pengampuKelas?->guruMapel?->guru_id === (int) $guruId;
        }

        return PengampuKelas::where('id', $quiz->pengampu_kelas_id)
            ->whereHas('guruMapel', fn (Builder $q) => $q->where('guru_id', $guruId))
            ->exists();
    }

    /**
     * Mengambil daftar ID PengampuKelas yang sah milik guru dari input yang diberikan.
     */
    public function getValidPengampuIdsForGuru(array|int $pengampuIds, int $guruId): array
    {
        $ids = (array) $pengampuIds;
        if (empty($ids)) {
            return [];
        }

        return PengampuKelas::whereIn('id', $ids)
            ->whereHas('guruMapel', fn (Builder $q) => $q->where('guru_id', $guruId))
            ->pluck('id')
            ->all();
    }

    /**
     * Query dasar daftar kuis milik guru beserta filter pencarian, status, kelas, dan kesulitan.
     */
    public function buildGuruQuizzesQuery(int $guruId, Request $request, array $withRelations = []): Builder
    {
        $defaultRelations = [
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.kelas',
        ];

        $query = Quiz::with(array_unique(array_merge($defaultRelations, $withRelations)))
            ->withCount('soals')
            ->whereHas('pengampuKelas.guruMapel', fn (Builder $q) => $q->where('guru_id', $guruId));

        if ($request->filled('search')) {
            $query->where('judul', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('kelas_id')) {
            $query->whereHas('pengampuKelas', fn (Builder $q) => $q->where('kelas_id', $request->kelas_id));
        }

        if ($request->filled('kesulitan')) {
            $query->whereJsonContains('kesulitan_aktif', $request->kesulitan);
        }

        return $query;
    }

    /**
     * Menghitung statistik ringkasan kuis milik guru (total kuis, draf, terbit, dan soal).
     */
    public function getGuruQuizStatistics(int $guruId): array
    {
        $baseGuruQuery = Quiz::whereHas('pengampuKelas.guruMapel', fn (Builder $q) => $q->where('guru_id', $guruId));

        $totalKuis = (clone $baseGuruQuery)->count();
        $totalDraft = (clone $baseGuruQuery)->where(function (Builder $q) {
            $q->where('status', 'draft')->orWhereNull('status');
        })->count();
        $totalPublished = (clone $baseGuruQuery)->where('status', 'published')->count();
        $totalSoal = Soal::whereHas('quiz.pengampuKelas.guruMapel', fn (Builder $q) => $q->where('guru_id', $guruId))->count();

        return [
            'total_kuis'      => $totalKuis,
            'total_draft'     => $totalDraft,
            'total_published' => $totalPublished,
            'total_soal'      => $totalSoal,
        ];
    }

    /**
     * Mengurutkan dan memfilter daftar tingkat kesulitan aktif sesuai urutan standar (Mudah, Sedang, Sulit).
     */
    public function normalizeActiveDifficulties(array $difficulties): array
    {
        return array_values(array_intersect(Quiz::KESULITAN_LIST, $difficulties));
    }

    /**
     * Menstandarkan input level menjadi format 'Level X' (1..5) dan mengembalikan nomor levelnya.
     */
    public function parseLevelNumber(string|int|null $levelInput): int
    {
        return (int) preg_replace('/[^0-9]/', '', (string) $levelInput);
    }

    /**
     * Menghitung jumlah butir soal untuk setiap Arc aktif dan Level (Level 1 s/d Level 5).
     */
    public function calculateLevelCounts(Quiz $quiz, array $activeDifficulties): array
    {
        $soals = $quiz->relationLoaded('soals') ? $quiz->soals : $quiz->soals()->get();
        $levelCounts = [];

        foreach ($activeDifficulties as $arc) {
            $levelCounts[$arc] = [];
            foreach (Quiz::LEVEL_LIST as $lvl) {
                $levelCounts[$arc][$lvl] = $soals
                    ->where('kesulitan', $arc)
                    ->where('level', $lvl)
                    ->count();
            }
        }

        return $levelCounts;
    }

    /**
     * Memvalidasi apakah kuis memenuhi syarat untuk diterbitkan (published).
     * Mengembalikan pesan error jika belum valid, atau null jika siap diterbitkan.
     */
    public function validateQuizBeforePublish(Quiz $quiz, string $emptyQuestionLabel = 'soal'): ?string
    {
        $activeDifficulties = $quiz->kesulitan_aktif_array;

        if (empty($activeDifficulties)) {
            return 'Aktifkan minimal satu tingkat kesulitan sebelum menerbitkan kuis.';
        }

        foreach ($activeDifficulties as $difficulty) {
            $hasQuestions = $quiz->soals()->where('kesulitan', $difficulty)->exists();
            if (!$hasQuestions) {
                return "Tingkat kesulitan {$difficulty} belum memiliki {$emptyQuestionLabel}. Tambahkan minimal satu soal terlebih dahulu.";
            }
        }

        return null;
    }

    /**
     * Menghapus berkas gambar soal dari storage publik jika ada.
     */
    public function deleteSoalImage(?string $gambarPath): void
    {
        if ($gambarPath && Storage::disk('public')->exists($gambarPath)) {
            Storage::disk('public')->delete($gambarPath);
        }
    }

    /**
     * Menyimpan berkas gambar soal baru dan menghapus gambar lama jika ada.
     */
    public function handleSoalImageUpload(Request $request, ?string $existingPath = null): ?string
    {
        if (!$request->hasFile('gambar')) {
            return $existingPath;
        }

        $this->deleteSoalImage($existingPath);

        return $request->file('gambar')->store('soals', 'public');
    }

    /**
     * Menghapus satu kuis beserta seluruh berkas gambar soalnya.
     */
    public function deleteQuizWithAssets(Quiz $quiz): void
    {
        $soals = $quiz->relationLoaded('soals') ? $quiz->soals : $quiz->soals()->get();
        foreach ($soals as $soal) {
            $this->deleteSoalImage($soal->gambar);
        }

        $quiz->delete();
    }

    /**
     * Menghapus beberapa kuis milik guru sekaligus dalam satu transaksi database.
     */
    public function bulkDeleteGuruQuizzes(int $guruId, array $quizIds): int
    {
        $quizzes = Quiz::whereIn('id', $quizIds)
            ->whereHas('pengampuKelas.guruMapel', fn (Builder $q) => $q->where('guru_id', $guruId))
            ->with('soals')
            ->get();

        $count = $quizzes->count();
        if ($count === 0) {
            return 0;
        }

        DB::transaction(function () use ($quizzes) {
            foreach ($quizzes as $quiz) {
                $this->deleteQuizWithAssets($quiz);
            }
        });

        return $count;
    }

    /**
     * Mengambil kelas utama siswa (dari relasi langsung atau riwayat anggota_kelas terbaru).
     */
    public function getSiswaPrimaryKelas(?Siswa $siswa): ?Kelas
    {
        if (!$siswa) {
            return null;
        }

        return $siswa->kelas ?? $siswa->anggotaKelases()->with('kelas')->latest()->first()?->kelas;
    }

    /**
     * Mengambil seluruh ID kelas yang diikuti oleh siswa.
     */
    public function getSiswaKelasIds(?Siswa $siswa): array
    {
        if (!$siswa) {
            return [];
        }

        $kelasIds = $siswa->anggotaKelases()->pluck('kelas_id')->filter()->unique()->values()->all();
        if ($siswa->kelas?->id) {
            $kelasIds[] = $siswa->kelas->id;
        }

        return array_values(array_unique($kelasIds));
    }

    /**
     * Memeriksa apakah siswa berhak mengakses kuis berdasarkan kelasnya.
     */
    public function isSiswaAuthorizedForQuiz(Quiz $quiz, ?Siswa $siswa): bool
    {
        if (!$siswa) {
            return false;
        }

        $quizKelasId = $quiz->pengampuKelas?->kelas_id;
        if (!$quizKelasId) {
            return true;
        }

        $siswaKelasIds = $this->getSiswaKelasIds($siswa);
        if (empty($siswaKelasIds)) {
            return true;
        }

        if (!in_array($quizKelasId, $siswaKelasIds)) {
            if (Kelas::count() <= 1) {
                return true;
            }
            return false;
        }

        return true;
    }

    /**
     * Mengacak urutan kunci pilihan jawaban soal apabila fitur acak_jawaban diaktifkan pada kuis.
     */
    public function getShuffledOptionsForSoal(Quiz $quiz, Soal $soal): mixed
    {
        $pilihan = $soal->pilihan;
        if ($quiz->acak_jawaban && is_array($pilihan) && count($pilihan) > 1) {
            $keys = array_keys($pilihan);
            shuffle($keys);
            $shuffled = [];
            foreach ($keys as $k) {
                $shuffled[$k] = $pilihan[$k];
            }
            return $shuffled;
        }

        return $pilihan;
    }

    /**
     * Memeriksa apakah jawaban yang dipilih siswa sesuai dengan kunci jawaban soal.
     */
    public function isAnswerCorrect(Soal $soal, mixed $jawabanUser): bool
    {
        if ($jawabanUser === null || $jawabanUser === '' || $jawabanUser === '-') {
            return false;
        }

        $cleanUser = strtolower(trim((string) $jawabanUser));
        $kunci = $soal->kunci_jawaban;

        if (is_array($kunci)) {
            $cleanKunci = array_map(fn ($k) => strtolower(trim((string) $k)), $kunci);
            return in_array($cleanUser, $cleanKunci, true);
        }

        $decodedKunci = json_decode((string) $kunci, true);
        if (is_array($decodedKunci)) {
            $cleanKunci = array_map(fn ($k) => strtolower(trim((string) $k)), $decodedKunci);
            return in_array($cleanUser, $cleanKunci, true);
        }

        return $cleanUser === strtolower(trim((string) $kunci));
    }

    /**
     * Mengonversi input durasi (angka detik atau string "MM:SS" / "HH:MM:SS") menjadi integer detik.
     */
    public function parseDurationToSeconds(mixed $rawDurasi): int
    {
        if (is_numeric($rawDurasi)) {
            return (int) $rawDurasi;
        }

        if (is_string($rawDurasi) && str_contains($rawDurasi, ':')) {
            $parts = explode(':', trim($rawDurasi));
            if (count($parts) === 2) {
                return ((int) $parts[0] * 60) + (int) $parts[1];
            }
            if (count($parts) === 3) {
                return ((int) $parts[0] * 3600) + ((int) $parts[1] * 60) + (int) $parts[2];
            }
        }

        return 0;
    }

    /**
     * Mengirim notifikasi ke guru pengampu ketika siswa menyelesaikan kuis untuk pertama kalinya.
     */
    public function notifyGuruQuizCompleted(Quiz $quiz, Siswa $siswa, ?string $fallbackName = null, array $extraPayload = []): void
    {
        $guruUser = $quiz->pengampuKelas?->guruMapel?->guru?->user;
        if (!$guruUser) {
            return;
        }

        $namaSiswa = $siswa->nama_siswa ?? $fallbackName ?? 'Siswa';
        $namaKelas = $quiz->pengampuKelas?->kelas?->nama_kelas ?? 'Kelas';
        $message = isset($extraPayload['nilai'])
            ? "Siswa {$namaSiswa} ({$namaKelas}) telah menyelesaikan kuis petualangan '{$quiz->judul}' dengan nilai {$extraPayload['nilai']}."
            : "Siswa {$namaSiswa} ({$namaKelas}) telah menyelesaikan seluruh level kuis '{$quiz->judul}'.";

        Notifikasi::kirim(
            $guruUser->id,
            "Kuis Selesai Dikerjakan: {$quiz->judul}",
            $message,
            'kuis_selesai',
            route('guru.kuis.show', $quiz->id),
            $quiz->id,
            array_merge(['siswa_id' => $siswa->id, 'quiz_id' => $quiz->id], $extraPayload)
        );
    }
}

