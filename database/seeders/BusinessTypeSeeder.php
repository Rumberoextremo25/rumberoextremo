<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BusinessType; // Asegúrate de importar el modelo BusinessType

class BusinessTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define los tipos de negocio que quieres crear
        $businessTypes = [
            ['name' => 'Restaurante', 'description' => 'Establecimiento que ofrece servicio de comida.'],
            ['name' => 'Bar', 'description' => 'Local donde se sirven bebidas alcohólicas y no alcohólicas.'],
            ['name' => 'Hotel', 'description' => 'Ofrece servicios de alojamiento y otras comodidades.'],
            ['name' => 'Discoteca', 'description' => 'Local de ocio nocturno con música para bailar.'],
            ['name' => 'Salón de Eventos', 'description' => 'Espacio destinado para la celebración de reuniones y fiestas.'],
            ['name' => 'Tienda de Ropa', 'description' => 'Comercio especializado en la venta de vestimenta.'],
            ['name' => 'Peluquería / Salón de Belleza', 'description' => 'Ofrece servicios de estilismo y cuidado personal.'],
            ['name' => 'Gimnasio', 'description' => 'Instalación equipada para realizar ejercicio físico.'],
            ['name' => 'Agencia de Viajes', 'description' => 'Empresa que organiza y vende viajes y paquetes turísticos.'],
            ['name' => 'Proveedor de Servicios', 'description' => 'Empresas que ofrecen servicios (ej. sonido, iluminación, seguridad).'],
            ['name' => 'Cine / Teatro', 'description' => 'Lugares de proyección de películas o representaciones artísticas.'],
            ['name' => 'Museo / Galería de Arte', 'description' => 'Espacios para la exhibición de colecciones de arte o historia.'],
        ];

        foreach ($businessTypes as $typeData) {
            // Usa firstOrCreate para evitar duplicados si el seeder se ejecuta varias veces
            BusinessType::firstOrCreate(
                ['name' => $typeData['name']], // Busca por el nombre
                [
                    'description' => $typeData['description'],
                    // El 'slug' se generará automáticamente gracias al mutator en el modelo BusinessType
                ]
            );
        }
    }
}