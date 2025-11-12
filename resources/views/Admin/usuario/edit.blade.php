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
                <p class="current-role">Rol actual: <strong>{{ $user->role ?? 'comun' }}</strong></p>
                <p class="current-status">Estado actual: 
                    <strong class="status-{{ $user->status ?? 'activo' }}">
                        {{ ucfirst($user->status ?? 'activo') }}
                    </strong>
                </p>
            </div>

            <hr class="section-divider">

            {{-- El action del formulario apunta a la ruta de actualización y usa el método PUT --}}
            <form id="editUserForm" action="{{ route('users.update', $user->id) }}" method="POST">
                @csrf {{-- Protección CSRF obligatoria en Laravel --}}
                @method('PUT') {{-- Indica que esta es una solicitud PUT para actualizar --}}

                <div class="form-grid">
                    <div class="form-group">
                        <label for="firstname">Nombre:</label>
                        <input type="text" id="firstname" name="firstname" placeholder="Ej: Juan" value="{{ old('firstname', $user->firstname) }}" required>
                        @error('firstname') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="lastname">Apellido:</label>
                        <input type="text" id="lastname" name="lastname" placeholder="Ej: Pérez" value="{{ old('lastname', $user->lastname) }}" required>
                        @error('lastname') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label for="email">Correo Electrónico:</label>
                        <input type="email" id="email" name="email" placeholder="ejemplo@dominio.com" value="{{ old('email', $user->email) }}" required>
                        @error('email') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">Nueva Contraseña (Opcional):</label>
                        <input type="password" id="password" name="password" placeholder="Mínimo 8 caracteres" minlength="8">
                        @error('password') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Confirmar Contraseña:</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Repite la nueva contraseña">
                    </div>

                    {{-- Campo user_type modificado para incluir todos los roles --}}
                    <div class="form-group">
                        <label for="user_type">Tipo de Usuario:</label>
                        <select id="user_type" name="user_type" required>
                            <option value="">Seleccione un tipo</option>
                            @if(Auth::user()->role === 'admin')
                                <option value="admin" {{ old('user_type', $user->role) == 'admin' ? 'selected' : '' }}>Administrador</option>
                            @endif
                            <option value="aliado" {{ old('user_type', $user->role) == 'aliado' ? 'selected' : '' }}>Aliado</option>
                            <option value="afiliado" {{ old('user_type', $user->role) == 'afiliado' ? 'selected' : '' }}>Afiliado</option>
                            <option value="comun" {{ old('user_type', $user->role) == 'comun' || old('user_type', $user->role) == null ? 'selected' : '' }}>Usuario Común</option>
                        </select>
                        @error('user_type') <div class="text-danger">{{ $message }}</div> @enderror
                        @if(Auth::user()->role === 'admin')
                            <small class="text-muted">Puedes cambiar el rol del usuario entre Administrador, Aliado, Afiliado o Usuario Común</small>
                        @endif
                    </div>

                    <div class="form-group">
                        <label for="phone1">Teléfono (Opcional):</label>
                        <input type="tel" id="phone1" name="phone1" placeholder="Ej: +58 412 1234567" value="{{ old('phone1', $user->phone1) }}">
                        @error('phone1') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    {{-- Campo Estado con información sobre la funcionalidad --}}
                    <div class="form-group">
                        <label for="status">Estado:</label>
                        <select id="status" name="status" required>
                            <option value="activo" {{ old('status', $user->status) == 'activo' ? 'selected' : '' }}>Activo</option>
                            <option value="inactivo" {{ old('status', $user->status) == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                            <option value="pendiente" {{ old('status', $user->status) == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                        </select>
                        @error('status') <div class="text-danger">{{ $message }}</div> @enderror
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> 
                            Los usuarios inactivos no podrán ingresar al portal sin importar su rol
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="registrationDate">Fecha de Registro:</label>
                        <input type="date" id="registrationDate" name="registrationDate" value="{{ old('registrationDate', \Carbon\Carbon::parse($user->registration_date)->format('Y-m-d')) }}" required>
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

    <style>
        .current-role, .current-status {
            margin-top: 5px;
            color: #666;
            font-size: 14px;
        }
        
        .text-muted {
            color: #6c757d !important;
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }
        
        .status-activo {
            color: #28a745;
            font-weight: bold;
        }
        
        .status-inactivo {
            color: #dc3545;
            font-weight: bold;
        }
        
        .status-pendiente {
            color: #ffc107;
            font-weight: bold;
        }
        
        .fa-info-circle {
            color: #17a2b8;
        }
    </style>
@endsection