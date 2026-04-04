<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use App\Models\BuenaPractica;
use App\Services\BuenaPracticaService;
use App\Http\Requests\BuenaPractica\StoreBuenaPracticaRequest;
use App\Http\Requests\BuenaPractica\UpdateBuenaPracticaRequest;
use App\Http\Resources\BuenaPracticaResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class BuenaPracticaController extends ApiController
{
    public function __construct(
        protected BuenaPracticaService $service
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', BuenaPractica::class);

        $buenasPracticas = $this->service->listar($request->all(), $request->user());

        return $this->respondPaginated(BuenaPracticaResource::collection($buenasPracticas), 'Buenas prácticas listadas correctamente');
    }

    public function store(StoreBuenaPracticaRequest $request): JsonResponse
    {
        $practica = $this->service->crear($request->validated(), $request->user());

        return $this->respondCreated(new BuenaPracticaResource($practica), 'Buena práctica creada exitosamente');
    }

    public function show(BuenaPractica $buena_practica): JsonResponse
    {
        Gate::authorize('view', $buena_practica);

        $practicaLoaded = $this->service->obtener($buena_practica->id);

        return $this->respondSuccess(new BuenaPracticaResource($practicaLoaded), 'Buena práctica obtenida correctamente');
    }

    public function update(UpdateBuenaPracticaRequest $request, BuenaPractica $buena_practica): JsonResponse
    {
        Gate::authorize('update', $buena_practica);

        $practicaActualizada = $this->service->actualizar($buena_practica, $request->validated());

        return $this->respondSuccess(new BuenaPracticaResource($practicaActualizada), 'Buena práctica actualizada exitosamente');
    }

    public function destroy(BuenaPractica $buena_practica): JsonResponse
    {
        Gate::authorize('delete', $buena_practica);

        $this->service->eliminar($buena_practica);

        return $this->respondSuccess(null, 'Buena práctica eliminada');
    }

    public function destacar(Request $request, BuenaPractica $buena_practica): JsonResponse
    {
        Gate::authorize('destacar', $buena_practica);

        $estado = $request->boolean('es_destacada', true);
        $practicaActualizada = $this->service->destacar($buena_practica, $estado);

        $msg = $estado ? 'Marcada como destacada' : 'Desmarcada como destacada';

        return $this->respondSuccess(new BuenaPracticaResource($practicaActualizada), $msg);
    }

    //TODO: IMPLEMENTAR LA FUNCIONALIDAD DE EXPORTAR
    public function exportar(Request $request)
    {
        Gate::authorize('exportar', BuenaPractica::class);

        $formato = $request->get('formato', 'excel');

        return match ($formato) {
            'pdf' => $this->service->exportarPdf($request->all()),
            default => $this->service->exportarExcel($request->all()),
        };
    }
}
