<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Proyecto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RedCooperacionController extends Controller
{
    /**
     * GET /api/v1/analisis/red-cooperacion
     *
     * Construye el grafo de red de cooperación.
     * Nodos: actores y provincias.
     * Aristas: proyectos que los conectan.
     *
     * Query params opcionales:
     *   ?estado=En ejecución    ← filtrar proyectos por estado
     *   ?tipo_actor=Multilateral
     *   ?min_proyectos=2        ← mínimo de proyectos para incluir una arista
     *   ?solo_mi_provincia=true ← (default: true) filtro estricto de aristas
     *                             Solo relevante para usuarios con provincias
     *                             asignadas y sin analisis.ver_global.
     *
     * Requiere: auth:sanctum + permiso analisis.ver
     */
    public function index(
        Request $request
    ): JsonResponse {

        // Verificar permiso de acceso al módulo
        if (!$request->user()->can('analisis.ver')) {
            abort(403,
                'No tienes permiso para acceder ' .
                'al análisis de red de cooperación.'
            );
        }

        $estado       = $request->estado;
        $tipoActor    = $request->tipo_actor;
        $minProyectos = (int) ($request->min_proyectos ?? 1);

        // ── Nuevo parámetro ───────────────────────────
        // Solo relevante para usuarios con provincias
        // asignadas y sin vista global.
        // Default: true (filtro estricto por defecto)
        $soloMiProvincia = filter_var(
            $request->input('solo_mi_provincia', 'true'),
            FILTER_VALIDATE_BOOLEAN
        );

        $usuario = $request->user();
        $usuario->load('provincias');

        // Determinar si el usuario tiene vista global
        $tieneVistaGlobal = $usuario->can('analisis.ver_global');

        // El filtro estricto se activa SOLO cuando:
        //   • el usuario NO tiene ver_global, Y
        //   • tiene al menos una provincia asignada, Y
        //   • explícitamente pidió solo_mi_provincia=true
        // Para super_admin y usuarios sin provincias el
        // parámetro se ignora (siempre ven todo).
        $aplicarFiltroEstricto =
            !$tieneVistaGlobal &&
            $usuario->provincias->isNotEmpty() &&
            $soloMiProvincia;

        // Clave de caché por usuario + todos los filtros
        // (incluye el modo de filtro estricto para que
        //  ambas versiones se cacheen de forma separada)
        $cacheKey =
            'analisis.red_cooperacion.' .
            $usuario->id . '.' .
            md5(json_encode([
                'estado'            => $estado,
                'tipo_actor'        => $tipoActor,
                'min_proyectos'     => $minProyectos,
                'solo_mi_provincia' => $aplicarFiltroEstricto,
            ]));

        $data = Cache::remember(
            $cacheKey,
            now()->addMinutes(15),
            function () use (
                $estado, $tipoActor, $minProyectos,
                $usuario, $tieneVistaGlobal,
                $aplicarFiltroEstricto
            ) {
                return $this->construirGrafo(
                    $estado,
                    $tipoActor,
                    $minProyectos,
                    $usuario,
                    $tieneVistaGlobal,
                    $aplicarFiltroEstricto
                );
            }
        );

        return response()->json([
            'success' => true,
            'message' => 'Red de cooperación obtenida',
            'data'    => array_merge($data, [
                'meta' => array_merge(
                    $data['meta'],
                    [
                        'vista_global' =>
                            $tieneVistaGlobal,
                        'provincias_usuario' =>
                            $usuario->provincias
                                ->pluck('nombre')
                                ->toArray(),
                        // Indica al frontend qué modo
                        // está activo para el toggle
                        'solo_mi_provincia' =>
                            $aplicarFiltroEstricto,
                    ]
                ),
            ]),
        ]);
    }

    /**
     * Construye la estructura del grafo desde la BD
     * aplicando filtro geográfico y filtro estricto
     * de aristas según los permisos del usuario.
     *
     * @param  string|null  $estadoFiltro
     * @param  string|null  $tipoActorFiltro
     * @param  int          $minProyectos
     * @param  mixed        $usuario              Usuario autenticado con 'provincias' cargadas
     * @param  bool         $tieneVistaGlobal     true si tiene analisis.ver_global
     * @param  bool         $aplicarFiltroEstricto true → omite aristas fuera de las provincias del usuario
     */
    private function construirGrafo(
        ?string $estadoFiltro,
        ?string $tipoActorFiltro,
        int     $minProyectos,
        $usuario,
        bool    $tieneVistaGlobal,
        bool    $aplicarFiltroEstricto
    ): array {

        // ── 1. Query base de proyectos ────────────────
        $query = Proyecto::query()
            ->with([
                'actores:id,nombre,tipo,pais_origen,estado',
                'provincias:id,nombre,codigo,capital',
            ])
            ->whereNull('deleted_at')
            ->where(function ($q) {
                // Solo proyectos con al menos un actor
                // y al menos una provincia
                $q->has('actores')
                  ->has('provincias');
            });

        // ── Filtro geográfico de proyectos ────────────
        // Restringe qué PROYECTOS se cargan según las
        // provincias del usuario. Independiente del toggle:
        // un admin_provincial siempre carga solo proyectos
        // donde participa su provincia.
        if (!$tieneVistaGlobal &&
            $usuario->provincias->isNotEmpty()) {
            $query->whereHas(
                'provincias',
                fn($q) => $q->whereIn(
                    'provincias.id',
                    $usuario->provincias->pluck('id')
                )
            );
        }
        // Si tiene ver_global O no tiene provincias
        // asignadas → no filtrar (ve todos los proyectos)

        // ── Filtros de la request ─────────────────────
        if ($estadoFiltro) {
            $query->where('estado', $estadoFiltro);
        }

        if ($tipoActorFiltro) {
            $query->whereHas(
                'actores',
                fn($q) => $q->where('tipo', $tipoActorFiltro)
            );
        }

        $proyectos = $query->get([
            'id', 'nombre', 'monto_total',
            'estado', 'flujo_direccion',
        ]);

        // ── 2. Construir aristas ──────────────────────
        // Map: "actor_id:provincia_id" → datos
        $aristasMap = [];

        // Set de IDs de provincias del usuario para
        // lookup O(1) en el filtro estricto
        $provinciasUsuarioIds =
            $usuario->provincias->pluck('id')->toArray();

        foreach ($proyectos as $proyecto) {
            $monto = (float) $proyecto->monto_total;

            foreach ($proyecto->actores as $actor) {
                foreach ($proyecto->provincias as $provincia) {

                    // ── FILTRO ESTRICTO DE ARISTAS ────
                    // Cuando está activo, solo se incluyen
                    // aristas hacia las provincias del usuario.
                    // Ejemplo: admin_provincial de Esmeraldas
                    // ve el proyecto Esmeraldas+Guayas →
                    // el nodo Guayas NO aparece en el grafo.
                    if ($aplicarFiltroEstricto &&
                        !in_array(
                            $provincia->id,
                            $provinciasUsuarioIds
                        )
                    ) {
                        continue; // saltar esta provincia
                    }
                    // ─────────────────────────────────

                    $key = "{$actor->id}:{$provincia->id}";

                    if (!isset($aristasMap[$key])) {
                        $aristasMap[$key] = [
                            'actor_id'          => $actor->id,
                            'actor_nombre'      => $actor->nombre,
                            'actor_tipo'        => $actor->tipo,
                            'actor_pais'        => $actor->pais_origen,
                            'actor_estado'      => $actor->estado,
                            'provincia_id'      => $provincia->id,
                            'provincia_nombre'  => $provincia->nombre,
                            'provincia_codigo'  => $provincia->codigo,
                            'provincia_capital' => $provincia->capital ?? '',
                            'peso'              => 0,
                            'proyectos'         => 0,
                            'nombres_proyectos' => [],
                        ];
                    }

                    $aristasMap[$key]['peso']      += $monto;
                    $aristasMap[$key]['proyectos'] += 1;

                    // Guardar nombre del proyecto
                    // (máximo 3 para el tooltip)
                    if (count($aristasMap[$key]['nombres_proyectos']) < 3) {
                        $aristasMap[$key]['nombres_proyectos'][] =
                            $proyecto->nombre;
                    }
                }
            }
        }

        // Filtrar por mínimo de proyectos
        $aristasMap = array_filter(
            $aristasMap,
            fn($a) => $a['proyectos'] >= $minProyectos
        );

        // ── 3. Construir nodos únicos ─────────────────
        // Los nodos se derivan de las aristas filtradas,
        // por lo que reflejan automáticamente el modo activo
        $actoresMap    = [];
        $provinciasMap = [];

        foreach ($aristasMap as $arista) {
            $aid = $arista['actor_id'];
            $pid = $arista['provincia_id'];

            if (!isset($actoresMap[$aid])) {
                $actoresMap[$aid] = [
                    'conexiones'  => 0,
                    'monto'       => 0,
                    'nombre'      => $arista['actor_nombre'],
                    'tipo'        => $arista['actor_tipo'],
                    'pais_origen' => $arista['actor_pais'],
                    'estado'      => $arista['actor_estado'],
                ];
            }
            $actoresMap[$aid]['conexiones']++;
            $actoresMap[$aid]['monto'] += $arista['peso'];

            if (!isset($provinciasMap[$pid])) {
                $provinciasMap[$pid] = [
                    'conexiones' => 0,
                    'monto'      => 0,
                    'nombre'     => $arista['provincia_nombre'],
                    'codigo'     => $arista['provincia_codigo'],
                    'capital'    => $arista['provincia_capital'],
                ];
            }
            $provinciasMap[$pid]['conexiones']++;
            $provinciasMap[$pid]['monto'] += $arista['peso'];
        }

        // ── 4. Formatear nodos ────────────────────────
        $nodos = [];

        foreach ($actoresMap as $id => $actor) {
            $nodos[] = [
                'id'      => "actor:{$id}",
                'tipo'    => 'actor',
                'nombre'  => $actor['nombre'],
                'subtipo' => $actor['tipo'] ?? 'Sin tipo',
                'valor'   => $actor['conexiones'],
                'monto'   => $actor['monto'],
                'extra'   => [
                    'pais_origen' => $actor['pais_origen'] ?? '',
                    'estado'      => $actor['estado']      ?? '',
                ],
            ];
        }

        foreach ($provinciasMap as $id => $prov) {
            $nodos[] = [
                'id'      => "prov:{$id}",
                'tipo'    => 'provincia',
                'nombre'  => $prov['nombre'],
                'subtipo' => 'Provincia',
                'valor'   => $prov['conexiones'],
                'monto'   => $prov['monto'],
                'extra'   => [
                    'codigo'  => $prov['codigo']  ?? '',
                    'capital' => $prov['capital'] ?? '',
                ],
            ];
        }

        // ── 5. Formatear aristas ──────────────────────
        $aristas = [];

        foreach ($aristasMap as $arista) {
            $aid = $arista['actor_id'];
            $pid = $arista['provincia_id'];

            $aristas[] = [
                'id'               => "actor:{$aid}:prov:{$pid}",
                'source'           => "actor:{$aid}",
                'target'           => "prov:{$pid}",
                'peso'             => $arista['peso'],
                'proyectos'        => $arista['proyectos'],
                'nombres_proyectos'=> $arista['nombres_proyectos'],
            ];
        }

        // ── 6. Calcular metadatos ─────────────────────
        $maxGrado = !empty($nodos)
            ? max(array_column($nodos, 'valor'))
            : 0;
        $maxMonto = !empty($aristas)
            ? max(array_column($aristas, 'peso'))
            : 0;

        $montoTotal      = $proyectos->sum('monto_total');
        $montoFormateado = $this->formatearMonto((float) $montoTotal);

        return [
            'nodos'   => $nodos,
            'aristas' => $aristas,
            'meta'    => [
                'total_nodos'      => count($nodos),
                'total_aristas'    => count($aristas),
                'total_actores'    => count($actoresMap),
                'total_provincias' => count($provinciasMap),
                'total_proyectos'  => $proyectos->count(),
                'monto_total'      => (float) $montoTotal,
                'monto_formateado' => $montoFormateado,
                'max_grado'        => $maxGrado,
                'max_monto'        => $maxMonto,
            ],
        ];
    }

    private function formatearMonto(float $monto): string
    {
        if ($monto === 0.0) return '$0 USD';
        if ($monto >= 1_000_000) {
            return '$' . number_format($monto / 1_000_000, 1) . 'M USD';
        }
        if ($monto >= 1_000) {
            return '$' . number_format($monto / 1_000, 1) . 'K USD';
        }
        return '$' . number_format($monto, 0, '.', ',') . ' USD';
    }
}
