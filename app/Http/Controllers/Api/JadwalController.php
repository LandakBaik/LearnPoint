<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    /**
     * GURU: Jadwal mengajar guru.
     */
    public function guruIndex(Request $request)
    {
        $user = $request->user();
        $guru = $user->guru;

        if (!$guru) {
            return $this->notFoundResponse('Profil guru tidak ditemukan.');
        }

        $guruMapels = $guru->guruMapels()
            ->has('pengampuKelases')
            ->with(['mapel', 'pengampuKelases.kelas'])
            ->get()
            ->map(function ($gm) {
                return [
                    'guru_mapel_id' => $gm->id,
                    'mapel' => $gm->mapel?->nama_mapel,
                    'kelases' => $gm->pengampuKelases->map(function ($pk) {
                        return [
                            'kelas_id' => $pk->kelas?->id,
                            'nama_kelas' => $pk->kelas?->nama_kelas,
                            'tingkatan' => $pk->kelas?->tingkatan,
                        ];
                    }),
                ];
            });

        return $this->successResponse([
            'jadwal_url' => $guru->jadwal_url,
            'jadwal_file' => $guru->jadwal,
            'mengajar' => $guruMapels,
        ]);
    }

    /**
     * SISWA: Jadwal kelas siswa.
     */
    public function siswaIndex(Request $request)
    {
        $user = $request->user();
        $siswa = $user->siswa;

        if (!$siswa) {
            return $this->notFoundResponse('Profil siswa tidak ditemukan.');
        }

        $anggotaKelas = $siswa->anggotaKelases()?->latest()?->first();
        $kelas = $siswa->kelas ?? $anggotaKelas?->kelas;

        if (!$kelas) {
            return $this->notFoundResponse('Data kelas siswa tidak ditemukan.');
        }

        $pengampuKelases = $kelas->pengampuKelases()
            ->with(['guruMapel.guru', 'guruMapel.mapel'])
            ->get()
            ->map(function ($pk) {
                return [
                    'mapel' => $pk->guruMapel?->mapel?->nama_mapel,
                    'guru' => $pk->guruMapel?->guru?->nama,
                    'nuptk' => $pk->guruMapel?->guru?->nuptk,
                ];
            });

        return $this->successResponse([
            'kelas' => [
                'id' => $kelas->id,
                'nama_kelas' => $kelas->nama_kelas,
                'tingkatan' => $kelas->tingkatan,
                'wali_kelas' => $kelas->guru?->nama,
            ],
            'jadwal_url' => $kelas->jadwal_url,
            'jadwal_file' => $kelas->jadwal,
            'mata_pelajaran' => $pengampuKelases,
        ]);
    }
}
