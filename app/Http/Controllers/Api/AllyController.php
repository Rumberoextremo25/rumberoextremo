<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ally;
use Illuminate\Support\Facades\Storage;
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
                // Decodificar las imágenes de productos si existen
                $productImages = [];
                if ($ally->product_images) {
                    $decodedImages = json_decode($ally->product_images, true);
                    if (is_array($decodedImages)) {
                        // Convertir rutas a URLs completas
                        $productImages = array_map(function($path) {
                            return Storage::url($path);
                        }, $decodedImages);
                    }
                }

                return [
                    'id' => $ally->id,
                    'company_name' => $ally->company_name,
                    'company_rif' => $ally->company_rif,
                    'category_name' => $ally->category?->name,
                    'sub_category_name' => $ally->subcategory?->name,
                    'description' => $ally->description,
                    'address' => $ally->address,
                    'discount' => $ally->discount,
                    'contact_phone' => $ally->contact_phone,
                    'website_url' => $ally->website_url,
                    'image_url' => $ally->image_url ? Storage::url($ally->image_url) : null,
                    'product_images' => $productImages, // NUEVO CAMPO
                    'recent' => $ally->recent,
                ];
            });

            return response()->json([
                'message' => 'Aliados obtenidos correctamente :D',
                'data' => $formattedAllies
            ], 200);
        } catch (\Exception $e) {
            // Manejo de errores
            return response()->json([
                'message' => 'Error al obtener los aliados',
                'error' => $e->getMessage(),
                'file' => $e->getFile(), // Para depuración
                'line' => $e->getLine() // Para depuración
            ], 500);
        }
    }

    public function show(int $id)
    {
        // Busca el aliado por su clave primaria
        $ally = Ally::with(['category', 'subcategory'])->find($id);

        if (!$ally) {
            return response()->json(['message' => 'Aliado no encontrado.'], 404);
        }

        // Decodificar las imágenes de productos si existen
        $productImages = [];
        if ($ally->product_images) {
            $decodedImages = json_decode($ally->product_images, true);
            if (is_array($decodedImages)) {
                // Convertir rutas a URLs completas
                $productImages = array_map(function($path) {
                    return Storage::url($path);
                }, $decodedImages);
            }
        }

        $formattedAlly = [
            'id' => $ally->id,
            'user_id' => $ally->user_id,
            'company_name' => $ally->company_name,
            'company_rif' => $ally->company_rif,
            'discount' => $ally->discount,
            'category_name' => $ally->category?->name,
            'sub_category_name' => $ally->subcategory?->name,
            'image_url' => $ally->image_url ? Storage::url($ally->image_url) : null,
            'product_images' => $productImages, // NUEVO CAMPO
            'rating' => $ally->rating,
            'address' => $ally->address,
            'contact_phone' => $ally->contact_phone,
            'website_url' => $ally->website_url,
            'hours_of_operation' => $ally->hours_of_operation,
            'description' => $ally->description,
            'qr_code_data' => $ally->qr_code_data,
            'status' => $ally->status,
            'registered_at' => $ally->registered_at,
            'notes' => $ally->notes,
            'business_type' => $ally->businessType?->name,
            'contact_person_name' => $ally->contact_person_name,
            'contact_email' => $ally->contact_email,
            'contact_phone_alt' => $ally->contact_phone_alt,
            'company_address' => $ally->company_address,
        ];

        return response()->json([
            'message' => 'Aliado obtenido correctamente',
            'data' => $formattedAlly
        ], 200);
    }

    /**
     * Obtiene los aliados por categoría específica
     * 
     * @param string $categoryName
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByCategory($categoryName)
    {
        try {
            $allies = Ally::with(['category', 'subcategory'])
                ->whereHas('category', function ($query) use ($categoryName) {
                    $query->where('name', 'LIKE', "%{$categoryName}%");
                })
                ->get();

            $formattedAllies = $allies->map(function ($ally) {
                // Decodificar las imágenes de productos si existen
                $productImages = [];
                if ($ally->product_images) {
                    $decodedImages = json_decode($ally->product_images, true);
                    if (is_array($decodedImages)) {
                        $productImages = array_map(function($path) {
                            return Storage::url($path);
                        }, $decodedImages);
                    }
                }

                return [
                    'id' => $ally->id,
                    'company_name' => $ally->company_name,
                    'company_rif' => $ally->company_rif,
                    'category_name' => $ally->category?->name,
                    'sub_category_name' => $ally->subcategory?->name,
                    'description' => $ally->description,
                    'address' => $ally->address,
                    'discount' => $ally->discount,
                    'contact_phone' => $ally->contact_phone,
                    'website_url' => $ally->website_url,
                    'image_url' => $ally->image_url ? Storage::url($ally->image_url) : null,
                    'product_images' => $productImages, // NUEVO CAMPO
                    'recent' => $ally->recent,
                ];
            });

            return response()->json([
                'message' => 'Aliados por categoría obtenidos correctamente',
                'data' => $formattedAllies
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener aliados por categoría',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene aliados recientes o destacados
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRecent()
    {
        try {
            $allies = Ally::with(['category', 'subcategory'])
                ->where('recent', true)
                ->orWhere('status', 'activo')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            $formattedAllies = $allies->map(function ($ally) {
                // Decodificar las imágenes de productos si existen
                $productImages = [];
                if ($ally->product_images) {
                    $decodedImages = json_decode($ally->product_images, true);
                    if (is_array($decodedImages)) {
                        $productImages = array_map(function($path) {
                            return Storage::url($path);
                        }, $decodedImages);
                    }
                }

                return [
                    'id' => $ally->id,
                    'company_name' => $ally->company_name,
                    'company_rif' => $ally->company_rif,
                    'category_name' => $ally->category?->name,
                    'sub_category_name' => $ally->subcategory?->name,
                    'description' => $ally->description,
                    'address' => $ally->address,
                    'discount' => $ally->discount,
                    'contact_phone' => $ally->contact_phone,
                    'website_url' => $ally->website_url,
                    'image_url' => $ally->image_url ? Storage::url($ally->image_url) : null,
                    'product_images' => $productImages, // NUEVO CAMPO
                    'recent' => $ally->recent,
                ];
            });

            return response()->json([
                'message' => 'Aliados recientes obtenidos correctamente',
                'data' => $formattedAllies
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener aliados recientes',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
