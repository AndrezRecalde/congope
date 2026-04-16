<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriaBeneficiarioSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            // --- GRUPOS DE ATENCIÓN PRIORITARIA Y SOCIAL ---
            ['nombre' => 'Mujeres', 'grupo' => 'Enfoque Social y Prioritario'],
            ['nombre' => 'Niñas, niños y adolescentes', 'grupo' => 'Enfoque Social y Prioritario'],
            ['nombre' => 'Personas adultas mayores', 'grupo' => 'Enfoque Social y Prioritario'],
            ['nombre' => 'Personas con discapacidad', 'grupo' => 'Enfoque Social y Prioritario'],
            ['nombre' => 'Personas en situación de movilidad humana', 'grupo' => 'Enfoque Social y Prioritario'],

            // --- PUEBLOS Y NACIONALIDADES ---
            ['nombre' => 'Comunidades Indígenas', 'grupo' => 'Pueblos y Nacionalidades'],
            ['nombre' => 'Comunidades Afroecuatorianas', 'grupo' => 'Pueblos y Nacionalidades'],
            ['nombre' => 'Pueblos Montubios', 'grupo' => 'Pueblos y Nacionalidades'],

            // --- SECTOR PRODUCTIVO Y ECONÓMICO ---
            ['nombre' => 'Agricultores / Campesinos', 'grupo' => 'Sector Productivo'],
            ['nombre' => 'Pescadores artesanales', 'grupo' => 'Sector Productivo'],
            ['nombre' => 'Ganaderos', 'grupo' => 'Sector Productivo'],
            ['nombre' => 'Artesanos', 'grupo' => 'Sector Productivo'],
            ['nombre' => 'Emprendedores / MIPYMES', 'grupo' => 'Sector Productivo'],
            ['nombre' => 'Asociaciones / Cooperativas de producción', 'grupo' => 'Sector Productivo'],

            // --- SOCIEDAD CIVIL Y COMUNITARIA ---
            ['nombre' => 'Familias / Hogares vulnerables', 'grupo' => 'Sociedad Civil'],
            ['nombre' => 'Juntas Administradoras de Agua Potable', 'grupo' => 'Sociedad Civil'],
            ['nombre' => 'Estudiantes', 'grupo' => 'Sociedad Civil'],
            ['nombre' => 'Docentes / Educadores', 'grupo' => 'Sociedad Civil'],

            // --- INSTITUCIONAL Y GUBERNAMENTAL ---
            ['nombre' => 'Servidores Públicos', 'grupo' => 'Institucional'],
            ['nombre' => 'GADs Municipales', 'grupo' => 'Institucional'],
            ['nombre' => 'GADs Parroquiales', 'grupo' => 'Institucional'],
            ['nombre' => 'Instituciones Educativas', 'grupo' => 'Institucional'],
            ['nombre' => 'Centros de Salud', 'grupo' => 'Institucional'],
        ];

        $now = now();
        foreach ($categorias as $cat) {
            DB::table('categorias_beneficiario')->insert([
                'nombre'     => $cat['nombre'],
                'grupo'      => $cat['grupo'],
                'activo'     => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
