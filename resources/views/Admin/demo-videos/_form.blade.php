@php
    $editing = isset($video) && $video;
    $source = old('source', $editing ? $video->source : 'youtube');
    $isActive = old('is_active', $editing ? $video->is_active : true);
@endphp

@if ($errors->any())
    <div class="alert alert-error">
        <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
        <div class="alert-content">
            <strong>Revisa los siguientes campos:</strong>
            <ul style="margin:.35rem 0 0;padding-left:1.1rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<form action="{{ $editing ? route('admin.demo-videos.update', $video) : route('admin.demo-videos.store') }}"
    method="POST" enctype="multipart/form-data" class="cf-editor" autocomplete="off">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <div class="cf-form">
        <div class="cf-col">

            {{-- Video --}}
            <section class="cf-card">
                <div class="cf-card__head">
                    <span class="cf-card__icon"><i class="fa-solid fa-video" aria-hidden="true"></i></span>
                    <div class="cf-card__head-text">
                        <h2>Video</h2>
                        <p>Usa un enlace de YouTube o sube un archivo MP4/WebM.</p>
                    </div>
                </div>

                <div class="cf-card__body">
                    <div class="form-group">
                        <label for="source">Fuente del video <span class="required-star">*</span></label>
                        <select id="source" name="source" class="form-control" required>
                            <option value="youtube" @selected($source === 'youtube')>YouTube</option>
                            <option value="file" @selected($source === 'file')>Archivo subido</option>
                        </select>
                    </div>

                    <div class="dv-source-fields" data-source="youtube" @if ($source !== 'youtube') hidden @endif>
                        <div class="form-group">
                            <label for="youtube_url">URL de YouTube <span class="required-star">*</span></label>
                            <input type="text" id="youtube_url" name="youtube_url"
                                class="form-control @error('youtube_url') is-invalid @enderror"
                                value="{{ old('youtube_url', $editing ? $video->video_url : '') }}"
                                placeholder="https://www.youtube.com/watch?v=v4j2Hxq7BoI">
                            <span class="field-hint">
                                <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                                Acepta watch, youtu.be, Shorts o el ID del video.
                            </span>
                            @error('youtube_url')
                                <span class="error-message"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i> {{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="dv-source-fields" data-source="file" @if ($source !== 'file') hidden @endif>
                        <div class="form-group">
                            <label for="video_file">Archivo de video</label>
                            <input type="file" id="video_file" name="video_file" accept="video/mp4,video/webm,video/ogg,video/quicktime"
                                class="form-control @error('video_file') is-invalid @enderror">
                            <span class="field-hint">
                                <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                                MP4, WebM, OGG o MOV · máximo 100 MB. Para videos más pesados usa YouTube.
                            </span>
                            @error('video_file')
                                <span class="error-message"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i> {{ $message }}</span>
                            @enderror

                            @if ($editing && $video->video_path)
                                <div class="dv-current-file">
                                    <i class="fa-solid fa-file-video" aria-hidden="true"></i>
                                    Archivo actual: <strong>{{ basename($video->video_path) }}</strong>
                                </div>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="thumbnail">Miniatura (opcional)</label>
                            <input type="file" id="thumbnail" name="thumbnail" accept="image/jpeg,image/png,image/webp"
                                class="form-control @error('thumbnail') is-invalid @enderror">
                            <span class="field-hint">
                                <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                                Se muestra antes de reproducir. Recomendado 16:9.
                            </span>
                            @error('thumbnail')
                                <span class="error-message"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i> {{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </section>

            {{-- Información --}}
            <section class="cf-card">
                <div class="cf-card__head">
                    <span class="cf-card__icon"><i class="fa-solid fa-circle-info" aria-hidden="true"></i></span>
                    <div class="cf-card__head-text">
                        <h2>Información</h2>
                        <p>Título, descripción y posición dentro del carrusel.</p>
                    </div>
                </div>

                <div class="cf-card__body">
                    <div class="cf-grid">
                        <div class="form-group cf-span">
                            <label for="title">Título <span class="required-star">*</span></label>
                            <input type="text" id="title" name="title" maxlength="255"
                                class="form-control @error('title') is-invalid @enderror"
                                value="{{ old('title', $editing ? $video->title : '') }}"
                                placeholder="Ej: Cómo registrarte como aliado" required>
                            @error('title')
                                <span class="error-message"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i> {{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group cf-span">
                            <label for="description">Descripción</label>
                            <textarea id="description" name="description" rows="4"
                                class="form-control @error('description') is-invalid @enderror"
                                placeholder="Una frase corta que explique el video…">{{ old('description', $editing ? $video->description : '') }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="display_order">Orden</label>
                            <input type="number" id="display_order" name="display_order" min="0" max="9999"
                                class="form-control @error('display_order') is-invalid @enderror"
                                value="{{ old('display_order', $editing ? $video->display_order : 0) }}">
                            <span class="field-hint">
                                <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                                Los números más bajos salen primero.
                            </span>
                        </div>

                        <div class="form-group">
                            <label>Visible en la demo</label>
                            <label class="cf-switch" for="is_active">
                                <input type="checkbox" id="is_active" name="is_active" value="1"
                                    @checked($isActive)>
                                <span class="cf-switch__track" aria-hidden="true"></span>
                                <span class="cf-switch__text">
                                    <strong>Mostrar en el carrusel</strong>
                                    <span>Si lo desactivas queda guardado pero no se muestra.</span>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
            </section>

        </div>

        <aside class="cf-aside">
            <section class="cf-card">
                <div class="cf-card__head">
                    <span class="cf-card__icon"><i class="fa-solid fa-eye" aria-hidden="true"></i></span>
                    <div class="cf-card__head-text">
                        <h2>Vista previa</h2>
                    </div>
                </div>
                <div class="cf-card__body">
                    @if ($editing && $video->thumbnail_src)
                        <img src="{{ $video->thumbnail_src }}" alt="{{ $video->title }}" class="dv-preview-image">
                    @elseif ($editing && $video->video_src && ! $video->is_youtube)
                        <video src="{{ $video->video_src }}" controls preload="metadata" class="dv-preview-video"></video>
                    @elseif ($editing && $video->is_youtube)
                        <div class="dv-preview-empty">
                            <i class="fa-brands fa-youtube" aria-hidden="true"></i>
                            <span>{{ $video->youtube_id }}</span>
                        </div>
                    @else
                        <div class="dv-preview-empty">
                            <i class="fa-solid fa-clapperboard" aria-hidden="true"></i>
                            <span>La vista previa aparece al guardar.</span>
                        </div>
                    @endif
                </div>
            </section>

            <section class="cf-card">
                <div class="cf-card__body">
                    <p class="dv-tip">
                        <i class="fa-solid fa-lightbulb" aria-hidden="true"></i>
                        Para mostrar 5 videos, agrega 4 más a este listado. El carrusel se arma solo con los activos.
                    </p>
                </div>
            </section>

            <div class="cf-actions dv-form-actions">
                <a href="{{ route('admin.demo-videos.index') }}" class="btn btn-secondary">
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                    {{ $editing ? 'Guardar cambios' : 'Agregar video' }}
                </button>
            </div>
        </aside>
    </div>
</form>

<script>
    (function () {
        var source = document.getElementById('source');
        var groups = document.querySelectorAll('[data-source]');

        function syncSource() {
            groups.forEach(function (group) {
                group.hidden = group.getAttribute('data-source') !== source.value;
            });
        }

        if (source) {
            source.addEventListener('change', syncSource);
            syncSource();
        }
    }());
</script>
