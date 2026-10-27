@extends('layouts.siswa')

@section('title', 'Kuis & Petualangan Belajar - LearnPoint')

@section('content')
<div class="space-y-6">

    {{-- 1. Hero / Header Banner --}}
    <div class="relative overflow-hidden rounded-[24px] bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 p-6 md:p-8 text-white shadow-lg shadow-blue-500/10">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="max-w-xl space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 backdrop-blur-md text-xs font-semibold text-blue-100 border border-white/20">
                    <i class="fa-solid fa-gamepad text-amber-300"></i>
                    <span>Tantangan Interaktif & Kuis Siswa</span>
                </div>
                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-white leading-tight">
                    Petualangan Kuis Pembelajaran
                </h1>
                <p class="text-sm text-blue-100/90 leading-relaxed">
                    Asah pemahamanmu, kumpulkan skor terbaik, dan taklukkan setiap level soal yang disiapkan oleh bapak/ibu gurumu untuk kelas 
                    <span class="font-bold underline decoration-amber-300 decoration-2 underline-offset-2">{{ $kelas->nama_kelas ?? 'Anda' }}</span>.
                </p>
            </div>

            {{-- Stat Pills Box --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-2 lg:grid-cols-4 gap-3 shrink-0">
                <div class="p-3.5 rounded-2xl bg-white/10 backdrop-blur-md border border-white/15 text-center">
                    <div class="text-2xl font-black text-white leading-none">{{ $totalKuis }}</div>
                    <div class="text-[11px] font-medium text-blue-100 mt-1 uppercase tracking-wider">Total Kuis</div>
                </div>
                <div class="p-3.5 rounded-2xl bg-white/10 backdrop-blur-md border border-white/15 text-center">
                    <div class="text-2xl font-black text-emerald-300 leading-none">{{ $totalTersedia }}</div>
                    <div class="text-[11px] font-medium text-blue-100 mt-1 uppercase tracking-wider">Tersedia</div>
                </div>
                <div class="p-3.5 rounded-2xl bg-white/10 backdrop-blur-md border border-white/15 text-center">
                    <div class="text-2xl font-black text-amber-300 leading-none">{{ $totalSelesai }}</div>
                    <div class="text-[11px] font-medium text-blue-100 mt-1 uppercase tracking-wider">Selesai</div>
                </div>
                <div class="p-3.5 rounded-2xl bg-white/10 backdrop-blur-md border border-white/15 text-center">
                    <div class="text-2xl font-black text-yellow-300 leading-none">{{ $rataRataNilai }}</div>
                    <div class="text-[11px] font-medium text-blue-100 mt-1 uppercase tracking-wider">Rata-rata</div>
                </div>
            </div>
        </div>

        {{-- Background decorative shapes --}}
        <div class="absolute -right-10 -bottom-10 w-60 h-60 bg-white/5 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute right-40 -top-10 w-40 h-40 bg-indigo-400/20 rounded-full blur-xl pointer-events-none"></div>
    </div>

    {{-- Error kelas jika siswa belum punya kelas --}}
    @if(isset($errorKelas))
        <div class="p-5 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 flex items-start gap-3">
            <i class="fa-solid fa-triangle-exclamation text-amber-500 text-xl shrink-0 mt-0.5"></i>
            <div>
                <h4 class="font-bold text-sm">Pemberitahuan Akun:</h4>
                <p class="text-xs text-amber-700 mt-0.5">{{ $errorKelas }}</p>
            </div>
        </div>
    @endif

    {{-- 2. Filter & Pencarian Bar --}}
    <div class="bg-white p-4 md:p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
        <form method="GET" action="{{ route('siswa.kuis') }}" class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
            {{-- Search Bar --}}
            <div class="relative flex-1">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}"
                    placeholder="Cari judul kuis atau materi..." 
                    class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-slate-200 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                />
            </div>

            {{-- Filter Mapel --}}
            <div class="w-full md:w-56 shrink-0">
                <select 
                    name="mapel_id" 
                    onchange="this.form.submit()"
                    class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                >
                    <option value="">Semua Mata Pelajaran</option>
                    @foreach($mapels as $mapel)
                        <option value="{{ $mapel->id }}" {{ request('mapel_id') == $mapel->id ? 'selected' : '' }}>
                            {{ $mapel->nama_mapel }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Submit / Reset --}}
            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition shadow-xs flex items-center justify-center gap-1.5 shrink-0">
                    <i class="fa-solid fa-filter text-[11px]"></i>
                    <span>Terapkan</span>
                </button>
                @if(request()->hasAny(['search', 'mapel_id', 'status']))
                    <a href="{{ route('siswa.kuis') }}" class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl transition shrink-0" title="Reset Filter">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>

        {{-- Status Filter Tabs --}}
        <div class="flex items-center gap-2 overflow-x-auto pt-2 border-t border-slate-100 scrollbar-none">
            @php
                $currentStatus = request('status', '');
                $tabs = [
                    '' => ['label' => 'Semua Kuis', 'icon' => 'fa-layer-group'],
                    'tersedia' => ['label' => 'Bisa Dikerjakan', 'icon' => 'fa-circle-play'],
                    'selesai' => ['label' => 'Sudah Selesai', 'icon' => 'fa-circle-check'],
                    'upcoming' => ['label' => 'Belum Dimulai', 'icon' => 'fa-clock'],
                    'expired' => ['label' => 'Sudah Berakhir', 'icon' => 'fa-clock-rotate-left'],
                ];
            @endphp
            @foreach($tabs as $key => $tab)
                @php
                    $isActive = ($currentStatus === $key);
                    $url = request()->fullUrlWithQuery(['status' => $key ?: null]);
                @endphp
                <a 
                    href="{{ $url }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition flex items-center gap-1.5
                    {{ $isActive ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200/60' }}"
                >
                    <i class="fa-solid {{ $tab['icon'] }} text-[10px]"></i>
                    <span>{{ $tab['label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>

    {{-- 3. Daftar Card Kuis --}}
    @if($quizzes->isEmpty())
        <div class="py-16 text-center bg-white rounded-2xl border border-dashed border-slate-200 p-8 shadow-xs">
            <div class="w-16 h-16 rounded-2xl bg-blue-50 text-blue-500 flex items-center justify-center text-2xl mx-auto mb-3 shadow-xs">
                <i class="fa-solid fa-puzzle-piece"></i>
            </div>
            <h3 class="text-base font-bold text-slate-800">Tidak Ada Kuis Ditemukan</h3>
            <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                @if(request()->hasAny(['search', 'mapel_id', 'status']))
                    Kuis yang kamu cari tidak sesuai dengan filter yang dipilih. Coba reset filter pencarian.
                @else
                    Belum ada kuis yang diterbitkan oleh gurumu untuk kelas {{ $kelas->nama_kelas ?? 'ini' }}.
                @endif
            </p>
            @if(request()->hasAny(['search', 'mapel_id', 'status']))
                <div class="mt-4">
                    <a href="{{ route('siswa.kuis') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-xl hover:bg-blue-700 transition">
                        <i class="fa-solid fa-rotate-left text-[10px]"></i>
                        <span>Reset Filter</span>
                    </a>
                </div>
            @endif
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($quizzes as $quiz)
                @php
                    $nilai = $quiz->riwayat_nilai;
                    $isCompleted = $quiz->student_status === 'selesai';
                    $isUpcoming = $quiz->student_status === 'upcoming';
                    $isExpired = $quiz->student_status === 'expired';
                    $isAvailable = $quiz->student_status === 'tersedia';

                    $mapelName = $quiz->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? 'Mata Pelajaran';
                    $guruName = $quiz->pengampuKelas?->guruMapel?->guru?->nama ?? 'Guru Pengampu';
                    $soalsCount = $quiz->soals_count ?? $quiz->soals->count();
                @endphp

                <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs hover:shadow-md transition flex flex-col justify-between group relative overflow-hidden">
                    {{-- Top colored accent line --}}
                    <div class="absolute top-0 left-0 right-0 h-1.5 
                        {{ $isCompleted ? 'bg-emerald-500' : ($isUpcoming ? 'bg-amber-400' : ($isExpired ? 'bg-rose-400' : 'bg-blue-600')) }}">
                    </div>

                    <div>
                        {{-- Badges Header --}}
                        <div class="flex items-center justify-between gap-2 mb-3 pt-1">
                            <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-100 truncate max-w-[170px]" title="{{ $mapelName }}">
                                {{ $mapelName }}
                            </span>

                            @if($isCompleted)
                                <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1 shrink-0">
                                    <i class="fa-solid fa-circle-check text-[10px]"></i>
                                    Skor: {{ number_format($nilai->nilai, 0) }}
                                </span>
                            @elseif($isUpcoming)
                                <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200 flex items-center gap-1 shrink-0">
                                    <i class="fa-solid fa-clock text-[10px]"></i>
                                    Belum Mulai
                                </span>
                            @elseif($isExpired)
                                <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200 flex items-center gap-1 shrink-0">
                                    <i class="fa-solid fa-hourglass-end text-[10px]"></i>
                                    Berakhir
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1.5 shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Bisa Dikerjakan
                                </span>
                            @endif
                        </div>

                        {{-- Title & Guru --}}
                        <h3 class="text-base font-extrabold text-slate-800 group-hover:text-blue-600 transition leading-snug line-clamp-2 mb-1">
                            {{ $quiz->judul }}
                        </h3>
                        <p class="text-xs text-slate-400 flex items-center gap-1.5 mb-3">
                            <i class="fa-solid fa-chalkboard-user text-slate-400 text-[11px]"></i>
                            <span class="truncate">{{ $guruName }}</span>
                        </p>

                        {{-- Metadata Pill List --}}
                        <div class="p-3 rounded-xl bg-slate-50/80 border border-slate-100 space-y-1.5 text-xs text-slate-600 mb-4">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400 flex items-center gap-1.5">
                                    <i class="fa-solid fa-list-check text-slate-400 text-[10px]"></i>
                                    Jumlah Soal:
                                </span>
                                <span class="font-bold text-slate-700">{{ $soalsCount }} Butir</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-slate-400 flex items-center gap-1.5">
                                    <i class="fa-solid fa-fire text-amber-500 text-[10px]"></i>
                                    Arc Kesulitan:
                                </span>
                                <span class="font-semibold text-slate-700 capitalize">
                                    {{ implode(', ', $quiz->kesulitan_aktif_array ?? ['Umum']) }}
                                </span>
                            </div>

                            @if($quiz->acak_jawaban)
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-400 flex items-center gap-1.5">
                                        <i class="fa-solid fa-shuffle text-indigo-500 text-[10px]"></i>
                                        Pilihan Opsi:
                                    </span>
                                    <span class="font-semibold text-indigo-600">Diacak Otomatis</span>
                                </div>
                            @endif
                        </div>

                        {{-- Schedule Details --}}
                        @if($quiz->waktu_mulai || $quiz->waktu_selesai)
                            <div class="space-y-1 mb-4 text-[11px]">
                                @if($quiz->waktu_mulai)
                                    <div class="flex items-center justify-between text-slate-500">
                                        <span class="text-slate-400">Waktu Buka:</span>
                                        <span class="font-semibold {{ $isUpcoming ? 'text-amber-600' : 'text-slate-700' }}">
                                            {{ $quiz->waktu_mulai->format('d M Y, H:i') }} WIB
                                        </span>
                                    </div>
                                @endif
                                @if($quiz->waktu_selesai)
                                    <div class="flex items-center justify-between text-slate-500">
                                        <span class="text-slate-400">Batas Selesai:</span>
                                        <span class="font-semibold {{ $isExpired ? 'text-rose-600' : 'text-slate-700' }}">
                                            {{ $quiz->waktu_selesai->format('d M Y, H:i') }} WIB
                                        </span>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Action Button --}}
                    <div class="pt-3 border-t border-slate-100">
                        @if($isCompleted)
                            <a 
                                href="{{ route('siswa.kuis.hasil', $quiz->id) }}"
                                class="w-full py-2.5 px-4 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-xs flex items-center justify-center gap-2 transition"
                            >
                                <i class="fa-solid fa-award text-sm"></i>
                                <span>Lihat Hasil Nilai ({{ number_format($nilai->nilai, 0) }})</span>
                            </a>
                        @elseif($isUpcoming)
                            <a 
                                href="{{ route('siswa.kuis.show', $quiz->id) }}"
                                class="w-full py-2.5 px-4 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-700 font-bold text-xs flex items-center justify-center gap-2 transition"
                            >
                                <i class="fa-solid fa-clock text-sm"></i>
                                <span>Lihat Detail Jadwal</span>
                            </a>
                        @elseif($isExpired)
                            <div class="w-full py-2.5 px-4 rounded-xl bg-slate-100 text-slate-400 font-bold text-xs flex items-center justify-center gap-2 cursor-not-allowed">
                                <i class="fa-solid fa-lock text-xs"></i>
                                <span>Waktu Pengerjaan Selesai</span>
                            </div>
                        @else
                            <a 
                                href="{{ route('siswa.kuis.show', $quiz->id) }}"
                                class="w-full py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs flex items-center justify-center gap-2 transition shadow-md shadow-blue-500/20"
                            >
                                <i class="fa-solid fa-play text-xs"></i>
                                <span>Mulai Kuis Petualangan</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection
