@extends('layouts.admin')

@section('title', 'Crear Nuevo Aliado Comercial')

@section('content')
    @push('styles')
        {{-- Si necesitas estilos específicos para esta vista (menos probables aquí), créalos aquí --}}
        <link rel="stylesheet" href="{{ asset('css/admin/commercial-allies/create.css') }}">
    @endpush

    <div class="form-container">
        <h2 class="form-title">Crear Nuevo <span style="color: var(--secondary-color);">Aliado Comercial</span></h2>

        {{-- Mensajes de Éxito o Error (si los hubiera al redireccionar con errores de validación) --}}
        @if (session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('admin.commercial-allies.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label for="name">Nombre:</label>
                <input type="text" name="name" id="name" placeholder="Ej: Tienda Deportiva" value="{{ old('name') }}" required>
                @error('name')
                    <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="logo">Logo:</label>
                <input type="file" name="logo" id="logo" required>
                @error('logo')
                    <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                @enderror
                {{-- Preview para el logo nuevo --}}
                <div id="logo-preview-container" class="new-logo-preview" style="display: none;">
                    <p>Previsualización del nuevo logo:</p>
                    <img id="logo-preview" src="#" alt="Vista Previa del Logo">
                </div>
            </div>

            <div class="form-group">
                <label for="rating">Rating (0.0 - 5.0):</label>
                <input type="number" step="0.1" min="0" max="5" name="rating" id="rating" placeholder="Ej: 4.5" value="{{ old('rating', 0.0) }}">
                @error('rating')
                    <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="description">Descripción (Opcional):</label>
                <textarea name="description" id="description" rows="3" placeholder="Una breve descripción sobre el aliado comercial...">{{ old('description') }}</textarea>
                @error('description')
                    <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="website_url">URL del Sitio Web (Opcional):</label>
                <input type="url" name="website_url" id="website_url" placeholder="https://www.ejemplo.com" value="{{ old('website_url') }}">
                @error('website_url')
                    <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                @enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="submit-btn">
                    <i class="fas fa-save"></i> Guardar Aliado
                </button>
                <a href="{{ route('admin.commercial-allies.index') }}" class="cancel-link">
                    Cancelar
                </a>
            </div>
        </form>
    </div>

    @push('scripts')
        {{-- Script específico para la creación de aliados --}}
        <script src="{{ asset('js/admin/commercial-allies/create.js') }}"></script>
    @endpush
@endsection