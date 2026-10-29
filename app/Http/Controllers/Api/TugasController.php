<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Nilai;
use App\Models\Notifikasi;
use App\Models\PengampuKelas;
use App\Models\Tugas;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
            return response()->json(['success' => false, 'message' => 'Profil guru tidak ditemukan.'], 404);
        }

        $query = Tugas::with(['pengampuKelas.guruMapel.mapel', 'pengampuKelas.kelas'])
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
                'file_url' => $item->file_lampiran ? asset('storage/' . $item->file_lampiran) : null,
                'is_deadline_passed' => Carbon::parse($item->deadline)->isPast(),
                'mapel' => $item->pengampuKelas?->guruMapel?->mapel?->nama_mapel,
                'kelas' => $item->pengampuKelas?->kelas?->nama_kelas,
                'tingkatan' => $item->pengampuKelas?->kelas?->tingkatan,
                'pengumpulans_count' => $item->pengumpulans_count,
                'pengumpulans_dinilai_count' => $item->pengumpulans_dinilai_count,
                'created_at' => $item->created_at,
            ];
        });

        // Statistik
        $totalTugas = $tugases->count();
        $totalAktif = $tugases->where('is_deadline_passed', false)->count();
        $totalSelesai = $totalTugas - $totalAktif;

        $perluDinilai = Nilai::whereNull('nilai')
            ->whereHas('tugas.pengampuKelas.guruMapel', function ($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            })
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'statistics' => [
                    'total_tugas' => $totalTugas,
                    'total_aktif' => $totalAktif,
                    'total_selesai' => $totalSelesai,
                    'perlu_dinilai' => $perluDinilai,
                ],
                'tugases' => $tugases,
            ],
        ]);
    }

    /**
     * GURU: Opsi kelas/mapel untuk form tugas.
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
            return response()->json(['success' => false, 'message' => 'Tugas tidak ditemukan.'], 404);
        }

        if ($tugas->pengampuKelas?->guruMapel?->guru_id !== $guruId) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
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
                'file_jawaban_url' => $item->file_jawaban ? asset('storage/' . $item->file_jawaban) : null,
                'nilai' => $item->nilai,
                'status' => $item->status,
                'submitted_at' => $item->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $tugas->id,
                'judul' => $tugas->judul,
                'deskripsi' => $tugas->deskripsi,
                'deadline' => $tugas->deadline,
                'tipe' => $tugas->tipe,
                'file_lampiran' => $tugas->file_lampiran,
                'file_url' => $tugas->file_lampiran ? asset('storage/' . $tugas->file_lampiran) : null,
                'mapel' => $tugas->pengampuKelas?->guruMapel?->mapel?->nama_mapel,
                'kelas' => $tugas->pengampuKelas?->kelas?->nama_kelas,
                'pengampu_kelas_id' => $tugas->pengampu_kelas_id,
                'pengumpulans' => $pengumpulans,
            ],
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
            'file_tugas_input' => ['nullable', 'file', 'mimes:pdf,docx,jpg,png', 'max:10240'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $pengampu = PengampuKelas::where('id', $request->pengampu_kelas_id)
            ->whereHas('guruMapel', function ($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            })->first();

        if (!$pengampu) {
            return response()->json(['success' => false, 'message' => 'Kelas/Mapel tidak valid atau bukan wewenang Anda.'], 403);
        }

        $filePath = null;
        if ($request->hasFile('file_tugas_input')) {
            $filePath = $request->file('file_tugas_input')->store('tugas_lampiran', 'public');
        }

        $tugas = Tugas::create([
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'deadline' => Carbon::parse($request->deadline),
            'tipe' => $request->tipe,
            'file_lampiran' => $filePath,
            'pengampu_kelas_id' => $request->pengampu_kelas_id,
        ]);

        // Kirim notifikasi interaktif ke seluruh siswa di kelas sasaran
        $mapelNama = $pengampu->guruMapel?->mapel?->nama_mapel ?? 'Mata Pelajaran';
        $deadlineFormatted = Carbon::parse($request->deadline)->locale('id')->isoFormat('D MMMM Y, HH:mm');
        Notifikasi::kirimKeKelas(
            $pengampu->kelas_id,
            "Tugas Baru: {$tugas->judul}",
            "Guru telah menerbitkan tugas baru untuk mata pelajaran {$mapelNama}. Batas pengumpulan: {$deadlineFormatted} WIB.",
            'tugas_baru',
            route('siswa.tugas.show', $tugas->id),
            $tugas->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil dibuat.',
            'data' => $tugas,
        ], 201);
    }

    /**
     * GURU: Perbarui tugas.
     */
    public function guruUpdate(Request $request, string $id)
    {
        $guruId = $request->user()?->guru?->id;

        $tugas = Tugas::with('pengampuKelas.guruMapel')->find($id);
        if (!$tugas) {
            return response()->json(['success' => false, 'message' => 'Tugas tidak ditemukan.'], 404);
        }

        if ($tugas->pengampuKelas?->guruMapel?->guru_id !== $guruId) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'judul' => ['required', 'string', 'max:200'],
            'deskripsi' => ['nullable', 'string'],
            'deadline' => ['required', 'date'],
            'tipe' => ['required', 'in:upload,pilihan_ganda'],
            'pengampu_kelas_id' => ['required', 'exists:pengampu_kelas,id'],
            'file_tugas_input' => ['nullable', 'file', 'mimes:pdf,docx,jpg,png', 'max:10240'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $filePath = $tugas->file_lampiran;
        if ($request->hasFile('file_tugas_input')) {
            if ($filePath && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
            $filePath = $request->file('file_tugas_input')->store('tugas_lampiran', 'public');
        }

        $tugas->update([
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'deadline' => Carbon::parse($request->deadline),
            'tipe' => $request->tipe,
            'file_lampiran' => $filePath,
            'pengampu_kelas_id' => $request->pengampu_kelas_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil diperbarui.',
            'data' => $tugas,
        ]);
    }

    /**
     * GURU: Hapus tugas.
     */
    public function guruDestroy(Request $request, string $id)
    {
        $guruId = $request->user()?->guru?->id;

        $tugas = Tugas::with('pengampuKelas.guruMapel')->find($id);
        if (!$tugas) {
            return response()->json(['success' => false, 'message' => 'Tugas tidak ditemukan.'], 404);
        }

        if ($tugas->pengampuKelas?->guruMapel?->guru_id !== $guruId) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        if ($tugas->file_lampiran && Storage::disk('public')->exists($tugas->file_lampiran)) {
            Storage::disk('public')->delete($tugas->file_lampiran);
        }

        $tugas->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil dihapus.',
        ]);
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
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $pengumpulan = Nilai::with(['tugas.pengampuKelas.guruMapel', 'siswa.user'])->find($id);
        if (!$pengumpulan) {
            return response()->json(['success' => false, 'message' => 'Data pengumpulan tidak ditemukan.'], 404);
        }

        if ($pengumpulan->tugas?->pengampuKelas?->guruMapel?->guru_id !== $guruId) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $pengumpulan->update([
            'nilai' => $request->nilai,
            'status' => 'selesai',
        ]);

        // Kirim notifikasi interaktif ke akun siswa
        if ($pengumpulan->siswa?->user) {
            $tugasJudul = $pengumpulan->tugas?->judul ?? 'Tugas';
            Notifikasi::kirim(
                $pengumpulan->siswa->user->id,
                "Nilai Tugas: {$tugasJudul}",
                "Guru telah memberikan nilai {$request->nilai} untuk tugas '{$tugasJudul}'. Silakan cek detail evaluasi Anda.",
                'nilai_tugas',
                route('siswa.tugas.show', $pengumpulan->tugas_id),
                $pengumpulan->tugas_id,
                ['nilai' => $request->nilai, 'tugas_id' => $pengumpulan->tugas_id]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Nilai siswa berhasil disimpan.',
            'data' => $pengumpulan,
        ]);
    }

    /**
     * SISWA: Menampilkan daftar tugas untuk kelas siswa.
     */
    public function siswaIndex(Request $request)
    {
        $siswa = $request->user()?->siswa;
        $kelas = $siswa?->kelas ?? $siswa?->anggotaKelases()?->latest()?->first()?->kelas;
        $kelasId = $kelas?->id;

        if (!$kelasId) {
            return response()->json(['success' => false, 'message' => 'Data kelas siswa tidak ditemukan.'], 404);
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
                'file_url' => $tugas->file_lampiran ? asset('storage/' . $tugas->file_lampiran) : null,
                'mapel' => $tugas->pengampuKelas?->guruMapel?->mapel?->nama_mapel,
                'is_submitted' => $pengumpulan !== null,
                'submission' => $pengumpulan ? [
                    'id' => $pengumpulan->id,
                    'file_jawaban_url' => $pengumpulan->file_jawaban ? asset('storage/' . $pengumpulan->file_jawaban) : null,
                    'nilai' => $pengumpulan->nilai,
                    'status' => $pengumpulan->status,
                    'submitted_at' => $pengumpulan->created_at,
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil data tugas',
            'data'    => $daftarTugas,
        ]);
    }

    /**
     * SISWA: Detail tugas siswa.
     */
    public function siswaShow(Request $request, string $id)
    {
        $siswa = $request->user()?->siswa;
        $kelas = $siswa?->kelas ?? $siswa?->anggotaKelases()?->latest()?->first()?->kelas;
        $kelasId = $kelas?->id;

        if (!$kelasId) {
            return response()->json(['success' => false, 'message' => 'Data kelas siswa tidak ditemukan.'], 404);
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
            return response()->json(['success' => false, 'message' => 'Tugas tidak ditemukan atau bukan untuk kelas Anda.'], 404);
        }

        $pengumpulan = $tugas->nilais->first();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $tugas->id,
                'judul' => $tugas->judul,
                'deskripsi' => $tugas->deskripsi,
                'deadline' => $tugas->deadline,
                'tipe' => $tugas->tipe,
                'file_lampiran' => $tugas->file_lampiran,
                'file_url' => $tugas->file_lampiran ? asset('storage/' . $tugas->file_lampiran) : null,
                'mapel' => $tugas->pengampuKelas?->guruMapel?->mapel?->nama_mapel,
                'guru' => $tugas->pengampuKelas?->guruMapel?->guru?->nama,
                'is_deadline_passed' => Carbon::parse($tugas->deadline)->isPast(),
                'submission' => $pengumpulan ? [
                    'id' => $pengumpulan->id,
                    'file_jawaban_url' => $pengumpulan->file_jawaban ? asset('storage/' . $pengumpulan->file_jawaban) : null,
                    'nilai' => $pengumpulan->nilai,
                    'status' => $pengumpulan->status,
                    'submitted_at' => $pengumpulan->created_at,
                ] : null,
            ],
        ]);
    }

    /**
     * SISWA: Mengumpulkan jawaban tugas (Upload File).
     */
    public function siswaKumpulkan(Request $request, string $id)
    {
        $siswa = $request->user()?->siswa;
        $kelas = $siswa?->kelas ?? $siswa?->anggotaKelases()?->latest()?->first()?->kelas;
        $kelasId = $kelas?->id;

        if (!$kelasId) {
            return response()->json(['success' => false, 'message' => 'Data kelas siswa tidak ditemukan.'], 404);
        }

        $tugas = Tugas::with(['pengampuKelas.guruMapel.guru.user', 'pengampuKelas.kelas'])
            ->whereHas('pengampuKelas', function ($query) use ($kelasId) {
                $query->where('kelas_id', $kelasId);
            })->find($id);

        if (!$tugas) {
            return response()->json(['success' => false, 'message' => 'Tugas tidak ditemukan.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'file_jawaban' => ['required', 'file', 'mimes:pdf,docx,zip,rar,jpg,png', 'max:10240'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $isLate = now()->gt($tugas->deadline);
        $status = $isLate ? 'telat' : 'selesai';

        // Cari atau buat pengumpulan
        $pengumpulan = Nilai::firstOrNew([
            'siswa_id' => $siswa->id,
            'tugas_id' => $tugas->id,
        ]);

        if ($pengumpulan->file_jawaban && Storage::disk('public')->exists($pengumpulan->file_jawaban)) {
            Storage::disk('public')->delete($pengumpulan->file_jawaban);
        }

        $filePath = $request->file('file_jawaban')->store('lampiran_jawaban_tugas', 'public');

        $pengumpulan->file_jawaban = $filePath;
        $pengumpulan->status = $status;
        $pengumpulan->save();

        // Kirim notifikasi interaktif ke Guru pengampu
        $guruUser = $tugas->pengampuKelas?->guruMapel?->guru?->user;
        if ($guruUser) {
            $namaSiswa = $siswa->nama_siswa ?? $request->user()->name;
            $namaKelas = $tugas->pengampuKelas?->kelas?->nama_kelas ?? 'Kelas';

            if ($isLate) {
                Notifikasi::kirim(
                    $guruUser->id,
                    "Pengumpulan Tugas Terlambat: {$tugas->judul}",
                    "Siswa {$namaSiswa} ({$namaKelas}) baru saja mengumpulkan tugas '{$tugas->judul}' melewati batas deadline.",
                    'tugas_terlambat',
                    route('guru.tugas.show', $tugas->id),
                    $tugas->id,
                    ['siswa_id' => $siswa->id, 'tugas_id' => $tugas->id, 'is_late' => true]
                );
            } else {
                Notifikasi::kirim(
                    $guruUser->id,
                    "Pengumpulan Tugas Masuk: {$tugas->judul}",
                    "Siswa {$namaSiswa} ({$namaKelas}) telah mengumpulkan tugas '{$tugas->judul}'.",
                    'tugas_dikumpulkan',
                    route('guru.tugas.show', $tugas->id),
                    $tugas->id,
                    ['siswa_id' => $siswa->id, 'tugas_id' => $tugas->id, 'is_late' => false]
                );
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil dikumpulkan.',
            'data' => [
                'id' => $pengumpulan->id,
                'file_jawaban_url' => asset('storage/' . $filePath),
                'status' => $pengumpulan->status,
                'is_late' => $isLate,
            ],
        ]);
    }
}
