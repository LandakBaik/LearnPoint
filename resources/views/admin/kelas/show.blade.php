@extends('layouts.app')

@section('title', 'Detail Kelas: ' . $kelas->nama_kelas)

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{ modalOpen: false, modalImg: '', assignModalOpen: false }">

    <!-- Header Navigation -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('kelas.index') }}" class="p-2 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Detail Kelas {{ $kelas->nama_kelas }}</h1>
                <p class="text-sm text-gray-500">Tingkat {{ $kelas->tingkatan }} &bull; Wali Kelas, Guru Pengampu, dan Siswa Terdaftar.</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button 
                @click="assignModalOpen = true" 
                type="button" 
                class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-rose-200 transition-all flex items-center gap-1.5"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Guru Pengampu</span>
            </button>
            <a href="{{ route('kelas.edit', $kelas->id) }}" class="px-4 py-2 bg-amber-50 hover:bg-amber-100 text-amber-700 text-sm font-semibold rounded-xl border border-amber-200 transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Edit Kelas</span>
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Summary Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <!-- Kelas Info -->
        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Nama Kelas</p>
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

    <!-- Guru Pengampu Mata Pelajaran di Kelas Ini (Poin 7 & 9) -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="text-base font-bold text-gray-900">Daftar Guru Pengampu Mata Pelajaran di Kelas {{ $kelas->nama_kelas }}</h3>
                <p class="text-xs text-gray-500">Mata pelajaran yang diajarkan pada kelas ini beserta guru pengampunya.</p>
            </div>
            <button 
                @click="assignModalOpen = true" 
                type="button" 
                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold rounded-xl transition-colors shrink-0"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tentukan Guru Pengampu</span>
            </button>
        </div>

        @if($kelas->guruMapels->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-[11px] font-bold uppercase text-gray-500">
                            <th class="py-3 px-4">Mata Pelajaran</th>
                            <th class="py-3 px-4">KKM</th>
                            <th class="py-3 px-4">Guru Pengampu</th>
                            <th class="py-3 px-4">NIP</th>
                            <th class="py-3 px-4 text-right pr-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($kelas->guruMapels as $gm)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="py-3.5 px-4 font-bold text-gray-900">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center font-bold text-xs shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-800">{{ $gm->mapel->nama_mapel ?? '-' }}</p>
                                            @if($gm->mapel)
                                                <a href="{{ route('mapel.show', $gm->mapel->id) }}" class="text-[11px] text-rose-600 hover:underline">Detail Mapel &rarr;</a>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-amber-700 text-xs">
                                    {{ $gm->mapel->kkm ?? '-' }}
                                </td>
                                <td class="py-3.5 px-4 font-bold text-gray-800">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 font-bold text-[10px] flex items-center justify-center shrink-0">
                                            {{ strtoupper(substr($gm->guru->nama ?? 'G', 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-gray-800">{{ $gm->guru->nama ?? 'Guru Tidak Ditemukan' }}</p>
                                            @if($gm->guru)
                                                <a href="{{ route('guru.show', $gm->guru->id) }}" class="text-[10px] text-indigo-600 hover:underline">Lihat Profil Guru</a>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-xs text-gray-500">
                                    {{ $gm->guru->nip ?? '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-right pr-4 whitespace-nowrap">
                                    <form action="{{ route('guru-mapel.destroy', $gm->id) }}" method="POST" data-confirm="Hapus penugasan guru {{ $gm->guru->nama ?? '' }} untuk mapel ini di kelas {{ $kelas->nama_kelas }}?" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus Pengampu">
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
            <div class="p-6 text-center rounded-xl border border-dashed border-gray-200 bg-gray-50/50">
                <p class="text-sm font-semibold text-gray-600">Belum ada guru pengampu mapel yang ditugaskan untuk kelas ini</p>
                <p class="text-xs text-gray-400 mt-1">Gunakan tombol "+ Tentukan Guru Pengampu" untuk memilih guru dan mata pelajaran.</p>
            </div>
        @endif
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
                <p class="text-xs text-gray-500">Seluruh siswa yang terdaftar dalam kelas {{ $kelas->nama_kelas }}.</p>
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

    <!-- Modal Penugasan Guru Mapel (Poin 7 & 9) -->
    <div 
        x-show="assignModalOpen" 
        class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;"
    >
        <div class="min-h-screen px-4 text-center flex items-center justify-center">
            <!-- Backdrop -->
            <div 
                class="fixed inset-0 bg-gray-900/60 transition-opacity" 
                @click="assignModalOpen = false"
            ></div>

            <!-- Modal Content -->
            <div class="relative bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 text-left shadow-2xl border border-gray-100 z-10 space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div>
                        <h3 class="text-lg font-extrabold text-gray-900">Tentukan Guru Pengampu</h3>
                        <p class="text-xs text-gray-500">Pilih mata pelajaran dan guru pengajar untuk kelas {{ $kelas->nama_kelas }}.</p>
                    </div>
                    <button 
                        type="button" 
                        @click="assignModalOpen = false"
                        class="p-2 text-gray-400 hover:text-gray-700 rounded-xl hover:bg-gray-100"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('guru-mapel.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">

                    <!-- Pilih Mapel -->
                    <div>
                        <label for="modal_mapel_id" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">
                            Pilih Mata Pelajaran <span class="text-rose-500">*</span>
                        </label>
                        <select 
                            id="modal_mapel_id" 
                            name="mapel_id" 
                            required 
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 text-sm focus:outline-none focus:ring-2 focus:ring-rose-500 font-medium text-gray-800"
                        >
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            @foreach($allMapels as $mapel)
                                <option value="{{ $mapel->id }}">{{ $mapel->nama_mapel }} (KKM: {{ $mapel->kkm }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Pilih Guru -->
                    <div>
                        <label for="modal_guru_id" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">
                            Pilih Guru Pengajar <span class="text-rose-500">*</span>
                        </label>
                        <select 
                            id="modal_guru_id" 
                            name="guru_id" 
                            required 
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 text-sm focus:outline-none focus:ring-2 focus:ring-rose-500 font-medium text-gray-800"
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
                            class="px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold transition-colors"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold shadow-md shadow-rose-200 transition-all"
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
