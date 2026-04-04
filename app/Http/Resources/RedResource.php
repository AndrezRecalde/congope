<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'tipo' => $this->tipo,
            'objetivo' => $this->objetivo,
            'rol_congope' => $this->rol_congope,
            'fecha_adhesion' => $this->fecha_adhesion ? $this->fecha_adhesion->format('d/m/Y') : null,
            'miembros' => RedMiembroResource::collection($this->whenLoaded('miembros')),
            'miembros_count' => $this->whenCounted('miembros'),
            'documentos' => DocumentoResource::collection($this->whenLoaded('documentos')),
            'created_at' => $this->created_at ? $this->created_at->format('d/m/Y') : null,
        ];
    }
}
