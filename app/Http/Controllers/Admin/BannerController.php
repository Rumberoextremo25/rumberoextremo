<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Promotion;
use App\Models\CommercialAlly;
use App\Models\Category;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Get all data for home screen (banners, promotions, allies)
     */
    public function index()
    {
        // ✅ CORREGIDO: Usar display_order en lugar de order
        $banners = Banner::where('is_active', true)
            ->orderBy('display_order', 'asc')
            ->get()
            ->map(function ($banner) {
                return [
                    'id' => $banner->id,
                    'title' => $banner->title,
                    'image_url' => $banner->image_url ? asset('storage/' . $banner->image_url) : null,
                    'description' => $banner->description,
                    'target_url' => $banner->target_url,
                    'display_order' => $banner->display_order,  // ← Cambiado de 'order' a 'display_order'
                ];
            });

        // Promociones destacadas
        $featuredPromotions = Promotion::where('is_featured', true)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->with('ally')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($promotion) {
                return [
                    'id' => $promotion->id,
                    'title' => $promotion->title,
                    'discount' => $promotion->discount,
                    'price' => $promotion->price,
                    'image_url' => $promotion->image_url ? asset('storage/' . $promotion->image_url) : null,
                    'ally_name' => $promotion->ally->company_name ?? null,
                    'is_featured' => $promotion->is_featured,
                ];
            });

        // Aliados comerciales
        $commercialAllies = CommercialAlly::where('is_active', true)
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($ally) {
                return [
                    'id' => $ally->id,
                    'name' => $ally->name,
                    'logo_url' => $ally->logo_url ? asset('storage/' . $ally->logo_url) : null,
                    'rating' => $ally->rating,
                    'description' => $ally->description,
                    'website_url' => $ally->website_url,
                ];
            });

        // Categorías
        $categories = Category::with('subCategories')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'icon' => $category->icon ?? null,
                    'subcategories' => $category->subCategories->map(function ($sub) {
                        return [
                            'id' => $sub->id,
                            'name' => $sub->name,
                            'slug' => $sub->slug,
                        ];
                    }),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'banners' => $banners,
                'featured_promotions' => $featuredPromotions,
                'commercial_allies' => $commercialAllies,
                'categories' => $categories,
            ],
            'message' => 'Datos cargados correctamente'
        ]);
    }

    /**
     * Get banners only (for app)
     */
    public function getBanners()
    {
        $banners = Banner::where('is_active', true)
            ->orderBy('display_order', 'asc')  // ← CORREGIDO
            ->get()
            ->map(function ($banner) {
                return [
                    'id' => $banner->id,
                    'title' => $banner->title,
                    'image_url' => $banner->image_url ? asset('storage/' . $banner->image_url) : null,
                    'description' => $banner->description,
                    'target_url' => $banner->target_url,
                    'display_order' => $banner->display_order,  // ← CORREGIDO
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $banners,
        ]);
    }
}