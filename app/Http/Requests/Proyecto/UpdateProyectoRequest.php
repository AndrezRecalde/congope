<?php

namespace App\Http\Requests\Proyecto;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProyectoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('proyectos.editar');
    }

    public function rules(): array
    {
        return [
            'codigo' => 'sometimes|nullable|string|max:50|unique:proyectos,codigo,' . $this->route('proyecto'),
            'nombre' => 'sometimes|required|string|max:255',
            'actor_id' => 'sometimes|required|uuid|exists:actores_cooperacion,id',
            'estado' => 'sometimes|required|string|in:En gestión,En ejecución,Finalizado,Suspendido',
            'monto_total' => 'sometimes|required|numeric|min:0',
            'moneda' => 'sometimes|required|string|max:10',
            'fecha_inicio' => 'sometimes|required|date',
            'fecha_fin_planificada' => 'sometimes|required|date|after_or_equal:fecha_inicio',
            'fecha_fin_real' => 'nullable|date|after_or_equal:fecha_inicio',
            'porcentaje_avance' => 'sometimes|required|integer|min:0|max:100',
            'beneficiarios_directos' => 'nullable|integer|min:0',
            'beneficiarios_indirectos' => 'nullable|integer|min:0',
            'sector_tematico' => 'sometimes|required|string|max:150',
            'ubicacion' => ['nullable', 'string', 'regex:/^POINT\(\s*[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)\s+[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?)\s*\)$/i'],
            'provincia_ids' => 'nullable|array',
            'provincia_ids.*' => 'uuid|exists:provincias,id',
            'ods_ids' => 'nullable|array',
            'ods_ids.*' => 'integer|exists:ods,id',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio',
            'actor_id.required' => 'El actor de cooperación es obligatorio',
            'estado.required' => 'El estado es obligatorio',
            'monto_total.required' => 'El monto total es obligatorio',
            'moneda.required' => 'La moneda es obligatoria',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria',
            'fecha_fin_planificada.required' => 'La fecha de fin planificada es obligatoria',
            'fecha_fin_planificada.after_or_equal' => 'La fecha planificada debe ser posterior o igual al inicio',
            'porcentaje_avance.required' => 'El porcentaje de avance es obligatorio',
            'sector_tematico.required' => 'El sector temático es obligatorio',
        ];
    }
}
