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
            'name' => 'Arc Mudah',
            'label' => 'Konsep Dasar',
            'tab_active' => 'bg-emerald-600 text-white shadow-md',
            'tab_inactive' => 'bg-white text-emerald-800 border border-emerald-200 hover:bg-emerald-50',
            'badge' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
            'dot' => 'bg-emerald-500',
            'bar' => 'bg-emerald-500',
            'emoji' => '😊',
        ],
        'Sedang' => [
            'name' => 'Arc Sedang',
            'label' => 'Analisis & Logika',
            'tab_active' => 'bg-indigo-600 text-white shadow-md',
            'tab_inactive' => 'bg-white text-indigo-800 border border-indigo-200 hover:bg-indigo-50',
            'badge' => 'bg-indigo-50 text-indigo-800 border-indigo-200',
            'dot' => 'bg-indigo-500',
            'bar' => 'bg-indigo-500',
            'emoji' => '🧠',
        ],
        'Sulit' => [
            'name' => 'Arc Sulit',
            'label' => 'Tantangan HOTS',
            'tab_active' => 'bg-rose-600 text-white shadow-md',
            'tab_inactive' => 'bg-white text-rose-800 border border-rose-200 hover:bg-rose-50',
            'badge' => 'bg-rose-50 text-rose-800 border-rose-200',
            'dot' => 'bg-rose-500',
            'bar' => 'bg-rose-500',
            'emoji' => '🔥',
        ],
    ];

    $currentArcConfig = $arcConfigs[$activeArc] ?? $arcConfigs['Mudah'];
@endphp

