<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\CommercialAlly;
use App\Models\Promotion;
use App\Helpers\ApiImageHelper; // 👈 IMPORTAR EL HELPER
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    /**
     * Obtiene todos los datos necesarios para la pantalla de inicio (Home):
     * Banners, Aliados Comerciales y Promociones.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            // ========== BANNERS ==========
            $banners = Banner::where('is_active', true)
                ->orderBy('order')
                ->get()
                ->map(function($banner) {
                    return [
                        'id' => $banner->id,
                        'title' => $banner->title,
                        'image_url' => ApiImageHelper::getImageUrl($banner->image_url),
                        'target_url' => $banner->target_url,
                        'order' => $banner->order,
                    ];
                });

            // ========== ALIADOS COMERCIALES ==========
            $commercialAllies = CommercialAlly::where('is_active', true)
                ->get()
                ->map(function($ally) {
                    return [
                        'id' => $ally->id,
                        'name' => $ally->name,
                        'logo_url' => ApiImageHelper::getImageUrl($ally->logo_url), // ✅ URL completa
                        'rating' => $ally->rating,
                        'description' => $ally->description,
                        'website_url' => $ally->website_url,
                        'is_active' => $ally->is_active,
                    ];
                });

            // ========== PROMOCIONES ==========
            $promotions = Promotion::with('ally') // Cargar relación con aliado
                ->where('status', 'active')
                ->get()
                ->map(function($promotion) {
                    return [
                        'id' => $promotion->id,
                        'title' => $promotion->title,
                        'image_url' => ApiImageHelper::getImageUrl($promotion->image_url), // ✅ URL completa
                        'discount' => $promotion->discount,
                        'price' => $promotion->price,
                        'description' => $promotion->description,
                        'expires_at' => $promotion->expires_at?->format('Y-m-d'),
                        'ally_name' => $promotion->ally?->company_name ?? $promotion->ally?->name,
                        'ally_id' => $promotion->ally_id,
                    ];
                });

            // Devolver los datos procesados
            return response()->json([
                'success' => true,
                'banners' => $banners,
                'commercial_allies' => $commercialAllies,
                'promotions' => $promotions,
            ]);

        } catch (\Exception $e) {
            Log::error('Error en HomeController: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los datos del home'
            ], 500);
        }
    }
}
