@if($paginator->hasPages())
    <nav class="v2-pagination" role="navigation" aria-label="{{ __('Pagination Navigation') }}">
        @if($paginator->onFirstPage())
            <span class="v2-pagination__control v2-pagination__control--disabled" aria-disabled="true">{{ __('pagination.previous') }}</span>
        @else
            <a class="v2-pagination__control" href="{{ $paginator->previousPageUrl() }}" rel="prev">{{ __('pagination.previous') }}</a>
        @endif

        <div class="v2-pagination__pages">
            @foreach($elements as $element)
                @if(is_string($element))
                    <span class="v2-pagination__ellipsis" aria-hidden="true">{{ $element }}</span>
                @endif

                @if(is_array($element))
                    @foreach($element as $page => $url)
                        @if($page === $paginator->currentPage())
                            <span class="v2-pagination__page v2-pagination__page--current" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="v2-pagination__page" href="{{ $url }}" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>

        @if($paginator->hasMorePages())
            <a class="v2-pagination__control" href="{{ $paginator->nextPageUrl() }}" rel="next">{{ __('pagination.next') }}</a>
        @else
            <span class="v2-pagination__control v2-pagination__control--disabled" aria-disabled="true">{{ __('pagination.next') }}</span>
        @endif
    </nav>
@endif
