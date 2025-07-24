@extends('layouts.admin')

@section('title', 'Crear Nueva Promoción')

@push('styles')
    {{-- Asegúrate de que Font Awesome y Google Fonts estén en tu layout admin global --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    {{-- Enlaza al archivo CSS compartido para el formulario de promoción --}}
    <link rel="stylesheet" href="{{ asset('css/admin/promotion.css') }}">
@endpush

@section('content')
    <div class="form-container">
        <h2 class="form-title">Crear Nueva <span style="color: var(--secondary-color);">Promoción</span></h2>

        <form action="{{ route('admin.promotions.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label for="title">Título:</label>
                <input type="text" name="title" id="title" placeholder="Ej: 20% de descuento en Zapatos" value="{{ old('title') }}" required>
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
                <label for="discount">Descuento:</label>
                <input type="text" name="discount" id="discount" placeholder="Ej: 20% OFF" value="{{ old('discount') }}" required>
                @error('discount')
                    <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="price">Precio:</label>
                <input type="text" name="price" id="price" placeholder="Ej: 19.99" value="{{ old('price') }}" required>
                @error('price')
                    <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="expires_at">Fecha de Expiración (Opcional):</label>
                <input type="date" name="expires_at" id="expires_at" value="{{ old('expires_at') }}">
                @error('expires_at')
                    <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="description">Descripción (Opcional):</label>
                <textarea name="description" id="description" rows="3" placeholder="Una descripción detallada de la promoción y sus condiciones...">{{ old('description') }}</textarea>
                @error('description')
                    <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                @enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="submit-btn">
                    <i class="fas fa-save"></i> Guardar Promoción
                </button>
                <a href="{{ route('admin.promotions.index') }}" class="cancel-link">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
@endsection