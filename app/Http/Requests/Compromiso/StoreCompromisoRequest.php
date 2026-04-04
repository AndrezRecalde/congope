<?php

namespace App\Http\Requests\Compromiso;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompromisoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('compromisos.crear');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'descripcion' => 'required|string|max:500',
            'responsable_id' => 'required|uuid|exists:users,id',
            'fecha_limite' => 'required|date|after:today',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.max' => 'La descripción no puede superar los 500 caracteres.',
            'responsable_id.required' => 'El responsable es obligatorio.',
            'responsable_id.uuid' => 'El identificador del responsable no es válido.',
            'responsable_id.exists' => 'El responsable especificado no existe.',
            'fecha_limite.required' => 'La fecha límite es obligatoria.',
            'fecha_limite.date' => 'La fecha límite no tiene un formato válido.',
            'fecha_limite.after' => 'La fecha límite debe ser una fecha posterior a hoy.',
        ];
    }
}
