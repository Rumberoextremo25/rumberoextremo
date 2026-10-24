<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeSlide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class HomeSlideController extends Controller
{
    public function index()
    {
        $slides = HomeSlide::query()
            ->orderBy('display_order')
            ->orderBy('id')
            ->get();

        return view('Admin.home-slides.index', compact('slides'));
    }

    public function create()
    {
        return view('Admin.home-slides.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateSlide($request);
        $imagePath = $request->file('image')->store('home-slides', 'public');

        if (! $imagePath) {
            throw ValidationException::withMessages([
                'image' => 'No se pudo guardar la imagen. Revisa los permisos de storage.',
            ]);
        }

        HomeSlide::create([
            'title' => $data['title'] ?? null,
            'alt_text' => $data['alt_text'] ?? null,
            'image_path' => $imagePath,
            'target_url' => $data['target_url'] ?? null,
            'display_order' => $data['display_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.home-slides.index')
            ->with('success', 'Imagen agregada al carrusel del Home.');
    }

    public function edit(HomeSlide $homeSlide)
    {
        return view('Admin.home-slides.edit', ['slide' => $homeSlide]);
    }

    public function update(Request $request, HomeSlide $homeSlide)
    {
        $data = $this->validateSlide($request, $homeSlide);
        $imagePath = $homeSlide->image_path;

        if ($request->hasFile('image')) {
            $newImagePath = $request->file('image')->store('home-slides', 'public');

            if (! $newImagePath) {
                throw ValidationException::withMessages([
                    'image' => 'No se pudo guardar la imagen. Revisa los permisos de storage.',
                ]);
            }

            $this->deleteStoredFile($imagePath);
            $imagePath = $newImagePath;
        }

        $homeSlide->update([
            'title' => $data['title'] ?? null,
            'alt_text' => $data['alt_text'] ?? null,
            'image_path' => $imagePath,
            'target_url' => $data['target_url'] ?? null,
            'display_order' => $data['display_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.home-slides.index')
            ->with('success', 'Imagen del Home actualizada.');
    }

    public function toggle(HomeSlide $homeSlide)
    {
        $homeSlide->update(['is_active' => ! $homeSlide->is_active]);

        return redirect()
            ->route('admin.home-slides.index')
            ->with('success', $homeSlide->is_active
                ? 'Imagen activada en el carrusel del Home.'
                : 'Imagen oculta del carrusel del Home.');
    }

    public function destroy(HomeSlide $homeSlide)
    {
        $this->deleteStoredFile($homeSlide->image_path);
        $homeSlide->delete();

        return redirect()
            ->route('admin.home-slides.index')
            ->with('success', 'Imagen eliminada del carrusel del Home.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateSlide(Request $request, ?HomeSlide $current = null): array
    {
        $hasCurrentImage = $current && $current->image_path;

        return $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'image' => [
                $hasCurrentImage ? 'nullable' : 'required',
                'image',
                'mimes:jpeg,png,jpg,webp,avif',
                'max:5120',
            ],
            'target_url' => ['nullable', 'url', 'max:500'],
            'display_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);
    }

    private function deleteStoredFile(?string $path): void
    {
        if (! $path || str_starts_with($path, 'http') || str_starts_with($path, 'assets/')) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
