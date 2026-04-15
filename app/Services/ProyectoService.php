<?php

namespace App\Services;

use App\Models\Proyecto;
use App\Models\ProyectoUbicacion;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Http\Response;

class ProyectoService
{
    public function listar(array $filtros, $usuario): LengthAwarePaginator
    {
        $query = Proyecto::with(['provincias', 'ods', 'actor']);

        if (!$usuario->can('proyectos.ver_todas_provincias')) {
            $provinciaIds = $usuario->provincias()->pluck('provincias.id');
            $query->whereHas('provincias', function ($q) use ($provinciaIds) {
                $q->whereIn('provincias.id', $provinciaIds);
            });
        }

        if (!empty($filtros['search'])) {
            $query->where('nombre', 'LIKE', '%' . $filtros['search'] . '%')
                  ->orWhere('codigo', 'LIKE', '%' . $filtros['search'] . '%');
        }

        if (!empty($filtros['estado'])) {
            $query->estado($filtros['estado']);
        }

        if (!empty($filtros['actor_id'])) {
            $query->where('actor_id', $filtros['actor_id']);
        }

        return $query->paginate(15);
    }

    public function obtener(string $id, $usuario): Proyecto
    {
        $query = Proyecto::with(['provincias', 'ubicaciones.canton', 'ods', 'actor', 'hitos', 'documentos']);

        if (!$usuario->can('proyectos.ver_todas_provincias')) {
            $provinciaIds = $usuario->provincias()->pluck('provincias.id');
            $query->whereHas('provincias', function ($q) use ($provinciaIds) {
                $q->whereIn('provincias.id', $provinciaIds);
            });
        }

        return $query->findOrFail($id);
    }

    public function crear(array $datos, $usuario): Proyecto
    {
        return DB::transaction(function () use ($datos, $usuario) {
            $datos['creado_por'] = $usuario->id;

            if (empty($datos['codigo'])) {
                $datos['codigo'] = 'PRJ-' . strtoupper(uniqid());
            }

            $proyecto = Proyecto::create($datos);

            if (isset($datos['provincias'])) {
                $syncData = [];
                foreach ($datos['provincias'] as $provincia) {
                    $syncData[$provincia['id']] = [
                        'rol'                     => $provincia['rol'] ?? 'Beneficiaria',
                        'porcentaje_avance'       => $provincia['porcentaje_avance'] ?? 0,
                        'beneficiarios_directos'  => $provincia['beneficiarios_directos'] ?? null,
                        'beneficiarios_indirectos' => $provincia['beneficiarios_indirectos'] ?? null,
                    ];
                }
                $proyecto->provincias()->sync($syncData);
            }

            // Cada ubicación ahora lleva su canton_id explícito.
            if (isset($datos['ubicaciones'])) {
                foreach ($datos['ubicaciones'] as $ubicacion) {
                    $ub = ProyectoUbicacion::create([
                        'proyecto_id' => $proyecto->id,
                        'canton_id'   => $ubicacion['canton_id'],
                        'nombre'      => $ubicacion['nombre'] ?? null,
                    ]);
                    DB::statement(
                        "UPDATE proyecto_ubicaciones SET ubicacion = ST_SetSRID(ST_MakePoint(?, ?), 4326) WHERE id = ?",
                        [$ubicacion['lng'], $ubicacion['lat'], $ub->id]
                    );
                }
            }

            if (!empty($datos['ods_ids'])) {
                $proyecto->ods()->sync($datos['ods_ids']);
            }

            \Illuminate\Support\Facades\Cache::forget('portal.mapa.catalogos');
            \Illuminate\Support\Facades\Cache::forget('portal.conteos');
            \Illuminate\Support\Facades\Cache::forget('portal.estadisticas');

            return $proyecto;
        });
    }

    public function actualizar(Proyecto $proyecto, array $datos): Proyecto
    {
        return DB::transaction(function () use ($proyecto, $datos) {
            $proyecto->update($datos);

            if (isset($datos['provincias'])) {
                $syncData = [];
                foreach ($datos['provincias'] as $provincia) {
                    $syncData[$provincia['id']] = [
                        'rol'                     => $provincia['rol'] ?? 'Beneficiaria',
                        'porcentaje_avance'       => $provincia['porcentaje_avance'] ?? 0,
                        'beneficiarios_directos'  => $provincia['beneficiarios_directos'] ?? null,
                        'beneficiarios_indirectos' => $provincia['beneficiarios_indirectos'] ?? null,
                    ];
                }
                $proyecto->provincias()->sync($syncData);
            }

            // Reemplaza todas las ubicaciones; cada una lleva su canton_id.
            if (isset($datos['ubicaciones'])) {
                $proyecto->ubicaciones()->delete();
                foreach ($datos['ubicaciones'] as $ubicacion) {
                    $ub = ProyectoUbicacion::create([
                        'proyecto_id' => $proyecto->id,
                        'canton_id'   => $ubicacion['canton_id'],
                        'nombre'      => $ubicacion['nombre'] ?? null,
                    ]);
                    DB::statement(
                        "UPDATE proyecto_ubicaciones SET ubicacion = ST_SetSRID(ST_MakePoint(?, ?), 4326) WHERE id = ?",
                        [$ubicacion['lng'], $ubicacion['lat'], $ub->id]
                    );
                }
            }

            if (isset($datos['ods_ids'])) {
                $proyecto->ods()->sync($datos['ods_ids']);
            }

            \Illuminate\Support\Facades\Cache::forget('portal.mapa.catalogos');
            \Illuminate\Support\Facades\Cache::forget('portal.conteos');
            \Illuminate\Support\Facades\Cache::forget('portal.estadisticas');

            return $proyecto;
        });
    }

    public function eliminar(Proyecto $proyecto): void
    {
        $proyecto->delete();
        \Illuminate\Support\Facades\Cache::forget('portal.mapa.catalogos');
        \Illuminate\Support\Facades\Cache::forget('portal.conteos');
        \Illuminate\Support\Facades\Cache::forget('portal.estadisticas');
    }

    public function cambiarEstado(Proyecto $proyecto, string $nuevoEstado): void
    {
        $proyecto->update(['estado' => $nuevoEstado]);
        \Illuminate\Support\Facades\Cache::forget('portal.mapa.catalogos');
        \Illuminate\Support\Facades\Cache::forget('portal.conteos');
        \Illuminate\Support\Facades\Cache::forget('portal.estadisticas');
    }

    public function exportarExcel(array $filtros): BinaryFileResponse
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ProyectosExport($filtros),
            'proyectos.xlsx'
        );
    }

    public function exportarPdf(array $filtros): Response
    {
        abort(501, 'No implementado');
    }
}
