<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Materi;
use Illuminate\Support\Facades\Auth;

class MateriController extends Controller
{
    /**
     * Halaman Materi Pembelajaran Guru.
     */
    public function index()
    {
        $materis = Materi::with([
            'pengampuKelas.guruMapel.guru',
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.kelas',
        ])->latest()->get();

        return view('guru.materi', compact('materis'));
    }
}
