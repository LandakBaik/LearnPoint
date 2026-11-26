@php
    $notifUser = auth()->user();
    $unreadDropdownCount = $notifUser?->unreadNotifikasiCount() ?? 0;
    $recentNotifs = $notifUser ? $notifUser->headerNotifikasis(6) : collect();
    $recentUnreadCount = $recentNotifs->where('is_read', false)->count();
    $isSiswaLayout = ($variant ?? 'default') === 'siswa';
@endphp

<div class="relative" x-data="{ notifOpen: false, tab: 'all' }" @click.outside="notifOpen = false" @keydown.escape.window="notifOpen = false">
    <!-- Bell Trigger Button -->
    <button
        type="button"
        @click="notifOpen = !notifOpen"
        aria-label="Notifikasi"
        title="Pusat Notifikasi"
        :class="notifOpen ? 'bg-blue-50 text-blue-600 ring-2 ring-blue-100' : 'text-slate-500 hover:text-blue-600 hover:bg-blue-50/80'"
        class="relative cursor-pointer focus:outline-none rounded-xl transition-all duration-200 {{ $isSiswaLayout ? 'p-2' : 'p-2' }}"
    >
        <svg class="{{ $isSiswaLayout ? 'w-6 h-6' : 'w-5 h-5' }} transition-transform duration-200" :class="notifOpen ? 'scale-105' : ''" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
        </svg>

        @if($unreadDropdownCount > 0)
            <span class="absolute {{ $isSiswaLayout ? '-top-0.5 -right-0.5' : 'top-1 right-1' }} flex h-4 min-w-4 px-1 items-center justify-center rounded-full bg-rose-500 text-[10px] font-extrabold text-white ring-2 ring-white leading-none">
                {{ $unreadDropdownCount > 99 ? '99+' : $unreadDropdownCount }}
            </span>
        @endif
    </button>

    <!-- Dropdown Panel -->
    <div
        x-show="notifOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-2 scale-95"
        class="absolute right-0 mt-3 w-[340px] sm:w-[400px] bg-white rounded-3xl shadow-2xl shadow-slate-900/15 border border-slate-200/80 ring-1 ring-black/5 z-50 overflow-hidden origin-top-right"
        style="display: none;"
    >
        <!-- Dropdown Header -->
        <div class="px-4 py-3 bg-slate-50/80 border-b border-slate-100 flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <h3 class="text-xs font-extrabold text-slate-800">Notifikasi Terbaru</h3>
                @if($unreadDropdownCount > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700">
                        {{ $unreadDropdownCount > 99 ? '99+' : $unreadDropdownCount }} baru
                    </span>
                @endif
            </div>
            @if($unreadDropdownCount > 0)
                <form action="{{ route('notifikasi.mark-all-read') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-[11px] font-bold text-blue-600 hover:text-blue-700 hover:underline cursor-pointer">
                        Tandai semua dibaca
                    </button>
                </form>
            @endif
        </div>

        <!-- Quick Filter Tabs -->
        @if($recentNotifs->count() > 0)
            <div class="px-4 py-2.5 bg-slate-50/90 border-b border-slate-100 flex items-center gap-1.5">
                <button
                    type="button"
                    @click="tab = 'all'"
                    :class="tab === 'all' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80'"
                    class="px-3 py-1 rounded-lg text-[11px] font-bold transition-all cursor-pointer inline-flex items-center gap-1.5"
                >
                    <span>Semua</span>
                    <span :class="tab === 'all' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'" class="px-1.5 py-0.2 rounded text-[10px]">
                        {{ $recentNotifs->count() }}
                    </span>
                </button>
                <button
                    type="button"
                    @click="tab = 'unread'"
                    :class="tab === 'unread' ? 'bg-amber-500 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80'"
                    class="px-3 py-1 rounded-lg text-[11px] font-bold transition-all cursor-pointer inline-flex items-center gap-1.5"
                >
                    <span>Belum Dibaca</span>
                    <span :class="tab === 'unread' ? 'bg-white/20 text-white' : 'bg-amber-50 text-amber-700'" class="px-1.5 py-0.2 rounded text-[10px]">
                        {{ $recentUnreadCount }}
                    </span>
                </button>
            </div>
        @endif

        <!-- Notification Items List -->
        <div class="max-h-[340px] overflow-y-auto divide-y divide-slate-100/90">
            @forelse($recentNotifs as $item)
                @php
                    $itemUnread = !$item->is_read;
                    $tipe = $item->tipe ?? 'info';

                    $cfg = match($tipe) {
                        'tugas_baru' => [
                            'iconBg' => 'bg-sky-50 text-sky-600 border-sky-200/80',
                            'badge'  => 'bg-sky-50 text-sky-700',
                            'label'  => 'Tugas Baru',
                            'icon'   => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                        ],
                        'tugas_terlambat' => [
                            'iconBg' => 'bg-rose-50 text-rose-600 border-rose-200/80',
                            'badge'  => 'bg-rose-50 text-rose-700',
                            'label'  => 'Terlambat',
                            'icon'   => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                        ],
                        'tugas_dikumpulkan' => [
                            'iconBg' => 'bg-amber-50 text-amber-600 border-amber-200/80',
                            'badge'  => 'bg-amber-50 text-amber-700',
                            'label'  => 'Tugas Masuk',
                            'icon'   => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2m-6 9l2 2 4-4',
                        ],
                        'kuis_baru' => [
                            'iconBg' => 'bg-violet-50 text-violet-600 border-violet-200/80',
                            'badge'  => 'bg-violet-50 text-violet-700',
                            'label'  => 'Kuis Baru',
                            'icon'   => 'M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                        ],
                        'kuis_selesai' => [
                            'iconBg' => 'bg-emerald-50 text-emerald-600 border-emerald-200/80',
                            'badge'  => 'bg-emerald-50 text-emerald-700',
                            'label'  => 'Kuis Selesai',
                            'icon'   => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                        ],
                        'ujian_baru', 'ujian_selesai' => [
                            'iconBg' => 'bg-indigo-50 text-indigo-600 border-indigo-200/80',
                            'badge'  => 'bg-indigo-50 text-indigo-700',
                            'label'  => $tipe === 'ujian_selesai' ? 'Ujian Selesai' : 'Ujian Baru',
                            'icon'   => 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
                        ],
                        'ujian_remidi' => [
                            'iconBg' => 'bg-orange-50 text-orange-600 border-orange-200/80',
                            'badge'  => 'bg-orange-50 text-orange-700',
                            'label'  => 'Remidi / Susulan',
                            'icon'   => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
                        ],
                        'nilai_tugas', 'nilai_ujian' => [
                            'iconBg' => 'bg-blue-50 text-blue-600 border-blue-200/80',
                            'badge'  => 'bg-blue-50 text-blue-700',
                            'label'  => $tipe === 'nilai_ujian' ? 'Nilai Ujian' : 'Nilai Tugas',
                            'icon'   => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                        ],
                        'materi_baru' => [
                            'iconBg' => 'bg-teal-50 text-teal-600 border-teal-200/80',
                            'badge'  => 'bg-teal-50 text-teal-700',
                            'label'  => 'Materi Baru',
                            'icon'   => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
                        ],
                        'pengingat_deadline', 'pengingat_ujian', 'pengingat_koreksi' => [
                            'iconBg' => 'bg-purple-50 text-purple-600 border-purple-200/80',
                            'badge'  => 'bg-purple-50 text-purple-700',
                            'label'  => 'Pengingat',
                            'icon'   => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                        ],
                        default => [
                            'iconBg' => 'bg-slate-50 text-slate-600 border-slate-200/80',
                            'badge'  => 'bg-slate-100 text-slate-700',
                            'label'  => 'Informasi',
                            'icon'   => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                        ],
                    };
                @endphp

                <a
                    href="{{ $item->url ? route('notifikasi.read', $item->id) : route('notifikasi.index') }}"
                    x-show="tab === 'all' || {{ $itemUnread ? 'true' : 'false' }}"
                    class="group flex items-start gap-3 px-4 py-3.5 transition-all duration-150 {{ $itemUnread ? 'bg-blue-50/35 hover:bg-blue-50/70 border-l-[3px] border-l-blue-600' : 'hover:bg-slate-50/90 border-l-[3px] border-l-transparent' }}"
                >
                    <!-- Type Icon -->
                    <div class="w-9 h-9 rounded-xl {{ $cfg['iconBg'] }} border flex items-center justify-center shrink-0 mt-0.5 group-hover:scale-105 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $cfg['icon'] }}"/>
                        </svg>
                    </div>

                    <!-- Content -->
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-2 mb-0.5">
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-bold {{ $cfg['badge'] }}">
                                {{ $cfg['label'] }}
                            </span>
                            <span class="text-[10px] text-slate-400 font-medium shrink-0">
                                {{ $item->created_at ? $item->created_at->diffForHumans() : 'Baru saja' }}
                            </span>
                        </div>
                        <p class="text-xs font-bold text-slate-800 group-hover:text-blue-600 transition-colors line-clamp-1">
                            {{ $item->judul }}
                        </p>
                        <p class="text-[11px] text-slate-500 line-clamp-2 mt-0.5 leading-relaxed">
                            {{ $item->pesan }}
                        </p>
                    </div>

                    <!-- Unread Dot / Hover Chevron -->
                    <div class="flex flex-col items-center justify-center self-center shrink-0 pl-0.5">
                        @if($itemUnread)
                            <span class="w-2 h-2 rounded-full bg-blue-600 ring-4 ring-blue-100"></span>
                        @else
                            <svg class="w-3.5 h-3.5 text-slate-300 group-hover:text-blue-500 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        @endif
                    </div>
                </a>
            @empty
                <div class="px-6 py-10 text-center">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-500 border border-blue-100 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                        </svg>
                    </div>
                    <p class="text-xs font-bold text-slate-700">Belum Ada Notifikasi</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Pemberitahuan tugas, materi, dan evaluasi akan muncul di sini.</p>
                </div>
            @endforelse

            @if($recentNotifs->count() > 0 && $recentUnreadCount === 0)
                <div x-show="tab === 'unread'" class="px-6 py-9 text-center" style="display: none;">
                    <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-500 border border-emerald-100 flex items-center justify-center mx-auto mb-2.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <p class="text-xs font-bold text-slate-700">Semua Sudah Dibaca!</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Tidak ada notifikasi baru yang belum ditinjau.</p>
                </div>
            @endif
        </div>

        <!-- Footer CTA -->
        <div class="p-3 bg-slate-50/90 border-t border-slate-100">
            <a href="{{ route('notifikasi.index') }}"
               class="w-full py-2.5 px-4 rounded-xl bg-white hover:bg-blue-600 text-blue-600 hover:text-white border border-slate-200/80 hover:border-blue-600 text-xs font-bold transition-all duration-150 flex items-center justify-center gap-1.5 shadow-2xs group">
                <span>Buka Pusat Notifikasi</span>
                <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </div>
</div>
