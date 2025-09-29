<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:api'])->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user();
    })->middleware('auth:sanctum');

    Route::apiResource('categories', \App\Http\Controllers\Api\V2\CategoryController::class)
        ->middleware('auth:sanctum');

    Route::post('register', \App\Http\Controllers\Api\Auth\RegisterController::class);
    Route::post('login', \App\Http\Controllers\Api\Auth\LoginController::class);
    Route::get('/products', [\App\Http\Controllers\Api\V2\ProductController::class, 'index']);
});
