<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ally;
use Illuminate\Http\Request;

class AllyController extends Controller
{
    /**
     * Muestra una lista de todos los aliados registrados,
     * incluyendo su categoría, subcategoría, nombre y descuento.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            // Cargar los aliados con sus relaciones de categoría y subcategoría.
            // Es crucial usar `with()` para que Eloquent cargue los datos de las relaciones.
            // No es necesario usar `select` aquí a menos que quieras explícitamente limitar las columnas
            // de la tabla principal 'allies'. Si lo usas, asegúrate de incluir 'id' y las claves foráneas.
            $allies = Ally::with(['category'])
                          // Si usas select, debes incluir el ID de la tabla principal y las claves foráneas
                          // para que las relaciones 'with' funcionen correctamente.
                          // Si solo quieres las columnas que estás mapeando, puedes quitar el select
                          // y dejar que Eloquent las traiga todas y luego las filtras en el map.
                          ->get();

            // Formatear la respuesta para incluir solo los campos deseados
            $formattedAllies = $allies->map(function ($ally) {
                return [
                    'company_name' => $ally->company_name,
                    'company_rif' => $ally->company_rif,
                    // Acceder al nombre de la categoría usando el operador nullsafe `?->`
                    // Esto devolverá null si no hay una categoría relacionada.
                    'category_name' => $ally->category?->name, // Cambiado de category_id a category_name para reflejar lo que se muestra
                    // Acceder al nombre de la subcategoría usando el operador nullsafe `?->`
                    // Esto devolverá null si no hay una subcategoría relacionada.
                    //'sub_category_name' => $ally->subcategory?->name, // Cambiado de sub_category_id a sub_category_name
                    'discount' => $ally->discount,
                    // Si aún quieres el ID numérico de la categoría y subcategoría:
                    // 'category_id' => $ally->category_id,
                    // 'sub_category_id' => $ally->sub_category_id,
                ];
            });

            return response()->json([
                'message' => 'Aliados obtenidos correctamente',
                'data' => $formattedAllies
            ], 200);

        } catch (\Exception $e) {
            // Manejo de errores
            // Es útil registrar el error completo para depuración en un entorno de desarrollo
            // Log::error("Error al obtener aliados: " . $e->getMessage()); // Requiere use Illuminate\Support\Facades\Log;
            return response()->json([
                'message' => 'Error al obtener los aliados',
                'error' => $e->getMessage(),
                'file' => $e->getFile(), // Para depuración
                'line' => $e->getLine() // Para depuración
            ], 500);
        }
    }
}
