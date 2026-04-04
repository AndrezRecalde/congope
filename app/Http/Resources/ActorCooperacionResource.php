<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActorCooperacionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'tipo' => $this->tipo,
            'pais_origen' => $this->pais_origen,
            'estado' => $this->estado,
            'contacto_nombre' => $this->contacto_nombre,
            'contacto_email' => $this->contacto_email,
            'contacto_telefono' => $this->contacto_telefono,
            'sitio_web' => $this->sitio_web,
            'areas_tematicas' => $this->whenLoaded('areasTematicas', function () {
                return $this->areasTematicas->pluck('area');
            }),
            'proyectos_count' => $this->whenCounted('proyectos'),
            'created_at' => $this->created_at ? $this->created_at->format('d/m/Y H:i') : null,
        ];
    }
}
