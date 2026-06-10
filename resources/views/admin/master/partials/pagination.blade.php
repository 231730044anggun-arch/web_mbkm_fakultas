@if ($paginator->total() > 0)
    @php
        $start = max(1, $paginator->currentPage() - 2);
        $end = min($paginator->lastPage(), $paginator->currentPage() + 2);
    @endphp

    <div class="master-pagination d-flex flex-wrap justify-content-between align-items-center gap-3 mt-3 pt-3 border-top">
        <div class="text-muted small">
            Menampilkan {{ $paginator->firstItem() }} sampai {{ $paginator->lastItem() }} dari {{ $paginator->total() }} data
        </div>

        @if ($paginator->lastPage() > 1)
            <nav aria-label="Navigasi halaman">
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $paginator->previousPageUrl() ?: '#' }}" aria-label="Halaman sebelumnya">Sebelumnya</a>
                    </li>

                    @if ($start > 1)
                        <li class="page-item"><a class="page-link" href="{{ $paginator->url(1) }}">1</a></li>
                        @if ($start > 2)
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        @endif
                    @endif

                    @foreach ($paginator->getUrlRange($start, $end) as $page => $url)
                        <li class="page-item {{ $page === $paginator->currentPage() ? 'active' : '' }}">
                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endforeach

                    @if ($end < $paginator->lastPage())
                        @if ($end < $paginator->lastPage() - 1)
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        @endif
                        <li class="page-item"><a class="page-link" href="{{ $paginator->url($paginator->lastPage()) }}">{{ $paginator->lastPage() }}</a></li>
                    @endif

                    <li class="page-item {{ $paginator->hasMorePages() ? '' : 'disabled' }}">
                        <a class="page-link" href="{{ $paginator->nextPageUrl() ?: '#' }}" aria-label="Halaman berikutnya">Berikutnya</a>
                    </li>
                </ul>
            </nav>
        @endif
    </div>
@endif

@once
    @push('styles')
        <style>
            .master-table-card {
                overflow: hidden;
            }

            .master-table-toolbar {
                padding: .9rem 1rem;
                border: 1px solid rgba(124, 58, 237, .12);
                border-radius: 14px;
                background: #faf8ff;
            }

            .master-per-page {
                width: 82px;
                border-color: #ddd6fe;
            }

            .master-search {
                width: min(260px, 45vw);
                border-color: #ddd6fe;
            }

            .master-table-wrap {
                width: 100%;
                overflow-x: auto;
                border: 1px solid rgba(124, 58, 237, .12);
                border-radius: 14px;
            }

            .master-table {
                min-width: 980px;
                margin-bottom: 0;
            }

            .master-table thead th {
                white-space: nowrap;
                font-size: .78rem;
                text-transform: uppercase;
                letter-spacing: .02em;
                color: #5b21b6;
                background: linear-gradient(180deg, #f5f3ff 0%, #ede9fe 100%);
                border-bottom: 1px solid #ddd6fe;
            }

            .master-table tbody td {
                vertical-align: middle;
                white-space: nowrap;
            }

            .master-table tbody tr:hover {
                background: #faf8ff;
            }

            .master-table tbody td.text-wrap-cell {
                white-space: normal;
                min-width: 180px;
            }

            .master-pagination .pagination {
                gap: .25rem;
            }

            .master-pagination .page-link {
                border-radius: .55rem;
                border-color: rgba(124, 58, 237, .18);
                color: #7c3aed;
                min-width: 2.1rem;
                text-align: center;
                background: #fff;
            }

            .master-pagination .page-link:hover {
                background: #ede9fe;
                color: #6d28d9;
            }

            .master-pagination .page-item.active .page-link {
                color: #fff;
                background: #8b5cf6;
                border-color: #8b5cf6;
                box-shadow: 0 .35rem .85rem rgba(124, 58, 237, .18);
            }

            .master-pagination .page-item.disabled .page-link {
                color: #94a3b8;
                background: #f8fafc;
            }

            @media (max-width: 768px) {
                .master-table-toolbar {
                    align-items: stretch !important;
                }

                .master-table-toolbar > div {
                    width: 100%;
                }

                .master-search {
                    width: 100%;
                }
            }
        </style>
    @endpush
@endonce