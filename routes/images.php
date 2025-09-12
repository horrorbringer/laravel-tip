<?php
use App\Http\Controllers\ImageUploadController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::prefix('api/images')->group(function () {
        Route::get('/', [ImageUploadController::class, 'index'])->name('images.index');
        Route::post('/upload', [ImageUploadController::class, 'upload']);
        Route::post('/upload-with-database', [ImageUploadController::class, 'uploadWithDatabase'])->name('images.uploadWithDatabase');
        Route::delete('/delete', [ImageUploadController::class, 'delete']);
        Route::post('/optimize', [ImageUploadController::class, 'getOptimizedUrl']);
    });
});
