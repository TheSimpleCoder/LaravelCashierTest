<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OrdersController;
use App\Http\Controllers\Api\CallbackController;

Route::post('/orders', [OrdersController::class, 'orders'])->name('orders.get');
Route::post('/callback', CallbackController::class)->name('callback');
