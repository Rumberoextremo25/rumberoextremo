<?php

namespace App\Http\Controllers;

use App\Models\Ally;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AllyController extends Controller
{
    /**
     * Muestra una lista de todos los aliados registrados.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $allies = Ally::with(['category', 'subcategory'])
                          ->get();

            // Formatear la respuesta para incluir todos los campos relevantes
            $formattedAllies = $allies->map(function ($ally) {
                return [
                    'id' => $ally->id,
                    'company_name' => $ally->company_name,
                    'company_rif' => $ally->company_rif,
                    'description' => $ally->description, // Nuevo campo
                    'image_url' => $ally->image_url ? asset('storage/' . $ally->image_url) : null, // Nuevo campo, usando asset()
                    'category_name' => $ally->category?->name,
                    'sub_category_name' => $ally->subcategory?->name,
                    'discount' => $ally->discount,
                    'contact_phone' => $ally->contact_phone,
                    'website_url' => $ally->website_url,
                ];
            });

            return response()->json([
                'message' => 'Aliados obtenidos correctamente :D',
                'data' => $formattedAllies
            ], 200);

        } catch (\Exception $e) {
            Log::error("Error al obtener aliados: " . $e->getMessage());

            return response()->json([
                'message' => 'Error al obtener los aliados',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Muestra la información detallada de un aliado por su ID.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id)
    {
        // Busca el aliado por su clave primaria, que es 'id'
        $ally = Ally::with(['category', 'subcategory', 'businessType'])->find($id);

        if (!$ally) {
            return response()->json(['message' => 'Aliado no encontrado.'], 404);
        }

        // Formatea la respuesta para el aliado encontrado
        $formattedAlly = [
            'id' => $ally->id,
            'company_name' => $ally->company_name,
            'company_rif' => $ally->company_rif,
            'description' => $ally->description, // Nuevo campo
            'image_url' => $ally->image_url ? asset('storage/' . $ally->image_url) : null, // Nuevo campo, usando asset()
            'category_name' => $ally->category?->name,
            'sub_category_name' => $ally->subcategory?->name,
            'business_type_name' => $ally->businessType?->name,
            'website_url' => $ally->website_url,
            'discount' => $ally->discount,
            'contact_person_name' => $ally->contact_person_name,
            'contact_email' => $ally->contact_email,
            'contact_phone' => $ally->contact_phone,
            'contact_phone_alt' => $ally->contact_phone_alt,
            'company_address' => $ally->company_address,
            'notes' => $ally->notes,
            'status' => $ally->status,
        ];

        return response()->json(['data' => $formattedAlly], 200);
    }
}