@extends('layouts.admin')

@section('title', 'Rumbero Extremo - Editar Aliado')
@section('page_title', 'Editar Aliado')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/aliados.css') }}">
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
            <div class="header-status">
                <span class="status-display badge-status-{{ strtolower($ally->status) }}">
                    <i class="fas fa-circle"></i>
                    {{ ucfirst($ally->status) }}
                </span>
            </div>
        </div>

        {{-- Alertas --}}
        @if ($errors->any())
            <div class="modern-alert error">
                <div class="alert-content">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <strong>¡Atención!</strong> Se encontraron errores en el formulario:
                        <ul>
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

                {{-- Sección de Datos Generales --}}
                <div class="card-section">
                    <h3 class="section-title">
                        <i class="fas fa-building"></i>
                        Información General del Aliado
                    </h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="company_name" class="form-label">
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
                                Descripción del Aliado
                            </label>
                            <textarea id="description" 
                                      name="description" 
                                      class="form-control"
                                      placeholder="Breve descripción del aliado y los servicios que ofrece..."
                                      rows="3">{{ old('description', $ally->description) }}</textarea>
                            @error('description')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Sección de Categorías --}}
                <div class="card-section">
                    <h3 class="section-title">
                        <i class="fas fa-tags"></i>
                        Categorías del Negocio
                    </h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="category_name" class="form-label">
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
                    </div>
                </div>

                {{-- Sección de Contacto --}}
                <div class="card-section">
                    <h3 class="section-title">
                        <i class="fas fa-address-book"></i>
                        Información de Contacto
                    </h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="contact_person_name" class="form-label">
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
                <div class="card-section">
                    <h3 class="section-title">
                        <i class="fas fa-info-circle"></i>
                        Información Adicional
                    </h3>
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label for="company_address" class="form-label">
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
                                Notas Internas
                            </label>
                            <textarea id="notes" 
                                      name="notes" 
                                      class="form-control"
                                      placeholder="Cualquier información adicional relevante..."
                                      rows="3">{{ old('notes', $ally->notes) }}</textarea>
                            @error('notes')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Sección de Imagen --}}
                <div class="card-section">
                    <h3 class="section-title">
                        <i class="fas fa-image"></i>
                        Imagen del Aliado
                    </h3>
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label for="image_url" class="form-label">
                                Logo / Imagen del Aliado
                            </label>
                            
                            @if ($ally->image_url)
                                <div class="current-image-container">
                                    <p class="current-image-label">Imagen actual:</p>
                                    <img src="{{ Storage::url($ally->image_url) }}" 
                                         alt="Imagen de {{ $ally->company_name }}"
                                         class="current-image-preview">
                                </div>
                            @endif

                            <input type="file" 
                                   id="image_url" 
                                   name="image_url" 
                                   class="form-control file-input"
                                   accept="image/*">
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

                {{-- Acciones del Formulario --}}
                <div class="form-actions-modern">
                    <a href="{{ route('aliados.index') }}" class="modern-secondary-btn cancel-btn">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </a>
                    <button type="submit" class="modern-primary-btn submit-btn">
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
        // Cerrar alertas automáticamente
        setTimeout(() => {
            document.querySelectorAll('.modern-alert').forEach(alert => {
                if (alert) {
                    alert.style.display = 'none';
                }
            });
        }, 5000);

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

        // Previsualización de imagen al seleccionar archivo
        const imageInput = document.getElementById('image_url');
        if (imageInput) {
            imageInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        // Crear o actualizar la previsualización
                        let previewContainer = document.querySelector('.current-image-container');
                        if (!previewContainer) {
                            previewContainer = document.createElement('div');
                            previewContainer.className = 'current-image-container';
                            imageInput.parentNode.insertBefore(previewContainer, imageInput);
                        }
                        
                        previewContainer.innerHTML = `
                            <p class="current-image-label">Nueva imagen seleccionada:</p>
                            <img src="${e.target.result}" alt="Vista previa" class="current-image-preview">
                        `;
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        // Validación básica del formulario
        const form = document.getElementById('editAllyForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                const requiredFields = form.querySelectorAll('[required]');
                let isValid = true;

                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.style.borderColor = 'var(--error-color)';
                    } else {
                        field.style.borderColor = '';
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    alert('Por favor, complete todos los campos requeridos.');
                }
            });
        }
    });
</script>
@endpush
