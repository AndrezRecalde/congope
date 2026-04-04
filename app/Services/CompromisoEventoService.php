<?php

namespace App\Services;

use App\Models\CompromisoEvento;
use App\Models\Evento;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class CompromisoEventoService
{
    public function listar(Evento $evento): Collection
    {
        return $evento->compromisos()
            ->with('responsable')
            ->orderBy('fecha_limite', 'asc')
            ->get();
    }

    public function listarPendientesUsuario(User $usuario): Collection
    {
        // Para el dashboard del usuario
        return CompromisoEvento::where('resuelto', false)
            ->where('responsable_id', $usuario->id)
            ->with('evento')
            ->orderBy('fecha_limite', 'asc')
            ->get();
    }

    public function crear(Evento $evento, array $datos): CompromisoEvento
    {
        $datos['evento_id'] = $evento->id;
        $compromiso = CompromisoEvento::create($datos);

        $responsable = User::find($datos['responsable_id']);
        if ($responsable) {
            $responsable->notify(new \App\Notifications\NuevoCompromisoNotification($compromiso));
        }

        return $compromiso;
    }

    public function resolver(CompromisoEvento $compromiso, bool $estado = true): CompromisoEvento
    {
        $compromiso->update([
            'resuelto'    => $estado,
            'resuelto_en' => $estado ? today() : null,
        ]);
        
        return $compromiso->fresh();
    }

    public function actualizar(CompromisoEvento $compromiso, array $datos): CompromisoEvento
    {
        $compromiso->update($datos);
        
        return $compromiso->fresh();
    }
}
