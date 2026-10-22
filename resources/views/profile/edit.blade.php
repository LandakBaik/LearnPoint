@extends(auth()->user()->isSiswa() ? 'layouts.siswa' : 'layouts.app')

@section('title', 'Edit Profil - LearnPoint')

@section('content')
@if(auth()->user()->isSiswa())
    <div class="space-y-6 max-w-4xl mx-auto">
        <!-- Back Navigation -->
        <div>
            <a href="{{ route('profile.show') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-700 transition">
                <svg class="w-4 h-4 stroke-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <span>Kembali ke Profil</span>
            </a>
        </div>

        @if (isset($errors) && $errors->any())
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm shadow-2xs">
                <div class="font-bold flex items-center gap-2">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Mohon periksa kembali form berikut:</span>
                </div>
                <ul class="list-disc list-inside mt-2 text-xs space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('profile.update') }}" method="POST" class="bg-white p-6 sm:p-10 rounded-3xl border border-slate-200/80 shadow-sm space-y-8">
            @csrf
            @method('PUT')

            <!-- Form Header -->
            <div class="flex items-start gap-4 pb-6 border-b border-slate-100">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100">
                    <i class="fa-solid fa-user-pen text-lg"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Edit Informasi Akun</h1>
                    <p class="text-sm text-slate-500 mt-1">Perbarui nama tampilan, email, atau kata sandi akun belajar Anda.</p>
                </div>
            </div>

            <!-- Profile Info Section -->
            <div class="space-y-6">
                <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-regular fa-id-badge text-blue-600"></i>
                    Informasi Dasar
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nama Lengkap -->
                    <div class="space-y-1.5">
                        <label for="name" class="block text-xs font-bold text-slate-600 uppercase tracking-wider">
                            Nama Lengkap <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input 
                                type="text" 
                                id="name" 
                                name="name" 
                                value="{{ old('name', $user->name) }}" 
                                required 
                                class="w-full px-4 py-3 rounded-2xl border @error('name') border-rose-400 bg-rose-50/20 @else border-slate-200 bg-slate-50/50 @enderror text-sm text-slate-800 font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                            >
                        </div>
                        @error('name')
                            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="space-y-1.5">
                        <label for="email" class="block text-xs font-bold text-slate-600 uppercase tracking-wider">
                            Alamat Email <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                value="{{ old('email', $user->email) }}" 
                                required 
                                class="w-full px-4 py-3 rounded-2xl border @error('email') border-rose-400 bg-rose-50/20 @else border-slate-200 bg-slate-50/50 @enderror text-sm text-slate-800 font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                            >
                        </div>
                        @error('email')
                            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- NIS (Read-only) -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">
                            Nomor Induk Siswa (NIS)
                        </label>
                        <div class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-100 text-sm text-slate-500 font-mono flex items-center justify-between cursor-not-allowed select-none">
                            <span>{{ $user->siswa?->nis ?? '-' }}</span>
                            <span class="text-[11px] font-sans text-slate-400 bg-white px-2 py-0.5 rounded-lg border border-slate-200">Hanya dapat diubah operator</span>
                        </div>
                    </div>

                    <!-- Username (Read-only) -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">
                            Username
                        </label>
                        <div class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-100 text-sm text-slate-500 font-mono flex items-center justify-between cursor-not-allowed select-none">
                            <span>{{ $user->username }}</span>
                            <span class="text-[11px] font-sans text-slate-400 bg-white px-2 py-0.5 rounded-lg border border-slate-200">Permanen</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Password Change Section -->
            <div class="space-y-6 pt-6 border-t border-slate-100">
                <div>
                    <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-lock text-amber-500"></i>
                        Ubah Kata Sandi
                    </h2>
                    <p class="text-xs text-slate-400 mt-1">Kosongkan jika Anda tidak ingin mengganti kata sandi.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Password Baru -->
                    <div class="space-y-1.5">
                        <label for="password" class="block text-xs font-bold text-slate-600 uppercase tracking-wider">
                            Kata Sandi Baru
                        </label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Minimal 6 karakter"
                            class="w-full px-4 py-3 rounded-2xl border @error('password') border-rose-400 bg-rose-50/20 @else border-slate-200 bg-slate-50/50 @enderror text-sm text-slate-800 font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                        >
                        @error('password')
                            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Konfirmasi Password -->
                    <div class="space-y-1.5">
                        <label for="password_confirmation" class="block text-xs font-bold text-slate-600 uppercase tracking-wider">
                            Konfirmasi Kata Sandi Baru
                        </label>
                        <input 
                            type="password" 
                            id="password_confirmation" 
                            name="password_confirmation" 
                            placeholder="Ketik ulang kata sandi baru"
                            class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/50 text-sm text-slate-800 font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                        >
                    </div>
                </div>
            </div>

            <!-- Form Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                <a href="{{ route('profile.show') }}" class="px-6 py-3 rounded-2xl border border-slate-200 text-slate-600 font-semibold text-sm hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="px-8 py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm transition shadow-md shadow-blue-500/20 inline-flex items-center gap-2">
                    <i class="fa-solid fa-check text-xs"></i>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
