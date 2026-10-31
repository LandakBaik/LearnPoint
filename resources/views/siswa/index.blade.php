@extends('layouts.app')

@section('title', 'Data Siswa & Peserta Didik')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto" x-data="{ csvModalOpen: false }">

    <!-- Header & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Data Siswa</h1>
            <p class="text-sm text-slate-500 mt-1">Daftar peserta didik aktif, kelas, dan informasi kontak wali murid.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('siswa.template-csv') }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2.5 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl border border-slate-200/80 shadow-2xs transition-colors" 
               title="Unduh contoh template CSV Siswa">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span>Format CSV</span>
            </a>
            <button type="button" 
                    @click="csvModalOpen = true" 
                    class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold rounded-xl border border-blue-100/80 shadow-2xs transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                <span>Import CSV</span>
            </button>
            <a href="{{ route('siswa.create') }}" 
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-md shadow-blue-500/20 hover:shadow-lg hover:shadow-blue-500/30 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Tambah Siswa</span>
            </a>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('siswa.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-5 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Cari nama, NIS, atau wali murid..."
                       class="w-full pl-10 pr-4 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-slate-50/50 text-slate-800 placeholder-slate-400">
            </div>

            <!-- Filter Tingkatan (7,8,9) -->
            <div class="sm:col-span-3">
                <select name="tingkatan" class="w-full py-2.5 px-3 text-xs sm:text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-slate-50/50 text-slate-700">
                    <option value="">Semua Tingkatan</option>
                    <option value="7" {{ request('tingkatan') == '7' ? 'selected' : '' }}>Tingkatan 7</option>
                    <option value="8" {{ request('tingkatan') == '8' ? 'selected' : '' }}>Tingkatan 8</option>
                    <option value="9" {{ request('tingkatan') == '9' ? 'selected' : '' }}>Tingkatan 9</option>
                </select>
            </div>

            <!-- Filter Kelas -->
            <div class="sm:col-span-2">
                <select name="kelas_id" class="w-full py-2.5 px-3 text-xs sm:text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-slate-50/50 text-slate-700">
                    <option value="">Semua Kelas</option>
                    @foreach($kelases as $k)
                        <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                            {{ $k->nama_kelas }} (Tingkat {{ $k->tingkatan }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2 flex gap-2">
                <button type="submit" class="flex-1 py-2.5 px-4 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-semibold transition-colors">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'kelas_id', 'tingkatan']))
                    <a href="{{ route('siswa.index') }}" class="py-2.5 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition-colors flex items-center justify-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        <!-- Total Data Counter -->
        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 font-medium">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 font-bold text-xs border border-blue-100/60">
                    Total: {{ $siswa->total() }} / {{ $totalSiswa }}
                </span>
                <span>Menampilkan <strong>{{ $siswa->total() }}</strong> dari total <strong>{{ $totalSiswa }}</strong> data siswa{{ request()->hasAny(['search', 'kelas_id', 'tingkatan']) ? ' (setelah difilter)' : '' }}.</span>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-200/60 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3.5 px-4 sm:px-6">Nama &amp; NIS</th>
                        <th class="py-3.5 px-4">Status Data</th>
                        <th class="py-3.5 px-4">Akun Login</th>
                        <th class="py-3.5 px-4">Kelas</th>
                        <th class="py-3.5 px-4">L/P</th>
                        <th class="py-3.5 px-4">Wali &amp; No. HP</th>
                        <th class="py-3.5 px-4 text-right pr-6">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                    @forelse($siswa as $data)
                        <tr class="hover:bg-slate-50/60 transition-colors {{ ($data->status ?? 'aktif') === 'nonaktif' ? 'bg-slate-50/60 opacity-75' : '' }}">
                            <td class="py-4 px-4 sm:px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl {{ ($data->status ?? 'aktif') === 'nonaktif' ? 'bg-slate-200 text-slate-600' : 'bg-blue-50 text-blue-600 border border-blue-100/60' }} flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                                        {{ strtoupper(substr($data->nama_siswa, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800">{{ $data->nama_siswa }}</p>
                                        <p class="text-xs text-slate-500 mt-0.5">NIS: {{ $data->nis }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                @if(($data->status ?? 'aktif') === 'aktif')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60 shadow-2xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200/60 shadow-2xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                @if($data->user)
                                    <div class="flex items-center gap-1.5 text-xs text-slate-600">
                                        <span class="w-2 h-2 rounded-full {{ ($data->user->status ?? 'aktif') === 'aktif' ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                        <span class="text-slate-800 font-semibold">{{ $data->user->username }}</span>
                                    </div>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                        Belum Ada Akun
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                @if($data->kelas)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100/60">
                                        {{ $data->kelas->nama_kelas }} (Tingkat {{ $data->kelas->tingkatan }})
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 text-slate-500">
                                        Tanpa Kelas
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                <span class="px-2.5 py-0.5 text-xs font-bold rounded-md {{ $data->jenis_kelamin == 'L' ? 'bg-blue-50 text-blue-700 border border-blue-100' : 'bg-rose-50 text-rose-700 border border-rose-100' }}">
                                    {{ $data->jenis_kelamin }}
                                </span>
                            </td>
                            <td class="py-4 px-4">
                                <p class="text-slate-800 font-semibold">{{ $data->wali_murid }}</p>
                                @if($data->nohp_wali)
                                    <p class="text-xs text-slate-500 mt-0.5">{{ $data->nohp_wali }}</p>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-right pr-6 whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('siswa.show', $data->id) }}" 
                                       class="p-2 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-colors" 
                                       title="Lihat Detail Siswa">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('siswa.edit', $data->id) }}" 
                                       class="p-2 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-colors" 
                                       title="Edit Siswa">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    
                                    <!-- Tombol Toggle Status Nonaktif / Aktif -->
                                    <form action="{{ route('siswa.toggle-status', $data->id) }}" method="POST" data-confirm="{{ ($data->status ?? 'aktif') === 'aktif' ? 'Apakah Anda yakin ingin menonaktifkan data siswa ' . $data->nama_siswa . '? Akun login yang terikat juga akan otomatis dinonaktifkan.' : 'Apakah Anda yakin ingin mengaktifkan kembali data siswa ' . $data->nama_siswa . '? Akun login yang terikat juga akan kembali aktif.' }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        @if(($data->status ?? 'aktif') === 'aktif')
                                            <button type="submit" class="p-2 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-xl transition-colors" title="Nonaktifkan Siswa">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                </svg>
                                            </button>
                                        @else
                                            <button type="submit" class="p-2 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition-colors" title="Aktifkan Siswa">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </button>
                                        @endif
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <div class="max-w-xs mx-auto text-center space-y-2">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                    </div>
                                    <p class="font-bold text-slate-700 text-sm">Tidak ada data siswa ditemukan</p>
                                    <p class="text-xs text-slate-400">Coba sesuaikan kata kunci pencarian atau filter kelas Anda.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($siswa->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $siswa->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Import CSV Siswa -->
    <div x-show="csvModalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <div class="min-h-screen px-4 text-center flex items-center justify-center">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" 
                 @click="csvModalOpen = false"></div>

            <!-- Modal Card -->
            <div class="relative bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 text-left shadow-2xl border border-slate-100 z-10 space-y-5">
                <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100/60">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900">Import Data Siswa via CSV</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Unggah berkas CSV untuk mendaftarkan siswa &amp; akun sekaligus.</p>
                        </div>
                    </div>
                    <button type="button" 
                            @click="csvModalOpen = false"
                            class="p-1.5 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="bg-blue-50/60 p-4 rounded-2xl border border-blue-100 text-xs text-blue-900 space-y-2">
                    <p class="font-bold flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Format Kolom Wajib CSV Siswa:
                    </p>
                    <div class="bg-white p-2.5 rounded-xl border border-blue-200 text-blue-800 text-[11px] overflow-x-auto font-semibold">
                        nama_siswa,nis,alamat,tanggal_lahir,jenis_kelamin,wali_murid,nohp_wali,kelas
                    </div>
                    <p class="text-blue-700 text-[11px]">
                        * Setiap siswa akan otomatis dibuatkan akun pengguna (Username: NIS, Password: NIS, Role: Siswa, Status: Aktif).
                    </p>
                    <div class="pt-1">
                        <a href="{{ route('siswa.template-csv') }}" class="text-blue-800 font-bold underline hover:text-blue-900 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Klik di sini untuk mengunduh template CSV Siswa
                        </a>
                    </div>
                </div>

                <form action="{{ route('siswa.import-csv') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Pilih File CSV (.csv) <span class="text-rose-500">*</span>
                        </label>
                        <input type="file" 
                               name="csv_file" 
                               accept=".csv,text/csv" 
                               required 
                               class="w-full text-xs text-slate-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded-xl p-1 bg-slate-50/50">
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-3 border-t border-slate-100">
                        <button type="button" 
                                @click="csvModalOpen = false" 
                                class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors">
                            Batal
                        </button>
                        <button type="submit" 
                                class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition-all">
                            Mulai Import Data Siswa
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection