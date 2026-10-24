@extends('layouts.app')

@section('title', 'Kelola Petualangan Kuis: ' . $quiz->judul)

@section('content')
    @php
        $maxSoal = \App\Models\Quiz::MAX_SOAL; // 10
        $currentSoalCount = $soalsInLevel->count();
        $isLevelFull = $currentSoalCount >= $maxSoal;
        $sisaSlot = max(0, $maxSoal - $currentSoalCount);
        $percent = min(100, ($currentSoalCount / $maxSoal) * 100);

        $arcConfigs = [
            'Mudah' => [
                'name' => 'Mudah',
                'label' => 'Konsep Dasar',
                'tab_active' => 'bg-emerald-600 text-white shadow-xs',
                'tab_inactive' => 'bg-white text-emerald-800 border border-emerald-200/80 hover:bg-emerald-50/70',
                'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'dot' => 'bg-emerald-500',
                'bar' => 'bg-emerald-500',
            ],
            'Sedang' => [
                'name' => 'Sedang',
                'label' => 'Analisis & Logika',
                'tab_active' => 'bg-blue-600 text-white shadow-xs',
                'tab_inactive' => 'bg-white text-blue-800 border border-blue-200/80 hover:bg-blue-50/70',
                'badge' => 'bg-blue-50 text-blue-700 border-blue-200',
                'dot' => 'bg-blue-500',
                'bar' => 'bg-blue-500',
            ],
            'Sulit' => [
                'name' => 'Sulit',
                'label' => 'Tantangan HOTS',
                'tab_active' => 'bg-rose-600 text-white shadow-xs',
                'tab_inactive' => 'bg-white text-rose-800 border border-rose-200/80 hover:bg-rose-50/70',
                'badge' => 'bg-rose-50 text-rose-700 border-rose-200',
                'dot' => 'bg-rose-500',
                'bar' => 'bg-rose-500',
            ],
        ];

        $currentArcConfig = $arcConfigs[$activeArc] ?? $arcConfigs['Mudah'];
    @endphp

    <div class="space-y-6" x-data="{
        showAddModal: false,
        showEditSoalModal: false,
        showEditQuizModal: false,
        isLevelFull: {{ $isLevelFull ? 'true' : 'false' }},
        allKeys: ['a', 'b', 'c', 'd', 'e', 'f'],

        editQuizData: {
            judul: '{{ addslashes($quiz->judul) }}',
            deskripsi: '{{ addslashes($quiz->deskripsi ?? '') }}',
            pengampu_kelas_id: '{{ $quiz->pengampu_kelas_id }}',
            kesulitan_aktif: {{ json_encode($quiz->kesulitan_aktif_array) }},
            status: '{{ $quiz->status ?? 'draft' }}',
            waktu_mulai: '{{ $quiz->waktu_mulai ? $quiz->waktu_mulai->format('Y-m-d\TH:i') : '' }}',
            waktu_selesai: '{{ $quiz->waktu_selesai ? $quiz->waktu_selesai->format('Y-m-d\TH:i') : '' }}',
            acak_jawaban: {{ $quiz->acak_jawaban ? 'true' : 'false' }}
        },

        addSoalData: {
            kunci_jawaban: 'a',
            options: [
                { key: 'a', label: 'A', text: '' },
                { key: 'b', label: 'B', text: '' },
                { key: 'c', label: 'C', text: '' },
                { key: 'd', label: 'D', text: '' }
            ]
        },

        editSoalData: {
            id: '',
            pertanyaan: '',
            tipe_soal: 'single_choice',
            bobot: 10,
            kunci_jawaban: 'a',
            options: []
        },

        todayStartDate: '{{ now()->format('Y-m-d\T00:00') }}',

        validateSchedule(start, end) {
            if (start) {
                const startDate = new Date(start);
                const now = new Date();
                const startOfToday = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 0, 0, 0);
                if (startDate < startOfToday) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Jadwal Tidak Valid',
                        text: 'Waktu mulai kuis tidak boleh sebelum hari ini!'
                    });
                    return false;
                }
            }
            if (start && end) {
                const startDate = new Date(start);
                const endDate = new Date(end);
                if (endDate.getTime() === startDate.getTime()) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Jadwal Tidak Valid',
                        text: 'Waktu selesai tidak boleh sama dengan waktu mulai! Waktu selesai harus lebih lambat dari waktu mulai.'
                    });
                    return false;
                }
                if (endDate < startDate) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Jadwal Tidak Valid',
                        text: 'Waktu selesai harus lebih lambat dari waktu mulai!'
                    });
                    return false;
                }
            }
            return true;
        },

        addOption(target) {
            let currentOptions = target === 'add' ? this.addSoalData.options : this.editSoalData.options;
            if (currentOptions.length >= 6) {
                Swal.fire({icon: 'info', title: 'Batas Maksimal', text: 'Maksimal 6 pilihan jawaban (A sampai F).'});
                return;
            }
            let nextIndex = currentOptions.length;
            let nextKey = this.allKeys[nextIndex];
            currentOptions.push({
                key: nextKey,
                label: nextKey.toUpperCase(),
                text: ''
            });
        },

        removeOption(target, index) {
            let obj = target === 'add' ? this.addSoalData : this.editSoalData;
            if (obj.options.length <= 2) {
                Swal.fire({icon: 'warning', title: 'Batas Minimal', text: 'Minimal harus ada 2 pilihan jawaban (A dan B).'});
                return;
            }
            obj.options.splice(index, 1);
            obj.options.forEach((opt, idx) => {
                opt.key = this.allKeys[idx];
                opt.label = this.allKeys[idx].toUpperCase();
            });
            let activeKeys = obj.options.map(o => o.key);
            if (!activeKeys.includes(obj.kunci_jawaban)) {
                obj.kunci_jawaban = activeKeys[0];
            }
        },

        openAddModal() {
            if (this.isLevelFull) {
                Swal.fire({icon: 'warning', title: 'Level Penuh', text: 'Level ini sudah mencapai batas maksimal 10 butir soal.'});
                return;
            }
            this.addSoalData = {
                kunci_jawaban: 'a',
                options: [
                    { key: 'a', label: 'A', text: '' },
                    { key: 'b', label: 'B', text: '' },
                    { key: 'c', label: 'C', text: '' },
                    { key: 'd', label: 'D', text: '' }
                ]
            };
            this.showAddModal = true;
        },

        openEditModal(soal) {
            let pil = soal.pilihan || {};
            let keys = Object.keys(pil);
            if (!keys || keys.length < 2) {
                keys = ['a', 'b', 'c', 'd'];
            }
            keys.sort();
            let options = keys.map((k, idx) => {
                let keyChar = this.allKeys[idx] || k;
                return {
                    key: keyChar,
                    label: keyChar.toUpperCase(),
                    text: pil[k] || ''
                };
            });

            let rawKunci = typeof soal.kunci_jawaban === 'string' ? soal.kunci_jawaban : (Array.isArray(soal.kunci_jawaban) ? soal.kunci_jawaban[0] : 'a');
            let validKunci = options.some(o => o.key === rawKunci.toLowerCase()) ? rawKunci.toLowerCase() : options[0].key;

            this.editSoalData = {
                id: soal.id,
                pertanyaan: soal.pertanyaan || '',
                tipe_soal: soal.tipe_soal || 'single_choice',
                bobot: soal.bobot || 10,
                kunci_jawaban: validKunci,
                options: options
            };
            this.showEditSoalModal = true;
        }
    }">



        {{-- =========================================================
            HEADER & CONTROLS
        ========================================================== --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-xs space-y-4">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="flex items-start gap-3.5">
                    <a href="{{ route('guru.kuis.index') }}"
                        class="p-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 hover:text-blue-600 hover:border-blue-200 hover:bg-blue-50/50 transition shrink-0 shadow-xs"
                        title="Kembali ke Daftar Kuis">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <div>
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                                {{ $quiz->judul }}
                            </h1>
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold border {{ $quiz->status === 'published' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200' }}">
                                <span
                                    class="w-1.5 h-1.5 rounded-full {{ $quiz->status === 'published' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                {{ $quiz->status === 'published' ? 'Diterbitkan' : 'Draf' }}
                            </span>

                            @php
                                $sched = $quiz->schedule_info;
                            @endphp
                            @if($quiz->waktu_mulai || $quiz->waktu_selesai)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold border {{ $sched['badge_class'] }}" title="{{ $sched['description'] }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $sched['label'] }}
                                </span>
                            @endif

                            @if($quiz->acak_jawaban)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200" title="Pilihan jawaban diacak untuk setiap siswa">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    Acak Jawaban
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center gap-2 text-xs font-semibold text-blue-600 mt-1 flex-wrap">
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                <span>{{ $quiz->pengampuKelas?->guruMapel?->mapel?->nama_mapel }}</span>
                                <span class="text-slate-300">•</span>
                                <span class="text-slate-600">Kelas {{ $quiz->pengampuKelas?->kelas?->tingkatan }} {{ $quiz->pengampuKelas?->kelas?->nama_kelas }}</span>
                            </div>

                            @if($quiz->waktu_mulai || $quiz->waktu_selesai)
                                <span class="text-slate-300 hidden sm:inline">•</span>
                                <div class="flex items-center gap-1 text-slate-500">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span>Jadwal: {{ $quiz->waktu_mulai ? $quiz->waktu_mulai->format('d M Y, H:i') : 'Langsung' }} s/d {{ $quiz->waktu_selesai ? $quiz->waktu_selesai->format('d M Y, H:i') : 'Tanpa Batas' }}</span>
                                </div>
                            @endif
                        </div>

                        @if ($quiz->deskripsi)
                            <p
                                class="text-xs text-slate-600 mt-2.5 italic bg-slate-50/80 p-3 rounded-xl border border-slate-200/70 max-w-2xl leading-relaxed">
                                "{{ $quiz->deskripsi }}"
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
                    {{-- Toggle Publish Form --}}
                    <form method="POST" action="{{ route('guru.kuis.toggle-status', $quiz) }}"
                        data-confirm="{{ $quiz->status === 'published' ? 'Apakah Anda yakin ingin mengubah status kuis ini menjadi Draf? Siswa tidak akan dapat mengakses kuis ini sementara waktu.' : 'Apakah Anda yakin ingin menerbitkan kuis ini? Siswa di kelas terkait akan dapat melihat dan mengerjakan kuis.' }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border text-xs font-bold transition shadow-xs cursor-pointer {{ $quiz->status === 'published' ? 'border-amber-200 bg-amber-50 text-amber-800 hover:bg-amber-100' : 'border-emerald-200 bg-emerald-50 text-emerald-800 hover:bg-emerald-100' }}">
                            @if ($quiz->status === 'published')
                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Jadikan Draf</span>
                            @else
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Terbitkan Kuis</span>
                            @endif
                        </button>
                    </form>

                    {{-- Edit Pengaturan Kuis --}}
                    <button @click="showEditQuizModal = true" type="button"
                        class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 text-xs font-bold hover:bg-slate-50 hover:text-blue-600 hover:border-blue-200 transition shadow-xs cursor-pointer">
                        <svg class="w-4 h-4 text-slate-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <span>Edit Kuis</span>
                    </button>

                    {{-- Delete Kuis --}}
                    <form method="POST" action="{{ route('guru.kuis.destroy', $quiz) }}"
                        data-confirm="Apakah Anda yakin ingin menghapus kuis petualangan '{{ addslashes($quiz->judul) }}'? Seluruh level tantangan dan butir soal di dalamnya akan dihapus secara permanen.">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-rose-200 bg-rose-50 text-rose-700 text-xs font-bold hover:bg-rose-100 hover:text-rose-800 transition shadow-xs cursor-pointer"
                            title="Hapus Kuis">
                            <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            <span>Hapus</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- =========================================================
            STATUS & READINESS ALERT
        ========================================================== --}}
        @php
            $emptyArcs = [];
            foreach ($activeDifficulties as $arcName) {
                $hasSoal = $quiz->soals->where('kesulitan', $arcName)->count() > 0;
                if (!$hasSoal) {
                    $emptyArcs[] = $arcName;
                }
            }
        @endphp

        @if ($quiz->status === 'draft')
            @if (count($emptyArcs) > 0)
                <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-sm flex items-start gap-3 shadow-xs">
                    <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div>
                        <span class="font-bold block">Kuis Berstatus Draf (Belum Siap Diterbitkan)</span>
                        <p class="text-xs text-amber-800 mt-0.5 leading-relaxed">
                            Agar kuis dapat diterbitkan ke siswa, setiap Arc kesulitan yang aktif harus memiliki minimal 1 butir soal. Arc yang saat ini belum memiliki soal: <strong class="text-amber-950 font-bold">[{{ implode(', ', $emptyArcs) }}]</strong>.
                        </p>
                    </div>
                </div>
            @else
                <div class="p-4 rounded-2xl bg-sky-50 border border-sky-200 text-sky-900 text-sm flex items-start gap-3 shadow-xs">
                    <svg class="w-5 h-5 text-sky-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <span class="font-bold block">Kuis Siap Diterbitkan</span>
                        <p class="text-xs text-sky-800 mt-0.5 leading-relaxed">
                            Semua Arc tingkat kesulitan yang aktif telah memiliki butir soal. Klik tombol <strong class="text-sky-950 font-bold">"Terbitkan Kuis"</strong> di atas jika Anda ingin membuka akses petualangan kuis ini bagi siswa.
                        </p>
                    </div>
                </div>
            @endif
        @else
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-sm flex items-start gap-3 shadow-xs">
                <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <span class="font-bold block">Kuis Sedang Aktif & Diterbitkan</span>
                    <p class="text-xs text-emerald-800 mt-0.5 leading-relaxed">
                        Kuis ini sedang aktif dan dapat diakses serta dikerjakan oleh siswa di kelas. Anda tetap dapat menambahkan butir soal baru atau mengubahnya menjadi Draf kapan saja jika diperlukan.
                    </p>
                </div>
            </div>
        @endif

        {{-- =========================================================
            ARC KESULITAN TABS (1 s/d 3 ARC)
        ========================================================== --}}
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-xs font-extrabold uppercase tracking-wider text-slate-400">
                    Pilih Arc Kesulitan ({{ count($activeDifficulties) }} Arc Aktif):
                </h2>
                <span class="text-xs text-slate-400">
                    Setiap arc memiliki 5 level bertingkat
                </span>
            </div>

            <div class="flex items-center gap-3 overflow-x-auto pb-1">
                @foreach ($activeDifficulties as $arc)
                    @php
                        $cfg = $arcConfigs[$arc] ?? $arcConfigs['Mudah'];
                        $isActive = $arc === $activeArc;
                        $totalSoalInArc = 0;
                        foreach (\App\Models\Quiz::LEVEL_LIST as $lvl) {
                            $totalSoalInArc += $levelCounts[$arc][$lvl] ?? 0;
                        }
                    @endphp
                    <a href="{{ route('guru.kuis.show', ['quiz' => $quiz, 'arc' => $arc, 'level' => 'Level 1']) }}"
                        class="flex items-center gap-3 px-5 py-3 rounded-2xl font-bold text-sm transition-all duration-200 shrink-0 shadow-xs {{ $isActive ? $cfg['tab_active'] : $cfg['tab_inactive'] }}">
                        <div>
                            <div class="leading-tight">{{ $cfg['name'] ?? $arc }}</div>
                            <div class="text-[10px] opacity-80 font-normal mt-0.5">{{ $totalSoalInArc }} Butir Soal Terpasang</div>
                        </div>
                        @if ($isActive)
                            <span class="w-2 h-2 rounded-full bg-white ml-1"></span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>

        {{-- =========================================================
            PETA 5 LEVEL TANTANGAN UNTUK ARC AKTIF
        ========================================================== --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        Peta Level Tantangan: {{ $currentArcConfig['name'] ?? $activeArc }}
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Pilih level di bawah untuk melihat dan menambah butir soal (maksimal 10 butir soal per level).
                    </p>
                </div>
                <div class="text-xs font-semibold px-3 py-1.5 rounded-xl border {{ $currentArcConfig['badge'] ?? 'bg-slate-100 text-slate-800' }}">
                    Arc Terpilih: {{ $activeArc }}
                </div>
            </div>

            {{-- 5 Level Roadmap Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-5 gap-3">
                @for ($i = 1; $i <= 5; $i++)
                    @php
                        $lvlKey = "Level {$i}";
                        $isLvlActive = $activeLevel === $lvlKey;
                        $soalCountLvl = $levelCounts[$activeArc][$lvlKey] ?? 0;
                        $isLvlFull = $soalCountLvl >= 10;
                        $percentLvl = min(100, ($soalCountLvl / 10) * 100);
                    @endphp
                    <a href="{{ route('guru.kuis.show', ['quiz' => $quiz, 'arc' => $activeArc, 'level' => $lvlKey]) }}"
                        class="p-4 rounded-2xl border text-center transition-all duration-200 flex flex-col justify-between group relative cursor-pointer {{ $isLvlActive ? 'border-blue-600 bg-blue-50/60 shadow-xs ring-2 ring-blue-100' : 'border-slate-200/80 bg-slate-50/40 hover:bg-slate-100/70 hover:border-slate-300' }}">
                        <div>
                            {{-- Top Level Stage & Icon --}}
                            <div class="flex items-center justify-between mb-2">
                                <span
                                    class="text-[10px] font-extrabold uppercase tracking-wider {{ $isLvlActive ? 'text-blue-700' : 'text-slate-400' }}">
                                    Stage {{ $i }}
                                </span>
                                @if ($isLvlFull)
                                    <span
                                        class="w-4 h-4 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[10px]"
                                        title="10/10 Soal Penuh">✓</span>
                                @endif
                            </div>

                            {{-- Level Name --}}
                            <h4 class="text-base font-extrabold text-slate-900 group-hover:text-blue-600 transition">
                                {{ $lvlKey }}
                            </h4>

                            {{-- Soal Count --}}
                            <div
                                class="mt-2 text-xs font-bold {{ $isLvlFull ? 'text-emerald-600' : ($soalCountLvl > 0 ? 'text-blue-700' : 'text-slate-400') }}">
                                {{ $soalCountLvl }} / 10 Soal
                            </div>
                        </div>

                        {{-- Progress Bar Mini --}}
                        <div class="mt-3">
                            <div class="w-full bg-slate-200/80 rounded-full h-1.5 overflow-hidden">
                                <div class="{{ $isLvlFull ? 'bg-emerald-500' : 'bg-blue-600' }} h-1.5 rounded-full transition-all duration-300"
                                    style="width: {{ $percentLvl }}%"></div>
                            </div>
                            <span
                                class="text-[10px] font-semibold mt-1.5 block {{ $isLvlActive ? 'text-blue-700 font-bold' : 'text-slate-400' }}">
                                {{ $isLvlActive ? '● Sedang Dibuka' : 'Klik Buka' }}
                            </span>
                        </div>
                    </a>
                @endfor
            </div>
        </div>

        {{-- =========================================================
            PANEL PENGELOLAAN SOAL LEVEL TERPILIH
        ========================================================== --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-xs space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                <div>
                    <div class="flex items-center gap-2.5">
                        <span
                            class="inline-flex items-center justify-center px-2.5 py-1 rounded-lg bg-blue-50 border border-blue-100 text-blue-700 font-extrabold text-xs">
                            {{ $activeLevel }}
                        </span>
                        <h3 class="text-lg font-bold text-slate-900">
                            Butir Soal [{{ $activeArc }} - {{ $activeLevel }}]
                        </h3>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        Kapasitas: <strong class="text-slate-800">{{ $currentSoalCount }} / {{ $maxSoal }} Butir Soal</strong>
                        @if ($isLevelFull)
                            • <span class="text-emerald-600 font-bold">✓ Kuota level ini telah terpenuhi (10 soal)</span>
                        @else
                            • <span class="text-blue-600 font-bold">Tersedia {{ $sisaSlot }} slot soal lagi</span>
                        @endif
                    </p>
                </div>

                {{-- Tombol Tambah Soal --}}
                @if ($isLevelFull)
                    <button type="button" disabled
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 text-slate-400 text-xs font-semibold border border-slate-200 cursor-not-allowed">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        Maksimal 10 Soal Tercapai
                    </button>
                @else
                    <button @click="openAddModal()" type="button"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 text-white font-semibold text-xs sm:text-sm hover:bg-blue-700 active:scale-[0.98] transition shadow-xs cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>+ Tambah Soal ke {{ $activeLevel }}</span>
                    </button>
                @endif
            </div>

            {{-- Progress Bar Kapasitas Soal di Level ini --}}
            <div class="space-y-1.5">
                <div class="flex items-center justify-between text-xs text-slate-600 font-medium">
                    <span>Progress Pengisian Soal</span>
                    <span class="font-extrabold {{ $isLevelFull ? 'text-emerald-600' : 'text-blue-600' }}">
                        {{ $currentSoalCount }} / {{ $maxSoal }} Soal ({{ $percent }}%)
                    </span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                    <div class="{{ $isLevelFull ? 'bg-emerald-500' : 'bg-blue-600' }} h-2.5 rounded-full transition-all duration-300"
                        style="width: {{ $percent }}%"></div>
                </div>
            </div>

            {{-- Daftar Butir Soal --}}
            @if ($soalsInLevel->count() > 0)
                <div class="space-y-4 pt-2">
                    @foreach ($soalsInLevel as $index => $soal)
                        <div
                            class="p-5 rounded-2xl bg-slate-50/60 border border-slate-200/80 hover:border-blue-200 hover:bg-white transition space-y-3.5 shadow-xs">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-start gap-3">
                                    <span
                                        class="inline-flex items-center justify-center w-7 h-7 rounded-xl bg-blue-600 text-white font-extrabold text-xs shrink-0 shadow-xs">
                                        {{ $index + 1 }}
                                    </span>
                                    <div class="text-sm font-semibold text-slate-800 leading-relaxed">
                                        {!! nl2br(e($soal->pertanyaan)) !!}
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <span
                                        class="text-xs font-bold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-200">
                                        {{ $soal->bobot }} Poin
                                    </span>

                                    {{-- Edit Soal --}}
                                    <button @click="openEditModal({{ json_encode($soal) }})" type="button"
                                        class="p-2 text-slate-400 hover:text-blue-600 rounded-xl hover:bg-blue-50/60 border border-slate-200/80 hover:border-blue-200 transition cursor-pointer"
                                        title="Edit Butir Soal">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>

                                    {{-- Delete Soal --}}
                                    <form method="POST"
                                        action="{{ route('guru.kuis.soal.destroy', ['quiz' => $quiz, 'soal' => $soal]) }}"
                                        data-confirm="Apakah Anda yakin ingin menghapus butir soal nomor {{ $index + 1 }} ini? Tindakan ini tidak dapat dibatalkan dan slot soal akan kembali terbuka.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="p-2 text-slate-400 hover:text-rose-600 rounded-xl hover:bg-rose-50/60 border border-slate-200/80 hover:border-rose-200 transition cursor-pointer"
                                            title="Hapus Butir Soal">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            {{-- Gambar Pendukung jika ada --}}
                            @if ($soal->gambar)
                                <div class="mt-2">
                                    <img src="{{ asset('storage/' . $soal->gambar) }}" alt="Gambar Soal"
                                        class="max-h-48 rounded-xl border border-slate-200 object-cover shadow-xs">
                                </div>
                            @endif

                            {{-- Opsi A, B, C, D --}}
                            @if (!empty($soal->pilihan) && is_array($soal->pilihan))
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-2">
                                    @foreach ($soal->pilihan as $key => $opt)
                                        @php
                                            $isKey = is_array($soal->kunci_jawaban)
                                                ? in_array($key, $soal->kunci_jawaban)
                                                : strtolower($soal->kunci_jawaban) == strtolower($key);
                                        @endphp
                                        <div
                                            class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl text-xs transition {{ $isKey ? 'bg-emerald-50 border border-emerald-300 font-bold text-emerald-900 shadow-xs' : 'bg-white border border-slate-200/80 text-slate-700' }}">
                                            <span
                                                class="w-5 h-5 rounded-lg {{ $isKey ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600' }} font-bold flex items-center justify-center shrink-0 text-[11px]">
                                                {{ strtoupper($key) }}
                                            </span>
                                            <span class="flex-1">{{ $opt }}</span>
                                            @if ($isKey)
                                                <span class="text-emerald-600 font-extrabold text-[11px] shrink-0">✓ KUNCI</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-12 text-center text-slate-400">
                    <div
                        class="w-14 h-14 rounded-2xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center mx-auto mb-3 shadow-xs">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-slate-800">Belum ada butir soal di {{ $activeArc }} - {{ $activeLevel }}</p>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                        Setiap level dapat diisi hingga 10 butir soal pilihan ganda. Klik tombol di bawah untuk menambahkan
                        soal pertama.
                    </p>
                    <button @click="openAddModal()" type="button"
                        class="mt-4 px-5 py-2.5 rounded-xl bg-blue-600 text-white font-semibold text-xs hover:bg-blue-700 active:scale-[0.98] transition shadow-xs cursor-pointer">
                        + Tambah Soal Pertama ke {{ $activeLevel }}
                    </button>
                </div>
            @endif
        </div>

        {{-- =========================================================
            MODAL TAMBAH BUTIR SOAL KE LEVEL AKTIF
        ========================================================== --}}
        <div x-show="showAddModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4 overflow-y-auto">
            <div @click.away="showAddModal = false"
                class="bg-white w-full max-w-2xl rounded-2xl shadow-xl border border-slate-200/80 overflow-hidden max-h-[90vh] flex flex-col animate-in fade-in zoom-in-95 duration-200 my-8">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">
                                Tambah Soal ke [{{ $activeArc }} - {{ $activeLevel }}]
                            </h3>
                            <p class="text-xs text-slate-500">Soal {{ $currentSoalCount + 1 }} dari maks
                                {{ $maxSoal }} butir soal</p>
                        </div>
                    </div>
                    <button @click="showAddModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('guru.kuis.soal.store', $quiz) }}" enctype="multipart/form-data"
                    class="p-6 overflow-y-auto space-y-4">
                    @csrf
                    <input type="hidden" name="tipe_soal" value="single_choice">
                    <input type="hidden" name="kesulitan" value="{{ $activeArc }}">
                    <input type="hidden" name="level" value="{{ $activeLevel }}">

                    {{-- Pertanyaan --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                            Teks Pertanyaan / Soal <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="pertanyaan" rows="3" required placeholder="Tuliskan teks pertanyaan soal untuk level ini..."
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition"></textarea>
                    </div>

                    {{-- Bobot & Gambar --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                                Bobot Poin <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" name="bobot" value="10" min="1" max="100" required
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 text-sm text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                                Gambar Pendukung (Opsional)
                            </label>
                            <input type="file" name="gambar" accept="image/*"
                                class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                        </div>
                    </div>

                    {{-- Opsi Pilihan Jawaban (Min 2 s/d 6 Opsi) & Kunci Jawaban --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase">
                                Pilihan Opsi Jawaban & Kunci Jawaban <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-[11px] text-slate-500">
                                Jumlah opsi: <strong class="text-blue-600 font-bold" x-text="addSoalData.options.length"></strong> (Min 2, Maks 6)
                            </span>
                        </div>
                        <div class="space-y-2.5">
                            <template x-for="(opt, index) in addSoalData.options" :key="opt.key">
                                <div class="flex items-center gap-3 p-2.5 rounded-xl border border-slate-200 bg-slate-50/50 hover:border-blue-200 transition">
                                    <label class="flex items-center gap-2 cursor-pointer shrink-0"
                                        title="Klik untuk jadikan kunci jawaban benar">
                                        <input type="radio" name="kunci_jawaban" :value="opt.key"
                                            x-model="addSoalData.kunci_jawaban" required
                                            class="w-4 h-4 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                        <span class="w-6 h-6 rounded-lg bg-slate-200 font-bold text-xs flex items-center justify-center text-slate-700"
                                            x-text="opt.label">
                                        </span>
                                    </label>
                                    <input type="text" :name="'pilihan[' + opt.key + ']'" x-model="opt.text" required
                                        :placeholder="'Teks untuk Pilihan ' + opt.label + '...'"
                                        class="flex-1 px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-100 outline-none transition">
                                    <button type="button" @click="removeOption('add', index)" x-show="addSoalData.options.length > 2"
                                        class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition"
                                        title="Hapus Opsi ini">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>

                        <div class="mt-3 flex items-center justify-between">
                            <button type="button" @click="addOption('add')" x-show="addSoalData.options.length < 6"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-dashed border-blue-300 bg-blue-50/70 text-blue-700 hover:bg-blue-100 text-xs font-bold transition shadow-xs cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                </svg>
                                <span>+ Tambah Opsi Jawaban (Maks 6)</span>
                            </button>
                            <span class="text-[11px] text-slate-400 ml-auto">
                                * Pilih radio button lingkaran di sebelah huruf untuk menetapkan kunci jawaban yang benar.
                            </span>
                        </div>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                        <button @click="showAddModal = false" type="button"
                            class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 active:scale-[0.98] transition shadow-xs cursor-pointer">
                            Simpan Butir Soal
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- =========================================================
            MODAL EDIT BUTIR SOAL
        ========================================================== --}}
        <div x-show="showEditSoalModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4 overflow-y-auto">
            <div @click.away="showEditSoalModal = false"
                class="bg-white w-full max-w-2xl rounded-2xl shadow-xl border border-slate-200/80 overflow-hidden max-h-[90vh] flex flex-col animate-in fade-in zoom-in-95 duration-200 my-8">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-slate-900">Edit Butir Soal</h3>
                    </div>
                    <button @click="showEditSoalModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" :action="'/guru/kuis/{{ $quiz->id }}/soal/' + editSoalData.id"
                    enctype="multipart/form-data" class="p-6 overflow-y-auto space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="tipe_soal" value="single_choice">

                    {{-- Pertanyaan --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                            Teks Pertanyaan <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="pertanyaan" x-model="editSoalData.pertanyaan" rows="3" required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition"></textarea>
                    </div>

                    {{-- Bobot & Gambar --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                                Bobot Poin <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" name="bobot" x-model="editSoalData.bobot" min="1"
                                max="100" required
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 text-sm text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                                Ganti Gambar (Opsional)
                            </label>
                            <input type="file" name="gambar" accept="image/*"
                                class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                        </div>
                    </div>

                    {{-- Opsi Pilihan Jawaban (Min 2 s/d 6 Opsi) & Kunci Jawaban --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase">
                                Pilihan Jawaban & Kunci Jawaban <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-[11px] text-slate-500">
                                Jumlah opsi: <strong class="text-blue-600 font-bold" x-text="editSoalData.options.length"></strong> (Min 2, Maks 6)
                            </span>
                        </div>
                        <div class="space-y-2.5">
                            <template x-for="(opt, index) in editSoalData.options" :key="opt.key">
                                <div class="flex items-center gap-3 p-2.5 rounded-xl border border-slate-200 bg-slate-50/50 hover:border-blue-200 transition">
                                    <label class="flex items-center gap-2 cursor-pointer shrink-0"
                                        title="Klik untuk jadikan kunci jawaban benar">
                                        <input type="radio" name="kunci_jawaban" :value="opt.key"
                                            x-model="editSoalData.kunci_jawaban" required
                                            class="w-4 h-4 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                        <span class="w-6 h-6 rounded-lg bg-slate-200 font-bold text-xs flex items-center justify-center text-slate-700"
                                            x-text="opt.label">
                                        </span>
                                    </label>
                                    <input type="text" :name="'pilihan[' + opt.key + ']'" x-model="opt.text" required
                                        :placeholder="'Teks untuk Pilihan ' + opt.label + '...'"
                                        class="flex-1 px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-100 outline-none transition">
                                    <button type="button" @click="removeOption('edit', index)" x-show="editSoalData.options.length > 2"
                                        class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition cursor-pointer"
                                        title="Hapus Opsi ini">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>

                        <div class="mt-3 flex items-center justify-between">
                            <button type="button" @click="addOption('edit')" x-show="editSoalData.options.length < 6"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-dashed border-blue-300 bg-blue-50/70 text-blue-700 hover:bg-blue-100 text-xs font-bold transition shadow-xs cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                </svg>
                                <span>+ Tambah Opsi Jawaban (Maks 6)</span>
                            </button>
                            <span class="text-[11px] text-slate-400 ml-auto">
                                * Pilih radio button lingkaran di sebelah huruf untuk menetapkan kunci jawaban yang benar.
                            </span>
                        </div>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                        <button @click="showEditSoalModal = false" type="button"
                            class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 active:scale-[0.98] transition shadow-xs cursor-pointer">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- =========================================================
            MODAL EDIT PENGATURAN KUIS
        ========================================================== --}}
        <div x-show="showEditQuizModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4 sm:p-6 overflow-hidden">
            <div @click.away="showEditQuizModal = false"
                class="bg-white w-full max-w-xl rounded-2xl shadow-2xl border border-slate-200/80 overflow-hidden max-h-[90vh] flex flex-col animate-in fade-in zoom-in-95 duration-200 my-auto">
                <div class="px-6 py-4 sm:py-5 border-b border-slate-100 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Edit Pengaturan Kuis</h3>
                            <p class="text-xs text-slate-500">Sesuaikan judul, deskripsi, jadwal, dan arc aktif.</p>
                        </div>
                    </div>
                    <button @click="showEditQuizModal = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('guru.kuis.update', $quiz) }}"
                    @submit="
                        if (![...$el.querySelectorAll('input[name=\'kesulitan_aktif[]\']:checked')].length) { 
                            $event.preventDefault(); 
                            Swal.fire({icon: 'warning', title: 'Peringatan', text: 'Pilih minimal satu tingkat kesulitan (Arc) yang aktif!'}); 
                            return;
                        }
                        if (!validateSchedule(editQuizData.waktu_mulai, editQuizData.waktu_selesai)) {
                            $event.preventDefault();
                        }
                    "
                    class="p-6 space-y-4 overflow-y-auto flex-1">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="pengampu_kelas_id" value="{{ $quiz->pengampu_kelas_id }}">

                    {{-- Judul Kuis --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                            Judul Kuis Petualangan <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="judul" x-model="editQuizData.judul" required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                    </div>

                    {{-- Deskripsi --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                            Deskripsi Petualangan
                        </label>
                        <textarea name="deskripsi" x-model="editQuizData.deskripsi" rows="2"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition"></textarea>
                    </div>

                    {{-- Arc Kesulitan Aktif --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                            Arc Kesulitan yang Aktif (1 s/d 3) <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                            <label
                                class="flex items-start gap-3 p-3 rounded-xl border border-emerald-200 bg-emerald-50/50 hover:bg-emerald-50 cursor-pointer transition">
                                <input type="checkbox" name="kesulitan_aktif[]" value="Mudah"
                                    :checked="editQuizData.kesulitan_aktif && editQuizData.kesulitan_aktif.includes('Mudah')"
                                    class="w-4 h-4 mt-0.5 text-emerald-600 rounded focus:ring-emerald-500">
                                <div>
                                    <span class="text-xs font-bold text-emerald-900 block">Mudah</span>
                                    <span class="text-[11px] text-emerald-700">5 Level</span>
                                </div>
                            </label>

                            <label
                                class="flex items-start gap-3 p-3 rounded-xl border border-blue-200 bg-blue-50/50 hover:bg-blue-50 cursor-pointer transition">
                                <input type="checkbox" name="kesulitan_aktif[]" value="Sedang"
                                    :checked="editQuizData.kesulitan_aktif && editQuizData.kesulitan_aktif.includes('Sedang')"
                                    class="w-4 h-4 mt-0.5 text-blue-600 rounded focus:ring-blue-500">
                                <div>
                                    <span class="text-xs font-bold text-blue-900 block">Sedang</span>
                                    <span class="text-[11px] text-blue-700">5 Level</span>
                                </div>
                            </label>

                            <label
                                class="flex items-start gap-3 p-3 rounded-xl border border-rose-200 bg-rose-50/50 hover:bg-rose-50 cursor-pointer transition">
                                <input type="checkbox" name="kesulitan_aktif[]" value="Sulit"
                                    :checked="editQuizData.kesulitan_aktif && editQuizData.kesulitan_aktif.includes('Sulit')"
                                    class="w-4 h-4 mt-0.5 text-rose-600 rounded focus:ring-rose-500">
                                <div>
                                    <span class="text-xs font-bold text-rose-900 block">Sulit</span>
                                    <span class="text-[11px] text-rose-700">5 Level</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Jadwal Mulai & Selesai (Opsional) --}}
                    <div class="border-t border-slate-100 pt-3">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Jadwal Pengerjaan Kuis (Opsional)
                        </label>
                        <p class="text-xs text-slate-500 mb-2.5">
                            Kosongkan jika kuis dapat dikerjakan kapan saja tanpa batasan tanggal/waktu.
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Waktu Mulai</label>
                                <input
                                    type="datetime-local"
                                    name="waktu_mulai"
                                    x-model="editQuizData.waktu_mulai"
                                    :min="todayStartDate"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 text-sm text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition"
                                >
                                <p class="text-[10px] text-slate-400 mt-1">Tidak boleh sebelum hari ini</p>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Waktu Berakhir</label>
                                <input
                                    type="datetime-local"
                                    name="waktu_selesai"
                                    x-model="editQuizData.waktu_selesai"
                                    :min="todayStartDate"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 text-sm text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition"
                                >
                                <p class="text-[10px] text-slate-400 mt-1">Harus lebih lambat dari waktu mulai</p>
                            </div>
                        </div>
                    </div>

                    {{-- Pengacakan Jawaban --}}
                    <div class="border-t border-slate-100 pt-3">
                        <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-200 bg-slate-50/60 hover:bg-slate-50 cursor-pointer transition">
                            <input
                                type="checkbox"
                                name="acak_jawaban"
                                value="1"
                                x-model="editQuizData.acak_jawaban"
                                class="w-4 h-4 mt-0.5 text-blue-600 rounded focus:ring-blue-500"
                            >
                            <div>
                                <span class="text-xs font-bold text-slate-900 block">Acak Pilihan Jawaban Soal</span>
                                <span class="text-[11px] text-slate-500">Urutan pilihan jawaban (A, B, C, ...) akan diacak secara dinamis untuk tiap siswa saat mengerjakan.</span>
                            </div>
                        </label>
                    </div>

                    {{-- Status --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                            Status Kuis
                        </label>
                        <div class="flex items-center gap-4">
                            <label class="flex items-center gap-2 cursor-pointer text-sm text-slate-700">
                                <input type="radio" name="status" value="draft" x-model="editQuizData.status"
                                    class="text-blue-600 focus:ring-blue-500">
                                <span>Draf</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer text-sm text-slate-700">
                                <input type="radio" name="status" value="published" x-model="editQuizData.status"
                                    class="text-blue-600 focus:ring-blue-500">
                                <span>Diterbitkan</span>
                            </label>
                        </div>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100 shrink-0">
                        <button @click="showEditQuizModal = false" type="button"
                            class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 active:scale-[0.98] transition shadow-xs cursor-pointer">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection
