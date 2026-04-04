<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use App\Models\Reconocimiento;
use App\Models\ProyectoEmblematico;
use App\Services\ReconocimientoService;
use App\Http\Requests\Reconocimiento\StoreReconocimientoRequest;
use App\Http\Requests\Reconocimiento\UpdateReconocimientoRequest;
use App\Http\Resources\ReconocimientoResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class ReconocimientoController extends ApiController
{
    public function __construct(private ReconocimientoService $service)
    {
    }

    public function index(ProyectoEmblematico $emblematico): JsonResponse
    {
        $reconocimientos = $emblematico->reconocimientos()->orderBy('anio', 'desc')->get();
        return $this->respondSuccess(ReconocimientoResource::collection($reconocimientos), 'Reconocimientos obtenidos');
    }

    public function store(StoreReconocimientoRequest $request, ProyectoEmblematico $emblematico): JsonResponse
    {
        Gate::authorize('create', Reconocimiento::class);
        $reco = $this->service->crear($emblematico, $request->validated());
        return $this->respondCreated(new ReconocimientoResource($reco), 'Reconocimiento registrado');
    }

    public function update(UpdateReconocimientoRequest $request, ProyectoEmblematico $emblematico, Reconocimiento $reconocimiento): JsonResponse
    {
        Gate::authorize('update', $reconocimiento);
        $reco = $this->service->actualizar($reconocimiento, $request->validated());
        return $this->respondSuccess(new ReconocimientoResource($reco), 'Reconocimiento actualizado');
    }

    public function destroy(ProyectoEmblematico $emblematico, Reconocimiento $reconocimiento): JsonResponse
    {
        Gate::authorize('delete', $reconocimiento);
        $this->service->eliminar($reconocimiento);
        return $this->respondSuccess(null, 'Reconocimiento eliminado');
    }
}
