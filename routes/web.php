<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;

Route::middleware('auth')->group(function () {
    Route::view('/', 'welcome');
    Route::get('/invoice-pdf', [InvoiceController::class, 'index']);
});

require __DIR__.'/auth.php';
require __DIR__.'/images.php';
