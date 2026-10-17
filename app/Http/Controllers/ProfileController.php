<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Halaman Lihat Profil User (Read-only).
     */
    public function show()
    {
        /** @var User|null $user */
        $user = Auth::user();
        if ($user && $user->isGuru() && $user->guru) {
            $user->guru->load(['guruMapels.mapel', 'kelas']);
        }
        return view('profile.index', compact('user'));
    }

    /**
     * Halaman Edit Profil User.
     */
    public function edit()
    {
        /** @var User|null $user */
        $user = Auth::user();
        if ($user && $user->isGuru() && $user->guru) {
            $user->guru->load(['guruMapels.mapel', 'kelas']);
        }
        return view('profile.edit', compact('user'));
    }

    /**
     * Simpan Perubahan Profil User.
     */
    public function update(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $updateData = [
            'name'  => $validated['name'],
            'email' => $validated['email'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        // Jika guru, sinkronkan nama di tabel gurus
        if ($user->isGuru() && $user->guru) {
            $user->guru->update(['nama' => $validated['name']]);
        }

        return redirect()->route('profile.show')->with('success', 'Profil Anda berhasil diperbarui.');
    }
}
