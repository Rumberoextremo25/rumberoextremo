<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category; // Asegúrate de importar tu modelo Category
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Muestra una lista de todas las categorías con sus subcategorías y productos.
     */
    public function index()
    {
        // El método 'with()' carga las relaciones ('subcategories' y dentro de ellas 'products')
        // Esto es clave para evitar el problema N+1 y cargar todo de una vez.
        $categories = Category::with('subcategories.products')->get();

        return response()->json([
            'success' => true,
            'message' => 'Categorías obtenidas exitosamente',
            'data' => $categories // Laravel automáticamente serializa esto a JSON
        ]);
    }

    /**
     * Muestra una categoría específica con sus subcategorías y productos.
     */
    public function show(Category $category)
    {
        // Carga las relaciones solo para la categoría encontrada
        $category->load('subcategories.products');

        return response()->json([
            'success' => true,
            'message' => 'Categoría obtenida exitosamente',
            'data' => $category
        ]);
    }
}
