<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Nilai;
use App\Models\Notifikasi;
use App\Models\PengampuKelas;
use App\Models\Quiz;
use App\Models\Soal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
}

