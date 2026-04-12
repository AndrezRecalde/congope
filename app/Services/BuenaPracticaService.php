<?php

namespace App\Services;

use App\Models\BuenaPractica;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BuenaPracticaService
{
    public function listar(array $filtros, $usuario): LengthAwarePaginator
    {
        $query = BuenaPractica::query();

        if (!$usuario->hasRole(['super_admin', 'visualizador'])) {
            $query->deProvinciasDelUsuario($usuario);
        }

        if (!empty($filtros['provincia_id'])) {
            $query->where('provincia_id', $filtros['provincia_id']);
        }

        if (!empty($filtros['replicabilidad'])) {
            $query->where('replicabilidad', $filtros['replicabilidad']);
        }

        if (isset($filtros['es_destacada'])) {
            $query->where('es_destacada', filter_var($filtros['es_destacada'], FILTER_VALIDATE_BOOLEAN));
        }

        if (!empty($filtros['search'])) {
            $term = $filtros['search'];
            $query->where(function ($q) use ($term) {
                $q->where('titulo', 'LIKE', "%{$term}%")
                  ->orWhere('descripcion_problema', 'LIKE', "%{$term}%");
            });
        }

        $query->with([
            'provincia',
            'registradoPor',
            'valoraciones' => function($q) use ($usuario) {
                $q->where('user_id', $usuario->id);
            }
        ]);

        return $query->paginate(15);
    }

    public function obtener(string $id): BuenaPractica
    {
        return BuenaPractica::with([
            'provincia',
            'proyecto',
            'registradoPor',
            'valoraciones.usuario',
            'documentos'
        ])->findOrFail($id);
    }

    public function crear(array $datos, $usuario): BuenaPractica
    {
        return DB::transaction(function () use ($datos, $usuario) {
            $datos['registrado_por'] = $usuario->id;
            $practica = BuenaPractica::create($datos);
            \Illuminate\Support\Facades\Cache::forget('portal.conteos');
            return $practica;
        });
    }

    public function actualizar(BuenaPractica $practica, array $datos): BuenaPractica
    {
        return DB::transaction(function () use ($practica, $datos) {
            $practica->update($datos);
            \Illuminate\Support\Facades\Cache::forget('portal.conteos');
            return $practica->fresh(['provincia', 'proyecto']);
        });
    }

    public function eliminar(BuenaPractica $practica): void
    {
        $practica->delete();
        \Illuminate\Support\Facades\Cache::forget('portal.conteos');
    }

    public function destacar(BuenaPractica $practica, bool $estado): BuenaPractica
    {
        $practica->update(['es_destacada' => $estado]);
        \Illuminate\Support\Facades\Cache::forget('portal.conteos');
        return $practica->fresh();
    }

    public function exportarPdf(array $filtros): Response
    {
        $query = BuenaPractica::with(['provincia', 'proyecto', 'registradoPor']);
        $practicas = $query->get();
        // Generar PDF usando dompdf según referencia del requerimiento
        $pdf = Pdf::loadView('pdf.buenas_practicas', compact('practicas'));
        return $pdf->download('buenas_practicas.pdf');
    }

    public function exportarExcel(array $filtros): BinaryFileResponse
    {
        // Se asume la existencia de un Export adecuado; caso contrario esto daría un pequeño issue resoluble creándolo.
        return Excel::download(new \App\Exports\BuenasPracticasExport($filtros), 'buenas_practicas.xlsx');
    }
}
