<?php

namespace App\Exports;

use App\Models\ActorCooperacion;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ActoresCooperacionExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected array $filtros;

    public function __construct(array $filtros)
    {
        $this->filtros = $filtros;
    }

    public function query()
    {
        $query = ActorCooperacion::query();

        if (!empty($this->filtros['search'])) {
            $query->buscar($this->filtros['search']);
        }

        if (!empty($this->filtros['estado'])) {
            $query->where('estado', $this->filtros['estado']);
        }

        if (!empty($this->filtros['tipo'])) {
            $query->porTipo($this->filtros['tipo']);
        }

        if (!empty($this->filtros['pais_origen'])) {
            $query->porPais($this->filtros['pais_origen']);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nombre',
            'Tipo',
            'País de Origen',
            'Estado',
            'Contacto (Nombre)',
            'Contacto (Email)',
            'Contacto (Teléfono)',
            'Sitio Web',
            'Notas',
            'Fecha de Registro',
        ];
    }

    public function map($actor): array
    {
        return [
            $actor->id,
            $actor->nombre,
            $actor->tipo,
            $actor->pais_origen,
            $actor->estado,
            $actor->contacto_nombre,
            $actor->contacto_email,
            $actor->contacto_telefono,
            $actor->sitio_web,
            $actor->notas,
            $actor->created_at ? $actor->created_at->format('Y-m-d H:i') : '',
        ];
    }
}
