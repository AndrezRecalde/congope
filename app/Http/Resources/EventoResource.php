<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventoResource extends JsonResource
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
            'lugar' => $this->lugar,
            'es_virtual' => $this->es_virtual,
            'url_virtual' => $this->url_virtual,
            'descripcion' => $this->descripcion,
            'creado_por' => new UserResumenResource($this->whenLoaded('creadoPor')),
            'participantes' => ParticipanteResource::collection($this->whenLoaded('participantes')),
            'participantes_count' => $this->whenCounted('participantes'),
            'compromisos_count' => $this->whenCounted('compromisos'),
            'created_at' => $this->created_at,
        ];
    }
}
