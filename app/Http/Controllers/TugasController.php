<?php

namespace App\Http\Controllers;

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
}
