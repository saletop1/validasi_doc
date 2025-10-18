@extends('layouts.app')

@section('title', 'Verifikasi Delivery Order')

@push('styles')
<style>
    :root {
        --primary-color: #0d6efd;
        --secondary-color: #6c757d;
        --success-color: #198754;
        --warning-color: #ffc107;
        --info-color: #0dcaf0;
        --light-gray: #f8f9fa;
        --dark-color: #212529;
        --border-color: #dee2e6;
    }

    body {
        background-color: var(--light-gray);
        font-family: 'Inter', sans-serif;
    }

    .main-card {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.07);
        overflow: hidden;
    }

    .card-header-custom {
        background: linear-gradient(45deg, var(--primary-color), var(--info-color));
        color: white;
        padding: 1.5rem;
        text-align: center;
    }

    .card-footer-custom {
        background-color: #f1f3f5;
        border-top: 1px solid var(--border-color);
    }

    .btn-gradient {
        background: linear-gradient(45deg, var(--success-color), #20c997);
        border: none;
        color: white;
        transition: transform 0.2s;
    }
    .btn-gradient:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    .shipping-header-card {
        background-color: #ffffff;
        padding: 1.5rem;
        border-radius: 0.75rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        height: 100%;
    }
    .company-info {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--border-color);
    }
    .company-info-left {
        display: flex;
        align-items: center;
    }
    .company-info img {
        max-height: 65px;
        margin-right: 1.5rem;
    }
    .company-details h5 {
        margin-bottom: 0.25rem;
        font-weight: 700;
        font-size: 1.1rem;
    }
    .company-details p {
        margin-bottom: 0;
        color: var(--secondary-color);
        font-size: 0.9rem;
    }

    .do-number-header {
        font-size: 2.0rem;
        font-weight: 700;
        color: var(--primary-color);
    }

    .shipping-details .row > div {
        margin-bottom: 0.5rem;
    }
    .shipping-details strong {
        display: block;
        color: var(--secondary-color);
        font-weight: 500;
        font-size: 0.85rem;
    }
    .shipping-details span {
        font-weight: 600;
        color: var(--dark-color);
        font-size: 0.95rem;
    }


    .info-card {
        background-color: #e9ecef;
        border-radius: 0.75rem;
        padding: 1.5rem;
        height: 100%;
    }

    .table-responsive {
        max-height: 65vh;
        overflow-y: auto;
        padding: 0 1rem;
    }

    .table {
        border-collapse: collapse;
        width: 100%;
    }

    .table thead th {
        background-color: var(--light-gray);
        color: var(--dark-color);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        border-bottom: 2px solid var(--border-color);
        border-top: none;
        position: sticky;
        top: 0;
        z-index: 1;
    }

    .table tbody tr {
        border-bottom: 1px solid var(--border-color);
        transition: background-color 0.2s ease;
    }

    .table tbody tr:last-child {
        border-bottom: none;
    }

    .table tbody tr:hover {
        background-color: #f1f3f5;
    }
    .table td, .table th {
        vertical-align: middle;
        padding: 0.8rem 1rem;
        border: none;
    }

    .main-item-row.has-details {
        cursor: pointer;
    }

    .main-item-row.expanded {
        background-color: #e9ecef;
        border-left: 4px solid var(--primary-color);
    }

    .material-cell strong {
        color: var(--dark-color);
        font-size: 0.95rem;
    }
    .material-cell span {
        font-size: 0.8rem;
        color: #6c757d;
    }

    .progress-container {
        width: 100%;
        background-color: #e9ecef;
        border-radius: 10px;
        height: 20px;
        overflow: hidden;
        position: relative;
    }
    .progress-bar-scan {
        height: 100%;
        background-color: transparent;
        border-radius: 10px;
        transition: width 0.4s ease-in-out, background-color 0.5s ease;
    }
    .progress-bar-scan.in-process {
        background-color: var(--warning-color);
    }
    .progress-bar-scan.complete {
        background-color: var(--success-color);
    }

    .progress-text {
        position: absolute;
        width: 100%;
        text-align: center;
        font-size: 0.8rem;
        font-weight: 600;
        line-height: 20px;
        color: #000000;
        z-index: 2;
    }

    .details-row > td {
        padding: 0 !important;
        background-color: #f8f9fa;
    }
    .details-table-wrapper {
        padding: 1.25rem;
    }
    .details-table {
        background-color: #ffffff;
        border: 1px solid var(--border-color);
    }
    .details-table th {
        background-color: #e9ecef;
    }

    .hu-scanned {
        background-color: #d1e7dd !important;
        color: var(--success-color);
        font-weight: 500;
    }

    .filter-input-group { position: relative; }
    .filter-input-group .form-control { padding-left: 2.5rem; }
    .filter-input-group .fa-filter {
        position: absolute; left: 1rem; top: 50%;
        transform: translateY(-50%); color: #adb5bd;
    }
    #loader {
        display: none; border: 5px solid #f3f3f3;
        border-top: 5px solid var(--primary-color);
        border-radius: 50%; width: 50px; height: 50px;
        animation: spin 1s linear infinite;
        position: fixed; top: 50%; left: 50%; z-index: 1056;
    }
    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>
