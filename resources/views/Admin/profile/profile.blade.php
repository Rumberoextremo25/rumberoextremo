@extends('layouts.admin')

@section('title', 'Perfil de Usuario')

@section('page_title', 'Mi Perfil')

@section('content')
    <div class="dashboard-container"> {{-- Contenedor principal para mantener la consistencia con el dashboard --}}

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
                <a href="{{ route('profile.edit') }}" class="btn btn-primary mt-3">
                    <i class="fas fa-edit"></i> Editar Perfil
                </a>
            </div>
        </div>

        ---

        <div class="profile-grid">
            {{-- Tarjeta de Información Personal --}}
            <div class="profile-card">
                <h3 class="card-title"><i class="fas fa-id-card me-2"></i> Información Personal</h3>
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
                <h3 class="card-title"><i class="fas fa-address-book me-2"></i> Información de Contacto</h3>
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
            {{-- Asumiendo que tienes un campo `is_ally` booleano en tu modelo User o una lógica similar --}}
            @if (Auth::user()->is_ally ?? false)
                <div class="profile-card allied-info-card">
                    <h3 class="card-title"><i class="fas fa-handshake me-2"></i> Información del Aliado</h3>
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
                            <strong>Fecha de Registro como Aliado:</strong>
                            <span>{{ Auth::user()->allied_registered_at ? \Carbon\Carbon::parse(Auth::user()->allied_registered_at)->format('d/m/Y') : 'N/A' }}</span>
                        </div>
                        <div class="detail-item">
                            <strong>URL del Sitio Web:</strong>
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
                            <span>{{ Auth::user()->discount ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Tarjeta de Seguridad y Preferencias (Opcional) --}}
            <div class="profile-card">
                <h3 class="card-title"><i class="fas fa-lock me-2"></i> Seguridad y Preferencias</h3>
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
                    <div class="d-flex justify-content-end mt-3">
                        <a href="{{ route('password.change') }}" class="btn btn-secondary btn-sm">Cambiar Contraseña</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Lógica para activar el enlace "Perfil" en la sidebar (si tu sidebar tiene esta estructura)
            const sidebarLinks = document.querySelectorAll('.sidebar ul li a');
            sidebarLinks.forEach(link => {
                link.classList.remove('active');
                if (link.href && link.href.includes('/profile')) { // Ajusta la ruta si es diferente en tu sidebar
                    link.classList.add('active');
                }
            });

            const body = document.body;
            const savedTheme = localStorage.getItem('theme'); // Asume que guardas el tema en localStorage
            if (savedTheme === 'dark-mode') {
                body.classList.add('dark-mode');
            } else {
                body.classList.remove('dark-mode');
            }

            const editButton = document.getElementById('editProfileButton'); // Este ID no está en tu HTML, asegúrate de añadirlo al botón de editar si lo quieres usar.
            if (editButton) {
                editButton.addEventListener('click', (e) => {
                    e.preventDefault();
                });
            }
        });
    </script>
@endsection