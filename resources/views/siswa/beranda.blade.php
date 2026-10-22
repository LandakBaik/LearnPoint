@extends('layouts.siswa')

@section('title', 'E-Learning Dashboard - LearnPoint')

@section('content')
<div x-data="{ currentTab: '{{ request('tab', 'mapel') }}' }" class="space-y-6">

    <!-- BEGIN: WelcomeBanner -->
    <section class="relative overflow-hidden rounded-[24px] bg-gradient-to-r from-[#EBF4FF] via-[#EDF6FF] to-[#DCEEFE] p-7 md:p-9 border border-blue-100 shadow-sm" data-purpose="welcome-banner">
        <div class="relative z-10 max-w-2xl">
            <h1 class="text-2xl md:text-[28px] font-extrabold text-[#0F2851] tracking-tight mb-3">
                Selamat Datang, {{ $siswa->nama_siswa ?? auth()->user()->name }}!
            </h1>

            <!-- Student Meta Badges -->
            <div class="flex flex-wrap items-center gap-y-2 text-sm text-[#475569] font-medium mb-4">
                <!-- Class Meta -->
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#2563EB]" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 3L1 9L12 15L21 10.09V17H23V9M5 13.18V17.18C5 19.94 8.13 22 12 22C15.87 22 19 19.94 19 17.18V13.18L12 17L5 13.18Z"></path>
                    </svg>
                    <span>Kelas: <strong class="font-semibold text-[#2563EB]">{{ $kelas->nama_kelas ?? ($siswa->anggotaKelases->first()->kelas->nama_kelas ?? 'Belum Ditentukan') }}</strong></span>
                </div>
                <span class="mx-3 text-slate-300">|</span>

                <!-- Generation Meta -->
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#2563EB]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                    <span>Angkatan: <strong class="font-semibold text-[#2563EB]">{{ $tahunAjaran ?? ($siswa->anggotaKelases->first()->tahun_ajaran ?? '2024') }}</strong></span>
                </div>
                <span class="mx-3 text-slate-300">|</span>

                <!-- Student ID Meta -->
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#2563EB]" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"></path>
                    </svg>
                    <span>NIS: <strong class="font-semibold text-[#2563EB]">{{ $siswa->nis ?? '-' }}</strong></span>
                </div>
            </div>

            <p class="text-sm font-medium text-slate-500">
                Terus semangat belajar dan raih cita-citamu!
            </p>
        </div>

        <!-- Right 3D Books Illustration Graphic -->
        <div class="absolute right-6 -bottom-2 hidden lg:flex items-end pointer-events-none select-none">
            <svg class="w-[280px] h-[160px]" fill="none" viewBox="0 0 340 190" xmlns="http://www.w3.org/2000/svg">
                <!-- Ambient Shadow -->
                <ellipse cx="170" cy="175" fill="#93C5FD" fill-opacity="0.3" rx="140" ry="12"></ellipse>
                <!-- Stacked Book Bottom (Blue) -->
                <path d="M30 148 L170 178 L255 142 L115 116 Z" fill="#3B82F6"></path>
                <path d="M30 148 L30 162 L170 192 L170 178 Z" fill="#1D4ED8"></path>
                <path d="M170 178 L170 192 L255 156 L255 142 Z" fill="#2563EB"></path>
                <!-- Pages edge -->
                <path d="M167 176 L252 140 L250 137 L167 172 Z" fill="#F8FAFC"></path>
                <!-- Stacked Book Middle (Yellow/Orange) -->
                <path d="M42 125 L172 153 L245 122 L118 97 Z" fill="#FBBF24"></path>
                <path d="M42 125 L42 138 L172 165 L172 153 Z" fill="#D97706"></path>
                <path d="M172 153 L172 165 L245 133 L245 122 Z" fill="#F59E0B"></path>
                <!-- Pages edge -->
                <path d="M170 151 L242 120 L240 117 L170 147 Z" fill="#FFFFFF"></path>
                <!-- Stacked Book Top (Sky/Cyan) -->
                <path d="M50 102 L174 127 L238 100 L118 78 Z" fill="#38BDF8"></path>
                <path d="M50 102 L50 114 L174 138 L174 127 Z" fill="#0284C7"></path>
                <path d="M174 127 L174 138 L238 111 L238 100 Z" fill="#0EA5E9"></path>
                <!-- Pages edge -->
                <path d="M172 125 L235 98 L233 95 L172 121 Z" fill="#F0F9FF"></path>
                <!-- Pencil Stand Cup & Stationery -->
                <rect fill="#3B82F6" height="48" rx="8" width="34" x="260" y="105"></rect>
                <ellipse cx="277" cy="105" fill="#60A5FA" rx="17" ry="5"></ellipse>
                <!-- Pencils / Ruler -->
                <rect fill="#10B981" height="42" rx="3" transform="rotate(-10 264 66)" width="6" x="264" y="66"></rect>
                <polygon fill="#FDE047" points="266,58 262,67 270,66"></polygon>
                <circle cx="266" cy="59" fill="#1E293B" r="1.5"></circle>
                <rect fill="#EF4444" height="50" rx="3" width="6" x="274" y="58"></rect>
                <polygon fill="#FDE047" points="277,50 273,59 281,59"></polygon>
                <circle cx="277" cy="51" fill="#1E293B" r="1.5"></circle>
                <rect fill="#F59E0B" height="38" rx="2" transform="rotate(12 282 70)" width="7" x="282" y="70"></rect>
            </svg>
        </div>
    </section>
    <!-- END: WelcomeBanner -->

    <!-- BEGIN: QuickAccessSection -->
    <section class="grid grid-cols-1 md:grid-cols-2 gap-5" data-purpose="quick-access">
        <!-- Tugas Terbaru Card -->
        <article class="bg-white rounded-[22px] p-5 border border-slate-100 shadow-[0_2px_8px_rgba(0,0,0,0.03)] flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                            <!-- Clipboard Icon -->
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-800">Tugas Terbaru</h2>
                            <p class="text-xs text-slate-400 mt-0.5">Lihat tugas yang perlu kamu kerjakan segera.</p>
                        </div>
                    </div>
                    <a class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:text-blue-700 transition" href="{{ route('siswa.tugas') }}">
                        Lihat Semua
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path d="M14 5l7 7m0 0l-7 7m7-7H3" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </a>
                </div>

                @if($tugasTerbaru)
                    <!-- Nested Task Item -->
                    <a class="group flex items-center justify-between p-3.5 rounded-xl border border-slate-100 bg-[#FBFDFF] hover:bg-slate-50 transition" href="{{ route('siswa.tugas.show', $tugasTerbaru->id) }}">
                        <div class="flex items-center gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-blue-500 text-white flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-800 group-hover:text-blue-600 transition truncate max-w-xs sm:max-w-md">
                                    {{ $tugasTerbaru->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? 'Tugas' }} – {{ $tugasTerbaru->judul }}
                                </h3>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    Batas pengumpulan: {{ $tugasTerbaru->deadline ? \Carbon\Carbon::parse($tugasTerbaru->deadline)->isoFormat('D MMM Y') : 'Tidak ditentukan' }}
                                </p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-slate-400 group-hover:translate-x-0.5 transition" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </a>
                @else
                    <div class="p-4 rounded-xl border border-dashed border-slate-200 bg-slate-50/50 text-center">
                        <p class="text-xs font-medium text-slate-500">Tidak ada tugas yang tertunda. Kerja bagus!</p>
                    </div>
                @endif
            </div>
        </article>

        <!-- Materi Terbaru Card -->
        <article class="bg-white rounded-[22px] p-5 border border-slate-100 shadow-[0_2px_8px_rgba(0,0,0,0.03)] flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <!-- Book Icon -->
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-800">Materi Terbaru</h2>
                            <p class="text-xs text-slate-400 mt-0.5">Lanjutkan materi yang terakhir kamu akses.</p>
                        </div>
                    </div>
                    <a class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:text-blue-700 transition" href="{{ route('siswa.materi') }}">
                        Lihat Semua
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path d="M14 5l7 7m0 0l-7 7m7-7H3" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </a>
                </div>

                @if($materiTerbaru)
                    @php
                        $materiMapelId = $materiTerbaru->pengampuKelas?->guruMapel?->mapel_id ?? $materiTerbaru->id;
                    @endphp
                    <!-- Nested Lesson Item -->
                    <a class="group flex items-center justify-between p-3.5 rounded-xl border border-slate-100 bg-[#FBFDFF] hover:bg-slate-50 transition" href="{{ route('siswa.materi.show', $materiMapelId) }}">
                        <div class="flex items-center gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-indigo-500 text-white flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H8V4h12v12z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-800 group-hover:text-indigo-600 transition truncate max-w-xs sm:max-w-md">
                                    {{ $materiTerbaru->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? 'Materi' }} – {{ $materiTerbaru->judul }}
                                </h3>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    Diunggah: {{ $materiTerbaru->created_at ? $materiTerbaru->created_at->isoFormat('D MMM Y') : 'Baru' }}
                                </p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-slate-400 group-hover:translate-x-0.5 transition" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </a>
                @else
                    <div class="p-4 rounded-xl border border-dashed border-slate-200 bg-slate-50/50 text-center">
                        <p class="text-xs font-medium text-slate-500">Belum ada materi terbaru yang tersedia.</p>
                    </div>
                @endif
            </div>
        </article>
    </section>
    <!-- END: QuickAccessSection -->

    <!-- BEGIN: NavigationTabs -->
    <div class="bg-[#EEF2F6] p-1.5 rounded-2xl flex items-center max-w-full sm:max-w-fit" data-purpose="section-tabs">
        <!-- Tab: Mata Pelajaran -->
        <button @click="currentTab = 'mapel'"
                :class="currentTab === 'mapel' ? 'bg-[#DCEAFE] text-blue-600 font-bold shadow-sm' : 'text-slate-500 font-semibold hover:text-slate-800'"
                class="flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-sm transition"
                type="button">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
            <span>Mata Pelajaran</span>
        </button>

        <!-- Tab: Statistik -->
        <button @click="currentTab = 'statistik'"
                :class="currentTab === 'statistik' ? 'bg-[#DCEAFE] text-blue-600 font-bold shadow-sm' : 'text-slate-500 font-semibold hover:text-slate-800'"
                class="flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-sm transition"
                type="button">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M18 20V10m-6 10V4m-6 16v-6" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
            <span>Statistik</span>
        </button>

        <!-- Tab: Kuiz -->
        <button @click="currentTab = 'kuiz'"
                :class="currentTab === 'kuiz' ? 'bg-[#DCEAFE] text-blue-600 font-bold shadow-sm' : 'text-slate-500 font-semibold hover:text-slate-800'"
                class="flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-sm transition"
                type="button">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
            <span>Kuiz</span>
        </button>
    </div>
    <!-- END: NavigationTabs -->

    <!-- BEGIN: SubjectsGridSection (Tab Mata Pelajaran) -->
    <section x-show="currentTab === 'mapel'" data-purpose="subjects-grid" class="space-y-5">
        <!-- Section Header -->
        <div>
            <h2 class="text-2xl font-extrabold text-[#0F2851] tracking-tight">Mata Pelajaran</h2>
            <p class="text-sm text-slate-500 mt-1">Pilih mata pelajaran untuk melihat materi, tugas, dan ujian.</p>
        </div>

        @php
            // Palet tema spesifik berdasarkan nama mapel atau index rotasi
            $subjectThemeStyles = [
                'matematika' => [
                    'card_bg' => 'bg-[#EEF5FF]',
                    'card_border' => 'border-[#DCE7F7]',
                    'badge_bg' => 'bg-[#2563EB]',
                    'badge_text' => 'text-white',
                    'hover_text' => 'group-hover:text-blue-600',
                    'icon_type' => 'math',
                ],
                'indonesia' => [
                    'card_bg' => 'bg-[#FFF1F2]',
                    'card_border' => 'border-[#FFE2E5]',
                    'badge_bg' => 'bg-[#EF4444]',
                    'badge_text' => 'text-white',
                    'hover_text' => 'group-hover:text-red-600',
                    'icon_type' => 'book',
                ],
                'inggris' => [
                    'card_bg' => 'bg-[#F3F0FF]',
                    'card_border' => 'border-[#E6E0FF]',
                    'badge_bg' => 'bg-[#6366F1]',
                    'badge_text' => 'text-white',
                    'hover_text' => 'group-hover:text-indigo-600',
                    'icon_type' => 'letters',
                ],
                'ipa' => [
                    'card_bg' => 'bg-[#EFFBF4]',
                    'card_border' => 'border-[#DCF4E7]',
                    'badge_bg' => 'bg-[#10B981]',
                    'badge_text' => 'text-white',
                    'hover_text' => 'group-hover:text-emerald-600',
                    'icon_type' => 'beaker',
                ],
                'ips' => [
                    'card_bg' => 'bg-[#FFF8EA]',
                    'card_border' => 'border-[#FDEBC7]',
                    'badge_bg' => 'bg-[#F59E0B]',
                    'badge_text' => 'text-white',
                    'hover_text' => 'group-hover:text-amber-600',
                    'icon_type' => 'globe',
                ],
                'pkn' => [
                    'card_bg' => 'bg-[#EDF8F9]',
                    'card_border' => 'border-[#D5F0F2]',
                    'badge_bg' => 'bg-[#06B6D4]',
                    'badge_text' => 'text-white',
                    'hover_text' => 'group-hover:text-cyan-600',
                    'icon_type' => 'group',
                ],
            ];

            $fallbackThemes = [
                ['card_bg' => 'bg-[#EEF5FF]', 'card_border' => 'border-[#DCE7F7]', 'badge_bg' => 'bg-[#2563EB]', 'hover_text' => 'group-hover:text-blue-600', 'icon_type' => 'book'],
                ['card_bg' => 'bg-[#FFF1F2]', 'card_border' => 'border-[#FFE2E5]', 'badge_bg' => 'bg-[#EF4444]', 'hover_text' => 'group-hover:text-red-600', 'icon_type' => 'book'],
                ['card_bg' => 'bg-[#F3F0FF]', 'card_border' => 'border-[#E6E0FF]', 'badge_bg' => 'bg-[#6366F1]', 'hover_text' => 'group-hover:text-indigo-600', 'icon_type' => 'letters'],
                ['card_bg' => 'bg-[#EFFBF4]', 'card_border' => 'border-[#DCF4E7]', 'badge_bg' => 'bg-[#10B981]', 'hover_text' => 'group-hover:text-emerald-600', 'icon_type' => 'beaker'],
                ['card_bg' => 'bg-[#FFF8EA]', 'card_border' => 'border-[#FDEBC7]', 'badge_bg' => 'bg-[#F59E0B]', 'hover_text' => 'group-hover:text-amber-600', 'icon_type' => 'globe'],
                ['card_bg' => 'bg-[#EDF8F9]', 'card_border' => 'border-[#D5F0F2]', 'badge_bg' => 'bg-[#06B6D4]', 'hover_text' => 'group-hover:text-cyan-600', 'icon_type' => 'group'],
            ];
        @endphp

        <!-- 3x2 Grid Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse ($daftarMapel as $index => $mapel)
                @php
                    $namaLower = strtolower($mapel->nama_mapel ?? '');
                    $theme = null;

                    if (str_contains($namaLower, 'matematika') || str_contains($namaLower, 'math')) {
                        $theme = $subjectThemeStyles['matematika'];
                    } elseif (str_contains($namaLower, 'indonesia')) {
                        $theme = $subjectThemeStyles['indonesia'];
                    } elseif (str_contains($namaLower, 'inggris') || str_contains($namaLower, 'english')) {
                        $theme = $subjectThemeStyles['inggris'];
                    } elseif (str_contains($namaLower, 'ipa') || str_contains($namaLower, 'sains') || str_contains($namaLower, 'biologi') || str_contains($namaLower, 'fisika')) {
                        $theme = $subjectThemeStyles['ipa'];
                    } elseif (str_contains($namaLower, 'ips') || str_contains($namaLower, 'sosial') || str_contains($namaLower, 'geografi') || str_contains($namaLower, 'sejarah')) {
                        $theme = $subjectThemeStyles['ips'];
                    } elseif (str_contains($namaLower, 'pkn') || str_contains($namaLower, 'ppkn') || str_contains($namaLower, 'kewarganegaraan')) {
                        $theme = $subjectThemeStyles['pkn'];
                    } else {
                        $theme = $fallbackThemes[$index % count($fallbackThemes)];
                    }
                @endphp

                <article class="{{ $theme['card_bg'] }} border {{ $theme['card_border'] }} rounded-[22px] p-5 flex flex-col justify-between hover:shadow-md transition">
                    <!-- Subject Header Link -->
                    <a class="flex items-center justify-between mb-5 group" href="{{ route('siswa.materi.show', $mapel->id) }}">
                        <div class="flex items-center gap-3.5">
                            <div class="w-12 h-12 rounded-2xl {{ $theme['badge_bg'] }} text-white flex items-center justify-center font-bold text-sm tracking-wider shadow-sm shrink-0">
                                @if($theme['icon_type'] === 'math')
                                    <span class="leading-none text-center">＋ －<br>✕ ÷</span>
                                @elseif($theme['icon_type'] === 'book')
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M18 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 4h5v8l-2.5-1.5L6 12V4z"></path>
                                    </svg>
                                @elseif($theme['icon_type'] === 'letters')
                                    <span class="text-lg">Aa</span>
                                @elseif($theme['icon_type'] === 'beaker')
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                @elseif($theme['icon_type'] === 'globe')
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                @elseif($theme['icon_type'] === 'group')
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"></path>
                                    </svg>
                                @else
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 3L1 9L12 15L21 10.09V17H23V9M5 13.18V17.18C5 19.94 8.13 22 12 22C15.87 22 19 19.94 19 17.18V13.18L12 17L5 13.18Z"></path>
                                    </svg>
                                @endif
                            </div>
                            <span class="text-base font-bold text-slate-800 {{ $theme['hover_text'] }} transition">{{ $mapel->nama_mapel }}</span>
                        </div>
                        <svg class="w-5 h-5 text-slate-400 group-hover:translate-x-0.5 transition" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </a>

                    <!-- Action Pills -->
                    <div class="grid grid-cols-3 gap-2">
                        <!-- Materi Pill -->
                        <a href="{{ route('siswa.materi.show', ['id' => $mapel->id, 'tab' => 'materi']) }}"
                           class="bg-white/80 hover:bg-white rounded-xl py-3 px-2 border border-slate-100 flex items-center justify-between text-slate-600 text-xs font-semibold shadow-2xs hover:text-blue-600 transition">
                            <span class="flex items-center gap-1.5 truncate">
                                <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Materi
                            </span>
                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"></path></svg>
                        </a>

                        <!-- Tugas Pill -->
                        <a href="{{ route('siswa.materi.show', ['id' => $mapel->id, 'tab' => 'tugas']) }}"
                           class="bg-white/80 hover:bg-white rounded-xl py-3 px-2 border border-slate-100 flex items-center justify-between text-slate-600 text-xs font-semibold shadow-2xs hover:text-blue-600 transition">
                            <span class="flex items-center gap-1.5 truncate">
                                <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                Tugas
                            </span>
                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"></path></svg>
                        </a>

                        <!-- Ujian Pill -->
                        <a href="{{ route('siswa.materi.show', ['id' => $mapel->id, 'tab' => 'ujian']) }}"
                           class="bg-white/80 hover:bg-white rounded-xl py-3 px-2 border border-slate-100 flex items-center justify-between text-slate-600 text-xs font-semibold shadow-2xs hover:text-blue-600 transition">
                            <span class="flex items-center gap-1.5 truncate">
                                <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                                Ujian
                            </span>
                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"></path></svg>
                        </a>
                    </div>
                </article>
            @empty
                <div class="col-span-full py-12 text-center bg-white rounded-2xl border border-dashed border-slate-200 text-slate-400">
                    <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    <p class="text-sm font-semibold text-slate-700">Belum ada mata pelajaran yang tersedia</p>
                    <p class="text-xs text-slate-400 mt-1">Silakan hubungi wali kelas atau operator sekolah.</p>
                </div>
            @endforelse
        </div>
    </section>
    <!-- END: SubjectsGridSection -->

    <!-- BEGIN: DetailedStatisticsSection (Tab Statistik) -->
    <section x-show="currentTab === 'statistik'" style="display: none;" class="bg-white rounded-[24px] border border-slate-100 p-6 md:p-8 shadow-[0_2px_12px_rgba(0,0,0,0.03)] space-y-7" data-purpose="statistics-content">
        <!-- Section Header with Blue Tile Icon -->
        <div class="flex items-start gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-[#2563EB] text-white flex items-center justify-center shrink-0 shadow-sm">
                <!-- Bookmark / Ribbon Icon -->
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-bold text-slate-900 leading-snug">Statistik Keseluruhan Nilai</h2>
                <p class="text-xs md:text-sm text-slate-400">Rekapitulasi nilai dari semua tugas, kuiz, dan ujian.</p>
            </div>
        </div>

        <!-- Top Metrics Row: Circular Progress Donut + 3 Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 items-stretch">
            <!-- Circular Donut Chart Card -->
            <div class="flex items-center justify-center p-2">
                <div class="relative w-36 h-36 rounded-full flex items-center justify-center p-3.5 shadow-sm transition-all duration-500"
                     style="background: conic-gradient(#2563eb 0deg {{ $donutDegree }}deg, #e2e8f0 {{ $donutDegree }}deg 360deg);">
                    <div class="w-full h-full bg-white rounded-full flex flex-col items-center justify-center shadow-inner">
                        <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-tight">Rata-rata Nilai</span>
                        <span class="text-2xl font-extrabold text-slate-800 my-0.5">{{ number_format($rataRataSemua, 1, ',', '.') }}</span>
                        <span class="px-2.5 py-0.5 text-[11px] font-bold rounded-full bg-blue-50 text-blue-600 border border-blue-100">{{ $kategoriNilai }}</span>
                    </div>
                </div>
            </div>

            <!-- Metric Card 1: Tugas (Sky/Blue) -->
            <div class="bg-[#F0F7FF] border border-[#DCEBFE] rounded-2xl p-5 flex flex-col justify-between hover:shadow-xs transition">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#E0EFFF] flex items-center justify-center text-blue-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </div>
                    <span class="text-sm font-bold text-slate-700">Tugas</span>
                </div>
                <div class="flex items-end justify-between mt-4">
                    <div>
                        <p class="text-3xl font-extrabold text-slate-900 tracking-tight leading-none">{{ number_format($rataRataTugas, 1, ',', '.') }}</p>
                        <p class="text-xs text-slate-400 mt-1">Rata-rata nilai</p>
                    </div>
                    <a href="{{ route('siswa.tugas') }}" aria-label="Detail Tugas" class="p-1 rounded-lg text-blue-600 hover:translate-x-0.5 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path d="M14 5l7 7m0 0l-7 7m7-7H3" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Metric Card 2: Kuiz (Purple) -->
            <div class="bg-[#F9F6FF] border border-[#EFE8FE] rounded-2xl p-5 flex flex-col justify-between hover:shadow-xs transition">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#EFE8FE] flex items-center justify-center text-purple-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </div>
                    <span class="text-sm font-bold text-slate-700">Kuiz</span>
                </div>
                <div class="flex items-end justify-between mt-4">
                    <div>
                        <p class="text-3xl font-extrabold text-slate-900 tracking-tight leading-none">{{ number_format($rataRataQuiz, 1, ',', '.') }}</p>
                        <p class="text-xs text-slate-400 mt-1">Rata-rata nilai</p>
                    </div>
                    <button @click="currentTab = 'kuiz'" aria-label="Detail Kuiz" class="p-1 rounded-lg text-purple-600 hover:translate-x-0.5 transition" type="button">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path d="M14 5l7 7m0 0l-7 7m7-7H3" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Metric Card 3: Ujian (Green/Emerald) -->
            <div class="bg-[#F0FAF4] border border-[#DCF4E4] rounded-2xl p-5 flex flex-col justify-between hover:shadow-xs transition">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#DCF4E4] flex items-center justify-center text-emerald-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </div>
                    <span class="text-sm font-bold text-slate-700">Ujian</span>
                </div>
                <div class="flex items-end justify-between mt-4">
                    <div>
                        <p class="text-3xl font-extrabold text-slate-900 tracking-tight leading-none">{{ number_format($rataRataUjian, 1, ',', '.') }}</p>
                        <p class="text-xs text-slate-400 mt-1">Rata-rata nilai</p>
                    </div>
                    <button aria-label="Detail Ujian" class="p-1 rounded-lg text-emerald-600 hover:translate-x-0.5 transition" type="button">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path d="M14 5l7 7m0 0l-7 7m7-7H3" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Bottom Split Section: Chart & Subject Score List -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 pt-2">
            <!-- Left: Perkembangan Nilai Line Chart -->
            <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-100 p-5 md:p-6 flex flex-col justify-between shadow-[0_2px_8px_rgba(0,0,0,0.02)]" data-purpose="progression-chart">
                <!-- Chart Header & Legends -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-50 gap-3">
                    <h3 class="text-base font-bold text-slate-900">Perkembangan Nilai</h3>
                    <div class="flex items-center gap-4 text-xs font-semibold">
                        <div class="flex items-center gap-1.5 text-blue-600">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                            <span>Tugas</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-purple-600">
                            <span class="w-2.5 h-2.5 rounded-full bg-purple-600"></span>
                            <span>Kuiz</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-emerald-500">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <span>Ujian</span>
                        </div>
                    </div>
                </div>

                <!-- Line Chart SVG Graphic -->
                <div class="w-full pt-4 pb-2">
                    <svg class="w-full h-56 overflow-visible text-xs select-none" viewBox="0 0 540 220">
                        <!-- Horizontal Grid Lines -->
                        <g stroke="#F1F5F9" stroke-width="1">
                            <line x1="40" x2="520" y1="20" y2="20"></line>
                            <line x1="40" x2="520" y1="55" y2="55"></line>
                            <line x1="40" x2="520" y1="90" y2="90"></line>
                            <line x1="40" x2="520" y1="125" y2="125"></line>
                            <line x1="40" x2="520" y1="160" y2="160"></line>
                            <line x1="40" x2="520" y1="195" y2="195"></line>
                        </g>
                        <!-- Y-Axis Labels -->
                        <g fill="#94A3B8" font-size="11" text-anchor="end">
                            <text x="32" y="24">100</text>
                            <text x="32" y="59">80</text>
                            <text x="32" y="94">60</text>
                            <text x="32" y="129">40</text>
                            <text x="32" y="164">20</text>
                            <text x="32" y="198">0</text>
                        </g>
                        <!-- Vertical Dash Guides -->
                        <g stroke="#E2E8F0" stroke-dasharray="3,3" stroke-width="1">
                            <line x1="80" x2="80" y1="20" y2="195"></line>
                            <line x1="180" x2="180" y1="20" y2="195"></line>
                            <line x1="280" x2="280" y1="20" y2="195"></line>
                            <line x1="380" x2="380" y1="20" y2="195"></line>
                            <line x1="480" x2="480" y1="20" y2="195"></line>
                        </g>
                        <!-- Data Line 1: Tugas (Blue) -->
                        <polyline fill="none" points="{{ $perkembanganNilai['tugas']['points'] }}" stroke="#2563EB" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"></polyline>
                        <!-- Data Points: Tugas -->
                        <g fill="#2563EB">
                            @foreach($perkembanganNilai['tugas']['coords'] as $pt)
                                <circle cx="{{ $pt[0] }}" cy="{{ $pt[1] }}" r="4" stroke="#ffffff" stroke-width="2"></circle>
                            @endforeach
                        </g>
                        <!-- Data Line 2: Kuiz (Purple) -->
                        <polyline fill="none" points="{{ $perkembanganNilai['kuiz']['points'] }}" stroke="#8B5CF6" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"></polyline>
                        <!-- Data Points: Kuiz -->
                        <g fill="#8B5CF6">
                            @foreach($perkembanganNilai['kuiz']['coords'] as $pt)
                                <circle cx="{{ $pt[0] }}" cy="{{ $pt[1] }}" r="4" stroke="#ffffff" stroke-width="2"></circle>
                            @endforeach
                        </g>
                        <!-- Data Line 3: Ujian (Green) -->
                        <polyline fill="none" points="{{ $perkembanganNilai['ujian']['points'] }}" stroke="#10B981" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"></polyline>
                        <!-- Data Points: Ujian -->
                        <g fill="#10B981">
                            @foreach($perkembanganNilai['ujian']['coords'] as $pt)
                                <circle cx="{{ $pt[0] }}" cy="{{ $pt[1] }}" r="4" stroke="#ffffff" stroke-width="2"></circle>
                            @endforeach
                        </g>
                        <!-- X-Axis Labels -->
                        <g fill="#64748B" font-size="11" font-weight="500" text-anchor="middle">
                            @php $xCoords = [80, 180, 280, 380, 480]; @endphp
                            @foreach($perkembanganNilai['bulan'] as $i => $bulan)
                                <text x="{{ $xCoords[$i] ?? 80 }}" y="215">{{ $bulan }}</text>
                            @endforeach
                        </g>
                    </svg>
                </div>
            </div>

            <!-- Right: Nilai per Mata Pelajaran List -->
            <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-100 p-5 md:p-6 flex flex-col justify-between shadow-[0_2px_8px_rgba(0,0,0,0.02)]" data-purpose="subject-scores-list">
                <div class="flex items-center justify-between pb-3 border-b border-slate-50">
                    <h3 class="text-base font-bold text-slate-900">Nilai per Mata Pelajaran</h3>
                    <span class="text-xs text-slate-400 font-medium">Semester {{ $semester ?? 'Ganjil' }}</span>
                </div>
                <div class="divide-y divide-slate-50 mt-1">
                    @foreach($listNilaiMapel as $mapelNilai)
                        @if(!empty($mapelNilai['id']))
                            <a href="{{ route('siswa.materi.show', $mapelNilai['id']) }}" class="py-3 flex items-center justify-between hover:bg-slate-50 rounded-xl px-2.5 -mx-2.5 transition-colors group">
                        @else
                            <div class="py-3 flex items-center justify-between hover:bg-slate-50 rounded-xl px-2.5 -mx-2.5 transition-colors cursor-pointer group">
                        @endif
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-xs text-white shadow-sm shrink-0"
                                     style="background-color: {{ $mapelNilai['color'] }};">
                                    @if($mapelNilai['type'] === 'math')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <rect height="18" rx="2" width="14" x="5" y="3"></rect>
                                            <line x1="8" x2="16" y1="7" y2="7"></line>
                                            <line x1="8" x2="16" y1="11" y2="11"></line>
                                            <line x1="8" x2="16" y1="15" y2="15"></line>
                                        </svg>
                                    @elseif($mapelNilai['type'] === 'book')
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M18 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 4h5v8l-2.5-1.5L6 12V4z"></path>
                                        </svg>
                                    @elseif($mapelNilai['type'] === 'letters')
                                        Aa
                                    @elseif($mapelNilai['type'] === 'beaker')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </svg>
                                    @elseif($mapelNilai['type'] === 'globe')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </svg>
                                    @elseif($mapelNilai['type'] === 'group')
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"></path>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 3L1 9L12 15L21 10.09V17H23V9M5 13.18V17.18C5 19.94 8.13 22 12 22C15.87 22 19 19.94 19 17.18V13.18L12 17L5 13.18Z"></path>
                                        </svg>
                                    @endif
                                </div>
                                <span class="text-sm font-semibold text-slate-800 group-hover:text-blue-600 transition-colors">{{ $mapelNilai['nama'] }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-bold text-slate-700">{{ number_format($mapelNilai['nilai'], 1, ',', '.') }}</span>
                                <svg class="w-4 h-4 text-slate-400 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </div>
                        @if(!empty($mapelNilai['id']))
                            </a>
                        @else
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    <!-- END: DetailedStatisticsSection -->

    <!-- BEGIN: QuizTabSection (Tab Kuiz) -->
    <section x-show="currentTab === 'kuiz'" style="display: none;" class="space-y-5">
        <div>
            <h2 class="text-2xl font-extrabold text-[#0F2851] tracking-tight">Kuiz Interaktif</h2>
            <p class="text-sm text-slate-500 mt-1">Uji pemahaman dan asah kemampuan belajarmu dengan kuiz interaktif.</p>
        </div>

        @if(isset($quizzes) && $quizzes->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($quizzes as $quiz)
                    <div class="bg-white rounded-[22px] p-5 border border-slate-100 shadow-[0_2px_8px_rgba(0,0,0,0.03)] flex flex-col justify-between hover:shadow-md transition">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="px-2.5 py-1 bg-blue-50 text-blue-600 font-bold text-xs rounded-lg">
                                    {{ $quiz->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? 'Umum' }}
                                </span>
                                <span class="px-2.5 py-1 bg-amber-50 text-amber-600 font-semibold text-xs rounded-lg">
                                    Level {{ $quiz->level ?? '1' }}
                                </span>
                            </div>
                            <h3 class="text-base font-bold text-slate-800 mb-2">{{ $quiz->judul }}</h3>
                            <p class="text-xs text-slate-400">Tingkat Kesulitan: <strong class="text-slate-600 capitalize">{{ $quiz->kesulitan ?? 'Sedang' }}</strong></p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-xs text-slate-400">{{ $quiz->created_at ? $quiz->created_at->diffForHumans() : 'Aktif' }}</span>
                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-600">
                                Mulai Kuiz
                                <i class="fa-solid fa-play text-[10px]"></i>
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="py-12 text-center bg-white rounded-2xl border border-dashed border-slate-200 text-slate-400">
                <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <p class="text-sm font-semibold text-slate-700">Belum ada kuiz aktif saat ini</p>
                <p class="text-xs text-slate-400 mt-1">Kuiz dari gurumu akan muncul di sini setelah dibuka.</p>
            </div>
        @endif
    </section>
    <!-- END: QuizTabSection -->

</div>
@endsection