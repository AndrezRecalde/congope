<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProyectoEmblematicoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'descripcion_impacto' => $this->descripcion_impacto,
            'periodo' => $this->periodo,
            'es_publico' => $this->es_publico,
            'provincia' => new ProvinciaNombreResource($this->whenLoaded('provincia')),
            'proyecto' => new ProyectoResumenResource($this->whenLoaded('proyecto')),
            'reconocimientos' => ReconocimientoResource::collection($this->whenLoaded('reconocimientos')),
            'reconocimientos_count' => $this->whenCounted('reconocimientos'),
            'created_at' => $this->created_at,
        ];
    }
}
