<?php

namespace App\Http\Requests\Proyecto;

use Illuminate\Foundation\Http\FormRequest;

class StoreProyectoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('proyectos.crear');
    }

    public function rules(): array
    {
        return [
            'codigo' => 'nullable|string|max:50|unique:proyectos,codigo',
            'nombre' => 'required|string|max:500',
            'descripcion' => 'nullable|string',
            'actor_id' => 'required|uuid|exists:actores_cooperacion,id',
            'estado' => 'required|string|in:En gestión,En ejecución,Finalizado,Suspendido',
            'monto_total' => 'required|numeric|min:0',
            'moneda' => 'required|string|max:10',
            'sector_tematico' => 'required|string|max:150',
            'flujo_direccion' => 'nullable|string|in:Norte-Sur,Sur-Sur,Triangular,Interna,Descentralizada',
            'modalidad_cooperacion' => 'nullable|array',
            'modalidad_cooperacion.*' => 'string|in:Técnica,Financiera No Reembolsable,Financiera Reembolsable,En Especies',
            'fecha_inicio' => 'required|date',
            'fecha_fin_planificada' => 'required|date|after_or_equal:fecha_inicio',
            'fecha_fin_real' => 'nullable|date|after_or_equal:fecha_inicio',
            'provincias' => 'nullable|array',
            'provincias.*.id' => 'required|uuid|exists:provincias,id',
            'provincias.*.rol' => 'nullable|string|in:Líder,Co-ejecutora,Beneficiaria',
            'provincias.*.porcentaje_avance' => 'nullable|integer|min:0|max:100',
            'provincias.*.beneficiarios_directos' => 'nullable|integer|min:0',
            'provincias.*.beneficiarios_indirectos' => 'nullable|integer|min:0',
            'canton_ids' => 'nullable|array',
            'canton_ids.*' => 'uuid|exists:cantones,id',
            'parroquia_ids' => 'nullable|array',
            'parroquia_ids.*' => 'uuid|exists:parroquias,id',
            'ubicaciones' => 'nullable|array',
            'ubicaciones.*.nombre' => 'nullable|string|max:255',
            'ubicaciones.*.lat' => 'required_with:ubicaciones|numeric|between:-90,90',
            'ubicaciones.*.lng' => 'required_with:ubicaciones|numeric|between:-180,180',
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
            'provincias.*.porcentaje_avance.max' => 'El porcentaje de avance por provincia no puede ser más de 100',
            'sector_tematico.required' => 'El sector temático es obligatorio',
        ];
    }
}
