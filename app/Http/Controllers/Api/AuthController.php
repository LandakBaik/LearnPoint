<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
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
            return $this->validationErrorResponse($validator->errors());
        }

        $login = $request->login;
        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $user = User::with(['guru', 'siswa.kelas'])->where($fieldType, $login)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->unauthorizedResponse('Username/Email atau kata sandi yang Anda masukkan salah.');
        }

        if ($user->status === 'nonaktif' || $user->guru?->status === 'nonaktif' || $user->siswa?->status === 'nonaktif') {
            return $this->forbiddenResponse('Akun Anda saat ini berstatus NONAKTIF. Silakan hubungi pihak administrator/sekolah.');
        }

        $deviceName = $request->input('device_name', 'Mobile App');

        // Hapus token sebelumnya agar hanya satu sesi mobile yang aktif per akun
        $revokedTokensCount = $user->tokens()->delete();

        $token = $user->createToken($deviceName)->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'token_type' => 'Bearer',
            'device_name' => $deviceName,
            'previous_session_revoked' => $revokedTokensCount > 0,
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
        ], 'Login berhasil.');
    }

    /**
     * Get authenticated user profile & verify active session.
     */
    public function me(Request $request)
    {
        $user = $request->user()->load(['guru', 'siswa.kelas']);

        if ($user->status === 'nonaktif' || $user->guru?->status === 'nonaktif' || $user->siswa?->status === 'nonaktif') {
            $token = method_exists($user, 'currentAccessToken') ? $user->currentAccessToken() : null;
            if ($token && method_exists($token, 'delete')) {
                $token->delete();
            }

            return $this->forbiddenResponse('Akun Anda saat ini berstatus NONAKTIF. Silakan hubungi pihak administrator/sekolah.');
        }

        $currentToken = method_exists($user, 'currentAccessToken') ? $user->currentAccessToken() : null;
        $deviceName = $currentToken instanceof \Laravel\Sanctum\PersonalAccessToken ? $currentToken->name : null;

        return $this->successResponse([
            'authenticated' => true,
            'device_name'   => $deviceName,
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
        ]);
    }

    /**
     * Logout and revoke token.
     */
    public function logout(Request $request)
    {
        $token = $request->user()?->currentAccessToken();
        if ($token && method_exists($token, 'delete')) {
            $token->delete();
        }

        return $this->successResponse(null, 'Berhasil keluar (logout).');
    }
}
