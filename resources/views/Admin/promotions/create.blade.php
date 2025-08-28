@extends('layouts.admin')

@section('title', 'Crear Nueva Promoción')

@section('content')

    <div class="card-container">
        <div class="card-header">
            <h2 class="card-title">Crear Nueva <span style="color: #8a3ffc;">Promoción</span></h2>
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

        <form action="{{ route('admin.promotions.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-grid">
                {{-- Título --}}
                <div class="form-group full-width">
                    <label for="title">Título:</label>
                    <input type="text" name="title" id="title" placeholder="Ej: 20% de descuento en Zapatos" value="{{ old('title') }}" required>
                    @error('title')
                        <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                    @enderror
                </div>

                {{-- Imagen --}}
                <div class="form-group full-width">
                    <label for="image">Imagen:</label>
                    <input type="file" name="image" id="image" required>
                    @error('image')
                        <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                    @enderror
                    <div id="image-preview-container" class="new-image-preview" style="display: none;">
                        <p>Previsualización de la imagen:</p>
                        <img id="image-preview" src="#" alt="Vista Previa de la Imagen">
                    </div>
                </div>

                {{-- Descuento --}}
                <div class="form-group">
                    <label for="discount">Descuento:</label>
                    <input type="text" name="discount" id="discount" placeholder="Ej: 20% OFF" value="{{ old('discount') }}" required>
                    @error('discount')
                        <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                    @enderror
                </div>

                {{-- Precio --}}
                <div class="form-group">
                    <label for="price">Precio:</label>
                    <input type="text" name="price" id="price" placeholder="Ej: 19.99" value="{{ old('price') }}" required>
                    @error('price')
                        <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                    @enderror
                </div>

                {{-- Fecha de Expiración --}}
                <div class="form-group full-width">
                    <label for="expires_at">Fecha de Expiración (Opcional):</label>
                    <input type="date" name="expires_at" id="expires_at" value="{{ old('expires_at') }}">
                    @error('expires_at')
                        <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                    @enderror
                </div>

                {{-- Descripción --}}
                <div class="form-group full-width">
                    <label for="description">Descripción (Opcional):</label>
                    <textarea name="description" id="description" rows="3" placeholder="Una descripción detallada de la promoción y sus condiciones...">{{ old('description') }}</textarea>
                    @error('description')
                        <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="submit-btn">
                    <i class="fas fa-save"></i> Guardar Promoción
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
                    const previewContainer = document.getElementById('image-preview-container');
                    const previewImage = document.getElementById('image-preview');
                    previewImage.src = URL.createObjectURL(file);
                    previewContainer.style.display = 'block';
                }
            });
        </script>
    @endpush
@endsection