<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PromotionController extends Controller
{
    public function index()
    {
        $promotions = Promotion::all();
        return view('Admin.promotions.index', compact('promotions'));
    }

    public function create()
    {
        return view('Admin.promotions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'discount' => 'required|numeric|min:0|max:100',
            'price' => 'required|numeric|min:0',
            'expires_at' => 'nullable|date|after_or_equal:today',
            'description' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'max_uses' => 'nullable|integer|min:0',
            'is_featured' => 'sometimes|boolean', // ← Para destacado
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('promotions', 'public');
        }

        Promotion::create([
            'ally_id' => 1, // Asumiendo un aliado por defecto, ajusta según tu lógica
            'title' => $request->title,
            'image_url' => $imagePath,
            'discount' => $request->discount,
            'price' => $request->price,
            'description' => $request->description,
            'terms_conditions' => $request->terms_conditions,
            'expires_at' => $request->expires_at,
            'max_uses' => $request->max_uses,
            'current_uses' => 0,
            'status' => $request->has('is_active') ? 'active' : 'inactive', // ← Usando status
            'is_featured' => $request->has('is_featured') ? true : false, // ← Destacado
        ]);

        return redirect()->route('admin.promotions.index')->with('success', '¡Promoción rumbera creada exitosamente!');
    }

    public function edit(Promotion $promotion)
    {
        return view('Admin.promotions.edit', compact('promotion'));
    }

    public function update(Request $request, Promotion $promotion)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'discount' => 'required|numeric|min:0|max:100',
            'price' => 'required|numeric|min:0',
            'expires_at' => 'nullable|date',
            'description' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'max_uses' => 'nullable|integer|min:0',
            'is_featured' => 'sometimes|boolean',
        ]);

        $imagePathToSave = $promotion->image_url;

        if ($request->hasFile('image')) {
            if ($promotion->image_url) {
                Storage::disk('public')->delete($promotion->image_url);
            }
            $imagePathToSave = $request->file('image')->store('promotions', 'public');
        }

        $promotion->update([
            'title' => $request->title,
            'image_url' => $imagePathToSave,
            'discount' => $request->discount,
            'price' => $request->price,
            'description' => $request->description,
            'terms_conditions' => $request->terms_conditions,
            'expires_at' => $request->expires_at,
            'max_uses' => $request->max_uses,
            'status' => $request->has('is_active') ? 'active' : 'inactive', // ← Usando status
            'is_featured' => $request->has('is_featured') ? true : false, // ← Destacado
        ]);

        return redirect()->route('admin.promotions.index')->with('success', '¡Promoción rumbera actualizada exitosamente!');
    }

    public function destroy(Promotion $promotion)
    {
        if ($promotion->image_url) {
            Storage::disk('public')->delete($promotion->image_url);
        }
        $promotion->delete();
        return redirect()->route('admin.promotions.index')->with('success', 'Promoción eliminada exitosamente.');
    }
}