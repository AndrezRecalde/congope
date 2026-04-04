<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ods;

class OdsSeeder extends Seeder
{
    public function run(): void
    {
        $ods = [
            ['numero' => 1, 'nombre' => 'Fin pobreza', 'color_hex' => '#E5243B'],
            ['numero' => 2, 'nombre' => 'Hambre cero', 'color_hex' => '#DDA63A'],
            ['numero' => 3, 'nombre' => 'Salud', 'color_hex' => '#4C9F38'],
            ['numero' => 4, 'nombre' => 'Educación', 'color_hex' => '#C5192D'],
            ['numero' => 5, 'nombre' => 'Igualdad género', 'color_hex' => '#FF3A21'],
            ['numero' => 6, 'nombre' => 'Agua', 'color_hex' => '#26BDE2'],
            ['numero' => 7, 'nombre' => 'Energía', 'color_hex' => '#FCC30B'],
            ['numero' => 8, 'nombre' => 'Trabajo', 'color_hex' => '#A21942'],
            ['numero' => 9, 'nombre' => 'Industria', 'color_hex' => '#FD6925'],
            ['numero' => 10, 'nombre' => 'Desigualdades', 'color_hex' => '#DD1367'],
            ['numero' => 11, 'nombre' => 'Ciudades', 'color_hex' => '#FD9D24'],
            ['numero' => 12, 'nombre' => 'Consumo', 'color_hex' => '#BF8B2E'],
            ['numero' => 13, 'nombre' => 'Clima', 'color_hex' => '#3F7E44'],
            ['numero' => 14, 'nombre' => 'Vida marina', 'color_hex' => '#0A97D9'],
            ['numero' => 15, 'nombre' => 'Ecosistemas', 'color_hex' => '#56C02B'],
            ['numero' => 16, 'nombre' => 'Paz', 'color_hex' => '#00689D'],
            ['numero' => 17, 'nombre' => 'Alianzas', 'color_hex' => '#19486A'],
        ];

        foreach ($ods as $item) {
            Ods::firstOrCreate(
                ['numero' => $item['numero']],
                [
                    'id' => $item['numero'],
                    'nombre' => $item['nombre'],
                    'color_hex' => $item['color_hex'],
                ]
            );
        }
    }
}
