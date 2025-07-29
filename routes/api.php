<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\AllyController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\AuthController;

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

// Puedes mantener esta ruta si la usas para desarrollo, pero considera los seeders
Route::post('populate-db', function () {
    \Illuminate\Support\Facades\Artisan::call('migrate:fresh --seed');
    return response()->json(['success' => true, 'message' => 'Base de datos refrescada y sembrada.']);
});

// Ruta para obtener todos los aliados (GET /api/aliados)
Route::get('/aliados', [AllyController::class, 'index']);

//Rutas para el HomeFragment de la Aplicación
Route::get('home-data', [HomeController::class, 'index']);

Route::get('/test-route', function () {
    return 'Test successful!';
});

// Rutas públicas (no requieren autenticación)
Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas por Sanctum (requieren un token de acceso válido)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
});
