{{-- ========================================================================= --}}
{{-- KOMPONEN PROTOTYPE FITUR POWER - SISTEM KUIS PETUALANGAN LEARNPOINT      --}}
{{-- File terpisah agar modular dan mudah dikembangkan ke backend permanen.   --}}
{{-- ========================================================================= --}}

{{-- 1. FLOATING TOAST FEEDBACK AKTIVASI / EFEK POWER --}}
<div 
    x-show="powerToast.visible"
    x-cloak
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
    x-transition:leave-end="opacity-0 -translate-y-4 scale-95"
    class="fixed top-20 left-1/2 -translate-x-1/2 z-50 w-[92%] max-w-md pointer-events-auto"
>
    <div 
        class="rounded-2xl p-4 shadow-xl border flex items-start gap-3.5 backdrop-blur-md"
        :class="{
            'bg-blue-600/95 text-white border-blue-400 shadow-blue-500/25': powerToast.type === 'activate',
            'bg-emerald-600/95 text-white border-emerald-400 shadow-emerald-500/25': powerToast.type === 'saved',
            'bg-amber-500/95 text-white border-amber-300 shadow-amber-500/25': powerToast.type === 'warning'
        }"
    >
        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-lg shrink-0 shadow-inner">
            <i :class="powerToast.icon || 'fa-solid fa-bolt'"></i>
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between gap-2">
                <h4 class="text-xs font-extrabold uppercase tracking-wider text-white/90" x-text="powerToast.title">
                    Power Diaktifkan!
                </h4>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-white/20 text-white" x-show="powerToast.badge" x-text="powerToast.badge"></span>
            </div>
            <p class="text-xs sm:text-sm font-bold text-white mt-0.5 leading-snug" x-text="powerToast.message"></p>
        </div>
        <button type="button" @click="powerToast.visible = false" class="text-white/70 hover:text-white p-1">
            <i class="fa-solid fa-xmark text-sm"></i>
        </button>
    </div>
</div>

{{-- 2. MODAL / PANEL DAFTAR POWER (DRAWER MODAL) --}}
<div 
    x-show="powerPanelOpen" 
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 overflow-y-auto"
    aria-labelledby="power-panel-title" 
    role="dialog" 
    aria-modal="true"
