@extends('layouts.admin') {{-- Extends the base admin layout --}}

@section('title', 'Editar Usuario') {{-- Sets the specific page title --}}

@section('page_title', 'Editar Usuario') {{-- Overrides the page title in the topbar --}}

@section('styles')
    {{-- Font Awesome para iconos (asegúrate de que esté en tu layout o aquí) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    {{-- Google Fonts - Inter para una tipografía moderna y legible --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* Estilos similares a los que ya tienes en tus otras vistas para consistencia */
        .edit-user-section {
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

        .edit-user-section h2 {
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
            margin-bottom: 5px; /* Ajustado, ya que el grid maneja la mayoría de los espaciados */
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
            box-sizing: border-box; /* Asegura que el padding no aumente el ancho total */
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
            background-color: #007bff;
            color: white;
        }

        .submit-btn:hover {
            background-color: #0056b3;
        }
    </style>
@endsection

@section('content')
    <div class="edit-user-section">
        <h2>Información del Usuario <span id="userIdDisplay">(ID: {{ $user->id }})</span></h2>
        {{-- El action del formulario apunta a la ruta de actualización y usa el método PUT --}}
        <form id="editUserForm" action="{{ route('users.update', $user->id) }}" method="POST">
            @csrf {{-- Protección CSRF obligatoria en Laravel --}}
            @method('PUT') {{-- Indica que esta es una solicitud PUT para actualizar --}}

            <input type="hidden" id="userId" name="userId" value="{{ $user->id }}">
            <div class="form-grid">
                <div class="form-group">
                    <label for="firstName">Nombre:</label>
                    <input type="text" id="firstName" name="firstName" placeholder="Ej: Juan" value="{{ old('firstName', $user->firstname) }}" required>
                    @error('firstName') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label for="lastName">Apellido:</label>
                    <input type="text" id="lastName" name="lastName" placeholder="Ej: Pérez" value="{{ old('lastName', $user->lastname) }}" required>
                    @error('lastName') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label for="email">Correo Electrónico:</label>
                    <input type="email" id="email" name="email" placeholder="ejemplo@dominio.com" value="{{ old('email', $user->email) }}" required>
                    @error('email') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label for="password">Nueva Contraseña (Dejar en blanco para no cambiar):</label>
                    <input type="password" id="password" name="password" placeholder="Mínimo 6 caracteres" minlength="6">
                    @error('password') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Confirmar Nueva Contraseña:</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Repite la nueva contraseña">
                </div>
                <div class="form-group">
                    <label for="userType">Tipo de Usuario:</label>
                    <select id="userType" name="userType" required>
                        <option value="">Seleccione un tipo</option>
                        <option value="comun" {{ old('userType', $user->userType) == 'comun' ? 'selected' : '' }}>Común</option>
                        <option value="aliado" {{ old('userType', $user->userType) == 'aliado' ? 'selected' : '' }}>Aliado</option>
                        <option value="afiliado" {{ old('userType', $user->userType) == 'afiliado' ? 'selected' : '' }}>Afiliado</option>
                        <option value="admin" {{ old('userType', $user->userType) == 'admin' ? 'selected' : '' }}>Administrador</option>
                    </select>
                    @error('userType') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label for="phone">Teléfono (Opcional):</label>
                    <input type="tel" id="phone" name="phone" placeholder="Ej: +58 412 1234567" value="{{ old('phone', $user->phone1) }}">
                    @error('phone') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label for="status">Estado:</label>
                    <select id="status" name="status" required>
                        <option value="activo" {{ old('status', $user->status) == 'activo' ? 'selected' : '' }}>Activo</option>
                        <option value="inactivo" {{ old('status', $user->status) == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                        <option value="pendiente" {{ old('status', $user->status) == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                        {{-- Puedes añadir 'banned' si lo usas en tu modelo User --}}
                        {{-- <option value="banned" {{ old('status', $user->status) == 'banned' ? 'selected' : '' }}>Baneado</option> --}}
                    </select>
                    @error('status') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label for="registrationDate">Fecha de Registro:</label>
                    <input type="date" id="registrationDate" name="registrationDate" value="{{ old('registrationDate', \Carbon\Carbon::parse($user->registrationDate)->format('Y-m-d')) }}" required>
                    @error('registrationDate') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label for="notes">Notas Internas (Opcional):</label>
                    <textarea id="notes" name="notes" placeholder="Información adicional sobre el usuario..." rows="3">{{ old('notes', $user->notes) }}</textarea>
                    @error('notes') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="button-group">
                <button type="button" class="cancel-btn" id="cancelEditUser"><i class="fas fa-times-circle"></i> Cancelar</button>
                <button type="submit" class="submit-btn"><i class="fas fa-save"></i> Actualizar Usuario</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // El userId se carga directamente de Blade en el HTML.
            // La carga de datos ya no es necesaria aquí, ya que Blade la maneja.

            // --- Funcionalidad del Formulario de Actualización ---
            const editUserForm = document.getElementById('editUserForm');
            editUserForm.addEventListener('submit', (event) => {
                const newPassword = document.getElementById('password').value; // Renombrado a 'password' para consistencia con Laravel
                const confirmNewPassword = document.getElementById('password_confirmation').value; // Renombrado

                if (newPassword && newPassword !== confirmNewPassword) {
                    event.preventDefault(); // Evita el envío si las contraseñas no coinciden
                    alert('Las nuevas contraseñas no coinciden. Por favor, inténtalo de nuevo.');
                }
                // Si las contraseñas coinciden (o si no se está cambiando la contraseña), el formulario se enviará normalmente.
            });

            // --- Botón de Cancelar ---
            document.getElementById('cancelEditUser').addEventListener('click', () => {
                if (confirm('¿Estás seguro de que quieres cancelar? Los cambios no guardados se perderán.')) {
                    // Redirigir a la vista de gestión de usuarios
                    window.location.href = '{{ route('users') }}'; // Usa la ruta de Blade
                }
            });
        });
    </script>
@endpush