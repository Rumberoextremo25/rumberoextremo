@extends('layouts.admin')

@section('title', 'Añadir Nuevo Usuario')

@section('page_title', 'Añadir Nuevo Usuario')

@push('styles') {{-- Agregamos el CSS específico de esta vista --}}
    {{-- Considera si Font Awesome y Google Fonts ya están en layouts.admin para evitar duplicados --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    {{-- Enlazamos al nuevo archivo CSS para añadir usuarios --}}
    <link rel="stylesheet" href="{{ asset('css/admin/users/create.css') }}">
@endpush

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
    {{-- Para pasar la URL de redirección a JavaScript de forma segura --}}
    <script>
        window.usersListUrl = "{{ route('users') }}";
    </script>
    {{-- Carga tu script externo aquí --}}
    <script src="{{ asset('js/admin/users/create.js') }}"></script>
@endpush