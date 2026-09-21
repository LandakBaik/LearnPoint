<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class GuruController extends Controller
{
    /**
     * Tampilkan data seluruh guru.
     */
    public function index(Request $request)
    {
        $query = Guru::query()->with(['user', 'kelas', 'guruMapels.mapel', 'guruMapels.kelas']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        $gurus = $query->latest()->paginate(10)->withQueryString();

        return view('admin.guru.index', compact('gurus'));
    }

    /**
     * Form tambah guru baru.
     */
    public function create()
    {
        return view('admin.guru.create');
    }

    /**
     * Simpan data guru baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'              => 'required|string|max:100',
            'nip'               => 'required|numeric|digits_between:5,30|unique:gurus,nip',
            'buat_akun'         => 'nullable|boolean',
            'email'             => 'nullable|required_if:buat_akun,1|email|unique:users,email',
            'username'          => 'nullable|required_if:buat_akun,1|alpha_dash|unique:users,username',
            'password'          => 'nullable|required_if:buat_akun,1|min:4',
        ]);

        $guru = Guru::create([
            'nama' => $validated['nama'],
            'nip'  => $validated['nip'],
        ]);

        // Buat akun otomatis jika dicentang
        if ($request->boolean('buat_akun')) {
            User::create([
                'name'      => $validated['nama'],
                'username'  => $validated['username'],
                'email'     => $validated['email'],
                'password'  => Hash::make($validated['password']),
                'role'      => 'guru',
                'guru_id'   => $guru->id,
            ]);
        }

        return redirect()->route('guru.index')->with('success', 'Data guru berhasil ditambahkan!');
    }

    /**
     * Detail profil guru dan pengampu mapel.
     */
    public function show(Guru $guru)
    {
        $guru->load(['user', 'kelas.siswas', 'guruMapels.mapel', 'guruMapels.kelas']);

        return view('admin.guru.show', compact('guru'));
    }

    /**
     * Form edit guru.
     */
    public function edit(Guru $guru)
    {
        return view('admin.guru.edit', compact('guru'));
    }

    /**
     * Update data guru.
     */
    public function update(Request $request, Guru $guru)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'nip'  => ['required', 'numeric', 'digits_between:5,30', Rule::unique('gurus')->ignore($guru->id)],
        ]);

        $guru->update($validated);

        // Update nama user jika terhubung
        if ($guru->user) {
            $guru->user->update(['name' => $validated['nama']]);
        }

        return redirect()->route('guru.index')->with('success', 'Data guru berhasil diperbarui!');
    }

    /**
     * Hapus data guru.
     */
    public function destroy(Guru $guru)
    {
        $guru->delete();

        return redirect()->route('guru.index')->with('success', 'Data guru berhasil dihapus!');
    }
}
