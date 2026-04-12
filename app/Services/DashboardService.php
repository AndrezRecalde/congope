<?php

namespace App\Services;

use App\Models\Proyecto;
use App\Models\ActorCooperacion;
use App\Models\BuenaPractica;
use App\Models\Documento;
use App\Models\HitoProyecto;
use App\Models\CompromisoEvento;
use App\Models\Red;
use App\Models\Ods;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function obtenerKpis($usuario): array
    {
        $provinciaIds = $usuario->esSuperAdmin() || $usuario->can('dashboard.ver_global')
            ? null
            : $usuario->provincias()->pluck('provincias.id');

        $queryProyectos = Proyecto::query();
        $queryActores = ActorCooperacion::query();
        $queryPracticas = BuenaPractica::query();

        if ($provinciaIds && $provinciaIds->isNotEmpty()) {
            $queryProyectos->whereHas('provincias', function ($q) use ($provinciaIds) {
                $q->whereIn('provincias.id', $provinciaIds);
            });
            $queryPracticas->whereIn('provincia_id', $provinciaIds);
        } elseif ($provinciaIds && $provinciaIds->isEmpty()) {
            // Si no tiene provincias que devolver, prevenimos match global
            $queryProyectos->whereIn('id', []);
            $queryPracticas->whereIn('provincia_id', []);
        }

        return [
            'proyectos' => [
                'total' => $queryProyectos->count(),
                'en_gestion' => (clone $queryProyectos)->where('estado', 'En gestión')->count(),
                'en_ejecucion' => (clone $queryProyectos)->where('estado', 'En ejecución')->count(),
                'finalizados' => (clone $queryProyectos)->where('estado', 'Finalizado')->count(),
                'monto_total' => (clone $queryProyectos)->sum('monto_total'),
            ],
            'actores' => [
                'total' => $queryActores->count(),
                'activos' => (clone $queryActores)->where('estado', 'Activo')->count(),
                'por_tipo' => (clone $queryActores)
                    ->select('tipo', DB::raw('count(*) as total'))
                    ->groupBy('tipo')
                    ->pluck('total', 'tipo'),
            ],
            'practicas' => [
                'total' => $queryPracticas->count(),
                'destacadas' => (clone $queryPracticas)->where('es_destacada', true)->count(),
            ],
            'redes' => [
                'total' => Red::count(),
                'por_tipo' => Red::select('tipo', DB::raw('count(*) as total'))
                    ->groupBy('tipo')
                    ->pluck('total', 'tipo'),
            ],
            'proyectos_destacados' => Proyecto::with(['actor', 'provincias'])->where('estado', 'En ejecución')->orderByDesc('monto_total')->take(5)->get()->map(function ($p) {
                return [
                    'id' => $p->id,
                    'nombre' => $p->nombre,
                    'inversion' => (string) $p->monto_total,
                    'actor' => optional($p->actor)->nombre ?? 'No asignado',
                    'provincias' => $p->provincias->map(function ($prov) {
                        return ['nombre' => $prov->nombre, 'porcentaje_avance' => $prov->pivot->porcentaje_avance];
                    })->toArray()
                ];
            }),
        ];
    }

    public function obtenerProyectosPorAnio($usuario): array
    {
        return DB::table('proyectos')
            ->selectRaw('EXTRACT(YEAR FROM fecha_inicio) as anio, COUNT(*) as total, SUM(monto_total) as monto')
            ->whereNotNull('fecha_inicio')
            ->where('fecha_inicio', '>=', now()->subYears(5))
            ->whereNull('deleted_at')
            ->groupByRaw('EXTRACT(YEAR FROM fecha_inicio)')
            ->orderBy('anio', 'asc')
            ->get()
            ->toArray();
    }

    public function obtenerProyectosPorOds($usuario): array
    {
        return DB::table('proyecto_ods')
            ->join('ods', 'proyecto_ods.ods_id', '=', 'ods.id')
            ->join('proyectos', 'proyecto_ods.proyecto_id', '=', 'proyectos.id')
            ->selectRaw('ods.numero, ods.nombre, ods.color_hex, COUNT(proyectos.id) as total_proyectos')
            ->whereNull('proyectos.deleted_at')
            ->groupBy('ods.id', 'ods.numero', 'ods.nombre', 'ods.color_hex')
            ->orderBy('total_proyectos', 'desc')
            ->get()
            ->toArray();
    }

    public function obtenerAlertas($usuario): array
    {
        $provinciaIds = $usuario->esSuperAdmin() || $usuario->can('dashboard.ver_global')
            ? null
            : $usuario->provincias()->pluck('provincias.id');

        $documentosVenciendo = Documento::query()
            ->whereNotNull('fecha_vencimiento')
            ->whereBetween('fecha_vencimiento', [today(), today()->addDays(30)])
            ->count();

        $queryHitos = HitoProyecto::pendientes()->where('fecha_limite', '<', today());

        if ($provinciaIds && $provinciaIds->isNotEmpty()) {
            $queryHitos->whereHas('proyecto.provincias', function ($pq) use ($provinciaIds) {
                $pq->whereIn('provincias.id', $provinciaIds);
            });
        } elseif ($provinciaIds && $provinciaIds->isEmpty()) {
            $queryHitos->whereIn('id', []);
        }

        $hitosVencidos = $queryHitos->count();

        $compromisosVencidos = CompromisoEvento::pendientes()
            ->where('fecha_limite', '<', today())
            ->where('responsable_id', $usuario->id)
            ->count();

        return [
            'documentos_venciendo' => $documentosVenciendo,
            'hitos_vencidos' => $hitosVencidos,
            'compromisos_pendientes' => $compromisosVencidos,
        ];
    }
}
