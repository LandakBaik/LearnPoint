<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Materi;
use App\Models\Quiz;
use App\Models\Siswa;
use App\Models\Tugas;
use App\Models\Ujian;
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

        // Hitung materi milik guru
        $totalMateri = Materi::whereHas('pengampuKelas.guruMapel', function ($q) use ($guruId) {
            $q->where('guru_id', $guruId);
        })->distinct('id_grub_materi')->count('id_grub_materi');

        // Hitung tugas milik guru
        $totalTugas = Tugas::whereHas('pengampuKelas.guruMapel', function ($q) use ($guruId) {
            $q->where('guru_id', $guruId);
        })->count();

        // Hitung kuis milik guru
        $totalKuis = Quiz::whereHas('pengampuKelas.guruMapel', function ($q) use ($guruId) {
            $q->where('guru_id', $guruId);
        })->count();

        // Hitung ujian milik guru
        $totalUjian = Ujian::whereHas('pengampuKelas.guruMapel', function ($q) use ($guruId) {
            $q->where('guru_id', $guruId);
        })->count();

        return response()->json([
            'success' => true,
            'data'    => [
                'user' => [
                    'id'   => $user->id,
                    'name' => $user->name,
                    'role' => $user->role,
                ],
                'guru' => $guru,
                'statistics' => [
                    'total_siswa'  => $totalSiswa,
                    'total_kelas'  => $totalKelas,
                    'total_mapel'  => $totalMapel,
                    'total_materi' => $totalMateri,
                    'total_tugas'  => $totalTugas,
                    'total_kuis'   => $totalKuis,
                    'total_ujian'  => $totalUjian,
                ],
            ],
        ]);
    }

    /**
     * Dashboard Data untuk Siswa.
     */
    public function siswaDashboard(Request $request)
    {
        $user = $request->user();
        $siswa = $user->siswa?->load('kelas');

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Profil Siswa tidak ditemukan untuk akun ini.',
            ], 404);
        }

        $totalMapel = Mapel::count();

        $guruPengampus = collect();
        if ($siswa->kelas) {
            $guruPengampus = $siswa->kelas->pengampuKelases()
                ->with(['guruMapel.guru', 'guruMapel.mapel'])
                ->get()
                ->map(function ($pk) {
                    return [
                        'pengampu_kelas_id' => $pk->id,
                        'mapel' => $pk->guruMapel?->mapel?->nama_mapel,
                        'kode_mapel' => $pk->guruMapel?->mapel?->kode_mapel,
                        'guru' => $pk->guruMapel?->guru?->nama,
                        'nip' => $pk->guruMapel?->guru?->nip,
                    ];
                });
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'user' => [
                    'id'   => $user->id,
                    'name' => $user->name,
                    'role' => $user->role,
                ],
                'siswa' => $siswa,
                'kelas' => $siswa->kelas,
                'statistics' => [
                    'total_mapel' => $totalMapel,
                ],
                'guru_pengampu' => $guruPengampus,
            ],
        ]);
    }
}
