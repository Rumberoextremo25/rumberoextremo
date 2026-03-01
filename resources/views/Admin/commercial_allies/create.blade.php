@extends('layouts.admin')

@section('title', 'Crear Nuevo Aliado Comercial')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/commercial-create.css') }}">
@endpush

@section('content')
<div class="allies-wrapper">
    {{-- HEADER MODERNO --}}
    <div class="allies-header-bar">
        <div class="header-content">
            <div class="page-title">
                <span class="title-main">Crear Nuevo</span>
                <span class="title-accent">Aliado Comercial</span>
            </div>
            <div class="page-subtitle">
                <i class="fas fa-store-alt"></i>
                <span>Completa el formulario para registrar un nuevo aliado comercial</span>
            </div>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.commercial-allies.index') }}" class="btn-add">
                <i class="fas fa-arrow-left"></i>
                Volver al Listado
            </a>
        </div>
    </div>

    {{-- ALERTAS --}}
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

    {{-- FORMULARIO DE CREACIÓN --}}
    <div class="table-container">
        <div class="table-responsive" style="padding: 2rem;">
            <form action="{{ route('admin.commercial-allies.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
                    {{-- Nombre --}}
                    <div style="grid-column: span 2 / span 2;">
                        <div class="form-group">
                            <label for="name" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #1e293b;">
                                Nombre del Aliado <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   id="name" 
                                   placeholder="Ej: Tienda Deportiva El Gol" 
                                   value="{{ old('name') }}" 
                                   required
                                   style="width: 100%; padding: 0.8rem 1rem; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 0.95rem; background: #f8fafc;">
                            @error('name')
                                <span style="color: #ef4444; font-size: 0.85rem; margin-top: 0.3rem; display: flex; align-items: center; gap: 0.3rem;">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </span>
                            @enderror
                        </div>
                    </div>

                    {{-- Rating --}}
                    <div>
                        <div class="form-group">
                            <label for="rating" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #1e293b;">
                                Rating (0.0 - 5.0)
                            </label>
                            <input type="number" 
                                   step="0.1" 
                                   min="0" 
                                   max="5" 
                                   name="rating" 
                                   id="rating" 
                                   placeholder="Ej: 4.5" 
                                   value="{{ old('rating', 0.0) }}"
                                   style="width: 100%; padding: 0.8rem 1rem; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 0.95rem; background: #f8fafc;">
                            @error('rating')
                                <span style="color: #ef4444; font-size: 0.85rem; margin-top: 0.3rem; display: flex; align-items: center; gap: 0.3rem;">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </span>
                            @enderror
                        </div>
                    </div>

                    {{-- Logo --}}
                    <div>
                        <div class="form-group">
                            <label for="logo" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #1e293b;">
                                Logo <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="file" 
                                   name="logo" 
                                   id="logo" 
                                   required
                                   style="width: 100%; padding: 0.8rem; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 0.95rem; background: #f8fafc;">
                            @error('logo')
                                <span style="color: #ef4444; font-size: 0.85rem; margin-top: 0.3rem; display: flex; align-items: center; gap: 0.3rem;">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </span>
                            @enderror
                            
                            {{-- Previsualización del logo --}}
                            <div id="logo-preview-container" 
                                 style="display: none; margin-top: 1rem; padding: 1rem; background: #f8fafc; border-radius: 12px; border: 2px dashed #e2e8f0; text-align: center;">
                                <p style="margin-bottom: 0.5rem; color: #64748b; font-size: 0.9rem;">Previsualización:</p>
                                <img id="logo-preview" 
                                     src="#" 
                                     alt="Vista Previa del Logo" 
                                     style="max-width: 150px; max-height: 150px; border-radius: 12px; border: 3px solid white; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                            </div>
                        </div>
                    </div>

                    {{-- Descripción --}}
                    <div style="grid-column: span 2 / span 2;">
                        <div class="form-group">
                            <label for="description" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #1e293b;">
                                Descripción (Opcional)
                            </label>
                            <textarea name="description" 
                                      id="description" 
                                      rows="4" 
                                      placeholder="Una breve descripción sobre el aliado comercial, sus servicios o productos..." 
                                      style="width: 100%; padding: 0.8rem 1rem; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 0.95rem; background: #f8fafc; resize: vertical;">{{ old('description') }}</textarea>
                            @error('description')
                                <span style="color: #ef4444; font-size: 0.85rem; margin-top: 0.3rem; display: flex; align-items: center; gap: 0.3rem;">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </span>
                            @enderror
                        </div>
                    </div>

                    {{-- URL del Sitio Web --}}
                    <div style="grid-column: span 2 / span 2;">
                        <div class="form-group">
                            <label for="website_url" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #1e293b;">
                                URL del Sitio Web (Opcional)
                            </label>
                            <input type="url" 
                                   name="website_url" 
                                   id="website_url" 
                                   placeholder="https://www.ejemplo.com" 
                                   value="{{ old('website_url') }}"
                                   style="width: 100%; padding: 0.8rem 1rem; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 0.95rem; background: #f8fafc;">
                            @error('website_url')
                                <span style="color: #ef4444; font-size: 0.85rem; margin-top: 0.3rem; display: flex; align-items: center; gap: 0.3rem;">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Botones de acción --}}
                <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #e9eef2;">
                    <a href="{{ route('admin.commercial-allies.index') }}" 
                       class="btn-secondary" 
                       style="padding: 0.8rem 2rem; text-decoration: none; background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; border-radius: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </a>
                    <button type="submit" 
                            class="btn-add" 
                            style="padding: 0.8rem 2rem; border: none; cursor: pointer;">
                        <i class="fas fa-save"></i>
                        Guardar Aliado
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/admin/commercial.js') }}"></script>
<script>
    // Script para la previsualización del logo
    document.getElementById('logo').addEventListener('change', function(event) {
        const [file] = event.target.files;
        if (file) {
            const previewContainer = document.getElementById('logo-preview-container');
            const previewImage = document.getElementById('logo-preview');
            previewImage.src = URL.createObjectURL(file);
            previewContainer.style.display = 'block';
            
            // Limpiar el objeto URL cuando ya no sea necesario
            previewImage.onload = function() {
                URL.revokeObjectURL(previewImage.src);
            }
        }
    });

    // Auto-cerrar alertas después de 5 segundos
    document.addEventListener('DOMContentLoaded', function() {
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
    });
</script>
@endpush
@endsection