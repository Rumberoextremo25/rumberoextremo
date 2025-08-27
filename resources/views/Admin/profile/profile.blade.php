{{-- resources/views/profile.blade.php --}}

@extends('layouts.admin')

@section('title', 'Mi Perfil')

@section('page_title_toolbar', 'Gestion de Perfil')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
@endpush

@section('content')
    <div class="main-content">
        <div class="profile-view-container">

            {{-- Sección del Encabezado del Perfil --}}
            <div class="profile-header">
                {{-- Si el usuario tiene una foto, úsala. De lo contrario, muestra un ícono. --}}
                <div class="profile-avatar">
                    @if (Auth::user()->profile_photo_path)
                        <img src="{{ Storage::url(Auth::user()->profile_photo_path) }}" alt="Foto de Perfil"
                            style="width:100%; height:100%; border-radius:50%; object-fit:cover;">
                    @else
                        <img src="{{ asset('assets/img/dashboard/logo_perfil.png') }}" alt="Avatar de Usuario">
                    @endif
                </div>
                <div class="profile-info">
                    <h2>{{ Auth::user()->name ?? 'Juan Pérez' }}</h2>
                    <p class="role">{{ Auth::user()->role ?? 'Administrador de plataforma' }}</p>
                    <p class="company">{{ Auth::user()->company ?? 'Rumbero Extremo S.A.' }}</p>
                </div>
                <a href="{{ route('profile.edit') }}" class="edit-profile-button">
                    <i class="fas fa-edit"></i> Editar Perfil
                </a>
            </div>

            {{-- Sección de Información Personal --}}
            <h3 class="info-section-title">Información personal</h3>
            <div class="details-grid">
                <div class="detail-item">
                    <span class="detail-label">Tipo de usuario:</span>
                    <span class="detail-value">{{ Auth::user()->user_type ?? 'Usuario principal' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Nombre completo:</span>
                    <span class="detail-value">{{ Auth::user()->full_name ?? (Auth::user()->name ?? 'Juan Pérez') }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Cédula/RIF:</span>
                    <span class="detail-value">{{ Auth::user()->identification ?? 'V 12.345.678' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Fecha de nacimiento:</span>
                    <span
                        class="detail-value">{{ Auth::user()->dob ? \Carbon\Carbon::parse(Auth::user()->dob)->format('d/m/Y') : '15/05/1990' }}</span>
                </div>
            </div>

            {{-- Sección de Información de Contacto --}}
            <h3 class="info-section-title">Información de contacto</h3>
            <div class="details-grid">
                <div class="detail-item">
                    <span class="detail-label">Correo electrónico:</span>
                    <span class="detail-value">{{ Auth::user()->email ?? 'juan.perez@rumberoextremo.com' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Teléfono principal:</span>
                    <span class="detail-value">{{ Auth::user()->phone1 ?? '+58 412 1254953' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Teléfono adicional:</span>
                    <span class="detail-value">{{ Auth::user()->phone2 ?? '+58 414 1244958' }}</span>
                </div>
            </div>

        </div> {{-- Fin .profile-view-container --}}
    </div> {{-- Fin .main-content --}}
@endsection
