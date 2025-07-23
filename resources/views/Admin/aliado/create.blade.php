@extends('layouts.admin')

@section('title', 'Añadir Nuevo Aliado')
@section('page_title', 'Añadir Nuevo Aliado')

@section('styles')
    <style>
        /* Variables CSS para colores y espaciado */
        :root {
            --primary-color: #530cbf;
            --success-color: #7628a7;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --text-color: #333;
            --light-text-color: #666;
            --border-color: #eee;
            --bg-light: #f8f9fa;
            --spacing-unit: 1rem; /* 16px */
            --border-radius: 0.5rem; /* 8px */
            --shadow-light: 0 4px 8px rgba(0, 0, 0, 0.05);
            --input-focus-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        /* Contenedor principal del formulario */
        .add-ally-section {
            background-color: #ffffff;
            padding: calc(var(--spacing-unit) * 2); /* 32px */
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-light);
            max-width: 900px;
            margin: calc(var(--spacing-unit) * 2) auto;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Título del formulario */
        .add-ally-section h2 {
            color: var(--text-color);
            margin-bottom: calc(var(--spacing-unit) * 1.5);
            text-align: center;
            font-size: 2rem; /* 32px */
            font-weight: 600;
        }

        /* Sección de usuario */
        .user-account-section {
            background-color: var(--bg-light);
            padding: var(--spacing-unit);
            border-radius: var(--border-radius);
            margin-bottom: calc(var(--spacing-unit) * 1.5);
            border: 1px solid var(--border-color);
        }

        .user-account-section h3 {
            color: var(--primary-color);
            margin-top: 0;
            margin-bottom: var(--spacing-unit);
            font-size: 1.25rem;
            text-align: center;
        }


        /* Alertas de errores */
        .alert {
            padding: var(--spacing-unit);
            margin-bottom: calc(var(--spacing-unit) * 1.5);
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            font-size: 0.95rem;
            border: 1px solid transparent;
        }

        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        .alert-danger ul {
            margin: 0;
            padding-left: 20px;
        }

        .alert i {
            margin-right: 0.75rem;
            font-size: 1.2rem;
        }

        /* Diseño de la cuadrícula del formulario */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: calc(var(--spacing-unit) * 1.5); /* 24px */
        }

        /* Grupo de cada campo del formulario */
        .form-group {
            margin-bottom: 0; /* Elimina el margin-bottom para que el gap de la cuadrícula lo maneje */
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem; /* 8px */
            font-weight: 600;
            color: var(--light-text-color);
            font-size: 0.9rem; /* 14.4px */
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="tel"],
        .form-group input[type="url"],
        .form-group input[type="date"],
        .form-group input[type="password"], /* Añadido para passwords */
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem 1rem; /* 12px 16px */
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            box-sizing: border-box;
            font-size: 1rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            background-color: #fdfdfd;
            color: var(--text-color);
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="email"]:focus,
        .form-group input[type="tel"]:focus,
        .form-group input[type="url"]:focus,
        .form-group input[type="date"]:focus,
        .form-group input[type="password"]:focus, /* Añadido para passwords */
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: var(--primary-color);
            box-shadow: var(--input-focus-shadow);
            outline: none;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        /* Mensajes de error de validación */
        .form-group .text-danger {
            color: var(--danger-color);
            font-size: 0.85em; /* 13.6px */
            margin-top: 0.4rem; /* 6.4px */
            display: block;
        }

        /* Campos de ancho completo en la cuadrícula */
        .form-group.full-width {
            grid-column: 1 / -1; /* Ocupa todas las columnas disponibles */
        }

        /* Grupo de botones al final del formulario */
        .button-group {
            display: flex;
            justify-content: flex-end;
            gap: var(--spacing-unit); /* 16px */
            margin-top: calc(var(--spacing-unit) * 2); /* 32px */
            padding-top: calc(var(--spacing-unit) * 1.5); /* 24px */
            border-top: 1px solid var(--border-color);
        }

        .button-group button {
            padding: 0.75rem 1.5rem; /* 12px 24px */
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.6rem; /* 9.6px */
            transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }

        .button-group .submit-btn {
            background-color: var(--success-color);
            color: white;
        }

        .button-group .submit-btn:hover {
            background-color: #270c9e; /* Un tono más oscuro del primary-color */
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(40, 167, 69, 0.2);
        }

        .button-group .cancel-btn {
            background-color: #6c757d; /* Un gris más neutro para cancelar */
            color: white;
        }

        .button-group .cancel-btn:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(108, 117, 125, 0.2);
        }

        /* Iconos en los botones */
        .button-group button i {
            font-size: 1.1rem;
        }

        /* Ajustes responsivos */
        @media (max-width: 768px) {
            .add-ally-section {
                margin: var(--spacing-unit) auto;
                padding: calc(var(--spacing-unit) * 1.2);
                max-width: 95%;
            }

            .add-ally-section h2 {
                font-size: 1.7rem;
                margin-bottom: var(--spacing-unit);
            }

            .form-grid {
                grid-template-columns: 1fr; /* Una columna en pantallas pequeñas */
                gap: var(--spacing-unit);
            }

            .form-group input,
            .form-group select,
            .form-group textarea {
                padding: 0.6rem 0.8rem;
                font-size: 0.95rem;
            }

            .button-group {
                flex-direction: column;
                align-items: stretch;
                gap: 0.8rem;
                padding-top: var(--spacing-unit);
            }

            .button-group button {
                width: 100%;
                justify-content: center;
                padding: 0.7rem 1.2rem;
                font-size: 0.95rem;
            }
        }
    </style>
