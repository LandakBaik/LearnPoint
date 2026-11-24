<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\Tugas;
use App\Models\Quiz;
use App\Models\Ujian;
use App\Models\Materi;
use App\Models\Nilai;
use App\Models\SeleksiUjian;
use App\Models\User;
use App\Services\RekapNilaiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Redirect ke dashboard sesuai role user yang login.
     */
    public function index()
    {
        $role = Auth::user()->role;

        return match ($role) {
            'operator', 'admin' => redirect()->route('admin.dashboard'),
            'guru' => redirect()->route('guru.dashboard'),
            'siswa' => redirect()->route('siswa.dashboard'),
            'kepala_sekolah' => redirect()->route('kepala_sekolah.dashboard'),
            default => redirect()->route('login'),
        };
    }

    /**
     * Halaman Dashboard Admin / Operator.
     */
    public function operatorDashboard()
    {
        $user = Auth::user();
        if (!in_array($user->role, ['operator', 'admin'])) {
            return redirect()->route('dashboard');
        }

        $totalSiswa = Siswa::count();
        $totalGuru = Guru::count();
        $totalKelas = Kelas::count();
        $totalMapel = Mapel::count();

        // Data Chart 1: Komposisi Pengguna Berdasarkan Peran (Pie Chart)
        $userRoleCounts = [
            'siswa'          => User::where('role', 'siswa')->count(),
            'guru'           => User::where('role', 'guru')->count(),
            'operator'       => User::where('role', 'operator')->count(),
            'kepala_sekolah' => User::where('role', 'kepala_sekolah')->count(),
        ];

        // Data Chart 2: Persebaran Siswa dan Kelas per Tingkatan (Bar Chart)
        $tingkatans = Kelas::select('tingkatan')
            ->distinct()
            ->orderBy('tingkatan', 'asc')
            ->pluck('tingkatan');

        $tingkatanLabels = [];
        $siswaPerTingkat = [];
        $kelasPerTingkat = [];

        foreach ($tingkatans as $tingkat) {
            $tingkatanLabels[] = 'Tingkat ' . $tingkat;
            $kelasPerTingkat[] = Kelas::where('tingkatan', $tingkat)->count();
            $siswaPerTingkat[] = Siswa::whereHas('kelas', function ($q) use ($tingkat) {
                $q->where('tingkatan', $tingkat);
            })->count();
        }

        if (empty($tingkatanLabels)) {
            $tingkatanLabels = ['Tingkat 7', 'Tingkat 8', 'Tingkat 9'];
            $kelasPerTingkat = [0, 0, 0];
            $siswaPerTingkat = [0, 0, 0];
        }

        return view('admin.dashboard', compact(
            'totalSiswa',
            'totalGuru',
            'totalKelas',
            'totalMapel',
            'userRoleCounts',
            'tingkatanLabels',
            'siswaPerTingkat',
            'kelasPerTingkat'
        ));
    }

    /**
     * Halaman Dashboard Guru.
     */
    public function guruDashboard()
    {
        $user = Auth::user();
        if ($user->role !== 'guru') {
            return redirect()->route('dashboard');
        }

        $guru = $user->guru;
        $totalSiswa = Siswa::count();
        $totalKelas = Kelas::count();
        $totalMapel = Mapel::count();

        $totalKelasDiajar = $guru ? $guru->pengampuKelases()->distinct('kelas_id')->count('kelas_id') : 0;
        if ($totalKelasDiajar === 0 && $guru && $guru->kelas) {
            $totalKelasDiajar = 1;
        }
        $totalMapelDiampu = $guru ? $guru->guruMapels()->count() : 0;
        $totalMateri = $guru ? Materi::whereHas('pengampuKelas.guruMapel', fn($q) => $q->where('guru_id', $guru->id))->distinct('id_grub_materi')->count('id_grub_materi') : 0;
        $totalTugas = $guru ? Tugas::whereHas('pengampuKelas.guruMapel', fn($q) => $q->where('guru_id', $guru->id))->count() : 0;
        $totalKuis = $guru ? Quiz::whereHas('pengampuKelas.guruMapel', fn($q) => $q->where('guru_id', $guru->id))->count() : 0;
        $totalUjian = $guru ? Ujian::whereHas('pengampuKelas.guruMapel', fn($q) => $q->where('guru_id', $guru->id))->count() : 0;

        // Data Jadwal Mengajar & Mapel Guru
        $guruMapels = $guru ? $guru->guruMapels()->with(['mapel', 'pengampuKelases.kelas'])->get() : collect();
        $jadwalUrl = $guru?->jadwal_url;

        // Data Perlu Ditindaklanjuti (Pengumpulan Tugas yang Belum Dinilai)
        $perluDinilaiTugases = collect();
        $perluDinilaiCount = 0;
        if ($guru) {
            $perluDinilaiTugases = Nilai::with(['siswa', 'tugas.pengampuKelas.kelas', 'tugas.pengampuKelas.guruMapel.mapel'])
                ->whereNull('nilai')
                ->whereNotNull('tugas_id')
                ->whereHas('tugas.pengampuKelas.guruMapel', fn($q) => $q->where('guru_id', $guru->id))
                ->latest()
                ->take(5)
                ->get();

            $perluDinilaiCount = Nilai::whereNull('nilai')
                ->whereNotNull('tugas_id')
                ->whereHas('tugas.pengampuKelas.guruMapel', fn($q) => $q->where('guru_id', $guru->id))
                ->count();
        }

        // Data Aktivitas Pembelajaran Terbaru (Materi, Tugas, Kuis yang Dibuat)
        $aktivitasTerbaru = collect();
        if ($guru) {
            $materis = Materi::whereHas('pengampuKelas.guruMapel', fn($q) => $q->where('guru_id', $guru->id))
                ->with(['pengampuKelas.guruMapel.mapel', 'pengampuKelas.kelas'])
                ->latest()
                ->get()
                ->groupBy('id_grub_materi')
                ->take(4)
                ->map(function ($group) {
                    $m = $group->first();
                    $kelasNames = $group->map(fn($item) => $item->pengampuKelas?->kelas?->nama_kelas)
                        ->filter()
                        ->unique()
                        ->sort()
                        ->implode(', ');

                    return [
                        'tipe' => 'Materi',
                        'judul' => $m->judul,
                        'kategori' => 'Modul Ajar',
                        'mapel' => $m->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? 'Mata Pelajaran',
                        'kelas' => $kelasNames !== '' ? $kelasNames : '-',
                        'waktu' => $m->created_at,
                        'status' => 'Aktif',
                        'url' => route('guru.materi'),
                    ];
                })
                ->values();

            $tugases = Tugas::whereHas('pengampuKelas.guruMapel', fn($q) => $q->where('guru_id', $guru->id))
                ->with(['pengampuKelas.guruMapel.mapel', 'pengampuKelas.kelas'])
                ->latest()
                ->take(4)
                ->get()
                ->map(fn($t) => [
                    'tipe' => 'Tugas',
                    'judul' => $t->judul,
                    'kategori' => 'Penugasan Siswa',
                    'mapel' => $t->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? 'Mata Pelajaran',
                    'kelas' => $t->pengampuKelas?->kelas?->nama_kelas ?? '-',
                    'waktu' => $t->created_at,
                    'status' => \Carbon\Carbon::parse($t->deadline)->isFuture() ? 'Berlangsung' : 'Selesai',
                    'url' => route('guru.tugas.show', $t->id),
                ]);

            $quizzes = Quiz::whereHas('pengampuKelas.guruMapel', fn($q) => $q->where('guru_id', $guru->id))
                ->with(['pengampuKelas.guruMapel.mapel', 'pengampuKelas.kelas'])
                ->latest()
                ->take(4)
                ->get()
                ->map(fn($q) => [
                    'tipe' => 'Kuis',
                    'judul' => $q->judul,
                    'kategori' => 'Evaluasi Interaktif',
                    'mapel' => $q->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? 'Mata Pelajaran',
                    'kelas' => $q->pengampuKelas?->kelas?->nama_kelas ?? '-',
                    'waktu' => $q->created_at,
                    'status' => $q->status === 'published' ? 'Aktif' : 'Draft',
                    'url' => route('guru.kuis.show', $q->id),
                ]);

            $aktivitasTerbaru = $materis->concat($tugases)->concat($quizzes)
                ->sortByDesc('waktu')
                ->take(5)
                ->values();
        }

        return view('guru.dashboard', compact(
            'user',
            'guru',
            'totalSiswa',
            'totalKelas',
            'totalMapel',
            'totalKelasDiajar',
            'totalMapelDiampu',
            'totalMateri',
            'totalTugas',
            'totalKuis',
            'totalUjian',
            'guruMapels',
            'jadwalUrl',
            'perluDinilaiTugases',
            'perluDinilaiCount',
            'aktivitasTerbaru'
        ));
    }

    /**
     * Halaman Dashboard Siswa.
     */
    public function siswaDashboard(Request $request, RekapNilaiService $rekapNilaiService)
    {
        $user = Auth::user();
        if ($user->role !== 'siswa') {
            return redirect()->route('dashboard');
        }

        if (in_array(request('tab'), ['kuiz', 'kuis'])) {
            return redirect()->route('siswa.kuis');
        }

        $siswa = $user->siswa;
        $activePeriod = \App\Models\AcademicPeriod::current();
        $anggotaKelas = $siswa?->anggotaKelases()?->with('academicPeriod')
            ?->where('academic_period_id', $activePeriod->id)
            ->first();
        $kelas = $siswa?->kelas ?? $anggotaKelas?->kelas;
        $kelasId = $kelas?->id;
        $tahunAjaran = $anggotaKelas?->academicPeriod?->tahun_ajaran ?? $activePeriod->tahun_ajaran;
        $semester = ucfirst($anggotaKelas?->academicPeriod?->semester ?? $activePeriod->semester);

        // Ambil daftar mapel beserta relasi guru khusus untuk kelas siswa
        if ($kelasId) {
            $daftarMapel = Mapel::forSiswa($siswa)->whereHas('pengampuKelases', function ($query) use ($kelasId) {
                $query->where('kelas_id', $kelasId);
            })->with(['pengampuKelases' => function ($query) use ($kelasId) {
                $query->where('kelas_id', $kelasId)->with(['guruMapel.guru', 'materis', 'tugases', 'ujians']);
            }])->get();
        } else {
            $daftarMapel = collect();
        }

        // Hitung total mapel sesuai kelas siswa
        $totalMapel = $daftarMapel->count();

        // Tugas terbaru
        $siswaId = $siswa?->id;

        $tugasTerbaru = null;

        if ($siswaId && $kelasId) {
            $tugasTerbaru = Tugas::whereHas('pengampuKelas', function ($q) use ($kelasId, $siswa) {
                $q->where('kelas_id', $kelasId)->forSiswa($siswa);
            })
                ->whereDoesntHave('nilais', function ($q) use ($siswaId) {
                    $q->where('siswa_id', $siswaId);
                })
                ->where('deadline', '>=', now())
                ->orderBy('deadline', 'asc')
                ->first();

            if (!$tugasTerbaru) {
                $tugasTerbaru = Tugas::whereHas('pengampuKelas', fn ($q) => $q->where('kelas_id', $kelasId)->forSiswa($siswa))
                    ->whereDoesntHave('nilais', function ($q) use ($siswaId) {
                        $q->where('siswa_id', $siswaId);
                    })
                    ->latest()
                    ->first();
            }
        }

        // Materi terbaru
        $materiTerbaru = $kelasId
            ? Materi::whereHas('pengampuKelas', fn($q) => $q->where('kelas_id', $kelasId)->forSiswa($siswa))
            ->with(['pengampuKelas.guruMapel.mapel'])
            ->latest()
            ->first()
            : null;

        // Hitung badge counts
        $tugasMenantiCount = $kelasId ? Tugas::whereHas('pengampuKelas', fn($q) => $q->where('kelas_id', $kelasId)->forSiswa($siswa))->where('deadline', '>=', now())->count() : 0;
        $quizCount         = $kelasId ? Quiz::whereHas('pengampuKelas', fn($q) => $q->where('kelas_id', $kelasId)->forSiswa($siswa))->where('status', 'published')->count() : 0;
        $ujianCount        = $kelasId ? Ujian::whereHas('pengampuKelas', fn($q) => $q->where('kelas_id', $kelasId)->forSiswa($siswa))->count() : 0;

        // Quizzes Dipublikasikan untuk Kelas Siswa
        $quizzes = $kelasId
            ? Quiz::whereHas('pengampuKelas', fn($q) => $q->where('kelas_id', $kelasId)->forSiswa($siswa))
            ->where('status', 'published')
            ->with([
                'pengampuKelas.guruMapel.mapel',
                'pengampuKelas.guruMapel.guru',
                'nilais' => fn($q) => $q->where('siswa_id', $siswa?->id),
            ])
            ->withCount('soals')
            ->latest()
            ->take(6)
            ->get()
            : collect();

        $unreadNotifCount = $user->notifikasi()->count() ?? 0;

        // =========================================================
        // STATISTIK SISWA (DATA RIIL & DINAMIS)
        // =========================================================
        $statistikPeriodIds = $siswa
            ? $siswa->anggotaKelases()->pluck('academic_period_id')->filter()->unique()->all()
            : [];
        if ($activePeriod?->id && !in_array($activePeriod->id, $statistikPeriodIds, true)) {
            $statistikPeriodIds[] = $activePeriod->id;
        }

        $statistikPeriods = !empty($statistikPeriodIds)
            ? \App\Models\AcademicPeriod::whereIn('id', $statistikPeriodIds)
                ->orderByDesc('tahun_ajaran')
                ->orderByRaw("CASE WHEN semester = 'genap' THEN 2 ELSE 1 END DESC")
                ->get()
            : collect([$activePeriod])->filter();

        $requestedPeriodId = $request->filled('period_id') ? (int) $request->input('period_id') : null;
        $selectedStatistikPeriod = ($requestedPeriodId ? $statistikPeriods->firstWhere('id', $requestedPeriodId) : null)
            ?? $activePeriod;

        $statistikKelasId = null;
        if ($siswa && $selectedStatistikPeriod) {
            if ($selectedStatistikPeriod->id === $activePeriod?->id) {
                $statistikKelasId = $kelasId;
            } else {
                $statistikKelasId = $siswa->anggotaKelases()
                    ->where('academic_period_id', $selectedStatistikPeriod->id)
                    ->value('kelas_id');
            }
        }

        $statistik = $rekapNilaiService->buildSiswaStatistik($siswa, $statistikKelasId, $selectedStatistikPeriod);

        $rataRataSemua     = $statistik['rata_rata_semua'];
        $rataRataTugas     = $statistik['rata_rata_tugas'];
        $rataRataQuiz      = $statistik['rata_rata_quiz'];
        $rataRataUjian     = $statistik['rata_rata_ujian'];
        $kategoriNilai     = $statistik['kategori_nilai'];
        $donutDegree       = $statistik['donut_degree'];
        $listNilaiMapel    = $statistik['list_nilai_mapel'];
        $perkembanganNilai = $statistik['perkembangan_nilai'];

        return view('siswa.dashboard', compact(
            'siswa',
            'kelas',
            'tahunAjaran',
            'semester',
            'totalMapel',
            'daftarMapel',
            'tugasTerbaru',
            'materiTerbaru',
            'tugasMenantiCount',
            'quizCount',
            'ujianCount',
            'quizzes',
            'unreadNotifCount',
            'statistik',
            'statistikPeriods',
            'selectedStatistikPeriod',
            'rataRataSemua',
            'rataRataTugas',
            'rataRataQuiz',
            'rataRataUjian',
            'kategoriNilai',
            'donutDegree',
            'listNilaiMapel',
            'perkembanganNilai'
        ));
    }

    /**
     * Halaman Dashboard Kepala Sekolah.
     */
    public function kepalaSekolahDashboard()
    {
        $totalSiswa = Siswa::count();
        $totalGuru = Guru::count();
        $totalKelas = Kelas::count();
        $totalMapel = Mapel::count();

        return view('kepala_sekolah.dashboard', compact('totalSiswa', 'totalGuru', 'totalKelas', 'totalMapel'));
    }
}
