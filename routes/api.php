<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:api'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    })->middleware('auth:sanctum');

    Route::apiResource('categories', \App\Http\Controllers\Api\V1\CategoryController::class);

    Route::post('register', \App\Http\Controllers\Api\Auth\RegisterController::class);
    Route::post('login', \App\Http\Controllers\Api\Auth\LoginController::class);
    Route::get('/products', [\App\Http\Controllers\Api\V1\ProductController::class, 'index']);
});
