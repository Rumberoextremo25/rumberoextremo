@extends('layouts.admin')

@section('title', 'Rumbero Extremo - Editar Aliado')
@section('page_title', 'Editar Aliado')

@section('styles')
    <style>
        /* Variables CSS para colores y espaciado */
        :root {
            --primary-color: #530cbf;
            --primary-dark-color: #420a9a; /* Added for hover */
            --success-color: #7628a7;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --text-color: #333;
            --light-text-color: #555; /* Slightly darker for better contrast */
            --border-color: #ddd; /* A bit softer */
            --bg-light: #f8f9fa;
            --spacing-unit: 1rem; /* 16px */
            --border-radius: 0.5rem; /* 8px */
            --shadow-light: 0 4px 12px rgba(0, 0, 0, 0.08); /* Slightly more prominent shadow */
            --input-focus-shadow: 0 0 0 0.25rem rgba(83, 12, 191, 0.25); /* Uses primary-color */
        }

        body {
            background-color: var(--bg-light);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
        }

        /* Contenedor principal del formulario */
        .edit-ally-section {
            background-color: #ffffff;
            padding: calc(var(--spacing-unit) * 2.5); /* 40px */
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-light);
            max-width: 900px;
            margin: calc(var(--spacing-unit) * 2) auto;
            box-sizing: border-box;
        }

        /* Título del formulario */
        .edit-ally-section h2 {
            color: var(--primary-color); /* Title uses primary color */
            margin-bottom: calc(var(--spacing-unit) * 2);
            text-align: center;
            font-size: 2.2rem; /* Larger title */
            font-weight: 700; /* Bolder */
        }

        .edit-ally-section h2 span {
            font-size: 1.3rem; /* Slightly larger ID display */
            color: var(--light-text-color);
            font-weight: 500;
            display: block; /* Ensures ID is on a new line or clearly separated */
            margin-top: 0.5rem;
        }

        /* Alertas de errores */
        .alert {
            padding: var(--spacing-unit);
            margin-bottom: calc(var(--spacing-unit) * 1.5);
            border-radius: var(--border-radius);
            display: flex;
            align-items: flex-start; /* Align icon to the top if message is long */
            font-size: 0.95rem;
            border: 1px solid transparent;
            box-sizing: border-box; /* Ensures padding is included in element's total width and height */
        }

        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        .alert-danger ul {
            margin: 0;
            padding-left: 1.5rem; /* Adjust padding for list */
            list-style-type: disc; /* Ensure disc bullets are visible */
        }

        .alert i {
            margin-right: 0.75rem;
            font-size: 1.4rem; /* Larger icon */
            flex-shrink: 0; /* Prevent icon from shrinking */
        }

        /* Fieldset for grouping form sections */
        fieldset {
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            padding: calc(var(--spacing-unit) * 1.5);
            margin-bottom: calc(var(--spacing-unit) * 2);
        }

        fieldset legend {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--primary-color);
            padding: 0 0.5rem;
            margin-left: -0.5rem; /* Counteract padding */
        }


        /* Diseño de la cuadrícula del formulario */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: calc(var(--spacing-unit) * 1.5); /* 24px */
        }

        /* Grupo de cada campo del formulario */
        .form-group {
            margin-bottom: 0;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.6rem; /* 9.6px, slightly more space */
            font-weight: 600;
            color: var(--light-text-color);
            font-size: 0.95rem; /* 15.2px, slightly larger */
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="tel"],
        .form-group input[type="url"],
        .form-group input[type="date"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.8rem 1rem; /* 12.8px 16px, slightly more padding */
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            box-sizing: border-box;
            font-size: 1rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            background-color: #fefefe; /* Lighter background */
            color: var(--text-color);
        }

        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: #999;
            opacity: 1; /* Ensure placeholder is always visible */
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="email"]:focus,
        .form-group input[type="tel"]:focus,
        .form-group input[type="url"]:focus,
        .form-group input[type="date"]:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: var(--primary-color);
            box-shadow: var(--input-focus-shadow);
            outline: none;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 90px; /* Slightly taller textarea */
        }

        /* Mensajes de error de validación */
        .form-group .text-danger {
            color: var(--danger-color);
            font-size: 0.875em; /* 14px */
            margin-top: 0.5rem; /* 8px, more space */
            display: block;
        }

        /* Campos de ancho completo en la cuadrícula */
        .form-group.full-width {
            grid-column: 1 / -1;
        }

        /* Grupo de botones al final del formulario */
        .button-group {
            grid-column: 1 / -1;
            display: flex;
            justify-content: flex-end;
            gap: var(--spacing-unit);
            margin-top: calc(var(--spacing-unit) * 2.5); /* More space before buttons */
            padding-top: calc(var(--spacing-unit) * 1.5);
            border-top: 1px solid var(--border-color);
        }

        .button-group button {
            padding: 0.8rem 1.6rem; /* Slightly more padding */
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Add a subtle shadow to buttons */
        }

        .button-group .submit-btn {
            background-color: var(--primary-color);
            color: white;
        }

        .button-group .submit-btn:hover {
            background-color: var(--primary-dark-color); /* Darker primary color on hover */
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(83, 12, 191, 0.3); /* Enhanced shadow on hover */
        }

        .button-group .cancel-btn {
            background-color: #6c757d;
            color: white;
        }

        .button-group .cancel-btn:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(108, 117, 125, 0.3);
        }

        /* Iconos en los botones */
        .button-group button i {
            font-size: 1.1rem;
        }

        /* Ajustes responsivos */
        @media (max-width: 768px) {
            .edit-ally-section {
                margin: var(--spacing-unit) auto;
                padding: calc(var(--spacing-unit) * 1.5); /* Reduce padding on mobile */
                max-width: 95%;
            }

            .edit-ally-section h2 {
                font-size: 1.8rem;
                margin-bottom: var(--spacing-unit);
            }

            .edit-ally-section h2 span {
                font-size: 1.1rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: var(--spacing-unit);
            }

            .form-group input,
            .form-group select,
            .form-group textarea {
                padding: 0.7rem 0.9rem;
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
                padding: 0.75rem 1.2rem;
                font-size: 0.95rem;
            }

            fieldset {
                padding: var(--spacing-unit);
                margin-bottom: calc(var(--spacing-unit) * 1.5);
            }

            fieldset legend {
                font-size: 1rem;
            }
        }
    </style>
@endsection

@section('content')
    <div class="edit-ally-section">
        <h2>
            Editar Información del Aliado
            <span id="allyIdDisplay">(ID: {{ $ally->id }})</span>
        </h2>

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
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

        <form id="editAllyForm" action="{{ route('aliados.update', $ally->id) }}" method="POST">
            @csrf {{-- Laravel CSRF token for security --}}
            @method('PUT') {{-- Method spoofing for PUT request --}}

            <fieldset>
                <legend>Datos Generales del Aliado</legend>
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

                    <div class="form-group">
                        <label for="category_id">Categoría:</label>
                        <select id="category_id" name="category_id" required aria-required="true">
                            <option value="">Seleccione una Categoría</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ old('category_id', $ally->category_id) == $category->id ? 'selected' : '' }}>
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
                            {{-- Options will be dynamically loaded by JavaScript --}}
                            @if ($ally->category_id && !empty($currentSubCategories))
                                @foreach ($currentSubCategories as $subCategory)
                                    <option value="{{ $subCategory->id }}"
                                        {{ old('sub_category_id', $ally->sub_category_id) == $subCategory->id ? 'selected' : '' }}>
                                        {{ $subCategory->name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @error('sub_category_id')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="business_type_id">Tipo de Negocio:</label>
                        <select id="business_type_id" name="business_type_id" required aria-required="true">
                            <option value="">Seleccione un Tipo de Negocio</option>
                            @foreach ($businessTypes as $businessType)
                                <option value="{{ $businessType->id }}"
                                    {{ old('business_type_id', $ally->business_type_id) == $businessType->id ? 'selected' : '' }}>
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
            </fieldset>

            <fieldset>
                <legend>Información de Contacto</legend>
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
                        <input type="email" id="contact_email" name="contact_email"
                            placeholder="Ej: contacto@empresa.com" value="{{ old('contact_email', $ally->contact_email) }}"
                            required aria-required="true">
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
                            placeholder="Ej: +58 212 9876543" value="{{ old('contact_phone_alt', $ally->contact_phone_alt) }}">
                        @error('contact_phone_alt')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend>Otros Detalles</legend>
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="company_address">Dirección Fiscal / Oficina:</label>
                        <textarea id="company_address" name="company_address"
                            placeholder="Ej: Av. Libertador, Edif. Caracas, Piso 10, Ofic. 10B, Caracas, Venezuela"
                            rows="3">{{ old('company_address', $ally->company_address) }}</textarea>
                        @error('company_address')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="website_url">Sitio Web (Opcional):</label>
                        <input type="url" id="website_url" name="website_url"
                            placeholder="Ej: https://www.empresadelaliado.com" value="{{ old('website_url', $ally->website_url) }}">
                        @error('website_url')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="discount">Oferta de Descuento / Beneficio (Opcional):</label>
                        <input type="text" id="discount" name="discount"
                            placeholder="Ej: 15% en alquiler de equipos, 2x1 en entradas" value="{{ old('discount', $ally->discount) }}">
                        @error('discount')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group full-width">
                        <label for="notes">Notas Internas (Opcional):</label>
                        <textarea id="notes" name="notes" placeholder="Cualquier información adicional relevante..."
                            rows="3">{{ old('notes', $ally->notes) }}</textarea>
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
            </fieldset>

            <div class="button-group">
                <button type="button" class="cancel-btn" id="cancelEditAlly">
                    <i class="fas fa-times-circle" aria-hidden="true"></i> Cancelar
                </button>
                <button type="submit" class="submit-btn">
                    <i class="fas fa-save" aria-hidden="true"></i> Actualizar Aliado
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts') {{-- Usa @push para añadir scripts a la pila definida en el layout --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Script para activar el enlace "Aliados" en el sidebar (assuming a standard sidebar structure)
            const sidebarLinks = document.querySelectorAll('.sidebar ul li a');
            sidebarLinks.forEach(link => {
                link.classList.remove('active');
                // Check if the current URL path contains '/admin/aliados'
                if (window.location.pathname.includes('/admin/aliados')) {
                    // Check if the link's href also contains '/admin/aliados'
                    if (link.getAttribute('href') && link.getAttribute('href').includes('/admin/aliados')) {
                        link.classList.add('active');
                    }
                }
            });

            // Lógica del botón de cancelar
            const cancelEditAllyButton = document.getElementById('cancelEditAlly');
            if (cancelEditAllyButton) {
                cancelEditAllyButton.addEventListener('click', () => {
                    if (confirm('¿Estás seguro de que quieres cancelar? Los cambios no guardados se perderán.')) {
                        window.location.href = "{{ route('aliados.index') }}";
                    }
                });
            }


            // --- Lógica para la carga dinámica de Subcategorías ---
            const categorySelect = document.getElementById('category_id');
            const subCategorySelect = document.getElementById('sub_category_id');

            // Obtener los valores iniciales del aliado o de la entrada antigua (en caso de error de validación)
            // Use dataset for cleaner access to initial values if passed from Blade
            const initialCategoryId = categorySelect.value;
            const initialSubCategoryId = "{{ old('sub_category_id', $ally->sub_category_id) }}";

            // Function to load subcategories based on category ID
            function loadSubCategories(categoryId, selectedSubId = null) {
                subCategorySelect.innerHTML = '<option value="">Cargando Subcategorías...</option>';
                subCategorySelect.disabled = true;
                subCategorySelect.classList.add('loading'); // Optional: Add a class for loading indicator via CSS

                if (!categoryId) {
                    subCategorySelect.innerHTML = '<option value="">Selecciona una Subcategoría (opcional)</option>';
                    subCategorySelect.disabled = false;
                    subCategorySelect.classList.remove('loading');
                    return;
                }

                fetch(`{{ route('get.subcategories') }}?category_id=${categoryId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok: ' + response.statusText);
                        }
                        return response.json();
                    })
                    .then(data => {
                        subCategorySelect.innerHTML = '<option value="">Selecciona una Subcategoría (opcional)</option>';
                        if (data.length > 0) {
                            data.forEach(subCategory => {
                                const option = document.createElement('option');
                                option.value = subCategory.id;
                                option.textContent = subCategory.name;
                                if (selectedSubId && subCategory.id == selectedSubId) {
                                    option.selected = true;
                                }
                                subCategorySelect.appendChild(option);
                            });
                        } else {
                            subCategorySelect.innerHTML = '<option value="">No hay Subcategorías para esta Categoría</option>';
                        }
                        subCategorySelect.disabled = false;
                        subCategorySelect.classList.remove('loading');
                    })
                    .catch(error => {
                        console.error('Error al cargar subcategorías:', error);
                        subCategorySelect.innerHTML = '<option value="">Error al cargar Subcategorías</option>';
                        subCategorySelect.disabled = false;
                        subCategorySelect.classList.remove('loading');
                        // Optionally, display a user-friendly error message on the page
                        alert('No se pudieron cargar las subcategorías. Por favor, intente de nuevo.');
                    });
            }

            // Initial load of subcategories when the page loads, if a category is already selected (e.g., on edit or validation error)
            if (initialCategoryId) {
                loadSubCategories(initialCategoryId, initialSubCategoryId);
            } else {
                // Ensure subcategory select is enabled if no initial category is set
                subCategorySelect.disabled = false;
            }

            // Listen for changes on the category select
            categorySelect.addEventListener('change', function() {
                // When category changes, reload subcategories and reset the selected subcategory
                loadSubCategories(this.value);
            });
        });
    </script>
@endpush