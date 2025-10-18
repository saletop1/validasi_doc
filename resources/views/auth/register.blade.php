@extends('layouts.app')

@section('title', 'Registrasi Pengguna Baru')

@push('styles')
<style>
    body {
        background-color: #f0f2f5;
        font-family: 'Inter', sans-serif;
    }
    .login-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .login-card {
        width: 100%;
        max-width: 450px;
        border: none;
        border-radius: 1rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }
    .login-header {
        background: linear-gradient(135deg, #0d6efd, #0dcaf0);
        color: white;
        text-align: center;
        padding: 2.5rem 1.5rem;
    }
    .login-header .logo {
        height: 70px;
        margin-bottom: 1rem;
    }
    .login-header h2 {
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    .login-body {
        padding: 2.5rem;
        background-color: #ffffff;
    }
    .form-control-lg {
        border-radius: 0.5rem;
        padding: 0.9rem 1.2rem;
    }
    .input-group-text {
        border-radius: 0.5rem 0 0 0.5rem;
    }
    .btn-primary {
        background: linear-gradient(135deg, #0d6efd, #0dcaf0);
        border: none;
        padding: 0.9rem;
        border-radius: 0.5rem;
        font-weight: 600;
        transition: transform 0.2s;
    }
    .btn-primary:hover {
        transform: translateY(-2px);
    }
    .footer-text {
        font-size: 0.85rem;
        color: #6c757d;
    }
    .form-text a {
        color: #0d6efd;
        text-decoration: none;
    }
    .form-text a:hover {
        text-decoration: underline;
    }
</style>
@endpush

@section('content')
<div class="login-container">
    <div class="card login-card">
        <div class="login-header">
            <img src="{{ asset('images/KMI.png') }}" alt="Logo KMI" class="logo">
            <h2>Buat Akun Baru</h2>
            <p class="mb-0">Silakan isi data untuk mendaftar</p>
        </div>
        <div class="login-body">
            <form method="POST" action="{{ route('register.post') }}">
                @csrf

                @if ($errors->any())
                    <div class="alert alert-danger mb-4">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mb-3">
                    <label for="name" class="form-label fw-bold">Username</label>
                    <input id="name" type="text" class="form-control form-control-lg @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus placeholder="Masukkan username">
                </div>

                <!-- Kolom Email Ditambahkan -->
                <div class="mb-3">
                    <label for="email" class="form-label fw-bold">Alamat Email</label>
                    <input id="email" type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="contoh@email.com">
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-bold">Password</label>
                    <input id="password" type="password" class="form-control form-control-lg @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" placeholder="Minimal 8 karakter">
                </div>

                <div class="mb-4">
                    <label for="password-confirm" class="form-label fw-bold">Konfirmasi Password</label>
                    <input id="password-confirm" type="password" class="form-control form-control-lg" name="password_confirmation" required autocomplete="new-password" placeholder="Ketik ulang password">
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-primary btn-lg">
                        Daftar
                    </button>
                </div>

                <p class="text-center form-text">
                    Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a>
                </p>
            </form>
        </div>
        <div class="card-footer text-center py-3">
             <small class="footer-text">&copy; {{ date('Y') }} PT. Kayu Mebel Indonesia</small>
        </div>
    </div>
</div>
@endsection

