<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeliveryOrderController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- RUTE UNTUK UMUM (TIDAK PERLU LOGIN) ---
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// Rute untuk menampilkan halaman registrasi
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
// Rute untuk memproses data dari form registrasi
Route::post('/register', [AuthController::class, 'register'])->name('register.post');


Route::get('/', function () {
    return redirect()->route('login');
});


// --- RUTE YANG MEMERLUKAN LOGIN ---
Route::middleware(['auth'])->group(function () {
    Route::get('/verify', [DeliveryOrderController::class, 'verifyIndex'])->name('do.verify.index');

    Route::post('/delivery-order/search', [DeliveryOrderController::class, 'search'])->name('do.verify.search');
    Route::post('/delivery-order/scan', [DeliveryOrderController::class, 'scan'])->name('do.verify.scan');
    Route::post('/delivery-order/complete-verification', [DeliveryOrderController::class, 'sendCompletionEmail'])->name('do.verify.complete');

    Route::get('/delivery-order/history', [DeliveryOrderController::class, 'historyIndex'])->name('do.history.index');
    Route::get('/delivery-order/history/details/{doNumber}', [DeliveryOrderController::class, 'getScannedItemsForDO'])->name('do.history.details');

    // Tambahkan rute logout agar bisa digunakan
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

