@extends('layouts.admin')

@section('title', 'Crear Nuevo Banner')

{{-- Incluye los estilos CSS definidos --}}
@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    {{-- Enlaza al archivo CSS específico para la vista de banners, que ahora incluye los estilos de formulario --}}
@endsection

@section('content')
    <div class="form-container">
        <h2 class="form-title">Crear Nuevo <span style="color: var(--secondary-color);">Banner</span></h2>

        <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label for="title">Título:</label>
                <input type="text" name="title" id="title" placeholder="Ej: Nueva Colección Verano" value="{{ old('title') }}" required>
                @error('title')
                    <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="image">Imagen:</label>
                <input type="file" name="image" id="image" required>
                @error('image')
                    <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="description">Descripción (Opcional):</label>
                <textarea name="description" id="description" rows="3" placeholder="Una breve descripción o eslogan para el banner...">{{ old('description') }}</textarea>
                @error('description')
                    <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="target_url">URL de Destino (Opcional):</label>
                <input type="url" name="target_url" id="target_url" placeholder="Ej: https://tutienda.com/nuevacoleccion" value="{{ old('target_url') }}">
                @error('target_url')
                    <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="order">Orden:</label>
                <input type="number" name="order" id="order" placeholder="Ej: 1 (Número para ordenar la visualización)" value="{{ old('order', 0) }}">
                @error('order')
                    <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                @enderror
            </div>

            <div class="form-group checkbox-group">
                <input type="checkbox" name="is_active" id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                <label for="is_active">Activo</label>
                @error('is_active')
                    <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                @enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="submit-btn">
                    <i class="fas fa-save"></i> Guardar Banner
                </button>
                <a href="{{ route('admin.banners.index') }}" class="cancel-link">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin/banner.js') }}"></script>
@endpush