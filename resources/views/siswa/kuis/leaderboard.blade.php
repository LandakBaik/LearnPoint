@extends('layouts.siswa')

@section('title', 'Leaderboard Kuis - LearnPoint')

@section('content')
@php
    $siswa = $siswa ?? auth()->user()?->siswa;
    $top1 = $topThree->get(0);
    $top2 = $topThree->get(1);
    $top3 = $topThree->get(2);
@endphp

<div class="max-w-6xl mx-auto space-y-6 pb-12">

    @include('siswa.partials.unassigned-kelas-banner', ['kelas' => $kelas ?? null])

    {{-- 1. Breadcrumb & Tombol Kembali --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <nav class="flex items-center gap-2 text-xs font-semibold text-slate-400 flex-wrap">
            <a href="{{ route('siswa.dashboard') }}" class="hover:text-blue-600 transition flex items-center gap-1.5">
                <i class="fa-solid fa-house text-[11px]"></i>
                <span>Beranda</span>
            </a>
            <span class="text-slate-300">›</span>
            <a href="{{ route('siswa.kuis') }}" class="hover:text-blue-600 transition">
                Kuis Petualangan
            </a>
            <span class="text-[#2563EB] font-bold">›</span>
            <span class="text-[#2563EB] font-bold">Leaderboard</span>
        </nav>

        <a 
            href="{{ $selectedMapel ? route('siswa.kuis', ['mapel_id' => $selectedMapel->id]) : route('siswa.kuis') }}"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 hover:text-slate-900 font-bold text-xs shadow-2xs transition self-start sm:self-auto"
        >
            <i class="fa-solid fa-arrow-left text-xs text-slate-500"></i>
            <span>Kembali ke Kuis</span>
        </a>
    </div>

    {{-- 2. Header Card & Filter Mata Pelajaran --}}
    <div class="bg-white rounded-[28px] border border-slate-200/80 p-6 sm:p-8 shadow-xs space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-start sm:items-center gap-4">
                <div class="w-13 h-13 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-tr from-amber-500 to-yellow-400 text-white flex items-center justify-center text-2xl shadow-md shadow-amber-500/20 shrink-0">
                    <i class="fa-solid fa-trophy"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h1 class="text-xl sm:text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">
                            Leaderboard Kuis
                        </h1>
                        @if($tingkatan)
                            <span class="px-3 py-1 rounded-full bg-blue-50 text-[#2563EB] border border-blue-200 text-xs font-extrabold">
                                Tingkatan {{ $tingkatan }}
                            </span>
                        @endif
                        @if($periodeLabel)
                            <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 border border-slate-200 text-xs font-bold">
                                {{ $periodeLabel }}
                            </span>
                        @endif
                    </div>
                    <p class="text-xs sm:text-sm text-slate-500 font-medium mt-1">
                        Peringkat akumulasi skor kuis siswa satu tingkatan pada periode akademik aktif{{ $selectedMapel ? ' untuk mata pelajaran ' . $selectedMapel->nama_mapel : '' }}.
                    </p>
                </div>
            </div>
        </div>

        {{-- Filter Mata Pelajaran --}}
        @if($mapels->isNotEmpty())
            <div class="pt-4 border-t border-slate-100 space-y-2.5">
                <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">
                    Filter Mata Pelajaran
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <a 
                        href="{{ route('siswa.kuis.leaderboard') }}"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 border {{ !$selectedMapel ? 'bg-[#2563EB] text-white border-[#2563EB] shadow-sm shadow-blue-500/20' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200/80' }}"
                    >
                        <i class="fa-solid fa-layer-group text-[11px]"></i>
                        <span>Semua Mapel</span>
                    </a>

                    @foreach($mapels as $m)
                        @php
                            $isActiveMapel = $selectedMapel && (int) $selectedMapel->id === (int) $m->id;
                        @endphp
                        <a 
                            href="{{ route('siswa.kuis.leaderboard', ['mapel_id' => $m->id]) }}"
                            class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 border {{ $isActiveMapel ? 'bg-[#2563EB] text-white border-[#2563EB] shadow-sm shadow-blue-500/20' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200/80' }}"
                        >
                            <i class="fa-solid fa-book text-[11px] {{ $isActiveMapel ? 'text-blue-200' : 'text-slate-400' }}"></i>
                            <span>{{ $m->nama_mapel }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- 3. Kartu Status Peringkat Siswa yang Sedang Login --}}
    @if($siswa)
        @php
            $myBanner = $myRank['active_banner'] ?? $siswa->active_banner ?? ['gif_url' => null, 'nama' => 'Tema Bawaan'];
            $myGifUrl = !empty($myBanner['gif_url']) ? asset($myBanner['gif_url']) : null;
        @endphp
        <div class="relative overflow-hidden rounded-2xl border-2 {{ $myRank ? 'border-blue-500/80' : 'border-slate-200' }} p-4 sm:p-5 shadow-xs">
            @if($myGifUrl)
                <img src="{{ $myGifUrl }}" alt="{{ $myBanner['nama'] ?? 'Banner' }}" class="absolute inset-0 w-full h-full object-cover pointer-events-none select-none z-0">
                <div class="absolute inset-0 bg-slate-900/65 backdrop-blur-[1px] z-0"></div>
            @else
                <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-indigo-600 z-0"></div>
            @endif

            <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-white">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-xl bg-white/20 backdrop-blur-md border border-white/30 flex items-center justify-center font-black text-base shrink-0">
                        {{ $myRank ? '#' . $myRank['rank'] : '-' }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs uppercase tracking-wider font-extrabold text-blue-200">
                                Posisi Kamu Saat Ini
                            </span>
                            @if(!empty($myBanner['badge']))
                                <span class="px-2 py-0.5 rounded-md bg-white/15 border border-white/20 text-[10px] font-bold text-white">
                                    {{ $myBanner['badge'] }}
                                </span>
                            @endif
                        </div>
                        <div class="text-sm sm:text-base font-extrabold mt-0.5">
                            @if($myRank)
                                {{ $myRank['nama'] }} <span class="text-xs font-semibold text-blue-100">({{ $myRank['kelas'] }})</span>
                            @else
                                Belum Masuk Peringkat — Selesaikan kuis untuk mulai mengumpulkan skor!
                            @endif
                        </div>
                    </div>
                </div>

                @if($myRank)
                    <div class="flex items-center gap-3 self-start sm:self-auto">
                        <div class="px-4 py-2 rounded-xl bg-white/15 backdrop-blur-md border border-white/25 text-right">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-blue-100">Total Skor</div>
                            <div class="text-base sm:text-lg font-black text-amber-300 flex items-center gap-1.5">
                                <i class="fa-solid fa-star text-xs"></i>
                                <span>{{ $myRank['skor_formatted'] }} XP</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if($leaderboard->isEmpty())
        {{-- Empty State jika belum ada siswa yang menyelesaikan kuis --}}
        <div class="bg-white rounded-[28px] border border-slate-200/80 p-12 text-center space-y-3 shadow-xs">
            <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-500 border border-amber-100 flex items-center justify-center mx-auto text-2xl">
                <i class="fa-solid fa-ranking-star"></i>
            </div>
            <h3 class="text-lg font-extrabold text-slate-800">Belum Ada Data Peringkat</h3>
            <p class="text-xs sm:text-sm text-slate-500 max-w-md mx-auto">
                Belum ada siswa di tingkatan {{ $tingkatan ?? 'ini' }} yang menyelesaikan kuis{{ $selectedMapel ? ' pada mata pelajaran ' . $selectedMapel->nama_mapel : '' }}. Jadilah yang pertama menempati peringkat #1!
            </p>
        </div>
    @else
        {{-- 4. PODIUM TOP 3 DENGAN LATAR BANNER GIF SISWA --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 items-end pt-2">
            @php
                // Urutan tampilan di desktop: Rank 2 (Kiri), Rank 1 (Tengah & Lebih Tinggi), Rank 3 (Kanan)
                $podiumCards = [
                    [
                        'data'        => $top2,
                        'rank'        => 2,
                        'order_class' => 'order-2 md:order-1',
                        'height_class'=> 'min-h-[210px]',
                        'ring_class'  => 'border-slate-300 shadow-md',
                        'badge_bg'    => 'from-slate-300 to-slate-400 text-slate-900',
                        'medal_icon'  => 'fa-medal text-slate-200',
                        'label'       => 'Peringkat #2',
                    ],
                    [
                        'data'        => $top1,
                        'rank'        => 1,
                        'order_class' => 'order-1 md:order-2',
                        'height_class'=> 'min-h-[245px]',
                        'ring_class'  => 'border-amber-400 shadow-xl shadow-amber-500/15',
                        'badge_bg'    => 'from-amber-400 to-yellow-300 text-amber-950',
                        'medal_icon'  => 'fa-crown text-amber-300',
                        'label'       => 'Peringkat #1',
                    ],
                    [
                        'data'        => $top3,
                        'rank'        => 3,
                        'order_class' => 'order-3 md:order-3',
                        'height_class'=> 'min-h-[200px]',
                        'ring_class'  => 'border-amber-700/60 shadow-md',
                        'badge_bg'    => 'from-amber-700 to-amber-600 text-white',
                        'medal_icon'  => 'fa-medal text-amber-500',
                        'label'       => 'Peringkat #3',
                    ],
                ];
            @endphp

            @foreach($podiumCards as $card)
                @if($card['data'])
                    @php
                        $item = $card['data'];
                        $bannerUrl = $item['active_banner']['full_url'] ?? null;
                    @endphp
                    <div class="{{ $card['order_class'] }} relative overflow-hidden rounded-[26px] border-2 {{ $card['ring_class'] }} {{ $card['height_class'] }} p-6 flex flex-col justify-between transition-transform duration-300 hover:-translate-y-1">
                        {{-- Background Banner GIF Siswa --}}
                        @if($bannerUrl)
                            <img src="{{ $bannerUrl }}" alt="{{ $item['active_banner']['nama'] ?? 'Banner' }}" class="absolute inset-0 w-full h-full object-cover pointer-events-none select-none z-0">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-900/55 to-slate-900/30 z-0"></div>
                        @else
                            <div class="absolute inset-0 bg-gradient-to-br from-slate-800 via-slate-900 to-blue-950 z-0"></div>
                        @endif

                        {{-- Top Row: Badge Rank & Banner Tag --}}
                        <div class="relative z-10 flex items-center justify-between gap-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-gradient-to-r {{ $card['badge_bg'] }} text-xs font-black shadow-xs">
                                <i class="fa-solid {{ $card['rank'] === 1 ? 'fa-crown' : 'fa-trophy' }} text-[11px]"></i>
                                <span>#{{ $card['rank'] }}</span>
                            </span>

                            @if($item['is_me'])
                                <span class="px-2.5 py-0.5 rounded-full bg-blue-500/90 text-white text-[10px] font-black uppercase tracking-wider">
                                    Kamu
                                </span>
                            @endif
                        </div>

                        {{-- Center/Bottom Info: Avatar, Nama, Kelas & Skor --}}
                        <div class="relative z-10 pt-6 flex items-end justify-between gap-3">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <div class="w-13 h-13 rounded-2xl bg-white/20 backdrop-blur-md border-2 border-white/50 text-white font-black text-base flex items-center justify-center shrink-0 shadow-md">
                                    {{ $item['initials'] }}
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-base sm:text-lg font-extrabold text-white truncate drop-shadow-xs">
                                        {{ $item['nama'] }}
                                    </h3>
                                    <p class="text-xs font-semibold text-slate-200">
                                        Kelas {{ $item['kelas'] }}
                                    </p>
                                </div>
                            </div>

                            <div class="text-right shrink-0">
                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-300">Skor</div>
                                <div class="text-lg font-black text-amber-300 whitespace-nowrap">
                                    {{ $item['skor_formatted'] }} <span class="text-xs font-bold">XP</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        {{-- 5. DAFTAR PERINGKAT SELANJUTNYA (RANK #4 KE BAWAH) --}}
        @if($restRankings->isNotEmpty())
            <div class="bg-white rounded-[28px] border border-slate-200/80 p-5 sm:p-7 shadow-xs space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-base sm:text-lg font-extrabold text-slate-900">
                        Daftar Peringkat Tingkatan {{ $tingkatan }}
                    </h2>
                    <span class="text-xs font-bold text-slate-400">
                        Total {{ $leaderboard->count() }} Siswa
                    </span>
                </div>

                <div class="space-y-3">
                    @foreach($restRankings as $row)
                        @php
                            $rowBannerUrl = $row['active_banner']['full_url'] ?? null;
                        @endphp
                        <div class="relative overflow-hidden rounded-2xl border {{ $row['is_me'] ? 'border-blue-500 ring-2 ring-blue-500/20' : 'border-slate-200/90' }} p-4 flex items-center justify-between gap-4 transition hover:shadow-sm">
                            {{-- Background Banner GIF Siswa --}}
                            @if($rowBannerUrl)
                                <img src="{{ $rowBannerUrl }}" alt="{{ $row['active_banner']['nama'] ?? 'Banner' }}" class="absolute inset-0 w-full h-full object-cover pointer-events-none select-none z-0">
                                <div class="absolute inset-0 bg-slate-900/65 backdrop-blur-[1px] z-0"></div>
                            @else
                                <div class="absolute inset-0 {{ $row['is_me'] ? 'bg-[#EFF6FF]' : 'bg-[#F8FAFC]' }} z-0"></div>
                            @endif

                            {{-- Kiri: Nomor Rank + Avatar + Nama --}}
                            <div class="relative z-10 flex items-center gap-3.5 min-w-0">
                                <div class="w-10 h-10 rounded-xl {{ $rowBannerUrl ? 'bg-white/20 text-white border border-white/30 backdrop-blur-md' : 'bg-white text-slate-700 border border-slate-200' }} font-black text-sm flex items-center justify-center shrink-0">
                                    #{{ $row['rank'] }}
                                </div>

                                <div class="w-10 h-10 rounded-full {{ $rowBannerUrl ? 'bg-blue-600/80 text-white border border-white/40' : 'bg-blue-600 text-white' }} font-bold text-xs flex items-center justify-center shrink-0">
                                    {{ $row['initials'] }}
                                </div>

                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-sm sm:text-base font-extrabold truncate {{ $rowBannerUrl ? 'text-white' : 'text-slate-900' }}">
                                            {{ $row['nama'] }}
                                        </span>
                                        @if($row['is_me'])
                                            <span class="px-2 py-0.5 rounded-md bg-blue-600 text-white text-[10px] font-black uppercase">
                                                Kamu
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-xs font-semibold {{ $rowBannerUrl ? 'text-slate-200' : 'text-slate-500' }}">
                                        Kelas {{ $row['kelas'] }}
                                    </div>
                                </div>
                            </div>

                            {{-- Kanan: Total Skor XP --}}
                            <div class="relative z-10 text-right shrink-0">
                                <div class="px-3.5 py-1.5 rounded-xl {{ $rowBannerUrl ? 'bg-white/15 backdrop-blur-md border border-white/25 text-amber-300' : 'bg-white border border-slate-200/80 text-[#2563EB]' }} font-black text-sm">
                                    {{ $row['skor_formatted'] }} XP
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif

</div>
@endsection
