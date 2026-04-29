<?php
// app/Http/Controllers/Admin/PromotionController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PromotionController extends Controller
{
    /**
     * Muestra la lista de promociones.
     */
    public function index()
    {
        // ✅ CORREGIDO: Usar orderBy en lugar de latest() para evitar problemas
        $promotions = Promotion::with('ally')->orderBy('created_at', 'desc')->get();
        return view('Admin.promotions.index', compact('promotions'));
    }

    /**
     * Muestra el formulario para crear una nueva promoción.
     */
    public function create()
    {
        return view('Admin.promotions.create');
    }

    /**
     * Almacena una nueva promoción en la base de datos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'discount' => 'required|string|max:50',  // ✅ CORREGIDO: string en lugar de numeric (permite "%", "2x1", etc.)
            'price' => 'required|string|max:50',     // ✅ CORREGIDO: string en lugar de numeric
            'expires_at' => 'nullable|date|after_or_equal:today',
            'description' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'max_uses' => 'nullable|integer|min:0',
            'is_featured' => 'sometimes|boolean',
        ], [
            'title.required' => 'El título de la promoción es obligatorio.',
            'image.required' => 'La imagen de la promoción es obligatoria.',
            'image.image' => 'El archivo debe ser una imagen.',
            'image.mimes' => 'La imagen debe ser de tipo: jpeg, png, jpg, gif, svg.',
            'image.max' => 'La imagen no debe pesar más de 2MB.',
            'discount.required' => 'El descuento es obligatorio.',
            'price.required' => 'El precio es obligatorio.',
            'expires_at.after_or_equal' => 'La fecha de expiración debe ser hoy o una fecha futura.',
        ]);

        // Procesar la imagen
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('promotions', 'public');
        }

        // ✅ Crear la promoción
        Promotion::create([
            'ally_id' => null,
            'title' => $request->title,
            'image_url' => $imagePath,
            'discount' => $request->discount,
            'price' => $request->price,
            'description' => $request->description,
            'terms_conditions' => $request->terms_conditions,
            'expires_at' => $request->expires_at,
            'max_uses' => $request->max_uses ?? null,  // ✅ CORREGIDO: null en lugar de 0
            'current_uses' => 0,
            'status' => $request->has('is_active') ? 'active' : 'inactive',
            'is_featured' => $request->has('is_featured'),
        ]);

        return redirect()
            ->route('admin.promotions.index')
            ->with('success', '¡Promoción creada exitosamente!');
    }

    /**
     * Muestra el formulario para editar una promoción.
     */
    public function edit(Promotion $promotion)
    {
        return view('Admin.promotions.edit', compact('promotion'));
    }

    /**
     * Actualiza una promoción existente.
     */
    public function update(Request $request, Promotion $promotion)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'discount' => 'required|string|max:50',  // ✅ CORREGIDO: string
            'price' => 'required|string|max:50',     // ✅ CORREGIDO: string
            'expires_at' => 'nullable|date|after_or_equal:today',
            'description' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'max_uses' => 'nullable|integer|min:0',
            'is_featured' => 'sometimes|boolean',
        ]);

        // Procesar la nueva imagen si se subió
        $imagePathToSave = $promotion->image_url;
        if ($request->hasFile('image')) {
            if ($promotion->image_url && Storage::disk('public')->exists($promotion->image_url)) {
                Storage::disk('public')->delete($promotion->image_url);
            }
            $imagePathToSave = $request->file('image')->store('promotions', 'public');
        }

        // ✅ Actualizar la promoción
        $promotion->update([
            'title' => $request->title,
            'image_url' => $imagePathToSave,
            'discount' => $request->discount,
            'price' => $request->price,
            'description' => $request->description,
            'terms_conditions' => $request->terms_conditions,
            'expires_at' => $request->expires_at,
            'max_uses' => $request->max_uses ?? null,  // ✅ CORREGIDO: null en lugar de 0
            'status' => $request->has('is_active') ? 'active' : 'inactive',
            'is_featured' => $request->has('is_featured'),
        ]);

        return redirect()
            ->route('admin.promotions.index')
            ->with('success', '¡Promoción actualizada exitosamente!');
    }

    /**
     * Elimina una promoción.
     */
    public function destroy(Promotion $promotion)
    {
        // ✅ Verificar que el archivo existe antes de eliminar
        if ($promotion->image_url && Storage::disk('public')->exists($promotion->image_url)) {
            Storage::disk('public')->delete($promotion->image_url);
        }
        
        $promotion->delete();

        return redirect()
            ->route('admin.promotions.index')
            ->with('success', 'Promoción eliminada exitosamente.');
    }
}