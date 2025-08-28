@extends('layouts.admin')

@section('title', 'Editar Promoción')

@section('content')

    <div class="card-container">
        <div class="card-header">
            <h2 class="card-title">Editar Promoción: <span style="color: #8a3ffc;">{{ $promotion->title }}</span></h2>
            <a href="{{ route('admin.promotions.index') }}" class="back-link">
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

        <form action="{{ route('admin.promotions.update', $promotion->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-grid">
                {{-- Título --}}
                <div class="form-group full-width">
                    <label for="title">Título:</label>
                    <input type="text" name="title" id="title" placeholder="Ej: 20% de descuento en Zapatos" value="{{ old('title', $promotion->title) }}" required>
                    @error('title')
                        <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                    @enderror
                </div>

                {{-- Imagen --}}
                <div class="form-group full-width">
                    <label for="image">Imagen (dejar en blanco para mantener la actual):</label>
                    <input type="file" name="image" id="image">
                    @error('image')
                        <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                    @enderror
                    {{-- Contenedor de previsualización de imagen --}}
                    <div id="image-preview-container" class="image-preview-container">
                        @if($promotion->image_url)
                            <div class="current-image-preview">
                                <p>Imagen actual:</p>
                                <img id="current-image-img" src="{{ $promotion->image_url }}" alt="Imagen actual de {{ $promotion->title }}">
                            </div>
                        @endif
                        <div id="new-image-preview" class="new-image-preview" style="display: none;">
                            <p>Previsualización de la nueva imagen:</p>
                            <img id="image-preview" src="#" alt="Vista Previa de la nueva Imagen">
                        </div>
                    </div>
                </div>

                {{-- Descuento --}}
                <div class="form-group">
                    <label for="discount">Descuento:</label>
                    <input type="text" name="discount" id="discount" placeholder="Ej: 20% OFF" value="{{ old('discount', $promotion->discount) }}" required>
                    @error('discount')
                        <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                    @enderror
                </div>

                {{-- Precio --}}
                <div class="form-group">
                    <label for="price">Precio:</label>
                    <input type="text" name="price" id="price" placeholder="Ej: 19.99" value="{{ old('price', $promotion->price) }}" required>
                    @error('price')
                        <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                    @enderror
                </div>

                {{-- Fecha de Expiración --}}
                <div class="form-group full-width">
                    <label for="expires_at">Fecha de Expiración (Opcional):</label>
                    <input type="date" name="expires_at" id="expires_at" value="{{ old('expires_at', $promotion->expires_at ? $promotion->expires_at->format('Y-m-d') : '') }}">
                    @error('expires_at')
                        <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                    @enderror
                </div>

                {{-- Descripción --}}
                <div class="form-group full-width">
                    <label for="description">Descripción (Opcional):</label>
                    <textarea name="description" id="description" rows="3" placeholder="Una descripción detallada de la promoción y sus condiciones...">{{ old('description', $promotion->description) }}</textarea>
                    @error('description')
                        <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="submit-btn">
                    <i class="fas fa-sync-alt"></i> Actualizar Promoción
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            // Script para la previsualización de la imagen
            document.getElementById('image').addEventListener('change', function(event) {
                const [file] = event.target.files;
                if (file) {
                    const newPreviewContainer = document.getElementById('new-image-preview');
                    const newPreviewImage = document.getElementById('image-preview');
                    newPreviewImage.src = URL.createObjectURL(file);
                    newPreviewContainer.style.display = 'block';

                    // Ocultar la previsualización de la imagen actual
                    const currentPreview = document.querySelector('.current-image-preview');
                    if (currentPreview) {
                        currentPreview.style.display = 'none';
                    }
                }
            });
        </script>
    @endpush
@endsection