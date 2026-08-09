@props(['paginator', 'label' => 'data'])

@if ($paginator->total() > 0)
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 px-3 py-3">
        <small class="text-muted">
            Menampilkan <strong>{{ $paginator->firstItem() }}</strong>–<strong>{{ $paginator->lastItem() }}</strong>
            dari <strong>{{ $paginator->total() }}</strong> {{ $label }}
        </small>
        <div>{{ $paginator->onEachSide(1)->links() }}</div>
    </div>
@else
    <div class="text-center text-muted py-5">
        <i class="fas fa-inbox fa-2x d-block mb-2 opacity-50"></i>
        Tidak ada {{ $label }} yang cocok dengan filter.
    </div>
@endif
