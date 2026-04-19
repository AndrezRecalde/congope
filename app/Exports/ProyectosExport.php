<?php

namespace App\Exports;

use App\Models\Proyecto;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Exportación de proyectos a Excel.
 * Genera un archivo con CUATRO hojas:
 *   1. Proyectos      → datos principales
 *   2. Actores        → actores cooperantes por proyecto
 *   3. ODS            → objetivos de desarrollo sostenible
 *   4. Beneficiarios  → beneficiarios por provincia
 *
 * Respeta los mismos filtros del listado web:
 * estado, sector, flujo, actor, provincia, fechas.
 */
class ProyectosExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(
        private readonly array $filtros = []
    ) {}

    public function sheets(): array
    {
        // Cargar los proyectos UNA sola vez con
        // todas las relaciones necesarias para
        // las cuatro hojas del Excel.
        $proyectos = $this->cargarProyectos();

        return [
            new ProyectosHojaPrincipal($proyectos),
            new ProyectosHojaActores($proyectos),
            new ProyectosHojaOds($proyectos),
            new ProyectosHojaBeneficiarios($proyectos),
        ];
    }

    private function cargarProyectos(): Collection
    {
        $query = Proyecto::query()
            ->with([
                'actores:id,nombre,tipo,pais_origen',
                'provincias:id,nombre',
                'ods:id,numero,nombre',
                'hitos:id,proyecto_id,titulo,'
                    . 'fecha_limite,completado',
                'beneficiarios.provincia:id,nombre',
                'beneficiarios.categoria:'
                    . 'id,nombre,grupo',
            ])
            ->whereNull('deleted_at');

        // Aplicar los mismos filtros del listado
        if (!empty($this->filtros['estado'])) {
            $query->where(
                'estado', $this->filtros['estado']
            );
        }

        if (!empty($this->filtros['sector_tematico'])) {
            $query->where(
                'sector_tematico',
                'like',
                '%' . $this->filtros['sector_tematico']
                    . '%'
            );
        }

        if (!empty($this->filtros['flujo_direccion'])) {
            $query->where(
                'flujo_direccion',
                $this->filtros['flujo_direccion']
            );
        }

        if (!empty($this->filtros['actor_id'])) {
            $query->whereHas(
                'actores',
                fn($q) => $q->where(
                    'actores_cooperacion.id',
                    $this->filtros['actor_id']
                )
            );
        }

        if (!empty($this->filtros['provincia_id'])) {
            $query->whereHas(
                'provincias',
                fn($q) => $q->where(
                    'provincias.id',
                    $this->filtros['provincia_id']
                )
            );
        }

        if (!empty($this->filtros['search'])) {
            $s = $this->filtros['search'];
            $query->where(function ($q) use ($s) {
                $q->where('nombre', 'like', "%$s%")
                  ->orWhere('codigo', 'like', "%$s%");
            });
        }

        return $query
            ->orderBy('codigo')
            ->get();
    }
}
