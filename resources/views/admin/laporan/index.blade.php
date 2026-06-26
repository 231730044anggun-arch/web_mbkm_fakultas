@extends('layouts.app')
@section('title', 'Laporan')
@section('page-title', 'Laporan & Monitoring')

@section('content')
<style>
    .monitor-dashboard {
        --monitor-accent: #7c3aed;
        --monitor-accent-rgb: 124, 58, 237;
        --monitor-soft: #f3e8ff;
        --monitor-map-land: #e9d5ff;
        --monitor-map-land-soft: #f5f0ff;
        --monitor-map-stroke: #c4b5fd;
    }
    .monitor-dashboard .monitor-summary-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(var(--monitor-accent-rgb), .12);
        color: var(--monitor-accent);
        font-size: 22px;
        flex: 0 0 auto;
    }
    .monitor-map {
        position: relative;
        min-height: 360px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: linear-gradient(135deg, #fbf7ff 0%, #f4ecff 100%);
        overflow: hidden;
    }
    .indonesia-map-svg {
        position: absolute;
        inset: 24px 18px 34px 18px;
        width: calc(100% - 36px);
        height: calc(100% - 58px);
        opacity: .95;
    }
    .indonesia-map-svg .island {
        fill: var(--monitor-map-land);
        stroke: var(--monitor-map-stroke);
        stroke-width: 1.2;
    }
    .indonesia-map-svg .island-soft {
        fill: var(--monitor-map-land-soft);
        stroke: var(--monitor-map-stroke);
        stroke-width: 1;
    }
    .monitor-point {
        position: absolute;
        width: var(--point-size, 16px);
        height: var(--point-size, 16px);
        border-radius: 50%;
        background: var(--monitor-accent);
        border: 3px solid #fff;
        box-shadow: 0 4px 14px rgba(var(--monitor-accent-rgb), .34);
        transform: translate(-50%, -50%);
        z-index: 4;
    }
    .monitor-point::after {
        content: '';
        position: absolute;
        inset: -8px;
        border-radius: 50%;
        border: 1px solid rgba(var(--monitor-accent-rgb), .28);
    }
    .monitor-point-label {
        position: absolute;
        transform: translate(10px, -28px);
        white-space: nowrap;
        font-size: 12px;
        color: #334155;
        background: rgba(255,255,255,.94);
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 4px 7px;
        box-shadow: 0 4px 12px rgba(15, 23, 42, .06);
        z-index: 5;
    }
    .map-legend {
        position: absolute;
        right: 14px;
        bottom: 14px;
        z-index: 6;
        background: rgba(255,255,255,.94);
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 10px 12px;
        font-size: 12px;
    }
    .legend-dot {
        display: inline-block;
        border-radius: 999px;
        background: var(--monitor-accent);
        border: 2px solid #fff;
        box-shadow: 0 0 0 1px rgba(var(--monitor-accent-rgb), .18);
        vertical-align: middle;
        margin-right: 6px;
    }
    .monitor-bar-row + .monitor-bar-row { margin-top: 14px; }
    .monitor-bar {
        height: 11px;
        border-radius: 999px;
        background: #e9ecef;
        overflow: hidden;
    }
    .monitor-bar > span {
        display: block;
        height: 100%;
        background: var(--monitor-accent);
        border-radius: 999px;
    }
    .monitor-rank {
        width: 26px;
        height: 26px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(var(--monitor-accent-rgb), .1);
        color: var(--monitor-accent);
        font-size: 12px;
        font-weight: 700;
    }
    @media (max-width: 768px) {
        .monitor-map { min-height: 300px; }
        .monitor-point-label { display: none; }
    }
    @media print {
        .sidebar, .navbar, .btn, form, .no-print { display: none !important; }
        .card { box-shadow: none !important; border: 1px solid #ddd !important; }
        body { background: #fff !important; }
    }
</style>

<div class="monitor-dashboard">
@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card p-4 mb-4">
    <form method="GET" action="{{ route('admin.laporan.index') }}" class="row g-3 align-items-end">
        <div class="col-md-2">
            <label class="form-label small text-muted">Periode Magang</label>
            <select name="periode_id" class="form-select">
                <option value="">Semua Periode</option>
                @foreach($periodes as $periode)
                <option value="{{ $periode->id }}" @selected(request('periode_id') == $periode->id)>{{ $periode->nama_periode }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small text-muted">Program Studi</label>
            <select name="prodi_id" class="form-select">
                <option value="">Semua Program Studi</option>
                @foreach($prodis as $prodi)
                <option value="{{ $prodi->id }}" @selected(request('prodi_id') == $prodi->id)>{{ $prodi->nama_prodi }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small text-muted">Status Magang</label>
            <select name="status" class="form-select">
                <option value="">Semua Status</option>
                <option value="berjalan" @selected(request('status') === 'berjalan')>Berjalan</option>
                <option value="proses_penilaian" @selected(request('status') === 'proses_penilaian')>Proses Penilaian</option>
                <option value="selesai" @selected(request('status') === 'selesai')>Selesai</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small text-muted">Mitra</label>
            <select name="mitra_id" class="form-select">
                <option value="">Semua Mitra</option>
                @foreach($mitras as $mitra)
                <option value="{{ $mitra->id }}" @selected(request('mitra_id') == $mitra->id)>{{ $mitra->nama_instansi }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small text-muted">Kota</label>
            <select name="kota" class="form-select">
                <option value="">Semua Kota</option>
                @foreach($kotaOptions as $kota)
                <option value="{{ $kota }}" @selected(request('kota') === $kota)>{{ $kota }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button class="btn btn-primary flex-fill">Terapkan Filter</button>
            <a href="{{ route('admin.laporan.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card p-3 h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="monitor-summary-icon"><i class="bi bi-people"></i></div>
                <div>
                    <div class="text-muted small">Mahasiswa Magang Berjalan</div>
                    <h3 class="fw-bold mb-0">{{ $pengajuan_berjalan }}</h3>
                    <div class="text-muted small">Belum masuk tahap penilaian final</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="monitor-summary-icon"><i class="bi bi-check2-circle"></i></div>
                <div>
                    <div class="text-muted small">Proses Penilaian</div>
                    <h3 class="fw-bold mb-0">{{ $pengajuan_proses_penilaian }}</h3>
                    <div class="text-muted small">Seminar diajukan, nilai belum lengkap</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="monitor-summary-icon"><i class="bi bi-building"></i></div>
                <div>
                    <div class="text-muted small">Mahasiswa Selesai Magang</div>
                    <h3 class="fw-bold mb-0">{{ $pengajuan_selesai }}</h3>
                    <div class="text-muted small">Nilai Akhir sudah keluar</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="monitor-summary-icon"><i class="bi bi-geo-alt"></i></div>
                <div>
                    <div class="text-muted small">Kota Penempatan</div>
                    <h3 class="fw-bold mb-0">{{ $kota_penempatan }}</h3>
                    <div class="text-muted small">Berdasarkan kota mitra</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-7">
        <div class="card p-4 h-100">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h6 class="fw-bold mb-1">Sebaran Mahasiswa Magang per Kota</h6>
                    <p class="text-muted small mb-0">Marker ditempatkan dengan mapping koordinat kota sederhana. Kota tanpa mapping tetap tampil di tabel sebaran.</p>
                </div>
                <span class="badge bg-light text-dark border">Peta Indonesia</span>
            </div>
            <div class="monitor-map mb-3">
                <svg class="indonesia-map-svg" viewBox="0 0 1000 420" role="img" aria-label="Peta Indonesia sederhana" preserveAspectRatio="xMidYMid meet">
                    <path class="island" d="M88 185 C130 150 185 145 238 165 C270 177 292 199 325 210 C283 235 224 239 174 222 C135 209 105 205 75 220 C66 205 72 194 88 185 Z" />
                    <path class="island" d="M310 237 C350 213 415 210 468 224 C505 234 548 252 589 247 C563 273 507 286 455 279 C396 271 352 263 303 271 C292 258 297 245 310 237 Z" />
                    <path class="island" d="M395 150 C445 124 516 129 563 158 C589 174 607 196 636 205 C607 224 554 225 508 210 C467 197 430 192 388 204 C377 183 380 162 395 150 Z" />
                    <path class="island-soft" d="M598 146 C628 126 666 127 693 149 C716 168 724 196 711 219 C682 209 660 190 630 184 C605 178 590 166 598 146 Z" />
                    <path class="island" d="M662 252 C711 223 781 214 838 229 C873 238 906 256 946 252 C918 285 858 304 794 300 C732 296 693 281 651 291 C640 274 646 261 662 252 Z" />
                    <path class="island-soft" d="M735 124 C773 105 832 107 879 127 C914 142 934 164 960 177 C910 191 852 186 805 171 C768 160 742 151 714 158 C709 143 718 131 735 124 Z" />
                    <path class="island-soft" d="M620 318 C656 303 700 306 734 326 C758 340 775 358 801 365 C764 378 714 377 672 365 C641 356 616 351 589 359 C584 343 596 326 620 318 Z" />
                    <path class="island-soft" d="M260 304 C303 292 358 298 399 317 C367 336 306 338 248 324 C243 316 249 308 260 304 Z" />
                </svg>

                @forelse($mapPoints as $point)
                    @php($pointSize = 14 + min(14, $point->total * 2))
                    <div class="monitor-point" title="{{ $point->kota }}: {{ $point->total }} mahasiswa" style="left: {{ $point->x }}%; top: {{ $point->y }}%; --point-size: {{ $pointSize }}px;"></div>
                    <div class="monitor-point-label" style="left: {{ $point->x }}%; top: {{ $point->y }}%;">{{ $point->kota }}: {{ $point->total }}</div>
                @empty
                    <div class="d-flex align-items-center justify-content-center h-100 text-muted position-relative" style="min-height: 320px; z-index: 2;">
                        Belum ada marker kota. Data kota tetap tersedia di tabel sebaran jika ada.
                    </div>
                @endforelse

                <div class="map-legend">
                    <div class="fw-semibold mb-1">Legend</div>
                    <div><span class="legend-dot" style="width: 10px; height: 10px;"></span>1-5 mahasiswa</div>
                    <div><span class="legend-dot" style="width: 14px; height: 14px;"></span>6-15 mahasiswa</div>
                    <div><span class="legend-dot" style="width: 18px; height: 18px;"></span>&gt;15 mahasiswa</div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Kota</th><th>Jumlah Mahasiswa Magang</th><th>Daftar/Top Mitra</th></tr>
                    </thead>
                    <tbody>
                        @forelse($topKota as $row)
                        <tr>
                            <td>{{ $row->kota }}</td>
                            <td><span class="badge" style="background: var(--monitor-accent);">{{ $row->total }}</span></td>
                            <td>
                                @forelse($row->top_mitras as $nama => $total)
                                    <span class="badge bg-light text-dark border me-1 mb-1">{{ $nama }} ({{ $total }})</span>
                                @empty
                                    <span class="text-muted">Data mitra belum tersedia.</span>
                                @endforelse
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted">Belum ada data mahasiswa magang berjalan atau selesai.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xl-5">
        <div class="card p-4 mb-3">
            <h6 class="fw-bold mb-3">Top Kota Penempatan</h6>
            @forelse($topKota->take(7) as $row)
                <div class="monitor-bar-row">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="monitor-rank">{{ $loop->iteration }}</span>
                        <span class="flex-fill small fw-semibold">{{ $row->kota }}</span>
                        <strong>{{ $row->total }}</strong>
                    </div>
                    <div class="monitor-bar"><span style="width: {{ round(($row->total / $maxKota) * 100) }}%"></span></div>
                </div>
            @empty
                <div class="text-muted">Data kota belum tersedia.</div>
            @endforelse
        </div>
        <div class="card p-4">
            <h6 class="fw-bold mb-3">Top Mitra</h6>
            @forelse($topMitras->take(7) as $row)
                <div class="monitor-bar-row">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="monitor-rank">{{ $loop->iteration }}</span>
                        <span class="flex-fill small fw-semibold">{{ $row->nama }} <span class="text-muted">({{ $row->kota }})</span></span>
                        <strong>{{ $row->total }}</strong>
                    </div>
                    <div class="monitor-bar"><span style="width: {{ round(($row->total / $maxMitra) * 100) }}%"></span></div>
                </div>
            @empty
                <div class="text-muted">Data mitra belum tersedia.</div>
            @endforelse
        </div>
    </div>
</div>

<div class="card p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h6 class="fw-bold mb-1">Data Monitoring Magang</h6>
            <div class="text-muted small">Menampilkan {{ $monitoringRows->count() }} mahasiswa magang berstatus berjalan atau selesai.</div>
        </div>
        <div class="d-flex gap-2 no-print">
            <button type="button" onclick="window.print()" class="btn btn-outline-secondary btn-sm">Cetak</button>
            <a href="{{ route('admin.laporan.export', request()->query()) }}" class="btn btn-outline-primary btn-sm">Export</a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Nama Mahasiswa</th>
                    <th>NIM</th>
                    <th>Program Studi</th>
                    <th>Mitra</th>
                    <th>Kota</th>
                    <th>Status Magang</th>
                    <th>Nama Dosen Pembimbing</th>
                    <th>Dosen Pembimbing</th>
                    <th>Pembimbing Lapangan</th>
                    <th>Periode</th>
                </tr>
            </thead>
            <tbody>
                @forelse($monitoringRows as $row)
                <tr>
                    <td>{{ $row->mahasiswa->nama_lengkap ?? '-' }}</td>
                    <td>{{ $row->mahasiswa->nim ?? '-' }}</td>
                    <td>{{ $row->mahasiswa->prodi->nama_prodi ?? '-' }}</td>
                    <td>{{ $row->mitra->nama_instansi ?? $row->nama_instansi_manual ?? '-' }}</td>
                    <td>{{ $row->mitra->kota ?? 'Tidak diketahui' }}</td>
                    <td>
                        @php($statusMonitoring = $row->penilaian?->nilai_akhir !== null ? 'selesai' : ($row->hasValidSeminar() ? 'proses_penilaian' : 'berjalan'))
                        <span class="badge bg-{{ $statusMonitoring === 'selesai' ? 'success' : ($statusMonitoring === 'proses_penilaian' ? 'warning text-dark' : 'info') }}">
                            {{ $statusMonitoring === 'selesai' ? 'Selesai' : ($statusMonitoring === 'proses_penilaian' ? 'Proses Penilaian' : 'Berjalan') }}
                        </span>
                    </td>
                    <td>{{ $row->bimbingans->pluck('dosen.nama_dosen')->filter()->unique()->implode(', ') ?: '-' }}</td>
                    <td>
                        @if($row->penilaian?->hasNilaiDosenTahap1())
                            <span class="badge bg-success">✓ Sudah Menilai</span>
                        @else
                            <span class="badge bg-light text-dark border">— Belum Menilai</span>
                        @endif
                    </td>
                    <td>
                        @if($row->penilaian?->hasNilaiPembimbingTahap1())
                            <span class="badge bg-success">✓ Sudah Menilai</span>
                        @else
                            <span class="badge bg-light text-dark border">— Belum Menilai</span>
                        @endif
                    </td>
                    <td>{{ $row->periode->nama_periode ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="10" class="text-center text-muted py-4">Belum ada data mahasiswa magang berjalan atau selesai.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</div>
@endsection


