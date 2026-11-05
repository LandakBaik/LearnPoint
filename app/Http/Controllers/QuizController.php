<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Soal;
use App\Models\PengampuKelas;
use App\Models\Nilai;
use App\Models\Menjawab;
use App\Models\Notifikasi;
use App\Models\Siswa;
use App\Models\Mapel;
use App\Models\Kelas;
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

        // Data terstruktur untuk pemilihan cascading Sasaran Kelas (Mapel -> Tingkatan -> Kelas)
        $mapelData = [];
        foreach ($pengampuKelases as $pk) {
            $mapel = $pk->guruMapel?->mapel;
            $kelas = $pk->kelas;
            if (!$mapel || !$kelas) continue;

            if (!isset($mapelData[$mapel->id])) {
                $mapelData[$mapel->id] = [
                    'id' => $mapel->id,
                    'nama' => $mapel->nama_mapel,
                    'tingkatans' => []
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

        usort($mapelData, fn($a, $b) => strcmp($a['nama'], $b['nama']));

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
            'pengampu_kelas_id'   => ['nullable', 'exists:pengampu_kelas,id'],
            'pengampu_kelas_ids'  => ['nullable', 'array', 'min:1'],
            'pengampu_kelas_ids.*'=> ['exists:pengampu_kelas,id'],
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

        $targetIds = [];
        if ($request->filled('pengampu_kelas_ids')) {
            $targetIds = (array) $request->input('pengampu_kelas_ids');
        } elseif ($request->filled('pengampu_kelas_id')) {
            $targetIds = [$request->input('pengampu_kelas_id')];
        }

        if (empty($targetIds)) {
            return back()->withInput()->with('error', 'Silakan pilih minimal satu kelas sasaran untuk kuis ini.');
        }

        $validPengampu = PengampuKelas::whereIn('id', $targetIds)
            ->whereHas('guruMapel', function ($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            })->pluck('id')->all();

        if (empty($validPengampu)) {
            return back()->withInput()->with('error', 'Anda tidak memiliki hak akses pada kelas/mata pelajaran yang dipilih.');
        }

        // Urutkan kesulitan agar konsisten: Mudah, Sedang, Sulit
        $orderedDifficulties = array_values(array_intersect(Quiz::KESULITAN_LIST, $validated['kesulitan_aktif']));

        $firstQuiz = null;
        $createdCount = 0;
        foreach ($validPengampu as $pkId) {
            $quiz = Quiz::create([
                'judul'             => $validated['judul'],
                'deskripsi'         => $validated['deskripsi'] ?? null,
                'pengampu_kelas_id' => $pkId,
                'kesulitan_aktif'   => $orderedDifficulties,
                'waktu_mulai'       => $validated['waktu_mulai'] ?? null,
                'waktu_selesai'     => $validated['waktu_selesai'] ?? null,
                'acak_jawaban'      => $request->boolean('acak_jawaban'),
                'status'            => $validated['status'] ?? 'draft',
                'level'             => 'Level 1',
                'kesulitan'         => $orderedDifficulties[0] ?? 'Mudah',
            ]);

            if (!$firstQuiz) {
                $firstQuiz = $quiz;
            }
            $createdCount++;
        }

        $msg = $createdCount > 1
            ? "Draf kuis '{$firstQuiz->judul}' berhasil dibuat untuk {$createdCount} kelas!"
            : "Draf kuis '{$firstQuiz->judul}' berhasil dibuat! Silakan kelola arc, level tantangan, dan butir soal di bawah.";

        return redirect()->route('guru.kuis.show', $firstQuiz)->with('success', $msg);
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

        return view('guru.kelola_kuis.show', compact(
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
            'pilihan'       => ['required', 'array'],
            'pilihan.a'     => ['required', 'string'],
            'pilihan.b'     => ['required', 'string'],
            'pilihan.c'     => ['required', 'string'],
            'pilihan.d'     => ['required', 'string'],
            'kunci_jawaban' => ['required', 'string', 'in:a,b,c,d,A,B,C,D'],
            'bobot'         => ['nullable', 'numeric', 'min:1'],
            'gambar'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'pilihan.required'   => 'Pilihan jawaban wajib diisi lengkap 4 opsi (A, B, C, dan D).',
            'pilihan.a.required' => 'Pilihan jawaban A wajib diisi.',
            'pilihan.b.required' => 'Pilihan jawaban B wajib diisi.',
            'pilihan.c.required' => 'Pilihan jawaban C wajib diisi.',
            'pilihan.d.required' => 'Pilihan jawaban D wajib diisi.',
            'kunci_jawaban.required' => 'Pilih kunci jawaban yang benar.',
            'kunci_jawaban.in'   => 'Kunci jawaban harus merupakan salah satu dari opsi A, B, C, atau D.',
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

        // Format pilihan jawaban fix 4 opsi (a, b, c, d)
        $validKeys = ['a', 'b', 'c', 'd'];
        $pilihanFormatted = [];
        foreach ($validKeys as $k) {
            $val = $request->input("pilihan.{$k}");
            if ($val === null || trim((string)$val) === '') {
                return back()->withInput()->with('error', 'Semua 4 opsi pilihan jawaban (A, B, C, dan D) wajib diisi.');
            }
            $pilihanFormatted[$k] = trim((string)$val);
        }

        $kunci = strtolower(trim((string)$validated['kunci_jawaban']));
        if (!in_array($kunci, $validKeys)) {
            return back()->withInput()->with('error', 'Kunci jawaban yang dipilih harus salah satu dari opsi A, B, C, atau D.');
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
            'pilihan'       => ['required', 'array'],
            'pilihan.a'     => ['required', 'string'],
            'pilihan.b'     => ['required', 'string'],
            'pilihan.c'     => ['required', 'string'],
            'pilihan.d'     => ['required', 'string'],
            'kunci_jawaban' => ['required', 'string', 'in:a,b,c,d,A,B,C,D'],
            'bobot'         => ['nullable', 'numeric', 'min:1'],
            'gambar'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'pilihan.required'   => 'Pilihan jawaban wajib diisi lengkap 4 opsi (A, B, C, dan D).',
            'pilihan.a.required' => 'Pilihan jawaban A wajib diisi.',
            'pilihan.b.required' => 'Pilihan jawaban B wajib diisi.',
            'pilihan.c.required' => 'Pilihan jawaban C wajib diisi.',
            'pilihan.d.required' => 'Pilihan jawaban D wajib diisi.',
            'kunci_jawaban.required' => 'Pilih kunci jawaban yang benar.',
            'kunci_jawaban.in'   => 'Kunci jawaban harus merupakan salah satu dari opsi A, B, C, atau D.',
        ]);

        $gambarPath = $soal->gambar;
        if ($request->hasFile('gambar')) {
            if ($soal->gambar && Storage::disk('public')->exists($soal->gambar)) {
                Storage::disk('public')->delete($soal->gambar);
            }
            $gambarPath = $request->file('gambar')->store('soals', 'public');
        }

        // Format pilihan jawaban fix 4 opsi (a, b, c, d)
        $validKeys = ['a', 'b', 'c', 'd'];
        $pilihanFormatted = [];
        foreach ($validKeys as $k) {
            $val = $request->input("pilihan.{$k}");
            if ($val === null || trim((string)$val) === '') {
                return back()->withInput()->with('error', 'Semua 4 opsi pilihan jawaban (A, B, C, dan D) wajib diisi.');
            }
            $pilihanFormatted[$k] = trim((string)$val);
        }

        $kunci = strtolower(trim((string)$validated['kunci_jawaban']));
        if (!in_array($kunci, $validKeys)) {
            return back()->withInput()->with('error', 'Kunci jawaban yang dipilih harus salah satu dari opsi A, B, C, atau D.');
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
     * Terhubung langsung dengan kuis yang dibuat dan dipublikasikan oleh Guru di database.
     * Jika data di database belum ada (kosong), otomatis menggunakan data dummy agar halaman selalu tampil persis desain mockup.
     */
    public function siswaIndex(Request $request)
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
        // HANYA data riil dari database (tanpa data dummy, dikosongkan jika belum ada data)
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

        // Jika planet dipilih: Tampilkan Mode 2 (Tahapan Penyelamatan & Target - Gambar 2)
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
                $nilai = $quiz->nilais->first();
                $isCompleted = $nilai !== null;
                $skor = $isCompleted ? (float)$nilai->nilai : 0;
                $stars = $skor >= 100 ? 3 : ($skor >= 80 ? 2 : ($skor > 0 ? 1 : 0));

                $zonas->push((object)[
                    'quiz' => $quiz,
                    'zona_nomor' => $zoneIndex,
                    'judul' => 'Zona ' . $zoneIndex . ': ' . $quiz->judul,
                    'deskripsi' => $quiz->deskripsi ?: 'Kalkulasi sudut siku-siku & teorema hipotenusa untuk menutup celah retakan dimensi.',
                    'status' => $isCompleted ? 'selesai' : 'belum_dikerjakan',
                    'is_completed' => $isCompleted,
                    'skor' => $skor,
                    'bintang' => $stars,
                    'soals_count' => $quiz->soals_count ?? $quiz->soals->count(),
                    'level_label' => ($quiz->soals_count > 0 ? $quiz->soals_count . ' Soal' : '30 Level'),
                    'buka_level_label' => 'Buka Level 26 Sekarang',
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

            // Leaderboard terhubung dengan data Nilai asli di database (tanpa data dummy)
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

        // Tampilan Mode 1 (Pilihan Planet - Gambar 1)
        return view('siswa.kuis.index', [
            'viewMode' => 'planets',
            'planets' => $planets,
            'kelas' => $kelas,
            'top1' => $top1,
            'top2' => $top2,
            'top3' => $top3,
        ]);
    }

    /**
     * SISWA: Halaman pengantar kuis (briefing) sebelum mulai pengerjaan.
     */
    public function siswaShow(Quiz $quiz)
    {
        $user = auth()->user();
        $siswa = $user?->siswa;

        $mapelId = $quiz->pengampuKelas?->guruMapel?->mapel_id ?? 1;
        $request = request();
        $request->merge(['mapel_id' => $mapelId, 'quiz_id' => $quiz->id]);

        return $this->siswaIndex($request);
    }

    /**
     * SISWA: Halaman pengerjaan kuis interaktif dengan timer dan pengacakan opsi.
     */
    public function siswaKerjakan(Quiz $quiz)
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

        // Ambil butir pertanyaan kuis dari guru
        $soals = $quiz->soals()->orderBy('urutan')->orderBy('id')->get();

        // JIKA GURU BELUM MENAMBAHKAN SOAL, SIAPKAN DUMMY SOAL AGAR DAPAT DIUJI COBA
        if ($soals->isEmpty()) {
            $soals = collect([
                (object)[
                    'id' => 9001,
                    'pertanyaan' => 'Sebuah segitiga siku-siku memiliki panjang alas 6 cm dan tinggi 8 cm. Berapakah panjang sisi hipotenusa segitiga tersebut?',
                    'tipe_soal' => 'single_choice',
                    'kesulitan' => 'Mudah',
                    'level' => 'Level 1',
                    'pilihan' => [
                        'a' => '10 cm',
                        'b' => '12 cm',
                        'c' => '14 cm',
                        'd' => '15 cm',
                    ],
                    'pilihan_shuffled' => [
                        'a' => '10 cm',
                        'b' => '12 cm',
                        'c' => '14 cm',
                        'd' => '15 cm',
                    ],
                    'kunci_jawaban' => 'a',
                    'bobot' => 50,
                    'gambar' => null,
                ],
                (object)[
                    'id' => 9002,
                    'pertanyaan' => 'Jika diketahui persamaan linear 3x + 12 = 27, maka berapakah nilai x yang memenuhi persamaan tersebut?',
                    'tipe_soal' => 'single_choice',
                    'kesulitan' => 'Sedang',
                    'level' => 'Level 2',
                    'pilihan' => [
                        'a' => '5',
                        'b' => '4',
                        'c' => '6',
                        'd' => '7',
                    ],
                    'pilihan_shuffled' => [
                        'a' => '5',
                        'b' => '4',
                        'c' => '6',
                        'd' => '7',
                    ],
                    'kunci_jawaban' => 'a',
                    'bobot' => 50,
                    'gambar' => null,
                ],
            ]);
        } else {
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
        }

        $savedAnswers = Menjawab::where('siswa_id', $siswa->id)
            ->whereIn('soal_id', $soals->pluck('id'))
            ->pluck('jawaban_dipilih', 'soal_id')
            ->toArray();

        $remainingSeconds = null;
        if ($quiz->waktu_selesai) {
            $remainingSeconds = max(0, now()->diffInSeconds($quiz->waktu_selesai, false));
        }

        $quiz->load(['pengampuKelas.guruMapel.mapel', 'pengampuKelas.guruMapel.guru']);

        return view('siswa.kuis.kerjakan', compact('quiz', 'soals', 'remainingSeconds', 'savedAnswers'));
    }



    /**
     * SISWA: Menyimpan progres jawaban kuis sementara (draft) dan keluar tanpa menyelesaikan,
     * sehingga siswa dapat melanjutkan mengerjakan nanti.
     */
    public function siswaSimpanDraft(Request $request, Quiz $quiz)
    {
        $user = auth()->user();
        $siswa = $user->siswa;
        abort_unless($user->role === 'siswa' && $siswa, 403, 'Akses ditolak.');

        $this->authorizeSiswaClass($quiz, $siswa);
        abort_unless($quiz->isPublished(), 403, 'Kuis belum dipublikasikan.');

        // Cegah jika sudah selesai dinilai
        $existing = Nilai::where('siswa_id', $siswa->id)->where('quiz_id', $quiz->id)->first();
        if ($existing) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kuis sudah pernah diselesaikan sebelumnya.'
                ], 400);
            }
            return redirect()->route('siswa.kuis.hasil', $quiz->id)
                ->with('warning', 'Jawaban kamu sebelumnya sudah tersimpan.');
        }

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
                        $cleanKunci = array_map(fn($k) => strtolower(trim((string)$k)), $kunci);
                        $isBenar = in_array(strtolower(trim((string)$jawabanUser)), $cleanKunci);
                    } else {
                        $isBenar = strtolower(trim((string)$jawabanUser)) === strtolower(trim((string)$kunci));
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
            }
        });

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'      => true,
                'message'      => 'Progres jawaban kuis berhasil disimpan! Kamu dapat melanjutkan pengerjaan kapan saja.',
                'redirect_url' => route('siswa.kuis'),
            ]);
        }

        return redirect()->route('siswa.kuis')
            ->with('success', 'Progres jawaban kuis berhasil disimpan! Kamu dapat melanjutkan pengerjaan nanti.');
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
        if ($soals->isEmpty()) {
            $nilaiAkhir = 100;
            $durasiDetik = (int)$request->input('durasi', 120);

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

        $totalBobot = $soals->sum('bobot') ?: 100;
        $skorDidapat = 0;
        $jumlahBenar = 0;

        DB::transaction(function () use ($soals, $request, $siswa, $quiz, &$skorDidapat, &$jumlahBenar, $totalBobot) {
            foreach ($soals as $soal) {
                $jawabanUser = $request->input("jawaban.{$soal->id}");

                // Jika tidak ada di request, coba ambil dari draft tersimpan sebelumnya
                if ($jawabanUser === null || $jawabanUser === '') {
                    $existingMenjawab = Menjawab::where('soal_id', $soal->id)->where('siswa_id', $siswa->id)->first();
                    if ($existingMenjawab && $existingMenjawab->jawaban_dipilih) {
                        $jawabanUser = $existingMenjawab->jawaban_dipilih;
                    }
                }

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

        $totalSoal = max(1, $soals->count());
        $totalBenar = $soals->isEmpty() ? 1 : $soals->filter(fn($s) => $s->menjawabs->first()?->benar)->count();
        $totalSalah = max(0, $totalSoal - $totalBenar);

        return view('siswa.kuis.hasil', compact('quiz', 'nilai', 'soals', 'totalSoal', 'totalBenar', 'totalSalah'));
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

