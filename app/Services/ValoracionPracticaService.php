<?php

namespace App\Services;

use App\Models\BuenaPractica;
use App\Models\ValoracionPractica;
use Illuminate\Support\Facades\DB;

class ValoracionPracticaService
{
    public function valorar(BuenaPractica $practica, array $datos, $usuario): ValoracionPractica
    {
        return DB::transaction(function () use ($practica, $datos, $usuario) {
            $valoracion = ValoracionPractica::updateOrCreate(
                [
                    'practica_id' => $practica->id,
                    'user_id' => $usuario->id
                ],
                [
                    'puntuacion' => $datos['puntuacion'],
                    'comentario' => $datos['comentario'] ?? null
                ]
            );

            // Supone la existencia de un método para recalcular el promedio en el modelo
            if (method_exists($practica, 'actualizarCalificacion')) {
                $practica->actualizarCalificacion();
            }

            return $valoracion;
        });
    }

    public function eliminarValoracion(BuenaPractica $practica, $usuario): void
    {
        DB::transaction(function () use ($practica, $usuario) {
            ValoracionPractica::where('practica_id', $practica->id)
                ->where('user_id', $usuario->id)
                ->delete();

            if (method_exists($practica, 'actualizarCalificacion')) {
                $practica->actualizarCalificacion();
            }
        });
    }
}
