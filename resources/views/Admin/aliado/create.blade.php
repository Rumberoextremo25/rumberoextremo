@extends('layouts.admin')

@section('title', 'Añadir Nuevo Aliado')

@section('page_title', 'Añadir Nuevo Aliado')

@section('styles')
    <style>
        /* Optional: Add basic styling for alert messages if not globally defined */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-danger {
            color: #a94442;
            background-color: #f2dede;
            border-color: #ebccd1;
        }
        .text-danger {
            color: var(--danger-red); /* Assuming you have this CSS variable defined */
            font-size: 0.85em;
            margin-top: 5px;
            display: block;
        }
    </style>
@endsection

@section('content')
    <div class="add-ally-section">
        <h2>Información del Nuevo Aliado</h2>

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

        {{-- Form action must point to the store route --}}
        <form id="addAllyForm" action="{{ route('aliado.store') }}" method="POST">
            @csrf {{-- Laravel CSRF token for security --}}

            <div class="form-grid">
                <div class="form-group">
                    <label for="company_name">Nombre de la Empresa / Aliado:</label>
                    <input type="text" id="company_name" name="company_name" placeholder="Ej: Eventos Rumberos C.A." value="{{ old('company_name') }}" required>
                    @error('company_name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="company_rif">RIF de la Empresa:</label>
                    <input type="text" id="company_rif" name="company_rif" placeholder="Ej: J-12345678-9" value="{{ old('company_rif') }}">
                    @error('company_rif')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="service_category">Tipo de Aliado:</label>
                    <select id="service_category" name="service_category" required>
                        <option value="">Seleccione un tipo</option>
                        {{-- Itera sobre los tipos de aliados obtenidos de la base de datos --}}
                        @foreach($allyTypes as $allyType)
                            <option value="{{ $allyType->name }}" {{ old('service_category') == $allyType->name ? 'selected' : '' }}>
                                {{ $allyType->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('service_category')
                        <span class="text-danger">{{ $message }}</span>
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
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="contact_person_name">Persona de Contacto Principal:</label>
                    <input type="text" id="contact_person_name" name="contact_person_name" placeholder="Ej: Ana García" value="{{ old('contact_person_name') }}" required>
                    @error('contact_person_name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="contact_email">Correo Electrónico de Contacto:</label>
                    <input type="email" id="contact_email" name="contact_email" placeholder="Ej: contacto@empresa.com" value="{{ old('contact_email') }}" required>
                    @error('contact_email')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="contact_phone">Teléfono Principal:</label>
                    <input type="tel" id="contact_phone" name="contact_phone" placeholder="Ej: +58 412 1234567" value="{{ old('contact_phone') }}" required>
                    @error('contact_phone')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="contact_phone_alt">Teléfono Adicional (Opcional):</label>
                    <input type="tel" id="contact_phone_alt" name="contact_phone_alt" placeholder="Ej: +58 212 9876543" value="{{ old('contact_phone_alt') }}">
                    @error('contact_phone_alt')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group" style="grid-column: 1 / -1;">
                    <label for="company_address">Dirección Fiscal / Oficina:</label>
                    <textarea id="company_address" name="company_address" placeholder="Ej: Av. Libertador, Edif. Caracas, Piso 10, Ofic. 10B, Caracas, Venezuela" rows="3">{{ old('company_address') }}</textarea>
                    @error('company_address')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="website_url">Sitio Web (Opcional):</label>
                    <input type="url" id="website_url" name="website_url" placeholder="Ej: https://www.empresadelaliado.com" value="{{ old('website_url') }}">
                    @error('website_url')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="discount">Oferta de Descuento / Beneficio para Rumbero Extremo (Opcional):</label>
                    <input type="text" id="discount" name="discount" placeholder="Ej: 15% en alquiler de equipos, 2x1 en entradas" value="{{ old('discount') }}">
                    @error('discount')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="notes">Notas Internas (Opcional):</label>
                    <textarea id="notes" name="notes" placeholder="Cualquier información adicional relevante..." rows="3">{{ old('notes') }}</textarea>
                    @error('notes')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="registered_at">Fecha de Registro:</label>
                    <input type="date" id="registered_at" name="registered_at" value="{{ old('registered_at', date('Y-m-d')) }}" required>
                    @error('registered_at')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                {{-- Si user_id se maneja desde el formulario (e.g., input oculto o seleccionable) --}}
                {{-- <div class="form-group">
                    <label for="user_id">Usuario Asociado:</label>
                    <input type="number" id="user_id" name="user_id" value="{{ old('user_id', auth()->id()) }}" required>
                    @error('user_id')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div> --}}
            </div>

            <div class="button-group">
                <button type="button" class="cancel-btn" id="cancelAddAlly"><i class="fas fa-times-circle"></i> Cancelar</button>
                <button type="submit" class="submit-btn"><i class="fas fa-plus-circle"></i> Añadir Aliado</button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Script para activar el enlace "Aliados" en el sidebar
            const sidebarLinks = document.querySelectorAll('.sidebar ul li a');
            sidebarLinks.forEach(link => {
                link.classList.remove('active');
                // Check if current route is allies.index or allies.create/edit
                if (window.location.pathname.includes('/admin/allies')) {
                    link.classList.add('active');
                }
            });

            document.getElementById('cancelAddAlly').addEventListener('click', () => {
                if (confirm('¿Estás seguro de que quieres cancelar? Los cambios no guardados se perderán.')) {
                    // Redirect to the allies management view
                    window.location.href = "{{ route('aliado') }}"; // Asegúrate que esta ruta exista
                }
            });
        });
    </script>
@endsection