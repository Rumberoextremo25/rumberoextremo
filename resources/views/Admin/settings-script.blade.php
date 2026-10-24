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
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            document.getElementById('notificationsToggle')?.addEventListener('change', function() {
                const toggle = this;
                const url = toggle.dataset.url || '/admin/settings/update-notifications';

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ notifications_enabled: toggle.checked })
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            showAlert('success', data.message || 'Preferencia guardada');
                        } else {
                            toggle.checked = !toggle.checked;
                            showAlert('error', data.message || 'No se pudo guardar la preferencia');
                        }
                    })
                    .catch(() => {
                        toggle.checked = !toggle.checked;
                        showAlert('error', 'Error al actualizar preferencias');
                    });
            });

            // Modo oscuro: se aplica al instante en todo el panel y luego se guarda
            document.getElementById('darkModeToggle')?.addEventListener('change', function() {
                const toggle = this;
                const enabled = toggle.checked;

                // 1) Feedback inmediato (el shell escucha este evento)
                window.dispatchEvent(new CustomEvent('dark-mode-changed', { detail: { enabled } }));

                // 2) Persistencia por usuario
                fetch('{{ route('admin.settings.update-dark-mode') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ dark_mode_enabled: enabled })
                })
                    .then(res => res.json())
                    .then(data => {
                        const applied = data.dark_mode_enabled ?? enabled;

                        window.dispatchEvent(new CustomEvent('dark-mode-changed', { detail: { enabled: applied } }));

                        if (data.success) {
                            showAlert('success', data.message || 'Tema actualizado');
                        } else {
                            toggle.checked = !applied;
                            showAlert('error', data.message || 'No se pudo guardar el tema');
                        }
                    })
                    .catch(() => {
                        toggle.checked = !enabled;
                        window.dispatchEvent(new CustomEvent('dark-mode-changed', { detail: { enabled: !enabled } }));
                        showAlert('error', 'Error al actualizar el tema');
                    });
            });

            // ========== DIAGNÓSTICO DE IA ==========
            document.getElementById('btn-test-ai')?.addEventListener('click', function () {
                const btn = this;
                const box = document.getElementById('support-ai-result');
                const original = btn.innerHTML;

                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Probando…';
                if (box) {
                    box.hidden = false;
                    box.innerHTML = '<span class="ai-result-pending">Consultando proveedores…</span>';
                }

                fetch('{{ route('admin.settings.test-ai') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                    .then(res => res.json())
                    .then(data => {
                        if (!box) return;

                        const rows = Object.entries(data.providers || {}).map(([name, info]) => `
                            <div class="ai-result-row ${info.ok ? 'is-ok' : 'is-error'}">
                                <i class="fa-solid ${info.ok ? 'fa-circle-check' : 'fa-circle-xmark'}"></i>
                                <strong>${name === 'gemini' ? 'Gemini' : 'DeepSeek'}:</strong>
                                <span>${info.ok ? 'responde' : 'no responde'} — ${info.detail || ''}</span>
                            </div>`).join('');

                        box.innerHTML = `<p class="ai-result-summary">${data.message || ''}</p>${rows}`;
                    })
                    .catch(() => {
                        if (box) box.innerHTML = '<span class="ai-result-error">No se pudo probar la IA.</span>';
                    })
                    .finally(() => {
                        btn.disabled = false;
                        btn.innerHTML = original;
                    });
            });

            // ========== SOPORTE DEL CHAT ==========
            document.getElementById('btn-save-support')?.addEventListener('click', function () {
                const btn = this;
                const status = document.getElementById('support-status');
                const original = btn.innerHTML;

                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Guardando…';
                if (status) status.textContent = '';

                fetch('{{ route('admin.settings.update-support') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        support_email: document.getElementById('support_email').value.trim(),
                        support_whatsapp: document.getElementById('support_whatsapp').value.trim(),
                        chat_ai_enabled: document.getElementById('chat_ai_enabled').checked
                    })
                })
                    .then(res => res.json())
                    .then(data => {
                        if (status) {
                            status.textContent = data.message || (data.success ? 'Guardado' : 'Error al guardar');
                            status.className = 'support-status ' + (data.success ? 'is-ok' : 'is-error');
                        }
                        if (data.success) {
                            showAlert('success', data.message || 'Configuración guardada');
                        } else {
                            showAlert('error', data.message || 'No se pudo guardar');
                        }
                    })
                    .catch(() => {
                        if (status) {
                            status.textContent = 'Error de conexión';
                            status.className = 'support-status is-error';
                        }
                        showAlert('error', 'Error al guardar la configuración de soporte');
                    })
                    .finally(() => {
                        btn.disabled = false;
                        btn.innerHTML = original;
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