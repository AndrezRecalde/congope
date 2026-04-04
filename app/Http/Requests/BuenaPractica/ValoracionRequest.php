<?php

namespace App\Http\Requests\BuenaPractica;

use Illuminate\Foundation\Http\FormRequest;

class ValoracionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('practicas.valorar');
    }

    public function rules(): array
    {
        return [
            'puntuacion' => 'required|integer|min:1|max:5',
            'comentario' => 'nullable|string|max:500'
        ];
    }

    public function messages(): array
    {
        return [
            'puntuacion.required' => 'La puntuación es obligatoria.',
            'puntuacion.integer' => 'La puntuación debe ser un número entero.',
            'puntuacion.min' => 'La puntuación mínima es 1.',
            'puntuacion.max' => 'La puntuación máxima es 5.',
            'comentario.string' => 'El comentario debe ser texto.',
            'comentario.max' => 'El comentario no puede exceder los 500 caracteres.'
        ];
    }
}
