<?php

namespace App\Http\Controllers;

use App\Models\Tugas;
use Illuminate\Http\Request;

class TugasController extends Controller
{
    /**
     * Halaman Daftar Tugas Guru.
     */
    public function index()
    {
        return view('Guru.Tugas');
    }



 /**
     * Halaman Daftar Tugas Siswa.
     */
    public function indexSiswa(Request $request)
    {
        $siswa = $request->user()?->siswa;
        $kelasId = $siswa?->kelas?->id;

        $daftarTugas = $kelasId
            ? Tugas::with([
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
            : collect();

        $daftarTugas->each(function (Tugas $tugas): void {
            $tugas->setRelation('pengumpulanSiswa', $tugas->nilais->first());
        });

        return view('siswa.tugas.index', compact('daftarTugas'));
    }
}
