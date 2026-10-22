@extends('layouts.siswa')

@section('title', ($mapel->nama_mapel ?? 'Mata Pelajaran') . ' - E-Learning LearnPoint')

@push('styles')
<style>
    .custom-shadow-card {
        box-shadow: 0 4px 20px -2px rgba(148, 163, 184, 0.12);
    }
</style>
@endpush

@section('content')
@php
    $namaLower = strtolower($mapel->nama_mapel ?? '');
    $totalMateri = $materis->count();
    $completedMateri = $totalMateri > 0 ? min($totalMateri, 1) : 0; // Default indikasi awal
    $persenSelesai = $totalMateri > 0 ? round(($completedMateri / $totalMateri) * 100) : 0;
@endphp

<div x-data="{ activeTab: '{{ $activeTab ?? 'materi' }}' }" class="space-y-6">

    <!-- BEGIN: HeroBanner -->
    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#e9f2ff] via-[#e4effe] to-[#ecf3ff] p-8 lg:p-10 border border-blue-100 shadow-sm" data-purpose="course-hero-banner">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <!-- Left: Course Details -->
            <div class="flex items-start gap-6 max-w-2xl">
                <!-- Course Square Icon -->
                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 flex-shrink-0 flex items-center justify-center shadow-lg shadow-blue-500/25 border-2 border-white/40">
                    @if(str_contains($namaLower, 'matematika') || str_contains($namaLower, 'math'))
                        <div class="grid grid-cols-2 gap-1.5 p-3 text-white font-extrabold text-lg sm:text-xl text-center leading-none">
                            <span class="flex items-center justify-center">＋</span>
                            <span class="flex items-center justify-center">－</span>
                            <span class="flex items-center justify-center">✕</span>
                            <span class="flex items-center justify-center">÷</span>
                        </div>
                    @elseif(str_contains($namaLower, 'indonesia'))
                        <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M18 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 4h5v8l-2.5-1.5L6 12V4z"></path>
                        </svg>
                    @elseif(str_contains($namaLower, 'inggris') || str_contains($namaLower, 'english'))
                        <span class="text-white font-black text-2xl sm:text-3xl">Aa</span>
                    @elseif(str_contains($namaLower, 'ipa') || str_contains($namaLower, 'sains'))
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    @elseif(str_contains($namaLower, 'ips') || str_contains($namaLower, 'sosial'))
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    @elseif(str_contains($namaLower, 'pkn') || str_contains($namaLower, 'ppkn'))
                        <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"></path>
                        </svg>
                    @else
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    @endif
                </div>

                <!-- Title and Meta -->
                <div class="flex-1 pt-1">
                    <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mb-2">{{ $mapel->nama_mapel }}</h1>
                    <p class="text-base text-slate-500 font-medium mb-6">
                        Kelas {{ $kelas->nama_kelas ?? 'VII' }} • {{ $semester ? (str_contains(strtolower($semester), 'semester') ? $semester : 'Semester ' . $semester) : 'Semester 1' }}
                    </p>

                    <!-- Progress Bar Section -->
                    <div class="w-full max-w-md">
                        <div class="w-full bg-blue-200/60 rounded-full h-3 overflow-hidden">
                            <div class="bg-blue-600 h-3 rounded-full transition-all duration-500" style="width: {{ $totalMateri > 0 ? max(15, $persenSelesai) : 0 }}%"></div>
                        </div>
                        <div class="flex justify-between items-center mt-2.5 text-xs sm:text-sm font-semibold text-slate-500">
                            <span>{{ $persenSelesai }}% selesai</span>
                            <span>{{ $completedMateri }} dari {{ $totalMateri }} materi</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Decorative Learning Stationery Illustration (Pure SVG) -->
        <div class="absolute right-4 bottom-0 top-0 hidden lg:flex items-center justify-end pointer-events-none opacity-90 select-none pr-8">
            <svg class="h-48 w-80 text-blue-300" fill="none" viewBox="0 0 320 200" xmlns="http://www.w3.org/2000/svg">
                <!-- Sparkle Top Left -->
                <path d="M72 65L74 58L76 65L83 67L76 69L74 76L72 69L65 67L72 65Z" fill="#93C5FD"></path>
                <!-- Scribble Arrow -->
                <path d="M130 52C140 45 152 48 158 55C164 62 172 58 178 52" stroke="#93C5FD" stroke-linecap="round" stroke-width="2.5"></path>
                <path d="M174 48L180 52L176 58" stroke="#93C5FD" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"></path>
                <!-- Set Square / Triangle Ruler -->
                <g transform="translate(68, 65) rotate(-15)">
                    <polygon fill="#BFDBFE" fill-opacity="0.75" points="0,95 75,95 75,20" stroke="#60A5FA" stroke-width="2.5"></polygon>
                    <polygon fill="#EBF3FE" points="20,83 60,83 60,43"></polygon>
                    <!-- Ruler Ticks -->
                    <line stroke="#60A5FA" stroke-width="2" x1="75" x2="68" y1="35" y2="35"></line>
                    <line stroke="#60A5FA" stroke-width="2" x1="75" x2="68" y1="50" y2="50"></line>
                    <line stroke="#60A5FA" stroke-width="2" x1="75" x2="68" y1="65" y2="65"></line>
                    <line stroke="#60A5FA" stroke-width="2" x1="75" x2="68" y1="80" y2="80"></line>
                </g>
                <!-- Book Stack -->
                <!-- Book 1 (Bottom) -->
                <g transform="translate(115, 120)">
                    <rect fill="#818CF8" height="28" rx="6" width="95" x="0" y="8"></rect>
                    <path d="M12 8L105 8C108 8 110 10 110 13L110 32C110 35 108 37 105 37L12 37" fill="#F8FAFC"></path>
                    <rect fill="#6366F1" height="29" rx="4" width="16" x="0" y="8"></rect>
                    <line stroke="#E2E8F0" stroke-width="1.5" x1="16" x2="95" y1="18" y2="18"></line>
                    <line stroke="#E2E8F0" stroke-width="1.5" x1="16" x2="95" y1="26" y2="26"></line>
                </g>
                <!-- Book 2 (Middle) -->
                <g transform="translate(118, 92)">
                    <rect fill="#60A5FA" height="26" rx="6" width="95" x="0" y="8"></rect>
                    <path d="M12 8L102 8C105 8 107 10 107 13L107 30C107 33 105 35 102 35L12 35" fill="#F8FAFC"></path>
                    <rect fill="#3B82F6" height="27" rx="4" width="16" x="0" y="8"></rect>
                    <line stroke="#E2E8F0" stroke-width="1.5" x1="16" x2="95" y1="17" y2="17"></line>
                    <line stroke="#E2E8F0" stroke-width="1.5" x1="16" x2="95" y1="24" y2="24"></line>
                </g>
                <!-- Book 3 (Top) -->
                <g transform="translate(122, 65)">
                    <rect fill="#3B82F6" height="24" rx="6" width="92" x="0" y="8"></rect>
                    <path d="M12 8L99 8C102 8 104 10 104 12L104 28C104 31 102 33 99 33L12 33" fill="#F8FAFC"></path>
                    <rect fill="#2563EB" height="25" rx="4" width="16" x="0" y="8"></rect>
                    <line stroke="#E2E8F0" stroke-width="1.5" x1="16" x2="90" y1="16" y2="16"></line>
                    <line stroke="#E2E8F0" stroke-width="1.5" x1="16" x2="90" y1="22" y2="22"></line>
                </g>
                <!-- Pencil Cup Holder & Pencils -->
                <g transform="translate(235, 68)">
                    <!-- Yellow Pencil -->
                    <g transform="translate(18, 0) rotate(12)">
                        <polygon fill="#FCD34D" points="5,0 9,15 1,15"></polygon>
                        <polygon fill="#1E293B" points="5,0 7,6 3,6"></polygon>
                        <rect fill="#F59E0B" height="42" width="8" x="1" y="15"></rect>
                    </g>
                    <!-- Blue Pen -->
                    <g transform="translate(4, 8) rotate(-10)">
                        <rect fill="#93C5FD" height="45" rx="2" width="7" x="0" y="0"></rect>
                        <rect fill="#3B82F6" height="10" width="5" x="1" y="2"></rect>
                    </g>
                    <!-- Cup -->
                    <path d="M2 38L8 85C8.5 88 11 90 14 90H28C31 90 33.5 88 34 85L40 38H2Z" fill="#3B82F6" fill-opacity="0.85"></path>
                </g>
            </svg>
        </div>
    </section>
    <!-- END: HeroBanner -->

    <!-- BEGIN: BackNavigation -->
    <div>
        <a class="inline-flex items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-700 transition-colors" href="{{ route('siswa.dashboard') }}">
            <svg class="w-4 h-4 stroke-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
            <span>Kembali ke Mata Pelajaran</span>
        </a>
    </div>
    <!-- END: BackNavigation -->

    <!-- BEGIN: SegmentedNavigationTabs -->
    <nav class="w-full bg-white rounded-2xl p-1.5 border border-slate-200/80 shadow-sm" data-purpose="course-tab-navigation">
        <div class="grid gap-1.5 grid-cols-3">
            <!-- Tab: Materi -->
            <button @click="activeTab = 'materi'"
                    :class="activeTab === 'materi' ? 'bg-[#eaf2ff] border border-blue-200 text-blue-600 font-bold shadow-sm' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                    class="flex items-center justify-center gap-2.5 py-3.5 px-4 rounded-xl text-sm transition-all"
                    type="button">
                <svg class="w-5 h-5" :class="activeTab === 'materi' ? 'text-blue-600' : 'text-slate-500'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <span>Materi ({{ $materis->count() }})</span>
            </button>

            <!-- Tab: Tugas -->
            <button @click="activeTab = 'tugas'"
                    :class="activeTab === 'tugas' ? 'bg-[#eaf2ff] border border-blue-200 text-blue-600 font-bold shadow-sm' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                    class="flex items-center justify-center gap-2.5 py-3.5 px-4 rounded-xl text-sm transition-all"
                    type="button">
                <svg class="w-5 h-5" :class="activeTab === 'tugas' ? 'text-blue-600' : 'text-slate-500'" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <span>Tugas ({{ $tugases->count() }})</span>
            </button>

            <!-- Tab: Ujian -->
            <button @click="activeTab = 'ujian'"
                    :class="activeTab === 'ujian' ? 'bg-[#eaf2ff] border border-blue-200 text-blue-600 font-bold shadow-sm' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                    class="flex items-center justify-center gap-2.5 py-3.5 px-4 rounded-xl text-sm transition-all"
                    type="button">
                <svg class="w-5 h-5" :class="activeTab === 'ujian' ? 'text-blue-600' : 'text-slate-500'" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <span>Ujian ({{ $ujians->count() }})</span>
            </button>
        </div>
    </nav>
    <!-- END: SegmentedNavigationTabs -->

    <!-- BEGIN: LearningMaterialSection (Tab: Materi) -->
    <section x-show="activeTab === 'materi'" class="bg-white rounded-3xl p-6 sm:p-10 border border-slate-200/80 shadow-sm" data-purpose="material-list-card">
        <!-- Section Header -->
        <div class="flex items-start gap-4 mb-8">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 border border-blue-100 flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </div>
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Materi Pembelajaran</h2>
                <p class="text-sm text-slate-500 font-normal mt-1">Pelajari materi berikut sesuai urutan untuk memahami konsep dengan lebih baik.</p>
            </div>
        </div>

        <!-- Item List Container -->
        <div class="space-y-4">
            @forelse($materis as $index => $materi)
                @php
                    $colors = [
                        ['bg' => 'bg-blue-50', 'border' => 'border-blue-100', 'text' => 'text-blue-600'],
                        ['bg' => 'bg-purple-50', 'border' => 'border-purple-100', 'text' => 'text-purple-600'],
                        ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-100', 'text' => 'text-emerald-600'],
                    ];
                    $c = $colors[$index % count($colors)];
                    $hasFile = !empty($materi->file_materi);
                    $hasYoutube = !empty($materi->url_youtube);
                @endphp

                <div class="group flex flex-col sm:flex-row sm:items-center justify-between p-5 rounded-2xl border border-slate-200 hover:border-blue-300 hover:bg-blue-50/20 transition-all">
                    <div class="flex items-center gap-4 mb-4 sm:mb-0">
                        <!-- Document Icon -->
                        <div class="w-12 h-12 rounded-xl {{ $c['bg'] }} border {{ $c['border'] }} flex items-center justify-center {{ $c['text'] }} flex-shrink-0 group-hover:scale-105 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </div>

                        <!-- Title and Sub Info -->
                        <div>
                            <h3 class="text-base font-bold text-slate-900 group-hover:text-blue-600 transition-colors">
                                {{ $loop->iteration }}. {{ $materi->judul }}
                            </h3>
                            <div class="flex items-center gap-2 mt-1 text-xs sm:text-sm text-slate-400 font-medium">
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10" stroke-width="2"></circle>
                                        <polyline points="12 6 12 12 16 14" stroke-width="2"></polyline>
                                    </svg>
                                    {{ $materi->created_at ? $materi->created_at->isoFormat('D MMM Y') : '15 menit' }}
                                </span>
                                <span>|</span>
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                    @if($hasFile && $hasYoutube) Modul PDF & Video @elseif($hasFile) Modul PDF @elseif($hasYoutube) Video Pembelajaran @else Modul Ajar @endif
                                </span>
                            </div>
                            @if(!empty($materi->deskripsi))
                                <p class="text-xs text-slate-500 mt-2 line-clamp-2 max-w-xl">
                                    {{ $materi->deskripsi }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <!-- Status Badge & Action -->
                    <div class="flex items-center justify-between sm:justify-end gap-2.5 flex-wrap">
                        @if($hasFile)
                            <!-- Tombol Baca Online (Buka di Tab Baru) -->
                            <a href="{{ asset('storage/' . $materi->file_materi) }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white border border-emerald-200 transition-colors shadow-2xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                                Baca Online
                            </a>

                            <!-- Tombol Unduh Modul -->
                            <a href="{{ asset('storage/' . $materi->file_materi) }}" download
                               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white border border-blue-200 transition-colors shadow-2xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                                Unduh Modul
                            </a>
                        @endif

                        @if($hasYoutube)
                            <a href="{{ $materi->url_youtube }}" target="_blank"
                               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-red-50 text-red-600 hover:bg-red-600 hover:text-white border border-red-200 transition-colors shadow-2xs">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"></path>
                                </svg>
                                Tonton Video
                            </a>
                        @endif

                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-600 border border-emerald-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                            Tersedia
                        </span>
                    </div>
                </div>
            @empty
                <div class="py-12 text-center bg-slate-50/50 rounded-2xl border border-dashed border-slate-200 text-slate-400">
                    <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    <p class="text-sm font-semibold text-slate-700">Belum ada materi untuk mata pelajaran ini</p>
                    <p class="text-xs text-slate-400 mt-1">Materi pelajaran dari guru pengampu akan segera diunggah ke sini.</p>
                </div>
            @endforelse
        </div>
    </section>
    <!-- END: LearningMaterialSection -->

    <!-- BEGIN: AssignmentSection (Tab: Tugas) -->
    <section x-show="activeTab === 'tugas'" style="display: none;" class="bg-white rounded-3xl p-6 sm:p-10 border border-slate-200/80 shadow-sm" data-purpose="assignment-list-card">
        <!-- Section Header -->
        <div class="flex items-start gap-4 mb-8">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 border border-amber-100 flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </div>
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Tugas & PR</h2>
                <p class="text-sm text-slate-500 font-normal mt-1">Kerjakan dan kumpulkan tugas sesuai tenggat waktu yang ditentukan guru pengampu.</p>
            </div>
        </div>

        <div class="space-y-4">
            @forelse($tugases as $tugas)
                @php
                    $pengumpulan = $tugas->nilais->first();
                    $sudahKumpul = $pengumpulan !== null;
                    $terlewat = $tugas->deadline && \Carbon\Carbon::parse($tugas->deadline)->isPast() && !$sudahKumpul;
                @endphp

                <div class="group flex flex-col sm:flex-row sm:items-center justify-between p-5 rounded-2xl border border-slate-200 hover:border-amber-300 hover:bg-amber-50/10 transition-all">
                    <div class="flex items-center gap-4 mb-4 sm:mb-0">
                        <div class="w-12 h-12 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 group-hover:text-amber-600 transition-colors">
                                {{ $loop->iteration }}. {{ $tugas->judul }}
                            </h3>
                            <div class="flex items-center gap-2 mt-1 text-xs sm:text-sm text-slate-400 font-medium">
                                <span>Batas: {{ $tugas->deadline ? \Carbon\Carbon::parse($tugas->deadline)->isoFormat('D MMM Y, HH:mm') : 'Tidak ada batas' }}</span>
                                <span>|</span>
                                <span>Tipe: {{ ucfirst($tugas->tipe ?? 'Upload File') }}</span>
                            </div>
                            @if(!empty($tugas->deskripsi))
                                <p class="text-xs text-slate-500 mt-2 line-clamp-2 max-w-xl">{{ $tugas->deskripsi }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center justify-between sm:justify-end gap-4">
                        @if($sudahKumpul)
                            <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-600 border border-emerald-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                                Sudah Dikumpulkan
                            </span>
                        @elseif($terlewat)
                            <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-red-50 text-red-600 border border-red-100">
                                Melewati Batas
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-600 border border-blue-200">
                                Belum Dikerjakan
                            </span>
                        @endif

                        <a href="{{ Route::has('siswa.tugas.show') ? route('siswa.tugas.show', $tugas->id) : route('siswa.tugas') }}" class="px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition shadow-xs">
                            {{ $sudahKumpul ? 'Lihat Tugas' : 'Kerjakan' }}
                        </a>
                    </div>
                </div>
            @empty
                <div class="py-12 text-center bg-slate-50/50 rounded-2xl border border-dashed border-slate-200 text-slate-400">
                    <p class="text-sm font-semibold text-slate-700">Belum ada tugas untuk mata pelajaran ini</p>
                    <p class="text-xs text-slate-400 mt-1">Tugas baru dari guru akan muncul di sini.</p>
                </div>
            @endforelse
        </div>
    </section>
    <!-- END: AssignmentSection -->

    <!-- BEGIN: ExamSection (Tab: Ujian) -->
    <section x-show="activeTab === 'ujian'" style="display: none;" class="bg-white rounded-3xl p-6 sm:p-10 border border-slate-200/80 shadow-sm" data-purpose="exam-list-card">
        <!-- Section Header -->
        <div class="flex items-start gap-4 mb-8">
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 border border-indigo-100 flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </div>
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Ujian & Penilaian</h2>
                <p class="text-sm text-slate-500 font-normal mt-1">Daftar evaluasi dan asesmen untuk mengukur pemahaman konsep.</p>
            </div>
        </div>

        <div class="space-y-4">
            @forelse($ujians as $ujian)
                <div class="group flex flex-col sm:flex-row sm:items-center justify-between p-5 rounded-2xl border border-slate-200 hover:border-indigo-300 hover:bg-indigo-50/10 transition-all">
                    <div class="flex items-center gap-4 mb-4 sm:mb-0">
                        <div class="w-12 h-12 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">
                                {{ $loop->iteration }}. {{ $ujian->judul }}
                            </h3>
                            <div class="flex items-center gap-2 mt-1 text-xs sm:text-sm text-slate-400 font-medium">
                                <span>Durasi: {{ $ujian->durasi_menit ?? 60 }} menit</span>
                                <span>|</span>
                                <span>Batas: {{ $ujian->deadline ? \Carbon\Carbon::parse($ujian->deadline)->isoFormat('D MMM Y, HH:mm') : 'Sesuai Jadwal' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between sm:justify-end gap-4">
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-600 border border-indigo-200">
                            Terjadwal
                        </span>
                    </div>
                </div>
            @empty
                <div class="py-12 text-center bg-slate-50/50 rounded-2xl border border-dashed border-slate-200 text-slate-400">
                    <p class="text-sm font-semibold text-slate-700">Belum ada ujian yang dijadwalkan</p>
                    <p class="text-xs text-slate-400 mt-1">Ujian resmi akan diinformasikan oleh guru mata pelajaran.</p>
                </div>
            @endforelse
        </div>
    </section>
    <!-- END: ExamSection -->


</div>
@endsection
