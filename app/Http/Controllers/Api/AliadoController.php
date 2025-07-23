<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ally; // Asegúrate de tener tu modelo Aliado
use Illuminate\Http\Request;

class AliadoController extends Controller
{
    public function index()
    {
        // Carga los aliados y sus relaciones 'category', 'subCategory' y 'businessType'.
        // También puedes seleccionar solo los campos que necesitas para reducir el tamaño de la respuesta.
        $aliados = Ally::with(['category', 'subCategory', 'businessType'])
                       ->select('id', 'company_name', 'discount', 'notes', 'category_id', 'sub_category_id', 'business_type_id') // Campos específicos
                       ->get();

        return response()->json([
            'status' => 'success',
            'data' => $aliados
        ]);
    }

    public function show($id)
    {
        // Carga un aliado específico con sus relaciones.
        $aliado = Ally::with(['category', 'subCategory', 'businessType'])->find($id);

        if (!$aliado) {
            return response()->json([
                'status' => 'error',
                'message' => 'Aliado no encontrado'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $aliado
        ]);
    }
}