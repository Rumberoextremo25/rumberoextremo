<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PromotionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $promotions = Promotion::all();
        return view('admin.promotions.index', compact('promotions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.promotions.create');
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
            $imagePath = Storage::disk('public')->put('promotions', $request->file('image'));
        }

        Promotion::create([
            'title' => $request->title,
            'image_url' => $imagePath ? Storage::url($imagePath) : null,
            'discount' => $request->discount,
            'price' => $request->price,
            'expires_at' => $request->expires_at,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.promotions.index')->with('success', 'Promoción creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Promotion $promotion)
    {
        return view('admin.promotions.show', compact('promotion'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Promotion $promotion)
    {
        return view('admin.promotions.edit', compact('promotion'));
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

        $imagePath = $promotion->image_url;
        if ($request->hasFile('image')) {
            if ($promotion->image_url && Storage::disk('public')->exists(str_replace('/storage/', '', $promotion->image_url))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $promotion->image_url));
            }
            $imagePath = Storage::disk('public')->put('promotions', $request->file('image'));
        }

        $promotion->update([
            'title' => $request->title,
            'image_url' => $imagePath ? Storage::url($imagePath) : $promotion->image_url,
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
        if ($promotion->image_url && Storage::disk('public')->exists(str_replace('/storage/', '', $promotion->image_url))) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $promotion->image_url));
        }
        $promotion->delete();
        return redirect()->route('admin.promotions.index')->with('success', 'Promoción eliminada exitosamente.');
    }
}
