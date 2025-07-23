<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;    // Importa el modelo Category
use App\Models\SubCategory; // Importa el modelo SubCategory

class SubCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener IDs de categorías existentes (asegúrate de que CategorySeeder ya se haya ejecutado)
        $eventos = Category::where('name', 'Eventos')->first();
        $comidaYBebida = Category::where('name', 'Comida y Bebida')->first();
        $alojamiento = Category::where('name', 'Alojamiento')->first();
        $entretenimiento = Category::where('name', 'Entretenimiento')->first();
        $transporte = Category::where('name', 'Transporte')->first();

        // Solo procede si las categorías existen
        if ($eventos) {
            $this->createSubCategory($eventos->id, 'Salones de Fiesta', 'Espacios dedicados para celebraciones y eventos.');
            $this->createSubCategory($eventos->id, 'Decoración de Eventos', 'Servicios de diseño y montaje de ambientes.');
            $this->createSubCategory($eventos->id, 'Fotografía y Video', 'Profesionales para capturar momentos especiales.');
            $this->createSubCategory($eventos->id, 'Música en Vivo / DJ', 'Bandas, orquestas y DJs para ambientar eventos.');
            $this->createSubCategory($eventos->id, 'Alquiler de Equipos', 'Alquiler de sonido, iluminación, mobiliario, etc.');
            $this->createSubCategory($eventos->id, 'Planificadores de Eventos', 'Profesionales que organizan y coordinan eventos.');
        }

        if ($comidaYBebida) {
            $this->createSubCategory($comidaYBebida->id, 'Restaurantes', 'Establecimientos de comida con servicio de mesa.');
            $this->createSubCategory($comidaYBebida->id, 'Bares y Discotecas', 'Lugares para bebidas, música y vida nocturna.');
            $this->createSubCategory($comidaYBebida->id, 'Cafeterías', 'Establecimientos para café, postres y comidas ligeras.');
            $this->createSubCategory($comidaYBebida->id, 'Food Trucks', 'Camiones de comida con diversas especialidades.');
            $this->createSubCategory($comidaYBebida->id, 'Catering', 'Servicios de comida y bebida para eventos.');
            $this->createSubCategory($comidaYBebida->id, 'Panaderías y Pastelerías', 'Elaboración y venta de productos de panadería y repostería.');
        }

        if ($alojamiento) {
            $this->createSubCategory($alojamiento->id, 'Hoteles', 'Establecimientos de hospedaje con diversos servicios.');
            $this->createSubCategory($alojamiento->id, 'Posadas', 'Alojamientos más pequeños y con ambiente local.');
            $this->createSubCategory($alojamiento->id, 'Apartamentos Turísticos', 'Alquiler de apartamentos amueblados para estancias cortas.');
        }

        if ($entretenimiento) {
            $this->createSubCategory($entretenimiento->id, 'Cines y Teatros', 'Lugares para ver películas y obras de teatro.');
            $this->createSubCategory($entretenimiento->id, 'Parques Temáticos', 'Parques con atracciones y espectáculos.');
            $this->createSubCategory($entretenimiento->id, 'Centros de Juego', 'Casinos, salones de arcade, etc.');
            $this->createSubCategory($entretenimiento->id, 'Conciertos y Festivales', 'Organización de eventos musicales.');
        }

        if ($transporte) {
            $this->createSubCategory($transporte->id, 'Taxis y Servicios VIP', 'Servicios de transporte privado.');
            $this->createSubCategory($transporte->id, 'Alquiler de Vehículos', 'Empresas de alquiler de coches.');
            $this->createSubCategory($transporte->id, 'Transporte para Eventos', 'Autobuses o vans para grupos en eventos.');
        }

        // Puedes añadir más subcategorías para otras categorías aquí
    }

    /**
     * Helper para crear subcategorías de forma limpia.
     */
    private function createSubCategory(int $categoryId, string $name, string $description = null): void
    {
        SubCategory::firstOrCreate(
            [
                'category_id' => $categoryId,
                'name' => $name, // Busca por category_id y name para asegurar unicidad
            ],
            [
                'description' => $description,
                // El 'slug' se generará automáticamente gracias al mutator en el modelo SubCategory
            ]
        );
    }
}
