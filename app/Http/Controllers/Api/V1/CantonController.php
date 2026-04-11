<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Canton\StoreCantonRequest;
use App\Http\Requests\Canton\UpdateCantonRequest;
use App\Http\Resources\CantonResource;
use App\Models\Canton;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CantonController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $cantones = Canton::query()
            ->with('provincia')
            ->when($request->search, fn($q) => $q->where('nombre', 'ilike', "%{$request->search}%"))
            ->when($request->provincia_id, fn($q) => $q->where('provincia_id', $request->provincia_id))
            ->orderBy('nombre', 'asc')
            ->paginate($request->per_page ?? 15);

        return $this->respondPaginated(
            CantonResource::collection($cantones),
            'Cantones obtenidos exitosamente'
        );
    }

    public function store(StoreCantonRequest $request): JsonResponse
    {
        $canton = Canton::create($request->validated());

        return $this->respondCreated(
            new CantonResource($canton),
            'Canton creado exitosamente'
        );
    }

    public function show(Canton $cantone): JsonResponse
    {
        $cantone->load('provincia');

        return $this->respondSuccess(
            new CantonResource($cantone),
            'Canton obtenido exitosamente'
        );
    }

    public function update(UpdateCantonRequest $request, Canton $cantone): JsonResponse
    {
        $cantone->update($request->validated());

        return $this->respondSuccess(
            new CantonResource($cantone),
            'Canton actualizado exitosamente'
        );
    }

    public function destroy(Canton $cantone): JsonResponse
    {
        $cantone->delete();

        return $this->respondSuccess(
            null,
            'Canton eliminado exitosamente'
        );
    }
}
