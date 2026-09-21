<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
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
        $totalUser = User::count();

        return view('dashboard.admin', compact('totalSiswa', 'totalGuru', 'totalKelas', 'totalUser'));
    }

    /**
     * Halaman Dashboard Guru.
     */
    public function guruDashboard()
    {
        $user = Auth::user();
        $guru = $user->guru;
        $totalSiswa = Siswa::count();
        $totalKelas = Kelas::count();
        $totalMapel = Mapel::count();

        return view('dashboard.guru', compact('user', 'guru', 'totalSiswa', 'totalKelas', 'totalMapel'));
    }

    /**
     * Halaman Dashboard Siswa.
     */
    public function siswaDashboard()
    {
        $user = Auth::user();
        $siswa = $user->siswa;
        $totalMapel = Mapel::count();

        return view('dashboard.siswa', compact('user', 'siswa', 'totalMapel'));
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
