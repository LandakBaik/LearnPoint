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

        // Urutkan default berdasarkan prioritas role: Admin/Operator -> Kepala Sekolah -> Guru -> Siswa
        $users = $query->orderByRaw("CASE 
            WHEN role = 'operator' THEN 1 
            WHEN role = 'kepala_sekolah' THEN 2 
            WHEN role = 'guru' THEN 3 
            WHEN role = 'siswa' THEN 4 
            ELSE 5 END ASC")
            ->orderBy('name', 'asc')
            ->paginate(10)
            ->withQueryString();

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
            'email'     => ['required', 'string', 'email:rfc', 'regex:/^[^@\s]+@[^@\s]+\.[^@\s]+$/', 'max:255', 'unique:users,email'],
            'password'  => 'required|string|min:4',
            'role'      => 'required|in:operator,guru,siswa,kepala_sekolah',
            'status'    => 'nullable|in:aktif,nonaktif',
            'guru_id'   => 'nullable|exists:gurus,id',
            'siswa_id'  => 'nullable|exists:siswas,id',
        ], [
            'email.regex' => 'Format email harus valid dan wajib menyertakan simbol @ serta nama domain.',
            'email.email' => 'Format email tidak valid.',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['status'] = $validated['status'] ?? 'aktif';

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
            'email'     => ['required', 'string', 'email:rfc', 'regex:/^[^@\s]+@[^@\s]+\.[^@\s]+$/', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password'  => 'nullable|string|min:4',
            'role'      => 'required|in:operator,guru,siswa,kepala_sekolah',
            'status'    => 'nullable|in:aktif,nonaktif',
            'guru_id'   => 'nullable|exists:gurus,id',
            'siswa_id'  => 'nullable|exists:siswas,id',
        ], [
            'email.regex' => 'Format email harus valid dan wajib menyertakan simbol @ serta nama domain.',
            'email.email' => 'Format email tidak valid.',
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
     * Ubah status akun pengguna menjadi aktif atau nonaktif (menggantikan fungsi hapus akun).
     */
    public function toggleStatus(User $user)
    {
        if (Auth::id() === $user->id) {
            return redirect()->route('users.index')->with('warning', 'Anda tidak dapat menonaktifkan akun Anda sendiri yang sedang aktif!');
        }

        $user->status = ($user->status === 'aktif') ? 'nonaktif' : 'aktif';
        $user->save();

        $statusText = $user->status === 'aktif' ? 'diaktifkan kembali' : 'dinonaktifkan';
        return redirect()->route('users.index')->with('success', "Akun {$user->name} berhasil {$statusText}!");
    }

    /**
     * Hapus akun pengguna (diarahkan ke toggleStatus nonaktif agar data riwayat tetap aman).
     */
    public function destroy(User $user)
    {
        return $this->toggleStatus($user);
    }
}
