{{-- resources/views/aliados/create.blade.php --}}

@extends('layouts.admin')

@section('title', 'Registrar Nuevo Aliado')

@section('page_title_toolbar', 'Registrar Nuevo Aliado')

@push('styles')
    {{-- Enlazamos al nuevo archivo CSS para el formulario de aliados --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
@endpush

@section('content')
    <div class="main-content">
        <div class="form-card-container">
            <h2 class="form-title"><i class="fas fa-handshake"></i> Registrar Nuevo Aliado</h2>
            <p class="form-subtitle">Completa los campos para añadir un nuevo aliado a tu sistema.</p>

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

            <form id="addAllyForm" action="{{ route('aliados.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- Sección de Información del Aliado --}}
                <h3 class="section-title"><i class="fas fa-building"></i> Información del Aliado</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="company_name">Nombre del Aliado / Empresa:</label>
                        <input type="text" id="company_name" name="company_name" placeholder="Ej: Eventos Rumberos C.A."
                            value="{{ old('company_name') }}" required>
                        @error('company_name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="company_rif">RIF de la Empresa:</label>
                        <input type="text" id="company_rif" name="company_rif" placeholder="Ej: J-12345678-9"
                            value="{{ old('company_rif') }}">
                        @error('company_rif')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group full-width">
                        <label for="description">Descripción del Aliado:</label>
                        <textarea id="description" name="description" placeholder="Breve descripción del aliado y los servicios que ofrece."
                            rows="3">{{ old('description') }}</textarea>
                        @error('description')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="image_url">Imagen del Aliado (Logo):</label>
                        <input type="file" id="image_url" name="image_url">
                        @error('image_url')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="contact_email">Correo Electrónico de Contacto:</label>
                        <input type="email" id="contact_email" name="contact_email" placeholder="Ej: contacto@empresa.com"
                            value="{{ old('contact_email') }}" required>
                        @error('contact_email')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="contact_phone">Teléfono Principal:</label>
                        <input type="tel" id="contact_phone" name="contact_phone" placeholder="Ej: +58 412 1234567"
                            value="{{ old('contact_phone') }}" required>
                        @error('contact_phone')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group full-width">
                        <label for="company_address">Dirección Fiscal / Oficina:</label>
                        <textarea id="company_address" name="company_address"
                            placeholder="Ej: Av. Libertador, Edif. Caracas, Piso 10, Ofic. 10B, Caracas, Venezuela" rows="3">{{ old('company_address') }}</textarea>
                        @error('company_address')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="category_name">Categoría de Negocio:</label>
                        <select id="category_name" name="category_name" required>
                            <option value="">Selecciona una categoría</option>
                            <option value="Restaurantes, Bares, Discotecas, Night Club, Juegos"
                                {{ old('category_name') == 'Restaurantes, Bares, Discotecas, Night Club, Juegos' ? 'selected' : '' }}>
                                Restaurantes, Bares, Discotecas, Night Club, Juegos</option>
                            <option value="Comidas, Bebidas, Cafes, Heladerias, Panaderias, Pastelerias"
                                {{ old('category_name') == 'Comidas, Bebidas, Cafes, Heladerias, Panaderias, Pastelerias' ? 'selected' : '' }}>
                                Comidas, Bebidas, Cafés, Heladerías, Panaderías, Pastelerías</option>
                            <option value="Deportes y Hobbies"
                                {{ old('category_name') == 'Deportes y Hobbies' ? 'selected' : '' }}>Deportes y Hobbies
                            </option>
                            <option value="Viajes y Turismo" {{ old('category_name') == 'Viajes y Turismo' ? 'selected' : '' }}>
                                Viajes y Turismo</option>
                            <option value="Eventos y Festejos"
                                {{ old('category_name') == 'Eventos y Festejos' ? 'selected' : '' }}>Eventos y Festejos
                            </option>
                        </select>
                        @error('category_name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="sub_category_name">Subcategoría de Negocio:</label>
                        <input type="text" id="sub_category_name" name="sub_category_name"
                            placeholder="Ej: Comida Rápida, Rock, Ropa Casual" value="{{ old('sub_category_name') }}">
                        @error('sub_category_name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="business_type_name">Tipo de Negocio:</label>
                        <select id="business_type_name" name="business_type_name" required>
                            <option value="">Selecciona un tipo de negocio</option>
                            <option value="Fisico" {{ old('business_type_name') == 'Fisico' ? 'selected' : '' }}>Físico
                            </option>
                            <option value="Online" {{ old('business_type_name') == 'Online' ? 'selected' : '' }}>Online
                            </option>
                            <option value="Servicio a domicilio"
                                {{ old('business_type_name') == 'Servicio a domicilio' ? 'selected' : '' }}>Servicio a
                                domicilio</option>
                        </select>
                        @error('business_type_name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="status">Estado del Aliado:</label>
                        <select id="status" name="status" required>
                            <option value="activo" {{ old('status') == 'activo' ? 'selected' : '' }}>Activo</option>
                            <option value="pendiente" {{ old('status') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="inactivo" {{ old('status') == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                        @error('status')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="contact_person_name">Persona de Contacto Principal:</label>
                        <input type="text" id="contact_person_name" name="contact_person_name"
                            placeholder="Ej: Ana García" value="{{ old('contact_person_name') }}" required>
                        @error('contact_person_name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="registered_at">Fecha de Registro:</label>
                        <input type="date" id="registered_at" name="registered_at"
                            value="{{ old('registered_at', date('Y-m-d')) }}" required>
                        @error('registered_at')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="discount">Oferta de Descuento / Beneficio (Opcional):</label>
                        <input type="text" id="discount" name="discount"
                            placeholder="Ej: 15% en alquiler de equipos, 2x1 en entradas" value="{{ old('discount') }}">
                        @error('discount')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Sección de Datos de la Cuenta de Usuario --}}
                <h3 class="section-title"><i class="fas fa-user-circle"></i> Datos de la Cuenta de Acceso</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="user_name">Nombre de Usuario:</label>
                        <input type="text" id="user_name" name="user_name" placeholder="Ej: Juan Pérez"
                            value="{{ old('user_name') }}" required>
                        @error('user_name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="user_email">Correo Electrónico de Acceso (Único):</label>
                        <input type="email" id="user_email" name="user_email" placeholder="Ej: usuario@rumbero.app"
                            value="{{ old('user_email') }}" required>
                        @error('user_email')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="user_password">Contraseña:</label>
                        <input type="password" id="user_password" name="user_password" placeholder="Mínimo 8 caracteres"
                            required>
                        @error('user_password')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="user_password_confirmation">Confirmar Contraseña:</label>
                        <input type="password" id="user_password_confirmation" name="user_password_confirmation"
                            placeholder="Repita la contraseña" required>
                        @error('user_password_confirmation')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="button-group">
                    <button type="button" class="btn cancel-btn" id="cancelAddAlly"
                        onclick="window.location='{{ route('aliados.index') }}'">
                        <i class="fas fa-times-circle"></i> Cancelar
                    </button>
                    <button type="submit" class="btn submit-btn"><i class="fas fa-plus-circle"></i> Añadir Aliado</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Tus scripts, si los necesitas --}}
@endpush
