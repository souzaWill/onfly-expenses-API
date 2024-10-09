<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExpenseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login');
    Route::post('logout', 'logout');
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('expenses', ExpenseController::class);

    Route::get('/user', function (Request $request) {
        //remove this route
        return $request->user();
    });
});
