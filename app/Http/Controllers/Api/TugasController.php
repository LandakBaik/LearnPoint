<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Nilai;
use App\Models\Notifikasi;
use App\Models\PengampuKelas;
use App\Models\Tugas;
use App\Models\Menjawab;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

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

        $query = Tugas::with([
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.kelas'
        ])
            ->whereHas('pengampuKelas.guruMapel', function ($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            })
            ->withCount([
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

        $tugases = $query->latest()->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'judul' => $item->judul,
                'deskripsi' => $item->deskripsi,
                'deadline' => $item->deadline,
                'tipe' => $item->tipe,
                'file_lampiran' => $item->file_lampiran,
                'file_url' => $item->file_lampiran
                    ? asset('storage/' . $item->file_lampiran)
                    : null,
                'is_deadline_passed' => Carbon::parse($item->deadline)->isPast(),
                'mapel' => $item->pengampuKelas?->guruMapel?->mapel?->nama_mapel,
                'kelas' => $item->pengampuKelas?->kelas?->nama_kelas,
                'tingkatan' => $item->pengampuKelas?->kelas?->tingkatan,
                'pengumpulans_count' => $item->pengumpulans_count,
                'pengumpulans_dinilai_count' => $item->pengumpulans_dinilai_count,
                'created_at' => $item->created_at,
            ];
        });

        $totalTugas = $tugases->count();
        $totalAktif = $tugases->where('is_deadline_passed', false)->count();
        $totalSelesai = $totalTugas - $totalAktif;

        $perluDinilai = Nilai::whereNull('nilai')
            ->whereHas('tugas.pengampuKelas.guruMapel', function ($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            })
            ->count();

        return $this->successResponse([
            'statistics' => [
                'total_tugas' => $totalTugas,
                'total_aktif' => $totalAktif,
                'total_selesai' => $totalSelesai,
                'perlu_dinilai' => $perluDinilai,
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
                    'kelas_id' => $pk->kelas?->id,
                    'nama_kelas' => $pk->kelas?->nama_kelas,
                    'tingkatan' => $pk->kelas?->tingkatan,
                    'mapel_id' => $pk->guruMapel?->mapel?->id,
                    'nama_mapel' => $pk->guruMapel?->mapel?->nama_mapel,
                ];
            });

        return $this->successResponse($pengampuKelases);
    }

    /**
     * GURU: Detail tugas & daftar pengumpulan siswa.
     */
    public function guruShow(Request $request, string $id)
    {
        $guruId = $request->user()?->guru?->id;

        $tugas = Tugas::with([
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.kelas',
            'nilais.siswa',
        ])->find($id);

        if (!$tugas) {
            return $this->notFoundResponse('Tugas tidak ditemukan.');
        }

        if ($tugas->pengampuKelas?->guruMapel?->guru_id !== $guruId) {
            return $this->forbiddenResponse('Akses ditolak.');
        }

        $pengumpulans = $tugas->nilais->map(function ($item) {
            return [
                'id' => $item->id,
                'siswa' => [
                    'id' => $item->siswa?->id,
                    'nama' => $item->siswa?->nama_siswa,
                    'nis' => $item->siswa?->nis,
                ],
                'file_jawaban' => $item->file_jawaban,
                'file_jawaban_url' => $item->file_jawaban
                    ? asset('storage/' . $item->file_jawaban)
                    : null,
                'nilai' => $item->nilai,
                'status' => $item->status,
                'submitted_at' => $item->created_at,
            ];
        });

        return $this->successResponse([
            'id' => $tugas->id,
            'judul' => $tugas->judul,
            'deskripsi' => $tugas->deskripsi,
            'deadline' => $tugas->deadline,
            'tipe' => $tugas->tipe,
            'file_lampiran' => $tugas->file_lampiran,
            'file_url' => $tugas->file_lampiran
                ? asset('storage/' . $tugas->file_lampiran)
                : null,
            'mapel' => $tugas->pengampuKelas?->guruMapel?->mapel?->nama_mapel,
            'kelas' => $tugas->pengampuKelas?->kelas?->nama_kelas,
            'pengampu_kelas_id' => $tugas->pengampu_kelas_id,
            'pengumpulans' => $pengumpulans,
        ]);
    }

    /**
     * GURU: Buat tugas baru.
     */
    public function guruStore(Request $request)
    {
        $guruId = $request->user()?->guru?->id;

        $validator = Validator::make($request->all(), [
            'judul' => ['required', 'string', 'max:200'],
            'deskripsi' => ['nullable', 'string'],
            'deadline' => ['required', 'date'],
            'tipe' => ['required', 'in:upload,pilihan_ganda'],
            'pengampu_kelas_id' => ['required', 'exists:pengampu_kelas,id'],
            'file_tugas_input' => [
                'nullable',
                'file',
                'mimes:pdf,docx,jpg,png',
                'max:10240'
            ],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $pengampu = PengampuKelas::where('id', $request->pengampu_kelas_id)
            ->whereHas('guruMapel', function ($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            })
            ->first();

        if (!$pengampu) {
            return $this->forbiddenResponse(
                'Kelas/Mapel tidak valid atau bukan wewenang Anda.'
            );
        }

        $filePath = null;

        if ($request->hasFile('file_tugas_input')) {
            $filePath = $request->file('file_tugas_input')
                ->store('tugas_lampiran', 'public');
        }

        $tugas = Tugas::create([
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'deadline' => Carbon::parse($request->deadline),
            'tipe' => $request->tipe,
            'file_lampiran' => $filePath,
            'pengampu_kelas_id' => $request->pengampu_kelas_id,
        ]);

        $mapelNama = $pengampu->guruMapel?->mapel?->nama_mapel
            ?? 'Mata Pelajaran';

        $deadlineFormatted = Carbon::parse($request->deadline)
            ->locale('id')
            ->isoFormat('D MMMM Y, HH:mm');

        Notifikasi::kirimKeKelas(
            $pengampu->kelas_id,
            "Tugas Baru: {$tugas->judul}",
            "Guru telah menerbitkan tugas baru untuk mata pelajaran {$mapelNama}. Batas pengumpulan: {$deadlineFormatted} WIB.",
            'tugas_baru',
            route('siswa.tugas.show', $tugas->id),
            $tugas->id
        );

        return $this->createdResponse($tugas, 'Tugas berhasil dibuat.');
    }

    /**
     * GURU: Perbarui tugas.
     */
    public function guruUpdate(Request $request, string $id)
    {
        $guruId = $request->user()?->guru?->id;

        $tugas = Tugas::with('pengampuKelas.guruMapel')->find($id);

        if (!$tugas) {
            return $this->notFoundResponse('Tugas tidak ditemukan.');
        }

        if ($tugas->pengampuKelas?->guruMapel?->guru_id !== $guruId) {
            return $this->forbiddenResponse('Akses ditolak.');
        }

        $validator = Validator::make($request->all(), [
            'judul' => ['required', 'string', 'max:200'],
            'deskripsi' => ['nullable', 'string'],
            'deadline' => ['required', 'date'],
            'tipe' => ['required', 'in:upload,pilihan_ganda'],
            'pengampu_kelas_id' => ['required', 'exists:pengampu_kelas,id'],
            'file_tugas_input' => [
                'nullable',
                'file',
                'mimes:pdf,docx,jpg,png',
                'max:10240'
            ],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $filePath = $tugas->file_lampiran;

        if ($request->hasFile('file_tugas_input')) {
            if ($filePath && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            $filePath = $request->file('file_tugas_input')
                ->store('tugas_lampiran', 'public');
        }

        $tugas->update([
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'deadline' => Carbon::parse($request->deadline),
            'tipe' => $request->tipe,
            'file_lampiran' => $filePath,
            'pengampu_kelas_id' => $request->pengampu_kelas_id,
        ]);

        return $this->successResponse($tugas, 'Tugas berhasil diperbarui.');
    }

    /**
     * GURU: Hapus tugas.
     */
    public function guruDestroy(Request $request, string $id)
    {
        $guruId = $request->user()?->guru?->id;

        $tugas = Tugas::with('pengampuKelas.guruMapel')->find($id);

        if (!$tugas) {
            return $this->notFoundResponse('Tugas tidak ditemukan.');
        }

        if ($tugas->pengampuKelas?->guruMapel?->guru_id !== $guruId) {
            return $this->forbiddenResponse('Akses ditolak.');
        }

        if (
            $tugas->file_lampiran &&
            Storage::disk('public')->exists($tugas->file_lampiran)
        ) {
            Storage::disk('public')->delete($tugas->file_lampiran);
        }

        $tugas->delete();

        return $this->successResponse(null, 'Tugas berhasil dihapus.');
    }

    /**
     * GURU: Berikan / perbarui nilai tugas siswa.
     */
    public function guruBerikanNilai(Request $request, string $id)
    {
        $guruId = $request->user()?->guru?->id;

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
            'nilai' => $request->nilai,
            'status' => 'selesai',
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
     * SISWA: Menampilkan daftar tugas untuk kelas siswa.
     */
    public function siswaIndex(Request $request)
    {
        $siswa = $request->user()?->siswa;

        $kelas = $siswa?->kelas
            ?? $siswa?->anggotaKelases()?->latest()?->first()?->kelas;

        $kelasId = $kelas?->id;

        if (!$kelasId) {
            return $this->notFoundResponse('Data kelas siswa tidak ditemukan.');
        }

        $daftarTugas = Tugas::with([
            'pengampuKelas.guruMapel.mapel',
            'nilais' => function ($query) use ($siswa) {
                $query->where('siswa_id', $siswa->id);
            },
        ])
            ->whereHas('pengampuKelas', function ($query) use ($kelasId) {
                $query->where('kelas_id', $kelasId);
            })
            ->latest()
            ->get()
            ->map(function ($tugas) {
                $pengumpulan = $tugas->nilais->first();

                return [
                    'id' => $tugas->id,
                    'judul' => $tugas->judul,
                    'deskripsi' => $tugas->deskripsi,
                    'deadline' => $tugas->deadline,
                    'tipe' => $tugas->tipe,
                    'file_lampiran' => $tugas->file_lampiran,
                    'file_url' => $tugas->file_lampiran
                        ? asset('storage/' . $tugas->file_lampiran)
                        : null,
                    'mapel' => $tugas->pengampuKelas?->guruMapel?->mapel?->nama_mapel,
                    'is_submitted' => $pengumpulan !== null,
                    'submission' => $pengumpulan ? [
                        'id' => $pengumpulan->id,
                        'file_jawaban_url' => $pengumpulan->file_jawaban
                            ? asset('storage/' . $pengumpulan->file_jawaban)
                            : null,
                        'nilai' => $pengumpulan->nilai,
                        'status' => $pengumpulan->status,
                        'submitted_at' => $pengumpulan->created_at,
                    ] : null,
                ];
            });

        return $this->successResponse(
            $daftarTugas,
            'Berhasil mengambil data tugas'
        );
    }

    /**
     * SISWA: Detail tugas siswa.
     */
    public function siswaShow(Request $request, string $id)
    {
        $siswa = $request->user()?->siswa;

        $kelas = $siswa?->kelas
            ?? $siswa?->anggotaKelases()?->latest()?->first()?->kelas;

        $kelasId = $kelas?->id;

        if (!$kelasId) {
            return $this->notFoundResponse('Data kelas siswa tidak ditemukan.');
        }

        $tugas = Tugas::with([
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.guruMapel.guru',
            'nilais' => function ($query) use ($siswa) {
                $query->where('siswa_id', $siswa->id);
            },
        ])
            ->whereHas('pengampuKelas', function ($query) use ($kelasId) {
                $query->where('kelas_id', $kelasId);
            })
            ->find($id);

        if (!$tugas) {
            return $this->notFoundResponse(
                'Tugas tidak ditemukan atau bukan untuk kelas Anda.'
            );
        }

        $pengumpulan = $tugas->nilais->first();

        $data = [
            'id' => $tugas->id,
            'judul' => $tugas->judul,
            'deskripsi' => $tugas->deskripsi,
            'deadline' => $tugas->deadline,
            'tipe' => $tugas->tipe,
            'file_lampiran' => $tugas->file_lampiran,
            'file_url' => $tugas->file_lampiran
                ? asset('storage/' . $tugas->file_lampiran)
                : null,
            'mapel' => $tugas->pengampuKelas?->guruMapel?->mapel?->nama_mapel,
            'guru' => $tugas->pengampuKelas?->guruMapel?->guru?->nama,
            'is_deadline_passed' => Carbon::parse($tugas->deadline)->isPast(),
            'submission' => $pengumpulan ? [
                'id' => $pengumpulan->id,
                'file_jawaban_url' => $pengumpulan->file_jawaban
                    ? asset('storage/' . $pengumpulan->file_jawaban)
                    : null,
                'nilai' => $pengumpulan->nilai,
                'status' => $pengumpulan->status,
                'submitted_at' => $pengumpulan->created_at,
            ] : null,
        ];

        if ($tugas->tipe === 'pilihan_ganda') {
            $soals = $tugas->soals()
                ->orderBy('urutan')
                ->get();

            $data['soals'] = $soals->map(function ($soal) {
                return [
                    'id' => $soal->id,
                    'urutan' => $soal->urutan,
                    'pertanyaan' => $soal->pertanyaan,
                    'gambar_url' => $soal->gambar
                        ? asset('storage/' . $soal->gambar)
                        : null,
                    'pilihan' => $soal->pilihan ?? [],
                    'bobot' => $soal->bobot,
                ];
            });

            $data['jawaban_siswa'] = $pengumpulan
                ? Menjawab::where('siswa_id', $siswa->id)
                    ->whereIn('soal_id', $soals->pluck('id'))
                    ->get(['soal_id', 'jawaban_dipilih'])
                    ->map(function ($jawaban) {
                        return [
                            'soal_id' => $jawaban->soal_id,
                            'jawaban_dipilih' => $jawaban->jawaban_dipilih,
                        ];
                    })
                : [];
        }

        return $this->successResponse($data);
    }

    /**
     * SISWA: Mengumpulkan jawaban tugas.
     */
    public function siswaKumpulkan(Request $request, string $id)
    {
        $siswa = $request->user()?->siswa;

        $kelas = $siswa?->kelas
            ?? $siswa?->anggotaKelases()?->latest()?->first()?->kelas;

        $kelasId = $kelas?->id;

        if (!$kelasId) {
            return $this->notFoundResponse('Data kelas siswa tidak ditemukan.');
        }

        $tugas = Tugas::with([
            'pengampuKelas.guruMapel.guru.user',
            'pengampuKelas.kelas'
        ])
            ->whereHas('pengampuKelas', function ($query) use ($kelasId) {
                $query->where('kelas_id', $kelasId);
            })
            ->find($id);

        if (!$tugas) {
            return $this->notFoundResponse('Tugas tidak ditemukan.');
        }

        /*
         * PENGUMPULAN PILIHAN GANDA
         */
        if ($tugas->tipe === 'pilihan_ganda') {
            $validator = Validator::make($request->all(), [
                'jawaban' => ['required', 'array'],
                'jawaban.*' => [
                    'required',
                    'in:a,b,c,d,e,A,B,C,D,E'
                ],
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $soals = $tugas->soals()
                ->orderBy('urutan')
                ->get();

            if ($soals->isEmpty()) {
                return $this->notFoundResponse(
                    'Soal pilihan ganda belum tersedia.'
                );
            }

            $jawabanSiswa = $request->input('jawaban', []);

            // Pastikan semua soal dijawab dan ID soal sesuai.
            $idSoalTugas = $soals->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->sort()
                ->values()
                ->all();

            $idSoalJawaban = collect(array_keys($jawabanSiswa))
                ->map(fn ($id) => (string) $id)
                ->sort()
                ->values()
                ->all();

            if ($idSoalTugas !== $idSoalJawaban) {
                return $this->validationErrorResponse([
                    'jawaban' => [
                        'Semua soal wajib dijawab dan ID soal harus sesuai dengan tugas.'
                    ],
                ]);
            }

            // Cegah pengumpulan dua kali.
            $sudahMengumpulkan = Nilai::where('siswa_id', $siswa->id)
                ->where('tugas_id', $tugas->id)
                ->exists();

            if ($sudahMengumpulkan) {
                return $this->forbiddenResponse(
                    'Tugas ini sudah pernah dikumpulkan.'
                );
            }

            $totalBobot = 0;
            $bobotBenar = 0;
            $hasilJawaban = [];

            foreach ($soals as $soal) {
                $soalId = (string) $soal->id;

                $jawabanDipilih = strtoupper(
                    trim((string) $jawabanSiswa[$soalId])
                );

                // kunci_jawaban sudah di-cast menjadi array di model Soal.
                $keyValue = $soal->kunci_jawaban;

                if (is_array($keyValue)) {
                    $keyValue = $keyValue['jawaban']
                        ?? $keyValue['kunci']
                        ?? $keyValue['answer']
                        ?? $keyValue[0]
                        ?? '';
                }

                $kunciJawaban = strtoupper(
                    trim((string) $keyValue)
                );

                if ($kunciJawaban === '') {
                    throw new \RuntimeException(
                        "Kunci jawaban soal {$soal->id} belum diatur dengan benar."
                    );
                }

                $bobot = (float) ($soal->bobot ?? 1);

                if ($bobot <= 0) {
                    $bobot = 1;
                }

                $benar = $jawabanDipilih === $kunciJawaban;

                $poin = Menjawab::hitungPoin(
                    $bobot,
                    $benar
                );

                $totalBobot += $bobot;

                if ($benar) {
                    $bobotBenar += $bobot;
                }

                $hasilJawaban[] = [
                    'soal_id' => $soal->id,
                    'jawaban_dipilih' => strtolower($jawabanDipilih),
                    'benar' => $benar,
                    'poin_diperoleh' => $poin,
                ];
            }

            // Nilai akhir dalam skala 0–100.
            $nilaiAkhir = $totalBobot > 0
                ? round(($bobotBenar / $totalBobot) * 100, 2)
                : 0;

            $isLate = now()->gt($tugas->deadline);
            $status = $isLate ? 'telat' : 'selesai';

            // Simpan pengumpulan dan jawaban secara bersamaan.
            $pengumpulan = DB::transaction(function () use (
                $siswa,
                $tugas,
                $hasilJawaban,
                $nilaiAkhir,
                $status
            ) {
                $pengumpulan = Nilai::create([
                    'siswa_id' => $siswa->id,
                    'tugas_id' => $tugas->id,
                    'nilai' => $nilaiAkhir,
                    'status' => $status,
                ]);

                foreach ($hasilJawaban as $jawaban) {
                    Menjawab::create([
                        'siswa_id' => $siswa->id,
                        'soal_id' => $jawaban['soal_id'],
                        'jawaban_dipilih' => $jawaban['jawaban_dipilih'],
                        'benar' => $jawaban['benar'],
                        'poin_diperoleh' => $jawaban['poin_diperoleh'],
                        'power_id' => null,
                    ]);
                }

                return $pengumpulan;
            });

            // Notifikasi kepada guru.
            $guruUser = $tugas->pengampuKelas?->guruMapel?->guru?->user;

            if ($guruUser) {
                $namaSiswa = $siswa->nama_siswa
                    ?? $request->user()->name;

                $namaKelas = $tugas->pengampuKelas?->kelas?->nama_kelas
                    ?? 'Kelas';

                Notifikasi::kirim(
                    $guruUser->id,
                    "Pengumpulan Tugas: {$tugas->judul}",
                    "Siswa {$namaSiswa} ({$namaKelas}) telah mengerjakan tugas pilihan ganda '{$tugas->judul}'. Nilai otomatis: {$nilaiAkhir}.",
                    'tugas_dikumpulkan',
                    route('guru.tugas.show', $tugas->id),
                    $tugas->id,
                    [
                        'siswa_id' => $siswa->id,
                        'tugas_id' => $tugas->id,
                        'nilai' => $nilaiAkhir,
                        'is_late' => $isLate,
                    ]
                );
            }

            return $this->successResponse([
                'id' => $pengumpulan->id,
                'nilai' => $nilaiAkhir,
                'status' => $pengumpulan->status,
                'is_late' => $isLate,
            ], 'Jawaban pilihan ganda berhasil dikumpulkan.');
        }

        /*
         * PENGUMPULAN UPLOAD FILE
         * Alur upload dipertahankan.
         */
        $validator = Validator::make($request->all(), [
            'file_jawaban' => [
                'required',
                'file',
                'mimes:pdf,docx,zip,rar,jpg,png',
                'max:10240'
            ],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $isLate = now()->gt($tugas->deadline);
        $status = $isLate ? 'telat' : 'selesai';

        $pengumpulan = Nilai::firstOrNew([
            'siswa_id' => $siswa->id,
            'tugas_id' => $tugas->id,
        ]);

        if (
            $pengumpulan->file_jawaban &&
            Storage::disk('public')->exists($pengumpulan->file_jawaban)
        ) {
            Storage::disk('public')->delete($pengumpulan->file_jawaban);
        }

        $filePath = $request->file('file_jawaban')
            ->store('lampiran_jawaban_tugas', 'public');

        $pengumpulan->file_jawaban = $filePath;
        $pengumpulan->status = $status;
        $pengumpulan->save();

        // Notifikasi kepada guru.
        $guruUser = $tugas->pengampuKelas?->guruMapel?->guru?->user;

        if ($guruUser) {
            $namaSiswa = $siswa->nama_siswa
                ?? $request->user()->name;

            $namaKelas = $tugas->pengampuKelas?->kelas?->nama_kelas
                ?? 'Kelas';

            if ($isLate) {
                Notifikasi::kirim(
                    $guruUser->id,
                    "Pengumpulan Tugas Terlambat: {$tugas->judul}",
                    "Siswa {$namaSiswa} ({$namaKelas}) baru saja mengumpulkan tugas '{$tugas->judul}' melewati batas deadline.",
                    'tugas_terlambat',
                    route('guru.tugas.show', $tugas->id),
                    $tugas->id,
                    [
                        'siswa_id' => $siswa->id,
                        'tugas_id' => $tugas->id,
                        'is_late' => true
                    ]
                );
            } else {
                Notifikasi::kirim(
                    $guruUser->id,
                    "Pengumpulan Tugas Masuk: {$tugas->judul}",
                    "Siswa {$namaSiswa} ({$namaKelas}) telah mengumpulkan tugas '{$tugas->judul}'.",
                    'tugas_dikumpulkan',
                    route('guru.tugas.show', $tugas->id),
                    $tugas->id,
                    [
                        'siswa_id' => $siswa->id,
                        'tugas_id' => $tugas->id,
                        'is_late' => false
                    ]
                );
            }
        }

        return $this->successResponse([
            'id' => $pengumpulan->id,
            'file_jawaban_url' => asset('storage/' . $filePath),
            'status' => $pengumpulan->status,
            'is_late' => $isLate,
        ], 'Tugas berhasil dikumpulkan.');
    }
}