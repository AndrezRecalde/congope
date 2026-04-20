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
                    })->values()->toArray();

                $opcionesProvincias = Provincia::query()
                    ->orderBy('nombre')
                    ->get(['id', 'nombre'])
                    ->map(fn($p) => [
                        'value' => $p->id,
                        'label' => $p->nombre,
                    ])->values()->toArray();

                $opcionesCantones = Canton::query()
                    ->orderBy('nombre')
                    ->with('provincia:id,nombre')
                    ->get(['id', 'nombre', 'provincia_id'])
                    ->map(fn($c) => [
                        'value' => $c->id,
                        'label' => $c->nombre,
                        'provincia_id' => $c->provincia_id,
                        'provincia' => $c->provincia?->nombre,
                    ])->values()->toArray();

                $opcionesActores = ActorCooperacion::query()
                    ->where('estado', 'Activo')
                    ->orderBy('nombre')
                    ->get(['id', 'nombre', 'tipo'])
                    ->map(fn($a) => [
                        'value' => $a->id,
                        'label' => $a->nombre,
                        'tipo' => $a->tipo,
                    ])->values()->toArray();

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
            'search' => ['nullable', 'string', 'max:150'],
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
                'actores:id,nombre,tipo,pais_origen',
                'provincias:id,nombre',
                'ods:id,numero,nombre,color_hex',
                'ubicaciones' => function ($q) use ($provinciaId, $cantonId) {
                    $q->select('id', 'proyecto_id', 'canton_id', 'nombre', DB::raw('ST_X(ubicacion::geometry) as lng'), DB::raw('ST_Y(ubicacion::geometry) as lat'));
                    
                    if ($cantonId) {
                        $q->where('canton_id', $cantonId);
                    } elseif ($provinciaId) {
                        $q->whereHas('canton', fn($c) => $c->where('provincia_id', $provinciaId));
                    }
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
            $queryProyectos->whereHas('actores', fn($q) => $q->where('actores_cooperacion.id', $actorId));
        }

        // ── Filtro de búsqueda por texto ─────────────
        // Busca en nombre, código y sector temático
        // del proyecto. Case-insensitive con LIKE.
        // Solo se aplica si search tiene contenido
        // (no vacío y no solo espacios).
        if (!empty(trim($request->search ?? ''))) {
            $search = '%' . trim($request->search) . '%';
            $queryProyectos->where(function ($q) use ($search) {
                $q->where('nombre', 'like', $search)
                  ->orWhere('codigo', 'like', $search)
                  ->orWhere('sector_tematico', 'like', $search);
            });
        }

        $proyectos = $queryProyectos
            ->select([
                'id', 'codigo', 'nombre', 'descripcion', 'estado', 'monto_total',
                'moneda', 'sector_tematico', 'flujo_direccion', 'modalidad_cooperacion',
                'fecha_inicio', 'fecha_fin_planificada', 'fecha_fin_real',
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
            $queryEmblematicos->whereHas('proyecto', fn($q) => $q->whereHas('actores', fn($aq) => $aq->where('actores_cooperacion.id', $actorId)));
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
            $queryPracticas->whereHas('proyecto', fn($q) => $q->whereHas('actores', fn($aq) => $aq->where('actores_cooperacion.id', $actorId)));
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
            'search_aplicado' => trim($request->search ?? '') ?: null,
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
                    ->whereIn('proyectos.estado', $estados)
                    ->sum('monto_total');

                $porOds = Ods::query()
                    ->whereHas('proyectos', fn($q) => $q->whereIn('proyectos.estado', $estados))
                    ->withCount([
                        'proyectos as total' => fn($q) => $q->whereIn('proyectos.estado', $estados),
                    ])
                    ->orderByDesc('total')
                    ->get()
                    ->map(fn($o) => [
                        'id' => $o->id,
                        'numero' => $o->numero,
                        'nombre' => $o->nombre,
                        'color_hex' => $o->color_hex,
                        'total_proyectos' => $o->total,
                    ])->values()->toArray();

                $porTipoActor = Proyecto::query()
                    ->whereIn('proyectos.estado', $estados)
                    ->join('proyecto_actor', 'proyectos.id', '=', 'proyecto_actor.proyecto_id')
                    ->join('actores_cooperacion', 'proyecto_actor.actor_id', '=', 'actores_cooperacion.id')
                    ->selectRaw('actores_cooperacion.tipo, COUNT(DISTINCT proyectos.id) as total')
                    ->groupBy('actores_cooperacion.tipo')
                    ->orderByDesc('total')
                    ->get()
                    ->map(fn($r) => [
                        'tipo'  => $r->tipo,
                        'total' => $r->total,
                    ])->values()->toArray();

                $porFlujo = Proyecto::query()
                    ->whereIn('proyectos.estado', $estados)
                    ->whereNotNull('flujo_direccion')
                    ->selectRaw('flujo_direccion, COUNT(*) as total')
                    ->groupBy('flujo_direccion')
                    ->orderByDesc('total')
                    ->get()
                    ->map(fn($r) => [
                        'flujo' => $r->flujo_direccion,
                        'total' => $r->total,
                    ])->values()->toArray();

                return [
                    'kpis' => [
                        'total_proyectos' => Proyecto::whereIn('proyectos.estado', $estados)->count(),
                        'total_actores' => ActorCooperacion::where('estado', 'Activo')->count(),
                        'total_provincias' => Provincia::whereHas('proyectos', fn($q) => $q->whereIn('proyectos.estado', $estados))->count(),
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
                'actores:id,nombre,tipo,pais_origen,sitio_web',
                'provincias:id,nombre',
                'ods:id,numero,nombre,color_hex',
                'ubicaciones' => function ($q) {
                    $q->select('id', 'proyecto_id', 'nombre', DB::raw('ST_X(ubicacion::geometry) as lng'), DB::raw('ST_Y(ubicacion::geometry) as lat'));
                },
                'hitos' => fn($q) => $q->select('id', 'proyecto_id', 'titulo', 'fecha_limite', 'completado', 'completado_en')->orderBy('fecha_limite'),
                'beneficiarios.categoria:id,nombre,grupo',
                'beneficiarios.provincia:id,nombre',
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
                'actores' => $proyecto->actores->map(fn($a) => [
                    'id'         => $a->id,
                    'nombre'     => $a->nombre,
                    'tipo'       => $a->tipo,
                    'pais_origen'=> $a->pais_origen,
                    'sitio_web'  => $a->sitio_web,
                ]),
                // Retrocompatibilidad: primer actor
                'actor' => $proyecto->actores->first() ? [
                    'id'         => $proyecto->actores->first()->id,
                    'nombre'     => $proyecto->actores->first()->nombre,
                    'tipo'       => $proyecto->actores->first()->tipo,
                    'pais_origen'=> $proyecto->actores->first()->pais_origen,
                    'sitio_web'  => $proyecto->actores->first()->sitio_web,
                ] : null,
                'provincias' => $proyecto->provincias->map(fn($p) => [
                    'id'                => $p->id,
                    'nombre'            => $p->nombre,
                    'rol'               => $p->pivot->rol,
                    'porcentaje_avance' => $p->pivot->porcentaje_avance,
                ]),
                'ods' => $proyecto->ods->map(fn($o) => [
                    'id'        => $o->id,
                    'numero'    => $o->numero,
                    'nombre'    => $o->nombre,
                    'color_hex' => $o->color_hex,
                ]),
                'ubicaciones' => $proyecto->ubicaciones->map(fn($u) => [
                    'id'     => $u->id,
                    'nombre' => $u->nombre,
                    'coordenadas' => [
                        'lat' => (float) $u->lat,
                        'lng' => (float) $u->lng,
                    ],
                ]),
                'avance' => [
                    'hitos_total'       => $hitosTotal,
                    'hitos_completados' => $hitosCompletados,
                    'porcentaje'        => $hitosTotal > 0 ? round(($hitosCompletados / $hitosTotal) * 100) : null,
                ],
                // Beneficiarios agrupados por provincia
                'beneficiarios_por_provincia' => $proyecto->provincias->map(function ($prov) use ($proyecto) {
                    $items = $proyecto->beneficiarios
                        ->where('provincia_id', $prov->id)
                        ->values();
                    return [
                        'provincia_id'   => $prov->id,
                        'provincia_nombre' => $prov->nombre,
                        'total_directos'   => $items->sum('cantidad_directos'),
                        'total_indirectos' => $items->sum('cantidad_indirectos'),
                        'categorias'       => $items->map(fn($b) => [
                            'categoria_nombre'   => $b->categoria?->nombre,
                            'categoria_grupo'    => $b->categoria?->grupo,
                            'cantidad_directos'  => $b->cantidad_directos,
                            'cantidad_indirectos'=> $b->cantidad_indirectos,
                            'observaciones'      => $b->observaciones,
                        ])->values(),
                    ];
                })->filter(fn($g) => count($g['categorias']) > 0)->values(),
                // Totales globales
                'beneficiarios' => [
                    'directos'   => $proyecto->beneficiarios->sum('cantidad_directos'),
                    'indirectos' => $proyecto->beneficiarios->sum('cantidad_indirectos'),
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
                'actores' => $proyecto->actores->map(fn($a) => [
                    'id'         => $a->id,
                    'nombre'     => $a->nombre,
                    'tipo'       => $a->tipo,
                    'pais_origen'=> $a->pais_origen,
                ]),
                // Retrocompatibilidad: primer actor
                'actor' => $proyecto->actores->first() ? [
                    'id'         => $proyecto->actores->first()->id,
                    'nombre'     => $proyecto->actores->first()->nombre,
                    'tipo'       => $proyecto->actores->first()->tipo,
                    'pais_origen'=> $proyecto->actores->first()->pais_origen,
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

    /**
     * GET /api/v1/publico/emblematicos
     *
     * Proyectos emblemáticos públicos para el portal.
     * Solo los marcados con es_publico = true.
     *
     * Respuesta completa con reconocimientos y proyecto
     * anidado para las cards del portal público.
     *
     * Query params opcionales:
     *   page     → número de página (default: 1)
     *   per_page → resultados por página (default: 12)
     *
     * Cacheado 15 minutos. Se invalida cuando cambia
     * un ProyectoEmblematico (via ProyectoEmblematicoObserver
     * si existe, o manualmente).
     */
    public function emblematicos(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 12);
        $page    = $request->integer('page', 1);

        $cacheKey = "portal.emblematicos.page_{$page}.per_{$perPage}";

        $data = Cache::remember(
            $cacheKey,
            now()->addMinutes(15),
            function () use ($perPage) {
                return ProyectoEmblematico::query()
                    ->with([
                        'provincia:id,nombre',
                        'proyecto:id,codigo,nombre,estado,'
                            . 'sector_tematico,monto_total,'
                            . 'monto_formateado,moneda',
                        'reconocimientos:id,emblematico_id,'
                            . 'titulo,organismo_otorgante,'
                            . 'ambito,anio',
                    ])
                    ->where('es_publico', true)
                    ->whereNull('deleted_at')
                    ->orderByDesc('created_at')
                    ->paginate($perPage);
            }
        );

        // Si el resultado viene del caché como objeto
        // paginado, mapear. Si no, paginar de nuevo.
        $emblematicos = $data instanceof \Illuminate\Pagination\LengthAwarePaginator
            ? $data
            : ProyectoEmblematico::query()
                ->with([
                    'provincia:id,nombre',
                    'proyecto:id,codigo,nombre,estado,'
                        . 'sector_tematico,monto_total,'
                        . 'monto_formateado,moneda',
                    'reconocimientos:id,emblematico_id,'
                        . 'titulo,organismo_otorgante,'
                        . 'ambito,anio',
                ])
                ->where('es_publico', true)
                ->whereNull('deleted_at')
                ->orderByDesc('created_at')
                ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Proyectos emblemáticos públicos',
            'data'    => $emblematicos->getCollection()->map(
                fn($e) => [
                    'id'                  => $e->id,
                    'titulo'              => $e->titulo,
                    'descripcion_impacto' => $e->descripcion_impacto,
                    'periodo'             => $e->periodo,

                    'provincia' => $e->provincia ? [
                        'id'     => $e->provincia->id,
                        'nombre' => $e->provincia->nombre,
                    ] : null,

                    'proyecto' => $e->proyecto ? [
                        'id'               => $e->proyecto->id,
                        'codigo'           => $e->proyecto->codigo,
                        'nombre'           => $e->proyecto->nombre,
                        'estado'           => $e->proyecto->estado,
                        'sector_tematico'  =>
                            $e->proyecto->sector_tematico,
                        'monto_formateado' =>
                            $e->proyecto->monto_formateado
                            ?? number_format(
                                (float) $e->proyecto->monto_total,
                                2, '.', ','
                            ) . ' ' . ($e->proyecto->moneda ?? 'USD'),
                    ] : null,

                    'reconocimientos' => $e->reconocimientos
                        ->map(fn($r) => [
                            'id'                  => $r->id,
                            'titulo'              => $r->titulo,
                            'organismo_otorgante' =>
                                $r->organismo_otorgante,
                            'ambito' => $r->ambito,
                            'anio'   => $r->anio,
                        ]),

                    'reconocimientos_count' =>
                        $e->reconocimientos->count(),

                    'created_at' =>
                        $e->created_at?->format('d/m/Y'),
                ]
            ),
            'meta' => [
                'current_page' => $emblematicos->currentPage(),
                'last_page'    => $emblematicos->lastPage(),
                'per_page'     => $emblematicos->perPage(),
                'total'        => $emblematicos->total(),
            ],
        ]);
    }

    /**
     * GET /api/v1/publico/buenas-practicas
     *
     * Buenas prácticas destacadas para el portal público.
     * Solo las marcadas con es_destacada = true.
     * Ordenadas por calificacion_promedio DESC para
     * mostrar las mejor valoradas primero.
     *
     * No expone datos sensibles de contacto ni de
     * gestión interna.
     *
     * Query params opcionales:
     *   page     → número de página (default: 1)
     *   per_page → resultados por página (default: 9)
     *
     * Se utiliza per_page: 9 para grids de 3 columnas.
     */
    public function buenasPracticas(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 9);

        $practicas = BuenaPractica::query()
            ->with([
                'provincia:id,nombre',
            ])
            ->where('es_destacada', true)
            ->whereNull('deleted_at')
            ->orderByDesc('calificacion_promedio')
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Buenas prácticas destacadas',
            'data'    => $practicas->getCollection()->map(
                fn($p) => [
                    'id'    => $p->id,
                    'titulo'=> $p->titulo,

                    'descripcion_problema' =>
                        $p->descripcion_problema,

                    'resultados' => $p->resultados,

                    'replicabilidad' => $p->replicabilidad,

                    'calificacion_promedio' =>
                        $p->calificacion_promedio,

                    'provincia' => $p->provincia ? [
                        'id'     => $p->provincia->id,
                        'nombre' => $p->provincia->nombre,
                    ] : null,

                    'created_at' =>
                        $p->created_at?->format('d/m/Y'),
                ]
            ),
            'meta' => [
                'current_page' => $practicas->currentPage(),
                'last_page'    => $practicas->lastPage(),
                'per_page'     => $practicas->perPage(),
                'total'        => $practicas->total(),
            ],
        ]);
    }
}
