<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Soal;
use App\Models\PengampuKelas;
use App\Models\Nilai;
use App\Models\Menjawab;
use App\Models\Notifikasi;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    /**
     * Menampilkan daftar semua kuis petualangan milik guru yang login.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $guruId = $user->guru?->id ?? $user->guru_id;
        abort_unless($user->role === 'guru' && $guruId, 403, 'Akses ditolak.');

        $query = Quiz::with([
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.kelas',
            'soals',
            'nilais',
        ])
        ->withCount('soals')
        ->whereHas('pengampuKelas.guruMapel', function ($q) use ($guruId) {
            $q->where('guru_id', $guruId);
        });

        // Search Judul
        if ($request->filled('search')) {
            $query->where('judul', 'like', '%' . $request->search . '%');
        }

        // Filter Status (draft / published)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter Kelas
        if ($request->filled('kelas_id')) {
            $query->whereHas('pengampuKelas', function ($q) use ($request) {
                $q->where('kelas_id', $request->kelas_id);
            });
        }

        // Filter Kesulitan / Arc
        if ($request->filled('kesulitan')) {
            $query->whereJsonContains('kesulitan_aktif', $request->kesulitan);
        }

        $quizzes = $query->latest()->paginate(12)->withQueryString();

        // Pengampu kelas untuk dropdown pilihan kelas
        $pengampuKelases = PengampuKelas::with(['guruMapel.mapel', 'kelas'])
            ->whereHas('guruMapel', function ($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            })
            ->get();

        $kelases = $pengampuKelases->pluck('kelas')->unique('id')->filter();

        // Statistik Dashboard Kuis Guru
        $baseGuruQuery = Quiz::whereHas('pengampuKelas.guruMapel', function ($q) use ($guruId) {
            $q->where('guru_id', $guruId);
        });

        $totalKuis = (clone $baseGuruQuery)->count();
        $totalDraft = (clone $baseGuruQuery)->where(function ($q) {
            $q->where('status', 'draft')->orWhereNull('status');
        })->count();
        $totalPublished = (clone $baseGuruQuery)->where('status', 'published')->count();

        $totalSoal = Soal::whereHas('quiz.pengampuKelas.guruMapel', function ($q) use ($guruId) {
            $q->where('guru_id', $guruId);
        })->count();

        return view('guru.kuis.index', compact(
            'quizzes',
            'pengampuKelases',
            'kelases',
            'totalKuis',
            'totalDraft',
            'totalPublished',
            'totalSoal'
        ));
    }

    /**
     * Menyimpan draf kuis petualangan baru.
     * Kuis dapat memiliki maksimal 3 Arc Kesulitan (Mudah, Sedang, Sulit).
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $guruId = $user->guru?->id ?? $user->guru_id;
        abort_unless($user->role === 'guru' && $guruId, 403);

        $validated = $request->validate([
            'judul'               => ['required', 'string', 'max:200'],
            'deskripsi'           => ['nullable', 'string', 'max:1000'],
            'pengampu_kelas_id'   => ['required', 'exists:pengampu_kelas,id'],
            'kesulitan_aktif'     => ['required', 'array', 'min:1', 'max:3'],
            'kesulitan_aktif.*'   => ['string', 'in:Mudah,Sedang,Sulit'],
            'waktu_mulai'         => ['nullable', 'date', 'after_or_equal:today'],
            'waktu_selesai'       => ['nullable', 'date', 'after:waktu_mulai'],
            'acak_jawaban'        => ['nullable', 'boolean'],
            'status'              => ['nullable', 'string', 'in:draft,published'],
        ], [
            'kesulitan_aktif.required'   => 'Pilih minimal satu tingkat kesulitan (Arc) untuk petualangan kuis ini.',
            'kesulitan_aktif.min'        => 'Kuis petualangan harus memiliki minimal 1 tingkat kesulitan.',
            'kesulitan_aktif.max'        => 'Kuis petualangan maksimal memiliki 3 tingkat kesulitan (Mudah, Sedang, Sulit).',
            'waktu_mulai.after_or_equal' => 'Waktu mulai kuis tidak boleh sebelum hari ini.',
            'waktu_selesai.after'        => 'Waktu selesai kuis harus lebih lambat dari waktu mulai (tidak boleh sama atau lebih awal).',
        ]);

        $isOwner = PengampuKelas::where('id', $validated['pengampu_kelas_id'])
            ->whereHas('guruMapel', function ($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            })->exists();

        if (!$isOwner) {
            return back()->withInput()->with('error', 'Anda tidak memiliki hak akses pada kelas/mata pelajaran ini.');
        }

        // Urutkan kesulitan agar konsisten: Mudah, Sedang, Sulit
        $orderedDifficulties = array_values(array_intersect(Quiz::KESULITAN_LIST, $validated['kesulitan_aktif']));

        $quiz = Quiz::create([
            'judul'             => $validated['judul'],
            'deskripsi'         => $validated['deskripsi'] ?? null,
            'pengampu_kelas_id' => $validated['pengampu_kelas_id'],
            'kesulitan_aktif'   => $orderedDifficulties,
            'waktu_mulai'       => $validated['waktu_mulai'] ?? null,
            'waktu_selesai'     => $validated['waktu_selesai'] ?? null,
            'acak_jawaban'      => $request->boolean('acak_jawaban'),
            'status'            => $validated['status'] ?? 'draft',
            'level'             => 'Level 1',
            'kesulitan'         => $orderedDifficulties[0] ?? 'Mudah',
        ]);

        return redirect()->route('guru.kuis.show', $quiz)
            ->with('success', "Draf kuis '{$quiz->judul}' berhasil dibuat! Silakan kelola arc, level tantangan, dan butir soal di bawah.");
    }

    /**
     * Menampilkan detail kuis petualangan: Arc Kesulitan, Level 1-5, dan daftar soal.
     */
    public function show(Request $request, Quiz $quiz)
    {
        $user = auth()->user();
        $guruId = $user->guru?->id ?? $user->guru_id;
        abort_unless($user->role === 'guru' && $guruId, 403);

        $this->authorizeGuru($quiz, $guruId);

        $quiz->load([
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.kelas',
            'soals',
            'nilais.siswa',
        ]);

        $activeDifficulties = $quiz->kesulitan_aktif_array;

        // Tentukan Arc yang sedang dibuka guru (default ke arc pertama)
        $activeArc = $request->query('arc');
        if (!in_array($activeArc, $activeDifficulties)) {
            $activeArc = $activeDifficulties[0] ?? 'Mudah';
        }

        // Tentukan Level yang sedang dibuka guru (default ke 'Level 1')
        $activeLevel = $request->query('level', 'Level 1');
        if (!in_array($activeLevel, Quiz::LEVEL_LIST)) {
            $activeLevel = 'Level 1';
        }

        // Hitung jumlah soal untuk setiap Arc dan Level
        $levelCounts = [];
        foreach ($activeDifficulties as $arc) {
            $levelCounts[$arc] = [];
            foreach (Quiz::LEVEL_LIST as $lvl) {
                $levelCounts[$arc][$lvl] = $quiz->soals
                    ->where('kesulitan', $arc)
                    ->where('level', $lvl)
                    ->count();
            }
        }

        // Ambil daftar butir soal untuk Arc dan Level aktif
        $soalsInLevel = $quiz->soals()
            ->where('kesulitan', $activeArc)
            ->where('level', $activeLevel)
            ->orderBy('urutan')
            ->get();

        return view('guru.kuis.show', compact(
            'quiz',
            'activeDifficulties',
            'activeArc',
            'activeLevel',
            'levelCounts',
            'soalsInLevel'
        ));
    }

    /**
     * Memperbarui draf / pengaturan kuis petualangan.
     */
    public function update(Request $request, Quiz $quiz)
    {
        $user = auth()->user();
        $guruId = $user->guru?->id ?? $user->guru_id;
        abort_unless($user->role === 'guru' && $guruId, 403);

        $this->authorizeGuru($quiz, $guruId);

        $validated = $request->validate([
            'judul'               => ['required', 'string', 'max:200'],
            'deskripsi'           => ['nullable', 'string', 'max:1000'],
            'pengampu_kelas_id'   => ['required', 'exists:pengampu_kelas,id'],
            'kesulitan_aktif'     => ['required', 'array', 'min:1', 'max:3'],
            'kesulitan_aktif.*'   => ['string', 'in:Mudah,Sedang,Sulit'],
            'waktu_mulai'         => ['nullable', 'date', 'after_or_equal:today'],
            'waktu_selesai'       => ['nullable', 'date', 'after:waktu_mulai'],
            'acak_jawaban'        => ['nullable', 'boolean'],
            'status'              => ['nullable', 'string', 'in:draft,published'],
        ], [
            'kesulitan_aktif.required'   => 'Pilih minimal satu tingkat kesulitan (Arc) untuk petualangan kuis ini.',
            'kesulitan_aktif.min'        => 'Kuis petualangan harus memiliki minimal 1 tingkat kesulitan.',
            'kesulitan_aktif.max'        => 'Kuis petualangan maksimal memiliki 3 tingkat kesulitan (Mudah, Sedang, Sulit).',
            'waktu_mulai.after_or_equal' => 'Waktu mulai kuis tidak boleh sebelum hari ini.',
            'waktu_selesai.after'        => 'Waktu selesai kuis harus lebih lambat dari waktu mulai (tidak boleh sama atau lebih awal).',
        ]);

        $isOwner = PengampuKelas::where('id', $validated['pengampu_kelas_id'])
            ->whereHas('guruMapel', function ($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            })->exists();

        if (!$isOwner) {
            return back()->withInput()->with('error', 'Anda tidak memiliki hak akses pada kelas/mata pelajaran ini.');
        }

        $orderedDifficulties = array_values(array_intersect(Quiz::KESULITAN_LIST, $validated['kesulitan_aktif']));

        $quiz->update([
            'judul'             => $validated['judul'],
            'deskripsi'         => $validated['deskripsi'] ?? null,
            'pengampu_kelas_id' => $validated['pengampu_kelas_id'],
            'kesulitan_aktif'   => $orderedDifficulties,
            'waktu_mulai'       => $validated['waktu_mulai'] ?? null,
            'waktu_selesai'     => $validated['waktu_selesai'] ?? null,
            'acak_jawaban'      => $request->boolean('acak_jawaban'),
            'status'            => $validated['status'] ?? $quiz->status,
        ]);

        return redirect()->back()->with('success', 'Pengaturan kuis petualangan berhasil diperbarui!');
    }

    /**
     * Mengubah status kuis antara Draf dan Diterbitkan.
     */
    public function toggleStatus(Quiz $quiz)
    {
        $user = auth()->user();
        $guruId = $user->guru?->id ?? $user->guru_id;

        abort_unless($user->role === 'guru' && $guruId, 403);

        $this->authorizeGuru($quiz, $guruId);

        // Jika kuis sudah diterbitkan, guru boleh mengembalikannya menjadi draf.
        if ($quiz->status === 'published') {
            $quiz->update(['status' => 'draft']);

            return redirect()->back()
                ->with('success', 'Kuis berhasil diubah menjadi draf.');
        }

        // Pastikan kuis memiliki tingkat kesulitan aktif.
        $activeDifficulties = $quiz->kesulitan_aktif_array;

        if (empty($activeDifficulties)) {
            return redirect()->back()
                ->with('error', 'Aktifkan minimal satu tingkat kesulitan sebelum menerbitkan kuis.');
        }

        // Pastikan setiap tingkat kesulitan aktif memiliki soal.
        foreach ($activeDifficulties as $difficulty) {
            $hasQuestions = $quiz->soals()
                ->where('kesulitan', $difficulty)
                ->exists();

            if (!$hasQuestions) {
                return redirect()->back()
                    ->with(
                        'error',
                        "Tingkat kesulitan {$difficulty} belum memiliki soal. Tambahkan minimal satu soal terlebih dahulu."
                    );
            }
        }

        // Terbitkan hanya setelah validasi berhasil.
        $quiz->update(['status' => 'published']);

        return redirect()->back()
            ->with('success', 'Kuis berhasil diterbitkan untuk siswa.');
    }
    /**
     * Menghapus kuis beserta seluruh butir soalnya.
     */
    public function destroy(Quiz $quiz)
    {
        $user = auth()->user();
        $guruId = $user->guru?->id ?? $user->guru_id;
        abort_unless($user->role === 'guru' && $guruId, 403);

        $this->authorizeGuru($quiz, $guruId);

        // Hapus berkas gambar pendukung soal jika ada
        foreach ($quiz->soals as $soal) {
            if ($soal->gambar && Storage::disk('public')->exists($soal->gambar)) {
                Storage::disk('public')->delete($soal->gambar);
            }
        }

        $quiz->delete();

        return redirect()->route('guru.kuis.index')->with('success', 'Kuis petualangan berhasil dihapus!');
    }

    /**
     * Menghapus beberapa kuis sekaligus (Bulk Destroy).
     */
    public function bulkDestroy(Request $request)
    {
        $user = auth()->user();
        $guruId = $user->guru?->id ?? $user->guru_id;
        abort_unless($user->role === 'guru' && $guruId, 403);

        $validated = $request->validate([
            'quiz_ids'   => ['required', 'array', 'min:1'],
            'quiz_ids.*' => ['required', 'exists:quizzes,id'],
        ], [
            'quiz_ids.required' => 'Pilih minimal satu kuis untuk dihapus.',
            'quiz_ids.min'      => 'Pilih minimal satu kuis untuk dihapus.',
        ]);

        $quizzes = Quiz::whereIn('id', $validated['quiz_ids'])
            ->whereHas('pengampuKelas.guruMapel', function ($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            })
            ->with('soals')
            ->get();

        $count = $quizzes->count();

        if ($count === 0) {
            return redirect()->route('guru.kuis.index')
                ->with('error', 'Tidak ada kuis valid milik Anda yang dapat dihapus.');
        }

        DB::transaction(function () use ($quizzes) {
            foreach ($quizzes as $quiz) {
                foreach ($quiz->soals as $soal) {
                    if ($soal->gambar && Storage::disk('public')->exists($soal->gambar)) {
                        Storage::disk('public')->delete($soal->gambar);
                    }
                }
                $quiz->delete();
            }
        });

        return redirect()->route('guru.kuis.index')
            ->with('success', "{$count} kuis petualangan terpilih berhasil dihapus!");
    }

    /**
     * Menambahkan butir soal ke dalam level tertentu pada Arc kuis.
     * Sistem: Maksimal 10 butir soal per level.
     */
    public function storeSoal(Request $request, Quiz $quiz)
    {
        $user = auth()->user();
        $guruId = $user->guru?->id ?? $user->guru_id;
        abort_unless($user->role === 'guru' && $guruId, 403);

        $this->authorizeGuru($quiz, $guruId);

        $validated = $request->validate([
            'pertanyaan'    => ['required', 'string'],
            'tipe_soal'     => ['required', 'in:single_choice,multiple_choice,essay,matching'],
            'kesulitan'     => ['required', 'string', 'in:Mudah,Sedang,Sulit'],
            'level'         => ['required', 'string', 'in:Level 1,Level 2,Level 3,Level 4,Level 5,1,2,3,4,5'],
            'pilihan'       => ['required', 'array', 'min:2', 'max:6'],
            'kunci_jawaban' => ['required'],
            'bobot'         => ['nullable', 'numeric', 'min:1'],
            'gambar'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'pilihan.required' => 'Pilihan jawaban wajib diisi minimal 2 opsi.',
            'pilihan.min'      => 'Minimal pilihan jawaban adalah 2 opsi.',
            'pilihan.max'      => 'Maksimal pilihan jawaban adalah 6 opsi.',
            'kunci_jawaban.required' => 'Pilih kunci jawaban yang benar.',
        ]);

        // Standardisasi format level menjadi 'Level X'
        $levelNum = (int) preg_replace('/[^0-9]/', '', $validated['level']);
        if ($levelNum < 1 || $levelNum > Quiz::MAX_LEVEL) {
            return back()->withInput()->with('error', 'Level tidak valid. Maksimal 5 level per tingkat kesulitan.');
        }
        $levelFormatted = 'Level ' . $levelNum;
        $kesulitan = $validated['kesulitan'];

        // Cek apakah tingkat kesulitan (Arc) ini diaktifkan di kuis ini
        if (!in_array($kesulitan, $quiz->kesulitan_aktif_array)) {
            return back()->withInput()->with('error', "Arc tingkat kesulitan {$kesulitan} tidak aktif pada kuis ini.");
        }

        // Validasi batas maksimal 10 butir soal per level
        $currentCount = $quiz->soals()
            ->where('kesulitan', $kesulitan)
            ->where('level', $levelFormatted)
            ->count();

        if ($currentCount >= Quiz::MAX_SOAL) {
            return back()->withInput()->with('error', "Level ini ({$kesulitan} - {$levelFormatted}) sudah mencapai batas maksimal " . Quiz::MAX_SOAL . " butir soal.");
        }

        $gambarPath = null;
        if ($request->hasFile('gambar')) {
            $gambarPath = $request->file('gambar')->store('soals', 'public');
        }

        $nextUrutan = ($quiz->soals()
            ->where('kesulitan', $kesulitan)
            ->where('level', $levelFormatted)
            ->max('urutan') ?? 0) + 1;

        // Proses pilihan jawaban dinamis (2 sampai 6 opsi: a, b, c, d, e, f)
        $validKeys = ['a', 'b', 'c', 'd', 'e', 'f'];
        $pilihanFormatted = [];
        if (in_array($validated['tipe_soal'], ['single_choice', 'multiple_choice'])) {
            foreach ($validKeys as $k) {
                $val = $request->input("pilihan.{$k}");
                if ($val !== null && trim((string)$val) !== '') {
                    $pilihanFormatted[$k] = trim((string)$val);
                }
            }

            if (count($pilihanFormatted) < 2) {
                return back()->withInput()->with('error', 'Pilihan jawaban wajib diisi minimal 2 opsi (misal Pilihan A dan B).');
            }

            if (count($pilihanFormatted) > 6) {
                return back()->withInput()->with('error', 'Maksimal pilihan jawaban adalah 6 opsi (Pilihan A hingga F).');
            }
        }

        $kunci = $validated['kunci_jawaban'];
        if (is_array($kunci)) {
            $kunci = json_encode($kunci);
        } else {
            $kunci = strtolower(trim((string)$kunci));
            if (!empty($pilihanFormatted) && !array_key_exists($kunci, $pilihanFormatted)) {
                return back()->withInput()->with('error', 'Kunci jawaban yang dipilih harus merupakan salah satu dari opsi pilihan jawaban yang aktif.');
            }
        }

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
            'quiz_id'       => $quiz->id,
            'tugas_id'      => null,
            'ujian_id'      => null,
        ]);

        return redirect()->route('guru.kuis.show', [
            'quiz'  => $quiz,
            'arc'   => $kesulitan,
            'level' => $levelFormatted,
        ])->with('success', "Soal berhasil ditambahkan ke {$kesulitan} - {$levelFormatted}! (" . ($currentCount + 1) . "/" . Quiz::MAX_SOAL . " soal)");
    }

    /**
     * Memperbarui butir soal kuis.
     */
    public function updateSoal(Request $request, Quiz $quiz, Soal $soal)
    {
        $user = auth()->user();
        $guruId = $user->guru?->id ?? $user->guru_id;
        abort_unless($user->role === 'guru' && $guruId, 403);

        $this->authorizeGuru($quiz, $guruId);
        abort_unless($soal->quiz_id === $quiz->id, 404);

        $validated = $request->validate([
            'pertanyaan'    => ['required', 'string'],
            'tipe_soal'     => ['required', 'in:single_choice,multiple_choice,essay,matching'],
            'pilihan'       => ['required', 'array', 'min:2', 'max:6'],
            'kunci_jawaban' => ['required'],
            'bobot'         => ['nullable', 'numeric', 'min:1'],
            'gambar'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'pilihan.required' => 'Pilihan jawaban wajib diisi minimal 2 opsi.',
            'pilihan.min'      => 'Minimal pilihan jawaban adalah 2 opsi.',
            'pilihan.max'      => 'Maksimal pilihan jawaban adalah 6 opsi.',
            'kunci_jawaban.required' => 'Pilih kunci jawaban yang benar.',
        ]);

        $gambarPath = $soal->gambar;
        if ($request->hasFile('gambar')) {
            if ($soal->gambar && Storage::disk('public')->exists($soal->gambar)) {
                Storage::disk('public')->delete($soal->gambar);
            }
            $gambarPath = $request->file('gambar')->store('soals', 'public');
        }

        // Proses pilihan jawaban dinamis (2 sampai 6 opsi: a, b, c, d, e, f)
        $validKeys = ['a', 'b', 'c', 'd', 'e', 'f'];
        $pilihanFormatted = [];
        if (in_array($validated['tipe_soal'], ['single_choice', 'multiple_choice'])) {
            foreach ($validKeys as $k) {
                $val = $request->input("pilihan.{$k}");
                if ($val !== null && trim((string)$val) !== '') {
                    $pilihanFormatted[$k] = trim((string)$val);
                }
            }

            if (count($pilihanFormatted) < 2) {
                return back()->withInput()->with('error', 'Pilihan jawaban wajib diisi minimal 2 opsi (misal Pilihan A dan B).');
            }

            if (count($pilihanFormatted) > 6) {
                return back()->withInput()->with('error', 'Maksimal pilihan jawaban adalah 6 opsi (Pilihan A hingga F).');
            }
        }

        $kunci = $validated['kunci_jawaban'];
        if (is_array($kunci)) {
            $kunci = json_encode($kunci);
        } else {
            $kunci = strtolower(trim((string)$kunci));
            if (!empty($pilihanFormatted) && !array_key_exists($kunci, $pilihanFormatted)) {
                return back()->withInput()->with('error', 'Kunci jawaban yang dipilih harus merupakan salah satu dari opsi pilihan jawaban yang aktif.');
            }
        }

        $soal->update([
            'pertanyaan'    => $validated['pertanyaan'],
            'tipe_soal'     => $validated['tipe_soal'],
            'pilihan'       => $pilihanFormatted,
            'kunci_jawaban' => $kunci,
            'bobot'         => $validated['bobot'] ?? $soal->bobot,
            'gambar'        => $gambarPath,
        ]);

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
        $user = auth()->user();
        $guruId = $user->guru?->id ?? $user->guru_id;
        abort_unless($user->role === 'guru' && $guruId, 403);

        $this->authorizeGuru($quiz, $guruId);
        abort_unless($soal->quiz_id === $quiz->id, 404);

        $arc = $soal->kesulitan ?? 'Mudah';
        $level = $soal->level ?? 'Level 1';

        if ($soal->gambar && Storage::disk('public')->exists($soal->gambar)) {
            Storage::disk('public')->delete($soal->gambar);
        }

        $soal->delete();

        return redirect()->route('guru.kuis.show', [
            'quiz'  => $quiz,
            'arc'   => $arc,
            'level' => $level,
        ])->with('success', 'Butir soal berhasil dihapus! Slot soal kini kembali tersedia.');
    }

    /**
     * Helper verifikasi kepemilikan kuis oleh guru yang login.
     */
    private function authorizeGuru(Quiz $quiz, int $guruId): void
    {
        $isOwner = PengampuKelas::where('id', $quiz->pengampu_kelas_id)
            ->whereHas('guruMapel', function ($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            })->exists();

        abort_unless($isOwner, 403, 'Akses ditolak: Anda bukan pengampu kelas untuk kuis ini.');
    }

    // =========================================================================
    // FITUR INTEGRASI KUIS SISWA
    // =========================================================================

    /**
     * SISWA: Menampilkan daftar kuis petualangan yang dipublikasikan untuk kelas siswa.
     */
    public function siswaIndex(Request $request)
    {
        $user = auth()->user();
        $siswa = $user->siswa;
        abort_unless($user->role === 'siswa' && $siswa, 403, 'Akses khusus siswa.');

        $anggotaKelas = $siswa->anggotaKelases()?->latest()?->first();
        $kelas = $siswa->kelas ?? $anggotaKelas?->kelas;
        $kelasId = $kelas?->id;

        if (!$kelasId) {
            return view('Siswa.kuis.index', [
                'quizzes'       => collect(),
                'kelas'         => null,
                'mapels'        => collect(),
                'totalKuis'     => 0,
                'totalSelesai'  => 0,
                'totalTersedia' => 0,
                'rataRataNilai' => 0,
                'errorKelas'    => 'Data kelas belum terdaftar. Silakan hubungi admin sekolah.',
            ]);
        }

        $query = Quiz::whereHas('pengampuKelas', function ($q) use ($kelasId) {
                $q->where('kelas_id', $kelasId);
            })
            ->where('status', 'published')
            ->with([
                'pengampuKelas.guruMapel.mapel',
                'pengampuKelas.guruMapel.guru',
                'nilais' => function ($q) use ($siswa) {
                    $q->where('siswa_id', $siswa->id);
                },
            ])
            ->withCount('soals');

        // Filter search judul kuis
        if ($request->filled('search')) {
            $query->where('judul', 'like', '%' . $request->search . '%');
        }

        // Filter mapel
        if ($request->filled('mapel_id')) {
            $query->whereHas('pengampuKelas.guruMapel', function ($q) use ($request) {
                $q->where('mapel_id', $request->mapel_id);
            });
        }

        $allPublished = $query->latest()->get();

        // Olah status kuis untuk siswa
        $quizzes = $allPublished->map(function ($quiz) {
            $nilai = $quiz->nilais->first();
            $isCompleted = $nilai !== null;

            $computedStatus = 'tersedia';
            if ($isCompleted) {
                $computedStatus = 'selesai';
            } elseif ($quiz->waktu_mulai && now()->lessThan($quiz->waktu_mulai)) {
                $computedStatus = 'upcoming';
            } elseif ($quiz->waktu_selesai && now()->greaterThan($quiz->waktu_selesai)) {
                $computedStatus = 'expired';
            }

            $quiz->student_status = $computedStatus;
            $quiz->riwayat_nilai = $nilai;
            return $quiz;
        });

        // Filter status tab
        if ($request->filled('status')) {
            $filterStatus = $request->status;
            $quizzes = $quizzes->filter(function ($q) use ($filterStatus) {
                return $q->student_status === $filterStatus;
            });
        }

        // Ringkasan Statistik
        $totalKuis = $allPublished->count();
        $totalSelesai = $allPublished->filter(fn($q) => $q->nilais->isNotEmpty())->count();
        $totalTersedia = $allPublished->filter(function ($q) {
            return $q->nilais->isEmpty() &&
                (!$q->waktu_mulai || now()->greaterThanOrEqualTo($q->waktu_mulai)) &&
                (!$q->waktu_selesai || now()->lessThanOrEqualTo($q->waktu_selesai));
        })->count();

        $avgNilai = Nilai::where('siswa_id', $siswa->id)
            ->whereNotNull('quiz_id')
            ->whereNotNull('nilai')
            ->avg('nilai');
        $rataRataNilai = $avgNilai !== null ? round((float)$avgNilai, 1) : 0;

        // Daftar Mata Pelajaran untuk filter dropdown
        $mapels = $allPublished->pluck('pengampuKelas.guruMapel.mapel')->filter()->unique('id')->values();

        return view('Siswa.kuis.index', compact(
            'quizzes',
            'kelas',
            'mapels',
            'totalKuis',
            'totalSelesai',
            'totalTersedia',
            'rataRataNilai'
        ));
    }

    /**
     * SISWA: Halaman pengantar kuis (briefing) sebelum mulai pengerjaan.
     */
    public function siswaShow(Quiz $quiz)
    {
        $user = auth()->user();
        $siswa = $user->siswa;
        abort_unless($user->role === 'siswa' && $siswa, 403, 'Akses khusus siswa.');

        $this->authorizeSiswaClass($quiz, $siswa);
        abort_unless($quiz->isPublished(), 404, 'Kuis belum dipublikasikan oleh guru.');

        $quiz->load(['pengampuKelas.guruMapel.mapel', 'pengampuKelas.guruMapel.guru', 'pengampuKelas.kelas']);
        $quiz->loadCount('soals');

        $nilai = Nilai::where('siswa_id', $siswa->id)
            ->where('quiz_id', $quiz->id)
            ->first();

        $soalCountsByArc = [];
        foreach ($quiz->kesulitan_aktif_array as $arc) {
            $soalCountsByArc[$arc] = $quiz->soals()->where('kesulitan', $arc)->count();
        }

        return view('Siswa.kuis.show', compact('quiz', 'nilai', 'soalCountsByArc'));
    }

    /**
     * SISWA: Halaman pengerjaan kuis interaktif dengan timer dan pengacakan opsi.
     */
    public function siswaKerjakan(Quiz $quiz)
    {
        $user = auth()->user();
        $siswa = $user->siswa;
        abort_unless($user->role === 'siswa' && $siswa, 403, 'Akses khusus siswa.');

        $this->authorizeSiswaClass($quiz, $siswa);
        abort_unless($quiz->isPublished(), 403, 'Kuis belum dipublikasikan.');

        // Cegah pengerjaan ulang jika sudah menyelesaikan
        $nilai = Nilai::where('siswa_id', $siswa->id)->where('quiz_id', $quiz->id)->first();
        if ($nilai) {
            return redirect()->route('siswa.kuis.hasil', $quiz->id)
                ->with('warning', 'Kamu sudah menyelesaikan kuis ini.');
        }

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

        // Ambil butir pertanyaan kuis
        $soals = $quiz->soals()->orderBy('urutan')->orderBy('id')->get();
        if ($soals->isEmpty()) {
            return redirect()->route('siswa.kuis.show', $quiz->id)
                ->with('error', 'Kuis ini belum memiliki butir soal untuk dikerjakan.');
        }

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

        // Hitung sisa waktu mundur dalam detik jika waktu selesai ditetapkan
        $remainingSeconds = null;
        if ($quiz->waktu_selesai) {
            $remainingSeconds = max(0, now()->diffInSeconds($quiz->waktu_selesai, false));
        }

        $quiz->load(['pengampuKelas.guruMapel.mapel', 'pengampuKelas.guruMapel.guru']);

        return view('Siswa.kuis.kerjakan', compact('quiz', 'soals', 'remainingSeconds'));
    }

    /**
     * SISWA: Memproses jawaban kuis, koreksi otomatis, dan rekap penilaian.
     */
    public function siswaSubmit(Request $request, Quiz $quiz)
    {
        $user = auth()->user();
        $siswa = $user->siswa;
        abort_unless($user->role === 'siswa' && $siswa, 403, 'Akses ditolak.');

        $this->authorizeSiswaClass($quiz, $siswa);
        abort_unless($quiz->isPublished(), 403, 'Kuis belum dipublikasikan.');

        // Cegah duplikasi submit
        $existing = Nilai::where('siswa_id', $siswa->id)->where('quiz_id', $quiz->id)->first();
        if ($existing) {
            return redirect()->route('siswa.kuis.hasil', $quiz->id)
                ->with('warning', 'Jawaban kamu sebelumnya sudah tersimpan.');
        }

        // Cek keterlambatan pengumpulan (toleransi 2 menit)
        if ($quiz->waktu_selesai && now()->greaterThan($quiz->waktu_selesai->copy()->addMinutes(2))) {
            return redirect()->route('siswa.kuis.show', $quiz->id)
                ->with('error', 'Waktu pengumpulan kuis telah kedaluwarsa.');
        }

        $soals = $quiz->soals;
        $totalBobot = $soals->sum('bobot') ?: 100;
        $skorDidapat = 0;
        $jumlahBenar = 0;

        DB::transaction(function () use ($soals, $request, $siswa, $quiz, &$skorDidapat, &$jumlahBenar, $totalBobot) {
            foreach ($soals as $soal) {
                $jawabanUser = $request->input("jawaban.{$soal->id}");
                $isBenar = false;

                if ($jawabanUser !== null && $jawabanUser !== '') {
                    $kunci = $soal->kunci_jawaban;
                    if (is_array($kunci)) {
                        $cleanKunci = array_map(fn($k) => strtolower(trim((string)$k)), $kunci);
                        $isBenar = in_array(strtolower(trim((string)$jawabanUser)), $cleanKunci);
                    } else {
                        $isBenar = strtolower(trim((string)$jawabanUser)) === strtolower(trim((string)$kunci));
                    }

                    if ($isBenar) {
                        $skorDidapat += ($soal->bobot ?: 10);
                        $jumlahBenar++;
                    }
                }

                Menjawab::updateOrCreate(
                    [
                        'soal_id'  => $soal->id,
                        'siswa_id' => $siswa->id,
                    ],
                    [
                        'jawaban_dipilih' => is_array($jawabanUser) ? json_encode($jawabanUser) : (string)$jawabanUser,
                        'benar'           => $isBenar,
                    ]
                );
            }

            // Normalisasi nilai ke skala 100
            $nilaiAkhir = $totalBobot > 0 ? round(($skorDidapat / $totalBobot) * 100, 2) : 0;
            if ($nilaiAkhir > 100) $nilaiAkhir = 100;

            // Konversi durasi ke detik integer (kolom integer pada tabel nilais)
            $rawDurasi = $request->input('durasi', 0);
            $durasiDetik = 0;
            if (is_numeric($rawDurasi)) {
                $durasiDetik = (int)$rawDurasi;
            } elseif (is_string($rawDurasi) && strpos($rawDurasi, ':') !== false) {
                $parts = explode(':', trim($rawDurasi));
                if (count($parts) === 2) {
                    $durasiDetik = ((int)$parts[0] * 60) + (int)$parts[1];
                } elseif (count($parts) === 3) {
                    $durasiDetik = ((int)$parts[0] * 3600) + ((int)$parts[1] * 60) + (int)$parts[2];
                }
            }

            Nilai::updateOrCreate(
                [
                    'siswa_id' => $siswa->id,
                    'quiz_id'  => $quiz->id,
                ],
                [
                    'nilai'  => $nilaiAkhir,
                    'durasi' => $durasiDetik,
                    'status' => 'selesai',
                ]
            );
        });

        // Kirim notifikasi interaktif ke Guru pengampu
        $guruUser = $quiz->pengampuKelas?->guruMapel?->guru?->user;
        if ($guruUser) {
            $namaSiswa = $siswa->nama_siswa ?? $user->name;
            $namaKelas = $quiz->pengampuKelas?->kelas?->nama_kelas ?? 'Kelas';
            Notifikasi::kirim(
                $guruUser->id,
                "Kuis Selesai Dikerjakan: {$quiz->judul}",
                "Siswa {$namaSiswa} ({$namaKelas}) telah menyelesaikan kuis petualangan '{$quiz->judul}'.",
                'kuis_selesai',
                route('guru.kuis.show', $quiz->id),
                $quiz->id,
                ['siswa_id' => $siswa->id, 'quiz_id' => $quiz->id]
            );
        }

        return redirect()->route('siswa.kuis.hasil', $quiz->id)
            ->with('success', 'Selamat! Kuis petualangan berhasil kamu selesaikan!');
    }

    /**
     * SISWA: Menampilkan hasil akhir penilaian kuis siswa.
     */
    public function siswaHasil(Quiz $quiz)
    {
        $user = auth()->user();
        $siswa = $user->siswa;
        abort_unless($user->role === 'siswa' && $siswa, 403, 'Akses ditolak.');

        $this->authorizeSiswaClass($quiz, $siswa);

        $nilai = Nilai::where('siswa_id', $siswa->id)->where('quiz_id', $quiz->id)->first();
        if (!$nilai) {
            return redirect()->route('siswa.kuis.show', $quiz->id)
                ->with('warning', 'Kamu belum mengerjakan kuis ini.');
        }

        $quiz->load(['pengampuKelas.guruMapel.mapel', 'pengampuKelas.guruMapel.guru', 'pengampuKelas.kelas']);

        $soals = $quiz->soals()->with(['menjawabs' => fn($q) => $q->where('siswa_id', $siswa->id)])->get();

        $totalSoal = $soals->count();
        $totalBenar = $soals->filter(fn($s) => $s->menjawabs->first()?->benar)->count();
        $totalSalah = $totalSoal - $totalBenar;

        return view('Siswa.kuis.hasil', compact('quiz', 'nilai', 'soals', 'totalSoal', 'totalBenar', 'totalSalah'));
    }

    /**
     * Helper verifikasi bahwa kuis ditujukan untuk kelas siswa yang login.
     */
    private function authorizeSiswaClass(Quiz $quiz, Siswa $siswa): void
    {
        $anggotaKelas = $siswa->anggotaKelases()?->latest()?->first();
        $kelasId = $siswa->kelas?->id ?? $anggotaKelas?->kelas_id;
        $quizKelasId = $quiz->pengampuKelas?->kelas_id;

        abort_unless($kelasId && $quizKelasId && $kelasId === $quizKelasId, 403, 'Akses ditolak: Kuis ini bukan untuk kelas Anda.');
    }
}

