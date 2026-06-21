<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MBKM Fakultas</title>
    <link rel="icon" href="{{ asset('assets/img/favicon.ico') }}" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            font-family: Inter, Poppins, Nunito, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: #1f1b2e;
            background:
                radial-gradient(circle at 18% 12%, rgba(196, 181, 253, 0.34), transparent 30%),
                radial-gradient(circle at 82% 86%, rgba(233, 213, 255, 0.42), transparent 34%),
                linear-gradient(135deg, #ffffff 0%, #f7f3ff 52%, #fbfaff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 28px 16px;
        }

        .login-shell {
            width: 100%;
            max-width: 460px;
        }

        .login-card {
            width: 100%;
            background: rgba(255, 255, 255, 0.96);
            border: 1px solid rgba(221, 214, 254, 0.9);
            border-radius: 22px;
            box-shadow: 0 22px 58px rgba(76, 29, 149, 0.12);
            padding: 38px 40px 30px;
        }

        .brand-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 24px;
        }

        .brand-logo-wrap {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: #f3f0ff;
            border: 1px solid #e9d5ff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .brand-logo {
            width: 46px;
            height: 46px;
            object-fit: contain;
            display: block;
        }

        .brand-copy {
            text-align: left;
            min-width: 0;
        }

        .app-name {
            font-size: 1.04rem;
            font-weight: 700;
            color: #3b0764;
            line-height: 1.25;
        }

        .app-subname {
            margin-top: 4px;
            font-size: 0.84rem;
            font-weight: 600;
            color: #7c3aed;
        }

        .login-title {
            font-size: 1.45rem;
            font-weight: 700;
            color: #1e1140;
            text-align: center;
            margin-bottom: 30px;
        }

        .error-box {
            background: #fff1f2;
            border: 1px solid #fecdd3;
            border-radius: 12px;
            padding: 11px 13px;
            color: #be123c;
            font-size: 0.86rem;
            display: flex;
            align-items: flex-start;
            gap: 9px;
            margin-bottom: 18px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-size: 0.86rem;
            font-weight: 700;
            color: #4c1d95;
            margin-bottom: 8px;
        }

        .input-box {
            display: flex;
            align-items: center;
            min-height: 54px;
            border: 1.5px solid #e9d5ff;
            border-radius: 14px;
            background: #fbfaff;
            padding: 0 14px;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
        }

        .input-box:focus-within {
            border-color: #8b5cf6;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.12);
        }

        .input-box i {
            color: #a78bfa;
            font-size: 1rem;
            margin-right: 10px;
            flex-shrink: 0;
        }

        .input-box input {
            flex: 1;
            width: 100%;
            border: 0;
            outline: 0;
            background: transparent;
            color: #1f2937;
            font-size: 0.94rem;
            padding: 12px 0;
        }

        .input-box input::placeholder {
            color: #c4b5fd;
        }

        .btn-login {
            width: 100%;
            min-height: 52px;
            border: 0;
            border-radius: 14px;
            background: #7c3aed;
            color: #fff;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 20px;
            transition: background 0.2s, box-shadow 0.2s, transform 0.2s;
        }

        .btn-login:hover {
            background: #6d28d9;
            box-shadow: 0 14px 28px rgba(124, 58, 237, 0.25);
            transform: translateY(-1px);
        }

        .login-footer {
            margin-top: 20px;
            text-align: center;
            color: #9ca3af;
            font-size: 0.78rem;
        }

        @media (max-width: 480px) {
            body {
                padding: 18px 12px;
            }

            .login-card {
                border-radius: 18px;
                padding: 30px 22px 24px;
            }

            .brand-header {
                gap: 12px;
            }

            .brand-logo-wrap {
                width: 50px;
                height: 50px;
            }

            .brand-logo {
                width: 44px;
                height: 44px;
            }

            .app-name {
                font-size: 0.9rem;
            }

            .app-subname {
                font-size: 0.78rem;
            }

            .login-title {
                font-size: 1.34rem;
                margin-bottom: 28px;
            }
        }
    </style>
</head>
<body>
    <main class="login-shell">
        <section class="login-card" aria-label="Form login MBKM Fakultas">
            <div class="brand-header">
                <div class="brand-logo-wrap">
                    <img src="{{ asset('assets/img/logo-uin-sidebar.png') }}" alt="Logo UIN" class="brand-logo">
                </div>
                <div class="brand-copy">
                    <div class="app-name">MBKM Fakultas Sains dan Teknologi</div>
                    <div class="app-subname">Sistem Informasi Magang</div>
                </div>
            </div>

            <h1 class="login-title">Login ke Akun Anda</h1>

            @if($errors->any())
                <div class="error-box">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="email">Alamat Email</label>
                    <div class="input-box">
                        <i class="bi bi-envelope-fill" aria-hidden="true"></i>
                        <input id="email" type="email" name="email" placeholder="nama@email.com" value="{{ old('email') }}" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-box">
                        <i class="bi bi-lock-fill" aria-hidden="true"></i>
                        <input id="password" type="password" name="password" placeholder="Masukkan password" required>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i>
                    Masuk
                </button>
            </form>

            <div class="login-footer">&copy; 2026 MBKM Fakultas</div>
        </section>
    </main>
</body>
</html>
