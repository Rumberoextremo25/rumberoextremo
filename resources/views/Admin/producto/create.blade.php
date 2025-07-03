@extends('layouts.admin')

@section('title', 'Crear Nuevo Producto - Rumbero Extremo')

@section('page_title', 'Crear Nuevo Producto')

@section('styles')
    {{-- Font Awesome for icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    {{-- Google Fonts - Inter for a modern and legible typography --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* --- CSS Variables for easy customization --- */
        
    </style>
@endsection

@section('content')
    <div class="form-section">
        <h2>Datos del Producto</h2>

        {{-- Display validation errors if any --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label for="name">Nombre del Producto</label>
                <input type="text" id="name" name="name" placeholder="Ej: Sonido Profesional XLR-500" value="{{ old('name') }}" required>
                @error('name')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="ally_name">Aliado</label>
                <input type="text" id="ally_name" name="ally_name" placeholder="Nombre del Aliado (Ej: Sonido Pro Venezuela)" value="{{ old('ally_name') }}" required>
                @error('ally_name')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="description">Descripción</label>
                <textarea id="description" name="description" placeholder="Descripción detallada del producto o servicio" rows="4">{{ old('description') }}</textarea>
                @error('description')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="base_price">Precio Base (USD)</label>
                <input type="number" id="base_price" name="base_price" step="0.01" min="0" placeholder="Ej: 250.00" value="{{ old('base_price') }}" required>
                @error('base_price')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="discount_percentage">Descuento (%)</label>
                <input type="number" id="discount_percentage" name="discount_percentage" step="1" min="0" max="100" value="{{ old('discount_percentage', 0) }}" placeholder="Ej: 10">
                @error('discount_percentage')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="product_image">Imagen del Producto</label>
                <input type="file" id="product_image" name="image_path" accept="image/*">
                <small style="color: var(--text-muted);">Opcional: Sube una imagen representativa del producto.</small>
                @error('image_path')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="status">Estado</label>
                <select id="status" name="status" required>
                    <option value="Disponible" {{ old('status') == 'Disponible' ? 'selected' : '' }}>Disponible</option>
                    <option value="No Disponible" {{ old('status') == 'No Disponible' ? 'selected' : '' }}>No Disponible</option>
                    <option value="Agotado" {{ old('status') == 'Agotado' ? 'selected' : '' }}>Agotado</option>
                </select>
                @error('status')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-actions">
                <button type="button" class="cancel-button" onclick="history.back();">Cancelar</button>
                <button type="submit" class="submit-button">Crear Producto</button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    {{-- No specific JavaScript needed for basic form functionality --}}
@endsection