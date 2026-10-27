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
        if ($user && $user->isSiswa() && $user->siswa) {
            $user->siswa->load(['anggotaKelases.kelas']);
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
        if ($user && $user->isSiswa() && $user->siswa) {
            $user->siswa->load(['anggotaKelases.kelas']);
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

        $rules = [
            'name'     => 'required|string|max:100',
            'email'    => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6|confirmed',
        ];

        if ($user->isGuru()) {
            $rules['nomor_telepon'] = 'nullable|string|max:20|regex:/^[0-9+\-\s]+$/';
            $rules['jenis_kelamin'] = 'nullable|in:Laki-laki,Perempuan';
        }

        $validated = $request->validate($rules, [
            'nomor_telepon.regex' => 'Nomor telepon hanya boleh berisi angka, tanda plus (+), minus (-), atau spasi.',
            'jenis_kelamin.in'    => 'Pilihan jenis kelamin tidak valid.',
        ]);

        $updateData = [
            'name'  => $validated['name'],
            'email' => $validated['email'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        // Jika guru, sinkronkan nama, nomor telepon, dan jenis kelamin di tabel gurus
        if ($user->isGuru() && $user->guru) {
            $guruData = ['nama' => $validated['name']];
            if ($request->has('nomor_telepon')) {
                $guruData['nomor_telepon'] = $validated['nomor_telepon'] ?? null;
            }
            if ($request->has('jenis_kelamin')) {
                $guruData['jenis_kelamin'] = $validated['jenis_kelamin'] ?? null;
            }
            $user->guru->update($guruData);
        }

        return redirect()->route('profile.show')->with('success', 'Profil Anda berhasil diperbarui.');
    }
}
