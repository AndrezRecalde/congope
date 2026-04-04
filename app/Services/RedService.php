<?php

namespace App\Services;

use App\Models\Red;
use App\Models\RedMiembro;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class RedService
{
    /**
     * Obtener listado de redes con filtros y paginado.
     */
    public function listar(array $filtros): LengthAwarePaginator
    {
        $query = Red::query()
            ->with(['miembros' => fn($q) => $q->limit(5)])
            ->withCount('miembros');

        if (!empty($filtros['search'])) {
            $query->where('nombre', 'ILIKE', '%' . $filtros['search'] . '%');
        }

        if (!empty($filtros['tipo'])) {
            $query->where('tipo', $filtros['tipo']);
        }

        if (!empty($filtros['rol_congope'])) {
            $query->where('rol_congope', $filtros['rol_congope']);
        }

        return $query->latest()->paginate(15);
    }

    /**
     * Obtener una red por ID con sus relaciones.
     */
    public function obtener(string $id): Red
    {
        return Red::with(['miembros.areasTematicas', 'documentos'])->findOrFail($id);
    }

    /**
     * Crear una nueva red.
     */
    public function crear(array $datos): Red
    {
        return DB::transaction(function () use ($datos) {
            $red = Red::create($datos);

            if (!empty($datos['actor_ids'])) {
                $syncData = [];
                foreach ($datos['actor_ids'] as $actorId) {
                    $syncData[$actorId] = [
                        'id' => (string) \Illuminate\Support\Str::uuid(),
                        'rol_miembro' => $datos['rol_miembro'] ?? null,
                        'fecha_ingreso' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                $red->miembros()->attach($syncData);
            }

            return $red;
        });
    }

    /**
     * Actualizar una red existente.
     */
    public function actualizar(Red $red, array $datos): Red
    {
        return DB::transaction(function () use ($red, $datos) {
            $red->update($datos);
            return $red->fresh();
        });
    }

    /**
     * Eliminar una red.
     */
    public function eliminar(Red $red): void
    {
        $red->delete();
    }

    /**
     * Gestionar (agregar o eliminar) miembros de una red.
     */
    public function gestionarMiembros(Red $red, array $datos): Red
    {
        return DB::transaction(function () use ($red, $datos) {
            $accion = $datos['accion']; // 'agregar' | 'eliminar'

            if ($accion === 'agregar') {
                foreach ($datos['actores'] as $actor) {
                    RedMiembro::firstOrCreate(
                        [
                            'red_id' => $red->id,
                            'actor_id' => $actor['actor_id']
                        ],
                        [
                            'rol_miembro' => $actor['rol_miembro'] ?? null,
                            'fecha_ingreso' => $actor['fecha_ingreso'] ?? today()
                        ]
                    );
                }
            } elseif ($accion === 'eliminar') {
                RedMiembro::where('red_id', $red->id)
                    ->whereIn('actor_id', $datos['actor_ids'])
                    ->delete();
            }

            return $red->fresh(['miembros']);
        });
    }
}
