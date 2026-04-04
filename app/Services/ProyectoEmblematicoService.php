<?php

namespace App\Services;

use App\Models\ProyectoEmblematico;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProyectoEmblematicoService
{
    public function listar(array $filtros, $usuario): LengthAwarePaginator
    {
        $query = ProyectoEmblematico::with(['provincia', 'proyecto', 'reconocimientos'])
            ->withCount('reconocimientos');

        if (!$usuario->hasRole('super_admin') && !$usuario->hasRole('visualizador')) {
            $provinciasIds = $usuario->provincias()->pluck('provincias.id');
            $query->whereIn('provincia_id', $provinciasIds);
        }

        if (isset($filtros['provincia_id'])) {
            $query->where('provincia_id', $filtros['provincia_id']);
        }

        if (isset($filtros['es_publico'])) {
            $query->where('es_publico', filter_var($filtros['es_publico'], FILTER_VALIDATE_BOOLEAN));
        }

        if (!empty($filtros['search'])) {
            $query->where('titulo', 'ilike', '%' . $filtros['search'] . '%');
        }

        return $query->paginate(15);
    }

    public function listarPublicos(array $filtros): LengthAwarePaginator
    {
        $query = ProyectoEmblematico::where('es_publico', true)
            ->with(['provincia', 'reconocimientos']);

        return $query->paginate(12);
    }

    public function obtener(string $id): ProyectoEmblematico
    {
        return ProyectoEmblematico::with(['proyecto', 'provincia', 'reconocimientos'])
            ->findOrFail($id);
    }

    public function crear(array $datos): ProyectoEmblematico
    {
        return DB::transaction(function () use ($datos) {
            $datos['es_publico'] = false;
            return ProyectoEmblematico::create($datos);
        });
    }

    public function actualizar(ProyectoEmblematico $emblematico, array $datos): ProyectoEmblematico
    {
        unset($datos['es_publico']);

        return DB::transaction(function () use ($emblematico, $datos) {
            $emblematico->update($datos);
            return $emblematico->fresh();
        });
    }

    public function publicar(ProyectoEmblematico $emblematico, bool $estado): ProyectoEmblematico
    {
        $emblematico->update(['es_publico' => $estado]);
        return $emblematico->fresh();
    }

    public function eliminar(ProyectoEmblematico $emblematico): void
    {
        $emblematico->delete();
    }
}
