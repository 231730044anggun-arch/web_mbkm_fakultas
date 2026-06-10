<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — MBKM Fakultas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            background: #ede9fe;
            font-family: 'Segoe UI', sans-serif;
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        /* ===== LEFT FULL HEIGHT ===== */
        .left {
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px 64px;
            position: relative;
            overflow: hidden;
        }

        .left-deco-1 {
            position: absolute;
            width: 300px; height: 300px;
            background: #f3f0ff;
            border-radius: 50%;
            top: -100px; left: -100px;
        }

        .left-deco-2 {
            position: absolute;
            width: 200px; height: 200px;
            background: #faf8ff;
            border-radius: 50%;
            bottom: 40px; right: -60px;
        }

        .left-inner { width: 100%; max-width: 380px; position: relative; z-index: 1; }

        .brand-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 40px;
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
        }

        .brand-name {
            font-size: 1rem;
            font-weight: 700;
            color: #1e1140;
            line-height: 1.2;
        }

        .brand-name span {
            display: block;
            font-size: 0.72rem;
            font-weight: 400;
            color: #a78bfa;
        }

        .left-inner h2 {
            font-size: 1.7rem;
            font-weight: 700;
            color: #1e1140;
            line-height: 1.3;
            margin-bottom: 8px;
        }

        .left-inner > p {
            font-size: 0.85rem;
            color: #9ca3af;
            margin-bottom: 36px;
        }

        .form-group { margin-bottom: 16px; }

        .form-group label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: #4c1d95;
            margin-bottom: 7px;
        }

        .input-box {
            display: flex;
            align-items: center;
            border: 1.5px solid #ede9fe;
            border-radius: 12px;
            background: #faf8ff;
            padding: 0 14px;
            transition: all 0.2s;
        }

        .input-box:focus-within {
            border-color: #8b5cf6;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(139,92,246,0.1);
        }

        .input-box i {
            color: #c4b5fd;
            font-size: 1rem;
            margin-right: 10px;
            flex-shrink: 0;
        }

        .input-box input {
            flex: 1;
            border: none;
            background: transparent;
            padding: 12px 0;
            font-size: 0.875rem;
            color: #1f2937;
            outline: none;
        }

        .input-box input::placeholder { color: #ddd6fe; }

        .btn-login {
            width: 100%;
            padding: 13px;
            background: #8b5cf6;
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.25s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 24px;
        }

        .btn-login:hover {
            background: #7c3aed;
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(139,92,246,0.3);
        }

        .error-box {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: 10px 14px;
            color: #b91c1c;
            font-size: 0.82rem;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 18px;
        }

        .left-footer {
            margin-top: 32px;
            text-align: center;
            color: #d1d5db;
            font-size: 0.7rem;
        }

        /* ===== RIGHT FULL HEIGHT ===== */
        .right {
            background: #ede9fe;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px 48px;
            position: relative;
            overflow: hidden;
        }

        .right-deco {
            position: absolute;
            width: 350px; height: 350px;
            background: #ddd6fe;
            border-radius: 50%;
            bottom: -100px; right: -100px;
        }

        .right-inner {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 360px;
        }

        .right-inner h3 {
            font-size: 1.2rem;
            font-weight: 700;
            color: #3b0764;
            margin-bottom: 6px;
        }

        .right-inner > p {
            font-size: 0.82rem;
            color: #7c3aed;
            margin-bottom: 28px;
        }

        .feature-card {
            background: rgba(255,255,255,0.6);
            border: 1.5px solid rgba(255,255,255,0.8);
            border-radius: 16px;
            padding: 16px 18px;
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 12px;
            backdrop-filter: blur(10px);
            transition: all 0.2s;
        }

        .feature-card:hover {
            background: rgba(255,255,255,0.85);
            transform: translateX(4px);
        }

        .feature-card-icon {
            width: 44px; height: 44px;
            background: #ddd6fe;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: #7c3aed;
            flex-shrink: 0;
        }

        .feature-card-text h5 {
            font-size: 0.85rem;
            font-weight: 600;
            color: #3b0764;
            margin-bottom: 2px;
        }

        .feature-card-text p {
            font-size: 0.76rem;
            color: #7c3aed;
            line-height: 1.4;
        }

        .roles-section {
            margin-top: 24px;
        }

        .roles-section p {
            font-size: 0.75rem;
            font-weight: 700;
            color: #6d28d9;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .roles-row {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
        }

        .role-pill {
            background: #fff;
            color: #6d28d9;
            border: 1.5px solid #ddd6fe;
            border-radius: 20px;
            padding: 5px 14px;
            font-size: 0.73rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
        }
    </style>
</head>
<body>

<!-- LEFT: FORM -->
<div class="left">
    <div class="left-deco-1"></div>
    <div class="left-deco-2"></div>

    <div class="left-inner">
        <div class="brand-row">
            <div class="brand-icon"><i class="bi bi-mortarboard-fill"></i></div>
            <div class="brand-name">
                MBKM Fakultas
                <span>Sistem Informasi Magang</span>
            </div>
        </div>

        <h2>Masuk ke Akun Anda</h2>
        <p>Selamat datang kembali! Silakan isi data login Anda.</p>

        @if($errors->any())
        <div class="error-box">
            <i class="bi bi-exclamation-circle-fill"></i>
            {{ $errors->first() }}
        </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Alamat Email</label>
                <div class="input-box">
                    <i class="bi bi-envelope-fill"></i>
                    <input type="email" name="email" placeholder="nama@email.com"
                        value="{{ old('email') }}" required autofocus>
                </div>
            </div>

            <div class="form-group">
                <label>Password</label>
                <div class="input-box">
                    <i class="bi bi-lock-fill"></i>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn-login">
                <i class="bi bi-box-arrow-in-right"></i>
                Masuk ke Sistem
            </button>
        </form>

        <div class="left-footer">© 2025 MBKM Fakultas v1.0</div>
    </div>
</div>

<!-- RIGHT: INFO -->
<div class="right">
    <div class="right-deco"></div>

    <div class="right-inner">
        <h3>Kenapa MBKM Fakultas?</h3>
        <p>Platform lengkap untuk pengelolaan program magang kampus merdeka</p>

        <div class="feature-card">
            <div class="feature-card-icon"><i class="bi bi-file-earmark-check-fill"></i></div>
            <div class="feature-card-text">
                <h5>Pengajuan Digital</h5>
                <p>Ajukan magang & dapatkan persetujuan secara online</p>
            </div>
        </div>

        <div class="feature-card">
            <div class="feature-card-icon"><i class="bi bi-journal-richtext"></i></div>
            <div class="feature-card-text">
                <h5>Logbook Harian</h5>
                <p>Catat & monitor kegiatan magang setiap hari</p>
            </div>
        </div>

        <div class="feature-card">
            <div class="feature-card-icon"><i class="bi bi-star-fill"></i></div>
            <div class="feature-card-text">
                <h5>Penilaian Terpadu</h5>
                <p>Nilai dari dosen & mitra terintegrasi otomatis</p>
            </div>
        </div>

        <div class="feature-card">
            <div class="feature-card-icon"><i class="bi bi-bar-chart-fill"></i></div>
            <div class="feature-card-text">
                <h5>Laporan & Statistik</h5>
                <p>Data real-time untuk monitoring & evaluasi</p>
            </div>
        </div>

        <div class="roles-section">
            <p><i class="bi bi-people-fill"></i> Tersedia untuk:</p>
            <div class="roles-row">
                <div class="role-pill"><i class="bi bi-shield-fill"></i> Superadmin</div>
                <div class="role-pill"><i class="bi bi-person-gear"></i> Admin</div>
                <div class="role-pill"><i class="bi bi-person-badge"></i> Mahasiswa</div>
                <div class="role-pill"><i class="bi bi-person-workspace"></i> Dosen</div>
                <div class="role-pill"><i class="bi bi-building"></i> Mitra</div>
            </div>
        </div>
    </div>
</div>

</body>
</html>