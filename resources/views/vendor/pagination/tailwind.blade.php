@if ($paginator->hasPages())
    <nav role="navigation" aria-label="التنقل بين الصفحات" class="pt-4">
        
        <!-- Mobile Pagination (Simple Next / Prev) -->
        <div class="flex gap-3 items-center justify-between sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center justify-center px-4 py-2.5 text-xs font-bold text-neutral-400 bg-neutral-100 border border-neutral-200 cursor-not-allowed rounded-lg">
                    السابق
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center justify-center px-4 py-2.5 text-xs font-bold text-[#08080a] bg-white border border-neutral-300 rounded-lg hover:border-[#CE1126] hover:text-[#CE1126] transition">
                    السابق
                </a>
            @endif

            <span class="text-xs font-bold text-neutral-600 font-mono">
                صفحة {{ $paginator->currentPage() }} من {{ $paginator->lastPage() }}
            </span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center justify-center px-4 py-2.5 text-xs font-bold text-white bg-[#08080a] border border-[#08080a] rounded-lg hover:bg-[#CE1126] hover:border-[#CE1126] transition">
                    التالي
                </a>
            @else
                <span class="inline-flex items-center justify-center px-4 py-2.5 text-xs font-bold text-neutral-400 bg-neutral-100 border border-neutral-200 cursor-not-allowed rounded-lg">
                    التالي
                </span>
            @endif
        </div>

        <!-- Desktop Pagination -->
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between bg-white px-5 py-3.5 rounded-xl border border-neutral-200">
            <div>
                <p class="text-xs text-neutral-600 font-semibold">
                    عرض
                    @if ($paginator->firstItem())
                        <span class="font-black text-[#08080a] font-mono">{{ $paginator->firstItem() }}</span>
                        إلى
                        <span class="font-black text-[#08080a] font-mono">{{ $paginator->lastItem() }}</span>
                    @else
                        <span class="font-black text-[#08080a] font-mono">{{ $paginator->count() }}</span>
                    @endif
                    من أصل
                    <span class="font-black text-[#CE1126] font-mono">{{ $paginator->total() }}</span>
                    سجل
                </p>
            </div>

            <div>
                <span class="inline-flex items-center gap-1.5">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="السابق">
                            <span class="inline-flex items-center justify-center w-9 h-9 text-xs font-bold text-neutral-300 bg-neutral-50 border border-neutral-200 cursor-not-allowed rounded-lg" aria-hidden="true">
                                ←
                            </span>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center justify-center w-9 h-9 text-xs font-bold text-[#08080a] bg-white border border-neutral-200 rounded-lg hover:border-[#CE1126] hover:text-[#CE1126] transition" aria-label="السابق">
                            →
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span aria-disabled="true">
                                <span class="inline-flex items-center justify-center w-9 h-9 text-xs font-bold text-neutral-400 bg-white">{{ $element }}</span>
                            </span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page">
                                        <span class="inline-flex items-center justify-center w-9 h-9 text-xs font-black font-mono text-white bg-[#08080a] border border-[#08080a] rounded-lg cursor-default">
                                            {{ $page }}
                                        </span>
                                    </span>
                                @else
                                    <a href="{{ $url }}" class="inline-flex items-center justify-center w-9 h-9 text-xs font-bold font-mono text-[#08080a] bg-white border border-neutral-200 rounded-lg hover:border-[#CE1126] hover:text-[#CE1126] transition" aria-label="الذهاب إلى صفحة {{ $page }}">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center justify-center w-9 h-9 text-xs font-bold text-[#08080a] bg-white border border-neutral-200 rounded-lg hover:border-[#CE1126] hover:text-[#CE1126] transition" aria-label="التالي">
                            ←
                        </a>
                    @else
                        <span aria-disabled="true" aria-label="التالي">
                            <span class="inline-flex items-center justify-center w-9 h-9 text-xs font-bold text-neutral-300 bg-neutral-50 border border-neutral-200 cursor-not-allowed rounded-lg" aria-hidden="true">
                                ←
                            </span>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif