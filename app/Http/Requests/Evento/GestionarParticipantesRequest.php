<?php

namespace App\Http\Requests\Evento;

use Illuminate\Foundation\Http\FormRequest;

class GestionarParticipantesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('eventos.gestionar_participantes');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'accion' => 'required|in:agregar,eliminar,marcar_asistencia',
            'user_ids' => 'required_if:accion,agregar|required_if:accion,eliminar|array',
            'user_ids.*' => 'integer|exists:users,id',
            'user_id' => 'required_if:accion,marcar_asistencia|integer|exists:users,id',
            'asistio' => 'required_if:accion,marcar_asistencia|boolean',
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
            'accion.in' => 'La acción seleccionada no es válida.',
            'user_ids.required_if' => 'Debe proporcionar al menos un usuario para agregar o eliminar.',
            'user_ids.array' => 'Los usuarios deben ser una lista válida.',
            'user_ids.*.integer' => 'El identificador del usuario no es válido.',
            'user_ids.*.exists' => 'El usuario especificado no existe.',
            'user_id.required_if' => 'El usuario es obligatorio para marcar asistencia.',
            'user_id.integer' => 'El identificador del usuario no es válido.',
            'user_id.exists' => 'El usuario especificado no existe.',
            'asistio.required_if' => 'Debe indicar si el usuario asistió o no.',
            'asistio.boolean' => 'El valor de asistencia debe ser verdadero o falso.',
        ];
    }
}
