@extends('layouts.siswa')

@section('title', 'Panduan Sistem Wish & Gacha - LearnPoint')

@section('content')
<div class="space-y-8 pb-16">

    {{-- Breadcrumb Navigation --}}
    <nav class="flex items-center gap-2 text-xs font-semibold text-slate-400">
        <a href="{{ route('siswa.dashboard') }}" class="hover:text-blue-600 transition flex items-center gap-1.5">
            <i class="fa-solid fa-house text-[11px]"></i>
            <span>Beranda</span>
        </a>
        <span class="text-slate-300">›</span>
        <a href="{{ route('siswa.gacha') }}" class="hover:text-blue-600 transition">
            Wish Antariksa
        </a>
        <span class="text-slate-300">›</span>
        <span class="text-amber-500 font-bold">Panduan & Mekanisme Gacha</span>
    </nav>

    {{-- Hero Header --}}
    <div class="relative overflow-hidden rounded-[32px] bg-gradient-to-br from-[#0B0F2A] via-[#1B1B47] to-[#2D1B4E] p-8 md:p-12 text-white border border-purple-500/30 shadow-2xl">
        <div class="relative z-10 max-w-2xl space-y-3">
            <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-amber-500/20 border border-amber-400/40 text-amber-300 text-xs font-bold tracking-wide backdrop-blur-md">
                <i class="fa-solid fa-wand-magic-sparkles text-amber-300"></i>
                <span>TATA CARA & PERSENTASE RATE WISH</span>
            </div>
            <h1 class="text-3xl md:text-4xl font-black tracking-tight text-white leading-tight">
                Panduan Sistem Gacha Antariksa
            </h1>
            <p class="text-sm md:text-base text-slate-300 font-medium leading-relaxed">
                Ketahui cara mendapatkan koin, aturan garansi 100% item Power, sistem Pity 30x, hingga peluang mendapatkan Banner GIF Dashboard bergerak eksklusif.
            </p>
            <div class="pt-2 flex flex-wrap items-center gap-3">
                <a href="{{ route('siswa.gacha') }}" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-600 hover:to-yellow-600 text-slate-950 font-black text-xs flex items-center gap-2 transition shadow-lg shadow-amber-500/25">
                    <i class="fa-solid fa-sparkles"></i>
                    <span>Buka Halaman Wish</span>
                </a>
                <a href="{{ route('siswa.kuis.panduan') }}" class="px-5 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white font-bold text-xs flex items-center gap-2 backdrop-blur-md transition">
                    <i class="fa-solid fa-compass text-blue-400"></i>
                    <span>Panduan Kuis Petualangan</span>
                </a>
            </div>
        </div>

        {{-- Background Cosmic Sparkle Elements --}}
        <div class="absolute -right-10 -bottom-10 w-96 h-96 rounded-full bg-purple-500/20 blur-3xl pointer-events-none"></div>
        <div class="absolute right-12 top-1/2 -translate-y-1/2 hidden lg:block opacity-25 pointer-events-none">
            <i class="fa-solid fa-meteor text-[180px] text-amber-400"></i>
        </div>
    </div>

    {{-- 1. Tiga Pilar Utama Sistem Gacha --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        {{-- Pilar 1 --}}
        <div class="bg-white rounded-3xl border border-slate-200/90 p-6 shadow-xs space-y-3">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl border border-amber-200">
                <i class="fa-solid fa-coins"></i>
            </div>
            <h3 class="text-base font-extrabold text-slate-800">Biaya Wish & Diskon Koin</h3>
            <p class="text-xs text-slate-500 leading-relaxed font-medium">
                Setiap <strong>1x Wish</strong> membutuhkan <strong>20 Koin</strong>. Lakukan <strong>10x Wish</strong> sekaligus seharga <strong>180 Koin</strong> untuk menghemat 20 Koin!
            </p>
        </div>

        {{-- Pilar 2 --}}
        <div class="bg-white rounded-3xl border border-slate-200/90 p-6 shadow-xs space-y-3">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl border border-emerald-200">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <h3 class="text-base font-extrabold text-slate-800">100% Pasti Dapat Power</h3>
            <p class="text-xs text-slate-500 leading-relaxed font-medium">
                Tidak ada tarikan kosong. Setiap 1x tarikan dijamin pasti memberikan <strong>1 Power Item</strong> pembantu kuis langsung ke inventori akunmu.
            </p>
        </div>

        {{-- Pilar 3 --}}
        <div class="bg-white rounded-3xl border border-slate-200/90 p-6 shadow-xs space-y-3">
            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl border border-purple-200">
                <i class="fa-solid fa-crown"></i>
            </div>
            <h3 class="text-base font-extrabold text-slate-800">Sistem Pity 30x Guaranteed</h3>
            <p class="text-xs text-slate-500 leading-relaxed font-medium">
                Peluang dasar Banner GIF adalah 3%. Jika dalam 29x tarikan belum mendapatkan Banner GIF, maka tarikan ke-<strong>30x DIJAMIN 100%</strong> menghasilkan Banner GIF baru!
            </p>
        </div>
    </div>

    {{-- 2. Rincian Rate Peluang (Tabel Persentase) --}}
    <div class="bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-8 shadow-xs space-y-5">
        <div>
            <h2 class="text-lg md:text-xl font-black text-slate-800 tracking-tight flex items-center gap-2.5">
                <i class="fa-solid fa-chart-pie text-blue-600"></i>
                <span>Tabel Rate Peluang Item & Rarity</span>
            </h2>
            <p class="text-xs md:text-sm text-slate-500 font-medium mt-0.5">
                Rincian transparansi bobot undian untuk setiap kategori hadiah di banner gacha.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-700 font-extrabold uppercase tracking-wider text-[11px] border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Kategori Hadiah</th>
                        <th class="py-3 px-4">Tingkat Rarity</th>
                        <th class="py-3 px-4">Peluang Tarikan</th>
                        <th class="py-3 px-4">Daftar Item / Efek</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr>
                        <td class="py-3.5 px-4 font-bold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-crown text-amber-500"></i>
                            <span>Banner GIF Dashboard</span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-amber-100 text-amber-800 border border-amber-200">
                                Jackpot / Epic
                            </span>
                        </td>
                        <td class="py-3.5 px-4 font-black text-amber-600">3% (Pity 30x Garansi)</td>
                        <td class="py-3.5 px-4">Banner animasi bergerak yang dapat dipasang di Dashboard & Kuis Petualangan.</td>
                    </tr>
                    <tr>
                        <td class="py-3.5 px-4 font-bold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-bolt text-purple-500"></i>
                            <span>Power Tingkat Epic</span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-purple-100 text-purple-800 border border-purple-200">
                                Epic
                            </span>
                        </td>
                        <td class="py-3.5 px-4 font-bold text-purple-700">~15%</td>
                        <td class="py-3.5 px-4">2X Score (Ganda Poin), Second Chance (Kesempatan Kedua).</td>
                    </tr>
                    <tr>
                        <td class="py-3.5 px-4 font-bold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-skull-crossbones text-emerald-500"></i>
                            <span>Power Tingkat Rare</span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-100 text-emerald-800 border border-emerald-200">
                                Rare
                            </span>
                        </td>
                        <td class="py-3.5 px-4 font-bold text-emerald-700">~20%</td>
                        <td class="py-3.5 px-4">Double Side Knife (2.5x Poin Berisiko).</td>
                    </tr>
                    <tr>
                        <td class="py-3.5 px-4 font-bold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-clover text-blue-500"></i>
                            <span>Power Tingkat Common</span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-blue-100 text-blue-800 border border-blue-200">
                                Common
                            </span>
                        </td>
                        <td class="py-3.5 px-4 font-bold text-blue-700">~62%</td>
                        <td class="py-3.5 px-4">50:50 (Hapus 2 Opsi Salah), Lucky Clover (Bocoran Kata Kunci).</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- 3. Cara Mendapatkan Koin --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-3xl border border-slate-200/90 p-6 shadow-xs space-y-4">
            <h3 class="text-base font-extrabold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-wallet text-amber-500"></i>
                <span>Cara Mengumpulkan Koin</span>
            </h3>
            <ul class="space-y-3 text-xs text-slate-600 font-medium">
                <li class="flex items-start gap-2.5">
                    <div class="w-6 h-6 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs font-bold shrink-0 mt-0.5 border border-amber-200">
                        1
                    </div>
                    <span><strong>Mengerjakan Kuis Petualangan:</strong> Menyelesaikan level kuis dengan ketelitian tinggi memberikan hadiah koin langsung ke saldo akunmu.</span>
                </li>
                <li class="flex items-start gap-2.5">
                    <div class="w-6 h-6 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs font-bold shrink-0 mt-0.5 border border-amber-200">
                        2
                    </div>
                    <span><strong>Mempertahankan Bintang 3:</strong> Nilai kuis di atas 85 menghasilkan bonus koin ekstra dibandingkan bintang standar.</span>
                </li>
                <li class="flex items-start gap-2.5">
                    <div class="w-6 h-6 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs font-bold shrink-0 mt-0.5 border border-amber-200">
                        3
                    </div>
                    <span><strong>Misi Harian & Tugas:</strong> Menyelesaikan materi pembelajaran dan penugasan guru tepat waktu dapat menambah pundi-pundi koinmu.</span>
                </li>
            </ul>
        </div>

        {{-- Aturan Kompensasi Banner Duplikat --}}
        <div class="bg-gradient-to-br from-amber-500/10 via-yellow-500/10 to-transparent rounded-3xl border border-amber-300/60 p-6 shadow-xs space-y-4">
            <h3 class="text-base font-extrabold text-amber-900 flex items-center gap-2">
                <i class="fa-solid fa-gift text-amber-600"></i>
                <span>Aturan Anti Duplikat & Kompensasi</span>
            </h3>
            <p class="text-xs text-amber-950 font-medium leading-relaxed">
                Kamu <strong>tidak akan pernah</strong> mendapatkan Banner GIF yang sama dua kali. Sistem akan selalu memprioritaskan Banner GIF yang belum pernah kamu miliki.
            </p>
            <div class="p-3.5 rounded-2xl bg-white/80 border border-amber-200 text-xs text-amber-900 space-y-1">
                <strong class="font-bold flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-check text-emerald-600"></i>
                    <span>Jika Seluruh Banner Sudah Dimiliki?</span>
                </strong>
                <p class="text-[11px] text-slate-600 leading-relaxed">
                    Sistem otomatis memberikan bonus kompensasi berupa Power Item langka <strong>2X Score</strong> ekstra setiap kali memicu Pity!
                </p>
            </div>
        </div>
    </div>

    {{-- 4. Preview Koleksi Banner yang Bisa Didapatkan --}}
    <div class="bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-8 shadow-xs space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h2 class="text-lg md:text-xl font-black text-slate-800 tracking-tight flex items-center gap-2.5">
                    <i class="fa-solid fa-images text-indigo-600"></i>
                    <span>Koleksi Banner GIF yang Tersedia di Wish</span>
                </h2>
                <p class="text-xs md:text-sm text-slate-500 font-medium mt-0.5">
                    Banner yang berhasil didapatkan akan langsung tersimpan di lemari profil akunmu selamanya.
                </p>
            </div>
            <a href="{{ route('siswa.gacha') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700 flex items-center gap-1 self-start sm:self-auto">
                <span>Coba Wish Sekarang</span>
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach(collect($banners)->filter(fn($b) => !empty($b['gif_url']))->take(8) as $b)
                <div class="rounded-2xl border border-slate-200 p-3.5 space-y-2 bg-slate-50/50">
                    <div class="h-24 rounded-xl overflow-hidden bg-slate-950 relative border border-slate-200">
                        <img src="{{ asset($b['gif_url']) }}" alt="{{ $b['nama'] }}" class="w-full h-full object-cover">
                        <span class="absolute top-2 left-2 px-2 py-0.5 rounded-md text-[9px] font-black uppercase text-white shadow-xs backdrop-blur-md {{ $b['rarity'] === 'legendary' ? 'bg-amber-500' : ($b['rarity'] === 'epic' ? 'bg-purple-600' : 'bg-blue-600') }}">
                            {{ $b['badge'] ?? $b['rarity'] }}
                        </span>
                    </div>
                    <div>
                        <h4 class="text-xs font-extrabold text-slate-800 truncate">{{ $b['nama'] }}</h4>
                        <p class="text-[11px] text-slate-500 line-clamp-1 mt-0.5">{{ $b['deskripsi'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
