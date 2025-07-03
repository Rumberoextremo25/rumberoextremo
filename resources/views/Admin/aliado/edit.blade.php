@extends('layouts.admin')

@section('title', 'Rumbero Extremo - Editar Aliado')

@section('page_title', 'Editar Aliado')

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
    <div class="edit-ally-section">
        <h2>Información del Aliado <span id="allyIdDisplay">(ID: {{ $ally->id }})</span></h2>

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

        <form id="editAllyForm" action="{{ route('aliado.update', $ally->id) }}" method="POST">
            @csrf {{-- Laravel CSRF token for security --}}
            @method('PUT')
            <input type="hidden" id="allyId" name="allyId" value="{{ $ally->id }}">

            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Nombre de la Empresa / Aliado:</label>
                    <input type="text" id="name" name="name" placeholder="Ej: Eventos Rumberos C.A." value="{{ old('name', $ally->name) }}" required>
                    @error('name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="rif">RIF de la Empresa:</label>
                    <input type="text" id="rif" name="rif" placeholder="Ej: J-12345678-9" value="{{ old('rif', $ally->rif) }}">
                    @error('rif')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="type">Tipo de Aliado:</label>
                    <select id="type" name="type" required>
                        <option value="">Seleccione un tipo</option>
                        {{-- Values must match what you want to store and validate --}}
                        <option value="Logística / Producción" {{ old('type', $ally->type) == 'Logística / Producción' ? 'selected' : '' }}>Logística / Producción</option>
                        <option value="Local / Venue" {{ old('type', $ally->type) == 'Local / Venue' ? 'selected' : '' }}>Local / Venue</option>
                        <option value="Audio / Iluminación" {{ old('type', $ally->type) == 'Audio / Iluminación' ? 'selected' : '' }}>Audio / Iluminación</option>
                        <option value="Alimentos y Bebidas" {{ old('type', $ally->type) == 'Alimentos y Bebidas' ? 'selected' : '' }}>Alimentos y Bebidas</option>
                        <option value="Transporte" {{ old('type', $ally->type) == 'Transporte' ? 'selected' : '' }}>Transporte</option>
                        <option value="Seguridad" {{ old('type', $ally->type) == 'Seguridad' ? 'selected' : '' }}>Seguridad</option>
                        <option value="Medios / Publicidad" {{ old('type', $ally->type) == 'Medios / Publicidad' ? 'selected' : '' }}>Medios / Publicidad</option>
                        <option value="Otros" {{ old('type', $ally->type) == 'Otros' ? 'selected' : '' }}>Otros</option>
                    </select>
                    @error('type')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="status">Estado del Aliado:</label>
                    <select id="status" name="status" required>
                        {{-- Values must match what your controller validates with 'in:activo,inactivo,pendiente' --}}
                        <option value="activo" {{ old('status', $ally->status) == 'activo' ? 'selected' : '' }}>Activo</option>
                        <option value="pendiente" {{ old('status', $ally->status) == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                        <option value="inactivo" {{ old('status', $ally->status) == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                    </select>
                    @error('status')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="contact_person">Persona de Contacto Principal:</label>
                    <input type="text" id="contact_person" name="contact_person" placeholder="Ej: Ana García" value="{{ old('contact_person', $ally->contact_person) }}" required>
                    @error('contact_person')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="contact_email">Correo Electrónico de Contacto:</label>
                    <input type="email" id="contact_email" name="contact_email" placeholder="Ej: contacto@empresa.com" value="{{ old('contact_email', $ally->contact_email) }}" required>
                    @error('contact_email')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="phone">Teléfono Principal:</label>
                    <input type="tel" id="phone" name="phone" placeholder="Ej: +58 412 1234567" value="{{ old('phone', $ally->phone) }}" required>
                    @error('phone')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="phone_alt">Teléfono Adicional (Opcional):</label>
                    <input type="tel" id="phone_alt" name="phone_alt" placeholder="Ej: +58 212 9876543" value="{{ old('phone_alt', $ally->phone_alt) }}">
                    @error('phone_alt')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group" style="grid-column: 1 / -1;">
                    <label for="address">Dirección Fiscal / Oficina:</label>
                    <textarea id="address" name="address" placeholder="Ej: Av. Libertador, Edif. Caracas, Piso 10, Ofic. 10B, Caracas, Venezuela" rows="3">{{ old('address', $ally->address) }}</textarea>
                    @error('address')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="website">Sitio Web (Opcional):</label>
                    <input type="url" id="website" name="website" placeholder="Ej: https://www.empresadelaliado.com" value="{{ old('website', $ally->website) }}">
                    @error('website')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="discount_offer">Oferta de Descuento / Beneficio para Rumbero Extremo (Opcional):</label>
                    <input type="text" id="discount_offer" name="discount_offer" placeholder="Ej: 15% en alquiler de equipos, 2x1 en entradas" value="{{ old('discount_offer', $ally->discount_offer) }}">
                    @error('discount_offer')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="notes">Notas Internas (Opcional):</label>
                    <textarea id="notes" name="notes" placeholder="Cualquier información adicional relevante..." rows="3">{{ old('notes', $ally->notes) }}</textarea>
                    @error('notes')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="registration_date">Fecha de Registro:</label>
                    {{-- Format date for HTML input, and use old() with fallback --}}
                    <input type="date" id="registration_date" name="registration_date" value="{{ old('registration_date', $ally->registration_date ? $ally->registration_date->format('Y-m-d') : '') }}" required>
                    @error('registration_date')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="button-group">
                <button type="button" class="cancel-btn" id="cancelEditAlly"><i class="fas fa-times-circle"></i> Cancelar</button>
                <button type="submit" class="submit-btn"><i class="fas fa-save"></i> Actualizar Aliado</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts') {{-- Use @push to add scripts to the stack defined in the layout --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Script to activate the "Aliados" link in the sidebar
            const sidebarLinks = document.querySelectorAll('.sidebar ul li a');
            sidebarLinks.forEach(link => {
                link.classList.remove('active');
                // Check if current route is allies.index or allies.create/edit
                if (window.location.pathname.includes('/admin/allies')) {
                    link.classList.add('active');
                }
            });

            document.getElementById('cancelEditAlly').addEventListener('click', () => {
                if (confirm('¿Estás seguro de que quieres cancelar? Los cambios no guardados se perderán.')) {
                    // Redirect to the allies management view
                    window.location.href = "{{ route('aliado') }}";
                }
            });
        });
    </script>
@endpush