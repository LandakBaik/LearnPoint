<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Halaman Lihat Profil User (Read-only).
     */
    public function show()
    {
        $user = Auth::user();
        return view('profile.index', compact('user'));
    }
}
