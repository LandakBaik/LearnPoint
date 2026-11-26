<?php 
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class LoginController extends Controller
{
    // Menampilkan view login
    public function showLoginForm()
    {
        $tahunAjaran = Schema::hasTable('academic_periods')
            ? AcademicPeriod::where('is_active', true)->value('tahun_ajaran')
            : null;

        return view('auth.login', [
            'tahunAjaran' => $tahunAjaran ?: '2025/2026',
        ]);
    }

    // Memproses otentikasi
    public function login(Request $request)
    {
        // Validasi input (bisa berupa username atau email)
        $credentials = $request->validate([
            'login'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Cek apakah inputan berbentuk email atau username
        $fieldType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // Attempt login (tanpa remember cookie persisten agar selaras dengan kebijakan single-session)
        if (Auth::attempt([$fieldType => $request->login, 'password' => $request->password], false)) {
            $user = Auth::user()->load(['guru', 'siswa']);

            // Periksa apakah akun user, guru, atau siswa dalam status aktif
            if ($user->status === 'nonaktif' || $user->guru?->status === 'nonaktif' || $user->siswa?->status === 'nonaktif') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'login' => 'Akun Anda saat ini berstatus NONAKTIF. Silakan hubungi pihak administrator/sekolah.',
                ])->onlyInput('login');
            }

            $request->session()->regenerate();

            // Simpan session ID aktif terbaru agar sesi di perangkat sebelumnya menjadi tidak valid
            $user->forceFill([
                'current_session_id' => $request->session()->getId(),
            ])->save();

            return redirect()->intended('/dashboard');
        }

        // Jika gagal
        return back()->withErrors([
            'login' => 'Username/Email atau kata sandi yang Anda masukkan salah.',
        ])->onlyInput('login');
    }

    // Logout
    public function logout(Request $request)
    {
        $user = Auth::user();
        $currentSessionId = $request->session()->getId();

        if ($user && $user->current_session_id === $currentSessionId) {
            $user->forceFill([
                'current_session_id' => null,
            ])->save();
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
?>