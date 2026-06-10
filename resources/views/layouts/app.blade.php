<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MBKM Fakultas')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: #f3f0ff;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            min-height: 100vh;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: #fff;
            border-right: 1.5px solid #ede9fe;
            flex-shrink: 0;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        .sidebar-brand {
            padding: 24px 20px;
            border-bottom: 1.5px solid #f3f0ff;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-icon {
            width: 42px; height: 42px;
            background: #ede9fe;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: #7c3aed;
            flex-shrink: 0;
        }

        .brand-text .brand-name {
            font-size: 0.95rem;
            font-weight: 700;
            color: #1e1140;
            line-height: 1.2;
        }

        .brand-text .brand-sub {
            font-size: 0.7rem;
            color: #a78bfa;
            font-weight: 400;
        }

        .sidebar-user {
            margin: 16px 16px 8px;
            background: #faf8ff;
            border: 1.5px solid #ede9fe;
            border-radius: 14px;
            padding: 12px 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, #c4b5fd, #a78bfa);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 0.9rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .user-info .user-name {
            font-size: 0.82rem;
            font-weight: 600;
            color: #1e1140;
            line-height: 1.2;
        }

        .user-info .user-role {
            font-size: 0.68rem;
            color: #a78bfa;
            font-weight: 500;
            text-transform: capitalize;
        }

        .sidebar-section {
            padding: 12px 14px 6px;
        }

        .sidebar-label {
            font-size: 0.62rem;
            font-weight: 700;
            color: #c4b5fd;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 0 6px;
            margin-bottom: 6px;
        }

        .nav-link {
            color: #6b7280 !important;
            padding: 10px 12px !important;
            border-radius: 12px !important;
            font-size: 0.845rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.2s;
            margin-bottom: 3px;
            text-decoration: none;
        }

        .nav-link i {
            font-size: 1rem;
            width: 20px;
            text-align: center;
            color: #c4b5fd;
            transition: all 0.2s;
        }

        .nav-link:hover {
            color: #7c3aed !important;
            background: #f3f0ff !important;
        }

        .nav-link:hover i { color: #7c3aed; }

        .nav-link.active {
            color: #7c3aed !important;
            background: #ede9fe !important;
            font-weight: 600;
        }

        .nav-link.active i { color: #7c3aed; }

        .sidebar-footer {
            margin-top: auto;
            padding: 16px;
            border-top: 1.5px solid #f3f0ff;
        }

        .btn-logout-side {
            width: 100%;
            background: #faf8ff;
            border: 1.5px solid #ede9fe;
            border-radius: 12px;
            padding: 9px 14px;
            color: #7c3aed;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-logout-side:hover {
            background: #ede9fe;
            color: #5b21b6;
        }

        /* ===== MAIN ===== */
        .main-wrapper {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
        }

        /* ===== TOPBAR ===== */
        .topbar {
            background: #fff;
            padding: 0 32px;
            height: 64px;
            border-bottom: 1.5px solid #ede9fe;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 99;
        }

        .topbar-left h5 {
            font-size: 1rem;
            font-weight: 700;
            color: #1e1140;
            margin: 0;
        }

        .topbar-breadcrumb {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.72rem;
            color: #c4b5fd;
            margin-top: 2px;
        }

        .topbar-breadcrumb i { font-size: 0.6rem; }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .notif-btn {
            width: 36px; height: 36px;
            background: #faf8ff;
            border: 1.5px solid #ede9fe;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #a78bfa;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .notif-btn:hover { background: #ede9fe; color: #7c3aed; }

        .topbar-user {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #faf8ff;
            border: 1.5px solid #ede9fe;
            border-radius: 12px;
            padding: 6px 12px 6px 8px;
        }

        .topbar-avatar {
            width: 30px; height: 30px;
            background: linear-gradient(135deg, #c4b5fd, #a78bfa);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .topbar-name {
            font-size: 0.8rem;
            font-weight: 600;
            color: #1e1140;
        }

        /* ===== CONTENT ===== */
        .content-area {
            padding: 28px 32px;
            flex: 1;
            background: #f3f0ff;
        }

        /* ===== CARDS ===== */
        .card {
            border: 1.5px solid #ede9fe;
            border-radius: 18px;
            box-shadow: 0 2px 12px rgba(109,40,217,0.05);
            background: #fff;
        }

        /* ===== STAT CARDS ===== */
        .stat-card {
            border-radius: 18px;
            padding: 22px 24px;
            position: relative;
            overflow: hidden;
            border: none;
        }

        .stat-card.purple { background: #ede9fe; }
        .stat-card.indigo { background: #e0e7ff; }
        .stat-card.pink   { background: #fce7f3; }
        .stat-card.teal   { background: #ccfbf1; }
        .stat-card.orange { background: #ffedd5; }
        .stat-card.blue   { background: #dbeafe; }

        .stat-label {
            font-size: 0.78rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .stat-card.purple .stat-label { color: #7c3aed; }
        .stat-card.indigo .stat-label { color: #4338ca; }
        .stat-card.pink   .stat-label { color: #be185d; }
        .stat-card.teal   .stat-label { color: #0f766e; }
        .stat-card.orange .stat-label { color: #c2410c; }
        .stat-card.blue   .stat-label { color: #1d4ed8; }

        .stat-value {
            font-size: 2.2rem;
            font-weight: 700;
            line-height: 1;
        }

        .stat-card.purple .stat-value { color: #5b21b6; }
        .stat-card.indigo .stat-value { color: #3730a3; }
        .stat-card.pink   .stat-value { color: #9d174d; }
        .stat-card.teal   .stat-value { color: #065f46; }
        .stat-card.orange .stat-value { color: #9a3412; }
        .stat-card.blue   .stat-value { color: #1e40af; }

        .stat-icon {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 3rem;
            opacity: 0.1;
        }

        .stat-card.purple .stat-icon { color: #7c3aed; }
        .stat-card.indigo .stat-icon { color: #4338ca; }
        .stat-card.pink   .stat-icon { color: #be185d; }
        .stat-card.teal   .stat-icon { color: #0f766e; }
        .stat-card.orange .stat-icon { color: #c2410c; }
        .stat-card.blue   .stat-icon { color: #1d4ed8; }

        /* ===== TABLE ===== */
        .table thead th {
            background: #faf8ff;
            color: #7c3aed;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
            padding: 13px 16px;
        }

        .table tbody td {
            padding: 13px 16px;
            vertical-align: middle;
            border-color: #f3f0ff;
            font-size: 0.86rem;
            color: #374151;
        }

        .table tbody tr:hover { background: #faf8ff; }

        /* ===== BADGES ===== */
        .badge-purple { background: #ede9fe; color: #6d28d9; border-radius: 8px; padding: 4px 12px; font-size: 0.75rem; font-weight: 600; }
        .badge-green  { background: #dcfce7; color: #15803d; border-radius: 8px; padding: 4px 12px; font-size: 0.75rem; font-weight: 600; }
        .badge-red    { background: #fee2e2; color: #b91c1c; border-radius: 8px; padding: 4px 12px; font-size: 0.75rem; font-weight: 600; }
        .badge-yellow { background: #fef9c3; color: #a16207; border-radius: 8px; padding: 4px 12px; font-size: 0.75rem; font-weight: 600; }
        .badge-blue   { background: #dbeafe; color: #1d4ed8; border-radius: 8px; padding: 4px 12px; font-size: 0.75rem; font-weight: 600; }
        .badge-gray   { background: #f3f4f6; color: #4b5563; border-radius: 8px; padding: 4px 12px; font-size: 0.75rem; font-weight: 600; }

        /* ===== BUTTONS ===== */
        .btn-primary {
            background: #8b5cf6 !important;
            border: none !important;
            border-radius: 10px !important;
            font-weight: 600;
            font-size: 0.86rem;
            transition: all 0.2s;
        }

        .btn-primary:hover {
            background: #7c3aed !important;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(124,58,237,0.2) !important;
        }

        .btn-secondary {
            background: #f3f4f6 !important;
            border: none !important;
            color: #4b5563 !important;
            border-radius: 10px !important;
            font-size: 0.86rem;
        }

        .btn-outline-primary {
            border: 1.5px solid #ddd6fe !important;
            color: #7c3aed !important;
            border-radius: 8px !important;
            font-size: 0.82rem;
            background: transparent !important;
        }

        .btn-outline-primary:hover { background: #ede9fe !important; }
        .btn-outline-warning { border: 1.5px solid #fde68a !important; color: #d97706 !important; border-radius: 8px !important; font-size: 0.82rem; background: transparent !important; }
        .btn-outline-warning:hover { background: #fef3c7 !important; }
        .btn-outline-danger { border: 1.5px solid #fca5a5 !important; color: #dc2626 !important; border-radius: 8px !important; font-size: 0.82rem; background: transparent !important; }
        .btn-outline-danger:hover { background: #fee2e2 !important; }
        .btn-outline-success { border: 1.5px solid #86efac !important; color: #16a34a !important; border-radius: 8px !important; font-size: 0.82rem; background: transparent !important; }
        .btn-outline-success:hover { background: #dcfce7 !important; }
        .btn-success { background: #16a34a !important; border: none !important; border-radius: 10px !important; font-size: 0.86rem; }
        .btn-danger  { background: #dc2626 !important; border: none !important; border-radius: 10px !important; font-size: 0.86rem; }

        /* ===== FORM ===== */
        .form-control, .form-select {
            border: 1.5px solid #ede9fe;
            border-radius: 10px;
            padding: 11px 14px;
            font-size: 0.875rem;
            color: #1f2937;
            background: #faf8ff;
            transition: all 0.2s;
        }

        .form-control:focus, .form-select:focus {
            border-color: #8b5cf6;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(139,92,246,0.1);
        }

        .form-label {
            font-size: 0.82rem;
            font-weight: 600;
            color: #4c1d95;
            margin-bottom: 7px;
        }

        /* ===== ALERTS ===== */
        .alert-success { background: #f0fdf4; border: 1.5px solid #86efac; color: #15803d; border-radius: 12px; }
        .alert-danger  { background: #fef2f2; border: 1.5px solid #fca5a5; color: #b91c1c; border-radius: 12px; }

        /* ===== SCROLLBAR ===== */
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #ddd6fe; border-radius: 99px; }

        /* ===== SECTION TITLE ===== */
        .section-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: #1e1140;
            margin-bottom: 16px;
        }
    </style>
    @stack('styles')
</head>
<body>
@php
    $unreadNotifications = auth()->user()->notifikasis()->where('status', 'belum')->count();
@endphp

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="bi bi-mortarboard-fill"></i></div>
        <div class="brand-text">
            <div class="brand-name">MBKM Fakultas</div>
            <div class="brand-sub">Sistem Informasi Magang</div>
        </div>
    </div>

    <a href="{{ route('profile.show') }}" class="sidebar-user text-decoration-none">
        <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
        <div class="user-info">
            <div class="user-name">{{ auth()->user()->name }}</div>
            <div class="user-role">{{ auth()->user()->role }}</div>
        </div>
    </a>

    <div class="sidebar-section">
        <div class="sidebar-label">Menu</div>
        <nav class="nav flex-column">
            @include('layouts.sidebar')
        </nav>
    </div>

    <div class="sidebar-footer">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn-logout-side">
                <i class="bi bi-box-arrow-left"></i> Keluar dari Sistem
            </button>
        </form>
    </div>
</div>

<!-- MAIN -->
<div class="main-wrapper">
    <div class="topbar">
        <div class="topbar-left">
            <h5>@yield('page-title')</h5>
            <div class="topbar-breadcrumb">
                <i class="bi bi-house-fill"></i>
                <span>Beranda</span>
                <i class="bi bi-chevron-right"></i>
                <span>@yield('page-title')</span>
            </div>
        </div>
        <div class="topbar-right">
            <a href="{{ route('notifikasi.index') }}" class="notif-btn text-decoration-none position-relative">
                <i class="bi bi-bell"></i>
                @if($unreadNotifications > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $unreadNotifications }}</span>
                @endif
            </a>
            <div class="dropdown">
                <button class="topbar-user border-0 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="topbar-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                    <span class="topbar-name">{{ auth()->user()->name }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('profile.show') }}">Profile</a></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item">Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="content-area">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-4">
                <i class="bi bi-exclamation-circle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
