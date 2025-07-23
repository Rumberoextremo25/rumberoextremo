@extends('layouts.admin')

@section('title', 'Editar Banner')

{{-- Incluye los estilos CSS definidos --}}
@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* Variables CSS para colores y espaciado */
        :root {
            --primary-color: #530cbf; /* Morado principal */
            --secondary-color: #7628a7; /* Morado secundario */
            --success-color: #28a745; /* Verde para éxito */
            --danger-color: #dc3545; /* Rojo para peligro */
            --warning-color: #ffc107; /* Amarillo para advertencia */
            --info-color: #17a2b8; /* Azul claro para información */
            --text-color: #343a40; /* Color de texto oscuro */
            --light-text-color: #6c757d; /* Color de texto más claro */
            --border-color: #dee2e6; /* Color de borde general */
            --bg-light: #f8f9fa; /* Fondo claro */
            --card-bg: #ffffff; /* Fondo de tarjeta */
            --spacing-unit: 1rem; /* 16px */
            --border-radius: 0.5rem; /* 8px */
            --shadow-light: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); /* Sombra ligera */
            --transition-speed: 0.3s;
        }

        body {
            background-color: var(--bg-light);
            color: var(--text-color);
            font-family: 'Inter', sans-serif; /* Fuente moderna */
            margin: 0;
        }

        /* Contenedor principal del formulario */
        .form-container {
            background-color: var(--card-bg);
            padding: calc(var(--spacing-unit) * 2); /* 32px */
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-light);
            margin: calc(var(--spacing-unit) * 2) auto; /* 32px auto */
            max-width: 700px; /* Ancho máximo para el formulario */
            box-sizing: border-box;
        }

        /* Título de la sección */
        .form-title {
            color: var(--primary-color);
            font-size: 2rem;
            margin-bottom: calc(var(--spacing-unit) * 1.5);
            font-weight: 700;
            text-align: center; /* Centrar el título */
        }

        .form-title span {
            color: var(--secondary-color);
        }

        /* Grupos de formulario */
        .form-group {
            margin-bottom: calc(var(--spacing-unit) * 1.25); /* Espacio entre campos */
        }

        .form-group label {
            display: block;
            color: var(--text-color);
            font-size: 0.9rem; /* Tamaño de fuente para etiquetas */
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .form-group input[type="text"],
        .form-group input[type="file"],
        .form-group input[type="number"],
        .form-group input[type="url"],
        .form-group input[type="date"],
        .form-group textarea {
            width: 100%;
            padding: 0.75rem 1rem; /* Padding uniforme */
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 0.95rem;
            color: var(--text-color);
            background-color: var(--bg-light); /* Fondo más claro para inputs */
            transition: border-color var(--transition-speed) ease, box-shadow var(--transition-speed) ease;
            box-sizing: border-box; /* Asegura que padding y border se incluyan en el ancho */
        }

        .form-group input[type="text"]::placeholder,
        .form-group textarea::placeholder {
            color: var(--light-text-color);
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="file"]:focus,
        .form-group input[type="number"]:focus,
        .form-group input[type="url"]:focus,
        .form-group input[type="date"]:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(83, 12, 191, 0.25); /* Sombra de enfoque */
            background-color: var(--card-bg); /* Fondo blanco al enfocar */
        }

        /* Estilo específico para input[type="file"] */
        .form-group input[type="file"] {
            padding-top: 0.6rem; /* Ajuste para el file input */
            padding-bottom: 0.6rem;
            cursor: pointer;
        }

        /* Estilo para textarea */
        .form-group textarea {
            resize: vertical; /* Permite redimensionar verticalmente */
            min-height: 80px; /* Altura mínima */
        }

        /* Estilo para checkbox */
        .form-group.checkbox-group {
            display: flex;
            align-items: center;
            margin-bottom: calc(var(--spacing-unit) * 1.25);
        }

        .form-group.checkbox-group input[type="checkbox"] {
            width: auto; /* Ancho automático para no tomar todo el ancho */
            margin-right: 0.6rem; /* Espacio entre checkbox y label */
            transform: scale(1.2); /* Agrandar un poco el checkbox */
            cursor: pointer;
        }

        .form-group.checkbox-group label {
            margin-bottom: 0; /* Eliminar margen inferior de la etiqueta */
            font-weight: normal; /* No tan bold como otras etiquetas */
            color: var(--text-color);
        }

        /* Estilos para la imagen del banner actual */
        .current-image-preview {
            display: flex;
            align-items: center;
            margin-top: 0.75rem;
            gap: 1rem;
            flex-wrap: wrap; /* Permite que el texto y la imagen se envuelvan */
        }

        .current-image-preview p {
            margin: 0;
            color: var(--light-text-color);
            font-size: 0.85rem;
            font-weight: 500;
        }

        .current-image-preview img {
            width: 128px; /* w-32 */
            height: auto;
            border-radius: var(--border-radius);
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        /* Mensajes de error de validación */
        .error-message {
            color: var(--danger-color);
            font-size: 0.8rem;
            margin-top: 0.5rem;
            display: block; /* Asegura que cada error esté en su propia línea */
        }

        /* Botones de acción */
        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: calc(var(--spacing-unit) * 2);
            flex-wrap: wrap; /* Para que los botones se envuelvan en móvil */
            gap: var(--spacing-unit);
        }

        .submit-btn {
            background-color: var(--primary-color); /* Usamos primary-color para actualizar */
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: background-color var(--transition-speed) ease, transform 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
        }

        .submit-btn:hover {
            background-color: #6a1aeb; /* Un morado más oscuro */
            transform: translateY(-2px);
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
        }

        .submit-btn i {
            font-size: 1.1rem;
        }

        .cancel-link {
            color: var(--light-text-color);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            padding: 0.75rem 1.5rem;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            transition: color var(--transition-speed) ease, border-color var(--transition-speed) ease;
        }

        .cancel-link:hover {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .form-container {
                padding: calc(var(--spacing-unit) * 1.25);
                margin: var(--spacing-unit) auto;
                max-width: 95%;
            }

            .form-title {
                font-size: 1.8rem;
                margin-bottom: var(--spacing-unit);
            }

            .form-actions {
                flex-direction: column;
                align-items: stretch; /* Estira los botones a todo el ancho */
                gap: 0.8rem;
            }

            .submit-btn,
            .cancel-link {
                width: 100%;
                justify-content: center; /* Centra el contenido del botón */
                text-align: center; /* Asegura el texto centrado si no es flex */
                padding: 0.65rem 1.2rem;
                font-size: 0.9rem;
            }

            .current-image-preview {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
        }
    </style>
@endsection

@section('content')
    <div class="form-container">
        <h2 class="form-title">Editar Banner: <span style="color: var(--secondary-color);">{{ $banner->title }}</span></h2>

        <form action="{{ route('admin.banners.update', $banner->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="title">Título:</label>
                <input type="text" name="title" id="title" placeholder="Ej: Nuevo Colección Verano" value="{{ old('title', $banner->title) }}" required>
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
                @if($banner->image_url)
                    <div class="current-image-preview">
                        <p>Imagen actual:</p>
                        <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}">
                    </div>
                @endif
            </div>

            <div class="form-group">
                <label for="description">Descripción (Opcional):</label>
                <textarea name="description" id="description" rows="3" placeholder="Una breve descripción o eslogan para el banner...">{{ old('description', $banner->description) }}</textarea>
                @error('description')
                    <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="target_url">URL de Destino (Opcional):</label>
                <input type="url" name="target_url" id="target_url" placeholder="Ej: https://tutienda.com/nuevacoleccion" value="{{ old('target_url', $banner->target_url) }}">
                @error('target_url')
                    <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="order">Orden:</label>
                <input type="number" name="order" id="order" placeholder="Ej: 1 (Número para ordenar la visualización)" value="{{ old('order', $banner->order) }}">
                @error('order')
                    <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                @enderror
            </div>

            <div class="form-group checkbox-group">
                {{-- CAMPO OCULTO AÑADIDO PARA ASEGURAR QUE SE ENVÍA UN VALOR FALSE SI EL CHECKBOX NO ESTÁ MARCADO --}}
                <input type="hidden" name="is_active" value="0"> 
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $banner->is_active) ? 'checked' : '' }}>
                <label for="is_active">Activo</label>
                @error('is_active')
                    <span class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                @enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="submit-btn">
                    <i class="fas fa-sync-alt"></i> Actualizar Banner
                </button>
                <a href="{{ route('admin.banners.index') }}" class="cancel-link">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
@endsection