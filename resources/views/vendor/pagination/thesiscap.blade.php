@if ($paginator->hasPages())
<nav class="pager" role="navigation" aria-label="Pagination">
    @if ($paginator->onFirstPage())
        <span class="btn sm ghost off">Previous</span>
    @else
        <a class="btn sm ghost" href="{{ $paginator->previousPageUrl() }}" rel="prev">Previous</a>
    @endif
    <span class="small muted">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>
    @if ($paginator->hasMorePages())
        <a class="btn sm ghost" href="{{ $paginator->nextPageUrl() }}" rel="next">Next</a>
    @else
        <span class="btn sm ghost off">Next</span>
    @endif
</nav>
@endif
