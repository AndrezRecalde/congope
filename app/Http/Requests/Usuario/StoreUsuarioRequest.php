<?php

namespace App\Http\Requests\Usuario;

use Illuminate\Foundation\Http\FormRequest;

class StoreUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('usuarios.crear');
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:200',
            'email' => 'required|email|unique:users,email',
            'rol' => 'required|in:super_admin,admin_provincial,editor,visualizador,publico',
            'provincia_ids' => 'nullable|required_if:rol,admin_provincial|required_if:rol,editor|array',
            'provincia_ids.*' => 'uuid|exists:provincias,id',
            'telefono' => 'required|string|max:15',
            'cargo' => 'required|string|max:255',
            'activo' => 'boolean',
            'entidad' => 'nullable|string|max:255',
            'dni' => 'nullable|string|max:30|unique:users,dni',
            'enviar_correo' => 'boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede tener más de 200 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser válido.',
            'email.unique' => 'El correo electrónico ya está en uso.',
            'rol.required' => 'El rol es obligatorio.',
            'rol.in' => 'El rol seleccionado no es válido.',
            'provincia_ids.required_if' => 'Las provincias son obligatorias para este rol.',
            'provincia_ids.array' => 'Las provincias deben enviarse como un arreglo.',
            'provincia_ids.*.uuid' => 'El ID de provincia debe ser un UUID válido.',
            'provincia_ids.*.exists' => 'La provincia seleccionada no existe.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'telefono.max' => 'El teléfono no puede tener más de 15 caracteres.',
            'cargo.required' => 'El cargo es obligatorio.',
            'cargo.max' => 'El cargo no puede tener más de 255 caracteres.',
            'dni.unique' => 'Este DNI ya está registrado.',
            'dni.max' => 'El DNI no puede tener más de 30 caracteres.',
        ];
    }
}
