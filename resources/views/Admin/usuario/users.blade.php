@extends('layouts.admin')

@section('page_title_toolbar', 'Gestion de Usuarios')

@section('page_title', 'Lista de Usuarios')

@section('styles')
    {{-- Font Awesome y Google Fonts (pueden ir en el layout principal si son globales) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
@endsection

@section('content')
    <div class="users-section-container">
        <h2 class="section-title">Todos los usuarios</h2>

        <div class="add-button-wrapper">
            <a href="{{ route('add-user') }}" class="add-user-btn">
                <i class="fas fa-plus-circle"></i> Añadir Nuevo Usuario
            </a>
        </div>

        @if ($users->isEmpty())
            <div class="empty-state-message">
                <p>No hay usuarios registrados en este momento. ¡Añade uno para empezar!</p>
            </div>
        @else
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre Completo</th>
                            <th>Email</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Teléfono</th>
                            <th>Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td data-label="ID" class="user-id">{{ $user->id }}</td>
                                <td data-label="Nombre">{{ $user->name }}</td>
                                <td data-label="Email">{{ $user->email }}</td>
                                <td data-label="Tipo">
                                    <span class="badge badge-type-{{ strtolower($user->user_type) }}">
                                        {{ $user->user_type }}
                                    </span>
                                </td>
                                <td data-label="Estado">
                                    <span class="badge badge-status-{{ strtolower($user->status) }}">
                                        {{ $user->status }}
                                    </span>
                                </td>
                                <td data-label="Teléfono">{{ $user->phone1 ?? 'N/A' }}</td>
                                <td data-label="Registro">{{ \Carbon\Carbon::parse($user->registrationDate)->format('d/m/Y') }}</td>
                                <td data-label="Acciones">
                                    <div class="action-group">
                                        <a href="{{ route('users.show', $user->id) }}" class="action-btn view" title="Ver Detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('users.edit', $user->id) }}" class="action-btn edit" title="Editar Usuario">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-btn delete delete-user-btn" title="Eliminar Usuario">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Modal de Confirmación para Eliminar Usuario --}}
    <div id="confirmationModal" class="modal-overlay">
        <div class="modal-content">
            <h3>Confirmar Eliminación</h3>
            <p>¿Estás seguro de que quieres eliminar este usuario? Esta acción no se puede deshacer.</p>
            <div class="modal-buttons">
                <button id="cancelDeleteBtn" class="btn-cancel">
                    Cancelar
                </button>
                <button id="confirmDeleteBtn" class="btn-confirm">
                    Eliminar
                </button>
            </div>
        </div>
    </div>
@endsection