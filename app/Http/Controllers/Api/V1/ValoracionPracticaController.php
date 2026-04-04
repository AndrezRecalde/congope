<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use App\Models\BuenaPractica;
use App\Services\ValoracionPracticaService;
use App\Http\Requests\BuenaPractica\ValoracionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class ValoracionPracticaController extends ApiController
{
    public function __construct(
        protected ValoracionPracticaService $service
    ) {}

    public function store(ValoracionRequest $request, BuenaPractica $buena_practica): JsonResponse
    {
        Gate::authorize('valorar', $buena_practica);

        $valoracion = $this->service->valorar(
            $buena_practica, 
            $request->validated(), 
            $request->user()
        );

        return $this->respondCreated([
            'valoracion' => $valoracion,
            'nueva_calificacion' => $buena_practica->fresh()->calificacion_promedio
        ], 'Valoración registrada');
    }

    public function destroy(BuenaPractica $buena_practica): JsonResponse
    {
        Gate::authorize('valorar', $buena_practica);

        $this->service->eliminarValoracion($buena_practica, auth()->user());

        return $this->respondSuccess(null, 'Valoración eliminada');
    }
}
