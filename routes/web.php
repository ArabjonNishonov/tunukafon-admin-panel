<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\OrderController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/order/add/{product}', [OrderController::class, 'addToOrder'])
        ->name('order.add');

    Route::post('/order/checkout/{order}', [OrderController::class, 'checkout'])
        ->name('order.checkout');

    Route::post('/order/complete/{order}', [OrderController::class, 'complete'])
        ->name('order.complete');
});


require __DIR__.'/auth.php';
