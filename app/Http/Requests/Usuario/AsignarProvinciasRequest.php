<?php

namespace App\Http\Requests\Usuario;

use Illuminate\Foundation\Http\FormRequest;

class AsignarProvinciasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('usuarios.asignar_provincia');
    }

    public function rules(): array
    {
        return [
            'provincia_ids' => 'required|array|min:1',
            'provincia_ids.*' => 'uuid|exists:provincias,id',
        ];
    }

    public function messages(): array
    {
        return [
            'provincia_ids.required' => 'Las provincias son obligatorias.',
            'provincia_ids.array' => 'Las provincias deben enviarse como un arreglo.',
            'provincia_ids.min' => 'Debe seleccionar al menos una provincia.',
            'provincia_ids.*.uuid' => 'El ID de provincia debe ser un UUID válido.',
            'provincia_ids.*.exists' => 'La provincia seleccionada no existe.',
        ];
    }
}
