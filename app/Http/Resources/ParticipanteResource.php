<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParticipanteResource extends JsonResource
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
            'nombres' => $this->name,
            'email' => $this->email,
            'asistio' => $this->whenPivotLoaded('evento_participantes', function () {
                return (bool) $this->pivot->asistio;
            }),
            'confirmado_en' => $this->whenPivotLoaded('evento_participantes', function () {
                return $this->pivot->confirmado_en ? \Carbon\Carbon::parse($this->pivot->confirmado_en)->format('d/m/Y H:i') : null;
            }),
        ];
    }
}
