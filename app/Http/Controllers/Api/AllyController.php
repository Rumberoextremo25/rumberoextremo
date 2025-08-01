<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // ¡Importa el Facade DB!
use Illuminate\Support\Facades\Log; // Para el manejo de errores, si lo deseas

class AllyController extends Controller
{
    public function index()
    {
        try {
            // Consulta SQL directamente
            $allies = DB::select("
                SELECT
                    a.company_name,
                    a.company_rif,
                    c.name AS category_name,
                    sc.name AS sub_category_name,
                    a.discount
                FROM
                    allies AS a
                LEFT JOIN
                    categories AS c ON a.category_id = c.id
                LEFT JOIN
                    subcategories AS sc ON a.sub_category_id = sc.id;
            ");

            // Los resultados de DB::select ya vienen como un array de objetos StdClass,
            // que es similar a lo que obtenías con la colección de Eloquent mapeada.
            // No necesitas el paso extra de ->map() a menos que quieras transformar algo más.
            // Si quieres que los resultados sean exactamente como los objetos de Eloquent
            // y luego mapearlos, tendrías que castearlos. Pero para este caso,
            // el resultado de DB::select es muy cercano al formato deseado.

            // Convertimos los objetos StdClass a arrays asociativos si lo prefieres,
            // o simplemente los devolvemos tal cual. Si tu frontend espera arrays asociativos,
            // esta parte es útil. Si solo necesita objetos, $allies ya es suficiente.
            $formattedAllies = array_map(function ($ally) {
                return (array) $ally;
            }, $allies);


            return response()->json([
                'message' => 'Aliados obtenidos correctamente',
                'data' => $formattedAllies
            ], 200);

        } catch (\Exception $e) {
            // Manejo de errores
            Log::error("Error al obtener aliados: " . $e->getMessage()); // Requiere use Illuminate\Support\Facades\Log;

            return response()->json([
                'message' => 'Error al obtener los aliados',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }
}