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
        cursor: pointer;
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
        min-height: 400px;
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
    /* --- Perbaikan: Padding Modal Body --- */
    .modal-body {
        padding: 0; /* Hapus padding default */
    }
    .table-responsive {
        max-height: 60vh; /* Sesuaikan tinggi maksimal tabel */
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
        /* Sticky Header */
        position: sticky;
        top: 0; /* Posisi relatif terhadap .table-responsive */
        z-index: 10;
    }
     /* --- Style untuk Print Section --- */
     #print-header-info {
         display: none; /* Sembunyikan di layar */
     }
    .print-summary-grid {
        display: grid;
        grid-template-columns: 1fr 1fr; /* Dua kolom sama lebar */
        gap: 20px;
        margin-bottom: 20px;
        padding: 15px;
        border: 1px solid #eee;
        border-radius: 8px;
    }
    .print-summary-grid div {
        /* Optional styling for boxes */
    }
     .print-summary-grid h6 { margin-bottom: 5px; color: #555; font-size: 10px; text-transform: uppercase;}
     .print-summary-grid p, .print-summary-grid h4 { margin: 0; font-size: 12px; }
     .print-summary-grid h4 { font-weight: bold; }

    @media print {
        body { margin: 20px; font-size: 10pt; }
        .modal-header, .modal-footer, #modal-loader, .btn { display: none; }
        .modal-content { box-shadow: none; border-radius: 0; }
        .modal-body { padding: 0 !important; }
        .table-responsive { max-height: none; overflow: visible; }
        .table thead th { position: static; background-color: #f2f2f2 !important; -webkit-print-color-adjust: exact; }
        .table { width: 100%; border-collapse: collapse; margin-top: 15px;}
        .table th, .table td { border: 1px solid #ccc; padding: 6px;}
        .summary-box { display: none; /* Sembunyikan summary box asli */ }
        #print-header-info { display: block !important; /* Tampilkan info header saat print */ }
        /* --- PERBAIKAN: Style Print Header --- */
        .print-summary-grid {
             grid-template-columns: 1fr 1fr;
             gap: 10px; /* Kurangi jarak antar item */
             margin-bottom: 15px; /* Kurangi margin bawah */
             padding: 10px;
             border: 1px solid #ccc;
             background-color: #f8f9fa !important;
            -webkit-print-color-adjust: exact;
         }
         .print-summary-grid div {
             padding-bottom: 3px; /* Kurangi padding bawah */
             border-bottom: 1px dotted #ddd;
         }
          .print-summary-grid h6 {
              margin-bottom: 2px; /* Kurangi margin bawah heading */
              color: #555;
              font-size: 10px; /* Sedikit lebih besar */
              text-transform: uppercase;
              font-weight: normal;
          }
          .print-summary-grid p, .print-summary-grid h4 {
              margin: 0;
              font-size: 12px; /* Sedikit lebih besar */
          }
          .print-summary-grid h4 { font-weight: bold; }
         /* --- Akhir Perbaikan --- */

         tr { page-break-inside: avoid; }
    }
    /* --- Akhir Style untuk Print Section --- */

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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <a href="{{ route('do.verify.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali ke Verifikasi
                </a>
                <div class="d-flex align-items-center">
                    <span class="me-2 text-muted">Urutkan:</span>
                    <select class="form-select form-select-sm sort-options" id="history-sort">
                        <option value="newest">Terbaru</option>
                        <option value="oldest">Terlama</option>
                    </select>
                </div>
            </div>

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

            <ul class="nav nav-tabs" id="historyTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed-panel" type="button" role="tab" aria-controls="completed-panel" aria-selected="true">
                        <span class="status-indicator status-completed"></span>
                        Selesai
                        <span class="history-count">{{ count($completedDos) }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="inprogress-tab" data-bs-toggle="tab" data-bs-target="#inprogress-panel" type="button" role="tab" aria-controls="inprogress-panel" aria-selected="false">
                        <span class="status-indicator status-inprogress"></span>
                        Dalam Proses
                        <span class="history-count">{{ count($inProgressDos) }}</span>
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="historyTabContent">
                <div class="tab-pane fade show active" id="completed-panel" role="tabpanel" aria-labelledby="completed-tab">
                    <div class="row history-list" id="completed-list">
                        @forelse ($completedDos as $items)
                            <div class="col-lg-6 history-item" data-filter-text="{{ strtolower($items->VBELN . ' ' . $items->NAME1) }}" data-date="{{ \Carbon\Carbon::parse($items->VERIFIED_AT)->timestamp }}">
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
                            <div class="col-12">
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <h4 class="text-muted">Belum ada riwayat verifikasi yang selesai</h4>
                                    <p class="mb-0">Semua verifikasi DO yang telah selesai akan muncul di sini</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
                <div class="tab-pane fade" id="inprogress-panel" role="tabpanel" aria-labelledby="inprogress-tab">
                    <div class="row history-list" id="inprogress-list">
                         @forelse ($inProgressDos as $items)
                            <div class="col-lg-6 history-item" data-filter-text="{{ strtolower($items->VBELN . ' ' . $items->NAME1) }}" data-date="{{ \Carbon\Carbon::parse($items->updated_at)->timestamp }}">
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
                                                {{ \Carbon\Carbon::parse($items->updated_at)->timezone('Asia/Jakarta')->format('d F Y H:i') }}
                                            </p>
                                            <i class="fas fa-chevron-right text-muted"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
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
             <div id="print-header-info" class="p-4 border-bottom"></div>
             <div id="modal-summary-area" class="p-4 border-bottom"></div>
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
                     <tbody id="modal-scanned-items-body">
                     </tbody>
                 </table>
             </div>
         </div>
       </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times me-2"></i>Tutup
        </button>
        <button type="button" class="btn btn-outline-primary" id="print-details">
            <i class="fas fa-print me-2"></i>Cetak
        </button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterInput = document.getElementById('history-filter');
        const sortSelect = document.getElementById('history-sort');
        const historyDetailModal = document.getElementById('historyDetailModal');
        const modalLoader = document.getElementById('modal-loader');
        const modalContent = document.getElementById('modal-content-area');
        const modalDoNumber = document.getElementById('modal-do-number');
        const modalTableBody = document.getElementById('modal-scanned-items-body');
        const modalSummaryArea = document.getElementById('modal-summary-area');
        const printHeaderInfo = document.getElementById('print-header-info');
        const totalCountElement = document.getElementById('total-count');
        const printButton = document.getElementById('print-details');
        let currentHeaderData = null;

        updateTotalCount();

        function updateTotalCount() {
            const activeTabPane = document.querySelector('.tab-pane.active');
            if (activeTabPane) {
                const visibleItems = activeTabPane.querySelectorAll('.history-item:not([style*="display: none"])');
                totalCountElement.textContent = visibleItems.length;
            }
        }

        filterInput.addEventListener('keyup', function() {
            const filterText = this.value.toLowerCase();
            const activeTabPane = document.querySelector('.tab-pane.active');
            if (activeTabPane) {
                const historyItems = activeTabPane.querySelectorAll('.history-item');
                let visibleCount = 0;
                let originalEmptyState = activeTabPane.querySelector('.original-empty-state');
                historyItems.forEach(item => {
                    const itemText = item.getAttribute('data-filter-text');
                    if (itemText.includes(filterText)) {
                        item.style.display = '';
                        visibleCount++;
                    } else {
                        item.style.display = 'none';
                    }
                });
                const listContainer = activeTabPane.querySelector('.history-list');
                let noResultMessage = listContainer.querySelector('.no-result-message');
                if (visibleCount === 0 && historyItems.length > 0) {
                    if (!noResultMessage) {
                        const emptyHtml = `<div class="col-12 no-result-message"><div class="empty-state"><i class="fas fa-search"></i><h4 class="text-muted">Tidak ada hasil ditemukan</h4><p class="mb-0">Coba ubah kata kunci pencarian</p></div></div>`;
                        listContainer.insertAdjacentHTML('beforeend', emptyHtml);
                    }
                    if (originalEmptyState) originalEmptyState.style.display = 'none';
                } else {
                    if (noResultMessage) noResultMessage.remove();
                    if (historyItems.length === 0 && originalEmptyState) originalEmptyState.style.display = '';
                }
                totalCountElement.textContent = visibleCount;
            }
        });

        sortSelect.addEventListener('change', function() {
            const sortValue = this.value;
            const activeTabPane = document.querySelector('.tab-pane.active');
            if (activeTabPane) {
                const historyList = activeTabPane.querySelector('.history-list');
                const historyItems = Array.from(activeTabPane.querySelectorAll('.history-item'));
                historyItems.sort((a, b) => {
                    const aText = a.querySelector('.do-number').textContent;
                    const bText = b.querySelector('.do-number').textContent;
                    const aDate = parseInt(a.getAttribute('data-date'));
                    const bDate = parseInt(b.getAttribute('data-date'));
                    switch(sortValue) {
                        case 'newest': return bDate - aDate;
                        case 'oldest': return aDate - bDate;
                        case 'do-asc': return aText.localeCompare(bText);
                        case 'do-desc': return bText.localeCompare(aText);
                        default: return 0;
                    }
                });
                historyItems.forEach(item => { historyList.appendChild(item); });
                filterInput.dispatchEvent(new Event('keyup'));
            }
        });

        historyDetailModal.addEventListener('show.bs.modal', async function (event) {
            const card = event.relatedTarget;
            const doNumber = card.getAttribute('data-do-number');
            modalDoNumber.textContent = doNumber;
            modalTableBody.innerHTML = '';
            modalSummaryArea.innerHTML = '';
            printHeaderInfo.innerHTML = '';
            currentHeaderData = null;
            modalLoader.classList.remove('d-none');
            modalContent.classList.add('d-none');

            try {
                const cacheBuster = new Date().getTime();
                const url = `{{ url('/delivery-order/history/details') }}/${doNumber}?t=${cacheBuster}`;

                const response = await fetch(url);
                if (!response.ok) {
                    const errorJson = await response.json().catch(() => ({}));
                    throw new Error(errorJson.error || 'Gagal mengambil data dari server');
                }
                const data = await response.json();
                currentHeaderData = data.header || {};

                if (data.summary) {
                    const totalOrder = data.summary.total_order;
                    const totalScan = data.summary.total_scan;
                    const isComplete = currentHeaderData.VERIFIED_AT != null;
                    const summaryClass = isComplete ? 'summary-success' : (totalScan > 0 ? 'summary-warning' : 'summary-danger');
                    const statusText = isComplete ? 'SELESAI' : (totalScan > 0 ? 'PROSES' : 'BELUM DIMULAI');

                    const summaryHtml = `<div class="row text-center summary-box ${summaryClass}"><div class="col-4"><h6>TOTAL QTY ORDER</h6><h3 class="fw-bold mb-0">${totalOrder.toLocaleString()}</h3></div><div class="col-4"><h6>TOTAL QTY SCAN</h6><h3 class="fw-bold mb-0">${totalScan.toLocaleString()}</h3></div><div class="col-4"><h6>STATUS</h6><h4 class="fw-bold mb-0">${statusText}</h4></div></div>`;
                    modalSummaryArea.innerHTML = summaryHtml;

                    const printHeaderHtml = `<div class="print-summary-grid"><div><h6>Pelanggan</h6><p>${currentHeaderData.customer || 'N/A'}</p></div><div><h6>Alamat</h6><p>${currentHeaderData.address || 'N/A'}</p></div><div><h6>No. Kontainer</h6><p>${currentHeaderData.container_no || 'N/A'}</p></div><div><h6>Status Verifikasi</h6><p>${statusText}</p></div><div><h6>Total Qty Order</h6><h4>${totalOrder.toLocaleString()}</h4></div><div><h6>Total Qty Scan</h6><h4>${totalScan.toLocaleString()}</h4></div></div>`;
                    printHeaderInfo.innerHTML = printHeaderHtml;
                }

                const items = data.items;
                if (items.length > 0) {
                    items.forEach(item => { // 1. Hapus ', index'
                        const materialDisplay = /^[0-9]+$/.test(item.material_number) ? item.material_number.replace(/^0+/, '') : item.material_number;
                        const qtyOrder = parseInt(item.qty_order);
                        const qtyScan = parseInt(item.qty_scan);
                        const qtyClass = qtyOrder === qtyScan ? 'qty-match' : 'qty-mismatch';

                        // 2. Ganti 'index + 1' kembali menjadi 'item.no'
                        const row = `<tr><td class="ps-4">${item.no}</td><td><div class="material-number">${materialDisplay}</div><div class="material-description">${item.description}</div></td><td class="text-center">${qtyOrder.toLocaleString()}</td><td class="text-center ${qtyClass}">${qtyScan.toLocaleString()}</td></tr>`;
                        modalTableBody.innerHTML += row;
                    });
                    } else {
                    modalTableBody.innerHTML = `<tr><td colspan="4" class="text-center py-4"><i class="fas fa-box-open fa-2x text-muted mb-2"></i><p class="text-muted mb-0">Tidak ada data scan ditemukan.</p></td></tr>`;
                }

            } catch (error) {
                console.error('Error fetching history details:', error);
                modalSummaryArea.innerHTML = '';
                printHeaderInfo.innerHTML = '';
                modalTableBody.innerHTML = `<tr><td colspan="4" class="text-center py-4"><i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i><p class="text-danger mb-0">Gagal memuat data: ${error.message}</p></td></tr>`;
            } finally {
                modalLoader.classList.add('d-none');
                modalContent.classList.remove('d-none');
            }
        });

        printButton.addEventListener('click', function() {
            const doNumber = modalDoNumber.textContent;
            const tableContent = document.querySelector('#modal-content-area .table-responsive').innerHTML;
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
                printWindow.print();
            }, 500);
        });

        document.querySelectorAll('#historyTab button').forEach(tab => {
            tab.addEventListener('shown.bs.tab', function() {
                updateTotalCount();
                 filterInput.dispatchEvent(new Event('keyup'));
            });
        });

        if (document.querySelector('.tab-pane.active')) {
             updateTotalCount();
             filterInput.dispatchEvent(new Event('keyup'));
        }

    });
</script>
@endpush

