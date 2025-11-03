@extends('layouts.admin')

@section('title', 'Editar Aliado Comercial')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/commercial-edit-form.css') }}">
@endpush

@section('content')

    <div class="card-container">
        <div class="card-header">
            <h2 class="card-title">Editar Aliado Comercial: <span>{{ $commercialAlly->name }}</span></h2>
            <a href="{{ route('admin.commercial-allies.index') }}" class="back-link">
                <i class="fas fa-arrow-left"></i> Volver al Listado
            </a>
        </div>

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

        <form action="{{ route('admin.commercial-allies.update', $commercialAlly->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-grid">
                {{-- Nombre --}}
                <div class="form-group">
                    <label for="name">Nombre:</label>
                    <input type="text" name="name" id="name" placeholder="Ej: Tienda Deportiva" value="{{ old('name', $commercialAlly->name) }}" required>
                    @error('name')
                        <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                    @enderror
                </div>

                {{-- Rating --}}
                <div class="form-group">
                    <label for="rating">Rating (0.0 - 5.0):</label>
                    <input type="number" step="0.1" min="0" max="5" name="rating" id="rating" placeholder="Ej: 4.5" value="{{ old('rating', $commercialAlly->rating) }}">
                    @error('rating')
                        <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                    @enderror
                </div>

                {{-- Logo --}}
                <div class="form-group full-width">
                    <label for="logo">Logo (dejar en blanco para mantener el actual):</label>
                    <input type="file" name="logo" id="logo" accept="image/*">
                    @error('logo')
                        <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                    @enderror

                    {{-- Contenedor de previsualización del logo --}}
                    <div id="logo-preview-container" class="logo-preview-container">
                        @if($commercialAlly->logo_url)
                            <div class="current-logo-preview">
                                <p>Logo actual:</p>
                                <img id="current-logo-img" src="{{ $commercialAlly->logo_url }}" alt="Logo actual de {{ $commercialAlly->name }}">
                            </div>
                        @endif
                        <div id="new-logo-preview" class="new-logo-preview" style="display: none;">
                            <p>Previsualización del nuevo logo:</p>
                            <img id="logo-preview" src="#" alt="Vista Previa del nuevo Logo">
                        </div>
                    </div>
                </div>

                {{-- Descripción --}}
                <div class="form-group full-width">
                    <label for="description">Descripción (Opcional):</label>
                    <textarea name="description" id="description" rows="3" placeholder="Una breve descripción sobre el aliado comercial...">{{ old('description', $commercialAlly->description) }}</textarea>
                    @error('description')
                        <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                    @enderror
                </div>

                {{-- URL del Sitio Web --}}
                <div class="form-group full-width">
                    <label for="website_url">URL del Sitio Web (Opcional):</label>
                    <input type="url" name="website_url" id="website_url" placeholder="https://www.ejemplo.com" value="{{ old('website_url', $commercialAlly->website_url) }}">
                    @error('website_url')
                        <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="submit-btn">
                    <i class="fas fa-sync-alt"></i> Actualizar Aliado
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            // Script para la previsualización del logo
            document.getElementById('logo').addEventListener('change', function(event) {
                const [file] = event.target.files;
                if (file) {
                    const newPreviewContainer = document.getElementById('new-logo-preview');
                    const newPreviewImage = document.getElementById('logo-preview');
                    newPreviewImage.src = URL.createObjectURL(file);
                    newPreviewContainer.style.display = 'block';

                    // Ocultar la previsualización del logo actual si hay un nuevo logo
                    const currentPreview = document.querySelector('.current-logo-preview');
                    if (currentPreview) {
                        currentPreview.style.display = 'none';
                    }
                }
            });

            // Mejora: Validación en tiempo real
            document.addEventListener('DOMContentLoaded', function() {
                const ratingInput = document.getElementById('rating');
                if (ratingInput) {
                    ratingInput.addEventListener('input', function() {
                        const value = parseFloat(this.value);
                        if (value < 0) this.value = 0;
                        if (value > 5) this.value = 5;
                    });
                }

                // Efectos hover en botones
                const buttons = document.querySelectorAll('.back-link, .submit-btn');
                buttons.forEach(button => {
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