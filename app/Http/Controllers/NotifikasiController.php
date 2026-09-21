<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    /**
     * Display notifications list.
     */
    public function index()
    {
        // Data notifikasi kosong sesuai permintaan
        $notifikasis = collect([]);

        return view('notifikasi.index', compact('notifikasis'));
    }
}
