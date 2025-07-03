<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AllyType; // Importa tu modelo AllyType

class AllyTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = [
            'Logística / Producción',
            'Local / Venue',
            'Audio / Iluminación',
            'Alimentos y Bebidas',
            'Transporte',
            'Seguridad',
            'Medios / Publicidad',
            'Otros',
        ];

        foreach ($types as $type) {
            // Solo crea si no existe para evitar duplicados
            AllyType::firstOrCreate(['name' => $type]);
        }
    }
}
