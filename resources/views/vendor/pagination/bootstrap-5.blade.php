@if ($paginator->total() > 0)
    <nav class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 w-100" aria-label="Navigasi Halaman">
        <div class="small text-muted text-center text-md-start">
            Menampilkan <span class="fw-bold text-dark">{{ $paginator->firstItem() ?? 0 }}</span> sampai <span class="fw-bold text-dark">{{ $paginator->lastItem() ?? 0 }}</span> dari total <span class="fw-bold text-primary">{{ $paginator->total() }}</span> data
        </div>

        @if ($paginator->hasPages())
        <ul class="pagination pagination-sm m-0 gap-1 flex-wrap justify-content-center">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true" aria-label="Sebelumnya">
                    <span class="page-link" aria-hidden="true">&lsaquo; Prev</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Sebelumnya">&lsaquo; Prev</a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Selanjutnya">Next &rsaquo;</a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true" aria-label="Selanjutnya">
                    <span class="page-link" aria-hidden="true">Next &rsaquo;</span>
                </li>
            @endif
        </ul>
        @endif
    </nav>
@endif
