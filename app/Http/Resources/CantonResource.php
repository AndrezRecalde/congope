<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CantonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'provincia_id' => $this->provincia_id,
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'provincia' => new ProvinciaResource($this->whenLoaded('provincia')),
            'creado_el' => $this->created_at,
            'actualizado_el' => $this->updated_at,
        ];
    }
}
