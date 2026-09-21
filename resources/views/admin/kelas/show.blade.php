@extends('layouts.app')

@section('title', 'Detail Kelas: ' . $kelas->nama_kelas)

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{ modalOpen: false, modalImg: '' }">

    <!-- Header Navigation -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('kelas.index') }}" class="p-2 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Detail Rombel {{ $kelas->nama_kelas }}</h1>
                <p class="text-sm text-gray-500">Tingkat {{ $kelas->tingkatan }} &bull; Wali Kelas, Siswa Terdaftar, dan Jadwal Pelajaran.</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('kelas.edit', $kelas->id) }}" class="px-4 py-2 bg-amber-50 hover:bg-amber-100 text-amber-700 text-sm font-semibold rounded-xl border border-amber-200 transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Edit Kelas</span>
            </a>
        </div>
    </div>

    <!-- Summary Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <!-- Rombel Info -->
        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Rombongan Belajar</p>
            <h3 class="text-2xl font-extrabold text-gray-900">{{ $kelas->nama_kelas }}</h3>
            <span class="inline-block mt-2 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                Jenjang Tingkat {{ $kelas->tingkatan }}
            </span>
        </div>

        <!-- Wali Kelas Info -->
        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Guru Wali Kelas</p>
            @if($kelas->guru)
                <h4 class="text-base font-bold text-gray-900 leading-snug">{{ $kelas->guru->nama }}</h4>
                <p class="text-xs text-gray-500 font-mono mt-0.5">NIP: {{ $kelas->guru->nip }}</p>
                <a href="{{ route('guru.show', $kelas->guru->id) }}" class="text-xs text-indigo-600 font-semibold hover:underline mt-2 inline-block">
                    Lihat Profil Guru &rarr;
                </a>
            @else
                <p class="text-sm text-gray-400 italic mt-1">Belum ada wali kelas</p>
            @endif
        </div>

        <!-- Total Siswa -->
        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Jumlah Siswa</p>
            <h3 class="text-2xl font-extrabold text-emerald-600">{{ $kelas->siswas->count() }} Siswa</h3>
            <p class="text-xs text-gray-500 mt-1">Terdaftar aktif di kelas ini</p>
        </div>
    </div>

    <!-- Foto Jadwal Pelajaran Kelas -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-gray-900">Foto Jadwal Pelajaran Kelas {{ $kelas->nama_kelas }}</h3>
                <p class="text-xs text-gray-500">Jadwal ini ditampilkan untuk semua siswa yang tergabung di kelas ini.</p>
            </div>
            <a href="{{ route('admin.jadwal.index', ['tab' => 'kelas']) }}" class="text-xs font-semibold text-amber-600 hover:underline">
                Kelola / Ganti Foto di Jadwal &rarr;
            </a>
        </div>

        @if($kelas->jadwal_url)
            <div class="rounded-2xl overflow-hidden border border-gray-200 bg-gray-50 p-3 text-center max-w-xl mx-auto">
                <img 
                    src="{{ $kelas->jadwal_url }}" 
                    alt="Jadwal {{ $kelas->nama_kelas }}" 
                    class="max-h-80 w-auto mx-auto rounded-xl object-contain shadow-sm cursor-pointer hover:opacity-95 transition-opacity"
                    @click="modalOpen = true; modalImg = '{{ $kelas->jadwal_url }}'"
                >
                <p class="text-xs text-gray-400 mt-2">Klik foto untuk melihat dalam ukuran penuh</p>
            </div>
        @else
            <div class="p-8 text-center rounded-2xl border border-dashed border-gray-200 bg-gray-50/50">
                <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <p class="text-sm font-semibold text-gray-600">Belum ada foto jadwal yang diunggah untuk kelas ini</p>
                <p class="text-xs text-gray-400 mt-0.5">Admin dapat mengunggah foto jadwal kelas di menu Kelola Jadwal.</p>
            </div>
        @endif
    </div>

    <!-- Daftar Siswa Terdaftar di Kelas Ini -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-gray-900">Daftar Siswa di Kelas Ini</h3>
                <p class="text-xs text-gray-500">Seluruh siswa yang terdaftar dalam rombel {{ $kelas->nama_kelas }}.</p>
            </div>
            <a href="{{ route('siswa.create') }}" class="text-xs font-semibold text-emerald-600 hover:underline">
                + Tambah Siswa Baru
            </a>
        </div>

        @if($kelas->siswas->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-[11px] font-bold uppercase text-gray-500">
                            <th class="py-2.5 px-4">Nama Siswa</th>
                            <th class="py-2.5 px-4">NIS</th>
                            <th class="py-2.5 px-4">L/P</th>
                            <th class="py-2.5 px-4">Wali Murid</th>
                            <th class="py-2.5 px-4">Kontak Wali</th>
                            <th class="py-2.5 px-4 text-right pr-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($kelas->siswas as $s)
                            <tr class="hover:bg-gray-50/50">
                                <td class="py-3 px-4 font-bold text-gray-800">{{ $s->nama_siswa }}</td>
                                <td class="py-3 px-4 font-mono text-xs text-gray-500">{{ $s->nis }}</td>
                                <td class="py-3 px-4 text-xs font-semibold">{{ $s->jenis_kelamin }}</td>
                                <td class="py-3 px-4 text-xs text-gray-700">{{ $s->wali_murid }}</td>
                                <td class="py-3 px-4 text-xs font-mono text-gray-600">{{ $s->nohp_wali }}</td>
                                <td class="py-3 px-4 text-right pr-4">
                                    <a href="{{ route('siswa.show', $s->id) }}" class="text-xs font-semibold text-indigo-600 hover:underline">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-sm text-gray-400 italic py-4 text-center">Belum ada siswa yang dimasukkan ke dalam kelas ini.</p>
        @endif
    </div>

    <!-- Modal Zoom Foto Interaktif -->
    <div 
        x-show="modalOpen" 
        style="display: none;"
        class="fixed inset-0 z-50 bg-gray-900/80 backdrop-blur-sm flex items-center justify-center p-4"
        @click.self="modalOpen = false"
    >
        <div class="relative max-w-4xl w-full bg-white rounded-2xl shadow-2xl overflow-hidden p-2">
            <div class="flex items-center justify-between p-3 border-b border-gray-100">
                <h4 class="font-bold text-gray-800 text-sm">Foto Jadwal Pelajaran {{ $kelas->nama_kelas }}</h4>
                <button @click="modalOpen = false" class="p-1 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-4 text-center max-h-[80vh] overflow-auto">
                <img :src="modalImg" alt="Jadwal Zoom" class="mx-auto max-h-[70vh] object-contain rounded-lg">
            </div>
        </div>
    </div>

</div>
@endsection
