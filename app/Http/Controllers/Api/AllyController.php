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
                    'id' => $ally->id,
                    'company_name' => $ally->company_name,
                    'company_rif' => $ally->company_rif,
                    'category_name' => $ally->category?->name,
                    'sub_category_name' => $ally->subcategory?->name,
                    'description' => $ally->description, // Campo de descripción agregado
                    'address' => $ally->address,
                    'discount' => $ally->discount,
                    'contact_phone' => $ally->contact_phone,
                    'website_url' => $ally->website_url,
                    'image_url' => $ally->image_url,
                    'recent' => $ally->recent,
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

    public function show(int $id) // <-- ¡El parámetro ahora es $user_id!
    {
        // Busca el aliado por su clave primaria, que es user_id
        $ally = Ally::with(['category', 'subcategory'])->find($id); // <-- find() usa la clave primaria definida

        if (!$ally) {
            return response()->json(['message' => 'Aliado no encontrado.'], 404);
        }

        $formattedAlly = [
            'id' => $ally->user_id, // <-- ¡Aquí es user_id!
            'company_name' => $ally->company_name,
            'company_rif' => $ally->company_rif,
            'discount' => $ally->discount,
            'category_name' => $ally->category?->name,
            'sub_category_name' => $ally->subcategory?->name,
            'image_url' => $ally->image_url,
            'rating' => $ally->rating,
            'address' => $ally->address,
            'contact_phone' => $ally->contact_phone,
            'website_url' => $ally->website_url,
            'hours_of_operation' => $ally->hours_of_operation,
            'description' => $ally->description,
            'qr_code_data' => $ally->qr_code_data,
        ];

        return response()->json(['data' => [$formattedAlly]], 200);
    }
}
