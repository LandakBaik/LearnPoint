@extends('layouts.app')

@section('title', 'Koreksi Essay Massal: ' . $ujian->judul)

@section('content')
<div class="space-y-6" x-data="koreksiEssayMassal({
    soals: {{ Js::from($dataKoreksi) }},
    saveUrl: '{{ route('guru.ujian.simpan-nilai-essay', $ujian->id) }}',
    csrfToken: '{{ csrf_token() }}'
})">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('guru.ujian.rekap', $ujian->id) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-500 hover:text-blue-600 transition">
                &larr; Kembali ke Rekap Nilai
            </a>
            <h1 class="text-xl sm:text-2xl font-black text-gray-900 mt-1">
                ✍️ Koreksi Essay Massal: {{ $ujian->judul }}
            </h1>
        </div>

        <div class="flex items-center gap-2">
            <span x-show="isSaving" class="text-xs font-bold text-amber-600 bg-amber-50 border border-amber-200 px-3 py-1.5 rounded-xl animate-pulse">
                Menyimpan nilai...
            </span>
            <span x-show="lastSaved && !isSaving" class="text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-xl">
                &check; Nilai Tersimpan Otomatis
            </span>
        </div>
    </div>

    <template x-if="soals.length === 0">
        <div class="bg-white rounded-3xl p-12 text-center border border-gray-200">
            <p class="text-sm font-bold text-gray-700">Ujian ini tidak memiliki soal bertipe Essay.</p>
        </div>
    </template>

    <template x-if="soals.length > 0">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            {{-- SIDEBAR KIRI: DAFTAR SOAL ESSAY & PROGRES PENILAIAN --}}
            <div class="lg:col-span-4 xl:col-span-3 bg-white rounded-3xl p-5 border border-gray-200 shadow-xs h-fit space-y-4">
                <div>
                    <h2 class="text-xs font-black uppercase tracking-wider text-gray-400">Daftar Soal Essay</h2>
                    <p class="text-[11px] text-gray-500 mt-0.5">Pilih soal untuk menilai seluruh siswa</p>
                </div>

                <div class="space-y-2.5">
                    <template x-for="(s, idx) in soals" :key="s.nomor_essay">
                        <button type="button" @click="pilihSoal(idx)"
                            class="w-full text-left p-3.5 rounded-2xl border transition-all cursor-pointer"
                            :class="{
                                'border-teal-600 bg-teal-50/70 ring-2 ring-teal-500/20': currentSoalIndex === idx,
                                'border-emerald-200 bg-emerald-50/40 hover:border-emerald-400': currentSoalIndex !== idx && hitungTernilai(s) === s.pesertas.length && s.pesertas.length > 0,
                                'border-gray-200 hover:border-gray-300 bg-white': currentSoalIndex !== idx && hitungTernilai(s) < s.pesertas.length
                            }">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-black text-gray-900">
                                    Soal Essay #<span x-text="s.nomor_essay"></span>
                                    <span class="text-[11px] font-normal text-gray-400" x-text="'(No. ' + s.nomor_asli + ')'"></span>
                                </span>

                                <template x-if="hitungTernilai(s) === s.pesertas.length && s.pesertas.length > 0">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-700">
                                        &check; Tuntas
                                    </span>
                                </template>
                                <template x-if="hitungTernilai(s) < s.pesertas.length">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800"
                                        x-text="hitungTernilai(s) + '/' + s.pesertas.length + ' Ternilai'"></span>
                                </template>
                            </div>
                            <p class="text-[11px] text-gray-500 mt-1.5 line-clamp-2" x-text="s.pertanyaan"></p>
                        </button>
                    </template>
                </div>
            </div>

            {{-- PANEL KANAN: EDITOR KOREKSI PER SISWA --}}
            <div class="lg:col-span-8 xl:col-span-9 bg-white rounded-3xl p-6 sm:p-8 border border-gray-200 shadow-xs space-y-6">

                <template x-if="currentSoal && currentPeserta">
                    <div class="space-y-6">
                        {{-- Header Soal & Identitas Siswa --}}
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-gray-100">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="px-3 py-1 rounded-xl text-xs font-extrabold bg-teal-50 text-teal-700 border border-teal-200">
                                        Soal Essay #<span x-text="currentSoal.nomor_essay"></span>
                                    </span>
                                    <span class="text-xs font-bold text-gray-400">
                                        Siswa ke-<span x-text="currentSiswaIndex + 1"></span> dari <span x-text="currentSoal.pesertas.length"></span>
                                    </span>
                                </div>
                                <h2 class="text-lg font-black text-gray-900 mt-2">
                                    <span x-text="currentPeserta.nama"></span>
                                    <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2.5 py-0.5 rounded-lg ml-1" x-text="'Kelas ' + currentPeserta.kelas"></span>
                                </h2>
                            </div>

                            {{-- Dropdown Cepat Pilih Siswa + Status Badge --}}
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold border"
                                    :class="currentPeserta.sudah_dinilai ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200'"
                                    x-text="currentPeserta.sudah_dinilai ? '✓ Sudah Dinilai' : '⏳ Belum Dinilai'"></span>

                                <select x-model.number="currentSiswaIndex"
                                    class="rounded-xl border border-gray-300 px-3 py-1.5 text-xs font-bold text-gray-700 outline-none cursor-pointer">
                                    <template x-for="(p, pIdx) in currentSoal.pesertas" :key="p.siswa_id">
                                        <option :value="pIdx" x-text="(pIdx + 1) + '. ' + p.nama + (p.sudah_dinilai ? ' (✓)' : '')"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        {{-- KOTAK INPUT NILAI SISWA & BOBOT MAKSIMAL (Menggantikan Tingkat Kesulitan & Bobot) --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-200">
                            <div>
                                <label class="block text-xs font-extrabold uppercase tracking-wider text-gray-700 mb-1.5">
                                    Input Skor Perolehan Siswa
                                </label>
                                <input type="number" step="0.01" min="0" :max="currentSoal.bobot_maks"
                                    x-model="currentPeserta.skor"
                                    @change="simpanSkorSekarang()"
                                    placeholder="0"
                                    class="w-full border-2 border-teal-500 rounded-xl px-4 py-2.5 text-lg font-black text-gray-900 bg-white focus:ring-4 focus:ring-teal-100 outline-none">

                                {{-- Tombol Quick Score --}}
                                <div class="flex items-center gap-1.5 mt-2 flex-wrap">
                                    <button type="button" @click="setQuickScore(0)"
                                        class="px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-[11px] font-bold cursor-pointer">
                                        0 (Kosong)
                                    </button>
                                    <button type="button" @click="setQuickScore(Number((currentSoal.bobot_maks / 2).toFixed(2)))"
                                        class="px-2.5 py-1 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 text-[11px] font-bold cursor-pointer">
                                        Setengah (<span x-text="(currentSoal.bobot_maks / 2).toFixed(2)"></span>)
                                    </button>
                                    <button type="button" @click="setQuickScore(currentSoal.bobot_maks)"
                                        class="px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-[11px] font-bold cursor-pointer">
                                        Poin Penuh (<span x-text="currentSoal.bobot_maks"></span>)
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-extrabold uppercase tracking-wider text-gray-500 mb-1.5">
                                    Bobot Maksimal Soal (Read-Only)
                                </label>
                                <div class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-lg font-black text-gray-500 bg-gray-100">
                                    <span x-text="currentSoal.bobot_maks"></span> Poin
                                </div>
                                <p class="text-[11px] text-gray-400 mt-2">
                                    Nilai otomatis dibatasi antara <strong>0</strong> hingga <strong x-text="currentSoal.bobot_maks"></strong> poin.
                                </p>
                            </div>
                        </div>

                        {{-- PERTANYAAN SOAL (READ-ONLY) --}}
                        <div class="space-y-2">
                            <label class="block text-xs font-extrabold uppercase tracking-wider text-gray-400">Pertanyaan Soal:</label>
                            <div class="p-4 rounded-2xl bg-gray-50 border border-gray-200 text-sm text-gray-800 leading-relaxed"
                                x-html="currentSoal.pertanyaan"></div>
                            <template x-if="currentSoal.gambar">
                                <img :src="currentSoal.gambar" class="max-h-56 rounded-2xl border border-gray-200 mt-2">
                            </template>
                        </div>

                        {{-- KUNCI JAWABAN / RUBRIK GURU (JIKA ADA) --}}
                        <template x-if="currentSoal.kunci_jawaban">
                            <div class="p-4 rounded-2xl bg-emerald-50/60 border border-emerald-200 space-y-1">
                                <span class="text-[11px] font-extrabold uppercase tracking-wider text-emerald-700 block">
                                    🔑 Kunci Jawaban / Pedoman Penskoran Guru:
                                </span>
                                <p class="text-xs text-emerald-950 whitespace-pre-line" x-text="currentSoal.kunci_jawaban"></p>
                            </div>
                        </template>

                        {{-- JAWABAN TEKS DARI SISWA --}}
                        <div class="space-y-2">
                            <label class="block text-xs font-extrabold uppercase tracking-wider text-indigo-600">
                                📝 Jawaban Siswa (<span x-text="currentPeserta.nama"></span>):
                            </label>
                            <div class="p-5 rounded-2xl bg-indigo-50/30 border-2 border-indigo-100 min-h-[120px] text-sm text-gray-900 whitespace-pre-line leading-relaxed">
                                <template x-if="currentPeserta.jawaban_teks && currentPeserta.jawaban_teks.trim() !== ''">
                                    <span x-text="currentPeserta.jawaban_teks"></span>
                                </template>
                                <template x-if="!currentPeserta.jawaban_teks || currentPeserta.jawaban_teks.trim() === ''">
                                    <span class="text-gray-400 italic">Siswa tidak mengisi jawaban pada nomor ini.</span>
                                </template>
                            </div>
                        </div>

                        {{-- FOOTER NAVIGASI: SISWA SEBELUMNYA | SISWA SELANJUTNYA | SOAL ESSAY BERIKUTNYA --}}
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-5 border-t border-gray-100">
                            <button type="button" @click="prevSiswa()" :disabled="currentSiswaIndex === 0"
                                class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-gray-300 text-xs font-bold text-gray-700 hover:bg-gray-50 disabled:opacity-40 cursor-pointer">
                                &larr; Siswa Sebelumnya
                            </button>

                            <div class="flex items-center gap-2.5 w-full sm:w-auto">
                                <button type="button" @click="nextSiswa()" :disabled="currentSiswaIndex >= currentSoal.pesertas.length - 1"
                                    class="flex-1 sm:flex-none px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-xs disabled:opacity-40 cursor-pointer">
                                    Simpan &amp; Siswa Selanjutnya &rarr;
                                </button>

                                <button type="button" @click="nextSoalEssay()" :disabled="currentSoalIndex >= soals.length - 1"
                                    class="flex-1 sm:flex-none px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-xs disabled:opacity-40 cursor-pointer">
                                    Soal Essay Berikutnya &#9197;
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