@endpush

@section('content')
<div id="loader"></div>
<div class="container-fluid py-4">
    <div class="card main-card">
        <div class="card-header card-header-custom">
            <h2 class="mb-0 h3"><i class="fas fa-truck me-2"></i> Verifikasi & Scan Delivery Order</h2>
        </div>
        <div class="card-body p-4 p-md-5">
            <div class="row justify-content-center align-items-end mb-4">
                <!-- --- PERBAIKAN: Layout Tombol Cari dan Riwayat --- -->
                <div class="col-lg-7 col-md-8">
                    <div class="form-group">
                        <label for="do_number" class="form-label fs-5 fw-bold mb-2"></label>
                        <div class="input-group">
                            <input type="text" class="form-control form-control-lg" id="do_number" placeholder="Masukkan nomor DO..." name="do_number">
                            <button class="btn btn-gradient px-4" type="button" id="btn-search-do"><i class="fas fa-search me-2"></i>Cari</button>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 text-md-end mt-3 mt-md-0">
                     <a href="{{ route('do.history.index') }}" class="btn btn-secondary btn-lg w-100"><i class="fas fa-history me-2"></i>Lihat Riwayat</a>
                </div>
            </div>

            <div id="do-details" class="d-none">
                <hr class="my-4">

                <div class="row mb-4">
                    <div class="col-lg-7 mb-4 mb-lg-0">
                        <div class="shipping-header-card">
                             <div class="company-info">
                                <div class="company-info-left">
                                    <img src="{{ asset('images/KMI.png') }}" alt="Logo KMI">
                                    <div class="company-details">
                                        <h5>PT. KAYU MEBEL INDONESIA</h5>
                                        <p>Jl. Jend. Urip Sumoharjo No.134, Semarang</p>
                                    </div>
                                </div>
                                <div class="do-number-header">
                                    <span id="header-do-number"></span>
                                </div>
                            </div>
                            <div class="shipping-details">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Pelanggan:</strong>
                                        <span id="customer-name"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Ship To:</strong>
                                        <span id="ship-to"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Alamat:</strong>
                                        <span id="customer-address"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Ship Type:</strong>
                                        <span id="ship-type"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Shipping Point:</strong>
                                        <span id="shipping-point"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Container No:</strong>
                                        <span id="container-no"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                         <div class="info-card bg-info text-white text-center d-flex flex-column justify-content-center">
                             <h4 class="mb-3"><i class="fas fa-qrcode me-2"></i>Scan Verifikasi</h4>
                             <p class="mb-3">Klik tombol di bawah untuk memindai menggunakan kamera.</p>
                             <button class="btn btn-light btn-lg" id="btn-camera-scan"><i class="fas fa-camera me-2"></i>Scan dengan Kamera</button>
                        </div>
                    </div>
                </div>

                <div class="card mt-3 summary-card">
                    <div class="card-header bg-light"><h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Ringkasan Verifikasi</h5></div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3 col-6 mb-3 mb-md-0"><h6 class="text-muted">Total Item (SKU)</h6><h4 class="fw-bold" id="summary-total-item">0</h4></div>
                            <div class="col-md-3 col-6 mb-3 mb-md-0"><h6 class="text-muted">Total Qty Order</h6><h4 class="fw-bold text-primary" id="summary-qty-order">0</h4></div>
                            <div class="col-md-3 col-6 mb-3 mb-md-0"><h6 class="text-muted">Total Qty Telah Scan</h6><h4 class="fw-bold text-success" id="summary-qty-scanned">0</h4></div>
                            <div class="col-md-3 col-6"><h6 class="text-muted">Total Sisa Belum Scan</h6><h4 class="fw-bold text-danger" id="summary-sisa">0</h4></div>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-list-check me-2"></i>Rincian Item (Picking List)</h5>
                    </div>
                    <div class="card-body p-3">
                        <div class="filter-input-group mb-3">
                             <i class="fas fa-filter"></i>
                            <input type="text" id="item-filter-input" class="form-control" placeholder="Ketik untuk memfilter item...">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-item-info">
                            <thead>
                                <tr>
                                    <th style="width: 5%;" class="text-center">No.</th>
                                    <th style="width: 30%;">Material</th>
                                    <th style="width: 10%;" class="text-center">DO</th>
                                    <th style="width: 10%;" class="text-center">Item</th>
                                    <th style="width: 15%;" class="text-center">Batch / HU</th>
                                    <th style="width: 10%;" class="text-center">Qty Order</th>
                                    <th style="width: 20%;" class="text-center">Progress Scan</th>
                                </tr>
                            </thead>
                            <tbody id="picking-list-body"></tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
        <div class="card-footer text-center text-muted card-footer-custom"><small>&copy; 2025 PT. Kayu Mebel Indonesia</small></div>
    </div>
