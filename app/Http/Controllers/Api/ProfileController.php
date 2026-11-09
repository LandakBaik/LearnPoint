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

        return $this->successResponse([
            'user' => [
                'id'       => $user->id,
                'name'     => $user->name,
                'username' => $user->username,
                'email'    => $user->email,
                'role'     => $user->role,
                'status'   => $user->status,
            ],
            'detail' => $user->isGuru() ? $user->guru : ($user->isSiswa() ? $user->siswa : null),
        ]);
    }

    /**
     * Perbarui data profil (Hanya diperbolehkan untuk guru memperbarui kontak/biodata; Nama dan Email tidak dapat diubah).
     */
    public function update(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        if (!$user->isGuru()) {
            return $this->errorResponse('Nama dan email tidak diperbolehkan untuk diubah. Peran Anda hanya dapat memperbarui kata sandi akun.', 403);
        }

        $rules = [
            'nomor_telepon' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s]+$/'],
            'jenis_kelamin' => ['nullable', 'in:Laki-laki,Perempuan,L,P'],
        ];

        $validator = Validator::make($request->all(), $rules, [
            'nomor_telepon.regex' => 'Nomor telepon hanya boleh berisi angka, tanda plus (+), minus (-), atau spasi.',
            'jenis_kelamin.in'    => 'Pilihan jenis kelamin harus Laki-laki atau Perempuan.',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $validated = $validator->validated();

        if ($user->guru) {
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

        return $this->successResponse([
            'user' => [
                'id'       => $user->id,
                'name'     => $user->name,
                'username' => $user->username,
                'email'    => $user->email,
                'role'     => $user->role,
                'status'   => $user->status,
            ],
            'detail' => $user->guru,
        ], 'Profil guru berhasil diperbarui.');
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
            return $this->validationErrorResponse($validator->errors());
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return $this->validationErrorResponse([
                'current_password' => ['Kata sandi saat ini salah.'],
            ], 'Kata sandi saat ini yang Anda masukkan salah.');
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return $this->successResponse(null, 'Kata sandi berhasil diperbarui.');
    }
}

