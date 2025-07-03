@extends('layouts.admin')

@section('title', 'Gestión de Usuarios')

@section('page_title', 'Lista de Usuarios')

@section('content')
    <div class="users-table-section">
        <h2>Gestión de Usuarios</h2>
        
        <div class="add-user-button-container">
            <a href="{{ route('add-user') }}" class="add-user-button">
                <i class="fas fa-plus-circle"></i> Añadir Nuevo Usuario
            </a>
        </div>

        @if ($users->isEmpty())
            <div class="no-users-message">
                <p>No hay usuarios registrados en este momento. ¡Añade uno para empezar!</p>
            </div>
        @else
            <div class="table-responsive"> {{-- Added for better mobile responsiveness --}}
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre Completo</th>
                            <th>Correo Electrónico</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Teléfono</th>
                            <th>Fecha de Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->firstname }} {{ $user->lastname }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <span class="type-badge type-{{ strtolower($user->user_type) }}">
                                        {{ $user->user_type }}
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-{{ strtolower($user->status) }}">
                                        {{ $user->status }}
                                    </span>
                                </td>
                                <td>{{ $user->phone1 ?? 'N/A' }}</td>
                                <td>{{ \Carbon\Carbon::parse($user->registrationDate)->format('d/m/Y') }}</td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('users.show', $user->id) }}" class="btn btn-view" title="Ver Detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-edit" title="Editar Usuario">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar a {{ $user->firstName }} {{ $user->lastName }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-delete" title="Eliminar Usuario">
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
@endsection

@push('scripts')
    {{-- Aquí irían scripts para funcionalidades de la tabla como búsqueda, paginación, etc. --}}
    {{-- Por ejemplo, si usas DataTables, lo inicializarías aquí. --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Puedes añadir aquí cualquier script interactivo para la tabla,
            // como la inicialización de DataTables si lo estuvieras usando.
            // Ejemplo básico si quisieras hacer algo al cargar la tabla:
            console.log('Vista de Gestión de Usuarios cargada.');
        });
    </script>
@endpush