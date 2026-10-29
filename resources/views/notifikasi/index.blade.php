@extends(auth()->user()->isSiswa() ? 'layouts.siswa' : 'layouts.app')

@section('title', 'Pusat Notifikasi - LearnPoint')

@section('content')
<div class="space-y-6 max-w-6xl mx-auto">

    @if(auth()->user()->isSiswa())
        <!-- Back Navigation for Siswa -->
        <div>
            <a href="{{ route('siswa.dashboard') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-700 transition">
                <svg class="w-4 h-4 stroke-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <span>Kembali ke Beranda</span>
            </a>
        </div>
    @endif

    <!-- Alert Flash Message -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <!-- BEGIN: Hero Banner -->
    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-blue-700 via-blue-600 to-indigo-700 text-white p-7 sm:p-9 shadow-md">
        <!-- Ambient Background Circles -->
        <div class="absolute -right-10 -top-12 w-64 h-64 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
        <div class="absolute right-52 -bottom-10 w-48 h-48 rounded-full bg-indigo-400/20 blur-xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                <!-- Icon Box -->
                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-white/15 backdrop-blur-md text-white flex items-center justify-center shadow-lg border border-white/20 shrink-0">
                    <svg class="w-7 h-7 sm:w-8 sm:h-8 text-white" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                    </svg>
                </div>

                <!-- Textual Header -->
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Pusat Notifikasi Interaktif</h1>
                    <p class="text-sm text-blue-100 font-normal mt-1 max-w-xl">
                        @if(auth()->user()->isGuru())
                            Pantau kiriman tugas siswa, peringatan pengumpulan terlambat, serta aktivitas evaluasi kelas secara langsung.
                        @else
                            Pantau tugas baru, modul ajar, rilis nilai evaluasi, dan pengumuman sekolah secara terpadu.
                        @endif
                    </p>

                    <div class="flex items-center gap-2.5 mt-3 flex-wrap">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-white/20 text-white backdrop-blur-sm border border-white/20">
                            {{ $totalCount ?? 0 }} Total Aktivitas
                        </span>
                        @if(($unreadCount ?? 0) > 0)
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-amber-400 text-amber-950 shadow-xs">
                                <span class="w-2 h-2 rounded-full bg-amber-900 animate-ping"></span>
                                {{ $unreadCount }} Belum Dibaca
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-emerald-400/20 text-emerald-100 border border-emerald-300/30">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Semua Sudah Dibaca
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Top Header Actions -->
            <div class="flex items-center gap-2.5 shrink-0 self-start md:self-center">
                @if(($unreadCount ?? 0) > 0)
                    <form action="{{ route('notifikasi.mark-all-read') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-white/15 hover:bg-white/25 text-white text-xs font-bold backdrop-blur-sm border border-white/20 transition-all flex items-center gap-1.5 shadow-xs cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Tandai Semua Dibaca</span>
                        </button>
                    </form>
                @endif

                @if(($totalCount ?? 0) > 0)
                    <form action="{{ route('notifikasi.clear-all') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus seluruh riwayat notifikasi?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3.5 py-2.5 rounded-xl bg-rose-500/80 hover:bg-rose-600 text-white text-xs font-bold transition-all flex items-center gap-1.5 shadow-xs cursor-pointer" title="Bersihkan Semua Notifikasi">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            <span>Bersihkan Semua</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </section>
    <!-- END: Hero Banner -->

    <!-- Filter & Search Controls -->
    <div class="bg-white rounded-2xl border border-gray-200 p-4 sm:p-5 shadow-sm space-y-4">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">

            <!-- Filter Tabs -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
                <a href="{{ route('notifikasi.index', ['filter' => 'semua', 'search' => request('search')]) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shrink-0 {{ ($filter ?? 'semua') === 'semua' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200/80' }}">
                    <span>Semua</span>
                    <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ ($filter ?? 'semua') === 'semua' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $totalCount ?? 0 }}</span>
                </a>

                <a href="{{ route('notifikasi.index', ['filter' => 'belum_dibaca', 'search' => request('search')]) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shrink-0 {{ ($filter ?? '') === 'belum_dibaca' ? 'bg-amber-600 text-white shadow-xs' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200/80' }}">
                    <span>Belum Dibaca</span>
                    <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ ($filter ?? '') === 'belum_dibaca' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800' }}">{{ $unreadCount ?? 0 }}</span>
                </a>

                <a href="{{ route('notifikasi.index', ['filter' => 'tugas', 'search' => request('search')]) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shrink-0 {{ ($filter ?? '') === 'tugas' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200/80' }}">
                    <span>Tugas</span>
                    <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ ($filter ?? '') === 'tugas' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $tugasCount ?? 0 }}</span>
                </a>

                <a href="{{ route('notifikasi.index', ['filter' => 'evaluasi', 'search' => request('search')]) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shrink-0 {{ ($filter ?? '') === 'evaluasi' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200/80' }}">
                    <span>Kuis &amp; Ujian</span>
                    <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ ($filter ?? '') === 'evaluasi' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $evaluasiCount ?? 0 }}</span>
                </a>
            </div>

            <!-- Search Input -->
            <form method="GET" action="{{ route('notifikasi.index') }}" class="flex items-center gap-2 w-full lg:w-72">
                <input type="hidden" name="filter" value="{{ $filter ?? 'semua' }}">
                <div class="relative w-full">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Cari notifikasi..."
                           class="w-full pl-9 pr-4 py-2 rounded-xl border border-gray-200 bg-gray-50/70 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                </div>
                <button type="submit" class="px-3 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shrink-0 transition-colors">
                    Cari
                </button>
                @if(request()->filled('search'))
                    <a href="{{ route('notifikasi.index', ['filter' => $filter]) }}" class="p-2 rounded-xl border border-gray-200 text-gray-400 hover:text-gray-700 hover:bg-gray-50 transition-colors" title="Reset Pencarian">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </form>

        </div>
    </div>

    <!-- Notifications List Container -->
    <div class="bg-white rounded-3xl border border-gray-200 shadow-sm overflow-hidden">
        @if($notifikasis->count() > 0)
            <div class="divide-y divide-gray-100">
                @foreach($notifikasis as $notif)
                    @php
                        $isUnread = !$notif->is_read;
                        $tipe = $notif->tipe ?? 'info';

                        // Styling and icons based on type
                        $typeConfig = match($tipe) {
                            'tugas_terlambat' => [
                                'iconBg' => 'bg-rose-50 text-rose-600 border-rose-200',
                                'badge'  => 'bg-rose-50 text-rose-700 border-rose-200',
                                'label'  => 'Tugas Terlambat',
                                'icon'   => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                            ],
                            'tugas_dikumpulkan' => [
                                'iconBg' => 'bg-amber-50 text-amber-600 border-amber-200',
                                'badge'  => 'bg-amber-50 text-amber-700 border-amber-200',
                                'label'  => 'Pengumpulan Tugas',
                                'icon'   => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
                            ],
                            'kuis_selesai' => [
                                'iconBg' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
                                'badge'  => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'label'  => 'Kuis Selesai',
                                'icon'   => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z',
                            ],
                            'ujian_baru', 'ujian_selesai' => [
                                'iconBg' => 'bg-indigo-50 text-indigo-600 border-indigo-200',
                                'badge'  => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                'label'  => 'Ujian & Evaluasi',
                                'icon'   => 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
                            ],
                            'nilai_tugas' => [
                                'iconBg' => 'bg-blue-50 text-blue-600 border-blue-200',
                                'badge'  => 'bg-blue-50 text-blue-700 border-blue-200',
                                'label'  => 'Nilai Diberikan',
                                'icon'   => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z',
                            ],
                            'materi_baru' => [
                                'iconBg' => 'bg-teal-50 text-teal-600 border-teal-200',
                                'badge'  => 'bg-teal-50 text-teal-700 border-teal-200',
                                'label'  => 'Materi Pembelajaran',
                                'icon'   => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
                            ],
                            default => [
                                'iconBg' => 'bg-blue-50 text-blue-600 border-blue-100',
                                'badge'  => 'bg-blue-50 text-blue-700 border-blue-200',
                                'label'  => 'Informasi',
                                'icon'   => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                            ],
                        };
                    @endphp

                    <div class="p-5 sm:p-6 transition-all flex flex-col sm:flex-row sm:items-start justify-between gap-4 {{ $isUnread ? 'bg-blue-50/25 border-l-4 border-l-blue-600' : 'hover:bg-slate-50/60' }} group">
                        
                        <div class="flex items-start gap-4 min-w-0 flex-1">
                            <!-- Type Icon -->
                            <div class="w-11 h-11 rounded-2xl {{ $typeConfig['iconBg'] }} border flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition-transform mt-0.5">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $typeConfig['icon'] }}"/>
                                </svg>
                            </div>

                            <!-- Notification Text -->
                            <div class="space-y-1 min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $typeConfig['badge'] }}">
                                        {{ $typeConfig['label'] }}
                                    </span>

                                    @if($isUnread)
                                        <span class="inline-flex items-center gap-1 text-[10px] font-extrabold bg-blue-600 text-white px-2 py-0.5 rounded-full">
                                            <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                                            Baru
                                        </span>
                                    @endif

                                    <span class="text-xs text-gray-400 font-medium">
                                        {{ $notif->created_at ? $notif->created_at->diffForHumans() : 'Baru saja' }}
                                    </span>
                                </div>

                                <h3 class="text-sm sm:text-base font-bold text-gray-900 group-hover:text-blue-600 transition-colors">
                                    @if($notif->url)
                                        <a href="{{ route('notifikasi.read', $notif->id) }}" class="hover:underline">
                                            {{ $notif->judul }}
                                        </a>
                                    @else
                                        {{ $notif->judul }}
                                    @endif
                                </h3>

                                <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">
                                    {{ $notif->pesan }}
                                </p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-2 shrink-0 sm:self-center pl-15 sm:pl-0 pt-2 sm:pt-0">
                            @if($notif->url)
                                <a href="{{ route('notifikasi.read', $notif->id) }}"
                                   class="px-3.5 py-1.5 rounded-xl bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700 transition shadow-xs inline-flex items-center gap-1">
                                    <span>Buka Aksi</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            @endif

                            <!-- Toggle Read Form -->
                            <form action="{{ route('notifikasi.toggle', $notif->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="p-2 rounded-xl border border-gray-200 text-gray-500 hover:text-blue-600 hover:bg-blue-50 transition-colors cursor-pointer"
                                        title="{{ $isUnread ? 'Tandai sudah dibaca' : 'Tandai belum dibaca' }}">
                                    @if($isUnread)
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    @endif
                                </button>
                            </form>

                            <!-- Delete Form -->
                            <form action="{{ route('notifikasi.destroy', $notif->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="p-2 rounded-xl border border-gray-200 text-gray-400 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer"
                                        title="Hapus notifikasi ini">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>

                    </div>
                @endforeach
            </div>

            <!-- Pagination Links -->
            @if($notifikasis->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $notifikasis->links() }}
                </div>
            @endif

        @else
            <!-- Empty State -->
            <div class="py-16 px-6 text-center max-w-md mx-auto space-y-4">
                <div class="w-20 h-20 rounded-3xl bg-blue-50 text-blue-500 flex items-center justify-center mx-auto border border-blue-100 shadow-sm">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                    </svg>
                </div>

                <div>
                    <h3 class="text-base font-bold text-gray-900">Tidak Ada Notifikasi Ditemukan</h3>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1 leading-relaxed">
                        @if(($filter ?? '') === 'belum_dibaca')
                            Hebat! Semua notifikasi pada kategori ini sudah Anda baca dan tinjau.
                        @else
                            Saat ini belum ada notifikasi atau pemberitahuan baru yang masuk ke akun Anda.
                        @endif
                    </p>
                </div>

                @if(request()->filled('search') || ($filter ?? 'semua') !== 'semua')
                    <div class="pt-2">
                        <a href="{{ route('notifikasi.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs inline-flex items-center gap-1.5 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Reset Filter Notifikasi</span>
                        </a>
                    </div>
                @endif
            </div>
        @endif
    </div>

</div>
@endsection
