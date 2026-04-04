<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OdsResource extends JsonResource
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
            'numero' => $this->numero,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'color_hex' => $this->color_hex,
            'icono_url' => $this->icono_url,
        ];
    }
}
