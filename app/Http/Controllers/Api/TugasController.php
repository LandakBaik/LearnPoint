<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\Menjawab;
use App\Models\Nilai;
use App\Models\Notifikasi;
use App\Models\PengampuKelas;
use App\Models\Soal;
use App\Models\Tugas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


class TugasController extends Controller
{
    /**
     * GURU: Menampilkan daftar tugas & statistik.
     */
    public function guruIndex(Request $request)
    {
        $guruId = $request->user()?->guru?->id;

        if (!$guruId) {
            return $this->notFoundResponse('Profil guru tidak ditemukan.');
        }

        $query = Tugas::with(['pengampuKelas.guruMapel.mapel', 'pengampuKelas.kelas', 'soals'])
            ->whereHas('pengampuKelas.guruMapel', function ($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            })
            ->withCount([
                'soals',
                'nilais as pengumpulans_count',
                'nilais as pengumpulans_dinilai_count' => function ($q) {
                    $q->whereNotNull('nilai');
                },
            ]);

        if ($request->filled('search')) {
            $query->where('judul', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('kelas_id')) {
            $query->whereHas('pengampuKelas', function ($q) use ($request) {
                $q->where('kelas_id', $request->kelas_id);
            });
        }

        if ($request->filled('mapel_id')) {
            $query->whereHas('pengampuKelas.guruMapel', function ($q) use ($request) {
                $q->where('mapel_id', $request->mapel_id);
            });
        }

        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }

        $tugases = $query->latest()->get()->map(function ($item) {
            $deadlineCarbon = $item->deadline ? Carbon::parse($item->deadline) : null;
            return [
                'id'                         => $item->id,
                'judul'                      => $item->judul,
                'deskripsi'                  => $item->deskripsi,
                'deadline'                   => $item->deadline,
                'tipe'                       => $item->tipe,
                'file_lampiran'              => $item->file_lampiran,
                'file_url'                   => $item->file_lampiran ? url("/api/file/tugas/{$item->id}") : null,
                'is_deadline_passed'         => $deadlineCarbon ? $deadlineCarbon->isPast() : false,
                'is_deadline_near'           => $deadlineCarbon ? ($deadlineCarbon->isFuture() && $deadlineCarbon->lessThanOrEqualTo(now()->addHours(24))) : false,
                'pengampu_kelas_id'          => $item->pengampu_kelas_id,
                'mapel_id'                   => $item->pengampuKelas?->guruMapel?->mapel_id,
                'mapel'                      => $item->pengampuKelas?->guruMapel?->mapel?->nama_mapel,
                'kelas_id'                   => $item->pengampuKelas?->kelas_id,
                'kelas'                      => $item->pengampuKelas?->kelas?->nama_kelas,
                'tingkatan'                  => $item->pengampuKelas?->kelas?->tingkatan,
                'soals_count'                => $item->soals_count,
                'pengumpulans_count'         => $item->pengumpulans_count,
                'pengumpulans_dinilai_count' => $item->pengumpulans_dinilai_count,
                'created_at'                 => $item->created_at,
            ];
        });

        $totalTugas = $tugases->count();
        $totalAktif = $tugases->where('is_deadline_passed', false)->count();
        $totalSelesai = $totalTugas - $totalAktif;
        $deadlineTerdekat = $tugases->where('is_deadline_near', true)->count();

        $perluDinilai = Nilai::whereNull('nilai')
            ->whereHas('tugas.pengampuKelas.guruMapel', function ($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            })
            ->count();

        return $this->successResponse([
            'statistics' => [
                'total_tugas'       => $totalTugas,
                'total_aktif'       => $totalAktif,
                'total_selesai'     => $totalSelesai,
                'perlu_dinilai'     => $perluDinilai,
                'deadline_terdekat' => $deadlineTerdekat,
            ],
            'tugases' => $tugases,
        ]);
    }

