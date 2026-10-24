@extends('layouts.admin')

@section('title', 'Nuevo aliado')

@section('page_title_toolbar', 'Nuevo aliado')

@section('content')
    <div class="cm-page cf-page">

        {{-- ============================ CABECERA ============================ --}}
        <div class="page-header-block">
            <div>
                <h1 class="title-main">Nuevo <span class="title-accent">aliado</span></h1>
                <p class="page-subtitle">
                    Registra el negocio, su cuenta de acceso y su material multimedia.
                </p>
            </div>

            <a href="{{ route('admin.aliados.index') }}" class="btn btn-secondary">
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

        @if (session('error'))
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div class="alert-content">{{ session('error') }}</div>
            </div>
        @endif

        {{-- ============================== FORM ============================== --}}
        <form id="createAllyForm" action="{{ route('admin.aliados.store') }}" method="POST" enctype="multipart/form-data"
            class="cf-editor" autocomplete="off">
            @csrf

            <div class="cf-form">

                {{-- ------------------------- Columna principal ------------------------- --}}
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
                                        placeholder="Ej: Nando Burger" value="{{ old('company_name') }}" required>
                                    @error('company_name')
                                        <span class="error-message"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="company_rif">RIF</label>
                                    <input type="text" id="company_rif" name="company_rif" maxlength="255"
                                        class="form-control @error('company_rif') is-invalid @enderror"
                                        placeholder="Ej: J-123456789" value="{{ old('company_rif') }}">
                                    @error('company_rif')
                                        <span class="error-message"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="status">Estado <span class="required-star">*</span></label>
                                    <select id="status" name="status" class="form-control" required>
                                        <option value="activo" @selected(old('status', 'activo') === 'activo')>Activo</option>
                                        <option value="pendiente" @selected(old('status') === 'pendiente')>Pendiente</option>
                                        <option value="inactivo" @selected(old('status') === 'inactivo')>Inactivo</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="registered_at">Fecha de registro <span class="required-star">*</span></label>
                                    <input type="date" id="registered_at" name="registered_at"
                                        class="form-control @error('registered_at') is-invalid @enderror"
                                        value="{{ old('registered_at', \Carbon\Carbon::now()->format('Y-m-d')) }}" required>
                                    @error('registered_at')
                                        <span class="error-message"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group cf-span">
                                    <label for="description">Descripción</label>
                                    <textarea id="description" name="description" rows="3" maxlength="1000"
                                        class="form-control @error('description') is-invalid @enderror"
                                        placeholder="Qué ofrece el negocio, ambiente, especialidad…">{{ old('description') }}</textarea>
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
                                            <option value="{{ $category }}" @selected(old('category_name') === $category)>{{ $category }}</option>
                                        @endforeach
                                    </select>
                                    @error('category_name')
                                        <span class="error-message"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="sub_category_name">Subcategoría</label>
                                    <input type="text" id="sub_category_name" name="sub_category_name" maxlength="255"
                                        class="form-control" placeholder="Ej: Comida rápida, Rock, Coctelería"
                                        value="{{ old('sub_category_name') }}">
                                </div>

                                <div class="form-group">
                                    <label for="business_type_name">Tipo de negocio <span class="required-star">*</span></label>
                                    <select id="business_type_name" name="business_type_name" class="form-control" required>
                                        <option value="">Selecciona un tipo</option>
                                        @foreach (['Fisico' => 'Físico', 'Online' => 'Online', 'Servicio a domicilio' => 'Servicio a domicilio'] as $value => $label)
                                            <option value="{{ $value }}" @selected(old('business_type_name') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('business_type_name')
                                        <span class="error-message"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="discount">Descuento</label>
                                    <input type="text" id="discount" name="discount" maxlength="500"
                                        class="form-control" placeholder="Ej: 15% o 2x1 en cócteles"
                                        value="{{ old('discount') }}">
                                    <span class="field-hint"><i class="fa-solid fa-circle-info"></i> Puede ser un porcentaje o una promoción.</span>
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
                                        placeholder="Ej: Carlos Blanco" value="{{ old('contact_person_name') }}" required>
                                    @error('contact_person_name')
                                        <span class="error-message"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="contact_email">Correo de contacto <span class="required-star">*</span></label>
                                    <input type="email" id="contact_email" name="contact_email" maxlength="255"
                                        class="form-control @error('contact_email') is-invalid @enderror"
                                        placeholder="contacto@negocio.com" value="{{ old('contact_email') }}" required>
                                    @error('contact_email')
                                        <span class="error-message"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="contact_phone">Teléfono principal <span class="required-star">*</span></label>
                                    <input type="tel" id="contact_phone" name="contact_phone" maxlength="20"
                                        class="form-control @error('contact_phone') is-invalid @enderror"
                                        placeholder="Ej: +58 412 1234567" value="{{ old('contact_phone') }}" required>
                                    @error('contact_phone')
                                        <span class="error-message"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="contact_phone_alt">Teléfono alternativo</label>
                                    <input type="tel" id="contact_phone_alt" name="contact_phone_alt" maxlength="20"
                                        class="form-control" placeholder="Opcional" value="{{ old('contact_phone_alt') }}">
                                </div>

                                <div class="form-group cf-span">
                                    <label for="company_address">Dirección</label>
                                    <textarea id="company_address" name="company_address" rows="2" maxlength="500"
                                        class="form-control" placeholder="Calle, número, ciudad, estado">{{ old('company_address') }}</textarea>
                                </div>

                                <div class="form-group">
                                    <label for="latitude">Latitud</label>
                                    <input type="number" step="0.0000001" min="-90" max="90" id="latitude"
                                        name="latitude" class="form-control" placeholder="10.4941844"
                                        value="{{ old('latitude') }}">
                                </div>

                                <div class="form-group">
                                    <label for="longitude">Longitud</label>
                                    <input type="number" step="0.0000001" min="-180" max="180" id="longitude"
                                        name="longitude" class="form-control" placeholder="-66.8323390"
                                        value="{{ old('longitude') }}">
                                </div>

                                <div class="form-group cf-span">
                                    <label for="website_url">Sitio web o red social</label>
                                    <input type="url" id="website_url" name="website_url" maxlength="255"
                                        class="form-control @error('website_url') is-invalid @enderror"
                                        placeholder="https://instagram.com/tunegocio" value="{{ old('website_url') }}">
                                    @error('website_url')
                                        <span class="error-message"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- Cuenta --}}
                    <section class="cf-card">
                        <div class="cf-card__head">
                            <span class="cf-card__icon"><i class="fa-solid fa-user-lock"></i></span>
                            <div class="cf-card__head-text">
                                <h2>Cuenta de acceso</h2>
                                <p>Con estas credenciales el aliado entra al panel.</p>
                            </div>
                        </div>

                        <div class="cf-card__body">
                            <div class="cf-grid">
                                <div class="form-group">
                                    <label for="user_name">Nombre de usuario <span class="required-star">*</span></label>
                                    <input type="text" id="user_name" name="user_name" maxlength="255"
                                        class="form-control @error('user_name') is-invalid @enderror"
                                        placeholder="Ej: Nando Burger" value="{{ old('user_name') }}" required>
                                    @error('user_name')
                                        <span class="error-message"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="user_email">Correo de acceso <span class="required-star">*</span></label>
                                    <input type="email" id="user_email" name="user_email" maxlength="255"
                                        class="form-control @error('user_email') is-invalid @enderror"
                                        placeholder="acceso@negocio.com" value="{{ old('user_email') }}" required>
                                    @error('user_email')
                                        <span class="error-message"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="user_password">Contraseña <span class="required-star">*</span></label>
                                    <div class="uf-pass">
                                        <input type="password" id="user_password" name="user_password" minlength="8"
                                            class="form-control @error('user_password') is-invalid @enderror"
                                            placeholder="Mínimo 8 caracteres" required>
                                        <button type="button" class="uf-pass__toggle" data-target="user_password"
                                            aria-label="Mostrar contraseña">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="uf-strength">
                                        <span class="uf-strength__track"><span class="uf-strength__bar" id="afStrength"></span></span>
                                        <span class="uf-strength__label" id="afStrengthLabel">Sin contraseña</span>
                                    </div>
                                    @error('user_password')
                                        <span class="error-message"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="user_password_confirmation">Repetir contraseña <span class="required-star">*</span></label>
                                    <div class="uf-pass">
                                        <input type="password" id="user_password_confirmation"
                                            name="user_password_confirmation" class="form-control"
                                            placeholder="Repite la contraseña" required>
                                        <button type="button" class="uf-pass__toggle"
                                            data-target="user_password_confirmation" aria-label="Mostrar contraseña">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="uf-match" id="afMatch"><i class="fa-solid fa-circle"></i> Repite la contraseña</div>
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
                                <p>Logo del negocio y hasta 5 imágenes de productos.</p>
                            </div>
                        </div>

                        <div class="cf-card__body">
                            <div class="form-group">
                                <label for="image_url">Logo / imagen principal</label>
                                <div class="cf-upload" data-af-upload>
                                    <div class="cf-upload__thumb">
                                        <i class="fa-regular fa-image"></i>
                                        <img id="afMainPreview" data-img alt="" hidden>
                                    </div>
                                    <div class="cf-upload__info">
                                        <span class="cf-upload__name">Ningún archivo seleccionado</span>
                                        <span class="cf-upload__hint">JPG, PNG, GIF o WEBP · máx. 2 MB</span>
                                    </div>
                                    <div class="cf-upload__actions">
                                        <label for="image_url" class="btn btn-secondary btn-sm">
                                            <i class="fa-solid fa-arrow-up-from-bracket"></i> Subir logo
                                        </label>
                                    </div>
                                    <input type="file" id="image_url" name="image_url" accept="image/*"
                                        data-af-main>
                                </div>
                                @error('image_url')
                                    <span class="error-message"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="product_images">Galería de productos</label>
                                <div class="cf-upload">
                                    <div class="cf-upload__thumb"><i class="fa-solid fa-images"></i></div>
                                    <div class="cf-upload__info">
                                        <span class="cf-upload__name">Añade hasta 5 imágenes</span>
                                        <span class="cf-upload__hint">Se mostrarán en la ficha del aliado.</span>
                                    </div>
                                    <div class="cf-upload__actions">
                                        <label for="product_images" class="btn btn-secondary btn-sm">
                                            <i class="fa-solid fa-plus"></i> Añadir imágenes
                                        </label>
                                    </div>
                                    <input type="file" id="product_images" name="product_images[]" accept="image/*"
                                        multiple>
                                </div>
                                <div class="af-gallery" id="afGalleryPreview"></div>
                                <span class="field-hint" id="afGalleryCounter">0/5 imágenes</span>
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
                            <div class="uf-avatar"><span id="afPreviewInitial">?</span></div>
                            <h3 class="uf-name" id="afPreviewName">Nombre del aliado</h3>
                            <p class="uf-email" id="afPreviewType">Sin tipo de negocio</p>

                            <div class="uf-chips">
                                <span class="badge badge-info" id="afPreviewDiscount">Sin descuento</span>
                                <span class="badge badge-success" id="afPreviewStatus">Activo</span>
                            </div>

                            <div class="uf-facts">
                                <span class="uf-fact"><i class="fa-solid fa-location-dot"></i><span id="afPreviewAddress">Sin dirección</span></span>
                                <span class="uf-fact"><i class="fa-solid fa-phone"></i><span id="afPreviewPhone">Sin teléfono</span></span>
                            </div>
                        </div>
                    </div>

                    <div class="cf-card">
                        <div class="cf-card__head">
                            <span class="cf-card__icon"><i class="fa-solid fa-lightbulb"></i></span>
                            <div class="cf-card__head-text">
                                <h2>Buenas prácticas</h2>
                            </div>
                        </div>

                        <div class="cf-tips">
                            <ul>
                                <li><i class="fa-solid fa-check"></i> Usa el nombre comercial exacto del local.</li>
                                <li><i class="fa-solid fa-check"></i> Un logo cuadrado se ve mejor en la app.</li>
                                <li><i class="fa-solid fa-check"></i> El descuento puede ser porcentaje o promoción.</li>
                                <li><i class="fa-solid fa-check"></i> Guarda el correo de acceso del aliado.</li>
                            </ul>
                        </div>
                    </div>
                </aside>
            </div>

            <div class="cf-actions">
                <a href="{{ route('admin.aliados.index') }}" class="btn btn-secondary">
                    <i class="fa-solid fa-xmark"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary" id="afSubmit">
                    <i class="fa-solid fa-floppy-disk"></i> Crear aliado
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var form = document.getElementById('createAllyForm');

            // Vista previa de datos
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

            // Logo principal
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

            // Galería
            var galleryInput = document.getElementById('product_images');
            var galleryPreview = document.getElementById('afGalleryPreview');
            var galleryCounter = document.getElementById('afGalleryCounter');

            galleryInput.addEventListener('change', function() {
                galleryPreview.innerHTML = '';
                var files = Array.from(this.files || []).slice(0, 5);

                files.forEach(function(file) {
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

                galleryCounter.textContent = files.length + '/5 imágenes';
            });

            // Contraseña
            var password = document.getElementById('user_password');
            var confirmation = document.getElementById('user_password_confirmation');
            var strength = document.getElementById('afStrength');
            var strengthLabel = document.getElementById('afStrengthLabel');
            var match = document.getElementById('afMatch');

            document.querySelectorAll('.uf-pass__toggle').forEach(function(button) {
                button.addEventListener('click', function() {
                    var input = document.getElementById(button.getAttribute('data-target'));
                    if (!input) return;
                    var show = input.type === 'password';
                    input.type = show ? 'text' : 'password';
                    var icon = button.querySelector('i');
                    if (icon) icon.className = 'fa-solid ' + (show ? 'fa-eye-slash' : 'fa-eye');
                });
            });

            function updatePassword() {
                var value = password.value;
                var checks = [
                    value.length >= 8,
                    /[A-ZÁÉÍÓÚÜÑ]/.test(value),
                    /[a-záéíóúüñ]/.test(value),
                    /[0-9]/.test(value),
                    /[^A-Za-z0-9]/.test(value)
                ];
                var score = checks.filter(Boolean).length;
                var percent = value ? Math.round(score / 5 * 100) : 0;

                strength.style.width = percent + '%';
                strength.className = 'uf-strength__bar' +
                    (percent > 80 ? ' is-strong' : percent > 60 ? ' is-good' : percent > 40 ? ' is-fair' : '');
                strengthLabel.textContent = !value ? 'Sin contraseña' :
                    percent > 80 ? 'Muy fuerte' : percent > 60 ? 'Fuerte' : percent > 40 ? 'Buena' : 'Débil';
            }

            function updateMatch() {
                var a = password.value;
                var b = confirmation.value;

                if (!a && !b) {
                    match.className = 'uf-match';
                    match.innerHTML = '<i class="fa-solid fa-circle"></i> Repite la contraseña';
                    return;
                }

                var ok = a === b && a.length > 0;
                match.className = 'uf-match ' + (ok ? 'is-ok' : 'is-error');
                match.innerHTML = '<i class="fa-solid ' + (ok ? 'fa-circle-check' : 'fa-circle-xmark') + '"></i> ' +
                    (ok ? 'Las contraseñas coinciden' : 'Las contraseñas no coinciden');
            }

            password.addEventListener('input', function() {
                updatePassword();
                updateMatch();
            });
            confirmation.addEventListener('input', updateMatch);

            form.addEventListener('submit', function(event) {
                if (password.value.length < 8) {
                    event.preventDefault();
                    password.setCustomValidity('La contraseña debe tener al menos 8 caracteres.');
                    password.reportValidity();
                    return;
                }

                if (password.value !== confirmation.value) {
                    event.preventDefault();
                    confirmation.setCustomValidity('Las contraseñas no coinciden.');
                    confirmation.reportValidity();
                    return;
                }

                password.setCustomValidity('');
                confirmation.setCustomValidity('');
                document.getElementById('afSubmit').classList.add('is-loading');
            });

            syncPreview();
            updatePassword();
            updateMatch();
        });
    </script>
@endpush
