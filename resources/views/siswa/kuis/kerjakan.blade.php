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
</style>
@endpush

@section('content')
<div x-data="quizPlayer()" x-init="initTimer()" class="max-w-5xl mx-auto space-y-6">

    {{-- 1. Sticky Header Bar Pengerjaan --}}
    <div class="sticky top-16 z-30 bg-white/95 backdrop-blur-md p-4 rounded-2xl border border-slate-200/90 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        {{-- Info Kuis & Mapel --}}
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center text-lg font-bold shadow-md shadow-blue-500/20 shrink-0">
                <i class="fa-solid fa-gamepad"></i>
            </div>
            <div>
                <h2 class="text-sm md:text-base font-extrabold text-slate-800 leading-tight truncate max-w-xs md:max-w-md">
                    {{ $quiz->judul }}
                </h2>
                <div class="flex items-center gap-2 mt-0.5 text-xs text-slate-400">
                    <span class="font-semibold text-blue-600">{{ $quiz->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? 'Mata Pelajaran' }}</span>
                    <span>•</span>
                    <span>Total <strong class="text-slate-700" x-text="totalQuestions"></strong> Soal</span>
                </div>
            </div>
        </div>

        {{-- Live Timer & Progress Summary --}}
        <div class="flex items-center justify-between md:justify-end gap-3 shrink-0">
            {{-- Elapsed Time Counter --}}
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-600">
                <i class="fa-regular fa-clock text-blue-600"></i>
                <span>Durasi:</span>
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

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            {{-- 2. Area Lembar Pertanyaan (8 Cols) --}}
            <div class="lg:col-span-8 space-y-5">
                @foreach($soals as $index => $soal)
                    <div 
                        x-show="currentIndex === {{ $index }}" 
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="bg-white rounded-3xl border border-slate-200/90 p-6 md:p-8 shadow-xs space-y-6"
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
                                @if($soal->kesulitan)
                                    <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider
                                        {{ $soal->kesulitan === 'Mudah' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                                           ($soal->kesulitan === 'Sedang' ? 'bg-amber-50 text-amber-700 border border-amber-200' :
                                           'bg-rose-50 text-rose-700 border border-rose-200') }}">
                                        {{ $soal->kesulitan }}
                                    </span>
                                @endif

                                @if($soal->level)
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-slate-100 text-slate-600">
                                        {{ $soal->level }}
                                    </span>
                                @endif

                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700">
                                    Bobot: {{ number_format($soal->bobot, 0) }} Poin
                                </span>
                            </div>
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
                        </div>

                        {{-- Choice Options (Pilihan Jawaban 2-6) --}}
                        <div class="space-y-3 pt-2">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">
                                Pilihan Jawaban:
                            </span>

                            @php
                                $pilihanMap = $soal->pilihan_shuffled ?? $soal->pilihan;
                            @endphp

                            @if(is_array($pilihanMap) && count($pilihanMap) > 0)
                                <div class="space-y-2.5">
                                    @foreach($pilihanMap as $optKey => $optVal)
                                        <div class="option-card relative">
                                            <input 
                                                type="radio" 
                                                id="opt_{{ $soal->id }}_{{ $optKey }}" 
                                                name="jawaban[{{ $soal->id }}]" 
                                                value="{{ $optKey }}"
                                                @change="recordAnswer({{ $soal->id }}, '{{ $optKey }}')"
                                                class="sr-only"
                                            />
                                            <label 
                                                for="opt_{{ $soal->id }}_{{ $optKey }}"
                                                class="flex items-center gap-3.5 p-4 rounded-2xl border-2 border-slate-200/80 bg-white hover:border-blue-400 hover:bg-slate-50/50 cursor-pointer transition select-none group"
                                            >
                                                {{-- Letter Badge (A, B, C, D, E, F) --}}
                                                <span class="option-badge w-8 h-8 rounded-xl border-2 border-slate-300 text-slate-600 font-extrabold text-xs uppercase flex items-center justify-center shrink-0 transition group-hover:border-blue-500 group-hover:text-blue-600">
                                                    {{ $optKey }}
                                                </span>

                                                <span class="text-sm font-semibold text-slate-700 flex-1 leading-snug">
                                                    {{ $optVal }}
                                                </span>
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
                        <div class="pt-4 border-t border-slate-100 flex items-center justify-between gap-3">
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

            {{-- 3. Sidebar Navigasi Nomor Soal (4 Cols) --}}
            <div class="lg:col-span-4 space-y-5">
                <div class="bg-white rounded-3xl border border-slate-200/90 p-5 shadow-xs sticky top-36 space-y-4">
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
                                class="h-10 rounded-xl font-bold text-xs flex items-center justify-center transition border-2"
                                :class="{
                                    'border-blue-600 bg-blue-600 text-white shadow-sm ring-2 ring-blue-400/40': currentIndex === {{ $index }},
                                    'border-emerald-500 bg-emerald-50 text-emerald-700': currentIndex !== {{ $index }} && isAnswered({{ $soal->id }}),
                                    'border-slate-200 bg-white text-slate-600 hover:border-slate-300': currentIndex !== {{ $index }} && !isAnswered({{ $soal->id }})
                                }"
                            >
                                <span>{{ $index + 1 }}</span>
                                <span x-show="isAnswered({{ $soal->id }}) && currentIndex !== {{ $index }}" class="ml-0.5 text-[9px]">
                                    <i class="fa-solid fa-check"></i>
                                </span>
                            </button>
                        @endforeach
                    </div>

                    {{-- Legend Keterangan --}}
                    <div class="pt-3 border-t border-slate-100 space-y-2 text-[11px] text-slate-500">
                        <div class="flex items-center gap-2">
                            <span class="w-3.5 h-3.5 rounded-md bg-blue-600 border border-blue-600 shrink-0"></span>
                            <span>Soal Sedang Dibuka</span>
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

                    {{-- Tombol Kumpulkan Kuis --}}
                    <div class="pt-2">
                        <button 
                            type="button" 
                            @click="confirmSubmit()"
                            class="w-full py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center justify-center gap-2 transition shadow-md shadow-emerald-600/20"
                        >
                            <i class="fa-solid fa-paper-plane text-xs"></i>
                            <span>Kumpulkan & Selesaikan Kuis</span>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>

