<?php

namespace App\Http\Requests\ProyectoEmblematico;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateProyectoEmblematicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('emblematicos.editar');
    }

    public function rules(): array
    {
        return [
            'proyecto_id'         => 'sometimes|required|uuid|exists:proyectos,id',
            'provincia_id'        => 'sometimes|required|uuid|exists:provincias,id',
            'titulo'              => 'sometimes|required|string|max:500',
            'descripcion_impacto' => 'sometimes|required|string',
            'periodo'             => 'sometimes|nullable|string|max:50',
        ];
    }
}
