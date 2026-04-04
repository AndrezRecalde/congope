<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use App\Models\Proyecto;
use App\Services\ProyectoService;
use App\Http\Requests\Proyecto\StoreProyectoRequest;
use App\Http\Requests\Proyecto\UpdateProyectoRequest;
use App\Http\Resources\ProyectoResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProyectoController extends ApiController
{
    public function __construct(
        protected ProyectoService $service
    ) {}

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Proyecto::class);

        $filtros = $request->only(['search', 'estado', 'actor_id']);
        $proyectos = $this->service->listar($filtros, $request->user());

        return $this->respondPaginated(ProyectoResource::collection($proyectos), 'Proyectos listados correctamente');
    }

    public function store(StoreProyectoRequest $request)
    {
        $proyecto = $this->service->crear($request->validated(), $request->user());

        return $this->respondCreated(new ProyectoResource($proyecto), 'Proyecto creado exitosamente');
    }

    public function show(Request $request, string $id)
    {
        $proyecto = $this->service->obtener($id, $request->user());
        Gate::authorize('view', $proyecto);

        return $this->respondSuccess(new ProyectoResource($proyecto), 'Proyecto obtenido correctamente');
    }

    public function update(UpdateProyectoRequest $request, string $id)
    {
        $proyecto = $this->service->obtener($id, $request->user());
        Gate::authorize('update', $proyecto);

        $proyecto = $this->service->actualizar($proyecto, $request->validated());

        return $this->respondSuccess(new ProyectoResource($proyecto), 'Proyecto actualizado exitosamente');
    }

    public function destroy(Request $request, string $id)
    {
        $proyecto = $this->service->obtener($id, $request->user());
        Gate::authorize('delete', $proyecto);

        $this->service->eliminar($proyecto);

        return $this->respondSuccess(null, 'Proyecto eliminado exitosamente');
    }

    public function cambiarEstado(Request $request, string $id)
    {
        $proyecto = $this->service->obtener($id, $request->user());
        Gate::authorize('cambiarEstado', $proyecto);

        $request->validate([
            'estado' => 'required|string|max:50'
        ]);

        $this->service->cambiarEstado($proyecto, $request->estado);

        return $this->respondSuccess(new ProyectoResource($proyecto), 'Estado del proyecto cambiado exitosamente');
    }

    public function exportar(Request $request)
    {
        Gate::authorize('exportar', Proyecto::class);

        $filtros = $request->only(['search', 'estado', 'actor_id']);
        return $this->service->exportarExcel($filtros);
    }
}
