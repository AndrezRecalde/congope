<?php

namespace App\Http\Requests\ActorCooperacion;

use Illuminate\Foundation\Http\FormRequest;

class UpdateActorCooperacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('actores.editar');
    }

    public function rules(): array
    {
        $actorId = $this->route('actore') ?? $this->route('actor') ?? $this->route('id');

        return [
            'identificador_institucional' => 'nullable|string|min:10|max:25|unique:actores_cooperacion,identificador_institucional,' . $actorId,
            'nombre' => 'sometimes|required|string|max:255|unique:actores_cooperacion,nombre,' . $actorId,
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'tipo' => 'sometimes|required|in:ONG,Multilateral,Embajada,Bilateral,Descentralizada,Privado,Academia',
            'pais_origen' => 'sometimes|required|string|max:100',
            'estado' => 'sometimes|in:Activo,Inactivo,Potencial',
            'contacto_nombre' => 'nullable|string|max:200',
            'contacto_email' => 'nullable|email|max:255',
            'contacto_telefono' => 'nullable|string|max:50',
            'sitio_web' => 'nullable|url|max:500',
            'notas' => 'nullable|string',
            'areas_tematicas' => 'nullable|array',
            'areas_tematicas.*' => 'string|max:150',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'tipo.required' => 'El tipo es obligatorio.',
            'tipo.in' => 'El tipo no es válido.',
            'pais_origen.required' => 'El país de origen es obligatorio.',
            'estado.in' => 'El estado no es válido.',
            'contacto_email.email' => 'El correo de contacto no es válido.',
            'sitio_web.url' => 'El sitio web no es válido.',
        ];
    }
}
