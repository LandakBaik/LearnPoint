@extends('layouts.siswa')

@section('title', 'Panduan Kuis Petualangan - LearnPoint')

@section('content')
<div class="space-y-8 pb-16">

    {{-- Breadcrumb Navigation --}}
    <nav class="flex items-center gap-2 text-xs font-semibold text-slate-400">
        <a href="{{ route('siswa.dashboard') }}" class="hover:text-blue-600 transition flex items-center gap-1.5">
            <i class="fa-solid fa-house text-[11px]"></i>
            <span>Beranda</span>
        </a>
        <span class="text-slate-300">›</span>
        <a href="{{ route('siswa.kuis') }}" class="hover:text-blue-600 transition">
            Kuis Petualangan
        </a>
        <span class="text-slate-300">›</span>
        <span class="text-[#2563EB] font-bold">Panduan & Tata Cara</span>
    </nav>

    {{-- Hero Header --}}
    <div class="relative overflow-hidden rounded-[32px] bg-gradient-to-br from-[#0B132B] via-[#1C2541] to-[#3A506B] p-8 md:p-12 text-white border border-slate-700/80 shadow-2xl">
        <div class="relative z-10 max-w-2xl space-y-3">
            <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-blue-500/20 border border-blue-400/40 text-blue-300 text-xs font-bold tracking-wide backdrop-blur-md">
                <i class="fa-solid fa-compass text-amber-400 animate-spin" style="animation-duration: 8s;"></i>
                <span>BUKU PANDUAN PENGEMBARA ANTARIKSA</span>
            </div>
            <h1 class="text-3xl md:text-4xl font-black tracking-tight text-white leading-tight">
                Panduan Lengkap Kuis Petualangan
            </h1>
            <p class="text-sm md:text-base text-slate-300 font-medium leading-relaxed">
                Pelajari alur ekspedisi galaksi, mekanisme perlevelan Duolingo-style, strategi penggunaan Power Item, hingga perhitungan skor & perolehan koin.
            </p>
            <div class="pt-2 flex flex-wrap items-center gap-3">
                <a href="{{ route('siswa.kuis') }}" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs flex items-center gap-2 transition shadow-lg shadow-blue-600/30">
                    <i class="fa-solid fa-rocket"></i>
                    <span>Mulai Menjelajah Planet</span>
                </a>
                <a href="{{ route('siswa.gacha.panduan') }}" class="px-5 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white font-bold text-xs flex items-center gap-2 backdrop-blur-md transition">
                    <i class="fa-solid fa-wand-magic-sparkles text-amber-400"></i>
                    <span>Panduan Sistem Gacha</span>
                </a>
            </div>
        </div>

        {{-- Background Cosmic Glow Element --}}
        <div class="absolute -right-10 -bottom-10 w-96 h-96 rounded-full bg-blue-500/20 blur-3xl pointer-events-none"></div>
        <div class="absolute right-12 top-1/2 -translate-y-1/2 hidden lg:block opacity-25 pointer-events-none">
            <i class="fa-solid fa-planet-ringed text-[180px] text-blue-300"></i>
        </div>
    </div>

    {{-- 1. Alur Petualangan 4 Langkah --}}
    <div class="space-y-4">
        <div>
            <h2 class="text-lg md:text-xl font-black text-slate-800 tracking-tight flex items-center gap-2.5">
                <i class="fa-solid fa-route text-blue-600"></i>
                <span>Alur Utama Kuis Petualangan</span>
            </h2>
            <p class="text-xs md:text-sm text-slate-500 font-medium mt-0.5">
                Empat langkah mudah untuk menyelesaikan misi dan membebaskan planet dari ancaman soal.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Step 1 --}}
            <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs space-y-3 relative overflow-hidden">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 font-black text-sm flex items-center justify-center border border-blue-100">
                    01
                </div>
                <h3 class="text-sm font-extrabold text-slate-800">Pilih Planet Mata Pelajaran</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Setiap planet mewakili 1 mata pelajaran yang tersedia di kelasmu. Pilih planet yang ingin kamu tolong terlebih dahulu.
                </p>
            </div>

            {{-- Step 2 --}}
            <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs space-y-3 relative overflow-hidden">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 font-black text-sm flex items-center justify-center border border-indigo-100">
                    02
                </div>
                <h3 class="text-sm font-extrabold text-slate-800">Pilih Zona Misi</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Setiap planet dibagi menjadi beberapa Zona Kuis yang dipublikasikan oleh Guru pengampu mata pelajaran.
                </p>
            </div>

            {{-- Step 3 --}}
            <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs space-y-3 relative overflow-hidden">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 font-black text-sm flex items-center justify-center border border-amber-100">
                    03
                </div>
                <h3 class="text-sm font-extrabold text-slate-800">Taklukkan Jalur Level</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Kerjakan level berurutan mulai dari Arc Mudah. Selesaikan level untuk membuka level berikutnya pada peta petualangan.
                </p>
            </div>

            {{-- Step 4 --}}
            <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs space-y-3 relative overflow-hidden">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 font-black text-sm flex items-center justify-center border border-emerald-100">
                    04
                </div>
                <h3 class="text-sm font-extrabold text-slate-800">Klaim XP & Koin Hadiah</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Raih skor maksimal, bintang kelulusan, peringkat leaderboard kelas, serta koin yang dapat ditukarkan di gacha antariksa!
                </p>
            </div>
        </div>
    </div>

    {{-- 2. Sistem Arc & Tingkat Kesulitan --}}
    <div class="bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-8 shadow-xs space-y-5">
        <div>
            <h2 class="text-lg md:text-xl font-black text-slate-800 tracking-tight flex items-center gap-2.5">
                <i class="fa-solid fa-layer-group text-purple-600"></i>
                <span>Sistem Tingkat Kesulitan (Arc & Level)</span>
            </h2>
            <p class="text-xs md:text-sm text-slate-500 font-medium mt-0.5">
                Konsep berjenjang di mana soal disusun secara runtut dan membuka tantangan baru secara otomatis.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            {{-- Arc Mudah --}}
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50/40 p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-300">
                        Arc 1 • Mudah
                    </span>
                    <div class="flex gap-1 text-emerald-500 text-xs">
                        <i class="fa-solid fa-circle"></i>
                    </div>
                </div>
                <h4 class="text-sm font-extrabold text-slate-800">Tingkat Pengenalan Konsep</h4>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Berisi soal-soal pemahaman dasar materi. Menyelesaikan semua level pada Arc Mudah akan membuka akses menuju Arc Sedang.
                </p>
            </div>

            {{-- Arc Sedang --}}
            <div class="rounded-2xl border border-amber-200 bg-amber-50/40 p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-300">
                        Arc 2 • Sedang
                    </span>
                    <div class="flex gap-1 text-amber-500 text-xs">
                        <i class="fa-solid fa-circle"></i>
                        <i class="fa-solid fa-circle"></i>
                    </div>
                </div>
                <h4 class="text-sm font-extrabold text-slate-800">Tingkat Aplikasi & Latihan</h4>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Soal memerlukan analisis rumus atau pemikiran mendalam. Selesaikan semua level di sini untuk membuka Arc pamungkas.
                </p>
            </div>

            {{-- Arc Sulit --}}
            <div class="rounded-2xl border border-rose-200 bg-rose-50/40 p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-rose-100 text-rose-800 border border-rose-300">
                        Arc 3 • Sulit
                    </span>
                    <div class="flex gap-1 text-rose-500 text-xs">
                        <i class="fa-solid fa-circle"></i>
                        <i class="fa-solid fa-circle"></i>
                        <i class="fa-solid fa-circle"></i>
                    </div>
                </div>
                <h4 class="text-sm font-extrabold text-slate-800">Tingkat Penalaran Tinggi (HOTS)</h4>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Tantangan puncak penentu nilai tertinggi. Menyelesaikan seluruh level di Arc ini berarti misi pada kuis selesai 100%!
                </p>
            </div>
        </div>
    </div>

    {{-- 3. Panduan Power Item --}}
    <div class="bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-8 shadow-xs space-y-6">
        <div>
            <h2 class="text-lg md:text-xl font-black text-slate-800 tracking-tight flex items-center gap-2.5">
                <i class="fa-solid fa-bolt text-amber-500"></i>
                <span>Katalog Power Item Pembantu</span>
            </h2>
            <p class="text-xs md:text-sm text-slate-500 font-medium mt-0.5">
                Power Item dapat kamu gunakan selama mengerjakan kuis untuk membalikkan keadaan atau memperbesar skor.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($powers as $item)
                @php
                    $attr = $item->atribut ?? [];
                    $rarity = $item->rarity ?? 'common';
                @endphp
                <div class="rounded-2xl border p-4 flex flex-col justify-between gap-3 bg-slate-50/60 hover:bg-white hover:shadow-md transition">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 shadow-2xs flex items-center justify-center text-base {{ $rarity === 'epic' ? 'text-purple-600' : ($rarity === 'rare' ? 'text-emerald-600' : 'text-blue-600') }}">
                                <i class="{{ $item->icon ?? ($attr['icon'] ?? 'fa-solid fa-cube') }}"></i>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider {{ $rarity === 'epic' ? 'bg-purple-100 text-purple-700 border border-purple-200' : ($rarity === 'rare' ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : 'bg-blue-100 text-blue-700 border border-blue-200') }}">
                                {{ $rarity }}
                            </span>
                        </div>

                        <div>
                            <h4 class="text-sm font-extrabold text-slate-800">{{ $item->nama }}</h4>
                            <p class="text-[11px] font-bold text-slate-400 mt-0.5">{{ $attr['tagline'] ?? '-' }}</p>
                            <p class="text-xs text-slate-600 leading-relaxed mt-2">{{ $item->deskripsi }}</p>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-200/70 text-[11px] font-semibold text-slate-500 flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-info text-blue-500"></i>
                        <span>{{ $attr['detail_efek'] ?? 'Efek kuis petualangan' }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- 4. Bintang & Aturan Poin --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Bintang Kelulusan --}}
        <div class="bg-white rounded-3xl border border-slate-200/90 p-6 shadow-xs space-y-4">
            <h3 class="text-base font-extrabold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-star text-amber-400"></i>
                <span>Tingkat Bintang Kelulusan</span>
            </h3>
            <p class="text-xs text-slate-500 font-medium leading-relaxed">
                Setiap menyelesaikan kuis secara penuh, kamu akan mendapatkan bintang prestasi sesuai total nilai yang diperoleh:
            </p>
            <div class="space-y-2.5 text-xs">
                <div class="flex items-center justify-between p-3 rounded-xl bg-amber-50/70 border border-amber-200">
                    <span class="font-bold text-amber-900 flex items-center gap-1.5">
                        <i class="fa-solid fa-star text-amber-400"></i>
                        <i class="fa-solid fa-star text-amber-400"></i>
                        <i class="fa-solid fa-star text-amber-400"></i>
                        <span>Bintang 3 (Sempurna)</span>
                    </span>
                    <span class="font-extrabold text-amber-700">Nilai ≥ 85</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200">
                    <span class="font-bold text-slate-700 flex items-center gap-1.5">
                        <i class="fa-solid fa-star text-amber-400"></i>
                        <i class="fa-solid fa-star text-amber-400"></i>
                        <i class="fa-regular fa-star text-slate-300"></i>
                        <span>Bintang 2 (Hebat)</span>
                    </span>
                    <span class="font-extrabold text-slate-600">Nilai 70 – 84</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200">
                    <span class="font-bold text-slate-700 flex items-center gap-1.5">
                        <i class="fa-solid fa-star text-amber-400"></i>
                        <i class="fa-regular fa-star text-slate-300"></i>
                        <i class="fa-regular fa-star text-slate-300"></i>
                        <span>Bintang 1 (Cukup)</span>
                    </span>
                    <span class="font-extrabold text-slate-600">Nilai &lt; 70</span>
                </div>
            </div>
        </div>

        {{-- Tips Sukses Pengembara --}}
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50/50 rounded-3xl border border-blue-100 p-6 shadow-xs space-y-4">
            <h3 class="text-base font-extrabold text-blue-900 flex items-center gap-2">
                <i class="fa-solid fa-lightbulb text-amber-500"></i>
                <span>Tips Sukses untuk Pengembara</span>
            </h3>
            <ul class="space-y-3 text-xs text-slate-600 font-medium">
                <li class="flex items-start gap-2.5">
                    <i class="fa-solid fa-check text-emerald-500 mt-0.5 shrink-0"></i>
                    <span><strong>Gunakan 50:50</strong> saat ragu antara 2 jawaban untuk membuang 2 opsi salah yang mengecoh.</span>
                </li>
                <li class="flex items-start gap-2.5">
                    <i class="fa-solid fa-check text-emerald-500 mt-0.5 shrink-0"></i>
                    <span><strong>Pasang 2X Score</strong> pada soal yang kamu yakin jawabannya benar untuk melipatgandakan perolehan poin.</span>
                </li>
                <li class="flex items-start gap-2.5">
                    <i class="fa-solid fa-check text-emerald-500 mt-0.5 shrink-0"></i>
                    <span><strong>Simpan Second Chance</strong> untuk soal tingkat Sulit agar jika salah masih ada kesempatan kedua.</span>
                </li>
                <li class="flex items-start gap-2.5">
                    <i class="fa-solid fa-check text-emerald-500 mt-0.5 shrink-0"></i>
                    <span>Koin hasil kuis bisa ditukarkan di <strong>Wish Gacha</strong> untuk menambah stok Power Item dan membuka Banner GIF langka!</span>
                </li>
            </ul>
        </div>
    </div>

</div>
@endsection
