<?php

namespace App\Http\Requests\Usuario;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('usuarios.editar');
    }

    public function rules(): array
    {
        $userId = $this->route('usuario') ? $this->route('usuario')->id : null;

        return [
            'name' => 'sometimes|string|max:200',
            'email' => 'sometimes|email|unique:users,email,' . $userId,
            'telefono' => 'sometimes|string|max:15',
            'cargo' => 'sometimes|string|max:255',
            'activo' => 'boolean',
            'entidad' => 'nullable|string|max:255',
            'dni' => 'nullable|string|max:30|unique:users,dni,' . $userId,
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.max' => 'El nombre no puede tener más de 200 caracteres.',
            'email.email' => 'El correo electrónico debe ser válido.',
            'email.unique' => 'El correo electrónico ya está en uso.',
            'telefono.max' => 'El teléfono no puede tener más de 15 caracteres.',
            'cargo.max' => 'El cargo no puede tener más de 255 caracteres.',
            'dni.unique' => 'Este DNI ya está registrado.',
            'dni.max' => 'El DNI no puede tener más de 30 caracteres.',
        ];
    }
}
