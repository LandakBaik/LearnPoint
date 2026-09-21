@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
<div class="space-y-6">

    <!-- Header Title -->
    <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Profil Saya</h1>
        <p class="text-sm text-gray-500 mt-1">Kelola informasi pribadi Anda.</p>
    </div>

    @if (session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <!-- Top Profile Card -->
    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-gray-200 shadow-sm flex flex-col sm:flex-row items-center sm:items-start gap-6">
        <!-- Avatar Photo & Camera Badge -->
        <div class="relative shrink-0">
            <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-full border-2 border-white shadow-md overflow-hidden bg-indigo-600 text-white font-extrabold text-2xl sm:text-3xl flex items-center justify-center">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <button type="button" class="absolute bottom-0 right-0 w-7 h-7 rounded-full bg-blue-600 text-white flex items-center justify-center shadow hover:bg-blue-700 transition-colors" title="Ubah Foto Profil">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </button>
        </div>

        <!-- Details Info -->
        <div class="space-y-1.5 text-center sm:text-left min-w-0 flex-1">
            <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900 leading-snug">{{ $user->name }}</h2>
                <span class="inline-block px-3 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 self-center sm:self-auto">
                    {{ $user->isGuru() ? 'Guru Matematika' : ($user->isSiswa() ? 'Siswa Kelas 9A' : ucfirst($user->role)) }}
                </span>
            </div>
            <div class="flex items-center justify-center sm:justify-start gap-2 text-xs text-gray-500 flex-wrap">
                <span>{{ $user->email }}</span>
                <span>&bull;</span>
                <span>NIP 198706152010012003</span>
                <span>&bull;</span>
                <span class="text-emerald-600 font-semibold flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Aktif
                </span>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="border-b border-gray-200">
        <nav class="flex items-center gap-6">
            <a href="#" class="text-blue-600 border-b-2 border-blue-600 font-bold py-3 px-1 text-sm flex items-center gap-2 -mb-px">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Informasi Pribadi
            </a>
        </nav>
    </div>

    <!-- Main Profile Form Card -->
    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-gray-200 shadow-sm space-y-6">
        <div>
            <h3 class="text-lg font-bold text-gray-900">Informasi Pribadi</h3>
            <p class="text-xs text-gray-500 mt-0.5">Informasi dasar yang digunakan pada akun LearnPoint.</p>
        </div>

        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Field 1: Nama Lengkap -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Lengkap</label>
                    <div class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-800 font-medium bg-gray-50">
                        {{ $user->name ?? '-' }}
                    </div>
                </div>

                <!-- Field 2: Email -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Email</label>
                    <div class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-800 font-medium bg-gray-50">
                        {{ $user->email ?? '-' }}
                    </div>
                </div>

                <!-- Field 3: Nomor Telepon -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Nomor Telepon</label>
                    <div class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-800 font-medium bg-gray-50">
                        {{ $user->phone ?? '0812 3456 7890' }}
                    </div>
                </div>

                <!-- Field 4: Jenis Kelamin -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Jenis Kelamin</label>
                    <div class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-800 font-medium bg-gray-50">
                        {{ $user->gender ?? 'Perempuan' }}
                    </div>
                </div>

                <!-- Field 5: Tanggal Lahir -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Tanggal Lahir</label>
                    <div class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-800 font-medium bg-gray-50">
                        {{ $user->birthdate ?? '15 Juni 1987' }}
                    </div>
                </div>

                <!-- Field 6: Alamat -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Alamat</label>
                    <div class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-800 font-medium bg-gray-50">
                        {{ $user->address ?? 'Jl. Pendidikan No. 12, Jember, Jawa Timur' }}
                    </div>
                </div>
            </div>

            <!-- Form Action Button (Go to Edit Page) -->
            <div class="flex justify-end pt-4">
                <a href="{{ route('profile.edit') }}" class="px-8 py-2.5 rounded-full bg-blue-600 text-white font-semibold text-sm hover:bg-blue-700 transition-colors shadow-sm inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </a>
            </div>
        </div>
    </div>

</div>
@endsection
