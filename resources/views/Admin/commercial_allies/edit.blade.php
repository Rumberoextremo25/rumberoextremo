@extends('layouts.admin')

@section('title', 'Editar Aliado Comercial')

@section('content')
    @push('styles')
        {{-- Si necesitas estilos específicos para esta vista, créalos aquí --}}
        <link rel="stylesheet" href="{{ asset('css/admin/commercial-allies/edit.css') }}">
    @endpush

    <div class="form-container">
        <h2 class="form-title">Editar Aliado Comercial: <span style="color: var(--secondary-color);">{{ $commercialAlly->name }}</span></h2>

        {{-- Mensajes de Éxito o Error --}}
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

            <div class="form-group">
                <label for="name">Nombre:</label>
                <input type="text" name="name" id="name" placeholder="Ej: Tienda Deportiva" value="{{ old('name', $commercialAlly->name) }}" required>
                @error('name')
                    <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="logo">Logo (dejar en blanco para mantener el actual):</label>
                <input type="file" name="logo" id="logo">
                @error('logo')
                    <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                @enderror
                @if($commercialAlly->logo_url)
                    <div class="current-logo-preview">
                        <p>Logo actual:</p>
                        <img src="{{ $commercialAlly->logo_url }}" alt="{{ $commercialAlly->name }}">
                    </div>
                @endif
            </div>

            <div class="form-group">
                <label for="rating">Rating (0.0 - 5.0):</label>
                <input type="number" step="0.1" min="0" max="5" name="rating" id="rating" placeholder="Ej: 4.5" value="{{ old('rating', $commercialAlly->rating) }}">
                @error('rating')
                    <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="description">Descripción (Opcional):</label>
                <textarea name="description" id="description" rows="3" placeholder="Una breve descripción sobre el aliado comercial...">{{ old('description', $commercialAlly->description) }}</textarea>
                @error('description')
                    <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="website_url">URL del Sitio Web (Opcional):</label>
                <input type="url" name="website_url" id="website_url" placeholder="https://www.ejemplo.com" value="{{ old('website_url', $commercialAlly->website_url) }}">
                @error('website_url')
                    <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                @enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="submit-btn">
                    <i class="fas fa-sync-alt"></i> Actualizar Aliado
                </button>
                <a href="{{ route('admin.commercial-allies.index') }}" class="cancel-link">
                    Cancelar
                </a>
            </div>
        </form>
    </div>

    @push('scripts')
        {{-- Aquí puedes incluir JavaScript específico para la edición de aliados si es necesario --}}
        {{-- Por ejemplo, vista previa de imagen o validaciones dinámicas --}}
        <script src="{{ asset('js/admin/commercial-allies/edit.js') }}"></script>
    @endpush
@endsection