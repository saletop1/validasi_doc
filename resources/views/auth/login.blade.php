@extends('layouts.app')

@section('title', 'Login Aplikasi Verifikasi DO')

@push('styles')
<style>
    :root {
        --primary-color: #0d6efd;
        --secondary-color: #6c757d;
        --light-gray: #f8f9fa;
        --dark-color: #212529;
        --border-color: #dee2e6;
    }

    .login-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        background-color: var(--light-gray);
        font-family: 'Inter', sans-serif;
    }

    .login-card {
        width: 100%;
        max-width: 450px;
        border: none;
        border-radius: 1rem;
        box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.1);
        overflow: hidden;
        background-color: #ffffff;
    }

    .login-header {
        background: linear-gradient(45deg, var(--primary-color), #0dcaf0);
        color: white;
        padding: 2.5rem;
        text-align: center;
    }

    .login-header img {
        max-width: 80px;
        margin-bottom: 1rem;
    }

    .login-header h2 {
        font-weight: 700;
        margin: 0;
    }

    .login-header p {
        opacity: 0.9;
        font-size: 0.9rem;
    }

    .login-body {
        padding: 2.5rem;
    }

    .form-group {
        position: relative;
        margin-bottom: 1.5rem;
    }

    .form-control {
        height: 50px;
        padding-left: 3rem;
        border: 1px solid var(--border-color);
        border-radius: 0.5rem;
        transition: border-color 0.2s;
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    .form-group .form-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #adb5bd;
    }

    .btn-login {
        width: 100%;
        padding: 0.75rem;
        font-size: 1rem;
        font-weight: 600;
        border-radius: 0.5rem;
        background: linear-gradient(45deg, var(--primary-color), #0dcaf0);
        border: none;
        color: white;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
    }

    .login-footer {
        padding: 1.5rem 2.5rem;
        background-color: #f1f3f5;
        font-size: 0.9rem;
    }

    .forgot-password {
        color: var(--primary-color);
        text-decoration: none;
    }

    .forgot-password:hover {
        text-decoration: underline;
    }

</style>
@endpush

@section('content')
<div class="login-container">
    <div class="card login-card">
        <div class="login-header">
            <!-- Ganti dengan path logo perusahaan Anda -->
            <img src="{{ asset('images/KMI.png') }}" alt="Logo Perusahaan">
            <h2>Selamat Datang</h2>
            <p>Silakan masuk untuk melanjutkan</p>
        </div>
        <div class="login-body">
            <!-- Arahkan action ke route login Anda -->
            <form action="{{-- route('login.post') --}}" method="POST">
                @csrf
                <div class="form-group">
                    <i class="fas fa-user form-icon"></i>
                    <input type="text" class="form-control" name="username" placeholder="Username atau Email" required>
                </div>
                <div class="form-group">
                     <i class="fas fa-lock form-icon"></i>
                    <input type="password" class="form-control" name="password" placeholder="Password" required>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label" for="remember">
                            Ingat Saya
                        </label>
                    </div>
                    <a href="#" class="forgot-password">Lupa Password?</a>
                </div>

                <button type="submit" class="btn btn-login">Masuk</button>
            </form>

        </div>
         <div class="login-footer text-center">
            <p class="text-muted mb-0">PT. Kayu Mebel Indonesia &copy; {{ date('Y') }}</p>
        </div>
    </div>
</div>
@endsection
