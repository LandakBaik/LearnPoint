<?php 
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    // Menampilkan view login
    public function showLoginForm()
    {
        return view('auth.login');
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

        // Attempt login
        if (Auth::attempt([$fieldType => $request->login, 'password' => $request->password], $request->boolean('remember'))) {
            // Periksa apakah akun dalam status aktif
            if (Auth::user()->status === 'nonaktif') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'login' => 'Akun Anda saat ini berstatus NONAKTIF. Silakan hubungi pihak administrator/sekolah.',
                ])->onlyInput('login');
            }

            $request->session()->regenerate();

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
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
?>