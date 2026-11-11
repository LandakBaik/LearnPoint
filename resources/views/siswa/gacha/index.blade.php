@extends('layouts.siswa')

@section('title', 'Wish Antariksa - Gacha Power & Banner - LearnPoint')

@push('styles')
<style>
    /* Styling Animasi & Efek Genshin Impact Wish */
    @keyframes starPulse {
        0%, 100% { opacity: 0.8; transform: scale(1); }
        50% { opacity: 1; transform: scale(1.05); filter: drop-shadow(0 0 15px rgba(251, 191, 36, 0.8)); }
    }
    .animate-star-pulse {
        animation: starPulse 3s infinite ease-in-out;
    }

    @keyframes meteorStreak {
        0% { transform: translateY(-100%) translateX(100%) rotate(45deg); opacity: 0; }
        20% { opacity: 1; }
        100% { transform: translateY(200%) translateX(-200%) rotate(45deg); opacity: 0; }
    }
    .meteor-effect {
        animation: meteorStreak 1.8s infinite linear;
    }

    /* Rarity Card Gradients */
    .rarity-epic-card {
        background: linear-gradient(145deg, #1e1b4b 0%, #312e81 40%, #d97706 100%);
        border-color: #f59e0b;
        box-shadow: 0 0 25px rgba(245, 158, 11, 0.35);
    }
    .rarity-rare-card {
        background: linear-gradient(145deg, #064e3b 0%, #065f46 50%, #10b981 100%);
        border-color: #34d399;
        box-shadow: 0 0 20px rgba(52, 211, 153, 0.3);
    }
    .rarity-common-card {
        background: linear-gradient(145deg, #0f172a 0%, #1e293b 60%, #3b82f6 100%);
        border-color: #60a5fa;
        box-shadow: 0 0 15px rgba(96, 165, 250, 0.25);
    }
</style>
@endpush

@section('content')
<div x-data="genshinWishSystem()" class="space-y-6 pb-16 select-none">

    {{-- 1. TOP HEADER & CURRENCY BAR (Genshin Style) --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900/90 border border-slate-800 rounded-3xl p-5 shadow-xl backdrop-blur-md">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-amber-500 via-orange-500 to-yellow-400 text-white flex items-center justify-center text-xl shadow-lg shadow-amber-500/30 shrink-0">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-xl font-black text-white tracking-tight leading-tight">
                        Wish Petualangan Antariksa
                    </h1>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-500/20 text-amber-300 border border-amber-500/30">
                        Space Horizon Banner
                    </span>
                </div>
                <p class="text-xs text-slate-400 font-medium mt-0.5">
                    Tukarkan Koin hasil ketelitianmu untuk memanggil Power legendaris & Banner GIF Dashboard eksklusif!
                </p>
            </div>
        </div>

        {{-- Sisi Kanan: Saldo Koin & Pity Counter --}}
        <div class="flex items-center gap-3 shrink-0 self-start sm:self-auto flex-wrap">
            {{-- Counter Pity --}}
            <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl px-4 py-2 text-center">
                <div class="text-[9px] font-extrabold uppercase tracking-widest text-slate-400">PITY BANNER</div>
                <div class="text-xs font-black text-amber-400">
                    <span x-text="pityCounter"></span> <span class="text-slate-500">/ 30</span>
                </div>
            </div>

            {{-- Saldo Koin --}}
            <div class="bg-gradient-to-r from-amber-500/20 to-yellow-500/20 border border-amber-500/40 rounded-2xl px-4 py-2 flex items-center gap-2.5 shadow-inner">
                <div class="w-7 h-7 rounded-full bg-amber-400 text-amber-950 flex items-center justify-center text-xs font-black shadow-xs">
                    <i class="fa-solid fa-coins"></i>
                </div>
                <div>
                    <div class="text-[9px] font-extrabold uppercase tracking-widest text-amber-300/80">Koin Kamu</div>
                    <div class="text-sm font-black text-white" x-text="koin"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. MAIN BANNER SHOWCASE (Genshin Wish Landscape Card) --}}
    <div class="relative overflow-hidden rounded-[32px] border-2 border-slate-700/80 bg-slate-950 shadow-2xl min-h-[460px] md:min-h-[500px] flex flex-col justify-between p-6 sm:p-10">
        
        {{-- Background GIF Galaksi Ruang Angkasa --}}
        <img 
            src="{{ asset('images/Banner/space-hero.gif') }}" 
            alt="Space Hero GIF" 
            class="absolute inset-0 w-full h-full object-cover pointer-events-none select-none z-0 opacity-60 brightness-90 contrast-110"
        >
        {{-- Vignette & Deep Space Gradient Overlay --}}
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/40 to-slate-950/70 z-0"></div>
        <div class="absolute inset-0 bg-radial from-transparent via-slate-950/30 to-slate-950/80 z-0"></div>

        {{-- Top Info Banner --}}
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-start justify-between gap-4">
            <div class="space-y-1.5 max-w-lg">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-900/80 border border-amber-400/50 backdrop-blur-md shadow-sm">
                    <i class="fa-solid fa-star text-amber-400 text-xs animate-star-pulse"></i>
                    <span class="text-xs font-black text-amber-300 uppercase tracking-wider">Garansi Hadiah Legendaris</span>
                </div>
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-black text-white tracking-tight drop-shadow-md">
                    Space Horizon
                </h2>
                <p class="text-xs sm:text-sm text-slate-200 font-medium leading-relaxed drop-shadow-sm max-w-md">
                    Dapatkan item Power untuk mendominasi Kuis Petualangan serta peluang meraih <strong>Banner GIF Bergerak Eksklusif</strong> untuk profil dashboard-mu!
                </p>
            </div>

            {{-- Rarity Rate Teaser Card --}}
            <div class="bg-slate-900/80 border border-slate-700/70 backdrop-blur-md rounded-2xl p-3.5 space-y-2 text-xs text-slate-300 max-w-xs shrink-0">
                <div class="font-extrabold text-white text-[11px] uppercase tracking-wider flex items-center justify-between border-b border-slate-700 pb-1.5">
                    <span>Rate Peluang Item</span>
                    <span class="text-amber-400 font-black">Pity: 30x Guaranteed</span>
                </div>
                <div class="space-y-1 text-[11px]">
                    <div class="flex items-center justify-between">
                        <span class="text-amber-400 font-bold flex items-center gap-1.5">
                            <i class="fa-solid fa-star text-[10px]"></i> Banner GIF & 2X Score
                        </span>
                        <span class="font-bold text-white">Epic</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-emerald-400 font-bold flex items-center gap-1.5">
                            <i class="fa-solid fa-shield-heart text-[10px]"></i> Second Chance & Knife
                        </span>
                        <span class="font-bold text-white">Rare</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-blue-400 font-bold flex items-center gap-1.5">
                            <i class="fa-solid fa-clover text-[10px]"></i> Clover & Remove Wrong
                        </span>
                        <span class="font-bold text-white">Common</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Middle Floating Star Particle Element --}}
        <div class="relative z-10 py-6 my-auto text-center pointer-events-none">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-slate-900/70 border border-white/20 text-slate-300 text-xs backdrop-blur-md shadow-lg">
                <i class="fa-solid fa-circle-check text-emerald-400"></i>
                <span>Setiap tarikan <strong>100% PASTI</strong> menghasilkan 1 Item Power!</span>
            </div>
        </div>

        {{-- Bottom Wish Action Buttons (Genshin Style Bottom Bar) --}}
        <div class="relative z-10 pt-4 flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-slate-800/80">
            {{-- Pity Progress Bar Visual --}}
            <div class="w-full sm:max-w-xs space-y-1.5">
                <div class="flex items-center justify-between text-[11px] font-bold text-slate-300">
                    <span>Garansi Banner GIF</span>
                    <span class="text-amber-400 font-black"><span x-text="pityCounter"></span> / 30 Wish</span>
                </div>
                <div class="w-full h-2.5 bg-slate-800/90 rounded-full overflow-hidden border border-slate-700">
                    <div 
                        class="h-full bg-gradient-to-r from-amber-400 via-orange-500 to-rose-500 rounded-full transition-all duration-500"
                        :style="'width: ' + ((pityCounter / 30) * 100) + '%'"
                    ></div>
                </div>
            </div>

            {{-- Wish Buttons (1x & 10x) --}}
            <div class="flex items-center gap-3.5 w-full sm:w-auto">
                {{-- Wish 1x --}}
                <button 
                    type="button" 
                    @click="executeWish(1)"
                    :disabled="loading || koin < 20"
                    class="flex-1 sm:flex-none px-6 py-3.5 rounded-2xl border-2 font-black text-xs sm:text-sm transition-all duration-200 flex flex-col items-center justify-center cursor-pointer shadow-lg"
                    :class="koin >= 20 
                        ? 'border-amber-400/80 bg-gradient-to-b from-slate-800 to-slate-900 hover:border-amber-300 hover:from-slate-700 hover:to-slate-800 text-white active:scale-95 shadow-amber-500/10' 
                        : 'border-slate-700 bg-slate-900 text-slate-500 cursor-not-allowed opacity-60'"
                >
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-wand-magic-sparkles text-amber-400"></i> Wish ×1
                    </span>
                    <span class="text-[11px] font-extrabold text-amber-300 flex items-center gap-1 mt-0.5">
                        <i class="fa-solid fa-coins text-[10px]"></i> 20 Koin
                    </span>
                </button>

                {{-- Wish 10x --}}
                <button 
                    type="button" 
                    @click="executeWish(10)"
                    :disabled="loading || koin < 180"
                    class="flex-1 sm:flex-none px-7 py-3.5 rounded-2xl border-2 font-black text-xs sm:text-sm transition-all duration-200 flex flex-col items-center justify-center cursor-pointer shadow-xl relative"
                    :class="koin >= 180 
                        ? 'border-amber-300 bg-gradient-to-b from-amber-500 via-orange-500 to-amber-600 hover:brightness-110 text-white active:scale-95 shadow-orange-500/30' 
                        : 'border-slate-700 bg-slate-900 text-slate-500 cursor-not-allowed opacity-60'"
                >
                    <span class="absolute -top-3 right-3 px-2 py-0.5 rounded-full text-[9px] font-black bg-rose-600 text-white uppercase tracking-wider shadow-sm">
                        Hemat 20 Koin
                    </span>
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-meteor text-yellow-200"></i> Wish ×10
                    </span>
                    <span class="text-[11px] font-extrabold text-amber-100 flex items-center gap-1 mt-0.5">
                        <i class="fa-solid fa-coins text-[10px]"></i> 180 Koin <span class="line-through text-white/50 text-[10px] ml-0.5">200</span>
                    </span>
                </button>
            </div>
        </div>
    </div>

    {{-- 3. KOLEKSI BANNER DASHBOARD (DIPASANG / TERBUKA / TERKUNCI) --}}
    <div class="bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-8 shadow-xs space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-slate-100">
            <div>
                <h3 class="text-base sm:text-lg font-extrabold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-palette text-blue-600"></i>
                    <span>Koleksi Banner GIF Dashboard Kamu</span>
                </h3>
                <p class="text-xs text-slate-400 font-medium mt-0.5">
                    Banner yang berhasil kamu dapatkan dari Wish akan terbuka di sini dan dapat dipasang ke dashboard kapan saja.
                </p>
            </div>
            <a href="{{ route('siswa.dashboard') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700 flex items-center gap-1 self-start sm:self-auto">
                <span>Lihat Dashboard</span>
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($banners as $b)
                <div 
                    class="rounded-3xl border-2 p-4 flex flex-col justify-between gap-4 transition-all relative overflow-hidden group
                        {{ $b['is_equipped'] ? 'border-blue-500 bg-blue-50/40 shadow-sm' : ($b['is_unlocked'] ? 'border-slate-200 bg-white hover:border-blue-300' : 'border-slate-100 bg-slate-50/70 opacity-60') }}"
                >
                    {{-- Pratinjau Gambar Banner --}}
                    <div class="h-28 rounded-2xl overflow-hidden relative bg-slate-900 flex items-center justify-center border border-slate-200">
                        @if(!empty($b['gif_url']))
                            <img src="{{ asset($b['gif_url']) }}" alt="{{ $b['nama'] }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-gradient-to-r from-blue-600 to-indigo-600 flex items-center justify-center text-white font-bold text-xs gap-2">
                                <i class="fa-solid fa-book-open"></i>
                                <span>Tema Bawaan</span>
                            </div>
                        @endif

                        {{-- Rarity Badge --}}
                        <span class="absolute top-2.5 left-2.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider shadow-sm backdrop-blur-md
                            {{ $b['rarity'] === 'legendary' ? 'bg-gradient-to-r from-amber-500 to-yellow-400 text-white shadow-xs' : ($b['rarity'] === 'epic' ? 'bg-purple-600/90 text-white' : ($b['rarity'] === 'rare' ? 'bg-blue-600/90 text-white' : 'bg-slate-800/80 text-white')) }}">
                            {{ $b['badge'] }}
                        </span>

                        @if($b['is_equipped'])
                            <span class="absolute top-2.5 right-2.5 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-blue-600 text-white shadow-sm flex items-center gap-1">
                                <i class="fa-solid fa-check text-[9px]"></i> Terpasang
                            </span>
                        @endif
                    </div>

                    {{-- Informasi Banner --}}
                    <div>
                        <h4 class="text-sm font-extrabold text-slate-800">{{ $b['nama'] }}</h4>
                        <p class="text-xs text-slate-500 line-clamp-2 mt-1 leading-relaxed">{{ $b['deskripsi'] }}</p>
                    </div>

                    {{-- Tombol Pasang --}}
                    <div>
                        @if($b['is_equipped'])
                            <button type="button" disabled class="w-full py-2.5 rounded-xl bg-blue-600 text-white text-xs font-bold cursor-default shadow-xs">
                                Sedang Digunakan
                            </button>
                        @elseif($b['is_unlocked'])
                            <button 
                                type="button" 
                                @click="equipBanner('{{ $b['kode'] }}')"
                                class="w-full py-2.5 rounded-xl bg-slate-100 hover:bg-blue-600 hover:text-white text-slate-700 text-xs font-bold transition cursor-pointer border border-slate-200"
                            >
                                Pasang ke Dashboard
                            </button>
                        @else
                            <div class="w-full py-2.5 rounded-xl bg-slate-100 text-slate-400 text-xs font-bold text-center flex items-center justify-center gap-1.5 border border-slate-200">
                                <i class="fa-solid fa-lock text-[10px]"></i> Belum Terbuka (via Wish)
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL POP-UP ANIMASI & HASIL WISH (GENSHIN IMPACT REVEAL SCREEN)         --}}
    {{-- ========================================================================= --}}
    <div 
        x-show="showResultModal" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 overflow-y-auto"
        role="dialog"
    >
        {{-- Backdrop Gelap Berbintang --}}
        <div class="fixed inset-0 bg-slate-950/90 backdrop-blur-md" @click="showResultModal = false"></div>

        <div class="relative w-full max-w-4xl bg-slate-900 border-2 border-slate-700/80 rounded-[36px] shadow-2xl p-6 sm:p-10 z-10 space-y-6 my-6 overflow-hidden text-center">
            
            {{-- Header Hasil Reveal --}}
            <div class="space-y-1">
                <div class="w-16 h-16 mx-auto rounded-3xl bg-gradient-to-tr from-amber-400 to-yellow-300 text-amber-950 flex items-center justify-center text-3xl shadow-lg shadow-amber-400/20">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                </div>
                <h3 class="text-2xl sm:text-3xl font-black text-white tracking-tight pt-2">
                    Hasil Pemanggilan Wish
                </h3>
                <p class="text-xs sm:text-sm text-slate-400 font-medium">
                    Item Power & Banner berhasil didapatkan dan langsung tersimpan di akunmu!
                </p>
            </div>

            {{-- Grid Kartu Hasil Gacha (1x atau 10x) --}}
            <div 
                class="grid gap-3.5 max-h-[55vh] overflow-y-auto p-2"
                :class="wishResults.length === 1 ? 'grid-cols-1 max-w-xs mx-auto' : 'grid-cols-2 sm:grid-cols-5'"
            >
                <template x-for="(item, idx) in wishResults" :key="idx">
                    <div 
                        class="rounded-3xl border-2 p-4 flex flex-col items-center justify-between text-center gap-3 transition-transform hover:scale-105 duration-200"
                        :class="item.power.rarity === 'epic' ? 'rarity-epic-card' : (item.power.rarity === 'rare' ? 'rarity-rare-card' : 'rarity-common-card')"
                    >
                        {{-- Icon Power --}}
                        <div class="w-14 h-14 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center text-2xl text-white shadow-inner">
                            <i :class="item.power.icon"></i>
                        </div>

                        {{-- Metadata Power --}}
                        <div class="space-y-0.5">
                            <span 
                                class="text-[10px] font-black uppercase tracking-wider block"
                                :class="item.power.rarity === 'epic' ? 'text-amber-300' : (item.power.rarity === 'rare' ? 'text-emerald-300' : 'text-blue-300')"
                                x-text="item.power.rarity"
                            ></span>
                            <h4 class="text-xs sm:text-sm font-black text-white line-clamp-1" x-text="item.power.nama"></h4>
                            <span class="text-[11px] font-extrabold text-white/70 block">+1 Item</span>
                        </div>

                        {{-- Tagline --}}
                        <div class="text-[9px] font-semibold text-white/60 line-clamp-1 border-t border-white/10 pt-1.5 w-full" x-text="item.power.tagline"></div>
                    </div>
                </template>
            </div>

            {{-- Banner JACKPOT Reveal jika dapat Banner GIF --}}
            <template x-if="wonBanners.length > 0">
                <div class="space-y-3 pt-2">
                    <template x-for="(wb, bIdx) in wonBanners" :key="bIdx">
                        <div class="p-5 rounded-3xl bg-gradient-to-r from-amber-500/20 via-yellow-500/30 to-amber-500/20 border-2 border-amber-400 text-left flex items-center gap-5 shadow-xl animate-pulse">
                            <div class="w-14 h-14 rounded-2xl bg-amber-400 text-amber-950 flex items-center justify-center text-2xl shrink-0 shadow-lg shadow-amber-400/40">
                                <i class="fa-solid fa-crown"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-amber-400 text-amber-950 uppercase tracking-wider">
                                    JACKPOT! BANNER GIF BARU
                                </span>
                                <h4 class="text-base sm:text-lg font-black text-white mt-1 truncate" x-text="wb.nama"></h4>
                                <p class="text-xs text-amber-200 line-clamp-1" x-text="wb.deskripsi"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            {{-- Footer Action Modal --}}
            <div class="pt-4 border-t border-slate-800 flex items-center justify-between gap-4 text-xs font-bold text-slate-400">
                <div class="flex items-center gap-1.5">
                    <span>Sisa Koin:</span>
                    <strong class="text-amber-400 font-black text-sm" x-text="koin"></strong>
                </div>
                <button 
                    type="button" 
                    @click="showResultModal = false; window.location.reload();"
                    class="px-8 py-3 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-extrabold transition cursor-pointer shadow-lg shadow-blue-600/30"
                >
                    Selesai & Tutup
                </button>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function genshinWishSystem() {
    return {
        koin: {{ (int) ($siswa->koin ?? 0) }},
        pityCounter: {{ (int) ($siswa->pity_gacha ?? 0) }},
        loading: false,
        showResultModal: false,
        wishResults: [],
        wonBanners: [],

        executeWish(rolls) {
            if (this.loading) return;
            this.loading = true;

            fetch('{{ route('siswa.gacha.pull') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ rolls: rolls })
            })
            .then(res => res.json())
            .then(data => {
                this.loading = false;
                if (data.success) {
                    this.koin = data.sisa_koin;
                    this.pityCounter = data.pity_counter;
                    this.wishResults = data.items || [];
                    this.wonBanners = (data.items || [])
                        .map(item => item.banner)
                        .filter(Boolean);

                    this.showResultModal = true;
                } else {
                    alert(data.message || 'Wish gagal diproses.');
                }
            })
            .catch(() => {
                this.loading = false;
                alert('Terjadi kesalahan jaringan.');
            });
        },

        equipBanner(code) {
            fetch('{{ route('siswa.banner.equip') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ banner_code: code })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Gagal mengubah banner');
                }
            })
            .catch(() => alert('Terjadi kesalahan jaringan.'));
        }
    };
}
</script>
@endpush
@endsection

