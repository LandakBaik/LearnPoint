@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
<div class="space-y-6">

    <!-- Header Title -->
    <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Profil Saya</h1>
        <p class="text-sm text-gray-500 mt-1">Kelola dan lihat informasi pribadi Anda.</p>
    </div>

    @if (session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @php
        $nipGuru = $user->nip ?? $user->guru?->nip ?? ($user->isGuru() ? $user->username : null);
        $cleanNip = $nipGuru ? preg_replace('/[^0-9]/', '', $nipGuru) : '';
        $roleLabel = ucfirst($user->role);
        if ($user->isGuru()) {
            $mapelList = $user->guru ? $user->guru->guruMapels->map(fn($gm) => $gm->mapel?->nama_mapel)->filter()->unique()->values() : collect();
            $roleLabel = $mapelList->isNotEmpty() ? 'Guru ' . $mapelList->implode(', ') : 'Guru';
        } elseif ($user->isSiswa() && $user->siswa?->kelas) {
            $roleLabel = 'Siswa ' . $user->siswa->kelas->nama_kelas;
        }
    @endphp

    <!-- Top Profile Card -->
    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-gray-200 shadow-sm flex flex-col sm:flex-row items-center sm:items-start gap-6">
        <!-- Avatar Photo -->
        <div class="relative shrink-0">
            <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-full border-2 border-white shadow-md overflow-hidden bg-indigo-600 text-white font-extrabold text-2xl sm:text-3xl flex items-center justify-center">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <a href="{{ route('profile.edit') }}" class="absolute bottom-0 right-0 w-7 h-7 rounded-full bg-blue-600 text-white flex items-center justify-center shadow hover:bg-blue-700 transition-colors" title="Ubah Profil">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
            </a>
        </div>

        <!-- Details Info -->
        <div class="space-y-1.5 text-center sm:text-left min-w-0 flex-1">
            <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900 leading-snug">{{ $user->name }}</h2>
                <span class="inline-block px-3 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 self-center sm:self-auto">
                    {{ $roleLabel }}
                </span>
            </div>
            <div class="flex items-center justify-center sm:justify-start gap-2 text-xs text-gray-500 flex-wrap">
                <span>{{ $user->email }}</span>
                @if($user->isGuru() && $nipGuru)
                    <span>&bull;</span>
                    <span class="font-mono bg-blue-50 text-blue-700 font-semibold px-2 py-0.5 rounded">NIP {{ $nipGuru }}</span>
                @elseif($user->isSiswa() && $user->siswa?->nis)
                    <span>&bull;</span>
                    <span class="font-mono bg-blue-50 text-blue-700 font-semibold px-2 py-0.5 rounded">NIS {{ $user->siswa->nis }}</span>
                @endif
                <span>&bull;</span>
                <span class="text-emerald-600 font-semibold flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    {{ ucfirst($user->status ?? 'Aktif') }}
                </span>
            </div>
        </div>
    </div>

    @if($user->isGuru() && strlen($cleanNip) >= 8)
        @php
            $seg1 = substr($cleanNip, 0, 8); // Tgl lahir
            $seg2 = strlen($cleanNip) >= 14 ? substr($cleanNip, 8, 6) : ''; // Pengangkatan
            $seg3 = strlen($cleanNip) >= 15 ? substr($cleanNip, 14, 1) : ''; // Jenis kelamin (1 digit ke-3)
            $seg4 = strlen($cleanNip) >= 18 ? substr($cleanNip, 15, 3) : ''; // No urut
        @endphp
        <!-- Card Penjelasan Otomasi NIP Guru -->
        <div class="p-5 rounded-2xl bg-gradient-to-r from-indigo-50 via-blue-50 to-purple-50 border border-indigo-100 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-gray-900">Format Resmi Nomor Induk Pegawai (NIP) 18 Digit</h4>
                        <p class="text-xs text-gray-600 mt-0.5">
                            Data profil Anda terintegrasi langsung dengan format NIP:
                            <span class="font-bold text-indigo-700">8 digit pertama</span> otomatis menentukan Tanggal Lahir, dan 
                            <span class="font-bold text-blue-700">1 digit ketiga</span> menentukan Jenis Kelamin.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 font-mono text-xs self-start md:self-auto bg-white/80 backdrop-blur px-3 py-2 rounded-xl border border-indigo-100 shadow-xs">
                    <span class="px-1.5 py-0.5 rounded bg-indigo-100 text-indigo-800 font-bold" title="8 Digit Pertama: Tanggal Lahir">{{ $seg1 }}</span>
                    @if($seg2)<span class="px-1.5 py-0.5 rounded bg-gray-100 text-gray-600" title="6 Digit Kedua: Pengangkatan">{{ $seg2 }}</span>@endif
                    @if($seg3)<span class="px-1.5 py-0.5 rounded bg-blue-100 text-blue-800 font-bold" title="1 Digit Ketiga: Jenis Kelamin">{{ $seg3 }}</span>@endif
                    @if($seg4)<span class="px-1.5 py-0.5 rounded bg-gray-100 text-gray-600" title="3 Digit Keempat: No Urut">{{ $seg4 }}</span>@endif
                </div>
            </div>
        </div>
    @endif

    <!-- Tab Navigation -->
    <div class="border-b border-gray-200">
        <nav class="flex items-center gap-6">
            <span class="text-blue-600 border-b-2 border-blue-600 font-bold py-3 px-1 text-sm flex items-center gap-2 -mb-px">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Informasi Pribadi
            </span>
        </nav>
    </div>

    <!-- Main Profile Form Card -->
    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-gray-200 shadow-sm space-y-6">
        <div>
            <h3 class="text-lg font-bold text-gray-900">Informasi Pribadi</h3>
            <p class="text-xs text-gray-500 mt-0.5">Informasi akun pengguna dan biodata Anda.</p>
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

                @if($user->isGuru())
                    <!-- Field 3: NIP Guru -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Nomor Induk Pegawai (NIP)</label>
                        <div class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-800 font-mono font-medium bg-gray-50 flex items-center justify-between">
                            <span>{{ $nipGuru ?? '-' }}</span>
                            <span class="text-[10px] bg-indigo-100 text-indigo-700 font-bold px-2 py-0.5 rounded-full">18 Digit NIP</span>
                        </div>
                    </div>
                @elseif($user->isSiswa() && $user->siswa)
                    <!-- Field 3: NIS Siswa -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Nomor Induk Siswa (NIS)</label>
                        <div class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-800 font-mono font-medium bg-gray-50">
                            {{ $user->siswa->nis ?? '-' }}
                        </div>
                    </div>
                @endif

                <!-- Field: Tanggal Lahir (Diambil dari 8 digit pertama NIP untuk Guru) -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Tanggal Lahir</label>
                        @if($user->isGuru())
                            <span class="text-[10px] text-indigo-600 font-bold bg-indigo-50 px-2 py-0.5 rounded-full">8 Digit Pertama NIP</span>
                        @endif
                    </div>
                    <div class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-800 font-medium bg-gray-50 flex items-center justify-between">
                        <span>{{ $user->tanggal_lahir ?? '-' }}</span>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    @if($user->isGuru())
                        <p class="text-[11px] text-gray-500 flex items-center gap-1 mt-1">
                            <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Otomatis diekstrak dari 8 digit pertama NIP (format: YYYYMMDD).
                        </p>
                    @endif
                </div>

                <!-- Field: Jenis Kelamin (Diambil dari 1 digit ketiga NIP untuk Guru) -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Jenis Kelamin</label>
                        @if($user->isGuru())
                            <span class="text-[10px] text-indigo-600 font-bold bg-indigo-50 px-2 py-0.5 rounded-full">1 Digit Ketiga NIP</span>
                        @endif
                    </div>
                    <div class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-800 font-medium bg-gray-50 flex items-center justify-between">
                        <span>{{ $user->jenis_kelamin ?? '-' }}</span>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    @if($user->isGuru())
                        <p class="text-[11px] text-gray-500 flex items-center gap-1 mt-1">
                            <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Otomatis diekstrak dari 1 digit ketiga NIP (digit ke-15: 1 = Laki-laki, 2 = Perempuan).
                        </p>
                    @endif
                </div>

                @if($user->isGuru() && $user->guru)
                    <!-- Field: Mata Pelajaran Diampu -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Mata Pelajaran Diampu</label>
                        <div class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-800 font-medium bg-gray-50">
                            @php
                                $mapels = $user->guru->guruMapels->map(fn($gm) => $gm->mapel?->nama_mapel)->filter()->unique();
                            @endphp
                            {{ $mapels->isNotEmpty() ? $mapels->implode(', ') : 'Belum ditugaskan mapel' }}
                        </div>
                    </div>

                    <!-- Field: Status Wali Kelas -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Status Wali Kelas</label>
                        <div class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-800 font-medium bg-gray-50">
                            {{ $user->guru->kelas ? 'Wali Kelas ' . $user->guru->kelas->nama_kelas : 'Bukan Wali Kelas' }}
                        </div>
                    </div>
                @elseif($user->isSiswa() && $user->siswa)
                    <!-- Field Siswa: Kelas -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Kelas</label>
                        <div class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-800 font-medium bg-gray-50">
                            {{ $user->siswa->kelas?->nama_kelas ?? 'Belum ada kelas' }}
                        </div>
                    </div>

                    <!-- Field Siswa: Alamat -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Alamat</label>
                        <div class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-800 font-medium bg-gray-50">
                            {{ $user->siswa->alamat ?? '-' }}
                        </div>
                    </div>
                @endif
            </div>

            <!-- Form Action Button (Go to Edit Page) -->
            <div class="flex justify-end pt-4">
                <a href="{{ route('profile.edit') }}" class="px-8 py-2.5 rounded-full bg-blue-600 text-white font-semibold text-sm hover:bg-blue-700 transition-colors shadow-sm inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit Profil
                </a>
            </div>
        </div>
    </div>

</div>
@endsection
