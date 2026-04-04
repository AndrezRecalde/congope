<?php

namespace App\Http\Requests\ActorCooperacion;

use Illuminate\Foundation\Http\FormRequest;

class StoreActorCooperacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('actores.crear');
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|in:ONG,Multilateral,Embajada,Bilateral,Privado,Academia',
            'pais_origen' => 'required|string|max:100',
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
