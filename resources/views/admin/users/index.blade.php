@extends('layouts.app')

@section('title', 'Kelola Akun Pengguna')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">

    <!-- Header & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Kelola Akun Pengguna</h1>
            <p class="text-sm text-slate-500 mt-1">Daftar seluruh akun sistem terurut hierarki peran (Admin, Kepala Sekolah, Guru, Siswa).</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('users.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-500/20 transition-all hover:-translate-y-0.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Akun</span>
            </a>
        </div>
    </div>

    <!-- 4 Highlight Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Akun -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs hover:shadow-xs transition-all relative overflow-hidden group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Akun</span>
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ $totalUsers }}</div>
            <div class="text-[11px] font-medium text-slate-500 mt-1">Terdaftar di sistem</div>
        </div>

        <!-- Akun Guru -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs hover:shadow-xs transition-all relative overflow-hidden group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Guru</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ $countGuru ?? 0 }}</div>
            <div class="text-[11px] font-medium text-slate-500 mt-1">Tenaga pendidik</div>
        </div>

        <!-- Akun Siswa -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs hover:shadow-xs transition-all relative overflow-hidden group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Siswa</span>
                <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ $countSiswa ?? 0 }}</div>
            <div class="text-[11px] font-medium text-slate-500 mt-1">Peserta didik</div>
        </div>

        <!-- Admin & Kepsek -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs hover:shadow-xs transition-all relative overflow-hidden group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Admin & Kepsek</span>
                <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ ($countOperator ?? 0) + ($countKepalaSekolah ?? 0) }}</div>
            <div class="text-[11px] font-medium text-slate-500 mt-1">{{ $countOperator ?? 0 }} Admin, {{ $countKepalaSekolah ?? 0 }} Kepsek</div>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50/80 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-3 shadow-2xs">
            <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div class="flex-1">
                <p class="font-bold">{{ session('success') }}</p>
                @if(session('import_errors') && count(session('import_errors')) > 0)
                    <div class="mt-2 text-xs text-rose-700 space-y-1 bg-white/70 p-2.5 rounded-xl border border-rose-200">
                        <p class="font-bold">Catatan baris yang dilewati:</p>
                        <ul class="list-disc pl-4 space-y-0.5">
                            @foreach(session('import_errors') as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if(session('warning'))
        <div class="p-4 rounded-2xl bg-amber-50/80 border border-amber-200 text-amber-800 text-sm flex items-center gap-3 shadow-2xs">
            <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <span>{{ session('warning') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-50/80 border border-rose-200 text-rose-800 text-sm flex items-center gap-3 shadow-2xs">
            <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Filter & Search Card -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-2xs">
        <form method="GET" action="{{ route('users.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <!-- Search Field -->
            <div class="sm:col-span-6 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Cari nama, username, atau email pengguna..."
                    class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl border border-slate-200 bg-slate-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-slate-800"
                >
            </div>

            <!-- Role Filter -->
            <div class="sm:col-span-4">
                <select name="role" class="w-full py-2.5 px-3 text-xs rounded-xl border border-slate-200 bg-slate-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-slate-700">
                    <option value="">Semua Peran (Default: Urut Hierarki)</option>
                    <option value="operator" {{ request('role') == 'operator' ? 'selected' : '' }}>Operator / Admin</option>
                    <option value="kepala_sekolah" {{ request('role') == 'kepala_sekolah' ? 'selected' : '' }}>Kepala Sekolah</option>
                    <option value="guru" {{ request('role') == 'guru' ? 'selected' : '' }}>Guru</option>
                    <option value="siswa" {{ request('role') == 'siswa' ? 'selected' : '' }}>Siswa</option>
                </select>
            </div>

            <!-- Submit Button -->
            <div class="sm:col-span-2 flex gap-2">
                <button type="submit" class="flex-1 py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-500/20 transition-all">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'role']))
                    <a href="{{ route('users.index') }}" class="py-2.5 px-3.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition-colors flex items-center justify-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        <!-- Total Data Counter -->
        <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 font-medium">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 font-bold text-[11px] border border-blue-100">
                    {{ $users->total() }} / {{ $totalUsers }}
                </span>
                <span>Menampilkan <strong>{{ $users->total() }}</strong> dari <strong>{{ $totalUsers }}</strong> total akun pengguna{{ request()->hasAny(['search', 'role']) ? ' (hasil filter)' : '' }}.</span>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3.5 px-4 sm:px-6">Pengguna</th>
                        <th class="py-3.5 px-4">Username & Email</th>
                        <th class="py-3.5 px-4">Peran (Role)</th>
                        <th class="py-3.5 px-4">Status Akun</th>
                        <th class="py-3.5 px-4">Tautan Profil Data</th>
                        <th class="py-3.5 px-4 text-right pr-6">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50/60 transition-colors {{ ($user->status ?? 'aktif') === 'nonaktif' ? 'bg-slate-50/40 opacity-75' : '' }}">
                            <td class="py-3.5 px-4 sm:px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl {{ ($user->status ?? 'aktif') === 'nonaktif' ? 'bg-slate-300 text-slate-600' : 'bg-gradient-to-tr from-blue-600 to-indigo-600 text-white shadow-xs' }} font-bold text-xs flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-slate-900 truncate">{{ $user->name }}</p>
                                        <p class="text-[11px] text-slate-400">ID #{{ $user->id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="text-xs font-semibold text-slate-800 block">{{ $user->username ?? '-' }}</span>
                                <span class="text-[11px] text-slate-400">{{ $user->email }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($user->role === 'operator')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-100">
                                        Operator / Admin
                                    </span>
                                @elseif($user->role === 'kepala_sekolah')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-100">
                                        Kepala Sekolah
                                    </span>
                                @elseif($user->role === 'guru')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                        Guru
                                    </span>
                                @elseif($user->role === 'siswa')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                        Siswa
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                @if(($user->status ?? 'aktif') === 'aktif')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-xs text-slate-600">
                                @if($user->guru)
                                    <a href="{{ route('guru.show', $user->guru->id) }}" class="inline-flex items-center gap-1.5 text-blue-600 hover:text-blue-800 font-semibold group">
                                        <svg class="w-3.5 h-3.5 text-blue-500 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        <span>Guru: {{ $user->guru->nama }}</span>
                                    </a>
                                @elseif($user->siswa)
                                    <a href="{{ route('siswa.show', $user->siswa->id) }}" class="inline-flex items-center gap-1.5 text-emerald-600 hover:text-emerald-800 font-semibold group">
                                        <svg class="w-3.5 h-3.5 text-emerald-500 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/></svg>
                                        <span>Siswa: {{ $user->siswa->nama_siswa }} ({{ $user->siswa->kelas->nama_kelas ?? '-' }})</span>
                                    </a>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">- Akun Sistem / Mandiri -</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right pr-6 whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('users.edit', $user->id) }}" class="p-2 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all" title="Edit Akun">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>

                                    @if(auth()->id() !== $user->id)
                                        <!-- Tombol Toggle Status Nonaktif / Aktif -->
                                        <form action="{{ route('users.toggle-status', $user->id) }}" method="POST" data-confirm="{{ ($user->status ?? 'aktif') === 'aktif' ? 'Apakah Anda yakin ingin menonaktifkan akun ' . $user->name . '? Data terikat dan akses login pengguna akan dinonaktifkan.' : 'Apakah Anda yakin ingin mengaktifkan kembali akun ' . $user->name . '?' }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            @if(($user->status ?? 'aktif') === 'aktif')
                                                <button type="submit" class="p-2 text-slate-500 hover:text-amber-600 hover:bg-amber-50 rounded-xl transition-all" title="Nonaktifkan Akun">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                                </button>
                                            @else
                                                <button type="submit" class="p-2 text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition-all" title="Aktifkan Kembali Akun">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                </button>
                                            @endif
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <div class="max-w-xs mx-auto text-center space-y-2">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto text-slate-400">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                    </div>
                                    <p class="font-bold text-slate-700 text-sm">Tidak ada akun ditemukan</p>
                                    <p class="text-xs text-slate-400">Coba ubah kata kunci pencarian atau filter peran Anda.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/30">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>
@endsection