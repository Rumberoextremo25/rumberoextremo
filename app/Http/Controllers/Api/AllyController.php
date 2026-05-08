<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ally;
use App\Helpers\ApiImageHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class AllyController extends Controller
{
    /**
     * Obtener todos los aliados
     */
    public function index(): JsonResponse
    {
        try {
            $allies = Ally::with(['category', 'subcategory'])
                ->where('status', 'activo') // Solo aliados activos
                ->get();

            $formattedAllies = $allies->map(function ($ally) {
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
                    'image_url' => ApiImageHelper::getImageUrl($ally->image_url),
                    'product_images' => ApiImageHelper::getProductImages($ally->product_images),
                    'rating' => $ally->rating ?? 0,
                    'recent' => (bool) $ally->recent,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Aliados obtenidos correctamente',
                'data' => $formattedAllies
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error en index de aliados: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los aliados',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener un aliado específico
     */
    public function show(int $id): JsonResponse
    {
        try {
            $ally = Ally::with(['category', 'subcategory', 'businessType'])
                ->where('status', 'activo')
                ->find($id);

            if (!$ally) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aliado no encontrado'
                ], 404);
            }

            $formattedAlly = [
                'id' => $ally->id,
                'user_id' => $ally->user_id,
                'company_name' => $ally->company_name,
                'company_rif' => $ally->company_rif,
                'discount' => $ally->discount,
                'category_name' => $ally->category?->name,
                'sub_category_name' => $ally->subcategory?->name,
                'business_type' => $ally->businessType?->name,
                'image_url' => ApiImageHelper::getImageUrl($ally->image_url),
                'product_images' => ApiImageHelper::getProductImages($ally->product_images),
                'rating' => $ally->rating ?? 0,
                'address' => $ally->address,
                'company_address' => $ally->company_address,
                'contact_person_name' => $ally->contact_person_name,
                'contact_email' => $ally->contact_email,
                'contact_phone' => $ally->contact_phone,
                'contact_phone_alt' => $ally->contact_phone_alt,
                'website_url' => $ally->website_url,
                'hours_of_operation' => $ally->hours_of_operation,
                'description' => $ally->description,
                'qr_code_data' => $ally->qr_code_data,
                'status' => $ally->status,
                'registered_at' => $ally->registered_at ? $ally->registered_at->format('Y-m-d') : null,
                'notes' => $ally->notes,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Aliado obtenido correctamente',
                'data' => $formattedAlly
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error en show de aliado: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el aliado',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener aliados por categoría
     */
    public function getByCategory(string $categoryName): JsonResponse
    {
        try {
            $allies = Ally::with(['category', 'subcategory'])
                ->where('status', 'activo')
                ->whereHas('category', function ($query) use ($categoryName) {
                    $query->where('name', 'LIKE', "%{$categoryName}%");
                })
                ->get();

            $formattedAllies = $allies->map(function ($ally) {
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
                    'image_url' => ApiImageHelper::getImageUrl($ally->image_url),
                    'product_images' => ApiImageHelper::getProductImages($ally->product_images),
                    'rating' => $ally->rating ?? 0,
                    'recent' => (bool) $ally->recent,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Aliados por categoría obtenidos correctamente',
                'data' => $formattedAllies
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error en getByCategory: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener aliados por categoría',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener aliados recientes/destacados
     */
    public function getRecent(): JsonResponse
    {
        try {
            $allies = Ally::with(['category', 'subcategory'])
                ->where('status', 'activo')
                ->where(function($query) {
                    $query->where('recent', true)
                          ->orWhere('created_at', '>=', now()->subDays(30));
                })
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            $formattedAllies = $allies->map(function ($ally) {
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
                    'image_url' => ApiImageHelper::getImageUrl($ally->image_url),
                    'product_images' => ApiImageHelper::getProductImages($ally->product_images),
                    'rating' => $ally->rating ?? 0,
                    'recent' => (bool) $ally->recent,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Aliados recientes obtenidos correctamente',
                'data' => $formattedAllies
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error en getRecent: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener aliados recientes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar aliados por término
     */
    public function search(string $term): JsonResponse
    {
        try {
            $allies = Ally::with(['category', 'subcategory'])
                ->where('status', 'activo')
                ->where(function($query) use ($term) {
                    $query->where('company_name', 'LIKE', "%{$term}%")
                          ->orWhere('description', 'LIKE', "%{$term}%")
                          ->orWhere('address', 'LIKE', "%{$term}%")
                          ->orWhereHas('category', function($q) use ($term) {
                              $q->where('name', 'LIKE', "%{$term}%");
                          });
                })
                ->get();

            $formattedAllies = $allies->map(function ($ally) {
                return [
                    'id' => $ally->id,
                    'company_name' => $ally->company_name,
                    'category_name' => $ally->category?->name,
                    'description' => $ally->description,
                    'address' => $ally->address,
                    'discount' => $ally->discount,
                    'image_url' => ApiImageHelper::getImageUrl($ally->image_url),
                    'rating' => $ally->rating ?? 0,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Búsqueda completada',
                'data' => $formattedAllies
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error en search: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al buscar aliados',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
