<!-- resources/views/auth/login.blade.php -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LearnPoint</title>
    
    <!-- Tailwind CSS (CDN / Vite) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js untuk fitur toggle password -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Font Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            /* Ganti 'bg-doodle.png' dengan path gambar pola latar belakang Anda */
            background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('{{ asset("images/background-login2.jpeg") }}');
            background-repeat: repeat;
            background-size: 400px;
        }
    </style>
</head>
<body class="bg-gray-300 min-h-screen flex items-center justify-center p-4">

    <!-- Card Login Container -->
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-8 sm:p-10 text-center relative z-10">
        
        <!-- Logo & Header -->
        <div class="flex items-center justify-center gap-3 mb-6">
            <!-- Icon Logo Buku -->
            <div class="bg-indigo-600 text-white p-3 rounded-2xl shadow-md flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
            <div class="text-left">
                <h1 class="text-2xl font-extrabold text-indigo-950 tracking-tight leading-tight">LearnPoint</h1>
                <p class="text-xs font-semibold text-indigo-500 tracking-wider">E-LEARNING SMP</p>
            </div>
        </div>

        <!-- Judul & Subtitle -->
        <h2 class="text-2xl font-bold text-gray-900 mb-1">Selamat Datang</h2>
        <p class="text-gray-400 text-xs sm:text-sm mb-6">Silakan masuk ke akun Anda untuk melanjutkan</p>

        <!-- Form Login -->
        <form 
            method="POST" 
            action="{{ route('login') }}" 
            x-data="{ showPassword: false, isSubmitting: false }"
            @submit="if (isSubmitting) { $event.preventDefault(); return; } isSubmitting = true;"
            @pageshow.window="if ($event.persisted) isSubmitting = false"
        >
            @csrf

            <!-- Username / Email -->
            <div class="text-left mb-4">
                <label for="login" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                    USERNAME / EMAIL
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400">
                        <!-- Icon User -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </span>
                    <input 
                        type="text" 
                        id="login" 
                        name="login" 
                        value="{{ old('login') }}"
                        placeholder="Masukkan username atau email" 
                        required 
                        autofocus
                        class="w-full pl-11 pr-4 py-3 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 placeholder-gray-300"
                    >
                </div>
                @error('login')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Kata Sandi -->
            <div class="text-left mb-4">
                <label for="password" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                    KATA SANDI
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400">
                        <!-- Icon Lock -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </span>
                    <input 
                        :type="showPassword ? 'text' : 'password'" 
                        id="password" 
                        name="password" 
                        placeholder="••••••••••••" 
                        required 
                        class="w-full pl-11 pr-11 py-3 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 placeholder-gray-300"
                    >
                    <!-- Toggle Show/Hide Password -->
                    <button 
                        type="button" 
                        @click="showPassword = !showPassword" 
                        class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-gray-400 hover:text-gray-600 focus:outline-none"
                    >
                        <!-- Icon Eye -->
                        <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <!-- Icon Eye Off -->
                        <svg x-show="showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.033 10.033 0 013.682-.863c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21M3 3l18 18" />
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Checkbox & Lupa Password -->
            {{-- <div class="flex items-center justify-between text-xs sm:text-sm mb-6">
                <label class="flex items-center text-gray-500 cursor-pointer">
                    <input 
                        type="checkbox" 
                        @click="showPassword = !showPassword"
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 w-4 h-4"
                    >
                    <span class="ml-2 text-gray-600">Tampilkan Password</span>
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-indigo-600 hover:text-indigo-800 font-medium hover:underline">
                        Lupa Password?
                    </a>
                @endif
            </div> --}}

            <!-- Tombol Masuk -->
            <button 
                type="submit" 
                :disabled="isSubmitting"
                class="w-full bg-indigo-600 hover:bg-indigo-700 active:scale-[0.98] disabled:bg-indigo-400 disabled:hover:bg-indigo-400 disabled:active:scale-100 disabled:cursor-not-allowed disabled:opacity-80 text-white font-medium py-3 rounded-xl shadow-lg shadow-indigo-200 transition duration-200 flex items-center justify-center gap-2"
            >
                <svg x-show="isSubmitting" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="display: none;">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span x-text="isSubmitting ? 'Memproses...' : 'Masuk'">Masuk</span>
            </button>
        </form>

        <!-- Footer Copyright -->
        <p class="text-xs text-gray-400 mt-8">
            &copy; {{ date('Y') }} Hak Cipta Dilindungi. Sistem Informasi Aman.
        </p>

    </div>

</body>
</html>