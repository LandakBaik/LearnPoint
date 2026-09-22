<?php

namespace App\Http\Controllers;

use App\Models\GuruMapel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GuruMapelController extends Controller
{
    /**
     * Simpan penugasan guru pengampu mapel pada kelas tertentu.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'guru_id'  => 'required|exists:gurus,id',
            'mapel_id' => 'required|exists:mapels,id',
            'kelas_id' => [
                'required',
                'exists:kelases,id',
                Rule::unique('guru_mapels')->where(function ($query) use ($request) {
                    return $query->where('guru_id', $request->guru_id)
                                 ->where('mapel_id', $request->mapel_id)
                                 ->where('kelas_id', $request->kelas_id);
                }),
            ],
        ], [
            'kelas_id.unique' => 'Guru ini sudah terdaftar sebagai pengampu mata pelajaran tersebut di kelas yang dipilih.',
        ]);

        GuruMapel::create([
            'guru_id'  => $validated['guru_id'],
            'mapel_id' => $validated['mapel_id'],
            'kelas_id' => $validated['kelas_id'],
        ]);

        return back()->with('success', 'Guru pengampu mata pelajaran berhasil ditugaskan!');
    }

    /**
     * Hapus penugasan guru pengampu mapel.
     */
    public function destroy(GuruMapel $guruMapel)
    {
        $guruMapel->delete();

        return back()->with('success', 'Penugasan guru pengampu mapel berhasil dihapus!');
    }
}
