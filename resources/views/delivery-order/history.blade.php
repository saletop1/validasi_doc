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
        border-radius: 1rem;
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.07);
    }
    .card-header-custom {
        background: linear-gradient(45deg, #1ddec8ff, #343a40);
        color: white;
        padding: 1.5rem;
        text-align: center;
    }
    .history-card {
        border: 1px solid #dee2e6;
        border-radius: 0.75rem;
        margin-bottom: 1rem;
        transition: box-shadow 0.3s ease, transform 0.3s ease;
        cursor: pointer;
    }
    .history-card:hover {
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        transform: translateY(-5px);
    }
    .history-card .card-body {
        padding: 1.5rem;
    }
    .do-number {
        font-size: 1.25rem;
        font-weight: 700;
        color: #17624aff;
    }
    .customer-name {
        font-weight: 600;
        color: #212529;
    }
    .verification-date {
        font-size: 0.9rem;
        color: #15a493ff;
    }
    .filter-input {
        margin-bottom: 1.5rem;
    }
    #historyDetailModal .modal-body {
        padding-top: 0;
    }
    .modal-body .table {
        margin-top: 0;
    }
    .summary-box {
        color: white;
        border-radius: 0.5rem;
        padding-top: 0.75rem;
        padding-bottom: 0.75rem;
    }
    .summary-box h6 {
        color: rgba(255, 255, 255, 0.85);
    }
    .summary-success {
        background: linear-gradient(135deg, #2E7D32, #388E3C);
    }
    .summary-danger {
        background: linear-gradient(135deg, #c6b928ff, #c6b928ff);
    }

    #modal-summary-area {
        position: sticky;
        top: 0;
        z-index: 3;
        background-color: #ffffff;
        padding: 1rem;
    }
    #historyDetailModal .table thead th {
        position: sticky;
        z-index: 2;
        background-color: #ffffff;
        box-shadow: inset 0 -2px 0 #dee2e6;
    }
    /* Style untuk Tab */
    .nav-tabs .nav-link {
        color: #6c757d;
        border: none;
        border-bottom: 3px solid transparent;
    }
    .nav-tabs .nav-link.active {
        color: #123301ff;
        font-weight: 600;
        background-color: transparent;
        border-bottom: 3px solid #6f6f6fe2;
    }
</style>
@endpush

