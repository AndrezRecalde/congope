<?php

namespace App\Http\Requests\Evento;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('eventos.editar');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'titulo' => 'sometimes|required|string|max:300',
            'tipo_evento' => 'sometimes|required|in:Misión técnica,Reunión bilateral,Conferencia,Visita de campo,Virtual,Otro',
            'fecha_evento' => 'sometimes|required|date',
            'lugar' => 'nullable|string|max:300',
            'es_virtual' => 'sometimes|boolean',
            'url_virtual' => 'required_if:es_virtual,true|nullable|url',
            'descripcion' => 'nullable|string',
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
            'titulo.required' => 'El título es obligatorio.',
            'titulo.max' => 'El título no puede superar los 300 caracteres.',
            'tipo_evento.required' => 'El tipo de evento es obligatorio.',
            'tipo_evento.in' => 'El tipo de evento seleccionado no es válido.',
            'fecha_evento.required' => 'La fecha del evento es obligatoria.',
            'fecha_evento.date' => 'La fecha del evento no tiene un formato válido.',
            'url_virtual.required_if' => 'La URL del evento virtual es obligatoria cuando es virtual.',
            'url_virtual.url' => 'La URL del evento virtual no tiene un formato válido.',
        ];
    }
}
