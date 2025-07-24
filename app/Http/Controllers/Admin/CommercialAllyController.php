<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommercialAlly;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            $logoPath = Storage::disk('public')->put('logos', $request->file('logo'));
        }

        CommercialAlly::create([
            'name' => $request->name,
            'logo_url' => $logoPath ? Storage::url($logoPath) : null,
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

        $logoPath = $commercialAlly->logo_url;
        if ($request->hasFile('logo')) {
            if ($commercialAlly->logo_url && Storage::disk('public')->exists(str_replace('/storage/', '', $commercialAlly->logo_url))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $commercialAlly->logo_url));
            }
            $logoPath = Storage::disk('public')->put('logos', $request->file('logo'));
        }

        $commercialAlly->update([
            'name' => $request->name,
            'logo_url' => $logoPath ? Storage::url($logoPath) : $commercialAlly->logo_url,
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
        if ($commercialAlly->logo_url && Storage::disk('public')->exists(str_replace('/storage/', '', $commercialAlly->logo_url))) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $commercialAlly->logo_url));
        }
        $commercialAlly->delete();
        return redirect()->route('admin.commercial-allies.index')->with('success', 'Aliado comercial eliminado exitosamente.');
    }
}