@section('content')
<div class="container py-5">
    <div class="card main-card">
        <div class="card-header card-header-custom">
            <h2 class="mb-0 h3"><i class="fas fa-history me-2"></i> Riwayat Verifikasi Delivery Order</h2>
        </div>
        <div class="card-body p-4 p-md-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <a href="{{ route('do.verify.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Kembali ke Verifikasi</a>
                <div class="w-50">
                    <input type="text" id="history-filter" class="form-control filter-input" placeholder="Cari berdasarkan No. DO atau Pelanggan...">
                </div>
            </div>

            <!-- Navigasi Tab -->
            <ul class="nav nav-tabs mb-4" id="historyTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed-panel" type="button" role="tab" aria-controls="completed-panel" aria-selected="true">Selesai</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="inprogress-tab" data-bs-toggle="tab" data-bs-target="#inprogress-panel" type="button" role="tab" aria-controls="inprogress-panel" aria-selected="false">Dalam Proses</button>
                </li>
            </ul>

            <!-- Konten Tab -->
            <div class="tab-content" id="historyTabContent">
                <!-- Panel Selesai -->
                <div class="tab-pane fade show active" id="completed-panel" role="tabpanel" aria-labelledby="completed-tab">
                    <div class="row history-list">
                        {{-- --- PERBAIKAN: Menggunakan $items langsung --- --}}
                        @forelse ($completedDos as $items)
                            <div class="col-lg-6 history-item" data-filter-text="{{ strtolower($items->VBELN . ' ' . $items->NAME1) }}">
                                <div class="card history-card" data-bs-toggle="modal" data-bs-target="#historyDetailModal" data-do-number="{{ $items->VBELN }}">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <p class="do-number mb-1">{{ $items->VBELN }}</p>
                                            <span class="badge bg-success">Selesai</span>
                                        </div>
                                        <p class="customer-name mb-1">{{ $items->NAME1 }}</p>
                                        <p class="verification-date mb-0">
                                            <i class="fas fa-check-circle me-1"></i>
                                            Diverifikasi pada: {{ \Carbon\Carbon::parse($items->VERIFIED_AT)->timezone('Asia/Jakarta')->format('d F Y H:i') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center">
                                <p class="text-muted">Belum ada riwayat verifikasi yang selesai.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
                <!-- Panel Dalam Proses -->
                <div class="tab-pane fade" id="inprogress-panel" role="tabpanel" aria-labelledby="inprogress-tab">
                    <div class="row history-list">
                         {{-- --- PERBAIKAN: Menggunakan $items langsung --- --}}
                        @forelse ($inProgressDos as $items)
                            <div class="col-lg-6 history-item" data-filter-text="{{ strtolower($items->VBELN . ' ' . $items->NAME1) }}">
                                <div class="card history-card" data-bs-toggle="modal" data-bs-target="#historyDetailModal" data-do-number="{{ $items->VBELN }}">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <p class="do-number mb-1">{{ $items->VBELN }}</p>
                                            <span class="badge bg-warning text-dark">Proses</span>
                                        </div>
                                        <p class="customer-name mb-1">{{ $items->NAME1 }}</p>
                                        <p class="verification-date mb-0">
                                            <i class="fas fa-sync-alt me-1"></i>
                                            Aktivitas terakhir: {{ \Carbon\Carbon::parse($items->updated_at)->timezone('Asia/Jakarta')->format('d F Y H:i') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center">
                                <p class="text-muted">Tidak ada verifikasi yang sedang dalam proses.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Detail (tidak ada perubahan di sini) --}}
<div class="modal fade" id="historyDetailModal" tabindex="-1" aria-labelledby="historyDetailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="historyDetailModalLabel">Detail Verifikasi DO: <span id="modal-do-number"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="modal-loader" class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
        <div id="modal-content-area" class="d-none">
            <div id="modal-summary-area"></div>
            <table class="table table-striped table-sm">
                <thead>
                    <tr>
                        <th style="width: 5%;">No.</th>
                        <th>Material</th>
                        <th class="text-center">Qty Order</th>
                        <th class="text-center">Qty Scan</th>
                    </tr>
                </thead>
                <tbody id="modal-scanned-items-body">
                </tbody>
            </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
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
        const historyDetailModal = document.getElementById('historyDetailModal');
        const modalLoader = document.getElementById('modal-loader');
        const modalContent = document.getElementById('modal-content-area');
        const modalDoNumber = document.getElementById('modal-do-number');
        const modalTableBody = document.getElementById('modal-scanned-items-body');
        const modalSummaryArea = document.getElementById('modal-summary-area');

        // Fungsi filter sekarang berlaku untuk tab yang aktif
        filterInput.addEventListener('keyup', function() {
            const filterText = this.value.toLowerCase();
            const activeTabPane = document.querySelector('.tab-pane.active');
            // Pastikan activeTabPane ditemukan sebelum querySelectorAll
            if (activeTabPane) {
                const historyItems = activeTabPane.querySelectorAll('.history-item');
                historyItems.forEach(item => {
                    const itemText = item.getAttribute('data-filter-text');
                    if (itemText.includes(filterText)) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });
            }
        });

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

                const response = await fetch(url); // Fetch tanpa timeout di sini
                if (!response.ok) throw new Error('Gagal mengambil data dari server');

                const data = await response.json();

                if (data.summary) {
                    const totalOrder = data.summary.total_order;
                    const totalScan = data.summary.total_scan;
                    const summaryClass = totalOrder === totalScan ? 'summary-success' : 'summary-danger';

                    const summaryHtml = `
                        <div class="row text-center summary-box ${summaryClass}">
                            <div class="col-6">
                                <h6>TOTAL QTY ORDER</h6>
                                <h4 class="fw-bold mb-0">${totalOrder}</h4>
                            </div>
                            <div class="col-6">
                                <h6>TOTAL QTY SCAN</h6>
                                <h4 class="fw-bold mb-0">${totalScan}</h4>
                            </div>
                        </div>
                    `;
                    modalSummaryArea.innerHTML = summaryHtml;
                }

                const items = data.items;

                if (items.length > 0) {
                    items.forEach(item => {
                        const materialDisplay = /^[0-9]+$/.test(item.material_number)
                            ? item.material_number.replace(/^0+/, '')
                            : item.material_number;

                        const row = `
                            <tr>
                                <td>${item.no}</td>
                                <td>
                                    <strong>${materialDisplay}</strong><br>
                                    <small>${item.description}</small>
                                </td>
                                <td class="text-center">${parseInt(item.qty_order)}</td>
                                <td class="text-center">${item.qty_scan}</td>
                            </tr>
                        `;
                        modalTableBody.innerHTML += row;
                    });
                } else {
                    modalTableBody.innerHTML = '<tr><td colspan="4" class="text-center">Tidak ada data scan ditemukan.</td></tr>';
                }

            } catch (error) {
                console.error('Error fetching history details:', error);
                modalTableBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Gagal memuat data.</td></tr>';
            } finally {
                modalLoader.classList.add('d-none');
                modalContent.classList.remove('d-none');

                // Logika untuk mengatur posisi sticky header tabel
                const summaryElement = document.getElementById('modal-summary-area');
                const tableHeaderCells = document.querySelectorAll('#historyDetailModal .table thead th');

                if (tableHeaderCells.length > 0) {
                    setTimeout(() => {
                        let stickyTopOffset = 0;
                        // Pastikan summaryElement ada dan punya child nodes sebelum ambil offsetHeight
                        if (summaryElement && summaryElement.hasChildNodes()) {
                            stickyTopOffset = summaryElement.offsetHeight;
                        }
                        tableHeaderCells.forEach(th => {
                            th.style.top = `${stickyTopOffset}px`;
                        });
                    }, 50);
                }
            }
        });
    });
</script>
@endpush
