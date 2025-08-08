@extends('layouts.admin')

@section('title', 'Rumbero Extremo - Editar Aliado')
@section('page_title', 'Editar Aliado')

@section('content')
    <div class="form-container">
        <h2 class="section-title">
            <i class="fas fa-edit"></i> Editar Información del Aliado
            <span id="allyIdDisplay">(ID: {{ $ally->id }})</span>
        </h2>

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

        <form id="editAllyForm" action="{{ route('aliados.update', $ally->id) }}" method="POST" enctype="multipart/form-data">
            @csrf {{-- Laravel CSRF token for security --}}
            @method('PUT') {{-- Method spoofing for PUT request --}}


            {{-- Sección de Datos Generales del Aliado --}}
            <h3 class="section-title"><i class="fas fa-building"></i> Datos Generales del Aliado</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="company_name">Nombre de la Empresa / Aliado:</label>
                    <input type="text" id="company_name" name="company_name" placeholder="Ej: Eventos Rumberos C.A."
                        value="{{ old('company_name', $ally->company_name) }}" required aria-required="true">
                    @error('company_name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="company_rif">RIF de la Empresa:</label>
                    <input type="text" id="company_rif" name="company_rif" placeholder="Ej: J-12345678-9"
                        value="{{ old('company_rif', $ally->company_rif) }}">
                    @error('company_rif')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Nuevo campo de descripción --}}
                <div class="form-group full-width">
                    <label for="description">Descripción del Aliado:</label>
                    <textarea id="description" name="description" placeholder="Breve descripción del aliado y los servicios que ofrece."
                        rows="3">{{ old('description', $ally->description) }}</textarea>
                    @error('description')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Campo de imagen con vista previa --}}
                <div class="form-group">
                    <label for="image_url">Imagen del Aliado (Logo):</label>
                    @if ($ally->image_url)
                        <div class="current-image">
                            <p>Imagen actual:</p>
                            <img src="{{ asset('storage/' . $ally->image_url) }}" alt="Imagen de {{ $ally->company_name }}"
                                class="ally-image-preview">
                        </div>
                    @endif
                    <input type="file" id="image_url" name="image_url">
                    <p class="form-text">Deje este campo vacío si no desea cambiar la imagen actual.</p>
                    @error('image_url')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Campos convertidos a INPUT TYPE="TEXT" --}}
                <div class="form-group">
                    <label for="category_name">Categoría de Negocio:</label>
                    <select id="category_name" name="category_name"
                        class="form-control @error('category_name') is-invalid @enderror" required>
                        <option value="">Selecciona una categoría</option> {{-- Opción por defecto --}}
                        <option value="Restaurante" {{ old('category_name') == 'Restaurante' ? 'selected' : '' }}>
                            Restaurante</option>
                        <option value="Bar" {{ old('category_name') == 'Bar' ? 'selected' : '' }}>Bar</option>
                        <option value="Tienda" {{ old('category_name') == 'Tienda' ? 'selected' : '' }}>Tienda</option>
                        <option value="Discoteca" {{ old('category_name') == 'Discoteca' ? 'selected' : '' }}>Discoteca
                        </option>
                        <option value="Hotel" {{ old('category_name') == 'Hotel' ? 'selected' : '' }}>Hotel</option>
                        <option value="Agencia de Festejos"
                            {{ old('category_name') == 'Agencia de Festejos' ? 'selected' : '' }}>Agencia de Festejos
                        </option>
                        <option value="Organizador de Eventos"
                            {{ old('category_name') == 'Organizador de Eventos' ? 'selected' : '' }}>Organizador de Eventos
                        </option>
                        <option value="Catering" {{ old('category_name') == 'Catering' ? 'selected' : '' }}>Catering
                        </option>
                        <option value="Salon de Fiestas"
                            {{ old('category_name') == 'Salon de Fiestas' ? 'selected' : '' }}>Salón de Fiestas</option>
                        <option value="Transporte" {{ old('category_name') == 'Transporte' ? 'selected' : '' }}>Transporte
                        </option>
                        <option value="Seguridad" {{ old('category_name') == 'Seguridad' ? 'selected' : '' }}>Seguridad
                        </option>
                        <option value="Floristeria" {{ old('category_name') == 'Floristeria' ? 'selected' : '' }}>
                            Floristería</option>
                        <option value="Banda Musical" {{ old('category_name') == 'Banda Musical' ? 'selected' : '' }}>Banda
                            Musical</option>
                        <option value="DJ" {{ old('category_name') == 'DJ' ? 'selected' : '' }}>DJ</option>
                        <option value="Fotografia y Video"
                            {{ old('category_name') == 'Fotografia y Video' ? 'selected' : '' }}>Fotografía y Video
                        </option>
                        <option value="Alquiler de Equipos"
                            {{ old('category_name') == 'Alquiler de Equipos' ? 'selected' : '' }}>Alquiler de Equipos
                        </option>
                        <option value="Maquillaje y Estilismo"
                            {{ old('category_name') == 'Maquillaje y Estilismo' ? 'selected' : '' }}>Maquillaje y Estilismo
                        </option>
                        <option value="Decoracion" {{ old('category_name') == 'Decoracion' ? 'selected' : '' }}>Decoración
                        </option>
                        <option value="Imprenta" {{ old('category_name') == 'Imprenta' ? 'selected' : '' }}>Imprenta
                        </option>
                        <option value="Publicidad" {{ old('category_name') == 'Publicidad' ? 'selected' : '' }}>Publicidad
                        </option>
                        <option value="Otros" {{ old('category_name') == 'Otros' ? 'selected' : '' }}>Otros</option>

                        {{-- Puedes generar estas opciones dinámicamente si las tienes en una base de datos o constante --}}
                        {{-- @foreach ($categories as $category)
            <option value="{{ $category }}" {{ old('category_name') == $category ? 'selected' : '' }}>{{ $category }}</option>
        @endforeach --}}
                    </select>
                    @error('category_name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="sub_category_name">Subcategoría de Negocio:</label>
                    <input type="text" id="sub_category_name" name="sub_category_name"
                        placeholder="Ej: Comida Rápida, Rock, Ropa Casual"
                        value="{{ old('sub_category_name', $ally->subCategory ? $ally->subCategory->name : '') }}">
                    @error('sub_category_name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="business_type_name">Tipo de Negocio:</label>
                    <select id="business_type_name" name="business_type_name"
                        class="form-control @error('business_type_name') is-invalid @enderror" required>
                        <option value="">Selecciona un tipo de negocio</option> {{-- Opción por defecto --}}
                        <option value="Fisico" {{ old('business_type_name') == 'Fisico' ? 'selected' : '' }}>Físico
                        </option>
                        <option value="Online" {{ old('business_type_name') == 'Online' ? 'selected' : '' }}>Online
                        </option>
                        <option value="Servicio a domicilio"
                            {{ old('business_type_name') == 'Servicio a domicilio' ? 'selected' : '' }}>Servicio a
                            domicilio</option>
                    </select>
                    @error('business_type_name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                {{-- Fin de campos convertidos --}}

                {{-- ESTADO DEL ALIADO --}}
                <div class="form-group">
                    <label for="status">Estado del Aliado:</label>
                    <select id="status" name="status" required aria-required="true">
                        <option value="activo" {{ old('status', $ally->status) == 'activo' ? 'selected' : '' }}>Activo
                        </option>
                        <option value="pendiente" {{ old('status', $ally->status) == 'pendiente' ? 'selected' : '' }}>
                            Pendiente</option>
                        <option value="inactivo" {{ old('status', $ally->status) == 'inactivo' ? 'selected' : '' }}>
                            Inactivo</option>
                    </select>
                    @error('status')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            ---

            {{-- Sección de Información de Contacto --}}
            <h3 class="section-title"><i class="fas fa-phone-alt"></i> Información de Contacto</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="contact_person_name">Persona de Contacto Principal:</label>
                    <input type="text" id="contact_person_name" name="contact_person_name"
                        placeholder="Ej: Ana García" value="{{ old('contact_person_name', $ally->contact_person_name) }}"
                        required aria-required="true">
                    @error('contact_person_name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="contact_email">Correo Electrónico de Contacto:</label>
                    <input type="email" id="contact_email" name="contact_email" placeholder="Ej: contacto@empresa.com"
                        value="{{ old('contact_email', $ally->contact_email) }}" required aria-required="true">
                    @error('contact_email')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="contact_phone">Teléfono Principal:</label>
                    <input type="tel" id="contact_phone" name="contact_phone" placeholder="Ej: +58 412 1234567"
                        value="{{ old('contact_phone', $ally->contact_phone) }}" required aria-required="true">
                    @error('contact_phone')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="contact_phone_alt">Teléfono Adicional (Opcional):</label>
                    <input type="tel" id="contact_phone_alt" name="contact_phone_alt"
                        placeholder="Ej: +58 212 9876543"
                        value="{{ old('contact_phone_alt', $ally->contact_phone_alt) }}">
                    @error('contact_phone_alt')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            ---

            {{-- Sección Otros Detalles --}}
            <h3 class="section-title"><i class="fas fa-info-circle"></i> Otros Detalles</h3>
            <div class="form-grid">
                <div class="form-group full-width">
                    <label for="company_address">Dirección Fiscal / Oficina:</label>
                    <textarea id="company_address" name="company_address"
                        placeholder="Ej: Av. Libertador, Edif. Caracas, Piso 10, Ofic. 10B, Caracas, Venezuela" rows="3">{{ old('company_address', $ally->company_address) }}</textarea>
                    @error('company_address')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="website_url">Sitio Web (Opcional):</label>
                    <input type="url" id="website_url" name="website_url"
                        placeholder="Ej: https://www.empresadelaliado.com"
                        value="{{ old('website_url', $ally->website_url) }}">
                    @error('website_url')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="discount">Oferta de Descuento / Beneficio (Opcional):</label>
                    <input type="text" id="discount" name="discount"
                        placeholder="Ej: 15% en alquiler de equipos, 2x1 en entradas"
                        value="{{ old('discount', $ally->discount) }}">
                    @error('discount')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group full-width">
                    <label for="notes">Notas Internas (Opcional):</label>
                    <textarea id="notes" name="notes" placeholder="Cualquier información adicional relevante..." rows="3">{{ old('notes', $ally->notes) }}</textarea>
                    @error('notes')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="registered_at">Fecha de Registro:</label>
                    <input type="date" id="registered_at" name="registered_at"
                        value="{{ old('registered_at', $ally->registered_at ? $ally->registered_at->format('Y-m-d') : '') }}"
                        required aria-required="true">
                    @error('registered_at')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>


            <div class="button-group">
                <button type="button" class="cancel-btn" id="cancelEditAlly"
                    data-index-route="{{ route('aliados.index') }}">
                    <i class="fas fa-times-circle"></i> Cancelar
                </button>
                <button type="submit" class="submit-btn">
                    <i class="fas fa-save"></i> Actualizar Aliado
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    {{-- Use @push to add scripts to the stack defined in the layout --}}
    {{-- This will load the general add-ally.js (now renamed to ally-form.js for broader use) --}}
    <script src="{{ asset('js/admin/aliados.js') }}"></script>
@endpush
