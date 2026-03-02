@extends('layouts.admin')

@section('title', 'Configuración - Rumbero Extremo')

@section('page_title_toolbar', 'Configuración')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/settings.css') }}">
@endpush

@section('content')
    <div class="settings-wrapper">
        {{-- Header con gradiente Rumbero --}}
        <div class="settings-header">
            <div class="header-content">
                <div class="header-tag">
                    <i class="fa-solid fa-bolt"></i>
                    <span>RUMBERO EXTREMO</span>
                    <i class="fa-solid fa-fire"></i>
                </div>
                <h1 class="page-title">
                    <span class="title-main">Configuración</span>
                    <span class="title-accent">de cuenta</span>
                </h1>
                <p class="page-subtitle">
                    <i class="fa-regular fa-circle-check"></i>
                    Administra tu cuenta y preferencias personales
                </p>
            </div>
            <div class="header-profile">
                <div class="profile-badge">
                    <div class="profile-avatar">
                        <span>{{ substr(auth()->user()->name, 0, 1) }}</span>
                    </div>
                    <div class="profile-info">
                        <span class="profile-name">{{ auth()->user()->name }}</span>
                        <span class="profile-email">{{ auth()->user()->email }}</span>
                        <span
                            class="profile-role">{{ auth()->user()->role === 'admin' ? 'Administrador' : 'Aliado' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Alertas flotantes --}}
        <div class="alert-container" id="alertContainer"></div>

        {{-- Contenido principal --}}
        <div class="settings-grid">
            {{-- Columna izquierda: Seguridad --}}
            <div class="settings-card security-card">
                <div class="card-header">
                    <div class="header-icon">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div class="header-title">
                        <h2>Seguridad</h2>
                        <p>Actualiza tu contraseña regularmente para mantener tu cuenta segura</p>
                    </div>
                </div>

                <form action="{{ route('admin.settings.change-password') }}" method="POST" id="passwordForm"
                    class="password-form">
                    @csrf

                    <div class="form-group">
                        <label for="current_password">
                            <i class="fa-solid fa-key"></i>
                            Contraseña Actual
                        </label>
                        <div class="input-wrapper">
                            <input type="password" name="current_password" id="current_password" required
                                class="form-control" placeholder="Ingresa tu contraseña actual">
                            <button type="button" class="toggle-password" data-target="current_password">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                        @error('current_password')
                            <span class="error-message"><i class="fa-solid fa-circle-exclamation"></i>
                                {{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="new_password">
                                <i class="fa-solid fa-lock"></i>
                                Nueva Contraseña
                            </label>
                            <div class="input-wrapper">
                                <input type="password" name="new_password" id="new_password" required class="form-control"
                                    placeholder="Mínimo 8 caracteres">
                                <button type="button" class="toggle-password" data-target="new_password">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                            <div class="password-strength" id="passwordStrength"></div>
                            @error('new_password')
                                <span class="error-message"><i class="fa-solid fa-circle-exclamation"></i>
                                    {{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="new_password_confirmation">
                                <i class="fa-solid fa-check-circle"></i>
                                Confirmar Contraseña
                            </label>
                            <div class="input-wrapper">
                                <input type="password" name="new_password_confirmation" id="new_password_confirmation"
                                    required class="form-control" placeholder="Repite la nueva contraseña">
                                <button type="button" class="toggle-password" data-target="new_password_confirmation">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                            <div class="password-match" id="passwordMatch"></div>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit" id="submitButton">
                        <i class="fa-solid fa-rotate-right"></i>
                        Actualizar Contraseña
                    </button>
                </form>
            </div>

            {{-- Columna derecha: Preferencias y 2FA --}}
            <div class="settings-card preferences-card">
                <div class="card-header">
                    <div class="header-icon">
                        <i class="fa-solid fa-sliders"></i>
                    </div>
                    <div class="header-title">
                        <h2>Preferencias</h2>
                        <p>Personaliza tu experiencia en la plataforma</p>
                    </div>
                </div>

                {{-- Toggles de preferencias --}}
                <div class="preferences-grid">
                    {{-- Autenticación 2FA --}}
                    <div class="preference-item">
                        <div class="preference-icon">
                            <i class="fa-solid fa-shield"></i>
                        </div>
                        <div class="preference-content">
                            <h3>Autenticación en dos pasos</h3>
                            <p>Añade una capa extra de seguridad a tu cuenta</p>
                            <div class="status-indicator {{ $user->two_factor_enabled ? 'active' : 'inactive' }}">
                                <i class="fa-solid fa-{{ $user->two_factor_enabled ? 'shield-check' : 'shield' }}"></i>
                                {{ $user->two_factor_enabled ? 'Activado' : 'Desactivado' }}
                            </div>
                        </div>
                        <div class="preference-action">
                            <label class="rumbero-switch">
                                <input type="checkbox" id="twoFactorToggle"
                                    {{ $user->two_factor_enabled ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    {{-- Notificaciones --}}
                    <div class="preference-item">
                        <div class="preference-icon">
                            <i class="fa-solid fa-bell"></i>
                        </div>
                        <div class="preference-content">
                            <h3>Notificaciones</h3>
                            <p>Recibe ofertas, actualizaciones y promociones</p>
                        </div>
                        <div class="preference-action">
                            <label class="rumbero-switch">
                                <input type="checkbox" id="notificationsToggle"
                                    {{ $user->notifications_enabled ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    {{-- Modo oscuro --}}
                    <div class="preference-item">
                        <div class="preference-icon">
                            <i class="fa-solid fa-moon"></i>
                        </div>
                        <div class="preference-content">
                            <h3>Modo oscuro</h3>
                            <p>Alterna entre tema claro y oscuro</p>
                        </div>
                        <div class="preference-action">
                            <label class="rumbero-switch">
                                <input type="checkbox" id="darkModeToggle"
                                    {{ $user->dark_mode_enabled ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Sección 2FA (expandible) --}}
                <div class="twofa-section" id="twofaSection" style="display: none;">
                    <div class="twofa-header">
                        <i class="fa-solid fa-qrcode"></i>
                        <h4>Configurar autenticación en dos pasos</h4>
                    </div>

                    <div class="twofa-steps">
                        {{-- Paso 1: QR Code --}}
                        <div class="step-card">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <h5>Escanea el código QR</h5>
                                <p>Usa Google Authenticator, Authy o cualquier app compatible</p>
                                <div class="qr-container">
                                    @if ($qrCodeSvg)
                                        <div class="qr-code">{!! $qrCodeSvg !!}</div>
                                    @else
                                        <div class="qr-placeholder">
                                            <i class="fa-solid fa-qrcode"></i>
                                            <p>Error al generar código QR</p>
                                        </div>
                                    @endif
                                    <div class="secret-key">
                                        <span class="key-label">Clave secreta:</span>
                                        <code id="secretKey">{{ $user->two_factor_secret }}</code>
                                        <button class="copy-btn" onclick="copySecretKey()" title="Copiar clave">
                                            <i class="fa-regular fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Paso 2: Verificación --}}
                        <div class="step-card">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <h5>Ingresa el código de verificación</h5>
                                <p>Introduce el código de 6 dígitos de tu aplicación</p>
                                <div class="verification-box">
                                    <div class="code-inputs">
                                        <input type="text" maxlength="1" class="code-digit" data-index="0">
                                        <input type="text" maxlength="1" class="code-digit" data-index="1">
                                        <input type="text" maxlength="1" class="code-digit" data-index="2">
                                        <input type="text" maxlength="1" class="code-digit" data-index="3">
                                        <input type="text" maxlength="1" class="code-digit" data-index="4">
                                        <input type="text" maxlength="1" class="code-digit" data-index="5">
                                    </div>
                                    <button class="btn-verify" id="verify2FABtn">
                                        <i class="fa-solid fa-check"></i>
                                        Verificar código
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Paso 3: Códigos de respaldo --}}
                        <div class="step-card">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <h5>Códigos de respaldo</h5>
                                <p>Guarda estos códigos en un lugar seguro</p>
                                <div class="backup-codes" id="backupCodesContainer">
                                    @if ($user->two_factor_recovery_codes)
                                        @php
                                            $backupCodes = json_decode($user->two_factor_recovery_codes, true);
                                            $unusedCodes = array_filter($backupCodes, fn($code) => !$code['used']);
                                        @endphp
                                        @if (count($unusedCodes) > 0)
                                            @foreach (array_slice($unusedCodes, 0, 8) as $code)
                                                <div class="code-chip">{{ $code['code'] }}</div>
                                            @endforeach
                                        @else
                                            <p class="text-muted">No hay códigos disponibles</p>
                                        @endif
                                    @else
                                        <p class="text-muted">Los códigos aparecerán después de activar 2FA</p>
                                    @endif
                                </div>
                                <button class="btn-generate" id="generateBackupCodesBtn">
                                    <i class="fa-solid fa-rotate"></i>
                                    Generar nuevos códigos
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ========== TOGGLE DE VISIBILIDAD DE CONTRASEÑA ==========
            document.querySelectorAll('.toggle-password').forEach(button => {
                button.addEventListener('click', function() {
                    const targetId = this.dataset.target;
                    const input = document.getElementById(targetId);
                    const icon = this.querySelector('i');

                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.className = 'fa-regular fa-eye-slash';
                    } else {
                        input.type = 'password';
                        icon.className = 'fa-regular fa-eye';
                    }
                });
            });

            // ========== VALIDACIÓN DE CONTRASEÑAS ==========
            const newPassword = document.getElementById('new_password');
            const confirmPassword = document.getElementById('new_password_confirmation');
            const passwordMatch = document.getElementById('passwordMatch');
            const passwordStrength = document.getElementById('passwordStrength');

            function validatePasswordMatch() {
                if (newPassword.value && confirmPassword.value) {
                    if (newPassword.value === confirmPassword.value) {
                        passwordMatch.innerHTML =
                            '<i class="fa-solid fa-check-circle"></i> Las contraseñas coinciden';
                        passwordMatch.className = 'password-match valid';
                        return true;
                    } else {
                        passwordMatch.innerHTML =
                            '<i class="fa-solid fa-times-circle"></i> Las contraseñas no coinciden';
                        passwordMatch.className = 'password-match invalid';
                        return false;
                    }
                } else {
                    passwordMatch.innerHTML = '';
                    return false;
                }
            }

            function checkPasswordStrength(password) {
                let strength = 0;
                if (password.length >= 8) strength++;
                if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
                if (password.match(/\d/)) strength++;
                if (password.match(/[^a-zA-Z\d]/)) strength++;

                let message = '';
                let className = '';

                switch (strength) {
                    case 0:
                    case 1:
                        message = '<i class="fa-solid fa-times-circle"></i> Contraseña débil';
                        className = 'invalid';
                        break;
                    case 2:
                    case 3:
                        message = '<i class="fa-solid fa-exclamation-circle"></i> Contraseña media';
                        className = 'warning';
                        break;
                    case 4:
                        message = '<i class="fa-solid fa-check-circle"></i> Contraseña fuerte';
                        className = 'valid';
                        break;
                }

                passwordStrength.innerHTML = message;
                passwordStrength.className = `password-strength ${className}`;
                return strength >= 3;
            }

            newPassword?.addEventListener('input', function() {
                checkPasswordStrength(this.value);
                validatePasswordMatch();
            });

            confirmPassword?.addEventListener('input', validatePasswordMatch);

            // ========== TOGGLE 2FA ==========
            const twoFactorToggle = document.getElementById('twoFactorToggle');
            const twofaSection = document.getElementById('twofaSection');

            twoFactorToggle?.addEventListener('change', function() {
                if (this.checked) {
                    twofaSection.style.display = 'block';
                } else {
                    if (confirm(
                        '¿Desactivar la autenticación en dos pasos? Tu cuenta será menos segura.')) {
                        toggleTwoFactor(false);
                    } else {
                        this.checked = true;
                    }
                }
            });

            // ========== INPUTS DE CÓDIGO 2FA ==========
            const codeInputs = document.querySelectorAll('.code-digit');
            codeInputs.forEach((input, index) => {
                input.addEventListener('input', function() {
                    if (this.value.length === 1 && index < codeInputs.length - 1) {
                        codeInputs[index + 1].focus();
                    }
                });

                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Backspace' && this.value.length === 0 && index > 0) {
                        codeInputs[index - 1].focus();
                    }
                });
            });

            // ========== FUNCIONES PARA ALERTAS ==========
            function showAlert(type, message) {
                const alertContainer = document.getElementById('alertContainer');
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert alert-${type}`;
                alertDiv.innerHTML = `
                <i class="fa-solid fa-${type === 'success' ? 'circle-check' : 'circle-exclamation'}"></i>
                <span>${message}</span>
                <button class="alert-close"><i class="fa-solid fa-times"></i></button>
            `;

                alertContainer.appendChild(alertDiv);

                setTimeout(() => alertDiv.remove(), 5000);

                alertDiv.querySelector('.alert-close')?.addEventListener('click', () => alertDiv.remove());
            }

            // ========== FUNCIONES PARA PETICIONES ==========
            async function toggleTwoFactor(enabled, code = null) {
                try {
                    const response = await fetch('/admin/settings/toggle-two-factor', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            enabled,
                            verification_code: code
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        showAlert('success', data.message);
                        if (data.backup_codes) updateBackupCodes(data.backup_codes);
                    } else {
                        showAlert('error', data.message);
                        twoFactorToggle.checked = !enabled;
                    }
                } catch (error) {
                    showAlert('error', 'Error al procesar la solicitud');
                }
            }

            async function verifyTwoFactorCode() {
                const code = Array.from(codeInputs).map(input => input.value).join('');
                if (code.length !== 6) {
                    showAlert('error', 'Ingresa un código de 6 dígitos');
                    return;
                }

                try {
                    const response = await fetch('/admin/settings/verify-two-factor', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            verification_code: code
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        showAlert('success', 'Código verificado correctamente');
                        toggleTwoFactor(true, code);
                    } else {
                        showAlert('error', 'Código inválido');
                    }
                } catch (error) {
                    showAlert('error', 'Error al verificar el código');
                }
            }

            document.getElementById('verify2FABtn')?.addEventListener('click', verifyTwoFactorCode);

            document.getElementById('generateBackupCodesBtn')?.addEventListener('click', async function() {
                try {
                    const response = await fetch('/admin/generate-backup-codes', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .content
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        updateBackupCodes(data.backup_codes);
                        showAlert('success', data.message);
                    } else {
                        showAlert('error', data.message);
                    }
                } catch (error) {
                    showAlert('error', 'Error al generar códigos');
                }
            });

            function updateBackupCodes(codes) {
                const container = document.getElementById('backupCodesContainer');
                container.innerHTML = codes.map(code => `<div class="code-chip">${code}</div>`).join('');
            }

            // ========== NOTIFICACIONES Y MODO OSCURO ==========
            document.getElementById('notificationsToggle')?.addEventListener('change', function() {
                fetch('/admin/update-notifications', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        notifications_enabled: this.checked
                    })
                }).then(res => res.json()).then(data => {
                    if (!data.success) this.checked = !this.checked;
                }).catch(() => showAlert('error', 'Error al actualizar preferencias'));
            });

            document.getElementById('darkModeToggle')?.addEventListener('change', function() {
                fetch('/admin/settings/update-dark-mode', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            dark_mode_enabled: this.checked
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // Disparar evento personalizado para que el layout lo escuche
                            window.dispatchEvent(new CustomEvent('dark-mode-changed', {
                                detail: {
                                    enabled: data.dark_mode_enabled
                                }
                            }));
                            showAlert('success', data.message);
                        } else {
                            this.checked = !this.checked;
                            showAlert('error', data.message);
                        }
                    })
                    .catch(() => {
                        this.checked = !this.checked;
                        showAlert('error', 'Error al actualizar el tema');
                    });
            });

            // ========== VALIDACIÓN DEL FORMULARIO ==========
            document.getElementById('passwordForm')?.addEventListener('submit', function(e) {
                if (!validatePasswordMatch() || !checkPasswordStrength(newPassword.value)) {
                    e.preventDefault();
                    showAlert('error', 'Por favor, corrige los errores en el formulario');
                }
            });
        });

        function copySecretKey() {
            const secretKey = document.getElementById('secretKey');
            navigator.clipboard?.writeText(secretKey.textContent).then(() => {
                showAlert('success', 'Clave secreta copiada');
            }).catch(() => {
                const textArea = document.createElement('textarea');
                textArea.value = secretKey.textContent;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                showAlert('success', 'Clave secreta copiada');
            });
        }
    </script>
@endpush
