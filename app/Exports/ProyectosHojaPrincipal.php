<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ProyectosHojaPrincipal implements
    FromCollection,
    WithHeadings,
    WithTitle,
    WithStyles,
    WithColumnWidths,
    WithMapping
{
    public function __construct(
        private readonly Collection $proyectos
    ) {}

    public function title(): string
    {
        return 'Proyectos';
    }

    public function collection(): Collection
    {
        return $this->proyectos;
    }

    public function headings(): array
    {
        return [
            'Código',
            'Nombre del Proyecto',
            'Estado',
            'Sector Temático',
            'Flujo de Cooperación',
            'Modalidad',
            'Monto Total',
            'Moneda',
            'Fecha Inicio',
            'Fecha Fin Planificada',
            'Fecha Fin Real',
            'Actores Cooperantes',
            'Provincias',
            'ODS',
            'Total Hitos',
            'Hitos Completados',
            '% Avance Hitos',
            'Descripción',
            'Registrado',
        ];
    }

    public function map($proyecto): array
    {
        // Hitos calculados
        $totalHitos      = $proyecto->hitos->count();
        $hitosCompletados = $proyecto->hitos
            ->where('completado', true)->count();
        $avanceHitos = $totalHitos > 0
            ? round(($hitosCompletados / $totalHitos)
                * 100) . '%'
            : 'Sin hitos';

        // Actores como texto separado por comas
        $actores = $proyecto->actores
            ->pluck('nombre')
            ->join(', ');

        // Provincias como texto separado por comas
        $provincias = $proyecto->provincias
            ->pluck('nombre')
            ->join(', ');

        // ODS como "ODS 6, ODS 11, ODS 13"
        $ods = $proyecto->ods
            ->map(fn($o) => "ODS {$o->numero}")
            ->join(', ');

        // Modalidad como texto separado por comas
        $modalidad = is_array(
            $proyecto->modalidad_cooperacion
        )
            ? implode(', ', $proyecto->modalidad_cooperacion)
            : ($proyecto->modalidad_cooperacion ?? '');

        return [
            $proyecto->codigo,
            $proyecto->nombre,
            $proyecto->estado,
            $proyecto->sector_tematico,
            $proyecto->flujo_direccion ?? '',
            $modalidad,
            number_format(
                (float) $proyecto->monto_total,
                2, '.', ','
            ),
            $proyecto->moneda,
            $proyecto->fecha_inicio
                ?->format('d/m/Y') ?? '',
            $proyecto->fecha_fin_planificada
                ?->format('d/m/Y') ?? '',
            $proyecto->fecha_fin_real
                ?->format('d/m/Y') ?? '',
            $actores,
            $provincias,
            $ods,
            $totalHitos,
            $hitosCompletados,
            $avanceHitos,
            $proyecto->descripcion ?? '',
            $proyecto->created_at
                ?->format('d/m/Y') ?? '',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18,  // Código
            'B' => 45,  // Nombre
            'C' => 15,  // Estado
            'D' => 25,  // Sector
            'E' => 22,  // Flujo
            'F' => 30,  // Modalidad
            'G' => 16,  // Monto
            'H' => 10,  // Moneda
            'I' => 14,  // Fecha inicio
            'J' => 18,  // Fecha fin plan
            'K' => 14,  // Fecha fin real
            'L' => 40,  // Actores
            'M' => 30,  // Provincias
            'N' => 25,  // ODS
            'O' => 12,  // Total hitos
            'P' => 16,  // Hitos completados
            'Q' => 14,  // % Avance
            'R' => 50,  // Descripción
            'S' => 14,  // Registrado
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $totalFilas =
            $this->proyectos->count() + 1;

        return [
            // Fila de encabezado
            1 => [
                'font' => [
                    'bold'  => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size'  => 11,
                ],
                'fill' => [
                    'fillType'   =>
                        Fill::FILL_SOLID,
                    'startColor' =>
                        ['rgb' => '1A3A5C'],
                ],
                'alignment' => [
                    'horizontal' =>
                        Alignment::HORIZONTAL_CENTER,
                    'vertical'   =>
                        Alignment::VERTICAL_CENTER,
                    'wrapText'   => true,
                ],
            ],
            // Filas de datos — alternadas
            "A2:S{$totalFilas}" => [
                'alignment' => [
                    'vertical' =>
                        Alignment::VERTICAL_CENTER,
                    'wrapText' => false,
                ],
            ],
        ];
    }
}
