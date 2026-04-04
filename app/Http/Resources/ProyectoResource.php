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
            'estado' => $this->estado,
            'monto_total' => $this->monto_total,
            'monto_formateado' => $this->monto_formateado,
            'moneda' => $this->moneda,
            'fecha_inicio' => $this->fecha_inicio ? $this->fecha_inicio->format('Y-m-d') : null,
            'fecha_fin_planificada' => $this->fecha_fin_planificada ? $this->fecha_fin_planificada->format('Y-m-d') : null,
            'fecha_fin_real' => $this->fecha_fin_real ? $this->fecha_fin_real->format('Y-m-d') : null,
            'porcentaje_avance' => $this->porcentaje_avance,
            'beneficiarios_directos' => $this->beneficiarios_directos,
            'beneficiarios_indirectos' => $this->beneficiarios_indirectos,
            'sector_tematico' => $this->sector_tematico,
            'ubicacion' => $this->ubicacion,
            
            'actor' => new ActorCooperacionResource($this->whenLoaded('actor')),
            'provincias' => $this->whenLoaded('provincias', function () {
                return $this->provincias->map(function ($prov) {
                    return [
                        'id' => $prov->id,
                        'nombre' => $prov->nombre,
                    ];
                });
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
