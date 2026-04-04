<?php

namespace App\Http\Requests\Red;

use Illuminate\Foundation\Http\FormRequest;

class GestionarMiembrosRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('redes.gestionar_miembros');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'accion' => ['required', 'in:agregar,eliminar'],
            'actores' => ['required_if:accion,agregar', 'array'],
            'actores.*.actor_id' => ['uuid', 'exists:actores_cooperacion,id'],
            'actores.*.rol_miembro' => ['nullable', 'string', 'max:150'],
            'actores.*.fecha_ingreso' => ['nullable', 'date'],
            
            'actor_ids' => ['required_if:accion,eliminar', 'array'],
            'actor_ids.*' => ['uuid', 'exists:actores_cooperacion,id'],
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
            'accion.required' => 'La acción es obligatoria.',
            'accion.in' => 'La acción debe ser agregar o eliminar.',
            'actores.required_if' => 'Debe enviar los actores a agregar.',
            'actores.array' => 'Los actores deben ser una lista.',
            'actores.*.actor_id.uuid' => 'El ID del actor enviado a agregar no es válido.',
            'actores.*.actor_id.exists' => 'El actor enviado a agregar no existe.',
            'actores.*.fecha_ingreso.date' => 'La fecha de ingreso no es válida.',
            'actor_ids.required_if' => 'Debe enviar los IDs de los actores a eliminar.',
            'actor_ids.array' => 'Los IDs deben ser una lista.',
            'actor_ids.*.uuid' => 'El ID del actor a eliminar no es válido.',
            'actor_ids.*.exists' => 'El actor a eliminar no existe.',
        ];
    }
}
