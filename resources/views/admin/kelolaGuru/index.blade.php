@extends('layouts.app')

@section('title', 'Data Guru & Tenaga Pengajar')

@section('content')
<div class="space-y-6" x-data="{ csvModalOpen: false }">

    <!-- Header & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Data Guru</h1>
            <p class="text-sm text-gray-500 mt-1">Daftar tenaga pendidik, NIP, tugas wali kelas, dan akun sistem.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('guru.template-csv') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-xl transition-colors" title="Unduh contoh template CSV Guru">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Format CSV</span>
            </a>
            <button 
                type="button" 
                @click="csvModalOpen = true" 
                class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md shadow-emerald-200 transition-all hover:-translate-y-0.5"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                <span>Import CSV</span>
            </button>
            <a href="{{ route('guru.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-indigo-200 transition-all hover:shadow-lg hover:-translate-y-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Guru</span>
            </a>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
        <form method="GET" action="{{ route('guru.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-6 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Cari berdasarkan nama atau NIP guru..."
                    class="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-gray-50/50"
                >
            </div>

            <!-- Filter Tingkatan (7,8,9) (Manager Requirement) -->
            <div class="sm:col-span-4">
                <select name="tingkatan" class="w-full py-2 px-3 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-gray-50/50 text-gray-700">
                    <option value="">Semua Tingkatan (7, 8, 9)</option>
                    <option value="7" {{ request('tingkatan') == '7' ? 'selected' : '' }}>Tingkatan 7</option>
                    <option value="8" {{ request('tingkatan') == '8' ? 'selected' : '' }}>Tingkatan 8</option>
                    <option value="9" {{ request('tingkatan') == '9' ? 'selected' : '' }}>Tingkatan 9</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex gap-2">
                <button type="submit" class="flex-1 py-2 px-4 bg-gray-800 hover:bg-gray-900 text-white rounded-xl text-sm font-medium transition-colors">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'tingkatan']))
                    <a href="{{ route('guru.index') }}" class="py-2 px-3 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-sm font-medium transition-colors flex items-center justify-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        <!-- Total Data Counter (Persyaratan Manager Poin 3) -->
        <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500 font-medium">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 font-bold text-xs">
                    Total: {{ $gurus->total() }} / {{ $totalGuru }}
                </span>
                <span>Menampilkan <strong>{{ $gurus->total() }}</strong> dari total <strong>{{ $totalGuru }}</strong> data guru{{ request()->hasAny(['search', 'tingkatan']) ? ' (setelah difilter)' : '' }}.</span>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-200 text-[11px] font-bold uppercase tracking-wider text-gray-500">
                        <th class="py-3.5 px-4 sm:px-6">Nama & NIP</th>
                        <th class="py-3.5 px-4">Status Data</th>
                        <th class="py-3.5 px-4">Akun Pengguna</th>
                        <th class="py-3.5 px-4">Wali Kelas</th>
                        <th class="py-3.5 px-4">Mapel Diampu</th>
                        <th class="py-3.5 px-4 text-right pr-6">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($gurus as $guru)
                        <tr class="hover:bg-gray-50/60 transition-colors {{ ($guru->status ?? 'aktif') === 'nonaktif' ? 'bg-gray-50/60 opacity-75' : '' }}">
                            <td class="py-4 px-4 sm:px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full {{ ($guru->status ?? 'aktif') === 'nonaktif' ? 'bg-gray-300 text-gray-600' : 'bg-indigo-100 text-indigo-700' }} flex items-center justify-center font-bold text-sm shrink-0">
                                        {{ strtoupper(substr($guru->nama, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900">{{ $guru->nama }}</p>
                                        <p class="text-xs text-gray-500 font-mono">NIP: {{ $guru->nip }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                @if(($guru->status ?? 'aktif') === 'aktif')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                @if($guru->user)
                                    <div class="flex items-center gap-1.5 text-xs text-gray-600">
                                        <span class="w-2 h-2 rounded-full {{ ($guru->user->status ?? 'aktif') === 'aktif' ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                        <span class="font-mono text-gray-800 font-semibold">{{ $guru->user->username }}</span>
                                        <span class="text-gray-400">({{ $guru->user->status }})</span>
                                    </div>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                        Belum Ada Akun
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                @if($guru->kelas)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200/50">
                                        Kelas {{ $guru->kelas->nama_kelas }} (Tingkat {{ $guru->kelas->tingkatan }})
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400 italic">Bukan Wali Kelas</span>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                @php
                                    $groupedMapels = $guru->pengampuKelases->groupBy('guru_mapel_id');
                                @endphp
                                @if($groupedMapels->count() > 0)
                                    <div class="flex flex-wrap gap-1.5 max-w-sm">
                                        @foreach($groupedMapels->take(2) as $gmId => $items)
                                            @php
                                                $mapelName = $items->first()->mapel->nama_mapel ?? '-';
                                                $kelasList = $items->map(fn($i) => $i->kelas->nama_kelas ?? '-')->filter()->join(', ');
                                            @endphp
                                            <span class="px-2 py-0.5 rounded-md bg-gray-100 text-gray-800 text-xs font-medium border border-gray-200/60">
                                                <strong class="font-bold text-gray-900">{{ $mapelName }}</strong> ({{ $kelasList }})
                                            </span>
                                        @endforeach
                                        @if($groupedMapels->count() > 2)
                                            <span class="px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-600 font-semibold text-[11px] self-center">
                                                +{{ $groupedMapels->count() - 2 }} mapel lainnya
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-400 italic">Belum ada mapel</span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-right pr-6 whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('guru.show', $guru->id) }}" class="p-1.5 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors" title="Lihat Detail">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a href="{{ route('guru.edit', $guru->id) }}" class="p-1.5 text-indigo-600 hover:text-indigo-900 hover:bg-indigo-50 rounded-lg transition-colors" title="Edit Guru & Mapel">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    
                                    <!-- Tombol Toggle Status Nonaktif / Aktif (Menggantikan Hapus Permanen) -->
                                    <form action="{{ route('guru.toggle-status', $guru->id) }}" method="POST" data-confirm="{{ ($guru->status ?? 'aktif') === 'aktif' ? 'Apakah Anda yakin ingin menonaktifkan data guru ' . $guru->nama . '? Akun login yang terikat juga akan otomatis dinonaktifkan.' : 'Apakah Anda yakin ingin mengaktifkan kembali data guru ' . $guru->nama . '? Akun login yang terikat juga akan kembali aktif.' }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        @if(($guru->status ?? 'aktif') === 'aktif')
                                            <button type="submit" class="p-1.5 text-amber-600 hover:text-amber-800 hover:bg-amber-50 rounded-lg transition-colors" title="Nonaktifkan Guru">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                            </button>
                                        @else
                                            <button type="submit" class="p-1.5 text-emerald-600 hover:text-emerald-800 hover:bg-emerald-50 rounded-lg transition-colors" title="Aktifkan Guru">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </button>
                                        @endif
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-gray-400">
                                <div class="max-w-xs mx-auto text-center space-y-2">
                                    <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto text-gray-400">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                    </div>
                                    <p class="font-semibold text-gray-700">Tidak ada data guru ditemukan</p>
                                    <p class="text-xs text-gray-400">Coba ubah kata kunci pencarian Anda atau tambahkan guru baru.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($gurus->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $gurus->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Import CSV Guru (Poin 6) -->
    <div 
        x-show="csvModalOpen" 
        class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;"
    >
        <div class="min-h-screen px-4 text-center flex items-center justify-center">
            <!-- Backdrop -->
            <div 
                class="fixed inset-0 bg-gray-900/60 transition-opacity" 
                @click="csvModalOpen = false"
            ></div>

            <!-- Modal Card -->
            <div class="relative bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 text-left shadow-2xl border border-gray-100 z-10 space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-extrabold text-gray-900">Import Data Guru via CSV</h3>
                            <p class="text-xs text-gray-500">Unggah file CSV untuk menambahkan guru & akun otomatis.</p>
                        </div>
                    </div>
                    <button 
                        type="button" 
                        @click="csvModalOpen = false"
                        class="p-2 text-gray-400 hover:text-gray-700 rounded-xl hover:bg-gray-100"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="bg-indigo-50/70 p-4 rounded-2xl border border-indigo-100 text-xs text-indigo-900 space-y-2">
                    <p class="font-bold flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Format Kolom Wajib CSV Guru:
                    </p>
                    <code class="block font-mono bg-white p-2 rounded-xl border border-indigo-200 text-indigo-700 text-[11px] overflow-x-auto font-bold">
                        nama,nip
                    </code>
                    <p class="text-indigo-600 text-[11px]">
                        * Setiap baris guru akan otomatis dibuatkan akun user (Username: NIP, Password: NIP, Role: Guru, Status: Aktif).
                    </p>
                    <div class="pt-1">
                        <a href="{{ route('guru.template-csv') }}" class="text-indigo-700 font-bold underline hover:text-indigo-900 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Klik di sini untuk unduh template CSV Guru
                        </a>
                    </div>
                </div>

                <form action="{{ route('guru.import-csv') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">
                            Pilih File CSV (.csv) <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="file" 
                            name="csv_file" 
                            accept=".csv,text/csv" 
                            required 
                            class="w-full text-xs text-gray-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border border-gray-200 rounded-xl p-1 bg-gray-50/50"
                        >
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-3">
                        <button 
                            type="button" 
                            @click="csvModalOpen = false"
                            class="px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold transition-colors"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-200 transition-all"
                        >
                            Mulai Import Data Guru
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
