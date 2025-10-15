<?php

use App\Http\Controllers\DeliveryOrderController;
use Illuminate\Support\Facades\Route;

// Rute untuk menampilkan halaman verifikasi
Route::get('/delivery-order/verify', [DeliveryOrderController::class, 'verifyIndex'])->name('do.verify.index');

// Rute untuk MENCARI DO dari SAP dan memuat progres (AJAX)
Route::post('/delivery-order/verify', [DeliveryOrderController::class, 'searchDO'])->name('do.verify.search');

// Rute untuk MENYIMPAN setiap item yang berhasil di-scan (AJAX)
Route::post('/delivery-order/scan', [DeliveryOrderController::class, 'saveScan'])->name('do.verify.scan');

// Rute untuk MENYIMPAN verifikasi yang sudah selesai ke DB (AJAX)
Route::post('/delivery-order/complete', [DeliveryOrderController::class, 'completeAndSaveVerification'])->name('do.verify.complete');
