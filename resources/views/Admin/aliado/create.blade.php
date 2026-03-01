@extends('layouts.admin')

@section('title', 'Registrar Nuevo Aliado')

@section('page_title_toolbar', 'Registrar Nuevo Aliado')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/aliado-create.css') }}">
@endpush

@section('content')
    <div class="aliado-create-wrapper">
        {{-- Header con bienvenida --}}
        <div class="create-header-bar">
            <div class="header-left">
                <a href="{{ route('admin.aliados.index') }}" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                    <span>Volver al Listado</span>
                </a>
                <div class="page-title">
                    <span class="title-main">Registrar</span>
                    <span class="title-accent">Aliado Comercial</span>
                </div>
            </div>
            <div class="header-actions">
                <div class="user-greeting">
                    <span>Creando,</span>
                    <strong>{{ Auth::user()->name }}</strong>
                </div>
                <div class="avatar-circle">
                    <span>{{ substr(Auth::user()->name, 0, 1) }}</span>
                </div>
            </div>
        </div>

        {{-- Barra de estado inicial --}}
        <div class="status-info-bar">
            <div class="info-content">
                <div class="info-icon">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="info-details">
                    <span class="info-label">Estado inicial:</span>
                    <span class="status-badge pendiente">
                        <i class="fas fa-clock"></i>
                        Pendiente
                    </span>
                    <span class="info-help" id="statusHelp">
                        Los aliados pendientes requieren activación manual
                    </span>
                </div>
            </div>
        </div>

        {{-- Alertas --}}
        @if ($errors->any())
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <div class="alert-content">
                    <strong>¡Atención!</strong> Se encontraron errores en el formulario:
                    <ul class="error-list">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button class="alert-close">&times;</button>
            </div>
        @endif

        {{-- Progreso del formulario --}}
        <div class="form-progress">
            <div class="progress-steps">
                <div class="step active" data-step="1">
                    <div class="step-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <span class="step-label">General</span>
                </div>
                <div class="step" data-step="2">
                    <div class="step-icon">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <span class="step-label">Negocio</span>
                </div>
                <div class="step" data-step="3">
                    <div class="step-icon">
                        <i class="fas fa-address-book"></i>
                    </div>
                    <span class="step-label">Contacto</span>
                </div>
                <div class="step" data-step="4">
                    <div class="step-icon">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <span class="step-label">Cuenta</span>
                </div>
                <div class="step" data-step="5">
                    <div class="step-icon">
                        <i class="fas fa-image"></i>
                    </div>
                    <span class="step-label">Multimedia</span>
                </div>
            </div>
        </div>

        {{-- Tarjeta principal del formulario --}}
        <div class="form-main-card">
            <form id="createAllyForm" action="{{ route('admin.aliados.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- Navegación entre pasos --}}
                <div class="step-navigation">
                    <button type="button" class="nav-btn prev" disabled>
                        <i class="fas fa-chevron-left"></i>
                        Anterior
                    </button>
                    <div class="step-indicator">
                        <span class="current-step">Paso 1 de 5</span>
                        <span class="step-name">Información General</span>
                    </div>
                    <button type="button" class="nav-btn next">
                        Siguiente
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>

                {{-- Sección 1: Información General --}}
                <div class="form-step active" data-step="1">
                    <div class="step-header">
                        <h3 class="step-title">
                            <i class="fas fa-building"></i>
                            Información General del Aliado
                        </h3>
                        <p class="step-description">Datos básicos e identificación del aliado comercial</p>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="company_name">
                                <i class="fas fa-building"></i>
                                Nombre de la Empresa <span class="required">*</span>
                            </label>
                            <input type="text" id="company_name" name="company_name" 
                                   value="{{ old('company_name') }}" 
                                   placeholder="Ej: Eventos Rumberos C.A." required>
                            @error('company_name')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="company_rif">
                                <i class="fas fa-id-card"></i>
                                RIF de la Empresa
                            </label>
                            <input type="text" id="company_rif" name="company_rif" 
                                   value="{{ old('company_rif') }}" 
                                   placeholder="Ej: J-12345678-9">
                            @error('company_rif')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="status">
                                <i class="fas fa-toggle-on"></i>
                                Estado del Aliado <span class="required">*</span>
                            </label>
                            <div class="select-wrapper">
                                <select id="status" name="status" required onchange="updateStatusPreview(this.value)">
                                    <option value="pendiente" {{ old('status') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                    <option value="activo" {{ old('status') == 'activo' ? 'selected' : '' }}>Activo</option>
                                    <option value="inactivo" {{ old('status') == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                                </select>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            @error('status')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="registered_at">
                                <i class="fas fa-calendar-alt"></i>
                                Fecha de Registro <span class="required">*</span>
                            </label>
                            <input type="date" id="registered_at" name="registered_at" 
                                   value="{{ old('registered_at', date('Y-m-d')) }}" required>
                            @error('registered_at')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group full-width">
                            <label for="description">
                                <i class="fas fa-align-left"></i>
                                Descripción del Aliado
                            </label>
                            <textarea id="description" name="description" rows="4" 
                                      placeholder="Breve descripción del aliado y los servicios que ofrece...">{{ old('description') }}</textarea>
                            <div class="char-counter">
                                <span id="description-counter">0</span>/500
                            </div>
                            @error('description')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Sección 2: Información de Negocio --}}
                <div class="form-step" data-step="2">
                    <div class="step-header">
                        <h3 class="step-title">
                            <i class="fas fa-briefcase"></i>
                            Información del Negocio
                        </h3>
                        <p class="step-description">Categorías y tipo de negocio del aliado</p>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="category_name">
                                <i class="fas fa-layer-group"></i>
                                Categoría Principal <span class="required">*</span>
                            </label>
                            <div class="select-wrapper">
                                <select id="category_name" name="category_name" required onchange="updateCategoryPreview()">
                                    <option value="">Selecciona una categoría</option>
                                    <option value="Restaurantes, Bares, Discotecas, Night Club, Juegos" {{ old('category_name') == 'Restaurantes, Bares, Discotecas, Night Club, Juegos' ? 'selected' : '' }}>
                                        Restaurantes, Bares, Discotecas, Night Club, Juegos
                                    </option>
                                    <option value="Comidas, Bebidas, Cafes, Heladerias, Panaderias, Pastelerias" {{ old('category_name') == 'Comidas, Bebidas, Cafes, Heladerias, Panaderias, Pastelerias' ? 'selected' : '' }}>
                                        Comidas, Bebidas, Cafés, Heladerías, Panaderías, Pastelerías
                                    </option>
                                    <option value="Deportes y Hobbies" {{ old('category_name') == 'Deportes y Hobbies' ? 'selected' : '' }}>
                                        Deportes y Hobbies
                                    </option>
                                    <option value="Viajes y Turismo" {{ old('category_name') == 'Viajes y Turismo' ? 'selected' : '' }}>
                                        Viajes y Turismo
                                    </option>
                                    <option value="Eventos y Festejos" {{ old('category_name') == 'Eventos y Festejos' ? 'selected' : '' }}>
                                        Eventos y Festejos
                                    </option>
                                </select>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            @error('category_name')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="sub_category_name">
                                <i class="fas fa-sitemap"></i>
                                Subcategoría
                            </label>
                            <input type="text" id="sub_category_name" name="sub_category_name" 
                                   value="{{ old('sub_category_name') }}" 
                                   placeholder="Ej: Comida Rápida, Rock, Ropa Casual"
                                   oninput="updateCategoryPreview()">
                            @error('sub_category_name')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="business_type_name">
                                <i class="fas fa-store"></i>
                                Tipo de Negocio <span class="required">*</span>
                            </label>
                            <div class="select-wrapper">
                                <select id="business_type_name" name="business_type_name" required>
                                    <option value="">Selecciona un tipo</option>
                                    <option value="Fisico" {{ old('business_type_name') == 'Fisico' ? 'selected' : '' }}>Físico</option>
                                    <option value="Online" {{ old('business_type_name') == 'Online' ? 'selected' : '' }}>Online</option>
                                    <option value="Servicio a domicilio" {{ old('business_type_name') == 'Servicio a domicilio' ? 'selected' : '' }}>Servicio a domicilio</option>
                                </select>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            @error('business_type_name')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="discount">
                                <i class="fas fa-percentage"></i>
                                Oferta de Descuento
                            </label>
                            <input type="text" id="discount" name="discount" 
                                   value="{{ old('discount') }}" 
                                   placeholder="Ej: 15% en alquiler de equipos">
                            @error('discount')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group full-width">
                            <div class="category-preview">
                                <span class="preview-label">Vista previa:</span>
                                <div class="preview-content">
                                    <span class="preview-category" id="preview-category">
                                        Sin categoría
                                    </span>
                                    <i class="fas fa-arrow-right"></i>
                                    <span class="preview-subcategory" id="preview-subcategory">
                                        Sin subcategoría
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sección 3: Información de Contacto --}}
                <div class="form-step" data-step="3">
                    <div class="step-header">
                        <h3 class="step-title">
                            <i class="fas fa-address-book"></i>
                            Información de Contacto
                        </h3>
                        <p class="step-description">Datos de contacto del representante del aliado</p>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="contact_person_name">
                                <i class="fas fa-user-tie"></i>
                                Persona de Contacto <span class="required">*</span>
                            </label>
                            <input type="text" id="contact_person_name" name="contact_person_name" 
                                   value="{{ old('contact_person_name') }}" 
                                   placeholder="Ej: Ana García" required>
                            @error('contact_person_name')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="contact_email">
                                <i class="fas fa-envelope"></i>
                                Correo Electrónico <span class="required">*</span>
                            </label>
                            <input type="email" id="contact_email" name="contact_email" 
                                   value="{{ old('contact_email') }}" 
                                   placeholder="Ej: contacto@empresa.com" required>
                            @error('contact_email')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="contact_phone">
                                <i class="fas fa-phone"></i>
                                Teléfono Principal <span class="required">*</span>
                            </label>
                            <input type="tel" id="contact_phone" name="contact_phone" 
                                   value="{{ old('contact_phone') }}" 
                                   placeholder="Ej: +58 412 1234567" required>
                            @error('contact_phone')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="contact_phone_alt">
                                <i class="fas fa-phone-alt"></i>
                                Teléfono Adicional
                            </label>
                            <input type="tel" id="contact_phone_alt" name="contact_phone_alt" 
                                   value="{{ old('contact_phone_alt') }}" 
                                   placeholder="Ej: +58 212 9876543">
                            @error('contact_phone_alt')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group full-width">
                            <label for="company_address">
                                <i class="fas fa-map-marker-alt"></i>
                                Dirección Fiscal / Oficina
                            </label>
                            <textarea id="company_address" name="company_address" rows="3" 
                                      placeholder="Ej: Av. Libertador, Edif. Caracas, Piso 10, Ofic. 10B, Caracas, Venezuela">{{ old('company_address') }}</textarea>
                            @error('company_address')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="website_url">
                                <i class="fas fa-globe"></i>
                                Sitio Web
                            </label>
                            <input type="url" id="website_url" name="website_url" 
                                   value="{{ old('website_url') }}" 
                                   placeholder="Ej: https://www.empresa.com">
                            @error('website_url')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Sección 4: Cuenta de Usuario --}}
                <div class="form-step" data-step="4">
                    <div class="step-header">
                        <h3 class="step-title">
                            <i class="fas fa-user-circle"></i>
                            Cuenta de Acceso
                        </h3>
                        <p class="step-description">Credenciales para el acceso al sistema del aliado</p>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="user_name">
                                <i class="fas fa-user"></i>
                                Nombre de Usuario <span class="required">*</span>
                            </label>
                            <input type="text" id="user_name" name="user_name" 
                                   value="{{ old('user_name') }}" 
                                   placeholder="Ej: Juan Pérez" required>
                            @error('user_name')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="user_email">
                                <i class="fas fa-at"></i>
                                Correo Electrónico de Acceso <span class="required">*</span>
                            </label>
                            <input type="email" id="user_email" name="user_email" 
                                   value="{{ old('user_email') }}" 
                                   placeholder="Ej: usuario@rumbero.app" required>
                            @error('user_email')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="user_password">
                                <i class="fas fa-lock"></i>
                                Contraseña <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="password" id="user_password" name="user_password" 
                                       placeholder="Mínimo 8 caracteres" required>
                                <button type="button" class="toggle-password" data-target="user_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="password-strength" id="passwordStrength">
                                <div class="strength-bar"></div>
                                <span class="strength-text">Seguridad de la contraseña</span>
                            </div>
                            @error('user_password')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="user_password_confirmation">
                                <i class="fas fa-lock"></i>
                                Confirmar Contraseña <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="password" id="user_password_confirmation" name="user_password_confirmation" 
                                       placeholder="Repita la contraseña" required>
                                <button type="button" class="toggle-password" data-target="user_password_confirmation">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="password-match" id="passwordMatch" style="display: none;">
                                <i class="fas fa-check-circle"></i>
                                <span>Las contraseñas coinciden</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sección 5: Multimedia --}}
                <div class="form-step" data-step="5">
                    <div class="step-header">
                        <h3 class="step-title">
                            <i class="fas fa-image"></i>
                            Multimedia del Aliado
                        </h3>
                        <p class="step-description">Imágenes representativas del aliado</p>
                    </div>

                    <div class="multimedia-section">
                        {{-- Logo principal --}}
                        <div class="subsection">
                            <h4 class="subsection-title">
                                <i class="fas fa-camera"></i>
                                Logo o Imagen Principal
                            </h4>
                            
                            <div class="image-upload-container">
                                <div class="upload-area" id="uploadArea">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span class="upload-text">Seleccionar logo</span>
                                    <span class="upload-hint">Arrastra o haz clic para subir</span>
                                </div>
                                <input type="file" id="image_url" name="image_url" class="file-input" accept="image/*">
                                <div id="uploadPreview" class="upload-preview"></div>
                                <p class="help-text">
                                    <i class="fas fa-info-circle"></i>
                                    Formatos: JPEG, PNG, JPG, GIF, SVG. Máx: 2MB
                                </p>
                                @error('image_url')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Galería de productos --}}
                        <div class="subsection">
                            <h4 class="subsection-title">
                                <i class="fas fa-images"></i>
                                Galería de Productos
                            </h4>
                            <p class="subsection-description">
                                Sube hasta 5 imágenes de productos destacados o menús
                            </p>

                            <div class="gallery-container" id="galleryContainer">
                                <div class="gallery-grid" id="galleryGrid"></div>
                                
                                <input type="file" id="product_images" name="product_images[]" 
                                       class="gallery-file-input" accept="image/*" multiple style="display: none;">
                                
                                <div class="gallery-controls">
                                    <button type="button" class="gallery-add-btn" id="addGalleryImages">
                                        <i class="fas fa-plus-circle"></i>
                                        Agregar Imágenes
                                    </button>
                                    <span class="gallery-counter" id="galleryCounter">0/5</span>
                                </div>

                                <div class="gallery-info">
                                    <i class="fas fa-info-circle"></i>
                                    <span>Formatos: JPEG, PNG, JPG. Máx por imagen: 2MB</span>
                                </div>

                                <div class="gallery-preview">
                                    <h5 class="preview-title">
                                        <i class="fas fa-eye"></i>
                                        Vista Previa
                                    </h5>
                                    <div class="preview-grid" id="galleryPreview">
                                        <div class="empty-preview">
                                            <i class="fas fa-images"></i>
                                            <p>No hay imágenes seleccionadas</p>
                                        </div>
                                    </div>
                                </div>

                                @error('product_images')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Botones de acción --}}
                <div class="form-actions">
                    <a href="{{ route('admin.aliados.index') }}" class="btn-cancel">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </a>
                    <button type="button" class="btn-prev-final" style="display: none;">
                        <i class="fas fa-chevron-left"></i>
                        Anterior
                    </button>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-plus-circle"></i>
                        Crear Aliado
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ========== CERRAR ALERTAS ==========
        document.querySelectorAll('.alert-close').forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('.alert').remove();
            });
        });

        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => alert.remove());
        }, 5000);

        // ========== TOGGLE DE VISIBILIDAD DE CONTRASEÑA ==========
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.dataset.target;
                const input = document.getElementById(targetId);
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.className = 'fas fa-eye-slash';
                } else {
                    input.type = 'password';
                    icon.className = 'fas fa-eye';
                }
            });
        });

        // ========== NAVEGACIÓN POR PASOS ==========
        const steps = document.querySelectorAll('.form-step');
        const progressSteps = document.querySelectorAll('.step');
        const prevBtn = document.querySelector('.nav-btn.prev');
        const nextBtn = document.querySelector('.nav-btn.next');
        const prevFinalBtn = document.querySelector('.btn-prev-final');
        let currentStep = 0;

        function updateStepNavigation() {
            steps.forEach((step, index) => {
                step.classList.toggle('active', index === currentStep);
            });

            progressSteps.forEach((step, index) => {
                step.classList.toggle('active', index === currentStep);
            });

            prevBtn.disabled = currentStep === 0;
            nextBtn.style.display = currentStep < steps.length - 1 ? 'inline-flex' : 'none';
            prevFinalBtn.style.display = currentStep === steps.length - 1 ? 'inline-flex' : 'none';

            document.querySelector('.current-step').textContent = `Paso ${currentStep + 1} de ${steps.length}`;
            const stepNames = ['General', 'Negocio', 'Contacto', 'Cuenta', 'Multimedia'];
            document.querySelector('.step-name').textContent = stepNames[currentStep];
        }

        function validateCurrentStep() {
            const currentSection = steps[currentStep];
            const requiredFields = currentSection.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = '#ef4444';
                    
                    let errorMsg = field.parentNode.querySelector('.field-error');
                    if (!errorMsg) {
                        errorMsg = document.createElement('span');
                        errorMsg.className = 'field-error';
                        errorMsg.textContent = 'Este campo es obligatorio';
                        field.parentNode.appendChild(errorMsg);
                    }
                } else {
                    field.style.borderColor = '';
                    const errorMsg = field.parentNode.querySelector('.field-error');
                    if (errorMsg) errorMsg.remove();
                }
            });

            if (!isValid) {
                const firstError = currentSection.querySelector('[required]:invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            }

            return isValid;
        }

        prevBtn.addEventListener('click', () => {
            if (currentStep > 0) {
                currentStep--;
                updateStepNavigation();
            }
        });

        nextBtn.addEventListener('click', () => {
            if (validateCurrentStep() && currentStep < steps.length - 1) {
                currentStep++;
                updateStepNavigation();
            }
        });

        prevFinalBtn.addEventListener('click', () => {
            if (currentStep > 0) {
                currentStep--;
                updateStepNavigation();
            }
        });

        progressSteps.forEach((step, index) => {
            step.addEventListener('click', () => {
                if (index <= currentStep) {
                    currentStep = index;
                    updateStepNavigation();
                }
            });
        });

        // ========== CONTADOR DE CARACTERES ==========
        const description = document.getElementById('description');
        const descCounter = document.getElementById('description-counter');

        if (description && descCounter) {
            descCounter.textContent = description.value.length;
            description.addEventListener('input', () => {
                descCounter.textContent = description.value.length;
            });
        }

        // ========== VISTA PREVIA DE CATEGORÍAS ==========
        const categorySelect = document.getElementById('category_name');
        const subcategoryInput = document.getElementById('sub_category_name');
        const previewCategory = document.getElementById('preview-category');
        const previewSubcategory = document.getElementById('preview-subcategory');

        window.updateCategoryPreview = function() {
            if (previewCategory) {
                previewCategory.textContent = categorySelect.options[categorySelect.selectedIndex]?.text.split(' (')[0] || 'Sin categoría';
            }
            if (previewSubcategory) {
                previewSubcategory.textContent = subcategoryInput.value || 'Sin subcategoría';
            }
        };

        // ========== PREVIEW DE ESTADO ==========
        window.updateStatusPreview = function(status) {
            const statusBadge = document.querySelector('.status-badge');
            const statusHelp = document.getElementById('statusHelp');
            
            statusBadge.className = `status-badge ${status}`;
            
            const icons = {
                'pendiente': 'fa-clock',
                'activo': 'fa-check',
                'inactivo': 'fa-pause'
            };
            
            const texts = {
                'pendiente': 'Pendiente',
                'activo': 'Activo',
                'inactivo': 'Inactivo'
            };
            
            const helps = {
                'pendiente': 'Los aliados pendientes requieren activación manual',
                'activo': 'Los aliados activos pueden acceder inmediatamente al sistema',
                'inactivo': 'Los aliados inactivos no podrán acceder al sistema'
            };
            
            statusBadge.innerHTML = `<i class="fas ${icons[status]}"></i> ${texts[status]}`;
            if (statusHelp) statusHelp.textContent = helps[status];
        };

        // ========== VALIDACIÓN DE CONTRASEÑA ==========
        const passwordInput = document.getElementById('user_password');
        const passwordConfirm = document.getElementById('user_password_confirmation');
        const passwordStrength = document.getElementById('passwordStrength');
        const passwordMatch = document.getElementById('passwordMatch');

        function checkPasswordStrength(password) {
            let strength = 0;
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/\d/)) strength++;
            if (password.match(/[^a-zA-Z\d]/)) strength++;
            return strength;
        }

        function updatePasswordStrength() {
            const password = passwordInput.value;
            const strength = checkPasswordStrength(password);
            const strengthBar = passwordStrength.querySelector('.strength-bar');
            const strengthText = passwordStrength.querySelector('.strength-text');
            
            strengthBar.className = 'strength-bar';
            strengthBar.classList.add(`strength-${strength}`);
            
            const messages = ['Muy débil', 'Débil', 'Regular', 'Fuerte', 'Muy fuerte'];
            strengthText.textContent = messages[strength] || 'Seguridad de la contraseña';
        }

        function checkPasswordMatch() {
            const password = passwordInput.value;
            const confirm = passwordConfirm.value;
            
            if (confirm.length === 0) {
                passwordMatch.style.display = 'none';
            } else if (password === confirm) {
                passwordMatch.style.display = 'flex';
                passwordMatch.style.color = '#10b981';
                passwordMatch.querySelector('span').textContent = 'Las contraseñas coinciden';
            } else {
                passwordMatch.style.display = 'flex';
                passwordMatch.style.color = '#ef4444';
                passwordMatch.querySelector('span').textContent = 'Las contraseñas no coinciden';
            }
        }

        if (passwordInput && passwordConfirm) {
            passwordInput.addEventListener('input', updatePasswordStrength);
            passwordInput.addEventListener('input', checkPasswordMatch);
            passwordConfirm.addEventListener('input', checkPasswordMatch);
        }

        // ========== UPLOAD DE IMAGEN PRINCIPAL ==========
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('image_url');
        const uploadPreview = document.getElementById('uploadPreview');

        if (uploadArea && fileInput) {
            uploadArea.addEventListener('click', () => fileInput.click());

            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(event => {
                uploadArea.addEventListener(event, preventDefaults);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(event => {
                uploadArea.addEventListener(event, () => uploadArea.classList.add('highlight'));
            });

            ['dragleave', 'drop'].forEach(event => {
                uploadArea.addEventListener(event, () => uploadArea.classList.remove('highlight'));
            });

            uploadArea.addEventListener('drop', (e) => {
                const files = e.dataTransfer.files;
                fileInput.files = files;
                handleImageUpload(files[0]);
            });

            fileInput.addEventListener('change', () => {
                if (fileInput.files.length > 0) {
                    handleImageUpload(fileInput.files[0]);
                }
            });

            function handleImageUpload(file) {
                if (!file.type.startsWith('image/')) {
                    showAlert('El archivo debe ser una imagen', 'error');
                    return;
                }

                const reader = new FileReader();
                reader.onload = (e) => {
                    uploadPreview.innerHTML = `
                        <div class="preview-container">
                            <img src="${e.target.result}" alt="Preview">
                            <button type="button" class="preview-remove" onclick="removePreview()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `;
                    uploadArea.querySelector('.upload-text').textContent = 'Cambiar imagen';
                };
                reader.readAsDataURL(file);
            }
        }

        // ========== GALERÍA DE PRODUCTOS ==========
        let galleryItems = [];

        const galleryGrid = document.getElementById('galleryGrid');
        const galleryFileInput = document.getElementById('product_images');
        const addGalleryBtn = document.getElementById('addGalleryImages');
        const galleryCounter = document.getElementById('galleryCounter');
        const galleryPreview = document.getElementById('galleryPreview');
        const maxFiles = 5;

        if (addGalleryBtn && galleryFileInput) {
            addGalleryBtn.addEventListener('click', () => galleryFileInput.click());

            galleryFileInput.addEventListener('change', function() {
                const files = Array.from(this.files);
                if (galleryItems.length + files.length > maxFiles) {
                    showAlert(`Solo puedes agregar ${maxFiles - galleryItems.length} imágenes más`, 'error');
                    return;
                }

                files.forEach(file => {
                    if (!file.type.startsWith('image/')) {
                        showAlert(`${file.name} no es una imagen válida`, 'error');
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = (e) => {
                        galleryItems.push({
                            id: Date.now() + Math.random(),
                            file: file,
                            preview: e.target.result,
                            name: file.name,
                            size: formatFileSize(file.size)
                        });
                        updateGallery();
                    };
                    reader.readAsDataURL(file);
                });

                this.value = '';
            });
        }

        function updateGallery() {
            if (!galleryGrid || !galleryCounter || !galleryPreview) return;

            galleryGrid.innerHTML = galleryItems.map((item, index) => `
                <div class="gallery-item" data-id="${item.id}" draggable="true">
                    <img src="${item.preview}" alt="${item.name}" class="gallery-item-image">
                    <div class="gallery-item-actions">
                        <button type="button" class="gallery-action move" title="Arrastrar">
                            <i class="fas fa-arrows-alt"></i>
                        </button>
                        <button type="button" class="gallery-action delete" onclick="removeGalleryItem('${item.id}')" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <div class="gallery-item-info">
                        <div class="gallery-item-title">Imagen ${index + 1}</div>
                        <div class="gallery-item-size">${item.size}</div>
                    </div>
                </div>
            `).join('');

            galleryCounter.textContent = `${galleryItems.length}/${maxFiles}`;

            if (galleryItems.length === 0) {
                galleryPreview.innerHTML = `
                    <div class="empty-preview">
                        <i class="fas fa-images"></i>
                        <p>No hay imágenes seleccionadas</p>
                    </div>
                `;
            } else {
                galleryPreview.innerHTML = galleryItems.map((item, index) => `
                    <div class="preview-item">
                        <img src="${item.preview}" alt="Preview">
                        <span class="preview-number">${index + 1}</span>
                    </div>
                `).join('');
            }

            makeGalleryItemsDraggable();
        }

        function makeGalleryItemsDraggable() {
            const items = document.querySelectorAll('.gallery-item');
            let draggedItem = null;

            items.forEach(item => {
                item.addEventListener('dragstart', (e) => {
                    draggedItem = item;
                    item.classList.add('dragging');
                });

                item.addEventListener('dragend', () => {
                    item.classList.remove('dragging');
                    draggedItem = null;
                });

                item.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    if (draggedItem !== item) {
                        item.classList.add('drag-over');
                    }
                });

                item.addEventListener('dragleave', () => {
                    item.classList.remove('drag-over');
                });

                item.addEventListener('drop', (e) => {
                    e.preventDefault();
                    item.classList.remove('drag-over');
                    
                    if (draggedItem && draggedItem !== item) {
                        const draggedId = draggedItem.dataset.id;
                        const targetId = item.dataset.id;
                        
                        const draggedIndex = galleryItems.findIndex(i => i.id.toString() === draggedId);
                        const targetIndex = galleryItems.findIndex(i => i.id.toString() === targetId);
                        
                        const [dragged] = galleryItems.splice(draggedIndex, 1);
                        galleryItems.splice(targetIndex, 0, dragged);
                        
                        updateGallery();
                    }
                });
            });
        }

        window.removeGalleryItem = function(id) {
            galleryItems = galleryItems.filter(item => item.id.toString() !== id);
            updateGallery();
        };

        window.removePreview = function() {
            if (uploadPreview) uploadPreview.innerHTML = '';
            if (fileInput) fileInput.value = '';
            if (uploadArea) {
                uploadArea.querySelector('.upload-text').textContent = 'Seleccionar imagen';
            }
        };

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function showAlert(message, type = 'error') {
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.innerHTML = `
                <i class="fas fa-${type === 'error' ? 'exclamation-triangle' : 'check-circle'}"></i>
                <span>${message}</span>
                <button class="alert-close">&times;</button>
            `;
            document.querySelector('.aliado-create-wrapper').insertBefore(alert, document.querySelector('.form-progress'));
            
            alert.querySelector('.alert-close').addEventListener('click', () => alert.remove());
            setTimeout(() => alert.remove(), 5000);
        }

        // ========== INICIALIZACIÓN ==========
        updateStepNavigation();
        updateCategoryPreview();
        if (document.getElementById('status')) {
            updateStatusPreview(document.getElementById('status').value);
        }
    });
</script>
@endpush