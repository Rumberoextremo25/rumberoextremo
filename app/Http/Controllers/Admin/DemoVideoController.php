<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DemoVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DemoVideoController extends Controller
{
    public function index()
    {
        $videos = DemoVideo::query()
            ->orderBy('display_order')
            ->orderBy('id')
            ->get();

        return view('Admin.demo-videos.index', compact('videos'));
    }

    public function create()
    {
        return view('Admin.demo-videos.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateVideo($request);
        $videoPath = null;
        $thumbnailPath = null;

        if ($data['source'] === 'file' && $request->hasFile('video_file')) {
            $videoPath = $request->file('video_file')->store('demo-videos', 'public');

            if (! $videoPath) {
                throw ValidationException::withMessages([
                    'video_file' => 'No se pudo guardar el video. Revisa los permisos de storage.',
                ]);
            }
        }

        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('demo-videos/thumbs', 'public');
        }

        DemoVideo::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'source' => $data['source'],
            'video_url' => $data['source'] === 'youtube'
                ? 'https://www.youtube.com/watch?v=' . DemoVideo::extractYoutubeId($data['youtube_url'])
                : null,
            'video_path' => $videoPath,
            'thumbnail_path' => $thumbnailPath,
            'display_order' => $data['display_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.demo-videos.index')
            ->with('success', 'Video agregado al carrusel de la demo.');
    }

    public function edit(DemoVideo $demoVideo)
    {
        return view('Admin.demo-videos.edit', ['video' => $demoVideo]);
    }

    public function update(Request $request, DemoVideo $demoVideo)
    {
        $data = $this->validateVideo($request, $demoVideo);
        $videoPath = $demoVideo->video_path;
        $thumbnailPath = $demoVideo->thumbnail_path;

        if ($request->hasFile('video_file') && $data['source'] === 'file') {
            $newVideoPath = $request->file('video_file')->store('demo-videos', 'public');

            if (! $newVideoPath) {
                throw ValidationException::withMessages([
                    'video_file' => 'No se pudo guardar el video. Revisa los permisos de storage.',
                ]);
            }

            $this->deleteStoredFile($videoPath);
            $videoPath = $newVideoPath;
        }

        if ($request->hasFile('thumbnail')) {
            $newThumbnailPath = $request->file('thumbnail')->store('demo-videos/thumbs', 'public');

            if (! $newThumbnailPath) {
                throw ValidationException::withMessages([
                    'thumbnail' => 'No se pudo guardar la miniatura. Revisa los permisos de storage.',
                ]);
            }

            $this->deleteStoredFile($thumbnailPath);
            $thumbnailPath = $newThumbnailPath;
        }

        if ($data['source'] === 'youtube') {
            // Al cambiar a YouTube el archivo local deja de usarse.
            $this->deleteStoredFile($videoPath);
            $videoPath = null;
        }

        $demoVideo->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'source' => $data['source'],
            'video_url' => $data['source'] === 'youtube'
                ? 'https://www.youtube.com/watch?v=' . DemoVideo::extractYoutubeId($data['youtube_url'])
                : null,
            'video_path' => $videoPath,
            'thumbnail_path' => $thumbnailPath,
            'display_order' => $data['display_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.demo-videos.index')
            ->with('success', 'Video actualizado correctamente.');
    }

    public function toggle(DemoVideo $demoVideo)
    {
        $demoVideo->update(['is_active' => ! $demoVideo->is_active]);

        return redirect()
            ->route('admin.demo-videos.index')
            ->with('success', $demoVideo->is_active
                ? 'Video activado en el carrusel.'
                : 'Video oculto del carrusel.');
    }

    public function destroy(DemoVideo $demoVideo)
    {
        $this->deleteStoredFile($demoVideo->video_path);
        $this->deleteStoredFile($demoVideo->thumbnail_path);
        $demoVideo->delete();

        return redirect()
            ->route('admin.demo-videos.index')
            ->with('success', 'Video eliminado del carrusel.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateVideo(Request $request, ?DemoVideo $current = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'source' => ['required', Rule::in(['youtube', 'file'])],
            'youtube_url' => ['nullable', 'string', 'max:500'],
            'video_file' => ['nullable', 'file', 'mimes:mp4,webm,ogg,mov,m4v', 'max:102400'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'display_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        if ($data['source'] === 'youtube') {
            $youtubeId = DemoVideo::extractYoutubeId($data['youtube_url'] ?? null);

            if (! $youtubeId) {
                throw ValidationException::withMessages([
                    'youtube_url' => 'Pega una URL de YouTube válida (watch, youtu.be o Shorts).',
                ]);
            }

            $data['youtube_url'] = 'https://www.youtube.com/watch?v=' . $youtubeId;
        } else {
            $hasExistingFile = $current && $current->video_path;
            $hasNewFile = $request->hasFile('video_file');

            if (! $hasExistingFile && ! $hasNewFile) {
                throw ValidationException::withMessages([
                    'video_file' => 'Sube un archivo de video o cambia la fuente a YouTube.',
                ]);
            }
        }

        return $data;
    }

    private function deleteStoredFile(?string $path): void
    {
        if (! $path || str_starts_with($path, 'http')) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
