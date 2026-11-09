<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Mapel;
use App\Models\Materi;
use App\Models\Tugas;
use App\Models\Ujian;
use Illuminate\Http\Request;

class MateriController extends Controller
{
    /**
     * Halaman Beranda / Daftar Materi untuk Siswa.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $siswa = $user?->siswa;
        $anggotaKelas = $siswa?->anggotaKelases()?->latest()?->first();
        $kelas = $siswa?->kelas ?? $anggotaKelas?->kelas;
        $kelasId = $kelas?->id;

        if ($kelasId) {
            $daftarMapel = Mapel::whereHas('pengampuKelases', function ($query) use ($kelasId) {
                $query->where('kelas_id', $kelasId);
            })
                ->with(['pengampuKelases' => function ($query) use ($kelasId) {
                    $query->where('kelas_id', $kelasId)
                        ->with(['guruMapel.guru', 'materis']);
                }, 'guruMapels.guru'])
                ->get();
        } else {
            $daftarMapel = collect();
        }

        if ($daftarMapel->isEmpty()) {
            $daftarMapel = Mapel::with([
                'pengampuKelases.guruMapel.guru',
                'pengampuKelases.materis',
                'guruMapels.guru'
            ])->get();
        }

        return view('siswa.materi.index', compact('daftarMapel', 'kelas', 'siswa'));
    }

    public function indexSiswa(Request $request)
    {
        return $this->index($request);
    }

    /**
     * Menampilkan detail materi, tugas, dan ujian dari mapel yang dipilih.
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $siswa = $user?->siswa;
        $anggotaKelas = $siswa?->anggotaKelases()?->latest()?->first();
        $kelas = $siswa?->kelas ?? $anggotaKelas?->kelas;
        $kelasId = $kelas?->id;
        $tahunAjaran = $anggotaKelas?->tahun_ajaran ?? '2026/2027';
        $semester = $anggotaKelas?->semester ? ucfirst($anggotaKelas->semester) : 'Ganjil';

        $mapel = Mapel::with(['guruMapels.guru', 'pengampuKelases.guruMapel.guru'])->findOrFail($id);

        // Ambil materi untuk mapel ini
        if ($kelasId) {
            $materis = Materi::with(['pengampuKelas.guruMapel.guru', 'pengampuKelas.kelas'])
                ->whereHas('pengampuKelas', function ($query) use ($kelasId, $id) {
                    $query->where('kelas_id', $kelasId)
                        ->whereHas('guruMapel', function ($query) use ($id) {
                            $query->where('mapel_id', $id);
                        });
                })
                ->latest()
                ->get();
        } else {
            $materis = collect();
        }

        if ($materis->isEmpty()) {
            $materis = Materi::with(['pengampuKelas.guruMapel.guru', 'pengampuKelas.kelas'])
                ->whereHas('pengampuKelas.guruMapel', function ($query) use ($id) {
                    $query->where('mapel_id', $id);
                })
                ->latest()
                ->get()
                ->unique('id_grub_materi');
        }

        // Ambil tugas untuk mapel ini
        if ($kelasId) {
            $tugases = Tugas::with(['pengampuKelas.guruMapel.guru', 'nilais' => function ($q) use ($siswa) {
                if ($siswa) $q->where('siswa_id', $siswa->id);
            }])
                ->whereHas('pengampuKelas', function ($query) use ($kelasId, $id) {
                    $query->where('kelas_id', $kelasId)
                        ->whereHas('guruMapel', function ($query) use ($id) {
                            $query->where('mapel_id', $id);
                        });
                })
                ->latest('deadline')
                ->get();
        } else {
            $tugases = collect();
        }

        if ($tugases->isEmpty()) {
            $tugases = Tugas::with(['pengampuKelas.guruMapel.guru', 'nilais' => function ($q) use ($siswa) {
                if ($siswa) $q->where('siswa_id', $siswa->id);
            }])
                ->whereHas('pengampuKelas.guruMapel', function ($query) use ($id) {
                    $query->where('mapel_id', $id);
                })
                ->latest('deadline')
                ->get();
        }

        // Ambil ujian untuk mapel ini beserta nilai siswa
        if ($kelasId) {
            $ujians = Ujian::with([
                'pengampuKelas.guruMapel.guru',
                'seleksiUjians' => function ($q) use ($siswa) {
                    if ($siswa) $q->where('siswa_id', $siswa->id);
                },
                'seleksiUjians.nilais'
            ])
                ->whereHas('pengampuKelas', function ($query) use ($kelasId, $id) {
                    $query->where('kelas_id', $kelasId)
                        ->whereHas('guruMapel', function ($query) use ($id) {
                            $query->where('mapel_id', $id);
                        });
                })
                ->latest('deadline')
                ->get();
        } else {
            $ujians = collect();
        }

        if ($ujians->isEmpty()) {
            $ujians = Ujian::with([
                'pengampuKelas.guruMapel.guru',
                'seleksiUjians' => function ($q) use ($siswa) {
                    if ($siswa) $q->where('siswa_id', $siswa->id);
                },
                'seleksiUjians.nilais'
            ])
                ->whereHas('pengampuKelas.guruMapel', function ($query) use ($id) {
                    $query->where('mapel_id', $id);
                })
                ->latest('deadline')
                ->get();
        }

        $activeTab = $request->query('tab', 'materi');
        $unreadNotifCount = $user ? $user->notifikasi()->count() : 0;

        return view('siswa.materi.show', compact(
            'mapel',
            'materis',
            'tugases',
            'ujians',
            'siswa',
            'kelas',
            'tahunAjaran',
            'semester',
            'activeTab',
            'unreadNotifCount'
        ));
    }

    public function showSiswa(Request $request, $id)
    {
        return $this->show($request, $id);
    }
}

