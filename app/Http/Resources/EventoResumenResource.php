<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventoResumenResource extends JsonResource
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
            'titulo' => $this->titulo,
            'tipo_evento' => $this->tipo_evento,
            'fecha_evento' => $this->fecha_evento ? $this->fecha_evento->format('d/m/Y') : null,
        ];
    }
}
