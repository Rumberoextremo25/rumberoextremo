{{-- resources/views/aliados/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Registrar Nuevo Aliado')

@section('page_title_toolbar', 'Registrar Nuevo Aliado')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/aliado-create.css') }}">
@endpush

@section('content')
    <div class="aliado-create-container">
        {{-- Header Moderno --}}
        <div class="create-header-modern">
            <div class="header-content">
                <div class="breadcrumb">
                    <a href="{{ route('aliados.index') }}" class="breadcrumb-link">
                        <i class="fas fa-arrow-left"></i>
                        Volver al Listado
                    </a>
                </div>
                <div class="title-section">
                    <h1 class="create-title">
                        <span class="title-prefix">Registrar Nuevo</span>
                        <span class="title-main">Aliado Comercial</span>
                    </h1>
                    <p class="page-subtitle">
                        <i class="fas fa-handshake"></i>
                        Completa la información para añadir un nuevo aliado al sistema
                    </p>
                </div>
            </div>
            <div class="header-actions">
                <div class="status-info">
                    <span class="status-label">Estado inicial:</span>
                    <span class="status-preview badge-status-pendiente">
                        <i class="fas fa-clock"></i>
                        Pendiente
                    </span>
                </div>
            </div>
        </div>

        {{-- Progreso del Formulario --}}
        <div class="form-progress-container">
            <div class="progress-steps">
                <div class="progress-step active" data-step="general">
                    <div class="step-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <span class="step-label">General</span>
                </div>
                <div class="progress-step" data-step="business">
                    <div class="step-icon">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <span class="step-label">Negocio</span>
                </div>
                <div class="progress-step" data-step="contact">
                    <div class="step-icon">
                        <i class="fas fa-address-book"></i>
                    </div>
                    <span class="step-label">Contacto</span>
                </div>
                <div class="progress-step" data-step="account">
                    <div class="step-icon">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <span class="step-label">Cuenta</span>
                </div>
                <div class="progress-step" data-step="media">
                    <div class="step-icon">
                        <i class="fas fa-image"></i>
                    </div>
                    <span class="step-label">Multimedia</span>
                </div>
            </div>
        </div>

        {{-- Alertas --}}
        @if ($errors->any())
            <div class="modern-alert error">
                <div class="alert-content">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <strong>¡Atención!</strong> Se encontraron errores en el formulario:
                        <ul class="error-list">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <button class="alert-close" onclick="this.parentElement.style.display='none'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif

        <div class="create-card-modern">
            <form id="createAllyForm" action="{{ route('aliados.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- Navegación entre Secciones --}}
                <div class="form-navigation">
                    <button type="button" class="nav-btn prev-btn" disabled>
                        <i class="fas fa-chevron-left"></i>
                        Anterior
                    </button>
                    <div class="nav-steps">
                        <span class="current-step">Paso 1 de 5</span>
                        <span class="step-title">Información General</span>
                    </div>
                    <button type="button" class="nav-btn next-btn">
                        Siguiente
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>

                {{-- Sección de Información General --}}
                <div class="form-section active" data-section="general">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-building"></i>
                            Información General del Aliado
                        </h3>
                        <p class="section-description">
                            Datos básicos e identificación del aliado comercial
                        </p>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="company_name" class="form-label">
                                <i class="fas fa-building"></i>
                                Nombre de la Empresa *
                            </label>
                            <input type="text" 
                                   id="company_name" 
                                   name="company_name" 
                                   class="form-control"
                                   placeholder="Ej: Eventos Rumberos C.A."
                                   value="{{ old('company_name') }}" 
                                   required>
                            @error('company_name')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="company_rif" class="form-label">
                                <i class="fas fa-id-card"></i>
                                RIF de la Empresa
                            </label>
                            <input type="text" 
                                   id="company_rif" 
                                   name="company_rif" 
                                   class="form-control"
                                   placeholder="Ej: J-12345678-9"
                                   value="{{ old('company_rif') }}">
                            @error('company_rif')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="status" class="form-label">
                                <i class="fas fa-toggle-on"></i>
                                Estado del Aliado *
                            </label>
                            <select id="status" 
                                    name="status" 
                                    class="form-control" 
                                    required
                                    onchange="updateStatusPreview(this.value)">
                                <option value="pendiente" {{ old('status') == 'pendiente' ? 'selected' : '' }}>
                                    Pendiente
                                </option>
                                <option value="activo" {{ old('status') == 'activo' ? 'selected' : '' }}>
                                    Activo
                                </option>
                                <option value="inactivo" {{ old('status') == 'inactivo' ? 'selected' : '' }}>
                                    Inactivo
                                </option>
                            </select>
                            <div class="status-help-text">
                                <i class="fas fa-info-circle"></i>
                                <span id="statusHelp">
                                    Los aliados pendientes requieren activación manual. Los inactivos no podrán acceder al sistema.
                                </span>
                            </div>
                            @error('status')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="registered_at" class="form-label">
                                <i class="fas fa-calendar-alt"></i>
                                Fecha de Registro *
                            </label>
                            <input type="date" 
                                   id="registered_at" 
                                   name="registered_at" 
                                   class="form-control"
                                   value="{{ old('registered_at', date('Y-m-d')) }}" 
                                   required>
                            @error('registered_at')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group full-width">
                            <label for="description" class="form-label">
                                <i class="fas fa-align-left"></i>
                                Descripción del Aliado
                            </label>
                            <textarea id="description" 
                                      name="description" 
                                      class="form-control"
                                      placeholder="Breve descripción del aliado y los servicios que ofrece..."
                                      rows="4">{{ old('description') }}</textarea>
                            <div class="char-counter">
                                <span id="description-counter">0</span>/500 caracteres
                            </div>
                            @error('description')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Sección de Información de Negocio --}}
                <div class="form-section" data-section="business">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-briefcase"></i>
                            Información del Negocio
                        </h3>
                        <p class="section-description">
                            Categorías y tipo de negocio del aliado
                        </p>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="category_name" class="form-label">
                                <i class="fas fa-layer-group"></i>
                                Categoría Principal *
                            </label>
                            <select id="category_name" 
                                    name="category_name" 
                                    class="form-control" 
                                    required
                                    onchange="updateCategoryPreview()">
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
                            @error('category_name')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="sub_category_name" class="form-label">
                                <i class="fas fa-sitemap"></i>
                                Subcategoría
                            </label>
                            <input type="text" 
                                   id="sub_category_name" 
                                   name="sub_category_name" 
                                   class="form-control"
                                   placeholder="Ej: Comida Rápida, Rock, Ropa Casual"
                                   value="{{ old('sub_category_name') }}"
                                   oninput="updateCategoryPreview()">
                            @error('sub_category_name')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="business_type_name" class="form-label">
                                <i class="fas fa-store"></i>
                                Tipo de Negocio *
                            </label>
                            <select id="business_type_name" 
                                    name="business_type_name" 
                                    class="form-control" 
                                    required>
                                <option value="">Selecciona un tipo de negocio</option>
                                <option value="Fisico" {{ old('business_type_name') == 'Fisico' ? 'selected' : '' }}>
                                    Físico
                                </option>
                                <option value="Online" {{ old('business_type_name') == 'Online' ? 'selected' : '' }}>
                                    Online
                                </option>
                                <option value="Servicio a domicilio" {{ old('business_type_name') == 'Servicio a domicilio' ? 'selected' : '' }}>
                                    Servicio a domicilio
                                </option>
                            </select>
                            @error('business_type_name')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="discount" class="form-label">
                                <i class="fas fa-percentage"></i>
                                Oferta de Descuento
                            </label>
                            <input type="text" 
                                   id="discount" 
                                   name="discount" 
                                   class="form-control"
                                   placeholder="Ej: 15% en alquiler de equipos, 2x1 en entradas"
                                   value="{{ old('discount') }}">
                            @error('discount')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group full-width">
                            <div class="category-preview">
                                <h4 class="preview-title">
                                    <i class="fas fa-eye"></i>
                                    Vista Previa de Categorías
                                </h4>
                                <div class="preview-content">
                                    <span class="preview-category" id="preview-category">
                                        Sin categoría
                                    </span>
                                    <span class="preview-separator">→</span>
                                    <span class="preview-subcategory" id="preview-subcategory">
                                        Sin subcategoría
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sección de Información de Contacto --}}
                <div class="form-section" data-section="contact">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-address-book"></i>
                            Información de Contacto
                        </h3>
                        <p class="section-description">
                            Datos de contacto del representante del aliado
                        </p>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="contact_person_name" class="form-label">
                                <i class="fas fa-user-tie"></i>
                                Persona de Contacto *
                            </label>
                            <input type="text" 
                                   id="contact_person_name" 
                                   name="contact_person_name" 
                                   class="form-control"
                                   placeholder="Ej: Ana García"
                                   value="{{ old('contact_person_name') }}" 
                                   required>
                            @error('contact_person_name')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="contact_email" class="form-label">
                                <i class="fas fa-envelope"></i>
                                Correo Electrónico *
                            </label>
                            <input type="email" 
                                   id="contact_email" 
                                   name="contact_email" 
                                   class="form-control"
                                   placeholder="Ej: contacto@empresa.com"
                                   value="{{ old('contact_email') }}" 
                                   required>
                            @error('contact_email')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="contact_phone" class="form-label">
                                <i class="fas fa-phone"></i>
                                Teléfono Principal *
                            </label>
                            <input type="tel" 
                                   id="contact_phone" 
                                   name="contact_phone" 
                                   class="form-control"
                                   placeholder="Ej: +58 412 1234567"
                                   value="{{ old('contact_phone') }}" 
                                   required>
                            @error('contact_phone')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="contact_phone_alt" class="form-label">
                                <i class="fas fa-phone-alt"></i>
                                Teléfono Adicional
                            </label>
                            <input type="tel" 
                                   id="contact_phone_alt" 
                                   name="contact_phone_alt" 
                                   class="form-control"
                                   placeholder="Ej: +58 212 9876543"
                                   value="{{ old('contact_phone_alt') }}">
                            @error('contact_phone_alt')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group full-width">
                            <label for="company_address" class="form-label">
                                <i class="fas fa-map-marker-alt"></i>
                                Dirección Fiscal / Oficina
                            </label>
                            <textarea id="company_address" 
                                      name="company_address" 
                                      class="form-control"
                                      placeholder="Ej: Av. Libertador, Edif. Caracas, Piso 10, Ofic. 10B, Caracas, Venezuela"
                                      rows="3">{{ old('company_address') }}</textarea>
                            @error('company_address')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="website_url" class="form-label">
                                <i class="fas fa-globe"></i>
                                Sitio Web
                            </label>
                            <input type="url" 
                                   id="website_url" 
                                   name="website_url" 
                                   class="form-control"
                                   placeholder="Ej: https://www.empresadelaliado.com"
                                   value="{{ old('website_url') }}">
                            @error('website_url')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Sección de Cuenta de Usuario --}}
                <div class="form-section" data-section="account">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-user-circle"></i>
                            Cuenta de Acceso
                        </h3>
                        <p class="section-description">
                            Credenciales para el acceso al sistema del aliado
                        </p>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="user_name" class="form-label">
                                <i class="fas fa-user"></i>
                                Nombre de Usuario *
                            </label>
                            <input type="text" 
                                   id="user_name" 
                                   name="user_name" 
                                   class="form-control"
                                   placeholder="Ej: Juan Pérez"
                                   value="{{ old('user_name') }}" 
                                   required>
                            @error('user_name')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="user_email" class="form-label">
                                <i class="fas fa-at"></i>
                                Correo Electrónico de Acceso *
                            </label>
                            <input type="email" 
                                   id="user_email" 
                                   name="user_email" 
                                   class="form-control"
                                   placeholder="Ej: usuario@rumbero.app"
                                   value="{{ old('user_email') }}" 
                                   required>
                            @error('user_email')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="user_password" class="form-label">
                                <i class="fas fa-lock"></i>
                                Contraseña *
                            </label>
                            <input type="password" 
                                   id="user_password" 
                                   name="user_password" 
                                   class="form-control"
                                   placeholder="Mínimo 8 caracteres"
                                   required>
                            <div class="password-strength" id="passwordStrength">
                                <div class="strength-bar"></div>
                                <span class="strength-text">Seguridad de la contraseña</span>
                            </div>
                            @error('user_password')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="user_password_confirmation" class="form-label">
                                <i class="fas fa-lock"></i>
                                Confirmar Contraseña *
                            </label>
                            <input type="password" 
                                   id="user_password_confirmation" 
                                   name="user_password_confirmation" 
                                   class="form-control"
                                   placeholder="Repita la contraseña"
                                   required>
                            <div class="password-match" id="passwordMatch">
                                <i class="fas fa-check"></i>
                                <span>Las contraseñas coinciden</span>
                            </div>
                            @error('user_password_confirmation')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Sección de Multimedia --}}
                <div class="form-section" data-section="media">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-image"></i>
                            Imagen del Aliado
                        </h3>
                        <p class="section-description">
                            Logo o imagen representativa del aliado comercial
                        </p>
                    </div>
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <div class="image-upload-container">
                                <div class="file-upload-section">
                                    <label for="image_url" class="file-upload-label">
                                        <div class="upload-area" id="uploadArea">
                                            <i class="fas fa-cloud-upload-alt"></i>
                                            <span class="upload-text">Seleccionar imagen</span>
                                            <span class="upload-hint">Arrastra o haz clic para subir</span>
                                        </div>
                                    </label>
                                    <input type="file" 
                                           id="image_url" 
                                           name="image_url" 
                                           class="file-input"
                                           accept="image/*">
                                    
                                    <div class="upload-preview" id="uploadPreview"></div>
                                    
                                    <p class="form-help-text">
                                        <i class="fas fa-info-circle"></i>
                                        Formatos aceptados: JPEG, PNG, JPG, GIF, SVG. Tamaño máximo: 2MB
                                    </p>
                                    @error('image_url')
                                        <span class="error-message">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Acciones del Formulario --}}
                <div class="form-actions-modern">
                    <a href="{{ route('aliados.index') }}" class="modern-secondary-btn cancel-btn">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </a>
                    <button type="button" class="modern-secondary-btn prev-final-btn">
                        <i class="fas fa-chevron-left"></i>
                        Anterior
                    </button>
                    <button type="submit" class="modern-primary-btn submit-btn">
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
        // Navegación por pasos del formulario
        const formSections = document.querySelectorAll('.form-section');
        const progressSteps = document.querySelectorAll('.progress-step');
        const prevBtn = document.querySelector('.prev-btn');
        const nextBtn = document.querySelector('.next-btn');
        const prevFinalBtn = document.querySelector('.prev-final-btn');
        let currentStep = 0;

        // Actualizar navegación
        function updateNavigation() {
            prevBtn.disabled = currentStep === 0;
            prevFinalBtn.style.display = currentStep === formSections.length - 1 ? 'block' : 'none';
            nextBtn.style.display = currentStep < formSections.length - 1 ? 'block' : 'none';
            
            progressSteps.forEach((step, index) => {
                step.classList.toggle('active', index === currentStep);
                step.classList.toggle('completed', index < currentStep);
            });

            const stepTitles = [
                'Información General',
                'Información del Negocio',
                'Información de Contacto',
                'Cuenta de Acceso',
                'Multimedia'
            ];
            
            document.querySelector('.current-step').textContent = `Paso ${currentStep + 1} de ${formSections.length}`;
            document.querySelector('.step-title').textContent = stepTitles[currentStep];
        }

        // Navegar al siguiente paso
        function nextStep() {
            if (validateCurrentStep() && currentStep < formSections.length - 1) {
                formSections[currentStep].classList.remove('active');
                currentStep++;
                formSections[currentStep].classList.add('active');
                updateNavigation();
            }
        }

        // Navegar al paso anterior
        function prevStep() {
            if (currentStep > 0) {
                formSections[currentStep].classList.remove('active');
                currentStep--;
                formSections[currentStep].classList.add('active');
                updateNavigation();
            }
        }

        // Validar paso actual
        function validateCurrentStep() {
            const currentSection = formSections[currentStep];
            const requiredFields = currentSection.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = '#ef4444';
                    // Mostrar mensaje de error
                    if (!field.nextElementSibling?.classList.contains('error-message')) {
                        const errorSpan = document.createElement('span');
                        errorSpan.className = 'error-message';
                        errorSpan.textContent = 'Este campo es obligatorio';
                        field.parentNode.appendChild(errorSpan);
                    }
                } else {
                    field.style.borderColor = '';
                    // Remover mensaje de error si existe
                    const errorMsg = field.parentNode.querySelector('.error-message');
                    if (errorMsg) errorMsg.remove();
                }
            });

            if (!isValid) {
                // Scroll al primer error
                const firstError = currentSection.querySelector('[required]:invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            }

            return isValid;
        }

        // Event listeners
        nextBtn.addEventListener('click', nextStep);
        prevBtn.addEventListener('click', prevStep);
        prevFinalBtn.addEventListener('click', prevStep);

        // Navegación por clic en progreso
        progressSteps.forEach((step, index) => {
            step.addEventListener('click', () => {
                if (index <= currentStep) {
                    formSections[currentStep].classList.remove('active');
                    currentStep = index;
                    formSections[currentStep].classList.add('active');
                    updateNavigation();
                }
            });
        });

        // Contadores de caracteres
        const descriptionTextarea = document.getElementById('description');
        const descriptionCounter = document.getElementById('description-counter');

        function updateCharCounter(textarea, counter, maxLength) {
            const length = textarea.value.length;
            counter.textContent = length;
            
            if (length > maxLength * 0.8) {
                counter.style.color = '#f59e0b';
            } else if (length > maxLength * 0.9) {
                counter.style.color = '#ef4444';
            } else {
                counter.style.color = '#6b7280';
            }
        }

        if (descriptionTextarea) {
            descriptionTextarea.addEventListener('input', () => {
                updateCharCounter(descriptionTextarea, descriptionCounter, 500);
            });
            updateCharCounter(descriptionTextarea, descriptionCounter, 500);
        }

        // Validación de contraseña
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
            
            const messages = [
                'Muy débil',
                'Débil',
                'Regular',
                'Fuerte',
                'Muy fuerte'
            ];
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

        // Upload de imagen con drag & drop
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('image_url');
        const uploadPreview = document.getElementById('uploadPreview');

        if (uploadArea && fileInput) {
            uploadArea.addEventListener('click', () => fileInput.click());

            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                uploadArea.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(eventName => {
                uploadArea.addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                uploadArea.addEventListener(eventName, unhighlight, false);
            });

            function highlight() {
                uploadArea.classList.add('highlight');
            }

            function unhighlight() {
                uploadArea.classList.remove('highlight');
            }

            uploadArea.addEventListener('drop', handleDrop, false);

            function handleDrop(e) {
                const files = e.dataTransfer.files;
                fileInput.files = files;
                handleFiles(files);
            }

            fileInput.addEventListener('change', function() {
                handleFiles(this.files);
            });

            function handleFiles(files) {
                if (files.length > 0) {
                    const file = files[0];
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            uploadPreview.innerHTML = `
                                <div class="preview-image-container">
                                    <img src="${e.target.result}" alt="Vista previa" class="preview-image">
                                    <button type="button" class="preview-remove" onclick="removePreview()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            `;
                            uploadArea.querySelector('.upload-text').textContent = 'Imagen seleccionada';
                        };
                        reader.readAsDataURL(file);
                    }
                }
            }
        }

        // Inicializar navegación
        updateNavigation();
    });

    // Funciones globales
    function updateStatusPreview(status) {
        const statusPreview = document.querySelector('.status-preview');
        const statusHelp = document.getElementById('statusHelp');
        
        statusPreview.className = `status-preview badge-status-${status}`;
        
        const statusText = {
            'pendiente': 'Pendiente',
            'activo': 'Activo',
            'inactivo': 'Inactivo'
        };
        
        const helpText = {
            'pendiente': 'Los aliados pendientes requieren activación manual antes de poder acceder al sistema.',
            'activo': 'Los aliados activos pueden acceder inmediatamente al sistema y sus funcionalidades.',
            'inactivo': 'Los aliados inactivos no podrán acceder al sistema hasta que sean reactivados.'
        };
        
        statusPreview.innerHTML = `<i class="fas fa-${status === 'pendiente' ? 'clock' : status === 'activo' ? 'check' : 'pause'}"></i> ${statusText[status]}`;
        statusHelp.textContent = helpText[status];
    }

    function updateCategoryPreview() {
        const categorySelect = document.getElementById('category_name');
        const subcategoryInput = document.getElementById('sub_category_name');
        const previewCategory = document.getElementById('preview-category');
        const previewSubcategory = document.getElementById('preview-subcategory');
        
        previewCategory.textContent = categorySelect.value || 'Sin categoría';
        previewSubcategory.textContent = subcategoryInput.value || 'Sin subcategoría';
    }

    function removePreview() {
        const uploadPreview = document.getElementById('uploadPreview');
        const fileInput = document.getElementById('image_url');
        const uploadArea = document.getElementById('uploadArea');
        
        if (uploadPreview) uploadPreview.innerHTML = '';
        if (fileInput) fileInput.value = '';
        if (uploadArea) {
            uploadArea.querySelector('.upload-text').textContent = 'Seleccionar imagen';
        }
    }

    // Inicializar funciones al cargar
    document.addEventListener('DOMContentLoaded', function() {
        updateStatusPreview(document.getElementById('status').value);
        updateCategoryPreview();
    });
</script>
@endpush