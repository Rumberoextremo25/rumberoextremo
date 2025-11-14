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

        {{-- Mostrar mensaje de éxito --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <strong>¡Éxito!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Estadísticas de usuarios --}}
        <div class="user-stats-container">
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-number">{{ $totalUsersCount ?? $users->total() }}</span>
                    <span class="stat-label">Total Usuarios</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon active">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-number">{{ $activeUsersCount ?? $users->where('status', 'activo')->count() }}</span>
                    <span class="stat-label">Activos</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon inactive">
                    <i class="fas fa-user-slash"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-number">{{ $inactiveUsersCount ?? $users->where('status', 'inactivo')->count() }}</span>
                    <span class="stat-label">Inactivos</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon pending">
                    <i class="fas fa-user-clock"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-number">{{ $pendingUsersCount ?? $users->where('status', 'pendiente')->count() }}</span>
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
                                <td data-label="Nombre">{{ $user->firstname }} {{ $user->lastname }}</td>
                                <td data-label="Email">{{ $user->email }}</td>
                                <td data-label="Tipo">
                                    <span class="badge badge-type-{{ strtolower($user->role) }}">
                                        @if($user->role === 'admin')
                                            Administrador
                                        @elseif($user->role === 'aliado')
                                            Aliado
                                        @elseif($user->role === 'afiliado')
                                            Afiliado
                                        @elseif($user->role === 'comun')
                                            Común
                                        @else
                                            {{ $user->role }}
                                        @endif
                                    </span>
                                </td>
                                <td data-label="Estado">
                                    <span class="badge badge-status-{{ strtolower($user->status) }}">
                                        {{ ucfirst($user->status) }}
                                    </span>
                                </td>
                                <td data-label="Teléfono">{{ $user->phone1 ?? 'N/A' }}</td>
                                <td data-label="Registro">
                                    @if($user->registration_date)
                                        {{ \Carbon\Carbon::parse($user->registration_date)->format('d/m/Y') }}
                                    @else
                                        N/A
                                    @endif
                                </td>
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

            {{-- PAGINACIÓN --}}
            <div class="pagination-container">
                {{-- Información de la paginación --}}
                <div class="pagination-info">
                    Mostrando {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} de {{ $users->total() }} usuarios
                </div>

                {{-- Links de paginación --}}
                @if ($users->hasPages())
                    <ul class="pagination">
                        {{-- Enlace anterior --}}
                        @if ($users->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link">
                                    <i class="fas fa-chevron-left"></i>
                                </span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $users->previousPageUrl() }}" rel="prev">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        @endif

                        {{-- Enlaces de páginas --}}
                        @foreach ($users->getUrlRange(1, $users->lastPage()) as $page => $url)
                            @if ($page == $users->currentPage())
                                <li class="page-item active">
                                    <span class="page-link">{{ $page }}</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                </li>
                            @endif
                        @endforeach

                        {{-- Enlace siguiente --}}
                        @if ($users->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $users->nextPageUrl() }}" rel="next">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <span class="page-link">
                                    <i class="fas fa-chevron-right"></i>
                                </span>
                            </li>
                        @endif
                    </ul>
                @endif
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

        // Auto-ocultar alertas después de 5 segundos
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 300);
            }, 5000);
        });
    });
</script>
@endpush