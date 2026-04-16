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
            'nombre' => 'sometimes|required|string|max:500',
            'descripcion' => 'nullable|string',
            'actor_ids'   => 'sometimes|required|array|min:1',
            'actor_ids.*' => 'uuid|exists:actores_cooperacion,id',
            'estado' => 'sometimes|required|string|in:En gestión,En ejecución,Finalizado,Suspendido',
            'monto_total' => 'sometimes|required|numeric|min:0',
            'moneda' => 'sometimes|required|string|max:10',
            'sector_tematico' => 'sometimes|required|string|max:150',
            'flujo_direccion' => 'nullable|string|in:Norte-Sur,Sur-Sur,Triangular,Interna,Descentralizada',
            'modalidad_cooperacion' => 'nullable|array',
            'modalidad_cooperacion.*' => 'string|in:Técnica,Financiera No Reembolsable,Financiera Reembolsable,En Especies',
            'fecha_inicio' => 'sometimes|required|date',
            'fecha_fin_planificada' => 'sometimes|required|date|after_or_equal:fecha_inicio',
            'fecha_fin_real' => 'nullable|date|after_or_equal:fecha_inicio',
            'provincias' => 'nullable|array',
            'provincias.*.id' => 'required|uuid|exists:provincias,id',
            'provincias.*.rol' => 'nullable|string|in:Líder,Co-ejecutora,Beneficiaria',
            'provincias.*.porcentaje_avance' => 'nullable|integer|min:0|max:100',
            'canton_ids' => 'prohibited',
            'ubicaciones' => 'nullable|array',
            'ubicaciones.*.canton_id' => 'required_with:ubicaciones|uuid|exists:cantones,id',
            'ubicaciones.*.nombre' => 'nullable|string|max:255',
            'ubicaciones.*.lat' => 'required_with:ubicaciones|numeric|between:-90,90',
            'ubicaciones.*.lng' => 'required_with:ubicaciones|numeric|between:-180,180',
            'ods_ids' => 'nullable|array',
            'ods_ids.*' => 'integer|exists:ods,id',
            'beneficiarios' => 'nullable|array',
            'beneficiarios.*.provincia_id' => 'required_with:beneficiarios|uuid|exists:provincias,id',
            'beneficiarios.*.categoria_id' => 'required_with:beneficiarios|integer|exists:categorias_beneficiario,id',
            'beneficiarios.*.cantidad_directos' => 'nullable|integer|min:0',
            'beneficiarios.*.cantidad_indirectos' => 'nullable|integer|min:0',
            'beneficiarios.*.observaciones' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio',
            'actor_ids.required' => 'Debe seleccionar al menos un actor de cooperación',
            'actor_ids.min'      => 'Debe seleccionar al menos un actor de cooperación',
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
