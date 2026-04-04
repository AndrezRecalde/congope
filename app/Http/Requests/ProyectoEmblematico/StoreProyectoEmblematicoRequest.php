<?php

namespace App\Http\Requests\ProyectoEmblematico;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreProyectoEmblematicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('emblematicos.crear');
    }

    public function rules(): array
    {
        return [
            'proyecto_id'         => 'required|uuid|exists:proyectos,id',
            'provincia_id'        => 'required|uuid|exists:provincias,id',
            'titulo'              => 'required|string|max:500',
            'descripcion_impacto' => 'required|string',
            'periodo'             => 'nullable|string|max:50',
        ];
    }
}
