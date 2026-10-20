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
use App\Models\User;
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
            'operator' => redirect()->route('operator.dashboard'),
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

        return view('dashboard.admin', compact(
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
    public function siswaDashboard()
    {
        $siswa = Auth::user()->siswa;
        $kelasId = $siswa?->kelas_id;

        // Hitung total mapel
        $totalMapel = Mapel::count();

        $guruPengampus = collect();
        if ($siswa && $siswa->kelas) {
            $guruPengampus = $siswa->kelas->pengampuKelases()->with(['guruMapel.guru', 'guruMapel.mapel'])->get();
        }

        return view('dashboard.siswa', compact('user', 'siswa', 'totalMapel', 'guruPengampus'));
    }

        // Hitung badge counts
        $tugasMenantiCount = $kelasId ? Tugas::where('kelas_id', $kelasId)->where('deadline', '>=', now())->count() : 0;
        $quizCount         = $kelasId ? Quiz::where('kelas_id', $kelasId)->where('status', 'aktif')->count() : 0;
        $ujianCount        = $kelasId ? Ujian::where('kelas_id', $kelasId)->count() : 0;

        // Ambil materi
        $materiList = $kelasId ? Materi::with(['mapel', 'guru'])->where('kelas_id', $kelasId)->latest()->get() : collect();

        return view('siswa.beranda', compact(
            'siswa',
            'totalMapel',
            'daftarMapel',
            'tugasMenantiCount',
            'quizCount',
            'ujianCount',
            'materiList'
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

        return view('dashboard.kepala_sekolah', compact('totalSiswa', 'totalGuru', 'totalKelas', 'totalMapel'));
    }
}
