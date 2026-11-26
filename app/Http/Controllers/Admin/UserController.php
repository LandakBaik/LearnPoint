<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

        $totalUsers = User::count();
        $countOperator = User::where('role', 'operator')->count();
        $countKepalaSekolah = User::where('role', 'kepala_sekolah')->count();
        $countGuru = User::where('role', 'guru')->count();
        $countSiswa = User::where('role', 'siswa')->count();

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

        return view('admin.kelola_users.index', compact('users', 'totalUsers', 'countOperator', 'countKepalaSekolah', 'countGuru', 'countSiswa'));
    }

    /**
     * Form tambah akun baru.
     */
    public function create()
    {
        // Ambil guru dan siswa yang belum memiliki akun user
        $unlinkedGurus = Guru::whereDoesntHave('user')->get();
        $unlinkedSiswas = Siswa::whereDoesntHave('user')->get();

        return view('admin.kelola_users.create', compact('unlinkedGurus', 'unlinkedSiswas'));
    }

    /**
     * Simpan akun baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z\s\.,]+$/u'],
            'username'  => 'required|string|max:25|unique:users,username|alpha_dash',
            'email'     => ['required', 'string', 'email:rfc', 'regex:/^[^@\s]+@[^@\s]+\.[^@\s]+$/', 'max:255', 'unique:users,email'],
            'password'  => 'required|string|min:8|max:100',
            'role'      => 'required|in:operator,guru,siswa,kepala_sekolah',
            'status'    => 'nullable|in:aktif,nonaktif',
            'guru_id'   => 'nullable|exists:gurus,id|unique:users,guru_id',
            'siswa_id'  => 'nullable|exists:siswas,id|unique:users,siswa_id',
        ], [
            'name.required'      => 'Nama lengkap wajib diisi.',
            'name.max'           => 'Nama lengkap maksimal 100 karakter.',
            'name.regex'         => 'Nama lengkap hanya boleh berisi huruf, spasi, koma (,), dan titik (.).',
            'username.required'  => 'Username wajib diisi.',
            'username.max'       => 'Username maksimal 25 karakter.',
            'username.unique'    => 'Username ini sudah digunakan oleh akun lain.',
            'username.alpha_dash'=> 'Username hanya boleh berisi huruf, angka, tanda hubung (-), dan garis bawah (_).',
            'email.required'     => 'Alamat email wajib diisi.',
            'email.regex'        => 'Format email harus valid dan wajib menyertakan simbol @ serta nama domain.',
            'email.email'        => 'Format alamat email tidak valid.',
            'email.max'          => 'Alamat email maksimal 255 karakter.',
            'email.unique'       => 'Alamat email ini sudah terdaftar pada akun lain.',
            'password.required'  => 'Password wajib diisi.',
            'password.min'       => 'Password minimal 8 karakter.',
            'password.max'       => 'Password maksimal 100 karakter.',
            'role.required'      => 'Peran pengguna wajib dipilih.',
            'role.in'            => 'Peran pengguna yang dipilih tidak valid.',
            'guru_id.exists'     => 'Data guru yang dipilih tidak ditemukan.',
            'guru_id.unique'     => 'Data guru ini sudah terhubung dengan akun lain.',
            'siswa_id.exists'    => 'Data siswa yang dipilih tidak ditemukan.',
            'siswa_id.unique'    => 'Data siswa ini sudah terhubung dengan akun lain.',
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

        return view('admin.kelola_users.edit', compact('user', 'unlinkedGurus', 'unlinkedSiswas'));
    }

    /**
     * Update akun pengguna.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z\s\.,]+$/u'],
            'username'  => ['required', 'string', 'max:25', 'alpha_dash', Rule::unique('users')->ignore($user->id)],
            'email'     => ['required', 'string', 'email:rfc', 'regex:/^[^@\s]+@[^@\s]+\.[^@\s]+$/', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password'  => 'nullable|string|min:8|max:100',
            'role'      => 'required|in:operator,guru,siswa,kepala_sekolah',
            'status'    => 'nullable|in:aktif,nonaktif',
            'guru_id'   => ['nullable', 'exists:gurus,id', Rule::unique('users', 'guru_id')->ignore($user->id)],
            'siswa_id'  => ['nullable', 'exists:siswas,id', Rule::unique('users', 'siswa_id')->ignore($user->id)],
        ], [
            'name.required'      => 'Nama lengkap wajib diisi.',
            'name.max'           => 'Nama lengkap maksimal 100 karakter.',
            'name.regex'         => 'Nama lengkap hanya boleh berisi huruf, spasi, koma (,), dan titik (.).',
            'username.required'  => 'Username wajib diisi.',
            'username.max'       => 'Username maksimal 25 karakter.',
            'username.unique'    => 'Username ini sudah digunakan oleh akun lain.',
            'username.alpha_dash'=> 'Username hanya boleh berisi huruf, angka, tanda hubung (-), dan garis bawah (_).',
            'email.required'     => 'Alamat email wajib diisi.',
            'email.regex'        => 'Format email harus valid dan wajib menyertakan simbol @ serta nama domain.',
            'email.email'        => 'Format alamat email tidak valid.',
            'email.max'          => 'Alamat email maksimal 255 karakter.',
            'email.unique'       => 'Alamat email ini sudah terdaftar pada akun lain.',
            'password.required'  => 'Password wajib diisi.',
            'password.min'       => 'Password minimal 8 karakter.',
            'password.max'       => 'Password maksimal 100 karakter.',
            'role.required'      => 'Peran pengguna wajib dipilih.',
            'role.in'            => 'Peran pengguna yang dipilih tidak valid.',
            'guru_id.exists'     => 'Data guru yang dipilih tidak ditemukan.',
            'guru_id.unique'     => 'Data guru ini sudah terhubung dengan akun lain.',
            'siswa_id.exists'    => 'Data siswa yang dipilih tidak ditemukan.',
            'siswa_id.unique'    => 'Data siswa ini sudah terhubung dengan akun lain.',
        ]);

        if (Auth::id() === $user->id) {
            if (($validated['status'] ?? $user->status) === 'nonaktif') {
                return back()->withInput()->withErrors(['status' => 'Anda tidak dapat menonaktifkan akun Anda sendiri.']);
            }
            if ($validated['role'] !== $user->role) {
                return back()->withInput()->withErrors(['role' => 'Anda tidak dapat mengubah peran akun Anda sendiri.']);
            }
        }

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

        if (!empty($validated['status'])) {
            if ($user->guru) {
                $user->guru->update(['status' => $validated['status']]);
            }
            if ($user->siswa) {
                $user->siswa->update(['status' => $validated['status']]);
            }
        }

        return redirect()->route('users.index')->with('success', 'Akun pengguna berhasil diperbarui!');
    }

    /**
     * Ubah status akun pengguna menjadi aktif atau nonaktif (menggantikan fungsi hapus akun) dan otomatis sinkronkan data terikat.
     */
    public function toggleStatus(User $user)
    {
        if (Auth::id() === $user->id) {
            return redirect()->route('users.index')->with('warning', 'Anda tidak dapat menonaktifkan akun Anda sendiri yang sedang aktif!');
        }

        $user->status = ($user->status === 'aktif') ? 'nonaktif' : 'aktif';
        $user->save();

        // Otomatis nonaktifkan/aktifkan data guru atau siswa yang terikat
        if ($user->guru) {
            $user->guru->update(['status' => $user->status]);
        }
        if ($user->siswa) {
            $user->siswa->update(['status' => $user->status]);
        }

        $statusText = $user->status === 'aktif' ? 'diaktifkan kembali' : 'dinonaktifkan';
        return redirect()->route('users.index')->with('success', "Akun {$user->name} dan data terkait berhasil {$statusText}!");
    }

    /**
     * Hapus akun pengguna (diarahkan ke toggleStatus nonaktif agar data riwayat tetap aman).
     */
    public function destroy(User $user)
    {
        return $this->toggleStatus($user);
    }
}