>
    {{-- Backdrop --}}
    <div 
        x-show="powerPanelOpen"
        x-transition:enter="transition-opacity ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="closePowerPanel()"
        class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs"
    ></div>

    {{-- Modal Content Card --}}
    <div 
        x-show="powerPanelOpen"
        x-transition:enter="transition ease-out duration-250"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        class="relative w-full max-w-2xl bg-white rounded-3xl border border-slate-200 shadow-2xl overflow-hidden z-10 my-8"
    >
        {{-- Panel Header --}}
        <div class="bg-[#EFF5FF] border-b border-[#DCE9FE] px-6 py-5 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-[#2563EB] text-white flex items-center justify-center text-xl shadow-md shadow-blue-500/20 shrink-0">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 id="power-panel-title" class="text-lg font-extrabold text-slate-900 tracking-tight">
                            Tas Power Petualangan
                        </h3>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-blue-600 text-white">
                            <span x-text="totalPowerRemaining()"></span> Item Tersedia
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">
                        Pilih Power untuk membantu menyelesaikan <strong class="text-slate-700" x-text="'Pertanyaan No. ' + (currentIndex + 1)"></strong>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button 
                    type="button" 
                    @click="closePowerPanel()"
                    class="w-9 h-9 rounded-xl bg-white hover:bg-slate-100 text-slate-500 flex items-center justify-center border border-slate-200 transition cursor-pointer"
                >
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        </div>

        {{-- Info Banner Status Soal Saat Ini --}}
        <div class="px-6 py-3 bg-slate-50 border-b border-slate-100 flex items-center justify-between gap-2 flex-wrap text-xs">
            <div class="flex items-center gap-2 text-slate-600 font-medium">
                <i class="fa-solid fa-circle-info text-blue-600"></i>
                <span>Maksimal <strong>1 Power per soal</strong>. Setiap aktivasi langsung mengurangi jumlah kepemilikan sebanyak 1.</span>
            </div>
            <template x-if="getActivePowersForCurrentQuestion().length > 0">
                <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 font-bold text-[11px]">
                    <span x-text="getActivePowersForCurrentQuestion()[0]?.name || '1 Power'"></span> Aktif di Soal Ini
                </span>
            </template>
        </div>

        {{-- Daftar Power Grid --}}
        <div class="p-6 max-h-[65vh] overflow-y-auto space-y-3.5">
            <template x-for="power in powers" :key="power.code || power.id">
                <div 
                    class="rounded-2xl border-2 p-4 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                    :class="{
                        'border-blue-500 bg-blue-50/40 shadow-xs': isPowerActiveOnCurrent(power.code || power.id),
                        'border-slate-200/90 bg-white hover:border-blue-300': !isPowerActiveOnCurrent(power.code || power.id) && canUsePower(power).allowed,
                        'border-slate-100 bg-slate-50/70 opacity-65': !isPowerActiveOnCurrent(power.code || power.id) && !canUsePower(power).allowed
                    }"
                >
                    {{-- Kiri: Ikon + Nama + Deskripsi Efek --}}
                    <div class="flex items-start gap-3.5 min-w-0">
                        <div 
                            class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl shrink-0 border shadow-2xs"
                            :class="power.qty > 0 ? power.iconBgClass : 'bg-slate-100 text-slate-400 border-slate-200'"
                        >
                            <i :class="power.icon"></i>
                        </div>

                        <div class="space-y-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h4 class="text-sm font-extrabold text-slate-900" x-text="power.name"></h4>

                                {{-- Badge Kategori / Efek Singkat --}}
                                <span 
                                    class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider border"
                                    :class="power.badgeClass"
                                    x-text="power.tagline"
                                ></span>

                                {{-- Badge Status Aktif di Soal Ini --}}
                                <template x-if="isPowerActiveOnCurrent(power.code || power.id)">
                                    <span class="px-2 py-0.5 rounded-full bg-blue-600 text-white text-[10px] font-extrabold flex items-center gap-1">
                                        <i class="fa-solid fa-check text-[9px]"></i> Aktif di Soal Ini
                                    </span>
                                </template>
                            </div>

                            <p class="text-xs text-slate-600 leading-relaxed" x-text="power.description"></p>

                            {{-- Catatan aturan khusus jika tidak dapat digunakan --}}
                            <template x-if="!canUsePower(power).allowed && !isPowerActiveOnCurrent(power.code || power.id)">
                                <div class="text-[11px] font-bold text-amber-700 flex items-center gap-1 pt-0.5">
                                    <i class="fa-solid fa-circle-exclamation text-[10px]"></i>
                                    <span x-text="canUsePower(power).reason"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Kanan: Jumlah Kepemilikan & Tombol Gunakan --}}
                    <div class="flex sm:flex-col items-center sm:items-end justify-between gap-2.5 shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                        <div class="flex items-center gap-1.5">
                            <span class="text-[11px] font-semibold text-slate-400">Dimiliki:</span>
                            <span 
                                class="px-2.5 py-0.5 rounded-lg text-xs font-extrabold border"
                                :class="power.qty > 0 ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-slate-100 text-slate-400 border-slate-200'"
                                x-text="power.qty + 'x'"
                            ></span>
                        </div>

                        <button 
                            type="button"
                            @click="promptConfirmPower(power)"
                            :disabled="!canUsePower(power).allowed"
                            class="px-4 py-2 rounded-xl font-extrabold text-xs flex items-center gap-1.5 transition"
                            :class="canUsePower(power).allowed 
                                ? 'bg-[#2563EB] hover:bg-[#1D4ED8] text-white shadow-xs cursor-pointer' 
                                : 'bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed'"
                        >
                            <template x-if="isPowerActiveOnCurrent(power.code || power.id)">
                                <span><i class="fa-solid fa-check mr-1"></i> Terpakai</span>
                            </template>
                            <template x-if="!isPowerActiveOnCurrent(power.code || power.id) && power.qty <= 0">
                                <span><i class="fa-solid fa-ban mr-1"></i> Habis</span>
                            </template>
                            <template x-if="!isPowerActiveOnCurrent(power.code || power.id) && power.qty > 0 && canUsePower(power).allowed">
                                <span>Gunakan <i class="fa-solid fa-bolt ml-1 text-[10px]"></i></span>
                            </template>
                            <template x-if="!isPowerActiveOnCurrent(power.code || power.id) && power.qty > 0 && !canUsePower(power).allowed">
                                <span>Terkunci</span>
                            </template>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        {{-- Panel Footer --}}
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between gap-4">
            <div class="text-xs text-slate-500 font-medium">
                Simulasi Poin Efek Power saat ini: 
                <strong class="font-extrabold" :class="calculatePowerScoreSummary().netBonus >= 0 ? 'text-blue-600' : 'text-rose-600'" x-text="(calculatePowerScoreSummary().netBonus >= 0 ? '+' : '') + calculatePowerScoreSummary().netBonus + ' Poin'"></strong>
            </div>
            <button 
                type="button" 
                @click="closePowerPanel()"
                class="px-5 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs transition cursor-pointer"
            >
                Tutup Panel
            </button>
        </div>
    </div>
</div>

{{-- 3. MODAL KONFIRMASI PENGGUNAAN POWER --}}
<div 
    x-show="confirmingPower !== null" 
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    role="dialog" 
    aria-modal="true"
>
    {{-- Backdrop --}}
    <div 
        x-show="confirmingPower !== null"
        x-transition:enter="transition-opacity ease-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        @click="cancelConfirmPower()"
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs"
    ></div>

    {{-- Card Konfirmasi --}}
    <template x-if="confirmingPower">
        <div 
            x-show="confirmingPower !== null"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            class="relative w-full max-w-md bg-white rounded-3xl border border-slate-200 p-6 shadow-2xl z-10 text-center space-y-5"
        >
            {{-- Icon Power --}}
            <div 
                class="w-16 h-16 mx-auto rounded-2xl flex items-center justify-center text-2xl border shadow-sm"
                :class="confirmingPower.iconBgClass"
            >
                <i :class="confirmingPower.icon"></i>
            </div>

            <div class="space-y-1.5">
                <span class="px-3 py-0.5 rounded-full text-[11px] font-extrabold bg-blue-50 text-blue-700 border border-blue-200">
                    Konfirmasi Aktivasi Power • Soal No. <span x-text="currentIndex + 1"></span>
                </span>
                <h3 class="text-xl font-black text-slate-900" x-text="'Gunakan ' + confirmingPower.name + '?'"></h3>
                <p class="text-xs text-slate-600 leading-relaxed" x-text="confirmingPower.description"></p>
            </div>

            {{-- Kotak Ringkasan Efek & Pengurangan Jumlah --}}
            <div class="bg-[#F8FAFC] border border-slate-200/80 rounded-2xl p-4 text-left space-y-2.5 text-xs">
                <div class="flex items-start gap-2.5">
                    <i class="fa-solid fa-wand-magic-sparkles text-blue-600 mt-0.5"></i>
                    <div>
                        <span class="font-bold text-slate-700 block">Efek pada Soal Ini:</span>
                        <span class="text-slate-600" x-text="confirmingPower.effectDetail"></span>
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-200/70 flex items-center justify-between font-bold">
                    <span class="text-slate-500">Perubahan Jumlah Kepemilikan:</span>
                    <div class="flex items-center gap-1.5">
                        <span class="px-2 py-0.5 rounded-md bg-slate-200 text-slate-700" x-text="confirmingPower.qty + 'x'"></span>
                        <i class="fa-solid fa-arrow-right text-[10px] text-slate-400"></i>
                        <span class="px-2 py-0.5 rounded-md bg-blue-600 text-white" x-text="(confirmingPower.qty - 1) + 'x'"></span>
                    </div>
                </div>
            </div>

            {{-- Tombol Aksi Konfirmasi --}}
            <div class="flex items-center gap-3 pt-1">
                <button 
                    type="button" 
                    @click="cancelConfirmPower()"
                    :disabled="powerActionLoading"
                    class="flex-1 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition cursor-pointer disabled:opacity-50"
                >
                    Batal
                </button>
                <button 
                    type="button" 
                    @click="confirmAndActivatePower()"
                    :disabled="powerActionLoading"
                    class="flex-1 py-3 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-extrabold text-xs flex items-center justify-center gap-2 shadow-md shadow-blue-500/20 transition cursor-pointer disabled:opacity-60"
                >
                    <i class="fa-solid" :class="powerActionLoading ? 'fa-spinner fa-spin' : 'fa-bolt'"></i>
                    <span x-text="powerActionLoading ? 'Mengaktifkan...' : 'Ya, Gunakan Power'"></span>
                </button>
            </div>
        </div>
    </template>
</div>

