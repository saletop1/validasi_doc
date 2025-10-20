<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeliveryOrderController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\EnsurePasswordIsChanged; // --- PERBAIKAN: Import kelas middleware ---

// Rute Publik (tidak perlu login)
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

Route::get('/', function () {
    return redirect()->route('login');
});

// Grup Rute yang memerlukan Autentikasi dan sudah ganti password
// --- PERBAIKAN: Menggunakan nama kelas langsung, bukan alias ---
Route::middleware(['auth', EnsurePasswordIsChanged::class])->group(function () {
    // Rute Aplikasi Utama
    Route::get('/verify', [DeliveryOrderController::class, 'verifyIndex'])->name('do.verify.index');
    Route::post('/delivery-order/search', [DeliveryOrderController::class, 'search'])->name('do.verify.search');
    Route::post('/delivery-order/scan', [DeliveryOrderController::class, 'scan'])->name('do.verify.scan');
    Route::post('/delivery-order/complete-verification', [DeliveryOrderController::class, 'sendCompletionEmail'])->name('do.verify.complete');
    Route::get('/delivery-order/history', [DeliveryOrderController::class, 'historyIndex'])->name('do.history.index');
    Route::get('/delivery-order/history/details/{doNumber}', [DeliveryOrderController::class, 'getScannedItemsForDO'])->name('do.history.details');

    // Rute Admin untuk Manajemen User
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
});

// Grup Rute yang hanya memerlukan Autentikasi (untuk ganti password & logout)
Route::middleware(['auth'])->group(function () {
    Route::get('/profile/password', [ProfileController::class, 'editPassword'])->name('password.edit');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('password.update');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

