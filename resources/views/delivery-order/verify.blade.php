@extends('layouts.app')
@section('title', 'Verifikasi Delivery Order')
@push('styles')
<style>
    /* ... (style Anda sebelumnya tidak berubah) ... */
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
        <div class="card-header card-header-custom d-flex justify-content-between align-items-center flex-wrap">
            <h2 class="mb-0 h3"><i class="fas fa-truck me-2"></i>Verifikasi Delivery Order</h2>
            @auth
            <div class="d-flex align-items-center mt-2 mt-md-0">
                <span class="text-white me-3 d-none d-md-inline">Selamat datang, <strong>{{ Auth::user()->name }}</strong></span>
                <form method="POST" action="{{ route('logout') }}" class="d-inline" id="logout-form">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-sign-out-alt me-1"></i>Logout</button>
                </form>
            </div>
            @endauth
        </div>

        <div class="card-body p-4 p-md-5">
            <div class="row justify-content-center align-items-end mb-4">
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
                             <p>Klik tombol di bawah untuk memindai menggunakan kamera.</p>
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
        <div class="card-footer text-center text-muted card-footer-custom"><small>&copy; {{ date('Y') }} PT. Kayu Mebel Indonesia</small></div>
    </div>
</div>

<div class="modal fade" id="cameraScannerModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Arahkan Kamera ke Barcode/QR Code</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div id="reader" width="100%"></div></div></div></div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/html5-qrcode" type="text-javascript"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const doNumberInput = document.getElementById('do_number');
    const btnSearchDO = document.getElementById('btn-search-do');
    const pickingListBody = document.getElementById('picking-list-body');
    const doDetailsSection = document.getElementById('do-details');
    const loader = document.getElementById('loader');
    const filterInput = document.getElementById('item-filter-input');
    const btnCameraScan = document.getElementById('btn-camera-scan');
    const cameraScannerModalElement = document.getElementById('cameraScannerModal');
    const cameraScannerModal = new bootstrap.Modal(cameraScannerModalElement);
    let html5QrcodeScanner;
    let isScanCooldown = false;
    let currentDOData = null;
    let scannedHUs = new Set();
    let isCompletionNotified = false;
    let multiItemHuMap = {};
    let isProcessingScan = false; // Flag untuk mencegah scan ganda saat awaiting

    const logoutForm = document.getElementById('logout-form');
    if (logoutForm) {
        logoutForm.addEventListener('submit', function() {
            sessionStorage.removeItem('lastSearchedDO');
        });
    }

    async function fetchWithTimeout(resource, options = {}, timeout = 30000) {
        const controller = new AbortController();
        const id = setTimeout(() => controller.abort(), timeout);
        try {
            const response = await fetch(resource, { ...options, signal: controller.signal });
            clearTimeout(id);
            if (response.status === 401 || response.status === 419) {
                showAlert('Sesi Habis', 'Sesi Anda telah berakhir. Silakan login kembali.', 'warning', 3000);
                setTimeout(() => { window.location.href = "{{ route('login') }}"; }, 3000);
                throw new Error('Unauthenticated');
            }
            if (!response.ok) {
                let errorData;
                try { errorData = await response.json(); } catch(e) { errorData = { message: `HTTP error! status: ${response.status}` }; }
                throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
            }
            return response;
        } catch (error) {
            clearTimeout(id);
            if (error.name === 'AbortError') {
                showAlert('Timeout', 'Permintaan ke server memakan waktu terlalu lama. Silakan coba lagi.', 'error', 3000);
            } else if (error.message !== 'Unauthenticated') {
                 showAlert('Error', `Terjadi kesalahan: ${error.message}`, 'error', 3000);
            }
            throw error;
        }
    }

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
        doDetailsSection.classList.add('d-none');
        scannedHUs = new Set();

        try {
            const searchUrl = '{{ route("do.verify.search") }}';
            const response = await fetchWithTimeout(searchUrl, {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                body: JSON.stringify({ do_number: doNumber })
            });

            const data = await response.json();

            if (data.status === 'completed') {
                showAlert('Informasi', data.message, 'info', 4000);
                sessionStorage.removeItem('lastSearchedDO');
            } else if (data.success) {
                currentDOData = data.data;
                multiItemHuMap = currentDOData.multiItemHuMap || {};
                console.log("Multi-Item HU Map:", multiItemHuMap); // Untuk debugging
                displayDODetails();
                doDetailsSection.classList.remove('d-none');
                sessionStorage.setItem('lastSearchedDO', doNumber);
                doNumberInput.value = '';
            } else {
                showAlert('Gagal', data.message || 'Data tidak ditemukan.', 'error');
                sessionStorage.removeItem('lastSearchedDO');
            }
        } catch (error) {
            console.error('Gagal mencari DO:', error.message);
            sessionStorage.removeItem('lastSearchedDO');
        } finally {
            loader.style.display = 'none';
        }
    }

    function displayDODetails() {
        if (!currentDOData) return;
        const savedProgress = currentDOData.progress;
        savedProgress.counts = savedProgress.counts || {};
        scannedHUs = new Set(savedProgress.hus || []);
        console.log("Initial scanned HUs:", scannedHUs);


        document.getElementById('customer-name').textContent = currentDOData.customer || 'T/A';
        document.getElementById('customer-address').textContent = currentDOData.address || 'T/A';
        document.getElementById('shipping-point').textContent = currentDOData.shipping_point || 'T/A';
        document.getElementById('ship-to').textContent = currentDOData.ship_to || 'T/A';
        document.getElementById('ship-type').textContent = currentDOData.ship_type || 'T/A';
        document.getElementById('container-no').textContent = currentDOData.container_no || 'T/A';
        document.getElementById('header-do-number').textContent = currentDOData.do_number || '';

        pickingListBody.innerHTML = '';
        currentDOData.items.forEach((item, index) => {
            if (item.item_no === undefined || item.item_no === null) {
                console.error("Item tidak memiliki item_no:", item);
                return;
            }
            const progressKey = `${item.material}-${item.item_no}`;
            const savedScanCount = savedProgress.counts[progressKey] || 0;

            let batchHuCellHtml = item.batch_no || '';
            let huCount = 0;
            if (item.is_hu && item.hu_details) {
                 const uniqueHus = new Set(item.hu_details.map(d => d.hu_no));
                 huCount = uniqueHus.size;
                 batchHuCellHtml = `${huCount} HU`;
            }

            let detailRowsHtml = '';
            if (item.is_hu && item.hu_details) {
                detailRowsHtml = item.hu_details.map(detail => {
                    const isScanned = scannedHUs.has(detail.hu_no);
                    const huNumberDisplay = detail.hu_no || 'N/A';
                    return `<tr class="${isScanned ? 'hu-scanned' : ''}" data-hu-number="${huNumberDisplay}">
                                <td>${detail.delivery || 'N/A'}</td>
                                <td>${detail.item || 'N/A'}</td>
                                <td>${huNumberDisplay}</td>
                                <td>${detail.item_hu || 'N/A'}</td>
                                <td>${detail.charg2 || 'N/A'}</td>
                                <td class="text-center">${detail.qty_hu || 0}</td>
                                <td class="text-center scan-status-cell">${isScanned ? '<i class="fas fa-check-circle text-success scan-status-icon"></i>' : ''}</td>
                            </tr>`;
                }).join('');
            }

            const mainRowClass = item.is_hu ? 'main-item-row has-details' : 'main-item-row';
            const mainRowHtml = `
                <tr class="${mainRowClass}" id="main-row-${item.item_no}" data-item-no="${item.item_no}">
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
                             <div class="progress-bar-scan" id="progressbar-${progressKey}" style="width: 0%;"></div>
                             <div class="progress-text" id="progresstext-${progressKey}">${savedScanCount} / ${item.qty_order}</div>
                        </div>
                    </td>
                </tr>`;

            const detailsHtml = (item.is_hu && item.hu_details && item.hu_details.length > 0) ? `
                <tr class="details-row" style="display: none;">
                    <td colspan="7">
                        <div class="details-table-wrapper">
                            <h6 class="mb-3">Rincian Handling Units (HU) untuk ${item.material} / Item ${item.item_no}</h6>
                            <table class="table table-bordered table-sm details-table">
                                <thead>
                                    <tr>
                                    <th>Delivery</th>
                                    <th>Item DO</th>
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
            updateProgressBarColor(progressKey, savedScanCount, item.qty_order);
        });
        updateSummary();
        updateHUDetailView();
    }

    function updateProgressBarColor(progressKey, currentScan, qtyOrder) {
        const progressBar = document.getElementById(`progressbar-${progressKey}`);
        if (!progressBar) {
            console.warn(`Progress bar dengan key ${progressKey} tidak ditemukan.`);
            return;
        }

        const numCurrentScan = Number(currentScan);
        const numQtyOrder = Number(qtyOrder);

        const progressPercent = numQtyOrder > 0 ? (numCurrentScan / numQtyOrder) * 100 : 0;
        progressBar.style.width = `${progressPercent}%`;

        progressBar.classList.remove('in-process', 'complete');

         console.log(`Updating progress bar ${progressKey}: Scan=${numCurrentScan}, Order=${numQtyOrder}`); // Logging tambahan

        if (numCurrentScan >= numQtyOrder && numQtyOrder > 0) {
            progressBar.classList.add('complete');
             console.log(` -> Marked as complete`);
        } else if (numCurrentScan > 0) {
            progressBar.classList.add('in-process');
             console.log(` -> Marked as in-process`);
        } else {
             console.log(` -> No class added (scan=0 or order=0)`);
        }
    }

    // --- PERUBAHAN UTAMA DIMULAI DI SINI ---

    // Fungsi processScannedBarcode diubah menjadi async
    async function processScannedBarcode(barcode) {
        if (!currentDOData || isProcessingScan) return; // Jangan proses jika scan sebelumnya masih berjalan

        isProcessingScan = true; // Set flag
        const originalBarcode = barcode.trim();
        const code = /^[0-9]+$/.test(originalBarcode) ? originalBarcode.replace(/^0+/, '') : originalBarcode;

        let foundItem = null;
        let isHUscan = false;
        let batchNumber = null;
        let scanQtyToAdd = 1;
        let huDetails = [];
        let affectedItems = [];

        const customerName = currentDOData.customer || '';
        const isBrunswick = customerName.toLowerCase().includes('brunswick');
        const associatedItemNos = isBrunswick ? (multiItemHuMap[code] || []) : [];
        const isMultiItemHu = isBrunswick && associatedItemNos.length > 0;

         console.log(`Scan: ${code}, Brunswick: ${isBrunswick}, MultiItemHU: ${isMultiItemHu}, AssociatedItems: ${associatedItemNos}`);

        try { // Bungkus semua logika dalam try-finally
            if (isMultiItemHu) {
                console.log(`HU ${code} adalah multi-item untuk Brunswick.`);

                if (scannedHUs.has(code)) {
                    showAlert('Duplikat!', `HU ${code} sudah pernah discan untuk DO ini.`, 'warning');
                    isProcessingScan = false; // Reset flag
                    return;
                }

                affectedItems = currentDOData.items.filter(item => {
                    return item.item_no !== undefined && item.item_no !== null && associatedItemNos.includes(String(item.item_no));
                });

                if (affectedItems.length === 0) {
                    showAlert('Error Internal', `Item terkait (${associatedItemNos.join(', ')}) untuk multi-item HU ${code} tidak ditemukan.`, 'error');
                    console.error("Item tidak ditemukan untuk associatedItemNos:", associatedItemNos, "di currentDOData.items:", currentDOData.items);
                    isProcessingScan = false; // Reset flag
                    return;
                }
                foundItem = affectedItems[0];
                batchNumber = foundItem.hu_details?.find(d => d.hu_no === code)?.charg2 || null;

                console.log(`HU ${code} mempengaruhi ${affectedItems.length} item:`, affectedItems.map(i => i.item_no));

                let itemsToSave = [];
                let uiUpdateTasks = [];

                affectedItems.forEach(item => {
                    const progressKey = `${item.material}-${item.item_no}`;
                    const scanTextEl = document.getElementById(`progresstext-${progressKey}`);
                    if (!scanTextEl) {
                        console.error(`Elemen progress text ${progressKey} tidak ditemukan untuk multi-item HU.`);
                        return; // Lewati item ini
                    }
                    let [currentScan, qtyOrder] = scanTextEl.textContent.split(' / ').map(Number);

                    const qtyToAddForThisItem = Math.max(0, qtyOrder - currentScan);
                    const newScanTotal = qtyOrder; // UI dipaksa complete

                    console.log(`Memproses item ${item.item_no}: currentScan=${currentScan}, qtyOrder=${qtyOrder}, qtyToAddForThisItem=${qtyToAddForThisItem}`);

                    // Simpan data untuk di-save ke DB
                    itemsToSave.push({
                        do_number: currentDOData.do_number,
                        material_number: item.material,
                        item_number: String(item.item_no),
                        scanned_code: originalBarcode,
                        batch_number: batchNumber,
                        qty_scanned: qtyToAddForThisItem // Kirim qty yang ditambahkan saja
                    });

                    // Simpan tugas untuk update UI (agar bisa di-await)
                    uiUpdateTasks.push({
                        element: scanTextEl,
                        progressKey: progressKey,
                        newScan: newScanTotal,
                        order: qtyOrder
                    });
                });

                if (itemsToSave.length > 0) {
                    // Kirim SEMUA data scan ke DB dan TUNGGU
                    await Promise.all(itemsToSave.map(scanData => saveScanToDatabase(scanData)));

                    // SETELAH berhasil save, baru update UI
                    scannedHUs.add(code);
                    uiUpdateTasks.forEach(task => {
                        task.element.textContent = `${task.newScan} / ${task.order}`;
                        updateProgressBarColor(task.progressKey, task.newScan, task.order);
                    });

                    showAlert('Berhasil!', `Multi-item HU ${code} discan, ${affectedItems.length} item diperbarui.`, 'success');

                    // SETELAH UI di-update, baru update summary
                    updateSummary();
                    updateHUDetailView();
                }

            } else {
                console.log(`Memproses scan biasa untuk ${code}.`);
                for (const item of currentDOData.items) {
                    if (item.is_hu && item.hu_details) {
                        const matchingHuDetails = item.hu_details.filter(d => d.hu_no === code);
                        if (matchingHuDetails.length > 0) {
                            foundItem = item;
                            isHUscan = true;
                            huDetails = matchingHuDetails;
                            batchNumber = huDetails[0].charg2;
                            break;
                        }
                    }
                    if (!item.is_hu && item.batch_no === code) {
                        foundItem = item;
                        isHUscan = false;
                        scanQtyToAdd = 1;
                        batchNumber = item.batch_no;
                        break;
                    }
                }

                if (foundItem && isHUscan && scannedHUs.has(code)) {
                    showAlert('Duplikat!', `HU ${code} sudah pernah discan untuk DO ini.`, 'warning');
                    isProcessingScan = false; // Reset flag
                    return;
                }

                if (foundItem) {
                    const progressKey = `${foundItem.material}-${foundItem.item_no}`;
                    const scanTextEl = document.getElementById(`progresstext-${progressKey}`);
                    if (!scanTextEl) {
                        console.error(`Elemen progress text tidak ditemukan untuk key: ${progressKey}`);
                        showAlert('Error Internal', 'Komponen UI tidak ditemukan. Coba refresh.', 'error');
                        isProcessingScan = false; // Reset flag
                        return;
                    }
                    let [currentScan, qtyOrder] = scanTextEl.textContent.split(' / ').map(Number);

                    if (isHUscan) {
                        if (isBrunswick) {
                            scanQtyToAdd = huDetails.reduce((sum, detail) => sum + (detail.qty_hu || 0), 0);
                        } else {
                            scanQtyToAdd = huDetails[0]?.qty_hu || 0;
                        }
                        if (scanQtyToAdd === 0) {
                            console.warn("Qty untuk HU adalah 0.", huDetails);
                            showAlert('Info', `Kuantitas untuk HU ${code} adalah 0.`, 'info');
                            scannedHUs.add(code);
                            updateHUDetailView();
                            isProcessingScan = false; // Reset flag
                            return;
                        }
                    }

                    if (currentScan + scanQtyToAdd <= qtyOrder) {
                        const qtyToAddForThisScan = scanQtyToAdd;
                        const newScanTotal = currentScan + scanQtyToAdd;

                        // TUNGGU save ke DB
                        await saveScanToDatabase({
                            do_number: currentDOData.do_number,
                            material_number: foundItem.material,
                            item_number: String(foundItem.item_no),
                            scanned_code: originalBarcode,
                            batch_number: batchNumber,
                            qty_scanned: qtyToAddForThisScan
                        });

                        // SETELAH berhasil save, baru update UI
                        if (isHUscan) {
                            scannedHUs.add(code);
                        }
                        showAlert('Berhasil!', `Item ${foundItem.material} (${foundItem.item_no}) discan (+${qtyToAddForThisScan}).`, 'success');
                        scanTextEl.textContent = `${newScanTotal} / ${qtyOrder}`;
                        updateProgressBarColor(progressKey, newScanTotal, qtyOrder);

                        // SETELAH UI di-update, baru update summary
                        updateSummary();
                        if(isHUscan) updateHUDetailView();

                    } else {
                        showAlert('Melebihi!', `Scan ${isHUscan ? 'HU' : 'batch'} ${code} (+${scanQtyToAdd}) akan melebihi kuantitas order (${qtyOrder}) untuk item ${foundItem.item_no}.`, 'warning');
                    }
                } else {
                    showAlert('Gagal!', `Kode "${originalBarcode}" tidak ditemukan di item DO ini.`, 'error');
                }
            }
        } catch (error) {
            // Tangkap error dari saveScanToDatabase
            console.error("Proses scan gagal karena save DB error:", error);
            // Alert sudah ditampilkan oleh saveScanToDatabase, jadi tidak perlu lagi.
        } finally {
            isProcessingScan = false; // Selalu reset flag setelah selesai
        }
    }


     function updateHUDetailView() {
          scannedHUs.forEach(huNum => {
            document.querySelectorAll(`.details-table tbody tr[data-hu-number="${huNum}"]`).forEach(row => {
                row.classList.add('hu-scanned');
                const statusCell = row.querySelector('.scan-status-cell');
                if (statusCell && !statusCell.querySelector('.fa-check-circle')) {
                    statusCell.innerHTML = '<i class="fas fa-check-circle text-success scan-status-icon"></i>';
                }
            });
        });
    }

    // Fungsi saveScanToDatabase diubah agar melempar error (throw)
    async function saveScanToDatabase(scanData) {
         try {
            const response = await fetchWithTimeout('{{ route("do.verify.scan") }}', {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                body: JSON.stringify(scanData)
            });

            // fetchWithTimeout sudah melempar error jika response.ok false,
            // tapi kita bisa tambahkan cek JSON jika perlu
            const data = await response.json();
            if (!data.success) { // Jika server mengembalikan {success: false}
                 throw new Error(data.message || 'Server menolak penyimpanan');
            }

            console.log('Scan berhasil disimpan ke DB:', scanData);
            // Tidak perlu return true, ketiadaan error sudah cukup
        } catch (error) {
            console.error('Gagal menyimpan scan ke DB:', error.message);
            if (error.message && error.message.includes('array')) {
                 showAlert('Error Validasi', 'Data item number tidak valid. Coba refresh halaman.', 'error');
            } else {
                // Tampilkan alert di sini agar terpusat
                showAlert('Gagal Simpan', `Gagal menyimpan data scan: ${error.message}. Perubahan dibatalkan.`, 'error');
            }
            throw error; // Lempar ulang error agar processScannedBarcode bisa menangkapnya
        }
    }

    // --- PERUBAHAN UTAMA SELESAI ---


    function updateSummary() {
          let totalOrder = 0; let totalScanned = 0;
        if (!currentDOData || !currentDOData.items) return;

        currentDOData.items.forEach(item => {
            if (item.item_no === undefined || item.item_no === null) return;
            const progressKey = `${item.material}-${item.item_no}`;
            const textElement = document.getElementById(`progresstext-${progressKey}`);
            if (textElement) {
                totalOrder += item.qty_order;
                const textContent = textElement.textContent || '0 / 0';
                const scanned = parseInt(textContent.split(' / ')[0], 10) || 0;
                totalScanned += scanned;
            } else {
                console.warn(`Elemen progresstext-${progressKey} tidak ditemukan.`);
            }
        });

        document.getElementById('summary-total-item').textContent = currentDOData.items.length;
        document.getElementById('summary-qty-order').textContent = totalOrder;
        document.getElementById('summary-qty-scanned').textContent = totalScanned;
        document.getElementById('summary-sisa').textContent = Math.max(0, totalOrder - totalScanned);

        if (totalOrder > 0 && totalScanned >= totalOrder && !isCompletionNotified) {
            isCompletionNotified = true;
            showAlert('Verifikasi Selesai!', 'Semua item telah berhasil diverifikasi. Notifikasi email sedang dikirim.', 'success', 3000);
            triggerEmailNotification();
        }
    }


    async function triggerEmailNotification() {
          try {
            if (!currentDOData || !currentDOData.do_number) {
                 console.error("Tidak bisa mengirim notifikasi, data DO tidak lengkap.");
                 return;
            }
            // Kita tidak perlu menunggu (await) notifikasi email, jalankan saja di background
            fetchWithTimeout('{{ route("do.verify.complete") }}', {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                body: JSON.stringify({ do_number: currentDOData.do_number })
            }).then(() => {
                 console.log('Notifikasi email berhasil dipicu untuk DO:', currentDOData.do_number);
            }).catch(error => {
                 // Tangkap error jika pengiriman notifikasi gagal, tapi jangan blokir user
                 console.error('Gagal memicu pengiriman email:', error.message);
                 // Tidak perlu alert, ini proses background
            });
        } catch(error) {
            // Catch error sinkron jika ada (jarang terjadi)
            console.error('Error sinkron saat memicu email:', error.message);
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

        const itemNo = mainRow.dataset.itemNo;
        if (!itemNo) return;

        const detailsRow = mainRow.nextElementSibling;

        if (detailsRow && detailsRow.classList.contains('details-row')) {
            mainRow.classList.toggle('expanded');
            detailsRow.style.display = detailsRow.style.display === 'none' ? 'table-row' : 'none';
        }
    });


    filterInput.addEventListener('keyup', function() {
        const filterText = this.value.toLowerCase();
        document.querySelectorAll('#picking-list-body .main-item-row').forEach(row => {
            const materialCell = row.querySelector('.material-cell');
            const itemCell = row.cells[3];
            const rowText = (materialCell ? materialCell.textContent.toLowerCase() : '')
                           + ' ' + (itemCell ? itemCell.textContent.toLowerCase() : '');

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
      btnCameraScan.addEventListener('click', () => {
        cameraScannerModal.show();
    });

    cameraScannerModalElement.addEventListener('shown.bs.modal', function () {
        startCameraScan();
    });

    cameraScannerModalElement.addEventListener('hidden.bs.modal', function () {
        stopCameraScan();
    });

    function startCameraScan() {
        if (html5QrcodeScanner && html5QrcodeScanner.isScanning) {
            console.log("Scanner sudah berjalan.");
            return;
        }
         try {
            html5QrcodeScanner = new Html5Qrcode("reader");
            const config = { fps: 10, qrbox: { width: 250, height: 250 }, rememberLastUsedCamera: true };
            html5QrcodeScanner.start({ facingMode: "environment" }, config, onScanSuccess, onScanFailure)
                .then(() => {
                    console.log("Scanner kamera dimulai.");
                })
                .catch(err => {
                    console.error("Gagal memulai scanner kamera.", err);
                     showAlert('Error Kamera', 'Gagal memulai kamera. Pastikan izin telah diberikan.', 'error');
                });
         } catch (e) {
             console.error("Error saat inisialisasi Html5Qrcode:", e);
             showAlert('Error Scanner', 'Gagal memuat komponen scanner.', 'error');
         }
    }

    function stopCameraScan() {
        if (html5QrcodeScanner && html5QrcodeScanner.isScanning) {
            html5QrcodeScanner.stop()
                .then(() => {
                    console.log("Scanner kamera dihentikan.");
                     const readerElement = document.getElementById('reader');
                     if(readerElement) readerElement.innerHTML = '';
                     html5QrcodeScanner = null;
                })
                .catch(err => {
                    console.error("Gagal menghentikan scanner.", err);
                });
        } else {
             console.log("Scanner tidak sedang berjalan atau belum diinisialisasi.");
        }
    }

    function onScanSuccess(decodedText, decodedResult) {
        if (isScanCooldown || isProcessingScan) return; // Tambahkan cek isProcessingScan
        isScanCooldown = true;

        // Panggil versi async dari processScannedBarcode
        // Kita tidak perlu 'await' di sini karena onScanSuccess bukan async
        // 'finally' block di dalam processScannedBarcode akan mereset flag
        processScannedBarcode(decodedText);

        if (window.navigator.vibrate) { window.navigator.vibrate(100); }
        //cameraScannerModal.hide();
        setTimeout(() => { isScanCooldown = false; }, 1000); // Cooldown untuk scan berikutnya
    }

    function onScanFailure(error) {

    }

});
</script>
@endpush
