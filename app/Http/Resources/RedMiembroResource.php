<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\ActorCooperacion;

class RedMiembroResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isActor = $this->resource instanceof ActorCooperacion;
        
        $actor = $isActor ? $this->resource : $this->whenLoaded('actor');
        
        $rolMiembro = $isActor ? optional($this->pivot)->rol_miembro : $this->rol_miembro;
        $fechaIngreso = $isActor ? optional($this->pivot)->fecha_ingreso : $this->fecha_ingreso;

        return [
            'id' => $isActor ? (optional($this->pivot)->id ?? $this->id) : $this->id,
            'actor' => $isActor ? new ActorResumenResource($actor) : new ActorResumenResource($actor),
            'rol_miembro' => $rolMiembro,
            'fecha_ingreso' => $fechaIngreso ? \Carbon\Carbon::parse($fechaIngreso)->format('d/m/Y') : null,
        ];
    }
}
