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
     * Menampilkan daftar semua kuis milik guru yang login.
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

        // Search
        if ($request->filled('search')) {
            $query->where('judul', 'like', '%' . $request->search . '%');
        }

        // Filter Kesulitan
        if ($request->filled('kesulitan')) {
            $query->where('kesulitan', $request->kesulitan);
        }

        // Filter Level / Kelas
        if ($request->filled('kelas_id')) {
            $query->whereHas('pengampuKelas', function ($q) use ($request) {
                $q->where('kelas_id', $request->kelas_id);
            });
        }

        $quizzes = $query->latest()->paginate(10)->withQueryString();

        // Pengampu kelas for dropdown
        $pengampuKelases = PengampuKelas::with(['guruMapel.mapel', 'kelas'])
            ->whereHas('guruMapel', function ($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            })
            ->get();

        $kelases = $pengampuKelases->pluck('kelas')->unique('id')->filter();

        // Statistik
        $totalKuis = Quiz::whereHas('pengampuKelas.guruMapel', function ($q) use ($guruId) {
            $q->where('guru_id', $guruId);
        })->count();

        $totalSoal = Soal::whereHas('quiz.pengampuKelas.guruMapel', function ($q) use ($guruId) {
            $q->where('guru_id', $guruId);
        })->count();

        $kuisMudah = Quiz::whereHas('pengampuKelas.guruMapel', function ($q) use ($guruId) {
            $q->where('guru_id', $guruId);
        })->where('kesulitan', 'Mudah')->count();

        $kuisSulit = Quiz::whereHas('pengampuKelas.guruMapel', function ($q) use ($guruId) {
            $q->where('guru_id', $guruId);
        })->where('kesulitan', 'Sulit')->count();

        return view('guru.kuis.index', compact(
            'quizzes',
            'pengampuKelases',
            'kelases',
            'totalKuis',
            'totalSoal',
            'kuisMudah',
            'kuisSulit'
        ));
    }

    /**
     * Menyimpan kuis baru.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $guruId = $user->guru?->id ?? $user->guru_id;
        abort_unless($user->role === 'guru' && $guruId, 403);

        $validated = $request->validate([
            'judul'             => ['required', 'string', 'max:200'],
            'level'             => ['required', 'string', 'max:50'],
            'kesulitan'         => ['required', 'string', 'max:50'],
            'pengampu_kelas_id' => ['required', 'exists:pengampu_kelas,id'],
        ]);

        $isOwner = PengampuKelas::where('id', $validated['pengampu_kelas_id'])
            ->whereHas('guruMapel', function ($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            })->exists();

        if (!$isOwner) {
            return back()->with('error', 'Anda tidak memiliki hak akses pada kelas/mata pelajaran ini.');
        }

        $quiz = Quiz::create($validated);

        return redirect()->route('guru.kuis.show', $quiz)->with('success', 'Kuis berhasil dibuat! Silakan tambahkan butir soal di bawah.');
    }

    /**
     * Menampilkan detail kuis & daftar soal.
     */
    public function show(Quiz $quiz)
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

        return view('guru.kuis.show', compact('quiz'));
    }

    /**
     * Memperbarui kuis.
     */
    public function update(Request $request, Quiz $quiz)
    {
        $user = auth()->user();
        $guruId = $user->guru?->id ?? $user->guru_id;
        abort_unless($user->role === 'guru' && $guruId, 403);

        $this->authorizeGuru($quiz, $guruId);

        $validated = $request->validate([
            'judul'             => ['required', 'string', 'max:200'],
            'level'             => ['required', 'string', 'max:50'],
            'kesulitan'         => ['required', 'string', 'max:50'],
            'pengampu_kelas_id' => ['required', 'exists:pengampu_kelas,id'],
        ]);

        $isOwner = PengampuKelas::where('id', $validated['pengampu_kelas_id'])
            ->whereHas('guruMapel', function ($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            })->exists();

        if (!$isOwner) {
            return back()->with('error', 'Anda tidak memiliki hak akses pada kelas/mata pelajaran ini.');
        }

        $quiz->update($validated);

        return redirect()->route('guru.kuis.index')->with('success', 'Kuis berhasil diperbarui!');
    }

    /**
     * Menghapus kuis.
     */
    public function destroy(Quiz $quiz)
    {
        $user = auth()->user();
        $guruId = $user->guru?->id ?? $user->guru_id;
        abort_unless($user->role === 'guru' && $guruId, 403);

        $this->authorizeGuru($quiz, $guruId);

        $quiz->delete();

        return redirect()->route('guru.kuis.index')->with('success', 'Kuis berhasil dihapus!');
    }

    /**
     * Menambahkan butir soal ke dalam Kuis.
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
            'pilihan'       => ['nullable', 'array'],
            'kunci_jawaban' => ['required'],
            'bobot'         => ['nullable', 'numeric', 'min:1'],
            'gambar'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $gambarPath = null;
        if ($request->hasFile('gambar')) {
            $gambarPath = $request->file('gambar')->store('soals', 'public');
        }

        $nextUrutan = ($quiz->soals()->max('urutan') ?? 0) + 1;

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
            'pilihan'       => $pilihanFormatted,
            'kunci_jawaban' => $kunci,
            'bobot'         => $validated['bobot'] ?? 10,
            'urutan'        => $nextUrutan,
            'gambar'        => $gambarPath,
            'quiz_id'       => $quiz->id,
            'tugas_id'      => null,
            'ujian_id'      => null,
        ]);

        return redirect()->route('guru.kuis.show', $quiz)->with('success', 'Soal berhasil ditambahkan ke kuis!');
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

        return redirect()->route('guru.kuis.show', $quiz)->with('success', 'Soal berhasil diperbarui!');
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

        if ($soal->gambar && Storage::disk('public')->exists($soal->gambar)) {
            Storage::disk('public')->delete($soal->gambar);
        }

        $soal->delete();

        return redirect()->route('guru.kuis.show', $quiz)->with('success', 'Soal berhasil dihapus!');
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
