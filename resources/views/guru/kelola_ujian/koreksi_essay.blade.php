@extends('layouts.app')

@section('title', 'Koreksi Essay Massal: ' . $ujian->judul)

@section('content')
<div class="max-w-7xl mx-auto space-y-6" x-data="koreksiEssayMassal({
    soals: {{ Js::from($dataKoreksi) }},
    saveUrl: '{{ route('guru.ujian.simpan-nilai-essay', $ujian->id) }}',
    csrfToken: '{{ csrf_token() }}'
})">

    {{-- =========================================================
        HEADER HALAMAN
    ========================================================== --}}
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div class="space-y-1">
            <div class="mb-2">
                <a href="{{ route('guru.ujian.rekap', $ujian->id) }}"
                    class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-slate-500 hover:text-blue-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Kembali ke Rekap Nilai</span>
                </a>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-tight">
                    Koreksi Essay Massal
                </h1>
                <span
                    class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-600 border border-blue-200/70 shadow-2xs">
                    Penilaian Per Soal
                </span>
            </div>

            <p class="text-sm text-slate-500 leading-relaxed font-normal">
                {{ $ujian->judul }}
            </p>
        </div>

        {{-- INDIKATOR PENYIMPANAN OTOMATIS --}}
        <div class="flex items-center gap-2 shrink-0">
            <span x-show="isSaving" x-cloak
                class="inline-flex items-center gap-2 text-xs font-semibold text-blue-600 bg-blue-50 border border-blue-200/80 px-3.5 py-2 rounded-xl shadow-2xs">
                <span class="w-2 h-2 rounded-full bg-blue-500 animate-ping"></span>
                <span>Menyimpan nilai...</span>
            </span>
            <span x-show="lastSaved && !isSaving" x-cloak
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200/70 px-3.5 py-2 rounded-xl shadow-2xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <span>Nilai Tersimpan Otomatis</span>
            </span>
        </div>
    </div>

    {{-- EMPTY STATE JIKA TIDAK ADA SOAL ESSAY --}}
    <template x-if="soals.length === 0">
        <div class="bg-white rounded-2xl p-12 text-center border border-slate-200/80 shadow-sm">
            <p class="text-sm font-bold text-slate-700">Ujian ini tidak memiliki soal bertipe Essay.</p>
            <p class="text-xs text-slate-400 mt-1">Semua butir soal pada ujian ini dinilai secara otomatis oleh sistem.</p>
        </div>
    </template>

    {{-- KONTEN UTAMA 2 KOLOM --}}
    <template x-if="soals.length > 0">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            {{-- SIDEBAR KIRI: DAFTAR SOAL ESSAY & PROGRES PENILAIAN --}}
            <div
                class="lg:col-span-4 xl:col-span-3 bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm h-fit space-y-4">
                <div class="pb-3 border-b border-slate-100">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Daftar Soal Essay</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Pilih soal untuk menilai seluruh siswa</p>
                </div>

                <div class="space-y-2">
                    <template x-for="(s, idx) in soals" :key="s.nomor_essay">
                        <button type="button" @click="pilihSoal(idx)"
                            class="w-full text-left p-3.5 rounded-xl border transition-all duration-150 cursor-pointer"
                            :class="{
                                'border-blue-600 bg-blue-50/60 ring-1 ring-blue-600/20 shadow-2xs': currentSoalIndex === idx,
                                'border-slate-200/80 bg-white hover:border-blue-200 hover:bg-slate-50/60': currentSoalIndex !== idx
                            }">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-xs font-bold"
                                    :class="currentSoalIndex === idx ? 'text-blue-700' : 'text-slate-800'">
                                    Essay #<span x-text="s.nomor_essay"></span>
                                    <span class="text-[11px] font-normal text-slate-400"
                                        x-text="'(No. ' + s.nomor_asli + ')'"></span>
                                </span>

                                <template x-if="hitungTernilai(s) === s.pesertas.length && s.pesertas.length > 0">
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/70 shrink-0">
                                        <span class="w-1 h-1 rounded-full bg-emerald-500"></span>
                                        Tuntas
                                    </span>
                                </template>
                                <template x-if="hitungTernilai(s) < s.pesertas.length">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200/80 shrink-0"
                                        x-text="hitungTernilai(s) + '/' + s.pesertas.length"></span>
                                </template>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1.5 line-clamp-2 leading-relaxed"
                                x-text="s.pertanyaan"></p>
                        </button>
                    </template>
                </div>
            </div>

            {{-- PANEL KANAN: EDITOR KOREKSI PER SISWA --}}
            <div
                class="lg:col-span-8 xl:col-span-9 bg-white rounded-2xl p-6 sm:p-7 border border-slate-200/80 shadow-sm space-y-6">

                <template x-if="currentSoal && currentPeserta">
                    <div class="space-y-6">
                        {{-- Header Soal & Identitas Siswa --}}
                        <div
                            class="flex flex-col sm:flex-row sm:items-center justify-between gap-3.5 pb-4 border-b border-slate-100">
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-blue-50 text-blue-600 border border-blue-200/70">
                                        Soal Essay #<span class="ml-0.5" x-text="currentSoal.nomor_essay"></span>
                                    </span>
                                    <span class="text-xs font-medium text-slate-400">
                                        Siswa ke-<span class="font-semibold text-slate-600" x-text="currentSiswaIndex + 1"></span>
                                        dari <span class="font-semibold text-slate-600" x-text="currentSoal.pesertas.length"></span>
                                    </span>
                                </div>
                                <div class="flex items-center gap-2 flex-wrap mt-2">
                                    <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight"
                                        x-text="currentPeserta.nama"></h2>
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-blue-50 text-blue-600 border border-blue-100/80"
                                        x-text="'Kelas ' + currentPeserta.kelas"></span>
                                </div>
                            </div>

                            {{-- Dropdown Cepat Pilih Siswa + Status Badge --}}
                            <div class="flex items-center gap-2 flex-wrap">
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold border shadow-2xs"
                                    :class="currentPeserta.sudah_dinilai
                                        ? 'bg-emerald-50 text-emerald-700 border-emerald-200/70'
                                        : 'bg-amber-50 text-amber-700 border-amber-200/80'">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                    <span x-text="currentPeserta.sudah_dinilai ? 'Sudah Dinilai' : 'Belum Dinilai'"></span>
                                </span>

                                <select x-model.number="currentSiswaIndex"
                                    class="rounded-xl border border-slate-200/80 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none cursor-pointer">
                                    <template x-for="(p, pIdx) in currentSoal.pesertas" :key="p.siswa_id">
                                        <option :value="pIdx"
                                            x-text="(pIdx + 1) + '. ' + p.nama + (p.sudah_dinilai ? ' (Sudah)' : '')">
                                        </option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        {{-- KOTAK INPUT NILAI SISWA & BOBOT MAKSIMAL --}}
                        <div
                            class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 sm:p-5 rounded-xl bg-blue-50/40 border border-blue-200/70">
                            <div>
                                <label
                                    class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                                    Input Skor Perolehan Siswa
                                </label>
                                <input type="number" step="0.01" min="0" :max="currentSoal.bobot_maks"
                                    x-model="currentPeserta.skor"
                                    @change="simpanSkorSekarang()"
                                    placeholder="0"
                                    class="w-full border border-slate-200/90 rounded-xl px-4 py-2.5 text-lg font-extrabold text-slate-900 bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">

                                {{-- Tombol Quick Score --}}
                                <div class="flex items-center gap-1.5 mt-2.5 flex-wrap">
                                    <button type="button" @click="setQuickScore(0)"
                                        class="px-2.5 py-1 rounded-lg bg-white hover:bg-rose-50 text-slate-600 hover:text-rose-600 border border-slate-200/80 hover:border-rose-200/80 text-[11px] font-semibold transition cursor-pointer">
                                        0 (Kosong)
                                    </button>
                                    <button type="button"
                                        @click="setQuickScore(Number((currentSoal.bobot_maks / 2).toFixed(2)))"
                                        class="px-2.5 py-1 rounded-lg bg-white hover:bg-blue-50 text-slate-600 hover:text-blue-600 border border-slate-200/80 hover:border-blue-200/80 text-[11px] font-semibold transition cursor-pointer">
                                        Setengah (<span x-text="(currentSoal.bobot_maks / 2).toFixed(2)"></span>)
                                    </button>
                                    <button type="button" @click="setQuickScore(currentSoal.bobot_maks)"
                                        class="px-2.5 py-1 rounded-lg bg-white hover:bg-emerald-50 text-slate-600 hover:text-emerald-700 border border-slate-200/80 hover:border-emerald-200/80 text-[11px] font-semibold transition cursor-pointer">
                                        Poin Penuh (<span x-text="currentSoal.bobot_maks"></span>)
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">
                                    Bobot Maksimal Soal
                                </label>
                                <div
                                    class="w-full border border-slate-200/80 rounded-xl px-4 py-2.5 text-lg font-extrabold text-slate-600 bg-white/80">
                                    <span x-text="currentSoal.bobot_maks"></span> Poin
                                </div>
                                <p class="text-[11px] text-slate-500 mt-2.5 leading-relaxed">
                                    Nilai otomatis dibatasi antara <strong class="text-slate-700">0</strong> hingga
                                    <strong class="text-slate-700" x-text="currentSoal.bobot_maks"></strong> poin.
                                </p>
                            </div>
                        </div>

                        {{-- PERTANYAAN SOAL (READ-ONLY) --}}
                        <div class="space-y-2">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                Pertanyaan Soal
                            </label>
                            <div class="p-4 rounded-xl bg-slate-50/70 border border-slate-200/80 text-sm text-slate-800 leading-relaxed"
                                x-html="currentSoal.pertanyaan"></div>
                            <template x-if="currentSoal.gambar">
                                <img :src="currentSoal.gambar"
                                    class="max-h-56 rounded-xl border border-slate-200/80 mt-2">
                            </template>
                        </div>

                        {{-- KUNCI JAWABAN / RUBRIK GURU (JIKA ADA) --}}
                        <template x-if="currentSoal.kunci_jawaban">
                            <div class="p-4 rounded-xl bg-slate-50/80 border border-slate-200/80 space-y-1.5">
                                <div class="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-emerald-700">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Kunci Jawaban / Pedoman Penskoran Guru</span>
                                </div>
                                <p class="text-xs text-slate-700 whitespace-pre-line leading-relaxed"
                                    x-text="currentSoal.kunci_jawaban"></p>
                            </div>
                        </template>

                        {{-- JAWABAN TEKS DARI SISWA --}}
                        <div class="space-y-2">
                            <div class="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-blue-600">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                <span>Jawaban Siswa (<span x-text="currentPeserta.nama"></span>)</span>
                            </div>
                            <div
                                class="p-5 rounded-xl bg-white border border-slate-200/90 min-h-[120px] text-sm text-slate-800 whitespace-pre-line leading-relaxed shadow-2xs">
                                <template x-if="currentPeserta.jawaban_teks && currentPeserta.jawaban_teks.trim() !== ''">
                                    <span x-text="currentPeserta.jawaban_teks"></span>
                                </template>
                                <template x-if="!currentPeserta.jawaban_teks || currentPeserta.jawaban_teks.trim() === ''">
                                    <span class="text-slate-400 italic text-xs">
                                        Siswa tidak mengisi jawaban pada nomor ini.
                                    </span>
                                </template>
                            </div>
                        </div>

                        {{-- FOOTER NAVIGASI --}}
                        <div
                            class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-5 border-t border-slate-100">
                            <button type="button" @click="prevSiswa()" :disabled="currentSiswaIndex === 0"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 border border-slate-200/80 text-xs font-semibold text-slate-700 disabled:opacity-40 transition cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                                </svg>
                                <span>Siswa Sebelumnya</span>
                            </button>

                            <div class="flex items-center gap-2.5 w-full sm:w-auto flex-wrap">
                                <button type="button" @click="nextSoalEssay()"
                                    :disabled="currentSoalIndex >= soals.length - 1"
                                    class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-blue-50/70 hover:bg-blue-100 text-blue-600 border border-blue-200/80 text-xs font-semibold disabled:opacity-40 transition cursor-pointer">
                                    <span>Soal Essay Berikutnya</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                                    </svg>
                                </button>

                                <button type="button" @click="nextSiswa()"
                                    :disabled="currentSiswaIndex >= currentSoal.pesertas.length - 1"
                                    class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-xs disabled:opacity-40 transition cursor-pointer">
                                    <span>Simpan &amp; Siswa Selanjutnya</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

            </div>
        </div>
    </template>
