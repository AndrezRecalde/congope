<?php

namespace App\Exports;

use App\Models\Proyecto;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProyectosExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected array $filtros;

    public function __construct(array $filtros)
    {
        $this->filtros = $filtros;
    }

    public function query()
    {
        $query = Proyecto::query()->with(['actor']);

        if (!empty($this->filtros['search'])) {
            $query->where(function($q) {
                $q->where('nombre', 'LIKE', '%' . $this->filtros['search'] . '%')
                  ->orWhere('codigo', 'LIKE', '%' . $this->filtros['search'] . '%');
            });
        }

        if (!empty($this->filtros['estado'])) {
            $query->where('estado', $this->filtros['estado']);
        }

        if (!empty($this->filtros['actor_id'])) {
            $query->where('actor_id', $this->filtros['actor_id']);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Código',
            'Nombre',
            'Actor Cooperación',
            'Estado',
            'Monto Total',
            'Moneda',
            'Fecha Inicio',
            'Fecha Fin Planificada',
            'Avance (%)',
            'Sector Temático',
            'Beneficiarios Directos',
            'Beneficiarios Indirectos',
            'Fecha Modificación',
        ];
    }

    public function map($proyecto): array
    {
        return [
            $proyecto->id,
            $proyecto->codigo,
            $proyecto->nombre,
            $proyecto->actor ? $proyecto->actor->nombre : '',
            $proyecto->estado,
            $proyecto->monto_total ?? 0,
            $proyecto->moneda,
            $proyecto->fecha_inicio ? $proyecto->fecha_inicio->format('Y-m-d') : '',
            $proyecto->fecha_fin_planificada ? $proyecto->fecha_fin_planificada->format('Y-m-d') : '',
            $proyecto->porcentaje_avance . '%',
            $proyecto->sector_tematico,
            $proyecto->beneficiarios_directos ?? 0,
            $proyecto->beneficiarios_indirectos ?? 0,
            $proyecto->updated_at ? $proyecto->updated_at->format('Y-m-d H:i') : '',
        ];
    }
}