</div>

<div class="modal fade" id="cameraScannerModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Arahkan Kamera ke Barcode/QR Code</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div id="reader" width="100%"></div></div></div></div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const doNumberInput = document.getElementById('do_number');
    const btnSearchDO = document.getElementById('btn-search-do');
    const pickingListBody = document.getElementById('picking-list-body');
    const doDetailsSection = document.getElementById('do-details');
    const loader = document.getElementById('loader');
    const filterInput = document.getElementById('item-filter-input');

    const btnCameraScan = document.getElementById('btn-camera-scan');
    const cameraScannerModal = new bootstrap.Modal(document.getElementById('cameraScannerModal'));
    let html5QrcodeScanner;
    let isScanCooldown = false;

    let currentDOData = null;
    let scannedHUs = new Set();
    let isCompletionNotified = false;

    const lastDO = sessionStorage.getItem('lastSearchedDO');
    if (lastDO) {
        doNumberInput.value = lastDO;
        searchDO();
    }

    function showAlert(title, text, icon, timer = 1500) {
        Swal.fire({ title, text, icon, timer: timer, showConfirmButton: false });
    }

    async function searchDO() {
        const doNumber = doNumberInput.value.trim();
        if (!doNumber) return;
        loader.style.display = 'block';
        isCompletionNotified = false;

        try {
            const searchUrl = '{{ route("do.verify.search") }}';
            const response = await fetch(searchUrl, {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                body: JSON.stringify({ do_number: doNumber })
            });

            const data = await response.json();

            if (data.status === 'completed') {
                showAlert('Informasi', data.message, 'info', 4000);
                doDetailsSection.classList.add('d-none');
                sessionStorage.removeItem('lastSearchedDO');
            } else if (data.success) {
                currentDOData = data.data;
                displayDODetails();
                doDetailsSection.classList.remove('d-none');
                sessionStorage.setItem('lastSearchedDO', doNumber);
                doNumberInput.value = '';
            } else {
                showAlert('Gagal', data.message || 'Data tidak ditemukan.', 'error');
                doDetailsSection.classList.add('d-none');
                sessionStorage.removeItem('lastSearchedDO');
            }
        } catch (error) {
            console.error('Terjadi kesalahan saat mencari DO:', error);
            showAlert('Error', 'Gagal terhubung ke server. Periksa konsol untuk detail.', 'error');
        } finally {
            loader.style.display = 'none';
        }
    }

    function displayDODetails() {
        if (!currentDOData) return;
        const savedProgress = currentDOData.progress;
        scannedHUs = new Set(savedProgress.hus || []);

        document.getElementById('customer-name').textContent = currentDOData.customer || 'T/A';
        document.getElementById('customer-address').textContent = currentDOData.address || 'T/A';
        document.getElementById('shipping-point').textContent = currentDOData.shipping_point || 'T/A';
        document.getElementById('ship-to').textContent = currentDOData.ship_to || 'T/A';
        document.getElementById('ship-type').textContent = currentDOData.ship_type || 'T/A';
        document.getElementById('container-no').textContent = currentDOData.container_no || 'T/A';
        document.getElementById('header-do-number').textContent = currentDOData.do_number || '';


        pickingListBody.innerHTML = '';
        currentDOData.items.forEach((item, index) => {
            const uniqueId = `${item.material}-${item.item_no}`;
            const savedScanCount = savedProgress.counts[uniqueId] || 0;

            let batchHuCellHtml = item.is_hu ? `${item.hu_details.length} HU` : item.batch_no;
            let detailRowsHtml = '';

            if (item.is_hu) {
                detailRowsHtml = item.hu_details.map(detail => {
                    const isScanned = scannedHUs.has(detail.hu_no);
                    return `<tr class="${isScanned ? 'hu-scanned' : ''}">
                                <td>${detail.delivery}</td>
                                <td>${detail.item}</td>
                                <td>${detail.hu_no}</td>
                                <td>${detail.item_hu}</td>
                                <td>${detail.charg2}</td>
                                <td class="text-center">${detail.qty_hu}</td>
                                <td class="text-center">${isScanned ? '<i class="fas fa-check-circle text-success scan-status-icon"></i>' : ''}</td>
                            </tr>`;
                }).join('');
            }

            const mainRowClass = item.is_hu ? 'main-item-row has-details' : 'main-item-row';
            const mainRowHtml = `
                <tr class="${mainRowClass}" id="main-row-${index}">
                    <td class="text-center">${index + 1}</td>
                    <td class="material-cell">
                        <strong>${item.material}</strong><br>
                        <span>${item.description}</span>
                    </td>
                    <td class="text-center">${item.do_no}</td>
                    <td class="text-center">${item.item_no}</td>
                    <td class="text-center">${batchHuCellHtml}</td>
                    <td class="text-center fw-bold">${item.qty_order}</td>
                    <td>
                        <div class="progress-container">
                             <div class="progress-bar-scan" id="progressbar-${uniqueId}"></div>
                             <div class="progress-text" id="progresstext-${uniqueId}">${savedScanCount} / ${item.qty_order}</div>
                        </div>
                    </td>
                </tr>`;

            const detailsHtml = item.is_hu ? `
                <tr class="details-row" style="display: none;">
                    <td colspan="7">
                        <div class="details-table-wrapper">
                            <h6 class="mb-3">Rincian Handling Units (HU) untuk ${item.material}</h6>
                            <table class="table table-bordered table-sm details-table">
                                <thead>
                                    <tr>
                                    <th>Delivery</th>
                                    <th>Item</th>
                                    <th>HU Number</th>
                                    <th>Item HU</th>
                                    <th>Batch</th>
                                    <th class="text-center">Qty HU</th>
                                    <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>${detailRowsHtml}</tbody>
                            </table>
                        </div>
                    </td>
                </tr>` : '';

            pickingListBody.innerHTML += mainRowHtml + detailsHtml;
            updateProgressBarColor(uniqueId, savedScanCount, item.qty_order);
        });
        updateSummary();
    }

    function updateProgressBarColor(uniqueId, currentScan, qtyOrder) {
        const progressBar = document.getElementById(`progressbar-${uniqueId}`);
        if (!progressBar) return;

        const progressPercent = qtyOrder > 0 ? (currentScan / qtyOrder) * 100 : 0;
        progressBar.style.width = `${progressPercent}%`;

        progressBar.classList.remove('in-process', 'complete');

        if (currentScan >= qtyOrder && qtyOrder > 0) {
            progressBar.classList.add('complete');
        } else if (currentScan > 0) {
            progressBar.classList.add('in-process');
        }
    }

    function processScannedBarcode(barcode) {
        if (!currentDOData) return;
        const originalBarcode = barcode.trim();
        const code = /^[0-9]+$/.test(originalBarcode) ? originalBarcode.replace(/^0+/, '') : originalBarcode;

        let foundItem = null; let isHUscan = false; let batchNumber = null;

        for (const item of currentDOData.items) {
            if (item.is_hu) {
                const foundHU = item.hu_details.find(d => d.hu_no === code);
                if (foundHU) {
                    foundItem = item;
                    isHUscan = true;
                    batchNumber = foundHU.charg2;
                    break;
                }
            }
            if (!item.is_hu && item.batch_no === code) {
                foundItem = item;
                isHUscan = false;
                batchNumber = item.batch_no;
                break;
            }
        }

        if (foundItem && isHUscan && scannedHUs.has(code)) {
            showAlert('Duplikat!', `HU ${code} sudah pernah discan.`, 'warning');
            return;
        }

        if (foundItem) {
            const uniqueId = `${foundItem.material}-${foundItem.item_no}`;
            const scanTextEl = document.getElementById(`progresstext-${uniqueId}`);
            let [currentScan, qtyOrder] = scanTextEl.textContent.split(' / ').map(Number);

            if (currentScan < qtyOrder) {
                currentScan++;
                if (isHUscan) {
                    scannedHUs.add(code);
                }

                showAlert('Berhasil!', `Item ${foundItem.material} discan.`, 'success');
                saveScanToDatabase({
                    do_number: currentDOData.do_number,
                    material_number: foundItem.material,
                    item_number: foundItem.item_no,
                    scanned_code: originalBarcode,
                    batch_number: batchNumber
                });

                scanTextEl.textContent = `${currentScan} / ${qtyOrder}`;
                updateProgressBarColor(uniqueId, currentScan, qtyOrder);

                updateSummary();
                if(isHUscan) updateHUDetailView();
            } else {
                showAlert('Penuh!', `Item ${foundItem.material} (${foundItem.item_no}) sudah memenuhi kuantitas.`, 'warning');
            }
        } else {
            showAlert('Gagal!', `Kode "${originalBarcode}" tidak ditemukan di DO ini.`, 'error');
        }
    }

    function updateHUDetailView() {
        document.querySelectorAll('.details-table tbody tr').forEach(row => {
            const huNumberCell = row.cells[2];
            if (huNumberCell && scannedHUs.has(huNumberCell.textContent)) {
                row.classList.add('hu-scanned');
                const statusCell = row.cells[6];
                if (statusCell && !statusCell.querySelector('.fa-check-circle')) {
                    statusCell.innerHTML = '<i class="fas fa-check-circle text-success scan-status-icon"></i>';
                }
            }
        });
    }

    async function saveScanToDatabase(scanData) {
        try {
            await fetch('{{ route("do.verify.scan") }}', {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                body: JSON.stringify(scanData)
            });
        } catch (error) { console.error('Gagal menyimpan scan ke DB:', error); }
    }

    function updateSummary() {
        let totalOrder = 0; let totalScanned = 0;
        currentDOData.items.forEach(item => {
            const uniqueId = `${item.material}-${item.item_no}`;
            totalOrder += item.qty_order;
            const textContent = document.getElementById(`progresstext-${uniqueId}`).textContent;
            const scanned = parseInt(textContent.split(' / ')[0], 10) || 0;
            totalScanned += scanned;
        });

        document.getElementById('summary-total-item').textContent = currentDOData.items.length;
        document.getElementById('summary-qty-order').textContent = totalOrder;
        document.getElementById('summary-qty-scanned').textContent = totalScanned;
        document.getElementById('summary-sisa').textContent = totalOrder - totalScanned;

        if (totalOrder > 0 && totalScanned >= totalOrder && !isCompletionNotified) {
            isCompletionNotified = true;
            showAlert('Verifikasi Selesai!', 'Semua item telah berhasil diverifikasi. Notifikasi email sedang dikirim.', 'success', 3000);
            triggerEmailNotification();
        }
    }

    async function triggerEmailNotification() {
        try {
            await fetch('{{ route("do.verify.complete") }}', {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                body: JSON.stringify({ do_number: currentDOData.do_number })
            });
        } catch(error) {
            console.error('Gagal memicu pengiriman email:', error);
        }
    }

    function isNumeric(str) {
        if (typeof str != "string") return false
        return !isNaN(str) && !isNaN(parseFloat(str))
    }

    // --- EVENT LISTENERS ---
    btnSearchDO.addEventListener('click', searchDO);
    doNumberInput.addEventListener('keypress', (e) => e.key === 'Enter' && searchDO());

    pickingListBody.addEventListener('click', function(event) {
        const mainRow = event.target.closest('.main-item-row.has-details');
        if (!mainRow) return;

        const detailsRow = mainRow.nextElementSibling;

        if (detailsRow && detailsRow.classList.contains('details-row')) {
            mainRow.classList.toggle('expanded');
            detailsRow.style.display = detailsRow.style.display === 'none' ? 'table-row' : 'none';
        }
    });

    filterInput.addEventListener('keyup', function() {
        const filterText = this.value.toLowerCase();
        document.querySelectorAll('#picking-list-body .main-item-row').forEach(row => {
            const rowText = row.querySelector('.material-cell').textContent.toLowerCase();
            const detailsRow = row.nextElementSibling;

            if (rowText.includes(filterText)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
                 if (detailsRow && detailsRow.classList.contains('details-row')) {
                    row.classList.remove('expanded');
                    detailsRow.style.display = 'none';
                }
            }
        });
    });

    // --- LOGIKA KAMERA ---
    btnCameraScan.addEventListener('click', () => cameraScannerModal.show());

    document.getElementById('cameraScannerModal').addEventListener('shown.bs.modal', function () {
        if (!html5QrcodeScanner || (html5QrcodeScanner && !html5QrcodeScanner.isScanning)) {
            html5QrcodeScanner = new Html5Qrcode("reader");
            const config = { fps: 10, qrbox: { width: 250, height: 250 } };
            html5QrcodeScanner.start({ facingMode: "environment" }, config, onScanSuccess, ()=>{});
        }
    });

    document.getElementById('cameraScannerModal').addEventListener('hidden.bs.modal', function () {
        if (html5QrcodeScanner && html5QrcodeScanner.isScanning) {
            html5QrcodeScanner.stop().catch(err => console.error("Gagal menghentikan scanner.", err));
        }
    });

    function onScanSuccess(decodedText, decodedResult) {
        if (isScanCooldown) return;
        isScanCooldown = true;
        processScannedBarcode(decodedText);
        if (window.navigator.vibrate) { window.navigator.vibrate(100); }
        setTimeout(() => { isScanCooldown = false; }, 2000);
    }
});
</script>
@endpush

