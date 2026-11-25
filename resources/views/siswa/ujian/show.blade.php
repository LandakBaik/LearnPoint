@extends('layouts.siswa')

@section('title', 'Detail Ujian: ' . ($ujian->judul ?? 'Ujian'))

@section('content')
<div class="max-w-7xl mx-auto py-6 px-4 space-y-6">

    <!-- Tombol Kembali & Judul Utama -->
    <div class="space-y-1">
        <a href="{{ url()->previous() != url()->current() ? url()->previous() : route('siswa.ujian.index') }}"
           class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-indigo-600 transition">
            &larr; Kembali
        </a>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ $ujian->judul }}</h1>
        <p class="text-sm text-slate-400 font-medium">Detail informasi pelaksanaan ujian dan kisi-kisi evaluasi.</p>
    </div>

    <!-- 4 Baris Kartu Status Singkat -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <!-- Status -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs">
            <span class="block text-xs font-medium text-slate-400">Status</span>
            <div class="mt-2">
                @if($ujian->status === 'scheduled')
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-600 border border-amber-200">
                        Terjadwal
                    </span>
                @elseif($ujian->status === 'published')
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-600 border border-blue-200">
                        Sedang Berlangsung
                    </span>
                @elseif($ujian->status === 'completed')
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">
                        Selesai
                    </span>
                @endif
            </div>
        </div>

        <!-- Mata Pelajaran -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs">
            <span class="block text-xs font-medium text-slate-400">Mata Pelajaran</span>
            <span class="text-base sm:text-lg font-bold text-slate-900 mt-1 block truncate">
                {{ $ujian->pengampuKelas->guruMapel->mapel->nama_mapel ?? 'Matematika' }}
            </span>
        </div>

        <!-- Kelas -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs">
            <span class="block text-xs font-medium text-slate-400">Kelas</span>
            <span class="text-base sm:text-lg font-bold text-slate-900 mt-1 block">
                {{ $ujian->pengampuKelas->kelas->nama_kelas ?? 'Semua Kelas' }}
            </span>
        </div>

        <!-- Jumlah Soal -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs">
            <span class="block text-xs font-medium text-slate-400">Jumlah Soal</span>
            <span class="text-xl sm:text-2xl font-black text-slate-900 mt-0.5 block">
                {{ $ujian->soals->count() }}
            </span>
        </div>
    </div>

    <!-- Informasi Ujian & Ringkasan Soal -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Kolom Kiri: Informasi Ujian (2 Kolom) -->
        <div class="lg:col-span-2 bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-6">
            <h2 class="text-lg font-bold text-slate-900 border-b border-slate-100 pb-3">Informasi Ujian</h2>

            <div class="grid grid-cols-2 gap-y-6 gap-x-4">
                <div>
                    <span class="block text-xs font-medium text-slate-400">Durasi</span>
                    <span class="text-base font-bold text-slate-800 mt-0.5 block">{{ $ujian->durasi_menit ?? 60 }} menit</span>
                </div>
                <div>
                    <span class="block text-xs font-medium text-slate-400">KKM</span>
                    <span class="text-base font-bold text-slate-800 mt-0.5 block">{{ number_format($ujian->kkm ?? 75, 2) }}</span>
                </div>
                <div>
                    <span class="block text-xs font-medium text-slate-400">Waktu Mulai</span>
                    <span class="text-sm font-semibold text-slate-800 mt-0.5 block">
                        {{ $ujian->waktu_mulai ? \Carbon\Carbon::parse($ujian->waktu_mulai)->isoFormat('DD MMM Y, HH:mm') : '-' }}
                    </span>
                </div>
                <div>
                    <span class="block text-xs font-medium text-slate-400">Deadline</span>
                    <span class="text-sm font-semibold text-slate-800 mt-0.5 block">
                        {{ $ujian->deadline ? \Carbon\Carbon::parse($ujian->deadline)->isoFormat('DD MMM Y, HH:mm') : '-' }}
                    </span>
                </div>
            </div>

            @if($ujian->deskripsi)
                <div class="pt-4 border-t border-slate-100">
                    <span class="block text-xs font-medium text-slate-400 mb-1">Deskripsi Tambahan</span>
                    <p class="text-sm text-slate-700 leading-relaxed">{{ $ujian->deskripsi }}</p>
                </div>
            @endif
        </div>

        <!-- Kolom Kanan: Ringkasan Soal (1 Kolom) -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-4 h-fit">
            <h2 class="text-lg font-bold text-slate-900 border-b border-slate-100 pb-3">Ringkasan Soal</h2>

            <div class="space-y-3 text-sm">
                <div class="flex justify-between items-center text-slate-600">
                    <span>Pilihan Tunggal</span>
                    <span class="font-bold text-slate-800">{{ $ringkasanTipe['single_choice'] }}</span>
                </div>
                <div class="flex justify-between items-center text-slate-600">
                    <span>Pilihan Ganda</span>
                    <span class="font-bold text-slate-800">{{ $ringkasanTipe['multiple_choice'] }}</span>
                </div>
                <div class="flex justify-between items-center text-slate-600">
                    <span>Essay</span>
                    <span class="font-bold text-slate-800">{{ $ringkasanTipe['essay'] }}</span>
                </div>
                <div class="flex justify-between items-center text-slate-600">
                    <span>Menjodohkan</span>
                    <span class="font-bold text-slate-800">{{ $ringkasanTipe['matching'] }}</span>
                </div>

                <div class="pt-3 border-t border-slate-100 flex justify-between items-center">
                    <span class="font-bold text-slate-900">Total Poin</span>
                    <span class="font-black text-indigo-600 text-base">{{ $ringkasanTipe['total_poin'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Kisi-Kisi Ujian -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-4">
        <h2 class="text-lg font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
            <span>📝</span>
            <span>Kisi-kisi Ujian</span>
        </h2>

        @if(!empty($ujian->kisi_kisi))
            <div class="prose max-w-none text-slate-700 text-sm leading-relaxed whitespace-pre-line bg-slate-50/60 p-5 rounded-2xl border border-slate-100">
                {{ $ujian->kisi_kisi }}
            </div>
        @else
            <div class="p-6 text-center text-slate-400 bg-slate-50/50 rounded-2xl border border-dashed border-slate-200 text-xs">
                Belum ada kisi-kisi khusus yang dilampirkan guru untuk ujian ini.
            </div>
        @endif
    </div>

    <!-- Tombol Aksi di Bagian Bawah -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            @if($sudahDikerjakan)
                <p class="text-sm font-bold text-emerald-600 flex items-center gap-1.5">
                    ✓ Kamu sudah menyelesaikan ujian ini dengan skor: {{ $nilai->nilai }}
                </p>
            @elseif($ujian->status === 'scheduled')
                <p class="text-sm font-semibold text-amber-700 flex items-center gap-1.5">
                    ⏱ Ujian belum dimulai. Silakan pelajari materi dan kisi-kisi terlebih dahulu.
                </p>
            @else
                <p class="text-sm font-semibold text-slate-600">
                    Ujian telah dibuka. Pastikan koneksi internet stabil sebelum menekan tombol mulai.
                </p>
            @endif
        </div>

        <div>
            @if($sudahDikerjakan)
                <a href="{{ route('siswa.ujian.hasil', $ujian->id) }}"
                   class="px-6 py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 border border-indigo-200 rounded-xl text-xs font-bold transition">
                    Lihat Hasil Nilai
                </a>
            @elseif($ujian->status === 'scheduled')
                <button type="button" disabled
                        class="px-6 py-2.5 bg-slate-100 text-slate-400 border border-slate-200 rounded-xl text-xs font-bold cursor-not-allowed">
                    Belum Dapat Dikerjakan
                </button>
            @else
                <a href="{{ route('siswa.ujian.kerjakan', $ujian->id) }}"
                   class="px-7 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center gap-2">
                    <span>Mulai Kerjakan Sekarang</span>
                    <span>&rarr;</span>
                </a>
            @endif
        </div>
    </div>

</div>
@endsection
