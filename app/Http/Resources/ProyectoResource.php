<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProyectoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'estado' => $this->estado,
            'color_marcador' => match ($this->estado) {
                'En ejecución' => '#10B981',
                'En gestión' => '#F59E0B',
                'Suspendido' => '#EF4444',
                'Finalizado' => '#0F52BA',
                default => '#9CA3AF'
            },
            'monto_total' => $this->monto_total,
            'monto_formateado' => $this->monto_formateado,
            'moneda' => $this->moneda,
            'fecha_inicio' => $this->fecha_inicio ? $this->fecha_inicio->format('Y-m-d') : null,
            'fecha_fin_planificada' => $this->fecha_fin_planificada ? $this->fecha_fin_planificada->format('Y-m-d') : null,
            'fecha_fin_real' => $this->fecha_fin_real ? $this->fecha_fin_real->format('Y-m-d') : null,
            'sector_tematico' => $this->sector_tematico,
            'flujo_direccion' => $this->flujo_direccion,
            'modalidad_cooperacion' => $this->modalidad_cooperacion,

            'actor' => new ActorCooperacionResource($this->whenLoaded('actor')),
            'provincias' => $this->whenLoaded('provincias', function () {
                return $this->provincias->map(function ($prov) {
                    return [
                        'id' => $prov->id,
                        'nombre' => $prov->nombre,
                        'rol' => $prov->pivot->rol ?? null,
                        'porcentaje_avance' => $prov->pivot->porcentaje_avance ?? null,
                        'beneficiarios_directos' => $prov->pivot->beneficiarios_directos ?? null,
                        'beneficiarios_indirectos' => $prov->pivot->beneficiarios_indirectos ?? null,
                    ];
                });
            }),

            // Ubicaciones agrupadas por cantón —fuente única de verdad.
            'ubicaciones_por_canton' => $this->whenLoaded('ubicaciones', function () {
                return $this->ubicaciones
                    ->load('canton')
                    ->groupBy('canton_id')
                    ->map(function ($ubicaciones, $cantonId) {
                        $canton = $ubicaciones->first()->canton;
                        return [
                            'canton_id' => $cantonId,
                            'canton_nombre' => $canton?->nombre,
                            'ubicaciones' => $ubicaciones->map(function ($u) {
                                return [
                                    'id' => $u->id,
                                    'nombre' => $u->nombre,
                                    'coordenadas' => $u->coordenadas,
                                ];
                            })->values(),
                        ];
                    })->values();
            }),

            // Lista plana de ubicaciones (mantiene compatibilidad con el mapa general).
            'ubicaciones' => $this->whenLoaded('ubicaciones', function () {
                return $this->ubicaciones->map(function ($u) {
                    return [
                        'id' => $u->id,
                        'canton_id' => $u->canton_id,
                        'nombre' => $u->nombre,
                        'coordenadas' => $u->coordenadas,
                    ];
                });
            }),

            // Cantones únicos cubiertos por el proyecto (derivados de ubicaciones).
            'cantones' => $this->whenLoaded('ubicaciones', function () {
                return $this->ubicaciones
                    ->load('canton')
                    ->pluck('canton')
                    ->filter()
                    ->unique('id')
                    ->map(fn($c) => ['id' => $c->id, 'nombre' => $c->nombre])
                    ->values();
            }),

            'ods' => $this->whenLoaded('ods', function () {
                return $this->ods->map(function ($o) {
                    return [
                        'id' => $o->id,
                        'numero' => $o->numero,
                        'nombre' => $o->nombre,
                    ];
                });
            }),
            'hitos_count' => $this->whenCounted('hitos'),
            'created_at' => $this->created_at ? $this->created_at->format('d/m/Y H:i') : null,
        ];
    }
}
