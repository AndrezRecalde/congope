<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ProyectosHojaActores implements
    FromCollection,
    WithHeadings,
    WithTitle,
    WithStyles,
    WithColumnWidths
{
    public function __construct(
        private readonly Collection $proyectos
    ) {}

    public function title(): string
    {
        return 'Actores Cooperantes';
    }

    public function collection(): Collection
    {
        // Una fila por cada relación proyecto-actor
        $filas = collect();

        foreach ($this->proyectos as $proyecto) {
            // Usar el array "actores" (múltiples)
            foreach ($proyecto->actores as $actor) {
                $filas->push([
                    $proyecto->codigo,
                    $proyecto->nombre,
                    $actor->nombre,
                    $actor->tipo,
                    $actor->pais_origen ?? '',
                    $proyecto->monto_formateado
                        ?? number_format(
                            (float) $proyecto->monto_total,
                            2, '.', ','
                        ) . ' ' . $proyecto->moneda,
                    $proyecto->estado,
                    $proyecto->flujo_direccion ?? '',
                ]);
            }

            // Si el proyecto no tiene actores
            // en el array, mostrar igual el proyecto
            if ($proyecto->actores->isEmpty()) {
                $filas->push([
                    $proyecto->codigo,
                    $proyecto->nombre,
                    'Sin actor asignado',
                    '',
                    '',
                    $proyecto->monto_formateado ?? '',
                    $proyecto->estado,
                    $proyecto->flujo_direccion ?? '',
                ]);
            }
        }

        return $filas;
    }

    public function headings(): array
    {
        return [
            'Código Proyecto',
            'Nombre Proyecto',
            'Actor Cooperante',
            'Tipo de Actor',
            'País de Origen',
            'Monto de Cooperación',
            'Estado del Proyecto',
            'Flujo de Cooperación',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18,
            'B' => 45,
            'C' => 45,
            'D' => 18,
            'E' => 20,
            'F' => 22,
            'G' => 16,
            'H' => 22,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold'  => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2E6DA4'],
                ],
                'alignment' => [
                    'horizontal' =>
                        Alignment::HORIZONTAL_CENTER,
                    'wrapText' => true,
                ],
            ],
        ];
    }
}
