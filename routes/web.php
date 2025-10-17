<?php

use App\Http\Controllers\DeliveryOrderController;
use Illuminate\Support\Facades\Route;

// Rute untuk menampilkan halaman verifikasi
Route::get('/', [DeliveryOrderController::class, 'verifyIndex'])->name('do.verify.index');
Route::get('/delivery-order/verify', [DeliveryOrderController::class, 'verifyIndex'])->name('do.verify.index.alt');


// Rute untuk MENCARI DO dari SAP dan memuat progres (AJAX)
Route::post('/delivery-order/search', [DeliveryOrderController::class, 'search'])->name('do.verify.search');

// Rute untuk MENYIMPAN setiap item yang berhasil di-scan (AJAX)
Route::post('/delivery-order/scan', [DeliveryOrderController::class, 'scan'])->name('do.verify.scan');

// Rute untuk memicu pengiriman email
Route::post('/delivery-order/complete-verification', [DeliveryOrderController::class, 'sendCompletionEmail'])->name('do.verify.complete');

// Rute untuk halaman riwayat
Route::get('/delivery-order/history', [DeliveryOrderController::class, 'historyIndex'])->name('do.history.index');

// --- BARU: Rute untuk mengambil detail riwayat (AJAX) ---
Route::get('/delivery-order/history/details/{doNumber}', [DeliveryOrderController::class, 'getScannedItemsForDO'])->name('do.history.details');

