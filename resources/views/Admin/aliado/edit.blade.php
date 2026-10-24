@extends('layouts.admin')

@section('title', 'Editar aliado')

@section('page_title_toolbar', 'Editar aliado')

@section('content')
    @php
        $existingGallery = collect($ally->product_images ?? [])->values();
        $registered = $ally->registered_at ?? $ally->created_at;
    @endphp

    <div class="cm-page cf-page">

        {{-- ============================ CABECERA ============================ --}}
        <div class="page-header-block">
            <div>
                <h1 class="title-main">Editar <span class="title-accent">aliado</span></h1>
                <p class="page-subtitle">
                    Actualiza los datos de «{{ $ally->company_name }}», su logo y su galería.
                </p>
            </div>

            <a href="{{ route('admin.aliados.show', $ally->id) }}" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-left"></i> Volver a la ficha
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

        @if (session('error'))
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div class="alert-content">{{ session('error') }}</div>
            </div>
        @endif

        {{-- ============================== FORM ============================== --}}
        <form id="editAllyForm" action="{{ route('admin.aliados.update', $ally->id) }}" method="POST"
            enctype="multipart/form-data" class="cf-editor" autocomplete="off">
            @csrf
            @method('PUT')

            <div class="cf-form">

                <div class="cf-col">

                    {{-- Empresa --}}
                    <section class="cf-card">
                        <div class="cf-card__head">
                            <span class="cf-card__icon"><i class="fa-solid fa-store"></i></span>
                            <div class="cf-card__head-text">
                                <h2>Datos de la empresa</h2>
                                <p>Información principal del negocio.</p>
                            </div>
                        </div>

                        <div class="cf-card__body">
                            <div class="cf-grid">
                                <div class="form-group">
                                    <label for="company_name">Nombre de la empresa <span class="required-star">*</span></label>
                                    <input type="text" id="company_name" name="company_name" maxlength="255"
                                        class="form-control @error('company_name') is-invalid @enderror"
                                        value="{{ old('company_name', $ally->company_name) }}" required>
                                    @error('company_name')
                                        <span class="error-message"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="company_rif">RIF</label>
                                    <input type="text" id="company_rif" name="company_rif" maxlength="255"
                                        class="form-control @error('company_rif') is-invalid @enderror"
                                        value="{{ old('company_rif', $ally->company_rif) }}">
                                    @error('company_rif')
                                        <span class="error-message"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="status">Estado <span class="required-star">*</span></label>
                                    <select id="status" name="status" class="form-control" required>
                                        <option value="activo" @selected(old('status', $ally->status) === 'activo')>Activo</option>
                                        <option value="pendiente" @selected(old('status', $ally->status) === 'pendiente')>Pendiente</option>
                                        <option value="inactivo" @selected(old('status', $ally->status) === 'inactivo')>Inactivo</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="registered_at">Fecha de registro <span class="required-star">*</span></label>
                                    <input type="date" id="registered_at" name="registered_at"
                                        class="form-control @error('registered_at') is-invalid @enderror"
                                        value="{{ old('registered_at', optional($registered)->format('Y-m-d')) }}" required>
                                    @error('registered_at')
                                        <span class="error-message"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group cf-span">
                                    <label for="description">Descripción</label>
                                    <textarea id="description" name="description" rows="3" maxlength="1000"
                                        class="form-control @error('description') is-invalid @enderror"
                                        placeholder="Qué ofrece el negocio…">{{ old('description', $ally->description) }}</textarea>
                                    @error('description')
                                        <span class="error-message"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- Clasificación --}}
                    <section class="cf-card">
                        <div class="cf-card__head">
                            <span class="cf-card__icon"><i class="fa-solid fa-tags"></i></span>
                            <div class="cf-card__head-text">
                                <h2>Clasificación</h2>
                                <p>Categoría, subcategoría y tipo de negocio.</p>
                            </div>
                        </div>

                        <div class="cf-card__body">
                            <div class="cf-grid">
                                <div class="form-group">
                                    <label for="category_name">Categoría <span class="required-star">*</span></label>
                                    <select id="category_name" name="category_name" class="form-control" required>
                                        <option value="">Selecciona una categoría</option>
                                        @foreach ([
                                            'Restaurantes, Bares, Discotecas, Night Club, Juegos',
                                            'Comidas, Bebidas, Cafes, Heladerias, Panaderias, Pastelerias',
                                            'Deportes y Hobbies',
                                            'Viajes y Turismo',
                                            'Eventos y Festejos',
                                            'Salud y Belleza',
                                        ] as $category)
                                            <option value="{{ $category }}"
                                                @selected(old('category_name', $ally->category->name ?? '') === $category)>{{ $category }}</option>
                                        @endforeach
                                    </select>
                                    @error('category_name')
                                        <span class="error-message"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="sub_category_name">Subcategoría</label>
                                    <input type="text" id="sub_category_name" name="sub_category_name" maxlength="255"
                                        class="form-control" value="{{ old('sub_category_name', $ally->subCategory->name ?? '') }}">
                                </div>

                                <div class="form-group">
                                    <label for="business_type_name">Tipo de negocio <span class="required-star">*</span></label>
                                    <select id="business_type_name" name="business_type_name" class="form-control" required>
                                        <option value="">Selecciona un tipo</option>
                                        @foreach (['Fisico' => 'Físico', 'Online' => 'Online', 'Servicio a domicilio' => 'Servicio a domicilio'] as $value => $label)
                                            <option value="{{ $value }}"
                                                @selected(old('business_type_name', $ally->businessType->name ?? '') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('business_type_name')
                                        <span class="error-message"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="discount">Descuento</label>
                                    <input type="text" id="discount" name="discount" maxlength="500" class="form-control"
                                        value="{{ old('discount', $ally->discount) }}">
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- Contacto --}}
                    <section class="cf-card">
                        <div class="cf-card__head">
                            <span class="cf-card__icon"><i class="fa-solid fa-address-book"></i></span>
                            <div class="cf-card__head-text">
                                <h2>Contacto</h2>
                                <p>Persona responsable y datos de atención.</p>
                            </div>
                        </div>

                        <div class="cf-card__body">
                            <div class="cf-grid">
                                <div class="form-group">
                                    <label for="contact_person_name">Persona de contacto <span class="required-star">*</span></label>
                                    <input type="text" id="contact_person_name" name="contact_person_name" maxlength="255"
                                        class="form-control @error('contact_person_name') is-invalid @enderror"
                                        value="{{ old('contact_person_name', $ally->contact_person_name) }}" required>
                                    @error('contact_person_name')
                                        <span class="error-message"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="contact_email">Correo de contacto <span class="required-star">*</span></label>
                                    <input type="email" id="contact_email" name="contact_email" maxlength="255"
                                        class="form-control @error('contact_email') is-invalid @enderror"
                                        value="{{ old('contact_email', $ally->contact_email) }}" required>
                                    @error('contact_email')
                                        <span class="error-message"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="contact_phone">Teléfono principal <span class="required-star">*</span></label>
                                    <input type="tel" id="contact_phone" name="contact_phone" maxlength="20"
                                        class="form-control @error('contact_phone') is-invalid @enderror"
                                        value="{{ old('contact_phone', $ally->contact_phone) }}" required>
                                    @error('contact_phone')
                                        <span class="error-message"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="contact_phone_alt">Teléfono alternativo</label>
                                    <input type="tel" id="contact_phone_alt" name="contact_phone_alt" maxlength="20"
                                        class="form-control" value="{{ old('contact_phone_alt', $ally->contact_phone_alt) }}">
                                </div>

                                <div class="form-group cf-span">
                                    <label for="company_address">Dirección</label>
                                    <textarea id="company_address" name="company_address" rows="2" maxlength="500"
                                        class="form-control">{{ old('company_address', $ally->company_address) }}</textarea>
                                </div>

                                <div class="form-group">
                                    <label for="latitude">Latitud</label>
                                    <input type="number" step="0.0000001" min="-90" max="90" id="latitude"
                                        name="latitude" class="form-control" placeholder="10.4941844"
                                        value="{{ old('latitude', $ally->latitude) }}">
                                </div>

                                <div class="form-group">
                                    <label for="longitude">Longitud</label>
                                    <input type="number" step="0.0000001" min="-180" max="180" id="longitude"
                                        name="longitude" class="form-control" placeholder="-66.8323390"
                                        value="{{ old('longitude', $ally->longitude) }}">
                                </div>

                                <div class="form-group cf-span">
                                    <label for="website_url">Sitio web o red social</label>
                                    <input type="url" id="website_url" name="website_url" maxlength="255"
                                        class="form-control @error('website_url') is-invalid @enderror"
                                        value="{{ old('website_url', $ally->website_url) }}">
                                    @error('website_url')
                                        <span class="error-message"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group cf-span">
                                    <label for="notes">Notas internas</label>
                                    <textarea id="notes" name="notes" rows="2" maxlength="500"
                                        class="form-control">{{ old('notes', $ally->notes) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- Multimedia --}}
                    <section class="cf-card">
                        <div class="cf-card__head">
                            <span class="cf-card__icon"><i class="fa-regular fa-images"></i></span>
                            <div class="cf-card__head-text">
                                <h2>Multimedia</h2>
                                <p>Logo del negocio y galería de productos.</p>
                            </div>
                        </div>

                        <div class="cf-card__body">
                            <div class="form-group">
                                <label for="image_url">Logo / imagen principal</label>
                                <div class="cf-upload {{ $ally->image_src ? 'has-file' : '' }}">
                                    <div class="cf-upload__thumb">
                                        <i class="fa-regular fa-image"></i>
                                        <img id="afMainPreview" data-img alt=""
                                            @if ($ally->image_src) src="{{ $ally->image_src }}" @else hidden @endif>
                                    </div>
                                    <div class="cf-upload__info">
                                        <span class="cf-upload__name">{{ $ally->image_url ? basename($ally->image_url) : 'Ningún archivo seleccionado' }}</span>
                                        <span class="cf-upload__hint">Deja el actual o sube uno nuevo · máx. 2 MB</span>
                                    </div>
                                    <div class="cf-upload__actions">
                                        <label for="image_url" class="btn btn-secondary btn-sm">
                                            <i class="fa-solid fa-arrow-up-from-bracket"></i> Cambiar logo
                                        </label>
                                    </div>
                                    <input type="file" id="image_url" name="image_url" accept="image/*">
                                </div>
                                @error('image_url')
                                    <span class="error-message"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span>
                                @enderror
                            </div>

                            @if ($existingGallery->isNotEmpty())
                                <div class="form-group">
                                    <label>Galería actual</label>
                                    <div class="af-gallery">
                                        @foreach ($existingGallery as $index => $path)
                                            <label class="af-gallery__item">
                                                <img src="{{ asset('storage/' . ltrim($path, '/')) }}"
                                                    alt="Imagen {{ $index + 1 }}" data-img>
                                                <input type="checkbox" name="deleted_images[]" value="{{ $index }}">
                                                <span class="af-gallery__check" title="Marcar para eliminar">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <span class="field-hint">Marca la papelera de una imagen para eliminarla al guardar.</span>
                                </div>
                            @endif

                            <div class="form-group">
                                <label for="product_images">Añadir imágenes</label>
                                <div class="cf-upload">
                                    <div class="cf-upload__thumb"><i class="fa-solid fa-plus"></i></div>
                                    <div class="cf-upload__info">
                                        <span class="cf-upload__name">Hasta 5 imágenes en total</span>
                                        <span class="cf-upload__hint">JPG, PNG o WEBP · máx. 2 MB cada una.</span>
                                    </div>
                                    <div class="cf-upload__actions">
                                        <label for="product_images" class="btn btn-secondary btn-sm">
                                            <i class="fa-solid fa-plus"></i> Seleccionar
                                        </label>
                                    </div>
                                    <input type="file" id="product_images" name="product_images[]" accept="image/*"
                                        multiple>
                                </div>
                                <div class="af-gallery" id="afGalleryPreview"></div>
                                @error('product_images')
                                    <span class="error-message"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </section>
                </div>

                {{-- ----------------------------- Aside ------------------------------ --}}
                <aside class="cf-aside">
                    <div class="cf-card">
                        <div class="cf-card__head">
                            <span class="cf-card__icon"><i class="fa-regular fa-address-card"></i></span>
                            <div class="cf-card__head-text">
                                <h2>Vista previa</h2>
                                <p>Así se verá el aliado en el listado.</p>
                            </div>
                        </div>

                        <div class="uf-preview__body">
                            <div class="uf-avatar">
                                <span id="afPreviewInitial">{{ mb_strtoupper(mb_substr($ally->company_name, 0, 1)) }}</span>
                            </div>
                            <h3 class="uf-name" id="afPreviewName">{{ $ally->company_name }}</h3>
                            <p class="uf-email" id="afPreviewType">{{ $ally->businessType->name ?? 'Sin tipo de negocio' }}</p>

                            <div class="uf-chips">
                                <span class="badge badge-info"
                                    id="afPreviewDiscount">{{ $ally->discount_label ?: 'Sin descuento' }}</span>
                                <span class="badge badge-success"
                                    id="afPreviewStatus">{{ ucfirst($ally->status ?: 'activo') }}</span>
                            </div>

                            <div class="uf-facts">
                                <span class="uf-fact"><i class="fa-solid fa-location-dot"></i><span
                                        id="afPreviewAddress">{{ $ally->company_address ?: 'Sin dirección' }}</span></span>
                                <span class="uf-fact"><i class="fa-solid fa-phone"></i><span
                                        id="afPreviewPhone">{{ $ally->contact_phone ?: 'Sin teléfono' }}</span></span>
                            </div>
                        </div>
                    </div>

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
                                <strong>#{{ $ally->id }}</strong>
                            </div>
                            <div class="cf-meta__row">
                                <span>Creado</span>
                                <strong>{{ optional($ally->created_at)->format('d/m/Y H:i') ?? '—' }}</strong>
                            </div>
                            <div class="cf-meta__row">
                                <span>Última edición</span>
                                <strong>{{ optional($ally->updated_at)->format('d/m/Y H:i') ?? '—' }}</strong>
                            </div>
                            <div class="cf-meta__row">
                                <span>Cuenta de acceso</span>
                                <strong>{{ $ally->user->email ?? 'Sin cuenta' }}</strong>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>

            <div class="cf-actions">
                <a href="{{ route('admin.aliados.show', $ally->id) }}" class="btn btn-secondary">
                    <i class="fa-solid fa-xmark"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary" id="afSubmit">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar cambios
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var name = document.getElementById('company_name');
            var type = document.getElementById('business_type_name');
            var discount = document.getElementById('discount');
            var status = document.getElementById('status');
            var address = document.getElementById('company_address');
            var phone = document.getElementById('contact_phone');

            var previewName = document.getElementById('afPreviewName');
            var previewType = document.getElementById('afPreviewType');
            var previewDiscount = document.getElementById('afPreviewDiscount');
            var previewStatus = document.getElementById('afPreviewStatus');
            var previewAddress = document.getElementById('afPreviewAddress');
            var previewPhone = document.getElementById('afPreviewPhone');
            var previewInitial = document.getElementById('afPreviewInitial');

            function syncPreview() {
                var value = name.value.trim();
                previewName.textContent = value || 'Nombre del aliado';
                previewInitial.textContent = (value.charAt(0) || '?').toUpperCase();
                previewType.textContent = type.value || 'Sin tipo de negocio';
                var discountValue = discount.value.trim();
                if (/^\d+([.,]\d+)?$/.test(discountValue)) discountValue += '%';
                previewDiscount.textContent = discountValue || 'Sin descuento';
                previewStatus.textContent = status.value.charAt(0).toUpperCase() + status.value.slice(1);
                previewAddress.textContent = address.value.trim() || 'Sin dirección';
                previewPhone.textContent = phone.value.trim() || 'Sin teléfono';
            }

            [name, type, discount, status, address, phone].forEach(function(field) {
                field.addEventListener('input', syncPreview);
                field.addEventListener('change', syncPreview);
            });

            var mainInput = document.getElementById('image_url');
            var mainPreview = document.getElementById('afMainPreview');

            mainInput.addEventListener('change', function() {
                var file = this.files && this.files[0];
                if (!file) return;

                if (file.size > 2 * 1024 * 1024) {
                    alert('La imagen supera los 2 MB.');
                    this.value = '';
                    return;
                }

                var reader = new FileReader();
                reader.onload = function(event) {
                    mainPreview.src = event.target.result;
                    mainPreview.hidden = false;
                };
                reader.readAsDataURL(file);
            });

            var galleryInput = document.getElementById('product_images');
            var galleryPreview = document.getElementById('afGalleryPreview');

            galleryInput.addEventListener('change', function() {
                galleryPreview.innerHTML = '';
                Array.from(this.files || []).slice(0, 5).forEach(function(file) {
                    var reader = new FileReader();
                    reader.onload = function(event) {
                        var item = document.createElement('div');
                        item.className = 'af-gallery__item';
                        var image = document.createElement('img');
                        image.src = event.target.result;
                        image.alt = file.name;
                        item.appendChild(image);
                        galleryPreview.appendChild(item);
                    };
                    reader.readAsDataURL(file);
                });
            });

            document.querySelectorAll('.af-gallery input[type="checkbox"]').forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    this.closest('.af-gallery__item').classList.toggle('is-marked', this.checked);
                });
            });

            syncPreview();
        });
    </script>
@endpush
