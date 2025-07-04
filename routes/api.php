<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\neworderController;
use App\Http\Controllers\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::middleware(['auth:sanctum', 'role:Team Lead'])->get('/team-only', function () {
    return response()->json(['message' => 'Only team can see this']);
});


//create order



Route::get('/user', [AuthController::class, 'user'])
    ->name('user')
    ->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login')
    ->middleware('guest');

Route::post('/register', [AuthController::class, 'register'])
    ->name('register')
    ->middleware('guest');


Route::middleware(['auth:sanctum', 'role:Worker|Team Lead|Department Head|Finance|Procurement|Warehouse'])->group(function () {
   Route::post('/orders/store', [OrderController::class, 'store']);
   Route::post('/orders/{order}/approve', [OrderController::class, 'approve']);
    Route::post('/orders/{order}/reject', [OrderController::class, 'reject']);
   // Route::post('/orders/{order}/return', [OrderController::class, 'return']);
});


Route::middleware(['auth:sanctum', 'role:Worker|Team Lead|Department Head|Finance|Procurement|Warehouse'])->group(function () {
   Route::post('/neworders/store', [neworderController::class, 'store']);
   Route::post('/neworders/{order}/approve', [neworderController::class, 'approve']);
    Route::post('/neworders/{order}/reject', [neworderController::class, 'reject']);
   // Route::post('/orders/{order}/return', [OrderController::class, 'return']);
});