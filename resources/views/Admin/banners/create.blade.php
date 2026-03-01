{{-- resources/views/admin/banners/create.blade.php --}}

@extends('layouts.admin')

@section('title', 'Crear Nuevo Banner')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/banner-create.css') }}">
@endpush

@section('content')
<div class="allies-wrapper">
    {{-- HEADER CON GRADIENTE --}}
    <div class="allies-header-bar">
        <div class="header-content">
            <div class="page-title">
                <span class="title-main">Crear Nuevo</span>
                <span class="title-accent">Banner</span>
            </div>
            <div class="page-subtitle">
                <i class="fas fa-image"></i>
                <span>Completa el formulario para crear un nuevo banner publicitario</span>
            </div>
        </div>
        <div class="header-actions">
            <div class="user-greeting">
                <i class="fas fa-images"></i>
                <span>Nuevo <strong>Banner</strong></span>
            </div>
            <a href="{{ route('admin.banners.index') }}" class="btn-add">
                <i class="fas fa-arrow-left"></i>
                Volver al Listado
            </a>
        </div>
    </div>

    {{-- ALERTA DE ERRORES --}}
    @if ($errors->any())
        <div class="alert alert-error">
            <i class="fas fa-exclamation-triangle"></i>
            <div style="flex: 1;">
                <strong style="display: block; margin-bottom: 0.5rem;">¡Atención! Se encontraron los siguientes errores:</strong>
                <ul style="margin-left: 1.5rem;">
                    @foreach ($errors->all() as $error)
                        <li style="margin-bottom: 0.2rem;">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    {{-- FORMULARIO DE CREACIÓN --}}
    <div class="table-container">
        <div class="table-responsive" style="padding: 2rem;">
            <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- INFORMACIÓN GENERAL --}}
                <h3 class="section-title">
                    <i class="fas fa-info-circle"></i>
                    Información General
                </h3>

                <div class="form-grid">
                    {{-- Título --}}
                    <div class="full-width">
                        <div class="form-group">
                            <label for="title">
                                Título del Banner <span class="required-star">*</span>
                            </label>
                            <input type="text" 
                                   name="title" 
                                   id="title" 
                                   class="form-control @error('title') error @enderror"
                                   placeholder="Ej: Nueva Colección Verano" 
                                   value="{{ old('title') }}" 
                                   required>
                            @error('title')
                                <span class="error-message">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </span>
                            @enderror
                        </div>
                    </div>

                    {{-- Imagen --}}
                    <div class="full-width">
                        <div class="form-group">
                            <label for="image">
                                Imagen del Banner <span class="required-star">*</span>
                            </label>
                            <input type="file" 
                                   name="image" 
                                   id="image" 
                                   class="form-control-file @error('image') error @enderror"
                                   accept="image/*"
                                   required>
                            @error('image')
                                <span class="error-message">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </span>
                            @enderror
                            
                            {{-- Previsualización de la imagen --}}
                            <div id="image-preview-container" class="preview-container" style="display: none;">
                                <p>
                                    <i class="fas fa-eye"></i>
                                    Previsualización:
                                </p>
                                <img id="image-preview" src="#" alt="Vista Previa del Banner" class="preview-image">
                            </div>
                        </div>
                    </div>

                    {{-- URL de Destino --}}
                    <div>
                        <div class="form-group">
                            <label for="target_url">
                                URL de Destino 
                                <span class="optional-badge">Opcional</span>
                            </label>
                            <input type="url" 
                                   name="target_url" 
                                   id="target_url" 
                                   class="form-control @error('target_url') error @enderror"
                                   placeholder="https://tutienda.com/nuevacoleccion" 
                                   value="{{ old('target_url') }}">
                            <span class="field-hint">
                                <i class="fas fa-info-circle"></i> Dejar vacío si no redirige
                            </span>
                            @error('target_url')
                                <span class="error-message">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </span>
                            @enderror
                        </div>
                    </div>

                    {{-- Orden --}}
                    <div>
                        <div class="form-group">
                            <label for="order">
                                Orden de Visualización
                            </label>
                            <input type="number" 
                                   name="order" 
                                   id="order" 
                                   class="form-control @error('order') error @enderror"
                                   placeholder="Ej: 1" 
                                   value="{{ old('order', 0) }}"
                                   min="0">
                            <span class="field-hint">
                                <i class="fas fa-info-circle"></i> Números más bajos se muestran primero
                            </span>
                            @error('order')
                                <span class="error-message">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </span>
                            @enderror
                        </div>
                    </div>

                    {{-- Descripción --}}
                    <div class="full-width">
                        <div class="form-group">
                            <label for="description">
                                Descripción 
                                <span class="optional-badge">Opcional</span>
                            </label>
                            <textarea name="description" 
                                      id="description" 
                                      class="form-control @error('description') error @enderror"
                                      rows="4" 
                                      placeholder="Una breve descripción o eslogan para el banner...">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="error-message">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- CONFIGURACIÓN --}}
                <h3 class="section-title" style="margin-top: 2rem;">
                    <i class="fas fa-cog"></i>
                    Configuración
                </h3>

                <div class="form-grid">
                    {{-- Estado Activo/Inactivo --}}
                    <div class="full-width">
                        <div class="checkbox-wrapper">
                            <input type="checkbox" 
                                   name="is_active" 
                                   id="is_active" 
                                   value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label for="is_active">
                                Activar banner inmediatamente
                            </label>
                        </div>
                        <span class="field-hint" style="margin-left: 2rem;">
                            <i class="fas fa-info-circle"></i> Si está activo, el banner se mostrará en la página principal
                        </span>
                        @error('is_active')
                            <span class="error-message">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            </span>
                        @enderror
                    </div>
                </div>

                {{-- BARRA DE ACCIONES --}}
                <div class="form-actions">
                    <a href="{{ route('admin.banners.index') }}" class="btn-secondary">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </a>
                    <button type="submit" class="btn-add">
                        <i class="fas fa-save"></i>
                        Guardar Banner
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Previsualización de la imagen
        const imageInput = document.getElementById('image');
        const previewContainer = document.getElementById('image-preview-container');
        const previewImage = document.getElementById('image-preview');

        if (imageInput) {
            imageInput.addEventListener('change', function(event) {
                const [file] = event.target.files;
                if (file) {
                    // Validar tamaño del archivo (máximo 2MB)
                    if (file.size > 2 * 1024 * 1024) {
                        alert('El archivo es demasiado grande. El tamaño máximo es 2MB.');
                        this.value = '';
                        previewContainer.style.display = 'none';
                        return;
                    }

                    // Validar tipo de archivo
                    if (!file.type.startsWith('image/')) {
                        alert('Por favor, selecciona un archivo de imagen válido.');
                        this.value = '';
                        previewContainer.style.display = 'none';
                        return;
                    }

                    previewImage.src = URL.createObjectURL(file);
                    previewContainer.style.display = 'block';

                    // Limpiar el objeto URL cuando ya no sea necesario
                    previewImage.onload = function() {
                        URL.revokeObjectURL(previewImage.src);
                    }
                } else {
                    previewContainer.style.display = 'none';
                }
            });
        }

        // Validación en tiempo real del campo orden
        const orderInput = document.getElementById('order');
        if (orderInput) {
            orderInput.addEventListener('input', function() {
                let value = parseInt(this.value);
                if (isNaN(value) || value < 0) {
                    this.value = 0;
                }
            });
        }

        // Auto-cerrar alertas después de 7 segundos
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                if (alert) {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }
            }, 7000);
        });

        // Efectos hover suaves
        document.querySelectorAll('.btn-add, .btn-secondary').forEach(button => {
            button.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
            });

            button.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    });
</script>
@endpush
@endsection