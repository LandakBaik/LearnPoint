<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PengampuKelas;
use App\Models\Notifikasi;
use App\Models\Soal;
use App\Models\Ujian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UjianController extends Controller
{
    /**
     * Helper authorization for Guru.
     */
    protected function authorizeGuruUjian(Ujian $ujian, $guruId)
    {
        $owner = $ujian->pengampuKelas?->guruMapel?->guru_id;
        if (!$owner || $owner !== $guruId) {
            abort(403, 'Akses ditolak.');
        }
    }

    /**
     * GURU: Menampilkan semua ujian milik guru yang sedang login.
     */
    public function guruIndex(Request $request)
    {
        $guruId = $request->user()?->guru?->id ?? $request->user()?->guru_id;

        if (!$guruId) {
            return response()->json(['success' => false, 'message' => 'Profil guru tidak ditemukan.'], 404);
        }

        $query = Ujian::with([
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.kelas',
        ])
        ->withCount('soals')
        ->whereHas('pengampuKelas.guruMapel', function ($q) use ($guruId) {
            $q->where('guru_id', $guruId);
        });

        if ($request->filled('search')) {
            $query->where('judul', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $ujians = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $ujians,
        ]);
    }

    /**
     * GURU: Opsi kelas pengampu untuk form ujian.
     */
    public function guruOptions(Request $request)
    {
        $guruId = $request->user()?->guru?->id ?? $request->user()?->guru_id;

        $pengampuKelases = PengampuKelas::with(['guruMapel.mapel', 'kelas'])
            ->whereHas('guruMapel', function ($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            })
            ->get()
            ->map(function ($pk) {
                return [
                    'pengampu_kelas_id' => $pk->id,
                    'kelas_id' => $pk->kelas?->id,
                    'nama_kelas' => $pk->kelas?->nama_kelas,
                    'tingkatan' => $pk->kelas?->tingkatan,
                    'mapel_id' => $pk->guruMapel?->mapel?->id,
                    'nama_mapel' => $pk->guruMapel?->mapel?->nama_mapel,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $pengampuKelases,
        ]);
    }

    /**
     * GURU: Detail ujian dan butir soal.
     */
    public function guruShow(Request $request, Ujian $ujian)
    {
        $guruId = $request->user()?->guru?->id ?? $request->user()?->guru_id;
        $this->authorizeGuruUjian($ujian, $guruId);

        $ujian->load([
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.kelas',
            'soals',
        ]);

        $soals = $ujian->soals->map(function ($s) {
            return [
                'id' => $s->id,
                'urutan' => $s->urutan,
                'pertanyaan' => $s->pertanyaan,
                'tipe_soal' => $s->tipe_soal,
                'pilihan' => $s->pilihan,
                'kunci_jawaban' => $s->kunci_jawaban,
                'bobot' => $s->bobot,
                'gambar' => $s->gambar,
                'gambar_url' => $s->gambar ? asset('storage/' . $s->gambar) : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $ujian->id,
                'judul' => $ujian->judul,
                'deskripsi' => $ujian->deskripsi,
                'kisi_kisi' => $ujian->kisi_kisi,
                'durasi_menit' => $ujian->durasi_menit,
                'kkm' => $ujian->kkm,
                'status' => $ujian->status,
                'waktu_mulai' => $ujian->waktu_mulai,
                'deadline' => $ujian->deadline,
                'dipublikasi_at' => $ujian->dipublikasi_at,
                'mapel' => $ujian->pengampuKelas?->guruMapel?->mapel?->nama_mapel,
                'kelas' => $ujian->pengampuKelas?->kelas?->nama_kelas,
                'pengampu_kelas_id' => $ujian->pengampu_kelas_id,
                'soals' => $soals,
            ],
        ]);
    }

    /**
     * GURU: Buat ujian baru + soal.
     */
    public function guruStore(Request $request)
    {
        $guruId = $request->user()?->guru?->id ?? $request->user()?->guru_id;

        $validator = Validator::make($request->all(), [
            'judul' => ['required', 'string', 'max:200'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'kisi_kisi' => ['nullable', 'string', 'max:2000'],
            'pengampu_kelas_id' => ['required', 'exists:pengampu_kelas,id'],
            'durasi_menit' => ['required', 'integer', 'min:1', 'max:999'],
            'kkm' => ['required', 'numeric', 'min:0', 'max:100'],
            'waktu_mulai' => ['nullable', 'date'],
            'deadline' => ['nullable', 'date', 'after_or_equal:waktu_mulai'],
            'action' => ['nullable', 'in:draft,publish,schedule'],
            'soals' => ['required', 'array', 'min:1'],
            'soals.*.pertanyaan' => ['required', 'string'],
            'soals.*.tipe_soal' => ['required', 'in:single_choice,multiple_choice,essay,matching'],
            'soals.*.pilihan' => ['nullable', 'array'],
            'soals.*.kunci_jawaban' => ['nullable'],
            'soals.*.bobot' => ['nullable', 'numeric', 'min:0'],
            'soals.*.urutan' => ['nullable', 'integer'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        $pengampuKelas = PengampuKelas::whereHas('guruMapel', function ($q) use ($guruId) {
            $q->where('guru_id', $guruId);
        })->find($validated['pengampu_kelas_id']);

        if (!$pengampuKelas) {
            return response()->json(['success' => false, 'message' => 'Kelas pengampu tidak valid.'], 403);
        }

        $action = $request->input('action', 'draft');
        $status = 'draft';
        if ($action === 'publish') {
            $status = 'published';
        } elseif ($action === 'schedule') {
            if (empty($validated['waktu_mulai']) || empty($validated['deadline'])) {
                return response()->json(['success' => false, 'message' => 'Waktu mulai dan deadline wajib diisi jika menjadwalkan ujian.'], 422);
            }
            $status = 'scheduled';
        }

        $ujian = null;

        DB::transaction(function () use ($request, $validated, $pengampuKelas, $status, $action, &$ujian) {
            $ujian = Ujian::create([
                'judul' => $validated['judul'],
                'deskripsi' => $validated['deskripsi'] ?? null,
                'kisi_kisi' => $validated['kisi_kisi'] ?? null,
                'pengampu_kelas_id' => $pengampuKelas->id,
                'guru_mapel_id' => $pengampuKelas->guru_mapel_id,
                'durasi_menit' => $validated['durasi_menit'],
                'kkm' => $validated['kkm'],
                'waktu_mulai' => $validated['waktu_mulai'] ?? null,
                'deadline' => $validated['deadline'] ?? null,
                'status' => $status,
                'dipublikasi_at' => $action === 'publish' ? now() : null,
            ]);

            foreach ($request->input('soals', []) as $index => $data) {
                $gambar = null;
                if ($request->hasFile("soals.$index.gambar")) {
                    $gambar = $request->file("soals.$index.gambar")->store('soal-images', 'public');
                }

                $pilihan = $data['pilihan'] ?? null;
                if (!is_array($pilihan)) {
                    $pilihan = null;
                }

                $kunciJawaban = $data['kunci_jawaban'] ?? [];

                Soal::create([
                    'pertanyaan' => $data['pertanyaan'],
                    'tipe_soal' => $data['tipe_soal'],
                    'pilihan' => $pilihan,
                    'kunci_jawaban' => is_array($kunciJawaban) ? json_encode($kunciJawaban) : $kunciJawaban,
                    'gambar' => $gambar,
                    'bobot' => $data['bobot'] ?? 10,
                    'urutan' => $data['urutan'] ?? ($index + 1),
                    'ujian_id' => $ujian->id,
                ]);
            }
        });

        if ($action === 'publish' && $ujian) {
            $mapelNama = $pengampuKelas->guruMapel?->mapel?->nama_mapel ?? 'Mata Pelajaran';
            Notifikasi::kirimKeKelas(
                $pengampuKelas->kelas_id,
                "Ujian Baru Dipublikasikan: {$ujian->judul}",
                "Ujian evaluasi {$mapelNama} telah dipublikasikan dan dapat diakses sesuai jadwal.",
                'ujian_baru',
                route('siswa.dashboard'),
                $ujian->id
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Ujian berhasil dibuat.',
            'data' => $ujian,
        ], 201);
    }

    /**
     * GURU: Perbarui ujian.
     */
    public function guruUpdate(Request $request, Ujian $ujian)
    {
        $guruId = $request->user()?->guru?->id ?? $request->user()?->guru_id;
        $this->authorizeGuruUjian($ujian, $guruId);

        $validator = Validator::make($request->all(), [
            'judul' => ['required', 'string', 'max:200'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'kisi_kisi' => ['nullable', 'string', 'max:2000'],
            'pengampu_kelas_id' => ['required', 'exists:pengampu_kelas,id'],
            'durasi_menit' => ['required', 'integer', 'min:1', 'max:999'],
            'kkm' => ['required', 'numeric', 'min:0', 'max:100'],
            'waktu_mulai' => ['nullable', 'date'],
            'deadline' => ['nullable', 'date', 'after_or_equal:waktu_mulai'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        $ujian->update([
            'judul' => $validated['judul'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'kisi_kisi' => $validated['kisi_kisi'] ?? null,
            'durasi_menit' => $validated['durasi_menit'],
            'kkm' => $validated['kkm'],
            'waktu_mulai' => $validated['waktu_mulai'] ?? $ujian->waktu_mulai,
            'deadline' => $validated['deadline'] ?? $ujian->deadline,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ujian berhasil diperbarui.',
            'data' => $ujian,
        ]);
    }

    /**
     * GURU: Hapus ujian.
     */
    public function guruDestroy(Request $request, Ujian $ujian)
    {
        $guruId = $request->user()?->guru?->id ?? $request->user()?->guru_id;
        $this->authorizeGuruUjian($ujian, $guruId);

        foreach ($ujian->soals as $soal) {
            if ($soal->gambar && Storage::disk('public')->exists($soal->gambar)) {
                Storage::disk('public')->delete($soal->gambar);
            }
        }

        $ujian->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ujian berhasil dihapus.',
        ]);
    }

    /**
     * GURU: Publikasi ujian.
     */
    public function guruPublish(Request $request, Ujian $ujian)
    {
        $guruId = $request->user()?->guru?->id ?? $request->user()?->guru_id;
        $this->authorizeGuruUjian($ujian, $guruId);

        $ujian->update([
            'status' => 'published',
            'dipublikasi_at' => now(),
        ]);

        // Kirim notifikasi interaktif ke kelas peserta ujian
        $kelasId = $ujian->pengampuKelas?->kelas_id;
        if ($kelasId) {
            $mapelNama = $ujian->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? 'Mata Pelajaran';
            Notifikasi::kirimKeKelas(
                $kelasId,
                "Ujian Baru Dipublikasikan: {$ujian->judul}",
                "Ujian evaluasi {$mapelNama} telah dipublikasikan dan dapat diakses sesuai jadwal.",
                'ujian_baru',
                route('siswa.dashboard'),
                $ujian->id
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Ujian berhasil dipublikasikan.',
            'data' => $ujian,
        ]);
    }

    /**
     * GURU: Jadwalkan ujian.
     */
    public function guruSchedule(Request $request, Ujian $ujian)
    {
        $guruId = $request->user()?->guru?->id ?? $request->user()?->guru_id;
        $this->authorizeGuruUjian($ujian, $guruId);

        $validator = Validator::make($request->all(), [
            'waktu_mulai' => ['required', 'date'],
            'deadline' => ['required', 'date', 'after:waktu_mulai'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'errors' => $validator->errors()], 422);
        }

        $ujian->update([
            'waktu_mulai' => $request->waktu_mulai,
            'deadline' => $request->deadline,
            'status' => 'scheduled',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ujian berhasil dijadwalkan.',
            'data' => $ujian,
        ]);
    }

    /**
     * SISWA: Menampilkan daftar ujian untuk kelas siswa.
     */
    public function siswaIndex(Request $request)
    {
        $siswa = $request->user()?->siswa;
        $kelas = $siswa?->kelas ?? $siswa?->anggotaKelases()?->latest()?->first()?->kelas;
        $kelasId = $kelas?->id;

        if (!$kelasId) {
            return response()->json(['success' => false, 'message' => 'Data kelas siswa tidak ditemukan.'], 404);
        }

        $ujians = Ujian::with(['pengampuKelas.guruMapel.mapel', 'pengampuKelas.guruMapel.guru'])
            ->whereHas('pengampuKelas', function ($q) use ($kelasId) {
                $q->where('kelas_id', $kelasId);
            })
            ->whereIn('status', ['published', 'scheduled'])
            ->latest()
            ->get()
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'judul' => $u->judul,
                    'deskripsi' => $u->deskripsi,
                    'durasi_menit' => $u->durasi_menit,
                    'kkm' => $u->kkm,
                    'status' => $u->status,
                    'waktu_mulai' => $u->waktu_mulai,
                    'deadline' => $u->deadline,
                    'mapel' => $u->pengampuKelas?->guruMapel?->mapel?->nama_mapel,
                    'guru' => $u->pengampuKelas?->guruMapel?->guru?->nama,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $ujians,
        ]);
    }

    /**
     * SISWA: Detail ujian untuk siswa.
     */
    public function siswaShow(Request $request, Ujian $ujian)
    {
        $siswa = $request->user()?->siswa;
        $kelas = $siswa?->kelas ?? $siswa?->anggotaKelases()?->latest()?->first()?->kelas;
        $kelasId = $kelas?->id;

        if (!$kelasId || $ujian->pengampuKelas?->kelas_id !== $kelasId) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $ujian->load(['pengampuKelas.guruMapel.mapel', 'pengampuKelas.guruMapel.guru']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $ujian->id,
                'judul' => $ujian->judul,
                'deskripsi' => $ujian->deskripsi,
                'kisi_kisi' => $ujian->kisi_kisi,
                'durasi_menit' => $ujian->durasi_menit,
                'kkm' => $ujian->kkm,
                'status' => $ujian->status,
                'waktu_mulai' => $ujian->waktu_mulai,
                'deadline' => $ujian->deadline,
                'mapel' => $ujian->pengampuKelas?->guruMapel?->mapel?->nama_mapel,
                'guru' => $ujian->pengampuKelas?->guruMapel?->guru?->nama,
            ],
        ]);
    }
}