</div>

<script>
function koreksiEssayMassal(config) {
    return {
        soals: config.soals || [],
        currentSoalIndex: 0,
        currentSiswaIndex: 0,
        isSaving: false,
        lastSaved: false,

        get currentSoal() {
            return this.soals[this.currentSoalIndex] || null;
        },

        get currentPeserta() {
            if (!this.currentSoal) return null;
            return this.currentSoal.pesertas[this.currentSiswaIndex] || null;
        },

        hitungTernilai(soalObj) {
            return (soalObj.pesertas || []).filter(p => p.sudah_dinilai).length;
        },

        pilihSoal(idx) {
            this.currentSoalIndex = idx;
            this.currentSiswaIndex = 0;
        },

        setQuickScore(nilai) {
            if (!this.currentPeserta) return;
            this.currentPeserta.skor = nilai;
            this.simpanSkorSekarang();
        },

        async simpanSkorSekarang() {
            const p = this.currentPeserta;
            const s = this.currentSoal;
            if (!p || !s || p.skor === '' || p.skor === null) return;

            let skorNum = parseFloat(p.skor);
            if (isNaN(skorNum) || skorNum < 0) skorNum = 0;
            if (skorNum > s.bobot_maks) skorNum = s.bobot_maks;
            p.skor = skorNum;

            this.isSaving = true;
            this.lastSaved = false;

            try {
                const res = await fetch(config.saveUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        ujian_id: p.ujian_id,
                        siswa_id: p.siswa_id,
                        soal_id: p.soal_id,
                        skor: skorNum
                    })
                });

                if (res.ok) {
                    p.sudah_dinilai = true;
                    this.lastSaved = true;
                    setTimeout(() => { this.lastSaved = false; }, 2000);
                }
            } catch (err) {
                console.error('Gagal menyimpan nilai essay:', err);
            } finally {
                this.isSaving = false;
            }
        },

        async prevSiswa() {
            await this.simpanSkorSekarang();
            if (this.currentSiswaIndex > 0) this.currentSiswaIndex--;
        },

        async nextSiswa() {
            await this.simpanSkorSekarang();
            if (this.currentSiswaIndex < this.currentSoal.pesertas.length - 1) {
                this.currentSiswaIndex++;
            }
        },

        async nextSoalEssay() {
            await this.simpanSkorSekarang();
            if (this.currentSoalIndex < this.soals.length - 1) {
                this.currentSoalIndex++;
                this.currentSiswaIndex = 0;
            }
        }
    };
}
</script>
@endsection
