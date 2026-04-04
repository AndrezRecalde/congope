<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use App\Models\Red;
use App\Services\RedService;
use App\Http\Requests\Red\StoreRedRequest;
use App\Http\Requests\Red\UpdateRedRequest;
use App\Http\Requests\Red\GestionarMiembrosRequest;
use App\Http\Resources\RedResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class RedController extends ApiController
{
    use AuthorizesRequests;

    protected RedService $redService;

    public function __construct(RedService $redService)
    {
        $this->redService = $redService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Red::class);

        $redes = $this->redService->listar($request->all());

        return $this->respondPaginated(RedResource::collection($redes), 'Redes obtenidas correctamente');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRedRequest $request): JsonResponse
    {
        $this->authorize('create', Red::class);

        $red = $this->redService->crear($request->validated());

        return $this->respondCreated(new RedResource($red), 'Red creada correctamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(Red $red): JsonResponse
    {
        $this->authorize('view', $red);

        $red = $this->redService->obtener($red->id);

        return $this->respondSuccess(new RedResource($red), 'Red obtenida correctamente');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRedRequest $request, Red $red): JsonResponse
    {
        $this->authorize('update', $red);

        $red = $this->redService->actualizar($red, $request->validated());

        return $this->respondSuccess(new RedResource($red), 'Red actualizada correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Red $red): JsonResponse
    {
        $this->authorize('delete', $red);

        $this->redService->eliminar($red);

        return $this->respondSuccess(null, 'Red eliminada correctamente');
    }

    /**
     * Gestionar miembros de una red.
     */
    public function gestionarMiembros(GestionarMiembrosRequest $request, Red $red): JsonResponse
    {
        $this->authorize('gestionarMiembros', $red);

        $red = $this->redService->gestionarMiembros($red, $request->validated());

        return $this->respondSuccess(new RedResource($red), 'Miembros actualizados correctamente');
    }
}
