<?php

namespace App\Services;

use App\Models\Evento;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class EventoService
{
    public function listar(array $filtros): LengthAwarePaginator
    {
        $query = Evento::query();

        if (isset($filtros['tipo_evento'])) {
            $query->where('tipo_evento', $filtros['tipo_evento']);
        }

        if (isset($filtros['fecha_desde'])) {
            $query->where('fecha_evento', '>=', $filtros['fecha_desde']);
        }

        if (isset($filtros['fecha_hasta'])) {
            $query->where('fecha_evento', '<=', $filtros['fecha_hasta']);
        }

        if (isset($filtros['es_virtual'])) {
            $query->where('es_virtual', $filtros['es_virtual']);
        }

        if (isset($filtros['search'])) {
            $query->where('titulo', 'like', '%' . $filtros['search'] . '%');
        }

        return $query->orderBy('fecha_evento', 'asc')
            ->with(['creadoPor', 'compromisos'])
            ->withCount(['participantes', 'compromisos'])
            ->paginate(15);
    }

    public function obtener(string $id): Evento
    {
        return Evento::with([
            'creadoPor',
            'participantes',
            'compromisos.responsable',
            'documentos'
        ])->findOrFail($id);
    }

    public function crear(array $datos, User $usuario): Evento
    {
        return DB::transaction(function () use ($datos, $usuario) {
            $datos['creado_por'] = $usuario->id;
            
            $evento = Evento::create($datos);
            
            // Auto-inscribir al creador como participante
            $evento->participantes()->attach($usuario->id, [
                'asistio'       => false,
                'confirmado_en' => now(),
            ]);
            
            return $evento;
        });
    }

    public function actualizar(Evento $evento, array $datos): Evento
    {
        $evento->update($datos);
        return $evento->fresh();
    }

    public function eliminar(Evento $evento): void
    {
        $evento->delete();
    }

    public function gestionarParticipantes(Evento $evento, array $datos): Evento
    {
        $accion = $datos['accion'];

        DB::transaction(function () use ($evento, $datos, $accion) {
            switch ($accion) {
                case 'agregar':
                    foreach ($datos['user_ids'] as $userId) {
                        $evento->participantes()->syncWithoutDetaching([
                            $userId => ['confirmado_en' => now()]
                        ]);
                    }

                    $usuarios = User::whereIn('id', $datos['user_ids'])->get();
                    foreach ($usuarios as $usuario) {
                        /** @var \App\Models\User $usuario */
                        $usuario->notify(new \App\Notifications\InvitacionEventoNotification($evento));
                    }
                    break;
                    
                case 'eliminar':
                    $evento->participantes()->detach($datos['user_ids']);
                    break;
                    
                case 'marcar_asistencia':
                    $evento->participantes()->updateExistingPivot(
                        $datos['user_id'],
                        ['asistio' => $datos['asistio']]
                    );
                    break;
            }
        });

        return $evento->fresh(['participantes']);
    }
}
