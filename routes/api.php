<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PessoaController;
use App\Http\Controllers\ServidorEfetivoController;

// Rota de login (pública)
Route::post('/login', [AuthController::class, 'login']);

// Rota de refresh (pública, pois usa o refresh token)
Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('jwt.auth');

// Rotas protegidas pelo JWT
Route::middleware('jwt.auth')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);

    Route::apiResource('pessoas', PessoaController::class);
    // Rotas personalizadas para servidores efetivos
    Route::get('servidores-efetivos', [ServidorEfetivoController::class, 'index']);
    Route::post('servidores-efetivos', [ServidorEfetivoController::class, 'store']);
    Route::put('servidores-efetivos/{id}', [ServidorEfetivoController::class, 'update']);
    Route::delete('servidores-efetivos/{id}', [ServidorEfetivoController::class, 'destroy']);
});
