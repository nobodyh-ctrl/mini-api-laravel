<?php

use App\Http\Controllers\Api\JugadorController;
use Illuminate\Support\Facades\Route;

Route::get('/jugadores', [JugadorController::class, 'index']);
Route::get('/jugadores/{jugador}', [JugadorController::class, 'show']);
