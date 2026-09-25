@extends('layouts.app')
@section('title', 'Dashboard Siswa')

@section('content')
<div class="space-y-5">
    {{-- Welcome Header Banner --}}
    <div class="bg-gradient-to-br from-blue-700 via-blue-800 to-indigo-900 rounded-2xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        {{-- Decorative circles --}}
        <div class="absolute -right-8 -top-8 w-40 h-40 bg-white/5 rounded-full"></div>
        <div class="absolute right-16 -bottom-10 w-32 h-32 bg-white/5 rounded-full"></div>
        <div class="relative z-10">
            <span class="px-3 py-1 bg-white/20 rounded-full text-xs font-semibold uppercase tracking-wider">Halaman Siswa</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold mt-3 mb-2">
                Selamat Belajar, {{ auth()->user()->name }}! 🎓
            </h2>
            <p class="text-blue-200 text-sm sm:text-base max-w-2xl leading-relaxed">
                Pantau jadwal pelajaran, unduh materi belajar, kerjakan kuis, dan lihat pencapaian nilai kamu di sini.
            </p>
            @if(isset($siswa))
            <p class="mt-3 text-xs text-blue-300 font-medium">
                Kelas: <span class="font-bold text-white">{{ $siswa->kelas->nama_kelas ?? 'Belum ditentukan' }}</span>
                &nbsp;·&nbsp; NIS: <span class="font-bold text-white">{{ $siswa->nis ?? 'Belum diatur' }}</span>
            </p>
            @endif
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        {{-- Rata-rata Nilai Akademik --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Rata-rata Nilai Akademik</p>
                <div class="flex items-end gap-1">
                    <span class="text-4xl font-extrabold text-gray-900">88.6</span>
                    <span class="text-sm font-semibold text-gray-400 mb-1">/ A</span>
                </div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-yellow-50 border border-yellow-100 flex items-center justify-center text-yellow-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
            </div>
        </div>

        {{-- Mata Pelajaran --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Mata Pelajaran</p>
                <div class="flex items-end gap-1">
                    <span class="text-4xl font-extrabold text-gray-900">{{ $totalMapel ?? 0 }}</span>
                    <span class="text-sm font-semibold text-gray-400 mb-1">Mapel</span>
                </div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
            </div>
        </div>

        {{-- Kuis & Ujian Mendatang --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Kuis & Ujian Mendatang</p>
                <div class="flex items-end gap-1">
                    <span class="text-4xl font-extrabold text-gray-900">0</span>
                    <span class="text-sm font-semibold text-gray-400 mb-1">Agenda</span>
                </div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Prioritas Tugas --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-start gap-4 flex-1 min-w-0">
                <div class="mt-1 w-9 h-9 rounded-full border-2 border-amber-200 bg-amber-50 flex items-center justify-center flex-shrink-0 text-amber-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    @if(isset($prioritasTugas))
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <span class="px-2.5 py-0.5 bg-amber-100 text-amber-800 text-[10px] font-bold rounded-md uppercase tracking-wider">
                                🔥 Prioritas Tugas
                            </span>
                            <span class="text-xs text-gray-500 font-medium">{{ $prioritasTugas->mapel->nama_mapel ?? 'Mata Pelajaran' }}</span>
                            <span class="text-xs text-amber-600 font-semibold flex items-center gap-1">
                                ⏱ Tenggat: {{ \Carbon\Carbon::parse($prioritasTugas->deadline)->format('d M Y, H:i') }} WIB
                            </span>
                        </div>
                        <h5 class="text-base font-bold text-gray-900 leading-snug truncate">
                            {{ $prioritasTugas->judul }}
                        </h5>
                        <p class="text-xs text-gray-500 mt-0.5 line-clamp-1">{{ $prioritasTugas->deskripsi ?? 'Kerjakan tugas ini sebelum batas waktu berakhir.' }}</p>
                    @else
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs font-bold rounded-md uppercase tracking-wide">
                                Info Tugas
                            </span>
                        </div>
                        <h5 class="text-sm font-bold text-gray-900 leading-snug">
                            Belum ada tugas prioritas yang ditetapkan
                        </h5>
                        <p class="text-xs text-gray-400 mt-0.5">Tugas terbaru kamu akan muncul di sini.</p>
                    @endif
                </div>
            </div>

            <a href="{{ route('siswa.tugas') }}" class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl transition-colors text-center shrink-0">
                Kerjakan Sekarang →
            </a>
        </div>
    </div>

    {{-- Lanjutkan Belajar (Dynamic List Materi dari Guru) --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h4 class="text-base font-bold text-gray-900">Lanjutkan Belajar</h4>
                <p class="text-xs text-gray-400">Aktivitas materi terbaru dari guru kamu</p>
            </div>
            <a href="{{ route('siswa.materi') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700 transition-colors">
                Lihat Semua Materi →
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @forelse($materiList ?? [] as $materi)
                <div class="p-4 rounded-xl border border-gray-100 bg-slate-50 space-y-3 hover:border-blue-200 transition">
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-100 text-blue-700">
                            {{ $materi->mapel->nama_mapel ?? 'Umum' }}
                        </span>
                        <span class="text-xs text-gray-400 flex items-center gap-1">
                            Guru: {{ $materi->guru->nama ?? 'Guru' }}
                        </span>
                    </div>

                    <div>
                        <h5 class="text-sm font-bold text-gray-800 line-clamp-1">{{ $materi->judul }}</h5>
                        <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ $materi->deskripsi ?? 'Klik tombol di bawah untuk membaca/mengunduh materi ini.' }}</p>
                    </div>

                    <div class="pt-2 border-t border-gray-200/60 flex items-center justify-between">
                        <span class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($materi->created_at)->diffForHumans() }}</span>
                        <a href="{{ route('siswa.materi') }}" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg transition">
                            Mulai Belajar →
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-2 text-center py-8 text-gray-400 border border-dashed rounded-xl">
                    Belum ada materi pembelajaran yang diunggah oleh guru.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection