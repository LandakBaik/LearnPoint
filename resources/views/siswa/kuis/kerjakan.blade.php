@extends('layouts.siswa')

@section('title', 'Mengerjakan: ' . $quiz->judul . ' - LearnPoint')

@push('styles')
<style>
    /* Styling khusus pengerjaan kuis */
    .option-card input[type="radio"]:checked + label {
        border-color: #2563eb;
        background-color: #eff6ff;
    }
    .option-card input[type="radio"]:checked + label .option-badge {
        background-color: #2563eb;
        color: #ffffff;
        border-color: #2563eb;
    }
    .option-card input[type="radio"]:disabled + label {
        cursor: not-allowed;
    }
</style>
@endpush

@section('content')
@php
    // Siapkan metadata soal untuk mekanisme Power (tanpa menyertakan kunci jawaban ke client)
    $powerQuestionsMeta = [];
    $powerQuestionIds = [];
    foreach ($soals as $s) {
        $pilihanMap = $s->pilihan_shuffled ?? $s->pilihan ?? [];
        $powerQuestionIds[] = (int) $s->id;
        $powerQuestionsMeta[$s->id] = [
            'id' => (int) $s->id,
            'bobot' => (float) ($s->bobot ?: 10),
            'optionKeys' => is_array($pilihanMap) ? array_map('strtolower', array_keys($pilihanMap)) : ['a', 'b', 'c', 'd'],
            'options' => is_array($pilihanMap) ? $pilihanMap : [],
        ];
    }
@endphp

<div x-data="quizPlayer()" x-init="initTimer(); initPowerBackend()" class="max-w-5xl mx-auto space-y-6 pb-12">

    {{-- Komponen Modal & Toast Power (Terpisah di Partial) --}}
    @include('siswa.kuis.partials.power-panel')

    {{-- 1. Sticky Header Bar Pengerjaan --}}
    <div class="sticky top-16 z-30 bg-white/95 backdrop-blur-md p-4 rounded-2xl border border-slate-200/90 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        {{-- Info Kuis & Mapel --}}
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center text-lg font-bold shadow-md shadow-blue-500/20 shrink-0">
                <i class="fa-solid fa-gamepad"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-sm md:text-base font-extrabold text-slate-800 leading-tight truncate max-w-xs md:max-w-md">
                        {{ $quiz->judul }}
                    </h2>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-blue-50 text-blue-700 border border-blue-200">
                        {{ $selectedArc ?? 'Mudah' }} • Level {{ $selectedLevel ?? 1 }}
                    </span>
                </div>
                <div class="flex items-center gap-2 mt-0.5 text-xs text-slate-400">
                    <span class="font-semibold text-blue-600">{{ $quiz->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? 'Mata Pelajaran' }}</span>
                    <span>•</span>
                    <span>Total <strong class="text-slate-700" x-text="totalQuestions"></strong> Soal pada Level Ini</span>
                </div>
            </div>
        </div>

        {{-- Live Timer, Tombol Power & Progress Summary --}}
        <div class="flex items-center justify-between md:justify-end gap-2.5 flex-wrap shrink-0">
            {{-- TOMBOL POWER UTAMA --}}
            <button 
                type="button" 
                @click="openPowerPanel()"
                class="flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-xs font-extrabold transition cursor-pointer shadow-sm shadow-blue-500/20"
                title="Buka Tas Power Petualangan"
            >
                <i class="fa-solid fa-wand-magic-sparkles text-amber-300"></i>
                <span>Power</span>
                <span 
                    class="px-1.5 py-0.2 rounded-md bg-white/20 text-white text-[11px] font-black"
                    x-text="totalPowerRemaining()"
                ></span>
            </button>

            {{-- Elapsed Time Counter --}}
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-600">
                <i class="fa-regular fa-clock text-blue-600"></i>
                <span class="hidden sm:inline">Durasi:</span>
                <span class="font-mono font-bold text-slate-800" x-text="formatElapsedTime()">00:00</span>
            </div>

            {{-- Countdown Deadline Timer (jika waktu_selesai ditentukan) --}}
            @if(isset($remainingSeconds) && $remainingSeconds !== null)
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-bold transition border"
                     :class="remainingSeconds < 300 ? 'bg-rose-50 text-rose-600 border-rose-200 animate-pulse' : 'bg-amber-50 text-amber-700 border-amber-200'">
                    <i class="fa-solid fa-stopwatch"></i>
                    <span>Sisa Waktu:</span>
                    <span class="font-mono" x-text="formatRemainingTime()">--:--</span>
                </div>
            @endif

            {{-- Flagged / Ditandai Counter --}}
            <div x-show="countFlagged() > 0" x-cloak class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-200 text-xs font-bold text-amber-800 transition">
                <i class="fa-solid fa-bookmark text-[11px] text-amber-600"></i>
                <span x-text="countFlagged()"></span> Ditandai
            </div>

            {{-- Progress Counter --}}
            <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 border border-blue-200 text-xs font-bold text-blue-700">
                <i class="fa-solid fa-check-double text-[11px]"></i>
                <span x-text="countAnswered()"></span>/<span x-text="totalQuestions"></span> Terjawab
            </div>
        </div>
    </div>

    {{-- Form Submit Kuis --}}
    <form id="quizForm" method="POST" action="{{ route('siswa.kuis.submit', $quiz->id) }}">
        @csrf
        <input type="hidden" name="durasi" id="durasiInput" value="00:00" />
        <input type="hidden" name="arc" value="{{ $selectedArc ?? 'Mudah' }}" />
        <input type="hidden" name="level" value="{{ $selectedLevel ?? 1 }}" />
        <input type="hidden" name="step" value="{{ $selectedStep ?? 1 }}" />

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            {{-- 2. Area Lembar Pertanyaan (8 Cols) --}}
            <div class="lg:col-span-8 space-y-5">
                @foreach($soals as $index => $soal)
                    @php
                        $bobotSoal = (int) ($soal->bobot ?: 10);
                    @endphp
                    <div 
                        x-show="currentIndex === {{ $index }}" 
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="bg-white rounded-3xl border p-6 md:p-8 shadow-xs space-y-6 transition-all"
                        :class="getActivePowersForQuestion({{ $soal->id }}).length > 0 ? 'border-blue-300 ring-2 ring-blue-100/80' : 'border-slate-200/90'"
                        style="{{ $index !== 0 ? 'display: none;' : '' }}"
                    >
                        {{-- Question Header Badges --}}
                        <div class="flex items-center justify-between gap-2 pb-4 border-b border-slate-100 flex-wrap">
                            <div class="flex items-center gap-2">
                                <span class="w-8 h-8 rounded-xl bg-blue-600 text-white font-extrabold text-sm flex items-center justify-center shadow-xs">
                                    {{ $index + 1 }}
                                </span>
                                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">
                                    Pertanyaan No. {{ $index + 1 }}
                                </span>
                            </div>

                            <div class="flex items-center gap-2 flex-wrap">
                                {{-- Tombol Pintas Gunakan Power pada Soal Ini --}}
                                <button 
                                    type="button" 
                                    @click="openPowerPanel()"
                                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-bold text-xs transition cursor-pointer select-none border bg-[#EFF6FF] hover:bg-blue-100/80 text-[#2563EB] border-blue-200"
                                    title="Gunakan item Power pada soal ini"
                                >
                                    <i class="fa-solid fa-wand-magic-sparkles text-xs"></i>
                                    <span>Gunakan Power</span>
                                    <span class="px-1.5 py-0.2 rounded-md bg-blue-600 text-white text-[10px] font-black" x-text="totalPowerRemaining()"></span>
                                </button>

                                {{-- Tombol Tandai Soal (Penanda / Ragu-ragu) --}}
                                <button 
                                    type="button" 
                                    @click="toggleFlag({{ $soal->id }})"
                                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-bold text-xs transition cursor-pointer select-none border"
                                    :class="isFlagged({{ $soal->id }}) 
                                        ? 'bg-amber-500 text-white border-amber-600 shadow-xs ring-2 ring-amber-300/40' 
                                        : 'bg-amber-50 hover:bg-amber-100 text-amber-800 border-amber-200'"
                                    :title="isFlagged({{ $soal->id }}) ? 'Hapus penanda ragu-ragu pada soal ini' : 'Tandai soal ini sebagai ragu-ragu'"
                                >
                                    <i :class="isFlagged({{ $soal->id }}) ? 'fa-solid fa-bookmark' : 'fa-regular fa-bookmark'" class="text-xs"></i>
                                    <span x-text="isFlagged({{ $soal->id }}) ? 'Ditandai (Ragu)' : 'Tandai Soal'"></span>
                                </button>

                                @if($soal->kesulitan)
                                    <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider
                                        {{ $soal->kesulitan === 'Mudah' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                                           ($soal->kesulitan === 'Sedang' ? 'bg-amber-50 text-amber-700 border border-amber-200' :
                                           'bg-rose-50 text-rose-700 border border-rose-200') }}">
                                        {{ $soal->kesulitan }}
                                    </span>
                                @endif

                                {{-- Badge Bobot Soal (Berubah Dinamis Jika Power Pengganda Skor Aktif) --}}
                                <template x-if="!getQuestionMultiplier({{ $soal->id }})">
                                    <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                        Bobot: {{ $bobotSoal }} Poin
                                    </span>
                                </template>
                                <template x-if="getQuestionMultiplier({{ $soal->id }}) === 'two_x_score'">
                                    <span class="px-2.5 py-1 rounded-lg text-[11px] font-extrabold bg-amber-50 text-amber-700 border border-amber-300 flex items-center gap-1 animate-pulse">
                                        <i class="fa-solid fa-bolt text-[10px]"></i>
                                        <span>Bobot 2X: {{ $bobotSoal * 2 }} Poin</span>
                                    </span>
                                </template>
                                <template x-if="getQuestionMultiplier({{ $soal->id }}) === 'double_side_knife'">
                                    <span class="px-2.5 py-1 rounded-lg text-[11px] font-extrabold bg-rose-50 text-rose-700 border border-rose-300 flex items-center gap-1">
                                        <i class="fa-solid fa-khanda text-[10px]"></i>
                                        <span>Benar +{{ $bobotSoal * 2 }} | Salah -{{ round($bobotSoal * 0.5) }} Poin</span>
                                    </span>
                                </template>
                            </div>
                        </div>

                        {{-- BANNER INDIKATOR POWER AKTIF PADA SOAL INI --}}
                        <div 
                            x-show="getActivePowersForQuestion({{ $soal->id }}).length > 0" 
                            x-cloak
                            x-transition
                            class="p-3.5 rounded-2xl bg-[#EFF6FF] border border-blue-200 space-y-2"
                        >
                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                <span class="text-[11px] font-extrabold uppercase tracking-wider text-[#2563EB] flex items-center gap-1.5">
                                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                                    <span>Efek Power Aktif pada Soal Ini:</span>
                                </span>
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <template x-for="actPower in getActivePowersForQuestion({{ $soal->id }})" :key="actPower.id">
                                        <span 
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-extrabold border shadow-2xs"
                                            :class="actPower.activePillClass"
                                        >
                                            <i :class="actPower.icon" class="text-[10px]"></i>
                                            <span x-text="actPower.name"></span>
                                        </span>
                                    </template>
                                </div>
                            </div>

                            {{-- Detail Status Second Chance यदि Aktif --}}
                            <template x-if="isSecondChanceArmed({{ $soal->id }})">
                                <div class="text-xs font-semibold text-emerald-800 bg-emerald-50/90 border border-emerald-200 rounded-xl px-3 py-2 flex items-center gap-2">
                                    <i class="fa-solid fa-shield-heart text-emerald-600"></i>
                                    <span><strong>Perisai Second Chance Siaga:</strong> Jika pilihan jawaban pertamamu salah, sistem otomatis menahan kesalahan tersebut dan memberimu kesempatan memilih ulang!</span>
                                </div>
                            </template>

                            <template x-if="isSecondChanceUsed({{ $soal->id }})">
                                <div class="text-xs font-semibold text-emerald-800 bg-emerald-100/80 border border-emerald-300 rounded-xl px-3 py-2 flex items-center gap-2">
                                    <i class="fa-solid fa-circle-check text-emerald-600"></i>
                                    <span><strong>Second Chance Telah Melindungimu!</strong> Pilihan yang kurang tepat sudah dicoret. Silakan pilih jawaban yang benar!</span>
                                </div>
                            </template>
                        </div>

                        {{-- Question Text --}}
                        <div class="space-y-3">
                            <h3 class="text-base md:text-lg font-bold text-slate-800 leading-relaxed whitespace-pre-line">
                                {{ $soal->pertanyaan }}
                            </h3>

                            {{-- Gambar Soal (jika ada) --}}
                            @if($soal->gambar)
                                <div class="mt-3 overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 max-h-80 flex items-center justify-center p-2">
                                    <img 
                                        src="{{ asset('storage/' . $soal->gambar) }}" 
                                        alt="Gambar Soal {{ $index + 1 }}" 
                                        class="max-h-72 w-auto object-contain rounded-xl hover:scale-105 transition-transform cursor-pointer"
                                        onclick="previewImage(this.src)"
                                    />
                                </div>
                            @endif

                            {{-- KARTU BANTUAN HINT (EFEK LUCKY CLOVER) --}}
                            <div 
                                x-show="getQuestionHint({{ $soal->id }})" 
                                x-cloak
                                x-transition
                                class="p-4 rounded-2xl bg-teal-50/90 border border-teal-200 text-teal-900 flex items-start gap-3 shadow-2xs"
                            >
                                <div class="w-9 h-9 rounded-xl bg-teal-600 text-white flex items-center justify-center shrink-0 text-sm shadow-xs">
                                    <i class="fa-solid fa-clover"></i>
                                </div>
                                <div class="space-y-0.5 text-xs sm:text-sm">
                                    <span class="font-extrabold text-teal-800 uppercase tracking-wider text-[11px] block">
                                        Bantuan Lucky Clover (Hint Soal)
                                    </span>
                                    <p class="font-semibold text-teal-900 leading-relaxed" x-text="getQuestionHint({{ $soal->id }})"></p>
                                </div>
                            </div>
                        </div>

                        {{-- Choice Options (Pilihan Jawaban 2-6) --}}
                        <div class="space-y-3 pt-2">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">
                                    Pilihan Jawaban:
                                </span>
                                <template x-if="questionPowerState[{{ $soal->id }}] && questionPowerState[{{ $soal->id }}].eliminatedOptions.length > 0">
                                    <span class="text-[11px] font-bold text-indigo-600">
                                        <span x-text="questionPowerState[{{ $soal->id }}].eliminatedOptions.length"></span> Opsi Salah Dieliminasi
                                    </span>
                                </template>
                            </div>

                            @php
                                $pilihanMap = $soal->pilihan_shuffled ?? $soal->pilihan;
                            @endphp

                            @if(is_array($pilihanMap) && count($pilihanMap) > 0)
                                <div class="space-y-2.5">
                                    @foreach($pilihanMap as $optKey => $optVal)
                                        @php
                                            $optKeyLower = strtolower((string) $optKey);
                                        @endphp
                                        <div class="option-card relative">
                                            <input 
                                                type="radio" 
                                                id="opt_{{ $soal->id }}_{{ $optKey }}" 
                                                name="jawaban[{{ $soal->id }}]" 
                                                value="{{ $optKey }}"
                                                :disabled="isOptionEliminated({{ $soal->id }}, '{{ $optKeyLower }}')"
                                                :checked="answers[{{ $soal->id }}] === '{{ $optKey }}'"
                                                @change="recordAnswer({{ $soal->id }}, '{{ $optKey }}')"
                                                class="sr-only"
                                            />
                                            <label 
                                                for="opt_{{ $soal->id }}_{{ $optKey }}"
                                                class="flex items-center justify-between gap-3.5 p-4 rounded-2xl border-2 transition select-none group"
                                                :class="isOptionEliminated({{ $soal->id }}, '{{ $optKeyLower }}')
                                                    ? 'border-slate-200 bg-slate-100/80 opacity-55 cursor-not-allowed'
                                                    : (answers[{{ $soal->id }}] === '{{ $optKey }}'
                                                        ? 'border-blue-600 bg-blue-50/70 cursor-pointer'
                                                        : 'border-slate-200/80 bg-white hover:border-blue-400 hover:bg-slate-50/50 cursor-pointer')"
                                            >
                                                <div class="flex items-center gap-3.5 min-w-0 flex-1">
                                                    {{-- Letter Badge (A, B, C, D) --}}
                                                    <span 
                                                        class="option-badge w-8 h-8 rounded-xl border-2 font-extrabold text-xs uppercase flex items-center justify-center shrink-0 transition"
                                                        :class="isOptionEliminated({{ $soal->id }}, '{{ $optKeyLower }}')
                                                            ? 'border-slate-300 bg-slate-200 text-slate-400'
                                                            : (answers[{{ $soal->id }}] === '{{ $optKey }}'
                                                                ? 'border-blue-600 bg-blue-600 text-white'
                                                                : 'border-slate-300 text-slate-600 group-hover:border-blue-500 group-hover:text-blue-600')"
                                                    >
                                                        {{ $optKey }}
                                                    </span>

                                                    <span 
                                                        class="text-sm font-semibold flex-1 leading-snug"
                                                        :class="isOptionEliminated({{ $soal->id }}, '{{ $optKeyLower }}') ? 'line-through text-slate-400' : 'text-slate-700'"
                                                    >
                                                        {{ $optVal }}
                                                    </span>
                                                </div>

                                                {{-- Badge Keterangan jika Opsi Dieliminasi oleh Power --}}
                                                <template x-if="isOptionEliminated({{ $soal->id }}, '{{ $optKeyLower }}')">
                                                    <span 
                                                        class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold shrink-0 border"
                                                        :class="isOptionBlockedBySecondChance({{ $soal->id }}, '{{ $optKeyLower }}')
                                                            ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                                            : 'bg-slate-200/80 text-slate-600 border-slate-300'"
                                                        x-text="isOptionBlockedBySecondChance({{ $soal->id }}, '{{ $optKeyLower }}') ? 'Salah • Ditahan Second Chance' : 'Dieliminasi Power'"
                                                    ></span>
                                                </template>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="p-4 rounded-xl bg-slate-50 text-slate-400 text-xs text-center">
                                    Pilihan jawaban tidak tersedia.
                                </div>
                            @endif
                        </div>

                        {{-- Navigation Step Buttons --}}
                        <div class="pt-4 border-t border-slate-100 flex items-center justify-between gap-3 flex-wrap">
                            <button 
                                type="button" 
                                @click="prevQuestion()"
                                :disabled="currentIndex === 0"
                                :class="currentIndex === 0 ? 'opacity-40 cursor-not-allowed bg-slate-100 text-slate-400' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                                class="px-4 py-2.5 rounded-xl font-bold text-xs flex items-center gap-1.5 transition"
                            >
                                <i class="fa-solid fa-arrow-left text-[11px]"></i>
                                <span>Sebelumnya</span>
                            </button>

                            {{-- Quick Toggle Penanda --}}
                            <button
                                type="button"
                                @click="toggleFlag({{ $soal->id }})"
                                class="px-3.5 py-2 rounded-xl text-xs font-bold flex items-center gap-2 transition cursor-pointer select-none border"
                                :class="isFlagged({{ $soal->id }}) 
                                    ? 'bg-amber-500 text-white border-amber-600 shadow-xs' 
                                    : 'bg-amber-50/70 hover:bg-amber-100 text-amber-800 border-amber-200'"
                            >
                                <i :class="isFlagged({{ $soal->id }}) ? 'fa-solid fa-bookmark' : 'fa-regular fa-bookmark'" class="text-xs"></i>
                                <span x-text="isFlagged({{ $soal->id }}) ? 'Soal Ditandai (Ragu)' : 'Tandai Soal (Ragu)'"></span>
                            </button>

                            <button 
                                type="button" 
                                @click="nextQuestion()"
                                x-show="currentIndex < totalQuestions - 1"
                                class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs flex items-center gap-1.5 transition shadow-xs"
                            >
                                <span>Selanjutnya</span>
                                <i class="fa-solid fa-arrow-right text-[11px]"></i>
                            </button>

                            <button 
                                type="button" 
                                @click="confirmSubmit()"
                                x-show="currentIndex === totalQuestions - 1"
                                class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center gap-1.5 transition shadow-md shadow-emerald-600/20"
                            >
                                <i class="fa-solid fa-paper-plane text-[11px]"></i>
                                <span>Selesaikan Kuis</span>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- 3. Sidebar Navigasi Nomor Soal & Widget Power (4 Cols) --}}
            <div class="lg:col-span-4 space-y-5">
                <div class="bg-white rounded-3xl border border-slate-200/90 p-5 shadow-xs sticky top-36 space-y-5">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fa-solid fa-table-cells-large text-blue-600"></i>
                            <span>Nomor Soal</span>
                        </h4>
                        <span class="text-[11px] font-semibold text-slate-400">
                            Pilih untuk melompat
                        </span>
                    </div>

                    {{-- Grid Tombol Nomor Soal --}}
                    <div class="grid grid-cols-5 gap-2.5">
                        @foreach($soals as $index => $soal)
                            <button 
                                type="button" 
                                @click="goToQuestion({{ $index }})"
                                class="relative h-10 rounded-xl font-bold text-xs flex items-center justify-center transition border-2"
                                :class="{
                                    'border-blue-600 bg-blue-600 text-white shadow-sm ring-2 ring-blue-400/40': currentIndex === {{ $index }},
                                    'border-amber-400 bg-amber-50 text-amber-800': currentIndex !== {{ $index }} && isFlagged({{ $soal->id }}),
                                    'border-emerald-500 bg-emerald-50 text-emerald-700': currentIndex !== {{ $index }} && !isFlagged({{ $soal->id }}) && isAnswered({{ $soal->id }}),
                                    'border-slate-200 bg-white text-slate-600 hover:border-slate-300': currentIndex !== {{ $index }} && !isFlagged({{ $soal->id }}) && !isAnswered({{ $soal->id }})
                                }"
                            >
                                <span>{{ $index + 1 }}</span>

                                {{-- Badge petir kecil di pojok kiri atas jika soal ini memiliki Power aktif --}}
                                <span 
                                    x-show="getActivePowersForQuestion({{ $soal->id }}).length > 0" 
                                    x-cloak
                                    class="absolute -top-1.5 -left-1.5 w-4 h-4 rounded-full bg-blue-600 text-white flex items-center justify-center text-[8px] shadow-xs ring-1 ring-white"
                                    title="Power aktif pada soal ini"
                                >
                                    <i class="fa-solid fa-bolt"></i>
                                </span>

                                {{-- Icon Penanda (Badge bookmark kecil di pojok kanan atas) --}}
                                <span 
                                    x-show="isFlagged({{ $soal->id }})" 
                                    x-cloak
                                    class="absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full bg-amber-500 text-white flex items-center justify-center text-[8px] shadow-xs ring-1 ring-white"
                                    title="Soal ini ditandai ragu-ragu"
                                >
                                    <i class="fa-solid fa-bookmark"></i>
                                </span>

                                {{-- Icon Centang Sudah Dijawab jika tidak ditandai dan tidak aktif --}}
                                <span x-show="isAnswered({{ $soal->id }}) && !isFlagged({{ $soal->id }}) && currentIndex !== {{ $index }}" class="ml-0.5 text-[9px]">
                                    <i class="fa-solid fa-check"></i>
                                </span>
                            </button>
                        @endforeach
                    </div>

                    {{-- WIDGET STATUS & SIMULASI POWER (PROTOTYPE) --}}
                    <div class="p-4 rounded-2xl bg-[#EFF5FF] border border-[#DCE9FE] space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-[#2563EB] text-white flex items-center justify-center text-xs shadow-2xs">
                                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                                </div>
                                <div>
                                    <h5 class="text-xs font-extrabold text-slate-900 leading-none">Item Power</h5>
                                    <span class="text-[10px] font-semibold text-slate-500"><span x-text="totalPowerRemaining()"></span> Power Tersedia</span>
                                </div>
                            </div>
                            <button 
                                type="button" 
                                @click="openPowerPanel()"
                                class="px-3 py-1.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-[11px] font-extrabold transition cursor-pointer shadow-2xs"
                            >
                                Buka Panel
                            </button>
                        </div>

                        {{-- Ringkasan Efek Power yang Sedang Berjalan --}}
                        <div class="bg-white/90 rounded-xl p-3 border border-blue-100 space-y-1.5 text-[11px]">
                            <div class="flex items-center justify-between text-slate-500 font-semibold">
                                <span>Power Digunakan:</span>
                                <strong class="text-slate-800" x-text="powerUsageLog.length + ' Item'"></strong>
                            </div>
                            <div class="flex items-center justify-between text-slate-500 font-semibold">
                                <span>Pengganda Skor Aktif:</span>
                                <strong class="text-blue-600" x-text="calculatePowerScoreSummary().activeMultiplierCount + ' Soal'"></strong>
                            </div>
                            <div class="pt-1.5 border-t border-slate-100 text-[10px] text-slate-500 leading-relaxed">
                                <i class="fa-solid fa-shield-halved text-blue-500 mr-1"></i>
                                Skor akhir & efek Power dihitung otomatis oleh sistem setelah level dikumpulkan.
                            </div>
                        </div>
                    </div>

                    {{-- Legend Keterangan --}}
                    <div class="pt-3 border-t border-slate-100 space-y-2 text-[11px] text-slate-500">
                        <div class="flex items-center gap-2">
                            <span class="w-3.5 h-3.5 rounded-md bg-blue-600 border border-blue-600 shrink-0"></span>
                            <span>Soal Sedang Dibuka</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3.5 h-3.5 rounded-md bg-amber-50 border border-amber-400 text-amber-600 shrink-0 flex items-center justify-center text-[8px] font-bold">
                                <i class="fa-solid fa-bookmark text-[8px]"></i>
                            </span>
                            <span>Ditandai (Ragu-ragu)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3.5 h-3.5 rounded-md bg-emerald-50 border border-emerald-500 text-emerald-600 shrink-0 flex items-center justify-center text-[8px] font-bold">✓</span>
                            <span>Sudah Dijawab</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3.5 h-3.5 rounded-md bg-white border border-slate-300 shrink-0"></span>
                            <span>Belum Dijawab</span>
                        </div>
                    </div>

                    {{-- Tombol Aksi Kuis (Tanpa Fitur Simpan Draft: 1 Sesi Langsung Submit Level) --}}
                    <div class="pt-2">
                        <button 
                            type="button" 
                            @click="confirmSubmit()"
                            class="w-full py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center justify-center gap-2 transition shadow-md shadow-emerald-600/20 cursor-pointer"
                        >
                            <i class="fa-solid fa-paper-plane text-xs"></i>
                            <span>Kumpulkan & Selesaikan Level</span>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>

</div>
@endsection

@push('scripts')
{{-- Modul Logika Sistem Power (Terintegrasi Backend) --}}
@include('siswa.kuis.partials.power-script')

<script>
function quizPlayer() {
    const powerSystem = window.createLearnPointPowerSystem({
        quizId: {{ $quiz->id }},
        levelKey: '{{ ($selectedArc ?? "Mudah") . "_" . ($selectedLevel ?? 1) }}',
        questionIds: @json($powerQuestionIds),
        questionsMeta: @json($powerQuestionsMeta),
        csrfToken: '{{ csrf_token() }}',
        indexUrl: "{{ route('siswa.kuis.powers.index', $quiz->id) }}",
        useUrl: "{{ route('siswa.kuis.powers.use', $quiz->id) }}",
        secondChanceUrl: "{{ route('siswa.kuis.powers.second-chance', $quiz->id) }}"
    });

    return {
        ...powerSystem,
        currentIndex: 0,
        totalQuestions: {{ $soals->count() }},
        answers: @json((object)($savedAnswers ?? [])),
        flagged: (function() {
            try {
                return JSON.parse(localStorage.getItem('lp_quiz_{{ $quiz->id }}_flagged') || '{}');
            } catch(e) {
                return {};
            }
        })(),
        elapsedSeconds: 0,
        remainingSeconds: {{ $remainingSeconds !== null ? $remainingSeconds : 'null' }},
        timerInterval: null,

        toggleFlag(soalId) {
            this.flagged[soalId] = !this.flagged[soalId];
            try {
                localStorage.setItem('lp_quiz_{{ $quiz->id }}_flagged', JSON.stringify(this.flagged));
            } catch(e) {}
        },

        isFlagged(soalId) {
            return !!this.flagged[soalId];
        },

        countFlagged() {
            return Object.values(this.flagged).filter(Boolean).length;
        },

        initTimer() {
            this.timerInterval = setInterval(() => {
                this.elapsedSeconds++;

                const durasiFormatted = this.formatElapsedTime();
                const durasiEl = document.getElementById('durasiInput');
                if (durasiEl) {
                    durasiEl.value = durasiFormatted;
                }

                if (this.remainingSeconds !== null) {
                    this.remainingSeconds--;
                    if (this.remainingSeconds <= 0) {
                        clearInterval(this.timerInterval);
                        this.autoSubmitOnTimeUp();
                    }
                }
            }, 1000);
        },

        formatElapsedTime() {
            const mins = Math.floor(this.elapsedSeconds / 60);
            const secs = this.elapsedSeconds % 60;
            return String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
        },

        formatRemainingTime() {
            if (this.remainingSeconds === null || this.remainingSeconds <= 0) return '00:00';
            const mins = Math.floor(this.remainingSeconds / 60);
            const secs = this.remainingSeconds % 60;
            return String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
        },

        goToQuestion(index) {
            if (index >= 0 && index < this.totalQuestions) {
                this.currentIndex = index;
                window.scrollTo({ top: 120, behavior: 'smooth' });
            }
        },

        nextQuestion() {
            if (this.currentIndex < this.totalQuestions - 1) {
                this.goToQuestion(this.currentIndex + 1);
            }
        },

        prevQuestion() {
            if (this.currentIndex > 0) {
                this.goToQuestion(this.currentIndex - 1);
            }
        },

        async recordAnswer(soalId, optionKey) {
            const cleanOpt = String(optionKey).toLowerCase();

            // Cegah memilih opsi yang sudah dieliminasi oleh Power
            if (this.isOptionEliminated(soalId, cleanOpt)) {
                return;
            }

            // Jika Perisai Second Chance sedang siaga pada soal ini, verifikasi ke backend (tanpa bocor kunci di client)
            if (this.isSecondChanceArmed(soalId)) {
                this.answers[soalId] = optionKey;
                const protectedWrong = await this.verifyAndTriggerSecondChance(soalId, cleanOpt);
                if (protectedWrong) {
                    return;
                }
            } else {
                this.answers[soalId] = optionKey;
            }

            this.persistSessionPowerSummary();
        },

        isAnswered(soalId) {
            return this.answers.hasOwnProperty(soalId) && this.answers[soalId] !== '' && this.answers[soalId] !== null;
        },

        countAnswered() {
            return Object.values(this.answers).filter(v => v !== '' && v !== null).length;
        },

        autoSubmitOnTimeUp() {
            this.persistSessionPowerSummary();
            Swal.fire({
                title: 'Waktu Habis!',
                text: 'Batas waktu pengerjaan kuis telah tercapai. Jawaban kamu akan otomatis dikumpulkan.',
                icon: 'warning',
                timer: 3000,
                showConfirmButton: false,
                allowOutsideClick: false
            }).then(() => {
                document.getElementById('quizForm').submit();
            });
        },

        confirmSubmit() {
            const answered = this.countAnswered();
            const unanswered = this.totalQuestions - answered;
            const flagged = this.countFlagged();
            const powerSum = this.calculatePowerScoreSummary();

            let htmlMessage = `<div style="text-align: left; font-size: 13px; line-height: 1.6;">`;
            if (unanswered > 0) {
                htmlMessage += `<p style="margin-bottom: 8px;">Kamu baru menjawab <strong>${answered}</strong> dari <strong>${this.totalQuestions}</strong> soal.</p>
                    <p style="color: #e11d48; font-weight: 600;">⚠️ Masih ada ${unanswered} butir soal yang belum dijawab!</p>`;
            } else {
                htmlMessage += `<p style="margin-bottom: 8px;">🎉 Luar biasa! Semua <strong>${this.totalQuestions}</strong> butir soal telah kamu jawab lengkap.</p>`;
            }

            if (powerSum.usedCount > 0) {
                htmlMessage += `<div style="margin-top: 10px; padding: 10px 12px; border-radius: 12px; background: #eff6ff; border: 1px solid #bfdbfe; color: #1e3a8a;">
                    <div style="font-weight: 800; font-size: 12px; margin-bottom: 4px;">⚡ Efek Power Aktif (${powerSum.usedCount} Item Digunakan)</div>
                    <div style="font-size: 12px;">Efek bonus & penalti Power akan dihitung otomatis setelah jawaban dikumpulkan.</div>
                </div>`;
            }

            if (flagged > 0) {
                htmlMessage += `<p style="margin-top: 8px; color: #d97706; font-weight: 600;">🔖 Perhatian: Terdapat <strong>${flagged}</strong> butir soal yang masih kamu tandai sebagai ragu-ragu.</p>`;
            }

            htmlMessage += `<p style="margin-top: 8px;">Apakah kamu siap mengumpulkan kuis dan melihat hasilnya?</p></div>`;

            Swal.fire({
                title: 'Kumpulkan Kuis?',
                html: htmlMessage,
                icon: (unanswered > 0 || flagged > 0) ? 'warning' : 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Kumpulkan Sekarang!',
                cancelButtonText: 'Periksa Kembali'
            }).then((result) => {
                if (result.isConfirmed) {
                    clearInterval(this.timerInterval);
                    this.persistSessionPowerSummary();
                    try {
                        localStorage.removeItem('lp_quiz_{{ $quiz->id }}_flagged');
                    } catch(e) {}
                    document.getElementById('durasiInput').value = this.formatElapsedTime();
                    document.getElementById('quizForm').submit();
                }
            });
        }
    };
}

function previewImage(src) {
    Swal.fire({
        imageUrl: src,
        imageAlt: 'Preview Gambar Soal',
        showCloseButton: true,
        showConfirmButton: false,
        width: 'auto'
    });
}
</script>
@endpush
