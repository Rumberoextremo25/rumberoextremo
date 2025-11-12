@extends('layouts.admin')

@section('title', 'Rumbero Extremo - Editar Aliado')
@section('page_title', 'Editar Aliado')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/aliado-edit.css') }}">
@endpush

@section('content')
    <div class="aliado-edit-container">
        {{-- Header Moderno --}}
        <div class="edit-header-modern">
            <div class="header-content">
                <div class="breadcrumb">
                    <a href="{{ route('aliados.index') }}" class="breadcrumb-link">
                        <i class="fas fa-arrow-left"></i>
                        Volver al Listado
                    </a>
                </div>
                <div class="title-section">
                    <h1 class="edit-title">
                        <span class="title-prefix">Editar Aliado:</span>
                        <span class="title-main">{{ $ally->company_name }}</span>
                        <span class="id-badge">#{{ $ally->id }}</span>
                    </h1>
                    <p class="page-subtitle">
                        <i class="fas fa-edit"></i>
                        Actualiza la información del aliado comercial
                    </p>
                </div>
            </div>
            <div class="header-meta">
                <span class="status-display badge-status-{{ strtolower($ally->status) }}">
                    <i class="fas fa-circle"></i>
                    {{ ucfirst($ally->status) }}
                </span>
                <span class="registration-info">
                    <i class="fas fa-calendar"></i>
                    Registrado: {{ \Carbon\Carbon::parse($ally->registered_at)->format('d/m/Y') }}
                </span>
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
                <div class="progress-step" data-step="categories">
                    <div class="step-icon">
                        <i class="fas fa-tags"></i>
                    </div>
                    <span class="step-label">Categorías</span>
                </div>
                <div class="progress-step" data-step="contact">
                    <div class="step-icon">
                        <i class="fas fa-address-book"></i>
                    </div>
                    <span class="step-label">Contacto</span>
                </div>
                <div class="progress-step" data-step="additional">
                    <div class="step-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <span class="step-label">Adicional</span>
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

        @if (session('success'))
            <div class="modern-alert success">
                <div class="alert-content">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button class="alert-close" onclick="this.parentElement.style.display='none'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif

        <div class="edit-card-modern">
            <form id="editAllyForm" action="{{ route('aliados.update', $ally->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

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

                {{-- Sección de Datos Generales --}}
                <div class="form-section active" data-section="general">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-building"></i>
                            Información General del Aliado
                        </h3>
                        <p class="section-description">
                            Información básica y estado del aliado comercial
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
                                   value="{{ old('company_name', $ally->company_name) }}" 
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
                                   value="{{ old('company_rif', $ally->company_rif) }}">
                            @error('company_rif')
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
                            @error('business_type_name')
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
                                    required>
                                <option value="activo" {{ old('status', $ally->status) == 'activo' ? 'selected' : '' }}>
                                    Activo
                                </option>
                                <option value="pendiente" {{ old('status', $ally->status) == 'pendiente' ? 'selected' : '' }}>
                                    Pendiente
                                </option>
                                <option value="inactivo" {{ old('status', $ally->status) == 'inactivo' ? 'selected' : '' }}>
                                    Inactivo
                                </option>
                            </select>
                            @error('status')
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
                                      rows="4">{{ old('description', $ally->description) }}</textarea>
                            <div class="char-counter">
                                <span id="description-counter">0</span>/500 caracteres
                            </div>
                            @error('description')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Sección de Categorías --}}
                <div class="form-section" data-section="categories">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-tags"></i>
                            Categorías del Negocio
                        </h3>
                        <p class="section-description">
                            Define la categoría principal y subcategoría del aliado
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
                                    required>
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
                                   value="{{ old('sub_category_name', $ally->subCategory->name ?? '') }}">
                            @error('sub_category_name')
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
                                        {{ $ally->category->name ?? 'Sin categoría' }}
                                    </span>
                                    <span class="preview-separator">→</span>
                                    <span class="preview-subcategory" id="preview-subcategory">
                                        {{ $ally->subCategory->name ?? 'Sin subcategoría' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sección de Contacto --}}
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
                                   value="{{ old('contact_person_name', $ally->contact_person_name) }}" 
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
                                   value="{{ old('contact_email', $ally->contact_email) }}" 
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
                                   value="{{ old('contact_phone', $ally->contact_phone) }}" 
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
                                   value="{{ old('contact_phone_alt', $ally->contact_phone_alt) }}">
                            @error('contact_phone_alt')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Sección de Información Adicional --}}
                <div class="form-section" data-section="additional">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-info-circle"></i>
                            Información Adicional
                        </h3>
                        <p class="section-description">
                            Datos complementarios y notas internas
                        </p>
                    </div>
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label for="company_address" class="form-label">
                                <i class="fas fa-map-marker-alt"></i>
                                Dirección Fiscal / Oficina
                            </label>
                            <textarea id="company_address" 
                                      name="company_address" 
                                      class="form-control"
                                      placeholder="Ej: Av. Libertador, Edif. Caracas, Piso 10, Ofic. 10B, Caracas, Venezuela"
                                      rows="3">{{ old('company_address', $ally->company_address) }}</textarea>
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
                                   value="{{ old('website_url', $ally->website_url) }}">
                            @error('website_url')
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
                                   value="{{ old('discount', $ally->discount) }}">
                            @error('discount')
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
                                   value="{{ old('registered_at', $ally->registered_at ? $ally->registered_at->format('Y-m-d') : '') }}" 
                                   required>
                            @error('registered_at')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group full-width">
                            <label for="notes" class="form-label">
                                <i class="fas fa-sticky-note"></i>
                                Notas Internas
                            </label>
                            <textarea id="notes" 
                                      name="notes" 
                                      class="form-control"
                                      placeholder="Cualquier información adicional relevante..."
                                      rows="4">{{ old('notes', $ally->notes) }}</textarea>
                            <div class="char-counter">
                                <span id="notes-counter">0</span>/1000 caracteres
                            </div>
                            @error('notes')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Sección de Imagen --}}
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
                                @if ($ally->image_url)
                                    <div class="current-image-section">
                                        <label class="current-image-label">
                                            <i class="fas fa-image"></i>
                                            Imagen actual:
                                        </label>
                                        <div class="current-image-wrapper">
                                            <img src="{{ Storage::url($ally->image_url) }}" 
                                                 alt="Imagen de {{ $ally->company_name }}"
                                                 class="current-image-preview">
                                            <div class="image-overlay">
                                                <button type="button" class="image-action-btn view-btn" onclick="openImageModal('{{ Storage::url($ally->image_url) }}')">
                                                    <i class="fas fa-expand"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="file-upload-section">
                                    <label for="image_url" class="file-upload-label">
                                        <div class="upload-area" id="uploadArea">
                                            <i class="fas fa-cloud-upload-alt"></i>
                                            <span class="upload-text">
                                                {{ $ally->image_url ? 'Cambiar imagen' : 'Seleccionar imagen' }}
                                            </span>
                                            <span class="upload-hint">
                                                Arrastra o haz clic para subir
                                            </span>
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
                        <i class="fas fa-save"></i>
                        Actualizar Aliado
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal para Imagen --}}
    <div id="imageModal" class="modal-overlay">
        <div class="modal-content image-modal">
            <button class="modal-close" onclick="closeImageModal()">
                <i class="fas fa-times"></i>
            </button>
            <img src="" alt="" class="modal-image" id="modalImage">
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
            // Actualizar botones
            prevBtn.disabled = currentStep === 0;
            prevFinalBtn.style.display = currentStep === formSections.length - 1 ? 'block' : 'none';
            nextBtn.style.display = currentStep < formSections.length - 1 ? 'block' : 'none';
            
            // Actualizar progreso
            progressSteps.forEach((step, index) => {
                step.classList.toggle('active', index === currentStep);
                step.classList.toggle('completed', index < currentStep);
            });

            // Actualizar información del paso
            const stepTitles = [
                'Información General',
                'Categorías del Negocio',
                'Información de Contacto',
                'Información Adicional',
                'Multimedia'
            ];
            
            document.querySelector('.current-step').textContent = `Paso ${currentStep + 1} de ${formSections.length}`;
            document.querySelector('.step-title').textContent = stepTitles[currentStep];
        }

        // Navegar al siguiente paso
        function nextStep() {
            if (currentStep < formSections.length - 1) {
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
        const notesTextarea = document.getElementById('notes');
        const descriptionCounter = document.getElementById('description-counter');
        const notesCounter = document.getElementById('notes-counter');

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
            // Inicializar contador
            updateCharCounter(descriptionTextarea, descriptionCounter, 500);
        }

        if (notesTextarea) {
            notesTextarea.addEventListener('input', () => {
                updateCharCounter(notesTextarea, notesCounter, 1000);
            });
            // Inicializar contador
            updateCharCounter(notesTextarea, notesCounter, 1000);
        }

        // Vista previa de categorías
        const categorySelect = document.getElementById('category_name');
        const subcategoryInput = document.getElementById('sub_category_name');
        const previewCategory = document.getElementById('preview-category');
        const previewSubcategory = document.getElementById('preview-subcategory');

        function updateCategoryPreview() {
            previewCategory.textContent = categorySelect.value || 'Sin categoría';
            previewSubcategory.textContent = subcategoryInput.value || 'Sin subcategoría';
        }

        if (categorySelect && subcategoryInput) {
            categorySelect.addEventListener('change', updateCategoryPreview);
            subcategoryInput.addEventListener('input', updateCategoryPreview);
            // Inicializar vista previa
            updateCategoryPreview();
        }

        // Upload de imagen con drag & drop
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('image_url');
        const uploadPreview = document.getElementById('uploadPreview');

        if (uploadArea && fileInput) {
            // Click en área de upload
            uploadArea.addEventListener('click', () => {
                fileInput.click();
            });

            // Drag & drop
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

            // Manejar archivo dropeado
            uploadArea.addEventListener('drop', handleDrop, false);

            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                fileInput.files = files;
                handleFiles(files);
            }

            // Manejar archivo seleccionado
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

        // Cerrar alertas automáticamente
        setTimeout(() => {
            document.querySelectorAll('.modern-alert').forEach(alert => {
                if (alert) {
                    alert.style.display = 'none';
                }
            });
        }, 8000);

        // Efectos hover en botones
        document.querySelectorAll('.modern-primary-btn, .modern-secondary-btn').forEach(btn => {
            if (btn) {
                btn.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px)';
                });
                
                btn.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            }
        });

        // Validación básica del formulario
        const form = document.getElementById('editAllyForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                const requiredFields = form.querySelectorAll('[required]');
                let isValid = true;

                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.style.borderColor = '#ef4444';
                    } else {
                        field.style.borderColor = '';
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    // Ir al primer campo con error
                    const firstError = form.querySelector('[required]:invalid');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstError.focus();
                    }
                }
            });
        }

        // Inicializar navegación
        updateNavigation();
    });

    // Funciones globales
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

    function openImageModal(imageSrc) {
        document.getElementById('modalImage').src = imageSrc;
        document.getElementById('imageModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeImageModal() {
        document.getElementById('imageModal').classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    // Cerrar modal con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeImageModal();
        }
    });
</script>
@endpush
