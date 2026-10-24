@extends('layouts.admin')

@section('title', 'Nueva promoción')

@section('page_title_toolbar', 'Nueva promoción')


@section('content')
    <div class="cm-page cf-page">

        {{-- ============================ CABECERA ============================ --}}
        <div class="page-header-block">
            <div>
                <h1 class="title-main">Nueva <span class="title-accent">promoción</span></h1>
                <p class="page-subtitle">
                    Crea una oferta para los Rumberos: descuento, precio, vigencia e imagen. Lo verás en vivo mientras lo editas.
                </p>
            </div>

            <a href="{{ route('admin.promotions.index') }}" class="btn btn-secondary">
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
        <form action="{{ route('admin.promotions.store') }}" method="POST" enctype="multipart/form-data"
            class="cf-editor" data-cf-form="promotion" id="promotionForm" autocomplete="off">
            @csrf

            <div class="cf-form">

                {{-- ------------------------- Columna principal ------------------------- --}}
                <div class="cf-col">

                    {{-- Imagen --}}
                    <section class="cf-card">
                        <div class="cf-card__head">
                            <span class="cf-card__icon"><i class="fa-regular fa-image"></i></span>
                            <div class="cf-card__head-text">
                                <h2>Imagen de la promoción</h2>
                                <p>Formato cuadrado o 4:3 · es la imagen principal de la oferta.</p>
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

                    {{-- Oferta --}}
                    <section class="cf-card">
                        <div class="cf-card__head">
                            <span class="cf-card__icon"><i class="fa-solid fa-tag"></i></span>
                            <div class="cf-card__head-text">
                                <h2>Información de la oferta</h2>
                                <p>Lo que leerá el Rumbero en la tarjeta de la promoción.</p>
                            </div>
                        </div>

                        <div class="cf-card__body">
                            <div class="cf-grid">
                                <div class="form-group cf-span">
                                    <label for="title">Título <span class="required-star">*</span></label>
                                    <input type="text" id="title" name="title" maxlength="255"
                                        class="form-control @error('title') is-invalid @enderror"
                                        placeholder="Ej: 20% de descuento en zapatos" value="{{ old('title') }}"
                                        required>
                                    @error('title')
                                        <span class="error-message">
                                            <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="discount">Descuento <span class="required-star">*</span></label>
                                    <input type="text" id="discount" name="discount" maxlength="50"
                                        class="form-control @error('discount') is-invalid @enderror"
                                        placeholder="20%, 2x1, $5…" value="{{ old('discount') }}" required>
                                    <span class="field-hint">
                                        <i class="fa-solid fa-circle-info"></i> Acepta porcentaje, 2x1 o monto fijo.
                                    </span>
                                    @error('discount')
                                        <span class="error-message">
                                            <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="price">Precio final <span class="required-star">*</span></label>
                                    <input type="number" id="price" name="price" step="0.01" min="0"
                                        class="form-control @error('price') is-invalid @enderror" placeholder="49.99"
                                        value="{{ old('price') }}" required>
                                    <span class="field-hint">
                                        <i class="fa-solid fa-circle-info"></i> Después de aplicar el descuento.
                                    </span>
                                    @error('price')
                                        <span class="error-message">
                                            <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="expires_at">Vence el</label>
                                    <input type="date" id="expires_at" name="expires_at"
                                        class="form-control @error('expires_at') is-invalid @enderror"
                                        value="{{ old('expires_at') }}" min="{{ date('Y-m-d') }}">
                                    <span class="field-hint">
                                        <i class="fa-solid fa-circle-info"></i> Déjalo vacío si no caduca.
                                    </span>
                                    @error('expires_at')
                                        <span class="error-message">
                                            <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="max_uses">Máximo de usos</label>
                                    <input type="number" id="max_uses" name="max_uses" min="0"
                                        class="form-control @error('max_uses') is-invalid @enderror"
                                        placeholder="Ilimitado" value="{{ old('max_uses') }}">
                                    <span class="field-hint">
                                        <i class="fa-solid fa-circle-info"></i> Vacío = sin límite de activaciones.
                                    </span>
                                    @error('max_uses')
                                        <span class="error-message">
                                            <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group cf-span">
                                    <label for="description">Descripción</label>
                                    <textarea id="description" name="description" rows="4"
                                        class="form-control @error('description') is-invalid @enderror"
                                        placeholder="Detalla la oferta: qué incluye, dónde canjearla…">{{ old('description') }}</textarea>
                                    @error('description')
                                        <span class="error-message">
                                            <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group cf-span">
                                    <label for="terms_conditions">Términos y condiciones</label>
                                    <textarea id="terms_conditions" name="terms_conditions" rows="3"
                                        class="form-control @error('terms_conditions') is-invalid @enderror"
                                        placeholder="Restricciones, vigencia, sucursales participantes…">{{ old('terms_conditions') }}</textarea>
                                    @error('terms_conditions')
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
                                <p>Decide si la promoción está visible y si se destaca en la portada.</p>
                            </div>
                        </div>

                        <div class="cf-card__body">
                            <div class="cf-switches">
                                <label class="cf-switch" for="is_active">
                                    <input type="checkbox" name="is_active" id="is_active" value="1"
                                        {{ old('is_active', true) ? 'checked' : '' }}>
                                    <span class="cf-switch__track" aria-hidden="true"></span>
                                    <span class="cf-switch__text">
                                        <strong>Promoción activa</strong>
                                        <span>Las promociones activas se muestran en la app.</span>
                                    </span>
                                </label>

                                <label class="cf-switch" for="is_featured">
                                    <input type="checkbox" name="is_featured" id="is_featured" value="1"
                                        {{ old('is_featured') ? 'checked' : '' }}>
                                    <span class="cf-switch__track" aria-hidden="true"></span>
                                    <span class="cf-switch__text">
                                        <strong>Promoción destacada</strong>
                                        <span>Aparece primero y con la etiqueta «Destacada».</span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </section>
                </div>

                {{-- ----------------------------- Aside ------------------------------ --}}
                <aside class="cf-aside">

                    {{-- Previsualización --}}
                    <div class="cf-preview cf-preview--promo" data-cf-preview>
                        <div class="cf-preview__head">
                            <strong><i class="fa-regular fa-eye"></i> Vista previa</strong>
                            <span class="cm-chip">Promoción</span>
                        </div>

                        <div class="cf-preview__media">
                            <img data-cf-preview-image data-img alt="" hidden>

                            <div class="cf-preview__empty" data-cf-preview-empty>
                                <i class="fa-regular fa-image"></i>
                                <span>La imagen se verá aquí</span>
                            </div>

                            <span class="cf-preview__flag" data-cf-preview="featured" hidden>
                                <i class="fa-solid fa-star"></i> Destacada
                            </span>

                            <span class="cm-card__state is-on" data-cf-preview="status">
                                <i class="fa-solid fa-circle-check"></i>
                                <span>Activa</span>
                            </span>

                            <span class="cf-preview__discount" data-cf-preview="discount" hidden>-20%</span>
                        </div>

                        <div class="cf-preview__body">
                            <h3 class="cf-preview__title" data-cf-preview="title"
                                data-empty="Título de la promoción">Título de la promoción</h3>
                            <p class="cf-preview__desc" data-cf-preview="desc"
                                data-empty="Aquí se verá la descripción que escribas.">Aquí se verá la descripción que escribas.</p>

                            <div class="cf-preview__meta">
                                <span class="cm-chip cm-chip--primary" data-cf-preview="price" hidden>$ 0</span>
                                <span class="cm-chip" data-cf-preview="expires">Sin caducidad</span>
                            </div>

                            <a class="cf-preview__cta" href="#" tabindex="-1" aria-hidden="true">
                                <i class="fa-solid fa-ticket"></i>
                                <span>Ver promoción</span>
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
                                <li><i class="fa-solid fa-check"></i> Títulos cortos: «2x1 en cócteles» funciona mejor.</li>
                                <li><i class="fa-solid fa-check"></i> Una fecha de vigencia clara evita reclamos.</li>
                                <li><i class="fa-solid fa-check"></i> Destaca solo las ofertas realmente fuertes.</li>
                                <li><i class="fa-solid fa-check"></i> La imagen debe verse bien en el móvil.</li>
                            </ul>
                        </div>
                    </div>
                </aside>
            </div>

            {{-- ------------------------------ Acciones ------------------------------ --}}
            <div class="cf-actions">
                <a href="{{ route('admin.promotions.index') }}" class="btn btn-secondary">
                    <i class="fa-solid fa-xmark"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary" data-cf-submit>
                    <i class="fa-solid fa-floppy-disk"></i> Crear promoción
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin-forms.js') }}"></script>
@endpush
