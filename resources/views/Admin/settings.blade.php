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
                        <div class="password-strength" id="passwordStrength"></div>
                        @error('new_password')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="new_password_confirmation">Confirmar Nueva Contraseña:</label>
                        <input type="password" name="new_password_confirmation" id="new_password_confirmation" required
                            class="form-control">
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
                    <p>Añade una capa extra de seguridad a tu cuenta.</p>
                    <div class="two-factor-status {{ $user->two_factor_enabled ? 'enabled' : 'disabled' }}">
                        <i class="fas fa-{{ $user->two_factor_enabled ? 'shield-check' : 'shield-alt' }}"></i>
                        {{ $user->two_factor_enabled ? '2FA Activado' : '2FA No activado' }}
                    </div>
                </div>
                <div class="action-control">
                    <label class="switch">
                        <input type="checkbox" id="twoFactorToggle" {{ $user->two_factor_enabled ? 'checked' : '' }}>
                        <span class="slider"></span>
                    </label>
                </div>
            </div>

            {{-- Configuración de 2FA --}}
            <div class="two-factor-section" id="twoFactorSection"
                style="{{ $user->two_factor_enabled ? 'display: block;' : 'display: none;' }}">
                <h4>Configurar Autenticación en Dos Pasos</h4>
                <div class="two-factor-steps">
                    <div class="two-factor-step">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h4>Escanea el código QR</h4>
                            <p>Usa una aplicación de autenticación como Google Authenticator o Authy para escanear este
                                código QR.</p>
                            <div class="qr-code-container">
                                @if ($qrCodeSvg)
                                    <div class="qr-code">
                                        {!! $qrCodeSvg !!}
                                    </div>
                                @else
                                    <div class="qr-code-placeholder">
                                        <i class="fas fa-qrcode"></i>
                                        <p>Error al generar código QR</p>
                                    </div>
                                @endif
                                <div class="secret-key-info">
                                    <p><strong>Clave secreta:</strong> <code
                                            id="secretKey">{{ $user->two_factor_secret }}</code></p>
                                    <button class="modern-button small" onclick="copySecretKey()">
                                        <i class="fas fa-copy"></i> Copiar clave
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="two-factor-step">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h4>Introduce el código de verificación</h4>
                            <p>Ingresa el código de 6 dígitos generado por tu aplicación de autenticación.</p>
                            <div class="verification-input">
                                <input type="text" maxlength="1" id="code1" data-index="0">
                                <input type="text" maxlength="1" id="code2" data-index="1">
                                <input type="text" maxlength="1" id="code3" data-index="2">
                                <input type="text" maxlength="1" id="code4" data-index="3">
                                <input type="text" maxlength="1" id="code5" data-index="4">
                                <input type="text" maxlength="1" id="code6" data-index="5">
                            </div>
                            <button class="modern-button" id="verify2FABtn" style="margin-top: 1rem;">
                                <i class="fas fa-check"></i> Verificar Código
                            </button>
                            <div id="verificationResult" style="margin-top: 1rem;"></div>
                        </div>
                    </div>

                    <div class="two-factor-step">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h4>Guarda tus códigos de respaldo</h4>
                            <p>Estos códigos te permitirán acceder a tu cuenta si pierdes tu dispositivo. Guárdalos en un
                                lugar seguro.</p>
                            <div class="backup-codes-container">
                                @if ($user->two_factor_recovery_codes)
                                    @php
                                        $backupCodes = json_decode($user->two_factor_recovery_codes, true);
                                        $unusedCodes = array_filter($backupCodes, function ($code) {
                                            return !$code['used'];
                                        });
                                    @endphp
                                    @if (count($unusedCodes) > 0)
                                        <div class="backup-codes">
                                            @foreach (array_slice($unusedCodes, 0, 6) as $code)
                                                <div class="backup-code">{{ $code['code'] }}</div>
                                            @endforeach
                                        </div>
                                        <button class="modern-button" id="generateBackupCodesBtn"
                                            style="margin-top: 1rem;">
                                            <i class="fas fa-redo"></i> Generar Nuevos Códigos
                                        </button>
                                    @else
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle"></i> No hay códigos de respaldo
                                            disponibles.
                                        </div>
                                        <button class="modern-button" id="generateBackupCodesBtn"
                                            style="margin-top: 1rem;">
                                            <i class="fas fa-redo"></i> Generar Códigos de Respaldo
                                        </button>
                                    @endif
                                @else
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> Los códigos de respaldo se generarán después de
                                        activar 2FA.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
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
                        <input type="checkbox" id="notificationsToggle"
                            {{ $user->notifications_enabled ? 'checked' : '' }}>
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
                        <input type="checkbox" id="darkModeToggle" {{ $user->dark_mode_enabled ? 'checked' : '' }}>
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
            // ========== SISTEMA DE TEMAS ==========
            const darkModeToggle = document.getElementById('darkModeToggle');
            const currentTheme = localStorage.getItem('theme') || 'light';

            // Aplicar tema guardado
            if (currentTheme === 'dark') {
                document.body.classList.add('dark-mode');
                darkModeToggle.checked = true;
            }

            // Cambiar tema
            darkModeToggle.addEventListener('change', function() {
                if (this.checked) {
                    document.body.classList.add('dark-mode');
                    localStorage.setItem('theme', 'dark');
                } else {
                    document.body.classList.remove('dark-mode');
                    localStorage.setItem('theme', 'light');
                }

                // Guardar preferencia en el servidor
                updateDarkMode(this.checked);
            });

            // ========== VALIDACIÓN DE CONTRASEÑAS ==========
            const newPassword = document.getElementById('new_password');
            const confirmPassword = document.getElementById('new_password_confirmation');
            const passwordMatch = document.getElementById('passwordMatch');
            const passwordStrength = document.getElementById('passwordStrength');

            function validatePasswordMatch() {
                if (newPassword.value && confirmPassword.value) {
                    if (newPassword.value === confirmPassword.value) {
                        passwordMatch.innerHTML = '<i class="fas fa-check-circle"></i> Las contraseñas coinciden';
                        passwordMatch.className = 'password-match valid';
                        return true;
                    } else {
                        passwordMatch.innerHTML =
                            '<i class="fas fa-times-circle"></i> Las contraseñas no coinciden';
                        passwordMatch.className = 'password-match invalid';
                        return false;
                    }
                } else {
                    passwordMatch.className = 'password-match';
                    return false;
                }
            }

            function checkPasswordStrength(password) {
                let strength = 0;
                let feedback = '';

                if (password.length >= 8) strength++;
                if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
                if (password.match(/\d/)) strength++;
                if (password.match(/[^a-zA-Z\d]/)) strength++;

                switch (strength) {
                    case 0:
                    case 1:
                        feedback = '<i class="fas fa-times-circle"></i> Contraseña débil';
                        passwordStrength.className = 'password-strength invalid';
                        break;
                    case 2:
                    case 3:
                        feedback = '<i class="fas fa-exclamation-circle"></i> Contraseña media';
                        passwordStrength.className = 'password-strength warning';
                        break;
                    case 4:
                        feedback = '<i class="fas fa-check-circle"></i> Contraseña fuerte';
                        passwordStrength.className = 'password-strength valid';
                        break;
                }

                passwordStrength.innerHTML = feedback;
                return strength >= 3;
            }

            newPassword.addEventListener('input', function() {
                checkPasswordStrength(this.value);
                validatePasswordMatch();
            });

            confirmPassword.addEventListener('input', validatePasswordMatch);

            // ========== TOGGLE DE VISIBILIDAD DE CONTRASEÑA ==========
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

            // ========== EFECTOS HOVER ==========
            document.querySelectorAll('.modern-button, .switch').forEach(element => {
                element.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px)';
                });

                element.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });

            // ========== CERRAR ALERTAS AUTOMÁTICAMENTE ==========
            setTimeout(() => {
                document.querySelectorAll('.alert').forEach(alert => {
                    alert.style.display = 'none';
                });
            }, 5000);

            // ========== FUNCIONALIDAD DE 2FA ==========
            const twoFactorToggle = document.getElementById('twoFactorToggle');
            const twoFactorSection = document.getElementById('twoFactorSection');
            const verify2FABtn = document.getElementById('verify2FABtn');
            const generateBackupCodesBtn = document.getElementById('generateBackupCodesBtn');

            twoFactorToggle.addEventListener('change', function() {
                if (this.checked) {
                    twoFactorSection.style.display = 'block';
                    setTimeout(() => {
                        twoFactorSection.classList.add('active');
                    }, 10);
                } else {
                    if (confirm(
                            '¿Estás seguro de que quieres desactivar la autenticación en dos pasos? Esto reduce la seguridad de tu cuenta.'
                        )) {
                        toggleTwoFactorAuth(false);
                    } else {
                        this.checked = true;
                    }
                }
            });

            // Verificar código 2FA
            if (verify2FABtn) {
                verify2FABtn.addEventListener('click', function() {
                    const code = getVerificationCode();
                    if (code.length === 6) {
                        verifyTwoFactorCode(code);
                    } else {
                        showAlert('error', 'Por favor, ingresa un código de 6 dígitos.');
                    }
                });
            }

            // ========== FUNCIONES PARA COMUNICACIÓN CON EL SERVIDOR ==========
            async function toggleTwoFactorAuth(enabled, verificationCode = null) {
                try {
                    const response = await fetch('/admin/toggle-two-factor', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': getCsrfToken()
                        },
                        body: JSON.stringify({
                            enabled: enabled,
                            verification_code: verificationCode
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        showAlert('success', data.message);

                        if (enabled && data.backup_codes) {
                            showBackupCodes(data.backup_codes);
                            updateTwoFactorUI(true);
                        }

                        if (!enabled) {
                            updateTwoFactorUI(false);
                        }
                    } else {
                        showAlert('error', data.message);
                        updateTwoFactorUI(!enabled);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showAlert('error', 'Error al procesar la solicitud');
                    updateTwoFactorUI(!enabled);
                }
            }

            async function verifyTwoFactorCode(code) {
                try {
                    showLoading('Verificando código...');

                    const response = await fetch('/admin/verify-two-factor', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': getCsrfToken()
                        },
                        body: JSON.stringify({
                            verification_code: code
                        })
                    });

                    const data = await response.json();

                    hideLoading();

                    const verificationResult = document.getElementById('verificationResult');
                    if (data.success) {
                        verificationResult.innerHTML =
                            '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Código verificado correctamente</div>';
                        // Activar 2FA después de verificación exitosa
                        await toggleTwoFactorAuth(true, code);
                    } else {
                        verificationResult.innerHTML =
                            '<div class="alert alert-danger"><i class="fas fa-times-circle"></i> Código inválido. Inténtalo de nuevo.</div>';
                        clearVerificationCode();
                    }
                } catch (error) {
                    console.error('Error:', error);
                    hideLoading();
                    showAlert('error', 'Error al verificar el código');
                }
            }

            // ========== ACTUALIZAR INTERFAZ DE 2FA ==========
            function updateTwoFactorUI(enabled) {
                const twoFactorToggle = document.getElementById('twoFactorToggle');
                const twoFactorSection = document.getElementById('twoFactorSection');
                const twoFactorStatus = document.querySelector('.two-factor-status');

                twoFactorToggle.checked = enabled;

                if (enabled) {
                    // Actualizar el estado visual a "Activado"
                    if (twoFactorStatus) {
                        twoFactorStatus.className = 'two-factor-status enabled';
                        twoFactorStatus.innerHTML = '<i class="fas fa-shield-check"></i> 2FA Activado';
                    }

                    // Mostrar la sección de configuración
                    twoFactorSection.style.display = 'block';
                    setTimeout(() => {
                        twoFactorSection.classList.add('active');
                    }, 10);

                    // Mostrar mensaje de éxito
                    showAlert('success', 'Autenticación en dos pasos activada correctamente.');

                } else {
                    // Actualizar el estado visual a "No activado"
                    if (twoFactorStatus) {
                        twoFactorStatus.className = 'two-factor-status disabled';
                        twoFactorStatus.innerHTML = '<i class="fas fa-shield-alt"></i> 2FA No activado';
                    }

                    // Ocultar la sección de configuración
                    twoFactorSection.classList.remove('active');
                    setTimeout(() => {
                        twoFactorSection.style.display = 'none';
                    }, 300);

                    // Mostrar mensaje de éxito
                    showAlert('success', 'Autenticación en dos pasos desactivada correctamente.');
                }
            }

            // ========== VALIDACIÓN DEL FORMULARIO DE CONTRASEÑA ==========
            document.getElementById('passwordForm').addEventListener('submit', function(e) {
                if (!validatePasswordMatch() || !checkPasswordStrength(newPassword.value)) {
                    e.preventDefault();
                    showAlert('error', 'Por favor, corrige los errores en el formulario antes de enviar.');
                }
            });

            // ========== FUNCIONES PARA COMUNICACIÓN CON EL SERVIDOR ==========
            async function toggleTwoFactorAuth(enabled, verificationCode = null) {
                try {
                    const response = await fetch('/admin/toggle-two-factor', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': getCsrfToken()
                        },
                        body: JSON.stringify({
                            enabled: enabled,
                            verification_code: verificationCode
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        showAlert('success', data.message);

                        if (enabled && data.backup_codes) {
                            showBackupCodes(data.backup_codes);
                            twoFactorSection.style.display = 'block';
                            setTimeout(() => {
                                twoFactorSection.classList.add('active');
                            }, 10);
                            twoFactorToggle.checked = true;
                        }

                        if (!enabled) {
                            twoFactorSection.classList.remove('active');
                            setTimeout(() => {
                                twoFactorSection.style.display = 'none';
                            }, 300);
                            twoFactorToggle.checked = false;
                        }
                    } else {
                        showAlert('error', data.message);
                        twoFactorToggle.checked = !enabled;
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showAlert('error', 'Error al procesar la solicitud');
                    twoFactorToggle.checked = !enabled;
                }
            }

            async function verifyTwoFactorCode(code) {
                try {
                    showLoading('Verificando código...');

                    const response = await fetch('/admin/verify-two-factor', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': getCsrfToken()
                        },
                        body: JSON.stringify({
                            verification_code: code
                        })
                    });

                    const data = await response.json();

                    hideLoading();

                    const verificationResult = document.getElementById('verificationResult');
                    if (data.success) {
                        verificationResult.innerHTML =
                            '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Código verificado correctamente</div>';
                        toggleTwoFactorAuth(true, code);
                    } else {
                        verificationResult.innerHTML =
                            '<div class="alert alert-danger"><i class="fas fa-times-circle"></i> Código inválido. Inténtalo de nuevo.</div>';
                        clearVerificationCode();
                    }
                } catch (error) {
                    console.error('Error:', error);
                    hideLoading();
                    showAlert('error', 'Error al verificar el código');
                }
            }

            async function generateNewBackupCodes() {
                try {
                    showLoading('Generando códigos de respaldo...');

                    const response = await fetch('/admin/generate-backup-codes', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': getCsrfToken()
                        }
                    });

                    const data = await response.json();

                    hideLoading();

                    if (data.success) {
                        showBackupCodes(data.backup_codes);
                        showAlert('success', data.message);
                    } else {
                        showAlert('error', data.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    hideLoading();
                    showAlert('error', 'Error al generar códigos de respaldo');
                }
            }

            async function updateDarkMode(enabled) {
                try {
                    const response = await fetch('/admin/update-dark-mode', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': getCsrfToken()
                        },
                        body: JSON.stringify({
                            dark_mode_enabled: enabled
                        })
                    });

                    const data = await response.json();

                    if (!data.success) {
                        showAlert('error', data.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            }

            async function updateNotifications(enabled) {
                try {
                    const response = await fetch('/admin/update-notifications', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': getCsrfToken()
                        },
                        body: JSON.stringify({
                            notifications_enabled: enabled
                        })
                    });

                    const data = await response.json();

                    if (!data.success) {
                        showAlert('error', data.message);
                        document.getElementById('notificationsToggle').checked = !enabled;
                    }
                } catch (error) {
                    console.error('Error:', error);
                    document.getElementById('notificationsToggle').checked = !enabled;
                }
            }

            // ========== FUNCIONES AUXILIARES ==========
            function getVerificationCode() {
                const inputs = document.querySelectorAll('.verification-input input');
                let code = '';
                inputs.forEach(input => {
                    code += input.value;
                });
                return code;
            }

            function clearVerificationCode() {
                const inputs = document.querySelectorAll('.verification-input input');
                inputs.forEach(input => {
                    input.value = '';
                });
                inputs[0].focus();
            }

            function showBackupCodes(codes) {
                const backupCodesContainer = document.querySelector('.backup-codes');
                if (backupCodesContainer) {
                    backupCodesContainer.innerHTML = '';

                    codes.forEach(code => {
                        const codeElement = document.createElement('div');
                        codeElement.className = 'backup-code';
                        codeElement.textContent = code;
                        backupCodesContainer.appendChild(codeElement);
                    });
                }
            }

            function showAlert(type, message) {
                const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                const icon = type === 'success' ? 'check-circle' : 'exclamation-triangle';

                const alertDiv = document.createElement('div');
                alertDiv.className = `alert ${alertClass}`;
                alertDiv.innerHTML = `<i class="fas fa-${icon}"></i> ${message}`;

                const settingsCard = document.querySelector('.settings-card');
                if (settingsCard) {
                    settingsCard.insertBefore(alertDiv, settingsCard.firstChild);

                    setTimeout(() => {
                        if (alertDiv.parentNode) {
                            alertDiv.remove();
                        }
                    }, 5000);
                }
            }

            function showLoading(message = 'Procesando...') {
                const loadingDiv = document.createElement('div');
                loadingDiv.id = 'loadingOverlay';
                loadingDiv.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.7);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 9999;
                    color: white;
                    font-size: 1.2rem;
                `;
                loadingDiv.innerHTML = `
                    <div style="text-align: center;">
                        <div class="spinner" style="border: 4px solid #f3f3f3; border-top: 4px solid #3b82f6; border-radius: 50%; width: 50px; height: 50px; animation: spin 1s linear infinite; margin: 0 auto 1rem;"></div>
                        <p>${message}</p>
                    </div>
                `;
                document.body.appendChild(loadingDiv);
            }

            function hideLoading() {
                const loadingDiv = document.getElementById('loadingOverlay');
                if (loadingDiv) {
                    document.body.removeChild(loadingDiv);
                }
            }

            function getCsrfToken() {
                return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            }

            // ========== CONFIGURACIÓN DE NOTIFICACIONES ==========
            const notificationsToggle = document.getElementById('notificationsToggle');
            if (notificationsToggle) {
                notificationsToggle.addEventListener('change', function() {
                    updateNotifications(this.checked);
                });
            }
        });

        // Función para copiar la clave secreta
        function copySecretKey() {
            const secretKey = document.getElementById('secretKey');
            const textArea = document.createElement('textarea');
            textArea.value = secretKey.textContent;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);

            showAlert('success', 'Clave secreta copiada al portapapeles');
        }

        // Función global para mostrar alertas
        function showAlert(type, message) {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const icon = type === 'success' ? 'check-circle' : 'exclamation-triangle';

            const alertDiv = document.createElement('div');
            alertDiv.className = `alert ${alertClass}`;
            alertDiv.innerHTML = `<i class="fas fa-${icon}"></i> ${message}`;

            const settingsCard = document.querySelector('.settings-card');
            if (settingsCard) {
                settingsCard.insertBefore(alertDiv, settingsCard.firstChild);

                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.remove();
                    }
                }, 5000);
            }
        }
    </script>
@endpush
