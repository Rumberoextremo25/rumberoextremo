{{-- resources/views/Admin/banners/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Editar banner')

@section('page_title_toolbar', 'Editar banner')


@section('content')
    @php
        $currentSrc = $banner->image_src;
        $hasImage = (bool) $banner->image_url;
        $currentName = $banner->image_url ? basename($banner->image_url) : 'Ninguna imagen seleccionada';
        $isActive = old('is_active', $banner->is_active);
    @endphp

    <div class="cm-page cf-page">

        {{-- ============================ CABECERA ============================ --}}
        <div class="page-header-block">
            <div>
                <h1 class="title-main">Editar <span class="title-accent">banner</span></h1>
                <p class="page-subtitle">
                    Actualiza la imagen, el enlace o el orden de «{{ $banner->title ?: 'banner sin título' }}».
                </p>
            </div>

            <a href="{{ route('admin.banners.index') }}" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-left"></i> Volver al listado
            </a>
        </div>

        {{-- ============================== AVISOS ============================== --}}
        @if ($errors->any())
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
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

        @if (session('success'))
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <div class="alert-content">{{ session('success') }}</div>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div class="alert-content">{{ session('error') }}</div>
            </div>
        @endif

        {{-- ============================== FORM ============================== --}}
        <form action="{{ route('admin.banners.update', $banner->id) }}" method="POST" enctype="multipart/form-data"
            class="cf-editor" data-cf-form="banner" id="bannerForm" autocomplete="off">
            @csrf
            @method('PUT')

            <div class="cf-form">

                {{-- ------------------------- Columna principal ------------------------- --}}
                <div class="cf-col">

                    {{-- Imagen --}}
                    <section class="cf-card">
                        <div class="cf-card__head">
                            <span class="cf-card__icon"><i class="fa-regular fa-image"></i></span>
                            <div class="cf-card__head-text">
                                <h2>Imagen del banner</h2>
                                <p>Deja la actual o sube una nueva para reemplazarla.</p>
                            </div>
                        </div>

                        <div class="cf-card__body">
                            <div class="cf-upload {{ $hasImage ? 'has-file' : '' }}" data-cf-upload>
                                <div class="cf-upload__thumb" data-cf-thumb>
                                    <i class="fa-regular fa-image"></i>
                                    <img data-cf-thumb-img data-img alt=""
                                        @if ($currentSrc) src="{{ $currentSrc }}" @else hidden @endif>
                                </div>

                                <div class="cf-upload__info">
                                    <span class="cf-upload__name" data-cf-file-name>{{ $currentName }}</span>
                                    <span class="cf-upload__hint" data-cf-file-hint>
                                        @if ($hasImage)
                                            Imagen actual. Arrastra otra aquí o usa el botón para cambiarla.
                                        @else
                                            Arrastra la imagen aquí o usa el botón. JPG, PNG, GIF o WEBP · máx. 2 MB.
                                        @endif
                                    </span>
                                </div>

                                <div class="cf-upload__actions">
                                    <button type="button" class="btn btn-secondary btn-sm" data-cf-image-remove hidden>
                                        <i class="fa-solid fa-xmark"></i> Descartar
                                    </button>
                                    <label for="image" class="btn btn-secondary btn-sm">
                                        <i class="fa-solid fa-arrow-up-from-bracket"></i>
                                        {{ $hasImage ? 'Cambiar' : 'Subir' }}
                                    </label>
                                </div>

                                <input type="file" id="image" name="image" accept="image/*"
                                    data-cf-image-input class="@error('image') is-invalid @enderror">
                            </div>

                            <span class="cf-file-error" data-cf-file-error></span>

                            @error('image')
                                <span class="error-message" style="margin-top:.5rem;">
                                    <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                                </span>
                            @enderror
                        </div>
                    </section>

                    {{-- Información --}}
                    <section class="cf-card">
                        <div class="cf-card__head">
                            <span class="cf-card__icon"><i class="fa-solid fa-circle-info"></i></span>
                            <div class="cf-card__head-text">
                                <h2>Información del banner</h2>
                                <p>El título y el enlace que verá el Rumbero al tocar la imagen.</p>
                            </div>
                        </div>

                        <div class="cf-card__body">
                            <div class="cf-grid">
                                <div class="form-group cf-span">
                                    <label for="title">Título <span class="required-star">*</span></label>
                                    <input type="text" id="title" name="title" maxlength="255"
                                        class="form-control @error('title') is-invalid @enderror"
                                        placeholder="Ej: Nueva colección de verano"
                                        value="{{ old('title', $banner->title) }}" required>
                                    @error('title')
                                        <span class="error-message">
                                            <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="target_url">Enlace de destino</label>
                                    <input type="url" id="target_url" name="target_url"
                                        class="form-control @error('target_url') is-invalid @enderror"
                                        placeholder="https://tutienda.com/nueva-coleccion"
                                        value="{{ old('target_url', $banner->target_url) }}">
                                    <span class="field-hint">
                                        <i class="fa-solid fa-circle-info"></i> Déjalo vacío si el banner no redirige.
                                    </span>
                                    @error('target_url')
                                        <span class="error-message">
                                            <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="display_order">Orden</label>
                                    <input type="number" id="display_order" name="display_order" min="0"
                                        class="form-control @error('display_order') is-invalid @enderror"
                                        placeholder="0" value="{{ old('display_order', $banner->display_order) }}">
                                    <span class="field-hint">
                                        <i class="fa-solid fa-circle-info"></i> Los números más bajos salen primero.
                                    </span>
                                    @error('display_order')
                                        <span class="error-message">
                                            <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group cf-span">
                                    <label for="description">Descripción</label>
                                    <textarea id="description" name="description" rows="4"
                                        class="form-control @error('description') is-invalid @enderror"
                                        placeholder="Una frase corta que acompañe al banner…">{{ old('description', $banner->description) }}</textarea>
                                    <span class="field-hint">
                                        <i class="fa-solid fa-circle-info"></i> Se muestra en la tarjeta de la portada.
                                    </span>
                                    @error('description')
                                        <span class="error-message">
                                            <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- Publicación --}}
                    <section class="cf-card">
                        <div class="cf-card__head">
                            <span class="cf-card__icon"><i class="fa-solid fa-toggle-on"></i></span>
                            <div class="cf-card__head-text">
                                <h2>Publicación</h2>
                                <p>Controla si el banner está visible en el sitio.</p>
                            </div>
                        </div>

                        <div class="cf-card__body">
                            <div class="cf-switches">
                                <label class="cf-switch" for="is_active">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" id="is_active" value="1"
                                        {{ $isActive ? 'checked' : '' }}>
                                    <span class="cf-switch__track" aria-hidden="true"></span>
                                    <span class="cf-switch__text">
                                        <strong>Banner activo</strong>
                                        <span>Se mostrará en la portada del sitio mientras esté activo.</span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </section>
                </div>

                {{-- ----------------------------- Aside ------------------------------ --}}
                <aside class="cf-aside">

                    {{-- Previsualización --}}
                    <div class="cf-preview" data-cf-preview>
                        <div class="cf-preview__head">
                            <strong><i class="fa-regular fa-eye"></i> Vista previa</strong>
                            <span class="cm-chip">Portada</span>
                        </div>

                        <div class="cf-preview__media">
                            <img data-cf-preview-image data-img alt="" @if ($currentSrc) src="{{ $currentSrc }}"
                                @else hidden @endif>

                            <div class="cf-preview__empty" data-cf-preview-empty
                                @if ($currentSrc) hidden @endif>
                                <i class="fa-regular fa-image"></i>
                                <span>La imagen se verá aquí</span>
                            </div>

                            <span class="cm-card__state {{ $isActive ? 'is-on' : 'is-off' }}"
                                data-cf-preview="status">
                                <i class="fa-solid {{ $isActive ? 'fa-circle-check' : 'fa-circle-pause' }}"></i>
                                <span>{{ $isActive ? 'Activo' : 'Inactivo' }}</span>
                            </span>

                            <span class="cm-card__order" data-cf-preview="order">Orden
                                {{ old('display_order', $banner->display_order) }}</span>
                        </div>

                        <div class="cf-preview__body">
                            <h3 class="cf-preview__title" data-cf-preview="title" data-empty="Título del banner">
                                {{ old('title', $banner->title) ?: 'Título del banner' }}</h3>
                            <p class="cf-preview__desc" data-cf-preview="desc"
                                data-empty="Aquí se verá la descripción que escribas.">
                                {{ old('description', $banner->description) ?: 'Aquí se verá la descripción que escribas.' }}
                            </p>

                            <a class="cf-preview__cta" href="#" tabindex="-1" aria-hidden="true">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                <span data-cf-preview="cta">Ver más</span>
                            </a>
                        </div>
                    </div>

                    {{-- Datos del registro --}}
                    <div class="cf-card">
                        <div class="cf-card__head">
                            <span class="cf-card__icon"><i class="fa-solid fa-clock-rotate-left"></i></span>
                            <div class="cf-card__head-text">
                                <h2>Datos del registro</h2>
                            </div>
                        </div>

                        <div class="cf-meta">
                            <div class="cf-meta__row">
                                <span>Identificador</span>
                                <strong>#{{ $banner->id }}</strong>
                            </div>
                            <div class="cf-meta__row">
                                <span>Creado</span>
                                <strong>{{ optional($banner->created_at)->format('d/m/Y H:i') ?? '—' }}</strong>
                            </div>
                            <div class="cf-meta__row">
                                <span>Última edición</span>
                                <strong>{{ optional($banner->updated_at)->format('d/m/Y H:i') ?? '—' }}</strong>
                            </div>
                            <div class="cf-meta__row">
                                <span>Estado</span>
                                <strong>
                                    @if ($isActive)
                                        <span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> Activo</span>
                                    @else
                                        <span class="badge badge-neutral"><i class="fa-solid fa-circle-pause"></i> Inactivo</span>
                                    @endif
                                </strong>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>

            {{-- ------------------------------ Acciones ------------------------------ --}}
            <div class="cf-actions">
                <a href="{{ route('admin.banners.index') }}" class="btn btn-secondary">
                    <i class="fa-solid fa-xmark"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary" data-cf-submit>
                    <i class="fa-solid fa-floppy-disk"></i> Guardar cambios
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin-forms.js') }}"></script>
@endpush
