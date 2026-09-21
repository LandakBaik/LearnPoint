<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Tampilkan daftar seluruh pengguna sistem.
     */
    public function index(Request $request)
    {
        $query = User::query()->with(['guru', 'siswa.kelas']);

        // Filter berdasarkan peran/role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Pencarian nama, username, email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(10)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Form tambah akun baru.
     */
    public function create()
    {
        // Ambil guru dan siswa yang belum memiliki akun user
        $unlinkedGurus = Guru::whereDoesntHave('user')->get();
        $unlinkedSiswas = Siswa::whereDoesntHave('user')->get();

        return view('admin.users.create', compact('unlinkedGurus', 'unlinkedSiswas'));
    }

    /**
     * Simpan akun baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'username'  => 'required|string|max:50|unique:users,username|alpha_dash',
            'email'     => 'required|string|email|max:255|unique:users,email',
            'password'  => 'required|string|min:4',
            'role'      => 'required|in:operator,guru,siswa,kepala_sekolah',
            'guru_id'   => 'nullable|exists:gurus,id',
            'siswa_id'  => 'nullable|exists:siswas,id',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        // Sesuaikan relasi jika role bukan guru atau siswa
        if ($validated['role'] !== 'guru') {
            $validated['guru_id'] = null;
        }
        if ($validated['role'] !== 'siswa') {
            $validated['siswa_id'] = null;
        }

        User::create($validated);

        return redirect()->route('users.index')->with('success', 'Akun pengguna berhasil ditambahkan!');
    }

    /**
     * Form edit akun.
     */
    public function edit(User $user)
    {
        $unlinkedGurus = Guru::whereDoesntHave('user')
            ->orWhere('id', $user->guru_id)
            ->get();

        $unlinkedSiswas = Siswa::whereDoesntHave('user')
            ->orWhere('id', $user->siswa_id)
            ->get();

        return view('admin.users.edit', compact('user', 'unlinkedGurus', 'unlinkedSiswas'));
    }

    /**
     * Update akun pengguna.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'username'  => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('users')->ignore($user->id)],
            'email'     => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password'  => 'nullable|string|min:4',
            'role'      => 'required|in:operator,guru,siswa,kepala_sekolah',
            'guru_id'   => 'nullable|exists:gurus,id',
            'siswa_id'  => 'nullable|exists:siswas,id',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        if ($validated['role'] !== 'guru') {
            $validated['guru_id'] = null;
        }
        if ($validated['role'] !== 'siswa') {
            $validated['siswa_id'] = null;
        }

        $user->update($validated);

        return redirect()->route('users.index')->with('success', 'Akun pengguna berhasil diperbarui!');
    }

    /**
     * Hapus akun pengguna.
     */
    public function destroy(User $user)
    {
        if (Auth::id() === $user->id) {
            return redirect()->route('users.index')->with('warning', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif!');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Akun pengguna berhasil dihapus!');
    }
}
