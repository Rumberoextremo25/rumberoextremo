<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner; // Asegúrate de que este namespace es correcto
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // Importa la fachada Storage

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
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validación para el archivo de imagen
            'description' => 'nullable|string',
            'target_url' => 'nullable|url',
            'order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $storagePath = Storage::disk('public')->put('banners', $request->file('image'));
            // MODIFICACIÓN CLAVE AQUÍ: Generar la URL absoluta usando asset()
            $imagePath = asset('storage/' . $storagePath);
        }

        Banner::create([
            'title' => $request->title,
            'image_url' => $imagePath, // Ahora $imagePath ya es la URL absoluta o null
            'description' => $request->description,
            'target_url' => $request->target_url,
            'order' => $request->order ?? 0,
            'is_active' => $request->boolean('is_active'), // Para checkboxes
        ]);

        return redirect()->route('admin.banners.index')->with('success', 'Banner creado exitosamente.');
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // 'nullable' para que no sea obligatorio al actualizar
            'description' => 'nullable|string',
            'target_url' => 'nullable|url',
            'order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $imagePathToSave = $banner->image_url; // Mantener la URL existente por defecto

        if ($request->hasFile('image')) {
            // Elimina la imagen antigua si existe
            if ($banner->image_url) {
                // Hay que convertir la URL absoluta a una ruta relativa para Storage::disk('public')->delete
                $relativePathToDelete = str_replace(url('storage') . '/', '', $banner->image_url);
                if (Storage::disk('public')->exists($relativePathToDelete)) {
                    Storage::disk('public')->delete($relativePathToDelete);
                }
            }

            // Guarda la nueva imagen
            $newStoragePath = Storage::disk('public')->put('banners', $request->file('image'));
            
            // MODIFICACIÓN CLAVE AQUÍ: Generar la URL absoluta para guardar en la base de datos
            $imagePathToSave = asset('storage/' . $newStoragePath);
        }

        $banner->update([
            'title' => $request->title,
            'image_url' => $imagePathToSave, // Ahora $imagePathToSave ya es la URL absoluta
            'description' => $request->description,
            'target_url' => $request->target_url,
            'order' => $request->order,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.banners.index')->with('success', 'Banner actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Banner $banner)
    {
        // Para eliminar, también necesitamos convertir la URL absoluta a una ruta relativa
        if ($banner->image_url) {
            $relativePathToDelete = str_replace(Storage::url(''), '', $banner->image_url);
            if (Storage::disk('public')->exists($relativePathToDelete)) {
                Storage::disk('public')->delete($relativePathToDelete);
            }
        }
        $banner->delete();
        return redirect()->route('admin.banners.index')->with('success', 'Banner eliminado exitosamente.');
    }
}