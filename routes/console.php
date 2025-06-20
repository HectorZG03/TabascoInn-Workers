<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;



Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


use Illuminate\Support\Facades\Schedule;
Schedule::command('contratos:verificar-vencidos')
    ->dailyAt('03:00') // ⏰ puedes cambiar la hora
    ->onSuccess(fn () => logger('✅ Contratos verificados correctamente.'))
    ->onFailure(fn () => logger('❌ Error al verificar contratos.'));
