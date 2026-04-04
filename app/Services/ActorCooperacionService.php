<?php

namespace App\Services;

use App\Models\ActorCooperacion;
use App\Models\ActorAreaTematica;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Http\Response;

class ActorCooperacionService
{
    public function listar(array $filtros, $usuario): LengthAwarePaginator
    {
        $query = ActorCooperacion::with(['areasTematicas']);

        if (!empty($filtros['search'])) {
            $query->buscar($filtros['search']);
        }

        if (!empty($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }

        if (!empty($filtros['tipo'])) {
            $query->porTipo($filtros['tipo']);
        }

        if (!empty($filtros['pais_origen'])) {
            $query->porPais($filtros['pais_origen']);
        }

        // Apply scopes based on requirements or role if needed

        return $query->paginate(15);
    }

    public function obtener(string $id): ActorCooperacion
    {
        return ActorCooperacion::with(['areasTematicas', 'proyectos', 'documentos'])->findOrFail($id);
    }

    public function crear(array $datos, $usuario): ActorCooperacion
    {
        return DB::transaction(function () use ($datos) {
            $actor = ActorCooperacion::create($datos);

            if (!empty($datos['areas_tematicas'])) {
                foreach ($datos['areas_tematicas'] as $area) {
                    $actor->areasTematicas()->create(['area' => $area]);
                }
            }
            return $actor;
        });
    }

    public function actualizar(ActorCooperacion $actor, array $datos): ActorCooperacion
    {
        return DB::transaction(function () use ($actor, $datos) {
            $actor->update($datos);

            if (isset($datos['areas_tematicas'])) {
                $actor->areasTematicas()->delete();
                foreach ($datos['areas_tematicas'] as $area) {
                    $actor->areasTematicas()->create(['area' => $area]);
                }
            }

            return $actor;
        });
    }

    public function eliminar(ActorCooperacion $actor): void
    {
        $actor->delete();
    }

    public function exportarExcel(array $filtros): BinaryFileResponse
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\ActoresCooperacionExport($filtros), 'actores_cooperacion.xlsx');
    }

    public function exportarPdf(array $filtros): Response
    {
        // Require ext-dompdf to fully accomplish this
        // Placeholder
        abort(501, 'Not Implemented');
    }
}
