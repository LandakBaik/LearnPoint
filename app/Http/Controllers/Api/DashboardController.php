<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Materi;
use App\Models\Nilai;
use App\Models\Quiz;
use App\Models\SeleksiUjian;
use App\Models\Siswa;
use App\Models\Tugas;
use App\Models\Ujian;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Dashboard Data untuk Guru.
     */
    public function guruDashboard(Request $request)
    {
        $user = $request->user();
        $guru = $user->guru;

        if (!$guru) {
            return response()->json([
                'success' => false,
                'message' => 'Profil Guru tidak ditemukan untuk akun ini.',
            ], 404);
        }

        $guruId = $guru->id;

        $totalSiswa = Siswa::count();
        $totalKelas = Kelas::count();
        $totalMapel = Mapel::count();

        $totalKelasDiajar = $guru->pengampuKelases()->distinct('kelas_id')->count('kelas_id');
        if ($totalKelasDiajar === 0 && $guru->kelas) {
            $totalKelasDiajar = 1;
        }

        $totalMapelDiampu = $guru->guruMapels()->count();

        $totalMateri = Materi::whereHas('pengampuKelas.guruMapel', function ($q) use ($guruId) {
            $q->where('guru_id', $guruId);
        })->distinct('id_grub_materi')->count('id_grub_materi');

        $totalTugas = Tugas::whereHas('pengampuKelas.guruMapel', function ($q) use ($guruId) {
            $q->where('guru_id', $guruId);
        })->count();

        $totalKuis = Quiz::whereHas('pengampuKelas.guruMapel', function ($q) use ($guruId) {
            $q->where('guru_id', $guruId);
        })->count();

        $totalUjian = Ujian::whereHas('pengampuKelas.guruMapel', function ($q) use ($guruId) {
            $q->where('guru_id', $guruId);
        })->count();

        // Data Mata Pelajaran dan Kelas yang Diampu
        $guruMapels = $guru->guruMapels()
            ->with(['mapel', 'pengampuKelases.kelas'])
            ->get()
            ->map(function ($gm) {
                return [
                    'guru_mapel_id' => $gm->id,
                    'mapel_id'      => $gm->mapel?->id,
                    'nama_mapel'    => $gm->mapel?->nama_mapel,
                    'kode_mapel'    => $gm->mapel?->kode_mapel,
                    'kelases'       => $gm->pengampuKelases->map(function ($pk) {
                        return [
                            'pengampu_kelas_id' => $pk->id,
                            'kelas_id'          => $pk->kelas?->id,
                            'nama_kelas'        => $pk->kelas?->nama_kelas,
                            'tingkatan'         => $pk->kelas?->tingkatan,
                        ];
                    }),
                ];
            });

        // Pengumpulan Tugas yang Belum Dinilai (Perlu Tindakan)
        $perluDinilaiTugases = Nilai::with(['siswa', 'tugas.pengampuKelas.kelas', 'tugas.pengampuKelas.guruMapel.mapel'])
            ->whereNull('nilai')
            ->whereNotNull('tugas_id')
            ->whereHas('tugas.pengampuKelas.guruMapel', fn($q) => $q->where('guru_id', $guruId))
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id'               => $item->id,
                    'tugas_id'         => $item->tugas_id,
                    'judul_tugas'      => $item->tugas?->judul,
                    'mapel'            => $item->tugas?->pengampuKelas?->guruMapel?->mapel?->nama_mapel,
                    'nama_kelas'       => $item->tugas?->pengampuKelas?->kelas?->nama_kelas,
                    'siswa'            => [
                        'id'   => $item->siswa?->id,
                        'nama' => $item->siswa?->nama_siswa,
                        'nis'  => $item->siswa?->nis,
                    ],
                    'file_jawaban_url' => $item->file_jawaban ? asset('storage/' . $item->file_jawaban) : null,
                    'submitted_at'     => $item->created_at,
                ];
            });

        $perluDinilaiCount = Nilai::whereNull('nilai')
            ->whereNotNull('tugas_id')
            ->whereHas('tugas.pengampuKelas.guruMapel', fn($q) => $q->where('guru_id', $guruId))
            ->count();

        // Aktivitas Pembelajaran Terbaru Milik Guru
        $materis = Materi::whereHas('pengampuKelas.guruMapel', fn($q) => $q->where('guru_id', $guruId))
            ->with(['pengampuKelas.guruMapel.mapel', 'pengampuKelas.kelas'])
            ->latest()
            ->take(4)
            ->get()
            ->map(fn($m) => [
                'id'       => $m->id,
                'tipe'     => 'Materi',
                'judul'    => $m->judul,
                'kategori' => 'Modul Ajar',
                'mapel'    => $m->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? 'Mata Pelajaran',
                'kelas'    => $m->pengampuKelas?->kelas?->nama_kelas ?? '-',
                'waktu'    => $m->created_at,
                'status'   => 'Aktif',
            ]);

        $tugases = Tugas::whereHas('pengampuKelas.guruMapel', fn($q) => $q->where('guru_id', $guruId))
            ->with(['pengampuKelas.guruMapel.mapel', 'pengampuKelas.kelas'])
            ->latest()
            ->take(4)
            ->get()
            ->map(fn($t) => [
                'id'       => $t->id,
                'tipe'     => 'Tugas',
                'judul'    => $t->judul,
                'kategori' => 'Penugasan Siswa',
                'mapel'    => $t->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? 'Mata Pelajaran',
                'kelas'    => $t->pengampuKelas?->kelas?->nama_kelas ?? '-',
                'waktu'    => $t->created_at,
                'status'   => Carbon::parse($t->deadline)->isFuture() ? 'Berlangsung' : 'Selesai',
            ]);

        $quizzes = Quiz::whereHas('pengampuKelas.guruMapel', fn($q) => $q->where('guru_id', $guruId))
            ->with(['pengampuKelas.guruMapel.mapel', 'pengampuKelas.kelas'])
            ->latest()
            ->take(4)
            ->get()
            ->map(fn($q) => [
                'id'       => $q->id,
                'tipe'     => 'Kuis',
                'judul'    => $q->judul,
                'kategori' => 'Evaluasi Interaktif',
                'mapel'    => $q->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? 'Mata Pelajaran',
                'kelas'    => $q->pengampuKelas?->kelas?->nama_kelas ?? '-',
                'waktu'    => $q->created_at,
                'status'   => $q->status === 'published' ? 'Aktif' : 'Draft',
            ]);

        $aktivitasTerbaru = $materis->concat($tugases)->concat($quizzes)
            ->sortByDesc('waktu')
            ->take(5)
            ->values();

        return response()->json([
            'success' => true,
            'data'    => [
                'user' => [
                    'id'       => $user->id,
                    'name'     => $user->name,
                    'username' => $user->username,
                    'email'    => $user->email,
                    'role'     => $user->role,
                ],
                'guru' => [
                    'id'            => $guru->id,
                    'nama'          => $guru->nama,
                    'nip'           => $guru->nip,
                    'jenis_kelamin' => $guru->jenis_kelamin,
                    'nomor_telepon' => $guru->nomor_telepon,
                    'alamat'        => $guru->alamat,
                    'jadwal_url'    => $guru->jadwal_url,
                ],
                'statistics' => [
                    'total_siswa'        => $totalSiswa,
                    'total_kelas'        => $totalKelas,
                    'total_mapel'        => $totalMapel,
                    'total_kelas_diajar' => $totalKelasDiajar,
                    'total_mapel_diampu' => $totalMapelDiampu,
                    'total_materi'       => $totalMateri,
                    'total_tugas'        => $totalTugas,
                    'total_kuis'         => $totalKuis,
                    'total_ujian'        => $totalUjian,
                    'perlu_dinilai'      => $perluDinilaiCount,
                ],
                'guru_mapels'           => $guruMapels,
                'perlu_dinilai_tugases' => $perluDinilaiTugases,
                'aktivitas_terbaru'     => $aktivitasTerbaru,
            ],
        ]);
    }

    /**
     * Dashboard Data untuk Siswa.
     */
    public function siswaDashboard(Request $request)
    {
        $user = $request->user();
        $siswa = $user->siswa;

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Profil Siswa tidak ditemukan untuk akun ini.',
            ], 404);
        }

        $anggotaKelas = $siswa->anggotaKelases()?->latest()?->first();
        $kelas = $siswa->kelas ?? $anggotaKelas?->kelas;
        $kelasId = $kelas?->id;
        $tahunAjaran = $anggotaKelas?->tahun_ajaran ?? '2026/2027';
        $semester = $anggotaKelas?->semester ? ucfirst($anggotaKelas->semester) : 'Ganjil';

        $totalMapel = Mapel::count();
        $siswaId = $siswa->id;

        // Hitung badge counts
        $tugasMenantiCount = $kelasId ? Tugas::whereHas('pengampuKelas', fn($q) => $q->where('kelas_id', $kelasId))
            ->where('deadline', '>=', now())
            ->whereDoesntHave('nilais', fn($q) => $q->where('siswa_id', $siswaId))
            ->count() : 0;

        $quizCount = $kelasId ? Quiz::whereHas('pengampuKelas', fn($q) => $q->where('kelas_id', $kelasId))
            ->where('status', 'published')
            ->count() : 0;

        $ujianCount = $kelasId ? Ujian::whereHas('pengampuKelas', fn($q) => $q->where('kelas_id', $kelasId))
            ->whereIn('status', ['published', 'scheduled'])
            ->count() : 0;

        $unreadNotifCount = $user->notifikasi()->where('is_read', false)->count();

        // Tugas terbaru yang belum diselesaikan
        $tugasTerbaru = null;
        if ($kelasId) {
            $tugas = Tugas::whereHas('pengampuKelas', fn($q) => $q->where('kelas_id', $kelasId))
                ->with(['pengampuKelas.guruMapel.mapel', 'pengampuKelas.guruMapel.guru'])
                ->whereDoesntHave('nilais', fn($q) => $q->where('siswa_id', $siswaId))
                ->where('deadline', '>=', now())
                ->orderBy('deadline', 'asc')
                ->first();

            if (!$tugas) {
                $tugas = Tugas::whereHas('pengampuKelas', fn($q) => $q->where('kelas_id', $kelasId))
                    ->with(['pengampuKelas.guruMapel.mapel', 'pengampuKelas.guruMapel.guru'])
                    ->latest()
                    ->first();
            }

            if ($tugas) {
                $tugasTerbaru = [
                    'id'                 => $tugas->id,
                    'judul'              => $tugas->judul,
                    'deskripsi'          => $tugas->deskripsi,
                    'deadline'           => $tugas->deadline,
                    'mapel'              => $tugas->pengampuKelas?->guruMapel?->mapel?->nama_mapel,
                    'guru'               => $tugas->pengampuKelas?->guruMapel?->guru?->nama,
                    'is_deadline_passed' => Carbon::parse($tugas->deadline)->isPast(),
                ];
            }
        }

        // Materi terbaru
        $materiTerbaru = null;
        if ($kelasId) {
            $materi = Materi::whereHas('pengampuKelas', fn($q) => $q->where('kelas_id', $kelasId))
                ->with(['pengampuKelas.guruMapel.mapel', 'pengampuKelas.guruMapel.guru'])
                ->latest()
                ->first();

            if ($materi) {
                $materiTerbaru = [
                    'id'             => $materi->id,
                    'id_grub_materi' => $materi->id_grub_materi,
                    'judul'          => $materi->judul,
                    'deskripsi'      => $materi->deskripsi,
                    'file_url'       => $materi->file_materi ? asset('storage/' . $materi->file_materi) : null,
                    'url_youtube'    => $materi->url_youtube,
                    'mapel'          => $materi->pengampuKelas?->guruMapel?->mapel?->nama_mapel,
                    'guru'           => $materi->pengampuKelas?->guruMapel?->guru?->nama,
                    'created_at'     => $materi->created_at,
                ];
            }
        }

        // Kuis interaktif yang tersedia untuk kelas
        $quizzes = collect();
        if ($kelasId) {
            $quizzes = Quiz::whereHas('pengampuKelas', fn($q) => $q->where('kelas_id', $kelasId))
                ->where('status', 'published')
                ->with([
                    'pengampuKelas.guruMapel.mapel',
                    'pengampuKelas.guruMapel.guru',
                    'nilais' => fn($q) => $q->where('siswa_id', $siswaId),
                ])
                ->withCount('soals')
                ->latest()
                ->take(6)
                ->get()
                ->map(function ($q) {
                    $riwayat = $q->nilais->first();
                    return [
                        'id'           => $q->id,
                        'judul'        => $q->judul,
                        'level'        => $q->level,
                        'kesulitan'    => $q->kesulitan,
                        'mapel'        => $q->pengampuKelas?->guruMapel?->mapel?->nama_mapel,
                        'guru'         => $q->pengampuKelas?->guruMapel?->guru?->nama,
                        'total_soal'   => $q->soals_count,
                        'is_completed' => $riwayat !== null,
                        'nilai'        => $riwayat?->nilai,
                    ];
                });
        }

        // =========================================================
        // STATISTIK NILAI SISWA (Real / Benchmark jika belum ada)
        // =========================================================
        $avgTugasReal = Nilai::where('siswa_id', $siswaId)->whereNotNull('tugas_id')->whereNotNull('nilai')->avg('nilai');
        $avgQuizReal = Nilai::where('siswa_id', $siswaId)->whereNotNull('quiz_id')->whereNotNull('nilai')->avg('nilai');
        $avgUjianReal = SeleksiUjian::where('siswa_id', $siswaId)->whereNotNull('nilai_akhir')->avg('nilai_akhir')
            ?? Nilai::where('siswa_id', $siswaId)->whereNotNull('seleksi_ujian_id')->whereNotNull('nilai')->avg('nilai');

        $hasRealData = ($avgTugasReal !== null || $avgQuizReal !== null || $avgUjianReal !== null);

        $rataRataTugas = $avgTugasReal !== null ? round((float)$avgTugasReal, 1) : 85.0;
        $rataRataQuiz  = $avgQuizReal !== null ? round((float)$avgQuizReal, 1) : 90.0;
        $rataRataUjian = $avgUjianReal !== null ? round((float)$avgUjianReal, 1) : 86.5;

        $rataRataSemua = $hasRealData
            ? round(($rataRataTugas + $rataRataQuiz + $rataRataUjian) / 3, 1)
            : 87.2;

        if ($rataRataSemua >= 90) {
            $kategoriNilai = 'Sangat Baik';
        } elseif ($rataRataSemua >= 80) {
            $kategoriNilai = 'Baik';
        } elseif ($rataRataSemua >= 70) {
            $kategoriNilai = 'Cukup';
        } else {
            $kategoriNilai = 'Perlu Bimbingan';
        }

        $donutDegree = (int) round(($rataRataSemua / 100) * 360);

        // Nilai per Mapel untuk visualisasi mobile
        $daftarMapel = collect();
        if ($kelasId) {
            $daftarMapel = Mapel::whereHas('pengampuKelases', fn($q) => $q->where('kelas_id', $kelasId))
                ->with(['pengampuKelases' => fn($q) => $q->where('kelas_id', $kelasId)->with(['guruMapel.guru'])])
                ->get();
        }

        $listNilaiMapel = [];
        $palette = ['#2563EB', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#06B6D4'];
        $idx = 0;

        foreach ($daftarMapel as $mapelItem) {
            $realMapelScore = Nilai::where('siswa_id', $siswaId)
                ->whereHas('tugas.pengampuKelas.guruMapel', fn($q) => $q->where('mapel_id', $mapelItem->id))
                ->whereNotNull('nilai')
                ->avg('nilai');

            $score = $realMapelScore !== null ? round((float)$realMapelScore, 1) : 85.0;
            $listNilaiMapel[] = [
                'mapel_id'   => $mapelItem->id,
                'nama_mapel' => $mapelItem->nama_mapel,
                'kode_mapel' => $mapelItem->kode_mapel,
                'nilai'      => $score,
                'color'      => $palette[$idx % count($palette)],
            ];
            $idx++;
        }

        // Daftar Guru Pengampu Kelas
        $guruPengampus = collect();
        if ($kelas) {
            $guruPengampus = $kelas->pengampuKelases()
                ->with(['guruMapel.guru', 'guruMapel.mapel'])
                ->get()
                ->map(function ($pk) {
                    return [
                        'pengampu_kelas_id' => $pk->id,
                        'mapel'             => $pk->guruMapel?->mapel?->nama_mapel,
                        'kode_mapel'        => $pk->guruMapel?->mapel?->kode_mapel,
                        'guru'              => $pk->guruMapel?->guru?->nama,
                        'nip'               => $pk->guruMapel?->guru?->nip,
                    ];
                });
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'user' => [
                    'id'       => $user->id,
                    'name'     => $user->name,
                    'username' => $user->username,
                    'email'    => $user->email,
                    'role'     => $user->role,
                ],
                'siswa' => [
                    'id'            => $siswa->id,
                    'nama_siswa'    => $siswa->nama_siswa,
                    'nis'           => $siswa->nis,
                    'jenis_kelamin' => $siswa->jenis_kelamin,
                    'alamat'        => $siswa->alamat,
                ],
                'kelas' => $kelas ? [
                    'id'         => $kelas->id,
                    'nama_kelas' => $kelas->nama_kelas,
                    'tingkatan'  => $kelas->tingkatan,
                    'wali_kelas' => $kelas->guru?->nama,
                    'jadwal_url' => $kelas->jadwal_url,
                ] : null,
                'akademik' => [
                    'tahun_ajaran' => $tahunAjaran,
                    'semester'     => $semester,
                ],
                'statistics' => [
                    'total_mapel'         => $totalMapel,
                    'tugas_menanti_count' => $tugasMenantiCount,
                    'quiz_count'          => $quizCount,
                    'ujian_count'         => $ujianCount,
                    'unread_notif_count'  => $unreadNotifCount,
                ],
                'evaluasi' => [
                    'rata_rata_semua' => $rataRataSemua,
                    'kategori_nilai'  => $kategoriNilai,
                    'donut_degree'    => $donutDegree,
                    'rata_rata_tugas' => $rataRataTugas,
                    'rata_rata_quiz'  => $rataRataQuiz,
                    'rata_rata_ujian' => $rataRataUjian,
                    'nilai_mapel'     => $listNilaiMapel,
                ],
                'tugas_terbaru'   => $tugasTerbaru,
                'materi_terbaru'  => $materiTerbaru,
                'kuis_tersedia'   => $quizzes,
                'guru_pengampu'   => $guruPengampus,
            ],
        ]);
    }
}
