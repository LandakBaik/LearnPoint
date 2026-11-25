@extends('layouts.siswa')

@section('title', 'Mengerjakan Ujian - ' . ($ujian->judul ?? 'LearnPoint'))

@section('content')
    @php
        $userSiswa = auth()->user();
        $idSiswaAcak = (int) ($sesi->siswa_id ?? $userSiswa?->siswa?->id ?? $userSiswa?->siswa_id ?? $userSiswa?->id ?? 1);
        $idUjianAcak = (int) ($ujian->id ?? 1);

        $koleksiSoal = collect($soalsData)->values();
        $totalSoalAcak = $koleksiSoal->count();

        if ($totalSoalAcak > 1) {
            // 1. Urutkan dasar secara konsisten per ujian
            $urutanDasar = $koleksiSoal->sortBy(function ($item) use ($idUjianAcak) {
                $soalId = is_array($item) ? ($item['id'] ?? 0) : ($item->id ?? 0);
                return md5("base-ujian-{$idUjianAcak}-soal-{$soalId}");
            })->values();

            // 2. Pilih soal pertama berdasarkan rotasi ID siswa agar antar-siswa tidak mendapat Nomor 1 yang sama
            $indexPertama = ($idSiswaAcak + $idUjianAcak) % $totalSoalAcak;
            $soalPertama = $urutanDasar->get($indexPertama);

            // 3. Acak sisa soal lainnya secara unik untuk masing-masing siswa
            $sisaSoal = $urutanDasar->forget($indexPertama)->sortBy(function ($item) use ($idUjianAcak, $idSiswaAcak) {
                $soalId = is_array($item) ? ($item['id'] ?? 0) : ($item->id ?? 0);
                return md5("acak-ujian-{$idUjianAcak}-siswa-{$idSiswaAcak}-soal-{$soalId}");
            })->values();

            $soalsDataAcak = collect([$soalPertama])->concat($sisaSoal)->values();
        } else {
            $soalsDataAcak = $koleksiSoal;
        }
    @endphp

    <div class="max-w-7xl mx-auto py-6 px-4 space-y-6" x-data="ruangUjian({
        soals: {{ Js::from($soalsDataAcak) }},
        savedAnswers: {{ Js::from($savedAnswers) }},
        sisaDetikAwal: {{ $sisaDetik }},
        saveUrl: '{{ route('siswa.ujian.simpan-jawaban', $ujian->id) }}',
        csrfToken: '{{ csrf_token() }}'
    })">

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

            <!-- Timer Box & Tombol Keluar Darurat -->
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

                {{-- Tombol Keluar (Memicu Auto Submit & Redirect ke Dashboard) --}}
                <button type="button" @click="konfirmasiKeluar()"
                    class="px-4 py-2.5 text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-xl transition cursor-pointer flex items-center gap-1.5 shadow-2xs">
                    &larr; Keluar
                </button>
            </div>
        </div>

        <!-- Layout Utama Soal -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

            <!-- Area Soal & Opsi Jawaban (3 Kolom) -->
            <div
                class="lg:col-span-3 bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm flex flex-col justify-between min-h-[480px]">
                <template x-if="currentSoal">
                    <div>
                        <!-- Baris Info Nomor & Bobot -->
                        <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-100">
                            <div class="flex items-center gap-2">
                                <span
                                    class="text-xs font-bold text-indigo-600 bg-indigo-50 border border-indigo-100 px-3.5 py-1.5 rounded-xl">
                                    Soal Nomor <span x-text="currentIndex + 1"></span> dari <span x-text="soals.length"></span>
                                </span>
                                <span
                                    class="text-[11px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600"
                                    x-text="currentSoal.tipe_soal === 'essay' ? 'Essay' : (currentSoal.tipe_soal === 'multiple_choice' ? 'Pilihan Ganda Kompleks' : 'Pilihan Ganda')">
                                </span>
                            </div>
                            <span class="text-xs font-medium text-slate-400">
                                Bobot: <strong class="text-slate-700" x-text="currentSoal.bobot"></strong> Poin
                            </span>
                        </div>

                        <!-- Gambar Soal -->
                        <template x-if="currentSoal.gambar">
                            <div
                                class="mb-6 inline-block rounded-2xl overflow-hidden border border-slate-200 bg-slate-50 p-2">
                                <img :src="currentSoal.gambar" alt="Gambar Soal"
                                    class="max-h-80 w-auto rounded-xl object-contain mx-auto">
                            </div>
                        </template>

                        <!-- Pertanyaan -->
                        <div class="text-slate-800 text-lg font-medium leading-relaxed mb-8 whitespace-pre-line"
                            x-text="currentSoal.pertanyaan"></div>

                        <!-- 1. JAWABAN PILIHAN GANDA (SINGLE & MULTIPLE CHOICE) -->
                        <template x-if="currentSoal.tipe_soal !== 'essay'">
                            <div class="space-y-3">
                                <template x-for="opsi in currentSoal.pilihan" :key="opsi.kode">
                                    <label
                                        class="flex items-center p-4 rounded-2xl border cursor-pointer transition-all select-none"
                                        :class="isOpsiDipilih(currentSoal.id, opsi.kode) ?
                                            'border-indigo-600 bg-indigo-50/70 ring-2 ring-indigo-600/20 shadow-xs' :
                                            'border-slate-200 hover:border-slate-300 hover:bg-slate-50/50'">

                                        <template x-if="currentSoal.tipe_soal !== 'multiple_choice'">
                                            <input type="radio" class="hidden" :name="'soal_' + currentSoal.id"
                                                :value="opsi.kode" @change="pilihOpsiSingle(currentSoal.id, opsi.kode)">
                                        </template>

                                        <template x-if="currentSoal.tipe_soal === 'multiple_choice'">
                                            <input type="checkbox" class="hidden" :value="opsi.kode"
                                                @change="toggleOpsiMultiple(currentSoal.id, opsi.kode)">
                                        </template>

                                        <span
                                            class="w-9 h-9 rounded-xl font-bold flex items-center justify-center text-sm mr-3.5 transition-colors shrink-0"
                                            :class="isOpsiDipilih(currentSoal.id, opsi.kode) ?
                                                'bg-indigo-600 text-white shadow-xs' :
                                                'bg-slate-100 text-slate-600'"
                                            x-text="opsi.label"></span>

                                        <span class="text-slate-800 font-medium text-base" x-text="opsi.teks"></span>
                                    </label>
                                </template>
                            </div>
                        </template>

                        <!-- 2. FORM JAWABAN ESSAY -->
                        <template x-if="currentSoal.tipe_soal === 'essay'">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <label :for="'essay_' + currentSoal.id"
                                        class="block text-sm font-bold text-slate-700">
                                        Ketik Jawaban Essay Anda:
                                    </label>
                                    <span class="text-xs font-medium text-slate-400"
                                        x-text="(answers[currentSoal.id] ? String(answers[currentSoal.id]).length : 0) + ' karakter'">
                                    </span>
                                </div>

                                <textarea :id="'essay_' + currentSoal.id" rows="7"
                                    :value="answers[currentSoal.id] || ''"
                                    @input="ubahJawabanEssay(currentSoal.id, $event.target.value)"
                                    @blur="simpanEssaySekarang(currentSoal.id)"
                                    placeholder="Tuliskan jawaban essay Anda secara jelas dan lengkap di sini..."
                                    class="w-full rounded-2xl border-2 border-slate-200 focus:border-indigo-600 focus:ring-4 focus:ring-indigo-600/10 p-4 text-slate-800 text-base leading-relaxed outline-none transition resize-y bg-slate-50/40 focus:bg-white"></textarea>

                                <p class="text-xs text-slate-400 flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Jawaban essay tersimpan otomatis saat Anda mengetik atau berpindah soal.
                                </p>
                            </div>
                        </template>

                        <!-- Form Submit Ujian -->
                        <form x-ref="formSubmitUjian" action="{{ route('siswa.ujian.submit', $ujian->id) }}" method="POST"
                            class="hidden">
                            @csrf
                            <input type="hidden" name="redirect_to" x-ref="inputRedirectTo" value="">
                        </form>
                    </div>
                </template>

                <!-- Navigasi Bawah -->
                <div class="flex items-center justify-between pt-6 mt-8 border-t border-slate-100">
                    <button type="button" @click="gantiNomorSoal(currentIndex - 1)" :disabled="currentIndex === 0"
                        class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-semibold text-xs hover:bg-slate-50 transition disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer">
                        &larr; Soal Sebelumnya
                    </button>

                    <button type="button" x-show="currentIndex < soals.length - 1"
                        @click="gantiNomorSoal(currentIndex + 1)"
                        class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs transition shadow-xs cursor-pointer">
                        Soal Selanjutnya &rarr;
                    </button>

                    <button type="button" x-show="currentIndex === soals.length - 1" @click="konfirmasiSelesai()"
                        class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition shadow-xs cursor-pointer">
                        Selesai & Kumpulkan &check;
                    </button>
                </div>
            </div>

            <!-- Sidebar Kisi Nomor Soal (1 Kolom) -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm h-fit space-y-5">
                <div>
                    <h2 class="text-sm font-bold text-slate-800">Navigasi Nomor Soal</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Pilih nomor untuk lompat ke soal</p>
                </div>

                <div class="grid grid-cols-4 sm:grid-cols-5 gap-2">
                    <template x-for="(s, idx) in soals" :key="s.id">
                        <button type="button" @click="gantiNomorSoal(idx)"
                            class="h-10 rounded-xl font-bold text-xs border transition-all flex items-center justify-center cursor-pointer"
                            :class="{
                                'ring-2 ring-indigo-600 border-indigo-600': currentIndex === idx,
                                'bg-emerald-500 text-white border-emerald-500 shadow-2xs': isSoalTerjawab(s.id),
                                'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100': !isSoalTerjawab(s.id)
                            }"
                            x-text="idx + 1">
                        </button>
                    </template>
                </div>

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

        {{-- =========================================================
            MODAL 1: WAJIB MODE FULLSCREEN (SEBELUM & SAAT UJIAN)
        ========================================================== --}}
        <div x-show="!isFullscreen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/95 backdrop-blur-md p-4">
            <div class="bg-white rounded-3xl max-w-md w-full p-8 text-center shadow-2xl border border-slate-200">
                <div
                    class="w-20 h-20 mx-auto rounded-3xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-4xl mb-5 font-black">
                    ⛶
                </div>
                <h2 class="text-xl font-black text-slate-900"
                    x-text="hasStartedFullscreen ? 'Layar Penuh Terputus!' : 'Wajib Mode Layar Penuh'"></h2>
                <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                    <template x-if="!hasStartedFullscreen">
                        <span>Untuk menjaga integritas dan fokus evaluasi, Anda wajib mengaktifkan mode layar penuh (full
                            screen) sebelum memulai pengerjaan ujian.</span>
                    </template>
                    <template x-if="hasStartedFullscreen">
                        <span class="text-rose-600 font-semibold">Anda terdeteksi keluar dari mode layar penuh. Aktivitas
                            ini dicatat sebagai indikasi pelanggaran. Segera kembali ke layar penuh untuk melanjutkan
                            ujian.</span>
                    </template>
                </p>

                <div class="mt-6">
                    <button type="button" @click="masukFullscreen()"
                        class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl transition cursor-pointer shadow-md flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5"
                            viewBox="0 0 24 24">
                            <path
                                d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4">
                            </path>
                        </svg>
                        <span
                            x-text="hasStartedFullscreen ? 'Kembali ke Layar Penuh' : 'Aktifkan Layar Penuh & Mulai Ujian'"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- =========================================================
            MODAL 2: KONFIRMASI KELUAR (AUTO SUBMIT & REDIRECT)
        ========================================================== --}}
        <div x-show="showExitModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 text-center shadow-2xl border-2 border-rose-200">
                <div
                    class="w-16 h-16 mx-auto rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center text-3xl mb-4 font-black">
                    🚪
                </div>
                <h3 class="text-lg font-black text-slate-900">Keluar & Kumpulkan Ujian?</h3>
                <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                    Perhatian: Jika Anda keluar sekarang, seluruh jawaban yang telah dipilih akan <strong>OTOMATIS
                        DIKUMPULKAN</strong> ke sistem dan Anda akan dialihkan ke Dashboard. Sesi ujian Anda dinyatakan
                    <strong>SELESAI</strong> dan tidak dapat diulang kembali.
                </p>

                <div class="mt-6 flex flex-col gap-2">
                    <button type="button" @click="eksekusiKeluarDanSubmit()"
                        class="w-full py-3 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl transition cursor-pointer shadow-md">
                        Ya, Kumpulkan Jawaban & Keluar ke Dashboard
                    </button>
                    <button type="button" @click="showExitModal = false"
                        class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition cursor-pointer">
                        Batal & Lanjutkan Mengerjakan
                    </button>
                </div>
            </div>
        </div>

        {{-- =========================================================
            MODAL 3: PERINGATAN PELANGGARAN TAB (ANTI-CURANG)
        ========================================================== --}}
        <div x-show="showWarningModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 text-center shadow-2xl border-2 border-rose-200">
                <div
                    class="w-16 h-16 mx-auto rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center text-3xl mb-4 font-black">
                    ⚠️
                </div>
                <h3 class="text-lg font-black text-gray-900">Peringatan Aktivitas Mencurigakan!</h3>
                <p class="text-xs text-gray-600 mt-2 leading-relaxed">
                    Anda terdeteksi berpindah jendela/aplikasi atau meminimalkan browser. Aktivitas keluar layar dicatat
                    oleh pengawas.
                </p>

                <div class="my-4 p-3 bg-rose-50 rounded-2xl border border-rose-200 space-y-1">
                    <p class="text-xs font-bold text-rose-800">
                        Peringatan ke-<span class="text-sm font-black" x-text="pelanggaranCount"></span> dari <span
                            x-text="maxToleransi"></span>
                    </p>
                    <p class="text-[11px] text-rose-600">
                        Jika mencapai <span x-text="maxToleransi"></span> kali atau lebih, ujian akan langsung <strong>TERKUNCI
                            OTOMATIS</strong>.
                    </p>
                </div>

                <button type="button" @click="tutupPeringatan()"
                    class="w-full py-3 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl transition cursor-pointer shadow-md">
                    Saya Mengerti & Kembali Mengerjakan
                </button>
            </div>
        </div>

        {{-- =========================================================
            MODAL 4: LAYAR TERKUNCI PERMANEN (BUKA VIA GURU ATAU KODE)
        ========================================================== --}}
        <div x-show="isLocked" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/90 backdrop-blur-md p-4">
            <div class="bg-white rounded-3xl max-w-md w-full p-7 sm:p-8 text-center shadow-2xl border border-gray-200">
                <div
                    class="w-16 h-16 mx-auto rounded-3xl bg-rose-100 text-rose-600 flex items-center justify-center text-3xl mb-4 font-black">
                    🔒
                </div>
                <span
                    class="inline-block px-3 py-1 rounded-full text-[11px] font-extrabold bg-rose-50 text-rose-600 border border-rose-200 mb-2">
                    SESI DIBLOKIR SISTEM
                </span>
                <h2 class="text-xl font-black text-gray-900">Halaman Anda Terkunci</h2>
                <p class="text-xs text-gray-600 mt-2 leading-relaxed">
                    Halaman Anda terkunci karena melampaui batas toleransi meninggalkan layar (<span
                        class="font-bold text-rose-600" x-text="pelanggaranCount + 'x Pelanggaran'"></span>).
                    <br>
                    <strong class="text-gray-800">Hubungi admin/pengawas atau masukkan kode untuk memulai kembali
                        ujian.</strong>
                </p>

                {{-- Form Input Kode Buka Blokir --}}
                <div class="mt-5 p-4 rounded-2xl bg-slate-50 border border-slate-200 text-left space-y-3">
                    <label class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-600 text-center">
                        Masukkan Kode Buka Blokir
                    </label>
                    <input type="text" x-model="inputUnlockCode" @keydown.enter.prevent="submitUnlockCode()"
                        maxlength="10" autocomplete="off" placeholder="Contoh: A7K9P2"
                        class="w-full text-center text-xl font-black font-mono tracking-widest uppercase py-2.5 px-3 rounded-xl border-2 border-slate-300 focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100 outline-none bg-white">

                    <p x-show="unlockError" x-text="unlockError" class="text-[11px] font-bold text-rose-600 text-center">
                    </p>

                    <button type="button" @click="submitUnlockCode()" :disabled="isUnlocking || !inputUnlockCode.trim()"
                        class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl transition shadow-xs cursor-pointer disabled:opacity-50">
                        <span x-text="isUnlocking ? 'Memverifikasi...' : '🔓 Buka Blokir & Lanjutkan Ujian'"></span>
                    </button>
                </div>

                <div
                    class="mt-5 pt-4 border-t border-gray-100 flex items-center justify-center gap-2 text-[11px] text-gray-400">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                    <span>Atau tunggu pengawas menekan tombol <strong>Buka Kunci</strong> di panel monitoring...</span>
                </div>
            </div>
        </div>
    </div>

    {{-- =========================================================
    LOGIKA JAVASCRIPT & STATE ALPINE.JS
========================================================== --}}
    <script>
        function ruangUjian(config) {
            return {
                soals: config.soals || [],
                currentIndex: 0,
                sisaDetik: Math.max(0, Math.floor(Number(config.sisaDetikAwal) || 0)),
                answers: Object.assign({}, config.savedAnswers),
                isSaving: false,
                lastSaved: false,
                isSubmitting: false,
                timerInterval: null,
                heartbeatInterval: null,

                // Timer & Buffer Auto-save Khusus Essay
                essaySaveTimers: {},
                pendingEssayValues: {},

                // Variabel Layar Penuh (Fullscreen)
                isFullscreen: false,
                hasStartedFullscreen: false,

                // Variabel Keluar Ujian
                showExitModal: false,

                // Variabel Anti-Curang
                pelanggaranCount: {{ (int) ($sesi->pelanggaran_count ?? 0) }},
                maxToleransi: 3,
                showWarningModal: false,
                isLocked: {{ ($sesi->status ?? '') === 'terkunci' ? 'true' : 'false' }},
                cooldownPelanggaran: false,

                // Variabel Unlock Code
                inputUnlockCode: '',
                unlockError: '',
                isUnlocking: false,

                get currentSoal() {
                    return this.soals[this.currentIndex] || null;
                },

                init() {
                    this.isFullscreen = !!(document.fullscreenElement || document.webkitFullscreenElement);
                    this.pasangListenerFullscreen();
                    this.pasangDeteksiKecurangan();
                    this.mulaiHeartbeatSesi();
                    this.initTimer();
                },

                // ==========================================
                // NAVIGASI SOAL & CEK STATUS TERJAWAB
                // ==========================================
                async gantiNomorSoal(targetIdx) {
                    if (targetIdx < 0 || targetIdx >= this.soals.length) return;
                    if (this.currentSoal && this.currentSoal.tipe_soal === 'essay') {
                        await this.simpanEssaySekarang(this.currentSoal.id);
                    }
                    this.currentIndex = targetIdx;
                },

                isSoalTerjawab(soalId) {
                    const val = this.answers[soalId];
                    if (val === undefined || val === null) return false;
                    if (Array.isArray(val)) return val.length > 0;
                    return String(val).trim() !== '';
                },

                // ==========================================
                // MANAJEMEN MODE FULLSCREEN
                // ==========================================
                masukFullscreen() {
                    const el = document.documentElement;
                    if (el.requestFullscreen) {
                        el.requestFullscreen().catch(err => console.error(err));
                    } else if (el.webkitRequestFullscreen) {
                        el.webkitRequestFullscreen();
                    } else if (el.msRequestFullscreen) {
                        el.msRequestFullscreen();
                    }
                },

                keluarFullscreen() {
                    if (document.fullscreenElement || document.webkitFullscreenElement) {
                        if (document.exitFullscreen) {
                            document.exitFullscreen().catch(err => console.error(err));
                        } else if (document.webkitExitFullscreen) {
                            document.webkitExitFullscreen();
                        }
                    }
                },

                pasangListenerFullscreen() {
                    const handleFsChange = () => {
                        const fs = !!(document.fullscreenElement || document.webkitFullscreenElement);
                        this.isFullscreen = fs;

                        if (fs) {
                            this.hasStartedFullscreen = true;
                        } else {
                            if (this.hasStartedFullscreen && !this.isSubmitting && !this.isLocked) {
                                this.kirimPelanggaran('Keluar dari mode layar penuh (Fullscreen)');
                            }
                        }
                    };

                    document.addEventListener('fullscreenchange', handleFsChange);
                    document.addEventListener('webkitfullscreenchange', handleFsChange);
                },

                // ==========================================
                // ALUR KELUAR -> AUTO SUBMIT -> DASHBOARD
                // ==========================================
                konfirmasiKeluar() {
                    this.showExitModal = true;
                },

                eksekusiKeluarDanSubmit() {
                    this.showExitModal = false;

                    if (this.$refs.inputRedirectTo) {
                        this.$refs.inputRedirectTo.value = 'dashboard';
                    }

                    this.eksekusiSubmitOtomatis('Menyimpan jawaban dan mengumpulkan ujian...');
                },

                async eksekusiSubmitOtomatis(pesan) {
                    if (this.isSubmitting) return;
                    this.isSubmitting = true;

                    if (this.heartbeatInterval) clearInterval(this.heartbeatInterval);
                    if (this.timerInterval) clearInterval(this.timerInterval);

                    // Pastikan jawaban essay yang baru diketik terkirim sebelum form di-submit
                    await this.flushSemuaEssay();

                    this.keluarFullscreen();

                    if (pesan && typeof Swal !== 'undefined') {
                        await Swal.fire({
                            icon: 'info',
                            title: 'Mengumpulkan Ujian',
                            text: pesan,
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#4f46e5',
                            allowOutsideClick: false,
                        });
                    }

                    if (this.$refs.formSubmitUjian) {
                        this.$refs.formSubmitUjian.submit();
                    }
                },

                // ==========================================
                // TIMER & SINKRONISASI SESI
                // ==========================================
                initTimer() {
                    if (this.timerInterval) clearInterval(this.timerInterval);

                    this.timerInterval = setInterval(() => {
                        if (this.sisaDetik > 0) {
                            this.sisaDetik--;
                        } else {
                            clearInterval(this.timerInterval);
                            this.eksekusiSubmitOtomatis(
                                'Waktu ujian telah berakhir! Jawaban otomatis dikumpulkan.');
                        }
                    }, 1000);
                },

                mulaiHeartbeatSesi() {
                    if (this.heartbeatInterval) clearInterval(this.heartbeatInterval);

                    this.heartbeatInterval = setInterval(async () => {
                        try {
                            const res = await fetch('{{ route('siswa.ujian.status-sesi', $ujian->id) }}', {
                                headers: {
                                    'Accept': 'application/json'
                                }
                            });
                            if (!res.ok) return;

                            const data = await res.json();

                            if (data.status === 'ditarik') {
                                clearInterval(this.heartbeatInterval);
                                clearInterval(this.timerInterval);
                                this.keluarFullscreen();
                                if (typeof Swal !== 'undefined') {
                                    await Swal.fire({
                                        icon: 'warning',
                                        title: 'Ujian Ditarik',
                                        text: data.message || 'Ujian sedang ditarik oleh guru pengawas.',
                                        confirmButtonText: 'OK',
                                        confirmButtonColor: '#4f46e5',
                                        allowOutsideClick: false,
                                    });
                                }
                                window.location.href = data.redirect_url;
                                return;
                            }

                            if (data.status === 'selesai') {
                                clearInterval(this.heartbeatInterval);
                                clearInterval(this.timerInterval);
                                this.keluarFullscreen();
                                if (typeof Swal !== 'undefined') {
                                    await Swal.fire({
                                        icon: 'info',
                                        title: 'Ujian Selesai',
                                        text: 'Ujian Anda telah ditarik paksa dan diselesaikan oleh guru pengawas.',
                                        confirmButtonText: 'Lihat Hasil',
                                        confirmButtonColor: '#4f46e5',
                                        allowOutsideClick: false,
                                    });
                                }
                                window.location.href = data.redirect_url;
                                return;
                            }

                            if (data.is_timeout) {
                                this.eksekusiSubmitOtomatis(
                                    'Waktu sesi ujian telah berakhir! Jawaban Anda otomatis dikumpulkan.');
                                return;
                            }

                            if (data.status === 'terkunci' && !this.isLocked) {
                                this.isLocked = true;
                                this.showWarningModal = false;
                            }

                            if (data.status === 'sedang_mengerjakan' && this.isLocked) {
                                this.isLocked = false;
                                this.pelanggaranCount = data.pelanggaran_count || 0;
                            }
                        } catch (err) {
                            console.error('Heartbeat error:', err);
                        }
                    }, 12000);
                },

                formatWaktu(sec) {
                    sec = Math.max(0, Math.floor(Number(sec) || 0));
                    const h = String(Math.floor(sec / 3600)).padStart(2, '0');
                    const m = String(Math.floor((sec % 3600) / 60)).padStart(2, '0');
                    const s = String(sec % 60).padStart(2, '0');
                    return `${h}:${m}:${s}`;
                },

                // ==========================================
                // AUTO-SAVE & JAWABAN (PG & ESSAY)
                // ==========================================
                isOpsiDipilih(soalId, kodeOpsi) {
                    const val = this.answers[soalId];
                    if (!val) return false;
                    if (Array.isArray(val)) return val.includes(kodeOpsi);
                    if (typeof val === 'string' && val.includes(',')) {
                        return val.split(',').includes(kodeOpsi);
                    }
                    return val === kodeOpsi;
                },

                pilihOpsiSingle(soalId, kodeOpsi) {
                    this.answers[soalId] = kodeOpsi;
                    this.kirimAutoSave(soalId, kodeOpsi);
                },

                toggleOpsiMultiple(soalId, kodeOpsi) {
                    let current = this.answers[soalId];
                    if (!Array.isArray(current)) {
                        current = current ? String(current).split(',') : [];
                    }

                    const index = current.indexOf(kodeOpsi);
                    if (index > -1) {
                        current.splice(index, 1);
                    } else {
                        current.push(kodeOpsi);
                    }

                    this.answers[soalId] = current;
                    this.kirimAutoSave(soalId, current.join(','));
                },

                ubahJawabanEssay(soalId, nilaiTeks) {
                    this.answers[soalId] = nilaiTeks;
                    this.pendingEssayValues[soalId] = nilaiTeks;

                    if (this.essaySaveTimers[soalId]) {
                        clearTimeout(this.essaySaveTimers[soalId]);
                    }

                    this.essaySaveTimers[soalId] = setTimeout(() => {
                        this.simpanEssaySekarang(soalId);
                    }, 700);
                },

                async simpanEssaySekarang(soalId) {
                    if (this.essaySaveTimers[soalId]) {
                        clearTimeout(this.essaySaveTimers[soalId]);
                        delete this.essaySaveTimers[soalId];
                    }

                    if (Object.prototype.hasOwnProperty.call(this.pendingEssayValues, soalId)) {
                        const teks = this.pendingEssayValues[soalId];
                        delete this.pendingEssayValues[soalId];
                        await this.kirimAutoSave(soalId, teks);
                    }
                },

                async flushSemuaEssay() {
                    const pendingIds = Object.keys(this.pendingEssayValues);
                    for (const id of pendingIds) {
                        await this.simpanEssaySekarang(id);
                    }
                },

                async kirimAutoSave(soalId, nilaiJawaban) {
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
                                jawaban: nilaiJawaban,
                                jawaban_dipilih: nilaiJawaban
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

                // ==========================================
                // DETEKSI KECURANGAN & PELANGGARAN
                // ==========================================
                pasangDeteksiKecurangan() {
                    document.addEventListener('visibilitychange', () => {
                        if (document.hidden && !this.isLocked && this.hasStartedFullscreen) {
                            this.kirimPelanggaran('Pindah tab atau meminimalkan browser');
                        }
                    });

                    window.addEventListener('blur', () => {
                        if (!document.hidden && !this.isLocked && this.hasStartedFullscreen) {
                            this.kirimPelanggaran('Membuka aplikasi lain atau split-screen');
                        }
                    });

                    document.addEventListener('contextmenu', (e) => e.preventDefault());
                },

                async kirimPelanggaran(alasan) {
                    if (this.cooldownPelanggaran || this.isLocked) return;
                    this.cooldownPelanggaran = true;
                    setTimeout(() => {
                        this.cooldownPelanggaran = false;
                    }, 3000);

                    try {
                        const res = await fetch('{{ route('siswa.ujian.pelanggaran', $ujian->id) }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                alasan: alasan
                            })
                        });

                        const data = await res.json();
                        if (data.status === 'success') {
                            this.pelanggaranCount = data.pelanggaran_count;
                            this.maxToleransi = data.max_toleransi || 3;

                            if (data.is_locked) {
                                this.isLocked = true;
                                this.showWarningModal = false;
                            } else {
                                this.showWarningModal = true;
                            }
                        }
                    } catch (e) {
                        console.error('Gagal mencatat pelanggaran:', e);
                    }
                },

                tutupPeringatan() {
                    this.showWarningModal = false;
                },

                async konfirmasiSelesai() {
                    const totalDijawab = this.soals.filter(s => this.isSoalTerjawab(s.id)).length;
                    const total = this.soals.length;
                    const teksKonfirmasi = totalDijawab < total
                        ? `Perhatian: Kamu baru menjawab ${totalDijawab} dari ${total} soal. Yakin ingin mengumpulkan ujian sekarang?`
                        : `Semua soal (${total}) telah dijawab. Yakin ingin mengumpulkan ujian sekarang?`;

                    if (typeof Swal !== 'undefined') {
                        const result = await Swal.fire({
                            icon: totalDijawab < total ? 'warning' : 'question',
                            title: 'Kumpulkan Ujian?',
                            text: teksKonfirmasi,
                            showCancelButton: true,
                            confirmButtonText: 'Ya, Kumpulkan',
                            cancelButtonText: 'Batal',
                            confirmButtonColor: '#4f46e5',
                            cancelButtonColor: '#64748b',
                        });

                        if (result.isConfirmed) {
                            this.eksekusiSubmitOtomatis();
                        }
                    } else {
                        this.eksekusiSubmitOtomatis();
                    }
                },

                // ==========================================
                // BUKA KUNCI DENGAN UNLOCK CODE
                // ==========================================
                async submitUnlockCode() {
                    if (!this.inputUnlockCode.trim() || this.isUnlocking) return;

                    this.isUnlocking = true;
                    this.unlockError = '';

                    try {
                        const res = await fetch('{{ route('siswa.ujian.buka-kunci-kode', $ujian->id) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': config.csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                kode: this.inputUnlockCode.trim()
                            })
                        });

                        const data = await res.json();

                        if (res.ok && data.status === 'success') {
                            this.isLocked = false;
                            this.pelanggaranCount = 0;
                            this.inputUnlockCode = '';
                            this.unlockError = '';
                        } else {
                            this.unlockError = data.message || 'Kode buka blokir salah.';
                        }
                    } catch (e) {
                        this.unlockError = 'Gagal terhubung ke server. Periksa koneksi internet Anda.';
                    } finally {
                        this.isUnlocking = false;
                    }
                }
            };
        }
    </script>
@endsection
