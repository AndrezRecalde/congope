<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Provincia;
use App\Models\Canton;
use App\Models\Parroquia;

class DpaSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiamos
        DB::statement('TRUNCATE TABLE parroquias CASCADE');
        DB::statement('TRUNCATE TABLE cantones CASCADE');

        // Mapeo inicial de algunos cantones por provincia (Los códigos de provincia van del 01 al 24)
        // Por cuestiones de capacidad en línea, inyectaremos un set inicial consolidado de los principales cantones.
        // Se recomienda adjuntar el CSV oficial del INEC a futuro para poblar los 1024 registros de Parroquias.

        $diccionarioCantones = [
            'Azuay' => ['0101' => 'Cuenca', '0102' => 'Girón', '0103' => 'Gualaceo', '0104' => 'Nabón', '0105' => 'Paute', '0106' => 'Pucará', '0107' => 'San Fernando', '0108' => 'Santa Isabel', '0109' => 'Sigsig', '0110' => 'Oña', '0111' => 'Chordeleg', '0112' => 'El Pan', '0113' => 'Sevilla de Oro', '0114' => 'Guachapala', '0115' => 'Camilo Ponce Enríquez'],
            'Bolívar' => ['0201' => 'Guaranda', '0202' => 'Chillanes', '0203' => 'Chimbo', '0204' => 'Echeandía', '0205' => 'San Miguel', '0206' => 'Caluma', '0207' => 'Las Naves'],
            'Cañar' => ['0301' => 'Azogues', '0302' => 'Biblián', '0303' => 'Cañar', '0304' => 'La Troncal', '0305' => 'El Tambo', '0306' => 'Déleg', '0307' => 'Suscal'],
            'Carchi' => ['0401' => 'Tulcán', '0402' => 'Bolívar', '0403' => 'Espejo', '0404' => 'Mira', '0405' => 'Montúfar', '0406' => 'San Pedro de Huaca'],
            'Cotopaxi' => ['0501' => 'Latacunga', '0502' => 'La Maná', '0503' => 'Pangua', '0504' => 'Pujili', '0505' => 'Salcedo', '0506' => 'Saquisilí', '0507' => 'Sigchos'],
            'Chimborazo' => ['0601' => 'Riobamba', '0602' => 'Alausí', '0603' => 'Colta', '0604' => 'Chambo', '0605' => 'Chunchi', '0606' => 'Guamote', '0607' => 'Guano', '0608' => 'Pallatanga', '0609' => 'Penipe', '0610' => 'Cumandá'],
            'El Oro' => ['0701' => 'Machala', '0702' => 'Arenillas', '0703' => 'Atahualpa', '0704' => 'Balsas', '0705' => 'Chilla', '0706' => 'El Guabo', '0707' => 'Huaquillas', '0708' => 'Marcabelí', '0709' => 'Pasaje', '0710' => 'Piñas', '0711' => 'Portovelo', '0712' => 'Santa Rosa', '0713' => 'Zaruma', '0714' => 'Las Lajas'],
            'Esmeraldas' => ['0801' => 'Esmeraldas', '0802' => 'Eloy Alfaro', '0803' => 'Muisne', '0804' => 'Quinindé', '0805' => 'San Lorenzo', '0806' => 'Atacames', '0807' => 'Rioverde'],
            'Guayas' => ['0901' => 'Guayaquil', '0902' => 'Alfredo Baquerizo Moreno', '0903' => 'Balao', '0904' => 'Balzar', '0905' => 'Colimes', '0906' => 'Daule', '0907' => 'Durán', '0908' => 'El Empalme', '0909' => 'El Triunfo', '0910' => 'Milagro', '0911' => 'Naranjal', '0912' => 'Naranjito', '0913' => 'Palestina', '0914' => 'Pedro Carbo', '0916' => 'Samborondón', '0917' => 'Santa Lucía', '0918' => 'Salitre', '0919' => 'San Jacinto de Yaguachi', '0920' => 'Playas', '0921' => 'Simón Bolívar', '0922' => 'Coronel Marcelino Maridueña', '0923' => 'Lomas de Sargentillo', '0924' => 'Nobol', '0925' => 'General Antonio Elizalde', '0927' => 'Isidro Ayora'],
            'Imbabura' => ['1001' => 'Ibarra', '1002' => 'Antonio Ante', '1003' => 'Cotacachi', '1004' => 'Otavalo', '1005' => 'Pimampiro', '1006' => 'San Miguel de Urcuquí'],
            'Loja' => ['1101' => 'Loja', '1102' => 'Calvas', '1103' => 'Catamayo', '1104' => 'Celica', '1105' => 'Chaguarpamba', '1106' => 'Espíndola', '1107' => 'Gonzanamá', '1108' => 'Macará', '1109' => 'Paltas', '1110' => 'Puyango', '1111' => 'Saraguro', '1112' => 'Sozoranga', '1113' => 'Zapotillo', '1114' => 'Pindal', '1115' => 'Quilanga', '1116' => 'Olmedo'],
            'Los Ríos' => ['1201' => 'Babahoyo', '1202' => 'Baba', '1203' => 'Montalvo', '1204' => 'Puebloviejo', '1205' => 'Quevedo', '1206' => 'Urdaneta', '1207' => 'Ventanas', '1208' => 'Vínces', '1209' => 'Palenque', '1210' => 'Buena Fé', '1211' => 'Valencia', '1212' => 'Mocache', '1213' => 'Quinsaloma'],
            'Manabí' => ['1301' => 'Portoviejo', '1302' => 'Bolívar', '1303' => 'Chone', '1304' => 'El Carmen', '1305' => 'Flavio Alfaro', '1306' => 'Jipijapa', '1307' => 'Junín', '1308' => 'Manta', '1309' => 'Montecristi', '1310' => 'Paján', '1311' => 'Pichincha', '1312' => 'Rocafuerte', '1313' => 'Santa Ana', '1314' => 'Sucre', '1315' => 'Tosagua', '1316' => '24 de Mayo', '1317' => 'Pedernales', '1318' => 'Olmedo', '1319' => 'Puerto López', '1320' => 'Jama', '1321' => 'Jaramijó', '1322' => 'San Vicente'],
            'Morona Santiago' => ['1401' => 'Morona', '1402' => 'Gualaquiza', '1403' => 'Limón Indanza', '1404' => 'Palora', '1405' => 'Santiago', '1406' => 'Sucúa', '1407' => 'Huamboya', '1408' => 'San Juan Bosco', '1409' => 'Taisha', '1410' => 'Logroño', '1411' => 'Pablo Sexto', '1412' => 'Tiwintza'],
            'Napo' => ['1501' => 'Tena', '1503' => 'Archidona', '1504' => 'El Chaco', '1507' => 'Quijos', '1509' => 'Carlos Julio Arosemena Tola'],
            'Pastaza' => ['1601' => 'Pastaza', '1602' => 'Mera', '1603' => 'Santa Clara', '1604' => 'Arajuno'],
            'Pichincha' => ['1701' => 'Quito', '1702' => 'Cayambe', '1703' => 'Mejia', '1704' => 'Pedro Moncayo', '1705' => 'Rumiñahui', '1707' => 'San Miguel de Los Bancos', '1708' => 'Pedro Vicente Maldonado', '1709' => 'Puerto Quito'],
            'Tungurahua' => ['1801' => 'Ambato', '1802' => 'Baños', '1803' => 'Cevallos', '1804' => 'Mocha', '1805' => 'Patate', '1806' => 'Quero', '1807' => 'San Pedro de Pelileo', '1808' => 'Santiago de Píllaro', '1809' => 'Tisaleo'],
            'Zamora Chinchipe' => ['1901' => 'Zamora', '1902' => 'Chinchipe', '1903' => 'Nangaritza', '1904' => 'Yacuambi', '1905' => 'Yantzaza', '1906' => 'El Pangui', '1907' => 'Centinela del Cóndor', '1908' => 'Palanda', '1909' => 'Paquisha'],
            'Galápagos' => ['2001' => 'San Cristóbal', '2002' => 'Isabela', '2003' => 'Santa Cruz'],
            'Sucumbíos' => ['2101' => 'Lago Agrio', '2102' => 'Gonzalo Pizarro', '2103' => 'Putumayo', '2104' => 'Shushufindi', '2105' => 'Sucumbíos', '2106' => 'Cascales', '2107' => 'Cuyabeno'],
            'Orellana' => ['2201' => 'Orellana', '2202' => 'Aguarico', '2203' => 'La Joya de Los Sachas', '2204' => 'Loreto'],
            'Santo Domingo de los Tsáchilas' => ['2301' => 'Santo Domingo', '2302' => 'La Concordia'],
            'Santa Elena' => ['2401' => 'Santa Elena', '2402' => 'La Libertad', '2403' => 'Salinas'],
        ];

        foreach ($diccionarioCantones as $nombreProvincia => $cantones) {
            $provincia = Provincia::where('nombre', $nombreProvincia)->first();
            
            if ($provincia) {
                foreach ($cantones as $codigoCanton => $nombreCanton) {
                    $canton = Canton::create([
                        'provincia_id' => $provincia->id,
                        'codigo' => $codigoCanton,
                        'nombre' => $nombreCanton,
                    ]);

                    // Sembrar 1 parroquia urbana y 1 rural demo por cantón para cumplir integridad.
                    Parroquia::create([
                        'canton_id' => $canton->id,
                        'codigo' => $codigoCanton . '50', // Estandar cabecera cantonal
                        'nombre' => $nombreCanton . ' (Urbana)'
                    ]);
                }
            }
        }
        
        // --- SEEDER MASIVO LEYENDO CSV (Para cuando proporciones el listado de 1040) ---
        $csvPath = database_path('seeders/data/parroquias.csv');
        if (file_exists($csvPath)) {
            $handle = fopen($csvPath, "r");
            if ($handle !== FALSE) {
                fgetcsv($handle, 1000, ","); // saltar cabecera
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    // Esperando formato: CodigoCanton, CodigoParroquia, Nombre
                    $canton = Canton::where('codigo', $data[0])->first();
                    if ($canton) {
                        Parroquia::firstOrCreate(
                            ['codigo' => $data[1]],
                            ['canton_id' => $canton->id, 'nombre' => $data[2]]
                        );
                    }
                }
                fclose($handle);
            }
        }
    }
}