</div>
@endsection

@push('scripts')
<script>
function quizPlayer() {
    return {
        currentIndex: 0,
        totalQuestions: {{ $soals->count() }},
        answers: {},
        elapsedSeconds: 0,
        remainingSeconds: {{ $remainingSeconds !== null ? $remainingSeconds : 'null' }},
        timerInterval: null,

        initTimer() {
            // Timer berjalan setiap detik
            this.timerInterval = setInterval(() => {
                this.elapsedSeconds++;

                // Update hidden durasi
                const durasiFormatted = this.formatElapsedTime();
                const durasiEl = document.getElementById('durasiInput');
                if (durasiEl) {
                    durasiEl.value = durasiFormatted;
                }

                // Hitung countdown jika ada batas waktu
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

        recordAnswer(soalId, optionKey) {
            this.answers[soalId] = optionKey;
        },

        isAnswered(soalId) {
            return this.answers.hasOwnProperty(soalId) && this.answers[soalId] !== '';
        },

        countAnswered() {
            return Object.keys(this.answers).length;
        },

        autoSubmitOnTimeUp() {
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

            let htmlMessage = '';
            if (unanswered > 0) {
                htmlMessage = `<div style="text-align: left; font-size: 13px; line-height: 1.6;">
                    <p style="margin-bottom: 8px;">Kamu baru menjawab <strong>${answered}</strong> dari <strong>${this.totalQuestions}</strong> soal.</p>
                    <p style="color: #e11d48; font-weight: 600;">⚠️ Masih ada ${unanswered} butir soal yang belum dijawab!</p>
                    <p style="margin-top: 8px;">Apakah kamu yakin ingin tetap mengumpulkan kuis ini?</p>
                </div>`;
            } else {
                htmlMessage = `<div style="text-align: left; font-size: 13px; line-height: 1.6;">
                    <p>🎉 Luar biasa! Semua <strong>${this.totalQuestions}</strong> butir soal telah kamu jawab dengan lengkap.</p>
                    <p style="margin-top: 6px;">Apakah kamu siap mengumpulkan kuis dan melihat nilai?</p>
                </div>`;
            }

            Swal.fire({
                title: 'Kumpulkan Kuis?',
                html: htmlMessage,
                icon: unanswered > 0 ? 'warning' : 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Kumpulkan Sekarang!',
                cancelButtonText: 'Periksa Kembali'
            }).then((result) => {
                if (result.isConfirmed) {
                    clearInterval(this.timerInterval);
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
