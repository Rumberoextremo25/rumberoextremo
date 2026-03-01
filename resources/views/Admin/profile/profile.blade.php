{{-- resources/views/profile.blade.php --}}
@extends('layouts.admin')

@section('title', 'Mi Perfil')

@section('page_title_toolbar', 'Gestión de Perfil')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/profile.css') }}">
@endpush

@section('content')
    <div class="profile-wrapper">
        {{-- Header con bienvenida --}}
        <div class="profile-header-bar">
            <div class="header-content">
                <h1 class="page-title">
                    <span class="title-main">Mi</span>
                    <span class="title-accent">Perfil</span>
                </h1>
                <p class="page-subtitle">
                    <i class="fas fa-user-circle"></i>
                    Información personal y de contacto
                </p>
            </div>
            <div class="header-actions">
                <div class="user-greeting">
                    <span>Bienvenido,</span>
                    <strong>{{ Auth::user()->name }}</strong>
                </div>
                <div class="avatar-circle">
                    <span>{{ substr(Auth::user()->name, 0, 1) }}</span>
                </div>
            </div>
        </div>

        <div class="profile-grid">
            {{-- Columna izquierda: Tarjeta de perfil --}}
            <div class="profile-card profile-card-main">
                <div class="profile-cover">
                    <div class="profile-avatar-wrapper">
                        <div class="profile-avatar">
                            @if (Auth::user()->profile_photo_path)
                                <img src="{{ Storage::url(Auth::user()->profile_photo_path) }}" alt="Foto de Perfil">
                            @else
                                <img src="{{ asset('assets/img/dashboard/logo_perfil.png') }}" alt="Avatar de Usuario">
                            @endif
                        </div>
                    </div>
                    <div class="profile-badge">
                        @if(Auth::user()->role === 'admin')
                            <span class="badge badge-admin">Administrador</span>
                        @elseif(Auth::user()->role === 'aliado')
                            <span class="badge badge-aliado">Aliado Comercial</span>
                        @else
                            <span class="badge badge-rumbero">Rumbero</span>
                        @endif
                    </div>
                </div>

                <div class="profile-info-main">
                    <h2>{{ Auth::user()->name ?? 'Juan Pérez' }}</h2>
                    
                    @if(Auth::user()->role === 'aliado')
                        <p class="profile-company">
                            <i class="fas fa-building"></i>
                            {{ Auth::user()->company ?? 'Empresa no especificada' }}
                        </p>
                    @elseif(Auth::user()->role === 'admin')
                        <p class="profile-role">Administrador de Plataforma</p>
                    @else
                        <p class="profile-role">Miembro de la comunidad Rumbero</p>
                    @endif

                    <div class="profile-stats">
                        <div class="stat-item">
                            <span class="stat-value">{{ \Carbon\Carbon::parse(Auth::user()->created_at)->diffForHumans() }}</span>
                            <span class="stat-label">Miembro desde</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value">{{ Auth::user()->login_count ?? 42 }}</span>
                            <span class="stat-label">Inicios de sesión</span>
                        </div>
                    </div>

                    <a href="{{ route('profile.edit') }}" class="btn-edit-profile">
                        <i class="fas fa-edit"></i>
                        Editar Perfil
                    </a>
                </div>
            </div>

            {{-- Columna derecha: Información detallada --}}
            <div class="profile-card profile-info-card">
                <div class="card-header">
                    <div class="header-left">
                        <i class="fas fa-user"></i>
                        <h3>Información personal</h3>
                    </div>
                </div>

                <div class="info-list">
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <div class="info-content">
                            <span class="info-label">Nombre completo</span>
                            <span class="info-value">{{ Auth::user()->full_name ?? Auth::user()->name }}</span>
                        </div>
                    </div>

                    @if(Auth::user()->role === 'aliado')
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="info-content">
                                <span class="info-label">Empresa / Razón social</span>
                                <span class="info-value">{{ Auth::user()->company ?? 'No especificada' }}</span>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <div class="info-content">
                                <span class="info-label">RIF / Identificación fiscal</span>
                                <span class="info-value">{{ Auth::user()->identification ?? 'No especificado' }}</span>
                            </div>
                        </div>
                    @else
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-id-card"></i>
                            </div>
                            <div class="info-content">
                                <span class="info-label">Cédula / Identificación</span>
                                <span class="info-value">{{ Auth::user()->identification ?? 'No especificada' }}</span>
                            </div>
                        </div>
                        @if(Auth::user()->role !== 'admin')
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-birthday-cake"></i>
                                </div>
                                <div class="info-content">
                                    <span class="info-label">Fecha de nacimiento</span>
                                    <span class="info-value">
                                        {{ Auth::user()->dob ? \Carbon\Carbon::parse(Auth::user()->dob)->format('d/m/Y') : 'No especificada' }}
                                    </span>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>

                <div class="card-header" style="margin-top: 2rem;">
                    <div class="header-left">
                        <i class="fas fa-address-book"></i>
                        <h3>Información de contacto</h3>
                    </div>
                </div>

                <div class="info-list">
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="info-content">
                            <span class="info-label">Correo electrónico</span>
                            <span class="info-value">{{ Auth::user()->email }}</span>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div class="info-content">
                            <span class="info-label">Teléfono principal</span>
                            <span class="info-value">{{ Auth::user()->phone1 ?? '+58 412 1254953' }}</span>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="info-content">
                            <span class="info-label">Teléfono adicional</span>
                            <span class="info-value">{{ Auth::user()->phone2 ?? '+58 414 1244958' }}</span>
                        </div>
                    </div>

                    @if(Auth::user()->role === 'aliado' && Auth::user()->website)
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-globe"></i>
                            </div>
                            <div class="info-content">
                                <span class="info-label">Sitio web</span>
                                <span class="info-value">
                                    <a href="{{ Auth::user()->website }}" target="_blank">{{ Auth::user()->website }}</a>
                                </span>
                            </div>
                        </div>
                    @endif

                    @if(Auth::user()->address)
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="info-content">
                                <span class="info-label">Dirección</span>
                                <span class="info-value">{{ Auth::user()->address }}</span>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Actividad reciente --}}
                <div class="card-header" style="margin-top: 2rem;">
                    <div class="header-left">
                        <i class="fas fa-history"></i>
                        <h3>Actividad reciente</h3>
                    </div>
                </div>

                <div class="activity-timeline">
                    <div class="timeline-item">
                        <div class="timeline-icon">
                            <i class="fas fa-sign-in-alt"></i>
                        </div>
                        <div class="timeline-content">
                            <span class="timeline-title">Último acceso</span>
                            <span class="timeline-time">{{ Auth::user()->last_login_at ? \Carbon\Carbon::parse(Auth::user()->last_login_at)->diffForHumans() : 'Hace 2 horas' }}</span>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-icon">
                            <i class="fas fa-edit"></i>
                        </div>
                        <div class="timeline-content">
                            <span class="timeline-title">Perfil actualizado</span>
                            <span class="timeline-time">{{ Auth::user()->updated_at ? Auth::user()->updated_at->diffForHumans() : 'Hace 3 días' }}</span>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="timeline-content">
                            <span class="timeline-title">Cuenta creada</span>
                            <span class="timeline-time">{{ Auth::user()->created_at ? Auth::user()->created_at->diffForHumans() : 'Hace 1 año' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection