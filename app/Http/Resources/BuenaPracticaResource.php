<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BuenaPracticaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'descripcion_problema' => $this->descripcion_problema,
            'metodologia' => $this->metodologia,
            'resultados' => $this->resultados,
            'replicabilidad' => $this->replicabilidad,
            'calificacion_promedio' => $this->calificacion_promedio,
            'es_destacada' => $this->es_destacada,
            'provincia' => new ProvinciaNombreResource($this->whenLoaded('provincia')),
            'proyecto' => new ProyectoResumenResource($this->whenLoaded('proyecto')),
            'registrado_por' => new UserResumenResource($this->whenLoaded('registradoPor')),
            'mi_valoracion' => $this->whenLoaded('valoraciones', function () {
                return $this->valoraciones->first() ? $this->valoraciones->first() : null;
            }),
            'valoraciones_count' => $this->whenCounted('valoraciones'),
            'created_at' => $this->created_at?->format('d/m/Y')
        ];
    }
}
