<?php

namespace App\Http\Controllers;

use App\Models\Materi;
use App\Models\Mapel;
use App\Models\PengampuKelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;

class MateriController extends Controller
{
    /**
     * Halaman Utama / Kelola Materi untuk Guru.
     */
    public function index(Request $request)
    {
        $guruId = $this->guruId($request);

        $materis = Materi::with([
            'pengampuKelas.guruMapel.guru',
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.kelas',
        ])
            ->whereHas('pengampuKelas.guruMapel', function ($query) use ($guruId) {
                $query->where('guru_id', $guruId);
            })
            ->latest()
            ->get();

        return view('Guru.Materi', compact('materis'));
    }

    public function store(Request $request)
    {
        $guruId = $this->guruId($request);
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:200'],
            'deskripsi' => ['nullable', 'string'],
            'file_materi' => ['required', 'file', 'mimes:pdf', 'max:25600'],
            'url_youtube' => ['nullable', 'url', 'max:2048', $this->youtubeUrlRule()],
            'pengampu_kelas_id' => ['required', 'integer', 'exists:pengampu_kelas,id'],
        ]);

        $this->assertAssignmentBelongsToGuru($validated['pengampu_kelas_id'], $guruId);

        $filePath = $request->file('file_materi')->store('materi', 'public');

        try {
            Materi::create([
                'judul' => $validated['judul'],
                'deskripsi' => $validated['deskripsi'] ?? null,
                'file_materi' => $filePath,
                'url_youtube' => $validated['url_youtube'] ?? null,
                'pengampu_kelas_id' => $validated['pengampu_kelas_id'],
            ]);
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($filePath);
            throw $exception;
        }

        return redirect()->route('guru.materi')->with('success', 'Materi berhasil ditambahkan.');
    }

    public function update(Request $request, Materi $materi)
    {
        $guruId = $this->guruId($request);
        $materi = $this->materiMilikGuru($materi, $guruId);

        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:200'],
            'deskripsi' => ['nullable', 'string'],
            'file_materi' => ['nullable', 'file', 'mimes:pdf', 'max:25600'],
            'url_youtube' => ['nullable', 'url', 'max:2048', $this->youtubeUrlRule()],
            'pengampu_kelas_id' => ['required', 'integer', 'exists:pengampu_kelas,id'],
        ]);

        $this->assertAssignmentBelongsToGuru($validated['pengampu_kelas_id'], $guruId);

        $oldFilePath = $materi->file_materi;
        $newFilePath = $request->hasFile('file_materi')
            ? $request->file('file_materi')->store('materi', 'public')
            : null;

        $materi->fill([
            'judul' => $validated['judul'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'url_youtube' => $validated['url_youtube'] ?? null,
            'pengampu_kelas_id' => $validated['pengampu_kelas_id'],
        ]);

        if ($newFilePath !== null) {
            $materi->file_materi = $newFilePath;
        }

        try {
            $materi->save();
        } catch (Throwable $exception) {
            if ($newFilePath !== null) {
                Storage::disk('public')->delete($newFilePath);
            }

            throw $exception;
        }

        if ($newFilePath !== null && $oldFilePath !== $newFilePath) {
            Storage::disk('public')->delete($oldFilePath);
        }

        return redirect()->route('guru.materi')->with('success', 'Materi berhasil diperbarui.');
    }

    public function destroy(Request $request, Materi $materi)
    {
        $guruId = $this->guruId($request);
        $materi = $this->materiMilikGuru($materi, $guruId);
        $filePath = $materi->file_materi;

        $materi->delete();
        Storage::disk('public')->delete($filePath);

        return redirect()->route('guru.materi')->with('success', 'Materi berhasil dihapus.');
    }

    /**
     * Halaman Detail Bab & File Materi per Mapel untuk Siswa.
     */
    public function indexSiswa(Request $request)
    {
        $kelasId = $request->user()?->siswa?->kelas?->id;

        $daftarMapel = $kelasId
            ? Mapel::whereHas('pengampuKelases', function ($query) use ($kelasId) {
                $query->where('kelas_id', $kelasId);
            })
                ->with(['pengampuKelases' => function ($query) use ($kelasId) {
                    $query->where('kelas_id', $kelasId)
                        ->with(['guruMapel.guru', 'materis']);
                }])
                ->get()
            : collect();

        return view('siswa.materi.index', compact('daftarMapel'));
    }

    /**
     * Method milikmu untuk menampilkan detail materi dari mapel yang dipilih
     */
    public function showSiswa(Request $request, $id)
    {
        $kelasId = $request->user()?->siswa?->kelas?->id;

        abort_unless($kelasId, 404);

        $mapel = Mapel::whereHas('pengampuKelases', function ($query) use ($kelasId) {
            $query->where('kelas_id', $kelasId);
        })->findOrFail($id);

        $materis = Materi::with([
            'pengampuKelas.guruMapel.guru',
            'pengampuKelas.kelas',
        ])
            ->whereHas('pengampuKelas', function ($query) use ($kelasId, $id) {
                $query->where('kelas_id', $kelasId)
                    ->whereHas('guruMapel', function ($query) use ($id) {
                        $query->where('mapel_id', $id);
                    });
            })
            ->get();

        return view('siswa.materi.show', compact('mapel', 'materis'));
    }

    private function guruId(Request $request): int
    {
        $user = $request->user();
        $guruId = $user?->guru?->id;

        abort_unless($user?->role === 'guru' && $guruId, 403);

        return (int) $guruId;
    }

    private function assertAssignmentBelongsToGuru(int|string $assignmentId, int $guruId): void
    {
        $belongsToGuru = PengampuKelas::whereKey($assignmentId)
            ->whereHas('guruMapel', function ($query) use ($guruId) {
                $query->where('guru_id', $guruId);
            })
            ->exists();

        abort_unless($belongsToGuru, 403);
    }

    private function materiMilikGuru(Materi $materi, int $guruId): Materi
    {
        return Materi::whereKey($materi->id)
            ->whereHas('pengampuKelas.guruMapel', function ($query) use ($guruId) {
                $query->where('guru_id', $guruId);
            })
            ->firstOrFail();
    }

    private function youtubeUrlRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if ($value === null || $value === '') {
                return;
            }

            $host = strtolower((string) parse_url($value, PHP_URL_HOST));
            $allowedHosts = [
                'youtube.com',
                'www.youtube.com',
                'm.youtube.com',
                'music.youtube.com',
                'youtu.be',
                'www.youtu.be',
                'youtube-nocookie.com',
                'www.youtube-nocookie.com',
            ];

            if (! in_array($host, $allowedHosts, true)) {
                $fail('URL harus berasal dari YouTube.');
            }
        };
    }
}
