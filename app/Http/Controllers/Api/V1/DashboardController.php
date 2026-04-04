<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DashboardController extends ApiController
{
    use AuthorizesRequests;

    protected $service;

    public function __construct(DashboardService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('dashboard.ver');

        $kpis = $this->service->obtenerKpis($request->user());
        $alertas = $this->service->obtenerAlertas($request->user());

        return $this->respondSuccess([
            'kpis' => $kpis,
            'alertas' => $alertas,
        ], 'Dashboard obtenido');
    }

    public function graficaAnual(Request $request): JsonResponse
    {
        $this->authorize('dashboard.ver');

        $datos = $this->service->obtenerProyectosPorAnio($request->user());

        return $this->respondSuccess($datos, 'Gráfica anual obtenida');
    }

    public function graficaOds(Request $request): JsonResponse
    {
        $this->authorize('dashboard.ver');

        $datos = $this->service->obtenerProyectosPorOds($request->user());

        return $this->respondSuccess($datos, 'Distribución ODS obtenida');
    }
}
