<?php

namespace App\Services;

use App\Models\Provincia;
use App\Models\Ods;
use App\Models\ActorCooperacion;
use App\Models\Proyecto;
use App\Exports\GenericExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReporteService
{
    /**
     * Genera la descarga del archivo en el formato respectivo
     */
    private function generarDescarga(string $formato, string $vista, array $datosVista, array $datosExcel, array $cabecerasExcel, string $nombreArchivo)
    {
        if ($formato === 'pdf') {
            $pdf = Pdf::loadView("reportes.{$vista}", $datosVista);
            return $pdf->download("{$nombreArchivo}.pdf");
        }

        $export = new GenericExport($datosExcel, $cabecerasExcel);

        if ($formato === 'csv') {
            return Excel::download($export, "{$nombreArchivo}.csv", \Maatwebsite\Excel\Excel::CSV);
        }

        return Excel::download($export, "{$nombreArchivo}.xlsx", \Maatwebsite\Excel\Excel::XLSX);
    }

    public function reporteProvincia(string $provinciaId, string $formato)
    {
        $provincia = Provincia::findOrFail($provinciaId);

        $proyectos = Proyecto::whereHas('provincias', function ($q) use ($provinciaId) {
            $q->where('provincias.id', $provinciaId);
        })->get();

        $montoTotal = $proyectos->sum('monto_total');
        $activos = $proyectos->where('estado', 'En ejecución')->count();
        $finalizados = $proyectos->where('estado', 'Finalizado')->count();

        $datosVista = [
            'provincia' => $provincia,
            'proyectos' => $proyectos,
            'monto_total' => $montoTotal,
            'total_activos' => $activos,
            'total_finalizados' => $finalizados,
        ];

        // Mapeo para el Excel
        $datosExcel = $proyectos->map(function ($proyecto) {
            return [
                'Código' => $proyecto->codigo,
                'Nombre' => $proyecto->nombre,
                'Estado' => $proyecto->estado,
                'Monto Total' => $proyecto->monto_total,
                'Fecha Inicio' => $proyecto->fecha_inicio ? $proyecto->fecha_inicio->format('Y-m-d') : 'N/A',
                'Fecha Fin' => $proyecto->fecha_fin_real ? $proyecto->fecha_fin_real->format('Y-m-d') : 'N/A',
            ];
        })->toArray();

        $cabeceras = ['Código', 'Nombre', 'Estado', 'Monto Total', 'Fecha Inicio', 'Fecha Fin'];

        return $this->generarDescarga($formato, 'provincia', $datosVista, $datosExcel, $cabeceras, "Reporte_Provincia_{$provincia->nombre}");
    }

    public function reporteOds(int $odsId, string $formato)
    {
        $ods = Ods::findOrFail($odsId);

        $proyectos = Proyecto::whereHas('ods', function ($q) use ($odsId) {
            $q->where('ods.id', $odsId);
        })->get();

        $montoTotal = $proyectos->sum('monto_total');

        $datosVista = [
            'ods' => $ods,
            'proyectos' => $proyectos,
            'monto_total' => $montoTotal,
        ];

        $datosExcel = $proyectos->map(function ($proyecto) {
            return [
                'Código' => $proyecto->codigo,
                'Nombre' => $proyecto->nombre,
                'Estado' => $proyecto->estado,
                'Monto Total' => $proyecto->monto_total,
            ];
        })->toArray();

        $cabeceras = ['Código', 'Nombre', 'Estado', 'Monto Total'];

        return $this->generarDescarga($formato, 'ods', $datosVista, $datosExcel, $cabeceras, "Reporte_ODS_{$ods->numero}");
    }

    public function reporteCooperante(string $actorId, string $formato)
    {
        $actor = ActorCooperacion::findOrFail($actorId);

        $proyectos = Proyecto::whereHas('actores', function ($q) use ($actorId) {
            $q->where('actores_cooperacion.id', $actorId);
        })->get();

        $datosVista = [
            'actor' => $actor,
            'proyectos' => $proyectos,
        ];

        $datosExcel = $proyectos->map(function ($proyecto) {
            return [
                'Código' => $proyecto->codigo,
                'Nombre' => $proyecto->nombre,
                'Estado' => $proyecto->estado,
                'Monto Total' => $proyecto->monto_total,
            ];
        })->toArray();

        $cabeceras = ['Código', 'Nombre', 'Estado', 'Monto Total'];

        return $this->generarDescarga($formato, 'cooperante', $datosVista, $datosExcel, $cabeceras, "Reporte_Cooperante_" . str_replace(' ', '_', $actor->nombre));
    }

    public function reporteAnual(int $anio, string $formato)
    {
        $proyectos = Proyecto::whereYear('fecha_inicio', $anio)->get();

        $iniciados = $proyectos->count();
        $finalizados = Proyecto::whereYear('fecha_fin_real', $anio)
            ->where('estado', 'Finalizado')
            ->count();

        $montoTotal = $proyectos->sum('monto_total');

        $datosVista = [
            'anio' => $anio,
            'proyectos' => $proyectos,
            'iniciados' => $iniciados,
            'finalizados' => $finalizados,
            'monto_total' => $montoTotal,
        ];

        $datosExcel = $proyectos->map(function ($proyecto) {
            return [
                'Código' => $proyecto->codigo,
                'Nombre' => $proyecto->nombre,
                'Estado' => $proyecto->estado,
                'Monto Total' => $proyecto->monto_total,
                'Fecha Inicio' => $proyecto->fecha_inicio ? $proyecto->fecha_inicio->format('Y-m-d') : 'N/A',
            ];
        })->toArray();

        $cabeceras = ['Código', 'Nombre', 'Estado', 'Monto Total', 'Fecha Inicio'];

        return $this->generarDescarga($formato, 'anual', $datosVista, $datosExcel, $cabeceras, "Reporte_Anual_{$anio}");
    }

    public function reporteGlobal(array $filtros, string $formato)
    {
        $query = Proyecto::query();

        if (!empty($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }

        if (!empty($filtros['provincia_id'])) {
            $query->whereHas('provincias', function ($q) use ($filtros) {
                $q->where('provincias.id', $filtros['provincia_id']);
            });
        }

        if (!empty($filtros['actor_id'])) {
            $query->whereHas('actores', function ($q) use ($filtros) {
                $q->where('actores_cooperacion.id', $filtros['actor_id']);
            });
        }

        if (!empty($filtros['ods_id'])) {
            $query->whereHas('ods', function ($q) use ($filtros) {
                $q->where('ods.id', $filtros['ods_id']);
            });
        }

        if (!empty($filtros['fecha_desde'])) {
            $query->where('fecha_inicio', '>=', $filtros['fecha_desde']);
        }

        if (!empty($filtros['fecha_hasta'])) {
            $query->where('fecha_inicio', '<=', $filtros['fecha_hasta']);
        }

        $proyectos = $query->get();

        $datosVista = [
            'proyectos' => $proyectos,
            'filtros' => $filtros,
        ];

        $datosExcel = $proyectos->map(function ($proyecto) {
            return [
                'Código' => $proyecto->codigo,
                'Nombre' => $proyecto->nombre,
                'Estado' => $proyecto->estado,
                'Monto Total' => $proyecto->monto_total,
                'Fecha Inicio' => $proyecto->fecha_inicio ? $proyecto->fecha_inicio->format('Y-m-d') : 'N/A',
                'Fecha Fin' => $proyecto->fecha_fin_real ? $proyecto->fecha_fin_real->format('Y-m-d') : 'N/A',
            ];
        })->toArray();

        $cabeceras = ['Código', 'Nombre', 'Estado', 'Monto Total', 'Fecha Inicio', 'Fecha Fin'];

        return $this->generarDescarga($formato, 'global', $datosVista, $datosExcel, $cabeceras, "Reporte_Global_" . date('Ymd_Hi'));
    }
}
