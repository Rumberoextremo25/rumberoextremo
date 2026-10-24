{{-- resources/views/Admin/banners/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Nuevo banner')

@section('page_title_toolbar', 'Nuevo banner')


@section('content')
    <div class="cm-page cf-page">

        {{-- ============================ CABECERA ============================ --}}
        <div class="page-header-block">
            <div>
                <h1 class="title-main">Nuevo <span class="title-accent">banner</span></h1>
                <p class="page-subtitle">
                    Sube una imagen horizontal y define a dónde lleva. Los números de orden más bajos se muestran primero.
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
        <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data"
            class="cf-editor" data-cf-form="banner" id="bannerForm" autocomplete="off">
            @csrf

            <div class="cf-form">

                {{-- ------------------------- Columna principal ------------------------- --}}
                <div class="cf-col">

                    {{-- Imagen --}}
                    <section class="cf-card">
                        <div class="cf-card__head">
                            <span class="cf-card__icon"><i class="fa-regular fa-image"></i></span>
                            <div class="cf-card__head-text">
                                <h2>Imagen del banner</h2>
                                <p>Formato horizontal 16:9 · se recorta al centro en la portada.</p>
                            </div>
                        </div>

                        <div class="cf-card__body">
                            <div class="cf-upload" data-cf-upload>
                                <div class="cf-upload__thumb" data-cf-thumb>
                                    <i class="fa-regular fa-image"></i>
                                    <img data-cf-thumb-img data-img alt="" hidden>
                                </div>

                                <div class="cf-upload__info">
                                    <span class="cf-upload__name" data-cf-file-name>Ninguna imagen seleccionada</span>
                                    <span class="cf-upload__hint" data-cf-file-hint>
                                        Arrastra la imagen aquí o usa el botón. JPG, PNG, GIF o WEBP · máx. 2 MB.
                                    </span>
                                </div>

                                <div class="cf-upload__actions">
                                    <button type="button" class="btn btn-secondary btn-sm" data-cf-image-remove hidden>
                                        <i class="fa-solid fa-xmark"></i> Quitar
                                    </button>
                                    <label for="image" class="btn btn-secondary btn-sm">
                                        <i class="fa-solid fa-arrow-up-from-bracket"></i> Subir imagen
                                    </label>
                                </div>

                                <input type="file" id="image" name="image" accept="image/*" data-cf-required="1"
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
                                        placeholder="Ej: Nueva colección de verano" value="{{ old('title') }}"
                                        required>
                                    @error('title')
                                        <span class="error-message">
                                            <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="target_url">
                                        Enlace de destino
                                    </label>
                                    <input type="url" id="target_url" name="target_url"
                                        class="form-control @error('target_url') is-invalid @enderror"
                                        placeholder="https://tutienda.com/nueva-coleccion"
                                        value="{{ old('target_url') }}">
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
                                        placeholder="0" value="{{ old('display_order', 0) }}">
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
                                        placeholder="Una frase corta que acompañe al banner…">{{ old('description') }}</textarea>
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
                                    <input type="checkbox" name="is_active" id="is_active" value="1"
                                        {{ old('is_active', true) ? 'checked' : '' }}>
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
                            <img data-cf-preview-image data-img alt="" hidden>

                            <div class="cf-preview__empty" data-cf-preview-empty>
                                <i class="fa-regular fa-image"></i>
                                <span>La imagen se verá aquí</span>
                            </div>

                            <span class="cm-card__state is-on" data-cf-preview="status">
                                <i class="fa-solid fa-circle-check"></i>
                                <span>Activo</span>
                            </span>

                            <span class="cm-card__order" data-cf-preview="order">Orden 0</span>
                        </div>

                        <div class="cf-preview__body">
                            <h3 class="cf-preview__title" data-cf-preview="title" data-empty="Título del banner">
                                Título del banner</h3>
                            <p class="cf-preview__desc" data-cf-preview="desc" data-empty="Aquí se verá la descripción que escribas.">
                                Aquí se verá la descripción que escribas.</p>

                            <a class="cf-preview__cta" href="#" tabindex="-1" aria-hidden="true">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                <span data-cf-preview="cta">Ver más</span>
                            </a>
                        </div>
                    </div>

                    {{-- Consejos --}}
                    <div class="cf-card">
                        <div class="cf-card__head">
                            <span class="cf-card__icon"><i class="fa-solid fa-lightbulb"></i></span>
                            <div class="cf-card__head-text">
                                <h2>Buenas prácticas</h2>
                            </div>
                        </div>

                        <div class="cf-tips">
                            <ul>
                                <li><i class="fa-solid fa-check"></i> Usa imágenes de al menos 1600 px de ancho.</li>
                                <li><i class="fa-solid fa-check"></i> Evita texto pequeño: en el móvil se lee poco.</li>
                                <li><i class="fa-solid fa-check"></i> Un enlace claro mejora el clic en la portada.</li>
                                <li><i class="fa-solid fa-check"></i> Si tienes varios banners, reparte el orden 1, 2, 3…</li>
                            </ul>
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
                    <i class="fa-solid fa-floppy-disk"></i> Guardar banner
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin-forms.js') }}"></script>
@endpush
