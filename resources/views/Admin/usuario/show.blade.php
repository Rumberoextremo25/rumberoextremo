@extends('layouts.admin')

@section('title', 'Detalles del Usuario')

@section('page_title', 'Detalles del Usuario')

@push('styles')
    {{-- Dependencias de CSS para la nueva vista --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/user-details.css') }}">
@endpush

@section('content')
    <div class="user-profile-container">
        {{-- Encabezado del Perfil --}}
        <div class="profile-header">
            <div class="profile-avatar">
                <div class="avatar-icon-wrapper">
                    <i class="fas fa-user-circle"></i>
                </div>
            </div>
            <div class="profile-info">
                <h1>{{ $user->firstname }} {{ $user->lastname }}</h1>
                <p class="user-email">{{ $user->email }}</p>
                <span class="badge badge-role-{{ strtolower($user->role ?? 'user') }} user-role-badge">
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
            </div>
        </div>
        
        <hr class="section-divider">

        {{-- Sección de Información del Usuario --}}
        <div class="info-section">
            <h2><i class="fas fa-info-circle"></i> Información General</h2>
            <div class="info-grid">
                <div class="info-group">
                    <span class="info-label">ID del Usuario:</span>
                    <span class="info-value">{{ $user->id }}</span>
                </div>
                <div class="info-group">
                    <span class="info-label">Tipo de Usuario:</span>
                    <span class="info-value">
                        <span class="badge badge-role-{{ strtolower($user->role ?? 'user') }}">
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
                    </span>
                </div>
                <div class="info-group">
                    <span class="info-label">Estado:</span>
                    <span class="info-value">
                        <span class="badge badge-status-{{ strtolower($user->status ?? 'activo') }}">
                            {{ ucfirst($user->status ?? 'activo') }}
                        </span>
                    </span>
                </div>
                <div class="info-group">
                    <span class="info-label">Teléfono:</span>
                    <span class="info-value">{{ $user->phone1 ?? 'N/A' }}</span>
                </div>
                <div class="info-group">
                    <span class="info-label">Fecha de Registro:</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($user->registration_date ?? $user->registrationDate)->format('d/m/Y H:i') }}</span>
                </div>
                <div class="info-group">
                    <span class="info-label">Última Actualización:</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($user->updated_at)->format('d/m/Y H:i') ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <hr class="section-divider">

        {{-- Sección de Notas --}}
        <div class="info-section full-width-section">
            <h2><i class="fas fa-clipboard-list"></i> Notas Internas</h2>
            <div class="info-group">
                <span class="info-value notes-text">{{ $user->notes ?? 'No hay notas.' }}</span>
            </div>
        </div>

        <hr class="section-divider">

        {{-- Botones de Acción --}}
        <div class="button-group">
            <a href="{{ route('users.edit', $user->id) }}" class="btn-primary">
                <i class="fas fa-edit"></i> Editar Usuario
            </a>
            <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar a {{ $user->firstname }} {{ $user->lastname }}? Esta acción es irreversible.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger">
                    <i class="fas fa-trash-alt"></i> Eliminar Usuario
                </button>
            </form>
            <a href="{{ route('users') }}" class="btn-secondary">
                <i class="fas fa-arrow-circle-left"></i> Volver a Usuarios
            </a>
        </div>
    </div>
@endsection