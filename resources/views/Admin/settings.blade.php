@extends('layouts.admin')

@section('title', 'Ajustes de la Cuenta')

@section('page_title_toolbar', 'Gestión de Ajustes')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/settings.css') }}">
@endpush

@section('content')
    <div class="account-settings-container">
        <div class="settings-card">
            
            {{-- Sección de Cambiar Contraseña --}}
            <div class="password-change-section">
                <h3 class="section-heading"><i class="fas fa-lock"></i> Cambiar Contraseña</h3>
                <form action="{{ route('admin.password.change') }}" method="POST" id="passwordForm">
                    @csrf
                    @if (session('success'))
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                        </div>
                    @endif
                    
                    <div class="form-group">
                        <label for="current_password">Contraseña Actual:</label>
                        <input type="password" name="current_password" id="current_password" required class="form-control">
                        <i class="fas fa-key"></i>
                        @error('current_password')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">Nueva Contraseña:</label>
                        <input type="password" name="new_password" id="new_password" required class="form-control">
                        <i class="fas fa-lock"></i>
                        <div class="password-strength"></div>
                        @error('new_password')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password_confirmation">Confirmar Nueva Contraseña:</label>
                        <input type="password" name="new_password_confirmation" id="new_password_confirmation" required class="form-control">
                        <i class="fas fa-lock"></i>
                        <div class="password-match" id="passwordMatch"></div>
                    </div>
                    
                    <button type="submit" class="modern-button" id="submitButton">
                        <i class="fas fa-save"></i> Actualizar Contraseña
                    </button>
                </form>
            </div>
            
            {{-- Sección de Otras Opciones --}}
            <h3 class="section-heading"><i class="fas fa-sliders-h"></i> Preferencias de la cuenta</h3>

            {{-- Autenticación en dos pasos (2FA) --}}
            <div class="setting-item">
                <div class="setting-info">
                    <h3>Autenticación en dos pasos (2FA)</h3>
                    <p>Añade una copia extra de seguridad a tu cuenta.</p>
                </div>
                <div class="action-control">
                    <label class="switch">
                        <input type="checkbox" id="twoFactorToggle">
                        <span class="slider"></span>
                    </label>
                </div>
            </div>

            {{-- Notificaciones --}}
            <div class="setting-item">
                <div class="setting-info">
                    <h3>Notificaciones</h3>
                    <p>Recibe ofertas sobre eventos, actualizaciones y promociones.</p>
                </div>
                <div class="action-control">
                    <label class="switch">
                        <input type="checkbox" id="notificationsToggle" checked>
                        <span class="slider"></span>
                    </label>
                </div>
            </div>

            {{-- Estilo de pantalla --}}
            <div class="setting-item">
                <div class="setting-info">
                    <h3>Estilo de pantalla</h3>
                    <p>Alterna entre el modo claro y el modo oscuro para una mejor visualización.</p>
                </div>
                <div class="action-control">
                    <label class="switch">
                        <input type="checkbox" id="darkModeToggle">
                        <span class="slider"></span>
                    </label>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Validación de coincidencia de contraseñas
        const newPassword = document.getElementById('new_password');
        const confirmPassword = document.getElementById('new_password_confirmation');
        const passwordMatch = document.getElementById('passwordMatch');
        const submitButton = document.getElementById('submitButton');

        function validatePasswordMatch() {
            if (newPassword.value && confirmPassword.value) {
                if (newPassword.value === confirmPassword.value) {
                    passwordMatch.innerHTML = '<i class="fas fa-check-circle"></i> Las contraseñas coinciden';
                    passwordMatch.className = 'password-match valid';
                } else {
                    passwordMatch.innerHTML = '<i class="fas fa-times-circle"></i> Las contraseñas no coinciden';
                    passwordMatch.className = 'password-match invalid';
                }
            } else {
                passwordMatch.className = 'password-match';
            }
        }

        newPassword.addEventListener('input', validatePasswordMatch);
        confirmPassword.addEventListener('input', validatePasswordMatch);

        // Toggle de visibilidad de contraseña
        function togglePasswordVisibility(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.parentNode.querySelector('.fas');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fas fa-eye';
            } else {
                input.type = 'password';
                icon.className = 'fas fa-eye-slash';
            }
        }

        // Añadir botones de toggle de visibilidad
        document.querySelectorAll('.form-group .fas').forEach(icon => {
            icon.style.cursor = 'pointer';
            icon.addEventListener('click', function() {
                const inputId = this.parentNode.querySelector('input').id;
                togglePasswordVisibility(inputId);
            });
        });

        // Efectos hover en elementos interactivos
        document.querySelectorAll('.modern-button, .switch').forEach(element => {
            element.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
            });

            element.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        // Cerrar alertas automáticamente después de 5 segundos
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                alert.style.display = 'none';
            });
        }, 5000);

        // Simulación de funcionalidad de toggles
        document.getElementById('twoFactorToggle').addEventListener('change', function() {
            if (this.checked) {
                alert('Autenticación en dos pasos activada. Serás redirigido a la configuración.');
            }
        });

        document.getElementById('darkModeToggle').addEventListener('change', function() {
            if (this.checked) {
                document.body.style.filter = 'invert(1) hue-rotate(180deg)';
            } else {
                document.body.style.filter = 'none';
            }
        });
    });
</script>
@endpush