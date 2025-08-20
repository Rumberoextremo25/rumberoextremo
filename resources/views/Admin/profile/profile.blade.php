@extends('layouts.admin')

@section('title', 'Mi Perfil')

@section('page_title', 'Mi Perfil de Usuario') {{-- Asegúrate de que tu layout.admin.blade.php tenga un @yield('page_title') --}}

@push('styles')
    {{-- Asegúrate de que Font Awesome esté cargado en tu layout global, si no, puedes añadirlo aquí --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    {{-- Enlaza al archivo CSS específico para la vista de perfil --}}
    <link rel="stylesheet" href="{{ asset('css/admin/profile.css') }}">

    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">

    <link rel="stylesheet" href="{{ asset('css/toolbar.css') }}">
@endpush

@section('content')
    <div class="dashboard-container"> {{-- Contenedor principal ajustado para ser más angosto --}}

        {{-- Sección del Encabezado del Perfil --}}
        <div class="profile-hero-section">
            <div class="profile-avatar-wrapper">
                {{-- Usa la URL de la foto de perfil si existe en profile_photo_path, de lo contrario, un avatar generado --}}
                <img src="{{ Auth::user()->profile_photo_path ? Storage::url(Auth::user()->profile_photo_path) : asset('assets/img/dashboard/logo_perfil_sidebar.png') }}"
                    alt="Foto de Perfil" class="profile-avatar-lg">
            </div>
            <div class="profile-header-info">
                <h2 class="profile-display-name">{{ Auth::user()->name }}</h2>
                <p class="profile-role-tag">{{ Auth::user()->role ?? 'Administrador de plataforma' }}</p>
                <p class="profile-company">{{ Auth::user()->company ?? 'Rumbero Externo S.A.' }}</p>
                <a href="{{ route('profile.edit') }}" class="btn btn-primary edit-profile-btn">
                    <i class="fas fa-edit"></i> Editar Perfil
                </a>
            </div>
        </div>

        <div class="profile-grid">
            {{-- Tarjeta de Información Personal --}}
            <div class="profile-card">
                <h3 class="card-title">Información personal</h3>
                <div class="detail-group">
                    <div class="detail-item">
                        <strong>Tipo de usuario:</strong>
                        <span>{{ Auth::user()->user_type ?? 'Usuario principal' }}</span>
                    </div>
                    <div class="detail-item">
                        <strong>Nombre completo:</strong>
                        <span>{{ Auth::user()->full_name ?? Auth::user()->name . ' Rodríguez' }}</span>
                    </div>
                    <div class="detail-item">
                        <strong>Cédula/RIF:</strong>
                        <span>{{ Auth::user()->identification ?? 'V 12.345.678' }}</span>
                    </div>
                    <div class="detail-item">
                        <strong>Fecha de nacimiento:</strong>
                        <span>{{ Auth::user()->dob ? \Carbon\Carbon::parse(Auth::user()->dob)->format('d/m/Y') : '15/05/1990' }}</span>
                    </div>
                </div>
            </div>

            {{-- Tarjeta de Información de Contacto --}}
            <div class="profile-card">
                <h3 class="card-title">Información de contacto</h3>
                <div class="detail-group">
                    <div class="detail-item">
                        <strong>Correo electrónico:</strong>
                        <span>{{ Auth::user()->email ?? 'juan.perez@rumberoextremo.com' }}</span>
                    </div>
                    <div class="detail-item">
                        <strong>Teléfono principal:</strong>
                        <span>{{ Auth::user()->phone1 ?? '+58 412 1254953' }}</span>
                    </div>
                    <div class="detail-item">
                        <strong>Teléfono adicional:</strong>
                        <span>{{ Auth::user()->phone2 ?? '+58 414 1244958' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection