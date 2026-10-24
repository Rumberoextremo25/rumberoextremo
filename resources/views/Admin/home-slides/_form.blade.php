@php
    $editing = isset($slide) && $slide;
    $isActive = old('is_active', $editing ? $slide->is_active : true);
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

<form action="{{ $editing ? route('admin.home-slides.update', $slide) : route('admin.home-slides.store') }}"
    method="POST" enctype="multipart/form-data" class="cf-editor" autocomplete="off">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <div class="cf-form">
        <div class="cf-col">
            <section class="cf-card">
                <div class="cf-card__head">
                    <span class="cf-card__icon"><i class="fa-regular fa-image" aria-hidden="true"></i></span>
                    <div class="cf-card__head-text">
                        <h2>Imagen</h2>
                        <p>Horizontal 21:9. Se recorta al centro en el carrusel del Home.</p>
                    </div>
                </div>

                <div class="cf-card__body">
                    <div class="form-group">
                        <label for="image">Imagen {{ $editing ? '' : '*' }}</label>
                        <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp,image/avif"
                            class="form-control @error('image') is-invalid @enderror"
                            @if (! $editing) required @endif>
                        <span class="field-hint">
                            <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                            JPG, PNG, WEBP o AVIF · máximo 5 MB. Se recomienda 1600 × 700 px.
                        </span>
                        @error('image')
                            <span class="error-message"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i> {{ $message }}</span>
                        @enderror

                        @if ($editing && $slide->image_src)
                            <div class="hs-current-image">
                                <img src="{{ $slide->image_src }}" alt="{{ $slide->alt_text ?: $slide->title }}">
                                <div>
                                    <strong>Imagen actual</strong>
                                    @if ($slide->is_static)
                                        <small>Imagen del proyecto. Al subir una nueva se reemplaza en el carrusel; el archivo original no se borra.</small>
                                    @else
                                        <small>{{ basename($slide->image_path) }}</small>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </section>

            <section class="cf-card">
                <div class="cf-card__head">
                    <span class="cf-card__icon"><i class="fa-solid fa-circle-info" aria-hidden="true"></i></span>
                    <div class="cf-card__head-text">
                        <h2>Información</h2>
                        <p>Texto alternativo, enlace opcional y posición.</p>
                    </div>
                </div>

                <div class="cf-card__body">
                    <div class="cf-grid">
                        <div class="form-group">
                            <label for="title">Título interno</label>
                            <input type="text" id="title" name="title" maxlength="255"
                                class="form-control @error('title') is-invalid @enderror"
                                value="{{ old('title', $editing ? $slide->title : '') }}"
                                placeholder="Ej: Discoteca">
                        </div>

                        <div class="form-group">
                            <label for="display_order">Orden</label>
                            <input type="number" id="display_order" name="display_order" min="0" max="9999"
                                class="form-control @error('display_order') is-invalid @enderror"
                                value="{{ old('display_order', $editing ? $slide->display_order : 0) }}">
                            <span class="field-hint">
                                <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                                Los números más bajos salen primero.
                            </span>
                        </div>

                        <div class="form-group cf-span">
                            <label for="alt_text">Texto alternativo</label>
                            <input type="text" id="alt_text" name="alt_text" maxlength="255"
                                class="form-control @error('alt_text') is-invalid @enderror"
                                value="{{ old('alt_text', $editing ? $slide->alt_text : '') }}"
                                placeholder="Ej: Rumbero Extremo - Discoteca">
                            <span class="field-hint">
                                <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                                Describe la imagen para accesibilidad y SEO.
                            </span>
                        </div>

                        <div class="form-group cf-span">
                            <label for="target_url">Enlace opcional</label>
                            <input type="url" id="target_url" name="target_url" maxlength="500"
                                class="form-control @error('target_url') is-invalid @enderror"
                                value="{{ old('target_url', $editing ? $slide->target_url : '') }}"
                                placeholder="https://…">
                            <span class="field-hint">
                                <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                                Si lo defines, al tocar la imagen se abre ese enlace.
                            </span>
                        </div>

                        <div class="form-group cf-span">
                            <label class="cf-switch" for="is_active">
                                <input type="checkbox" id="is_active" name="is_active" value="1" @checked($isActive)>
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
                    @if ($editing && $slide->image_src)
                        <img src="{{ $slide->image_src }}" alt="{{ $slide->alt_text ?: '' }}" class="hs-preview-image">
                    @else
                        <div class="hs-preview-empty">
                            <i class="fa-regular fa-image" aria-hidden="true"></i>
                            <span>La vista previa aparece al guardar.</span>
                        </div>
                    @endif
                </div>
            </section>

            <section class="cf-card">
                <div class="cf-card__body">
                    <p class="hs-tip">
                        <i class="fa-solid fa-lightbulb" aria-hidden="true"></i>
                        Puedes agregar todas las imágenes que quieras. El carrusel del Home se arma con las activas y respeta el orden.
                    </p>
                </div>
            </section>

            <div class="cf-actions hs-form-actions">
                <a href="{{ route('admin.home-slides.index') }}" class="btn btn-secondary">
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                    {{ $editing ? 'Guardar cambios' : 'Agregar imagen' }}
                </button>
            </div>
        </aside>
    </div>
</form>
