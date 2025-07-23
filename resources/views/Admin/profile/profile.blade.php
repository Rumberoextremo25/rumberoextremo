@extends('layouts.admin')

@section('title', 'Mi Perfil')

@section('page_title', 'Mi Perfil de Usuario') {{-- Asegúrate de que tu layout.admin.blade.php tenga un @yield('page_title') --}}

@push('styles')
    {{-- Asegúrate de que Font Awesome esté cargado en tu layout global, si no, puedes añadirlo aquí --}}
    {{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> --}}

    {{-- Enlaza al archivo CSS específico para la vista de perfil --}}
    <link rel="stylesheet" href="{{ asset('css/admin/profile.css') }}">
@endpush

@section('content')
    <div class="dashboard-container"> {{-- Contenedor principal ajustado para ser más angosto --}}

        {{-- Sección del Encabezado del Perfil --}}
        <div class="profile-hero-section">
            <div class="profile-avatar-wrapper">
                {{-- Usa la URL de la foto de perfil si existe en profile_photo_path, de lo contrario, un avatar generado --}}
                <img src="{{ Auth::user()->profile_photo_path ? Storage::url(Auth::user()->profile_photo_path) : asset('assets/img/logos/usuario.png') }}"
                    alt="Foto de Perfil" class="profile-avatar-lg">
                <span class="user-status-indicator online"></span> {{-- Indicador de estado (ej: online) --}}
            </div>
            <div class="profile-header-info">
                <h2 class="profile-display-name">{{ Auth::user()->name }}</h2>
                <p class="profile-role-tag">{{ Auth::user()->role ?? 'Usuario' }}</p> {{-- Asume un campo 'role' --}}
                <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Editar Perfil
                </a>
            </div>
        </div>

        <div class="profile-grid">
            {{-- Tarjeta de Información Personal --}}
            <div class="profile-card">
                <h3 class="card-title"><i class="fas fa-id-card"></i> Información Personal</h3>
                <div class="detail-group">
                    <div class="detail-item">
                        <strong>Tipo de Usuario:</strong>
                        <span>{{ Auth::user()->user_type ?? 'Usuario Principal' }}</span> {{-- Asume 'user_type' --}}
                    </div>
                    <div class="detail-item">
                        <strong>Nombre Completo:</strong>
                        <span>{{ Auth::user()->full_name ?? Auth::user()->name }}</span> {{-- Asume 'full_name' --}}
                    </div>
                    <div class="detail-item">
                        <strong>Cédula/RIF:</strong>
                        <span>{{ Auth::user()->identification ?? 'N/A' }}</span> {{-- Asume 'identification' --}}
                    </div>
                    <div class="detail-item">
                        <strong>Fecha de Nacimiento:</strong>
                        {{-- Asegúrate de que 'dob' sea una fecha en tu BD para usar Carbon --}}
                        <span>{{ Auth::user()->dob ? \Carbon\Carbon::parse(Auth::user()->dob)->format('d/m/Y') : 'N/A' }}</span>
                    </div>
                </div>
            </div>

            {{-- Tarjeta de Información de Contacto --}}
            <div class="profile-card">
                <h3 class="card-title"><i class="fas fa-address-book"></i> Información de Contacto</h3>
                <div class="detail-group">
                    <div class="detail-item">
                        <strong>Correo Electrónico:</strong>
                        <span>{{ Auth::user()->email }}</span>
                    </div>
                    <div class="detail-item">
                        <strong>Teléfono Principal:</strong>
                        <span>{{ Auth::user()->phone1 ?? 'N/A' }}</span> {{-- Asume 'phone1' --}}
                    </div>
                    <div class="detail-item">
                        <strong>Teléfono Adicional:</strong>
                        <span>{{ Auth::user()->phone2 ?? 'N/A' }}</span> {{-- Asume 'phone2' --}}
                    </div>
                    <div class="detail-item">
                        <strong>Dirección:</strong>
                        <span>{{ Auth::user()->address ?? 'N/A' }}</span> {{-- Asume 'address' --}}
                    </div>
                </div>
            </div>

            {{-- Tarjeta de Información del Aliado (Condicional) --}}
            @if (Auth::user()->is_ally ?? false)
                <div class="profile-card allied-info-card">
                    <h3 class="card-title"><i class="fas fa-handshake"></i> Información del Aliado</h3>
                    <div class="detail-group">
                        <div class="detail-item">
                            <strong>Nombre de la Empresa:</strong>
                            <span>{{ Auth::user()->allied_company_name ?? 'N/A' }}</span>
                        </div>
                        <div class="detail-item">
                            <strong>RIF de la Empresa:</strong>
                            <span>{{ Auth::user()->allied_company_rif ?? 'N/A' }}</span>
                        </div>
                        <div class="detail-item">
                            <strong>Categoría de Servicio:</strong>
                            <span>{{ Auth::user()->service_category ?? 'N/A' }}</span>
                        </div>
                        <div class="detail-item">
                            <strong>Fecha de Registro:</strong>
                            <span>{{ Auth::user()->allied_registered_at ? \Carbon\Carbon::parse(Auth::user()->allied_registered_at)->format('d/m/Y') : 'N/A' }}</span>
                        </div>
                        <div class="detail-item">
                            <strong>Sitio Web:</strong>
                            <span>
                                @if(Auth::user()->website_url)
                                    <a href="{{ Auth::user()->website_url }}" target="_blank">{{ Auth::user()->website_url }}</a>
                                @else
                                    N/A
                                @endif
                            </span>
                        </div>
                        <div class="detail-item">
                            <strong>Descuento Rumbero Extremo:</strong>
                            <span>{{ Auth::user()->discount ? number_format(Auth::user()->discount, 0) . '%' : 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Tarjeta de Seguridad y Preferencias (Opcional) --}}
            <div class="profile-card">
                <h3 class="card-title"><i class="fas fa-shield-alt"></i> Seguridad y Preferencias</h3>
                <div class="detail-group">
                    <div class="detail-item">
                        <strong>Último Acceso:</strong>
                        {{-- Asume un campo 'last_login_at' o similar --}}
                        <span>{{ Auth::user()->last_login_at ? \Carbon\Carbon::parse(Auth::user()->last_login_at)->diffForHumans() : 'Primera vez' }}</span>
                    </div>
                    <div class="detail-item">
                        <strong>Verificación de 2 Pasos:</strong>
                        {{-- Asume un campo 'two_factor_enabled' --}}
                        <span class="status-badge {{ (Auth::user()->two_factor_enabled ?? false) ? 'status-success' : 'status-danger' }}">
                            {{ (Auth::user()->two_factor_enabled ?? false) ? 'Activa' : 'Inactiva' }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('password.change') }}" class="btn btn-secondary">Cambiar Contraseña</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Enlaza al archivo JavaScript específico para la vista de perfil --}}
    <script src="{{ asset('js/admin/profile/profile.js') }}"></script>
@endpush