@extends('layouts.admin')

@section('page_title_toolbar', 'Gestion de Usuarios')

@section('styles')
    {{-- Font Awesome y Google Fonts (pueden ir en el layout principal si son globales) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/users.css') }}">
@endsection

@section('content')
    <div class="users-section-container">
        <h2 class="section-title">Todos los usuarios</h2>

        {{-- Estadísticas de usuarios --}}
        <div class="user-stats-container">
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-number">{{ $users->count() }}</span>
                    <span class="stat-label">Total Usuarios</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon active">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-number">{{ $users->where('status', 'activo')->count() }}</span>
                    <span class="stat-label">Activos</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon inactive">
                    <i class="fas fa-user-slash"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-number">{{ $users->where('status', 'inactivo')->count() }}</span>
                    <span class="stat-label">Inactivos</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon pending">
                    <i class="fas fa-user-clock"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-number">{{ $users->where('status', 'pendiente')->count() }}</span>
                    <span class="stat-label">Pendientes</span>
                </div>
            </div>
        </div>

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
                            <th>Nombre completo</th>
                            <th>Email</th>
                            <th>Tipo de usuario</th>
                            <th>Estado</th>
                            <th>Teléfono</th>
                            <th>Fecha de registro</th>
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
                                    <span class="badge badge-type-{{ strtolower($user->role) }}">
                                        @if($user->role === 'admin')
                                            Administrador
                                        @elseif($user->role === 'aliado')
                                            Aliado
                                        @elseif($user->role === 'afiliado')
                                            Afiliado
                                        @else
                                            Usuario
                                        @endif
                                    </span>
                                </td>
                                <td data-label="Estado">
                                    <span class="badge badge-status-{{ strtolower($user->status) }}">
                                        {{ ucfirst($user->status) }}
                                    </span>
                                </td>
                                <td data-label="Teléfono">{{ $user->phone1 ?? 'N/A' }}</td>
                                <td data-label="Registro">{{ \Carbon\Carbon::parse($user->registration_date)->format('d/m/Y') }}</td>
                                <td data-label="Acciones">
                                    <div class="action-group compact">
                                        <a href="{{ route('users.show', $user->id) }}" class="action-btn view" title="Ver Detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('users.edit', $user->id) }}" class="action-btn edit" title="Editar Usuario">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="delete-form">
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteButtons = document.querySelectorAll('.delete-user-btn');
        const modal = document.getElementById('confirmationModal');
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        const cancelBtn = document.getElementById('cancelDeleteBtn');
        let formToSubmit = null;

        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                // Prevent the form from submitting immediately
                e.preventDefault();

                // Get the parent form element
                formToSubmit = this.closest('form');

                // Display the modal
                modal.classList.add('active');
            });
        });

        confirmBtn.addEventListener('click', function() {
            // If a form is stored, submit it and hide the modal
            if (formToSubmit) {
                formToSubmit.submit();
            }
        });

        cancelBtn.addEventListener('click', function() {
            // Hide the modal and reset the form reference
            modal.classList.remove('active');
            formToSubmit = null;
        });

        // Optional: Hide modal if user clicks outside of it
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.remove('active');
                formToSubmit = null;
            }
        });
    });
</script>
@endpush