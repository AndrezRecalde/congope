<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Parroquia\StoreParroquiaRequest;
use App\Http\Requests\Parroquia\UpdateParroquiaRequest;
use App\Http\Resources\ParroquiaResource;
use App\Models\Parroquia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParroquiaController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $parroquias = Parroquia::query()
            ->with('canton.provincia')
            ->when($request->search, fn($q) => $q->where('nombre', 'ilike', "%{$request->search}%"))
            ->when($request->canton_id, fn($q) => $q->where('canton_id', $request->canton_id))
            ->orderBy('nombre', 'asc')
            ->paginate($request->per_page ?? 15);

        return $this->respondPaginated(
            ParroquiaResource::collection($parroquias),
            'Parroquias obtenidas exitosamente'
        );
    }

    public function store(StoreParroquiaRequest $request): JsonResponse
    {
        $parroquia = Parroquia::create($request->validated());

        return $this->respondCreated(
            new ParroquiaResource($parroquia),
            'Parroquia creada exitosamente'
        );
    }

    public function show(Parroquia $parroquia): JsonResponse
    {
        $parroquia->load('canton.provincia');

        return $this->respondSuccess(
            new ParroquiaResource($parroquia),
            'Parroquia obtenida exitosamente'
        );
    }

    public function update(UpdateParroquiaRequest $request, Parroquia $parroquia): JsonResponse
    {
        $parroquia->update($request->validated());

        return $this->respondSuccess(
            new ParroquiaResource($parroquia),
            'Parroquia actualizada exitosamente'
        );
    }

    public function destroy(Parroquia $parroquia): JsonResponse
    {
        $parroquia->delete();

        return $this->respondSuccess(
            null,
            'Parroquia eliminada exitosamente'
        );
    }
}
