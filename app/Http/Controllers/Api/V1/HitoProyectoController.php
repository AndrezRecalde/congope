<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use App\Models\Proyecto;
use App\Models\HitoProyecto;
use Illuminate\Http\Request;

class HitoProyectoController extends ApiController
{
    public function index(Request $request, string $proyectoId)
    {
        $proyecto = Proyecto::findOrFail($proyectoId);
        // Authorization depends on visibility of project
        if (!$request->user()->can('proyectos.ver')) {
            return $this->respondForbidden('No tiene permiso para ver hitos');
        }
        
        return $this->respondSuccess($proyecto->hitos, 'Hitos listados correctamente');
    }

    public function store(Request $request, string $proyectoId)
    {
        if (!$request->user()->can('hitos.crear')) {
            return $this->respondForbidden('No tiene permiso para crear hitos');
        }
        $proyecto = Proyecto::findOrFail($proyectoId);
        
        $validated = $request->validate([
            'titulo' => 'required|string|max:300',
            'descripcion' => 'nullable|string',
            'fecha_limite' => 'required|date',
            'completado' => 'sometimes|boolean'
        ]);

        if (!empty($validated['completado'])) {
            $validated['completado_en'] = now();
        } else {
            $validated['completado'] = false;
        }

        $hito = $proyecto->hitos()->create($validated);

        return $this->respondCreated($hito, 'Hito creado exitosamente');
    }

    public function update(Request $request, string $proyectoId, string $hitoId)
    {
        if (!$request->user()->can('hitos.editar')) {
            return $this->respondForbidden('No tiene permiso para editar hitos');
        }
        $hito = HitoProyecto::where('proyecto_id', $proyectoId)->findOrFail($hitoId);

        $validated = $request->validate([
            'titulo' => 'sometimes|required|string|max:300',
            'descripcion' => 'nullable|string',
            'fecha_limite' => 'sometimes|required|date',
            'completado' => 'sometimes|boolean'
        ]);

        if (array_key_exists('completado', $validated)) {
            if ($validated['completado'] && !$hito->completado) {
                $validated['completado_en'] = now();
            } else if (!$validated['completado']) {
                $validated['completado_en'] = null;
            }
        }

        $hito->update($validated);

        return $this->respondSuccess($hito, 'Hito actualizado exitosamente');
    }

    public function destroy(Request $request, string $proyectoId, string $hitoId)
    {
        if (!$request->user()->can('hitos.editar')) {
            return $this->respondForbidden('No tiene permiso para eliminar hitos');
        }
        $hito = HitoProyecto::where('proyecto_id', $proyectoId)->findOrFail($hitoId);
        $hito->delete();

        return $this->respondSuccess(null, 'Hito eliminado exitosamente');
    }

    public function completar(Request $request, string $proyectoId, string $hitoId)
    {
        if (!$request->user()->can('hitos.completar')) {
            return $this->respondForbidden('No tiene permiso para completar hitos');
        }

        $hito = HitoProyecto::where('proyecto_id', $proyectoId)->findOrFail($hitoId);
        $hito->update([
            'completado' => true,
            'completado_en' => now()
        ]);

        return $this->respondSuccess($hito, 'Hito marcado como completado');
    }
}
