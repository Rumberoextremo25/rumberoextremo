<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category; // Asegúrate de importar el modelo Category

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define las categorías que quieres crear
        $categories = [
            ['name' => 'Eventos', 'description' => 'Lugares y servicios relacionados con la organización y celebración de eventos.'],
            ['name' => 'Comida y Bebida', 'description' => 'Restaurantes, bares, cafeterías y servicios de catering.'],
            ['name' => 'Alojamiento', 'description' => 'Hoteles, posadas y otros tipos de hospedaje.'],
            ['name' => 'Transporte', 'description' => 'Servicios de transporte para eventos y turismo.'],
            ['name' => 'Entretenimiento', 'description' => 'Actividades recreativas, espectáculos y atracciones.'],
            ['name' => 'Belleza y Cuidado Personal', 'description' => 'Salones de belleza, spas y servicios de cuidado personal.'],
            ['name' => 'Deportes y Recreación', 'description' => 'Gimnasios, centros deportivos y actividades al aire libre.'],
            ['name' => 'Tecnología y Medios', 'description' => 'Servicios de tecnología, fotografía, video y medios.'],
            ['name' => 'Servicios Profesionales', 'description' => 'Asesoría legal, contable, marketing, etc.'],
            ['name' => 'Compras y Tiendas', 'description' => 'Tiendas de ropa, accesorios, regalos y otros productos.'],
        ];

        foreach ($categories as $categoryData) {
            // Usa firstOrCreate para evitar duplicados si el seeder se ejecuta varias veces
            Category::firstOrCreate(
                ['name' => $categoryData['name']], // Busca por el nombre
                [
                    'description' => $categoryData['description'],
                    // El 'slug' se generará automáticamente gracias al mutator en el modelo Category
                ]
            );
        }
    }
}