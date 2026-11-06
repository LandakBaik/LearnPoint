@extends('layouts.siswa')

@section('title', 'Mengerjakan Ujian - ' . ($ujian->judul ?? 'LearnPoint'))

@section('content')
    <div class="max-w-7xl mx-auto py-6 px-4 space-y-6" x-data="ruangUjian({
        soals: {{ Js::from($soalsData) }},
        savedAnswers: {{ Js::from($savedAnswers) }},
        sisaDetikAwal: {{ $sisaDetik }},
        saveUrl: '{{ route('siswa.ujian.simpan-jawaban', $ujian->id) }}',
        csrfToken: '{{ csrf_token() }}'
    })" x-init="initTimer()">

        <!-- Header Ujian & Timer Hitung Mundur -->
        <div
            class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div
                    class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                </div>
                <div>
                    <span
                        class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-600 border border-indigo-100">
                        {{ $ujian->pengampuKelas->guruMapel->mapel->nama_mapel ?? 'Mata Pelajaran' }}
                    </span>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 mt-1">{{ $ujian->judul }}</h1>
                </div>
            </div>

            <!-- Timer Box & Status Auto-Save -->
            <div class="flex items-center gap-3">
                <span x-show="isSaving" class="text-xs text-indigo-600 font-semibold animate-pulse">Menyimpan...</span>
                <span x-show="lastSaved" class="text-xs text-emerald-600 font-semibold">&check; Tersimpan</span>

                <div class="px-5 py-2.5 rounded-2xl border flex items-center gap-2.5 font-mono shadow-xs"
                    :class="sisaDetik < 300 ? 'bg-red-50 border-red-200 text-red-600' :
                        'bg-slate-50 border-slate-200 text-slate-800'">
                    <svg class="w-5 h-5 animate-pulse text-indigo-600" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <span class="text-lg font-bold" x-text="formatWaktu(sisaDetik)"></span>
                </div>

                <a href="{{ route('siswa.materi') }}"
                    onclick="return confirm('Keluar dari halaman ujian? Pastikan jawaban Anda sudah tersimpan.')"
                    class="px-4 py-2.5 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                    &larr; Keluar
                </a>
            </div>
        </div>

        <!-- Layout Utama -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

            <!-- Area Soal & Opsi Jawaban (3 Kolom) -->
            <div
                class="lg:col-span-3 bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm flex flex-col justify-between min-h-[480px]">
                <template x-if="currentSoal">
                    <div>
                        <!-- Baris Info Nomor & Bobot -->
                        <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-100">
                            <span
                                class="text-xs font-bold text-indigo-600 bg-indigo-50 border border-indigo-100 px-3.5 py-1.5 rounded-xl">
                                Soal Nomor <span x-text="currentIndex + 1"></span> dari <span x-text="soals.length"></span>
                            </span>
                            <span class="text-xs font-medium text-slate-400">
                                Bobot: <strong class="text-slate-700" x-text="currentSoal.bobot"></strong> Poin
                            </span>
                        </div>

                        <!-- Gambar Soal (Jika ada) -->
                        <template x-if="currentSoal.gambar">
                            <div class="mb-5 max-w-lg rounded-2xl overflow-hidden border border-slate-200">
                                <img :src="currentSoal.gambar" alt="Gambar Soal" class="w-full object-cover">
                            </div>
                        </template>

                        <!-- Pertanyaan Soal -->
                        <div class="text-slate-800 text-lg font-medium leading-relaxed mb-8"
                            x-html="currentSoal.pertanyaan"></div>

                        <!-- Pilihan Ganda -->
                        <div class="space-y-3">
                            <template x-for="opsi in currentSoal.pilihan" :key="opsi.kode">
                                <label
                                    class="flex items-center p-4 rounded-2xl border cursor-pointer transition-all select-none"
                                    :class="answers[currentSoal.id] === opsi.kode ?
                                        'border-indigo-600 bg-indigo-50/70 ring-2 ring-indigo-600/20 shadow-xs' :
                                        'border-slate-200 hover:border-slate-300 hover:bg-slate-50/50'">
                                    <input type="radio" class="hidden" :name="'soal_' + currentSoal.id"
                                        :value="opsi.kode" @change="pilihOpsi(currentSoal.id, opsi.kode)">

                                    <!-- Huruf Opsi A, B, C, D -->
                                    <span
                                        class="w-9 h-9 rounded-xl font-bold flex items-center justify-center text-sm mr-3.5 transition-colors"
                                        :class="answers[currentSoal.id] === opsi.kode ?
                                            'bg-indigo-600 text-white shadow-xs' :
                                            'bg-slate-100 text-slate-600'"
                                        x-text="opsi.label"></span>

                                    <!-- Teks Pilihan -->
                                    <span class="text-slate-800 font-medium text-base" x-text="opsi.teks"></span>
                                </label>
                            </template>
                        </div>
                        <!-- Form Tersembunyi untuk Submit Ujian -->
                        <form x-ref="formSubmitUjian" action="{{ route('siswa.ujian.submit', $ujian->id) }}" method="POST"
                            class="hidden">
                            @csrf
                        </form>
                    </div>
                </template>

                <!-- Navigasi Bawah -->
                <div class="flex items-center justify-between pt-6 mt-8 border-t border-slate-100">
                    <button type="button" @click="currentIndex--" :disabled="currentIndex === 0"
                        class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-semibold text-xs hover:bg-slate-50 transition disabled:opacity-40 disabled:cursor-not-allowed">
                        &larr; Soal Sebelumnya
                    </button>

                    <button type="button" x-show="currentIndex < soals.length - 1" @click="currentIndex++"
                        class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs transition shadow-xs">
                        Soal Selanjutnya &rarr;
                    </button>

                    <button type="button" x-show="currentIndex === soals.length - 1" @click="konfirmasiSelesai()"
                        class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition shadow-xs">
                        Selesai & Kumpulkan &check;
                    </button>
                </div>
            </div>

            <!-- Sidebar Kanan: Kisi Nomor Soal (1 Kolom) -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm h-fit space-y-5">
                <div>
                    <h2 class="text-sm font-bold text-slate-800">Navigasi Nomor Soal</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Pilih nomor untuk lompat ke soal</p>
                </div>

                <!-- Grid Nomor Soal -->
                <div class="grid grid-cols-4 sm:grid-cols-5 gap-2">
                    <template x-for="(s, idx) in soals" :key="s.id">
                        <button type="button" @click="currentIndex = idx"
                            class="h-10 rounded-xl font-bold text-xs border transition-all flex items-center justify-center"
                            :class="{
                                'ring-2 ring-indigo-600 border-indigo-600': currentIndex === idx,
                                'bg-emerald-500 text-white border-emerald-500 shadow-2xs': answers[s.id],
                                'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100': !answers[s.id]
                            }"
                            x-text="idx + 1">
                        </button>
                    </template>
                </div>

                <!-- Keterangan Status -->
                <div class="pt-4 border-t border-slate-100 space-y-2 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="w-3.5 h-3.5 rounded-md bg-emerald-500"></span>
                        <span class="text-slate-600">Sudah Terjawab</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3.5 h-3.5 rounded-md bg-slate-100 border border-slate-300"></span>
                        <span class="text-slate-600">Belum Terjawab</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3.5 h-3.5 rounded-md border-2 border-indigo-600"></span>
                        <span class="text-slate-600">Soal Aktif</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        function ruangUjian(config) {
            return {
                soals: config.soals || [],
                currentIndex: 0,
                sisaDetik: config.sisaDetikAwal || 3600,
                answers: Object.assign({}, config.savedAnswers),
                isSaving: false,
                lastSaved: false,
                timerInterval: null,

                get currentSoal() {
                    return this.soals[this.currentIndex] || null;
                },

                initTimer() {
                    this.timerInterval = setInterval(() => {
                        if (this.sisaDetik > 0) {
                            this.sisaDetik--;
                        } else {
                            clearInterval(this.timerInterval);
                            alert('Waktu ujian telah berakhir!');
                        }
                    }, 1000);
                },

                formatWaktu(sec) {
                    const h = String(Math.floor(sec / 3600)).padStart(2, '0');
                    const m = String(Math.floor((sec % 3600) / 60)).padStart(2, '0');
                    const s = String(Math.floor(sec % 60)).padStart(2, '0');
                    return `${h}:${m}:${s}`;
                },

                async pilihOpsi(soalId, kodeOpsi) {
                    this.answers[soalId] = kodeOpsi;
                    this.isSaving = true;
                    this.lastSaved = false;

                    try {
                        const response = await fetch(config.saveUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': config.csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                soal_id: soalId,
                                jawaban: kodeOpsi
                            })
                        });

                        if (response.ok) {
                            this.lastSaved = true;
                            setTimeout(() => {
                                this.lastSaved = false;
                            }, 2000);
                        }
                    } catch (err) {
                        console.error('Gagal auto-save jawaban:', err);
                    } finally {
                        this.isSaving = false;
                    }
                },

                konfirmasiSelesai() {
                    const totalDijawab = Object.keys(this.answers).length;
                    const total = this.soals.length;

                    const konfirmasi = confirm(
                        totalDijawab < total ?
                        `Perhatian: Kamu baru menjawab ${totalDijawab} dari ${total} soal. Yakin ingin mengumpulkan ujian sekarang?` :
                        `Semua soal (${total}) telah dijawab. Yakin ingin mengumpulkan ujian sekarang?`
                    );

                    if (konfirmasi) {
                        clearInterval(this.timerInterval);
                        this.$refs.formSubmitUjian.submit();
                    }
                }
            };
        }
    </script>

@endsection
