<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
     *
     * Aturan Edit Profil:
     * 1. Admin/Operator/Kepala Sekolah: Hanya bisa ganti password saja. Edit nama dan email tidak diperbolehkan.
     * 2. Guru: Nama dan email tidak diperbolehkan diubah. Selain itu (nomor telepon, jenis kelamin, password) boleh diubah.
     * 3. Siswa: Hanya bisa ganti password saja. Edit nama dan email tidak diperbolehkan.
     */
    public function update(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->isGuru()) {
            // Guru: Boleh ubah nomor telepon (10-13 digit angka), jenis kelamin, dan password (opsional)
            $rules = [
                'nomor_telepon' => 'nullable|string|regex:/^[0-9]{10,13}$/',
                'jenis_kelamin' => 'nullable|in:Laki-laki,Perempuan,L,P',
                'password'      => 'nullable|string|min:6|confirmed',
            ];

            $messages = [
                'nomor_telepon.regex' => 'Nomor telepon harus berupa angka dan berisikan 10 hingga 13 digit.',
                'jenis_kelamin.in'    => 'Pilihan jenis kelamin tidak valid.',
                'password.min'        => 'Kata sandi baru minimal harus 6 karakter.',
                'password.confirmed'  => 'Konfirmasi kata sandi baru tidak cocok.',
            ];
        } else {
            // Admin (operator / kepala_sekolah) & Siswa: Hanya bisa ubah password
            $rules = [
                'password' => 'required|string|min:6|confirmed',
            ];

            $messages = [
                'password.required'  => 'Kata sandi baru wajib diisi.',
                'password.min'       => 'Kata sandi baru minimal harus 6 karakter.',
                'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
            ];
        }

        $validated = $request->validate($rules, $messages);

        // Update password jika diisi
        if (!empty($validated['password'])) {
            $user->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        // Khusus Guru: update nomor_telepon & jenis_kelamin (nama & email TIDAK diubah)
        if ($user->isGuru() && $user->guru) {
            $guruData = [];
            if ($request->has('nomor_telepon')) {
                $guruData['nomor_telepon'] = $validated['nomor_telepon'] ?? null;
            }
            if ($request->has('jenis_kelamin')) {
                $guruData['jenis_kelamin'] = $validated['jenis_kelamin'] ?? null;
            }
            if (!empty($guruData)) {
                $user->guru->update($guruData);
            }
        }

        $successMessage = $user->isGuru()
            ? 'Profil Anda berhasil diperbarui.'
            : 'Kata sandi Anda berhasil diperbarui.';

        return redirect()->route('profile.show')->with('success', $successMessage);
    }
}
