<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promotion; // Asegúrate de que este namespace es correcto para tu modelo Promotion
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // Importa la fachada Storage

class PromotionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $promotions = Promotion::all();
        return view('Admin.promotions.index', compact('promotions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Admin.promotions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'discount' => 'required|string|max:255',
            'price' => 'required|string|max:255',
            'expires_at' => 'nullable|date',
            'description' => 'nullable|string',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $storagePath = Storage::disk('public')->put('promotions', $request->file('image'));
            // MODIFICACIÓN CLAVE AQUÍ: Generar la URL absoluta usando asset()
            $imagePath = asset('storage/' . $storagePath);
        }

        Promotion::create([
            'title' => $request->title,
            'image_url' => $imagePath, // Ahora $imagePath ya es la URL absoluta o null
            'discount' => $request->discount,
            'price' => $request->price,
            'expires_at' => $request->expires_at,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.promotions.index')->with('success', 'Promoción creada exitosamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Promotion $promotion)
    {
        return view('Admin.promotions.edit', compact('promotion'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Promotion $promotion)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'discount' => 'required|string|max:255',
            'price' => 'required|string|max:255',
            'expires_at' => 'nullable|date',
            'description' => 'nullable|string',
        ]);

        $imagePathToSave = $promotion->image_url; // Mantener la URL existente por defecto

        if ($request->hasFile('image')) {
            // Eliminar la imagen anterior si existe
            if ($promotion->image_url) {
                // Hay que convertir la URL absoluta a una ruta relativa para Storage::disk('public')->delete
                // Si la URL es: http://tu_dominio.com/storage/promotions/imagen.jpg
                // Necesitamos extraer: promotions/imagen.jpg
                $relativePathToDelete = str_replace(Storage::url(''), '', $promotion->image_url);
                if (Storage::disk('public')->exists($relativePathToDelete)) {
                    Storage::disk('public')->delete($relativePathToDelete);
                }
            }

            // Guardar la nueva imagen
            $newStoragePath = Storage::disk('public')->put('promotions', $request->file('image'));
            
            // MODIFICACIÓN CLAVE AQUÍ: Generar la URL absoluta para guardar en la base de datos
            $imagePathToSave = asset('storage/' . $newStoragePath);
        }

        $promotion->update([
            'title' => $request->title,
            'image_url' => $imagePathToSave, // Ahora $imagePathToSave ya es la URL absoluta
            'discount' => $request->discount,
            'price' => $request->price,
            'expires_at' => $request->expires_at,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.promotions.index')->with('success', 'Promoción actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Promotion $promotion)
    {
        // Para eliminar, también necesitamos convertir la URL absoluta a una ruta relativa
        if ($promotion->image_url) {
            $relativePathToDelete = str_replace(Storage::url(''), '', $promotion->image_url);
            if (Storage::disk('public')->exists($relativePathToDelete)) {
                Storage::disk('public')->delete($relativePathToDelete);
            }
        }
        $promotion->delete();
        return redirect()->route('admin.promotions.index')->with('success', 'Promoción eliminada exitosamente.');
    }
}