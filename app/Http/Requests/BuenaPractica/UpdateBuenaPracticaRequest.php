<?php

namespace App\Http\Requests\BuenaPractica;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBuenaPracticaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('practicas.editar');
    }

    public function rules(): array
    {
        return [
            'provincia_id' => 'sometimes|required|uuid|exists:provincias,id',
            'proyecto_id' => 'nullable|uuid|exists:proyectos,id',
            'titulo' => 'sometimes|required|string|max:500',
            'descripcion_problema' => 'sometimes|required|string',
            'metodologia' => 'sometimes|required|string',
            'resultados' => 'sometimes|required|string',
            'replicabilidad' => 'sometimes|required|in:Alta,Media,Baja',
            'es_destacada' => 'sometimes|boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'provincia_id.required' => 'La provincia es obligatoria.',
            'provincia_id.uuid' => 'La provincia debe ser un identificador válido.',
            'provincia_id.exists' => 'La provincia seleccionada no es válida.',
            'proyecto_id.uuid' => 'El proyecto debe ser un identificador válido.',
            'proyecto_id.exists' => 'El proyecto seleccionado no es válido.',
            'titulo.required' => 'El título es obligatorio.',
            'titulo.string' => 'El título debe ser texto.',
            'titulo.max' => 'El título no puede exceder los 500 caracteres.',
            'descripcion_problema.required' => 'La descripción del problema es obligatoria.',
            'descripcion_problema.string' => 'La descripción debe ser texto.',
            'metodologia.required' => 'La metodología es obligatoria.',
            'metodologia.string' => 'La metodología debe ser texto.',
            'resultados.required' => 'Los resultados son obligatorios.',
            'resultados.string' => 'Los resultados deben ser texto.',
            'replicabilidad.required' => 'La replicabilidad es obligatoria.',
            'replicabilidad.in' => 'La replicabilidad debe ser Alta, Media o Baja.',
            'es_destacada.boolean' => 'El estado destacado debe ser verdadero o falso.'
        ];
    }
}
