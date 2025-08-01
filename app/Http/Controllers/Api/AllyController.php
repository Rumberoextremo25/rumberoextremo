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
            $allies = Ally::with(['category', 'subcategory'])
                          ->get();

            // Formatear la respuesta para incluir solo los campos deseados
            $formattedAllies = $allies->map(function ($ally) {
                return [
                    'company_name' => $ally->company_name,
                    'company_rif' => $ally->company_rif,
                    'category_name' => $ally->category?->name, // Cambiado de category_id a category_name para reflejar lo que se muestra
                    'sub_category_name' => $ally->subcategory?->name, // Cambiado de sub_category_id a sub_category_name
                    'discount' => $ally->discount,
                ];
            });

            return response()->json([
                'message' => 'Aliados obtenidos correctamente :D',
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
