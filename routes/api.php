<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SubcategoryController; // Asegúrate de importarlo
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Rutas para Subcategorías
// GET /api/subcategories
// GET /api/subcategories/{id}
// POST /api/subcategories
// PUT/PATCH /api/subcategories/{id}
// DELETE /api/subcategories/{id}
Route::middleware('auth:sanctum')->group(function () {
    // Rutas protegidas
    Route::apiResource('products', ProductController::class); // Si tu dashboard usa esto
    // ... otras rutas de gestión
});

// Rutas públicas (ej. para la app Android que solo lee)
Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::get('/{category}', [CategoryController::class, 'show']);
});
Route::get('subcategories/{subcategory}/products', [ProductController::class, 'getProductsBySubcategory']);

// Puedes mantener esta ruta si la usas para desarrollo, pero considera los seeders
Route::post('populate-db', function () {
    \Illuminate\Support\Facades\Artisan::call('migrate:fresh --seed');
    return response()->json(['success' => true, 'message' => 'Base de datos refrescada y sembrada.']);
});