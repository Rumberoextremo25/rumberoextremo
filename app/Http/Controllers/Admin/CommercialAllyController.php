<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommercialAlly;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CommercialAllyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $estado = $request->query('estado');

        $alliesQuery = CommercialAlly::query();

        if ($q !== '') {
            $like = '%' . $q . '%';
            $operator = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

            $alliesQuery->where(function ($sub) use ($like, $operator) {
                $sub->where('name', $operator, $like)
                    ->orWhere('description', $operator, $like)
                    ->orWhere('website_url', $operator, $like);
            });
        }

        if (in_array($estado, ['1', '0'], true)) {
            $alliesQuery->where('is_active', $estado === '1');
        }

        $allies = $alliesQuery->orderBy('name')->paginate(10)->withQueryString();

        $stats = [
            'total' => CommercialAlly::count(),
            'active' => CommercialAlly::where('is_active', true)->count(),
            'inactive' => CommercialAlly::where('is_active', false)->count(),
            'rating' => round((float) CommercialAlly::avg('rating'), 1),
        ];

        return view('Admin.commercial_allies.index', compact('allies', 'stats', 'q', 'estado'));
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
            'is_active' => 'sometimes|boolean',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('commercial-allies/logos', 'public');
        }

        CommercialAlly::create([
            'name' => $request->name,
            'logo_url' => $logoPath,
            'rating' => $request->rating ?? 0.0,
            'description' => $request->description,
            'website_url' => $request->website_url,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.commercial-allies.index')
            ->with('success', 'Aliado comercial creado exitosamente.');
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
            'is_active' => 'sometimes|boolean',
        ]);

        $logoPathToSave = $commercialAlly->logo_url;

        if ($request->hasFile('logo')) {
            // Eliminar logo anterior si existe
            if ($commercialAlly->logo_url && Storage::disk('public')->exists($commercialAlly->logo_url)) {
                Storage::disk('public')->delete($commercialAlly->logo_url);
            }
            $logoPathToSave = $request->file('logo')->store('commercial-allies/logos', 'public');
        }

        $commercialAlly->update([
            'name' => $request->name,
            'logo_url' => $logoPathToSave,
            'rating' => $request->rating ?? 0.0,
            'description' => $request->description,
            'website_url' => $request->website_url,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.commercial-allies.index')
            ->with('success', 'Aliado comercial actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CommercialAlly $commercialAlly)
    {
        // Eliminar el logo si existe
        if ($commercialAlly->logo_url && Storage::disk('public')->exists($commercialAlly->logo_url)) {
            Storage::disk('public')->delete($commercialAlly->logo_url);
        }
        
        $commercialAlly->delete();
        
        return redirect()->route('admin.commercial-allies.index')
            ->with('success', 'Aliado comercial eliminado exitosamente.');
    }
}
