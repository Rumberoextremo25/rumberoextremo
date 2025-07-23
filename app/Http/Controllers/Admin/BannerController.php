<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // Para manejar la subida de archivos

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $banners = Banner::orderBy('order')->get();
        return view('admin.banners.index', compact('banners'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.banners.create');
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
            $imagePath = Storage::disk('public')->put('banners', $request->file('image'));
        }

        Banner::create([
            'title' => $request->title,
            'image_url' => $imagePath ? Storage::url($imagePath) : null,
            'description' => $request->description,
            'target_url' => $request->target_url,
            'order' => $request->order ?? 0,
            'is_active' => $request->boolean('is_active'), // Para checkboxes
        ]);

        return redirect()->route('admin.banners.index')->with('success', 'Banner creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Banner $banner)
    {
        return view('admin.banners.show', compact('banner'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Banner $banner)
    {
        return view('admin.banners.edit', compact('banner'));
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

        $imagePath = $banner->image_url;
        if ($request->hasFile('image')) {
            // Elimina la imagen antigua si existe
            if ($banner->image_url && Storage::disk('public')->exists(str_replace('/storage/', '', $banner->image_url))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $banner->image_url));
            }
            $imagePath = Storage::disk('public')->put('banners', $request->file('image'));
        }

        $banner->update([
            'title' => $request->title,
            'image_url' => $imagePath ? Storage::url($imagePath) : $banner->image_url,
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
        if ($banner->image_url && Storage::disk('public')->exists(str_replace('/storage/', '', $banner->image_url))) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $banner->image_url));
        }
        $banner->delete();
        return redirect()->route('admin.banners.index')->with('success', 'Banner eliminado exitosamente.');
    }
}
