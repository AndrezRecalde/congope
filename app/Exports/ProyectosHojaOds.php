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

class ProyectosHojaOds implements
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
        return 'ODS';
    }

    public function collection(): Collection
    {
        // Una fila por cada relación proyecto-ODS
        $filas = collect();

        foreach ($this->proyectos as $proyecto) {
            foreach ($proyecto->ods as $ods) {
                $filas->push([
                    $proyecto->codigo,
                    $proyecto->nombre,
                    $proyecto->estado,
                    "ODS {$ods->numero}",
                    $ods->nombre,
                    // Grupo del ODS (1-5=Personas,
                    // 6-12=Prosperidad, etc.)
                    $this->grupoOds($ods->numero),
                    $proyecto->sector_tematico,
                    $proyecto->monto_formateado
                        ?? number_format(
                            (float) $proyecto->monto_total,
                            2, '.', ','
                        ) . ' ' . $proyecto->moneda,
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
            'ODS',
            'Nombre ODS',
            'Grupo ODS',
            'Sector Temático',
            'Monto de Cooperación',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18,
            'B' => 45,
            'C' => 16,
            'D' => 10,
            'E' => 30,
            'F' => 20,
            'G' => 25,
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
                    'startColor' => ['rgb' => '19486A'],
                ],
                'alignment' => [
                    'horizontal' =>
                        Alignment::HORIZONTAL_CENTER,
                    'wrapText' => true,
                ],
            ],
        ];
    }

    /**
     * Clasifica el ODS en uno de los 5 grupos
     * oficiales de la Agenda 2030 de la ONU.
     */
    private function grupoOds(int $numero): string
    {
        return match (true) {
            $numero <= 5  => 'Personas',
            $numero <= 9  => 'Prosperidad',
            $numero <= 12 => 'Planeta',
            $numero <= 16 => 'Paz',
            default       => 'Alianzas',
        };
    }
}
