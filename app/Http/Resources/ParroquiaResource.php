<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParroquiaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'canton_id' => $this->canton_id,
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'canton' => new CantonResource($this->whenLoaded('canton')),
            'creado_el' => $this->created_at,
            'actualizado_el' => $this->updated_at,
        ];
    }
}
