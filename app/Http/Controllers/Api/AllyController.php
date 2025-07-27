<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ally;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AllyController extends Controller
{
    /**
     * Muestra una lista de todos los aliados registrados,
     * incluyendo su categoría, subcategoría, nombre y descuento.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            // Cargar los aliados con sus relaciones de categoría y subcategoría.
            // *** CAMBIO CLAVE: Agregamos whereHas('category') para filtrar ***
            // Solo se traerán aliados que tengan una categoría válida asociada.
            $allies = Ally::with(['category', 'subcategory'])
                          ->whereHas('category') // <--- ¡Esta es la línea clave para filtrar!
                          ->select('company_name', 'company_rif', 'discount', 'category_id', 'sub_category_id')
                          ->get();

            // Formatear la respuesta para incluir solo los campos deseados
            $formattedAllies = $allies->map(function ($ally) {
                return [
                    'company_name' => $ally->company_name,
                    'company_rif' => $ally->company_rif,
                    // Aquí ya sabemos que $ally->category NO será null gracias al whereHas
                    'category_id' => $ally->category->name,
                    // sub_category_id aún puede ser null si no tiene subcategoría en la DB
                    'sub_category_id' => $ally->subcategory ? $ally->subcategory->name : null,
                    'discount' => $ally->discount,
                ];
            });

            // Opcional: Puedes cambiar el mensaje si no se encuentran aliados después del filtro.
            if ($formattedAllies->isEmpty()) {
                return response()->json([
                    'message' => 'No se encontraron aliados con una categoría asociada.',
                    'data' => []
                ], 200); // Se usa 200 OK porque la solicitud fue procesada con éxito, pero el filtro no arrojó resultados.
            }

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
