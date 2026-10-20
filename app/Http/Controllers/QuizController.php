<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Soal;
use App\Models\PengampuKelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            'status'              => ['nullable', 'in:draft,published'],
        ], [
            'kesulitan_aktif.required' => 'Pilih minimal satu tingkat kesulitan (Arc) untuk petualangan kuis ini.',
            'kesulitan_aktif.min'      => 'Kuis petualangan harus memiliki minimal 1 tingkat kesulitan.',
            'kesulitan_aktif.max'      => 'Kuis petualangan maksimal memiliki 3 tingkat kesulitan (Mudah, Sedang, Sulit).',
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
            'status'              => ['nullable', 'in:draft,published'],
        ], [
            'kesulitan_aktif.required' => 'Pilih minimal satu tingkat kesulitan (Arc) untuk petualangan kuis ini.',
            'kesulitan_aktif.min'      => 'Kuis petualangan harus memiliki minimal 1 tingkat kesulitan.',
            'kesulitan_aktif.max'      => 'Kuis petualangan maksimal memiliki 3 tingkat kesulitan (Mudah, Sedang, Sulit).',
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
            'status'            => $validated['status'] ?? $quiz->status,
        ]);

        return redirect()->back()->with('success', 'Pengaturan kuis petualangan berhasil diperbarui!');
    }

    /**
     * Mengubah status kuis antara Draf dan Diterbitkan (Published).
     */
    public function toggleStatus(Quiz $quiz)
    {
        $user = auth()->user();
        $guruId = $user->guru?->id ?? $user->guru_id;
        abort_unless($user->role === 'guru' && $guruId, 403);

        $this->authorizeGuru($quiz, $guruId);

        $newStatus = ($quiz->status === 'published') ? 'draft' : 'published';
        $quiz->update(['status' => $newStatus]);

        $statusText = $newStatus === 'published' ? 'diterbitkan untuk siswa' : 'diubah menjadi draf';
        return redirect()->back()->with('success', "Status kuis berhasil {$statusText}.");
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
            'pilihan'       => ['nullable', 'array'],
            'kunci_jawaban' => ['required'],
            'bobot'         => ['nullable', 'numeric', 'min:1'],
            'gambar'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
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

        $pilihanFormatted = null;
        if (in_array($validated['tipe_soal'], ['single_choice', 'multiple_choice'])) {
            $pilihanFormatted = [
                'a' => $request->input('pilihan.a', ''),
                'b' => $request->input('pilihan.b', ''),
                'c' => $request->input('pilihan.c', ''),
                'd' => $request->input('pilihan.d', ''),
            ];
        }

        $kunci = $validated['kunci_jawaban'];
        if (is_array($kunci)) {
            $kunci = json_encode($kunci);
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
            'pilihan'       => ['nullable', 'array'],
            'kunci_jawaban' => ['required'],
            'bobot'         => ['nullable', 'numeric', 'min:1'],
            'gambar'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $gambarPath = $soal->gambar;
        if ($request->hasFile('gambar')) {
            if ($soal->gambar && Storage::disk('public')->exists($soal->gambar)) {
                Storage::disk('public')->delete($soal->gambar);
            }
            $gambarPath = $request->file('gambar')->store('soals', 'public');
        }

        $pilihanFormatted = $soal->pilihan;
        if (in_array($validated['tipe_soal'], ['single_choice', 'multiple_choice'])) {
            $pilihanFormatted = [
                'a' => $request->input('pilihan.a', ''),
                'b' => $request->input('pilihan.b', ''),
                'c' => $request->input('pilihan.c', ''),
                'd' => $request->input('pilihan.d', ''),
            ];
        }

        $kunci = $validated['kunci_jawaban'];
        if (is_array($kunci)) {
            $kunci = json_encode($kunci);
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
}
