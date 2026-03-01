@extends('layouts.admin')

@section('title', 'Crear Promoción Rumbera')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/promotion-create.css') }}">
@endpush

@section('content')
<div class="rumbero-wrapper">
    {{-- HEADER CON FRASE RUMBERA --}}
    <div class="rumbero-header">
        <div class="header-content">
            <div class="header-tag">
                <i class="fas fa-bolt"></i>
                <span>RUMBERO EXTREMO</span>
            </div>
            <h1 class="header-title">
                Crear <span class="gradient-text">Promoción</span>
            </h1>
            <p class="header-subtitle">
                <i class="fas fa-fire"></i>
                ¡Prepara una oferta extrema para la comunidad!
            </p>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.promotions.index') }}" class="btn-rumbero-secondary">
                <i class="fas fa-arrow-left"></i>
                Volver al Listado
            </a>
        </div>
    </div>

    {{-- ALERTAS --}}
    @if (session('success'))
        <div class="alert-modern success">
            <div class="alert-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="alert-content">
                <strong>¡Éxito rumbero!</strong>
                <p>{{ session('success') }}</p>
            </div>
            <button class="alert-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert-modern error">
            <div class="alert-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="alert-content">
                <strong>¡Ojo rumbero!</strong>
                <p>{{ session('error') }}</p>
            </div>
            <button class="alert-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    {{-- ERRORES DE VALIDACIÓN --}}
    @if ($errors->any())
        <div class="alert-modern error">
            <div class="alert-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="alert-content">
                <strong>¡Corrige estos errores rumbero!</strong>
                <ul style="margin-top: 0.5rem; margin-left: 1.5rem;">
                    @foreach ($errors->all() as $error)
                        <li style="margin-bottom: 0.2rem;">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button class="alert-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    {{-- FORMULARIO DE CREACIÓN --}}
    <div class="form-card">
        <div class="form-card-header">
            <div class="header-icon">
                <i class="fas fa-tag"></i>
            </div>
            <h2 class="form-card-title">Nueva Promoción Rumbera</h2>
            <p class="form-card-subtitle">Completa los datos para crear una oferta que hará vibrar a la comunidad</p>
        </div>

        <div class="form-card-body">
            <form action="{{ route('admin.promotions.store') }}" method="POST" enctype="multipart/form-data" id="promotionForm">
                @csrf

                {{-- INFORMACIÓN BÁSICA --}}
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-info-circle"></i>
                        <span>Información Básica</span>
                    </div>

                    <div class="form-grid">
                        {{-- Título --}}
                        <div class="full-width">
                            <div class="form-group">
                                <label for="title">
                                    <i class="fas fa-heading"></i>
                                    Título de la Promoción <span class="required-star">*</span>
                                </label>
                                <input type="text" 
                                       name="title" 
                                       id="title" 
                                       class="form-control @error('title') error @enderror"
                                       placeholder="Ej: 20% de descuento en Zapatos" 
                                       value="{{ old('title') }}" 
                                       required>
                                <span class="field-hint">
                                    <i class="fas fa-info-circle"></i> Un título llamativo para la promoción
                                </span>
                                @error('title')
                                    <span class="error-message">
                                        <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                    </span>
                                @enderror
                            </div>
                        </div>

                        {{-- Descuento --}}
                        <div>
                            <div class="form-group">
                                <label for="discount">
                                    <i class="fas fa-percent"></i>
                                    Descuento <span class="required-star">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" 
                                           name="discount" 
                                           id="discount" 
                                           class="form-control @error('discount') error @enderror"
                                           placeholder="20" 
                                           value="{{ old('discount') }}"
                                           min="0"
                                           max="100"
                                           required>
                                    <span class="input-group-text">%</span>
                                </div>
                                <span class="field-hint">
                                    <i class="fas fa-info-circle"></i> Porcentaje de descuento (0-100)
                                </span>
                                @error('discount')
                                    <span class="error-message">
                                        <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                    </span>
                                @enderror
                            </div>
                        </div>

                        {{-- Precio --}}
                        <div>
                            <div class="form-group">
                                <label for="price">
                                    <i class="fas fa-dollar-sign"></i>
                                    Precio <span class="required-star">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" 
                                           name="price" 
                                           id="price" 
                                           class="form-control @error('price') error @enderror"
                                           placeholder="49.99" 
                                           value="{{ old('price') }}"
                                           step="0.01"
                                           min="0"
                                           required>
                                </div>
                                <span class="field-hint">
                                    <i class="fas fa-info-circle"></i> Precio final después del descuento
                                </span>
                                @error('price')
                                    <span class="error-message">
                                        <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                    </span>
                                @enderror
                            </div>
                        </div>

                        {{-- Fecha de Expiración --}}
                        <div>
                            <div class="form-group">
                                <label for="expires_at">
                                    <i class="fas fa-calendar-alt"></i>
                                    Fecha de Expiración
                                    <span class="optional-badge">Opcional</span>
                                </label>
                                <input type="date" 
                                       name="expires_at" 
                                       id="expires_at" 
                                       class="form-control @error('expires_at') error @enderror"
                                       value="{{ old('expires_at') }}"
                                       min="{{ date('Y-m-d') }}">
                                <span class="field-hint">
                                    <i class="fas fa-info-circle"></i> Dejar vacío si no tiene fecha de expiración
                                </span>
                                @error('expires_at')
                                    <span class="error-message">
                                        <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                    </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- DESCRIPCIÓN --}}
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-align-left"></i>
                        <span>Descripción</span>
                    </div>

                    <div class="form-group">
                        <label for="description">
                            <i class="fas fa-pencil-alt"></i>
                            Descripción Detallada
                            <span class="optional-badge">Opcional</span>
                        </label>
                        <textarea name="description" 
                                  id="description" 
                                  class="form-control @error('description') error @enderror"
                                  rows="5" 
                                  placeholder="Describe los detalles de la promoción, condiciones, restricciones, etc...">{{ old('description') }}</textarea>
                        <span class="field-hint">
                            <i class="fas fa-info-circle"></i> Explica los detalles de la oferta para los rumberos
                        </span>
                        @error('description')
                            <span class="error-message">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            </span>
                        @enderror
                    </div>
                </div>

                {{-- IMAGEN --}}
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-image"></i>
                        <span>Imagen de la Promoción</span>
                    </div>

                    <div class="image-upload-area" id="dropArea">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <h3>Arrastra tu imagen aquí</h3>
                        <p>o haz clic para seleccionar un archivo</p>
                        <input type="file" 
                               name="image" 
                               id="image" 
                               class="file-input" 
                               accept="image/*"
                               required>
                        <span class="upload-hint">Formatos: JPG, PNG, GIF • Máx 2MB</span>
                    </div>

                    <div id="image-preview-container" class="image-preview-wrapper" style="display: none;">
                        <div class="image-preview-card">
                            <img id="image-preview" src="#" alt="Vista Previa">
                            <button type="button" class="remove-image" onclick="removeImage()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    @error('image')
                        <span class="error-message" style="margin-top: 1rem;">
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- CONFIGURACIÓN ADICIONAL --}}
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-cog"></i>
                        <span>Configuración</span>
                    </div>

                    <div class="config-grid">
                        {{-- Estado Activo --}}
                        <div class="config-item">
                            <div class="checkbox-wrapper">
                                <input type="checkbox" 
                                       name="is_active" 
                                       id="is_active" 
                                       value="1"
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label for="is_active">
                                    <i class="fas fa-bolt"></i>
                                    Promoción Activa
                                </label>
                            </div>
                            <span class="field-hint">
                                <i class="fas fa-info-circle"></i> Las promociones activas se muestran en la página
                            </span>
                        </div>

                        {{-- Destacado --}}
                        <div class="config-item">
                            <div class="checkbox-wrapper">
                                <input type="checkbox" 
                                       name="is_featured" 
                                       id="is_featured" 
                                       value="1"
                                       {{ old('is_featured') ? 'checked' : '' }}>
                                <label for="is_featured">
                                    <i class="fas fa-star"></i>
                                    Promoción Destacada
                                </label>
                            </div>
                            <span class="field-hint">
                                <i class="fas fa-info-circle"></i> Las promociones destacadas aparecen en la portada
                            </span>
                        </div>
                    </div>
                </div>

                {{-- BOTONES DE ACCIÓN --}}
                <div class="form-actions">
                    <a href="{{ route('admin.promotions.index') }}" class="btn-rumbero-secondary">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </a>
                    <button type="submit" class="btn-rumbero" id="submitBtn">
                        <i class="fas fa-save"></i>
                        <span>Crear Promoción Rumbera</span>
                        <i class="fas fa-fire"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- DECORACIÓN DE FONDO --}}
    <div class="rumbero-bg-decoration">
        <div class="circle circle-1"></div>
        <div class="circle circle-2"></div>
        <div class="circle circle-3"></div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Elementos del DOM
        const form = document.getElementById('promotionForm');
        const submitBtn = document.getElementById('submitBtn');
        const imageInput = document.getElementById('image');
        const dropArea = document.getElementById('dropArea');
        const previewContainer = document.getElementById('image-preview-container');
        const previewImage = document.getElementById('image-preview');

        // ========== DRAG & DROP ==========
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, unhighlight, false);
        });

        function highlight() {
            dropArea.classList.add('highlight');
        }

        function unhighlight() {
            dropArea.classList.remove('highlight');
        }

        dropArea.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            imageInput.files = files;
            handleFiles(files);
        }

        // ========== PREVISUALIZACIÓN DE IMAGEN ==========
        imageInput.addEventListener('change', function() {
            handleFiles(this.files);
        });

        function handleFiles(files) {
            if (files.length > 0) {
                const file = files[0];
                
                // Validar tamaño
                if (file.size > 2 * 1024 * 1024) {
                    showError('¡El archivo es demasiado pesado! Máximo 2MB');
                    imageInput.value = '';
                    return;
                }

                // Validar tipo
                if (!file.type.startsWith('image/')) {
                    showError('¡Solo imágenes! (JPG, PNG, GIF)');
                    imageInput.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    previewContainer.style.display = 'block';
                    dropArea.style.display = 'none';
                }
                reader.readAsDataURL(file);
            }
        }

        // ========== VALIDACIONES ==========
        // Validar descuento
        const discountInput = document.getElementById('discount');
        if (discountInput) {
            discountInput.addEventListener('input', function() {
                let value = parseInt(this.value);
                if (isNaN(value) || value < 0) this.value = 0;
                if (value > 100) this.value = 100;
            });
        }

        // Validar precio
        const priceInput = document.getElementById('price');
        if (priceInput) {
            priceInput.addEventListener('input', function() {
                let value = parseFloat(this.value);
                if (isNaN(value) || value < 0) this.value = 0;
            });
        }

        // Validar fecha de expiración
        const expiresInput = document.getElementById('expires_at');
        if (expiresInput) {
            expiresInput.addEventListener('change', function() {
                const today = new Date().toISOString().split('T')[0];
                if (this.value && this.value < today) {
                    showError('La fecha de expiración no puede ser en el pasado');
                    this.value = '';
                }
            });
        }

        // ========== PREVENIR DOBLE ENVÍO ==========
        if (form) {
            form.addEventListener('submit', function(e) {
                if (submitBtn.disabled) {
                    e.preventDefault();
                    return;
                }

                // Validar que haya imagen
                if (!imageInput.files.length) {
                    e.preventDefault();
                    showError('Debes seleccionar una imagen para la promoción');
                    return;
                }

                submitBtn.disabled = true;
                submitBtn.classList.add('loading');
                submitBtn.querySelector('span').textContent = 'Creando...';
            });
        }

        // ========== AUTO-CERRAR ALERTAS ==========
        const alerts = document.querySelectorAll('.alert-modern');
        alerts.forEach(alert => {
            setTimeout(() => {
                if (alert) {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }
            }, 5000);
        });

        // ========== FUNCIONES AUXILIARES ==========
        function showError(message) {
            // Crear alerta temporal
            const alert = document.createElement('div');
            alert.className = 'alert-modern error';
            alert.innerHTML = `
                <div class="alert-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="alert-content">
                    <strong>¡Error!</strong>
                    <p>${message}</p>
                </div>
                <button class="alert-close" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            const wrapper = document.querySelector('.rumbero-wrapper');
            wrapper.insertBefore(alert, wrapper.firstChild);
            
            setTimeout(() => {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }, 3000);
        }

        // Efectos hover
        document.querySelectorAll('.btn-rumbero, .btn-rumbero-secondary').forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
            });
            btn.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    });

    // ========== FUNCIÓN GLOBAL PARA REMOVER IMAGEN ==========
    function removeImage() {
        const imageInput = document.getElementById('image');
        const dropArea = document.getElementById('dropArea');
        const previewContainer = document.getElementById('image-preview-container');
        
        imageInput.value = '';
        previewContainer.style.display = 'none';
        dropArea.style.display = 'block';
    }
</script>
@endpush
@endsection