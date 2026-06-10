<form method="GET" action="{{ $action ?? url()->current() }}" class="master-table-toolbar d-flex flex-wrap justify-content-between align-items-end gap-3 mb-3">
    @foreach (($hidden ?? []) as $name => $value)
        @if (filled($value))
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endif
    @endforeach

    <div class="d-flex align-items-center gap-2">
        <span class="text-muted small">Tampilkan</span>
        <select name="per_page" class="form-select form-select-sm master-per-page" onchange="this.form.submit()">
            @foreach ([10, 25, 50, 100] as $option)
                <option value="{{ $option }}" @selected((int) request('per_page', 10) === $option)>{{ $option }}</option>
            @endforeach
        </select>
        <span class="text-muted small">data</span>
    </div>

    <div class="d-flex align-items-center gap-2 ms-auto">
        <label for="{{ $searchId ?? 'table-search' }}" class="text-muted small mb-0">Cari:</label>
        <input id="{{ $searchId ?? 'table-search' }}" type="text" name="search" class="form-control form-control-sm master-search"
               placeholder="{{ $placeholder ?? 'Cari data...' }}" value="{{ request('search') }}">
        <button class="btn btn-sm btn-outline-primary">Cari</button>
        <a href="{{ $resetUrl ?? ($action ?? url()->current()) }}" class="btn btn-sm btn-outline-secondary">Reset</a>
    </div>
</form>