@extends('layouts.admin')

@section('title', 'Detalles del Usuario')

@section('page_title_toolbar', 'Gestión de Usuarios')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/user-details.css') }}">
@endpush

@section('content')
    <div class="user-details-wrapper">
        {{-- Header con bienvenida --}}
        <div class="details-header-bar">
            <div class="header-content">
                <h1 class="page-title">
                    <span class="title-main">Detalles del</span>
                    <span class="title-accent">Usuario</span>
                </h1>
                <p class="page-subtitle">
                    <i class="fas fa-user-circle"></i>
                    Información completa del usuario #{{ $user->id }}
                </p>
            </div>
            <div class="header-actions">
                <div class="user-greeting">
                    <span>Visualizando,</span>
                    <strong>{{ $user->firstname }} {{ $user->lastname }}</strong>
                </div>
                <div class="avatar-circle">
                    <span>{{ substr($user->firstname ?? $user->name, 0, 1) }}</span>
                </div>
            </div>
        </div>

        {{-- Tarjeta principal del perfil --}}
        <div class="profile-main-card">
            {{-- Cabecera del perfil con portada --}}
            <div class="profile-cover">
                <div class="profile-avatar-wrapper">
                    <div class="profile-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                </div>
                <div class="profile-badge-container">
                    <span class="badge badge-role-{{ strtolower($user->role ?? 'user') }}">
                        @if($user->role === 'admin')
                            Administrador
                        @elseif($user->role === 'aliado')
                            Aliado Comercial
                        @elseif($user->role === 'afiliado')
                            Afiliado
                        @else
                            Usuario
                        @endif
                    </span>
                    <span class="badge badge-status-{{ strtolower($user->status ?? 'activo') }}">
                        <i class="fas fa-circle"></i>
                        {{ ucfirst($user->status ?? 'Activo') }}
                    </span>
                </div>
            </div>

            {{-- Información básica del usuario --}}
            <div class="profile-info-header">
                <h2>{{ $user->firstname }} {{ $user->lastname }}</h2>
                <p class="user-email">{{ $user->email }}</p>
                <div class="user-meta">
                    <span><i class="fas fa-calendar-alt"></i> Registrado: {{ \Carbon\Carbon::parse($user->registration_date ?? $user->created_at)->format('d/m/Y') }}</span>
                    <span><i class="fas fa-clock"></i> Última actividad: {{ $user->updated_at ? \Carbon\Carbon::parse($user->updated_at)->diffForHumans() : 'N/A' }}</span>
                </div>
            </div>

            {{-- Grid de información detallada --}}
            <div class="details-grid-container">
                {{-- Columna izquierda: Información personal --}}
                <div class="details-card">
                    <div class="card-header">
                        <div class="header-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <h3>Información Personal</h3>
                    </div>

                    <div class="info-list">
                        <div class="info-item">
                            <span class="info-label">ID Usuario</span>
                            <span class="info-value">#{{ $user->id }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Nombre completo</span>
                            <span class="info-value">{{ $user->firstname }} {{ $user->lastname }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Correo electrónico</span>
                            <span class="info-value">{{ $user->email }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Teléfono</span>
                            <span class="info-value">{{ $user->phone1 ?? 'No especificado' }}</span>
                        </div>
                        @if($user->phone2)
                        <div class="info-item">
                            <span class="info-label">Teléfono adicional</span>
                            <span class="info-value">{{ $user->phone2 }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Columna derecha: Información de cuenta y fechas --}}
                <div class="details-card">
                    <div class="card-header">
                        <div class="header-icon">
                            <i class="fas fa-cog"></i>
                        </div>
                        <h3>Detalles de Cuenta</h3>
                    </div>

                    <div class="info-list">
                        <div class="info-item">
                            <span class="info-label">Fecha de registro</span>
                            <span class="info-value">{{ \Carbon\Carbon::parse($user->registration_date ?? $user->created_at)->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Última actualización</span>
                            <span class="info-value">{{ $user->updated_at ? \Carbon\Carbon::parse($user->updated_at)->format('d/m/Y H:i') : 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Tipo de usuario</span>
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
                        <div class="info-item">
                            <span class="info-label">Estado de la cuenta</span>
                            <span class="info-value">
                                <span class="badge badge-status-{{ strtolower($user->status ?? 'activo') }}">
                                    <i class="fas fa-circle"></i>
                                    {{ ucfirst($user->status ?? 'Activo') }}
                                </span>
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Fila completa para notas --}}
                <div class="details-card full-width">
                    <div class="card-header">
                        <div class="header-icon">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <h3>Notas Internas</h3>
                    </div>

                    <div class="notes-content">
                        @if($user->notes)
                            <p>{{ $user->notes }}</p>
                        @else
                            <p class="text-muted">No hay notas internas para este usuario.</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Botones de acción --}}
            <div class="action-buttons-container">
                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn-edit">
                    <i class="fas fa-edit"></i>
                    Editar Usuario
                </a>
                
                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="delete-form" 
                      onsubmit="return confirm('¿Estás seguro de que quieres eliminar a {{ $user->firstname }} {{ $user->lastname }}? Esta acción es irreversible.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-delete">
                        <i class="fas fa-trash-alt"></i>
                        Eliminar Usuario
                    </button>
                </form>

                <a href="{{ route('admin.users.index') }}" class="btn-back">
                    <i class="fas fa-arrow-left"></i>
                    Volver a Usuarios
                </a>
            </div>
        </div>
    </div>
@endsection