@extends('layouts.siswa')

@section('title', 'Daftar Ujian - LearnPoint')

@section('content')
<div x-data="{ tabAktif: 'semua' }" class="max-w-7xl mx-auto py-6 px-4 space-y-6">

    <!-- Hero Banner Ujian -->
    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-indigo-500 via-indigo-600 to-blue-600 p-8 lg:p-10 text-white shadow-lg shadow-indigo-500/20">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="max-w-xl space-y-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-white/20 backdrop-blur-md text-white border border-white/20">
                    Kelas {{ $kelas->nama_kelas ?? 'Siswa' }}
                </span>
                <h1 class="text-3xl sm:text-4xl font-black tracking-tight">Evaluasi & Ujian Sekolah</h1>
                <p class="text-indigo-100 text-sm sm:text-base leading-relaxed">
                    Daftar ujian resmi sekolah. Pelajari kisi-kisi pada ujian yang terjadwal dan kerjakan ujian yang sedang berlangsung.
                </p>
            </div>

            <!-- Kartu Statistik Singkat -->
            <div class="flex items-center gap-3 bg-white/10 backdrop-blur-md p-3 rounded-2xl border border-white/20">
                <div class="px-4 py-2 text-center border-r border-white/20">
                    <span class="block text-2xl font-black">{{ $totalUjian ?? $ujians->count() }}</span>
                    <span class="text-xs text-indigo-100 font-medium">Total Ujian</span>
                </div>
                <div class="px-4 py-2 text-center border-r border-white/20">
                    <span class="block text-2xl font-black text-amber-300">{{ $totalBelum ?? $ujians->where('sudah_dikerjakan', false)->count() }}</span>
                    <span class="text-xs text-indigo-100 font-medium">Belum Selesai</span>
                </div>
                <div class="px-4 py-2 text-center">
                    <span class="block text-2xl font-black text-emerald-300">{{ $totalSelesai ?? $ujians->where('sudah_dikerjakan', true)->count() }}</span>
                    <span class="text-xs text-indigo-100 font-medium">Selesai</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Navigasi Tab Filter -->
    <nav class="bg-white rounded-2xl p-1.5 border border-slate-200/80 shadow-xs max-w-md">
        <div class="grid grid-cols-3 gap-1">
            <button type="button"
                    @click="tabAktif = 'semua'"
                    :class="tabAktif === 'semua' ? 'bg-indigo-50 text-indigo-600 font-bold border border-indigo-200 shadow-xs' : 'text-slate-600 font-semibold hover:text-slate-900'"
                    class="py-2.5 px-3 rounded-xl text-xs transition text-center">
                Semua ({{ $totalUjian ?? $ujians->count() }})
            </button>
            <button type="button"
                    @click="tabAktif = 'belum'"
                    :class="tabAktif === 'belum' ? 'bg-indigo-50 text-indigo-600 font-bold border border-indigo-200 shadow-xs' : 'text-slate-600 font-semibold hover:text-slate-900'"
                    class="py-2.5 px-3 rounded-xl text-xs transition text-center">
                Belum ({{ $totalBelum ?? $ujians->where('sudah_dikerjakan', false)->count() }})
            </button>
            <button type="button"
                    @click="tabAktif = 'selesai'"
                    :class="tabAktif === 'selesai' ? 'bg-indigo-50 text-indigo-600 font-bold border border-indigo-200 shadow-xs' : 'text-slate-600 font-semibold hover:text-slate-900'"
                    class="py-2.5 px-3 rounded-xl text-xs transition text-center">
                Selesai ({{ $totalSelesai ?? $ujians->where('sudah_dikerjakan', true)->count() }})
            </button>
        </div>
    </nav>

    <!-- Daftar Kartu Ujian -->
    <div class="space-y-4">
        @forelse($ujians as $ujian)
            <div x-show="tabAktif === 'semua' || (tabAktif === 'selesai' && {{ $ujian->sudah_dikerjakan ? 'true' : 'false' }}) || (tabAktif === 'belum' && {{ !$ujian->sudah_dikerjakan ? 'true' : 'false' }})"
                 class="group bg-white rounded-3xl p-6 border border-slate-200/80 hover:border-indigo-300 shadow-xs hover:shadow-md transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-5">

                <!-- Info Ujian -->
                <div class="flex items-start sm:items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 flex-shrink-0 group-hover:scale-105 transition-transform">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>

                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="px-3 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-600 border border-indigo-100">
                                {{ $ujian->pengampuKelas->guruMapel->mapel->nama_mapel ?? 'Mata Pelajaran' }}
                            </span>
                            <span class="text-xs text-slate-400 font-medium">
                                Guru: {{ $ujian->pengampuKelas->guruMapel->guru->nama_guru ?? 'Guru Pengampu' }}
                            </span>
                        </div>

                        <h3 class="text-lg font-bold text-slate-900 group-hover:text-indigo-600 transition-colors mt-1.5">
                            {{ $ujian->judul }}
                        </h3>

                        <div class="flex items-center gap-3 mt-1.5 text-xs text-slate-400 font-medium flex-wrap">
                            <span class="flex items-center gap-1">
                                ⏱ {{ $ujian->durasi_menit ?? 60 }} Menit
                            </span>
                            <span>&bull;</span>
                            <span>
                                Batas: {{ $ujian->deadline ? \Carbon\Carbon::parse($ujian->deadline)->isoFormat('D MMM Y, HH:mm') : 'Sesuai Jadwal' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Tombol Aksi Sesuai Status Ujian -->
                <div class="flex items-center justify-between sm:justify-end gap-3 pt-3 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                    @if($ujian->sudah_dikerjakan)
                        <!-- 1. JIKA SUDAH DIKERJAKAN: Tampilkan nilai dan tombol Lihat Hasil -->
                        <div class="text-right">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Nilai</span>
                            <span class="text-lg font-black text-indigo-600">{{ $ujian->nilai_siswa }}</span>
                        </div>
                        <a href="{{ route('siswa.ujian.hasil', $ujian->id) }}"
                           class="inline-flex items-center gap-1.5 px-4 py-2.5 text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 rounded-xl transition">
                            Lihat Hasil
                        </a>
                    @elseif($ujian->status === 'scheduled')
                        <!-- 2. JIKA TERJADWAL: Hanya bisa melihat kisi-kisi dan info detail -->
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                            ⏱ Terjadwal
                        </span>
                        <a href="{{ route('siswa.ujian.show', $ujian->id) }}"
                           class="inline-flex items-center gap-1.5 px-4 py-2.5 text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 rounded-xl transition">
                            📋 Detail & Kisi-kisi
                        </a>
                    @else
                        <!-- 3. JIKA SUDAH DIPUBLIKASI: Tombol aktif untuk mulai ujian -->
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-600 border border-blue-200">
                            Tersedia
                        </span>
                        <a href="{{ route('siswa.ujian.kerjakan', $ujian->id) }}"
                           class="inline-flex items-center gap-1.5 px-5 py-2.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition shadow-xs">
                            Kerjakan &rarr;
                        </a>
                    @endif
                </div>

            </div>
        @empty
            <div class="py-16 text-center bg-white rounded-3xl border border-dashed border-slate-200 text-slate-400">
                <svg class="w-12 h-12 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="text-base font-semibold text-slate-700">Belum ada ujian untuk kelas Anda</p>
                <p class="text-xs text-slate-400 mt-1">Ujian resmi yang diterbitkan guru pengampu akan muncul di halaman ini.</p>
            </div>
        @endforelse
    </div>

</div>
@endsection
