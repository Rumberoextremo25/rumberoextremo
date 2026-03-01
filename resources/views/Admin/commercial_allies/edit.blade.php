@extends('layouts.admin')

@section('title', 'Editar Aliado Comercial')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/commercial-ally-edit.css') }}">
@endpush

@section('content')
    <div class="allies-wrapper">
        {{-- HEADER MODERNO CON GRADIENTE --}}
        <div class="allies-header-bar">
            <div class="header-content">
                <div class="page-title">
                    <span class="title-main">Editar Aliado Comercial:</span>
                    <span class="title-accent">{{ $commercialAlly->name }}</span>
                </div>
                <div class="page-subtitle">
                    <i class="fas fa-edit"></i>
                    <span>Modifica los datos del aliado comercial</span>
                </div>
            </div>
            <div class="header-actions">
                <div class="user-greeting">
                    <i class="fas fa-store"></i>
                    <span>ID: <strong>#{{ $commercialAlly->id }}</strong></span>
                </div>
                <a href="{{ route('admin.commercial-allies.index') }}" class="btn-add">
                    <i class="fas fa-arrow-left"></i>
                    Volver al Listado
                </a>
            </div>
        </div>

        {{-- ALERTAS MODERNAS --}}
        @if (session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
                <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ session('error') }}</span>
                <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif

        {{-- TARJETA DE INFORMACION DEL ALIADO --}}
        <div class="stats-grid" style="margin-bottom: 1.5rem;">
            <div class="stat-card" data-color="purple">
                <div class="stat-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value">{{ $commercialAlly->rating }}</span>
                    <span class="stat-label">Rating Actual</span>
                </div>
            </div>
            <div class="stat-card" data-color="green">
                <div class="stat-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value">{{ $commercialAlly->created_at->format('d/m/Y') }}</span>
                    <span class="stat-label">Fecha Creacion</span>
                </div>
            </div>
            <div class="stat-card" data-color="orange">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value">{{ $commercialAlly->updated_at->diffForHumans() }}</span>
                    <span class="stat-label">Ultima Actualizacion</span>
                </div>
            </div>
            <div class="stat-card" data-color="red">
                <div class="stat-icon">
                    <i class="fas fa-link"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value">{{ $commercialAlly->website_url ? 'Si' : 'No' }}</span>
                    <span class="stat-label">Sitio Web</span>
                </div>
            </div>
        </div>

        {{-- FORMULARIO DE EDICION --}}
        <div class="table-container">
            <div class="table-responsive" style="padding: 2rem;">
                <form action="{{ route('admin.commercial-allies.update', $commercialAlly->id) }}" method="POST"
                    enctype="multipart/form-data" id="editForm">
                    @csrf
                    @method('PUT')

                    {{-- PROGRESO DEL FORMULARIO --}}
                    <div class="form-progress">
                        <div class="progress-step completed">
                            <span class="step-indicator"><i class="fas fa-check"></i></span>
                            <span>Datos Basicos</span>
                        </div>
                        <div class="progress-step active">
                            <span class="step-indicator">2</span>
                            <span>Edicion</span>
                        </div>
                        <div class="progress-step">
                            <span class="step-indicator">3</span>
                            <span>Confirmar Cambios</span>
                        </div>
                    </div>

                    <div class="form-grid">
                        {{-- Nombre --}}
                        <div class="full-width">
                            <div class="form-group">
                                <label for="name">
                                    Nombre del Aliado <span class="required">*</span>
                                    <span class="tooltip-icon" data-tooltip="Nombre comercial del aliado">?</span>
                                </label>
                                <input type="text" name="name" id="name"
                                    class="form-control @error('name') error @enderror"
                                    placeholder="Ej: Tienda Deportiva El Gol"
                                    value="{{ old('name', $commercialAlly->name) }}" required>
                                @error('name')
                                    <span class="error-message"><i class="fas fa-exclamation-circle"></i>
                                        {{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Rating --}}
                        <div>
                            <div class="form-group">
                                <label for="rating">
                                    Rating <span class="tooltip-icon" data-tooltip="Valoracion de 0.0 a 5.0">?</span>
                                </label>
                                <div style="position: relative;">
                                    <input type="number" step="0.1" min="0" max="5" name="rating"
                                        id="rating" class="form-control @error('rating') error @enderror"
                                        placeholder="Ej: 4.5" value="{{ old('rating', $commercialAlly->rating) }}">
                                    <span class="field-hint">
                                        <i class="fas fa-info-circle"></i> Valor entre 0 y 5
                                    </span>
                                </div>
                                @error('rating')
                                    <span class="error-message"><i class="fas fa-exclamation-circle"></i>
                                        {{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Logo --}}
                        <div>
                            <div class="form-group">
                                <label for="logo">
                                    Logo <span class="required-badge">Opcional</span>
                                    <span class="tooltip-icon"
                                        data-tooltip="Dejar en blanco para mantener el actual">?</span>
                                </label>
                                <input type="file" name="logo" id="logo"
                                    class="form-control-file @error('logo') error @enderror" accept="image/*">
                                @error('logo')
                                    <span class="error-message"><i class="fas fa-exclamation-circle"></i>
                                        {{ $message }}</span>
                                @enderror

                                {{-- Contenedor de previsualizacion del logo --}}
                                <div id="logo-preview-container" class="logo-preview-container">
                                    @if ($commercialAlly->logo_url)
                                        <div class="current-logo-preview" id="current-logo-preview">
                                            <p>
                                                <i class="fas fa-image"></i>
                                                Logo actual:
                                            </p>
                                            <img id="current-logo-img" src="{{ asset('storage/' . $commercialAlly->logo_url) }}"
                                                alt="Logo actual de {{ $commercialAlly->name }}" class="logo-preview">
                                        </div>
                                    @endif
                                    <div id="new-logo-preview" class="new-logo-preview" style="display: none;">
                                        <p>
                                            <i class="fas fa-eye"></i>
                                            Previsualizacion del nuevo logo:
                                        </p>
                                        <img id="logo-preview" src="#" alt="Vista Previa del nuevo Logo"
                                            class="logo-preview">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Descripcion --}}
                        <div class="full-width">
                            <div class="form-group">
                                <label for="description">Descripcion <span class="required-badge">Opcional</span></label>
                                <textarea name="description" id="description" class="form-control @error('description') error @enderror"
                                    rows="4" placeholder="Una breve descripcion sobre el aliado comercial, sus servicios o productos...">{{ old('description', $commercialAlly->description) }}</textarea>
                                @error('description')
                                    <span class="error-message"><i class="fas fa-exclamation-circle"></i>
                                        {{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Estado Activo/Inactivo --}}
                        <div class="full-width">
                            <div class="checkbox-wrapper">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" id="is_active" value="1"
                                    {{ old('is_active', $commercialAlly->is_active) ? 'checked' : '' }}>
                                <label for="is_active">
                                    <i class="fas fa-bolt"></i>
                                    Aliado Activo
                                </label>
                            </div>
                            <span class="field-hint">
                                <i class="fas fa-info-circle"></i> Si está activo, el aliado se mostrará en el sitio
                            </span>
                        </div>

                        {{-- URL del Sitio Web --}}
                        <div class="full-width">
                            <div class="form-group">
                                <label for="website_url">URL del Sitio Web <span
                                        class="required-badge">Opcional</span></label>
                                <input type="url" name="website_url" id="website_url"
                                    class="form-control @error('website_url') error @enderror"
                                    placeholder="https://www.ejemplo.com"
                                    value="{{ old('website_url', $commercialAlly->website_url) }}">
                                @error('website_url')
                                    <span class="error-message"><i class="fas fa-exclamation-circle"></i>
                                        {{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- BARRA DE ACCIONES --}}
                    <div class="form-actions">
                        <a href="{{ route('admin.commercial-allies.index') }}" class="btn-secondary">
                            <i class="fas fa-times"></i>
                            Cancelar
                        </a>
                        <button type="submit" class="btn-primary" id="submitBtn">
                            <i class="fas fa-sync-alt"></i>
                            <span class="btn-text">Actualizar Aliado</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Previsualizacion del logo
                const logoInput = document.getElementById('logo');
                const newPreviewContainer = document.getElementById('new-logo-preview');
                const newPreviewImage = document.getElementById('logo-preview');
                const currentPreview = document.querySelector('.current-logo-preview');

                if (logoInput) {
                    logoInput.addEventListener('change', function(event) {
                        const [file] = event.target.files;
                        if (file) {
                            // Validar tamaño del archivo (maximo 2MB)
                            if (file.size > 2 * 1024 * 1024) {
                                alert('El archivo es demasiado grande. El tamaño maximo es 2MB.');
                                this.value = '';
                                return;
                            }

                            // Validar tipo de archivo
                            if (!file.type.startsWith('image/')) {
                                alert('Por favor, selecciona un archivo de imagen valido.');
                                this.value = '';
                                return;
                            }

                            newPreviewImage.src = URL.createObjectURL(file);
                            newPreviewContainer.style.display = 'block';

                            // Ocultar la previsualizacion del logo actual
                            if (currentPreview) {
                                currentPreview.style.display = 'none';
                            }

                            // Limpiar el objeto URL cuando ya no sea necesario
                            newPreviewImage.onload = function() {
                                URL.revokeObjectURL(newPreviewImage.src);
                            }
                        }
                    });
                }

                // Validacion en tiempo real del rating
                const ratingInput = document.getElementById('rating');
                if (ratingInput) {
                    ratingInput.addEventListener('input', function() {
                        let value = parseFloat(this.value);
                        if (isNaN(value)) {
                            this.value = '';
                        } else {
                            if (value < 0) this.value = 0;
                            if (value > 5) this.value = 5;
                            // Redondear a 1 decimal
                            if (this.value.includes('.')) {
                                const parts = this.value.split('.');
                                if (parts[1].length > 1) {
                                    this.value = parseFloat(this.value).toFixed(1);
                                }
                            }
                        }
                    });

                    ratingInput.addEventListener('blur', function() {
                        if (this.value === '') {
                            this.value = '0';
                        }
                    });
                }

                // Validacion del formulario antes de enviar
                const editForm = document.getElementById('editForm');
                const submitBtn = document.getElementById('submitBtn');

                if (editForm) {
                    editForm.addEventListener('submit', function(e) {
                        // Prevenir doble envio
                        if (submitBtn.disabled) {
                            e.preventDefault();
                            return;
                        }

                        // Deshabilitar boton y mostrar estado de carga
                        submitBtn.disabled = true;
                        submitBtn.classList.add('btn-loading');

                        // Cambiar texto del boton
                        const btnText = submitBtn.querySelector('.btn-text');
                        if (btnText) {
                            btnText.textContent = 'Actualizando...';
                        }
                    });
                }

                // Auto-cerrar alertas despues de 5 segundos
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    setTimeout(() => {
                        if (alert) {
                            alert.style.transition = 'opacity 0.5s ease';
                            alert.style.opacity = '0';
                            setTimeout(() => alert.remove(), 500);
                        }
                    }, 5000);
                });

                // Efectos hover suaves
                const buttons = document.querySelectorAll('.btn-primary, .btn-secondary, .btn-add');
                buttons.forEach(button => {
                    button.addEventListener('mouseenter', function() {
                        this.style.transform = 'translateY(-2px)';
                    });

                    button.addEventListener('mouseleave', function() {
                        this.style.transform = 'translateY(0)';
                    });
                });

                // Confirmacion antes de salir si hay cambios
                let formChanged = false;
                const formInputs = editForm.querySelectorAll('input, textarea');
                formInputs.forEach(input => {
                    input.addEventListener('change', () => {
                        formChanged = true;
                    });
                });

                window.addEventListener('beforeunload', function(e) {
                    if (formChanged && !submitBtn.disabled) {
                        e.preventDefault();
                        e.returnValue = '';
                    }
                });
            });
        </script>
    @endpush
@endsection
