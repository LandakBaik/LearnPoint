<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Ambil data profil lengkap pengguna yang sedang login.
     */
    public function show(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->isGuru() && $user->guru) {
            $user->guru->load(['guruMapels.mapel', 'kelas']);
        }

        if ($user->isSiswa() && $user->siswa) {
            $user->siswa->load(['anggotaKelases.kelas', 'kelas.guru']);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id'       => $user->id,
                    'name'     => $user->name,
                    'username' => $user->username,
                    'email'    => $user->email,
                    'role'     => $user->role,
                    'status'   => $user->status,
                ],
                'detail' => $user->isGuru() ? $user->guru : ($user->isSiswa() ? $user->siswa : null),
            ],
        ]);
    }

    /**
     * Perbarui data profil (Nama, Email, dan informasi spesifik role).
     */
    public function update(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $rules = [
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ];

        if ($user->isGuru()) {
            $rules['nomor_telepon'] = ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s]+$/'];
            $rules['jenis_kelamin'] = ['nullable', 'in:Laki-laki,Perempuan'];
            $rules['alamat']        = ['nullable', 'string', 'max:500'];
        } elseif ($user->isSiswa()) {
            $rules['alamat']        = ['nullable', 'string', 'max:500'];
            $rules['nohp_wali']     = ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s]+$/'];
        }

        $validator = Validator::make($request->all(), $rules, [
            'nomor_telepon.regex' => 'Nomor telepon hanya boleh berisi angka, tanda plus (+), minus (-), atau spasi.',
            'nohp_wali.regex'     => 'Nomor HP wali hanya boleh berisi angka, tanda plus (+), minus (-), atau spasi.',
            'jenis_kelamin.in'    => 'Pilihan jenis kelamin harus Laki-laki atau Perempuan.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        $user->update([
            'name'  => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($user->isGuru() && $user->guru) {
            $guruData = ['nama' => $validated['name']];
            if ($request->has('nomor_telepon')) {
                $guruData['nomor_telepon'] = $validated['nomor_telepon'] ?? null;
            }
            if ($request->has('jenis_kelamin')) {
                $guruData['jenis_kelamin'] = $validated['jenis_kelamin'] ?? null;
            }
            if ($request->has('alamat')) {
                $guruData['alamat'] = $validated['alamat'] ?? null;
            }
            $user->guru->update($guruData);
        }

        if ($user->isSiswa() && $user->siswa) {
            $siswaData = ['nama_siswa' => $validated['name']];
            if ($request->has('alamat')) {
                $siswaData['alamat'] = $validated['alamat'] ?? null;
            }
            if ($request->has('nohp_wali')) {
                $siswaData['nohp_wali'] = $validated['nohp_wali'] ?? null;
            }
            $user->siswa->update($siswaData);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui.',
            'data'    => [
                'user' => [
                    'id'       => $user->id,
                    'name'     => $user->name,
                    'username' => $user->username,
                    'email'    => $user->email,
                    'role'     => $user->role,
                    'status'   => $user->status,
                ],
                'detail' => $user->isGuru() ? $user->guru : ($user->isSiswa() ? $user->siswa : null),
            ],
        ]);
    }

    /**
     * Perbarui kata sandi akun.
     */
    public function updatePassword(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
            'password.min'       => 'Kata sandi baru minimal harus terdiri dari 6 karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Kata sandi saat ini yang Anda masukkan salah.',
                'errors'  => ['current_password' => ['Kata sandi saat ini salah.']],
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kata sandi berhasil diperbarui.',
        ]);
    }
}

