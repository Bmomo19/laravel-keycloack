<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\KeycloakAuth;


Route::prefix('auth')->group(function () {
    Route::post('/refresh', [AuthController::class, 'refreshToken']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware([KeycloakAuth::class])->group(function () {
        Route::get('/user', [AuthController::class, 'getUserInfo']);
    });
});

// Routes protégées
Route::middleware([KeycloakAuth::class])->group(function () {
    Route::get('/protected-data', function () {
        return response()->json(['data' => 'Données protégées']);
    });
});
