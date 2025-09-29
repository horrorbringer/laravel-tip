<?php

use App\Http\Controllers\GoogleController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;

Route::middleware('auth')->group(function () {
    Route::view('/', 'welcome');
    Route::get('/invoice-pdf', [InvoiceController::class, 'index']);
});

Route::get('auth/google/redirect', [GoogleController::class, 'redirect'])->name('google.login');
Route::get('auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');
