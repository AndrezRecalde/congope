<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoriaBeneficiarioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'nombre'          => $this->nombre,
            'grupo'           => $this->grupo,
            'activo'          => $this->activo,
            'proyectos_count' => $this->whenCounted('proyectos'),
            'created_at'      => $this->created_at?->format('d/m/Y H:i'),
        ];
    }
}
