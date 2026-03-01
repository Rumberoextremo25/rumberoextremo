@extends('layouts.admin')

@section('title', 'Rumbero Extremo - Editar Aliado')

@section('page_title_toolbar', 'Editar Aliado')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/aliado-edit.css') }}">
@endpush

@section('content')
    <div class="aliado-edit-wrapper">
        {{-- Header con bienvenida --}}
        <div class="edit-header-bar">
            <div class="header-left">
                <a href="{{ route('admin.aliados.index') }}" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                    <span>Volver al Listado</span>
                </a>
                <div class="page-title">
                    <span class="title-main">Editar</span>
                    <span class="title-accent">Aliado</span>
                </div>
            </div>
            <div class="header-actions">
                <div class="user-greeting">
                    <span>Editando,</span>
                    <strong>{{ Auth::user()->name }}</strong>
                </div>
                <div class="avatar-circle">
                    <span>{{ substr(Auth::user()->name, 0, 1) }}</span>
                </div>
            </div>
        </div>

        {{-- Información del aliado --}}
        <div class="ally-info-bar">
            <div class="info-content">
                <div class="info-icon">
                    <i class="fas fa-building"></i>
                </div>
                <div class="info-details">
                    <span class="info-label">Editando:</span>
                    <span class="info-value">{{ $ally->company_name }}</span>
                    <span class="info-badge">ID #{{ $ally->id }}</span>
                </div>
            </div>
            <div class="status-badge {{ strtolower($ally->status) }}">
                <i class="fas fa-circle"></i>
                {{ ucfirst($ally->status) }}
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

        @if (session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
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
                        <i class="fas fa-tags"></i>
                    </div>
                    <span class="step-label">Categorías</span>
                </div>
                <div class="step" data-step="3">
                    <div class="step-icon">
                        <i class="fas fa-address-book"></i>
                    </div>
                    <span class="step-label">Contacto</span>
                </div>
                <div class="step" data-step="4">
                    <div class="step-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <span class="step-label">Adicional</span>
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
            <form id="editAllyForm" action="{{ route('admin.aliados.update', $ally->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

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
                        <p class="step-description">Datos básicos y estado del aliado comercial</p>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="company_name">
                                <i class="fas fa-building"></i>
                                Nombre de la Empresa <span class="required">*</span>
                            </label>
                            <input type="text" id="company_name" name="company_name" 
                                   value="{{ old('company_name', $ally->company_name) }}" 
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
                                   value="{{ old('company_rif', $ally->company_rif) }}" 
                                   placeholder="Ej: J-12345678-9">
                            @error('company_rif')
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
                                    <option value="Fisico" {{ old('business_type_name', $ally->businessType->name ?? '') == 'Fisico' ? 'selected' : '' }}>
                                        Físico
                                    </option>
                                    <option value="Online" {{ old('business_type_name', $ally->businessType->name ?? '') == 'Online' ? 'selected' : '' }}>
                                        Online
                                    </option>
                                    <option value="Servicio a domicilio" {{ old('business_type_name', $ally->businessType->name ?? '') == 'Servicio a domicilio' ? 'selected' : '' }}>
                                        Servicio a domicilio
                                    </option>
                                </select>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            @error('business_type_name')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="status">
                                <i class="fas fa-toggle-on"></i>
                                Estado del Aliado <span class="required">*</span>
                            </label>
                            <div class="select-wrapper">
                                <select id="status" name="status" required>
                                    <option value="activo" {{ old('status', $ally->status) == 'activo' ? 'selected' : '' }}>Activo</option>
                                    <option value="pendiente" {{ old('status', $ally->status) == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                    <option value="inactivo" {{ old('status', $ally->status) == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                                </select>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            @error('status')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group full-width">
                            <label for="description">
                                <i class="fas fa-align-left"></i>
                                Descripción del Aliado
                            </label>
                            <textarea id="description" name="description" rows="4" 
                                      placeholder="Breve descripción del aliado y los servicios que ofrece...">{{ old('description', $ally->description) }}</textarea>
                            <div class="char-counter">
                                <span id="description-counter">0</span>/500
                            </div>
                            @error('description')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Sección 2: Categorías --}}
                <div class="form-step" data-step="2">
                    <div class="step-header">
                        <h3 class="step-title">
                            <i class="fas fa-tags"></i>
                            Categorías del Negocio
                        </h3>
                        <p class="step-description">Define la categoría principal y subcategoría</p>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="category_name">
                                <i class="fas fa-layer-group"></i>
                                Categoría Principal <span class="required">*</span>
                            </label>
                            <div class="select-wrapper">
                                <select id="category_name" name="category_name" required>
                                    <option value="">Selecciona una categoría</option>
                                    <option value="Restaurantes, Bares, Discotecas, Night Club, Juegos" {{ old('category_name', $ally->category->name ?? '') == 'Restaurantes, Bares, Discotecas, Night Club, Juegos' ? 'selected' : '' }}>
                                        Restaurantes, Bares, Discotecas, Night Club, Juegos
                                    </option>
                                    <option value="Comidas, Bebidas, Cafes, Heladerias, Panaderias, Pastelerias" {{ old('category_name', $ally->category->name ?? '') == 'Comidas, Bebidas, Cafes, Heladerias, Panaderias, Pastelerias' ? 'selected' : '' }}>
                                        Comidas, Bebidas, Cafés, Heladerías, Panaderías, Pastelerías
                                    </option>
                                    <option value="Deportes y Hobbies" {{ old('category_name', $ally->category->name ?? '') == 'Deportes y Hobbies' ? 'selected' : '' }}>
                                        Deportes y Hobbies
                                    </option>
                                    <option value="Viajes y Turismo" {{ old('category_name', $ally->category->name ?? '') == 'Viajes y Turismo' ? 'selected' : '' }}>
                                        Viajes y Turismo
                                    </option>
                                    <option value="Eventos y Festejos" {{ old('category_name', $ally->category->name ?? '') == 'Eventos y Festejos' ? 'selected' : '' }}>
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
                                   value="{{ old('sub_category_name', $ally->subCategory->name ?? '') }}" 
                                   placeholder="Ej: Comida Rápida, Rock, Ropa Casual">
                            @error('sub_category_name')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group full-width">
                            <div class="category-preview">
                                <span class="preview-label">Vista previa:</span>
                                <div class="preview-content">
                                    <span class="preview-category" id="preview-category">
                                        {{ $ally->category->name ?? 'Sin categoría' }}
                                    </span>
                                    <i class="fas fa-arrow-right"></i>
                                    <span class="preview-subcategory" id="preview-subcategory">
                                        {{ $ally->subCategory->name ?? 'Sin subcategoría' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sección 3: Contacto --}}
                <div class="form-step" data-step="3">
                    <div class="step-header">
                        <h3 class="step-title">
                            <i class="fas fa-address-book"></i>
                            Información de Contacto
                        </h3>
                        <p class="step-description">Datos del representante del aliado</p>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="contact_person_name">
                                <i class="fas fa-user-tie"></i>
                                Persona de Contacto <span class="required">*</span>
                            </label>
                            <input type="text" id="contact_person_name" name="contact_person_name" 
                                   value="{{ old('contact_person_name', $ally->contact_person_name) }}" 
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
                                   value="{{ old('contact_email', $ally->contact_email) }}" 
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
                                   value="{{ old('contact_phone', $ally->contact_phone) }}" 
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
                                   value="{{ old('contact_phone_alt', $ally->contact_phone_alt) }}" 
                                   placeholder="Ej: +58 212 9876543">
                            @error('contact_phone_alt')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Sección 4: Información Adicional --}}
                <div class="form-step" data-step="4">
                    <div class="step-header">
                        <h3 class="step-title">
                            <i class="fas fa-info-circle"></i>
                            Información Adicional
                        </h3>
                        <p class="step-description">Datos complementarios y notas internas</p>
                    </div>

                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label for="company_address">
                                <i class="fas fa-map-marker-alt"></i>
                                Dirección Fiscal / Oficina
                            </label>
                            <textarea id="company_address" name="company_address" rows="3" 
                                      placeholder="Ej: Av. Libertador, Edif. Caracas, Piso 10, Ofic. 10B, Caracas, Venezuela">{{ old('company_address', $ally->company_address) }}</textarea>
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
                                   value="{{ old('website_url', $ally->website_url) }}" 
                                   placeholder="Ej: https://www.empresa.com">
                            @error('website_url')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="discount">
                                <i class="fas fa-percentage"></i>
                                Oferta de Descuento
                            </label>
                            <input type="text" id="discount" name="discount" 
                                   value="{{ old('discount', $ally->discount) }}" 
                                   placeholder="Ej: 15% en alquiler de equipos">
                            @error('discount')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="registered_at">
                                <i class="fas fa-calendar-alt"></i>
                                Fecha de Registro <span class="required">*</span>
                            </label>
                            <input type="date" id="registered_at" name="registered_at" 
                                   value="{{ old('registered_at', $ally->registered_at ? $ally->registered_at->format('Y-m-d') : '') }}" 
                                   required>
                            @error('registered_at')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group full-width">
                            <label for="notes">
                                <i class="fas fa-sticky-note"></i>
                                Notas Internas
                            </label>
                            <textarea id="notes" name="notes" rows="4" 
                                      placeholder="Información adicional relevante...">{{ old('notes', $ally->notes) }}</textarea>
                            <div class="char-counter">
                                <span id="notes-counter">0</span>/1000
                            </div>
                            @error('notes')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
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
                                @if ($ally->image_url)
                                    <div class="current-image">
                                        <span class="current-image-label">Imagen actual:</span>
                                        <div class="image-preview">
                                            <img src="{{ Storage::url($ally->image_url) }}" 
                                                 alt="{{ $ally->company_name }}">
                                            <div class="image-actions">
                                                <a href="{{ Storage::url($ally->image_url) }}" 
                                                   download class="image-action-btn download">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="upload-area" id="uploadArea">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span class="upload-text">
                                        {{ $ally->image_url ? 'Cambiar imagen' : 'Seleccionar imagen' }}
                                    </span>
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
                                {{-- Imágenes existentes --}}
                                @if($ally->product_images && count($ally->product_images) > 0)
                                    <div class="existing-gallery">
                                        @foreach($ally->product_images as $index => $image)
                                            <div class="gallery-item existing" data-id="{{ $index }}">
                                                <img src="{{ Storage::url($image) }}" alt="Producto {{ $index + 1 }}">
                                                <div class="gallery-item-actions">
                                                    <button type="button" class="gallery-action delete" 
                                                            onclick="removeExistingImage({{ $index }})">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                                <input type="hidden" name="existing_images[]" value="{{ $image }}">
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Contenedor para nuevas imágenes --}}
                                <div class="new-gallery" id="newGallery"></div>

                                {{-- Controles de galería --}}
                                <div class="gallery-controls">
                                    <button type="button" class="gallery-add-btn" id="addGalleryImages">
                                        <i class="fas fa-plus-circle"></i>
                                        Agregar Imágenes
                                    </button>
                                    <span class="gallery-counter" id="galleryCounter">
                                        {{ count($ally->product_images ?? []) }}/5
                                    </span>
                                </div>

                                {{-- Vista previa --}}
                                <div class="gallery-preview">
                                    <h5 class="preview-title">
                                        <i class="fas fa-eye"></i>
                                        Vista Previa
                                    </h5>
                                    <div class="preview-grid" id="galleryPreview">
                                        @if($ally->product_images && count($ally->product_images) > 0)
                                            @foreach($ally->product_images as $index => $image)
                                                <div class="preview-item">
                                                    <img src="{{ Storage::url($image) }}" alt="Preview">
                                                    <span class="preview-number">{{ $index + 1 }}</span>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="empty-preview">
                                                <i class="fas fa-images"></i>
                                                <p>No hay imágenes</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <input type="file" id="product_images" name="product_images[]" 
                                       class="gallery-file-input" accept="image/*" multiple style="display: none;">
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
                        <i class="fas fa-save"></i>
                        Actualizar Aliado
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

        // ========== NAVEGACIÓN POR PASOS ==========
        const steps = document.querySelectorAll('.form-step');
        const progressSteps = document.querySelectorAll('.step');
        const prevBtn = document.querySelector('.nav-btn.prev');
        const nextBtn = document.querySelector('.nav-btn.next');
        const prevFinalBtn = document.querySelector('.btn-prev-final');
        let currentStep = 0;

        function updateStepNavigation() {
            // Actualizar visibilidad de pasos
            steps.forEach((step, index) => {
                step.classList.toggle('active', index === currentStep);
            });

            // Actualizar progreso
            progressSteps.forEach((step, index) => {
                step.classList.toggle('active', index === currentStep);
            });

            // Actualizar botones
            prevBtn.disabled = currentStep === 0;
            nextBtn.style.display = currentStep < steps.length - 1 ? 'inline-flex' : 'none';
            prevFinalBtn.style.display = currentStep === steps.length - 1 ? 'inline-flex' : 'none';

            // Actualizar indicador
            document.querySelector('.current-step').textContent = `Paso ${currentStep + 1} de ${steps.length}`;
            const stepNames = ['General', 'Categorías', 'Contacto', 'Adicional', 'Multimedia'];
            document.querySelector('.step-name').textContent = stepNames[currentStep];
        }

        prevBtn.addEventListener('click', () => {
            if (currentStep > 0) {
                currentStep--;
                updateStepNavigation();
            }
        });

        nextBtn.addEventListener('click', () => {
            if (currentStep < steps.length - 1) {
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

        // ========== CONTADORES DE CARACTERES ==========
        const description = document.getElementById('description');
        const notes = document.getElementById('notes');
        const descCounter = document.getElementById('description-counter');
        const notesCounter = document.getElementById('notes-counter');

        if (description && descCounter) {
            descCounter.textContent = description.value.length;
            description.addEventListener('input', () => {
                descCounter.textContent = description.value.length;
            });
        }

        if (notes && notesCounter) {
            notesCounter.textContent = notes.value.length;
            notes.addEventListener('input', () => {
                notesCounter.textContent = notes.value.length;
            });
        }

        // ========== VISTA PREVIA DE CATEGORÍAS ==========
        const categorySelect = document.getElementById('category_name');
        const subcategoryInput = document.getElementById('sub_category_name');
        const previewCategory = document.getElementById('preview-category');
        const previewSubcategory = document.getElementById('preview-subcategory');

        function updateCategoryPreview() {
            if (previewCategory) {
                previewCategory.textContent = categorySelect.options[categorySelect.selectedIndex]?.text.split(' (')[0] || 'Sin categoría';
            }
            if (previewSubcategory) {
                previewSubcategory.textContent = subcategoryInput.value || 'Sin subcategoría';
            }
        }

        if (categorySelect) {
            categorySelect.addEventListener('change', updateCategoryPreview);
        }
        if (subcategoryInput) {
            subcategoryInput.addEventListener('input', updateCategoryPreview);
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
        let existingImages = @json($ally->product_images ?? []);
        let newImages = [];

        const galleryContainer = document.getElementById('galleryContainer');
        const newGallery = document.getElementById('newGallery');
        const addGalleryBtn = document.getElementById('addGalleryImages');
        const galleryFileInput = document.getElementById('product_images');
        const galleryCounter = document.getElementById('galleryCounter');
        const galleryPreview = document.getElementById('galleryPreview');

        if (addGalleryBtn && galleryFileInput) {
            addGalleryBtn.addEventListener('click', () => galleryFileInput.click());

            galleryFileInput.addEventListener('change', function() {
                const files = Array.from(this.files);
                const total = existingImages.length + newImages.length + files.length;

                if (total > 5) {
                    showAlert('Solo puedes subir hasta 5 imágenes en total', 'error');
                    return;
                }

                files.forEach(file => {
                    if (!file.type.startsWith('image/')) {
                        showAlert(`${file.name} no es una imagen válida`, 'error');
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = (e) => {
                        newImages.push({
                            id: Date.now() + Math.random(),
                            file: file,
                            preview: e.target.result,
                            name: file.name
                        });
                        updateGallery();
                    };
                    reader.readAsDataURL(file);
                });

                this.value = '';
            });
        }

        function updateGallery() {
            // Actualizar nuevas imágenes
            if (newGallery) {
                newGallery.innerHTML = newImages.map((img, index) => `
                    <div class="gallery-item new" data-id="${img.id}">
                        <img src="${img.preview}" alt="Nueva imagen">
                        <div class="gallery-item-actions">
                            <button type="button" class="gallery-action delete" onclick="removeNewImage('${img.id}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `).join('');
            }

            // Actualizar contador
            if (galleryCounter) {
                galleryCounter.textContent = `${existingImages.length + newImages.length}/5`;
            }

            // Actualizar vista previa
            updateGalleryPreview();
        }

        function updateGalleryPreview() {
            if (!galleryPreview) return;

            const allImages = [
                ...existingImages.map((img, i) => ({ src: img, index: i + 1 })),
                ...newImages.map((img, i) => ({ src: img.preview, index: existingImages.length + i + 1 }))
            ];

            if (allImages.length === 0) {
                galleryPreview.innerHTML = `
                    <div class="empty-preview">
                        <i class="fas fa-images"></i>
                        <p>No hay imágenes</p>
                    </div>
                `;
                return;
            }

            galleryPreview.innerHTML = allImages.map(img => `
                <div class="preview-item">
                    <img src="${img.src}" alt="Preview">
                    <span class="preview-number">${img.index}</span>
                </div>
            `).join('');
        }

        // ========== FUNCIONES GLOBALES ==========
        window.removePreview = function() {
            if (uploadPreview) uploadPreview.innerHTML = '';
            if (fileInput) fileInput.value = '';
            if (uploadArea) {
                uploadArea.querySelector('.upload-text').textContent = 'Seleccionar imagen';
            }
        };

        window.removeExistingImage = function(index) {
            if (confirm('¿Eliminar esta imagen?')) {
                existingImages = existingImages.filter((_, i) => i !== index);
                document.querySelector(`.gallery-item.existing[data-id="${index}"]`)?.remove();
                updateGallery();
            }
        };

        window.removeNewImage = function(id) {
            newImages = newImages.filter(img => img.id !== id);
            updateGallery();
        };

        function showAlert(message, type = 'error') {
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.innerHTML = `
                <i class="fas fa-${type === 'error' ? 'exclamation-triangle' : 'check-circle'}"></i>
                <span>${message}</span>
                <button class="alert-close">&times;</button>
            `;
            document.querySelector('.aliado-edit-wrapper').insertBefore(alert, document.querySelector('.form-progress'));
            
            alert.querySelector('.alert-close').addEventListener('click', () => alert.remove());
            setTimeout(() => alert.remove(), 5000);
        }
    });
</script>
@endpush
