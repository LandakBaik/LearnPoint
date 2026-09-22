<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Materi;
use App\Models\Mapel;
use App\Models\Bab;
use Illuminate\Support\Facades\Auth;

class MateriController extends Controller
{
    /**
     * Halaman Utama / Kelola Materi untuk Guru.
     */
    public function index()
    {
        $materis = Materi::with([
            'guruMapel.guru',
            'guruMapel.mapel',
            'guruMapel.kelas',
        ])->latest()->get();

        return view('guru.materi', compact('materis'));
    }

    /**
     * Halaman Daftar Mapel untuk Siswa.
     */
    public function indexSiswa()
    {
        return view('siswa.materi.index');
    }

    /**
     * Halaman Detail Bab & File Materi per Mapel untuk Siswa.
     */
    public function showSiswa($id)
{
    $mapel = Mapel::find($id);

    // Ambil data materi berdasarkan guru_mapel_id atau mapel_id
    $materis = Materi::whereHas('guruMapel', function ($query) use ($id) {
        $query->where('mapel_id', $id);
    })->get();

    return view('siswa.materi.show', compact('mapel', 'materis', 'id'));
}
}