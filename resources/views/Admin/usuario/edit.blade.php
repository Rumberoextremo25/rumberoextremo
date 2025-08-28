@extends('layouts.admin')

@section('title', 'Editar Usuario')

@section('page_title', 'Editar Usuario')

@push('styles')
    {{-- Dependencias de CSS para la nueva vista, adaptadas al diseño de "añadir usuario" --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
@endpush

@section('content')
    <div class="edit-user-container">
        <div class="form-card">
            <div class="form-header">
                <div class="form-icon-wrapper">
                    <i class="fas fa-user-edit"></i>
                </div>
                <h2>Editar Usuario: <span class="user-id-text">ID #{{ $user->id }}</span></h2>
            </div>

            <hr class="section-divider">

            {{-- El action del formulario apunta a la ruta de actualización y usa el método PUT --}}
            <form id="editUserForm" action="{{ route('users.update', $user->id) }}" method="POST">
                @csrf {{-- Protección CSRF obligatoria en Laravel --}}
                @method('PUT') {{-- Indica que esta es una solicitud PUT para actualizar --}}

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
                        <label for="password">Nueva Contraseña (Opcional):</label>
                        <input type="password" id="password" name="password" placeholder="Mínimo 6 caracteres" minlength="6">
                        @error('password') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Confirmar Contraseña:</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Repite la nueva contraseña">
                    </div>

                    <div class="form-group">
                        <label for="userType">Tipo de Usuario:</label>
                        <select id="userType" name="userType" required>
                            <option value="">Seleccione un tipo</option>
                            <option value="aliado" {{ old('userType', $user->user_type) == 'aliado' ? 'selected' : '' }}>Aliado</option>
                            <option value="usuario rumbero" {{ old('userType', $user->user_type) == 'usuario rumbero' ? 'selected' : '' }}>Usuario Rumbero</option>
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
                        </select>
                        @error('status') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label for="registrationDate">Fecha de Registro:</label>
                        <input type="date" id="registrationDate" name="registrationDate" value="{{ old('registrationDate', \Carbon\Carbon::parse($user->registrationDate)->format('Y-m-d')) }}" required>
                        @error('registrationDate') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="notes">Notas Internas (Opcional):</label>
                        <textarea id="notes" name="notes" placeholder="Información adicional sobre el usuario..." rows="3">{{ old('notes', $user->notes) }}</textarea>
                        @error('notes') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="button-group">
                    {{-- Usamos una etiqueta <a> para el botón de cancelar, para que sea navegable --}}
                    <a href="{{ route('users.show', $user->id) }}" class="cancel-btn">
                        <i class="fas fa-times-circle"></i> Cancelar
                    </a>
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-save"></i> Actualizar Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection