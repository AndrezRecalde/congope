<?php

namespace App\Services;

use App\Models\Reconocimiento;
use App\Models\ProyectoEmblematico;

class ReconocimientoService
{
    public function crear(ProyectoEmblematico $emblematico, array $datos): Reconocimiento
    {
        $datos['emblematico_id'] = $emblematico->id;
        return Reconocimiento::create($datos);
    }

    public function actualizar(Reconocimiento $reconocimiento, array $datos): Reconocimiento
    {
        $reconocimiento->update($datos);
        return $reconocimiento->fresh();
    }

    public function eliminar(Reconocimiento $reconocimiento): void
    {
        $reconocimiento->delete();
    }
}
