<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menjawab;
use App\Models\Nilai;
use App\Models\Notifikasi;
use App\Models\PengampuKelas;
use App\Models\Quiz;
use App\Models\Soal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class QuizController extends Controller
{
    /**
     * Helper to authorize Guru ownership.
     */
    protected function authorizeGuru(Quiz $quiz, $guruId)
    {
        $owner = $quiz->pengampuKelas?->guruMapel?->guru_id;
        if (!$owner || $owner !== $guruId) {
            abort(403, 'Akses ditolak.');
        }
    }

    /**
     * GURU: Menampilkan daftar semua kuis petualangan milik guru.
     */
    public function guruIndex(Request $request)
    {
        $guruId = $request->user()?->guru?->id;

        if (!$guruId) {
            return $this->notFoundResponse('Profil guru tidak ditemukan.');
        }

        $query = Quiz::with([
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

        if ($request->filled('kelas_id')) {
            $query->whereHas('pengampuKelas', function ($q) use ($request) {
                $q->where('kelas_id', $request->kelas_id);
            });
        }

        if ($request->filled('kesulitan')) {
            $query->whereJsonContains('kesulitan_aktif', $request->kesulitan);
        }

        $perPage = (int) $request->query('per_page', 15);
        $quizzes = $query->latest()->paginate($perPage);

        // Statistik Dashboard Kuis Guru
        $baseGuruQuery = Quiz::whereHas('pengampuKelas.guruMapel', function ($q) use ($guruId) {
            $q->where('guru_id', $guruId);
        });

        $totalKuis      = (clone $baseGuruQuery)->count();
        $totalDraft     = (clone $baseGuruQuery)->where(function ($q) {
            $q->where('status', 'draft')->orWhereNull('status');
        })->count();
        $totalPublished = (clone $baseGuruQuery)->where('status', 'published')->count();
        $totalSoal      = Soal::whereHas('quiz.pengampuKelas.guruMapel', function ($q) use ($guruId) {
            $q->where('guru_id', $guruId);
        })->count();

        return $this->successResponse([
            'statistics' => [
                'total_kuis'      => $totalKuis,
                'total_draft'     => $totalDraft,
                'total_published' => $totalPublished,
                'total_soal'      => $totalSoal,
            ],
            'quizzes' => $quizzes,
        ]);
    }

    /**
     * GURU: Opsi pengampu kelas untuk kuis.
     */
    public function guruOptions(Request $request)
    {
        $guruId = $request->user()?->guru?->id;

        $pengampuKelases = PengampuKelas::with(['guruMapel.mapel', 'kelas'])
            ->whereHas('guruMapel', function ($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            })
            ->get()
            ->map(function ($pk) {
                return [
                    'pengampu_kelas_id' => $pk->id,
                    'kelas_id'          => $pk->kelas?->id,
                    'nama_kelas'        => $pk->kelas?->nama_kelas,
                    'tingkatan'         => $pk->kelas?->tingkatan,
                    'mapel_id'          => $pk->guruMapel?->mapel?->id,
                    'nama_mapel'        => $pk->guruMapel?->mapel?->nama_mapel,
                ];
            });

        return $this->successResponse($pengampuKelases);
    }

    /**
     * GURU: Detail kuis dan daftar butir soal.
     */
    public function guruShow(Request $request, Quiz $quiz)
    {
        $guruId = $request->user()?->guru?->id;
        $this->authorizeGuru($quiz, $guruId);

        $quiz->load([
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.kelas',
            'soals',
            'nilais.siswa',
        ]);

        $activeArc = $request->query('arc');
        $activeDifficulties = $quiz->kesulitan_aktif_array;
        if (!in_array($activeArc, $activeDifficulties)) {
            $activeArc = $activeDifficulties[0] ?? 'Mudah';
        }

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

        $soals = $quiz->soals->map(function ($s) {
            return [
                'id'            => $s->id,
                'pertanyaan'    => $s->pertanyaan,
                'tipe_soal'     => $s->tipe_soal,
                'kesulitan'     => $s->kesulitan,
                'level'         => $s->level,
                'pilihan'       => $s->pilihan,
                'kunci_jawaban' => $s->kunci_jawaban,
                'bobot'         => $s->bobot,
                'urutan'        => $s->urutan,
                'gambar'        => $s->gambar,
                'gambar_url'    => $s->gambar ? asset('storage/' . $s->gambar) : null,
            ];
        });

        $nilais = $quiz->nilais->map(function ($n) {
            return [
                'id'         => $n->id,
                'siswa'      => [
                    'id'   => $n->siswa?->id,
                    'nama' => $n->siswa?->nama_siswa,
                    'nis'  => $n->siswa?->nis,
                ],
                'nilai'      => $n->nilai,
                'status'     => $n->status,
                'durasi'     => $n->durasi,
                'created_at' => $n->created_at,
            ];
        });

        return $this->successResponse([
            'id'                  => $quiz->id,
            'judul'               => $quiz->judul,
            'deskripsi'           => $quiz->deskripsi,
            'status'              => $quiz->status,
            'kesulitan_aktif'     => $quiz->kesulitan_aktif_array,
            'waktu_mulai'         => $quiz->waktu_mulai?->format('Y-m-d H:i:s'),
            'waktu_selesai'       => $quiz->waktu_selesai?->format('Y-m-d H:i:s'),
            'acak_jawaban'        => (bool) $quiz->acak_jawaban,
            'mapel'               => $quiz->pengampuKelas?->guruMapel?->mapel?->nama_mapel,
            'kelas'               => $quiz->pengampuKelas?->kelas?->nama_kelas,
            'pengampu_kelas_id'   => $quiz->pengampu_kelas_id,
            'level_counts'        => $levelCounts,
            'soals'               => $soals,
            'nilais'              => $nilais,
        ]);
    }

    /**
     * GURU: Simpan draf kuis baru.
     */
    public function guruStore(Request $request)
    {
        $guruId = $request->user()?->guru?->id;

        $validator = Validator::make($request->all(), [
            'judul'             => ['required', 'string', 'max:200'],
            'deskripsi'         => ['nullable', 'string', 'max:1000'],
            'pengampu_kelas_id' => ['required', 'exists:pengampu_kelas,id'],
            'kesulitan_aktif'   => ['required', 'array', 'min:1', 'max:3'],
            'kesulitan_aktif.*' => ['string', 'in:Mudah,Sedang,Sulit'],
            'waktu_mulai'       => ['nullable', 'date', 'after_or_equal:today'],
            'waktu_selesai'     => ['nullable', 'date', 'after:waktu_mulai'],
            'acak_jawaban'      => ['nullable', 'boolean'],
            'status'            => ['nullable', 'string', 'in:draft,published'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $validated = $validator->validated();

        $isOwner = PengampuKelas::where('id', $validated['pengampu_kelas_id'])
            ->whereHas('guruMapel', function ($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            })->exists();

        if (!$isOwner) {
            return $this->forbiddenResponse('Anda tidak memiliki hak akses pada kelas/mata pelajaran ini.');
        }

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

        return $this->createdResponse($quiz, 'Draf kuis berhasil dibuat.');
    }

    /**
     * GURU: Perbarui pengaturan kuis.
     */
    public function guruUpdate(Request $request, Quiz $quiz)
    {
        $guruId = $request->user()?->guru?->id;
        $this->authorizeGuru($quiz, $guruId);

        $validator = Validator::make($request->all(), [
            'judul'             => ['required', 'string', 'max:200'],
            'deskripsi'         => ['nullable', 'string', 'max:1000'],
            'pengampu_kelas_id' => ['required', 'exists:pengampu_kelas,id'],
            'kesulitan_aktif'   => ['required', 'array', 'min:1', 'max:3'],
            'kesulitan_aktif.*' => ['string', 'in:Mudah,Sedang,Sulit'],
            'waktu_mulai'       => ['nullable', 'date', 'after_or_equal:today'],
            'waktu_selesai'     => ['nullable', 'date', 'after:waktu_mulai'],
            'acak_jawaban'      => ['nullable', 'boolean'],
            'status'            => ['nullable', 'string', 'in:draft,published'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $validated = $validator->validated();

        $isOwner = PengampuKelas::where('id', $validated['pengampu_kelas_id'])
            ->whereHas('guruMapel', function ($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            })->exists();

        if (!$isOwner) {
            return $this->forbiddenResponse('Anda tidak memiliki hak akses pada kelas/mata pelajaran ini.');
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

        return $this->successResponse($quiz, 'Pengaturan kuis berhasil diperbarui.');
    }

    /**
     * GURU: Mengubah status kuis antara Draf dan Published.
     */
    public function guruToggleStatus(Request $request, Quiz $quiz)
    {
        $guruId = $request->user()?->guru?->id;
        $this->authorizeGuru($quiz, $guruId);

        if ($quiz->status === 'published') {
            $quiz->update(['status' => 'draft']);

            return $this->successResponse([
                'status'  => 'draft',
            ], 'Kuis berhasil diubah menjadi draf.');
        }

        $activeDifficulties = $quiz->kesulitan_aktif_array;
        if (empty($activeDifficulties)) {
            return $this->errorResponse('Aktifkan minimal satu tingkat kesulitan sebelum menerbitkan kuis.', 422);
        }

        foreach ($activeDifficulties as $difficulty) {
            $hasQuestions = $quiz->soals()->where('kesulitan', $difficulty)->exists();
            if (!$hasQuestions) {
                return $this->errorResponse("Tingkat kesulitan {$difficulty} belum memiliki butir soal. Tambahkan minimal satu soal terlebih dahulu.", 422);
            }
        }

        $quiz->update(['status' => 'published']);

        // Kirim notifikasi interaktif ke kelas peserta kuis
        $kelasId = $quiz->pengampuKelas?->kelas_id;
        if ($kelasId) {
            $mapelNama = $quiz->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? 'Mata Pelajaran';
            Notifikasi::kirimKeKelas(
                $kelasId,
                "Kuis Baru: {$quiz->judul}",
                "Guru telah menerbitkan tantangan kuis petualangan baru untuk mata pelajaran {$mapelNama}.",
                'kuis_baru',
                route('siswa.kuis.show', $quiz->id),
                $quiz->id
            );
        }

        return $this->successResponse([
            'status'  => 'published',
        ], 'Kuis berhasil diterbitkan untuk siswa.');
    }

    /**
     * GURU: Hapus kuis beserta butir soalnya.
     */
    public function guruDestroy(Request $request, Quiz $quiz)
    {
        $guruId = $request->user()?->guru?->id;
        $this->authorizeGuru($quiz, $guruId);

        foreach ($quiz->soals as $soal) {
            if ($soal->gambar && Storage::disk('public')->exists($soal->gambar)) {
                Storage::disk('public')->delete($soal->gambar);
            }
        }

        $quiz->delete();

        return $this->successResponse(null, 'Kuis berhasil dihapus.');
    }

    /**
     * GURU: Hapus beberapa kuis sekaligus (Bulk Destroy).
     */
    public function guruBulkDestroy(Request $request)
    {
        $guruId = $request->user()?->guru?->id;
        if (!$guruId) {
            return $this->forbiddenResponse('Akses ditolak.');
        }

        $validator = Validator::make($request->all(), [
            'quiz_ids'   => ['required', 'array', 'min:1'],
            'quiz_ids.*' => ['required', 'exists:quizzes,id'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $validated = $validator->validated();

        $quizzes = Quiz::whereIn('id', $validated['quiz_ids'])
            ->whereHas('pengampuKelas.guruMapel', function ($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            })
            ->with('soals')
            ->get();

        $count = $quizzes->count();
        if ($count === 0) {
            return $this->notFoundResponse('Tidak ada kuis valid yang dapat dihapus.');
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

        return $this->successResponse([
            'deleted_count' => $count,
        ], "{$count} kuis berhasil dihapus.");
    }

    /**
     * GURU: Tambahkan butir soal ke dalam level tertentu pada Arc kuis.
     */
    public function guruStoreSoal(Request $request, Quiz $quiz)
    {
        $guruId = $request->user()?->guru?->id;
        $this->authorizeGuru($quiz, $guruId);

        $validator = Validator::make($request->all(), [
            'pertanyaan'    => ['required', 'string'],
            'tipe_soal'     => ['required', 'in:single_choice,multiple_choice,essay,matching'],
            'kesulitan'     => ['required', 'string', 'in:Mudah,Sedang,Sulit'],
            'level'         => ['required', 'string', 'in:Level 1,Level 2,Level 3,Level 4,Level 5,1,2,3,4,5'],
            'pilihan'       => ['required', 'array', 'min:2', 'max:6'],
            'kunci_jawaban' => ['required'],
            'bobot'         => ['nullable', 'numeric', 'min:1'],
            'gambar'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $validated = $validator->validated();

        $levelNum = (int) preg_replace('/[^0-9]/', '', $validated['level']);
        if ($levelNum < 1 || $levelNum > Quiz::MAX_LEVEL) {
            return $this->errorResponse('Level tidak valid. Maksimal 5 level.', 422);
        }
        $levelFormatted = 'Level ' . $levelNum;

        // Cek kuota maksimal 10 soal per level
        $soalCount = $quiz->soals()
            ->where('kesulitan', $validated['kesulitan'])
            ->where('level', $levelFormatted)
            ->count();

        if ($soalCount >= Quiz::MAX_SOAL) {
            return $this->errorResponse("Tingkat {$validated['kesulitan']} {$levelFormatted} sudah mencapai batas maksimal 10 soal.", 422);
        }

        $gambarPath = null;
        if ($request->hasFile('gambar')) {
            $gambarPath = $request->file('gambar')->store('soals', 'public');
        }

        $nextUrutan = ($quiz->soals()
            ->where('kesulitan', $validated['kesulitan'])
            ->where('level', $levelFormatted)
            ->max('urutan') ?? 0) + 1;

        $pilihanFormatted = [
            'a' => $request->input('pilihan.a', ''),
            'b' => $request->input('pilihan.b', ''),
            'c' => $request->input('pilihan.c', ''),
            'd' => $request->input('pilihan.d', ''),
        ];

        $kunci = $validated['kunci_jawaban'];
        if (is_array($kunci)) {
            $kunci = json_encode($kunci);
        }

        $soal = Soal::create([
            'pertanyaan'    => $validated['pertanyaan'],
            'tipe_soal'     => $validated['tipe_soal'],
            'kesulitan'     => $validated['kesulitan'],
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

        return $this->createdResponse($soal, 'Soal berhasil ditambahkan.');
    }

    /**
     * GURU: Perbarui butir soal kuis.
     */
    public function guruUpdateSoal(Request $request, Quiz $quiz, Soal $soal)
    {
        $guruId = $request->user()?->guru?->id;
        $this->authorizeGuru($quiz, $guruId);

        if ($soal->quiz_id !== $quiz->id) {
            return $this->forbiddenResponse('Soal bukan milik kuis ini.');
        }

        $validator = Validator::make($request->all(), [
            'pertanyaan'    => ['required', 'string'],
            'tipe_soal'     => ['required', 'in:single_choice,multiple_choice,essay,matching'],
            'kesulitan'     => ['nullable', 'string', 'in:Mudah,Sedang,Sulit'],
            'level'         => ['nullable', 'string', 'in:Level 1,Level 2,Level 3,Level 4,Level 5,1,2,3,4,5'],
            'pilihan'       => ['required', 'array', 'min:2', 'max:6'],
            'kunci_jawaban' => ['required'],
            'bobot'         => ['nullable', 'numeric', 'min:1'],
            'gambar'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $validated = $validator->validated();

        $gambarPath = $soal->gambar;
        if ($request->hasFile('gambar')) {
            if ($gambarPath && Storage::disk('public')->exists($gambarPath)) {
                Storage::disk('public')->delete($gambarPath);
            }
            $gambarPath = $request->file('gambar')->store('soals', 'public');
        }

        $pilihanFormatted = [
            'a' => $request->input('pilihan.a', $soal->pilihan['a'] ?? ''),
            'b' => $request->input('pilihan.b', $soal->pilihan['b'] ?? ''),
            'c' => $request->input('pilihan.c', $soal->pilihan['c'] ?? ''),
            'd' => $request->input('pilihan.d', $soal->pilihan['d'] ?? ''),
        ];

        $kunci = $validated['kunci_jawaban'];
        if (is_array($kunci)) {
            $kunci = json_encode($kunci);
        }

        $updateData = [
            'pertanyaan'    => $validated['pertanyaan'],
            'tipe_soal'     => $validated['tipe_soal'],
            'pilihan'       => $pilihanFormatted,
            'kunci_jawaban' => $kunci,
            'bobot'         => $validated['bobot'] ?? $soal->bobot,
            'gambar'        => $gambarPath,
        ];

        if (!empty($validated['kesulitan'])) {
            $updateData['kesulitan'] = $validated['kesulitan'];
        }

        if (!empty($validated['level'])) {
            $levelNum = (int) preg_replace('/[^0-9]/', '', $validated['level']);
            $updateData['level'] = 'Level ' . $levelNum;
        }

        $soal->update($updateData);

        return $this->successResponse($soal, 'Soal berhasil diperbarui.');
    }

    /**
     * GURU: Hapus butir soal kuis.
     */
    public function guruDestroySoal(Request $request, Quiz $quiz, Soal $soal)
    {
        $guruId = $request->user()?->guru?->id;
        $this->authorizeGuru($quiz, $guruId);

        if ($soal->quiz_id !== $quiz->id) {
            return $this->forbiddenResponse('Soal bukan milik kuis ini.');
        }

        if ($soal->gambar && Storage::disk('public')->exists($soal->gambar)) {
            Storage::disk('public')->delete($soal->gambar);
        }

        $soal->delete();

        return $this->successResponse(null, 'Soal berhasil dihapus.');
    }

    /**
     * SISWA: Menampilkan daftar kuis terpublikasi untuk kelas siswa.
     */
    public function siswaIndex(Request $request)
    {
        $siswa = $request->user()?->siswa;
        $kelas = $siswa?->kelas ?? $siswa?->anggotaKelases()?->latest()?->first()?->kelas;
        $kelasId = $kelas?->id;

        if (!$kelasId) {
            return $this->notFoundResponse('Data kelas siswa tidak ditemukan.');
        }

        $query = Quiz::whereHas('pengampuKelas', function ($q) use ($kelasId) {
            $q->where('kelas_id', $kelasId);
        })
        ->where('status', 'published')
        ->with([
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.guruMapel.guru',
            'nilais' => function ($q) use ($siswa) {
                if ($siswa) {
                    $q->where('siswa_id', $siswa->id);
                }
            },
        ])
        ->withCount('soals');

        if ($request->filled('mapel_id')) {
            $query->whereHas('pengampuKelas.guruMapel', function ($q) use ($request) {
                $q->where('mapel_id', $request->mapel_id);
            });
        }

        $quizzes = $query->latest()->get()->map(function ($quiz) {
            $nilai = $quiz->nilais->first();

            return [
                'id'              => $quiz->id,
                'judul'           => $quiz->judul,
                'deskripsi'       => $quiz->deskripsi,
                'kesulitan_aktif' => $quiz->kesulitan_aktif_array,
                'mapel_id'        => $quiz->pengampuKelas?->guruMapel?->mapel_id,
                'mapel'           => $quiz->pengampuKelas?->guruMapel?->mapel?->nama_mapel,
                'guru'            => $quiz->pengampuKelas?->guruMapel?->guru?->nama,
                'total_soal'      => $quiz->soals_count,
                'waktu_mulai'     => $quiz->waktu_mulai?->format('Y-m-d H:i:s'),
                'waktu_selesai'   => $quiz->waktu_selesai?->format('Y-m-d H:i:s'),
                'is_completed'    => $nilai !== null,
                'result'          => $nilai ? [
                    'id'           => $nilai->id,
                    'nilai'        => $nilai->nilai,
                    'status'       => $nilai->status,
                    'durasi'       => $nilai->durasi,
                    'completed_at' => $nilai->created_at,
                ] : null,
            ];
        });

        return $this->successResponse($quizzes);
    }

    /**
     * SISWA: Detail kuis petualangan (Ringkasan Arc & status pengerjaan).
     */
    public function siswaShow(Request $request, Quiz $quiz)
    {
        $siswa = $request->user()?->siswa;
        $kelas = $siswa?->kelas ?? $siswa?->anggotaKelases()?->latest()?->first()?->kelas;
        $kelasId = $kelas?->id;

        if (!$kelasId || $quiz->pengampuKelas?->kelas_id !== $kelasId) {
            return $this->forbiddenResponse('Kuis tidak ditemukan untuk kelas Anda.');
        }

        if ($quiz->status !== 'published') {
            return $this->forbiddenResponse('Kuis belum dipublikasikan oleh guru.');
        }

        $quiz->load(['pengampuKelas.guruMapel.mapel', 'pengampuKelas.guruMapel.guru', 'soals']);

        $soalCountsByArc = [];
        foreach ($quiz->kesulitan_aktif_array as $arc) {
            $soalCountsByArc[$arc] = $quiz->soals->where('kesulitan', $arc)->count();
        }

        $soalIds = $quiz->soals->pluck('id');
        $answeredSoalIds = $siswa
            ? Menjawab::where('siswa_id', $siswa->id)
                ->whereIn('soal_id', $soalIds)
                ->pluck('soal_id')
                ->toArray()
            : [];

        $levelsInfo = [];
        foreach (Quiz::LEVEL_LIST as $index => $lvl) {
            $soalsInLevel = $quiz->soals->where('level', $lvl);
            $count = $soalsInLevel->count();
            $answeredCount = $soalsInLevel->whereIn('id', $answeredSoalIds)->count();
            $levelsInfo[] = [
                'level_number'   => $index + 1,
                'level'          => $lvl,
                'total_soal'     => $count,
                'answered_soal'  => $answeredCount,
                'is_completed'   => $count > 0 && $answeredCount >= $count,
                'is_unlocked'    => $count > 0,
            ];
        }

        $riwayatNilai = $siswa
            ? Nilai::where('siswa_id', $siswa->id)
                ->where('quiz_id', $quiz->id)
                ->first()
            : null;

        return $this->successResponse([
            'id'                 => $quiz->id,
            'judul'              => $quiz->judul,
            'deskripsi'          => $quiz->deskripsi,
            'kesulitan_aktif'    => $quiz->kesulitan_aktif_array,
            'waktu_mulai'        => $quiz->waktu_mulai?->format('Y-m-d H:i:s'),
            'waktu_selesai'      => $quiz->waktu_selesai?->format('Y-m-d H:i:s'),
            'acak_jawaban'       => (bool) $quiz->acak_jawaban,
            'mapel'              => $quiz->pengampuKelas?->guruMapel?->mapel?->nama_mapel,
            'guru'               => $quiz->pengampuKelas?->guruMapel?->guru?->nama,
            'soal_counts_by_arc' => $soalCountsByArc,
            'levels'             => $levelsInfo,
            'is_completed'       => $riwayatNilai !== null,
            'result'             => $riwayatNilai ? [
                'id'           => $riwayatNilai->id,
                'nilai'        => $riwayatNilai->nilai,
                'status'       => $riwayatNilai->status,
                'durasi'       => $riwayatNilai->durasi,
                'completed_at' => $riwayatNilai->created_at,
            ] : null,
        ]);
    }

    /**
     * SISWA: Ambil daftar butir soal kuis untuk dikerjakan.
     */
    public function siswaKerjakan(Request $request, Quiz $quiz)
    {
        $siswa = $request->user()?->siswa;
        $kelas = $siswa?->kelas ?? $siswa?->anggotaKelases()?->latest()?->first()?->kelas;
        $kelasId = $kelas?->id;

        if (!$kelasId || $quiz->pengampuKelas?->kelas_id !== $kelasId) {
            return $this->forbiddenResponse('Akses ditolak.');
        }

        if ($quiz->status !== 'published') {
            return $this->forbiddenResponse('Kuis belum dipublikasikan.');
        }

        // Cek apakah siswa sudah pernah menyelesaikan seluruh kuis ini
        $existing = Nilai::where('siswa_id', $siswa->id)->where('quiz_id', $quiz->id)->first();
        $totalSoalQuiz = $quiz->soals()->count();
        $answeredCount = Menjawab::where('siswa_id', $siswa->id)->whereIn('soal_id', $quiz->soals()->pluck('id'))->count();
        if ($existing && $totalSoalQuiz > 0 && $answeredCount >= $totalSoalQuiz) {
            return $this->errorResponse('Anda telah menyelesaikan kuis ini sebelumnya.', 400, [
                'nilai'        => $existing->nilai,
                'completed_at' => $existing->created_at,
            ]);
        }

        if ($quiz->waktu_mulai && now()->lessThan($quiz->waktu_mulai)) {
            return $this->forbiddenResponse('Kuis belum dimulai. Jadwal mulai pada: ' . $quiz->waktu_mulai->format('d M Y, H:i') . ' WIB.');
        }

        if ($quiz->waktu_selesai && now()->greaterThan($quiz->waktu_selesai)) {
            return $this->forbiddenResponse('Waktu pengerjaan kuis telah berakhir.');
        }

        $query = $quiz->soals();

        if ($request->filled('arc')) {
            $query->where('kesulitan', $request->arc);
        }

        if ($request->filled('level')) {
            $lvlInput = $request->level;
            $levelNum = (int) preg_replace('/[^0-9]/', '', (string) $lvlInput);
            if ($levelNum >= 1 && $levelNum <= Quiz::MAX_LEVEL) {
                $query->where('level', 'Level ' . $levelNum);
            } else {
                $query->where('level', $lvlInput);
            }
        }

        $soals = $query->orderBy('urutan')->get()->map(function ($s) use ($quiz) {
            $pilihan = $s->pilihan;
            if ($quiz->acak_jawaban && is_array($pilihan)) {
                $keys = array_keys($pilihan);
                shuffle($keys);
                $shuffled = [];
                foreach ($keys as $k) {
                    $shuffled[$k] = $pilihan[$k];
                }
                $pilihan = $shuffled;
            }

            $kunci = $s->kunci_jawaban;
            if (is_array($kunci)) {
                $kunci = $kunci[0] ?? 'a';
            } elseif (is_string($kunci)) {
                $decoded = json_decode($kunci, true);
                if (is_array($decoded)) {
                    $kunci = $decoded[0] ?? 'a';
                }
            }

            return [
                'id'            => $s->id,
                'urutan'        => $s->urutan,
                'kesulitan'     => $s->kesulitan,
                'level'         => $s->level,
                'tipe_soal'     => $s->tipe_soal,
                'pertanyaan'    => $s->pertanyaan,
                'pilihan'       => $pilihan,
                'kunci_jawaban' => strtolower(trim((string) $kunci)),
                'gambar_url'    => $s->gambar ? asset('storage/' . $s->gambar) : null,
                'bobot'         => $s->bobot,
            ];
        });

        return $this->successResponse([
            'quiz' => [
                'id'            => $quiz->id,
                'judul'         => $quiz->judul,
                'waktu_selesai' => $quiz->waktu_selesai?->format('Y-m-d H:i:s'),
            ],
            'soals' => $soals,
        ]);
    }

    /**
     * SISWA: Submit jawaban kuis, koreksi otomatis, dan rekam riwayat pengerjaan.
     */
    public function siswaSubmit(Request $request, Quiz $quiz)
    {
        $user = $request->user();
        $siswa = $user->siswa;
        $kelas = $siswa?->kelas ?? $siswa?->anggotaKelases()?->latest()?->first()?->kelas;
        $kelasId = $kelas?->id;

        if (!$kelasId || $quiz->pengampuKelas?->kelas_id !== $kelasId) {
            return $this->forbiddenResponse('Akses ditolak.');
        }

        if ($quiz->status !== 'published') {
            return $this->forbiddenResponse('Kuis belum dipublikasikan.');
        }

        // Cegah duplikasi submit jika seluruh kuis sudah tuntas
        $existing = Nilai::where('siswa_id', $siswa->id)->where('quiz_id', $quiz->id)->first();
        $totalSoalQuiz = $quiz->soals()->count();
        $answeredCount = Menjawab::where('siswa_id', $siswa->id)->whereIn('soal_id', $quiz->soals()->pluck('id'))->count();
        if ($existing && $totalSoalQuiz > 0 && $answeredCount >= $totalSoalQuiz) {
            return $this->errorResponse('Jawaban kuis Anda sebelumnya sudah tersimpan.', 400, [
                'nilai'  => $existing->nilai,
                'durasi' => $existing->durasi,
            ]);
        }

        // Cek toleransi batas waktu (toleransi 2 menit)
        if ($quiz->waktu_selesai && now()->greaterThan($quiz->waktu_selesai->copy()->addMinutes(2))) {
            return $this->forbiddenResponse('Waktu pengumpulan kuis telah kedaluwarsa.');
        }

        $validator = Validator::make($request->all(), [
            'jawaban' => ['required', 'array'], // [soal_id => "a", ...]
            'durasi'  => ['nullable'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $soals = $quiz->soals;
        $totalBobot = $soals->sum('bobot') ?: 100;
        $skorDidapat = 0;
        $jumlahBenar = 0;
        $jumlahSalah = 0;

        $wasAlreadyCompleted = Nilai::where('siswa_id', $siswa->id)->where('quiz_id', $quiz->id)->exists();

        $nilaiRecord = DB::transaction(function () use ($soals, $request, $siswa, $quiz, &$skorDidapat, &$jumlahBenar, &$jumlahSalah, $totalBobot) {
            // 1. Simpan jawaban yang dikirim pada sesi/level ini terlebih dahulu
            $submittedAnswers = $request->input('jawaban', []);
            foreach ($soals as $soal) {
                $jawabanUser = $submittedAnswers[$soal->id] ?? $submittedAnswers[(string) $soal->id] ?? null;

                // Jika tidak dikirim di request ini (misal sedang submit per level), ambil dari riwayat Menjawab sebelumnya
                if ($jawabanUser === null) {
                    $existingAns = Menjawab::where('soal_id', $soal->id)
                        ->where('siswa_id', $siswa->id)
                        ->first();
                    if ($existingAns) {
                        $jawabanUser = $existingAns->jawaban_dipilih;
                    }
                }

                $isBenar = false;

                if ($jawabanUser !== null && $jawabanUser !== '') {
                    $kunci = $soal->kunci_jawaban;
                    if (is_array($kunci)) {
                        $isBenar = in_array(strtolower(trim((string) $jawabanUser)), array_map('strtolower', $kunci));
                    } else {
                        $decodedKunci = json_decode((string) $kunci, true);
                        if (is_array($decodedKunci)) {
                            $isBenar = in_array(strtolower(trim((string) $jawabanUser)), array_map('strtolower', $decodedKunci));
                        } else {
                            $isBenar = strtolower(trim((string) $jawabanUser)) === strtolower(trim((string) $kunci));
                        }
                    }

                    if ($isBenar) {
                        $skorDidapat += ($soal->bobot ?: 10);
                        $jumlahBenar++;
                    } else {
                        $jumlahSalah++;
                    }

                    // Simpan ke tabel menjawabs
                    Menjawab::updateOrCreate(
                        [
                            'soal_id'  => $soal->id,
                            'siswa_id' => $siswa->id,
                        ],
                        [
                            'jawaban_dipilih' => is_array($jawabanUser) ? json_encode($jawabanUser) : (string) $jawabanUser,
                            'benar'           => $isBenar,
                        ]
                    );
                } else {
                    $jumlahSalah++;
                }
            }

            $nilaiAkhir = $totalBobot > 0 ? round(($skorDidapat / $totalBobot) * 100, 2) : 0;
            if ($nilaiAkhir > 100) $nilaiAkhir = 100;

            // Konversi durasi
            $rawDurasi = $request->input('durasi', 0);
            $durasiDetik = 0;
            if (is_numeric($rawDurasi)) {
                $durasiDetik = (int) $rawDurasi;
            } elseif (is_string($rawDurasi) && strpos($rawDurasi, ':') !== false) {
                $parts = explode(':', trim($rawDurasi));
                if (count($parts) === 2) {
                    $durasiDetik = ((int) $parts[0] * 60) + (int) $parts[1];
                } elseif (count($parts) === 3) {
                    $durasiDetik = ((int) $parts[0] * 3600) + ((int) $parts[1] * 60) + (int) $parts[2];
                }
            }

            return Nilai::updateOrCreate(
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

        // Kirim notifikasi interaktif ke Guru pengampu (hanya saat pertama kali selesai agar tidak spam)
        if (!$wasAlreadyCompleted) {
            $guruUser = $quiz->pengampuKelas?->guruMapel?->guru?->user;
            if ($guruUser) {
                $namaSiswa = $siswa->nama_siswa ?? $user->name;
                $namaKelas = $quiz->pengampuKelas?->kelas?->nama_kelas ?? 'Kelas';
                Notifikasi::kirim(
                    $guruUser->id,
                    "Kuis Selesai Dikerjakan: {$quiz->judul}",
                    "Siswa {$namaSiswa} ({$namaKelas}) telah menyelesaikan kuis petualangan '{$quiz->judul}' dengan nilai {$nilaiRecord->nilai}.",
                    'kuis_selesai',
                    route('guru.kuis.show', $quiz->id),
                    $quiz->id,
                    ['siswa_id' => $siswa->id, 'quiz_id' => $quiz->id, 'nilai' => $nilaiRecord->nilai]
                );
            }
        }

        return $this->successResponse([
            'nilai'        => $nilaiRecord->nilai,
            'total_soal'   => $soals->count(),
            'jumlah_benar' => $jumlahBenar,
            'jumlah_salah' => $jumlahSalah,
            'durasi_detik' => $nilaiRecord->durasi,
        ], 'Selamat! Kuis petualangan berhasil Anda selesaikan.');
    }

    /**
     * SISWA: Hasil evaluasi pengerjaan kuis siswa.
     */
    public function siswaHasil(Request $request, Quiz $quiz)
    {
        $siswa = $request->user()?->siswa;
        $kelas = $siswa?->kelas ?? $siswa?->anggotaKelases()?->latest()?->first()?->kelas;
        $kelasId = $kelas?->id;

        if (!$kelasId || $quiz->pengampuKelas?->kelas_id !== $kelasId) {
            return $this->forbiddenResponse('Akses ditolak.');
        }

        $nilai = Nilai::where('siswa_id', $siswa->id)->where('quiz_id', $quiz->id)->first();
        if (!$nilai) {
            return $this->notFoundResponse('Anda belum menyelesaikan kuis ini.');
        }

        $quiz->load(['pengampuKelas.guruMapel.mapel', 'pengampuKelas.guruMapel.guru']);

        $soals = $quiz->soals()->with(['menjawabs' => fn($q) => $q->where('siswa_id', $siswa->id)])->get();

        $totalSoal  = $soals->count();
        $totalBenar = $soals->filter(fn($s) => $s->menjawabs->first()?->benar)->count();
        $totalSalah = $totalSoal - $totalBenar;

        $detailSoal = $soals->map(function ($s) {
            $menjawab = $s->menjawabs->first();

            return [
                'id'              => $s->id,
                'pertanyaan'      => $s->pertanyaan,
                'tipe_soal'       => $s->tipe_soal,
                'kesulitan'       => $s->kesulitan,
                'level'           => $s->level,
                'pilihan'         => $s->pilihan,
                'kunci_jawaban'   => $s->kunci_jawaban,
                'jawaban_dipilih' => $menjawab?->jawaban_dipilih,
                'is_benar'        => (bool) $menjawab?->benar,
                'bobot'           => $s->bobot,
            ];
        });

        return $this->successResponse([
            'quiz' => [
                'id'    => $quiz->id,
                'judul' => $quiz->judul,
                'mapel' => $quiz->pengampuKelas?->guruMapel?->mapel?->nama_mapel,
                'guru'  => $quiz->pengampuKelas?->guruMapel?->guru?->nama,
            ],
            'summary' => [
                'nilai'        => $nilai->nilai,
                'durasi_detik' => $nilai->durasi,
                'total_soal'   => $totalSoal,
                'total_benar'  => $totalBenar,
                'total_salah'  => $totalSalah,
                'completed_at' => $nilai->created_at,
            ],
            'detail_soals' => $detailSoal,
        ]);
    }
}
