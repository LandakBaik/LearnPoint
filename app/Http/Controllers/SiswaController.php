<?php

namespace App\Http\Controllers;

use App\Models\AnggotaKelas;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SiswaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Siswa::with(['kelas.guru', 'user', 'anggotaKelases']);

        // Filter kelas
        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_siswa', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('wali_murid', 'like', "%{$search}%");
            });
        }

        $siswa = $query->latest()->paginate(10)->withQueryString();
        $kelases = Kelas::all();

        return view('siswa.index', compact('siswa', 'kelases'));
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
            'nama_siswa'    => ['required', 'regex:/^[a-zA-Z\s\.\,\'\-]+$/'],
            'nis'           => 'required|unique:siswas,nis|numeric',
            'alamat'        => 'required|string',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'wali_murid'    => ['required', 'regex:/^[a-zA-Z\s\.\,\'\-]+$/'],
            'nohp_wali'     => 'required|numeric',
            'kelas_id'      => 'nullable|exists:kelases,id',
            'buat_akun'     => 'nullable|boolean',
            'email'         => 'nullable|required_if:buat_akun,1|email|unique:users,email',
            'username'      => 'nullable|required_if:buat_akun,1|alpha_dash|unique:users,username',
            'password'      => 'nullable|required_if:buat_akun,1|min:4',
        ]);

        $siswa = Siswa::create([
            'nama_siswa'    => $validated['nama_siswa'],
            'nis'           => $validated['nis'],
            'alamat'        => $validated['alamat'],
            'tanggal_lahir' => $validated['tanggal_lahir'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'wali_murid'    => $validated['wali_murid'],
            'nohp_wali'     => $validated['nohp_wali'],
            'kelas_id'      => $validated['kelas_id'] ?? null,
        ]);

        // Jika kelas dipilih, daftarkan otomatis ke anggota_kelases
        if (!empty($validated['kelas_id'])) {
            // Cek apakah kelas sudah memiliki foto jadwal sebelumnya
            $jadwalFoto = AnggotaKelas::where('kelas_id', $validated['kelas_id'])
                ->whereNotNull('jadwal')
                ->latest()
                ->value('jadwal');

            AnggotaKelas::firstOrCreate(
                [
                    'kelas_id'     => $validated['kelas_id'],
                    'siswa_id'     => $siswa->id,
                    'tahun_ajaran' => '2026/2027',
                    'semester'     => 'ganjil',
                ],
                [
                    'jadwal'       => $jadwalFoto,
                ]
            );
        }

        // Buat akun otomatis jika dicentang
        if ($request->boolean('buat_akun')) {
            User::create([
                'name'      => $validated['nama_siswa'],
                'username'  => $validated['username'],
                'email'     => $validated['email'],
                'password'  => Hash::make($validated['password']),
                'role'      => 'siswa',
                'siswa_id'  => $siswa->id,
            ]);
        }

        return redirect()->route('siswa.index')->with('success', 'Data siswa berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Siswa $siswa)
    {
        $siswa->load(['kelas.guru', 'user', 'anggotaKelases.kelas']);

        return view('siswa.show', compact('siswa'));
    }

    /**
     * Show the form for editing the specified resource.
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
            'nama_siswa'    => ['required', 'regex:/^[a-zA-Z\s\.\,\'\-]+$/'],
            'nis'           => ['required', 'numeric', Rule::unique('siswas')->ignore($siswa->id)],
            'alamat'        => 'required|string',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'wali_murid'    => ['required', 'regex:/^[a-zA-Z\s\.\,\'\-]+$/'],
            'nohp_wali'     => 'required|numeric',
            'kelas_id'      => 'nullable|exists:kelases,id',
        ]);

        $siswa->update($validated);

        // Sinkronisasi ke anggota_kelases
        if (!empty($validated['kelas_id'])) {
            $jadwalFoto = AnggotaKelas::where('kelas_id', $validated['kelas_id'])
                ->whereNotNull('jadwal')
                ->latest()
                ->value('jadwal');

            AnggotaKelas::updateOrCreate(
                [
                    'siswa_id'     => $siswa->id,
                    'tahun_ajaran' => '2026/2027',
                    'semester'     => 'ganjil',
                ],
                [
                    'kelas_id'     => $validated['kelas_id'],
                    'jadwal'       => $jadwalFoto,
                ]
            );
        }

        if ($siswa->user) {
            $siswa->user->update(['name' => $validated['nama_siswa']]);
        }

        return redirect()->route('siswa.index')->with('success', 'Data siswa berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Siswa $siswa)
    {
        $siswa->delete();

        return redirect()->route('siswa.index')->with('success', 'Data siswa berhasil dihapus!');
    }
}
