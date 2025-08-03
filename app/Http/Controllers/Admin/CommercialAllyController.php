<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommercialAlly; // Asegúrate de que este namespace es correcto
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // Importa la fachada Storage

class CommercialAllyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $allies = CommercialAlly::all();
        return view('Admin.commercial_allies.index', compact('allies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Admin.commercial_allies.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'rating' => 'nullable|numeric|min:0|max:5',
            'description' => 'nullable|string',
            'website_url' => 'nullable|url',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $storagePath = Storage::disk('public')->put('logos', $request->file('logo'));
            // MODIFICACIÓN CLAVE AQUÍ: Generar la URL absoluta usando asset()
            $logoPath = asset('storage/' . $storagePath);
        }

        CommercialAlly::create([
            'name' => $request->name,
            'logo_url' => $logoPath, // Ahora $logoPath ya es la URL absoluta o null
            'rating' => $request->rating ?? 0.0,
            'description' => $request->description,
            'website_url' => $request->website_url,
        ]);

        return redirect()->route('admin.commercial-allies.index')->with('success', 'Aliado comercial creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(CommercialAlly $commercialAlly)
    {
        return view('Admin.commercial_allies.show', compact('commercialAlly'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CommercialAlly $commercialAlly)
    {
        return view('Admin.commercial_allies.edit', compact('commercialAlly'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CommercialAlly $commercialAlly)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'rating' => 'nullable|numeric|min:0|max:5',
            'description' => 'nullable|string',
            'website_url' => 'nullable|url',
        ]);

        $logoPathToSave = $commercialAlly->logo_url; // Mantener la URL existente por defecto

        if ($request->hasFile('logo')) {
            // Eliminar el logo anterior si existe
            if ($commercialAlly->logo_url) {
                // Hay que convertir la URL absoluta a una ruta relativa para Storage::disk('public')->delete
                $relativePathToDelete = str_replace(asset('storage/'), '', $commercialAlly->logo_url);
                if (Storage::disk('public')->exists($relativePathToDelete)) {
                    Storage::disk('public')->delete($relativePathToDelete);
                }
            }

            // Guardar el nuevo logo
            $newStoragePath = Storage::disk('public')->put('logos', $request->file('logo'));
            
            // MODIFICACIÓN CLAVE AQUÍ: Generar la URL absoluta para guardar en la base de datos
            $logoPathToSave = asset('storage/' . $newStoragePath);
        }

        $commercialAlly->update([
            'name' => $request->name,
            'logo_url' => $logoPathToSave, // Ahora $logoPathToSave ya es la URL absoluta
            'rating' => $request->rating,
            'description' => $request->description,
            'website_url' => $request->website_url,
        ]);

        return redirect()->route('admin.commercial-allies.index')->with('success', 'Aliado comercial actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CommercialAlly $commercialAlly)
    {
        // Para eliminar, también necesitamos convertir la URL absoluta a una ruta relativa
        if ($commercialAlly->logo_url) {
            $relativePathToDelete = str_replace(asset('storage/'), '', $commercialAlly->logo_url);
            if (Storage::disk('public')->exists($relativePathToDelete)) {
                Storage::disk('public')->delete($relativePathToDelete);
            }
        }
        $commercialAlly->delete();
        return redirect()->route('admin.commercial-allies.index')->with('success', 'Aliado comercial eliminado exitosamente.');
    }
}
