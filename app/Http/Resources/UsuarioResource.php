<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsuarioResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'cargo' => $this->cargo,
            'activo' => $this->activo,
            'entidad' => $this->entidad,
            'dni' => $this->dni,
            'requires_password_change' => $this->requires_password_change,
            'password_generada' => $this->when(isset($this->password_generada), $this->password_generada),
            'two_factor_enabled' => $this->two_factor_enabled,
            'roles' => $this->whenLoaded('roles', fn() => $this->roles->pluck('name')),
            'provincias' => ProvinciaNombreResource::collection($this->whenLoaded('provincias')),
            'permisos_count' => $this->whenCounted('permissions'),
            'created_at' => $this->created_at?->format('d/m/Y'),
            'email_verified_at' => $this->email_verified_at?->format('d/m/Y'),
        ];
    }
}
