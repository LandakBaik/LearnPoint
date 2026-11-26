<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Mapel;
use App\Models\Materi;
use App\Models\Tugas;
use App\Models\Ujian;
use Illuminate\Http\Request;

class MapelController extends Controller
{
    /**
     * Halaman Daftar Mata Pelajaran untuk Siswa.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $siswa = $user?->siswa;
        $activePeriod = \App\Models\AcademicPeriod::current();
        $anggotaKelas = $siswa?->anggotaKelases()?->where('academic_period_id', $activePeriod->id)->first();
        $kelas = $siswa?->kelas ?? $anggotaKelas?->kelas;
        $kelasId = $kelas?->id;

        if ($kelasId) {
            $daftarMapel = Mapel::forSiswa($siswa)->whereHas('pengampuKelases', function ($query) use ($kelasId) {
                $query->currentPeriod()->where('kelas_id', $kelasId);
            })
                ->with(['pengampuKelases' => function ($query) use ($kelasId) {
                    $query->currentPeriod()->where('kelas_id', $kelasId)
                        ->with(['guruMapel.guru', 'materis']);
                }, 'guruMapels.guru'])
                ->get();
        } else {
            $daftarMapel = collect();
        }

        return view('siswa.mapel.index', compact('daftarMapel', 'kelas', 'siswa'));
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
        $activePeriod = \App\Models\AcademicPeriod::current();
        $anggotaKelas = $siswa?->anggotaKelases()?->with('academicPeriod')
            ?->where('academic_period_id', $activePeriod->id)
            ->first();
        $kelas = $siswa?->kelas ?? $anggotaKelas?->kelas;
        $kelasId = $kelas?->id;
        $tahunAjaran = $anggotaKelas?->academicPeriod?->tahun_ajaran ?? $activePeriod->tahun_ajaran;
        $semester = ucfirst($anggotaKelas?->academicPeriod?->semester ?? $activePeriod->semester);

        if (!$kelasId) {
            abort(403, 'Anda belum ditempatkan di kelas pada periode akademik aktif. Silakan hubungi operator/admin sekolah.');
        }

        $mapel = Mapel::with(['guruMapels.guru', 'pengampuKelases.guruMapel.guru'])->findOrFail($id);

        if (!$mapel->isAccessibleBySiswa($siswa)) {
            abort(403, 'Mata pelajaran ini tidak sesuai dengan agama Anda.');
        }

        $hasMapelInKelas = $mapel->pengampuKelases()->currentPeriod()->where('kelas_id', $kelasId)->exists();
        if (!$hasMapelInKelas) {
            abort(403, 'Mata pelajaran ini tidak tersedia untuk kelas Anda.');
        }

        // Ambil guru pengampu aktif pada kelas dan periode berjalan
        $pengampuAktif = $mapel->pengampuKelases()
            ->with('guruMapel.guru')
            ->currentPeriod()
            ->where('kelas_id', $kelasId)
            ->first();

        $namaGuruPengampu = $pengampuAktif?->guruMapel?->guru?->nama ?? 'Belum ditentukan';

        // Ambil materi untuk mapel ini khusus kelas siswa pada periode aktif
        $materis = Materi::with(['pengampuKelas.guruMapel.guru', 'pengampuKelas.kelas'])
            ->whereHas('pengampuKelas', function ($query) use ($kelasId, $id) {
                $query->currentPeriod()
                    ->where('kelas_id', $kelasId)
                    ->whereHas('guruMapel', function ($query) use ($id) {
                        $query->where('mapel_id', $id);
                    });
            })
            ->latest()
            ->get();

        // Ambil tugas untuk mapel ini khusus kelas siswa pada periode aktif
        $tugases = Tugas::with(['pengampuKelas.guruMapel.guru', 'nilais' => function ($q) use ($siswa) {
            if ($siswa) $q->where('siswa_id', $siswa->id);
        }])
            ->whereHas('pengampuKelas', function ($query) use ($kelasId, $id) {
                $query->currentPeriod()
                    ->where('kelas_id', $kelasId)
                    ->whereHas('guruMapel', function ($query) use ($id) {
                        $query->where('mapel_id', $id);
                    });
            })
            ->latest('deadline')
            ->get();

        $siswaId = $siswa?->id;

        // Ambil ujian untuk mapel ini beserta nilai siswa (Hanya status scheduled, published, completed)
        $statusTampil = ['scheduled', 'published', 'completed'];

        // Filter agar ujian remidi/susulan hanya tampil untuk siswa yang terdaftar di peserta_khusus
        $filterPesertaKhusus = function ($query) use ($siswaId) {
            $query->where(function ($q) {
                $q->where('jenis_ujian', 'reguler')
                  ->orWhereNull('jenis_ujian');
            });

            if ($siswaId) {
                $query->orWhere(function ($q) use ($siswaId) {
                    $q->whereIn('jenis_ujian', ['remidi', 'susulan'])
                      ->whereJsonContains('peserta_khusus', (int) $siswaId);
                });
            }
        };

        $ujians = Ujian::with([
            'pengampuKelas.guruMapel.guru',
            'seleksiUjians' => function ($q) use ($siswa) {
                if ($siswa) $q->where('siswa_id', $siswa->id);
            },
            'seleksiUjians.nilais'
        ])
            ->whereIn('status', $statusTampil)
            ->where($filterPesertaKhusus)
            ->whereHas('pengampuKelas', function ($query) use ($kelasId, $id) {
                $query->currentPeriod()
                    ->where('kelas_id', $kelasId)
                    ->whereHas('guruMapel', function ($query) use ($id) {
                        $query->where('mapel_id', $id);
                    });
            })
            ->latest('deadline')
            ->get();

        $activeTab = $request->query('tab', 'materi');
        $unreadNotifCount = $user ? $user->unreadNotifikasiCount() : 0;

        // Statistik progress pengerjaan tugas & rata-rata nilai tugas siswa pada mapel ini
        $totalTugas = $tugases->count();
        $tugasSelesai = $tugases->filter(fn ($tugas) => $tugas->nilais->isNotEmpty())->count();
        $persenTugas = $totalTugas > 0 ? (int) round(($tugasSelesai / $totalTugas) * 100) : 0;

        $nilaiTugasDinilai = $tugases
            ->map(fn ($tugas) => $tugas->nilais->first())
            ->filter(fn ($nilai) => $nilai !== null && $nilai->nilai !== null)
            ->pluck('nilai')
            ->map(fn ($val) => (float) $val);

        $jumlahTugasDinilai = $nilaiTugasDinilai->count();
        $rataRataNilaiTugas = $jumlahTugasDinilai > 0
            ? round($nilaiTugasDinilai->avg(), 1)
            : null;

        return view('siswa.mapel.show', compact(
            'mapel',
            'materis',
            'tugases',
            'ujians',
            'siswa',
            'kelas',
            'tahunAjaran',
            'semester',
            'activeTab',
            'unreadNotifCount',
            'namaGuruPengampu',
            'totalTugas',
            'tugasSelesai',
            'persenTugas',
            'jumlahTugasDinilai',
            'rataRataNilaiTugas'
        ));
    }

    public function showSiswa(Request $request, $id)
    {
        return $this->show($request, $id);
    }
}

