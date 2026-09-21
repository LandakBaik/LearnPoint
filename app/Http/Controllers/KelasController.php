<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KelasController extends Controller
{
    /**
     * Tampilkan daftar seluruh kelas.
     */
    public function index(Request $request)
    {
        $query = Kelas::query()->with(['guru', 'siswas', 'anggotaKelases.siswa']);

        if ($request->filled('tingkatan')) {
            $query->where('tingkatan', $request->tingkatan);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('nama_kelas', 'like', "%{$search}%");
        }

        $kelases = $query->latest()->paginate(10)->withQueryString();

        return view('admin.kelas.index', compact('kelases'));
    }

    /**
     * Form tambah kelas baru.
     */
    public function create()
    {
        // Guru yang belum menjadi wali kelas di kelas lain
        $gurus = Guru::whereDoesntHave('kelas')->get();

        return view('admin.kelas.create', compact('gurus'));
    }

    /**
     * Simpan kelas baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kelas' => 'required|string|max:20|unique:kelases,nama_kelas',
            'tingkatan'  => 'required|integer|min:7|max:12',
            'guru_id'    => 'nullable|exists:gurus,id|unique:kelases,guru_id',
        ]);

        Kelas::create($validated);

        return redirect()->route('kelas.index')->with('success', 'Kelas baru berhasil ditambahkan!');
    }

    /**
     * Detail kelas beserta anggota kelas dan jadwalnya.
     */
    public function show(Kelas $kela)
    {
        // Parameter binding route 'kelas' returns $kela
        $kelas = $kela;
        $kelas->load(['guru', 'siswas', 'anggotaKelases.siswa', 'guruMapels.guru', 'guruMapels.mapel']);

        return view('admin.kelas.show', compact('kelas'));
    }

    /**
     * Form edit kelas.
     */
    public function edit(Kelas $kela)
    {
        $kelas = $kela;
        $gurus = Guru::whereDoesntHave('kelas')
            ->orWhere('id', $kelas->guru_id)
            ->get();

        return view('admin.kelas.edit', compact('kelas', 'gurus'));
    }

    /**
     * Update data kelas.
     */
    public function update(Request $request, Kelas $kela)
    {
        $kelas = $kela;
        $validated = $request->validate([
            'nama_kelas' => ['required', 'string', 'max:20', Rule::unique('kelases')->ignore($kelas->id)],
            'tingkatan'  => 'required|integer|min:7|max:12',
            'guru_id'    => ['nullable', 'exists:gurus,id', Rule::unique('kelases')->ignore($kelas->id)],
        ]);

        $kelas->update($validated);

        return redirect()->route('kelas.index')->with('success', 'Data kelas berhasil diperbarui!');
    }

    /**
     * Hapus kelas.
     */
    public function destroy(Kelas $kela)
    {
        $kela->delete();

        return redirect()->route('kelas.index')->with('success', 'Data kelas berhasil dihapus!');
    }
}
