<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $banners = Banner::orderBy('order')->get();
        return view('Admin.banners.index', compact('banners'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Admin.banners.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'description' => 'nullable|string',
            'target_url' => 'nullable|url',
            'order' => 'integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            // Guardar la imagen y obtener la ruta relativa
            $imagePath = $request->file('image')->store('banners', 'public');
        }

        Banner::create([
            'title' => $request->title,
            'image_url' => $imagePath, // Guarda ruta relativa: "banners/nombre-imagen.jpg"
            'description' => $request->description,
            'target_url' => $request->target_url,
            'order' => $request->order ?? 0,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('admin.banners.index')->with('success', '¡Banner creado exitosamente!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Banner $banner)
    {
        return view('Admin.banners.edit', compact('banner'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Banner $banner)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'description' => 'nullable|string',
            'target_url' => 'nullable|url',
            'order' => 'integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $imagePathToSave = $banner->image_url; // Mantener la ruta existente por defecto

        if ($request->hasFile('image')) {
            // Eliminar la imagen anterior si existe
            if ($banner->image_url) {
                Storage::disk('public')->delete($banner->image_url);
            }

            // Guardar la nueva imagen (ruta relativa)
            $imagePathToSave = $request->file('image')->store('banners', 'public');
        }

        $banner->update([
            'title' => $request->title,
            'image_url' => $imagePathToSave,
            'description' => $request->description,
            'target_url' => $request->target_url,
            'order' => $request->order,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('admin.banners.index')->with('success', '¡Banner actualizado exitosamente!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Banner $banner)
    {
        // Eliminar la imagen asociada
        if ($banner->image_url) {
            Storage::disk('public')->delete($banner->image_url);
        }
        
        $banner->delete();
        
        return redirect()->route('admin.banners.index')->with('success', 'Banner eliminado exitosamente.');
    }
}