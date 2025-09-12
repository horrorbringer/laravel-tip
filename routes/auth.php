<?php
use Illuminate\Support\Facades\Route;

Route::get('/login', [App\Http\Controllers\AuthController::class, 'login']);
Route::post('/login', [App\Http\Controllers\AuthController::class, 'authenticate'])->name('login');
Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout')->middleware('auth');
Route::get('/register', [App\Http\Controllers\AuthController::class, 'showRegistrationForm'])->name('auth.create');
Route::post('/register', [App\Http\Controllers\AuthController::class, 'register'])->name('register');