    /**
     * GURU: Opsi kelas/mapel untuk form tugas.
     */
    public function guruOptions(Request $request)
    {
        $guruId = $request->user()?->guru?->id;
        if (!$guruId) {
            return $this->notFoundResponse('Profil guru tidak ditemukan.');
        }

        $pengampuKelases = PengampuKelas::with([
            'guruMapel.mapel',
            'kelas'
        ])
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
     * GURU: Detail tugas, butir soal pilihan ganda, & daftar pengumpulan seluruh siswa kelas.
     */
    public function guruShow(Request $request, string $id)
    {
        $guruId = $request->user()?->guru?->id;
        if (!$guruId) {
            return $this->notFoundResponse('Profil guru tidak ditemukan.');
        }

        $tugas = Tugas::with([
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.kelas',
            'soals',
            'nilais.siswa',
        ])->find($id);

        if (!$tugas) {
            return $this->notFoundResponse('Tugas tidak ditemukan.');
        }

        if ($tugas->pengampuKelas?->guruMapel?->guru_id !== $guruId) {
            return $this->forbiddenResponse('Akses ditolak.');
        }

        $kelas = $tugas->pengampuKelas?->kelas;
        $periodId = $tugas->pengampuKelas?->academic_period_id ?? AcademicPeriod::currentId();
        $mapel = $tugas->pengampuKelas?->guruMapel?->mapel;
        $targetAgama = $mapel?->target_agama;

        $siswas = $kelas
            ? $kelas->siswas()
            ->wherePivot('academic_period_id', $periodId)
            ->when(filled($targetAgama), fn($q) => $q->where('siswas.agama', $targetAgama))
            ->orderBy('siswas.nama_siswa')
            ->get()
            : collect();

        $pengumpulansRaw = Nilai::with('siswa')
            ->where('tugas_id', $tugas->id)
            ->orderByDesc('id')
            ->get();

        $pengumpulanBySiswa = $pengumpulansRaw
            ->unique('siswa_id')
            ->keyBy('siswa_id');

        $deadline = $tugas->deadline ? Carbon::parse($tugas->deadline) : null;

        $daftarSiswa = $siswas->map(function ($siswa) use ($pengumpulanBySiswa, $deadline) {
            $pengumpulan = $pengumpulanBySiswa->get($siswa->id);
            $waktuKumpul = $pengumpulan?->created_at ? Carbon::parse($pengumpulan->created_at) : null;
            $terlambat = $waktuKumpul && $deadline ? $waktuKumpul->greaterThan($deadline) : false;

            if (!$pengumpulan) {
                $statusFilter = 'belum_kumpul';
            } elseif ($pengumpulan->nilai !== null) {
                $statusFilter = 'sudah_dinilai';
            } else {
                $statusFilter = 'perlu_diperiksa';
            }

            return [
                'siswa_id'         => $siswa->id,
                'nama'             => $siswa->nama_siswa,
                'nis'              => $siswa->nis,
                'pengumpulan_id'   => $pengumpulan?->id,
                'waktu'            => $waktuKumpul ? $waktuKumpul->locale('id')->translatedFormat('d M Y, H:i') : null,
                'submitted_at'     => $pengumpulan?->created_at,
                'terlambat'        => (bool) $terlambat,
                'file_jawaban'     => $pengumpulan?->file_jawaban,
                'file_jawaban_url' => $pengumpulan && $pengumpulan->file_jawaban ? url("/api/file/jawaban/{$pengumpulan->id}") : null,
                'nilai'            => $pengumpulan?->nilai,
                'status'           => $pengumpulan?->status,
                'status_filter'    => $statusFilter,
            ];
        })->values();

        $pengumpulans = $tugas->nilais->map(function ($item) {
            return [
                'id'               => $item->id,
                'siswa'            => [
                    'id'   => $item->siswa?->id,
                    'nama' => $item->siswa?->nama_siswa,
                    'nis'  => $item->siswa?->nis,
                ],
                'file_jawaban'     => $item->file_jawaban,
                'file_jawaban_url' => $item->file_jawaban ? url("/api/file/jawaban/{$item->id}") : null,
                'nilai'            => $item->nilai,
                'status'           => $item->status,
                'submitted_at'     => $item->created_at,
            ];
        });

        $soals = $tugas->soals->sortBy('urutan')->values()->map(function ($s) {
            return [
                'id'            => $s->id,
                'urutan'        => $s->urutan,
                'pertanyaan'    => $s->pertanyaan,
                'tipe_soal'     => $s->tipe_soal,
                'pilihan'       => $s->pilihan,
                'kunci_jawaban' => $s->kunci_jawaban,
                'bobot'         => $s->bobot,
                'gambar'        => $s->gambar,
                'gambar_url'    => $s->gambar ? asset('storage/' . $s->gambar) : null,
            ];
        });

        return $this->successResponse([
            'id'                => $tugas->id,
            'judul'             => $tugas->judul,
            'deskripsi'         => $tugas->deskripsi,
            'deadline'          => $tugas->deadline,
            'tipe'              => $tugas->tipe,
            'file_lampiran'     => $tugas->file_lampiran,
            'file_url'          => $tugas->file_lampiran ? url("/api/file/tugas/{$tugas->id}") : null,
            'mapel'             => $tugas->pengampuKelas?->guruMapel?->mapel?->nama_mapel,
            'kelas'             => $tugas->pengampuKelas?->kelas?->nama_kelas,
            'pengampu_kelas_id' => $tugas->pengampu_kelas_id,
            'soals'             => $soals,
            'pengumpulans'      => $pengumpulans,
            'daftar_siswa'      => $daftarSiswa,
        ]);
    }

    /**
     * GURU: Kunci jawaban tugas pilihan ganda.
     */
    public function guruKunciJawaban(Request $request, string $id)
    {
        $guruId = $request->user()?->guru?->id;

        $tugas = Tugas::with([
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.kelas',
            'soals',
        ])->find($id);

        if (!$tugas) {
            return $this->notFoundResponse('Tugas tidak ditemukan.');
        }

        if ($tugas->pengampuKelas?->guruMapel?->guru_id !== $guruId) {
            return $this->forbiddenResponse('Akses ditolak.');
        }

        if ($tugas->tipe !== 'pilihan_ganda') {
            return $this->errorResponse('Tugas ini bukan tugas pilihan ganda.', 422);
        }

        $soals = $tugas->soals->sortBy('urutan')->values()->map(fn($s) => [
            'id'            => $s->id,
            'urutan'        => $s->urutan,
            'pertanyaan'    => $s->pertanyaan,
            'pilihan'       => $s->pilihan,
            'kunci_jawaban' => $s->kunci_jawaban,
            'bobot'         => $s->bobot,
            'gambar'        => $s->gambar,
            'gambar_url'    => $s->gambar ? asset('storage/' . $s->gambar) : null,
        ]);

        return $this->successResponse([
            'tugas' => [
                'id'    => $tugas->id,
                'judul' => $tugas->judul,
                'mapel' => $tugas->pengampuKelas?->guruMapel?->mapel?->nama_mapel,
                'kelas' => $tugas->pengampuKelas?->kelas?->nama_kelas,
            ],
            'soals' => $soals,
        ]);
    }

    /**
     * GURU: Detail jawaban siswa pada tugas pilihan ganda.
     */
    public function guruDetailJawabanSiswa(Request $request, string $id, string $pengumpulanId)
    {
        $guruId = $request->user()?->guru?->id;

        $tugas = Tugas::with([
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.kelas',
            'soals',
        ])->find($id);

        if (!$tugas) {
            return $this->notFoundResponse('Tugas tidak ditemukan.');
        }

        if ($tugas->pengampuKelas?->guruMapel?->guru_id !== $guruId) {
            return $this->forbiddenResponse('Akses ditolak.');
        }

        if ($tugas->tipe !== 'pilihan_ganda') {
            return $this->errorResponse('Tugas ini bukan tugas pilihan ganda.', 422);
        }

        $pengumpulan = Nilai::with(['siswa', 'tugas'])
            ->where('tugas_id', $tugas->id)
            ->find($pengumpulanId);

        if (!$pengumpulan) {
            return $this->notFoundResponse('Data pengumpulan tidak ditemukan.');
        }

        $soals = $tugas->soals->sortBy('urutan')->values();
        $jawabans = Menjawab::where('siswa_id', $pengumpulan->siswa_id)
            ->whereIn('soal_id', $soals->pluck('id'))
            ->get()
            ->keyBy('soal_id');

        $detailSoals = $soals->map(function ($s) use ($jawabans) {
            $jwb = $jawabans->get($s->id);
            return [
                'id'              => $s->id,
                'urutan'          => $s->urutan,
                'pertanyaan'      => $s->pertanyaan,
                'pilihan'         => $s->pilihan,
                'kunci_jawaban'   => $s->kunci_jawaban,
                'bobot'           => $s->bobot,
                'gambar_url'      => $s->gambar ? asset('storage/' . $s->gambar) : null,
                'jawaban_dipilih' => $jwb?->jawaban_dipilih,
                'benar'           => (bool) $jwb?->benar,
            ];
        });

        return $this->successResponse([
            'tugas' => [
                'id'    => $tugas->id,
                'judul' => $tugas->judul,
                'mapel' => $tugas->pengampuKelas?->guruMapel?->mapel?->nama_mapel,
                'kelas' => $tugas->pengampuKelas?->kelas?->nama_kelas,
            ],
            'siswa' => [
                'id'   => $pengumpulan->siswa?->id,
                'nama' => $pengumpulan->siswa?->nama_siswa,
                'nis'  => $pengumpulan->siswa?->nis,
            ],
            'pengumpulan' => [
                'id'           => $pengumpulan->id,
                'nilai'        => $pengumpulan->nilai,
                'status'       => $pengumpulan->status,
                'submitted_at' => $pengumpulan->created_at,
            ],
            'detail_soals' => $detailSoals,
        ]);
    }

    /**
     * Helper validasi tugas & soal pilihan ganda.
     */
    private function buildTugasValidator(Request $request, bool $isUpdate = false)
    {
        $rules = [
            'judul'               => ['required', 'string', 'max:200'],
            'deskripsi'           => ['nullable', 'string'],
            'deadline'            => $isUpdate ? ['required', 'date'] : ['required', 'date'],
            'tipe'                => ['required', 'in:upload,pilihan_ganda'],
            'pengampu_kelas_id'   => ['nullable'],
            'pengampu_kelas_ids'  => ['nullable', 'array', 'min:1'],
            'file_tugas_input'    => ['nullable', 'file', 'mimes:pdf,docx,jpg,jpeg,png', 'max:10240'],
            'soal'                => ['required_if:tipe,pilihan_ganda', 'array', 'min:1'],
            'soal.*.id'           => ['nullable', 'integer'],
            'soal.*.pertanyaan'   => ['required_if:tipe,pilihan_ganda', 'string'],
            'soal.*.gambar'       => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'soal.*.pilihan'      => [
                'required_if:tipe,pilihan_ganda',
                'array',
                function ($attribute, $value, $fail) {
                    if (!is_array($value)) {
                        $fail('Pilihan jawaban harus berupa daftar pilihan.');
                        return;
                    }
                    $jumlahPilihan = collect($value)
                        ->filter(fn($jawaban) => is_string($jawaban) && trim($jawaban) !== '')
                        ->count();
                    if ($jumlahPilihan < 2) {
                        $fail('Minimal harus mengisi 2 pilihan jawaban.');
                    }
                    if ($jumlahPilihan > 5) {
                        $fail('Maksimal hanya boleh mengisi 5 pilihan jawaban.');
                    }
                },
            ],
            'soal.*.pilihan.a'    => ['nullable', 'string'],
            'soal.*.pilihan.b'    => ['nullable', 'string'],
            'soal.*.pilihan.c'    => ['nullable', 'string'],
            'soal.*.pilihan.d'    => ['nullable', 'string'],
            'soal.*.pilihan.e'    => ['nullable', 'string'],
            'soal.*.kunci_jawaban' => [
                'required_if:tipe,pilihan_ganda',
                'in:a,b,c,d,e,A,B,C,D,E',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('tipe') !== 'pilihan_ganda') {
                        return;
                    }
                    preg_match('/soal\.(\d+)\./', $attribute, $matches);
                    $index = $matches[1] ?? null;
                    $pilihan = $request->input("soal.$index.pilihan", []);
                    $key = strtolower((string) $value);
                    $jawabanBenar = is_array($pilihan) ? ($pilihan[$key] ?? $pilihan[strtoupper($key)] ?? null) : null;
                    if (!is_string($jawabanBenar) || trim($jawabanBenar) === '') {
                        $fail('Kunci jawaban harus sesuai dengan pilihan yang sudah diisi.');
                    }
                },
            ],
        ];

