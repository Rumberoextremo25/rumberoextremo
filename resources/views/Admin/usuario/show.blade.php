@extends('layouts.admin')

@section('title', 'Detalles del Usuario')

@section('page_title', 'Detalles del Usuario')

@section('styles')
    {{-- Font Awesome para iconos --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    {{-- Google Fonts - Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        .user-details-section {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            margin-top: 30px;
            font-family: 'Inter', sans-serif;
            max-width: 800px; /* Limita el ancho para mejor legibilidad */
            margin-left: auto;
            margin-right: auto;
        }

        .user-details-section h2 {
            font-size: 28px;
            color: #333;
            margin-bottom: 25px;
            text-align: center;
            font-weight: 700;
        }

        .user-info-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        @media (min-width: 768px) {
            .user-info-grid {
                grid-template-columns: 1fr 1fr; /* Dos columnas en pantallas más grandes */
            }
        }

        .info-group {
            background-color: #f8f9fa;
            padding: 15px 20px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
        }

        .info-group label {
            display: block;
            font-weight: 600;
            color: #555;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .info-group p {
            margin: 0;
            font-size: 16px;
            color: #333;
            word-wrap: break-word; /* Para manejar textos largos */
        }

        .info-group.full-width {
            grid-column: 1 / -1; /* Ocupa todo el ancho en el grid */
        }

        /* Estilos para los diferentes estados */
        .status-badge {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 13px;
            font-weight: 500;
            text-transform: capitalize;
            color: white;
        }

        .status-activo { background-color: #28a745; } /* Green */
        .status-inactivo { background-color: #dc3545; } /* Red */
        .status-pendiente { background-color: #ffc107; color: #333; } /* Yellow */
        .status-banned { background-color: #6c757d; } /* Gray */

        /* Estilos para los tipos de usuario */
        .type-badge {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 13px;
            font-weight: 500;
            text-transform: capitalize;
            background-color: #007bff;
            color: white;
        }

        .type-admin { background-color: #6f42c1; } /* Purple */
        .type-comun { background-color: #17a2b8; } /* Cyan */
        .type-aliado { background-color: #fd7e14; } /* Orange */
        .type-afiliado { background-color: #20c997; } /* Teal */

        .button-group {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }

        .button-group .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.2s ease, transform 0.1s ease;
        }

        .button-group .btn:hover {
            transform: translateY(-1px);
        }

        .btn-back {
            background-color: #6c757d;
            color: white;
        }

        .btn-back:hover {
            background-color: #5a6268;
        }

        .btn-edit {
            background-color: #007bff;
            color: white;
        }

        .btn-edit:hover {
            background-color: #0056b3;
        }

        .btn-delete {
            background-color: #dc3545;
            color: white;
        }

        .btn-delete:hover {
            background-color: #c82333;
        }
    </style>
@endsection

@section('content')
    <div class="user-details-section">
        <h2>Detalles del Usuario: {{ $user->firstname }} {{ $user->lastname }}</h2>

        <div class="user-info-grid">
            <div class="info-group">
                <label>ID:</label>
                <p>{{ $user->id }}</p>
            </div>
            <div class="info-group">
                <label>Nombre Completo:</label>
                <p>{{ $user->firstname }} {{ $user->lastname }}</p>
            </div>
            <div class="info-group">
                <label>Correo Electrónico:</label>
                <p>{{ $user->email }}</p>
            </div>
            <div class="info-group">
                <label>Tipo de Usuario:</label>
                <p>
                    <span class="type-badge type-{{ strtolower($user->user_type) }}">
                        {{ $user->user_type }}
                    </span>
                </p>
            </div>
            <div class="info-group">
                <label>Estado:</label>
                <p>
                    <span class="status-badge status-{{ strtolower($user->status) }}">
                        {{ $user->status }}
                    </span>
                </p>
            </div>
            <div class="info-group">
                <label>Teléfono:</label>
                <p>{{ $user->phone1 ?? 'N/A' }}</p>
            </div>
            <div class="info-group">
                <label>Fecha de Registro:</label>
                <p>{{ \Carbon\Carbon::parse($user->registrationDate)->format('d/m/Y H:i') }}</p>
            </div>
            <div class="info-group">
                <label>Última Actualización:</label>
                <p>{{ \Carbon\Carbon::parse($user->updated_at)->format('d/m/Y H:i') ?? 'N/A' }}</p>
            </div>
            <div class="info-group full-width">
                <label>Notas Internas:</label>
                <p>{{ $user->notes ?? 'No hay notas.' }}</p>
            </div>
        </div>

        <div class="button-group">
            <a href="{{ route('users') }}" class="btn btn-back">
                <i class="fas fa-arrow-circle-left"></i> Volver a Usuarios
            </a>
            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-edit">
                <i class="fas fa-edit"></i> Editar Usuario
            </a>
            <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar a {{ $user->firstName }} {{ $user->lastName }}? Esta acción es irreversible.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-delete">
                    <i class="fas fa-trash-alt"></i> Eliminar Usuario
                </button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Aquí puedes añadir scripts si fuera necesario para esta vista en particular --}}
@endpush