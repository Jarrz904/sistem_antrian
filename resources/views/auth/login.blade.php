@extends('layouts.app')

@section('content')
<style>
    /* Background Animasi Halus */
    body {
        background: linear-gradient(135deg, #f4f7fe 0%, #e2e8f0 100%);
        min-height: 100vh;
    }

    /* Card Styling */
    .login-card {
        border-radius: 24px;
        border: 1px solid rgba(255, 255, 255, 0.8);
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        box-shadow: 0 20px 40px rgba(0, 35, 71, 0.1) !important;
        overflow: hidden;
        transition: transform 0.3s ease;
    }

    /* Header Visual */
    .login-header-bg {
        background: linear-gradient(45deg, #002347, #0056b3);
        padding: 40px 20px;
        text-align: center;
        color: white;
    }

    .login-header-bg i {
        font-size: 3rem;
        margin-bottom: 15px;
        color: #00d2ff;
        text-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }

    /* Form Styling */
    .form-label {
        font-weight: 700;
        color: #1e293b; /* Warna gelap tegas */
        font-size: 0.9rem;
        margin-bottom: 8px;
    }

    .form-control {
        border-radius: 12px;
        padding: 12px 16px;
        border: 2px solid #e2e8f0;
        transition: all 0.3s ease;
        background-color: #f8fafc;
        color: #0f172a; /* Text input sangat jelas */
    }

    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.1);
        background-color: #fff;
    }

    /* Button Styling */
    .btn-login {
        border-radius: 12px;
        padding: 12px;
        font-weight: 800;
        letter-spacing: 1px;
        background: linear-gradient(to right, #0056b3, #007bff);
        border: none;
        box-shadow: 0 10px 20px rgba(0, 86, 179, 0.3);
        transition: all 0.3s ease;
    }

    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 25px rgba(0, 86, 179, 0.4);
        filter: brightness(1.1);
    }

    .card-footer {
        background: #f8fafc !important;
        border-top: 1px solid #e2e8f0;
    }

    /* Alert Styling */
    .alert-danger {
        border-radius: 12px;
        border: none;
        background-color: #fff1f2;
        color: #be123c;
        font-weight: 600;
    }
</style>

<div class="container">
    <div class="row justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="col-md-5 col-lg-4">
            
            <div class="card login-card shadow">
                <div class="login-header-bg">
                    <i class="fas fa-shield-halved"></i>
                    <h3 class="fw-800 mb-0">PORTAL LOGIN</h3>
                    <p class="mb-0 opacity-75 small">Sistem Internal Dukcapil Digital</p>
                </div>

                <div class="card-body p-4 p-lg-5">
                    @if($errors->any())
                        <div class="alert alert-danger mb-4 shadow-sm">
                            <ul class="mb-0 small">
                                @foreach($errors->all() as $error)
                                    <li><i class="fas fa-exclamation-circle me-2"></i>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('login') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">USERNAME</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0" style="border-radius: 12px 0 0 12px; border: 2px solid #e2e8f0;">
                                    <i class="fas fa-user text-muted"></i>
                                </span>
                                <input type="text" name="username" class="form-control border-start-0" 
                                       placeholder="Username petugas" required autofocus style="border-radius: 0 12px 12px 0;">
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between">
                                <label class="form-label">PASSWORD</label>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0" style="border-radius: 12px 0 0 12px; border: 2px solid #e2e8f0;">
                                    <i class="fas fa-lock text-muted"></i>
                                </span>
                                <input type="password" name="password" class="form-control border-start-0" 
                                       placeholder="••••••••" required style="border-radius: 0 12px 12px 0;">
                            </div>
                        </div>

                        <div class="mb-4 d-flex justify-content-between align-items-center">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label small fw-bold text-secondary" for="remember cursor-pointer">Ingat Saya</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-login w-100 mb-3">
                            <i class="fas fa-sign-in-alt me-2"></i> MASUK SEKARANG
                        </button>
                    </form>
                </div>

                <div class="card-footer py-3">
                    <div class="text-center">
                        <a href="/" class="text-decoration-none small fw-bold text-primary">
                            <i class="fas fa-arrow-left me-1"></i> Kembali ke Beranda Utama
                        </a>
                    </div>
                </div>
            </div>

            <p class="text-center text-muted mt-4 small">
                &copy; {{ date('Y') }} Dukcapil Digital. <br>
                Secure Internal Access Only.
            </p>

        </div>
    </div>
</div>
@endsection