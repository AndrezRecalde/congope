<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Rutas públicas (sin autenticación)
    Route::prefix('auth')->name('auth.')->group(function () {
        // Placeholder: el agente de Auth completará estas rutas
    });

    // Rutas protegidas
    Route::middleware('auth:sanctum')->group(function () {
        // Placeholder: cada agente de módulo añadirá sus rutas
    });

});
