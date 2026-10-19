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

        return view('dashboard.admin', compact('totalSiswa', 'totalGuru', 'totalKelas', 'totalMapel'));
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

        // Ambil daftar mapel beserta relasi guru, bab, dan materi
        $daftarMapel = Mapel::with(['guruMapels.guru'])->get();

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
