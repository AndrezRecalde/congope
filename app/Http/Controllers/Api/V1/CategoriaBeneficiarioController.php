<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use App\Http\Requests\CategoriaBeneficiario\StoreCategoriaRequest;
use App\Http\Requests\CategoriaBeneficiario\UpdateCategoriaRequest;
use App\Http\Resources\CategoriaBeneficiarioResource;
use App\Models\CategoriaBeneficiario;
use App\Services\CategoriaBeneficiarioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoriaBeneficiarioController extends ApiController
{
    public function __construct(
        protected CategoriaBeneficiarioService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filtros     = $request->only(['search', 'grupo']);
        $soloActivos = $request->boolean('activos', false);
        $categorias  = $this->service->listar($filtros, $soloActivos);

        return $this->respondSuccess(
            CategoriaBeneficiarioResource::collection($categorias),
            'Categorías de beneficiarios listadas'
        );
    }

    public function agrupadas(): JsonResponse
    {
        $agrupadas = $this->service->listarAgrupados();

        return $this->respondSuccess($agrupadas, 'Categorías agrupadas');
    }

    public function store(StoreCategoriaRequest $request): JsonResponse
    {
        $categoria = $this->service->crear($request->validated());

        return $this->respondCreated(
            new CategoriaBeneficiarioResource($categoria),
            'Categoría creada exitosamente'
        );
    }

    public function show(CategoriaBeneficiario $categorias_beneficiario): JsonResponse
    {
        $categoria = $this->service->obtener($categorias_beneficiario);

        return $this->respondSuccess(
            new CategoriaBeneficiarioResource($categoria),
            'Categoría obtenida'
        );
    }

    public function update(UpdateCategoriaRequest $request, CategoriaBeneficiario $categorias_beneficiario): JsonResponse
    {
        $categoria = $this->service->actualizar($categorias_beneficiario, $request->validated());

        return $this->respondSuccess(
            new CategoriaBeneficiarioResource($categoria),
            'Categoría actualizada exitosamente'
        );
    }

    public function destroy(CategoriaBeneficiario $categorias_beneficiario): JsonResponse
    {
        $this->service->eliminar($categorias_beneficiario);

        return $this->respondSuccess(null, 'Categoría eliminada exitosamente');
    }
}
