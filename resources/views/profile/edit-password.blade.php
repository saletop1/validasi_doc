@extends('layouts.app')

@section('title', 'Ganti Password')

@push('styles')
<style>
    .form-card {
        max-width: 600px;
        margin: 2rem auto;
        border: none;
        border-radius: 1rem;
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.07);
    }
</style>
@endpush

@section('content')
<div class="container">
    <div class="card form-card">
        <div class="card-header bg-success text-white text-center">
            <h4><i class="fas fa-key me-2"></i>Ubah Password Anda</h4>
        </div>
        <div class="card-body p-4">

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning">
                    {{ session('warning') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                @method('PUT') {{-- --- PERBAIKAN: Menambahkan baris ini --- --}}

                <div class="mb-3">
                    <label for="current_password" class="form-label">Password Saat Ini</label>
                    <input id="current_password" type="password" class="form-control @error('current_password') is-invalid @enderror" name="current_password" required>
                    @error('current_password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="new_password" class="form-label">Password Baru</label>
                    <input id="new_password" type="password" class="form-control @error('new_password') is-invalid @enderror" name="new_password" required>
                    @error('new_password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="new_password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                    <input id="new_password_confirmation" type="password" class="form-control" name="new_password_confirmation" required>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-success btn-lg">
                        Simpan Password Baru
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

