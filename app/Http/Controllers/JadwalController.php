<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class JadwalController extends Controller
{
    /**
     * Halaman Jadwal Mengajar Guru.
     */
    public function index()
    {
        // Data jadwal mengajar (kosong/empty state)
        $jadwals = collect([]);

        return view('Guru.Jadwal', compact('jadwals'));
    }
}
