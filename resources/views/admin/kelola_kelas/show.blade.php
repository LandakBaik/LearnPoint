@extends('layouts.app')

@section('title', 'Detail Kelas: ' . $kelas->nama_kelas)

@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="{ modalOpen: false, modalImg: '', assignModalOpen: false }">

    <!-- Header Navigation & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('kelas.index') }}" class="p-2.5 rounded-xl bg-white border border-slate-200/80 text-slate-500 hover:text-slate-900 hover:bg-slate-50 transition-colors shadow-2xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Kelas {{ $kelas->nama_kelas }}</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">
                        Tingkat {{ $kelas->tingkatan }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Informasi kelas, guru wali kelas, pengampu mata pelajaran, dan daftar siswa.</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button 
                @click="assignModalOpen = true" 
                type="button" 
                class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-500/20 transition-all hover:-translate-y-0.5"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ Guru Pengampu</span>
            </button>
            <a href="{{ route('kelas.edit', $kelas->id) }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl border border-slate-200/80 shadow-2xs transition-colors">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Edit Kelas</span>
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50/80 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-3 shadow-2xs">
            <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <span class="font-bold">{{ session('success') }}</span>
        </div>
    @endif
    @if(isset($errors) && $errors->any())
        <div class="p-4 rounded-2xl bg-rose-50/80 border border-rose-200 text-rose-800 text-xs shadow-2xs">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- 3 Highlight Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Wali Kelas Info -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Guru Wali Kelas</span>
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
            </div>
            @if($kelas->guru)
                <h4 class="text-base font-bold text-slate-900 leading-snug">{{ $kelas->guru->nama }}</h4>
                <p class="text-xs text-slate-500 mt-0.5">NIP: {{ $kelas->guru->nip }}</p>
                <a href="{{ route('guru.show', $kelas->guru->id) }}" class="text-xs text-blue-600 font-semibold hover:underline mt-2 inline-flex items-center gap-1">
                    <span>Lihat Profil Wali</span>
                    <span>&rarr;</span>
                </a>
            @else
                <p class="text-xs text-slate-400 italic mt-1">Belum ada wali kelas</p>
                <a href="{{ route('kelas.edit', $kelas->id) }}" class="text-xs text-blue-600 font-semibold hover:underline mt-2 inline-block">
                    + Tentukan Wali Kelas
                </a>
            @endif
        </div>

        <!-- Total Siswa -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Jumlah Siswa</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ $kelas->siswas->count() }}</div>
            <div class="text-[11px] font-medium text-slate-500 mt-1">Siswa terdaftar aktif</div>
        </div>

        <!-- Guru Pengampu -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Mata Pelajaran Diajar</span>
                <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ $kelas->pengampuKelases->count() }}</div>
            <div class="text-[11px] font-medium text-slate-500 mt-1">Alokasi guru pengampu</div>
        </div>
    </div>

    <!-- Guru Pengampu Mata Pelajaran di Kelas Ini -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5 sm:p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="text-base font-bold text-slate-900">Daftar Guru Pengampu Mata Pelajaran</h3>
                <p class="text-xs text-slate-500">Mata pelajaran yang diajarkan pada kelas {{ $kelas->nama_kelas }} beserta guru pengampunya.</p>
            </div>
            <button 
                @click="assignModalOpen = true" 
                type="button" 
                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold rounded-xl border border-blue-100/80 transition-colors shrink-0"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tentukan Guru Pengampu</span>
            </button>
        </div>

        @if($kelas->pengampuKelases->count() > 0)
            <div class="overflow-x-auto rounded-xl border border-slate-200/80">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase text-slate-500">
                            <th class="py-3 px-4">Mata Pelajaran</th>
                            <th class="py-3 px-4">Standar KKM</th>
                            <th class="py-3 px-4">Guru Pengampu</th>
                            <th class="py-3 px-4">NIP</th>
                            <th class="py-3 px-4 text-right pr-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($kelas->pengampuKelases as $gm)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3.5 px-4 font-bold text-slate-900">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-800">{{ $gm->mapel->nama_mapel ?? '-' }}</p>
                                            @if($gm->mapel)
                                                <a href="{{ route('mapel.show', $gm->mapel->id) }}" class="text-[11px] text-blue-600 hover:underline">Detail Mapel &rarr;</a>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 font-bold border border-amber-200/80 text-[11px]">
                                        KKM: {{ $gm->mapel->kkm ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-bold text-slate-800">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-slate-100 text-slate-700 font-bold text-[10px] flex items-center justify-center shrink-0 border border-slate-200">
                                            {{ strtoupper(substr($gm->guru->nama ?? 'G', 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-slate-800">{{ $gm->guru->nama ?? 'Guru Tidak Ditemukan' }}</p>
                                            @if($gm->guru)
                                                <a href="{{ route('guru.show', $gm->guru->id) }}" class="text-[10px] text-blue-600 hover:underline">Lihat Profil Guru</a>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-xs text-slate-500">
                                    {{ $gm->guru->nip ?? '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-right pr-4 whitespace-nowrap">
                                    <form action="{{ route('guru-mapel.destroy', $gm->id) }}" method="POST" data-confirm="Hapus penugasan guru {{ $gm->guru->nama ?? '' }} untuk mapel ini di kelas {{ $kelas->nama_kelas }}?" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus Pengampu">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-6 text-center rounded-2xl border border-dashed border-slate-200 bg-slate-50/50">
                <p class="text-xs font-bold text-slate-600">Belum ada guru pengampu yang ditugaskan untuk kelas ini</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Gunakan tombol "+ Tentukan Guru Pengampu" untuk menautkan mata pelajaran ke guru.</p>
            </div>
        @endif
    </div>

    <!-- Foto Jadwal Pelajaran Kelas -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5 sm:p-6 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900">Foto Jadwal Pelajaran</h3>
                <p class="text-xs text-slate-500">Jadwal resmi kelas {{ $kelas->nama_kelas }} yang ditampilkan pada portal siswa.</p>
            </div>
            <a href="{{ route('admin.jadwal.index', ['tab' => 'kelas']) }}" class="text-xs font-semibold text-blue-600 hover:underline">
                Kelola di Menu Jadwal &rarr;
            </a>
        </div>

        @if($kelas->jadwal_url)
            <div class="rounded-2xl overflow-hidden border border-slate-200/80 bg-slate-50/50 p-4 text-center max-w-xl mx-auto">
                <img 
                    src="{{ $kelas->jadwal_url }}" 
                    alt="Jadwal {{ $kelas->nama_kelas }}" 
                    class="max-h-80 w-auto mx-auto rounded-xl object-contain shadow-2xs cursor-pointer hover:opacity-95 transition-opacity"
                    @click="modalOpen = true; modalImg = '{{ $kelas->jadwal_url }}'"
                >
                <p class="text-[11px] text-slate-400 mt-2">Klik gambar untuk melihat dalam ukuran resolusi penuh.</p>
            </div>
        @else
            <div class="p-8 text-center rounded-2xl border border-dashed border-slate-200 bg-slate-50/50">
                <svg class="w-10 h-10 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <p class="text-xs font-bold text-slate-600">Belum ada foto jadwal yang diunggah untuk kelas ini</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Admin dapat mengunggah jadwal kelas di menu Kelola Jadwal.</p>
            </div>
        @endif
    </div>

    <!-- Daftar Siswa Terdaftar di Kelas Ini -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5 sm:p-6 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900">Daftar Siswa di Kelas Ini</h3>
                <p class="text-xs text-slate-500">Seluruh siswa yang terdaftar aktif dalam kelas {{ $kelas->nama_kelas }}.</p>
            </div>
            <a href="{{ route('siswa.create') }}" class="text-xs font-bold text-blue-600 hover:underline">
                + Tambah Siswa Baru
            </a>
        </div>

        @if($kelas->siswas->count() > 0)
            <div class="overflow-x-auto rounded-xl border border-slate-200/80">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase text-slate-500">
                            <th class="py-3 px-4">Nama Siswa</th>
                            <th class="py-3 px-4">NIS</th>
                            <th class="py-3 px-4">L/P</th>
                            <th class="py-3 px-4">Wali Murid</th>
                            <th class="py-3 px-4">Kontak Wali</th>
                            <th class="py-3 px-4 text-right pr-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($kelas->siswas as $s)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3 px-4 font-bold text-slate-900">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-lg bg-emerald-50 text-emerald-700 font-bold text-[10px] flex items-center justify-center shrink-0 border border-emerald-100">
                                            {{ strtoupper(substr($s->nama_siswa, 0, 1)) }}
                                        </div>
                                        <span>{{ $s->nama_siswa }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-slate-500">{{ $s->nis }}</td>
                                <td class="py-3 px-4 font-semibold text-slate-700">{{ $s->jenis_kelamin }}</td>
                                <td class="py-3 px-4 text-slate-700">{{ $s->wali_murid }}</td>
                                <td class="py-3 px-4 text-slate-600">{{ $s->nohp_wali }}</td>
                                <td class="py-3 px-4 text-right pr-4">
                                    <a href="{{ route('siswa.show', $s->id) }}" class="text-xs font-bold text-blue-600 hover:underline">
                                        Detail &rarr;
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-xs text-slate-400 italic py-6 text-center">Belum ada siswa yang dimasukkan ke dalam kelas ini.</p>
        @endif
    </div>

    <!-- Modal Zoom Foto Interaktif -->
    <div 
        x-show="modalOpen" 
        style="display: none;"
        class="fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-xs flex items-center justify-center p-4"
        @click.self="modalOpen = false"
    >
        <div class="relative max-w-4xl w-full bg-white rounded-3xl shadow-2xl overflow-hidden p-3 border border-slate-200">
            <div class="flex items-center justify-between p-3 border-b border-slate-100">
                <h4 class="font-bold text-slate-900 text-sm">Foto Jadwal Pelajaran {{ $kelas->nama_kelas }}</h4>
                <button @click="modalOpen = false" class="p-1.5 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-4 text-center max-h-[80vh] overflow-auto">
                <img :src="modalImg" alt="Jadwal Zoom" class="mx-auto max-h-[70vh] object-contain rounded-2xl shadow-sm">
            </div>
        </div>
    </div>

    <!-- Modal Penugasan Guru Mapel -->
    <div 
        x-show="assignModalOpen" 
        class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;"
    >
        <div class="min-h-screen px-4 text-center flex items-center justify-center">
            <!-- Backdrop -->
            <div 
                class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" 
                @click="assignModalOpen = false"
            ></div>

            <!-- Modal Content -->
            <div class="relative bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 text-left shadow-2xl border border-slate-100 z-10 space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-lg font-extrabold text-slate-900">Tentukan Guru Pengampu</h3>
                        <p class="text-xs text-slate-500">Pilih mata pelajaran dan guru pengajar untuk kelas {{ $kelas->nama_kelas }}.</p>
                    </div>
                    <button 
                        type="button" 
                        @click="assignModalOpen = false"
                        class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                @php
                    $assignedMapels = $kelas->pengampuKelases->mapWithKeys(function($pk) {
                        return [$pk->guruMapel?->mapel_id => [
                            'guru_nama' => $pk->guruMapel?->guru?->nama ?? 'Guru Lain',
                            'guru_id' => $pk->guruMapel?->guru_id,
                        ]];
                    });
                    $availableMapelsCount = $allMapels->filter(fn($m) => !$assignedMapels->has($m->id))->count();
                @endphp

                @if($availableMapelsCount === 0)
                    <div class="p-3.5 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-xs flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <p class="font-bold">Semua Mata Pelajaran Sudah Memiliki Guru</p>
                            <p class="text-[11px] text-amber-700 font-normal mt-0.5">Seluruh mata pelajaran di kurikulum sudah memiliki guru pengampu di kelas ini. Anda dapat menghapus pengampu pada tabel di bawah jika ingin mengganti guru.</p>
                        </div>
                    </div>
                @endif

                <form action="{{ route('guru-mapel.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">

                    <!-- Pilih Mapel -->
                    <div>
                        <label for="modal_mapel_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Pilih Mata Pelajaran <span class="text-rose-500">*</span>
                        </label>
                        <select 
                            id="modal_mapel_id" 
                            name="mapel_id" 
                            required 
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-semibold text-slate-800 transition-all"
                        >
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            @foreach($allMapels as $mapel)
                                @php $assigned = $assignedMapels->get($mapel->id); @endphp
                                @if($assigned)
                                    <option value="{{ $mapel->id }}" disabled class="text-slate-400 bg-slate-100 italic">
                                        {{ $mapel->nama_mapel }} — [Sudah Diampu: {{ $assigned['guru_nama'] }}] 🔒
                                    </option>
                                @else
                                    <option value="{{ $mapel->id }}">
                                        {{ $mapel->nama_mapel }} (KKM: {{ $mapel->kkm }})
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-400 mt-1">Satu mata pelajaran hanya dapat diampu oleh 1 guru dalam kelas ini.</p>
                    </div>

                    <!-- Pilih Guru -->
                    <div>
                        <label for="modal_guru_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Pilih Guru Pengajar <span class="text-rose-500">*</span>
                        </label>
                        <select 
                            id="modal_guru_id" 
                            name="guru_id" 
                            required 
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-semibold text-slate-800 transition-all"
                        >
                            <option value="">-- Pilih Guru --</option>
                            @foreach($allGurus as $guru)
                                <option value="{{ $guru->id }}">{{ $guru->nama }} (NIP: {{ $guru->nip }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="pt-3 flex items-center justify-end gap-3">
                        <button 
                            type="button" 
                            @click="assignModalOpen = false"
                            class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            @if($availableMapelsCount === 0) disabled @endif
                            class="px-5 py-2.5 rounded-xl {{ $availableMapelsCount === 0 ? 'bg-slate-200 cursor-not-allowed text-slate-400' : 'bg-blue-600 hover:bg-blue-700 text-white shadow-md shadow-blue-500/20 hover:-translate-y-0.5' }} text-xs font-bold transition-all"
                        >
                            Simpan Pengampu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection