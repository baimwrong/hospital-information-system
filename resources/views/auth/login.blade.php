<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Medical Staff Login - Hospital Information System</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/hospital.css') }}">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1rem;
        }
        .login-card {
            background: #ffffff;
            border-radius: 1rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 440px;
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #ffffff;
            padding: 2rem;
            text-align: center;
        }
        .demo-chip {
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .demo-chip:hover {
            transform: scale(1.02);
            filter: brightness(0.95);
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <div class="d-inline-flex p-3 rounded-circle bg-white text-primary mb-3 shadow">
                <i class="bi bi-hospital fs-1 text-primary"></i>
            </div>
            <h3 class="fw-bold mb-1">Hospital Information System</h3>
            <p class="text-white-50 mb-0 small">Enterprise Clinical Information System</p>
        </div>

        <div class="p-4">
            @if(session('info'))
                <div class="alert alert-info py-2 small mb-3">{{ session('info') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger py-2 small mb-3">
                    @foreach($errors->all() as $err)
                        <div><i class="bi bi-exclamation-circle me-1"></i> {{ $err }}</div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label small fw-semibold text-secondary">Hospital Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" id="email" class="form-control border-start-0 ps-0" placeholder="staff@hospital.local" value="{{ old('email', 'admin@hospital.local') }}" required autofocus>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label small fw-semibold text-secondary">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" id="password" class="form-control border-start-0 ps-0" placeholder="••••••••" value="password" required>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" checked>
                        <label class="form-check-label small text-muted" for="remember">Keep session active</label>
                    </div>
                    <span class="badge bg-light text-muted border">Local LAN Mode</span>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-box-arrow-in-right"></i> Sign In to Portal
                </button>
            </form>

            <div class="mt-4 pt-3 border-top">
                <div class="text-muted small fw-semibold mb-2 text-center">Quick Demo Credentials:</div>
                <div class="d-flex flex-wrap gap-1 justify-content-center">
                    <button type="button" class="btn btn-outline-secondary btn-sm demo-chip" onclick="fillCreds('admin@hospital.local')">Admin</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm demo-chip" onclick="fillCreds('dr.chen@hospital.local')">Doctor</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm demo-chip" onclick="fillCreds('nurse@hospital.local')">Nurse</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm demo-chip" onclick="fillCreds('pharmacist@hospital.local')">Pharmacist</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm demo-chip" onclick="fillCreds('lab@hospital.local')">Lab Tech</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm demo-chip" onclick="fillCreds('billing@hospital.local')">Billing</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function fillCreds(email) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = 'password';
        }
    </script>
</body>
</html>
