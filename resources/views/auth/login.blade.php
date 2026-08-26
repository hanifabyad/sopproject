<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>e-QMS — Login Portal PT PKM Group</title>
    <meta name="description" content="Electronic Quality Management System — Portal Login PT PKM Group.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,300;0,14..32,400;0,14..32,500;0,14..32,600;0,14..32,700;0,14..32,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            height: 100%;
            font-family: 'Inter', sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        body {
            display: grid;
            grid-template-columns: 1fr;
            min-height: 100vh;
            background-color: #f5f4f0;
        }

        @media (min-width: 900px) {
            body {
                grid-template-columns: 1fr 1fr;
            }
        }

        /* ── LEFT PANEL ── */
        .panel-left {
            display: none;
            background: linear-gradient(160deg, #1677B8 0%, #0e5a8a 60%, #0a3d5e 100%);
            padding: 3rem;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        @media (min-width: 900px) {
            .panel-left {
                display: flex;
            }
        }

        /* Subtle noise texture overlay */
        .panel-left::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.03'/%3E%3C/svg%3E");
            pointer-events: none;
        }

        .panel-left-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            position: relative;
            z-index: 1;
        }

        .panel-left-brand img {
            width: 36px;
            height: 36px;
            object-fit: contain;
            filter: brightness(10);
        }

        .panel-left-brand-text {
            line-height: 1;
        }

        .panel-left-brand-text .name {
            font-size: 13px;
            font-weight: 700;
            color: white;
            letter-spacing: 0.02em;
        }

        .panel-left-brand-text .sub {
            font-size: 10px;
            color: rgba(255,255,255,0.5);
            margin-top: 3px;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .panel-left-content {
            position: relative;
            z-index: 1;
        }

        .panel-left-content h1 {
            font-size: clamp(2rem, 3.5vw, 3rem);
            font-weight: 800;
            color: white;
            line-height: 1.1;
            letter-spacing: -0.03em;
            margin-bottom: 1.25rem;
        }

        .panel-left-content h1 span {
            color: rgba(255,255,255,0.35);
        }

        .panel-left-content p {
            font-size: 13px;
            color: rgba(255,255,255,0.55);
            line-height: 1.75;
            max-width: 340px;
            font-weight: 400;
        }

        .panel-left-footer {
            position: relative;
            z-index: 1;
            font-size: 11px;
            color: rgba(255,255,255,0.25);
        }

        /* Decorative circles */
        .deco-circle {
            position: absolute;
            border-radius: 50%;
            border: 1px solid rgba(255,255,255,0.07);
            pointer-events: none;
        }
        .deco-1 { width: 320px; height: 320px; bottom: -80px; right: -80px; }
        .deco-2 { width: 200px; height: 200px; bottom: 40px; right: 40px; }
        .deco-3 { width: 120px; height: 120px; bottom: 100px; right: 100px; }

        /* ── RIGHT PANEL ── */
        .panel-right {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.5rem;
            background-color: #f5f4f0;
            min-height: 100vh;
        }

        @media (min-width: 900px) {
            .panel-right {
                min-height: auto;
                padding: 3rem 2.5rem;
            }
        }

        .login-box {
            width: 100%;
            max-width: 380px;
        }

        /* Mobile-only brand */
        .mobile-brand {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            margin-bottom: 2.5rem;
        }

        @media (min-width: 900px) {
            .mobile-brand {
                display: none;
            }
        }

        .mobile-brand img {
            width: 32px;
            height: 32px;
            object-fit: contain;
        }

        .mobile-brand-text .name {
            font-size: 12px;
            font-weight: 700;
            color: #1a1a1a;
        }

        .mobile-brand-text .sub {
            font-size: 10px;
            color: #9e9e9e;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-top: 2px;
        }

        /* Heading */
        .login-heading {
            margin-bottom: 2rem;
        }

        .login-heading h2 {
            font-size: 22px;
            font-weight: 700;
            color: #111;
            letter-spacing: -0.02em;
            margin-bottom: 0.4rem;
        }

        .login-heading p {
            font-size: 13px;
            color: #888;
            font-weight: 400;
        }

        /* Error */
        .error-box {
            background: #fff0f0;
            border: 1px solid #fca5a5;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            margin-bottom: 1.25rem;
            font-size: 12px;
            color: #b91c1c;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .error-row {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .error-row .material-symbols-outlined {
            font-size: 14px;
            flex-shrink: 0;
        }

        /* Form */
        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            font-size: 11.5px;
            font-weight: 600;
            color: #555;
            margin-bottom: 0.4rem;
            letter-spacing: 0.01em;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
            color: #bbb;
            pointer-events: none;
            transition: color 0.15s;
        }

        .form-input {
            width: 100%;
            height: 44px;
            padding: 0 12px 0 38px;
            background: white;
            border: 1.5px solid #e2e0dc;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 500;
            color: #111;
            font-family: 'Inter', sans-serif;
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
            -webkit-appearance: none;
        }

        .form-input::placeholder {
            color: #c8c5bf;
            font-weight: 400;
        }

        .form-input:focus {
            border-color: #1677B8;
            box-shadow: 0 0 0 3px rgba(22,119,184,0.12);
        }

        .input-wrap:focus-within .input-icon {
            color: #1677B8;
        }

        /* Submit */
        .btn-submit {
            width: 100%;
            height: 46px;
            margin-top: 0.5rem;
            background: #1677B8;
            border: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            color: white;
            font-family: 'Inter', sans-serif;
            letter-spacing: 0.02em;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            transition: background 0.15s, transform 0.1s, box-shadow 0.15s;
            box-shadow: 0 2px 8px rgba(22,119,184,0.25);
        }

        .btn-submit:hover {
            background: #1260a0;
            box-shadow: 0 4px 16px rgba(22,119,184,0.32);
        }

        .btn-submit:active {
            transform: scale(0.99);
        }

        .btn-submit:disabled {
            opacity: 0.65;
            cursor: not-allowed;
        }

        .btn-submit .material-symbols-outlined {
            font-size: 17px;
        }

        /* Footer */
        .login-footer {
            margin-top: 1.75rem;
            text-align: center;
            font-size: 11.5px;
            color: #aaa;
            line-height: 1.6;
        }

        /* Loading spin */
        @keyframes spin {
            from { transform: rotate(0deg); }
            to   { transform: rotate(360deg); }
        }
        .spin { animation: spin 0.9s linear infinite; display: inline-block; }
    </style>
</head>
<body>

    <!-- LEFT PANEL -->
    <div class="panel-left">
        <!-- Decorative rings -->
        <div class="deco-circle deco-1"></div>
        <div class="deco-circle deco-2"></div>
        <div class="deco-circle deco-3"></div>

        <div class="panel-left-brand">
            <img src="{{ asset('img/logopkm.svg') }}" alt="PKM">
            <div class="panel-left-brand-text">
                <div class="name">e-QMS Portal</div>
                <div class="sub">PT PKM Group</div>
            </div>
        </div>

        <div class="panel-left-content">
            <h1>Quality<br>Management<br><span>System</span></h1>
            <p>Sistem digitalisasi alur pengesahan dokumen mutu, tanda tangan elektronik, dan E-Library terpusat PT PKM Group.</p>
        </div>

        <div class="panel-left-footer">
            © {{ date('Y') }} PT PKM Group · e-QMS
        </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="panel-right">
        <div class="login-box">

            <!-- Mobile brand -->
            <div class="mobile-brand">
                <img src="{{ asset('img/logopkm.svg') }}" alt="PKM">
                <div class="mobile-brand-text">
                    <div class="name">e-QMS Portal</div>
                    <div class="sub">PT PKM Group</div>
                </div>
            </div>

            <div class="login-heading">
                <h2>Masuk ke Akun</h2>
                <p>Masukkan kredensial Anda untuk melanjutkan</p>
            </div>

            @if ($errors->any())
                <div class="error-box" role="alert">
                    @foreach ($errors->all() as $error)
                        <div class="error-row">
                            <span class="material-symbols-outlined">error</span>
                            <span>{{ $error }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <form id="login-form" method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-wrap">
                        <span class="material-symbols-outlined input-icon">person</span>
                        <input
                            id="username"
                            type="text"
                            name="username"
                            class="form-input"
                            placeholder="Masukkan username"
                            value="{{ old('username') }}"
                            autocomplete="username"
                            required
                            autofocus
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-wrap">
                        <span class="material-symbols-outlined input-icon">lock</span>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            class="form-input"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            required
                        >
                    </div>
                </div>

                <button id="btn-login" type="submit" class="btn-submit">
                    <span class="material-symbols-outlined">login</span>
                    <span id="btn-text">Masuk</span>
                </button>
            </form>

            <div class="login-footer">
                Hanya untuk karyawan resmi PT PKM Group.<br>
                Hubungi admin jika tidak bisa login.
            </div>

        </div>
    </div>

    <script>
        document.getElementById('login-form').addEventListener('submit', function () {
            const btn = document.getElementById('btn-login');
            const txt = document.getElementById('btn-text');
            const icon = btn.querySelector('.material-symbols-outlined');
            btn.disabled = true;
            icon.classList.add('spin');
            icon.textContent = 'autorenew';
            txt.textContent = 'Memverifikasi...';
        });
    </script>

</body>
</html>