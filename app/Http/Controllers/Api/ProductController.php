<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subcategory; // Importa tu modelo Subcategory
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Muestra una lista de productos para una subcategoría específica.
     */
    public function getProductsBySubcategory(Subcategory $subcategory)
    {
        // Accede a los productos a través de la relación definida en el modelo Subcategory
        $products = $subcategory->products;

        return response()->json([
            'success' => true,
            'message' => 'Productos obtenidos exitosamente para la subcategoría.',
            'data' => $products
        ]);
    }
}
