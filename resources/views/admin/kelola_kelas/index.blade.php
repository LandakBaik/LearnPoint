@extends('layouts.app')

@section('title', 'Kelola Kelas')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">

    <!-- Header & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Kelola Kelas</h1>
            <p class="text-sm text-slate-500 mt-1">Daftar kelas, tingkatan jenjang, penugasan wali kelas, dan jadwal pelajaran.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('kelas.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-500/20 transition-all hover:-translate-y-0.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Kelas</span>
            </a>
        </div>
    </div>

    <!-- 4 Highlight Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Kelas -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs hover:shadow-xs transition-all relative overflow-hidden group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Kelas</span>
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m3 0h1m-1-4h.01M9 16h.01M9 12h.01M9 8h.01M15 16h.01M15 12h.01M15 8h.01"/></svg>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ $totalKelas ?? $kelases->total() }}</div>
            <div class="text-[11px] font-medium text-slate-500 mt-1">Kelas aktif</div>
        </div>

        <!-- Tingkat 7 -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs hover:shadow-xs transition-all relative overflow-hidden group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Tingkat 7</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <span class="font-black text-sm">VII</span>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ $countTingkat7 ?? 0 }}</div>
            <div class="text-[11px] font-medium text-slate-500 mt-1">Kelas kelas 7</div>
        </div>

        <!-- Tingkat 8 -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs hover:shadow-xs transition-all relative overflow-hidden group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Tingkat 8</span>
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <span class="font-black text-sm">VIII</span>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ $countTingkat8 ?? 0 }}</div>
            <div class="text-[11px] font-medium text-slate-500 mt-1">Kelas kelas 8</div>
        </div>

        <!-- Tingkat 9 -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs hover:shadow-xs transition-all relative overflow-hidden group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Tingkat 9</span>
                <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                    <span class="font-black text-sm">IX</span>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ $countTingkat9 ?? 0 }}</div>
            <div class="text-[11px] font-medium text-slate-500 mt-1">Kelas kelas 9</div>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50/80 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-3 shadow-2xs">
            <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <span class="font-bold">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Filter & Search Card -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-2xs">
        <form method="GET" action="{{ route('kelas.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-6 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Cari nama kelas..."
                    class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl border border-slate-200 bg-slate-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-slate-800"
                >
            </div>

            <div class="sm:col-span-4">
                <select name="tingkatan" class="w-full py-2.5 px-3 text-xs rounded-xl border border-slate-200 bg-slate-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-slate-700">
                    <option value="">Semua Tingkatan</option>
                    <option value="7" {{ request('tingkatan') == '7' ? 'selected' : '' }}>Tingkat 7 (Kelas VII)</option>
                    <option value="8" {{ request('tingkatan') == '8' ? 'selected' : '' }}>Tingkat 8 (Kelas VIII)</option>
                    <option value="9" {{ request('tingkatan') == '9' ? 'selected' : '' }}>Tingkat 9 (Kelas IX)</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex gap-2">
                <button type="submit" class="flex-1 py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-500/20 transition-all">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'tingkatan']))
                    <a href="{{ route('kelas.index') }}" class="py-2.5 px-3.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition-colors flex items-center justify-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        <!-- Total Data Counter -->
        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 font-medium">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 font-bold text-xs border border-blue-100/60">
                    Total: {{ $kelases->total() }} / {{ $totalKelas }}
                </span>
                <span>Menampilkan <strong>{{ $kelases->total() }}</strong> dari total <strong>{{ $totalKelas }}</strong> data kelas{{ request()->hasAny(['search', 'tingkatan']) ? ' (setelah difilter)' : '' }}.</span>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3.5 px-4 sm:px-6">Nama Kelas</th>
                        <th class="py-3.5 px-4">Tingkatan</th>
                        <th class="py-3.5 px-4">Wali Kelas</th>
                        <th class="py-3.5 px-4">Guru Mapel</th>
                        <th class="py-3.5 px-4">Jumlah Siswa</th>
                        <th class="py-3.5 px-4">Jadwal (Foto)</th>
                        <th class="py-3.5 px-4 text-right pr-6">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($kelases as $item)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4 sm:px-6 font-bold text-slate-900">
                                <a href="{{ route('kelas.show', $item->id) }}" class="inline-flex items-center gap-2 hover:text-blue-600 transition-colors">
                                    <!-- <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs shrink-0">
                                        {{ substr($item->nama_kelas, 0, 2) }}
                                    </div> -->
                                    <span>{{ $item->nama_kelas }}</span>
                                </a>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                    Tingkat {{ $item->tingkatan }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($item->guru)
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-full bg-slate-100 text-slate-700 font-bold text-[10px] flex items-center justify-center shrink-0 border border-slate-200">
                                            {{ strtoupper(substr($item->guru->nama, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-slate-800">{{ $item->guru->nama }}</p>
                                            <p class="text-[11px] text-slate-400">NUPTK: {{ $item->guru->nuptk }}</p>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">- Belum Ada Wali Kelas -</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-semibold">
                                <a href="{{ route('kelas.show', $item->id) }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-50 hover:bg-blue-50 hover:text-blue-700 text-slate-700 border border-slate-200/80 transition-colors text-[11px]">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                    <span>{{ $item->pengampu_kelases_count }} Mapel &rarr;</span>
                                </a>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-700">
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                    {{ $item->siswas->count() }} Siswa
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-xs">
                                @if($item->jadwal_url)
                                    <span class="inline-flex items-center gap-1 text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200/80 text-[11px]">
                                        <svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Ada Foto
                                    </span>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">Belum diupload</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right pr-6 whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('kelas.show', $item->id) }}" class="p-2 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all" title="Lihat Detail Kelas">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a href="{{ route('kelas.edit', $item->id) }}" class="p-2 text-slate-500 hover:text-amber-600 hover:bg-amber-50 rounded-xl transition-all" title="Edit Kelas">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form action="{{ route('kelas.destroy', $item->id) }}" method="POST" data-confirm="Apakah Anda yakin ingin menghapus kelas {{ $item->nama_kelas }}?" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all" title="Hapus Kelas">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <div class="max-w-xs mx-auto text-center space-y-2">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto text-slate-400">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m3 0h1m-1-4h.01M9 16h.01M9 12h.01M9 8h.01M15 16h.01M15 12h.01M15 8h.01"/></svg>
                                    </div>
                                    <p class="font-bold text-slate-700 text-sm">Tidak ada data kelas ditemukan</p>
                                    <p class="text-xs text-slate-400">Coba ubah kata kunci pencarian atau buat kelas baru.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($kelases->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/30">
                {{ $kelases->links() }}
            </div>
        @endif
    </div>

</div>
@endsection