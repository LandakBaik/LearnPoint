<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Soal;
use App\Models\PengampuKelas;
use Illuminate\Http\Request;

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

        Quiz::create($validated);

        return redirect()->route('guru.kuis.index')->with('success', 'Kuis berhasil ditambahkan!');
    }

    /**
     * Menampilkan detail kuis.
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
