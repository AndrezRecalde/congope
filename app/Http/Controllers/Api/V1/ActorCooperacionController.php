<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use App\Models\ActorCooperacion;
use App\Services\ActorCooperacionService;
use App\Http\Requests\ActorCooperacion\StoreActorCooperacionRequest;
use App\Http\Requests\ActorCooperacion\UpdateActorCooperacionRequest;
use App\Http\Resources\ActorCooperacionResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ActorCooperacionController extends ApiController
{
    public function __construct(
        protected ActorCooperacionService $service
    ) {}

    public function index(Request $request)
    {
        Gate::authorize('viewAny', ActorCooperacion::class);

        $filtros = $request->only(['search', 'estado', 'tipo', 'pais_origen']);
        $actores = $this->service->listar($filtros, $request->user());

        return $this->respondPaginated(ActorCooperacionResource::collection($actores), 'Actores listados correctamente');
    }

    public function store(StoreActorCooperacionRequest $request)
    {
        $actor = $this->service->crear($request->validated(), $request->user());

        return $this->respondCreated(new ActorCooperacionResource($actor), 'Actor de cooperación creado exitosamente');
    }

    public function show(string $id)
    {
        $actor = $this->service->obtener($id);
        Gate::authorize('view', $actor);

        return $this->respondSuccess(new ActorCooperacionResource($actor), 'Actor obtenido correctamente');
    }

    public function update(UpdateActorCooperacionRequest $request, string $id)
    {
        $actor = $this->service->obtener($id);
        // Authorization is handled by form request, but just to be sure we do it here if needed,
        // Wait, Form Request authorises general 'actores.editar' maybe. To be strictly compliant with Policy:
        Gate::authorize('update', $actor);

        $actor = $this->service->actualizar($actor, $request->validated());

        return $this->respondSuccess(new ActorCooperacionResource($actor), 'Actor de cooperación actualizado exitosamente');
    }

    public function destroy(string $id)
    {
        $actor = $this->service->obtener($id);
        Gate::authorize('delete', $actor);

        $this->service->eliminar($actor);

        return $this->respondSuccess(null, 'Actor de cooperación eliminado exitosamente');
    }

    public function exportar(Request $request)
    {
        Gate::authorize('exportar', ActorCooperacion::class);

        $filtros = $request->only(['search', 'estado', 'tipo', 'pais_origen']);
        return $this->service->exportarExcel($filtros);
    }
}
