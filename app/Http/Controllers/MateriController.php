<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Materi;
use App\Models\Mapel;
use App\Models\Bab;
use App\Models\Siswa;
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
     * Halaman Beranda / Dashboard Siswa.
     */
    public function indexSiswa()
    {
        $daftarMapel = Mapel::with(['guruMapels.guru'])->get();
        $totalMateri = Materi::count();
        $materiSelesai = 0;
        $progressPersen = $totalMateri > 0 ? round(($materiSelesai / $totalMateri) * 100) : 0;

        return view('siswa.beranda', compact('daftarMapel', 'progressPersen'));
    }

    /**
     * Menampilkan detail materi dari mapel yang dipilih
     */
    public function showSiswa($id)
    {
        $mapel = Mapel::findOrFail($id);
        $siswa = Auth::user()->siswa ?? Siswa::where('user_id', Auth::id())->first();
        $materis = Materi::whereHas('pengampuKelas.guruMapel', function ($query) use ($id) {
            $query->where('mapel_id', $id);
        })->with('pengampuKelas.guruMapel.guru')->get();

        return view('siswa.materi.show', compact('mapel', 'materis', 'id', 'siswa'));
    }
}
