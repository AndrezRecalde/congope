<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Provincia;

class ProvinciasSeeder extends Seeder
{
    public function run(): void
    {
        $provincias = [
            ['codigo' => '01', 'nombre' => 'Azuay', 'capital' => 'Cuenca'],
            ['codigo' => '02', 'nombre' => 'Bolívar', 'capital' => 'Guaranda'],
            ['codigo' => '03', 'nombre' => 'Cañar', 'capital' => 'Azogues'],
            ['codigo' => '04', 'nombre' => 'Carchi', 'capital' => 'Tulcán'],
            ['codigo' => '05', 'nombre' => 'Cotopaxi', 'capital' => 'Latacunga'],
            ['codigo' => '06', 'nombre' => 'Chimborazo', 'capital' => 'Riobamba'],
            ['codigo' => '07', 'nombre' => 'El Oro', 'capital' => 'Machala'],
            ['codigo' => '08', 'nombre' => 'Esmeraldas', 'capital' => 'Esmeraldas'],
            ['codigo' => '09', 'nombre' => 'Guayas', 'capital' => 'Guayaquil'],
            ['codigo' => '10', 'nombre' => 'Imbabura', 'capital' => 'Ibarra'],
            ['codigo' => '11', 'nombre' => 'Loja', 'capital' => 'Loja'],
            ['codigo' => '12', 'nombre' => 'Los Ríos', 'capital' => 'Babahoyo'],
            ['codigo' => '13', 'nombre' => 'Manabí', 'capital' => 'Portoviejo'],
            ['codigo' => '14', 'nombre' => 'Morona Santiago', 'capital' => 'Macas'],
            ['codigo' => '15', 'nombre' => 'Napo', 'capital' => 'Tena'],
            ['codigo' => '16', 'nombre' => 'Pastaza', 'capital' => 'Puyo'],
            ['codigo' => '17', 'nombre' => 'Pichincha', 'capital' => 'Quito'],
            ['codigo' => '18', 'nombre' => 'Sucumbíos', 'capital' => 'Nueva Loja'],
            ['codigo' => '19', 'nombre' => 'Tungurahua', 'capital' => 'Ambato'],
            ['codigo' => '20', 'nombre' => 'Galápagos', 'capital' => 'Puerto Baquerizo Moreno'],
            ['codigo' => '21', 'nombre' => 'Orellana', 'capital' => 'Puerto Francisco de Orellana'],
            ['codigo' => '22', 'nombre' => 'Zamora Chinchipe', 'capital' => 'Zamora'],
            ['codigo' => '23', 'nombre' => 'Santo Domingo de los Tsáchilas', 'capital' => 'Santo Domingo'],
            ['codigo' => '24', 'nombre' => 'Santa Elena', 'capital' => 'Santa Elena'],
        ];

        foreach ($provincias as $p) {
            Provincia::firstOrCreate(
                ['codigo' => $p['codigo']],
                [
                    'nombre' => $p['nombre'],
                    'capital' => $p['capital'],
                ]
            );
        }
    }
}
