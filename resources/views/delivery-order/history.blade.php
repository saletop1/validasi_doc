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
        background: linear-gradient(45deg, #6c757d, #343a40);
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
        color: #0d6efd;
    }
    .customer-name {
        font-weight: 600;
        color: #212529;
    }
    .verification-date {
        font-size: 0.9rem;
        color: #6c757d;
    }
    .filter-input {
        margin-bottom: 1.5rem;
    }
    .modal-body .table {
        margin-top: 1rem;
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

            <div id="history-list" class="row">
                @forelse ($completedDos as $doNumber => $items)
                    @php
                        $firstItem = $items->first();
                    @endphp
                    <div class="col-lg-6 history-item" data-filter-text="{{ strtolower($doNumber . ' ' . $firstItem->NAME1) }}">
                        <div class="card history-card" data-bs-toggle="modal" data-bs-target="#historyDetailModal" data-do-number="{{ $doNumber }}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <p class="do-number mb-1">{{ $doNumber }}</p>
                                    <span class="badge bg-success">Selesai</span>
                                </div>
                                <p class="customer-name mb-1">{{ $firstItem->NAME1 }}</p>
                                <p class="verification-date mb-0">
                                    <i class="fas fa-check-circle me-1"></i>
                                    Diverifikasi pada: {{ \Carbon\Carbon::parse($firstItem->VERIFIED_AT)->timezone('Asia/Jakarta')->format('d F Y H:i') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center">
                        <p class="text-muted">Belum ada riwayat verifikasi yang tersimpan.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

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
            <table class="table table-striped table-sm">
                <thead>
                    <tr>
                        <!-- --- PERBAIKAN: Menambahkan kolom No. --- -->
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterInput = document.getElementById('history-filter');
        const historyItems = document.querySelectorAll('.history-item');
        const historyDetailModal = document.getElementById('historyDetailModal');
        const modalLoader = document.getElementById('modal-loader');
        const modalContent = document.getElementById('modal-content-area');
        const modalDoNumber = document.getElementById('modal-do-number');
        const modalTableBody = document.getElementById('modal-scanned-items-body');

        filterInput.addEventListener('keyup', function() {
            const filterText = this.value.toLowerCase();
            historyItems.forEach(item => {
                const itemText = item.getAttribute('data-filter-text');
                if (itemText.includes(filterText)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        historyDetailModal.addEventListener('show.bs.modal', async function (event) {
            const card = event.relatedTarget;
            const doNumber = card.getAttribute('data-do-number');

            modalDoNumber.textContent = doNumber;
            modalTableBody.innerHTML = '';
            modalLoader.classList.remove('d-none');
            modalContent.classList.add('d-none');

            try {
                // Pastikan route ini sesuai dengan yang ada di web.php Anda
                const url = `{{ url('/delivery-order/history/details') }}/${doNumber}`;

                const response = await fetch(url);
                if (!response.ok) throw new Error('Gagal mengambil data dari server');

                const items = await response.json();

                if (items.length > 0) {
                    items.forEach(item => {
                        const materialDisplay = /^[0-9]+$/.test(item.material_number)
                            ? item.material_number.replace(/^0+/, '')
                            : item.material_number;

                        // --- PERBAIKAN: Menambahkan sel untuk nomor urut ---
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
            }
        });
    });
</script>
@endpush
