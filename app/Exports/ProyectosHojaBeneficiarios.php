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

class ProyectosHojaBeneficiarios implements
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
        return 'Beneficiarios';
    }

    public function collection(): Collection
    {
        // Una fila por cada registro de beneficiarios
        // (puede haber varios por proyecto y provincia
        // según la categoría de beneficiario)
        $filas = collect();

        foreach ($this->proyectos as $proyecto) {
            if ($proyecto->beneficiarios->isEmpty()) {
                // Proyecto sin beneficiarios registrados
                $filas->push([
                    $proyecto->codigo,
                    $proyecto->nombre,
                    $proyecto->estado,
                    'Sin datos',
                    '',
                    '',
                    0,
                    0,
                    '',
                ]);
                continue;
            }

            foreach ($proyecto->beneficiarios
                as $beneficiario) {
                $filas->push([
                    $proyecto->codigo,
                    $proyecto->nombre,
                    $proyecto->estado,
                    $beneficiario->provincia?->nombre
                        ?? $beneficiario->provincia_nombre
                        ?? '',
                    $beneficiario->categoria?->nombre
                        ?? $beneficiario->categoria_nombre
                        ?? '',
                    $beneficiario->categoria?->grupo
                        ?? $beneficiario->categoria_grupo
                        ?? '',
                    $beneficiario->cantidad_directos
                        ?? 0,
                    $beneficiario->cantidad_indirectos
                        ?? 0,
                    $beneficiario->observaciones ?? '',
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
            'Estado',
            'Provincia',
            'Categoría de Beneficiario',
            'Grupo',
            'Directos',
            'Indirectos',
            'Observaciones',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18,
            'B' => 45,
            'C' => 15,
            'D' => 20,
            'E' => 38,
            'F' => 28,
            'G' => 12,
            'H' => 14,
            'I' => 35,
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
                    'startColor' => ['rgb' => 'E8A020'],
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
