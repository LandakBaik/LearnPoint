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
     * Halaman Detail Bab & File Materi per Mapel untuk Siswa.
     */
   public function indexSiswa()
    {
        // Ambil data mapel beserta relasi guru
        $daftarMapel = Mapel::with(['guruMapels.guru'])->get();

        // Kirim variabel $daftarMapel ke view siswa.materi.index
        return view('siswa.materi.index', compact('daftarMapel'));
    }

    /**
     * Method milikmu untuk menampilkan detail materi dari mapel yang dipilih
     */
    public function showSiswa($id)
    {
        $mapel = Mapel::findOrFail($id);

        // Ambil data materi berdasarkan guru_mapel_id atau mapel_id
        $materis = Materi::whereHas('guruMapel', function ($query) use ($id) {
            $query->where('mapel_id', $id);
        })->get();

        return view('siswa.materi.show', compact('mapel', 'materis', 'id'));
    }
}