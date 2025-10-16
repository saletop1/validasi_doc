<?php

use App\Http\Controllers\DeliveryOrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Di sini Anda dapat mendaftarkan rute web untuk aplikasi Anda. Rute-rute
| ini dimuat oleh RouteServiceProvider dan semuanya akan
| ditetapkan ke grup middleware "web". Buat sesuatu yang hebat!
|
*/

// Rute untuk menampilkan halaman verifikasi utama
Route::get('/delivery-order/verify', [DeliveryOrderController::class, 'verifyIndex'])->name('do.verify.index');

// --- PERBAIKAN ---
// URL dan nama method disesuaikan agar cocok dengan frontend (JavaScript) dan Controller.
// Sebelumnya: Route::post('/delivery-order/verify', [DeliveryOrderController::class, 'searchDO'])
Route::post('/delivery-order/search', [DeliveryOrderController::class, 'search'])->name('do.verify.search');

// --- PERBAIKAN ---
// Nama method 'saveScan' diubah menjadi 'scan' agar sesuai dengan yang ada di Controller.
// Sebelumnya: Route::post('/delivery-order/scan', [DeliveryOrderController::class, 'saveScan'])
Route::post('/delivery-order/scan', [DeliveryOrderController::class, 'scan'])->name('do.verify.scan');

// Catatan: Rute ini dinonaktifkan sementara karena method 'completeAndSaveVerification' tidak ditemukan di Controller.
// Anda bisa mengaktifkannya kembali jika sudah membuat method tersebut.
// Route::post('/delivery-order/complete', [DeliveryOrderController::class, 'completeAndSaveVerification'])->name('do.verify.complete');
