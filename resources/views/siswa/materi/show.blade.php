@extends('layouts.app')

@section('title', 'Detail Materi - ' . ($mapel->nama_mapel ?? 'Mata Pelajaran'))

@section('content')
    <div class="space-y-5">

        <!-- 1. Top Navigation Bar -->
        <div class="flex items-center gap-3 text-sm font-medium">
            <a href="{{ route('siswa.beranda') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-white text-slate-700 hover:bg-slate-100 rounded-full border border-slate-200 transition shadow-2xs">
                <i class="fa-solid fa-arrow-left text-xs"></i>
                Kembali ke Beranda
            </a>
            <span class="text-slate-300">/</span>
            <span class="text-slate-800 font-semibold">
                {{ $mapel->nama_mapel ?? $mapel->nama ?? 'Matematika' }}
            </span>
        </div>

        <!-- 2. Hero Banner Informasi Mapel & Kemajuan Belajar -->
        <div class="relative bg-gradient-to-r from-[#0d1e4c] via-[#102b66] to-[#1e3b8a] text-white p-8 rounded-3xl shadow-xl overflow-hidden flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">

            <!-- Info Mapel & Pengampu -->
            <div class="space-y-4 max-w-xl z-10">
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 bg-white/10 backdrop-blur-md rounded-full text-xs font-medium text-blue-200 border border-white/10">
                        Tahun Ajaran {{ $tahunAjaran ?? '2024/2025' }} • Semester {{ $semester ?? 'Ganjil' }}
                    </span>
                    <span class="px-3 py-1 bg-white/10 backdrop-blur-md rounded-full text-xs font-medium text-blue-200 border border-white/10">
                        Kelas {{ $siswa->anggotaKelases->first()->kelas->nama_kelas ?? 'VII IPA 1' }}
                    </span>
                </div>

                <h1 class="text-4xl font-extrabold tracking-tight">
                    {{ $mapel->nama_mapel ?? $mapel->nama ?? 'IPA' }}
                </h1>

                <div class="flex items-center gap-3 pt-2">
                    <div class="w-10 h-10 rounded-full bg-slate-700/60 border border-white/20 flex items-center justify-center font-bold text-sm text-blue-200">
                        {{ strtoupper(substr($mapel->guru->nama ?? 'AP', 0, 2)) }}
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-white leading-none">
                            {{ $mapel->guru->nama ?? 'Drs. Amba Pratama, M.Pd' }}
                        </p>
                        <p class="text-xs text-blue-200/80 mt-1">
                            Guru Utama Pengampu {{ $mapel->nama_mapel ?? 'Matematika' }}
                        </p>
                    </div>
                </div>

                <div class="pt-2">
                    <button class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white font-semibold text-sm rounded-xl transition shadow-lg shadow-blue-600/30">
                        <i class="fa-solid fa-play text-xs"></i>
                        Lanjutkan Belajar
                    </button>
                </div>
            </div>

            <!-- Widget Kemajuan Belajar -->
            <div class="w-full lg:w-80 bg-white/10 backdrop-blur-md border border-white/10 p-5 rounded-2xl space-y-4 z-10">
                <div class="flex justify-between items-center text-xs font-semibold uppercase tracking-wider text-blue-200">
                    <span>Kemajuan Belajar</span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-[10px]">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        Aktif Belajar
                    </span>
                </div>

                <div class="flex items-baseline justify-between">
                    <span class="text-3xl font-extrabold text-white">
                        {{ $progressPersen ?? '84.21' }}%
                    </span>
                    <span class="text-xs text-blue-200">
                        {{ $materiSelesaiCount ?? 16 }} dari {{ $totalMateriCount ?? 19 }} Langkah
                    </span>
                </div>

                <div class="w-full bg-white/20 h-2 rounded-full overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-400 to-blue-600 h-full rounded-full transition-all duration-500"
                        style="--progress: {{ $progressPersen ?? 0 }}%; width: var(--progress);">
                    </div>
                </div>

                <!-- Banner Motivasi Small -->
                <div class="flex items-start gap-3 bg-white/10 p-3 rounded-xl border border-white/10">
                    <div class="p-1.5 bg-amber-400/20 text-amber-300 rounded-lg text-xs">
                        ⚡
                    </div>
                    <div>
                        <p class="text-xs font-bold text-white">Performa Sangat Baik!</p>
                        <p class="text-[11px] text-blue-200/80">Selesaikan 3 modul untuk sertifikat.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Navigation Filter Tabs (Minggu 1 - Minggu N) -->
        <div x-data="{ selectedTab: 1 }" class="space-y-6">
            <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
                @foreach($babs ?? range(1, 7) as $index => $bab)
                    @php $weekNum = is_object($bab) ? $loop->iteration : $bab; @endphp
                    <button @click="selectedTab = {{ $weekNum }}"
                        :class="selectedTab === {{ $weekNum }}
                        ? 'bg-blue-600 text-white font-semibold shadow-md shadow-blue-500/20'
                        : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                        class="px-5 py-2.5 rounded-2xl text-sm whitespace-nowrap transition flex items-center gap-2">
                        <i class="fa-regular fa-calendar-check text-xs"></i>
                        <span>Minggu {{ $weekNum }}</span>
                        <template x-if="selectedTab === {{ $weekNum }}">
                            <span class="w-2 h-2 rounded-full bg-white ml-1"></span>
                        </template>
                    </button>
                @endforeach
            </div>

            <!-- 4. Daftar Item Berkas / Tugas Materi -->
            <div class="space-y-4">
                @if(isset($babs) && $babs->isNotEmpty())
                    @foreach($babs as $index => $bab)
                        <div x-show="selectedTab === {{ $loop->iteration }}" class="space-y-4">
                            @forelse($bab->materis as $materi)
                                <!-- Card Item Dokumen / Tugas -->
                                <div class="bg-white p-5 rounded-2xl border {{ $materi->is_tugas ?? false ? 'border-blue-200 bg-blue-50/20' : 'border-slate-100' }} shadow-xs hover:shadow-md transition flex flex-col md:flex-row md:items-center justify-between gap-4">
                                    <div class="flex items-start gap-4">
                                        <!-- Icon Type Box -->
                                        <div class="p-3 rounded-xl {{ $materi->is_tugas ?? false ? 'bg-amber-100 text-amber-600' : 'bg-purple-100 text-purple-600' }} text-xl flex items-center justify-center shrink-0">
                                            <i class="fa-solid {{ $materi->is_tugas ?? false ? 'fa-clipboard-list' : 'fa-file-lines' }}"></i>
                                        </div>

                                        <div class="space-y-1">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold tracking-wide uppercase {{ $materi->is_tugas ?? false ? 'bg-amber-100 text-amber-700' : 'bg-purple-100 text-purple-700' }}">
                                                    {{ $materi->is_tugas ?? false ? 'TUGAS MANDIRI' : 'DOKUMEN' }}
                                                </span>

                                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium {{ $materi->is_terunduh ?? true ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                                    • {{ $materi->status_label ?? ($materi->is_tugas ?? false ? 'Belum Selesai' : 'Terunduh') }}
                                                </span>

                                                <span class="text-xs text-slate-400">
                                                    • {{ $materi->file_size ?? 'PDF 3.4 MB' }}
                                                </span>
                                            </div>

                                            <h3 class="text-base font-bold text-slate-800">
                                                {{ $materi->judul ?? 'BKPM 1-4: Ringkasan Rumus & Kumpulan Soal Terpilih' }}
                                            </h3>

                                            <p class="text-xs text-slate-500 max-w-2xl leading-relaxed">
                                                {{ $materi->deskripsi ?? 'Buku Kerja Praktik Mandiri berisi daftar rumus cepat eksponen, bentuk akar, dan langkah penyelesaian sistematis.' }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="shrink-0 flex items-center gap-2 self-end md:self-center">
                                        @if($materi->is_tugas ?? false)
                                            <a href="#" class="px-5 py-2.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition flex items-center gap-2 shadow-md shadow-blue-500/20">
                                                <i class="fa-solid fa-arrow-up-from-bracket"></i>
                                                Kirim Jawaban
                                            </a>
                                        @else
                                            <a href="{{ asset('storage/' . ($materi->file_path ?? '#')) }}" download class="px-4 py-2 text-xs font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition flex items-center gap-2">
                                                <i class="fa-solid fa-download text-xs"></i>
                                                Unduh Lagi
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="p-8 bg-white rounded-2xl border border-slate-100 text-center text-slate-400 text-sm">
                                    Belum ada materi untuk minggu ini.
                                </div>
                            @endforelse
                        </div>
                    @endforeach
                @else
                    <!-- Dummy Static Content (Tampilan persis gambar jika data $babs belum di-load) -->
                    <div class="space-y-4" x-show="selectedTab === 1">
                        <!-- Item Dokumen -->
                        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="flex items-start gap-4">
                                <div class="p-3.5 rounded-xl bg-purple-50 text-purple-600 text-xl flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-file-lines"></i>
                                </div>
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold tracking-wide uppercase bg-purple-100 text-purple-700">DOKUMEN</span>
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-emerald-100 text-emerald-700">• Terunduh</span>
                                        <span class="text-xs text-slate-400">• PDF 3.4 MB</span>
                                    </div>
                                    <h3 class="text-base font-bold text-slate-800">BKPM 1-4: Ringkasan Rumus & Kumpulan Soal Terpilih</h3>
                                    <p class="text-xs text-slate-500 max-w-2xl">Buku Kerja Praktik Mandiri berisi daftar rumus cepat eksponen, bentuk akar, dan langkah penyelesaian sistematis.</p>
                                </div>
                            </div>
                            <div class="shrink-0 self-end md:self-center">
                                <button class="px-4 py-2 text-xs font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition flex items-center gap-2">
                                    <i class="fa-solid fa-download text-xs"></i> Unduh Lagi
                                </button>
                            </div>
                        </div>

                        <!-- Item Tugas -->
                        <div class="bg-white p-5 rounded-2xl border border-blue-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="flex items-start gap-4">
                                <div class="p-3.5 rounded-xl bg-amber-50 text-amber-600 text-xl flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-clipboard-list"></i>
                                </div>
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold tracking-wide uppercase bg-amber-100 text-amber-800">TUGAS MANDIRI</span>
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-600">• Belum Selesai</span>
                                        <span class="text-xs text-slate-400">• Tenggat: Besok, 23:59 WIB</span>
                                    </div>
                                    <h3 class="text-base font-bold text-slate-800">Tugas 1: Studi Kasus Penyederhanaan Eksponensial Terapan</h3>
                                    <p class="text-xs text-slate-500 max-w-2xl">Kerjakan 5 studi kasus pemecahan persoalan eksponen berbasis sains dan lampirkan hasil pengerjaan dalam format PDF atau foto buku tulis.</p>
                                </div>
                            </div>
                            <div class="shrink-0 self-end md:self-center">
                                <button class="px-5 py-2.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition flex items-center gap-2 shadow-md shadow-blue-500/20">
                                    <i class="fa-solid fa-arrow-up-from-bracket"></i> Kirim Jawaban
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
