<?php

namespace App\Http\Requests\Usuario;

use Illuminate\Foundation\Http\FormRequest;

class AsignarRolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('usuarios.asignar_rol');
    }

    public function rules(): array
    {
        return [
            'rol' => 'required|in:super_admin,admin_provincial,editor,visualizador,publico',
        ];
    }

    public function messages(): array
    {
        return [
            'rol.required' => 'El rol es obligatorio.',
            'rol.in' => 'El rol seleccionado no es válido.',
        ];
    }
}
