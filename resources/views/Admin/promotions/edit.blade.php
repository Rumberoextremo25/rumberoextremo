@extends('layouts.admin')

@section('title', 'Editar Promoción')

@push('styles')
    {{-- Asegúrate de que Font Awesome y Google Fonts estén en tu layout admin global --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    {{-- Enlaza al nuevo archivo CSS para el formulario de promoción --}}
    <link rel="stylesheet" href="{{ asset('css/admin/promotions/form.css') }}">
@endpush

@section('content')
    <div class="form-container">
        <h2 class="form-title">Editar Promoción: <span style="color: var(--secondary-color);">{{ $promotion->title }}</span></h2>

        <form action="{{ route('admin.promotions.update', $promotion->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="title">Título:</label>
                <input type="text" name="title" id="title" placeholder="Ej: 20% de descuento en Zapatos" value="{{ old('title', $promotion->title) }}" required>
                @error('title')
                    <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="image">Imagen (dejar en blanco para mantener la actual):</label>
                <input type="file" name="image" id="image">
                @error('image')
                    <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                @enderror
                @if($promotion->image_url)
                    <div class="current-image-preview">
                        <p>Imagen actual:</p>
                        <img src="{{ $promotion->image_url }}" alt="{{ $promotion->title }}">
                    </div>
                @endif
            </div>

            <div class="form-group">
                <label for="discount">Descuento:</label>
                <input type="text" name="discount" id="discount" placeholder="Ej: 20% OFF" value="{{ old('discount', $promotion->discount) }}" required>
                @error('discount')
                    <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="price">Precio:</label>
                <input type="text" name="price" id="price" placeholder="Ej: 19.99" value="{{ old('price', $promotion->price) }}" required>
                @error('price')
                    <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="expires_at">Fecha de Expiración (Opcional):</label>
                <input type="date" name="expires_at" id="expires_at" value="{{ old('expires_at', $promotion->expires_at ? $promotion->expires_at->format('Y-m-d') : '') }}">
                @error('expires_at')
                    <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="description">Descripción (Opcional):</label>
                <textarea name="description" id="description" rows="3" placeholder="Una descripción detallada de la promoción y sus condiciones...">{{ old('description', $promotion->description) }}</textarea>
                @error('description')
                    <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                @enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="submit-btn">
                    <i class="fas fa-sync-alt"></i> Actualizar Promoción
                </button>
                <a href="{{ route('admin.promotions.index') }}" class="cancel-link">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    {{-- Si tuvieras scripts específicos para este formulario, irían aquí. --}}
    {{-- Por ahora, no hay scripts específicos necesarios basados en la vista proporcionada. --}}
@endpush