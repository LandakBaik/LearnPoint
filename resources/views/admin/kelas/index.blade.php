@extends('layouts.app')

@section('title', 'Kelola Kelas')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Kelola Kelas</h1>
            <p class="text-sm text-gray-500 mt-1">Daftar kelas, tingkatan kelas, dan guru wali kelas.</p>
        </div>
        <div>
            <a href="{{ route('kelas.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-amber-200 transition-all hover:shadow-lg hover:-translate-y-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Kelas</span>
            </a>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
        <form method="GET" action="{{ route('kelas.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-6 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Cari nama kelas..."
                    class="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent bg-gray-50/50"
                >
            </div>

            <div class="sm:col-span-4">
                <select name="tingkatan" class="w-full py-2 px-3 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent bg-gray-50/50 text-gray-700">
                    <option value="">Semua Tingkatan</option>
                    <option value="7" {{ request('tingkatan') == '7' ? 'selected' : '' }}>Tingkat 7 (Kelas VII)</option>
                    <option value="8" {{ request('tingkatan') == '8' ? 'selected' : '' }}>Tingkat 8 (Kelas VIII)</option>
                    <option value="9" {{ request('tingkatan') == '9' ? 'selected' : '' }}>Tingkat 9 (Kelas IX)</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex gap-2">
                <button type="submit" class="flex-1 py-2 px-4 bg-gray-800 hover:bg-gray-900 text-white rounded-xl text-sm font-medium transition-colors">
                    Cari
                </button>
                @if(request()->hasAny(['search', 'tingkatan']))
                    <a href="{{ route('kelas.index') }}" class="py-2 px-3 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-sm font-medium transition-colors flex items-center justify-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-200 text-[11px] font-bold uppercase tracking-wider text-gray-500">
                        <th class="py-3.5 px-4 sm:px-6">Nama Kelas</th>
                        <th class="py-3.5 px-4">Tingkatan</th>
                        <th class="py-3.5 px-4">Wali Kelas</th>
                        <th class="py-3.5 px-4">Guru Mapel</th>
                        <th class="py-3.5 px-4">Jumlah Siswa</th>
                        <th class="py-3.5 px-4">Jadwal (Foto)</th>
                        <th class="py-3.5 px-4 text-right pr-6">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($kelases as $item)
                        <tr class="hover:bg-amber-50/30 transition-colors">
                            <td class="py-4 px-4 sm:px-6 font-bold text-gray-900">
                                <a href="{{ route('kelas.show', $item->id) }}" class="hover:text-amber-600 transition-colors">
                                    {{ $item->nama_kelas }}
                                </a>
                            </td>
                            <td class="py-4 px-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                    Tingkat {{ $item->tingkatan }}
                                </span>
                            </td>
                            <td class="py-4 px-4">
                                @if($item->guru)
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 font-bold text-[10px] flex items-center justify-center shrink-0">
                                            {{ strtoupper(substr($item->guru->nama, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-xs text-gray-800">{{ $item->guru->nama }}</p>
                                            <p class="text-[10px] text-gray-400 font-mono">NIP: {{ $item->guru->nip }}</p>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-400 italic text-xs">- Belum Ada Wali Kelas -</span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-xs font-semibold">
                                <a href="{{ route('kelas.show', $item->id) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                    <span>{{ $item->pengampu_kelases_count }} Mapel &rarr;</span>
                                </a>
                            </td>
                            <td class="py-4 px-4 font-semibold text-gray-700">
                                <span class="inline-flex items-center gap-1">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                    {{ $item->siswas->count() }} Siswa
                                </span>
                            </td>
                            <td class="py-4 px-4 text-xs">
                                @if($item->jadwal_url)
                                    <span class="inline-flex items-center gap-1 text-emerald-600 font-semibold bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Ada Foto
                                    </span>
                                @else
                                    <span class="text-gray-400 italic">Belum diupload</span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-right pr-6 whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('kelas.show', $item->id) }}" class="p-1.5 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors" title="Lihat Detail Kelas">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a href="{{ route('kelas.edit', $item->id) }}" class="p-1.5 text-amber-600 hover:text-amber-900 hover:bg-amber-50 rounded-lg transition-colors" title="Edit Kelas">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form action="{{ route('kelas.destroy', $item->id) }}" method="POST" data-confirm="Apakah Anda yakin ingin menghapus kelas {{ $item->nama_kelas }}?" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus Kelas">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-gray-400">
                                <div class="max-w-xs mx-auto text-center space-y-2">
                                    <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto text-gray-400">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m3 0h1m-1-4h.01M9 16h.01M9 12h.01M9 8h.01M15 16h.01M15 12h.01M15 8h.01"/></svg>
                                    </div>
                                    <p class="font-semibold text-gray-700">Tidak ada data kelas ditemukan</p>
                                    <p class="text-xs text-gray-400">Coba ubah kata kunci pencarian atau buat kelas baru.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($kelases->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $kelases->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
