<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - LearnPoint SMP</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "surface-container": "#e5eeff",
                        "primary-fixed-dim": "#c0c1ff",
                        "secondary-fixed-dim": "#b4c5ff",
                        "on-secondary": "#ffffff",
                        "on-error-container": "#93000a",
                        "primary-fixed": "#e1e0ff",
                        "surface-tint": "#4242ed",
                        "error-container": "#ffdad6",
                        "surface": "#f8f9ff",
                        "on-surface": "#0b1c30",
                        "tertiary-container": "#00668b",
                        "on-tertiary": "#ffffff",
                        "on-primary-fixed-variant": "#251ed7",
                        "inverse-primary": "#c0c1ff",
                        "tertiary-fixed": "#c4e7ff",
                        "error": "#ba1a1a",
                        "surface-bright": "#f8f9ff",
                        "surface-container-high": "#dce9ff",
                        "background": "#f8f9ff",
                        "on-error": "#ffffff",
                        "outline-variant": "#e2e6f0",
                        "surface-container-lowest": "#ffffff",
                        "on-primary": "#ffffff",
                        "primary": "#261fd7",
                        "on-secondary-fixed": "#00174b",
                        "on-tertiary-fixed": "#001e2c",
                        "surface-container-highest": "#d3e4fe",
                        "on-primary-container": "#d6d6ff",
                        "primary-container": "#4343ee",
                        "tertiary-fixed-dim": "#7bd0ff",
                        "on-secondary-container": "#fefcff",
                        "on-primary-fixed": "#06006c",
                        "on-surface-variant": "#5a6578",
                        "secondary-container": "#316bf3",
                        "surface-dim": "#cbdbf5",
                        "secondary": "#0051d5",
                        "secondary-fixed": "#dbe1ff",
                        "on-background": "#0b1c30",
                        "tertiary": "#004d6a",
                        "outline": "#8a94a6",
                        "surface-container-low": "#f4f7fc",
                        "surface-variant": "#edf2fb"
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    boxShadow: {
                        xs: '0 1px 2px 0 rgba(11, 28, 48, 0.05)',
                    },
                    backdropBlur: {
                        xs: '2px',
                    }
                }
            }
        };
    </script>

    <style>
        html, body {
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        body {
            overscroll-behavior: none;
        }
        [x-cloak] {
            display: none !important;
        }
        ::-webkit-scrollbar {
            display: none;
        }
    </style>
</head>
<body
    x-data="{ showHelpModal: false }"
    @keydown.escape.window="showHelpModal = false"
    class="bg-background text-on-surface min-h-screen flex flex-col justify-between antialiased relative overflow-x-hidden selection:bg-primary/10 selection:text-primary"
>
    <!-- Animated GIF Background (Crisp & Unblurred) -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
        <div
            class="absolute inset-0 bg-cover bg-center bg-no-repeat opacity-55"
            style="background-image: url('{{ asset('images/latar-elearning-sekolah.gif') }}');"
        ></div>
        <!-- Subtle center radial highlight so the login card pops without muting perimeter doodles -->
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(248,249,255,0.45)_0%,rgba(248,249,255,0.08)_55%,rgba(248,249,255,0.18)_100%)]"></div>
    </div>

    <!-- Minimal Top Header Bar -->
    <header class="w-full relative z-10 px-5 sm:px-10 py-4 sm:py-5 flex items-center justify-between">
        <div class="flex items-center gap-2.5 bg-white/85 backdrop-blur-md pl-2 pr-3.5 py-1.5 rounded-2xl border border-primary/10 shadow-[0_4px_16px_-4px_rgba(67,67,238,0.10)]">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary to-primary-container text-white flex items-center justify-center shadow-md shadow-primary/25">
                <span class="material-symbols-outlined text-[20px]">school</span>
            </div>
            <div class="flex flex-col">
                <span class="font-bold text-[15px] tracking-tight text-on-surface leading-tight">LearnPoint</span>
                <span class="text-[10px] font-semibold uppercase tracking-wider text-primary">Portal Belajar SMP</span>
            </div>
        </div>
        <div class="flex items-center gap-2 text-[12px] font-medium text-on-surface-variant bg-white/85 backdrop-blur-md px-3.5 py-1.5 rounded-full border border-primary/10 shadow-[0_4px_16px_-4px_rgba(67,67,238,0.08)]">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span class="hidden sm:inline">Tahun Ajaran {{ $tahunAjaran ?? '2025/2026' }}</span>
            <span class="sm:hidden">{{ $tahunAjaran ?? '2025/2026' }}</span>
        </div>
    </header>

    <!-- Main Center Content -->
    <main class="relative z-10 flex-1 flex items-center justify-center px-4 sm:px-6 py-4 sm:py-8">
        <div class="w-full max-w-[430px]">
            <!-- Minimalist Card Container -->
            <div class="bg-white/95 backdrop-blur-xl rounded-2xl sm:rounded-3xl border border-primary/12 shadow-[0_24px_60px_-14px_rgba(67,67,238,0.16),0_2px_8px_rgba(11,28,48,0.04)] p-7 sm:p-9 transition-all">
                <!-- Header Text inside Card -->
                <div class="text-center mb-7">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-primary-fixed/60 text-primary mb-4 border border-primary/15 shadow-xs">
                        <span class="material-symbols-outlined text-[25px]">lock_open</span>
                    </div>
                    <h1 class="text-2xl sm:text-[26px] font-bold text-on-surface tracking-tight mb-1.5">
                        Selamat Datang
                    </h1>
                    <p class="text-[13.5px] text-on-surface-variant leading-relaxed">
                        Silakan masukkan kredensial untuk mengakses portal belajar.
                    </p>
                </div>

                @if (session('status'))
                    <div class="mb-5 flex items-start gap-2.5 rounded-xl bg-emerald-50 border border-emerald-200/80 px-3.5 py-3 text-xs text-emerald-800">
                        <span class="material-symbols-outlined text-[18px] text-emerald-600 shrink-0 mt-0.5">check_circle</span>
                        <p class="leading-relaxed">{{ session('status') }}</p>
                    </div>
                @endif

                <!-- Form Elements -->
                <form
                    method="POST"
                    action="{{ route('login') }}"
                    class="space-y-4"
                    x-data="{
                        loginValue: @js(old('login', '')),
                        rememberLogin: false,
                        showPassword: false,
                        isSubmitting: false,
                        init() {
                            try {
                                const saved = localStorage.getItem('learnpoint_saved_login');
                                if (saved) {
                                    this.rememberLogin = true;
                                    if (!this.loginValue) {
                                        this.loginValue = saved;
                                    }
                                }
                            } catch (e) {}
                        },
                        syncSavedLogin() {
                            try {
                                if (this.rememberLogin && this.loginValue.trim() !== '') {
                                    localStorage.setItem('learnpoint_saved_login', this.loginValue.trim());
                                } else {
                                    localStorage.removeItem('learnpoint_saved_login');
                                }
                            } catch (e) {}
                        },
                        handleSubmit(event) {
                            if (this.isSubmitting) {
                                event.preventDefault();
                                return;
                            }
                            this.syncSavedLogin();
                            this.isSubmitting = true;
                        }
                    }"
                    @submit="handleSubmit($event)"
                    @pageshow.window="if ($event.persisted) isSubmitting = false"
                >
                    @csrf

                    <!-- Identifier Input -->
                    <div>
                        <label class="block text-xs font-semibold text-on-surface-variant mb-1.5 uppercase tracking-wider" for="login">
                            EMAIL ATAU USERNAME
                        </label>
                        <div class="relative flex items-center group">
                            <span class="absolute left-3.5 material-symbols-outlined text-[20px] pointer-events-none transition-colors @error('login') text-error @else text-outline group-focus-within:text-primary @enderror">
                                badge
                            </span>
                            <input
                                id="login"
                                name="login"
                                type="text"
                                x-model="loginValue"
                                value="{{ old('login') }}"
                                placeholder="Masukkan Email Anda"
                                required
                                autofocus
                                autocomplete="username"
                                class="w-full h-11 pl-11 pr-4 bg-surface-container-low/70 border rounded-xl text-sm text-on-surface placeholder:text-outline transition-all duration-150 focus:bg-white outline-none @error('login') border-error/80 focus:border-error focus:ring-2 focus:ring-error/15 @else border-outline-variant/80 focus:border-primary focus:ring-2 focus:ring-primary/10 @enderror"
                            >
                        </div>
                        @error('login')
                            <p class="mt-1.5 flex items-start gap-1.5 text-xs text-error leading-relaxed">
                                <span class="material-symbols-outlined text-[15px] shrink-0 mt-0.5">error</span>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <!-- Password Input -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider" for="password">
                                Kata Sandi
                            </label>
                            <!-- <button
                                type="button"
                                @click="showHelpModal = true"
                                class="text-xs font-medium text-primary hover:text-primary-container transition-colors focus:outline-none"
                            >
                                Lupa sandi?
                            </button> -->
                        </div>
                        <div class="relative flex items-center group">
                            <span class="absolute left-3.5 material-symbols-outlined text-[20px] pointer-events-none transition-colors @error('password') text-error @else text-outline group-focus-within:text-primary @enderror">
                                lock
                            </span>
                            <input
                                id="password"
                                name="password"
                                :type="showPassword ? 'text' : 'password'"
                                placeholder="Masukkan kata sandi"
                                required
                                autocomplete="current-password"
                                class="w-full h-11 pl-11 pr-11 bg-surface-container-low/70 border rounded-xl text-sm text-on-surface placeholder:text-outline transition-all duration-150 focus:bg-white outline-none @error('password') border-error/80 focus:border-error focus:ring-2 focus:ring-error/15 @else border-outline-variant/80 focus:border-primary focus:ring-2 focus:ring-primary/10 @enderror"
                            >
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                aria-label="Tampilkan atau sembunyikan kata sandi"
                                class="absolute right-3 text-outline hover:text-on-surface transition-colors p-1 flex items-center justify-center focus:outline-none"
                            >
                                <span class="material-symbols-outlined text-[19px]" x-text="showPassword ? 'visibility_off' : 'visibility'">visibility</span>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1.5 flex items-start gap-1.5 text-xs text-error leading-relaxed">
                                <span class="material-symbols-outlined text-[15px] shrink-0 mt-0.5">error</span>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <!-- Remember Email / Username Checkbox (localStorage only, no persistent session) -->
                    <div class="flex items-center pt-1">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input
                                id="rememberLogin"
                                type="checkbox"
                                x-model="rememberLogin"
                                @change="if (!rememberLogin) syncSavedLogin()"
                                class="w-4 h-4 rounded text-primary focus:ring-primary/20 border-outline-variant/90 cursor-pointer accent-primary"
                            >
                            <span class="text-xs text-on-surface-variant font-normal">Ingat email / username di perangkat ini</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button
                        id="submitBtn"
                        type="submit"
                        :disabled="isSubmitting"
                        class="w-full h-11 mt-3 bg-gradient-to-r from-primary to-primary-container hover:from-primary-container hover:to-secondary-container active:scale-[0.99] disabled:opacity-75 disabled:cursor-not-allowed disabled:active:scale-100 text-white text-sm font-semibold rounded-xl flex items-center justify-center gap-2 shadow-[0_8px_20px_-4px_rgba(67,67,238,0.42)] transition-all duration-150"
                    >
                        <template x-if="!isSubmitting">
                            <span class="inline-flex items-center gap-2">
                                <span>Masuk</span>
                                <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                            </span>
                        </template>
                        <template x-if="isSubmitting">
                            <span class="inline-flex items-center gap-2">
                                <span class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span>
                                <span>Memverifikasi...</span>
                            </span>
                        </template>
                    </button>
                </form>

                <!-- Discreet Help Note (Strictly No Registration) -->
                <div class="mt-7 pt-5 border-t border-outline-variant/50 text-center">
                    <p class="text-xs text-on-surface-variant leading-relaxed">
                        Belum memiliki akun? <span class="text-on-surface font-medium">Hubungi Administrator Sekolah / Tata Usaha.</span>
                    </p>
                </div>
            </div>

            <!-- Quick Secondary Status Indicator -->
            <div class="flex items-center justify-center mt-5">
                <div class="inline-flex items-center gap-3.5 px-4 py-1.5 rounded-full bg-white/85 backdrop-blur-md border border-primary/10 shadow-[0_4px_16px_-4px_rgba(67,67,238,0.08)] text-[11.5px] text-on-surface-variant">
                    <span class="flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Sistem Aktif &amp; Terhubung
                    </span>
                    <span class="text-outline-variant">&bull;</span>
                    <button
                        type="button"
                        @click="showHelpModal = true"
                        class="hover:text-primary transition-colors focus:outline-none font-medium"
                    >
                        Bantuan Akses
                    </button>
                </div>
            </div>
        </div>
    </main>

    <!-- Clean Floating Bottom Footer -->
    <footer class="relative z-10 w-full px-5 sm:px-10 py-4">
        <div class="w-full flex flex-col sm:flex-row items-center justify-between gap-2 text-center sm:text-left">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/80 backdrop-blur-md border border-primary/10 shadow-[0_4px_16px_-4px_rgba(67,67,238,0.08)] text-[11.5px] text-on-surface-variant">
                <span>&copy; {{ date('Y') }} <span class="font-semibold text-on-surface">LearnPoint SMP</span></span>
                <span class="text-outline-variant">&bull;</span>
                <span>Portal E-Learning Terpadu</span>
            </div>
            <div class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-white/80 backdrop-blur-md border border-primary/10 shadow-[0_4px_16px_-4px_rgba(67,67,238,0.08)] text-[11px] font-medium text-on-surface-variant">
                <span class="material-symbols-outlined text-[14px] text-primary">verified_user</span>
                <span>Versi 1.0.0</span>
            </div>
        </div>
    </footer>

    <!-- Modal Informasi Pemulihan Akun -->
    <div
        x-cloak
        x-show="showHelpModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-on-surface/40 backdrop-blur-xs"
        role="dialog"
        aria-modal="true"
        aria-labelledby="helpModalTitle"
    >
        <div
            @click.outside="showHelpModal = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
            class="w-full max-w-sm bg-white rounded-2xl border border-outline-variant/80 shadow-[0_24px_48px_-8px_rgba(67,67,238,0.18)] p-6 text-left"
        >
            <div class="flex items-start justify-between gap-3 mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-surface-container-low text-primary border border-outline-variant/60 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[22px]">support_agent</span>
                    </div>
                    <div>
                        <h2 id="helpModalTitle" class="text-[15px] font-bold text-on-surface leading-tight">
                            Informasi Pemulihan Akun
                        </h2>
                        <p class="text-[11.5px] text-on-surface-variant">Bantuan Akses Portal</p>
                    </div>
                </div>
                <button
                    type="button"
                    @click="showHelpModal = false"
                    aria-label="Tutup modal"
                    class="text-outline hover:text-on-surface transition-colors p-1 rounded-lg hover:bg-surface-container-low"
                >
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            <p class="text-[13px] text-on-surface-variant leading-relaxed mb-5">
                Untuk menjaga keamanan data akademik, kata sandi akun SMP hanya dapat diatur ulang melalui <span class="font-semibold text-on-surface">Administrator Dapodik / Ruang Tata Usaha</span> sekolah.
            </p>

            <button
                type="button"
                @click="showHelpModal = false"
                class="w-full h-10 bg-primary hover:bg-primary-container active:scale-[0.99] text-white text-xs font-semibold rounded-xl transition-all duration-150 shadow-sm shadow-primary/25"
            >
                Mengerti
            </button>
        </div>
    </div>
</body>
</html>
