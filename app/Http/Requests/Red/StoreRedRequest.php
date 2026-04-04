<?php

namespace App\Http\Requests\Red;

use Illuminate\Foundation\Http\FormRequest;

class StoreRedRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('redes.crear');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:300'],
            'tipo' => ['required', 'in:Regional,Nacional,Internacional,Temática'],
            'objetivo' => ['nullable', 'string'],
            'rol_congope' => ['required', 'in:Miembro,Coordinador,Observador'],
            'fecha_adhesion' => ['nullable', 'date'],
            'actor_ids' => ['nullable', 'array'],
            'actor_ids.*' => ['uuid', 'exists:actores_cooperacion,id'],
            'rol_miembro' => ['nullable', 'string', 'max:150'], // Agregado para el sync inicial
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
            'nombre.required' => 'El nombre de la red es obligatorio.',
            'nombre.string' => 'El nombre de la red debe ser texto.',
            'nombre.max' => 'El nombre de la red no debe exceder los 300 caracteres.',
            'tipo.required' => 'El tipo de red es obligatorio.',
            'tipo.in' => 'El tipo de red seleccionado no es válido.',
            'rol_congope.required' => 'El rol del CONGOPE es obligatorio.',
            'rol_congope.in' => 'El rol seleccionado no es válido.',
            'fecha_adhesion.date' => 'La fecha de adhesión no es una fecha válida.',
            'actor_ids.array' => 'Los actores deben enviarse como una lista.',
            'actor_ids.*.uuid' => 'El ID del actor enviado no es un UUID válido.',
            'actor_ids.*.exists' => 'El actor seleccionado no existe en el sistema.',
        ];
    }
}
