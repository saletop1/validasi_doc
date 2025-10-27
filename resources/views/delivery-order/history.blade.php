@extends('layouts.app')

@section('title', 'Riwayat Verifikasi DO')

@push('styles')
<style>
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
    .modal-body {
        padding: 0;
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

            <!-- Navigasi Tab -->
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

            <!-- Konten Tab -->
            <div class="tab-content" id="historyTabContent">
                <!-- Panel Selesai -->
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

                <!-- Panel Dalam Proses -->
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
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
        const totalCountElement = document.getElementById('total-count');
        const printButton = document.getElementById('print-details');

        // Inisialisasi jumlah total
        updateTotalCount();

        // Fungsi untuk memperbarui jumlah total item yang ditampilkan
        function updateTotalCount() {
            const activeTabPane = document.querySelector('.tab-pane.active');
            if (activeTabPane) {
                const visibleItems = activeTabPane.querySelectorAll('.history-item:not([style*="display: none"])');
                totalCountElement.textContent = visibleItems.length;
            }
        }

        // Fungsi filter
        filterInput.addEventListener('keyup', function() {
            const filterText = this.value.toLowerCase();
            const activeTabPane = document.querySelector('.tab-pane.active');

            if (activeTabPane) {
                const historyItems = activeTabPane.querySelectorAll('.history-item');
                let visibleCount = 0;

                historyItems.forEach(item => {
                    const itemText = item.getAttribute('data-filter-text');
                    if (itemText.includes(filterText)) {
                        item.style.display = '';
                        visibleCount++;
                    } else {
                        item.style.display = 'none';
                    }
                });

                // Tampilkan pesan jika tidak ada hasil
                const listContainer = activeTabPane.querySelector('.history-list');
                const emptyMessage = listContainer.querySelector('.empty-state');

                if (visibleCount === 0 && !emptyMessage) {
                    const emptyHtml = `
                        <div class="col-12">
                            <div class="empty-state">
                                <i class="fas fa-search"></i>
                                <h4 class="text-muted">Tidak ada hasil ditemukan</h4>
                                <p class="mb-0">Coba ubah kata kunci pencarian atau filter</p>
                            </div>
                        </div>
                    `;
                    listContainer.innerHTML = emptyHtml;
                } else if (visibleCount > 0 && emptyMessage) {
                    // Jika ada hasil dan sebelumnya ada pesan kosong, hapus pesan kosong
                    emptyMessage.remove();
                }

                totalCountElement.textContent = visibleCount;
            }
        });

        // Fungsi sorting
        sortSelect.addEventListener('change', function() {
            const sortValue = this.value;
            const activeTabPane = document.querySelector('.tab-pane.active');

            if (activeTabPane) {
                const historyList = activeTabPane.querySelector('.history-list');
                const historyItems = Array.from(activeTabPane.querySelectorAll('.history-item:not([style*="display: none"])'));

                historyItems.sort((a, b) => {
                    const aText = a.querySelector('.do-number').textContent;
                    const bText = b.querySelector('.do-number').textContent;
                    const aDate = parseInt(a.getAttribute('data-date'));
                    const bDate = parseInt(b.getAttribute('data-date'));

                    switch(sortValue) {
                        case 'newest':
                            return bDate - aDate;
                        case 'oldest':
                            return aDate - bDate;
                        case 'do-asc':
                            return aText.localeCompare(bText);
                        case 'do-desc':
                            return bText.localeCompare(aText);
                        default:
                            return 0;
                    }
                });

                // Hapus semua item dan tambahkan kembali sesuai urutan
                historyItems.forEach(item => {
                    historyList.appendChild(item);
                });
            }
        });

        // Event listener untuk modal detail
        historyDetailModal.addEventListener('show.bs.modal', async function (event) {
            const card = event.relatedTarget;
            const doNumber = card.getAttribute('data-do-number');

            modalDoNumber.textContent = doNumber;
            modalTableBody.innerHTML = '';
            modalSummaryArea.innerHTML = '';
            modalLoader.classList.remove('d-none');
            modalContent.classList.add('d-none');

            try {
                const url = `{{ url('/delivery-order/history/details') }}/${doNumber}`;
                const response = await fetch(url);

                if (!response.ok) throw new Error('Gagal mengambil data dari server');
                const data = await response.json();

                // Tampilkan summary
                if (data.summary) {
                    const totalOrder = data.summary.total_order;
                    const totalScan = data.summary.total_scan;
                    const isComplete = totalOrder === totalScan;
                    const summaryClass = isComplete ? 'summary-success' :
                                        (totalScan > 0 ? 'summary-warning' : 'summary-danger');

                    const summaryHtml = `
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
                                <h4 class="fw-bold mb-0">${isComplete ? 'SELESAI' : 'BELUM SELESAI'}</h4>
                            </div>
                        </div>
                    `;
                    modalSummaryArea.innerHTML = summaryHtml;
                }

                // Tampilkan items
                const items = data.items;
                if (items.length > 0) {
                    items.forEach(item => {
                        const materialDisplay = /^[0-9]+$/.test(item.material_number)
                            ? item.material_number.replace(/^0+/, '')
                            : item.material_number;

                        const qtyOrder = parseInt(item.qty_order);
                        const qtyScan = parseInt(item.qty_scan);
                        const qtyClass = qtyOrder === qtyScan ? 'qty-match' : 'qty-mismatch';

                        const row = `
                            <tr>
                                <td class="ps-4">${item.no}</td>
                                <td>
                                    <div class="material-number">${materialDisplay}</div>
                                    <div class="material-description">${item.description}</div>
                                </td>
                                <td class="text-center">${qtyOrder.toLocaleString()}</td>
                                <td class="text-center ${qtyClass}">${qtyScan.toLocaleString()}</td>
                            </tr>
                        `;
                        modalTableBody.innerHTML += row;
                    });
                } else {
                    modalTableBody.innerHTML = `
                        <tr>
                            <td colspan="4" class="text-center py-4">
                                <i class="fas fa-box-open fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">Tidak ada data scan ditemukan.</p>
                            </td>
                        </tr>
                    `;
                }

            } catch (error) {
                console.error('Error fetching history details:', error);
                modalTableBody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center py-4">
                            <i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i>
                            <p class="text-danger mb-0">Gagal memuat data. Silakan coba lagi.</p>
                        </td>
                    </tr>
                `;
            } finally {
                modalLoader.classList.add('d-none');
                modalContent.classList.remove('d-none');
            }
        });

        // Fungsi untuk mencetak detail
        printButton.addEventListener('click', function() {
            const doNumber = modalDoNumber.textContent;
            const printContent = document.querySelector('#modal-content-area').innerHTML;

            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                    <head>
                        <title>Detail Verifikasi DO: ${doNumber}</title>
                        <style>
                            body { font-family: Arial, sans-serif; margin: 20px; }
                            .summary-box { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
                            table { width: 100%; border-collapse: collapse; }
                            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                            th { background-color: #f2f2f2; }
                            .qty-match { color: green; }
                            .qty-mismatch { color: red; }
                            @media print {
                                body { margin: 0; }
                                .summary-box { background: #f8f9fa !important; -webkit-print-color-adjust: exact; }
                                th { background-color: #f2f2f2 !important; -webkit-print-color-adjust: exact; }
                            }
                        </style>
                    </head>
                    <body>
                        <h2>Detail Verifikasi DO: ${doNumber}</h2>
                        <div>${printContent}</div>
                    </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        });

        // Update count ketika tab diubah
        document.querySelectorAll('#historyTab button').forEach(tab => {
            tab.addEventListener('shown.bs.tab', function() {
                updateTotalCount();
            });
        });
    });
</script>
@endpush
