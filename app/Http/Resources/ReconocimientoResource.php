<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReconocimientoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'organismo_otorgante' => $this->organismo_otorgante,
            'ambito' => $this->ambito,
            'anio' => $this->anio,
            'descripcion' => $this->descripcion,
            'created_at' => $this->created_at,
        ];
    }
}