        return Validator::make($request->all(), $rules, [
            'judul.required'                   => 'Judul tugas wajib diisi.',
            'deadline.required'                => 'Deadline tugas wajib diisi.',
            'tipe.required'                    => 'Jenis tugas wajib dipilih.',
            'soal.required_if'                 => 'Minimal harus ada satu soal pilihan ganda.',
            'soal.*.pertanyaan.required_if'    => 'Pertanyaan wajib diisi.',
            'soal.*.kunci_jawaban.required_if' => 'Kunci jawaban wajib dipilih.',
        ]);
    }

    /**
     * Helper menyimpan butir soal pilihan ganda tugas.
     */
    private function simpanSoal(Tugas $tugas, Request $request): void
    {
        if ($tugas->tipe !== 'pilihan_ganda') {
            $soalsLama = Soal::where('tugas_id', $tugas->id)->get();
            foreach ($soalsLama as $soalLama) {
                if ($soalLama->gambar && Storage::disk('public')->exists($soalLama->gambar)) {
                    Storage::disk('public')->delete($soalLama->gambar);
                }
            }
            Soal::where('tugas_id', $tugas->id)->delete();
            return;
        }

        $daftarSoal = $request->input('soal', []);
        $totalSoal = count($daftarSoal);
        if ($totalSoal === 0) {
            return;
        }

        $bobotPerSoal = 100 / $totalSoal;
        $soalIdsDipertahankan = [];

        foreach (array_values($daftarSoal) as $index => $item) {
            $pilihan = $item['pilihan'] ?? [];
            $dataPilihan = [];

            foreach (['a', 'b', 'c', 'd', 'e'] as $huruf) {
                $jawaban = $pilihan[$huruf] ?? $pilihan[strtoupper($huruf)] ?? null;
                $dataPilihan[$huruf] = is_string($jawaban) && trim($jawaban) !== '' ? trim($jawaban) : null;
            }

            $soalId = !empty($item['id']) ? (int) $item['id'] : null;
            $soal = $soalId
                ? Soal::where('tugas_id', $tugas->id)->where('id', $soalId)->first()
                : null;

            $gambarPath = $soal?->gambar;
            $gambarBaru = $request->file("soal.$index.gambar");

            if ($gambarBaru) {
                $gambarPath = $gambarBaru->store('gambar_soal', 'public');
            }

            $payload = [
                'pertanyaan'    => $item['pertanyaan'],
                'tipe_soal'     => 'single_choice',
                'pilihan'       => $dataPilihan,
                'kunci_jawaban' => strtolower($item['kunci_jawaban']),
                'gambar'        => $gambarPath,
                'bobot'         => $bobotPerSoal,
                'urutan'        => $index + 1,
                'quiz_id'       => null,
                'tugas_id'      => $tugas->id,
                'ujian_id'      => null,
            ];

            if ($soal) {
                $gambarLama = $soal->gambar;
                $soal->update($payload);
                if ($gambarBaru && $gambarLama && $gambarLama !== $gambarPath && Storage::disk('public')->exists($gambarLama)) {
                    Storage::disk('public')->delete($gambarLama);
                }
            } else {
                $soal = Soal::create($payload);
            }

            $soalIdsDipertahankan[] = $soal->id;
        }

        $soalYangDihapus = Soal::where('tugas_id', $tugas->id)
            ->whereNotIn('id', $soalIdsDipertahankan)
            ->get();

        foreach ($soalYangDihapus as $soal) {
            if ($soal->gambar && Storage::disk('public')->exists($soal->gambar)) {
                Storage::disk('public')->delete($soal->gambar);
            }
            $soal->delete();
        }
    }

    /**
     * GURU: Buat tugas baru (mendukung multi-kelas & soal pilihan ganda).
     */
    public function guruStore(Request $request)
    {
        $guruId = $request->user()?->guru?->id;
        if (!$guruId) {
            return $this->notFoundResponse('Profil guru tidak ditemukan.');
        }

        $validator = $this->buildTugasValidator($request, false);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $rawPengampu = $request->input('pengampu_kelas_ids') ?? $request->input('pengampu_kelas_id');
        if (empty($rawPengampu)) {
            return $this->validationErrorResponse([
                'pengampu_kelas_id' => ['Mata pelajaran dan kelas wajib dipilih.'],
            ]);
        }

        $pengampuIds = array_values(array_unique(array_map('intval', (array) $rawPengampu)));
        $pengampus = PengampuKelas::with('guruMapel.mapel')
            ->whereIn('id', $pengampuIds)
            ->whereHas('guruMapel', function ($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            })
            ->get();

        if ($pengampus->count() !== count($pengampuIds)) {
            return $this->forbiddenResponse('Kelas/Mapel tidak valid atau bukan wewenang Anda.');
        }

        if ($pengampus->pluck('guru_mapel_id')->unique()->count() > 1) {
            return $this->validationErrorResponse([
                'pengampu_kelas_id' => ['Pilih kelas untuk mata pelajaran yang sama.'],
            ]);
        }

        $filePath = null;
        if ($request->input('tipe') === 'upload' && $request->hasFile('file_tugas_input')) {
            $filePath = $request->file('file_tugas_input')->store('lampiran_tugas', 'local');
        }

        try {
            $hasilTugas = DB::transaction(function () use ($request, $filePath, $pengampus) {
                $hasil = collect();
                foreach ($pengampus as $pengampu) {
                    $tugas = Tugas::create([
                        'judul'             => $request->judul,
                        'deskripsi'         => $request->deskripsi,
                        'deadline'          => Carbon::parse($request->deadline),
                        'tipe'              => $request->tipe,
                        'file_lampiran'     => $filePath,
                        'pengampu_kelas_id' => $pengampu->id,
                    ]);

                    $this->simpanSoal($tugas, $request);

                    $hasil->push([
                        'tugas'    => $tugas->load('soals'),
                        'pengampu' => $pengampu,
                    ]);
                }
                return $hasil;
            });
        } catch (\Throwable $e) {
            if ($filePath && Storage::disk('local')->exists($filePath)) {
                Storage::disk('local')->delete($filePath);
            }
            throw $e;
        }

        $deadlineFormatted = Carbon::parse($request->deadline)->locale('id')->isoFormat('D MMMM Y, HH:mm');
        foreach ($hasilTugas as $item) {
            $tugas = $item['tugas'];
            $pengampu = $item['pengampu'];
            $mapelNama = $pengampu->guruMapel?->mapel?->nama_mapel ?? 'Mata Pelajaran';

            Notifikasi::kirimKeKelas(
                $pengampu->kelas_id,
                "Tugas Baru: {$tugas->judul}",
                "Guru telah menerbitkan tugas baru untuk mata pelajaran {$mapelNama}. Batas pengumpulan: {$deadlineFormatted} WIB.",
                'tugas_baru',
                route('siswa.tugas.show', $tugas->id),
                $tugas->id
            );
        }

        $firstTugas = $hasilTugas->first()['tugas'] ?? null;

        return $this->createdResponse(
            $hasilTugas->count() === 1 ? $firstTugas : $hasilTugas->pluck('tugas'),
            'Tugas berhasil dibuat untuk ' . $hasilTugas->count() . ' kelas.'
        );
    }

    /**
     * GURU: Perbarui tugas.
     */
    public function guruUpdate(Request $request, string $id)
    {
        $guruId = $request->user()?->guru?->id;
        if (!$guruId) {
            return $this->notFoundResponse('Profil guru tidak ditemukan.');
        }

        $tugas = Tugas::with('pengampuKelas.guruMapel')->find($id);

        if (!$tugas) {
            return $this->notFoundResponse('Tugas tidak ditemukan.');
        }

        if ($tugas->pengampuKelas?->guruMapel?->guru_id !== $guruId) {
            return $this->forbiddenResponse('Akses ditolak.');
        }

        $validator = $this->buildTugasValidator($request, true);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $pengampuKelasId = (int) ($request->input('pengampu_kelas_id') ?? $tugas->pengampu_kelas_id);
        $pengampu = PengampuKelas::where('id', $pengampuKelasId)
            ->whereHas('guruMapel', fn($q) => $q->where('guru_id', $guruId))
            ->first();

        if (!$pengampu) {
            return $this->forbiddenResponse('Kelas atau mata pelajaran bukan wewenang Anda.');
        }

        $fileLama = $tugas->file_lampiran;
        $fileBaru = null;

        if ($request->input('tipe') === 'upload' && $request->hasFile('file_tugas_input')) {
            $fileBaru = $request->file('file_tugas_input')->store('lampiran_tugas', 'local');
        }

        $filePath = $fileBaru ?? ($request->input('tipe') === 'upload' ? $fileLama : null);

        try {
            DB::transaction(function () use ($request, $tugas, $filePath, $pengampuKelasId) {
                $tugas->update([
                    'judul'             => $request->judul,
                    'deskripsi'         => $request->deskripsi,
                    'deadline'          => Carbon::parse($request->deadline),
                    'tipe'              => $request->tipe,
                    'file_lampiran'     => $filePath,
                    'pengampu_kelas_id' => $pengampuKelasId,
                ]);

                $this->simpanSoal($tugas, $request);
            });
        } catch (\Throwable $e) {
            if ($fileBaru && Storage::disk('local')->exists($fileBaru)) {
                Storage::disk('local')->delete($fileBaru);
            }
            throw $e;
        }

        if (
            $fileLama &&
            $fileLama !== $filePath &&
            !Tugas::where('file_lampiran', $fileLama)->exists()
        ) {
            Storage::disk('local')->delete($fileLama);
            Storage::disk('public')->delete($fileLama);
        }

        return $this->successResponse($tugas->fresh('soals'), 'Tugas berhasil diperbarui.');
    }

    /**
     * GURU: Hapus tugas.
     */
    public function guruDestroy(Request $request, string $id)
    {
        $guruId = $request->user()?->guru?->id;
        if (!$guruId) {
            return $this->notFoundResponse('Profil guru tidak ditemukan.');
        }

        $tugas = Tugas::with(['pengampuKelas.guruMapel', 'soals'])->find($id);
        if (!$tugas) {
            return $this->notFoundResponse('Tugas tidak ditemukan.');
        }

        if ($tugas->pengampuKelas?->guruMapel?->guru_id !== $guruId) {
            return $this->forbiddenResponse('Akses ditolak.');
        }

        $fileLama = $tugas->file_lampiran;
        $gambarSoals = $tugas->soals->pluck('gambar')->filter()->all();

        DB::transaction(function () use ($tugas) {
            Soal::where('tugas_id', $tugas->id)->delete();
            $tugas->delete();
        });

        foreach ($gambarSoals as $g) {
            if (!Soal::where('gambar', $g)->exists() && Storage::disk('public')->exists($g)) {
                Storage::disk('public')->delete($g);
            }
        }

        if (
            $fileLama &&
            !Tugas::where('file_lampiran', $fileLama)->exists()
        ) {
            Storage::disk('local')->delete($fileLama);
            Storage::disk('public')->delete($fileLama);
        }

        return $this->successResponse(null, 'Tugas berhasil dihapus.');
    }

    /**
     * GURU: Berikan / perbarui nilai tugas siswa.
     */
    public function guruBerikanNilai(Request $request, string $id)
    {
        $guruId = $request->user()?->guru?->id;
        if (!$guruId) {
            return $this->notFoundResponse('Profil guru tidak ditemukan.');
        }

        $validator = Validator::make($request->all(), [
            'nilai' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $pengumpulan = Nilai::with([
            'tugas.pengampuKelas.guruMapel',
            'siswa.user'
        ])->find($id);

        if (!$pengumpulan) {
            return $this->notFoundResponse('Data pengumpulan tidak ditemukan.');
        }

        if ($pengumpulan->tugas?->pengampuKelas?->guruMapel?->guru_id !== $guruId) {
            return $this->forbiddenResponse('Akses ditolak.');
        }

        $pengumpulan->update([
            'nilai'  => $request->nilai,
            'status' => $pengumpulan->status === 'telat' ? 'telat' : 'selesai',
        ]);

        if ($pengumpulan->siswa?->user) {
            $tugasJudul = $pengumpulan->tugas?->judul ?? 'Tugas';

            Notifikasi::kirim(
                $pengumpulan->siswa->user->id,
                "Nilai Tugas: {$tugasJudul}",
                "Guru telah memberikan nilai {$request->nilai} untuk tugas '{$tugasJudul}'. Silakan cek detail evaluasi Anda.",
                'nilai_tugas',
                route('siswa.tugas.show', $pengumpulan->tugas_id),
                $pengumpulan->tugas_id,
                [
                    'nilai' => $request->nilai,
                    'tugas_id' => $pengumpulan->tugas_id
                ]
            );
        }

        return $this->successResponse(
            $pengumpulan,
            'Nilai siswa berhasil disimpan.'
        );
    }

    /**
     * SISWA: Menampilkan daftar tugas untuk kelas siswa beserta statistik.
     */
    public function siswaIndex(Request $request)
    {
        $siswa = $request->user()?->siswa;

        $kelas = $siswa?->kelas;

        $kelasId = $kelas?->id;

        if (!$kelasId) {
            return $this->notFoundResponse('Data kelas siswa tidak ditemukan.');
        }

        $daftarTugas = Tugas::with([
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.guruMapel.guru',
            'nilais' => function ($query) use ($siswa) {
                $query->where('siswa_id', $siswa->id);
            },
        ])
            ->withCount('soals')
            ->whereHas('pengampuKelas', function ($query) use ($kelasId, $siswa) {
                $query->where('kelas_id', $kelasId)->forSiswa($siswa);
            })
            ->latest()
            ->get()
            ->map(function ($tugas) {
                $pengumpulan = $tugas->nilais->first();

                return [
                    'id'                 => $tugas->id,
                    'judul'              => $tugas->judul,
                    'deskripsi'          => $tugas->deskripsi,
                    'deadline'           => $tugas->deadline,
                    'is_deadline_passed' => $tugas->deadline ? Carbon::parse($tugas->deadline)->isPast() : false,
                    'tipe'               => $tugas->tipe,
                    'soals_count'        => $tugas->soals_count,
                    'file_lampiran'      => $tugas->file_lampiran,
                    'file_url'           => $tugas->file_lampiran ? url("/api/file/tugas/{$tugas->id}") : null,
                    'mapel'              => $tugas->pengampuKelas?->guruMapel?->mapel?->nama_mapel,
                    'guru'               => $tugas->pengampuKelas?->guruMapel?->guru?->nama,
                    'is_submitted'       => $pengumpulan !== null,
                    'submission'         => $pengumpulan ? [
                        'id'               => $pengumpulan->id,
                        'file_jawaban_url' => $pengumpulan->file_jawaban ? url("/api/file/jawaban/{$pengumpulan->id}") : null,
                        'nilai'            => $pengumpulan->nilai,
                        'status'           => $pengumpulan->status,
                        'submitted_at'     => $pengumpulan->created_at,
                    ] : null,
                ];
            });

        return $this->successResponse(
            $daftarTugas,
            'Berhasil mengambil data tugas'
        );
    }

    /**
     * SISWA: Detail tugas siswa (beserta butir soal jika tipe pilihan_ganda).
     */

    /**
     * SISWA: Detail tugas siswa (beserta butir soal jika tipe pilihan_ganda).
     */
    public function siswaShow(Request $request, string $id)
    {
        $siswa = $request->user()?->siswa;

        $kelas = $siswa?->kelas;

        $kelasId = $kelas?->id;

        if (!$kelasId) {
            return $this->notFoundResponse('Data kelas siswa tidak ditemukan.');
        }

        $tugas = Tugas::with([
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.guruMapel.guru',
            'soals',
            'nilais' => function ($query) use ($siswa) {
                $query->where('siswa_id', $siswa->id);
            },
        ])
            ->whereHas('pengampuKelas', function ($query) use ($kelasId, $siswa) {
                $query->where('kelas_id', $kelasId)->forSiswa($siswa);
            })
            ->find($id);

        if (!$tugas) {
            return $this->notFoundResponse(
                'Tugas tidak ditemukan atau bukan untuk kelas Anda.'
            );
        }

        $pengumpulan = $tugas->nilais->first();
        $soalsOrdered = $tugas->soals->sortBy('urutan')->values();

        $jawabanSiswa = collect();

        if ($tugas->tipe === 'pilihan_ganda' && $pengumpulan) {
            $jawabanSiswa = Menjawab::where('siswa_id', $siswa->id)
                ->whereIn('soal_id', $soalsOrdered->pluck('id'))
                ->get()
                ->keyBy('soal_id');
        }

        $soalsPayload = $soalsOrdered->map(function ($s) use ($pengumpulan, $jawabanSiswa) {
            $jwb = $jawabanSiswa->get($s->id);

            $item = [
                'id'         => $s->id,
                'urutan'     => $s->urutan,
                'pertanyaan' => $s->pertanyaan,
                'tipe_soal'  => $s->tipe_soal,
                'pilihan'    => $s->pilihan,
                'bobot'      => $s->bobot,
                'gambar'     => $s->gambar,
                'gambar_url' => $s->gambar
                    ? asset('storage/' . $s->gambar)
                    : null,
            ];

            if ($pengumpulan) {
                $item['kunci_jawaban'] = $s->kunci_jawaban;
                $item['jawaban_dipilih'] = $jwb?->jawaban_dipilih;
                $item['benar'] = (bool) $jwb?->benar;
            }

            return $item;
        });

        return $this->successResponse([
            'id'                 => $tugas->id,
            'judul'              => $tugas->judul,
            'deskripsi'          => $tugas->deskripsi,
            'deadline'           => $tugas->deadline,
            'tipe'               => $tugas->tipe,
            'file_lampiran'      => $tugas->file_lampiran,
            'file_url'           => $tugas->file_lampiran
                ? url("/api/file/tugas/{$tugas->id}")
                : null,
            'mapel'              => $tugas->pengampuKelas?->guruMapel?->mapel?->nama_mapel,
            'guru'               => $tugas->pengampuKelas?->guruMapel?->guru?->nama,
            'is_deadline_passed' => $tugas->deadline
                ? Carbon::parse($tugas->deadline)->isPast()
                : false,
            'soals'              => $soalsPayload,
            'jawaban_siswa'      => $pengumpulan
                ? $jawabanSiswa->map(function ($jawaban) {
                    return [
                        'soal_id' => $jawaban->soal_id,
                        'jawaban_dipilih' => $jawaban->jawaban_dipilih,
                    ];
                })->values()
                : [],
            'submission'         => $pengumpulan ? [
                'id'               => $pengumpulan->id,
                'file_jawaban_url' => $pengumpulan->file_jawaban
                    ? url("/api/file/jawaban/{$pengumpulan->id}")
                    : null,
                'nilai'            => $pengumpulan->nilai,
                'status'           => $pengumpulan->status,
                'submitted_at'     => $pengumpulan->created_at,
            ] : null,
        ]);
    }

    /**
     * SISWA: Mengumpulkan jawaban tugas (Mendukung tipe 'upload' & 'pilihan_ganda').
     */
    public function siswaKumpulkan(Request $request, string $id)
    {
        $siswa = $request->user()?->siswa;

        $kelas = $siswa?->kelas;

        $kelasId = $kelas?->id;

        if (!$kelasId) {
            return $this->notFoundResponse('Data kelas siswa tidak ditemukan.');
        }

        $tugas = Tugas::with(['soals', 'pengampuKelas.guruMapel.guru.user', 'pengampuKelas.kelas'])
            ->whereHas('pengampuKelas', function ($query) use ($kelasId, $siswa) {
                $query->where('kelas_id', $kelasId)->forSiswa($siswa);
            })->find($id);

        if (!$tugas) {
            return $this->notFoundResponse('Tugas tidak ditemukan.');
        }

        $isLate = $tugas->deadline && now()->gt($tugas->deadline);
        $status = $isLate ? 'telat' : 'selesai';

        // =========================================================
        // CABANG 1: TUGAS PILIHAN GANDA
        // =========================================================
        if ($tugas->tipe === 'pilihan_ganda') {
            $sudahMengumpulkan = Nilai::where('siswa_id', $siswa->id)
                ->where('tugas_id', $tugas->id)
                ->exists();

            if ($sudahMengumpulkan) {
                return $this->errorResponse('Kamu sudah mengumpulkan tugas ini dan tidak dapat mengubah jawaban.', 422);
            }

            $validator = Validator::make($request->all(), [
                'jawaban'   => ['required', 'array'],
                'jawaban.*' => ['required', 'in:a,b,c,d,e,A,B,C,D,E'],
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $jawaban = $request->input('jawaban', []);
            $soals = $tugas->soals()->orderBy('urutan')->get();

            if ($soals->isEmpty()) {
                return $this->errorResponse('Soal tugas belum tersedia.', 422);
            }

            $idSoal = $soals->pluck('id')->map(fn($sid) => (string) $sid)->sort()->values()->all();
            $idJawaban = collect(array_keys($jawaban))->map(fn($sid) => (string) $sid)->sort()->values()->all();

            if ($idSoal !== $idJawaban) {
                return $this->validationErrorResponse([
                    'jawaban' => ['Pastikan semua soal sudah dijawab.'],
                ]);
            }

            $totalBobot = 0;
            $bobotBenar = 0;
            $jumlahBenar = 0;
            $pengumpulan = null;

            DB::transaction(function () use ($soals, $jawaban, $siswa, $tugas, $status, &$totalBobot, &$bobotBenar, &$jumlahBenar, &$pengumpulan) {
                foreach ($soals as $soal) {
                    $jawabanDipilih = strtolower(trim((string) $jawaban[$soal->id]));
                    $kunci = $soal->kunci_jawaban;
                    if (is_array($kunci)) {
                        $kunci = $kunci[0] ?? '';
                    }
                    $kunci = strtolower(trim((string) $kunci));

                    $benar = $jawabanDipilih === $kunci;
                    $bobot = (float) ($soal->bobot ?? 1);
                    $totalBobot += $bobot;

                    if ($benar) {
                        $bobotBenar += $bobot;
                        $jumlahBenar++;
                    }

                    Menjawab::create([
                        'soal_id'         => $soal->id,
                        'siswa_id'        => $siswa->id,
                        'jawaban_dipilih' => $jawabanDipilih,
                        'benar'           => $benar,
                    ]);
                }

                $nilaiAkhir = $totalBobot > 0 ? round(($bobotBenar / $totalBobot) * 100, 2) : 0;

                $pengumpulan = Nilai::create([
                    'siswa_id'     => $siswa->id,
                    'tugas_id'     => $tugas->id,
                    'nilai'        => $nilaiAkhir,
                    'status'       => $status,
                    'file_jawaban' => null,
                    'durasi'       => null,
                ]);
            });

            // Pastikan data pengumpulan berhasil dibuat.
            if (!$pengumpulan instanceof Nilai) {
                return $this->errorResponse(
                    'Gagal menyimpan hasil pengumpulan tugas.',
                    500
                );
            }

            $guruUser = $tugas->pengampuKelas?->guruMapel?->guru?->user;
            if ($guruUser) {
                $namaSiswa = $siswa->nama_siswa ?? $request->user()->name;
                $namaKelas = $tugas->pengampuKelas?->kelas?->nama_kelas ?? 'Kelas';
                Notifikasi::kirim(
                    $guruUser->id,
                    ($isLate ? "Pengumpulan Tugas Terlambat: " : "Tugas Pilihan Ganda Dikumpulkan: ") . $tugas->judul,
                    "Siswa {$namaSiswa} ({$namaKelas}) telah menyelesaikan tugas pilihan ganda '{$tugas->judul}' dengan nilai {$pengumpulan->nilai}.",
                    $isLate ? 'tugas_terlambat' : 'tugas_dikumpulkan',
                    route('guru.tugas.show', $tugas->id),
                    $tugas->id,
                    ['siswa_id' => $siswa->id, 'tugas_id' => $tugas->id, 'nilai' => $pengumpulan->nilai, 'is_late' => $isLate]
                );
            }

            return $this->successResponse([
                'id'           => $pengumpulan->id,
                'nilai'        => $pengumpulan->nilai,
                'total_soal'   => $soals->count(),
                'jumlah_benar' => $jumlahBenar,
                'jumlah_salah' => $soals->count() - $jumlahBenar,
                'status'       => $pengumpulan->status,
                'is_late'      => $isLate,
            ], 'Tugas pilihan ganda berhasil dikumpulkan! Nilai kamu: ' . $pengumpulan->nilai);
        }

        // =========================================================
        // CABANG 2: TUGAS UPLOAD FILE
        // =========================================================
        $validator = Validator::make($request->all(), [
            'file_jawaban' => ['required', 'file', 'mimes:pdf,docx,zip,rar,jpg,jpeg,png', 'max:10240'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $pengumpulanLama = Nilai::where('siswa_id', $siswa->id)
            ->where('tugas_id', $tugas->id)
            ->first();

        if ($pengumpulanLama && $pengumpulanLama->nilai !== null) {
            return $this->errorResponse('Tugas sudah dinilai dan tidak dapat diubah lagi.', 422);
        }

        $filePath = $request->file('file_jawaban')->store('jawaban_tugas', 'local');

        $pengumpulan = Nilai::updateOrCreate(
            [
                'siswa_id' => $siswa->id,
                'tugas_id' => $tugas->id,
            ],
            [
                'file_jawaban' => $filePath,
                'nilai'        => null,
                'status'       => $status,
            ]
        );

        if ($pengumpulanLama && $pengumpulanLama->file_jawaban && $pengumpulanLama->file_jawaban !== $filePath) {
            Storage::disk('local')->delete($pengumpulanLama->file_jawaban);
            Storage::disk('public')->delete($pengumpulanLama->file_jawaban);
        }

        $guruUser = $tugas->pengampuKelas?->guruMapel?->guru?->user;

        if ($guruUser) {
            $namaSiswa = $siswa->nama_siswa
                ?? $request->user()->name;

            $namaKelas = $tugas->pengampuKelas?->kelas?->nama_kelas
                ?? 'Kelas';

            Notifikasi::kirim(
                $guruUser->id,
                ($isLate ? "Pengumpulan Tugas Terlambat: " : "Pengumpulan Tugas Masuk: ") . $tugas->judul,
                "Siswa {$namaSiswa} ({$namaKelas}) telah mengumpulkan tugas '{$tugas->judul}'" . ($isLate ? ' melewati batas deadline.' : '.'),
                $isLate ? 'tugas_terlambat' : 'tugas_dikumpulkan',
                route('guru.tugas.show', $tugas->id),
                $tugas->id,
                ['siswa_id' => $siswa->id, 'tugas_id' => $tugas->id, 'is_late' => $isLate]
            );
        }

        return $this->successResponse([
            'id'               => $pengumpulan->id,
            'file_jawaban_url' => url("/api/file/jawaban/{$pengumpulan->id}"),
            'status'           => $pengumpulan->status,
            'is_late'          => $isLate,
        ], 'Tugas berhasil dikumpulkan.');
    }
}
