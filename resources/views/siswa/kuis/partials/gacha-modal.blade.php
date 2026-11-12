{{-- ========================================================================= --}}
{{-- MODAL & POPUP SISTEM GACHA POWER & BANNER GIF SISWA (LEARNPOINT)           --}}
{{-- ========================================================================= --}}
<div 
    x-data="gachaSystem()" 
    @open-gacha-modal.window="openModal()"
    class="relative"
>
    <!-- Modal Utama Gacha -->
    <div 
        x-show="gachaModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto"
        role="dialog"
    >
        <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs" @click="closeModal()"></div>

        <div class="relative w-full max-w-xl bg-white rounded-3xl border border-slate-200 shadow-2xl p-6 sm:p-8 z-10 space-y-6 overflow-hidden my-6">
            
            {{-- Header Gacha --}}
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-amber-500 to-yellow-400 text-white flex items-center justify-center text-xl shadow-md shadow-amber-500/20 shrink-0">
                        <i class="fa-solid fa-box-open"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-800 tracking-tight">Peti Misteri Antariksa</h3>
                        <p class="text-xs text-slate-500 font-medium mt-0.5">Dapatkan Item Power & Peluang Hadiah Jackpot Banner GIF!</p>
                    </div>
                </div>
                <button type="button" @click="closeModal()" class="text-slate-400 hover:text-slate-600 p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            {{-- Saldo Koin & Pity Bar Status --}}
            <div class="bg-gradient-to-r from-blue-50/80 via-indigo-50/70 to-blue-50/80 border border-blue-200/80 rounded-2xl p-4.5 space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-coins text-amber-500 text-base"></i>
                        <span class="text-xs font-bold text-slate-600">Saldo Koin Kamu:</span>
                        <strong class="text-sm font-extrabold text-amber-600" x-text="koin + ' Koin'"></strong>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-blue-600 text-white shadow-2xs">
                        Pity: <span x-text="pityCounter"></span> / 30
                    </span>
                </div>

                {{-- Progress Bar Pity 30x --}}
                <div class="space-y-1">
                    <div class="w-full h-2.5 bg-slate-200/80 rounded-full overflow-hidden">
                        <div 
                            class="h-full bg-gradient-to-r from-amber-400 via-orange-500 to-rose-500 rounded-full transition-all duration-300"
                            :style="'width: ' + ((pityCounter / 30) * 100) + '%'"
                        ></div>
                    </div>
                    <div class="flex items-center justify-between text-[10px] font-semibold text-slate-500">
                        <span>Pasti Dapat Banner GIF di Tarikan ke-30</span>
                        <span x-text="(30 - pityCounter) + ' Tarikan Lagi!'"></span>
                    </div>
                </div>
            </div>

            {{-- Banner Preview Teaser --}}
            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/70 flex items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-2.5 text-slate-600">
                    <i class="fa-solid fa-gift text-purple-600 text-sm"></i>
                    <span>Setiap tarikan <strong>100% pasti mendapat Power</strong> + peluang dapat <strong>Banner GIF Dashboard</strong>!</span>
                </div>
            </div>

            {{-- Action Buttons: 1x Roll & 10x Roll --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 pt-1">
                {{-- 1x Gacha --}}
                <button 
                    type="button" 
                    @click="doGacha(1)"
                    :disabled="loading || koin < 20"
                    class="p-4 rounded-2xl border-2 font-extrabold text-xs flex flex-col items-center justify-center gap-1.5 transition cursor-pointer"
                    :class="koin >= 20 
                        ? 'border-blue-600 bg-white hover:bg-blue-50/50 text-blue-700 shadow-sm active:scale-98' 
                        : 'border-slate-200 bg-slate-100 text-slate-400 cursor-not-allowed opacity-60'"
                >
                    <span class="flex items-center gap-1.5 text-sm">
                        <i class="fa-solid fa-cube"></i> Gacha 1x
                    </span>
                    <span class="text-xs font-bold text-amber-600 flex items-center gap-1">
                        <i class="fa-solid fa-coins text-[11px]"></i> 20 Koin
                    </span>
                </button>

                {{-- 10x Gacha (Hemat) --}}
                <button 
                    type="button" 
                    @click="doGacha(10)"
                    :disabled="loading || koin < 180"
                    class="p-4 rounded-2xl font-extrabold text-xs flex flex-col items-center justify-center gap-1.5 transition cursor-pointer relative shadow-md shadow-blue-500/20"
                    :class="koin >= 180 
                        ? 'bg-[#2563EB] hover:bg-[#1D4ED8] text-white active:scale-98' 
                        : 'bg-slate-200 text-slate-400 cursor-not-allowed opacity-60'"
                >
                    <span class="absolute -top-2.5 right-3 px-2 py-0.5 rounded-full text-[9px] font-black bg-rose-500 text-white uppercase tracking-wider">
                        Diskon 10%
                    </span>
                    <span class="flex items-center gap-1.5 text-sm">
                        <i class="fa-solid fa-cubes"></i> Gacha 10x Sekaligus
                    </span>
                    <span class="text-xs font-bold text-amber-300 flex items-center gap-1">
                        <i class="fa-solid fa-coins text-[11px]"></i> 180 Koin <span class="line-through text-white/50 text-[10px] ml-0.5">200</span>
                    </span>
                </button>
            </div>

            <div x-show="loading" class="text-center py-4 space-y-2">
                <i class="fa-solid fa-spinner fa-spin text-2xl text-blue-600"></i>
                <p class="text-xs font-bold text-slate-600">Membuka Peti Antariksa...</p>
            </div>
        </div>
    </div>

    <!-- Modal Pop-up Hasil Tarikan Gacha -->
    <div 
        x-show="resultModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto"
        role="dialog"
    >
        <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="resultModalOpen = false"></div>

        <div class="relative w-full max-w-2xl bg-white rounded-3xl border border-slate-200 shadow-2xl p-6 sm:p-8 z-10 space-y-5 overflow-hidden my-6 text-center">
            
            <div class="space-y-1">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl shadow-xs">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                </div>
                <h3 class="text-xl font-black text-slate-900 tracking-tight">Selamat! Hasil Gacha Kamu:</h3>
                <p class="text-xs text-slate-500 font-medium">Item telah otomatis ditambahkan ke Tas Power & Koleksi Banner-mu.</p>
            </div>

            {{-- Grid Item Hasil Gacha --}}
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 max-h-[50vh] overflow-y-auto p-1">
                <template x-for="(res, idx) in lastPullResults" :key="idx">
                    <div class="rounded-2xl border p-3 flex flex-col items-center justify-between text-center gap-2 bg-slate-50/80 shadow-2xs hover:scale-102 transition">
                        <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-lg shadow-2xs border border-slate-200 text-blue-600">
                            <i :class="res.power.icon"></i>
                        </div>
                        <div>
                            <span class="text-[9px] font-black uppercase tracking-wider block"
                                  :class="res.power.rarity === 'epic' ? 'text-amber-600' : (res.power.rarity === 'rare' ? 'text-emerald-600' : 'text-blue-600')" 
                                  x-text="res.power.rarity"></span>
                            <h4 class="text-xs font-black text-slate-800 line-clamp-1 leading-snug" x-text="res.power.nama"></h4>
                            <span class="text-[10px] font-bold text-slate-400 block">+1 (Total: <span x-text="res.power.new_qty"></span>)</span>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Jackpot Banner GIF jika beruntung dapat --}}
            <template x-if="wonBannersInLastPull.length > 0">
                <div class="space-y-2 pt-2">
                    <template x-for="(wb, bIdx) in wonBannersInLastPull" :key="bIdx">
                        <div class="p-4 rounded-2xl bg-gradient-to-r from-amber-50 via-yellow-50 to-amber-50 border-2 border-amber-300 text-left flex items-center gap-4 animate-pulse">
                            <div class="w-12 h-12 rounded-xl bg-amber-500 text-white flex items-center justify-center text-xl shrink-0 shadow-md shadow-amber-500/20">
                                <i class="fa-solid fa-crown"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-500 text-white uppercase tracking-wider">
                                    JACKPOT BANNER GIF BARU!
                                </span>
                                <h4 class="text-sm font-extrabold text-slate-900 mt-0.5 truncate" x-text="wb.nama"></h4>
                                <p class="text-xs text-slate-600 line-clamp-1" x-text="wb.deskripsi"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-3 text-xs">
                <div class="font-bold text-slate-600">
                    Sisa Koin: <span class="text-amber-600 font-extrabold" x-text="koin"></span>
                </div>
                <button type="button" @click="resultModalOpen = false" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold transition cursor-pointer">
                    Selesai
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function gachaSystem() {
    return {
        gachaModalOpen: false,
        resultModalOpen: false,
        loading: false,
        koin: {{ (int) (auth()->user()?->siswa?->koin ?? 0) }},
        pityCounter: {{ (int) (auth()->user()?->siswa?->pity_gacha ?? 0) }},
        lastPullResults: [],
        wonBannersInLastPull: [],

        openModal() {
            this.gachaModalOpen = true;
        },

        closeModal() {
            this.gachaModalOpen = false;
        },

        doGacha(rolls) {
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
                    this.lastPullResults = data.items || [];
                    this.wonBannersInLastPull = (data.items || [])
                        .map(item => item.banner)
                        .filter(Boolean);

                    this.gachaModalOpen = false;
                    this.resultModalOpen = true;

                    // Update indikator koin di header jika ada
                    const koinEl = document.getElementById('navbarKoinDisplay');
                    if (koinEl) {
                        koinEl.innerText = this.koin;
                    }
                } else {
                    alert(data.message || 'Gacha gagal.');
                }
            })
            .catch(() => {
                this.loading = false;
                alert('Terjadi kesalahan jaringan.');
            });
        }
    };
}
</script>

