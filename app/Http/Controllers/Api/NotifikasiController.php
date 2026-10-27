<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    /**
     * Menampilkan daftar notifikasi pengguna.
     */
    public function index(Request $request)
    {
        $notifikasis = $request->user()->notifikasi ?? collect([]);

        return response()->json([
            'success' => true,
            'data' => $notifikasis,
        ]);
    }
}
