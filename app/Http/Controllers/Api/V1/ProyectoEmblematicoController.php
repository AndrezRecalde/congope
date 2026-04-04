<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use App\Models\ProyectoEmblematico;
use App\Services\ProyectoEmblematicoService;
use App\Http\Requests\ProyectoEmblematico\StoreProyectoEmblematicoRequest;
use App\Http\Requests\ProyectoEmblematico\UpdateProyectoEmblematicoRequest;
use App\Http\Resources\ProyectoEmblematicoResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProyectoEmblematicoController extends ApiController
{
    public function __construct(private ProyectoEmblematicoService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', ProyectoEmblematico::class);
        $filtros = $request->all();
        $emblematicos = $this->service->listar($filtros, $request->user());
        return $this->respondPaginated($emblematicos, 'Proyectos emblemáticos obtenidos');
    }

    public function indexPublico(Request $request): JsonResponse
    {
        $filtros = $request->all();
        $emblematicos = $this->service->listarPublicos($filtros);
        return $this->respondPaginated($emblematicos, 'Proyectos emblemáticos públicos');
    }

    public function store(StoreProyectoEmblematicoRequest $request): JsonResponse
    {
        $emblematico = $this->service->crear($request->validated());
        return $this->respondCreated(new ProyectoEmblematicoResource($emblematico), 'Proyecto emblemático registrado');
    }

    public function show(ProyectoEmblematico $emblematico): JsonResponse
    {
        Gate::authorize('view', $emblematico);
        $emblematicoCargado = $this->service->obtener($emblematico->id);
        return $this->respondSuccess(new ProyectoEmblematicoResource($emblematicoCargado), 'Proyecto emblemático obtenido');
    }

    public function update(UpdateProyectoEmblematicoRequest $request, ProyectoEmblematico $emblematico): JsonResponse
    {
        Gate::authorize('update', $emblematico);
        $actualizado = $this->service->actualizar($emblematico, $request->validated());
        return $this->respondSuccess(new ProyectoEmblematicoResource($actualizado), 'Proyecto emblemático actualizado');
    }

    public function destroy(ProyectoEmblematico $emblematico): JsonResponse
    {
        Gate::authorize('delete', $emblematico);
        $this->service->eliminar($emblematico);
        return $this->respondSuccess(null, 'Proyecto emblemático eliminado');
    }

    public function publicar(Request $request, ProyectoEmblematico $emblematico): JsonResponse
    {
        Gate::authorize('publicar', $emblematico);
        $estado = $request->boolean('es_publico', true);
        $actualizado = $this->service->publicar($emblematico, $estado);
        $msg = $estado ? 'Publicado en portal público' : 'Retirado del portal público';
        return $this->respondSuccess(new ProyectoEmblematicoResource($actualizado), $msg);
    }
}
