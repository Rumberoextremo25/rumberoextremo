{{-- resources/views/admin/banners/edit.blade.php --}}

@extends('layouts.admin')

@section('title', 'Editar Banner')

@push('styles')
    {{-- Usamos los mismos estilos del formulario de aliados para mantener la consistencia --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
@endpush

@section('content')
    <div class="main-content">
        <div class="form-card-container">
            <h2 class="form-title"><i class="fas fa-image"></i> Editar Banner: <span style="color: #6a0dad;">{{ $banner->title }}</span></h2>
            <p class="form-subtitle">Actualiza los detalles del banner y la imagen si es necesario.</p>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle alert-icon"></i>
                    <div class="alert-content">
                        <strong>¡Atención!</strong> Se encontraron los siguientes errores:
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form action="{{ route('admin.banners.update', $banner->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- Sección de Información del Banner --}}
                <h3 class="section-title"><i class="fas fa-info-circle"></i> Información General</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="title">Título:</label>
                        <input type="text" name="title" id="title" placeholder="Ej: Nuevo Colección Verano"
                            value="{{ old('title', $banner->title) }}" required>
                        @error('title')
                            <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="order">Orden de Visualización:</label>
                        <input type="number" name="order" id="order"
                            placeholder="Ej: 1 (Número para ordenar la visualización)" value="{{ old('order', $banner->order) }}">
                        @error('order')
                            <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group full-width">
                        <label for="description">Descripción (Opcional):</label>
                        <textarea name="description" id="description" rows="3"
                            placeholder="Una breve descripción o eslogan para el banner...">{{ old('description', $banner->description) }}</textarea>
                        @error('description')
                            <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="target_url">URL de Destino (Opcional):</label>
                        <input type="url" name="target_url" id="target_url"
                            placeholder="Ej: https://tutienda.com/nuevacoleccion" value="{{ old('target_url', $banner->target_url) }}">
                        @error('target_url')
                            <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>
                </div>
                
                {{-- Sección de Archivos --}}
                <h3 class="section-title"><i class="fas fa-upload"></i> Archivos e Imágenes</h3>
                <div class="form-group full-width">
                    <label for="image">Imagen (dejar en blanco para mantener la actual):</label>
                    <input type="file" name="image" id="image">
                    @error('image')
                        <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                    @enderror
                    @if($banner->image)
                        <div class="current-image-preview">
                            <p>Imagen actual:</p>
                            <img src="{{ asset('storage/' . $banner->image) }}" alt="{{ $banner->title }}">
                        </div>
                    @endif
                </div>

                {{-- Sección de Configuración --}}
                <h3 class="section-title"><i class="fas fa-cog"></i> Configuración</h3>
                <div class="form-grid">
                    <div class="form-group checkbox-group">
                        {{-- CAMPO OCULTO AÑADIDO PARA ASEGURAR QUE SE ENVÍA UN VALOR FALSE SI EL CHECKBOX NO ESTÁ MARCADO --}}
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $banner->is_active) ? 'checked' : '' }}>
                        <label for="is_active">Activo</label>
                        @error('is_active')
                            <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="button-group">
                    <a href="{{ route('admin.banners.index') }}" class="btn cancel-btn">
                        <i class="fas fa-times-circle"></i> Cancelar
                    </a>
                    <button type="submit" class="btn submit-btn">
                        <i class="fas fa-sync-alt"></i> Actualizar Banner
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection