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
    public function indexSiswa()
    {
        $siswaId = auth()->user()->siswa->id ?? null;

        // Ambil semua tugas beserta relasi pengumpulan siswa yang sedang login
        $daftarTugas = Tugas::with(['guruMapel.mapel', 'pengumpulanSiswa' => function($query) use ($siswaId) {
            $query->where('siswa_id', $siswaId);
        }])->latest()->get();

        return view('siswa.tugas.index', compact('daftarTugas'));
    }
}
