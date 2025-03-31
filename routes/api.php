<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PessoaController;

// Rota de login (pública)
Route::post('/login', [AuthController::class, 'login']);

// Rota de refresh (pública, pois usa o refresh token)
Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('jwt.auth');

// Rotas protegidas pelo JWT
Route::middleware('jwt.auth')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);

    Route::apiResource('pessoas', PessoaController::class);
});
