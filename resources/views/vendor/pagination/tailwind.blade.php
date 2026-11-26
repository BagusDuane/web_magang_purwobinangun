@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi Halaman" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 w-full relative z-10">
        <div class="text-xs text-slate-600">
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
            <span class="text-slate-400 ml-1">(Hal. {{ $paginator->currentPage() }}/{{ $paginator->lastPage() }})</span>
        </div>

        <div class="flex flex-wrap items-center gap-1.5">
            {{-- Tombol Sebelumnya --}}
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" title="Sudah berada di halaman pertama" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-400 bg-slate-100 border border-slate-200 rounded-lg cursor-not-allowed select-none">
                    <i class="fa-solid fa-chevron-left text-[10px]"></i>
                    <span>{!! __('pagination.previous') !!}</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-slate-700 bg-white hover:bg-teal-50 hover:text-[#0b7c89] hover:border-[#0b7c89] border border-slate-300 rounded-lg shadow-2xs transition cursor-pointer">
                    <i class="fa-solid fa-chevron-left text-[10px]"></i>
                    <span>{!! __('pagination.previous') !!}</span>
                </a>
            @endif

            {{-- Nomor Halaman --}}
            <div class="hidden sm:inline-flex items-center gap-1">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span aria-disabled="true" class="inline-flex items-center px-2.5 py-1.5 text-xs font-semibold text-slate-400 bg-white border border-slate-200 rounded-lg select-none">
                            {{ $element }}
                        </span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-white bg-[#0b7c89] border border-[#0b7c89] rounded-lg shadow-2xs select-none">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}" aria-label="Ke halaman {{ $page }}" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white hover:bg-teal-50 hover:text-[#0b7c89] hover:border-[#0b7c89] border border-slate-300 rounded-lg transition cursor-pointer">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            {{-- Tombol Selanjutnya --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-slate-700 bg-white hover:bg-teal-50 hover:text-[#0b7c89] hover:border-[#0b7c89] border border-slate-300 rounded-lg shadow-2xs transition cursor-pointer">
                    <span>{!! __('pagination.next') !!}</span>
                    <i class="fa-solid fa-chevron-right text-[10px]"></i>
                </a>
            @else
                <span aria-disabled="true" title="Sudah berada di halaman terakhir" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-400 bg-slate-100 border border-slate-200 rounded-lg cursor-not-allowed select-none">
                    <span>{!! __('pagination.next') !!}</span>
                    <i class="fa-solid fa-chevron-right text-[10px]"></i>
                </span>
            @endif
        </div>
    </nav>
@endif
