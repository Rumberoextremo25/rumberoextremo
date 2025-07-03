@extends('layouts.admin')

@section('title', 'Añadir Nuevo Usuario')

@section('page_title', 'Añadir Nuevo Usuario')

@section('styles')
    {{-- Font Awesome para iconos --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    {{-- Google Fonts - Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* Estilos generales para el formulario (similar a la vista de edición) */
        .add-user-section {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            margin-top: 30px;
            font-family: 'Inter', sans-serif;
            max-width: 900px; /* Limita el ancho del formulario */
            margin-left: auto;
            margin-right: auto;
        }

        .add-user-section h2 {
            font-size: 28px;
            color: #333;
            margin-bottom: 25px;
            text-align: center;
            font-weight: 700;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }

        @media (min-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr 1fr; /* Dos columnas en pantallas más grandes */
            }
        }

        .form-group {
            margin-bottom: 5px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #444;
            font-size: 15px;
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"],
        .form-group input[type="tel"],
        .form-group input[type="date"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            color: #333;
            box-sizing: border-box;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-group input[type="text"]::placeholder,
        .form-group input[type="email"]::placeholder,
        .form-group input[type="password"]::placeholder,
        .form-group input[type="tel"]::placeholder,
        .form-group textarea::placeholder {
            color: #999;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
            outline: none;
        }

        /* Estilo para los mensajes de error de validación */
        .text-danger {
            color: #dc3545;
            font-size: 13px;
            margin-top: 5px;
        }

        .button-group {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
        }

        .button-group button {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 17px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.2s ease, transform 0.1s ease;
        }

        .button-group button:hover {
            transform: translateY(-1px);
        }

        .cancel-btn {
            background-color: #6c757d;
            color: white;
        }

        .cancel-btn:hover {
            background-color: #5a6268;
        }

        .submit-btn {
            background-color: #28a745;
            color: white;
        }

        .submit-btn:hover {
            background-color: #218838;
        }
    </style>
@endsection

@section('content')
    <div class="add-user-section">
        <h2>Información del Nuevo Usuario</h2>
        {{-- El action del formulario apunta a la ruta para almacenar nuevos usuarios --}}
        <form id="addUserForm" action="{{ route('users.store') }}" method="POST">
            @csrf {{-- Protección CSRF obligatoria en Laravel --}}
            <div class="form-grid">
                <div class="form-group">
                    <label for="firstName">Nombre:</label>
                    {{-- old() para repoblar el campo si hay error de validación --}}
                    <input type="text" id="firstName" name="firstName" placeholder="Ej: Juan" value="{{ old('firstname') }}" required>
                    @error('firstName') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label for="lastName">Apellido:</label>
                    <input type="text" id="lastName" name="lastName" placeholder="Ej: Pérez" value="{{ old('lastname') }}" required>
                    @error('lastName') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label for="email">Correo Electrónico:</label>
                    <input type="email" id="email" name="email" placeholder="ejemplo@dominio.com" value="{{ old('email') }}" required>
                    @error('email') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" placeholder="Mínimo 6 caracteres" required minlength="6">
                    @error('password') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Confirmar Contraseña:</label>
                    {{-- Laravel espera 'password_confirmation' para la regla 'confirmed' --}}
                    <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Repite la contraseña" required>
                </div>
                <div class="form-group">
                    <label for="user_type">Tipo de Usuario:</label>
                    <select id="user_type" name="user_type" required>
                        <option value="">Seleccione un tipo</option>
                        <option value="comun" {{ old('user_type') == 'comun' ? 'selected' : '' }}>Común</option>
                        <option value="aliado" {{ old('user_type') == 'aliado' ? 'selected' : '' }}>Aliado</option>
                        <option value="afiliado" {{ old('user_type') == 'afiliado' ? 'selected' : '' }}>Afiliado</option>
                        <option value="admin" {{ old('user_type') == 'admin' ? 'selected' : '' }}>Administrador</option>
                    </select>
                    @error('user_type') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label for="phone">Teléfono (Opcional):</label>
                    <input type="tel" id="phone" name="phone" placeholder="Ej: +58 412 1234567" value="{{ old('phone1') }}">
                    @error('phone') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label for="status">Estado:</label>
                    <select id="status" name="status" required>
                        <option value="activo" {{ old('status') == 'activo' ? 'selected' : '' }}>Activo</option>
                        <option value="inactivo" {{ old('status') == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                        <option value="pendiente" {{ old('status') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                    </select>
                    @error('status') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label for="registrationDate">Fecha de Registro:</label>
                    {{-- Si no hay old data, usa la fecha actual --}}
                    <input type="date" id="registrationDate" name="registrationDate" value="{{ old('registrationDate', \Carbon\Carbon::now()->format('Y-m-d')) }}" required>
                    @error('registrationDate') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label for="notes">Notas Internas (Opcional):</label>
                    <textarea id="notes" name="notes" placeholder="Información adicional sobre el usuario..." rows="3">{{ old('notes') }}</textarea>
                    @error('notes') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="button-group">
                <button type="button" class="cancel-btn" id="cancelAddUser"><i class="fas fa-times-circle"></i> Cancelar</button>
                <button type="submit" class="submit-btn"><i class="fas fa-user-plus"></i> Añadir Usuario</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // --- Funcionalidad del Formulario de Añadir Usuario ---
            const addUserForm = document.getElementById('addUserForm');
            addUserForm.addEventListener('submit', (event) => {
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('password_confirmation').value; // Usar el nombre de campo de Laravel

                if (password !== confirmPassword) {
                    event.preventDefault(); // Evita el envío si las contraseñas no coinciden
                    alert('Las contraseñas no coinciden. Por favor, inténtalo de nuevo.');
                }
                // Si las contraseñas coinciden, el formulario se enviará al controlador de Laravel.
            });

            // --- Botón de Cancelar ---
            document.getElementById('cancelAddUser').addEventListener('click', () => {
                if (confirm('¿Estás seguro de que quieres cancelar? Los cambios no guardados se perderán.')) {
                    // Redirigir a la vista de gestión de usuarios usando la ruta de Blade
                    window.location.href = '{{ route('users') }}';
                }
            });

            // Establecer la fecha de registro por defecto a la fecha actual si el campo está vacío (solo JS, Blade ya lo hace con old())
            // Esto es más un fallback o para casos donde no se usa old()
            const registrationDateInput = document.getElementById('registrationDate');
            if (registrationDateInput && !registrationDateInput.value) {
                const today = new Date();
                const year = today.getFullYear();
                const month = String(today.getMonth() + 1).padStart(2, '0'); // Meses son 0-index
                const day = String(today.getDate()).padStart(2, '0');
                registrationDateInput.value = `${year}-${month}-${day}`;
            }
        });
    </script>
@endpush