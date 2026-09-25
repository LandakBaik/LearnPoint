<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Kelas;
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
        $query = Mapel::query()->withCount([
            'guruMapels' => function ($q) {
                $q->has('pengampuKelases');
            }
        ]);

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
     * Detail mata pelajaran dan daftar seluruh guru pengampunya.
     */
    public function show(Mapel $mapel)
    {
        $mapel->load(['pengampuKelases.guruMapel.guru', 'pengampuKelases.kelas']);

        $gurus = Guru::orderBy('nama', 'asc')->get();
        $kelases = Kelas::orderBy('tingkatan', 'asc')->orderBy('nama_kelas', 'asc')->get();

        return view('admin.mapel.show', compact('mapel', 'gurus', 'kelases'));
    }

    /**
     * Form edit mapel dan kelola guru pengampunya.
     */
    public function edit(Mapel $mapel)
    {
        $mapel->load(['pengampuKelases.guruMapel.guru', 'pengampuKelases.kelas']);
        $gurus = Guru::orderBy('nama', 'asc')->get();
        $kelases = Kelas::orderBy('tingkatan', 'asc')->orderBy('nama_kelas', 'asc')->get();

        return view('admin.mapel.edit', compact('mapel', 'gurus', 'kelases'));
    }

    /**
     * Update data mapel beserta penambahan guru pengampu.
     */
    public function update(Request $request, Mapel $mapel)
    {
        $validated = $request->validate([
            'nama_mapel'      => ['required', 'string', 'max:100', Rule::unique('mapels')->ignore($mapel->id)],
            'kkm'             => 'required|integer|min:0|max:100',
            'new_guru_id'     => 'nullable|exists:gurus,id',
            'new_kelas_ids'   => 'nullable|array',
            'new_kelas_ids.*' => 'exists:kelases,id',
            'new_kelas_id'    => 'nullable|exists:kelases,id',
        ]);

        $mapel->update([
            'nama_mapel' => $validated['nama_mapel'],
            'kkm'        => $validated['kkm'],
        ]);

        $newKelasIds = $request->input('new_kelas_ids', []);
        if ($request->filled('new_kelas_id') && !in_array($request->new_kelas_id, $newKelasIds)) {
            $newKelasIds[] = $request->new_kelas_id;
        }

        if (!empty($validated['new_guru_id']) && !empty($newKelasIds)) {
            $gm = \App\Models\GuruMapel::firstOrCreate([
                'guru_id'  => $validated['new_guru_id'],
                'mapel_id' => $mapel->id,
            ]);
            foreach ($newKelasIds as $kId) {
                \App\Models\PengampuKelas::firstOrCreate([
                    'guru_mapel_id' => $gm->id,
                    'kelas_id'      => $kId,
                ]);
            }
        }

        return redirect()->route('mapel.edit', $mapel->id)->with('success', 'Mata pelajaran dan guru pengampu berhasil diperbarui!');
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
