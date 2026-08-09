@props(['action', 'reset' => null])

{{-- Filter bar seragam untuk semua menu. Setiap <select> di dalamnya otomatis jadi
     custom/searchable dropdown karena Select2 di-init global di master.blade.php. --}}
<form method="GET" action="{{ $action }}" class="card filter-bar border-0 mb-3">
    <div class="card-body py-3">
        <div class="row g-3 align-items-end">
            {{ $slot }}

            <div class="col-12 col-md-auto ms-md-auto d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill">
                    <i class="fas fa-filter me-1"></i> Terapkan
                </button>
                <a href="{{ $reset ?? $action }}" class="btn btn-outline-secondary flex-fill">
                    <i class="fas fa-rotate-left me-1"></i> Reset
                </a>
            </div>
        </div>
    </div>
</form>
