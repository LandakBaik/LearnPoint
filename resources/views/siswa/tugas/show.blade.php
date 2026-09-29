@extends('layouts.app')

@section('title', 'Detail Tugas - ' . $tugas->judul)

@section('content')
<div class="max-w-4xl mx-auto p-4 sm:p-6 space-y-6">

    <!-- Header & Tombol Kembali -->
    <div class="flex items-center gap-3">
        <a href="{{ route('siswa.tugas') }}" class="p-2.5 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition">
            <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-slate-900">{{ $tugas->judul }}</h1>
            <p class="text-xs text-slate-500">{{ $tugas->guruMapel->mapel->nama_mapel ?? 'Mata Pelajaran' }}</p>
        </div>
    </div>

    <!-- Alert Success / Error -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-xs font-semibold">
            {{ session('success') }}
        </div>
    @endif

    <!-- Card 1: Informasi Tugas dari Guru (READ) -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 pb-4">
            <span class="px-3 py-1 bg-blue-50 text-blue-600 text-xs font-semibold rounded-full">
                Deadline: {{ \Carbon\Carbon::parse($tugas->deadline)->format('d M Y, H:i') }}
            </span>
            
            @if($tugas->file_lampiran)
                <a href="{{ asset('storage/' . $tugas->file_lampiran) }}" download class="text-xs text-blue-600 font-semibold hover:underline flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Unduh Lampiran Guru
                </a>
            @endif
        </div>

        <div>
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Deskripsi Tugas</h2>
            <p class="text-sm text-slate-700 leading-relaxed">{{ $tugas->deskripsi ?? 'Tidak ada deskripsi tugas.' }}</p>
        </div>
    </div>

    <!-- Card 2: Pengumpulan & Nilai Siswa (CREATE, READ, UPDATE, DELETE) -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 space-y-5">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-800">Jawaban Saya</h2>
            @if($pengumpulan)
                <span class="px-3 py-1 text-xs font-bold rounded-full {{ $pengumpulan->status === 'selesai' ? 'bg-indigo-50 text-indigo-600' : 'bg-emerald-50 text-emerald-600' }}">
                    {{ $pengumpulan->status === 'selesai' ? 'Sudah Dinilai' : 'Sudah Dikumpulkan' }}
                </span>
            @else
                <span class="px-3 py-1 text-xs font-bold bg-amber-50 text-amber-600 rounded-full">
                    Belum Mengumpulkan
                </span>
            @endif
        </div>

        <!-- Jika Sudah Mengumpulkan (READ, UPDATE, DELETE) -->
        @if($pengumpulan)
            <div class="p-4 bg-slate-50 rounded-xl space-y-3 border border-slate-200">
                <div class="flex justify-between items-center text-xs text-slate-500">
                    <span>Dikumpulkan pada: <strong>{{ \Carbon\Carbon::parse($pengumpulan->updated_at)->format('d M Y, H:i') }}</strong></span>
                </div>

                @if($pengumpulan->status === 'selesai')
                    <div class="p-3 bg-white rounded-lg border border-slate-200 flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-600">Nilai dari Guru:</span>
                        <span class="text-xl font-black text-indigo-600">{{ $pengumpulan->nilai }} / 100</span>
                    </div>
                @endif

                <div class="flex items-center justify-between pt-2">
                    <a href="{{ asset('storage/' . $pengumpulan->file_jawaban) }}" target="_blank" class="text-xs text-blue-600 font-semibold underline flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        Lihat Berkas Terunggah
                    </a>

                    <!-- Tombol DELETE (Batalkan Pengumpulan) -->
                    @if($pengumpulan->status !== 'selesai')
                        <form action="{{ route('siswa.tugas.batalkan', $tugas->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pengumpulan ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-rose-600 font-semibold hover:underline">
                                Batalkan Pengumpulan
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endif

        <!-- Form UPLOAD / GANTI FILE (CREATE & UPDATE) -->
        @if(!$pengumpulan || $pengumpulan->status !== 'selesai')
            <form action="{{ route('siswa.tugas.kumpulkan', $tugas->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4 pt-2">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                        {{ $pengumpulan ? 'Ganti File Jawaban (Revisi)' : 'Upload Berkas Jawaban' }}
                    </label>
                    <input 
                        type="file" 
                        name="file_jawaban" 
                        required 
                        accept=".pdf,.doc,.docx,.zip,.rar" 
                        class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100 border border-slate-200 rounded-xl p-1 cursor-pointer"
                    >
                    <p class="text-[10px] text-slate-400 mt-1">Format: PDF, DOC, DOCX, ZIP, RAR (Maksimal 10MB)</p>
                </div>

                <button type="submit" class="px-5 py-2.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition shadow-sm">
                    {{ $pengumpulan ? 'Simpan Perubahan (Revisi)' : 'Kumpulkan Tugas' }}
                </button>
            </form>
        @else
            <p class="text-xs text-slate-400 italic">Tugas ini sudah dinilai oleh guru dan jawaban tidak dapat diubah lagi.</p>
        @endif
    </div>

</div>
@endsection