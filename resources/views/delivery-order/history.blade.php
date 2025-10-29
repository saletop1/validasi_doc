@extends('layouts.app')

@section('title', 'Riwayat Verifikasi DO')

@push('styles')
<style>
    /* ... (style Anda sebelumnya tidak berubah) ... */
     body {
        background-color: #f8f9fa;
        font-family: 'Inter', sans-serif;
    }
    .main-card {
        border: none;
        border-radius: 1.25rem;
        box-shadow: 0 0.75rem 2rem rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }
    .card-header-custom {
        background: linear-gradient(135deg, #1ddec8 0%, #17624a 100%);
        color: white;
        padding: 2rem 1.5rem;
        text-align: center;
        position: relative;
    }
    .card-header-custom::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%2315a493' fill-opacity='0.05' fill-rule='evenodd'/%3E%3C/svg%3E");
        opacity: 0.3;
    }
    .history-card {
        border: 1px solid #e9ecef;
        border-radius: 0.875rem;
        margin-bottom: 1.25rem;
        transition: all 0.3s ease;
        cursor: pointer; /* Kembalikan cursor pointer */
        position: relative;
        overflow: hidden;
    }
    .history-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: linear-gradient(to bottom, #1ddec8, #17624a);
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .history-card:hover {
        box-shadow: 0 10px 30px rgba(0,0,0,0.12);
        transform: translateY(-5px);
        border-color: #1ddec8;
    }
    .history-card:hover::before {
        opacity: 1;
    }
    .history-card .card-body {
        padding: 1.5rem;
    }
    .do-number {
        font-size: 1.25rem;
        font-weight: 700;
        color: #17624a;
    }
    .customer-name {
        font-weight: 600;
        color: #212529;
        margin-bottom: 0.5rem;
    }
    .verification-date {
        font-size: 0.9rem;
        color: #6c757d;
    }
    .filter-section {
        background: #f8f9fa;
        border-radius: 0.75rem;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }
    .filter-input {
        border-radius: 0.5rem;
        border: 1px solid #dee2e6;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
    }
    .filter-input:focus {
        border-color: #1ddec8;
        box-shadow: 0 0 0 0.2rem rgba(29, 222, 200, 0.25);
    }
    .summary-box {
        color: white;
        border-radius: 0.75rem;
        padding: 1.25rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    .summary-box h6 {
        color: rgba(255, 255, 255, 0.9);
        font-size: 0.85rem;
        margin-bottom: 0.5rem;
    }
    .summary-success {
        background: linear-gradient(135deg, #2E7D32, #4CAF50);
    }
    .summary-danger {
        background: linear-gradient(135deg, #c6b928, #d4c63c);
    }
    .summary-warning {
        background: linear-gradient(135deg, #f57c00, #ff9800);
    }
     /* Tambahkan style untuk summary abu-abu (Menunggu tanpa scan) */
    .summary-secondary {
        background: linear-gradient(135deg, #6c757d, #adb5bd);
    }

    .badge-status {
        font-size: 0.75rem;
        padding: 0.35rem 0.75rem;
        border-radius: 1rem;
    }
    .empty-state {
        padding: 3rem 2rem;
        text-align: center;
        color: #6c757d;
    }
    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        color: #dee2e6;
    }
    .nav-tabs {
        border-bottom: 1px solid #e9ecef;
        margin-bottom: 1.5rem;
    }
    .nav-tabs .nav-link {
        color: #6c757d;
        border: none;
        border-bottom: 3px solid transparent;
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    .nav-tabs .nav-link:hover {
        color: #17624a;
        border-bottom-color: rgba(29, 222, 200, 0.5);
    }
    .nav-tabs .nav-link.active {
        color: #17624a;
        font-weight: 600;
        background-color: transparent;
        border-bottom: 3px solid #1ddec8;
    }
    .tab-content {
        min-height: 400px; /* Jaga tinggi minimum untuk semua tab */
    }
    .history-count {
        background: #e9ecef;
        border-radius: 1rem;
        padding: 0.25rem 0.75rem;
        font-size: 0.75rem;
        font-weight: 600;
        color: #495057;
        margin-left: 0.5rem;
    }
    .sort-options {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
    }
    .modal-content {
        border: none;
        border-radius: 1rem;
        overflow: hidden;
    }
    .modal-header {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        border-bottom: 1px solid #dee2e6;
        padding: 1.25rem 1.5rem;
    }
    .modal-title {
        color: #17624a;
        font-weight: 600;
    }
    .modal-body {
        padding: 0;
    }
    .table-responsive {
        max-height: 60vh;
        overflow-y: auto;
    }
    .table {
        margin-bottom: 0;
    }
    .table thead th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        color: #495057;
        padding: 1rem 0.75rem;
        position: sticky;
        top: 0;
        z-index: 10;
    }
     #print-header-info {
         display: none;
     }
    .print-summary-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
        padding: 15px;
        border: 1px solid #eee;
        border-radius: 8px;
    }
    .print-summary-grid h6 { margin-bottom: 5px; color: #555; font-size: 10px; text-transform: uppercase;}
     .print-summary-grid p, .print-summary-grid h4 { margin: 0; font-size: 12px; }
     .print-summary-grid h4 { font-weight: bold; }

    @media print {
        body { margin: 20px; font-size: 10pt; }
        .modal-header, .modal-footer, #modal-loader, .btn, .nav-pills { display: none !important; } /* Sembunyikan sub-nav saat print */
        .modal-content { box-shadow: none; border-radius: 0; }
        .modal-body { padding: 0 !important; }
        .table-responsive { max-height: none; overflow: visible; }
        .table thead th { position: static; background-color: #f2f2f2 !important; -webkit-print-color-adjust: exact; }
        .table { width: 100%; border-collapse: collapse; margin-top: 15px;}
        .table th, .table td { border: 1px solid #ccc; padding: 6px;}
        .summary-box { display: none; }
        #print-header-info { display: block !important; }
        .print-summary-grid {
             grid-template-columns: 1fr 1fr;
             gap: 10px;
             margin-bottom: 15px;
             padding: 10px;
             border: 1px solid #ccc;
             background-color: #f8f9fa !important;
            -webkit-print-color-adjust: exact;
         }
         .print-summary-grid div {
             padding-bottom: 3px;
             border-bottom: 1px dotted #ddd;
         }
          .print-summary-grid h6 {
              margin-bottom: 2px;
              color: #555;
              font-size: 10px;
              text-transform: uppercase;
              font-weight: normal;
          }
          .print-summary-grid p, .print-summary-grid h4 {
              margin: 0;
              font-size: 12px;
          }
          .print-summary-grid h4 { font-weight: bold; }
         tr { page-break-inside: avoid; }
    }

    .table tbody td {
        padding: 0.875rem 0.75rem;
        vertical-align: middle;
    }
    .table tbody tr:hover {
        background-color: rgba(29, 222, 200, 0.05);
    }
    .material-number {
        font-weight: 600;
        color: #17624a;
    }
    .material-description {
        font-size: 0.875rem;
        color: #6c757d;
    }
    .qty-match {
        color: #2E7D32;
        font-weight: 600;
    }
    .qty-mismatch {
        color: #c62828;
        font-weight: 600;
    }
    .status-indicator {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 0.5rem;
    }
    .status-completed {
        background-color: #4CAF50;
    }
    .status-inprogress {
        background-color: #ff9800;
    }
    .status-waiting {
        background-color: #6c757d;
    }
    .loading-spinner {
        display: inline-block;
        width: 1.5rem;
        height: 1.5rem;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #1ddec8;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-right: 0.5rem;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
     /* Style untuk pesan warning di modal */
    .modal-warning-message {
        padding: 2rem;
        text-align: center;
        background-color: #fff3cd; /* Warna kuning muda */
        color: #664d03; /* Warna teks coklat tua */
        border-bottom: 1px solid #ffc107; /* Border kuning */
    }
    .modal-warning-message i {
        font-size: 2rem;
        margin-bottom: 0.75rem;
        color: #ffc107; /* Warna ikon kuning */
    }

    @media (max-width: 768px) {
        .card-header-custom {
            padding: 1.5rem 1rem;
        }
        .filter-section {
            padding: 1rem;
        }
        .history-card .card-body {
            padding: 1.25rem;
        }
        .nav-tabs .nav-link {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }
         /* Tampilan sub-nav di mobile */
        .nav-pills .nav-link {
            padding: 0.5rem 0.75rem;
            font-size: 0.8rem;
        }
    }

     /* --- Style untuk Sub-Navigasi (Pills) --- */
    .nav-pills {
        margin-bottom: 1.5rem; /* Jarak bawah dari pills ke konten */
        background-color: #e9ecef; /* Latar belakang abu-abu muda */
        border-radius: 0.5rem; /* Sedikit rounded corner */
        padding: 0.3rem; /* Padding internal */
        display: inline-flex; /* Agar pas dengan konten */
    }
    .nav-pills .nav-link {
        color: #495057; /* Warna teks abu-abu gelap */
        border-radius: 0.375rem; /* Rounded corner untuk link */
        padding: 0.5rem 1rem; /* Padding link */
        margin: 0 0.1rem; /* Jarak antar link */
        font-weight: 500;
        transition: background-color 0.3s ease, color 0.3s ease; /* Transisi halus */
    }
    .nav-pills .nav-link:hover {
        background-color: #dee2e6; /* Warna hover sedikit lebih gelap */
    }
    .nav-pills .nav-link.active {
        background-color: #17624a; /* Warna hijau tua saat aktif */
        color: white; /* Teks putih saat aktif */
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Sedikit shadow saat aktif */
    }
     /* Konten di dalam sub-tab */
    .waiting-plant-content .tab-pane {
         /* Tidak perlu style khusus lagi jika row sudah ada */
    }

</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="card main-card">
        <div class="card-header card-header-custom">
            <h2 class="mb-0 h3 fw-bold"><i class="fas fa-history me-2"></i>Riwayat Verifikasi Delivery Order</h2>
            <p class="mb-0 mt-2 opacity-75">Pantau dan kelola semua riwayat verifikasi DO dalam satu tempat</p>
        </div>
        <div class="card-body p-4 p-md-5">
            <!-- Header dengan Filter dan Navigasi -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <a href="{{ route('do.verify.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali ke Verifikasi
                </a>
                <div class="d-flex align-items-center">
                    <span class="me-2 text-muted">Urutkan:</span>
                    <select class="form-select form-select-sm sort-options" id="history-sort">
                        <option value="newest">Terbaru</option>
                        <option value="oldest">Terlama</option>
                        <option value="do-asc">No. DO (A-Z)</option>
                        <option value="do-desc">No. DO (Z-A)</option>
                    </select>
                </div>
            </div>

            <!-- Section Filter -->
            <div class="filter-section">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" id="history-filter" class="form-control filter-input border-start-0" placeholder="Cari berdasarkan No. DO atau Nama Pelanggan...">
                        </div>
                    </div>
                    <div class="col-md-4 mt-2 mt-md-0">
                        <div class="d-flex justify-content-end">
                            <span class="text-muted me-2">Total:</span>
                            <span class="fw-bold" id="total-count">0</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigasi Tab Utama -->
            <ul class="nav nav-tabs" id="historyTab" role="tablist">
                {{-- Urutan: Selesai, Menunggu, Dalam Proses --}}
                 </li>
                 <li class="nav-item" role="presentation">
                    <button class="nav-link" id="waiting-tab" data-bs-toggle="tab" data-bs-target="#waiting-panel" type="button" role="tab" aria-controls="waiting-panel" aria-selected="false">
                        Menunggu
                        {{-- Hitung total dari semua plant --}}
                        <span class="history-count">{{ $waitingDos->sum(fn($items) => $items->count()) }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="inprogress-tab" data-bs-toggle="tab" data-bs-target="#inprogress-panel" type="button" role="tab" aria-controls="inprogress-panel" aria-selected="false">
                        Dalam Proses
                        <span class="history-count">{{ $inProgressDos->count() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed-panel" type="button" role="tab" aria-controls="completed-panel" aria-selected="true">
                        Selesai
                        <span class="history-count">{{ $completedDos->count() }}</span>
                    </button>
            </ul>

            <!-- Konten Tab Utama -->
            <div class="tab-content" id="historyTabContent">
                <!-- Panel Selesai -->
                <div class="tab-pane fade show active" id="completed-panel" role="tabpanel" aria-labelledby="completed-tab">
                    <div class="row history-list" id="completed-list">
                        @forelse ($completedDos as $items)
                            <div class="col-lg-6 history-item" data-filter-text="{{ strtolower($items->VBELN . ' ' . $items->NAME1) }}" data-date="{{ \Carbon\Carbon::parse($items->VERIFIED_AT)->timestamp }}">
                                {{-- Card ini bisa diklik --}}
                                <div class="card history-card" data-bs-toggle="modal" data-bs-target="#historyDetailModal" data-do-number="{{ $items->VBELN }}">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <p class="do-number mb-0">{{ $items->VBELN }}</p>
                                            <span class="badge bg-success badge-status">Selesai</span>
                                        </div>
                                        <p class="customer-name">{{ $items->NAME1 }}</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <p class="verification-date mb-0">
                                                <i class="fas fa-check-circle me-1 text-success"></i>
                                                {{ \Carbon\Carbon::parse($items->VERIFIED_AT)->timezone('Asia/Jakarta')->format('d F Y H:i') }}
                                            </p>
                                            <i class="fas fa-chevron-right text-muted"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 original-empty-state">
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <h4 class="text-muted">Belum ada riwayat verifikasi yang selesai</h4>
                                    <p class="mb-0">Semua verifikasi DO yang telah selesai akan muncul di sini</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Panel Menunggu -->
                <div class="tab-pane fade" id="waiting-panel" role="tabpanel" aria-labelledby="waiting-tab">
                     {{-- Cek apakah ada data sama sekali --}}
                    @if ($waitingDos->isEmpty())
                        <div class="col-12 original-empty-state">
                            <div class="empty-state">
                                <i class="fas fa-hourglass-start"></i>
                                <h4 class="text-muted">Tidak ada DO yang menunggu verifikasi</h4>
                                <p class="mb-0">Data DO yang belum diverifikasi akan muncul di sini setelah diambil dari SAP.</p>
                                <small>(Pastikan proses pengambilan data otomatis berjalan)</small>
                            </div>
                        </div>
                    @else
                        {{-- Navigasi Sub-Tab (Pills) untuk Plant --}}
                        <ul class="nav nav-pills mb-3" id="waitingPlantTab" role="tablist">
                            {{-- Tab untuk Surabaya (2000) --}}
                             @php $plant2000 = $waitingDos->get('2000'); @endphp
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $plant2000 ? 'active' : '' }}" id="waiting-plant-2000-tab" data-bs-toggle="pill" data-bs-target="#waiting-plant-2000-panel" type="button" role="tab" aria-controls="waiting-plant-2000-panel" aria-selected="{{ $plant2000 ? 'true' : 'false' }}">
                                    Surabaya (2000)
                                    <span class="history-count">{{ $plant2000 ? $plant2000->count() : 0 }}</span>
                                </button>
                            </li>
                             {{-- Tab untuk Semarang (3000) --}}
                            @php $plant3000 = $waitingDos->get('3000'); @endphp
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ !$plant2000 && $plant3000 ? 'active' : '' }}" id="waiting-plant-3000-tab" data-bs-toggle="pill" data-bs-target="#waiting-plant-3000-panel" type="button" role="tab" aria-controls="waiting-plant-3000-panel" aria-selected="{{ !$plant2000 && $plant3000 ? 'true' : 'false' }}">
                                    Semarang (3000)
                                    <span class="history-count">{{ $plant3000 ? $plant3000->count() : 0 }}</span>
                                </button>
                            </li>
                            {{-- Anda bisa menambahkan plant lain di sini jika perlu --}}
                        </ul>

                         {{-- Konten Sub-Tab Plant --}}
                        <div class="tab-content waiting-plant-content" id="waitingPlantTabContent">
                            {{-- Konten Plant 2000 (Surabaya) --}}
                            <div class="tab-pane fade {{ $plant2000 ? 'show active' : '' }}" id="waiting-plant-2000-panel" role="tabpanel" aria-labelledby="waiting-plant-2000-tab">
                                <div class="row history-list" id="waiting-list-2000">
                                    @forelse ($plant2000 ?? [] as $items)
                                        <div class="col-lg-6 history-item" data-filter-text="{{ strtolower($items->delivery_number . ' ' . $items->customer_name) }}" data-date="{{ \Carbon\Carbon::parse($items->created_at)->timestamp }}">
                                            {{-- PERUBAHAN: Tambahkan atribut modal & hapus style cursor --}}
                                            <div class="card history-card" data-bs-toggle="modal" data-bs-target="#historyDetailModal" data-do-number="{{ $items->delivery_number }}">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <p class="do-number mb-0">{{ $items->delivery_number }}</p>
                                                        <span class="badge bg-secondary badge-status">Menunggu</span>
                                                    </div>
                                                    <p class="customer-name">{{ $items->customer_name }}</p>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <p class="verification-date mb-0">
                                                            <i class="fas fa-hourglass-start me-1 text-secondary"></i>
                                                            Data diambil: {{ \Carbon\Carbon::parse($items->created_at)->timezone('Asia/Jakarta')->format('d F Y H:i') }}
                                                        </p>
                                                        {{-- PERUBAHAN: Tambahkan ikon chevron --}}
                                                        <i class="fas fa-chevron-right text-muted"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-12 original-empty-state"> {{-- Tambahkan class original-empty-state --}}
                                             <div class="empty-state" style="padding: 1.5rem 1rem;"> {{-- Perkecil padding empty state di sub-tab --}}
                                                <i class="fas fa-inbox"></i>
                                                <h5 class="text-muted">Tidak ada DO menunggu untuk Surabaya</h5>
                                            </div>
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                             {{-- Konten Plant 3000 (Semarang) --}}
                            <div class="tab-pane fade {{ !$plant2000 && $plant3000 ? 'show active' : '' }}" id="waiting-plant-3000-panel" role="tabpanel" aria-labelledby="waiting-plant-3000-tab">
                                <div class="row history-list" id="waiting-list-3000">
                                    @forelse ($plant3000 ?? [] as $items)
                                        <div class="col-lg-6 history-item" data-filter-text="{{ strtolower($items->delivery_number . ' ' . $items->customer_name) }}" data-date="{{ \Carbon\Carbon::parse($items->created_at)->timestamp }}">
                                             {{-- PERUBAHAN: Tambahkan atribut modal & hapus style cursor --}}
                                            <div class="card history-card" data-bs-toggle="modal" data-bs-target="#historyDetailModal" data-do-number="{{ $items->delivery_number }}">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <p class="do-number mb-0">{{ $items->delivery_number }}</p>
                                                        <span class="badge bg-secondary badge-status">Menunggu</span>
                                                    </div>
                                                    <p class="customer-name">{{ $items->customer_name }}</p>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <p class="verification-date mb-0">
                                                            <i class="fas fa-hourglass-start me-1 text-secondary"></i>
                                                            Data diambil: {{ \Carbon\Carbon::parse($items->created_at)->timezone('Asia/Jakarta')->format('d F Y H:i') }}
                                                        </p>
                                                         {{-- PERUBAHAN: Tambahkan ikon chevron --}}
                                                        <i class="fas fa-chevron-right text-muted"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                         <div class="col-12 original-empty-state"> {{-- Tambahkan class original-empty-state --}}
                                             <div class="empty-state" style="padding: 1.5rem 1rem;"> {{-- Perkecil padding empty state di sub-tab --}}
                                                <i class="fas fa-inbox"></i>
                                                <h5 class="text-muted">Tidak ada DO menunggu untuk Semarang</h5>
                                            </div>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                             {{-- Anda bisa menambahkan panel plant lain di sini jika perlu --}}
                        </div>
                    @endif
                </div>


                <!-- Panel Dalam Proses -->
                <div class="tab-pane fade" id="inprogress-panel" role="tabpanel" aria-labelledby="inprogress-tab">
                    <div class="row history-list" id="inprogress-list">
                         {{-- --- PERBAIKAN: Menggunakan $items langsung --- --}}
                        @forelse ($inProgressDos as $items)
                            <div class="col-lg-6 history-item" data-filter-text="{{ strtolower($items->VBELN . ' ' . $items->NAME1) }}" data-date="{{ \Carbon\Carbon::parse($items->updated_at)->timestamp }}">
                                {{-- Card ini bisa diklik --}}
                                <div class="card history-card" data-bs-toggle="modal" data-bs-target="#historyDetailModal" data-do-number="{{ $items->VBELN }}">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <p class="do-number mb-0">{{ $items->VBELN }}</p>
                                            <span class="badge bg-warning text-dark badge-status">Dalam Proses</span>
                                        </div>
                                        <p class="customer-name">{{ $items->NAME1 }}</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <p class="verification-date mb-0">
                                                <i class="fas fa-sync-alt me-1 text-warning"></i>
                                                Aktivitas terakhir: {{ \Carbon\Carbon::parse($items->updated_at)->timezone('Asia/Jakarta')->format('d F Y H:i') }}
                                            </p>
                                            <i class="fas fa-chevron-right text-muted"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 original-empty-state">
                                <div class="empty-state">
                                    <i class="fas fa-clock"></i>
                                    <h4 class="text-muted">Tidak ada verifikasi yang sedang dalam proses</h4>
                                    <p class="mb-0">Semua verifikasi DO yang sedang berjalan akan muncul di sini</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="historyDetailModal" tabindex="-1" aria-labelledby="historyDetailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="historyDetailModalLabel">
            <i class="fas fa-file-invoice me-2"></i>
            Detail Verifikasi DO: <span id="modal-do-number" class="text-primary"></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
       <div class="modal-body p-0">
         <div id="modal-loader" class="text-center py-5">
             <div class="spinner-border text-primary" role="status">
                 <span class="visually-hidden">Loading...</span>
             </div>
             <p class="mt-2 text-muted">Memuat data verifikasi...</p>
         </div>
         <div id="modal-content-area" class="d-none">
             {{-- Container untuk Info Header Cetak --}}
             <div id="print-header-info" class="p-4 border-bottom"></div>
             {{-- Container untuk Summary Box (Tetap Tampil di Layar) --}}
             <div id="modal-summary-area" class="p-4 border-bottom"></div>
             {{-- PERUBAHAN: Container untuk pesan warning atau tabel --}}
             <div id="modal-detail-content">
                 {{-- Tabel akan dirender di sini oleh JS --}}
             </div>
         </div>
       </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary d-none me-auto" id="process-do-button">
            <i class="fas fa-play me-2"></i>Proses Verifikasi
        </button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times me-2"></i>Tutup
        </button>
        <button type="button" class="btn btn-outline-primary d-none" id="print-details">
            <i class="fas fa-print me-2"></i>Cetak
        </button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterInput = document.getElementById('history-filter');
        const sortSelect = document.getElementById('history-sort');
        const historyDetailModal = document.getElementById('historyDetailModal');
        const modalLoader = document.getElementById('modal-loader');
        const modalContent = document.getElementById('modal-content-area');
        const modalDoNumber = document.getElementById('modal-do-number');
        // PERUBAHAN: Ganti modalTableBody menjadi modalDetailContent
        const modalDetailContent = document.getElementById('modal-detail-content');
        const modalSummaryArea = document.getElementById('modal-summary-area');
        const printHeaderInfo = document.getElementById('print-header-info');
        const totalCountElement = document.getElementById('total-count');
        const printButton = document.getElementById('print-details');
        const processDoButton = document.getElementById('process-do-button'); // TAMBAHKAN INI
        let currentHeaderData = null; // Simpan data header saat ini

        updateTotalCount(); // Panggil saat load

        function updateTotalCount() {
            const activeTabPane = document.querySelector('.tab-pane.active'); // Tab Utama (Selesai/Menunggu/Proses)
            if (activeTabPane) {
                 // Cek apakah ini tab Menunggu (yang punya sub-tab)
                const activeSubTabPane = activeTabPane.querySelector('.tab-pane.active'); // Sub-Tab (Plant 2000/3000)
                const targetPane = activeSubTabPane || activeTabPane; // Gunakan sub-tab jika ada, jika tidak, gunakan tab utama

                const visibleItems = targetPane.querySelectorAll('.history-list .history-item:not([style*="display: none"])');
                totalCountElement.textContent = visibleItems.length;
            }
        }


        filterInput.addEventListener('keyup', function() {
            const filterText = this.value.toLowerCase();
            const activeTabPane = document.querySelector('.tab-pane.active'); // Tab Utama
            if (activeTabPane) {
                 // Cek apakah ini tab Menunggu
                const activeSubTabPane = activeTabPane.querySelector('.tab-pane.active'); // Sub-Tab
                const targetPane = activeSubTabPane || activeTabPane; // Targetkan sub-tab jika ada

                const historyLists = targetPane.querySelectorAll('.history-list'); // Ambil list di dalam pane target
                let totalVisibleCount = 0;

                 // Sembunyikan empty state global dulu
                 let originalEmptyStateGlobal = activeTabPane.querySelector(':scope > .original-empty-state'); // Hanya direct child

                 if (originalEmptyStateGlobal) originalEmptyStateGlobal.style.display = 'none';


                historyLists.forEach(list => {
                    const historyItems = list.querySelectorAll('.history-item');
                    let listVisibleCount = 0;

                    historyItems.forEach(item => {
                        const itemText = item.getAttribute('data-filter-text');
                        if (itemText.includes(filterText)) {
                            item.style.display = '';
                            listVisibleCount++;
                        } else {
                            item.style.display = 'none';
                        }
                    });

                    totalVisibleCount += listVisibleCount;

                    // Handle pesan 'Tidak ada hasil' untuk setiap list
                    let noResultMessage = list.querySelector('.no-result-message');
                     let originalListEmptyState = list.querySelector('.original-empty-state'); // empty state sub-tab/list

                    // Sembunyikan empty state asli list jika filter aktif dan ada item di list tsb
                     if (originalListEmptyState) {
                         originalListEmptyState.style.display = (filterText !== '' && historyItems.length > 0) ? 'none' : '';
                     }


                    // Tampilkan pesan 'no result' HANYA jika list itu punya item tapi tidak ada yang cocok filter
                    if (listVisibleCount === 0 && historyItems.length > 0) {
                         if (!noResultMessage) {
                            const emptyHtml = `<div class="col-12 no-result-message"><div class="empty-state" style="padding: 1.5rem 1rem;"><i class="fas fa-search"></i><h5 class="text-muted">Tidak ada hasil ditemukan</h5><p class="mb-0 small">Coba ubah kata kunci pencarian</p></div></div>`;
                            list.insertAdjacentHTML('beforeend', emptyHtml);

                        } else {
                             // Jika ada pesan 'no result', tampilkan
                            noResultMessage.style.display = '';
                        }
                         // Sembunyikan juga empty state asli jika no result tampil
                         if(originalListEmptyState) originalListEmptyState.style.display = 'none';

                    } else {
                        if (noResultMessage) noResultMessage.style.display = 'none'; // Sembunyikan 'no result' jika ada hasil
                         // Tampilkan empty state asli JIKA filter kosong DAN memang list itu kosong
                         if (filterText === '' && historyItems.length === 0 && originalListEmptyState){
                             originalListEmptyState.style.display = '';
                         }
                    }


                });

                // Tampilkan original empty state global JIKA filter kosong DAN memang tidak ada item sama sekali di tab itu
                if (filterText === '' && activeTabPane.querySelectorAll('.history-item').length === 0 && originalEmptyStateGlobal) {
                    originalEmptyStateGlobal.style.display = '';
                }

                // Update total count BUKAN berdasarkan totalVisibleCount tapi panggil updateTotalCount()
                // karena total count harus merefleksikan pane yang aktif
                 updateTotalCount();
            }
        });


        sortSelect.addEventListener('change', function() {
            const sortValue = this.value;
            const activeTabPane = document.querySelector('.tab-pane.active'); // Tab Utama
            if (activeTabPane) {
                // Cek apakah ini tab Menunggu
                const activeSubTabPane = activeTabPane.querySelector('.tab-pane.active'); // Sub-Tab
                const targetPane = activeSubTabPane || activeTabPane; // Targetkan sub-tab jika ada

                const historyLists = targetPane.querySelectorAll('.history-list');

                historyLists.forEach(historyList => {
                    const historyItems = Array.from(historyList.querySelectorAll('.history-item'));

                    historyItems.sort((a, b) => {
                        // Ambil nomor DO dari elemen yang benar di dalam kartu
                         const aCard = a.querySelector('.card');
                         const bCard = b.querySelector('.card');
                         // Tentukan elemen teks DO (bisa '.do-number' atau langsung di card jika beda struktur)
                         const aTextElement = aCard ? aCard.querySelector('.do-number') : null;
                         const bTextElement = bCard ? bCard.querySelector('.do-number') : null;

                         // Handle jika elemen tidak ditemukan
                         const aText = aTextElement ? aTextElement.textContent : '';
                         const bText = bTextElement ? bTextElement.textContent : '';


                        const aDate = parseInt(a.getAttribute('data-date')) || 0; // Fallback ke 0 jika null
                        const bDate = parseInt(b.getAttribute('data-date')) || 0; // Fallback ke 0 jika null

                        switch(sortValue) {
                            case 'newest': return bDate - aDate;
                            case 'oldest': return aDate - bDate;
                            case 'do-asc': return aText.localeCompare(bText);
                            case 'do-desc': return bText.localeCompare(aText);
                            default: return 0;
                        }
                    });

                    // Kosongkan list dan tambahkan kembali item yang sudah diurutkan
                    // Gunakan innerHTML = '' agar lebih bersih
                    historyList.innerHTML = '';
                    historyItems.forEach(item => { historyList.appendChild(item); });
                });


                // Panggil ulang filter setelah sorting agar pesan 'no result' benar
                filterInput.dispatchEvent(new Event('keyup'));
            }
        });

        historyDetailModal.addEventListener('show.bs.modal', async function (event) {
            const card = event.relatedTarget;
             // Verifikasi awal: hanya untuk debugging, bisa dihapus nanti
            if (!card) {
                console.error("Modal event triggered without relatedTarget (card).");
                event.preventDefault();
                return;
            }
             // Cek apakah card memiliki atribut data-do-number
            if (!card.hasAttribute('data-do-number')) {
                 console.warn("Card clicked does not have data-do-number attribute. Modal prevented.");
                event.preventDefault(); // Batalkan pembukaan modal
                return;
            }

            const doNumber = card.getAttribute('data-do-number');
            modalDoNumber.textContent = doNumber;
             // PERUBAHAN: Kosongkan modalDetailContent
            modalDetailContent.innerHTML = '';
            modalSummaryArea.innerHTML = ''; // Kosongkan summary
            printHeaderInfo.innerHTML = ''; // Kosongkan print header
            currentHeaderData = null; // Reset data header
            modalLoader.classList.remove('d-none'); // Tampilkan loader
            modalContent.classList.add('d-none'); // Sembunyikan konten utama
            printButton.classList.add('d-none'); // Sembunyikan tombol cetak
            processDoButton.classList.add('d-none');

            try {
                const url = `{{ url('/delivery-order/history/details') }}/${doNumber}`;
                const response = await fetch(url);
                if (!response.ok) {
                    const errorJson = await response.json().catch(() => ({})); // Tangkap jika respons bukan JSON
                    throw new Error(errorJson.error || `Gagal mengambil data dari server (Status: ${response.status})`);
                }
                const data = await response.json();
                currentHeaderData = data.header || {}; // Simpan data header

                // --- LOGIKA STATUS TERPADU ---
                const isWaitingOnly = currentHeaderData.status === 'waiting';
                const summary = data.summary;
                const items = data.items;
                const totalScan = (summary && summary.total_scan !== undefined) ? parseInt(summary.total_scan) : 0;
                const totalOrder = (summary && summary.total_order !== undefined) ? parseInt(summary.total_order) : 0;

                // Ini adalah KUNCI utamanya:
                // Tampilkan layout "Menunggu" jika statusnya 'waiting' ATAU jika 'totalScan' masih 0
                const showAsWaitingLayout = isWaitingOnly || (!isWaitingOnly && totalScan === 0);

                // Tentukan visibilitas tombol
                const shouldShowProcessButton = showAsWaitingLayout;
                const shouldShowPrintButton = !showAsWaitingLayout && items && items.length > 0;
                // --- AKHIR LOGIKA TERPADU ---


                // --- LOGIKA RENDER ---
                let summaryHtml = '';
                let printHeaderHtml = '';
                let statusText = 'N/A';

                // 1. RENDER SUMMARY (BERDASARKAN LOGIKA BARU)
                if (showAsWaitingLayout) {
                    // Tampilkan layout "Menunggu Scan" (Gambar 1)
                    statusText = isWaitingOnly ? 'MENUNGGU SCAN' : 'BELUM DISCAN';
                    let infoText = isWaitingOnly ? 'DO ini belum masuk proses verifikasi.' : 'DO ini sudah dibuka namun belum discan.';

                    summaryHtml = `
                        <div class="row text-center summary-box summary-secondary">
                            <div class="col-12">
                                <h6>STATUS</h6>
                                <h4 class="fw-bold mb-0">${statusText}</h4>
                                <small>${infoText}</small>
                            </div>
                        </div>`;
                     printHeaderHtml = `
                        <div class="print-summary-grid">
                            <div><h6>Pelanggan</h6><p>${currentHeaderData.customer || 'N/A'}</p></div>
                             <div><h6>Status Verifikasi</h6><p>${statusText}</p></div>
                             <div><h6>No. DO</h6><h4>${doNumber}</h4></div>
                             <div><h6>Alamat</h6><p>N/A</p></div>
                             <div><h6>Total Qty Order</h6><h4>N/A</h4></div>
                            <div><h6>Total Qty Scan</h6><h4>N/A</h4></div>
                        </div>`;

                } else if (summary) {
                    // Tampilkan layout Selesai/Dalam Proses (scan > 0)
                    const isComplete = currentHeaderData.VERIFIED_AT != null;

                    let summaryClass = 'summary-danger';
                    statusText = 'BELUM DIMULAI';
                    if (isComplete) {
                        summaryClass = 'summary-success';
                        statusText = 'SELESAI';
                    } else if (totalScan > 0) { // Ini pasti true jika !showAsWaitingLayout
                        summaryClass = 'summary-warning';
                        statusText = 'PROSES';
                    }

                    summaryHtml = `
                        <div class="row text-center summary-box ${summaryClass}">
                            <div class="col-4">
                                <h6>TOTAL QTY ORDER</h6>
                                <h3 class="fw-bold mb-0">${totalOrder.toLocaleString()}</h3>
                            </div>
                            <div class="col-4">
                                <h6>TOTAL QTY SCAN</h6>
                                <h3 class="fw-bold mb-0">${totalScan.toLocaleString()}</h3>
                            </div>
                            <div class="col-4">
                                <h6>STATUS</h6>
                                <h4 class="fw-bold mb-0">${statusText}</h4>
                            </div>
                        </div>`;

                    printHeaderHtml = `
                        <div class="print-summary-grid">
                            <div><h6>Pelanggan</h6><p>${currentHeaderData.customer || 'N/A'}</p></div>
                            <div><h6>Alamat</h6><p>${currentHeaderData.address || 'N/A'}</p></div>
                            <div><h6>No. Kontainer</h6><p>${currentHeaderData.container_no || 'N/A'}</p></div>
                            <div><h6>Status Verifikasi</h6><p>${statusText}</p></div>
                            <div><h6>Total Qty Order</h6><h4>${totalOrder.toLocaleString()}</h4></div>
                            <div><h6>Total Qty Scan</h6><h4>${totalScan.toLocaleString()}</h4></div>
                        </div>`;
                } else {
                     console.warn('Data summary tidak ditemukan untuk DO: ' + doNumber);
                     summaryHtml = '<p class="text-warning text-center">Data summary tidak tersedia.</p>';
                     printHeaderHtml = '<p class="text-warning text-center">Data summary tidak tersedia.</p>';
                }
                 modalSummaryArea.innerHTML = summaryHtml;
                 printHeaderInfo.innerHTML = printHeaderHtml;


                // 2. RENDER KONTEN DETAIL (BERDASARKAN LOGIKA BARU)
                if (showAsWaitingLayout) {
                    // Tampilkan pesan warning (Layout Gambar 1)
                    let infoText = isWaitingOnly ? 'Delivery Order ini masih menunggu untuk diproses. Belum ada data scan yang tersedia.' : 'Belum ada data scan ditemukan untuk Delivery Order ini.';
                    let iconClass = isWaitingOnly ? 'fa-info-circle' : 'fa-box-open';

                    modalDetailContent.innerHTML = `
                        <div class="modal-warning-message">
                            <i class="fas ${iconClass}"></i>
                            <h5 class="mb-1">Informasi</h5>
                            <p class="mb-0">${infoText}</p>
                        </div>`;
                } else if (items && items.length > 0) {
                    // Render tabel jika ada item (Layout Gambar 2, tapi scan > 0)
                    let tableRowsHtml = '';
                    items.forEach((item, index) => {
                        const qtyOrder = parseInt(item.qty_order || 0);
                        const qtyScan = parseInt(item.qty_scan || 0);
                        const qtyClass = qtyOrder === qtyScan ? 'qty-match' : 'qty-mismatch';
                        const itemNumber = item.no ? item.no : (index + 1);
                        const materialStr = String(item.material_number || '');
                        const materialDisplay = /^[0-9]+$/.test(materialStr) ? materialStr.replace(/^0+/, '') : materialStr;

                        tableRowsHtml += `
                            <tr>
                                <td class="ps-4">${itemNumber}</td>
                                <td>
                                    <div class="material-number">${materialDisplay}</div>
                                    <div class="material-description">${item.description || 'N/A'}</div>
                                </td>
                                <td class="text-center">${qtyOrder.toLocaleString()}</td>
                                <td class="text-center ${qtyClass}">${qtyScan.toLocaleString()}</td>
                            </tr>`;
                    });
                    modalDetailContent.innerHTML = `
                        <div class="table-responsive">
                             <table class="table table-hover mb-0">
                                 <thead class="sticky-top bg-light">
                                     <tr>
                                         <th style="width: 5%;" class="ps-4">No.</th>
                                         <th style="width: 45%;">Material</th>
                                         <th style="width: 25%;" class="text-center">Qty Order</th>
                                         <th style="width: 25%;" class="text-center">Qty Scan</th>
                                     </tr>
                                 </thead>
                                 <tbody>${tableRowsHtml}</tbody>
                             </table>
                         </div>`;
                } else {
                    // Fallback jika tidak ada item (aneh, tapi ditangani)
                     modalDetailContent.innerHTML = `
                        <div class="modal-warning-message">
                             <i class="fas fa-box-open"></i>
                             <h5 class="mb-1">Informasi</h5>
                             <p class="mb-0">Data item tidak ditemukan untuk DO ini.</p>
                         </div>`;
                }

                // 3. TERAPKAN VISIBILITAS TOMBOL (BERDASARKAN LOGIKA BARU)
                if (shouldShowProcessButton) {
                    processDoButton.classList.remove('d-none');
                } else {
                    processDoButton.classList.add('d-none');
                }

                if (shouldShowPrintButton) {
                    printButton.classList.remove('d-none');
                } else {
                    printButton.classList.add('d-none');
                }

            } catch (error) {
                console.error('Error fetching history details:', error);
                modalSummaryArea.innerHTML = '';
                printHeaderInfo.innerHTML = '';
                 // Tampilkan pesan error di area konten detail
                modalDetailContent.innerHTML = `
                    <div class="modal-warning-message text-danger bg-danger-subtle border-danger"> {{-- Style error --}}
                         <i class="fas fa-exclamation-triangle text-danger"></i>
                         <h5 class="mb-1">Error</h5>
                         <p class="mb-0">Gagal memuat data: ${error.message}</p>
                     </div>`;
                 printButton.classList.add('d-none'); // Sembunyikan tombol cetak
                 processDoButton.classList.add('d-none'); // Sembunyikan tombol proses
            } finally {
                modalLoader.classList.add('d-none');
                modalContent.classList.remove('d-none');
            }
        });

        printButton.addEventListener('click', function() {
             // Cek apakah ada tabel di dalam modalDetailContent
             const tableElement = modalDetailContent.querySelector('.table-responsive');
             if (!tableElement) {
                 console.warn("Tombol cetak diklik, tetapi tidak ada tabel untuk dicetak.");
                 return; // Jangan lakukan apa-apa jika tidak ada tabel
             }

            const doNumber = modalDoNumber.textContent;
            const tableContent = tableElement.innerHTML;
            const headerPrintContent = printHeaderInfo.innerHTML;

            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                    <head>
                        <title>Detail Verifikasi DO: ${doNumber}</title>
                         <style>
                             body { font-family: Arial, sans-serif; margin: 1px; font-size: 12pt; } /* Sedikit perbesar font dasar */
                             h2 {
                                 text-align: center;
                                 margin-top: 0; /* Hapus margin atas */
                                 margin-bottom: 5px; /* Kurangi margin bawah */
                                 font-size: 14pt;
                             }
                            .print-summary-grid {
                                 display: grid;
                                 grid-template-columns: 1fr 1fr;
                                 gap: 2px 3px; /* Kurangi gap vertikal, biarkan horizontal */
                                 margin-bottom: 0.2px; /* Kurangi margin bawah */
                                 padding: 1px; /* Kurangi padding */
                                 border: 1px solid #ccc;
                                 border-radius: 3px; /* Sedikit kurangi radius */
                                 background-color: #f8f9fa !important;
                                -webkit-print-color-adjust: exact;
                             }
                             .print-summary-grid div {
                                 padding-bottom: 0.2px; /* Sangat kurangi padding bawah */
                                 border-bottom: 1px dotted #ddd;
                                 margin-bottom: 1px; /* Tambah sedikit margin antar baris info */
                             }
                              /* Perbesar sedikit font header cetak */
                              .print-summary-grid h6 {
                                  margin-bottom: 0.5px; /* Rapatkan heading dan isi */
                                  color: #555;
                                  font-size: 14px; /* Sedikit lebih besar */
                                  text-transform: uppercase;
                                  font-weight: normal;
                              }
                              .print-summary-grid p, .print-summary-grid h4 {
                                  margin: 0;
                                  font-size: 12px; /* Sedikit lebih besar */
                                  line-height: 1.8; /* Sedikit renggangkan baris jika teks panjang */
                              }
                              .print-summary-grid h4 { font-weight: bold; }

                             table { width: 100%; border-collapse: collapse; margin-top: 10px;} /* Kurangi margin atas tabel */
                             th, td { border: 1px solid #ccc; padding: 6px; text-align: left; vertical-align: top;} /* Kurangi padding sel */
                             th { background-color: #f2f2f2 !important; font-weight: bold; -webkit-print-color-adjust: exact; }
                             .text-center { text-align: center; }
                             .ps-4 { padding-left: 1rem !important; } /* Kurangi padding kiri kolom No */
                             .material-number { font-weight: bold; font-size: 11pt; } /* Sesuaikan font size */
                             .material-description { font-size: 8pt; color: #333; } /* Sesuaikan font size */
                             .qty-match { color: green !important; -webkit-print-color-adjust: exact;}
                             .qty-mismatch { color: red !important; -webkit-print-color-adjust: exact;}
                             tr { page-break-inside: avoid; }
                         </style>
                    </head>
                    <body>
                        <h2>Detail Verifikasi DO: ${doNumber}</h2>
                        ${headerPrintContent}
                        ${tableContent}
                    </body>
                </html>
            `);
            printWindow.document.close();
            setTimeout(() => {
                try {
                    printWindow.print();
                    // Tutup jendela cetak setelah beberapa saat jika tidak ditutup otomatis
                    // setTimeout(() => { printWindow.close(); }, 2000);
                } catch (e) {
                     console.error("Gagal mencetak:", e);
                     // Mungkin tampilkan pesan ke pengguna jika perlu
                     printWindow.close(); // Tutup jika gagal
                }
            }, 500); // Tunggu sebentar agar konten dimuat
        });

        processDoButton.addEventListener('click', function() {
            const doNumber = modalDoNumber.textContent;
            if (doNumber) {
                // Simpan DO ke sessionStorage agar dibaca oleh halaman verifikasi
                sessionStorage.setItem('lastSearchedDO', doNumber);
                // Redirect ke halaman verifikasi
                window.location.href = "{{ route('do.verify.index') }}";
            } else {
                console.error("Tidak bisa memproses DO, nomor DO tidak ditemukan di modal.");
                alert('Gagal memuat nomor DO. Silakan coba lagi.');
            }
        });

        // Event listener untuk update total count saat TAB UTAMA berganti
        document.querySelectorAll('#historyTab > .nav-item > .nav-link').forEach(tab => {
            tab.addEventListener('shown.bs.tab', function(event) {
                updateTotalCount();
                 // Reset dan panggil filter saat tab utama berganti
                 filterInput.value = ''; // Kosongkan input filter
                 filterInput.dispatchEvent(new Event('keyup')); // Picu filter
                 // Reset sort ke default (Terbaru)
                 sortSelect.value = 'newest';
                 sortSelect.dispatchEvent(new Event('change'));
            });
        });

         // Event listener untuk update total count saat SUB-TAB PLANT berganti
         document.querySelectorAll('#waitingPlantTab > .nav-item > .nav-link').forEach(subTab => {
             subTab.addEventListener('shown.bs.tab', function(event) {
                 updateTotalCount();
                 // Panggil filter lagi agar pesan 'no result' benar
                 filterInput.dispatchEvent(new Event('keyup'));
                  // Reset sort ke default (Terbaru) saat sub-tab berganti
                 sortSelect.value = 'newest';
                 sortSelect.dispatchEvent(new Event('change'));
             });
         });


        // Panggil filter sekali saat halaman load untuk tab yang aktif
        if (document.querySelector('.tab-pane.active')) {
             filterInput.dispatchEvent(new Event('keyup'));
        }

    });
</script>
@endpush

