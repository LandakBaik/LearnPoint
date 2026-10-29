@extends(auth()->user()->isSiswa() ? 'layouts.siswa' : 'layouts.app')

@section('title', 'Profil Saya - LearnPoint')

@section('content')
@if(auth()->user()->isSiswa())
    @php
        $siswa = $user->siswa;
        $anggotaKelas = $siswa?->anggotaKelases()?->latest()?->first();
        $kelas = $siswa?->kelas ?? $anggotaKelas?->kelas;
        $tahunAjaran = $anggotaKelas?->tahun_ajaran ?? '2024/2025';
        $semester = $anggotaKelas?->semester ? ucfirst($anggotaKelas->semester) : 'Ganjil';
        $namaSiswa = $siswa?->nama_siswa ?? $user->name;
        $initials = strtoupper(substr($namaSiswa, 0, 2));
    @endphp

    <div class="space-y-6">
        <!-- Back Navigation -->
        <div>
            <a href="{{ route('siswa.dashboard') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-700 transition">
                <svg class="w-4 h-4 stroke-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <span>Kembali ke Beranda</span>
            </a>
        </div>

        @if (session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium flex items-center justify-between shadow-2xs">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        <!-- BEGIN: ProfileHeroBanner -->
        <section class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#EBF4FF] via-[#EDF6FF] to-[#DCEEFE] p-7 md:p-9 border border-blue-100 shadow-sm">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
                    <!-- Avatar Photo / Initials -->
                    <div class="relative shrink-0">
                        <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-3xl bg-gradient-to-br from-[#2563EB] to-[#1D4ED8] text-white font-extrabold text-3xl sm:text-4xl flex items-center justify-center shadow-lg shadow-blue-500/25 border-4 border-white">
                            {{ $initials }}
                        </div>
                        <a href="{{ route('profile.edit') }}" class="absolute -bottom-1 -right-1 w-8 h-8 rounded-xl bg-white text-blue-600 flex items-center justify-center shadow-md border border-blue-100 hover:bg-blue-50 transition" title="Edit Profil">
                            <i class="fa-solid fa-pen text-xs"></i>
                        </a>
                    </div>

                    <!-- Meta & Name -->
                    <div class="space-y-2 text-center sm:text-left">
                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100/80 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Siswa Aktif
                            </span>
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-100/80 text-blue-700 border border-blue-200">
                                Kelas {{ $kelas->nama_kelas ?? 'Belum Ditentukan' }}
                            </span>
                        </div>

                        <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F2851] tracking-tight">
                            {{ $namaSiswa }}
                        </h1>

                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-y-1 gap-x-3 text-xs sm:text-sm text-slate-500 font-medium">
                            <span class="flex items-center gap-1.5">
                                <i class="fa-regular fa-envelope text-blue-500"></i>
                                {{ $user->email }}
                            </span>
                            <span class="text-slate-300">|</span>
                            <span class="flex items-center gap-1.5">
                                <i class="fa-solid fa-id-card text-blue-500"></i>
                                NIS: <strong>{{ $siswa->nis ?? '-' }}</strong>
                            </span>
                            <span class="text-slate-300">|</span>
                            <span class="flex items-center gap-1.5">
                                <i class="fa-regular fa-calendar-check text-blue-500"></i>
                                T.A. {{ $tahunAjaran }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Right Action Button -->
                <div class="flex items-center justify-center md:justify-end shrink-0 z-10">
                    <a href="{{ route('profile.edit') }}" class="px-5 py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm transition shadow-md shadow-blue-500/20 inline-flex items-center gap-2">
                        <i class="fa-solid fa-user-pen text-xs"></i>
                        <span>Edit Informasi Akun</span>
                    </a>
                </div>
            </div>

            <!-- Background Aesthetic SVG decoration -->
            <div class="absolute right-4 -bottom-4 hidden lg:block opacity-40 pointer-events-none select-none">
                <svg class="w-48 h-48 text-blue-300" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 3L1 9L12 15L21 10.09V17H23V9M5 13.18V17.18C5 19.94 8.13 22 12 22C15.87 22 19 19.94 19 17.18V13.18L12 17L5 13.18Z"></path>
                </svg>
            </div>
        </section>
        <!-- END: ProfileHeroBanner -->

        <!-- Details Grid Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Card 1: Biodata Pribadi -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-5">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-user text-base"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-800">Biodata Pribadi</h2>
                        <p class="text-xs text-slate-400">Data identitas diri siswa.</p>
                    </div>
                </div>

                <div class="space-y-4 text-sm">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3.5 rounded-2xl bg-slate-50/70 border border-slate-100">
                        <span class="text-xs font-semibold text-slate-400">Nama Lengkap</span>
                        <span class="font-bold text-slate-800 mt-1 sm:mt-0">{{ $namaSiswa }}</span>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3.5 rounded-2xl bg-slate-50/70 border border-slate-100">
                        <span class="text-xs font-semibold text-slate-400">Nomor Induk Siswa (NIS)</span>
                        <span class="font-mono font-bold text-blue-600 mt-1 sm:mt-0">{{ $siswa->nis ?? '-' }}</span>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3.5 rounded-2xl bg-slate-50/70 border border-slate-100">
                        <span class="text-xs font-semibold text-slate-400">Jenis Kelamin</span>
                        <span class="font-semibold text-slate-800 mt-1 sm:mt-0">
                            {{ $siswa?->jenis_kelamin === 'L' ? 'Laki-laki' : ($siswa?->jenis_kelamin === 'P' ? 'Perempuan' : ($siswa?->jenis_kelamin ?? '-')) }}
                        </span>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3.5 rounded-2xl bg-slate-50/70 border border-slate-100">
                        <span class="text-xs font-semibold text-slate-400">Tanggal Lahir</span>
                        <span class="font-semibold text-slate-800 mt-1 sm:mt-0">
                            {{ $user->tanggal_lahir ?? ($siswa?->tanggal_lahir ? \Carbon\Carbon::parse($siswa->tanggal_lahir)->locale('id')->translatedFormat('d F Y') : '-') }}
                        </span>
                    </div>

                    <div class="flex flex-col p-3.5 rounded-2xl bg-slate-50/70 border border-slate-100">
                        <span class="text-xs font-semibold text-slate-400 mb-1">Alamat Domisili</span>
                        <span class="font-medium text-slate-800">{{ $siswa->alamat ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- Card 2: Informasi Akademik & Wali -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-5">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-graduation-cap text-base"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-800">Akademik & Wali Murid</h2>
                        <p class="text-xs text-slate-400">Informasi penempatan kelas dan kontak wali.</p>
                    </div>
                </div>

                <div class="space-y-4 text-sm">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3.5 rounded-2xl bg-slate-50/70 border border-slate-100">
                        <span class="text-xs font-semibold text-slate-400">Kelas Aktif</span>
                        <span class="font-bold text-indigo-600 mt-1 sm:mt-0">{{ $kelas->nama_kelas ?? 'Belum Ditentukan' }}</span>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3.5 rounded-2xl bg-slate-50/70 border border-slate-100">
                        <span class="text-xs font-semibold text-slate-400">Tingkatan</span>
                        <span class="font-semibold text-slate-800 mt-1 sm:mt-0">Tingkat {{ $kelas->tingkatan ?? '-' }}</span>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3.5 rounded-2xl bg-slate-50/70 border border-slate-100">
                        <span class="text-xs font-semibold text-slate-400">Tahun Ajaran / Semester</span>
                        <span class="font-semibold text-slate-800 mt-1 sm:mt-0">{{ $tahunAjaran }} • {{ $semester }}</span>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3.5 rounded-2xl bg-slate-50/70 border border-slate-100">
                        <span class="text-xs font-semibold text-slate-400">Nama Wali Murid</span>
                        <span class="font-bold text-slate-800 mt-1 sm:mt-0">{{ $siswa->wali_murid ?? '-' }}</span>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3.5 rounded-2xl bg-slate-50/70 border border-slate-100">
                        <span class="text-xs font-semibold text-slate-400">Nomor HP Wali</span>
                        <span class="font-mono font-semibold text-slate-800 mt-1 sm:mt-0">{{ $siswa->nohp_wali ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- Card 3: Informasi Akun LearnPoint -->
            <div class="col-span-1 md:col-span-2 bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-5">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-shield-halved text-base"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-800">Keamanan & Akun</h2>
                        <p class="text-xs text-slate-400">Kredensial akses portal belajar Anda.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                    <div class="p-4 rounded-2xl bg-slate-50/70 border border-slate-100">
                        <span class="text-xs font-semibold text-slate-400 block mb-1">Username</span>
                        <span class="font-mono font-bold text-slate-800">{{ $user->username }}</span>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50/70 border border-slate-100">
                        <span class="text-xs font-semibold text-slate-400 block mb-1">Email Terdaftar</span>
                        <span class="font-semibold text-slate-800 truncate block">{{ $user->email }}</span>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50/70 border border-slate-100">
                        <span class="text-xs font-semibold text-slate-400 block mb-1">Peran Pengguna</span>
                        <span class="font-bold text-blue-600 capitalize">Siswa</span>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50/70 border border-slate-100">
                        <span class="text-xs font-semibold text-slate-400 block mb-1">Terdaftar Sejak</span>
                        <span class="font-semibold text-slate-800">{{ $user->created_at ? $user->created_at->isoFormat('D MMM Y') : '-' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@else
    {{-- Default Profile for Guru, Admin & Operator --}}
    <div class="space-y-6">
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
            $nuptkGuru = $user->nuptk ?? $user->guru?->nuptk ?? ($user->isGuru() ? $user->username : null);
            $roleLabel = ucfirst($user->role);
            if ($user->isGuru()) {
                $mapelList = $user->guru ? $user->guru->guruMapels->map(fn($gm) => $gm->mapel?->nama_mapel)->filter()->unique()->values() : collect();
                $roleLabel = $mapelList->isNotEmpty() ? 'Guru ' . $mapelList->implode(', ') : 'Guru';
            }
        @endphp

        <!-- Top Profile Card -->
        <div class="bg-white p-6 sm:p-8 rounded-2xl border border-gray-200 shadow-sm flex flex-col sm:flex-row items-center sm:items-start gap-6">
            <div class="relative shrink-0">
                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-full border-2 border-white shadow-md overflow-hidden bg-indigo-600 text-white font-extrabold text-2xl sm:text-3xl flex items-center justify-center">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <a href="{{ route('profile.edit') }}" class="absolute bottom-0 right-0 w-7 h-7 rounded-full bg-blue-600 text-white flex items-center justify-center shadow hover:bg-blue-700 transition-colors" title="Ubah Profil">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                </a>
            </div>

            <div class="space-y-1.5 text-center sm:text-left min-w-0 flex-1">
                <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                    <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900 leading-snug">{{ $user->name }}</h2>
                    <span class="inline-block px-3 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 self-center sm:self-auto">
                        {{ $roleLabel }}
                    </span>
                </div>
                <div class="flex items-center justify-center sm:justify-start gap-2 text-xs text-gray-500 flex-wrap">
                    <span>{{ $user->email }}</span>
                    @if($user->isGuru() && $nuptkGuru)
                        <span>&bull;</span>
                        <span class="font-mono bg-blue-50 text-blue-700 font-semibold px-2 py-0.5 rounded">NUPTK {{ $nuptkGuru }}</span>
                    @endif
                    @if($user->isGuru() && $user->guru?->jenis_kelamin)
                        <span>&bull;</span>
                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-gray-700 bg-gray-100 px-2 py-0.5 rounded">
                            {{ in_array($user->guru->jenis_kelamin, ['L', 'Laki-laki']) ? 'Laki-laki' : (in_array($user->guru->jenis_kelamin, ['P', 'Perempuan']) ? 'Perempuan' : $user->guru->jenis_kelamin) }}
                        </span>
                    @endif
                    <span>&bull;</span>
                    <span class="text-emerald-600 font-semibold flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        {{ ucfirst($user->status ?? 'Aktif') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Main Profile Form Card -->
        <div class="bg-white p-6 sm:p-8 rounded-2xl border border-gray-200 shadow-sm space-y-6">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Informasi Pribadi</h3>
                <p class="text-xs text-gray-500 mt-0.5">Informasi akun pengguna dan biodata Anda.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Lengkap</label>
                    <div class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-800 font-medium bg-gray-50">
                        {{ $user->name ?? '-' }}
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Email</label>
                    <div class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-800 font-medium bg-gray-50">
                        {{ $user->email ?? '-' }}
                    </div>
                </div>

                @if($user->isGuru())
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">NUPTK</label>
                        <div class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-800 font-mono font-medium bg-gray-50">
                            {{ $nuptkGuru ?? '-' }}
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Nomor Telepon</label>
                        <div class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-800 font-medium bg-gray-50">
                            {{ $user->guru?->nomor_telepon ?? '-' }}
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Jenis Kelamin</label>
                        <div class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-800 font-medium bg-gray-50">
                            {{ in_array($user->guru?->jenis_kelamin, ['L', 'Laki-laki']) ? 'Laki-laki' : (in_array($user->guru?->jenis_kelamin, ['P', 'Perempuan']) ? 'Perempuan' : ($user->guru?->jenis_kelamin ?? '-')) }}
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Status Wali Kelas</label>
                        <div class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm font-semibold {{ $user->guru?->kelas ? 'text-indigo-700 bg-indigo-50/50' : 'text-gray-500 bg-gray-50' }}">
                            {{ $user->guru?->kelas ? 'Wali Kelas ' . $user->guru->kelas->nama_kelas : 'Bukan Wali Kelas' }}
                        </div>
                    </div>

                    <div class="space-y-1.5 md:col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Mata Pelajaran &amp; Kelas yang Diampu</label>
                        <div class="w-full p-4 rounded-xl border border-gray-200 bg-gray-50 text-sm space-y-2">
                            @php
                                $guruMapels = $user->guru ? $user->guru->guruMapels()->with(['mapel', 'pengampuKelases.kelas'])->get() : collect();
                            @endphp
                            @forelse($guruMapels as $gm)
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 pb-2 border-b border-gray-200/60 last:border-b-0 last:pb-0">
                                    <span class="font-bold text-gray-800 text-xs">{{ $gm->mapel?->nama_mapel ?? 'Mapel' }}</span>
                                    <div class="flex items-center gap-1 flex-wrap">
                                        @forelse($gm->pengampuKelases as $pk)
                                            <span class="px-2 py-0.5 rounded bg-white border border-gray-200 text-gray-700 text-[11px] font-medium">
                                                {{ $pk->kelas?->nama_kelas ?? '-' }}
                                            </span>
                                        @empty
                                            <span class="text-[11px] text-gray-400 italic">Belum ditentukan kelas</span>
                                        @endforelse
                                    </div>
                                </div>
                            @empty
                                <span class="text-xs text-gray-400">Belum ada mata pelajaran yang ditugaskan oleh admin.</span>
                            @endforelse
                        </div>
                    </div>
                @endif
            </div>

            <div class="flex justify-end pt-4">
                <a href="{{ route('profile.edit') }}" class="px-8 py-2.5 rounded-full bg-blue-600 text-white font-semibold text-sm hover:bg-blue-700 transition-colors shadow-sm inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit Profil
                </a>
            </div>
        </div>
    </div>
@endif
@endsection
