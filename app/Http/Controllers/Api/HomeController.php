<?php

// app/Http/Controllers/Api/HomeController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\CommercialAlly;
use App\Models\Promotion;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Obtiene todos los datos necesarios para la pantalla de inicio (Home):
     * Banners, Aliados Comerciales y Promociones.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Obtener todos los banners activos, ordenados
        $banners = Banner::where('is_active', true)
                         ->orderBy('order')
                         ->get();

        // Obtener todos los aliados comerciales
        $commercialAllies = CommercialAlly::all();

        // Obtener todas las promociones
        $promotions = Promotion::all();

        // Devolver los datos en una única respuesta JSON
        return response()->json([
            'banners' => $banners,
            'commercial_allies' => $commercialAllies, // Usamos snake_case para JSON
            'promotions' => $promotions,
        ]);
    }

    // --- Métodos de creación/actualización/eliminación (Opcional) ---
    // Si necesitas que este mismo controlador maneje CUD, puedes añadir métodos como:
    /*
    public function storeBanner(Request $request) { /* ... lógica para guardar un banner ... */ /* }
    public function updateBanner(Request $request, Banner $banner) { /* ... lógica para actualizar un banner ... */ /* }
    // ... y así para CommercialAlly y Promotion.
    // Sin embargo, para CUD, a menudo es más RESTful y claro tener controladores separados
    // o un controlador para cada recurso específico (como BannerController, etc.)
    // Este HomeController está optimizado para la lectura combinada.
    */
}
