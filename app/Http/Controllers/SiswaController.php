<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\Request;

class SiswaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $siswa = Siswa::with('kelas')->get();

        return view('siswa.index', compact('siswa'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kelases = Kelas::all();

        return view('siswa.create', compact('kelases'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_siswa'    => ['required', 'regex:/^[a-zA-Z\s]+$/'],
            'nis'           => 'required|unique:siswas,nis|numeric',
            'alamat'        => 'required',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'wali_murid'    => ['required', 'regex:/^[a-zA-Z\s]+$/'],
            'nohp_wali'     => 'required|numeric',
            'kelas_id'      => 'nullable|exists:kelases,id',
        ]);

        Siswa::create($validated);

        return redirect()->route('siswa.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Siswa $siswa)
    {
        return view('siswa.show', compact('siswa'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function edit(Siswa $siswa)
    {
        $kelases = Kelas::all();

        return view('siswa.edit', compact('siswa', 'kelases'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Siswa $siswa)
    {
        $validated = $request->validate([
            'nama_siswa'    => ['required', 'regex:/^[a-zA-Z\s]+$/'],
            'nis'           => 'required|numeric|unique:siswas,nis,' . $siswa->id,
            'alamat'        => 'required',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'wali_murid'    => ['required', 'regex:/^[a-zA-Z\s]+$/'],
            'nohp_wali'     => 'required|numeric',
            'kelas_id'      => 'nullable|exists:kelases,id',
        ]);

        $siswa->update($validated);

        return redirect()->route('siswa.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Siswa $siswa)
    {
        $siswa->delete();

        return redirect()->route('siswa.index');
    }
}