@endsection

@section('content')
    <div class="add-ally-section">
        <h2>Añadir Nuevo Aliado</h2>

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

            {{-- Sección de Datos de la Cuenta de Usuario --}}
            <div class="user-account-section">
                <h3><i class="fas fa-user-circle"></i> Datos de la Cuenta de Acceso</h3>
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
            </div>
            {{-- Fin Sección de Datos de la Cuenta de Usuario --}}

            {{-- Información Básica del Aliado --}}
            <div class="form-grid">
                <div class="form-group">
                    <label for="company_name">Nombre de la Empresa / Aliado:</label>
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

                {{-- Categoría y Subcategoría (desplegables dinámicos) --}}
                <div class="form-group">
                    <label for="category_id">Categoría:</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">Seleccione una Categoría</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}"
                                {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="sub_category_id">Subcategoría (Opcional):</label>
                    <select id="sub_category_id" name="sub_category_id">
                        <option value="">Seleccione una Subcategoría</option>
                        {{-- Las opciones se cargarán vía JavaScript --}}
                    </select>
                    @error('sub_category_id')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Tipo de Negocio y Estado --}}
                <div class="form-group">
                    <label for="business_type_id">Tipo de Negocio:</label>
                    <select id="business_type_id" name="business_type_id" required>
                        <option value="">Seleccione un Tipo de Negocio</option>
                        @foreach ($businessTypes as $businessType)
                            <option value="{{ $businessType->id }}"
                                {{ old('business_type_id') == $businessType->id ? 'selected' : '' }}>
                                {{ $businessType->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('business_type_id')
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

                {{-- Información de Contacto --}}
                <div class="form-group">
                    <label for="contact_person_name">Persona de Contacto Principal:</label>
                    <input type="text" id="contact_person_name" name="contact_person_name"
                        placeholder="Ej: Ana García" value="{{ old('contact_person_name') }}" required>
                    @error('contact_person_name')
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
                <div class="form-group">
                    <label for="contact_phone_alt">Teléfono Adicional (Opcional):</label>
                    <input type="tel" id="contact_phone_alt" name="contact_phone_alt"
                        placeholder="Ej: +58 212 9876543" value="{{ old('contact_phone_alt') }}">
                    @error('contact_phone_alt')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Detalles Adicionales (Full Width) --}}
                <div class="form-group full-width">
                    <label for="company_address">Dirección Fiscal / Oficina:</label>
                    <textarea id="company_address" name="company_address"
                        placeholder="Ej: Av. Libertador, Edif. Caracas, Piso 10, Ofic. 10B, Caracas, Venezuela" rows="3">{{ old('company_address') }}</textarea>
                    @error('company_address')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="website_url">Sitio Web (Opcional):</label>
                    <input type="url" id="website_url" name="website_url"
                        placeholder="Ej: https://www.empresadelaliado.com" value="{{ old('website_url') }}">
                    @error('website_url')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="discount">Oferta de Descuento / Beneficio (Opcional):</label>
                    <input type="text" id="discount" name="discount"
                        placeholder="Ej: 15% en alquiler de equipos, 2x1 en entradas" value="{{ old('discount') }}">
                    @error('discount')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group full-width">
                    <label for="notes">Notas Internas (Opcional):</label>
                    <textarea id="notes" name="notes" placeholder="Cualquier información adicional relevante..." rows="3">{{ old('notes') }}</textarea>
                    @error('notes')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="registered_at">Fecha de Registro:</label>
                    <input type="date" id="registered_at" name="registered_at"
                        value="{{ old('registered_at', date('Y-m-d')) }}" required>
                    @error('registered_at')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="button-group">
                <button type="button" class="cancel-btn" id="cancelAddAlly"><i class="fas fa-times-circle"></i>
                    Cancelar</button>
                <button type="submit" class="submit-btn"><i class="fas fa-plus-circle"></i> Añadir Aliado</button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    @parent {{-- Incluye los scripts del layout padre --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Script para activar el enlace "Aliados" en el sidebar
            const sidebarLinks = document.querySelectorAll('.sidebar ul li a');
            sidebarLinks.forEach(link => {
                link.classList.remove('active');
                if (window.location.pathname.includes('/admin/aliados')) {
                    if (link.getAttribute('href') && link.getAttribute('href').includes('/admin/aliados')) {
                        link.classList.add('active');
                    }
                }
            });

            // Script para botón de cancelar
            document.getElementById('cancelAddAlly').addEventListener('click', () => {
                if (confirm('¿Estás seguro de que quieres cancelar? Los cambios no guardados se perderán.')) {
                    window.location.href = "{{ route('aliados.index') }}";
                }
            });

            // --- Lógica para desplegables de Categoría y Subcategoría ---
            const categorySelect = document.getElementById('category_id');
            const subCategorySelect = document.getElementById('sub_category_id');

            // Función para cargar subcategorías
            const loadSubcategories = async (categoryId) => {
                subCategorySelect.innerHTML = '<option value="">Cargando Subcategorías...</option>'; // Mensaje de carga
                subCategorySelect.disabled = true; // Deshabilitar mientras carga

                if (!categoryId) {
                    subCategorySelect.innerHTML = '<option value="">Seleccione una Subcategoría</option>';
                    subCategorySelect.disabled = false;
                    return;
                }

                try {
                    // Realizar una petición AJAX al controlador para obtener subcategorías
                    // Asegúrate de que la ruta 'get.subcategories' esté definida en web.php
                    const response = await fetch(`{{ route('get.subcategories') }}?category_id=${categoryId}`);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    const subcategories = await response.json();

                    subCategorySelect.innerHTML = '<option value="">Seleccione una Subcategoría</option>';
                    if (subcategories.length > 0) {
                        subcategories.forEach(sub => {
                            const option = document.createElement('option');
                            option.value = sub.id;
                            option.textContent = sub.name;
                            // Para conservar el valor seleccionado si hay un old('sub_category_id')
                            if ("{{ old('sub_category_id') }}" == sub.id) {
                                option.selected = true;
                            }
                            subCategorySelect.appendChild(option);
                        });
                    } else {
                        subCategorySelect.innerHTML = '<option value="">No hay Subcategorías disponibles</option>';
                    }

                } catch (error) {
                    console.error('Error al cargar subcategorías:', error);
                    subCategorySelect.innerHTML = '<option value="">Error al cargar Subcategorías</option>';
                } finally {
                    subCategorySelect.disabled = false; // Habilitar select al finalizar
                }
            };

            // Event listener para el cambio en el select de Categorías
            categorySelect.addEventListener('change', (event) => {
                loadSubcategories(event.target.value);
            });

            // Cargar subcategorías al cargar la página si ya hay una categoría seleccionada (para old() en caso de error de validación)
            if (categorySelect.value) {
                loadSubcategories(categorySelect.value);
            }
        });
    </script>
@endsection