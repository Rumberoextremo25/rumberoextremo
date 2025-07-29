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
            // Usamos 'load' para cargar las relaciones después de obtener los aliados,
            // o 'with' para cargarlas directamente en la consulta principal (más eficiente).
            $allies = Ally::all(); // Esto trae todas las columnas
            // O si quieres seleccionar:
            // $allies = Ally::select('company_name', 'company_rif', 'category_id', 'sub_category_id', 'discount')->get();

            // Formatear la respuesta para incluir solo los campos deseados
            $formattedAllies = $allies->map(function ($ally) {
                return [
                    'company_name' => $ally->company_name,
                    'company_rif' => $ally->company_rif,
                    'category_id' => $ally->category ? $ally->category->name : null,
                    'sub_category_id' => $ally->subcategory ? $ally->subcategory->name : null,
                    'discount' => $ally->discount,
                ];
            });

            return response()->json([
                'message' => 'Aliados obtenidos correctamente',
                'data' => $formattedAllies
            ], 200);
        } catch (\Exception $e) {
            // Manejo de errores
            return response()->json([
                'message' => 'Error al obtener los aliados',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
