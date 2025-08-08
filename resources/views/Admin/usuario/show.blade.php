@extends('layouts.admin')

@section('title', 'Detalles del Usuario')

@section('page_title', 'Detalles del Usuario')

@push('styles') {{-- Agregamos el CSS específico de esta vista --}}
    {{-- Asegúrate de que Font Awesome esté en tu layout admin global para evitar duplicados --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    {{-- Enlazamos al nuevo archivo CSS para los detalles del usuario --}}
    <link rel="stylesheet" href="{{ asset('css/admin/users.css') }}">
@endpush

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