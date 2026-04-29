<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\CommercialAlly;
use App\Models\Promotion;
use App\Helpers\ApiImageHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            Log::info('===> HomeController - Iniciando carga de datos');

            // ========== BANNERS ==========
            $banners = Banner::where('is_active', true)
                ->orderBy('display_order', 'asc')
                ->get();

            Log::info('Banners encontrados: ' . $banners->count());

            $banners = $banners->map(function($banner) {
                return [
                    'id' => $banner->id,
                    'title' => $banner->title,
                    'image_url' => ApiImageHelper::getImageUrl($banner->image_url),
                    'target_url' => $banner->target_url,
                    'order' => $banner->display_order,
                ];
            });

            // ========== ALIADOS COMERCIALES ==========
            $commercialAllies = CommercialAlly::where('is_active', true)
                ->orderBy('name', 'asc')
                ->get();

            Log::info('Aliados comerciales encontrados: ' . $commercialAllies->count());

            $commercialAllies = $commercialAllies->map(function($ally) {
                return [
                    'id' => $ally->id,
                    'name' => $ally->name,
                    'logo_url' => ApiImageHelper::getImageUrl($ally->logo_url),
                    'rating' => $ally->rating,
                    'description' => $ally->description,
                    'website_url' => $ally->website_url,
                    'is_active' => $ally->is_active,
                ];
            });

            // ========== PROMOCIONES ==========
            $promotions = Promotion::with('ally')
                ->where('status', 'active')
                ->where(function($query) {
                    $query->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                })
                ->orderBy('is_featured', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info('Promociones encontradas: ' . $promotions->count());

            $promotions = $promotions->map(function($promotion) {
                return [
                    'id' => $promotion->id,
                    'title' => $promotion->title,
                    'image_url' => ApiImageHelper::getImageUrl($promotion->image_url),
                    'discount' => $promotion->discount,
                    'price' => $promotion->price,
                    'description' => $promotion->description,
                    'expires_at' => $promotion->expires_at?->format('Y-m-d'),
                    'is_featured' => $promotion->is_featured,
                    'ally_name' => $promotion->ally?->company_name ?? $promotion->ally?->name,
                    'ally_id' => $promotion->ally_id,
                ];
            });

            $response = response()->json([
                'success' => true,
                'banners' => $banners,
                'commercial_allies' => $commercialAllies,
                'promotions' => $promotions,
            ]);

            Log::info('===> HomeController - Respuesta generada exitosamente');
            
            return $response;

        } catch (\Exception $e) {
            Log::error('Error en HomeController: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los datos del home: ' . $e->getMessage()
            ], 500);
        }
    }
}