@else
    {{-- Non-Siswa Edit Profile (Guru / Admin / Operator) --}}
    <div class="space-y-6 max-w-4xl mx-auto">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Edit Profil</h1>
                <p class="text-sm text-gray-500 mt-1">Perbarui informasi akun Anda.</p>
            </div>
            <a href="{{ route('profile.show') }}" class="px-4 py-2 rounded-xl border border-gray-300 text-gray-700 font-semibold text-sm hover:bg-gray-50 transition-colors inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali
            </a>
        </div>

        @if (isset($errors) && $errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
                <div class="font-bold flex items-center gap-2">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Mohon periksa kembali form berikut:</span>
                </div>
                <ul class="list-disc list-inside mt-2 text-xs space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('profile.update') }}" method="POST" class="bg-white p-6 sm:p-8 rounded-2xl border border-gray-200 shadow-sm space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-1.5">
                    <label for="name" class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="{{ old('name', $user->name) }}" 
                        required 
                        class="w-full px-4 py-2.5 rounded-xl border @error('name') border-rose-400 bg-rose-50/30 @else border-gray-300 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                    @error('name')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1.5">
                    <label for="email" class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Email <span class="text-rose-500">*</span></label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="{{ old('email', $user->email) }}" 
                        required 
                        class="w-full px-4 py-2.5 rounded-xl border @error('email') border-rose-400 bg-rose-50/30 @else border-gray-300 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                    @error('email')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                @if($user->isGuru())
                    <div class="space-y-1.5">
                        <label for="nomor_telepon" class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Nomor Telepon</label>
                        <input 
                            type="text" 
                            id="nomor_telepon" 
                            name="nomor_telepon" 
                            value="{{ old('nomor_telepon', $user->guru?->nomor_telepon) }}" 
                            placeholder="Contoh: 081234567890" 
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                    </div>

                    <div class="space-y-1.5">
                        <label for="jenis_kelamin" class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Jenis Kelamin</label>
                        <select 
                            id="jenis_kelamin" 
                            name="jenis_kelamin" 
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">-- Pilih Jenis Kelamin --</option>
                            <option value="Laki-laki" {{ old('jenis_kelamin', $user->guru?->jenis_kelamin) == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="Perempuan" {{ old('jenis_kelamin', $user->guru?->jenis_kelamin) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                @endif

                <div class="space-y-1.5">
                    <label for="password" class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Password Baru (Opsional)</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Minimal 6 karakter"
                        class="w-full px-4 py-2.5 rounded-xl border @error('password') border-rose-400 bg-rose-50/30 @else border-gray-300 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                    @error('password')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1.5">
                    <label for="password_confirmation" class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Konfirmasi Password Baru</label>
                    <input 
                        type="password" 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        placeholder="Ulangi password baru"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('profile.show') }}" class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 font-semibold text-sm hover:bg-gray-50 transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 text-white font-semibold text-sm hover:bg-blue-700 transition-colors shadow-sm">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
@endif
@endsection
