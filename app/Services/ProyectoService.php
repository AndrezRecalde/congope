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
    public function listar(array $filtros, $usuario, int $perPage = 15): LengthAwarePaginator
    {
        $query = Proyecto::with(['provincias', 'ods', 'actores', 'beneficiarios.categoria', 'beneficiarios.provincia']);

        if (!$usuario->can('proyectos.ver_todas_provincias')) {
            $provinciaIds = $usuario->provincias()->pluck('provincias.id');
            $query->whereHas('provincias', function ($q) use ($provinciaIds) {
                $q->whereIn('provincias.id', $provinciaIds);
            });
        }

        if (!empty($filtros['search'])) {
            $s = $filtros['search'];
            $query->where(function ($q) use ($s) {
                $q->where('nombre', 'LIKE', '%' . $s . '%')
                  ->orWhere('codigo', 'LIKE', '%' . $s . '%');
            });
        }

        if (!empty($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }

        if (!empty($filtros['provincia_id'])) {
            $query->whereHas('provincias', function ($q) use ($filtros) {
                $q->where('provincias.id', $filtros['provincia_id']);
            });
        }

        if (!empty($filtros['actor_id'])) {
            $query->whereHas('actores', function ($q) use ($filtros) {
                $q->where('actores_cooperacion.id', $filtros['actor_id']);
            });
        }

        if (!empty($filtros['sector_tematico'])) {
            $query->where('sector_tematico', 'like', '%' . $filtros['sector_tematico'] . '%');
        }

        if (!empty($filtros['flujo_direccion'])) {
            $query->where('flujo_direccion', $filtros['flujo_direccion']);
        }

        $query->orderByDesc('created_at');

        return $query->paginate($perPage);
    }

    public function obtener(string $id, $usuario): Proyecto
    {
        $query = Proyecto::with(['provincias', 'ubicaciones.canton', 'ods', 'actores', 'hitos', 'documentos', 'beneficiarios.categoria', 'beneficiarios.provincia']);

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

            // Extraer actor_ids antes de crear el proyecto (no es columna de la tabla)
            $actorIds = $datos['actor_ids'] ?? [];
            unset($datos['actor_ids']);

            $proyecto = Proyecto::create($datos);

            // Sincronizar actores cooperantes (relación muchos-a-muchos)
            if (!empty($actorIds)) {
                $proyecto->actores()->sync($actorIds);
            }

            if (isset($datos['provincias'])) {
                $syncData = [];
                foreach ($datos['provincias'] as $provincia) {
                    $syncData[$provincia['id']] = [
                        'rol'               => $provincia['rol'] ?? 'Beneficiaria',
                        'porcentaje_avance' => $provincia['porcentaje_avance'] ?? 0,
                    ];
                }
                $proyecto->provincias()->sync($syncData);
            }

            // Sincronizar beneficiarios por categoría y provincia
            if (isset($datos['beneficiarios'])) {
                $proyecto->beneficiarios()->delete();
                foreach ($datos['beneficiarios'] as $b) {
                    if (!empty($b['categoria_id']) && !empty($b['provincia_id'])) {
                        $proyecto->beneficiarios()->create([
                            'provincia_id'              => $b['provincia_id'],
                            'categoria_beneficiario_id' => $b['categoria_id'],
                            'cantidad_directos'         => $b['cantidad_directos'] ?? null,
                            'cantidad_indirectos'       => $b['cantidad_indirectos'] ?? null,
                            'observaciones'             => $b['observaciones'] ?? null,
                        ]);
                    }
                }
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
            // Extraer actor_ids antes de actualizar (no es columna de la tabla)
            $actorIds = $datos['actor_ids'] ?? null;
            unset($datos['actor_ids']);

            $proyecto->update($datos);

            // Sincronizar actores si se enviaron
            if ($actorIds !== null) {
                $proyecto->actores()->sync($actorIds);
            }

            if (isset($datos['provincias'])) {
                $syncData = [];
                foreach ($datos['provincias'] as $provincia) {
                    $syncData[$provincia['id']] = [
                        'rol'               => $provincia['rol'] ?? 'Beneficiaria',
                        'porcentaje_avance' => $provincia['porcentaje_avance'] ?? 0,
                    ];
                }
                $proyecto->provincias()->sync($syncData);
            }

            // Sincronizar beneficiarios por categoría y provincia
            if (isset($datos['beneficiarios'])) {
                $proyecto->beneficiarios()->delete();
                foreach ($datos['beneficiarios'] as $b) {
                    if (!empty($b['categoria_id']) && !empty($b['provincia_id'])) {
                        $proyecto->beneficiarios()->create([
                            'provincia_id'              => $b['provincia_id'],
                            'categoria_beneficiario_id' => $b['categoria_id'],
                            'cantidad_directos'         => $b['cantidad_directos'] ?? null,
                            'cantidad_indirectos'       => $b['cantidad_indirectos'] ?? null,
                            'observaciones'             => $b['observaciones'] ?? null,
                        ]);
                    }
                }
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
