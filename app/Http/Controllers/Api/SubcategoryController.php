<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subcategory; // Importa el modelo Subcategory
use App\Models\Category;    // Importa el modelo Category si lo vas a usar
use Illuminate\Http\Request;

class SubcategoryController extends Controller
{
    /**
     * Muestra una lista de todas las subcategorías.
     * Opcionalmente, filtra por category_id si se proporciona.
     * GET /api/subcategories
     * GET /api/subcategories?category_id=X
     */
    public function index(Request $request)
    {
        $query = Subcategory::query();

        // Si se proporciona un category_id en la URL (ej. ?category_id=1)
        if ($request->has('category_id')) {
            $categoryId = $request->query('category_id');
            $query->where('category_id', $categoryId);
        }

        // Carga la relación 'products' de forma "eager" para evitar problemas N+1
        // y también la relación 'category' si necesitas ver a qué categoría pertenecen.
        $subcategories = $query->with('products', 'category')->get();

        return response()->json([
            'success' => true,
            'message' => 'Subcategorías obtenidas exitosamente',
            'data' => $subcategories
        ]);
    }

    /**
     * Muestra una subcategoría específica por su ID.
     * GET /api/subcategories/{id}
     */
    public function show(Subcategory $subcategory)
    {
        // Carga las relaciones 'products' y 'category' para la subcategoría específica.
        $subcategory->load('products', 'category');

        return response()->json([
            'success' => true,
            'message' => 'Subcategoría obtenida exitosamente',
            'data' => $subcategory
        ]);
    }

    /**
     * Crea una nueva subcategoría.
     * POST /api/subcategories
     */
    public function store(Request $request)
    {
        // Valida los datos de la petición
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id', // Asegura que la categoría exista
        ]);

        $subcategory = Subcategory::create($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Subcategoría creada exitosamente',
            'data' => $subcategory
        ], 201); // Código de estado 201 Created
    }

    /**
     * Actualiza una subcategoría existente.
     * PUT/PATCH /api/subcategories/{id}
     */
    public function update(Request $request, Subcategory $subcategory)
    {
        // Valida los datos de la petición (el nombre es opcional si no se cambia)
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'category_id' => 'sometimes|required|exists:categories,id',
        ]);

        $subcategory->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Subcategoría actualizada exitosamente',
            'data' => $subcategory
        ]);
    }

    /**
     * Elimina una subcategoría.
     * DELETE /api/subcategories/{id}
     */
    public function destroy(Subcategory $subcategory)
    {
        $subcategory->delete();

        return response()->json([
            'success' => true,
            'message' => 'Subcategoría eliminada exitosamente'
        ], 204); // Código de estado 204 No Content para una eliminación exitosa
    }
}
