@extends('layouts.admin')

@section('title', 'Actualizar Producto - Rumbero Extremo')

@section('page_title', 'Actualizar Producto')

@section('content')
    <div class="form-section">
        <h2>Editar Datos del Producto</h2>

        <form action="#" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="product_name">Nombre del Producto</label>
                <input type="text" id="product_name" name="product_name" value="{{ old('product_name', $product->product_name) }}" required>
                @error('product_name')
                    <small style="color: var(--danger-red);">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="aliado">Aliado</label>
                <select id="aliado" name="aliado_id" required>
                    <option value="">Selecciona un aliado</option>
                    {{-- Itera sobre tus aliados y selecciona el actual --}}
                    @foreach($allies as $aliado)
                        <option value="{{ $aliado->id }}" {{ old('aliado_id', $product->aliado_id) == $aliado->id ? 'selected' : '' }}>
                            {{ $aliado->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('aliado_id')
                    <small style="color: var(--danger-red);">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="description">Descripción</label>
                <textarea id="description" name="description" rows="4" required>{{ old('description', $product->description) }}</textarea>
                @error('description')
                    <small style="color: var(--danger-red);">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="base_price">Precio Base (USD)</label>
                <input type="number" id="base_price" name="base_price" step="0.01" min="0" value="{{ old('base_price', $product->base_price) }}" required>
                @error('base_price')
                    <small style="color: var(--danger-red);">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="discount">Descuento (%)</label>
                <input type="number" id="discount" name="discount" step="1" min="0" max="100" value="{{ old('discount', $product->discount) }}" placeholder="Ej: 10">
                @error('discount')
                    <small style="color: var(--danger-red);">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="product_image">Imagen del Producto</label>
                @if($product->image_path) {{-- Asumiendo que 'image_path' es la columna en tu DB --}}
                    <img src="{{ asset('storage/' . $product->image_path) }}" alt="Imagen actual del producto" class="current-image">
                    <small style="color: var(--text-color);">Imagen actual. Sube una nueva para reemplazarla.</small>
                @else
                    <small style="color: var(--text-color);">No hay imagen actual. Sube una para el producto.</small>
                @endif
                <input type="file" id="product_image" name="product_image" accept="image/*">
                @error('product_image')
                    <small style="color: var(--danger-red);">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="status">Estado</label>
                <select id="status" name="status" required>
                    <option value="disponible" {{ old('status', $product->status) == 'disponible' ? 'selected' : '' }}>Disponible</option>
                    <option value="no_disponible" {{ old('status', $product->status) == 'no_disponible' ? 'selected' : '' }}>No Disponible</option>
                    <option value="agotado" {{ old('status', $product->status) == 'agotado' ? 'selected' : '' }}>Agotado</option>
                    <option value="proximamente" {{ old('status', $product->status) == 'proximamente' ? 'selected' : '' }}>Próximamente</option>
                </select>
                @error('status')
                    <small style="color: var(--danger-red);">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-actions">
                <button type="button" class="cancel-button" onclick="history.back();">Cancelar</button>
                <button type="submit" class="submit-button">Actualizar Producto</button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>

    </script>
@endsection