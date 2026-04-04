<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;

Schedule::command('congope:alertas-vencimiento')
    ->dailyAt('07:00')
    ->timezone('America/Guayaquil')
    ->withoutOverlapping()
    ->onFailure(function () {
        Log::error('Falló congope:alertas-vencimiento');
    });

Schedule::command('congope:limpiar-auditoria')
    ->monthlyOn(1, '02:00')
    ->timezone('America/Guayaquil')
    ->withoutOverlapping();
