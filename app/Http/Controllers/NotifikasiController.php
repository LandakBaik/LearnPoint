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
        $user = auth()->user();
        $notifikasis = $user ? $user->notifikasi()->latest()->get() : collect([]);

        return view('notifikasi.index', compact('notifikasis'));
    }
}
