<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class JadwalController extends Controller
{
    /**
     * Halaman Jadwal Mengajar Guru (/guru/jadwal).
     */
    public function index()
    {
        $user = Auth::user();
        $guru = $user->guru;

        $guruMapels = collect();
        $jadwalUrl = null;

        if ($guru) {
            $guruMapels = $guru->guruMapels()
                ->with(['mapel', 'pengampuKelases' => fn ($q) => $q->currentPeriod()->with('kelas')])
                ->get();

            $jadwalUrl = $guru->jadwal_url;
        }

        return view('guru.jadwal.index', compact('guru', 'guruMapels', 'jadwalUrl'));
    }
}

