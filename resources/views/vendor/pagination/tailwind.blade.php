@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex flex-col sm:flex-row items-center justify-between gap-3">
        {{-- Mobile View --}}
        <div class="flex items-center justify-between w-full sm:hidden gap-2">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-slate-400 bg-slate-50 border border-slate-200/80 rounded-xl cursor-not-allowed select-none">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    <span>Sebelumnya</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition-colors shadow-2xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    <span>Sebelumnya</span>
                </a>
            @endif

            <span class="text-xs font-semibold text-slate-500">
                Hal. {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
            </span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition-colors shadow-2xs">
                    <span>Berikutnya</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @else
                <span class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-slate-400 bg-slate-50 border border-slate-200/80 rounded-xl cursor-not-allowed select-none">
                    <span>Berikutnya</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </span>
            @endif
        </div>

        {{-- Desktop View --}}
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-xs text-slate-500 font-medium">
                    Menampilkan
                    @if ($paginator->firstItem())
                        <span class="font-bold text-slate-800">{{ $paginator->firstItem() }}</span>
                        sampai
                        <span class="font-bold text-slate-800">{{ $paginator->lastItem() }}</span>
                    @else
                        <span class="font-bold text-slate-800">{{ $paginator->count() }}</span>
                    @endif
                    dari
                    <span class="font-bold text-slate-800">{{ $paginator->total() }}</span>
                    data
                </p>
            </div>

            <div class="flex items-center gap-1.5">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <span aria-disabled="true" aria-label="Sebelumnya" class="inline-flex items-center justify-center w-9 h-9 text-slate-300 bg-slate-50 border border-slate-200/80 rounded-xl cursor-not-allowed select-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Sebelumnya" class="inline-flex items-center justify-center w-9 h-9 text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition-all shadow-2xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <span aria-disabled="true" class="inline-flex items-center justify-center min-w-[2.25rem] h-9 px-2 text-xs font-bold text-slate-400 select-none">
                            {{ $element }}
                        </span>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="inline-flex items-center justify-center min-w-[2.25rem] h-9 px-3 text-xs font-bold text-white bg-blue-600 border border-blue-600 rounded-xl shadow-sm shadow-blue-500/25 select-none">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}" aria-label="Halaman {{ $page }}" class="inline-flex items-center justify-center min-w-[2.25rem] h-9 px-3 text-xs font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition-all shadow-2xs">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Berikutnya" class="inline-flex items-center justify-center w-9 h-9 text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition-all shadow-2xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                @else
                    <span aria-disabled="true" aria-label="Berikutnya" class="inline-flex items-center justify-center w-9 h-9 text-slate-300 bg-slate-50 border border-slate-200/80 rounded-xl cursor-not-allowed select-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </span>
                @endif
            </div>
        </div>
    </nav>
@endif
