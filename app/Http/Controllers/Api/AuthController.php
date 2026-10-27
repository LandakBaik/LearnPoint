<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Mobile login endpoint.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login'    => ['required', 'string'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $login = $request->login;
        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $user = User::with(['guru', 'siswa.kelas'])->where($fieldType, $login)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Username/Email atau kata sandi yang Anda masukkan salah.',
            ], 401);
        }

        if ($user->status === 'nonaktif') {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda saat ini berstatus NONAKTIF. Silakan hubungi pihak administrator/sekolah.',
            ], 403);
        }

        $deviceName = $request->input('device_name', 'Mobile App');
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data'    => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user'  => [
                    'id'       => $user->id,
                    'name'     => $user->name,
                    'username' => $user->username,
                    'email'    => $user->email,
                    'role'     => $user->role,
                    'status'   => $user->status,
                    'guru'     => $user->guru,
                    'siswa'    => $user->siswa,
                ],
            ],
        ]);
    }

    /**
     * Get authenticated user profile.
     */
    public function me(Request $request)
    {
        $user = $request->user()->load(['guru', 'siswa.kelas']);

        return response()->json([
            'success' => true,
            'data'    => [
                'user' => [
                    'id'       => $user->id,
                    'name'     => $user->name,
                    'username' => $user->username,
                    'email'    => $user->email,
                    'role'     => $user->role,
                    'status'   => $user->status,
                    'guru'     => $user->guru,
                    'siswa'    => $user->siswa,
                ],
            ],
        ]);
    }

    /**
     * Logout and revoke token.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil keluar (logout).',
        ]);
    }
}
