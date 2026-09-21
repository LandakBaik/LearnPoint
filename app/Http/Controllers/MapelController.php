<?php

namespace App\Http\Controllers;

use App\Models\Mapel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MapelController extends Controller
{
    /**
     * Tampilkan daftar mata pelajaran.
     */
    public function index(Request $request)
    {
        $query = Mapel::query()->withCount('guruMapels');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('nama_mapel', 'like', "%{$search}%");
        }

        $mapels = $query->latest()->paginate(10)->withQueryString();

        return view('admin.mapel.index', compact('mapels'));
    }

    /**
     * Form tambah mapel baru.
     */
    public function create()
    {
        return view('admin.mapel.create');
    }

    /**
     * Simpan mapel baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_mapel' => 'required|string|max:100|unique:mapels,nama_mapel',
            'kkm'        => 'required|integer|min:0|max:100',
        ]);

        Mapel::create($validated);

        return redirect()->route('mapel.index')->with('success', 'Mata pelajaran berhasil ditambahkan!');
    }

    /**
     * Form edit mapel.
     */
    public function edit(Mapel $mapel)
    {
        return view('admin.mapel.edit', compact('mapel'));
    }

    /**
     * Update data mapel.
     */
    public function update(Request $request, Mapel $mapel)
    {
        $validated = $request->validate([
            'nama_mapel' => ['required', 'string', 'max:100', Rule::unique('mapels')->ignore($mapel->id)],
            'kkm'        => 'required|integer|min:0|max:100',
        ]);

        $mapel->update($validated);

        return redirect()->route('mapel.index')->with('success', 'Mata pelajaran berhasil diperbarui!');
    }

    /**
     * Hapus data mapel.
     */
    public function destroy(Mapel $mapel)
    {
        $mapel->delete();

        return redirect()->route('mapel.index')->with('success', 'Mata pelajaran berhasil dihapus!');
    }
}
