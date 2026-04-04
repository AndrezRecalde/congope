<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use App\Services\ReporteService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ReporteController extends ApiController
{
    use AuthorizesRequests;

    protected $service;

    public function __construct(ReporteService $service)
    {
        $this->service = $service;
    }

    private function validarFormato(Request $request): string
    {
        $formato = $request->get('formato', 'pdf');

        if (!in_array($formato, ['pdf', 'excel', 'csv'])) {
            abort(422, 'Formato no válido. Use: pdf, excel o csv');
        }

        return $formato;
    }

    public function provincia(Request $request)
    {
        $this->authorize('reportes.generar');

        $request->validate([
            'provincia_id' => 'required|uuid|exists:provincias,id',
        ]);

        $formato = $this->validarFormato($request);

        return $this->service->reporteProvincia($request->provincia_id, $formato);
    }

    public function ods(Request $request)
    {
        $this->authorize('reportes.generar');

        $request->validate([
            'ods_id' => 'required|integer|between:1,17',
        ]);

        $formato = $this->validarFormato($request);

        return $this->service->reporteOds($request->ods_id, $formato);
    }

    public function cooperante(Request $request)
    {
        $this->authorize('reportes.generar');

        $request->validate([
            'actor_id' => 'required|uuid|exists:actores_cooperacion,id',
        ]);

        $formato = $this->validarFormato($request);

        return $this->service->reporteCooperante($request->actor_id, $formato);
    }

    public function anual(Request $request)
    {
        $this->authorize('reportes.generar');

        $request->validate([
            'anio' => 'required|integer|min:2000|max:' . now()->year,
        ]);

        $formato = $this->validarFormato($request);

        return $this->service->reporteAnual($request->anio, $formato);
    }

    public function global(Request $request)
    {
        $this->authorize('reportes.exportar_masivo');

        $formato = $this->validarFormato($request);

        return $this->service->reporteGlobal(
            $request->only(['estado', 'provincia_id', 'actor_id', 'ods_id', 'fecha_desde', 'fecha_hasta']),
            $formato
        );
    }
}
