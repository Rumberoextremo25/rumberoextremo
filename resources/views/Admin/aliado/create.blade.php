@extends('layouts.admin')

@section('title', 'Registrar Nuevo Aliado')
@section('page_title', 'Registrar Nuevo Aliado')

{{-- No need for @section('styles') here if all styles are in external CSS --}}

@section('content')
    <div class="form-container">
        <h2 class="section-title"><i class="fas fa-handshake"></i> Registrar Nuevo Aliado</h2>

        @if ($errors->any())
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>¡Atención!</strong> Se encontraron los siguientes errores:
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form id="addAllyForm" action="{{ route('aliados.store') }}" method="POST">
            @csrf

            ---

            {{-- Sección de Datos de la Cuenta de Usuario --}}
            <h3 class="section-title"><i class="fas fa-user-circle"></i> Datos de la Cuenta de Acceso</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="user_name">Nombre de Usuario:</label>
                    <input type="text" id="user_name" name="user_name" placeholder="Ej: Juan Pérez"
                        value="{{ old('user_name') }}" required>
                    @error('user_name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="user_email">Correo Electrónico de Acceso (Único):</label>
                    <input type="email" id="user_email" name="user_email" placeholder="Ej: usuario@rumbero.app"
                        value="{{ old('user_email') }}" required>
                    @error('user_email')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="user_password">Contraseña:</label>
                    <input type="password" id="user_password" name="user_password" placeholder="Mínimo 8 caracteres"
                        required>
                    @error('user_password')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="user_password_confirmation">Confirmar Contraseña:</label>
                    <input type="password" id="user_password_confirmation" name="user_password_confirmation"
                        placeholder="Repita la contraseña" required>
                    @error('user_password_confirmation')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            ---

            {{-- Sección de Información del Aliado --}}
            <h3 class="section-title"><i class="fas fa-building"></i> Información del Aliado</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="company_name">Nombre del Aliado / Empresa:</label>
                    <input type="text" id="company_name" name="company_name" placeholder="Ej: Eventos Rumberos C.A."
                        value="{{ old('company_name') }}" required>
                    @error('company_name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="company_rif">RIF de la Empresa:</label>
                    <input type="text" id="company_rif" name="company_rif" placeholder="Ej: J-12345678-9"
                        value="{{ old('company_rif') }}">
                    @error('company_rif')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="contact_email">Correo Electrónico de Contacto:</label>
                    <input type="email" id="contact_email" name="contact_email"
                        placeholder="Ej: contacto@empresa.com" value="{{ old('contact_email') }}" required>
                    @error('contact_email')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="contact_phone">Teléfono Principal:</label>
                    <input type="tel" id="contact_phone" name="contact_phone" placeholder="Ej: +58 412 1234567"
                        value="{{ old('contact_phone') }}" required>
                    @error('contact_phone')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group full-width">
                    <label for="company_address">Dirección Fiscal / Oficina:</label>
                    <textarea id="company_address" name="company_address"
                        placeholder="Ej: Av. Libertador, Edif. Caracas, Piso 10, Ofic. 10B, Caracas, Venezuela" rows="3">{{ old('company_address') }}</textarea>
                    @error('company_address')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Campos convertidos a INPUT TYPE="TEXT" --}}
                <div class="form-group">
                    <label for="category_name">Categoría de Negocio:</label>
                    <input type="text" id="category_name" name="category_name" placeholder="Ej: Restaurante, Bar, Tienda"
                        value="{{ old('category_name') }}" required>
                    @error('category_name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="sub_category_name">Subcategoría de Negocio (Opcional):</label>
                    <input type="text" id="sub_category_name" name="sub_category_name" placeholder="Ej: Comida Rápida, Rock, Ropa Casual"
                        value="{{ old('sub_category_name') }}">
                    @error('sub_category_name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="business_type_name">Tipo de Negocio:</label>
                    <input type="text" id="business_type_name" name="business_type_name" placeholder="Ej: Físico, Online, Híbrido"
                        value="{{ old('business_type_name') }}" required>
                    @error('business_type_name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                {{-- Fin de campos convertidos --}}

                {{-- CAMPO DE ESTADO --}}
                <div class="form-group">
                    <label for="status">Estado del Aliado:</label>
                    <select id="status" name="status" required>
                        <option value="activo" {{ old('status') == 'activo' ? 'selected' : '' }}>Activo</option>
                        <option value="pendiente" {{ old('status') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                        <option value="inactivo" {{ old('status') == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                    </select>
                    @error('status')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                {{-- FIN CAMPO DE ESTADO --}}

                {{-- CAMPO PERSONA DE CONTACTO PRINCIPAL AÑADIDO --}}
                <div class="form-group">
                    <label for="contact_person_name">Persona de Contacto Principal:</label>
                    <input type="text" id="contact_person_name" name="contact_person_name"
                        placeholder="Ej: Ana García" value="{{ old('contact_person_name') }}" required>
                    @error('contact_person_name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                {{-- FIN CAMPO PERSONA DE CONTACTO PRINCIPAL --}}

                {{-- CAMPO FECHA DE REGISTRO AÑADIDO --}}
                <div class="form-group">
                    <label for="registered_at">Fecha de Registro:</label>
                    <input type="date" id="registered_at" name="registered_at"
                        value="{{ old('registered_at', date('Y-m-d')) }}" required>
                    @error('registered_at')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                {{-- FIN CAMPO FECHA DE REGISTRO --}}

                <div class="form-group">
                    <label for="discount">Oferta de Descuento / Beneficio (Opcional):</label>
                    <input type="text" id="discount" name="discount"
                        placeholder="Ej: 15% en alquiler de equipos, 2x1 en entradas" value="{{ old('discount') }}">
                    @error('discount')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            ---

            <div class="button-group">
                <button type="button" class="cancel-btn" id="cancelAddAlly" data-index-route="{{ route('aliados.index') }}">
                    <i class="fas fa-times-circle"></i> Cancelar
                </button>
                <button type="submit" class="submit-btn"><i class="fas fa-plus-circle"></i> Añadir Aliado</button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    @parent {{-- Incluye los scripts del layout padre --}}
    {{-- Link to the specific JavaScript for this view --}}
    <script src="{{ asset('js/admin/aliados.js') }}"></script>
@endsection
