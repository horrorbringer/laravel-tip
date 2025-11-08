<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\PayWayController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth','verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::post('/payway/checkout', [PayWayController::class,'checkoutDeeplink'])
        ->name('payway.checkout');
        Route::post('/payway/callback', [PayWayController::class, 'callback']) // PayWay pushback
    ->name('payway.callback');

Route::get('/payway/check/{tran_id}', [PayWayController::class, 'check'])
    ->name('payway.check'); // polling from the UI

    Route::post('/payway/checkout/cards', [PayWayController::class, 'checkoutCards'])->name('payway.checkoutCards');
    // routes/web.php
Route::post('/payway/checkout/khqr', [PayWayController::class, 'startKhqrDeeplink'])
    ->name('payway.checkout.khqr');

Route::get('/payway/check', [PayWayController::class, 'poll'])
    ->name('payway.check');


Route::get('/payway/return',   [PayWayController::class,'return'])->name('payway.return');
Route::get('/payway/cancel',   [PayWayController::class,'cancel'])->name('payway.cancel');
Route::post('/payway/webhook', [PayWayController::class,'webhook'])->name('payway.webhook'); // give this to ABA
Route::get('/payway/khqr/{tranId}', [PayWayController::class, 'khqr'])->name('payway.khqr');
Route::get('/payway/poll/{tranId}', [PayWayController::class, 'poll'])->name('payway.poll');

Route::get('/orders',            [OrderController::class,'index'])->name('orders.index');
Route::get('/orders/{tran_id}',  [OrderController::class,'show'])->name('orders.show');
Route::post('/orders',           [OrderController::class,'store'])->name('orders.store');

require __DIR__.'/auth.php';
