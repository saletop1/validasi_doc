@extends('layouts.app')

@section('title', 'Verifikasi Delivery Order')

@push('styles')
<style>
    body {
        background-color: #f8f9fa;
    }
    .main-card {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 0.5rem 1rem 0 rgba(0, 0, 0, 0.1);
    }
    .card-header-custom {
        position: relative;
        background: linear-gradient(45deg, #0d6efd, #0dcaf0);
        color: white;
        border-radius: 1rem 1rem 0 0 !important;
        padding: 1.5rem;
    }
    .header-logo {
        position: absolute;
        top: 15px;
        left: 20px;
        max-height: 60px;
        opacity: 0.9;
    }
    .card-footer-custom {
        background-color: #f8f9fa;
        border-top: 1px solid #e9ecef;
        border-radius: 0 0 1rem 1rem !important;
    }
    .btn-gradient {
        background: linear-gradient(45deg, #198754, #28a745);
        border: none;
        color: white;
    }
    .info-card {
        background-color: #e9ecef;
        border-radius: 0.75rem;
        padding: 1.5rem;
        height: 100%;
    }
    .table-responsive {
        max-height: 60vh;
        overflow-y: auto;
    }
    .table thead th {
        position: sticky; top: 0; z-index: 2;
        background-color: #212529;
        color: white;
    }

    .table-item-info .main-item-row:hover {
        background-color: #f5f5f5;
    }
    .info-icon {
        display: inline-block;
        width: 22px;
        height: 22px;
        line-height: 22px;
        text-align: center;
        border-radius: 50%;
        background-color: #0dcaf0;
        color: white;
        font-style: normal;
        font-weight: bold;
        cursor: pointer;
        transition: background-color 0.2s, transform 0.2s;
        vertical-align: middle;
        margin-left: 5px;
    }
    .info-icon:hover {
        background-color: #0b9eb8;
        transform: scale(1.1);
    }
    .main-item-row.expanded .info-icon {
        background-color: #0d6efd;
    }
    .hu-batch-cell {
        min-width: 120px;
        text-align: center;
    }
    .hu-count-badge {
        font-weight: 500;
        color: #212529;
    }
    .details-row > td {
        padding: 0 !important;
        border-top: none;
    }
    .details-table-wrapper {
        padding: 1rem 1rem 1rem 4rem;
        background-color: #f1f1f1;
        border-bottom: 1px solid #dee2e6;
    }
    .details-table {
        background-color: #ffffff;
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 0.2rem 0.5rem rgba(0,0,0,.05);
    }
    .summary-card h4 {
        margin-top: 0.25rem;
    }
    #loader {
        display: none;
        border: 5px solid #f3f3f3;
        border-top: 5px solid #3498db;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        animation: spin 1s linear infinite;
        position: fixed;
        top: 50%;
        left: 50%;
        z-index: 1056;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endpush

@section('content')
<div id="loader"></div>
<div class="container-fluid py-4">
    <div class="card main-card">
        <div class="card-header card-header-custom text-center">
            <img src="{{ asset('images/KMI.png') }}" alt="Logo KMI" class="header-logo">
            <h2 class="mb-0 h3"><i class="fas fa-truck me-2"></i> Verifikasi & Scan Delivery Order (DO)</h2>
        </div>
        <div class="card-body p-4 p-md-5">
            {{-- Bagian Input & Cari DO --}}
            <div class="row justify-content-center mb-5">
                <div class="col-lg-8 col-md-10">
                    <div class="form-group">
                        <label for="do_number" class="form-label fs-5 fw-bold mb-2">Nomor Delivery Order</label>
                        <div class="input-group">
                            <input type="text" class="form-control form-control-lg" id="do_number" placeholder="Masukkan nomor DO..." name="do_number">
                            <button class="btn btn-gradient px-4" type="button" id="btn-search-do"><i class="fas fa-search me-2"></i>Cari</button>
                        </div>
                    </div>
                </div>
            </div>

            <div id="do-details" class="d-none">
                <hr class="my-5">
                <div class="row mb-4">
                    {{-- Info Pengiriman --}}
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <div class="info-card">
                            <h4 class="mb-3"><i class="fas fa-info-circle me-2"></i>Informasi Pengiriman</h4>
                            <p class="mb-2"><strong>Pelanggan:</strong> <span id="customer-name"></span></p>
                            <p class="mb-2"><strong>Alamat:</strong> <span id="customer-address"></span></p>
                            <p class="mb-2"><strong>Shipping Point:</strong> <span id="shipping-point"></span></p>
                            <p class="mb-2"><strong>Ship To:</strong> <span id="ship-to"></span></p>
                            <p class="mb-0"><strong>Ship Type:</strong> <span id="ship-type"></span></p>
                        </div>
                    </div>
                    {{-- Info Scan --}}
                    <div class="col-lg-6">
                         <div class="info-card bg-info text-white text-center d-flex flex-column justify-content-center">
                             <h4 class="mb-3"><i class="fas fa-qrcode me-2"></i>Scan Verifikasi</h4>
                             <p class="mb-2">Gunakan scanner genggam atau klik tombol kamera.</p>
                             <input type="text" class="form-control form-control-lg text-center mb-2" id="scan-verification-input" placeholder="Arahkan scanner ke sini...">
                             <button class="btn btn-light" id="btn-camera-scan"><i class="fas fa-camera me-2"></i>Scan dengan Kamera</button>
                        </div>
                    </div>
                </div>

                {{-- --- KARTU RINGKASAN BARU --- --}}
                <div class="card mt-4 summary-card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Ringkasan Verifikasi</h5>
                    </div>
                    <div class="card-body">
                        {{-- Tata letak menjadi 4 kolom --}}
                        <div class="row text-center">
                            <div class="col-md-3 col-6 mb-3 mb-md-0">
                                <h6 class="text-muted">Total Item (SKU)</h6>
                                <h4 class="fw-bold" id="summary-total-item">0</h4>
                            </div>
                            <div class="col-md-3 col-6 mb-3 mb-md-0">
                                <h6 class="text-muted">Total Qty Order</h6>
                                <h4 class="fw-bold text-primary" id="summary-qty-order">0</h4>
                            </div>
                            <div class="col-md-3 col-6 mb-3 mb-md-0">
                                <h6 class="text-muted">Total Qty Telah Scan</h6>
                                <h4 class="fw-bold text-success" id="summary-qty-scanned">0</h4>
                            </div>
                            <div class="col-md-3 col-6">
                                <h6 class="text-muted">Total Sisa Belum Scan</h6>
                                <h4 class="fw-bold text-danger" id="summary-sisa">0</h4>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tabel Rincian Item --}}
                <div class="card mt-4">
                    <div class="card-header bg-warning"><h5 class="mb-0"><i class="fas fa-list-check me-2"></i>Rincian Item (Picking List)</h5></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 table-item-info">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="text-center">No.</th>
                                        <th>Material</th>
                                        <th>Deskripsi</th>
                                        <th class="text-center">DO</th>
                                        <th class="text-center">Item</th>
                                        <th class="text-center">Batch / HU</th>
                                        <th class="text-center">Qty Order</th>
                                        <th class="text-center">Qty Scan</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="picking-list-body"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-center text-muted card-footer-custom"><small>&copy; 2025 PT. Kayu Mebel Indonesia</small></div>
    </div>
