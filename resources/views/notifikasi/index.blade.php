@extends(auth()->user()->isSiswa() ? 'layouts.siswa' : 'layouts.app')

@section('title', 'Notifikasi - LearnPoint')

@section('content')
@if(auth()->user()->isSiswa())
    <div x-data="{ activeFilter: 'semua' }" class="space-y-6">

        <!-- Back Navigation -->
        <div>
            <a href="{{ route('siswa.dashboard') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-700 transition">
                <svg class="w-4 h-4 stroke-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <span>Kembali ke Beranda</span>
            </a>
        </div>

        <!-- BEGIN: NotificationHeroBanner -->
        <section class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#EBF4FF] via-[#EDF6FF] to-[#DCEEFE] p-7 md:p-9 border border-blue-100 shadow-sm">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex items-center gap-5">
                    <!-- Bell Icon Box -->
                    <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/25 border-2 border-white/50 shrink-0">
                        <svg class="w-7 h-7 sm:w-8 sm:h-8" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </div>

                    <!-- Title & Details -->
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F2851] tracking-tight">Pusat Notifikasi</h1>
                        <p class="text-sm text-slate-500 font-medium mt-1">Pantau pembaruan penting mengenai tugas, modul baru, dan pengumuman sekolah.</p>
                        
                        <div class="flex items-center gap-2 mt-3">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-100/80 text-blue-700 border border-blue-200">
                                {{ $notifikasis->count() }} Total Notifikasi
                            </span>
                            <span class="text-xs text-slate-400">• Diperbarui secara otomatis</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Decorative Illustration -->
            <div class="absolute right-4 -bottom-4 hidden lg:block opacity-35 pointer-events-none select-none">
                <svg class="w-44 h-44 text-blue-300" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                    <path d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"></path>
                </svg>
            </div>
        </section>
        <!-- END: NotificationHeroBanner -->

        <!-- Filter Tabs & Content Section -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-6">
            <!-- Filter Pills -->
            <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
                <button @click="activeFilter = 'semua'"
                        :class="activeFilter === 'semua' ? 'bg-[#eaf2ff] border border-blue-200 text-blue-600 font-bold shadow-2xs' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-100'"
                        class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition flex items-center gap-2">
                    <i class="fa-solid fa-list-ul text-xs"></i>
                    <span>Semua ({{ $notifikasis->count() }})</span>
                </button>
            </div>

            <!-- Notifications List Container -->
            <div class="space-y-3">
                @forelse($notifikasis as $notif)
                    <div class="group flex items-start gap-4 p-4 sm:p-5 rounded-2xl border border-slate-100 hover:border-blue-200 hover:bg-blue-50/20 transition-all">
                        <!-- Icon -->
                        <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100 group-hover:scale-105 transition-transform">
                            <i class="fa-solid fa-bell text-base"></i>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2 flex-wrap mb-1">
                                <h3 class="text-sm sm:text-base font-bold text-slate-800 group-hover:text-blue-600 transition-colors">
                                    {{ $notif->judul }}
                                </h3>
                                <span class="text-xs text-slate-400 font-medium">
                                    {{ $notif->created_at ? $notif->created_at->diffForHumans() : 'Baru saja' }}
                                </span>
                            </div>
                            <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">
                                {{ $notif->pesan }}
                            </p>
                        </div>
                    </div>
                @empty
                    <!-- Empty State -->
                    <div class="py-12 text-center max-w-md mx-auto space-y-4">
                        <div class="w-20 h-20 rounded-3xl bg-blue-50 text-blue-500 flex items-center justify-center mx-auto border border-blue-100 shadow-sm">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Belum Ada Notifikasi</h2>
                            <p class="text-xs sm:text-sm text-slate-400 mt-1 leading-relaxed">
                                Saat ini belum ada pengumuman atau tugas baru yang diterbitkan. Kami akan memberitahumu saat ada aktivitas terbaru!
                            </p>
                        </div>
                        <div class="pt-2 flex items-center justify-center gap-3">
                            <a href="{{ route('siswa.dashboard') }}" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs sm:text-sm transition shadow-sm inline-flex items-center gap-2">
                                <i class="fa-solid fa-book-open text-xs"></i>
                                Buka Mata Pelajaran
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
@else
    {{-- Non-Siswa Notifikasi (Guru, Admin & Operator) --}}
    <div class="space-y-6" x-data="{ activeTab: 'semua' }">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Notifikasi</h1>
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                    Pusat Aktivitas
                </span>
            </div>
            <p class="text-sm text-gray-500 mt-1">Pantau aktivitas terbaru terkait tugas, kelas, dan pembelajaran.</p>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 sm:p-6 space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 01-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <div class="text-sm font-bold text-gray-900">
                        {{ $notifikasis->count() }} notifikasi
                    </div>
                </div>
            </div>

            <div class="p-8 sm:p-12 text-center">
                @forelse($notifikasis as $notif)
                    <div class="text-left p-4 rounded-xl border border-gray-100 mb-2">
                        <h4 class="font-bold text-sm text-gray-800">{{ $notif->judul }}</h4>
                        <p class="text-xs text-gray-500 mt-1">{{ $notif->pesan }}</p>
                    </div>
                @empty
                    <div class="max-w-md mx-auto py-4 space-y-4">
                        <div class="w-16 h-16 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center mx-auto border border-blue-100 shadow-inner">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 01-6 0v-1m6 0H9"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Belum Ada Notifikasi</h3>
                            <p class="text-sm text-gray-500 mt-1">
                                Saat ini belum ada notifikasi atau aktivitas baru untuk akun Anda.
                            </p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endif
@endsection
