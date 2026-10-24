<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menjawab;
use App\Models\Nilai;
use App\Models\PengampuKelas;
use App\Models\Quiz;
use App\Models\Soal;
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
     * GURU: Menampilkan daftar semua kuis milik guru.
     */
    public function guruIndex(Request $request)
    {
        $guruId = $request->user()?->guru?->id;

        if (!$guruId) {
            return response()->json(['success' => false, 'message' => 'Profil guru tidak ditemukan.'], 404);
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

        if ($request->filled('kesulitan')) {
            $query->where('kesulitan', $request->kesulitan);
        }

        if ($request->filled('kelas_id')) {
            $query->whereHas('pengampuKelas', function ($q) use ($request) {
                $q->where('kelas_id', $request->kelas_id);
            });
        }

        $quizzes = $query->latest()->paginate(15);

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

        return response()->json([
            'success' => true,
            'data' => [
                'statistics' => [
                    'total_kuis' => $totalKuis,
                    'total_soal' => $totalSoal,
                    'kuis_mudah' => $kuisMudah,
                    'kuis_sulit' => $kuisSulit,
                ],
                'quizzes' => $quizzes,
            ],
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

        $soals = $quiz->soals->map(function ($s) {
            return [
                'id' => $s->id,
                'pertanyaan' => $s->pertanyaan,
                'tipe_soal' => $s->tipe_soal,
                'pilihan' => $s->pilihan,
                'kunci_jawaban' => $s->kunci_jawaban,
                'bobot' => $s->bobot,
                'urutan' => $s->urutan,
                'gambar' => $s->gambar,
                'gambar_url' => $s->gambar ? asset('storage/' . $s->gambar) : null,
            ];
        });

        $nilais = $quiz->nilais->map(function ($n) {
            return [
                'id' => $n->id,
                'siswa' => [
                    'id' => $n->siswa?->id,
                    'nama' => $n->siswa?->nama_siswa,
                    'nis' => $n->siswa?->nis,
                ],
                'nilai' => $n->nilai,
                'status' => $n->status,
                'durasi' => $n->durasi,
                'created_at' => $n->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $quiz->id,
                'judul' => $quiz->judul,
                'level' => $quiz->level,
                'kesulitan' => $quiz->kesulitan,
                'mapel' => $quiz->pengampuKelas?->guruMapel?->mapel?->nama_mapel,
                'kelas' => $quiz->pengampuKelas?->kelas?->nama_kelas,
                'pengampu_kelas_id' => $quiz->pengampu_kelas_id,
                'soals' => $soals,
                'nilais' => $nilais,
            ],
        ]);
    }

    /**
     * GURU: Simpan kuis baru.
     */
    public function guruStore(Request $request)
    {
        $guruId = $request->user()?->guru?->id;

        $validator = Validator::make($request->all(), [
            'judul'             => ['required', 'string', 'max:200'],
            'level'             => ['required', 'string', 'in:Level 1,Level 2,Level 3,Level 4,Level 5,1,2,3,4,5'],
            'kesulitan'         => ['required', 'string', 'in:Mudah,Sedang,Sulit'],
            'pengampu_kelas_id' => ['required', 'exists:pengampu_kelas,id'],
            'waktu_mulai'       => ['nullable', 'date', 'after_or_equal:today'],
            'waktu_selesai'     => ['nullable', 'date', 'after:waktu_mulai'],
            'acak_jawaban'      => ['nullable', 'boolean'],
        ], [
            'level.in'                   => 'Level kuis harus berada di rentang Level 1 sampai Level 5.',
            'kesulitan.in'               => 'Tingkat kesulitan hanya boleh: Mudah, Sedang, atau Sulit.',
            'waktu_mulai.after_or_equal' => 'Waktu mulai kuis tidak boleh sebelum hari ini.',
            'waktu_selesai.after'        => 'Waktu selesai kuis harus lebih lambat dari waktu mulai (tidak boleh sama atau lebih awal).',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        $isOwner = PengampuKelas::where('id', $validated['pengampu_kelas_id'])
            ->whereHas('guruMapel', function ($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            })->exists();

        if (!$isOwner) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki hak akses pada kelas/mata pelajaran ini.'], 403);
        }

        $levelNum = (int) preg_replace('/[^0-9]/', '', $validated['level']);
        if ($levelNum < 1 || $levelNum > Quiz::MAX_LEVEL) {
            return response()->json(['success' => false, 'message' => 'Level kuis tidak valid. Maksimal adalah Level 5.'], 422);
        }
        $validated['level'] = 'Level ' . $levelNum;

        $alreadyExists = Quiz::where('pengampu_kelas_id', $validated['pengampu_kelas_id'])
            ->where('kesulitan', $validated['kesulitan'])
            ->where('level', $validated['level'])
            ->exists();

        if ($alreadyExists) {
            return response()->json(['success' => false, 'message' => "{$validated['level']} untuk tingkat kesulitan {$validated['kesulitan']} pada kelas ini sudah pernah dibuat."], 422);
        }

        $countLevels = Quiz::where('pengampu_kelas_id', $validated['pengampu_kelas_id'])
            ->where('kesulitan', $validated['kesulitan'])
            ->count();

        if ($countLevels >= Quiz::MAX_LEVEL) {
            return response()->json(['success' => false, 'message' => "Tingkat kesulitan {$validated['kesulitan']} untuk kelas ini sudah memenuhi batas maksimal " . Quiz::MAX_LEVEL . " level kuis."], 422);
        }

        $quiz = Quiz::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Kuis berhasil dibuat.',
            'data' => $quiz,
        ], 201);
    }

    /**
     * GURU: Perbarui kuis.
     */
    public function guruUpdate(Request $request, Quiz $quiz)
    {
        $guruId = $request->user()?->guru?->id;
        $this->authorizeGuru($quiz, $guruId);

        $validator = Validator::make($request->all(), [
            'judul'             => ['required', 'string', 'max:200'],
            'level'             => ['required', 'string', 'in:Level 1,Level 2,Level 3,Level 4,Level 5,1,2,3,4,5'],
            'kesulitan'         => ['required', 'string', 'in:Mudah,Sedang,Sulit'],
            'pengampu_kelas_id' => ['required', 'exists:pengampu_kelas,id'],
            'waktu_mulai'       => ['nullable', 'date', 'after_or_equal:today'],
            'waktu_selesai'     => ['nullable', 'date', 'after:waktu_mulai'],
            'acak_jawaban'      => ['nullable', 'boolean'],
        ], [
            'level.in'                   => 'Level kuis harus berada di rentang Level 1 sampai Level 5.',
            'kesulitan.in'               => 'Tingkat kesulitan hanya boleh: Mudah, Sedang, atau Sulit.',
            'waktu_mulai.after_or_equal' => 'Waktu mulai kuis tidak boleh sebelum hari ini.',
            'waktu_selesai.after'        => 'Waktu selesai kuis harus lebih lambat dari waktu mulai (tidak boleh sama atau lebih awal).',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        $isOwner = PengampuKelas::where('id', $validated['pengampu_kelas_id'])
            ->whereHas('guruMapel', function ($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            })->exists();

        if (!$isOwner) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki hak akses pada kelas/mata pelajaran ini.'], 403);
        }

        $levelNum = (int) preg_replace('/[^0-9]/', '', $validated['level']);
        if ($levelNum < 1 || $levelNum > Quiz::MAX_LEVEL) {
            return response()->json(['success' => false, 'message' => 'Level kuis tidak valid. Maksimal adalah Level 5.'], 422);
        }
        $validated['level'] = 'Level ' . $levelNum;

        $alreadyExists = Quiz::where('pengampu_kelas_id', $validated['pengampu_kelas_id'])
            ->where('kesulitan', $validated['kesulitan'])
            ->where('level', $validated['level'])
            ->where('id', '!=', $quiz->id)
            ->exists();

        if ($alreadyExists) {
            return response()->json(['success' => false, 'message' => "{$validated['level']} untuk tingkat kesulitan {$validated['kesulitan']} sudah digunakan oleh kuis lain pada kelas ini."], 422);
        }

        $quiz->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Kuis berhasil diperbarui.',
            'data' => $quiz,
        ]);
    }

    /**
     * GURU: Hapus kuis.
     */
    public function guruDestroy(Request $request, Quiz $quiz)
    {
        $guruId = $request->user()?->guru?->id;
        $this->authorizeGuru($quiz, $guruId);

        // Hapus file gambar soal jika ada
        foreach ($quiz->soals as $soal) {
            if ($soal->gambar && Storage::disk('public')->exists($soal->gambar)) {
                Storage::disk('public')->delete($soal->gambar);
            }
        }

        $quiz->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kuis berhasil dihapus.',
        ]);
    }

    /**
     * GURU: Hapus beberapa kuis sekaligus (Bulk Destroy).
     */
    public function guruBulkDestroy(Request $request)
    {
        $guruId = $request->user()?->guru?->id;
        if (!$guruId) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'quiz_ids'   => ['required', 'array', 'min:1'],
            'quiz_ids.*' => ['required', 'exists:quizzes,id'],
        ], [
            'quiz_ids.required' => 'Pilih minimal satu kuis untuk dihapus.',
            'quiz_ids.min'      => 'Pilih minimal satu kuis untuk dihapus.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'errors' => $validator->errors()], 422);
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
            return response()->json(['success' => false, 'message' => 'Tidak ada kuis valid yang dapat dihapus.'], 404);
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

        return response()->json([
            'success' => true,
            'message' => "{$count} kuis berhasil dihapus.",
            'deleted_count' => $count,
        ]);
    }

    /**
     * GURU: Tambahkan soal ke kuis (Maksimal 10 soal).
     */
    public function guruStoreSoal(Request $request, Quiz $quiz)
    {
        $guruId = $request->user()?->guru?->id;
        $this->authorizeGuru($quiz, $guruId);

        if ($quiz->soals()->count() >= Quiz::MAX_SOAL) {
            return response()->json([
                'success' => false,
                'message' => 'Kuis ini sudah mencapai batas maksimal ' . Quiz::MAX_SOAL . ' butir soal untuk level ini.',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'pertanyaan'    => ['required', 'string'],
            'tipe_soal'     => ['required', 'in:single_choice,multiple_choice,essay,matching'],
            'pilihan'       => ['nullable', 'array'],
            'kunci_jawaban' => ['required'],
            'bobot'         => ['nullable', 'numeric', 'min:1'],
            'gambar'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

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

        $soal = Soal::create([
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

        return response()->json([
            'success' => true,
            'message' => 'Soal berhasil ditambahkan.',
            'data' => $soal,
        ], 201);
    }

    /**
     * GURU: Perbarui soal kuis.
     */
    public function guruUpdateSoal(Request $request, Quiz $quiz, Soal $soal)
    {
        $guruId = $request->user()?->guru?->id;
        $this->authorizeGuru($quiz, $guruId);

        if ($soal->quiz_id !== $quiz->id) {
            return response()->json(['success' => false, 'message' => 'Soal bukan milik kuis ini.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'pertanyaan'    => ['required', 'string'],
            'tipe_soal'     => ['required', 'in:single_choice,multiple_choice,essay,matching'],
            'pilihan'       => ['nullable', 'array'],
            'kunci_jawaban' => ['required'],
            'bobot'         => ['nullable', 'numeric', 'min:1'],
            'gambar'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        $gambarPath = $soal->gambar;
        if ($request->hasFile('gambar')) {
            if ($gambarPath && Storage::disk('public')->exists($gambarPath)) {
                Storage::disk('public')->delete($gambarPath);
            }
            $gambarPath = $request->file('gambar')->store('soals', 'public');
        }

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

        $soal->update([
            'pertanyaan'    => $validated['pertanyaan'],
            'tipe_soal'     => $validated['tipe_soal'],
            'pilihan'       => $pilihanFormatted,
            'kunci_jawaban' => $kunci,
            'bobot'         => $validated['bobot'] ?? $soal->bobot,
            'gambar'        => $gambarPath,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Soal berhasil diperbarui.',
            'data' => $soal,
        ]);
    }

    /**
     * GURU: Hapus soal kuis.
     */
    public function guruDestroySoal(Request $request, Quiz $quiz, Soal $soal)
    {
        $guruId = $request->user()?->guru?->id;
        $this->authorizeGuru($quiz, $guruId);

        if ($soal->quiz_id !== $quiz->id) {
            return response()->json(['success' => false, 'message' => 'Soal bukan milik kuis ini.'], 403);
        }

        if ($soal->gambar && Storage::disk('public')->exists($soal->gambar)) {
            Storage::disk('public')->delete($soal->gambar);
        }

        $soal->delete();

        return response()->json([
            'success' => true,
            'message' => 'Soal berhasil dihapus.',
        ]);
    }

    /**
     * SISWA: Menampilkan daftar kuis untuk kelas siswa.
     */
    public function siswaIndex(Request $request)
    {
        $siswa = $request->user()?->siswa;
        $kelasId = $siswa?->kelas?->id;

        if (!$kelasId) {
            return response()->json(['success' => false, 'message' => 'Data kelas siswa tidak ditemukan.'], 404);
        }

        $quizzes = Quiz::with([
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.guruMapel.guru',
            'nilais' => function ($q) use ($siswa) {
                $q->where('siswa_id', $siswa->id);
            },
        ])
        ->withCount('soals')
        ->whereHas('pengampuKelas', function ($q) use ($kelasId) {
            $q->where('kelas_id', $kelasId);
        })
        ->latest()
        ->get()
        ->map(function ($quiz) {
            $nilai = $quiz->nilais->first();

            return [
                'id' => $quiz->id,
                'judul' => $quiz->judul,
                'level' => $quiz->level,
                'kesulitan' => $quiz->kesulitan,
                'mapel' => $quiz->pengampuKelas?->guruMapel?->mapel?->nama_mapel,
                'guru' => $quiz->pengampuKelas?->guruMapel?->guru?->nama,
                'soals_count' => $quiz->soals_count,
                'is_completed' => $nilai !== null,
                'result' => $nilai ? [
                    'id' => $nilai->id,
                    'nilai' => $nilai->nilai,
                    'status' => $nilai->status,
                    'durasi' => $nilai->durasi,
                    'completed_at' => $nilai->created_at,
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $quizzes,
        ]);
    }

    /**
     * SISWA: Detail kuis & butir soal (Kunci jawaban disembunyikan!).
     */
    public function siswaShow(Request $request, Quiz $quiz)
    {
        $siswa = $request->user()?->siswa;
        $kelasId = $siswa?->kelas?->id;

        if (!$kelasId) {
            return response()->json(['success' => false, 'message' => 'Data kelas siswa tidak ditemukan.'], 404);
        }

        // Pastikan kuis untuk kelas siswa
        if ($quiz->pengampuKelas?->kelas_id !== $kelasId) {
            return response()->json(['success' => false, 'message' => 'Kuis tidak ditemukan untuk kelas Anda.'], 403);
        }

        // Cek jadwal mulai & selesai
        if ($quiz->waktu_mulai && now()->lessThan($quiz->waktu_mulai)) {
            return response()->json([
                'success' => false,
                'message' => 'Kuis belum dimulai. Jadwal mulai pada: ' . $quiz->waktu_mulai->format('d M Y, H:i') . ' WIB.',
            ], 403);
        }

        if ($quiz->waktu_selesai && now()->greaterThan($quiz->waktu_selesai)) {
            return response()->json([
                'success' => false,
                'message' => 'Kuis telah berakhir pada: ' . $quiz->waktu_selesai->format('d M Y, H:i') . ' WIB.',
            ], 403);
        }

        $quiz->load(['pengampuKelas.guruMapel.mapel', 'pengampuKelas.guruMapel.guru', 'soals']);

        $soals = $quiz->soals->map(function ($s) use ($quiz) {
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

            return [
                'id' => $s->id,
                'urutan' => $s->urutan,
                'tipe_soal' => $s->tipe_soal,
                'pertanyaan' => $s->pertanyaan,
                'pilihan' => $pilihan,
                'gambar_url' => $s->gambar ? asset('storage/' . $s->gambar) : null,
                'bobot' => $s->bobot,
                // Kunci jawaban disembunyikan
            ];
        });

        $riwayatNilai = Nilai::where('siswa_id', $siswa->id)
            ->where('quiz_id', $quiz->id)
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $quiz->id,
                'judul' => $quiz->judul,
                'level' => $quiz->level,
                'kesulitan' => $quiz->kesulitan,
                'waktu_mulai' => $quiz->waktu_mulai?->format('Y-m-d H:i:s'),
                'waktu_selesai' => $quiz->waktu_selesai?->format('Y-m-d H:i:s'),
                'acak_jawaban' => (bool)$quiz->acak_jawaban,
                'mapel' => $quiz->pengampuKelas?->guruMapel?->mapel?->nama_mapel,
                'guru' => $quiz->pengampuKelas?->guruMapel?->guru?->nama,
                'soals' => $soals,
                'completed' => $riwayatNilai !== null,
                'result' => $riwayatNilai ? [
                    'nilai' => $riwayatNilai->nilai,
                    'status' => $riwayatNilai->status,
                ] : null,
            ],
        ]);
    }

    /**
     * SISWA: Submit pengerjaan kuis (Koreksi otomatis soal pilihan).
     */
    public function siswaKerjakan(Request $request, Quiz $quiz)
    {
        $siswa = $request->user()?->siswa;
        $kelasId = $siswa?->kelas?->id;

        if (!$kelasId || $quiz->pengampuKelas?->kelas_id !== $kelasId) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        // Cek jadwal mulai & selesai
        if ($quiz->waktu_mulai && now()->lessThan($quiz->waktu_mulai)) {
            return response()->json([
                'success' => false,
                'message' => 'Kuis belum dimulai.',
            ], 403);
        }

        if ($quiz->waktu_selesai && now()->greaterThan($quiz->waktu_selesai)) {
            return response()->json([
                'success' => false,
                'message' => 'Waktu pengerjaan kuis telah berakhir.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'jawaban' => ['required', 'array'], // [soal_id => "a", ...]
            'durasi'  => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'errors' => $validator->errors()], 422);
        }

        $soals = $quiz->soals;
        $totalBobot = $soals->sum('bobot') ?: 100;
        $skorDidapat = 0;

        DB::transaction(function () use ($soals, $request, $siswa, $quiz, &$skorDidapat, $totalBobot) {
            foreach ($soals as $soal) {
                $jawabanUser = $request->input("jawaban.{$soal->id}");
                $isBenar = false;

                if ($jawabanUser !== null) {
                    $kunci = $soal->kunci_jawaban;
                    // Kunci bisa json atau string
                    $decodedKunci = json_decode($kunci, true);
                    if (is_array($decodedKunci)) {
                        $isBenar = in_array(strtolower(trim($jawabanUser)), array_map('strtolower', $decodedKunci));
                    } else {
                        $isBenar = strtolower(trim((string)$jawabanUser)) === strtolower(trim((string)$kunci));
                    }

                    if ($isBenar) {
                        $skorDidapat += ($soal->bobot ?: 10);
                    }
                }

                // Catat ke menjawabs
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

            // Normalisasi nilai ke skala 100 jika total bobot bukan 100
            $nilaiAkhir = $totalBobot > 0 ? round(($skorDidapat / $totalBobot) * 100, 2) : 0;
            if ($nilaiAkhir > 100) $nilaiAkhir = 100;

            Nilai::updateOrCreate(
                [
                    'siswa_id' => $siswa->id,
                    'quiz_id'  => $quiz->id,
                ],
                [
                    'nilai'  => $nilaiAkhir,
                    'durasi' => $request->input('durasi', '00:00'),
                    'status' => 'selesai',
                ]
            );
        });

        return response()->json([
            'success' => true,
            'message' => 'Kuis berhasil diselesaikan!',
            'data' => [
                'nilai' => Nilai::where('siswa_id', $siswa->id)->where('quiz_id', $quiz->id)->value('nilai'),
            ],
        ]);
    }
}
