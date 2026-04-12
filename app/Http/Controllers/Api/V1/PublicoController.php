<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use App\Models\ActorCooperacion;
use App\Models\BuenaPractica;
use App\Models\Canton;
use App\Models\Ods;
use App\Models\Proyecto;
use App\Models\ProyectoEmblematico;
use App\Models\Provincia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PublicoController extends ApiController
{
    /**
     * GET /api/v1/publico/mapa/catalogos
     */
    public function mapaCatalogos(): JsonResponse
    {
        $data = Cache::remember(
            'portal.mapa.catalogos',
            now()->addMinutes(30),
            function () {

                $provincias = Provincia::query()
                    ->select('id', 'nombre', 'codigo')
                    ->get()
                    ->map(function (Provincia $prov) {

                        $proyectos = $prov->proyectos()
                            ->whereIn('estado', ['En gestión', 'En ejecución', 'Finalizado'])
                            ->get(['proyectos.id', 'proyectos.estado', 'proyectos.monto_total']);

                        $total = $proyectos->count();
                        $montoTotal = (float) $proyectos->sum('monto_total');

                        return [
                            'id' => $prov->id,
                            'nombre' => $prov->nombre,
                            'codigo' => $prov->codigo,
                            'proyectos_count' => $total,
                            'monto_total' => $montoTotal,
                            'monto_formateado' => '$' . number_format($montoTotal, 0, '.', ',') . ' USD',
                            'tooltip' => [
                                'total' => $total,
                                'en_gestion' => $proyectos->where('estado', 'En gestión')->count(),
                                'en_ejecucion' => $proyectos->where('estado', 'En ejecución')->count(),
                                'finalizado' => $proyectos->where('estado', 'Finalizado')->count(),
                            ],
                        ];
                    });

                $opcionesProvincias = Provincia::query()
                    ->whereHas('proyectos', function ($q) {
                        $q->whereIn('estado', ['En gestión', 'En ejecución', 'Finalizado']);
                    })
                    ->orderBy('nombre')
                    ->get(['id', 'nombre'])
                    ->map(fn($p) => [
                        'value' => $p->id,
                        'label' => $p->nombre,
                    ]);

                $opcionesCantones = Canton::query()
                    ->whereHas('proyectos')
                    ->orderBy('nombre')
                    ->with('provincia:id,nombre')
                    ->get(['id', 'nombre', 'provincia_id'])
                    ->map(fn($c) => [
                        'value' => $c->id,
                        'label' => $c->nombre,
                        'provincia_id' => $c->provincia_id,
                        'provincia' => $c->provincia?->nombre,
                    ]);

                $opcionesActores = ActorCooperacion::query()
                    ->where('estado', 'Activo')
                    ->whereHas('proyectos', function ($q) {
                        $q->whereIn('estado', ['En gestión', 'En ejecución', 'Finalizado']);
                    })
                    ->orderBy('nombre')
                    ->get(['id', 'nombre', 'tipo'])
                    ->map(fn($a) => [
                        'value' => $a->id,
                        'label' => $a->nombre,
                        'tipo' => $a->tipo,
                    ]);

                return [
                    'provincias' => $provincias,
                    'opciones_filtro' => [
                        'provincias' => $opcionesProvincias,
                        'cantones' => $opcionesCantones,
                        'actores' => $opcionesActores,
                    ],
                ];
            }
        );

        return response()->json([
            'success' => true,
            'message' => 'Catálogos del mapa obtenidos',
            'data' => $data,
        ]);
    }

    /**
     * GET /api/v1/publico/mapa/filtrar
     */
    public function mapaFiltrar(Request $request): JsonResponse
    {
        $request->validate([
            'provincia_id' => ['nullable', 'uuid', 'exists:provincias,id'],
            'canton_id' => ['nullable', 'uuid', 'exists:cantones,id'],
            'actor_id' => ['nullable', 'uuid', 'exists:actores_cooperacion,id'],
        ]);

        $provinciaId = $request->provincia_id;
        $cantonId = $request->canton_id;
        $actorId = $request->actor_id;

        $provinciaInferida = null;
        if ($cantonId && !$provinciaId) {
            $canton = Canton::find($cantonId, ['id', 'provincia_id']);
            if ($canton) {
                $provinciaInferida = $canton->provincia_id;
            }
        }

        $queryProyectos = Proyecto::query()
            ->with([
                'actor:id,nombre,tipo,pais_origen',
                'provincias:id,nombre',
                'ods:id,numero,nombre,color_hex',
                'ubicaciones' => function ($q) {
                    $q->select('id', 'proyecto_id', 'nombre', DB::raw('ST_X(ubicacion::geometry) as lng'), DB::raw('ST_Y(ubicacion::geometry) as lat'));
                },
            ])
            ->whereIn('estado', ['En gestión', 'En ejecución', 'Finalizado']);

        if ($provinciaId) {
            $queryProyectos->whereHas('provincias', fn($q) => $q->where('provincias.id', $provinciaId));
        }

        if ($cantonId) {
            $queryProyectos->whereHas('cantones', fn($q) => $q->where('cantones.id', $cantonId));
        }

        if ($actorId) {
            $queryProyectos->where('actor_id', $actorId);
        }

        $proyectos = $queryProyectos
            ->select([
                'id', 'codigo', 'nombre', 'descripcion', 'estado', 'monto_total',
                'moneda', 'sector_tematico', 'flujo_direccion', 'modalidad_cooperacion',
                'fecha_inicio', 'fecha_fin_planificada', 'fecha_fin_real', 'actor_id',
            ])
            ->get()
            ->map(fn($p) => head($this->formatearProyecto($p)));

        $queryEmblematicos = ProyectoEmblematico::query()
            ->with([
                'provincia:id,nombre',
                'proyecto:id,codigo,nombre,estado,sector_tematico',
                'reconocimientos:id,emblematico_id,titulo,organismo_otorgante,ambito,anio',
            ])
            ->where('es_publico', true);

        if ($provinciaId || $provinciaInferida) {
            $filtrarPorProvincia = $provinciaId ?? $provinciaInferida;
            $queryEmblematicos->where('provincia_id', $filtrarPorProvincia);
        }

        if ($actorId) {
            $queryEmblematicos->whereHas('proyecto', fn($q) => $q->where('actor_id', $actorId));
        }

        if ($cantonId && !$provinciaId && $provinciaInferida) {
            $queryEmblematicos->where('provincia_id', $provinciaInferida);
        }

        $emblematicos = $queryEmblematicos
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($e) => [
                'id' => $e->id,
                'titulo' => $e->titulo,
                'descripcion_impacto' => $e->descripcion_impacto,
                'periodo' => $e->periodo,
                'provincia' => $e->provincia ? [
                    'id' => $e->provincia->id,
                    'nombre' => $e->provincia->nombre,
                ] : null,
                'proyecto' => $e->proyecto ? [
                    'id' => $e->proyecto->id,
                    'codigo' => $e->proyecto->codigo,
                    'nombre' => $e->proyecto->nombre,
                    'estado' => $e->proyecto->estado,
                    'sector_tematico' => $e->proyecto->sector_tematico,
                    'monto_formateado' => $e->proyecto->monto_formateado,
                ] : null,
                'reconocimientos' => $e->reconocimientos->map(fn($r) => [
                    'id' => $r->id,
                    'titulo' => $r->titulo,
                    'organismo_otorgante' => $r->organismo_otorgante,
                    'ambito' => $r->ambito,
                    'anio' => $r->anio,
                ]),
                'reconocimientos_count' => $e->reconocimientos->count(),
            ]);

        $queryPracticas = BuenaPractica::query()
            ->with(['provincia:id,nombre'])
            ->where('es_destacada', true);

        if ($provinciaId) {
            $queryPracticas->where('provincia_id', $provinciaId);
        }

        if ($cantonId && !$provinciaId && $provinciaInferida) {
            $queryPracticas->where('provincia_id', $provinciaInferida);
        }

        if ($actorId) {
            $queryPracticas->whereHas('proyecto', fn($q) => $q->where('actor_id', $actorId));
        }

        $practicas = $queryPracticas
            ->orderByDesc('calificacion_promedio')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'titulo' => $p->titulo,
                'descripcion_problema' => $p->descripcion_problema,
                'resultados' => $p->resultados,
                'replicabilidad' => $p->replicabilidad,
                'calificacion_promedio' => $p->calificacion_promedio,
                'provincia' => $p->provincia ? [
                    'id' => $p->provincia->id,
                    'nombre' => $p->provincia->nombre,
                ] : null,
                'created_at' => $p->created_at?->format('d/m/Y'),
            ]);

        $resumen = [
            'total_proyectos' => $proyectos->count(),
            'total_emblematicos' => $emblematicos->count(),
            'total_buenas_practicas' => $practicas->count(),
            'provincia_resaltada' => $provinciaId ?? $provinciaInferida,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Resultados del filtro obtenidos',
            'data' => [
                'proyectos' => $proyectos,
                'emblematicos' => $emblematicos,
                'buenas_practicas' => $practicas,
                'resumen' => $resumen,
            ],
            'filtros_aplicados' => array_filter([
                'provincia_id' => $provinciaId,
                'canton_id' => $cantonId,
                'actor_id' => $actorId,
            ]),
        ]);
    }

    /**
     * GET /api/v1/publico/conteos
     */
    public function conteos(): JsonResponse
    {
        $data = Cache::remember(
            'portal.conteos',
            now()->addMinutes(15),
            function () {
                return [
                    'total_proyectos' => Proyecto::query()
                        ->whereIn('estado', ['En gestión', 'En ejecución', 'Finalizado'])
                        ->count(),

                    'total_emblematicos' => ProyectoEmblematico::query()
                        ->where('es_publico', true)
                        ->count(),

                    'total_buenas_practicas' => BuenaPractica::query()
                        ->where('es_destacada', true)
                        ->count(),

                    'total_provincias_activas' => Provincia::query()
                        ->whereHas('proyectos', function ($q) {
                            $q->whereIn('estado', ['En gestión', 'En ejecución', 'Finalizado']);
                        })
                        ->count(),
                ];
            }
        );

        return response()->json([
            'success' => true,
            'message' => 'Conteos obtenidos correctamente',
            'data' => $data,
        ]);
    }

    /**
     * GET /api/v1/publico/estadisticas
     */
    public function estadisticas(): JsonResponse
    {
        $data = Cache::remember(
            'portal.estadisticas',
            now()->addMinutes(30),
            function () {
                $estados = ['En gestión', 'En ejecución', 'Finalizado'];

                $montoTotal = (float) Proyecto::query()
                    ->whereIn('estado', $estados)
                    ->sum('monto_total');

                $porOds = Ods::query()
                    ->withCount([
                        'proyectos as total' => fn($q) => $q->whereIn('estado', $estados),
                    ])
                    ->having('total', '>', 0)
                    ->orderByDesc('total')
                    ->get()
                    ->map(fn($o) => [
                        'id' => $o->id,
                        'numero' => $o->numero,
                        'nombre' => $o->nombre,
                        'color_hex' => $o->color_hex,
                        'total' => $o->total,
                    ]);

                $porTipoActor = Proyecto::query()
                    ->whereIn('estado', $estados)
                    ->join('actores_cooperacion', 'proyectos.actor_id', '=', 'actores_cooperacion.id')
                    ->selectRaw('actores_cooperacion.tipo, COUNT(*) as total')
                    ->groupBy('actores_cooperacion.tipo')
                    ->orderByDesc('total')
                    ->get()
                    ->map(fn($r) => [
                        'tipo' => $r->tipo,
                        'total' => $r->total,
                    ]);

                $porFlujo = Proyecto::query()
                    ->whereIn('estado', $estados)
                    ->whereNotNull('flujo_direccion')
                    ->selectRaw('flujo_direccion, COUNT(*) as total')
                    ->groupBy('flujo_direccion')
                    ->orderByDesc('total')
                    ->get()
                    ->map(fn($r) => [
                        'flujo' => $r->flujo_direccion,
                        'total' => $r->total,
                    ]);

                return [
                    'kpis' => [
                        'total_proyectos' => Proyecto::whereIn('estado', $estados)->count(),
                        'total_actores' => ActorCooperacion::where('estado', 'Activo')->count(),
                        'total_provincias' => Provincia::whereHas('proyectos', fn($q) => $q->whereIn('estado', $estados))->count(),
                        'monto_formateado' => '$' . number_format($montoTotal / 1_000_000, 1) . 'M USD',
                    ],
                    'por_ods' => $porOds,
                    'por_tipo_actor' => $porTipoActor,
                    'por_flujo' => $porFlujo,
                ];
            }
        );

        return response()->json([
            'success' => true,
            'message' => 'Estadísticas obtenidas',
            'data' => $data,
        ]);
    }

    /**
     * GET /api/v1/publico/proyectos/{id}
     */
    public function showProyecto(string $id): JsonResponse
    {
        $proyecto = Proyecto::query()
            ->with([
                'actor:id,nombre,tipo,pais_origen,sitio_web',
                'provincias:id,nombre',
                'ods:id,numero,nombre,color_hex',
                'ubicaciones' => function ($q) {
                    $q->select('id', 'proyecto_id', 'nombre', DB::raw('ST_X(ubicacion::geometry) as lng'), DB::raw('ST_Y(ubicacion::geometry) as lat'));
                },
                'hitos' => fn($q) => $q->select('id', 'proyecto_id', 'titulo', 'fecha_limite', 'completado', 'completado_en')->orderBy('fecha_limite'),
            ])
            ->whereIn('estado', ['En gestión', 'En ejecución', 'Finalizado'])
            ->findOrFail($id);

        $hitosTotal = $proyecto->hitos->count();
        $hitosCompletados = $proyecto->hitos->where('completado', true)->count();

        return response()->json([
            'success' => true,
            'message' => 'Proyecto obtenido correctamente',
            'data' => [
                'id' => $proyecto->id,
                'codigo' => $proyecto->codigo,
                'nombre' => $proyecto->nombre,
                'descripcion' => $proyecto->descripcion,
                'estado' => $proyecto->estado,
                'color_marcador' => $proyecto->color_marcador,
                'sector_tematico' => $proyecto->sector_tematico,
                'flujo_direccion' => $proyecto->flujo_direccion,
                'modalidad_cooperacion' => $proyecto->modalidad_cooperacion,
                'monto_formateado' => $proyecto->monto_formateado,
                'moneda' => $proyecto->moneda,
                'fecha_inicio' => $proyecto->fecha_inicio?->format('Y-m-d'),
                'fecha_fin_planificada' => $proyecto->fecha_fin_planificada?->format('Y-m-d'),
                'fecha_fin_real' => $proyecto->fecha_fin_real?->format('Y-m-d'),
                'actor' => $proyecto->actor ? [
                    'id' => $proyecto->actor->id,
                    'nombre' => $proyecto->actor->nombre,
                    'tipo' => $proyecto->actor->tipo,
                    'pais_origen' => $proyecto->actor->pais_origen,
                    'sitio_web' => $proyecto->actor->sitio_web,
                ] : null,
                'provincias' => $proyecto->provincias->map(fn($p) => [
                    'id' => $p->id,
                    'nombre' => $p->nombre,
                    'rol' => $p->pivot->rol,
                    'porcentaje_avance' => $p->pivot->porcentaje_avance,
                    'beneficiarios_directos' => $p->pivot->beneficiarios_directos,
                    'beneficiarios_indirectos' => $p->pivot->beneficiarios_indirectos,
                ]),
                'ods' => $proyecto->ods->map(fn($o) => [
                    'id' => $o->id,
                    'numero' => $o->numero,
                    'nombre' => $o->nombre,
                    'color_hex' => $o->color_hex,
                ]),
                'ubicaciones' => $proyecto->ubicaciones->map(fn($u) => [
                    'id' => $u->id,
                    'nombre' => $u->nombre,
                    'coordenadas' => [
                        'lat' => (float) $u->lat,
                        'lng' => (float) $u->lng,
                    ],
                ]),
                'avance' => [
                    'hitos_total' => $hitosTotal,
                    'hitos_completados' => $hitosCompletados,
                    'porcentaje' => $hitosTotal > 0 ? round(($hitosCompletados / $hitosTotal) * 100) : null,
                ],
                'beneficiarios' => [
                    'directos' => (int) $proyecto->provincias->sum('pivot.beneficiarios_directos'),
                    'indirectos' => (int) $proyecto->provincias->sum('pivot.beneficiarios_indirectos'),
                ],
            ],
        ]);
    }

    /**
     * Helper privado para formatear proyecto en index de mapa
     */
    private function formatearProyecto(Proyecto $proyecto): array
    {
        return [
            [
                'id' => $proyecto->id,
                'codigo' => $proyecto->codigo,
                'nombre' => $proyecto->nombre,
                'estado' => $proyecto->estado,
                'color_marcador' => $proyecto->color_marcador,
                'monto_formateado' => $proyecto->monto_formateado,
                'sector_tematico' => $proyecto->sector_tematico,
                'flujo_direccion' => $proyecto->flujo_direccion,
                'fecha_inicio' => $proyecto->fecha_inicio?->format('Y-m-d'),
                'fecha_fin_planificada' => $proyecto->fecha_fin_planificada?->format('Y-m-d'),
                'actor' => $proyecto->actor ? [
                    'id' => $proyecto->actor->id,
                    'nombre' => $proyecto->actor->nombre,
                    'tipo' => $proyecto->actor->tipo,
                    'pais_origen' => $proyecto->actor->pais_origen,
                ] : null,
                'provincias' => $proyecto->provincias->map(fn($p) => [
                    'id' => $p->id,
                    'nombre' => $p->nombre,
                ]),
                'ods' => $proyecto->ods->map(fn($o) => [
                    'id' => $o->id,
                    'numero' => $o->numero,
                    'nombre' => $o->nombre,
                    'color_hex' => $o->color_hex,
                ]),
                'ubicaciones' => $proyecto->ubicaciones->map(fn($u) => [
                    'id' => $u->id,
                    'nombre' => $u->nombre,
                    'coordenadas' => [
                        'lat' => (float) $u->lat,
                        'lng' => (float) $u->lng,
                    ],
                ]),
            ]
        ];
    }
}
