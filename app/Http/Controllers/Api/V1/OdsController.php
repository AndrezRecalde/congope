<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use App\Http\Resources\OdsDetalleResource;
use App\Http\Resources\OdsResource;
use App\Models\Ods;
use Illuminate\Http\JsonResponse;

class OdsController extends ApiController
{
    public function index(): JsonResponse
    {
        $ods = Ods::orderBy('numero', 'asc')->get();

        return $this->respondSuccess(
            OdsResource::collection($ods),
            'ODS obtenidos'
        );
    }

    public function show(Ods $od): JsonResponse
    {
        $od->loadCount('proyectos');
        $od->load([
            'proyectos' => fn($q) => $q->activos()->limit(5)
        ]);

        return $this->respondSuccess(
            new OdsDetalleResource($od),
            'ODS obtenido'
        );
    }

    public function proyectosPorOds(Ods $od): JsonResponse
    {
        $proyectos = $od->proyectos()
            ->activos()
            ->with(['actor', 'provincias'])
            ->paginate(15);

        return $this->respondPaginated(
            $proyectos,
            "Proyectos alineados con ODS: {$od->nombre}"
        );
    }
}
