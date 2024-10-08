<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {
    Route::post('register', 'register');
    Route::login('login', 'login');
    Route::logout('logout', 'logout');
});

Route::get('/user', function (Request $request) {
    //remove this route
    return $request->user();
})->middleware('auth:sanctum');