<div class="space-y-6" x-data="{
    showAddModal: false,
    showEditSoalModal: false,
    showEditQuizModal: false,
    isLevelFull: {{ $isLevelFull ? 'true' : 'false' }},
    editQuizData: {
        judul: '{{ addslashes($quiz->judul) }}',
        deskripsi: '{{ addslashes($quiz->deskripsi ?? '') }}',
        pengampu_kelas_id: '{{ $quiz->pengampu_kelas_id }}',
        kesulitan_aktif: {{ json_encode($quiz->kesulitan_aktif_array) }},
        status: '{{ $quiz->status ?? 'draft' }}'
    },
    editSoalData: {
        id: '',
        pertanyaan: '',
        tipe_soal: 'single_choice',
        bobot: 10,
        kunci_jawaban: 'a',
        pilihan: {
            a: '',
            b: '',
            c: '',
            d: ''
        }
    },

    openAddModal() {
        if (this.isLevelFull) {
            alert('Level ini sudah mencapai batas maksimal 10 butir soal.');
            return;
        }
        this.showAddModal = true;
    },

    openEditModal(soal) {
        let pil = soal.pilihan || {};
        this.editSoalData = {
            id: soal.id,
            pertanyaan: soal.pertanyaan || '',
            tipe_soal: soal.tipe_soal || 'single_choice',
            bobot: soal.bobot || 10,
            kunci_jawaban: typeof soal.kunci_jawaban === 'string' ? soal.kunci_jawaban : (Array.isArray(soal.kunci_jawaban) ? soal.kunci_jawaban[0] : 'a'),
            pilihan: {
                a: pil.a || '',
                b: pil.b || '',
                c: pil.c || '',
                d: pil.d || ''
            }
        };
        this.showEditSoalModal = true;
    }
}">

    {{-- Alert Success / Error --}}
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-3 animate-in fade-in">
            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center gap-3 animate-in fade-in">
            <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm space-y-1">
            @foreach ($errors->all() as $error)
                <p>• {{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- =========================================================
        HEADER & CONTROLS
    ========================================================== --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm space-y-4">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex items-start gap-3.5">
                <a
                    href="{{ route('guru.kuis.index') }}"
                    class="p-2.5 rounded-xl border border-gray-200 bg-gray-50 text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition shrink-0"
                    title="Kembali ke Daftar Kuis"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                            {{ $quiz->judul }}
                        </h1>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold border {{ $quiz->status === 'published' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $quiz->status === 'published' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                            {{ $quiz->status === 'published' ? 'Diterbitkan' : 'Draf' }}
                        </span>
                    </div>

                    <p class="text-sm text-gray-500 mt-1">
                        {{ $quiz->pengampuKelas?->guruMapel?->mapel?->nama_mapel }} • Kelas {{ $quiz->pengampuKelas?->kelas?->tingkatan }} {{ $quiz->pengampuKelas?->kelas?->nama_kelas }}
                    </p>

                    @if($quiz->deskripsi)
                        <p class="text-xs text-gray-600 mt-2 italic bg-gray-50 p-2.5 rounded-xl border border-gray-100 max-w-2xl">
                            "{{ $quiz->deskripsi }}"
                        </p>
                    @endif
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-2 shrink-0 flex-wrap">
                {{-- Toggle Publish Form --}}
                <form method="POST" action="{{ route('guru.kuis.toggle-status', $quiz) }}">
                    @csrf
                    @method('PATCH')
                    <button
                        type="submit"
                        class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border text-xs font-bold transition cursor-pointer {{ $quiz->status === 'published' ? 'border-amber-200 bg-amber-50 text-amber-800 hover:bg-amber-100' : 'border-emerald-200 bg-emerald-50 text-emerald-800 hover:bg-emerald-100' }}"
                    >
                        @if($quiz->status === 'published')
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Jadikan Draf
                        @else
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Terbitkan Kuis
                        @endif
                    </button>
                </form>

                {{-- Edit Pengaturan Kuis --}}
                <button
                    @click="showEditQuizModal = true"
                    type="button"
                    class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-gray-700 text-xs font-bold hover:bg-gray-50 transition cursor-pointer"
                >
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit Kuis
                </button>
            </div>
        </div>
    </div>

    {{-- =========================================================
        ARC KESULITAN TABS (1 s/d 3 ARC)
    ========================================================== --}}
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-xs font-extrabold uppercase tracking-wider text-gray-500">
                Pilih Arc Kesulitan ({{ count($activeDifficulties) }} Arc Aktif):
            </h2>
            <span class="text-xs text-gray-400">
                Setiap arc memiliki 5 level bertingkat
            </span>
        </div>

        <div class="flex items-center gap-3 overflow-x-auto pb-1">
            @foreach($activeDifficulties as $arc)
                @php
                    $cfg = $arcConfigs[$arc] ?? $arcConfigs['Mudah'];
                    $isActive = ($arc === $activeArc);
                    $totalSoalInArc = 0;
                    foreach(\App\Models\Quiz::LEVEL_LIST as $lvl) {
                        $totalSoalInArc += $levelCounts[$arc][$lvl] ?? 0;
                    }
                @endphp
                <a
                    href="{{ route('guru.kuis.show', ['quiz' => $quiz, 'arc' => $arc, 'level' => 'Level 1']) }}"
                    class="flex items-center gap-3 px-5 py-3 rounded-2xl font-bold text-sm transition-all duration-200 shrink-0 {{ $isActive ? $cfg['tab_active'] : $cfg['tab_inactive'] }}"
                >
                    <span class="text-lg">{{ $cfg['emoji'] }}</span>
                    <div>
                        <div class="leading-tight">{{ $cfg['name'] }}</div>
                        <div class="text-[10px] opacity-80 font-normal">{{ $totalSoalInArc }} Butir Soal Terpasang</div>
                    </div>
                    @if($isActive)
                        <span class="w-2 h-2 rounded-full bg-white ml-1"></span>
                    @endif
                </a>
            @endforeach
        </div>
    </div>

    {{-- =========================================================
        PETA 5 LEVEL TANTANGAN UNTUK ARC AKTIF
    ========================================================== --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-100 pb-3">
            <div>
                <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                    <span class="text-lg">{{ $currentArcConfig['emoji'] }}</span>
                    Peta Level Tantangan: {{ $currentArcConfig['name'] }}
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">
                    Pilih level di bawah untuk melihat dan menambah butir soal (maksimal 10 butir soal per level).
                </p>
            </div>
            <div class="text-xs font-semibold px-3 py-1.5 rounded-xl {{ $currentArcConfig['badge'] }}">
                Arc Terpilih: {{ $activeArc }}
            </div>
        </div>

        {{-- 5 Level Roadmap Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-5 gap-3">
            @for($i = 1; $i <= 5; $i++)
                @php
                    $lvlKey = "Level {$i}";
                    $isLvlActive = ($activeLevel === $lvlKey);
                    $soalCountLvl = $levelCounts[$activeArc][$lvlKey] ?? 0;
                    $isLvlFull = $soalCountLvl >= 10;
                    $percentLvl = min(100, ($soalCountLvl / 10) * 100);
                @endphp
                <a
                    href="{{ route('guru.kuis.show', ['quiz' => $quiz, 'arc' => $activeArc, 'level' => $lvlKey]) }}"
                    class="p-4 rounded-2xl border text-center transition-all duration-200 flex flex-col justify-between group relative cursor-pointer {{ $isLvlActive ? 'border-purple-600 bg-purple-50/70 shadow-sm ring-2 ring-purple-200' : 'border-gray-200 bg-gray-50/50 hover:bg-gray-100 hover:border-purple-200' }}"
                >
                    <div>
                        {{-- Top Level Stage & Icon --}}
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-extrabold uppercase tracking-wider {{ $isLvlActive ? 'text-purple-700' : 'text-gray-400' }}">
                                Stage {{ $i }}
                            </span>
                            @if($isLvlFull)
                                <span class="w-4 h-4 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[10px]" title="10/10 Soal Penuh">✓</span>
                            @endif
                        </div>

                        {{-- Level Name --}}
                        <h4 class="text-base font-extrabold text-gray-900 group-hover:text-purple-600 transition">
                            {{ $lvlKey }}
                        </h4>

                        {{-- Soal Count --}}
                        <div class="mt-2 text-xs font-bold {{ $isLvlFull ? 'text-emerald-600' : ($soalCountLvl > 0 ? 'text-purple-700' : 'text-gray-400') }}">
                            {{ $soalCountLvl }} / 10 Soal
                        </div>
                    </div>

                    {{-- Progress Bar Mini --}}
                    <div class="mt-3">
                        <div class="w-full bg-gray-200/80 rounded-full h-1.5 overflow-hidden">
                            <div
                                class="{{ $isLvlFull ? 'bg-emerald-500' : 'bg-purple-600' }} h-1.5 rounded-full transition-all duration-300"
                                style="width: {{ $percentLvl }}%"
                            ></div>
                        </div>
                        <span class="text-[10px] font-semibold mt-1 block {{ $isLvlActive ? 'text-purple-700' : 'text-gray-400' }}">
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
    <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100 pb-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-purple-100 text-purple-700 font-extrabold text-xs">
                        {{ $activeLevel }}
                    </span>
                    <h3 class="text-lg font-bold text-gray-900">
                        Butir Soal [{{ $activeArc }} - {{ $activeLevel }}]
                    </h3>
                </div>
                <p class="text-xs text-gray-500 mt-1">
                    Kapasitas: <strong>{{ $currentSoalCount }} / {{ $maxSoal }} Butir Soal</strong>
                    @if($isLevelFull)
                        • <span class="text-emerald-600 font-bold">✓ Kuota level ini telah terpenuhi (10 soal)</span>
                    @else
                        • <span class="text-purple-600 font-bold">Tersedia {{ $sisaSlot }} slot soal lagi</span>
                    @endif
                </p>
            </div>

            {{-- Tombol Tambah Soal --}}
            @if($isLevelFull)
                <button
                    type="button"
                    disabled
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gray-100 text-gray-400 text-xs font-semibold border border-gray-200 cursor-not-allowed"
                >
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    Maksimal 10 Soal Tercapai
                </button>
            @else
                <button
                    @click="openAddModal()"
                    type="button"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-purple-600 text-white font-semibold text-xs sm:text-sm hover:bg-purple-700 active:scale-[0.98] transition shadow-sm cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    + Tambah Soal ke {{ $activeLevel }}
                </button>
            @endif
        </div>

        {{-- Progress Bar Kapasitas Soal di Level ini --}}
        <div class="space-y-1.5">
            <div class="flex items-center justify-between text-xs text-gray-600 font-medium">
                <span>Progress Pengisian Soal</span>
                <span class="font-extrabold {{ $isLevelFull ? 'text-emerald-600' : 'text-purple-600' }}">
                    {{ $currentSoalCount }} / {{ $maxSoal }} Soal ({{ $percent }}%)
                </span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                <div
                    class="{{ $isLevelFull ? 'bg-emerald-500' : 'bg-purple-600' }} h-2.5 rounded-full transition-all duration-300"
                    style="width: {{ $percent }}%"
                ></div>
            </div>
        </div>

        {{-- Daftar Butir Soal --}}
        @if($soalsInLevel->count() > 0)
            <div class="space-y-4 pt-2">
                @foreach($soalsInLevel as $index => $soal)
                    <div class="p-5 rounded-2xl bg-gray-50/70 border border-gray-200 hover:border-purple-200 transition space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-3">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-xl bg-purple-600 text-white font-extrabold text-xs shrink-0 shadow-sm">
                                    {{ $index + 1 }}
                                </span>
                                <div class="text-sm font-semibold text-gray-800 leading-relaxed">
                                    {!! nl2br(e($soal->pertanyaan)) !!}
                                </div>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                <span class="text-xs font-bold text-purple-700 bg-purple-50 px-2.5 py-1 rounded-lg border border-purple-200">
                                    {{ $soal->bobot }} Poin
                                </span>

                                {{-- Edit Soal --}}
                                <button
                                    @click="openEditModal({{ json_encode($soal) }})"
                                    type="button"
                                    class="p-2 text-gray-400 hover:text-blue-600 rounded-xl hover:bg-blue-50 transition cursor-pointer"
                                    title="Edit Butir Soal"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>

                                {{-- Delete Soal --}}
                                <form
                                    method="POST"
                                    action="{{ route('guru.kuis.soal.destroy', ['quiz' => $quiz, 'soal' => $soal]) }}"
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus butir soal nomor {{ $index + 1 }} ini? Slot akan kembali terbuka.')"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="p-2 text-gray-400 hover:text-rose-600 rounded-xl hover:bg-rose-50 transition cursor-pointer"
                                        title="Hapus Butir Soal"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Gambar Pendukung jika ada --}}
                        @if($soal->gambar)
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $soal->gambar) }}" alt="Gambar Soal" class="max-h-48 rounded-xl border border-gray-200 object-cover">
                            </div>
                        @endif

                        {{-- Opsi A, B, C, D --}}
                        @if(!empty($soal->pilihan) && is_array($soal->pilihan))
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-2">
                                @foreach($soal->pilihan as $key => $opt)
                                    @php
                                        $isKey = is_array($soal->kunci_jawaban) 
                                            ? in_array($key, $soal->kunci_jawaban) 
                                            : (strtolower($soal->kunci_jawaban) == strtolower($key));
                                    @endphp
                                    <div class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl text-xs transition {{ $isKey ? 'bg-emerald-50 border border-emerald-300 font-bold text-emerald-900 shadow-sm' : 'bg-white border border-gray-200 text-gray-700' }}">
                                        <span class="w-5 h-5 rounded-lg {{ $isKey ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600' }} font-bold flex items-center justify-center shrink-0">
                                            {{ strtoupper($key) }}
                                        </span>
                                        <span class="flex-1">{{ $opt }}</span>
                                        @if($isKey)
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
            <div class="py-12 text-center text-gray-400">
                <div class="w-14 h-14 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <p class="text-sm font-bold text-gray-700">Belum ada butir soal di {{ $activeArc }} - {{ $activeLevel }}</p>
                <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                    Setiap level dapat diisi hingga 10 butir soal pilihan ganda. Klik tombol di bawah untuk menambahkan soal pertama.
                </p>
                <button
                    @click="openAddModal()"
                    type="button"
                    class="mt-4 px-4 py-2 rounded-xl bg-purple-600 text-white font-semibold text-xs hover:bg-purple-700 transition cursor-pointer"
                >
                    + Tambah Soal Pertama ke {{ $activeLevel }}
                </button>
            </div>
        @endif
    </div>

    {{-- =========================================================
        MODAL TAMBAH BUTIR SOAL KE LEVEL AKTIF
    ========================================================== --}}
    <div
        x-show="showAddModal"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 overflow-y-auto"
    >
        <div
            @click.away="showAddModal = false"
            class="bg-white w-full max-w-2xl rounded-2xl shadow-xl overflow-hidden max-h-[90vh] flex flex-col animate-in fade-in zoom-in-95 duration-200 my-8"
        >
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">
                            Tambah Soal ke [{{ $activeArc }} - {{ $activeLevel }}]
                        </h3>
                        <p class="text-xs text-gray-500">Soal {{ $currentSoalCount + 1 }} dari maks {{ $maxSoal }} butir soal</p>
                    </div>
                </div>
                <button @click="showAddModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form
                method="POST"
                action="{{ route('guru.kuis.soal.store', $quiz) }}"
                enctype="multipart/form-data"
                class="p-6 overflow-y-auto space-y-4"
            >
                @csrf
                <input type="hidden" name="tipe_soal" value="single_choice">
                <input type="hidden" name="kesulitan" value="{{ $activeArc }}">
                <input type="hidden" name="level" value="{{ $activeLevel }}">

                {{-- Pertanyaan --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">
                        Teks Pertanyaan / Soal <span class="text-rose-500">*</span>
                    </label>
                    <textarea
                        name="pertanyaan"
                        rows="3"
                        required
                        placeholder="Tuliskan teks pertanyaan soal untuk level ini..."
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:bg-white focus:border-purple-500 focus:ring-2 focus:ring-purple-100 outline-none transition"
                    ></textarea>
                </div>

                {{-- Bobot & Gambar --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">
                            Bobot Poin <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="number"
                            name="bobot"
                            value="10"
                            min="1"
                            max="100"
                            required
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:bg-white focus:border-purple-500 focus:ring-2 focus:ring-purple-100 outline-none transition"
                        >
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">
                            Gambar Pendukung (Opsional)
                        </label>
                        <input
                            type="file"
                            name="gambar"
                            accept="image/*"
                            class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 cursor-pointer"
                        >
                    </div>
                </div>

                {{-- Opsi Pilihan A, B, C, D & Kunci Jawaban --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-2">
                        Pilihan Opsi Jawaban & Tentukan Kunci Jawaban <span class="text-rose-500">*</span>
                    </label>
                    <div class="space-y-2.5">
                        @foreach(['a' => 'Pilihan A', 'b' => 'Pilihan B', 'c' => 'Pilihan C', 'd' => 'Pilihan D'] as $key => $label)
                            <div class="flex items-center gap-3 p-2.5 rounded-xl border border-gray-200 bg-gray-50/50 hover:border-purple-200 transition">
                                <label class="flex items-center gap-2 cursor-pointer shrink-0" title="Klik untuk jadikan kunci jawaban benar">
                                    <input
                                        type="radio"
                                        name="kunci_jawaban"
                                        value="{{ $key }}"
                                        {{ $key === 'a' ? 'checked' : '' }}
                                        required
                                        class="w-4 h-4 text-purple-600 focus:ring-purple-500 cursor-pointer"
                                    >
                                    <span class="w-6 h-6 rounded-lg bg-gray-200 font-bold text-xs flex items-center justify-center text-gray-700">
                                        {{ strtoupper($key) }}
                                    </span>
                                </label>
                                <input
                                    type="text"
                                    name="pilihan[{{ $key }}]"
                                    required
                                    placeholder="Teks untuk {{ $label }}..."
                                    class="flex-1 px-3 py-1.5 rounded-lg border border-gray-200 bg-white text-sm focus:border-purple-500 focus:ring-1 focus:ring-purple-100 outline-none transition"
                                >
                            </div>
                        @endforeach
                    </div>
                    <p class="text-[11px] text-gray-400 mt-1.5">
                        * Pilih tombol radio lingkaran di sebelah huruf untuk menandai opsi yang merupakan kunci jawaban yang benar.
                    </p>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100">
                    <button
                        @click="showAddModal = false"
                        type="button"
                        class="px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="px-5 py-2.5 rounded-xl bg-purple-600 text-white text-sm font-semibold hover:bg-purple-700 transition shadow-sm cursor-pointer"
                    >
                        Simpan Butir Soal
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- =========================================================
        MODAL EDIT BUTIR SOAL
    ========================================================== --}}
    <div
        x-show="showEditSoalModal"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 overflow-y-auto"
    >
        <div
            @click.away="showEditSoalModal = false"
            class="bg-white w-full max-w-2xl rounded-2xl shadow-xl overflow-hidden max-h-[90vh] flex flex-col animate-in fade-in zoom-in-95 duration-200 my-8"
        >
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-gray-900">Edit Butir Soal</h3>
                </div>
                <button @click="showEditSoalModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form
                method="POST"
                :action="'/guru/kuis/{{ $quiz->id }}/soal/' + editSoalData.id"
                enctype="multipart/form-data"
                class="p-6 overflow-y-auto space-y-4"
            >
                @csrf
                @method('PUT')
                <input type="hidden" name="tipe_soal" value="single_choice">

                {{-- Pertanyaan --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">
                        Teks Pertanyaan <span class="text-rose-500">*</span>
                    </label>
                    <textarea
                        name="pertanyaan"
                        x-model="editSoalData.pertanyaan"
                        rows="3"
                        required
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:bg-white focus:border-purple-500 focus:ring-2 focus:ring-purple-100 outline-none transition"
                    ></textarea>
                </div>

                {{-- Bobot & Gambar --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">
                            Bobot Poin <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="number"
                            name="bobot"
                            x-model="editSoalData.bobot"
                            min="1"
                            max="100"
                            required
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:bg-white focus:border-purple-500 focus:ring-2 focus:ring-purple-100 outline-none transition"
                        >
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">
                            Ganti Gambar (Opsional)
                        </label>
                        <input
                            type="file"
                            name="gambar"
                            accept="image/*"
                            class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 cursor-pointer"
                        >
                    </div>
                </div>

                {{-- Opsi Pilihan A, B, C, D --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-2">
                        Pilihan Jawaban & Kunci Jawaban <span class="text-rose-500">*</span>
                    </label>
                    <div class="space-y-2.5">
                        @foreach(['a' => 'Pilihan A', 'b' => 'Pilihan B', 'c' => 'Pilihan C', 'd' => 'Pilihan D'] as $key => $label)
                            <div class="flex items-center gap-3 p-2.5 rounded-xl border border-gray-200 bg-gray-50/50 hover:border-purple-200 transition">
                                <label class="flex items-center gap-2 cursor-pointer shrink-0">
                                    <input
                                        type="radio"
                                        name="kunci_jawaban"
                                        value="{{ $key }}"
                                        x-model="editSoalData.kunci_jawaban"
                                        required
                                        class="w-4 h-4 text-purple-600 focus:ring-purple-500 cursor-pointer"
                                    >
                                    <span class="w-6 h-6 rounded-lg bg-gray-200 font-bold text-xs flex items-center justify-center text-gray-700">
                                        {{ strtoupper($key) }}
                                    </span>
                                </label>
                                <input
                                    type="text"
                                    name="pilihan[{{ $key }}]"
                                    x-model="editSoalData.pilihan.{{ $key }}"
                                    required
                                    placeholder="Teks untuk {{ $label }}..."
                                    class="flex-1 px-3 py-1.5 rounded-lg border border-gray-200 bg-white text-sm focus:border-purple-500 focus:ring-1 focus:ring-purple-100 outline-none transition"
                                >
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100">
                    <button
                        @click="showEditSoalModal = false"
                        type="button"
                        class="px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="px-5 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition shadow-sm cursor-pointer"
                    >
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- =========================================================
        MODAL EDIT PENGATURAN KUIS
    ========================================================== --}}
    <div
        x-show="showEditQuizModal"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 overflow-y-auto"
    >
        <div
            @click.away="showEditQuizModal = false"
            class="bg-white w-full max-w-xl rounded-2xl shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-200 my-8"
        >
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Edit Pengaturan Kuis</h3>
                        <p class="text-xs text-gray-500">Sesuaikan judul, deskripsi, dan arc kesulitan aktif.</p>
                    </div>
                </div>
                <button @click="showEditQuizModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form
                method="POST"
                action="{{ route('guru.kuis.update', $quiz) }}"
                class="p-6 space-y-4"
            >
                @csrf
                @method('PUT')
                <input type="hidden" name="pengampu_kelas_id" value="{{ $quiz->pengampu_kelas_id }}">

                {{-- Judul Kuis --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">
                        Judul Kuis Petualangan <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="judul"
                        x-model="editQuizData.judul"
                        required
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:bg-white focus:border-purple-500 focus:ring-2 focus:ring-purple-100 outline-none transition"
                    >
                </div>

                {{-- Deskripsi --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">
                        Deskripsi Petualangan
                    </label>
                    <textarea
                        name="deskripsi"
                        x-model="editQuizData.deskripsi"
                        rows="2"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:bg-white focus:border-purple-500 focus:ring-2 focus:ring-purple-100 outline-none transition"
                    ></textarea>
                </div>

                {{-- Arc Kesulitan Aktif --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">
                        Arc Kesulitan yang Aktif (1 s/d 3) <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                        <label class="flex items-start gap-3 p-3 rounded-xl border border-emerald-200 bg-emerald-50/40 hover:bg-emerald-50 cursor-pointer transition">
                            <input
                                type="checkbox"
                                name="kesulitan_aktif[]"
                                value="Mudah"
                                :checked="editQuizData.kesulitan_aktif && editQuizData.kesulitan_aktif.includes('Mudah')"
                                class="w-4 h-4 mt-0.5 text-emerald-600 rounded focus:ring-emerald-500"
                            >
                            <div>
                                <span class="text-xs font-bold text-emerald-900 block">😊 Arc Mudah</span>
                                <span class="text-[11px] text-emerald-700">5 Level</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-3 rounded-xl border border-indigo-200 bg-indigo-50/40 hover:bg-indigo-50 cursor-pointer transition">
                            <input
                                type="checkbox"
                                name="kesulitan_aktif[]"
                                value="Sedang"
                                :checked="editQuizData.kesulitan_aktif && editQuizData.kesulitan_aktif.includes('Sedang')"
                                class="w-4 h-4 mt-0.5 text-indigo-600 rounded focus:ring-indigo-500"
                            >
                            <div>
                                <span class="text-xs font-bold text-indigo-900 block">🧠 Arc Sedang</span>
                                <span class="text-[11px] text-indigo-700">5 Level</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-3 rounded-xl border border-rose-200 bg-rose-50/40 hover:bg-rose-50 cursor-pointer transition">
                            <input
                                type="checkbox"
                                name="kesulitan_aktif[]"
                                value="Sulit"
                                :checked="editQuizData.kesulitan_aktif && editQuizData.kesulitan_aktif.includes('Sulit')"
                                class="w-4 h-4 mt-0.5 text-rose-600 rounded focus:ring-rose-500"
                            >
                            <div>
                                <span class="text-xs font-bold text-rose-900 block">🔥 Arc Sulit</span>
                                <span class="text-[11px] text-rose-700">5 Level</span>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">
                        Status Kuis
                    </label>
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700">
                            <input type="radio" name="status" value="draft" x-model="editQuizData.status" class="text-purple-600 focus:ring-purple-500">
                            <span>Draf</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700">
                            <input type="radio" name="status" value="published" x-model="editQuizData.status" class="text-purple-600 focus:ring-purple-500">
                            <span>Diterbitkan</span>
                        </label>
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100">
                    <button
                        @click="showEditQuizModal = false"
                        type="button"
                        class="px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="px-5 py-2.5 rounded-xl bg-purple-600 text-white text-sm font-semibold hover:bg-purple-700 transition shadow-sm cursor-pointer"
                    >
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
