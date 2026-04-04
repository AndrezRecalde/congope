<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class CompromisoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $diasRestantes = null;
        $vencido = false;

        if (!$this->resuelto && $this->fecha_limite) {
            $fechaLimite = Carbon::parse($this->fecha_limite);
            $vencido = Carbon::today()->gt($fechaLimite);
            $diasRestantes = $vencido ? 0 : Carbon::today()->diffInDays($fechaLimite);
        }

        return [
            'id' => $this->id,
            'descripcion' => $this->descripcion,
            'fecha_limite' => $this->fecha_limite ? Carbon::parse($this->fecha_limite)->format('d/m/Y') : null,
            'resuelto' => (bool) $this->resuelto,
            'resuelto_en' => $this->resuelto_en ? Carbon::parse($this->resuelto_en)->format('d/m/Y') : null,
            'responsable' => new UserResumenResource($this->whenLoaded('responsable')),
            'evento' => new EventoResumenResource($this->whenLoaded('evento')),
            'dias_restantes' => $diasRestantes,
            'vencido' => $vencido,
            'created_at' => $this->created_at,
        ];
    }
}