</div>

{{-- Modal Kamera --}}
<div class="modal fade" id="cameraScannerModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Arahkan Kamera ke Barcode/QR Code</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="reader" width="100%"></div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Variabel Global ---
    const doNumberInput = document.getElementById('do_number');
    const btnSearchDO = document.getElementById('btn-search-do');
    const pickingListBody = document.getElementById('picking-list-body');
    const doDetailsSection = document.getElementById('do-details');
    const loader = document.getElementById('loader');
    const scanInput = document.getElementById('scan-verification-input');

    // --- Variabel untuk Kamera ---
    const btnCameraScan = document.getElementById('btn-camera-scan');
    const cameraScannerModal = new bootstrap.Modal(document.getElementById('cameraScannerModal'));
    let html5QrcodeScanner;
    let isScanCooldown = false; // Flag untuk jeda scan kamera

    let currentDOData = null;
    let scannedHUs = new Set();

    // --- Cek sessionStorage saat halaman dimuat ---
    const lastDO = sessionStorage.getItem('lastSearchedDO');
    if (lastDO) {
        doNumberInput.value = lastDO;
        searchDO(); // Secara otomatis memicu pencarian
    }

    function showAlert(title, text, icon) {
        Swal.fire({ title, text, icon, timer: 2000, showConfirmButton: false });
    }

    // --- FUNGSI UTAMA ---
    function searchDO() {
        const doNumber = doNumberInput.value.trim();
        if (!doNumber) {
            // Hapus progress jika input kosong
            sessionStorage.removeItem('lastSearchedDO');
            sessionStorage.removeItem(`scanProgress_${doNumber}`);
            return;
        }
        loader.style.display = 'block';

        fetch('{{ route("do.verify.search") }}', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: JSON.stringify({ do_number: doNumber })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentDOData = data.data;
                scannedHUs.clear();
                displayDODetails();
                doDetailsSection.classList.remove('d-none');
                sessionStorage.setItem('lastSearchedDO', doNumber);
            } else {
                showAlert('Gagal', data.message || 'Data tidak ditemukan.', 'error');
                doDetailsSection.classList.add('d-none');
                sessionStorage.removeItem('lastSearchedDO');
                sessionStorage.removeItem(`scanProgress_${doNumber}`);
            }
        })
        .finally(() => {
            loader.style.display = 'none';
        });
    }

    function displayDODetails() {
        if (!currentDOData) return;

        // PERBAIKAN: Muat progres dari sessionStorage
        const savedProgress = JSON.parse(sessionStorage.getItem(`scanProgress_${doNumberInput.value.trim()}`));
        if (savedProgress && savedProgress.hus) {
            scannedHUs = new Set(savedProgress.hus);
        } else {
            scannedHUs.clear();
        }

        document.getElementById('customer-name').textContent = currentDOData.customer || 'T/A';
        document.getElementById('customer-address').textContent = currentDOData.address || 'T/A';
        document.getElementById('shipping-point').textContent = currentDOData.shipping_point || 'T/A';
        document.getElementById('ship-to').textContent = currentDOData.ship_to || 'T/A';
        document.getElementById('ship-type').textContent = currentDOData.ship_type || 'T/A';

        pickingListBody.innerHTML = '';
        currentDOData.items.forEach((item, index) => {
            const detailsId = `details-item-${index}`;
            let detailsHtml = '';
            let batchHuCellHtml = '';
            // Muat jumlah scan yang tersimpan
            const savedScanCount = (savedProgress && savedProgress.counts) ? (savedProgress.counts[item.material] || 0) : 0;

            if (item.is_hu) {
                batchHuCellHtml = `<span class="hu-count-badge">${item.hu_details.length} HU<i class="info-icon" data-bs-target="#${detailsId}">i</i></span>`;
                detailsHtml = `<tr class="details-row collapse" id="${detailsId}"><td colspan="9"><div class="details-table-wrapper"><h6 class="mb-2">Rincian Handling Units (HU)</h6><table class="table table-bordered table-sm details-table"><thead><tr><th>HU Number</th><th>Batch Number</th><th>DO</th><th>Item</th></tr></thead><tbody>${item.hu_details.map(detail => `<tr><td>${detail.hu_no}</td><td>${detail.batch_no}</td><td>${detail.do_no}</td><td>${detail.item_no}</td></tr>`).join('')}</tbody></table></div></td></tr>`;
            } else {
                batchHuCellHtml = `<span>${item.batch_no}</span>`;
            }

            // Tentukan status awal berdasarkan progres yang dimuat
            let initialStatusClass = 'bg-secondary';
            let initialStatusText = 'Belum Diverifikasi';
            if (savedScanCount > 0) {
                if (savedScanCount >= item.qty_order) {
                    initialStatusClass = 'bg-success';
                    initialStatusText = 'Lengkap';
                } else {
                    initialStatusClass = 'bg-warning';
                    initialStatusText = 'Kurang';
                }
            }

            const mainRowClass = item.is_hu ? 'main-item-row has-details' : 'main-item-row';
            const mainRowHtml = `<tr class="${mainRowClass}" id="main-row-${index}"><td class="text-center">${index + 1}</td><td><strong>${item.material}</strong></td><td>${item.description}</td><td class="text-center">${item.do_no}</td><td class="text-center">${item.item_no}</td><td class="hu-batch-cell">${batchHuCellHtml}</td><td class="text-center">${item.qty_order}</td><td class="text-center" id="scancount-${item.material}">${savedScanCount}</td><td class="text-center" id="status-${item.material}"><span class="badge ${initialStatusClass}">${initialStatusText}</span></td></tr>`;

            pickingListBody.innerHTML += mainRowHtml + detailsHtml;
        });

        // Event listener manual untuk ikon 'i'
        document.querySelectorAll('.info-icon').forEach(icon => {
            icon.addEventListener('click', function (event) {
                event.stopPropagation();
                const targetId = this.getAttribute('data-bs-target');
                if (targetId) {
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        const bsCollapse = bootstrap.Collapse.getOrCreateInstance(targetElement);
                        bsCollapse.toggle();
                    }
                }
            });
        });

        // Event listener untuk styling saat expand/collapse
        document.querySelectorAll('.details-row.collapse').forEach(detailsRow => {
            const mainRowId = `main-row-${detailsRow.id.split('-').pop()}`;
            const mainRow = document.getElementById(mainRowId);
            if (mainRow) {
                detailsRow.addEventListener('show.bs.collapse', () => mainRow.classList.add('expanded'));
                detailsRow.addEventListener('hide.bs.collapse', () => mainRow.classList.remove('expanded'));
            }
        });

        // Inisialisasi ringkasan
        updateSummary();
    }

    // --- FUNGSI SCANNING & SUMMARY BARU ---
    function processScannedBarcode(barcode) {
        if (!currentDOData) return;

        const code = barcode.trim();
        let foundItem = null;
        let isHUscan = false;

        for (const item of currentDOData.items) {
            if (item.is_hu && item.hu_details.some(detail => detail.hu_no === code)) {
                foundItem = item;
                isHUscan = true;
                break;
            }
            if (item.material === code) {
                if (item.is_hu) {
                    showAlert('Scan HU!', `Item ini dikelola oleh HU. Silakan scan nomor HU.`, 'warning');
                    return;
                }
                foundItem = item;
                isHUscan = false;
                break;
            }
        }

        if (foundItem) {
            if (isHUscan && scannedHUs.has(code)) {
                showAlert('Duplikat!', `HU ${code} sudah pernah discan.`, 'warning');
                return;
            }

            const qtyOrder = foundItem.qty_order;
            const scanCountEl = document.getElementById(`scancount-${foundItem.material}`);
            let currentScan = parseInt(scanCountEl.textContent, 10);

            if (currentScan < qtyOrder) {
                currentScan++;
                scanCountEl.textContent = currentScan;

                if (isHUscan) {
                    scannedHUs.add(code);
                }

                showAlert('Berhasil!', `Item ${foundItem.material} discan.`, 'success');

                const statusEl = document.getElementById(`status-${foundItem.material}`).querySelector('.badge');
                if (currentScan === qtyOrder) {
                    statusEl.textContent = 'Lengkap';
                    statusEl.classList.remove('bg-secondary', 'bg-warning');
                    statusEl.classList.add('bg-success');
                } else {
                    statusEl.textContent = 'Kurang';
                    statusEl.classList.remove('bg-secondary', 'bg-success');
                    statusEl.classList.add('bg-warning');
                }

                updateSummary();
            } else {
                showAlert('Penuh!', `Item ${foundItem.material} sudah memenuhi kuantitas.`, 'warning');
            }
        } else {
            showAlert('Gagal!', `Kode "${code}" tidak ditemukan di DO ini.`, 'error');
        }
    }

    function updateSummary() {
        if (!currentDOData) return;

        let totalOrder = 0;
        let totalScanned = 0;
        const totalItems = currentDOData.items.length;
        const currentProgress = {
            counts: {},
            hus: Array.from(scannedHUs)
        };

        currentDOData.items.forEach(item => {
            totalOrder += item.qty_order;
            const scanCountEl = document.getElementById(`scancount-${item.material}`);
            if (scanCountEl) {
                const scannedValue = parseInt(scanCountEl.textContent, 10);
                totalScanned += scannedValue;
                currentProgress.counts[item.material] = scannedValue;
            }
        });

        const sisa = totalOrder - totalScanned;

        document.getElementById('summary-total-item').textContent = totalItems;
        document.getElementById('summary-qty-order').textContent = totalOrder;
        document.getElementById('summary-qty-scanned').textContent = totalScanned;
        document.getElementById('summary-sisa').textContent = sisa;

        // PERBAIKAN: Simpan progres setiap kali summary di-update
        sessionStorage.setItem(`scanProgress_${doNumberInput.value.trim()}`, JSON.stringify(currentProgress));
    }

    // --- EVENT LISTENERS ---
    btnSearchDO.addEventListener('click', searchDO);
    doNumberInput.addEventListener('keypress', (e) => e.key === 'Enter' && searchDO());

    scanInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const code = this.value.trim();
            if (code) {
                processScannedBarcode(code);
                this.value = '';
            }
        }
    });

    // --- LOGIKA UNTUK KAMERA ---
    btnCameraScan.addEventListener('click', () => {
        cameraScannerModal.show();
    });

    document.getElementById('cameraScannerModal').addEventListener('shown.bs.modal', function () {
        if (!html5QrcodeScanner || !html5QrcodeScanner.isScanning) {
            html5QrcodeScanner = new Html5Qrcode("reader");
            const config = { fps: 10, qrbox: { width: 250, height: 250 } };
            html5QrcodeScanner.start({ facingMode: "environment" }, config, onScanSuccess, onScanFailure)
                .catch(err => console.error("Gagal memulai scanner", err));
        }
    });

    document.getElementById('cameraScannerModal').addEventListener('hidden.bs.modal', function () {
        if (html5QrcodeScanner && html5QrcodeScanner.isScanning) {
            html5QrcodeScanner.stop().catch(err => console.error("Gagal menghentikan scanner.", err));
        }
    });

    function onScanSuccess(decodedText, decodedResult) {
        // PERBAIKAN: Logika Jeda (Cooldown)
        if (isScanCooldown) {
            return; // Abaikan scan jika masih dalam masa jeda
        }

        processScannedBarcode(decodedText);

        isScanCooldown = true;
        setTimeout(() => {
            isScanCooldown = false;
        }, 3000); // Jeda 3 detik
    }

    function onScanFailure(error) {
        // Abaikan error
    }

});
</script>
@endpush

